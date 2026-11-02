<div x-data="{
        visible: false,
        showCustomize: false,
        functionalCookies: true,
        storageKey: 'linkod_cookie_consent_v1',

        init() {
            const saved = this.readConsent();
            if (!saved) {
                setTimeout(() => { this.visible = true; }, 350);
            } else {
                this.functionalCookies = saved.functional !== false;
            }
        },

        readConsent() {
            try {
                const raw = localStorage.getItem(this.storageKey);
                if (raw) return JSON.parse(raw);
            } catch (e) {}

            const match = document.cookie.match(/(?:^|;\s*)linkod_cookie_consent=([^;]*)/);
            if (match && match[1]) {
                try {
                    return JSON.parse(decodeURIComponent(match[1]));
                } catch (e) {
                    return { essential: true, functional: match[1] === 'all' };
                }
            }
            return null;
        },

        persistConsent(functional) {
            const payload = {
                essential: true,
                functional: Boolean(functional),
                updatedAt: new Date().toISOString()
            };
            this.functionalCookies = Boolean(functional);

            try {
                localStorage.setItem(this.storageKey, JSON.stringify(payload));
                if (!functional) {
                    sessionStorage.removeItem('linkod_client_req_draft');
                }
            } catch (e) {}

            const maxAge = 60 * 60 * 24 * 365; // 1 year
            const secureFlag = window.location.protocol === 'https:' ? '; Secure' : '';
            document.cookie = 'linkod_cookie_consent=' + encodeURIComponent(JSON.stringify({
                essential: true,
                functional: Boolean(functional)
            })) + '; Max-Age=' + maxAge + '; Path=/; SameSite=Lax' + secureFlag;

            this.visible = false;
            this.showCustomize = false;
        },

        acceptAll() {
            this.persistConsent(true);
        },

        acceptEssentialOnly() {
            this.persistConsent(false);
        },

        savePreferences() {
            this.persistConsent(this.functionalCookies);
        },

        openSettings() {
            const saved = this.readConsent();
            if (saved) {
                this.functionalCookies = saved.functional !== false;
            }
            this.showCustomize = true;
            this.visible = true;
        }
    }"
    @open-cookie-settings.window="openSettings()"
    x-show="visible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
    x-cloak
    role="region"
    aria-label="Cookie consent notice"
    class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-6 sm:w-full sm:max-w-md md:max-w-lg z-[90] font-sans">

    <div class="bg-white/98 dark:bg-[#1c1c1e]/98 backdrop-blur-md border border-slate-200 dark:border-zinc-700/90 rounded-2xl shadow-2xl p-5 sm:p-6 space-y-4">
        <!-- Header -->
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/70 border border-blue-200 dark:border-blue-800 text-[#0033a0] dark:text-blue-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white leading-snug">
                    Cookie &amp; Privacy Notice
                </h3>
                <p class="text-xs text-slate-600 dark:text-gray-300 mt-1 leading-relaxed">
                    BU-GSO LINKod uses essential cookies to keep your BU Google Single Sign-On session secure, protect forms against CSRF attacks, and remember your portal preferences. Read our
                    <a href="{{ route('privacy.policy') }}" class="font-bold text-[#0033a0] dark:text-blue-400 hover:underline">Privacy Policy</a>
                    and
                    <a href="{{ route('terms.conditions') }}" class="font-bold text-[#0033a0] dark:text-blue-400 hover:underline">Terms &amp; Conditions</a>.
                </p>
            </div>
        </div>

        <!-- Expandable Preferences Panel -->
        <div x-show="showCustomize"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             x-cloak
             class="pt-3 border-t border-gray-100 dark:border-zinc-800 space-y-2.5">
            <!-- Strictly Necessary -->
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/60 border border-gray-200/80 dark:border-zinc-700/80 flex items-start justify-between gap-3">
                <div>
                    <div class="text-xs font-bold text-slate-900 dark:text-white">Strictly Necessary Cookies</div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 leading-snug">
                        Required for authentication, 2FA verification, CSRF protection, and role security.
                    </p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 shrink-0">
                    Always On
                </span>
            </div>

            <!-- Functional Preferences -->
            <label class="p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/60 border border-gray-200/80 dark:border-zinc-700/80 flex items-start justify-between gap-3 cursor-pointer">
                <div>
                    <div class="text-xs font-bold text-slate-900 dark:text-white">Functional &amp; UI Preferences</div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 leading-snug">
                        Stores light/dark display mode and temporary requisition form drafts on your device.
                    </p>
                </div>
                <input type="checkbox"
                       x-model="functionalCookies"
                       class="mt-0.5 w-4 h-4 rounded border-gray-300 dark:border-zinc-600 text-[#0033a0] focus:ring-[#0033a0] shrink-0 cursor-pointer">
            </label>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pt-1">
            <button type="button"
                    @click="showCustomize = !showCustomize"
                    class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-gray-400 dark:hover:text-gray-200 underline underline-offset-2 transition text-left cursor-pointer">
                <span x-text="showCustomize ? 'Hide Preferences' : 'Customize'">Customize</span>
            </button>

            <div class="flex items-center gap-2 justify-end">
                <button type="button"
                        @click="acceptEssentialOnly()"
                        class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-gray-200 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs font-bold transition cursor-pointer">
                    Essential Only
                </button>
                <button type="button"
                        x-show="showCustomize"
                        @click="savePreferences()"
                        class="px-4 py-2 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                    Save Choices
                </button>
                <button type="button"
                        x-show="!showCustomize"
                        @click="acceptAll()"
                        class="px-4 py-2 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                    Accept All
                </button>
            </div>
        </div>
    </div>
</div>
