<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $worker = $user->staff?->worker;

        $assignments = $worker
            ? $worker->assignments()
                ->with('project.request.category', 'project.latestHistory')
                ->get()
                ->filter(fn($a) => $a->project && $a->project->current_status !== 'Completed')
                ->sortBy(function($a) {
                    $prio = strtolower($a->project?->request?->priority ?? 'low');
                    return match($prio) {
                        'high' => 1,
                        'medium' => 2,
                        'low' => 3,
                        default => 4
                    };
                })
            : collect();

        $notifications = $user->notifications()->latest('sent_at')->take(10)->get();
        $unreadCount   = $user->notifications()->where('is_read', false)->count();

        return view('worker.dashboard', compact('user', 'assignments', 'notifications', 'unreadCount'));
    }
}
