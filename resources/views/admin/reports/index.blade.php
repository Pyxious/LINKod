@extends('layouts.admin')

@section('page-title', 'System Reports')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6 font-sans">
    
    <!-- Page Banner Header -->
    <div class="bg-[#fefce8] dark:bg-[#1c1c1e] border border-[#1a3c8f] dark:border-zinc-800 rounded-xl px-8 py-6 flex justify-between items-center shadow-sm">
        <div>
            <h1 class="text-[#1a3c8f] dark:text-blue-400 text-2xl font-bold mb-1">System Reports</h1>
            <p class="text-[#1a3c8f] dark:text-gray-300 text-sm opacity-90">Generate, export, and monitor university maintenance accomplishment reports.</p>
        </div>
    </div>

    <!-- Quick Report Types Cards -->
    <div class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 p-6 shadow-sm">
        <h2 class="text-base font-bold text-[#1a3c8f] dark:text-blue-400 mb-4">Report Types &amp; Analytics</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $reports = [
                    [
                        'name' => 'Request Summary', 
                        'desc' => 'Overview of all requests',
                        'svg'  => '<svg class="w-5 h-5 text-[#1a3c8f] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>'
                    ],
                    [
                        'name' => 'Unit Performance', 
                        'desc' => 'Maintenance team stats',
                        'svg'  => '<svg class="w-5 h-5 text-[#1a3c8f] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>'
                    ],
                    [
                        'name' => 'Worker Performance', 
                        'desc' => 'Individual worker logs',
                        'svg'  => '<svg class="w-5 h-5 text-[#1a3c8f] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
                    ],
                    [
                        'name' => 'Clientele History', 
                        'desc' => 'Office request volume',
                        'svg'  => '<svg class="w-5 h-5 text-[#1a3c8f] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-4-8l-2-2m0 0l-2 2m2-2v6"/></svg>'
                    ],
                    [
                        'name' => 'Accomplishment Report', 
                        'desc' => 'Section monthly tasks',
                        'svg'  => '<svg class="w-5 h-5 text-[#1a3c8f] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'
                    ],
                    [
                        'name' => 'Preventive Maintenance', 
                        'desc' => 'Scheduled maintenance',
                        'svg'  => '<svg class="w-5 h-5 text-[#1a3c8f] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
                    ],
                    [
                        'name' => 'Recurring Problems', 
                        'desc' => 'Frequent facility issues',
                        'svg'  => '<svg class="w-5 h-5 text-[#1a3c8f] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'
                    ],
                    [
                        'name' => 'Priority Analysis', 
                        'desc' => 'Priority distribution',
                        'svg'  => '<svg class="w-5 h-5 text-[#1a3c8f] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>'
                    ],
                ];
            @endphp
            @foreach($reports as $report)
            <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 hover:border-[#1a3c8f] transition shadow-sm flex flex-col justify-between group cursor-pointer h-28">
                <div class="w-9 h-9 bg-blue-50 dark:bg-zinc-800/80 rounded-lg flex items-center justify-center border border-blue-100 dark:border-zinc-700 shrink-0">
                    {!! $report['svg'] !!}
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-[#1a3c8f] transition leading-tight">{!! $report['name'] !!}</h3>
                    <p class="text-[10px] text-gray-400 mt-0.5 truncate">{!! $report['desc'] !!}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Generate / Customize Reports Form -->
    <div class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 p-6 shadow-sm">
        <h2 class="text-base font-bold text-[#1a3c8f] dark:text-blue-400 mb-1">Generate Accomplishment Report</h2>
        <p class="text-xs text-gray-400 mb-6">Select a maintenance category section and date range to export a formatted Excel spreadsheet.</p>

        <form action="{{ route('admin.reports.export') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
                <div>
                    <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-1.5">Category (Maintenance Section) <span class="text-red-500">*</span></label>
                    <select name="category_id" class="w-full px-4 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#1a3c8f]" required>
                        <option value="" disabled selected>Select Category Section</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->category_id }}">{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-1.5">Date Start <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" class="w-full px-4 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#1a3c8f]" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-1.5">Date End <span class="text-red-500">*</span></label>
                    <input type="date" name="end_date" class="w-full px-4 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#1a3c8f]" required>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-zinc-800">
                <button type="reset" class="px-5 py-2.5 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-bold transition">
                    Reset
                </button>
                <button type="submit" class="px-6 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-lg text-xs font-bold transition shadow-sm inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Generate Excel Report (.xlsx)
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Reports Log Table -->
    <div class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-[#1a3c8f] dark:text-blue-400 mb-0.5">Recent Reports History</h2>
                <p class="text-xs text-gray-400">Log of generated accomplishment reports and exports.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-zinc-800 text-[11px] font-bold text-[#1a3c8f] dark:text-blue-400 uppercase tracking-wider">
                        <th class="py-3 px-4">Report Details</th>
                        <th class="py-3 px-4">Generated By</th>
                        <th class="py-3 px-4">Date Generated</th>
                        <th class="py-3 px-4">Format</th>
                        <th class="py-3 px-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-xs">
                    @forelse($recentReports as $log)
                    <tr class="hover:bg-gray-50/80 dark:hover:bg-zinc-800/50 transition">
                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                            {{ $log->action }}
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-700 dark:text-gray-300">
                            {{ $log->user->first_name ?? 'Administrator' }} {{ $log->user->last_name ?? '' }}
                        </td>
                        <td class="py-3.5 px-4 text-gray-500">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-blue-600 dark:text-blue-400">
                            Excel (.xlsx)
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Generated
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400 italic">
                            No recent report logs found. Generate a report above to populate history.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-zinc-800">
            {{ $recentReports->links() }}
        </div>
    </div>
</div>
@endsection
