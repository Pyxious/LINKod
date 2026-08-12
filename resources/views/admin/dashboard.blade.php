@extends('layouts.admin')
@section('page-title', 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@endpush

@section('content')

<!-- Dash Banner -->
<div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-8 py-6 flex justify-between items-center mb-6 shadow-sm font-sans" x-data="{ openNotifs: false }">
    <div>
        <h1 class="text-[#0033a0] dark:text-blue-400 text-2xl font-bold mb-1">Dashboard</h1>
        <p class="text-[#0033a0]/80 dark:text-gray-300 text-sm font-medium">Welcome back, admin!</p>
    </div>
    
    <div class="flex items-center gap-6">
        <!-- Interactive Notification Bell & Dropdown Tab -->
        <div class="relative">
            <button @click="openNotifs = !openNotifs" 
                    type="button" 
                    class="relative bg-[#1a3c8f] hover:bg-[#152e6e] text-white w-11 h-11 rounded-full flex items-center justify-center transition shadow-md focus:outline-none">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if(($unreadCount ?? 0) > 0)
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-extrabold px-1.5 py-0.5 rounded-full border-2 border-[#fefce8] animate-pulse">
                        {{ $unreadCount }}
                    </span>
                @endif
            </button>

            <!-- Notifications Dropdown Popover Tab -->
            <div x-show="openNotifs" 
                 @click.outside="openNotifs = false" 
                 x-transition 
                 x-cloak 
                 class="absolute right-0 mt-3 w-80 sm:w-96 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden z-50">
                
                <!-- Popover Header -->
                <div class="px-5 py-4 border-b border-gray-100 dark:border-zinc-800 flex justify-between items-center bg-[#f8faff] dark:bg-zinc-800/50">
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-sm text-[#0033a0] dark:text-blue-400 uppercase tracking-wider">Notifications</span>
                        @if(($unreadCount ?? 0) > 0)
                            <span class="px-2 py-0.5 text-[10px] font-black bg-red-100 text-red-700 rounded-full">
                                {{ $unreadCount }} New
                            </span>
                        @endif
                    </div>

                    @if(($unreadCount ?? 0) > 0)
                        <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
                            @csrf
                            <button type="submit" class="text-[11px] font-bold text-[#0033a0] dark:text-blue-400 hover:underline">
                                Mark all as read
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Notifications List -->
                <div class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-zinc-800">
                    @forelse($notifications ?? [] as $notif)
                        <a href="{{ route('admin.notifications.read', $notif->notification_id) }}" 
                           class="block px-5 py-3.5 hover:bg-blue-50/60 dark:hover:bg-zinc-800/60 transition {{ !$notif->is_read ? 'bg-blue-50/40 dark:bg-zinc-800/30' : '' }}">
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 mt-1.5 rounded-full shrink-0 {{ !$notif->is_read ? 'bg-[#0033a0]' : 'bg-gray-300' }}"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-baseline gap-2 mb-0.5">
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                            {{ $notif->title }}
                                        </h4>
                                        <span class="text-[10px] text-gray-400 shrink-0">
                                            {{ \Carbon\Carbon::parse($notif->sent_at)->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-snug line-clamp-2">
                                        {{ $notif->message }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="py-10 text-center text-gray-400 text-xs italic">
                            No notifications yet.
                        </div>
                    @endforelse
                </div>

            </div>
        </div>

        <div class="border-l-2 border-[#1a3c8f] pl-6">
            <div class="text-[#1a3c8f] dark:text-blue-400 font-bold text-[15px]">{{ now()->format('F j, Y') }}</div>
            <div class="text-[#1a3c8f] dark:text-gray-300 text-[13px] opacity-90">{{ now()->format('l, h:i A') }}</div>
        </div>
    </div>
</div>

<!-- KPI Grid (Functional Subtexts) -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 font-sans">
    <!-- Card 1: Total Requests -->
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Total Requests</div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none mb-2">{{ $totalRequests }}</div>
        <div class="text-xs font-medium text-gray-500">
            <span class="text-emerald-600 font-bold">↑ +{{ $requestsToday }}</span> today
        </div>
    </div>

    <!-- Card 2: Active Tasks -->
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Active Tasks</div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none mb-2">{{ $activeTasks }}</div>
        <div class="text-xs font-medium text-gray-500">
            <span class="text-emerald-600 font-bold">{{ $activeTasks }}</span> in progress
        </div>
    </div>

    <!-- Card 3: Available Workers -->
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Available Workers</div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none mb-2">{{ $availableWorkers }}</div>
        <div class="text-xs font-medium text-gray-500">
            <span class="text-emerald-600 font-bold">{{ $availablePct }}%</span> of {{ $totalWorkers }} workers
        </div>
    </div>

    <!-- Card 4: Completion Rate -->
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Completion Rate</div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none mb-2">{{ $completionRate }}%</div>
        <div class="text-xs font-medium text-gray-500">
            <span class="text-emerald-600 font-bold">{{ $completedThisMonth }}</span> completed this month
        </div>
    </div>
</div>

<!-- Chart Section -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 font-sans">
    <!-- Task Status -->
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm">
        <div class="flex justify-between items-start mb-5">
            <div>
                <div class="text-[#1a3c8f] dark:text-blue-400 text-lg font-bold">Task Status</div>
                <div class="text-xs text-gray-500">Overview</div>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="relative w-[180px] h-[180px]">
                <canvas id="taskDonut"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <div class="text-[11px] text-gray-500 font-semibold">Total Tasks</div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white">{{ array_sum($taskStatus) }}</div>
                </div>
            </div>
            <div class="space-y-2 flex-1 text-xs">
                @foreach(['Pending' => '#facc15', 'On Hold' => '#f97316', 'In Progress' => '#eab308', 'Pending Verification' => '#94a3b8', 'Completed' => '#4ade80', 'Cancelled' => '#f87171'] as $st => $c)
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $c }};"></span>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $st }}</span>
                        </div>
                        <span class="font-extrabold text-slate-900 dark:text-white">{{ $taskStatus[$st] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Client Request Progress -->
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm">
        <div class="flex justify-between items-start mb-5">
            <div>
                <div class="text-[#1a3c8f] dark:text-blue-400 text-lg font-bold">Client Request Progress</div>
                <div class="text-xs text-gray-500">Overview</div>
            </div>
            <span class="text-xs text-gray-400 font-semibold bg-gray-100 dark:bg-zinc-800 px-3 py-1 rounded-md">This Month</span>
        </div>

        <div class="grid grid-cols-5 gap-2 text-center my-6">
            @foreach(['Submitted' => '#1e40af', 'Approved' => '#f87171', 'On Hold' => '#f97316', 'In Progress' => '#eab308', 'Completed' => '#4ade80'] as $st => $col)
                @php $cnt = $requestProgress[$st] ?? 0; @endphp
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold mb-2 shadow-xs" style="background-color: {{ $col }};">
                        {{ $cnt }}
                    </div>
                    <div class="text-[11px] font-bold text-slate-800 dark:text-gray-200 mb-0.5">{{ $st }}</div>
                    <div class="text-[10px] text-gray-400">
                        {{ $totalRequests > 0 ? round(($cnt / $totalRequests) * 100, 1) : 0 }}%
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-zinc-800 flex justify-between items-center text-xs">
            <span class="font-bold text-gray-500">Total Requests: <strong class="text-slate-900 dark:text-white">{{ $totalRequests }}</strong></span>
            <a href="{{ route('admin.requests.index') }}" class="text-[#1a3c8f] dark:text-blue-400 font-bold hover:underline">
                View all requests &gt;
            </a>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Donut Chart
new Chart(document.getElementById('taskDonut'), {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'On Hold', 'In Progress', 'Pending Verification', 'Completed', 'Cancelled'],
        datasets: [{
            data: [
                {{ $taskStatus['Pending'] ?? 0 }},
                {{ $taskStatus['On Hold'] ?? 0 }},
                {{ $taskStatus['In Progress'] ?? 0 }},
                {{ $taskStatus['Pending Verification'] ?? 0 }},
                {{ $taskStatus['Completed'] ?? 0 }},
                {{ $taskStatus['Cancelled'] ?? 0 }}
            ],
            backgroundColor: ['#facc15', '#f97316', '#eab308', '#94a3b8', '#4ade80', '#f87171'],
            borderWidth: 0,
        }]
    },
    options: {
        cutout: '70%',
        plugins: { legend: { display: false } },
        responsive: true,
        maintainAspectRatio: false,
    }
});
</script>
@endpush
