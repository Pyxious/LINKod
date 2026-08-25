@extends('layouts.worker')
@section('page-title', 'Preventive Maintenance Report')

@section('content')
<div class="w-full max-w-4xl mx-auto space-y-6 font-sans">

    <!-- Header Banner -->
    <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-6 sm:px-8 py-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('worker.job-orders.show', $project->project_id) }}" class="text-xs font-bold text-gray-500 dark:text-gray-400 hover:text-[#0033a0] dark:hover:text-blue-400 flex items-center gap-1 mb-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Job Order Details</span>
            </a>
            <h1 class="text-[#0033a0] dark:text-blue-400 text-2xl sm:text-3xl font-black flex items-center gap-3">
                <span>Preventive Maintenance Report</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">
                Project #{{ $project->project_id }} &bull; Requisition: <span class="font-bold text-slate-800 dark:text-gray-200">{{ $project->request->title ?? 'General Maintenance' }}</span>
            </p>
        </div>

        <div class="shrink-0">
            <span class="px-3 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 text-xs font-extrabold uppercase rounded-full border border-emerald-300 dark:border-emerald-700 inline-flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Job Completed
            </span>
        </div>
    </div>

    @if(session('error'))
        <div class="p-4 bg-red-50 dark:bg-red-950/40 border border-red-300 dark:border-red-800 rounded-xl text-xs font-bold text-red-800 dark:text-red-300 flex items-center gap-2 shadow-xs">
            <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Form Card -->
    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm p-6 sm:p-8" x-data="{ submitting: false }">
        <form action="{{ route('worker.maintenance.store', $project->project_id) }}" method="POST" class="space-y-6" @submit="submitting = true">
            @csrf
            
            <!-- Work Done -->
            <div>
                <label class="block text-xs font-extrabold text-[#0033a0] dark:text-blue-300 uppercase tracking-wider mb-2">
                    Work Done / Scope of Accomplishment <span class="text-red-500">*</span>
                </label>
                <textarea name="work_done" 
                          rows="4" 
                          class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 text-slate-900 dark:text-white rounded-xl text-sm focus:ring-2 focus:ring-[#0033a0] focus:border-transparent outline-none transition placeholder-gray-400 dark:placeholder-gray-500" 
                          placeholder="Describe in detail the repairs, maintenance, inspections, or installations performed..." 
                          required></textarea>
            </div>

            <!-- Materials Used -->
            <div>
                <label class="block text-xs font-extrabold text-[#0033a0] dark:text-blue-300 uppercase tracking-wider mb-2">
                    Materials & Spare Parts Consumed
                </label>
                <textarea name="materials_used" 
                          rows="3" 
                          class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 text-slate-900 dark:text-white rounded-xl text-sm focus:ring-2 focus:ring-[#0033a0] focus:border-transparent outline-none transition placeholder-gray-400 dark:placeholder-gray-500" 
                          placeholder="List any consumed materials, wires, pipes, paints, or spare parts (e.g. 2 pcs PVC elbow, 1 roll teflon tape)..."></textarea>
            </div>

            <!-- Recommendations / Notes -->
            <div>
                <label class="block text-xs font-extrabold text-[#0033a0] dark:text-blue-300 uppercase tracking-wider mb-2">
                    Recommendations / Technical Notes
                </label>
                <textarea name="recommendations" 
                          rows="3" 
                          class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 text-slate-900 dark:text-white rounded-xl text-sm focus:ring-2 focus:ring-[#0033a0] focus:border-transparent outline-none transition placeholder-gray-400 dark:placeholder-gray-500" 
                          placeholder="Provide any preventative recommendations for future upkeep or warnings on equipment condition..."></textarea>
            </div>

            <!-- Date Completed -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-extrabold text-[#0033a0] dark:text-blue-300 uppercase tracking-wider mb-2">
                        Date Completed <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="completed_at" 
                           value="{{ date('Y-m-d') }}" 
                           class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 text-slate-900 dark:text-white rounded-xl text-sm font-semibold focus:ring-2 focus:ring-[#0033a0] focus:border-transparent outline-none transition" 
                           required>
                </div>
            </div>

            <!-- Submit Action -->
            <div class="pt-6 border-t border-gray-100 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                <a href="{{ route('worker.job-orders.show', $project->project_id) }}" class="w-full sm:w-auto text-center px-6 py-3 border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                    Cancel
                </a>

                <button type="submit" 
                        :disabled="submitting" 
                        class="w-full sm:w-auto px-8 py-3 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition shadow-md inline-flex items-center justify-center gap-2 disabled:opacity-60 cursor-pointer">
                    <svg x-show="submitting" x-cloak class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="submitting ? 'Submitting Report...' : 'Submit Maintenance Report'">Submit Maintenance Report</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
