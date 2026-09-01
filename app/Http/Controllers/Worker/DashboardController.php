<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $worker = $user->staff?->worker;

        $allAssignments = $worker
            ? $worker->assignments()
                ->with(['project.request.category', 'project.latestHistory', 'project.histories'])
                ->get()
            : collect();

        // 1. Pending Tasks (Needs Attention): Pending or On Hold
        $pendingCount = $allAssignments->filter(function($a) {
            $status = $a->project?->current_status;
            return in_array($status, ['Pending', 'On Hold']);
        })->count();

        // 2. In Progress (Currently working)
        $inProgressCount = $allAssignments->filter(function($a) {
            $status = $a->project?->current_status;
            return $status === 'In Progress';
        })->count();

        // 3. Completed Today (Great job!)
        $today = Carbon::today();
        $completedTodayCount = $allAssignments->filter(function($a) use ($today) {
            $project = $a->project;
            if (!$project) return false;

            $status = $project->current_status;
            if (!in_array($status, ['Completed', 'Pending Verification'])) {
                return false;
            }

            // Check if any Completed or Pending Verification history entry occurred today
            $compHistory = $project->histories?->first(function($h) use ($today) {
                return in_array($h->current_status, ['Completed', 'Pending Verification'])
                    && $h->updated_at
                    && Carbon::parse($h->updated_at)->isSameDay($today);
            });

            if ($compHistory) {
                return true;
            }

            // Fallback: check project updated_at if isSameDay
            if ($project->updated_at && Carbon::parse($project->updated_at)->isSameDay($today)) {
                return true;
            }

            return false;
        })->count();

        // 4. Active assignments for the task list on dashboard (Pending, On Hold, In Progress)
        $assignments = $allAssignments
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
            ->values();

        $notifications = $user->notifications()->latest('sent_at')->take(10)->get();
        $unreadCount   = $user->notifications()->where('is_read', false)->count();

        return view('worker.dashboard', compact(
            'user',
            'assignments',
            'pendingCount',
            'inProgressCount',
            'completedTodayCount',
            'notifications',
            'unreadCount'
        ));
    }
}
