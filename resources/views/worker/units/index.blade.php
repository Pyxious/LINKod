@extends('layouts.worker')
@section('page-title', 'My Unit')

@section('content')

<!-- Header Banner -->
<div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-6 sm:px-8 py-6 mb-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Units / Sections</h1>
        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">Your assigned unit and workforce deployment information</p>
    </div>
    <div class="shrink-0">
        <span class="px-3.5 py-1.5 bg-[#0033a0] text-white text-xs font-extrabold uppercase tracking-wider rounded-xl shadow-xs inline-flex items-center gap-1.5">
            {{ $team->team_name }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- 1. Assigned Unit Info -->
    <div class="space-y-3">
        <h2 class="text-xs font-extrabold uppercase tracking-wider text-[#0033a0] dark:text-blue-400">My Assigned Unit</h2>
        
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm space-y-6">
            <!-- Unit Header -->
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 bg-blue-50 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-400 rounded-xl flex items-center justify-center font-bold text-lg shrink-0 border border-blue-100 dark:border-blue-900">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base sm:text-lg leading-tight truncate">{{ $team->team_name }}</h3>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-0.5">Facilities Maintenance Section</p>
                </div>
            </div>

            <!-- Unit Metrics -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-blue-50/50 dark:bg-zinc-800/40 p-4 rounded-xl border border-blue-100/60 dark:border-zinc-700/60 text-center">
                    <div class="text-2xl sm:text-3xl font-black text-[#0033a0] dark:text-blue-400">{{ $activeRequestsCount }}</div>
                    <div class="text-[10.5px] text-gray-500 dark:text-gray-400 uppercase tracking-wider font-extrabold mt-1">Active Requests</div>
                </div>
                <div class="bg-emerald-50/50 dark:bg-zinc-800/40 p-4 rounded-xl border border-emerald-100/60 dark:border-zinc-700/60 text-center">
                    <div class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $availableWorkersCount }}</div>
                    <div class="text-[10.5px] text-gray-500 dark:text-gray-400 uppercase tracking-wider font-extrabold mt-1">Available Workers</div>
                </div>
            </div>

            <!-- Team Composition Summary -->
            <div class="border-t border-gray-100 dark:border-zinc-800 pt-4 space-y-2 text-xs">
                <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                    <span>Total Team Roster</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $team->workers->count() }} Workers</span>
                </div>
                <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                    <span>Assigned Section</span>
                    <span class="font-bold text-slate-900 dark:text-white">GSO Facilities</span>
                </div>
            </div>

            <!-- Action Link -->
            <a href="{{ route('worker.job-orders.index') }}" class="block w-full py-2.5 px-4 bg-[#0033a0] hover:bg-[#002480] text-white font-bold text-xs text-center rounded-xl shadow-sm transition">
                View Job Orders
            </a>
        </div>
    </div>

    <!-- 2. Team Leader and Members -->
    <div class="space-y-3">
        <h2 class="text-xs font-extrabold uppercase tracking-wider text-[#0033a0] dark:text-blue-400">Team Leader and Members</h2>
        
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm min-h-[250px] flex flex-col">
            <!-- Leader Section -->
            @if($team->leader && $team->leader->staff)
                <div class="bg-blue-50/50 dark:bg-zinc-800/50 border border-blue-100 dark:border-zinc-700/80 rounded-xl p-3.5 flex items-center gap-3.5 mb-4 shadow-2xs">
                    <div class="w-10 h-10 bg-[#0033a0] text-white rounded-xl flex items-center justify-center font-black text-sm shrink-0 shadow-xs">
                        {{ strtoupper(substr($team->leader->staff->user->first_name ?? 'L', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <div class="font-bold text-slate-900 dark:text-white text-sm truncate">
                                {{ $team->leader->staff->user->first_name }} {{ $team->leader->staff->user->last_name }}
                            </div>
                            <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/60 text-[#0033a0] dark:text-blue-300 rounded text-[10px] font-extrabold uppercase tracking-wider shrink-0">
                                Leader
                            </span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Team Unit Supervisor</div>
                    </div>
                </div>
            @endif

            <!-- Members List -->
            <div class="space-y-2.5 flex-1 overflow-y-auto max-h-[380px] pr-1">
                @foreach($team->workers as $w)
                    <div class="p-3 rounded-xl border border-gray-100 dark:border-zinc-800 bg-gray-50/40 dark:bg-zinc-800/30 hover:bg-gray-50 dark:hover:bg-zinc-800/60 transition flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 bg-gray-200 dark:bg-zinc-700 text-slate-700 dark:text-gray-200 rounded-lg flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($w->staff->user->first_name ?? 'W', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm truncate">
                                    {{ $w->staff->user->first_name ?? '' }} {{ $w->staff->user->last_name ?? '' }}
                                </div>
                                @php
                                    $activeProject = $w->projects->first();
                                    $taskTitle = $activeProject?->request?->title ?? ($activeProject?->request?->category?->category_name ?? null);
                                @endphp
                                @if(!$w->is_available && ($taskTitle || $activeProject))
                                    <div class="text-[11px] text-[#0033a0] dark:text-blue-400 font-semibold truncate max-w-[150px] sm:max-w-[190px]" title="{{ $taskTitle }}">
                                        Assigned: {{ $taskTitle ?? 'Project #'.$activeProject->project_id }}
                                    </div>
                                @else
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">Skilled Worker</div>
                                @endif
                            </div>
                        </div>

                        @if(!$w->is_available)
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shrink-0">
                                Busy
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shrink-0">
                                Available
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 3. Current Deployments -->
    <div class="space-y-3">
        <h2 class="text-xs font-extrabold uppercase tracking-wider text-[#0033a0] dark:text-blue-400">Current Deployments</h2>
        
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm min-h-[250px] flex flex-col justify-between">
            <div class="space-y-2.5 overflow-y-auto max-h-[420px] pr-1">
                @forelse($deployments as $project)
                    @php
                        $catName = strtolower($project->request->category->category_name ?? '');
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
                        $reqCode = $project->request_id ? ($prefix . '-' . str_pad($project->request_id, 3, '0', STR_PAD_LEFT)) : ('PROJ-' . $project->project_id);
                        $status = $project->current_status ?? 'Pending';
                    @endphp

                    <div class="border border-gray-100 dark:border-zinc-800 rounded-xl p-3.5 bg-gray-50/40 dark:bg-zinc-800/30 hover:bg-blue-50/30 dark:hover:bg-blue-950/20 hover:border-blue-200 dark:hover:border-blue-900 transition group">
                        <div class="flex justify-between items-start gap-2 mb-2">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-[11px] font-black font-mono text-[#0033a0] dark:text-blue-400">{{ $reqCode }}</span>
                                    <span class="text-gray-300 dark:text-zinc-600">•</span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium truncate">{{ $project->request->category->category_name ?? 'Maintenance' }}</span>
                                </div>
                                <div class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm leading-snug group-hover:text-[#0033a0] dark:group-hover:text-blue-400 transition truncate" title="{{ $project->request->title ?? 'Project #'.$project->project_id }}">
                                    {{ $project->request->title ?? 'Project #'.$project->project_id }}
                                </div>
                            </div>
                            
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 border
                                {{ match($status) {
                                    'Completed' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                    'Pending Verification' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                    'In Progress' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                    default => 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-zinc-700'
                                } }}">
                                {{ $status }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-100 dark:border-zinc-800/80">
                            <span class="truncate max-w-[150px]">{{ $project->request->location ?? 'Campus' }}</span>
                            <span class="font-semibold">{{ $project->workers->count() }} {{ Str::plural('worker', $project->workers->count()) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs italic">
                        No active deployments for this unit.
                    </div>
                @endforelse
            </div>

            @if($deployments->count() > 0)
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-zinc-800 text-right">
                    <a href="{{ route('worker.job-orders.index') }}" class="text-[#0033a0] dark:text-blue-400 text-xs font-extrabold hover:underline inline-flex items-center gap-1">
                        <span>View all job orders</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
