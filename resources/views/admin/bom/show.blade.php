@extends('layouts.admin')

@section('page-title', 'Review & Price Bill of Materials')

@section('content')
<div class="w-full max-w-6xl mx-auto space-y-6 font-sans">
    
    <!-- Top Header Banner -->
    <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-6 sm:px-8 py-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2 flex-wrap">
                <span class="px-3 py-1 bg-[#0033a0] text-white text-[11px] font-extrabold uppercase tracking-wider rounded-full shadow-sm">
                    Project #{{ str_pad($project->project_id, 4, '0', STR_PAD_LEFT) }}
                </span>
                @if($project->request)
                    <span class="px-3 py-1 bg-blue-100 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-300 text-[11px] font-extrabold uppercase tracking-wider rounded-full">
                        {{ $project->request->requisition_no ?: ('REQ-' . str_pad($project->request->request_id, 3, '0', STR_PAD_LEFT)) }}
                    </span>
                @endif
                <span class="px-3 py-1 text-[11px] font-extrabold uppercase tracking-wider rounded-full border {{ $project->current_status === 'On Hold' ? 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                    {{ $project->current_status }}
                </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Bill of Materials (BOM) Pricing & Approval
            </h1>

            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">
                For Requisition: <span class="font-bold text-slate-800 dark:text-gray-200">{{ $project->request->title ?? 'General Maintenance Project' }}</span>
                • Client: <span class="font-bold text-slate-800 dark:text-gray-200">{{ $project->request->client->user->first_name ?? 'N/A' }} {{ $project->request->client->user->last_name ?? '' }}</span>
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0 flex-wrap">
            <a href="{{ route('admin.bom.index') }}" class="px-4 py-2 bg-white dark:bg-zinc-800 hover:bg-gray-50 border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-xl transition shadow-xs inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to BOMs</span>
            </a>

            @if($project->request)
                <a href="{{ route('admin.requests.show', $project->request->request_id) }}" class="px-4 py-2 bg-[#0033a0] hover:bg-[#002480] text-white text-xs font-bold rounded-xl transition shadow-xs inline-flex items-center gap-1.5">
                    <span>View Requisition</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 rounded-xl text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-2 shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-50 dark:bg-red-950/40 border border-red-300 dark:border-red-800 rounded-xl text-xs font-bold text-red-800 dark:text-red-300 flex items-center gap-2 shadow-xs">
            <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Pricing Table Form Area -->
    <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-6 sm:p-7 shadow-sm"
         x-data="{
             items: {{ Js::from($project->billOfMaterials->map(fn($b) => [
                 'bom_id' => $b->bom_id,
                 'material_name' => $b->material->material_name ?? 'Material',
                 'unit' => $b->material->unit_of_measurement ?? 'pcs',
                 'qty' => (float)$b->qty,
                 'unit_cost' => (float)($b->material->unit_cost ?? 0),
                 'is_approved' => !is_null($b->date_approved),
             ])) }},
             submitting: false,
             isDiscrete(unit) {
                 if (!unit) return true;
                 const u = unit.toString().trim().toLowerCase();
                 const continuousUnits = ['meter', 'meters', 'm', 'length', 'lengths', 'ft', 'feet', 'foot', 'liter', 'liters', 'l', 'kg', 'kilo', 'kilos', 'kilogram', 'kilograms', 'gallon', 'gallons', 'gal', 'yard', 'yards', 'yd', 'inch', 'inches', 'cm', 'mm'];
                 return !continuousUnits.includes(u);
             },
             get grandTotal() {
                 return this.items.reduce((sum, item) => sum + ((parseFloat(item.qty) || 0) * (parseFloat(item.unit_cost) || 0)), 0);
             }
         }">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-gray-100 dark:border-zinc-800">
            <div>
                <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Requested Materials & Unit Pricing</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Review requested items, enter or update current unit prices (₱), and approve the bill of materials.
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3 py-1 rounded-full text-xs font-extrabold {{ $pendingCount > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                    {{ $pendingCount > 0 ? ($pendingCount . ' Pending Pricing/Approval') : 'All Approved' }}
                </span>
            </div>
        </div>

        @if($project->billOfMaterials->count() > 0)
            <form action="{{ route('admin.bom.approve', $project->project_id) }}" method="POST" @submit="submitting = true">
                @csrf
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-zinc-800 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">
                                <th class="py-3 px-3">Material Item</th>
                                <th class="py-3 px-3 text-center w-28">Qty</th>
                                <th class="py-3 px-3 text-center w-28">Unit</th>
                                <th class="py-3 px-3 text-right w-40">Unit Price (₱)</th>
                                <th class="py-3 px-3 text-right w-36">Total (₱)</th>
                                <th class="py-3 px-3 text-center w-28">Status</th>
                                <th class="py-3 px-3 text-center w-16">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 text-xs">
                            <template x-for="(item, idx) in items" :key="item.bom_id">
                                <tr class="hover:bg-blue-50/40 dark:hover:bg-zinc-800/40 transition">
                                    
                                    <!-- Hidden BOM ID -->
                                    <input type="hidden" :name="'items[' + idx + '][bom_id]'" :value="item.bom_id">

                                    <!-- Material Name -->
                                    <td class="py-3.5 px-3">
                                        <div class="font-bold text-slate-900 dark:text-white text-sm" x-text="item.material_name"></div>
                                    </td>

                                    <!-- Quantity Input (Step 1 for discrete units, 0.01 for continuous) -->
                                    <td class="py-3.5 px-3 text-center">
                                        <input type="number" 
                                               :name="'items[' + idx + '][qty]'" 
                                               x-model.number="item.qty" 
                                               :step="isDiscrete(item.unit) ? '1' : '0.01'" 
                                               :min="isDiscrete(item.unit) ? '1' : '0.01'" 
                                               class="w-24 px-2.5 py-1.5 text-center font-bold border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#0033a0]" 
                                               required>
                                    </td>

                                    <!-- Unit of Measurement (Non-editable badge) -->
                                    <td class="py-3.5 px-3 text-center">
                                        <div class="px-2.5 py-1.5 text-center font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-zinc-800/80 rounded-lg text-xs border border-gray-200 dark:border-zinc-700 select-none" x-text="item.unit || 'pcs'"></div>
                                        <input type="hidden" :name="'items[' + idx + '][unit_of_measurement]'" :value="item.unit">
                                    </td>

                                    <!-- Unit Price Input -->
                                    <td class="py-3.5 px-3 text-right">
                                        <div class="relative inline-block w-36">
                                            <span class="absolute left-3 top-2 text-xs font-bold text-gray-400">₱</span>
                                            <input type="number" 
                                                   :name="'items[' + idx + '][unit_cost]'" 
                                                   x-model.number="item.unit_cost" 
                                                   step="0.01" 
                                                   min="0" 
                                                   placeholder="0.00" 
                                                   class="w-full pl-7 pr-3 py-1.5 text-right font-black border border-gray-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-[#0033a0] dark:text-blue-400 focus:ring-2 focus:ring-[#0033a0]" 
                                                   required>
                                        </div>
                                    </td>

                                    <!-- Row Total -->
                                    <td class="py-3.5 px-3 text-right font-black text-slate-900 dark:text-white">
                                        ₱<span x-text="((parseFloat(item.qty) || 0) * (parseFloat(item.unit_cost) || 0)).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3.5 px-3 text-center">
                                        <span x-show="item.is_approved" class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            Approved
                                        </span>
                                        <span x-show="!item.is_approved" class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            Pending
                                        </span>
                                    </td>

                                    <!-- Delete Item Button -->
                                    <td class="py-3.5 px-3 text-center">
                                        <button type="button" 
                                                @click="if(confirm('Remove this material from the BOM?')) { document.getElementById('delete-bom-' + item.bom_id).submit(); }" 
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

                <!-- Grand Total & Action Bar -->
                <div class="mt-6 pt-5 border-t border-gray-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-gray-50 dark:bg-zinc-800/50 p-5 rounded-2xl">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold text-slate-700 dark:text-gray-300">Estimated Total Bill of Materials:</span>
                        <span class="text-xl sm:text-2xl font-black text-[#0033a0] dark:text-blue-400">
                            ₱<span x-text="grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                        </span>
                    </div>

                    <button type="submit" 
                            :disabled="submitting" 
                            class="w-full sm:w-auto px-8 py-3 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-sm font-bold transition shadow-md inline-flex items-center justify-center gap-2 disabled:opacity-60 cursor-pointer">
                        <svg x-show="submitting" x-cloak class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="submitting ? 'Saving & Approving...' : 'Save Prices & Approve BOM'">Save Prices & Approve BOM</span>
                    </button>
                </div>
            </form>

            <!-- Hidden delete forms -->
            @foreach($project->billOfMaterials as $bItem)
                <form id="delete-bom-{{ $bItem->bom_id }}" action="{{ route('admin.bom.destroy-item', ['projectId' => $project->project_id, 'bomId' => $bItem->bom_id]) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach

        @else
            <div class="p-12 text-center text-gray-400 bg-gray-50 dark:bg-zinc-800/30 rounded-2xl border border-dashed border-gray-200 dark:border-zinc-700">
                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <h3 class="text-sm font-bold text-gray-600 dark:text-gray-300">No materials requested yet for this project.</h3>
                <p class="text-xs text-gray-400 mt-1">You can add materials manually using the form below.</p>
            </div>
        @endif
    </div>

    <!-- Add Additional Material Card (Admin) -->
    <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl border border-gray-200 dark:border-zinc-800 p-6 sm:p-7 shadow-sm"
         x-data="{
             selectedMaterialId: '',
             customName: '',
             unit: 'pcs',
             qty: 1,
             unitCost: 0,
             catalog: {{ Js::from($allMaterials->map(fn($m) => ['id' => $m->material_id, 'name' => $m->material_name, 'unit' => $m->unit_of_measurement ?? 'pcs', 'cost' => (float)$m->unit_cost])) }},
             isDiscrete(unit) {
                 if (!unit) return true;
                 const u = unit.toString().trim().toLowerCase();
                 const continuousUnits = ['meter', 'meters', 'm', 'length', 'lengths', 'ft', 'feet', 'foot', 'liter', 'liters', 'l', 'kg', 'kilo', 'kilos', 'kilogram', 'kilograms', 'gallon', 'gallons', 'gal', 'yard', 'yards', 'yd', 'inch', 'inches', 'cm', 'mm'];
                 return !continuousUnits.includes(u);
             },
             onSelectChange() {
                 if (this.selectedMaterialId && this.selectedMaterialId !== 'custom') {
                     const found = this.catalog.find(m => m.id == this.selectedMaterialId);
                     if (found) {
                         this.unit = found.unit || 'pcs';
                         this.unitCost = found.cost || 0;
                     }
                 } else if (this.selectedMaterialId === 'custom') {
                     this.unitCost = 0;
                     if (!this.unit) this.unit = 'pcs';
                 }
             }
         }">
        
        <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add Additional Material to this Project</span>
        </h3>

        <form action="{{ route('admin.bom.store', $project->project_id) }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                
                <!-- Catalog Select -->
                <div :class="selectedMaterialId === 'custom' ? 'sm:col-span-4' : 'sm:col-span-5'">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Select Catalog Material</label>
                    <select name="material_id" 
                            x-model="selectedMaterialId" 
                            @change="onSelectChange()" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#0033a0]" 
                            required>
                        <option value="">Select a material...</option>
                        @foreach($allMaterials as $m)
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
                           x-model.number="qty" 
                           :step="isDiscrete(unit) ? '1' : '0.01'" 
                           :min="isDiscrete(unit) ? '1' : '0.01'" 
                           placeholder="1" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white" 
                           required>
                </div>

                <!-- Unit of Measurement (Non-editable for catalog items, select dropdown for custom items) -->
                <div :class="selectedMaterialId === 'custom' ? 'sm:col-span-1' : 'sm:col-span-2'">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Unit</label>
                    
                    <!-- Non-editable display for catalog material -->
                    <div x-show="selectedMaterialId !== 'custom'" class="w-full px-3 py-2 border border-gray-200 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800/80 rounded-lg text-xs font-bold text-slate-700 dark:text-gray-300 text-center flex items-center justify-center min-h-[38px] select-none">
                        <span x-text="unit || 'pcs'"></span>
                    </div>
                    <input type="hidden" x-show="selectedMaterialId !== 'custom'" name="unit_of_measurement" :value="unit">

                    <!-- Predefined dropdown for custom material items -->
                    <select x-show="selectedMaterialId === 'custom'" 
                            name="unit_of_measurement" 
                            x-model="unit" 
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
                               x-model.number="unitCost" 
                               step="0.01" 
                               min="0" 
                               placeholder="0.00" 
                               class="w-full pl-6 pr-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-bold bg-white dark:bg-zinc-800 text-slate-900 dark:text-white" 
                               required>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="sm:col-span-12 flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Item to BOM</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection

