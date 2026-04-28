<?php

namespace App\Http\Controllers;

use App\Models\BranchSession;
use App\Models\StaffAttendance;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrAuthController extends Controller
{
    /**
     * Show the QR code scanner page for login/logout.
     */
    public function scanner()
    {
        return view('qr-auth.scanner');
    }

    /**
     * Process QR code scan for login/logout.
     */
    public function process(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
            'action' => 'required|in:login,logout',
        ]);

        $user = User::where('qr_token', $request->qr_token)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code. Please try again or contact your admin.',
            ], 404);
        }

        // Check if user is a cashier (QR login is for cashiers only)
        if (!$user->isCashier()) {
            return response()->json([
                'success' => false,
                'message' => 'QR login is only available for cashiers.',
            ], 403);
        }

        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact your admin.',
            ], 403);
        }

        $action = $request->action;

        if ($action === 'login') {
            // Check if already logged in today
            $lastActivity = UserActivityLog::forUser($user->id)
                ->whereDate('created_at', today())
                ->latest()
                ->first();

            if ($lastActivity && $lastActivity->action === 'login') {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already logged in. Please logout first.',
                ], 400);
            }

            // Log the user in
            Auth::login($user);
            $request->session()->regenerate();

            // Log the login activity
            UserActivityLog::logActivity(
                $user,
                'login',
                $request->ip(),
                $request->userAgent() . ' (QR Scan)'
            );

            // Auto clock-in for cashiers/employees
            if ($user->role === 'cashier' && !StaffAttendance::isUserClockedIn($user->id)) {
                StaffAttendance::recordAttendance(
                    $user,
                    'clock_in',
                    null,
                    $request->ip(),
                    null,
                    null,
                    'Auto clock-in on QR login'
                );
            }

            // Start branch session
            if ($user->branch_id && $user->role === 'cashier' && !BranchSession::hasActiveSession($user->id)) {
                BranchSession::startSession($user->branch_id, $user->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Welcome, ' . $user->name . '! You have been logged in successfully.',
                'user' => [
                    'name' => $user->name,
                    'role' => ucfirst($user->role),
                    'branch' => $user->branch?->name,
                ],
                'redirect' => route('pos.index'),
            ]);
        } else {
            // Logout action
            $lastActivity = UserActivityLog::forUser($user->id)
                ->whereDate('created_at', today())
                ->latest()
                ->first();

            if (!$lastActivity || $lastActivity->action === 'logout') {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not currently logged in.',
                ], 400);
            }

            // Log the logout activity
            UserActivityLog::logActivity(
                $user,
                'logout',
                $request->ip(),
                $request->userAgent() . ' (QR Scan)'
            );

            // Auto clock-out for cashiers/employees
            if ($user->role === 'cashier' && StaffAttendance::isUserClockedIn($user->id)) {
                StaffAttendance::recordAttendance(
                    $user,
                    'clock_out',
                    null,
                    $request->ip(),
                    null,
                    null,
                    'Auto clock-out on QR logout'
                );
            }

            // End branch session
            if ($user->branch_id && $user->role === 'cashier') {
                $branchSession = BranchSession::getActiveSessionForUser($user->id);
                if ($branchSession) {
                    if ($branchSession->is_cashier && !BranchSession::canCashierLogout($user->branch_id)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You are the cashier. Other employees must log out first before you can log out.',
                        ], 403);
                    }
                    $branchSession->endSession();
                }
            }

            // If the scanned user is the current authenticated user, log them out
            if (Auth::check() && Auth::id() === $user->id) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return response()->json([
                'success' => true,
                'message' => 'Goodbye, ' . $user->name . '! You have been logged out successfully.',
                'user' => [
                    'name' => $user->name,
                    'role' => ucfirst($user->role),
                ],
                'redirect' => route('qr.scanner'),
            ]);
        }
    }

    /**
     * Show the user's QR code.
     */
    public function showMyQrCode()
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(403);
        }

        // Generate QR token if not exists
        if (!$user->qr_token) {
            $user->generateQrToken();
        }

        return view('qr-auth.my-qrcode', compact('user'));
    }

    /**
     * Generate a new QR code for the user.
     * Only admins can regenerate QR codes.
     */
    public function regenerateQrCode()
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(403);
        }
        
        // Only admins can regenerate their own QR code
        if (!$user->isAdmin()) {
            return redirect()->route('qr.my-qrcode')
                ->with('error', 'Only administrators can regenerate QR codes. Please contact your admin.');
        }
        
        $user->generateQrToken();

        return redirect()->route('qr.my-qrcode')->with('success', 'Your QR code has been regenerated successfully.');
    }

    /**
     * Get QR code image as SVG.
     */
    public function getQrCodeImage(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            abort(403);
        }

        if (!$user->qr_token) {
            $user->generateQrToken();
        }

        $qrCode = QrCode::size(300)
            ->margin(2)
            ->generate($user->qr_token);

        return response($qrCode)->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Admin: Show QR code for a specific user.
     */
    public function showUserQrCode(User $user)
    {
        $authUser = Auth::user();
        if (!$authUser instanceof User) {
            abort(403);
        }

        // Only admins can view other users' QR codes
        if (!$authUser->isAdmin()) {
            abort(403);
        }

        // Generate QR token if not exists
        if (!$user->qr_token) {
            $user->generateQrToken();
        }

        return view('qr-auth.user-qrcode', compact('user'));
    }

    /**
     * Admin: Regenerate QR code for a specific user.
     */
    public function regenerateUserQrCode(User $user)
    {
        $authUser = Auth::user();
        if (!$authUser instanceof User) {
            abort(403);
        }

        // Only admins can regenerate other users' QR codes
        if (!$authUser->isAdmin()) {
            abort(403);
        }

        $user->generateQrToken();

        return redirect()->route('qr.user-qrcode', $user)->with('success', 'QR code has been regenerated for ' . $user->name);
    }

    /**
     * Switch the current POS user via QR code scan.
     */
    public function switchByQr(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
        ]);

        $targetUser = User::where('qr_token', $request->qr_token)->first();

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code. Please try again.',
            ], 404);
        }

        $currentUser = Auth::user();
        if (!$currentUser instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Must be same branch
        if ($targetUser->branch_id !== $currentUser->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'This employee belongs to a different branch.',
            ], 403);
        }

        // Must be active
        if (!$targetUser->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This account has been deactivated.',
            ], 403);
        }

        // Already the active user
        if ($targetUser->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are already the active user.',
            ], 400);
        }

        // Log the switch
        UserActivityLog::logActivity(
            $targetUser,
            'login',
            $request->ip(),
            $request->userAgent() . ' (QR Switch from ' . $currentUser->name . ')'
        );

        // Switch session
        Auth::login($targetUser);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Switched to ' . $targetUser->name,
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'role' => ucfirst($targetUser->role),
            ],
        ]);
    }
}
