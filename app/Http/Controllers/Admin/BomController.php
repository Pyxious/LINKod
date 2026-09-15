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
        $project = Project::with('request')->findOrFail($projectId);
        if (in_array($project->current_status, ['Cancelled', 'Rejected'])) {
            return redirect()->back()->with('error', 'Materials cannot be modified for cancelled or disapproved requests.');
        }
        $staff = auth()->user()->staff;

        $items = [];
        if ($request->has('items') && is_array($request->input('items'))) {
            $validated = $request->validate([
                'items'                         => 'required|array|min:1',
                'items.*.material_id'           => 'nullable',
                'items.*.custom_material_name'  => 'nullable|string|max:200',
                'items.*.unit_of_measurement'   => 'nullable|string|max:50',
                'items.*.qty'                   => 'required|numeric|min:0.01',
                'items.*.unit_cost'             => 'nullable|numeric|min:0',
            ]);
            $items = $validated['items'];
        } else {
            $validated = $request->validate([
                'material_id'          => 'nullable',
                'custom_material_name' => 'nullable|string|max:200',
                'unit_of_measurement'  => 'nullable|string|max:50',
                'qty'                  => 'required|numeric|min:0.01',
                'unit_cost'            => 'nullable|numeric|min:0',
            ]);
            $items = [$validated];
        }

        $addedCount = 0;
        foreach ($items as $item) {
            $materialId = $item['material_id'] ?? null;
            $customName = trim($item['custom_material_name'] ?? '');
            $unit = trim($item['unit_of_measurement'] ?? '') ?: 'pcs';
            $qty = (float)$item['qty'];
            $unitCost = (float)($item['unit_cost'] ?? 0);

            if ($qty <= 0) continue;

            $material = null;
            if ($materialId && $materialId !== 'custom' && is_numeric($materialId)) {
                $material = Materials::find($materialId);
                if ($material && $unitCost > 0) {
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
                if ($unitCost > 0) {
                    $material->update(['unit_cost' => $unitCost, 'unit_of_measurement' => $unit]);
                }
            }

            if (!$material) continue;

            BillOfMaterials::create([
                'project_id'    => $project->project_id,
                'material_id'   => $material->material_id,
                'qty'           => $qty,
                'total_cost'    => $qty * $unitCost,
                'created_by'    => $staff?->staff_id,
                'date_approved' => null, // Requires Admin verification before forwarding to client
                'fulfilled_by'  => null,
            ]);
            $addedCount++;
        }

        if ($addedCount === 0) {
            return redirect()->back()->with('error', 'Please select or enter valid material(s).');
        }

        $prevProjectStatus = $project->current_status;
        $newStatus = 'Awaiting Verification of Bill of Materials';

        $project->update(['current_status' => $newStatus]);

        ProjectHistory::create([
            'project_id'      => $project->project_id,
            'previous_status' => $prevProjectStatus,
            'current_status'  => $newStatus,
            'remarks'         => "Admin added {$addedCount} material item(s) to List of Materials. Awaiting verification.",
            'updated_at'      => now(),
            'updated_by'      => auth()->id(),
        ]);

        if ($project->request_id) {
            $serviceRequest = \App\Models\ServiceRequest::find($project->request_id);
            if ($serviceRequest) {
                $prevReqStatus = $serviceRequest->current_status;
                $serviceRequest->update([
                    'current_status' => $newStatus,
                    'bom_status'     => 'awaiting_admin',
                ]);

                RequestHistory::create([
                    'request_id'      => $serviceRequest->request_id,
                    'previous_status' => $prevReqStatus,
                    'current_status'  => $newStatus,
                    'remarks'         => "Admin added {$addedCount} material item(s) to List of Materials. Awaiting verification.",
                    'updated_at'      => now(),
                    'updated_by'      => auth()->id(),
                ]);
            }
        }

        UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin added {$addedCount} material item(s) to List of Materials for project #{$project->project_id}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', "{$addedCount} material item(s) added to List of Materials. Please verify items to forward to client.");
        }

        return redirect()->back()->with('success', "{$addedCount} material item(s) added to List of Materials. Please verify items to forward to client.");
    }

    public function approve(Request $request, int $projectId)
    {
        $validated = $request->validate([
            'items'                       => 'required|array|min:1',
            'items.*.bom_id'              => 'required|exists:bill_of_materials,bom_id',
            'items.*.unit_cost'           => 'nullable|numeric|min:0',
            'items.*.qty'                 => 'required|numeric|min:0.01',
            'items.*.unit_of_measurement' => 'nullable|string|max:50',
        ]);

        $project = Project::with(['client.user', 'billOfMaterials.material', 'workers.staff.user'])->findOrFail($projectId);
        if (in_array($project->current_status, ['Completed', 'Cancelled', 'Rejected'])) {
            return redirect()->back()->with('error', 'List of Materials cannot be modified for closed or cancelled requests.');
        }
        $staff   = auth()->user()->staff;

        foreach ($validated['items'] as $item) {
            $bom = BillOfMaterials::with('material')
                ->where('project_id', $project->project_id)
                ->where('bom_id', $item['bom_id'])
                ->first();

            if ($bom) {
                $qty = (float)$item['qty'];
                $unitCost = (float)($item['unit_cost'] ?? 0);
                $unit = trim($item['unit_of_measurement'] ?? '') ?: ($bom->material?->unit_of_measurement ?? 'pcs');

                if ($bom->material && $unitCost > 0) {
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

        $prevStatus = $project->current_status;
        $newStatus = 'BOM Verified (Awaiting Client Approval)';
        $remarks = 'GSO Admin verified the List of Materials. Awaiting final approval from client.';

        $project->update(['current_status' => $newStatus]);

        ProjectHistory::create([
            'project_id'      => $project->project_id,
            'previous_status' => $prevStatus,
            'current_status'  => $newStatus,
            'remarks'         => $remarks,
            'updated_at'      => now(),
            'updated_by'      => auth()->id(),
        ]);

        if ($project->request_id) {
            $serviceRequest = \App\Models\ServiceRequest::find($project->request_id);
            if ($serviceRequest) {
                $prevReqStatus = $serviceRequest->current_status;
                $serviceRequest->update([
                    'current_status' => $newStatus,
                    'bom_status'     => 'awaiting_client',
                ]);

                RequestHistory::create([
                    'request_id'      => $serviceRequest->request_id,
                    'previous_status' => $prevReqStatus,
                    'current_status'  => $newStatus,
                    'remarks'         => $remarks,
                    'updated_at'      => now(),
                    'updated_by'      => auth()->id(),
                ]);
            }
        }

        UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin verified List of Materials for project #{$project->project_id}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        $projectTitle = $project->request?->title ?? "Project #{$project->project_id}";

        // Notify client that List of Materials is verified and awaits client confirmation
        if ($project->client?->user_id) {
            $this->notifications->bomVerifiedAwaitingClient(
                $project->client->user_id,
                $projectTitle,
                $project->request_id ?? $project->project_id
            );
        }

        // Notify assigned workers that List of Materials has been verified by admin
        if ($project->workers) {
            foreach ($project->workers as $pw) {
                $workerUserId = $pw->staff?->user_id ?? $pw->user?->user_id;
                if ($workerUserId) {
                    $this->notifications->send(
                        $workerUserId,
                        'bom_verified',
                        'List of Materials Verified by Admin',
                        "The requested materials for \"{$projectTitle}\" have been verified. Awaiting client approval.",
                        route('worker.job-orders.show', $project->project_id, false)
                    );
                }
            }
        }

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', 'List of Materials verified successfully. Forwarded to client for approval.');
        }

        return redirect()->back()->with('success', 'List of Materials verified successfully. Forwarded to client for approval.');
    }

    public function destroyItem(int $projectId, int $bomId)
    {
        $project = Project::findOrFail($projectId);
        if (in_array($project->current_status, ['Cancelled', 'Rejected'])) {
            return redirect()->back()->with('error', 'Materials cannot be modified for cancelled or disapproved requests.');
        }

        $bom = BillOfMaterials::where('project_id', $projectId)->where('bom_id', $bomId)->firstOrFail();
        $materialName = $bom->material?->material_name ?? 'Item';
        $bom->delete();

        UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin removed material item #{$bomId} ({$materialName}) from project #{$projectId}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', "Removed {$materialName} from List of Materials.");
    }
}


