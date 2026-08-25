@extends('layouts.client')

@section('page-title', 'Bill of Materials')

@section('content')
<div class="w-full max-w-4xl mx-auto space-y-6 font-sans">
    
    <!-- Top Header Banner -->
    <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-6 sm:px-8 py-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2 flex-wrap">
                <span class="px-3 py-1 bg-[#0033a0] text-white text-[11px] font-extrabold uppercase tracking-wider rounded-full shadow-sm">
                    Requisition {{ $project->request->requisition_no ?: ('#' . str_pad($project->request->request_id, 4, '0', STR_PAD_LEFT)) }}
                </span>
                <span class="px-3 py-1 bg-blue-100 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-300 text-[11px] font-extrabold uppercase tracking-wider rounded-full">
                    Project #{{ $project->project_id }}
                </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Bill of Materials (BOM) Breakdown
            </h1>

            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">
                For: <span class="font-bold text-slate-800 dark:text-gray-200">{{ $project->request->title }}</span>
            </p>
        </div>

        @if($project->request)
            <a href="{{ route('client.requests.show', $project->request->request_id) }}" class="px-4 py-2 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-xl transition shadow-xs inline-flex items-center gap-1.5 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Requisition</span>
            </a>
        @endif
    </div>

    <!-- BOM Table Card -->
    <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-6 sm:p-7 shadow-sm">
        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>Itemized Materials & Supplies</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-zinc-800 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        <th class="py-3 px-3">Material Item</th>
                        <th class="py-3 px-3 text-center">Unit</th>
                        <th class="py-3 px-3 text-center">Qty</th>
                        <th class="py-3 px-3 text-right">Unit Price</th>
                        <th class="py-3 px-3 text-right">Total Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-xs">
                    @forelse($project->billOfMaterials as $bom)
                        @php
                            $unit = $bom->material->unit_of_measurement ?? 'pcs';
                            $unitCost = $bom->material->unit_cost ?? 0;
                            $itemTotal = $bom->total_cost ?: ($bom->qty * $unitCost);
                            $isApproved = !is_null($bom->date_approved);
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-zinc-800/40 transition">
                            <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <span>{{ $bom->material->material_name ?? 'Material Item' }}</span>
                                    @if(!$isApproved)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 uppercase">Pending Pricing</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center text-gray-500 font-semibold">
                                {{ $unit }}
                            </td>
                            <td class="py-3 px-3 text-center font-bold text-slate-800 dark:text-gray-200">
                                {{ rtrim(rtrim(number_format($bom->qty, 2), '0'), '.') }}
                            </td>
                            <td class="py-3 px-3 text-right text-gray-500 font-medium">
                                @if($unitCost > 0)
                                    ₱{{ number_format($unitCost, 2) }}
                                @else
                                    <span class="text-gray-400 italic">--</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                                @if($itemTotal > 0)
                                    ₱{{ number_format($itemTotal, 2) }}
                                @else
                                    <span class="text-gray-400 italic">--</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-400 italic">No materials recorded yet for this project.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5 pt-3 border-t border-gray-200 dark:border-zinc-800 flex items-center justify-between bg-gray-50 dark:bg-zinc-800/50 p-4 rounded-xl">
            <span class="text-xs font-bold text-slate-700 dark:text-gray-300">Total Materials Cost:</span>
            <span class="text-base font-black text-[#0033a0] dark:text-blue-400">₱{{ number_format($totalCost ?? 0, 2) }}</span>
        </div>
    </div>

</div>
@endsection
