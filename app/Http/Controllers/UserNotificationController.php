<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    /**
     * List unread notifications (Bell Icon dropdown — only unread shown)
     */
    public function index(Request $request)
    {
        $notifications = UserNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->latest()
            ->take(15)
            ->get();

        $unreadCount = $notifications->count();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'notifications' => $notifications,
                'unread_count'  => $unreadCount,
            ]);
        }

        return view('account.notifications', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark a single notification as read and redirect to its link
     */
    public function markRead(UserNotification $notification)
    {
        // Security: Only the owner of this notification can mark it read
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success'      => true,
                'unread_count' => UserNotification::where('user_id', Auth::id())->unread()->count(),
            ]);
        }

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back();
    }

    /**
     * Mark all notifications as read for the logged-in user
     */
    public function markAllRead()
    {
        UserNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success'      => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Return only the unread count (for polling / page load)
     */
    public function unreadCount()
    {
        return response()->json([
            'unread_count' => UserNotification::where('user_id', Auth::id())->unread()->count(),
        ]);
    }
}
