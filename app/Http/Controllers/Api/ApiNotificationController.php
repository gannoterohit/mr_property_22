<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class ApiNotificationController extends BaseApiController
{
    /**
     * Get user's in-app notifications (paginated / latest).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = UserNotification::where('user_id', $user->id)
            ->latest()
            ->paginate(max(1, min(50, $request->integer('limit', 20))));

        $unreadCount = UserNotification::where('user_id', $user->id)->unread()->count();

        return $this->sendSuccess([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications->items(),
            'pagination'    => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'total'        => $notifications->total(),
            ]
        ], 'Notifications fetched successfully.');
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, $id)
    {
        $user = $request->user();

        $notification = UserNotification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return $this->sendError('Notification not found.', [], 404);
        }

        $notification->markAsRead();

        $unreadCount = UserNotification::where('user_id', $user->id)->unread()->count();

        return $this->sendSuccess([
            'unread_count' => $unreadCount,
        ], 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request)
    {
        $user = $request->user();

        UserNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->sendSuccess([
            'unread_count' => 0,
        ], 'All notifications marked as read.');
    }

    /**
     * Get unread count only (for mobile app badge).
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $unreadCount = UserNotification::where('user_id', $user->id)->unread()->count();

        return $this->sendSuccess([
            'unread_count' => $unreadCount,
        ], 'Unread count fetched.');
    }
}
