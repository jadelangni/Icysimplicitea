<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeInventoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $branch = $user->branch;

        if (!$branch) {
            abort(403, 'You are not assigned to any branch.');
        }

        // Products with inventory for the employee's branch only
        $products = Product::with(['category', 'ingredients.inventories' => function ($q) use ($branch) {
            $q->where('branch_id', $branch->id);
        }])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($branch) {
                $inventory = Inventory::where('product_id', $product->id)
                    ->where('branch_id', $branch->id)
                    ->first();

                $hasIngredients = $product->ingredients->count() > 0;
                $quantity = $inventory ? $inventory->quantity : 0;
                $minStock = $inventory ? $inventory->min_stock_level : 10;

                // For composite products, check ingredient stock
                $ingredientLow = false;
                $ingredientOut = false;
                if ($hasIngredients) {
                    foreach ($product->ingredients as $ingredient) {
                        $ingInv = $ingredient->inventories->where('branch_id', $branch->id)->first();
                        $ingQty = $ingInv ? (float) $ingInv->quantity : 0;
                        $ingMin = $ingInv ? (float) $ingInv->min_stock_level : 10;
                        if ($ingQty <= 0) $ingredientOut = true;
                        elseif ($ingQty <= $ingMin) $ingredientLow = true;
                    }
                }

                $isDirectProduct = !$hasIngredients;

                if ($isDirectProduct) {
                    $isLow = $quantity > 0 && $quantity <= $minStock;
                    $isOut = $quantity <= 0;
                } else {
                    $isLow = $ingredientLow && !$ingredientOut;
                    $isOut = $ingredientOut;
                }

                if ($isOut) {
                    $status = 'Out of Stock';
                    $statusColor = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400';
                } elseif ($isLow) {
                    $status = 'Low Stock';
                    $statusColor = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400';
                } else {
                    $status = 'In Stock';
                    $statusColor = 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400';
                }

                return (object) [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name ?? 'Uncategorized',
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'min_stock' => $minStock,
                    'is_direct' => $isDirectProduct,
                    'status' => $status,
                    'status_color' => $statusColor,
                    'is_low' => $isLow,
                    'is_out' => $isOut,
                ];
            });

        // Ingredients with inventory for the employee's branch
        $ingredients = Ingredient::with(['inventories' => function ($q) use ($branch) {
            $q->where('branch_id', $branch->id);
        }])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($ingredient) use ($branch) {
                $inv = $ingredient->inventories->where('branch_id', $branch->id)->first();
                $qty = $inv ? (float) $inv->quantity : 0;
                $minStock = $inv ? (float) $inv->min_stock_level : 10;

                $isOut = $qty <= 0;
                $isLow = !$isOut && $qty <= $minStock;

                if ($isOut) {
                    $status = 'Out of Stock';
                    $statusColor = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400';
                } elseif ($isLow) {
                    $status = 'Low Stock';
                    $statusColor = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400';
                } else {
                    $status = 'In Stock';
                    $statusColor = 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400';
                }

                return (object) [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'unit' => $ingredient->unit,
                    'quantity' => $qty,
                    'min_stock' => $minStock,
                    'status' => $status,
                    'status_color' => $statusColor,
                    'is_low' => $isLow,
                    'is_out' => $isOut,
                ];
            });

        // Stats
        $totalProducts = $products->count();
        $lowStockProducts = $products->filter(fn($p) => $p->is_low)->count();
        $outOfStockProducts = $products->filter(fn($p) => $p->is_out)->count();
        $totalIngredients = $ingredients->count();
        $lowStockIngredients = $ingredients->filter(fn($i) => $i->is_low)->count();
        $outOfStockIngredients = $ingredients->filter(fn($i) => $i->is_out)->count();

        // Low stock alerts (products + ingredients combined)
        $lowStockAlerts = $products->filter(fn($p) => $p->is_low || $p->is_out)
            ->merge($ingredients->filter(fn($i) => $i->is_low || $i->is_out));

        $activeTab = $request->get('tab', 'products');

        return view('employee-inventory.index', compact(
            'branch',
            'products',
            'ingredients',
            'totalProducts',
            'lowStockProducts',
            'outOfStockProducts',
            'totalIngredients',
            'lowStockIngredients',
            'outOfStockIngredients',
            'lowStockAlerts',
            'activeTab'
        ));
    }
}
