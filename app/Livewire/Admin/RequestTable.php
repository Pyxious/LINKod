<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ServiceRequest;

class RequestTable extends Component
{
    use WithPagination;

    public $search = '';
    public $priority = '';
    public $status = '';
    public $ratingFilter = '';
    public $sortField = '';
    public $sortDirection = 'asc';

    protected $queryString = ['search', 'priority', 'status', 'ratingFilter', 'sortField', 'sortDirection'];

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
                $q->whereIn('current_status', ['Pending', 'Approved', 'Submitted']);
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
            $explicitId = null;
            if (preg_match('/^(?:REQ|LS|JS|CMS|PLS|EMS|PAINT|MAN)?[-\s#]*0*([1-9]\d*)$/i', $rawSearch, $matches)) {
                $explicitId = (int)$matches[1];
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

            $query->where(function($q) use ($rawSearch, $searchTerm, $explicitId, $matchingClientIds, $matchingWorkerIds, $words) {
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
                    $q->orWhere('request_id', $explicitId);
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

        if ($this->sortField === 'priority') {
            $dir = strtoupper($this->sortDirection) === 'DESC' ? 'DESC' : 'ASC';
            $query->orderByRaw("CASE WHEN LOWER(priority) IN ('high', 'urgent') THEN 1 ELSE 2 END {$dir}")
                  ->orderBy('submitted_at', 'asc')
                  ->orderBy('request_id', 'asc');
        } elseif ($this->sortField === 'submitted_at') {
            $dateDir = strtoupper($this->sortDirection) === 'DESC' ? 'DESC' : 'ASC';
            if ($this->status === 'Completed') {
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

            if ($this->status === 'Completed') {
                $query->orderBy($statusSub, $dir);
            } else {
                $query->orderByRaw("CASE WHEN LOWER(priority) IN ('high', 'urgent') THEN 1 ELSE 2 END ASC")
                      ->orderBy($statusSub, $dir);
            }
        } elseif (in_array($this->sortField, ['request_id', 'title', 'campus', 'location'])) {
            if ($this->status === 'Completed') {
                $query->orderBy($this->sortField, $this->sortDirection);
            } else {
                $query->orderByRaw("CASE WHEN LOWER(priority) IN ('high', 'urgent') THEN 1 ELSE 2 END ASC")
                      ->orderBy($this->sortField, $this->sortDirection);
            }
        } else {
            if ($this->status === 'Completed') {
                // Completed sorting: No need to follow urgent on top!
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

        // Dynamic KPI metrics calculated in real-time
        $totalRequests = ServiceRequest::count();
        $submitted = ServiceRequest::where(function($q) {
            $q->whereHas('latestHistory', fn($lh) => $lh->where('current_status', 'Submitted'))
              ->orWhereDoesntHave('histories');
        })->count();
        $awaitingMaterials = ServiceRequest::whereHas('latestHistory', fn($q) => $q->whereIn('current_status', [
            'Awaiting Materials',
            'Awaiting Verification of Bill of Materials',
            'BOM Verified (Awaiting Client Approval)',
            'On Hold',
        ]))->count();
        $inProgress = ServiceRequest::whereHas('latestHistory', fn($q) => $q->whereIn('current_status', ['In Progress', 'Pending Verification']))->count();
        
        $completedBase = ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'Completed'));
        $completed = (clone $completedBase)->count();
        $completedRated = (clone $completedBase)->whereHas('evaluation')->count();
        $completedNotRated = (clone $completedBase)->whereDoesntHave('evaluation')->count();

        $recurringCount = ServiceRequest::recurring()->count();

        return view('livewire.admin.request-table', [
            'requests'          => $requests,
            'totalRequests'     => $totalRequests,
            'submitted'         => $submitted,
            'onHold'            => $awaitingMaterials,
            'awaitingMaterials' => $awaitingMaterials,
            'inProgress'        => $inProgress,
            'completed'         => $completed,
            'completedRated'    => $completedRated,
            'completedNotRated' => $completedNotRated,
            'recurringCount'    => $recurringCount,
        ]);
    }

    #[\Livewire\Attributes\On('refreshRequests')]
    public function refreshRequests()
    {
        // Triggers fresh render cycle
    }
}
