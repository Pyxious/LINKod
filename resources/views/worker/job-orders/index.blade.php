@extends('layouts.worker')
@section('page-title', 'Job Orders')

@section('content')

<!-- Page Banner -->
<div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-5 sm:px-8 py-5 sm:py-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6 shadow-sm">
    <div>
        <h1 class="text-[#0033a0] dark:text-blue-400 text-xl sm:text-2xl font-bold mb-1">Job Orders & Assignments</h1>
        <p class="text-[#0033a0]/80 dark:text-gray-400 text-xs sm:text-sm font-medium">View and manage your assigned maintenance tasks.</p>
    </div>
    <div id="job-orders-sync-indicator" class="hidden bg-amber-50 dark:bg-amber-950/50 text-amber-900 dark:text-amber-200 border border-amber-300 dark:border-amber-700 px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-2 shadow-xs">
        <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
        <span>Offline updates pending sync</span>
    </div>
</div>

<!-- Admin-Style Filtering Control Bar -->
<form method="GET" action="{{ route('worker.job-orders.index') }}" class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-6 font-sans">
    <!-- Priority Toggle Buttons -->
    <div class="flex bg-gray-100 dark:bg-zinc-800/80 p-1 rounded-xl gap-1 w-full md:w-auto shadow-2xs overflow-x-auto">
        <a href="{{ route('worker.job-orders.index', array_merge(request()->query(), ['priority' => ''])) }}"
           class="flex-1 md:flex-initial text-center px-3 py-1.5 text-xs font-bold rounded-lg transition whitespace-nowrap {{ empty($priorityFilter) ? 'bg-white dark:bg-zinc-900 text-[#0038A8] dark:text-blue-400 shadow-2xs' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400' }}">
            All Priorities
        </a>
        <a href="{{ route('worker.job-orders.index', array_merge(request()->query(), ['priority' => 'High'])) }}"
           class="flex-1 md:flex-initial text-center px-3 py-1.5 text-xs font-bold rounded-lg transition whitespace-nowrap {{ $priorityFilter === 'High' ? 'bg-red-50 text-red-600 border border-red-200 shadow-2xs' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400' }}">
            High
        </a>
        <a href="{{ route('worker.job-orders.index', array_merge(request()->query(), ['priority' => 'Medium'])) }}"
           class="flex-1 md:flex-initial text-center px-3 py-1.5 text-xs font-bold rounded-lg transition whitespace-nowrap {{ $priorityFilter === 'Medium' ? 'bg-amber-50 text-amber-700 border border-amber-300 shadow-2xs' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400' }}">
            Medium
        </a>
        <a href="{{ route('worker.job-orders.index', array_merge(request()->query(), ['priority' => 'Low'])) }}"
           class="flex-1 md:flex-initial text-center px-3 py-1.5 text-xs font-bold rounded-lg transition whitespace-nowrap {{ $priorityFilter === 'Low' ? 'bg-emerald-50 text-emerald-700 border border-emerald-300 shadow-2xs' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400' }}">
            Low
        </a>
    </div>

    <!-- Status Filter Dropdown & Search Input -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
        <input type="hidden" name="priority" value="{{ $priorityFilter }}">
        <input type="hidden" name="direction" value="{{ $direction ?? 'asc' }}">

        <!-- Status Filter Dropdown -->
        <select name="status" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl border border-[#0038A8]/30 dark:border-zinc-700 text-[#0038A8] dark:text-blue-400 bg-white dark:bg-zinc-900 text-xs font-bold outline-none cursor-pointer shadow-2xs w-full sm:w-auto">
            <option value="" {{ empty($statusFilter) || $statusFilter === 'active' ? 'selected' : '' }}>Active Tasks Only</option>
            <option value="Completed" {{ $statusFilter === 'Completed' ? 'selected' : '' }}>Completed Tasks</option>
            <option value="In Progress" {{ $statusFilter === 'In Progress' ? 'selected' : '' }}>In Progress</option>
            <option value="Pending" {{ $statusFilter === 'Pending' ? 'selected' : '' }}>Pending</option>
            <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Tasks</option>
        </select>

        <!-- Search Input -->
        <div class="relative flex-1 sm:flex-initial">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#0038A8] dark:text-blue-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search tasks..." onchange="this.form.submit()" class="pl-9 pr-3 py-2 rounded-xl border border-[#0038A8]/30 dark:border-zinc-700 text-[#0038A8] dark:text-blue-300 text-xs font-semibold outline-none w-full sm:w-48 bg-white dark:bg-zinc-900 shadow-2xs">
        </div>
    </div>
</form>

@php
    $inProgressTasks = $assignments->filter(fn($a) => $a->project && $a->project->current_status === 'In Progress');
    
    if ($statusFilter === 'In Progress') {
        $tableTasks = $inProgressTasks;
        $showInProgressBox = false;
    } elseif ($statusFilter === 'Completed' || $statusFilter === 'all' || $statusFilter === 'Pending' || ($sort && $sort !== 'priority')) {
        $tableTasks = $assignments;
        $showInProgressBox = false;
    } else {
        $tableTasks = $assignments->reject(fn($a) => $a->project && $a->project->current_status === 'In Progress');
        $showInProgressBox = $inProgressTasks->isNotEmpty();
    }
@endphp

<!-- Separate Box for Currently In Progress Task(s) -->
@if($showInProgressBox)
    <div class="mb-6 space-y-3 font-sans">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-[#042B74] dark:text-blue-400 font-bold text-sm sm:text-base flex items-center gap-2">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                </span>
                Currently In Progress
            </h2>
            <span class="text-[11px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 px-2.5 py-0.5 rounded-full border border-amber-200 dark:border-amber-800">
                {{ $inProgressTasks->count() }} {{ Str::plural('Task', $inProgressTasks->count()) }} Active
            </span>
        </div>

        @foreach($inProgressTasks as $inProg)
            @php
                $ipReqId = $inProg->project?->request_id;
                $ipCatName = strtolower($inProg->project?->request?->category?->category_name ?? '');
                $ipPrefix = match(true) {
                    str_contains($ipCatName, 'landscaping') => 'LS',
                    str_contains($ipCatName, 'electrical') || str_contains($ipCatName, 'mechanical') => 'EMS',
                    str_contains($ipCatName, 'carpentry') || str_contains($ipCatName, 'masonry') => 'CMS',
                    str_contains($ipCatName, 'plumbing') => 'PLS',
                    default => 'REQ'
                };
                $ipReqCode = $ipReqId ? ($ipPrefix . '-' . str_pad($ipReqId, 3, '0', STR_PAD_LEFT)) : ('REQ-'.str_pad($inProg->project_id, 3, '0', STR_PAD_LEFT));
                $ipPrio = ucfirst(strtolower($inProg->project?->request?->priority ?? 'Low'));
                $ipPrioClass = match($ipPrio) {
                    'High' => 'bg-red-50 text-red-600 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800',
                    'Medium' => 'bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                    default => 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                };
            @endphp
            <div class="bg-white dark:bg-[#1c1c1e] border-2 border-amber-400 dark:border-amber-500/80 rounded-2xl p-5 sm:p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-2 min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="bg-blue-50 dark:bg-blue-950/60 text-[#0038A8] dark:text-blue-300 font-mono font-extrabold px-2.5 py-1 rounded-md border border-blue-200 dark:border-blue-800 text-xs">
                            {{ $ipReqCode }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border {{ $ipPrioClass }}">
                            {{ $ipPrio }} Priority
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            In Progress
                        </span>
                    </div>

                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white leading-snug truncate">
                            {{ $inProg->project->request->title ?? 'Untitled Job Order' }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ $inProg->project->request->location ?? 'Location N/A' }}</span>
                            <span class="text-gray-400">•</span>
                            <span>Assigned: {{ \Carbon\Carbon::parse($inProg->date_assigned)->format('M d, Y') }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('worker.job-orders.show', $inProg->project_id) }}" 
                       class="w-full sm:w-auto px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold shadow-sm transition inline-flex items-center justify-center gap-2">
                        <span>Continue Task</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif

<!-- Queue / Assignments Container -->
<div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xs overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-900/40 flex justify-between items-center">
        <h3 class="text-[#042B74] dark:text-blue-400 font-bold text-sm sm:text-base">
            @if($statusFilter === 'Completed')
                Completed Job Orders
            @elseif($statusFilter === 'In Progress')
                In Progress Tasks
            @elseif($statusFilter === 'all')
                All Assigned Tasks
            @else
                Task Queue
            @endif
        </h3>
        @if(empty($statusFilter) || $statusFilter === 'active')
            <span class="text-[11px] sm:text-xs font-semibold text-gray-500 dark:text-gray-400 bg-blue-50 dark:bg-blue-950/50 px-2.5 py-1 rounded-md border border-blue-200 dark:border-blue-800">
                {{ $tableTasks->count() }} Queued
            </span>
        @elseif($statusFilter === 'Completed')
            <span class="text-[11px] sm:text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 px-2.5 py-1 rounded-md border border-emerald-200 dark:border-emerald-800">
                Completed Tasks
            </span>
        @endif
    </div>

    <!-- Mobile Card View (visible only on mobile < md screens) -->
    <div class="block md:hidden divide-y divide-gray-100 dark:divide-zinc-800/80">
        @forelse($tableTasks as $a)
            @php
                $reqId = $a->project?->request_id;
                $catName = strtolower($a->project?->request?->category?->category_name ?? '');
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
                $reqCode = $reqId ? ($prefix . '-' . str_pad($reqId, 3, '0', STR_PAD_LEFT)) : ('REQ-'.str_pad($a->project_id, 3, '0', STR_PAD_LEFT));
                $prio = ucfirst(strtolower($a->project?->request?->priority ?? 'Low'));
                $prioClass = match($prio) {
                    'High' => 'bg-red-50 text-red-600 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800',
                    'Medium' => 'bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                    default => 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                };
            @endphp

            <div class="p-4 space-y-3">
                <!-- Top Line: Queue & Code & Priority badge -->
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-gray-200 dark:bg-zinc-800 text-gray-700 dark:text-gray-300">
                            #{{ $loop->iteration }}
                        </span>
                        <span class="bg-blue-50 dark:bg-blue-950/60 text-[#0038A8] dark:text-blue-300 font-mono font-extrabold px-2.5 py-1 rounded-md border border-blue-200 dark:border-blue-800 text-xs">
                            {{ $reqCode }}
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border {{ $prioClass }}">
                            {{ $prio }} Priority
                        </span>
                    </div>
                </div>

                <!-- Title & Location -->
                <div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">
                        {{ $a->project->request->title ?? 'Untitled Job Order' }}
                    </h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>{{ $a->project->request->location ?? 'Location N/A' }}</span>
                    </p>
                </div>

                <!-- Bottom Line: Status, Date & Open Button -->
                <div class="border-t border-gray-100 dark:border-zinc-800/60 pt-3 flex items-center justify-between gap-2">
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                            @if($a->project->current_status === 'Pending') bg-red-50 text-red-700 border border-red-200
                            @elseif($a->project->current_status === 'In Progress') bg-amber-50 text-amber-700 border border-amber-200
                            @elseif($a->project->current_status === 'Completed') bg-emerald-50 text-emerald-700 border border-emerald-200
                            @else bg-blue-50 text-blue-700 border border-blue-200 @endif">
                            {{ $a->project->current_status }}
                        </span>
                        <div class="text-[10px] text-gray-400 font-medium mt-1">
                            Assigned: {{ \Carbon\Carbon::parse($a->date_assigned)->format('M d, Y') }}
                        </div>
                    </div>

                    <a href="{{ route('worker.job-orders.show', $a->project_id) }}" 
                       class="px-4 py-2.5 bg-[#0038A8] hover:bg-[#002480] text-white rounded-xl text-xs font-bold shadow-sm transition inline-flex items-center gap-1 shrink-0">
                        <span>Open Task</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="px-6 py-10 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-zinc-800 mb-3 text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-xs font-bold text-gray-900 dark:text-white mb-1">No Job Orders in Queue</h3>
                <p class="text-xs text-gray-400">
                    @if($statusFilter === 'Completed')
                        You have no completed job orders recorded.
                    @else
                        No pending task assignments in your queue.
                    @endif
                </p>
            </div>
        @endforelse
    </div>
    
    <!-- Desktop Table View (hidden on mobile < md, visible on desktop >= md) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/80 dark:bg-zinc-900/60 border-b border-gray-200 dark:border-zinc-800 text-[#042B74] dark:text-blue-400 text-xs uppercase tracking-wider font-bold">
                    <th class="px-6 py-4">Queue #</th>
                    <th class="px-6 py-4">
                        <a href="{{ route('worker.job-orders.index', array_merge(request()->query(), ['sort' => 'req_no', 'direction' => (($sort === 'req_no' && ($direction ?? 'asc') === 'asc') ? 'desc' : 'asc')])) }}" class="inline-flex items-center gap-1.5 hover:text-blue-600 transition">
                            <span>Requisition No.</span>
                            @if(($sort ?? '') === 'req_no')
                                <span class="text-blue-600 font-extrabold">{{ ($direction ?? 'asc') === 'asc' ? '▲' : '▼' }}</span>
                            @else
                                <span class="text-gray-300 dark:text-zinc-600">↕</span>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4">
                        <a href="{{ route('worker.job-orders.index', array_merge(request()->query(), ['sort' => ($sort === 'title_asc' ? 'title_desc' : 'title_asc')])) }}" class="inline-flex items-center gap-1.5 hover:text-blue-600 transition">
                            <span>Title / Location</span>
                            @if(($sort ?? '') === 'title_asc')
                                <span class="text-blue-600 font-extrabold">▲</span>
                            @elseif(($sort ?? '') === 'title_desc')
                                <span class="text-blue-600 font-extrabold">▼</span>
                            @else
                                <span class="text-gray-300 dark:text-zinc-600">↕</span>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4">
                        <a href="{{ route('worker.job-orders.index', array_merge(request()->query(), ['sort' => 'priority', 'direction' => (($sort === 'priority' && ($direction ?? 'asc') === 'asc') ? 'desc' : 'asc')])) }}" class="inline-flex items-center gap-1.5 hover:text-blue-600 transition">
                            <span>Priority</span>
                            @if(($sort ?? 'priority') === 'priority')
                                <span class="text-blue-600 font-extrabold">{{ ($direction ?? 'asc') === 'asc' ? '▲' : '▼' }}</span>
                            @else
                                <span class="text-gray-300 dark:text-zinc-600">↕</span>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4">
                        <a href="{{ route('worker.job-orders.index', array_merge(request()->query(), ['sort' => ($sort === 'date_asc' ? 'date_desc' : 'date_asc')])) }}" class="inline-flex items-center gap-1.5 hover:text-blue-600 transition">
                            <span>Assigned Date</span>
                            @if(($sort ?? '') === 'date_asc')
                                <span class="text-blue-600 font-extrabold">▲</span>
                            @elseif(($sort ?? '') === 'date_desc')
                                <span class="text-blue-600 font-extrabold">▼</span>
                            @else
                                <span class="text-gray-300 dark:text-zinc-600">↕</span>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4">
                        <a href="{{ route('worker.job-orders.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => (($sort === 'status' && ($direction ?? 'asc') === 'asc') ? 'desc' : 'asc')])) }}" class="inline-flex items-center gap-1.5 hover:text-blue-600 transition">
                            <span>Status</span>
                            @if(($sort ?? '') === 'status')
                                <span class="text-blue-600 font-extrabold">{{ ($direction ?? 'asc') === 'asc' ? '▲' : '▼' }}</span>
                            @else
                                <span class="text-gray-300 dark:text-zinc-600">↕</span>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-xs">
                @forelse($tableTasks as $a)
                    @php
                        $reqId = $a->project?->request_id;
                        $catName = strtolower($a->project?->request?->category?->category_name ?? '');
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
                        $reqCode = $reqId ? ($prefix . '-' . str_pad($reqId, 3, '0', STR_PAD_LEFT)) : ('REQ-'.str_pad($a->project_id, 3, '0', STR_PAD_LEFT));
                        $prio = ucfirst(strtolower($a->project?->request?->priority ?? 'Low'));
                        $prioClass = match($prio) {
                            'High' => 'bg-red-50 text-red-600 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800',
                            'Medium' => 'bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                            default => 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/70 dark:hover:bg-zinc-800/50 transition group">
                        <!-- Queue Number -->
                        <td class="px-6 py-4 font-bold text-gray-500 dark:text-gray-400">
                            <span class="px-2 py-0.5 rounded text-[11px] font-extrabold bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300">
                                #{{ $loop->iteration }}
                            </span>
                        </td>

                        <!-- Requisition Code -->
                        <td class="px-6 py-4 font-extrabold text-[#0038A8] dark:text-blue-300 font-mono text-xs">
                            <span class="bg-blue-50 dark:bg-blue-950/60 px-2 py-1 rounded-md border border-blue-200 dark:border-blue-800">
                                {{ $reqCode }}
                            </span>
                        </td>

                        <!-- Title / Location -->
                        <td class="px-6 py-4">
                            <div class="text-xs font-bold text-gray-900 dark:text-white">
                                {{ $a->project->request->title ?? 'Untitled Job Order' }}
                            </div>
                            <div class="text-[11px] text-gray-400 mt-0.5 truncate max-w-xs">{{ $a->project->request->location ?? 'Location N/A' }}</div>
                        </td>

                        <!-- Priority Badge -->
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-extrabold border {{ $prioClass }}">
                                {{ $prio }} Priority
                            </span>
                        </td>

                        <!-- Date -->
                        <td class="px-6 py-4 text-xs font-medium text-gray-600 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($a->date_assigned)->format('M d, Y') }}
                        </td>

                        <!-- Status Badge -->
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold 
                                @if($a->project->current_status === 'Pending') bg-red-50 text-red-700 border border-red-200
                                @elseif($a->project->current_status === 'In Progress') bg-amber-50 text-amber-700 border border-amber-200
                                @elseif($a->project->current_status === 'Completed') bg-emerald-50 text-emerald-700 border border-emerald-200
                                @else bg-blue-50 text-blue-700 border border-blue-200 @endif">
                                {{ $a->project->current_status }}
                            </span>
                        </td>

                        <!-- Action -->
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('worker.job-orders.show', $a->project_id) }}" class="inline-flex items-center justify-center px-4 py-2 border border-[#0038A8] text-[#0038A8] dark:text-blue-300 hover:bg-[#0038A8] hover:text-white dark:hover:bg-blue-600 rounded-lg text-xs font-bold transition shadow-2xs">
                                Open Task
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-zinc-800 mb-3 text-gray-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white mb-1">No Job Orders in Queue</h3>
                            <p class="text-xs text-gray-400">
                                @if($statusFilter === 'Completed')
                                    You have no completed job orders recorded.
                                @else
                                    No pending task assignments in your queue.
                                @endif
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

