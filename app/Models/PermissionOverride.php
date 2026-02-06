<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionOverride extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'requested_by',
        'approved_by',
        'branch_id',
        'sale_id',
        'action',
        'status',
        'original_amount',
        'new_amount',
        'discount_percent',
        'reason',
        'denial_reason',
        'requested_at',
        'resolved_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'original_amount' => 'decimal:2',
            'new_amount' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'requested_at' => 'datetime',
            'resolved_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Actions that require manager/owner approval.
     */
    public const RESTRICTED_ACTIONS = [
        'void_sale' => 'Void Sale',
        'apply_discount' => 'Apply Discount',
        'price_override' => 'Price Override',
        'open_drawer_no_sale' => 'Open Drawer (No Sale)',
        'refund' => 'Refund',
        'delete_item' => 'Delete Item from Sale',
    ];

    /**
     * Get the user who requested the override.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved/denied the override.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the branch associated with this override.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the sale associated with this override (if any).
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Scope for pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope for non-expired requests.
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Check if this override is still valid.
     */
    public function isValid(): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Request a new permission override.
     */
    public static function request(
        User $requestedBy,
        string $action,
        ?Sale $sale = null,
        ?float $originalAmount = null,
        ?float $newAmount = null,
        ?float $discountPercent = null,
        ?string $reason = null,
        ?int $expiresInMinutes = 30
    ): self {
        return self::create([
            'requested_by' => $requestedBy->id,
            'branch_id' => $requestedBy->branch_id,
            'sale_id' => $sale?->id,
            'action' => $action,
            'status' => 'pending',
            'original_amount' => $originalAmount,
            'new_amount' => $newAmount,
            'discount_percent' => $discountPercent,
            'reason' => $reason,
            'requested_at' => now(),
            'expires_at' => $expiresInMinutes ? now()->addMinutes($expiresInMinutes) : null,
        ]);
    }

    /**
     * Approve this override.
     */
    public function approve(User $approvedBy, ?string $notes = null): self
    {
        $this->update([
            'approved_by' => $approvedBy->id,
            'status' => 'approved',
            'resolved_at' => now(),
        ]);

        return $this;
    }

    /**
     * Deny this override.
     */
    public function deny(User $deniedBy, ?string $reason = null): self
    {
        $this->update([
            'approved_by' => $deniedBy->id,
            'status' => 'denied',
            'denial_reason' => $reason,
            'resolved_at' => now(),
        ]);

        return $this;
    }

    /**
     * Get action label.
     */
    public function getActionLabelAttribute(): string
    {
        return self::RESTRICTED_ACTIONS[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'denied' => 'red',
            'expired' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Check if user can perform a restricted action.
     */
    public static function canPerformAction(User $user, string $action): bool
    {
        // Admins can perform all actions
        if ($user->isAdmin()) {
            return true;
        }

        // Cashiers cannot perform restricted actions without override
        return false;
    }
}
