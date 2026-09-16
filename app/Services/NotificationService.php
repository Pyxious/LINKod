<?php

namespace App\Services;

use App\Mail\ClientRequestNotificationMail;
use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Cache sent email types during request lifecycle to prevent duplicates.
     */
    protected static array $sentEmailKeys = [];
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
     * Safely send an automated branded email notification to a client.
     * Wrapped in try/catch so email/network errors never disrupt DB transactions.
     */
    public function sendClientEmail(int|User $user, ServiceRequest $serviceRequest, string $eventType, array $extraData = []): void
    {
        $cacheKey = "{$serviceRequest->request_id}_{$eventType}";
        if (isset(self::$sentEmailKeys[$cacheKey])) {
            return;
        }

        try {
            $clientUser = ($user instanceof User) ? $user : User::find($user);
            if (!$clientUser || empty($clientUser->email_account)) {
                return;
            }

            // Ensure relations like category and project are loaded if needed
            if (!$serviceRequest->relationLoaded('category')) {
                $serviceRequest->load('category');
            }
            if (!$serviceRequest->relationLoaded('project')) {
                $serviceRequest->load('project');
            }

            Mail::to($clientUser->email_account)->send(
                new ClientRequestNotificationMail($clientUser, $serviceRequest, $eventType, $extraData)
            );

            self::$sentEmailKeys[$cacheKey] = true;
        } catch (\Throwable $e) {
            Log::warning("Client email notification failed [{$eventType}] for request #{$serviceRequest->request_id}: " . $e->getMessage());
        }
    }

    /**
     * Notify client that their new requisition was submitted and received.
     */
    public function requestSubmitted(ServiceRequest $serviceRequest): void
    {
        if (!$serviceRequest->relationLoaded('client.user')) {
            $serviceRequest->load('client.user');
        }

        $clientUser = $serviceRequest->client?->user;
        if ($clientUser) {
            $this->sendClientEmail($clientUser, $serviceRequest, 'submitted');
        }
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

        // Client automated email notification for status progression
        if ($role === 'client' && $requestId) {
            $normalizedStatus = strtolower(trim($newStatus));
            if ($normalizedStatus === 'in progress') {
                $req = ServiceRequest::find($requestId);
                if ($req) {
                    $this->sendClientEmail($userId, $req, 'in_progress');
                }
            } elseif ($normalizedStatus === 'completed') {
                $req = ServiceRequest::find($requestId);
                if ($req) {
                    $this->sendClientEmail($userId, $req, 'completed');
                }
            }
        }
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
     * Notify client that List of Materials is available.
     */
    public function bomAvailable(int $clientUserId, string $projectTitle, ?int $projectId = null): void
    {
        $actionUrl = $projectId ? route('client.bom.show', $projectId, false) : route('client.requests.index', [], false);

        $this->send(
            $clientUserId,
            'bom_approved',
            'List of Materials Available',
            "The List of Materials for project \"{$projectTitle}\" has been approved.",
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

        $req = ServiceRequest::find($requestId);
        if ($req) {
            $this->sendClientEmail($clientUserId, $req, 'schedule_proposed', [
                'scheduled_date'   => $date,
                'scheduled_window' => $windowText,
            ]);
        }
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
     * Notify client that List of Materials is verified and awaiting their approval.
     */
    public function bomVerifiedAwaitingClient(int $clientUserId, string $projectTitle, int $requestId): void
    {
        $actionUrl = route('client.requests.show', $requestId, false);

        $this->send(
            $clientUserId,
            'bom_verified',
            'List of Materials Verified',
            "The List of Materials for \"{$projectTitle}\" has been verified by GSO Admin. Please review and approve.",
            $actionUrl
        );

        $req = ServiceRequest::find($requestId);
        if ($req) {
            $this->sendClientEmail($clientUserId, $req, 'bom_ready');
        }
    }

    /**
     * Notify admin & workers that client approved the List of Materials.
     */
    public function bomApprovedByClient(int $userId, string $projectTitle, int $requestId, string $role = 'admin'): void
    {
        $actionUrl = $role === 'admin'
            ? route('admin.requests.show', $requestId, false)
            : route('worker.job-orders.show', $requestId, false);

        $this->send(
            $userId,
            'bom_client_approved',
            'List of Materials Approved',
            "The client approved the List of Materials for \"{$projectTitle}\". Work may proceed.",
            $actionUrl
        );
    }

    /**
     * Notify admin that client declined the List of Materials.
     */
    public function bomDeclinedByClient(int $adminUserId, string $projectTitle, int $requestId, ?string $reason = null): void
    {
        $actionUrl = route('admin.requests.show', $requestId, false);
        $reasonText = $reason ? " Reason: {$reason}" : '';

        $this->send(
            $adminUserId,
            'bom_client_declined',
            'List of Materials Declined',
            "Client declined the List of Materials for \"{$projectTitle}\".{$reasonText}",
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

        $req = ServiceRequest::find($requestId);
        if ($req) {
            $this->sendClientEmail($clientUserId, $req, 'completed');
        }
    }
}

