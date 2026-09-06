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
            $worker && $project->workers->contains('worker_id', $worker->worker_id) && $worker->isTeamLeader(),
            403,
            'Only Team Leaders are authorized to prepare and submit a Bill of Materials.'
        );

        if ($project->request && !$project->request->isScheduleApproved()) {
            return redirect()->back()->with('error', 'Materials cannot be requested until the client has approved the scheduled date.');
        }

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
            $newStatus = 'Awaiting Verification of Bill of Materials';

            \App\Models\ProjectHistory::create([
                'project_id'      => $project->project_id,
                'previous_status' => $project->current_status,
                'current_status'  => $newStatus,
                'remarks'         => 'Team Leader prepared and submitted Bill of Materials for GSO Admin pricing and verification.',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            if ($project->request_id) {
                $serviceRequest = \App\Models\ServiceRequest::find($project->request_id);
                if ($serviceRequest) {
                    $serviceRequest->update(['bom_status' => 'awaiting_admin']);

                    \App\Models\RequestHistory::create([
                        'request_id'      => $serviceRequest->request_id,
                        'previous_status' => $serviceRequest->current_status,
                        'current_status'  => $newStatus,
                        'remarks'         => 'Team Leader prepared and submitted Bill of Materials for GSO Admin pricing and verification.',
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
                    'Bill of Materials Awaiting Verification',
                    "Team Leader {$workerName} submitted a Bill of Materials for \"{$projectTitle}\". Review and set prices before forwarding to client.",
                    route('admin.bom.show', $project->project_id, false)
                );
            }
        }

        return redirect()->route('worker.job-orders.show', $projectId)
            ->with('success', 'Materials requested successfully. Status updated to Awaiting Verification of Bill of Materials.');
    }

    /**
     * Team Leader Direct Override: Confirm on-site direct cash/materials handed by client
     */
    public function teamLeaderApprove(Request $request, int $projectId)
    {
        try {
            $project = Project::with(['workers', 'request.client.user', 'billOfMaterials'])->findOrFail($projectId);
            $worker = auth()->user()->staff?->worker;

            abort_unless(
                $worker && $project->workers->contains('worker_id', $worker->worker_id) && $worker->isTeamLeader(),
                403,
                'Only the assigned Team Leader is authorized to confirm direct on-site client materials.'
            );

            if ($project->request && !$project->request->isScheduleApproved()) {
                return redirect()->back()->with('error', 'Action cannot be performed until the client has approved the scheduled date.');
            }

            // Mark all project BOM items as approved
            $project->billOfMaterials()->whereNull('date_approved')->update([
                'date_approved' => now()->toDateString(),
                'fulfilled_by'  => auth()->user()->staff?->staff_id,
            ]);

            $remarks = 'Team Leader confirmed on-site direct materials/cash provided by client. Fast-tracked to In Progress.';

            \App\Models\ProjectHistory::create([
                'project_id'      => $project->project_id,
                'previous_status' => $project->current_status,
                'current_status'  => 'In Progress',
                'remarks'         => $remarks,
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            if ($project->request_id) {
                $serviceRequest = \App\Models\ServiceRequest::find($project->request_id);
                if ($serviceRequest) {
                    $serviceRequest->update(['bom_status' => 'approved']);

                    \App\Models\RequestHistory::create([
                        'request_id'      => $serviceRequest->request_id,
                        'previous_status' => $serviceRequest->current_status,
                        'current_status'  => 'In Progress',
                        'remarks'         => $remarks,
                        'updated_at'      => now(),
                        'updated_by'      => auth()->id(),
                    ]);
                }
            }

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Team Leader confirmed direct materials on-site for project #{$project->project_id}",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            // Notify Admins
            $admins = User::where('role', 'admin')->get();
            $tlName = auth()->user()->first_name . ' ' . auth()->user()->last_name;
            $projectTitle = $project->request?->title ?? "Project #{$project->project_id}";
            foreach ($admins as $admin) {
                $this->notifications->send(
                    $admin->user_id,
                    'bom_client_approved',
                    'Materials Confirmed On-Site',
                    "Team Leader {$tlName} confirmed on-site direct materials/cash from client for \"{$projectTitle}\". Work is now In Progress.",
                    route('admin.requests.show', $project->request_id ?? $project->project_id, false)
                );
            }

            return redirect()->route('worker.job-orders.show', $projectId)
                ->with('success', 'On-site materials confirmed! Project status updated to In Progress.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error confirming materials: ' . $e->getMessage());
        }
    }
}

