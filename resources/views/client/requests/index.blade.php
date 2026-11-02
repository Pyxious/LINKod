@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full flex flex-col font-sans min-h-[calc(100vh-64px)] bg-slate-50/50 dark:bg-[#111111]">
    
    <!-- Top Hero Section (Wide Rectangle Banner) -->
    <div class="bg-[#fffde7] dark:bg-[#18181b] py-8 px-6 md:px-12 border-b border-gray-200/80 dark:border-zinc-800">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">
                    <a href="{{ route('client.dashboard') }}" class="hover:text-[#0033a0] dark:hover:text-blue-400 transition">Portal Home</a>
                    <span>/</span>
                    <span class="text-[#0033a0] dark:text-blue-400 font-bold">Track Requests</span>
                </div>
                <h1 class="text-[#0033a0] dark:text-blue-400 text-2xl sm:text-3xl font-extrabold tracking-tight">My Service Requests</h1>
                <p class="text-[#0033a0]/80 dark:text-gray-400 text-xs sm:text-sm font-medium mt-1">Track live job progress, approve material lists, and manage your submitted requisitions.</p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('client.requests.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-[#0038A8] hover:bg-[#002B82] text-white rounded-xl font-bold text-xs sm:text-sm transition shadow-sm gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Submit New Request</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="max-w-6xl w-full mx-auto px-4 sm:px-6 md:px-8 py-8 flex-1 space-y-6">
        
        <!-- Action Required Callout Banner (Shown only when client action is needed) -->
        @if(($actionRequiredCount ?? 0) > 0)
            <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800/80 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 shadow-2xs">
                <div class="flex items-start sm:items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/60 border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-xs sm:text-sm font-extrabold text-amber-900 dark:text-amber-200">
                            {{ $actionRequiredCount }} {{ Str::plural('request', $actionRequiredCount) }} {{ $actionRequiredCount === 1 ? 'requires' : 'require' }} your attention
                        </h2>
                        <p class="text-xs text-amber-800/90 dark:text-amber-300/80 mt-0.5">
                            Review pending List of Materials approvals, schedule confirmations, or rate completed work to keep your requests moving smoothly.
                        </p>
                    </div>
                </div>
                <a href="{{ route('client.requests.index', ['status' => 'action_required']) }}"
                   class="shrink-0 inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                    <span>Show Action Needed ({{ $actionRequiredCount }})</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        @endif

        <!-- Summary Cards Grid (2 per row on mobile, 4 cols on desktop) -->
        <div data-realtime-container="client-requests-kpis" class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4 md:gap-5">
            <!-- Total Requests -->
            <a href="{{ route('client.requests.index', ['status' => 'all']) }}" class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 hover:border-[#0038A8]/50 dark:hover:border-blue-500/50 p-4 sm:p-5 shadow-2xs hover:shadow-sm transition group">
                <span class="text-[#254378] dark:text-blue-300 text-xs sm:text-sm font-semibold block mb-2">Total Requests</span>
                <span id="totalRequestsCount" class="text-[#042B74] dark:text-white text-2xl sm:text-3xl font-extrabold block leading-none">{{ $totalRequests }}</span>
                <span class="text-[11px] text-gray-400 dark:text-gray-500 mt-1.5 block">All submitted requisitions</span>
            </a>

            <!-- Pending / Preparing -->
            <a href="{{ route('client.requests.index', ['status' => 'pending']) }}" class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 hover:border-amber-400/70 p-4 sm:p-5 shadow-2xs hover:shadow-sm transition group">
                <span class="text-[#254378] dark:text-blue-300 text-xs sm:text-sm font-semibold block mb-2">Pending &amp; Scheduled</span>
                <span id="pendingRequestsCount" class="text-[#042B74] dark:text-white text-2xl sm:text-3xl font-extrabold block leading-none">{{ $pendingCount }}</span>
                <span class="text-[11px] text-gray-400 dark:text-gray-500 mt-1.5 block">Under review, approved, or awaiting materials</span>
            </a>

            <!-- In Progress -->
            <a href="{{ route('client.requests.index', ['status' => 'in_progress']) }}" class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 hover:border-blue-400/70 p-4 sm:p-5 shadow-2xs hover:shadow-sm transition group">
                <span class="text-[#254378] dark:text-blue-300 text-xs sm:text-sm font-semibold block mb-2">In Progress</span>
                <span id="inProgressRequestsCount" class="text-[#042B74] dark:text-white text-2xl sm:text-3xl font-extrabold block leading-none">{{ $inProgressCount }}</span>
                <span class="text-[11px] text-gray-400 dark:text-gray-500 mt-1.5 block">Maintenance work actively underway</span>
            </a>

            <!-- Completed -->
            <a href="{{ route('client.requests.index', ['status' => 'completed']) }}" class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 hover:border-emerald-400/70 p-4 sm:p-5 shadow-2xs hover:shadow-sm transition group">
                <span class="text-[#254378] dark:text-blue-300 text-xs sm:text-sm font-semibold block mb-2">Completed</span>
                <span id="completedRequestsCount" class="text-[#042B74] dark:text-white text-2xl sm:text-3xl font-extrabold block leading-none">{{ $completedCount }}</span>
                <span class="text-[11px] text-gray-400 dark:text-gray-500 mt-1.5 block">Accomplished &amp; accepted jobs</span>
            </a>
        </div>

        <!-- Main Outer Box (Soft Blue Container with Border) -->
        <div class="bg-[#EBF3FE] dark:bg-[#151d2a] border border-[#7DAAF4] dark:border-blue-800 rounded-2xl md:rounded-3xl p-4 sm:p-6 md:p-8 shadow-2xs">
            
            <!-- Filter Bar & Search Form -->
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 mb-6">
                
                <!-- Status Filter Pills (Horizontally scrollable on mobile) -->
                @php
                    $currentStatus = strtolower(request('status', 'all'));
                    $searchParam = request('search', request('tracking_number'));
                @endphp
                <div id="statusFilterPills" class="flex items-center gap-2 overflow-x-auto pb-1 lg:pb-0 scrollbar-none shrink-0 max-w-full">
                    <a data-status-pill="all" href="{{ route('client.requests.index', array_filter(['status' => 'all', 'search' => $searchParam])) }}" 
                       class="px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition whitespace-nowrap shadow-2xs {{ $currentStatus === 'all' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        All ({{ $totalRequests }})
                    </a>

                    @if(($actionRequiredCount ?? 0) > 0 || $currentStatus === 'action_required')
                        <a data-status-pill="action_required" href="{{ route('client.requests.index', array_filter(['status' => 'action_required', 'search' => $searchParam])) }}" 
                           class="px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-bold transition whitespace-nowrap shadow-2xs inline-flex items-center gap-1.5 {{ $currentStatus === 'action_required' ? 'bg-amber-600 text-white' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700 hover:bg-amber-100' }}">
                            <span class="w-2 h-2 rounded-full {{ $currentStatus === 'action_required' ? 'bg-white' : 'bg-amber-500 animate-pulse' }}"></span>
                            <span>Needs Action ({{ $actionRequiredCount ?? 0 }})</span>
                        </a>
                    @endif

                    <a data-status-pill="pending" href="{{ route('client.requests.index', array_filter(['status' => 'pending', 'search' => $searchParam])) }}" 
                       class="px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition whitespace-nowrap shadow-2xs {{ $currentStatus === 'pending' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        Pending ({{ $pendingCount }})
                    </a>

                    <a data-status-pill="in_progress" href="{{ route('client.requests.index', array_filter(['status' => 'in_progress', 'search' => $searchParam])) }}" 
                       class="px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition whitespace-nowrap shadow-2xs {{ $currentStatus === 'in_progress' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        In Progress ({{ $inProgressCount }})
                    </a>

                    <a data-status-pill="completed" href="{{ route('client.requests.index', array_filter(['status' => 'completed', 'search' => $searchParam])) }}" 
                       class="px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition whitespace-nowrap shadow-2xs {{ $currentStatus === 'completed' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        Completed ({{ $completedCount }})
                    </a>

                    <a data-status-pill="cancelled" href="{{ route('client.requests.index', array_filter(['status' => 'cancelled', 'search' => $searchParam])) }}" 
                       class="px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition whitespace-nowrap shadow-2xs {{ $currentStatus === 'cancelled' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        Cancelled
                    </a>

                    <a data-status-pill="rejected" href="{{ route('client.requests.index', array_filter(['status' => 'rejected', 'search' => $searchParam])) }}" 
                       class="px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition whitespace-nowrap shadow-2xs {{ $currentStatus === 'rejected' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        Disapproved
                    </a>
                </div>

                <!-- Live Search Input Field -->
                <form id="clientRequestsSearchForm" method="GET" action="{{ route('client.requests.index') }}" class="w-full lg:w-80 shrink-0">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="relative">
                        <svg id="clientRequestsSearchIcon" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <svg id="clientRequestsSearchSpinner" class="hidden animate-spin w-4 h-4 text-[#0038A8] dark:text-blue-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <input type="text" 
                               id="clientRequestsLiveSearch"
                               name="search" 
                               value="{{ $searchParam }}" 
                               autocomplete="off"
                               placeholder="Search code (e.g. CMS, PLS-012), title, or room..." 
                               class="w-full pl-9 pr-8 py-2 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs sm:text-sm text-gray-800 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:border-[#0038A8] focus:ring-1 focus:ring-[#0038A8] shadow-2xs">
                        <button type="button"
                                id="clientRequestsSearchClear"
                                title="Clear search"
                                class="{{ $searchParam ? '' : 'hidden' }} absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs font-bold cursor-pointer">
                            ✕
                        </button>
                    </div>
                </form>
            </div>

            <!-- Request Cards Feed -->
            <div id="requestsCardsContainer" data-realtime-container="client-requests-list" data-client-id="{{ auth()->user()->client?->client_id }}" class="space-y-3.5 sm:space-y-4">
                @forelse($requests as $r)
                    @php
                        $rawStatus = strtolower($r->current_status ?? 'submitted');
                        $needsBomApproval = ($r->bom_status === 'awaiting_client') || in_array($rawStatus, [
                            'bom verified (awaiting client approval)',
                            'list of materials verified (awaiting client approval)',
                        ]);
                        $needsScheduleApproval = ($r->schedule_status === 'pending_client_approval') && !in_array($rawStatus, ['cancelled', 'rejected', 'completed']);
                        $needsRating = ($rawStatus === 'completed') && !$r->evaluation;
                        $hasActionRequired = $needsBomApproval || $needsScheduleApproval || $needsRating;

                        $displayStatus = match($rawStatus) {
                            'rejected' => 'Disapproved',
                            'awaiting verification of bill of materials', 'awaiting verification of list of materials' => 'Approved',
                            'bom verified (awaiting client approval)', 'list of materials verified (awaiting client approval)' => 'Materials Verified',
                            'awaiting materials', 'waiting for materials' => 'Awaiting Materials',
                            'materials arrived' => 'Materials Arrived',
                            'pending verification' => 'Final Check',
                            default => ucfirst($r->current_status ?? 'Submitted')
                        };

                        $statusBadgeClass = match(true) {
                            $rawStatus === 'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                            in_array($rawStatus, ['cancelled', 'rejected']) => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
                            in_array($rawStatus, ['in progress', 'pending verification']) => 'bg-blue-50 text-[#0038A8] border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800',
                            $needsBomApproval => 'bg-indigo-50 text-indigo-700 border-indigo-300 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800',
                            in_array($rawStatus, ['approved', 'schedule confirmed', 'materials arrived', 'awaiting verification of bill of materials', 'awaiting verification of list of materials']) => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-800',
                            default => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
                        };

                        // Compute 4-step progress (1: Submitted, 2: Approved/Scheduled/Materials, 3: In Progress, 4: Completed)
                        $isClosedNegative = in_array($rawStatus, ['cancelled', 'rejected']);
                        $stepNumber = match(true) {
                            $rawStatus === 'completed' => 4,
                            in_array($rawStatus, ['in progress', 'pending verification']) => 3,
                            in_array($rawStatus, ['approved', 'schedule set', 'schedule confirmed', 'awaiting verification of bill of materials', 'awaiting verification of list of materials', 'bom verified (awaiting client approval)', 'list of materials verified (awaiting client approval)', 'awaiting materials', 'waiting for materials', 'materials arrived', 'on hold']) => 2,
                            default => 1,
                        };
                        $stepHint = match(true) {
                            $rawStatus === 'cancelled' => 'Withdrawn by client',
                            $rawStatus === 'rejected' => 'Disapproved by GSO — view reason inside',
                            $needsRating => 'Completed — please submit your rating',
                            $rawStatus === 'completed' => 'Completed & evaluated',
                            $rawStatus === 'pending verification' => 'Work finished — awaiting final GSO acceptance',
                            $rawStatus === 'in progress' => 'Step 3 of 4 • Maintenance team working on-site',
                            $needsBomApproval => 'Step 2 of 4 • Waiting for your List of Materials approval',
                            $needsScheduleApproval => 'Step 2 of 4 • Waiting for your schedule confirmation',
                            $rawStatus === 'materials arrived' => 'Step 2 of 4 • Materials arrived, ready for on-site work',
                            in_array($rawStatus, ['awaiting materials', 'waiting for materials']) => 'Step 2 of 4 • Materials approved, preparing supplies',
                            $rawStatus === 'on hold' => 'Step 2 of 4 • Temporarily on hold',
                            $stepNumber === 2 => 'Step 2 of 4 • Approved & scheduling deployment',
                            default => 'Step 1 of 4 • Submitted for GSO review',
                        };
                    @endphp
                    
                    <div class="bg-white dark:bg-[#1c1c1e] border {{ $hasActionRequired ? 'border-amber-300 dark:border-amber-700 ring-1 ring-amber-200/70 dark:ring-amber-900/40' : 'border-gray-200 dark:border-zinc-800' }} rounded-xl md:rounded-2xl p-4 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-2xs hover:shadow-sm transition" data-request-card-id="{{ $r->request_id }}">
                        
                        <div class="flex flex-col gap-1.5 flex-1 min-w-0">
                            <!-- Top Line: Unified Tracking Code, Category & Action/Schedule Pills -->
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="bg-blue-50 dark:bg-blue-950/60 text-[#0038A8] dark:text-blue-300 font-mono font-extrabold px-2.5 py-0.5 rounded-md border border-blue-200 dark:border-blue-800 text-[11px] sm:text-xs" title="Requisition #{{ str_pad($r->request_id, 4, '0', STR_PAD_LEFT) }}">
                                    {{ $r->tracking_code }} <span class="text-blue-400 dark:text-blue-500 font-normal">• #{{ str_pad($r->request_id, 4, '0', STR_PAD_LEFT) }}</span>
                                </span>

                                @if($r->category)
                                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-gray-300">
                                        {{ $r->category->display_name ?? $r->category->category_name }}
                                    </span>
                                @endif

                                @if($r->scheduled_date && !$isClosedNegative)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                        <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Visit: {{ \Carbon\Carbon::parse($r->scheduled_date)->format('M d') }} ({{ $r->scheduled_time_window ?? 'Whole Day' }})
                                    </span>
                                @endif

                                @if($needsBomApproval)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-extrabold bg-indigo-100 text-indigo-800 border border-indigo-300 dark:bg-indigo-950/70 dark:text-indigo-200 dark:border-indigo-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-ping"></span>
                                        Action Needed: Approve Materials
                                    </span>
                                @elseif($needsScheduleApproval)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-950/70 dark:text-amber-200 dark:border-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-ping"></span>
                                        Action Needed: Confirm Schedule
                                    </span>
                                @elseif($needsRating)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-950/70 dark:text-amber-200 dark:border-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                                        Action Needed: Rate Service
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Title -->
                            <h3 class="text-gray-900 dark:text-white font-bold text-sm sm:text-base md:text-lg leading-snug">
                                <a href="{{ route('client.requests.show', $r->request_id) }}" class="hover:text-[#0038A8] dark:hover:text-blue-400 transition">
                                    {{ $r->title ?? ($r->category->category_name ?? 'Service Request') }}
                                </a>
                            </h3>
                            
                            <!-- Department / Location & Submission Timestamp -->
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 text-xs text-gray-600 dark:text-gray-400">
                                <span class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ $r->campus ?? 'BU Main' }}{{ $r->location ? ' — ' . $r->location : '' }}
                                </span>
                                <span class="hidden sm:inline text-gray-300 dark:text-zinc-600">•</span>
                                <span class="text-[11px] sm:text-xs text-gray-400 dark:text-gray-500">
                                    Submitted {{ \Carbon\Carbon::parse($r->submitted_at)->format('M d, Y h:i A') }}
                                </span>
                            </div>

                            <!-- Mini 4-Segment Progress Indicator -->
                            <div class="pt-1.5 flex items-center gap-3 max-w-md">
                                @if(!$isClosedNegative)
                                    <div class="flex items-center gap-1 w-28 sm:w-36 shrink-0" aria-hidden="true">
                                        @for($step = 1; $step <= 4; $step++)
                                            <span class="h-1.5 flex-1 rounded-full transition-all {{ $step <= $stepNumber ? ($stepNumber === 4 ? 'bg-emerald-500' : 'bg-[#0038A8] dark:bg-blue-400') : 'bg-gray-200 dark:bg-zinc-700' }}"></span>
                                        @endfor
                                    </div>
                                @endif
                                <span class="text-[11px] font-semibold {{ $hasActionRequired ? 'text-amber-700 dark:text-amber-300' : ($isClosedNegative ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-gray-400') }} truncate">
                                    {{ $stepHint }}
                                </span>
                            </div>
                        </div>

                        <!-- Right Column: Color-Coded Status Badge & Primary Action -->
                        <div class="shrink-0 flex flex-row md:flex-col items-center md:items-end justify-between md:justify-center gap-2.5 pt-3 md:pt-0 border-t md:border-t-0 border-gray-100 dark:border-zinc-800/80">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold border whitespace-nowrap {{ $statusBadgeClass }}">
                                @if(in_array($rawStatus, ['in progress', 'pending verification']))
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#0038A8] dark:bg-blue-400 animate-pulse"></span>
                                @elseif($rawStatus === 'completed')
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @endif
                                <span>{{ $displayStatus }}</span>
                            </span>

                            <div class="flex items-center gap-2">
                                @if($needsRating)
                                    <a href="{{ route('client.evaluations.create', $r->request_id) }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition shadow-xs whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        <span>Rate Service</span>
                                    </a>
                                @endif

                                <a href="{{ route('client.requests.show', $r->request_id) }}" class="inline-flex items-center justify-center gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 {{ $needsBomApproval ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-[#0038A8] hover:bg-[#002B82]' }} text-white rounded-xl font-bold text-xs sm:text-sm transition shadow-xs whitespace-nowrap">
                                    <span>{{ $needsBomApproval ? 'Review Materials' : 'Track Progress' }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                        
                    </div>
                @empty
                    <div id="noRequestsFound" class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl p-8 sm:p-12 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-zinc-800 text-[#0038A8] dark:text-blue-400 flex items-center justify-center mx-auto mb-3.5">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <h3 class="text-slate-900 dark:text-white font-bold text-base">No matching requests found</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-xs sm:text-sm mt-1 max-w-md mx-auto">
                            @if($searchParam || $currentStatus !== 'all')
                                No service requests matched your active filter or search query. Try viewing all requests or clearing your search.
                            @else
                                You haven't submitted any service requisitions yet. Submit a request anytime to get started.
                            @endif
                        </p>
                        <div class="mt-5 flex items-center justify-center gap-3 flex-wrap">
                            @if($searchParam || $currentStatus !== 'all')
                                <a href="{{ route('client.requests.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-gray-200 rounded-xl text-xs font-bold transition">
                                    Reset Filters
                                </a>
                            @endif
                            <a href="{{ route('client.requests.create') }}" class="px-5 py-2 bg-[#0038A8] hover:bg-[#002B82] text-white rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Create Request</span>
                            </a>
                        </div>
                    </div>
                @endforelse

                <!-- Pagination -->
                @if($requests instanceof \Illuminate\Pagination\AbstractPaginator && $requests->hasPages())
                    <div class="pt-6">
                        {{ $requests->links() }}
                    </div>
                @endif
            </div>

        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('requestsCardsContainer');
    const clientId = container ? container.getAttribute('data-client-id') : null;

    // Live Search Updates (instant as-you-type filtering with AbortController)
    const searchForm = document.getElementById('clientRequestsSearchForm');
    const searchInput = document.getElementById('clientRequestsLiveSearch');
    const clearBtn = document.getElementById('clientRequestsSearchClear');
    const searchIcon = document.getElementById('clientRequestsSearchIcon');
    const searchSpinner = document.getElementById('clientRequestsSearchSpinner');

    if (searchForm && searchInput && container) {
        let debounceTimer = null;
        let activeAbortController = null;

        const setLoading = (loading) => {
            if (searchIcon && searchSpinner) {
                searchIcon.classList.toggle('hidden', loading);
                searchSpinner.classList.toggle('hidden', !loading);
            }
        };

        const syncFilterLinksAndUrl = (rawTerm) => {
            const term = rawTerm.trim();
            if (clearBtn) {
                clearBtn.classList.toggle('hidden', rawTerm.length === 0);
            }

            const currentUrl = new URL(window.location.href);
            if (term) {
                currentUrl.searchParams.set('search', term);
            } else {
                currentUrl.searchParams.delete('search');
            }
            currentUrl.searchParams.delete('tracking_number');
            currentUrl.searchParams.delete('page');

            window.history.replaceState({}, '', currentUrl.toString());

            document.querySelectorAll('[data-status-pill]').forEach((pill) => {
                const pillStatus = pill.getAttribute('data-status-pill');
                const pillUrl = new URL(searchForm.action, window.location.origin);
                if (pillStatus) {
                    pillUrl.searchParams.set('status', pillStatus);
                }
                if (term) {
                    pillUrl.searchParams.set('search', term);
                }
                pill.setAttribute('href', pillUrl.toString());
            });

            return currentUrl.toString();
        };

        const runLiveSearch = (immediate = false) => {
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }

            const execute = async () => {
                const targetUrl = syncFilterLinksAndUrl(searchInput.value);

                if (activeAbortController) {
                    activeAbortController.abort();
                }
                activeAbortController = new AbortController();
                setLoading(true);

                try {
                    const res = await fetch(targetUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        },
                        signal: activeAbortController.signal
                    });

                    if (!res.ok) return;

                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const updatedContainer = doc.getElementById('requestsCardsContainer');
                    if (updatedContainer) {
                        container.innerHTML = updatedContainer.innerHTML;
                    }
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        console.warn('[LiveSearch] Fetch warning:', err);
                    }
                } finally {
                    if (!activeAbortController.signal.aborted) {
                        setLoading(false);
                    }
                }
            };

            if (immediate) {
                execute();
            } else {
                if (clearBtn) {
                    clearBtn.classList.toggle('hidden', searchInput.value.length === 0);
                }
                debounceTimer = setTimeout(execute, 150);
            }
        };

        searchInput.addEventListener('input', () => runLiveSearch(false));

        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            runLiveSearch(true);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                searchInput.focus();
                runLiveSearch(true);
            });
        }
    }

    if (window.Echo && clientId) {
        try {
            window.Echo.channel('linkod.updates')
                .listen('.RequestUpdated', (e) => {
                    if (window.LINKodTriggerRealtimeRefresh) {
                        window.LINKodTriggerRealtimeRefresh(e);
                    }
                });
        } catch (err) {
            console.warn('[Reverb] Channel subscription warning:', err);
        }
    }

    if (window.supabaseClient && clientId) {
        window.supabaseClient
            .channel(`client-requests-${clientId}`)
            .on(
                'postgres_changes',
                {
                    event: 'INSERT',
                    schema: 'public',
                    table: 'request',
                    filter: `client_id=eq.${clientId}`
                },
                (payload) => {
                    const req = payload.new;
                    if (!req) return;

                    // 1. Update summary counters
                    const totalEl = document.getElementById('totalRequestsCount');
                    if (totalEl) totalEl.textContent = (parseInt(totalEl.textContent) || 0) + 1;
                    const pendingEl = document.getElementById('pendingRequestsCount');
                    if (pendingEl) pendingEl.textContent = (parseInt(pendingEl.textContent) || 0) + 1;

                    // 2. Remove empty state placeholder
                    const emptyState = document.getElementById('noRequestsFound');
                    if (emptyState) emptyState.remove();

                    // 3. Prepend newly added card
                    if (container) {
                        const card = document.createElement('div');
                        card.className = 'bg-white dark:bg-[#1c1c1e] border-2 border-blue-400 dark:border-blue-600 rounded-xl md:rounded-2xl p-4 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4 shadow-lg animate-fadeIn';
                        card.setAttribute('data-request-card-id', req.request_id);

                        const paddedId = String(req.request_id).padStart(3, '0');
                        const padded4Id = String(req.request_id).padStart(4, '0');
                        const locationText = req.campus ? `${req.campus} ${req.location ? '— ' + req.location : ''}` : (req.location || 'BU Main');

                        card.innerHTML = `
                            <div class="flex flex-col gap-1.5 flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="bg-blue-50 dark:bg-blue-950/60 text-[#0038A8] dark:text-blue-300 font-mono font-extrabold px-2.5 py-0.5 rounded-md border border-blue-200 dark:border-blue-800 text-[11px] sm:text-xs">
                                        REQ-${paddedId} <span class="text-blue-400 font-normal">• #${padded4Id}</span>
                                    </span>
                                    <span class="px-2 py-0.5 text-[10px] font-extrabold bg-blue-100 dark:bg-blue-900 text-[#0038A8] dark:text-blue-300 rounded uppercase">New</span>
                                </div>
                                <h3 class="text-gray-900 dark:text-white font-bold text-sm sm:text-base md:text-lg leading-snug">
                                    ${req.title || 'New Service Request'}
                                </h3>
                                <p class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                    ${locationText} • Submitted just now
                                </p>
                            </div>
                            <div class="shrink-0 flex flex-row md:flex-col items-center md:items-end justify-between md:justify-center gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-gray-100 dark:border-zinc-800/80">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-extrabold border bg-amber-50 text-amber-700 border-amber-200">
                                    <span>Submitted</span>
                                </span>
                                <a href="/client/requests/${req.request_id}" class="inline-flex items-center justify-center gap-1.5 px-5 py-2 sm:py-2.5 bg-[#0038A8] hover:bg-[#002B82] text-white rounded-xl font-bold text-xs sm:text-sm transition shadow-xs whitespace-nowrap">
                                    <span>Track Progress</span>
                                </a>
                            </div>
                        `;
                        container.prepend(card);
                    }

                    // 4. Toast alert
                    if (window.LINKodRealtime) {
                        window.LINKodRealtime.showNotificationToast(
                            'New Request Submitted',
                            `Requisition #${req.request_id}: "${req.title}"`,
                            `/client/requests/${req.request_id}`
                        );
                    }

                    if (window.LINKodTriggerRealtimeRefresh) {
                        window.LINKodTriggerRealtimeRefresh({ request_id: Number(req.request_id) });
                    }
                }
            )
            .subscribe();
    }
});
</script>
@endsection
