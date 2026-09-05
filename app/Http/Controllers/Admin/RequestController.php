<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Client;
use App\Models\User;
use App\Models\UserLog;
use App\Models\ServiceRequest;
use App\Models\RequestHistory;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Services\NotificationService;
use App\Services\DecisionTreeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class RequestController extends Controller
{
    public function __construct(
        protected NotificationService $notifications,
        protected DecisionTreeService $decisionTree
    ) {}

    public function index(Request $request)
    {
        $query = ServiceRequest::with('client.user', 'category', 'latestHistory')
            ->orderByRaw("CASE WHEN LOWER(priority) = 'high' THEN 1 ELSE 2 END ASC")
            ->orderBy('submitted_at', 'asc')
            ->orderBy('request_id', 'asc');

        if ($request->filled('status')) {
            $query->whereHas('latestHistory', fn($q) =>
                $q->where('current_status', $request->status));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $requests = $query->paginate(15);

        // Fetch KPI Metrics — cached 300s (5 min) since counts don't need to be live
        $kpi = Cache::remember('admin_requests_kpi', 300, function () {
            return [
                'totalRequests' => ServiceRequest::count(),
                'submitted'     => ServiceRequest::where(function($q) {
                    $q->whereHas('latestHistory', fn($lh) => $lh->where('current_status', 'Submitted'))
                      ->orWhereDoesntHave('histories');
                })->count(),
                'onHold'        => ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'On Hold'))->count(),
                'inProgress'    => ServiceRequest::whereHas('latestHistory', fn($q) => $q->whereIn('current_status', ['In Progress', 'Pending Verification']))->count(),
                'completed'     => ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'Completed'))->count(),
            ];
        });

        extract($kpi);

        return view('admin.requests.index', compact(
            'requests', 'totalRequests', 'submitted', 'onHold', 'inProgress', 'completed'
        ));
    }

    public function create(Request $request)
    {
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

        return view('admin.requests.create', compact('categories', 'preselectedCatId'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                // Walk-in Client details
                'client_email'   => ['required', 'email', 'max:255', 'ends_with:@bicol-u.edu.ph'],
                'client_phone'   => ['required', 'string', 'regex:/^09\d{9}$/'],
                'first_name'     => 'required|string|max:50',
                'last_name'      => 'required|string|max:50',
                'office'         => 'nullable|string|max:100',

                // Service Request details
                'category_id'    => 'required|exists:category,category_id',
                'title'          => 'required|string|max:150',
                'description'    => 'nullable|string',
                'campus'         => 'required|string|max:100',
                'location'       => 'required|string|max:255',
                'complexity'     => 'nullable|in:low,medium,high',
                'urgency'        => 'nullable|in:low,medium,high',
                'attachment'     => ['nullable', 'file', new \App\Rules\SecureFileUpload(['pdf', 'jpg', 'jpeg', 'png'], 5120)],
            ], [
                'client_email.ends_with' => 'The client email address must be an official Bicol University email ending with @bicol-u.edu.ph.',
                'client_email.email'     => 'Please enter a valid email address.',
                'client_email.required'  => 'Client email address is required.',
                'client_phone.regex'     => 'The contact number must be an 11-digit Philippine mobile number starting with 09 (e.g. 09123456789).',
                'client_phone.required'  => 'Client contact number is required.',
            ]);

            $email = strtolower(trim($validated['client_email']));
            $emailHash = hash('sha256', $email);

            // Find or create User for walk-in client
            $user = User::where('email_hash', $emailHash)->first();

            if (!$user) {
                $baseUsername = strtolower(explode('@', $email)[0]);
                $baseUsername = preg_replace('/[^a-z0-9_]/', '_', $baseUsername);
                $username = $baseUsername;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $counter++;
                }

                $user = User::create([
                    'username'       => $username,
                    'first_name'     => $validated['first_name'],
                    'last_name'      => $validated['last_name'],
                    'email_account'  => $email,
                    'email_hash'     => $emailHash,
                    'contact_number' => $validated['client_phone'],
                    'role'           => 'client',
                    'password'       => Str::random(32),
                ]);
            } else {
                $user->update([
                    'contact_number' => $validated['client_phone'],
                ]);
            }

            // Get or create Client model
            $client = $user->client;
            if (!$client) {
                $client = Client::create([
                    'user_id' => $user->user_id,
                    'office'  => $validated['office'] ?? null,
                    'campus'  => $validated['campus'],
                ]);
            } elseif ($validated['office'] ?? null) {
                $client->update([
                    'office' => $validated['office'],
                ]);
            }

            // Package Manpower structured details into description if provided
            if ($request->filled('activity_title') || $request->filled('prep_details') || $request->filled('prep_date') || $request->filled('assistance_details') || $request->filled('clearing_details')) {
                $manpowerData = [
                    'activity_title'          => $request->input('activity_title', $validated['title']),
                    'event_date'              => $request->input('event_date', ''),
                    'venue'                   => $request->input('venue', $validated['location']),
                    'prep_date'               => $request->input('prep_date', ''),
                    'prep_details'            => $request->input('prep_details', ''),
                    'prep_regular'            => $request->boolean('prep_regular', true),
                    'prep_overtime'           => $request->boolean('prep_overtime', false),
                    'prep_regular_time'       => $request->input('prep_regular_time', '8:00 - 12:00 / 1:00 - 5:00'),
                    'prep_overtime_time'      => $request->input('prep_overtime_time', ''),
                    'assistance_date'         => $request->input('assistance_date', ''),
                    'assistance_details'      => $request->input('assistance_details', ''),
                    'assistance_regular'      => $request->boolean('assistance_regular', true),
                    'assistance_overtime'     => $request->boolean('assistance_overtime', false),
                    'assistance_regular_time' => $request->input('assistance_regular_time', '8:00 - 12:00 / 1:00 - 5:00'),
                    'assistance_overtime_time'=> $request->input('assistance_overtime_time', ''),
                    'clearing_date'           => $request->input('clearing_date', ''),
                    'clearing_details'        => $request->input('clearing_details', ''),
                    'clearing_regular'        => $request->boolean('clearing_regular', true),
                    'clearing_overtime'       => $request->boolean('clearing_overtime', false),
                    'clearing_regular_time'   => $request->input('clearing_regular_time', '8:00 - 12:00 / 1:00 - 5:00'),
                    'clearing_overtime_time'  => $request->input('clearing_overtime_time', ''),
                    'additional_date'         => $request->input('additional_date', ''),
                    'additional_notes'        => $request->input('additional_notes', ''),
                    'general_description'     => $request->input('description', ''),
                ];

                $validated['description'] = json_encode($manpowerData);
                if ($request->filled('activity_title')) {
                    $validated['title'] = $request->input('activity_title');
                }
            }

            // Handle attachment upload
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
                'client_id'    => $client->client_id,
                'category_id'  => $validated['category_id'],
                'title'        => $validated['title'],
                'description'  => $validated['description'] ?? null,
                'campus'       => $validated['campus'],
                'location'     => $validated['location'],
                'complexity'   => $validated['complexity'] ?? 'low',
                'urgency'      => $validated['urgency'] ?? 'low',
                'priority'     => 'low',
                'attachment'   => $attachmentPath,
                'submitted_at' => now(),
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

            UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Admin created walk-in request #{$serviceRequest->request_id} for {$email}",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            return redirect()->route('admin.requests.show', $serviceRequest->request_id)
                ->with('success', "Walk-in service request created successfully for {$email}.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating walk-in request: ' . $e->getMessage());
        }
    }

    public function show(int $id)
    {
        $serviceRequest = ServiceRequest::with(
            'client.user', 'category', 'histories.updatedBy', 'evaluation', 'project.histories', 'project.billOfMaterials.material'
        )->findOrFail($id);

        // Mark viewed conversation messages as read
        if (auth()->check()) {
            \App\Models\RequestMessage::where('request_id', $serviceRequest->request_id)
                ->where('sender_id', '!=', auth()->id())
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $workers = \App\Models\Worker::whereHas('staff.user', fn($q) => $q->where('role', 'worker'))
            ->with([
                'user', 
                'team',
                'projects' => function($q) {
                    $q->with('request.category', 'latestHistory')
                      ->whereHas('latestHistory', function($lh) {
                          $lh->whereNotIn('current_status', ['Completed', 'Cancelled']);
                      });
                }
            ])
            ->get()
            ->sortBy(function($worker) use ($serviceRequest) {
                $teamName = strtolower($worker->team->team_name ?? '');
                $categoryName = strtolower($serviceRequest->category->category_name ?? '');
                $isRecommended = false;
                if ($teamName && $categoryName) {
                    preg_match_all('/\w+/', $categoryName, $catWords);
                    foreach ($catWords[0] as $word) {
                        if (strlen($word) > 3 && str_contains($teamName, $word)) {
                            $isRecommended = true;
                            break;
                        }
                    }
                }
                $recKey = $isRecommended ? 0 : 1;
                $activeCount = $worker->projects->count();
                $teamKey = $worker->team_id ?? 999999;
                return sprintf('%d-%03d-%06d-%06d', $recKey, $activeCount, $teamKey, $worker->worker_id);
            })
            ->values();

        $categories = \App\Models\Category::all();
        $allMaterials = \App\Models\Materials::orderBy('material_name')->get();

        return view('admin.requests.show', compact('serviceRequest', 'workers', 'categories', 'allMaterials'));
    }

    public function approve(Request $request, int $id)
    {
        try {
            $request->validate([
                'category_id'           => 'required|exists:category,category_id',
                'priority'              => 'required|in:High,Medium,Low,high,medium,low,urgent,routine,Urgent,Routine',
                'scheduled_date'        => 'nullable|date',
                'scheduled_time_window' => 'nullable|in:AM,PM,AM-PM',
                'worker_ids'            => 'nullable|array',
                'worker_ids.*'          => 'exists:worker,worker_id',
            ]);

            $serviceRequest = ServiceRequest::findOrFail($id);

            // Guard against duplicate approvals / already active project
            if ($serviceRequest->project && in_array($serviceRequest->current_status, ['Approved', 'In Progress', 'Completed'])) {
                return redirect()->route('admin.requests.show', $id)
                    ->with('info', 'This request has already been approved.');
            }

            $user           = auth()->user();
            $staff          = $user->staff;

            if (!$staff) {
                $staff = \App\Models\Staff::create([
                    'user_id'    => $user->user_id,
                    'role'       => $user->role,
                    'date_hired' => now()->toDateString(),
                ]);
            }

            $updateData = [
                'category_id' => $request->category_id,
                'priority'    => $request->priority,
            ];

            if ($request->filled('scheduled_date')) {
                $updateData['scheduled_date'] = $request->scheduled_date;
                $updateData['scheduled_time_window'] = $request->input('scheduled_time_window', $serviceRequest->scheduled_time_window ?? 'AM-PM');
                $updateData['schedule_status'] = 'approved';
                $updateData['schedule_decline_reason'] = null;
            } elseif ($serviceRequest->scheduled_date && $serviceRequest->schedule_status !== 'approved') {
                $updateData['schedule_status'] = 'approved';
                $updateData['schedule_decline_reason'] = null;
            }

            $serviceRequest->update($updateData);

            $previous = $serviceRequest->current_status;

            // Find or create Project
            $project = Project::firstOrCreate([
                'request_id' => $serviceRequest->request_id,
            ], [
                'client_id'     => $serviceRequest->client_id,
                'approved_by'   => $staff?->staff_id,
                'date_approved' => now()->toDateString(),
            ]);

            $project->update([
                'approved_by'   => $staff?->staff_id,
                'date_approved' => now()->toDateString(),
            ]);

            $workerIds = $request->input('worker_ids', []);

            // If empty, check if workers were already assigned to project
            if (empty($workerIds) && $project->workers()->exists()) {
                $workerIds = $project->workers->pluck('worker_id')->toArray();
            }

            // If still empty, auto-assign workers from the team matching the category (least loaded first)
            if (empty($workerIds) && $request->category_id) {
                $category = \App\Models\Category::find($request->category_id);
                if ($category) {
                    $catName = strtolower($category->category_name);
                    preg_match_all('/\w+/', $catName, $catWords);
                    $keywords = array_filter($catWords[0], fn($w) => strlen($w) > 3);

                    $matchingTeam = \App\Models\Team::all()->first(function ($team) use ($keywords) {
                        $tName = strtolower($team->team_name);
                        foreach ($keywords as $word) {
                            if (str_contains($tName, $word)) return true;
                        }
                        return false;
                    });

                    if ($matchingTeam) {
                        $teamWorkers = \App\Models\Worker::where('team_id', $matchingTeam->team_id)
                            ->with(['projects' => function($q) {
                                $q->whereHas('latestHistory', fn($lh) => $lh->whereNotIn('current_status', ['Completed', 'Cancelled']));
                            }])
                            ->get()
                            ->sortBy(fn($w) => $w->projects->count());

                        $availableOnTeam = $teamWorkers->filter(fn($w) => $w->projects->isEmpty());
                        if ($availableOnTeam->isNotEmpty()) {
                            $workerIds = $availableOnTeam->pluck('worker_id')->toArray();
                        } else {
                            $workerIds = $teamWorkers->take(2)->pluck('worker_id')->toArray();
                        }
                    }
                }
            }

            if (!empty($workerIds)) {
                $syncData = [];
                foreach ($workerIds as $workerId) {
                    $syncData[$workerId] = ['date_assigned' => now()->toDateString()];
                }
                $project->workers()->sync($syncData);

                foreach ($workerIds as $workerId) {
                    $worker = \App\Models\Worker::find($workerId);
                    if ($worker) {
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
                }
            }

            ProjectHistory::create([
                'project_id'      => $project->project_id,
                'previous_status' => $project->current_status,
                'current_status'  => 'Pending',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            $dateFormatted = $serviceRequest->scheduled_date?->format('M d, Y') ?? 'Confirmed Schedule';
            $windowText = match($serviceRequest->scheduled_time_window) {
                'AM' => 'Morning (AM)',
                'PM' => 'Afternoon (PM)',
                'AM-PM' => 'Whole Day (AM - PM)',
                default => $serviceRequest->scheduled_time_window ?? 'Visit'
            };

            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $previous,
                'current_status'  => 'Approved',
                'remarks'         => "Request approved for {$dateFormatted} ({$windowText}). Maintenance workers assigned.",
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Admin approved request #{$serviceRequest->request_id} and activated project #{$project->project_id}",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            // Notify client
            $this->notifications->requestStatusChanged(
                $serviceRequest->client->user_id,
                $serviceRequest->title,
                'Approved',
                $serviceRequest->request_id,
                'client'
            );

            return redirect()->route('admin.requests.show', $id)
                ->with('success', 'Request approved, project launched, and maintenance workers assigned.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error approving request: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, int $id)
    {
        try {
            $serviceRequest = ServiceRequest::findOrFail($id);

            if ($serviceRequest->current_status === 'Rejected') {
                return redirect()->route('admin.requests.show', $id)
                    ->with('info', 'This request has already been rejected.');
            }

            $previous = $serviceRequest->current_status;
            $remarks = $request->input('feedback') ?: $request->input('remarks');

            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $previous,
                'current_status'  => 'Rejected',
                'remarks'         => $remarks,
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Admin rejected request #{$serviceRequest->request_id}" . ($remarks ? ": {$remarks}" : ""),
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            $this->notifications->requestStatusChanged(
                $serviceRequest->client->user_id,
                $serviceRequest->title,
                'Rejected',
                $serviceRequest->request_id,
                'client'
            );

            return redirect()->route('admin.requests.show', $id)
                ->with('success', 'Request has been rejected.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error rejecting request: ' . $e->getMessage());
        }
    }

    public function verifyCompletion(Request $request, int $id)
    {
        try {
            $serviceRequest = ServiceRequest::with(['project', 'category'])->findOrFail($id);

            $catName = strtolower($serviceRequest->category->category_name ?? '');
            $isManpower = str_contains($catName, 'manpower') || str_contains($catName, 'event');

            if ($isManpower && empty(trim((string)$request->input('work_details')))) {
                return redirect()->back()->with('error', 'Accomplished manpower work details are required before verifying and completing this request.');
            }

            // Update Project Details
            if ($serviceRequest->project) {
                $isInspection = $serviceRequest->project->nature_of_work === 'Inspection & Assessment Only' 
                    || $request->input('nature_of_work') === 'Inspection & Assessment Only';

                if ($isInspection) {
                    $serviceRequest->project->nature_of_work = 'Inspection & Assessment Only';
                    if ($request->filled('work_details')) {
                        $serviceRequest->project->recommendation = $request->input('work_details');
                    } elseif ($request->filled('recommendation')) {
                        $serviceRequest->project->recommendation = $request->input('recommendation');
                    }
                } else {
                    $details = $request->input('work_details') ?: $request->input('recommendation') ?: $request->input('nature_of_work');
                    if ($details) {
                        $serviceRequest->project->nature_of_work = $details;
                        $serviceRequest->project->recommendation = null;
                    } elseif (!$serviceRequest->project->nature_of_work) {
                        $serviceRequest->project->nature_of_work = 'Repair & Maintenance Done';
                    }
                }

                $serviceRequest->project->save();
            }

            if ($serviceRequest->current_status === 'Completed') {
                return redirect()->route('admin.requests.show', $id)
                    ->with('success', 'Project work details and nature of work updated successfully.');
            }

            // Update Project History if not completed yet
            if ($serviceRequest->project) {
                ProjectHistory::create([
                    'project_id'      => $serviceRequest->project->project_id,
                    'previous_status' => $serviceRequest->project->current_status,
                    'current_status'  => 'Completed',
                    'updated_at'      => now(),
                    'updated_by'      => auth()->id(),
                ]);

                // Recalculate availability for all assigned workers
                foreach ($serviceRequest->project->workers as $assignedWorker) {
                    $assignedWorker->recalculateAvailability();
                }
            }

            $remarks = ($serviceRequest->project?->nature_of_work ?? 'Completed') 
                . ($serviceRequest->project?->recommendation ? ' — ' . $serviceRequest->project->recommendation : '');

            // Update Request History
            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $serviceRequest->current_status,
                'current_status'  => 'Completed',
                'remarks'         => $remarks,
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Admin verified completion for request #{$serviceRequest->request_id}",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            // Notify Client
            $this->notifications->requestStatusChanged(
                $serviceRequest->client->user_id,
                $serviceRequest->title,
                'Completed',
                $serviceRequest->request_id,
                'client'
            );

            return redirect()->route('admin.requests.show', $id)
                ->with('success', 'Project completion verified and request closed successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error verifying completion: ' . $e->getMessage());
        }
    }

    public function export(int $id)
    {
        try {
            $serviceRequest = ServiceRequest::with('client.user', 'category', 'project.workers.user')->findOrFail($id);
            
            return view('admin.requests.print', compact('serviceRequest'));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to print request: ' . $e->getMessage());
        }
    }

    public function printSatisfaction(int $id)
    {
        try {
            $serviceRequest = ServiceRequest::with([
                'client.user', 
                'category', 
                'evaluation', 
                'project.workers.user',
                'project.approvedBy.user',
                'latestHistory'
            ])->findOrFail($id);

            if (!$serviceRequest->evaluation) {
                return redirect()->back()
                    ->with('error', 'The client has not submitted a satisfaction evaluation for this request yet.');
            }

            return view('admin.requests.satisfaction', compact('serviceRequest'));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to print satisfaction page: ' . $e->getMessage());
        }
    }

    public function proposeSchedule(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'scheduled_date'        => 'required|date',
                'scheduled_time_window' => 'required|in:AM,PM,AM-PM',
                'category_id'           => 'nullable|exists:category,category_id',
                'priority'              => 'nullable|in:High,Medium,Low,high,medium,low,urgent,routine,Urgent,Routine',
                'worker_ids'            => 'nullable|array',
                'worker_ids.*'          => 'exists:worker,worker_id',
            ]);

            $serviceRequest = ServiceRequest::with('client.user')->findOrFail($id);

            $updateData = [
                'scheduled_date'          => $validated['scheduled_date'],
                'scheduled_time_window'   => $validated['scheduled_time_window'],
                'schedule_status'         => 'pending_client_approval',
                'schedule_decline_reason' => null,
            ];

            if (!empty($validated['category_id'])) {
                $updateData['category_id'] = $validated['category_id'];
            }
            if (!empty($validated['priority'])) {
                $updateData['priority'] = $validated['priority'];
            }

            $serviceRequest->update($updateData);

            // Pre-assign workers to Project in pending confirmation state
            $user  = auth()->user();
            $staff = $user->staff;
            if (!$staff) {
                $staff = \App\Models\Staff::create([
                    'user_id'    => $user->user_id,
                    'role'       => $user->role,
                    'date_hired' => now()->toDateString(),
                ]);
            }

            $project = Project::firstOrCreate([
                'request_id' => $serviceRequest->request_id,
            ], [
                'client_id'     => $serviceRequest->client_id,
                'approved_by'   => $staff?->staff_id,
                'date_approved' => null,
            ]);

            $workerIds = $request->input('worker_ids', []);
            if (!empty($workerIds)) {
                $syncData = [];
                foreach ($workerIds as $wId) {
                    $syncData[$wId] = ['date_assigned' => now()->toDateString()];
                }
                $project->workers()->sync($syncData);
            }

            ProjectHistory::create([
                'project_id'      => $project->project_id,
                'previous_status' => $project->current_status,
                'current_status'  => 'Pending Schedule Confirmation',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            $dateFormatted = \Carbon\Carbon::parse($validated['scheduled_date'])->format('F d, Y');
            $windowText = match($validated['scheduled_time_window']) {
                'AM' => 'Morning (AM)',
                'PM' => 'Afternoon (PM)',
                'AM-PM' => 'Whole Day (AM - PM)',
                default => $validated['scheduled_time_window']
            };

            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $serviceRequest->current_status,
                'current_status'  => 'Schedule Set',
                'remarks'         => "Admin proposed visit schedule for {$dateFormatted} ({$windowText}). Maintenance personnel selected. Awaiting client confirmation.",
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Admin set visit schedule for request #{$serviceRequest->request_id}: {$dateFormatted} ({$windowText})",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            // Notify client
            if ($serviceRequest->client?->user_id) {
                $this->notifications->scheduleProposed(
                    $serviceRequest->client->user_id,
                    $serviceRequest->title,
                    $dateFormatted,
                    $validated['scheduled_time_window'],
                    $serviceRequest->request_id
                );
            }

            return redirect()->route('admin.requests.show', $id)
                ->with('success', "Visit schedule and maintenance personnel set for {$dateFormatted} ({$windowText}). Client notified for confirmation.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error setting schedule: ' . $e->getMessage());
        }
    }

    public function startTaskOverride(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'proof'   => ['required', 'file', new \App\Rules\SecureFileUpload(['jpg', 'jpeg', 'png', 'webp'], 10240)],
                'remarks' => 'nullable|string|max:500',
            ], [
                'proof.required' => 'A Before-Work photo is required to start this task.',
            ]);

            $serviceRequest = ServiceRequest::with('project.workers', 'client.user')->findOrFail($id);

            if (!$serviceRequest->project) {
                return redirect()->back()->with('error', 'No active project found for this request. Please approve the request first.');
            }

            $project = $serviceRequest->project;
            $disk = config('filesystems.default', 'public');
            $proofPath = $request->file('proof')->store('proofs', $disk);

            $previousStatus = $project->current_status;
            $newStatus = 'In Progress';
            $remarks = 'Admin Operational Override: Work commenced with documented Before-Work photo.' . ($request->filled('remarks') ? ' Note: ' . $request->input('remarks') : '');

            ProjectHistory::create([
                'project_id'       => $project->project_id,
                'previous_status'  => $previousStatus,
                'current_status'   => $newStatus,
                'proof_attachment' => $proofPath,
                'remarks'          => $remarks,
                'updated_at'       => now(),
                'updated_by'       => auth()->id(),
            ]);

            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $serviceRequest->current_status,
                'current_status'  => $newStatus,
                'remarks'         => $remarks,
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Admin started task for request #{$serviceRequest->request_id} (Before-Work Photo uploaded)",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            // Notify Client
            if ($serviceRequest->client?->user_id) {
                $this->notifications->requestStatusChanged(
                    $serviceRequest->client->user_id,
                    $serviceRequest->title,
                    'In Progress',
                    $serviceRequest->request_id,
                    'client'
                );
            }

            return redirect()->route('admin.requests.show', $id)
                ->with('success', 'Task started successfully with Before-Work photo. Status is now In Progress.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error starting task: ' . $e->getMessage());
        }
    }

    public function completeTaskOverride(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'proof'           => ['required', 'file', new \App\Rules\SecureFileUpload(['jpg', 'jpeg', 'png', 'webp'], 10240)],
                'completion_type' => 'nullable|in:Full Repair,Inspection Only',
                'nature_of_work'  => 'nullable|string|max:200',
                'recommendation'  => 'nullable|string|max:1000',
            ], [
                'proof.required' => 'An After-Work / accomplishment photo is required to complete this task.',
            ]);

            $serviceRequest = ServiceRequest::with('project.workers', 'client.user')->findOrFail($id);

            if (!$serviceRequest->project) {
                return redirect()->back()->with('error', 'No active project found for this request.');
            }

            $project = $serviceRequest->project;
            $disk = config('filesystems.default', 'public');
            $proofPath = $request->file('proof')->store('proofs', $disk);

            $completionType = $validated['completion_type'] ?? $request->input('completion_type', 'Full Repair');
            $natureOfWork = trim($validated['nature_of_work'] ?? $request->input('nature_of_work', ''));

            if ($completionType === 'Inspection Only' || $natureOfWork === 'Inspection & Assessment Only') {
                $finalNature = 'Inspection & Assessment Only';
            } else {
                $finalNature = !empty($natureOfWork) ? $natureOfWork : 'Repair & Maintenance Done';
            }

            $project->nature_of_work = $finalNature;
            if ($request->filled('recommendation')) {
                $project->recommendation = $request->input('recommendation');
            } else {
                $project->recommendation = null;
            }
            $project->save();

            $previousStatus = $project->current_status;
            $newStatus = 'Completed';
            $remarks = $finalNature . ($request->filled('recommendation') ? ' — ' . $request->input('recommendation') : '');

            ProjectHistory::create([
                'project_id'       => $project->project_id,
                'previous_status'  => $previousStatus,
                'current_status'   => $newStatus,
                'proof_attachment' => $proofPath,
                'remarks'          => 'Admin Operational Override: ' . $remarks,
                'updated_at'       => now(),
                'updated_by'       => auth()->id(),
            ]);

            // Release workers
            foreach ($project->workers as $worker) {
                $worker->recalculateAvailability();
            }

            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $serviceRequest->current_status,
                'current_status'  => $newStatus,
                'remarks'         => $remarks,
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Admin completed task for request #{$serviceRequest->request_id} (After-Work Photo uploaded, Nature: {$finalNature})",
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            // Notify Client
            if ($serviceRequest->client?->user_id) {
                $this->notifications->requestStatusChanged(
                    $serviceRequest->client->user_id,
                    $serviceRequest->title,
                    'Completed',
                    $serviceRequest->request_id,
                    'client'
                );
            }

            return redirect()->route('admin.requests.show', $id)
                ->with('success', 'Task successfully completed with After-Work photo and closed.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error completing task: ' . $e->getMessage());
        }
    }

    public function storeBom(Request $request, int $id)
    {
        try {
            $serviceRequest = ServiceRequest::findOrFail($id);
            if (in_array($serviceRequest->current_status, ['In Progress', 'Pending Verification', 'Completed', 'Cancelled', 'Rejected'])) {
                return redirect()->back()->with('error', 'Bill of Materials cannot be modified once work is In Progress or closed.');
            }
            $user  = auth()->user();
            $staff = $user?->staff;
            if (!$staff && $user) {
                $staff = \App\Models\Staff::firstOrCreate([
                    'user_id' => $user->user_id,
                ], [
                    'role'       => $user->role,
                    'date_hired' => now()->toDateString(),
                ]);
            }

            $project = Project::firstOrCreate([
                'request_id' => $serviceRequest->request_id,
            ], [
                'client_id'   => $serviceRequest->client_id,
                'approved_by' => $staff?->staff_id,
            ]);

            return app(\App\Http\Controllers\Admin\BomController::class)->store($request, $project->project_id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error adding material to BOM: ' . $e->getMessage());
        }
    }
}
