@extends('layouts.admin')

@section('page-title', 'Bill of Materials')

@section('content')
<div class="w-full max-w-7xl mx-auto font-sans">
    
    <!-- Page Banner Header (Matching Requests Page) -->
    <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-5 sm:px-8 py-5 sm:py-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 shadow-sm">
        <div>
            <h1 class="text-[#0033a0] dark:text-blue-400 text-xl sm:text-2xl font-bold mb-1">Bill of Materials (BOM)</h1>
            <p class="text-[#0033a0]/80 dark:text-gray-300 text-xs sm:text-sm font-medium">Track, monitor, and price university material requisitions from maintenance teams.</p>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('admin.requests.index') }}" class="w-full sm:w-auto text-center bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 text-[#0033a0] dark:text-blue-400 border border-[#0033a0]/30 dark:border-blue-600/50 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition shadow-xs inline-flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Requests</span>
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards Grid (Matching Requests Page KPI Grid) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 sm:gap-4 mb-6 font-sans">
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm">
            <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Total BOM Jobs</div>
            <div class="text-[#1a3c8f] dark:text-white text-2xl sm:text-3xl font-extrabold leading-none">{{ $counts['all'] ?? 0 }}</div>
        </div>

        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm">
            <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2 flex items-center justify-between">
                <span>Pending Pricing</span>
                @if(($counts['pending'] ?? 0) > 0)
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                @endif
            </div>
            <div class="text-[#1a3c8f] dark:text-white text-2xl sm:text-3xl font-extrabold leading-none {{ ($counts['pending'] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-400' : '' }}">
                {{ $counts['pending'] ?? 0 }}
            </div>
        </div>

        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm">
            <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Approved BOMs</div>
            <div class="text-[#1a3c8f] dark:text-white text-2xl sm:text-3xl font-extrabold leading-none text-emerald-600 dark:text-emerald-400">{{ $counts['approved'] ?? 0 }}</div>
        </div>

        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm">
            <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Approved Materials Total</div>
            <div class="text-[#1a3c8f] dark:text-white text-xl sm:text-2xl font-black leading-none truncate">
                ₱{{ number_format($counts['total_cost'] ?? 0, 2) }}
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls (Matching Requests Page Controls Bar) -->
    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-3 mb-4">
        <!-- Status Tabs -->
        <div class="flex bg-gray-100 dark:bg-zinc-800/80 p-1 rounded-lg gap-1 overflow-x-auto">
            <a href="{{ route('admin.bom.index', ['status' => 'all', 'search' => request('search')]) }}" 
               class="flex-1 md:flex-initial text-center px-3.5 py-1.5 text-xs rounded-md transition-all whitespace-nowrap {{ ($status ?? 'all') === 'all' ? 'bg-white dark:bg-zinc-900 text-gray-900 dark:text-blue-400 shadow-xs font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 font-medium' }}">
                All BOMs ({{ $counts['all'] ?? 0 }})
            </a>
            <a href="{{ route('admin.bom.index', ['status' => 'pending', 'search' => request('search')]) }}" 
               class="flex-1 md:flex-initial text-center px-3.5 py-1.5 text-xs rounded-md transition-all whitespace-nowrap {{ ($status ?? 'all') === 'pending' ? 'bg-white dark:bg-zinc-900 text-amber-600 dark:text-amber-400 shadow-xs font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 font-medium' }}">
                Pending Pricing ({{ $counts['pending'] ?? 0 }})
            </a>
            <a href="{{ route('admin.bom.index', ['status' => 'approved', 'search' => request('search')]) }}" 
               class="flex-1 md:flex-initial text-center px-3.5 py-1.5 text-xs rounded-md transition-all whitespace-nowrap {{ ($status ?? 'all') === 'approved' ? 'bg-white dark:bg-zinc-900 text-emerald-600 dark:text-emerald-400 shadow-xs font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 font-medium' }}">
                Approved ({{ $counts['approved'] ?? 0 }})
            </a>
        </div>

        <!-- Search Input -->
        <form method="GET" action="{{ route('admin.bom.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#1a3c8f] dark:text-blue-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Search requisition or client..." 
                       class="pl-9 pr-3 py-2 rounded-xl border border-[#1a3c8f]/30 dark:border-zinc-700 text-[#1a3c8f] dark:text-blue-300 text-xs font-semibold outline-none w-full bg-white dark:bg-zinc-900 shadow-2xs">
            </div>
            <button type="submit" class="px-3.5 py-2 bg-[#1a3c8f] hover:bg-[#152e6e] text-white rounded-xl text-xs font-bold transition shadow-xs shrink-0">
                Search
            </button>
        </form>
    </div>

    <!-- Mobile BOM Cards View (Matching Requests Mobile Cards View) -->
    <div class="block md:hidden space-y-3 mb-6">
        @forelse($projects as $p)
            @php
                $catName = strtolower($p->request->category->category_name ?? '');
                $prefix = match(true) {
                    str_contains($catName, 'landscaping') => 'LS',
                    str_contains($catName, 'janitorial') => 'JS',
                    str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
                    str_contains($catName, 'plumbing') => 'PLS',
                    str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
                    str_contains($catName, 'painting') || str_contains($catName, 'paint') => 'PAINT',
                    str_contains($catName, 'manpower') || str_contains($catName, 'event') => 'MAN',
                    default => 'REQ'
                };
                $reqCode = $p->request ? ($prefix . '-' . str_pad($p->request->request_id, 3, '0', STR_PAD_LEFT)) : ('PROJ-' . $p->project_id);
                $pendingInThis = $p->billOfMaterials->whereNull('date_approved')->count();
                $totalCost = $p->billOfMaterials->sum('total_cost');
                $assignedWorkers = $p->workers ?? collect();
            @endphp

            <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-mono font-bold text-xs text-[#1a3c8f] dark:text-blue-400 bg-blue-50 dark:bg-zinc-800 px-2 py-0.5 rounded">{{ $reqCode }}</span>
                    @if($pendingInThis > 0)
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300">
                            {{ $pendingInThis }} Pending Pricing
                        </span>
                    @else
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300">
                            Approved
                        </span>
                    @endif
                </div>

                <div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $p->request->title ?? 'Maintenance Project' }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Client: {{ $p->request?->client?->user->first_name ?? '' }} {{ $p->request?->client?->user->last_name ?? '' }}
                    </p>
                </div>

                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-100 dark:border-zinc-800">
                    <span>{{ $p->billOfMaterials->count() }} Material Item(s)</span>
                    <span class="font-black text-[#1a3c8f] dark:text-blue-400 text-sm">₱{{ number_format($totalCost, 2) }}</span>
                </div>

                @if($assignedWorkers->count() > 0)
                <div class="text-xs text-gray-600 dark:text-gray-300 pt-1">
                    <span class="font-semibold text-gray-400">Assigned:</span>
                    @foreach($assignedWorkers as $w)
                        <span class="inline-block bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded text-[11px] font-medium mr-1">{{ $w->staff->user->first_name ?? '' }}</span>
                    @endforeach
                </div>
                @endif

                <div class="pt-2 flex justify-between items-center text-xs border-t border-gray-100 dark:border-zinc-800">
                    <a href="{{ route('admin.requests.show', $p->request->request_id) }}#bom-section" class="text-gray-500 dark:text-gray-400 font-semibold hover:underline">View Request</a>
                    <a href="{{ route('admin.requests.show', $p->request->request_id) }}#bom-section" class="text-[#1a3c8f] dark:text-blue-400 font-bold hover:underline">Price & Manage &rarr;</a>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500 bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 text-xs">
                No Bill of Materials records found matching your filters.
            </div>
        @endforelse

        <div class="mt-4">
            {{ $projects->links() }}
        </div>
    </div>

    <!-- Desktop BOM Table View (Matching Requests Page `border-separate` 0 8px) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-separate" style="border-spacing: 0 8px;">
            <thead>
                <tr>
                    <th class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800 px-4">
                        Requisition No.
                    </th>
                    <th class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800">
                        Requestor / Title
                    </th>
                    <th class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800">
                        Assigned Personnel
                    </th>
                    <th class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800 text-center">
                        Materials Count
                    </th>
                    <th class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800 text-right">
                        Total Est. Cost
                    </th>
                    <th class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800 text-center">
                        Status
                    </th>
                    <th class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800 text-right px-4">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $p)
                    @php
                        $catName = strtolower($p->request->category->category_name ?? '');
                        $prefix = match(true) {
                            str_contains($catName, 'landscaping') => 'LS',
                            str_contains($catName, 'janitorial') => 'JS',
                            str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
                            str_contains($catName, 'plumbing') => 'PLS',
                            str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
                            str_contains($catName, 'painting') || str_contains($catName, 'paint') => 'PAINT',
                            str_contains($catName, 'manpower') || str_contains($catName, 'event') => 'MAN',
                            default => 'REQ'
                        };
                        $reqCode = $p->request ? ($prefix . '-' . str_pad($p->request->request_id, 3, '0', STR_PAD_LEFT)) : ('PROJ-' . $p->project_id);
                        $pendingInThis = $p->billOfMaterials->whereNull('date_approved')->count();
                        $totalCost = $p->billOfMaterials->sum('total_cost');
                        $assignedWorkers = $p->workers ?? collect();
                    @endphp
                    <tr class="bg-white dark:bg-[#1c1c1e] hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition shadow-xs group">
                        
                        <!-- Requisition No -->
                        <td class="px-4 py-4 border-y border-l border-gray-200 dark:border-zinc-800 rounded-l-lg">
                            <span class="font-bold text-[#1a3c8f] dark:text-blue-400 text-[13px] tracking-wide">{{ $reqCode }}</span>
                            <div class="text-[11px] text-gray-600 dark:text-gray-400 font-medium">Project #{{ $p->project_id }}</div>
                        </td>

                        <!-- Requestor / Title -->
                        <td class="py-4 border-y border-gray-200 dark:border-zinc-800">
                            <div class="font-bold text-gray-900 dark:text-white text-[13px]">{{ $p->request->title ?? 'General Maintenance' }}</div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-400">
                                By: {{ $p->request?->client?->user->first_name ?? '' }} {{ $p->request?->client?->user->last_name ?? '' }} 
                                ({{ $p->request?->client?->user->email_account ?? 'Client' }})
                            </div>
                        </td>

                        <!-- Assigned Personnel -->
                        <td class="py-4 border-y border-gray-200 dark:border-zinc-800">
                            @if($assignedWorkers->count() > 0)
                                <div class="flex flex-wrap gap-1 items-center">
                                    @foreach($assignedWorkers as $w)
                                        <span class="inline-flex items-center gap-1 bg-blue-50 dark:bg-zinc-800 text-[#1a3c8f] dark:text-blue-300 px-2 py-0.5 rounded-full text-[11px] font-semibold border border-blue-100 dark:border-zinc-700">
                                            <span class="w-3.5 h-3.5 rounded-full bg-[#1a3c8f] text-white flex items-center justify-center text-[8px] font-extrabold">
                                                {{ strtoupper(substr($w->staff->user->first_name ?? 'W', 0, 1)) }}
                                            </span>
                                            {{ $w->staff->user->first_name ?? '' }} {{ $w->staff->user->last_name ?? '' }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">Unassigned</span>
                            @endif
                        </td>

                        <!-- Materials Count -->
                        <td class="py-4 border-y border-gray-200 dark:border-zinc-800 text-center">
                            <span class="font-bold text-slate-800 dark:text-gray-200 bg-gray-100 dark:bg-zinc-800 px-2.5 py-1 rounded-md text-xs">
                                {{ $p->billOfMaterials->count() }} item(s)
                            </span>
                        </td>

                        <!-- Total Est. Cost -->
                        <td class="py-4 border-y border-gray-200 dark:border-zinc-800 text-right font-black text-slate-900 dark:text-white text-[13px]">
                            ₱{{ number_format($totalCost, 2) }}
                        </td>

                        <!-- Status -->
                        <td class="py-4 border-y border-gray-200 dark:border-zinc-800 text-center">
                            @if($pendingInThis > 0)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-700 border border-amber-300 dark:bg-amber-950/40 dark:text-amber-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    {{ $pendingInThis }} Pending
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Approved
                                </span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="px-4 py-4 border-y border-r border-gray-200 dark:border-zinc-800 rounded-r-lg text-right">
                            <a href="{{ route('admin.requests.show', $p->request->request_id) }}#bom-section" 
                               class="text-[#1a3c8f] dark:text-blue-400 font-bold hover:underline text-xs inline-flex items-center gap-1">
                                <span>Manage & Price</span>
                                <span>&rarr;</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-gray-500 bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 text-xs">
                            No Bill of Materials records found matching your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($projects->hasPages())
            <div class="mt-4">
                {{ $projects->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
