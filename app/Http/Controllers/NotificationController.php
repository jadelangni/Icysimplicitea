<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of admin notifications.
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all'); // all, unread, read

        $query = AdminNotification::with('triggeredByUser')
            ->orderBy('created_at', 'desc');

        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'read') {
            $query->where('is_read', true);
        }

        $notifications = $query->paginate(20);
        $unreadCount = AdminNotification::unreadCount();

        return view('notifications.index', compact('notifications', 'unreadCount', 'filter'));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(AdminNotification $notification)
    {
        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        AdminNotification::markAllAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Get unread notification count (AJAX).
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => AdminNotification::unreadCount(),
        ]);
    }
}
