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
            'ratings'       => 'nullable|array',
            'ratings.*'     => 'nullable|integer|min:1|max:5',
            'rating'        => 'nullable|integer|min:1|max:5',
            'feedback_text' => 'nullable|string|max:1000',
        ]);

        $client         = auth()->user()->client;
        $serviceRequest = ServiceRequest::where('client_id', $client->client_id)
            ->findOrFail($requestId);

        // Calculate average rating if multiple function ratings provided
        if (!empty($validated['ratings'])) {
            $sum = array_sum($validated['ratings']);
            $count = count($validated['ratings']);
            $overallRating = (int) round($sum / max($count, 1));
        } else {
            $overallRating = $validated['rating'] ?? 5;
        }

        Evaluation::create([
            'client_id'     => $client->client_id,
            'request_id'    => $serviceRequest->request_id,
            'rating'        => $overallRating,
            'feedback_text' => $validated['feedback_text'] ?? null,
            'rated_at'      => now(),
        ]);

        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Client submitted evaluation for request #{$serviceRequest->request_id} (Rating: {$overallRating})",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('client.requests.show', $requestId)
            ->with('success', 'Thank you for your feedback! We are happy to serve!!!');
    }
}
