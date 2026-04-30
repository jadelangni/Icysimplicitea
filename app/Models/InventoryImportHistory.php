<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryImportHistory extends Model
{
    protected $fillable = [
        'imported_at',
        'admin_id',
        'ingredient_id',
        'branch_id',
        'supplier',
        'imported_file',
        'previous_qty',
        'added_qty',
        'final_qty',
    ];

    protected $casts = [
        'imported_at' => 'datetime',
        'previous_qty' => 'decimal:2',
        'added_qty' => 'decimal:2',
        'final_qty' => 'decimal:2',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
