<!DOCTYPE html>
<html lang="en" class="scroll-smooth scroll-pt-16">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'BU-GSO LINKod' }}</title>
    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    <meta name="supabase-url" content="{{ config('services.supabase.url') }}">
    <meta name="supabase-anon-key" content="{{ config('services.supabase.anon_key') }}">
    
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
                 activeTab: '{{ request()->routeIs('portal.select') ? 'home' : '' }}',
                 init() {
                     const updateTab = () => {
                         const path = window.location.pathname;
                         if (path === '/portal-select') this.activeTab = 'home';
                         else if (path === '/client/dashboard') this.activeTab = 'client';
                         else if (path === '/worker/dashboard') this.activeTab = 'worker';
                         else if (path === '/faq') this.activeTab = 'faq';
                         else this.activeTab = '';
                     };
                     updateTab();
                     window.addEventListener('hashchange', updateTab);
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

            <!-- FAQ -->
            <a href="{{ route('faq') }}" 
               @click="activeTab = 'faq'"
               :class="activeTab === 'faq' ? 'text-[#0033a0] dark:text-blue-400 font-bold border-b-2 border-[#0033a0] dark:border-blue-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-semibold'"
               class="h-full flex items-center px-2 text-xs uppercase tracking-wider transition">
                FAQ
            </a>
        </div>
        @else
        <div class="hidden md:flex items-center gap-8 h-full"
             x-data="{ 
                 activeTab: '{{ request()->routeIs('home') ? 'home' : (request()->routeIs('faq') ? 'faq' : '') }}',
                 init() {
                     const updateTab = () => {
                         const path = window.location.pathname;
                         const hash = window.location.hash;

                         if (path === '/faq') {
                             this.activeTab = 'faq';
                         } else if (path === '/') {
                             if (hash === '#services') this.activeTab = 'services';
                             else if (hash === '#track') this.activeTab = 'track';
                             else if (hash === '#faq') this.activeTab = 'faq';
                             else this.activeTab = 'home';
                         } else {
                             this.activeTab = '';
                         }
                     };
                     updateTab();
                     window.addEventListener('hashchange', updateTab);
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
            <a href="{{ route('home') }}#track" 
               @click="activeTab = 'track'"
               :class="activeTab === 'track' ? 'text-[#0033a0] dark:text-blue-400 font-bold border-b-2 border-[#0033a0] dark:border-blue-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-semibold'"
               class="h-full flex items-center px-2 text-xs uppercase tracking-wider transition">
                TRACK
            </a>

            <!-- FAQ -->
            <a href="{{ route('faq') }}" 
               @click="activeTab = 'faq'"
               :class="activeTab === 'faq' ? 'text-[#0033a0] dark:text-blue-400 font-bold border-b-2 border-[#0033a0] dark:border-blue-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-semibold'"
               class="h-full flex items-center px-2 text-xs uppercase tracking-wider transition">
                FAQ
            </a>
        </div>
        @endif

        @php
            $clientUser = auth()->user();
            $clientId = $clientUser?->client?->client_id;
            $navNotifications = $clientUser ? $clientUser->notifications()->where('type', '!=', 'new_message')->latest('sent_at')->take(5)->get() : collect();
            $navUnreadCount = $clientUser ? $clientUser->notifications()->where('type', '!=', 'new_message')->where('is_read', false)->count() : 0;
            
            $clientUnreadMessagesCount = $clientUser ? \App\Models\RequestMessage::where('is_read', false)
                ->where('sender_id', '!=', $clientUser->user_id)
                ->whereHas('serviceRequest', function($q) use ($clientId) {
                    if ($clientId) {
                        $q->where('client_id', $clientId);
                    }
                })
                ->count() : 0;
        @endphp

        <!-- Right: Notification Bell + Theme Toggle + Avatar -->
        <div class="flex items-center gap-2">
            <!-- Theme Toggle -->
            <button onclick="toggleTheme()" class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-zinc-800 transition">
                <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
            </button>

            @auth
            <!-- Notification Bell (Client Navbar) -->
            <div class="relative" x-data="{ openNotifs: false }">
                <button @click="openNotifs = !openNotifs" 
                        type="button" 
                        title="Notifications"
                        class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-zinc-800 transition relative focus:outline-none">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span data-notification-badge class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[9px] font-black rounded-full flex items-center justify-center border border-white dark:border-zinc-900 animate-pulse {{ ($navUnreadCount ?? 0) > 0 ? '' : 'hidden' }}">
                        {{ $navUnreadCount ?? 0 }}
                    </span>
                </button>

                <!-- Notifications Dropdown Popover (Client Navbar) -->
                <div x-show="openNotifs" 
                     @click.outside="openNotifs = false" 
                     x-transition 
                     x-cloak 
                     class="fixed left-4 right-4 sm:absolute sm:left-auto sm:right-0 top-16 sm:top-auto sm:mt-2 w-auto sm:w-80 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden z-50 text-left">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-zinc-800 flex justify-between items-center bg-[#f8faff] dark:bg-zinc-800/50">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-xs text-[#0033a0] dark:text-blue-400 uppercase tracking-wider">Notifications</span>
                            <span data-notification-header-count class="px-2 py-0.5 text-[10px] font-black bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 rounded-full {{ ($navUnreadCount ?? 0) > 0 ? '' : 'hidden' }}">
                                {{ $navUnreadCount ?? 0 }} New
                            </span>
                        </div>
                        @if(($navUnreadCount ?? 0) > 0)
                            <form method="POST" action="{{ route('client.notifications.mark-all-read') }}">
                                @csrf
                                <button type="submit" class="text-[11px] font-bold text-[#0033a0] dark:text-blue-400 hover:underline">
                                    Mark read
                                </button>
                            </form>
                        @endif
                    </div>
                    <div data-notification-list class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-zinc-800">
                        @forelse($navNotifications ?? [] as $notif)
                            <a href="{{ route('client.notifications.read', $notif->notification_id) }}" 
                               class="block px-4 py-3 hover:bg-blue-50/60 dark:hover:bg-zinc-800/60 transition {{ !$notif->is_read ? 'bg-blue-50/40 dark:bg-zinc-800/30' : '' }}">
                                <div class="flex items-start gap-2.5">
                                    <div class="w-2 h-2 mt-1.5 rounded-full shrink-0 {{ !$notif->is_read ? 'bg-[#0033a0] dark:bg-blue-400' : 'bg-gray-300 dark:bg-zinc-700' }}"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-baseline gap-2 mb-0.5">
                                            <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                                {{ $notif->title ?? 'Notification' }}
                                            </h4>
                                            <span class="text-[10px] text-gray-400 shrink-0">
                                                {{ \Carbon\Carbon::parse($notif->sent_at)->diffForHumans() }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-gray-600 dark:text-gray-300 line-clamp-2 leading-relaxed">
                                            {{ $notif->message }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div data-notification-empty class="p-6 text-center text-xs text-gray-400 dark:text-gray-500">
                                No notifications yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-1.5 cursor-pointer p-1 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 transition">
                    <div class="w-9 h-9 bg-[#0033a0] text-white rounded-md flex items-center justify-center font-bold text-sm shadow-sm relative">
                        {{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}
                    </div>
                    <!-- Chevron -->
                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-300 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
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
                     class="absolute right-0 top-[calc(100%+8px)] bg-white dark:bg-[#18181b] rounded-lg shadow-xl min-w-[240px] overflow-hidden z-50 border border-gray-200 dark:border-zinc-800 font-sans">
                    
                    <!-- Header Section: Avatar + Name + Role/Client Type -->
                    <div class="p-4 flex items-center gap-3">
                        <div class="w-11 h-11 bg-[#0033a0] text-white rounded-md flex items-center justify-center font-bold text-lg shrink-0 shadow-sm">
                            {{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-sm text-slate-900 dark:text-white truncate leading-tight">
                                {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium truncate mt-0.5">
                                {{ ucfirst(auth()->user()->client?->client_type ?? auth()->user()->role) }}
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-zinc-800"></div>

                    <!-- Items List -->
                    <div class="py-1">
                        @if(auth()->user()->role === 'worker')
                        <a href="{{ route('portal.select') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-[#0033a0] dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-zinc-800 transition">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            <span>SWITCH PORTAL</span>
                        </a>
                        @endif

                        <a href="{{ route('client.requests.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 022 2h2a2 2 0 022-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span>MY REQUESTS</span>
                        </a>

                        <a href="{{ route('client.messages.index') }}" class="flex items-center justify-between px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                <span>MESSAGES</span>
                            </div>
                            <span data-messages-badge class="bg-red-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-xs {{ $clientUnreadMessagesCount > 0 ? '' : 'hidden' }}">
                                {{ $clientUnreadMessagesCount > 99 ? '99+' : $clientUnreadMessagesCount }}
                            </span>
                        </a>

                        <a href="{{ route('profile.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>PROFILE SETTINGS</span>
                        </a>

                        <a href="{{ route('client.notifications.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span>NOTIFICATIONS</span>
                        </a>
                    </div>

                    <div class="border-t border-gray-100 dark:border-zinc-800"></div>

                    <!-- Sign Out Section -->
                    <div class="py-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition text-left cursor-pointer">
                                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>SIGN OUT</span>
                            </button>
                        </form>
                    </div>
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
        @unless(View::hasSection('hide_alerts'))
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
        @endunless

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
                    <li><a href="{{ route('faq') }}" class="hover:text-white transition">FAQs</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="bg-[#020d24] py-3 text-center text-gray-300 text-[11px]">
            © 2026 Bicol University – General Services Office. All rights reserved.
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
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
