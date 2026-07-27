@extends('layouts.worker')
@section('page-title', 'Job Orders')

@section('content')

<!-- Page Banner -->
<div class="bg-[#fefce8] border border-[#1a3c8f] rounded-xl px-8 py-6 flex justify-between items-center mb-6 shadow-sm">
    <div>
        <h1 class="text-[#1a3c8f] text-2xl font-bold mb-1">Job Orders</h1>
        <p class="text-[#1a3c8f] text-sm opacity-90">View and manage all your assigned tasks.</p>
    </div>
</div>

<!-- Table -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
        <h3 class="text-gray-800 font-bold">All Assignments</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4 font-semibold">Project ID</th>
                    <th class="px-6 py-4 font-semibold">Title / Concern</th>
                    <th class="px-6 py-4 font-semibold">Assigned Date</th>
                    <th class="px-6 py-4 font-semibold">Status</th>
                    <th class="px-6 py-4 font-semibold text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($assignments as $a)
                    <tr class="hover:bg-gray-50 transition group">
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">#{{ $a->project_id }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $a->project->request->title ?? 'Untitled' }}</div>
                            <div class="text-xs text-gray-500 mt-0.5 truncate max-w-xs">{{ $a->project->request->location ?? 'Unknown Location' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ \Carbon\Carbon::parse($a->date_assigned)->format('M d, Y') }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold 
                                @if($a->project->current_status === 'Pending') bg-red-100 text-red-800
                                @elseif($a->project->current_status === 'In Progress') bg-amber-100 text-amber-800
                                @else bg-emerald-100 text-emerald-800 @endif">
                                {{ $a->project->current_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('worker.job-orders.show', $a->project_id) }}" class="inline-flex items-center justify-center px-4 py-2 border border-[#1a3c8f] text-[#1a3c8f] bg-white rounded-md text-sm font-semibold hover:bg-[#1a3c8f] hover:text-white transition">
                                Open Task
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-4">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900 mb-1">No Job Orders Found</h3>
                            <p class="text-sm text-gray-500">You currently have no assignments.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
