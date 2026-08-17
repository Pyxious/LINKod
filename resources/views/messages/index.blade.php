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
                <form method="GET" action="{{ route($currentRole . '.messages.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <div class="relative flex-1">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search..." 
                               class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-semibold focus:outline-none focus:border-[#0033a0]">
                        <svg class="w-4 h-4 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <button type="submit" class="p-2.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition shadow-sm" title="Search">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </form>

                <!-- Status Tabs (All, Active, Resolved) -->
                <div class="flex items-center gap-2 border-b border-gray-100 dark:border-zinc-800 pb-2">
                    @php
                        $baseUrl = route(auth()->user()->role . '.messages.index');
                        $searchParam = request('search') ? '&search=' . urlencode(request('search')) : '';
                    @endphp
                    <a href="{{ $baseUrl }}?status=all{{ $searchParam }}" class="text-xs font-bold px-3 py-1 rounded-lg transition {{ $statusFilter === 'all' ? 'text-[#0033a0] bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 hover:text-gray-800' }}">
                        All
                    </a>
                    <a href="{{ $baseUrl }}?status=active{{ $searchParam }}" class="text-xs font-bold px-3 py-1 rounded-lg transition {{ $statusFilter === 'active' ? 'text-[#0033a0] bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 hover:text-gray-800' }}">
                        Active
                    </a>
                    <a href="{{ $baseUrl }}?status=resolved{{ $searchParam }}" class="text-xs font-bold px-3 py-1 rounded-lg transition {{ $statusFilter === 'resolved' ? 'text-[#0033a0] bg-blue-50 dark:bg-blue-900/30' : 'text-gray-500 hover:text-gray-800' }}">
                        Resolved
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
                        $prefix = match(true) {
                            str_contains($catName, 'landscaping') => 'LS',
                            str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
                            str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
                            str_contains($catName, 'plumbing') => 'PS',
                            str_contains($catName, 'painting') => 'PAINT',
                            default => 'REQ'
                        };
                        $reqCode = $req->requisition_no ?: ($prefix . '-' . str_pad($req->request_id, 3, '0', STR_PAD_LEFT));
                    @endphp
                    <a href="{{ route($currentRole . '.messages.index', ['requestId' => $req->request_id, 'status' => $statusFilter]) }}{{ request('search') ? '&search=' . urlencode(request('search')) : '' }}" 
                       class="block p-3 rounded-xl border transition flex items-center gap-3 {{ $isSelected ? 'border-[#0033a0] bg-blue-50/50 dark:bg-blue-950/20 ring-1 ring-[#0033a0]' : 'border-gray-200 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50' }}">
                        <!-- Client Avatar Icon -->
                        <div class="w-10 h-10 rounded-full bg-[#0033a0] text-white flex items-center justify-center font-extrabold text-xs shrink-0 shadow-sm relative">
                            @if($clientUser && $clientUser->first_name)
                                {{ $initials }}
                            @else
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                            @endif
                        </div>
                        
                        <!-- Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <div class="flex items-center gap-1.5 truncate">
                                    <span class="font-bold text-xs text-slate-900 dark:text-white truncate">
                                        {{ $reqCode }}
                                    </span>
                                    <span class="px-1.5 py-0.5 text-[9px] font-extrabold rounded uppercase tracking-wider {{ strtolower($req->priority ?? 'low') === 'high' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : (strtolower($req->priority ?? 'low') === 'medium' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300') }}">
                                        {{ $req->priority ?? $req->urgency ?? 'Low' }}
                                    </span>
                                </div>
                                <span class="text-[10px] text-gray-400 shrink-0">
                                    {{ $lastMsg ? $lastMsg->created_at->diffForHumans(null, true, true) : $req->submitted_at?->diffForHumans(null, true, true) }}
                                </span>
                            </div>
                            <p class="text-[11px] font-semibold text-gray-600 dark:text-gray-300 truncate mt-0.5 flex items-center gap-1">
                                <svg class="w-3 h-3 text-gray-400 inline shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                <span>{{ $clientUser ? $clientUser->first_name . ' ' . $clientUser->last_name : 'Client Requestor' }}</span>
                            </p>
                            <p class="text-[10px] text-gray-400 truncate mt-0.5">
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
                    $isResolved = $selectedRequest->isResolved();
                    
                    $catNameSelected = strtolower($selectedRequest->category->category_name ?? '');
                    $prefixSel = match(true) {
                        str_contains($catNameSelected, 'landscaping') => 'LS',
                        str_contains($catNameSelected, 'electrical') || str_contains($catNameSelected, 'mechanical') => 'EMS',
                        str_contains($catNameSelected, 'carpentry') || str_contains($catNameSelected, 'masonry') => 'CMS',
                        str_contains($catNameSelected, 'plumbing') => 'PS',
                        str_contains($catNameSelected, 'painting') => 'PAINT',
                        default => 'REQ'
                    };
                    $selectedReqCode = $selectedRequest->requisition_no ?: ($prefixSel . '-' . str_pad($selectedRequest->request_id, 3, '0', STR_PAD_LEFT));

                    // Route for posting message based on user role
                    $storeRoute = match (auth()->user()->role) {
                        'admin'  => route('admin.requests.messages.store', $selectedRequest->request_id),
                        'worker' => route('worker.job-orders.messages.store', $selectedRequest->request_id),
                        default  => route('client.requests.messages.store', $selectedRequest->request_id),
                    };
                @endphp

                <!-- Blue Header Top Bar with Centered Overlapping Circular Avatar DP -->
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

                <!-- Chat Feed Area -->
                <div id="chatFeedContainer" class="flex-1 min-h-0 p-3 sm:p-6 overflow-y-auto bg-white dark:bg-zinc-950/30 flex flex-col">
                    <div id="chatMessagesInner" class="mt-auto space-y-4 flex flex-col w-full">
                        @forelse($selectedRequest->messages as $msg)
                            @php
                                $isSelf = $msg->sender_id === auth()->id();
                                $senderRole = strtoupper($msg->sender?->role ?? 'USER');
                            @endphp
                            <div class="flex flex-col {{ $isSelf ? 'items-end' : 'items-start' }}">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] font-extrabold text-gray-400">
                                        {{ $msg->sender?->first_name }} {{ $msg->sender?->last_name }}
                                    </span>
                                    <span class="px-1.5 py-0.5 text-[9px] font-bold rounded {{ $senderRole === 'ADMIN' ? 'bg-purple-100 text-purple-700' : ($senderRole === 'WORKER' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
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
                                        <div class="mt-2 pt-2 border-t border-gray-300 dark:border-zinc-600">
                                            <a href="{{ Storage::url($msg->attachment) }}" target="_blank" class="inline-flex items-center gap-1.5 font-bold text-[11px] text-[#0033a0] dark:text-blue-400 hover:underline">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                View Attachment
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <!-- Sent Status under chat bubble -->
                                <div class="mt-1 flex items-center gap-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 {{ $isSelf ? 'justify-end pr-1' : 'justify-start pl-1' }}">
                                    <svg class="w-3 h-3 text-blue-500 dark:text-blue-400 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Sent</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-gray-400 text-xs">
                                No messages in this requisition chat thread yet. Send a message to start communicating!
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Footer / Input Bar (Matches Mockup Design Exactly) -->
                <div class="p-3 sm:p-4 bg-white dark:bg-zinc-900 border-t-2 border-[#0033a0] dark:border-blue-700 shrink-0">
                    @if($isResolved)
                        <div class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-xl text-center text-xs font-semibold text-amber-800 dark:text-amber-300">
                            <strong>Requisition Resolved</strong>: Messaging is locked for completed or closed requests.
                        </div>
                    @else
                        <form id="chatMessageForm" method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" autocomplete="off" class="flex items-center gap-3">
                            @csrf
                            
                            <!-- Document Attachment Icon (Far Left) -->
                            <label class="p-2 text-[#0033a0] dark:text-blue-400 hover:opacity-80 cursor-pointer transition shrink-0" title="Attach Document / Photo">
                                <input type="file" name="attachment" class="hidden" onchange="alert('File attached: ' + this.files[0].name)">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
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
                            <button type="submit" class="p-2 text-[#0033a0] dark:text-blue-400 hover:scale-110 transition shrink-0 flex items-center justify-center" title="Send Message">
                                <svg class="w-7 h-7 fill-current" viewBox="0 0 24 24">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const feed = document.getElementById('chatFeedContainer');
    const inner = document.getElementById('chatMessagesInner') || feed;

    function scrollToBottom() {
        if (!feed) return;
        feed.scrollTop = feed.scrollHeight;
        requestAnimationFrame(function() {
            feed.scrollTop = feed.scrollHeight;
        });
        setTimeout(function() {
            if (feed) feed.scrollTop = feed.scrollHeight;
        }, 50);
        setTimeout(function() {
            if (feed) feed.scrollTop = feed.scrollHeight;
        }, 200);
    }

    scrollToBottom();

    const form = document.getElementById('chatMessageForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const input = form.querySelector('input[name="message"]');
        if (!input || !input.value.trim()) return;

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP error ' + res.status);
            return res.json();
        })
        .then(data => {
            if (data.success && data.message && feed) {
                const msg = data.message;
                const emptyNotice = feed.querySelector('.text-center.py-12');
                if (emptyNotice) emptyNotice.remove();

                const r = (msg.sender_role || '').toUpperCase();
                let roleClass = 'bg-blue-100 text-blue-800';
                if (r === 'ADMIN') roleClass = 'bg-purple-100 text-purple-700';
                if (r === 'WORKER') roleClass = 'bg-amber-100 text-amber-800';

                const escMsg = (msg.message || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                const attachmentHtml = msg.attachment ? `
                    <div class="mt-2 pt-2 border-t border-gray-300 dark:border-zinc-600">
                        <a href="${msg.attachment}" target="_blank" class="inline-flex items-center gap-1.5 font-bold text-[11px] text-[#0033a0] dark:text-blue-400 hover:underline">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            View Attachment
                        </a>
                    </div>
                ` : '';

                const wrapper = document.createElement('div');
                wrapper.className = 'flex flex-col items-end';
                wrapper.innerHTML = `
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[10px] font-extrabold text-gray-400">${msg.sender_name}</span>
                        <span class="px-1.5 py-0.5 text-[9px] font-bold rounded ${roleClass}">${r}</span>
                        <span class="text-[10px] text-gray-400">${msg.created_time || 'Just now'}</span>
                    </div>
                    <div class="max-w-md p-4 rounded-2xl text-xs font-medium leading-relaxed shadow-xs bg-[#d9d9d9] dark:bg-zinc-700 text-slate-900 dark:text-white">
                        <p class="whitespace-pre-line">${escMsg}</p>
                        ${attachmentHtml}
                    </div>
                    <div class="mt-1 flex items-center gap-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 justify-end pr-1">
                        <svg class="w-3 h-3 text-blue-500 dark:text-blue-400 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Sent</span>
                    </div>
                `;
                inner.appendChild(wrapper);
                scrollToBottom();

                input.value = '';
                const fileInp = form.querySelector('input[type="file"]');
                if (fileInp) fileInp.value = '';
            }
        })
        .catch(err => {
            console.error('AJAX message submission error:', err);
        })
        .finally(() => {
            if (submitBtn) submitBtn.disabled = false;
        });
    });
});
</script>
@endpush
@endsection
