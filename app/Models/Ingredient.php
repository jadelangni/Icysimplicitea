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
        $to = self::normalizeUnit($this->getRecipeUnit());

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
            $unit = strtolower((string) $this->unit);

            if (in_array($unit, ['can', 'cans', 'bottle', 'bottles'], true)) {
                return self::normalizeUnit('ml');
            }

            if (in_array($unit, ['pack', 'packs', 'bag', 'bags', 'box', 'boxes'], true)) {
                return self::normalizeUnit('pieces');
            }

            return self::normalizeUnit('ml');
        }

        return self::normalizeUnit($recipeUnit ?: $this->unit) ?: ($recipeUnit ?: $this->unit);
    }

    public function getRecipeConversionLabelAttribute(): string
    {
        return '1 ' . $this->unit . ' = ' . rtrim(rtrim(number_format((float) ($this->recipe_units_per_inventory_unit ?: 1), 4), '0'), '.') . ' ' . $this->getRecipeUnit();
    }

    /**
     * Get the recommended recipe unit for this ingredient.
     * If a recipe_unit is already set, returns it. Otherwise recommends one based on inventory unit type.
     */
    public function getRecommendedRecipeUnit(): string
    {
        if ($this->recipe_unit) {
            return self::normalizeUnit($this->recipe_unit) ?: $this->recipe_unit;
        }

        $normalizedUnit = self::normalizeUnit($this->unit);
        
        if (!$normalizedUnit) {
            return $this->unit;
        }

        // Get the unit type for the inventory unit
        $unitMeta = self::UNIT_FACTORS[$normalizedUnit] ?? null;
        
        if (!$unitMeta) {
            return $normalizedUnit;
        }

        // Recommend a base unit for each type
        $typeRecommendations = [
            'weight' => 'g',      // grams for weight
            'volume' => 'ml',     // milliliters for volume
            'count'  => 'pieces', // pieces for count
        ];

        return $typeRecommendations[$unitMeta['type']] ?? $normalizedUnit;
    }

    /**
     * Get all compatible units for this ingredient based on its unit type.
     */
    public function getCompatibleUnits(): array
    {
        $normalizedUnit = self::normalizeUnit($this->unit);
        
        if (!$normalizedUnit) {
            return [$this->unit];
        }

        $unitMeta = self::UNIT_FACTORS[$normalizedUnit] ?? null;
        
        if (!$unitMeta) {
            return [$this->unit];
        }

        // Get all units of the same type
        $compatibleUnits = [];
        foreach (self::UNIT_FACTORS as $unit => $meta) {
            if ($meta['type'] === $unitMeta['type']) {
                $compatibleUnits[] = $unit;
            }
        }

        return $compatibleUnits;
    }

    /**
     * Convert a quantity between two units.
     * Returns null if units are incompatible (different types).
     */
    public static function convertBetweenUnits(float $quantity, string $fromUnit, string $toUnit): ?float
    {
        $from = self::normalizeUnit($fromUnit);
        $to = self::normalizeUnit($toUnit);

        if (!$from || !$to) {
            return null;
        }

        $fromMeta = self::UNIT_FACTORS[$from] ?? null;
        $toMeta = self::UNIT_FACTORS[$to] ?? null;

        if (!$fromMeta || !$toMeta) {
            return null;
        }

        // Units must be of the same type (weight, volume, or count)
        if ($fromMeta['type'] !== $toMeta['type']) {
            return null;
        }

        // If same unit, no conversion needed
        if ($from === $to) {
            return $quantity;
        }

        // Convert: quantity * (fromFactor / toFactor)
        return ($quantity * $fromMeta['factor']) / $toMeta['factor'];
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
}
