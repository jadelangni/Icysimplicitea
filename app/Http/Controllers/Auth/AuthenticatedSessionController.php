<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
    public function create(Request $request): View|RedirectResponse
    {
        if ($this->shouldShowAdminLoginOnMobile($request)) {
            return redirect()->route('admin.login');
        }

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
     * Route phone browsers to the admin login while preserving desktop access
     * to the employee login and allowing an explicit override.
     */
    protected function shouldShowAdminLoginOnMobile(Request $request): bool
    {
        if ($request->boolean('employee')) {
            return false;
        }

        $mobileHint = $request->header('sec-ch-ua-mobile');
        if ($mobileHint === '?1') {
            return true;
        }

        $userAgent = strtolower($request->userAgent() ?? '');

        return $userAgent !== ''
            && preg_match('/android.+mobile|iphone|ipod|windows phone|blackberry|opera mini|mobile/', $userAgent) === 1;
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
                            $activeCrew = BranchSession::getActiveCrew($user->branch_id)->pluck('user.name')->filter()->values();
                            $crewList = $activeCrew->isNotEmpty() ? (' Active crew: ' . $activeCrew->join(', ')) : '';

                            return redirect()->back()->with('error', 'You are the cashier. Other employees must log out first before you can log out.' . $crewList);
                        }

                        // Cashier is last one - redirect to daily report (auto-print)
                        return redirect()->route('reports.cashier-logout-report');
                    }

                    // Non-cashier crew member - end their session and proceed with logout
                    $branchSession->endSession();

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
                    if ($branchSession->is_cashier && !BranchSession::canCashierLogout($user->branch_id)) {
                        $activeCrew = BranchSession::getActiveCrew($user->branch_id)->pluck('user.name')->filter()->values();
                        $crewList = $activeCrew->isNotEmpty() ? (' Active crew: ' . $activeCrew->join(', ')) : '';

                        return redirect()->back()->with('error', 'You are the cashier. Other employees must log out first before you can log out.' . $crewList);
                    }

                    $roleLabel = $branchSession->is_cashier ? 'Cashier' : 'Crew';
                    $branchSession->endSession();

                    // Record daily shift summary for admin
                    $todayDutyStaff = BranchSession::getTodayDutyStaff($user->branch_id);
                    $staffNames = $todayDutyStaff->map(fn($s) => $s->user->name . ($s->is_cashier ? ' (Cashier)' : ' (Crew)'))->join(', ');

                    $todaySales = Sale::whereDate('created_at', Carbon::today())
                        ->where('branch_id', $user->branch_id)
                        ->sum('total_amount');

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
