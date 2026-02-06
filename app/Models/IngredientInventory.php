<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientInventory extends Model
{
    protected $table = 'ingredient_inventory';
    
    protected $fillable = [
        'ingredient_id',
        'branch_id',
        'quantity',
        'min_stock_level'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'min_stock_level' => 'decimal:2',
    ];

    /**
     * Get the ingredient this inventory record belongs to.
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /**
     * Get the branch this inventory belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Check if this ingredient is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock_level;
    }

    /**
     * Check if this ingredient is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Get the status of this ingredient inventory based on quantity
     */
    public function getStatusAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'Out of Stock';
        } elseif ($this->quantity <= $this->min_stock_level) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }

    /**
     * Get the status color class
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400';
        } elseif ($this->quantity <= $this->min_stock_level) {
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400';
        } else {
            return 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400';
        }
    }

    /**
     * Deduct quantity from this ingredient inventory.
     * Returns the amount actually deducted (may be less if insufficient stock).
     */
    public function deduct(float $amount): float
    {
        $actualDeduction = min($this->quantity, $amount);
        $this->quantity = max(0, $this->quantity - $amount);
        $this->save();
        return $actualDeduction;
    }

    /**
     * Add quantity to this ingredient inventory (restock).
     */
    public function restock(float $amount): void
    {
        $this->quantity += $amount;
        $this->save();
    }
}
