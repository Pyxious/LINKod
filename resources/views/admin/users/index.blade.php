@extends('layouts.admin')
@section('page-title', 'User Management')



@section('content')
<div x-data="{
        openEditModal: false,
        editUserId: null,
        editUserName: '',
        editUserEmail: '',
        editUserRole: '',
        editFormAction: '',

        openEdit(id, name, email, role, actionUrl) {
            this.editUserId = id;
            this.editUserName = name;
            this.editUserEmail = email;
            this.editUserRole = role;
            this.editFormAction = actionUrl;
            this.openEditModal = true;
        }
     }" 
     class="w-full max-w-7xl mx-auto space-y-6 font-sans">
    
    <!-- Page Banner (Pale Yellow Header matching theme) -->
    <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-8 py-6 flex justify-between items-center shadow-sm">
        <div>
            <h1 class="text-[#0033a0] dark:text-blue-400 text-2xl font-bold mb-1">Users Management</h1>
            <p class="text-[#0033a0]/80 dark:text-gray-400 text-sm font-medium">Manage user accounts, roles, and permissions across campuses</p>
        </div>
    </div>

    <!-- KPI Grid (4 Cards) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-2xs">
            <div class="text-[#254378] dark:text-blue-300 text-sm font-semibold mb-2">Total Users</div>
            <div class="text-[#042B74] dark:text-white text-3xl font-bold leading-none mb-2">{{ $totalUsers }}</div>
            <div class="text-xs text-gray-400">Active accounts</div>
        </div>
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-2xs">
            <div class="text-[#254378] dark:text-blue-300 text-sm font-semibold mb-2">Administrators</div>
            <div class="text-[#042B74] dark:text-white text-3xl font-bold leading-none mb-2">{{ $totalAdmins }}</div>
            <div class="text-xs text-gray-400">With full access</div>
        </div>
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-2xs">
            <div class="text-[#254378] dark:text-blue-300 text-sm font-semibold mb-2">Workers</div>
            <div class="text-[#042B74] dark:text-white text-3xl font-bold leading-none mb-2">{{ $totalWorkers }}</div>
            <div class="text-xs text-gray-400">Field personnel</div>
        </div>
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-2xs">
            <div class="text-[#254378] dark:text-blue-300 text-sm font-semibold mb-2">Clients</div>
            <div class="text-[#042B74] dark:text-white text-3xl font-bold leading-none mb-2">{{ $totalClients }}</div>
            <div class="text-xs text-gray-400">Requestors</div>
        </div>
    </div>

    <!-- Chart Section (2 Graphs) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- User Distribution Donut Chart -->
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 shadow-2xs">
            <div class="flex justify-between items-start mb-5">
                <div>
                    <div class="text-[#042B74] dark:text-blue-400 text-lg font-bold">User Distribution</div>
                    <div class="text-xs text-[#47658F] dark:text-gray-400">By Role</div>
                </div>
            </div>
            <div class="relative flex justify-center items-center h-[220px]">
                <canvas id="userDonut"></canvas>
            </div>
        </div>

        <!-- Registration Trends Bar Chart -->
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 shadow-2xs">
            <div class="flex justify-between items-start mb-5">
                <div>
                    <div class="text-[#042B74] dark:text-blue-400 text-lg font-bold">User Registration Trends</div>
                    <div class="text-xs text-[#47658F] dark:text-gray-400">New Users</div>
                </div>
                <select id="trendPeriodSelect" class="bg-gray-100 dark:bg-zinc-800 border-none px-3 py-1.5 rounded-md text-xs font-semibold text-gray-700 dark:text-gray-300 outline-none cursor-pointer">
                    <option value="month" selected>This Month (by Week)</option>
                    <option value="week">This Week (by Day)</option>
                    <option value="year">This Year (by Month)</option>
                </select>
            </div>
            <div class="relative h-[220px]">
                <canvas id="trendBar"></canvas>
            </div>
            <div id="trendPeriodLabel" class="text-center text-[11px] text-gray-400 mt-2 font-bold">{{ strtoupper(now()->format('F Y')) }} (WEEK 1 - 5)</div>
        </div>
    </div>

    <!-- Users Directory Table Container -->
    <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 shadow-2xs p-6">
        
        <!-- Header Bar: Title + Search & Filters -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-5 border-b border-gray-100 dark:border-zinc-800">
            <div>
                <h2 class="text-2xl font-bold text-[#042B74] dark:text-blue-400 tracking-tight">Users Directory</h2>
                <p class="text-xs text-[#47658F] dark:text-gray-400 font-medium mt-0.5">Manage and edit registered user roles</p>
            </div>

            <!-- Controls: Search Input, Role Select, Filters -->
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Search Input -->
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search users..." 
                           class="w-56 pl-4 pr-10 py-2 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0038A8]">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#0038A8]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </div>

                <!-- Role Filter Select -->
                <select name="role" onchange="this.form.submit()" class="px-4 py-2 bg-[#0038A8] text-white rounded-xl text-xs font-semibold focus:outline-none cursor-pointer shadow-2xs">
                    <option value="">All Roles</option>
                    <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>Client</option>
                    <option value="worker" {{ request('role') === 'worker' ? 'selected' : '' }}>Worker</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>

                <!-- Sort Order Select (Recently Joined / Oldest First) -->
                <select name="sort" onchange="this.form.submit()" class="px-4 py-2 bg-[#0038A8] text-white rounded-xl text-xs font-semibold focus:outline-none cursor-pointer shadow-2xs">
                    <option value="recent" {{ request('sort', 'recent') === 'recent' ? 'selected' : '' }}>Recently Joined</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                </select>

                @if(request('search') || request('role') || (request('sort') && request('sort') !== 'recent'))
                    <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-gray-300 rounded-xl text-xs font-semibold hover:bg-gray-200 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Users Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-zinc-800 text-xs font-bold text-[#042B74] dark:text-blue-400 uppercase tracking-wider">
                        <th class="py-3 px-3 w-10">
                            <input type="checkbox" class="rounded border-gray-300 text-[#0038A8] focus:ring-[#0038A8]">
                        </th>
                        <th class="py-3 px-4">NAME</th>
                        <th class="py-3 px-4">EMAIL / CONTACT</th>
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
                                <input type="checkbox" class="rounded border-gray-300 text-[#0038A8] focus:ring-[#0038A8]">
                            </td>

                            <!-- Name Column (Avatar + Full Name + ID) -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#0038A8] text-white shrink-0 flex items-center justify-center font-bold text-xs shadow-2xs">
                                        {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white text-xs leading-tight">
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
                                <div class="font-semibold text-gray-800 dark:text-gray-200 text-xs">
                                    {{ $user->email_account }}
                                </div>
                                @if($user->contact_number)
                                    <div class="text-[11px] text-gray-400 font-medium mt-0.5">
                                        {{ $user->contact_number }}
                                    </div>
                                @endif
                            </td>

                            <!-- Role Column -->
                            <td class="py-3.5 px-4 font-semibold text-gray-700 dark:text-gray-300">
                                @php
                                    $roleTitle = match(strtolower($user->role)) {
                                        'admin' => 'Administrator',
                                        'worker' => 'Worker',
                                        default => 'Client'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-[#0038A8] dark:bg-blue-950/50 dark:text-blue-300">
                                    {{ $roleTitle }}
                                </span>
                            </td>

                            <!-- Status Column -->
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border border-emerald-400 text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-300">
                                    Active
                                </span>
                            </td>

                            <!-- Last Active Column -->
                            <td class="py-3.5 px-4 font-medium text-gray-600 dark:text-gray-300">
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
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Edit User Role Button (Triggers Popup Modal) -->
                                    <button type="button" 
                                            @click="openEdit({{ $user->user_id }}, '{{ addslashes($user->first_name . ' ' . $user->last_name) }}', '{{ addslashes($user->email_account) }}', '{{ $user->role }}', '{{ route('admin.users.update', $user->user_id) }}')" 
                                            class="p-2 text-[#0038A8] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-zinc-800 rounded-lg transition" 
                                            title="Edit User Role">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
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

    <!-- Edit User Role Modal Popup (Themed Dialog) -->
    <div x-show="openEditModal" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[99999] bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        
        <div @click.outside="openEditModal = false"
             x-show="openEditModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl max-w-md w-full shadow-2xl overflow-hidden my-auto">
            
            <!-- Modal Top Header (Sleek Clean Header) -->
            <div class="bg-white dark:bg-[#1c1c1e] border-b border-gray-100 dark:border-zinc-800 p-6 flex items-center justify-between">
                <div>
                    <h3 class="text-[#042B74] dark:text-blue-400 font-bold text-xl">Edit User Role</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manage user access privileges & system role</p>
                </div>
                <button type="button" @click="openEditModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-full hover:bg-gray-100 dark:hover:bg-zinc-800 transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form :action="editFormAction" method="POST">
                @csrf
                @method('PUT')
                
                <div class="p-6 space-y-5">
                    <!-- User Info Summary Box -->
                    <div class="bg-[#EBF3FE] dark:bg-[#151d2a] border border-[#7DAAF4]/60 dark:border-blue-800/60 rounded-xl p-4 flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-full bg-[#0038A8] text-white font-extrabold text-lg flex items-center justify-center shrink-0 shadow-2xs"
                             x-text="editUserName ? editUserName.charAt(0).toUpperCase() : 'U'">
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate" x-text="editUserName"></h4>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate mt-0.5" x-text="editUserEmail"></p>
                        </div>
                    </div>

                    <!-- Role Selection Options -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3">
                            Assign New Role <span class="text-red-500">*</span>
                        </label>

                        <div class="space-y-2.5">
                            <!-- Client Role Option -->
                            <label class="flex items-start p-3.5 border rounded-xl cursor-pointer transition"
                                   :class="editUserRole === 'client' ? 'border-[#0038A8] bg-blue-50/50 dark:bg-blue-950/40 dark:border-blue-700' : 'border-gray-200 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50'">
                                <input type="radio" name="role" value="client" x-model="editUserRole" class="mt-0.5 text-[#0038A8] focus:ring-[#0038A8]">
                                <div class="ml-3">
                                    <span class="block text-xs font-bold text-gray-900 dark:text-white">Client</span>
                                    <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Regular service requestor with tracking access</span>
                                </div>
                            </label>

                            <!-- Worker Role Option -->
                            <label class="flex items-start p-3.5 border rounded-xl cursor-pointer transition"
                                   :class="editUserRole === 'worker' ? 'border-[#0038A8] bg-blue-50/50 dark:bg-blue-950/40 dark:border-blue-700' : 'border-gray-200 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50'">
                                <input type="radio" name="role" value="worker" x-model="editUserRole" class="mt-0.5 text-[#0038A8] focus:ring-[#0038A8]">
                                <div class="ml-3">
                                    <span class="block text-xs font-bold text-gray-900 dark:text-white">Worker</span>
                                    <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Field staff assigned to job orders & maintenance</span>
                                </div>
                            </label>

                            <!-- Administrator Role Option -->
                            <label class="flex items-start p-3.5 border rounded-xl cursor-pointer transition"
                                   :class="editUserRole === 'admin' ? 'border-[#0038A8] bg-blue-50/50 dark:bg-blue-950/40 dark:border-blue-700' : 'border-gray-200 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50'">
                                <input type="radio" name="role" value="admin" x-model="editUserRole" class="mt-0.5 text-[#0038A8] focus:ring-[#0038A8]">
                                <div class="ml-3">
                                    <span class="block text-xs font-bold text-gray-900 dark:text-white">Administrator</span>
                                    <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Full access to system management & configuration</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer Buttons -->
                <div class="p-4 bg-gray-50 dark:bg-zinc-900/60 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-end gap-3">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 font-semibold text-xs rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-700 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-[#0038A8] hover:bg-[#002B82] text-white font-semibold text-xs rounded-lg shadow-xs transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function initUserCharts() {
        if (typeof Chart === 'undefined') {
            setTimeout(initUserCharts, 100);
            return;
        }

        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? '#27272a' : '#f3f4f6';

        // 1. User Distribution (Donut / Circle Chart)
        const donutCanvas = document.getElementById('userDonut');
        if (donutCanvas) {
            new Chart(donutCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Clients', 'Administrators', 'Workers'],
                    datasets: [{
                        data: [{{ $totalClients }}, {{ $totalAdmins }}, {{ $totalWorkers }}],
                        backgroundColor: ['#3b82f6', '#f59e0b', '#10b981'],
                        hoverBackgroundColor: ['#2563eb', '#d97706', '#059669'],
                        borderWidth: 2,
                        borderColor: isDark ? '#1c1c1e' : '#ffffff',
                    }]
                },
                options: {
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                color: textColor,
                                font: { size: 12, family: 'Inter', weight: 600 },
                                padding: 16
                            }
                        },
                        tooltip: {
                            backgroundColor: isDark ? '#27272a' : '#1e293b',
                            titleColor: '#ffffff',
                            bodyColor: '#e2e8f0',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(ctx) {
                                    const total = {{ $totalUsers > 0 ? $totalUsers : 1 }};
                                    const val = ctx.raw || 0;
                                    const pct = Math.round((val / total) * 100);
                                    return ` ${ctx.label}: ${val} (${pct}%)`;
                                }
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        }

        // 2. User Registration Trends (Bar Chart)
        const trendCanvas = document.getElementById('trendBar');
        if (trendCanvas) {
            const weeklyData = {
                labels: {!! json_encode(array_keys($weeklyTrends ?? ['Mon'=>0,'Tue'=>0,'Wed'=>0,'Thu'=>0,'Fri'=>0,'Sat'=>0,'Sun'=>0])) !!},
                data: {!! json_encode(array_values($weeklyTrends ?? [0,0,0,0,0,0,0])) !!}
            };

            const monthlyData = {
                labels: {!! json_encode(array_keys($monthlyTrends ?? ['Week 1'=>0,'Week 2'=>0,'Week 3'=>0,'Week 4'=>0,'Week 5'=>0])) !!},
                data: {!! json_encode(array_values($monthlyTrends ?? [0,0,0,0,0])) !!}
            };

            const yearlyData = {
                labels: {!! json_encode(array_keys($yearlyTrends ?? ['Jan'=>0,'Feb'=>0,'Mar'=>0,'Apr'=>0,'May'=>0,'Jun'=>0,'Jul'=>0,'Aug'=>0,'Sep'=>0,'Oct'=>0,'Nov'=>0,'Dec'=>0])) !!},
                data: {!! json_encode(array_values($yearlyTrends ?? [0,0,0,0,0,0,0,0,0,0,0,0])) !!}
            };

            const trendChart = new Chart(trendCanvas, {
                type: 'bar',
                data: {
                    labels: monthlyData.labels,
                    datasets: [{
                        label: 'New Users',
                        data: monthlyData.data,
                        backgroundColor: '#0038A8',
                        hoverBackgroundColor: '#002B82',
                        borderRadius: 6,
                        barPercentage: 0.55,
                        categoryPercentage: 0.7,
                    }]
                },
                options: {
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark ? '#27272a' : '#1e293b',
                            titleColor: '#ffffff',
                            bodyColor: '#e2e8f0',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(ctx) {
                                    return ` New Users: ${ctx.raw}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: textColor,
                                stepSize: 1,
                                font: { size: 11 }
                            },
                            grid: { color: gridColor },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: textColor,
                                font: { size: 11, family: 'Inter', weight: 500 }
                            },
                            border: { display: false }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });

            // Toggle Event Listener for "This Month" / "This Week" / "This Year"
            const periodSelect = document.getElementById('trendPeriodSelect');
            const periodLabel = document.getElementById('trendPeriodLabel');
            if (periodSelect) {
                periodSelect.addEventListener('change', function() {
                    if (this.value === 'week') {
                        trendChart.data.labels = weeklyData.labels;
                        trendChart.data.datasets[0].data = weeklyData.data;
                        if (periodLabel) periodLabel.textContent = 'THIS WEEK (MON - SUN)';
                    } else if (this.value === 'year') {
                        trendChart.data.labels = yearlyData.labels;
                        trendChart.data.datasets[0].data = yearlyData.data;
                        if (periodLabel) periodLabel.textContent = 'YEAR {{ now()->format("Y") }} (JAN - DEC)';
                    } else {
                        trendChart.data.labels = monthlyData.labels;
                        trendChart.data.datasets[0].data = monthlyData.data;
                        if (periodLabel) periodLabel.textContent = '{{ strtoupper(now()->format('F Y')) }} (WEEK 1 - 5)';
                    }
                    trendChart.update();
                });
            }
        }
    }

    initUserCharts();
});
</script>
@endpush
