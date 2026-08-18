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

<!-- Livewire Component Table & Live KPI Grid -->
@livewire('admin.request-table')

@endsection
