@extends('layouts.admin')
@section('page-title', 'Service Requests')

@section('content')

<!-- Page Banner Header -->
<div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-5 sm:px-8 py-5 sm:py-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 shadow-sm">
    <div>
        <h1 class="text-[#0033a0] dark:text-blue-400 text-xl sm:text-2xl font-bold mb-1">Service Requests</h1>
        <p class="text-[#0033a0]/80 dark:text-gray-300 text-xs sm:text-sm font-medium">Track, monitor, and manage university maintenance requisitions.</p>
    </div>
    <div class="flex gap-3 w-full sm:w-auto">
        <a href="{{ route('admin.requests.create') }}" class="w-full sm:w-auto text-center bg-[#1a3c8f] hover:bg-[#152e6e] text-white px-5 py-2.5 rounded-md text-xs sm:text-sm font-medium transition shadow-sm inline-flex items-center justify-center gap-1.5">
            + New Request
        </a>
    </div>
</div>

<!-- KPI Grid (5 Metrics: Total Requests, Submitted, On Hold, In Progress, Completed) -->
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3.5 sm:gap-4 mb-6 font-sans">
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Total Requests</div>
        <div class="text-[#1a3c8f] dark:text-white text-2xl sm:text-3xl font-extrabold leading-none">{{ $totalRequests }}</div>
    </div>

    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Submitted</div>
        <div class="text-[#1a3c8f] dark:text-white text-2xl sm:text-3xl font-extrabold leading-none">{{ $submitted }}</div>
    </div>

    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">On Hold</div>
        <div class="text-[#1a3c8f] dark:text-white text-2xl sm:text-3xl font-extrabold leading-none">{{ $onHold }}</div>
    </div>

    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">In Progress</div>
        <div class="text-[#1a3c8f] dark:text-white text-2xl sm:text-3xl font-extrabold leading-none">{{ $inProgress }}</div>
    </div>

    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm col-span-2 sm:col-span-1">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Completed</div>
        <div class="text-[#1a3c8f] dark:text-white text-2xl sm:text-3xl font-extrabold leading-none">{{ $completed }}</div>
    </div>
</div>

<!-- Livewire Component Table -->
@livewire('admin.request-table')

@endsection
