<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class ClientRequestNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $clientUser,
        public ServiceRequest $serviceRequest,
        public string $eventType,
        public array $extraData = []
    ) {
        $host = parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'bicol-u.edu.ph';
        if ($host === 'localhost' || empty($host)) {
            $host = 'bicol-u.edu.ph';
        }

        $threadMessageId = "requisition-{$this->serviceRequest->request_id}@{$host}";

        $this->withSymfonyMessage(function ($message) use ($threadMessageId) {
            $headers = $message->getHeaders();

            if ($headers->has('References')) {
                $headers->remove('References');
            }
            $headers->addTextHeader('References', "<{$threadMessageId}>");

            if ($this->eventType !== 'submitted') {
                if ($headers->has('In-Reply-To')) {
                    $headers->remove('In-Reply-To');
                }
                $headers->addTextHeader('In-Reply-To', "<{$threadMessageId}>");
            }
        });
    }

    public function envelope(): Envelope
    {
        $reqId = $this->serviceRequest->formatted_id ?? ('REQ-' . str_pad((string)$this->serviceRequest->request_id, 4, '0', STR_PAD_LEFT));
        $title = $this->serviceRequest->title;

        // Unified conversation subject for Gmail/Outlook threading
        $baseSubject = "[BU-GSO LINKod] Requisition #{$reqId}: {$title}";

        $subject = ($this->eventType === 'submitted')
            ? $baseSubject
            : "Re: {$baseSubject}";

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
