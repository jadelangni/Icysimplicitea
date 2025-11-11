<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $table = 'inventory';
    
    protected $fillable = [
        'branch_id',
        'product_id',
        'quantity',
        'min_stock_level'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_stock_level' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock_level;
    }
}
