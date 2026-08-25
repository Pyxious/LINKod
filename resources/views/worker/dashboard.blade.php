@extends('layouts.worker')
@section('page-title', 'Dashboard')

@section('content')

@php
    $pendingCount = $assignments->filter(fn($a) => $a->project && $a->project->current_status === 'Pending')->count();
    $inProgressCount = $assignments->filter(fn($a) => $a->project && $a->project->current_status === 'In Progress')->count();
    $completedCount = $assignments->filter(fn($a) => $a->project && $a->project->current_status === 'Completed')->count();
    $worker = auth()->user()->staff?->worker;
    $teamName = $worker?->team?->team_name ?? 'General Services Office';
@endphp

<!-- Dash Banner -->
<div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-5 sm:px-8 py-5 flex justify-between items-center mb-6 shadow-sm font-sans">
    <div>
        <h1 class="text-[#0033a0] dark:text-blue-400 text-xl sm:text-2xl font-bold mb-1">Welcome back, {{ auth()->user()->first_name }}!</h1>
        <p class="text-[#0033a0]/80 dark:text-gray-300 text-xs sm:text-sm font-medium">{{ $teamName }} &bull; General Services Office</p>
    </div>
    
    <div class="border-l-2 border-[#1a3c8f] dark:border-blue-500 pl-4 sm:pl-6 text-right">
        <div class="text-[#1a3c8f] dark:text-blue-400 font-bold text-xs sm:text-[15px]">{{ now()->format('F j, Y') }}</div>
        <div class="text-[#1a3c8f] dark:text-gray-300 text-[11px] sm:text-[13px] opacity-90">{{ now()->format('l, h:i A') }}</div>
    </div>
</div>

<!-- KPI Grid -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-4 mb-6 font-sans">
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm flex sm:block justify-between items-center">
        <div>
            <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-1 sm:mb-2">Pending Tasks</div>
            <div class="text-xs text-gray-500"><span class="text-red-500 font-bold">Needs Attention</span></div>
        </div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none sm:mt-2">{{ $pendingCount }}</div>
    </div>

    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm flex sm:block justify-between items-center">
        <div>
            <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-1 sm:mb-2">In Progress</div>
            <div class="text-xs text-gray-500"><span class="text-amber-500 font-bold">Currently working</span></div>
        </div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none sm:mt-2">{{ $inProgressCount }}</div>
    </div>

    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm flex sm:block justify-between items-center">
        <div>
            <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-1 sm:mb-2">Completed Today</div>
            <div class="text-xs text-gray-500"><span class="text-emerald-500 font-bold">Great job!</span></div>
        </div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none sm:mt-2">{{ $completedCount }}</div>
    </div>
</div>

<!-- Task List Area -->
<div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm mb-6 font-sans overflow-hidden">
    <div class="px-5 sm:px-6 py-4 sm:py-5 border-b border-gray-100 dark:border-zinc-800 flex justify-between items-center bg-gray-50/50 dark:bg-zinc-900/40">
        <h3 class="text-[#1a3c8f] dark:text-blue-400 font-bold text-base sm:text-lg">Your Assignments</h3>
        <a href="{{ route('worker.job-orders.index') }}" class="text-xs sm:text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline transition">View All →</a>
    </div>
    <div class="p-4 sm:p-6">
        <div class="space-y-3">
            @forelse($assignments->take(5) as $a)
                @php
                    $reqId = $a->project?->request_id;
                    $catName = strtolower($a->project?->request?->category?->category_name ?? '');
                    $prefix = match(true) {
                        str_contains($catName, 'landscaping') => 'LS',
                        str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
                        str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
                        str_contains($catName, 'plumbing') => 'PLS',
                        default => 'REQ'
                    };
                    $reqCode = $reqId ? ($prefix . '-' . str_pad($reqId, 3, '0', STR_PAD_LEFT)) : ('REQ-'.str_pad($a->project_id, 3, '0', STR_PAD_LEFT));
                    $prio = ucfirst(strtolower($a->project?->request?->priority ?? 'Low'));
                    $isHighPrio = strtolower($prio) === 'high';
                    $prioClass = match($prio) {
                        'High' => 'bg-red-50 text-red-600 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800',
                        'Medium' => 'bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                        default => 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                    };
                @endphp
                <div class="border border-gray-200 dark:border-zinc-800 rounded-xl p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition group {{ $isHighPrio ? 'bg-red-50/20 dark:bg-red-950/10' : '' }}">
                    <div class="flex items-start sm:items-center gap-3 sm:gap-4 min-w-0 w-full sm:w-auto">
                        <!-- Status Dot -->
                        <div class="w-3 h-3 rounded-full shrink-0 mt-1 sm:mt-0
                            @if($a->project->current_status === 'Pending') bg-red-500
                            @elseif($a->project->current_status === 'In Progress') bg-amber-500
                            @else bg-emerald-500 @endif">
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300">
                                    #{{ $loop->iteration }}
                                </span>
                                <span class="text-[#0038A8] dark:text-blue-300 font-extrabold bg-blue-50 dark:bg-blue-950/60 px-2 py-0.5 rounded border border-blue-200 dark:border-blue-800 text-[11px] font-mono">
                                    {{ $reqCode }}
                                </span>
                                <h4 class="text-slate-900 dark:text-white font-bold text-sm group-hover:text-[#0038A8] transition truncate">
                                    {{ $a->project->request->title ?? 'Service Requisition' }}
                                </h4>
                                <span class="px-2 py-0.2 rounded-full text-[10px] font-extrabold border {{ $prioClass }}">
                                    {{ $prio }}
                                </span>

                            </div>
                            <div class="text-xs text-gray-500 flex items-center gap-2 flex-wrap">
                                <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ \Carbon\Carbon::parse($a->date_assigned)->format('M d, Y') }}</span>
                                <span>&bull;</span>
                                <span class="font-bold text-gray-700 dark:text-gray-300">{{ $a->project->current_status }}</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('worker.job-orders.show', $a->project->project_id) }}" class="w-full sm:w-auto text-center bg-white dark:bg-zinc-800 border border-[#0038A8]/30 dark:border-zinc-700 text-[#0038A8] dark:text-blue-300 hover:bg-[#0038A8] hover:text-white dark:hover:bg-blue-600 px-4 py-2 rounded-lg text-xs font-bold transition shadow-xs shrink-0 mt-1 sm:mt-0">
                        View Assignment
                    </a>
                </div>

            @empty
                <div class="text-center py-10">
                    <div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-bold mb-1">No active assignments</h3>
                    <p class="text-sm text-gray-500">You're all caught up! Enjoy your break.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
