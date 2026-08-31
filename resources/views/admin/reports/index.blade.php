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

    <!-- Quick Report Selection Cards (8 Cards Grid - 2 per row on mobile, 4 cols on desktop) -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @php
            $reportCards = [
                ['id' => 'accomplishment-report', 'name' => 'Accomplishment Report', 'type' => 'Accomplishment Report', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['id' => 'request-summary', 'name' => 'Request Summary', 'type' => 'Request Summary', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['id' => 'unit-performance', 'name' => 'Unit Performance', 'type' => 'Unit Performance', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                ['id' => 'worker-performance', 'name' => 'Worker Performance', 'type' => 'Worker Performance', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['id' => 'clientele-history', 'name' => 'Clientele History', 'type' => 'Clientele History', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-4-8l-2-2m0 0l-2 2m2-2v6'],
                ['id' => 'preventive-maintenance', 'name' => 'Preventive Maintenance', 'type' => 'Preventive Maintenance', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                ['id' => 'recurring-problems', 'name' => 'Recurring Problems', 'type' => 'Recurring Problems', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                ['id' => 'priority-analysis', 'name' => 'Priority Analysis', 'type' => 'Priority Analysis', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
            ];
        @endphp

        @foreach($reportCards as $index => $card)
            <div onclick="selectQuickReport('{{ $card['type'] }}')" 
                 class="quick-report-card bg-white dark:bg-zinc-900 border rounded-xl p-3 sm:p-4 hover:border-[#0033a0] dark:hover:border-blue-500 transition shadow-xs flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5 cursor-pointer group {{ $index === 0 ? 'border-[#0033a0] ring-2 ring-[#0033a0]/30 bg-blue-50/50 dark:bg-blue-950/20' : 'border-gray-200 dark:border-zinc-800' }}"
                 data-report-type="{{ $card['type'] }}">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg {{ $index === 0 ? 'bg-[#0033a0] text-white' : 'bg-gray-100 dark:bg-zinc-800 text-gray-500' }} flex items-center justify-center group-hover:bg-[#0033a0] group-hover:text-white transition shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-gray-200 group-hover:text-[#0033a0] dark:group-hover:text-blue-400 transition leading-snug sm:leading-tight">
                        {{ $card['name'] }}
                    </h3>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Generate / Customize Reports Card (Matches Mockup) -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm space-y-6">
        
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black text-[#0033a0] dark:text-blue-400 tracking-tight">
                    Generate Accomplishment & System Reports
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Accomplishment reports automatically compile all finished/completed jobs for each maintenance section.
                </p>
            </div>
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-full text-xs font-bold">
                Completed Jobs Only
            </span>
        </div>

        <form id="reportForm" action="{{ route('admin.reports.export') }}" method="POST" onsubmit="handleExportSubmit(event)" class="space-y-6">
            @csrf
            
            <!-- Row 1: Filters (Report Type, Year, Period / Semester, Date Start, Date End) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                
                <!-- Report Type -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Report Type</label>
                    <select name="report_type" id="reportType" onchange="updateLivePreview()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                        <option value="Accomplishment Report" selected>Accomplishment Report</option>
                        <option value="Request Summary">Request Summary</option>
                        <option value="Unit Performance">Unit Performance</option>
                        <option value="Worker Performance">Worker Performance</option>
                        <option value="Clientele History">Clientele History</option>
                        <option value="Preventive Maintenance">Preventive Maintenance</option>
                        <option value="Recurring Problems">Recurring Problems</option>
                        <option value="Priority Analysis">Priority Analysis</option>
                    </select>
                </div>

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
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end pt-1">
                
                <!-- Service Unit / Section -->
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Maintenance Section / Unit</label>
                    <select name="category_id" id="categoryId" onchange="updateLivePreview()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                        <option value="">ALL SERVICE UNITS (Combined)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->category_id }}">{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3 flex items-center justify-end gap-3">
                    <button type="button" onclick="resetReportForm()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl border border-gray-200 dark:border-zinc-700 transition">
                        Reset Defaults
                    </button>

                    <button type="submit" id="exportBtn" class="px-6 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-xl transition shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Export Excel Sheet (.xlsx)</span>
                    </button>
                </div>

            </div>
        </form>

        <div class="pt-6 border-t border-gray-100 dark:border-zinc-800">
            <!-- Preview Section Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="text-xs font-extrabold text-[#0033a0] dark:text-blue-400 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Official Accomplishment Report Preview</span>
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

    function selectQuickReport(type) {
        const reportTypeSelect = document.getElementById('reportType');
        if (reportTypeSelect) {
            reportTypeSelect.value = type;
            updateLivePreview();
        }

        document.querySelectorAll('.quick-report-card').forEach(card => {
            if (card.getAttribute('data-report-type') === type) {
                card.classList.add('border-[#0033a0]', 'ring-2', 'ring-[#0033a0]/30', 'bg-blue-50/50', 'dark:bg-blue-950/20');
                card.querySelector('div').classList.add('bg-[#0033a0]', 'text-white');
                card.querySelector('div').classList.remove('bg-gray-100', 'dark:bg-zinc-800', 'text-gray-500');
            } else {
                card.classList.remove('border-[#0033a0]', 'ring-2', 'ring-[#0033a0]/30', 'bg-blue-50/50', 'dark:bg-blue-950/20');
                card.querySelector('div').classList.remove('bg-[#0033a0]', 'text-white');
                card.querySelector('div').classList.add('bg-gray-100', 'dark:bg-zinc-800', 'text-gray-500');
            }
        });
    }

    function resetReportForm() {
        document.getElementById('reportForm').reset();
        document.getElementById('reportYear').value = new Date().getFullYear();
        document.getElementById('reportPeriod').value = (new Date().getMonth() + 1 <= 6) ? 'sem1' : 'sem2';
        handlePeriodChange();
    }

    function updateLivePreview() {
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

        // Sort by unit order: CMS -> PLS -> PAINTING -> JS -> LS -> MAN
        const categoryOrderMap = {
            'CMS': 1,
            'PLS': 2,
            'PAINT': 3,
            'PAINTING': 3,
            'JS': 4,
            'LS': 5,
            'MAN': 6
        };

        filteredRequests.sort((a, b) => {
            const orderA = a.category_order || categoryOrderMap[a.prefix] || 7;
            const orderB = b.category_order || categoryOrderMap[b.prefix] || 7;
            if (orderA !== orderB) {
                return orderA - orderB;
            }
            if (a.submitted_at !== b.submitted_at) {
                return (a.submitted_at || '').localeCompare(b.submitted_at || '');
            }
            return (a.request_id || 0) - (b.request_id || 0);
        });

        // Update badge count
        const countBadge = document.getElementById('previewCountBadge');
        if (countBadge) {
            countBadge.textContent = `${filteredRequests.length} Finished Job${filteredRequests.length === 1 ? '' : 's'}`;
        }

        // Generate rows matching Excel layout - sequentially starting with 001
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

        // Render EXACT Excel Sheet Preview Layout
        let previewHTML = `
            <div class="bg-white dark:bg-zinc-900 border-2 border-black rounded-lg p-5 shadow-sm font-sans">
                <!-- Excel Sheet Header -->
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

                <!-- Excel Sheet Grid Table -->
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
            </div>
        `;

        previewContainer.innerHTML = previewHTML;
    }

    function handleExportSubmit(e) {
        const btn = document.getElementById('exportBtn');
        if (!btn) return;
        const originalContent = btn.innerHTML;
        btn.innerHTML = `
            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Generating & Downloading...</span>
        `;
        btn.classList.add('opacity-80', 'pointer-events-none');
        setTimeout(() => {
            btn.innerHTML = originalContent;
            btn.classList.remove('opacity-80', 'pointer-events-none');
        }, 2000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        handlePeriodChange();
    });
</script>
@endpush
@endsection
