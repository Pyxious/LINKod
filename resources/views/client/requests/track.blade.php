@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full max-w-6xl mx-auto px-6 py-16 flex flex-col md:flex-row items-center justify-between gap-16">
    
    <!-- Left Side: Text Content -->
    <div class="flex-1">
        <h1 class="text-[#0033a0] text-5xl md:text-[56px] font-extrabold leading-[1.1] mb-6 tracking-tight">
            Track your<br>request status
        </h1>
        <p class="text-[#0033a0]/80 text-[15px] leading-relaxed max-w-sm">
            Enter your tracking number to see the real-time status of your submitted service job request.
        </p>
    </div>

    <!-- Right Side: Tracking Form Card -->
    <div class="flex-1 w-full max-w-md">
        <div class="bg-[#fefce8] rounded-2xl p-8 shadow-sm">
            <h2 class="text-[#0033a0] font-extrabold text-sm uppercase tracking-widest mb-1">Enter Tracking Number</h2>
            <p class="text-gray-400 text-xs mb-6 leading-relaxed">Your tracking number was provided when you submitted your request.</p>

            <form action="{{ route('client.requests.track') }}" method="GET" class="space-y-4">
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">#</span>
                    <input type="text" name="tracking_number" placeholder="e.g., PS-012" class="w-full pl-9 pr-4 py-4 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0] shadow-sm">
                </div>
                
                <button type="submit" class="w-full py-4 bg-[#0033a0] text-white rounded-xl font-bold text-sm hover:bg-[#002480] transition shadow-md">
                    Track Now
                </button>
            </form>

            <div class="mt-5 text-center">
                <span class="text-xs text-gray-400">Can't find your number? </span>
                <a href="{{ route('client.requests.index') }}" class="text-xs text-[#0033a0] font-medium underline underline-offset-2 hover:text-[#002480] transition">
                    View your requests
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
