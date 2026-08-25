<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\BillOfMaterials;
use App\Models\Materials;
use App\Models\Project;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BomController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function store(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'items'                         => 'required|array|min:1',
            'items.*.material_id'           => 'nullable',
            'items.*.custom_material_name'  => 'nullable|string|max:200',
            'items.*.unit_of_measurement'   => 'nullable|string|max:50',
            'items.*.qty'                   => 'required|numeric|min:0.01',
        ]);

        $project = Project::with(['workers', 'request'])->findOrFail($projectId);
        $worker = auth()->user()->staff?->worker;

        abort_unless(
            $worker && $project->workers->contains('worker_id', $worker->worker_id),
            403
        );

        $staff = auth()->user()->staff;
        $addedItems = 0;

        foreach ($validated['items'] as $item) {
            $materialId = $item['material_id'] ?? null;
            $customName = trim($item['custom_material_name'] ?? '');
            $unit = trim($item['unit_of_measurement'] ?? '') ?: 'pcs';
            $qty = (float)$item['qty'];

            if ($qty <= 0) continue;

            $material = null;

            if ($materialId && $materialId !== 'custom' && is_numeric($materialId)) {
                $material = Materials::find($materialId);
                if ($material && $unit && $unit !== $material->unit_of_measurement) {
                    if (empty($material->unit_of_measurement)) {
                        $material->update(['unit_of_measurement' => $unit]);
                    }
                }
            } elseif (!empty($customName)) {
                // Find or create material by custom name
                $material = Materials::firstOrCreate(
                    ['material_name' => $customName],
                    [
                        'unit_of_measurement' => $unit,
                        'unit_cost' => 0.00,
                    ]
                );
            }

            if (!$material) {
                continue;
            }

            BillOfMaterials::create([
                'project_id'    => $project->project_id,
                'material_id'   => $material->material_id,
                'qty'           => $qty,
                'total_cost'    => 0.00, // Price is set by Admin before approving
                'created_by'    => $staff?->staff_id,
                'date_approved' => null, // Pending Admin pricing & approval
            ]);

            $addedItems++;
        }

        if ($addedItems > 0) {
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

            // Notify Admins about material request requiring pricing & approval
            $admins = User::where('role', 'admin')->get();
            $workerName = auth()->user()->first_name . ' ' . auth()->user()->last_name;
            $projectTitle = $project->request?->title ?? "Project #{$project->project_id}";
            foreach ($admins as $admin) {
                $this->notifications->send(
                    $admin->user_id,
                    'bom_requested',
                    'New Material Request (BOM)',
                    "{$workerName} requested materials for \"{$projectTitle}\". Review and set prices before approving.",
                    route('admin.bom.show', $project->project_id, false)
                );
            }
        }

        return redirect()->route('worker.job-orders.show', $projectId)
            ->with('success', 'Materials requested successfully. Submitted to Admin for pricing and approval.');
    }
}

