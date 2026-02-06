<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\SalesItem;
use App\Events\SaleCompleted;
use App\Services\InventorySyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class POSController extends Controller
{
    protected InventorySyncService $inventorySyncService;

    public function __construct(InventorySyncService $inventorySyncService)
    {
        $this->inventorySyncService = $inventorySyncService;
    }

    public function index()
    {
        $categories = Category::where('is_active', true)->with('products')->get();
        $branchId = Auth::user()->branch_id;
        
        // Get products with current inventory for this branch
        $products = Product::where('is_active', true)
            ->with(['category', 'inventory' => function($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            }, 'ingredients'])
            ->get();

        return view('pos.index', compact('categories', 'products'));
    }

    public function processSale(Request $request)
    {
        try {
            // Normalize items: client may send JSON string in FormData
            if (is_string($request->input('items'))) {
                $decoded = json_decode($request->input('items'), true);
                $request->merge(['items' => $decoded]);
            }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.options' => 'nullable|array',
            'payment_method' => 'required|in:cash,card,gcash',
            'amount_paid' => 'required|numeric|min:0',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        // Idempotency check - prevent duplicate submissions
        if ($request->filled('idempotency_key')) {
            $existingSale = Sale::where('idempotency_key', $request->idempotency_key)->first();
            if ($existingSale) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sale already processed (duplicate request)',
                    'redirect_url' => route('pos.receipt', $existingSale->id),
                    'low_stock_alerts' => [],
                    'inventory_synced' => $existingSale->inventory_synced,
                    'is_duplicate' => true
                ]);
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . $validator->errors()->first()
            ], 422);
        }

        $branchId = Auth::user()->branch_id;
        $subtotal = 0;
        $items = [];

        // Pre-check inventory availability for all items
        foreach ($request->items as $item) {
            $product = Product::with('ingredients')->findOrFail($item['product_id']);
            $availability = $this->inventorySyncService->checkProductAvailability(
                $product, 
                $branchId, 
                $item['quantity']
            );
            
            if (!$availability['available']) {
                $reason = $availability['reason'] ?? 'Unknown availability issue';
                if (isset($availability['missing_ingredients'])) {
                    $missing = collect($availability['missing_ingredients'])
                        ->pluck('name')
                        ->implode(', ');
                    $reason .= ": {$missing}";
                }
                return response()->json([
                    'success' => false,
                    'error' => "Cannot sell {$product->name}: {$reason}"
                ], 400);
            }
        }

        // Calculate totals
        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);

            // Compute unit price based on selected options (if any)
            $unitPrice = $this->getUnitPrice($product, $item['options'] ?? null);
            $lineTotal = $unitPrice * $item['quantity'];
            $subtotal += $lineTotal;

            $items[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'total_price' => $lineTotal,
                'options' => $item['options'] ?? null,
            ];
        }

        $taxAmount = 0; // You can implement tax calculation here
        $discountAmount = 0; // You can implement discount logic here
        $totalAmount = $subtotal + $taxAmount - $discountAmount;
        $changeAmount = max(0, $request->amount_paid - $totalAmount);

        if ($request->amount_paid < $totalAmount) {
            return response()->json([
                'success' => false,
                'error' => 'Insufficient payment amount'
            ], 400);
        }

        // Wrap sale creation, items, and inventory sync in a database transaction
        // This ensures atomicity - all or nothing
        $result = DB::transaction(function () use ($branchId, $subtotal, $taxAmount, $discountAmount, $totalAmount, $request, $changeAmount, $items) {
            // Create the sale with idempotency key
            $sale = Sale::create([
                'branch_id' => $branchId,
                'user_id' => Auth::id(),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'amount_paid' => $request->amount_paid,
                'change_amount' => $changeAmount,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'receipt_number' => $this->generateReceiptNumber($branchId),
                'inventory_synced' => false,
                'idempotency_key' => $request->idempotency_key,
            ]);

            // Create sale items
            foreach ($items as $item) {
                SalesItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                    'options' => $item['options'] ?? null,
                ]);
            }

            // *** REAL-TIME INVENTORY SYNC ***
            // Deduct inventory immediately upon payment confirmation
            // This also records stock movements for audit trail
            $syncResult = $this->inventorySyncService->processSaleDeductions($sale);
            
            if (!$syncResult['success']) {
                // Log but don't fail the sale - inventory sync errors are logged
                Log::warning('Inventory sync had issues for sale #' . $sale->id, $syncResult['errors']);
            }

            return [
                'sale' => $sale,
                'syncResult' => $syncResult,
            ];
        });

        $sale = $result['sale'];
        $syncResult = $result['syncResult'];

        // Include low stock alerts in response for frontend notification
        $lowStockAlerts = $syncResult['low_stock_alerts'] ?? [];

        // Broadcast sale completed event for real-time dashboard updates
        try {
            event(new SaleCompleted($sale));
        } catch (\Exception $e) {
            // Log but don't fail the sale if broadcasting fails
            Log::warning('Failed to broadcast sale event: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Sale completed successfully!',
            'redirect_url' => route('pos.receipt', $sale->id),
            'low_stock_alerts' => $lowStockAlerts,
            'inventory_synced' => $syncResult['success']
        ]);
        } catch (\Exception $e) {
            Log::error('POS Sale Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while processing the sale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Void a sale and restore inventory.
     */
    public function voidSale(Request $request, Sale $sale)
    {
        try {
            // Verify the sale belongs to the current user's branch or user is admin
            $user = Auth::user();
            if (!$user->isAdmin() && $sale->branch_id !== $user->branch_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized to void this sale'
                ], 403);
            }

            if ($sale->status === 'voided') {
                return response()->json([
                    'success' => false,
                    'error' => 'Sale is already voided'
                ], 400);
            }

            // Restore inventory
            $restoreResult = $this->inventorySyncService->restoreVoidedSale($sale);

            if (!$restoreResult['success'] && !empty($restoreResult['errors'])) {
                Log::warning('Inventory restoration had issues for voided sale #' . $sale->id, $restoreResult['errors']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sale voided successfully. Inventory has been restored.',
                'restorations' => $restoreResult['restorations'] ?? []
            ]);

        } catch (\Exception $e) {
            Log::error('Void Sale Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while voiding the sale: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showReceipt(Sale $sale)
    {
        $sale->load(['salesItems.product', 'user', 'branch']);
        return view('pos.receipt', compact('sale'));
    }

    /**
     * Determine unit price from product options and selected values.
     * Supports both fixed pricing and modifier-based pricing:
     * - Fixed pricing: priceType = 'fixed' or price >= base price (replaces base price)
     * - Modifier pricing: priceType = 'modifier' or small price values (adds to base price)
     */
    private function getUnitPrice(Product $product, $selectedOptions)
    {
        $base = $product->price;

        $options = $product->options ?? [];
        if (empty($options) || empty($selectedOptions) || !is_array($selectedOptions)) {
            return $base;
        }

        $totalModifiers = 0;
        $hasFixedPrice = false;
        $fixedPriceValue = $base;

        foreach ($options as $opt) {
            $optName = $opt['name'] ?? null;
            $values = $opt['values'] ?? [];
            if (!$optName) continue;

            // selected option value for this option name
            if (!array_key_exists($optName, $selectedOptions)) continue;
            $selectedVal = $selectedOptions[$optName];

            // find matching value in option definitions
            foreach ($values as $v) {
                if (is_array($v) || is_object($v)) {
                    $vArr = (array) $v;
                    $label = $vArr['label'] ?? $vArr['value'] ?? $vArr['name'] ?? null;
                    if ($label == $selectedVal && isset($vArr['price'])) {
                        $priceValue = (float) $vArr['price'];
                        $priceType = $vArr['priceType'] ?? null;
                        
                        // Determine if this is a fixed price or modifier
                        // Fixed price: explicitly marked as 'fixed' OR price >= base price
                        $isFixed = ($priceType === 'fixed') || ($priceValue >= $base);
                        
                        if ($isFixed && $priceValue > 0) {
                            $hasFixedPrice = true;
                            $fixedPriceValue = $priceValue;
                        } else if (!$hasFixedPrice) {
                            // Modifier adds to the running total
                            $totalModifiers += $priceValue;
                        }
                        break; // found match for this option
                    }
                } else {
                    if ($v == $selectedVal) {
                        // no price info, continue
                        break;
                    }
                }
            }
        }

        // Return fixed price if set, otherwise base + modifiers
        if ($hasFixedPrice) {
            return $fixedPriceValue;
        }

        return $base + $totalModifiers;
    }

    private function generateReceiptNumber($branchId)
    {
        $branch = \App\Models\Branch::find($branchId);
        $branchCode = strtoupper(substr($branch->name, 0, 3));
        $date = now()->format('Ymd');
        $sequence = Sale::where('branch_id', $branchId)
            ->whereDate('created_at', now())
            ->count() + 1;
        
        return $branchCode . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
