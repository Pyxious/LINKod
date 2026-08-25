@extends('layouts.admin')
@section('page-title', 'Service Requests')

@section('content')

<!-- Page Banner Header -->
<div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-5 sm:px-8 py-5 sm:py-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 shadow-sm">
    <div>
        <h1 class="text-[#0033a0] dark:text-blue-400 text-xl sm:text-2xl font-bold mb-1">Service Requests</h1>
        <p class="text-[#0033a0]/80 dark:text-gray-300 text-xs sm:text-sm font-medium">Track, monitor, and manage university maintenance requisitions.</p>
    </div>
    <div class="flex items-center gap-3 w-full sm:w-auto flex-wrap">
        @php
            $pendingBomCount = \App\Models\BillOfMaterials::whereNull('date_approved')->count();
        @endphp
        <a href="{{ route('admin.bom.index') }}" class="w-full sm:w-auto text-center bg-white dark:bg-zinc-800 hover:bg-blue-50 dark:hover:bg-zinc-700 text-[#0033a0] dark:text-blue-400 border-2 border-[#0033a0]/30 dark:border-blue-600/50 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition shadow-xs inline-flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>Bill of Materials (BOM)</span>
            @if($pendingBomCount > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-white animate-pulse">
                    {{ $pendingBomCount }}
                </span>
            @endif
        </a>

        <a href="{{ route('admin.requests.create') }}" class="w-full sm:w-auto text-center bg-[#0033a0] hover:bg-[#002480] text-white px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition shadow-sm inline-flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>+ New Request</span>
        </a>
    </div>

</div>

<!-- Livewire Component Table & Live KPI Grid -->
@livewire('admin.request-table')

@endsection
