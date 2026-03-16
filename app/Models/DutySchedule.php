<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DutySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_day_off',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_day_off' => 'boolean',
        ];
    }

    /**
     * Day names for display.
     */
    public const DAY_NAMES = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the day name (e.g., "Monday").
     */
    public function getDayNameAttribute(): string
    {
        return self::DAY_NAMES[$this->day_of_week] ?? 'Unknown';
    }

    /**
     * Get formatted start time (e.g., "06:00 AM").
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return \Carbon\Carbon::createFromFormat('H:i:s', $this->start_time)->format('h:i A');
    }

    /**
     * Get formatted end time (e.g., "02:00 PM").
     */
    public function getFormattedEndTimeAttribute(): string
    {
        return \Carbon\Carbon::createFromFormat('H:i:s', $this->end_time)->format('h:i A');
    }

    /**
     * Check if a given time falls within this schedule.
     */
    public function isWithinSchedule(\Carbon\Carbon $time): bool
    {
        if ($this->is_day_off) {
            return false;
        }

        $start = \Carbon\Carbon::createFromFormat('H:i:s', $this->start_time);
        $end = \Carbon\Carbon::createFromFormat('H:i:s', $this->end_time);

        $checkTime = $time->copy()->setDate($start->year, $start->month, $start->day);
        $start = $start->setDate($checkTime->year, $checkTime->month, $checkTime->day);
        $end = $end->setDate($checkTime->year, $checkTime->month, $checkTime->day);

        return $checkTime->between($start, $end);
    }

    /**
     * Get the schedule for a user on a specific day.
     */
    public static function getScheduleForDay(int $userId, int $dayOfWeek): ?self
    {
        return self::where('user_id', $userId)
            ->where('day_of_week', $dayOfWeek)
            ->first();
    }

    /**
     * Check if a user is scheduled to work on a given day/time.
     * Returns: 'on_schedule', 'off_schedule', 'day_off', 'no_schedule'
     */
    public static function checkScheduleStatus(int $userId, \Carbon\Carbon $dateTime): string
    {
        $dayOfWeek = $dateTime->dayOfWeek; // 0=Sun, 6=Sat
        $schedule = self::getScheduleForDay($userId, $dayOfWeek);

        if (!$schedule) {
            return 'no_schedule';
        }

        if ($schedule->is_day_off) {
            return 'day_off';
        }

        if ($schedule->isWithinSchedule($dateTime)) {
            return 'on_schedule';
        }

        return 'off_schedule';
    }
}
