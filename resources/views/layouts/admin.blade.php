<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin — BU-GSO LINKod' }}</title>
    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind & Livewire -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; }
        [x-cloak] { display: none !important; }
    </style>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @stack('styles')
</head>
<body class="bg-[#f0f4f8] dark:bg-[#111111] text-slate-800 dark:text-gray-200 antialiased flex min-h-screen transition-colors duration-200">
    
    <!-- Sidebar -->
    <aside class="fixed top-0 left-0 h-screen w-56 bg-white dark:bg-[#1a1a1a] border-r border-gray-200 dark:border-gray-800 flex flex-col z-50 transition-colors duration-200">
        <!-- Logo -->
        <div class="px-5 pt-6 pb-5 border-b border-[#f0f0e0] dark:border-gray-800 flex justify-between items-center">
            <img src="{{ asset('images/LINKOD logo.png') }}" alt="BUGSO LINKOD Logo" class="h-10 w-auto object-contain">
            <!-- Dark Mode Toggle inside sidebar top -->
            <button onclick="toggleTheme()" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition">
                <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 flex flex-col gap-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#f0f6ff] dark:bg-gray-800 text-[#1a3c8f] dark:text-white font-bold border border-[#1a3c8f] dark:border-gray-700 shadow-sm' : 'text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                <img src="{{ asset('images/DASHBOARD LOGO.png') }}" class="w-5 h-5 shrink-0 object-contain dark:invert" alt="Dashboard">
                Dashboard
            </a>

            <a href="{{ route('admin.users.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-[#f0f6ff] dark:bg-gray-800 text-[#1a3c8f] dark:text-white font-bold border border-[#1a3c8f] dark:border-gray-700 shadow-sm' : 'text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                <img src="{{ asset('images/USERS LOGO.png') }}" class="w-5 h-5 shrink-0 object-contain dark:invert" alt="Users">
                Users
            </a>

            <a href="{{ route('admin.workforce.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.workforce.*') ? 'bg-[#f0f6ff] dark:bg-gray-800 text-[#1a3c8f] dark:text-white font-bold border border-[#1a3c8f] dark:border-gray-700 shadow-sm' : 'text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                <img src="{{ asset('images/UNITS Logo.png') }}" class="w-5 h-5 shrink-0 object-contain dark:invert" alt="Units">
                Units
            </a>

            <a href="{{ route('admin.requests.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.requests.*') ? 'bg-[#f0f6ff] dark:bg-gray-800 text-[#1a3c8f] dark:text-white font-bold border border-[#1a3c8f] dark:border-gray-700 shadow-sm' : 'text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                <img src="{{ asset('images/REQUESTS LOGO.png') }}" class="w-5 h-5 shrink-0 object-contain dark:invert" alt="Requests">
                Requests
            </a>

            <a href="{{ route('admin.materials.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.materials.*') ? 'bg-[#f0f6ff] dark:bg-gray-800 text-[#1a3c8f] dark:text-white font-bold border border-[#1a3c8f] dark:border-gray-700 shadow-sm' : 'text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                <img src="{{ asset('images/MESSAGES LOGO.png') }}" class="w-5 h-5 shrink-0 object-contain dark:invert" alt="Messages">
                Messages
            </a>

            <a href="{{ route('admin.reports.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.reports.*') ? 'bg-[#f0f6ff] dark:bg-gray-800 text-[#1a3c8f] dark:text-white font-bold border border-[#1a3c8f] dark:border-gray-700 shadow-sm' : 'text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                <img src="{{ asset('images/REPORTS LOGO.png') }}" class="w-5 h-5 shrink-0 object-contain dark:invert" alt="Reports">
                Reports
            </a>

            <a href="{{ route('admin.audit.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.audit.*') ? 'bg-[#f0f6ff] dark:bg-gray-800 text-[#1a3c8f] dark:text-white font-bold border border-[#1a3c8f] dark:border-gray-700 shadow-sm' : 'text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                <svg class="w-5 h-5 shrink-0 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Audit Logs
            </a>
        </nav>

        <!-- Footer Profile using Alpine JS for toggle -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-800" x-data="{ open: false }">
            <div class="relative">
                <div @click="open = !open" class="flex items-center gap-3 border-2 border-dashed border-[#1a3c8f] dark:border-gray-600 rounded-xl p-3 bg-[#f8faff] dark:bg-gray-800/50 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-800 transition">
                    <div class="w-9 h-9 shrink-0 border border-[#1a3c8f] dark:border-gray-500 bg-white dark:bg-gray-700 text-[#1a3c8f] dark:text-white rounded-full flex items-center justify-center font-bold">
                        {{ strtoupper(substr(auth()->user()->first_name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-[13px] font-bold text-[#1a3c8f] dark:text-gray-200 truncate">Admin</div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->email_account }}</div>
                    </div>
                </div>

                <!-- Dropdown Menu -->
                <div x-show="open" 
                     @click.outside="open = false" 
                     x-transition 
                     x-cloak
                     class="absolute bottom-full left-0 mb-2 w-full bg-white dark:bg-[#222222] border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-hidden z-50">
                    <a href="{{ route('profile.index') }}" class="block w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition border-b border-gray-100 dark:border-gray-700">
                        My Profile Settings
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-3 text-sm text-red-600 dark:text-red-400 font-medium hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="ml-56 flex-1 flex flex-col min-h-screen">
        <main class="p-7 flex-1">
            @if(session('success'))
                <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg mb-6 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg mb-6 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @livewireScripts
    <script>
        // Set initial icon state on load
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.getElementById('theme-toggle-light-icon').classList.remove('hidden');
        } else {
            document.getElementById('theme-toggle-dark-icon').classList.remove('hidden');
        }

        function toggleTheme() {
            var darkIcon = document.getElementById('theme-toggle-dark-icon');
            var lightIcon = document.getElementById('theme-toggle-light-icon');
            darkIcon.classList.toggle('hidden');
            lightIcon.classList.toggle('hidden');

            if (localStorage.getItem('theme')) {
                if (localStorage.getItem('theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }
            } else {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
