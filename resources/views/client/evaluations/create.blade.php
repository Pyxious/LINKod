@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full bg-[#edf4fb] dark:bg-[#111111] py-10 px-4 sm:px-6 lg:px-8 font-sans min-h-[calc(100vh-64px)] flex flex-col items-center">
    
    <div class="w-full max-w-4xl space-y-6">
        
        <!-- Header Banner Card -->
        <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-6 sm:px-8 py-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#e5e1b0] dark:border-zinc-800 pb-5 mb-5">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-[#0033a0] dark:bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                            Clientele Satisfaction Survey &amp; Feedback
                        </h1>
                        <p class="text-xs sm:text-sm text-[#0033a0] dark:text-blue-400 font-bold mt-0.5">
                            Bicol University — General Services Office (BU-GSO)
                        </p>
                    </div>
                </div>
                <div class="bg-[#0033a0] text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider shrink-0 text-center shadow-xs">
                    Requisition #{{ str_pad($serviceRequest->request_id, 4, '0', STR_PAD_LEFT) }}
                </div>
            </div>

            <!-- Intro Instructions Text -->
            <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed font-medium mb-6">
                This Feedback Form is an important tool for us to measure our performance to help us improve our services to our clients. Kindly fill out this form to assess the performance of our staff and to improve further the quality of our services. You may use this form to express your Praise, Recommendations, or Criticisms. Please let us know how we performed our services to you by checking the appropriate box opposite the respective rating scales.
            </p>

            <!-- Rating Scale Emote Legend Grid (Adaptive SVGs for Light & Dark Mode) -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700/80 rounded-xl p-4 shadow-sm">
                <div class="text-[11px] font-bold text-[#0033a0] dark:text-blue-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Rating Scales Legend</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <!-- 5: Very Satisfied -->
                    <div class="flex items-center gap-2.5 p-2 bg-slate-50 dark:bg-zinc-800/70 rounded-lg border border-gray-100 dark:border-zinc-700/80 shadow-2xs hover:border-emerald-300 dark:hover:border-emerald-500/50 transition">
                        <x-survey-mood-icon :score="5" class="w-8 h-8 shrink-0" />
                        <div>
                            <div class="text-xs font-black text-slate-900 dark:text-white">5</div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 font-bold leading-tight">Very Satisfied</div>
                        </div>
                    </div>

                    <!-- 4: Satisfied -->
                    <div class="flex items-center gap-2.5 p-2 bg-slate-50 dark:bg-zinc-800/70 rounded-lg border border-gray-100 dark:border-zinc-700/80 shadow-2xs hover:border-green-300 dark:hover:border-green-500/50 transition">
                        <x-survey-mood-icon :score="4" class="w-8 h-8 shrink-0" />
                        <div>
                            <div class="text-xs font-black text-slate-900 dark:text-white">4</div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 font-bold leading-tight">Satisfied</div>
                        </div>
                    </div>

                    <!-- 3: Neutral -->
                    <div class="flex items-center gap-2.5 p-2 bg-slate-50 dark:bg-zinc-800/70 rounded-lg border border-gray-100 dark:border-zinc-700/80 shadow-2xs hover:border-amber-300 dark:hover:border-amber-500/50 transition">
                        <x-survey-mood-icon :score="3" class="w-8 h-8 shrink-0" />
                        <div>
                            <div class="text-xs font-black text-slate-900 dark:text-white">3</div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 font-bold leading-tight">Neutral</div>
                        </div>
                    </div>

                    <!-- 2: Dissatisfied -->
                    <div class="flex items-center gap-2.5 p-2 bg-slate-50 dark:bg-zinc-800/70 rounded-lg border border-gray-100 dark:border-zinc-700/80 shadow-2xs hover:border-orange-300 dark:hover:border-orange-500/50 transition">
                        <x-survey-mood-icon :score="2" class="w-8 h-8 shrink-0" />
                        <div>
                            <div class="text-xs font-black text-slate-900 dark:text-white">2</div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 font-bold leading-tight">Dissatisfied</div>
                        </div>
                    </div>

                    <!-- 1: Very Dissatisfied -->
                    <div class="flex items-center gap-2.5 p-2 bg-slate-50 dark:bg-zinc-800/70 rounded-lg border border-gray-100 dark:border-zinc-700/80 shadow-2xs hover:border-rose-300 dark:hover:border-rose-500/50 transition">
                        <x-survey-mood-icon :score="1" class="w-8 h-8 shrink-0" />
                        <div>
                            <div class="text-xs font-black text-slate-900 dark:text-white">1</div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 font-bold leading-tight">Very Dissatisfied</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Job Completed Specification Details -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 pt-5 border-t border-[#e5e1b0] dark:border-zinc-800 text-xs">
                <div class="flex items-start gap-2.5">
                    <span class="p-1.5 rounded-lg bg-blue-50 dark:bg-zinc-800 text-[#0033a0] dark:text-blue-400 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <div>
                        <span class="font-bold text-gray-500 dark:text-gray-400 block mb-0.5">Services Completed:</span>
                        <span class="font-black text-slate-900 dark:text-white block">{{ $serviceRequest->title }}</span>
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="p-1.5 rounded-lg bg-blue-50 dark:bg-zinc-800 text-[#0033a0] dark:text-blue-400 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </span>
                    <div>
                        <span class="font-bold text-gray-500 dark:text-gray-400 block mb-0.5">GSO Service Team Leader:</span>
                        <span class="font-black text-[#0033a0] dark:text-blue-400 block">
                            {{ $serviceRequest->project->approvedBy->user->full_name ?? 'BU-GSO Team Leader' }}
                        </span>
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="p-1.5 rounded-lg bg-blue-50 dark:bg-zinc-800 text-[#0033a0] dark:text-blue-400 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                    <div>
                        <span class="font-bold text-gray-500 dark:text-gray-400 block mb-0.5">Date Completed:</span>
                        <span class="font-black text-slate-900 dark:text-white block">
                            {{ \Carbon\Carbon::parse($serviceRequest->latestHistory->updated_at ?? now())->format('F d, Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evaluation Form (Themed Light Blue Container Box) -->
        <form action="{{ route('client.evaluations.store', $serviceRequest->request_id) }}" method="POST" class="bg-[#eef5fc] dark:bg-[#181a20] rounded-2xl border-2 border-[#c2daf2] dark:border-zinc-800 p-6 sm:p-7 shadow-sm space-y-6">
            @csrf

            <!-- Functions & Emote Rating Scales Table -->
            <div>
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#0033a0] dark:bg-blue-400"></span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Evaluation Functions &amp; Service Criteria</h2>
                    </div>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">Please select a score for each function</span>
                </div>
                
                <div class="overflow-x-auto rounded-xl border border-blue-200/80 dark:border-zinc-700/80 bg-white dark:bg-zinc-900 shadow-xs">
                    <table class="w-full text-left border-collapse table-fixed min-w-[620px]">
                        <thead>
                            <!-- Clean White Bar with perfectly aligned Emotes -->
                            <tr class="bg-white dark:bg-zinc-800 text-slate-900 dark:text-white text-xs font-extrabold uppercase tracking-wider border-b-2 border-blue-200/80 dark:border-zinc-700">
                                <th class="py-3.5 px-5 text-left align-middle w-auto text-slate-900 dark:text-white font-black tracking-wide">
                                    FUNCTIONS
                                </th>
                                @php
                                    $scoreColumns = [
                                        5 => '5 - Very Satisfied',
                                        4 => '4 - Satisfied',
                                        3 => '3 - Neutral',
                                        2 => '2 - Dissatisfied',
                                        1 => '1 - Very Dissatisfied',
                                    ];
                                @endphp
                                @foreach($scoreColumns as $score => $scoreLabel)
                                    <th class="py-3 px-1 text-center align-top w-20 sm:w-28">
                                        <div class="flex flex-col items-center justify-start">
                                            <!-- Fixed-height container to lock all emotes on the exact same horizontal baseline -->
                                            <div class="h-8 flex items-center justify-center mb-1">
                                                <x-survey-mood-icon :score="$score" class="w-7 h-7" />
                                            </div>
                                            <span class="text-[10px] tracking-tight font-extrabold leading-tight text-slate-800 dark:text-gray-200 uppercase text-center block">
                                                {{ $scoreLabel }}
                                            </span>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-100 dark:divide-zinc-800 text-xs sm:text-sm bg-white dark:bg-zinc-900">
                            @php
                                $functions = [
                                    'quality' => [
                                        'label' => 'Quality of Service',
                                        'desc'  => 'Standard of workmanship, precision, and adherence to maintenance specs',
                                    ],
                                    'attitude' => [
                                        'label' => 'Attitude',
                                        'desc'  => 'Courtesy, professionalism, respectfulness, and communication',
                                    ],
                                    'safety' => [
                                        'label' => 'Safety Precaution Awareness',
                                        'desc'  => 'Observance of occupational safety practices and hazard prevention',
                                    ],
                                    'time' => [
                                        'label' => 'Time Bound',
                                        'desc'  => 'Prompt response, punctuality, and timely completion of target tasks',
                                    ],
                                    'housekeeping' => [
                                        'label' => 'Workplace Housekeeping',
                                        'desc'  => 'Site cleanliness, proper disposal of scrap/debris, and orderliness',
                                    ],
                                ];
                            @endphp

                            @foreach($functions as $key => $fn)
                                <tr class="hover:bg-blue-50/40 dark:hover:bg-zinc-800/40 transition">
                                    <!-- Clean Section Title & Description (No leading icon) -->
                                    <td class="py-4 px-5 text-left align-middle">
                                        <div class="flex items-center gap-1">
                                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $fn['label'] }}</span>
                                            <span class="text-red-500 font-bold">*</span>
                                        </div>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 font-normal leading-tight mt-0.5">
                                            {{ $fn['desc'] }}
                                        </p>
                                        @error('ratings.'.$key)
                                            <p class="text-xs text-red-500 font-normal mt-1">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <!-- Radio Option Columns aligned precisely under each emote -->
                                    @for($score = 5; $score >= 1; $score--)
                                        <td class="py-4 px-1 text-center align-middle w-20 sm:w-28">
                                            <label class="cursor-pointer inline-flex items-center justify-center p-2 rounded-xl hover:bg-blue-50 dark:hover:bg-zinc-800 transition">
                                                <input type="radio" 
                                                       name="ratings[{{ $key }}]" 
                                                       value="{{ $score }}" 
                                                       class="w-5 h-5 text-[#0033a0] dark:text-blue-500 border-gray-300 dark:border-zinc-600 dark:bg-zinc-900 focus:ring-[#0033a0] dark:focus:ring-blue-500 cursor-pointer transition"
                                                       {{ (old('ratings.'.$key) == $score) ? 'checked' : '' }}
                                                       required>
                                            </label>
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Suggestions / Recommendations Text Area (Feedback) -->
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 sm:p-5 border border-blue-200/80 dark:border-zinc-700/80 shadow-2xs">
                <label class="flex items-center gap-2 text-xs font-bold text-[#0033a0] dark:text-blue-400 uppercase tracking-wider mb-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <span>Suggestions / Recommendations &amp; Feedback</span>
                </label>
                <textarea name="feedback_text" 
                          rows="4" 
                          placeholder="Express your Praise, Recommendations, or Criticisms here to help BU-GSO continuously improve..." 
                          class="w-full px-4 py-3 bg-[#f8fafc] dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs sm:text-sm text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0] dark:focus:border-blue-500 focus:ring-1 focus:ring-[#0033a0] dark:focus:ring-blue-500 transition"></textarea>
            </div>

            <!-- Rater Name Visibility Preference (Optional Name) -->
            @php
                $clientUserName = auth()->user()->full_name ?? '';
            @endphp
            <div class="p-4 bg-white dark:bg-zinc-900 rounded-xl border border-blue-200/80 dark:border-zinc-700/80 flex items-start sm:items-center justify-between gap-4 shadow-2xs">
                <div class="flex items-center gap-3">
                    <input type="checkbox" 
                           id="show_name" 
                           name="show_name" 
                           value="1" 
                           class="w-5 h-5 text-[#0033a0] dark:text-blue-500 rounded border-gray-300 dark:border-zinc-600 dark:bg-zinc-900 focus:ring-[#0033a0] dark:focus:ring-blue-500 cursor-pointer shrink-0"
                           {{ old('show_name', '1') ? 'checked' : '' }}>
                    <label for="show_name" class="cursor-pointer select-none">
                        <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Include my name as Rater</span>
                            <span class="text-xs font-normal text-gray-500 dark:text-gray-400">({{ $clientUserName ?: 'Your Name' }})</span>
                        </span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 block mt-0.5">
                            Name is optional. Uncheck this box if you prefer to submit this evaluation anonymously.
                        </span>
                    </label>
                </div>
                <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider bg-blue-100 dark:bg-blue-950 text-[#0033a0] dark:text-blue-300 rounded-full shrink-0">
                    Optional
                </span>
            </div>

            <!-- Bottom Sign-off & Submit Button -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-blue-200/80 dark:border-zinc-800">
                <div class="text-sm font-black text-[#ea580c] dark:text-orange-400 italic flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>We are happy to serve!</span>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('client.requests.show', $serviceRequest->request_id) }}" class="px-5 py-2.5 bg-white dark:bg-zinc-800 hover:bg-gray-100 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-300 rounded-full text-xs font-bold transition border border-gray-300 dark:border-zinc-700 shadow-2xs">
                        Back to Request
                    </a>
                    <button type="submit" class="px-8 py-3 bg-[#0033a0] hover:bg-[#002480] text-white rounded-full text-xs font-bold transition shadow-md inline-flex items-center gap-2">
                        <span>Submit Feedback &amp; Rating</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection
