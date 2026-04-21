<?php

namespace App\Http\Controllers;

use App\Models\BranchSession;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\PaymongoTransaction;
use App\Models\Sale;
use App\Models\SalesItem;
use App\Models\User;
use App\Events\SaleCompleted;
use App\Services\InventorySyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(403);
        }

        if ($user->isAdmin()) {
            abort(403, 'Admin users do not have access to the POS system.');
        }

        $branchSession = null;
        if ($user->branch_id) {
            $branchSession = BranchSession::getActiveSessionForUser($user->id);
            if (!$branchSession) {
                $branchSession = BranchSession::startSession($user->branch_id, $user->id);
            }
        }

        $categories = Category::where('is_active', true)->with('products')->get();
        $branchId = $user->branch_id;
        
        // Get products with current inventory for this branch
        $products = Product::where('is_active', true)
            ->with(['category', 'inventory' => function($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            }, 'ingredients'])
            ->get();

        // Pre-compute availability for each product
        foreach ($products as $product) {
            if ($product->product_type === 'composite') {
                // Composite products: check ingredient availability
                $availability = $this->inventorySyncService->checkProductAvailability($product, $branchId, 1);
                $product->is_available = $availability['available'];
                $product->missing_ingredients = $availability['missing_ingredients'] ?? [];
                $product->stock_display = $availability['available'] ? 'In Stock' : 'Missing Ingredients';
            } else {
                // Direct products: check product inventory stock
                $qty = $product->inventory->first()->quantity ?? 0;
                $product->is_available = $qty > 0;
                $product->direct_stock = $qty;
                $product->stock_display = $qty > 0 ? $qty . 'pcs' : 'Out of Stock';
            }
        }

        $paymongoConfigured = !empty(config('services.paymongo.secret_key'));

        return view('pos.index', compact('categories', 'products', 'paymongoConfigured', 'branchSession'));
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
            'payment_method' => 'required|in:cash,gcash',
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
            'receipt_number' => $this->generateReceiptNumber($branchId),
            'inventory_synced' => false
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
        $syncResult = $this->inventorySyncService->processSaleDeductions($sale);
        
        if (!$syncResult['success']) {
            // Log but don't fail the sale - inventory sync errors are logged
            Log::warning('Inventory sync had issues for sale #' . $sale->id, $syncResult['errors']);
        }

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
            'sale_id' => $sale->id,
            'redirect_url' => route('pos.receipt', $sale->id),
            'print_url' => route('pos.receipt.print', $sale->id),
            'direct_print_url' => route('pos.receipt.direct-print', $sale->id),
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
     * Create a PayMongo GCash source and return checkout URL for QR generation.
     */
    public function createGcashQr(Request $request)
    {
        try {
            if (is_string($request->input('items'))) {
                $decoded = json_decode($request->input('items'), true);
                $request->merge(['items' => $decoded]);
            }

            $validator = Validator::make($request->all(), [
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.options' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed: ' . $validator->errors()->first(),
                ], 422);
            }

            $secretKey = config('services.paymongo.secret_key');
            if (empty($secretKey)) {
                return response()->json([
                    'success' => false,
                    'error' => 'GCash is not configured yet. Please set PAYMONGO_SECRET_KEY in your .env file.',
                ], 500);
            }

            $branchId = Auth::user()->branch_id;
            $subtotal = 0;

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
                        'error' => "Cannot sell {$product->name}: {$reason}",
                    ], 400);
                }

                $unitPrice = $this->getUnitPrice($product, $item['options'] ?? null);
                $subtotal += $unitPrice * $item['quantity'];
            }

            $totalAmount = $subtotal;
            $amountInCentavos = (int) round($totalAmount * 100);
            $baseUrl = rtrim(config('services.paymongo.base_url', 'https://api.paymongo.com/v1'), '/');

            $response = Http::withBasicAuth($secretKey, '')
                ->acceptJson()
                ->post($baseUrl . '/sources', [
                    'data' => [
                        'attributes' => [
                            'amount' => $amountInCentavos,
                            'currency' => 'PHP',
                            'type' => 'gcash',
                            'redirect' => [
                                'success' => route('pos.index'),
                                'failed' => route('pos.index'),
                            ],
                            'billing' => [
                                'name' => Auth::user()->name,
                                'email' => Auth::user()->email,
                            ],
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                $errorMessage = $response->json('errors.0.detail')
                    ?? $response->json('errors.0.title')
                    ?? 'Failed to create GCash source.';

                Log::warning('PayMongo create source failed', ['response' => $response->json()]);

                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                ], 400);
            }

            $sourceData = $response->json('data');
            $checkoutUrl = data_get($sourceData, 'attributes.redirect.checkout_url');
            $sourceId = data_get($sourceData, 'id');
            $expiresAt = data_get($sourceData, 'attributes.created_at');
            $expiresAt = $expiresAt ? now()->parse($expiresAt)->addHours(1) : now()->addHours(1);

            if (empty($checkoutUrl)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unable to get GCash checkout URL from PayMongo.',
                ], 500);
            }

            PaymongoTransaction::updateOrCreate(
                ['source_id' => $sourceId],
                [
                    'branch_id' => (int) Auth::user()->branch_id,
                    'user_id' => (int) Auth::id(),
                    'payment_method' => 'gcash',
                    'amount' => $totalAmount,
                    'currency' => 'PHP',
                    'status' => data_get($sourceData, 'attributes.status', 'pending'),
                    'items_snapshot' => $request->input('items'),
                    'source_payload' => $sourceData,
                    'expires_at' => $expiresAt,
                    'error_message' => null,
                ]
            );

            return response()->json([
                'success' => true,
                'source_id' => $sourceId,
                'checkout_url' => $checkoutUrl,
                'status' => data_get($sourceData, 'attributes.status', 'pending'),
                'amount' => $totalAmount,
            ]);
        } catch (\Exception $e) {
            Log::error('Create GCash QR Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Unable to create GCash payment right now. Please try again.',
            ], 500);
        }
    }

    /**
     * Check PayMongo GCash source status and finalize sale when paid.
     */
    public function checkGcashStatus(Request $request)
    {
        try {
            if (is_string($request->input('items'))) {
                $decoded = json_decode($request->input('items'), true);
                $request->merge(['items' => $decoded]);
            }

            $validator = Validator::make($request->all(), [
                'source_id' => 'required|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.options' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed: ' . $validator->errors()->first(),
                ], 422);
            }

            $secretKey = config('services.paymongo.secret_key');
            if (empty($secretKey)) {
                return response()->json([
                    'success' => false,
                    'error' => 'GCash is not configured yet. Please set PAYMONGO_SECRET_KEY in your .env file.',
                ], 500);
            }

            $sourceId = $request->input('source_id');
            $transaction = PaymongoTransaction::where('source_id', $sourceId)->first();
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'error' => 'GCash transaction not found. Please generate a new QR code.',
                ], 404);
            }

            if ($transaction->sale_id && $transaction->status === 'paid') {
                return response()->json([
                    'success' => true,
                    'status' => 'paid',
                    'message' => 'GCash payment already confirmed and sale already processed.',
                    'sale_id' => $transaction->sale_id,
                    'redirect_url' => route('pos.receipt', $transaction->sale_id),
                    'print_url' => route('pos.receipt.print', $transaction->sale_id),
                    'direct_print_url' => route('pos.receipt.direct-print', $transaction->sale_id),
                ]);
            }

            $baseUrl = rtrim(config('services.paymongo.base_url', 'https://api.paymongo.com/v1'), '/');
            $sourceResponse = Http::withBasicAuth($secretKey, '')
                ->acceptJson()
                ->get($baseUrl . '/sources/' . $sourceId);

            if (!$sourceResponse->successful()) {
                $errorMessage = $sourceResponse->json('errors.0.detail')
                    ?? $sourceResponse->json('errors.0.title')
                    ?? 'Failed to check GCash payment status.';

                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                ], 400);
            }

            $sourceData = $sourceResponse->json('data');
            $sourceStatus = data_get($sourceData, 'attributes.status', 'pending');
            $amountCentavos = (int) data_get($sourceData, 'attributes.amount', 0);

            $transaction->update([
                'status' => $sourceStatus,
                'source_payload' => $sourceData,
                'error_message' => null,
            ]);

            if (in_array($sourceStatus, ['pending'])) {
                return response()->json([
                    'success' => false,
                    'status' => 'pending',
                    'message' => 'Waiting for customer payment confirmation.',
                ]);
            }

            if (in_array($sourceStatus, ['cancelled', 'failed', 'expired'])) {
                return response()->json([
                    'success' => false,
                    'status' => $sourceStatus,
                    'error' => 'GCash payment was not completed. Please create a new QR and try again.',
                ], 400);
            }

            if ($sourceStatus === 'chargeable') {
                $paymentKey = 'gcash_payment_' . $sourceId;
                $paymentId = Cache::get($paymentKey);

                if (empty($paymentId)) {
                    $paymentResponse = Http::withBasicAuth($secretKey, '')
                        ->acceptJson()
                        ->post($baseUrl . '/payments', [
                            'data' => [
                                'attributes' => [
                                    'amount' => $amountCentavos,
                                    'currency' => 'PHP',
                                    'source' => [
                                        'id' => $sourceId,
                                        'type' => 'source',
                                    ],
                                    'description' => 'POS GCash payment for branch ' . Auth::user()->branch_id,
                                ],
                            ],
                        ]);

                    if (!$paymentResponse->successful()) {
                        $errorMessage = $paymentResponse->json('errors.0.detail')
                            ?? $paymentResponse->json('errors.0.title')
                            ?? 'Unable to capture GCash payment.';

                        Log::warning('PayMongo create payment failed', ['response' => $paymentResponse->json()]);

                        return response()->json([
                            'success' => false,
                            'status' => 'failed',
                            'error' => $errorMessage,
                        ], 400);
                    }

                    $paymentId = data_get($paymentResponse->json(), 'data.id');
                    Cache::put($paymentKey, $paymentId, now()->addHours(2));

                    $transaction->update([
                        'payment_id' => $paymentId,
                        'payment_payload' => $paymentResponse->json('data'),
                        'status' => data_get($paymentResponse->json(), 'data.attributes.status', 'paid'),
                        'paid_at' => now(),
                    ]);
                }
            }

            $lockKey = 'gcash_sale_lock_' . $sourceId;
            if (!Cache::add($lockKey, true, 120)) {
                return response()->json([
                    'success' => false,
                    'status' => 'processing',
                    'message' => 'Payment confirmed. Finalizing sale, please wait...',
                ]);
            }

            try {
                $freshTransaction = PaymongoTransaction::where('source_id', $sourceId)->first();
                if ($freshTransaction && $freshTransaction->sale_id && $freshTransaction->status === 'paid') {
                    return response()->json([
                        'success' => true,
                        'status' => 'paid',
                        'message' => 'GCash payment completed and sale already processed successfully!',
                        'sale_id' => $freshTransaction->sale_id,
                        'redirect_url' => route('pos.receipt', $freshTransaction->sale_id),
                        'print_url' => route('pos.receipt.print', $freshTransaction->sale_id),
                        'direct_print_url' => route('pos.receipt.direct-print', $freshTransaction->sale_id),
                        'low_stock_alerts' => [],
                        'inventory_synced' => true,
                    ]);
                }

                $amountPaid = $amountCentavos / 100;
                $saleRequest = new Request([
                    'items' => $request->input('items'),
                    'payment_method' => 'gcash',
                    'amount_paid' => $amountPaid,
                ]);

                $saleRequest->setUserResolver(fn () => Auth::user());
                $saleResponse = $this->processSale($saleRequest);
                $salePayload = $saleResponse->getData(true);

                if (!($salePayload['success'] ?? false)) {
                    return response()->json([
                        'success' => false,
                        'status' => 'processing_failed',
                        'error' => $salePayload['error'] ?? 'Payment is confirmed but sale processing failed. Please contact admin.',
                    ], 500);
                }

                DB::transaction(function () use ($sourceId, $salePayload, $sourceData) {
                    $tx = PaymongoTransaction::where('source_id', $sourceId)->lockForUpdate()->first();
                    if (!$tx) {
                        return;
                    }

                    $tx->update([
                        'sale_id' => $salePayload['sale_id'] ?? null,
                        'status' => 'paid',
                        'paid_at' => $tx->paid_at ?? now(),
                        'source_payload' => $sourceData,
                        'error_message' => null,
                    ]);
                });

                $result = [
                    'success' => true,
                    'status' => 'paid',
                    'message' => 'GCash payment completed and sale processed successfully!',
                    'sale_id' => $salePayload['sale_id'] ?? null,
                    'redirect_url' => $salePayload['redirect_url'] ?? null,
                    'print_url' => $salePayload['print_url'] ?? null,
                    'direct_print_url' => $salePayload['direct_print_url'] ?? null,
                    'low_stock_alerts' => $salePayload['low_stock_alerts'] ?? [],
                    'inventory_synced' => $salePayload['inventory_synced'] ?? false,
                ];

                Cache::put('gcash_sale_' . $sourceId, $result, now()->addHours(2));
                return response()->json($result);
            } finally {
                Cache::forget($lockKey);
            }
        } catch (\Exception $e) {
            Log::error('Check GCash Status Error: ' . $e->getMessage());

            if (!empty($request->input('source_id'))) {
                PaymongoTransaction::where('source_id', $request->input('source_id'))
                    ->update(['error_message' => $e->getMessage()]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Unable to verify GCash status right now. Please try again.',
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
            if (!$user instanceof User) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

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
     * Show printable receipt in a standalone page (opens in new tab).
     */
    public function printReceipt(Sale $sale)
    {
        $sale->load(['salesItems.product', 'user', 'branch']);
        return view('pos.receipt-print', compact('sale'));
    }

    /**
     * Direct print receipt - returns receipt HTML optimized for silent/direct printing
     * to thermal receipt printers (iframe-based printing without dialog).
     */
    public function directPrintReceipt(Sale $sale)
    {
        $sale->load(['salesItems.product', 'user', 'branch']);
        return view('pos.receipt-direct-print', compact('sale'));
    }

    /**
     * Get raw ESC/POS receipt data for direct thermal printer communication.
     * Returns structured data that can be used with ESC/POS JavaScript libraries.
     */
    public function getRawReceiptData(Sale $sale)
    {
        $sale->load(['salesItems.product', 'user', 'branch']);
        
        $items = $sale->salesItems->map(function ($item) {
            $options = '';
            if (!empty($item->options) && is_array($item->options)) {
                $optParts = [];
                foreach ($item->options as $name => $value) {
                    $optParts[] = "{$name}: {$value}";
                }
                $options = implode(', ', $optParts);
            }
            
            return [
                'name' => $item->product->name ?? 'Product',
                'options' => $options,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ];
        });

        return response()->json([
            'success' => true,
            'receipt' => [
                'store_name' => config('app.name'),
                'branch_name' => $sale->branch->name ?? 'Main Branch',
                'receipt_number' => $sale->receipt_number,
                'date' => $sale->created_at->format('M d, Y h:i A'),
                'items' => $items,
                'subtotal' => $sale->subtotal,
                'tax_amount' => $sale->tax_amount,
                'discount_amount' => $sale->discount_amount,
                'total_amount' => $sale->total_amount,
                'payment_method' => ucfirst($sale->payment_method),
                'amount_paid' => $sale->amount_paid,
                'change_amount' => $sale->change_amount,
                'cashier' => $sale->user->name ?? 'Staff',
            ]
        ]);
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
        $location = $branch
            ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $branch->name))
            : 'LOCATION';
        $date = now()->format('Ymd');

        // Build a Location-Date-random4 format and retry on rare collisions.
        $attempts = 0;
        do {
            $random = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $receiptNumber = $location . '-' . $date . '-' . $random;
            $exists = Sale::where('receipt_number', $receiptNumber)->exists();
            $attempts++;
        } while ($exists && $attempts < 10);

        return $receiptNumber;
    }

    /**
     * Get live product availability data for POS polling
     */
    public function liveData()
    {
        $branchId = Auth::user()->branch_id;
        
        $products = Product::where('is_active', true)
            ->with(['inventory' => function($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            }, 'ingredients'])
            ->get();

        $productData = [];
        foreach ($products as $product) {
            if ($product->product_type === 'composite') {
                $availability = $this->inventorySyncService->checkProductAvailability($product, $branchId, 1);
                $productData[$product->id] = [
                    'product_type' => 'composite',
                    'is_available' => $availability['available'],
                    'stock' => $availability['available'] ? 9999 : 0,
                    'stock_display' => $availability['available'] ? 'Available' : 'Unavailable',
                    'missing_ingredients' => $availability['missing_ingredients'] ?? [],
                ];
            } else {
                $qty = $product->inventory->first()->quantity ?? 0;
                $productData[$product->id] = [
                    'product_type' => 'direct',
                    'is_available' => $qty > 0,
                    'stock' => (int) $qty,
                    'stock_display' => $qty > 0 ? $qty . 'pcs' : 'Out of Stock',
                    'missing_ingredients' => [],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'products' => $productData,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
