@extends('layouts.worker')
@section('page-title', 'Job Order Details')

@section('content')

@php
    $req = $project->request;
    $reqId = $project->request_id;
    $catName = strtolower($req->category->category_name ?? '');
    $prefix = match(true) {
        str_contains($catName, 'landscaping') => 'LS',
        str_contains($catName, 'janitorial') => 'JS',
        str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
        str_contains($catName, 'plumbing') => 'PLS',
        str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
        str_contains($catName, 'painting') || str_contains($catName, 'paint') => 'PAINT',
        str_contains($catName, 'manpower') || str_contains($catName, 'event') => 'MAN',
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
    
    @if(in_array($project->current_status, ['Completed', 'Pending Verification']) || !empty($project->recommendation))
    <div>
        @if(!empty($project->recommendation))
            <button disabled 
                    type="button" 
                    class="bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-zinc-700 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold cursor-not-allowed flex items-center gap-2 shadow-xs select-none opacity-80"
                    title="Preventive Maintenance Report has already been submitted.">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Maintenance Report Submitted</span>
            </button>
        @else
            <a href="{{ route('worker.maintenance.create', $project->project_id) }}" 
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Submit Maintenance Report</span>
            </a>
        @endif
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
            
            <div class="prose prose-sm text-gray-600 dark:text-gray-300 max-w-none whitespace-pre-line">
                {{ $req->display_description ?? 'No description provided by the client.' }}
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


        @php
            $beforeHistory = $project->histories->where('current_status', 'In Progress')->whereNotNull('proof_attachment')->last();
            $afterHistory = $project->histories->whereIn('current_status', ['Pending Verification', 'Completed'])->whereNotNull('proof_attachment')->last();
            $hasProofPhotos = ($beforeHistory && $beforeHistory->proof_attachment) || ($afterHistory && $afterHistory->proof_attachment);
        @endphp

        @if($hasProofPhotos)
            <!-- Photo Evidence & Proof of Work Card (Visible to Worker at all stages) -->
            <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-sm p-6 space-y-4"
                 x-data="{ lightboxOpen: false, lightboxImg: '', lightboxTitle: '' }">
                
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-zinc-800 pb-3 flex-wrap gap-2">
                    <div>
                        <h2 class="text-base font-bold text-[#0033a0] dark:text-blue-400">
                            Submitted Photo Proofs
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Photos captured and submitted as evidence for this job order.
                        </p>
                    </div>
                    <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-300 border border-blue-100 dark:border-blue-900 rounded-lg text-xs font-bold">
                        Proof of Work
                    </span>
                </div>

                <!-- 2-Column Photo Grid: Before Photo & After Photo -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Before Photo Card -->
                    <div class="bg-gray-50/70 dark:bg-zinc-800/40 p-4 rounded-xl border border-gray-200 dark:border-zinc-700 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3 flex-wrap">
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
                                    <img src="{{ Storage::url($beforeHistory->proof_attachment) }}" alt="Before Work" class="w-full max-h-56 object-contain rounded-lg group-hover:scale-[1.01] transition duration-200 mx-auto">
                                    <div class="absolute inset-0 bg-black/35 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1.5 rounded-xl">
                                        <span class="bg-black/70 px-3 py-1.5 rounded-lg backdrop-blur-xs shadow-sm flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Click to View Full</span>
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="min-h-[140px] bg-gray-100 dark:bg-zinc-800/40 rounded-xl flex items-center justify-center text-xs text-gray-400 font-medium border border-dashed border-gray-200 dark:border-zinc-700">
                                    No before photo recorded
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- After Photo Card -->
                    <div class="bg-gray-50/70 dark:bg-zinc-800/40 p-4 rounded-xl border border-gray-200 dark:border-zinc-700 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3 flex-wrap">
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
                                    <img src="{{ Storage::url($afterHistory->proof_attachment) }}" alt="After Work" class="w-full max-h-56 object-contain rounded-lg group-hover:scale-[1.01] transition duration-200 mx-auto">
                                    <div class="absolute inset-0 bg-black/35 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1.5 rounded-xl">
                                        <span class="bg-black/70 px-3 py-1.5 rounded-lg backdrop-blur-xs shadow-sm flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Click to View Full</span>
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="min-h-[140px] bg-gray-100 dark:bg-zinc-800/40 rounded-xl flex items-center justify-center text-xs text-gray-400 font-medium border border-dashed border-gray-200 dark:border-zinc-700">
                                    Pending completion photo upload
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Lightbox Preview Modal -->
                <div x-show="lightboxOpen" 
                     x-cloak 
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/80 backdrop-blur-xs" 
                     @click.outside="lightboxOpen = false" 
                     @keydown.escape.window="lightboxOpen = false">
                    <div class="relative max-w-4xl w-full max-h-[90vh] bg-zinc-900 rounded-2xl overflow-hidden shadow-2xl border border-zinc-700 flex flex-col">
                        <div class="w-full flex items-center justify-between py-3 px-5 bg-zinc-800 text-white border-b border-zinc-700 shrink-0">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-200" x-text="lightboxTitle">Photo Preview</span>
                            <div class="flex items-center gap-2">
                                <a :href="lightboxImg" target="_blank" download class="p-1.5 text-gray-300 hover:text-white hover:bg-zinc-700 rounded-lg transition" title="Open in New Tab / Download">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                <button type="button" @click="lightboxOpen = false" class="p-1.5 text-gray-400 hover:text-white hover:bg-zinc-700 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="w-full p-4 flex items-center justify-center overflow-auto max-h-[78vh] bg-black/60">
                            <img :src="lightboxImg" alt="Proof Preview" class="max-h-[72vh] w-auto max-w-full object-contain rounded-lg shadow-lg">
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(!in_array($project->current_status, ['Completed', 'Pending Verification']))

        <!-- 2nd Box: Update Task Progress & Attach Proofs -->
        @php
            $defaultNextStatus = ($project->current_status === 'In Progress') ? 'Completed' : 'In Progress';
        @endphp
        <div class="bg-[#f0f6ff] dark:bg-blue-950/20 border border-[#1a3c8f] dark:border-blue-700 rounded-xl shadow-xs p-7"
             x-data="{
                 currentStatusVal: '{{ $defaultNextStatus }}',
                 completionType: 'Full Repair',
                 saving: false,
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
                         const file = new File([blob], 'proof_' + (this.currentStatusVal === 'In Progress' ? 'before_' : 'after_') + Date.now() + '.jpg', { type: 'image/jpeg' });
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
            <h3 class="text-[#1a3c8f] dark:text-blue-400 font-bold text-lg mb-4">Update Task Progress</h3>
            
            <form action="{{ route('worker.task-progress.update', $project->project_id) }}" method="POST" enctype="multipart/form-data" class="space-y-4" @submit="saving = true">
                @csrf
                @method('PUT')
                <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-[#1a3c8f] dark:text-blue-300 mb-2">Mark Current Status As:</label>
                        <select name="status" x-model="currentStatusVal" class="w-full px-4 py-3 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" required>
                            <option value="In Progress">In Progress (Working on it — Requires Before Photo)</option>
                            <option value="Completed">Completed (Ready for Verification — Requires Photo)</option>
                        </select>
                    </div>
                    <button type="submit" :disabled="saving" class="bg-[#1a3c8f] hover:bg-[#152e6e] text-white px-8 py-3 rounded-lg text-sm font-semibold transition shadow-sm shrink-0 inline-flex items-center justify-center gap-2 disabled:opacity-60">
                        <svg x-show="saving" x-cloak class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="saving ? 'Saving...' : 'Save Update'">Save Update</span>
                    </button>
                </div>

                <!-- When Completed: Option to select Full Repair vs Inspection & Assessment Only -->
                <div x-show="currentStatusVal === 'Completed'" x-cloak class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-900/40 space-y-3">
                    <label class="block text-sm font-bold text-[#1a3c8f] dark:text-blue-300">
                        Nature of Work Completed:
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition"
                               :class="completionType === 'Full Repair' ? 'border-[#1a3c8f] bg-blue-50/80 dark:bg-blue-950/40' : 'border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900'">
                            <input type="radio" name="completion_type" value="Full Repair" x-model="completionType" class="mt-1 text-[#1a3c8f] focus:ring-[#1a3c8f]">
                            <div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">Direct Repair / Maintenance Done</div>
                                <div class="text-[11px] text-gray-500 mt-0.5">Physical repair, installation, or maintenance work was executed on site.</div>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition"
                               :class="completionType === 'Inspection Only' ? 'border-[#1a3c8f] bg-blue-50/80 dark:bg-blue-950/40' : 'border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900'">
                            <input type="radio" name="completion_type" value="Inspection Only" x-model="completionType" class="mt-1 text-[#1a3c8f] focus:ring-[#1a3c8f]">
                            <div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">Inspection &amp; Assessment Only</div>
                                <div class="text-[11px] text-gray-500 mt-0.5">Site visited and inspected; findings evaluated (e.g. no repair needed or referred to contractor).</div>
                            </div>
                        </label>
                    </div>

                    <!-- Inspection / Completion Findings Note -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            <span x-show="completionType === 'Inspection Only'">Inspection Findings &amp; Recommendation (Optional):</span>
                            <span x-show="completionType === 'Full Repair'">Work Notes / Remarks (Optional):</span>
                        </label>
                        <textarea name="recommendation" 
                                  rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]"
                                  :placeholder="completionType === 'Inspection Only' ? 'e.g. Inspected site; outlet circuit breaker tripped and reset, or referred for PDMO major overhaul.' : 'e.g. Replaced 4 fluorescent light bulbs and tested electrical lines.'"></textarea>
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-t border-blue-100 dark:border-blue-900/40">
                    <label class="block text-sm font-semibold text-[#1a3c8f] dark:text-blue-300 mb-1">
                        <span x-show="currentStatusVal === 'In Progress'">Attach Before-Work Photo <span class="text-red-500 font-bold">(Required)</span>:</span>
                        <span x-show="currentStatusVal === 'Completed' && completionType === 'Full Repair'">Attach After-Work Photo / Proof of Completion <span class="text-red-500 font-bold">(Required)</span>:</span>
                        <span x-show="currentStatusVal === 'Completed' && completionType === 'Inspection Only'">Attach Site Inspection Photo / Proof <span class="text-red-500 font-bold">(Required)</span>:</span>
                    </label>

                    <p class="text-xs text-gray-500 mb-3" x-text="currentStatusVal === 'In Progress' ? 'Upload a photo showing the initial condition before starting work.' : (completionType === 'Inspection Only' ? 'Upload a photo taken during the site inspection.' : 'Upload a photo showing the finished repairs or cleaned site.')"></p>
                    
                    <!-- Hidden File Input -->
                    <input type="file" 
                           name="proof" 
                           x-ref="workerFileInput" 
                           accept="image/*,application/pdf" 
                           @change="handleFile($event.target.files[0])" 
                           class="hidden"
                           required>

                    <!-- Option Buttons Card -->
                    <div x-show="!proofFile" class="border-2 border-dashed border-[#1a3c8f]/30 rounded-xl p-5 text-center bg-white dark:bg-zinc-900 space-y-3">
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                            <!-- Choose File Button -->
                            <button type="button" 
                                    @click="$refs.workerFileInput.click()" 
                                    class="w-full sm:w-auto px-5 py-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 transition shadow-xs inline-flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-[#0038A8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <span>Choose Photo</span>
                            </button>

                            <span class="text-xs text-gray-400 font-semibold">or</span>

                            <!-- Take a Pic Button -->
                            <button type="button" 
                                    @click="openCamera()" 
                                    class="w-full sm:w-auto px-5 py-2.5 bg-[#0038A8] hover:bg-[#002480] text-white rounded-lg text-xs font-bold transition shadow-xs inline-flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Take a Photo</span>
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-500">Supports JPG, PNG, WEBP images.</p>
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
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3" x-text="currentStatusVal === 'In Progress' ? 'Take Before-Work Photo' : 'Take After-Work / Completion Photo'"></h3>
                            
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
        </div>
        @endif

        <!-- Submitted Preventive Maintenance Report Card -->
        @if(!empty($project->recommendation))
        <div class="bg-white dark:bg-[#1c1c1e] border border-emerald-300 dark:border-emerald-700/60 rounded-2xl shadow-xs p-6">
            <div class="flex items-center justify-between mb-3 border-b border-emerald-100 dark:border-emerald-950/60 pb-3">
                <h3 class="text-xs sm:text-sm font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Submitted Preventive Maintenance Report</span>
                </h3>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                    Report Filed
                </span>
            </div>
            <div class="text-xs text-slate-800 dark:text-gray-200 whitespace-pre-line leading-relaxed font-mono bg-gray-50 dark:bg-zinc-900/80 p-4 rounded-xl border border-gray-200 dark:border-zinc-800">
                {{ $project->recommendation }}
            </div>
        </div>
        @endif

        <!-- 3rd Box: Material Requisition (Optional) -->
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl shadow-xs p-7">

            <h3 class="text-gray-900 dark:text-white font-bold text-lg mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Request Materials / Bill of Materials (BOM)
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-5">Specify needed tools, parts, or supplies. Pricing is verified and approved by Admin.</p>
            
            @if($project->billOfMaterials->count() > 0)
                <div class="mb-6">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Previously Requested Materials</h4>
                    <div class="space-y-2.5">
                        @foreach($project->billOfMaterials as $bom)
                            @php
                                $unit = $bom->material->unit_of_measurement ?? 'pcs';
                                $isApproved = !is_null($bom->date_approved);
                            @endphp
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-gray-50 dark:bg-zinc-800/60 p-3.5 rounded-xl border border-gray-200/80 dark:border-zinc-700">
                                <div>
                                    <div class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <span>{{ $bom->material->material_name ?? 'Unknown Material' }}</span>
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-black bg-blue-100 text-[#0033a0] dark:bg-blue-950/60 dark:text-blue-300">
                                            {{ rtrim(rtrim(number_format($bom->qty, 2), '0'), '.') }} {{ $unit }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        Requested on {{ \Carbon\Carbon::parse($bom->created_at ?? $project->date_assigned)->format('M d, Y') }}
                                        @if($isApproved && $bom->total_cost > 0)
                                            • <span class="text-slate-700 dark:text-gray-300 font-semibold">Total: ₱{{ number_format($bom->total_cost, 2) }}</span> (₱{{ number_format($bom->material->unit_cost ?? 0, 2) }}/{{ $unit }})
                                        @endif
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    @if($isApproved)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 text-xs font-extrabold rounded-md uppercase">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Approved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 text-xs font-extrabold rounded-md uppercase">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pending Admin Pricing & Approval
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($project->current_status !== 'Completed' && $project->current_status !== 'Pending Verification')
                <div x-data="{
                    submittingBOM: false,
                    rows: [
                        { material_id: '', custom_name: '', unit: 'pcs', qty: 1 }
                    ],
                    catalog: {{ Js::from($materials->map(fn($m) => ['id' => $m->material_id, 'name' => $m->material_name, 'unit' => $m->unit_of_measurement ?? 'pcs'])) }},
                    isDiscrete(unit) {
                        if (!unit) return true;
                        const u = unit.toString().trim().toLowerCase();
                        const continuousUnits = ['meter', 'meters', 'm', 'length', 'lengths', 'ft', 'feet', 'foot', 'liter', 'liters', 'l', 'kg', 'kilo', 'kilos', 'kilogram', 'kilograms', 'gallon', 'gallons', 'gal', 'yard', 'yards', 'yd', 'inch', 'inches', 'cm', 'mm'];
                        return !continuousUnits.includes(u);
                    },
                    addRow() {
                        this.rows.push({ material_id: '', custom_name: '', unit: 'pcs', qty: 1 });
                    },
                    removeRow(index) {
                        if (this.rows.length > 1) {
                            this.rows.splice(index, 1);
                        }
                    },
                    onMaterialChange(row) {
                        if (row.material_id && row.material_id !== 'custom') {
                            const found = this.catalog.find(m => m.id == row.material_id);
                            if (found && found.unit) {
                                row.unit = found.unit;
                            }
                        } else if (row.material_id === 'custom') {
                            if (!row.unit) row.unit = 'pcs';
                        }
                    }
                }" class="pt-2">
                    
                    <form action="{{ route('worker.bom.store', $project->project_id) }}" method="POST" @submit="submittingBOM = true">
                        @csrf
                        
                        <div class="space-y-3 mb-4">
                            <template x-for="(row, idx) in rows" :key="idx">
                                <div class="p-3.5 bg-gray-50 dark:bg-zinc-800/60 rounded-xl border border-gray-200 dark:border-zinc-700 transition">
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                                        
                                        <!-- Material Selection -->
                                        <div :class="row.material_id === 'custom' ? 'sm:col-span-4' : 'sm:col-span-6'">
                                            <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1">
                                                Select Material
                                            </label>
                                            <select :name="'items[' + idx + '][material_id]'" 
                                                    x-model="row.material_id" 
                                                    @change="onMaterialChange(row)" 
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" 
                                                    required>
                                                <option value="">Select from catalog...</option>
                                                @foreach($materials ?? [] as $m)
                                                    <option value="{{ $m->material_id }}">{{ $m->material_name }} ({{ $m->unit_of_measurement ?? 'pcs' }})</option>
                                                @endforeach
                                                <option value="custom" class="font-bold text-[#0033a0]">+ Add New / Custom Material...</option>
                                            </select>
                                        </div>

                                        <!-- Custom Name Input if 'custom' selected -->
                                        <div class="sm:col-span-3" x-show="row.material_id === 'custom'">
                                            <label class="block text-[11px] font-bold text-[#0033a0] dark:text-blue-400 uppercase tracking-wider mb-1">
                                                New Material Name
                                            </label>
                                            <input type="text" 
                                                   :name="'items[' + idx + '][custom_material_name]'" 
                                                   x-model="row.custom_name" 
                                                   :required="row.material_id === 'custom'" 
                                                   placeholder="e.g. 10m Extension Wire" 
                                                   class="w-full px-3 py-2 border border-[#0033a0] dark:border-blue-500 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]">
                                        </div>

                                        <!-- Quantity -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1">
                                                Quantity
                                            </label>
                                            <input type="number" 
                                                   :name="'items[' + idx + '][qty]'" 
                                                   x-model.number="row.qty" 
                                                   :min="isDiscrete(row.unit) ? '1' : '0.01'" 
                                                   :step="isDiscrete(row.unit) ? '1' : '0.01'" 
                                                   placeholder="1" 
                                                   class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]" 
                                                   required>
                                        </div>

                                        <!-- Unit of Measurement (Non-editable for catalog items, select dropdown for custom items) -->
                                        <div :class="row.material_id === 'custom' ? 'sm:col-span-2' : 'sm:col-span-3'">
                                            <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider mb-1">
                                                Unit / Measurement
                                            </label>
                                            
                                            <!-- Non-editable display for catalog items -->
                                            <div x-show="row.material_id !== 'custom'" class="w-full px-3 py-2 border border-gray-200 dark:border-zinc-700 bg-gray-100 dark:bg-zinc-800/80 rounded-lg text-xs font-bold text-slate-700 dark:text-gray-300 text-center flex items-center justify-center min-h-[34px] select-none">
                                                <span x-text="row.unit || 'pcs'"></span>
                                            </div>
                                            <input type="hidden" x-show="row.material_id !== 'custom'" :name="'items[' + idx + '][unit_of_measurement]'" :value="row.unit">

                                            <!-- Predefined dropdown for custom material items -->
                                            <select x-show="row.material_id === 'custom'" 
                                                    :name="'items[' + idx + '][unit_of_measurement]'" 
                                                    x-model="row.unit" 
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs bg-white dark:bg-zinc-800 text-slate-900 dark:text-white focus:ring-[#1a3c8f] focus:border-[#1a3c8f]">
                                                <option value="pcs">pcs (pieces)</option>
                                                <option value="meters">meters</option>
                                                <option value="lengths">lengths</option>
                                                <option value="rolls">rolls</option>
                                                <option value="boxes">boxes</option>
                                                <option value="bags">bags</option>
                                                <option value="liters">liters</option>
                                                <option value="sheets">sheets</option>
                                                <option value="sets">sets</option>
                                                <option value="units">units</option>
                                                <option value="kg">kg (kilograms)</option>
                                                <option value="gallons">gallons</option>
                                                <option value="pairs">pairs</option>
                                                <option value="tubes">tubes</option>
                                                <option value="packs">packs</option>
                                                <option value="feet">feet</option>
                                                <option value="can">can / cans</option>
                                            </select>
                                        </div>

                                        <!-- Remove Row Button -->
                                        <div class="sm:col-span-1 flex justify-end">
                                            <button type="button" 
                                                    @click="removeRow(idx)" 
                                                    x-show="rows.length > 1" 
                                                    class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition" 
                                                    title="Remove item">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                            <button type="button" 
                                    @click="addRow()" 
                                    class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold hover:underline inline-flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Another Material Item</span>
                            </button>

                            <button type="submit" 
                                    :disabled="submittingBOM" 
                                    class="w-full sm:w-auto bg-[#0033a0] hover:bg-[#002480] text-white px-6 py-2.5 rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center justify-center gap-2 disabled:opacity-60 cursor-pointer">
                                <svg x-show="submittingBOM" x-cloak class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-text="submittingBOM ? 'Submitting...' : 'Submit Material Request'">Submit Material Request</span>
                            </button>
                        </div>
                    </form>
                </div>
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
