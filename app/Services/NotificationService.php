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

        event(new \App\Events\NotificationSent($notification));

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
            $actionUrl
        );
    }
}
