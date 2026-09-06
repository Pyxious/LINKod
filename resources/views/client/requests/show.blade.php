@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full flex flex-col font-sans min-h-[calc(100vh-64px)]">
    
    <!-- Hero Header Banner (Full-Width Shaded Section) -->
    <section class="w-full bg-[#fffde7] dark:bg-[#18181b] py-8 sm:py-10 px-4 sm:px-6 lg:px-8 border-b border-gray-200/80 dark:border-zinc-800">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">

            <div>
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wider">
                    <a href="{{ route('home') }}" class="hover:text-[#0033a0]">Home</a>
                    <span>/</span>
                    <a href="{{ route('client.requests.index') }}" class="hover:text-[#0033a0]">Track Requests</a>
                    <span>/</span>
                    <span class="text-[#0033a0] dark:text-blue-400 font-bold">Request #{{ str_pad($request->request_id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>

                <div class="flex items-center gap-3 mb-2 flex-wrap">
                    <span class="px-3 py-1 bg-[#0033a0] text-white text-[11px] font-extrabold uppercase tracking-wider rounded-full shadow-sm">
                        Requisition #{{ str_pad($request->request_id, 4, '0', STR_PAD_LEFT) }}
                    </span>
                    <span id="requestStatusBadge" data-request-status-badge class="px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider rounded-full border
                        @if($request->current_status === 'Completed')
                            bg-emerald-100 text-emerald-700 border-emerald-300
                        @elseif($request->current_status === 'Awaiting Verification of Bill of Materials')
                            bg-amber-100 text-amber-800 border-amber-300
                        @elseif($request->current_status === 'BOM Verified (Awaiting Client Approval)')
                            bg-indigo-100 text-indigo-800 border-indigo-300
                        @elseif(in_array($request->current_status, ['In Progress', 'Pending Verification']))
                            bg-blue-100 text-blue-700 border-blue-300
                        @elseif(in_array($request->current_status, ['Cancelled', 'Rejected']))
                            bg-rose-100 text-rose-700 border-rose-300
                        @else
                            bg-amber-100 text-amber-700 border-amber-300
                        @endif">
                        {{ $request->current_status }}
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                    {{ $request->title }}
                </h1>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 shrink-0">
                @if($request->current_status === 'Completed')
                    @if(!$request->evaluation)
                        <a href="{{ route('client.evaluations.create', $request->request_id) }}" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-full transition shadow-md inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            <span>Rate &amp; Evaluate Service</span>
                        </a>
                    @else
                        <div x-data="{ evalModalOpen: false }">
                            <!-- Service Evaluated Button (No Emoji) -->
                            <button type="button" 
                                    @click="evalModalOpen = true" 
                                    class="px-5 py-2.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-full text-xs font-bold inline-flex items-center gap-2 shadow-2xs transition cursor-pointer">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Service Evaluated ({{ $request->evaluation->rating }}/5★)</span>
                            </button>

                            <!-- View Service Rating & Feedback Modal (No Emoji) -->
                            <div x-show="evalModalOpen" 
                                 x-cloak 
                                 class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/75 backdrop-blur-xs"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 @click.outside="evalModalOpen = false" 
                                 @keydown.escape.window="evalModalOpen = false">
                                
                                <div class="relative w-full max-w-lg bg-white dark:bg-[#1c1c1e] rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-800 overflow-hidden transform transition-all p-6 sm:p-7 space-y-5"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     @click.stop>
                                    
                                    <!-- Modal Header -->
                                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-zinc-800">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/80 flex items-center justify-center shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                            <div>
                                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">
                                                    Your Service Rating &amp; Feedback
                                                </h3>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    Submitted {{ ($request->evaluation->show_name ?? true) ? 'under your name' : 'anonymously' }} on {{ $request->evaluation->rated_at ? $request->evaluation->rated_at->format('M d, Y h:i A') : 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                        <button type="button" @click="evalModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg transition cursor-pointer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    <!-- Overall Score Box (No Emoji) -->
                                    <div class="bg-slate-50 dark:bg-zinc-800/60 p-4 rounded-xl border border-gray-200 dark:border-zinc-700 flex items-center justify-between gap-4">
                                        <div>
                                            <div class="text-[11px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Overall Rating</div>
                                            <div class="text-base font-black text-slate-900 dark:text-white mt-0.5">
                                                {{ match((int)$request->evaluation->rating) {
                                                    5 => '5 / 5 — Very Satisfied',
                                                    4 => '4 / 5 — Satisfied',
                                                    3 => '3 / 5 — Neutral',
                                                    2 => '2 / 5 — Dissatisfied',
                                                    default => '1 / 5 — Very Dissatisfied'
                                                } }}
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1 text-amber-500 shrink-0">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-5 h-5 {{ $i <= $request->evaluation->rating ? 'text-amber-400 fill-amber-400' : 'text-gray-300 dark:text-zinc-600 fill-transparent' }}" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                    </div>

                                    <!-- Written Feedback / Suggestions -->
                                    <div class="space-y-1.5">
                                        <div class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                            <span>Your Feedback &amp; Suggestions</span>
                                        </div>
                                        <div class="p-3.5 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-gray-200 dark:border-zinc-700 text-xs sm:text-sm text-slate-800 dark:text-gray-200 italic leading-relaxed">
                                            "{{ $request->evaluation->feedback_text ?: 'No written feedback was provided.' }}"
                                        </div>
                                    </div>

                                    <!-- Detailed Function Breakdown (No Emoji) -->
                                    @php
                                        $clientFuncRatings = $request->evaluation->function_ratings;
                                        $clientFuncLabels = [
                                            'quality'      => 'Quality of Service',
                                            'attitude'     => 'Attitude',
                                            'safety'       => 'Safety Precaution',
                                            'time'         => 'Time Bound',
                                            'housekeeping' => 'Housekeeping',
                                        ];
                                    @endphp
                                    @if($clientFuncRatings)
                                        <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-zinc-800">
                                            <div class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Detailed Function Breakdown
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                @foreach($clientFuncLabels as $k => $lbl)
                                                    @php $sVal = (int)($clientFuncRatings[$k] ?? $request->evaluation->rating); @endphp
                                                    <div class="p-2.5 bg-slate-50 dark:bg-zinc-800/60 border border-gray-200 dark:border-zinc-700 rounded-lg flex items-center justify-between text-xs">
                                                        <span class="font-medium text-slate-700 dark:text-gray-300">{{ $lbl }}</span>
                                                        <span class="font-black text-[#0033a0] dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 px-2 py-0.5 rounded text-[11px]">
                                                            {{ $sVal }} / 5★
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Modal Footer -->
                                    <div class="flex justify-end pt-3 border-t border-gray-100 dark:border-zinc-800">
                                        <button type="button" @click="evalModalOpen = false" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl transition cursor-pointer">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                @if(in_array($request->current_status, ['Submitted', 'Pending']))
                    <div x-data="{ cancelModalOpen: false, cancelling: false }">
                        <!-- Trigger Button -->
                        <button type="button" 
                                @click="cancelModalOpen = true" 
                                class="px-5 sm:px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-full transition shadow-md inline-flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Cancel Request</span>
                        </button>

                        <!-- Cancel Confirmation Modal Popup -->
                        <div x-show="cancelModalOpen" 
                             x-cloak 
                             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/75 backdrop-blur-xs"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             @click.outside="cancelModalOpen = false" 
                             @keydown.escape.window="cancelModalOpen = false">
                            
                            <div class="relative w-full max-w-md bg-white dark:bg-[#1c1c1e] rounded-2xl shadow-2xl border border-gray-200 dark:border-zinc-800 overflow-hidden transform transition-all p-6 sm:p-7 space-y-5"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95">
                                
                                <!-- Icon & Header -->
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/80 flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-black text-slate-900 dark:text-white leading-tight">Cancel Service Request?</h3>
                                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-0.5">Requisition #{{ str_pad($request->request_id, 4, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>

                                <!-- Request Summary Box -->
                                <div class="bg-gray-50/80 dark:bg-zinc-800/50 rounded-xl p-4 border border-gray-200 dark:border-zinc-700/80 space-y-2 text-xs">
                                    <div class="flex justify-between items-start gap-2">
                                        <span class="text-gray-500 dark:text-gray-400 font-medium">Request Title:</span>
                                        <span class="font-bold text-slate-900 dark:text-white text-right truncate max-w-[200px]">{{ $request->title }}</span>
                                    </div>
                                    <div class="flex justify-between items-center gap-2">
                                        <span class="text-gray-500 dark:text-gray-400 font-medium">Category:</span>
                                        <span class="font-bold text-slate-900 dark:text-white text-right">{{ $request->category->category_name ?? 'General' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center gap-2">
                                        <span class="text-gray-500 dark:text-gray-400 font-medium">Location:</span>
                                        <span class="font-bold text-slate-900 dark:text-white text-right truncate max-w-[200px]">{{ $request->campus ?? 'BU Main' }} — {{ $request->location }}</span>
                                    </div>
                                </div>

                                <!-- Warning Message -->
                                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                                    Are you sure you want to cancel this request? Once cancelled, this service request will be withdrawn and closed from GSO maintenance scheduling.
                                </p>

                                <!-- Form Actions -->
                                <form action="{{ route('client.requests.cancel', $request->request_id) }}" method="POST" @submit="cancelling = true">
                                    @csrf
                                    <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5 pt-2">
                                        <button type="button" 
                                                @click="cancelModalOpen = false" 
                                                :disabled="cancelling"
                                                class="w-full sm:w-auto px-5 py-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-700 transition cursor-pointer">
                                            Keep Request
                                        </button>
                                        <button type="submit" 
                                                :disabled="cancelling"
                                                class="w-full sm:w-auto px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition shadow-md inline-flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60">
                                            <svg x-show="cancelling" x-cloak class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <span x-text="cancelling ? 'Cancelling...' : 'Yes, Cancel Request'">Yes, Cancel Request</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 space-y-6">
        
        @php
            $latestRejection = $request->histories->where('current_status', 'Rejected')->last();
        @endphp

        @if($request->current_status === 'Rejected')
            <!-- Rejection Notice & Recommendation Banner -->
            <div class="bg-red-50/90 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/70 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0 border border-red-200 dark:border-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-base font-bold text-red-900 dark:text-red-300 uppercase tracking-tight mb-1">
                            Service Request Disapproved / Rejected
                        </h2>
                        <p class="text-xs text-red-700 dark:text-red-400 mb-3">
                            This requisition cannot be processed by General Services Office (GSO). Please review the administrator's feedback below:
                        </p>
                        
                        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-red-200 dark:border-red-800/80 shadow-2xs">
                            <div class="text-[10px] font-extrabold text-red-600 dark:text-red-400 uppercase tracking-wider mb-1">
                                Admin Reason / Recommendation:
                            </div>
                            <p class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white leading-relaxed whitespace-pre-line">
                                {{ $latestRejection && $latestRejection->remarks ? $latestRejection->remarks : 'No specific reason provided by the administrator.' }}
                            </p>
                        </div>

                        <div class="mt-4 flex items-center gap-3">
                            <a href="{{ route('client.requests.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Submit a New Requisition
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left Column: Details & BOM -->
            <div class="lg:col-span-2 space-y-6">

                @if($request->scheduled_date)
                    <!-- Scheduled Visit Card -->
                    <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border-2 {{ $request->schedule_status === 'pending_client_approval' ? 'border-amber-400 dark:border-amber-600 bg-amber-50/20' : ($request->schedule_status === 'approved' ? 'border-emerald-400 dark:border-emerald-600' : 'border-gray-200 dark:border-zinc-800') }} p-6 shadow-sm space-y-4"
                         x-data="{ rescheduleModal: false, decliningReason: '' }">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-gray-100 dark:border-zinc-800">
                            <div class="flex items-center gap-2.5">
                                <span class="p-2 rounded-xl {{ $request->schedule_status === 'pending_client_approval' ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300' : ($request->schedule_status === 'approved' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-gray-300') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </span>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                        Maintenance Visit Schedule
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Target date for GSO staff on-site inspection and service.
                                    </p>
                                </div>
                            </div>

                            <div>
                                @if($request->schedule_status === 'approved')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 text-xs font-bold rounded-full">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        Schedule Confirmed
                                    </span>
                                @elseif($request->schedule_status === 'pending_client_approval')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-800 text-xs font-bold rounded-full animate-pulse">
                                        Action Required: Please Confirm
                                    </span>
                                @elseif($request->schedule_status === 'declined')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-800 text-xs font-bold rounded-full">
                                        Reschedule Requested
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-slate-50 dark:bg-zinc-800/60 rounded-xl border border-gray-200 dark:border-zinc-700 gap-4">
                            <div class="space-y-1">
                                <div class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Proposed Schedule
                                </div>
                                <div class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-[#0033a0] dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>{{ $request->scheduled_date->format('F d, Y') }} ({{ $request->scheduled_date->format('l') }})</span>
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-[#0033a0] text-white shadow-2xs">
                                        {{ match($request->scheduled_time_window) {
                                            'AM' => 'Morning (8:00 AM - 12:00 PM)',
                                            'PM' => 'Afternoon (1:00 PM - 5:00 PM)',
                                            'AM-PM' => 'Whole Day (8:00 AM - 5:00 PM)',
                                            default => $request->scheduled_time_window ?? 'Whole Day'
                                        } }}
                                    </span>
                                </div>
                            </div>

                            @if($request->schedule_status === 'pending_client_approval')
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" @click="rescheduleModal = true" class="px-4 py-2 bg-white dark:bg-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-600 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-zinc-600 rounded-xl text-xs font-bold transition shadow-2xs">
                                        Decline / Reschedule
                                    </button>
                                    <form action="{{ route('client.requests.schedule.approve', $request->request_id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Confirm Schedule
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        @if($request->schedule_status === 'declined')
                            <div class="p-3 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 rounded-xl text-xs text-rose-700 dark:text-rose-300">
                                <strong>Reschedule request sent:</strong> "{{ $request->schedule_decline_reason }}". GSO Admin will propose an alternative date shortly.
                            </div>
                        @endif

                        <!-- Reschedule Modal -->
                        <div x-show="rescheduleModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs" @keydown.escape.window="rescheduleModal = false">
                            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.outside="rescheduleModal = false">
                                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-800">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Request Alternative Schedule</h4>
                                    <button type="button" @click="rescheduleModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                                </div>
                                <form action="{{ route('client.requests.schedule.decline', $request->request_id) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5 uppercase tracking-wider">
                                            Reason &amp; Preferred Dates <span class="text-red-500">*</span>
                                        </label>
                                        <textarea name="decline_reason" x-model="decliningReason" rows="3" required placeholder="Please state why the proposed date is not suitable and suggest dates/times when your office or facility will be available..." class="w-full p-3 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0]"></textarea>
                                    </div>
                                    <div class="flex justify-end gap-2 pt-2">
                                        <button type="button" @click="rescheduleModal = false" class="px-4 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl">Cancel</button>
                                        <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs">Submit Reschedule Request</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Request Specifications Card -->
                <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm">
                    @php
                        $isManpower = $request->is_manpower;
                        $m = $request->manpower_details ?? [];
                    @endphp

                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3 mb-5">
                        <h2 class="text-base font-bold text-[#0033a0] dark:text-blue-400 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Request Details &amp; Specifications</span>
                        </h2>
                        @if($isManpower)
                            <span class="px-2.5 py-0.5 bg-blue-50 text-[#0033a0] dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800 rounded-full text-xs font-bold">
                                Manpower Services
                            </span>
                        @endif
                    </div>

                    @if($isManpower)
                        <!-- Top Details Grid for Manpower: Category, Campus, Location -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                            <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Service Category</div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $request->category->category_name ?? 'Manpower' }}</div>
                            </div>

                            <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Campus</div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $request->campus ?? 'BU Main' }}</div>
                            </div>

                            <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Office / Location</div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $request->location }}</div>
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
                                    {{ $m['activity_title'] ?: $request->title }}
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
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Description</div>
                            <div class="bg-slate-50 dark:bg-zinc-800/60 p-4 rounded-xl text-slate-800 dark:text-gray-200 text-xs sm:text-sm leading-relaxed border border-gray-100 dark:border-zinc-700 whitespace-pre-line">
                                {{ $request->display_description ?: 'No detailed description provided.' }}
                            </div>
                        </div>

                        <!-- Details Grid (Non-Manpower) -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Category</div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $request->category->category_name ?? 'General' }}</div>
                            </div>

                            <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Campus</div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $request->campus ?? 'BU Main' }}</div>
                            </div>

                            <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Office / Location</div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $request->location }}</div>
                            </div>
                        </div>
                    @endif

                    <!-- Supporting Attachment -->
                    @if($request->attachment)
                        @php
                            $isImg = Str::endsWith(strtolower($request->attachment), ['.jpg', '.jpeg', '.png', '.webp']);
                            $attachUrl = Storage::url($request->attachment);
                        @endphp
                        <div class="mt-6 border-t border-gray-100 dark:border-zinc-800 pt-5" x-data="{ attModal: false }">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Supporting Attachment</div>
                            <div @click="attModal = true" class="inline-flex items-center gap-3 p-3 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl hover:border-[#0033a0] transition group cursor-pointer">
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
                    @endif
                </div>

                <!-- Bill of Materials (BOM) Card -->
                <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Bill of Materials (BOM)
                        </h2>
                        @if($request->project && $request->project->billOfMaterials->count() > 0)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200 uppercase">
                                {{ $request->project->billOfMaterials->count() }} Item(s)
                            </span>
                        @endif
                    </div>

                    @if($request->project && $request->project->billOfMaterials->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-zinc-800 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                                        <th class="py-2.5 px-3">Material Item</th>
                                        <th class="py-2.5 px-3 text-center">Unit</th>
                                        <th class="py-2.5 px-3 text-center">Qty</th>
                                        <th class="py-2.5 px-3 text-right">Unit Cost</th>
                                        <th class="py-2.5 px-3 text-right">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-xs">
                                    @php $grandTotal = 0; @endphp
                                    @foreach($request->project->billOfMaterials as $bom)
                                        @php 
                                            $unit = $bom->material->unit_of_measurement ?? 'pcs';
                                            $unitCost = $bom->material->unit_cost ?? 0;
                                            $itemTotal = $bom->total_cost ?: ($bom->qty * $unitCost);
                                            $isApproved = !is_null($bom->date_approved);
                                            $grandTotal += $itemTotal;
                                        @endphp
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/40 transition">
                                            <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">
                                                <div class="flex items-center gap-2">
                                                    <span>{{ $bom->material->material_name ?? 'Material Item' }}</span>
                                                    @if(!$isApproved)
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 uppercase">Pending Pricing</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="py-3 px-3 text-center text-gray-500 font-semibold">
                                                {{ $unit }}
                                            </td>
                                            <td class="py-3 px-3 text-center font-bold text-slate-800 dark:text-gray-200">
                                                {{ rtrim(rtrim(number_format($bom->qty, 2), '0'), '.') }}
                                            </td>
                                            <td class="py-3 px-3 text-right text-gray-500 font-medium">
                                                @if($unitCost > 0)
                                                    ₱{{ number_format($unitCost, 2) }}
                                                @else
                                                    <span class="text-gray-400 italic">--</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                                                @if($itemTotal > 0)
                                                    ₱{{ number_format($itemTotal, 2) }}
                                                @else
                                                    <span class="text-gray-400 italic">--</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Total Estimated Cost Summary -->
                        <div class="mt-4 pt-3 border-t border-gray-200 dark:border-zinc-800 flex items-center justify-between bg-gray-50 dark:bg-zinc-800/50 p-4 rounded-xl">
                            <span class="text-xs font-bold text-slate-700 dark:text-gray-300">Total Estimated Materials Cost:</span>
                            <span class="text-base font-black text-[#0033a0] dark:text-blue-400">₱{{ number_format($grandTotal, 2) }}</span>
                        </div>

                        @if($request->bom_status === 'awaiting_client' || $request->current_status === 'BOM Verified (Awaiting Client Approval)')
                            <div class="mt-4 p-5 bg-indigo-50/80 dark:bg-indigo-950/30 border-2 border-indigo-200 dark:border-indigo-800 rounded-2xl space-y-3"
                                 x-data="{ declineBomModal: false }">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-900 dark:text-indigo-200">
                                                Action Required: Bill of Materials Approval
                                            </h4>
                                        </div>
                                        <p class="text-xs text-indigo-700 dark:text-indigo-300 mt-1">
                                            GSO Admin has verified and priced the required materials. Please review and approve to proceed with procurement and maintenance work.
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" @click="declineBomModal = true" class="px-4 py-2 bg-white dark:bg-zinc-800 hover:bg-gray-50 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-bold transition shadow-2xs">
                                            Decline BOM
                                        </button>
                                        <form action="{{ route('client.requests.bom.approve', $request->request_id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Approve BOM
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Decline BOM Modal -->
                                <div x-show="declineBomModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs" @keydown.escape.window="declineBomModal = false">
                                    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.outside="declineBomModal = false">
                                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-zinc-800">
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Decline Bill of Materials</h4>
                                            <button type="button" @click="declineBomModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                                        </div>
                                        <form action="{{ route('client.requests.bom.decline', $request->request_id) }}" method="POST" class="space-y-4">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5 uppercase tracking-wider">
                                                    Feedback / Note (Optional)
                                                </label>
                                                <textarea name="remarks" rows="3" placeholder="Provide details on why this BOM is declined or if alternative supplies are available on-site..." class="w-full p-3 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0]"></textarea>
                                            </div>
                                            <div class="flex justify-end gap-2 pt-2">
                                                <button type="button" @click="declineBomModal = false" class="px-4 py-2 bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl">Cancel</button>
                                                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs">Confirm Decline</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @else
                        <div class="p-6 bg-slate-50 dark:bg-zinc-800/30 rounded-xl text-center border border-gray-100 dark:border-zinc-800">
                            <p class="text-xs text-gray-400 italic">No Bill of Materials (BOM) required or requested yet for this job.</p>
                        </div>
                    @endif
                </div>

                <!-- Per-Request Messaging Channel (Under BOM, matching BOM width) -->
                @include('partials.request-messages', ['serviceRequest' => $request])

            </div>

            <!-- Right Column: Status Timeline Stepper -->
            <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-6 shadow-sm">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100 dark:border-zinc-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Status Timeline</span>
                    </h2>
                    <span class="text-[10.5px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                        {{ $request->histories->count() }} Updates
                    </span>
                </div>

                <div id="requestTimelineFeed" class="relative pl-5 space-y-4 before:absolute before:left-1.5 before:top-2 before:bottom-2 before:w-px before:bg-gray-200 dark:before:bg-zinc-700/80">
                    @forelse($request->histories as $history)
                        <div class="relative group">
                            <!-- Bullet Indicator: sleek dot -->
                            <div class="absolute -left-[19px] top-1.5 w-2.5 h-2.5 rounded-full {{ $history->bullet_color_class }} ring-4 ring-white dark:ring-[#1c1c1e] shadow-2xs"></div>

                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $history->badge_color_class }}">
                                        {{ $history->action_title }}
                                    </span>
                                    <span class="text-[10.5px] text-gray-400 dark:text-gray-500 font-medium tabular-nums">
                                        {{ \Carbon\Carbon::parse($history->updated_at)->format('M d, g:i A') }}
                                    </span>
                                </div>

                                @if($history->remarks)
                                    @php
                                        $displayRemarks = $history->remarks;
                                        if ($history->action_title === 'Client Rated Service') {
                                            $displayRemarks = $history->remarks;
                                        } elseif ($history->action_title === 'Acceptance' || $history->current_status === 'Pending Verification') {
                                            $displayRemarks = 'Work accomplished by the maintenance unit. Ready for final acceptance.';
                                        } elseif ($history->current_status === 'Completed' && !empty($history->remarks)) {
                                            $displayRemarks = 'Project completed and officially accepted.';
                                        }
                                    @endphp
                                    <p class="text-xs text-slate-700 dark:text-gray-300 bg-slate-50 dark:bg-zinc-800/60 rounded-lg px-3 py-1.5 border-l-2 border-slate-300 dark:border-zinc-600 leading-relaxed font-normal">
                                        {{ $displayRemarks }}
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
                        <p id="noHistoryText" class="text-xs text-gray-400 italic">No history records found.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const requestId = "{{ $request->request_id }}";
    if (requestId && window.supabaseClient) {
        // 1. Listen for request status updates
        window.supabaseClient
            .channel(`realtime-request-status-${requestId}`)
            .on(
                'postgres_changes',
                {
                    event: 'UPDATE',
                    schema: 'public',
                    table: 'request',
                    filter: `request_id=eq.${requestId}`
                },
                (payload) => {
                    const newStatus = payload.new?.current_status;
                    if (newStatus) {
                        const badge = document.getElementById('requestStatusBadge');
                        if (badge) {
                            badge.textContent = newStatus;
                            badge.className = 'px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider rounded-full border transition-all duration-300 ';
                            if (newStatus === 'Completed') {
                                badge.className += 'bg-emerald-100 text-emerald-700 border-emerald-300';
                            } else if (newStatus === 'In Progress' || newStatus === 'Pending Verification') {
                                badge.className += 'bg-blue-100 text-blue-700 border-blue-300';
                            } else if (newStatus === 'Cancelled' || newStatus === 'Rejected') {
                                badge.className += 'bg-amber-100 text-amber-700 border-amber-300';
                            } else {
                                badge.className += 'bg-amber-100 text-amber-700 border-amber-300';
                            }
                        }

                        if (window.LINKodRealtime) {
                            window.LINKodRealtime.showNotificationToast(
                                'Request Status Updated',
                                `Requisition #${requestId} is now "${newStatus}"`
                            );
                        }
                    }
                }
            )
            .on(
                'postgres_changes',
                {
                    event: 'INSERT',
                    schema: 'public',
                    table: 'request_history',
                    filter: `request_id=eq.${requestId}`
                },
                (payload) => {
                    const history = payload.new;
                    if (history) {
                        const timeline = document.getElementById('requestTimelineFeed');
                        const emptyMsg = document.getElementById('noHistoryText');
                        if (emptyMsg) emptyMsg.remove();

                        if (timeline) {
                            const dateStr = new Date().toLocaleString('en-US', {
                                month: 'short',
                                day: 'numeric',
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                            });

                            let actionTitle = history.current_status || 'Status Update';
                            const rem = (history.remarks || '').toLowerCase();
                            if (rem.includes('proposed visit schedule') || rem.includes('proposed schedule') || actionTitle === 'Schedule Set') {
                                actionTitle = 'Schedule Proposed';
                            } else if (rem.includes('requested rescheduling') || rem.includes('declined schedule') || rem.includes('refused schedule') || actionTitle === 'Schedule Refused') {
                                actionTitle = 'Schedule Declined';
                            } else if (rem.includes('confirmed visit schedule') || rem.includes('approved schedule') || actionTitle === 'Schedule Confirmed') {
                                actionTitle = 'Schedule Confirmed';
                            } else if (rem.includes('client approved bill of materials') || actionTitle === 'BOM Approved by Client') {
                                actionTitle = 'BOM Approved';
                            } else if (rem.includes('client declined bill of materials') || actionTitle === 'BOM Declined by Client') {
                                actionTitle = 'BOM Declined';
                            } else if (rem.includes('admin verified bill of materials') || actionTitle === 'BOM Verified (Awaiting Client Approval)') {
                                actionTitle = 'BOM Verified';
                            } else if (rem.includes('submitted bill of materials') || actionTitle === 'Awaiting Verification of Bill of Materials') {
                                actionTitle = 'BOM Submitted';
                            } else if (actionTitle === 'Pending Verification' || actionTitle === 'Completed (Pending Review)') {
                                actionTitle = 'Acceptance';
                            } else if (actionTitle === 'Submitted') {
                                actionTitle = 'Submitted';
                            } else if (actionTitle === 'Approved') {
                                actionTitle = 'Approved';
                            } else if (actionTitle === 'Rejected') {
                                actionTitle = 'Rejected';
                            }

                            const isRej = actionTitle.includes('Rejected') || actionTitle.includes('Cancelled') || actionTitle.includes('Declined');
                            const isComp = actionTitle.includes('Confirmed') || actionTitle.includes('Approved') || actionTitle.includes('Completed');
                            const isWarn = actionTitle.includes('Proposed') || actionTitle.includes('Submitted') || actionTitle.includes('Pending') || actionTitle.includes('On Hold');
                            const bulletBg = isRej ? 'bg-rose-500' : (isComp ? 'bg-emerald-500' : (isWarn ? 'bg-amber-500' : 'bg-blue-600'));
                            const badgeClass = isRej 
                                ? 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900' 
                                : (isComp ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900' : (isWarn ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900' : 'bg-blue-50 text-[#0038A8] border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-900'));

                            let displayRem = history.remarks || '';
                            if (actionTitle === 'Acceptance' || history.current_status === 'Pending Verification') {
                                displayRem = 'Work accomplished by the maintenance unit. Ready for final acceptance.';
                            } else if (history.current_status === 'Completed') {
                                displayRem = 'Project completed and officially accepted.';
                            }

                            const remarksHtml = displayRem 
                                ? `<p class="text-xs text-slate-700 dark:text-gray-300 bg-slate-50 dark:bg-zinc-800/60 rounded-lg px-3 py-1.5 border-l-2 border-slate-300 dark:border-zinc-600 leading-relaxed font-normal">${displayRem}</p>` 
                                : '';

                            const item = document.createElement('div');
                            item.className = 'relative group animate-fadeIn';
                            item.innerHTML = `
                                <div class="absolute -left-[19px] top-1.5 w-2.5 h-2.5 rounded-full ${bulletBg} ring-4 ring-white dark:ring-[#1c1c1e] shadow-2xs"></div>
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border ${badgeClass}">
                                            ${actionTitle}
                                        </span>
                                        <span class="text-[9px] font-bold uppercase bg-blue-100 dark:bg-blue-900 text-[#0033a0] dark:text-blue-300 px-1.5 py-0.5 rounded">Just now</span>
                                    </div>
                                    <div class="text-[10.5px] text-gray-400 dark:text-gray-500 font-medium tabular-nums">
                                        ${dateStr}
                                    </div>
                                    ${remarksHtml}
                                </div>
                            `;
                            timeline.appendChild(item);
                        }
                    }
                }
            )
            .subscribe();
    }
});
</script>
@endsection
