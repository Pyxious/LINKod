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
            <div class="text-[#1a3c8f] dark:text-blue-400 text-xs font-bold uppercase tracking-wider mb-2">On Hold</div>
            <div class="text-[#1a3c8f] dark:text-white text-2xl sm:text-3xl font-extrabold leading-none">{{ $onHold }}</div>
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
        <!-- Priority Toggles -->
        <div class="flex bg-gray-100 dark:bg-zinc-800/80 p-1 rounded-lg gap-1 overflow-x-auto">
            <button wire:click="setPriority('High')" class="flex-1 md:flex-initial text-center px-3.5 py-1.5 text-xs rounded-md transition-all whitespace-nowrap {{ $priority === 'High' ? 'bg-white dark:bg-zinc-900 text-gray-900 dark:text-blue-400 shadow-xs font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">High</button>
            <button wire:click="setPriority('Medium')" class="flex-1 md:flex-initial text-center px-3.5 py-1.5 text-xs rounded-md transition-all whitespace-nowrap {{ $priority === 'Medium' ? 'bg-white dark:bg-zinc-900 text-gray-900 dark:text-blue-400 shadow-xs font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">Medium</button>
            <button wire:click="setPriority('Low')" class="flex-1 md:flex-initial text-center px-3.5 py-1.5 text-xs rounded-md transition-all whitespace-nowrap {{ $priority === 'Low' ? 'bg-white dark:bg-zinc-900 text-gray-900 dark:text-blue-400 shadow-xs font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">Low</button>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <select wire:model.live="status" class="px-3 py-2 rounded-xl border border-[#1a3c8f]/30 dark:border-zinc-700 text-[#1a3c8f] dark:text-blue-400 bg-white dark:bg-zinc-900 text-xs font-bold outline-none cursor-pointer shadow-2xs">
                <option value="">Active Requests</option>
                <option value="Pending">Pending / Approved</option>
                <option value="On Hold">On Hold</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
                <option value="all">All Statuses</option>
            </select>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#1a3c8f] dark:text-blue-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search" class="pl-9 pr-3 py-2 rounded-xl border border-[#1a3c8f]/30 dark:border-zinc-700 text-[#1a3c8f] dark:text-blue-300 text-xs font-semibold outline-none w-full sm:w-48 bg-white dark:bg-zinc-900 shadow-2xs">
            </div>
        </div>
    </div>

    <!-- Mobile Request Cards View (visible only on < md screens) -->
    <div class="block md:hidden space-y-3">
        @forelse($requests as $r)
            @php
                $catName = strtolower($r->category->category_name ?? '');
                $prefix = match(true) {
                    str_contains($catName, 'landscaping') => 'LS',
                    str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
                    str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
                    str_contains($catName, 'plumbing') => 'PS',
                    default => 'REQ'
                };
                $reqCode = $prefix . '-' . str_pad($r->request_id, 3, '0', STR_PAD_LEFT);
                
                $priClass = match(strtolower($r->priority ?? 'low')) { 
                    'high'=>'bg-red-50 text-red-600 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800', 
                    'medium'=>'bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800', 
                    default=>'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' 
                };

                $s = $r->current_status;
                $sClass = match($s) {
                    'Pending', 'Approved'=>'bg-orange-50 text-orange-600 border-orange-300',
                    'On Hold'=>'bg-orange-50 text-orange-600 border-orange-300',
                    'In Progress', 'Pending Verification'=>'bg-amber-50 text-amber-700 border-amber-300',
                    'Completed'=>'bg-emerald-50 text-emerald-700 border-emerald-300',
                    default=>'bg-gray-50 text-gray-600 border-gray-300'
                };
                $assignedWorkers = $r->project?->workers ?? collect();
            @endphp

            <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-4 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-mono font-bold text-xs text-[#1a3c8f] dark:text-blue-400 bg-blue-50 dark:bg-zinc-800 px-2 py-0.5 rounded">{{ $reqCode }}</span>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $sClass }}">{{ $s }}</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $r->title }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">By: {{ $r->client->user->first_name ?? '' }} {{ $r->client->user->last_name ?? '' }}</p>
                </div>
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-100 dark:border-zinc-800">
                    <span>{{ $r->location }}</span>
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold border {{ $priClass }}">{{ ucfirst($r->priority ?? 'Low') }}</span>
                </div>
                @if($assignedWorkers->count() > 0)
                <div class="text-xs text-gray-600 dark:text-gray-300 pt-1">
                    <span class="font-semibold text-gray-400">Assigned:</span>
                    @foreach($assignedWorkers as $w)
                        <span class="inline-block bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 px-2 py-0.5 rounded text-[11px] font-medium mr-1">{{ $w->staff->user->first_name ?? '' }}</span>
                    @endforeach
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
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-separate" style="border-spacing: 0 8px;">
            <thead>
                <tr>
                    <th class="w-10 px-4 pb-2 border-b-2 border-slate-300">
                        <input type="checkbox" class="rounded border-gray-300 text-[#1a3c8f] focus:ring-[#1a3c8f]">
                    </th>
                    <th wire:click="sortBy('request_id')" class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800 cursor-pointer select-none hover:text-blue-600 transition">
                        Requisition No.
                        @if($sortField === 'request_id')
                            <span class="ml-0.5 text-blue-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th wire:click="sortBy('title')" class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800 cursor-pointer select-none hover:text-blue-600 transition">
                        Requestor / Title
                        @if($sortField === 'title')
                            <span class="ml-0.5 text-blue-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th wire:click="sortBy('location')" class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800 cursor-pointer select-none hover:text-blue-600 transition">
                        Office/Unit
                        @if($sortField === 'location')
                            <span class="ml-0.5 text-blue-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800">Assigned Personnel</th>
                    <th wire:click="sortBy('priority')" class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800 cursor-pointer select-none hover:text-blue-600 transition">
                        Priority
                        @if($sortField === 'priority')
                            <span class="ml-0.5 text-blue-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800">Status</th>
                    <th wire:click="sortBy('submitted_at')" class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800 cursor-pointer select-none hover:text-blue-600 transition">
                        Date Requested
                        @if($sortField === 'submitted_at')
                            <span class="ml-0.5 text-blue-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="text-[#1a3c8f] dark:text-blue-400 text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300 dark:border-zinc-800">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $r)
                <tr class="bg-white dark:bg-[#1c1c1e] hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition shadow-xs group">
                    <td class="px-4 py-4 border-y border-l border-gray-200 dark:border-zinc-800 rounded-l-lg">
                        <input type="checkbox" class="rounded border-gray-300 text-[#1a3c8f] focus:ring-[#1a3c8f]">
                    </td>
                    <td class="py-4 border-y border-gray-200 dark:border-zinc-800">
                        @php
                            $catName = strtolower($r->category->category_name ?? '');
                            $prefix = match(true) {
                                str_contains($catName, 'landscaping') => 'LS',
                                str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
                                str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
                                str_contains($catName, 'plumbing') => 'PS',
                                default => 'REQ'
                            };
                            $reqCode = $prefix . '-' . str_pad($r->request_id, 3, '0', STR_PAD_LEFT);
                        @endphp
                        <span class="font-bold text-[#1a3c8f] dark:text-blue-400 text-[13px] tracking-wide">{{ $reqCode }}</span>
                        <div class="text-[11px] text-gray-600 dark:text-gray-400 font-medium">{{ $r->category->category_name ?? 'General' }}</div>
                    </td>
                    <td class="py-4 border-y border-gray-200 dark:border-zinc-800">
                        <div class="font-bold text-gray-900 dark:text-white text-[13px]">{{ $r->client->user->first_name ?? '' }} {{ $r->client->user->last_name ?? '' }}</div>
                        <div class="text-[11px] text-gray-600 dark:text-gray-400">{{ $r->client->user->email_account ?? '' }}</div>
                    </td>
                    <td class="py-4 border-y border-gray-200 dark:border-zinc-800">
                        <div class="font-bold text-gray-900 dark:text-white text-[13px]">{{ $r->campus ?? 'Main Campus' }}</div>
                        <div class="text-[11px] text-gray-600 dark:text-gray-400">{{ $r->location }}</div>
                    </td>
                    <td class="py-4 border-y border-gray-200 dark:border-zinc-800">
                        @php
                            $assignedWorkers = $r->project?->workers ?? collect();
                        @endphp
                        @if($assignedWorkers->count() > 0)
                            <div class="flex flex-wrap gap-1 items-center">
                                @foreach($assignedWorkers as $w)
                                    <span class="inline-flex items-center gap-1 bg-blue-50 dark:bg-zinc-800 text-[#1a3c8f] dark:text-blue-300 px-2 py-0.5 rounded-full text-[11px] font-semibold border border-blue-100 dark:border-zinc-700">
                                        <span class="w-3.5 h-3.5 rounded-full bg-[#1a3c8f] text-white flex items-center justify-center text-[8px] font-extrabold">
                                            {{ strtoupper(substr($w->staff->user->first_name ?? 'W', 0, 1)) }}
                                        </span>
                                        {{ $w->staff->user->first_name ?? '' }} {{ $w->staff->user->last_name ?? '' }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-gray-400 italic">Unassigned</span>
                        @endif
                    </td>
                    <td class="py-4 border-y border-gray-200 dark:border-zinc-800">
                        @php
                            $priClass = match(strtolower($r->priority ?? 'low')) { 
                                'high'=>'bg-red-50 text-red-600 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800', 
                                'medium'=>'bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800', 
                                default=>'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' 
                            };
                        @endphp
                        <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold border {{ $priClass }}">
                            {{ ucfirst($r->priority ?? 'Low') }}
                        </span>
                    </td>
                    <td class="py-4 border-y border-gray-200 dark:border-zinc-800">
                        @php
                            $s = $r->current_status;
                            $sClass = match($s) {
                                'Pending', 'Approved'=>'bg-orange-50 text-orange-600 border-orange-300',
                                'On Hold'=>'bg-orange-50 text-orange-600 border-orange-300',
                                'In Progress', 'Pending Verification'=>'bg-amber-50 text-amber-600 border-amber-300',
                                'Completed'=>'bg-emerald-50 text-emerald-600 border-emerald-300',
                                default=>'bg-gray-50 text-gray-600 border-gray-300'
                            };
                        @endphp
                        <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold border {{ $sClass }}">
                            {{ $s }}
                        </span>
                    </td>
                    <td class="py-4 border-y border-gray-200 dark:border-zinc-800">
                        <span class="text-[#1a3c8f] dark:text-gray-200 font-bold text-[13px]">{{ \Carbon\Carbon::parse($r->submitted_at)->format('m/d/Y') }}</span>
                    </td>
                    <td class="px-4 py-4 border-y border-r border-gray-200 dark:border-zinc-800 rounded-r-lg">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.requests.show', $r->request_id) }}" class="text-[#1a3c8f] dark:text-blue-400 hover:text-blue-600 transition" title="View / Manage Request">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-8 text-gray-500">No requests found matching your filters.</td>
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
