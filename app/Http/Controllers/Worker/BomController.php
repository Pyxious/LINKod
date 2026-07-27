<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\BillOfMaterials;
use App\Models\Materials;
use App\Models\Project;
use Illuminate\Http\Request;

class BomController extends Controller
{
    public function store(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,material_id',
            'items.*.qty'         => 'required|numeric|min:0.01',
        ]);

        $project = Project::findOrFail($projectId);
        $worker = auth()->user()->staff?->worker;

        abort_unless(
            $worker && $project->workers->contains('worker_id', $worker->worker_id),
            403
        );

        $staff = auth()->user()->staff;

        foreach ($validated['items'] as $item) {
            $material = Materials::find($item['material_id']);
            BillOfMaterials::create([
                'project_id'  => $project->project_id,
                'material_id' => $item['material_id'],
                'qty'         => $item['qty'],
                'total_cost'  => $material->unit_cost * $item['qty'],
                'created_by'  => $staff?->staff_id,
                'date_approved' => null, // Needs Admin approval
            ]);
        }

        \App\Models\ProjectHistory::create([
            'project_id'      => $project->project_id,
            'previous_status' => $project->current_status,
            'current_status'  => 'On Hold',
            'updated_at'      => now(),
            'updated_by'      => auth()->id(),
        ]);

        if ($project->request_id) {
            $serviceRequest = \App\Models\ServiceRequest::find($project->request_id);
            if ($serviceRequest) {
                \App\Models\RequestHistory::create([
                    'request_id'      => $serviceRequest->request_id,
                    'previous_status' => $serviceRequest->current_status,
                    'current_status'  => 'On Hold',
                    'updated_at'      => now(),
                    'updated_by'      => auth()->id(),
                ]);
            }
        }

        return redirect()->route('worker.job-orders.show', $projectId)
            ->with('success', 'Material request submitted. Project is now On Hold until materials arrive.');
    }
}
