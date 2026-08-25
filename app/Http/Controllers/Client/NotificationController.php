<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $type = $request->query('type', 'all');
        $user = auth()->user();

        $query = $user->notifications()->latest('sent_at');

        if ($type === 'requests') {
            $query->where('type', '!=', 'new_message');
        } elseif ($type === 'messages') {
            $query->where('type', 'new_message');
        }

        $notifications = $query->paginate(20)->withQueryString();

        $totalCount   = $user->notifications()->count();
        $requestCount = $user->notifications()->where('type', '!=', 'new_message')->count();
        $messageCount = $user->notifications()->where('type', 'new_message')->count();

        // Mark all as read
        $user->notifications()->where('is_read', false)->update(['is_read' => true]);

        return view('client.notifications.index', compact('notifications', 'totalCount', 'requestCount', 'messageCount', 'type'));
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
