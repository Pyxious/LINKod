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

class RequestController extends Controller
{
    public function __construct(
        protected NotificationService $notifications,
        protected DecisionTreeService $decisionTree
    ) {}

    public function index(Request $request)
    {
        $query = ServiceRequest::with('client.user', 'category', 'latestHistory')
            ->latest('submitted_at');

        if ($request->filled('status')) {
            $query->whereHas('latestHistory', fn($q) =>
                $q->where('current_status', $request->status));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $requests = $query->paginate(15);

        // Fetch KPI Metrics for Admin Request Tracking
        $totalRequests = ServiceRequest::count();
        $submitted     = ServiceRequest::where(function($q) {
            $q->whereHas('latestHistory', fn($lh) => $lh->where('current_status', 'Submitted'))
              ->orWhereDoesntHave('histories');
        })->count();
        $onHold        = ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'On Hold'))->count();
        $inProgress    = ServiceRequest::whereHas('latestHistory', fn($q) => $q->whereIn('current_status', ['In Progress', 'Pending Verification']))->count();
        $completed     = ServiceRequest::whereHas('latestHistory', fn($q) => $q->where('current_status', 'Completed'))->count();

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
                'attachment'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
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

            // Handle attachment upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $extension = strtolower($file->getClientOriginalExtension());

                if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp']) && extension_loaded('gd')) {
                    try {
                        $destinationPath = storage_path("app/public/attachments/{$client->client_id}");
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0755, true);
                        }

                        $filename = uniqid('att_') . '.' . ($extension === 'png' ? 'png' : 'jpg');
                        $fullPath = $destinationPath . '/' . $filename;

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

                            if ($extension === 'png') {
                                imagepng($image, $fullPath, 6);
                            } else {
                                imagejpeg($image, $fullPath, 75);
                            }
                            imagedestroy($image);

                            $attachmentPath = "attachments/{$client->client_id}/{$filename}";
                        } else {
                            $attachmentPath = $file->store("attachments/{$client->client_id}", 'public');
                        }
                    } catch (\Exception $ex) {
                        $attachmentPath = $file->store("attachments/{$client->client_id}", 'public');
                    }
                } else {
                    $attachmentPath = $file->store("attachments/{$client->client_id}", 'public');
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
            'client.user', 'category', 'histories.updatedBy', 'evaluation', 'project.histories'
        )->findOrFail($id);

        $workers = \App\Models\Worker::whereHas('staff.user', fn($q) => $q->where('role', 'worker'))
            ->with('user', 'team')
            ->where('is_available', true)
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
                $teamKey = $worker->team_id ?? 999999;
                return sprintf('%d-%06d-%06d', $recKey, $teamKey, $worker->worker_id);
            })
            ->values();

        $categories = \App\Models\Category::all();

        return view('admin.requests.show', compact('serviceRequest', 'workers', 'categories'));
    }

    public function approve(Request $request, int $id)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:category,category_id',
                'priority'    => 'required|in:High,Medium,Low,high,medium,low',
            ]);

            $serviceRequest = ServiceRequest::findOrFail($id);
            $user           = auth()->user();
            $staff          = $user->staff;

            if (!$staff) {
                $staff = \App\Models\Staff::create([
                    'user_id'    => $user->user_id,
                    'role'       => $user->role,
                    'date_hired' => now()->toDateString(),
                ]);
            }

            $serviceRequest->update([
                'category_id' => $request->category_id,
                'priority'    => $request->priority,
            ]);

            $previous = $serviceRequest->current_status;

            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $previous,
                'current_status'  => 'Approved',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            // Create a Project record
            $project = Project::create([
                'client_id'     => $serviceRequest->client_id,
                'request_id'    => $serviceRequest->request_id,
                'approved_by'   => $staff?->staff_id,
                'date_approved' => now()->toDateString(),
            ]);

            $workerIds = $request->input('worker_ids', []);

            // If no workers were explicitly checked, auto-assign available workers from the team matching the category
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
                        $workerIds = \App\Models\Worker::where('team_id', $matchingTeam->team_id)
                            ->where('is_available', true)
                            ->pluck('worker_id')
                            ->toArray();
                    }
                }
            }

            if (!empty($workerIds)) {
                foreach ($workerIds as $workerId) {
                    \App\Models\ProjectWorker::firstOrCreate([
                        'project_id' => $project->project_id,
                        'worker_id'  => $workerId,
                    ], [
                        'date_assigned' => now()->toDateString(),
                    ]);

                    $worker = \App\Models\Worker::find($workerId);
                    if ($worker) {
                        $worker->update(['is_available' => false]);
                        
                        $this->notifications->workerAssigned(
                            $worker->staff->user_id,
                            $serviceRequest->title,
                            $project->project_id
                        );
                    }
                }
            }

            ProjectHistory::create([
                'project_id'      => $project->project_id,
                'previous_status' => null,
                'current_status'  => 'Pending',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Admin approved request #{$serviceRequest->request_id} and created project #{$project->project_id}",
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
                ->with('success', 'Request approved, project created, and workers assigned.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error approving request: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, int $id)
    {
        try {
            $serviceRequest = ServiceRequest::findOrFail($id);
            $previous       = $serviceRequest->current_status;

            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $previous,
                'current_status'  => 'Rejected',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

            \App\Models\UserLog::create([
                'user_id'    => auth()->id(),
                'action'     => "Admin rejected request #{$serviceRequest->request_id}",
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

    public function verifyCompletion(int $id)
    {
        try {
            $serviceRequest = ServiceRequest::with('project')->findOrFail($id);

            // Update Project History
            if ($serviceRequest->project) {
                ProjectHistory::create([
                    'project_id'      => $serviceRequest->project->project_id,
                    'previous_status' => $serviceRequest->project->current_status,
                    'current_status'  => 'Completed',
                    'updated_at'      => now(),
                    'updated_by'      => auth()->id(),
                ]);
            }

            // Update Request History
            RequestHistory::create([
                'request_id'      => $serviceRequest->request_id,
                'previous_status' => $serviceRequest->current_status,
                'current_status'  => 'Completed',
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
}
