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
                ->filter(fn($a) => $a->project && !in_array($a->project->current_status, ['Completed', 'Cancelled']))
                ->sort(function($a, $b) {
                    $prioA = strtolower($a->project?->request?->priority ?? 'low');
                    $prioB = strtolower($b->project?->request?->priority ?? 'low');

                    $isHighA = ($prioA === 'high');
                    $isHighB = ($prioB === 'high');

                    if ($isHighA && !$isHighB) {
                        return -1;
                    }
                    if (!$isHighA && $isHighB) {
                        return 1;
                    }

                    $idA = $a->assignment_id ?? $a->project_id ?? 0;
                    $idB = $b->assignment_id ?? $b->project_id ?? 0;

                    if ($idA !== $idB) {
                        return $idA <=> $idB;
                    }

                    return 0;
                })
                ->values()
            : collect();


        $notifications = $user->notifications()->latest('sent_at')->take(10)->get();
        $unreadCount   = $user->notifications()->where('is_read', false)->count();

        return view('worker.dashboard', compact('user', 'assignments', 'notifications', 'unreadCount'));
    }
}
