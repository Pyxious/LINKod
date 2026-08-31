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
                'status'           => 'required|in:In Progress,Completed',
                'completion_type'  => 'nullable|string|max:150',
                'nature_of_work'   => 'nullable|string|max:150',
                'recommendation'   => 'nullable|string|max:1000',
                'proof'            => ['nullable', 'file', new \App\Rules\SecureFileUpload(['jpg', 'jpeg', 'png', 'webp', 'pdf'], 10240)],
            ]);

            $worker  = auth()->user()->staff?->worker;
            $project = Project::findOrFail($projectId);

            abort_unless(
                $worker && $project->workers->contains('worker_id', $worker->worker_id),
                403
            );

            if ($validated['status'] === 'In Progress' && !$request->hasFile('proof')) {
                return redirect()->back()->with('error', 'A Before-Work photo is required before setting task to In Progress.');
            }

            if ($validated['status'] === 'Completed' && !$request->hasFile('proof')) {
                return redirect()->back()->with('error', 'An After-Work / proof of completion photo is required.');
            }

            $disk = config('filesystems.default', 'public');
            $proofPath = null;
            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('proofs', $disk);
            }

            $previousStatus = $project->current_status;
            $actualStatus = $validated['status'] === 'Completed' ? 'Pending Verification' : $validated['status'];

            // Guard against duplicate rapid clicks
            if ($previousStatus === $actualStatus) {
                return redirect()->route('worker.job-orders.show', $projectId)
                    ->with('info', "Task status is already {$actualStatus}.");
            }

            // Save nature_of_work and recommendation if completed
            if ($validated['status'] === 'Completed') {
                $completionType = $validated['completion_type'] ?? $request->input('completion_type');
                $natureInput    = $validated['nature_of_work'] ?? $request->input('nature_of_work');

                if ($completionType === 'Inspection Only' || $natureInput === 'Inspection & Assessment Only') {
                    $project->nature_of_work = 'Inspection & Assessment Only';
                }

                if ($request->filled('recommendation')) {
                    $project->recommendation = $request->input('recommendation');
                }
                $project->save();
            }

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
                    $remarks = null;
                    if ($validated['status'] === 'Completed') {
                        $remarks = ($project->nature_of_work ?? 'Completed') . ($project->recommendation ? ' — ' . $project->recommendation : '');
                    }

                    \App\Models\RequestHistory::create([
                        'request_id'      => $serviceRequest->request_id,
                        'previous_status' => $serviceRequest->current_status,
                        'current_status'  => $actualStatus,
                        'remarks'         => $remarks,
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

            // If completed by worker, notify admins to verify and recalculate worker availability
            if ($validated['status'] === 'Completed') {
                $worker->recalculateAvailability();

                
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

    public function syncProgress(Request $request, int $projectId)
    {
        try {
            $validated = $request->validate([
                'status'                => 'required|in:In Progress,Completed',
                'completion_type'       => 'nullable|string|max:150',
                'nature_of_work'        => 'nullable|string|max:150',
                'recommendation'        => 'nullable|string|max:1000',
                'offline_performed_at'  => 'nullable|string',
                'proof'                 => ['nullable', 'file', new \App\Rules\SecureFileUpload(['jpg', 'jpeg', 'png', 'webp', 'pdf'], 10240)],
            ]);

            $worker  = auth()->user()->staff?->worker;
            $project = Project::findOrFail($projectId);

            abort_unless(
                $worker && $project->workers->contains('worker_id', $worker->worker_id),
                403
            );

            if ($validated['status'] === 'In Progress' && !$request->hasFile('proof')) {
                return response()->json(['success' => false, 'message' => 'A Before-Work photo is required before setting task to In Progress.'], 422);
            }

            if ($validated['status'] === 'Completed' && !$request->hasFile('proof')) {
                return response()->json(['success' => false, 'message' => 'An After-Work / proof of completion photo is required.'], 422);
            }

            $disk = config('filesystems.default', 'public');
            $proofPath = null;
            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('proofs', $disk);
            }

            $previousStatus = $project->current_status;
            $actualStatus = $validated['status'] === 'Completed' ? 'Pending Verification' : $validated['status'];

            // Parse offline work timestamp if provided
            $performedAt = now();
            $offlineNote = null;
            if (!empty($validated['offline_performed_at'])) {
                try {
                    $parsedTime = \Carbon\Carbon::parse($validated['offline_performed_at'])->setTimezone(config('app.timezone', 'Asia/Manila'));
                    if ($parsedTime->lessThanOrEqualTo(now())) {
                        $performedAt = $parsedTime;
                        $offlineNote = "Recorded offline at " . $parsedTime->format('M j, Y g:i A') . " • Synced at " . now()->format('g:i A');
                    }
                } catch (\Exception $e) {
                    // fallback to now()
                }
            }

            // Save nature_of_work and recommendation if completed
            if ($validated['status'] === 'Completed') {
                $completionType = $validated['completion_type'] ?? $request->input('completion_type');
                $natureInput    = $validated['nature_of_work'] ?? $request->input('nature_of_work');

                if ($completionType === 'Inspection Only' || $natureInput === 'Inspection & Assessment Only') {
                    $project->nature_of_work = 'Inspection & Assessment Only';
                }

                if ($request->filled('recommendation')) {
                    $project->recommendation = $request->input('recommendation');
                }
                $project->save();
            }

            ProjectHistory::create([
                'project_id'       => $project->project_id,
                'previous_status'  => $previousStatus,
                'current_status'   => $actualStatus,
                'proof_attachment' => $proofPath,
                'updated_at'       => $performedAt,
                'updated_by'       => auth()->id(),
            ]);

            // Sync status to parent ServiceRequest
            if ($project->request_id) {
                $serviceRequest = \App\Models\ServiceRequest::find($project->request_id);
                if ($serviceRequest) {
                    $remarks = null;
                    if ($validated['status'] === 'Completed') {
                        $remarks = ($project->nature_of_work ?? 'Completed') . ($project->recommendation ? ' — ' . $project->recommendation : '');
                    }
                    if ($offlineNote) {
                        $remarks = $remarks ? ($remarks . " [{$offlineNote}]") : "[{$offlineNote}]";
                    }

                    \App\Models\RequestHistory::create([
                        'request_id'      => $serviceRequest->request_id,
                        'previous_status' => $serviceRequest->current_status,
                        'current_status'  => $actualStatus,
                        'remarks'         => $remarks,
                        'updated_at'      => $performedAt,
                        'updated_by'      => auth()->id(),
                    ]);
                }
            }

            $logAction = "Worker updated project #{$project->project_id} status to '{$actualStatus}'";
            if ($offlineNote) {
                $logAction .= " ({$offlineNote})";
            }

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => $logAction,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            // If completed by worker, notify admins
            if ($validated['status'] === 'Completed') {
                $worker->recalculateAvailability();

                $notificationService = new \App\Services\NotificationService();
                $admins = \App\Models\User::where('role', 'admin')->get();
                $projectTitle = $project->request?->title ?? "Project #{$project->project_id}";
                
                foreach ($admins as $admin) {
                    $notificationService->taskCompleted($admin->user_id, $projectTitle, $project->request_id ?? $project->project_id);
                }
            }

            return response()->json([
                'success'      => true,
                'message'      => "Task status updated to \"{$validated['status']}\".",
                'status'       => $actualStatus,
                'performed_at' => $performedAt->toIso8601String(),
                'offline_note' => $offlineNote
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing task status: ' . $e->getMessage()
            ], 500);
        }
    }
}
