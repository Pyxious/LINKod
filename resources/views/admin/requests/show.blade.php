@extends('layouts.admin')

@section('page-title', 'Review & Action Request')

@section('content')
<div class="w-full max-w-6xl mx-auto space-y-6 font-sans">
    
    <!-- Top Header Banner -->
    <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-8 py-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2 flex-wrap">
                <span class="px-3 py-1 bg-[#0033a0] text-white text-[11px] font-extrabold uppercase tracking-wider rounded-full shadow-sm">
                    Requisition #{{ str_pad($serviceRequest->request_id, 4, '0', STR_PAD_LEFT) }}
                </span>
                <span class="px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider rounded-full border 
                    {{ match(strtolower($serviceRequest->priority ?? 'low')) {
                        'high' => 'bg-red-100 text-red-700 border-red-300',
                        'medium' => 'bg-amber-100 text-amber-700 border-amber-300',
                        default => 'bg-emerald-100 text-emerald-700 border-emerald-300'
                    } }}">
                    {{ strtoupper($serviceRequest->priority ?? 'Low') }} Priority
                </span>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-[11px] font-bold rounded-full">
                    {{ $serviceRequest->current_status }}
                </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                {{ $serviceRequest->title }}
            </h1>

            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">
                Submitted by <span class="font-bold text-slate-800 dark:text-gray-200">{{ $serviceRequest->client->user->first_name ?? 'N/A' }} {{ $serviceRequest->client->user->last_name ?? '' }}</span> 
                ({{ $serviceRequest->client->user->email_account ?? '' }})
                • {{ \Carbon\Carbon::parse($serviceRequest->submitted_at)->format('M d, Y h:i A') }}
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0 flex-wrap">
            <a href="{{ route('admin.requests.export', $serviceRequest->request_id) }}" target="_blank" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-full transition shadow-md inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Requisition
            </a>

            @if($serviceRequest->evaluation)
                <a href="{{ route('admin.requests.satisfaction', $serviceRequest->request_id) }}" target="_blank" class="px-5 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-full transition shadow-md inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    Print Satisfaction Page
                </a>
            @else
                <button type="button" disabled class="px-5 py-2.5 bg-gray-200 dark:bg-zinc-800 text-gray-400 dark:text-gray-500 text-xs font-bold rounded-full cursor-not-allowed inline-flex items-center gap-2 opacity-80" title="Client has not rated this service request yet">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    Print Satisfaction Page (Not Rated Yet)
                </button>
            @endif
        </div>
    </div>

    <!-- Request Details Card -->
    <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm">
        <h2 class="text-base font-bold text-[#0033a0] dark:text-blue-400 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Request Details & Specification
        </h2>

        <!-- Description Box -->
        <div class="mb-6">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Description / Issue Summary</div>
            <div class="bg-slate-50 dark:bg-zinc-800/60 p-4 rounded-xl text-slate-800 dark:text-gray-200 text-sm leading-relaxed border border-gray-100 dark:border-zinc-700">
                {{ $serviceRequest->description ?: 'No additional description provided.' }}
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Service Category</div>
                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $serviceRequest->category->category_name ?? 'Unclassified' }}</div>
            </div>

            <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Campus</div>
                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $serviceRequest->campus ?? 'BU Main' }}</div>
            </div>

            <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Office / Location</div>
                <div class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $serviceRequest->location }}</div>
            </div>
        </div>

        <!-- Supporting Attachment (if any) -->
        @if($serviceRequest->attachment)
            <div class="mt-6 border-t border-gray-100 dark:border-zinc-800 pt-5">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Attachment / Photo Evidence</div>
                <a href="{{ Storage::url($serviceRequest->attachment) }}" target="_blank" class="inline-flex items-center gap-3 p-3 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl hover:border-[#0033a0] transition group">
                    @if(Str::endsWith(strtolower($serviceRequest->attachment), ['.jpg', '.jpeg', '.png', '.webp']))
                        <img src="{{ Storage::url($serviceRequest->attachment) }}" alt="Attachment" class="w-16 h-16 object-cover rounded-lg border border-gray-200">
                    @else
                        <div class="w-12 h-12 bg-blue-100 text-[#0033a0] rounded-lg flex items-center justify-center font-bold text-xs">PDF</div>
                    @endif
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-[#0033a0] transition">View Full Attachment ↗</div>
                        <div class="text-[11px] text-gray-400">Click to open original file</div>
                    </div>
                </a>
            </div>
        @endif
    </div>

    <!-- Clientele Satisfaction Rating Section Card (Displayed when client has rated the request) -->
    @if($serviceRequest->evaluation)
        <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border-2 border-[#0033a0] dark:border-blue-700 p-7 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-zinc-800 pb-4 mb-4">
                <div>
                    <h2 class="text-base font-extrabold text-[#0033a0] dark:text-blue-400 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        Clientele Satisfaction Measurement Rating
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Submitted by client on {{ $serviceRequest->evaluation->rated_at ? $serviceRequest->evaluation->rated_at->format('M d, Y h:i A') : 'N/A' }}</p>
                </div>
                <a href="{{ route('admin.requests.satisfaction', $serviceRequest->request_id) }}" target="_blank" class="px-5 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-xl transition shadow-md inline-flex items-center gap-2 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Satisfaction Form
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50/70 dark:bg-zinc-800 p-4 rounded-xl border border-blue-200 dark:border-zinc-700 flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-[#0033a0] text-white font-black text-xl flex items-center justify-center shrink-0 shadow-sm">
                        {{ $serviceRequest->evaluation->rating }}★
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Overall Rating</div>
                        <div class="text-sm font-black text-slate-900 dark:text-white">
                            {{ match((int)$serviceRequest->evaluation->rating) {
                                5 => '5 / 5 — Very Satisfied',
                                4 => '4 / 5 — Satisfied',
                                3 => '3 / 5 — Neutral',
                                2 => '2 / 5 — Dissatisfied',
                                default => '1 / 5 — Very Dissatisfied'
                            } }}
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 bg-slate-50 dark:bg-zinc-800 p-4 rounded-xl border border-gray-200 dark:border-zinc-700">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Client Feedback & Comments</div>
                    <p class="text-xs text-slate-800 dark:text-gray-200 font-medium italic">
                        "{{ $serviceRequest->evaluation->feedback_text ?: 'No additional written feedback provided.' }}"
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Assigned Personnel & Maintenance Unit Card (Displayed when request is approved and has assigned workers) -->
    @if($serviceRequest->project && $serviceRequest->project->workers->isNotEmpty())
        <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-2xs">
            <h2 class="text-base font-bold text-[#042B74] dark:text-blue-400 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#0038A8] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Assigned Maintenance Personnel & Unit
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($serviceRequest->project->workers as $assignedWorker)
                    @php
                        $workerUser = $assignedWorker->staff->user ?? null;
                        $workerTeam = $assignedWorker->team->team_name ?? 'Maintenance Unit';
                        $isLeader = ($assignedWorker->team && $assignedWorker->team->teamLeader && $assignedWorker->team->teamLeader->staff_id === $assignedWorker->staff_id);
                    @endphp
                    <div class="bg-blue-50/50 dark:bg-zinc-800/60 border border-blue-100 dark:border-zinc-700 rounded-xl p-4 flex items-center gap-3.5 shadow-2xs">
                        <div class="w-10 h-10 rounded-full bg-[#0038A8] text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-2xs">
                            {{ strtoupper(substr($workerUser->first_name ?? 'W', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-gray-900 dark:text-white text-xs flex items-center gap-1.5">
                                <span class="truncate">{{ $workerUser->first_name ?? 'Worker' }} {{ $workerUser->last_name ?? '' }}</span>
                                @if($isLeader)
                                    <span class="text-[10px] font-bold text-[#0038A8] dark:text-blue-300 bg-blue-100 dark:bg-blue-950/80 px-1.5 py-0.2 rounded border border-blue-200 shrink-0">
                                        Leader
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                                {{ $workerTeam }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Action Forms Section -->
    @if(in_array($serviceRequest->current_status, ['Submitted', 'Pending']))
        <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#0033a0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Action Required: Review & Process Request
            </h2>
            
            <div class="flex flex-col lg:flex-row gap-6 items-stretch">
                <!-- Approve Form -->
                <form action="{{ route('admin.requests.approve', $serviceRequest->request_id) }}" method="POST" class="flex-1 bg-[#f0f6ff] dark:bg-zinc-800/50 p-6 rounded-2xl border border-blue-200 dark:border-zinc-700 shadow-sm flex flex-col justify-between">
                    @csrf
                    
                    <div>
                        <div class="flex items-center gap-2 text-[#0033a0] dark:text-blue-400 font-bold text-sm mb-4">
                            <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center font-extrabold">1</span>
                            Approve Request & Assign Project
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-1.5">Verify Category</label>
                                <select name="category_id" id="categorySelect" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->category_id }}" data-name="{{ strtolower($category->category_name) }}" {{ $serviceRequest->category_id == $category->category_id ? 'selected' : '' }}>
                                            {{ $category->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-1.5">Set Project Priority</label>
                                <select name="priority" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]" required>
                                    <option value="Low" {{ strtolower($serviceRequest->priority ?? 'low') === 'low' ? 'selected' : '' }}>Low Priority</option>
                                    <option value="Medium" {{ strtolower($serviceRequest->priority ?? 'low') === 'medium' ? 'selected' : '' }}>Medium Priority</option>
                                    <option value="High" {{ strtolower($serviceRequest->priority ?? 'low') === 'high' ? 'selected' : '' }}>High Priority</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-2">Assign Maintenance Workers</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-64 overflow-y-auto p-2.5 border border-blue-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900">
                                @foreach($workers as $worker)
                                    @php
                                        $teamName = strtolower($worker->team->team_name ?? '');
                                        $categoryName = strtolower($serviceRequest->category->category_name ?? '');
                                        $isRecommended = false;
                                        if ($teamName && $categoryName) {
                                            preg_match_all('/\w+/', $categoryName, $catWords);
                                            foreach ($catWords[0] as $word) {
                                                if (strlen($word) > 3 && str_contains($teamName, $word)) {
                                                    $isRecommended = true;
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    <label class="worker-option flex items-center gap-2.5 cursor-pointer p-2.5 hover:bg-blue-50 dark:hover:bg-zinc-800 rounded-xl border border-gray-100 dark:border-zinc-800 {{ $isRecommended ? 'bg-blue-50/80 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800' : '' }} transition-colors min-w-0" data-team="{{ strtolower($worker->team->team_name ?? '') }}">
                                        <input type="checkbox" name="worker_ids[]" value="{{ $worker->worker_id }}" {{ $isRecommended ? 'checked' : '' }} class="worker-checkbox rounded text-[#0033a0] focus:ring-[#0033a0] w-4 h-4 shrink-0">
                                        <div class="flex-1 min-w-0 flex items-center justify-between gap-1.5">
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-slate-900 dark:text-gray-200 truncate" title="{{ $worker->user->first_name ?? 'Unknown' }} {{ $worker->user->last_name ?? '' }}">
                                                    {{ $worker->user->first_name ?? 'Unknown' }} {{ $worker->user->last_name ?? '' }}
                                                </div>
                                                <div class="text-[11px] text-gray-500 truncate" title="{{ $worker->team->team_name ?? 'No Unit' }}">
                                                    {{ $worker->team->team_name ?? 'No Unit' }}
                                                </div>
                                            </div>
                                            <span class="recommended-badge text-[9px] bg-[#0033a0] text-white px-2 py-0.5 rounded-full font-extrabold uppercase tracking-wide shrink-0 {{ $isRecommended ? '' : 'hidden' }}">Recommended</span>
                                        </div>
                                    </label>
                                @endforeach
                                @if($workers->isEmpty())
                                    <p class="text-xs text-gray-400 p-2 italic col-span-2">No active workers found in database.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-[#0033a0] hover:bg-[#002480] text-white font-bold py-3 px-4 rounded-xl transition shadow-md flex justify-center items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Approve & Launch Project
                    </button>
                </form>

                <!-- Reject Form -->
                <form action="{{ route('admin.requests.reject', $serviceRequest->request_id) }}" method="POST" class="w-full lg:w-80 bg-red-50/60 dark:bg-red-950/20 p-6 rounded-2xl border border-red-200 dark:border-red-900/50 shadow-sm flex flex-col justify-between">
                    @csrf
                    <div>
                        <div class="flex items-center gap-2 text-red-700 dark:text-red-400 font-bold text-sm mb-4">
                            <span class="w-6 h-6 rounded-full bg-red-600 text-white text-xs flex items-center justify-center font-extrabold">2</span>
                            Reject Request
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-1.5">Reason for Rejection <span class="text-red-500">*</span></label>
                            <textarea name="feedback" rows="5" placeholder="State why this request cannot be processed..." class="w-full p-3 bg-white dark:bg-zinc-900 border border-red-200 dark:border-zinc-700 rounded-xl text-xs text-slate-800 dark:text-gray-200 focus:outline-none focus:border-red-500" required></textarea>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl transition flex justify-center items-center gap-2 text-sm shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        Reject Request
                    </button>
                </form>
            </div>
        </div>
    @elseif($serviceRequest->project && $serviceRequest->project->current_status === 'Pending Verification')
        <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm">
            <h2 class="text-base font-bold text-[#0033a0] dark:text-blue-400 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Worker Completed Job — Pending Final Admin Verification
            </h2>

            <div class="bg-blue-50/60 dark:bg-zinc-800/50 p-6 rounded-2xl border border-blue-200 dark:border-zinc-700 flex flex-col items-center text-center max-w-xl mx-auto">
                @php
                    $pendingHistory = $serviceRequest->project->histories->where('current_status', 'Pending Verification')->last();
                @endphp
                
                @if($pendingHistory && $pendingHistory->proof_attachment)
                    <div class="mb-5">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Worker Uploaded Proof of Completion</p>
                        <a href="{{ Storage::url($pendingHistory->proof_attachment) }}" target="_blank" class="inline-block p-1 bg-white border border-blue-300 rounded-xl shadow-sm hover:opacity-90 transition">
                            <img src="{{ Storage::url($pendingHistory->proof_attachment) }}" alt="Proof" class="max-w-[240px] rounded-lg object-cover">
                        </a>
                    </div>
                @endif
                
                <form action="{{ route('admin.requests.verify', $serviceRequest->request_id) }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="w-full bg-[#0033a0] hover:bg-[#002480] text-white font-bold py-3 px-6 rounded-xl transition shadow-md flex justify-center items-center gap-2 text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Verify Completion & Close Request
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Per-Request Messaging Channel -->
    @include('partials.request-messages', ['serviceRequest' => $serviceRequest])
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('categorySelect');
    if (!categorySelect) return;

    function updateRecommendations() {
        const selectedOpt = categorySelect.options[categorySelect.selectedIndex];
        const catName = selectedOpt ? (selectedOpt.getAttribute('data-name') || '') : '';
        const words = (catName.match(/\w+/g) || []).filter(w => w.length > 3);

        document.querySelectorAll('.worker-option').forEach(option => {
            const teamName = option.getAttribute('data-team') || '';
            const checkbox = option.querySelector('.worker-checkbox');
            const badge = option.querySelector('.recommended-badge');

            let isRec = false;
            for (const word of words) {
                if (teamName.includes(word)) {
                    isRec = true;
                    break;
                }
            }

            if (isRec) {
                option.classList.add('bg-blue-50/80', 'border-blue-200');
                badge.classList.remove('hidden');
                checkbox.checked = true;
            } else {
                option.classList.remove('bg-blue-50/80', 'border-blue-200');
                badge.classList.add('hidden');
                checkbox.checked = false;
            }
        });
    }

    categorySelect.addEventListener('change', updateRecommendations);
});
</script>
@endpush
@endsection
