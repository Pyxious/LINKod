<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\Worker;
use Illuminate\Http\Request;

class TaskProgressController extends Controller
{
    public function update(Request $request, int $projectId)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:In Progress,Completed',
                'proof'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);

            $worker  = auth()->user()->staff?->worker;
            $project = Project::findOrFail($projectId);

            abort_unless(
                $worker && $project->workers->contains('worker_id', $worker->worker_id),
                403
            );

            if ($validated['status'] === 'Completed' && !$request->hasFile('proof')) {
                return redirect()->back()->with('error', 'Proof of completion is required.');
            }

            $proofPath = null;
            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('proofs', 'public');
            }

            $previousStatus = $project->current_status;

            $actualStatus = $validated['status'] === 'Completed' ? 'Pending Verification' : $validated['status'];

            ProjectHistory::create([
                'project_id'       => $project->project_id,
                'previous_status'  => $previousStatus,
                'current_status'   => $actualStatus,
                'proof_attachment' => $proofPath,
                'updated_at'       => now(),
                'updated_by'       => auth()->id(),
            ]);

            // Sync status to parent ServiceRequest so client & admin tracking updates out of 'On Hold'
            if ($project->request_id) {
                $serviceRequest = \App\Models\ServiceRequest::find($project->request_id);
                if ($serviceRequest) {
                    \App\Models\RequestHistory::create([
                        'request_id'      => $serviceRequest->request_id,
                        'previous_status' => $serviceRequest->current_status,
                        'current_status'  => $actualStatus,
                        'updated_at'      => now(),
                        'updated_by'      => auth()->id(),
                    ]);
                }
            }

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Worker updated project #{$project->project_id} status to '{$actualStatus}'",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            // If completed by worker, notify admins to verify
            if ($validated['status'] === 'Completed') {
                $worker->update(['is_available' => true]);
                
                $notificationService = new \App\Services\NotificationService();
                $admins = \App\Models\User::where('role', 'admin')->get();
                $projectTitle = $project->request?->title ?? "Project #{$project->project_id}";
                
                foreach ($admins as $admin) {
                    $notificationService->taskCompleted($admin->user_id, $projectTitle, $project->request_id ?? $project->project_id);
                }
            }

            return redirect()->route('worker.job-orders.show', $projectId)
                ->with('success', "Task status updated to \"{$validated['status']}\".");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating task status: ' . $e->getMessage());
        }
    }
}
