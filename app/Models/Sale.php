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
        'status',
        'inventory_synced'
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
        $location = $branch
            ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $branch->name))
            : 'LOCATION';
        $date = now()->format('Ymd');

        $attempts = 0;
        do {
            $random = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $receiptNumber = $location . '-' . $date . '-' . $random;
            $exists = Sale::where('receipt_number', $receiptNumber)->exists();
            $attempts++;
        } while ($exists && $attempts < 10);

        return $receiptNumber;
    }
}
