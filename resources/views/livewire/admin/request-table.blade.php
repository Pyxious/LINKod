<div>
    <div class="flex justify-between items-center mb-4">
        <!-- Priority Toggles -->
        <div class="flex bg-gray-100 rounded-md p-1">
            <button wire:click="setPriority('High')" class="px-4 py-1.5 text-[13px] rounded-md transition-all {{ $priority === 'High' ? 'bg-white text-gray-900 shadow-sm font-semibold' : 'text-gray-500 hover:text-gray-700' }}">High</button>
            <button wire:click="setPriority('Medium')" class="px-4 py-1.5 text-[13px] rounded-md transition-all {{ $priority === 'Medium' ? 'bg-white text-gray-900 shadow-sm font-semibold' : 'text-gray-500 hover:text-gray-700' }}">Medium</button>
            <button wire:click="setPriority('Low')" class="px-4 py-1.5 text-[13px] rounded-md transition-all {{ $priority === 'Low' ? 'bg-white text-gray-900 shadow-sm font-semibold' : 'text-gray-500 hover:text-gray-700' }}">Low</button>
        </div>

        <div class="flex items-center gap-3">
            <select wire:model.live="status" class="px-3 py-2 rounded-md border border-[#1a3c8f] text-[#1a3c8f] bg-white text-[13px] font-medium outline-none">
                <option value="">Active Requests</option>
                <option value="Pending">Pending / Approved</option>
                <option value="On Hold">On Hold</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
            </select>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-[#1a3c8f]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search" class="pl-9 pr-3 py-2 rounded-md border border-[#1a3c8f] text-[#1a3c8f] text-[13px] outline-none w-48">
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="w-full">
        <table class="w-full text-left border-separate" style="border-spacing: 0 8px;">
            <thead>
                <tr>
                    <th class="w-10 px-4 pb-2 border-b-2 border-slate-300">
                        <input type="checkbox" class="rounded border-gray-300 text-[#1a3c8f] focus:ring-[#1a3c8f]">
                    </th>
                    <th class="text-[#1a3c8f] text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300">Requisition No.</th>
                    <th class="text-[#1a3c8f] text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300">Requestor ↑</th>
                    <th class="text-[#1a3c8f] text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300">Office/Unit</th>
                    <th class="text-[#1a3c8f] text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300">Assigned Personnel</th>
                    <th class="text-[#1a3c8f] text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300">Priority</th>
                    <th class="text-[#1a3c8f] text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300">Status</th>
                    <th class="text-[#1a3c8f] text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300">Date Requested</th>
                    <th class="text-[#1a3c8f] text-[11px] font-bold uppercase pb-2 border-b-2 border-slate-300">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $r)
                <tr class="bg-white hover:bg-gray-50 transition shadow-sm group">
                    <td class="px-4 py-4 border-y border-l border-gray-200 rounded-l-lg">
                        <input type="checkbox" class="rounded border-gray-300 text-[#1a3c8f] focus:ring-[#1a3c8f]">
                    </td>
                    <td class="py-4 border-y border-gray-200">
                        @php
                            $catName = strtolower($r->category->category_name ?? '');
                            $prefix = match(true) {
                                str_contains($catName, 'landscaping') => 'LS',
                                str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
                                str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
                                str_contains($catName, 'plumbing') => 'PS',
                                default => 'REQ'
                            };
                        @endphp
                        <div class="text-[#1a3c8f] font-bold text-[13px]">{{ $prefix }}-{{ str_pad($r->request_id, 3, '0', STR_PAD_LEFT) }}</div>
                        <div class="text-gray-500 text-[11px] mt-0.5">{{ $r->category->category_name ?? 'General' }}</div>
                    </td>
                    <td class="py-4 border-y border-gray-200">
                        <div class="text-[#1a3c8f] font-bold text-[13px]">{{ $r->client->user->first_name ?? 'Jane' }} {{ $r->client->user->last_name ?? 'Doe' }}</div>
                        <div class="text-gray-500 text-[11px] mt-0.5">{{ $r->client->user->email_account ?? 'email@bicol-u.edu.ph' }}</div>
                    </td>
                    <td class="py-4 border-y border-gray-200">
                        <div class="text-[#1a3c8f] font-bold text-[13px]">{{ strtok($r->location, ' ') ?? 'BU COA' }}</div>
                        <div class="text-gray-500 text-[11px] mt-0.5 max-w-[120px] truncate">{{ $r->location ?? '--' }}</div>
                    </td>
                    <!-- Assigned Personnel Column -->
                    <td class="py-4 border-y border-gray-200">
                        @php
                            $assignedWorkers = $r->project?->workers ?? collect();
                        @endphp
                        @if($assignedWorkers->isNotEmpty())
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @foreach($assignedWorkers as $w)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-[#0038A8] border border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800">
                                        <span class="w-4 h-4 rounded-full bg-[#0038A8] text-white flex items-center justify-center text-[9px] font-bold shrink-0">
                                            {{ strtoupper(substr($w->staff->user->first_name ?? 'W', 0, 1)) }}
                                        </span>
                                        <span>{{ $w->staff->user->first_name ?? 'Worker' }} {{ $w->staff->user->last_name ?? '' }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-gray-400 italic">Not Assigned</span>
                        @endif
                    </td>
                    <td class="py-4 border-y border-gray-200">
                        @php 
                            $priClass = match(strtolower($r->priority ?? 'low')) { 
                                'high'=>'bg-red-50 text-red-600 border-red-200', 
                                'medium'=>'bg-yellow-50 text-yellow-600 border-yellow-300', 
                                default=>'bg-green-50 text-green-600 border-green-300' 
                            }; 
                        @endphp
                        <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold border {{ $priClass }}">
                            {{ ucfirst($r->priority ?? 'Low') }}
                        </span>
                    </td>
                    <td class="py-4 border-y border-gray-200">
                        @php
                            $s = $r->current_status;
                            $sClass = match($s) {
                                'Pending', 'Approved'=>'bg-orange-50 text-orange-600 border-orange-300',
                                'On Hold'=>'bg-orange-50 text-orange-600 border-orange-300',
                                'In Progress', 'Pending Verification'=>'bg-yellow-50 text-yellow-600 border-yellow-300',
                                'Completed'=>'bg-green-50 text-green-600 border-green-300',
                                default=>'bg-gray-50 text-gray-600 border-gray-300'
                            };
                        @endphp
                        <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold border {{ $sClass }}">
                            {{ $s }}
                        </span>
                    </td>
                    <td class="py-4 border-y border-gray-200">
                        <span class="text-[#1a3c8f] font-bold text-[13px]">{{ \Carbon\Carbon::parse($r->submitted_at)->format('m/d/Y') }}</span>
                    </td>
                    <td class="px-4 py-4 border-y border-r border-gray-200 rounded-r-lg">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.requests.show', $r->request_id) }}" class="text-[#1a3c8f] hover:text-blue-600 transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <button class="text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-8 text-gray-500">No requests found matching your filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    </div>
</div>
