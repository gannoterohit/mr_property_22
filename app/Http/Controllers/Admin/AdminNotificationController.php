<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = AdminNotification::latest()->paginate(30);
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'notifications' => $notifications->items(),
                'unread_count' => AdminNotification::where('is_read', false)->count(),
            ]);
        }

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markRead(AdminNotification $notification)
    {
        $notification->markAsRead();

        if ($notification->type === 'contact_inquiry') {
            \App\Models\ContactMessage::where('is_read', false)->update(['is_read' => true]);
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => AdminNotification::where('is_read', false)->count(),
            ]);
        }

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        AdminNotification::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        \App\Models\ContactMessage::where('is_read', false)->update(['is_read' => true]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function unreadCount()
    {
        return response()->json([
            'unread_count' => AdminNotification::where('is_read', false)->count(),
        ]);
    }
}
