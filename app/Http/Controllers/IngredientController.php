<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\IngredientInventory;
use App\Models\Branch;
use Illuminate\Http\Request;

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
     * Creates the ingredient globally and adds inventory for all branches.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ingredients,name',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'initial_quantity' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
        ]);

        // Create the ingredient
        $ingredient = Ingredient::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'unit' => $validated['unit'],
            'is_active' => true,
        ]);

        // Create inventory records for all branches
        $branches = Branch::all();
        $initialQuantity = $validated['initial_quantity'] ?? 0;
        $minStockLevel = $validated['min_stock_level'] ?? 10;

        foreach ($branches as $branch) {
            IngredientInventory::create([
                'ingredient_id' => $ingredient->id,
                'branch_id' => $branch->id,
                'quantity' => $initialQuantity,
                'min_stock_level' => $minStockLevel,
            ]);
        }

        return redirect()->route('product-inventory.index', ['tab' => 'ingredients'])
            ->with('success', "Ingredient '{$ingredient->name}' added to all branches.");
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
            'quantity' => 'required|numeric|min:0',
            'min_stock_level' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

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
