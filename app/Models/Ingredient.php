<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    private const PACKAGING_UNITS = [
        'pack',
        'packs',
        'can',
        'cans',
        'bottle',
        'bottles',
        'bag',
        'bags',
        'box',
        'boxes',
        'tray',
        'trays',
    ];

    private const UNIT_ALIASES = [
        'milligram' => 'mg',
        'milligrams' => 'mg',
        'mg' => 'mg',
        'gram' => 'g',
        'grams' => 'g',
        'g' => 'g',
        'kilogram' => 'kg',
        'kilograms' => 'kg',
        'kg' => 'kg',
        'milliliter' => 'ml',
        'milliliters' => 'ml',
        'millilitre' => 'ml',
        'millilitres' => 'ml',
        'ml' => 'ml',
        'liter' => 'l',
        'liters' => 'l',
        'litre' => 'l',
        'litres' => 'l',
        'l' => 'l',
        'teaspoon' => 'tsp',
        'teaspoons' => 'tsp',
        'tsp' => 'tsp',
        'tablespoon' => 'tbsp',
        'tablespoons' => 'tbsp',
        'tbsp' => 'tbsp',
        'tbs' => 'tbsp',
        'tbl' => 'tbsp',
        'cup' => 'cup',
        'cups' => 'cup',
        'gallon' => 'gallon',
        'gallons' => 'gallon',
        'piece' => 'pieces',
        'pieces' => 'pieces',
        'pc' => 'pieces',
        'pcs' => 'pieces',
        'unit' => 'pieces',
        'units' => 'pieces',
        'scoop' => 'scoop',
        'scoops' => 'scoop',
        'pearl scoop' => 'pearl_scoop',
        'pearl scoops' => 'pearl_scoop',
        'tray' => 'tray',
        'trays' => 'tray',
        'sack' => 'sack',
        'sacks' => 'sack',
        'bottle' => 'bottle',
        'bottles' => 'bottle',
        'can' => 'can',
        'cans' => 'can',
        'pack' => 'pack',
        'packs' => 'pack',
        'box' => 'box',
        'boxes' => 'box',
    ];

    private const UNIT_FACTORS = [
        'mg' => ['type' => 'weight', 'factor' => 0.001],
        'g' => ['type' => 'weight', 'factor' => 1],
        'kg' => ['type' => 'weight', 'factor' => 1000],
        'ml' => ['type' => 'volume', 'factor' => 1],
        'tsp' => ['type' => 'volume', 'factor' => 5],
        'tbsp' => ['type' => 'volume', 'factor' => 15],
        'cup' => ['type' => 'volume', 'factor' => 240],
        'l' => ['type' => 'volume', 'factor' => 1000],
        'gallon' => ['type' => 'volume', 'factor' => 3785.411784],
        'pieces' => ['type' => 'count', 'factor' => 1],
        'scoop' => ['type' => 'volume', 'factor' => 15],
        'pearl_scoop' => ['type' => 'weight', 'factor' => 1],
        'tray' => ['type' => 'count', 'factor' => 1],
        'sack' => ['type' => 'weight', 'factor' => 1000],
        'bottle' => ['type' => 'volume', 'factor' => 1],
        'can' => ['type' => 'volume', 'factor' => 1],
        'pack' => ['type' => 'count', 'factor' => 1],
        'box' => ['type' => 'count', 'factor' => 1],
    ];

    /**
     * Mapping of inventory units to recommended recipe units.
     * When adding new recipes, these recommendations will be used as defaults.
     */
    private const UNIT_RECOMMENDATIONS = [
        'kg' => 'g',                    // Kilogram → Gram
        'g' => 'tbsp',                  // Gram → Tablespoon
        'l' => 'ml',                    // Liter → Milliliter
        'bottle' => 'ml',               // Bottle → Milliliter
        'gallon' => 'l',                // Gallon → Liter
        'cup' => 'ml',                  // Cup → Milliliter
        'tbsp' => 'ml',                 // Tablespoon → Milliliter
        'pack' => 'pieces',             // Pack → Pieces
        'tray' => 'pieces',             // Tray → Pieces
        'box' => 'pieces',              // Box → Pieces
        'can' => 'ml',                  // Can → Milliliter
        'sack' => 'kg',                 // Sack → Kilogram
        'pearl_scoop' => 'g',           // Pearl Scoop → Gram
        'scoop' => 'g',                 // Scoop → Gram
    ];

    protected $fillable = [
        'name',
        'description',
        'unit',
        'recipe_unit',
        'recipe_units_per_inventory_unit',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'recipe_units_per_inventory_unit' => 'decimal:4',
    ];

    protected $appends = [
        'recipe_conversion_label',
    ];

    /**
     * Get all inventory records for this ingredient (one per branch).
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(IngredientInventory::class);
    }

    /**
     * Get the inventory for a specific branch.
     */
    public function inventoryForBranch(int $branchId): ?IngredientInventory
    {
        return $this->inventories()->where('branch_id', $branchId)->first();
    }

    /**
     * Get or create inventory for a specific branch.
     */
    public function getOrCreateInventoryForBranch(int $branchId): IngredientInventory
    {
        return IngredientInventory::firstOrCreate(
            ['ingredient_id' => $this->id, 'branch_id' => $branchId],
            ['quantity' => 0, 'min_stock_level' => 0]
        );
    }

    /**
     * Get the products that use this ingredient in their recipe.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_ingredients')
            ->withPivot('quantity_required', 'unit')
            ->withTimestamps();
    }

    /**
     * Get the quantity for a specific branch.
     */
    public function getQuantityForBranch(int $branchId): float
    {
        $inventory = $this->inventoryForBranch($branchId);
        return $inventory ? (float) $inventory->quantity : 0;
    }

    /**
     * Get the min stock level for a specific branch.
     */
    public function getMinStockLevelForBranch(int $branchId): float
    {
        $inventory = $this->inventoryForBranch($branchId);
        return $inventory ? (float) $inventory->min_stock_level : 0;
    }

    /**
     * Check if this ingredient is low on stock for a specific branch.
     */
    public function isLowStockForBranch(int $branchId): bool
    {
        $inventory = $this->inventoryForBranch($branchId);
        return $inventory ? $inventory->isLowStock() : true;
    }

    /**
     * Check if this ingredient is out of stock for a specific branch.
     */
    public function isOutOfStockForBranch(int $branchId): bool
    {
        $inventory = $this->inventoryForBranch($branchId);
        return $inventory ? $inventory->isOutOfStock() : true;
    }

    /**
     * Deduct quantity from this ingredient for a specific branch.
     * Returns the amount actually deducted (may be less if insufficient stock).
     */
    public function deductForBranch(int $branchId, float $amount): float
    {
        $inventory = $this->inventoryForBranch($branchId);
        if (!$inventory) {
            return 0;
        }
        return $inventory->deduct($amount);
    }

    /**
     * Add quantity to this ingredient for a specific branch (restocking).
     */
    public function restockForBranch(int $branchId, float $amount): void
    {
        $inventory = $this->getOrCreateInventoryForBranch($branchId);
        $inventory->restock($amount);
    }

    /**
     * Get the status of this ingredient for a specific branch.
     */
    public function getStatusForBranch(int $branchId): string
    {
        $inventory = $this->inventoryForBranch($branchId);
        return $inventory ? $inventory->status : 'No Stock';
    }

    /**
     * Get the status color class for a specific branch.
     */
    public function getStatusColorForBranch(int $branchId): string
    {
        $inventory = $this->inventoryForBranch($branchId);
        return $inventory ? $inventory->status_color : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400';
    }

    /**
     * Convert a recipe quantity into this ingredient's stock unit.
     * Returns null when units are incompatible or unknown.
     */
    public function convertRecipeQuantityToStockUnit(float $quantity, ?string $recipeUnit): ?float
    {
        $from = self::normalizeUnit($recipeUnit ?: $this->getRecipeUnit());
        $to = self::normalizeUnit($this->unit);

        if (!$from || !$to) {
            return null;
        }

        if ($from !== $to) {
            $fromMeta = self::UNIT_FACTORS[$from] ?? null;
            $toMeta = self::UNIT_FACTORS[$to] ?? null;

            if (!$fromMeta || !$toMeta || $fromMeta['type'] !== $toMeta['type']) {
                return null;
            }

            $quantity = ($quantity * $fromMeta['factor']) / $toMeta['factor'];
        }

        $recipeUnitsPerInventoryUnit = (float) ($this->recipe_units_per_inventory_unit ?: 1);
        if ($recipeUnitsPerInventoryUnit <= 0) {
            return null;
        }

        return $quantity / $recipeUnitsPerInventoryUnit;
    }

    public function getRecipeUnit(): string
    {
        $recipeUnit = $this->recipe_unit;

        if (!$recipeUnit && in_array(strtolower((string) $this->unit), self::PACKAGING_UNITS, true)) {
            return 'g';
        }

        return self::normalizeUnit($recipeUnit ?: $this->unit) ?: ($recipeUnit ?: $this->unit);
    }

    /**
     * Get the recommended recipe unit for this ingredient based on its inventory unit.
     * This is used when creating new recipes - the system will automatically suggest
     * this unit instead of asking the user to choose.
     * 
     * @return string The recommended recipe unit
     */
    public function getRecommendedRecipeUnit(): string
    {
        $normalizedInventoryUnit = self::normalizeUnit($this->unit);
        
        // Check if there's a recommendation for this inventory unit
        if ($normalizedInventoryUnit && isset(self::UNIT_RECOMMENDATIONS[$normalizedInventoryUnit])) {
            $recommendedUnit = self::UNIT_RECOMMENDATIONS[$normalizedInventoryUnit];
            // Return the recommendation, ensuring it's normalized
            return self::normalizeUnit($recommendedUnit) ?: $recommendedUnit;
        }
        
        // Fall back to the current getRecipeUnit behavior
        return $this->getRecipeUnit();
    }

    public function getRecipeConversionLabelAttribute(): string
    {
        return '1 ' . $this->unit . ' = ' . rtrim(rtrim(number_format((float) ($this->recipe_units_per_inventory_unit ?: 1), 4), '0'), '.') . ' ' . $this->getRecipeUnit();
    }

    public static function normalizeUnit(?string $unit): ?string
    {
        if ($unit === null) {
            return null;
        }

        $normalized = strtolower(trim($unit));
        if ($normalized === '') {
            return null;
        }

        return self::UNIT_ALIASES[$normalized] ?? $normalized;
    }

    /**
     * Get all compatible units for this ingredient based on its inventory unit type.
     * Compatible units are those in the same unit type (weight, volume, count).
     */
    public function getCompatibleUnits(): array
    {
        $inventoryUnit = self::normalizeUnit($this->unit);
        if (!$inventoryUnit) {
            return [];
        }

        $unitMeta = self::UNIT_FACTORS[$inventoryUnit] ?? null;
        if (!$unitMeta) {
            return [$inventoryUnit];
        }

        $type = $unitMeta['type'];
        $compatibleUnits = [];

        foreach (self::UNIT_FACTORS as $unit => $meta) {
            if ($meta['type'] === $type) {
                $compatibleUnits[] = $unit;
            }
        }

        return $compatibleUnits;
    }

    /**
     * Convert a quantity from one unit to another.
     * Returns null if units are incompatible.
     */
    public static function convertBetweenUnits(float $quantity, ?string $fromUnit, ?string $toUnit): ?float
    {
        $from = self::normalizeUnit($fromUnit);
        $to = self::normalizeUnit($toUnit);

        if (!$from || !$to) {
            return null;
        }

        $fromMeta = self::UNIT_FACTORS[$from] ?? null;
        $toMeta = self::UNIT_FACTORS[$to] ?? null;

        if (!$fromMeta || !$toMeta || $fromMeta['type'] !== $toMeta['type']) {
            return null;
        }

        // Convert to base unit, then to target unit
        $baseQuantity = $quantity * $fromMeta['factor'];
        return $baseQuantity / $toMeta['factor'];
    }

    /**
     * Convert recipe quantity when changing the recipe unit.
     * This is used when the admin changes the unit of an ingredient in the recipe.
     */
    public function convertRecipeQuantityBetweenUnits(float $quantity, ?string $currentUnit, ?string $newUnit): ?float
    {
        return self::convertBetweenUnits($quantity, $currentUnit, $newUnit);
    }
}
