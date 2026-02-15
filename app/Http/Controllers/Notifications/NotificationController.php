<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * JSON feed for notification bell dropdown.
     */
    public function feed(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(fn ($n) => [
                'id'      => $n->id,
                'title'   => $n->title,
                'message' => $n->message,
                'is_read' => (bool) $n->is_read,
                'time_ago' => $n->created_at->diffForHumans(),
            ]);

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }
}
