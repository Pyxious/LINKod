@extends('layouts.worker')
@section('page-title', 'Notifications')

@section('content')

<div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6 max-w-3xl mx-auto">
    <div class="px-6 py-5 border-b border-gray-100">
        <h1 class="text-[#1a3c8f] font-bold text-xl">Your Notifications</h1>
        <p class="text-sm text-gray-500 mt-1">Updates and alerts for your assignments</p>
    </div>
    
    <div class="divide-y divide-gray-100">
        @forelse($notifications as $notification)
            <a href="{{ $notification->action_url ?? '#' }}" class="block p-5 hover:bg-gray-50 transition {{ $notification->is_read ? 'opacity-80' : 'bg-blue-50/30' }}">
                <div class="flex gap-4">
                    <div class="w-10 h-10 bg-[#1a3c8f]/10 text-[#1a3c8f] rounded-full flex items-center justify-center shrink-0">
                        @if($notification->type === 'job_assigned')
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-gray-900">{{ $notification->title }}</span>
                            @if(!$notification->is_read)
                                <span class="w-2 h-2 bg-red-500 rounded-full inline-block"></span>
                            @endif
                        </div>
                        <p class="text-gray-600 text-sm">{{ $notification->message }}</p>
                        <div class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $notification->sent_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="text-center py-10">
                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-3">
                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <h3 class="text-gray-900 font-bold mb-1">No notifications</h3>
                <p class="text-sm text-gray-500">You don't have any notifications at the moment.</p>
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
