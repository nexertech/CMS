<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    /**
     * Get all notifications for the authenticated house
     */
    public function index(Request $request)
    {
        $house = $request->input('authenticated_house') ?: $request->user();

        if (!$house) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get unread notifications primarily, or all if requested
        $notifications = $house->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $house = $request->input('authenticated_house') ?: $request->user();

        if (!$house) {
             return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notification = $house->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true, 'message' => 'Marked as read']);
        }

        return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $house = $request->input('authenticated_house') ?: $request->user();

        if (!$house) {
             return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $house->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true, 
            'message' => 'All notifications marked as read'
        ]);
    }
}
