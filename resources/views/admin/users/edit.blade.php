@extends('layouts.admin')
@section('page-title', 'Edit User Role')

@section('content')
<div class="w-full max-w-2xl mx-auto space-y-6 font-sans">
    
    <!-- Top Banner -->
    <div class="bg-[#FFFDE6] dark:bg-[#18181b] border border-amber-200/80 dark:border-zinc-800 rounded-2xl px-8 py-6 flex justify-between items-center shadow-2xs">
        <div>
            <h1 class="text-[#042B74] dark:text-blue-400 text-2xl font-bold mb-1">Edit User Role</h1>
            <p class="text-[#47658F] dark:text-gray-400 text-sm">Update system access privileges for {{ $user->first_name }} {{ $user->last_name }}</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-zinc-700 px-4 py-2 rounded-lg text-xs font-semibold hover:bg-gray-50 transition shadow-2xs">
            ← Back to Users
        </a>
    </div>

    <!-- Main Outer Container -->
    <div class="bg-[#EBF3FE] dark:bg-[#151d2a] border border-[#7DAAF4] dark:border-blue-800 rounded-2xl p-6 md:p-8 shadow-2xs">
        
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-6 md:p-8 shadow-2xs max-w-lg mx-auto">
            
            <!-- User Summary -->
            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100 dark:border-zinc-800">
                <div class="w-14 h-14 rounded-full bg-[#0038A8] text-white font-extrabold text-xl flex items-center justify-center shrink-0 shadow-2xs">
                    {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $user->first_name }} {{ $user->last_name }}</h3>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $user->email_account }}</p>
                </div>
            </div>

            <form action="{{ route('admin.users.update', $user->user_id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3">
                        Select Role <span class="text-red-500">*</span>
                    </label>

                    <div class="space-y-3" x-data="{ currentRole: '{{ old('role', $user->role) }}' }">
                        <!-- Client Role -->
                        <label class="flex items-start p-3.5 border rounded-xl cursor-pointer transition"
                               :class="currentRole === 'client' ? 'border-[#0038A8] bg-blue-50/50 dark:bg-blue-950/40 dark:border-blue-700' : 'border-gray-200 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50'">
                            <input type="radio" name="role" value="client" x-model="currentRole" class="mt-0.5 text-[#0038A8] focus:ring-[#0038A8]">
                            <div class="ml-3">
                                <span class="block text-xs font-bold text-gray-900 dark:text-white">Client</span>
                                <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Regular service requestor with tracking access</span>
                            </div>
                        </label>

                        <!-- Worker Role -->
                        <label class="flex items-start p-3.5 border rounded-xl cursor-pointer transition"
                               :class="currentRole === 'worker' ? 'border-[#0038A8] bg-blue-50/50 dark:bg-blue-950/40 dark:border-blue-700' : 'border-gray-200 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50'">
                            <input type="radio" name="role" value="worker" x-model="currentRole" class="mt-0.5 text-[#0038A8] focus:ring-[#0038A8]">
                            <div class="ml-3">
                                <span class="block text-xs font-bold text-gray-900 dark:text-white">Worker</span>
                                <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Field staff assigned to job orders & maintenance</span>
                            </div>
                        </label>

                        <!-- Administrator Role -->
                        <label class="flex items-start p-3.5 border rounded-xl cursor-pointer transition"
                               :class="currentRole === 'admin' ? 'border-[#0038A8] bg-blue-50/50 dark:bg-blue-950/40 dark:border-blue-700' : 'border-gray-200 dark:border-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-800/50'">
                            <input type="radio" name="role" value="admin" x-model="currentRole" class="mt-0.5 text-[#0038A8] focus:ring-[#0038A8]">
                            <div class="ml-3">
                                <span class="block text-xs font-bold text-gray-900 dark:text-white">Administrator</span>
                                <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Full access to system management & configuration</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 font-semibold text-xs rounded-lg hover:bg-gray-100 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-[#0038A8] hover:bg-[#002B82] text-white font-semibold text-xs rounded-lg shadow-xs transition">
                        Save Changes
                    </button>
                </div>
            </form>

        </div>

    </div>
</div>
@endsection
