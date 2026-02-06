<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stock Movement Ledger - Tracks all inventory changes with full audit trail.
 * Never mutate - only append new records.
 */
class StockMovement extends Model
{
    // Movement types
    public const TYPE_SALE = 'sale';
    public const TYPE_VOID = 'void';
    public const TYPE_RESTOCK = 'restock';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_TRANSFER_OUT = 'transfer_out';
    public const TYPE_TRANSFER_IN = 'transfer_in';
    public const TYPE_RETURN = 'return';
    public const TYPE_WASTE = 'waste';
    public const TYPE_INITIAL = 'initial';

    // Inventory types
    public const INVENTORY_PRODUCT = 'product';
    public const INVENTORY_INGREDIENT = 'ingredient';

    protected $fillable = [
        'branch_id',
        'inventory_type',      // 'product' or 'ingredient'
        'product_id',          // nullable, for product inventory
        'ingredient_id',       // nullable, for ingredient inventory
        'movement_type',       // sale, void, restock, adjustment, transfer, etc.
        'quantity_before',
        'quantity_change',     // negative for deductions, positive for additions
        'quantity_after',
        'unit',
        'reference_type',      // Sale, Transfer, Adjustment, etc.
        'reference_id',        // ID of the related record
        'reason_code',         // Optional reason code for adjustments
        'notes',               // Optional notes
        'user_id',             // Who performed this action
        'cost_per_unit',       // For FIFO/weighted average calculations
        'total_cost',          // quantity_change * cost_per_unit
    ];

    protected $casts = [
        'quantity_before' => 'decimal:4',
        'quantity_change' => 'decimal:4',
        'quantity_after' => 'decimal:4',
        'cost_per_unit' => 'decimal:4',
        'total_cost' => 'decimal:4',
    ];

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('inventory_type', self::INVENTORY_PRODUCT)
                     ->where('product_id', $productId);
    }

    public function scopeForIngredient($query, int $ingredientId)
    {
        return $query->where('inventory_type', self::INVENTORY_INGREDIENT)
                     ->where('ingredient_id', $ingredientId);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeDeductions($query)
    {
        return $query->where('quantity_change', '<', 0);
    }

    public function scopeAdditions($query)
    {
        return $query->where('quantity_change', '>', 0);
    }

    /**
     * Get the item name (product or ingredient).
     */
    public function getItemNameAttribute(): string
    {
        if ($this->inventory_type === self::INVENTORY_PRODUCT) {
            return $this->product?->name ?? 'Unknown Product';
        }
        return $this->ingredient?->name ?? 'Unknown Ingredient';
    }

    /**
     * Create a stock movement record.
     */
    public static function record(array $data): self
    {
        return self::create($data);
    }
}
