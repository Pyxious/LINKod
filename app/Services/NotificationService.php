<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    /**
     * Send a notification to a user.
     */
    public function send(int $userId, string $type, string $title, string $message, ?string $actionUrl = null): Notification
    {
        // Store relative path if absolute URL is provided to prevent port mismatch (e.g. localhost vs localhost:8000)
        if ($actionUrl) {
            $actionUrl = parse_url($actionUrl, PHP_URL_PATH) ?? $actionUrl;
        }

        $notification = Notification::create([
            'user_id'    => $userId,
            'sent_at'    => now(),
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'action_url' => $actionUrl,
            'is_read'    => false,
        ]);

        try {
            if (config('broadcasting.default') && !in_array(config('broadcasting.default'), ['null', 'log'])) {
                event(new \App\Events\NotificationSent($notification));
            }
        } catch (\Throwable $e) {
            \Log::warning('Broadcast notification skipped: ' . $e->getMessage());
        }

        return $notification;
    }

    /**
     * Notify a user about a request status change.
     */
    public function requestStatusChanged(int $userId, string $requestTitle, string $newStatus, ?int $requestId = null, string $role = 'client'): void
    {
        $actionUrl = null;
        if ($requestId) {
            if ($role === 'client') {
                $actionUrl = route('client.requests.show', $requestId, false);
            } elseif ($role === 'admin') {
                $actionUrl = route('admin.requests.show', $requestId, false);
            } elseif ($role === 'worker') {
                $actionUrl = route('worker.job-orders.show', $requestId, false);
            }
        }

        $this->send(
            $userId,
            'request_status',
            'Request Status Updated',
            "Your request \"{$requestTitle}\" status is now: {$newStatus}.",
            $actionUrl
        );
    }

    /**
     * Notify admin about a new request submission.
     */
    public function newRequestSubmitted(int $adminUserId, string $requestTitle, ?int $requestId = null): void
    {
        $actionUrl = $requestId ? route('admin.requests.show', $requestId, false) : route('admin.requests.index', [], false);

        $this->send(
            $adminUserId,
            'new_request',
            'New Service Request',
            "A new service request \"{$requestTitle}\" has been submitted and awaits review.",
            $actionUrl
        );
    }

    /**
     * Notify a worker about a new job assignment.
     */
    public function workerAssigned(int $workerUserId, string $projectTitle, ?int $projectId = null): void
    {
        $actionUrl = $projectId ? route('worker.job-orders.show', $projectId, false) : route('worker.job-orders.index', [], false);

        $this->send(
            $workerUserId,
            'job_assigned',
            'New Job Assignment',
            "You have been assigned to project: \"{$projectTitle}\".",
            $actionUrl
        );
    }

    /**
     * Notify client that BOM is available.
     */
    public function bomAvailable(int $clientUserId, string $projectTitle, ?int $projectId = null): void
    {
        $actionUrl = $projectId ? route('client.bom.show', $projectId, false) : route('client.requests.index', [], false);

        $this->send(
            $clientUserId,
            'bom_approved',
            'Bill of Materials Available',
            "The Bill of Materials for project \"{$projectTitle}\" has been approved.",
            $actionUrl
        );
    }

    /**
     * Notify admin that a task has been completed by a worker.
     */
    public function taskCompleted(int $adminUserId, string $projectTitle, int $requestId): void
    {
        $actionUrl = route('admin.requests.show', $requestId, false);
        
        $this->send(
            $adminUserId,
            'task_completed',
            'Task Completed',
            "The task for project \"{$projectTitle}\" has been marked as completed.",
            $actionUrl
        );
    }

    /**
     * Notify user about a new message on a request.
     */
    public function newMessagePosted(int $recipientUserId, string $senderName, string $requestTitle, int $requestId, string $role = 'client'): void
    {
        $actionUrl = match($role) {
            'admin'  => route('admin.requests.show', $requestId, false),
            'worker' => route('worker.job-orders.show', $requestId, false),
            default  => route('client.requests.show', $requestId, false)
        };

        $this->send(
            $recipientUserId,
            'new_message',
            'New Message Received',
            "{$senderName} sent a message regarding \"{$requestTitle}\".",
            $actionUrl . '#messages-section'
        );
    }

    /**
     * Notify client about a proposed visit schedule.
     */
    public function scheduleProposed(int $clientUserId, string $requestTitle, string $date, string $window, int $requestId): void
    {
        $actionUrl = route('client.requests.show', $requestId, false);
        $windowText = match($window) {
            'AM' => 'Morning (AM)',
            'PM' => 'Afternoon (PM)',
            'AM-PM' => 'Whole Day (AM - PM)',
            default => $window
        };

        $this->send(
            $clientUserId,
            'schedule_proposed',
            'Visit Schedule Proposed',
            "A maintenance visit schedule for \"{$requestTitle}\" has been set for {$date} ({$windowText}). Please confirm or request reschedule.",
            $actionUrl
        );
    }

    /**
     * Notify admin that client approved the visit schedule.
     */
    public function scheduleApproved(int $adminUserId, string $requestTitle, string $date, string $window, int $requestId): void
    {
        $actionUrl = route('admin.requests.show', $requestId, false);

        $this->send(
            $adminUserId,
            'schedule_approved',
            'Visit Schedule Confirmed',
            "Client confirmed the scheduled visit on {$date} ({$window}) for \"{$requestTitle}\".",
            $actionUrl
        );
    }

    /**
     * Notify admin that client declined the visit schedule.
     */
    public function scheduleDeclined(int $adminUserId, string $requestTitle, string $reason, int $requestId): void
    {
        $actionUrl = route('admin.requests.show', $requestId, false);

        $this->send(
            $adminUserId,
            'schedule_declined',
            'Visit Reschedule Requested',
            "Client requested rescheduling for \"{$requestTitle}\". Reason: {$reason}",
            $actionUrl
        );
    }

    /**
     * Notify client that BOM is verified and awaiting their approval.
     */
    public function bomVerifiedAwaitingClient(int $clientUserId, string $projectTitle, int $requestId): void
    {
        $actionUrl = route('client.requests.show', $requestId, false);

        $this->send(
            $clientUserId,
            'bom_verified',
            'Bill of Materials Verified',
            "The Bill of Materials for \"{$projectTitle}\" has been verified and priced by GSO Admin. Please review and approve.",
            $actionUrl
        );
    }

    /**
     * Notify admin & workers that client approved the BOM.
     */
    public function bomApprovedByClient(int $userId, string $projectTitle, int $requestId, string $role = 'admin'): void
    {
        $actionUrl = $role === 'admin'
            ? route('admin.requests.show', $requestId, false)
            : route('worker.job-orders.show', $requestId, false);

        $this->send(
            $userId,
            'bom_client_approved',
            'Bill of Materials Approved',
            "The client approved the Bill of Materials for \"{$projectTitle}\". Work may proceed.",
            $actionUrl
        );
    }

    /**
     * Notify admin that client declined the BOM.
     */
    public function bomDeclinedByClient(int $adminUserId, string $projectTitle, int $requestId, ?string $reason = null): void
    {
        $actionUrl = route('admin.requests.show', $requestId, false);
        $reasonText = $reason ? " Reason: {$reason}" : '';

        $this->send(
            $adminUserId,
            'bom_client_declined',
            'Bill of Materials Declined',
            "Client declined the Bill of Materials for \"{$projectTitle}\".{$reasonText}",
            $actionUrl
        );
    }

    /**
     * Remind client to rate a completed service request.
     */
    public function ratingReminder(int $clientUserId, string $requestTitle, int $requestId): void
    {
        $actionUrl = route('client.requests.show', $requestId, false);

        $this->send(
            $clientUserId,
            'rating_reminder',
            'Service Satisfaction Rating Reminder',
            "Your maintenance request \"{$requestTitle}\" has been completed. Please take a moment to rate the service received.",
            $actionUrl
        );
    }
}

