<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestHistory extends Model
{
    protected $table = 'request_history';
    protected $primaryKey = 'history_id';
    public $timestamps = false;

    protected $fillable = [
        'request_id', 'previous_status', 'current_status', 'remarks', 'updated_at', 'updated_by',
    ];

    protected $casts = ['updated_at' => 'datetime'];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id', 'request_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    /**
     * Get a specific, concise action title for the timeline.
     */
    public function getActionTitleAttribute(): string
    {
        $status = trim($this->current_status ?? '');
        $remarks = strtolower($this->remarks ?? '');

        // Schedule actions
        if (str_contains($remarks, 'proposed visit schedule') || str_contains($remarks, 'proposed schedule') || $status === 'Schedule Set' || $status === 'Visit Schedule Proposed') {
            return 'Schedule Proposed';
        }
        if (str_contains($remarks, 'requested rescheduling') || str_contains($remarks, 'declined schedule') || str_contains($remarks, 'refused schedule') || $status === 'Schedule Refused' || $status === 'Reschedule Requested') {
            return 'Schedule Declined';
        }
        if (str_contains($remarks, 'confirmed visit schedule') || str_contains($remarks, 'schedule confirmed') || str_contains($remarks, 'approved schedule') || $status === 'Schedule Confirmed') {
            return 'Schedule Confirmed';
        }

        // BOM actions
        if (str_contains($remarks, 'on-site direct') || $status === 'BOM Approved (On-Site Direct)') {
            return 'BOM Approved (On-Site)';
        }
        if (str_contains($remarks, 'client approved bill of materials') || str_contains($remarks, 'approved bom') || $status === 'BOM Approved by Client') {
            return 'BOM Approved';
        }
        if (str_contains($remarks, 'client declined bill of materials') || str_contains($remarks, 'declined bom') || $status === 'BOM Declined by Client') {
            return 'BOM Declined';
        }
        if (str_contains($remarks, 'admin verified bill of materials') || str_contains($remarks, 'bom verified') || $status === 'BOM Verified (Awaiting Client Approval)') {
            return 'BOM Verified';
        }
        if (str_contains($remarks, 'submitted bill of materials') || str_contains($remarks, 'awaiting verification of bill of materials') || $status === 'Awaiting Verification of Bill of Materials') {
            return 'BOM Submitted';
        }

        // Client evaluation / rating actions
        if (str_contains($remarks, 'rated the service') || str_contains($remarks, 'client rated') || str_contains($remarks, 'submitted satisfaction') || $status === 'Evaluated' || $status === 'Client Rated Service') {
            return 'Client Rated Service';
        }

        // Standard Lifecycle actions
        return match($status) {
            'Submitted'            => 'Submitted',
            'Approved'             => 'Approved',
            'Rejected'             => 'Rejected',
            'In Progress'          => 'In Progress',
            'Pending Verification' => 'Acceptance',
            'Completed'            => 'Completed',
            'Cancelled'            => 'Cancelled',
            'On Hold'              => 'On Hold',
            default                => $status ?: 'Updated'
        };
    }

    /**
     * Get badge color styling based on action type.
     */
    public function getBadgeColorClassAttribute(): string
    {
        $title = $this->action_title;

        return match(true) {
            str_contains($title, 'Rejected') || str_contains($title, 'Cancelled') || str_contains($title, 'Declined')
                => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900',

            str_contains($title, 'Confirmed') || str_contains($title, 'Approved') || str_contains($title, 'Completed') || str_contains($title, 'Acceptance') || str_contains($title, 'Client Rated') || str_contains($title, 'Evaluated')
                => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900',

            str_contains($title, 'Proposed') || str_contains($title, 'Submitted') || str_contains($title, 'Pending') || str_contains($title, 'On Hold')
                => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900',

            default
                => 'bg-blue-50 text-[#0038A8] border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-900',
        };
    }

    /**
     * Get timeline bullet point color.
     */
    public function getBulletColorClassAttribute(): string
    {
        $title = $this->action_title;

        return match(true) {
            str_contains($title, 'Rejected') || str_contains($title, 'Cancelled') || str_contains($title, 'Declined')
                => 'bg-rose-500 ring-rose-100 dark:ring-rose-950',

            str_contains($title, 'Confirmed') || str_contains($title, 'Approved') || str_contains($title, 'Completed') || str_contains($title, 'Acceptance') || str_contains($title, 'Client Rated') || str_contains($title, 'Evaluated')
                => 'bg-emerald-500 ring-emerald-100 dark:ring-emerald-950',

            str_contains($title, 'Proposed') || str_contains($title, 'Submitted') || str_contains($title, 'Pending') || str_contains($title, 'On Hold')
                => 'bg-amber-500 ring-amber-100 dark:ring-amber-950',

            default
                => 'bg-blue-600 ring-blue-100 dark:ring-blue-950',
        };
    }
}
