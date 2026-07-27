<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest('sent_at')
            ->paginate(20);

        // Mark all as read
        auth()->user()->notifications()->where('is_read', false)->update(['is_read' => true]);

        return view('client.notifications.index', compact('notifications'));
    }

    public function readNotification(int $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->update(['is_read' => true]);

        if ($notification->action_url) {
            try {
                return redirect($notification->action_url);
            } catch (\Exception $e) {
                // Fallback if URL invalid
            }
        }

        return redirect()->route('client.requests.index');
    }

    public function markAllRead()
    {
        auth()->user()->notifications()->where('is_read', false)->update(['is_read' => true]);
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
