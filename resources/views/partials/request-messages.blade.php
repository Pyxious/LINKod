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
    <div id="requestMessagesFeed" class="max-h-96 overflow-y-auto pr-1 mb-5 scroll-smooth flex flex-col">
        <div id="requestMessagesInner" class="mt-auto space-y-4 flex flex-col w-full">
            @forelse($messages as $msg)
                @php
                    $isSelf = ($msg->sender_id === $currentUser->user_id);
                    $senderRole = ucfirst($msg->sender->role ?? 'User');
                    $roleBadgeClass = match(strtolower($msg->sender->role ?? '')) {
                        'admin' => 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200',
                        'worker' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200',
                        default => 'bg-blue-100 text-[#0038A8] dark:bg-blue-950/60 dark:text-blue-300 border-blue-200'
                    };
                @endphp

                <div class="flex flex-col {{ $isSelf ? 'items-end' : 'items-start' }}">
                    <!-- Meta Info (Name, Role, Date) -->
                    <div class="flex items-center gap-2 mb-1 text-[11px] text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-slate-800 dark:text-gray-200">
                            {{ $isSelf ? 'You' : ($msg->sender->first_name . ' ' . $msg->sender->last_name) }}
                        </span>
                        <span class="px-1.5 py-0.2 text-[10px] font-extrabold uppercase rounded border {{ $roleBadgeClass }}">
                            {{ $senderRole }}
                        </span>
                        <span>&bull;</span>
                        <span>{{ $msg->created_at->diffForHumans() }}</span>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const feed = document.getElementById('requestMessagesFeed');
        if (feed) {
            feed.scrollTop = feed.scrollHeight;
        }
    });
    </script>

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

                <button type="submit" class="w-full sm:w-auto bg-[#0038A8] hover:bg-[#002B82] text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-2xs inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Send Message
                </button>
            </div>
        </form>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const feed = document.getElementById('requestMessagesFeed');
    const inner = document.getElementById('requestMessagesInner') || feed;

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

    const form = document.getElementById('requestMessageForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const textarea = form.querySelector('textarea[name="message"]');
        if (!textarea || !textarea.value.trim()) return;

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
                const emptyNotice = feed.querySelector('.border-dashed');
                if (emptyNotice) emptyNotice.remove();

                const r = (msg.sender_role || '').toLowerCase();
                let roleClass = 'bg-blue-100 text-[#0038A8] dark:bg-blue-950/60 dark:text-blue-300 border-blue-200';
                if (r === 'admin') roleClass = 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200';
                if (r === 'worker') roleClass = 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200';

                const escMsg = (msg.message || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                const attachmentHtml = msg.attachment ? `
                    <div class="mt-2.5 pt-2 border-t border-blue-400/40">
                        <a href="${msg.attachment}" target="_blank" class="inline-flex items-center gap-2 text-[11px] font-semibold text-blue-100 hover:text-white">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            View Attachment
                        </a>
                    </div>
                ` : '';

                const wrapper = document.createElement('div');
                wrapper.className = 'flex flex-col items-end';
                wrapper.innerHTML = `
                    <div class="flex items-center gap-2 mb-1 text-[11px] text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-slate-800 dark:text-gray-200">You</span>
                        <span class="px-1.5 py-0.2 text-[10px] font-extrabold uppercase rounded border ${roleClass}">${msg.sender_role}</span>
                        <span>&bull;</span>
                        <span>just now</span>
                    </div>
                    <div class="max-w-xl p-3.5 rounded-2xl text-xs leading-relaxed bg-[#0038A8] text-white rounded-tr-none shadow-2xs">
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

                textarea.value = '';
                const fileInp = form.querySelector('input[type="file"]');
                if (fileInp) fileInp.value = '';
                const span = form.querySelector('.truncate');
                if (span) { span.textContent = ''; span.classList.add('hidden'); }
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
