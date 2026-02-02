<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\SalesItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class POSController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->with('products')->get();
        $branchId = Auth::user()->branch_id;
        
        // Get products with current inventory for this branch
        $products = Product::where('is_active', true)
            ->with(['category', 'inventory' => function($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            }])
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . $validator->errors()->first()
            ], 422);
        }

        $branchId = Auth::user()->branch_id;
        $subtotal = 0;
        $items = [];

        // Calculate totals (inventory now managed at ingredient level)
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

        // Create the sale
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
            'receipt_number' => $this->generateReceiptNumber($branchId)
        ]);

        // Create sale items and update inventory
        foreach ($items as $item) {
            SalesItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['product']->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price'],
                'options' => $item['options'] ?? null,
            ]);

            // Note: Inventory (ingredients) managed separately in ingredients system
        }

        return response()->json([
            'success' => true,
            'message' => 'Sale completed successfully!',
            'redirect_url' => route('pos.receipt', $sale->id)
        ]);
        } catch (\Exception $e) {
            Log::error('POS Sale Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while processing the sale: ' . $e->getMessage()
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
     * Uses fixed pricing: use the price from the selected option (typically Size).
     */
    private function getUnitPrice(Product $product, $selectedOptions)
    {
        $base = $product->price;

        $options = $product->options ?? [];
        if (empty($options) || empty($selectedOptions) || !is_array($selectedOptions)) {
            return $base;
        }

        foreach ($options as $opt) {
            $optName = $opt['name'] ?? null;
            $values = $opt['values'] ?? [];
            if (!$optName) continue;

            // selected option value for this option name
            if (!array_key_exists($optName, $selectedOptions)) continue;
            $selectedVal = $selectedOptions[$optName];

            // find matching value in option definitions and use fixed price
            foreach ($values as $v) {
                if (is_array($v) || is_object($v)) {
                    $vArr = (array) $v;
                    $label = $vArr['label'] ?? $vArr['value'] ?? $vArr['name'] ?? null;
                    if ($label == $selectedVal && isset($vArr['price'])) {
                        $fixedPrice = (float) $vArr['price'];
                        // Use fixed price if it's greater than 0, otherwise fallback to base
                        if ($fixedPrice > 0) {
                            return $fixedPrice;
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

        return $base;
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
