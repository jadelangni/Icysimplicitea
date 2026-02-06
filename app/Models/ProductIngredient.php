<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductIngredient extends Pivot
{
    protected $table = 'product_ingredients';

    protected $fillable = [
        'product_id',
        'ingredient_id',
        'quantity_required',
        'unit'
    ];

    protected $casts = [
        'quantity_required' => 'decimal:2'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
