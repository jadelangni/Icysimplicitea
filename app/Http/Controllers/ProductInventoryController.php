<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductInventoryExport;
use App\Exports\IngredientInventoryExport;

class ProductInventoryController extends Controller
{
    /**
     * Display the product inventory management page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $branches = Branch::where('is_active', true)->get();
        $selectedParam = $request->get('branch_id', null);

        if ($user->isAdmin() && ($selectedParam === null || $selectedParam === 'all') && $branches->isNotEmpty()) {
            return redirect()->route('product-inventory.index', array_merge(
                $request->except('branch_id'),
                ['branch_id' => $branches->first()->id]
            ));
        }

        if ($user->isAdmin()) {
            // Admin inventory uses one consistent branch-specific UI.
            $selectedBranchId = ($selectedParam !== null && $selectedParam !== 'all') ? (int) $selectedParam : null;
            $selectedBranch = $branches->firstWhere('id', $selectedBranchId) ?? $branches->first();
            $selectedBranchId = $selectedBranch?->id;
            $displayBranches = $selectedBranch ? collect([$selectedBranch]) : collect();
        } else {
            // Non-admin users see only their assigned branch
            $selectedBranchId = $user->branch_id;
            $selectedBranch = $branches->firstWhere('id', $selectedBranchId) ?? Branch::find($selectedBranchId);
            $displayBranches = $selectedBranch ? collect([$selectedBranch]) : collect();
        }
        $categories = Category::where('is_active', true)->get();
        
        // Raw Product tab only shows ready-for-resale (direct) products.
        $products = Product::with(['category', 'inventory.branch', 'ingredients.inventories'])
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('product_type')
                    ->orWhere('product_type', Product::TYPE_DIRECT);
            })
            ->orderBy('name')
            ->get()
            ->sortBy(function ($product) use ($selectedBranchId) {
                $inventory = $product->inventory->firstWhere('branch_id', $selectedBranchId);
                $quantity = $inventory ? (float) $inventory->quantity : 0;
                $minStock = $inventory ? (float) $inventory->min_stock_level : 10;
                $priority = $quantity <= 0 ? 0 : ($quantity <= $minStock ? 1 : 2);

                return sprintf('%02d-%s', $priority, strtolower($product->name));
            })
            ->values();
        
        // Get low stock alerts across all branches (for admin)
        $lowStockAlerts = [];
        if ($user->isAdmin()) {
            $lowStockAlertsQuery = Inventory::with(['product', 'branch'])
                ->whereHas('product', function ($query) {
                    $query->where('is_active', true)
                        ->where(function ($innerQuery) {
                            $innerQuery->whereNull('product_type')
                                ->orWhere('product_type', Product::TYPE_DIRECT);
                        });
                })
                ->whereRaw('quantity <= min_stock_level')
                ->orderBy('quantity', 'asc');

            if ($selectedBranchId) {
                $lowStockAlertsQuery->where('branch_id', $selectedBranchId);
            }

            $lowStockAlerts = $lowStockAlertsQuery->get();
        }
        
        // Get ingredients for the Ingredients tab (display branch-specific values)
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
                'quantity' => 0,
                'min_stock_level' => 10,
                'is_low_stock' => false,
                'is_out_of_stock' => true,
            ];

            $selectedQuantity = (float) $selectedBranchData['quantity'];
            $selectedMinStock = (float) $selectedBranchData['min_stock_level'];
            $isOutOfStock = (bool) $selectedBranchData['is_out_of_stock'];
            $isLowStock = (bool) $selectedBranchData['is_low_stock'];
            
            // Determine status for selected branch
            if ($isOutOfStock) {
                $status = 'No Stock';
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
                'recipe_unit' => $ingredient->getRecipeUnit(),
                'recipe_units_per_inventory_unit' => (float) ($ingredient->recipe_units_per_inventory_unit ?: 1),
                'recipe_conversion_label' => $ingredient->recipe_conversion_label,
                'is_active' => $ingredient->is_active,
                'quantity' => $selectedQuantity,
                'min_stock_level' => $selectedMinStock,
                'status' => $status,
                'status_color' => $statusColor,
                'is_low_stock' => $isLowStock,
                'is_out_of_stock' => $isOutOfStock,
                'branches' => $branchData,
                'selected_branch_id' => $selectedBranchId,
                'selected_branch_name' => $selectedBranch?->name,
                'updated_at' => $ingredient->updated_at,
            ];
        })->sortBy(function ($ingredient) {
            $priority = $ingredient->is_out_of_stock ? 0 : ($ingredient->is_low_stock ? 1 : 2);

            return sprintf('%02d-%s', $priority, strtolower($ingredient->name));
        })->values();
        
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
                'activeTab',
                'selectedBranch',
                'selectedBranchId',
                'displayBranches'
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
        $product->load(['category', 'ingredients.inventories']);
        $branches = Branch::where('is_active', true)->get();
        $isCompositeProduct = $product->isCompositeProduct() || $product->ingredients->isNotEmpty();
        
        // Get inventory for each branch
        $branchInventory = [];
        foreach ($branches as $branch) {
            $inventory = Inventory::where('product_id', $product->id)
                ->where('branch_id', $branch->id)
                ->first();

            $ingredientIssueNames = [];
            $ingredientLowCount = 0;
            $ingredientOutCount = 0;

            if ($isCompositeProduct) {
                foreach ($product->ingredients as $ingredient) {
                    $ingredientInventory = $ingredient->inventories->firstWhere('branch_id', $branch->id);
                    $qty = $ingredientInventory ? (float) $ingredientInventory->quantity : 0;
                    $minStock = $ingredientInventory ? (float) $ingredientInventory->min_stock_level : 10;

                    if ($qty <= 0) {
                        $ingredientOutCount++;
                        $ingredientIssueNames[] = $ingredient->name;
                    } elseif ($qty <= $minStock) {
                        $ingredientLowCount++;
                        $ingredientIssueNames[] = $ingredient->name;
                    }
                }
            }
            
            $branchInventory[] = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'quantity' => $inventory ? $inventory->quantity : 0,
                'min_stock_level' => $inventory ? $inventory->min_stock_level : 10,
                'is_low_stock' => $inventory ? $inventory->isLowStock() : false,
                'has_low_ingredients' => ($ingredientLowCount + $ingredientOutCount) > 0,
                'ingredient_issue_count' => $ingredientLowCount + $ingredientOutCount,
                'ingredient_low_count' => $ingredientLowCount,
                'ingredient_out_count' => $ingredientOutCount,
                'ingredient_issue_names' => array_slice($ingredientIssueNames, 0, 4),
                'ingredient_total_count' => $product->ingredients->count(),
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
                'product_type' => $product->product_type,
                'is_direct' => $product->isDirectProduct(),
                'is_composite' => $isCompositeProduct,
                'ingredient_count' => $product->ingredients->count(),
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

        if (!$product->isDirectProduct()) {
            return response()->json([
                'success' => false,
                'message' => 'Composite products use ingredient inventory. Update stock in the Ingredients tab.',
            ], 422);
        }

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

        if (!$product->isDirectProduct()) {
            return response()->json([
                'success' => false,
                'message' => 'Composite products use ingredient inventory. Update stock in the Ingredients tab.',
            ], 422);
        }

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
        
        $productQuery = Inventory::with(['product', 'branch'])
            ->whereHas('product', function ($productQuery) {
                $productQuery->where('is_active', true)
                    ->where(function ($typeQuery) {
                        $typeQuery->whereNull('product_type')
                            ->orWhere('product_type', Product::TYPE_DIRECT);
                    });
            })
            ->whereRaw('quantity <= min_stock_level');

        $ingredientQuery = IngredientInventory::with(['ingredient', 'branch'])
            ->whereHas('ingredient', function ($ingredientQuery) {
                $ingredientQuery->where('is_active', true);
            })
            ->whereRaw('quantity <= min_stock_level');
        
        // Filter by branch if specified
        if ($branchId && $branchId !== 'all') {
            $productQuery->where('branch_id', $branchId);
            $ingredientQuery->where('branch_id', $branchId);
        }
        
        // Non-admin users can only see their branch
        if (!$user->isAdmin()) {
            $productQuery->where('branch_id', $user->branch_id);
            $ingredientQuery->where('branch_id', $user->branch_id);
        }
        
        // Get total count first (without limit)
        $totalCount = (clone $productQuery)->count() + (clone $ingredientQuery)->count();
        
        $productAlerts = $productQuery->get()->map(function ($inventory) {
            return [
                'id' => $inventory->id,
                'product_name' => $inventory->product->name ?? 'Unknown',
                'item_type' => 'Product',
                'branch_id' => $inventory->branch_id,
                'branch_name' => $inventory->branch->name ?? 'Unknown',
                'current_stock' => $inventory->quantity,
                'min_stock_level' => $inventory->min_stock_level,
                'urgency' => $inventory->quantity <= 0 ? 'critical' : ($inventory->quantity <= $inventory->min_stock_level / 2 ? 'high' : 'medium'),
            ];
        });

        $ingredientAlerts = $ingredientQuery->get()->map(function ($inventory) {
            return [
                'id' => $inventory->id,
                'product_name' => $inventory->ingredient->name ?? 'Unknown',
                'item_type' => 'Ingredient',
                'branch_id' => $inventory->branch_id,
                'branch_name' => $inventory->branch->name ?? 'Unknown',
                'current_stock' => (float) $inventory->quantity,
                'min_stock_level' => (float) $inventory->min_stock_level,
                'urgency' => $inventory->quantity <= 0 ? 'critical' : ($inventory->quantity <= $inventory->min_stock_level / 2 ? 'high' : 'medium'),
            ];
        });

        $alerts = $productAlerts
            ->concat($ingredientAlerts)
            ->sortBy('current_stock')
            ->values()
            ->take(10);
        
        return response()->json([
            'success' => true,
            'alerts' => $alerts,
            'count' => $totalCount,
        ]);
    }

    /**
     * Get sync status for all products
     */
    public function getSyncStatus()
    {
        $branches = Branch::where('is_active', true)->get();
        $branchCount = $branches->count();
        
        $products = Product::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('product_type')
                    ->orWhere('product_type', Product::TYPE_DIRECT);
            })
            ->get()
            ->map(function ($product) use ($branches, $branchCount) {
            $inventoryCount = Inventory::where('product_id', $product->id)
                ->whereIn('branch_id', $branches->pluck('id'))
                ->count();
            $lowStockBranches = Inventory::where('product_id', $product->id)
                ->whereIn('branch_id', $branches->pluck('id'))
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

    /**
     * Get live stock data for polling updates
     */
    public function liveData()
    {
        $branches = Branch::where('is_active', true)->get();
        
        // Product stock per branch
        $productStocks = Inventory::with(['product', 'branch'])
            ->whereHas('product', function ($productQuery) {
                $productQuery->where('is_active', true)
                    ->where(function ($typeQuery) {
                        $typeQuery->whereNull('product_type')
                            ->orWhere('product_type', Product::TYPE_DIRECT);
                    });
            })
            ->get()
            ->map(function ($inv) {
                return [
                    'product_id' => $inv->product_id,
                    'product_name' => $inv->product->name ?? 'Unknown',
                    'branch_id' => $inv->branch_id,
                    'branch_name' => $inv->branch->name ?? 'Unknown',
                    'quantity' => $inv->quantity,
                    'min_stock_level' => $inv->min_stock_level,
                    'is_low' => $inv->quantity <= $inv->min_stock_level,
                    'is_out' => $inv->quantity <= 0,
                ];
            });

        // Ingredient stock per branch
        $ingredientStocks = \App\Models\IngredientInventory::with(['ingredient', 'branch'])
            ->get()
            ->map(function ($inv) {
                return [
                    'ingredient_id' => $inv->ingredient_id,
                    'ingredient_name' => $inv->ingredient->name ?? 'Unknown',
                    'branch_id' => $inv->branch_id,
                    'branch_name' => $inv->branch->name ?? 'Unknown',
                    'quantity' => $inv->quantity,
                    'min_stock_level' => $inv->min_stock_level,
                    'is_low' => $inv->quantity <= $inv->min_stock_level,
                    'is_out' => $inv->quantity <= 0,
                ];
            });

        $lowStockCount = Inventory::whereHas('product', function ($productQuery) {
                $productQuery->where('is_active', true)
                    ->where(function ($typeQuery) {
                        $typeQuery->whereNull('product_type')
                            ->orWhere('product_type', Product::TYPE_DIRECT);
                    });
            })
            ->whereRaw('quantity <= min_stock_level')
            ->count()
            + IngredientInventory::whereHas('ingredient', function ($ingredientQuery) {
                $ingredientQuery->where('is_active', true);
            })
            ->whereRaw('quantity <= min_stock_level')
            ->count();

        return response()->json([
            'success' => true,
            'product_stocks' => $productStocks,
            'ingredient_stocks' => $ingredientStocks,
            'low_stock_count' => $lowStockCount,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Export inventory to Excel
     */
    public function exportToExcel(Request $request)
    {
        $type = $request->get('type', 'products'); // products or ingredients
        
        if ($type === 'products') {
            $filename = 'product_inventory_' . now()->format('Y-m-d_His') . '.xlsx';
            return Excel::download(new ProductInventoryExport(), $filename);
        } else {
            $filename = 'ingredient_inventory_' . now()->format('Y-m-d_His') . '.xlsx';
            return Excel::download(new IngredientInventoryExport(), $filename);
        }
    }
}
