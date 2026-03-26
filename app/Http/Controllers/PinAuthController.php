<?php

namespace App\Http\Controllers;

use App\Models\BranchSession;
use App\Models\StaffAttendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\UserActivityLog;

class PinAuthController extends Controller
{
    /**
     * Show the PIN login page for quick cashier switching.
     */
    public function showPinLogin()
    {
        $branchId = session('last_branch_id', 1);
        $users = User::where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereNotNull('pin')
            ->get(['id', 'name', 'role']);

        return view('auth.pin-login', compact('users', 'branchId'));
    }

    /**
     * Authenticate user via PIN.
     */
    public function authenticatePin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'pin' => 'required|string|min:4|max:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::find($request->user_id);

        if (!$user || !$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or inactive',
            ], 404);
        }

        if (!$user->pin) {
            return response()->json([
                'success' => false,
                'message' => 'PIN not set for this user. Please use email login.',
            ], 403);
        }

        if (!$user->verifyPin($request->pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid PIN',
            ], 401);
        }

        // Log out current user if any and auto clock-out
        if (Auth::check()) {
            $previousUser = Auth::user();
            UserActivityLog::logActivity($previousUser, 'logout_pin_switch', $request->ip(), $request->userAgent());

            // Auto clock-out previous cashier/employee
            if ($previousUser->role === 'cashier' && StaffAttendance::isUserClockedIn($previousUser->id)) {
                StaffAttendance::recordAttendance(
                    $previousUser,
                    'clock_out',
                    null,
                    $request->ip(),
                    null,
                    null,
                    'Auto clock-out on PIN switch'
                );
            }
        }

        // Login the user
        Auth::login($user);
        $user->recordPinLogin();

        // Log the activity
        UserActivityLog::logActivity($user, 'login_pin', $request->ip(), $request->userAgent());

        // Auto clock-in for cashiers/employees
        if ($user->role === 'cashier' && !StaffAttendance::isUserClockedIn($user->id)) {
            StaffAttendance::recordAttendance(
                $user,
                'clock_in',
                null,
                $request->ip(),
                null,
                null,
                'Auto clock-in on PIN login'
            );
        }

        // Start branch session
        if ($user->branch_id && $user->role === 'cashier' && !BranchSession::hasActiveSession($user->id)) {
            BranchSession::startSession($user->branch_id, $user->id);
        }

        // Store branch ID in session
        session(['last_branch_id' => $user->branch_id]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'redirect_url' => route('pos.index'),
        ]);
    }

    /**
     * Show PIN setup form for current user.
     */
    public function showSetupPin()
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(403);
        }

        $hasPin = !empty($user->pin);
        
        return view('auth.setup-pin', compact('hasPin'));
    }

    /**
     * Save or update PIN for current user.
     */
    public function savePin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'pin' => 'required|string|min:4|max:6|confirmed|regex:/^[0-9]+$/',
        ], [
            'pin.regex' => 'PIN must contain only numbers.',
            'pin.min' => 'PIN must be at least 4 digits.',
            'pin.max' => 'PIN must not exceed 6 digits.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        if (!$user instanceof User) {
            abort(403);
        }

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Set the PIN
        $user->setPin($request->pin);

        // Log the activity
        UserActivityLog::logActivity($user, 'pin_updated', $request->ip(), $request->userAgent());

        return back()->with('success', 'PIN has been set successfully. You can now use quick PIN login.');
    }

    /**
     * Remove PIN for current user.
     */
    public function removePin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        if (!$user instanceof User) {
            abort(403);
        }

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Remove the PIN
        $user->pin = null;
        $user->save();

        // Log the activity
        UserActivityLog::logActivity($user, 'pin_removed', $request->ip(), $request->userAgent());

        return back()->with('success', 'PIN has been removed.');
    }

    /**
     * Admin: Set PIN for a user.
     */
    public function adminSetPin(Request $request, User $user)
    {
        $authUser = Auth::user();
        if (!$authUser instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Only admins can set PINs for other users
        if (!$authUser->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'pin' => 'required|string|min:4|max:6|regex:/^[0-9]+$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid PIN format',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->setPin($request->pin);

        // Log the activity
        UserActivityLog::logActivity(
            $authUser,
            'admin_set_pin_for_user_' . $user->id, 
            $request->ip(), 
            $request->userAgent()
        );

        return response()->json([
            'success' => true,
            'message' => 'PIN set successfully for ' . $user->name,
        ]);
    }
}
