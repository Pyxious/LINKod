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
        } elseif ($this->status === 'On Hold') {
            $query->whereHas('latestHistory', function($q) {
                $q->where('current_status', 'On Hold');
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
            // Default active requests (exclude finished/cancelled)
            $query->whereHas('latestHistory', function($q) {
                $q->whereNotIn('current_status', ['Completed', 'Cancelled', 'Rejected']);
            });
        }

        if ($this->search) {
            $searchTerm = strtolower(trim($this->search));

            // Find matching client IDs by decrypting client names/emails (AES-256 encrypted columns)
            $matchingClientUserIds = \App\Models\User::where('role', 'client')
                ->get()
                ->filter(function ($user) use ($searchTerm) {
                    $fullName = strtolower("{$user->first_name} {$user->last_name}");
                    if (str_contains($fullName, $searchTerm)) {
                        return true;
                    }
                    if (str_contains(strtolower($user->email_account ?? ''), $searchTerm)) {
                        return true;
                    }
                    return false;
                })
                ->pluck('user_id');

            $matchingClientIds = \App\Models\Client::whereIn('user_id', $matchingClientUserIds)->pluck('client_id');

            // Extract numeric ID if searching by code like "CMS-005" or "005"
            $numericId = preg_replace('/\D/', '', $this->search);

            $query->where(function($q) use ($matchingClientIds, $numericId) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('location', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%")
                  ->orWhereHas('category', function($qCat) {
                      $qCat->where('category_name', 'like', "%{$this->search}%");
                  });

                if ($numericId !== '') {
                    $q->orWhere('request_id', (int)$numericId)
                      ->orWhere('request_id', 'like', "%{$numericId}%");
                } else {
                    $q->orWhere('request_id', 'like', "%{$this->search}%");
                }

                if ($matchingClientIds->isNotEmpty()) {
                    $q->orWhereIn('client_id', $matchingClientIds);
                }
            });
        }

        if ($this->sortField === 'priority') {
            $dir = strtoupper($this->sortDirection) === 'DESC' ? 'DESC' : 'ASC';
            $query->orderByRaw("CASE WHEN LOWER(priority) = 'high' THEN 1 ELSE 2 END {$dir}")
                  ->orderBy('submitted_at', 'asc')
                  ->orderBy('request_id', 'asc');
        } elseif ($this->sortField === 'submitted_at') {
            $dateDir = strtoupper($this->sortDirection) === 'DESC' ? 'DESC' : 'ASC';
            if ($this->status === 'Completed') {
                $query->orderBy('submitted_at', $dateDir)
                      ->orderBy('request_id', $dateDir);
            } else {
                $query->orderByRaw("CASE WHEN LOWER(priority) = 'high' THEN 1 ELSE 2 END ASC")
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
        } elseif (in_array($this->sortField, ['request_id', 'title', 'campus', 'location'])) {
            $query->orderBy($this->sortField, $this->sortDirection);
        } else {
            if ($this->status === 'Completed') {
                // Completed sorting: No need to follow urgent on top!
                $query->orderBy('submitted_at', 'desc')
                      ->orderBy('request_id', 'desc');
            } else {
                // Default queue: High Priority at top (FCFS), Medium & Low below (FCFS regardless of med/low)
                $query->orderByRaw("CASE WHEN LOWER(priority) = 'high' THEN 1 ELSE 2 END ASC")
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
        $onHold = ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'On Hold'))->count();
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
            'onHold'            => $onHold,
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
