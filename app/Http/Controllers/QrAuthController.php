<?php

namespace App\Http\Controllers;

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
                'message' => 'Invalid QR code. Please try again or contact your supervisor.',
            ], 404);
        }

        // Check if user is a supervisor or cashier
        if (!$user->isSupervisor() && !$user->isCashier()) {
            return response()->json([
                'success' => false,
                'message' => 'QR login is only available for supervisors and cashiers.',
            ], 403);
        }

        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact your supervisor.',
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

            return response()->json([
                'success' => true,
                'message' => 'Welcome, ' . $user->name . '! You have been logged in successfully.',
                'user' => [
                    'name' => $user->name,
                    'role' => ucfirst($user->role),
                    'branch' => $user->branch?->name,
                ],
                'redirect' => route('dashboard'),
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

        // Generate QR token if not exists
        if (!$user->qr_token) {
            $user->generateQrToken();
        }

        return view('qr-auth.my-qrcode', compact('user'));
    }

    /**
     * Generate a new QR code for the user.
     */
    public function regenerateQrCode()
    {
        $user = Auth::user();
        $user->generateQrToken();

        return redirect()->route('qr.my-qrcode')->with('success', 'Your QR code has been regenerated successfully.');
    }

    /**
     * Get QR code image as SVG.
     */
    public function getQrCodeImage(Request $request)
    {
        $user = Auth::user();

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
        // Only owners can view other users' QR codes
        if (!Auth::user()->isOwner()) {
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
        // Only owners can regenerate other users' QR codes
        if (!Auth::user()->isOwner()) {
            abort(403);
        }

        $user->generateQrToken();

        return redirect()->route('qr.user-qrcode', $user)->with('success', 'QR code has been regenerated for ' . $user->name);
    }
}
