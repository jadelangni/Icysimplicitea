<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
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
     */
    public function store(Request $request)
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

        Ingredient::create($validated);

        return redirect()->route('inventory.index')->with('success', 'Ingredient created successfully.');
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

        return redirect()->route('inventory.index')->with('success', 'Ingredient updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $ingredient = Ingredient::findOrFail($id);
        $ingredient->delete();

        return redirect()->route('inventory.index')->with('success', 'Ingredient deleted successfully.');
    }
}
