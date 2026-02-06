<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'is_active',
        'options',
        'product_type'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'options' => 'array',
    ];

    /**
     * Product types:
     * - 'direct': Finished goods (cookies, bottled drinks) - deducts from product inventory
     * - 'composite': Made-to-order items (milk tea) - deducts from ingredients based on recipe
     */
    const TYPE_DIRECT = 'direct';
    const TYPE_COMPOSITE = 'composite';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function salesItems(): HasMany
    {
        return $this->hasMany(SalesItem::class);
    }

    /**
     * Get the ingredients required to make this product (Recipe/BOM).
     * Only applicable for composite products (milk tea, etc.)
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredients')
            ->withPivot('quantity_required', 'unit')
            ->withTimestamps();
    }

    /**
     * Check if this is a direct product (finished goods).
     */
    public function isDirectProduct(): bool
    {
        return $this->product_type === self::TYPE_DIRECT;
    }

    /**
     * Check if this is a composite product (made-to-order).
     */
    public function isCompositeProduct(): bool
    {
        return $this->product_type === self::TYPE_COMPOSITE;
    }

    /**
     * Sync ingredients for this product's recipe.
     * 
     * @param array $ingredients Array of ['ingredient_id' => ['quantity_required' => X, 'unit' => 'Y']]
     */
    public function syncRecipe(array $ingredients): void
    {
        $this->ingredients()->sync($ingredients);
    }
}
