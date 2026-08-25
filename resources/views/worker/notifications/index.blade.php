@extends('layouts.worker')
@section('page-title', 'Notifications')

@section('content')

<div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm mb-6 max-w-3xl mx-auto overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[#1a3c8f] dark:text-blue-400 font-bold text-xl">Your Notifications</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Updates and alerts for your assignments and discussions</p>
        </div>
        
        <form method="POST" action="{{ route('worker.notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-semibold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Mark All as Read</span>
            </button>
        </form>
    </div>

    <!-- Filter Tabs -->
    <div class="px-6 py-3 bg-gray-50 dark:bg-zinc-800/40 border-b border-gray-100 dark:border-zinc-800 flex items-center gap-2 overflow-x-auto">
        <a href="{{ route('worker.notifications.index', ['type' => 'all']) }}" 
           class="px-3 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 {{ ($type ?? 'all') === 'all' ? 'bg-[#1a3c8f] text-white shadow-xs' : 'bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-zinc-700 border border-gray-200 dark:border-zinc-700' }}">
            <span>All</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($type ?? 'all') === 'all' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-zinc-700' }}">{{ $totalCount ?? 0 }}</span>
        </a>

        <a href="{{ route('worker.notifications.index', ['type' => 'requests']) }}" 
           class="px-3 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 {{ ($type ?? 'all') === 'requests' ? 'bg-[#1a3c8f] text-white shadow-xs' : 'bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-zinc-700 border border-gray-200 dark:border-zinc-700' }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>Assignments</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($type ?? 'all') === 'requests' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-zinc-700' }}">{{ $requestCount ?? 0 }}</span>
        </a>

        <a href="{{ route('worker.notifications.index', ['type' => 'messages']) }}" 
           class="px-3 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 {{ ($type ?? 'all') === 'messages' ? 'bg-[#1a3c8f] text-white shadow-xs' : 'bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-zinc-700 border border-gray-200 dark:border-zinc-700' }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span>Messages</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($type ?? 'all') === 'messages' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-zinc-700' }}">{{ $messageCount ?? 0 }}</span>
        </a>
    </div>
    
    <div class="divide-y divide-gray-100 dark:divide-zinc-800">
        @forelse($notifications as $notification)
            @php
                $isMessage = ($notification->type === 'new_message');
            @endphp
            <a href="{{ route('worker.notifications.read', $notification->notification_id) }}" class="block p-5 hover:bg-gray-50 dark:hover:bg-zinc-800/60 transition {{ $notification->is_read ? '' : 'bg-blue-50/40 dark:bg-blue-950/20' }}">
                <div class="flex gap-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $isMessage ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-400' : 'bg-[#1a3c8f]/10 text-[#1a3c8f] dark:bg-blue-950 dark:text-blue-400' }}">
                        @if($isMessage)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        @elseif($notification->type === 'job_assigned')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="font-bold text-gray-900 dark:text-white text-sm truncate">{{ $notification->title }}</span>
                            <div class="flex items-center gap-2 shrink-0">
                                @if(!$notification->is_read)
                                    <span class="w-2 h-2 bg-[#1a3c8f] dark:bg-blue-400 rounded-full inline-block"></span>
                                @endif
                                <span class="text-xs text-gray-400">{{ $notification->sent_at ? $notification->sent_at->diffForHumans() : '' }}</span>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 text-xs leading-relaxed">{{ $notification->message }}</p>
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
        <div class="p-4 border-t border-gray-100">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

@endsection
