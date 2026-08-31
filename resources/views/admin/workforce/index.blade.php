@extends('layouts.admin')
@section('page-title', 'Units / Sections')

@section('content')

<!-- Page Banner (Pale Yellow Header matching theme) -->
<div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-6 sm:px-8 py-5 sm:py-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 shadow-sm">
    <div>
        <h1 class="text-[#0033a0] dark:text-blue-400 text-xl sm:text-2xl font-bold mb-1">Units & Sections</h1>
        <p class="text-[#0033a0]/80 dark:text-gray-400 text-xs sm:text-sm font-medium">Manage service units, section leaders, and personnel deployments</p>
    </div>
    <button type="button" 
            onclick="openAddUnitModal()"
            class="bg-[#0033a0] hover:bg-[#002480] text-white px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition shadow-sm inline-flex items-center gap-1.5 shrink-0 cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Add New Unit</span>
    </button>
</div>

<!-- KPI Grid -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-2xs">
        <div class="text-[#254378] dark:text-blue-300 text-sm font-semibold mb-2">Total Workers</div>
        <div class="text-[#042B74] dark:text-white text-3xl font-bold leading-none">{{ $totalWorkers }}</div>
    </div>
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-2xs">
        <div class="text-[#254378] dark:text-blue-300 text-sm font-semibold mb-2">Busy Personnel</div>
        <div class="text-[#042B74] dark:text-white text-3xl font-bold leading-none">{{ $busyWorkers }}</div>
    </div>
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-2xs">
        <div class="text-[#254378] dark:text-blue-300 text-sm font-semibold mb-2">Available Personnel</div>
        <div class="text-[#042B74] dark:text-white text-3xl font-bold leading-none">{{ $availableWorkers }}</div>
    </div>
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-2xs">
        <div class="text-[#254378] dark:text-blue-300 text-sm font-semibold mb-2">Unassigned</div>
        <div class="text-[#042B74] dark:text-white text-3xl font-bold leading-none">{{ $workers->whereNull('team_id')->count() }}</div>
    </div>
</div>

<!-- Team Cards Grid (Per Category/Team Above with Leader Action & Member Listing) -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
    @foreach($teams as $team)
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 shadow-2xs hover:shadow-xs transition">
        <!-- Header: Team Icon, Name, and Actions -->
        <div class="flex items-start justify-between gap-3 mb-4">
                <div class="flex items-center gap-3.5 min-w-0">
                    @php
                        $teamNameLower = strtolower($team->team_name);
                    @endphp
                    <div class="w-12 h-12 bg-blue-50/80 dark:bg-zinc-800 border border-blue-100 dark:border-zinc-700 rounded-xl flex items-center justify-center shrink-0 shadow-2xs p-2 overflow-hidden">
                        <img src="{{ $team->icon_url }}" alt="{{ $team->team_name }}" class="w-8 h-8 object-contain">
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-[#042B74] dark:text-white text-base font-bold leading-tight truncate">{{ $team->team_name }}</h3>
                        <div class="text-xs text-[#47658F] dark:text-gray-400 font-medium mt-0.5 flex items-center gap-1.5">
                            <span>Section Leader:</span> 
                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ $team->leader_name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons: Assign Leader & Delete Unit -->
                <div class="flex items-center gap-1.5 shrink-0">
                    @if($team->team_members->isNotEmpty())
                        <button type="button"
                                onclick="openChangeLeaderModal({{ $team->team_id }}, '{{ addslashes($team->team_name) }}', {{ json_encode($team->team_members->map(fn($w) => ['worker_id' => $w->worker_id, 'name' => $w->staff->user->first_name . ' ' . $w->staff->user->last_name, 'is_leader' => ($team->leader_worker && $team->leader_worker->worker_id === $w->worker_id)])) }})"
                                class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-[#0038A8] dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 hover:bg-[#0038A8] hover:text-white dark:hover:bg-blue-600 border border-[#0038A8]/20 transition shrink-0 cursor-pointer shadow-2xs inline-flex items-center gap-1.5"
                                title="Assign or Change Team Leader for {{ $team->team_name }}">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="hidden sm:inline">{{ $team->leader_name !== 'Not Assigned' ? 'Change Leader' : 'Assign Leader' }}</span>
                        </button>
                    @endif

                    <button type="button"
                            onclick="openDeleteUnitModal({{ $team->team_id }}, '{{ addslashes($team->team_name) }}', {{ $team->team_members->count() }})"
                            class="p-1.5 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40 border border-red-200 dark:border-red-900/50 transition shrink-0 cursor-pointer shadow-2xs"
                            title="Delete Unit {{ $team->team_name }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>

        <!-- Stats Bar -->
        <div class="grid grid-cols-3 gap-2 border border-gray-200 dark:border-zinc-800 rounded-xl overflow-hidden mb-4 bg-gray-50/50 dark:bg-zinc-900/50">
            <div class="p-2.5 text-center border-r border-gray-200 dark:border-zinc-800">
                <div class="text-sm font-extrabold text-[#0038A8] dark:text-blue-400">{{ $team->skilled_workers }}</div>
                <div class="text-[10px] font-semibold text-gray-500 uppercase">Workers</div>
            </div>
            <div class="p-2.5 text-center border-r border-gray-200 dark:border-zinc-800">
                <div class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">{{ $team->available }}</div>
                <div class="text-[10px] font-semibold text-gray-500 uppercase">Available</div>
            </div>
            <div class="p-2.5 text-center">
                <div class="text-sm font-extrabold text-amber-600 dark:text-amber-400">{{ $team->skilled_workers - $team->available }}</div>
                <div class="text-[10px] font-semibold text-gray-500 uppercase">Busy</div>
            </div>
        </div>

        <!-- Unit Personnel List (Members of each Team with Requisition Number) -->
        <div class="space-y-2">
            <div class="text-[11px] font-bold uppercase tracking-wider text-[#042B74] dark:text-blue-400 flex items-center justify-between">
                <span>Unit Personnel</span>
                <span class="text-[11px] font-semibold text-gray-400">({{ $team->team_members->count() }} members)</span>
            </div>
            
            <div class="space-y-2 max-h-52 overflow-y-auto pr-1">
                @forelse($team->team_members as $member)
                    @php
                        $isLeader = ($team->leader_worker && $team->leader_worker->worker_id === $member->worker_id);
                        $mProject = $member->projects->first();
                        $mReqId = $mProject?->request_id;
                        $mCatName = strtolower($mProject?->request?->category?->category_name ?? '');
                        $mPrefix = match(true) {
                            str_contains($mCatName, 'carpentry') || str_contains($mCatName, 'masonry') => 'CMS',
                            str_contains($mCatName, 'plumbing') => 'PLS',
                            str_contains($mCatName, 'paint') => 'PTS',
                            str_contains($mCatName, 'electric') => 'EES',
                            default => 'REQ'
                        };
                        $mReqCode = $mReqId ? ($mPrefix . '-' . str_pad($mReqId, 3, '0', STR_PAD_LEFT)) : null;
                        $mTaskTitle = $mProject?->request?->title ?? ($mProject?->request?->category?->category_name ?? null);
                    @endphp
                    <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-zinc-800/60 border border-gray-100 dark:border-zinc-800 flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-[#0038A8] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                {{ strtoupper(substr($member->staff->user->first_name ?? 'W', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-gray-900 dark:text-gray-100 text-xs flex items-center gap-1.5">
                                    <span class="truncate">{{ $member->staff->user->first_name ?? '' }} {{ $member->staff->user->last_name ?? '' }}</span>
                                    @if($isLeader)
                                        <span class="text-[10px] font-bold text-[#0038A8] dark:text-blue-300 bg-blue-100 dark:bg-blue-950/80 px-1.5 py-0.2 rounded border border-blue-200 shrink-0">Leader</span>
                                    @endif
                                </div>
                                @if(!$member->is_available && ($mReqCode || $mTaskTitle))
                                    <div class="text-[11px] font-semibold text-gray-600 dark:text-gray-300 flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[#0038A8] dark:text-blue-300 font-extrabold bg-blue-50 dark:bg-blue-950/60 px-1.5 py-0.2 rounded border border-blue-200 dark:border-blue-800 font-mono text-[10px]">
                                            {{ $mReqCode ?? 'REQ' }}
                                        </span>
                                        <span class="truncate text-gray-700 dark:text-gray-300 font-medium max-w-[160px]" title="{{ $mTaskTitle }}">{{ $mTaskTitle ?? 'Assigned Task' }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="shrink-0">
                            @php
                                $mActiveCount = $member->projects->count();
                            @endphp
                            @if($mActiveCount === 0)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300">
                                    Available
                                </span>
                            @elseif($mActiveCount === 1)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-300">
                                    Busy (1 Active)
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-300">
                                    Busy (1 Active, {{ $mActiveCount - 1 }} Queued)
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-3 text-center text-xs text-gray-400 italic bg-gray-50 dark:bg-zinc-800/40 rounded-xl">
                        No personnel currently assigned to this unit.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
    @endforeach
</div>

<!-- Workers Management List Table -->
<div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xs overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 dark:border-zinc-800 flex justify-between items-center bg-gray-50/50 dark:bg-zinc-900/40">
        <div>
            <h2 class="text-[#042B74] dark:text-blue-400 text-lg font-bold">Worker Directory</h2>
            <p class="text-gray-500 dark:text-gray-400 text-xs mt-0.5">Assign workers to teams and view active requisitions</p>
        </div>
        <div class="flex items-center gap-2">
            <!-- Filter by team -->
            <select id="teamFilter" onchange="filterWorkers()" class="text-xs border border-gray-300 dark:border-zinc-700 rounded-xl px-3 py-2 focus:outline-none focus:border-[#0038A8] bg-white dark:bg-zinc-900 text-gray-700 dark:text-gray-200 font-semibold cursor-pointer shadow-2xs">
                <option value="">All Units & Teams</option>
                <option value="unassigned">Unassigned Workers</option>
                @foreach($teams as $t)
                    <option value="{{ $t->team_id }}">{{ $t->team_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="p-0 overflow-x-auto">
        <table class="w-full text-left border-collapse" id="workersTable">
            <thead>
                <tr class="bg-gray-50/80 dark:bg-zinc-900/60 border-b border-gray-200 dark:border-zinc-800">
                    <th class="px-6 py-3.5 text-[#042B74] dark:text-blue-400 text-[11px] font-bold uppercase tracking-wider">Worker</th>
                    <th class="px-6 py-3.5 text-[#042B74] dark:text-blue-400 text-[11px] font-bold uppercase tracking-wider">Current Team</th>
                    <th class="px-6 py-3.5 text-[#042B74] dark:text-blue-400 text-[11px] font-bold uppercase tracking-wider">Status & Requisition</th>
                    <th class="px-6 py-3.5 text-[#042B74] dark:text-blue-400 text-[11px] font-bold uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-xs">
                @foreach($workers as $worker)
                <tr class="hover:bg-gray-50/70 dark:hover:bg-zinc-800/50 transition worker-row" data-team="{{ $worker->team_id ?? 'unassigned' }}">
                    <!-- Worker Info -->
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#0038A8] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                {{ strtoupper(substr($worker->staff->user->first_name ?? 'W', 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white font-bold text-xs">
                                    {{ $worker->staff->user->first_name ?? 'Unknown' }} {{ $worker->staff->user->last_name ?? '' }}
                                </div>
                                <div class="text-[11px] text-gray-400">Worker #{{ $worker->worker_id }}</div>
                            </div>
                        </div>
                    </td>

                    <!-- Team Column -->
                    <td class="px-6 py-4">
                        @if($worker->team)
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-[#0038A8] border border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800">
                                    {{ $worker->team->team_name }}
                                </span>
                                @if($worker->team->teamLeader && $worker->team->teamLeader->staff_id === $worker->staff_id)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-[#0038A8] border border-blue-200 dark:bg-blue-950/80 dark:text-blue-300">
                                        Leader
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200 dark:bg-zinc-800 dark:text-gray-400 dark:border-zinc-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Unassigned
                            </span>
                        @endif
                    </td>

                    <!-- Status & Requisition Column -->
                    <td class="px-6 py-4">
                        @php
                            $wActiveCount = $worker->projects->count();
                        @endphp
                        @if($wActiveCount === 0)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-green-50 text-green-700 border border-green-200 dark:bg-emerald-950/50 dark:text-emerald-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Available
                            </span>
                        @else
                            @php
                                $activeProject = $worker->projects->first();
                                $reqId = $activeProject?->request_id;
                                $catName = strtolower($activeProject?->request?->category?->category_name ?? '');
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
                                $reqCode = $reqId ? ($prefix . '-' . str_pad($reqId, 3, '0', STR_PAD_LEFT)) : null;
                                $taskTitle = $activeProject?->request?->title ?? ($activeProject?->request?->category?->category_name ?? null);
                            @endphp
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 w-fit dark:bg-amber-950/50 dark:text-amber-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> 
                                    @if($wActiveCount === 1)
                                        Busy (1 Active)
                                    @else
                                        Busy (1 Active, {{ $wActiveCount - 1 }} Queued)
                                    @endif
                                </span>
                                @if($activeProject)
                                    <div class="text-[11px] text-gray-600 dark:text-gray-300 font-semibold flex items-center gap-1.5 mt-0.5">
                                        @if($reqCode)
                                            <span class="text-[#0038A8] dark:text-blue-300 font-extrabold bg-blue-50 dark:bg-blue-950/60 px-1.5 py-0.5 rounded border border-blue-200 dark:border-blue-800 text-[10px] font-mono">
                                                {{ $reqCode }}
                                            </span>
                                        @endif
                                        <span class="text-gray-700 dark:text-gray-300 font-medium truncate max-w-[200px]" title="{{ $taskTitle }}">
                                            {{ $taskTitle ?? 'Project #'.$activeProject->project_id }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </td>


                    <!-- Actions Column (Assign Team Only — Leader is managed on Team cards above) -->
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <!-- Assign / Change Team Button -->
                            <button
                                onclick="openAssignModal({{ $worker->worker_id }}, '{{ addslashes($worker->staff->user->first_name ?? 'Worker') }} {{ addslashes($worker->staff->user->last_name ?? '') }}', {{ $worker->team_id ?? 'null' }})"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#0038A8] dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 hover:bg-[#0038A8] hover:text-white dark:hover:bg-blue-600 px-3 py-1.5 rounded-lg border border-[#0038A8]/20 transition cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $worker->team ? 'Change Team' : 'Assign Team' }}
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($workers->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-sm font-medium">No workers found</p>
        </div>
        @endif
    </div>
</div>


<!-- ── Assign Team Modal ─────────────────────────────────────────────── -->
<div id="assignTeamModal" class="fixed inset-0 z-[99999] hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-xs" onclick="closeAssignModal()"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-200 scale-95 opacity-0" id="assignModalPanel">

            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-zinc-800 bg-[#FFFDE6] dark:bg-[#18181b]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-[#0038A8] font-bold flex items-center justify-center">
                        👥
                    </div>
                    <div>
                        <h3 class="text-[#042B74] dark:text-blue-400 font-bold text-base">Assign Team / Unit</h3>
                        <p id="modalWorkerName" class="text-gray-500 text-xs mt-0.5"></p>
                    </div>
                </div>
                <button onclick="closeAssignModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-amber-100/60 dark:hover:bg-zinc-800 text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="assignTeamForm" method="POST">
                @csrf
                <div class="px-6 py-5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3">Select Unit</label>

                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1" id="teamOptionsList">
                        @foreach($teams as $team)
                        <label class="team-option-label flex items-center gap-4 p-3.5 rounded-xl border border-gray-200 dark:border-zinc-800 cursor-pointer hover:border-[#0038A8] hover:bg-blue-50/50 transition-all has-[:checked]:border-[#0038A8] has-[:checked]:bg-blue-50/50"
                            data-team-id="{{ $team->team_id }}">
                            <input type="radio" name="team_id" value="{{ $team->team_id }}" class="sr-only team-radio">
                            <div class="w-9 h-9 rounded-lg bg-blue-50/80 dark:bg-zinc-800 border border-blue-100 dark:border-zinc-700 flex items-center justify-center shrink-0 p-1.5 overflow-hidden">
                                <img src="{{ $team->icon_url }}" alt="{{ $team->team_name }}" class="w-6 h-6 object-contain">
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold text-gray-900 dark:text-white">{{ $team->team_name }}</div>
                                <div class="text-[11px] text-gray-400 mt-0.5">
                                    {{ $team->skilled_workers }} worker(s) · Leader: {{ $team->leader_name }}
                                </div>
                            </div>
                            <div class="shrink-0 w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center team-radio-dot transition-all">
                                <div class="w-2.5 h-2.5 rounded-full bg-[#0038A8] scale-0 transition-transform team-radio-fill"></div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <label class="flex items-center gap-4 p-3.5 rounded-xl border border-gray-200 dark:border-zinc-800 cursor-pointer hover:border-red-300 hover:bg-red-50/50 transition-all has-[:checked]:border-red-400 has-[:checked]:bg-red-50 mt-2"
                        id="removeTeamLabel" style="display:none">
                        <input type="radio" name="team_id" value="" class="sr-only">
                        <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center text-lg shrink-0">🚫</div>
                        <div class="flex-1">
                            <div class="text-xs font-bold text-red-600">Remove from unit</div>
                            <div class="text-[11px] text-gray-400 mt-0.5">Worker will be marked as unassigned</div>
                        </div>
                    </label>
                </div>

                <div class="flex gap-3 px-6 pb-6 pt-2 border-t border-gray-100 dark:border-zinc-800">
                    <button type="button" onclick="closeAssignModal()"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-zinc-700 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" id="assignSubmitBtn"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-[#0038A8] text-white text-xs font-semibold hover:bg-[#002B82] transition shadow-2xs flex items-center justify-center gap-2">
                        Assign Team
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Change Team Leader Modal Popup (Navy Theme Styled) ──────────────── -->
<div id="changeLeaderModal" class="fixed inset-0 z-[99999] hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-xs" onclick="closeChangeLeaderModal()"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-200 scale-95 opacity-0" id="changeLeaderPanel">

            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-zinc-800 bg-[#FFFDE6] dark:bg-[#18181b]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-[#0038A8] dark:text-blue-400 font-bold flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-[#042B74] dark:text-blue-400 font-bold text-base">Assign Section Leader</h3>
                        <p id="leaderModalTeamName" class="text-gray-500 text-xs mt-0.5"></p>
                    </div>
                </div>
                <button onclick="closeChangeLeaderModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-amber-100/60 dark:hover:bg-zinc-800 text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="changeLeaderForm" method="POST">
                @csrf
                <div class="px-6 py-5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3">
                        Select Member to Lead this Unit <span class="text-red-500">*</span>
                    </label>

                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1" id="leaderMembersList">
                        <!-- Populated via JS -->
                    </div>
                </div>

                <div class="flex gap-3 px-6 pb-6 pt-2 border-t border-gray-100 dark:border-zinc-800">
                    <button type="button" onclick="closeChangeLeaderModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-zinc-700 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-[#0038A8] hover:bg-[#002B82] text-white text-xs font-semibold transition shadow-2xs">
                        Confirm Leader
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Add New Unit Modal Popup ────────────────────────────────────────── -->
<div id="addUnitModal" class="fixed inset-0 z-[99999] hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-xs" onclick="closeAddUnitModal()"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-200 scale-95 opacity-0" id="addUnitPanel">

            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-zinc-800 bg-[#FFFDE6] dark:bg-[#18181b]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-[#0038A8] dark:text-blue-400 font-bold flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-[#042B74] dark:text-blue-400 font-bold text-base">Add New Unit / Section</h3>
                        <p class="text-gray-500 text-xs mt-0.5">Creates a service unit and its 1-to-1 category</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddUnitModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-amber-100/60 dark:hover:bg-zinc-800 text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.workforce.store-unit') }}" method="POST">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Unit / Section Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="unit_name" 
                               required 
                               placeholder="e.g. Airconditioning & Refrigeration Unit" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-xs font-medium text-slate-800 dark:text-gray-200 outline-none focus:border-[#0038A8] focus:ring-1 focus:ring-[#0038A8] transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Description <span class="text-gray-400 font-normal text-[11px]">(Optional)</span>
                        </label>
                        <textarea name="description" 
                                  rows="2" 
                                  placeholder="Brief description of works handled by this section..." 
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-xs font-medium text-slate-800 dark:text-gray-200 outline-none focus:border-[#0038A8] focus:ring-1 focus:ring-[#0038A8] transition resize-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Section Leader <span class="text-gray-400 font-normal text-[11px]">(Optional)</span>
                        </label>
                        <select name="leader_id" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-xs font-medium text-slate-800 dark:text-gray-200 outline-none focus:border-[#0038A8] focus:ring-1 focus:ring-[#0038A8] transition">
                            <option value="">-- No Leader Assigned (Assign Later) --</option>
                            @foreach($workers as $w)
                                <option value="{{ $w->worker_id }}">
                                    {{ $w->staff->user->first_name }} {{ $w->staff->user->last_name }} 
                                    ({{ $w->team ? $w->team->team_name : 'Unassigned' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 px-6 pb-6 pt-2 border-t border-gray-100 dark:border-zinc-800">
                    <button type="button" onclick="closeAddUnitModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-zinc-700 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-[#0038A8] hover:bg-[#002B82] text-white text-xs font-semibold transition shadow-2xs">
                        Create Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Delete Unit Confirmation Modal Popup ────────────────────────────── -->
<div id="deleteUnitModal" class="fixed inset-0 z-[99999] hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-xs" onclick="closeDeleteUnitModal()"></div>

    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-200 scale-95 opacity-0" id="deleteUnitPanel">

            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-zinc-800 bg-red-50/60 dark:bg-red-950/30">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/40 text-red-600 font-bold flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <div>
                        <h3 class="text-red-900 dark:text-red-300 font-bold text-base">Delete Unit / Section</h3>
                        <p class="text-red-700/80 dark:text-red-400 text-xs mt-0.5">Soft-deletes unit and linked category</p>
                    </div>
                </div>
                <button type="button" onclick="closeDeleteUnitModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-red-100 dark:hover:bg-zinc-800 text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="deleteUnitForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="px-6 py-5 space-y-3">
                    <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
                        Are you sure you want to delete <strong id="deleteUnitModalName" class="text-slate-900 dark:text-white"></strong>?
                    </p>

                    <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 text-xs text-amber-800 dark:text-amber-300 space-y-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Soft Deletion & Safety</span>
                        </div>
                        <p class="text-[11px] leading-normal text-amber-700 dark:text-amber-400">
                            Existing service requests referencing this category will remain completely intact. Any workers assigned to this unit will be moved to Unassigned.
                        </p>
                    </div>
                </div>

                <div class="flex gap-3 px-6 pb-6 pt-2 border-t border-gray-100 dark:border-zinc-800">
                    <button type="button" onclick="closeDeleteUnitModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-zinc-700 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-semibold transition shadow-2xs">
                        Yes, Delete Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .team-radio:checked ~ .team-radio-dot {
        border-color: #0038A8;
    }
    .team-radio:checked ~ .team-radio-dot .team-radio-fill {
        transform: scale(1);
    }
    #assignTeamModal.modal-open #assignModalPanel,
    #changeLeaderModal.modal-open #changeLeaderPanel,
    #addUnitModal.modal-open #addUnitPanel,
    #deleteUnitModal.modal-open #deleteUnitPanel {
        transform: scale(1);
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script>
    // Filter workers by team
    function filterWorkers() {
        const val = document.getElementById('teamFilter').value;
        document.querySelectorAll('.worker-row').forEach(row => {
            if (!val) {
                row.style.display = '';
                return;
            }
            const rowTeam = row.dataset.team;
            row.style.display = (rowTeam == val) ? '' : 'none';
        });
    }

    // Add Unit Modal
    function openAddUnitModal() {
        const modal = document.getElementById('addUnitModal');
        const panel = document.getElementById('addUnitPanel');
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.add('modal-open');
            panel.style.transform = 'scale(1)';
            panel.style.opacity   = '1';
        });
    }

    function closeAddUnitModal() {
        const modal = document.getElementById('addUnitModal');
        const panel = document.getElementById('addUnitPanel');
        panel.style.transform = 'scale(0.95)';
        panel.style.opacity   = '0';
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('modal-open');
        }, 180);
    }

    // Delete Unit Modal
    function openDeleteUnitModal(teamId, teamName, memberCount) {
        const modal  = document.getElementById('deleteUnitModal');
        const panel  = document.getElementById('deleteUnitPanel');
        const nameEl = document.getElementById('deleteUnitModalName');
        const form   = document.getElementById('deleteUnitForm');

        nameEl.textContent = teamName;
        form.action = `/admin/workforce/teams/${teamId}`;

        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.add('modal-open');
            panel.style.transform = 'scale(1)';
            panel.style.opacity   = '1';
        });
    }

    function closeDeleteUnitModal() {
        const modal = document.getElementById('deleteUnitModal');
        const panel = document.getElementById('deleteUnitPanel');
        panel.style.transform = 'scale(0.95)';
        panel.style.opacity   = '0';
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('modal-open');
        }, 180);
    }

    // Assign Team Modal
    function openAssignModal(workerId, workerName, currentTeamId) {
        const modal     = document.getElementById('assignTeamModal');
        const panel     = document.getElementById('assignModalPanel');
        const form      = document.getElementById('assignTeamForm');
        const nameEl    = document.getElementById('modalWorkerName');
        const removeBtn = document.getElementById('removeTeamLabel');

        form.action = `/admin/workforce/${workerId}/assign-team`;
        nameEl.textContent = workerName.trim();

        document.querySelectorAll('.team-radio').forEach(radio => {
            radio.checked = (parseInt(radio.value) === currentTeamId);
            updateRadioStyle(radio);
        });

        removeBtn.style.display = currentTeamId ? 'flex' : 'none';

        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.add('modal-open');
            panel.style.transform = 'scale(1)';
            panel.style.opacity   = '1';
        });
    }

    function closeAssignModal() {
        const modal = document.getElementById('assignTeamModal');
        const panel = document.getElementById('assignModalPanel');
        panel.style.transform = 'scale(0.95)';
        panel.style.opacity   = '0';
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('modal-open');
        }, 180);
    }

    function updateRadioStyle(radio) {
        const label    = radio.closest('label');
        const dot      = label?.querySelector('.team-radio-dot');
        const fill     = label?.querySelector('.team-radio-fill');
        if (!dot) return;
        if (radio.checked) {
            dot.classList.add('border-[#0038A8]');
            fill?.classList.remove('scale-0');
        } else {
            dot.classList.remove('border-[#0038A8]');
            fill?.classList.add('scale-0');
        }
    }

    // Change Leader Modal (For Team Cards Above with Primary Navy Theme)
    function openChangeLeaderModal(teamId, teamName, members) {
        const modal  = document.getElementById('changeLeaderModal');
        const panel  = document.getElementById('changeLeaderPanel');
        const nameEl = document.getElementById('leaderModalTeamName');
        const listEl = document.getElementById('leaderMembersList');
        const form   = document.getElementById('changeLeaderForm');

        nameEl.textContent = teamName;
        listEl.innerHTML = '';

        if (!members || members.length === 0) {
            listEl.innerHTML = '<p class="text-xs text-gray-400 italic py-3 text-center">No personnel available in this team to assign as leader.</p>';
        } else {
            members.forEach((m, idx) => {
                const isChecked = m.is_leader ? 'checked' : (idx === 0 ? 'checked' : '');
                const formUrl = `/admin/workforce/${m.worker_id}/make-leader`;
                
                const html = `
                    <label class="flex items-center justify-between p-3.5 rounded-xl border border-gray-200 dark:border-zinc-800 cursor-pointer hover:border-[#0038A8] hover:bg-blue-50/40 transition">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="selected_worker" value="${m.worker_id}" data-action="${formUrl}" ${isChecked} onchange="document.getElementById('changeLeaderForm').action = this.dataset.action" class="text-[#0038A8] focus:ring-[#0038A8]">
                            <div>
                                <div class="text-xs font-bold text-gray-900 dark:text-white">${m.name}</div>
                                <div class="text-[11px] text-gray-400">Worker #${m.worker_id} ${m.is_leader ? '· Current Leader' : ''}</div>
                            </div>
                        </div>
                        ${m.is_leader ? '<span class="text-[10px] font-bold bg-blue-100 text-[#0038A8] dark:bg-blue-950/80 dark:text-blue-300 px-2 py-0.5 rounded border border-blue-200">Current Leader</span>' : ''}
                    </label>
                `;
                listEl.insertAdjacentHTML('beforeend', html);
            });

            const firstRadio = listEl.querySelector('input[type="radio"]:checked') || listEl.querySelector('input[type="radio"]');
            if (firstRadio) {
                form.action = firstRadio.dataset.action;
            }
        }

        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.add('modal-open');
            panel.style.transform = 'scale(1)';
            panel.style.opacity   = '1';
        });
    }

    function closeChangeLeaderModal() {
        const modal = document.getElementById('changeLeaderModal');
        const panel = document.getElementById('changeLeaderPanel');
        panel.style.transform = 'scale(0.95)';
        panel.style.opacity   = '0';
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('modal-open');
        }, 180);
    }

    document.querySelectorAll('.team-radio').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.team-radio').forEach(r => updateRadioStyle(r));
        });
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeAssignModal();
            closeChangeLeaderModal();
            closeAddUnitModal();
            closeDeleteUnitModal();
        }
    });

    setTimeout(() => {
        document.getElementById('flash-success')?.remove();
        document.getElementById('flash-error')?.remove();
    }, 4000);
</script>
@endpush
