@extends('layouts.admin')

@section('page-title', 'Reports Management')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-8 font-sans">
    
    <!-- Top Header Banner (Matches Mockup) -->
    <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-8 py-6 shadow-sm">
        <h1 class="text-2xl font-bold text-[#0033a0] dark:text-blue-400 mb-1">Reports</h1>
        <p class="text-sm font-medium text-[#0033a0]/80 dark:text-gray-300">
            View, generate, and export system reports
        </p>
    </div>

    <!-- Quick Report Selection Cards (8 Cards Grid - 2 per row on mobile, 4 cols on desktop) -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @php
            $reportCards = [
                ['id' => 'request-summary', 'name' => 'Request Summary', 'type' => 'Request Summary', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['id' => 'unit-performance', 'name' => 'Unit Performance', 'type' => 'Unit Performance', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                ['id' => 'worker-performance', 'name' => 'Worker Performance', 'type' => 'Worker Performance', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['id' => 'clientele-history', 'name' => 'Clientele History', 'type' => 'Clientele History', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-4-8l-2-2m0 0l-2 2m2-2v6'],
                ['id' => 'accomplishment-report', 'name' => 'Accomplishment Report', 'type' => 'Accomplishment Report', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['id' => 'preventive-maintenance', 'name' => 'Preventive Maintenance', 'type' => 'Preventive Maintenance', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                ['id' => 'recurring-problems', 'name' => 'Recurring Problems', 'type' => 'Recurring Problems', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                ['id' => 'priority-analysis', 'name' => 'Priority Analysis', 'type' => 'Priority Analysis', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
            ];
        @endphp

        @foreach($reportCards as $card)
            <div onclick="selectQuickReport('{{ $card['type'] }}')" 
                 class="quick-report-card bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl p-3 sm:p-4 hover:border-[#0033a0] dark:hover:border-blue-500 transition shadow-xs flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5 cursor-pointer group"
                 data-report-type="{{ $card['type'] }}">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-gray-100 dark:bg-zinc-800 flex items-center justify-center text-gray-500 group-hover:bg-[#0033a0] group-hover:text-white transition shrink-0">
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
        
        <div>
            <h2 class="text-lg font-black text-[#0033a0] dark:text-blue-400 tracking-tight">
                Generate / Customize Reports
            </h2>
        </div>

        <form id="reportForm" action="{{ route('admin.reports.export') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Row 1: 5 Filters (Report Type, Date Start, Date End, Service Unit/Section, Status) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                
                <!-- Report Type -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Report Type</label>
                    <select name="report_type" id="reportType" onchange="updateLivePreview()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                        <option value="Accomplishment Report">Accomplishment Report</option>
                        <option value="Request Summary">Request Summary</option>
                        <option value="Unit Performance">Unit Performance</option>
                        <option value="Worker Performance">Worker Performance</option>
                        <option value="Clientele History">Clientele History</option>
                        <option value="Preventive Maintenance">Preventive Maintenance</option>
                        <option value="Recurring Problems">Recurring Problems</option>
                        <option value="Priority Analysis">Priority Analysis</option>
                    </select>
                </div>

                <!-- Date Start -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Date Start</label>
                    <input type="date" name="start_date" id="startDate" onchange="updateLivePreview()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                </div>

                <!-- Date End -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Date End</label>
                    <input type="date" name="end_date" id="endDate" onchange="updateLivePreview()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                </div>

                <!-- Service Unit / Section -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Service Unit/Section</label>
                    <select name="category_id" id="categoryId" onchange="updateLivePreview()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                        <option value="">All Service Units</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->category_id }}">{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Status</label>
                    <select name="status" id="statusFilter" onchange="updateLivePreview()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                        <option value="">All Status</option>
                        <option value="Submitted">Submitted</option>
                        <option value="Approved">Approved</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Pending Verification">Pending Verification</option>
                        <option value="Completed">Completed</option>
                        <option value="On Hold">On Hold</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>

            </div>

            <!-- Row 2: Worker Filter + Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                
                <!-- Worker Filter -->
                <div class="w-full sm:w-1/4">
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-gray-300 mb-1.5">Worker</label>
                    <select name="worker_id" id="workerId" onchange="updateLivePreview()" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]">
                        <option value="">Select worker...</option>
                        @foreach($workers as $w)
                            <option value="{{ $w->worker_id }}">{{ $w->user->first_name ?? 'Worker' }} {{ $w->user->last_name ?? '' }} ({{ $w->team->team_name ?? 'Unit' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-3 shrink-0 self-end">
                    <button type="button" onclick="resetReportForm()" class="px-6 py-2.5 bg-blue-100 hover:bg-blue-200 text-[#0033a0] text-xs font-bold rounded-xl border border-blue-200 transition shadow-2xs">
                        Reset
                    </button>

                    <button type="submit" class="px-6 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-xl transition shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Generate Report
                    </button>
                </div>

            </div>
        </form>

        <div class="pt-6 border-t border-gray-100 dark:border-zinc-800">
            <!-- Preview Section Title -->
            <div class="text-center text-xs font-extrabold text-gray-500 uppercase tracking-wider mb-4">
                Preview
            </div>

            <!-- Live Document Interactive Preview Container (Matches Mockup) -->
            <div id="previewFrame" class="max-w-3xl mx-auto bg-slate-50 dark:bg-zinc-950 border-2 border-gray-300 dark:border-zinc-800 rounded-2xl p-6 shadow-inner min-h-[380px] flex flex-col justify-between">
                
                <!-- Live Dynamic Report Document -->
                <div id="reportPreviewContent" class="space-y-4">
                    <!-- Dynamic HTML Generated via JS -->
                </div>

                <!-- Page Footer -->
                <div class="text-[11px] font-bold text-gray-400 text-center border-t border-gray-200 dark:border-zinc-800 pt-3">
                    Page 1/1
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
                Reports History
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-zinc-800 text-[11px] font-bold text-[#0033a0] dark:text-blue-400 uppercase tracking-wider">
                        <th class="py-3 px-4 flex items-center gap-1 cursor-pointer">
                            REPORT NAME <span class="text-xs">↑</span>
                        </th>
                        <th class="py-3 px-4">TYPE</th>
                        <th class="py-3 px-4">DATE GENERATED</th>
                        <th class="py-3 px-4">GENERATED BY</th>
                        <th class="py-3 px-4">FORMAT</th>
                        <th class="py-3 px-4 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-xs">
                    @forelse($recentReports as $index => $log)
                        @php
                            $reportTitle = match(true) {
                                str_contains(strtolower($log->action), 'accomplishment') => '2026 ACCOMPLISHMENT REPORT',
                                str_contains(strtolower($log->action), 'summary') => 'REQUEST SUMMARY REPORT',
                                str_contains(strtolower($log->action), 'worker') => 'WORKER PERFORMANCE REPORT',
                                default => 'SYSTEM MAINTENANCE REPORT'
                            };
                            $format = 'Excel';
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
                            <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-gray-300">
                                {{ $format }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" onclick="updateLivePreview()" title="Preview Report" class="p-1.5 text-gray-500 hover:text-[#0033a0] dark:hover:text-blue-400 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button type="button" class="p-1.5 text-gray-400 hover:text-slate-800 dark:hover:text-white transition">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-xs text-gray-400">
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
    // Real database requests for Excel Live Preview
    const realDbRequests = @json($previewRequests);

    function selectQuickReport(type) {
        const reportTypeSelect = document.getElementById('reportType');
        if (reportTypeSelect) {
            reportTypeSelect.value = type;
            updateLivePreview();
        }

        document.querySelectorAll('.quick-report-card').forEach(card => {
            if (card.getAttribute('data-report-type') === type) {
                card.classList.add('border-[#0033a0]', 'ring-2', 'ring-[#0033a0]/30', 'bg-blue-50/50');
            } else {
                card.classList.remove('border-[#0033a0]', 'ring-2', 'ring-[#0033a0]/30', 'bg-blue-50/50');
            }
        });
    }

    function resetReportForm() {
        document.getElementById('reportForm').reset();
        document.querySelectorAll('.quick-report-card').forEach(card => {
            card.classList.remove('border-[#0033a0]', 'ring-2', 'ring-[#0033a0]/30', 'bg-blue-50/50');
        });
        updateLivePreview();
    }

    function updateLivePreview() {
        const type = document.getElementById('reportType').value || 'Accomplishment Report';
        const startDateVal = document.getElementById('startDate').value;
        const endDateVal = document.getElementById('endDate').value;

        const formatDateStr = (dStr) => {
            if (!dStr) return '';
            const d = new Date(dStr);
            return isNaN(d.getTime()) ? '' : ((d.getMonth() + 1) + '/' + d.getDate() + '/' + d.getFullYear());
        };

        const monthNames = ["JANUARY", "FEBRUARY", "MARCH", "APRIL", "MAY", "JUNE", "JULY", "AUGUST", "SEPTEMBER", "OCTOBER", "NOVEMBER", "DECEMBER"];
        
        let startMonthStr = 'JANUARY';
        let endMonthStr   = 'DECEMBER';

        if (startDateVal) {
            const d1 = new Date(startDateVal);
            if (!isNaN(d1.getTime())) startMonthStr = monthNames[d1.getMonth()];
        }
        if (endDateVal) {
            const d2 = new Date(endDateVal);
            if (!isNaN(d2.getTime())) endMonthStr = monthNames[d2.getMonth()];
        }

        const monthRangeHeader = (startMonthStr === endMonthStr) ? startMonthStr : `${startMonthStr} TO ${endMonthStr}`;

        const categoryOpt = document.getElementById('categoryId');
        const categoryId  = categoryOpt.value;
        const categoryName = categoryOpt.options[categoryOpt.selectedIndex]?.text || 'ALL MAINTENANCE SECTIONS';
        const statusVal = document.getElementById('statusFilter').value;

        const previewContainer = document.getElementById('reportPreviewContent');

        // Filter real requests from database based on inputs
        let filteredRequests = realDbRequests.filter(req => {
            if (categoryId && String(req.category_id) !== String(categoryId)) {
                return false;
            }
            if (statusVal && req.current_status !== statusVal) {
                return false;
            }
            return true;
        });

        // Generate rows matching Excel layout
        let tableRowsHTML = '';
        if (filteredRequests.length > 0) {
            filteredRequests.forEach(req => {
                const catName = (req.category?.category_name || '').toLowerCase();
                const prefix = catName.includes('landscaping') ? 'LS' :
                               (catName.includes('electrical') || catName.includes('mechanical')) ? 'EMS' :
                               (catName.includes('carpentry') || catName.includes('masonry')) ? 'CMS' :
                               (catName.includes('plumbing') || catName.includes('painting')) ? 'PS' : 'REQ';

                const reqNum = req.requisition_no || (prefix + '-' + String(req.request_id).padStart(3, '0'));
                const office = req.location || 'N/A';
                const reqDate = req.submitted_at ? formatDateStr(req.submitted_at) : '';

                let ratingVal = '';
                if (req.evaluation && req.evaluation.rating) {
                    const r = parseFloat(req.evaluation.rating);
                    ratingVal = Number.isInteger(r) ? r.toString() : r.toFixed(1);
                }

                tableRowsHTML += `
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/60 transition text-[11px]">
                        <td class="border border-black px-2 py-2 text-center font-bold font-mono">${reqNum}</td>
                        <td class="border border-black px-2 py-2 text-center font-semibold">${office}</td>
                        <td class="border border-black px-2 py-2">
                            <div class="font-bold text-slate-900 dark:text-white">${req.title || ''}</div>
                            <div class="text-[10px] text-gray-500">${req.description || ''}</div>
                        </td>
                        <td class="border border-black px-2 py-2 text-center font-medium">${reqDate}</td>
                        <td class="border border-black px-2 py-2 text-center font-medium">${req.project ? reqDate : ''}</td>
                        <td class="border border-black px-2 py-2 text-center font-medium">${req.current_status === 'Completed' ? reqDate : ''}</td>
                        <td class="border border-black px-2 py-2 text-center font-bold">${ratingVal || '—'}</td>
                    </tr>
                `;
            });
        } else {
            tableRowsHTML = `
                <tr>
                    <td colspan="7" class="border border-black py-6 text-center text-xs text-gray-400 italic">
                        No service requests found matching selected criteria.
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
                        ${new Date().getFullYear()} ACCOMPLISHMENT REPORT
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
                                <th rowspan="2" class="border border-black px-2 py-1.5 text-center w-[15%]">OFFICE/<br>UNIT</th>
                                <th rowspan="2" class="border border-black px-2 py-1.5 text-center w-[30%]">TASK DETAILS</th>
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

    document.addEventListener('DOMContentLoaded', function() {
        updateLivePreview();
    });
</script>
@endpush
@endsection
