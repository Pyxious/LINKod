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
    public $sortField = '';
    public $sortDirection = 'asc';

    protected $queryString = ['search', 'priority', 'status', 'sortField', 'sortDirection'];

    public function setPriority($prio)
    {
        $this->priority = $this->priority === $prio ? '' : $prio;
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
        $this->resetPage();
    }

    public function updatingPriority()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = ServiceRequest::with('client.user', 'category', 'latestHistory', 'project.workers.staff.user', 'project.workers.team');

        if ($this->priority) {
            $query->where('priority', $this->priority);
        }

        if ($this->status === 'Completed') {
            $query->whereHas('latestHistory', function($q) {
                $q->where('current_status', 'Completed');
            });
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
        } elseif ($this->status === 'all') {
            // No status constraint - show everything
        } else {
            // Default active requests (exclude finished/cancelled)
            $query->whereHas('latestHistory', function($q) {
                $q->whereNotIn('current_status', ['Completed', 'Cancelled', 'Rejected']);
            });
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('request_id', 'like', "%{$this->search}%")
                  ->orWhere('title', 'like', "%{$this->search}%")
                  ->orWhere('location', 'like', "%{$this->search}%")
                  ->orWhereHas('category', function($qCat) {
                      $qCat->where('category_name', 'like', "%{$this->search}%");
                  })
                  ->orWhereHas('client.user', function($q2) {
                      $q2->where('first_name', 'like', "%{$this->search}%")
                         ->orWhere('last_name', 'like', "%{$this->search}%");
                  });
            });
        }

        if ($this->sortField === 'priority') {
            $dir = strtoupper($this->sortDirection) === 'DESC' ? 'DESC' : 'ASC';
            $query->orderByRaw("CASE WHEN LOWER(priority) = 'high' THEN 1 ELSE 2 END {$dir}")
                  ->orderBy('submitted_at', 'asc')
                  ->orderBy('request_id', 'asc');
        } elseif ($this->sortField === 'submitted_at') {
            $dateDir = strtoupper($this->sortDirection) === 'DESC' ? 'DESC' : 'ASC';
            $query->orderByRaw("CASE WHEN LOWER(priority) = 'high' THEN 1 ELSE 2 END ASC")
                  ->orderBy('submitted_at', $dateDir)
                  ->orderBy('request_id', $dateDir);
        } elseif (in_array($this->sortField, ['request_id', 'title', 'campus', 'location'])) {
            $query->orderBy($this->sortField, $this->sortDirection);
        } else {
            // Default queue: High Priority at top (FCFS), Medium & Low below (FCFS regardless of med/low)
            $query->orderByRaw("CASE WHEN LOWER(priority) = 'high' THEN 1 ELSE 2 END ASC")
                  ->orderBy('submitted_at', 'asc')
                  ->orderBy('request_id', 'asc');
        }

        $requests = $query->paginate(15);

        // Dynamic KPI metrics calculated in real-time
        $totalRequests = ServiceRequest::count();
        $submitted = ServiceRequest::where(function($q) {
            $q->whereHas('latestHistory', fn($lh) => $lh->where('current_status', 'Submitted'))
              ->orWhereDoesntHave('histories');
        })->count();
        $onHold = ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'On Hold'))->count();
        $inProgress = ServiceRequest::whereHas('latestHistory', fn($q) => $q->whereIn('current_status', ['In Progress', 'Pending Verification']))->count();
        $completed = ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'Completed'))->count();

        return view('livewire.admin.request-table', [
            'requests'      => $requests,
            'totalRequests' => $totalRequests,
            'submitted'     => $submitted,
            'onHold'        => $onHold,
            'inProgress'    => $inProgress,
            'completed'     => $completed,
        ]);
    }

    #[\Livewire\Attributes\On('refreshRequests')]
    public function refreshRequests()
    {
        // Triggers fresh render cycle
    }
}
