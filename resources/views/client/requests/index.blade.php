@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full flex flex-col font-sans min-h-[calc(100vh-64px)] bg-slate-50/50 dark:bg-[#111111]">
    
    <!-- Top Hero Section (Wide Rectangle Banner) -->
    <div class="bg-[#fffde7] dark:bg-[#18181b] py-8 px-6 md:px-12">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-[#0033a0] dark:text-blue-400 text-3xl font-bold tracking-tight">My Requests</h1>
                <p class="text-[#0033a0]/80 dark:text-gray-400 text-sm font-medium mt-1">Track and manage your service job requests</p>
            </div>
            <div>
                <a href="{{ route('client.requests.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-[#0038A8] hover:bg-[#002B82] text-white rounded-lg font-semibold text-sm transition shadow-xs gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>New Request</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="max-w-6xl w-full mx-auto px-6 md:px-8 py-8 flex-1">
        
        <!-- Summary Cards Grid (2 per row on mobile, 4 cols on desktop) -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4 md:gap-5 mb-8">
            <!-- Total Requests -->
            <div class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 p-4 sm:p-6 shadow-2xs">
                <span class="text-[#254378] dark:text-blue-300 text-xs sm:text-sm font-semibold block mb-1.5 sm:mb-2">Total Requests</span>
                <span id="totalRequestsCount" class="text-[#042B74] dark:text-white text-2xl sm:text-3xl font-bold block leading-none">{{ $totalRequests }}</span>
            </div>

            <!-- Pending -->
            <div class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 p-4 sm:p-6 shadow-2xs">
                <span class="text-[#254378] dark:text-blue-300 text-xs sm:text-sm font-semibold block mb-1.5 sm:mb-2">Pending</span>
                <span id="pendingRequestsCount" class="text-[#042B74] dark:text-white text-2xl sm:text-3xl font-bold block leading-none">{{ $pendingCount }}</span>
            </div>

            <!-- In Progress -->
            <div class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 p-4 sm:p-6 shadow-2xs">
                <span class="text-[#254378] dark:text-blue-300 text-xs sm:text-sm font-semibold block mb-1.5 sm:mb-2">In Progress</span>
                <span id="inProgressRequestsCount" class="text-[#042B74] dark:text-white text-2xl sm:text-3xl font-bold block leading-none">{{ $inProgressCount }}</span>
            </div>

            <!-- Completed -->
            <div class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 p-4 sm:p-6 shadow-2xs">
                <span class="text-[#254378] dark:text-blue-300 text-xs sm:text-sm font-semibold block mb-1.5 sm:mb-2">Completed</span>
                <span id="completedRequestsCount" class="text-[#042B74] dark:text-white text-2xl sm:text-3xl font-bold block leading-none">{{ $completedCount }}</span>
            </div>
        </div>

        <!-- Main Outer Box (Soft Blue Container with Border) -->
        <div class="bg-[#EBF3FE] dark:bg-[#151d2a] border border-[#7DAAF4] dark:border-blue-800 rounded-2xl md:rounded-3xl p-4 sm:p-6 md:p-8 shadow-2xs">
            
            <!-- Filter Bar & Search Form -->
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 mb-6">
                
                <!-- Status Filter Pills (Horizontally scrollable on mobile) -->
                @php $currentStatus = request('status', 'all'); @endphp
                <div class="flex items-center gap-2 overflow-x-auto pb-1 lg:pb-0 scrollbar-none shrink-0 max-w-full">
                    <a href="{{ route('client.requests.index', array_filter(['status' => 'all', 'search' => request('search')])) }}" 
                       class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition whitespace-nowrap shadow-2xs {{ $currentStatus === 'all' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        All
                    </a>

                    <a href="{{ route('client.requests.index', array_filter(['status' => 'pending', 'search' => request('search')])) }}" 
                       class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition whitespace-nowrap shadow-2xs {{ $currentStatus === 'pending' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        Pending
                    </a>

                    <a href="{{ route('client.requests.index', array_filter(['status' => 'in_progress', 'search' => request('search')])) }}" 
                       class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition whitespace-nowrap shadow-2xs {{ $currentStatus === 'in_progress' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        In Progress
                    </a>

                    <a href="{{ route('client.requests.index', array_filter(['status' => 'completed', 'search' => request('search')])) }}" 
                       class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition whitespace-nowrap shadow-2xs {{ $currentStatus === 'completed' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        Completed
                    </a>

                    <a href="{{ route('client.requests.index', array_filter(['status' => 'follow_up', 'search' => request('search')])) }}" 
                       class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition whitespace-nowrap shadow-2xs {{ $currentStatus === 'follow_up' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        Follow Up
                    </a>

                    <a href="{{ route('client.requests.index', array_filter(['status' => 'cancelled', 'search' => request('search')])) }}" 
                       class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition whitespace-nowrap shadow-2xs {{ $currentStatus === 'cancelled' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        Cancelled
                    </a>

                    <a href="{{ route('client.requests.index', array_filter(['status' => 'rejected', 'search' => request('search')])) }}" 
                       class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold transition whitespace-nowrap shadow-2xs {{ $currentStatus === 'rejected' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        Disapproved
                    </a>
                </div>

                <!-- Search Input Field -->
                <form method="GET" action="{{ route('client.requests.index') }}" class="w-full lg:w-72">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search requests..." 
                               class="w-full px-4 py-2 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs sm:text-sm text-gray-800 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:border-[#0038A8] shadow-2xs">
                        @if(request('search'))
                            <a href="{{ route('client.requests.index', array_filter(['status' => request('status')])) }}" 
                               class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">
                               ✕
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Request Cards Feed (Matches Mobile Mockup Feed) -->
            <div id="requestsCardsContainer" data-client-id="{{ auth()->user()->client?->client_id }}" class="space-y-3 sm:space-y-4">
                @forelse($requests as $r)
                    @php
                        $catName = strtolower($r->category->category_name ?? '');
                        $prefix = match(true) {
                            str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') || str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'CMS',
                            str_contains($catName, 'plumbing') => 'PLS',
                            str_contains($catName, 'painting') || str_contains($catName, 'paint') => 'PAS',
                            str_contains($catName, 'janitorial') => 'JS',
                            str_contains($catName, 'landscaping') => 'LS',
                            str_contains($catName, 'manpower') || str_contains($catName, 'event') => 'MAN',
                            default => 'REQ'
                        };
                        $displayStatus = match(strtolower($r->current_status ?? '')) {
                            'rejected' => 'Disapproved',
                            default => ucfirst($r->current_status ?? 'Pending')
                        };
                    @endphp
                    
                    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl md:rounded-2xl p-4 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4 shadow-2xs hover:shadow-xs transition" data-request-card-id="{{ $r->request_id }}">
                        
                        <div class="flex flex-col gap-1 flex-1 min-w-0">
                            <!-- Top Line: Code & Scheduled Visit Pill -->
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="bg-blue-50 dark:bg-blue-950/60 text-[#0038A8] dark:text-blue-300 font-mono font-extrabold px-2.5 py-0.5 rounded-md border border-blue-200 dark:border-blue-800 text-[11px] sm:text-xs">
                                    {{ $prefix }}-{{ str_pad($r->request_id, 3, '0', STR_PAD_LEFT) }}
                                </span>
                                @if($r->scheduled_date && $r->is_routine)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300">
                                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Visit: {{ \Carbon\Carbon::parse($r->scheduled_date)->format('M d') }} ({{ $r->scheduled_time_window ?? 'Whole Day' }})
                                        @if($r->schedule_status === 'pending_client')
                                            <span class="text-amber-600 font-extrabold animate-pulse">Action Required</span>
                                        @endif
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Title -->
                            <h3 class="text-gray-900 dark:text-white font-bold text-sm sm:text-base md:text-lg leading-snug">
                                {{ $r->title ?? ($r->category->category_name ?? 'Service Request') }}
                            </h3>
                            
                            <!-- Department / Location -->
                            <p class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mt-1">
                                {{ $r->campus ?? 'BU Main' }} {{ $r->location ? '— ' . $r->location : '' }}
                            </p>
                            
                            <!-- Submission Date & Time -->
                            <p class="text-[11px] sm:text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                Submitted: {{ \Carbon\Carbon::parse($r->submitted_at)->format('F d, Y h:i A') }}
                            </p>
                        </div>

                        <!-- Right Column: Status & Action Button -->
                        <div class="flex-shrink-0 flex flex-col items-start md:items-end justify-center gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-gray-100 dark:border-zinc-800/80">
                            <span class="inline-flex items-center gap-1 text-xs sm:text-sm font-bold text-[#0038A8] dark:text-blue-400">
                                <span>{{ $displayStatus }}</span>
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                            <a href="{{ route('client.requests.show', $r->request_id) }}" class="w-full md:w-auto inline-flex items-center justify-center px-5 py-2 sm:py-2.5 bg-[#0038A8] hover:bg-[#002B82] text-white rounded-lg font-bold text-xs sm:text-sm transition shadow-xs whitespace-nowrap">
                                View Details
                            </a>
                        </div>
                        
                    </div>
                @empty
                    <div id="noRequestsFound" class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-8 sm:p-12 text-center">
                        <div class="text-gray-300 dark:text-zinc-600 mb-3">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <h3 class="text-gray-800 dark:text-white font-bold text-base">No requests found</h3>
                        <p class="text-gray-400 text-xs mt-1">There are no service requests matching your selected filter or search term.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($requests->hasPages())
                <div class="pt-6">
                    {{ $requests->links() }}
                </div>
            @endif

        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('requestsCardsContainer');
    const clientId = container ? container.getAttribute('data-client-id') : null;

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
                        const locationText = req.campus ? `${req.campus} ${req.location ? '— ' + req.location : ''}` : (req.location || 'BU Main');

                        card.innerHTML = `
                            <div class="flex flex-col gap-1 flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <span class="bg-blue-50 dark:bg-blue-950/60 text-[#0038A8] dark:text-blue-300 font-mono font-extrabold px-2.5 py-0.5 rounded-md border border-blue-200 dark:border-blue-800 text-[11px] sm:text-xs">
                                        REQ-${paddedId}
                                    </span>
                                </div>
                                <h3 class="text-gray-900 dark:text-white font-bold text-sm sm:text-base md:text-lg leading-snug">
                                    ${req.title || 'New Service Request'}
                                </h3>
                                <p class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mt-1">
                                    ${locationText}
                                </p>
                                <p class="text-[11px] sm:text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                    Submitted: Just now
                                </p>
                            </div>
                            <div class="flex-shrink-0 flex flex-col items-start md:items-end justify-center gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-gray-100 dark:border-zinc-800/80">
                                <span class="inline-flex items-center gap-1 text-xs sm:text-sm font-bold text-[#0038A8] dark:text-blue-400">
                                    <span>Submitted</span>
                                    <span class="px-1.5 py-0.5 text-[9px] font-extrabold bg-blue-100 dark:bg-blue-900 text-[#0038A8] dark:text-blue-300 rounded uppercase">New</span>
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </span>
                                <a href="/client/requests/${req.request_id}" class="w-full md:w-auto inline-flex items-center justify-center px-5 py-2 sm:py-2.5 bg-[#0038A8] hover:bg-[#002B82] text-white rounded-lg font-bold text-xs sm:text-sm transition shadow-xs whitespace-nowrap">
                                    View Details
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
                }
            )
            .subscribe();
    }
});
</script>
@endsection
