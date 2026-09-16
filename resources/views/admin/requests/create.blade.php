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

    <form action="{{ route('admin.requests.store') }}" method="POST" enctype="multipart/form-data" 
        x-data="adminRequestForm({ preselectedCatId: '{{ $preselectedCatId ?? '' }}', oldCampus: '{{ old('campus', '') }}' })"
        @submit.prevent="validateManpowerForm($event)" class="space-y-6">
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

                    <!-- Dynamic Title of Activity Box (Appears when Event / Manpower is chosen) -->
                    <div x-show="isEventConcern" x-cloak class="mt-3 p-4 bg-blue-50/70 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#1a3c8f] dark:text-blue-300 mb-1.5">
                            Title of the Activity / Event Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               x-model="activityTitle" 
                               name="activity_title" 
                               placeholder="e.g. 56th Commencement Exercises, University Intramurals, General Assembly" 
                               class="w-full px-3.5 py-2 bg-white dark:bg-zinc-800 border border-blue-200 dark:border-zinc-700 rounded-lg text-sm text-gray-900 dark:text-white font-medium focus:ring-2 focus:ring-[#1a3c8f]"
                               :required="isEventConcern">
                        <p class="text-[11px] text-blue-600 dark:text-blue-400 mt-1">Specify the exact event title for the official Manpower Request Form.</p>
                    </div>

                    <!-- Custom Concern Input if 'Other' is selected -->
                    <div x-show="selectedConcern && selectedConcern.includes('Other') && !isManpowerCategory" x-cloak class="mt-2">
                        <input type="text" 
                               x-model="customConcern" 
                               placeholder="Please specify your concern / title" 
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white"
                               :required="selectedConcern && selectedConcern.includes('Other') && !isManpowerCategory">
                    </div>

                    <!-- Hidden Input submitting final title -->
                    <input type="hidden" name="title" :value="finalTitle">
                    @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- College/Campus Unit, Office & Specific Room Details -->
                <div class="space-y-4">
                    <!-- Row 1: College / Campus Unit & Cluster (College bigger, Cluster smaller) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Column 1: College / Campus Unit (2/3 width) -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                                College / Unit <span class="text-red-500">*</span>
                            </label>
                            <select x-model="selectedCollege"
                                    @change="onCollegeChange()"
                                    class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white" 
                                    required>
                                <option value="" disabled selected>Select College</option>
                                <template x-for="unit in collegeUnits" :key="unit.college">
                                    <option :value="unit.college" x-text="unit.college"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Column 2: Campus / Cluster (1/3 width, Auto-populated from selected college) -->
                        <div class="md:col-span-1">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                                Campus / Cluster <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text"
                                       :value="selectedCluster || ''"
                                       readonly
                                       class="w-full px-3.5 py-2.5 bg-gray-100 dark:bg-zinc-800/50 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 cursor-not-allowed select-none">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            <input type="hidden" name="campus" :value="selectedCluster">
                            @error('campus') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Row 2: Office/Dept & Specific Room share a row (2 columns) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Column 1: Office / Department Dropdown -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                                Office / Department <span class="text-red-500">*</span>
                            </label>
                            <select x-model="selectedOffice"
                                    :disabled="!selectedCollege"
                                    class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white disabled:bg-gray-100 dark:disabled:bg-zinc-800/50 disabled:text-gray-400 disabled:cursor-not-allowed" 
                                    required>
                                <option value="" disabled selected x-text="selectedCollege ? 'Select Office / Department' : 'Select College / Unit first'"></option>
                                <template x-for="office in availableOffices" :key="office">
                                    <option :value="office" x-text="office"></option>
                                </template>
                            </select>

                            <!-- Custom Office Input if 'Other' is selected -->
                            <div x-show="selectedOffice && selectedOffice.includes('Other')" x-cloak class="mt-2">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Specify Office / Facility Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="customOffice" 
                                       placeholder="Specify office, department, or unit name" 
                                       class="w-full px-3.5 py-2 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white"
                                       :required="selectedOffice && selectedOffice.includes('Other')">
                            </div>
                        </div>

                        <!-- Column 2: Specific Room / Location (Optional) -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                                Building / Room No. <span class="text-xs font-normal text-gray-400 lowercase">(optional)</span>
                            </label>
                            <input type="text" 
                                   x-model="specificLocation" 
                                   placeholder="e.g. Room 204, 2nd Floor, Left Wing / Chemistry Lab 1 / Faculty Office Cubicle 3" 
                                   class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm focus:ring-2 focus:ring-[#1a3c8f] focus:border-transparent dark:text-white">
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Please enter the specific room number, floor, or landmark where work will take place (optional).</p>
                        </div>
                    </div>

                    <!-- Formatted Location Preview Pill -->
                    <div x-show="finalLocation" x-cloak class="p-3 bg-blue-50/90 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/80 rounded-lg text-xs text-blue-800 dark:text-blue-300 flex items-start gap-2">
                        <svg class="w-4 h-4 shrink-0 text-[#1a3c8f] dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <div>
                            <span class="font-bold uppercase tracking-wider text-[10px] text-blue-600 dark:text-blue-400 block mb-0.5">Recorded Location</span>
                            <span class="font-medium" x-text="finalLocation"></span>
                        </div>
                    </div>

                    <!-- Hidden Input submitting final location -->
                    <input type="hidden" name="location" :value="finalLocation">
                    @error('location') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Hidden Complexity & Urgency (default low) -->
                <input type="hidden" name="complexity" value="low">
                <input type="hidden" name="urgency" value="low">

                <!-- CONDITIONAL: MANPOWER WORK DETAILS & SCHEDULE -->
                <div x-show="isManpowerCategory" x-cloak class="space-y-3 pt-2">
                    <div class="p-3 bg-blue-900 text-white rounded-lg text-xs font-bold uppercase tracking-wider">
                        Work Details To Be Completed For The Event
                    </div>

                    <!-- 1. Preparation Activity -->
                    <div class="p-3.5 bg-gray-50 dark:bg-zinc-800/60 border border-gray-200 dark:border-zinc-700 rounded-lg space-y-2">
                        <label class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase">1. Preparation Activity</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-0.5">From Date</label>
                                <input type="date" x-model="prepDateFrom" @change="if(!prepDateTo) prepDateTo = prepDateFrom"
                                       class="w-full px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-0.5">To Date</label>
                                <input type="date" x-model="prepDateTo" :min="prepDateFrom"
                                       class="w-full px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs dark:text-white">
                            </div>
                        </div>
                        <input type="hidden" name="prep_date" :value="prepDate">
                        <textarea x-model="prepDetails" name="prep_details" rows="2" placeholder="Describe preparation tasks and venue requirements" class="w-full p-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs dark:text-white"></textarea>
                        <div class="flex flex-wrap items-center gap-3 text-xs">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" x-model="prepRegular" name="prep_regular" value="1" class="rounded text-[#1a3c8f]">
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Regular:</span>
                            </label>
                            <div class="flex items-center gap-2" x-show="prepRegular">
                                <select x-model="prepTimePreset" @change="applyTimePreset('prep')"
                                        class="px-2 py-0.5 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 dark:text-white">
                                    <template x-for="p in timePresets" :key="p.value">
                                        <option :value="p.value" x-text="p.label"></option>
                                    </template>
                                </select>
                                <input type="text" x-model="prepRegularTime" x-show="prepTimePreset === 'custom'" name="prep_regular_time"
                                       placeholder="e.g. 10:00 AM - 3:00 PM" class="px-2 py-0.5 border border-gray-300 dark:border-zinc-700 rounded text-xs w-32 bg-white dark:bg-zinc-800 dark:text-white">
                                <span x-show="prepTimePreset !== 'custom'" class="font-medium text-gray-700 dark:text-gray-300 text-xs" x-text="prepRegularTime"></span>
                                <input type="hidden" name="prep_regular_time" :value="prepRegularTime">
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" x-model="prepOvertime" name="prep_overtime" value="1" class="rounded text-[#1a3c8f]">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">Overtime:</span>
                                </label>
                                <div class="flex flex-col">
                                    <input type="text" x-model="prepOvertimeTime" name="prep_overtime_time"
                                           placeholder="e.g. 5:00PM-6:00PM"
                                           :class="{'border-red-500 focus:border-red-500 ring-1 ring-red-500 bg-red-50/40 dark:bg-red-950/30': prepOvertime && prepOvertimeTime && !isValidTimeFormat(prepOvertimeTime)}"
                                           class="px-2 py-0.5 border border-gray-300 dark:border-zinc-700 rounded text-xs w-36 bg-white dark:bg-zinc-800 dark:text-white"
                                           :disabled="!prepOvertime">
                                    <span x-show="prepOvertime && prepOvertimeTime && !isValidTimeFormat(prepOvertimeTime)" x-cloak class="text-[10px] text-red-600 dark:text-red-400 font-semibold mt-0.5">Format: 5:00PM-6:00PM</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Assistance During Event -->
                    <div class="p-3.5 bg-gray-50 dark:bg-zinc-800/60 border border-gray-200 dark:border-zinc-700 rounded-lg space-y-2">
                        <label class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase">2. Assistance During The Event</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-0.5">From Date</label>
                                <input type="date" x-model="assistanceDateFrom" @change="if(!assistanceDateTo) assistanceDateTo = assistanceDateFrom"
                                       class="w-full px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-0.5">To Date</label>
                                <input type="date" x-model="assistanceDateTo" :min="assistanceDateFrom"
                                       class="w-full px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs dark:text-white">
                            </div>
                        </div>
                        <input type="hidden" name="assistance_date" :value="assistanceDate">
                        <textarea x-model="assistanceDetails" name="assistance_details" rows="2" placeholder="Describe event assistance tasks" class="w-full p-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs dark:text-white"></textarea>
                        <div class="flex flex-wrap items-center gap-3 text-xs">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" x-model="assistanceRegular" name="assistance_regular" value="1" class="rounded text-[#1a3c8f]">
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Regular:</span>
                            </label>
                            <div class="flex items-center gap-2" x-show="assistanceRegular">
                                <select x-model="assistanceTimePreset" @change="applyTimePreset('assistance')"
                                        class="px-2 py-0.5 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 dark:text-white">
                                    <template x-for="p in timePresets" :key="p.value">
                                        <option :value="p.value" x-text="p.label"></option>
                                    </template>
                                </select>
                                <input type="text" x-model="assistanceRegularTime" x-show="assistanceTimePreset === 'custom'" name="assistance_regular_time"
                                       placeholder="e.g. 10:00 AM - 3:00 PM" class="px-2 py-0.5 border border-gray-300 dark:border-zinc-700 rounded text-xs w-32 bg-white dark:bg-zinc-800 dark:text-white">
                                <span x-show="assistanceTimePreset !== 'custom'" class="font-medium text-gray-700 dark:text-gray-300 text-xs" x-text="assistanceRegularTime"></span>
                                <input type="hidden" name="assistance_regular_time" :value="assistanceRegularTime">
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" x-model="assistanceOvertime" name="assistance_overtime" value="1" class="rounded text-[#1a3c8f]">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">Overtime:</span>
                                </label>
                                <div class="flex flex-col">
                                    <input type="text" x-model="assistanceOvertimeTime" name="assistance_overtime_time"
                                           placeholder="e.g. 5:00PM-10:00PM"
                                           :class="{'border-red-500 focus:border-red-500 ring-1 ring-red-500 bg-red-50/40 dark:bg-red-950/30': assistanceOvertime && assistanceOvertimeTime && !isValidTimeFormat(assistanceOvertimeTime)}"
                                           class="px-2 py-0.5 border border-gray-300 dark:border-zinc-700 rounded text-xs w-36 bg-white dark:bg-zinc-800 dark:text-white"
                                           :disabled="!assistanceOvertime">
                                    <span x-show="assistanceOvertime && assistanceOvertimeTime && !isValidTimeFormat(assistanceOvertimeTime)" x-cloak class="text-[10px] text-red-600 dark:text-red-400 font-semibold mt-0.5">Format: 5:00PM-6:00PM</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Clearing Upon Event -->
                    <div class="p-3.5 bg-gray-50 dark:bg-zinc-800/60 border border-gray-200 dark:border-zinc-700 rounded-lg space-y-2">
                        <label class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase">3. Clearing Upon The Event</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-0.5">From Date</label>
                                <input type="date" x-model="clearingDateFrom" @change="if(!clearingDateTo) clearingDateTo = clearingDateFrom"
                                       class="w-full px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs dark:text-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase mb-0.5">To Date</label>
                                <input type="date" x-model="clearingDateTo" :min="clearingDateFrom"
                                       class="w-full px-2.5 py-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs dark:text-white">
                            </div>
                        </div>
                        <input type="hidden" name="clearing_date" :value="clearingDate">
                        <textarea x-model="clearingDetails" name="clearing_details" rows="2" placeholder="Describe clearing and post-event tasks" class="w-full p-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs dark:text-white"></textarea>
                        <div class="flex flex-wrap items-center gap-3 text-xs">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" x-model="clearingRegular" name="clearing_regular" value="1" class="rounded text-[#1a3c8f]">
                                <span class="font-semibold text-gray-700 dark:text-gray-300">Regular:</span>
                            </label>
                            <div class="flex items-center gap-2" x-show="clearingRegular">
                                <select x-model="clearingTimePreset" @change="applyTimePreset('clearing')"
                                        class="px-2 py-0.5 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 dark:text-white">
                                    <template x-for="p in timePresets" :key="p.value">
                                        <option :value="p.value" x-text="p.label"></option>
                                    </template>
                                </select>
                                <input type="text" x-model="clearingRegularTime" x-show="clearingTimePreset === 'custom'" name="clearing_regular_time"
                                       placeholder="e.g. 10:00 AM - 3:00 PM" class="px-2 py-0.5 border border-gray-300 dark:border-zinc-700 rounded text-xs w-32 bg-white dark:bg-zinc-800 dark:text-white">
                                <span x-show="clearingTimePreset !== 'custom'" class="font-medium text-gray-700 dark:text-gray-300 text-xs" x-text="clearingRegularTime"></span>
                                <input type="hidden" name="clearing_regular_time" :value="clearingRegularTime">
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" x-model="clearingOvertime" name="clearing_overtime" value="1" class="rounded text-[#1a3c8f]">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">Overtime:</span>
                                </label>
                                <div class="flex flex-col">
                                    <input type="text" x-model="clearingOvertimeTime" name="clearing_overtime_time"
                                           placeholder="e.g. 5:00PM-8:00PM"
                                           :class="{'border-red-500 focus:border-red-500 ring-1 ring-red-500 bg-red-50/40 dark:bg-red-950/30': clearingOvertime && clearingOvertimeTime && !isValidTimeFormat(clearingOvertimeTime)}"
                                           class="px-2 py-0.5 border border-gray-300 dark:border-zinc-700 rounded text-xs w-36 bg-white dark:bg-zinc-800 dark:text-white"
                                           :disabled="!clearingOvertime">
                                    <span x-show="clearingOvertime && clearingOvertimeTime && !isValidTimeFormat(clearingOvertimeTime)" x-cloak class="text-[10px] text-red-600 dark:text-red-400 font-semibold mt-0.5">Format: 5:00PM-6:00PM</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Additional Note -->
                    <div class="p-3.5 bg-gray-50 dark:bg-zinc-800/60 border border-gray-200 dark:border-zinc-700 rounded-lg space-y-2">
                        <label class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase">4. Additional Note (Supplies / Tools)</label>
                        <textarea x-model="additionalNotes" name="additional_notes" rows="2" placeholder="Specify required materials, equipment, or special instructions" class="w-full p-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded text-xs dark:text-white"></textarea>
                    </div>
                </div>

                <!-- Standard Description (Non-Manpower) -->
                <div x-show="!isManpowerCategory" x-cloak>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Detailed Description</label>
                    <textarea name="description" rows="4" placeholder="Provide details regarding the maintenance work required"
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
                            <div class="mt-3 p-2.5 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg text-xs text-[#1a3c8f] dark:text-blue-300 font-medium flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    <template x-if="isImage && filePreviewUrl">
                                        <img :src="filePreviewUrl" alt="Preview" @click="viewPreviewModal = true" class="w-10 h-10 object-cover rounded-md border border-blue-300 dark:border-blue-700 shrink-0 cursor-pointer hover:opacity-80 transition" title="Click to view full photo">
                                    </template>
                                    <template x-if="!isImage">
                                        <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </template>
                                    <div class="text-left truncate">
                                        <span class="font-bold block truncate" x-text="fileName"></span>
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400" x-text="fileSizeFormatted"></span>
                                    </div>
                                </div>
                                <template x-if="filePreviewUrl">
                                    <button type="button" @click="viewPreviewModal = true" class="px-3 py-1 bg-white dark:bg-zinc-800 border border-blue-300 dark:border-blue-700 text-[#1a3c8f] dark:text-blue-300 rounded-md text-xs font-bold hover:bg-blue-50 dark:hover:bg-zinc-700 transition shrink-0 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Preview
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                    @error('attachment') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                    <!-- Uploaded File Preview Modal (Popup) -->
                    <div x-show="viewPreviewModal" 
                         x-cloak 
                         class="fixed inset-0 bg-black/80 backdrop-blur-xs z-50 flex items-center justify-center p-4" 
                         @keydown.escape.window="viewPreviewModal = false">
                        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-3xl w-full max-h-[90vh] shadow-2xl relative flex flex-col border border-gray-200 dark:border-zinc-800 overflow-hidden" 
                             @click.away="viewPreviewModal = false">
                            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-200 dark:border-zinc-800 bg-gray-50/80 dark:bg-zinc-800/80">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-4 h-4 text-[#1a3c8f] dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
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
                            </div>
                        </div>
                    </div>
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
                <span x-text="submitting ? 'Submitting Request...' : 'Submit Walk-In Request'">Submit Walk-In Request</span>
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
function adminRequestForm(config = {}) {
    return {
        submitting: false,
        viewPreviewModal: false,
        fileName: '',
        fileSizeFormatted: '',
        filePreviewUrl: '',
        isImage: false,
        selectedCategoryId: config.preselectedCatId || '',
        selectedCategoryName: '',
        selectedCluster: config.oldCampus || '',
        selectedCollege: '',
        selectedOffice: '',
        customOffice: '',
        specificLocation: '',
        selectedConcern: '',
        customConcern: '',

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

        timePresets: [
            { value: 'morning',   label: 'Morning (8:00 AM – 12:00 PM)',    time: '8:00 - 12:00' },
            { value: 'afternoon', label: 'Afternoon (1:00 PM – 5:00 PM)',    time: '1:00 - 5:00' },
            { value: 'regular',   label: 'Regular (8:00 AM – 5:00 PM)',      time: '8:00 - 12:00 / 1:00 - 5:00' },
            { value: 'fullday',   label: 'Full Day (8:00 AM – 5:00 PM)',     time: '8:00 - 5:00' },
            { value: 'custom',    label: 'Custom…',                          time: '' },
        ],

        applyTimePreset(section) {
            const map = { prep: 'prepTimePreset', assistance: 'assistanceTimePreset', clearing: 'clearingTimePreset' };
            const timeMap = { prep: 'prepRegularTime', assistance: 'assistanceRegularTime', clearing: 'clearingRegularTime' };
            const preset = this.timePresets.find(p => p.value === this[map[section]]);
            if (preset && preset.value !== 'custom') this[timeMap[section]] = preset.time;
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
            this.$nextTick(() => {
                const selectEl = this.$refs.categorySelect;
                if (selectEl) {
                    if (this.selectedCategoryId) {
                        selectEl.value = this.selectedCategoryId;
                    }
                    if (selectEl.selectedIndex >= 0) {
                        const selectedOpt = selectEl.options[selectEl.selectedIndex];
                        if (selectedOpt && selectEl.value) {
                            this.selectedCategoryName = selectedOpt.text;
                        }
                    }
                }
            });
        },

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
            'Painting': [
                'Wall / Ceiling Repainting',
                'Exterior Facade Repainting',
                'Door / Window Repainting',
                'Gate / Fence Repainting',
                'Other Painting Concern'
            ],
            'Air Conditioning': [
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
            'Janitorial and Manpower': [
                'Event & Activity Venue Setup',
                'Heavy Equipment & Furniture Relocation',
                'Hauling & Waste Disposal Assistance',
                'Deep Cleaning & Disinfection Service',
                'Waste Management & Garbage Collection',
                'Restroom Sanitation & Supplies Check',
                'Other manpower service',
                'Other janitorial service'
            ]
        },

        // Combined College & Campus Units: (Cluster) College Name
        collegeUnits: [
            {
                cluster: 'Main',
                label: 'GASS & Auxiliary Services',
                college: 'GASS & Auxiliary Services',
                offices: [
                    'Office of the University President',
                    'Office of the Vice President for Academic Affairs (OVPAA)',
                    'Office of the Vice President for Administration and Finance (OVPAF)',
                    'Office of the Vice President for Research, Development and Extension (OVPRDE)',
                    'Office of the Vice President for Planning and Development (OVPPD)',
                    'General Services Office (GSO)',
                    'University Registrar\'s Office',
                    'Cashier\'s Office & Accounting Office',
                    'Human Resource Development Office (HRDO)',
                    'Supply and Property Management Office (SPMO)',
                    'Information & Communications Technology Office (ICTO)',
                    'University Health Services / Clinic',
                    'University Main Library & Audio-Visual Hall',
                    'Office of Student Affairs and Services (OSAS)',
                    'University Student Center (USC)',
                    'University Gymnasium & Sports Complex',
                    'BUCFAO / Auxiliary Services Office',
                    'Other Office / Facility'
                ]
            },
            {
                cluster: 'Cluster 1',
                label: 'BUCE (College of Education)',
                college: 'BUCE (College of Education)',
                offices: [
                    'Dean\'s Office & Administrative Staff',
                    'Elementary Dept / Integrated Lab School (ILS-Elem)',
                    'High School Dept / Integrated Lab School (ILS-HS)',
                    'Dept of Elementary Education (BEED)',
                    'Dept of Secondary Education (BSED)',
                    'Science & Mathematics Education Unit',
                    'Educational Media & Audio-Visual Room (AVR)',
                    'Reading Clinic & Learning Resource Center',
                    'Guidance & Counseling Office',
                    'Faculty Offices & Consultation Rooms',
                    'Other Office (BUCE)'
                ]
            },
            {
                cluster: 'Cluster 1',
                label: 'BUCM (College of Medicine)',
                college: 'BUCM (College of Medicine)',
                offices: [
                    'Dean\'s Office & College Secretary',
                    'Basic Medical Sciences Department',
                    'Clinical Skills Simulation Laboratory',
                    'Gross Anatomy & Dissection Laboratory',
                    'Histology & Pathology Laboratory',
                    'Physiology & Pharmacology Laboratory',
                    'Medical Amphitheater & Lecture Halls',
                    'Medical Library & Learning Hub',
                    'Faculty Consultation Room',
                    'Other Office (BUCM)'
                ]
            },
            {
                cluster: 'Cluster 1',
                label: 'IPESR (Institute of Physical Education, Sports and Recreation)',
                college: 'IPESR (Institute of Physical Education, Sports and Recreation)',
                offices: [
                    'Director\'s Office & Administration',
                    'Physical Education Department',
                    'Sports Development & Athletic Office',
                    'University Gymnasium & Main Court',
                    'Fitness & Weight Training Gym',
                    'Dance Studio & Aerobics Hall',
                    'Equipment & Supplies Custodian Room',
                    'Swimming Pool Complex & Locker Rooms',
                    'Other Office (IPESR)'
                ]
            },
            {
                cluster: 'Cluster 2',
                label: 'CS (College of Science)',
                college: 'CS (College of Science)',
                offices: [
                    'Dean\'s Office & Administrative Staff',
                    'Biology Department & Laboratories',
                    'Chemistry Department & Laboratories',
                    'Physics Department & Laboratories',
                    'Computer Science & IT Department (CSIT)',
                    'Mathematics & Statistics Department',
                    'Science Research & Science Resource Center',
                    'Faculty Offices & Consultation Rooms',
                    'Other Office (CS)'
                ]
            },
            {
                cluster: 'Cluster 2',
                label: 'BUCN (College of Nursing)',
                college: 'BUCN (College of Nursing)',
                offices: [
                    'Dean\'s Office & College Secretary',
                    'Nursing Arts Laboratory (NAL)',
                    'Maternal & Child Health Laboratory',
                    'Medical-Surgical Skills Laboratory',
                    'Community Health Nursing Unit',
                    'Faculty Room & Student Consultation Area',
                    'Other Office (BUCN)'
                ]
            },
            {
                cluster: 'Cluster 2',
                label: 'CENG (College of Engineering)',
                college: 'CENG (College of Engineering)',
                offices: [
                    'Dean\'s Office & College Secretary',
                    'Civil Engineering Department',
                    'Electrical Engineering Department',
                    'Mechanical Engineering Department',
                    'Chemical Engineering Department',
                    'Geodetic Engineering Department',
                    'Mining Engineering Department',
                    'Materials Testing Laboratory',
                    'CAD & Computing Laboratory',
                    'Engineering Machine Shop',
                    'Other Office (CENG)'
                ]
            },
            {
                cluster: 'Cluster 3',
                label: 'CAL (College of Arts and Letters)',
                college: 'CAL (College of Arts and Letters)',
                offices: [
                    'Dean\'s Office & Administrative Staff',
                    'English & Applied Linguistics Department',
                    'Literature & Performing Arts Department',
                    'Humanities & Philosophy Department',
                    'Journalism & Communication Department',
                    'Speech & Multimedia Broadcasting Laboratory',
                    'Amphitheater & Audio-Visual Room',
                    'Other Office (CAL)'
                ]
            },
            {
                cluster: 'Cluster 3',
                label: 'CIT (College of Industrial Technology)',
                college: 'CIT (College of Industrial Technology)',
                offices: [
                    'Dean\'s Office & Administrative Staff',
                    'Automotive Technology Shop',
                    'Electrical Technology Shop',
                    'Electronics & Computer Technology Shop',
                    'Mechanical & Fabrication Shop',
                    'Drafting & Civil Technology Lab',
                    'Food and Garments Technology Lab',
                    'Other Office (CIT)'
                ]
            },
            {
                cluster: 'Cluster 3',
                label: 'CBPA (College of Business and Public Administration)',
                college: 'CBPA (College of Business and Public Administration)',
                offices: [
                    'Dean\'s Office & College Secretary',
                    'Public Administration Department',
                    'Business Administration Department',
                    'Student Services & Consultation Area',
                    'Other Office (CBPA)'
                ]
            },
            {
                cluster: 'Cluster 4',
                label: 'BUIDeA (Institute of Design and Architecture)',
                college: 'BUIDeA (Institute of Design and Architecture)',
                offices: [
                    'Director\'s Office & Administrative Staff',
                    'Architecture Design Studios (1 to 4)',
                    'Building Science & Materials Laboratory',
                    'Digital Drafting & 3D Modeling Laboratory',
                    'Faculty Consultation Room & Archives',
                    'Other Office (BUIDeA)'
                ]
            },
            {
                cluster: 'Cluster 4',
                label: 'Graduate School (BUGS)',
                college: 'Graduate School (BUGS)',
                offices: [
                    'Dean\'s Office & Graduate Secretary',
                    'Doctoral Programs Unit',
                    'Masteral Programs Unit',
                    'Research, Statistics & Defense Room',
                    'Graduate Student Lounge & Seminar Room',
                    'Other Office (BUGS)'
                ]
            },
            {
                cluster: 'Cluster 4',
                label: 'East Campus Facilities (ESC)',
                college: 'East Campus Facilities (ESC)',
                offices: [
                    'East Campus Admin & Property Custodian',
                    'East Campus General Library',
                    'East Campus Student Center & Canteen',
                    'Multi-Purpose Hall & Audio-Visual Room',
                    'Security & Maintenance Quarters',
                    'Other Office (ESC)'
                ]
            },
            {
                cluster: 'Cluster 4',
                label: 'RDC (Research and Development Center)',
                college: 'RDC (Research and Development Center)',
                offices: [
                    'Director\'s Office & Research Services',
                    'Intellectual Property / ITSO Office',
                    'Central Analytical Testing Laboratory',
                    'Extension & Community Engagement Office',
                    'Publications & Journal Editorial Office',
                    'Other Office (RDC)'
                ]
            },
            {
                cluster: 'Cluster Daraga',
                label: 'CSSP (College of Social Sciences and Philosophy)',
                college: 'CSSP (College of Social Sciences and Philosophy)',
                offices: [
                    'Dean\'s Office & College Secretary',
                    'Department of Political Science & Public Affairs',
                    'Department of Sociology & Anthropology',
                    'Department of Psychology',
                    'Department of Philosophy',
                    'Department of Peace Studies',
                    'Psychological Testing & Assessment Laboratory',
                    'Social Science Research & Audio-Visual Hall',
                    'Guidance, Career & Counseling Office',
                    'Other Office (CSSP)'
                ]
            },
            {
                cluster: 'Cluster Daraga',
                label: 'CBEM (College of Business, Economics and Management)',
                college: 'CBEM (College of Business, Economics and Management)',
                offices: [
                    'Dean\'s Office & College Secretary',
                    'Department of Accountancy',
                    'Department of Business Admin & Marketing',
                    'Department of Economics',
                    'Department of Entrepreneurship',
                    'Center for Entrepreneurship & Business Incubator',
                    'Accounting Simulation Computer Lab',
                    'CBEM Auditorium & Multi-Purpose Center',
                    'Student Organization & Activity Center',
                    'Other Office (CBEM)'
                ]
            },
            {
                cluster: 'Guinobatan',
                label: 'BUCAF (College of Agriculture and Forestry)',
                college: 'BUCAF (College of Agriculture and Forestry)',
                offices: [
                    'Dean\'s Office & Campus Administration',
                    'Department of Agricultural Sciences',
                    'Department of Animal Science & Veterinary Clinic',
                    'Department of Forestry & Agroforestry',
                    'Department of Agricultural and Biosystems Eng',
                    'Crop Science & Tissue Culture Laboratory',
                    'Soil Science & Agricultural Chemistry Lab',
                    'Farm Machinery Shop & Demo Farm Office',
                    'BUCAF Campus Library & Auditorium',
                    'Other Office (BUCAF)'
                ]
            },
            {
                cluster: 'Polangui',
                label: 'BUPC (Polangui Campus)',
                college: 'BUPC (Polangui Campus)',
                offices: [
                    'Campus Director\'s Office & Administration',
                    'Department of Information Technology & CS',
                    'Department of Computer Engineering',
                    'Department of Nursing and Health Sciences',
                    'Department of Teacher Education',
                    'Department of Automotive & Mechanical Technology',
                    'Computer Laboratories (1 to 4)',
                    'Health Skills Laboratory & Clinic',
                    'Polangui Campus Library & Student Center',
                    'Other Office (BUPC)'
                ]
            },
            {
                cluster: 'Tabaco',
                label: 'BUTC (Tabaco Campus)',
                college: 'BUTC (Tabaco Campus)',
                offices: [
                    'Campus Director\'s Office & Administration',
                    'Department of Fisheries & Marine Sciences',
                    'Department of Business Admin & Entrepreneurship',
                    'Department of Teacher Education',
                    'Aquaculture Hatchery & Wet Laboratories',
                    'Post-Harvest & Food Processing Laboratory',
                    'Oceanography & Marine Biology Lab',
                    'Tabaco Campus Library & Learning Hub',
                    'Other Office (BUTC)'
                ]
            },
            {
                cluster: 'Gubat',
                label: 'BUGC (Gubat Campus)',
                college: 'BUGC (Gubat Campus)',
                offices: [
                    'Campus Director\'s Office & Administration',
                    'Department of Teacher Education',
                    'Department of Business Administration',
                    'Department of Information & Computing Sciences',
                    'Department of Agricultural Technology',
                    'Computer Laboratory & Multimedia Center',
                    'Campus Library & Audio-Visual Room',
                    'Student Services & Guidance Office',
                    'Other Office (BUGC)'
                ]
            }
        ],

        onCollegeChange() {
            const found = this.collegeUnits.find(u => u.college === this.selectedCollege);
            this.selectedCluster = found ? found.cluster : '';
            this.selectedOffice = '';
            this.customOffice = '';
        },

        get selectedCampus() {
            return this.selectedCluster;
        },

        get availableOffices() {
            if (!this.selectedCollege) return [];
            const found = this.collegeUnits.find(u => u.college === this.selectedCollege);
            return found ? found.offices : [];
        },

        get availableConcerns() {
            if (!this.selectedCategoryName) return [];
            const catLower = this.selectedCategoryName.toLowerCase();
            const matchKey = Object.keys(this.concernsMap).find(k => catLower.includes(k.toLowerCase()));
            return matchKey ? this.concernsMap[matchKey] : [
                'General Repair & Maintenance Request',
                'Equipment Repair Request',
                'Facility Inspection Request',
                'Other Concern'
            ];
        },

        get isManpowerCategory() {
            const cat = (this.selectedCategoryName || '').toLowerCase();
            if (!cat.includes('manpower') && !cat.includes('janitor')) return false;
            const c = (this.selectedConcern || '').toLowerCase();
            return c.includes('event') || c.includes('relocation') || c.includes('hauling') || c.includes('manpower');
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
            if (!this.selectedCollege || !this.selectedOffice) return '';
            const officeName = (this.selectedOffice.includes('Other') && this.customOffice)
                ? this.customOffice.trim()
                : this.selectedOffice;
            const roomPart = this.specificLocation && this.specificLocation.trim() 
                ? ` (${this.specificLocation.trim()})` 
                : '';
            return `${this.selectedCollege} — ${officeName}${roomPart}`;
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.fileName = file.name;
                this.fileSizeFormatted = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                this.isImage = file.type.startsWith('image/') || /\.(jpe?g|png|webp|gif)$/i.test(file.name);
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.filePreviewUrl = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        isValidTimeFormat(val) {
            if (!val || typeof val !== 'string') return false;
            const v = val.trim();
            const timePattern = /^(?:(0?[1-9]|1[0-2]):[0-5][0-9]\s*(?:AM|PM|am|pm)\s*(?:-|–|to)\s*(0?[1-9]|1[0-2]):[0-5][0-9]\s*(?:AM|PM|am|pm)|(0?[1-9]|1[0-2]):[0-5][0-9]\s*(?:AM|PM|am|pm)\s*onwards)$/i;
            return timePattern.test(v);
        },

        validateManpowerForm(event) {
            if (this.prepOvertime) {
                if (!this.prepOvertimeTime || !this.isValidTimeFormat(this.prepOvertimeTime)) {
                    alert('Please enter a valid Preparation Overtime time (e.g. 5:00PM-6:00PM or 5:00 PM - 8:00 PM).');
                    const el = document.querySelector('input[name="prep_overtime_time"]');
                    if (el) el.focus();
                    return false;
                }
            }
            if (this.assistanceOvertime) {
                if (!this.assistanceOvertimeTime || !this.isValidTimeFormat(this.assistanceOvertimeTime)) {
                    alert('Please enter a valid Event Assistance Overtime time (e.g. 5:00PM-6:00PM or 5:00 PM - 10:00 PM).');
                    const el = document.querySelector('input[name="assistance_overtime_time"]');
                    if (el) el.focus();
                    return false;
                }
            }
            if (this.clearingOvertime) {
                if (!this.clearingOvertimeTime || !this.isValidTimeFormat(this.clearingOvertimeTime)) {
                    alert('Please enter a valid Clearing Overtime time (e.g. 5:00PM-6:00PM or 5:00 PM - 8:00 PM).');
                    const el = document.querySelector('input[name="clearing_overtime_time"]');
                    if (el) el.focus();
                    return false;
                }
            }
            this.submitting = true;
            event.target.submit();
        }
    };
}
</script>
@endpush
@endsection
