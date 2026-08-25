@extends('layouts.admin')

@section('page-title', 'Review & Action Request')

@section('content')
<div class="w-full max-w-6xl mx-auto space-y-6 font-sans">
    
    <!-- Top Header Banner -->
    <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-8 py-6 shadow-sm space-y-4"
         x-data="{
             dateStarted: '',
             targetCompletion: '',
             get printUrl() {
                 let base = '{{ route('admin.requests.export', $serviceRequest->request_id) }}';
                 let params = new URLSearchParams();
                 if (this.dateStarted) params.append('date_started', this.dateStarted);
                 if (this.targetCompletion) params.append('target_completion', this.targetCompletion);
                 let qs = params.toString();
                 return qs ? (base + '?' + qs) : base;
             }
         }">
        
        <!-- Row 1: Badges on Left, Clientele Satisfaction Button on Right (Same Line) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="px-3 py-1 bg-[#0033a0] text-white text-[11px] font-extrabold uppercase tracking-wider rounded-full shadow-sm">
                    Requisition #{{ str_pad($serviceRequest->request_id, 4, '0', STR_PAD_LEFT) }}
                </span>
                <span class="px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider rounded-full border 
                    {{ match(strtolower($serviceRequest->priority ?? 'low')) {
                        'high' => 'bg-red-100 text-red-700 border-red-300',
                        'medium' => 'bg-amber-100 text-amber-700 border-amber-300',
                        default => 'bg-emerald-100 text-emerald-700 border-emerald-300'
                    } }}">
                    {{ strtoupper($serviceRequest->priority ?? 'Low') }} Priority
                </span>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-[11px] font-bold rounded-full">
                    {{ $serviceRequest->current_status }}
                </span>
                @if($serviceRequest->project?->nature_of_work)
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 border border-amber-300 text-[11px] font-bold rounded-full inline-flex items-center gap-1">
                        <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        {{ $serviceRequest->project->nature_of_work }}
                    </span>
                @endif
            </div>

            <!-- Clientele Satisfaction Button (Same line as badges) -->
            <div class="shrink-0">
                @if($serviceRequest->evaluation)
                    <a href="{{ route('admin.requests.satisfaction', $serviceRequest->request_id) }}" target="_blank" class="px-4 py-1.5 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-full transition shadow-sm inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        Print Satisfaction Page
                    </a>
                @else
                    <button type="button" disabled class="px-4 py-1.5 bg-gray-200 dark:bg-zinc-800 text-gray-400 dark:text-gray-500 text-xs font-bold rounded-full cursor-not-allowed inline-flex items-center gap-1.5 opacity-80" title="Client has not rated this service request yet">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        Print Satisfaction Page (Not Rated Yet)
                    </button>
                @endif
            </div>
        </div>

        <!-- Row 2: Title & Details on Left, Print Requisition & Date Range Controls on Right -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-1 border-t border-[#e5e1b0] dark:border-zinc-800">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    {{ $serviceRequest->title }}
                </h1>

                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">
                    Submitted by <span class="font-bold text-slate-800 dark:text-gray-200">{{ $serviceRequest->client->user->first_name ?? 'N/A' }} {{ $serviceRequest->client->user->last_name ?? '' }}</span> 
                    ({{ $serviceRequest->client->user->email_account ?? '' }})
                    • {{ \Carbon\Carbon::parse($serviceRequest->submitted_at)->format('M d, Y h:i A') }}
                </p>
            </div>

            <!-- Print Requisition Action & Date Range Controls -->
            <div class="flex flex-col gap-2 shrink-0 w-full sm:w-auto min-w-[300px]">
                <!-- Print Requisition Button -->
                <a :href="printUrl" target="_blank" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-md inline-flex items-center justify-center gap-2 w-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Requisition
                </a>

                <!-- 2 Boxes: Left = Date Started, Right = Target Date of Completion -->
                <div class="grid grid-cols-2 gap-2 bg-white/70 dark:bg-zinc-800/80 p-2.5 rounded-xl border border-amber-200/80 dark:border-zinc-700">
                    <div>
                        <label class="block text-[10px] font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                            Date Started
                        </label>
                        <input type="date" 
                               x-model="dateStarted" 
                               class="w-full px-2 py-1 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-semibold text-gray-800 dark:text-white focus:outline-none focus:border-[#0033a0]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                            Target Completion
                        </label>
                        <input type="date" 
                               x-model="targetCompletion" 
                               :min="dateStarted" 
                               class="w-full px-2 py-1 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-semibold text-gray-800 dark:text-white focus:outline-none focus:border-[#0033a0]">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Details Card -->
    <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm"
         x-data="{ lightboxOpen: false, lightboxImg: '', lightboxTitle: '' }">
        <h2 class="text-base font-bold text-[#0033a0] dark:text-blue-400 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Request Details & Specification
        </h2>

        <!-- Description Box -->
        <div class="mb-6">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Description / Issue Summary</div>
            <div class="bg-slate-50 dark:bg-zinc-800/60 p-4 rounded-xl text-slate-800 dark:text-gray-200 text-sm leading-relaxed border border-gray-100 dark:border-zinc-700 whitespace-pre-line">
                {{ $serviceRequest->display_description ?: 'No additional description provided.' }}
            </div>

        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Service Category</div>
                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $serviceRequest->category->category_name ?? 'Unclassified' }}</div>
            </div>

            <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Campus</div>
                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $serviceRequest->campus ?? 'BU Main' }}</div>
            </div>

            <div class="bg-blue-50/50 dark:bg-zinc-800/30 p-4 rounded-xl border border-blue-100 dark:border-zinc-700">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Office / Location</div>
                <div class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $serviceRequest->location }}</div>
            </div>
        </div>

        <!-- Supporting Attachment (if any) -->
        @if($serviceRequest->attachment)
            <div class="mt-6 border-t border-gray-100 dark:border-zinc-800 pt-5" x-data="{ attModal: false }">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Attachment / Photo Evidence</div>
                @if(Str::endsWith(strtolower($serviceRequest->attachment), ['.jpg', '.jpeg', '.png', '.webp']))
                    <div @click="attModal = true" 
                         class="inline-flex items-center gap-3 p-3 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl hover:border-[#0033a0] transition group cursor-pointer">
                        <img src="{{ Storage::url($serviceRequest->attachment) }}" alt="Attachment" class="w-14 h-14 object-cover rounded-lg border border-gray-200">
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-[#0033a0] transition">Click to View Photo</div>
                            <div class="text-[11px] text-gray-400">Click to preview in popup modal</div>
                        </div>
                    </div>

                    <!-- Lightbox Modal for Supporting Attachment -->
                    <div x-show="attModal" 
                         x-cloak 
                         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/80 backdrop-blur-xs"
                         @click.outside="attModal = false" 
                         @keydown.escape.window="attModal = false">
                        <div class="relative max-w-4xl w-full max-h-[90vh] bg-zinc-900 rounded-2xl overflow-hidden shadow-2xl border border-zinc-700 flex flex-col items-center">
                            <div class="w-full flex items-center justify-between py-3 px-5 bg-zinc-800 text-white border-b border-zinc-700">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-200">Client Supporting Photo Evidence</span>
                                <button type="button" @click="attModal = false" class="p-1.5 text-gray-400 hover:text-white hover:bg-zinc-700 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="w-full p-4 flex items-center justify-center overflow-auto max-h-[80vh] bg-black/50">
                                <img src="{{ Storage::url($serviceRequest->attachment) }}" alt="Attachment" class="max-h-[75vh] w-auto max-w-full object-contain rounded-lg shadow-lg">
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ Storage::url($serviceRequest->attachment) }}" target="_blank" class="inline-flex items-center gap-3 p-3 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl hover:border-[#0033a0] transition group">
                        <div class="w-12 h-12 bg-blue-100 text-[#0033a0] rounded-lg flex items-center justify-center font-bold text-xs">PDF</div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-[#0033a0] transition">Download Document ↗</div>
                            <div class="text-[11px] text-gray-400">Click to open original file</div>
                        </div>
                    </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Bill of Materials (BOM) Section Card (Direct Pricing & Approval in Request Page) -->
    @if($serviceRequest->project && $serviceRequest->project->billOfMaterials->count() > 0)
        @php
            $pendingBomCount = $serviceRequest->project->billOfMaterials->whereNull('date_approved')->count();
        @endphp
        <div id="bom-section" 
             class="bg-white dark:bg-[#1c1c1e] rounded-2xl border {{ $pendingBomCount > 0 ? 'border-amber-400 dark:border-amber-600' : 'border-gray-200 dark:border-zinc-800' }} p-6 sm:p-7 shadow-sm"
             x-data="{
                 items: {{ Js::from($serviceRequest->project->billOfMaterials->map(fn($b) => [
                     'bom_id' => $b->bom_id,
                     'material_name' => $b->material->material_name ?? 'Material Item',
                     'unit' => $b->material->unit_of_measurement ?? 'pcs',
                     'qty' => (float)$b->qty,
                     'unit_cost' => (float)($b->material->unit_cost ?? 0),
                     'is_approved' => !is_null($b->date_approved),
                 ])) }},
                 showAddMaterial: false,
                 submittingBOM: false,
                 selectedMaterialId: '',
                 customName: '',
                 addUnit: 'pcs',
                 addQty: 1,
                 addUnitCost: 0,
                 catalog: {{ Js::from(($allMaterials ?? collect())->map(fn($m) => ['id' => $m->material_id, 'name' => $m->material_name, 'unit' => $m->unit_of_measurement ?? 'pcs', 'cost' => (float)$m->unit_cost])) }},
                 isDiscrete(unit) {
                     if (!unit) return true;
                     const u = unit.toString().trim().toLowerCase();
                     const continuousUnits = ['meter', 'meters', 'm', 'length', 'lengths', 'ft', 'feet', 'foot', 'liter', 'liters', 'l', 'kg', 'kilo', 'kilos', 'kilogram', 'kilograms', 'gallon', 'gallons', 'gal', 'yard', 'yards', 'yd', 'inch', 'inches', 'cm', 'mm'];
                     return !continuousUnits.includes(u);
                 },
                 get grandTotal() {
                     return this.items.reduce((sum, item) => sum + ((parseFloat(item.qty) || 0) * (parseFloat(item.unit_cost) || 0)), 0);
                 },
                 onSelectAddChange() {
                     if (this.selectedMaterialId && this.selectedMaterialId !== 'custom') {
                         const found = this.catalog.find(m => m.id == this.selectedMaterialId);
                         if (found) {
                             this.addUnit = found.unit || 'pcs';
                             this.addUnitCost = found.cost || 0;
                         }
                     } else if (this.selectedMaterialId === 'custom') {
                         this.addUnitCost = 0;
                         if (!this.addUnit) this.addUnit = 'pcs';
                     }
                 }
             }">
            
            <!-- BOM Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-zinc-800 pb-4 mb-5">
                <div>
                    <div class="flex items-center gap-2.5 mb-1 flex-wrap">
                        <h2 class="text-base font-extrabold text-[#0033a0] dark:text-blue-400 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span>Bill of Materials (BOM) — Pricing & Approval</span>
                        </h2>
                        @if($pendingBomCount > 0)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                {{ $pendingBomCount }} Pending Pricing / Approval
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 uppercase">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Approved
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                        Set or adjust unit prices directly below, then click "Save Prices & Approve BOM" to approve.
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" 
                            @click="showAddMaterial = !showAddMaterial" 
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-800 dark:text-gray-200 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span x-text="showAddMaterial ? 'Close Form' : '+ Add Material'">+ Add Material</span>
                    </button>
                </div>
            </div>

            <!-- In-Place Pricing & Approval Form -->
            <form action="{{ route('admin.bom.approve', $serviceRequest->project->project_id) }}" method="POST" @submit="submittingBOM = true">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ route('admin.requests.show', $serviceRequest->request_id) }}#bom-section">
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-zinc-800 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">
                                <th class="py-2.5 px-3">Material Item</th>
                                <th class="py-2.5 px-3 text-center w-28">Qty</th>
                                <th class="py-2.5 px-3 text-center w-24">Unit</th>
                                <th class="py-2.5 px-3 text-right w-36">Unit Price (₱)</th>
                                <th class="py-2.5 px-3 text-right w-36">Total (₱)</th>
                                <th class="py-2.5 px-3 text-center w-24">Status</th>
                                <th class="py-2.5 px-3 text-center w-14">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-xs">
                            <template x-for="(item, idx) in items" :key="item.bom_id">
                                <tr class="hover:bg-blue-50/40 dark:hover:bg-zinc-800/40 transition">
                                    
                                    <!-- Hidden BOM ID -->
                                    <input type="hidden" :name="'items[' + idx + '][bom_id]'" :value="item.bom_id">

                                    <!-- Material Name -->
                                    <td class="py-3 px-3">
                                        <div class="font-bold text-slate-900 dark:text-white" x-text="item.material_name"></div>
                                    </td>

                                    <!-- Quantity Input (Step 1 for discrete units, 0.01 for continuous) -->
                                    <td class="py-3 px-3 text-center">
                                        <input type="number" 
                                               :name="'items[' + idx + '][qty]'" 
                                               x-model.number="item.qty" 
                                               :step="isDiscrete(item.unit) ? '1' : '0.01'" 
                                               :min="isDiscrete(item.unit) ? '1' : '0.01'" 
                                               class="w-20 px-2 py-1.5 text-center font-bold border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#0033a0]" 
                                               required>
                                    </td>

                                    <!-- Unit of Measurement (Non-editable badge) -->
                                    <td class="py-3 px-3 text-center">
                                        <div class="px-2 py-1 text-center font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-zinc-800/80 rounded-lg text-xs border border-gray-200 dark:border-zinc-700 select-none" x-text="item.unit || 'pcs'"></div>
                                        <input type="hidden" :name="'items[' + idx + '][unit_of_measurement]'" :value="item.unit">
                                    </td>

                                    <!-- Unit Price Input -->
                                    <td class="py-3 px-3 text-right">
                                        <div class="relative inline-block w-32">
                                            <span class="absolute left-2.5 top-1.5 text-xs font-bold text-gray-400">₱</span>
                                            <input type="number" 
                                                   :name="'items[' + idx + '][unit_cost]'" 
                                                   x-model.number="item.unit_cost" 
                                                   step="0.01" 
                                                   min="0" 
                                                   placeholder="0.00" 
                                                   class="w-full pl-6 pr-2.5 py-1.5 text-right font-black border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-[#0033a0] dark:text-blue-400 focus:ring-2 focus:ring-[#0033a0]" 
                                                   required>
                                        </div>
                                    </td>

                                    <!-- Row Total -->
                                    <td class="py-3 px-3 text-right font-black text-slate-900 dark:text-white">
                                        ₱<span x-text="((parseFloat(item.qty) || 0) * (parseFloat(item.unit_cost) || 0)).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3 px-3 text-center">
                                        <span x-show="item.is_approved" class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            Approved
                                        </span>
                                        <span x-show="!item.is_approved" class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            Pending
                                        </span>
                                    </td>

                                    <!-- Delete Item Button -->
                                    <td class="py-3 px-3 text-center">
                                        <button type="button" 
                                                @click="if(confirm('Remove this material from the BOM?')) { document.getElementById('delete-bom-item-' + item.bom_id).submit(); }" 
                                                class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition" 
                                                title="Delete material">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Grand Total & Direct Approval Action Bar -->
                <div class="mt-5 pt-4 border-t border-gray-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-gray-50 dark:bg-zinc-800/50 p-4 rounded-xl">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-bold text-slate-700 dark:text-gray-300">Total Materials Budget:</span>
                        <span class="text-lg sm:text-xl font-black text-[#0033a0] dark:text-blue-400">
                            ₱<span x-text="grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                        </span>
                    </div>

                    <button type="submit" 
                            :disabled="submittingBOM" 
                            class="w-full sm:w-auto px-7 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition shadow-md inline-flex items-center justify-center gap-2 disabled:opacity-60 cursor-pointer">
                        <svg x-show="submittingBOM" x-cloak class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="submittingBOM ? 'Saving & Approving...' : 'Save Prices & Approve BOM'">Save Prices & Approve BOM</span>
                    </button>
                </div>
            </form>

            <!-- Inline Form: Add Additional Material (Toggled) -->
            <div x-show="showAddMaterial" 
                 x-cloak 
                 x-transition 
                 class="mt-5 pt-5 border-t border-gray-200 dark:border-zinc-800 bg-blue-50/40 dark:bg-zinc-800/30 p-4 rounded-xl">
                <h3 class="text-xs font-black uppercase tracking-wider text-[#0033a0] dark:text-blue-400 mb-3 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Item to this Request's BOM</span>
                </h3>

                <form action="{{ route('admin.bom.store', $serviceRequest->project->project_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('admin.requests.show', $serviceRequest->request_id) }}#bom-section">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <!-- Catalog Select -->
                        <div :class="selectedMaterialId === 'custom' ? 'sm:col-span-4' : 'sm:col-span-5'">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Select Catalog Material</label>
                            <select name="material_id" 
                                    x-model="selectedMaterialId" 
                                    @change="onSelectAddChange()" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#0033a0]" 
                                    required>
                                <option value="">Select a material...</option>
                                @foreach($allMaterials ?? [] as $m)
                                    <option value="{{ $m->material_id }}">{{ $m->material_name }} ({{ $m->unit_of_measurement ?? 'pcs' }} - ₱{{ number_format($m->unit_cost, 2) }})</option>
                                @endforeach
                                <option value="custom" class="font-bold text-[#0033a0]">+ Add New Custom Material...</option>
                            </select>
                        </div>

                        <!-- Custom Material Name if 'custom' -->
                        <div class="sm:col-span-3" x-show="selectedMaterialId === 'custom'">
                            <label class="block text-[11px] font-bold text-[#0033a0] uppercase tracking-wider mb-1">Custom Material Name</label>
                            <input type="text" 
                                   name="custom_material_name" 
                                   x-model="customName" 
                                   :required="selectedMaterialId === 'custom'" 
                                   placeholder="e.g. Teflon Tape 1/2 in" 
                                   class="w-full px-3 py-2 border border-[#0033a0] rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white">
                        </div>

                        <!-- Quantity -->
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Quantity</label>
                            <input type="number" 
                                   name="qty" 
                                   x-model.number="addQty" 
                                   :step="isDiscrete(addUnit) ? '1' : '0.01'" 
                                   :min="isDiscrete(addUnit) ? '1' : '0.01'" 
                                   placeholder="1" 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white" 
                                   required>
                        </div>

                        <!-- Unit of Measurement -->
                        <div :class="selectedMaterialId === 'custom' ? 'sm:col-span-1' : 'sm:col-span-2'">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Unit</label>
                            
                            <div x-show="selectedMaterialId !== 'custom'" class="w-full px-3 py-2 border border-gray-200 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800/80 rounded-lg text-xs font-bold text-slate-700 dark:text-gray-300 text-center flex items-center justify-center min-h-[38px] select-none">
                                <span x-text="addUnit || 'pcs'"></span>
                            </div>
                            <input type="hidden" x-show="selectedMaterialId !== 'custom'" name="unit_of_measurement" :value="addUnit">

                            <select x-show="selectedMaterialId === 'custom'" 
                                    name="unit_of_measurement" 
                                    x-model="addUnit" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white">
                                <option value="pcs">pcs</option>
                                <option value="meters">meters</option>
                                <option value="lengths">lengths</option>
                                <option value="rolls">rolls</option>
                                <option value="boxes">boxes</option>
                                <option value="bags">bags</option>
                                <option value="liters">liters</option>
                                <option value="sheets">sheets</option>
                                <option value="sets">sets</option>
                                <option value="units">units</option>
                                <option value="kg">kg</option>
                                <option value="gallons">gallons</option>
                                <option value="pairs">pairs</option>
                                <option value="tubes">tubes</option>
                                <option value="packs">packs</option>
                                <option value="feet">feet</option>
                                <option value="can">can</option>
                            </select>
                        </div>

                        <!-- Unit Cost -->
                        <div :class="selectedMaterialId === 'custom' ? 'sm:col-span-2' : 'sm:col-span-3'">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Unit Price (₱)</label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-2 text-xs font-bold text-gray-400">₱</span>
                                <input type="number" 
                                       name="unit_cost" 
                                       x-model.number="addUnitCost" 
                                       step="0.01" 
                                       min="0" 
                                       placeholder="0.00" 
                                       class="w-full pl-6 pr-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-bold bg-white dark:bg-zinc-800 text-slate-900 dark:text-white" 
                                       required>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="sm:col-span-12 flex justify-end pt-1">
                            <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Item to BOM</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Hidden delete forms for each BOM item -->
            @foreach($serviceRequest->project->billOfMaterials as $bItem)
                <form id="delete-bom-item-{{ $bItem->bom_id }}" action="{{ route('admin.bom.destroy-item', ['projectId' => $serviceRequest->project->project_id, 'bomId' => $bItem->bom_id]) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        </div>
    @endif


    <!-- Clientele Satisfaction Rating Section Card (Displayed when client has rated the request) -->
    @if($serviceRequest->evaluation)
        <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border-2 border-[#0033a0] dark:border-blue-700 p-7 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-zinc-800 pb-4 mb-4">
                <div>
                    <h2 class="text-base font-extrabold text-[#0033a0] dark:text-blue-400 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        Clientele Satisfaction Measurement Rating
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">
                        Submitted {{ ($serviceRequest->evaluation->show_name ?? true) ? 'by ' . ($serviceRequest->client->user->first_name . ' ' . $serviceRequest->client->user->last_name) : 'anonymously' }} on {{ $serviceRequest->evaluation->rated_at ? $serviceRequest->evaluation->rated_at->format('M d, Y h:i A') : 'N/A' }}
                    </p>
                </div>
                <a href="{{ route('admin.requests.satisfaction', $serviceRequest->request_id) }}" target="_blank" class="px-5 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-xl transition shadow-md inline-flex items-center gap-2 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Satisfaction Form
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50/70 dark:bg-zinc-800 p-4 rounded-xl border border-blue-200 dark:border-zinc-700 flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-[#0033a0] text-white font-black text-xl flex items-center justify-center shrink-0 shadow-sm">
                        {{ $serviceRequest->evaluation->rating }}★
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Overall Rating</div>
                        <div class="text-sm font-black text-slate-900 dark:text-white">
                            {{ match((int)$serviceRequest->evaluation->rating) {
                                5 => '5 / 5 — Very Satisfied',
                                4 => '4 / 5 — Satisfied',
                                3 => '3 / 5 — Neutral',
                                2 => '2 / 5 — Dissatisfied',
                                default => '1 / 5 — Very Dissatisfied'
                            } }}
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 bg-slate-50 dark:bg-zinc-800 p-4 rounded-xl border border-gray-200 dark:border-zinc-700">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Client Feedback & Comments</div>
                    <p class="text-xs text-slate-800 dark:text-gray-200 font-medium italic">
                        "{{ $serviceRequest->evaluation->feedback_text ?: 'No additional written feedback provided.' }}"
                    </p>
                </div>
            </div>

            @php
                $funcRatings = $serviceRequest->evaluation->function_ratings;
                $funcLabels = [
                    'quality'      => 'Quality of Service',
                    'attitude'     => 'Attitude',
                    'safety'       => 'Safety Precaution',
                    'time'         => 'Time Bound',
                    'housekeeping' => 'Housekeeping',
                ];
            @endphp
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-zinc-800 flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase mr-1">Rating Breakdown:</span>
                @foreach($funcLabels as $k => $lbl)
                    <span class="px-2.5 py-1 bg-slate-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-lg text-xs font-bold text-slate-800 dark:text-gray-200">
                        {{ $lbl }}: <span class="text-[#0033a0] dark:text-blue-400 font-extrabold">{{ $funcRatings[$k] ?? $serviceRequest->evaluation->rating }}★</span>
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Assigned Personnel & Maintenance Unit Card (Displayed when request is approved and has assigned workers) -->
    @if($serviceRequest->project && $serviceRequest->project->workers->isNotEmpty())
        <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-2xs">
            <h2 class="text-base font-bold text-[#042B74] dark:text-blue-400 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#0038A8] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Assigned Maintenance Personnel & Unit
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($serviceRequest->project->workers as $assignedWorker)
                    @php
                        $workerUser = $assignedWorker->staff->user ?? null;
                        $workerTeam = $assignedWorker->team->team_name ?? 'Maintenance Unit';
                        $isLeader = ($assignedWorker->team && $assignedWorker->team->teamLeader && $assignedWorker->team->teamLeader->staff_id === $assignedWorker->staff_id);
                    @endphp
                    <div class="bg-blue-50/50 dark:bg-zinc-800/60 border border-blue-100 dark:border-zinc-700 rounded-xl p-4 flex items-center gap-3.5 shadow-2xs">
                        <div class="w-10 h-10 rounded-full bg-[#0038A8] text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-2xs">
                            {{ strtoupper(substr($workerUser->first_name ?? 'W', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-gray-900 dark:text-white text-xs flex items-center gap-1.5">
                                <span class="truncate">{{ $workerUser->first_name ?? 'Worker' }} {{ $workerUser->last_name ?? '' }}</span>
                                @if($isLeader)
                                    <span class="text-[10px] font-bold text-[#0038A8] dark:text-blue-300 bg-blue-100 dark:bg-blue-950/80 px-1.5 py-0.2 rounded border border-blue-200 shrink-0">
                                        Leader
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                                {{ $workerTeam }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Action Forms Section -->
    @if(in_array($serviceRequest->current_status, ['Submitted', 'Pending']))
        <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-7 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#0033a0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Action Required: Review & Process Request
            </h2>
            
            <div class="flex flex-col lg:flex-row gap-6 items-stretch">
                <!-- Approve Form -->
                <form action="{{ route('admin.requests.approve', $serviceRequest->request_id) }}" method="POST" class="flex-1 bg-[#f0f6ff] dark:bg-zinc-800/50 p-6 rounded-2xl border border-blue-200 dark:border-zinc-700 shadow-sm flex flex-col justify-between">
                    @csrf
                    
                    <div>
                        <div class="flex items-center gap-2 text-[#0033a0] dark:text-blue-400 font-bold text-sm mb-4">
                            <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center font-extrabold">1</span>
                            Approve Request & Assign Project
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-1.5">Verify Category</label>
                                <select name="category_id" id="categorySelect" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->category_id }}" data-name="{{ strtolower($category->category_name) }}" {{ $serviceRequest->category_id == $category->category_id ? 'selected' : '' }}>
                                            {{ $category->category_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-1.5">Set Project Priority</label>
                                <select name="priority" class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-gray-200 focus:outline-none focus:border-[#0033a0]" required>
                                    <option value="Low" {{ strtolower($serviceRequest->priority ?? 'low') === 'low' ? 'selected' : '' }}>Low Priority</option>
                                    <option value="Medium" {{ strtolower($serviceRequest->priority ?? 'low') === 'medium' ? 'selected' : '' }}>Medium Priority</option>
                                    <option value="High" {{ strtolower($serviceRequest->priority ?? 'low') === 'high' ? 'selected' : '' }}>High Priority</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-2">Assign Maintenance Workers</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-64 overflow-y-auto p-2.5 border border-blue-200 dark:border-zinc-700 rounded-xl bg-white dark:bg-zinc-900">
                                @foreach($workers as $worker)
                                    @php
                                        $teamName = strtolower($worker->team->team_name ?? '');
                                        $categoryName = strtolower($serviceRequest->category->category_name ?? '');
                                        $isRecommended = false;
                                        if ($teamName && $categoryName) {
                                            preg_match_all('/\w+/', $categoryName, $catWords);
                                            foreach ($catWords[0] as $word) {
                                                if (strlen($word) > 3 && str_contains($teamName, $word)) {
                                                    $isRecommended = true;
                                                    break;
                                                }
                                            }
                                        }
                                        $activeCount = $worker->projects->count();
                                    @endphp
                                    <label class="worker-option flex items-center gap-2.5 cursor-pointer p-2.5 hover:bg-blue-50 dark:hover:bg-zinc-800 rounded-xl border border-gray-100 dark:border-zinc-800 {{ $isRecommended ? 'bg-blue-50/80 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800' : '' }} transition-colors min-w-0" data-team="{{ strtolower($worker->team->team_name ?? '') }}">
                                        <input type="checkbox" name="worker_ids[]" value="{{ $worker->worker_id }}" {{ $isRecommended ? 'checked' : '' }} class="worker-checkbox rounded text-[#0033a0] focus:ring-[#0033a0] w-4 h-4 shrink-0">
                                        <div class="flex-1 min-w-0 flex items-center justify-between gap-1.5">
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-slate-900 dark:text-gray-200 truncate" title="{{ $worker->user->first_name ?? 'Unknown' }} {{ $worker->user->last_name ?? '' }}">
                                                    {{ $worker->user->first_name ?? 'Unknown' }} {{ $worker->user->last_name ?? '' }}
                                                </div>
                                                <div class="text-[11px] text-gray-500 truncate flex items-center gap-1.5" title="{{ $worker->team->team_name ?? 'No Unit' }}">
                                                    <span>{{ $worker->team->team_name ?? 'No Unit' }}</span>
                                                    <span>•</span>
                                                    @if($activeCount === 0)
                                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">🟢 Available</span>
                                                    @elseif($activeCount === 1)
                                                        <span class="text-amber-600 dark:text-amber-400 font-bold">🟡 Busy (1 Active)</span>
                                                    @else
                                                        <span class="text-amber-600 dark:text-amber-400 font-bold">🟡 Busy (1 Active, {{ $activeCount - 1 }} Queued)</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="recommended-badge text-[9px] bg-[#0033a0] text-white px-2 py-0.5 rounded-full font-extrabold uppercase tracking-wide shrink-0 {{ $isRecommended ? '' : 'hidden' }}">Recommended</span>
                                        </div>
                                    </label>

                                @endforeach
                                @if($workers->isEmpty())
                                    <p class="text-xs text-gray-400 p-2 italic col-span-2">No active workers found in database.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-[#0033a0] hover:bg-[#002480] text-white font-bold py-3 px-4 rounded-xl transition shadow-md flex justify-center items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Approve & Launch Project
                    </button>
                </form>

                <!-- Reject Form -->
                <form action="{{ route('admin.requests.reject', $serviceRequest->request_id) }}" method="POST" class="w-full lg:w-80 bg-red-50/60 dark:bg-red-950/20 p-6 rounded-2xl border border-red-200 dark:border-red-900/50 shadow-sm flex flex-col justify-between">
                    @csrf
                    <div>
                        <div class="flex items-center gap-2 text-red-700 dark:text-red-400 font-bold text-sm mb-4">
                            <span class="w-6 h-6 rounded-full bg-red-600 text-white text-xs flex items-center justify-center font-extrabold">2</span>
                            Reject Request
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 mb-1.5">Reason for Rejection <span class="text-red-500">*</span></label>
                            <textarea name="feedback" rows="5" placeholder="State why this request cannot be processed..." class="w-full p-3 bg-white dark:bg-zinc-900 border border-red-200 dark:border-zinc-700 rounded-xl text-xs text-slate-800 dark:text-gray-200 focus:outline-none focus:border-red-500" required></textarea>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl transition flex justify-center items-center gap-2 text-sm shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        Reject Request
                    </button>
                </form>
            </div>
        </div>
    @elseif($serviceRequest->project && $serviceRequest->project->current_status === 'Pending Verification')
        @php
            $beforeHistory = $serviceRequest->project->histories->where('current_status', 'In Progress')->whereNotNull('proof_attachment')->last();
            $afterHistory = $serviceRequest->project->histories->whereIn('current_status', ['Pending Verification', 'Completed'])->whereNotNull('proof_attachment')->last();
        @endphp
        <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border-2 border-blue-400 dark:border-blue-700 p-7 shadow-sm"
             x-data="{ lightboxOpen: false, lightboxImg: '', lightboxTitle: '' }">
            <div class="flex items-center justify-between mb-3 border-b border-gray-100 dark:border-zinc-800 pb-3">
                <h2 class="text-base font-bold text-[#0033a0] dark:text-blue-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Worker Completed Job — Pending Final Admin Verification
                </h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300">
                    Awaiting Inspection
                </span>
            </div>
            
            <p class="text-xs text-gray-500 mb-6">Inspect the before & after work evidence photos below to ensure the task was completed satisfactorily before closing this requisition.</p>

            <!-- Before & After Photos Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Before Photo -->
                <div class="bg-gray-50 dark:bg-zinc-800/60 p-4 rounded-xl border border-gray-200 dark:border-zinc-700 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                                BEFORE WORK PHOTO
                            </span>
                            @if($beforeHistory)
                                <span class="text-[10px] text-gray-400 font-medium">{{ \Carbon\Carbon::parse($beforeHistory->updated_at)->format('M d, Y h:i A') }}</span>
                            @endif
                        </div>
                        @if($beforeHistory && $beforeHistory->proof_attachment)
                            <div @click="lightboxOpen = true; lightboxImg = '{{ Storage::url($beforeHistory->proof_attachment) }}'; lightboxTitle = 'Before Work Photo'" 
                                 class="block group relative overflow-hidden rounded-xl border border-gray-200 dark:border-zinc-700 bg-black/5 dark:bg-black/40 p-2 cursor-pointer transition hover:border-amber-400">
                                <img src="{{ Storage::url($beforeHistory->proof_attachment) }}" alt="Before Work" class="w-full max-h-64 object-contain rounded-lg group-hover:scale-[1.01] transition duration-200 mx-auto">
                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1.5 rounded-xl">
                                    <span class="bg-black/60 px-3 py-1.5 rounded-lg backdrop-blur-xs">Click to Preview</span>
                                </div>
                            </div>
                        @else
                            <div class="min-h-[140px] bg-gray-100 dark:bg-zinc-800/40 rounded-xl flex items-center justify-center text-xs text-gray-400 font-medium border border-dashed border-gray-200 dark:border-zinc-700">
                                No before photo attached
                            </div>
                        @endif
                    </div>
                </div>

                <!-- After Photo -->
                <div class="bg-gray-50 dark:bg-zinc-800/60 p-4 rounded-xl border border-gray-200 dark:border-zinc-700 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                AFTER WORK PHOTO (COMPLETION)
                            </span>
                            @if($afterHistory)
                                <span class="text-[10px] text-gray-400 font-medium">{{ \Carbon\Carbon::parse($afterHistory->updated_at)->format('M d, Y h:i A') }}</span>
                            @endif
                        </div>
                        @if($afterHistory && $afterHistory->proof_attachment)
                            <div @click="lightboxOpen = true; lightboxImg = '{{ Storage::url($afterHistory->proof_attachment) }}'; lightboxTitle = 'After Work Photo (Completion)'" 
                                 class="block group relative overflow-hidden rounded-xl border border-gray-200 dark:border-zinc-700 bg-black/5 dark:bg-black/40 p-2 cursor-pointer transition hover:border-emerald-400">
                                <img src="{{ Storage::url($afterHistory->proof_attachment) }}" alt="After Work" class="w-full max-h-64 object-contain rounded-lg group-hover:scale-[1.01] transition duration-200 mx-auto">
                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1.5 rounded-xl">
                                    <span class="bg-black/60 px-3 py-1.5 rounded-lg backdrop-blur-xs">Click to Preview</span>
                                </div>
                            </div>
                        @else
                            <div class="min-h-[140px] bg-gray-100 dark:bg-zinc-800/40 rounded-xl flex items-center justify-center text-xs text-gray-400 font-medium border border-dashed border-gray-200 dark:border-zinc-700">
                                Pending completion upload
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Verification Action Section -->
            @php 
                $isWorkerInspectionOnly = ($serviceRequest->project?->nature_of_work === 'Inspection & Assessment Only'); 
            @endphp
            <div class="bg-blue-50/70 dark:bg-zinc-800/70 p-6 rounded-2xl border-2 border-blue-200 dark:border-zinc-700 space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-blue-200/80 dark:border-zinc-700">
                    <div>
                        <h3 class="text-sm font-bold text-[#0033a0] dark:text-blue-400 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Verify Project Completion
                        </h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                            @if($isWorkerInspectionOnly)
                                Worker completed this job as <strong>Inspection &amp; Assessment Only</strong>. Review the returned form and findings before closing.
                            @else
                                Inspect the returned physical paper form. Type the nature of work and findings written by the team before closing the request.
                            @endif
                        </p>
                    </div>
                    @if($isWorkerInspectionOnly)
                        <span class="px-3.5 py-1.5 bg-amber-100 text-amber-900 border border-amber-300 text-xs font-bold rounded-full inline-flex items-center gap-1.5 shrink-0 shadow-xs">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Inspection &amp; Assessment Only
                        </span>
                    @elseif($serviceRequest->project?->nature_of_work)
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 border border-blue-300 text-xs font-bold rounded-full inline-flex items-center gap-1.5 shrink-0">
                            {{ $serviceRequest->project->nature_of_work }}
                        </span>
                    @endif
                </div>

                <form action="{{ route('admin.requests.verify', $serviceRequest->request_id) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    @if($isWorkerInspectionOnly)
                        <input type="hidden" name="nature_of_work" value="Inspection & Assessment Only">
                        <!-- Inspection Findings from Paper -->
                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 uppercase tracking-wider mb-1.5">
                                Inspection Findings &amp; Recommendations Written on Paper (Optional):
                            </label>
                            <textarea name="work_details" 
                                      rows="2" 
                                      placeholder="e.g. Conducted on-site inspection; circuit breaker reset and functioning properly / referred to external contractor."
                                      class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs text-gray-800 dark:text-white focus:outline-none focus:border-[#0033a0]">{{ $serviceRequest->project?->recommendation ?? '' }}</textarea>
                        </div>
                    @else
                        <!-- Work Details / Nature of Work written on paper -->
                        <div>
                            <label class="block text-xs font-bold text-slate-800 dark:text-gray-200 uppercase tracking-wider mb-1.5">
                                Work Details / Findings Written on Paper (Nature of Work Done):
                            </label>
                            <textarea name="work_details" 
                                      rows="2.5" 
                                      placeholder="Type what the worker wrote on the physical paper (e.g. Replaced 4 fluorescent light bulbs and checked electrical lines)..."
                                      class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs text-gray-800 dark:text-white focus:outline-none focus:border-[#0033a0]">{{ $serviceRequest->project?->nature_of_work && $serviceRequest->project->nature_of_work !== 'Repair & Maintenance Done' ? $serviceRequest->project->nature_of_work : ($serviceRequest->project?->recommendation ?? '') }}</textarea>
                        </div>
                    @endif

                    <div class="flex justify-end pt-1">
                        <button type="submit" class="w-full sm:w-auto bg-[#0033a0] hover:bg-[#002480] text-white font-bold py-3 px-7 rounded-xl transition shadow-md flex justify-center items-center gap-2 text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Verify Completion &amp; Close Request
                        </button>
                    </div>
                </form>
            </div>

            <!-- Lightbox Modal Popup -->
            <div x-show="lightboxOpen" 
                 x-cloak 
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/80 backdrop-blur-xs"
                 @keydown.escape.window="lightboxOpen = false"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                
                <div class="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center bg-zinc-900 rounded-2xl overflow-hidden shadow-2xl border border-zinc-700" 
                     @click.outside="lightboxOpen = false">
                    <!-- Header Bar -->
                    <div class="w-full flex items-center justify-between py-3 px-5 bg-zinc-800 text-white border-b border-zinc-700">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-200" x-text="lightboxTitle"></span>
                        <button type="button" @click="lightboxOpen = false" class="p-1.5 text-gray-400 hover:text-white hover:bg-zinc-700 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <!-- Adaptive Image Area -->
                    <div class="w-full p-4 flex items-center justify-center overflow-auto max-h-[80vh] bg-black/50">
                        <img :src="lightboxImg" alt="Enlarged Photo" class="max-h-[75vh] w-auto max-w-full object-contain rounded-lg shadow-lg">
                    </div>
                </div>
            </div>
        </div>

    @elseif($serviceRequest->current_status === 'Rejected')
        @php
            $rejectionHistory = $serviceRequest->histories->where('current_status', 'Rejected')->last();
        @endphp
        <div class="bg-red-50/90 dark:bg-red-950/40 border-2 border-red-300 dark:border-red-800 rounded-2xl p-6 sm:p-7 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-red-100 dark:bg-red-900/70 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0 mt-0.5 border border-red-200 dark:border-red-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-base sm:text-lg font-black text-red-900 dark:text-red-300 uppercase tracking-tight mb-1">
                        This Requisition Was Disapproved / Rejected
                    </h2>
                    <p class="text-xs text-red-700 dark:text-red-400 mb-3">
                        Rejection logged on {{ $rejectionHistory ? \Carbon\Carbon::parse($rejectionHistory->updated_at)->format('F d, Y \a\t h:i A') : 'N/A' }} 
                        @if($rejectionHistory && $rejectionHistory->updatedBy)
                            by {{ $rejectionHistory->updatedBy->first_name ?? '' }} {{ $rejectionHistory->updatedBy->last_name ?? '' }}
                        @endif
                    </p>
                    
                    <div class="bg-white dark:bg-zinc-900 p-4 rounded-xl border border-red-200 dark:border-red-800/80 shadow-2xs">
                        <div class="text-[11px] font-extrabold text-red-600 dark:text-red-400 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <span>Logged Reason for Rejection / Recommendation:</span>
                        </div>
                        <p class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white leading-relaxed whitespace-pre-line">
                            {{ $rejectionHistory && $rejectionHistory->remarks ? $rejectionHistory->remarks : 'No specific reason entered.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Per-Request Messaging Channel -->
    @include('partials.request-messages', ['serviceRequest' => $serviceRequest])
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('categorySelect');
    if (!categorySelect) return;

    function updateRecommendations() {
        const selectedOpt = categorySelect.options[categorySelect.selectedIndex];
        const catName = selectedOpt ? (selectedOpt.getAttribute('data-name') || '') : '';
        const words = (catName.match(/\w+/g) || []).filter(w => w.length > 3);

        document.querySelectorAll('.worker-option').forEach(option => {
            const teamName = option.getAttribute('data-team') || '';
            const checkbox = option.querySelector('.worker-checkbox');
            const badge = option.querySelector('.recommended-badge');

            let isRec = false;
            for (const word of words) {
                if (teamName.includes(word)) {
                    isRec = true;
                    break;
                }
            }

            if (isRec) {
                option.classList.add('bg-blue-50/80', 'border-blue-200');
                badge.classList.remove('hidden');
                checkbox.checked = true;
            } else {
                option.classList.remove('bg-blue-50/80', 'border-blue-200');
                badge.classList.add('hidden');
                checkbox.checked = false;
            }
        });
    }

    categorySelect.addEventListener('change', updateRecommendations);
});
</script>
@endpush
@endsection
