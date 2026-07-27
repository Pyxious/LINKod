<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $client = $user->client;

        $activeRequests = $client
            ? $client->requests()->latest('submitted_at')->take(5)->get()
            : collect();

        $unreadCount = $user->notifications()->where('is_read', false)->count();

        return view('client.dashboard', compact('user', 'activeRequests', 'unreadCount'));
    }
}
