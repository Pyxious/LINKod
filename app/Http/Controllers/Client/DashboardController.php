<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $client = $user->client;

        $activeRequests = $client
            ? Cache::remember("client_{$client->client_id}_recent_requests", 120, fn() =>
                $client->requests()->with('latestHistory', 'category')->latest('submitted_at')->take(5)->get()
              )
            : collect();

        // Compute unread from a single notifications fetch
        $notifications = $user->notifications()->latest('sent_at')->take(10)->get();
        $unreadCount   = $notifications->where('is_read', false)->count();

        return view('client.dashboard', compact('user', 'activeRequests', 'unreadCount'));
    }
}
