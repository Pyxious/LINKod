@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full flex flex-col font-sans min-h-[calc(100vh-64px)] bg-slate-50/50 dark:bg-[#111111]">
    
    <!-- Top Hero Section (Unified LinkOD Client Banner) -->
    <div class="bg-[#fffde7] dark:bg-[#18181b] py-8 px-6 md:px-12 border-b border-gray-200/80 dark:border-zinc-800">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <a href="{{ route('client.requests.index') }}" class="text-[#0033a0] dark:text-blue-400 hover:underline text-xs font-bold uppercase tracking-wider flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back to Requests
                    </a>
                </div>
                <h1 class="text-[#0033a0] dark:text-blue-400 text-2xl sm:text-3xl font-black tracking-tight">Create Service Request</h1>
                <p class="text-[#0033a0]/80 dark:text-gray-400 text-xs sm:text-sm font-medium mt-1">Submit an official job requisition to General Services Office (GSO).</p>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-[#0033a0]/10 text-[#0033a0] dark:bg-blue-900/30 dark:text-blue-300 border border-[#0033a0]/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Requisition Form
                </span>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <main class="max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">

        @if(isset($unratedCompletedRequest) && $unratedCompletedRequest)
            <div class="w-full mb-8 p-6 bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-700/80 rounded-2xl shadow-sm text-amber-900 dark:text-amber-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/60 border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-amber-900 dark:text-amber-200 mb-1">New Service Request Locked</h3>
                        <p class="text-xs md:text-sm text-amber-800 dark:text-amber-300/90 leading-relaxed">
                            You have a completed service request (<strong>#{{ $unratedCompletedRequest->request_id }} — {{ $unratedCompletedRequest->title }}</strong>) that has not been evaluated yet. 
                            Please submit your rating for your last completed request to unlock creating new requests.
                        </p>
                    </div>
                </div>
                <a href="{{ route('client.evaluations.create', $unratedCompletedRequest->request_id) }}" class="shrink-0 px-5 py-2.5 bg-[#0038A8] hover:bg-[#002B82] text-white rounded-xl text-xs md:text-sm font-bold shadow-md transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-yellow-300 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    Rate Request Now
                </a>
            </div>
        @endif

        <form x-data="{
            submitting: false,
            viewPreviewModal: false,
            selectedCategoryId: '{{ $preselectedCatId ?? "" }}',
            selectedCategoryName: '',
            selectedCampus: '',
            selectedConcern: '',
            customConcern: '',
            selectedLocation: '',
            customLocation: '',

            // Manpower & Event Specific Fields
            activityTitle: '',
            eventDate: '',
            prepDateFrom: '',
            prepDateTo: '',
            prepDetails: '',
            prepRegular: true,
            prepOvertime: false,
            prepTimePreset: 'regular',
            prepRegularTime: '8:00 - 12:00 / 1:00 - 5:00',
            prepOvertimeTime: '',

            assistanceDateFrom: '',
            assistanceDateTo: '',
            assistanceDetails: '',
            assistanceRegular: true,
            assistanceOvertime: false,
            assistanceTimePreset: 'regular',
            assistanceRegularTime: '8:00 - 12:00 / 1:00 - 5:00',
            assistanceOvertimeTime: '',

            clearingDateFrom: '',
            clearingDateTo: '',
            clearingDetails: '',
            clearingRegular: true,
            clearingOvertime: false,
            clearingTimePreset: 'regular',
            clearingRegularTime: '8:00 - 12:00 / 1:00 - 5:00',
            clearingOvertimeTime: '',

            additionalNotes: '',

            // Time preset options
            timePresets: [
                { value: 'morning',   label: 'Morning (8:00 AM – 12:00 PM)',        time: '8:00 - 12:00' },
                { value: 'afternoon', label: 'Afternoon (1:00 PM – 5:00 PM)',        time: '1:00 - 5:00' },
                { value: 'regular',   label: 'Regular Time (8:00 AM – 5:00 PM)',     time: '8:00 - 12:00 / 1:00 - 5:00' },
                { value: 'fullday',   label: 'Full Day (8:00 AM – 5:00 PM)',         time: '8:00 - 5:00' },
                { value: 'custom',    label: 'Custom Time…',                         time: '' },
            ],

            applyTimePreset(section) {
                const map = { prep: 'prepTimePreset', assistance: 'assistanceTimePreset', clearing: 'clearingTimePreset' };
                const timeMap = { prep: 'prepRegularTime', assistance: 'assistanceRegularTime', clearing: 'clearingRegularTime' };
                const preset = this.timePresets.find(p => p.value === this[map[section]]);
                if (preset && preset.value !== 'custom') {
                    this[timeMap[section]] = preset.time;
                }
            },

            formatDateRange(from, to) {
                if (!from && !to) return '';
                if (!to || from === to) return from;
                return from + ' to ' + to;
            },

            get prepDate() { return this.formatDateRange(this.prepDateFrom, this.prepDateTo); },
            get assistanceDate() { return this.formatDateRange(this.assistanceDateFrom, this.assistanceDateTo); },
            get clearingDate() { return this.formatDateRange(this.clearingDateFrom, this.clearingDateTo); },

            init() {
                if (this.selectedCategoryId) {
                    this.$nextTick(() => {
                        const selectEl = this.$refs.categorySelect;
                        if (selectEl) {
                            selectEl.value = this.selectedCategoryId;
                            const selectedOpt = selectEl.options[selectEl.selectedIndex];
                            this.selectedCategoryName = selectedOpt ? selectedOpt.text : '';
                        }
                    });
                }
            },

            // Attachment State
            fileName: '',
            fileSizeFormatted: '',
            filePreviewUrl: '',
            isImage: false,
            cameraActive: false,
            cameraStream: null,

            // Concerns grouped by category keyword
            concernsMap: {
                'Electrical': [
                    'Power Outlet Repair / Installation',
                    'Lighting Fixture Repair / Replacement',
                    'Circuit Breaker Tripping / Power Outage',
                    'Wiring Inspection & Electrical Safety',
                    'Ceiling Fan / Exhaust Fan Repair',
                    'Other Electrical Concern'
                ],
                'Carpentry': [
                    'Door Lock / Handle / Hinge Repair',
                    'Window Glass & Wooden Frame Repair',
                    'Table / Desk Fabrication or Repair',
                    'Chair / Bench Repair',
                    'Ceiling / Roof Leak Inspection & Repair',
                    'Cabinet / Drawer Repair',
                    'Other Carpentry Concern'
                ],
                'Plumbing': [
                    'Faucet / Pipe Leak Repair',
                    'Toilet / Urinal Clog Repair',
                    'Water Pressure Issue / Pump Concern',
                    'Drainage / Sewage Clog',
                    'Water Tank / Fixture Installation',
                    'Other Plumbing Concern'
                ],
                'Air Conditioning': [
                    'Aircon Cleaning & Preventive Maintenance',
                    'Aircon Cooling Failure / Freon Refill',
                    'Aircon Water Leakage Repair',
                    'Aircon Noise / Power Issue',
                    'Other Aircon Concern'
                ],
                'Aircon': [
                    'Aircon Cleaning & Preventive Maintenance',
                    'Aircon Cooling Failure / Freon Refill',
                    'Aircon Water Leakage Repair',
                    'Aircon Noise / Power Issue',
                    'Other Aircon Concern'
                ],
                'Landscaping': [
                    'Grass Cutting / Lawn Mowing',
                    'Tree Trimming & Branch Removal',
                    'Garden & Grounds Cleaning / Clearing',
                    'Planting & Campus Beautification Request',
                    'Weed Control & Soil Maintenance',
                    'Other Landscaping Concern'
                ],
                'Manpower': [
                    'Event & Activity Venue Setup',
                    'Heavy Equipment & Furniture Relocation',
                    'Hauling & Waste Disposal Assistance',
                    'Other Manpower Need'
                ],
                'Janitorial': [
                    'Deep Cleaning & Disinfection Service',
                    'Waste Management & Garbage Collection',
                    'Restroom Sanitation & Supplies Check',
                    'Other Janitorial Service'
                ]
            },

            // Locations / Offices grouped by Campus
            locationsMap: {
                'BU Main': [
                    'Administration Building (GSO, OSAS, Registrar, Cashier)',
                    'College of Education (BUCE)',
                    'College of Arts and Letters (BUCAL)',
                    'College of Science (BUCS)',
                    'College of Nursing (BUCN)',
                    'College of Medicine (BUCM)',
                    'Information & Communications Technology Office (ICTO)',
                    'University Student Center (USC)',
                    'University Main Library & Amphitheater',
                    'BU Gymnasium & Sports Complex',
                    'Other Location (BU Main)'
                ],
                'BU Daraga': [
                    'College of Social Sciences and Philosophy (CSSP)',
                    'College of Business, Economics and Management (CBEM)',
                    'Daraga Campus Administration Building',
                    'Daraga Campus Library',
                    'Student Activity Center & Canteen',
                    'Other Location (BU Daraga)'
                ],
                'BU East': [
                    'College of Engineering (BUCENG)',
                    'College of Industrial Technology (BUCIT)',
                    'East Campus Administration & Library',
                    'Mechanical & Electrical Shop Buildings',
                    'East Campus Student Center',
                    'Other Location (BU East)'
                ],
                'BU Polangui': [
                    'Polangui Campus Administration Building',
                    'Department of Information Technology',
                    'Engineering & Technology Building',
                    'Nursing & Health Sciences Building',
                    'Polangui Campus Library & Student Center',
                    'Other Location (BU Polangui)'
                ],
                'BU Tabaco': [
                    'Tabaco Campus Administration Building',
                    'Department of Fisheries & Aquaculture',
                    'Business & Teacher Education Building',
                    'Tabaco Campus Library',
                    'Other Location (BU Tabaco)'
                ],
                'BU Gubat': [
                    'Gubat Campus Administration Building',
                    'Academic & Multi-Purpose Building',
                    'Gubat Campus Library',
                    'Other Location (BU Gubat)'
                ],
                'BU Guinobatan': [
                    'College of Agriculture and Forestry (BUCAF) Admin',
                    'BUCAF Academic & Laboratory Buildings',
                    'Research, Extension & Demonstration Farm',
                    'Guinobatan Campus Library & Auditorium',
                    'Other Location (BU Guinobatan)'
                ]
            },

            get availableConcerns() {
                if (!this.selectedCategoryName) return [];
                const matchKey = Object.keys(this.concernsMap).find(k => this.selectedCategoryName.toLowerCase().includes(k.toLowerCase()));
                return matchKey ? this.concernsMap[matchKey] : [
                    'General Repair & Maintenance Request',
                    'Equipment Repair Request',
                    'Facility Inspection Request',
                    'Other Concern'
                ];
            },

            get availableLocations() {
                if (!this.selectedCampus) return [];
                return this.locationsMap[this.selectedCampus] || ['General Campus Area', 'Other Location'];
            },

            get isManpowerCategory() {
                return (this.selectedCategoryName || '').toLowerCase().includes('manpower');
            },

            get isEventConcern() {
                return (this.selectedConcern || '') === 'Event & Activity Venue Setup' ||
                       (this.selectedConcern || '').toLowerCase().includes('event & activity');
            },

            get finalTitle() {
                if (this.isEventConcern && this.activityTitle) {
                    return this.activityTitle;
                }
                if (this.selectedConcern && this.selectedConcern.includes('Other')) {
                    return this.customConcern || this.selectedConcern;
                }
                return this.selectedConcern;
            },

            get finalLocation() {
                if (this.selectedLocation.includes('Other')) {
                    return this.customLocation || this.selectedLocation;
                }
                return this.selectedLocation;
            },

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.processFile(file);
            },

            processFile(file) {
                this.fileName = file.name;
                this.isImage = file.type.startsWith('image/');
                const origSizeFormatted = this.formatBytes(file.size);
                this.fileSizeFormatted = origSizeFormatted;

                if (this.isImage) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.filePreviewUrl = e.target.result;
                        this.compressImage(e.target.result, (compressedBlob, compressedUrl) => {
                            this.filePreviewUrl = compressedUrl;
                            const compSizeFormatted = this.formatBytes(compressedBlob.size);
                            this.fileSizeFormatted = origSizeFormatted + ' → compressed to ~' + compSizeFormatted;

                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(new File([compressedBlob], file.name, { type: 'image/jpeg' }));
                            this.$refs.fileInput.files = dataTransfer.files;
                        });
                    };
                    reader.readAsDataURL(file);
                } else {
                    this.filePreviewUrl = '';
                }
            },

            compressImage(dataUrl, callback) {
                const img = new Image();
                img.onload = () => {
                    const maxWidth = 1200;
                    const maxHeight = 1200;
                    let width = img.width;
                    let height = img.height;

                    if (width > height) {
                        if (width > maxWidth) {
                            height = Math.round((height * maxWidth) / width);
                            width = maxWidth;
                        }
                    } else {
                        if (height > maxHeight) {
                            width = Math.round((width * maxHeight) / height);
                            height = maxHeight;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        const url = URL.createObjectURL(blob);
                        callback(blob, url);
                    }, 'image/jpeg', 0.75);
                };
                img.src = dataUrl;
            },

            removeFile() {
                this.fileName = '';
                this.fileSizeFormatted = '';
                this.filePreviewUrl = '';
                this.isImage = false;
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },

            openCamera() {
                this.cameraActive = true;
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                    .then(stream => {
                        this.cameraStream = stream;
                        this.$nextTick(() => {
                            if (this.$refs.video) {
                                this.$refs.video.srcObject = stream;
                            }
                        });
                    })
                    .catch(err => {
                        alert('Camera access denied or unavailable: ' + err.message);
                        this.cameraActive = false;
                    });
            },

            captureCameraPhoto() {
                const video = this.$refs.video;
                if (!video) return;
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                canvas.toBlob((blob) => {
                    const file = new File([blob], 'camera_photo_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                    this.processFile(file);
                    this.closeCamera();
                }, 'image/jpeg', 0.75);
            },

            closeCamera() {
                if (this.cameraStream) {
                    this.cameraStream.getTracks().forEach(track => track.stop());
                    this.cameraStream = null;
                }
                this.cameraActive = false;
            },

            formatBytes(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
            }
        }" 
        @submit="submitting = true" 
        action="{{ route('client.requests.store') }}" 
        method="POST" 
        enctype="multipart/form-data">
            
            <fieldset class="w-full flex flex-col lg:flex-row gap-8 items-start" {{ (isset($unratedCompletedRequest) && $unratedCompletedRequest) ? 'disabled' : '' }}>
                @csrf

                <!-- Left Column: Requisition Form Card -->
                <div class="flex-1 bg-white dark:bg-[#1c1c1e] rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-800 p-6 sm:p-8 space-y-6">
                    
                    <div class="border-b border-gray-100 dark:border-zinc-800 pb-4">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#0033a0] dark:bg-blue-400"></span>
                            Requisition Specifications
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Select your service requirements and provide full operational location details.</p>
                    </div>

                    <!-- 1. Service Category -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                            Service Category <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id" 
                                x-ref="categorySelect"
                                @change="
                                    const selectedOpt = $event.target.options[$event.target.selectedIndex];
                                    selectedCategoryName = selectedOpt ? selectedOpt.text : '';
                                    selectedConcern = '';
                                    customConcern = '';
                                    activityTitle = '';
                                " 
                                class="w-full px-4 py-3 bg-gray-50/70 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0] transition" 
                                required>
                            <option value="" disabled selected>Select a category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->category_id }}">{{ $cat->category_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Concern / Title -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                            Title of Concern <span class="text-red-500">*</span>
                        </label>
                        
                        <select x-model="selectedConcern"
                                :disabled="!selectedCategoryName"
                                class="w-full px-4 py-3 bg-gray-50/70 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0] disabled:bg-gray-100 dark:disabled:bg-zinc-800/40 disabled:text-gray-400 disabled:cursor-not-allowed transition" 
                                required>
                            <option value="" disabled selected x-text="selectedCategoryName ? 'Select a concern for ' + selectedCategoryName : 'Please select a Service Category first'"></option>
                            <template x-for="concern in availableConcerns" :key="concern">
                                <option :value="concern" x-text="concern"></option>
                            </template>
                        </select>

                        <!-- Dynamic Title of Activity Box (Appears ONLY when 'Event & Activity Venue Setup' is chosen) -->
                        <div x-show="isEventConcern" x-cloak class="mt-3.5 p-4 bg-blue-50/80 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/80 rounded-xl transition-all">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#0033a0] dark:text-blue-300 mb-1.5">
                                Title of the Activity / Event Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   x-model="activityTitle" 
                                   name="activity_title" 
                                   placeholder="e.g. 56th Commencement Exercises, University Intramurals, General Assembly" 
                                   class="w-full px-4 py-2.5 bg-white dark:bg-zinc-800 border border-blue-200 dark:border-zinc-700 rounded-xl text-sm text-gray-900 dark:text-white font-semibold focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0]"
                                   :required="isEventConcern">
                            <p class="text-[11px] text-blue-700 dark:text-blue-400 mt-1">Specify the exact event title to be reflected on the official Manpower Request Form.</p>
                        </div>

                        <!-- Custom Concern Input if 'Other' is selected and not an event -->
                        <div x-show="selectedConcern && selectedConcern.includes('Other') && !isEventConcern" x-cloak class="mt-3">
                            <input type="text" 
                                   x-model="customConcern" 
                                   placeholder="Please specify your concern / title" 
                                   class="w-full px-4 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0]"
                                   :required="selectedConcern && selectedConcern.includes('Other') && !isEventConcern">
                        </div>

                        <!-- Hidden Input submitting the final title -->
                        <input type="hidden" name="title" :value="finalTitle">
                    </div>

                    <!-- 3. Campus & Location Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Campus -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                                Campus <span class="text-red-500">*</span>
                            </label>
                            <select name="campus" 
                                    x-model="selectedCampus"
                                    @change="selectedLocation = ''; customLocation = '';"
                                    class="w-full px-4 py-3 bg-gray-50/70 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0] transition" 
                                    required>
                                <option value="" disabled selected>Select Campus</option>
                                <option value="BU Main">BU Main</option>
                                <option value="BU Daraga">BU Daraga</option>
                                <option value="BU East">BU East</option>
                                <option value="BU Polangui">BU Polangui</option>
                                <option value="BU Tabaco">BU Tabaco</option>
                                <option value="BU Gubat">BU Gubat</option>
                                <option value="BU Guinobatan">BU Guinobatan</option>
                            </select>
                        </div>

                        <!-- Office / Location Dropdown (Dependent on Campus) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                                Office / Location / Venue <span class="text-red-500">*</span>
                            </label>
                            
                            <select x-model="selectedLocation"
                                    :disabled="!selectedCampus"
                                    class="w-full px-4 py-3 bg-gray-50/70 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0] disabled:bg-gray-100 dark:disabled:bg-zinc-800/40 disabled:text-gray-400 disabled:cursor-not-allowed transition" 
                                    required>
                                <option value="" disabled selected x-text="selectedCampus ? 'Select venue in ' + selectedCampus : 'Please select a Campus first'"></option>
                                <template x-for="loc in availableLocations" :key="loc">
                                    <option :value="loc" x-text="loc"></option>
                                </template>
                            </select>

                            <!-- Custom Location Input if 'Other' is selected -->
                            <div x-show="selectedLocation && selectedLocation.includes('Other')" x-cloak class="mt-2.5">
                                <input type="text" 
                                       x-model="customLocation" 
                                       placeholder="Please specify specific venue, office, or room (e.g. BU-PGA Sports Complex, Grounds, Room 204)" 
                                       class="w-full px-4 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0]"
                                       :required="selectedLocation && selectedLocation.includes('Other')">
                            </div>

                            <!-- Hidden Input submitting the final location -->
                            <input type="hidden" name="location" :value="finalLocation">
                        </div>
                    </div>

                    <!-- Hidden Complexity & Urgency Defaults -->
                    <input type="hidden" name="complexity" value="low">
                    <input type="hidden" name="urgency" value="low">

                    <!-- ============================================================== -->
                    <!-- CONDITIONAL SECTION: MANPOWER WORK DETAILS & SCHEDULE          -->
                    <!-- ============================================================== -->
                    <div x-show="isManpowerCategory" x-cloak class="space-y-4 pt-2">
                        <div class="p-4 bg-blue-50/80 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/80 rounded-xl">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#0033a0] dark:bg-blue-400"></span>
                                <h3 class="text-xs font-bold tracking-wider text-[#0033a0] dark:text-blue-300 uppercase">
                                    Work Details To Be Completed For The Event / Request
                                </h3>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Please provide preparation, event assistance, and clearing details with schedule timings.</p>
                        </div>

                        <!-- 1. Preparation Activity -->
                        <div class="p-4 bg-slate-50/70 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-700 rounded-xl space-y-3">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                <label class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide">1. Preparation Activity</label>
                            </div>
                            <!-- Date Range -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">From Date</label>
                                    <input type="date" x-model="prepDateFrom" @change="if(!prepDateTo) prepDateTo = prepDateFrom"
                                           class="w-full px-3 py-1.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">To Date</label>
                                    <input type="date" x-model="prepDateTo" :min="prepDateFrom"
                                           class="w-full px-3 py-1.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                                </div>
                            </div>
                            <input type="hidden" name="prep_date" :value="prepDate">
                            <!-- Details -->
                            <textarea x-model="prepDetails" name="prep_details" rows="2"
                                      placeholder="Describe preparation tasks (e.g. Assist PDMO in ground preparation, stage setup, table & chair arrangement)..."
                                      class="w-full p-3 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0] resize-y"></textarea>
                            <!-- Time Row -->
                            <div class="flex flex-wrap items-center gap-4 pt-1 text-xs">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="prepRegular" name="prep_regular" value="1"
                                           class="w-4 h-4 text-[#0033a0] rounded border-gray-300 dark:border-zinc-700 focus:ring-[#0033a0]">
                                    <span class="font-bold text-gray-700 dark:text-gray-300">Regular time:</span>
                                </label>
                                <div class="flex items-center gap-2" x-show="prepRegular">
                                    <select x-model="prepTimePreset" @change="applyTimePreset('prep')"
                                            class="px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                                        <template x-for="p in timePresets" :key="p.value">
                                            <option :value="p.value" x-text="p.label"></option>
                                        </template>
                                    </select>
                                    <input type="text" x-model="prepRegularTime"
                                           x-show="prepTimePreset === 'custom'"
                                           placeholder="e.g. 10:00 - 3:00"
                                           class="px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs text-gray-900 dark:text-white w-36 font-medium">
                                    <span x-show="prepTimePreset !== 'custom'" class="text-gray-600 dark:text-gray-300 font-medium" x-text="prepRegularTime"></span>
                                    <input type="hidden" name="prep_regular_time" :value="prepRegularTime">
                                </div>
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="prepOvertime" name="prep_overtime" value="1"
                                           class="w-4 h-4 text-[#0033a0] rounded border-gray-300 dark:border-zinc-700 focus:ring-[#0033a0]">
                                    <span class="font-bold text-gray-700 dark:text-gray-300">Overtime:</span>
                                    <input type="text" x-model="prepOvertimeTime" name="prep_overtime_time"
                                           placeholder="e.g. 5:00 PM - 8:00 PM"
                                           class="px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs text-gray-900 dark:text-white w-36 font-medium"
                                           :disabled="!prepOvertime">
                                </label>
                            </div>
                        </div>

                        <!-- 2. Assistance During the Event -->
                        <div class="p-4 bg-slate-50/70 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-700 rounded-xl space-y-3">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                <label class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide">2. Assistance During The Event</label>
                            </div>
                            <!-- Date Range -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">From Date</label>
                                    <input type="date" x-model="assistanceDateFrom" @change="if(!assistanceDateTo) assistanceDateTo = assistanceDateFrom"
                                           class="w-full px-3 py-1.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">To Date</label>
                                    <input type="date" x-model="assistanceDateTo" :min="assistanceDateFrom"
                                           class="w-full px-3 py-1.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                                </div>
                            </div>
                            <input type="hidden" name="assistance_date" :value="assistanceDate">
                            <!-- Details -->
                            <textarea x-model="assistanceDetails" name="assistance_details" rows="2"
                                      placeholder="Describe event assistance (e.g. Maintain cleanliness, physical orderliness of venue, ushering assistance)..."
                                      class="w-full p-3 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0] resize-y"></textarea>
                            <!-- Time Row -->
                            <div class="flex flex-wrap items-center gap-4 pt-1 text-xs">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="assistanceRegular" name="assistance_regular" value="1"
                                           class="w-4 h-4 text-[#0033a0] rounded border-gray-300 dark:border-zinc-700 focus:ring-[#0033a0]">
                                    <span class="font-bold text-gray-700 dark:text-gray-300">Regular time:</span>
                                </label>
                                <div class="flex items-center gap-2" x-show="assistanceRegular">
                                    <select x-model="assistanceTimePreset" @change="applyTimePreset('assistance')"
                                            class="px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                                        <template x-for="p in timePresets" :key="p.value">
                                            <option :value="p.value" x-text="p.label"></option>
                                        </template>
                                    </select>
                                    <input type="text" x-model="assistanceRegularTime"
                                           x-show="assistanceTimePreset === 'custom'"
                                           placeholder="e.g. 10:00 - 3:00"
                                           class="px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs text-gray-900 dark:text-white w-36 font-medium">
                                    <span x-show="assistanceTimePreset !== 'custom'" class="text-gray-600 dark:text-gray-300 font-medium" x-text="assistanceRegularTime"></span>
                                    <input type="hidden" name="assistance_regular_time" :value="assistanceRegularTime">
                                </div>
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="assistanceOvertime" name="assistance_overtime" value="1"
                                           class="w-4 h-4 text-[#0033a0] rounded border-gray-300 dark:border-zinc-700 focus:ring-[#0033a0]">
                                    <span class="font-bold text-gray-700 dark:text-gray-300">Overtime:</span>
                                    <input type="text" x-model="assistanceOvertimeTime" name="assistance_overtime_time"
                                           placeholder="e.g. 5:00 PM - 10:00 PM"
                                           class="px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs text-gray-900 dark:text-white w-36 font-medium"
                                           :disabled="!assistanceOvertime">
                                </label>
                            </div>
                        </div>

                        <!-- 3. Clearing Upon the Event -->
                        <div class="p-4 bg-slate-50/70 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-700 rounded-xl space-y-3">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                                <label class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide">3. Clearing Upon The Event</label>
                            </div>
                            <!-- Date Range -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">From Date</label>
                                    <input type="date" x-model="clearingDateFrom" @change="if(!clearingDateTo) clearingDateTo = clearingDateFrom"
                                           class="w-full px-3 py-1.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">To Date</label>
                                    <input type="date" x-model="clearingDateTo" :min="clearingDateFrom"
                                           class="w-full px-3 py-1.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                                </div>
                            </div>
                            <input type="hidden" name="clearing_date" :value="clearingDate">
                            <!-- Details -->
                            <textarea x-model="clearingDetails" name="clearing_details" rows="2"
                                      placeholder="Describe clearing tasks (e.g. Collect & stack chairs, dismantle booths, and haul/dispose wastes)..."
                                      class="w-full p-3 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0] resize-y"></textarea>
                            <!-- Time Row -->
                            <div class="flex flex-wrap items-center gap-4 pt-1 text-xs">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="clearingRegular" name="clearing_regular" value="1"
                                           class="w-4 h-4 text-[#0033a0] rounded border-gray-300 dark:border-zinc-700 focus:ring-[#0033a0]">
                                    <span class="font-bold text-gray-700 dark:text-gray-300">Regular time:</span>
                                </label>
                                <div class="flex items-center gap-2" x-show="clearingRegular">
                                    <select x-model="clearingTimePreset" @change="applyTimePreset('clearing')"
                                            class="px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                                        <template x-for="p in timePresets" :key="p.value">
                                            <option :value="p.value" x-text="p.label"></option>
                                        </template>
                                    </select>
                                    <input type="text" x-model="clearingRegularTime"
                                           x-show="clearingTimePreset === 'custom'"
                                           placeholder="e.g. 10:00 - 3:00"
                                           class="px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs text-gray-900 dark:text-white w-36 font-medium">
                                    <span x-show="clearingTimePreset !== 'custom'" class="text-gray-600 dark:text-gray-300 font-medium" x-text="clearingRegularTime"></span>
                                    <input type="hidden" name="clearing_regular_time" :value="clearingRegularTime">
                                </div>
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="clearingOvertime" name="clearing_overtime" value="1"
                                           class="w-4 h-4 text-[#0033a0] rounded border-gray-300 dark:border-zinc-700 focus:ring-[#0033a0]">
                                    <span class="font-bold text-gray-700 dark:text-gray-300">Overtime:</span>
                                    <input type="text" x-model="clearingOvertimeTime" name="clearing_overtime_time"
                                           placeholder="e.g. 5:00 PM - 8:00 PM"
                                           class="px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs text-gray-900 dark:text-white w-36 font-medium"
                                           :disabled="!clearingOvertime">
                                </label>
                            </div>
                        </div>

                        <!-- 4. Additional Note -->
                        <div class="p-4 bg-slate-50/70 dark:bg-zinc-800/40 border border-gray-200 dark:border-zinc-700 rounded-xl space-y-2">
                            <label class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wide">
                                4. Additional Note <span class="text-gray-500 dark:text-gray-400 font-normal lowercase">(supplies, materials, tools, equipment to be used)</span>
                            </label>
                            <textarea x-model="additionalNotes" name="additional_notes" rows="2"
                                      placeholder="e.g. 100 Monoblock chairs needed, ensure all materials are prepared before the event date..."
                                      class="w-full p-3 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-xs text-gray-900 dark:text-white focus:outline-none focus:border-[#0033a0] resize-y"></textarea>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- STANDARD DESCRIPTION (FOR ALL NON-MANPOWER CATEGORIES)        -->
                    <!-- ============================================================== -->
                    <div x-show="!isManpowerCategory" x-cloak>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                            Description / Specific Work Requirements <span class="text-red-500">*</span>
                        </label>
                        <div class="border border-gray-200 dark:border-zinc-700 rounded-xl overflow-hidden focus-within:border-[#0033a0] focus-within:ring-1 focus-within:ring-[#0033a0] transition">
                            <!-- Toolbar -->
                            <div class="bg-gray-50 dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700 px-3 py-2 flex items-center gap-2.5 text-gray-400 dark:text-gray-400 text-[14px] flex-wrap">
                                <button type="button" class="hover:text-gray-700 dark:hover:text-gray-200 font-bold px-1">B</button>
                                <button type="button" class="hover:text-gray-700 dark:hover:text-gray-200 italic px-1">I</button>
                                <button type="button" class="hover:text-gray-700 dark:hover:text-gray-200 underline px-1">U</button>
                                <button type="button" class="hover:text-gray-700 dark:hover:text-gray-200 line-through px-1">S</button>
                                <span class="text-gray-300 dark:text-zinc-600">|</span>
                                <button type="button" class="hover:text-gray-700 dark:hover:text-gray-200 px-1">≡</button>
                                <button type="button" class="hover:text-gray-700 dark:hover:text-gray-200 px-1">☰</button>
                                <button type="button" class="hover:text-gray-700 dark:hover:text-gray-200 px-1">❝</button>
                            </div>
                            <textarea name="description" rows="5" placeholder="Provide complete specifications regarding the problem, symptoms, or requested maintenance work..." class="w-full p-4 bg-white dark:bg-zinc-900 text-sm text-gray-900 dark:text-white focus:outline-none border-none resize-y" :required="!isManpowerCategory"></textarea>
                        </div>
                    </div>

                    <!-- 4. Supporting Documents & Camera Photo Capture -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                            Upload Supporting Documents / Photo (Optional)
                        </label>

                        <!-- Hidden Native File Input -->
                        <input type="file" 
                               x-ref="fileInput" 
                               name="attachment" 
                               accept="image/*,application/pdf"
                               @change="handleFileSelect($event)" 
                               class="hidden">

                        <!-- Dropzone / Selected File Card -->
                        <div x-show="!fileName" 
                             class="border-2 border-dashed border-gray-300 dark:border-zinc-700 rounded-xl p-6 text-center bg-gray-50/60 dark:bg-zinc-800/40 hover:bg-gray-100/50 dark:hover:bg-zinc-800/70 transition relative">
                            
                            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                                <!-- Browse Button -->
                                <button type="button" 
                                        @click="$refs.fileInput.click()" 
                                        class="px-5 py-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700 transition shadow-xs inline-flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#0033a0] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Browse Document / Image
                                </button>

                                <span class="text-xs text-gray-400 font-medium">or</span>

                                <!-- Camera Button -->
                                <button type="button" 
                                        @click="openCamera()" 
                                        class="px-5 py-2.5 bg-[#0033a0] text-white rounded-xl text-xs font-bold hover:bg-[#002480] transition shadow-xs inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Take Photo with Camera
                                </button>
                            </div>

                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-2.5">
                                Accepts images (JPG, PNG) or PDF. Photos are automatically compressed to low MB size.
                            </p>
                        </div>

                        <!-- Selected File Preview Card -->
                        <div x-show="fileName" x-cloak class="border border-blue-200 dark:border-blue-900 bg-blue-50/50 dark:bg-blue-950/20 rounded-xl p-4 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <!-- Image Preview Thumbnail or PDF Icon -->
                                <template x-if="isImage && filePreviewUrl">
                                    <img :src="filePreviewUrl" alt="Preview" @click="viewPreviewModal = true" class="w-12 h-12 object-cover rounded-lg border border-blue-200 dark:border-blue-800 shadow-xs shrink-0 cursor-pointer hover:opacity-80 transition" title="Click to view full photo">
                                </template>
                                <template x-if="!isImage">
                                    <div @click="viewPreviewModal = true" class="w-12 h-12 bg-blue-100 dark:bg-blue-900/60 text-[#0033a0] dark:text-blue-300 rounded-lg flex items-center justify-center font-black text-xs uppercase shrink-0 cursor-pointer hover:opacity-80 transition" title="Click to preview document">
                                        DOC
                                    </div>
                                </template>

                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate" x-text="fileName"></p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5" x-text="fileSizeFormatted"></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <template x-if="filePreviewUrl">
                                    <button type="button" 
                                            @click="viewPreviewModal = true" 
                                            class="px-3 py-1.5 text-xs font-bold text-[#0033a0] dark:text-blue-400 bg-blue-100 dark:bg-blue-900/40 hover:bg-blue-200 dark:hover:bg-blue-800/60 rounded-lg transition border border-blue-200 dark:border-blue-800 shrink-0 inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Preview
                                    </button>
                                </template>
                                <button type="button" 
                                        @click="removeFile()" 
                                        class="px-3 py-1.5 text-xs font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/50 hover:bg-red-100 dark:hover:bg-red-900/60 rounded-lg transition border border-red-200 dark:border-red-800/80 shrink-0">
                                    ✕ Remove
                                </button>
                            </div>
                        </div>

                        <!-- Uploaded File Preview Modal (Popup without leaving page) -->
                        <div x-show="viewPreviewModal" 
                             x-cloak 
                             class="fixed inset-0 bg-black/80 backdrop-blur-xs z-50 flex items-center justify-center p-4" 
                             @keydown.escape.window="viewPreviewModal = false">
                            <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-3xl w-full max-h-[90vh] shadow-2xl relative flex flex-col border border-gray-200 dark:border-zinc-800 overflow-hidden" 
                                 @click.away="viewPreviewModal = false">
                                <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-200 dark:border-zinc-800 bg-gray-50/80 dark:bg-zinc-800/80">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <svg class="w-4 h-4 text-[#0033a0] dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="fileName || 'Attachment Preview'"></span>
                                    </div>
                                    <button type="button" @click="viewPreviewModal = false" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition p-1 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div class="p-4 flex items-center justify-center overflow-auto max-h-[75vh] bg-zinc-950/40">
                                    <template x-if="isImage && filePreviewUrl">
                                        <img :src="filePreviewUrl" alt="Full Preview" class="max-h-[70vh] w-auto max-w-full object-contain rounded-lg shadow-md">
                                    </template>
                                    <template x-if="!isImage && filePreviewUrl">
                                        <iframe :src="filePreviewUrl" class="w-full h-[70vh] rounded-lg border-0 bg-white"></iframe>
                                    </template>
                                    <template x-if="!filePreviewUrl">
                                        <div class="text-center py-12 text-gray-400 text-xs">
                                            <p>Document selected: <span class="font-bold text-white" x-text="fileName"></span></p>
                                            <p class="text-[11px] text-gray-500 mt-1">Ready for submission.</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- WebCam Camera Snapshot Modal -->
                        <div x-show="cameraActive" x-cloak class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
                            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 max-w-lg w-full shadow-2xl relative flex flex-col items-center border border-gray-200 dark:border-zinc-800">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Snap Photo of Service Concern</h3>
                                
                                <div class="w-full bg-black rounded-xl overflow-hidden mb-4 relative aspect-video flex items-center justify-center">
                                    <video x-ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
                                </div>

                                <div class="flex items-center gap-3 w-full justify-end">
                                    <button type="button" @click="closeCamera()" class="px-5 py-2.5 bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition">
                                        Cancel
                                    </button>
                                    <button type="button" @click="captureCameraPhoto()" class="px-6 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition inline-flex items-center gap-2 shadow-md">
                                        Snap Photo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Action Submit Button -->
                    <div class="pt-4 border-t border-gray-100 dark:border-zinc-800 flex justify-end">
                        <button type="submit" 
                                :disabled="submitting" 
                                class="w-full sm:w-auto px-8 py-3.5 bg-[#0038A8] hover:bg-[#002B82] text-white rounded-xl font-bold text-sm transition shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2">
                            <svg x-show="submitting" x-cloak class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="submitting ? 'Submitting Request...' : 'Submit Service Request'">Submit Service Request</span>
                        </button>
                    </div>

                </div>

                <!-- Right Column: Requester Information & Guidelines -->
                <div class="w-full lg:w-80 space-y-6 shrink-0">
                    
                    <!-- Requester Profile Card -->
                    <div class="bg-white dark:bg-[#1c1c1e] rounded-2xl shadow-sm border border-gray-200 dark:border-zinc-800 p-6 space-y-5">
                        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 dark:border-zinc-800">
                            <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-400 flex items-center justify-center font-black text-sm border border-blue-100 dark:border-blue-900 shrink-0">
                                {{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name ?? '', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                    {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->email_account }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                                Contact Number <span class="text-red-500">*</span>
                            </label>
                            <div class="flex bg-gray-50 dark:bg-zinc-800/80 border border-gray-200 dark:border-zinc-700 rounded-xl overflow-hidden focus-within:border-[#0033a0] focus-within:ring-1 focus-within:ring-[#0033a0] transition">
                                <div class="bg-gray-100 dark:bg-zinc-700/50 px-3 py-2.5 border-r border-gray-200 dark:border-zinc-700 text-sm flex items-center">
                                    🇵🇭
                                </div>
                                <input type="text" 
                                       name="contact_number" 
                                       value="{{ old('contact_number', auth()->user()->contact_number) }}" 
                                       placeholder="09123456789" 
                                       class="w-full px-3 py-2.5 bg-transparent text-sm text-gray-900 dark:text-white focus:outline-none" 
                                       pattern="^09\d{9}$" 
                                       title="Contact number must be 11 digits starting with 09" 
                                       maxlength="11" 
                                       required>
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1.5">
                                GSO will use this number for urgent dispatch & status inquiries.
                            </p>
                        </div>
                    </div>

                    <!-- Quick Help & Guidelines Card -->
                    <div class="bg-gradient-to-br from-blue-50/80 to-indigo-50/50 dark:from-zinc-900 dark:to-zinc-800/80 rounded-2xl border border-blue-100 dark:border-zinc-800 p-6 space-y-3.5">
                        <div class="flex items-center gap-2 text-[#0033a0] dark:text-blue-400 font-bold text-xs uppercase tracking-wider">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Requisition Guidelines
                        </div>
                        
                        <ul class="text-xs text-gray-600 dark:text-gray-300 space-y-2.5 leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="text-[#0033a0] dark:text-blue-400 font-bold">•</span>
                                <span><strong>Event Setup:</strong> When selecting <em>Event & Activity Venue Setup</em>, enter your official event title and complete phase timetable.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#0033a0] dark:text-blue-400 font-bold">•</span>
                                <span><strong>GSO Review:</strong> Requests undergo priority triage & worker dispatch.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#0033a0] dark:text-blue-400 font-bold">•</span>
                                <span><strong>Photo Proof:</strong> Attach photos to help our team prepare necessary tools.</span>
                            </li>
                        </ul>
                    </div>

                </div>
            </fieldset>
        </form>
    </main>
</div>
@endsection
