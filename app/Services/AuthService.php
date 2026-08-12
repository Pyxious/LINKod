<?php

namespace App\Services;

use App\Models\User;
use App\Models\Client;
use App\Models\Staff;
use App\Models\Worker;
use App\Models\Team;
use App\Models\UserLog;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthService
{
    protected string $allowedDomain;

    public function __construct()
    {
        $this->allowedDomain = env('BU_EMAIL_DOMAIN', 'bicol-u.edu.ph');
    }

    /**
     * Validate that the Google email belongs to the BU domain.
     */
    public function isAllowedDomain(string $email): bool
    {
        return str_ends_with($email, '@' . $this->allowedDomain);
    }

    /**
     * Find or create a User from the Google OAuth payload.
     * - If the email already exists → return that user (role unchanged).
     * - If new → create USER record with role=client + CLIENT record.
     */
    public function findOrCreateUser(SocialiteUser $googleUser): User
    {
        // Look up by SHA-256 hash of the email, since email_account is
        // encrypted at rest and cannot be queried directly.
        $emailHash = hash('sha256', strtolower(trim($googleUser->getEmail())));
        $user = User::where('email_hash', $emailHash)->first();

        if (!$user) {
            // Parse name parts from Google
            $nameParts = explode(' ', $googleUser->getName(), 3);
            $firstName  = $nameParts[0] ?? '';
            $lastName   = $nameParts[1] ?? '';
            $middleName = $nameParts[2] ?? null;

            $user = User::create([
                'username'       => $this->generateUsername($googleUser->getEmail()),
                'first_name'     => $firstName,
                'last_name'      => $lastName,
                'middle_name'    => $middleName,
                'email_account'  => $googleUser->getEmail(), // encrypted via $casts
                'email_hash'     => hash('sha256', strtolower(trim($googleUser->getEmail()))),
                'role'           => 'client',   // default role
                'password'       => \Illuminate\Support\Str::random(32), // hashed via $casts
            ]);

            // Create corresponding CLIENT record
            Client::create([
                'user_id' => $user->user_id,
            ]);
        } else {
            // If user exists (e.g., created via walk-in request), ensure client model exists
            if (!$user->client && $user->role === 'client') {
                Client::create([
                    'user_id' => $user->user_id,
                ]);
            }

            // Update placeholder names if they were set as walk-in defaults
            $nameParts = explode(' ', $googleUser->getName(), 3);
            $firstName = $nameParts[0] ?? '';
            $lastName  = $nameParts[1] ?? '';
            if ($firstName && (empty($user->first_name) || strtolower($user->first_name) === 'walk-in')) {
                $user->update([
                    'first_name' => $firstName,
                    'last_name'  => $lastName ?: $user->last_name,
                ]);
            }
        }

        // Ensure Staff & Worker records exist if user has worker role
        if ($user->role === 'worker') {
            $staff = Staff::firstOrCreate([
                'user_id' => $user->user_id,
            ], [
                'role'       => 'Maintenance Personnel',
                'date_hired' => now()->toDateString(),
            ]);

            if (!$staff->worker) {
                $defaultTeam = Team::first();
                Worker::create([
                    'staff_id'     => $staff->staff_id,
                    'team_id'      => $defaultTeam?->team_id,
                    'date_hired'   => now()->toDateString(),
                    'is_available' => true,
                ]);
            }
        }

        // Log the login event
        UserLog::create([
            'user_id'    => $user->user_id,
            'action'     => 'User logged in via Google SSO',
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return $user;
    }

    /**
     * Generate a username from the email prefix.
     */
    protected function generateUsername(string $email): string
    {
        $base = strtolower(explode('@', $email)[0]);
        $base = preg_replace('/[^a-z0-9_]/', '_', $base);

        // Ensure uniqueness
        $username = $base;
        $counter  = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $counter++;
        }

        return $username;
    }

    /**
     * Return the redirect path based on the user's role.
     */
    public function dashboardRoute(User $user): string
    {
        return match ($user->role) {
            'admin'  => route('admin.dashboard'),
            'worker' => route('worker.dashboard'),
            default  => route('client.dashboard'),
        };
    }
}
