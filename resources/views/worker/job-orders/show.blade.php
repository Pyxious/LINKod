@extends('layouts.worker')
@section('page-title', 'Job Order Details')

@section('content')

@php
    $reqId = $project->request_id;
    $catName = strtolower($project->request->category->category_name ?? '');
    $prefix = match(true) {
        str_contains($catName, 'landscaping') => 'LS',
        str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
        str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
        str_contains($catName, 'plumbing') => 'PS',
        default => 'REQ'
    };
    $reqCode = $reqId ? ($prefix . '-' . str_pad($reqId, 3, '0', STR_PAD_LEFT)) : ('REQ-'.str_pad($project->project_id, 3, '0', STR_PAD_LEFT));
@endphp

<!-- Header -->
<div class="flex justify-between items-center mb-6">
    <div>
        <a href="{{ route('worker.job-orders.index') }}" class="text-xs font-semibold text-gray-500 hover:text-[#0038A8] flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Job Orders
        </a>
        <h1 class="text-[#042B74] dark:text-blue-400 text-2xl font-bold flex items-center gap-3">
            Requisition #{{ $reqCode }}
            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                @if($project->current_status === 'Pending') bg-red-50 text-red-700 border border-red-200
                @elseif($project->current_status === 'In Progress') bg-amber-50 text-amber-700 border border-amber-200
                @elseif($project->current_status === 'Pending Verification') bg-blue-50 text-blue-700 border border-blue-200
                @else bg-emerald-50 text-emerald-700 border border-emerald-200 @endif">
                {{ $project->current_status }}
            </span>
        </h1>
    </div>
    
    @if($project->current_status === 'Completed')
    <div>
        <a href="{{ route('worker.maintenance.create', $project->project_id) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Submit Maintenance Report
        </a>
    </div>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Info (Left) -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-7">
            <h2 class="text-xl font-bold text-gray-900 mb-4">{{ $project->request->title ?? 'Untitled Job Order' }}</h2>
            
            <div class="prose prose-sm text-gray-600 max-w-none mb-6">
                {{ $project->request->description ?? 'No description provided by the client.' }}
            </div>
            
            @if($project->request->attachment)
                <div class="mt-6 border-t border-gray-100 pt-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-3">Supporting Documents</h3>
                    <a href="{{ Storage::url($project->request->attachment) }}" target="_blank" class="inline-flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition w-full max-w-sm">
                        <div class="bg-blue-50 text-blue-600 w-10 h-10 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-700 truncate">Attachment</div>
                            <div class="text-xs text-gray-500">Click to view file</div>
                        </div>
                    </a>
                </div>
            @endif
        </div>

        @if(!in_array($project->current_status, ['Completed', 'Pending Verification']))
        <!-- Update Task Status -->
        <div class="bg-[#f0f6ff] border border-[#1a3c8f] rounded-xl shadow-sm p-7">
            <h3 class="text-[#1a3c8f] font-bold text-lg mb-4">Update Task Progress</h3>
            <form action="{{ route('worker.task-progress.update', $project->project_id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="flex items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-[#1a3c8f] mb-2">Mark Current Status As:</label>
                        <select name="status" id="status-select" class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" required onchange="toggleProofInput()">
                            <option value="In Progress" {{ $project->current_status === 'In Progress' ? 'selected' : '' }}>In Progress (Working on it)</option>
                            <option value="Completed" {{ $project->current_status === 'Completed' ? 'selected' : '' }}>Completed (Job Finished)</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-[#1a3c8f] text-white px-8 py-3 rounded-lg text-sm font-semibold hover:bg-[#152e6e] transition shadow-sm">
                        Save Update
                    </button>
                </div>
                
                <div id="proof-container" style="display: {{ $project->current_status === 'Completed' ? 'block' : 'none' }};">
                    <label class="block text-sm font-semibold text-[#1a3c8f] mb-2 mt-4">Attach Proof of Completion (Image/PDF):</label>
                    <input type="file" name="proof" accept="image/*" capture="environment" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-[#1a3c8f] focus:border-[#1a3c8f]">
                </div>
            </form>
            <p class="text-xs text-[#1a3c8f] opacity-80 mt-4">
                <span class="font-bold">Note:</span> Marking a task as completed will require a photo proof and notify the admin and client. You will then be prompted to fill out a maintenance report.
            </p>
        </div>
        
        <script>
            function toggleProofInput() {
                const select = document.getElementById('status-select');
                const container = document.getElementById('proof-container');
                container.style.display = select.value === 'Completed' ? 'block' : 'none';
            }
        </script>
        @endif

        <!-- Material Requisition (Optional) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-7 mt-6">
            <h3 class="text-gray-900 font-bold text-lg mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Request Materials (Optional)
            </h3>
            
            @if($project->billOfMaterials->count() > 0)
                <div class="mb-6">
                    <h4 class="text-sm font-bold text-gray-700 mb-3">Previously Requested Materials</h4>
                    <ul class="space-y-2">
                        @foreach($project->billOfMaterials as $bom)
                            <li class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-100">
                                <div>
                                    <div class="text-sm font-semibold text-gray-800">{{ $bom->material->material_name ?? 'Unknown' }} (x{{ $bom->qty }})</div>
                                    <div class="text-xs text-gray-500">Requested on {{ \Carbon\Carbon::parse($bom->created_at ?? $project->date_assigned)->format('M d, Y') }}</div>
                                </div>
                                <div>
                                    @if($bom->date_approved)
                                        <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-md uppercase">Approved</span>
                                    @else
                                        <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-md uppercase">Pending</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($project->current_status !== 'Completed' && $project->current_status !== 'Pending Verification')
                <form action="{{ route('worker.bom.store', $project->project_id) }}" method="POST">
                    @csrf
                    <div id="material-rows" class="space-y-3 mb-4">
                        <div class="flex gap-3 material-row">
                            <select name="items[0][material_id]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" required>
                                <option value="">Select Material...</option>
                                @foreach($materials ?? [] as $m)
                                    <option value="{{ $m->material_id }}">{{ $m->material_name }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="items[0][qty]" min="0.01" step="0.01" placeholder="Qty" class="w-24 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" required>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <button type="button" onclick="addMaterialRow()" class="text-[#1a3c8f] text-sm font-semibold hover:underline flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Another Item
                        </button>
                        <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-gray-900 transition">
                            Submit Request
                        </button>
                    </div>
                </form>
                <script>
                    let rowCount = 1;
                    function addMaterialRow() {
                        const container = document.getElementById('material-rows');
                        const newRow = document.createElement('div');
                        newRow.className = 'flex gap-3 material-row';
                        newRow.innerHTML = `
                            <select name="items[${rowCount}][material_id]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" required>
                                <option value="">Select Material...</option>
                                @foreach($materials ?? [] as $m)
                                    <option value="{{ $m->material_id }}">{{ $m->material_name }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="items[${rowCount}][qty]" min="0.01" step="0.01" placeholder="Qty" class="w-24 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" required>
                            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 p-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        `;
                        container.appendChild(newRow);
                        rowCount++;
                    }
                </script>
            @endif
        </div>
    </div>

    <!-- Sidebar Details (Right) -->
    <div class="space-y-6">
        <!-- Request Details -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Job Details</h3>
            
            <div class="space-y-4">
                <div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Service Category</div>
                    <div class="text-sm font-bold text-gray-900">{{ $project->request->category->category_name ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Priority Level</div>
                    <div>
                        @php
                            $priority = ucfirst(strtolower($project->request->priority ?? 'Low'));
                            $prioClass = match($priority) {
                                'High' => 'bg-red-50 text-red-600 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800',
                                'Medium' => 'bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                default => 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold border {{ $prioClass }}">
                            {{ $priority }} Priority
                        </span>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Campus</div>
                    <div class="text-sm font-bold text-gray-900">{{ $project->request->campus ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Office / Location</div>
                    <div class="text-sm font-bold text-gray-900">{{ $project->request->location ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Client Information -->
        <div class="bg-[#fefce8] border border-[#d4d0a8] rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-[#1a3c8f] uppercase tracking-wider mb-4 border-b border-[#d4d0a8]/50 pb-2">Client Contact</h3>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-white border border-[#1a3c8f] rounded-full flex items-center justify-center text-[#1a3c8f] font-bold">
                    {{ strtoupper(substr($project->request->client->user->first_name ?? 'C', 0, 1)) }}
                </div>
                <div>
                    <div class="text-sm font-bold text-[#1a3c8f]">{{ $project->request->client->user->first_name ?? 'Client' }} {{ $project->request->client->user->last_name ?? '' }}</div>
                    <div class="text-xs text-[#1a3c8f] opacity-80">{{ $project->request->client->user->email_account ?? 'No email' }}</div>
                </div>
            </div>

            @php
                $clientPhone = $project->request->client->user->contact_number 
                    ?? $project->request->client->contact_number 
                    ?? 'Not Provided';
            @endphp
            <div class="pt-3 border-t border-[#d4d0a8]/50">
                <div class="text-[11px] font-bold text-[#1a3c8f] uppercase tracking-wider mb-1">Phone / Contact Number</div>
                <div class="text-sm font-extrabold text-[#1a3c8f] flex items-center gap-2 bg-white p-2.5 rounded-lg border border-[#d4d0a8]">
                    <span>📞</span>
                    <a href="tel:{{ $clientPhone }}" class="hover:underline">
                        {{ $clientPhone }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
