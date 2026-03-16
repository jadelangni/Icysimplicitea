<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StaffAttendance extends Model
{
    use HasFactory;

    protected $table = 'staff_attendance';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'branch_id',
        'type',
        'selfie_path',
        'ip_address',
        'notes',
        'latitude',
        'longitude',
        'recorded_at',
        'is_late',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_late' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the attendance record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch associated with this attendance.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope for clock-in records.
     */
    public function scopeClockIn($query)
    {
        return $query->where('type', 'clock_in');
    }

    /**
     * Scope for clock-out records.
     */
    public function scopeClockOut($query)
    {
        return $query->where('type', 'clock_out');
    }

    /**
     * Scope for today's records.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('recorded_at', today());
    }

    /**
     * Scope for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Check if user is currently clocked in.
     */
    public static function isUserClockedIn($userId): bool
    {
        $lastRecord = self::where('user_id', $userId)
            ->whereDate('recorded_at', today())
            ->orderBy('recorded_at', 'desc')
            ->first();

        return $lastRecord && $lastRecord->type === 'clock_in';
    }

    /**
     * Get user's last attendance record.
     */
    public static function getLastRecord($userId): ?self
    {
        return self::where('user_id', $userId)
            ->orderBy('recorded_at', 'desc')
            ->first();
    }

    /**
     * Check if the current PH time is within allowed clock-in hours (6:00 AM - 8:00 PM).
     */
    public static function isWithinAllowedClockInHours(): bool
    {
        $phNow = Carbon::now('Asia/Manila');
        $startTime = Carbon::createFromTime(6, 0, 0, 'Asia/Manila');
        $endTime = Carbon::createFromTime(20, 0, 0, 'Asia/Manila');

        return $phNow->between($startTime, $endTime);
    }

    /**
     * Check if a clock-in at the current PH time is considered late (after 8:00 AM).
     */
    public static function isLateClockIn(): bool
    {
        $phNow = Carbon::now('Asia/Manila');
        $lateThreshold = Carbon::createFromTime(8, 0, 0, 'Asia/Manila');

        return $phNow->greaterThan($lateThreshold);
    }

    /**
     * Record a clock-in or clock-out.
     */
    public static function recordAttendance(
        User $user, 
        string $type, 
        ?string $selfiePath = null,
        ?string $ipAddress = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $notes = null
    ): self {
        $isLate = false;

        // Check if this is a clock-in and determine if it's late
        if ($type === 'clock_in') {
            $isLate = self::isLateClockIn();
        }

        return self::create([
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'type' => $type,
            'selfie_path' => $selfiePath,
            'ip_address' => $ipAddress,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'notes' => $notes,
            'recorded_at' => now(),
            'is_late' => $isLate,
        ]);
    }

    /**
     * Calculate total hours worked for a user on a given date.
     */
    public static function calculateHoursWorked($userId, $date): float
    {
        $records = self::where('user_id', $userId)
            ->whereDate('recorded_at', $date)
            ->orderBy('recorded_at')
            ->get();

        $totalMinutes = 0;
        $clockInTime = null;

        foreach ($records as $record) {
            if ($record->type === 'clock_in') {
                $clockInTime = $record->recorded_at;
            } elseif ($record->type === 'clock_out' && $clockInTime) {
                $totalMinutes += $clockInTime->diffInMinutes($record->recorded_at);
                $clockInTime = null;
            }
        }

        return round($totalMinutes / 60, 2);
    }
}
