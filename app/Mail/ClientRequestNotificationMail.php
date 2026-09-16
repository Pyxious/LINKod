<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientRequestNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $clientUser,
        public ServiceRequest $serviceRequest,
        public string $eventType,
        public array $extraData = []
    ) {}

    public function envelope(): Envelope
    {
        $reqId = $this->serviceRequest->formatted_id ?? ('REQ-' . str_pad((string)$this->serviceRequest->request_id, 4, '0', STR_PAD_LEFT));
        $title = $this->serviceRequest->title;

        $subject = match ($this->eventType) {
            'submitted'         => "[LINKod] Requisition #{$reqId} Received: {$title}",
            'schedule_proposed' => "[LINKod] Maintenance Visit Scheduled: Requisition #{$reqId}",
            'bom_ready'         => "[LINKod] Action Required: List of Materials for Requisition #{$reqId}",
            'in_progress'       => "[LINKod] Work Commenced: Requisition #{$reqId} is In Progress",
            'completed'         => "[LINKod] Job Completed — Please Rate Your Service: Requisition #{$reqId}",
            default             => "[LINKod] Status Update: Requisition #{$reqId} - {$title}",
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-request-notification',
            with: [
                'clientUser'     => $this->clientUser,
                'serviceRequest' => $this->serviceRequest,
                'eventType'      => $this->eventType,
                'extraData'      => $this->extraData,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
