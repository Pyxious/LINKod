@php
    $logoutUser = auth()->user();
    $logoutInitial = strtoupper(substr($logoutUser?->first_name ?? 'U', 0, 1));
    $logoutFullName = trim(($logoutUser?->first_name ?? '') . ' ' . ($logoutUser?->last_name ?? '')) ?: 'LINKod User';
    $logoutEmail = $logoutUser?->email_account ?? '';
@endphp

<div x-data="{ showLogoutModal: false, loggingOut: false }"
     @open-logout-modal.window="showLogoutModal = true"
     @keydown.escape.window="if (showLogoutModal && !loggingOut) showLogoutModal = false">
    <div x-show="showLogoutModal"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100000] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs font-sans">
        <div x-show="showLogoutModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-1"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-1"
             @click.outside="if (!loggingOut) showLogoutModal = false"
             class="relative bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xl w-full max-w-[410px] overflow-hidden text-left">

            <!-- Brand Header Banner (BU Cream & Navy) -->
            <div class="flex items-center justify-between px-6 py-4 bg-[#FFFDE6] dark:bg-[#18181b] border-b border-[#e5e1b0]/80 dark:border-zinc-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#0033a0]/10 dark:bg-blue-500/15 border border-[#0033a0]/20 dark:border-blue-500/30 text-[#0033a0] dark:text-blue-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-[#042B74] dark:text-white font-extrabold text-[15px] leading-tight">Sign Out of LINKod?</h4>
                        <p class="text-slate-500 dark:text-gray-400 text-[11px] font-medium mt-0.5">BU-GSO Session Confirmation</p>
                    </div>
                </div>
                <button type="button"
                        @click="if (!loggingOut) showLogoutModal = false"
                        :disabled="loggingOut"
                        class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-amber-200/50 dark:hover:bg-zinc-800 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition cursor-pointer disabled:opacity-50"
                        aria-label="Close">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-5 space-y-4">
                @auth
                    <!-- Active Account Strip (matches sidebar profile card) -->
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-[#f8faff] dark:bg-zinc-800/60 border border-dashed border-[#1a3c8f]/35 dark:border-zinc-700">
                        <div class="w-9 h-9 rounded-lg bg-[#0033a0] text-white font-bold text-sm flex items-center justify-center shrink-0 shadow-2xs">
                            {{ $logoutInitial }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-900 dark:text-white truncate leading-tight">{{ $logoutFullName }}</div>
                            @if($logoutEmail)
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ $logoutEmail }}</div>
                            @endif
                        </div>
                    </div>
                @endauth

                <p class="text-xs text-slate-600 dark:text-gray-300 leading-relaxed">
                    Are you sure you want to end your current session? You can sign back in anytime using your BU account.
                </p>
            </div>

            <!-- Footer Actions -->
            <form method="POST" action="{{ route('logout') }}"
                  @submit="loggingOut = true"
                  class="px-6 py-4 bg-slate-50/70 dark:bg-zinc-900/50 border-t border-gray-100 dark:border-zinc-800 flex items-center gap-2.5">
                @csrf
                <button type="button"
                        @click="showLogoutModal = false"
                        :disabled="loggingOut"
                        class="flex-1 px-4 py-2.5 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 border border-gray-300 dark:border-zinc-700 text-slate-700 dark:text-gray-200 text-xs font-bold rounded-xl transition cursor-pointer disabled:opacity-60">
                    Stay Signed In
                </button>
                <button type="submit"
                        :disabled="loggingOut"
                        data-no-auto-loading
                        class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 active:scale-[0.99] text-white text-xs font-extrabold rounded-xl shadow-sm hover:shadow transition inline-flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60">
                    <svg x-show="!loggingOut" class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <svg x-show="loggingOut" x-cloak class="animate-spin h-3.5 w-3.5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loggingOut ? 'Signing out...' : 'Sign Out'">Sign Out</span>
                </button>
            </form>
        </div>
    </div>
</div>
