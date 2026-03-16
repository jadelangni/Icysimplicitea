<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'triggered_by',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function triggeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    /**
     * Create a notification for off-schedule clock-in.
     */
    public static function notifyOffScheduleClockIn(User $user, string $scheduleStatus, ?DutySchedule $schedule = null): self
    {
        $phNow = \Carbon\Carbon::now('Asia/Manila');

        if ($scheduleStatus === 'day_off') {
            $title = 'Day Off Clock-In';
            $message = "{$user->name} clocked in at {$phNow->format('h:i A')} on their day off ({$phNow->format('l, M d')}).";
        } elseif ($scheduleStatus === 'off_schedule') {
            $scheduledTime = $schedule 
                ? "{$schedule->formatted_start_time} - {$schedule->formatted_end_time}" 
                : 'N/A';
            $title = 'Off-Schedule Clock-In';
            $message = "{$user->name} clocked in at {$phNow->format('h:i A')} outside their scheduled duty ({$scheduledTime}) on {$phNow->format('l, M d')}.";
        } elseif ($scheduleStatus === 'no_schedule') {
            $title = 'Unscheduled Clock-In';
            $message = "{$user->name} clocked in at {$phNow->format('h:i A')} on {$phNow->format('l, M d')} but has no duty schedule assigned.";
        } else {
            $title = 'Clock-In Alert';
            $message = "{$user->name} clocked in at {$phNow->format('h:i A')} on {$phNow->format('l, M d')}.";
        }

        return self::create([
            'type' => 'off_schedule_clock_in',
            'title' => $title,
            'message' => $message,
            'triggered_by' => $user->id,
        ]);
    }

    /**
     * Create a notification for late clock-in.
     */
    public static function notifyLateClockIn(User $user): self
    {
        $phNow = \Carbon\Carbon::now('Asia/Manila');

        return self::create([
            'type' => 'late_clock_in',
            'title' => 'Late Clock-In',
            'message' => "{$user->name} clocked in late at {$phNow->format('h:i A')} on {$phNow->format('l, M d')}.",
            'triggered_by' => $user->id,
        ]);
    }

    /**
     * Get unread notifications count.
     */
    public static function unreadCount(): int
    {
        return self::where('is_read', false)->count();
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public static function markAllAsRead(): void
    {
        self::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
