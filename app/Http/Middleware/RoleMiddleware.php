<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        foreach ($roles as $role) {
            if ($user->role === $role) {
                return $next($request);
            }

            // Workers can also access client portal features
            if ($role === 'client' && $user->role === 'worker') {
                if (!$user->client) {
                    \App\Models\Client::firstOrCreate(['user_id' => $user->user_id]);
                }
                return $next($request);
            }
        }

        return redirect()->route('unauthorized');
    }
}
