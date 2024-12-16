<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications;

        return response()->json([
            'status' => 'success',
            'notifications' => $notifications,
        ]);
    }

    /**
     * Get all unread notifications for the authenticated user.
     */
    public function unread(Request $request)
    {
        $user = $request->user();
        $unreadNotifications = $user->unreadNotifications;

        return response()->json([
            'status' => 'success',
            'unread_notifications' => $unreadNotifications,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read.',
        ]);
    }
}