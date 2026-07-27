@extends('layouts.worker')
@section('page-title', 'Preventive Maintenance Form')

@section('content')

<!-- Header -->
<div class="flex justify-between items-center mb-6">
    <div>
        <a href="{{ route('worker.job-orders.show', $project->project_id) }}" class="text-sm font-medium text-gray-500 hover:text-[#1a3c8f] flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Job Order
        </a>
        <h1 class="text-[#1a3c8f] text-2xl font-bold flex items-center gap-3">
            Preventive Maintenance Report
        </h1>
        <p class="text-gray-500 text-sm mt-1">Project #{{ $project->project_id }} &bull; {{ $project->request->title ?? 'Untitled' }}</p>
    </div>
</div>

<div class="bg-white border border-[#1a3c8f] rounded-xl shadow-sm p-8 max-w-3xl">
    <form action="{{ route('worker.maintenance.store', $project->project_id) }}" method="POST" class="space-y-6">
        @csrf
        
        <div>
            <label class="block text-sm font-bold text-[#1a3c8f] mb-2">Work Done <span class="text-red-500">*</span></label>
            <textarea name="work_done" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" placeholder="Describe the maintenance or repair work performed..." required></textarea>
        </div>

        <div>
            <label class="block text-sm font-bold text-[#1a3c8f] mb-2">Materials Used</label>
            <textarea name="materials_used" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" placeholder="List any materials or spare parts consumed..."></textarea>
        </div>

        <div>
            <label class="block text-sm font-bold text-[#1a3c8f] mb-2">Recommendations / Notes</label>
            <textarea name="recommendations" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" placeholder="Any further recommendations for this equipment or location?"></textarea>
        </div>

        <div>
            <label class="block text-sm font-bold text-[#1a3c8f] mb-2">Date Completed <span class="text-red-500">*</span></label>
            <input type="date" name="completed_at" class="w-full md:w-1/2 px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="bg-[#1a3c8f] hover:bg-[#152e6e] text-white px-8 py-3 rounded-lg text-sm font-bold transition shadow-sm">
                Submit Report
            </button>
        </div>
    </form>
</div>

@endsection
