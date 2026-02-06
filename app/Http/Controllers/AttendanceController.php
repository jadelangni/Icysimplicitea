<?php

namespace App\Http\Controllers;

use App\Models\StaffAttendance;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Display the attendance terminal/kiosk view.
     */
    public function terminal()
    {
        return view('attendance.terminal');
    }

    /**
     * Show attendance records for the authenticated user.
     */
    public function myAttendance(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $attendance = StaffAttendance::where('user_id', $user->id)
            ->whereBetween('recorded_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('recorded_at', 'desc')
            ->paginate(20);

        $isClockedIn = StaffAttendance::isUserClockedIn($user->id);
        $todayHours = StaffAttendance::calculateHoursWorked($user->id, today());

        return view('attendance.my-attendance', compact('attendance', 'isClockedIn', 'todayHours', 'startDate', 'endDate'));
    }

    /**
     * Show all staff attendance (for admins).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Only admins can view all attendance
        if (!$user->isAdmin()) {
            return redirect()->route('attendance.my-attendance');
        }

        $branchId = $request->get('branch_id', $user->branch_id);
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $userId = $request->get('user_id');

        $query = StaffAttendance::with(['user', 'branch'])
            ->whereBetween('recorded_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $attendance = $query->orderBy('recorded_at', 'desc')->paginate(30);

        // Get staff list for filter
        $staff = User::where('branch_id', $branchId)
            ->where('is_active', true)
            ->get();

        // Get branches for filter (admins only)
        $branches = $user->isAdmin() 
            ? \App\Models\Branch::where('is_active', true)->get() 
            : collect();

        return view('attendance.index', compact('attendance', 'staff', 'branches', 'branchId', 'startDate', 'endDate', 'userId'));
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

        // Record attendance
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

        return response()->json([
            'success' => true,
            'message' => 'Clocked in successfully at ' . $attendance->recorded_at->format('h:i A'),
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
            'message' => 'Clocked out successfully at ' . $attendance->recorded_at->format('h:i A'),
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

        return response()->json([
            'success' => true,
            'is_clocked_in' => $isClockedIn,
            'last_record' => $lastRecord,
            'today_hours' => $todayHours,
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

        return response()->json([
            'success' => true,
            'users' => $users,
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
            \Log::error('Failed to save attendance selfie: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * View selfie for an attendance record.
     */
    public function viewSelfie(StaffAttendance $attendance)
    {
        $user = Auth::user();
        
        // Only allow viewing own selfies or if admin
        if ($attendance->user_id !== $user->id && !$user->isAdmin()) {
            abort(403);
        }

        if (!$attendance->selfie_path || !Storage::disk('public')->exists($attendance->selfie_path)) {
            abort(404, 'Selfie not found');
        }

        return response()->file(Storage::disk('public')->path($attendance->selfie_path));
    }
}
