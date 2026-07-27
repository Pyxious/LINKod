<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Project;

class BomController extends Controller
{
    public function show(int $projectId)
    {
        $client  = auth()->user()->client;
        $project = Project::with('billOfMaterials.material', 'request')
            ->where('client_id', $client->client_id)
            ->findOrFail($projectId);

        $totalCost = $project->billOfMaterials->sum('total_cost');

        return view('client.bom.show', compact('project', 'totalCost'));
    }
}
