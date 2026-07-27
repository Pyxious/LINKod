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
            ? $worker->assignments()->with('project.request')->get()
            : collect();

        $notifications = $user->notifications()->latest('sent_at')->take(10)->get();
        $unreadCount   = $user->notifications()->where('is_read', false)->count();

        return view('worker.dashboard', compact('user', 'assignments', 'notifications', 'unreadCount'));
    }
}
