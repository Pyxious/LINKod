@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<form x-data="{
    submitting: false,
    selectedCategoryId: '{{ $preselectedCatId ?? "" }}',
    selectedCategoryName: '',
    selectedCampus: '',
    selectedConcern: '',
    customConcern: '',
    selectedLocation: '',
    customLocation: '',

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
            'Heavy Equipment & Furniture Relocation',
            'Event & Activity Venue Setup',
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

    get finalTitle() {
        if (this.selectedConcern.includes('Other')) {
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
                this.compressImage(e.target.result, (compressedBlob, compressedUrl) => {
                    this.filePreviewUrl = compressedUrl;
                    const compSizeFormatted = this.formatBytes(compressedBlob.size);
                    this.fileSizeFormatted = origSizeFormatted + ' → compressed to ~' + compSizeFormatted;

                    const compressedFile = new File([compressedBlob], file.name.replace(/\.[^/.]+$/, '') + '.jpg', {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });
                    const container = new DataTransfer();
                    container.items.add(compressedFile);
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.files = container.files;
                    }
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
            const canvas = document.createElement('canvas');
            const MAX_WIDTH = 1200;
            const MAX_HEIGHT = 1200;
            let width = img.width;
            let height = img.height;

            if (width > height) {
                if (width > MAX_WIDTH) {
                    height *= MAX_WIDTH / width;
                    width = MAX_WIDTH;
                }
            } else {
                if (height > MAX_HEIGHT) {
                    width *= MAX_HEIGHT / height;
                    height = MAX_HEIGHT;
                }
            }

            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            canvas.toBlob((blob) => {
                const compressedUrl = URL.createObjectURL(blob);
                callback(blob, compressedUrl);
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
                if (this.$refs.video) {
                    this.$refs.video.srcObject = stream;
                }
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
enctype="multipart/form-data" 
class="w-full max-w-6xl mx-auto px-6 py-8 flex flex-col font-sans">
    
    @if(isset($unratedCompletedRequest) && $unratedCompletedRequest)
        <div class="w-full mb-6 p-6 bg-amber-50 border-2 border-amber-300 rounded-2xl shadow-sm text-amber-900 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-amber-100 border border-amber-300 text-amber-700 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-amber-900 mb-1">New Service Request Locked</h3>
                    <p class="text-xs md:text-sm text-amber-800 leading-relaxed">
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

    <div class="flex flex-col md:flex-row gap-6 items-start w-full">
        <fieldset class="w-full flex flex-col md:flex-row gap-6 items-start" {{ (isset($unratedCompletedRequest) && $unratedCompletedRequest) ? 'disabled' : '' }}>
            <!-- Left Column: Service Job Request Details -->
            <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <div class="mb-6">
            <h2 class="text-[#0033a0] text-lg font-bold italic mb-1">Service Job Request Details</h2>
            <p class="text-gray-400 text-sm">Please provide the necessary details for your request.</p>
        </div>

        <div class="space-y-5">
            @csrf

            <!-- Service Category -->
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1.5">Service Category <span class="text-red-500">*</span></label>
                <select name="category_id" 
                        x-ref="categorySelect"
                        @change="
                            const selectedOpt = $event.target.options[$event.target.selectedIndex];
                            selectedCategoryName = selectedOpt ? selectedOpt.text : '';
                            selectedConcern = '';
                            customConcern = '';
                        " 
                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0]" 
                        required>
                    <option value="" disabled selected>Select an option</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->category_id }}">{{ $cat->category_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Concern / Title Dropdown (Dependent on Service Category) -->
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1.5">Title of Concern <span class="text-red-500">*</span></label>
                
                <select x-model="selectedConcern"
                        :disabled="!selectedCategoryName"
                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0] disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed" 
                        required>
                    <option value="" disabled selected x-text="selectedCategoryName ? 'Select a concern for ' + selectedCategoryName : 'Please select a Service Category first'"></option>
                    <template x-for="concern in availableConcerns" :key="concern">
                        <option :value="concern" x-text="concern"></option>
                    </template>
                </select>

                <!-- Custom Concern Input if 'Other' is selected -->
                <div x-show="selectedConcern && selectedConcern.includes('Other')" x-cloak class="mt-3">
                    <input type="text" 
                           x-model="customConcern" 
                           placeholder="Please specify your concern / title" 
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0]"
                           :required="selectedConcern && selectedConcern.includes('Other')">
                </div>

                <!-- Hidden Input submitting the final title -->
                <input type="hidden" name="title" :value="finalTitle">
            </div>

            <!-- Campus -->
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1.5">Campus <span class="text-red-500">*</span></label>
                <select name="campus" 
                        x-model="selectedCampus"
                        @change="selectedLocation = ''; customLocation = '';"
                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0]" 
                        required>
                    <option value="" disabled selected>Select an option</option>
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
                <label class="block text-sm font-bold text-gray-800 mb-1.5">Office / Location <span class="text-red-500">*</span></label>
                
                <select x-model="selectedLocation"
                        :disabled="!selectedCampus"
                        class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0] disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed" 
                        required>
                    <option value="" disabled selected x-text="selectedCampus ? 'Select office / building in ' + selectedCampus : 'Please select a Campus first'"></option>
                    <template x-for="loc in availableLocations" :key="loc">
                        <option :value="loc" x-text="loc"></option>
                    </template>
                </select>

                <!-- Custom Location Input if 'Other' is selected -->
                <div x-show="selectedLocation && selectedLocation.includes('Other')" x-cloak class="mt-3">
                    <input type="text" 
                           x-model="customLocation" 
                           placeholder="Please specify specific office, room number, or area (e.g. IT Building, Room 202)" 
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:border-[#0033a0] focus:ring-1 focus:ring-[#0033a0]"
                           :required="selectedLocation && selectedLocation.includes('Other')">
                </div>

                <!-- Hidden Input submitting the final location -->
                <input type="hidden" name="location" :value="finalLocation">
            </div>

            <!-- Complexity & Urgency (default low) -->
            <input type="hidden" name="complexity" value="low">
            <input type="hidden" name="urgency" value="low">

            <!-- Description (Mock WYSIWYG) -->
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1.5">Description <span class="text-red-500">*</span></label>
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <!-- Toolbar -->
                    <div class="bg-gray-50 border-b border-gray-200 px-3 py-2 flex items-center gap-2.5 text-gray-400 text-[15px] flex-wrap">
                        <button type="button" class="hover:text-gray-700 font-bold px-1">B</button>
                        <button type="button" class="hover:text-gray-700 italic px-1">I</button>
                        <button type="button" class="hover:text-gray-700 underline px-1">U</button>
                        <button type="button" class="hover:text-gray-700 line-through px-1">S</button>
                        <button type="button" class="hover:text-gray-700 text-xs px-1">X₂</button>
                        <button type="button" class="hover:text-gray-700 text-xs px-1">X²</button>
                        <button type="button" class="hover:text-gray-700 px-1">🔗</button>
                        <span class="text-gray-200">|</span>
                        <button type="button" class="hover:text-gray-700 font-bold text-xs px-1">H₂</button>
                        <button type="button" class="hover:text-gray-700 font-bold text-xs px-1">H₃</button>
                        <span class="text-gray-200">|</span>
                        <button type="button" class="hover:text-gray-700 px-1">≡</button>
                        <button type="button" class="hover:text-gray-700 px-1">☰</button>
                        <button type="button" class="hover:text-gray-700 px-1">⊞</button>
                        <span class="text-gray-200">|</span>
                        <button type="button" class="hover:text-gray-700 px-1">❝</button>
                        <button type="button" class="hover:text-gray-700 px-1">☷</button>
                        <button type="button" class="hover:text-gray-700 px-1">⊞</button>
                        <span class="text-gray-200">|</span>
                        <button type="button" class="hover:text-gray-700 px-1">🔗</button>
                        <span class="text-gray-200">|</span>
                        <button type="button" class="hover:text-gray-700 px-1">↺</button>
                        <button type="button" class="hover:text-gray-700 px-1">↻</button>
                    </div>
                    <textarea name="description" rows="5" placeholder="Provide additional details regarding your request..." class="w-full p-4 bg-white text-sm text-gray-700 focus:outline-none border-none resize-y" required></textarea>
                </div>
            </div>

            <!-- Supporting Documents & Camera Photo Capture -->
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1.5">Upload Supporting Documents / Photo</label>

                <!-- Hidden Native File Input -->
                <input type="file" 
                       x-ref="fileInput" 
                       name="attachment" 
                       accept="image/*,application/pdf"
                       @change="handleFileSelect($event)" 
                       class="hidden">

                <!-- Dropzone / Selected File Card -->
                <div x-show="!fileName" 
                     class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center bg-gray-50/50 hover:bg-gray-50 transition relative">
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <!-- Browse Button -->
                        <button type="button" 
                                @click="$refs.fileInput.click()" 
                                class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm inline-flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#0033a0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Browse / Drop File
                        </button>

                        <span class="text-xs text-gray-400 font-medium">or</span>

                        <!-- Camera Button -->
                        <button type="button" 
                                @click="openCamera()" 
                                class="px-5 py-2.5 bg-[#0033a0] text-white rounded-lg text-sm font-semibold hover:bg-[#002480] transition shadow-sm inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Take Photo with Camera
                        </button>
                    </div>

                    <p class="text-xs text-gray-400 mt-3">
                        Accepts images (JPG, PNG) or PDF. Photos are automatically compressed to low MB size.
                    </p>
                </div>

                <!-- Selected File Preview Card -->
                <div x-show="fileName" x-cloak class="border border-blue-200 bg-blue-50/50 rounded-xl p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Image Preview Thumbnail or PDF Icon -->
                        <template x-if="isImage && filePreviewUrl">
                            <img :src="filePreviewUrl" alt="Preview" class="w-14 h-14 object-cover rounded-lg border border-blue-200 shadow-sm shrink-0">
                        </template>
                        <template x-if="!isImage">
                            <div class="w-14 h-14 bg-blue-100 text-[#0033a0] rounded-lg flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                DOC
                            </div>
                        </template>

                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate" x-text="fileName"></p>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="fileSizeFormatted"></p>
                        </div>
                    </div>

                    <button type="button" 
                            @click="removeFile()" 
                            class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition border border-red-200 shrink-0">
                        ✕ Remove Photo
                    </button>
                </div>

                <!-- WebCam Camera Snapshot Modal -->
                <div x-show="cameraActive" x-cloak class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl relative flex flex-col items-center">
                        <h3 class="text-base font-bold text-gray-900 mb-3">Snap Photo of Service Concern</h3>
                        
                        <div class="w-full bg-black rounded-xl overflow-hidden mb-4 relative aspect-video flex items-center justify-center">
                            <video x-ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
                        </div>

                        <div class="flex items-center gap-3 w-full justify-end">
                            <button type="button" @click="closeCamera()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">
                                Cancel
                            </button>
                            <button type="button" @click="captureCameraPhoto()" class="px-6 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition inline-flex items-center gap-2 shadow-md">
                                Snap Photo
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" 
                        :disabled="submitting" 
                        class="px-8 py-3 bg-[#0033a0] text-white rounded-lg font-semibold text-sm hover:bg-[#002480] transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-2">
                    <svg x-show="submitting" x-cloak class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="submitting ? 'Submitting Request...' : 'Submit Request'">Submit Request</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Right Column: Client Information -->
    <div class="w-full md:w-80 bg-[#e8f0fe] rounded-xl shadow-sm border border-blue-100 p-7">
        <div class="mb-5">
            <h2 class="text-[#0033a0] text-base font-bold mb-1">Client Information</h2>
            <p class="text-gray-400 text-xs">Please provide the necessary details for your request.</p>
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-800 mb-1.5">Contact Number <span class="text-red-500">*</span></label>
            <div class="flex bg-white border border-gray-200 rounded-xl overflow-hidden focus-within:border-[#0033a0] focus-within:ring-1 focus-within:ring-[#0033a0]">
                <div class="bg-gray-50 px-3 py-3 border-r border-gray-200 text-gray-400 text-sm flex items-center">
                    🇵🇭
                </div>
                <input type="text" name="contact_number" value="{{ old('contact_number', auth()->user()->contact_number) }}" placeholder="Enter your contact number" class="w-full px-3 py-3 bg-white text-sm text-gray-700 focus:outline-none border-none" pattern="^09\d{9}$" title="Contact number must be 11 digits and start with 09" maxlength="11" required>
            </div>
            <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                Your contact number will be used to contact you regarding your request.
            </p>
        </div>
    </div>
    </fieldset>
    </div>
</form>
@endsection
