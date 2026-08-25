<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Project;
use App\Models\Worker;
use App\Models\Notification;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRequests    = ServiceRequest::count();
        $requestsToday    = ServiceRequest::whereDate('submitted_at', today())->count();

        $totalWorkers     = Worker::whereHas('staff.user', fn($q) => $q->where('role', 'worker'))->count();
        $availableWorkers = Worker::whereHas('staff.user', fn($q) => $q->where('role', 'worker'))->where('is_available', true)->count();
        $availablePct     = $totalWorkers > 0 ? round(($availableWorkers / $totalWorkers) * 100) : 0;

        // Task status breakdown from project_history
        $statuses = ['Pending', 'On Hold', 'In Progress', 'Pending Verification', 'Completed', 'Cancelled'];
        $taskStatus = [];
        foreach ($statuses as $status) {
            $taskStatus[$status] = Project::whereHas('latestHistory', fn($q) =>
                $q->where('current_status', $status)
            )->count();
        }

        $activeTasks        = $taskStatus['In Progress'] ?? 0;
        $completedTasks     = $taskStatus['Completed'] ?? 0;
        $completionRate     = $totalRequests > 0
            ? round(($completedTasks / $totalRequests) * 100)
            : 0;

        $completedThisMonth = ServiceRequest::whereHas('latestHistory', fn($q) =>
            $q->where('current_status', 'Completed')->whereMonth('updated_at', now()->month)
        )->count();

        // Client request progress counts
        $requestProgress = [
            'Submitted'  => ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'Submitted'))->count(),
            'Approved'   => ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'Approved'))->count(),
            'On Hold'   => ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'On Hold'))->count(),
            'In Progress'=> ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'In Progress'))->count(),
            'Completed'  => ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'Completed'))->count(),
        ];

        // Notifications
        $user = auth()->user();
        $notifications = $user ? $user->notifications()->latest('sent_at')->take(10)->get() : collect();
        $unreadCount   = $user ? $user->notifications()->where('is_read', false)->count() : 0;

        return view('admin.dashboard', compact(
            'totalRequests', 'requestsToday', 'activeTasks', 'availableWorkers', 'totalWorkers',
            'availablePct', 'completionRate', 'completedThisMonth', 'taskStatus',
            'requestProgress', 'notifications', 'unreadCount'
        ));
    }

    public function notificationsIndex(Request $request)
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

        return view('admin.notifications.index', compact('notifications', 'totalCount', 'requestCount', 'messageCount', 'type'));
    }

    public function readNotification(int $id)

    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->update(['is_read' => true]);

        $url = $notification->action_url;

        if ($url) {
            $path = parse_url($url, PHP_URL_PATH) ?? $url;

            // If action_url points to admin.requests.show with project_id instead of request_id
            if (str_contains($path, '/admin/requests/')) {
                $parts = explode('/admin/requests/', $path);
                if (isset($parts[1]) && is_numeric($parts[1])) {
                    $targetId = (int)$parts[1];
                    $requestExists = \App\Models\ServiceRequest::where('request_id', $targetId)->exists();
                    if (!$requestExists) {
                        $project = \App\Models\Project::where('project_id', $targetId)->first();
                        if ($project && $project->request_id) {
                            return redirect()->route('admin.requests.show', $project->request_id);
                        }
                    }
                }
            }

            try {
                return redirect($path);
            } catch (\Exception $e) {
                // Fallback
            }
        }

        return redirect()->route('admin.requests.index');
    }

    public function markAllRead()
    {
        auth()->user()->notifications()->where('is_read', false)->update(['is_read' => true]);
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
