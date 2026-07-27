@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full flex flex-col font-sans min-h-[calc(100vh-64px)]">
    
    <!-- Yellow gradient header area -->
    <div class="bg-gradient-to-b from-[#fefce8] to-[#e8eef7] dark:from-[#18181b] dark:to-[#111111] pt-8 pb-6 px-6">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-[#0033a0] dark:text-blue-400 text-3xl font-black uppercase tracking-tight mb-1">My Requests</h1>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Track, filter, and manage your university service job requests</p>
                </div>
                <a href="{{ route('client.requests.create') }}" class="inline-flex items-center justify-center px-6 py-3 bg-[#0033a0] hover:bg-[#002480] text-white rounded-full font-bold text-xs transition shadow-md gap-2">
                    <span>+ Submit New Request</span>
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-800 px-6 py-5">
                    <span class="text-[#0033a0] dark:text-blue-400 text-xs font-bold uppercase tracking-wider block mb-1">Total Requests</span>
                    <span class="text-slate-900 dark:text-white text-3xl font-black leading-none">{{ $totalRequests }}</span>
                </div>

                <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-800 px-6 py-5">
                    <span class="text-amber-600 dark:text-amber-400 text-xs font-bold uppercase tracking-wider block mb-1">Pending</span>
                    <span class="text-slate-900 dark:text-white text-3xl font-black leading-none">{{ $pendingCount }}</span>
                </div>

                <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-800 px-6 py-5">
                    <span class="text-blue-600 dark:text-blue-400 text-xs font-bold uppercase tracking-wider block mb-1">In Progress</span>
                    <span class="text-slate-900 dark:text-white text-3xl font-black leading-none">{{ $inProgressCount }}</span>
                </div>

                <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-800 px-6 py-5">
                    <span class="text-emerald-600 dark:text-emerald-400 text-xs font-bold uppercase tracking-wider block mb-1">Completed</span>
                    <span class="text-slate-900 dark:text-white text-3xl font-black leading-none">{{ $completedCount }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content area -->
    <main class="max-w-6xl w-full mx-auto px-6 py-6 flex-1">
        
        <!-- Filters & Search Bar -->
        <div class="bg-[#fefce8]/60 dark:bg-[#1c1c1e] rounded-2xl border border-[#e5e1b0]/50 dark:border-zinc-800 p-4 mb-6 flex flex-col lg:flex-row items-center justify-between gap-4">
            
            <!-- Dynamic Status Filter Tabs -->
            @php $currentStatus = request('status', 'all'); @endphp
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('client.requests.index', array_filter(['status' => 'all', 'search' => request('search')])) }}" 
                   class="px-5 py-2 rounded-full text-xs font-bold transition shadow-sm {{ $currentStatus === 'all' ? 'bg-[#0033a0] text-white' : 'bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 border border-gray-200 dark:border-zinc-700' }}">
                    All ({{ $totalRequests }})
                </a>

                <a href="{{ route('client.requests.index', array_filter(['status' => 'pending', 'search' => request('search')])) }}" 
                   class="px-5 py-2 rounded-full text-xs font-bold transition shadow-sm {{ $currentStatus === 'pending' ? 'bg-[#0033a0] text-white' : 'bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 border border-gray-200 dark:border-zinc-700' }}">
                    Pending ({{ $pendingCount }})
                </a>

                <a href="{{ route('client.requests.index', array_filter(['status' => 'in_progress', 'search' => request('search')])) }}" 
                   class="px-5 py-2 rounded-full text-xs font-bold transition shadow-sm {{ $currentStatus === 'in_progress' ? 'bg-[#0033a0] text-white' : 'bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 border border-gray-200 dark:border-zinc-700' }}">
                    In Progress ({{ $inProgressCount }})
                </a>

                <a href="{{ route('client.requests.index', array_filter(['status' => 'completed', 'search' => request('search')])) }}" 
                   class="px-5 py-2 rounded-full text-xs font-bold transition shadow-sm {{ $currentStatus === 'completed' ? 'bg-[#0033a0] text-white' : 'bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 border border-gray-200 dark:border-zinc-700' }}">
                    Completed ({{ $completedCount }})
                </a>

                <a href="{{ route('client.requests.index', array_filter(['status' => 'cancelled', 'search' => request('search')])) }}" 
                   class="px-5 py-2 rounded-full text-xs font-bold transition shadow-sm {{ $currentStatus === 'cancelled' ? 'bg-[#0033a0] text-white' : 'bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 border border-gray-200 dark:border-zinc-700' }}">
                    Cancelled ({{ $cancelledCount }})
                </a>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('client.requests.index') }}" class="w-full lg:w-64">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search title or location..." 
                           class="w-full pl-3.5 pr-8 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs text-gray-700 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                    @if(request('search'))
                        <a href="{{ route('client.requests.index', array_filter(['status' => request('status')])) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs font-bold">✕</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Request Cards List -->
        <div class="space-y-4 pb-8">
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
                @endphp
                
                <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-800 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:shadow-md transition">
                    
                    <div class="flex flex-col gap-1.5 flex-1">
                        <!-- ID & Category Badge -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-black text-[#0033a0] dark:text-blue-400 tracking-wider">
                                {{ $prefix }}-{{ str_pad($r->request_id, 4, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="text-[11px] font-semibold text-gray-400">
                                • {{ $r->category->category_name ?? 'General Service' }}
                            </span>
                        </div>
                        
                        <!-- Title & Status Badge -->
                        <div class="flex items-center gap-3 flex-wrap">
                            <h3 class="text-slate-900 dark:text-white font-extrabold text-base">
                                {{ $r->title ?? 'Service Request' }}
                            </h3>

                            @php
                                $statusStyle = match(strtolower($r->current_status ?? 'pending')) {
                                    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'in progress', 'pending verification' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                                    default => 'bg-amber-50 text-amber-700 border-amber-200'
                                };
                            @endphp

                            <span class="inline-flex items-center gap-1 text-[11px] font-extrabold px-3 py-0.5 rounded-full border {{ $statusStyle }}">
                                {{ ucfirst($r->current_status ?? 'Pending') }}
                            </span>
                        </div>
                        
                        <!-- Location & Campus -->
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $r->campus ?? 'BU Main' }}</span> — <span>{{ $r->location ?? 'General' }}</span>
                        </p>
                        
                        <!-- Date -->
                        <p class="text-[11px] text-gray-400 mt-1">
                            Submitted on {{ \Carbon\Carbon::parse($r->submitted_at)->format('F d, Y \a\t h:i A') }}
                        </p>
                    </div>

                    <div class="flex-shrink-0">
                        <a href="{{ route('client.requests.show', $r->request_id) }}" class="inline-flex items-center gap-1.5 px-6 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-full font-bold text-xs transition shadow-sm whitespace-nowrap">
                            <span>View Details</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    
                </div>
            @empty
                <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-800 p-12 text-center">
                    <div class="text-gray-300 dark:text-zinc-600 mb-3">
                        <svg class="w-14 h-14 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    </div>
                    <h3 class="text-slate-900 dark:text-white font-extrabold text-lg">No requests found</h3>
                    <p class="text-gray-400 text-xs mt-1">There are no service requests matching your selected filter or search term.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
            <div class="pb-8">
                {{ $requests->links() }}
            </div>
        @endif

    </main>
</div>
@endsection
