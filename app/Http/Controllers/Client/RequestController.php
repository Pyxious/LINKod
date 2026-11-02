<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ServiceRequest;
use App\Models\RequestHistory;
use App\Services\DecisionTreeService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class RequestController extends Controller
{
    public function __construct(
        protected DecisionTreeService $decisionTree,
        protected NotificationService $notifications
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $client = $user->client;

        if ($client) {
            $nonPendingStatuses = ['In Progress', 'Pending Verification', 'Completed', 'Cancelled', 'Rejected'];

            // Cache per-client KPI counts (60s)
            $kpi = Cache::remember("client_{$client->client_id}_request_kpi_v2", 60, function () use ($client, $nonPendingStatuses) {
                $baseQuery = $client->requests();
                return [
                    'totalRequests'       => (clone $baseQuery)->count(),
                    'pendingCount'        => (clone $baseQuery)->where(function ($q) use ($nonPendingStatuses) {
                        $q->whereHas('latestHistory', fn($lh) => $lh->whereNotIn('current_status', $nonPendingStatuses))
                          ->orWhereDoesntHave('histories');
                    })->count(),
                    'inProgressCount'     => (clone $baseQuery)->whereHas('latestHistory', fn($lh) => $lh->whereIn('current_status', ['In Progress', 'Pending Verification']))->count(),
                    'completedCount'      => (clone $baseQuery)->whereHas('latestHistory', fn($lh) => $lh->where('current_status', 'Completed'))->count(),
                    'cancelledCount'      => (clone $baseQuery)->whereHas('latestHistory', fn($lh) => $lh->where('current_status', 'Cancelled'))->count(),
                    'actionRequiredCount' => (clone $baseQuery)->where(function ($q) {
                        $q->where('bom_status', 'awaiting_client')
                          ->orWhereHas('latestHistory', fn($lh) => $lh->whereIn('current_status', [
                              'BOM Verified (Awaiting Client Approval)',
                              'List of Materials Verified (Awaiting Client Approval)',
                          ]))
                          ->orWhere(function ($sq) {
                              $sq->where('schedule_status', 'pending_client_approval')
                                 ->whereHas('latestHistory', fn($lh) => $lh->whereNotIn('current_status', ['Cancelled', 'Rejected', 'Completed']));
                          })
                          ->orWhere(function ($eq) {
                              $eq->whereHas('latestHistory', fn($lh) => $lh->where('current_status', 'Completed'))
                                 ->doesntHave('evaluation');
                          });
                    })->count(),
                ];
            });
            extract($kpi);

            $query = $client->requests()->with(['category', 'latestHistory', 'evaluation']);

            // Status Filter
            if ($request->filled('status') && $request->status !== 'all') {
                $status = strtolower($request->status);
                if ($status === 'pending') {
                    $query->where(function ($q) use ($nonPendingStatuses) {
                        $q->whereHas('latestHistory', fn($lh) => $lh->whereNotIn('current_status', $nonPendingStatuses))
                          ->orWhereDoesntHave('histories');
                    });
                } elseif ($status === 'in_progress') {
                    $query->whereHas('latestHistory', fn($lh) => $lh->whereIn('current_status', ['In Progress', 'Pending Verification']));
                } elseif ($status === 'action_required') {
                    $query->where(function ($q) {
                        $q->where('bom_status', 'awaiting_client')
                          ->orWhereHas('latestHistory', fn($lh) => $lh->whereIn('current_status', [
                              'BOM Verified (Awaiting Client Approval)',
                              'List of Materials Verified (Awaiting Client Approval)',
                          ]))
                          ->orWhere(function ($sq) {
                              $sq->where('schedule_status', 'pending_client_approval')
                                 ->whereHas('latestHistory', fn($lh) => $lh->whereNotIn('current_status', ['Cancelled', 'Rejected', 'Completed']));
                          })
                          ->orWhere(function ($eq) {
                              $eq->whereHas('latestHistory', fn($lh) => $lh->where('current_status', 'Completed'))
                                 ->doesntHave('evaluation');
                          });
                    });
                } else {
                    $query->whereHas('latestHistory', fn($lh) => $lh->where('current_status', ucfirst($status)));
                }
            }

            // Search / Tracking Code / Keyword Query
            $rawSearch = trim((string) ($request->input('search') ?? $request->input('tracking_number') ?? ''));
            if ($rawSearch !== '') {
                $filtered = $query->latest('submitted_at')
                    ->get()
                    ->filter(fn (ServiceRequest $r) => $r->matchesSearchQuery($rawSearch))
                    ->values();

                $perPage = 10;
                $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
                $currentItems = $filtered->slice(($currentPage - 1) * $perPage, $perPage)->values();

                $requests = new \Illuminate\Pagination\LengthAwarePaginator(
                    $currentItems,
                    $filtered->count(),
                    $perPage,
                    $currentPage,
                    [
                        'path'  => $request->url(),
                        'query' => $request->query(),
                    ]
                );
            } else {
                $requests = $query->latest('submitted_at')->paginate(10)->appends($request->query());
            }
        } else {
            $totalRequests = 0;
            $pendingCount = 0;
            $inProgressCount = 0;
            $completedCount = 0;
            $cancelledCount = 0;
            $actionRequiredCount = 0;
            $requests = collect();
        }

        return view('client.requests.index', compact(
            'requests', 'totalRequests', 'pendingCount', 'inProgressCount', 'completedCount', 'cancelledCount', 'actionRequiredCount'
        ));
    }

    public function track(Request $request)
    {
        $term = trim((string) ($request->input('tracking_number') ?? $request->input('search') ?? ''));
        $client = auth()->user()?->client;

        if ($term !== '') {
            if ($client && preg_match('/^([a-z]{1,6})?[-\s#]*0*([1-9]\d*)$/i', $term, $matches)) {
                $typedPrefix = strtolower($matches[1] ?? '');
                $typedPrefix = match ($typedPrefix) {
                    'paint' => 'pas',
                    'ps'    => 'pls',
                    default => $typedPrefix,
                };
                $exactRequest = $client->requests()->with('category')->where('request_id', (int) $matches[2])->first();
                if ($exactRequest) {
                    $actualPrefix = strtolower(explode('-', $exactRequest->tracking_code)[0] ?? 'req');
                    if ($typedPrefix === '' || $typedPrefix === 'req' || $typedPrefix === $actualPrefix) {
                        return redirect()->route('client.requests.show', $exactRequest->request_id);
                    }
                }
            }

            return redirect()->route('client.requests.index', ['search' => $term]);
        }

        return redirect()->route('client.requests.index');
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $client = $user?->client;
        $unratedCompletedRequest = null;
        $activeQuotaCount = 0;
        $reachedMaxActiveRequests = false;
        $activeClientRequestsData = [];

        if ($client) {
            $unratedCompletedRequest = $client->requests()
                ->whereHas('latestHistory', fn($q) => $q->where('current_status', 'Completed'))
                ->doesntHave('evaluation')
                ->latest('submitted_at')
                ->first();

            $unfinishedRequests = ServiceRequest::getUnfinishedRequestsForClient((int) $client->client_id);
            $activeQuotaCount = $unfinishedRequests->count();
            $reachedMaxActiveRequests = $activeQuotaCount >= 5;

            if (!$reachedMaxActiveRequests) {
                $activeClientRequestsData = $unfinishedRequests->map(fn (ServiceRequest $r) => [
                    'request_id'    => $r->request_id,
                    'tracking_code' => $r->tracking_code,
                    'status'        => $r->current_status,
                    'category_id'   => (int) $r->category_id,
                    'campus_norm'   => ServiceRequest::normalizeText($r->campus),
                    'location_norm' => ServiceRequest::normalizeText($r->location),
                    'title_norm'    => ServiceRequest::normalizeText($r->title),
                    'desc_norm'     => ServiceRequest::normalizeDescriptionForComparison($r->description),
                    'show_url'      => route('client.requests.show', $r->request_id),
                ])->values()->all();
            }
        }

        $categories = Category::orderBy('category_name')->get();
        $preselectedCatId = null;

        if ($request->filled('category')) {
            $catQuery = trim(strtolower($request->query('category')));
            $foundCat = $categories->first(function($c) use ($catQuery) {
                $name = strtolower($c->category_name);
                return str_contains($name, $catQuery) || str_contains($catQuery, $name);
            });
            if ($foundCat) {
                $preselectedCatId = $foundCat->category_id;
            }
        }

        return view('client.requests.create', compact(
            'categories',
            'preselectedCatId',
            'unratedCompletedRequest',
            'activeQuotaCount',
            'reachedMaxActiveRequests',
            'activeClientRequestsData'
        ));
    }

    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $client = $user->client;

            if ($client) {
                $unratedCompletedRequest = $client->requests()
                    ->whereHas('latestHistory', fn($q) => $q->where('current_status', 'Completed'))
                    ->doesntHave('evaluation')
                    ->latest('submitted_at')
                    ->first();

                if ($unratedCompletedRequest) {
                    return redirect()->route('client.evaluations.create', $unratedCompletedRequest->request_id)
                        ->with('error', 'Please rate your completed request first.');
                }

                // Overall 5-request cap across all services (only reduced when disapproved or completed & rated)
                if (ServiceRequest::countUnfinishedRequestsForClient((int) $client->client_id) >= 5) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Request limit reached (5/5): Wait until a request is disapproved or completed & rated.');
                }
            }

            $validated = $request->validate([
                'category_id'    => 'required|exists:category,category_id',
                'title'          => 'required|string|max:150',
                'description'    => 'nullable|string',
                'campus'         => 'required|string|max:100',
                'location'       => 'required|string|max:255',
                'complexity'     => 'required|in:low,medium,high',
                'urgency'        => 'required|in:low,medium,high',
                'attachment'     => ['nullable', 'file', new \App\Rules\SecureFileUpload(['pdf', 'jpg', 'jpeg', 'png', 'webp'], 10240)],
                'contact_number' => 'required|string|regex:/^09\d{9}$/',
            ]);

            // Update user's contact number if it has changed
            if ($user->contact_number !== $validated['contact_number']) {
                $user->update(['contact_number' => $validated['contact_number']]);
            }

            unset($validated['contact_number']);

            // Package Manpower/Janitorial structured details only if category is Janitorial & Manpower (Category ID 4)
            $isJanitorialAndManpowerCategory = ((int)$validated['category_id'] === 4);

            if ($isJanitorialAndManpowerCategory) {
                if ($request->filled('activity_title') || $request->filled('prep_details') || $request->filled('prep_date') || $request->filled('assistance_details') || $request->filled('clearing_details')) {
                    $timeRegex = '/^(?:(0?[1-9]|1[0-2]):[0-5][0-9]\s*(?:AM|PM|am|pm)\s*(?:-|–|to)\s*(0?[1-9]|1[0-2]):[0-5][0-9]\s*(?:AM|PM|am|pm)|(0?[1-9]|1[0-2]):[0-5][0-9]\s*(?:AM|PM|am|pm)\s*onwards)$/i';

                    if ($request->boolean('prep_overtime') && !preg_match($timeRegex, trim($request->input('prep_overtime_time', '')))) {
                        return redirect()->back()->withInput()->with('error', 'Please enter a valid Preparation Overtime time format (e.g., 5:00PM-6:00PM).');
                    }
                    if ($request->boolean('assistance_overtime') && !preg_match($timeRegex, trim($request->input('assistance_overtime_time', '')))) {
                        return redirect()->back()->withInput()->with('error', 'Please enter a valid Event Assistance Overtime time format (e.g., 5:00PM-6:00PM).');
                    }
                    if ($request->boolean('clearing_overtime') && !preg_match($timeRegex, trim($request->input('clearing_overtime_time', '')))) {
                        return redirect()->back()->withInput()->with('error', 'Please enter a valid Clearing Overtime time format (e.g., 5:00PM-6:00PM).');
                    }

                    $manpowerData = [
                        'activity_title'          => $request->input('activity_title', $validated['title']),
                        'event_date'              => $request->input('event_date', ''),
                        'venue'                   => $request->input('venue', $validated['location']),
                        'prep_date'               => $request->input('prep_date', ''),
                        'prep_details'            => $request->input('prep_details', ''),
                        'prep_regular'            => $request->boolean('prep_regular', true),
                        'prep_overtime'           => $request->boolean('prep_overtime', false),
                        'prep_regular_time'       => $request->input('prep_regular_time', '8:00 - 12:00 / 1:00 - 5:00'),
                        'prep_overtime_time'      => $request->boolean('prep_overtime') ? trim($request->input('prep_overtime_time', '')) : '',
                        'assistance_date'         => $request->input('assistance_date', ''),
                        'assistance_details'      => $request->input('assistance_details', ''),
                        'assistance_regular'      => $request->boolean('assistance_regular', true),
                        'assistance_overtime'     => $request->boolean('assistance_overtime', false),
                        'assistance_regular_time' => $request->input('assistance_regular_time', '8:00 - 12:00 / 1:00 - 5:00'),
                        'assistance_overtime_time'=> $request->boolean('assistance_overtime') ? trim($request->input('assistance_overtime_time', '')) : '',
                        'clearing_date'           => $request->input('clearing_date', ''),
                        'clearing_details'        => $request->input('clearing_details', ''),
                        'clearing_regular'        => $request->boolean('clearing_regular', true),
                        'clearing_overtime'       => $request->boolean('clearing_overtime', false),
                        'clearing_regular_time'   => $request->input('clearing_regular_time', '8:00 - 12:00 / 1:00 - 5:00'),
                        'clearing_overtime_time'  => $request->boolean('clearing_overtime') ? trim($request->input('clearing_overtime_time', '')) : '',
                        'additional_date'         => $request->input('additional_date', ''),
                        'additional_notes'        => $request->input('additional_notes', ''),
                        'general_description'     => $request->input('description', ''),
                    ];

                    $validated['description'] = json_encode($manpowerData);
                    if ($request->filled('activity_title')) {
                        $validated['title'] = $request->input('activity_title');
                    }
                } elseif ($request->filled('janitorial_areas') || $request->filled('janitorial_type')) {
                    $janitorialData = [
                        'type'                 => 'janitorial',
                        'janitorial_areas'     => $request->input('janitorial_areas', ''),
                        'janitorial_type'      => $request->input('janitorial_type', ''),
                        'janitorial_frequency' => $request->input('janitorial_frequency', ''),
                        'janitorial_supplies'  => $request->input('janitorial_supplies', ''),
                        'general_description'  => $request->input('description', ''),
                    ];

                    $validated['description'] = json_encode($janitorialData);
                }
            }

            // 1) Allow up to 4 active requests per client with the exact same structured details (category + campus + location + concern)
            $sameDetailsCount = ServiceRequest::countClientSameDetailsRequests(
                (int) $client->client_id,
                (int) $validated['category_id'],
                (string) $validated['title'],
                (string) $validated['campus'],
                (string) $validated['location']
            );

            if ($sameDetailsCount >= 4) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Same-concern limit reached (4/4) for this location.');
            }

            // 2) If description is also identical to an active request, warn the client unless already confirmed
            if (!$request->boolean('confirm_same_description')) {
                $sameDescriptionMatch = ServiceRequest::findActiveSameDescriptionForClient(
                    (int) $client->client_id,
                    (int) $validated['category_id'],
                    (string) $validated['title'],
                    (string) $validated['campus'],
                    (string) $validated['location'],
                    $validated['description'] ?? null
                );

                if ($sameDescriptionMatch) {
                    return redirect()->back()
                        ->withInput()
                        ->with('same_description_request_id', $sameDescriptionMatch->request_id)
                        ->with('same_description_tracking_code', $sameDescriptionMatch->tracking_code)
                        ->with('warning', "Same request warning: Matches {$sameDescriptionMatch->tracking_code}. Confirm below to submit anyway.");
                }
            }

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $extension = strtolower($file->getClientOriginalExtension());

                if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp']) && extension_loaded('gd')) {
                    try {
                        $disk = config('filesystems.default', 'public');
                        $filename = uniqid('att_') . '.' . ($extension === 'png' ? 'png' : 'jpg');
                        $relativeAttachmentPath = "attachments/{$client->client_id}/{$filename}";

                        $image = match ($extension) {
                            'png' => @imagecreatefrompng($file->getRealPath()),
                            'webp' => @imagecreatefromwebp($file->getRealPath()),
                            default => @imagecreatefromjpeg($file->getRealPath()),
                        };

                        if ($image) {
                            $width = imagesx($image);
                            $height = imagesy($image);
                            $maxDim = 1200;

                            if ($width > $maxDim || $height > $maxDim) {
                                $ratio = min($maxDim / $width, $maxDim / $height);
                                $newWidth = (int)($width * $ratio);
                                $newHeight = (int)($height * $ratio);

                                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                                imagedestroy($image);
                                $image = $newImage;
                            }

                            ob_start();
                            if ($extension === 'png') {
                                imagepng($image, null, 6);
                            } else {
                                imagejpeg($image, null, 75);
                            }
                            $imageData = ob_get_clean();
                            imagedestroy($image);

                            \Illuminate\Support\Facades\Storage::disk($disk)->put($relativeAttachmentPath, $imageData);
                            $attachmentPath = $relativeAttachmentPath;
                        } else {
                            $attachmentPath = $file->store("attachments/{$client->client_id}", $disk);
                        }
                    } catch (\Exception $ex) {
                        $attachmentPath = $file->store("attachments/{$client->client_id}", config('filesystems.default', 'public'));
                    }
                } else {
                    $attachmentPath = $file->store("attachments/{$client->client_id}", config('filesystems.default', 'public'));
                }
            }

            $serviceRequest = ServiceRequest::create([
                ...$validated,
                'client_id'    => $client->client_id,
                'priority'     => 'low', // Default, will be overwritten by decision tree immediately
                'attachment'   => $attachmentPath,
                'submitted_at' => now(),
            ]);

            Cache::forget("client_{$client->client_id}_request_kpi_v2");

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Client submitted service request: {$serviceRequest->title}",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            // Auto-assign priority via Decision Tree
            $this->decisionTree->assignPriority($serviceRequest);

            // Log initial status
            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => null,
                'current_status'  => 'Submitted',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            // Dispatch automated client confirmation email
            $this->notifications->requestSubmitted($serviceRequest);

            return redirect()->route('client.requests.show', $serviceRequest->request_id)
                ->with('success', 'Your service request has been submitted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while submitting your request: ' . $e->getMessage());
        }
    }

    public function show(int $id)
    {
        $request = ServiceRequest::with([
            'category', 
            'histories.updatedBy', 
            'evaluation', 
            'project.billOfMaterials.material',
            'project.workers.user'
        ])->findOrFail($id);

        $this->authorize('view', $request);

        // Mark viewed conversation messages as read
        if (auth()->check()) {
            \App\Models\RequestMessage::where('request_id', $request->request_id)
                ->where('sender_id', '!=', auth()->id())
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return view('client.requests.show', compact('request'));

    }

    public function cancel(int $id)
    {
        try {
            $request = ServiceRequest::with('project.workers')->findOrFail($id);

            $this->authorize('update', $request);

            if ($request->project()->exists() || !in_array($request->current_status, ['Submitted'])) {
                return redirect()->back()->with('error', 'This request has already been approved and scheduled. Please contact the GSO Admin via Messages to request a cancellation.');
            }

            RequestHistory::create([
                'request_id'      => $request->request_id,
                'previous_status' => $request->current_status,
                'current_status'  => 'Cancelled',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            // Release workers and cancel project if one existed
            if ($request->project) {
                \App\Models\ProjectHistory::create([
                    'project_id'      => $request->project->project_id,
                    'previous_status' => $request->project->current_status,
                    'current_status'  => 'Cancelled',
                    'updated_at'      => now(),
                    'updated_by'      => auth()->id(),
                ]);

                foreach ($request->project->workers as $worker) {
                    $worker->recalculateAvailability();
                }
            }


            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Client cancelled service request #{$request->request_id}",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            return redirect()->route('client.requests.show', $id)
                ->with('success', 'Your request has been successfully cancelled.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error cancelling request: ' . $e->getMessage());
        }
    }

    public function approveSchedule(int $id)
    {
        try {
            $serviceRequest = ServiceRequest::with(['project.workers.staff.user', 'project.workers.user', 'client'])->findOrFail($id);
            $this->authorize('update', $serviceRequest);

            if (in_array($serviceRequest->current_status, ['Rejected', 'Cancelled']) || !$serviceRequest->scheduled_date) {
                return redirect()->route('client.requests.show', $id)
                    ->with('error', 'This request is no longer active (' . $serviceRequest->current_status . ') and its visit schedule cannot be confirmed.');
            }

            if ($serviceRequest->schedule_status !== 'pending_client_approval') {
                return redirect()->back()->with('info', 'There is no pending schedule proposal awaiting your approval.');
            }

            $serviceRequest->update([
                'schedule_status'         => 'approved',
                'schedule_decline_reason' => null,
            ]);

            $formattedDate = $serviceRequest->scheduled_date?->format('F d, Y') ?? 'Selected Date';
            $window = match($serviceRequest->scheduled_time_window) {
                'AM' => 'Morning',
                'PM' => 'Afternoon',
                'AM-PM' => 'Whole Day',
                default => $serviceRequest->scheduled_time_window ?? 'Morning'
            };

            $project = $serviceRequest->project;
            if ($project && $project->workers->isNotEmpty()) {
                // Workers were pre-assigned by admin! Launch project now!
                $project->update([
                    'date_approved' => now()->toDateString(),
                ]);

                foreach ($project->workers as $worker) {
                    $worker->update(['is_available' => false]);
                    $workerUserId = $worker->staff?->user_id ?? $worker->user?->user_id;
                    if ($workerUserId) {
                        $this->notifications->workerAssigned(
                            $workerUserId,
                            $serviceRequest->title,
                            $project->project_id
                        );
                    }
                }

                \App\Models\ProjectHistory::create([
                    'project_id'      => $project->project_id,
                    'previous_status' => $project->current_status,
                    'current_status'  => 'Pending',
                    'updated_at'      => now(),
                    'updated_by'      => auth()->id(),
                ]);

                RequestHistory::create([
                    'request_id'      => $serviceRequest->request_id,
                    'previous_status' => $serviceRequest->current_status,
                    'current_status'  => 'Approved',
                    'remarks'         => "GSO Team scheduled to visit the area on {$formattedDate} ({$window}).",
                    'updated_at'      => now(),
                    'updated_by'      => auth()->id(),
                ]);

                // Notify Client of approval
                $this->notifications->requestStatusChanged(
                    $serviceRequest->client->user_id,
                    $serviceRequest->title,
                    'Approved',
                    $serviceRequest->request_id,
                    'client'
                );
            } else {
                RequestHistory::create([
                    'request_id'      => $serviceRequest->request_id,
                    'previous_status' => $serviceRequest->current_status,
                    'current_status'  => 'Schedule Confirmed',
                    'remarks'         => "GSO Team scheduled to visit the area on {$formattedDate} ({$window}).",
                    'updated_at'      => now(),
                    'updated_by'      => auth()->id(),
                ]);
            }

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Client confirmed visit schedule for request #{$serviceRequest->request_id}",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            // Notify all admins
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $this->notifications->scheduleApproved(
                    $admin->user_id,
                    $serviceRequest->title,
                    $formattedDate,
                    $window,
                    $serviceRequest->request_id
                );
            }

            return redirect()->route('client.requests.show', $id)
                ->with('success', "Visit schedule on {$formattedDate} ({$window}) has been confirmed.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error approving schedule: ' . $e->getMessage());
        }
    }

    public function declineSchedule(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'decline_reason' => 'required|string|max:500',
            ], [
                'decline_reason.required' => 'Please provide a reason or your preferred alternative dates.',
            ]);

            $serviceRequest = ServiceRequest::findOrFail($id);
            $this->authorize('update', $serviceRequest);

            if (in_array($serviceRequest->current_status, ['Rejected', 'Cancelled']) || !$serviceRequest->scheduled_date) {
                return redirect()->route('client.requests.show', $id)
                    ->with('error', 'This request is no longer active (' . $serviceRequest->current_status . ') and its visit schedule cannot be declined.');
            }

            $reason = trim($validated['decline_reason']);

            $serviceRequest->update([
                'schedule_status'         => 'declined',
                'schedule_decline_reason' => $reason,
            ]);

            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $serviceRequest->current_status,
                'current_status'  => 'Schedule Refused',
                'remarks'         => "Client refused schedule and requested rescheduling: {$reason}",
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Client declined schedule for request #{$serviceRequest->request_id}: {$reason}",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            // Notify all admins
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $this->notifications->scheduleDeclined(
                    $admin->user_id,
                    $serviceRequest->title,
                    $reason,
                    $serviceRequest->request_id
                );
            }

            return redirect()->route('client.requests.show', $id)
                ->with('success', 'Reschedule request sent to GSO Admin. You will be notified when a new date is proposed.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error requesting reschedule: ' . $e->getMessage());
        }
    }

    public function approveBom(int $id)
    {
        try {
            $serviceRequest = ServiceRequest::with('project.billOfMaterials', 'project.workers.staff.user')->findOrFail($id);
            $this->authorize('update', $serviceRequest);

            $project = $serviceRequest->project;
            if (!$project) {
                return redirect()->back()->with('error', 'Project not found for this request.');
            }

            $totalCost = $project->billOfMaterials->sum('total_cost');

            $previousStatus = $serviceRequest->current_status;
            $newStatus = 'Awaiting Materials';
            $remarks = 'Client approved the List of Materials. Awaiting materials procurement/delivery before work commences.';

            $serviceRequest->update([
                'bom_status'     => 'approved',
                'current_status' => $newStatus,
            ]);

            $project->update([
                'current_status' => $newStatus,
            ]);

            \App\Models\ProjectHistory::create([
                'project_id'      => $project->project_id,
                'previous_status' => $project->current_status,
                'current_status'  => $newStatus,
                'remarks'         => $remarks,
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $previousStatus,
                'current_status'  => $newStatus,
                'remarks'         => $remarks,
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Client approved List of Materials for request #{$serviceRequest->request_id}",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            // Notify Admins
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $this->notifications->bomApprovedByClient(
                    $admin->user_id,
                    $serviceRequest->title,
                    $serviceRequest->request_id,
                    'admin'
                );
            }

            // Notify Workers
            foreach ($project->workers as $pw) {
                $workerUserId = $pw->staff?->user_id ?? $pw->user?->user_id;
                if ($workerUserId) {
                    $this->notifications->bomApprovedByClient(
                        $workerUserId,
                        $serviceRequest->title,
                        $project->project_id,
                        'worker'
                    );
                }
            }

            return redirect()->route('client.requests.show', $id)
                ->with('success', 'List of Materials approved successfully. Work may commence once materials are ready!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error approving List of Materials: ' . $e->getMessage());
        }
    }

    public function declineBom(Request $request, int $id)
    {
        try {
            $serviceRequest = ServiceRequest::with('project.billOfMaterials')->findOrFail($id);
            $this->authorize('update', $serviceRequest);

            $reason = trim((string)$request->input('remarks', ''));

            $serviceRequest->update([
                'bom_status' => 'declined',
            ]);

            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $serviceRequest->current_status,
                'current_status'  => 'On Hold',
                'remarks'         => 'Client declined List of Materials.' . ($reason ? " Reason: {$reason}" : ''),
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            if ($serviceRequest->project) {
                \App\Models\ProjectHistory::create([
                    'project_id'      => $serviceRequest->project->project_id,
                    'previous_status' => $serviceRequest->project->current_status,
                    'current_status'  => 'On Hold',
                    'remarks'         => 'Client declined List of Materials.' . ($reason ? " Reason: {$reason}" : ''),
                    'updated_at'      => now(),
                    'updated_by'      => auth()->id(),
                ]);
            }

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Client declined List of Materials for request #{$serviceRequest->request_id}",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $this->notifications->bomDeclinedByClient(
                    $admin->user_id,
                    $serviceRequest->title,
                    $serviceRequest->request_id,
                    $reason
                );
            }

            return redirect()->route('client.requests.show', $id)
                ->with('success', 'List of Materials declined. GSO Admin has been notified.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error declining List of Materials: ' . $e->getMessage());
        }
    }
}

