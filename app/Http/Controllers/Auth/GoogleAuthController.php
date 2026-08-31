<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * Redirect to Google OAuth.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $domain = env('BU_EMAIL_DOMAIN', 'bicol-u.edu.ph');

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with([
                'hd'     => $domain,
                'prompt' => 'select_account',
            ])
            ->redirect();
    }

    /**
     * Redirect to Google OAuth (Admin).
     */
    public function redirectAdmin(Request $request): RedirectResponse
    {
        return $this->redirect($request);
    }

    /**
     * Redirect to Google OAuth (Staff).
     */
    public function redirectStaff(Request $request): RedirectResponse
    {
        return $this->redirect($request);
    }

    /**
     * Handle Google OAuth callback.
     */
    public function callback(Request $request): RedirectResponse
    {
        // User denied access
        if ($request->has('error')) {
            return redirect()->route('login')
                ->with('error', 'Google sign-in was cancelled.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Auth Error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Failed to authenticate with Google. Please try again.');
        }

        // Domain check — only BU accounts allowed
        if (!$this->authService->isAllowedDomain($googleUser->getEmail())) {
            return redirect()->route('login')
                ->with('error', 'Only Bicol University accounts (@bicol-u.edu.ph) are allowed to sign in.');
        }

        // Find or create user
        $user = $this->authService->findOrCreateUser($googleUser);

        // Log in
        Auth::login($user);

        // Redirect to 2FA if enabled, otherwise go to intended dashboard
        if ($user->totp_secret) {
            return redirect()->route('2fa.index');
        }

        // Generate redirect route based on role (RBAC)
        $redirectRoute = match ($user->role) {
            'admin' => 'admin.dashboard',
            'worker' => 'worker.dashboard',
            default => 'client.dashboard',
        };

        return redirect()->route($redirectRoute);
    }

    /**
     * Logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
