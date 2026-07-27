@extends('layouts.admin')
@section('page-title', 'Units / Sections')

@section('content')

{{-- Flash Messages --}}
@if(session('success'))
<div id="flash-success" class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-5 py-3.5 rounded-xl shadow-sm">
    <svg class="w-5 h-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    <span class="text-sm font-medium">{{ session('success') }}</span>
    <button onclick="document.getElementById('flash-success').remove()" class="ml-auto text-green-500 hover:text-green-700">✕</button>
</div>
@endif
@if(session('error'))
<div id="flash-error" class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-5 py-3.5 rounded-xl shadow-sm">
    <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
    <span class="text-sm font-medium">{{ session('error') }}</span>
    <button onclick="document.getElementById('flash-error').remove()" class="ml-auto text-red-500 hover:text-red-700">✕</button>
</div>
@endif

<!-- Page Banner -->
<div class="bg-[#fefce8] border border-[#1a3c8f] rounded-xl px-8 py-6 flex justify-between items-center mb-6 shadow-sm">
    <div>
        <h1 class="text-[#1a3c8f] text-2xl font-bold mb-1">Units / Sections</h1>
        <p class="text-[#1a3c8f] text-sm opacity-90">Manage service units and their personnel</p>
    </div>
</div>

<!-- KPI Grid -->
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
        <div class="text-[#1a3c8f] text-sm font-medium mb-2">Total Workers</div>
        <div class="text-[#1a3c8f] text-3xl font-bold leading-none">{{ $totalWorkers }}</div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
        <div class="text-[#1a3c8f] text-sm font-medium mb-2">Busy</div>
        <div class="text-[#1a3c8f] text-3xl font-bold leading-none">{{ $busyWorkers }}</div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
        <div class="text-[#1a3c8f] text-sm font-medium mb-2">Available</div>
        <div class="text-[#1a3c8f] text-3xl font-bold leading-none">{{ $availableWorkers }}</div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
        <div class="text-[#1a3c8f] text-sm font-medium mb-2">Unassigned</div>
        <div class="text-[#1a3c8f] text-3xl font-bold leading-none">{{ $workers->whereNull('team_id')->count() }}</div>
    </div>
</div>

<!-- Team Cards Grid -->
<div class="grid grid-cols-2 gap-5 mb-8">
    @foreach($teams as $team)
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start gap-4 mb-5">
            @php
                $teamNameLower = strtolower($team->team_name);
            @endphp
            <div class="w-12 h-12 bg-blue-50 dark:bg-zinc-800 border border-blue-100 dark:border-zinc-700 rounded-xl flex items-center justify-center shrink-0 shadow-xs">
                @if(str_contains($teamNameLower, 'plumb'))
                    <!-- Plumbing SVG -->
                    <svg class="w-6 h-6 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.6 15.12a2 2 0 01-1.022-.547l-1.07-1.07a2 2 0 010-2.828l1.07-1.07a2 2 0 011.022-.547l2.387-.477a6 6 0 003.86-.517l.318-.158a6 6 0 013.86-.517l2.387.477a2 2 0 011.022.547l1.07 1.07a2 2 0 010 2.828l-1.07 1.07z"/></svg>
                @elseif(str_contains($teamNameLower, 'paint'))
                    <!-- Painting SVG -->
                    <svg class="w-6 h-6 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                @elseif(str_contains($teamNameLower, 'janitor'))
                    <!-- Janitorial SVG -->
                    <svg class="w-6 h-6 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                @elseif(str_contains($teamNameLower, 'manpower') || str_contains($teamNameLower, 'landscaping'))
                    <!-- Manpower / Landscaping SVG -->
                    <svg class="w-6 h-6 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                @else
                    <!-- Carpentry, Masonry & Electrical SVG -->
                    <svg class="w-6 h-6 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.9 6.91a2.12 2.12 0 01-3-3l6.91-6.9a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-[#1a3c8f] text-base font-bold mb-1 leading-tight">{{ $team->team_name }}</div>
                <div class="text-xs text-gray-500">Team Leader: 
                    <span class="font-medium text-gray-700">{{ $team->leader_name }}</span>
                </div>
            </div>
        </div>

        <div class="flex border border-slate-300 rounded-md overflow-hidden mb-4">
            <div class="flex-1 p-2.5 text-center border-r border-slate-300 bg-blue-50/50">
                <div class="text-base font-bold text-[#1a3c8f]">{{ $team->skilled_workers > 0 ? $team->skilled_workers : '--' }}</div>
                <div class="text-[10px] text-gray-500 mt-0.5">Workers</div>
            </div>
            <div class="flex-1 p-2.5 text-center border-r border-slate-300 bg-blue-50/50">
                <div class="text-base font-bold text-emerald-600">{{ $team->available }}</div>
                <div class="text-[10px] text-gray-500 mt-0.5">Available</div>
            </div>
            <div class="flex-1 p-2.5 text-center bg-blue-50/50">
                <div class="text-base font-bold text-amber-600">{{ $team->skilled_workers - $team->available }}</div>
                <div class="text-[10px] text-gray-500 mt-0.5">Busy</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Workers List -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-[#f8faff]">
        <div>
            <h2 class="text-[#1a3c8f] text-lg font-bold">Worker Management</h2>
            <p class="text-gray-500 text-xs mt-0.5">Assign teams and promote team leaders</p>
        </div>
        <div class="flex items-center gap-2">
            <!-- Filter by team -->
            <select id="teamFilter" onchange="filterWorkers()" class="text-xs border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#1a3c8f] bg-white text-gray-700">
                <option value="">All Teams</option>
                <option value="unassigned">Unassigned</option>
                @foreach($teams as $t)
                    <option value="{{ $t->team_id }}">{{ $t->team_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="p-0 overflow-x-auto">
        <table class="w-full text-left border-collapse" id="workersTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-6 py-3 text-[#1a3c8f] text-[11px] font-bold uppercase tracking-wider">Worker</th>
                    <th class="px-6 py-3 text-[#1a3c8f] text-[11px] font-bold uppercase tracking-wider">Current Team</th>
                    <th class="px-6 py-3 text-[#1a3c8f] text-[11px] font-bold uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-[#1a3c8f] text-[11px] font-bold uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($workers as $worker)
                <tr class="hover:bg-gray-50 transition worker-row" data-team="{{ $worker->team_id ?? 'unassigned' }}">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($worker->staff->user->first_name ?? 'W', 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-[#1a3c8f] font-bold text-sm">
                                    {{ $worker->staff->user->first_name ?? 'Unknown' }} {{ $worker->staff->user->last_name ?? '' }}
                                </div>
                                <div class="text-xs text-gray-400">Worker #{{ $worker->worker_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($worker->team)
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-[#1a3c8f] border border-blue-200">
                                    {{ $worker->team->team_name }}
                                </span>
                                @if($worker->team->teamLeader && $worker->team->teamLeader->staff_id === $worker->staff_id)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        ★ Leader
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Unassigned
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($worker->is_available)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-green-50 text-green-700 border border-green-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Available
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Busy
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <!-- Assign Team Button -->
                            <button
                                onclick="openAssignModal({{ $worker->worker_id }}, '{{ addslashes($worker->staff->user->first_name ?? 'Worker') }} {{ addslashes($worker->staff->user->last_name ?? '') }}', {{ $worker->team_id ?? 'null' }})"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#1a3c8f] bg-blue-50 hover:bg-[#1a3c8f] hover:text-white px-3 py-1.5 rounded-lg border border-[#1a3c8f]/20 transition-all duration-150"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $worker->team ? 'Change Team' : 'Assign Team' }}
                            </button>

                            <!-- Make Leader button -->
                            @if($worker->team && (!$worker->team->teamLeader || $worker->team->teamLeader->staff_id !== $worker->staff_id))
                                <form action="{{ route('admin.workforce.make-leader', $worker->worker_id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-700 hover:text-white px-3 py-1.5 rounded-lg border border-purple-200 transition-all duration-150"
                                        onclick="return confirm('Promote {{ addslashes($worker->staff->user->first_name ?? 'this worker') }} as Team Leader of {{ addslashes($worker->team->team_name) }}?')"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                        Make Leader
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($workers->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-sm font-medium">No workers found</p>
        </div>
        @endif
    </div>
</div>

<!-- ── Assign Team Modal ─────────────────────────────────────────────── -->
<div id="assignTeamModal" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeAssignModal()"></div>

    <!-- Panel -->
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all duration-200 scale-95 opacity-0" id="assignModalPanel">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#1a3c8f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-[#1a3c8f] font-bold text-base">Assign Team</h3>
                        <p id="modalWorkerName" class="text-gray-400 text-xs mt-0.5"></p>
                    </div>
                </div>
                <button onclick="closeAssignModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <form id="assignTeamForm" method="POST">
                @csrf
                <div class="px-6 py-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Select a Team</label>

                    <!-- Team option cards -->
                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1" id="teamOptionsList">
                        @foreach($teams as $team)
                        <label class="team-option-label flex items-center gap-4 p-3.5 rounded-xl border-2 border-gray-100 cursor-pointer hover:border-[#1a3c8f]/30 hover:bg-blue-50/50 transition-all has-[:checked]:border-[#1a3c8f] has-[:checked]:bg-[#f0f4ff]"
                            data-team-id="{{ $team->team_id }}">
                            <input type="radio" name="team_id" value="{{ $team->team_id }}" class="sr-only team-radio">
                            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center text-lg shrink-0">
                                {{ $team->icon }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-gray-800">{{ $team->team_name }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $team->skilled_workers }} worker(s) · Leader: {{ $team->leader_name }}
                                </div>
                            </div>
                            <div class="shrink-0 w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center team-radio-dot transition-all">
                                <div class="w-2.5 h-2.5 rounded-full bg-[#1a3c8f] scale-0 transition-transform team-radio-fill"></div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <!-- Remove from team option -->
                    <label class="flex items-center gap-4 p-3.5 rounded-xl border-2 border-gray-100 cursor-pointer hover:border-red-200 hover:bg-red-50/50 transition-all has-[:checked]:border-red-300 has-[:checked]:bg-red-50 mt-2"
                        id="removeTeamLabel" style="display:none">
                        <input type="radio" name="team_id" value="" class="sr-only">
                        <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center text-lg shrink-0">🚫</div>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-red-600">Remove from team</div>
                            <div class="text-xs text-gray-400 mt-0.5">Worker will be marked as unassigned</div>
                        </div>
                    </label>
                </div>

                <!-- Footer -->
                <div class="flex gap-3 px-6 pb-6">
                    <button type="button" onclick="closeAssignModal()"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" id="assignSubmitBtn"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-[#1a3c8f] text-white text-sm font-semibold hover:bg-[#152e6e] transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Assign Team
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
        border-color: #1a3c8f;
    }
    .team-radio:checked ~ .team-radio-dot .team-radio-fill {
        transform: scale(1);
    }
    /* Smooth modal entrance */
    #assignTeamModal.modal-open #assignModalPanel {
        transform: scale(1);
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script>
    // ── Filter workers by team ─────────────────────────────────────────
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

    // ── Assign Team Modal ──────────────────────────────────────────────
    let currentWorkerId = null;

    function openAssignModal(workerId, workerName, currentTeamId) {
        currentWorkerId = workerId;
        const modal     = document.getElementById('assignTeamModal');
        const panel     = document.getElementById('assignModalPanel');
        const form      = document.getElementById('assignTeamForm');
        const nameEl    = document.getElementById('modalWorkerName');
        const removeBtn = document.getElementById('removeTeamLabel');

        // Set form action for this worker
        form.action = `/admin/workforce/${workerId}/assign-team`;
        nameEl.textContent = workerName.trim();

        // Pre-select current team (if any)
        document.querySelectorAll('.team-radio').forEach(radio => {
            radio.checked = (parseInt(radio.value) === currentTeamId);
            updateRadioStyle(radio);
        });

        // Show "remove from team" option only if worker is currently on a team
        removeBtn.style.display = currentTeamId ? 'flex' : 'none';

        // Show modal with animation
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
            dot.classList.add('border-[#1a3c8f]');
            fill?.classList.remove('scale-0');
        } else {
            dot.classList.remove('border-[#1a3c8f]');
            fill?.classList.add('scale-0');
        }
    }

    // Radio visual update on click
    document.querySelectorAll('.team-radio').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.team-radio').forEach(r => updateRadioStyle(r));
        });
    });

    // Close modal on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeAssignModal();
    });

    // Auto-dismiss flash messages
    setTimeout(() => {
        document.getElementById('flash-success')?.remove();
        document.getElementById('flash-error')?.remove();
    }, 4000);
</script>
@endpush
