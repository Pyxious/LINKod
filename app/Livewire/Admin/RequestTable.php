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

    protected $queryString = ['search', 'priority', 'status'];

    public function setPriority($prio)
    {
        $this->priority = $this->priority === $prio ? '' : $prio;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = ServiceRequest::with('client.user', 'category', 'latestHistory', 'project.workers.staff.user', 'project.workers.team')
            ->orderByRaw("FIELD(priority, 'High', 'Medium', 'Low')")
            ->orderBy('submitted_at', 'asc');

        if ($this->priority) {
            $query->where('priority', $this->priority);
        }

        if ($this->status) {
            $query->whereHas('latestHistory', function($q) {
                if ($this->status === 'Pending') {
                    $q->whereIn('current_status', ['Pending', 'Approved']);
                } else {
                    $q->where('current_status', $this->status);
                }
            });
        } else {
            // Default: show everything EXCEPT completed/cancelled/rejected
            $query->whereHas('latestHistory', function($q) {
                $q->whereNotIn('current_status', ['Completed', 'Cancelled', 'Rejected']);
            });
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('request_id', 'like', "%{$this->search}%")
                  ->orWhere('title', 'like', "%{$this->search}%")
                  ->orWhereHas('client.user', function($q2) {
                      $q2->where('first_name', 'like', "%{$this->search}%")
                         ->orWhere('last_name', 'like', "%{$this->search}%");
                  });
            });
        }

        $requests = $query->paginate(15);

        return view('livewire.admin.request-table', [
            'requests' => $requests
        ]);
    }
}
