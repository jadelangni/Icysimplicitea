<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Ingredient;
use App\Models\IngredientInventory;
use Illuminate\Http\Request;
use App\Services\CatalogInventorySyncService;

class IngredientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Ingredient::query();

        // Search functionality
        if ($request->filled('q')) {
            $search = $request->get('q');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $ingredients = $query->orderBy('updated_at', 'desc')->get();

        return view('inventory.index', compact('ingredients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inventory.create');
    }

    /**
     * Store a newly created resource in storage.
     * Creates the ingredient globally and seeds selected branch inventory values.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ingredients,name',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'branch_id' => 'nullable|exists:branches,id',
            'recipe_unit' => 'nullable|string|max:50',
            'recipe_units_per_inventory_unit' => 'nullable|numeric|min:0.0001',
            'initial_quantity' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
            'branches' => 'nullable|array',
            'branches.*.quantity' => 'nullable|numeric|min:0',
            'branches.*.min_stock_level' => 'nullable|numeric|min:0',
        ]);

        $ingredient = Ingredient::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'unit' => $validated['unit'],
            'recipe_unit' => $validated['recipe_unit'] ?? $validated['unit'],
            'recipe_units_per_inventory_unit' => $validated['recipe_units_per_inventory_unit'] ?? 1,
            'is_active' => true,
        ]);

        app(CatalogInventorySyncService::class)->ensureIngredientInventory($ingredient);

        $branches = Branch::all();
        $submittedBranches = $validated['branches'] ?? [];

        if (!empty($submittedBranches)) {
            foreach ($submittedBranches as $branchId => $branchData) {
                if (!$branches->contains('id', (int) $branchId)) {
                    continue;
                }

                IngredientInventory::updateOrCreate(
                    ['ingredient_id' => $ingredient->id, 'branch_id' => (int) $branchId],
                    [
                        'quantity' => (float) ($branchData['quantity'] ?? 0),
                        'min_stock_level' => (float) ($branchData['min_stock_level'] ?? 10),
                    ]
                );
            }

            return redirect()->route('product-inventory.index', ['tab' => 'ingredients'])
                ->with('success', "Ingredient '{$ingredient->name}' added for all branches.");
        }

        // Backward-compatible fallback for older single-branch forms.
        $initialQuantity = $validated['initial_quantity'] ?? 0;
        $minStockLevel = $validated['min_stock_level'] ?? 10;
        $fallbackBranchId = (int) ($validated['branch_id'] ?? 0);

        if ($fallbackBranchId > 0) {
            IngredientInventory::updateOrCreate(
                ['ingredient_id' => $ingredient->id, 'branch_id' => $fallbackBranchId],
                [
                    'quantity' => $initialQuantity,
                    'min_stock_level' => $minStockLevel,
                ]
            );

            $branchName = Branch::where('id', $fallbackBranchId)->value('name') ?? 'Selected Branch';

            return redirect()->route('product-inventory.index', ['tab' => 'ingredients'])
                ->with('success', "Ingredient '{$ingredient->name}' added for {$branchName}.");
        }

        return redirect()->route('product-inventory.index', ['tab' => 'ingredients'])
            ->with('success', "Ingredient '{$ingredient->name}' added successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $ingredient = Ingredient::findOrFail($id);
        return view('inventory.show', compact('ingredient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $ingredient = Ingredient::findOrFail($id);
        return view('inventory.edit', compact('ingredient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'recipe_unit' => 'nullable|string|max:50',
            'recipe_units_per_inventory_unit' => 'nullable|numeric|min:0.0001',
            'quantity' => 'required|numeric|min:0',
            'min_stock_level' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['recipe_unit'] = $validated['recipe_unit'] ?: $validated['unit'];
        $validated['recipe_units_per_inventory_unit'] = $validated['recipe_units_per_inventory_unit'] ?? 1;

        $ingredient = Ingredient::findOrFail($id);
        $ingredient->update($validated);

        return redirect()->route('product-inventory.index', ['tab' => 'ingredients'])
            ->with('success', 'Ingredient updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * Also deletes all related inventory records across branches.
     */
    public function destroy($id)
    {
        $ingredient = Ingredient::findOrFail($id);
        $ingredientName = $ingredient->name;
        
        // Delete all inventory records for this ingredient across all branches
        IngredientInventory::where('ingredient_id', $id)->delete();
        
        // Delete the ingredient itself
        $ingredient->delete();

        return redirect()->route('product-inventory.index', ['tab' => 'ingredients'])
            ->with('success', "Ingredient '{$ingredientName}' deleted from all branches.");
    }
}
