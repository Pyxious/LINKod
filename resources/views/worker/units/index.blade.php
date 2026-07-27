@extends('layouts.worker')
@section('page-title', 'My Unit')

@section('content')

<!-- Header -->
<div class="bg-[#fefce8] border border-[#1a3c8f] rounded-xl px-8 py-6 mb-8 shadow-sm">
    <h1 class="text-[#1a3c8f] text-2xl font-bold mb-1">Units / Sections</h1>
    <p class="text-[#1a3c8f] text-sm opacity-90">Your assigned unit and team information</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Assigned Unit Info -->
    <div>
        <h2 class="text-[#1a3c8f] font-bold text-sm mb-3">My Assigned Unit</h2>
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-xl shrink-0 border border-gray-200">
                    🏗️
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-lg leading-tight">{{ $team->team_name }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Facilities Maintenance Section</p>
                </div>
            </div>

            <div class="flex border-t border-gray-100 pt-5 mb-6 text-center">
                <div class="flex-1 border-r border-gray-100">
                    <div class="text-2xl font-bold text-gray-900">{{ $activeRequestsCount }}</div>
                    <div class="text-[11px] text-gray-500 uppercase tracking-wide font-medium mt-1">Active Requests</div>
                </div>
                <div class="flex-1">
                    <div class="text-2xl font-bold text-gray-900">{{ $availableWorkersCount }}</div>
                    <div class="text-[11px] text-gray-500 uppercase tracking-wide font-medium mt-1">Available Workers</div>
                </div>
            </div>

            <a href="#" class="block w-full py-2.5 px-4 bg-[#f8faff] text-[#1a3c8f] font-medium text-sm text-center rounded-lg border border-[#1a3c8f]/20 hover:bg-blue-50 transition">
                View Unit Details
            </a>
        </div>
    </div>

    <!-- Team Leader and Members -->
    <div>
        <h2 class="text-[#1a3c8f] font-bold text-sm mb-3">Team Leader and Members</h2>
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm min-h-[250px]">
            <div class="space-y-4">
                <!-- Leader -->
                @if($team->leader && $team->leader->staff)
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center shrink-0 border border-gray-200">
                        <span class="text-gray-500 font-bold text-sm">{{ strtoupper(substr($team->leader->staff->user->first_name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <div class="font-bold text-gray-900 text-sm">{{ $team->leader->staff->user->first_name }} {{ $team->leader->staff->user->last_name }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Team Leader</div>
                    </div>
                </div>
                @endif
                
                <!-- Members -->
                @foreach($team->workers as $w)
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center shrink-0 border border-gray-200">
                        <span class="text-gray-500 font-bold text-sm">{{ strtoupper(substr($w->staff->user->first_name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <div class="font-bold text-gray-900 text-sm">{{ $w->staff->user->first_name }} {{ $w->staff->user->last_name }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Skilled Worker</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Current Deployments -->
    <div>
        <h2 class="text-[#1a3c8f] font-bold text-sm mb-3">Current Deployments</h2>
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm min-h-[250px] flex flex-col">
            <div class="space-y-3 flex-1">
                @forelse($deployments as $project)
                <div class="border border-gray-100 rounded-lg p-3 hover:bg-gray-50 transition">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-md flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900 text-sm leading-tight">{{ $project->request->title ?? 'Project #'.$project->project_id }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $project->request->category->category_name ?? 'Maintenance' }}</div>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium 
                            {{ $project->current_status === 'Completed' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $project->current_status }}
                        </span>
                    </div>
                    <div class="flex justify-end text-xs text-gray-500">
                        {{ $project->workers->count() }} workers
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-gray-500 text-sm">
                    No active deployments.
                </div>
                @endforelse
            </div>
            @if($deployments->count() > 0)
            <div class="mt-4 text-right">
                <a href="{{ route('worker.job-orders.index') }}" class="text-[#1a3c8f] text-sm font-bold hover:underline">View all deployments &rarr;</a>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
