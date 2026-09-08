<div wire:poll.10s>
    <!-- KPI Grid (5 Metrics: Total Requests, Submitted, On Hold, In Progress, Completed) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3.5 sm:gap-4 mb-6 font-sans">
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm">
            <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Total Requests</div>
            <div class="text-[#1a3c8f] dark:text-white text-2xl sm:text-3xl font-extrabold leading-none">{{ $totalRequests }}</div>
        </div>

        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm">
            <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Submitted</div>
            <div class="text-[#1a3c8f] dark:text-white text-2xl sm:text-3xl font-extrabold leading-none">{{ $submitted }}</div>
        </div>

        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm">
            <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Awaiting Materials</div>
            <div class="text-[#1a3c8f] dark:text-white text-2xl sm:text-3xl font-extrabold leading-none">{{ $awaitingMaterials }}</div>
        </div>

        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm">
            <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">In Progress</div>
            <div class="text-[#1a3c8f] dark:text-white text-2xl sm:text-3xl font-extrabold leading-none">{{ $inProgress }}</div>
        </div>

        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-sm col-span-2 sm:col-span-1">
            <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">Completed</div>
            <div class="text-[#1a3c8f] dark:text-white text-2xl sm:text-3xl font-extrabold leading-none">{{ $completed }}</div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-3 mb-4">
        <!-- Priority & Rating Status Toggles -->
        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex bg-gray-100 dark:bg-zinc-800/80 p-1 rounded-lg gap-1 overflow-x-auto">
                <button wire:click="setPriority('High')" 
                        wire:loading.attr="disabled"
                        class="flex-1 md:flex-initial text-center px-3.5 py-1.5 text-xs rounded-md transition-all whitespace-nowrap inline-flex items-center justify-center gap-1.5 {{ in_array(strtolower($priority), ['urgent', 'high']) ? 'bg-red-50 text-red-600 border border-red-200 dark:bg-red-950/40 dark:text-red-300 shadow-xs font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
                    <svg wire:loading wire:target="setPriority('High')" class="animate-spin h-3 w-3 text-red-600 dark:text-red-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>High</span>
                </button>
                <button wire:click="setPriority('Routine')" 
                        wire:loading.attr="disabled"
                        class="flex-1 md:flex-initial text-center px-3.5 py-1.5 text-xs rounded-md transition-all whitespace-nowrap inline-flex items-center justify-center gap-1.5 {{ in_array(strtolower($priority), ['routine', 'medium', 'low']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 shadow-xs font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
                    <svg wire:loading wire:target="setPriority('Routine')" class="animate-spin h-3 w-3 text-emerald-600 dark:text-emerald-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Routine</span>
                </button>
            </div>

            @if($status === 'Completed')
                <!-- Rating Status Sub-filters (Visible only when Completed is selected) -->
                <div class="flex items-center bg-blue-50/70 dark:bg-zinc-800/80 p-1 rounded-lg gap-1 border border-blue-100 dark:border-zinc-700">
                    <button wire:click="setRatingFilter('')" 
                            wire:loading.attr="disabled"
                            class="px-2.5 py-1 text-xs rounded-md font-bold transition-all whitespace-nowrap inline-flex items-center gap-1.5 {{ $ratingFilter === '' ? 'bg-[#0033a0] text-white shadow-xs' : 'text-slate-600 hover:text-[#0033a0] dark:text-gray-300' }}">
                        <svg wire:loading wire:target="setRatingFilter('')" class="animate-spin h-3 w-3 {{ $ratingFilter === '' ? 'text-white' : 'text-[#0033a0] dark:text-blue-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>All Completed ({{ $completed }})</span>
                    </button>
                    <button wire:click="setRatingFilter('not_rated')" 
                            wire:loading.attr="disabled"
                            class="px-2.5 py-1 text-xs rounded-md font-bold transition-all whitespace-nowrap inline-flex items-center gap-1.5 {{ $ratingFilter === 'not_rated' ? 'bg-amber-500 text-white shadow-xs' : 'text-amber-700 hover:bg-amber-100/60 dark:text-amber-400' }}">
                        <svg wire:loading wire:target="setRatingFilter('not_rated')" class="animate-spin h-3 w-3 {{ $ratingFilter === 'not_rated' ? 'text-white' : 'text-amber-600 dark:text-amber-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Not Rated ({{ $completedNotRated }})</span>
                    </button>
                    <button wire:click="setRatingFilter('rated')" 
                            wire:loading.attr="disabled"
                            class="px-2.5 py-1 text-xs rounded-md font-bold transition-all whitespace-nowrap inline-flex items-center gap-1.5 {{ $ratingFilter === 'rated' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-700 hover:bg-emerald-100/60 dark:text-emerald-400' }}">
                        <svg wire:loading wire:target="setRatingFilter('rated')" class="animate-spin h-3 w-3 {{ $ratingFilter === 'rated' ? 'text-white' : 'text-emerald-600 dark:text-emerald-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Rated ({{ $completedRated }})</span>
                    </button>
                </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <select wire:model.live="status" class="px-3 py-2 rounded-xl border border-[#1a3c8f]/30 dark:border-zinc-700 text-[#1a3c8f] dark:text-blue-400 bg-white dark:bg-zinc-900 text-xs font-bold outline-none cursor-pointer shadow-2xs">
                <option value="">Active Requests</option>
                <option value="Pending">Pending / Approved</option>
                <option value="Awaiting Materials">Awaiting Materials</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
                <option value="Rejected">Rejected</option>
                <option value="recurring">Recurring Issues</option>
                <option value="all">All Statuses</option>
            </select>
            <div class="relative w-full sm:w-56">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#1a3c8f] dark:text-blue-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search requests..." class="pl-9 pr-9 py-2 rounded-xl border border-[#1a3c8f]/30 dark:border-zinc-700 text-[#1a3c8f] dark:text-blue-300 text-xs font-semibold outline-none w-full bg-white dark:bg-zinc-900 shadow-2xs focus:border-[#1a3c8f] focus:ring-1 focus:ring-[#1a3c8f]">
                <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#0038A8] dark:text-blue-400">
                    <svg class="animate-spin h-4 w-4 text-[#0038A8] dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Loading Bar for table sorting & filtering -->
    <div wire:loading wire:target="sortBy, status, priority, ratingFilter, setPriority, setRatingFilter, search" class="w-full h-1 bg-blue-100 dark:bg-blue-950 overflow-hidden rounded-full mb-3">
        <div class="h-full bg-[#0038A8] dark:bg-blue-400 animate-pulse w-full"></div>
    </div>

    <!-- Mobile Request Cards View (visible only on < md screens) -->
    <div class="block md:hidden space-y-3" wire:loading.class="opacity-60 pointer-events-none" wire:target="sortBy, status, priority, ratingFilter, setPriority, setRatingFilter, search">
        @forelse($requests as $r)
            @php
                $catName = strtolower($r->category->category_name ?? '');
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
                $reqCode = $prefix . '-' . str_pad($r->request_id, 3, '0', STR_PAD_LEFT);
                
                $priClass = $r->is_urgent 
                    ? 'bg-red-50 text-red-600 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800' 
                    : 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800';

                $s = $r->current_status;
                $sClass = match($s) {
                    'Pending', 'Approved', 'Submitted'=>'bg-orange-50 text-orange-600 border-orange-300',
                    'On Hold', 'Awaiting Materials', 'Awaiting Verification of Bill of Materials', 'BOM Verified (Awaiting Client Approval)'=>'bg-amber-50 text-amber-700 border-amber-300',
                    'In Progress', 'Pending Verification'=>'bg-blue-50 text-blue-700 border-blue-300',
                    'Completed'=>'bg-emerald-50 text-emerald-700 border-emerald-300',
                    'Rejected', 'Cancelled'=>'bg-red-50 text-red-600 border-red-300',
                    default=>'bg-gray-50 text-gray-600 border-gray-300'
                };
                $assignedWorkers = $r->project?->workers ?? collect();
            @endphp

            <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-mono font-bold text-xs text-[#1a3c8f] dark:text-blue-400 bg-blue-50 dark:bg-zinc-800 px-2 py-0.5 rounded">{{ $reqCode }}</span>
                    <div class="flex items-center gap-1.5">
                        @if($r->is_recurring)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800" title="Recurring Problem: {{ $r->recurring_count }} similar requests this month">
                                Recurring
                            </span>
                        @endif
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $sClass }}">{{ $s }}</span>
                    </div>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $r->title }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">By: {{ $r->client->user->first_name ?? '' }} {{ $r->client->user->last_name ?? '' }}</p>
                </div>
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-100 dark:border-zinc-800">
                    <span>{{ $r->location }}</span>
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold border {{ $priClass }}">{{ $r->priority_label }}</span>
                </div>

                @if($status === 'Completed')
                    <div class="flex items-center justify-between text-xs pt-2 border-t border-gray-100 dark:border-zinc-800">
                        <span class="text-gray-500 dark:text-gray-400 font-semibold">Rating Status:</span>
                        @if($r->evaluation)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-300">
                                <svg class="w-3 h-3 text-amber-500 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Rated (★ {{ number_format($r->evaluation->rating ?? 5, 1) }})
                            </span>
                        @else
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Not Rated
                                </span>
                                <button wire:click="notifyToRate({{ $r->request_id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="notifyToRate({{ $r->request_id }})"
                                        type="button"
                                        class="p-1 rounded-md bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-300 shadow-xs transition"
                                        title="Send rating reminder">
                                    <svg wire:loading.remove wire:target="notifyToRate({{ $r->request_id }})" class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        @if(session('notified_rate_' . $r->request_id))
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        @endif
                                    </svg>
                                    <svg wire:loading wire:target="notifyToRate({{ $r->request_id }})" class="animate-spin w-3.5 h-3.5 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

                @if($assignedWorkers->count() > 0)
                <div class="text-xs text-gray-600 dark:text-gray-300 pt-1">
                    <span class="font-semibold text-gray-400">Assigned:</span>
                    @if($assignedWorkers->count() <= 2)
                        @foreach($assignedWorkers as $w)
                            <span class="inline-block bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded text-[11px] font-medium mr-1">{{ $w->staff->user->first_name ?? '' }}</span>
                        @endforeach
                    @else
                        @php
                            $firstWorker = $assignedWorkers->first();
                            $allWorkerNames = $assignedWorkers->map(fn($w) => ($w->staff->user->first_name ?? '') . ' ' . ($w->staff->user->last_name ?? ''))->filter()->join(', ');
                        @endphp
                        <span class="inline-block bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded text-[11px] font-medium mr-1">{{ $firstWorker->staff->user->first_name ?? '' }} {{ $firstWorker->staff->user->last_name ?? '' }}</span>
                        <span class="inline-block bg-blue-100 dark:bg-zinc-700 text-[#1a3c8f] dark:text-blue-300 px-2 py-0.5 rounded text-[11px] font-bold cursor-pointer" title="{{ $allWorkerNames }}">+{{ $assignedWorkers->count() - 1 }} more</span>
                    @endif
                </div>
                @endif
                <div class="pt-2 flex justify-between items-center text-xs">
                    <span class="text-gray-400">{{ \Carbon\Carbon::parse($r->submitted_at)->format('m/d/Y') }}</span>
                    <a href="{{ route('admin.requests.show', $r->request_id) }}" class="text-[#1a3c8f] dark:text-blue-400 font-bold hover:underline">View Details &rarr;</a>
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-500 bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 text-xs">
                No requests found matching your filters.
            </div>
        @endforelse

        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    </div>

    <!-- Desktop Request Table View (hidden on < md screens) -->
    <div class="hidden md:block overflow-x-auto" wire:loading.class="opacity-60 pointer-events-none" wire:target="sortBy, status, priority, ratingFilter, setPriority, setRatingFilter, search">
        <table class="w-full text-left border-separate" style="border-spacing: 0 6px;">
            <thead>
                <tr>
                    <th wire:click="sortBy('request_id')" class="px-3.5 text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2.5 border-b-2 border-slate-300 dark:border-zinc-800 cursor-pointer select-none hover:text-blue-600 transition">
                        <span>Requisition No.</span>
                        <span wire:loading.remove wire:target="sortBy('request_id')">
                            @if($sortField === 'request_id')
                                <span class="ml-0.5 text-blue-600 font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </span>
                        <span wire:loading wire:target="sortBy('request_id')" class="inline-block ml-1">
                            <svg class="animate-spin inline w-3 h-3 text-[#0038A8] dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </th>
                    <th wire:click="sortBy('title')" class="px-3 text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2.5 border-b-2 border-slate-300 dark:border-zinc-800 cursor-pointer select-none hover:text-blue-600 transition">
                        <span>Requestor / Title</span>
                        <span wire:loading.remove wire:target="sortBy('title')">
                            @if($sortField === 'title')
                                <span class="ml-0.5 text-blue-600 font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </span>
                        <span wire:loading wire:target="sortBy('title')" class="inline-block ml-1">
                            <svg class="animate-spin inline w-3 h-3 text-[#0038A8] dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </th>
                    <th wire:click="sortBy('location')" class="px-3 text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2.5 border-b-2 border-slate-300 dark:border-zinc-800 cursor-pointer select-none hover:text-blue-600 transition">
                        <span>Office/Unit</span>
                        <span wire:loading.remove wire:target="sortBy('location')">
                            @if($sortField === 'location')
                                <span class="ml-0.5 text-blue-600 font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </span>
                        <span wire:loading wire:target="sortBy('location')" class="inline-block ml-1">
                            <svg class="animate-spin inline w-3 h-3 text-[#0038A8] dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </th>
                    <th class="px-3 text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2.5 border-b-2 border-slate-300 dark:border-zinc-800">Assigned Personnel</th>
                    <th wire:click="sortBy('priority')" class="px-3 text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2.5 border-b-2 border-slate-300 dark:border-zinc-800 cursor-pointer select-none hover:text-blue-600 transition">
                        <span>Priority</span>
                        <span wire:loading.remove wire:target="sortBy('priority')">
                            @if($sortField === 'priority')
                                <span class="ml-0.5 text-blue-600 font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </span>
                        <span wire:loading wire:target="sortBy('priority')" class="inline-block ml-1">
                            <svg class="animate-spin inline w-3 h-3 text-[#0038A8] dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </th>
                    <th wire:click="sortBy('status')" class="px-3 text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2.5 border-b-2 border-slate-300 dark:border-zinc-800 cursor-pointer select-none hover:text-blue-600 transition">
                        <span>Status</span>
                        <span wire:loading.remove wire:target="sortBy('status')">
                            @if($sortField === 'status')
                                <span class="ml-0.5 text-blue-600 font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </span>
                        <span wire:loading wire:target="sortBy('status')" class="inline-block ml-1">
                            <svg class="animate-spin inline w-3 h-3 text-[#0038A8] dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </th>
                    @if($status === 'Completed')
                        <th wire:click="sortBy('rating_status')" class="px-3 text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2.5 border-b-2 border-slate-300 dark:border-zinc-800 cursor-pointer select-none hover:text-blue-600 transition">
                            <span>Rating Status</span>
                            <span wire:loading.remove wire:target="sortBy('rating_status')">
                                @if($sortField === 'rating_status')
                                    <span class="ml-0.5 text-blue-600 font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </span>
                            <span wire:loading wire:target="sortBy('rating_status')" class="inline-block ml-1">
                                <svg class="animate-spin inline w-3 h-3 text-[#0038A8] dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </th>
                    @endif
                    <th wire:click="sortBy('submitted_at')" class="px-3 text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2.5 border-b-2 border-slate-300 dark:border-zinc-800 cursor-pointer select-none hover:text-blue-600 transition">
                        <span>Date Requested</span>
                        <span wire:loading.remove wire:target="sortBy('submitted_at')">
                            @if($sortField === 'submitted_at')
                                <span class="ml-0.5 text-blue-600 font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </span>
                        <span wire:loading wire:target="sortBy('submitted_at')" class="inline-block ml-1">
                            <svg class="animate-spin inline w-3 h-3 text-[#0038A8] dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </th>
                    <th class="px-4 text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2.5 border-b-2 border-slate-300 dark:border-zinc-800">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $r)
                <tr class="bg-white dark:bg-[#1c1c1e] hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition shadow-xs group">
                    <td class="px-3.5 py-3 border-y border-l border-gray-200 dark:border-zinc-800 rounded-l-lg">
                        @php
                            $catName = strtolower($r->category->category_name ?? '');
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
                            $reqCode = $prefix . '-' . str_pad($r->request_id, 3, '0', STR_PAD_LEFT);
                        @endphp
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-bold text-[#1a3c8f] dark:text-blue-400 text-[13px] tracking-wide">{{ $reqCode }}</span>
                            @if($r->is_recurring)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800" title="Recurring Problem: {{ $r->recurring_count }} similar requests this month">
                                    Recurring
                                </span>
                            @endif
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">{{ $r->category->category_name ?? 'General' }}</div>
                    </td>
                    <td class="px-3 py-3 border-y border-gray-200 dark:border-zinc-800">
                        <div class="font-bold text-gray-900 dark:text-white text-[13px] line-clamp-1">{{ $r->client->user->first_name ?? '' }} {{ $r->client->user->last_name ?? '' }}</div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-1">{{ $r->title ?? $r->client->user->email_account ?? '' }}</div>
                    </td>
                    <td class="px-3 py-3 border-y border-gray-200 dark:border-zinc-800">
                        <div class="font-bold text-gray-900 dark:text-white text-[13px]">{{ $r->campus ?? 'Main Campus' }}</div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-1">{{ $r->location }}</div>
                    </td>
                    <td class="px-3 py-3 border-y border-gray-200 dark:border-zinc-800">
                        @php
                            $assignedWorkers = $r->project?->workers ?? collect();
                            $workerCount = $assignedWorkers->count();
                        @endphp
                        @if($workerCount > 0)
                            <div class="flex items-center gap-1.5 flex-wrap" x-data="{ open: false }">
                                @if($workerCount <= 2)
                                    @foreach($assignedWorkers as $w)
                                        <span class="inline-flex items-center gap-1 bg-blue-50 dark:bg-zinc-800 text-[#1a3c8f] dark:text-blue-300 px-2 py-0.5 rounded-full text-[11px] font-semibold border border-blue-100 dark:border-zinc-700">
                                            <span class="w-3.5 h-3.5 rounded-full bg-[#1a3c8f] text-white flex items-center justify-center text-[8px] font-extrabold flex-shrink-0">
                                                {{ strtoupper(substr($w->staff->user->first_name ?? 'W', 0, 1)) }}
                                            </span>
                                            <span class="truncate max-w-[110px]">{{ $w->staff->user->first_name ?? '' }} {{ $w->staff->user->last_name ?? '' }}</span>
                                        </span>
                                    @endforeach
                                @else
                                    @php
                                        $firstWorker = $assignedWorkers->first();
                                        $remainingCount = $workerCount - 1;
                                        $allNames = $assignedWorkers->map(fn($w) => ($w->staff->user->first_name ?? '') . ' ' . ($w->staff->user->last_name ?? ''))->filter()->join(', ');
                                    @endphp
                                    <span class="inline-flex items-center gap-1 bg-blue-50 dark:bg-zinc-800 text-[#1a3c8f] dark:text-blue-300 px-2 py-0.5 rounded-full text-[11px] font-semibold border border-blue-100 dark:border-zinc-700" title="{{ $firstWorker->staff->user->first_name ?? '' }} {{ $firstWorker->staff->user->last_name ?? '' }}">
                                        <span class="w-3.5 h-3.5 rounded-full bg-[#1a3c8f] text-white flex items-center justify-center text-[8px] font-extrabold flex-shrink-0">
                                            {{ strtoupper(substr($firstWorker->staff->user->first_name ?? 'W', 0, 1)) }}
                                        </span>
                                        <span class="truncate max-w-[100px]">{{ $firstWorker->staff->user->first_name ?? '' }} {{ $firstWorker->staff->user->last_name ?? '' }}</span>
                                    </span>

                                    <div class="relative inline-block" @click.outside="open = false">
                                        <button type="button" 
                                                @click.stop="open = !open" 
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 hover:bg-blue-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-[#1a3c8f] dark:text-blue-300 border border-blue-200 dark:border-zinc-700 transition cursor-pointer shadow-2xs"
                                                title="{{ $allNames }}">
                                            +{{ $remainingCount }} more
                                        </button>

                                        <div x-show="open" 
                                             x-cloak
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                             x-transition:leave="transition ease-in duration-100"
                                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                             x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                             class="absolute left-0 bottom-full mb-1.5 z-50 w-64 p-3 bg-white dark:bg-[#1c1c1e] rounded-xl shadow-xl border border-gray-200 dark:border-zinc-700 text-left">
                                            <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-gray-100 dark:border-zinc-800">
                                                <span class="text-[11px] font-bold text-[#1a3c8f] dark:text-blue-400">Assigned Team ({{ $workerCount }})</span>
                                                <button type="button" @click.stop="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs font-bold leading-none">&times;</button>
                                            </div>
                                            <div class="max-h-48 overflow-y-auto space-y-1 pr-1">
                                                @foreach($assignedWorkers as $w)
                                                    <div class="flex items-center gap-1.5 text-xs text-slate-700 dark:text-gray-300 py-0.5">
                                                        <span class="w-4 h-4 rounded-full bg-[#1a3c8f] text-white flex items-center justify-center text-[8px] font-extrabold flex-shrink-0">
                                                            {{ strtoupper(substr($w->staff->user->first_name ?? 'W', 0, 1)) }}
                                                        </span>
                                                        <span class="truncate text-[11px] font-medium">{{ $w->staff->user->first_name ?? '' }} {{ $w->staff->user->last_name ?? '' }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="text-xs text-gray-400 italic">Unassigned</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 border-y border-gray-200 dark:border-zinc-800 whitespace-nowrap">
                        @php
                            $priClass = $r->is_urgent 
                                ? 'bg-red-50 text-red-600 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800' 
                                : 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800';
                        @endphp
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $priClass }}">
                            {{ $r->priority_label }}
                        </span>
                    </td>
                    <td class="px-3 py-3 border-y border-gray-200 dark:border-zinc-800 whitespace-nowrap">
                        @php
                            $s = $r->current_status;
                            $sClass = match($s) {
                                'Pending', 'Approved', 'Submitted'=>'bg-orange-50 text-orange-600 border-orange-300',
                                'On Hold', 'Awaiting Materials', 'Awaiting Verification of Bill of Materials', 'BOM Verified (Awaiting Client Approval)'=>'bg-amber-50 text-amber-700 border-amber-300',
                                'In Progress', 'Pending Verification'=>'bg-blue-50 text-blue-700 border-blue-300',
                                'Completed'=>'bg-emerald-50 text-emerald-600 border-emerald-300',
                                'Rejected', 'Cancelled'=>'bg-red-50 text-red-600 border-red-300',
                                default=>'bg-gray-50 text-gray-600 border-gray-300'
                            };
                        @endphp
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $sClass }}">
                            {{ $s }}
                        </span>
                    </td>

                    @if($status === 'Completed')
                        <td class="px-3 py-3 border-y border-gray-200 dark:border-zinc-800 whitespace-nowrap">
                            @if($r->evaluation)
                                @php
                                    $ratingScore = $r->evaluation->rating ?? 5;
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800" title="Overall Rating: {{ $ratingScore }}/5">
                                    <svg class="w-3.5 h-3.5 text-amber-500 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span>Rated (★ {{ number_format($ratingScore, 1) }})</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    <span>Not Rated</span>
                                </span>
                            @endif
                        </td>
                    @endif

                    <td class="px-3 py-3 border-y border-gray-200 dark:border-zinc-800 whitespace-nowrap">
                        <span class="text-[#1a3c8f] dark:text-gray-200 font-bold text-[13px]">{{ \Carbon\Carbon::parse($r->submitted_at)->format('m/d/Y') }}</span>
                    </td>
                    <td class="px-4 py-3 border-y border-r border-gray-200 dark:border-zinc-800 rounded-r-lg whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.requests.show', $r->request_id) }}" class="p-1 text-[#1a3c8f] dark:text-blue-400 hover:text-blue-600 transition" title="View / Manage Request">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>

                            @if($status === 'Completed' && !$r->evaluation)
                                <button wire:click="notifyToRate({{ $r->request_id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="notifyToRate({{ $r->request_id }})"
                                        type="button"
                                        class="p-1.5 rounded-lg transition-all shadow-xs {{ session('notified_rate_' . $r->request_id) ? 'bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-700' }}"
                                        title="{{ session('notified_rate_' . $r->request_id) ? 'Rating reminder already sent!' : 'Send rating reminder to ' . ($r->client->user->first_name ?? 'Client') }}">
                                    <svg wire:loading.remove wire:target="notifyToRate({{ $r->request_id }})" class="w-4 h-4 {{ session('notified_rate_' . $r->request_id) ? 'text-emerald-600' : 'text-amber-600 dark:text-amber-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        @if(session('notified_rate_' . $r->request_id))
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        @endif
                                    </svg>
                                    <svg wire:loading wire:target="notifyToRate({{ $r->request_id }})" class="animate-spin w-4 h-4 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $status === 'Completed' ? 9 : 8 }}" class="text-center py-8 text-gray-500">No requests found matching your filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.addEventListener('rating-reminded', function(e) {
        const detail = e.detail;
        if (window.LINKodRealtime && window.LINKodRealtime.showNotificationToast) {
            window.LINKodRealtime.showNotificationToast(
                detail.success ? 'Reminder Sent' : 'Notice',
                detail.message || 'Rating reminder has been delivered to client.'
            );
        }
    });

    if (window.supabaseClient) {
        window.supabaseClient
            .channel('admin-realtime-request-table')
            .on(
                'postgres_changes',
                { event: '*', schema: 'public', table: 'request' },
                (payload) => {
                    console.log('[Supabase Realtime] Request table change:', payload);
                    if (window.Livewire) {
                        Livewire.dispatch('refreshRequests');
                    }
                    if (payload.eventType === 'INSERT' && window.LINKodRealtime) {
                        window.LINKodRealtime.showNotificationToast('New Request Submitted', payload.new?.title || 'A new service requisition has been submitted.', '/admin/requests/' + payload.new?.request_id);
                    }
                }
            )
            .on(
                'postgres_changes',
                { event: '*', schema: 'public', table: 'request_history' },
                (payload) => {
                    if (window.Livewire) {
                        Livewire.dispatch('refreshRequests');
                    }
                }
            )
            .subscribe();
    }
});
</script>
