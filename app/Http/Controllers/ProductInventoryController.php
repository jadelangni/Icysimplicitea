<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Category;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductInventoryController extends Controller
{
    /**
     * Display the product inventory management page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $branches = Branch::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        
        // Get products with inventory for all branches
        $products = Product::with(['category', 'inventory.branch'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Get low stock alerts across all branches (for admin)
        $lowStockAlerts = [];
        if ($user->isAdmin()) {
            $lowStockAlerts = Inventory::with(['product', 'branch'])
                ->whereRaw('quantity <= min_stock_level')
                ->orderBy('quantity', 'asc')
                ->get();
        }
        
        // Get ingredients for the Ingredients tab with aggregated inventory data
        $allBranches = Branch::all();
        $ingredients = Ingredient::with('inventories')->orderBy('name')->get()->map(function($ingredient) use ($allBranches) {
            // Aggregate inventory across all branches
            $totalQuantity = 0;
            $totalMinStock = 0;
            $branchCount = 0;
            $hasLowStock = false;
            $hasOutOfStock = false;
            $branchData = [];
            
            foreach ($allBranches as $branch) {
                $inv = $ingredient->inventories->where('branch_id', $branch->id)->first();
                $qty = $inv ? (float)$inv->quantity : 0;
                $minStock = $inv ? (float)$inv->min_stock_level : 10;
                
                $totalQuantity += $qty;
                $totalMinStock += $minStock;
                $branchCount++;
                
                $isOut = $qty <= 0;
                $isLow = !$isOut && $qty <= $minStock;
                
                if ($isOut) $hasOutOfStock = true;
                if ($isLow) $hasLowStock = true;
                
                $branchData[$branch->id] = [
                    'inventory_id' => $inv ? $inv->id : null,
                    'quantity' => $qty,
                    'min_stock_level' => $minStock,
                    'is_low_stock' => $isLow,
                    'is_out_of_stock' => $isOut,
                    'branch_name' => $branch->name,
                ];
            }
            
            // Determine overall status
            if ($hasOutOfStock) {
                $status = 'Out of Stock';
                $statusColor = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400';
            } elseif ($hasLowStock) {
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
                'quantity' => $totalQuantity, // Total across all branches
                'min_stock_level' => $totalMinStock / max($branchCount, 1), // Average threshold
                'status' => $status,
                'status_color' => $statusColor,
                'is_low_stock' => $hasLowStock && !$hasOutOfStock,
                'is_out_of_stock' => $hasOutOfStock,
                'branches' => $branchData,
                'updated_at' => $ingredient->updated_at,
            ];
        });
        
        // Calculate counts based on aggregated data
        $lowStockIngredients = $ingredients->filter(fn($i) => $i->is_low_stock || $i->is_out_of_stock)->count();
        $inStockIngredients = $ingredients->filter(fn($i) => !$i->is_low_stock && !$i->is_out_of_stock)->count();
        $outOfStockIngredients = $ingredients->filter(fn($i) => $i->is_out_of_stock)->count();
        
        // Determine active tab from query param
        $activeTab = $request->get('tab', 'products');
        
        // Return with no-cache headers to ensure fresh data
        return response()
            ->view('product-inventory.index', compact(
                'products', 
                'branches', 
                'categories', 
                'lowStockAlerts', 
                'ingredients', 
                'lowStockIngredients',
                'inStockIngredients',
                'outOfStockIngredients',
                'allBranches',
                'activeTab'
            ))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Get product details with all branch inventory
     */
    public function show(Product $product)
    {
        $branches = Branch::where('is_active', true)->get();
        
        // Get inventory for each branch
        $branchInventory = [];
        foreach ($branches as $branch) {
            $inventory = Inventory::where('product_id', $product->id)
                ->where('branch_id', $branch->id)
                ->first();
            
            $branchInventory[] = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'quantity' => $inventory ? $inventory->quantity : 0,
                'min_stock_level' => $inventory ? $inventory->min_stock_level : 10,
                'is_low_stock' => $inventory ? $inventory->isLowStock() : false,
            ];
        }
        
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category_id' => $product->category_id,
                'category_name' => $product->category->name ?? 'Uncategorized',
                'is_active' => $product->is_active,
                'image' => $product->image,
            ],
            'branch_inventory' => $branchInventory,
        ]);
    }

    /**
     * Update global product price (affects all branches)
     */
    public function updatePrice(Request $request, Product $product)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $updateData = ['price' => $validated['price']];
        
        if (!empty($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }
        if (isset($validated['description'])) {
            $updateData['description'] = $validated['description'];
        }
        if (!empty($validated['category_id'])) {
            $updateData['category_id'] = $validated['category_id'];
        }

        $product->update($updateData);

        // Return JSON for AJAX calls
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product price updated globally!',
                'product' => $product->fresh(),
            ]);
        }

        return redirect()->back()->with('success', 'Product price updated across all branches!');
    }

    /**
     * Update stock for a specific branch
     */
    public function updateStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
        ]);

        $inventory = Inventory::updateOrCreate(
            [
                'product_id' => $product->id,
                'branch_id' => $validated['branch_id'],
            ],
            [
                'quantity' => $validated['quantity'],
                'min_stock_level' => $validated['min_stock_level'] ?? 10,
            ]
        );

        // Check if this triggered a low stock alert
        $isLowStock = $inventory->isLowStock();
        $branch = Branch::find($validated['branch_id']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Stock updated for {$branch->name}!",
                'inventory' => $inventory,
                'is_low_stock' => $isLowStock,
            ]);
        }

        return redirect()->back()->with('success', "Stock updated for {$branch->name}!");
    }

    /**
     * Bulk update stock for all branches
     */
    public function updateAllBranchStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'stocks' => 'required|array',
            'stocks.*.branch_id' => 'required|exists:branches,id',
            'stocks.*.quantity' => 'required|numeric|min:0',
            'stocks.*.min_stock_level' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['stocks'] as $stock) {
                Inventory::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'branch_id' => $stock['branch_id'],
                    ],
                    [
                        'quantity' => $stock['quantity'],
                        'min_stock_level' => $stock['min_stock_level'] ?? 10,
                    ]
                );
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'All branch stocks updated successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stocks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get low stock alerts for dashboard
     */
    public function getLowStockAlerts(Request $request)
    {
        $user = Auth::user();
        $branchId = $request->get('branch_id');
        
        $query = Inventory::with(['product', 'branch'])
            ->whereRaw('quantity <= min_stock_level')
            ->orderBy('quantity', 'asc');
        
        // Filter by branch if specified
        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }
        
        // Non-admin users can only see their branch
        if (!$user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }
        
        $alerts = $query->limit(10)->get()->map(function ($inventory) {
            return [
                'id' => $inventory->id,
                'product_name' => $inventory->product->name ?? 'Unknown',
                'branch_id' => $inventory->branch_id,
                'branch_name' => $inventory->branch->name ?? 'Unknown',
                'current_stock' => $inventory->quantity,
                'min_stock_level' => $inventory->min_stock_level,
                'urgency' => $inventory->quantity <= 0 ? 'critical' : ($inventory->quantity <= $inventory->min_stock_level / 2 ? 'high' : 'medium'),
            ];
        });
        
        return response()->json([
            'success' => true,
            'alerts' => $alerts,
            'count' => $alerts->count(),
        ]);
    }

    /**
     * Get sync status for all products
     */
    public function getSyncStatus()
    {
        $branches = Branch::where('is_active', true)->get();
        $branchCount = $branches->count();
        
        $products = Product::where('is_active', true)->get()->map(function ($product) use ($branches, $branchCount) {
            $inventoryCount = Inventory::where('product_id', $product->id)->count();
            $lowStockBranches = Inventory::where('product_id', $product->id)
                ->whereRaw('quantity <= min_stock_level')
                ->with('branch')
                ->get();
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'is_synced' => $inventoryCount >= $branchCount,
                'sync_count' => $inventoryCount,
                'total_branches' => $branchCount,
                'low_stock_branches' => $lowStockBranches->map(function ($inv) {
                    return [
                        'branch_id' => $inv->branch_id,
                        'branch_name' => $inv->branch->name ?? 'Unknown',
                        'quantity' => $inv->quantity,
                    ];
                }),
            ];
        });
        
        return response()->json([
            'success' => true,
            'products' => $products,
        ]);
    }
}
