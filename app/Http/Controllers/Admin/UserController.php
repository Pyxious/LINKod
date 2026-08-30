<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Staff;
use App\Models\Worker;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Sort Filter (recently added/joined, oldest, name)
        $sort = $request->query('sort', 'recent');

        // Base query — apply role filter at DB level (unencrypted column, safe)
        $query = User::with('latestLog');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // ── Search (Fix: first_name, last_name, email_account are AES-256 encrypted)
        // LIKE queries on encrypted ciphertext always return 0 results.
        // Solution: load all matching-role users, decrypt in PHP, then filter by search term.
        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $emailHash = hash('sha256', $search); // exact email match via hash index

            // Fetch all users for in-memory search (paginate after filtering)
            $allUsers = $query->get();

            $filtered = $allUsers->filter(function (User $user) use ($search, $emailHash) {
                // Match by SHA-256 hash for exact email lookup
                if ($user->getAttributes()['email_hash'] === $emailHash) {
                    return true;
                }
                // Match decrypted name fields (partial, case-insensitive)
                $fullName = strtolower("{$user->first_name} {$user->last_name}");
                if (str_contains($fullName, $search)) {
                    return true;
                }
                // Match decrypted email (partial)
                if (str_contains(strtolower($user->email_account ?? ''), $search)) {
                    return true;
                }
                return false;
            });

            // Apply sort on filtered collection
            $filtered = match($sort) {
                'oldest' => $filtered->sortBy('user_id'),
                'name'   => $filtered->sortBy(fn($u) => strtolower("{$u->first_name} {$u->last_name}")),
                default  => $filtered->sortByDesc('user_id'),
            };

            // Manual pagination of collection
            $page       = $request->query('page', 1);
            $perPage    = 15;
            $users      = new \Illuminate\Pagination\LengthAwarePaginator(
                $filtered->forPage($page, $perPage)->values(),
                $filtered->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            // No search — safe to sort at DB level for non-encrypted columns
            $query = match($sort) {
                'oldest' => $query->orderBy('user_id', 'asc'),
                'name'   => $query->orderBy('user_id', 'asc'), // can't ORDER BY encrypted columns; fallback to ID
                default  => $query->orderBy('user_id', 'desc'),
            };

            $users = $query->paginate(15)->appends($request->query());
        }

        $totalUsers   = User::count();
        $totalAdmins  = User::where('role', 'admin')->count();
        $totalWorkers = User::where('role', 'worker')->count();
        $totalClients = User::where('role', 'client')->count();

        // ── Real Registration Trends (Week, Month, Year) ───────────────────
        $allUsersForTrends = User::with('staff')->get();
        $firstLogs = \App\Models\UserLog::selectRaw('user_id, MIN(created_at) as first_log_date')
            ->groupBy('user_id')
            ->pluck('first_log_date', 'user_id');

        $userRegistrationDates = [];
        foreach ($allUsersForTrends as $u) {
            if (isset($firstLogs[$u->user_id]) && $firstLogs[$u->user_id]) {
                $userRegistrationDates[] = Carbon::parse($firstLogs[$u->user_id]);
            } elseif ($u->staff?->date_hired) {
                $userRegistrationDates[] = Carbon::parse($u->staff->date_hired);
            } else {
                $userRegistrationDates[] = Carbon::parse('2026-07-27');
            }
        }

        // 1. Weekly: breakdown by day (Mon–Sun) for the current week
        $startOfWeek  = now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek    = now()->endOfWeek(Carbon::SUNDAY);
        $dayLabels    = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $weeklyTrends = array_fill_keys($dayLabels, 0);

        foreach ($userRegistrationDates as $date) {
            if ($date->between($startOfWeek, $endOfWeek)) {
                $dayName = $date->format('D');
                if (isset($weeklyTrends[$dayName])) {
                    $weeklyTrends[$dayName]++;
                }
            }
        }

        // 2. Monthly: breakdown by week of month (Week 1–5) for current month
        $monthlyTrends = ['Week 1' => 0, 'Week 2' => 0, 'Week 3' => 0, 'Week 4' => 0, 'Week 5' => 0];

        foreach ($userRegistrationDates as $date) {
            if ($date->isSameMonth(now()) && $date->isSameYear(now())) {
                $dayOfMonth = $date->day;
                $weekNum = min(5, (int) ceil($dayOfMonth / 7));
                $monthlyTrends["Week {$weekNum}"]++;
            }
        }

        // 3. Yearly: breakdown by month (Jan–Dec) for current year
        $monthLabels  = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $yearlyTrends = array_fill_keys($monthLabels, 0);

        foreach ($userRegistrationDates as $date) {
            if ($date->isSameYear(now())) {
                $monthName = $date->format('M');
                if (isset($yearlyTrends[$monthName])) {
                    $yearlyTrends[$monthName]++;
                }
            }
        }

        $trends = $monthlyTrends;

        return view('admin.users.index', compact(
            'users', 'totalUsers', 'totalAdmins', 'totalWorkers', 'totalClients',
            'trends', 'weeklyTrends', 'monthlyTrends', 'yearlyTrends'
        ));
    }

    public function edit(int $id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'role' => 'required|in:client,worker,admin',
        ]);

        $user = User::findOrFail($id);
        $oldRole = $user->role;
        $newRole = $validated['role'];

        $user->update(['role' => $newRole]);

        // If user was previously a worker and role is changed to non-worker, remove worker record
        if ($oldRole === 'worker' && $newRole !== 'worker') {
            if ($user->staff?->worker) {
                $user->staff->worker->delete();
            }
        }

        // Ensure corresponding role record exists
        if ($newRole === 'client' && !$user->client) {
            Client::create(['user_id' => $user->user_id]);
        }

        if (in_array($newRole, ['worker', 'admin'])) {
            $staff = $user->staff ?? Staff::create([
                'user_id'    => $user->user_id,
                'role'       => $newRole,
                'date_hired' => now()->toDateString(),
            ]);

            if ($newRole === 'worker' && !$staff->worker) {
                Worker::create([
                    'staff_id'     => $staff->staff_id,
                    'is_available' => true,
                ]);
            }
        }

        \App\Models\UserLog::create([
            'user_id'    => auth()->id(),
            'action'     => "Admin updated user #{$id} role from {$oldRole} to {$newRole}",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Role updated to \"{$newRole}\" for {$user->first_name} {$user->last_name}.");
    }
}
