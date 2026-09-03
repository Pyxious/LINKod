@extends('layouts.worker')
@section('page-title', 'Job Order Details')

@section('content')

@php
    $req = $project->request;
    $reqId = $project->request_id;
    $catName = strtolower($req->category->category_name ?? '');
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
    $reqCode = $reqId ? ($prefix . '-' . str_pad($reqId, 3, '0', STR_PAD_LEFT)) : ('REQ-'.str_pad($project->project_id, 3, '0', STR_PAD_LEFT));
    
    $clientUser = $req?->client?->user;
    $clientName = $clientUser ? ($clientUser->first_name . ' ' . $clientUser->last_name) : 'Client Requestor';
    $clientEmail = $clientUser?->email_account ?? 'No email provided';
    $clientPhone = $clientUser?->contact_number ?? $req?->client?->contact_number ?? 'Not Provided';
    $initials = strtoupper(substr($clientUser?->first_name ?? 'K', 0, 1));
@endphp

<!-- Header -->
<div class="flex justify-between items-center mb-6">
    <div>
        <a href="{{ route('worker.job-orders.index') }}" class="text-xs font-semibold text-gray-500 hover:text-[#0038A8] flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Job Orders
        </a>
        <h1 class="text-[#042B74] dark:text-blue-400 text-2xl font-bold flex items-center gap-3">
            Requisition #{{ $reqCode }}
            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                @if($project->current_status === 'Pending') bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/40 dark:text-red-300
                @elseif($project->current_status === 'In Progress') bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300
                @elseif($project->current_status === 'Pending Verification') bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300
                @else bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 @endif">
                {{ $project->current_status }}
            </span>
    </div>
</div>


<!-- Main Top Section: 1st, 2nd, 3rd boxes on Left, Job Details + Client Contact on Right -->
<div class="flex flex-col md:flex-row gap-6 items-start w-full">

    <!-- 1st, 2nd, 3rd Boxes (Left Side) -->
    <div class="flex-1 min-w-0 w-full space-y-6">
        <!-- 1st Box: Issue Description & Supporting Documents -->
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl shadow-xs p-7">
            @php
                $catName = strtolower($req->category->category_name ?? '');
                $isManpower = str_contains($catName, 'manpower') || str_contains($catName, 'event');
                $m = $req->manpower_details ?? [];
            @endphp

            @if($isManpower)
                <div class="space-y-4 mb-2">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3">
                        <h2 class="text-base sm:text-lg font-bold text-[#0038A8] dark:text-blue-400">Request Details &amp; Specification</h2>
                        <span class="px-2.5 py-0.5 bg-blue-50 text-[#0038A8] dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800 rounded-full text-xs font-bold">
                            Manpower Services
                        </span>
                    </div>

                    <!-- 1. Activity / Event Overview Box -->
                    <div class="bg-blue-50/60 dark:bg-zinc-800/60 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                        <div class="flex items-center justify-between gap-2 mb-1.5 flex-wrap">
                            <div class="text-[11px] font-bold text-[#0038A8] dark:text-blue-300 uppercase tracking-wider">
                                Activity / Event
                            </div>
                            @if(!empty($m['event_date']))
                                <span class="px-2.5 py-0.5 bg-blue-100 dark:bg-blue-950 text-[#0038A8] dark:text-blue-300 rounded-md text-[11px] font-bold">
                                    Event Date: {{ $m['event_date'] }}
                                </span>
                            @endif
                        </div>
                        <div class="text-sm font-bold text-slate-900 dark:text-white">
                            {{ $m['activity_title'] ?: ($req->title ?? 'Activity') }}
                        </div>
                    </div>

                    <!-- 2. Preparation Box (if provided) -->
                    @if(!empty($m['prep_details']))
                        @php
                            $prepTimeStr = (!empty($m['prep_regular']) ? ('Regular Time: ' . ($m['prep_regular_time'] ?? '8:00 - 12:00 / 1:00 - 5:00')) : '') 
                                         . (!empty($m['prep_overtime']) ? ((!empty($m['prep_regular']) ? ' • ' : '') . 'Overtime: ' . ($m['prep_overtime_time'] ?? '5:00 PM onwards')) : '');
                        @endphp
                        <div class="bg-slate-50 dark:bg-zinc-800/60 p-4 rounded-xl border border-gray-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
                                <div class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Preparation
                                </div>
                                @if(!empty($m['prep_date']) || $prepTimeStr)
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if(!empty($m['prep_date']))
                                            <span class="px-2 py-0.5 bg-gray-200/80 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 rounded text-[10.5px] font-bold">
                                                {{ $m['prep_date'] }}
                                            </span>
                                        @endif
                                        @if($prepTimeStr)
                                            <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950/60 text-[#0038A8] dark:text-blue-300 border border-blue-100 dark:border-blue-900 rounded text-[10.5px] font-semibold">
                                                {{ $prepTimeStr }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="text-xs sm:text-sm text-slate-800 dark:text-gray-200 font-medium whitespace-pre-line leading-relaxed">
                                {{ $m['prep_details'] }}
                            </div>
                        </div>
                    @endif

                    <!-- 3. Event Assistance Box (if provided) -->
                    @if(!empty($m['assistance_details']))
                        @php
                            $assistTimeStr = (!empty($m['assistance_regular']) ? ('Regular Time: ' . ($m['assistance_regular_time'] ?? '8:00 - 12:00 / 1:00 - 5:00')) : '') 
                                           . (!empty($m['assistance_overtime']) ? ((!empty($m['assistance_regular']) ? ' • ' : '') . 'Overtime: ' . ($m['assistance_overtime_time'] ?? '5:00 PM onwards')) : '');
                        @endphp
                        <div class="bg-slate-50 dark:bg-zinc-800/60 p-4 rounded-xl border border-gray-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
                                <div class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Event Assistance
                                </div>
                                @if(!empty($m['assistance_date']) || $assistTimeStr)
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if(!empty($m['assistance_date']))
                                            <span class="px-2 py-0.5 bg-gray-200/80 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 rounded text-[10.5px] font-bold">
                                                {{ $m['assistance_date'] }}
                                            </span>
                                        @endif
                                        @if($assistTimeStr)
                                            <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950/60 text-[#0038A8] dark:text-blue-300 border border-blue-100 dark:border-blue-900 rounded text-[10.5px] font-semibold">
                                                {{ $assistTimeStr }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="text-xs sm:text-sm text-slate-800 dark:text-gray-200 font-medium whitespace-pre-line leading-relaxed">
                                {{ $m['assistance_details'] }}
                            </div>
                        </div>
                    @endif

                    <!-- 4. Clearing / Teardown Box (if provided) -->
                    @if(!empty($m['clearing_details']))
                        @php
                            $clearTimeStr = (!empty($m['clearing_regular']) ? ('Regular Time: ' . ($m['clearing_regular_time'] ?? '8:00 - 12:00 / 1:00 - 5:00')) : '') 
                                          . (!empty($m['clearing_overtime']) ? ((!empty($m['clearing_regular']) ? ' • ' : '') . 'Overtime: ' . ($m['clearing_overtime_time'] ?? '5:00 PM onwards')) : '');
                        @endphp
                        <div class="bg-slate-50 dark:bg-zinc-800/60 p-4 rounded-xl border border-gray-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
                                <div class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Clearing / Teardown
                                </div>
                                @if(!empty($m['clearing_date']) || $clearTimeStr)
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if(!empty($m['clearing_date']))
                                            <span class="px-2 py-0.5 bg-gray-200/80 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 rounded text-[10.5px] font-bold">
                                                {{ $m['clearing_date'] }}
                                            </span>
                                        @endif
                                        @if($clearTimeStr)
                                            <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950/60 text-[#0038A8] dark:text-blue-300 border border-blue-100 dark:border-blue-900 rounded text-[10.5px] font-semibold">
                                                {{ $clearTimeStr }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="text-xs sm:text-sm text-slate-800 dark:text-gray-200 font-medium whitespace-pre-line leading-relaxed">
                                {{ $m['clearing_details'] }}
                            </div>
                        </div>
                    @endif

                    <!-- 5. Additional Notes Box (if provided) -->
                    @if(!empty($m['additional_notes']))
                        <div class="bg-slate-50 dark:bg-zinc-800/60 p-4 rounded-xl border border-gray-200 dark:border-zinc-700">
                            <div class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                                Additional Notes &amp; Special Instructions
                            </div>
                            <div class="text-xs sm:text-sm text-slate-800 dark:text-gray-200 font-medium whitespace-pre-line leading-relaxed">
                                {{ $m['additional_notes'] }}
                            </div>
                        </div>
                    @endif

                    <!-- 6. General Description (if provided) -->
                    @if(!empty($m['general_description']))
                        <div class="bg-slate-50 dark:bg-zinc-800/60 p-4 rounded-xl border border-gray-200 dark:border-zinc-700">
                            <div class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                                General Description
                            </div>
                            <div class="text-xs sm:text-sm text-slate-800 dark:text-gray-200 font-medium whitespace-pre-line leading-relaxed">
                                {{ $m['general_description'] }}
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ $req->title ?? 'Untitled Job Order' }}</h2>
                
                <div class="prose prose-sm text-gray-600 dark:text-gray-300 max-w-none whitespace-pre-line">
                    {{ $req->display_description ?? 'No description provided by the client.' }}
                </div>
            @endif

            
            @if($req->attachment)
                @php
                    $isImg = Str::endsWith(strtolower($req->attachment), ['.jpg', '.jpeg', '.png', '.webp']);
                    $attachUrl = Storage::url($req->attachment);
                @endphp
                <div class="mt-6 border-t border-gray-100 dark:border-zinc-800 pt-6" x-data="{ attModal: false }">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-3">Supporting Documents</h3>
                    <div @click="attModal = true" class="inline-flex items-center gap-3 p-3 border border-gray-200 dark:border-zinc-700 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800 transition w-full max-w-sm cursor-pointer group">
                        @if($isImg)
                            <img src="{{ $attachUrl }}" alt="Attachment" class="w-12 h-12 object-cover rounded-lg border border-gray-200 dark:border-zinc-700 shrink-0">
                        @else
                            <div class="bg-blue-50 dark:bg-blue-950 text-[#0038A8] dark:text-blue-400 w-12 h-12 rounded-lg flex items-center justify-center font-black text-xs shrink-0">
                                PDF
                            </div>
                        @endif
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-[#0038A8] dark:group-hover:text-blue-400 transition flex items-center gap-1.5">
                                <span>View Attachment</span>
                                <svg class="w-3.5 h-3.5 text-[#0038A8] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </div>
                            <div class="text-xs text-gray-500">Click to preview in popup modal</div>
                        </div>
                    </div>

                    <!-- Lightbox Modal for Supporting Attachment -->
                    <div x-show="attModal" 
                         x-cloak 
                         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/80 backdrop-blur-xs"
                         @click.outside="attModal = false" 
                         @keydown.escape.window="attModal = false">
                        <div class="relative max-w-4xl w-full max-h-[90vh] bg-zinc-900 rounded-2xl overflow-hidden shadow-2xl border border-zinc-700 flex flex-col items-center">
                            <div class="w-full flex items-center justify-between py-3 px-5 bg-zinc-800 text-white border-b border-zinc-700">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-200">Supporting Attachment</span>
                                <div class="flex items-center gap-3">
                                    <a href="{{ $attachUrl }}" download class="text-xs text-blue-400 hover:text-blue-300 font-semibold inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Download
                                    </a>
                                    <button type="button" @click="attModal = false" class="p-1.5 text-gray-400 hover:text-white hover:bg-zinc-700 rounded-lg transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="w-full p-4 flex items-center justify-center overflow-auto max-h-[80vh] bg-black/50">
                                @if($isImg)
                                    <img src="{{ $attachUrl }}" alt="Attachment" class="max-h-[75vh] w-auto max-w-full object-contain rounded-lg shadow-lg">
                                @else
                                    <iframe src="{{ $attachUrl }}" class="w-full h-[75vh] rounded-lg border-0 bg-white"></iframe>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>


        @php
            $beforeHistory = $project->histories->where('current_status', 'In Progress')->whereNotNull('proof_attachment')->last();
            $afterHistory = $project->histories->whereIn('current_status', ['Pending Verification', 'Completed'])->whereNotNull('proof_attachment')->last();
            $hasProofPhotos = ($beforeHistory && $beforeHistory->proof_attachment) || ($afterHistory && $afterHistory->proof_attachment);
        @endphp

        @if($hasProofPhotos)
            <!-- Photo Evidence & Proof of Work Card (Visible to Worker at all stages) -->
            <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm p-6 space-y-4"
                 x-data="{ lightboxOpen: false, lightboxImg: '', lightboxTitle: '' }">
                
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3 flex-wrap gap-2">
                    <div>
                        <h2 class="text-base font-bold text-[#0033a0] dark:text-blue-400">
                            Submitted Photo Proofs
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Photos captured and submitted as evidence for this job order.
                        </p>
                    </div>
                    <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-300 border border-blue-100 dark:border-blue-900 rounded-lg text-xs font-bold">
                        Proof of Work
                    </span>
                </div>

                <!-- 2-Column Photo Grid: Before Photo & After Photo -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Before Photo Card -->
                    <div class="bg-gray-50/70 dark:bg-zinc-800/40 p-4 rounded-xl border border-gray-200 dark:border-zinc-700 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] sm:text-[10.5px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 dark:border-amber-700 shrink-0">
                                    BEFORE WORK PHOTO
                                </span>
                                @if($beforeHistory)
                                    <span class="text-[10px] text-gray-400 font-medium whitespace-nowrap shrink-0">{{ \Carbon\Carbon::parse($beforeHistory->updated_at)->format('M d, Y h:i A') }}</span>
                                @endif
                            </div>

                            @if($beforeHistory && $beforeHistory->proof_attachment)
                                <div @click="lightboxOpen = true; lightboxImg = '{{ Storage::url($beforeHistory->proof_attachment) }}'; lightboxTitle = 'Before Work Photo'" 
                                     class="block group relative overflow-hidden rounded-xl border border-gray-200 dark:border-zinc-700 bg-black/5 dark:bg-black/40 p-2 cursor-pointer transition hover:border-amber-400">
                                    <img src="{{ Storage::url($beforeHistory->proof_attachment) }}" alt="Before Work" class="w-full max-h-56 object-contain rounded-lg group-hover:scale-[1.01] transition duration-200 mx-auto">
                                    <div class="absolute inset-0 bg-black/35 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1.5 rounded-xl">
                                        <span class="bg-black/70 px-3 py-1.5 rounded-lg backdrop-blur-xs shadow-sm flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Click to View Full</span>
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="min-h-[140px] bg-gray-100 dark:bg-zinc-800/40 rounded-xl flex items-center justify-center text-xs text-gray-400 font-medium border border-dashed border-gray-200 dark:border-zinc-700">
                                    No before photo recorded
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- After Photo Card -->
                    <div class="bg-gray-50/70 dark:bg-zinc-800/40 p-4 rounded-xl border border-gray-200 dark:border-zinc-700 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] sm:text-[10.5px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 shrink-0">
                                    AFTER WORK PHOTO (COMPLETION)
                                </span>
                                @if($afterHistory)
                                    <span class="text-[10px] text-gray-400 font-medium whitespace-nowrap shrink-0">{{ \Carbon\Carbon::parse($afterHistory->updated_at)->format('M d, Y h:i A') }}</span>
                                @endif
                            </div>

                            @if($afterHistory && $afterHistory->proof_attachment)
                                <div @click="lightboxOpen = true; lightboxImg = '{{ Storage::url($afterHistory->proof_attachment) }}'; lightboxTitle = 'After Work Photo (Completion)'" 
                                     class="block group relative overflow-hidden rounded-xl border border-gray-200 dark:border-zinc-700 bg-black/5 dark:bg-black/40 p-2 cursor-pointer transition hover:border-emerald-400">
                                    <img src="{{ Storage::url($afterHistory->proof_attachment) }}" alt="After Work" class="w-full max-h-56 object-contain rounded-lg group-hover:scale-[1.01] transition duration-200 mx-auto">
                                    <div class="absolute inset-0 bg-black/35 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1.5 rounded-xl">
                                        <span class="bg-black/70 px-3 py-1.5 rounded-lg backdrop-blur-xs shadow-sm flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Click to View Full</span>
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="min-h-[140px] bg-gray-100 dark:bg-zinc-800/40 rounded-xl flex items-center justify-center text-xs text-gray-400 font-medium border border-dashed border-gray-200 dark:border-zinc-700">
                                    Pending completion photo upload
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Lightbox Preview Modal -->
                <div x-show="lightboxOpen" 
                     x-cloak 
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/80 backdrop-blur-xs" 
                     @click.outside="lightboxOpen = false" 
                     @keydown.escape.window="lightboxOpen = false">
                    <div class="relative max-w-4xl w-full max-h-[90vh] bg-zinc-900 rounded-2xl overflow-hidden shadow-2xl border border-zinc-700 flex flex-col">
                        <div class="w-full flex items-center justify-between py-3 px-5 bg-zinc-800 text-white border-b border-zinc-700 shrink-0">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-200" x-text="lightboxTitle">Photo Preview</span>
                            <div class="flex items-center gap-2">
                                <a :href="lightboxImg" target="_blank" download class="p-1.5 text-gray-300 hover:text-white hover:bg-zinc-700 rounded-lg transition" title="Open in New Tab / Download">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                <button type="button" @click="lightboxOpen = false" class="p-1.5 text-gray-400 hover:text-white hover:bg-zinc-700 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="w-full p-4 flex items-center justify-center overflow-auto max-h-[78vh] bg-black/60">
                            <img :src="lightboxImg" alt="Proof Preview" class="max-h-[72vh] w-auto max-w-full object-contain rounded-lg shadow-lg">
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(!in_array($project->current_status, ['Completed', 'Pending Verification']))

        <!-- 2nd Box: Update Task Progress & Attach Proofs -->
        @php
            // On Hold = BOM submitted/approved, next step is In Progress
            // In Progress = currently working, next step is Completed
            $defaultNextStatus = ($project->current_status === 'In Progress') ? 'Completed' : 'In Progress';
        @endphp
        <div class="bg-white dark:bg-[#1c1c1e] border-2 border-[#1a3c8f]/30 dark:border-blue-700/60 rounded-2xl shadow-sm p-6 sm:p-7"
             x-data="workerTaskProgress({{ $project->project_id }}, '{{ $defaultNextStatus }}', {{ json_encode($project->request->title ?? 'Project #' . $project->project_id) }}, '{{ route('worker.task-progress.sync', $project->project_id) }}')">

             <div class="flex items-center justify-between gap-3 mb-5 pb-3 border-b border-gray-100 dark:border-zinc-800">
                 <h3 class="text-[#1a3c8f] dark:text-blue-400 font-extrabold text-lg flex items-center gap-2">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                     <span>Update Task Progress</span>
                 </h3>
                 <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                     Action Required
                 </span>
             </div>

              @php
                  $hasPendingBom = $project->billOfMaterials->where('date_approved', null)->isNotEmpty();
                  $hasApprovedBom = $project->billOfMaterials->where('date_approved', '!=', null)->isNotEmpty();
              @endphp

              @if($project->current_status === 'On Hold' && $hasPendingBom)
              {{-- BOM still awaiting admin approval — don't let worker update yet --}}
              <div class="p-4 bg-amber-50 dark:bg-amber-950/30 border-2 border-amber-300 dark:border-amber-700 rounded-2xl mb-5 text-slate-800 dark:text-amber-100 text-xs sm:text-sm font-semibold flex items-start gap-3 shadow-xs">
                  <div class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0 mt-0.5">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  </div>
                  <div>
                      <p class="font-bold text-amber-800 dark:text-amber-200 mb-0.5">Awaiting Material Approval (BOM)</p>
                      <p class="text-amber-700 dark:text-amber-300 font-medium text-xs leading-relaxed">Your Bill of Materials request is pending admin review and pricing. You can proceed to update task progress once the BOM is approved.</p>
                  </div>
              </div>
              @elseif($project->current_status === 'On Hold' && $hasApprovedBom)
              {{-- BOM approved — show info that worker can now proceed --}}
              <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border-2 border-emerald-300 dark:border-emerald-700 rounded-2xl mb-5 text-slate-800 dark:text-emerald-100 text-xs sm:text-sm font-semibold flex items-start gap-3 shadow-xs">
                  <div class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                  </div>
                  <div>
                      <p class="font-bold text-emerald-800 dark:text-emerald-200 mb-0.5">Materials Approved — Ready to Begin</p>
                      <p class="text-emerald-700 dark:text-emerald-300 font-medium text-xs leading-relaxed">Your BOM has been approved by admin. Take a before-work photo and set the task to In Progress to proceed.</p>
                  </div>
              </div>
              @endif

              @if(!($project->current_status === 'On Hold' && $hasPendingBom))
              {{-- Only show update form if not blocked by pending BOM --}}

              <!-- Offline Success Notice (Step 1) -->
              <div x-show="offlineSaved && !taskFinishedOffline" x-cloak class="p-4 bg-blue-50/80 dark:bg-blue-950/40 border-2 border-blue-200 dark:border-blue-800 rounded-2xl mb-5 text-slate-800 dark:text-blue-100 text-xs sm:text-sm font-semibold flex items-start sm:items-center gap-3 shadow-xs">
                  <div class="w-7 h-7 rounded-lg bg-[#0038A8] text-white flex items-center justify-center shrink-0 mt-0.5 sm:mt-0">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                  </div>
                  <span class="leading-relaxed" x-text="offlineMsg"></span>
              </div>

              <!-- Finished Offline State Card (Step 2 Completed) -->
              <div x-show="taskFinishedOffline" x-cloak class="p-7 bg-slate-50 dark:bg-zinc-900/80 border-2 border-emerald-500/40 dark:border-emerald-500/30 rounded-2xl text-center space-y-4 shadow-sm">
                  <div class="w-14 h-14 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center mx-auto shadow-xs">
                      <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                  </div>
                  
                  <div>
                      <h4 class="font-extrabold text-lg text-slate-900 dark:text-white">Task Completed (Saved Locally Offline)</h4>
                      <p class="text-xs text-slate-600 dark:text-gray-300 max-w-md mx-auto mt-1.5 leading-relaxed" x-text="offlineMsg"></p>
                  </div>

                  <div class="pt-2 flex justify-center">
                      <a href="{{ route('worker.job-orders.index') }}" 
                         class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-[#0038A8] hover:bg-[#002480] text-white font-bold text-xs rounded-xl shadow-md transition cursor-pointer">
                          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                          <span>Back to Job Orders</span>
                      </a>
                  </div>
              </div>
            
             <form x-show="!taskFinishedOffline" action="{{ route('worker.task-progress.update', $project->project_id) }}" method="POST" enctype="multipart/form-data" class="space-y-5" @submit="submitProgressForm($event)">
                 @csrf
                 @method('PUT')

                 <!-- Status Selector -->
                 <div>
                     <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">
                         Mark Current Status As <span class="text-red-500">*</span>:
                     </label>
                     <select name="status" x-model="currentStatusVal" class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-700 rounded-xl text-sm font-semibold bg-gray-50 dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f] transition" required>
                         <option value="In Progress">In Progress (Currently working — Requires Before-Work Photo)</option>
                         <option value="Completed">Completed (Ready for Verification — Requires After-Work Photo)</option>
                     </select>
                 </div>

                 <!-- Completed Options (Full Repair vs Inspection Only) -->
                 <div x-show="currentStatusVal === 'Completed'" x-cloak class="pt-4 border-t border-gray-100 dark:border-zinc-800 space-y-3">
                     <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                         Nature of Work Executed:
                     </label>
                     <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                         <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition"
                                :class="completionType === 'Full Repair' ? 'border-[#1a3c8f] bg-blue-50/70 dark:bg-blue-950/40' : 'border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900'">
                             <input type="radio" name="completion_type" value="Full Repair" x-model="completionType" class="mt-1 text-[#1a3c8f] focus:ring-[#1a3c8f]">
                             <div>
                                 <div class="text-xs font-bold text-slate-900 dark:text-white">Direct Repair / Maintenance Done</div>
                                 <div class="text-[11px] text-gray-500 mt-0.5">Physical repair, replacement, or maintenance executed.</div>
                             </div>
                         </label>

                         <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition"
                                :class="completionType === 'Inspection Only' ? 'border-[#1a3c8f] bg-blue-50/70 dark:bg-blue-950/40' : 'border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900'">
                             <input type="radio" name="completion_type" value="Inspection Only" x-model="completionType" class="mt-1 text-[#1a3c8f] focus:ring-[#1a3c8f]">
                             <div>
                                 <div class="text-xs font-bold text-slate-900 dark:text-white">Inspection &amp; Assessment Only</div>
                                 <div class="text-[11px] text-gray-500 mt-0.5">Site inspected and assessed without direct physical repairs.</div>
                             </div>
                         </label>
                     </div>

                     <div>
                         <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                             <span x-show="completionType === 'Inspection Only'">Inspection Findings &amp; Recommendation (Optional):</span>
                             <span x-show="completionType === 'Full Repair'">Work Notes / Summary (Optional):</span>
                         </label>
                         <textarea name="recommendation" 
                                   rows="2" 
                                   class="w-full px-3 py-2.5 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]"
                                   :placeholder="completionType === 'Inspection Only' ? 'e.g. Inspected circuit breaker; no major wiring damage found.' : 'e.g. Replaced leaking faucet gasket and tested water pressure.'"></textarea>
                     </div>
                 </div>

                 <!-- Proof Photo Upload & Camera Card -->
                 <div class="pt-4 border-t border-gray-100 dark:border-zinc-800">
                     <div class="flex items-center justify-between mb-2">
                         <label class="block text-xs font-bold uppercase tracking-wider text-[#1a3c8f] dark:text-blue-300">
                             <span x-show="currentStatusVal === 'In Progress'">Before-Work Photo <span class="text-red-500 font-black">*</span></span>
                             <span x-show="currentStatusVal === 'Completed'">Proof of Completion Photo <span class="text-red-500 font-black">*</span></span>
                         </label>
                         <span class="text-[11px] text-gray-400">JPG, PNG, WEBP, PDF</span>
                     </div>

                     <!-- Hidden File Input (Standard File Picker) -->
                     <input type="file" 
                            name="proof" 
                            x-ref="workerFileInput" 
                            accept="image/*,application/pdf" 
                            @change="handleFile($event.target.files[0])" 
                            class="hidden">

                     <!-- Hidden Native Device Camera Input -->
                     <input type="file" 
                            x-ref="mobileCameraInput" 
                            accept="image/*" 
                            capture="environment" 
                            @change="handleFile($event.target.files[0])" 
                            class="hidden">

                     <!-- Option Buttons (When no file chosen) -->
                     <div x-show="!proofFile" class="border-2 border-dashed border-blue-300 dark:border-blue-900/60 rounded-2xl p-6 bg-blue-50/30 dark:bg-blue-950/20 text-center">
                         <div class="flex flex-col sm:flex-row items-center justify-center gap-3 max-w-md mx-auto">
                             <!-- Choose Photo / File -->
                             <button type="button" 
                                     @click="$refs.workerFileInput.click()" 
                                     class="w-full sm:flex-1 px-4 py-3 bg-white dark:bg-zinc-800 border-2 border-gray-200 dark:border-zinc-700 hover:border-[#1a3c8f] dark:hover:border-blue-500 rounded-xl text-xs font-bold text-gray-800 dark:text-gray-100 hover:bg-gray-50 transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                                 <svg class="w-4 h-4 text-[#0038A8] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                 <span>Choose File</span>
                             </button>

                             <span class="text-xs text-gray-400 font-bold">or</span>

                             <!-- Take Photo with Camera -->
                             <button type="button" 
                                     @click="openCamera()" 
                                     class="w-full sm:flex-1 px-4 py-3 bg-[#0038A8] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                 <span>Snap Photo (Camera)</span>
                             </button>
                         </div>
                         <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-3">
                             <span x-show="currentStatusVal === 'In Progress'">Take or attach an initial photo before beginning work.</span>
                             <span x-show="currentStatusVal === 'Completed'">Take or attach a proof photo of the completed repair.</span>
                         </p>
                     </div>

                     <!-- Preview Card (When file is chosen) -->
                     <div x-show="proofFile" x-cloak class="border-2 border-emerald-300 dark:border-emerald-800 bg-emerald-50/40 dark:bg-emerald-950/20 rounded-2xl p-4 flex items-center justify-between gap-4 shadow-xs">
                         <div class="flex items-center gap-3.5 min-w-0">
                             <div class="w-16 h-20 rounded-xl border border-emerald-200 dark:border-emerald-800 shrink-0 shadow-xs overflow-hidden bg-emerald-100 dark:bg-emerald-950/50 flex items-center justify-center">
                                 <template x-if="proofPreviewUrl">
                                     <img :src="proofPreviewUrl" alt="" class="w-full h-full object-cover">
                                 </template>
                                 <template x-if="!proofPreviewUrl">
                                     <span class="font-bold text-xs text-emerald-800 dark:text-emerald-300">DOC</span>
                                 </template>
                             </div>
                             <div class="min-w-0">
                                 <div class="flex items-center gap-2">
                                     <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                     <span class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="proofFile"></span>
                                 </div>
                                 <p class="text-[11px] text-emerald-700 dark:text-emerald-300 font-semibold mt-1">Photo attached &amp; ready</p>
                                 <p class="text-[10px] text-gray-400 font-mono" x-text="proofSize"></p>
                             </div>
                         </div>
                         <button type="button" @click="clearProof()" class="px-3.5 py-2 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-900/60 rounded-xl transition border border-red-200 dark:border-red-800 shrink-0 cursor-pointer">
                             ✕ Remove
                         </button>
                     </div>
                 </div>

                 <!-- Submit Action Button -->
                 <div class="pt-3">
                     <button type="submit" 
                             :disabled="saving || taskFinishedOffline" 
                             :class="(saving || taskFinishedOffline) ? 'opacity-50 cursor-not-allowed pointer-events-none' : 'cursor-pointer'"
                             class="w-full sm:w-auto bg-[#1a3c8f] hover:bg-[#152e6e] text-white px-8 py-3.5 rounded-xl text-sm font-bold transition shadow-sm flex items-center justify-center gap-2">
                         <svg x-show="saving" x-cloak class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                         <span x-text="saving ? 'Saving...' : (currentStatusVal === 'In Progress' ? 'Save Before-Work & Set In Progress' : 'Save & Mark Task as Completed')"></span>
                     </button>
                 </div>
             </form>

              <!-- Modern Portrait Camera Modal (3:4 Ratio) -->
              <div x-show="cameraActive" x-cloak class="fixed inset-0 bg-black/85 z-50 flex items-center justify-center p-4">
                 <div class="bg-[#18181b] border border-zinc-700 rounded-3xl p-5 max-w-sm w-full shadow-2xl relative flex flex-col items-center">
                     <div class="flex items-center justify-between w-full mb-3 text-white">
                         <h3 class="text-xs font-bold uppercase tracking-wider flex items-center gap-2" x-text="currentStatusVal === 'In Progress' ? 'Before-Work Photo' : 'Proof of Completion'"></h3>
                         <button type="button" @click="flipCamera()" class="p-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-gray-300 hover:text-white transition text-xs font-bold flex items-center gap-1.5 cursor-pointer" title="Flip Camera">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                             <span>Flip</span>
                         </button>
                     </div>

                     <!-- In-Modal Camera Error Notification -->
                     <div x-show="cameraError" x-cloak class="w-full mb-4 p-4 bg-red-950/40 border border-red-800/80 rounded-2xl text-center text-xs text-red-200 space-y-2">
                         <p class="font-bold flex items-center justify-center gap-1.5">
                             <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                             <span>Camera In Use or Blocked</span>
                         </p>
                         <p class="text-[11px] text-gray-300 leading-relaxed" x-text="cameraError"></p>
                         <div class="pt-2 flex flex-col gap-2">
                             <button type="button" @click="startStream()" class="w-full px-3 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-xl text-xs font-bold transition">
                                 🔄 Retry Camera
                             </button>
                             <button type="button" @click="closeCamera(); $refs.workerFileInput.click()" class="w-full px-3 py-2 bg-[#0038A8] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition">
                                 📁 Choose Photo from Files Instead
                             </button>
                         </div>
                     </div>

                     <!-- Portrait Viewfinder (3:4 Ratio) -->
                     <div x-show="!cameraError" class="w-full bg-black rounded-2xl overflow-hidden mb-4 relative aspect-[3/4] flex items-center justify-center border-2 border-zinc-700">
                         <video x-ref="workerVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>

                         <!-- Viewfinder Reticle Overlay -->
                         <div class="absolute inset-4 border-2 border-white/30 rounded-xl pointer-events-none flex flex-col justify-between p-2">
                             <div class="flex justify-between">
                                 <span class="w-4 h-4 border-t-2 border-l-2 border-white"></span>
                                 <span class="w-4 h-4 border-t-2 border-r-2 border-white"></span>
                             </div>
                             <div class="text-center">
                                 <span class="text-[10px] font-bold text-white/75 bg-black/40 px-2 py-0.5 rounded-full">Portrait Viewfinder</span>
                             </div>
                             <div class="flex justify-between">
                                 <span class="w-4 h-4 border-b-2 border-l-2 border-white"></span>
                                 <span class="w-4 h-4 border-b-2 border-r-2 border-white"></span>
                             </div>
                         </div>
                     </div>

                     <!-- Shutter Action Bar -->
                     <div class="flex items-center justify-between w-full px-4">
                         <button type="button" @click="closeCamera()" class="px-4 py-2.5 bg-zinc-800 hover:bg-zinc-700 text-gray-300 rounded-xl text-xs font-bold transition cursor-pointer">
                             Cancel
                         </button>

                         <!-- Big Circular Shutter Button -->
                         <button type="button" 
                                 x-show="!cameraError"
                                 @click="capturePhoto()" 
                                 class="w-14 h-14 rounded-full bg-white hover:bg-gray-100 p-1.5 transition flex items-center justify-center shadow-lg cursor-pointer" 
                                 title="Take Photo">
                             <div class="w-full h-full rounded-full border-2 border-zinc-900 bg-red-600 hover:bg-red-700 flex items-center justify-center transition">
                                 <div class="w-3.5 h-3.5 rounded-full bg-white"></div>
                             </div>
                         </button>

                         <div class="w-12"></div>
                     </div>
                 </div>
             </div>
             @endif {{-- end pending BOM check --}}
         </div>
         @endif

        <script>
            function workerTaskProgress(projectId, defaultNextStatus, projectTitle, syncUrl) {
                return {
                    currentStatusVal: defaultNextStatus || 'In Progress',
                    completionType: 'Full Repair',
                    saving: false,
                    proofFile: '',
                    proofSize: '',
                    proofPreviewUrl: '',
                    capturedFile: null,
                    cameraActive: false,
                    cameraStream: null,
                    cameraError: '',
                    facingMode: 'environment',
                    offlineSaved: false,
                    offlineMsg: '',
                    taskFinishedOffline: false,

                    handleFile(file) {
                        if (!file) return;
                        this.proofFile = file.name;
                        this.proofSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                        this.capturedFile = file;
                        if (file.type && file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.proofPreviewUrl = e.target.result;
                            };
                            reader.readAsDataURL(file);
                        } else {
                            this.proofPreviewUrl = '';
                        }
                    },

                    async openCamera() {
                        this.cameraActive = true;
                        this.cameraError = '';
                        await this.$nextTick();
                        await this.startStream();
                    },

                    async startStream() {
                        this.cameraError = '';
                        
                        if (this.cameraStream) {
                            try {
                                this.cameraStream.getTracks().forEach(t => {
                                    t.stop();
                                });
                            } catch (e) {}
                            this.cameraStream = null;
                        }

                        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                            this.cameraError = 'Live camera is not supported in this browser. Please use "Choose File" to select a photo.';
                            return;
                        }

                        let stream = null;
                        const targetFacing = this.facingMode || 'environment';

                        // Attempt 1: Facing mode with ideal resolution
                        try {
                            stream = await navigator.mediaDevices.getUserMedia({
                                video: {
                                    facingMode: { ideal: targetFacing },
                                    width: { ideal: 1280 },
                                    height: { ideal: 720 }
                                },
                                audio: false
                            });
                        } catch (err1) {
                            console.warn('High-res facingMode attempt failed, trying basic facingMode...', err1);
                            try {
                                // Attempt 2: Direct facingMode
                                stream = await navigator.mediaDevices.getUserMedia({
                                    video: {
                                        facingMode: targetFacing
                                    },
                                    audio: false
                                });
                            } catch (err2) {
                                console.warn('Direct facingMode attempt failed, trying generic video...', err2);
                                try {
                                    // Attempt 3: Generic video
                                    stream = await navigator.mediaDevices.getUserMedia({
                                        video: true,
                                        audio: false
                                    });
                                } catch (err3) {
                                    console.error('All camera attempts failed:', err3);
                                    this.cameraError = 'Could not access camera (' + (err3.message || 'Permission denied') + '). Please ensure camera access is allowed in browser settings, or choose a file.';
                                    return;
                                }
                            }
                        }

                        if (stream) {
                            this.cameraStream = stream;
                            await this.$nextTick();
                            const video = this.$refs.workerVideo;
                            if (video) {
                                video.srcObject = stream;
                                video.muted = true;
                                video.setAttribute('playsinline', 'true');
                                video.setAttribute('autoplay', 'true');
                                video.onloadedmetadata = async () => {
                                    try {
                                        await video.play();
                                    } catch (err) {
                                        console.warn('Autoplay prevented:', err);
                                    }
                                };
                                try {
                                    await video.play();
                                } catch (e) {}
                            }
                        }
                    },

                    async flipCamera() {
                        this.facingMode = (this.facingMode === 'environment') ? 'user' : 'environment';
                        await this.startStream();
                    },

                    capturePhoto() {
                        const video = this.$refs.workerVideo;
                        if (!video || !this.cameraStream) return;

                        const vw = video.videoWidth || 640;
                        const vh = video.videoHeight || 480;

                        // Target 3:4 portrait crop
                        const targetWidth = 720;
                        const targetHeight = 960;

                        let srcW = vw;
                        let srcH = Math.round(vw * (4 / 3));
                        let srcX = 0;
                        let srcY = 0;

                        if (srcH > vh) {
                            srcH = vh;
                            srcW = Math.round(vh * (3 / 4));
                            srcX = Math.round((vw - srcW) / 2);
                            srcY = 0;
                        } else {
                            srcY = Math.round((vh - srcH) / 2);
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = targetWidth;
                        canvas.height = targetHeight;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(video, srcX, srcY, srcW, srcH, 0, 0, targetWidth, targetHeight);

                        canvas.toBlob((blob) => {
                            if (!blob) return;
                            const filename = 'proof_' + (this.currentStatusVal === 'In Progress' ? 'before_' : 'after_') + Date.now() + '.jpg';
                            const file = new File([blob], filename, { type: 'image/jpeg' });
                            
                            this.handleFile(file);

                            try {
                                const dt = new DataTransfer();
                                dt.items.add(file);
                                if (this.$refs.workerFileInput) {
                                    this.$refs.workerFileInput.files = dt.files;
                                }
                            } catch (e) {}

                            this.closeCamera();
                        }, 'image/jpeg', 0.90);
                    },

                    closeCamera() {
                        if (this.cameraStream) {
                            try {
                                this.cameraStream.getTracks().forEach(track => track.stop());
                            } catch (e) {}
                            this.cameraStream = null;
                        }
                        this.cameraActive = false;
                        this.cameraError = '';
                    },

                    clearProof() {
                        this.proofFile = '';
                        this.proofSize = '';
                        this.proofPreviewUrl = '';
                        this.capturedFile = null;
                        if (this.$refs.workerFileInput) {
                            this.$refs.workerFileInput.value = '';
                        }
                        if (this.$refs.mobileCameraInput) {
                            this.$refs.mobileCameraInput.value = '';
                        }
                    },

                    async submitProgressForm(e) {
                        // 1. ALWAYS prevent native browser POST navigation to avoid Chrome Dino offline screen
                        if (e) {
                            e.preventDefault();
                            e.stopPropagation();
                        }

                        // Prevent duplicate clicks if already saving or finished
                        if (this.saving || this.taskFinishedOffline) return;

                        const fileInput = this.$refs.workerFileInput;
                        const file = (fileInput && fileInput.files && fileInput.files[0]) || this.capturedFile;

                        if (!file) {
                            alert(this.currentStatusVal === 'In Progress' ? 'A Before-Work photo is required before setting task to In Progress.' : 'An After-Work / proof of completion photo is required.');
                            return;
                        }

                        this.saving = true;
                        const formEl = e.target;
                        const recVal = formEl.querySelector('textarea[name="recommendation"]')?.value || '';
                        const submittedStatus = this.currentStatusVal;

                        // 2. If currently offline, queue in IndexedDB outbox
                        if (!navigator.onLine) {
                            try {
                                const payload = {
                                    projectId: projectId,
                                    projectTitle: projectTitle,
                                    syncUrl: syncUrl,
                                    status: submittedStatus,
                                    completionType: submittedStatus === 'Completed' ? this.completionType : null,
                                    natureOfWork: submittedStatus === 'Completed' ? (this.completionType === 'Inspection Only' ? 'Inspection & Assessment Only' : 'Direct Repair') : null,
                                    recommendation: recVal,
                                    photoBlob: file,
                                    photoName: file.name,
                                    offlinePerformedAt: new Date().toISOString(),
                                    createdAt: new Date().toISOString()
                                };

                                if (window.LINKodOffline) {
                                    await window.LINKodOffline.addToOutbox(payload);
                                    if (window.LINKodOffline.showSyncToast) {
                                        window.LINKodOffline.showSyncToast(`Saved "${submittedStatus}" offline for #${projectId}!`, 'success');
                                    }
                                    if (window.LINKodOffline.updateUIState) {
                                        window.LINKodOffline.updateUIState();
                                    }
                                }

                                this.clearProof();

                                if (submittedStatus === 'In Progress') {
                                    // Progress to step 2: Completed
                                    this.currentStatusVal = 'Completed';
                                    this.offlineSaved = true;
                                    this.offlineMsg = '✓ Step 1 Saved: Marked as In Progress offline! You can now proceed with repairs. When finished, attach your After-Work photo below to mark Completed.';
                                } else {
                                    // Completed state
                                    this.taskFinishedOffline = true;
                                    this.offlineSaved = true;
                                    this.offlineMsg = '✓ All steps completed and saved to device! Both Before and After photo proofs are safely stored and will automatically sync with the server once connected.';
                                }
                            } catch (err) {
                                console.error('Error saving offline:', err);
                                alert('Failed to save offline: ' + err.message);
                            } finally {
                                this.saving = false;
                            }
                            return;
                        }

                        // 3. If online, submit via AJAX FormData
                        try {
                            const formData = new FormData(formEl);
                            if (file && (!fileInput || !fileInput.files || !fileInput.files.length)) {
                                formData.set('proof', file, file.name);
                            }

                            const response = await fetch(formEl.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            if (response.ok) {
                                window.location.reload();
                            } else {
                                throw new Error('Server returned ' + response.status);
                            }
                        } catch (netErr) {
                            console.warn('Online request failed, saving offline fallback:', netErr);
                            try {
                                const payload = {
                                    projectId: projectId,
                                    projectTitle: projectTitle,
                                    syncUrl: syncUrl,
                                    status: submittedStatus,
                                    completionType: submittedStatus === 'Completed' ? this.completionType : null,
                                    natureOfWork: submittedStatus === 'Completed' ? (this.completionType === 'Inspection Only' ? 'Inspection & Assessment Only' : 'Direct Repair') : null,
                                    recommendation: recVal,
                                    photoBlob: file,
                                    photoName: file.name,
                                    offlinePerformedAt: new Date().toISOString(),
                                    createdAt: new Date().toISOString()
                                };

                                if (window.LINKodOffline) {
                                    await window.LINKodOffline.addToOutbox(payload);
                                    if (window.LINKodOffline.showSyncToast) {
                                        window.LINKodOffline.showSyncToast(`Saved "${submittedStatus}" offline for #${projectId}!`, 'success');
                                    }
                                    if (window.LINKodOffline.updateUIState) {
                                        window.LINKodOffline.updateUIState();
                                    }
                                }

                                this.clearProof();

                                if (submittedStatus === 'In Progress') {
                                    this.currentStatusVal = 'Completed';
                                    this.offlineSaved = true;
                                    this.offlineMsg = '✓ Connection dropped. Step 1 marked as In Progress offline! You can now attach your After-Work photo below to mark Completed.';
                                } else {
                                    this.taskFinishedOffline = true;
                                    this.offlineSaved = true;
                                    this.offlineMsg = '✓ Connection dropped. All steps completed and saved to device! Will auto-sync when online.';
                                }
                            } catch (fallbackErr) {
                                alert('Submission error: ' + netErr.message);
                            } finally {
                                this.saving = false;
                            }
                        }
                    }
                };
            }
        </script>

        <!-- 3rd Box: Material Requisition (Optional) -->
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl shadow-xs p-7">

            <h3 class="text-gray-900 dark:text-white font-bold text-lg mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Request Materials / Bill of Materials (BOM)
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-5">Specify needed tools, parts, or supplies. Pricing is verified and approved by Admin.</p>
            
            @if($project->billOfMaterials->count() > 0)
                <div class="mb-6">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Previously Requested Materials</h4>
                    <div class="space-y-2.5">
                        @foreach($project->billOfMaterials as $bom)
                            @php
                                $unit = $bom->material->unit_of_measurement ?? 'pcs';
                                $isApproved = !is_null($bom->date_approved);
                            @endphp
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gray-50 dark:bg-zinc-800/60 p-3.5 rounded-xl border border-gray-200/80 dark:border-zinc-700">
                                <div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <span>{{ $bom->material->material_name ?? 'Unknown Material' }}</span>
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-black bg-blue-100 text-[#0033a0] dark:bg-blue-950/60 dark:text-blue-300">
                                            {{ rtrim(rtrim(number_format($bom->qty, 2), '0'), '.') }} {{ $unit }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        Requested on {{ \Carbon\Carbon::parse($bom->created_at ?? $project->date_assigned)->format('M d, Y') }}
                                        @if($isApproved && $bom->total_cost > 0)
                                            • <span class="text-slate-700 dark:text-gray-300 font-semibold">Total: ₱{{ number_format($bom->total_cost, 2) }}</span> (₱{{ number_format($bom->material->unit_cost ?? 0, 2) }}/{{ $unit }})
                                        @endif
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    @if($isApproved)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 text-xs font-extrabold rounded-md uppercase">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Approved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 text-xs font-extrabold rounded-md uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pending Admin Pricing & Approval
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($project->current_status !== 'Completed' && $project->current_status !== 'Pending Verification')
                <div x-data="{
                    submittingBOM: false,
                    rows: [
                        { material_id: '', custom_name: '', unit: 'pcs', qty: 1 }
                    ],
                    catalog: {{ Js::from($materials->map(fn($m) => ['id' => $m->material_id, 'name' => $m->material_name, 'unit' => $m->unit_of_measurement ?? 'pcs'])) }},
                    isDiscrete(unit) {
                        if (!unit) return true;
                        const u = unit.toString().trim().toLowerCase();
                        const continuousUnits = ['meter', 'meters', 'm', 'length', 'lengths', 'ft', 'feet', 'foot', 'liter', 'liters', 'l', 'kg', 'kilo', 'kilos', 'kilogram', 'kilograms', 'gallon', 'gallons', 'gal', 'yard', 'yards', 'yd', 'inch', 'inches', 'cm', 'mm'];
                        return !continuousUnits.includes(u);
                    },
                    addRow() {
                        this.rows.push({ material_id: '', custom_name: '', unit: 'pcs', qty: 1 });
                    },
                    removeRow(index) {
                        if (this.rows.length > 1) {
                            this.rows.splice(index, 1);
                        }
                    },
                    onMaterialChange(row) {
                        if (row.material_id && row.material_id !== 'custom') {
                            const found = this.catalog.find(m => m.id == row.material_id);
                            if (found && found.unit) {
                                row.unit = found.unit;
                            }
                        } else if (row.material_id === 'custom') {
                            if (!row.unit) row.unit = 'pcs';
                        }
                    }
                }" class="pt-2">
                    
                    <form action="{{ route('worker.bom.store', $project->project_id) }}" method="POST" @submit="submittingBOM = true">
                        @csrf
                        
                        <div class="space-y-3 mb-4">
                            <template x-for="(row, idx) in rows" :key="idx">
                                <div class="p-3.5 bg-gray-50 dark:bg-zinc-800/60 rounded-xl border border-gray-200 dark:border-zinc-700 transition">
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                                        
                                        <!-- Material Selection -->
                                        <div :class="row.material_id === 'custom' ? 'sm:col-span-4' : 'sm:col-span-6'">
                                            <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1">
                                                Select Material
                                            </label>
                                            <select :name="'items[' + idx + '][material_id]'" 
                                                    x-model="row.material_id" 
                                                    @change="onMaterialChange(row)" 
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" 
                                                    required>
                                                <option value="">Select from catalog...</option>
                                                @foreach($materials ?? [] as $m)
                                                    <option value="{{ $m->material_id }}">{{ $m->material_name }} ({{ $m->unit_of_measurement ?? 'pcs' }})</option>
                                                @endforeach
                                                <option value="custom" class="font-bold text-[#0033a0]">+ Add New / Custom Material...</option>
                                            </select>
                                        </div>

                                        <!-- Custom Name Input if 'custom' selected -->
                                        <div class="sm:col-span-3" x-show="row.material_id === 'custom'">
                                            <label class="block text-[11px] font-bold text-[#0033a0] dark:text-blue-400 uppercase tracking-wider mb-1">
                                                New Material Name
                                            </label>
                                            <input type="text" 
                                                   :name="'items[' + idx + '][custom_material_name]'" 
                                                   x-model="row.custom_name" 
                                                   :required="row.material_id === 'custom'" 
                                                   placeholder="e.g. 10m Extension Wire" 
                                                   class="w-full px-3 py-2 border border-[#0033a0] dark:border-blue-500 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]">
                                        </div>

                                        <!-- Quantity -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1">
                                                Quantity
                                            </label>
                                            <input type="number" 
                                                   :name="'items[' + idx + '][qty]'" 
                                                   x-model.number="row.qty" 
                                                   :min="isDiscrete(row.unit) ? '1' : '0.01'" 
                                                   :step="isDiscrete(row.unit) ? '1' : '0.01'" 
                                                   placeholder="1" 
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" 
                                                   required>
                                        </div>

                                        <!-- Unit of Measurement (Non-editable for catalog items, select dropdown for custom items) -->
                                        <div :class="row.material_id === 'custom' ? 'sm:col-span-2' : 'sm:col-span-3'">
                                            <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1">
                                                Unit / Measurement
                                            </label>
                                            
                                            <!-- Non-editable display for catalog items -->
                                            <div x-show="row.material_id !== 'custom'" class="w-full px-3 py-2 border border-gray-200 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800/80 rounded-lg text-xs font-bold text-slate-700 dark:text-gray-300 text-center flex items-center justify-center min-h-[34px] select-none">
                                                <span x-text="row.unit || 'pcs'"></span>
                                            </div>
                                            <input type="hidden" x-show="row.material_id !== 'custom'" :name="'items[' + idx + '][unit_of_measurement]'" :value="row.unit">

                                            <!-- Predefined dropdown for custom material items -->
                                            <select x-show="row.material_id === 'custom'" 
                                                    :name="'items[' + idx + '][unit_of_measurement]'" 
                                                    x-model="row.unit" 
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]">
                                                <option value="pcs">pcs (pieces)</option>
                                                <option value="meters">meters</option>
                                                <option value="lengths">lengths</option>
                                                <option value="rolls">rolls</option>
                                                <option value="boxes">boxes</option>
                                                <option value="bags">bags</option>
                                                <option value="liters">liters</option>
                                                <option value="sheets">sheets</option>
                                                <option value="sets">sets</option>
                                                <option value="units">units</option>
                                                <option value="kg">kg (kilograms)</option>
                                                <option value="gallons">gallons</option>
                                                <option value="pairs">pairs</option>
                                                <option value="tubes">tubes</option>
                                                <option value="packs">packs</option>
                                                <option value="feet">feet</option>
                                                <option value="can">can / cans</option>
                                            </select>
                                        </div>

                                        <!-- Remove Row Button -->
                                        <div class="sm:col-span-1 flex justify-end">
                                            <button type="button" 
                                                    @click="removeRow(idx)" 
                                                    x-show="rows.length > 1" 
                                                    class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition" 
                                                    title="Remove item">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                            <button type="button" 
                                    @click="addRow()" 
                                    class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold hover:underline inline-flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Another Material Item</span>
                            </button>

                            <button type="submit" 
                                    :disabled="submittingBOM" 
                                    class="w-full sm:w-auto bg-[#0033a0] hover:bg-[#002480] text-white px-6 py-2.5 rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center justify-center gap-2 disabled:opacity-60 cursor-pointer">
                                <svg x-show="submittingBOM" x-cloak class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-text="submittingBOM ? 'Submitting...' : 'Submit Material Request'">Submit Material Request</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

    </div>

    <!-- The 2 Boxes on the Right Side (Job Details & Client Contact) -->
    <div class="w-full md:w-80 lg:w-96 shrink-0 space-y-6">
        <!-- Job Details Card -->
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl shadow-xs p-6">
            <h3 class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-4 border-b border-gray-100 dark:border-zinc-800 pb-2">JOB DETAILS</h3>
            <div class="space-y-4">
                <div>
                    <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">SERVICE CATEGORY</div>
                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $req?->category?->category_name ?? 'General Maintenance' }}</div>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">PRIORITY LEVEL</div>
                    <div>
                        @php
                            $prio2 = ucfirst(strtolower($req->priority ?? 'Low'));
                            $prioBadge2 = match(strtolower($req->priority ?? 'low')) {
                                'high' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-300',
                                'medium' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300',
                                default => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300'
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $prioBadge2 }}">{{ $prio2 }} Priority</span>
                    </div>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">CAMPUS</div>
                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $req->campus ?? 'BU Main' }}</div>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">OFFICE / LOCATION</div>
                    <div class="text-sm font-bold text-gray-900 dark:text-white leading-relaxed">{{ $req->location ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Client Contact Card -->
        <div class="bg-[#fefce8] dark:bg-yellow-950/20 border border-[#d4d0a8] dark:border-yellow-900/50 rounded-xl shadow-xs p-6">
            <h3 class="text-xs font-bold text-[#1a3c8f] dark:text-yellow-400 uppercase tracking-wider mb-4 border-b border-[#d4d0a8]/60 dark:border-yellow-900/40 pb-2">CLIENT CONTACT</h3>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-[#0033a0] text-white rounded-full flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                    {{ $initials }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-bold text-[#1a3c8f] dark:text-yellow-200 truncate">{{ $clientName }}</div>
                    <div class="text-xs text-[#1a3c8f]/80 dark:text-yellow-400/80 truncate">{{ $clientEmail }}</div>
                </div>
            </div>
            <div class="pt-3 border-t border-[#d4d0a8]/60 dark:border-yellow-900/40">
                <div class="text-[11px] font-bold text-[#1a3c8f] dark:text-yellow-400 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                    <span>📞</span>
                    <span>PHONE / CONTACT NUMBER</span>
                </div>
                <div class="bg-white dark:bg-zinc-900 p-3 rounded-lg border border-[#d4d0a8] dark:border-zinc-700 flex items-center justify-between gap-2 shadow-2xs">
                    <div class="font-extrabold text-sm text-[#0033a0] dark:text-blue-400">{{ $clientPhone }}</div>
                    @if($clientPhone !== 'Not Provided')
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $clientPhone) }}" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-xs inline-flex items-center gap-1 shrink-0">Call</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Full Width Discussion & Messages Channel -->
@if($req)
    <div class="w-full mt-6">
        @include('partials.request-messages', ['serviceRequest' => $req])
    </div>
@endif

@endsection
