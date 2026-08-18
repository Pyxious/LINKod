<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin — BU-GSO LINKod' }}</title>
    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    <meta name="supabase-url" content="{{ config('services.supabase.url') }}">
    <meta name="supabase-anon-key" content="{{ config('services.supabase.anon_key') }}">
    
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
<body x-data="{ sidebarOpen: false }" class="bg-[#f0f4f8] dark:bg-[#111111] text-slate-800 dark:text-gray-200 antialiased flex flex-col md:flex-row min-h-screen transition-colors duration-200">
    
    @php
        $navNotifications = auth()->check() ? auth()->user()->notifications()->where('type', '!=', 'new_message')->latest('sent_at')->take(5)->get() : collect();
        $navUnreadCount = auth()->check() ? auth()->user()->notifications()->where('type', '!=', 'new_message')->where('is_read', false)->count() : 0;
        $adminUnreadMessagesCount = auth()->check() ? \App\Models\RequestMessage::where('is_read', false)->where('sender_id', '!=', auth()->id())->count() : 0;
    @endphp

    <!-- Mobile Top Header Bar (visible only on mobile screens < md) -->
    <header class="md:hidden sticky top-0 z-40 bg-white dark:bg-[#1a1a1a] border-b border-gray-200 dark:border-gray-800 px-4 py-3 flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = !sidebarOpen" 
                    aria-label="Open Navigation Menu"
                    class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <img src="{{ asset('images/LINKOD logo.png') }}" alt="BUGSO LINKOD Logo" class="h-8 w-auto object-contain">
        </div>

        <div class="flex items-center gap-2">
            <!-- Notification Bell (Mobile) -->
            <div class="relative" x-data="{ openNotifs: false }">
                <button @click="openNotifs = !openNotifs" 
                        type="button" 
                        title="Notifications"
                        class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition relative focus:outline-none">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span data-notification-badge class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-black px-1.5 py-0.2 rounded-full border border-white dark:border-zinc-900 animate-pulse {{ ($navUnreadCount ?? 0) > 0 ? '' : 'hidden' }}">
                        {{ $navUnreadCount ?? 0 }}
                    </span>
                </button>

                <!-- Notifications Dropdown Popover (Mobile) -->
                <div x-show="openNotifs" 
                     @click.outside="openNotifs = false" 
                     x-transition 
                     x-cloak 
                     class="fixed left-4 right-4 sm:absolute sm:left-auto sm:right-0 top-14 sm:top-auto sm:mt-2 w-auto sm:w-80 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden z-50 text-left">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-zinc-800 flex justify-between items-center bg-[#f8faff] dark:bg-zinc-800/50">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-xs text-[#0033a0] dark:text-blue-400 uppercase tracking-wider">Notifications</span>
                            <span data-notification-header-count class="px-2 py-0.5 text-[10px] font-black bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 rounded-full {{ ($navUnreadCount ?? 0) > 0 ? '' : 'hidden' }}">
                                {{ $navUnreadCount ?? 0 }} New
                            </span>
                        </div>
                        @if(($navUnreadCount ?? 0) > 0)
                            <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
                                @csrf
                                <button type="submit" class="text-[11px] font-bold text-[#0033a0] dark:text-blue-400 hover:underline">
                                    Mark read
                                </button>
                            </form>
                        @endif
                    </div>
                    <div data-notification-list class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-zinc-800">
                        @forelse($navNotifications ?? [] as $notif)
                            <a href="{{ route('admin.notifications.read', $notif->notification_id) }}" 
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

            <!-- Theme Toggle Button -->
            <button onclick="toggleTheme()" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition">
                <svg class="theme-toggle-dark-icon w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg class="theme-toggle-light-icon w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
            </button>
        </div>
    </header>

    <!-- Mobile Backdrop Overlay -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/60 z-40 md:hidden" 
         x-cloak>
    </div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
           class="fixed top-0 left-0 h-screen w-56 bg-white dark:bg-[#1a1a1a] border-r border-gray-200 dark:border-gray-800 flex flex-col z-50 transition-transform duration-300 ease-in-out">
        <!-- Logo Header & Mobile Close Button -->
        <div class="px-5 pt-6 pb-5 border-b border-[#f0f0e0] dark:border-gray-800 flex justify-between items-center">
            <img src="{{ asset('images/LINKOD logo.png') }}" alt="BUGSO LINKOD Logo" class="h-10 w-auto object-contain">
            
            <div class="flex items-center gap-1">
                <!-- Notification Bell (Desktop Sidebar) -->
                <div class="relative hidden md:block" x-data="{ openNotifs: false }">
                    <button @click="openNotifs = !openNotifs" 
                            type="button" 
                            title="Notifications"
                            class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition relative focus:outline-none">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span data-notification-badge class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-black px-1.5 py-0.2 rounded-full border border-white dark:border-zinc-900 animate-pulse {{ ($navUnreadCount ?? 0) > 0 ? '' : 'hidden' }}">
                            {{ $navUnreadCount ?? 0 }}
                        </span>
                    </button>

                    <!-- Notifications Dropdown Popover (Desktop Sidebar) -->
                    <div x-show="openNotifs" 
                         @click.outside="openNotifs = false" 
                         x-transition 
                         x-cloak 
                         class="fixed left-[236px] top-5 w-80 sm:w-96 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden z-[999999] text-left">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-zinc-800 flex justify-between items-center bg-[#f8faff] dark:bg-zinc-800/50">
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-xs text-[#0033a0] dark:text-blue-400 uppercase tracking-wider">Notifications</span>
                                <span data-notification-header-count class="px-2 py-0.5 text-[10px] font-black bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 rounded-full {{ ($navUnreadCount ?? 0) > 0 ? '' : 'hidden' }}">
                                    {{ $navUnreadCount ?? 0 }} New
                                </span>
                            </div>
                            @if(($navUnreadCount ?? 0) > 0)
                                <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-bold text-[#0033a0] dark:text-blue-400 hover:underline">
                                        Mark read
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div data-notification-list class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-zinc-800">
                            @forelse($navNotifications ?? [] as $notif)
                                <a href="{{ route('admin.notifications.read', $notif->notification_id) }}" 
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

                <!-- Dark Mode Toggle (Desktop) -->
                <button onclick="toggleTheme()" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition hidden md:block">
                    <svg class="theme-toggle-dark-icon w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                    <svg class="theme-toggle-light-icon w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
                </button>

                <!-- Close Button (Mobile) -->
                <button @click="sidebarOpen = false" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition md:hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 flex flex-col gap-1 overflow-y-auto" @click="if (window.innerWidth < 768) sidebarOpen = false">
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

            <a href="{{ route('admin.messages.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.messages.*') ? 'bg-[#f0f6ff] dark:bg-gray-800 text-[#1a3c8f] dark:text-white font-bold border border-[#1a3c8f] dark:border-gray-700 shadow-sm' : 'text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                <img src="{{ asset('images/MESSAGES LOGO.png') }}" class="w-5 h-5 shrink-0 object-contain dark:invert" alt="Messages">
                <span>Messages</span>
                <span data-messages-badge class="ml-auto bg-red-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full shadow-xs {{ $adminUnreadMessagesCount > 0 ? '' : 'hidden' }}">
                    {{ $adminUnreadMessagesCount > 99 ? '99+' : $adminUnreadMessagesCount }}
                </span>
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
        <div class="p-4 border-t border-gray-200 dark:border-gray-800" x-data="{ openProfile: false }">
            <div class="relative" @click.outside="openProfile = false">
                <button type="button" @click.stop="openProfile = !openProfile" class="w-full flex items-center gap-3 border-2 border-dashed border-[#1a3c8f] dark:border-gray-600 rounded-xl p-3 bg-[#f8faff] dark:bg-gray-800/50 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-800 transition text-left focus:outline-none">
                    <div class="w-9 h-9 shrink-0 border border-[#1a3c8f] dark:border-gray-500 bg-white dark:bg-gray-700 text-[#1a3c8f] dark:text-white rounded-full flex items-center justify-center font-bold">
                        {{ strtoupper(substr(auth()->user()->first_name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[13px] font-bold text-[#1a3c8f] dark:text-gray-200 truncate">Admin</div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->email_account }}</div>
                    </div>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="openProfile" 
                     x-transition 
                     x-cloak
                     class="absolute bottom-full left-0 mb-2 w-64 bg-white dark:bg-[#18181b] border border-gray-200 dark:border-zinc-800 rounded-xl shadow-2xl overflow-hidden z-[99999] font-sans">
                    
                    <!-- Header Section: Avatar + Name + Role -->
                    <div class="p-4 flex items-center gap-3 bg-gray-50 dark:bg-zinc-800/40">
                        <div class="w-10 h-10 bg-[#0033a0] text-white rounded-md flex items-center justify-center font-bold text-base shrink-0 shadow-sm">
                            {{ strtoupper(substr(auth()->user()->first_name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-sm text-slate-900 dark:text-white truncate leading-tight">
                                {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium truncate mt-0.5">
                                System Administrator
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-zinc-800"></div>

                    <!-- Items List -->
                    <div class="py-1">
                        <a href="{{ route('admin.profile.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>PROFILE SETTINGS</span>
                        </a>
                    </div>

                    <div class="border-t border-gray-100 dark:border-zinc-800"></div>

                    <!-- Sign Out Section -->
                    <div class="py-1">
                        <form method="POST" action="{{ route('logout') }}" x-data="{ loggingOut: false }" @submit="loggingOut = true">
                            @csrf
                            <button type="submit" :disabled="loggingOut" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition text-left cursor-pointer disabled:opacity-60">
                                <svg x-show="!loggingOut" class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <svg x-show="loggingOut" x-cloak class="w-4 h-4 animate-spin text-red-500 shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-text="loggingOut ? 'LOGGING OUT...' : 'SIGN OUT'">SIGN OUT</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Container -->
    <div class="md:ml-56 flex-1 flex flex-col min-h-screen w-full">
        <main class="p-4 sm:p-7 flex-1">
            @unless(View::hasSection('hide_alerts'))
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
            @endunless

            @yield('content')
        </main>
    </div>

    @livewireScripts
    <script>
        // Set initial icon state on load
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.querySelectorAll('.theme-toggle-light-icon').forEach(el => el.classList.remove('hidden'));
        } else {
            document.querySelectorAll('.theme-toggle-dark-icon').forEach(el => el.classList.remove('hidden'));
        }

        function toggleTheme() {
            var darkIcons = document.querySelectorAll('.theme-toggle-dark-icon');
            var lightIcons = document.querySelectorAll('.theme-toggle-light-icon');
            darkIcons.forEach(el => el.classList.toggle('hidden'));
            lightIcons.forEach(el => el.classList.toggle('hidden'));

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
