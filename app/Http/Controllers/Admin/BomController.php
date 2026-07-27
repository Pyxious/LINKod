<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillOfMaterials;
use App\Models\Materials;
use App\Models\Project;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BomController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function index()
    {
        $projects = Project::with('request', 'billOfMaterials', 'latestHistory')->paginate(15);
        return view('admin.bom.index', compact('projects'));
    }

    public function create(int $projectId)
    {
        $project   = Project::with('request')->findOrFail($projectId);
        $materials = Materials::orderBy('material_name')->get();
        return view('admin.bom.create', compact('project', 'materials'));
    }

    public function store(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,material_id',
            'items.*.qty'         => 'required|numeric|min:0.01',
        ]);

        $project = Project::findOrFail($projectId);
        $staff   = auth()->user()->staff;

        foreach ($validated['items'] as $item) {
            $material = Materials::find($item['material_id']);
            BillOfMaterials::create([
                'project_id'  => $project->project_id,
                'material_id' => $item['material_id'],
                'qty'         => $item['qty'],
                'total_cost'  => $material->unit_cost * $item['qty'],
                'created_by'  => $staff?->staff_id,
            ]);
        }

        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin created BOM for project #{$project->project_id}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.bom.show', $projectId)
            ->with('success', 'Bill of Materials created.');
    }

    public function show(int $projectId)
    {
        $project   = Project::with('billOfMaterials.material', 'request')->findOrFail($projectId);
        $totalCost = $project->billOfMaterials->sum('total_cost');
        return view('admin.bom.show', compact('project', 'totalCost'));
    }

    public function approve(int $projectId)
    {
        $project = Project::with('client', 'billOfMaterials')->findOrFail($projectId);
        $staff   = auth()->user()->staff;

        $project->billOfMaterials()->update([
            'date_approved' => now()->toDateString(),
            'fulfilled_by'  => $staff?->staff_id,
        ]);

        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin approved BOM for project #{$project->project_id}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        $this->notifications->bomAvailable(
            $project->client->user_id,
            $project->request?->title ?? "Project #{$project->project_id}"
        );

        return redirect()->route('admin.bom.show', $projectId)
            ->with('success', 'BOM approved and client notified.');
    }
}
