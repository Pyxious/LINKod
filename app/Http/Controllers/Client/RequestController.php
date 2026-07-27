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
            $baseQuery = $client->requests();

            $totalRequests   = (clone $baseQuery)->count();
            $pendingCount    = (clone $baseQuery)->where(function($q) {
                $q->whereHas('latestHistory', fn($lh) => $lh->whereIn('current_status', ['Submitted', 'Pending', 'On Hold']))
                  ->orWhereDoesntHave('histories');
            })->count();
            $inProgressCount = (clone $baseQuery)->whereHas('latestHistory', fn($lh) => $lh->whereIn('current_status', ['In Progress', 'Pending Verification']))->count();
            $completedCount  = (clone $baseQuery)->whereHas('latestHistory', fn($lh) => $lh->where('current_status', 'Completed'))->count();
            $cancelledCount  = (clone $baseQuery)->whereHas('latestHistory', fn($lh) => $lh->where('current_status', 'Cancelled'))->count();

            $query = $client->requests()->with('category', 'latestHistory');

            // Status Filter
            if ($request->filled('status') && $request->status !== 'all') {
                $status = strtolower($request->status);
                if ($status === 'pending') {
                    $query->where(function($q) {
                        $q->whereHas('latestHistory', fn($lh) => $lh->whereIn('current_status', ['Submitted', 'Pending', 'On Hold']))
                          ->orWhereDoesntHave('histories');
                    });
                } elseif ($status === 'in_progress') {
                    $query->whereHas('latestHistory', fn($lh) => $lh->whereIn('current_status', ['In Progress', 'Pending Verification']));
                } else {
                    $query->whereHas('latestHistory', fn($lh) => $lh->where('current_status', ucfirst($status)));
                }
            }

            // Search Query
            if ($request->filled('search')) {
                $rawSearch = trim($request->search);
                $numericId = (int) preg_replace('/[^0-9]/', '', $rawSearch);

                $query->where(function($q) use ($rawSearch, $numericId) {
                    $q->where('title', 'LIKE', "%{$rawSearch}%")
                      ->orWhere('location', 'LIKE', "%{$rawSearch}%")
                      ->orWhere('campus', 'LIKE', "%{$rawSearch}%");

                    if ($numericId > 0) {
                        $q->orWhere('request_id', $numericId);
                    }
                });
            }

            $requests = $query->latest('submitted_at')->paginate(10)->appends($request->query());
        } else {
            $totalRequests = 0;
            $pendingCount = 0;
            $inProgressCount = 0;
            $completedCount = 0;
            $cancelledCount = 0;
            $requests = collect();
        }

        return view('client.requests.index', compact(
            'requests', 'totalRequests', 'pendingCount', 'inProgressCount', 'completedCount', 'cancelledCount'
        ));
    }

    public function track(Request $request)
    {
        
        
        return view('client.requests.track');
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

        return view('client.requests.create', compact('categories', 'preselectedCatId'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'category_id'    => 'required|exists:category,category_id',
                'title'          => 'required|string|max:150',
                'description'    => 'nullable|string',
                'campus'         => 'required|string|max:100',
                'location'       => 'required|string|max:255',
                'complexity'     => 'required|in:low,medium,high',
                'urgency'        => 'required|in:low,medium,high',
                'attachment'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'contact_number' => 'required|string|regex:/^09\d{9}$/',
            ]);

            $user = auth()->user();
            $client = $user->client;

            // Update user's contact number if it has changed
            if ($user->contact_number !== $validated['contact_number']) {
                $user->update(['contact_number' => $validated['contact_number']]);
            }

            
            unset($validated['contact_number']);

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
                                imagejpeg($image, $fullPath, 75); // 75% quality for low MB footprint
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
                ...$validated,
                'client_id'    => $client->client_id,
                'priority'     => 'low', // Default, will be overwritten by decision tree immediately
                'attachment'   => $attachmentPath,
                'submitted_at' => now(),
            ]);

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

        return view('client.requests.show', compact('request'));
    }

    public function cancel(int $id)
    {
        try {
            $request = ServiceRequest::findOrFail($id);

            $this->authorize('update', $request);

            if (!in_array($request->current_status, ['Submitted', 'Pending'])) {
                return redirect()->back()->with('error', 'You can only cancel requests that are Submitted or Pending.');
            }

            RequestHistory::create([
                'request_id'      => $request->request_id,
                'previous_status' => $request->current_status,
                'current_status'  => 'Cancelled',
                'updated_at'      => now(),
                'updated_by'      => auth()->id(),
            ]);

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
}

