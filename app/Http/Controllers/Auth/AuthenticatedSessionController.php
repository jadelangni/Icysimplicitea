<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AdminNotification;
use App\Models\BranchSession;
use App\Models\StaffAttendance;
use App\Models\UserActivityLog;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Carbon\Carbon;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the employee login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Display the admin login view.
     */
    public function createAdmin(): View
    {
        return view('auth.admin-login');
    }

    /**
     * Handle an incoming authentication request (employee login).
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Reject admin users from employee login
        if ($user->role === 'admin') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin.login')->with('error', 'Please use the admin login page.');
        }

        $request->session()->regenerate();

        // Log the login activity
        UserActivityLog::logActivity(
            $user,
            'login',
            $request->ip(),
            $request->userAgent()
        );

        // Auto clock-in for cashiers/employees
        if ($user->role === 'cashier') {
            $alreadyClockedIn = StaffAttendance::isUserClockedIn($user->id);
            if (!$alreadyClockedIn) {
                StaffAttendance::recordAttendance(
                    $user,
                    'clock_in',
                    null,
                    $request->ip(),
                    null,
                    null,
                    'Auto clock-in on login'
                );
            }

            // Start branch session - first employee becomes the designated cashier
            if ($user->branch_id && !BranchSession::hasActiveSession($user->id)) {
                $session = BranchSession::startSession($user->branch_id, $user->id);
                $roleLabel = $session->is_cashier ? 'Cashier' : 'Crew';

                // Notify admin about the branch session
                AdminNotification::create([
                    'type' => 'branch_session',
                    'title' => "Employee Logged In ({$roleLabel})",
                    'message' => "{$user->name} logged in as {$roleLabel} at " . ($user->branch->name ?? 'branch') . " on " . Carbon::now('Asia/Manila')->format('M d, Y h:i A') . ".",
                    'triggered_by' => $user->id,
                ]);
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Handle an incoming admin authentication request.
     */
    public function storeAdmin(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Reject non-admin users from admin login
        if ($user->role !== 'admin') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin.login')->with('error', 'This login is for admin accounts only.');
        }

        $request->session()->regenerate();

        // Log the login activity
        UserActivityLog::logActivity(
            $user,
            'login',
            $request->ip(),
            $request->userAgent()
        );

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Prepare logout - enforce branch session logout order and daily report printing.
     */
    public function prepareLogout(Request $request): RedirectResponse
    {
        $isAdmin = false;
        if (Auth::check()) {
            $user = Auth::user();
            $isAdmin = $user->role === 'admin';

            if ($user->role === 'cashier' && $user->branch_id) {
                $branchSession = BranchSession::getActiveSessionForUser($user->id);

                if ($branchSession) {
                    // If this user is the designated cashier, they must be the last to leave
                    if ($branchSession->is_cashier) {
                        if (!BranchSession::canCashierLogout($user->branch_id)) {
                            // There are still crew members logged in
                            return redirect()->back()->with('error', 'You are the cashier. Other employees must log out first before you can log out.');
                        }

                        // Cashier is last one - redirect to daily report (auto-print)
                        return redirect()->route('reports.cashier-logout-report');
                    }

                    // Non-cashier crew member - end their session and proceed with logout
                    $branchSession->endSession();

                    AdminNotification::create([
                        'type' => 'branch_session',
                        'title' => 'Crew Member Logged Out',
                        'message' => "{$user->name} (Crew) logged out from " . ($user->branch->name ?? 'branch') . " on " . Carbon::now('Asia/Manila')->format('M d, Y h:i A') . ".",
                        'triggered_by' => $user->id,
                    ]);
                }
            }

            // Perform logout for non-cashier or cashier with no branch session
            UserActivityLog::logActivity(
                $user,
                'logout',
                $request->ip(),
                $request->userAgent()
            );

            if ($user->role === 'cashier') {
                $isClockedIn = StaffAttendance::isUserClockedIn($user->id);
                if ($isClockedIn) {
                    StaffAttendance::recordAttendance(
                        $user,
                        'clock_out',
                        null,
                        $request->ip(),
                        null,
                        null,
                        'Auto clock-out on logout'
                    );
                }
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect($isAdmin ? route('admin.login') : '/');
    }

    /**
     * Destroy an authenticated session (final logout after daily report print).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $isAdmin = false;
        if (Auth::check()) {
            $user = Auth::user();
            $isAdmin = $user->role === 'admin';

            // End branch session if active
            if ($user->role === 'cashier' && $user->branch_id) {
                $branchSession = BranchSession::getActiveSessionForUser($user->id);
                if ($branchSession) {
                    $roleLabel = $branchSession->is_cashier ? 'Cashier' : 'Crew';
                    $branchSession->endSession();

                    // Record daily shift summary for admin
                    $todayDutyStaff = BranchSession::getTodayDutyStaff($user->branch_id);
                    $staffNames = $todayDutyStaff->map(fn($s) => $s->user->name . ($s->is_cashier ? ' (Cashier)' : ' (Crew)'))->join(', ');

                    $todaySales = Sale::whereDate('created_at', Carbon::today())
                        ->where('branch_id', $user->branch_id)
                        ->sum('total_amount');

                    AdminNotification::create([
                        'type' => 'daily_shift_summary',
                        'title' => 'Branch Shift Ended - ' . ($user->branch->name ?? 'Branch'),
                        'message' => "{$user->name} ({$roleLabel}) completed their shift at " . ($user->branch->name ?? 'branch') . ". Today's staff: {$staffNames}. Total branch sales: ₱" . number_format($todaySales, 2) . ". Shift ended: " . Carbon::now('Asia/Manila')->format('M d, Y h:i A') . ".",
                        'triggered_by' => $user->id,
                    ]);
                }
            }

            UserActivityLog::logActivity(
                $user,
                'logout',
                $request->ip(),
                $request->userAgent()
            );

            if ($user->role === 'cashier') {
                $isClockedIn = StaffAttendance::isUserClockedIn($user->id);
                if ($isClockedIn) {
                    StaffAttendance::recordAttendance(
                        $user,
                        'clock_out',
                        null,
                        $request->ip(),
                        null,
                        null,
                        'Auto clock-out on logout'
                    );
                }
            }
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect($isAdmin ? route('admin.login') : '/');
    }
}
