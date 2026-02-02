<?php

namespace App\Http\Controllers;

use App\Models\UserActivityLog;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Display the activity logs.
     */
    public function index(Request $request)
    {
        $query = UserActivityLog::with(['user', 'branch'])
            ->orderBy('created_at', 'desc');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        // Filter by action type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->forBranch($request->branch_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $logs = $query->paginate(25)->withQueryString();

        // Get filter options
        $users = User::where('role', '!=', 'owner')
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
        $branches = Branch::orderBy('name')->get(['id', 'name']);

        // Get summary stats for today
        $today = Carbon::today();
        $todayStats = [
            'logins' => UserActivityLog::logins()->whereDate('created_at', $today)->count(),
            'logouts' => UserActivityLog::logouts()->whereDate('created_at', $today)->count(),
            'active_users' => $this->getActiveUsers(),
        ];

        return view('activity-logs.index', compact('logs', 'users', 'branches', 'todayStats'));
    }

    /**
     * Get currently active users (logged in but not logged out).
     */
    private function getActiveUsers()
    {
        // Get all users who have logged in today and haven't logged out after their last login
        $today = Carbon::today();
        
        return User::whereHas('activityLogs', function ($query) use ($today) {
            $query->where('action', 'login')
                ->whereDate('created_at', $today);
        })->whereDoesntHave('activityLogs', function ($query) use ($today) {
            $query->where('action', 'logout')
                ->whereDate('created_at', $today)
                ->whereRaw('created_at > (SELECT MAX(created_at) FROM user_activity_logs AS ual WHERE ual.user_id = user_activity_logs.user_id AND ual.action = "login" AND DATE(ual.created_at) = ?)', [$today->toDateString()]);
        })->count();
    }

    /**
     * Show activity details for a specific user.
     */
    public function userActivity(Request $request, User $user)
    {
        $query = UserActivityLog::with(['branch'])
            ->forUser($user->id)
            ->orderBy('created_at', 'desc');

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $logs = $query->paginate(25)->withQueryString();

        // Calculate stats for this user
        $stats = [
            'total_logins' => UserActivityLog::forUser($user->id)->logins()->count(),
            'total_logouts' => UserActivityLog::forUser($user->id)->logouts()->count(),
            'last_login' => UserActivityLog::forUser($user->id)->logins()->latest()->first(),
            'last_logout' => UserActivityLog::forUser($user->id)->logouts()->latest()->first(),
        ];

        return view('activity-logs.user', compact('user', 'logs', 'stats'));
    }
}
