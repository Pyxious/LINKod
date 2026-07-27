@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full flex flex-col font-sans min-h-[calc(100vh-64px)] bg-slate-50/50 dark:bg-[#111111]">
    
    <!-- Top Hero Section (Pale yellow full-width banner) -->
    <div class="bg-[#FFFDE6] dark:bg-[#18181b] border-b border-amber-100/60 dark:border-zinc-800 py-8 px-6 md:px-12">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-[#042B74] dark:text-blue-400 text-3xl font-bold tracking-tight">My Requests</h1>
                <p class="text-[#47658F] dark:text-gray-400 text-sm font-medium mt-1">Track and manage your service job requests</p>
            </div>
            <div>
                <a href="{{ route('client.requests.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-[#0038A8] hover:bg-[#002B82] text-white rounded-lg font-semibold text-sm transition shadow-xs gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>New Request</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="max-w-6xl w-full mx-auto px-6 md:px-8 py-8 flex-1">
        
        <!-- Summary Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5 mb-8">
            <!-- Total Requests -->
            <div class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 p-6 shadow-2xs">
                <span class="text-[#254378] dark:text-blue-300 text-sm font-semibold block mb-2">Total Requests</span>
                <span class="text-[#042B74] dark:text-white text-3xl font-bold block leading-none">{{ $totalRequests }}</span>
            </div>

            <!-- Pending -->
            <div class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 p-6 shadow-2xs">
                <span class="text-[#254378] dark:text-blue-300 text-sm font-semibold block mb-2">Pending</span>
                <span class="text-[#042B74] dark:text-white text-3xl font-bold block leading-none">{{ $pendingCount }}</span>
            </div>

            <!-- In Progress -->
            <div class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 p-6 shadow-2xs">
                <span class="text-[#254378] dark:text-blue-300 text-sm font-semibold block mb-2">In Progress</span>
                <span class="text-[#042B74] dark:text-white text-3xl font-bold block leading-none">{{ $inProgressCount }}</span>
            </div>

            <!-- Completed -->
            <div class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 p-6 shadow-2xs">
                <span class="text-[#254378] dark:text-blue-300 text-sm font-semibold block mb-2">Completed</span>
                <span class="text-[#042B74] dark:text-white text-3xl font-bold block leading-none">{{ $completedCount }}</span>
            </div>
        </div>

        <!-- Main Outer Box (Soft Blue Container with Border) -->
        <div class="bg-[#EBF3FE] dark:bg-[#151d2a] border border-[#7DAAF4] dark:border-blue-800 rounded-2xl md:rounded-3xl p-6 md:p-8 shadow-2xs">
            
            <!-- Filter Bar & Search Form -->
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 mb-6">
                
                <!-- Status Filter Pills -->
                @php $currentStatus = request('status', 'all'); @endphp
                <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                    <a href="{{ route('client.requests.index', array_filter(['status' => 'all', 'search' => request('search')])) }}" 
                       class="px-5 py-2 rounded-full text-sm font-semibold transition shadow-2xs {{ $currentStatus === 'all' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        All
                    </a>

                    <a href="{{ route('client.requests.index', array_filter(['status' => 'pending', 'search' => request('search')])) }}" 
                       class="px-5 py-2 rounded-full text-sm font-semibold transition shadow-2xs {{ $currentStatus === 'pending' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        Pending
                    </a>

                    <a href="{{ route('client.requests.index', array_filter(['status' => 'in_progress', 'search' => request('search')])) }}" 
                       class="px-5 py-2 rounded-full text-sm font-semibold transition shadow-2xs {{ $currentStatus === 'in_progress' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        In Progress
                    </a>

                    <a href="{{ route('client.requests.index', array_filter(['status' => 'completed', 'search' => request('search')])) }}" 
                       class="px-5 py-2 rounded-full text-sm font-semibold transition shadow-2xs {{ $currentStatus === 'completed' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        Completed
                    </a>

                    <a href="{{ route('client.requests.index', array_filter(['status' => 'follow_up', 'search' => request('search')])) }}" 
                       class="px-5 py-2 rounded-full text-sm font-semibold transition shadow-2xs {{ $currentStatus === 'follow_up' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        Follow Up
                    </a>

                    <a href="{{ route('client.requests.index', array_filter(['status' => 'cancelled', 'search' => request('search')])) }}" 
                       class="px-5 py-2 rounded-full text-sm font-semibold transition shadow-2xs {{ $currentStatus === 'cancelled' ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                        Cancelled
                    </a>
                </div>

                <!-- Search Input Field -->
                <form method="GET" action="{{ route('client.requests.index') }}" class="w-full lg:w-72">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search requests..." 
                               class="w-full px-4 py-2 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm text-gray-800 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:border-[#0038A8] shadow-2xs">
                        @if(request('search'))
                            <a href="{{ route('client.requests.index', array_filter(['status' => request('status')])) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs font-bold">✕</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Request Cards List -->
            <div class="space-y-4">
                @forelse($requests as $r)
                    @php
                        $catName = strtolower($r->category->category_name ?? '');
                        $prefix = match(true) {
                            str_contains($catName, 'landscaping') => 'LS',
                            str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
                            str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
                            str_contains($catName, 'plumbing') => 'PS',
                            default => 'REQ'
                        };
                        $displayStatus = ucfirst($r->current_status ?? 'Pending');
                    @endphp
                    
                    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl md:rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-2xs hover:shadow-xs transition">
                        
                        <div class="flex flex-col gap-1 flex-1">
                            <!-- Code / ID -->
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">
                                {{ $prefix }}-{{ str_pad($r->request_id, 3, '0', STR_PAD_LEFT) }}
                            </span>
                            
                            <!-- Title & Status with Arrow -->
                            <div class="flex items-center gap-4 flex-wrap mt-0.5">
                                <h3 class="text-gray-900 dark:text-white font-bold text-base md:text-lg">
                                    {{ $r->title ?? ($r->category->category_name ?? 'Service Request') }}
                                </h3>

                                <span class="inline-flex items-center gap-1.5 text-sm font-bold text-[#0038A8] dark:text-blue-400">
                                    <span>{{ $displayStatus }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </span>
                            </div>
                            
                            <!-- Department / Location -->
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mt-2">
                                {{ $r->campus ?? 'BU Main' }} {{ $r->location ? '— ' . $r->location : '' }}
                            </p>
                            
                            <!-- Submission Date & Time -->
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                {{ \Carbon\Carbon::parse($r->submitted_at)->format('F d, Y h:i A') }}
                            </p>
                        </div>

                        <!-- Action Button -->
                        <div class="flex-shrink-0 pt-2 md:pt-0">
                            <a href="{{ route('client.requests.show', $r->request_id) }}" class="inline-flex items-center justify-center px-6 py-2.5 bg-[#0038A8] hover:bg-[#002B82] text-white rounded-lg font-semibold text-sm transition shadow-xs whitespace-nowrap">
                                View Details
                            </a>
                        </div>
                        
                    </div>
                @empty
                    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-12 text-center">
                        <div class="text-gray-300 dark:text-zinc-600 mb-3">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <h3 class="text-gray-800 dark:text-white font-bold text-base">No requests found</h3>
                        <p class="text-gray-400 text-xs mt-1">There are no service requests matching your selected filter or search term.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($requests->hasPages())
                <div class="pt-6">
                    {{ $requests->links() }}
                </div>
            @endif

        </div>

    </main>
</div>
@endsection
