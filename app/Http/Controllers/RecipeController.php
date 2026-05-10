<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Ingredient;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Services\CatalogInventorySyncService;

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
            ->get()
            ->map(function ($ingredient) {
                return [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'unit' => $ingredient->unit,
                    'recipe_unit' => $ingredient->getRecipeUnit(),
                    'recommended_recipe_unit' => $ingredient->getRecommendedRecipeUnit(),
                    'recipe_conversion_label' => $ingredient->recipe_conversion_label,
                ];
            });

        return view('recipes.index', compact('products', 'ingredients'));
    }

    /**
     * Get the recipe for a specific product.
     */
    public function show(Product $product)
    {
        $product->load('ingredients');
        $branchId = $this->resolveInventoryBranchId();

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'product_type' => $product->product_type,
                'category' => $product->category->name ?? 'Uncategorized'
            ],
            'recipe' => $product->ingredients->map(function ($ingredient) use ($branchId) {
                return [
                    'ingredient_id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'quantity_required' => $ingredient->pivot->quantity_required,
                    'unit' => $ingredient->pivot->unit ?? $ingredient->getRecommendedRecipeUnit(),
                    'available_quantity' => $branchId ? $ingredient->getQuantityForBranch($branchId) : 0,
                    'inventory_unit' => $ingredient->unit,
                    'recipe_unit' => $ingredient->getRecipeUnit(),
                    'recommended_recipe_unit' => $ingredient->getRecommendedRecipeUnit(),
                    'recipe_conversion_label' => $ingredient->recipe_conversion_label,
                    'is_low_stock' => $branchId ? $ingredient->isLowStockForBranch($branchId) : false
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
                        $ingredient = Ingredient::find($ingredientData['ingredient_id']);
                        if (!$ingredient) {
                            throw ValidationException::withMessages([
                                'ingredients' => ["Invalid ingredient ID: {$ingredientData['ingredient_id']}"]
                            ]);
                        }

                        // Use provided unit, or recommend based on inventory unit if not provided
                        $recipeUnit = $ingredientData['unit'] 
                            ? Ingredient::normalizeUnit($ingredientData['unit'])
                            : $ingredient->getRecommendedRecipeUnit();
                            
                        if (!$recipeUnit) {
                            throw ValidationException::withMessages([
                                'ingredients' => ["Please select a valid unit for {$ingredient->name}."]
                            ]);
                        }

                        $isConvertible = $ingredient->convertRecipeQuantityToStockUnit(
                            (float) $ingredientData['quantity_required'],
                            $recipeUnit
                        ) !== null;

                        if (!$isConvertible) {
                            throw ValidationException::withMessages([
                                'ingredients' => ["Unit '{$recipeUnit}' is not compatible with {$ingredient->name} recipe unit '{$ingredient->getRecipeUnit()}'. Set the ingredient recipe conversion in inventory first."]
                            ]);
                        }

                        $syncData[$ingredientData['ingredient_id']] = [
                            'quantity_required' => $ingredientData['quantity_required'],
                            'unit' => $recipeUnit
                        ];
                    }
                    
                    $product->ingredients()->sync($syncData);
                } else {
                    // Direct product - clear any existing recipe
                    $product->ingredients()->detach();
                    app(CatalogInventorySyncService::class)->ensureDirectProductInventory($product);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Recipe updated successfully!'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => collect($e->errors())->flatten()->first() ?? 'Invalid recipe data.'
            ], 422);
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
            app(CatalogInventorySyncService::class)->ensureDirectProductInventory($product);

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
                        app(CatalogInventorySyncService::class)->ensureDirectProductInventory($product);
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
        $branchId = $this->resolveInventoryBranchId();

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
            $required = $ingredient->convertRecipeQuantityToStockUnit(
                (float) $ingredient->pivot->quantity_required,
                $ingredient->pivot->unit
            );
            $available = $branchId ? $ingredient->getQuantityForBranch($branchId) : 0;

            if ($required === null) {
                return response()->json([
                    'success' => false,
                    'error' => "Unit mismatch in recipe for {$ingredient->name}: '{$ingredient->pivot->unit}' is not compatible with configured recipe unit '{$ingredient->getRecipeUnit()}'."
                ], 422);
            }

            if ($required > 0) {
                $possibleServings = floor($available / $required);
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
            'ingredients' => $product->ingredients->map(function ($ing) use ($branchId) {
                $available = $branchId ? $ing->getQuantityForBranch($branchId) : 0;
                $required = $ing->convertRecipeQuantityToStockUnit(
                    (float) $ing->pivot->quantity_required,
                    $ing->pivot->unit
                );
                return [
                    'name' => $ing->name,
                    'available' => $available,
                    'required_per_unit' => $required ?? 0,
                    'unit' => $ing->unit,
                    'possible_servings' => $required && $required > 0 ? floor($available / $required) : 0,
                    'recipe_unit' => $ing->pivot->unit ?? $ing->getRecipeUnit()
                ];
            })
        ]);
    }

    private function resolveInventoryBranchId(): ?int
    {
        $userBranchId = auth()->user()?->branch_id;

        if ($userBranchId) {
            return (int) $userBranchId;
        }

        return Branch::where('is_active', true)->value('id') ?? Branch::value('id');
    }

    /**
     * Get compatible units for an ingredient based on its inventory unit.
     */
    public function getCompatibleUnits(Ingredient $ingredient)
    {
        $compatibleUnits = $ingredient->getCompatibleUnits();
        
        return response()->json([
            'success' => true,
            'ingredient_id' => $ingredient->id,
            'ingredient_name' => $ingredient->name,
            'inventory_unit' => $ingredient->unit,
            'compatible_units' => $compatibleUnits
        ]);
    }

    /**
     * Convert a quantity between two units for an ingredient.
     * Used when the admin changes the unit of an ingredient in the recipe.
     */
    public function convertQuantity(Request $request)
    {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0.01',
            'from_unit' => 'required|string',
            'to_unit' => 'required|string'
        ]);

        $ingredient = Ingredient::find($validated['ingredient_id']);
        $convertedQuantity = Ingredient::convertBetweenUnits(
            (float) $validated['quantity'],
            $validated['from_unit'],
            $validated['to_unit']
        );

        if ($convertedQuantity === null) {
            return response()->json([
                'success' => false,
                'error' => "Cannot convert from '{$validated['from_unit']}' to '{$validated['to_unit']}' for {$ingredient->name}. Units must be of compatible types."
            ], 422);
        }

        return response()->json([
            'success' => true,
            'ingredient_id' => $ingredient->id,
            'original_quantity' => (float) $validated['quantity'],
            'original_unit' => $validated['from_unit'],
            'converted_quantity' => round($convertedQuantity, 4),
            'new_unit' => $validated['to_unit']
        ]);
    }
}
