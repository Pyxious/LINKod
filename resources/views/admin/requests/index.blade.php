@extends('layouts.admin')
@section('page-title', 'Service Requests')

@section('content')

<!-- Page Banner Header -->
<div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-8 py-6 flex justify-between items-center mb-6 shadow-sm">
    <div>
        <h1 class="text-[#0033a0] dark:text-blue-400 text-2xl font-bold mb-1">Service Requests</h1>
        <p class="text-[#0033a0]/80 dark:text-gray-300 text-sm font-medium">Track, monitor, and manage university maintenance requisitions.</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('admin.requests.create') }}" class="bg-[#1a3c8f] hover:bg-[#152e6e] text-white px-5 py-2.5 rounded-md text-sm font-medium transition shadow-sm inline-flex items-center gap-1.5">
            + New Request
        </a>
    </div>
</div>

<!-- KPI Grid (5 Metrics: Total Requests, Submitted, On Hold, In Progress, Completed) -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6 font-sans">
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Total Requests</div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none">{{ $totalRequests }}</div>
    </div>

    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Submitted</div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none">{{ $submitted }}</div>
    </div>

    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">On Hold</div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none">{{ $onHold }}</div>
    </div>

    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">In Progress</div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none">{{ $inProgress }}</div>
    </div>

    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm">
        <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Completed</div>
        <div class="text-[#1a3c8f] dark:text-white text-3xl font-extrabold leading-none">{{ $completed }}</div>
    </div>
</div>

<!-- Livewire Component Table -->
@livewire('admin.request-table')

@endsection
