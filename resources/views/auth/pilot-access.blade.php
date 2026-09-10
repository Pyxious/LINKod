<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pilot Testing</title>
    <link rel="icon" type="image/png" href="{{ asset('images/LOGO.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 dark:bg-[#0f1117] text-slate-900 dark:text-slate-100 antialiased flex items-center justify-center p-4">
    
    <div class="w-full max-w-md" x-data="{ showCode: false, loading: false }">
        
        <!-- Main Card -->
        <div class="bg-white dark:bg-[#181a20] border border-slate-200 dark:border-zinc-800/80 rounded-2xl p-6 sm:p-8 shadow-xl shadow-slate-200/50 dark:shadow-none space-y-6">
            
            <!-- Header Icon & Status Badge -->
            <div class="flex flex-col items-center text-center space-y-3">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-950/60 border border-blue-100 dark:border-blue-900/50 flex items-center justify-center text-[#0033a0] dark:text-blue-400 shadow-xs">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>

                <div class="space-y-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-[#0033a0] dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/50">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0033a0] dark:bg-blue-400 animate-pulse"></span>
                        Restricted Access
                    </span>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white pt-1">
                        Pilot Testing
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 max-w-xs mx-auto leading-relaxed">
                        Please enter the access code to continue to the testing environment.
                    </p>
                </div>
            </div>

            <!-- Error Banner -->
            @if(session('error'))
                <div class="p-3.5 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 flex items-start gap-3 text-red-700 dark:text-red-300 text-xs animate-shake">
                    <svg class="w-4 h-4 shrink-0 text-red-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium leading-snug">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('pilot.access.verify') }}" @submit="loading = true" class="space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <label for="access_code" class="block text-xs font-bold text-slate-700 dark:text-zinc-300">
                        Access Code
                    </label>
                    
                    <div class="relative">
                        <input :type="showCode ? 'text' : 'password'" 
                               id="access_code"
                               name="access_code" 
                               required 
                               autofocus
                               placeholder="Enter access code"
                               class="w-full pl-4 pr-11 py-3 text-sm font-semibold rounded-xl bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 focus:bg-white dark:focus:bg-zinc-900 focus:outline-none focus:border-[#0033a0] focus:ring-2 focus:ring-[#0033a0]/20 dark:focus:ring-blue-500/20 text-slate-900 dark:text-white transition">
                        
                        <button type="button" 
                                @click="showCode = !showCode" 
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 transition focus:outline-none"
                                title="Toggle visibility">
                            <svg x-show="!showCode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showCode" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                        :disabled="loading" 
                        class="w-full py-3 px-4 bg-[#0033a0] hover:bg-[#002880] active:bg-[#002066] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition duration-150 shadow-md shadow-[#0033a0]/20 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                    <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? 'Verifying...' : 'Continue'"></span>
                </button>
            </form>

            <div class="pt-2 text-center border-t border-slate-100 dark:border-zinc-800">
                <p class="text-[11px] text-slate-400 dark:text-zinc-500">
                    Authorized testing personnel only.
                </p>
            </div>

        </div>

    </div>

</body>
</html>
