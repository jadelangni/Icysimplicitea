<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymongoTransaction extends Model
{
    protected $fillable = [
        'branch_id',
        'user_id',
        'sale_id',
        'payment_method',
        'source_id',
        'payment_id',
        'amount',
        'currency',
        'status',
        'items_snapshot',
        'source_payload',
        'payment_payload',
        'error_message',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'items_snapshot' => 'array',
        'source_payload' => 'array',
        'payment_payload' => 'array',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
