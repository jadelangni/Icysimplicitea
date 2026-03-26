<?php

namespace App\Http\Controllers;

use App\Models\StaffAttendance;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Models\DutySchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendanceController extends Controller
{


    /**
     * Show attendance records for the authenticated user.
     */
    public function myAttendance(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(403);
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Get all attendance records for the period
        $rawAttendance = StaffAttendance::where('user_id', $user->id)
            ->whereBetween('recorded_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('recorded_at', 'asc')
            ->get();

        // Group into sessions (clock_in + clock_out pairs)
        $sessions = [];
        $currentSession = null;
        
        foreach ($rawAttendance as $record) {
            if ($record->type === 'clock_in') {
                // Start a new session
                $currentSession = [
                    'date' => $record->recorded_at->format('Y-m-d'),
                    'clock_in' => $record->recorded_at,
                    'clock_out' => null,
                    'hours_worked' => null,
                    'is_running' => true,
                ];
            } elseif ($record->type === 'clock_out' && $currentSession) {
                // Close the current session
                $currentSession['clock_out'] = $record->recorded_at;
                $currentSession['is_running'] = false;
                $currentSession['hours_worked'] = $currentSession['clock_in']->diffInMinutes($record->recorded_at) / 60;
                $sessions[] = $currentSession;
                $currentSession = null;
            }
        }
        
        // If there's an unclosed session (still clocked in), add it
        if ($currentSession) {
            $currentSession['hours_worked'] = $currentSession['clock_in']->diffInMinutes(now()) / 60;
            $sessions[] = $currentSession;
        }
        
        // Reverse to show most recent first
        $sessions = array_reverse($sessions);
        
        // Calculate total hours
        $totalHours = array_sum(array_column($sessions, 'hours_worked'));

        $isClockedIn = StaffAttendance::isUserClockedIn($user->id);
        $todayHours = StaffAttendance::calculateHoursWorked($user->id, today());

        return view('attendance.my-attendance', compact('sessions', 'isClockedIn', 'todayHours', 'totalHours', 'startDate', 'endDate'));
    }

    /**
     * Show all staff attendance (for admins).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(403);
        }
        
        // Only admins can view all attendance
        if (!$user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        $branchId = $request->get('branch_id', '');
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $userId = $request->get('user_id');

        // Get attendance records
        $query = StaffAttendance::with(['user', 'branch'])
            ->whereBetween('recorded_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $attendance = $query->orderBy('recorded_at', 'desc')->paginate(30);

        // Get staff list for filter — show ALL active staff regardless of branch
        $staffQuery = User::where('is_active', true);
        if ($branchId) {
            $staffQuery->where('branch_id', $branchId);
        }
        $staff = $staffQuery->get();

        // Build employee attendance summaries for the selected period
        $employeeSummaries = [];
        foreach ($staff as $member) {
            $memberRecords = StaffAttendance::where('user_id', $member->id)
                ->whereBetween('recorded_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->orderBy('recorded_at')
                ->get();

            // Count days present (unique dates with clock_in)
            $daysPresent = $memberRecords->where('type', 'clock_in')
                ->map(fn($r) => $r->recorded_at->format('Y-m-d'))
                ->unique()
                ->count();

            // Count late check-ins
            $lateCount = $memberRecords->where('type', 'clock_in')->where('is_late', true)->count();

            // Calculate total hours worked
            $totalMinutes = 0;
            $clockInTime = null;
            foreach ($memberRecords as $record) {
                if ($record->type === 'clock_in') {
                    $clockInTime = $record->recorded_at;
                } elseif ($record->type === 'clock_out' && $clockInTime) {
                    $totalMinutes += $clockInTime->diffInMinutes($record->recorded_at);
                    $clockInTime = null;
                }
            }

            // Check current clock-in status
            $isClockedIn = StaffAttendance::isUserClockedIn($member->id);

            $employeeSummaries[] = [
                'user' => $member,
                'days_present' => $daysPresent,
                'late_count' => $lateCount,
                'total_hours' => round($totalMinutes / 60, 1),
                'is_clocked_in' => $isClockedIn,
                'record_count' => $memberRecords->count(),
                'last_clock_in' => $memberRecords->where('type', 'clock_in')->last()?->recorded_at,
                'last_clock_out' => $memberRecords->where('type', 'clock_out')->last()?->recorded_at,
            ];
        }

        // Get branches for filter (admins only)
        $branches = $user->isAdmin() 
            ? \App\Models\Branch::where('is_active', true)->get() 
            : collect();

        return view('attendance.index', compact('attendance', 'staff', 'branches', 'branchId', 'startDate', 'endDate', 'userId', 'employeeSummaries'));
    }

    /**
     * Clock in with selfie.
     */
    public function clockIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'pin' => 'required|string',
            'selfie' => 'nullable|string', // Base64 encoded image
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::find($request->user_id);

        // Verify PIN
        if (!$user->verifyPin($request->pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid PIN',
            ], 401);
        }

        // Check if within allowed clock-in hours (8:00 AM - 8:00 PM PH time)
        if (!StaffAttendance::isWithinAllowedClockInHours()) {
            $phNow = Carbon::now('Asia/Manila');
            return response()->json([
                'success' => false,
                'message' => 'Clock-in is only allowed between 6:00 AM and 8:00 PM (Philippine Time). Current PH time: ' . $phNow->format('h:i A'),
            ], 400);
        }

        // Check if already clocked in
        if (StaffAttendance::isUserClockedIn($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already clocked in. Please clock out first.',
            ], 400);
        }

        // Save selfie if provided
        $selfiePath = null;
        if ($request->selfie) {
            $selfiePath = $this->saveSelfie($request->selfie, $user->id, 'clock_in');
        }

        // Record attendance (is_late is automatically determined in the model)
        $attendance = StaffAttendance::recordAttendance(
            $user,
            'clock_in',
            $selfiePath,
            $request->ip(),
            $request->latitude,
            $request->longitude
        );

        // Log activity
        UserActivityLog::logActivity($user, 'clock_in', $request->ip(), $request->userAgent());

        // Build response message
        $message = 'Clocked in successfully at ' . $attendance->recorded_at->setTimezone('Asia/Manila')->format('h:i A');
        if ($attendance->is_late) {
            $message .= ' (LATE - after 8:00 AM)';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_late' => $attendance->is_late,
            'attendance' => $attendance,
        ]);
    }

    /**
     * Clock out with selfie.
     */
    public function clockOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'pin' => 'required|string',
            'selfie' => 'nullable|string', // Base64 encoded image
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::find($request->user_id);

        // Verify PIN
        if (!$user->verifyPin($request->pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid PIN',
            ], 401);
        }

        // Check if clocked in
        if (!StaffAttendance::isUserClockedIn($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not clocked in. Please clock in first.',
            ], 400);
        }

        // Save selfie if provided
        $selfiePath = null;
        if ($request->selfie) {
            $selfiePath = $this->saveSelfie($request->selfie, $user->id, 'clock_out');
        }

        // Record attendance
        $attendance = StaffAttendance::recordAttendance(
            $user,
            'clock_out',
            $selfiePath,
            $request->ip(),
            $request->latitude,
            $request->longitude,
            $request->notes
        );

        // Calculate hours worked today
        $hoursWorked = StaffAttendance::calculateHoursWorked($user->id, today());

        // Log activity
        UserActivityLog::logActivity($user, 'clock_out', $request->ip(), $request->userAgent());

        return response()->json([
            'success' => true,
            'message' => 'Clocked out successfully at ' . $attendance->recorded_at->setTimezone('Asia/Manila')->format('h:i A'),
            'hours_worked' => $hoursWorked,
            'attendance' => $attendance,
        ]);
    }

    /**
     * Get attendance status for a user.
     */
    public function getStatus(Request $request)
    {
        $userId = $request->get('user_id', Auth::id());
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $isClockedIn = StaffAttendance::isUserClockedIn($user->id);
        $lastRecord = StaffAttendance::getLastRecord($user->id);
        $todayHours = StaffAttendance::calculateHoursWorked($user->id, today());

        // Check if clock-in is currently allowed
        $isClockInAllowed = StaffAttendance::isWithinAllowedClockInHours();
        $phNow = Carbon::now('Asia/Manila');

        return response()->json([
            'success' => true,
            'is_clocked_in' => $isClockedIn,
            'last_record' => $lastRecord,
            'today_hours' => $todayHours,
            'clock_in_allowed' => $isClockInAllowed,
            'current_ph_time' => $phNow->format('h:i A'),
        ]);
    }

    /**
     * Get list of users for the attendance terminal.
     */
    public function getUsers(Request $request)
    {
        $branchId = $request->get('branch_id', 1);
        
        $users = User::where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereNotNull('pin')
            ->get(['id', 'name', 'role'])
            ->map(function($user) {
                $user->is_clocked_in = StaffAttendance::isUserClockedIn($user->id);
                return $user;
            });

        // Check if clock-in is currently allowed
        $isClockInAllowed = StaffAttendance::isWithinAllowedClockInHours();
        $phNow = Carbon::now('Asia/Manila');

        return response()->json([
            'success' => true,
            'users' => $users,
            'clock_in_allowed' => $isClockInAllowed,
            'current_ph_time' => $phNow->format('h:i A'),
        ]);
    }

    /**
     * Save base64 selfie to storage.
     */
    private function saveSelfie(string $base64Image, int $userId, string $type): ?string
    {
        try {
            // Remove data URL prefix if present
            $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
            $image = base64_decode($image);

            if (!$image) {
                return null;
            }

            $filename = sprintf(
                'attendance/%d/%s_%s_%s.jpg',
                $userId,
                $type,
                now()->format('Y-m-d'),
                now()->format('His')
            );

            Storage::disk('public')->put($filename, $image);

            return $filename;
        } catch (\Exception $e) {
            Log::error('Failed to save attendance selfie: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * View selfie for an attendance record.
     */
    public function viewSelfie(StaffAttendance $attendance)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(403);
        }
        
        // Only allow viewing own selfies or if admin
        if ($attendance->user_id !== $user->id && !$user->isAdmin()) {
            abort(403);
        }

        if (!$attendance->selfie_path || !Storage::disk('public')->exists($attendance->selfie_path)) {
            abort(404, 'Selfie not found');
        }

        return response()->file(Storage::disk('public')->path($attendance->selfie_path));
    }

    /**
     * Show duty schedule for a user.
     */
    public function showSchedule(User $user)
    {
        $schedules = DutySchedule::where('user_id', $user->id)
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week');

        return view('attendance.schedule', compact('user', 'schedules'));
    }

    /**
     * Update duty schedule for a user.
     */
    public function updateSchedule(Request $request, User $user)
    {
        $request->validate([
            'schedules' => 'required|array',
            'schedules.*.day_of_week' => 'required|integer|min:0|max:6',
            'schedules.*.start_time' => 'nullable|date_format:H:i',
            'schedules.*.end_time' => 'nullable|date_format:H:i',
            'schedules.*.is_day_off' => 'nullable|boolean',
        ]);

        foreach ($request->schedules as $scheduleData) {
            DutySchedule::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'day_of_week' => $scheduleData['day_of_week'],
                ],
                [
                    'start_time' => $scheduleData['is_day_off'] ?? false ? '00:00' : ($scheduleData['start_time'] ?? '06:00'),
                    'end_time' => $scheduleData['is_day_off'] ?? false ? '00:00' : ($scheduleData['end_time'] ?? '14:00'),
                    'is_day_off' => $scheduleData['is_day_off'] ?? false,
                ]
            );
        }

        return redirect()->route('duty-schedules.show', $user)
            ->with('success', "Duty schedule for {$user->name} updated successfully.");
    }

}
