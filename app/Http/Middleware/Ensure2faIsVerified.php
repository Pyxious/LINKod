<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Ensure2faIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for unauthenticated users
        if (!auth()->check()) {
            return $next($request);
        }

        // If the user has a totp_secret but hasn't verified this session, redirect to 2FA
        if (auth()->user()->totp_secret && !session('2fa_verified')) {
            return redirect()->route('2fa.index');
        }

        return $next($request);
    }
}
