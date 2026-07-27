@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full flex flex-col font-sans">
    <!-- Hero Section -->
    <section class="w-full bg-[#edf4fb] dark:bg-[#18181b] py-20 px-4 sm:px-6 lg:px-8 flex flex-col items-center text-center">
        <!-- Title -->
        <h1 class="text-4xl sm:text-6xl font-black text-black dark:text-white tracking-tight uppercase max-w-4xl leading-[1.15] mb-6">
            BU <span class="text-[#0033a0] dark:text-blue-400">GSO</span> SERVICE<br>REQUEST SYSTEM
        </h1>

        <!-- Subtitle -->
        <p class="text-gray-600 dark:text-gray-300 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed mb-8 font-medium">
            Submit service requests, track your request status, and provide feedback to improve further the quality of our services for all Bicol University personnel.
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-6 w-full max-w-md">
            <a href="{{ route('client.requests.create') }}" class="w-full sm:w-auto px-8 py-3.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-full font-bold text-sm transition flex items-center justify-center gap-2 shadow-md shadow-blue-900/20 min-w-[200px]">
                Submit a Request
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
            </a>
            <a href="#track" class="w-full sm:w-auto px-8 py-3.5 bg-[#fef3c7] hover:bg-[#fde68a] text-[#b45309] border border-[#fde68a] rounded-full font-bold text-sm transition text-center min-w-[200px]">
                Track a Request
            </a>
        </div>

        <!-- Availability Note -->
        <p class="text-gray-500 dark:text-gray-400 text-xs italic mb-10">
            * Available every Monday to Friday, 8:00 AM - 5:00 PM
        </p>

        <!-- Indicator Circles -->
        <div class="flex items-center justify-center gap-3">
            <div class="w-8 h-8 rounded-full bg-gray-300/60 dark:bg-zinc-700"></div>
            <div class="w-8 h-8 rounded-full bg-gray-300/60 dark:bg-zinc-700"></div>
            <div class="w-8 h-8 rounded-full bg-gray-300/60 dark:bg-zinc-700"></div>
        </div>
    </section>

    <!-- Services Offered Section (FIRST - WITH SCROLL OFFSET) -->
    <section id="services" class="w-full bg-white dark:bg-[#111111] py-16 px-4 sm:px-6 lg:px-12 border-b border-gray-100 dark:border-zinc-800 scroll-mt-16">
        <div class="max-w-6xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-10">
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    <span class="text-[#ea580c]">Services</span> <span class="text-black dark:text-white">Offered</span>
                </h2>
            </div>

            <!-- 6 Services Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: Carpentry -->
                <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-xl border border-gray-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition flex flex-col justify-between items-start min-h-[170px]">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm sm:text-[15px] mb-2 leading-snug">
                            Carpentry, Masonry, and Electrical Services
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 text-xs leading-relaxed mb-4">
                            Repairs and maintenance of facilities and infrastructure.
                        </p>
                    </div>
                    <a href="{{ route('client.requests.create', ['category' => 'Carpentry/Masonry/Electrical']) }}" class="px-4 py-2 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-lg shadow-sm transition inline-block">
                        Request now
                    </a>
                </div>

                <!-- Card 2: Plumbing -->
                <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-xl border border-gray-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition flex flex-col justify-between items-start min-h-[170px]">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm sm:text-[15px] mb-2 leading-snug">
                            Plumbing Services
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 text-xs leading-relaxed mb-4">
                            Plumbing repairs, installations, and maintenance.
                        </p>
                    </div>
                    <a href="{{ route('client.requests.create', ['category' => 'Plumbing']) }}" class="px-4 py-2 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-lg shadow-sm transition inline-block">
                        Request now
                    </a>
                </div>

                <!-- Card 3: Painting -->
                <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-xl border border-gray-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition flex flex-col justify-between items-start min-h-[170px]">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm sm:text-[15px] mb-2 leading-snug">
                            Painting Services
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 text-xs leading-relaxed mb-4">
                            Painting of buildings, rooms, and other facilities.
                        </p>
                    </div>
                    <a href="{{ route('client.requests.create', ['category' => 'Painting']) }}" class="px-4 py-2 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-lg shadow-sm transition inline-block">
                        Request now
                    </a>
                </div>

                <!-- Card 4: Landscaping -->
                <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-xl border border-gray-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition flex flex-col justify-between items-start min-h-[170px]">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm sm:text-[15px] mb-2 leading-snug">
                            Landscaping Services
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 text-xs leading-relaxed mb-4">
                            Grounds maintenance, gardening, and landscaping.
                        </p>
                    </div>
                    <a href="{{ route('client.requests.create', ['category' => 'Landscaping']) }}" class="px-4 py-2 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-lg shadow-sm transition inline-block">
                        Request now
                    </a>
                </div>

                <!-- Card 5: Manpower -->
                <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-xl border border-gray-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition flex flex-col justify-between items-start min-h-[170px]">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm sm:text-[15px] mb-2 leading-snug">
                            Manpower Services
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 text-xs leading-relaxed mb-4">
                            Assistance for events, activities, and other manpower needs.
                        </p>
                    </div>
                    <a href="{{ route('client.requests.create', ['category' => 'Manpower']) }}" class="px-4 py-2 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-lg shadow-sm transition inline-block">
                        Request now
                    </a>
                </div>

                <!-- Card 6: Janitorial -->
                <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-xl border border-gray-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition flex flex-col justify-between items-start min-h-[170px]">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm sm:text-[15px] mb-2 leading-snug">
                            Janitorial Services
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 text-xs leading-relaxed mb-4">
                            Cleaning and janitorial support services.
                        </p>
                    </div>
                    <a href="{{ route('client.requests.create', ['category' => 'Janitorial']) }}" class="px-4 py-2 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-lg shadow-sm transition inline-block">
                        Request now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Track Your Request Status Section (SECOND - WITH SCROLL OFFSET) -->
    <section id="track" class="w-full bg-[#edf4fb] dark:bg-[#18181b] py-16 px-4 sm:px-6 lg:px-12 border-b border-gray-200 dark:border-zinc-800 scroll-mt-16">
        <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
            
            <!-- Left Info -->
            <div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                    <span class="text-[#0033a0] dark:text-blue-400">Track</span> your request status
                </h2>
                <p class="text-gray-600 dark:text-gray-300 text-xs sm:text-sm mt-3 leading-relaxed max-w-md">
                    Enter your tracking number to see the real-time status of your submitted service job request.
                </p>
            </div>

            <!-- Right Form Box (Dotted Border Card) -->
            <div class="border-2 border-dashed border-[#0033a0] rounded-2xl p-7 bg-white dark:bg-zinc-900 shadow-sm">
                <form method="GET" action="{{ route('client.requests.index') }}">
                    <label class="block text-xs font-black text-slate-800 dark:text-gray-200 uppercase tracking-wider mb-0.5">
                        ENTER TRACKING NUMBER
                    </label>
                    <span class="text-[11px] text-gray-400 block mb-4">
                        Your tracking number was provided when you submitted your request.
                    </span>

                    <input type="text" 
                           name="search" 
                           placeholder="# e.g., PS-012" 
                           class="w-full px-4 py-3 bg-[#edf4fb] dark:bg-zinc-800 border border-blue-200 dark:border-zinc-700 rounded-xl text-xs sm:text-sm font-semibold text-slate-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-[#0033a0] mb-4">

                    <button type="submit" class="w-full py-3 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition shadow-md">
                        Track Now
                    </button>

                    <div class="text-center mt-3">
                        <span class="text-[11px] text-gray-400">Can't find your number? </span>
                        <a href="{{ route('client.requests.index') }}" class="text-[11px] font-bold text-[#0033a0] dark:text-blue-400 underline">View your requests</a>
                    </div>
                </form>
            </div>

        </div>
    </section>

    <!-- Frequently Asked Questions Section (THIRD - WITH SCROLL OFFSET) -->
    <section id="faq" class="w-full bg-white dark:bg-[#111111] py-20 px-4 sm:px-6 lg:px-12 border-b border-gray-100 dark:border-zinc-800 scroll-mt-16">
        <div class="max-w-6xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-black tracking-tight">
                    <span class="text-[#ea580c]">Frequently Asked</span> <span class="text-slate-900 dark:text-white">Questions</span>
                </h2>
            </div>

            <!-- 6 FAQ Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- FAQ 1 -->
                <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-2xl border border-gray-200 dark:border-zinc-800 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="inline-block px-3 py-1 text-[10px] font-bold text-[#0033a0] dark:text-blue-400 border border-dashed border-[#0033a0] rounded-lg uppercase tracking-wider mb-4">
                            GENERAL
                        </span>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                            What is LINKod?
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-4">
                            LINKod is the official web-based Service Request System of the Bicol University General Services Office (BUGSO). It enables authorized university personnel to submit job requisitions online.
                        </p>
                    </div>
                    <a href="#" class="text-xs font-bold text-[#0033a0] dark:text-blue-400 hover:underline">Read more &gt;</a>
                </div>

                <!-- FAQ 2 -->
                <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-2xl border border-gray-200 dark:border-zinc-800 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="inline-block px-3 py-1 text-[10px] font-bold text-[#0033a0] dark:text-blue-400 border border-dashed border-[#0033a0] rounded-lg uppercase tracking-wider mb-4">
                            GENERAL
                        </span>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                            Who can use LINKod?
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-4">
                            LINKod is intended for authorized Bicol University faculty, staff, offices, and/or personnel using their official BU Google account (@bicol-u.edu.ph).
                        </p>
                    </div>
                    <a href="#" class="text-xs font-bold text-[#0033a0] dark:text-blue-400 hover:underline">Read more &gt;</a>
                </div>

                <!-- FAQ 3 -->
                <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-2xl border border-gray-200 dark:border-zinc-800 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="inline-block px-3 py-1 text-[10px] font-bold text-[#0033a0] dark:text-blue-400 border border-dashed border-[#0033a0] rounded-lg uppercase tracking-wider mb-4">
                            GENERAL
                        </span>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                            What services can I request through LINKod?
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-4">
                            You may submit requests for: Carpentry / Masonry / Electrical Services, Plumbing Services, Painting Services, Landscaping Services, Janitorial Services, and Manpower Assistance.
                        </p>
                    </div>
                    <a href="#" class="text-xs font-bold text-[#0033a0] dark:text-blue-400 hover:underline">Read more &gt;</a>
                </div>

                <!-- FAQ 4 -->
                <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-2xl border border-gray-200 dark:border-zinc-800 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="inline-block px-3 py-1 text-[10px] font-bold text-[#0033a0] dark:text-blue-400 border border-dashed border-[#0033a0] rounded-lg uppercase tracking-wider mb-4">
                            SERVICE REQUESTS
                        </span>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                            Can I upload photos or supporting documents?
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-4">
                            Yes. You may attach images or other supporting documents to help GSO personnel assess your request before inspection and worker deployment.
                        </p>
                    </div>
                    <a href="#" class="text-xs font-bold text-[#0033a0] dark:text-blue-400 hover:underline">Read more &gt;</a>
                </div>

                <!-- FAQ 5 -->
                <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-2xl border border-gray-200 dark:border-zinc-800 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="inline-block px-3 py-1 text-[10px] font-bold text-[#0033a0] dark:text-blue-400 border border-dashed border-[#0033a0] rounded-lg uppercase tracking-wider mb-4">
                            SERVICE REQUESTS
                        </span>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                            How do I know if my request has been approved?
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-4">
                            You can monitor your request through the Track Request page. LINKod also sends status updates whenever your request status changes.
                        </p>
                    </div>
                    <a href="#" class="text-xs font-bold text-[#0033a0] dark:text-blue-400 hover:underline">Read more &gt;</a>
                </div>

                <!-- FAQ 6 -->
                <div class="bg-white dark:bg-[#1c1c1e] p-6 rounded-2xl border border-gray-200 dark:border-zinc-800 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="inline-block px-3 py-1 text-[10px] font-bold text-[#0033a0] dark:text-blue-400 border border-dashed border-[#0033a0] rounded-lg uppercase tracking-wider mb-4">
                            TECHNICAL
                        </span>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2 leading-snug">
                            I cannot sign in. What should I do?
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-4">
                            Ensure that you are using your official Bicol University Google account. If the problem persists, contact the ICTO Helpdesk for account assistance.
                        </p>
                    </div>
                    <a href="#" class="text-xs font-bold text-[#0033a0] dark:text-blue-400 hover:underline">Read more &gt;</a>
                </div>

            </div>
        </div>
    </section>

    <!-- Organizational Structure Section (FOURTH - WITH SCROLL OFFSET) -->
    <section id="org-structure" class="w-full bg-white dark:bg-[#111111] border-t border-gray-100 dark:border-zinc-800 font-sans scroll-mt-16">
        <!-- Section Header Banner with Light Blue Background -->
        <div class="w-full bg-[#edf4fb] dark:bg-[#18181b] py-6 px-4 sm:px-6 lg:px-8 border-b border-gray-200 dark:border-zinc-800 text-center">
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                <span class="text-[#0033a0] dark:text-blue-400">Organizational</span> <span class="text-slate-900 dark:text-white">Structure</span>
            </h2>
        </div>

        <!-- Graph Content Area with Pure White Background -->
        <div class="max-w-7xl mx-auto text-center py-6 px-2 sm:px-4 lg:px-6 flex justify-center items-center">
            <div class="w-full overflow-x-auto">
                <svg viewBox="0 0 1150 590" class="w-full max-w-[1150px] mx-auto h-auto max-h-[80vh] font-sans" xmlns="http://www.w3.org/2000/svg">
                    <style>
                        .box-bg { fill: #ffffff !important; stroke: #94a3b8 !important; stroke-width: 1.5; stroke-dasharray: 4,4; rx: 10px; }
                        .line-blue { stroke: #2563eb !important; stroke-width: 2; fill: none; stroke-linecap: round; stroke-linejoin: round; }
                        .title-orange { fill: #c2410c !important; font-weight: 800; font-size: 10px; text-anchor: middle; letter-spacing: 0.3px; }
                        .name-bold { fill: #0f172a !important; font-weight: 800; font-size: 11px; text-anchor: middle; }
                        .sub-gray { fill: #64748b !important; font-weight: 500; font-size: 10px; text-anchor: middle; }
                    </style>

                    <!-- CONNECTING LINES (Rendered cleanly below boxes) -->
                    
                    <!-- Line 1: Nebres -> Barrameda -->
                    <line x1="575" y1="60" x2="575" y2="80" class="line-blue" />
                    
                    <!-- Line 2: Barrameda -> Caparas -->
                    <line x1="575" y1="125" x2="575" y2="145" class="line-blue" />

                    <!-- Line 3: Caparas to Campus Cluster AO top -->
                    <path d="M 575 195 H 990 V 215" class="line-blue" />

                    <!-- Line 4: Caparas Bottom -> Padilla top -->
                    <line x1="575" y1="195" x2="575" y2="215" class="line-blue" />

                    <!-- Line 5: Padilla Right side -> Campus AO left side -->
                    <line x1="700" y1="237.5" x2="880" y2="237.5" class="line-blue" />

                    <!-- Line 6: Main Horizontal Bus Line from Padilla bottom across Cols 1, 2, 3 -->
                    <line x1="575" y1="260" x2="575" y2="270" class="line-blue" />
                    <line x1="125" y1="270" x2="705" y2="270" class="line-blue" />
                    
                    <!-- Line 7: Drops from Bus Line to Col 1, Col 2, Col 3 -->
                    <line x1="125" y1="270" x2="125" y2="280" class="line-blue" />
                    <line x1="415" y1="270" x2="415" y2="280" class="line-blue" />
                    <line x1="705" y1="270" x2="705" y2="280" class="line-blue" />

                    <!-- Line 8: Campus Cluster AO bottom -> Support Staff top -->
                    <line x1="990" y1="260" x2="990" y2="280" class="line-blue" />

                    <!-- Line 9: Facilities Maintenance -> 4 Sub Pairs Trunk -->
                    <line x1="125" y1="330" x2="125" y2="549" class="line-blue" />

                    <line x1="125" y1="369" x2="140" y2="369" class="line-blue" />
                    <line x1="370" y1="369" x2="390" y2="369" class="line-blue" />

                    <line x1="125" y1="429" x2="140" y2="429" class="line-blue" />
                    <line x1="370" y1="429" x2="390" y2="429" class="line-blue" />

                    <line x1="125" y1="489" x2="140" y2="489" class="line-blue" />
                    <line x1="370" y1="489" x2="390" y2="489" class="line-blue" />

                    <line x1="125" y1="549" x2="140" y2="549" class="line-blue" />
                    <line x1="370" y1="549" x2="390" y2="549" class="line-blue" />

                    <!-- Line 10: Support Staff -> Utility Personnel -->
                    <line x1="990" y1="330" x2="990" y2="355" class="line-blue" />


                    <!-- BOXES & TEXT CONTENT -->

                    <!-- 1. DR. BABY BOY BENJAMIN D. NEBRES III -->
                    <g>
                        <rect x="450" y="15" width="250" height="45" class="box-bg" />
                        <text x="575" y="34" class="name-bold">DR. BABY BOY BENJAMIN D. NEBRES III</text>
                        <text x="575" y="48" class="sub-gray">SUC President IV</text>
                    </g>

                    <!-- 2. CYRUS A. BARRAMEDA -->
                    <g>
                        <rect x="450" y="80" width="250" height="45" class="box-bg" />
                        <text x="575" y="99" class="name-bold">CYRUS A. BARRAMEDA</text>
                        <text x="575" y="113" class="sub-gray">Vice President for Admission &amp; Finance</text>
                    </g>

                    <!-- 3. MA. MYRA A. CAPARAS -->
                    <g>
                        <rect x="450" y="145" width="250" height="50" class="box-bg" />
                        <text x="575" y="164" class="name-bold">MA. MYRA A. CAPARAS</text>
                        <text x="575" y="177" class="sub-gray">Acting Chief Administrative Officer for</text>
                        <text x="575" y="188" class="sub-gray">Administrative Services Division</text>
                    </g>

                    <!-- CAMPUS/CLUSTER AO (PAR HORIZONTAL WITH SIR REY PADILLA AT Y=215) -->
                    <g>
                        <rect x="880" y="215" width="220" height="45" class="box-bg" />
                        <text x="990" y="242" class="name-bold">CAMPUS/CLUSTER AO</text>
                    </g>

                    <!-- 4. REY A. PADILLA -->
                    <g>
                        <rect x="450" y="215" width="250" height="45" class="box-bg" />
                        <text x="575" y="234" class="name-bold">REY A. PADILLA</text>
                        <text x="575" y="248" class="sub-gray">Head, General Services Office</text>
                    </g>

                    <!-- COLUMN 1: FACILITIES MAINTENANCE SECTION -->
                    <g>
                        <rect x="10" y="280" width="230" height="50" class="box-bg" />
                        <text x="125" y="297" class="title-orange">FACILITIES MAINTENANCE SECTION</text>
                        <text x="125" y="311" class="name-bold">DIOGENES L. LONDONIO</text>
                        <text x="125" y="322" class="sub-gray">PERSON-IN-CHARGE</text>
                    </g>

                    <!-- COLUMN 2: MA. KYLA NICOLE N. BERNALES -->
                    <g>
                        <rect x="300" y="280" width="230" height="50" class="box-bg" />
                        <text x="415" y="304" class="name-bold">MA. KYLA NICOLE N. BERNALES</text>
                        <text x="415" y="318" class="sub-gray">Clerical Staff</text>
                    </g>

                    <!-- COLUMN 3: JANITORIAL SERVICES SECTION -->
                    <g>
                        <rect x="590" y="280" width="230" height="50" class="box-bg" />
                        <text x="705" y="297" class="title-orange">JANITORIAL SERVICES SECTION</text>
                        <text x="705" y="311" class="name-bold">ERNIE B. DIMAANO</text>
                        <text x="705" y="322" class="sub-gray">PERSON-IN-CHARGE</text>
                    </g>

                    <!-- COLUMN 4: CAMPUS/UNIT GSO SUPPORT STAFF -->
                    <g>
                        <rect x="880" y="280" width="220" height="50" class="box-bg" />
                        <text x="990" y="310" class="name-bold">CAMPUS/UNIT GSO SUPPORT STAFF</text>
                    </g>

                    <!-- CAMPUS/UNIT UTILITY PERSONNEL -->
                    <g>
                        <rect x="880" y="355" width="220" height="45" class="box-bg" />
                        <text x="990" y="382" class="name-bold">CAMPUS/UNIT UTILITY PERSONNEL</text>
                    </g>

                    <!-- SUB PAIR 1: CARPENTRY -->
                    <g>
                        <rect x="140" y="345" width="230" height="48" class="box-bg" />
                        <text x="255" y="360" class="title-orange">CARPENTRY / MASONRY / ELEC. SERVICES</text>
                        <text x="255" y="373" class="name-bold">DIOGENES L. LONDONIO</text>
                        <text x="255" y="384" class="sub-gray">Team Leader</text>

                        <rect x="390" y="345" width="190" height="48" class="box-bg" />
                        <text x="485" y="368" class="name-bold">REYNANTE MADRONA</text>
                        <text x="485" y="381" class="sub-gray">(SKILLED WORKER)</text>
                    </g>

                    <!-- SUB PAIR 2: PLUMBING -->
                    <g>
                        <rect x="140" y="405" width="230" height="48" class="box-bg" />
                        <text x="255" y="420" class="title-orange">PLUMBING SERVICES</text>
                        <text x="255" y="433" class="name-bold">SONNY B. MARAYA</text>
                        <text x="255" y="444" class="sub-gray">Team Leader</text>

                        <rect x="390" y="405" width="190" height="48" class="box-bg" />
                        <text x="485" y="428" class="name-bold">FIDEL LANETA</text>
                        <text x="485" y="441" class="sub-gray">(SKILLED WORKER)</text>
                    </g>

                    <!-- SUB PAIR 3: PAINTING -->
                    <g>
                        <rect x="140" y="465" width="230" height="48" class="box-bg" />
                        <text x="255" y="480" class="title-orange">PAINTING SERVICES</text>
                        <text x="255" y="493" class="name-bold">JACOB JUAN N. BAÑARES</text>
                        <text x="255" y="504" class="sub-gray">Team Leader</text>

                        <rect x="390" y="465" width="190" height="48" class="box-bg" />
                        <text x="485" y="488" class="name-bold">GEORGE BORJE</text>
                        <text x="485" y="501" class="sub-gray">(SKILLED WORKER)</text>
                    </g>

                    <!-- SUB PAIR 4: LANDSCAPING -->
                    <g>
                        <rect x="140" y="525" width="230" height="48" class="box-bg" />
                        <text x="255" y="540" class="title-orange">LANDSCAPING SERVICES</text>
                        <text x="255" y="553" class="name-bold">SAMUEL C. BONAOBRA</text>
                        <text x="255" y="564" class="sub-gray">Team Leader</text>

                        <rect x="390" y="525" width="190" height="48" class="box-bg" />
                        <text x="485" y="548" class="name-bold">WILFREDO BUMALAY</text>
                        <text x="485" y="561" class="sub-gray">(SKILLED WORKER)</text>
                    </g>

                </svg>
            </div>
        </div>
    </section>
</div>
@endsection
