@extends('layouts.admin')

@section('page-title', 'Audit Logs')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6 font-sans">
    
    <!-- Page Banner Header -->
    <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-8 py-6 flex justify-between items-center shadow-sm">
        <div>
            <h1 class="text-[#0033a0] dark:text-blue-400 text-2xl font-bold mb-1">Audit Logs</h1>
            <p class="text-[#0033a0]/80 dark:text-gray-300 text-sm font-medium">View system-wide user activity, security events, logins, and report generations.</p>
        </div>
    </div>

    <!-- Logs Table Container Card -->
    <div class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-[#1a3c8f] dark:text-blue-400 mb-0.5">System Activity History</h2>
                <p class="text-xs text-gray-400">Complete audit trail of user actions across the LINKod portal.</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 bg-blue-50 dark:bg-zinc-800 text-[#1a3c8f] dark:text-blue-400 rounded-full border border-blue-100 dark:border-zinc-700">
                Total Logs: {{ $logs->total() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-zinc-800 text-[11px] font-bold text-[#1a3c8f] dark:text-blue-400 uppercase tracking-wider">
                        <th class="py-3 px-4 w-1/3">User</th>
                        <th class="py-3 px-4 w-1/2">Action / Activity</th>
                        <th class="py-3 px-4 text-right">Date &amp; Time (PST)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-xs">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50/80 dark:hover:bg-zinc-800/50 transition">
                        <!-- User Cell -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-zinc-800 text-[#1a3c8f] dark:text-blue-400 font-bold text-xs flex items-center justify-center border border-blue-200 dark:border-zinc-700 shrink-0">
                                    {{ strtoupper(substr($log->user->first_name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    @if($log->user)
                                        <p class="font-bold text-slate-900 dark:text-white truncate">
                                            {{ $log->user->first_name }} {{ $log->user->last_name }}
                                        </p>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate font-mono">
                                            {{ $log->user->email_account }}
                                        </p>
                                    @else
                                        <span class="text-gray-400 italic">System / Visitor</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Action Cell -->
                        <td class="py-3.5 px-4 font-medium text-slate-800 dark:text-gray-200">
                            {{ $log->action ?? 'No action specified' }}
                        </td>

                        <!-- Date/Time Cell -->
                        <td class="py-3.5 px-4 text-right text-gray-500 dark:text-gray-400 font-medium">
                            {{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-12 text-center text-gray-400 italic">
                            No audit logs recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-zinc-800">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
