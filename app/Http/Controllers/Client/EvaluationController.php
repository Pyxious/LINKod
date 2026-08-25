<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function create(int $requestId)
    {
        $client        = auth()->user()->client;
        $serviceRequest = ServiceRequest::where('client_id', $client->client_id)
            ->findOrFail($requestId);

        // Only completed requests can be evaluated
        abort_if($serviceRequest->current_status !== 'Completed', 403, 'Only completed requests can be evaluated.');
        abort_if($serviceRequest->evaluation !== null, 403, 'You have already submitted an evaluation for this request.');

        return view('client.evaluations.create', compact('serviceRequest'));
    }

    public function store(Request $request, int $requestId)
    {
        $validated = $request->validate([
            'ratings'              => 'required|array',
            'ratings.quality'      => 'required|integer|min:1|max:5',
            'ratings.attitude'     => 'required|integer|min:1|max:5',
            'ratings.safety'       => 'required|integer|min:1|max:5',
            'ratings.time'         => 'required|integer|min:1|max:5',
            'ratings.housekeeping' => 'required|integer|min:1|max:5',
            'feedback_text'        => 'nullable|string|max:1000',
        ], [
            'ratings.quality.required'      => 'Please select a rating for Quality of Service.',
            'ratings.attitude.required'     => 'Please select a rating for Attitude.',
            'ratings.safety.required'       => 'Please select a rating for Safety Precaution Awareness.',
            'ratings.time.required'         => 'Please select a rating for Time Bound.',
            'ratings.housekeeping.required' => 'Please select a rating for Workplace Housekeeping.',
        ]);

        $client         = auth()->user()->client;
        $serviceRequest = ServiceRequest::where('client_id', $client->client_id)
            ->findOrFail($requestId);

        $ratings = $validated['ratings'];
        $sum     = array_sum($ratings);
        $count   = count($ratings);
        $overallRating = (int) round($sum / max($count, 1));

        $showName = $request->boolean('show_name', false);

        Evaluation::create([
            'client_id'         => $client->client_id,
            'request_id'        => $serviceRequest->request_id,
            'rating'            => $overallRating,
            'ratings_breakdown' => $ratings,
            'show_name'         => $showName,
            'feedback_text'     => $validated['feedback_text'] ?? null,
            'rated_at'          => now(),
        ]);

        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Client submitted evaluation for request #{$serviceRequest->request_id} (Average Rating: {$overallRating}/5)",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('client.requests.show', $requestId)
            ->with('success', 'Thank you for your feedback! We are happy to serve!!!');
    }
}
