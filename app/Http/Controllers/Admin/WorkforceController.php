<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\Team;
use App\Models\Worker;
use App\Models\ProjectWorker;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class WorkforceController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function index()
    {
        $workers = Worker::whereHas('staff.user', fn($q) => $q->where('role', 'worker'))
            ->with([
                'staff.user', 
                'team',
                'projects' => function($q) {
                    $q->with('request.category', 'latestHistory')
                      ->whereHas('latestHistory', function($lh) {
                          $lh->whereNotIn('current_status', ['Completed', 'Cancelled']);
                      });
                }
            ])->get();
        $projects = Project::with('request', 'latestHistory')
            ->whereHas('latestHistory', fn($q) => $q->where('current_status', 'Pending'))
            ->get();

        $totalWorkers = $workers->count();
        $availableWorkers = $workers->filter(fn($w) => $w->projects->isEmpty())->count();
        $busyWorkers = $totalWorkers - $availableWorkers;
        $onLeave = 0; // Mocked

        $teams = \App\Models\Team::all();
        // Decorate teams with stats and members list
        foreach ($teams as $team) {
            $teamWorkers = $workers->where('team_id', $team->team_id);
            $team->team_members = $teamWorkers->values();
            $team->skilled_workers = $teamWorkers->count();
            $team->available = $teamWorkers->filter(fn($w) => $w->projects->isEmpty())->count();
            $team->active_tasks = $teamWorkers->sum(fn($w) => $w->projects->count());

            
            // Team leader name & leader worker
            $leader = null;
            $leaderWorkerObj = null;
            if ($team->team_leader) {
                $teamLeaderObj = \App\Models\TeamLeader::find($team->team_leader);
                if ($teamLeaderObj) {
                    $leaderWorker = $workers->firstWhere('staff_id', $teamLeaderObj->staff_id);
                    if ($leaderWorker) {
                        $leaderWorkerObj = $leaderWorker;
                        $leader = $leaderWorker->staff->user->first_name . ' ' . $leaderWorker->staff->user->last_name;
                    }
                }
            }
            $team->leader_name = $leader ?? 'Not Assigned';
            $team->leader_worker = $leaderWorkerObj;

            // Match icons based on name
            $name = strtolower($team->team_name);
            if (str_contains($name, 'carpentry') || str_contains($name, 'masonry') || str_contains($name, 'electrical')) $team->icon = '⚡';
            elseif (str_contains($name, 'plumbing')) $team->icon = '🚰';
            elseif (str_contains($name, 'painting')) $team->icon = '🎨';
            elseif (str_contains($name, 'landscaping')) $team->icon = '🍃';
            elseif (str_contains($name, 'manpower')) $team->icon = '👥';
            elseif (str_contains($name, 'janitorial')) $team->icon = '✨';
            else $team->icon = '🏢';
        }

        return view('admin.workforce.index', compact(
            'workers', 'projects', 'totalWorkers', 'availableWorkers', 'busyWorkers', 'onLeave', 'teams'
        ));
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'project_id'  => 'required|exists:project,project_id',
            'worker_ids'  => 'required|array|min:1',
            'worker_ids.*'=> 'exists:worker,worker_id',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        foreach ($validated['worker_ids'] as $workerId) {
            $worker = Worker::findOrFail($workerId);

            // Create assignment
            ProjectWorker::firstOrCreate([
                'project_id' => $project->project_id,
                'worker_id'  => $workerId,
            ], [
                'date_assigned' => now()->toDateString(),
            ]);

            // Mark worker as unavailable
            $worker->update(['is_available' => false]);

            // Notify worker
            $this->notifications->workerAssigned(
                $worker->staff->user_id,
                $project->request?->title ?? "Project #{$project->project_id}",
                $project->project_id
            );
        }

        // Update project status to Assigned
        ProjectHistory::create([
            'project_id'      => $project->project_id,
            'previous_status' => $project->current_status,
            'current_status'  => 'Assigned',
            'updated_at'      => now(),
            'updated_by'      => auth()->id(),
        ]);

        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin assigned workers to project #{$project->project_id}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.workforce.index')
            ->with('success', 'Workers assigned successfully.');
    }

    public function makeTeamLeader(Request $request, int $workerId)
    {
        $worker = Worker::findOrFail($workerId);
        $team = $worker->team;
        
        if (!$team) {
            return redirect()->back()->with('error', 'Worker is not assigned to any team.');
        }

        // Create or get TeamLeader entry for this staff
        $teamLeader = \App\Models\TeamLeader::firstOrCreate([
            'staff_id' => $worker->staff_id
        ]);

        // Set the team's leader
        $team->update([
            'team_leader' => $teamLeader->leader_id
        ]);

        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin promoted worker #{$workerId} to Team Leader of {$team->team_name}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.workforce.index')
            ->with('success', 'Worker has been promoted to Team Leader of ' . $team->team_name);
    }

    public function assignTeam(Request $request, int $workerId)
    {
        $validated = $request->validate([
            'team_id' => 'nullable|exists:team,team_id',
        ]);

        $worker  = Worker::findOrFail($workerId);
        $oldTeamId = $worker->team_id;
        $newTeamId = $validated['team_id'] ?: null;

        $worker->update(['team_id' => $newTeamId]);

        // Recalculate member_count for affected teams
        if ($oldTeamId) {
            Team::find($oldTeamId)?->update([
                'member_count' => Worker::where('team_id', $oldTeamId)->count(),
            ]);
        }
        if ($newTeamId && $newTeamId !== $oldTeamId) {
            Team::find($newTeamId)?->update([
                'member_count' => Worker::where('team_id', $newTeamId)->count(),
            ]);
        }

        $teamName = $newTeamId ? (Team::find($newTeamId)?->team_name ?? "Team #{$newTeamId}") : 'no team';

        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => $newTeamId
                ? "Admin assigned worker #{$workerId} to {$teamName}"
                : "Admin removed worker #{$workerId} from their team",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        $message = $newTeamId
            ? "Worker successfully assigned to {$teamName}."
            : 'Worker has been removed from their team.';

        return redirect()->route('admin.workforce.index')
            ->with('success', $message);
    }
}
