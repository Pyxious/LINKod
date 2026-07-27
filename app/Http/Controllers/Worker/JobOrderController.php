<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\ProjectWorker;
use App\Models\Project;

class JobOrderController extends Controller
{
    public function index()
    {
        $worker = auth()->user()->staff?->worker;

        $assignments = $worker
            ? $worker->assignments()->with('project.request.category', 'project.latestHistory')->get()
            : collect();

        return view('worker.job-orders.index', compact('assignments'));
    }

    public function show(int $projectId)
    {
        $worker  = auth()->user()->staff?->worker;
        $project = Project::with(
            'request.category',
            'billOfMaterials.material',
            'latestHistory',
            'workers'
        )->findOrFail($projectId);

        // Ensure the worker is assigned to this project
        abort_unless(
            $worker && $project->workers->contains('worker_id', $worker->worker_id),
            403
        );

        $materials = \App\Models\Materials::orderBy('material_name')->get();
        return view('worker.job-orders.show', compact('project', 'materials'));
    }
}
