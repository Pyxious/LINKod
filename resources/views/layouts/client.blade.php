<!DOCTYPE html>
<html lang="en" class="scroll-smooth scroll-pt-16">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'BU-GSO LINKod' }}</title>
    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
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
<body class="bg-[#e8eef7] dark:bg-[#111111] min-h-screen text-slate-800 dark:text-gray-200 antialiased flex flex-col transition-colors duration-200">
    <!-- Top Nav -->
    <nav class="bg-white dark:bg-[#18181b] border-b border-gray-200 dark:border-zinc-800 h-16 flex items-center justify-between px-6 md:px-12 sticky top-0 z-50 shadow-sm">
        <!-- Left: Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <img src="{{ asset('images/LINKOD logo.png') }}" alt="BU-GSO LINKOD" class="h-9 w-auto">
        </a>

        <!-- Center: Navigation Links -->
        @if(request()->routeIs('portal.select'))
        <div class="hidden md:flex items-center gap-8 h-full"
             x-data="{ 
                 activeTab: 'home',
                 init() {
                     if (window.location.hash === '#about') this.activeTab = 'about';
                     window.addEventListener('hashchange', () => {
                         if (window.location.hash === '#about') this.activeTab = 'about';
                         else this.activeTab = 'home';
                     });
                 }
             }">
            <!-- HOME (Selection) -->
            <a href="{{ route('portal.select') }}" 
               @click="activeTab = 'home'"
               :class="activeTab === 'home' ? 'text-[#0033a0] dark:text-blue-400 font-bold border-b-2 border-[#0033a0] dark:border-blue-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-semibold'"
               class="h-full flex items-center px-2 text-xs uppercase tracking-wider transition">
                HOME
            </a>

            <!-- CLIENT PORTAL -->
            <a href="{{ route('client.dashboard') }}" 
               @click="activeTab = 'client'"
               :class="activeTab === 'client' ? 'text-[#0033a0] dark:text-blue-400 font-bold border-b-2 border-[#0033a0] dark:border-blue-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-semibold'"
               class="h-full flex items-center px-2 text-xs uppercase tracking-wider transition">
                CLIENT PORTAL
            </a>

            <!-- WORKER PORTAL -->
            <a href="{{ route('worker.dashboard') }}" 
               @click="activeTab = 'worker'"
               :class="activeTab === 'worker' ? 'text-[#0033a0] dark:text-blue-400 font-bold border-b-2 border-[#0033a0] dark:border-blue-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-semibold'"
               class="h-full flex items-center px-2 text-xs uppercase tracking-wider transition">
                WORKER PORTAL
            </a>

            <!-- ABOUT -->
            <a href="#about" 
               @click="activeTab = 'about'"
               :class="activeTab === 'about' ? 'text-[#0033a0] dark:text-blue-400 font-bold border-b-2 border-[#0033a0] dark:border-blue-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-semibold'"
               class="h-full flex items-center px-2 text-xs uppercase tracking-wider transition">
                ABOUT
            </a>
        </div>
        @else
        <div class="hidden md:flex items-center gap-8 h-full"
             x-data="{ 
                 activeTab: '{{ request()->routeIs('client.requests.*') ? 'track' : 'home' }}',
                 init() {
                     if (window.location.hash === '#services') this.activeTab = 'services';
                     else if (window.location.hash === '#about') this.activeTab = 'about';
                     
                     window.addEventListener('hashchange', () => {
                         if (window.location.hash === '#services') this.activeTab = 'services';
                         else if (window.location.hash === '#about') this.activeTab = 'about';
                         else if (window.location.pathname === '/') this.activeTab = 'home';
                     });
                 }
             }">
            <!-- HOME -->
            <a href="{{ route('home') }}" 
               @click="activeTab = 'home'"
               :class="activeTab === 'home' ? 'text-[#0033a0] dark:text-blue-400 font-bold border-b-2 border-[#0033a0] dark:border-blue-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-semibold'"
               class="h-full flex items-center px-2 text-xs uppercase tracking-wider transition">
                HOME
            </a>

            <!-- SERVICES (Always goes to main page #services section) -->
            <a href="{{ route('home') }}#services" 
               @click="activeTab = 'services'"
               :class="activeTab === 'services' ? 'text-[#0033a0] dark:text-blue-400 font-bold border-b-2 border-[#0033a0] dark:border-blue-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-semibold'"
               class="h-full flex items-center px-2 text-xs uppercase tracking-wider transition">
                SERVICES
            </a>

            <!-- TRACK -->
            <a href="{{ route('client.requests.index') }}" 
               @click="activeTab = 'track'"
               :class="activeTab === 'track' ? 'text-[#0033a0] dark:text-blue-400 font-bold border-b-2 border-[#0033a0] dark:border-blue-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-semibold'"
               class="h-full flex items-center px-2 text-xs uppercase tracking-wider transition">
                TRACK
            </a>

            <!-- ABOUT -->
            <a href="{{ route('home') }}#about" 
               @click="activeTab = 'about'"
               :class="activeTab === 'about' ? 'text-[#0033a0] dark:text-blue-400 font-bold border-b-2 border-[#0033a0] dark:border-blue-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-semibold'"
               class="h-full flex items-center px-2 text-xs uppercase tracking-wider transition">
                ABOUT
            </a>
        </div>
        @endif

        <!-- Right: Avatar + Dropdown -->
        <div class="flex items-center gap-3">
            <!-- Theme Toggle -->
            <button onclick="toggleTheme()" class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-zinc-800 transition mr-1">
                <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
            </button>
            @auth
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-1.5 cursor-pointer p-1 rounded-full hover:bg-gray-100 dark:hover:bg-zinc-800 transition">
                    <div class="w-9 h-9 bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-white rounded-full flex items-center justify-center font-bold text-[14px] border border-gray-300 dark:border-zinc-600 relative">
                        {{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}
                        @if(isset($unreadCount) && $unreadCount > 0)
                            <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-white text-[10px] font-bold flex items-center justify-center border border-white">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </div>
                    <!-- Chevron -->
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" 
                     @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     x-cloak
                     class="absolute right-0 top-[calc(100%+8px)] bg-white dark:bg-[#1a1a1a] rounded-xl shadow-2xl min-w-[200px] overflow-hidden z-50 border border-gray-100 dark:border-gray-800">
                    <div class="px-4 pt-3 pb-2 text-[13px] text-gray-500 dark:text-gray-400 truncate">
                        {{ auth()->user()->email_account }}
                    </div>
                    <hr class="border-gray-100 dark:border-gray-700">
                    @if(auth()->user()->role === 'worker')
                    <a href="{{ route('portal.select') }}" class="block w-full px-4 py-2.5 text-left text-sm font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-800 transition border-b border-gray-100 dark:border-gray-700">
                        Switch Portal / Services
                    </a>
                    @endif
                    <a href="{{ route('profile.index') }}" class="block w-full px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        My Profile Settings
                    </a>
                    <hr class="border-gray-100 dark:border-gray-700">
                    <a href="{{ route('client.notifications.index') }}" class="block w-full px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        Notifications
                    </a>
                    <hr class="border-gray-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 font-medium transition">
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
            @else
            <!-- Unauthenticated Profile Icon / Login Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-1 cursor-pointer p-1 text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                    </svg>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="open" 
                     @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     x-cloak
                     class="absolute right-0 top-[calc(100%+8px)] bg-white dark:bg-[#1a1a1a] rounded-xl shadow-xl min-w-[160px] p-2 z-50 border border-gray-100 dark:border-gray-800">
                    <a href="{{ route('login') }}" class="block w-full px-4 py-2 text-center text-sm font-semibold text-white bg-[#0033a0] rounded-lg hover:bg-[#002480] transition">
                        Sign In
                    </a>
                </div>
            </div>
            @endauth
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 @hasSection('fullwidth') @else flex flex-col items-center justify-center @endif min-h-[calc(100vh-56px)]">
        @if(session('success'))
            <div class="max-w-2xl mx-auto mt-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2.5 rounded-lg text-sm text-center shadow-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="max-w-2xl mx-auto mt-4 bg-red-100 border border-red-300 text-red-800 px-4 py-2.5 rounded-lg text-sm text-center shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="w-full mt-auto flex flex-col font-sans">
        <!-- Main Footer -->
        <div class="bg-[#041a40] text-white py-12 px-6 md:px-16 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 text-xs">
            <!-- Col 1: Brand -->
            <div class="flex flex-col items-start">
                <div class="flex items-center gap-3 mb-2">
                    <img src="{{ asset('images/BUlogo.png') }}" alt="BU Seal" class="h-10 w-auto">
                    <div class="flex flex-col">
                        <span class="font-bold text-sm tracking-wide text-white">BU-GSO LINKOD</span>
                        <span class="text-gray-300 text-[11px]">General Services Office</span>
                    </div>
                </div>
            </div>

            <!-- Col 2: Contact Us -->
            <div>
                <h4 class="font-bold text-[11px] tracking-wider uppercase text-white mb-4">CONTACT US</h4>
                <ul class="space-y-2.5 text-gray-300 text-[11px]">
                    <li class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>0912 345 6789</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <a href="mailto:bu-gso@bicol-u.edu.ph" class="hover:underline">bu-gso@bicol-u.edu.ph</a>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <a href="#" class="underline text-gray-300 hover:text-white leading-snug">Bicol University Main Campus, Legazpi City, Albay, Philippines</a>
                    </li>
                </ul>
            </div>

            <!-- Col 3: Quick Links -->
            <div>
                <h4 class="font-bold text-[11px] tracking-wider uppercase text-white mb-4">QUICK LINKS</h4>
                <ul class="space-y-2 text-gray-300 text-[11px]">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition">Home</a></li>
                    <li><a href="#services" class="hover:text-white transition">Services</a></li>
                    <li><a href="{{ route('client.requests.index') }}" class="hover:text-white transition">Track Request</a></li>
                </ul>
            </div>

            <!-- Col 4: Help -->
            <div>
                <h4 class="font-bold text-[11px] tracking-wider uppercase text-white mb-4">HELP</h4>
                <ul class="space-y-2 text-gray-300 text-[11px]">
                    <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-white transition">Technical Support</a></li>
                    <li><a href="#" class="hover:text-white transition">FAQs</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="bg-[#020d24] py-3 text-center text-gray-300 text-[11px]">
            © 2026 Bicol University – General Services Office. All rights reserved.
        </div>
    </footer>

    @livewireScripts
    <script>
        // Set initial icon state on load
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            var icon = document.getElementById('theme-toggle-light-icon');
            if(icon) icon.classList.remove('hidden');
        } else {
            var icon = document.getElementById('theme-toggle-dark-icon');
            if(icon) icon.classList.remove('hidden');
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
</body>
</html>
