<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Cache;

class RequestTable extends Component
{
    use WithPagination;

    public $search = '';
    public $priority = '';
    public $status = '';
    public $ratingFilter = '';
    public $sortField = '';
    public $sortDirection = 'asc';

    // Popup mark & merge state for duplicate/recurring entries
    public array $modalGroupIds = [];
    public array $selectedIds = [];
    public ?int $mergePrimaryId = null;
    public string $mergeRemarks = '';
    public bool $showMergeModal = false;
    public int $mergeModalVersion = 0;

    protected $queryString = ['search', 'priority', 'status', 'ratingFilter', 'sortField', 'sortDirection'];

    #[\Livewire\Attributes\Renderless]
    public function clearSelection(): void
    {
        $this->modalGroupIds = [];
        $this->selectedIds = [];
        $this->mergePrimaryId = null;
        $this->mergeRemarks = '';
        $this->showMergeModal = false;
    }

    public function updatedMergePrimaryId($value): void
    {
        $newPrimary = (int) $value;
        // Remove the newly chosen primary from duplicate checkboxes so it isn't merged into itself
        $this->selectedIds = array_values(array_filter(
            $this->selectedIds,
            fn ($id) => (int) $id !== $newPrimary
        ));
    }

    public function selectAllModalDuplicates(): void
    {
        $primary = (int) $this->mergePrimaryId;
        $this->selectedIds = collect($this->modalGroupIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0 && $id !== $primary)
            ->map(fn (int $id) => (string) $id)
            ->values()
            ->all();
    }

    public function uncheckAllModalDuplicates(): void
    {
        $this->selectedIds = [];
    }

    public function selectDuplicateGroup(int $seedRequestId): void
    {
        $seed = ServiceRequest::find($seedRequestId);
        if (!$seed || $seed->isResolved()) {
            return;
        }

        $siblings = $seed->findPotentialPrimaryDuplicates();
        $groupIds = collect([$seed->request_id])
            ->concat($siblings->pluck('request_id'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->all();

        if (count($groupIds) < 2) {
            return;
        }

        $this->modalGroupIds = $groupIds;
        $this->mergePrimaryId = $groupIds[0]; // First entry automatically chosen as primary
        $this->selectedIds = array_map('strval', array_slice($groupIds, 1)); // Pre-check duplicates; unchecking keeps them visible in list
        $this->mergeRemarks = '';
        $this->showMergeModal = true;
        $this->mergeModalVersion++;
    }

    public function mergeSelected(
        ?int $submittedPrimaryId = null,
        ?array $submittedDuplicateIds = null,
        ?string $submittedRemarks = null
    ): void {
        $notificationService = app(\App\Services\NotificationService::class);
        $allowedGroupIds = array_map('intval', $this->modalGroupIds);

        $candidatePrimary = (int) ($submittedPrimaryId ?? $this->mergePrimaryId ?: ($allowedGroupIds[0] ?? 0));
        $primaryId = in_array($candidatePrimary, $allowedGroupIds, true)
            ? $candidatePrimary
            : ($allowedGroupIds[0] ?? 0);

        $rawDuplicateIds = $submittedDuplicateIds ?? $this->selectedIds;
        $duplicateIds = collect($rawDuplicateIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0 && $id !== $primaryId && in_array($id, $allowedGroupIds, true))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $remarks = trim((string) ($submittedRemarks ?? $this->mergeRemarks));

        if ($primaryId <= 0 || empty($duplicateIds)) {
            $this->dispatch('requests-merged', [
                'success' => false,
                'message' => 'Please check at least 1 duplicate entry to merge into the primary requisition.',
            ]);
            return;
        }

        $primary = ServiceRequest::with(['client.user', 'category', 'latestHistory'])->find($primaryId);
        if (!$primary || in_array($primary->current_status, ['Cancelled', 'Rejected'], true)) {
            $this->dispatch('requests-merged', [
                'success' => false,
                'message' => 'Selected primary requisition is invalid or closed.',
            ]);
            return;
        }

        $mergedCount = ServiceRequest::mergeDuplicatesIntoPrimary(
            $primary,
            $duplicateIds,
            (int) auth()->id(),
            $remarks !== '' ? $remarks : null,
            $notificationService
        );

        $this->clearSelection();
        Cache::forget('admin_requests_table_kpis');
        Cache::forget('admin_requests_table_kpis_v2');

        $primaryPad = str_pad((string) $primary->request_id, 4, '0', STR_PAD_LEFT);
        $this->dispatch('requests-merged', [
            'success' => $mergedCount > 0,
            'message' => $mergedCount > 0
                ? "Merged {$mergedCount} duplicate " . \Illuminate\Support\Str::plural('entry', $mergedCount) . " into #{$primaryPad} ({$primary->tracking_code})."
                : 'No active duplicates needed merging.',
        ]);
    }

    public function setPriority($prio)
    {
        $this->priority = $this->priority === $prio ? '' : $prio;
        $this->resetPage();
    }

    public function setRatingFilter($filter)
    {
        $this->ratingFilter = $this->ratingFilter === $filter ? '' : $filter;
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->ratingFilter = '';
        $this->resetPage();
    }

    public function updatingPriority()
    {
        $this->resetPage();
    }

    public function updatingRatingFilter()
    {
        $this->resetPage();
    }

    public function notifyToRate(int $requestId, \App\Services\NotificationService $notificationService)
    {
        $request = ServiceRequest::with('client.user', 'evaluation')->find($requestId);
        if (!$request) {
            $this->dispatch('rating-reminded', [
                'success' => false,
                'message' => 'Service request not found.'
            ]);
            return;
        }

        if ($request->evaluation) {
            $this->dispatch('rating-reminded', [
                'success' => false,
                'message' => 'This request has already been rated by the client.'
            ]);
            return;
        }

        $clientUser = $request->client?->user;
        if (!$clientUser) {
            $this->dispatch('rating-reminded', [
                'success' => false,
                'message' => 'Client account not found for this request.'
            ]);
            return;
        }

        $notificationService->ratingReminder(
            $clientUser->user_id,
            $request->title ?? ('Request #' . $request->request_id),
            $request->request_id
        );

        session()->flash('notified_rate_' . $requestId, true);

        $this->dispatch('rating-reminded', [
            'success' => true,
            'requestId' => $requestId,
            'clientName' => trim(($clientUser->first_name ?? '') . ' ' . ($clientUser->last_name ?? '')),
            'message' => 'Rating reminder sent to ' . trim(($clientUser->first_name ?? '') . ' ' . ($clientUser->last_name ?? '')) . '.'
        ]);
    }

    public function render()
    {
        $query = ServiceRequest::with('client.user', 'category', 'latestHistory', 'project.workers.staff.user', 'project.workers.team', 'evaluation');

        if ($this->priority) {
            $p = strtolower($this->priority);
            if ($p === 'urgent' || $p === 'high') {
                $query->whereIn('priority', ['urgent', 'high']);
            } elseif ($p === 'routine' || $p === 'medium' || $p === 'low') {
                $query->whereIn('priority', ['routine', 'medium', 'low']);
            } else {
                $query->where('priority', $this->priority);
            }
        }

        if ($this->status === 'Completed') {
            $query->whereHas('latestHistory', function($q) {
                $q->where('current_status', 'Completed');
            });

            if ($this->ratingFilter === 'not_rated') {
                $query->whereDoesntHave('evaluation');
            } elseif ($this->ratingFilter === 'rated') {
                $query->whereHas('evaluation');
            }
        } elseif ($this->status === 'Pending') {
            $query->whereHas('latestHistory', function($q) {
                $q->whereIn('current_status', ['Pending', 'Approved', 'Submitted', 'Schedule Confirmed', 'Materials Arrived']);
            });
        } elseif (in_array($this->status, ['Awaiting Materials', 'On Hold'])) {
            $query->whereHas('latestHistory', function($q) {
                $q->whereIn('current_status', [
                    'Awaiting Materials',
                    'Awaiting Verification of Bill of Materials',
                    'BOM Verified (Awaiting Client Approval)',
                    'On Hold',
                ]);
            });
        } elseif ($this->status === 'Rejected') {
            $query->whereHas('latestHistory', function($q) {
                $q->where('current_status', 'Rejected');
            });
        } elseif ($this->status === 'In Progress') {
            $query->whereHas('latestHistory', function($q) {
                $q->whereIn('current_status', ['In Progress', 'Pending Verification']);
            });
        } elseif ($this->status === 'recurring') {
            // Filter by 4+ recurring requests in category & location per month
            $query->recurring();
        } elseif ($this->status === 'all') {
            // No status constraint - show everything
        } else {
            // Default active requests (exclude finished/cancelled unless searching)
            $query->whereHas('latestHistory', function($q) {
                if (empty(trim($this->search))) {
                    $q->whereNotIn('current_status', ['Completed', 'Cancelled', 'Rejected']);
                }
            });
        }

        if ($this->search) {
            $rawSearch = trim($this->search);
            $searchTerm = strtolower($rawSearch);

            // Check if search query is a specific requisition code/ID (e.g. "REQ-034", "CMS-005", "#34", "034", "34")
            // or a tracking-code prefix (e.g. "CMS", "PLS", "PAS", "EMS", "JS", "LS", "MAN", "REQ")
            $explicitId = null;
            $prefixCodeFilter = null;
            if (preg_match('/^([a-z]{1,6})?[-\s#]*0*([1-9]\d*)$/i', $rawSearch, $matches)) {
                $typedPfx = strtolower($matches[1] ?? '');
                $typedPfx = match ($typedPfx) {
                    'paint' => 'pas',
                    'ps'    => 'pls',
                    default => $typedPfx,
                };
                if ($typedPfx === '' || in_array($typedPfx, ['req', 'ls', 'js', 'cms', 'pls', 'ems', 'pas', 'man'], true)) {
                    $explicitId = (int) $matches[2];
                    if ($typedPfx !== '' && $typedPfx !== 'req') {
                        $prefixCodeFilter = $typedPfx;
                    }
                }
            } elseif (preg_match('/^(req|ls|js|cms|pls|ems|pas|paint|man|ps)[-\s#]*0*$/i', $rawSearch, $pfxOnly)) {
                $prefixCodeFilter = match (strtolower($pfxOnly[1])) {
                    'paint' => 'pas',
                    'ps'    => 'pls',
                    default => strtolower($pfxOnly[1]),
                };
            }

            // Find matching client or worker user IDs by decrypting client names/emails (AES-256 encrypted columns)
            $matchingUserIds = \App\Models\User::all()
                ->filter(function ($user) use ($searchTerm) {
                    $fullName = strtolower(trim("{$user->first_name} {$user->last_name}"));
                    if (str_contains($fullName, $searchTerm)) {
                        return true;
                    }
                    if (str_contains(strtolower($user->email_account ?? ''), $searchTerm)) {
                        return true;
                    }
                    return false;
                })
                ->pluck('user_id');

            $matchingClientIds = \App\Models\Client::whereIn('user_id', $matchingUserIds)->pluck('client_id');
            $matchingStaffIds = \App\Models\Staff::whereIn('user_id', $matchingUserIds)->pluck('staff_id');
            $matchingWorkerIds = \App\Models\Worker::whereIn('staff_id', $matchingStaffIds)->pluck('worker_id');

            $words = array_values(array_filter(explode(' ', $rawSearch), fn($w) => strlen(trim($w)) >= 2));

            $query->where(function($q) use ($rawSearch, $searchTerm, $explicitId, $prefixCodeFilter, $matchingClientIds, $matchingWorkerIds, $words) {
                $q->where(function($sub) use ($rawSearch, $searchTerm, $words) {
                    $sub->where('title', 'like', "%{$rawSearch}%")
                        ->orWhere('location', 'like', "%{$rawSearch}%")
                        ->orWhere('campus', 'like', "%{$rawSearch}%")
                        ->orWhere('description', 'like', "%{$rawSearch}%")
                        ->orWhere('priority', 'like', "%{$searchTerm}%")
                        ->orWhereHas('category', function($qCat) use ($rawSearch) {
                            $qCat->where('category_name', 'like', "%{$rawSearch}%");
                        })
                        ->orWhereHas('latestHistory', function($qHist) use ($rawSearch) {
                            $qHist->where('current_status', 'like', "%{$rawSearch}%");
                        });

                    // Multi-word search matching all keywords across fields
                    if (count($words) > 1) {
                        $sub->orWhere(function($kwQ) use ($words) {
                            foreach ($words as $word) {
                                $kwQ->where(function($single) use ($word) {
                                    $single->where('title', 'like', "%{$word}%")
                                           ->orWhere('location', 'like', "%{$word}%")
                                           ->orWhere('campus', 'like', "%{$word}%")
                                           ->orWhere('description', 'like', "%{$word}%")
                                           ->orWhereHas('category', fn($c) => $c->where('category_name', 'like', "%{$word}%"));
                                });
                            }
                        });
                    }
                });

                if ($explicitId !== null) {
                    if ($prefixCodeFilter !== null) {
                        $q->orWhere(function ($codeQ) use ($explicitId, $prefixCodeFilter) {
                            $codeQ->where('request_id', $explicitId);
                            $this->applyPrefixFilterToQuery($codeQ, $prefixCodeFilter);
                        });
                    } else {
                        $q->orWhere('request_id', $explicitId);
                    }
                } elseif ($prefixCodeFilter !== null) {
                    if ($prefixCodeFilter === 'req') {
                        $q->orWhereNotNull('request_id');
                    } else {
                        $q->orWhere(function ($pfxQ) use ($prefixCodeFilter) {
                            $this->applyPrefixFilterToQuery($pfxQ, $prefixCodeFilter);
                        });
                    }
                }

                if ($matchingClientIds->isNotEmpty()) {
                    $q->orWhereIn('client_id', $matchingClientIds);
                }

                if ($matchingWorkerIds->isNotEmpty()) {
                    $q->orWhereHas('project.workers', function($qWorker) use ($matchingWorkerIds) {
                        $qWorker->whereIn('worker.worker_id', $matchingWorkerIds);
                    });
                }
            });
        }

        $isDateDefaultStatus = in_array($this->status, ['Completed', 'all'], true);

        if ($this->sortField === 'priority') {
            $dir = strtoupper($this->sortDirection) === 'DESC' ? 'DESC' : 'ASC';
            $query->orderByRaw("CASE WHEN LOWER(priority) IN ('high', 'urgent') THEN 1 ELSE 2 END {$dir}")
                  ->orderBy('submitted_at', 'asc')
                  ->orderBy('request_id', 'asc');
        } elseif ($this->sortField === 'submitted_at') {
            $dateDir = strtoupper($this->sortDirection) === 'DESC' ? 'DESC' : 'ASC';
            if ($isDateDefaultStatus) {
                $query->orderBy('submitted_at', $dateDir)
                      ->orderBy('request_id', $dateDir);
            } else {
                $query->orderByRaw("CASE WHEN LOWER(priority) IN ('high', 'urgent') THEN 1 ELSE 2 END ASC")
                      ->orderBy('submitted_at', $dateDir)
                      ->orderBy('request_id', $dateDir);
            }
        } elseif ($this->sortField === 'rating_status' && $this->status === 'Completed') {
            $dir = strtoupper($this->sortDirection) === 'DESC' ? 'DESC' : 'ASC';
            $query->leftJoin('evaluation', 'request.request_id', '=', 'evaluation.request_id')
                  ->select('request.*')
                  ->orderByRaw("CASE WHEN evaluation.evaluation_id IS NULL THEN 0 ELSE 1 END {$dir}")
                  ->orderBy('request.submitted_at', 'desc')
                  ->orderBy('request.request_id', 'desc');
        } elseif ($this->sortField === 'status') {
            $dir = strtoupper($this->sortDirection) === 'DESC' ? 'DESC' : 'ASC';
            $statusSub = \App\Models\RequestHistory::select('current_status')
                ->whereColumn('request_history.request_id', 'request.request_id')
                ->latest('updated_at')
                ->latest('history_id')
                ->limit(1);

            if ($isDateDefaultStatus) {
                $query->orderBy($statusSub, $dir)
                      ->orderBy('submitted_at', 'desc')
                      ->orderBy('request_id', 'desc');
            } else {
                $query->orderByRaw("CASE WHEN LOWER(priority) IN ('high', 'urgent') THEN 1 ELSE 2 END ASC")
                      ->orderBy($statusSub, $dir);
            }
        } elseif (in_array($this->sortField, ['request_id', 'title', 'campus', 'location'])) {
            if ($isDateDefaultStatus) {
                $query->orderBy($this->sortField, $this->sortDirection)
                      ->orderBy('submitted_at', 'desc')
                      ->orderBy('request_id', 'desc');
            } else {
                $query->orderByRaw("CASE WHEN LOWER(priority) IN ('high', 'urgent') THEN 1 ELSE 2 END ASC")
                      ->orderBy($this->sortField, $this->sortDirection);
            }
        } else {
            if ($isDateDefaultStatus) {
                // Completed & All Statuses sorting: Sort by date as default without pinning urgent on top
                $query->orderBy('submitted_at', 'desc')
                      ->orderBy('request_id', 'desc');
            } else {
                // Active requests: High priorities ALWAYS at the top of the list (FCFS: submitted_at asc)
                $query->orderByRaw("CASE WHEN LOWER(priority) IN ('high', 'urgent') THEN 1 ELSE 2 END ASC")
                      ->orderBy('submitted_at', 'asc')
                      ->orderBy('request_id', 'asc');
            }
        }

        $requests = $query->paginate(15);
        ServiceRequest::warmRecurringCounts($requests->getCollection());

        $activeDuplicateCounts = ServiceRequest::whereDoesntHave('latestHistory', function ($q) {
            $q->whereIn('current_status', ['Completed', 'Cancelled', 'Rejected']);
        })
            ->get(['request_id', 'category_id', 'title', 'campus', 'location'])
            ->groupBy(fn ($r) => ServiceRequest::buildSameDetailsSignature(
                (int) $r->category_id,
                $r->title,
                $r->campus,
                $r->location
            ))
            ->map(fn ($group) => $group->count())
            ->all();

        $selectedModels = !empty($this->modalGroupIds)
            ? ServiceRequest::with(['client.user', 'category', 'latestHistory'])
                ->whereIn('request_id', array_map('intval', $this->modalGroupIds))
                ->orderBy('request_id', 'asc')
                ->get()
            : collect();

        // KPI metrics cached for 180 seconds (busted immediately on any request update)
        $kpis = Cache::remember('admin_requests_table_kpis_v2', 180, function () {
            $totalRequests = ServiceRequest::count();

            $statusCounts = \App\Models\RequestHistory::whereIn('history_id', function ($sub) {
                $sub->selectRaw('MAX(history_id)')->from('request_history')->groupBy('request_id');
            })
            ->whereHas('request')
            ->selectRaw('current_status, COUNT(*) as total')
            ->groupBy('current_status')
            ->pluck('total', 'current_status');

            $noHistoryCount = max(0, $totalRequests - (int) $statusCounts->sum());
            $submitted = (int) ($statusCounts['Submitted'] ?? 0) + $noHistoryCount;

            $awaitingMaterials = (int) ($statusCounts['Awaiting Materials'] ?? 0)
                + (int) ($statusCounts['Awaiting Verification of Bill of Materials'] ?? 0)
                + (int) ($statusCounts['BOM Verified (Awaiting Client Approval)'] ?? 0)
                + (int) ($statusCounts['On Hold'] ?? 0);

            $inProgress = (int) ($statusCounts['In Progress'] ?? 0)
                + (int) ($statusCounts['Pending Verification'] ?? 0);

            $completed = (int) ($statusCounts['Completed'] ?? 0);
            $completedRated = $completed > 0
                ? ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'Completed'))
                    ->whereHas('evaluation')
                    ->count()
                : 0;
            $completedNotRated = max(0, $completed - $completedRated);

            $recurringCount = ServiceRequest::recurring()->count();

            return [
                'totalRequests'     => $totalRequests,
                'submitted'         => $submitted,
                'onHold'            => $awaitingMaterials,
                'awaitingMaterials' => $awaitingMaterials,
                'inProgress'        => $inProgress,
                'completed'         => $completed,
                'completedRated'    => $completedRated,
                'completedNotRated' => $completedNotRated,
                'recurringCount'    => $recurringCount,
            ];
        });

        return view('livewire.admin.request-table', array_merge([
            'requests'              => $requests,
            'activeDuplicateCounts' => $activeDuplicateCounts,
            'selectedModels'        => $selectedModels,
        ], $kpis));
    }

    #[\Livewire\Attributes\On('refreshRequests')]
    public function refreshRequests()
    {
        // Bust KPI cache so real-time Supabase events recalculate immediately
        Cache::forget('admin_requests_table_kpis');
        Cache::forget('admin_requests_table_kpis_v2');
    }

    protected function applyPrefixFilterToQuery($query, string $prefix): void
    {
        match ($prefix) {
            'cms' => $query->whereHas('category', fn ($c) => $c->where(function ($sub) {
                $sub->where('category_name', 'like', '%carpentry%')
                    ->orWhere('category_name', 'like', '%masonry%');
            })),
            'pls' => $query->whereHas('category', fn ($c) => $c->where('category_name', 'like', '%plumbing%')),
            'pas' => $query->whereHas('category', fn ($c) => $c->where('category_name', 'like', '%paint%')),
            'ls'  => $query->whereHas('category', fn ($c) => $c->where('category_name', 'like', '%landscaping%')),
            'ems' => $query->whereHas('category', fn ($c) => $c->where(function ($sub) {
                $sub->where('category_name', 'like', '%electrical%')
                    ->orWhere('category_name', 'like', '%mechanical%');
            })->where('category_name', 'not like', '%carpentry%')->where('category_name', 'not like', '%masonry%')),
            'js'  => $query->whereHas('category', fn ($c) => $c->where('category_name', 'like', '%janitor%'))
                           ->where(function ($sub) {
                               $sub->whereNull('description')
                                   ->orWhere('description', 'not like', '%"type":"manpower"%');
                           }),
            'man' => $query->where(function ($sub) {
                $sub->where('description', 'like', '%"type":"manpower"%')
                    ->orWhere('description', 'like', '%"activity_title"%')
                    ->orWhereHas('category', fn ($c) => $c->where('category_name', 'like', '%manpower%'));
            }),
            default => null,
        };
    }
}
