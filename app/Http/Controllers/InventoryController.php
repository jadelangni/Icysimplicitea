<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Ingredient;
use App\Models\IngredientInventory;
use App\Models\SalesItem;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * Redirects to product-inventory for consolidated view
     */
    public function index(Request $request)
    {
        // Redirect to consolidated product-inventory page
        $view = $request->get('view', 'products');
        $tab = $view === 'ingredients' ? 'ingredients' : 'products';
        
        return redirect()->route('product-inventory.index', array_merge(
            $request->except('view'),
            ['tab' => $tab]
        ));
        
        // Legacy code below - kept for reference
        $user = Auth::user();
        $branchId = $user->branch_id;
        $view = $request->get('view', 'products'); // Default to products view
        $search = $request->get('q', '');

        // Get branch info for display
        $currentBranch = Branch::find($branchId);
        $allBranches = Branch::all(); // All branches for multi-branch ingredient view
        $branches = $user->role === 'admin' ? $allBranches : collect([$currentBranch]);

        // Allow admin to switch branches (for products view)
        if ($user->role === 'admin' && $request->has('branch_id')) {
            $branchId = $request->get('branch_id');
            $currentBranch = Branch::find($branchId);
        }

        if ($view === 'ingredients') {
            // Ingredients view - Global multi-branch table
            // Get all ingredients with their inventory across all branches
            $query = Ingredient::query();

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $ingredients = $query->orderBy('name')->get();
            
            // Build multi-branch inventory data
            $inventoryItems = $ingredients->map(function($ingredient) use ($allBranches) {
                $branchData = [];
                $statuses = [];
                $hasLowStock = false;
                $hasOutOfStock = false;
                
                foreach ($allBranches as $branch) {
                    $inv = IngredientInventory::where('ingredient_id', $ingredient->id)
                        ->where('branch_id', $branch->id)
                        ->first();
                    
                    $quantity = $inv ? $inv->quantity : 0;
                    $minStock = $inv ? $inv->min_stock_level : 10;
                    $isOutOfStock = $quantity <= 0;
                    $isLowStock = !$isOutOfStock && $quantity <= $minStock;
                    
                    $branchData[$branch->id] = [
                        'inventory_id' => $inv ? $inv->id : null,
                        'quantity' => $quantity,
                        'min_stock_level' => $minStock,
                        'is_low_stock' => $isLowStock,
                        'is_out_of_stock' => $isOutOfStock,
                    ];
                    
                    if ($isOutOfStock) {
                        $statuses[] = "Out of Stock ({$branch->name})";
                        $hasOutOfStock = true;
                    } elseif ($isLowStock) {
                        $statuses[] = "Low Stock ({$branch->name})";
                        $hasLowStock = true;
                    }
                }
                
                // Determine overall status
                if ($hasOutOfStock) {
                    $status = implode(', ', array_filter($statuses, fn($s) => str_contains($s, 'Out of Stock')));
                    $statusColor = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400';
                } elseif ($hasLowStock) {
                    $status = implode(', ', array_filter($statuses, fn($s) => str_contains($s, 'Low Stock')));
                    $statusColor = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400';
                } else {
                    $status = 'In Stock';
                    $statusColor = 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400';
                }
                
                return (object)[
                    'id' => $ingredient->id,
                    'ingredient_id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'description' => $ingredient->description,
                    'unit' => $ingredient->unit,
                    'branches' => $branchData,
                    'status' => $status,
                    'status_color' => $statusColor,
                    'is_low_stock' => $hasLowStock && !$hasOutOfStock, // Only low stock if not out of stock
                    'is_out_of_stock' => $hasOutOfStock,
                    'updated_at' => $ingredient->updated_at,
                ];
            });

            $totalItems = $inventoryItems->count();
            // Count only items that are low stock (excluding out of stock) for accurate summary
            $lowStockCount = $inventoryItems->filter(fn($i) => $i->is_low_stock)->count();
            $outOfStockCount = $inventoryItems->filter(fn($i) => $i->is_out_of_stock)->count();

            // For adding new ingredients to the system (global)
            $allIngredients = collect(); // Can add new ingredient creation instead

        } else {
            // Products view (finished goods)
            $query = Inventory::with(['product.category', 'branch'])
                ->where('branch_id', $branchId);

            if ($search) {
                $query->whereHas('product', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $inventoryItems = $query->get()->map(function($inv) {
                $isOutOfStock = $inv->quantity <= 0;
                $isLowStock = !$isOutOfStock && $inv->quantity <= $inv->min_stock_level;
                
                return (object)[
                    'id' => $inv->id,
                    'product_id' => $inv->product_id,
                    'name' => $inv->product->name,
                    'description' => $inv->product->category->name ?? 'Uncategorized',
                    'unit' => 'pcs',
                    'quantity' => $inv->quantity,
                    'min_stock_level' => $inv->min_stock_level,
                    'status' => $isOutOfStock ? 'Out of Stock' : ($isLowStock ? 'Low Stock' : 'In Stock'),
                    'status_color' => $isOutOfStock 
                        ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400' 
                        : ($isLowStock 
                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400' 
                            : 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400'),
                    'updated_at' => $inv->updated_at,
                    'is_low_stock' => $isLowStock,
                    'is_out_of_stock' => $isOutOfStock,
                    'product_type' => $inv->product->product_type ?? 'direct',
                    'price' => $inv->product->price,
                ];
            });

            $totalItems = $inventoryItems->count();
            $lowStockCount = $inventoryItems->filter(fn($i) => $i->is_low_stock)->count();
            $outOfStockCount = $inventoryItems->filter(fn($i) => $i->is_out_of_stock)->count();
            $allIngredients = collect();
        }

        // Sales report for last 30 days (for products view)
        $chartLabels = collect();
        $chartData = collect();
        
        if ($view === 'products') {
            $start = now()->subDays(30)->startOfDay();
            $end = now()->endOfDay();

            $salesReport = SalesItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                ->join('sales', 'sales.id', '=', 'sales_items.sale_id')
                ->where('sales.branch_id', $branchId)
                ->whereBetween('sales.created_at', [$start, $end])
                ->groupBy('product_id')
                ->orderByDesc('total_quantity')
                ->limit(10)
                ->get()
                ->map(function($row) {
                    $product = \App\Models\Product::find($row->product_id);
                    return [
                        'product' => $product ? $product->name : 'Unknown',
                        'quantity' => (int)$row->total_quantity
                    ];
                });

            $chartLabels = $salesReport->pluck('product');
            $chartData = $salesReport->pluck('quantity');
        }

        // Return view with no-cache headers to ensure fresh data
        return response()
            ->view('inventory.index', compact(
                'inventoryItems',
                'view',
                'search',
                'totalItems',
                'lowStockCount',
                'outOfStockCount',
                'currentBranch',
                'branches',
                'allBranches',
                'branchId',
                'chartLabels',
                'chartData',
                'allIngredients'
            ))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $view = $request->get('view', 'products');
        $branchId = Auth::user()->branch_id;
        
        if ($view === 'ingredients') {
            // Get ingredients not yet in this branch's inventory
            $ingredients = Ingredient::whereNotIn('id', 
                IngredientInventory::where('branch_id', $branchId)->pluck('ingredient_id')
            )->get();
            
            return view('inventory.create-ingredient', compact('ingredients'));
        }
        
        // For products, return product creation form
        return view('inventory.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $view = $request->get('view', 'products');
        $branchId = Auth::user()->branch_id;

        if ($view === 'ingredients') {
            $validated = $request->validate([
                'ingredient_id' => 'required|exists:ingredients,id',
                'quantity' => 'required|numeric|min:0',
                'min_stock_level' => 'required|numeric|min:0',
            ]);

            IngredientInventory::updateOrCreate(
                ['ingredient_id' => $validated['ingredient_id'], 'branch_id' => $branchId],
                ['quantity' => $validated['quantity'], 'min_stock_level' => $validated['min_stock_level']]
            );

            return redirect()->route('product-inventory.index', ['tab' => 'ingredients'])
                ->with('success', 'Ingredient added to inventory successfully.');
        }

        // Product inventory store logic
        return redirect()->route('product-inventory.index')->with('success', 'Inventory updated successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $view = $request->get('view', 'products');
        
        if ($view === 'ingredients') {
            $inventoryItem = IngredientInventory::with('ingredient')->findOrFail($id);
            return view('inventory.edit-ingredient', compact('inventoryItem'));
        }
        
        $inventoryItem = Inventory::with('product')->findOrFail($id);
        return view('inventory.edit', compact('inventoryItem'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $view = $request->get('view', 'products');

        if ($view === 'ingredients') {
            $validated = $request->validate([
                'quantity' => 'required|numeric|min:0',
                'min_stock_level' => 'required|numeric|min:0',
            ]);

            $inventory = IngredientInventory::findOrFail($id);
            $inventory->update($validated);

            return redirect()->route('product-inventory.index', ['tab' => 'ingredients'])
                ->with('success', 'Ingredient inventory updated successfully.');
        }

        // Product inventory update logic
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
        ]);

        $inventory = Inventory::findOrFail($id);
        $inventory->update($validated);

        return redirect()->route('product-inventory.index')
            ->with('success', 'Product inventory updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $view = $request->get('view', 'products');

        if ($view === 'ingredients') {
            $inventory = IngredientInventory::findOrFail($id);
            $ingredientName = $inventory->ingredient->name;
            $inventory->delete();

            return redirect()->route('product-inventory.index', ['tab' => 'ingredients'])
                ->with('success', "Ingredient '{$ingredientName}' removed from inventory.");
        }

        // Product inventory delete is typically not allowed
        return redirect()->route('product-inventory.index')
            ->with('error', 'Cannot delete product inventory directly.');
    }

    /**
     * Quick restock for an ingredient
     */
    public function restock(Request $request, string $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $inventory = IngredientInventory::findOrFail($id);
        $oldQuantity = $inventory->quantity;
        $inventory->quantity += $validated['amount'];
        $inventory->save();
        
        // Refresh the model to get updated computed attributes
        $inventory->refresh();
        
        $statusMessage = "Restocked {$validated['amount']} {$inventory->ingredient->unit} of {$inventory->ingredient->name}.";
        
        // Add status change notification if applicable
        if ($oldQuantity <= $inventory->min_stock_level && $inventory->quantity > $inventory->min_stock_level) {
            $statusMessage .= " Status changed to In Stock.";
        }

        // Always redirect to ingredients tab with fresh data
        return redirect()->route('product-inventory.index', ['tab' => 'ingredients'])
            ->with('success', $statusMessage);
    }

    /**
     * Update ingredient inventory across all branches
     */
    public function updateIngredientBranches(Request $request)
    {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'branches' => 'required|array',
            'branches.*.quantity' => 'required|numeric|min:0',
            'branches.*.min_stock_level' => 'required|numeric|min:0',
        ]);

        $ingredient = Ingredient::findOrFail($validated['ingredient_id']);
        $allBranches = Branch::all();

        foreach ($validated['branches'] as $branchId => $data) {
            // Ensure branch exists
            if (!$allBranches->contains('id', $branchId)) {
                continue;
            }

            IngredientInventory::updateOrCreate(
                ['ingredient_id' => $ingredient->id, 'branch_id' => $branchId],
                [
                    'quantity' => $data['quantity'],
                    'min_stock_level' => $data['min_stock_level'],
                ]
            );
        }

        return redirect()->route('product-inventory.index', ['tab' => 'ingredients'])
            ->with('success', "Updated inventory for '{$ingredient->name}' across all branches.");
    }
}
