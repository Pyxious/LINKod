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
            403,
            'Only assigned team members are authorized to prepare and submit a List of Materials.'
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
                'date_approved' => null, // Pending verification & approval
            ]);

            $addedItems++;
        }

        if ($addedItems > 0) {
            $prevProjectStatus = $project->current_status;
            $newStatus = 'Awaiting Verification of Bill of Materials';
            $roleTitle = ($worker && $worker->isTeamLeader()) ? 'Team Leader' : 'Worker';
            $workerName = auth()->user()->first_name . ' ' . auth()->user()->last_name;
            $projectTitle = $project->request?->title ?? "Project #{$project->project_id}";

            $project->update(['current_status' => $newStatus]);

            \App\Models\ProjectHistory::create([
                'project_id'      => $project->project_id,
                'previous_status' => $prevProjectStatus,
                'current_status'  => $newStatus,
                'remarks'         => "{$roleTitle} prepared and submitted List of Materials for verification.",
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            if ($project->request_id) {
                $serviceRequest = \App\Models\ServiceRequest::find($project->request_id);
                if ($serviceRequest) {
                    $prevReqStatus = $serviceRequest->current_status;
                    $serviceRequest->update([
                        'current_status' => $newStatus,
                        'bom_status'     => 'awaiting_admin',
                    ]);

                    \App\Models\RequestHistory::create([
                        'request_id'      => $serviceRequest->request_id,
                        'previous_status' => $prevReqStatus,
                        'current_status'  => $newStatus,
                        'remarks'         => "{$roleTitle} prepared and submitted List of Materials for verification.",
                        'updated_at'      => now(),
                        'updated_by'      => auth()->id(),
                    ]);
                }
            }

            // If submitted by a regular worker, alert the Team Leader to review
            if (!$worker->isTeamLeader()) {
                $teamLeader = $project->workers->first(fn($w) => $w->isTeamLeader());
                $tlUserId = $teamLeader?->staff?->user_id ?? $teamLeader?->user?->user_id;
                if ($tlUserId) {
                    $this->notifications->send(
                        $tlUserId,
                        'bom_requested',
                        'Materials Submitted by Crew Member',
                        "{$workerName} submitted materials for \"{$projectTitle}\". Review and verify before submitting to client.",
                        route('worker.job-orders.show', $project->project_id, false)
                    );
                }
            }

            // Notify Admins about material request requiring verification
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $this->notifications->send(
                    $admin->user_id,
                    'bom_requested',
                    'List of Materials Submitted',
                    "{$roleTitle} {$workerName} submitted a List of Materials for \"{$projectTitle}\".",
                    route('admin.bom.show', $project->project_id, false)
                );
            }
        }

        return redirect()->route('worker.job-orders.show', $projectId)
            ->with('success', 'Materials requested successfully. Status updated to Awaiting Verification of List of Materials.');
    }

    /**
     * Team Leader reviews & verifies the List of Materials and submits to client for approval.
     */
    public function verifyAndSubmitToClient(Request $request, int $projectId)
    {
        try {
            $project = Project::with(['workers', 'request.client.user', 'billOfMaterials'])->findOrFail($projectId);
            $worker = auth()->user()->staff?->worker;

            abort_unless(
                $worker && $project->workers->contains('worker_id', $worker->worker_id) && $worker->isTeamLeader(),
                403,
                'Only the assigned Team Leader is authorized to verify and submit the List of Materials to the client.'
            );

            if ($project->request && !$project->request->isScheduleApproved()) {
                return redirect()->back()->with('error', 'Action cannot be performed until the client has approved the scheduled date.');
            }

            if ($project->billOfMaterials()->count() === 0) {
                return redirect()->back()->with('error', 'No materials found to submit to client.');
            }

            $prevStatus = $project->current_status;
            $newStatus = 'BOM Verified (Awaiting Client Approval)';
            $remarks = 'Team Leader verified the List of Materials and submitted it for client approval.';

            $project->update(['current_status' => $newStatus]);

            \App\Models\ProjectHistory::create([
                'project_id'      => $project->project_id,
                'previous_status' => $prevStatus,
                'current_status'  => $newStatus,
                'remarks'         => $remarks,
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            if ($project->request_id) {
                $serviceRequest = \App\Models\ServiceRequest::find($project->request_id);
                if ($serviceRequest) {
                    $prevReqStatus = $serviceRequest->current_status;
                    $serviceRequest->update([
                        'current_status' => $newStatus,
                        'bom_status'     => 'awaiting_client',
                    ]);

                    \App\Models\RequestHistory::create([
                        'request_id'      => $serviceRequest->request_id,
                        'previous_status' => $prevReqStatus,
                        'current_status'  => $newStatus,
                        'remarks'         => $remarks,
                        'updated_at'      => now(),
                        'updated_by'      => auth()->id(),
                    ]);
                }
            }

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Team Leader verified List of Materials for project #{$project->project_id} and submitted to client",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            $projectTitle = $project->request?->title ?? "Project #{$project->project_id}";

            // Notify Client (in-app + email notification)
            if ($project->client?->user_id) {
                $this->notifications->bomVerifiedAwaitingClient(
                    $project->client->user_id,
                    $projectTitle,
                    $project->request_id ?? $project->project_id
                );
            }

            // Notify Admins
            $admins = User::where('role', 'admin')->get();
            $tlName = auth()->user()->first_name . ' ' . auth()->user()->last_name;
            foreach ($admins as $admin) {
                $this->notifications->send(
                    $admin->user_id,
                    'bom_verified',
                    'List of Materials Verified by Team Leader',
                    "Team Leader {$tlName} verified the List of Materials for \"{$projectTitle}\" and submitted it for client approval.",
                    route('admin.requests.show', $project->request_id ?? $project->project_id, false)
                );
            }

            return redirect()->route('worker.job-orders.show', $projectId)
                ->with('success', 'List of Materials verified successfully and submitted to the client for approval.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error submitting materials to client: ' . $e->getMessage());
        }
    }

    /**
     * Team Leader approves List of Materials on behalf of the client.
     * Transitions status to "Awaiting Materials" until materials arrive.
     */
    public function teamLeaderApprove(Request $request, int $projectId)
    {
        try {
            $project = Project::with(['workers', 'request.client.user', 'billOfMaterials'])->findOrFail($projectId);
            $worker = auth()->user()->staff?->worker;

            abort_unless(
                $worker && $project->workers->contains('worker_id', $worker->worker_id) && $worker->isTeamLeader(),
                403,
                'Only the assigned Team Leader is authorized to approve the List of Materials on behalf of the client.'
            );

            if ($project->request && !$project->request->isScheduleApproved()) {
                return redirect()->back()->with('error', 'Action cannot be performed until the client has approved the scheduled date.');
            }

            // Mark all project BOM items as approved
            $project->billOfMaterials()->whereNull('date_approved')->update([
                'date_approved' => now()->toDateString(),
                'fulfilled_by'  => auth()->user()->staff?->staff_id,
            ]);

            $targetStatus = 'Awaiting Materials';
            $remarks = 'Team Leader approved the List of Materials on behalf of the client. Awaiting materials procurement/delivery before work commences.';

            \App\Models\ProjectHistory::create([
                'project_id'      => $project->project_id,
                'previous_status' => $project->current_status,
                'current_status'  => $targetStatus,
                'remarks'         => $remarks,
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            $project->update(['current_status' => $targetStatus]);

            if ($project->request_id) {
                $serviceRequest = \App\Models\ServiceRequest::find($project->request_id);
                if ($serviceRequest) {
                    $prevReqStatus = $serviceRequest->current_status;
                    $serviceRequest->update([
                        'bom_status'     => 'approved',
                        'current_status' => $targetStatus,
                    ]);

                    \App\Models\RequestHistory::create([
                        'request_id'      => $serviceRequest->request_id,
                        'previous_status' => $prevReqStatus,
                        'current_status'  => $targetStatus,
                        'remarks'         => $remarks,
                        'updated_at'      => now(),
                        'updated_by'      => auth()->id(),
                    ]);
                }
            }

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Team Leader approved List of Materials on client's behalf for project #{$project->project_id}",
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
                    'List of Materials Approved on Client\'s Behalf',
                    "Team Leader {$tlName} approved the List of Materials on client's behalf for \"{$projectTitle}\". Status is now Awaiting Materials.",
                    route('admin.requests.show', $project->request_id ?? $project->project_id, false)
                );
            }

            return redirect()->route('worker.job-orders.show', $projectId)
                ->with('success', 'List of Materials approved on client\'s behalf. Status updated to Awaiting Materials. Confirm materials arrival once received to start work.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error approving materials on client\'s behalf: ' . $e->getMessage());
        }
    }
}

