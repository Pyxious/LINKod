<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\ProjectWorker;
use App\Models\Project;
use Illuminate\Http\Request;

class JobOrderController extends Controller
{
    public function index(Request $request)
    {
        $worker = auth()->user()->staff?->worker;

        $statusFilter   = $request->get('status', '');
        $priorityFilter = $request->get('priority', '');
        $search         = strtolower(trim($request->get('search', '')));

        if (!$worker) {
            $assignments = collect();
            return view('worker.job-orders.index', compact('assignments', 'statusFilter', 'priorityFilter', 'search'));
        }

        $allAssignments = $worker->assignments()
            ->with('project.request.category', 'project.latestHistory')
            ->get();

        $assignments = $allAssignments->filter(function($a) use ($statusFilter, $priorityFilter, $search) {
            $project = $a->project;
            if (!$project) return false;

            $req    = $project->request;
            $status = $project->current_status;
            $prio   = ucfirst(strtolower($req?->priority ?? 'Low'));

            // 1. Status Filtering (Default = active tasks only, excluding Completed)
            if (empty($statusFilter) || $statusFilter === 'active') {
                if ($status === 'Completed') {
                    return false;
                }
            } elseif ($statusFilter === 'Completed') {
                if ($status !== 'Completed') {
                    return false;
                }
            } elseif ($statusFilter !== 'all') {
                if (strtolower($status) !== strtolower($statusFilter)) {
                    return false;
                }
            }

            // 2. Priority Filtering
            if (!empty($priorityFilter) && strtolower($priorityFilter) !== 'all') {
                if (strtolower($prio) !== strtolower($priorityFilter)) {
                    return false;
                }
            }

            // 3. Search Query (Title, Location, Requisition Code/ID)
            if (!empty($search)) {
                $title = strtolower($req?->title ?? '');
                $loc   = strtolower($req?->location ?? '');
                $reqId = (string)($req?->request_id ?? '');
                if (!str_contains($title, $search) && !str_contains($loc, $search) && !str_contains($reqId, $search)) {
                    return false;
                }
            }

            return true;
        })->sort(function($a, $b) {
            $prioA = strtolower($a->project?->request?->priority ?? 'low');
            $prioB = strtolower($b->project?->request?->priority ?? 'low');

            $isHighA = ($prioA === 'high');
            $isHighB = ($prioB === 'high');

            // High priority requests are always at the top
            if ($isHighA && !$isHighB) {
                return -1;
            }
            if (!$isHighA && $isHighB) {
                return 1;
            }

            // Both High OR both Med/Low -> strictly FCFS (earliest assignment first)
            $idA = $a->assignment_id ?? $a->project_id ?? 0;
            $idB = $b->assignment_id ?? $b->project_id ?? 0;

            if ($idA !== $idB) {
                return $idA <=> $idB;
            }

            return 0;
        })->values();

        return view('worker.job-orders.index', compact('assignments', 'statusFilter', 'priorityFilter', 'search'));

    }

    public function show(int $projectId)
    {
        $worker  = auth()->user()->staff?->worker;
        $project = Project::with(
            'request.category',
            'request.client.user',
            'billOfMaterials.material',
            'latestHistory',
            'workers'
        )->findOrFail($projectId);

        // Ensure the worker is assigned to this project
        abort_unless(
            $worker && $project->workers->contains('worker_id', $worker->worker_id),
            403
        );

        // Mark viewed conversation messages as read
        if (auth()->check() && $project->request_id) {
            \App\Models\RequestMessage::where('request_id', $project->request_id)
                ->where('sender_id', '!=', auth()->id())
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $materials = \App\Models\Materials::orderBy('material_name')->get();
        return view('worker.job-orders.show', compact('project', 'materials'));

    }
}
