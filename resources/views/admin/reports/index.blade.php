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

            <!-- Row 2: Service Unit/Section & Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-4 items-end pt-1">
                
                <!-- Service Unit / Section (5 cols) -->
                <div class="md:col-span-5">
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Maintenance Section / Unit</label>
                    <select name="category_id" id="categoryId" onchange="updateLivePreview()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                        <option value="">ALL SERVICE UNITS (Combined)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->category_id }}">{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-7 flex flex-wrap items-center justify-end gap-2.5">
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
        const categoryId  = categoryOpt.value;
        const categoryName = categoryId ? (categoryOpt.options[categoryOpt.selectedIndex]?.text || 'MAINTENANCE SECTION') : 'ALL SERVICE UNITS';

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
                        </tr>
                    `;
                });
            } else {
                tableRowsHTML = `
                    <tr>
                        <td colspan="7" class="border border-black py-8 text-center text-xs text-gray-400 italic">
                            No finished service requests found for ${categoryName.toUpperCase()} in ${monthRangeHeader} ${year}.
                        </td>
                    </tr>
                `;
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
                                    <th rowspan="2" class="border border-black px-2 py-1.5 text-center w-[15%]">REQUISITION<br>NUMBER</th>
                                    <th rowspan="2" class="border border-black px-2 py-1.5 text-center w-[16%]">OFFICE/<br>UNIT</th>
                                    <th rowspan="2" class="border border-black px-2 py-1.5 text-center w-[29%]">TASK DETAILS</th>
                                    <th colspan="3" class="border border-black px-2 py-1 text-center w-[25%]">DATES</th>
                                    <th rowspan="2" class="border border-black px-2 py-1.5 text-center w-[15%]">CLIENTELE<br>SATISFACTION<br>RATING</th>
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
