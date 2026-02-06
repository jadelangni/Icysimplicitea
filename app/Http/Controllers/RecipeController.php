<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Ingredient;
use App\Models\ProductIngredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecipeController extends Controller
{
    /**
     * Display the recipe management page.
     */
    public function index()
    {
        $products = Product::with(['category', 'ingredients'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $ingredients = Ingredient::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('recipes.index', compact('products', 'ingredients'));
    }

    /**
     * Get the recipe for a specific product.
     */
    public function show(Product $product)
    {
        $product->load('ingredients');

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'product_type' => $product->product_type,
                'category' => $product->category->name ?? 'Uncategorized'
            ],
            'recipe' => $product->ingredients->map(function ($ingredient) {
                return [
                    'ingredient_id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'quantity_required' => $ingredient->pivot->quantity_required,
                    'unit' => $ingredient->pivot->unit ?? $ingredient->unit,
                    'available_quantity' => $ingredient->quantity,
                    'is_low_stock' => $ingredient->isLowStock()
                ];
            })
        ]);
    }

    /**
     * Update or create a recipe for a product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_type' => 'required|in:direct,composite',
            'ingredients' => 'required_if:product_type,composite|array',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity_required' => 'required|numeric|min:0.01',
            'ingredients.*.unit' => 'nullable|string|max:50'
        ]);

        try {
            DB::transaction(function () use ($product, $validated) {
                // Update product type
                $product->product_type = $validated['product_type'];
                $product->save();

                if ($validated['product_type'] === 'composite' && !empty($validated['ingredients'])) {
                    // Build the sync array
                    $syncData = [];
                    foreach ($validated['ingredients'] as $ingredientData) {
                        $syncData[$ingredientData['ingredient_id']] = [
                            'quantity_required' => $ingredientData['quantity_required'],
                            'unit' => $ingredientData['unit'] ?? null
                        ];
                    }
                    
                    $product->ingredients()->sync($syncData);
                } else {
                    // Direct product - clear any existing recipe
                    $product->ingredients()->detach();
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Recipe updated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Recipe update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update recipe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a recipe (remove all ingredients from a product).
     */
    public function destroy(Product $product)
    {
        try {
            $product->ingredients()->detach();
            $product->product_type = 'direct';
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Recipe cleared successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Recipe deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear recipe'
            ], 500);
        }
    }

    /**
     * Bulk update multiple product recipes at once.
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'recipes' => 'required|array',
            'recipes.*.product_id' => 'required|exists:products,id',
            'recipes.*.product_type' => 'required|in:direct,composite',
            'recipes.*.ingredients' => 'array'
        ]);

        $results = [];

        try {
            DB::transaction(function () use ($validated, &$results) {
                foreach ($validated['recipes'] as $recipeData) {
                    $product = Product::find($recipeData['product_id']);
                    $product->product_type = $recipeData['product_type'];
                    $product->save();

                    if ($recipeData['product_type'] === 'composite' && !empty($recipeData['ingredients'])) {
                        $syncData = [];
                        foreach ($recipeData['ingredients'] as $ing) {
                            $syncData[$ing['ingredient_id']] = [
                                'quantity_required' => $ing['quantity_required'],
                                'unit' => $ing['unit'] ?? null
                            ];
                        }
                        $product->ingredients()->sync($syncData);
                    } else {
                        $product->ingredients()->detach();
                    }

                    $results[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'success' => true
                    ];
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Bulk recipe update completed',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk recipe update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Bulk update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get estimated servings based on current ingredient inventory.
     */
    public function getServingEstimates(Product $product)
    {
        $product->load('ingredients');

        if ($product->product_type === 'direct') {
            return response()->json([
                'success' => true,
                'product_type' => 'direct',
                'message' => 'Direct products use finished goods inventory'
            ]);
        }

        if ($product->ingredients->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'No recipe defined for this product'
            ]);
        }

        $maxServings = PHP_INT_MAX;
        $limitingIngredient = null;

        foreach ($product->ingredients as $ingredient) {
            $required = $ingredient->pivot->quantity_required;
            if ($required > 0) {
                $possibleServings = floor($ingredient->quantity / $required);
                if ($possibleServings < $maxServings) {
                    $maxServings = $possibleServings;
                    $limitingIngredient = $ingredient->name;
                }
            }
        }

        return response()->json([
            'success' => true,
            'product_type' => 'composite',
            'max_servings' => $maxServings === PHP_INT_MAX ? 0 : $maxServings,
            'limiting_ingredient' => $limitingIngredient,
            'ingredients' => $product->ingredients->map(function ($ing) {
                return [
                    'name' => $ing->name,
                    'available' => $ing->quantity,
                    'required_per_unit' => $ing->pivot->quantity_required,
                    'unit' => $ing->unit,
                    'possible_servings' => floor($ing->quantity / $ing->pivot->quantity_required)
                ];
            })
        ]);
    }
}
