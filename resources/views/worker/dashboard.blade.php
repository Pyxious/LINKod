@extends('layouts.worker')
@section('page-title', 'Dashboard')

@section('content')

@php
    $pendingCount = $assignments->filter(fn($a) => $a->project && $a->project->current_status === 'Pending')->count();
    $inProgressCount = $assignments->filter(fn($a) => $a->project && $a->project->current_status === 'In Progress')->count();
    $completedCount = $assignments->filter(fn($a) => $a->project && $a->project->current_status === 'Completed')->count();
    $worker = auth()->user()->staff?->worker;
    $teamName = $worker?->team?->team_name ?? 'General Services Office';
@endphp

<!-- Dash Banner -->
<div class="bg-[#fefce8] dark:bg-[#1c1c1e] border border-[#1a3c8f] rounded-xl px-8 py-6 flex justify-between items-center mb-6 shadow-sm font-sans" x-data="{ openNotifs: false }">
    <div>
        <h1 class="text-[#1a3c8f] dark:text-blue-400 text-2xl font-bold mb-1">Welcome back, {{ auth()->user()->first_name }}!</h1>
        <p class="text-[#1a3c8f] dark:text-gray-300 text-sm opacity-90">{{ $teamName }} &bull; General Services Office</p>
    </div>
    
    <div class="flex items-center gap-6">
        <!-- Interactive Notification Bell & Dropdown Tab -->
        <div class="relative">
            <button @click="openNotifs = !openNotifs" 
                    type="button" 
                    class="relative bg-[#1a3c8f] hover:bg-[#152e6e] text-white w-11 h-11 rounded-full flex items-center justify-center transition shadow-md focus:outline-none">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if(($unreadCount ?? 0) > 0)
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-extrabold px-1.5 py-0.5 rounded-full border-2 border-[#fefce8] animate-pulse">
                        {{ $unreadCount }}
                    </span>
                @endif
            </button>

            <!-- Notifications Dropdown Popover Tab -->
            <div x-show="openNotifs" 
                 @click.outside="openNotifs = false" 
                 x-transition 
                 x-cloak 
                 class="absolute right-0 mt-3 w-80 sm:w-96 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden z-50">
                
                <!-- Popover Header -->
                <div class="px-5 py-4 border-b border-gray-100 dark:border-zinc-800 flex justify-between items-center bg-[#f8faff] dark:bg-zinc-800/50">
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-sm text-[#0033a0] dark:text-blue-400 uppercase tracking-wider">Notifications</span>
                        @if(($unreadCount ?? 0) > 0)
                            <span class="px-2 py-0.5 text-[10px] font-black bg-red-100 text-red-700 rounded-full">
                                {{ $unreadCount }} New
                            </span>
                        @endif
                    </div>

                    @if(($unreadCount ?? 0) > 0)
                        <form method="POST" action="{{ route('worker.notifications.mark-all-read') }}">
                            @csrf
                            <button type="submit" class="text-[11px] font-bold text-[#0033a0] dark:text-blue-400 hover:underline">
                                Mark all as read
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Notifications List -->
                <div class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-zinc-800">
                    @forelse($notifications ?? [] as $notif)
                        <a href="{{ route('worker.notifications.read', $notif->notification_id) }}" 
                           class="block px-5 py-3.5 hover:bg-blue-50/60 dark:hover:bg-zinc-800/60 transition {{ !$notif->is_read ? 'bg-blue-50/40 dark:bg-zinc-800/30' : '' }}">
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 mt-1.5 rounded-full shrink-0 {{ !$notif->is_read ? 'bg-[#0033a0]' : 'bg-gray-300' }}"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-baseline gap-2 mb-0.5">
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                            {{ $notif->title }}
                                        </h4>
                                        <span class="text-[10px] text-gray-400 shrink-0">
                                            {{ \Carbon\Carbon::parse($notif->sent_at)->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-snug line-clamp-2">
                                        {{ $notif->message }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="py-10 text-center text-gray-400 text-xs italic">
                            No notifications yet.
                        </div>
                    @endforelse
                </div>

                <!-- Popover Footer -->
                <div class="p-3 bg-gray-50 dark:bg-zinc-800/50 border-t border-gray-100 dark:border-zinc-800 text-center">
                    <a href="{{ route('worker.notifications.index') }}" class="text-xs font-bold text-[#0033a0] dark:text-blue-400 hover:underline">
                        View All Notifications →
                    </a>
                </div>
            </div>
        </div>

        <div class="border-l-2 border-[#1a3c8f] pl-6">
            <div class="text-[#1a3c8f] dark:text-blue-400 font-bold text-[15px]">{{ now()->format('F j, Y') }}</div>
            <div class="text-[#1a3c8f] dark:text-gray-300 text-[13px] opacity-90">{{ now()->format('l, h:i A') }}</div>
        </div>
    </div>
</div>

<!-- KPI Grid -->
<div class="grid grid-cols-3 gap-4 mb-6 font-sans">
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-lg p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Pending Tasks</div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none mb-2">{{ $pendingCount }}</div>
        <div class="text-xs text-gray-500"><span class="text-red-500 font-bold">Needs Attention</span></div>
    </div>
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-lg p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">In Progress</div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none mb-2">{{ $inProgressCount }}</div>
        <div class="text-xs text-gray-500"><span class="text-amber-500 font-bold">Currently working</span></div>
    </div>
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-lg p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Completed Today</div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none mb-2">{{ $completedCount }}</div>
        <div class="text-xs text-gray-500"><span class="text-emerald-500 font-bold">Great job!</span></div>
    </div>
</div>

<!-- Task List Area -->
<div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl shadow-sm mb-6 font-sans">
    <div class="px-6 py-5 border-b border-gray-100 dark:border-zinc-800 flex justify-between items-center">
        <h3 class="text-[#1a3c8f] dark:text-blue-400 font-bold text-lg">Your Assignments</h3>
        <a href="{{ route('worker.job-orders.index') }}" class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline transition">View All →</a>
    </div>
    <div class="p-6">
        <div class="space-y-3">
            @forelse($assignments->take(5) as $a)
                <div class="border border-gray-200 dark:border-zinc-800 rounded-xl p-4 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition group">
                    <div class="flex items-center gap-4">
                        <!-- Status Dot -->
                        <div class="w-3 h-3 rounded-full shrink-0 
                            @if($a->project->current_status === 'Pending') bg-red-500
                            @elseif($a->project->current_status === 'In Progress') bg-amber-500
                            @else bg-emerald-500 @endif">
                        </div>
                        <div>
                            <div class="text-slate-900 dark:text-white font-bold text-sm mb-1 group-hover:text-[#1a3c8f] transition">{{ $a->project->request->title ?? 'Project #'.$a->project->project_id }}</div>
                            <div class="text-xs text-gray-500 flex items-center gap-2">
                                <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ $a->date_assigned->format('M d, Y') }}</span>
                                <span>&bull;</span>
                                <span class="font-bold text-gray-600 dark:text-gray-300">{{ $a->project->current_status }}</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('worker.job-orders.show', $a->project->project_id) }}" class="bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700 px-4 py-2 rounded-lg text-xs font-bold transition shadow-xs">
                        View Assignment
                    </a>
                </div>
            @empty
                <div class="text-center py-10">
                    <div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-bold mb-1">No active assignments</h3>
                    <p class="text-sm text-gray-500">You're all caught up! Enjoy your break.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
