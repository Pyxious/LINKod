@extends('layouts.admin')

@section('page-title', 'Create Walk-In Service Request')

@section('content')
<div class="max-w-5xl mx-auto py-4">

    <!-- Top Banner -->
    <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-8 py-6 mb-6 shadow-sm flex justify-between items-center">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.requests.index') }}" class="text-[#0033a0] dark:text-blue-400 hover:underline text-xs font-semibold uppercase tracking-wider">&larr; Back to Requests</a>
            </div>
            <h1 class="text-[#0033a0] dark:text-blue-400 text-2xl font-bold mb-1">Walk-In Service Request</h1>
            <p class="text-[#0033a0]/80 dark:text-gray-300 text-sm font-medium">File a service request on behalf of a walk-in client without an active portal account.</p>
        </div>
        <div class="hidden sm:block">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-[#1a3c8f]/10 text-[#1a3c8f] dark:bg-blue-900/30 dark:text-blue-300 border border-[#1a3c8f]/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Walk-In Entry
            </span>
        </div>
    </div>

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-300 text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.requests.store') }}" method="POST" enctype="multipart/form-data" 
        x-data="{
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

            fileName: '',
            fileSizeFormatted: '',

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
                if (file) {
                    this.fileName = file.name;
                    this.fileSizeFormatted = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                }
            }
        }"
        @submit="submitting = true" class="space-y-6">
        @csrf

        <!-- SECTION 1: Walk-In Client Details -->
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-6 shadow-xs">
            <div class="flex items-center gap-3 mb-5 border-b border-gray-100 dark:border-zinc-800 pb-3">
                <div class="w-8 h-8 rounded-lg bg-[#1a3c8f]/10 text-[#1a3c8f] dark:bg-blue-900/30 dark:text-blue-400 flex items-center justify-center font-bold text-sm">1</div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Walk-In Client Information</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Enter client's contact info. If they register in the future using this email, their requests will automatically reflect on their portal.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- First Name -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required 
                        placeholder="e.g. Juan"
                        class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white">
                    @error('first_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required 
                        placeholder="e.g. Dela Cruz"
                        class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white">
                    @error('last_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Client Email -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Client Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="client_email" value="{{ old('client_email') }}" required 
                        placeholder="e.g. walkin.client@bicol-u.edu.ph"
                        pattern=".*@bicol-u\.edu\.ph$"
                        title="Must be an official BU email address ending with @bicol-u.edu.ph"
                        class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border @error('client_email') border-red-500 dark:border-red-500 @else border-gray-300 dark:border-zinc-700 @enderror rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white">
                    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Must end with <span class="font-semibold text-gray-700 dark:text-gray-300">@bicol-u.edu.ph</span></p>
                    @error('client_email') <p class="mt-1 text-xs text-red-500 font-semibold flex items-center gap-1"><svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> {{ $message }}</p> @enderror
                </div>

                <!-- Contact Number -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Mobile Contact Number <span class="text-red-500">*</span></label>
                    <input type="text" name="client_phone" value="{{ old('client_phone') }}" required placeholder="09123456789"
                        pattern="^09\d{9}$"
                        maxlength="11"
                        title="Contact number must be an 11-digit mobile number starting with 09"
                        class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border @error('client_phone') border-red-500 dark:border-red-500 @else border-gray-300 dark:border-zinc-700 @enderror rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white">
                    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Must be an 11-digit mobile number starting with <span class="font-semibold text-gray-700 dark:text-gray-300">09</span></p>
                    @error('client_phone') <p class="mt-1 text-xs text-red-500 font-semibold flex items-center gap-1"><svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> {{ $message }}</p> @enderror
                </div>

                <!-- Office / Department -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Office / Department / Unit (Optional)</label>
                    <input type="text" name="office" value="{{ old('office') }}" placeholder="e.g. OSAS / College of Education"
                        class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white">
                    @error('office') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- SECTION 2: Request Details -->
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-6 shadow-xs">
            <div class="flex items-center gap-3 mb-5 border-b border-gray-100 dark:border-zinc-800 pb-3">
                <div class="w-8 h-8 rounded-lg bg-[#1a3c8f]/10 text-[#1a3c8f] dark:bg-blue-900/30 dark:text-blue-400 flex items-center justify-center font-bold text-sm">2</div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Service Request Details</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Specify the nature and location of the maintenance requirement.</p>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Category Select -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Service Category <span class="text-red-500">*</span></label>
                    <select name="category_id" x-ref="categorySelect" required
                        @change="
                            selectedCategoryId = $event.target.value;
                            const opt = $event.target.options[$event.target.selectedIndex];
                            selectedCategoryName = opt ? opt.text : '';
                            selectedConcern = '';
                            customConcern = '';
                        "
                        class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white">
                        <option value="" disabled selected>Select a Service Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->category_id }}" {{ (isset($preselectedCatId) && $preselectedCatId == $cat->category_id) ? 'selected' : '' }}>
                                {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Concern / Title -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Title of Concern <span class="text-red-500">*</span></label>
                    
                    <select x-model="selectedConcern"
                            :disabled="!selectedCategoryName"
                            class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white disabled:bg-gray-100 dark:disabled:bg-zinc-800/50 disabled:text-gray-400 disabled:cursor-not-allowed" 
                            required>
                        <option value="" disabled selected x-text="selectedCategoryName ? 'Select a concern for ' + selectedCategoryName : 'Please select a Service Category first'"></option>
                        <template x-for="concern in availableConcerns" :key="concern">
                            <option :value="concern" x-text="concern"></option>
                        </template>
                    </select>

                    <!-- Custom Concern Input if 'Other' is selected -->
                    <div x-show="selectedConcern && selectedConcern.includes('Other')" x-cloak class="mt-2">
                        <input type="text" 
                               x-model="customConcern" 
                               placeholder="Please specify your concern / title" 
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white"
                               :required="selectedConcern && selectedConcern.includes('Other')">
                    </div>

                    <!-- Hidden Input submitting final title -->
                    <input type="hidden" name="title" :value="finalTitle">
                    @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Campus & Location Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Campus -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Campus <span class="text-red-500">*</span></label>
                        <select name="campus" 
                                x-model="selectedCampus"
                                @change="selectedLocation = ''; customLocation = '';"
                                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white" 
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
                        @error('campus') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <!-- Location / Office Dropdown -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Office / Location <span class="text-red-500">*</span></label>
                        
                        <select x-model="selectedLocation"
                                :disabled="!selectedCampus"
                                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white disabled:bg-gray-100 dark:disabled:bg-zinc-800/50 disabled:text-gray-400 disabled:cursor-not-allowed" 
                                required>
                            <option value="" disabled selected x-text="selectedCampus ? 'Select office / building in ' + selectedCampus : 'Please select a Campus first'"></option>
                            <template x-for="loc in availableLocations" :key="loc">
                                <option :value="loc" x-text="loc"></option>
                            </template>
                        </select>

                        <!-- Custom Location Input if 'Other' is selected -->
                        <div x-show="selectedLocation && selectedLocation.includes('Other')" x-cloak class="mt-2">
                            <input type="text" 
                                   x-model="customLocation" 
                                   placeholder="Please specify specific office, room number, or area" 
                                   class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white"
                                   :required="selectedLocation && selectedLocation.includes('Other')">
                        </div>

                        <!-- Hidden Input submitting final location -->
                        <input type="hidden" name="location" :value="finalLocation">
                        @error('location') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Hidden Complexity & Urgency (default low) -->
                <input type="hidden" name="complexity" value="low">
                <input type="hidden" name="urgency" value="low">

                <!-- Description -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Detailed Description</label>
                    <textarea name="description" rows="4" placeholder="Provide additional details regarding the issue (e.g. room number, exact problem symptoms)..."
                        class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white">{{ old('description') }}</textarea>
                    @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Attachment -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Attachment (Photo / PDF Document)</label>
                    <div class="border-2 border-dashed border-gray-300 dark:border-zinc-700 rounded-lg p-4 text-center bg-gray-50/50 dark:bg-zinc-800/30 hover:bg-gray-100/50 dark:hover:bg-zinc-800/60 transition">
                        <input type="file" name="attachment" id="attachment" @change="handleFileSelect($event)" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                        <label for="attachment" class="cursor-pointer flex flex-col items-center">
                            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            <span class="text-sm font-semibold text-[#1a3c8f] dark:text-blue-400">Click to upload attachment</span>
                            <span class="text-xs text-gray-400 mt-1">PDF, PNG, JPG up to 5MB</span>
                        </label>
                        <template x-if="fileName">
                            <div class="mt-3 p-2 bg-blue-50 dark:bg-blue-900/20 rounded-md text-xs text-[#1a3c8f] dark:text-blue-300 font-medium inline-flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span x-text="fileName"></span> (<span x-text="fileSizeFormatted"></span>)
                            </div>
                        </template>
                    </div>
                    @error('attachment') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.requests.index') }}" class="px-5 py-2.5 border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-800 rounded-lg text-sm font-semibold transition">
                Cancel
            </a>
            <button type="submit" :disabled="submitting"
                class="px-6 py-2.5 bg-[#1a3c8f] hover:bg-[#152e6e] text-white rounded-lg text-sm font-semibold shadow-md transition flex items-center gap-2 disabled:opacity-50">
                <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="submitting ? 'Submitting Request...' : 'Submit Walk-In Request'"></span>
            </button>
        </div>

    </form>
</div>
@endsection
