@extends('layouts.admin')
@section('page-title', 'Messages')
@section('content')

<!-- Header Section -->
<div class="bg-[#fdfde8] rounded-xl p-6 border border-gray-200 shadow-sm mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-[#1a3c8f] mb-1">Messages</h1>
        <p class="text-sm text-[#1a3c8f] opacity-90">Communicate with workers and clients</p>
    </div>
    <button class="bg-[#1a3c8f] hover:bg-[#152e6e] text-white font-medium px-5 py-2.5 rounded-lg flex items-center gap-2 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Message
    </button>
</div>

<!-- Main Chat Interface -->
<div class="flex gap-6 h-[calc(100vh-280px)] min-h-[500px]">
    
    <!-- Sidebar / Contacts -->
    <div class="w-80 bg-[#f0f4f8] rounded-xl border border-gray-200 flex flex-col overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-white">
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <input type="text" placeholder="Search" class="w-full pl-3 pr-10 py-2 border border-[#1a3c8f] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#1a3c8f]">
                    <svg class="w-4 h-4 absolute right-3 top-3 text-[#1a3c8f]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button class="w-10 h-10 bg-[#1a3c8f] text-white rounded-lg flex items-center justify-center hover:bg-[#152e6e] shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
            
            <div class="flex gap-4 mt-4 px-1">
                <button class="text-xs font-bold text-[#1a3c8f] border-b-2 border-[#1a3c8f] pb-1">All</button>
                <button class="text-xs font-medium text-gray-400 pb-1">Clients</button>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto p-3 space-y-2">
            <!-- Dummy Contacts -->
            <div class="bg-[#d0dbe8] rounded-xl h-16 flex items-center px-3 gap-3">
                <div class="w-10 h-10 rounded-full border border-[#1a3c8f] bg-transparent shrink-0"></div>
            </div>
            <div class="bg-[#d0dbe8] rounded-xl h-16 flex items-center px-3 gap-3">
                <div class="w-10 h-10 rounded-full border border-[#1a3c8f] bg-transparent shrink-0"></div>
            </div>
            
            <!-- Active Contact -->
            <div class="bg-white rounded-xl h-20 border border-[#1a3c8f] flex items-center px-3 gap-3 shadow-sm cursor-pointer">
                <div class="w-12 h-12 rounded-full border-2 border-[#1a3c8f] overflow-hidden shrink-0 flex items-center justify-center bg-gray-100 font-bold text-[#1a3c8f]">
                    JD
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[14px] font-bold text-[#1a3c8f]">Jane Doe</div>
                    <div class="text-[11px] text-[#1a3c8f] opacity-80 truncate">lorem ipsum tak tak tak tak tak...</div>
                </div>
            </div>
            
            <div class="bg-[#d0dbe8] rounded-xl h-16 flex items-center px-3 gap-3">
                <div class="w-10 h-10 rounded-full border border-[#1a3c8f] bg-transparent shrink-0"></div>
            </div>
            <div class="bg-[#d0dbe8] rounded-xl h-16 flex items-center px-3 gap-3">
                <div class="w-10 h-10 rounded-full border border-[#1a3c8f] bg-transparent shrink-0"></div>
            </div>
            <div class="bg-[#d0dbe8] rounded-xl h-16 flex items-center px-3 gap-3">
                <div class="w-10 h-10 rounded-full border border-[#1a3c8f] bg-transparent shrink-0"></div>
            </div>
        </div>
    </div>
    
    <!-- Chat Area -->
    <div class="flex-1 bg-white rounded-xl border-2 border-[#1a3c8f] flex flex-col overflow-hidden">
        <!-- Chat Header -->
        <div class="h-20 bg-[#0033a0] flex flex-col items-center justify-center relative shadow-sm">
            <div class="absolute -bottom-8 w-16 h-16 rounded-full border-4 border-white bg-gray-100 flex items-center justify-center overflow-hidden z-10 text-xl font-bold text-[#0033a0]">
                JD
            </div>
        </div>
        
        <div class="text-center mt-10 mb-4">
            <h2 class="text-lg font-bold text-[#0033a0]">Jane Doe</h2>
            <p class="text-xs text-[#0033a0] opacity-80">jvd@bicol-u.edu.ph</p>
        </div>
        
        <!-- Chat Messages -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <!-- Received Messages -->
            <div class="max-w-[70%]">
                <div class="bg-[#d1d5db] rounded-xl p-4 text-sm text-gray-800 mb-2 min-h-[40px]"></div>
                <div class="bg-[#d1d5db] rounded-xl p-4 text-sm text-gray-800 min-h-[80px]"></div>
            </div>
            
            <!-- Sent Messages -->
            <div class="max-w-[70%] ml-auto">
                <div class="bg-[#d1d5db] rounded-xl p-4 text-sm text-gray-800 mb-2 min-h-[40px]"></div>
                <div class="bg-[#d1d5db] rounded-xl p-4 text-sm text-gray-800 min-h-[80px]"></div>
            </div>
        </div>
        
        <!-- Chat Input -->
        <div class="p-4 border-t-2 border-[#1a3c8f] bg-white flex items-center gap-4">
            <button class="text-[#1a3c8f] hover:text-[#0033a0] transition p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
            </button>
            <div class="flex-1 relative">
                <input type="text" placeholder="Type a Message" class="w-full bg-[#fdfde8] border border-[#1a3c8f] rounded-full py-2.5 pl-5 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a3c8f]">
            </div>
            <button class="w-10 h-10 bg-transparent text-[#1a3c8f] hover:text-[#0033a0] transition flex items-center justify-center shrink-0">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
            </button>
        </div>
    </div>
</div>
@endsection
