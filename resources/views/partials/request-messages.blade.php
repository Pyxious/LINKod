@php
    $messages = $serviceRequest->messages()->with('sender')->get();
    $isCancelled = in_array(strtolower($serviceRequest->latestHistory?->current_status ?? $serviceRequest->current_status ?? ''), ['cancelled', 'rejected']);
    $isResolved = $serviceRequest->isResolved() && !$isCancelled;
    $currentUser = auth()->user();
    $postRoute = match($currentUser->role) {
        'admin' => route('admin.requests.messages.store', $serviceRequest->request_id),
        'worker' => route('worker.job-orders.messages.store', $serviceRequest->request_id),
        default => route('client.requests.messages.store', $serviceRequest->request_id)
    };
    $markReadRoute = match($currentUser->role) {
        'admin' => route('admin.requests.messages.mark-read', $serviceRequest->request_id),
        'worker' => route('worker.job-orders.messages.mark-read', $serviceRequest->request_id),
        default => route('client.requests.messages.mark-read', $serviceRequest->request_id)
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
                    <span id="requestMessagesCountBadge" class="text-xs font-semibold text-gray-500 bg-gray-100 dark:bg-zinc-800 dark:text-gray-400 px-2 py-0.5 rounded-full">
                        {{ $messages->count() }}
                    </span>
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Direct communication channel between client, admin, and maintenance team.</p>
            </div>
        </div>

        <!-- Status Badge -->
        @if($isCancelled)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-red-50 text-red-700 border border-red-300 dark:bg-red-950/50 dark:text-red-300 dark:border-red-800">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Cancelled
            </span>
        @elseif($isResolved)
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
         data-current-user-role="{{ $currentUser->role }}"
         data-current-user-name="{{ $currentUser->first_name . ' ' . $currentUser->last_name }}"
         data-client-user-id="{{ $serviceRequest->client?->user_id }}"
         data-client-name="{{ $serviceRequest->client?->user ? ($serviceRequest->client->user->first_name . ' ' . $serviceRequest->client->user->last_name) : 'Client' }}"
         data-mark-read-url="{{ $markReadRoute }}">
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

                <div class="flex flex-col {{ $isSelf ? 'items-end' : 'items-start' }}" data-message-id="{{ $msg->message_id }}" data-is-self="{{ $isSelf ? 'true' : 'false' }}">
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
                <div id="requestMessagesEmptyNotice" class="py-8 text-center bg-gray-50/60 dark:bg-zinc-900/40 rounded-xl border border-dashed border-gray-200 dark:border-zinc-800">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">No messages yet for this requisition.</p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Start the conversation below to coordinate details.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Input Form / Lock Banner -->
    @if($isCancelled)
        <div class="p-4 rounded-xl bg-red-50/80 dark:bg-red-950/40 border border-red-200 dark:border-red-800 flex items-center gap-3">
            <div>
                <div class="text-xs font-bold text-red-900 dark:text-red-300 uppercase tracking-wider">Requisition Cancelled</div>
                <div class="text-xs text-red-700 dark:text-red-400 font-medium">This service request was cancelled. Discussion history is preserved in read-only mode.</div>
            </div>
        </div>
    @elseif($isResolved)
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
                        <span>Attach File/Image (Optional)</span>
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
    const countBadge = document.getElementById('requestMessagesCountBadge');

    if (!feed || !inner) return;

    const requestId = feed.getAttribute('data-request-id');
    const currentUserId = parseInt(feed.getAttribute('data-current-user-id') || 0);
    const currentUserRole = feed.getAttribute('data-current-user-role') || 'user';
    const currentUserName = feed.getAttribute('data-current-user-name') || 'You';
    const clientUserId = parseInt(feed.getAttribute('data-client-user-id') || 0);
    const clientName = feed.getAttribute('data-client-name') || 'Client';
    const markReadUrl = feed.getAttribute('data-mark-read-url');
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
                    <svg class="w-3 h-3 animate-spin text-[#0038A8] dark:text-blue-400 inline-block shrink-0" fill="none" viewBox="0 0 24 24">
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

    // Connect to Supabase Realtime for instant chat & seen status updates
    function initRealtimeDetailsChat() {
        if (!requestId || !window.supabaseClient) {
            setTimeout(initRealtimeDetailsChat, 200);
            return;
        }

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
                    if (!newMsg) return;

                    // If incoming message from another user
                    if (parseInt(newMsg.sender_id) !== currentUserId) {
                        // Check if already rendered
                        if (inner.querySelector(`[data-message-id="${newMsg.message_id}"]`)) return;

                        const emptyNotice = document.getElementById('requestMessagesEmptyNotice') || inner.querySelector('.border-dashed');
                        if (emptyNotice) emptyNotice.remove();

                        const isFromClient = (parseInt(newMsg.sender_id) === clientUserId);
                        const sName = isFromClient ? clientName : 'GSO Staff / Admin';
                        const sRole = isFromClient ? 'Client' : 'Admin';
                        const roleBadgeClass = isFromClient 
                            ? 'bg-blue-100 text-[#0038A8] dark:bg-blue-950/60 dark:text-blue-300 border-blue-200' 
                            : 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200';

                        const escMsg = (newMsg.message || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                        const attachHtml = newMsg.attachment ? `
                            <div class="mt-2.5 pt-2 border-t border-gray-200 dark:border-zinc-700">
                                <a href="${newMsg.attachment.startsWith('/') ? newMsg.attachment : '/storage/' + newMsg.attachment}" target="_blank" class="inline-flex items-center gap-2 text-[11px] font-semibold text-[#0038A8] dark:text-blue-400 hover:underline">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    View Attachment
                                </a>
                            </div>
                        ` : '';

                        const wrapper = document.createElement('div');
                        wrapper.className = 'flex flex-col items-start transition-all duration-300 animate-fadeIn';
                        wrapper.setAttribute('data-message-id', newMsg.message_id);
                        wrapper.setAttribute('data-is-self', 'false');
                        wrapper.innerHTML = `
                            <div class="flex items-center gap-2 mb-1 text-[11px] text-gray-500 dark:text-gray-400">
                                <span class="font-bold text-slate-800 dark:text-gray-200">${sName}</span>
                                <span class="px-1.5 py-0.2 text-[10px] font-extrabold uppercase rounded border ${roleBadgeClass}">${sRole}</span>
                                <span>&bull;</span>
                                <span>Just now</span>
                            </div>
                            <div class="max-w-xl p-3.5 rounded-2xl text-xs leading-relaxed bg-gray-100 dark:bg-zinc-800 text-slate-900 dark:text-gray-100 rounded-tl-none border border-gray-200 dark:border-zinc-700">
                                <p class="whitespace-pre-line">${escMsg}</p>
                                ${attachHtml}
                            </div>
                        `;

                        inner.appendChild(wrapper);
                        scrollToBottom();

                        // Increment badge count
                        if (countBadge) {
                            const cur = parseInt(countBadge.textContent.trim()) || 0;
                            countBadge.textContent = cur + 1;
                        }

                        // Mark read since user is actively on this page
                        triggerMarkAsRead();
                    }
                }
            )
            .on(
                'postgres_changes',
                {
                    event: 'UPDATE',
                    schema: 'public',
                    table: 'request_messages',
                    filter: `request_id=eq.${requestId}`
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
    initRealtimeDetailsChat();

    // Form Submission with Optimistic Sending Animation & Instant Sent State
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const textarea = form.querySelector('textarea[name="message"]');
            if (!textarea || !textarea.value.trim()) return;

            const textValue = textarea.value.trim();
            const fileInput = form.querySelector('input[type="file"]');
            const fileAttached = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0].name : null;

            const submitBtn = document.getElementById('requestMessageSubmitBtn');
            const submitText = document.getElementById('requestMessageSubmitText');
            const submitIcon = document.getElementById('requestMessageSubmitIcon');
            const submitSpinner = document.getElementById('requestMessageSubmitSpinner');

            // Optimistic Message Item Creation
            const tempId = 'temp-' + Date.now();
            const emptyNotice = document.getElementById('requestMessagesEmptyNotice') || inner.querySelector('.border-dashed');
            if (emptyNotice) emptyNotice.remove();

            const myRole = currentUserRole.toUpperCase();
            let roleClass = 'bg-blue-100 text-[#0038A8] dark:bg-blue-950/60 dark:text-blue-300 border-blue-200';
            if (myRole === 'ADMIN') roleClass = 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200';
            if (myRole === 'WORKER') roleClass = 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200';

            const escMsg = textValue.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            const optAttachHtml = fileAttached ? `
                <div class="mt-2.5 pt-2 border-t border-blue-400/40 text-[11px] font-semibold text-blue-100 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span>${fileAttached}</span>
                </div>
            ` : '';

            const optWrapper = document.createElement('div');
            optWrapper.id = tempId;
            optWrapper.className = 'flex flex-col items-end transition-all duration-300';
            optWrapper.setAttribute('data-is-self', 'true');
            optWrapper.innerHTML = `
                <div class="flex items-center gap-2 mb-1 text-[11px] text-gray-500 dark:text-gray-400">
                    <span class="font-bold text-slate-800 dark:text-gray-200">You</span>
                    <span class="px-1.5 py-0.2 text-[10px] font-extrabold uppercase rounded border ${roleClass}">${myRole}</span>
                    <span>&bull;</span>
                    <span>Just now</span>
                </div>
                <div class="max-w-xl p-3.5 rounded-2xl text-xs leading-relaxed bg-[#0038A8] text-white rounded-tr-none shadow-2xs">
                    <p class="whitespace-pre-line">${escMsg}</p>
                    ${optAttachHtml}
                </div>
                ${getStatusBadgeHtml('sending')}
            `;

            inner.appendChild(optWrapper);
            scrollToBottom();

            // Clear input
            const formData = new FormData(form);
            textarea.value = '';
            if (fileInput) fileInput.value = '';
            const fileNameSpan = form.querySelector('.truncate');
            if (fileNameSpan) { fileNameSpan.textContent = ''; fileNameSpan.classList.add('hidden'); }

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
                    const msg = data.message;
                    optWrapper.setAttribute('data-message-id', msg.message_id);

                    // Update attachment URL if returned
                    if (msg.attachment) {
                        const bubble = optWrapper.querySelector('.max-w-xl');
                        if (bubble) {
                            const existingAttach = bubble.querySelector('.border-t');
                            const attachLinkHtml = `
                                <div class="mt-2.5 pt-2 border-t border-blue-400/40">
                                    <a href="${msg.attachment}" target="_blank" class="inline-flex items-center gap-2 text-[11px] font-semibold text-blue-100 hover:text-white">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        View Attachment
                                    </a>
                                </div>
                            `;
                            if (existingAttach) {
                                existingAttach.outerHTML = attachLinkHtml;
                            } else {
                                bubble.insertAdjacentHTML('beforeend', attachLinkHtml);
                            }
                        }
                    }

                    // Update status badge from sending to sent or seen
                    const statusEl = optWrapper.querySelector('[data-message-status]');
                    if (statusEl) {
                        statusEl.outerHTML = getStatusBadgeHtml(msg.is_read ? 'seen' : 'sent');
                    }

                    if (countBadge) {
                        const cur = parseInt(countBadge.textContent.trim()) || 0;
                        countBadge.textContent = cur + 1;
                    }
                } else {
                    const statusEl = optWrapper.querySelector('[data-message-status]');
                    if (statusEl) statusEl.outerHTML = getStatusBadgeHtml('failed');
                }
            })
            .catch(err => {
                console.error('Message submission error:', err);
                const statusEl = optWrapper.querySelector('[data-message-status]');
                if (statusEl) statusEl.outerHTML = getStatusBadgeHtml('failed');
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
</script>
