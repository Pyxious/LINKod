<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePilotAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. If site lock is not enabled, pass through
        if (!config('app.site_lock_enabled', false)) {
            return $next($request);
        }

        // 2. Allow health check and pilot access endpoints
        if (
            $request->is('up') ||
            $request->is('pilot-access') ||
            $request->is('pilot-access/*')
        ) {
            return $next($request);
        }

        $expectedCode = (string) config('app.site_access_code', 'LINKodTEAM2026');
        $validToken = hash_hmac('sha256', $expectedCode, (string) config('app.key'));

        // 3. Check session first
        if ($request->session()->get('pilot_access_granted') === true) {
            return $next($request);
        }

        // 4. Check persistent cookie (30-day token)
        $cookieToken = $request->cookie('pilot_access_token');
        if ($cookieToken && hash_equals($validToken, (string) $cookieToken)) {
            $request->session()->put('pilot_access_granted', true);
            return $next($request);
        }

        // 5. Store intended URL for GET requests so user returns there after entering code
        if ($request->isMethod('GET') && !$request->expectsJson()) {
            $request->session()->put('pilot_access_intended', $request->fullUrl());
        }

        return redirect()->route('pilot.access');
    }
}
