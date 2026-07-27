<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\RequestMessage;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RequestMessageController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function store(Request $request, int $requestId)
    {
        $validated = $request->validate([
            'message'    => 'required|string|max:2000',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
        ]);

        $serviceRequest = ServiceRequest::with([
            'client.user',
            'project.workers.staff.user',
        ])->findOrFail($requestId);

        $user = auth()->user();

        // 1. Authorization check
        $isClient = ($serviceRequest->client?->user_id === $user->user_id);
        $isAdmin = ($user->role === 'admin');
        $isAssignedWorker = false;

        if ($user->role === 'worker' && $serviceRequest->project) {
            foreach ($serviceRequest->project->workers as $pw) {
                if ($pw->staff?->user_id === $user->user_id) {
                    $isAssignedWorker = true;
                    break;
                }
            }
        }

        if (!$isClient && !$isAdmin && !$isAssignedWorker) {
            return redirect()->back()->with('error', 'You are not authorized to send messages on this requisition.');
        }

        // 2. Resolved Status Check
        if ($serviceRequest->isResolved()) {
            return redirect()->back()->with('error', 'This requisition is resolved/completed. Messaging is locked.');
        }

        // 3. Process Attachment if present
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('messages', 'public');
        }

        // 4. Create Message
        $message = RequestMessage::create([
            'request_id' => $serviceRequest->request_id,
            'sender_id'  => $user->user_id,
            'message'    => trim($validated['message']),
            'attachment' => $attachmentPath,
            'is_read'    => false,
        ]);

        // 5. Notify Other Participants
        $senderName = $user->first_name . ' ' . $user->last_name;

        // Notify Client if sender is not client
        if (!$isClient && $serviceRequest->client?->user_id) {
            $this->notifications->newMessagePosted(
                $serviceRequest->client->user_id,
                $senderName,
                $serviceRequest->title,
                $serviceRequest->request_id,
                'client'
            );
        }

        // Notify Admin users if sender is not admin
        if (!$isAdmin) {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $this->notifications->newMessagePosted(
                    $admin->user_id,
                    $senderName,
                    $serviceRequest->title,
                    $serviceRequest->request_id,
                    'admin'
                );
            }
        }

        // Notify Assigned Workers if sender is not worker
        if (!$isAssignedWorker && $serviceRequest->project) {
            foreach ($serviceRequest->project->workers as $pw) {
                $workerUser = $pw->staff?->user;
                if ($workerUser && $workerUser->user_id !== $user->user_id) {
                    $this->notifications->newMessagePosted(
                        $workerUser->user_id,
                        $senderName,
                        $serviceRequest->title,
                        $serviceRequest->request_id,
                        'worker'
                    );
                }
            }
        }

        return redirect()->back()->with('success', 'Message sent successfully.');
    }
}
