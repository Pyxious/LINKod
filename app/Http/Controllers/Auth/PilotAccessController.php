<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class PilotAccessController extends Controller
{
    /**
     * Show the pilot access code screen.
     */
    public function show(Request $request): View|RedirectResponse
    {
        // If site lock is not enabled, redirect to home
        if (!config('app.site_lock_enabled', false)) {
            return redirect()->route('home');
        }

        // If already unlocked, redirect to intended or home
        $expectedCode = (string) config('app.site_access_code', 'LINKodTEAM2026');
        $validToken = hash_hmac('sha256', $expectedCode, (string) config('app.key'));

        if (
            $request->session()->get('pilot_access_granted') === true ||
            ($request->cookie('pilot_access_token') && hash_equals($validToken, (string) $request->cookie('pilot_access_token')))
        ) {
            $intended = $request->session()->pull('pilot_access_intended', route('home'));
            return redirect()->to($intended);
        }

        return view('auth.pilot-access');
    }

    /**
     * Verify the entered passcode.
     */
    public function verify(Request $request): RedirectResponse
    {
        // Rate limit: 5 attempts per minute per IP
        $throttleKey = 'pilot-access:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', "Too many failed attempts. Please try again in {$seconds} seconds.");
        }

        $request->validate([
            'access_code' => ['required', 'string'],
        ]);

        $expectedCode = (string) config('app.site_access_code', 'LINKodTEAM2026');

        if (!hash_equals($expectedCode, trim((string) $request->input('access_code')))) {
            RateLimiter::hit($throttleKey, 60);
            return back()->with('error', 'Incorrect access code. Please try again.');
        }

        RateLimiter::clear($throttleKey);

        $validToken = hash_hmac('sha256', $expectedCode, (string) config('app.key'));

        // Set session
        $request->session()->put('pilot_access_granted', true);

        // Set 30-day persistent cookie (43,200 minutes)
        $cookie = cookie(
            'pilot_access_token',
            $validToken,
            43200,
            '/',
            null,
            $request->isSecure(),
            true, // httpOnly
            false,
            'Lax'
        );

        $intended = $request->session()->pull('pilot_access_intended', route('home'));

        return redirect()->to($intended)->withCookie($cookie);
    }
}
