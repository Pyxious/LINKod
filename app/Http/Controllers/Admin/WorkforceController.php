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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WorkforceController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function index()
    {
        $workers = Worker::whereHas('staff.user', fn($q) => $q->where('role', 'worker'))
            ->with([
                'staff.user',
                'teams',
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

        $totalWorkers     = $workers->count();
        $availableWorkers = $workers->filter(fn($w) => $w->projects->isEmpty())->count();
        $busyWorkers      = $totalWorkers - $availableWorkers;
        $onLeave          = 0;

        $teams = Team::with('category')->get();

        // Pre-load all team leaders in a single query to avoid N+1
        $allTeamLeaders = TeamLeader::with('staff.user')->get()->keyBy('leader_id');

        foreach ($teams as $team) {
            $teamWorkers = $workers->filter(fn($w) => $w->belongsToTeam($team->team_id))->values();

            // Resolve leader from pre-loaded collection
            $leader = null;
            $leaderWorkerObj = null;
            if ($team->team_leader) {
                $teamLeaderObj = $allTeamLeaders->get($team->team_leader);
                if ($teamLeaderObj) {
                    $leaderWorker = $teamWorkers->firstWhere('staff_id', $teamLeaderObj->staff_id);
                    if ($leaderWorker) {
                        $leaderWorkerObj = $leaderWorker;
                        $leader = $leaderWorker->staff->user->first_name . ' ' . $leaderWorker->staff->user->last_name;
                    } else {
                        // Worker is not in this team anymore: clear stale leader
                        Team::where('team_id', $team->team_id)->update(['team_leader' => null]);
                        $team->team_leader = null;
                    }
                } else {
                    Team::where('team_id', $team->team_id)->update(['team_leader' => null]);
                    $team->team_leader = null;
                }
            }
            $team->leader_name  = $leader ?? 'Not Assigned';
            $team->leader_worker = $leaderWorkerObj;

            $team->team_members   = $teamWorkers;
            $team->skilled_workers = $teamWorkers->count();
            $team->available      = $teamWorkers->filter(fn($w) => $w->projects->isEmpty())->count();
            $team->active_tasks   = $teamWorkers->sum(fn($w) => $w->projects->count());

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
            $lWorker = Worker::find($validated['leader_id']);
            if ($lWorker) {
                $lWorker->teams()->syncWithoutDetaching([$team->team_id]);
                if (!$lWorker->team_id) {
                    $lWorker->update(['team_id' => $team->team_id]);
                }
            }
            $team->update(['member_count' => 1]);
        }
        Cache::forget('team_leader_staff_map');

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

        // 2. Unassign workers from team_worker pivot and worker table
        DB::table('team_worker')->where('team_id', $team->team_id)->delete();
        Worker::where('team_id', $team->team_id)->update(['team_id' => null]);

        // 3. Clear leader & Soft-delete Team
        $team->update(['team_leader' => null]);
        $team->delete();
        Cache::forget('team_leader_staff_map');

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
        $worker = Worker::with('teams')->findOrFail($workerId);
        $teamId = $request->input('team_id') ?: $worker->team_id ?: $worker->teams->first()?->team_id;

        if (!$teamId || !$worker->belongsToTeam((int)$teamId)) {
            return back()->with('error', 'Worker must belong to this unit first.');
        }

        // Find or create TeamLeader record for this worker's staff_id
        $teamLeader = TeamLeader::firstOrCreate([
            'staff_id' => $worker->staff_id,
        ]);

        // Assign to the specified unit
        $team = Team::findOrFail($teamId);
        $team->update(['team_leader' => $teamLeader->leader_id]);
        Cache::forget('team_leader_staff_map');

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
            'team_ids'   => 'nullable|array',
            'team_ids.*' => 'exists:team,team_id',
            'team_id'    => 'nullable',
        ]);

        $worker = Worker::with('teams', 'staff.teamLeader')->findOrFail($workerId);

        // Get old team IDs
        $oldTeamIds = $worker->teams->pluck('team_id')->toArray();
        if (empty($oldTeamIds) && $worker->team_id) {
            $oldTeamIds = [$worker->team_id];
        }

        // Determine new team IDs (supports multi-unit array or single team_id)
        if ($request->has('team_ids')) {
            $newTeamIds = array_values(array_unique(array_filter(array_map('intval', (array)($request->input('team_ids') ?? [])))));
        } elseif ($request->has('team_id')) {
            $rawId = $request->input('team_id');
            $newTeamIds = (!empty($rawId) && is_numeric($rawId)) ? [(int)$rawId] : [];
        } else {
            $newTeamIds = [];
        }

        // Teams the worker is being removed from
        $removedTeamIds = array_diff($oldTeamIds, $newTeamIds);

        // Leader unassignment: If worker is removed from a unit, they are NOT the leader anymore!
        if (!empty($removedTeamIds)) {
            $workerLeaderId = $worker->staff?->teamLeader?->leader_id;
            foreach ($removedTeamIds as $removedTeamId) {
                $removedTeam = Team::find($removedTeamId);
                if ($removedTeam) {
                    if ($workerLeaderId && (int)$removedTeam->team_leader === (int)$workerLeaderId) {
                        $removedTeam->update(['team_leader' => null]);
                    }
                }
            }
            Cache::forget('team_leader_staff_map');
        }

        // Sync pivot table
        $worker->teams()->sync($newTeamIds);

        // Keep primary team_id synced for backward compatibility
        $primaryTeamId = $newTeamIds[0] ?? null;
        $worker->update(['team_id' => $primaryTeamId]);

        // Recalculate member_count for all affected teams
        $affectedTeamIds = array_unique(array_merge($oldTeamIds, $newTeamIds));
        foreach ($affectedTeamIds as $affectedId) {
            $count = DB::table('team_worker')->where('team_id', $affectedId)->count();
            Team::where('team_id', $affectedId)->update(['member_count' => $count]);
        }

        $assignedTeams = Team::whereIn('team_id', $newTeamIds)->pluck('team_name')->toArray();
        $teamSummary = !empty($assignedTeams) ? implode(', ', $assignedTeams) : 'unassigned';

        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => !empty($newTeamIds)
                ? "Admin updated worker #{$workerId} units to: {$teamSummary}"
                : "Admin removed worker #{$workerId} from all units",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        $message = !empty($newTeamIds)
            ? "Worker successfully assigned to: {$teamSummary}."
            : 'Worker has been removed from all units and leader roles cleared.';

        return redirect()->route('admin.workforce.index')
            ->with('success', $message);
    }
}
