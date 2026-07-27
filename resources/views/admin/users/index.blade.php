@extends('layouts.admin')
@section('page-title', 'User Management')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@endpush

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6 font-sans">
    
    <!-- Page Banner -->
    <div class="bg-[#fefce8] border border-[#1a3c8f] rounded-xl px-8 py-6 flex justify-between items-center shadow-sm">
        <div>
            <h1 class="text-[#1a3c8f] text-2xl font-bold mb-1">Users</h1>
            <p class="text-[#1a3c8f] text-sm opacity-90">Manage users, roles, and permissions</p>
        </div>
        <button class="bg-[#1a3c8f] text-white px-5 py-2.5 rounded-md text-sm font-medium hover:bg-[#152e6e] transition">
            + Add User
        </button>
    </div>

    <!-- KPI Grid (4 Cards) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
            <div class="text-[#1a3c8f] text-sm font-medium mb-2">Total Users</div>
            <div class="text-[#1a3c8f] text-3xl font-bold leading-none mb-2">{{ $totalUsers }}</div>
            <div class="text-xs text-gray-500">Active users</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
            <div class="text-[#1a3c8f] text-sm font-medium mb-2">Administrators</div>
            <div class="text-[#1a3c8f] text-3xl font-bold leading-none mb-2">{{ $totalAdmins }}</div>
            <div class="text-xs text-gray-500">With full access</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
            <div class="text-[#1a3c8f] text-sm font-medium mb-2">Workers</div>
            <div class="text-[#1a3c8f] text-3xl font-bold leading-none mb-2">{{ $totalWorkers }}</div>
            <div class="text-xs text-gray-500">Field personnel</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
            <div class="text-[#1a3c8f] text-sm font-medium mb-2">Clients</div>
            <div class="text-[#1a3c8f] text-3xl font-bold leading-none mb-2">{{ $totalClients }}</div>
            <div class="text-xs text-gray-500">Requestors</div>
        </div>
    </div>

    <!-- Chart Section (2 Graphs) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- User Distribution Donut Chart -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <div class="flex justify-between items-start mb-5">
                <div>
                    <div class="text-[#1a3c8f] text-lg font-bold">User Distribution</div>
                    <div class="text-[#1a3c8f] text-[13px] opacity-80">By Role</div>
                </div>
            </div>
            <div class="flex justify-center items-center h-[220px]">
                <canvas id="userDonut"></canvas>
            </div>
        </div>

        <!-- Registration Trends Bar Chart -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <div class="flex justify-between items-start mb-5">
                <div>
                    <div class="text-[#1a3c8f] text-lg font-bold">User Registration Trends</div>
                    <div class="text-[#1a3c8f] text-[13px] opacity-80">New Users</div>
                </div>
                <select class="bg-gray-100 border-none px-3 py-1.5 rounded-md text-[#1a3c8f] text-xs outline-none cursor-pointer">
                    <option>This Week</option>
                    <option>This Month</option>
                </select>
            </div>
            <div class="h-[220px]">
                <canvas id="trendBar"></canvas>
            </div>
            <div class="text-center text-[11px] text-gray-500 mt-2 font-bold">JUNE 2026</div>
        </div>
    </div>

    <!-- Users Directory Table Container (White Background) -->
    <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 shadow-sm p-6">
        
        <!-- Header Bar: Title + Search & Filters -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-5 border-b border-gray-100 dark:border-zinc-800">
            <div>
                <h2 class="text-2xl font-black text-[#0033a0] dark:text-blue-400 tracking-tight">Users Directory</h2>
                <p class="text-xs text-blue-500 dark:text-gray-400 font-semibold mt-0.5">List of Users</p>
            </div>

            <!-- Controls: Search Input, Role Select, Filters -->
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Search Input -->
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search" 
                           class="w-56 pl-4 pr-10 py-2 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#0033a0]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </div>

                <!-- Role Filter Select -->
                <select name="role" onchange="this.form.submit()" class="px-4 py-2 bg-[#0033a0] text-white rounded-xl text-xs font-bold focus:outline-none cursor-pointer shadow-sm">
                    <option value="">All Roles</option>
                    <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>Client</option>
                    <option value="worker" {{ request('role') === 'worker' ? 'selected' : '' }}>Worker</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>

                <!-- Date Filter Input -->
                <div class="relative">
                    <input type="date" 
                           name="date" 
                           value="{{ request('date') }}" 
                           onchange="this.form.submit()"
                           class="px-4 py-2 bg-[#0033a0] text-white rounded-xl text-xs font-bold focus:outline-none cursor-pointer shadow-sm appearance-none">
                </div>

                @if(request('search') || request('role') || request('date'))
                    <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-gray-300 rounded-xl text-xs font-bold hover:bg-gray-200 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Users Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-zinc-800 text-xs font-extrabold text-[#0033a0] dark:text-blue-400 uppercase tracking-wider">
                        <th class="py-3 px-3 w-10">
                            <input type="checkbox" class="rounded border-gray-300 text-[#0033a0] focus:ring-[#0033a0]">
                        </th>
                        <th class="py-3 px-4">
                            NAME <span class="text-[10px]">↑</span>
                        </th>
                        <th class="py-3 px-4">EMAIL/CONTACT</th>
                        <th class="py-3 px-4">ROLE</th>
                        <th class="py-3 px-4">STATUS</th>
                        <th class="py-3 px-4">LAST ACTIVE</th>
                        <th class="py-3 px-4 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-xs">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-zinc-800/50 transition">
                            <!-- Checkbox Column -->
                            <td class="py-3.5 px-3">
                                <input type="checkbox" class="rounded border-gray-300 text-[#0033a0] focus:ring-[#0033a0]">
                            </td>

                            <!-- Name Column (Avatar + Full Name + ID) -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-gray-200 dark:bg-zinc-700 shrink-0 flex items-center justify-center font-bold text-gray-500 dark:text-gray-300 text-xs">
                                        {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-[#0033a0] dark:text-blue-400 text-xs leading-tight">
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </div>
                                        <div class="text-[11px] text-gray-400 font-semibold">
                                            ID: {{ $user->user_id }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Email / Contact Column -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-[#0033a0] dark:text-gray-300 text-xs">
                                    {{ $user->email_account }}
                                </div>
                                @if($user->contact_number)
                                    <div class="text-[11px] text-[#0033a0] dark:text-gray-400 font-medium">
                                        {{ $user->contact_number }}
                                    </div>
                                @endif
                            </td>

                            <!-- Role Column -->
                            <td class="py-3.5 px-4 font-semibold text-[#0033a0] dark:text-blue-300">
                                @php
                                    $roleTitle = match(strtolower($user->role)) {
                                        'admin' => 'Administrator',
                                        'worker' => 'Worker',
                                        default => 'Client'
                                    };
                                @endphp
                                {{ $roleTitle }}
                            </td>

                            <!-- Status Column -->
                            <td class="py-3.5 px-4">
                                <span class="px-3 py-0.5 rounded-full text-[11px] font-bold border border-emerald-400 text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-300">
                                    Active
                                </span>
                            </td>

                            <!-- Last Active Column (Functional from UserLog) -->
                            <td class="py-3.5 px-4 font-semibold text-[#0033a0] dark:text-gray-300">
                                @if($user->latestLog?->created_at)
                                    <span title="{{ \Carbon\Carbon::parse($user->latestLog->created_at)->format('M d, Y h:i A') }}">
                                        {{ \Carbon\Carbon::parse($user->latestLog->created_at)->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-gray-400 font-normal">No recent activity</span>
                                @endif
                            </td>

                            <!-- Actions Column -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user->user_id) }}" class="p-1 text-[#0033a0] dark:text-blue-400 hover:text-[#002480] transition" title="Edit Role">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <button type="button" class="p-1 text-gray-400 hover:text-gray-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400 italic">
                                No users found matching your directory filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer: Centered Pagination & Counter -->
        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="hidden sm:block w-1/4"></div>

            <!-- Centered Pagination Links -->
            <div class="flex justify-center items-center flex-1">
                {{ $users->links() }}
            </div>

            <!-- Right Counter -->
            <div class="text-xs text-gray-400 font-semibold text-center sm:text-right w-full sm:w-1/4">
                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
// Donut Chart
new Chart(document.getElementById('userDonut'), {
    type: 'doughnut',
    data: {
        labels: ['Client', 'Admin', 'Worker'],
        datasets: [{
            data: [{{ $totalClients }}, {{ $totalAdmins }}, {{ $totalWorkers }}],
            backgroundColor: ['#93c5fd','#fcd34d','#c6e8b3'],
            borderWidth: 0,
        }]
    },
    options: {
        cutout: '60%',
        plugins: { 
            legend: { 
                position: 'right',
                labels: { boxWidth: 12, usePointStyle: true, color: '#6b7280' }
            } 
        },
        responsive: true,
        maintainAspectRatio: false,
    }
});

// Bar Chart
new Chart(document.getElementById('trendBar'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($trends)) !!},
        datasets: [{
            data: {!! json_encode(array_values($trends)) !!},
            backgroundColor: '#1a3c8f',
            borderRadius: 2,
            barThickness: 30,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { 
                beginAtZero: true, 
                ticks: { precision: 0, color: '#9ca3af', stepSize: 1 }, 
                grid: { color: '#f3f4f6' },
                border: { display: false }
            },
            x: { 
                grid: { display: false },
                ticks: { color: '#9ca3af', font: {size: 10} },
                border: { display: false }
            }
        },
        responsive: true,
        maintainAspectRatio: false,
    }
});
</script>
@endpush
