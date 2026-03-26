<?php

namespace App\Http\Controllers;

use App\Models\BranchSession;
use App\Models\StaffAttendance;
use App\Models\User;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CrewSessionController extends Controller
{
    /**
     * Crew check-in: another employee checks in on the same device.
     * Supports QR token (primary) and email/password (fallback).
     * Creates a BranchSession without changing the authenticated user.
     */
    public function checkIn(Request $request)
    {
        $cashier = Auth::user();

        if (!$cashier || !$cashier->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in with a branch assigned.',
            ], 403);
        }

        // QR token check-in
        if ($request->filled('qr_token')) {
            $request->validate(['qr_token' => 'required|string']);
            $crewUser = User::where('qr_token', $request->qr_token)->first();

            if (!$crewUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code.',
                ], 401);
            }
        } else {
            // Email/password check-in
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
            $crewUser = User::where('email', $request->email)->first();

            if (!$crewUser || !Hash::check($request->password, $crewUser->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password.',
                ], 401);
            }
        }

        if (!$crewUser->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This account has been deactivated.',
            ], 403);
        }

        if ($crewUser->id === $cashier->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are already logged in as the cashier.',
            ], 400);
        }

        if ($crewUser->branch_id !== $cashier->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'This employee belongs to a different branch.',
            ], 403);
        }

        // Check if already checked in
        if (BranchSession::hasActiveSession($crewUser->id)) {
            return response()->json([
                'success' => false,
                'message' => $crewUser->name . ' is already checked in.',
            ], 400);
        }

        // Create branch session as crew (not cashier)
        $session = BranchSession::startSession($cashier->branch_id, $crewUser->id);

        // Auto clock-in
        if ($crewUser->role === 'cashier' && !StaffAttendance::isUserClockedIn($crewUser->id)) {
            StaffAttendance::recordAttendance(
                $crewUser,
                'clock_in',
                null,
                $request->ip(),
                null,
                null,
                'Auto clock-in on crew check-in'
            );
        }

        // Log activity
        UserActivityLog::logActivity(
            $crewUser,
            'crew_check_in',
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'success' => true,
            'message' => $crewUser->name . ' has checked in as Crew!',
            'crew_name' => $crewUser->name,
        ]);
    }

    /**
     * Crew check-out: crew member checks out from the same device.
     * Ends their BranchSession without affecting the cashier's auth session.
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'session_id' => 'required|integer|exists:branch_sessions,id',
        ]);

        $cashier = Auth::user();
        $session = BranchSession::where('id', $request->session_id)
            ->where('branch_id', $cashier->branch_id)
            ->where('is_active', true)
            ->where('is_cashier', false)
            ->with('user')
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found or already ended.',
            ], 404);
        }

        $crewUser = $session->user;

        // Verify crew identity via QR token or email/password
        if ($request->filled('qr_token')) {
            $verifiedUser = User::where('qr_token', $request->qr_token)->first();
            if (!$verifiedUser || $verifiedUser->id !== $crewUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR code does not match this crew member.',
                ], 403);
            }
        } elseif ($request->filled('email')) {
            $request->validate(['password' => 'required|string']);
            if ($request->email !== $crewUser->email || !Hash::check($request->password, $crewUser->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credentials do not match this crew member.',
                ], 403);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Identity verification required (QR scan or email/password).',
            ], 400);
        }

        // End the session
        $session->endSession();

        // Auto clock-out
        if ($crewUser->role === 'cashier' && StaffAttendance::isUserClockedIn($crewUser->id)) {
            StaffAttendance::recordAttendance(
                $crewUser,
                'clock_out',
                null,
                $request->ip(),
                null,
                null,
                'Auto clock-out on crew check-out'
            );
        }

        // Log activity
        UserActivityLog::logActivity(
            $crewUser,
            'crew_check_out',
            $request->ip(),
            $request->userAgent()
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $crewUser->name . ' has been checked out.',
            ]);
        }

        return back()->with('success', $crewUser->name . ' has been checked out.');
    }

    /**
     * Get active crew sessions for the current branch.
     */
    public function activeCrew()
    {
        $user = Auth::user();

        if (!$user->branch_id) {
            return response()->json(['crew' => []]);
        }

        $crew = BranchSession::where('branch_id', $user->branch_id)
            ->where('is_active', true)
            ->where('is_cashier', false)
            ->with('user:id,name')
            ->get()
            ->map(fn($s) => [
                'session_id' => $s->id,
                'name' => $s->user->name,
                'checked_in_at' => $s->logged_in_at->timezone('Asia/Manila')->format('h:i A'),
            ]);

        return response()->json(['crew' => $crew]);
    }
}
