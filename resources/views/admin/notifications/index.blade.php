@extends('layouts.admin')

@section('page-title', 'Notifications')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Notifications</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Review system updates, service request alerts, and incoming user messages</p>
        </div>

        <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 text-[#0033a0] dark:text-blue-400 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold transition shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Mark All as Read</span>
            </button>
        </form>
    </div>

    <!-- Main Container -->
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden">
        <!-- Filter Tabs -->
        <div class="px-6 py-4 bg-gray-50/70 dark:bg-zinc-800/40 border-b border-gray-200 dark:border-zinc-800 flex items-center gap-2 overflow-x-auto">
            <a href="{{ route('admin.notifications.index', ['type' => 'all']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-extrabold transition inline-flex items-center gap-2 {{ ($type ?? 'all') === 'all' ? 'bg-[#0033a0] text-white shadow-xs' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 border border-gray-200 dark:border-zinc-700' }}">
                <span>All</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($type ?? 'all') === 'all' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-zinc-700' }}">{{ $totalCount ?? 0 }}</span>
            </a>

            <a href="{{ route('admin.notifications.index', ['type' => 'requests']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-extrabold transition inline-flex items-center gap-2 {{ ($type ?? 'all') === 'requests' ? 'bg-[#0033a0] text-white shadow-xs' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 border border-gray-200 dark:border-zinc-700' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Request Alerts</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($type ?? 'all') === 'requests' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-zinc-700' }}">{{ $requestCount ?? 0 }}</span>
            </a>

            <a href="{{ route('admin.notifications.index', ['type' => 'messages']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-extrabold transition inline-flex items-center gap-2 {{ ($type ?? 'all') === 'messages' ? 'bg-[#0033a0] text-white shadow-xs' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-700 border border-gray-200 dark:border-zinc-700' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span>Messages</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($type ?? 'all') === 'messages' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-zinc-700' }}">{{ $messageCount ?? 0 }}</span>
            </a>
        </div>

        <!-- Notification List -->
        <div class="divide-y divide-gray-100 dark:divide-zinc-800">
            @forelse($notifications as $notif)
                @php
                    $isMessage = ($notif->type === 'new_message');
                @endphp
                <a href="{{ route('admin.notifications.read', $notif->notification_id) }}" 
                   class="block p-5 hover:bg-blue-50/50 dark:hover:bg-zinc-800/60 transition {{ !$notif->is_read ? 'bg-blue-50/30 dark:bg-zinc-800/30' : '' }}">
                    <div class="flex gap-4 items-start">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 mt-0.5 {{ $isMessage ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-400' : 'bg-blue-100 text-[#0033a0] dark:bg-blue-950 dark:text-blue-400' }}">
                            @if($isMessage)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ $notif->title ?? 'Notification' }}</h3>
                                    @if(!$notif->is_read)
                                        <span class="w-2 h-2 rounded-full bg-[#0033a0] dark:bg-blue-400 inline-block"></span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400 shrink-0">{{ $notif->sent_at ? \Carbon\Carbon::parse($notif->sent_at)->diffForHumans() : '' }}</span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ $notif->message }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-12">
                    <div class="w-14 h-14 mx-auto bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mb-3 text-gray-400">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-bold text-sm mb-1">No notifications</h3>
                    <p class="text-xs text-gray-400">You don't have any notifications under this filter.</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-zinc-800">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
