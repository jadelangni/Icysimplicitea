<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'branch_id',
    ];

    /**
     * Get the user that owns the activity log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch associated with this activity.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope a query to only include login activities.
     */
    public function scopeLogins($query)
    {
        return $query->where('action', 'login');
    }

    /**
     * Scope a query to only include logout activities.
     */
    public function scopeLogouts($query)
    {
        return $query->where('action', 'logout');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter by branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Log a user activity.
     */
    public static function logActivity(User $user, string $action, ?string $ipAddress = null, ?string $userAgent = null): self
    {
        return self::create([
            'user_id' => $user->id,
            'action' => $action,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'branch_id' => $user->branch_id,
        ]);
    }
}
