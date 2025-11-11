<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\SalesItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,gcash',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $branchId = Auth::user()->branch_id;
        $subtotal = 0;
        $items = [];

        // Calculate totals and validate inventory
        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $inventory = Inventory::where('branch_id', $branchId)
                ->where('product_id', $product->id)
                ->first();

            if (!$inventory || $inventory->quantity < $item['quantity']) {
                return back()->withErrors(['error' => "Insufficient stock for {$product->name}"]);
            }

            $lineTotal = $product->price * $item['quantity'];
            $subtotal += $lineTotal;

            $items[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'total_price' => $lineTotal
            ];
        }

        $taxAmount = 0; // You can implement tax calculation here
        $discountAmount = 0; // You can implement discount logic here
        $totalAmount = $subtotal + $taxAmount - $discountAmount;
        $changeAmount = max(0, $request->amount_paid - $totalAmount);

        if ($request->amount_paid < $totalAmount) {
            return back()->withErrors(['error' => 'Insufficient payment amount']);
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
                'total_price' => $item['total_price']
            ]);

            // Update inventory
            $inventory = Inventory::where('branch_id', $branchId)
                ->where('product_id', $item['product']->id)
                ->first();
            $inventory->decrement('quantity', $item['quantity']);
        }

        return redirect()->route('pos.receipt', $sale->id)->with('success', 'Sale completed successfully!');
    }

    public function showReceipt(Sale $sale)
    {
        $sale->load(['salesItems.product', 'user', 'branch']);
        return view('pos.receipt', compact('sale'));
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
