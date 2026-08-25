@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full flex flex-col font-sans min-h-[calc(100vh-64px)] bg-slate-50/50 dark:bg-[#111111]">
    
    <!-- Top Hero Section (Wide Rectangle Banner) -->
    <div class="bg-[#fffde7] dark:bg-[#18181b] py-8 px-6 md:px-12">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-[#0033a0] dark:text-blue-400 text-3xl font-bold tracking-tight">Notifications</h1>
                <p class="text-[#0033a0]/80 dark:text-gray-400 text-sm font-medium mt-1">Stay updated on your service job request status, updates, and announcements</p>
            </div>
            
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('client.notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 text-[#0038A8] dark:text-blue-400 border border-gray-200 dark:border-zinc-700 rounded-lg font-semibold text-xs transition shadow-2xs gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Mark All as Read</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="max-w-4xl w-full mx-auto px-6 md:px-8 py-8 flex-1">
        
        <!-- Main Outer Box (Soft Blue Container with Blue Border) -->
        <div class="bg-[#EBF3FE] dark:bg-[#151d2a] border border-[#7DAAF4] dark:border-blue-800 rounded-2xl md:rounded-3xl p-6 md:p-8 shadow-2xs">
            
            <!-- Notification Filter Tabs -->
            <div class="flex items-center gap-2 mb-6 border-b border-[#7DAAF4]/40 dark:border-blue-800/60 pb-3 overflow-x-auto">
                <a href="{{ route('client.notifications.index', ['type' => 'all']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-extrabold transition inline-flex items-center gap-2 {{ ($type ?? 'all') === 'all' ? 'bg-[#0038A8] text-white shadow-xs' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-white/80 border border-gray-200 dark:border-zinc-700' }}">
                    <span>All</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ ($type ?? 'all') === 'all' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-zinc-700 text-gray-600 dark:text-gray-300' }}">{{ $totalCount ?? 0 }}</span>
                </a>

                <a href="{{ route('client.notifications.index', ['type' => 'requests']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-extrabold transition inline-flex items-center gap-2 {{ ($type ?? 'all') === 'requests' ? 'bg-[#0038A8] text-white shadow-xs' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-white/80 border border-gray-200 dark:border-zinc-700' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Request Updates</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ ($type ?? 'all') === 'requests' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-zinc-700 text-gray-600 dark:text-gray-300' }}">{{ $requestCount ?? 0 }}</span>
                </a>

                <a href="{{ route('client.notifications.index', ['type' => 'messages']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-extrabold transition inline-flex items-center gap-2 {{ ($type ?? 'all') === 'messages' ? 'bg-[#0038A8] text-white shadow-xs' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-white/80 border border-gray-200 dark:border-zinc-700' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span>Messages</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ ($type ?? 'all') === 'messages' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-zinc-700 text-gray-600 dark:text-gray-300' }}">{{ $messageCount ?? 0 }}</span>
                </a>
            </div>

            <div class="space-y-3.5">
                @forelse($notifications as $n)
                    @php
                        $isRead = $n->is_read;
                        $sentDate = $n->sent_at ? \Carbon\Carbon::parse($n->sent_at) : null;
                        $isMessage = ($n->type === 'new_message');
                    @endphp
                    
                    <div class="bg-white dark:bg-[#1c1c1e] border {{ $isRead ? 'border-gray-200 dark:border-zinc-800' : 'border-[#7DAAF4] dark:border-blue-700 ring-1 ring-blue-100 dark:ring-blue-900/30' }} rounded-xl p-5 shadow-2xs hover:shadow-xs transition flex gap-4 items-start relative">
                        
                        <!-- Left Icon Badge -->
                        <div class="w-10 h-10 rounded-full {{ $isRead ? 'bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-zinc-400' : ($isMessage ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400' : 'bg-blue-50 dark:bg-blue-950/60 text-[#0038A8] dark:text-blue-400') }} flex items-center justify-center shrink-0 mt-0.5">
                            @if($isMessage)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            @endif
                        </div>


                        <!-- Notification Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-gray-900 dark:text-white text-base">
                                        {{ $n->title ?? 'System Notification' }}
                                    </h3>
                                    @if(!$isRead)
                                        <span class="w-2 h-2 rounded-full bg-[#0038A8] dark:bg-blue-400 inline-block"></span>
                                    @endif
                                </div>

                                <span class="text-xs font-semibold text-gray-400 shrink-0">
                                    {{ $sentDate ? $sentDate->diffForHumans() : '' }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 leading-relaxed">
                                {{ $n->message }}
                            </p>

                            <!-- Optional Action Button -->
                            @if(!empty($n->action_url))
                                <div class="mt-3">
                                    <a href="{{ route('client.notifications.read', $n->notification_id) }}" 
                                       class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-[#0038A8] hover:bg-[#002B82] text-white rounded-md font-semibold text-xs transition shadow-2xs">
                                        <span>View Details</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-12 text-center">
                        <div class="text-gray-300 dark:text-zinc-600 mb-3">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <h3 class="text-gray-800 dark:text-white font-bold text-base">No notifications yet</h3>
                        <p class="text-gray-400 text-xs mt-1">You will receive updates here as your service requests progress.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="pt-6">
                    {{ $notifications->links() }}
                </div>
            @endif

        </div>

    </main>
</div>
@endsection
