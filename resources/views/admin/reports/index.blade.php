@extends('layouts.admin')

@section('page-title', 'Reports Management')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-8 font-sans">
    
    <!-- Top Header Banner (Matches Mockup) -->
    <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-8 py-6 shadow-sm">
        <h1 class="text-2xl font-bold text-[#0033a0] dark:text-blue-400 mb-1">Reports</h1>
        <p class="text-sm font-medium text-[#0033a0]/80 dark:text-gray-300">
            View, generate, and export system accomplishment and performance reports
        </p>
    </div>

    <!-- Generate / Customize Reports Card (Matches Mockup) -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm space-y-6">
        
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black text-[#0033a0] dark:text-blue-400 tracking-tight">
                    Generate Accomplishment Reports
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Accomplishment reports automatically compile all finished/completed jobs for each maintenance section.
                </p>
            </div>
        </div>

        <form id="reportForm" action="{{ route('admin.reports.export') }}" method="POST" onsubmit="handleExportSubmit(event)" class="space-y-6">
            @csrf
            
            <!-- Row 0: Report Format Selection Buttons (Filling Container Space) -->
            <div class="bg-blue-50/50 dark:bg-zinc-800/40 p-4 sm:p-5 rounded-2xl border border-blue-100 dark:border-zinc-700 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <span class="block text-xs font-black text-[#0033a0] dark:text-blue-400 uppercase tracking-wide">
                        Select Report Type / Format
                    </span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">
                        Choose between Detailed Accomplishment Registry or Summary & Clientele Satisfaction Survey
                    </span>
                </div>

                <!-- Hidden Input for Form Submission -->
                <input type="hidden" name="report_type" id="reportType" value="Accomplishment Report">

                <!-- 2 Full-Width Buttons filling that space -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full">
                    <!-- Left: Accomplishments -->
                    <button type="button" 
                            id="btnReportTypeAccomplishment" 
                            onclick="setReportType('Accomplishment Report')" 
                            class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl border-2 font-bold text-xs sm:text-sm transition-all shadow-xs cursor-pointer bg-[#0033a0] text-white border-[#0033a0]">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Accomplishments</span>
                    </button>

                    <!-- Right: Summary of Clientele -->
                    <button type="button" 
                            id="btnReportTypeSummary" 
                            onclick="setReportType('Summary of Accomplishment & Clientele Satisfaction Survey')" 
                            class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl border-2 font-semibold text-xs sm:text-sm transition-all shadow-xs cursor-pointer bg-white dark:bg-zinc-900 text-slate-700 dark:text-gray-300 border-gray-200 dark:border-zinc-700 hover:border-[#0033a0]/50">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span>Summary of Clientele</span>
                    </button>
                </div>
            </div>
            
            <!-- Row 1: Filters (Year, Period / Semester, Date Start, Date End) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                
                <!-- Report Year -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Report Year</label>
                    <select name="report_year" id="reportYear" onchange="handlePeriodChange()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                        @php
                            $curYear = now()->year;
                        @endphp
                        @for($y = $curYear; $y >= $curYear - 4; $y--)
                            <option value="{{ $y }}" {{ $y === $curYear ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Semi-Annual Period / Semester -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Period / Semester</label>
                    <select name="period" id="reportPeriod" onchange="handlePeriodChange()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                        <option value="sem1" {{ now()->month <= 6 ? 'selected' : '' }}>January to June (1st Sem)</option>
                        <option value="sem2" {{ now()->month > 6 ? 'selected' : '' }}>July to December (2nd Sem)</option>
                        <option value="year">January to December (Full Year)</option>
                        <option value="custom">Custom Date Range</option>
                    </select>
                </div>

                <!-- Date Start -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Date Start</label>
                    <input type="date" name="start_date" id="startDate" onchange="handleCustomDateInput()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                </div>

                <!-- Date End -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Date End</label>
                    <input type="date" name="end_date" id="endDate" onchange="handleCustomDateInput()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                </div>

            </div>

            <!-- Row 2: Service Unit/Section, Worker Filter, and Include Worker Column -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-4 items-end pt-1">
                
                <!-- Service Unit / Section (4 cols) -->
                <div class="md:col-span-4">
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Maintenance Section / Unit</label>
                    <select name="category_id" id="categoryId" onchange="updateLivePreview()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                        <option value="">ALL SERVICE UNITS (Combined)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->category_id }}">{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter by Worker / Service (4 cols) -->
                <div class="md:col-span-4">
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Filter by Worker / Service</label>
                    <select name="worker_id" id="workerId" onchange="updateLivePreview()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                        <option value="">ALL WORKERS</option>
                        @foreach($workers as $w)
                            <option value="{{ $w['worker_id'] }}">{{ $w['name'] }} ({{ $w['service'] }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Include Assigned Worker (Column H) Toggle (4 cols) -->
                <div class="md:col-span-4 flex items-center h-[42px]">
                    <label class="relative inline-flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" name="include_worker" id="includeWorker" value="1" onchange="updateLivePreview()" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 dark:bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0033a0]"></div>
                        <span class="text-xs font-bold text-slate-800 dark:text-gray-200">Include Assigned Worker (Col H)</span>
                    </label>
                </div>

            </div>

            <!-- Row 3: Action Buttons -->
            <div class="flex flex-wrap items-center justify-end gap-2.5 pt-2 border-t border-gray-100 dark:border-zinc-800">
                <button type="button" onclick="resetReportForm()" class="px-3.5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl border border-gray-200 dark:border-zinc-700 transition">
                    Reset Defaults
                </button>

                <button type="button" id="printReportBtn" onclick="printOfficialReport()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-white text-xs font-bold rounded-xl transition shadow-md flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Official Report (A4)</span>
                </button>

                <button type="submit" id="exportBtn" class="px-5 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-xl transition shadow-md flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span id="exportBtnText">Export Excel Sheet (.xlsx)</span>
                </button>
            </div>
        </form>

        <div class="pt-6 border-t border-gray-100 dark:border-zinc-800">
            <!-- Preview Section Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div class="text-xs font-extrabold text-[#0033a0] dark:text-blue-400 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span id="previewTitle">Official Accomplishment Report Preview</span>
                </div>

                <!-- Tabs for Summary Report Preview (Only shown when Summary is selected) -->
                <div id="summaryPreviewTabs" class="hidden flex items-center gap-1.5 bg-gray-100 dark:bg-zinc-800 p-1 rounded-xl text-xs font-bold">
                    <button type="button" onclick="setSummaryTab('photo1')" id="tabBtnPhoto1" class="px-3 py-1.5 rounded-lg bg-white dark:bg-zinc-900 shadow-xs text-[#0033a0] dark:text-blue-400 transition">
                        Photo 1: Summary Table
                    </button>
                    <button type="button" onclick="setSummaryTab('photo2')" id="tabBtnPhoto2" class="px-3 py-1.5 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 transition">
                        Photo 2: CS Survey Matrix
                    </button>
                </div>

                <span id="previewCountBadge" class="text-[11px] font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-zinc-800 px-2.5 py-1 rounded-full">
                    0 Completed Jobs
                </span>
            </div>

            <!-- Live Document Interactive Preview Container (Matches Mockup) -->
            <div id="previewFrame" class="max-w-4xl mx-auto bg-slate-50 dark:bg-zinc-950 border-2 border-gray-300 dark:border-zinc-800 rounded-2xl p-6 shadow-inner min-h-[380px] flex flex-col justify-between">
                
                <!-- Live Dynamic Report Document -->
                <div id="reportPreviewContent" class="space-y-4">
                    <!-- Dynamic HTML Generated via JS -->
                </div>

                <!-- Page Footer -->
                <div class="text-[11px] font-bold text-gray-400 text-center border-t border-gray-200 dark:border-zinc-800 pt-3 mt-4">
                    Official Document Format &bull; Bicol University General Services Office
                </div>
            </div>

            <!-- Worker Accomplishment Summary Card (On-Page Only) -->
            <div id="workerAccomplishmentCard" class="max-w-4xl mx-auto mt-6 bg-slate-50/70 dark:bg-zinc-800/40 rounded-2xl border border-gray-200 dark:border-zinc-700/80 p-5 sm:p-6 shadow-2xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-200 dark:border-zinc-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-[#0033a0] dark:text-blue-300 flex items-center justify-center flex-shrink-0 font-bold shadow-2xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white tracking-tight">
                                Worker Accomplishment Summary
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Completed jobs accomplished by each maintenance worker for the selected period
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white dark:bg-zinc-800 text-[#0033a0] dark:text-blue-300 border border-blue-200 dark:border-blue-800/60 shadow-2xs">
                            <span id="summaryTotalWorkers" class="font-black">0</span> Personnel
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white dark:bg-zinc-800 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 shadow-2xs">
                            <span id="summaryTotalJobs" class="font-black">0</span> Total Jobs Done
                        </span>
                    </div>
                </div>

                <!-- Search / Filter for Worker Summary -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                    <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                        Ranked by total finished tasks completed
                    </div>
                    <div class="relative w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-gray-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" id="workerSummarySearchInput" oninput="filterWorkerSummary(this.value)" placeholder="Search worker or team..." class="w-full text-xs pl-8 pr-3 py-1.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl focus:outline-none focus:border-[#0033a0] text-slate-800 dark:text-gray-200 placeholder-gray-400 shadow-2xs">
                    </div>
                </div>

                <!-- Table Content -->
                <div id="workerAccomplishmentContent" class="pt-1">
                    <!-- Injected dynamically via JS -->
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Reports History Table (Matches Mockup) -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm space-y-4">
        
        <div>
            <h2 class="text-lg font-black text-[#0033a0] dark:text-blue-400 tracking-tight">
                Recent Reports
            </h2>
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-0.5">
                Audit Trail & Generated Reports History
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-zinc-800 text-[11px] font-bold text-[#0033a0] dark:text-blue-400 uppercase tracking-wider">
                        <th class="py-3 px-4">REPORT NAME</th>
                        <th class="py-3 px-4">TYPE</th>
                        <th class="py-3 px-4">DATE GENERATED</th>
                        <th class="py-3 px-4">GENERATED BY</th>
                        <th class="py-3 px-4">FORMAT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-xs">
                    @forelse($recentReports as $index => $log)
                        @php
                            $reportTitle = match(true) {
                                str_contains(strtolower($log->action), 'accomplishment') => 'ACCOMPLISHMENT REPORT',
                                str_contains(strtolower($log->action), 'summary') => 'REQUEST SUMMARY REPORT',
                                str_contains(strtolower($log->action), 'worker') => 'WORKER PERFORMANCE REPORT',
                                default => 'SYSTEM MAINTENANCE REPORT'
                            };
                        @endphp
                        <tr class="hover:bg-blue-50/50 dark:hover:bg-zinc-800/50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white uppercase">
                                {{ $reportTitle }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700 dark:text-gray-300">
                                Accomplishment Report
                            </td>
                            <td class="py-3.5 px-4 text-gray-500">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('F d, Y h:i A') }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-gray-200">
                                {{ $log->user->first_name ?? 'Administrator' }} {{ $log->user->last_name ?? '' }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-emerald-600 dark:text-emerald-400">
                                Excel (.xlsx)
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-xs text-gray-400">
                                No recent reports generated yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-100 dark:border-zinc-800 text-xs">
            <div class="text-gray-400 font-medium">
                Showing {{ $recentReports->firstItem() ?? 0 }} to {{ $recentReports->lastItem() ?? 0 }} of {{ $recentReports->total() }} reports
            </div>
            <div class="flex items-center gap-1.5">
                {{ $recentReports->links() }}
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    // Real finished requests from database for Accomplishment Reports Live Preview
    const completedDbRequests = @json($previewRequests);
    const teamLeadersMap = @json($teamLeaders ?? []);
    let activeSummaryTab = 'photo1';

    const definedSections = [
        'PLUMBING SERVICES',
        'ELECTRICAL SERVICES',
        'CARPENTRY/MASONRY SERVICES',
        'LANDSCAPING SERVICES',
        'JANITORIAL SERVICES',
        'PAINTING SERVICES',
        'MANPOWER SERVICES FOR SPECIAL EVENTS'
    ];

    let currentWorkerSummaryList = [];

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getTeamBadgeHTML(service) {
        const s = (service || '').toLowerCase();
        let colorClass = 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700';
        if (s.includes('carpentry') || s.includes('electrical') || s.includes('masonry') || s.includes('mechanical')) {
            colorClass = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800/50';
        } else if (s.includes('plumbing')) {
            colorClass = 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-900/30 dark:text-sky-300 dark:border-sky-800/50';
        } else if (s.includes('painting') || s.includes('paint')) {
            colorClass = 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-900/30 dark:text-violet-300 dark:border-violet-800/50';
        } else if (s.includes('landscaping')) {
            colorClass = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800/50';
        } else if (s.includes('janitorial')) {
            colorClass = 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-900/30 dark:text-teal-300 dark:border-teal-800/50';
        } else if (s.includes('manpower') || s.includes('event')) {
            colorClass = 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800/50';
        }
        return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-semibold border ${colorClass}">${escapeHtml(service || 'General Maintenance')}</span>`;
    }

    function getWorkerInitials(name) {
        if (!name) return 'W';
        const parts = name.trim().split(/\s+/);
        if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    function renderWorkerAccomplishmentSummary(workerSummaryList) {
        currentWorkerSummaryList = workerSummaryList || [];
        const searchInput = document.getElementById('workerSummarySearchInput');
        if (searchInput) searchInput.value = '';

        const totalWorkersEl = document.getElementById('summaryTotalWorkers');
        const totalJobsEl = document.getElementById('summaryTotalJobs');
        if (totalWorkersEl) totalWorkersEl.textContent = currentWorkerSummaryList.length;
        if (totalJobsEl) totalJobsEl.textContent = currentWorkerSummaryList.reduce((sum, w) => sum + (w.count || 0), 0);

        renderWorkerAccomplishmentRows(currentWorkerSummaryList);
    }

    function filterWorkerSummary(searchTerm) {
        const term = (searchTerm || '').trim().toLowerCase();
        if (!term) {
            renderWorkerAccomplishmentRows(currentWorkerSummaryList);
            return;
        }
        const filtered = currentWorkerSummaryList.filter(w => 
            (w.name || '').toLowerCase().includes(term) || 
            (w.service || '').toLowerCase().includes(term)
        );
        renderWorkerAccomplishmentRows(filtered);
    }

    function renderWorkerAccomplishmentRows(workers) {
        const contentEl = document.getElementById('workerAccomplishmentContent');
        if (!contentEl) return;

        if (!workers || workers.length === 0) {
            contentEl.innerHTML = `
                <div class="py-8 text-center text-xs text-gray-400 dark:text-gray-500 italic bg-white dark:bg-zinc-900 rounded-xl border border-gray-200 dark:border-zinc-800">
                    No worker accomplishments found matching criteria.
                </div>
            `;
            return;
        }

        const maxCount = currentWorkerSummaryList[0]?.count || 1;

        let rowsHTML = workers.map((w, idx) => {
            const initials = getWorkerInitials(w.name);
            const pct = Math.min(100, Math.round((w.count / maxCount) * 100));

            let rankBadge = `<span class="text-gray-400 dark:text-gray-500 font-bold text-xs inline-block w-6 text-center">${idx + 1}</span>`;
            if (idx === 0) {
                rankBadge = `<span class="w-6 h-6 rounded-full bg-amber-400 text-slate-900 font-black inline-flex items-center justify-center text-[11px] shadow-2xs">1</span>`;
            } else if (idx === 1) {
                rankBadge = `<span class="w-6 h-6 rounded-full bg-slate-300 text-slate-800 dark:bg-zinc-600 dark:text-zinc-100 font-bold inline-flex items-center justify-center text-[11px]">2</span>`;
            } else if (idx === 2) {
                rankBadge = `<span class="w-6 h-6 rounded-full bg-amber-700 text-amber-100 font-bold inline-flex items-center justify-center text-[11px]">3</span>`;
            }

            return `
                <tr class="hover:bg-blue-50/40 dark:hover:bg-zinc-800/50 transition">
                    <td class="py-3 px-3.5 text-center">
                        ${rankBadge}
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/50 text-[#0033a0] dark:text-blue-300 text-[10px] font-black flex items-center justify-center flex-shrink-0">
                                ${initials}
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white text-xs tracking-tight">
                                ${escapeHtml(w.name)}
                            </span>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        ${getTeamBadgeHTML(w.service)}
                    </td>
                    <td class="py-3 px-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <div class="hidden sm:block w-20 bg-gray-100 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-[#0033a0] dark:bg-blue-500 h-1.5 rounded-full" style="width: ${pct}%"></div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-blue-50 dark:bg-blue-900/40 text-[#0033a0] dark:text-blue-300 border border-blue-200 dark:border-blue-800/60 shadow-2xs">
                                ${w.count} <span class="font-semibold text-[10px] ml-1 text-slate-600 dark:text-gray-300">${w.count === 1 ? 'job' : 'jobs'}</span>
                            </span>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        contentEl.innerHTML = `
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-zinc-800">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-zinc-800/80 text-[10.5px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-zinc-800">
                            <th class="py-2.5 px-3.5 w-12 text-center">Rank</th>
                            <th class="py-2.5 px-4">Maintenance Personnel</th>
                            <th class="py-2.5 px-4">Service Team / Unit</th>
                            <th class="py-2.5 px-4 text-right">Accomplishment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                        ${rowsHTML}
                    </tbody>
                </table>
            </div>
        `;
    }

    function setSummaryTab(tab) {
        activeSummaryTab = tab;
        const btn1 = document.getElementById('tabBtnPhoto1');
        const btn2 = document.getElementById('tabBtnPhoto2');
        if (tab === 'photo1') {
            btn1.className = 'px-3 py-1.5 rounded-lg bg-white dark:bg-zinc-900 shadow-xs text-[#0033a0] dark:text-blue-400 font-bold transition';
            btn2.className = 'px-3 py-1.5 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 font-bold transition';
        } else {
            btn2.className = 'px-3 py-1.5 rounded-lg bg-white dark:bg-zinc-900 shadow-xs text-[#0033a0] dark:text-blue-400 font-bold transition';
            btn1.className = 'px-3 py-1.5 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 font-bold transition';
        }
        updateLivePreview();
    }

    function setReportType(val) {
        const input = document.getElementById('reportType');
        if (input) input.value = val;

        const btnAccomplish = document.getElementById('btnReportTypeAccomplishment');
        const btnSummary = document.getElementById('btnReportTypeSummary');

        const activeClasses = 'bg-[#0033a0] text-white border-[#0033a0] shadow-sm font-bold';
        const inactiveClasses = 'bg-white dark:bg-zinc-900 text-slate-700 dark:text-gray-300 border-gray-200 dark:border-zinc-700 hover:border-[#0033a0]/50 font-semibold';

        if (btnAccomplish && btnSummary) {
            if (val === 'Accomplishment Report') {
                btnAccomplish.className = 'w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl border-2 text-xs sm:text-sm transition-all shadow-xs cursor-pointer ' + activeClasses;
                btnSummary.className = 'w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl border-2 text-xs sm:text-sm transition-all shadow-xs cursor-pointer ' + inactiveClasses;
            } else {
                btnSummary.className = 'w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl border-2 text-xs sm:text-sm transition-all shadow-xs cursor-pointer ' + activeClasses;
                btnAccomplish.className = 'w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl border-2 text-xs sm:text-sm transition-all shadow-xs cursor-pointer ' + inactiveClasses;
            }
        }

        handleReportTypeChange();
    }

    function handleReportTypeChange() {
        const reportType = document.getElementById('reportType').value;
        const tabs = document.getElementById('summaryPreviewTabs');
        const title = document.getElementById('previewTitle');
        const exportText = document.getElementById('exportBtnText');

        if (reportType === 'Summary of Accomplishment & Clientele Satisfaction Survey') {
            if (tabs) tabs.classList.remove('hidden');
            if (title) title.textContent = 'Summary & CS Survey Preview';
            if (exportText) exportText.textContent = 'Export Excel (.xlsx) [2 Sheets]';
        } else {
            if (tabs) tabs.classList.add('hidden');
            if (title) title.textContent = 'Official Accomplishment Report Preview';
            if (exportText) exportText.textContent = 'Export Excel Sheet (.xlsx)';
        }
        updateLivePreview();
    }

    function printOfficialReport() {
        const form = document.getElementById('reportForm');
        const formData = new FormData(form);
        const params = new URLSearchParams();
        for (let [key, val] of formData.entries()) {
            if (key !== '_token') {
                params.append(key, val);
            }
        }
        const printUrl = "{{ route('admin.reports.print') }}?" + params.toString();
        window.open(printUrl, '_blank');
    }

    function handlePeriodChange() {
        const year = document.getElementById('reportYear').value || new Date().getFullYear();
        const period = document.getElementById('reportPeriod').value;
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');

        if (period === 'sem1') {
            startDateInput.value = `${year}-01-01`;
            endDateInput.value = `${year}-06-30`;
        } else if (period === 'sem2') {
            startDateInput.value = `${year}-07-01`;
            endDateInput.value = `${year}-12-31`;
        } else if (period === 'year') {
            startDateInput.value = `${year}-01-01`;
            endDateInput.value = `${year}-12-31`;
        }
        updateLivePreview();
    }

    function handleCustomDateInput() {
        document.getElementById('reportPeriod').value = 'custom';
        updateLivePreview();
    }

    function resetReportForm() {
        document.getElementById('reportForm').reset();
        document.getElementById('reportYear').value = new Date().getFullYear();
        document.getElementById('reportPeriod').value = (new Date().getMonth() + 1 <= 6) ? 'sem1' : 'sem2';
        if (document.getElementById('workerId')) document.getElementById('workerId').value = '';
        if (document.getElementById('includeWorker')) document.getElementById('includeWorker').checked = false;
        setReportType('Accomplishment Report');
        handlePeriodChange();
    }

    function updateLivePreview() {
        const reportType = document.getElementById('reportType')?.value || 'Accomplishment Report';
        const year = document.getElementById('reportYear').value || new Date().getFullYear();
        const period = document.getElementById('reportPeriod').value;
        const startDateVal = document.getElementById('startDate').value;
        const endDateVal = document.getElementById('endDate').value;

        const monthNames = ["JANUARY", "FEBRUARY", "MARCH", "APRIL", "MAY", "JUNE", "JULY", "AUGUST", "SEPTEMBER", "OCTOBER", "NOVEMBER", "DECEMBER"];
        
        let monthRangeHeader = 'JANUARY TO JUNE';
        if (period === 'sem1') {
            monthRangeHeader = 'JANUARY TO JUNE';
        } else if (period === 'sem2') {
            monthRangeHeader = 'JULY TO DECEMBER';
        } else if (period === 'year') {
            monthRangeHeader = 'JANUARY TO DECEMBER';
        } else if (startDateVal && endDateVal) {
            const d1 = new Date(startDateVal);
            const d2 = new Date(endDateVal);
            if (!isNaN(d1.getTime()) && !isNaN(d2.getTime())) {
                const m1 = monthNames[d1.getMonth()];
                const m2 = monthNames[d2.getMonth()];
                monthRangeHeader = (m1 === m2) ? m1 : `${m1} TO ${m2}`;
            }
        }

        const categoryOpt = document.getElementById('categoryId');
        const categoryId  = categoryOpt?.value || '';
        const categoryName = categoryId ? (categoryOpt.options[categoryOpt.selectedIndex]?.text || 'MAINTENANCE SECTION') : 'ALL SERVICE UNITS';

        const workerId = document.getElementById('workerId')?.value || '';
        const includeWorker = document.getElementById('includeWorker')?.checked || false;

        let leaderName = 'GSO MAINTENANCE TEAM LEADERS';
        let sectionName = 'General Services Office';
        if (categoryId && teamLeadersMap[categoryId]) {
            leaderName = teamLeadersMap[categoryId].leader_name || 'TEAM LEADER';
            sectionName = teamLeadersMap[categoryId].section_name || categoryName;
        } else if (categoryName && categoryName !== 'ALL SERVICE UNITS') {
            sectionName = categoryName;
        }

        const previewContainer = document.getElementById('reportPreviewContent');

        // Filter finished requests from database based on inputs
        let filteredRequests = completedDbRequests.filter(req => {
            if (categoryId && String(req.category_id) !== String(categoryId)) {
                return false;
            }
            if (workerId && (!req.worker_ids || !req.worker_ids.includes(parseInt(workerId)))) {
                return false;
            }
            if (startDateVal && req.submitted_at && req.submitted_at < startDateVal) {
                return false;
            }
            if (endDateVal && req.submitted_at && req.submitted_at > endDateVal) {
                return false;
            }
            return true;
        });

        // If Accomplishment Report:
        if (reportType === 'Accomplishment Report') {
            const categoryOrderMap = { 'CMS': 1, 'PLS': 2, 'PAS': 3, 'PAINT': 3, 'PAINTING': 3, 'JS': 4, 'LS': 5, 'MAN': 6 };
            filteredRequests.sort((a, b) => {
                const orderA = a.category_order || categoryOrderMap[a.prefix] || 7;
                const orderB = b.category_order || categoryOrderMap[b.prefix] || 7;
                if (orderA !== orderB) return orderA - orderB;
                if (a.submitted_at !== b.submitted_at) return (a.submitted_at || '').localeCompare(b.submitted_at || '');
                return (a.request_id || 0) - (b.request_id || 0);
            });

            const countBadge = document.getElementById('previewCountBadge');
            if (countBadge) {
                countBadge.textContent = `${filteredRequests.length} Finished Job${filteredRequests.length === 1 ? '' : 's'}`;
            }

            let tableRowsHTML = '';
            if (filteredRequests.length > 0) {
                filteredRequests.forEach((req, index) => {
                    const seqNumber = String(index + 1).padStart(3, '0');
                    const reqNum = `${req.prefix}-${seqNumber}`;
                    const office = req.location || 'N/A';
                    const reqDate = req.request_date_formatted || '';
                    const startedDate = req.started_date || reqDate;
                    const completionDate = req.completion_date || reqDate;
                    const ratingVal = req.rating || '—';

                    tableRowsHTML += `
                        <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/60 transition text-[11px]">
                            <td class="border border-black px-2 py-2 text-center font-bold font-mono text-blue-900 dark:text-blue-300">${reqNum}</td>
                            <td class="border border-black px-2 py-2 text-center font-semibold text-slate-800 dark:text-gray-200">${office}</td>
                            <td class="border border-black px-2 py-2">
                                <div class="font-bold text-slate-900 dark:text-white">${req.title || ''}</div>
                                ${req.description ? `<div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">${req.description}</div>` : ''}
                            </td>
                            <td class="border border-black px-2 py-2 text-center font-medium text-slate-700 dark:text-gray-300">${reqDate}</td>
                            <td class="border border-black px-2 py-2 text-center font-medium text-slate-700 dark:text-gray-300">${startedDate}</td>
                            <td class="border border-black px-2 py-2 text-center font-bold text-emerald-700 dark:text-emerald-400">${completionDate}</td>
                            <td class="border border-black px-2 py-2 text-center font-black text-slate-900 dark:text-white">${ratingVal}</td>
                            ${includeWorker ? `<td class="border border-black px-2 py-2 text-center text-[10px] font-medium text-slate-800 dark:text-gray-200">${req.assigned_workers || 'Unassigned'}</td>` : ''}
                        </tr>
                    `;
                });
            } else {
                tableRowsHTML = `
                    <tr>
                        <td colspan="${includeWorker ? 8 : 7}" class="border border-black py-8 text-center text-xs text-gray-400 italic">
                            No finished service requests found for ${categoryName.toUpperCase()} in ${monthRangeHeader} ${year}.
                        </td>
                    </tr>
                `;
            }

            // Automated Worker Accomplishment Counter Summary (service)(worker name)(number of jobs done)
            const workerStatsMap = {};
            filteredRequests.forEach(req => {
                if (req.worker_details && req.worker_details.length > 0) {
                    req.worker_details.forEach(w => {
                        if (!workerStatsMap[w.worker_id]) {
                            workerStatsMap[w.worker_id] = {
                                worker_id: w.worker_id,
                                name: w.name,
                                service: w.service,
                                count: 0
                            };
                        }
                        workerStatsMap[w.worker_id].count++;
                    });
                }
            });
            const workerSummaryList = Object.values(workerStatsMap).sort((a, b) => {
                if (b.count !== a.count) return b.count - a.count;
                return a.name.localeCompare(b.name);
            });

            // Update dedicated on-page Worker Accomplishment Summary Card
            const workerCard = document.getElementById('workerAccomplishmentCard');
            if (workerCard) {
                workerCard.classList.remove('hidden');
                renderWorkerAccomplishmentSummary(workerSummaryList);
            }

            previewContainer.innerHTML = `
                <div class="bg-white dark:bg-zinc-900 border-2 border-black rounded-lg p-5 shadow-sm font-sans">
                    <div class="text-center mb-4 space-y-1">
                        <h2 class="text-base font-black text-black dark:text-white uppercase tracking-wider font-serif">
                            ${year} ACCOMPLISHMENT REPORT
                        </h2>
                        <div class="text-xs font-bold text-black dark:text-gray-300 uppercase">
                            MAINTENANCE SECTION: ${categoryName.toUpperCase()}
                        </div>
                        <div class="text-xs font-bold text-black dark:text-gray-400 uppercase tracking-wide">
                            ${monthRangeHeader}
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border-2 border-black text-xs">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-zinc-800 text-black dark:text-white text-[10px] font-bold uppercase">
                                    <th rowspan="2" class="border border-black px-2 py-1.5 text-center ${includeWorker ? 'w-[12%]' : 'w-[15%]'}">REQUISITION<br>NUMBER</th>
                                    <th rowspan="2" class="border border-black px-2 py-1.5 text-center ${includeWorker ? 'w-[14%]' : 'w-[16%]'}">OFFICE/<br>UNIT</th>
                                    <th rowspan="2" class="border border-black px-2 py-1.5 text-center ${includeWorker ? 'w-[25%]' : 'w-[29%]'}">TASK DETAILS</th>
                                    <th colspan="3" class="border border-black px-2 py-1 text-center ${includeWorker ? 'w-[21%]' : 'w-[25%]'}">DATES</th>
                                    <th rowspan="2" class="border border-black px-2 py-1.5 text-center ${includeWorker ? 'w-[13%]' : 'w-[15%]'}">CLIENTELE<br>SATISFACTION<br>RATING</th>
                                    ${includeWorker ? '<th rowspan="2" class="border border-black px-2 py-1.5 text-center w-[15%]">WORKER<br>ASSIGNED</th>' : ''}
                                </tr>
                                <tr class="bg-gray-100 dark:bg-zinc-800 text-black dark:text-white text-[10px] font-bold uppercase">
                                    <th class="border border-black px-2 py-1 text-center">REQUEST</th>
                                    <th class="border border-black px-2 py-1 text-center">STARTED</th>
                                    <th class="border border-black px-2 py-1 text-center">COMPLETION</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRowsHTML}
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 pt-4 space-y-6 text-left text-xs text-black dark:text-gray-200 border-t border-gray-100 dark:border-zinc-800">
                        <div class="space-y-0.5">
                            <div class="text-[11px] text-gray-600 dark:text-gray-400">Prepared By:</div>
                            <div class="pt-3 font-black uppercase text-xs tracking-wide text-black dark:text-white">${leaderName}</div>
                            <div class="text-[11px] text-gray-700 dark:text-gray-300">Team Leader</div>
                            <div class="text-[11px] text-gray-700 dark:text-gray-300">${sectionName}</div>
                        </div>
                        <div class="space-y-0.5">
                            <div class="text-[11px] text-gray-600 dark:text-gray-400">Certified True and Correct:</div>
                            <div class="pt-3 font-black uppercase text-xs tracking-wide text-black dark:text-white">REY A. PADILLA</div>
                            <div class="text-[11px] text-gray-700 dark:text-gray-300">Administrative Officer I</div>
                            <div class="text-[11px] text-gray-700 dark:text-gray-300">Head, General Services Office</div>
                        </div>
                        <div class="space-y-0.5">
                            <div class="text-[11px] text-gray-600 dark:text-gray-400">Noted By:</div>
                            <div class="pt-3 font-black uppercase text-xs tracking-wide text-black dark:text-white">MA. MYRA A. CAPARAS</div>
                            <div class="text-[11px] text-gray-700 dark:text-gray-300">Acting Chief Administrative Officer for</div>
                            <div class="text-[11px] text-gray-700 dark:text-gray-300">Administrative Services Division</div>
                        </div>
                    </div>
                </div>
            `;
            return;
        }

        // Hide worker accomplishment summary for Summary of Accomplishment & Clientele Satisfaction Survey
        const workerCard = document.getElementById('workerAccomplishmentCard');
        if (workerCard) {
            workerCard.classList.add('hidden');
        }

        // Otherwise: Summary of Accomplishment & Clientele Satisfaction Survey
        const countBadge = document.getElementById('previewCountBadge');
        if (countBadge) {
            countBadge.textContent = `${filteredRequests.length} Finished Job${filteredRequests.length === 1 ? '' : 's'} Across Sections`;
        }

        // Compute section aggregations
        const sectionStats = {};
        definedSections.forEach(secName => {
            const secReqs = filteredRequests.filter(r => (r.section_name || '') === secName);
            const evals = secReqs.filter(r => r.function_ratings);

            const raters = [];
            let rNum = 1;
            const counts = { 5: { quality: 0, attitude: 0, safety: 0, time: 0, housekeeping: 0 }, 4: { quality: 0, attitude: 0, safety: 0, time: 0, housekeeping: 0 } };
            const points = { 5: { quality: 0, attitude: 0, safety: 0, time: 0, housekeeping: 0 }, 4: { quality: 0, attitude: 0, safety: 0, time: 0, housekeeping: 0 } };
            const totals = { quality: 0, attitude: 0, safety: 0, time: 0, housekeeping: 0 };

            evals.forEach(req => {
                const fr = req.function_ratings;
                raters.push(Object.assign({ rater_no: rNum++ }, fr));
                ['quality', 'attitude', 'safety', 'time', 'housekeeping'].forEach(f => {
                    let score = parseInt(fr[f] || 5);
                    if (score > 5) score = 5;
                    if (score < 1) score = 1;
                    if (score === 5 || score === 4) {
                        counts[score][f] = (counts[score][f] || 0) + 1;
                        points[score][f] = (points[score][f] || 0) + score;
                    }
                    totals[f] = (totals[f] || 0) + score;
                });
            });

            const rCount = raters.length;
            const means = { quality: 0, attitude: 0, safety: 0, time: 0, housekeeping: 0 };
            let overallMean = 0;
            if (rCount > 0) {
                ['quality', 'attitude', 'safety', 'time', 'housekeeping'].forEach(f => {
                    means[f] = parseFloat((totals[f] / rCount).toFixed(2));
                });
                overallMean = parseFloat(((means.quality + means.attitude + means.safety + means.time + means.housekeeping) / 5).toFixed(2));
            }

            sectionStats[secName] = {
                name: secName,
                total_requests: secReqs.length,
                cs_result: overallMean,
                raters: raters,
                counts: counts,
                points: points,
                means: means,
                overall_mean: overallMean
            };
        });

        if (activeSummaryTab === 'photo1') {
            // PHOTO 1: SUMMARY TABLE
            let summaryTablesHTML = '';
            definedSections.forEach(secName => {
                const sec = sectionStats[secName];
                summaryTablesHTML += `
                    <table class="w-full border-collapse border border-black text-xs mb-3">
                        <tr class="bg-gray-50 dark:bg-zinc-800">
                            <td colspan="2" class="border border-black px-3 py-1.5 font-bold uppercase text-slate-900 dark:text-white">
                                Maintenance Section: ${sec.name}
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-black px-3 py-1.5 w-[75%] text-slate-800 dark:text-gray-200">
                                Total Number of Request Received:
                            </td>
                            <td class="border border-black px-3 py-1.5 text-center font-bold text-slate-900 dark:text-white">
                                ${sec.total_requests}
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-black px-3 py-1.5 w-[75%] text-slate-800 dark:text-gray-200">
                                Clientele Satisfaction Survey Result:
                            </td>
                            <td class="border border-black px-3 py-1.5 text-center font-bold text-slate-900 dark:text-white">
                                ${sec.cs_result > 0 ? sec.cs_result.toFixed(2) : '0'}
                            </td>
                        </tr>
                    </table>
                `;
            });

            previewContainer.innerHTML = `
                <div class="bg-white dark:bg-zinc-900 border-2 border-black rounded-lg p-6 shadow-sm font-serif">
                    <div class="text-center mb-6 space-y-1">
                        <h2 class="text-base font-bold text-black dark:text-white uppercase tracking-wide leading-tight">
                            Summary of Accomplishment Report and Clientele<br>Satisfaction Survey
                        </h2>
                        <div class="text-xs font-bold text-black dark:text-gray-300 uppercase mt-1">
                            ${monthRangeHeader} ${year}
                        </div>
                    </div>

                    <div class="max-w-xl mx-auto">
                        ${summaryTablesHTML}

                        <div class="mt-8 pt-4 flex justify-between items-start text-xs text-black dark:text-gray-200 font-serif">
                            <div class="w-1/2">
                                <p>Prepared by:</p>
                                <div class="h-8"></div>
                                <p class="font-bold underline text-sm">REY A. PADILLA</p>
                                <p class="text-[11px]">Administrative Officer II</p>
                                <p class="text-[11px]">Head, General Services Office</p>
                            </div>
                            <div class="w-1/2">
                                <p>Certified Correct:</p>
                                <div class="h-8"></div>
                                <p class="font-bold underline text-sm">MA. MYRA A. CAPARAS</p>
                                <p class="text-[11px]">Acting Chief Administrative Officer</p>
                                <p class="text-[11px]">For Administrative Services Division</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            // PHOTO 2: CS SURVEY MATRIX
            let surveySectionsHTML = '';
            definedSections.forEach(secName => {
                const sec = sectionStats[secName];
                if (sec.raters.length === 0 && categoryId && !secName.includes(categoryName.toUpperCase())) {
                    return;
                }

                let ratersRows = '';
                if (sec.raters.length > 0) {
                    sec.raters.forEach(r => {
                        ratersRows += `
                            <tr>
                                <td class="border border-black px-2 py-1 font-bold text-center">${r.rater_no}</td>
                                <td class="border border-black px-2 py-1 text-center">${r.quality}</td>
                                <td class="border border-black px-2 py-1 text-center">${r.attitude}</td>
                                <td class="border border-black px-2 py-1 text-center">${r.safety}</td>
                                <td class="border border-black px-2 py-1 text-center">${r.time}</td>
                                <td class="border border-black px-2 py-1 text-center">${r.housekeeping}</td>
                            </tr>
                        `;
                    });
                } else {
                    ratersRows = `
                        <tr>
                            <td colspan="6" class="border border-black py-4 text-center italic text-gray-400">
                                No survey responses recorded for this section in the selected period.
                            </td>
                        </tr>
                    `;
                }

                surveySectionsHTML += `
                    <div class="border border-gray-300 dark:border-zinc-700 rounded-lg p-4 mb-6 bg-white dark:bg-zinc-900">
                        <div class="text-center mb-3">
                            <h3 class="text-sm font-bold uppercase text-black dark:text-white">Clientele Satisfaction Survey</h3>
                            <p class="text-xs font-bold uppercase text-black dark:text-gray-300">Maintenance Section: ${sec.name}</p>
                            <p class="text-[11px] font-bold uppercase text-gray-500">${monthRangeHeader} ${year}</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse border border-black text-xs text-center mb-3">
                                <thead>
                                    <tr class="bg-gray-100 dark:bg-zinc-800 font-bold uppercase text-[10px]">
                                        <th rowspan="2" class="border border-black px-2 py-1 w-[16%]">Number of<br>Rater</th>
                                        <th colspan="5" class="border border-black px-2 py-1">Functions</th>
                                    </tr>
                                    <tr class="bg-gray-100 dark:bg-zinc-800 font-bold uppercase text-[10px]">
                                        <th class="border border-black px-2 py-1 w-[16.8%]">Quality of<br>Service</th>
                                        <th class="border border-black px-2 py-1 w-[16.8%]">Attitude</th>
                                        <th class="border border-black px-2 py-1 w-[16.8%]">Safety<br>Precautions<br>Awareness</th>
                                        <th class="border border-black px-2 py-1 w-[16.8%]">Time<br>Bounded</th>
                                        <th class="border border-black px-2 py-1 w-[16.8%]">Workplace<br>Housekeeping</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${ratersRows}
                                </tbody>
                            </table>
                        </div>

                        ${sec.raters.length > 0 ? `
                            <div class="mt-2 text-xs font-mono">
                                <table class="w-full border-collapse text-center mb-2">
                                    <tr class="text-gray-700 dark:text-gray-300 font-semibold">
                                        <td class="w-[16%] font-bold">5</td>
                                        <td class="w-[16.8%]">${sec.counts[5].quality}</td>
                                        <td class="w-[16.8%]">${sec.counts[5].attitude}</td>
                                        <td class="w-[16.8%]">${sec.counts[5].safety}</td>
                                        <td class="w-[16.8%]">${sec.counts[5].time}</td>
                                        <td class="w-[16.8%]">${sec.counts[5].housekeeping}</td>
                                    </tr>
                                    <tr class="text-gray-700 dark:text-gray-300 font-semibold">
                                        <td class="font-bold">4</td>
                                        <td>${sec.counts[4].quality}</td>
                                        <td>${sec.counts[4].attitude}</td>
                                        <td>${sec.counts[4].safety}</td>
                                        <td>${sec.counts[4].time}</td>
                                        <td>${sec.counts[4].housekeeping}</td>
                                    </tr>
                                    <tr><td colspan="6" class="h-2"></td></tr>
                                    <tr class="text-gray-700 dark:text-gray-300 font-semibold">
                                        <td class="font-bold">5</td>
                                        <td>${sec.points[5].quality}</td>
                                        <td>${sec.points[5].attitude}</td>
                                        <td>${sec.points[5].safety}</td>
                                        <td>${sec.points[5].time}</td>
                                        <td>${sec.points[5].housekeeping}</td>
                                    </tr>
                                    <tr class="text-gray-700 dark:text-gray-300 font-semibold">
                                        <td class="font-bold">4</td>
                                        <td>${sec.points[4].quality}</td>
                                        <td>${sec.points[4].attitude}</td>
                                        <td>${sec.points[4].safety}</td>
                                        <td>${sec.points[4].time}</td>
                                        <td>${sec.points[4].housekeeping}</td>
                                    </tr>
                                    <tr><td colspan="6" class="h-2"></td></tr>
                                    <tr class="font-bold text-slate-900 dark:text-white border-t border-b border-black">
                                        <td></td>
                                        <td>${sec.means.quality.toFixed(2)}</td>
                                        <td>${sec.means.attitude.toFixed(2)}</td>
                                        <td>${sec.means.safety.toFixed(2)}</td>
                                        <td>${sec.means.time.toFixed(2)}</td>
                                        <td>${sec.means.housekeeping.toFixed(2)}</td>
                                    </tr>
                                </table>

                                <div class="flex justify-end mt-3">
                                    <div class="inline-flex border-2 border-black font-bold font-sans text-xs">
                                        <div class="px-4 py-1 border-r-2 border-black bg-gray-50 dark:bg-zinc-800">${sec.name}</div>
                                        <div class="px-4 py-1 text-center min-w-[70px] bg-blue-50 dark:bg-blue-950/40 text-[#0033a0] dark:text-blue-400 font-black">${sec.overall_mean.toFixed(2)}</div>
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                `;
            });

            previewContainer.innerHTML = surveySectionsHTML;
        }
    }

    async function handleExportSubmit(e) {
        if (e) {
            e.preventDefault();
        }

        const btn = document.getElementById('exportBtn');
        const form = document.getElementById('reportForm');
        if (!btn || !form) return;

        const reportType = document.getElementById('reportType')?.value || 'Accomplishment Report';
        const defaultText = (reportType === 'Summary of Accomplishment & Clientele Satisfaction Survey') 
            ? 'Export Excel (.xlsx) [2 Sheets]' 
            : 'Export Excel Sheet (.xlsx)';

        const originalHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span id="exportBtnText">${defaultText}</span>
        `;

        // Loading state
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Generating & Downloading...</span>
        `;
        btn.classList.add('opacity-75', 'cursor-wait');

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}'
                }
            });

            if (!response.ok) {
                throw new Error('Export download failed');
            }

            const blob = await response.blob();
            
            let filename = reportType.includes('Summary') ? 'Summary_Accomplishment_and_CS_Survey.xlsx' : 'Accomplishment_Report.xlsx';
            const disposition = response.headers.get('Content-Disposition');
            if (disposition && disposition.indexOf('filename=') !== -1) {
                const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                if (matches != null && matches[1]) {
                    filename = matches[1].replace(/['"]/g, '');
                }
            }

            const blobUrl = window.URL.createObjectURL(blob);
            const tempLink = document.createElement('a');
            tempLink.href = blobUrl;
            tempLink.setAttribute('download', filename);
            document.body.appendChild(tempLink);
            tempLink.click();
            document.body.removeChild(tempLink);
            window.URL.revokeObjectURL(blobUrl);

        } catch (err) {
            console.error('Export download error:', err);
            form.submit();
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            btn.classList.remove('opacity-75', 'cursor-wait');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const initialType = document.getElementById('reportType')?.value || 'Accomplishment Report';
        setReportType(initialType);
        handlePeriodChange();
    });
</script>
@endpush
@endsection
