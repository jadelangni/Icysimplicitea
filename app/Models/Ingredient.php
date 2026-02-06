<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'description',
        'unit',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
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
        return $inventory ? $inventory->status : 'No Stock Data';
    }

    /**
     * Get the status color class for a specific branch.
     */
    public function getStatusColorForBranch(int $branchId): string
    {
        $inventory = $this->inventoryForBranch($branchId);
        return $inventory ? $inventory->status_color : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400';
    }
}
