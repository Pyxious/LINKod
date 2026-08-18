@extends('layouts.worker')
@section('page-title', 'Job Order Details')

@section('content')

@php
    $req = $project->request;
    $reqId = $project->request_id;
    $catName = strtolower($req->category->category_name ?? '');
    $prefix = match(true) {
        str_contains($catName, 'landscaping') => 'LS',
        str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
        str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
        str_contains($catName, 'plumbing') => 'PS',
        str_contains($catName, 'painting') => 'PAINT',
        default => 'REQ'
    };
    $reqCode = $reqId ? ($prefix . '-' . str_pad($reqId, 3, '0', STR_PAD_LEFT)) : ('REQ-'.str_pad($project->project_id, 3, '0', STR_PAD_LEFT));
    
    $clientUser = $req?->client?->user;
    $clientName = $clientUser ? ($clientUser->first_name . ' ' . $clientUser->last_name) : 'Client Requestor';
    $clientEmail = $clientUser?->email_account ?? 'No email provided';
    $clientPhone = $clientUser?->contact_number ?? $req?->client?->contact_number ?? 'Not Provided';
    $initials = strtoupper(substr($clientUser?->first_name ?? 'K', 0, 1));
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
                @if($project->current_status === 'Pending') bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/40 dark:text-red-300
                @elseif($project->current_status === 'In Progress') bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300
                @elseif($project->current_status === 'Pending Verification') bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300
                @else bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 @endif">
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

<!-- Main Top Section: 1st, 2nd, 3rd boxes on Left, Job Details + Client Contact on Right -->
<div class="flex flex-col md:flex-row gap-6 items-start w-full">

    <!-- 1st, 2nd, 3rd Boxes (Left Side) -->
    <div class="flex-1 min-w-0 w-full space-y-6">
        <!-- 1st Box: Issue Description & Supporting Documents -->
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl shadow-xs p-7">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ $req->title ?? 'Untitled Job Order' }}</h2>
            
            <div class="prose prose-sm text-gray-600 dark:text-gray-300 max-w-none">
                {{ $req->description ?? 'No description provided by the client.' }}
            </div>
            
            @if($req->attachment)
                <div class="mt-6 border-t border-gray-100 dark:border-zinc-800 pt-6">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-3">Supporting Documents</h3>
                    <a href="{{ Storage::url($req->attachment) }}" target="_blank" class="inline-flex items-center gap-3 p-3 border border-gray-200 dark:border-zinc-700 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition w-full max-w-sm">
                        <div class="bg-blue-50 dark:bg-blue-950 text-[#0038A8] dark:text-blue-400 w-10 h-10 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-200 truncate">Attachment</div>
                            <div class="text-xs text-gray-500">Click to view file</div>
                        </div>
                    </a>
                </div>
            @endif
        </div>

        @if(!in_array($project->current_status, ['Completed', 'Pending Verification']))
        <!-- 2nd Box: Update Task Status -->
        <div class="bg-[#f0f6ff] dark:bg-blue-950/20 border border-[#1a3c8f] dark:border-blue-700 rounded-xl shadow-xs p-7">
            <h3 class="text-[#1a3c8f] dark:text-blue-400 font-bold text-lg mb-4">Update Task Progress</h3>
            <form action="{{ route('worker.task-progress.update', $project->project_id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-[#1a3c8f] dark:text-blue-300 mb-2">Mark Current Status As:</label>
                        <select name="status" id="status-select" class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" required onchange="toggleProofInput()">
                            <option value="In Progress" {{ $project->current_status === 'In Progress' ? 'selected' : '' }}>In Progress (Working on it)</option>
                            <option value="Completed" {{ $project->current_status === 'Completed' ? 'selected' : '' }}>Completed (Job Finished)</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-[#1a3c8f] hover:bg-[#152e6e] text-white px-8 py-3 rounded-lg text-sm font-semibold transition shadow-sm shrink-0">
                        Save Update
                    </button>
                </div>
                
                <div id="proof-container" style="display: {{ $project->current_status === 'Completed' ? 'block' : 'none' }};" x-data="{
                    proofFile: '',
                    cameraActive: false,
                    cameraStream: null,
                    proofPreviewUrl: '',

                    handleFile(file) {
                        if (!file) return;
                        this.proofFile = file.name;
                        if (file.type.startsWith('image/')) {
                            this.proofPreviewUrl = URL.createObjectURL(file);
                        } else {
                            this.proofPreviewUrl = '';
                        }
                    },

                    openCamera() {
                        this.cameraActive = true;
                        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                            .then(stream => {
                                this.cameraStream = stream;
                                if (this.$refs.workerVideo) {
                                    this.$refs.workerVideo.srcObject = stream;
                                }
                            })
                            .catch(err => {
                                alert('Camera access denied: ' + err.message);
                                this.cameraActive = false;
                            });
                    },

                    capturePhoto() {
                        const video = this.$refs.workerVideo;
                        if (!video) return;
                        const canvas = document.createElement('canvas');
                        canvas.width = video.videoWidth || 640;
                        canvas.height = video.videoHeight || 480;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                        canvas.toBlob((blob) => {
                            const file = new File([blob], 'proof_completion_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                            this.handleFile(file);
                            const container = new DataTransfer();
                            container.items.add(file);
                            if (this.$refs.workerFileInput) {
                                this.$refs.workerFileInput.files = container.files;
                            }
                            this.closeCamera();
                        }, 'image/jpeg', 0.85);
                    },

                    closeCamera() {
                        if (this.cameraStream) {
                            this.cameraStream.getTracks().forEach(track => track.stop());
                            this.cameraStream = null;
                        }
                        this.cameraActive = false;
                    },

                    clearProof() {
                        this.proofFile = '';
                        this.proofPreviewUrl = '';
                        if (this.$refs.workerFileInput) {
                            this.$refs.workerFileInput.value = '';
                        }
                    }
                }">
                    <label class="block text-sm font-semibold text-[#1a3c8f] dark:text-blue-300 mb-2 mt-4">Attach Proof of Completion (Required):</label>
                    
                    <!-- Hidden File Input -->
                    <input type="file" 
                           name="proof" 
                           x-ref="workerFileInput" 
                           accept="image/*,application/pdf" 
                           @change="handleFile($event.target.files[0])" 
                           class="hidden">

                    <!-- Option Buttons Card -->
                    <div x-show="!proofFile" class="border-2 border-dashed border-[#1a3c8f]/30 rounded-xl p-5 text-center bg-white dark:bg-zinc-900 space-y-3">
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                            <!-- Choose File Button -->
                            <button type="button" 
                                    @click="$refs.workerFileInput.click()" 
                                    class="w-full sm:w-auto px-5 py-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 transition shadow-xs inline-flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-[#0038A8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <span>Choose File</span>
                            </button>

                            <span class="text-xs text-gray-400 font-semibold">or</span>

                            <!-- Take a Pic Button -->
                            <button type="button" 
                                    @click="openCamera()" 
                                    class="w-full sm:w-auto px-5 py-2.5 bg-[#0038A8] hover:bg-[#002480] text-white rounded-lg text-xs font-bold transition shadow-xs inline-flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Take a Pic</span>
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-500">Upload a photo of finished work or maintenance document.</p>
                    </div>

                    <!-- Selected Proof Preview -->
                    <div x-show="proofFile" x-cloak class="border border-blue-200 bg-white dark:bg-zinc-900 rounded-xl p-3.5 flex items-center justify-between gap-3 shadow-xs">
                        <div class="flex items-center gap-3 min-w-0">
                            <template x-if="proofPreviewUrl">
                                <img :src="proofPreviewUrl" alt="Proof" class="w-12 h-12 object-cover rounded-lg border border-gray-200 shrink-0">
                            </template>
                            <template x-if="!proofPreviewUrl">
                                <div class="w-12 h-12 bg-blue-50 text-[#0038A8] rounded-lg flex items-center justify-center font-bold text-xs shrink-0">DOC</div>
                            </template>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="proofFile"></p>
                                <p class="text-[10px] text-emerald-600 font-semibold mt-0.5">Ready for upload</p>
                            </div>
                        </div>
                        <button type="button" @click="clearProof()" class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition border border-red-200 shrink-0">
                            ✕ Remove
                        </button>
                    </div>

                    <!-- Worker Camera Modal -->
                    <div x-show="cameraActive" x-cloak class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 max-w-lg w-full shadow-2xl relative flex flex-col items-center">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Take Proof Photo of Completed Work</h3>
                            
                            <div class="w-full bg-black rounded-xl overflow-hidden mb-4 relative aspect-video flex items-center justify-center">
                                <video x-ref="workerVideo" autoplay playsinline class="w-full h-full object-cover"></video>
                            </div>

                            <div class="flex items-center gap-3 w-full justify-end">
                                <button type="button" @click="closeCamera()" class="px-4 py-2 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold transition">
                                    Cancel
                                </button>
                                <button type="button" @click="capturePhoto()" class="px-5 py-2 bg-[#0038A8] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 shadow-md">
                                    <span>Snap Photo</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <p class="text-xs text-[#1a3c8f] dark:text-blue-300 opacity-80 mt-4">
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

        <!-- 3rd Box: Material Requisition (Optional) -->
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl shadow-xs p-7">
            <h3 class="text-gray-900 dark:text-white font-bold text-lg mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Request Materials (Optional)
            </h3>
            
            @if($project->billOfMaterials->count() > 0)
                <div class="mb-6">
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Previously Requested Materials</h4>
                    <ul class="space-y-2">
                        @foreach($project->billOfMaterials as $bom)
                            <li class="flex justify-between items-center bg-gray-50 dark:bg-zinc-800/60 p-3 rounded-lg border border-gray-100 dark:border-zinc-700">
                                <div>
                                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $bom->material->material_name ?? 'Unknown' }} (x{{ $bom->qty }})</div>
                                    <div class="text-xs text-gray-500">Requested on {{ \Carbon\Carbon::parse($bom->created_at ?? $project->date_assigned)->format('M d, Y') }}</div>
                                </div>
                                <div>
                                    @if($bom->date_approved)
                                        <span class="px-2 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 text-xs font-bold rounded-md uppercase">Approved</span>
                                    @else
                                        <span class="px-2 py-1 bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 text-xs font-bold rounded-md uppercase">Pending</span>
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
                            <select name="items[0][material_id]" class="flex-1 px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" required>
                                <option value="">Select Material...</option>
                                @foreach($materials ?? [] as $m)
                                    <option value="{{ $m->material_id }}">{{ $m->material_name }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="items[0][qty]" min="0.01" step="0.01" placeholder="Qty" class="w-24 px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" required>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <button type="button" onclick="addMaterialRow()" class="text-[#1a3c8f] dark:text-blue-400 text-sm font-semibold hover:underline flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Another Item
                        </button>
                        <button type="submit" class="bg-gray-800 hover:bg-black text-white px-6 py-2 rounded-lg text-sm font-semibold transition">
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
                            <select name="items[${rowCount}][material_id]" class="flex-1 px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" required>
                                <option value="">Select Material...</option>
                                @foreach($materials ?? [] as $m)
                                    <option value="{{ $m->material_id }}">{{ $m->material_name }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="items[${rowCount}][qty]" min="0.01" step="0.01" placeholder="Qty" class="w-24 px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" required>
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

    <!-- The 2 Boxes on the Right Side (Job Details & Client Contact) -->
    <div class="w-full md:w-80 lg:w-96 shrink-0 space-y-6">
        <!-- Job Details Card -->
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl shadow-xs p-6">
            <h3 class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-4 border-b border-gray-100 dark:border-zinc-800 pb-2">JOB DETAILS</h3>
            <div class="space-y-4">
                <div>
                    <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">SERVICE CATEGORY</div>
                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $req?->category?->category_name ?? 'General Maintenance' }}</div>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">PRIORITY LEVEL</div>
                    <div>
                        @php
                            $prio2 = ucfirst(strtolower($req->priority ?? 'Low'));
                            $prioBadge2 = match(strtolower($req->priority ?? 'low')) {
                                'high' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-300',
                                'medium' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300',
                                default => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300'
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $prioBadge2 }}">{{ $prio2 }} Priority</span>
                    </div>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">CAMPUS</div>
                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $req->campus ?? 'BU Main' }}</div>
                </div>
                <div>
                    <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">OFFICE / LOCATION</div>
                    <div class="text-sm font-bold text-gray-900 dark:text-white leading-relaxed">{{ $req->location ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Client Contact Card -->
        <div class="bg-[#fefce8] dark:bg-yellow-950/20 border border-[#d4d0a8] dark:border-yellow-900/50 rounded-xl shadow-xs p-6">
            <h3 class="text-xs font-bold text-[#1a3c8f] dark:text-yellow-400 uppercase tracking-wider mb-4 border-b border-[#d4d0a8]/60 dark:border-yellow-900/40 pb-2">CLIENT CONTACT</h3>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-[#0033a0] text-white rounded-full flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                    {{ $initials }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-bold text-[#1a3c8f] dark:text-yellow-200 truncate">{{ $clientName }}</div>
                    <div class="text-xs text-[#1a3c8f]/80 dark:text-yellow-400/80 truncate">{{ $clientEmail }}</div>
                </div>
            </div>
            <div class="pt-3 border-t border-[#d4d0a8]/60 dark:border-yellow-900/40">
                <div class="text-[11px] font-bold text-[#1a3c8f] dark:text-yellow-400 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                    <span>📞</span>
                    <span>PHONE / CONTACT NUMBER</span>
                </div>
                <div class="bg-white dark:bg-zinc-900 p-3 rounded-lg border border-[#d4d0a8] dark:border-zinc-700 flex items-center justify-between gap-2 shadow-2xs">
                    <div class="font-extrabold text-sm text-[#0033a0] dark:text-blue-400">{{ $clientPhone }}</div>
                    @if($clientPhone !== 'Not Provided')
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $clientPhone) }}" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-xs inline-flex items-center gap-1 shrink-0">Call</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Full Width Discussion & Messages Channel -->
@if($req)
    <div class="w-full mt-6">
        @include('partials.request-messages', ['serviceRequest' => $req])
    </div>
@endif

@endsection
