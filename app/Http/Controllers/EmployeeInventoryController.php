<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Category;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeInventoryController extends Controller
{
    /**
     * Display the inventory overview for cashiers (read-only).
     * Reuses the same view as admin's product-inventory but with $readOnly = true.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $branch = $user->branch;

        if (!$branch) {
            abort(403, 'You are not assigned to any branch.');
        }

        $branches = collect([$branch]);
        $selectedBranch = $branch;
        $selectedBranchId = $branch->id;
        $displayBranches = collect([$branch]);
        $categories = Category::where('is_active', true)->get();

        // Products - same query as admin ProductInventoryController
        $products = Product::with(['category', 'inventory.branch', 'ingredients.inventories'])
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('product_type')
                    ->orWhere('product_type', Product::TYPE_DIRECT);
            })
            ->orderBy('name')
            ->get();

        // Low stock alerts for this branch
        $lowStockAlerts = Inventory::with(['product', 'branch'])
            ->whereHas('product', function ($query) {
                $query->where('is_active', true)
                    ->where(function ($innerQuery) {
                        $innerQuery->whereNull('product_type')
                            ->orWhere('product_type', Product::TYPE_DIRECT);
                    });
            })
            ->where('branch_id', $branch->id)
            ->whereRaw('quantity <= min_stock_level')
            ->orderBy('quantity', 'asc')
            ->get();

        // Ingredients - same format as admin
        $allBranches = $branches;
        $ingredients = Ingredient::with('inventories')->orderBy('name')->get()->map(function($ingredient) use ($allBranches, $selectedBranchId, $selectedBranch) {
            $branchData = [];

            foreach ($allBranches as $branch) {
                $inv = $ingredient->inventories->where('branch_id', $branch->id)->first();
                $qty = $inv ? (float)$inv->quantity : 0;
                $minStock = $inv ? (float)$inv->min_stock_level : 10;
                $isOut = $qty <= 0;
                $isLow = !$isOut && $qty <= $minStock;

                $branchData[$branch->id] = [
                    'inventory_id' => $inv ? $inv->id : null,
                    'quantity' => $qty,
                    'min_stock_level' => $minStock,
                    'is_low_stock' => $isLow,
                    'is_out_of_stock' => $isOut,
                    'branch_name' => $branch->name,
                ];
            }

            $selectedBranchData = $branchData[$selectedBranchId] ?? [
                'quantity' => 0, 'min_stock_level' => 10,
                'is_low_stock' => false, 'is_out_of_stock' => true,
            ];

            $qty = (float) $selectedBranchData['quantity'];
            $minStock = (float) $selectedBranchData['min_stock_level'];
            $isOutOfStock = (bool) $selectedBranchData['is_out_of_stock'];
            $isLowStock = (bool) $selectedBranchData['is_low_stock'];

            if ($isOutOfStock) {
                $status = 'Out of Stock';
                $statusColor = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400';
            } elseif ($isLowStock) {
                $status = 'Low Stock';
                $statusColor = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400';
            } else {
                $status = 'In Stock';
                $statusColor = 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400';
            }

            return (object)[
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'description' => $ingredient->description,
                'unit' => $ingredient->unit,
                'is_active' => $ingredient->is_active,
                'quantity' => $qty,
                'min_stock_level' => $minStock,
                'status' => $status,
                'status_color' => $statusColor,
                'is_low_stock' => $isLowStock,
                'is_out_of_stock' => $isOutOfStock,
                'branches' => $branchData,
                'selected_branch_id' => $selectedBranchId,
                'selected_branch_name' => $selectedBranch->name,
                'updated_at' => $ingredient->updated_at,
            ];
        });

        $lowStockIngredients = $ingredients->filter(fn($i) => $i->is_low_stock || $i->is_out_of_stock)->count();
        $inStockIngredients = $ingredients->filter(fn($i) => !$i->is_low_stock && !$i->is_out_of_stock)->count();
        $outOfStockIngredients = $ingredients->filter(fn($i) => $i->is_out_of_stock)->count();

        $activeTab = $request->get('tab', 'products');
        $readOnly = true;

        return response()
            ->view('product-inventory.index', compact(
                'products', 'branches', 'categories', 'lowStockAlerts',
                'ingredients', 'lowStockIngredients', 'inStockIngredients',
                'outOfStockIngredients', 'allBranches', 'activeTab',
                'selectedBranch', 'selectedBranchId', 'displayBranches',
                'readOnly'
            ))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
