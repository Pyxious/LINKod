@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full bg-[#edf4fb] dark:bg-[#111111] py-10 px-4 sm:px-6 lg:px-8 font-sans min-h-[calc(100vh-64px)] flex flex-col items-center">
    
    <div class="w-full max-w-4xl space-y-6">
        
        <!-- Header Banner Card -->
        <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-8 py-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#e5e1b0] dark:border-zinc-800 pb-5 mb-5">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                        Clientele Satisfaction Survey & Feedback
                    </h1>
                    <p class="text-xs sm:text-sm text-[#0033a0] dark:text-blue-400 font-bold mt-1">
                        Bicol University — General Services Office (BU-GSO)
                    </p>
                </div>
                <div class="bg-[#0033a0] text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider shrink-0 text-center">
                    Requisition #{{ str_pad($serviceRequest->request_id, 4, '0', STR_PAD_LEFT) }}
                </div>
            </div>

            <!-- Intro Instructions Text -->
            <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed font-medium mb-6">
                This Feedback Form is an important tool for us to measure our performance to help us improve our services to our clients. Kindly fill out this form to assess the performance of our staff and to improve further the quality of our services. You may use this form to express your Praise, Recommendations, or Criticisms. Please let us know how we performed our services to you by checking the appropriate box opposite the respective rating scales.
            </p>

            <!-- Rating Scale Emote Legend Grid (Using Photo Names) -->
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl p-4 shadow-sm">
                <div class="text-[11px] font-bold text-[#0033a0] dark:text-blue-400 uppercase tracking-wider mb-3">
                    Rating Scales Legend
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <!-- 5: Very Satisfied -->
                    <div class="flex items-center gap-2.5 p-2 bg-slate-50 dark:bg-zinc-800 rounded-lg border border-gray-100 dark:border-zinc-700">
                        <img src="{{ asset('images/very-satisfied.png') }}" alt="Very Satisfied" class="w-8 h-8 object-contain shrink-0">
                        <div>
                            <div class="text-xs font-black text-slate-900 dark:text-white">5</div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 font-bold leading-tight">Very Satisfied</div>
                        </div>
                    </div>

                    <!-- 4: Satisfied -->
                    <div class="flex items-center gap-2.5 p-2 bg-slate-50 dark:bg-zinc-800 rounded-lg border border-gray-100 dark:border-zinc-700">
                        <img src="{{ asset('images/satisfied.png') }}" alt="Satisfied" class="w-8 h-8 object-contain shrink-0">
                        <div>
                            <div class="text-xs font-black text-slate-900 dark:text-white">4</div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 font-bold leading-tight">Satisfied</div>
                        </div>
                    </div>

                    <!-- 3: Neutral -->
                    <div class="flex items-center gap-2.5 p-2 bg-slate-50 dark:bg-zinc-800 rounded-lg border border-gray-100 dark:border-zinc-700">
                        <img src="{{ asset('images/neutral.png') }}" alt="Neutral" class="w-8 h-8 object-contain shrink-0">
                        <div>
                            <div class="text-xs font-black text-slate-900 dark:text-white">3</div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 font-bold leading-tight">Neutral</div>
                        </div>
                    </div>

                    <!-- 2: Dissatisfied -->
                    <div class="flex items-center gap-2.5 p-2 bg-slate-50 dark:bg-zinc-800 rounded-lg border border-gray-100 dark:border-zinc-700">
                        <img src="{{ asset('images/dissatisfied.png') }}" alt="Dissatisfied" class="w-8 h-8 object-contain shrink-0">
                        <div>
                            <div class="text-xs font-black text-slate-900 dark:text-white">2</div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 font-bold leading-tight">Dissatisfied</div>
                        </div>
                    </div>

                    <!-- 1: Very Dissatisfied -->
                    <div class="flex items-center gap-2.5 p-2 bg-slate-50 dark:bg-zinc-800 rounded-lg border border-gray-100 dark:border-zinc-700">
                        <img src="{{ asset('images/very-dissatisfied.png') }}" alt="Very Dissatisfied" class="w-8 h-8 object-contain shrink-0">
                        <div>
                            <div class="text-xs font-black text-slate-900 dark:text-white">1</div>
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 font-bold leading-tight">Very Dissatisfied</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Job Completed Specification Details -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 pt-5 border-t border-[#e5e1b0] dark:border-zinc-800 text-xs">
                <div>
                    <span class="font-bold text-gray-500 dark:text-gray-400 block mb-0.5">Services Completed:</span>
                    <span class="font-black text-slate-900 dark:text-white block">{{ $serviceRequest->title }}</span>
                </div>
                <div>
                    <span class="font-bold text-gray-500 dark:text-gray-400 block mb-0.5">GSO Service Team Leader:</span>
                    <span class="font-black text-[#0033a0] dark:text-blue-400 block">
                        {{ $serviceRequest->project->approvedBy->user->full_name ?? 'BU-GSO Team Leader' }}
                    </span>
                </div>
                <div>
                    <span class="font-bold text-gray-500 dark:text-gray-400 block mb-0.5">Date Completed:</span>
                    <span class="font-black text-slate-900 dark:text-white block">
                        {{ \Carbon\Carbon::parse($serviceRequest->latestHistory->updated_at ?? now())->format('F d, Y') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Evaluation Form -->
        <form action="{{ route('client.evaluations.store', $serviceRequest->request_id) }}" method="POST" class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm space-y-6">
            @csrf

            <!-- Functions & Emote Rating Scales Table -->
            <div class="overflow-x-auto">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Evaluation Functions</h2>
                
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#0033a0] text-white text-xs font-extrabold uppercase tracking-wider border-b border-gray-200">
                            <th class="py-3.5 px-5 rounded-tl-xl">FUNCTIONS</th>
                            <th class="py-3 px-3 text-center min-w-[100px]">
                                <div class="flex flex-col items-center gap-1">
                                    <img src="{{ asset('images/very-satisfied.png') }}" alt="5" class="w-7 h-7 object-contain">
                                    <span class="text-[10px] tracking-normal font-bold">5 - Very Satisfied</span>
                                </div>
                            </th>
                            <th class="py-3 px-3 text-center min-w-[100px]">
                                <div class="flex flex-col items-center gap-1">
                                    <img src="{{ asset('images/satisfied.png') }}" alt="4" class="w-7 h-7 object-contain">
                                    <span class="text-[10px] tracking-normal font-bold">4 - Satisfied</span>
                                </div>
                            </th>
                            <th class="py-3 px-3 text-center min-w-[100px]">
                                <div class="flex flex-col items-center gap-1">
                                    <img src="{{ asset('images/neutral.png') }}" alt="3" class="w-7 h-7 object-contain">
                                    <span class="text-[10px] tracking-normal font-bold">3 - Neutral</span>
                                </div>
                            </th>
                            <th class="py-3 px-3 text-center min-w-[100px]">
                                <div class="flex flex-col items-center gap-1">
                                    <img src="{{ asset('images/dissatisfied.png') }}" alt="2" class="w-7 h-7 object-contain">
                                    <span class="text-[10px] tracking-normal font-bold">2 - Dissatisfied</span>
                                </div>
                            </th>
                            <th class="py-3 px-3 text-center min-w-[100px] rounded-tr-xl">
                                <div class="flex flex-col items-center gap-1">
                                    <img src="{{ asset('images/very-dissatisfied.png') }}" alt="1" class="w-7 h-7 object-contain">
                                    <span class="text-[10px] tracking-normal font-bold">1 - Very Dissatisfied</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-800 text-xs sm:text-sm">
                        @php
                            $functions = [
                                'quality'     => 'Quality of Service',
                                'attitude'    => 'Attitude',
                                'safety'      => 'Safety Precaution Awareness',
                                'time'        => 'Time Bound',
                                'housekeeping'=> 'Workplace Housekeeping',
                            ];
                        @endphp

                        @foreach($functions as $key => $label)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition">
                                <td class="py-4 px-5 font-bold text-slate-900 dark:text-white">
                                    {{ $label }}
                                </td>
                                @for($score = 5; $score >= 1; $score--)
                                    <td class="py-4 px-3 text-center">
                                        <label class="cursor-pointer inline-flex items-center justify-center p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-zinc-700 transition">
                                            <input type="radio" 
                                                   name="ratings[{{ $key }}]" 
                                                   value="{{ $score }}" 
                                                   class="w-5 h-5 text-[#0033a0] border-gray-300 focus:ring-[#0033a0] cursor-pointer"
                                                   {{ $score === 5 ? 'checked' : '' }}>
                                        </label>
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Suggestions / Recommendations Text Area -->
            <div>
                <label class="block text-xs font-bold text-[#0033a0] dark:text-blue-400 uppercase tracking-wider mb-2">
                    SUGGESTIONS / RECOMMENDATIONS:
                </label>
                <textarea name="feedback_text" 
                          rows="4" 
                          placeholder="Express your Praise, Recommendations, or Criticisms here..." 
                          class="w-full px-4 py-3 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs sm:text-sm text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]"></textarea>
            </div>

            <!-- Bottom Sign-off & Submit Button -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-gray-100 dark:border-zinc-800">
                <div class="text-sm font-black text-[#ea580c] italic">
                    We are happy to serve!!!
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('client.requests.show', $serviceRequest->request_id) }}" class="px-5 py-2.5 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 text-gray-700 dark:text-gray-300 rounded-full text-xs font-bold transition">
                        Back to Request
                    </a>
                    <button type="submit" class="px-8 py-3 bg-[#0033a0] hover:bg-[#002480] text-white rounded-full text-xs font-bold transition shadow-md inline-flex items-center gap-2">
                        <span>Submit Feedback & Rating</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection
