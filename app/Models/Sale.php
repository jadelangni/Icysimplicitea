<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'receipt_number',
        'branch_id',
        'user_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'change_amount',
        'payment_method',
        'status'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salesItems(): HasMany
    {
        return $this->hasMany(SalesItem::class);
    }

    public function generateReceiptNumber(): string
    {
        $branch = $this->branch;
        $branchCode = strtoupper(substr($branch->name, 0, 3));
        $date = now()->format('Ymd');
        $sequence = Sale::where('branch_id', $this->branch_id)
            ->whereDate('created_at', now())
            ->count() + 1;
        
        return $branchCode . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
