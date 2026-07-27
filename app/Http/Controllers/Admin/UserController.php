<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Staff;
use App\Models\Worker;
use App\Models\Client;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email_account', 'LIKE', "%{$search}%");
            });
        }

        // Sort Filter (recently added/joined, oldest, name)
        $sort = $request->query('sort', 'recent');
        if ($sort === 'oldest') {
            $query->orderBy('user_id', 'asc');
        } elseif ($sort === 'name') {
            $query->orderBy('first_name', 'asc')->orderBy('last_name', 'asc');
        } else { // default 'recent'
            $query->orderBy('user_id', 'desc');
        }

        $users = $query->with('latestLog')->paginate(15)->appends($request->query());

        $totalUsers   = User::count();
        $totalAdmins  = User::where('role', 'admin')->count();
        $totalWorkers = User::where('role', 'worker')->count();
        $totalClients = User::where('role', 'client')->count();

        // Chart Data
        $trends = [
            '1-7'   => 2,
            '8-14'  => 1,
            '15-21' => 4,
            '22-28' => 2,
            '29-30' => 3,
        ];

        return view('admin.users.index', compact(
            'users', 'totalUsers', 'totalAdmins', 'totalWorkers', 'totalClients', 'trends'
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
