<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Staff;
use App\Models\TeamLeader;
use App\Models\Team;
use App\Models\Worker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('team')->truncate();
        DB::table('team_leader')->truncate();

        // Delete previous mock users/staff/workers
        $mockUserIds = DB::table('user')->where('username', 'like', 'mock_%')->pluck('user_id');
        if ($mockUserIds->isNotEmpty()) {
            $staffIds = DB::table('staff')->whereIn('user_id', $mockUserIds)->pluck('staff_id');
            if ($staffIds->isNotEmpty()) {
                DB::table('worker')->whereIn('staff_id', $staffIds)->delete();
                DB::table('staff')->whereIn('staff_id', $staffIds)->delete();
            }
            DB::table('user')->whereIn('user_id', $mockUserIds)->delete();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $teamsData = [
            [
                'name' => 'Carpentry, Masonry & Electrical Team',
                'leader' => ['username' => 'mock_leader_cme', 'first_name' => 'Roberto', 'last_name' => 'Santos', 'email' => 'roberto.santos@bicol-u.edu.ph'],
                'workers' => [
                    ['username' => 'mock_worker_cme1', 'first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'email' => 'juan.delacruz@bicol-u.edu.ph'],
                    ['username' => 'mock_worker_cme2', 'first_name' => 'Pedro', 'last_name' => 'Penduko', 'email' => 'pedro.penduko@bicol-u.edu.ph'],
                ]
            ],
            [
                'name' => 'Plumbing Team',
                'leader' => ['username' => 'mock_leader_plumb', 'first_name' => 'Mario', 'last_name' => 'Bautista', 'email' => 'mario.bautista@bicol-u.edu.ph'],
                'workers' => [
                    ['username' => 'mock_worker_plumb1', 'first_name' => 'Luigi', 'last_name' => 'Gonzales', 'email' => 'luigi.gonzales@bicol-u.edu.ph'],
                    ['username' => 'mock_worker_plumb2', 'first_name' => 'Ramon', 'last_name' => 'Magsaysay', 'email' => 'ramon.magsaysay@bicol-u.edu.ph'],
                ]
            ],
            [
                'name' => 'Painting Team',
                'leader' => ['username' => 'mock_leader_paint', 'first_name' => 'Fernando', 'last_name' => 'Amorsolo', 'email' => 'fernando.amorsolo@bicol-u.edu.ph'],
                'workers' => [
                    ['username' => 'mock_worker_paint1', 'first_name' => 'Juan', 'last_name' => 'Luna', 'email' => 'juan.luna@bicol-u.edu.ph'],
                    ['username' => 'mock_worker_paint2', 'first_name' => 'Guillermo', 'last_name' => 'Tolentino', 'email' => 'guillermo.tolentino@bicol-u.edu.ph'],
                ]
            ],
            [
                'name' => 'Janitorial Team',
                'leader' => ['username' => 'mock_leader_janitor', 'first_name' => 'Maria', 'last_name' => 'Clara', 'email' => 'maria.clara@bicol-u.edu.ph'],
                'workers' => [
                    ['username' => 'mock_worker_janitor1', 'first_name' => 'Gabriela', 'last_name' => 'Silang', 'email' => 'gabriela.silang@bicol-u.edu.ph'],
                    ['username' => 'mock_worker_janitor2', 'first_name' => 'Teresa', 'last_name' => 'Magbanua', 'email' => 'teresa.magbanua@bicol-u.edu.ph'],
                ]
            ],
            [
                'name' => 'Manpower Team',
                'leader' => ['username' => 'mock_leader_manpower', 'first_name' => 'Andres', 'last_name' => 'Bonifacio', 'email' => 'andres.bonifacio@bicol-u.edu.ph'],
                'workers' => [
                    ['username' => 'mock_worker_manpower1', 'first_name' => 'Emilio', 'last_name' => 'Jacinto', 'email' => 'emilio.jacinto@bicol-u.edu.ph'],
                    ['username' => 'mock_worker_manpower2', 'first_name' => 'Apolinario', 'last_name' => 'Mabini', 'email' => 'apolinario.mabini@bicol-u.edu.ph'],
                ]
            ],
            [
                'name' => 'Landscaping Team',
                'leader' => ['username' => 'mock_leader_landscape', 'first_name' => 'Samuel', 'last_name' => 'Bonaobra', 'email' => 'samuel.bonaobra@bicol-u.edu.ph'],
                'workers' => [
                    ['username' => 'mock_worker_landscape1', 'first_name' => 'Wilfredo', 'last_name' => 'Bumalay', 'email' => 'wilfredo.bumalay@bicol-u.edu.ph'],
                    ['username' => 'mock_worker_landscape2', 'first_name' => 'Jose', 'last_name' => 'Rizal', 'email' => 'jose.rizal@bicol-u.edu.ph'],
                ]
            ]
        ];

        foreach ($teamsData as $data) {
            $totalCount = count($data['workers']) + 1;

            // 1. Create Team record
            $team = Team::create([
                'team_name' => $data['name'],
                'team_leader' => null,
                'member_count' => $totalCount,
            ]);

            // 2. Create Leader User & Staff & TeamLeader & Worker
            $leaderData = $data['leader'];
            $emailHash = hash('sha256', strtolower(trim($leaderData['email'])));
            $leaderUser = User::create([
                'username' => $leaderData['username'],
                'first_name' => $leaderData['first_name'],
                'last_name' => $leaderData['last_name'],
                'email_account' => $leaderData['email'],
                'email_hash' => $emailHash,
                'role' => 'worker',
                'password' => Hash::make('Password123!'),
            ]);

            $leaderStaff = Staff::create([
                'user_id' => $leaderUser->user_id,
                'role' => 'Team Leader',
                'date_hired' => now()->subMonths(12)->toDateString(),
            ]);

            $teamLeaderObj = TeamLeader::create([
                'staff_id' => $leaderStaff->staff_id,
            ]);

            $team->update(['team_leader' => $teamLeaderObj->leader_id]);

            Worker::create([
                'staff_id' => $leaderStaff->staff_id,
                'team_id' => $team->team_id,
                'date_hired' => now()->subMonths(12)->toDateString(),
                'is_available' => true,
            ]);

            // 3. Create Team Workers
            foreach ($data['workers'] as $wData) {
                $wEmailHash = hash('sha256', strtolower(trim($wData['email'])));
                $wUser = User::create([
                    'username' => $wData['username'],
                    'first_name' => $wData['first_name'],
                    'last_name' => $wData['last_name'],
                    'email_account' => $wData['email'],
                    'email_hash' => $wEmailHash,
                    'role' => 'worker',
                    'password' => Hash::make('Password123!'),
                ]);

                $wStaff = Staff::create([
                    'user_id' => $wUser->user_id,
                    'role' => 'Worker',
                    'date_hired' => now()->subMonths(6)->toDateString(),
                ]);

                Worker::create([
                    'staff_id' => $wStaff->staff_id,
                    'team_id' => $team->team_id,
                    'date_hired' => now()->subMonths(6)->toDateString(),
                    'is_available' => true,
                ]);
            }
        }

        $this->command->info('✅ 6 Teams & Leaders & Workers successfully seeded!');
    }
}
