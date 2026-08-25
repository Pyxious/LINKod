<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\RequestMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessagePortalController extends Controller
{
    /**
     * Display the Grab-style dual-pane messaging portal.
     */
    public function index(Request $request, ?int $requestId = null)
    {
        $user = Auth::user();
        $query = ServiceRequest::with(['client.user', 'category', 'project.workers.staff.user', 'messages.sender']);

        $isClientPortal = request()->is('client/*') || request()->routeIs('client.*');
        $effectiveRole  = $isClientPortal ? 'client' : $user->role;

        // Role-based filtering
        if ($effectiveRole === 'client') {
            $clientId = $user->client?->client_id;
            if (!$clientId && $user) {
                $client = \App\Models\Client::firstOrCreate(['user_id' => $user->user_id]);
                $clientId = $client->client_id;
            }
            $query->where('client_id', $clientId);
        } elseif ($effectiveRole === 'worker') {
            $worker = $user->staff?->worker;
            $workerId = $worker?->worker_id;
            $teamId   = $worker?->team_id;

            $query->where(function($q) use ($workerId, $teamId) {
                if ($workerId || $teamId) {
                    $q->whereHas('project.workers', function($pw) use ($workerId, $teamId) {
                        $pw->where(function($wq) use ($workerId, $teamId) {
                            if ($workerId) {
                                $wq->where('worker.worker_id', $workerId);
                            }
                            if ($teamId) {
                                $wq->orWhere('worker.team_id', $teamId);
                            }
                        });
                    });
                }

                // Also allow workers to view conversations where they sent messages
                if (auth()->id()) {
                    $q->orWhereHas('messages', function($mq) {
                        $mq->where('sender_id', auth()->id());
                    });
                }
            });
        }
        // Admin sees all requests

        // Status tab filter (active, all, resolved, cancelled) - Defaults to 'active' on page open
        $statusFilter = strtolower($request->query('status', 'active'));
        if ($statusFilter === 'active') {
            $query->where(function($q) {
                $q->whereHas('latestHistory', function($lh) {
                    $lh->whereNotIn('current_status', ['Completed', 'Cancelled', 'Rejected']);
                })->orWhereDoesntHave('histories');
            });
        } elseif ($statusFilter === 'resolved') {
            $query->whereHas('latestHistory', function($lh) {
                $lh->where('current_status', 'Completed');
            });
        } elseif ($statusFilter === 'cancelled') {
            $query->whereHas('latestHistory', function($lh) {
                $lh->whereIn('current_status', ['Cancelled', 'Rejected']);
            });
        }


        $requests = $query->orderByRaw("
            CASE 
                WHEN LOWER(priority) = 'high' THEN 1 
                ELSE 2 
            END ASC
        ")->orderBy('submitted_at', 'asc')->orderBy('request_id', 'asc')->get();

        // Perform search filtering on loaded Collection (supports decrypted user PII names, requisition codes, categories, titles, locations, emails)
        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $numericId = (int) preg_replace('/[^0-9]/', '', $search);

            $requests = $requests->filter(function($req) use ($search, $numericId) {
                $clientUser = $req->client?->user;
                $firstName  = strtolower($clientUser?->first_name ?? '');
                $lastName   = strtolower($clientUser?->last_name ?? '');
                $fullName   = trim("{$firstName} {$lastName}");
                $email      = strtolower($clientUser?->email_account ?? '');
                $title      = strtolower($req->title ?? '');
                $location   = strtolower($req->location ?? '');
                $campus     = strtolower($req->campus ?? '');
                $catName    = strtolower($req->category?->category_name ?? '');

                $prefix = match(true) {
                    str_contains($catName, 'landscaping') => 'ls',
                    str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'ems',
                    str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'cms',
                    str_contains($catName, 'plumbing') => 'pls',
                    str_contains($catName, 'painting') => 'paint',
                    default => 'req'
                };
                $reqCode = strtolower($req->requisition_no ?: ($prefix . '-' . str_pad($req->request_id, 3, '0', STR_PAD_LEFT)));
                $rawReqId = (string) $req->request_id;

                return str_contains($title, $search)
                    || str_contains($location, $search)
                    || str_contains($campus, $search)
                    || str_contains($catName, $search)
                    || str_contains($firstName, $search)
                    || str_contains($lastName, $search)
                    || str_contains($fullName, $search)
                    || str_contains($email, $search)
                    || str_contains($reqCode, $search)
                    || str_contains($rawReqId, $search)
                    || ($numericId > 0 && $req->request_id === $numericId);
            });
        }

        // Selected Service Request
        $selectedRequest = null;
        if ($requestId) {
            $selectedRequest = $requests->firstWhere('request_id', $requestId);
        }

        if (!$selectedRequest && $requests->isNotEmpty()) {
            $selectedRequest = $requests->first();
        }

        // Mark viewed conversation messages as read
        if ($selectedRequest && $user) {
            RequestMessage::where('request_id', $selectedRequest->request_id)
                ->where('sender_id', '!=', $user->user_id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        // Calculate unread counts per request for the sidebar/list
        $unreadCounts = [];
        if ($user && $requests->isNotEmpty()) {
            $unreadCounts = RequestMessage::where('is_read', false)
                ->where('sender_id', '!=', $user->user_id)
                ->whereIn('request_id', $requests->pluck('request_id'))
                ->groupBy('request_id')
                ->selectRaw('request_id, count(*) as count')
                ->pluck('count', 'request_id')
                ->toArray();
        }

        // Determine view request link for selected request
        $viewRequestUrl = null;
        if ($selectedRequest) {
            $viewRequestUrl = match ($effectiveRole) {
                'admin'  => route('admin.requests.show', $selectedRequest->request_id),
                'worker' => $selectedRequest->project ? route('worker.job-orders.show', $selectedRequest->project->project_id) : '#',
                default  => route('client.requests.show', $selectedRequest->request_id),
            };
        }

        return view('messages.index', compact(
            'requests',
            'selectedRequest',
            'viewRequestUrl',
            'statusFilter',
            'unreadCounts'
        ));
    }
}
