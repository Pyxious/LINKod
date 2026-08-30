<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\Team;
use App\Models\TeamLeader;
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

        $teams = Team::with('category')->get();
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
                $teamLeaderObj = TeamLeader::find($team->team_leader);
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

    public function storeUnit(Request $request)
    {
        $validated = $request->validate([
            'unit_name'   => 'required|string|max:100|unique:team,team_name,NULL,team_id,deleted_at,NULL',
            'description' => 'nullable|string|max:255',
            'leader_id'   => 'nullable|exists:worker,worker_id',
        ], [
            'unit_name.required' => 'Unit / Section name is required.',
            'unit_name.unique'   => 'A unit or section with this name already exists.',
        ]);

        $unitName = trim($validated['unit_name']);

        // 1. Create matching Category (1-to-1 sync)
        $category = Category::create([
            'category_name' => $unitName,
            'description'   => $validated['description'] ?? "Maintenance and service works for {$unitName}.",
        ]);

        // 2. Resolve team leader if selected
        $teamLeaderId = null;
        if (!empty($validated['leader_id'])) {
            $worker = Worker::find($validated['leader_id']);
            if ($worker) {
                $leaderRecord = TeamLeader::firstOrCreate(['staff_id' => $worker->staff_id]);
                $teamLeaderId = $leaderRecord->leader_id;
            }
        }

        // 3. Create Team
        $team = Team::create([
            'team_name'    => $unitName,
            'category_id'  => $category->category_id,
            'team_leader'  => $teamLeaderId,
            'member_count' => 0,
        ]);

        // If leader worker was selected, assign them to this new team
        if (!empty($validated['leader_id'])) {
            Worker::where('worker_id', $validated['leader_id'])->update(['team_id' => $team->team_id]);
            $team->update(['member_count' => 1]);
        }

        // 4. Audit Log
        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin created service unit '{$team->team_name}' and linked category '{$category->category_name}'",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.workforce.index')->with('success', "Unit '{$team->team_name}' and its linked category were successfully created.");
    }

    public function destroyUnit($teamId)
    {
        $team = Team::findOrFail($teamId);
        $teamName = $team->team_name;

        // 1. Soft-delete linked Category
        if ($team->category_id) {
            $category = Category::find($team->category_id);
            if ($category) {
                $category->delete();
            }
        } else {
            $cat = Category::where('category_name', $team->team_name)->first();
            if ($cat) $cat->delete();
        }

        // 2. Unassign workers in this team
        Worker::where('team_id', $team->team_id)->update(['team_id' => null]);

        // 3. Soft-delete Team
        $team->delete();

        // 4. Audit Log
        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin soft-deleted service unit '{$teamName}' and its linked category",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.workforce.index')->with('success', "Unit '{$teamName}' was safely deleted. Existing requests with this category remain intact.");
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

            // Mark worker as busy / update availability
            $worker->recalculateAvailability();

            // Notify worker
            $this->notifications->workerAssigned($worker, $project);
        }

        // Transition project to In Progress if it was pending
        if ($project->current_status === 'Pending') {
            ProjectHistory::create([
                'project_id'      => $project->project_id,
                'previous_status' => 'Pending',
                'current_status'  => 'In Progress',
                'remarks'         => 'Workers deployed by GSO Admin',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);
        }

        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin assigned " . count($validated['worker_ids']) . " worker(s) to project #{$project->project_id}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.workforce.index')
            ->with('success', 'Workers assigned successfully.');
    }

    public function makeTeamLeader(Request $request, $workerId)
    {
        $worker = Worker::findOrFail($workerId);

        if (!$worker->team_id) {
            return back()->with('error', 'Worker must belong to a team first.');
        }

        // Find or create TeamLeader record for this worker's staff_id
        $teamLeader = TeamLeader::firstOrCreate([
            'staff_id' => $worker->staff_id,
        ]);

        // Assign to the worker's team
        $team = Team::findOrFail($worker->team_id);
        $team->update(['team_leader' => $teamLeader->leader_id]);

        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin assigned {$worker->staff->user->first_name} {$worker->staff->user->last_name} as leader of {$team->team_name}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.workforce.index')
            ->with('success', "{$worker->staff->user->first_name} {$worker->staff->user->last_name} is now the leader of {$team->team_name}.");
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
