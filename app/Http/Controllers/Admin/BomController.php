<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillOfMaterials;
use App\Models\Materials;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\RequestHistory;
use App\Models\User;
use App\Models\UserLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BomController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status', 'all');

        $query = Project::with(['request.client.user', 'billOfMaterials.material', 'workers.user', 'latestHistory'])
            ->whereHas('billOfMaterials');

        if ($search) {
            $query->whereHas('request', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('requisition_no', 'like', "%{$search}%")
                  ->orWhereHas('client.user', function($qu) use ($search) {
                      $qu->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status === 'pending') {
            $query->whereHas('billOfMaterials', function ($q) {
                $q->whereNull('date_approved');
            });
        } elseif ($status === 'approved') {
            $query->whereDoesntHave('billOfMaterials', function ($q) {
                $q->whereNull('date_approved');
            });
        }

        $projects = $query->latest('project_id')->paginate(15)->withQueryString();

        $counts = [
            'all' => Project::whereHas('billOfMaterials')->count(),
            'pending' => Project::whereHas('billOfMaterials', fn($q) => $q->whereNull('date_approved'))->count(),
            'approved' => Project::whereHas('billOfMaterials')->whereDoesntHave('billOfMaterials', fn($q) => $q->whereNull('date_approved'))->count(),
            'total_cost' => BillOfMaterials::sum('total_cost'),
            'total_items' => BillOfMaterials::count(),
        ];

        return view('admin.bom.index', compact('projects', 'counts', 'status', 'search'));
    }


    public function show(int $projectId)
    {
        $project = Project::with([
            'billOfMaterials.material',
            'request.category',
            'request.client.user',
            'workers.user',
            'workers.staff'
        ])->findOrFail($projectId);

        $allMaterials = Materials::orderBy('material_name')->get();
        $totalCost = $project->billOfMaterials->sum('total_cost');
        $pendingCount = $project->billOfMaterials->whereNull('date_approved')->count();

        return view('admin.bom.show', compact('project', 'allMaterials', 'totalCost', 'pendingCount'));
    }

    public function store(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'material_id'          => 'nullable',
            'custom_material_name' => 'nullable|string|max:200',
            'unit_of_measurement'  => 'nullable|string|max:50',
            'qty'                  => 'required|numeric|min:0.01',
            'unit_cost'            => 'required|numeric|min:0',
        ]);

        $project = Project::findOrFail($projectId);
        $staff   = auth()->user()->staff;

        $materialId = $validated['material_id'] ?? null;
        $customName = trim($validated['custom_material_name'] ?? '');
        $unit = trim($validated['unit_of_measurement'] ?? '') ?: 'pcs';
        $qty = (float)$validated['qty'];
        $unitCost = (float)$validated['unit_cost'];

        $material = null;
        if ($materialId && $materialId !== 'custom' && is_numeric($materialId)) {
            $material = Materials::find($materialId);
            if ($material) {
                $material->update([
                    'unit_cost' => $unitCost,
                    'unit_of_measurement' => $unit ?: $material->unit_of_measurement
                ]);
            }
        } elseif (!empty($customName)) {
            $material = Materials::firstOrCreate(
                ['material_name' => $customName],
                [
                    'unit_of_measurement' => $unit,
                    'unit_cost' => $unitCost,
                ]
            );
            $material->update(['unit_cost' => $unitCost, 'unit_of_measurement' => $unit]);
        }

        if (!$material) {
            return redirect()->back()->with('error', 'Please select or enter a valid material.');
        }

        BillOfMaterials::create([
            'project_id'    => $project->project_id,
            'material_id'   => $material->material_id,
            'qty'           => $qty,
            'total_cost'    => $qty * $unitCost,
            'created_by'    => $staff?->staff_id,
            'date_approved' => now()->toDateString(), // Admin added is automatically approved
            'fulfilled_by'  => $staff?->staff_id,
        ]);

        UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin added material ({$material->material_name}) to BOM for project #{$project->project_id}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', 'Material added to Bill of Materials.');
        }

        return redirect()->back()->with('success', 'Material added to Bill of Materials.');
    }

    public function approve(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'items'                       => 'required|array|min:1',
            'items.*.bom_id'              => 'required|exists:bill_of_materials,bom_id',
            'items.*.unit_cost'           => 'required|numeric|min:0',
            'items.*.qty'                 => 'required|numeric|min:0.01',
            'items.*.unit_of_measurement' => 'nullable|string|max:50',
        ]);

        $project = Project::with(['client.user', 'billOfMaterials.material', 'workers.staff.user'])->findOrFail($projectId);
        $staff   = auth()->user()->staff;

        foreach ($validated['items'] as $item) {
            $bom = BillOfMaterials::with('material')
                ->where('project_id', $project->project_id)
                ->where('bom_id', $item['bom_id'])
                ->first();

            if ($bom) {
                $qty = (float)$item['qty'];
                $unitCost = (float)$item['unit_cost'];
                $unit = trim($item['unit_of_measurement'] ?? '') ?: ($bom->material?->unit_of_measurement ?? 'pcs');

                if ($bom->material) {
                    $bom->material->update([
                        'unit_cost' => $unitCost,
                        'unit_of_measurement' => $unit,
                    ]);
                }

                $bom->update([
                    'qty'           => $qty,
                    'total_cost'    => $qty * $unitCost,
                    'date_approved' => now()->toDateString(),
                    'fulfilled_by'  => $staff?->staff_id,
                ]);
            }
        }

        // Resume project from 'On Hold' to 'In Progress' if needed
        if ($project->current_status === 'On Hold') {
            $project->update(['current_status' => 'In Progress']);
            ProjectHistory::create([
                'project_id'      => $project->project_id,
                'previous_status' => 'On Hold',
                'current_status'  => 'In Progress',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            if ($project->request) {
                $project->request->update(['current_status' => 'In Progress']);
                RequestHistory::create([
                    'request_id'      => $project->request->request_id,
                    'previous_status' => 'On Hold',
                    'current_status'  => 'In Progress',
                    'updated_at'      => now(),
                    'updated_by'      => auth()->id(),
                ]);
            }
        }

        UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin priced and approved BOM for project #{$project->project_id}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        // Notify client that BOM is approved with cost
        if ($project->client?->user_id) {
            $this->notifications->bomAvailable(
                $project->client->user_id,
                $project->request?->title ?? "Project #{$project->project_id}",
                $project->project_id
            );
        }

        // Notify assigned workers that materials are approved and job can resume
        if ($project->workers) {
            $projectTitle = $project->request?->title ?? "Project #{$project->project_id}";
            foreach ($project->workers as $pw) {
                $workerUserId = $pw->staff?->user_id ?? $pw->user?->user_id;
                if ($workerUserId) {
                    $this->notifications->send(
                        $workerUserId,
                        'bom_approved',
                        'BOM Materials Approved',
                        "The requested materials for \"{$projectTitle}\" have been approved by Admin. You may proceed with the job order.",
                        route('worker.job-orders.show', $project->project_id, false)
                    );
                }
            }
        }

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', 'BOM prices saved and approved successfully. Client and workers notified.');
        }

        return redirect()->back()->with('success', 'BOM prices saved and approved successfully. Client and workers notified.');
    }

    public function destroyItem(int $projectId, int $bomId)
    {
        $bom = BillOfMaterials::where('project_id', $projectId)->where('bom_id', $bomId)->firstOrFail();
        $materialName = $bom->material?->material_name ?? 'Item';
        $bom->delete();

        UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin removed BOM item #{$bomId} ({$materialName}) from project #{$projectId}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', "Removed {$materialName} from Bill of Materials.");
    }
}


