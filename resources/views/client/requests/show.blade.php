@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full flex flex-col font-sans min-h-[calc(100vh-64px)]">
    
    <!-- Hero Header Banner (Full-Width Shaded Section) -->
    <section class="w-full bg-[#edf4fb] dark:bg-[#18181b] py-10 px-4 sm:px-6 lg:px-8 border-b border-gray-200 dark:border-zinc-800">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wider">
                    <a href="{{ route('home') }}" class="hover:text-[#0033a0]">Home</a>
                    <span>/</span>
                    <a href="{{ route('client.requests.index') }}" class="hover:text-[#0033a0]">Track Requests</a>
                    <span>/</span>
                    <span class="text-[#0033a0] dark:text-blue-400 font-bold">Request #{{ str_pad($request->request_id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>

                <div class="flex items-center gap-3 mb-2 flex-wrap">
                    <span class="px-3 py-1 bg-[#0033a0] text-white text-[11px] font-extrabold uppercase tracking-wider rounded-full shadow-sm">
                        Requisition #{{ str_pad($request->request_id, 4, '0', STR_PAD_LEFT) }}
                    </span>
                    <span id="requestStatusBadge" data-request-status-badge class="px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider rounded-full border
                        {{ match($request->current_status) {
                            'Completed' => 'bg-emerald-100 text-emerald-700 border-emerald-300',
                            'In Progress', 'Pending Verification' => 'bg-blue-100 text-blue-700 border-blue-300',
                            'Cancelled', 'Rejected' => 'bg-amber-100 text-amber-700 border-amber-300',
                            default => 'bg-amber-100 text-amber-700 border-amber-300'
                        } }}">
                        {{ $request->current_status }}
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                    {{ $request->title }}
                </h1>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 shrink-0">
                @if($request->current_status === 'Completed' && !$request->evaluation)
                    <a href="{{ route('client.evaluations.create', $request->request_id) }}" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-full transition shadow-md inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        Rate & Evaluate Service
                    </a>
                @endif

                @if(in_array($request->current_status, ['Submitted', 'Pending']))
                    <form action="{{ route('client.requests.cancel', $request->request_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this request?');">
                        @csrf
                        <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-full transition shadow-md inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Cancel Request
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 space-y-6">
        
        @php
            $latestRejection = $request->histories->where('current_status', 'Rejected')->last();
        @endphp

        @if($request->current_status === 'Rejected')
            <!-- Rejection Notice & Recommendation Banner -->
            <div class="bg-red-50/90 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 rounded-2xl p-6 sm:p-7 shadow-xs">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/70 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0 border border-red-200 dark:border-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-base font-bold text-red-900 dark:text-red-300 uppercase tracking-tight mb-1">
                            Service Request Disapproved / Rejected
                        </h2>
                        <p class="text-xs text-red-700 dark:text-red-400 mb-3">
                            This requisition cannot be processed by General Services Office (GSO). Please review the administrator's feedback below:
                        </p>
                        
                        <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-red-200 dark:border-red-800/80 shadow-2xs">
                            <div class="text-[10px] font-extrabold text-red-600 dark:text-red-400 uppercase tracking-wider mb-1">
                                Admin Reason / Recommendation:
                            </div>
                            <p class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white leading-relaxed whitespace-pre-line">
                                {{ $latestRejection && $latestRejection->remarks ? $latestRejection->remarks : 'No specific reason provided by the administrator.' }}
                            </p>
                        </div>

                        <div class="mt-4 flex items-center gap-3">
                            <a href="{{ route('client.requests.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Submit a New Requisition
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left Column: Details & BOM -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Request Specifications Card -->
                <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm">
                    <h2 class="text-base font-bold text-[#0033a0] dark:text-blue-400 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Service Request Specifications
                    </h2>

                    <!-- Description -->
                    <div class="mb-6">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Description</div>
                        <div class="bg-slate-50 dark:bg-zinc-800/60 p-4 rounded-xl text-slate-800 dark:text-gray-200 text-xs sm:text-sm leading-relaxed border border-gray-100 dark:border-zinc-700">
                            {{ $request->description ?: 'No detailed description provided.' }}
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                            <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Category</div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $request->category->category_name ?? 'General' }}</div>
                        </div>

                        <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                            <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Campus</div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $request->campus ?? 'BU Main' }}</div>
                        </div>

                        <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                            <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Office / Location</div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $request->location }}</div>
                        </div>
                    </div>

                    <!-- Supporting Attachment -->
                    @if($request->attachment)
                        <div class="mt-6 border-t border-gray-100 dark:border-zinc-800 pt-5">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Supporting Attachment</div>
                            <a href="{{ Storage::url($request->attachment) }}" target="_blank" class="inline-flex items-center gap-3 p-3 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl hover:border-[#0033a0] transition group">
                                @if(Str::endsWith(strtolower($request->attachment), ['.jpg', '.jpeg', '.png', '.webp']))
                                    <img src="{{ Storage::url($request->attachment) }}" alt="Attachment" class="w-14 h-14 object-cover rounded-lg border border-gray-200">
                                @else
                                    <div class="w-12 h-12 bg-blue-100 text-[#0033a0] rounded-lg flex items-center justify-center font-bold text-xs">PDF</div>
                                @endif
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-[#0033a0] transition">View Attachment ↗</div>
                                    <div class="text-[11px] text-gray-400">Click to view or download full file</div>
                                </div>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Bill of Materials (BOM) Card -->
                <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Bill of Materials (BOM)
                        </h2>
                        @if($request->project && $request->project->billOfMaterials->count() > 0)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200 uppercase">
                                {{ $request->project->billOfMaterials->count() }} Item(s)
                            </span>
                        @endif
                    </div>

                    @if($request->project && $request->project->billOfMaterials->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-zinc-800 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                                        <th class="py-2.5 px-3">Material Item</th>
                                        <th class="py-2.5 px-3 text-center">Unit</th>
                                        <th class="py-2.5 px-3 text-center">Qty</th>
                                        <th class="py-2.5 px-3 text-right">Unit Cost</th>
                                        <th class="py-2.5 px-3 text-right">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-xs">
                                    @php $grandTotal = 0; @endphp
                                    @foreach($request->project->billOfMaterials as $bom)
                                        @php 
                                            $unitCost = $bom->material->unit_cost ?? 0;
                                            $itemTotal = $bom->total_cost ?: ($bom->qty * $unitCost);
                                            $grandTotal += $itemTotal;
                                        @endphp
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/40 transition">
                                            <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">
                                                {{ $bom->material->material_name ?? 'Material Item' }}
                                            </td>
                                            <td class="py-3 px-3 text-center text-gray-500">
                                                {{ $bom->material->unit ?? 'pcs' }}
                                            </td>
                                            <td class="py-3 px-3 text-center font-bold text-slate-800 dark:text-gray-200">
                                                {{ number_format($bom->qty, 0) }}
                                            </td>
                                            <td class="py-3 px-3 text-right text-gray-500">
                                                ₱{{ number_format($unitCost, 2) }}
                                            </td>
                                            <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                                                ₱{{ number_format($itemTotal, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Total Estimated Cost Summary -->
                        <div class="mt-4 pt-3 border-t border-gray-200 dark:border-zinc-800 flex items-center justify-between bg-gray-50 dark:bg-zinc-800/50 p-4 rounded-xl">
                            <span class="text-xs font-bold text-slate-700 dark:text-gray-300">Total Estimated Materials Cost:</span>
                            <span class="text-base font-black text-[#0033a0] dark:text-blue-400">₱{{ number_format($grandTotal, 2) }}</span>
                        </div>
                    @else
                        <div class="p-6 bg-slate-50 dark:bg-zinc-800/30 rounded-xl text-center border border-gray-100 dark:border-zinc-800">
                            <p class="text-xs text-gray-400 italic">No Bill of Materials (BOM) required or requested yet for this job.</p>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Right Column: Status Timeline Stepper -->
            <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Status Timeline & History
                </h2>

                <div id="requestTimelineFeed" class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200 dark:before:bg-zinc-700">
                    @forelse($request->histories as $history)
                        @php
                            $isRejected = ($history->current_status === 'Rejected');
                            $isCancelled = ($history->current_status === 'Cancelled');
                            $isCompleted = ($history->current_status === 'Completed');
                        @endphp
                        <div class="relative">
                            <!-- Bullet Indicator -->
                            <div class="absolute -left-[23px] top-1 w-4 h-4 rounded-full {{ ($isRejected || $isCancelled) ? 'bg-red-600 dark:bg-red-500' : ($isCompleted ? 'bg-emerald-600 dark:bg-emerald-500' : 'bg-[#0033a0] dark:bg-blue-500') }} border-2 border-white dark:border-zinc-900 shadow-sm flex items-center justify-center text-white text-[8px] font-bold">
                                {{ ($isRejected || $isCancelled) ? '✕' : '✓' }}
                            </div>

                            <div class="bg-slate-50 dark:bg-zinc-800/40 p-3.5 rounded-xl border border-gray-100 dark:border-zinc-800">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-extrabold {{ $isRejected ? 'text-red-700 dark:text-red-400' : ($isCompleted ? 'text-emerald-700 dark:text-emerald-400' : 'text-[#0033a0] dark:text-blue-400') }}">
                                        {{ $history->current_status }}
                                    </span>
                                </div>

                                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($history->updated_at)->format('M d, Y h:i A') }}
                                </div>

                                @if($history->updatedBy)
                                    <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">
                                        Updated by: {{ $history->updatedBy->first_name ?? '' }} {{ $history->updatedBy->last_name ?? '' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p id="noHistoryText" class="text-xs text-gray-400 italic">No history records found.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Per-Request Messaging Channel (Full-Width Bottom) -->
        @include('partials.request-messages', ['serviceRequest' => $request])

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const requestId = "{{ $request->request_id }}";
    if (requestId && window.supabaseClient) {
        // 1. Listen for request status updates
        window.supabaseClient
            .channel(`realtime-request-status-${requestId}`)
            .on(
                'postgres_changes',
                {
                    event: 'UPDATE',
                    schema: 'public',
                    table: 'request',
                    filter: `request_id=eq.${requestId}`
                },
                (payload) => {
                    const newStatus = payload.new?.current_status;
                    if (newStatus) {
                        const badge = document.getElementById('requestStatusBadge');
                        if (badge) {
                            badge.textContent = newStatus;
                            badge.className = 'px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider rounded-full border transition-all duration-300 ';
                            if (newStatus === 'Completed') {
                                badge.className += 'bg-emerald-100 text-emerald-700 border-emerald-300';
                            } else if (newStatus === 'In Progress' || newStatus === 'Pending Verification') {
                                badge.className += 'bg-blue-100 text-blue-700 border-blue-300';
                            } else if (newStatus === 'Cancelled' || newStatus === 'Rejected') {
                                badge.className += 'bg-amber-100 text-amber-700 border-amber-300';
                            } else {
                                badge.className += 'bg-amber-100 text-amber-700 border-amber-300';
                            }
                        }

                        if (window.LINKodRealtime) {
                            window.LINKodRealtime.showNotificationToast(
                                'Request Status Updated',
                                `Requisition #${requestId} is now "${newStatus}"`
                            );
                        }
                    }
                }
            )
            .on(
                'postgres_changes',
                {
                    event: 'INSERT',
                    schema: 'public',
                    table: 'request_history',
                    filter: `request_id=eq.${requestId}`
                },
                (payload) => {
                    const history = payload.new;
                    if (history) {
                        const timeline = document.getElementById('requestTimelineFeed');
                        const emptyMsg = document.getElementById('noHistoryText');
                        if (emptyMsg) emptyMsg.remove();

                        if (timeline) {
                            const dateStr = new Date().toLocaleString('en-US', {
                                month: 'short',
                                day: 'numeric',
                                year: 'numeric',
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                            });

                            const isRej = (history.current_status === 'Rejected');
                            const isCanc = (history.current_status === 'Cancelled');
                            const isComp = (history.current_status === 'Completed');
                            const bulletBg = (isRej || isCanc) ? 'bg-red-600 dark:bg-red-500' : (isComp ? 'bg-emerald-600 dark:bg-emerald-500' : 'bg-[#0033a0] dark:bg-blue-500');
                            const bulletIcon = (isRej || isCanc) ? '✕' : '✓';
                            const statusColor = isRej ? 'text-red-700 dark:text-red-400' : (isComp ? 'text-emerald-700 dark:text-emerald-400' : 'text-[#0033a0] dark:text-blue-400');

                            const item = document.createElement('div');
                            item.className = 'relative animate-fadeIn';
                            item.innerHTML = `
                                <div class="absolute -left-[23px] top-1 w-4 h-4 rounded-full ${bulletBg} border-2 border-white dark:border-zinc-900 shadow-sm flex items-center justify-center text-white text-[8px] font-bold">
                                    ${bulletIcon}
                                </div>
                                <div class="bg-slate-50 dark:bg-zinc-800/40 p-3.5 rounded-xl border border-gray-100 dark:border-zinc-800">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs font-extrabold ${statusColor}">
                                            ${history.current_status || 'Updated'}
                                        </span>
                                        <span class="text-[9px] font-bold uppercase bg-blue-100 dark:bg-blue-900 text-[#0033a0] dark:text-blue-300 px-1.5 py-0.5 rounded">Just now</span>
                                    </div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                        ${dateStr}
                                    </div>
                                </div>
                            `;
                            timeline.appendChild(item);
                        }
                    }
                }
            )
            .subscribe();
    }
});
</script>
@endsection
