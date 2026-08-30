@extends('layouts.admin')
@section('page-title', 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@endpush

@section('content')

<!-- Dash Banner -->
<div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-6 sm:px-8 py-5 flex justify-between items-center mb-6 shadow-sm font-sans">
    <div>
        <h1 class="text-[#0033a0] dark:text-blue-400 text-xl sm:text-2xl font-bold mb-1">Dashboard</h1>
        <p class="text-[#0033a0]/80 dark:text-gray-300 text-xs sm:text-sm font-medium">Welcome back, admin!</p>
    </div>
    
    <div class="border-l-2 border-[#1a3c8f] dark:border-blue-500 pl-4 sm:pl-6 text-right">
        <div class="text-[#1a3c8f] dark:text-blue-400 font-bold text-xs sm:text-[15px]">{{ now()->format('F j, Y') }}</div>
        <div class="text-[#1a3c8f] dark:text-gray-300 text-[11px] sm:text-[13px] opacity-90">{{ now()->format('l, h:i A') }}</div>
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

<!-- Real-Time Monitoring & Service Requests Inventory Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mt-6 font-sans">
    
    <!-- Card 1: Real-Time Task Monitoring -->
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
        <div>
            <div class="mb-5">
                <h2 class="text-[#0033a0] dark:text-blue-400 text-lg font-bold leading-tight">Real-Time Task Monitoring</h2>
                <p class="text-xs text-gray-400 font-medium mt-0.5">Overview</p>
            </div>

            <!-- Monitoring Activities Feed -->
            <div class="space-y-3.5 mb-6">
                @forelse($realTimeMonitoring as $act)
                    @php
                        $circleColor = match($act['color'] ?? 'emerald') {
                            'emerald' => 'border-emerald-500 text-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20',
                            'yellow'  => 'border-amber-400 text-amber-400 bg-amber-50/50 dark:bg-amber-950/20',
                            'orange'  => 'border-orange-400 text-orange-400 bg-orange-50/50 dark:bg-orange-950/20',
                            'blue'    => 'border-blue-500 text-blue-500 bg-blue-50/50 dark:bg-blue-950/20',
                            default   => 'border-gray-400 text-gray-400 bg-gray-50/50 dark:bg-zinc-800'
                        };
                    @endphp
                    <div class="flex items-center justify-between gap-3 pb-3 border-b border-gray-100 dark:border-zinc-800/80 last:border-0 last:pb-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-3.5 h-3.5 rounded-full border-2 {{ $circleColor }} shrink-0"></div>
                            <span class="text-xs font-semibold text-slate-800 dark:text-gray-200 truncate">{{ $act['text'] }}</span>
                        </div>
                        <span class="text-[11px] font-medium text-gray-400 dark:text-gray-500 shrink-0 whitespace-nowrap">
                            {{ $act['time']->format('M j, Y - h:i A') }}
                        </span>
                    </div>
                @empty
                    <div class="text-center py-8 text-xs text-gray-400 italic">
                        No recent real-time monitoring events recorded yet.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-zinc-800 flex justify-end">
            <a href="{{ route('admin.audit.index') }}" class="bg-[#0033a0] hover:bg-[#002480] text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center gap-1.5">
                <span>View all activities</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

    <!-- Card 2: Service Requests Inventory -->
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
        <div>
            <div class="mb-4">
                <h2 class="text-[#0033a0] dark:text-blue-400 text-lg font-bold leading-tight">Service Requests Inventory</h2>
                <p class="text-xs text-gray-400 font-medium mt-0.5">Preview of Latest 5 Requests</p>
            </div>

            <!-- Inventory Table -->
            <div class="overflow-x-auto mb-6">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-zinc-800 text-[11px] font-bold text-[#1a3c8f] dark:text-blue-400 uppercase tracking-wider">
                            <th class="pb-2.5 pr-3">Requisition No.</th>
                            <th class="pb-2.5 px-3">Category</th>
                            <th class="pb-2.5 px-3 text-center">Priority</th>
                            <th class="pb-2.5 px-3 text-center">Status</th>
                            <th class="pb-2.5 pl-3 text-right">Date Requested</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/80">
                        @forelse($recentRequests as $req)
                            @php
                                $catName = strtolower($req->category->category_name ?? '');
                                $prefix = match(true) {
                                    str_contains($catName, 'landscaping') => 'LS',
                                    str_contains($catName, 'janitorial') => 'JS',
                                    str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
                                    str_contains($catName, 'plumbing') => 'PLS',
                                    str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
                                    str_contains($catName, 'painting') || str_contains($catName, 'paint') => 'PAINT',
                                    str_contains($catName, 'manpower') || str_contains($catName, 'event') => 'MAN',
                                    default => 'REQ'
                                };
                                $reqCode = $prefix . '-' . str_pad($req->request_id, 3, '0', STR_PAD_LEFT);

                                $prio = strtolower($req->priority ?? 'low');
                                $prioClass = match($prio) {
                                    'high'   => 'bg-red-50 text-red-600 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800',
                                    'medium' => 'bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                    default  => 'bg-yellow-50 text-yellow-700 border-yellow-300 dark:bg-yellow-950/40 dark:text-yellow-300 dark:border-yellow-800'
                                };

                                $st = $req->current_status;
                                $stClass = match($st) {
                                    'Submitted'            => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
                                    'Pending', 'Approved'  => 'bg-orange-50 text-orange-600 border-orange-200 dark:bg-orange-950/40 dark:text-orange-300 dark:border-orange-800',
                                    'In Progress'          => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                    'Pending Verification' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
                                    'Completed'            => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                    default                => 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-zinc-800 dark:text-gray-300 dark:border-zinc-700'
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-zinc-800/40 transition">
                                <td class="py-3 pr-3 font-mono font-bold text-[#0033a0] dark:text-blue-400">
                                    <a href="{{ route('admin.requests.show', $req->request_id) }}" class="hover:underline">
                                        {{ $reqCode }}
                                    </a>
                                </td>
                                <td class="py-3 px-3 font-medium text-slate-800 dark:text-gray-200">
                                    {{ $req->category->category_name ?? 'General' }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $prioClass }}">
                                        {{ ucfirst($req->priority ?? 'Low') }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $stClass }}">
                                        {{ $st }}
                                    </span>
                                </td>
                                <td class="py-3 pl-3 text-right text-gray-500 dark:text-gray-400 font-medium whitespace-nowrap">
                                    {{ $req->submitted_at ? \Carbon\Carbon::parse($req->submitted_at)->format('m/d/Y') : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-xs text-gray-400 italic">
                                    No service requests found in inventory.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-zinc-800 flex justify-end">
            <a href="{{ route('admin.requests.index') }}" class="bg-[#0033a0] hover:bg-[#002480] text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center gap-1.5">
                <span>View all requests</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
