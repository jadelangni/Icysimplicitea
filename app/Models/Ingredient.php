<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'description',
        'unit',
        'quantity',
        'min_stock_level',
        'is_active'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'min_stock_level' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Get the status of this ingredient based on quantity
     */
    public function getStatusAttribute()
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
    public function getStatusColorAttribute()
    {
        if ($this->quantity <= 0) {
            return 'bg-red-100 text-red-800';
        } elseif ($this->quantity <= $this->min_stock_level) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-green-100 text-green-800';
        }
    }
}
