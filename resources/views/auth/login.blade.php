<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BU-GSO LINKod — Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#edf2f8] font-sans antialiased min-h-screen flex flex-col items-center justify-center p-4 selection:bg-[#0038A8] selection:text-white">

    <div class="w-full max-w-[490px] flex flex-col items-center">
        <!-- LINKod Brand Logo (Centered at top) -->
        <a href="{{ route('home') }}" class="mb-6 sm:mb-8 transition hover:opacity-90 inline-block focus:outline-none focus:ring-2 focus:ring-[#0038A8] rounded-xl">
            <img src="{{ asset('images/LINKOD logo.png') }}" alt="BU-GSO LINKod Logo" class="h-10 sm:h-12 w-auto mx-auto block">
        </a>

        <!-- Sign-In Card Container -->
        <div class="w-full bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-[0_4px_25px_rgba(0,0,0,0.03)] p-7 sm:p-9">
            
            <!-- Back to Home Link -->
            <div class="mb-6">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-slate-500 hover:text-slate-900 transition focus:outline-none">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Back to home</span>
                </a>
            </div>

            <!-- Role / Account Header -->
            <div class="flex items-start gap-3.5 mb-6 text-left">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0038A8] border border-blue-100 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                    <svg class="w-5 h-5 text-[#0038A8]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 10h10M7 14h6"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">GSO Staff and BU Personnel Clients</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">Welcome back! Sign in with your BU email account to access LINKod.</p>
                </div>
            </div>

            <!-- Error Banner (if any) -->
            @if(session('error'))
                <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-xs font-semibold text-red-700 text-center">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Sign In with Google Button -->
            <a href="{{ route('google.redirect') }}" class="w-full py-3 px-4 bg-white hover:bg-slate-50 active:bg-slate-100 border border-slate-200 hover:border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 flex items-center justify-center gap-2.5 transition shadow-xs hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-[#0038A8]/20">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Sign In with Google (@bicol-u.edu.ph)</span>
            </a>

            <!-- Contact Support Help Text -->
            <p class="mt-6 text-center text-xs text-slate-400">
                Forgot your BU email password? <a href="https://icto.bicol-u.edu.ph/request/email-password-reset" target="_blank" class="text-slate-500 hover:text-slate-800 underline underline-offset-2 transition">Contact Support</a>
            </p>
        </div>

        <!-- Terms of Service & Privacy Policy Footer -->
        <p class="mt-6 sm:mt-7 text-center text-xs text-slate-400">
            By signing in, you agree to our <a href="#" class="underline underline-offset-2 hover:text-slate-600 transition">Terms of Service</a> and <a href="#" class="underline underline-offset-2 hover:text-slate-600 transition">Privacy Policy</a> .
        </p>
    </div>

</body>
</html>
