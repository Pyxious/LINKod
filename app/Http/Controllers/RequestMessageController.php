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
        $isClient         = ($serviceRequest->client?->user_id === $user->user_id);
        $isAdmin          = ($user->role === 'admin');
        $isAssignedWorker = false;
        if ($user->role === 'worker') {
            $worker = $user->staff?->worker;
            $workerId = $worker?->worker_id;
            $teamId   = $worker?->team_id;

            if ($serviceRequest->project) {
                foreach ($serviceRequest->project->workers as $pw) {
                    if ($pw->worker_id === $workerId || ($teamId && $pw->team_id === $teamId) || ($pw->staff?->user_id === $user->user_id)) {
                        $isAssignedWorker = true;
                        break;
                    }
                }
            }

            if (!$isAssignedWorker && $teamId) {
                $isAssignedWorker = true;
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

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $message->load('sender');
            return response()->json([
                'success' => true,
                'message' => [
                    'message_id'   => $message->message_id,
                    'message'      => $message->message,
                    'attachment'   => $message->attachment ? Storage::url($message->attachment) : null,
                    'sender_name'  => $user->first_name . ' ' . $user->last_name,
                    'sender_role'  => ucfirst($user->role ?? 'User'),
                    'created_at'   => $message->created_at->diffForHumans(),
                    'created_time' => $message->created_at->format('h:i A'),
                    'is_self'      => true,
                ]
            ]);
        }

        $prevUrl = url()->previous();
        if (str_contains($prevUrl, '/messages')) {
            return redirect()->back();
        }

        $targetUrl = str_contains($prevUrl, '#messages-section') ? $prevUrl : ($prevUrl . '#messages-section');

        return redirect()->to($targetUrl);
    }
}
