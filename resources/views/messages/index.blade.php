@php
    $isClientPortal = request()->is('client/*') || request()->routeIs('client.*');
    $currentRole = $isClientPortal ? 'client' : auth()->user()->role;
    $layoutName = match($currentRole) {
        'admin' => 'layouts.admin',
        'worker' => 'layouts.worker',
        default => 'layouts.client'
    };
    $hasActiveSelection = request()->filled('requestId') || request()->route('requestId');
@endphp
@extends($layoutName)

@if($isClientPortal)
    @section('fullwidth', true)
@endif

@section('content')
<div class="{{ $isClientPortal ? 'w-full max-w-6xl mx-auto px-1 sm:px-6 pt-1 pb-0 sm:py-6 font-sans' : 'space-y-0 sm:space-y-6 font-sans' }}">

    <!-- Dual Pane Chat Layout (Matches Mockup) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch h-[calc(100dvh-64px)] lg:h-[calc(100vh-120px)]">

        <!-- Left Pane: Conversations List (4 Columns on Desktop) -->
        <div class="lg:col-span-4 bg-white dark:bg-zinc-900 rounded-2xl border border-gray-200 dark:border-zinc-800 p-3 sm:p-4 shadow-sm flex flex-col h-full overflow-hidden {{ $hasActiveSelection ? 'hidden lg:flex' : 'flex' }}">
            
            <div class="shrink-0 space-y-3 mb-3">
                <!-- Search & Filter Controls -->
                <form method="GET" action="{{ route(auth()->user()->role . '.messages.index') }}" x-data="{ searching: false }" @submit="searching = true" class="flex items-center gap-2 mb-4">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <div class="relative flex-1">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search..." 
                               class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:outline-none focus:border-[#0033a0]">
                        <svg x-show="!searching" class="w-4 h-4 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <svg x-show="searching" x-cloak class="w-4 h-4 absolute left-3 top-2.5 text-[#0033a0] animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                    <button type="submit" :disabled="searching" class="p-2.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition shadow-sm disabled:opacity-60" title="Search">
                        <svg x-show="!searching" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <svg x-show="searching" x-cloak class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </button>
                </form>

                <!-- Status Tabs (All, Active, Resolved, Cancelled) -->
                <div class="flex items-center gap-1.5 border-b border-gray-100 dark:border-zinc-800 pb-2 overflow-x-auto">
                    @php
                        $baseUrl = route(auth()->user()->role . '.messages.index');
                        $searchParam = request('search') ? '&search=' . urlencode(request('search')) : '';
                    @endphp
                    <a href="{{ $baseUrl }}?status=all{{ $searchParam }}" class="text-xs font-bold px-2.5 py-1 rounded-lg transition shrink-0 {{ $statusFilter === 'all' ? 'text-[#0033a0] dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 font-extrabold' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400' }}">
                        All
                    </a>
                    <a href="{{ $baseUrl }}?status=active{{ $searchParam }}" class="text-xs font-bold px-2.5 py-1 rounded-lg transition shrink-0 {{ $statusFilter === 'active' ? 'text-[#0033a0] dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 font-extrabold' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400' }}">
                        Active
                    </a>
                    <a href="{{ $baseUrl }}?status=resolved{{ $searchParam }}" class="text-xs font-bold px-2.5 py-1 rounded-lg transition shrink-0 {{ $statusFilter === 'resolved' ? 'text-[#0033a0] dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 font-extrabold' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400' }}">
                        Resolved
                    </a>
                    <a href="{{ $baseUrl }}?status=cancelled{{ $searchParam }}" class="text-xs font-bold px-2.5 py-1 rounded-lg transition shrink-0 {{ $statusFilter === 'cancelled' ? 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/40 font-extrabold' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400' }}">
                        Cancelled
                    </a>
                </div>
            </div>


            <!-- Conversation List Items -->
            <div class="flex-1 min-h-0 space-y-2 overflow-y-auto pr-1">
                @forelse($requests as $req)
                    @php
                        $isSelected = $selectedRequest && $selectedRequest->request_id === $req->request_id;
                        $lastMsg = $req->messages->last();
                        $clientUser = $req->client?->user;
                        $initials = strtoupper(substr($clientUser->first_name ?? 'C', 0, 1) . substr($clientUser->last_name ?? 'L', 0, 1));
                        
                        $catName = strtolower($req->category->category_name ?? '');
                        $isReqCancelled = in_array(strtolower($req->latestHistory?->current_status ?? $req->current_status ?? ''), ['cancelled', 'rejected']);
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
                        $reqCode = $req->requisition_no ?: ($prefix . '-' . str_pad($req->request_id, 3, '0', STR_PAD_LEFT));
                        $unreadThisReq = (!$isSelected && isset($unreadCounts[$req->request_id])) ? $unreadCounts[$req->request_id] : 0;
                    @endphp
                    <a href="{{ route($currentRole . '.messages.index', ['requestId' => $req->request_id, 'status' => $statusFilter]) }}{{ request('search') ? '&search=' . urlencode(request('search')) : '' }}" 
                       class="block p-3 rounded-xl border transition flex items-center gap-3 {{ $isSelected ? 'border-[#0033a0] bg-blue-50/50 dark:bg-blue-950/20 ring-1 ring-[#0033a0]' : ($unreadThisReq > 0 ? 'border-red-300 bg-red-50/30 dark:bg-red-950/20 hover:bg-red-50/60 dark:hover:bg-red-950/40' : 'border-gray-200 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50') }}">
                        @if($isClientPortal)
                            <!-- Client Portal: Requisition Document Icon Badge -->
                            <div class="w-10 h-10 rounded-xl {{ $isSelected ? 'bg-[#0033a0] text-white shadow-xs' : 'bg-blue-50 text-[#0033a0] dark:bg-blue-950/60 dark:text-blue-400' }} flex items-center justify-center font-extrabold text-xs shrink-0 relative">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                @if($unreadThisReq > 0)
                                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center border-2 border-white dark:border-zinc-900 shadow-sm animate-pulse">
                                        {{ $unreadThisReq > 9 ? '9+' : $unreadThisReq }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <!-- Admin/Worker Portal: Client Avatar Icon with Initials -->
                            <div class="w-10 h-10 rounded-full bg-[#0033a0] text-white flex items-center justify-center font-extrabold text-xs shrink-0 shadow-sm relative">
                                @if($clientUser && $clientUser->first_name)
                                    {{ $initials }}
                                @else
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                @endif

                                @if($unreadThisReq > 0)
                                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center border-2 border-white dark:border-zinc-900 shadow-sm animate-pulse">
                                        {{ $unreadThisReq > 9 ? '9+' : $unreadThisReq }}
                                    </span>
                                @endif
                            </div>
                        @endif
                        
                        <!-- Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <div class="flex items-center gap-1.5 truncate">
                                    <span class="font-bold text-xs {{ $unreadThisReq > 0 ? 'text-red-900 dark:text-red-200' : 'text-slate-900 dark:text-white' }} truncate">
                                        {{ $reqCode }}
                                    </span>
                                    @if($isReqCancelled)
                                        <span class="px-1.5 py-0.2 text-[9px] font-extrabold rounded uppercase tracking-wider bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 border border-red-200 dark:border-red-800">
                                            Cancelled
                                        </span>
                                    @elseif(!$isClientPortal)
                                        <span class="px-1.5 py-0.5 text-[9px] font-extrabold rounded uppercase tracking-wider {{ strtolower($req->priority ?? 'low') === 'high' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : (strtolower($req->priority ?? 'low') === 'medium' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300') }}">
                                            {{ $req->priority ?? $req->urgency ?? 'Low' }}
                                        </span>
                                    @endif
                                    @if($unreadThisReq > 0)
                                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-extrabold bg-red-500 text-white shadow-xs">
                                            {{ $unreadThisReq }} new
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[10px] {{ $unreadThisReq > 0 ? 'text-red-600 font-bold' : 'text-gray-400' }} shrink-0">
                                    {{ $lastMsg ? $lastMsg->created_at->diffForHumans(null, true, true) : $req->submitted_at?->diffForHumans(null, true, true) }}
                                </span>
                            </div>

                            @if($isClientPortal)
                                <p class="text-[11px] {{ $unreadThisReq > 0 ? 'font-bold text-slate-800 dark:text-gray-200' : 'font-semibold text-gray-700 dark:text-gray-300' }} truncate mt-0.5">
                                    {{ $req->title ?? 'General Maintenance Request' }}
                                </p>
                            @else
                                <p class="text-[11px] {{ $unreadThisReq > 0 ? 'font-bold text-slate-800 dark:text-gray-200' : 'font-semibold text-gray-600 dark:text-gray-300' }} truncate mt-0.5 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-gray-400 inline shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                    <span>{{ $clientUser ? $clientUser->first_name . ' ' . $clientUser->last_name : 'Client Requestor' }}</span>
                                </p>
                            @endif

                            <p class="text-[10px] {{ $unreadThisReq > 0 ? 'font-bold text-slate-900 dark:text-white' : 'text-gray-400' }} truncate mt-0.5">
                                {{ $lastMsg ? $lastMsg->message : ($req->title ?? 'Chat room created') }}
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-8 text-xs text-gray-400">
                        No requisition conversations found.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Pane: Active Messaging View (Matches Mockup Design) -->
        <div class="lg:col-span-8 bg-white dark:bg-zinc-900 rounded-2xl border-2 border-[#0033a0] dark:border-blue-700 shadow-md overflow-hidden flex flex-col h-full {{ $hasActiveSelection ? 'flex' : 'hidden lg:flex' }}">

            @if($selectedRequest)
                @php
                    $clientUser = $selectedRequest->client?->user;
                    $initials = strtoupper(substr($clientUser->first_name ?? 'J', 0, 1) . substr($clientUser->last_name ?? 'D', 0, 1));
                    $isCancelled = in_array(strtolower($selectedRequest->latestHistory?->current_status ?? $selectedRequest->current_status ?? ''), ['cancelled', 'rejected']);
                    $isResolved = $selectedRequest->isResolved() && !$isCancelled;
                    
                    $catNameSelected = strtolower($selectedRequest->category->category_name ?? '');
                    $prefixSel = match(true) {
                        str_contains($catNameSelected, 'landscaping') => 'LS',
                        str_contains($catNameSelected, 'janitorial') => 'JS',
                        str_contains($catNameSelected, 'carpentry') || str_contains($catNameSelected, 'masonry') => 'CMS',
                        str_contains($catNameSelected, 'plumbing') => 'PLS',
                        str_contains($catNameSelected, 'electrical') || str_contains($catNameSelected, 'mechanical') => 'EMS',
                        str_contains($catNameSelected, 'painting') || str_contains($catNameSelected, 'paint') => 'PAINT',
                        str_contains($catNameSelected, 'manpower') || str_contains($catNameSelected, 'event') => 'MAN',
                        default => 'REQ'
                    };
                    $selectedReqCode = $selectedRequest->requisition_no ?: ($prefixSel . '-' . str_pad($selectedRequest->request_id, 3, '0', STR_PAD_LEFT));

                    // Route for posting message based on user role
                    $storeRoute = match ($currentRole) {
                        'admin'  => route('admin.requests.messages.store', $selectedRequest->request_id),
                        'worker' => route('worker.job-orders.messages.store', $selectedRequest->request_id),
                        default  => route('client.requests.messages.store', $selectedRequest->request_id),
                    };
                    $markReadRoute = match ($currentRole) {
                        'admin'  => route('admin.requests.messages.mark-read', $selectedRequest->request_id),
                        'worker' => route('worker.job-orders.messages.mark-read', $selectedRequest->request_id),
                        default  => route('client.requests.messages.mark-read', $selectedRequest->request_id),
                    };
                @endphp

                @if($isClientPortal)
                    <!-- Client View: Prominent Requisition Header without Client DP/Email -->
                    <div class="bg-[#0033a0] px-4 sm:px-6 py-3.5 w-full flex items-center justify-between shrink-0 shadow-sm border-b border-[#002480] dark:border-blue-900 z-10">
                        <div class="flex items-center gap-3 min-w-0">
                            <a href="{{ route($currentRole . '.messages.index', ['status' => $statusFilter]) }}{{ request('search') ? '&search=' . urlencode(request('search')) : '' }}" 
                               class="lg:hidden inline-flex items-center gap-1 text-xs font-bold text-white bg-white/20 hover:bg-white/30 px-2.5 py-1.5 rounded-lg transition shrink-0" 
                               title="Back to Conversations">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                <span>Back</span>
                            </a>
                            
                            <div class="w-10 h-10 rounded-xl bg-white/15 text-white flex items-center justify-center font-black shrink-0 shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-base sm:text-lg font-black text-white tracking-wide">
                                        {{ $selectedReqCode }}
                                    </span>
                                    @if($isCancelled)
                                        <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-md bg-red-500/30 text-red-100 border border-red-400/40 flex items-center gap-1">
                                            Cancelled
                                        </span>
                                    @elseif($isResolved)
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-white/20 text-blue-100">
                                            Resolved
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-400/20 text-emerald-200 border border-emerald-400/30 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                            Active
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs font-semibold text-blue-100/90 truncate max-w-xs sm:max-w-md md:max-w-lg mt-0.5">
                                    {{ $selectedRequest->title ?? 'General Maintenance Request' }}
                                </p>
                            </div>
                        </div>

                        @if($viewRequestUrl)
                            <a href="{{ $viewRequestUrl }}" class="shrink-0 inline-flex items-center gap-1.5 text-xs font-bold text-white bg-white/15 hover:bg-white/25 border border-white/20 px-3.5 py-1.5 rounded-xl transition shadow-xs">
                                <span>View Request</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @endif
                    </div>
                @else
                    <!-- Admin/Worker View: Blue Header Top Bar with Centered Overlapping Circular Avatar DP -->
                    <div class="bg-[#0033a0] h-14 sm:h-16 w-full relative flex items-center justify-between px-3 sm:px-6 shrink-0 z-10">
                        <div class="flex items-center gap-2">
                            <a href="{{ route($currentRole . '.messages.index', ['status' => $statusFilter]) }}{{ request('search') ? '&search=' . urlencode(request('search')) : '' }}" 
                               class="lg:hidden inline-flex items-center gap-1 text-xs font-bold text-white bg-white/20 hover:bg-white/30 px-2.5 py-1 rounded-lg transition" 
                               title="Back to Conversations">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                <span>Back</span>
                            </a>
                            <span class="text-xs uppercase tracking-wider text-blue-200 font-bold">
                                {{ $selectedReqCode }}
                            </span>
                            @if($isCancelled)
                                <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-red-500 text-white uppercase shadow-2xs">
                                    Cancelled
                                </span>
                            @elseif($isResolved)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-white/20 text-blue-100">
                                    Resolved
                                </span>
                            @endif
                        </div>
                        @if($viewRequestUrl)
                            <a href="{{ $viewRequestUrl }}" class="text-xs font-bold text-white hover:underline bg-white/10 px-2.5 sm:px-3 py-1 rounded-full text-[11px] sm:text-xs">
                                View Request &rarr;
                            </a>
                        @endif

                        <!-- Circular Avatar DP in the Middle (Floating z-20 on boundary) -->
                        <div class="absolute -bottom-7 sm:-bottom-8 left-1/2 -translate-x-1/2 z-20">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-[#0033a0] text-white font-black text-base sm:text-lg flex items-center justify-center border-4 border-white dark:border-zinc-900 shadow-md overflow-hidden shrink-0">
                                @if($clientUser && $clientUser->first_name)
                                    {{ $initials }}
                                @else
                                    <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Centered Name & Email Info -->
                    <div class="flex flex-col items-center justify-center pt-8 pb-2 sm:pt-9 sm:pb-3 px-4 sm:px-6 border-b border-gray-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 shrink-0">
                        <!-- Client Name -->
                        <h2 class="text-sm sm:text-base font-extrabold text-[#0033a0] dark:text-blue-400">
                            {{ $clientUser ? $clientUser->first_name . ' ' . $clientUser->last_name : 'Jane Doe' }}
                        </h2>

                        <!-- Client Email -->
                        <p class="text-[11px] sm:text-xs font-medium text-[#0033a0]/80 dark:text-gray-400">
                            {{ $clientUser?->email_account ?? 'client@bicol-u.edu.ph' }}
                        </p>

                        <div class="mt-0.5 text-[10px] sm:text-[11px] text-gray-500 font-semibold truncate max-w-xs sm:max-w-md">
                            {{ $selectedRequest->title ?? 'General Maintenance Request' }}
                        </div>
                    </div>
                @endif


                <!-- Chat Feed Area -->
                <div id="chatFeedContainer" 
                     data-request-id="{{ $selectedRequest->request_id }}"
                     data-client-user-id="{{ $selectedRequest->client?->user_id }}"
                     data-client-name="{{ $clientUser ? $clientUser->first_name . ' ' . $clientUser->last_name : 'Client Requestor' }}"
                     data-current-user-role="{{ auth()->user()->role }}"
                     data-current-user-name="{{ auth()->user()->first_name . ' ' . auth()->user()->last_name }}"
                     data-current-user-id="{{ auth()->id() }}"
                     data-mark-read-url="{{ $markReadRoute }}"
                     class="flex-1 min-h-0 p-3 sm:p-6 overflow-y-auto bg-white dark:bg-zinc-950/30 flex flex-col">
                    <div id="chatMessagesInner" class="mt-auto space-y-4 flex flex-col w-full">
                        @forelse($selectedRequest->messages as $msg)
                            @php
                                $isSelf = $msg->sender_id === auth()->id();
                                $senderRole = strtoupper($msg->sender?->role ?? 'USER');
                                $roleClass = match($senderRole) {
                                    'ADMIN' => 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300',
                                    'WORKER' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
                                    default => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300'
                                };
                            @endphp
                            <div class="flex flex-col {{ $isSelf ? 'items-end' : 'items-start' }}" data-message-id="{{ $msg->message_id }}" data-is-self="{{ $isSelf ? 'true' : 'false' }}">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] font-extrabold {{ $isSelf ? 'text-gray-400' : 'text-gray-600 dark:text-gray-300' }}">
                                        {{ $isSelf ? 'You' : ($msg->sender ? $msg->sender->first_name . ' ' . $msg->sender->last_name : 'User') }}
                                    </span>
                                    <span class="px-1.5 py-0.5 text-[9px] font-bold rounded {{ $roleClass }}">
                                        {{ $senderRole }}
                                    </span>
                                    <span class="text-[10px] text-gray-400">
                                        {{ $msg->created_at->format('h:i A') }}
                                    </span>
                                </div>

                                <!-- Gray Rounded Rectangle Bubble (Matches Mockup) -->
                                <div class="max-w-md p-4 rounded-2xl text-xs font-medium leading-relaxed shadow-xs {{ $isSelf ? 'bg-[#d9d9d9] dark:bg-zinc-700 text-slate-900 dark:text-white' : 'bg-[#e5e5e5] dark:bg-zinc-800 text-slate-900 dark:text-gray-100' }}">
                                    <p class="whitespace-pre-line">{{ $msg->message }}</p>

                                    @if($msg->attachment)
                                        @php
                                            $isImg = Str::endsWith(strtolower($msg->attachment), ['.jpg', '.jpeg', '.png', '.webp']);
                                            $attachUrl = Storage::url($msg->attachment);
                                        @endphp
                                        <div class="mt-2 pt-2 border-t border-gray-300 dark:border-zinc-600">
                                            @if($isImg)
                                                <div class="rounded-xl overflow-hidden cursor-pointer group/img relative border border-gray-300 dark:border-zinc-600"
                                                     onclick="window.openMessageLightbox('{{ $attachUrl }}', 'Image Attachment', false)">
                                                    <img src="{{ $attachUrl }}" alt="Attachment" class="max-h-48 w-auto max-w-full rounded-xl object-contain bg-black/5 dark:bg-black/20 hover:opacity-90 transition">
                                                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover/img:opacity-100 transition flex items-center justify-center text-white text-[11px] font-bold gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                        <span>Click to view</span>
                                                    </div>
                                                </div>
                                            @else
                                                <div onclick="window.openMessageLightbox('{{ $attachUrl }}', 'Document Attachment', true)" class="inline-flex items-center gap-1.5 font-bold text-[11px] text-[#0033a0] dark:text-blue-400 hover:underline cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                    <span>View Document</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Sent / Seen Status under chat bubble (for self sent messages) -->
                                @if($isSelf)
                                    <div data-message-status class="mt-1 flex items-center gap-1 text-[10px] justify-end pr-1 transition-all duration-300 {{ $msg->is_read ? 'font-bold text-blue-600 dark:text-blue-400' : 'font-semibold text-gray-400 dark:text-gray-500' }}">
                                        @if($msg->is_read)
                                            <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M18 6L7 17l-5-5"/>
                                                <path d="M22 10l-7.5 7.5L13 16"/>
                                            </svg>
                                            <span>Seen</span>
                                        @else
                                            <svg class="w-3 h-3 text-gray-400 dark:text-gray-500 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Sent</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div id="chatEmptyNotice" class="text-center py-12 text-gray-400 text-xs">
                                No messages in this requisition chat thread yet. Send a message to start communicating!
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Footer / Input Bar (Matches Mockup Design Exactly) -->
                <div class="p-3 sm:p-4 bg-white dark:bg-zinc-900 border-t-2 border-[#0033a0] dark:border-blue-700 shrink-0">
                    @if($isCancelled)
                        <div class="p-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/50 rounded-xl text-center text-xs font-semibold text-red-800 dark:text-red-300">
                            <strong>Requisition Cancelled</strong>: This request has been cancelled and messaging is closed.
                        </div>
                    @elseif($isResolved)
                        <div class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-xl text-center text-xs font-semibold text-amber-800 dark:text-amber-300">
                            <strong>Requisition Resolved</strong>: Messaging is locked for completed or closed requests.
                        </div>
                    @else
                        <form id="chatMessageForm" method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" autocomplete="off" class="flex items-center gap-3">

                            @csrf
                            
                            <!-- Document Attachment Icon (Far Left) -->
                            <label class="p-2 text-[#0033a0] dark:text-blue-400 hover:opacity-80 cursor-pointer transition shrink-0 relative" title="Attach Document / Photo">
                                <input type="file" name="attachment" accept="image/*,.pdf" class="hidden" onchange="const f = this.files[0]; if(f){ const notif = this.parentElement.querySelector('.attach-badge'); if(notif){ notif.classList.remove('hidden'); } }">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="attach-badge hidden absolute top-1 right-1 w-2.5 h-2.5 bg-green-500 rounded-full border border-white dark:border-zinc-900"></span>
                            </label>

                            <!-- Yellow Cream Pill Shaped Input Box (Center) -->
                            <div class="flex-1">
                                <input type="text" 
                                       name="message" 
                                       placeholder="Type a Message" 
                                       autocomplete="off" 
                                       autocorrect="off" 
                                       autocapitalize="off" 
                                       spellcheck="false" 
                                       class="w-full px-5 py-3 bg-[#fffde7] dark:bg-zinc-800 border-2 border-[#0033a0] dark:border-blue-500 rounded-full text-xs font-bold text-slate-900 dark:text-white placeholder-[#0033a0]/70 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#0033a0]" 
                                       required>
                            </div>

                            <!-- Dark Blue Paper Plane Send Button (Far Right) -->
                            <button type="submit" id="chatSubmitBtn" class="p-2 text-[#0033a0] dark:text-blue-400 hover:scale-110 transition shrink-0 flex items-center justify-center disabled:opacity-50" title="Send Message">
                                <svg id="chatSubmitIcon" class="w-7 h-7 fill-current" viewBox="0 0 24 24">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                </svg>
                                <svg id="chatSubmitSpinner" class="w-6 h-6 animate-spin text-[#0033a0] dark:text-blue-400 hidden" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </form>
                    @endif
                </div>

            @else
                <div class="flex-1 flex flex-col items-center justify-center text-center p-8 text-gray-400">
                    <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <p class="text-sm font-bold text-gray-500">No Conversation Selected</p>
                    <p class="text-xs text-gray-400 mt-1">Select a requisition from the left list to view messages.</p>
                </div>
            @endif

        </div>
    </div>
</div>

<!-- Message Lightbox Modal -->
<div id="messageLightboxModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 sm:p-6 bg-black/85 backdrop-blur-sm" onclick="window.closeMessageLightbox()">
    <div class="relative max-w-4xl w-full max-h-[90vh] bg-zinc-900 rounded-2xl overflow-hidden shadow-2xl border border-zinc-700 flex flex-col items-center" onclick="event.stopPropagation()">
        <div class="w-full flex items-center justify-between py-3 px-5 bg-zinc-800 text-white border-b border-zinc-700 shrink-0">
            <span id="messageLightboxTitle" class="text-xs font-bold uppercase tracking-wider text-gray-200 truncate max-w-md">Attachment Preview</span>
            <div class="flex items-center gap-3 shrink-0">
                <a id="messageLightboxDownload" href="#" download class="text-xs text-blue-400 hover:text-blue-300 font-semibold inline-flex items-center gap-1.5 px-2.5 py-1 bg-zinc-700/60 hover:bg-zinc-700 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download</span>
                </a>
                <button type="button" onclick="window.closeMessageLightbox()" class="p-1.5 text-gray-400 hover:text-white hover:bg-zinc-700 rounded-lg transition" title="Close (Esc)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <div class="w-full p-4 flex items-center justify-center overflow-auto max-h-[80vh] bg-black/60">
            <img id="messageLightboxImg" src="" alt="Attachment" class="max-h-[75vh] w-auto max-w-full object-contain rounded-lg shadow-lg hidden">
            <iframe id="messageLightboxIframe" src="" class="w-full h-[75vh] rounded-lg border-0 bg-white hidden"></iframe>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.renderAttachmentHtml = function(attachUrl, isSelf) {
    if (!attachUrl) return '';
    const url = (attachUrl.startsWith('http') || attachUrl.startsWith('/') || attachUrl.startsWith('data:')) ? attachUrl : '/storage/' + attachUrl;
    const isImg = /\.(jpe?g|png|webp|gif|bmp|svg)($|\?)/i.test(url) || url.startsWith('data:image') || url.startsWith('blob:');

    if (isImg) {
        return `
            <div class="mt-2 pt-2 border-t border-gray-300 dark:border-zinc-600">
                <div class="rounded-xl overflow-hidden cursor-pointer group/img relative border border-gray-300 dark:border-zinc-600 max-w-xs"
                     onclick="window.openMessageLightbox('${url}', 'Image Attachment', false)">
                    <img src="${url}" alt="Attachment" class="w-full max-h-48 object-cover rounded-xl bg-black/5 dark:bg-black/20 hover:opacity-90 transition block">
                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover/img:opacity-100 transition flex items-center justify-center text-white text-[11px] font-bold gap-1.5 backdrop-blur-[1px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Click to expand</span>
                    </div>
                </div>
            </div>
        `;
    } else {
        return `
            <div class="mt-2 pt-2 border-t border-gray-300 dark:border-zinc-600">
                <div onclick="window.openMessageLightbox('${url}', 'Document Attachment', true)" class="inline-flex items-center gap-1.5 font-bold text-[11px] text-[#0033a0] dark:text-blue-400 hover:underline cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span>View Attachment (PDF)</span>
                </div>
            </div>
        `;
    }
};

window.openMessageLightbox = function(url, title = 'Attachment', isPdf = false) {
    if (!url) return;
    const modal = document.getElementById('messageLightboxModal');
    const titleEl = document.getElementById('messageLightboxTitle');
    const downloadEl = document.getElementById('messageLightboxDownload');
    const imgEl = document.getElementById('messageLightboxImg');
    const iframeEl = document.getElementById('messageLightboxIframe');
    if (!modal) return;

    if (titleEl) titleEl.textContent = title;
    if (downloadEl) downloadEl.href = url;

    const isImage = !isPdf && (/\.(jpe?g|png|webp|gif|bmp|svg)($|\?)/i.test(url) || url.startsWith('data:image') || url.startsWith('blob:'));

    if (isImage) {
        if (imgEl) {
            imgEl.src = url;
            imgEl.classList.remove('hidden');
        }
        if (iframeEl) {
            iframeEl.src = '';
            iframeEl.classList.add('hidden');
        }
    } else {
        if (iframeEl) {
            iframeEl.src = url;
            iframeEl.classList.remove('hidden');
        }
        if (imgEl) {
            imgEl.src = '';
            imgEl.classList.add('hidden');
        }
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
};

window.closeMessageLightbox = function() {
    const modal = document.getElementById('messageLightboxModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        const imgEl = document.getElementById('messageLightboxImg');
        const iframeEl = document.getElementById('messageLightboxIframe');
        if (imgEl) imgEl.src = '';
        if (iframeEl) iframeEl.src = '';
        document.body.style.overflow = '';
    }
};

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') window.closeMessageLightbox();
});

document.addEventListener('DOMContentLoaded', function() {
    const feed = document.getElementById('chatMessagesFeed');
    const inner = document.getElementById('chatMessagesInner') || feed;
    const activeRequestId = feed ? feed.getAttribute('data-request-id') : null;
    const currentUserId = parseInt(feed ? (feed.getAttribute('data-current-user-id') || 0) : 0);
    const currentUserRole = feed ? (feed.getAttribute('data-current-user-role') || 'client') : 'client';
    const clientUserId = parseInt(feed ? (feed.getAttribute('data-client-user-id') || 0) : 0);
    const clientName = feed ? (feed.getAttribute('data-client-name') || 'Client') : 'Client';
    const markReadUrl = feed ? feed.getAttribute('data-mark-read-url') : null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    function scrollToBottom() {
        if (!feed) return;
        feed.scrollTop = feed.scrollHeight;
        requestAnimationFrame(() => { if (feed) feed.scrollTop = feed.scrollHeight; });
        setTimeout(() => { if (feed) feed.scrollTop = feed.scrollHeight; }, 100);
    }

    scrollToBottom();

    function getStatusBadgeHtml(status) {
        if (status === 'sending') {
            return `
                <div data-message-status class="mt-1 flex items-center gap-1.5 text-[10px] font-semibold text-gray-400 dark:text-gray-500 justify-end pr-1 transition-all duration-300">
                    <svg class="w-3 h-3 animate-spin text-[#0033a0] dark:text-blue-400 inline-block shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="animate-pulse">Sending...</span>
                </div>
            `;
        } else if (status === 'seen') {
            return `
                <div data-message-status class="mt-1 flex items-center gap-1 text-[10px] font-bold text-blue-600 dark:text-blue-400 justify-end pr-1 transition-all duration-300">
                    <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6L7 17l-5-5"/>
                        <path d="M22 10l-7.5 7.5L13 16"/>
                    </svg>
                    <span>Seen</span>
                </div>
            `;
        } else if (status === 'sent') {
            return `
                <div data-message-status class="mt-1 flex items-center gap-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 justify-end pr-1 transition-all duration-300">
                    <svg class="w-3 h-3 text-gray-400 dark:text-gray-500 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Sent</span>
                </div>
            `;
        } else if (status === 'failed') {
            return `
                <div data-message-status class="mt-1 flex items-center gap-1 text-[10px] font-semibold text-red-500 dark:text-red-400 justify-end pr-1 transition-all duration-300">
                    <svg class="w-3 h-3 text-red-500 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Failed to send</span>
                </div>
            `;
        }
        return '';
    }

    function markSentMessagesAsSeen() {
        if (!inner) return;
        const selfMessages = inner.querySelectorAll('[data-is-self="true"]');
        selfMessages.forEach(el => {
            const statusContainer = el.querySelector('[data-message-status]');
            if (statusContainer) {
                statusContainer.outerHTML = getStatusBadgeHtml('seen');
            }
        });
    }

    function triggerMarkAsRead() {
        if (!markReadUrl) return;
        fetch(markReadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).catch(() => {});
    }

    function initRealtimeChat() {
        if (!activeRequestId || !window.supabaseClient) {
            setTimeout(initRealtimeChat, 200);
            return;
        }

        window.supabaseClient
            .channel(`realtime-chat-${activeRequestId}`)
            .on(
                'postgres_changes',
                {
                    event: 'INSERT',
                    schema: 'public',
                    table: 'request_messages',
                    filter: `request_id=eq.${activeRequestId}`
                },
                (payload) => {
                    const newMsg = payload.new;
                    if (!newMsg || parseInt(newMsg.sender_id) === currentUserId) return;
                    if (inner.querySelector(`[data-message-id="${newMsg.message_id}"]`)) return;

                    const emptyNotice = document.getElementById('chatEmptyNotice') || inner.querySelector('.text-center.py-12');
                    if (emptyNotice) emptyNotice.remove();

                    const isFromClient = (parseInt(newMsg.sender_id) === clientUserId);
                    const senderName = isFromClient ? clientName : (currentUserRole === 'client' ? 'GSO Staff / Admin' : 'Staff / Worker');
                    const senderRole = isFromClient ? 'CLIENT' : (currentUserRole === 'client' ? 'ADMIN' : 'STAFF');
                    const roleClass = isFromClient ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-700';

                    const escMsg = (newMsg.message || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                    const attachHtml = window.renderAttachmentHtml(newMsg.attachment, false);

                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex flex-col items-start transition-all duration-300 animate-fadeIn';
                    wrapper.setAttribute('data-message-id', newMsg.message_id);
                    wrapper.setAttribute('data-is-self', 'false');
                    wrapper.innerHTML = `
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[10px] font-extrabold text-gray-600 dark:text-gray-300">${senderName}</span>
                            <span class="px-1.5 py-0.5 text-[9px] font-bold rounded ${roleClass}">${senderRole}</span>
                            <span class="text-[10px] text-gray-400">Just now</span>
                        </div>
                        <div class="max-w-md p-4 rounded-2xl text-xs font-medium leading-relaxed shadow-xs bg-[#e5e5e5] dark:bg-zinc-800 text-slate-900 dark:text-gray-100">
                            <p class="whitespace-pre-line">${escMsg}</p>
                            ${attachHtml}
                        </div>
                    `;
                    inner.appendChild(wrapper);
                    scrollToBottom();
                    triggerMarkAsRead();
                }
            )
            .on(
                'postgres_changes',
                {
                    event: 'UPDATE',
                    schema: 'public',
                    table: 'request_messages',
                    filter: `request_id=eq.${activeRequestId}`
                },
                (payload) => {
                    const updated = payload.new;
                    if (updated && updated.is_read) {
                        markSentMessagesAsSeen();
                    }
                }
            )
            .subscribe();
    }
    initRealtimeChat();

    // Handle AJAX form submission for smooth local feel
    const form = document.getElementById('chatMessageForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const input = form.querySelector('input[name="message"]');
            const fileInput = form.querySelector('input[type="file"]');
            const submitBtn = document.getElementById('chatSubmitBtn');
            const submitIcon = document.getElementById('chatSubmitIcon');
            const submitSpinner = document.getElementById('chatSubmitSpinner');

            const textValue = input ? input.value.trim() : '';
            const hasFile = fileInput && fileInput.files && fileInput.files[0];
            const fileAttached = hasFile ? fileInput.files[0].name : null;
            const filePreview = (hasFile && fileInput.files[0].type.startsWith('image/')) ? URL.createObjectURL(fileInput.files[0]) : '';

            if (!textValue && !fileAttached) return;

            const tempId = 'temp-' + Date.now();
            const emptyNotice = document.getElementById('chatEmptyNotice') || inner.querySelector('.text-center.py-12');
            if (emptyNotice) emptyNotice.remove();

            const myRole = currentUserRole.toUpperCase();
            let roleClass = 'bg-blue-100 text-blue-800';
            if (myRole === 'ADMIN') roleClass = 'bg-purple-100 text-purple-700';
            if (myRole === 'WORKER') roleClass = 'bg-amber-100 text-amber-700';

            const escMsg = textValue.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            const optAttachHtml = hasFile ? window.renderAttachmentHtml(filePreview || fileAttached, true) : '';

            const optWrapper = document.createElement('div');
            optWrapper.id = tempId;
            optWrapper.className = 'flex flex-col items-end transition-all duration-300 animate-fadeIn';
            optWrapper.setAttribute('data-is-self', 'true');
            optWrapper.innerHTML = `
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[10px] font-extrabold text-gray-400">You</span>
                    <span class="px-1.5 py-0.5 text-[9px] font-bold rounded ${roleClass}">${myRole}</span>
                    <span class="text-[10px] text-gray-400">Just now</span>
                </div>
                <div class="max-w-md p-4 rounded-2xl text-xs font-medium leading-relaxed shadow-xs bg-[#d9d9d9] dark:bg-zinc-700 text-slate-900 dark:text-white">
                    <p class="whitespace-pre-line">${escMsg}</p>
                    ${optAttachHtml}
                </div>
                ${getStatusBadgeHtml('sending')}
            `;

            inner.appendChild(optWrapper);
            scrollToBottom();

            const formData = new FormData(form);
            if (input) input.value = '';
            if (fileInput) fileInput.value = '';
            const attachBadge = form.querySelector('.attach-badge');
            if (attachBadge) attachBadge.classList.add('hidden');

            if (submitBtn) submitBtn.disabled = true;
            if (submitIcon) submitIcon.classList.add('hidden');
            if (submitSpinner) submitSpinner.classList.remove('hidden');

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.message) {
                    const msg = data.message;
                    optWrapper.setAttribute('data-message-id', msg.message_id);

                    if (msg.attachment) {
                        const bubble = optWrapper.querySelector('.max-w-md');
                        if (bubble) {
                            const existingAttach = bubble.querySelector('.border-t');
                            const attachLinkHtml = window.renderAttachmentHtml(msg.attachment, true);
                            if (existingAttach) {
                                existingAttach.outerHTML = attachLinkHtml;
                            } else {
                                bubble.insertAdjacentHTML('beforeend', attachLinkHtml);
                            }
                        }
                    }

                    const statusEl = optWrapper.querySelector('[data-message-status]');
                    if (statusEl) {
                        statusEl.outerHTML = getStatusBadgeHtml(msg.is_read ? 'seen' : 'sent');
                    }
                } else {
                    const statusEl = optWrapper.querySelector('[data-message-status]');
                    if (statusEl) statusEl.outerHTML = getStatusBadgeHtml('failed');
                }
            })
            .catch(() => {
                const statusEl = optWrapper.querySelector('[data-message-status]');
                if (statusEl) statusEl.outerHTML = getStatusBadgeHtml('failed');
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
                if (submitIcon) submitIcon.classList.remove('hidden');
                if (submitSpinner) submitSpinner.classList.add('hidden');
            });
        });
    }
});
</script>
@endpush
