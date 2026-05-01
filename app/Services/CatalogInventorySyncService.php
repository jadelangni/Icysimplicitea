<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Ingredient;
use App\Models\IngredientInventory;
use App\Models\Inventory;
use App\Models\Product;

class CatalogInventorySyncService
{
    public function ensureDirectProductInventory(Product $product): int
    {
        if (!$product->isDirectProduct()) {
            return 0;
        }

        $created = 0;

        foreach ($this->activeBranches() as $branch) {
            $inventory = Inventory::firstOrCreate(
                [
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => 0,
                    'min_stock_level' => 10,
                ]
            );

            if ($inventory->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    public function ensureIngredientInventory(Ingredient $ingredient): int
    {
        $created = 0;

        foreach ($this->activeBranches() as $branch) {
            $inventory = IngredientInventory::firstOrCreate(
                [
                    'branch_id' => $branch->id,
                    'ingredient_id' => $ingredient->id,
                ],
                [
                    'quantity' => 0,
                    'min_stock_level' => 10,
                ]
            );

            if ($inventory->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    public function ensureBranchInventory(Branch $branch): array
    {
        $createdProducts = 0;
        $createdIngredients = 0;

        Product::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('product_type')
                    ->orWhere('product_type', Product::TYPE_DIRECT);
            })
            ->each(function (Product $product) use ($branch, &$createdProducts) {
                $inventory = Inventory::firstOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity' => 0,
                        'min_stock_level' => 10,
                    ]
                );

                if ($inventory->wasRecentlyCreated) {
                    $createdProducts++;
                }
            });

        Ingredient::where('is_active', true)
            ->each(function (Ingredient $ingredient) use ($branch, &$createdIngredients) {
                $inventory = IngredientInventory::firstOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'ingredient_id' => $ingredient->id,
                    ],
                    [
                        'quantity' => 0,
                        'min_stock_level' => 10,
                    ]
                );

                if ($inventory->wasRecentlyCreated) {
                    $createdIngredients++;
                }
            });

        return [
            'products' => $createdProducts,
            'ingredients' => $createdIngredients,
        ];
    }

    public function syncAllActiveCatalog(): array
    {
        $createdProducts = 0;
        $createdIngredients = 0;

        Product::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('product_type')
                    ->orWhere('product_type', Product::TYPE_DIRECT);
            })
            ->each(function (Product $product) use (&$createdProducts) {
                $createdProducts += $this->ensureDirectProductInventory($product);
            });

        Ingredient::where('is_active', true)
            ->each(function (Ingredient $ingredient) use (&$createdIngredients) {
                $createdIngredients += $this->ensureIngredientInventory($ingredient);
            });

        return [
            'products' => $createdProducts,
            'ingredients' => $createdIngredients,
        ];
    }

    private function activeBranches()
    {
        $branches = Branch::where('is_active', true)->get();

        return $branches->isNotEmpty() ? $branches : Branch::all();
    }
}
