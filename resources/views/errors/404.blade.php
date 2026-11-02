<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Page Not Found — BU-GSO LINKod</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-[#edf4fb] dark:bg-[#111111] text-slate-800 dark:text-gray-200 font-sans antialiased min-h-screen flex flex-col">

    <!-- Simple Top Brand Bar -->
    <header class="w-full bg-white dark:bg-[#18181b] border-b border-gray-200 dark:border-zinc-800 h-16 px-6 md:px-12 flex items-center justify-between">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
            <span class="text-[#0033a0] dark:text-blue-400 font-extrabold text-base sm:text-lg tracking-tight group-hover:opacity-85 transition">BU-GSO</span>
            <span class="text-gray-300 dark:text-zinc-700 font-light text-base">|</span>
            <img src="{{ asset('images/LINKOD logo.png') }}" alt="BU-GSO LINKod" class="h-9 w-auto">
        </a>
        <a href="{{ url('/faq') }}" class="text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-[#0033a0] dark:text-gray-400 dark:hover:text-blue-400 transition">
            Help &amp; FAQs
        </a>
    </header>

    <!-- Main 404 Card -->
    <main class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-lg bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-3xl shadow-xl p-8 sm:p-11 text-center space-y-6">
            <!-- Icon & 404 Pill -->
            <div class="space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 text-[#0033a0] dark:text-blue-400 flex items-center justify-center mx-auto shadow-2xs">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="inline-block px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 text-[11px] font-extrabold uppercase tracking-wider">
                    Error 404 • Page Not Found
                </span>
            </div>

            <!-- Heading & Description -->
            <div class="space-y-2">
                <h1 class="text-4xl sm:text-5xl font-black text-[#0033a0] dark:text-blue-400 tracking-tight leading-none">
                    404
                </h1>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white">
                    We couldn't find that page
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed max-w-sm mx-auto">
                    The page or service requisition link you are looking for may have been moved, removed, or the URL might be mistyped.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <button type="button"
                        onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ url('/') }}'; }"
                        class="w-full sm:w-auto px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-gray-200 border border-gray-200 dark:border-zinc-700 rounded-xl text-xs sm:text-sm font-bold transition inline-flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Go Back</span>
                </button>

                <a href="{{ url('/') }}"
                   class="w-full sm:w-auto px-6 py-2.5 bg-[#0033a0] hover:bg-[#002480] text-white rounded-xl text-xs sm:text-sm font-bold transition shadow-md inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Return to Home</span>
                </a>
            </div>

            <!-- Footer Links -->
            <div class="pt-4 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-center gap-4 text-xs text-gray-400">
                <a href="{{ url('/faq') }}" class="hover:text-[#0033a0] dark:hover:text-blue-400 transition">FAQs</a>
                <span>•</span>
                <a href="{{ url('/privacy-policy') }}" class="hover:text-[#0033a0] dark:hover:text-blue-400 transition">Privacy Policy</a>
                <span>•</span>
                <a href="{{ url('/terms-and-conditions') }}" class="hover:text-[#0033a0] dark:hover:text-blue-400 transition">Terms &amp; Conditions</a>
            </div>
        </div>
    </main>

</body>
</html>

