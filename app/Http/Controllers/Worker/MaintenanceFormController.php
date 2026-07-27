<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class MaintenanceFormController extends Controller
{
    public function create(int $projectId)
    {
        $worker  = auth()->user()->staff?->worker;
        $project = Project::with('request')->findOrFail($projectId);

        abort_unless(
            $worker && $project->workers->contains('worker_id', $worker->worker_id),
            403
        );

        return view('worker.maintenance-form.create', compact('project'));
    }

    public function store(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'work_done'        => 'required|string',
            'materials_used'   => 'nullable|string',
            'recommendations'  => 'nullable|string',
            'completed_at'     => 'required|date',
        ]);

        // Store as project recommendation for now (expandable to separate PM table)
        $project = Project::findOrFail($projectId);
        $project->update([
            'recommendation' => "Work Done: {$validated['work_done']}\n" .
                                "Materials Used: {$validated['materials_used']}\n" .
                                "Recommendations: {$validated['recommendations']}\n" .
                                "Completed At: {$validated['completed_at']}",
        ]);

        return redirect()->route('worker.job-orders.show', $projectId)
            ->with('success', 'Preventive Maintenance Form submitted successfully.');
    }
}
