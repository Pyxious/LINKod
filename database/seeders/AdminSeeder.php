<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Staff;
use App\Models\Worker;
use App\Models\Team;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Accounts
        $adminEmails = [
            'jvba2023-8191-82939@bicol-u.edu.ph',
            'admin@bicol-u.edu.ph'
        ];

        foreach ($adminEmails as $email) {
            $emailHash = hash('sha256', strtolower(trim($email)));
            $user = User::where('email_hash', $emailHash)->first();

            if ($user) {
                $user->update(['role' => 'admin']);
            } else {
                $user = User::create([
                    'username'      => explode('@', $email)[0],
                    'first_name'    => 'Admin',
                    'last_name'     => 'User',
                    'email_account' => $email,
                    'email_hash'    => $emailHash,
                    'role'          => 'admin',
                    'password'      => Str::random(32),
                ]);
            }

            Staff::firstOrCreate([
                'user_id' => $user->user_id,
            ], [
                'role'       => 'Administrator',
                'date_hired' => now()->toDateString(),
            ]);
        }

        // 2. Worker Accounts
        $workerEmails = [
            'kjqa2023-7321-29411@bicol-u.edu.ph',
            'worker@bicol-u.edu.ph'
        ];

        $defaultTeam = Team::first();

        foreach ($workerEmails as $email) {
            $emailHash = hash('sha256', strtolower(trim($email)));
            $user = User::where('email_hash', $emailHash)->first();

            if ($user) {
                $user->update(['role' => 'worker']);
            } else {
                $user = User::create([
                    'username'      => explode('@', $email)[0],
                    'first_name'    => 'Worker',
                    'last_name'     => 'Personnel',
                    'email_account' => $email,
                    'email_hash'    => $emailHash,
                    'role'          => 'worker',
                    'password'      => Str::random(32),
                ]);
            }

            $staff = Staff::firstOrCreate([
                'user_id' => $user->user_id,
            ], [
                'role'       => 'Maintenance Personnel',
                'date_hired' => now()->toDateString(),
            ]);

            if (!$staff->worker) {
                Worker::create([
                    'staff_id'     => $staff->staff_id,
                    'team_id'      => $defaultTeam?->team_id,
                    'date_hired'   => now()->toDateString(),
                    'is_available' => true,
                ]);
            }
        }
    }
}
