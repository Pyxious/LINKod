@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full flex flex-col font-sans">
    <!-- Hero Section with Portal Choice Cards -->
    <section class="w-full bg-[#edf4fb] dark:bg-[#18181b] py-16 sm:py-20 px-4 sm:px-6 lg:px-8 flex flex-col items-center text-center min-h-[calc(100vh-64px-200px)]">
        
        <!-- Welcome Title -->
        <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-3">
            Welcome back, {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
        </h1>

        <!-- Subtitle -->
        <p class="text-slate-600 dark:text-gray-300 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed mb-12 font-medium">
            Access your assigned university services and account tools in one place.
        </p>

        <!-- Section: Your main service -->
        <div class="w-full max-w-5xl mx-auto text-left">
            <div class="mb-6 text-center sm:text-left">
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Your main service</h2>
                <p class="text-xs text-slate-500 dark:text-gray-400">Select a portal module below for faster access.</p>
            </div>

            <!-- 2-Column Portal Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Card 1: Worker Portal -->
                <div class="bg-white dark:bg-[#1c1c1e] p-8 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-md hover:shadow-lg transition flex flex-col justify-between items-start group">
                    <div class="w-full">
                        <!-- Header Badge -->
                        <div class="flex items-center justify-between mb-5 w-full">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 uppercase tracking-wider">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                WORKER SERVICES
                            </span>
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a2 2 0 01-2 2 2 2 0 01-2-2V4zm-6 8a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H7a2 2 0 01-2-2v-8z"/></svg>
                            </div>
                        </div>

                        <!-- Title & Description -->
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-2 group-hover:text-[#0033a0] transition">
                            Worker Portal
                        </h3>
                        <p class="text-slate-600 dark:text-gray-300 text-xs leading-relaxed mb-8">
                            View and update assigned job orders, track task progress, request materials (BOM), and complete maintenance reports.
                        </p>
                    </div>

                    <a href="{{ route('worker.dashboard') }}" class="w-full sm:w-auto px-7 py-3 bg-[#0033a0] hover:bg-[#002480] text-white rounded-full font-bold text-xs transition flex items-center justify-center gap-2 shadow-sm">
                        <span>Go to Worker Portal</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <!-- Card 2: Client Portal -->
                <div class="bg-white dark:bg-[#1c1c1e] p-8 rounded-2xl border border-slate-200/80 dark:border-zinc-800 shadow-md hover:shadow-lg transition flex flex-col justify-between items-start group">
                    <div class="w-full">
                        <!-- Header Badge -->
                        <div class="flex items-center justify-between mb-5 w-full">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800 uppercase tracking-wider">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                CLIENT SERVICES
                            </span>
                            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                        </div>

                        <!-- Title & Description -->
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-2 group-hover:text-[#2563eb] transition">
                            Client Portal
                        </h3>
                        <p class="text-slate-600 dark:text-gray-300 text-xs leading-relaxed mb-8">
                            Submit service job requests for your campus office or building, track request status in real-time, and evaluate completed work.
                        </p>
                    </div>

                    <a href="{{ route('client.dashboard') }}" class="w-full sm:w-auto px-7 py-3 bg-[#2563eb] hover:bg-[#1d4ed8] text-white rounded-full font-bold text-xs transition flex items-center justify-center gap-2 shadow-sm">
                        <span>Go to Client Portal</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
