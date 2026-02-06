<?php

namespace App\Http\Controllers;

use App\Models\PermissionOverride;
use App\Models\Sale;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PermissionOverrideController extends Controller
{
    /**
     * Display all permission override requests.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Only admins can view override requests
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $status = $request->get('status', 'all');
        $branchId = $request->get('branch_id', $user->branch_id);
        $startDate = $request->get('start_date', today()->subDays(7)->format('Y-m-d'));
        $endDate = $request->get('end_date', today()->format('Y-m-d'));

        $query = PermissionOverride::with(['requestedBy', 'approvedBy', 'branch', 'sale'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $overrides = $query->orderBy('created_at', 'desc')->paginate(30);

        // Get pending count for notification
        $pendingCount = PermissionOverride::where('branch_id', $user->branch_id)
            ->where('status', 'pending')
            ->notExpired()
            ->count();

        // Get branches for filter (admins only)
        $branches = $user->isAdmin() 
            ? \App\Models\Branch::where('is_active', true)->get() 
            : collect();

        return view('permission-overrides.index', compact('overrides', 'pendingCount', 'branches', 'branchId', 'status', 'startDate', 'endDate'));
    }

    /**
     * Get pending overrides for real-time notifications.
     */
    public function getPending()
    {
        $user = Auth::user();
        
        if (!$user->canApproveOverrides()) {
            return response()->json(['overrides' => [], 'count' => 0]);
        }

        $overrides = PermissionOverride::with(['requestedBy', 'sale'])
            ->where('branch_id', $user->branch_id)
            ->where('status', 'pending')
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'overrides' => $overrides,
            'count' => $overrides->count(),
        ]);
    }

    /**
     * Request a new permission override.
     */
    public function request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|string|in:' . implode(',', array_keys(PermissionOverride::RESTRICTED_ACTIONS)),
            'sale_id' => 'nullable|exists:sales,id',
            'original_amount' => 'nullable|numeric|min:0',
            'new_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();

        // Check if user already has permission
        if ($user->canPerformRestrictedAction($request->action)) {
            return response()->json([
                'success' => true,
                'message' => 'You already have permission to perform this action',
                'has_permission' => true,
            ]);
        }

        $sale = $request->sale_id ? Sale::find($request->sale_id) : null;

        $override = PermissionOverride::request(
            $user,
            $request->action,
            $sale,
            $request->original_amount,
            $request->new_amount,
            $request->discount_percent,
            $request->reason,
            30 // expires in 30 minutes
        );

        // Log activity
        UserActivityLog::logActivity($user, 'permission_request_' . $request->action, $request->ip(), $request->userAgent());

        return response()->json([
            'success' => true,
            'message' => 'Permission request submitted. Waiting for manager approval.',
            'override' => $override,
        ]);
    }

    /**
     * Approve a permission override.
     */
    public function approve(Request $request, PermissionOverride $override)
    {
        $user = Auth::user();
        
        if (!$user->canApproveOverrides()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to approve overrides',
            ], 403);
        }

        // Void sales require admin approval
        if ($override->action === 'void_sale' && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the admin can approve void sale requests',
            ], 403);
        }

        if ($override->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request has already been ' . $override->status,
            ], 400);
        }

        // Check if expired
        if ($override->expires_at && $override->expires_at->isPast()) {
            $override->update(['status' => 'expired']);
            return response()->json([
                'success' => false,
                'message' => 'This request has expired',
            ], 400);
        }

        $override->approve($user);

        // Log activity
        UserActivityLog::logActivity($user, 'permission_approved_' . $override->action, $request->ip(), $request->userAgent());

        return response()->json([
            'success' => true,
            'message' => 'Request approved',
            'override' => $override->fresh(),
        ]);
    }

    /**
     * Deny a permission override.
     */
    public function deny(Request $request, PermissionOverride $override)
    {
        $user = Auth::user();
        
        if (!$user->canApproveOverrides()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to deny overrides',
            ], 403);
        }

        if ($override->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request has already been ' . $override->status,
            ], 400);
        }

        $override->deny($user, $request->get('reason', 'Denied by manager'));

        // Log activity
        UserActivityLog::logActivity($user, 'permission_denied_' . $override->action, $request->ip(), $request->userAgent());

        return response()->json([
            'success' => true,
            'message' => 'Request denied',
            'override' => $override->fresh(),
        ]);
    }

    /**
     * Check if a user has permission (or approved override) for an action.
     */
    public function check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|string',
            'sale_id' => 'nullable|exists:sales,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
            ], 422);
        }

        $user = Auth::user();

        // Check native permission
        if ($user->canPerformRestrictedAction($request->action)) {
            return response()->json([
                'success' => true,
                'has_permission' => true,
                'source' => 'role',
            ]);
        }

        // Check for approved override
        $override = PermissionOverride::where('requested_by', $user->id)
            ->where('action', $request->action)
            ->where('status', 'approved')
            ->notExpired()
            ->when($request->sale_id, function($q) use ($request) {
                $q->where('sale_id', $request->sale_id);
            })
            ->first();

        if ($override && $override->isValid()) {
            return response()->json([
                'success' => true,
                'has_permission' => true,
                'source' => 'override',
                'override_id' => $override->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'has_permission' => false,
        ]);
    }

    /**
     * Quick approve using manager PIN.
     */
    public function quickApprove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'manager_id' => 'required|exists:users,id',
            'manager_pin' => 'required|string',
            'action' => 'required|string|in:' . implode(',', array_keys(PermissionOverride::RESTRICTED_ACTIONS)),
            'sale_id' => 'nullable|exists:sales,id',
            'original_amount' => 'nullable|numeric|min:0',
            'new_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $manager = \App\Models\User::find($request->manager_id);

        // Verify manager's PIN
        if (!$manager->verifyPin($request->manager_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid manager PIN',
            ], 401);
        }

        // Verify manager has permission
        if (!$manager->canApproveOverrides()) {
            return response()->json([
                'success' => false,
                'message' => 'Selected user is not authorized to approve overrides',
            ], 403);
        }

        // Void sales require admin
        if ($request->action === 'void_sale' && !$manager->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the admin can approve void sale requests',
            ], 403);
        }

        $user = Auth::user();
        $sale = $request->sale_id ? Sale::find($request->sale_id) : null;

        // Create and immediately approve the override
        $override = PermissionOverride::create([
            'requested_by' => $user->id,
            'approved_by' => $manager->id,
            'branch_id' => $user->branch_id,
            'sale_id' => $sale?->id,
            'action' => $request->action,
            'status' => 'approved',
            'original_amount' => $request->original_amount,
            'new_amount' => $request->new_amount,
            'discount_percent' => $request->discount_percent,
            'reason' => $request->reason,
            'requested_at' => now(),
            'resolved_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ]);

        // Log activities
        UserActivityLog::logActivity($user, 'permission_quick_request_' . $request->action, $request->ip(), $request->userAgent());
        UserActivityLog::logActivity($manager, 'permission_quick_approved_' . $request->action, $request->ip(), $request->userAgent());

        return response()->json([
            'success' => true,
            'message' => 'Override approved by ' . $manager->name,
            'override' => $override,
        ]);
    }
}
