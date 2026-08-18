@php
    $messages = $serviceRequest->messages()->with('sender')->get();
    $isResolved = $serviceRequest->isResolved();
    $currentUser = auth()->user();
    $postRoute = match($currentUser->role) {
        'admin' => route('admin.requests.messages.store', $serviceRequest->request_id),
        'worker' => route('worker.job-orders.messages.store', $serviceRequest->request_id),
        default => route('client.requests.messages.store', $serviceRequest->request_id)
    };
@endphp

<div id="messages-section" class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-6 shadow-2xs font-sans mt-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-4 mb-5">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-[#0038A8] dark:text-blue-400 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    Requisition Discussion & Messages
                    <span class="text-xs font-semibold text-gray-500 bg-gray-100 dark:bg-zinc-800 dark:text-gray-400 px-2 py-0.5 rounded-full">
                        {{ $messages->count() }}
                    </span>
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Direct communication channel between client, admin, and maintenance team.</p>
            </div>
        </div>

        <!-- Status Badge -->
        @if($isResolved)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-300 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Resolved / Locked
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Active Chat
            </span>
        @endif
    </div>

    <!-- Messages Container -->
    <div id="requestMessagesFeed" 
         class="max-h-96 overflow-y-auto pr-1 mb-5 scroll-smooth flex flex-col" 
         data-request-id="{{ $serviceRequest->request_id }}" 
         data-current-user-id="{{ $currentUser->user_id }}"
         data-client-user-id="{{ $serviceRequest->client?->user_id }}"
         data-client-name="{{ $serviceRequest->client?->user ? ($serviceRequest->client->user->first_name . ' ' . $serviceRequest->client->user->last_name) : 'Client' }}">
        <div id="requestMessagesInner" class="mt-auto space-y-4 flex flex-col w-full">
            @forelse($messages as $msg)
                @php
                    $isSelf = ($msg->sender_id === $currentUser->user_id);
                    $senderObj = $msg->sender;
                    $senderRole = ucfirst($senderObj->role ?? 'User');
                    $roleBadgeClass = match(strtolower($senderObj->role ?? '')) {
                        'admin' => 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200',
                        'worker' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200',
                        default => 'bg-blue-100 text-[#0038A8] dark:bg-blue-950/60 dark:text-blue-300 border-blue-200'
                    };
                    $senderName = $isSelf ? 'You' : ($senderObj ? ($senderObj->first_name . ' ' . $senderObj->last_name) : 'User');
                    $msgDate = $msg->created_at ? (\Carbon\Carbon::parse($msg->created_at)->diffForHumans()) : 'Just now';
                @endphp

                <div class="flex flex-col {{ $isSelf ? 'items-end' : 'items-start' }}" data-message-id="{{ $msg->message_id }}">
                    <!-- Meta Info (Name, Role, Date) -->
                    <div class="flex items-center gap-2 mb-1 text-[11px] text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-slate-800 dark:text-gray-200">
                            {{ $senderName }}
                        </span>
                        <span class="px-1.5 py-0.2 text-[10px] font-extrabold uppercase rounded border {{ $roleBadgeClass }}">
                            {{ $senderRole }}
                        </span>
                        <span>&bull;</span>
                        <span>{{ $msgDate }}</span>
                    </div>

                    <!-- Message Bubble -->
                    <div class="max-w-xl p-3.5 rounded-2xl text-xs leading-relaxed {{ $isSelf ? 'bg-[#0038A8] text-white rounded-tr-none shadow-2xs' : 'bg-gray-100 dark:bg-zinc-800 text-slate-900 dark:text-gray-100 rounded-tl-none border border-gray-200 dark:border-zinc-700' }}">
                        <p class="whitespace-pre-line">{{ $msg->message }}</p>

                        <!-- Attachment if any -->
                        @if($msg->attachment)
                            <div class="mt-2.5 pt-2 border-t {{ $isSelf ? 'border-blue-400/40' : 'border-gray-200 dark:border-zinc-700' }}">
                                <a href="{{ Storage::url($msg->attachment) }}" target="_blank" class="inline-flex items-center gap-2 text-[11px] font-semibold {{ $isSelf ? 'text-blue-100 hover:text-white' : 'text-[#0038A8] dark:text-blue-400 hover:underline' }}">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
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
                <div class="py-8 text-center bg-gray-50/60 dark:bg-zinc-900/40 rounded-xl border border-dashed border-gray-200 dark:border-zinc-800">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">No messages yet for this requisition.</p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Start the conversation below to coordinate details.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Input Form / Resolved Lock Banner -->
    @if($isResolved)
        <div class="p-4 rounded-xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 flex items-center gap-3">
            <div>
                <div class="text-xs font-bold text-amber-900 dark:text-amber-300 uppercase tracking-wider">Requisition Resolved</div>
                <div class="text-xs text-amber-700 dark:text-amber-400 font-medium">This service request is completed or closed. Messaging is marked as resolved and locked for new replies.</div>
            </div>
        </div>
    @else
        <form id="requestMessageForm" action="{{ $postRoute }}" method="POST" enctype="multipart/form-data" autocomplete="off" class="space-y-3">
            @csrf
            <div class="relative">
                <textarea name="message" 
                          rows="3" 
                          placeholder="Type a message or note for this requisition..." 
                          autocomplete="off" 
                          autocorrect="off" 
                          autocapitalize="off" 
                          spellcheck="false" 
                          class="w-full p-3.5 bg-gray-50 dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0038A8] transition resize-none" 
                          onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); if(this.value.trim().length > 0) { this.form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true })); } }"
                          required></textarea>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="w-full sm:w-auto flex items-center gap-2">
                    <label class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-semibold cursor-pointer transition">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        Attach File/Image (Optional)
                        <input type="file" name="attachment" accept="image/*,.pdf" class="hidden" onchange="const fname = this.files[0]?.name; const span = this.parentElement.nextElementSibling; if(fname){ span.textContent = fname; span.classList.remove('hidden'); }">
                    </label>
                    <span class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 truncate max-w-[180px] hidden"></span>
                </div>

                <button type="submit" id="requestMessageSubmitBtn" class="w-full sm:w-auto ml-auto bg-[#0038A8] hover:bg-[#002B82] text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-2xs inline-flex items-center justify-center gap-2 shrink-0 disabled:opacity-60">
                    <span id="requestMessageSubmitText">Send Message</span>
                    <svg id="requestMessageSubmitIcon" class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                    <svg id="requestMessageSubmitSpinner" class="w-4 h-4 animate-spin text-white shrink-0 hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </button>
            </div>
        </form>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const feed = document.getElementById('requestMessagesFeed');
    const inner = document.getElementById('requestMessagesInner') || feed;
    const form = document.getElementById('requestMessageForm');
    const requestId = feed ? feed.getAttribute('data-request-id') : null;
    const currentUserId = feed ? parseInt(feed.getAttribute('data-current-user-id')) : null;

    function scrollToBottom() {
        if (!feed) return;
        feed.scrollTop = feed.scrollHeight;
        requestAnimationFrame(() => { if (feed) feed.scrollTop = feed.scrollHeight; });
    }

    scrollToBottom();

    // Helper to render incoming or newly sent message in feed
    function appendMessageToFeed(msg, isSelf) {
        if (!feed || !inner) return;
        if (msg.message_id && document.querySelector(`[data-message-id="${msg.message_id}"]`)) {
            return; // Avoid duplicate rendering
        }

        const emptyNotice = feed.querySelector('.border-dashed');
        if (emptyNotice) emptyNotice.remove();

        const role = (msg.sender_role || 'User').toLowerCase();
        let roleClass = 'bg-blue-100 text-[#0038A8] dark:bg-blue-950/60 dark:text-blue-300 border-blue-200';
        if (role === 'admin') roleClass = 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200';
        if (role === 'worker') roleClass = 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200';

        const escMsg = (msg.message || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        const attachmentHtml = msg.attachment ? `
            <div class="mt-2.5 pt-2 border-t ${isSelf ? 'border-blue-400/40' : 'border-gray-200 dark:border-zinc-700'}">
                <a href="${msg.attachment.startsWith('/') ? msg.attachment : '/storage/' + msg.attachment}" target="_blank" class="inline-flex items-center gap-2 text-[11px] font-semibold ${isSelf ? 'text-blue-100 hover:text-white' : 'text-[#0038A8] dark:text-blue-400 hover:underline'}">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    View Attachment
                </a>
            </div>
        ` : '';

        const wrapper = document.createElement('div');
        wrapper.className = `flex flex-col ${isSelf ? 'items-end' : 'items-start'}`;
        if (msg.message_id) wrapper.setAttribute('data-message-id', msg.message_id);

        const senderName = isSelf ? 'You' : (msg.sender_name || 'Staff / User');

        wrapper.innerHTML = `
            <div class="flex items-center gap-2 mb-1 text-[11px] text-gray-500 dark:text-gray-400">
                <span class="font-bold text-slate-800 dark:text-gray-200">${senderName}</span>
                <span class="px-1.5 py-0.2 text-[10px] font-extrabold uppercase rounded border ${roleClass}">${msg.sender_role || 'User'}</span>
                <span>&bull;</span>
                <span>just now</span>
            </div>
            <div class="max-w-xl p-3.5 rounded-2xl text-xs leading-relaxed ${isSelf ? 'bg-[#0038A8] text-white rounded-tr-none shadow-2xs' : 'bg-gray-100 dark:bg-zinc-800 text-slate-900 dark:text-gray-100 rounded-tl-none border border-gray-200 dark:border-zinc-700'}">
                <p class="whitespace-pre-line">${escMsg}</p>
                ${attachmentHtml}
            </div>
            <div class="mt-1 flex items-center gap-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 ${isSelf ? 'justify-end pr-1' : 'justify-start pl-1'}">
                <svg class="w-3 h-3 text-blue-500 dark:text-blue-400 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Sent</span>
            </div>
        `;

        inner.appendChild(wrapper);
        scrollToBottom();
    }

    // Connect to Supabase Realtime for instant chat messages
    function initRealtimeDetailsChat() {
        if (!requestId || !window.supabaseClient) {
            setTimeout(initRealtimeDetailsChat, 200);
            return;
        }

        const clientUserId = parseInt(feed.getAttribute('data-client-user-id') || 0);
        const clientName = feed.getAttribute('data-client-name') || 'Client';

        window.supabaseClient
            .channel(`realtime-request-${requestId}`)
            .on(
                'postgres_changes',
                {
                    event: 'INSERT',
                    schema: 'public',
                    table: 'request_messages',
                    filter: `request_id=eq.${requestId}`
                },
                (payload) => {
                    const newMsg = payload.new;
                    if (newMsg && parseInt(newMsg.sender_id) !== currentUserId) {
                        const isFromClient = (parseInt(newMsg.sender_id) === clientUserId);
                        newMsg.sender_name = isFromClient ? clientName : 'GSO Staff / Admin';
                        newMsg.sender_role = isFromClient ? 'Client' : 'Admin';
                        appendMessageToFeed(newMsg, false);
                    }
                }
            )
            .subscribe();
    }
    initRealtimeDetailsChat();

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const textarea = form.querySelector('textarea[name="message"]');
            if (!textarea || !textarea.value.trim()) return;

            const submitBtn = document.getElementById('requestMessageSubmitBtn');
            const submitText = document.getElementById('requestMessageSubmitText');
            const submitIcon = document.getElementById('requestMessageSubmitIcon');
            const submitSpinner = document.getElementById('requestMessageSubmitSpinner');

            if (submitBtn) submitBtn.disabled = true;
            if (submitText) submitText.textContent = 'Sending...';
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
            .then(res => {
                if (!res.ok) throw new Error('HTTP error ' + res.status);
                return res.json();
            })
            .then(data => {
                if (data.success && data.message) {
                    appendMessageToFeed(data.message, true);
                    textarea.value = '';
                    const fileInp = form.querySelector('input[type="file"]');
                    if (fileInp) fileInp.value = '';
                    const span = form.querySelector('.truncate');
                    if (span) { span.textContent = ''; span.classList.add('hidden'); }
                }
            })
            .catch(err => {
                console.error('Message submission error:', err);
            })
            .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
                if (submitText) submitText.textContent = 'Send Message';
                if (submitIcon) submitIcon.classList.remove('hidden');
                if (submitSpinner) submitSpinner.classList.add('hidden');
            });
        });
    }
});
