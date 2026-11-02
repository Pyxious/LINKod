@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full max-w-6xl mx-auto px-6 py-16 flex flex-col md:flex-row items-center justify-between gap-12">
    <div class="flex-1">
        <h1 class="text-[#0033a0] dark:text-blue-400 text-4xl md:text-5xl font-extrabold leading-tight mb-4 tracking-tight">
            Track your<br>request status
        </h1>
        <p class="text-slate-600 dark:text-gray-300 text-sm sm:text-base leading-relaxed max-w-md">
            Enter your tracking code (e.g., <span class="font-mono font-bold">PLS-012</span>) or view all your submitted service requisitions in one place.
        </p>
    </div>

    <div class="flex-1 w-full max-w-md">
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl p-8 shadow-sm">
            <h2 class="text-[#0033a0] dark:text-blue-400 font-extrabold text-sm uppercase tracking-wider mb-1">Enter Tracking Code</h2>
            <p class="text-gray-500 dark:text-gray-400 text-xs mb-6 leading-relaxed">Jump straight to a request or open your full requests list below.</p>

            <form action="{{ route('client.requests.track') }}" method="GET" class="space-y-4">
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">#</span>
                    <input type="text" name="tracking_number" required placeholder="e.g., PLS-012 or 0012" class="w-full pl-9 pr-4 py-3.5 bg-slate-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0]">
                </div>
                
                <button type="submit" class="w-full py-3.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl font-bold text-sm transition shadow-sm cursor-pointer">
                    Track Request
                </button>
            </form>

            <div class="mt-5 pt-4 border-t border-gray-100 dark:border-zinc-800 text-center">
                <a href="{{ route('client.requests.index') }}" class="text-xs text-[#0033a0] dark:text-blue-400 font-bold hover:underline transition">
                    View all my submitted requests &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
