@extends('layouts.admin')

@section('page-title', 'Review & Action Request')

@section('content')
@php
    $isManpower = $serviceRequest->is_manpower;
@endphp

<div class="w-full max-w-6xl mx-auto space-y-6 font-sans">
    
    <!-- Top Header Banner -->
    <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-8 py-6 shadow-sm space-y-4"
         x-data="{
             dateStarted: '{{ now()->toDateString() }}',
             targetCompletion: '{{ now()->toDateString() }}',
             get printUrl() {
                 let base = '{{ route('admin.requests.export', $serviceRequest->request_id) }}';
                 let params = new URLSearchParams();
                 if (this.dateStarted) params.append('date_started', this.dateStarted);
                 if (this.targetCompletion) params.append('target_completion', this.targetCompletion);
                 let qs = params.toString();
                 return qs ? (base + '?' + qs) : base;
             }
         }">
        
        <!-- Row 1: Badges on Left, Clientele Satisfaction Button on Right (Same Line) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="px-3 py-1 bg-[#0033a0] text-white text-[11px] font-extrabold uppercase tracking-wider rounded-full shadow-sm">
                    Requisition #{{ str_pad($serviceRequest->request_id, 4, '0', STR_PAD_LEFT) }}
                </span>
                <span class="px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider rounded-full border 
                    {{ $serviceRequest->is_urgent ? 'bg-red-100 text-red-700 border-red-300' : 'bg-blue-100 text-blue-700 border-blue-300' }}">
                    {{ $serviceRequest->priority_label }}
                </span>
                <span class="px-3 py-1 text-[11px] font-bold rounded-full 
                    @if($serviceRequest->current_status === 'Awaiting Verification of Bill of Materials')
                        bg-amber-100 text-amber-900 border border-amber-300
                    @elseif($serviceRequest->current_status === 'BOM Verified (Awaiting Client Approval)')
                        bg-indigo-100 text-indigo-900 border border-indigo-300
                    @elseif($serviceRequest->current_status === 'Completed')
                        bg-emerald-100 text-emerald-800
                    @else
                        bg-blue-100 text-blue-800
                    @endif">
                    {{ $serviceRequest->current_status }}
                </span>
                @if($serviceRequest->project?->nature_of_work)
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 border border-amber-300 text-[11px] font-bold rounded-full">
                        {{ $serviceRequest->project->nature_of_work }}
                    </span>
                @endif
                @if($serviceRequest->scheduled_date)
                    <span class="px-3 py-1 bg-blue-100 text-[#0033a0] dark:bg-blue-950/70 dark:text-blue-300 border border-blue-300 dark:border-blue-800 text-[11px] font-bold rounded-full flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ $serviceRequest->scheduled_date->format('M d, Y') }} ({{ $serviceRequest->scheduled_time_window }})</span>
                    </span>
                @endif
            </div>

            <!-- Clientele Satisfaction Button (Same line as badges) -->
            <div class="shrink-0">
                @if($serviceRequest->evaluation)
                    <a href="{{ route('admin.requests.satisfaction', $serviceRequest->request_id) }}" target="_blank" class="px-4 py-1.5 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-full transition shadow-sm inline-flex items-center">
                        Print Satisfaction Page
                    </a>
                @else
                    <button type="button" disabled class="px-4 py-1.5 bg-gray-200 dark:bg-zinc-800 text-gray-400 dark:text-gray-500 text-xs font-bold rounded-full cursor-not-allowed inline-flex items-center opacity-80" title="Client has not rated this service request yet">
                        Print Satisfaction Page (Not Rated Yet)
                    </button>
                @endif
            </div>
        </div>

        <!-- Row 2: Title & Details on Left, Print Requisition & Date Range Controls on Right -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-1 border-t border-[#e5e1b0] dark:border-zinc-800">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    {{ $serviceRequest->title }}
                </h1>

                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">
                    Submitted by <span class="font-bold text-slate-800 dark:text-gray-200">{{ $serviceRequest->client->user->first_name ?? 'N/A' }} {{ $serviceRequest->client->user->last_name ?? '' }}</span> 
                    ({{ $serviceRequest->client->user->email_account ?? '' }})
                    • {{ \Carbon\Carbon::parse($serviceRequest->submitted_at)->format('M d, Y h:i A') }}
                </p>
            </div>

            <!-- Print Requisition Action & Date Range Controls -->
            <div class="flex flex-col gap-2 shrink-0 w-full sm:w-auto {{ $isManpower ? 'min-w-[180px]' : 'min-w-[300px]' }}">
                <!-- Print Requisition Button -->
                @if($serviceRequest->isScheduleApproved())
                    <a :href="printUrl" target="_blank" class="px-5 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-xl transition shadow-md inline-flex items-center justify-center w-full">
                        Print Requisition
                    </a>
                @else
                    <button type="button" disabled class="px-5 py-2.5 bg-gray-200 dark:bg-zinc-800 text-gray-400 dark:text-gray-500 text-xs font-bold rounded-xl cursor-not-allowed inline-flex items-center justify-center w-full opacity-80" title="Client must approve the scheduled date before printing requisition">
                        Print Requisition
                    </button>
                @endif

                @if(!$isManpower)
                    <!-- 2 Boxes: Left = Date Started, Right = Target Date of Completion -->
                    <div class="grid grid-cols-2 gap-2 bg-white dark:bg-zinc-800 p-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 shadow-2xs">
                        <div>
                            <label class="block text-[10px] font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                Date Started
                            </label>
                            <input type="date" 
                                   x-model="dateStarted" 
                                   class="w-full px-2 py-1 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-semibold text-gray-800 dark:text-white focus:outline-none focus:border-[#0033a0]">
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                Target Completion
                            </label>
                            <input type="date" 
                                   x-model="targetCompletion" 
                                   :min="dateStarted" 
                                   class="w-full px-2 py-1 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-semibold text-gray-800 dark:text-white focus:outline-none focus:border-[#0033a0]">
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>



    <!-- Request Details Card -->
    <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm"
         x-data="{ lightboxOpen: false, lightboxImg: '', lightboxTitle: '' }">
        <h2 class="text-base font-bold text-[#0033a0] dark:text-blue-400 mb-4">
            Request Details &amp; Specification
        </h2>

        @php
            $m = $serviceRequest->manpower_details;
        @endphp

        @if($isManpower)
            <!-- Top Details Grid for Manpower: Category, Campus, Location -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Service Category</div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $serviceRequest->category->category_name ?? 'Manpower' }}</div>
                </div>

                <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Campus</div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $serviceRequest->campus ?? 'BU Main' }}</div>
                </div>

                <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Office / Location</div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $serviceRequest->location }}</div>
                </div>
            </div>

            <!-- Manpower Request Breakdown (Separate Themed Boxes) -->
            <div class="space-y-3.5 mb-2">
                <!-- 1. Activity / Event Overview Box -->
                <div class="bg-blue-50/60 dark:bg-zinc-800/60 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                    <div class="flex items-center justify-between gap-2 mb-1.5 flex-wrap">
                        <div class="text-[11px] font-bold text-[#0033a0] dark:text-blue-300 uppercase tracking-wider">
                            Activity / Event
                        </div>
                        @if(!empty($m['event_date']))
                            <span class="px-2.5 py-0.5 bg-blue-100 dark:bg-blue-950 text-[#0033a0] dark:text-blue-300 rounded-md text-[11px] font-bold">
                                Event Date: {{ $m['event_date'] }}
                            </span>
                        @endif
                    </div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white">
                        {{ $m['activity_title'] ?: $serviceRequest->title }}
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
                                        <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-300 border border-blue-100 dark:border-blue-900 rounded text-[10.5px] font-semibold">
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
                                        <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-300 border border-blue-100 dark:border-blue-900 rounded text-[10.5px] font-semibold">
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
                                        <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-300 border border-blue-100 dark:border-blue-900 rounded text-[10.5px] font-semibold">
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
            <!-- Standard Description Box (Non-Manpower) -->
            <div class="mb-6">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                    {{ $serviceRequest->is_janitorial ? 'Description / Specific Work Requirements' : 'Description / Issue Summary' }}
                </div>
                <div class="bg-slate-50 dark:bg-zinc-800/60 p-4 rounded-xl text-slate-800 dark:text-gray-200 text-sm leading-relaxed border border-gray-100 dark:border-zinc-700 whitespace-pre-line">
                    {{ $serviceRequest->display_description ?: 'No additional description provided.' }}
                </div>
            </div>

            <!-- Details Grid (Non-Manpower) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Service Category</div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $serviceRequest->category->category_name ?? 'Unclassified' }}</div>
                </div>

                <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Campus</div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $serviceRequest->campus ?? 'BU Main' }}</div>
                </div>

                <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Office / Location</div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $serviceRequest->location }}</div>
                </div>
            </div>
                <!-- Supporting Attachment (if any) -->
        @if($serviceRequest->attachment)
            @php
                $isImg = Str::endsWith(strtolower($serviceRequest->attachment), ['.jpg', '.jpeg', '.png', '.webp']);
                $attachUrl = Storage::url($serviceRequest->attachment);
            @endphp
            <div class="mt-6 border-t border-gray-100 dark:border-zinc-800 pt-5" x-data="{ attModal: false }">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Attachment / Photo Evidence</div>
                <div @click="attModal = true" 
                     class="inline-flex items-center gap-3 p-3 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl hover:border-[#0033a0] transition group cursor-pointer">
                    @if($isImg)
                        <img src="{{ $attachUrl }}" alt="Attachment" class="w-14 h-14 object-cover rounded-lg border border-gray-200 dark:border-zinc-700">
                    @else
                        <div class="w-14 h-14 bg-blue-100 dark:bg-blue-950 text-[#0033a0] dark:text-blue-300 rounded-lg flex items-center justify-center font-black text-xs">PDF</div>
                    @endif
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-[#0033a0] dark:group-hover:text-blue-400 transition flex items-center gap-1.5">
                            <span>View Attachment</span>
                            <svg class="w-3.5 h-3.5 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </div>
                        <div class="text-[11px] text-gray-400">Click to preview in popup modal</div>
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
        @endif    @endif
    </div>

    <!-- Unified Maintenance Visit Scheduling & Project Assignment Card -->
    @php
        $isTerminal = in_array($serviceRequest->current_status, ['Completed', 'Cancelled', 'Rejected']);
        $showUnifiedBox = !$isTerminal && (
            !$serviceRequest->project 
            || $serviceRequest->project->current_status === 'Pending Schedule Confirmation'
            || in_array($serviceRequest->current_status, ['Submitted', 'Pending', 'Schedule Set', 'Schedule Confirmed', 'Schedule Refused'])
        );
    @endphp
    @if($showUnifiedBox)
        <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-6 sm:p-7 shadow-sm space-y-4"
             x-data="{ 
                 scheduledDate: '{{ $serviceRequest->scheduled_date?->format('Y-m-d') ?? date('Y-m-d') }}',
                 timeWindow: '{{ $serviceRequest->scheduled_time_window ?? 'AM-PM' }}',
                 priority: '{{ strtolower($serviceRequest->priority ?? 'routine') }}',
                 scheduleStatus: '{{ $serviceRequest->schedule_status ?? 'none' }}',
                 showRejectPanel: false,
                 isMinimized: false
             }">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3"
                 :class="{'pb-3 border-b border-gray-100 dark:border-zinc-800': !isMinimized}">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                            Maintenance Visit Scheduling &amp; Project Assignment
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Set visit schedule, assign maintenance personnel, and process request approval.
                        </p>
                    </div>
                </div>

                <!-- Status Badge & Optional Toggle -->
                <div class="flex items-center gap-2 flex-wrap">
                    @if($serviceRequest->schedule_status === 'approved')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 text-xs font-bold rounded-full">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Schedule Confirmed by Client
                        </span>
                    @elseif($serviceRequest->schedule_status === 'pending_client_approval')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-800 text-xs font-bold rounded-full">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            Awaiting Client Confirmation
                        </span>
                        <button type="button" 
                                @click="isMinimized = !isMinimized" 
                                class="px-2.5 py-1 text-xs font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white rounded-lg border border-gray-200 dark:border-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-800 transition inline-flex items-center gap-1.5"
                                :title="isMinimized ? 'Expand form to modify schedule or workers' : 'Minimize form'">
                            <span x-text="isMinimized ? 'Modify' : 'Minimize'"></span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{'rotate-180': !isMinimized}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    @elseif($serviceRequest->schedule_status === 'declined')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-800 text-xs font-bold rounded-full">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            Client Requested Reschedule
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-slate-100 dark:bg-zinc-800 text-gray-600 dark:text-gray-400 text-xs font-bold rounded-full">
                            Visit Schedule Pending
                        </span>
                    @endif
                </div>
            </div>

            <!-- Minimized Info Banner when Awaiting -->
            @if($serviceRequest->schedule_status === 'pending_client_approval')
                <div x-show="isMinimized" x-cloak class="pt-1 text-xs text-gray-600 dark:text-gray-400 flex flex-wrap items-center gap-x-5 gap-y-1">
                    @if($serviceRequest->scheduled_date)
                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <span class="text-gray-400 font-bold uppercase text-[10px]">Proposed Visit:</span>
                            <span class="font-bold text-slate-800 dark:text-gray-200">{{ $serviceRequest->scheduled_date->format('M d, Y') }} ({{ $serviceRequest->scheduled_time_window }})</span>
                        </span>
                    @endif
                    @if($serviceRequest->project && $serviceRequest->project->workers->isNotEmpty())
                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <span class="text-gray-400 font-bold uppercase text-[10px]">Assigned Personnel:</span>
                            <span class="font-bold text-slate-800 dark:text-gray-200">{{ $serviceRequest->project->workers->map(fn($w) => $w->user->first_name . ' ' . $w->user->last_name)->join(', ') }}</span>
                        </span>
                    @endif
                </div>
            @endif

            <!-- Bottom Part: Form & Action Steps (Hidden when isMinimized is true) -->
            <div x-show="!isMinimized" class="space-y-5">

            @if($serviceRequest->schedule_status === 'declined' && $serviceRequest->schedule_decline_reason)
                <div class="p-3.5 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 rounded-xl flex items-start gap-3">
                    <span class="text-rose-600 dark:text-rose-400 shrink-0 text-base">⚠️</span>
                    <div class="text-xs">
                        <p class="font-bold text-rose-900 dark:text-rose-200">Client's Reason for Rescheduling:</p>
                        <p class="text-rose-700 dark:text-rose-300 mt-0.5">{{ $serviceRequest->schedule_decline_reason }}</p>
                        <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1 font-semibold">Please adjust the date and time window below, verify workers, and resend proposal.</p>
                    </div>
                </div>
            @endif

            @if($serviceRequest->scheduled_date && $serviceRequest->schedule_status === 'approved')
                <div class="p-4 bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="p-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </span>
                        <div>
                            <div class="text-xs font-bold text-emerald-900 dark:text-emerald-200">
                                Agreed Visit Schedule (Confirmed by Client)
                            </div>
                            <div class="text-xs text-emerald-700 dark:text-emerald-300 font-medium">
                                {{ $serviceRequest->scheduled_date->format('F d, Y') }} ({{ $serviceRequest->scheduled_date->format('l') }}) • {{ match($serviceRequest->scheduled_time_window) {
                                    'AM' => 'Morning (8:00 AM - 12:00 PM)',
                                    'PM' => 'Afternoon (1:00 PM - 5:00 PM)',
                                    'AM-PM' => 'Whole Day (8:00 AM - 5:00 PM)',
                                    default => $serviceRequest->scheduled_time_window ?? 'Whole Day'
                                } }}
                            </div>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/60 px-3 py-1 rounded-full border border-emerald-200 dark:border-emerald-800 shrink-0">
                        Ready to Assign &amp; Launch
                    </span>
                </div>
            @endif

            <!-- Unified Form: Confirm Schedule, Approve & Assign in One Step -->
            <form action="{{ route('admin.requests.approve', $serviceRequest->request_id) }}" 
                  method="POST" 
                  class="space-y-5">
                @csrf

                <!-- 1. Schedule Selection -->
                <div>
                    <div class="text-xs font-bold text-slate-800 dark:text-gray-200 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-blue-600 text-white text-[11px] flex items-center justify-center font-extrabold">1</span>
                        Visit Schedule
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 dark:bg-zinc-800/40 p-4 rounded-xl border border-gray-100 dark:border-zinc-800">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-1.5">
                                Visit Date (Calendar) <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="scheduled_date" 
                                   x-model="scheduledDate" 
                                   min="{{ date('Y-m-d') }}" 
                                   required 
                                   class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-[#0033a0] shadow-2xs">
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Target date for GSO maintenance staff on-site visit.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-1.5">
                                Time Window / Duration <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition text-center"
                                       :class="timeWindow === 'AM' ? 'border-[#0033a0] bg-blue-50/80 dark:bg-blue-950/40 text-[#0033a0] dark:text-blue-300 font-bold' : 'border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-900'">
                                    <input type="radio" name="scheduled_time_window" value="AM" x-model="timeWindow" class="sr-only">
                                    <span class="text-xs font-extrabold">AM</span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">8AM - 12PM</span>
                                </label>

                                <label class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition text-center"
                                       :class="timeWindow === 'PM' ? 'border-[#0033a0] bg-blue-50/80 dark:bg-blue-950/40 text-[#0033a0] dark:text-blue-300 font-bold' : 'border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-900'">
                                    <input type="radio" name="scheduled_time_window" value="PM" x-model="timeWindow" class="sr-only">
                                    <span class="text-xs font-extrabold">PM</span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">1PM - 5PM</span>
                                </label>

                                <label class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition text-center"
                                       :class="timeWindow === 'AM-PM' ? 'border-[#0033a0] bg-blue-50/80 dark:bg-blue-950/40 text-[#0033a0] dark:text-blue-300 font-bold' : 'border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-900'">
                                    <input type="radio" name="scheduled_time_window" value="AM-PM" x-model="timeWindow" class="sr-only">
                                    <span class="text-xs font-extrabold">AM - PM</span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">Whole Day</span>
                                </label>
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Specify whether the visit is morning, afternoon, or whole day.</p>
                        </div>
                    </div>
                </div>

                <!-- 2. Category & Priority -->
                <div>
                    <div class="text-xs font-bold text-slate-800 dark:text-gray-200 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-blue-600 text-white text-[11px] flex items-center justify-center font-extrabold">2</span>
                        Verification &amp; Priority
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-1.5">Verify Category <span class="text-red-500">*</span></label>
                            <select name="category_id" id="categorySelect" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->category_id }}" data-name="{{ strtolower($category->category_name) }}" {{ $serviceRequest->category_id == $category->category_id ? 'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-1.5">Project Priority <span class="text-red-500">*</span></label>
                            <select name="priority" x-model="priority" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]" required>
                                <option value="routine">Routine Maintenance (Requires Client Schedule Confirmation)</option>
                                <option value="urgent">High Priority (Bypasses Client Confirmation)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 3. Assign Maintenance Personnel -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-bold text-slate-800 dark:text-gray-200 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-full bg-blue-600 text-white text-[11px] flex items-center justify-center font-extrabold">3</span>
                            Assign Maintenance Personnel
                        </div>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">Select one or more staff to assign to this project</span>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-72 overflow-y-auto p-3 border border-blue-200 dark:border-zinc-700 rounded-xl bg-slate-50/50 dark:bg-zinc-900/50">
                        @php
                            $assignedIds = $serviceRequest->project ? $serviceRequest->project->workers->pluck('worker_id')->toArray() : [];
                        @endphp
                        @foreach($workers as $worker)
                            @php
                                $teamName = strtolower($worker->team->team_name ?? '');
                                $categoryName = strtolower($serviceRequest->category->category_name ?? '');
                                $isRecommended = false;
                                if ($teamName && $categoryName) {
                                    preg_match_all('/\w+/', $categoryName, $catWords);
                                    foreach ($catWords[0] as $word) {
                                        if (strlen($word) > 3 && str_contains($teamName, $word)) {
                                            $isRecommended = true;
                                            break;
                                        }
                                    }
                                }
                                $isAssigned = in_array($worker->worker_id, $assignedIds);
                                $isChecked = $isAssigned || (empty($assignedIds) && $isRecommended);
                                $activeCount = $worker->projects->count();
                            @endphp
                            <label class="worker-option flex items-center gap-2.5 cursor-pointer p-2.5 bg-white dark:bg-zinc-900 hover:bg-blue-50 dark:hover:bg-zinc-800 rounded-xl border border-gray-200 dark:border-zinc-800 {{ $isRecommended ? 'border-blue-200 dark:border-blue-800' : '' }} transition-colors min-w-0" data-team="{{ strtolower($worker->team->team_name ?? '') }}">
                                <input type="checkbox" name="worker_ids[]" value="{{ $worker->worker_id }}" {{ $isChecked ? 'checked' : '' }} class="worker-checkbox rounded text-[#0033a0] focus:ring-[#0033a0] w-4 h-4 shrink-0">
                                <div class="flex-1 min-w-0 flex items-center justify-between gap-1.5">
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-slate-900 dark:text-gray-200 truncate" title="{{ $worker->user->first_name ?? 'Unknown' }} {{ $worker->user->last_name ?? '' }}">
                                            {{ $worker->user->first_name ?? 'Unknown' }} {{ $worker->user->last_name ?? '' }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 truncate flex items-center gap-1.5" title="{{ $worker->team->team_name ?? 'No Unit' }}">
                                            <span>{{ $worker->team->team_name ?? 'No Unit' }}</span>
                                            <span>•</span>
                                            @if($activeCount === 0)
                                                <span class="text-emerald-600 dark:text-emerald-400 font-bold">Available</span>
                                            @elseif($activeCount === 1)
                                                <span class="text-amber-600 dark:text-amber-400 font-bold">Busy (1 Active)</span>
                                            @else
                                                <span class="text-amber-600 dark:text-amber-400 font-bold">Busy ({{ $activeCount }} Active)</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="recommended-badge text-[9px] bg-[#0033a0] text-white px-2 py-0.5 rounded-full font-extrabold uppercase tracking-wide shrink-0 {{ $isRecommended ? '' : 'hidden' }}">Recommended</span>
                                </div>
                            </label>
                        @endforeach
                        @if($workers->isEmpty())
                            <p class="text-xs text-gray-400 p-2 italic col-span-full">No active maintenance workers found.</p>
                        @endif
                    </div>
                </div>

                <!-- 4. Submit Action & Disapprove Option -->
                <div class="pt-3 border-t border-gray-100 dark:border-zinc-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <button type="button" 
                            @click="showRejectPanel = !showRejectPanel" 
                            class="px-4 py-2.5 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition border border-rose-200 dark:border-rose-900/50 inline-flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span x-text="showRejectPanel ? 'Hide Disapproval Form' : 'Decline / Disapprove Request'"></span>
                    </button>

                    <div class="flex items-center gap-3">
                        <button type="submit" 
                                class="w-full sm:w-auto px-7 py-3 bg-[#0033a0] hover:bg-[#002480] text-white font-bold text-xs sm:text-sm rounded-xl transition shadow-md inline-flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Confirm Schedule, Approve &amp; Assign Workers</span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Collapsible Disapproval Panel -->
            <div x-show="showRejectPanel" 
                 x-cloak 
                 class="pt-4 border-t border-rose-200 dark:border-rose-900/50">
                <form action="{{ route('admin.requests.reject', $serviceRequest->request_id) }}" method="POST" class="bg-rose-50/60 dark:bg-rose-950/20 p-5 rounded-2xl border border-rose-200 dark:border-rose-900/60 space-y-3">
                    @csrf
                    <div class="flex items-center gap-2 text-rose-700 dark:text-rose-400 font-bold text-xs uppercase tracking-wider">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Confirm Request Disapproval
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-1">Reason for Disapproval <span class="text-red-500">*</span></label>
                        <textarea name="feedback" rows="3" placeholder="State why this service request cannot be fulfilled" class="w-full p-3 bg-white dark:bg-zinc-900 border border-rose-200 dark:border-zinc-700 rounded-xl text-xs text-slate-800 dark:text-gray-200 focus:outline-none focus:border-rose-500" required></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showRejectPanel = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                            Confirm Disapprove Request
                        </button>
                    </div>
                </form>
            </div>
            </div> <!-- Close div for !isMinimized -->
        </div>
    @endif


    <!-- Clientele Satisfaction Rating Section Card (Displayed when client has rated the request) -->
    <!-- Clientele Satisfaction Rating Section Card (Displayed when client has rated the request) -->
    @if($serviceRequest->evaluation)
        <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border-2 border-[#0033a0] dark:border-blue-700 p-7 shadow-sm"
             x-data="{ evalProofOpen: false }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-zinc-800 pb-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-400 border border-blue-200 dark:border-blue-800/80 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-base font-extrabold text-[#0033a0] dark:text-blue-400">
                                Clientele Satisfaction Measurement Rating
                            </h2>
                            @if($serviceRequest->evaluation->rated_by_admin)
                                <span class="px-2.5 py-0.5 bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-extrabold rounded-full uppercase tracking-wider">
                                    Recorded by Admin (Physical Paper at GSO)
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium">
                            Submitted {{ ($serviceRequest->evaluation->show_name ?? true) ? 'by ' . ($serviceRequest->client->user->first_name . ' ' . $serviceRequest->client->user->last_name) : 'anonymously' }} on {{ $serviceRequest->evaluation->rated_at ? $serviceRequest->evaluation->rated_at->format('M d, Y h:i A') : 'N/A' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    @if($serviceRequest->evaluation->proof_image_path)
                        <button type="button" @click="evalProofOpen = true" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-800 dark:text-gray-200 text-xs font-bold rounded-xl transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>View Signed Paper Proof</span>
                        </button>
                    @endif
                    <a href="{{ route('admin.requests.satisfaction', $serviceRequest->request_id) }}" target="_blank" class="px-5 py-2 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-xl transition shadow-md inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Print Form</span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-slate-50 dark:bg-zinc-800/70 p-4 rounded-xl border border-gray-200 dark:border-zinc-700/80 flex items-center gap-3.5">
                    <x-survey-mood-icon :score="$serviceRequest->evaluation->rating" class="w-12 h-12 shrink-0" />
                    <div>
                        <div class="text-[11px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Overall Rating</div>
                        <div class="text-sm font-black text-slate-900 dark:text-white">
                            {{ match((int)$serviceRequest->evaluation->rating) {
                                5 => '5 / 5 — Very Satisfied',
                                4 => '4 / 5 — Satisfied',
                                3 => '3 / 5 — Neutral',
                                2 => '2 / 5 — Dissatisfied',
                                default => '1 / 5 — Very Dissatisfied'
                            } }}
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 bg-slate-50 dark:bg-zinc-800/70 p-4 rounded-xl border border-gray-200 dark:border-zinc-700/80">
                    <div class="text-[11px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <span>Client Feedback &amp; Comments</span>
                    </div>
                    <p class="text-xs text-slate-800 dark:text-gray-200 font-medium italic">
                        "{{ $serviceRequest->evaluation->feedback_text ?: 'No additional written feedback provided.' }}"
                    </p>
                </div>
            </div>

            @php
                $funcRatings = $serviceRequest->evaluation->function_ratings;
                $funcLabels = [
                    'quality'      => 'Quality of Service',
                    'attitude'     => 'Attitude',
                    'safety'       => 'Safety Precaution',
                    'time'         => 'Time Bound',
                    'housekeeping' => 'Housekeeping',
                ];
            @endphp
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-zinc-800 flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase mr-1">Rating Breakdown:</span>
                @foreach($funcLabels as $k => $lbl)
                    @php $scoreVal = (int)($funcRatings[$k] ?? $serviceRequest->evaluation->rating); @endphp
                    <span class="px-2.5 py-1 bg-slate-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-lg text-xs font-bold text-slate-800 dark:text-gray-200 inline-flex items-center gap-1.5">
                        <x-survey-mood-icon :score="$scoreVal" class="w-4 h-4" />
                        <span>{{ $lbl }}:</span>
                        <span class="text-[#0033a0] dark:text-blue-400 font-extrabold">{{ $scoreVal }}★</span>
                    </span>
                @endforeach
            </div>

            <!-- Lightbox for Signed Physical Evaluation Photo Proof -->
            @if($serviceRequest->evaluation->proof_image_path)
                <div x-show="evalProofOpen" 
                     x-cloak 
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/80 backdrop-blur-xs"
                     @keydown.escape.window="evalProofOpen = false"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    <div class="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center bg-zinc-900 rounded-2xl overflow-hidden shadow-2xl border border-zinc-700" 
                         @click.outside="evalProofOpen = false">
                        <div class="w-full flex items-center justify-between py-3 px-5 bg-zinc-800 text-white border-b border-zinc-700">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-200">Signed Physical Satisfaction Evaluation Proof</span>
                            <button type="button" @click="evalProofOpen = false" class="p-1.5 text-gray-400 hover:text-white hover:bg-zinc-700 rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="w-full p-4 flex items-center justify-center overflow-auto max-h-[80vh] bg-black/50">
                            <img src="{{ Storage::url($serviceRequest->evaluation->proof_image_path) }}" alt="Signed Evaluation Proof" class="max-h-[75vh] w-auto max-w-full object-contain rounded-lg shadow-lg">
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @elseif($serviceRequest->current_status === 'Completed')
        <!-- Option for Admin to Record Physical Rating Submitted at GSO -->
        <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-6 sm:p-7 shadow-sm space-y-4"
             x-data="{ 
                 openForm: false,
                 rating: 5,
                 quality: 5,
                 attitude: 5,
                 safety: 5,
                 time: 5,
                 housekeeping: 5,
                 calcAvg() {
                     const avg = (Number(this.quality) + Number(this.attitude) + Number(this.safety) + Number(this.time) + Number(this.housekeeping)) / 5;
                     this.rating = Math.round(avg);
                 }
             }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                            Physical Paper Satisfaction Rating (Submitted at GSO)
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Did the client submit a physical paper evaluation form at the GSO office? Record it here with signed photo proof.
                        </p>
                    </div>
                </div>
                <button type="button" 
                        @click="openForm = !openForm" 
                        class="px-4 py-2 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-xl transition shadow-sm inline-flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span x-text="openForm ? 'Cancel / Close Form' : 'Record Physical Client Evaluation'">Record Physical Client Evaluation</span>
                </button>
            </div>

            <!-- Collapsible Form -->
            <div x-show="openForm" x-cloak x-transition class="pt-4 border-t border-gray-100 dark:border-zinc-800">
                <form action="{{ route('admin.requests.physical-evaluation', $serviceRequest->request_id) }}" method="POST" enctype="multipart/form-data" class="bg-blue-50/40 dark:bg-zinc-800/40 p-5 sm:p-6 rounded-2xl border border-blue-200 dark:border-zinc-700 space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                        <!-- Quality -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 uppercase mb-1">Quality of Service</label>
                            <select name="quality" x-model.number="quality" @change="calcAvg()" class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-bold text-slate-900 dark:text-white">
                                <option value="5">5 - Very Satisfied</option>
                                <option value="4">4 - Satisfied</option>
                                <option value="3">3 - Neutral</option>
                                <option value="2">2 - Dissatisfied</option>
                                <option value="1">1 - Very Dissatisfied</option>
                            </select>
                        </div>

                        <!-- Attitude -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 uppercase mb-1">Attitude</label>
                            <select name="attitude" x-model.number="attitude" @change="calcAvg()" class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-bold text-slate-900 dark:text-white">
                                <option value="5">5 - Very Satisfied</option>
                                <option value="4">4 - Satisfied</option>
                                <option value="3">3 - Neutral</option>
                                <option value="2">2 - Dissatisfied</option>
                                <option value="1">1 - Very Dissatisfied</option>
                            </select>
                        </div>

                        <!-- Safety -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 uppercase mb-1">Safety Precautions</label>
                            <select name="safety" x-model.number="safety" @change="calcAvg()" class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-bold text-slate-900 dark:text-white">
                                <option value="5">5 - Very Satisfied</option>
                                <option value="4">4 - Satisfied</option>
                                <option value="3">3 - Neutral</option>
                                <option value="2">2 - Dissatisfied</option>
                                <option value="1">1 - Very Dissatisfied</option>
                            </select>
                        </div>

                        <!-- Time -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 uppercase mb-1">Time Bound</label>
                            <select name="time" x-model.number="time" @change="calcAvg()" class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-bold text-slate-900 dark:text-white">
                                <option value="5">5 - Very Satisfied</option>
                                <option value="4">4 - Satisfied</option>
                                <option value="3">3 - Neutral</option>
                                <option value="2">2 - Dissatisfied</option>
                                <option value="1">1 - Very Dissatisfied</option>
                            </select>
                        </div>

                        <!-- Housekeeping -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 uppercase mb-1">Housekeeping</label>
                            <select name="housekeeping" x-model.number="housekeeping" @change="calcAvg()" class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-bold text-slate-900 dark:text-white">
                                <option value="5">5 - Very Satisfied</option>
                                <option value="4">4 - Satisfied</option>
                                <option value="3">3 - Neutral</option>
                                <option value="2">2 - Dissatisfied</option>
                                <option value="1">1 - Very Dissatisfied</option>
                            </select>
                        </div>
                    </div>

                    <!-- Overall Rating & Feedback -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 uppercase mb-1">Overall Rating (1 - 5)</label>
                            <select name="rating" x-model.number="rating" class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border-2 border-[#0033a0] rounded-lg text-xs font-black text-[#0033a0] dark:text-blue-400">
                                <option value="5">5 - Very Satisfied</option>
                                <option value="4">4 - Satisfied</option>
                                <option value="3">3 - Neutral</option>
                                <option value="2">2 - Dissatisfied</option>
                                <option value="1">1 - Very Dissatisfied</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 uppercase mb-1">Client Feedback / Comments (Optional)</label>
                            <input type="text" name="feedback_text" placeholder="e.g. Excellent service, responsive workers" class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs text-slate-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Required Photo Proof of Physical Signed Form -->
                    <div class="pt-2">
                        <label class="block text-xs font-bold text-slate-900 dark:text-white mb-1">
                            Photo Proof of Signed Physical Form <span class="text-red-500 font-bold">* (Required)</span>
                        </label>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-2">
                            Upload a clear photo or scan of the physical evaluation sheet with client's signature.
                        </p>
                        <input type="file" name="proof_photo" accept="image/jpeg,image/png,image/webp" required class="block w-full text-xs text-gray-500 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#0033a0] file:text-white hover:file:bg-[#002480] file:cursor-pointer cursor-pointer border border-gray-300 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900 p-2">
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-200 dark:border-zinc-700">
                        <button type="button" @click="openForm = false" class="px-4 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-md inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Save &amp; Record Physical Evaluation</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Assigned Personnel & Maintenance Unit Card (Displayed when request is approved and has assigned workers) -->
    @if($serviceRequest->project && $serviceRequest->project->workers->isNotEmpty())
        <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-2xs">
            <h2 class="text-base font-bold text-[#0033a0] dark:text-blue-400 mb-4">
                Assigned Maintenance Personnel &amp; Unit
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($serviceRequest->project->workers as $assignedWorker)
                    @php
                        $workerUser = $assignedWorker->staff->user ?? null;
                        $workerTeam = $assignedWorker->team->team_name ?? 'Maintenance Unit';
                        $isLeader = ($assignedWorker->team && $assignedWorker->team->teamLeader && $assignedWorker->team->teamLeader->staff_id === $assignedWorker->staff_id);
                    @endphp
                    <div class="bg-blue-50/50 dark:bg-zinc-800/60 border border-blue-100 dark:border-zinc-700 rounded-xl p-4 flex items-center gap-3.5 shadow-2xs">
                        <div class="w-10 h-10 rounded-full bg-[#0038A8] text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-2xs">
                            {{ strtoupper(substr($workerUser->first_name ?? 'W', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-gray-900 dark:text-white text-xs flex items-center gap-1.5">
                                <span class="truncate">{{ $workerUser->first_name ?? 'Worker' }} {{ $workerUser->last_name ?? '' }}</span>
                                @if($isLeader)
                                    <span class="text-[10px] font-bold text-[#0038A8] dark:text-blue-300 bg-blue-100 dark:bg-blue-950/80 px-1.5 py-0.2 rounded border border-blue-200 shrink-0">
                                        Leader
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                                {{ $workerTeam }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif



    @if($serviceRequest->project && !in_array($serviceRequest->current_status, ['Completed', 'Cancelled', 'Rejected']))
        <!-- Admin Operational Override Card -->
        <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-blue-200 dark:border-zinc-800 p-6 shadow-sm space-y-4"
             x-data="{ showStartModal: false, showCompleteModal: false }"
             @close-start-modal.window="showStartModal = false"
             @close-complete-modal.window="showCompleteModal = false">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-gray-100 dark:border-zinc-800">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                            Admin Operational Override
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Perform operational progress updates on behalf of workers if they are offline or in the field without connectivity.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @if(in_array($serviceRequest->current_status, ['Approved', 'Pending', 'On Hold', 'Awaiting Materials', 'Awaiting Verification of Bill of Materials', 'BOM Verified (Awaiting Client Approval)', 'Schedule Set', 'Schedule Confirmed']))
                        @if($serviceRequest->isScheduleApproved())
                            <button type="button" @click="showStartModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Start Task (Before Photo)
                            </button>
                        @else
                            <button type="button" disabled class="px-4 py-2 bg-gray-200 dark:bg-zinc-800 text-gray-400 dark:text-gray-500 text-xs font-bold rounded-xl cursor-not-allowed inline-flex items-center gap-1.5 opacity-80" title="Client must approve the scheduled date before starting task">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Start Task (Before Photo)
                            </button>
                        @endif
                    @endif

                    @if(in_array($serviceRequest->current_status, ['In Progress', 'Pending Verification']))
                        <button type="button" @click="showCompleteModal = true" class="px-4 py-2 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-xl transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Complete Task (After Photo &amp; Close)
                        </button>
                    @endif
                </div>
            </div>

            <!-- Start Task Modal -->
            <div x-show="showStartModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs"
                 x-data="adminOverrideHandler('start')"
                 @keydown.escape.window="closeModal()">
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto" @click.outside="closeModal()">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-800">
                        <div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Admin Override: Start Task</h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Set task to In Progress with required Before-Work photo evidence.</p>
                        </div>
                        <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">✕</button>
                    </div>

                    <form action="{{ route('admin.requests.start-override', $serviceRequest->request_id) }}" method="POST" enctype="multipart/form-data" class="space-y-4" @submit="validateAndSubmit($event)">
                        @csrf

                        <!-- Proof Photo Upload & Camera Trigger -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-300">
                                    Before-Work Photo Evidence <span class="text-red-500 font-black">*</span>
                                </label>
                                <span class="text-[11px] text-gray-400">JPG, PNG, WEBP</span>
                            </div>

                            <!-- Hidden file inputs -->
                            <input type="file" name="proof" x-ref="fileInput" accept="image/*,application/pdf" @change="handleFile($event.target.files[0])" class="hidden">
                            <input type="file" x-ref="mobileCameraInput" accept="image/*" capture="environment" @change="handleFile($event.target.files[0])" class="hidden">

                            <!-- Buttons when no file selected -->
                            <div x-show="!proofFile" class="border-2 border-dashed border-blue-300 dark:border-blue-900/60 rounded-2xl p-5 bg-blue-50/30 dark:bg-blue-950/20 text-center">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 max-w-sm mx-auto">
                                    <button type="button" 
                                            @click="$refs.fileInput.click()" 
                                            class="w-full sm:flex-1 px-4 py-2.5 bg-white dark:bg-zinc-800 border-2 border-gray-200 dark:border-zinc-700 hover:border-[#0033a0] rounded-xl text-xs font-bold text-gray-800 dark:text-gray-100 transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                        <span>Choose File</span>
                                    </button>

                                    <span class="text-xs text-gray-400 font-bold">or</span>

                                    <button type="button" 
                                            @click="openCamera()" 
                                            class="w-full sm:flex-1 px-4 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Snap Photo (Camera)</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-2.5">Capture or select an on-site photo before work begins.</p>
                            </div>

                            <!-- Preview Card when file selected -->
                            <div x-show="proofFile" x-cloak class="border-2 border-emerald-300 dark:border-emerald-800 bg-emerald-50/40 dark:bg-emerald-950/20 rounded-2xl p-3.5 flex items-center justify-between gap-4 shadow-xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-14 h-16 rounded-xl border border-emerald-200 dark:border-emerald-800 shrink-0 overflow-hidden bg-emerald-100 dark:bg-emerald-950/50 flex items-center justify-center">
                                        <template x-if="proofPreviewUrl">
                                            <img :src="proofPreviewUrl" alt="" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!proofPreviewUrl">
                                            <span class="font-bold text-xs text-emerald-800 dark:text-emerald-300">DOC</span>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="proofFile"></span>
                                        </div>
                                        <p class="text-[11px] text-emerald-700 dark:text-emerald-300 font-semibold mt-0.5">Photo attached &amp; ready</p>
                                        <p class="text-[10px] text-gray-400 font-mono" x-text="proofSize"></p>
                                    </div>
                                </div>
                                <button type="button" @click="clearProof()" class="px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-950/40 rounded-xl transition border border-red-200 dark:border-red-800 shrink-0 cursor-pointer">
                                    ✕ Remove
                                </button>
                            </div>
                        </div>

                        <!-- Operational Notes -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                                Operational Notes / Remarks (Optional)
                            </label>
                            <textarea name="remarks" rows="2" placeholder="e.g. Worker offline, admin started task on team's behalf..." class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0]"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl cursor-pointer">Cancel</button>
                            <button type="submit" :disabled="saving" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs inline-flex items-center gap-2 cursor-pointer disabled:opacity-60">
                                <svg x-show="saving" x-cloak class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-text="saving ? 'Starting...' : 'Set In Progress'">Set In Progress</span>
                            </button>
                        </div>
                    </form>

                    <!-- Live Camera Modal -->
                    <div x-show="cameraActive" x-cloak class="fixed inset-0 bg-black/85 z-60 flex items-center justify-center p-4">
                        <div class="bg-[#18181b] border border-zinc-700 rounded-3xl p-5 max-w-sm w-full shadow-2xl relative flex flex-col items-center">
                            <div class="flex items-center justify-between w-full mb-3 text-white">
                                <h3 class="text-xs font-bold uppercase tracking-wider flex items-center gap-2">
                                    <span>Before-Work Photo</span>
                                </h3>
                                <button type="button" @click="flipCamera()" class="p-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-gray-300 hover:text-white transition text-xs font-bold flex items-center gap-1.5 cursor-pointer" title="Flip Camera">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Flip</span>
                                </button>
                            </div>

                            <div x-show="cameraError" x-cloak class="w-full mb-4 p-4 bg-red-950/40 border border-red-800/80 rounded-2xl text-center text-xs text-red-200 space-y-2">
                                <p class="font-bold flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Camera In Use or Blocked</span>
                                </p>
                                <p class="text-[11px] text-gray-300 leading-relaxed" x-text="cameraError"></p>
                                <div class="pt-2 flex flex-col gap-2">
                                    <button type="button" @click="startStream()" class="w-full px-3 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-xl text-xs font-bold transition cursor-pointer">
                                        🔄 Retry Camera
                                    </button>
                                    <button type="button" @click="closeCamera(); $refs.fileInput.click()" class="w-full px-3 py-2 bg-[#0038A8] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition cursor-pointer">
                                        📁 Choose Photo from Files Instead
                                    </button>
                                </div>
                            </div>

                            <div x-show="!cameraError" class="w-full bg-black rounded-2xl overflow-hidden mb-4 relative aspect-[3/4] flex items-center justify-center border-2 border-zinc-700">
                                <video x-ref="cameraVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>

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

                            <div class="flex items-center justify-between w-full px-4">
                                <button type="button" @click="closeCamera()" class="px-4 py-2.5 bg-zinc-800 hover:bg-zinc-700 text-gray-300 rounded-xl text-xs font-bold transition cursor-pointer">
                                    Cancel
                                </button>

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
                </div>
            </div>

            <!-- Complete Task Modal -->
            <div x-show="showCompleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs"
                 x-data="adminOverrideHandler('complete')"
                 @keydown.escape.window="closeModal()">
                <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto" @click.outside="closeModal()">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-800">
                        <div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Admin Override: Complete Task</h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Document work accomplishment and close request on behalf of workers.</p>
                        </div>
                        <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">✕</button>
                    </div>

                    <form action="{{ route('admin.requests.complete-override', $serviceRequest->request_id) }}" method="POST" enctype="multipart/form-data" class="space-y-4" @submit="validateAndSubmit($event)">
                        @csrf

                        <!-- Nature of Work Executed (Full Repair vs Inspection Only) -->
                        <div class="space-y-3">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Nature of Work Executed <span class="text-red-500">*</span>:
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition"
                                       :class="completionType === 'Full Repair' ? 'border-[#0033a0] bg-blue-50/70 dark:bg-blue-950/40' : 'border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900'">
                                    <input type="radio" name="completion_type" value="Full Repair" x-model="completionType" class="mt-1 text-[#0033a0] focus:ring-[#0033a0]">
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 dark:text-white">Direct Repair / Maintenance Done</div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Physical repair, replacement, or maintenance executed.</div>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition"
                                       :class="completionType === 'Inspection Only' ? 'border-[#0033a0] bg-blue-50/70 dark:bg-blue-950/40' : 'border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900'">
                                    <input type="radio" name="completion_type" value="Inspection Only" x-model="completionType" class="mt-1 text-[#0033a0] focus:ring-[#0033a0]">
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 dark:text-white">Inspection &amp; Assessment Only</div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Site inspected and assessed without direct physical repairs.</div>
                                    </div>
                                </label>
                            </div>

                            <!-- Details if Full Repair -->
                            <div x-show="completionType === 'Full Repair'">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Accomplishment Summary / Work Executed:
                                </label>
                                <input type="text" 
                                       name="nature_of_work" 
                                       x-model="natureOfWork" 
                                       placeholder="e.g. Repair &amp; Maintenance Done / Replaced ball valve" 
                                       class="w-full px-3.5 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                            </div>

                            <!-- Recommendations / Notes (Only for Inspection Only) -->
                            <div x-show="completionType === 'Inspection Only'">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Inspection Findings &amp; Recommendation (Optional):
                                </label>
                                <textarea name="recommendation" 
                                          rows="2" 
                                          x-model="recommendation"
                                          placeholder="e.g. Inspected circuit breaker; no major wiring damage found." 
                                          class="w-full px-3 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0]"></textarea>
                            </div>
                        </div>

                        <!-- Proof Photo Upload & Camera Trigger -->
                        <div class="pt-3 border-t border-gray-100 dark:border-zinc-800">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-300">
                                    Proof of Completion Photo Evidence <span class="text-red-500 font-black">*</span>
                                </label>
                                <span class="text-[11px] text-gray-400">JPG, PNG, WEBP</span>
                            </div>

                            <!-- Hidden file inputs -->
                            <input type="file" name="proof" x-ref="fileInput" accept="image/*,application/pdf" @change="handleFile($event.target.files[0])" class="hidden">
                            <input type="file" x-ref="mobileCameraInput" accept="image/*" capture="environment" @change="handleFile($event.target.files[0])" class="hidden">

                            <!-- Buttons when no file selected -->
                            <div x-show="!proofFile" class="border-2 border-dashed border-blue-300 dark:border-blue-900/60 rounded-2xl p-5 bg-blue-50/30 dark:bg-blue-950/20 text-center">
                                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 max-w-sm mx-auto">
                                    <button type="button" 
                                            @click="$refs.fileInput.click()" 
                                            class="w-full sm:flex-1 px-4 py-2.5 bg-white dark:bg-zinc-800 border-2 border-gray-200 dark:border-zinc-700 hover:border-[#0033a0] rounded-xl text-xs font-bold text-gray-800 dark:text-gray-100 transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                        <span>Choose File</span>
                                    </button>

                                    <span class="text-xs text-gray-400 font-bold">or</span>

                                    <button type="button" 
                                            @click="openCamera()" 
                                            class="w-full sm:flex-1 px-4 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Snap Photo (Camera)</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-2.5">Capture or select an accomplishment photo showing finished work or inspection on-site.</p>
                            </div>

                            <!-- Preview Card when file selected -->
                            <div x-show="proofFile" x-cloak class="border-2 border-blue-300 dark:border-blue-800 bg-blue-50/40 dark:bg-blue-950/20 rounded-2xl p-3.5 flex items-center justify-between gap-4 shadow-xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-14 h-16 rounded-xl border border-blue-200 dark:border-blue-800 shrink-0 overflow-hidden bg-blue-100 dark:bg-blue-950/50 flex items-center justify-center">
                                        <template x-if="proofPreviewUrl">
                                            <img :src="proofPreviewUrl" alt="" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!proofPreviewUrl">
                                            <span class="font-bold text-xs text-blue-800 dark:text-blue-300">DOC</span>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-block w-2 h-2 rounded-full bg-blue-600"></span>
                                            <span class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="proofFile"></span>
                                        </div>
                                        <p class="text-[11px] text-blue-700 dark:text-blue-300 font-semibold mt-0.5">Photo attached &amp; ready</p>
                                        <p class="text-[10px] text-gray-400 font-mono" x-text="proofSize"></p>
                                    </div>
                                </div>
                                <button type="button" @click="clearProof()" class="px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-950/40 rounded-xl transition border border-red-200 dark:border-red-800 shrink-0 cursor-pointer">
                                    ✕ Remove
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl cursor-pointer">Cancel</button>
                            <button type="submit" :disabled="saving" class="px-5 py-2 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-xl shadow-xs inline-flex items-center gap-2 cursor-pointer disabled:opacity-60">
                                <svg x-show="saving" x-cloak class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-text="saving ? 'Completing...' : 'Complete & Close Requisition'">Complete &amp; Close Requisition</span>
                            </button>
                        </div>
                    </form>

                    <!-- Live Camera Modal -->
                    <div x-show="cameraActive" x-cloak class="fixed inset-0 bg-black/85 z-60 flex items-center justify-center p-4">
                        <div class="bg-[#18181b] border border-zinc-700 rounded-3xl p-5 max-w-sm w-full shadow-2xl relative flex flex-col items-center">
                            <div class="flex items-center justify-between w-full mb-3 text-white">
                                <h3 class="text-xs font-bold uppercase tracking-wider flex items-center gap-2">
                                    <span>Proof of Completion Photo</span>
                                </h3>
                                <button type="button" @click="flipCamera()" class="p-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-gray-300 hover:text-white transition text-xs font-bold flex items-center gap-1.5 cursor-pointer" title="Flip Camera">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Flip</span>
                                </button>
                            </div>

                            <div x-show="cameraError" x-cloak class="w-full mb-4 p-4 bg-red-950/40 border border-red-800/80 rounded-2xl text-center text-xs text-red-200 space-y-2">
                                <p class="font-bold flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Camera In Use or Blocked</span>
                                </p>
                                <p class="text-[11px] text-gray-300 leading-relaxed" x-text="cameraError"></p>
                                <div class="pt-2 flex flex-col gap-2">
                                    <button type="button" @click="startStream()" class="w-full px-3 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-xl text-xs font-bold transition cursor-pointer">
                                        🔄 Retry Camera
                                    </button>
                                    <button type="button" @click="closeCamera(); $refs.fileInput.click()" class="w-full px-3 py-2 bg-[#0038A8] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition cursor-pointer">
                                        📁 Choose Photo from Files Instead
                                    </button>
                                </div>
                            </div>

                            <div x-show="!cameraError" class="w-full bg-black rounded-2xl overflow-hidden mb-4 relative aspect-[3/4] flex items-center justify-center border-2 border-zinc-700">
                                <video x-ref="cameraVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>

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

                            <div class="flex items-center justify-between w-full px-4">
                                <button type="button" @click="closeCamera()" class="px-4 py-2.5 bg-zinc-800 hover:bg-zinc-700 text-gray-300 rounded-xl text-xs font-bold transition cursor-pointer">
                                    Cancel
                                </button>

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
                </div>
            </div>
        </div>
    @endif

    <!-- List of Materials Section Card -->
    @if($serviceRequest->project || !in_array($serviceRequest->current_status, ['Cancelled', 'Rejected']))
        @php
            $boms = $serviceRequest->project?->billOfMaterials ?? collect();
            $pendingBomCount = $boms->whereNull('date_approved')->count();
            $hasBomItems = $boms->count() > 0;
            $isBomEditable = !in_array($serviceRequest->current_status, ['Cancelled', 'Rejected']);
        @endphp
        <div id="bom-section" 
             class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-6 sm:p-7 shadow-sm"
             x-data="{
                 items: {{ Js::from($boms->map(fn($b) => [
                     'bom_id' => $b->bom_id,
                     'material_name' => $b->material->material_name ?? 'Material Item',
                     'unit' => $b->material->unit_of_measurement ?? 'pcs',
                     'qty' => (float)$b->qty,
                     'is_approved' => !is_null($b->date_approved),
                 ])) }},
                 showAddMaterial: false,
                 submittingBOM: false,
                 selectedMaterialId: '',
                 customName: '',
                 addUnit: 'pcs',
                 addQty: 1,
                 catalog: {{ Js::from(($allMaterials ?? collect())->map(fn($m) => ['id' => $m->material_id, 'name' => $m->material_name, 'unit' => $m->unit_of_measurement ?? 'pcs'])) }},
                 isDiscrete(unit) {
                     if (!unit) return true;
                     const u = unit.toString().trim().toLowerCase();
                     const continuousUnits = ['meter', 'meters', 'm', 'length', 'lengths', 'ft', 'feet', 'foot', 'liter', 'liters', 'l', 'kg', 'kilo', 'kilos', 'kilogram', 'kilograms', 'gallon', 'gallons', 'gal', 'yard', 'yards', 'yd', 'inch', 'inches', 'cm', 'mm'];
                     return !continuousUnits.includes(u);
                 },
                 onSelectAddChange() {
                     if (this.selectedMaterialId && this.selectedMaterialId !== 'custom') {
                         const found = this.catalog.find(m => m.id == this.selectedMaterialId);
                         if (found) {
                             this.addUnit = found.unit || 'pcs';
                         }
                     } else if (this.selectedMaterialId === 'custom') {
                         if (!this.addUnit) this.addUnit = 'pcs';
                     }
                 }
             }">
            
            <!-- List of Materials Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-zinc-800 pb-4 mb-5">
                <div>
                    <div class="flex items-center gap-2.5 mb-1 flex-wrap">
                        <h2 class="text-base font-extrabold text-[#0033a0] dark:text-blue-400">
                            List of Materials
                        </h2>
                        @if($serviceRequest->bom_status === 'approved')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 uppercase">
                                <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Approved
                            </span>
                        @else
                            <template x-if="items.length === 0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 uppercase">
                                    Ready to Add
                                </span>
                            </template>
                            <template x-if="items.length > 0">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 uppercase">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Pending Approval
                                </span>
                            </template>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                        Specify required materials and quantities for this requisition.
                    </p>
                </div>

                @if($isBomEditable)
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" 
                                @click="showAddMaterial = !showAddMaterial" 
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-800 dark:text-gray-200 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span x-text="showAddMaterial ? 'Close Form' : 'Add Material'">Add Material</span>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Empty State when items.length === 0 -->
            <div x-show="items.length === 0" class="py-8 px-4 text-center border-2 border-dashed border-gray-200 dark:border-zinc-800 rounded-2xl bg-gray-50/50 dark:bg-zinc-800/30 my-4">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-blue-50 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <h4 class="text-sm font-bold text-slate-800 dark:text-gray-200 mb-1">No Materials Listed</h4>
                @if($isBomEditable)
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-4">You can add required catalog or custom materials to this requisition below.</p>
                    <button type="button" @click="showAddMaterial = true" x-show="!showAddMaterial" class="px-4 py-2 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-xl transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Add First Material Item</span>
                    </button>
                @else
                    <p class="text-xs text-gray-400 max-w-md mx-auto">No materials were requested for this requisition.</p>
                @endif
            </div>

            <!-- List of Materials Table Form -->
            <form x-show="items.length > 0" action="{{ $serviceRequest->project ? route('admin.bom.approve', $serviceRequest->project->project_id) : '#' }}" method="POST" @submit="submittingBOM = true">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ route('admin.requests.show', $serviceRequest->request_id) }}#bom-section">
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-zinc-800 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">
                                <th class="py-2.5 px-3">Material Item</th>
                                <th class="py-2.5 px-3 text-center w-32">Qty</th>
                                <th class="py-2.5 px-3 text-center w-28">Unit</th>
                                <th class="py-2.5 px-3 text-center w-28">Status</th>
                                <th class="py-2.5 px-3 text-center w-16">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-xs">
                            <template x-for="(item, idx) in items" :key="item.bom_id">
                                <tr class="hover:bg-blue-50/40 dark:hover:bg-zinc-800/40 transition">
                                    
                                    <!-- Hidden BOM ID & Unit Cost -->
                                    <input type="hidden" :name="'items[' + idx + '][bom_id]'" :value="item.bom_id">
                                    <input type="hidden" :name="'items[' + idx + '][unit_cost]'" value="0">

                                    <!-- Material Name -->
                                    <td class="py-3 px-3">
                                        <div class="font-bold text-slate-900 dark:text-white" x-text="item.material_name"></div>
                                    </td>

                                    <!-- Quantity Input -->
                                    <td class="py-3 px-3 text-center">
                                        @if($isBomEditable)
                                            <input type="number" 
                                                   :name="'items[' + idx + '][qty]'" 
                                                   x-model.number="item.qty" 
                                                   :step="isDiscrete(item.unit) ? '1' : '0.01'" 
                                                   :min="isDiscrete(item.unit) ? '1' : '0.01'" 
                                                   class="w-24 px-2 py-1.5 text-center font-bold border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#0033a0]" 
                                                   required>
                                        @else
                                            <span class="font-bold text-slate-800 dark:text-gray-200 text-xs px-2.5 py-1 bg-gray-100 dark:bg-zinc-800 rounded-lg border border-gray-200 dark:border-zinc-700" x-text="item.qty"></span>
                                        @endif
                                    </td>

                                    <!-- Unit of Measurement -->
                                    <td class="py-3 px-3 text-center">
                                        <div class="px-2.5 py-1 text-center font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-zinc-800/80 rounded-lg text-xs border border-gray-200 dark:border-zinc-700 select-none" x-text="item.unit || 'pcs'"></div>
                                        <input type="hidden" :name="'items[' + idx + '][unit_of_measurement]'" :value="item.unit">
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3 px-3 text-center">
                                        <span x-show="item.is_approved" class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            Approved
                                        </span>
                                        <span x-show="!item.is_approved" class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            Pending
                                        </span>
                                    </td>

                                    <!-- Delete Item Button -->
                                    <td class="py-3 px-3 text-center">
                                        @if($isBomEditable)
                                            <button type="button" 
                                                    @click="if(confirm('Remove this material from the list?')) { document.getElementById('delete-bom-item-' + item.bom_id).submit(); }" 
                                                    class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition cursor-pointer" 
                                                    title="Delete material">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @else
                                            <span class="text-gray-300 dark:text-zinc-600 text-xs select-none">—</span>
                                        @endif
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Action Bar -->
                <div class="mt-5 pt-4 border-t border-gray-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-gray-50 dark:bg-zinc-800/50 p-4 rounded-xl">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-700 dark:text-gray-300">Total Materials Listed:</span>
                        <span class="text-sm font-black text-[#0033a0] dark:text-blue-400" x-text="items.length + ' item' + (items.length === 1 ? '' : 's')"></span>
                    </div>

                    <div class="flex items-center gap-2.5 flex-wrap">
                        @if($isBomEditable)
                            <button type="submit" 
                                    :disabled="submittingBOM" 
                                    class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center gap-2 cursor-pointer">
                                <span x-text="submittingBOM ? 'Updating...' : 'Update Quantities'">Update Quantities</span>
                            </button>

                            @if($serviceRequest->bom_status !== 'approved')
                                <button type="button" 
                                        onclick="document.getElementById('admin-approve-bom-form').submit()" 
                                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-md inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Approve List of Materials (on Client's Behalf)</span>
                                </button>
                            @else
                                <span class="px-3 py-1.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 rounded-xl text-xs font-bold inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <span>List of Materials Approved</span>
                                </span>
                            @endif
                        @endif
                    </div>
                </div>
            </form>

            <!-- Hidden Admin Approve on Client's Behalf Form -->
            <form id="admin-approve-bom-form" action="{{ route('admin.requests.bom.approve-for-client', $serviceRequest->request_id) }}" method="POST" class="hidden">
                @csrf
            </form>

            @if($isBomEditable)
                <!-- Inline Form: Add Additional Material (Toggled) -->
                <div x-show="showAddMaterial" 
                     x-cloak 
                     x-transition 
                     class="mt-5 pt-5 border-t border-gray-200 dark:border-zinc-800 bg-blue-50/40 dark:bg-zinc-800/30 p-4 rounded-xl">
                    <h3 class="text-xs font-black uppercase tracking-wider text-[#0033a0] dark:text-blue-400 mb-3 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Item to List of Materials</span>
                    </h3>

                    <form action="{{ $serviceRequest->project ? route('admin.bom.store', $serviceRequest->project->project_id) : route('admin.requests.bom.store', $serviceRequest->request_id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ route('admin.requests.show', $serviceRequest->request_id) }}#bom-section">
                        <input type="hidden" name="unit_cost" value="0">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                            <!-- Catalog Select -->
                            <div :class="selectedMaterialId === 'custom' ? 'sm:col-span-5' : 'sm:col-span-7'">
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Select Catalog Material</label>
                                <select name="material_id" 
                                        x-model="selectedMaterialId" 
                                        @change="onSelectAddChange()" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#0033a0]" 
                                        required>
                                    <option value="">Select a material...</option>
                                    @foreach($allMaterials ?? [] as $m)
                                        <option value="{{ $m->material_id }}">{{ $m->material_name }} ({{ $m->unit_of_measurement ?? 'pcs' }})</option>
                                    @endforeach
                                    <option value="custom" class="font-bold text-[#0033a0]">+ Add New Custom Material...</option>
                                </select>
                            </div>

                            <!-- Custom Material Name if 'custom' -->
                            <div class="sm:col-span-4" x-show="selectedMaterialId === 'custom'">
                                <label class="block text-[11px] font-bold text-[#0033a0] uppercase tracking-wider mb-1">Custom Material Name</label>
                                <input type="text" 
                                       name="custom_material_name" 
                                       x-model="customName" 
                                       :required="selectedMaterialId === 'custom'" 
                                       placeholder="e.g. Teflon Tape 1/2 in" 
                                       class="w-full px-3 py-2 border border-[#0033a0] rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white">
                            </div>

                            <!-- Quantity -->
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Quantity</label>
                                <input type="number" 
                                       name="qty" 
                                       x-model.number="addQty" 
                                       :step="isDiscrete(addUnit) ? '1' : '0.01'" 
                                       :min="isDiscrete(addUnit) ? '1' : '0.01'" 
                                       placeholder="1" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white" 
                                       required>
                            </div>

                            <!-- Unit of Measurement -->
                            <div :class="selectedMaterialId === 'custom' ? 'sm:col-span-1' : 'sm:col-span-3'">
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Unit</label>
                                
                                <div x-show="selectedMaterialId !== 'custom'" class="w-full px-3 py-2 border border-gray-200 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800/80 rounded-lg text-xs font-bold text-slate-700 dark:text-gray-300 text-center flex items-center justify-center min-h-[38px] select-none">
                                    <span x-text="addUnit || 'pcs'"></span>
                                </div>
                                <input type="hidden" x-show="selectedMaterialId !== 'custom'" name="unit_of_measurement" :value="addUnit">

                                <select x-show="selectedMaterialId === 'custom'" 
                                        name="unit_of_measurement" 
                                        x-model="addUnit" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white">
                                    <option value="pcs">pcs</option>
                                    <option value="meters">meters</option>
                                    <option value="lengths">lengths</option>
                                    <option value="rolls">rolls</option>
                                    <option value="boxes">boxes</option>
                                    <option value="bags">bags</option>
                                    <option value="liters">liters</option>
                                    <option value="sheets">sheets</option>
                                    <option value="sets">sets</option>
                                    <option value="units">units</option>
                                    <option value="kg">kg</option>
                                    <option value="gallons">gallons</option>
                                    <option value="pairs">pairs</option>
                                    <option value="tubes">tubes</option>
                                    <option value="packs">packs</option>
                                    <option value="feet">feet</option>
                                    <option value="can">can</option>
                                </select>
                            </div>

                            <!-- Submit Button -->
                            <div class="sm:col-span-12 flex justify-end pt-1">
                                <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Add Material to List</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Hidden delete forms for each material item -->
                @if($serviceRequest->project)
                    @foreach($serviceRequest->project->billOfMaterials as $bItem)
                        <form id="delete-bom-item-{{ $bItem->bom_id }}" action="{{ route('admin.bom.destroy-item', ['projectId' => $serviceRequest->project->project_id, 'bomId' => $bItem->bom_id]) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                @endif
            @endif
        </div>
    @endif

    @if($serviceRequest->project && $serviceRequest->project->current_status === 'Pending Verification')
        @php
            $beforeHistory = $serviceRequest->project->histories->where('current_status', 'In Progress')->whereNotNull('proof_attachment')->last();
            $afterHistory = $serviceRequest->project->histories->whereIn('current_status', ['Pending Verification', 'Completed'])->whereNotNull('proof_attachment')->last();
        @endphp
        <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border-2 border-blue-400 dark:border-blue-700 p-7 shadow-sm"
             x-data="{ lightboxOpen: false, lightboxImg: '', lightboxTitle: '' }">
            <div class="flex items-center justify-between mb-3 border-b border-gray-100 dark:border-zinc-800 pb-3">
                <h2 class="text-base font-bold text-[#0033a0] dark:text-blue-400">
                    Worker Completed Job — Pending Final Admin Verification
                </h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300">
                    Awaiting Inspection
                </span>
            </div>
            
            <p class="text-xs text-gray-500 mb-6">Inspect the before &amp; after work evidence photos below to ensure the task was completed satisfactorily before closing this requisition.</p>

            <!-- Before & After Photos Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Before Photo -->
                <div class="bg-gray-50 dark:bg-zinc-800/60 p-4 rounded-xl border border-gray-200 dark:border-zinc-700 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                                BEFORE WORK PHOTO
                            </span>
                            @if($beforeHistory)
                                <span class="text-[10px] text-gray-400 font-medium">{{ \Carbon\Carbon::parse($beforeHistory->updated_at)->format('M d, Y h:i A') }}</span>
                            @endif
                        </div>
                        @if($beforeHistory && $beforeHistory->proof_attachment)
                            <div @click="lightboxOpen = true; lightboxImg = '{{ Storage::url($beforeHistory->proof_attachment) }}'; lightboxTitle = 'Before Work Photo'" 
                                 class="block group relative overflow-hidden rounded-xl border border-gray-200 dark:border-zinc-700 bg-black/5 dark:bg-black/40 p-2 cursor-pointer transition hover:border-amber-400">
                                <img src="{{ Storage::url($beforeHistory->proof_attachment) }}" alt="Before Work" class="w-full max-h-64 object-contain rounded-lg group-hover:scale-[1.01] transition duration-200 mx-auto">
                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1.5 rounded-xl">
                                    <span class="bg-black/60 px-3 py-1.5 rounded-lg backdrop-blur-xs">Click to Preview</span>
                                </div>
                            </div>
                        @else
                            <div class="min-h-[140px] bg-gray-100 dark:bg-zinc-800/40 rounded-xl flex items-center justify-center text-xs text-gray-400 font-medium border border-dashed border-gray-200 dark:border-zinc-700">
                                No before photo attached
                            </div>
                        @endif
                    </div>
                </div>

                <!-- After Photo -->
                <div class="bg-gray-50 dark:bg-zinc-800/60 p-4 rounded-xl border border-gray-200 dark:border-zinc-700 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                AFTER WORK PHOTO (COMPLETION)
                            </span>
                            @if($afterHistory)
                                <span class="text-[10px] text-gray-400 font-medium">{{ \Carbon\Carbon::parse($afterHistory->updated_at)->format('M d, Y h:i A') }}</span>
                            @endif
                        </div>
                        @if($afterHistory && $afterHistory->proof_attachment)
                            <div @click="lightboxOpen = true; lightboxImg = '{{ Storage::url($afterHistory->proof_attachment) }}'; lightboxTitle = 'After Work Photo (Completion)'" 
                                 class="block group relative overflow-hidden rounded-xl border border-gray-200 dark:border-zinc-700 bg-black/5 dark:bg-black/40 p-2 cursor-pointer transition hover:border-emerald-400">
                                <img src="{{ Storage::url($afterHistory->proof_attachment) }}" alt="After Work" class="w-full max-h-64 object-contain rounded-lg group-hover:scale-[1.01] transition duration-200 mx-auto">
                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1.5 rounded-xl">
                                    <span class="bg-black/60 px-3 py-1.5 rounded-lg backdrop-blur-xs">Click to Preview</span>
                                </div>
                            </div>
                        @else
                            <div class="min-h-[140px] bg-gray-100 dark:bg-zinc-800/40 rounded-xl flex items-center justify-center text-xs text-gray-400 font-medium border border-dashed border-gray-200 dark:border-zinc-700">
                                Pending completion upload
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Verification Action Section -->
            @php 
                $isWorkerInspectionOnly = ($serviceRequest->project?->nature_of_work === 'Inspection & Assessment Only'); 
            @endphp
            <div class="bg-blue-50/70 dark:bg-zinc-800/70 p-6 rounded-2xl border-2 border-blue-200 dark:border-zinc-700 space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-blue-200/80 dark:border-zinc-700">
                    <div>
                        <h3 class="text-sm font-bold text-[#0033a0] dark:text-blue-400">
                            Verify Project Completion
                        </h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                            @if($isWorkerInspectionOnly)
                                Worker completed this job as <strong>Inspection &amp; Assessment Only</strong>. Review findings before closing.
                            @else
                                Review and confirm the nature of work done before closing the request.
                            @endif
                        </p>
                    </div>
                    @if($isWorkerInspectionOnly)
                        <span class="px-3.5 py-1.5 bg-amber-100 text-amber-900 border border-amber-300 text-xs font-bold rounded-full shrink-0 shadow-xs">
                            Inspection &amp; Assessment Only
                        </span>
                    @elseif($serviceRequest->project?->nature_of_work)
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 border border-blue-300 text-xs font-bold rounded-full shrink-0">
                            {{ $serviceRequest->project->nature_of_work }}
                        </span>
                    @endif
                </div>

                <form action="{{ route('admin.requests.verify', $serviceRequest->request_id) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    @if($isManpower)
                        <!-- Nature of Work Done (MANDATORY FOR MANPOWER) -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 uppercase tracking-wider">
                                    Nature of work done (reflected on Accomplishment Report) <span class="text-red-500 font-bold">*</span>:
                                </label>
                                <span class="px-2 py-0.5 bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 text-[10px] font-extrabold rounded-md uppercase tracking-wider border border-rose-300 dark:border-rose-800">
                                    Required
                                </span>
                            </div>
                            <textarea name="work_details" 
                                      rows="3" 
                                      required
                                      placeholder="e.g. Grass cutting, ground preparation, and event assistance completed"
                                      class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border-2 border-blue-400 dark:border-blue-600 rounded-xl text-xs text-gray-800 dark:text-white focus:outline-none focus:border-[#0033a0] shadow-2xs font-medium">{{ $serviceRequest->project?->nature_of_work && $serviceRequest->project->nature_of_work !== 'Repair & Maintenance Done' ? $serviceRequest->project->nature_of_work : ($serviceRequest->project?->recommendation ?? '') }}</textarea>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                Recorded as the official <strong>Nature of work done</strong> on the Accomplishment Report.
                            </p>
                        </div>
                    @elseif($isWorkerInspectionOnly)
                        <input type="hidden" name="nature_of_work" value="Inspection & Assessment Only">
                        <!-- Nature of Work Done (Inspection Only) -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 uppercase tracking-wider">
                                    Nature of work done (reflected on Accomplishment Report) <span class="text-red-500 font-bold">*</span>:
                                </label>
                                <span class="px-2 py-0.5 bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 text-[10px] font-extrabold rounded-md uppercase tracking-wider border border-rose-300 dark:border-rose-800">
                                    Required
                                </span>
                            </div>
                            <textarea name="work_details" 
                                      rows="2.5" 
                                      required
                                      placeholder="e.g. Conducted on-site inspection; circuit breaker reset and functioning properly"
                                      class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border-2 border-blue-400 dark:border-blue-600 rounded-xl text-xs text-gray-800 dark:text-white focus:outline-none focus:border-[#0033a0] shadow-2xs font-medium">{{ $serviceRequest->project?->recommendation ?? '' }}</textarea>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                Recorded as the official <strong>Nature of work done</strong> on the Accomplishment Report.
                            </p>
                        </div>
                    @else
                        <!-- Nature of Work Done -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 uppercase tracking-wider">
                                    Nature of work done (reflected on Accomplishment Report) <span class="text-red-500 font-bold">*</span>:
                                </label>
                                <span class="px-2 py-0.5 bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 text-[10px] font-extrabold rounded-md uppercase tracking-wider border border-rose-300 dark:border-rose-800">
                                    Required
                                </span>
                            </div>
                            <textarea name="work_details" 
                                      rows="2.5" 
                                      required
                                      placeholder="e.g. Replaced fluorescent lamps and repaired electrical wiring"
                                      class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border-2 border-blue-400 dark:border-blue-600 rounded-xl text-xs text-gray-800 dark:text-white focus:outline-none focus:border-[#0033a0] shadow-2xs font-medium">{{ $serviceRequest->project?->nature_of_work && $serviceRequest->project->nature_of_work !== 'Repair & Maintenance Done' ? $serviceRequest->project->nature_of_work : ($serviceRequest->project?->recommendation ?? '') }}</textarea>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                Recorded as the official <strong>Nature of work done</strong> on the Accomplishment Report.
                            </p>
                        </div>
                    @endif

                    <div class="flex justify-end pt-1">
                        <button type="submit" class="w-full sm:w-auto bg-[#0033a0] hover:bg-[#002480] text-white font-bold py-3 px-7 rounded-xl transition shadow-md flex justify-center items-center gap-2 text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Verify Completion &amp; Close Request
                        </button>
                    </div>
                </form>
            </div>

            <!-- Lightbox Modal Popup -->
            <div x-show="lightboxOpen" 
                 x-cloak 
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/80 backdrop-blur-xs"
                 @keydown.escape.window="lightboxOpen = false"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                
                <div class="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center bg-zinc-900 rounded-2xl overflow-hidden shadow-2xl border border-zinc-700" 
                     @click.outside="lightboxOpen = false">
                    <!-- Header Bar -->
                    <div class="w-full flex items-center justify-between py-3 px-5 bg-zinc-800 text-white border-b border-zinc-700">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-200" x-text="lightboxTitle"></span>
                        <button type="button" @click="lightboxOpen = false" class="p-1.5 text-gray-400 hover:text-white hover:bg-zinc-700 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <!-- Adaptive Image Area -->
                    <div class="w-full p-4 flex items-center justify-center overflow-auto max-h-[80vh] bg-black/50">
                        <img :src="lightboxImg" alt="Enlarged Photo" class="max-h-[75vh] w-auto max-w-full object-contain rounded-lg shadow-lg">
                    </div>
                </div>
            </div>
        </div>

    @elseif($serviceRequest->current_status === 'Rejected')
        @php
            $rejectionHistory = $serviceRequest->histories->where('current_status', 'Rejected')->last();
        @endphp
        <div class="bg-red-50/90 dark:bg-red-950/40 border-2 border-red-300 dark:border-red-800 rounded-2xl p-6 sm:p-7 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-red-100 dark:bg-red-900/70 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0 mt-0.5 border border-red-200 dark:border-red-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-base sm:text-lg font-black text-red-900 dark:text-red-300 uppercase tracking-tight mb-1">
                        This Requisition Was Disapproved / Rejected
                    </h2>
                    <p class="text-xs text-red-700 dark:text-red-400 mb-3">
                        Rejection logged on {{ $rejectionHistory ? \Carbon\Carbon::parse($rejectionHistory->updated_at)->format('F d, Y \a\t h:i A') : 'N/A' }} 
                        @if($rejectionHistory && $rejectionHistory->updatedBy)
                            by {{ $rejectionHistory->updatedBy->first_name ?? '' }} {{ $rejectionHistory->updatedBy->last_name ?? '' }}
                        @endif
                    </p>
                    
                    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-red-200 dark:border-red-800/80 shadow-2xs">
                        <div class="text-[11px] font-extrabold text-red-600 dark:text-red-400 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <span>Logged Reason for Rejection / Recommendation:</span>
                        </div>
                        <p class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white leading-relaxed whitespace-pre-line">
                            {{ $rejectionHistory && $rejectionHistory->remarks ? $rejectionHistory->remarks : 'No specific reason entered.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Status Timeline & History Card -->
    <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-6 shadow-sm space-y-4"
         x-data="{ editTimeline: false }">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-800 gap-3 flex-wrap">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Status Timeline &amp; History Audit Log
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Detailed log of all state transitions, scheduling proposals, BOM submissions, and approvals.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-zinc-800 px-3 py-1 rounded-full">
                    {{ $serviceRequest->histories->count() }} Updates
                </span>
                <button type="button" 
                        @click="editTimeline = !editTimeline" 
                        class="px-2.5 py-1 text-xs font-bold rounded-lg border transition cursor-pointer flex items-center gap-1.5 shadow-2xs"
                        :class="editTimeline ? 'bg-[#0033a0] text-white border-[#0033a0]' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span x-text="editTimeline ? 'Done' : 'Edit Date/Time'"></span>
                </button>
            </div>
        </div>

        <div class="relative pl-5 space-y-4 before:absolute before:left-1.5 before:top-2 before:bottom-2 before:w-px before:bg-gray-200 dark:before:bg-zinc-700/80">
            @forelse($serviceRequest->histories->sortBy([['updated_at', 'asc'], ['history_id', 'asc']]) as $history)
                <div class="relative group" x-data="{ newDate: '{{ $history->updated_at ? \Carbon\Carbon::parse($history->updated_at)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}', saving: false }">
                    <!-- Bullet Indicator: sleek dot -->
                    <div class="absolute -left-[19px] top-1.5 w-2.5 h-2.5 rounded-full {{ $history->bullet_color_class }} ring-4 ring-white dark:ring-[#1c1c1e] shadow-2xs"></div>

                    <div class="space-y-1">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $history->badge_color_class }}">
                                {{ $history->action_title }}
                            </span>
                            <div class="flex items-center gap-2">
                                <span x-show="!editTimeline" class="text-[10.5px] text-gray-400 dark:text-gray-500 font-medium tabular-nums">
                                    {{ $history->updated_at ? \Carbon\Carbon::parse($history->updated_at)->format('M d, g:i A') : 'N/A' }}
                                </span>
                                <form x-show="editTimeline" 
                                      x-cloak
                                      method="POST" 
                                      action="{{ route('admin.requests.history.update-time', [$serviceRequest->request_id, $history->history_id]) }}" 
                                      class="inline-flex items-center gap-1.5"
                                      @submit="saving = true">
                                    @csrf
                                    <input type="datetime-local" 
                                           name="updated_at" 
                                           x-model="newDate" 
                                           required
                                           class="text-[11px] px-2 py-0.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-gray-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                                    <button type="submit" 
                                            :disabled="saving"
                                            class="px-2 py-0.5 bg-[#0033a0] hover:bg-[#002480] text-white text-[11px] font-bold rounded-lg transition shadow-2xs cursor-pointer disabled:opacity-60">
                                        <span x-text="saving ? '...' : 'Save'">Save</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if($history->remarks)
                            <p class="text-xs text-slate-700 dark:text-gray-300 bg-slate-50 dark:bg-zinc-800/60 rounded-lg px-3 py-1.5 border-l-2 border-slate-300 dark:border-zinc-600 leading-relaxed font-normal">
                                {{ $history->display_remarks ?? $history->remarks }}
                            </p>
                        @endif

                        @if($history->updatedBy)
                            <div class="text-[10px] text-gray-400 dark:text-gray-500 flex items-center gap-1.5 pt-0.5">
                                <span>by <span class="font-semibold text-slate-600 dark:text-gray-300">{{ $history->updatedBy->first_name ?? '' }} {{ $history->updatedBy->last_name ?? '' }}</span></span>
                                @if($history->updatedBy->role)
                                    <span class="uppercase text-[8.5px] px-1 py-0.2 rounded font-bold {{ $history->updatedBy->role === 'admin' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300' : ($history->updatedBy->role === 'worker' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'bg-gray-100 text-gray-600 dark:bg-zinc-800 dark:text-gray-400') }}">
                                        {{ $history->updatedBy->role }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-400 italic">No history records found.</p>
            @endforelse
        </div>
    </div>

    <!-- Per-Request Messaging Channel -->
    @include('partials.request-messages', ['serviceRequest' => $serviceRequest])
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('categorySelect');
    if (!categorySelect) return;

    function updateRecommendations() {
        const selectedOpt = categorySelect.options[categorySelect.selectedIndex];
        const catName = selectedOpt ? (selectedOpt.getAttribute('data-name') || '') : '';
        const words = (catName.match(/\w+/g) || []).filter(w => w.length > 3);

        document.querySelectorAll('.worker-option').forEach(option => {
            const teamName = option.getAttribute('data-team') || '';
            const checkbox = option.querySelector('.worker-checkbox');
            const badge = option.querySelector('.recommended-badge');

            let isRec = false;
            for (const word of words) {
                if (teamName.includes(word)) {
                    isRec = true;
                    break;
                }
            }

            if (isRec) {
                option.classList.add('bg-blue-50/80', 'border-blue-200');
                badge.classList.remove('hidden');
                checkbox.checked = true;
            } else {
                option.classList.remove('bg-blue-50/80', 'border-blue-200');
                badge.classList.add('hidden');
                checkbox.checked = false;
            }
        });
    }

    categorySelect.addEventListener('change', updateRecommendations);
});

function adminOverrideHandler(modalType) {
    return {
        modalType: modalType,
        completionType: 'Full Repair',
        natureOfWork: '',
        recommendation: '',
        saving: false,
        proofFile: '',
        proofSize: '',
        proofPreviewUrl: '',
        capturedFile: null,
        cameraActive: false,
        cameraStream: null,
        cameraError: '',
        facingMode: 'environment',

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
                    this.cameraStream.getTracks().forEach(t => t.stop());
                } catch (e) {}
                this.cameraStream = null;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.cameraError = 'Live camera is not supported in this browser. Please use "Choose File" to select a photo.';
                return;
            }

            let stream = null;
            const targetFacing = this.facingMode || 'environment';

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
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: targetFacing },
                        audio: false
                    });
                } catch (err2) {
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({
                            video: true,
                            audio: false
                        });
                    } catch (err3) {
                        this.cameraError = 'Could not access camera (' + (err3.message || 'Permission denied') + '). Please ensure camera access is allowed in browser settings, or choose a file.';
                        return;
                    }
                }
            }

            if (stream) {
                this.cameraStream = stream;
                await this.$nextTick();
                const video = this.$refs.cameraVideo;
                if (video) {
                    video.srcObject = stream;
                    video.muted = true;
                    video.setAttribute('playsinline', 'true');
                    video.setAttribute('autoplay', 'true');
                    video.onloadedmetadata = async () => {
                        try {
                            await video.play();
                        } catch (err) {}
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
            const video = this.$refs.cameraVideo;
            if (!video || !this.cameraStream) return;

            const vw = video.videoWidth || 640;
            const vh = video.videoHeight || 480;

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
                const prefix = this.modalType === 'start' ? 'proof_before_' : 'proof_after_';
                const filename = prefix + Date.now() + '.jpg';
                const file = new File([blob], filename, { type: 'image/jpeg' });
                
                this.handleFile(file);

                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.files = dt.files;
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
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
            if (this.$refs.mobileCameraInput) {
                this.$refs.mobileCameraInput.value = '';
            }
        },

        closeModal() {
            this.closeCamera();
            if (this.modalType === 'start') {
                this.$dispatch('close-start-modal');
            } else {
                this.$dispatch('close-complete-modal');
            }
        },

        validateAndSubmit(e) {
            const fileInput = this.$refs.fileInput;
            const file = (fileInput && fileInput.files && fileInput.files[0]) || this.capturedFile;
            if (!file) {
                e.preventDefault();
                alert(this.modalType === 'start' ? 'A Before-Work photo is required to start this task.' : 'An After-Work / accomplishment photo is required to complete this task.');
                return;
            }
            this.saving = true;
        }
    };
}
</script>
@endpush
@endsection
