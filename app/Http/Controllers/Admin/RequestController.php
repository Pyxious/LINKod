<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\RequestHistory;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function index(Request $request)
    {
        $query = ServiceRequest::with('client.user', 'category', 'latestHistory')
            ->latest('submitted_at');

        if ($request->filled('status')) {
            $query->whereHas('latestHistory', fn($q) =>
                $q->where('current_status', $request->status));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $requests = $query->paginate(15);

        // Fetch KPI Metrics for Admin Request Tracking
        $totalRequests = ServiceRequest::count();
        $submitted     = ServiceRequest::where(function($q) {
            $q->whereHas('latestHistory', fn($lh) => $lh->where('current_status', 'Submitted'))
              ->orWhereDoesntHave('histories');
        })->count();
        $onHold        = ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'On Hold'))->count();
        $inProgress    = ServiceRequest::whereHas('latestHistory', fn($q) => $q->whereIn('current_status', ['In Progress', 'Pending Verification']))->count();
        $completed     = ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'Completed'))->count();

        return view('admin.requests.index', compact(
            'requests', 'totalRequests', 'submitted', 'onHold', 'inProgress', 'completed'
        ));
    }

    public function show(int $id)
    {
        $serviceRequest = ServiceRequest::with(
            'client.user', 'category', 'histories.updatedBy', 'evaluation', 'project.histories'
        )->findOrFail($id);

        $workers = \App\Models\Worker::with('user', 'team')->where('is_available', true)->get();
        $categories = \App\Models\Category::all();

        return view('admin.requests.show', compact('serviceRequest', 'workers', 'categories'));
    }

    public function approve(Request $request, int $id)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:category,category_id',
                'priority'    => 'required|in:High,Medium,Low',
            ]);

            $serviceRequest = ServiceRequest::findOrFail($id);
            $user           = auth()->user();
            $staff          = $user->staff;

            if (!$staff) {
                $staff = \App\Models\Staff::create([
                    'user_id'    => $user->user_id,
                    'role'       => $user->role,
                    'date_hired' => now()->toDateString(),
                ]);
            }

            $serviceRequest->update([
                'category_id' => $request->category_id,
                'priority'    => $request->priority,
            ]);

            $previous = $serviceRequest->current_status;

            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $previous,
                'current_status'  => 'Approved',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            // Create a Project record
            $project = Project::create([
                'client_id'     => $serviceRequest->client_id,
                'request_id'    => $serviceRequest->request_id,
                'approved_by'   => $staff?->staff_id,
                'date_approved' => now()->toDateString(),
            ]);

            $workerIds = $request->input('worker_ids', []);

            // If no workers were explicitly checked, auto-assign available workers from the team matching the category
            if (empty($workerIds) && $request->category_id) {
                $category = \App\Models\Category::find($request->category_id);
                if ($category) {
                    $catName = strtolower($category->category_name);
                    preg_match_all('/\w+/', $catName, $catWords);
                    $keywords = array_filter($catWords[0], fn($w) => strlen($w) > 3);

                    $matchingTeam = \App\Models\Team::all()->first(function ($team) use ($keywords) {
                        $tName = strtolower($team->team_name);
                        foreach ($keywords as $word) {
                            if (str_contains($tName, $word)) return true;
                        }
                        return false;
                    });

                    if ($matchingTeam) {
                        $workerIds = \App\Models\Worker::where('team_id', $matchingTeam->team_id)
                            ->where('is_available', true)
                            ->pluck('worker_id')
                            ->toArray();
                    }
                }
            }

            if (!empty($workerIds)) {
                foreach ($workerIds as $workerId) {
                    \App\Models\ProjectWorker::firstOrCreate([
                        'project_id' => $project->project_id,
                        'worker_id'  => $workerId,
                    ], [
                        'date_assigned' => now()->toDateString(),
                    ]);

                    $worker = \App\Models\Worker::find($workerId);
                    if ($worker) {
                        $worker->update(['is_available' => false]);
                        
                        $this->notifications->workerAssigned(
                            $worker->staff->user_id,
                            $serviceRequest->title,
                            $project->project_id
                        );
                    }
                }
            }

            ProjectHistory::create([
                'project_id'      => $project->project_id,
                'previous_status' => null,
                'current_status'  => 'Pending',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Admin approved request #{$serviceRequest->request_id} and created project #{$project->project_id}",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            // Notify client
            $this->notifications->requestStatusChanged(
                $serviceRequest->client->user_id,
                $serviceRequest->title,
                'Approved',
                $serviceRequest->request_id,
                'client'
            );

            return redirect()->route('admin.requests.show', $id)
                ->with('success', 'Request approved, project created, and workers assigned.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error approving request: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, int $id)
    {
        try {
            $serviceRequest = ServiceRequest::findOrFail($id);
            $previous       = $serviceRequest->current_status;

            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $previous,
                'current_status'  => 'Rejected',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Admin rejected request #{$serviceRequest->request_id}",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            $this->notifications->requestStatusChanged(
                $serviceRequest->client->user_id,
                $serviceRequest->title,
                'Rejected',
                $serviceRequest->request_id,
                'client'
            );

            return redirect()->route('admin.requests.show', $id)
                ->with('success', 'Request has been rejected.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error rejecting request: ' . $e->getMessage());
        }
    }

    public function verifyCompletion(int $id)
    {
        try {
            $serviceRequest = ServiceRequest::with('project')->findOrFail($id);

            // Update Project History
            if ($serviceRequest->project) {
                ProjectHistory::create([
                    'project_id'      => $serviceRequest->project->project_id,
                    'previous_status' => $serviceRequest->project->current_status,
                    'current_status'  => 'Completed',
                    'updated_at'      => now(),
                    'updated_by'      => auth()->id(),
                ]);
            }

            // Update Request History
            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $serviceRequest->current_status,
                'current_status'  => 'Completed',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Admin verified completion for request #{$serviceRequest->request_id}",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            // Notify Client
            $this->notifications->requestStatusChanged(
                $serviceRequest->client->user_id,
                $serviceRequest->title,
                'Completed',
                $serviceRequest->request_id,
                'client'
            );

            return redirect()->route('admin.requests.show', $id)
                ->with('success', 'Project completion verified and request closed successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error verifying completion: ' . $e->getMessage());
        }
    }

    public function export(int $id)
    {
        try {
            $serviceRequest = ServiceRequest::with('client.user', 'category', 'project.workers.user')->findOrFail($id);
            
            return view('admin.requests.print', compact('serviceRequest'));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to print request: ' . $e->getMessage());
        }
    }
}
