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
        $sort           = $request->get('sort', 'priority');
        $direction      = strtolower($request->get('direction', 'asc'));

        if (!$worker) {
            $assignments = collect();
            return view('worker.job-orders.index', compact('assignments', 'statusFilter', 'priorityFilter', 'search', 'sort', 'direction'));
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
        })->sort(function($a, $b) use ($sort, $direction) {
            $reqA = $a->project?->request;
            $reqB = $b->project?->request;

            // Date sorting
            if ($sort === 'date_desc' || ($sort === 'date' && $direction === 'desc') || ($sort === 'assigned_date' && $direction === 'desc')) {
                $dateA = $a->date_assigned ?? $a->created_at ?? '';
                $dateB = $b->date_assigned ?? $b->created_at ?? '';
                return strcmp((string)$dateB, (string)$dateA);
            }

            if ($sort === 'date_asc' || ($sort === 'date' && $direction === 'asc') || ($sort === 'assigned_date' && $direction === 'asc')) {
                $dateA = $a->date_assigned ?? $a->created_at ?? '';
                $dateB = $b->date_assigned ?? $b->created_at ?? '';
                return strcmp((string)$dateA, (string)$dateB);
            }

            // Title sorting
            if ($sort === 'title_asc' || ($sort === 'title' && $direction === 'asc')) {
                return strcasecmp($reqA?->title ?? '', $reqB?->title ?? '');
            }

            if ($sort === 'title_desc' || ($sort === 'title' && $direction === 'desc')) {
                return strcasecmp($reqB?->title ?? '', $reqA?->title ?? '');
            }

            // Requisition / Project ID sorting
            if ($sort === 'req_no') {
                $idA = (int)($reqA?->request_id ?? $a->project_id ?? 0);
                $idB = (int)($reqB?->request_id ?? $b->project_id ?? 0);
                return $direction === 'desc' ? ($idB <=> $idA) : ($idA <=> $idB);
            }

            // Status sorting
            if ($sort === 'status') {
                $statusA = $a->project?->current_status ?? '';
                $statusB = $b->project?->current_status ?? '';
                return $direction === 'desc' ? strcasecmp($statusB, $statusA) : strcasecmp($statusA, $statusB);
            }

            // Priority sorting (Default: High Priority first)
            $prioA = strtolower($reqA?->priority ?? 'low');
            $prioB = strtolower($reqB?->priority ?? 'low');

            $isHighA = ($prioA === 'high');
            $isHighB = ($prioB === 'high');

            if ($direction === 'desc' || $sort === 'priority_asc') {
                if (!$isHighA && $isHighB) return -1;
                if ($isHighA && !$isHighB) return 1;
            } else {
                if ($isHighA && !$isHighB) return -1;
                if (!$isHighA && $isHighB) return 1;
            }

            // Both High OR both Med/Low -> strictly FCFS (earliest assignment first)
            $idA = $a->assignment_id ?? $a->project_id ?? 0;
            $idB = $b->assignment_id ?? $b->project_id ?? 0;

            if ($idA !== $idB) {
                return $idA <=> $idB;
            }

            return 0;
        })->values();

        return response()
            ->view('worker.job-orders.index', compact('assignments', 'statusFilter', 'priorityFilter', 'search', 'sort', 'direction'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function show(int $projectId)
    {
        $worker  = auth()->user()->staff?->worker;
        $project = Project::with(
            'request.category',
            'request.client.user',
            'billOfMaterials.material',
            'histories',
            'latestHistory',
            'workers',
            'approvedBy.user'
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
