<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class BranchSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'is_cashier',
        'logged_in_at',
        'logged_out_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_cashier' => 'boolean',
            'is_active' => 'boolean',
            'logged_in_at' => 'datetime',
            'logged_out_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all active sessions for a branch.
     */
    public static function getActiveSessions(int $branchId)
    {
        return self::where('branch_id', $branchId)
            ->where('is_active', true)
            ->with('user')
            ->orderBy('logged_in_at', 'asc')
            ->get();
    }

    /**
     * Get the active cashier session for a branch.
     */
    public static function getActiveCashier(int $branchId): ?self
    {
        return self::where('branch_id', $branchId)
            ->where('is_active', true)
            ->where('is_cashier', true)
            ->first();
    }

    /**
     * Get active non-cashier (crew) sessions for a branch.
     */
    public static function getActiveCrew(int $branchId)
    {
        return self::where('branch_id', $branchId)
            ->where('is_active', true)
            ->where('is_cashier', false)
            ->with('user')
            ->get();
    }

    /**
     * Check if a user has an active session.
     */
    public static function hasActiveSession(int $userId): bool
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get the active session for a user.
     */
    public static function getActiveSessionForUser(int $userId): ?self
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Start a new branch session for a user.
     * First employee at the branch becomes the cashier.
     */
    public static function startSession(int $branchId, int $userId): self
    {
        // Check if there's already an active cashier at this branch
        $activeCashier = self::getActiveCashier($branchId);
        $isCashier = $activeCashier === null;

        return self::create([
            'branch_id' => $branchId,
            'user_id' => $userId,
            'is_cashier' => $isCashier,
            'logged_in_at' => Carbon::now(),
            'is_active' => true,
        ]);
    }

    /**
     * End this session.
     */
    public function endSession(): void
    {
        $this->update([
            'is_active' => false,
            'logged_out_at' => Carbon::now(),
        ]);
    }

    /**
     * Check if the cashier can logout (all crew must be logged out first).
     */
    public static function canCashierLogout(int $branchId): bool
    {
        $activeCrew = self::where('branch_id', $branchId)
            ->where('is_active', true)
            ->where('is_cashier', false)
            ->count();

        return $activeCrew === 0;
    }

    /**
     * Get all users who were on duty today at a branch (active + already logged out).
     * Uses Asia/Manila timezone to determine "today".
     */
    public static function getTodayDutyStaff(int $branchId)
    {
        $todayStart = Carbon::today('Asia/Manila')->utc();
        $todayEnd = Carbon::tomorrow('Asia/Manila')->utc();

        return self::where('branch_id', $branchId)
            ->where('logged_in_at', '>=', $todayStart)
            ->where('logged_in_at', '<', $todayEnd)
            ->with('user')
            ->orderBy('logged_in_at', 'asc')
            ->get()
            ->unique('user_id');
    }
}
