<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\ProjectWorker;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $worker = auth()->user()->staff?->worker;
        
        if (!$worker || !$worker->team) {
            return redirect()->route('worker.dashboard')->with('error', 'You are not assigned to a unit.');
        }

        $team = $worker->team()->with([
            'leader.staff.user', 
            'workers' => function($wq) {
                $wq->with([
                    'staff.user',
                    'projects' => function($pq) {
                        $pq->with('request.category', 'latestHistory')
                          ->whereHas('latestHistory', function($lh) {
                              $lh->where('current_status', '!=', 'Completed');
                          });
                    }
                ]);
            }
        ])->first();
        
        // Active Requests count for the team
        // This is the number of projects currently assigned to any worker in the team that are not completed
        $activeRequestsCount = ProjectWorker::whereIn('worker_id', $team->workers->pluck('worker_id'))
            ->whereHas('project', function($query) {
                $query->whereHas('latestHistory', function($q) {
                    $q->where('current_status', '!=', 'Completed');
                });
            })
            ->distinct('project_id')
            ->count('project_id');

        $availableWorkersCount = $team->workers->where('is_available', true)->count();
        
        // Current deployments (projects assigned to the team)
        $deployments = \App\Models\Project::whereHas('workers', function($query) use ($team) {
            $query->whereIn('project_worker.worker_id', $team->workers->pluck('worker_id'));
        })
        ->with('request.category')
        ->orderBy('project_id', 'desc')
        ->limit(10)
        ->get();

        return view('worker.units.index', compact('team', 'activeRequestsCount', 'availableWorkersCount', 'deployments'));
    }
}
