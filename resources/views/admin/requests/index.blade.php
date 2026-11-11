@extends('layouts.admin')
@section('page-title', 'Service Requests')

@push('styles')
<style>
    .custom-modal-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 51, 160, 0.35) transparent;
    }
    .dark .custom-modal-scroll {
        scrollbar-color: rgba(96, 165, 250, 0.35) transparent;
    }
    .custom-modal-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-modal-scroll::-webkit-scrollbar-track {
        background: transparent;
        margin: 6px 0;
    }
    .custom-modal-scroll::-webkit-scrollbar-thumb {
        background-color: rgba(0, 51, 160, 0.28);
        border-radius: 9999px;
    }
    .custom-modal-scroll::-webkit-scrollbar-thumb:hover {
        background-color: rgba(0, 51, 160, 0.6);
    }
    .dark .custom-modal-scroll::-webkit-scrollbar-thumb {
        background-color: rgba(96, 165, 250, 0.28);
    }
    .dark .custom-modal-scroll::-webkit-scrollbar-thumb:hover {
        background-color: rgba(96, 165, 250, 0.55);
    }
</style>
@endpush

@section('content')

<div x-data="formOptionsManager({{ Js::from($formOptions ?? \App\Http\Controllers\Admin\RequestController::getDefaultFormOptions()) }})">

    <!-- Page Banner Header -->
    <div data-realtime-container="admin-requests-header" class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-5 sm:px-8 py-5 sm:py-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 shadow-sm">
        <div>
            <h1 class="text-[#0033a0] dark:text-blue-400 text-xl sm:text-2xl font-bold mb-1">Service Requests</h1>
            <p class="text-[#0033a0]/80 dark:text-gray-300 text-xs sm:text-sm font-medium">Track, monitor, and manage university maintenance requisitions.</p>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto flex-wrap">
            @php
                $pendingBomCount = \App\Models\BillOfMaterials::whereNull('date_approved')->count();
            @endphp
            <a href="{{ route('admin.bom.index') }}" class="w-full sm:w-auto text-center bg-white dark:bg-zinc-800 hover:bg-blue-50 dark:hover:bg-zinc-700 text-[#0033a0] dark:text-blue-400 border-2 border-[#0033a0]/30 dark:border-blue-600/50 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition shadow-xs inline-flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>List of Materials</span>
                @if($pendingBomCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-white animate-pulse">
                        {{ $pendingBomCount }}
                    </span>
                @endif
            </a>

            <!-- Split Button: + Add New Request + Down Arrow Dropdown -->
            <div class="relative inline-flex w-full sm:w-auto rounded-xl shadow-sm" @click.outside="dropdownOpen = false">
                <a href="{{ route('admin.requests.create') }}"
                   class="flex-1 sm:flex-initial text-center bg-[#0033a0] hover:bg-[#002480] text-white pl-5 pr-4 py-2.5 rounded-l-xl text-xs sm:text-sm font-bold transition inline-flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add New Request</span>
                </a>
                <button type="button"
                        @click="dropdownOpen = !dropdownOpen"
                        aria-label="More request options"
                        class="bg-[#0033a0] hover:bg-[#002480] text-white px-3 py-2.5 rounded-r-xl border-l border-white/25 text-xs sm:text-sm font-bold transition inline-flex items-center justify-center cursor-pointer focus:outline-none">
                    <svg class="w-4 h-4 transition-transform duration-150" :class="dropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="dropdownOpen"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                     x-cloak
                     class="absolute right-0 top-full mt-2 w-56 bg-white dark:bg-[#18181b] border border-gray-200 dark:border-zinc-800 rounded-xl shadow-xl overflow-hidden z-50">
                    <button type="button"
                            @click="dropdownOpen = false; openModal()"
                            class="w-full px-4 py-3 text-left text-xs sm:text-sm font-bold text-slate-800 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-zinc-800 flex items-center gap-2.5 transition cursor-pointer">
                        <svg class="w-4 h-4 text-[#0033a0] dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        <span>Manage Form Options</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Form Options Modal -->
    <template x-teleport="body">
        <div x-show="modalOpen"
             x-cloak
             class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             x-transition.opacity>
            <div @click.outside="modalOpen = false"
                 style="max-width: 52rem;"
                 class="w-full max-w-[52rem] bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[88vh]">

                <!-- Modal Header -->
                <div class="px-6 py-4 bg-[#fffde7] dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-800 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-[#0033a0] dark:text-blue-400">Manage Request Form Dropdown Options</h3>
                        <p class="text-xs text-slate-600 dark:text-gray-400">Add or remove dropdown choices in Requesition Form</p>
                    </div>
                    <button type="button" @click="modalOpen = false" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-zinc-800 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Tabs Navigation -->
                <div class="px-6 pt-3 bg-gray-50 dark:bg-zinc-900/60 border-b border-gray-200 dark:border-zinc-800 grid grid-cols-2 sm:grid-cols-4 gap-2 shrink-0">
                    <button type="button"
                            @click="activeTab = 'concerns'"
                            :class="activeTab === 'concerns' ? 'border-[#0033a0] text-[#0033a0] dark:border-blue-400 dark:text-blue-400 bg-white dark:bg-[#1c1c1e]' : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400'"
                            class="w-full flex items-center justify-center px-3 py-2.5 text-xs sm:text-sm font-bold border-b-2 rounded-t-lg transition cursor-pointer whitespace-nowrap text-center">
                        Title of Concerns
                    </button>
                    <button type="button"
                            @click="activeTab = 'colleges'"
                            :class="activeTab === 'colleges' ? 'border-[#0033a0] text-[#0033a0] dark:border-blue-400 dark:text-blue-400 bg-white dark:bg-[#1c1c1e]' : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400'"
                            class="w-full flex items-center justify-center gap-1 px-3 py-2.5 text-xs sm:text-sm font-bold border-b-2 rounded-t-lg transition cursor-pointer whitespace-nowrap text-center">
                        <span>Colleges / Units</span>
                        <span>(<span x-text="collegeUnits.length"></span>)</span>
                    </button>
                    <button type="button"
                            @click="activeTab = 'clusters'"
                            :class="activeTab === 'clusters' ? 'border-[#0033a0] text-[#0033a0] dark:border-blue-400 dark:text-blue-400 bg-white dark:bg-[#1c1c1e]' : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400'"
                            class="w-full flex items-center justify-center gap-1 px-3 py-2.5 text-xs sm:text-sm font-bold border-b-2 rounded-t-lg transition cursor-pointer whitespace-nowrap text-center">
                        <span>Campus / Clusters</span>
                        <span>(<span x-text="clusters.length"></span>)</span>
                    </button>
                    <button type="button"
                            @click="activeTab = 'offices'"
                            :class="activeTab === 'offices' ? 'border-[#0033a0] text-[#0033a0] dark:border-blue-400 dark:text-blue-400 bg-white dark:bg-[#1c1c1e]' : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400'"
                            class="w-full flex items-center justify-center gap-1 px-3 py-2.5 text-xs sm:text-sm font-bold border-b-2 rounded-t-lg transition cursor-pointer whitespace-nowrap text-center">
                        <span>Offices / Departments</span>
                        <span>(<span x-text="collegeOffices.length"></span>)</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 flex-1 space-y-4 overflow-hidden">

                    <!-- TAB 1: CONCERNS BY CATEGORY -->
                    <div x-show="activeTab === 'concerns'" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                            <div class="sm:col-span-5">
                                <select x-model="selectedConcernCategory"
                                        class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs sm:text-sm font-bold text-slate-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                                    <template x-for="catKey in Object.keys(concernsMap)" :key="catKey">
                                        <option :value="catKey" x-text="catKey + ' (' + (concernsMap[catKey]?.length || 0) + ' choices)'"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:col-span-5">
                                <input type="text"
                                       x-model="newConcernText"
                                       @keydown.enter.prevent="addConcern()"
                                       placeholder="Type a new concern choice."
                                       class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                            </div>
                            <div class="sm:col-span-2">
                                <button type="button"
                                        @click="addConcern()"
                                        class="w-full px-4 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs sm:text-sm font-bold transition shrink-0 cursor-pointer inline-flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Add</span>
                                </button>
                            </div>
                        </div>

                        <!-- Concern Items List -->
                        <div class="custom-modal-scroll border border-gray-200 dark:border-zinc-800 rounded-xl divide-y divide-gray-100 dark:divide-zinc-800 max-h-52 overflow-y-auto bg-gray-50/40 dark:bg-zinc-900/40">
                            <template x-for="(item, idx) in (concernsMap[selectedConcernCategory] || [])" :key="idx">
                                <div class="px-4 py-2.5 flex items-center justify-between gap-3 hover:bg-white dark:hover:bg-zinc-800/60 transition">
                                    <span class="text-xs sm:text-sm font-medium text-slate-800 dark:text-gray-200" x-text="item"></span>
                                    <button type="button"
                                            @click="removeConcern(idx)"
                                            title="Remove choice"
                                            class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg transition cursor-pointer shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </template>
                            <div x-show="!(concernsMap[selectedConcernCategory] && concernsMap[selectedConcernCategory].length)" class="p-6 text-center text-xs text-gray-400">
                                No concern choices in this category. Add one above.
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: COLLEGES / UNITS -->
                    <div x-show="activeTab === 'colleges'" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                            <div class="sm:col-span-6">
                                <input type="text"
                                       x-model="newCollegeName"
                                       @keydown.enter.prevent="addCollege()"
                                       placeholder="e.g. CSSP - College of Social Sciences and Philosophy"
                                       class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                            </div>
                            <div class="sm:col-span-4">
                                <select x-model="newCollegeCluster"
                                        class="w-full px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs sm:text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                                    <template x-for="cl in clusters" :key="cl">
                                        <option :value="cl" x-text="cl"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <button type="button"
                                        @click="addCollege()"
                                        class="w-full px-4 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer inline-flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Add</span>
                                </button>
                            </div>
                        </div>

                        <div class="custom-modal-scroll border border-gray-200 dark:border-zinc-800 rounded-xl divide-y divide-gray-100 dark:divide-zinc-800 max-h-52 overflow-y-auto bg-gray-50/40 dark:bg-zinc-900/40">
                            <template x-for="(unit, idx) in collegeUnits" :key="idx">
                                <div class="px-4 py-2.5 flex items-center justify-between gap-3 hover:bg-white dark:hover:bg-zinc-800/60 transition">
                                    <div class="min-w-0 flex-1 flex items-center gap-2.5 flex-wrap">
                                        <span class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-gray-200" x-text="unit.college"></span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <select x-model="unit.cluster"
                                                class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-blue-50 dark:bg-blue-950/60 text-[#0033a0] dark:text-blue-300 border border-blue-200 dark:border-blue-800 focus:outline-none">
                                            <template x-for="cl in clusters" :key="cl">
                                                <option :value="cl" x-text="cl" :selected="cl === unit.cluster"></option>
                                            </template>
                                        </select>
                                        <button type="button"
                                                @click="removeCollege(idx)"
                                                title="Remove college/unit"
                                                class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg transition cursor-pointer shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <div x-show="collegeUnits.length === 0" class="p-6 text-center text-xs text-gray-400">
                                No colleges/units configured. Add one above.
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: CAMPUS / CLUSTERS -->
                    <div x-show="activeTab === 'clusters'" class="space-y-4">
                        <div class="flex items-center gap-2">
                            <input type="text"
                                   x-model="newClusterName"
                                   @keydown.enter.prevent="addCluster()"
                                   placeholder="e.g. Cluster 1, Cluster Daraga, Polangui."
                                   class="flex-1 px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                            <button type="button"
                                    @click="addCluster()"
                                    class="px-4 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs sm:text-sm font-bold transition shrink-0 cursor-pointer inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Cluster</span>
                            </button>
                        </div>

                        <div class="custom-modal-scroll border border-gray-200 dark:border-zinc-800 rounded-xl divide-y divide-gray-100 dark:divide-zinc-800 max-h-52 overflow-y-auto bg-gray-50/40 dark:bg-zinc-900/40">
                            <template x-for="(cl, idx) in clusters" :key="idx">
                                <div class="px-4 py-2.5 flex items-center justify-between gap-3 hover:bg-white dark:hover:bg-zinc-800/60 transition">
                                    <div class="flex items-center gap-2.5">
                                        <span class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-gray-200" x-text="cl"></span>
                                        <span class="text-[11px] font-medium text-gray-400" x-text="'(' + collegeUnits.filter(u => u.cluster === cl).length + ' colleges)'"></span>
                                    </div>
                                    <button type="button"
                                            @click="removeCluster(idx)"
                                            title="Remove cluster"
                                            class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg transition cursor-pointer shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </template>
                            <div x-show="clusters.length === 0" class="p-6 text-center text-xs text-gray-400">
                                No campus/clusters configured. Add one above.
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: OFFICES / DEPARTMENTS -->
                    <div x-show="activeTab === 'offices'" class="space-y-4">
                        <div class="flex items-center gap-2">
                            <input type="text"
                                   x-model="newOfficeName"
                                   @keydown.enter.prevent="addOffice()"
                                   placeholder="e.g. Dean's Office, Library, ICTO."
                                   class="flex-1 px-3.5 py-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:border-[#0033a0]">
                            <button type="button"
                                    @click="addOffice()"
                                    class="px-4 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs sm:text-sm font-bold transition shrink-0 cursor-pointer inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Office</span>
                            </button>
                        </div>

                        <div class="custom-modal-scroll border border-gray-200 dark:border-zinc-800 rounded-xl divide-y divide-gray-100 dark:divide-zinc-800 max-h-52 overflow-y-auto bg-gray-50/40 dark:bg-zinc-900/40">
                            <template x-for="(office, idx) in collegeOffices" :key="idx">
                                <div class="px-4 py-2.5 flex items-center justify-between gap-3 hover:bg-white dark:hover:bg-zinc-800/60 transition">
                                    <span class="text-xs sm:text-sm font-medium text-slate-800 dark:text-gray-200" x-text="office"></span>
                                    <button type="button"
                                            @click="removeOffice(idx)"
                                            title="Remove office"
                                            class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg transition cursor-pointer shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </template>
                            <div x-show="collegeOffices.length === 0" class="p-6 text-center text-xs text-gray-400">
                                No offices configured. Add one above.
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-3 shrink-0">
                    <button type="button"
                            @click="confirmRestoreOpen = true"
                            class="px-3.5 py-2 text-xs font-bold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-xl transition cursor-pointer inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Restore Defaults</span>
                    </button>

                    <div class="flex items-center gap-2.5">
                        <button type="button"
                                @click="modalOpen = false"
                                class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-700 text-slate-700 dark:text-gray-300 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer">
                            Cancel
                        </button>

                        <form method="POST" action="{{ route('admin.requests.form-options.update') }}">
                            @csrf
                            <input type="hidden" name="options_json" :value="serializedPayload">
                            <button type="submit"
                                    class="px-5 py-2 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs sm:text-sm font-bold transition shadow-sm cursor-pointer">
                                Save Changes
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </template>

    <!-- Restore Defaults Confirmation Modal -->
    <template x-teleport="body">
        <div x-show="confirmRestoreOpen"
             x-cloak
             class="fixed inset-0 z-[100000] flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs"
             x-transition.opacity>
            <div @click.outside="confirmRestoreOpen = false"
                 class="w-full max-w-md bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden">

                <!-- Header -->
                <div class="px-6 py-4 bg-red-50/80 dark:bg-red-950/30 border-b border-red-100 dark:border-red-900/50 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/60 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm sm:text-base font-extrabold text-red-900 dark:text-red-200">Restore System Defaults?</h4>
                        <p class="text-[11px] text-red-700 dark:text-red-300/80 font-medium">This action will reset all custom dropdown changes</p>
                    </div>
                    <button type="button" @click="confirmRestoreOpen = false" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-3 text-xs sm:text-sm text-slate-600 dark:text-gray-300 leading-relaxed">
                    <p>
                        Are you sure you want to restore all request form dropdowns back to their original system defaults?
                    </p>
                    <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-zinc-800/70 border border-gray-200 dark:border-zinc-700/80 text-xs space-y-1">
                        <div class="font-bold text-slate-800 dark:text-gray-200">What will be reset:</div>
                        <ul class="list-disc pl-4 space-y-0.5 text-gray-600 dark:text-gray-400">
                            <li>All custom <strong>Title of Concerns</strong> across categories</li>
                            <li>All custom <strong>Colleges / Units</strong> and cluster assignments</li>
                            <li>All custom <strong>Campus / Clusters</strong> and <strong>Offices / Departments</strong></li>
                        </ul>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-800 flex items-center justify-end gap-2.5">
                    <button type="button"
                            @click="confirmRestoreOpen = false"
                            class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-700 text-slate-700 dark:text-gray-300 rounded-xl text-xs sm:text-sm font-bold transition cursor-pointer">
                        Keep Current Options
                    </button>
                    <form method="POST" action="{{ route('admin.requests.form-options.reset') }}">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs sm:text-sm font-bold transition shadow-sm cursor-pointer inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Yes, Restore Defaults</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </template>

</div>

<!-- Livewire Component Table & Live KPI Grid -->
@livewire('admin.request-table')

@push('scripts')
<script>
function formOptionsManager(initialOptions) {
    return {
        dropdownOpen: false,
        modalOpen: false,
        confirmRestoreOpen: false,
        activeTab: 'concerns',
        selectedConcernCategory: 'Carpentry/Masonry/Electrical',
        newConcernText: '',
        newCollegeName: '',
        newCollegeCluster: 'Main',
        newClusterName: '',
        newOfficeName: '',

        concernsMap: JSON.parse(JSON.stringify(initialOptions.concernsMap || {})),
        collegeUnits: JSON.parse(JSON.stringify(initialOptions.collegeUnits || [])),
        collegeOffices: JSON.parse(JSON.stringify(initialOptions.collegeOffices || [])),
        clusters: JSON.parse(JSON.stringify(initialOptions.clusters || [
            'Main', 'Cluster 1', 'Cluster 2', 'Cluster 3', 'Cluster 4',
            'Cluster Daraga', 'Guinobatan', 'Polangui', 'Tabaco', 'Gubat'
        ])),

        sortConcernsBottomOther(list) {
            if (!Array.isArray(list)) return [];
            const regular = list.filter(item => !/^other\b/i.test(String(item).trim()));
            const others = list.filter(item => /^other\b/i.test(String(item).trim()));
            return [...regular, ...others];
        },

        sortOfficesBottomOther(list) {
            if (!Array.isArray(list)) return [];
            const regular = list.filter(item => !/^other/i.test(String(item).trim()));
            const others = list.filter(item => /^other/i.test(String(item).trim()));
            return [...regular, ...others];
        },

        openModal() {
            const keys = Object.keys(this.concernsMap);
            keys.forEach(k => {
                this.concernsMap[k] = this.sortConcernsBottomOther(this.concernsMap[k]);
            });
            this.collegeOffices = this.sortOfficesBottomOther(this.collegeOffices);
            if (keys.length && !this.concernsMap[this.selectedConcernCategory]) {
                this.selectedConcernCategory = keys[0];
            }
            if (this.clusters.length && !this.clusters.includes(this.newCollegeCluster)) {
                this.newCollegeCluster = this.clusters[0];
            }
            this.modalOpen = true;
        },

        addConcern() {
            const val = (this.newConcernText || '').trim();
            if (!val) return;
            if (!this.concernsMap[this.selectedConcernCategory]) {
                this.concernsMap[this.selectedConcernCategory] = [];
            }
            if (!this.concernsMap[this.selectedConcernCategory].includes(val)) {
                this.concernsMap[this.selectedConcernCategory].push(val);
                this.concernsMap[this.selectedConcernCategory] = this.sortConcernsBottomOther(this.concernsMap[this.selectedConcernCategory]);
            }
            this.newConcernText = '';
        },

        removeConcern(idx) {
            if (this.concernsMap[this.selectedConcernCategory]) {
                this.concernsMap[this.selectedConcernCategory].splice(idx, 1);
            }
        },

        addCollege() {
            const name = (this.newCollegeName || '').trim();
            const cluster = (this.newCollegeCluster || 'Main').trim();
            if (!name) return;
            const exists = this.collegeUnits.some(u => u.college.toLowerCase() === name.toLowerCase());
            if (!exists) {
                this.collegeUnits.push({
                    cluster: cluster,
                    label: name,
                    college: name
                });
            }
            this.newCollegeName = '';
        },

        removeCollege(idx) {
            this.collegeUnits.splice(idx, 1);
        },

        addCluster() {
            const name = (this.newClusterName || '').trim();
            if (!name) return;
            const exists = this.clusters.some(c => c.toLowerCase() === name.toLowerCase());
            if (!exists) {
                this.clusters.push(name);
                if (!this.newCollegeCluster) {
                    this.newCollegeCluster = name;
                }
            }
            this.newClusterName = '';
        },

        removeCluster(idx) {
            this.clusters.splice(idx, 1);
            if (this.clusters.length && !this.clusters.includes(this.newCollegeCluster)) {
                this.newCollegeCluster = this.clusters[0];
            }
        },

        addOffice() {
            const name = (this.newOfficeName || '').trim();
            if (!name) return;
            if (!this.collegeOffices.includes(name)) {
                this.collegeOffices.push(name);
                this.collegeOffices = this.sortOfficesBottomOther(this.collegeOffices);
            }
            this.newOfficeName = '';
        },

        removeOffice(idx) {
            this.collegeOffices.splice(idx, 1);
        },

        get serializedPayload() {
            return JSON.stringify({
                concernsMap: this.concernsMap,
                collegeUnits: this.collegeUnits,
                collegeOffices: this.collegeOffices,
                clusters: this.clusters
            });
        }
    };
}
</script>
@endpush

@endsection
