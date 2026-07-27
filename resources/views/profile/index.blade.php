<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - LINKod</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Apply dark mode based on local storage to prevent FOUC
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 dark:bg-[#111111] dark:text-gray-200 antialiased font-['Inter'] transition-colors duration-200">
    
    <!-- Navbar -->
    <nav class="bg-white dark:bg-[#1a1a1a] border-b border-gray-200 dark:border-gray-800 py-4 px-6 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center gap-6">
            <img src="{{ asset('images/LINKOD logo.png') }}" alt="LINKod Logo" class="h-8 dark:brightness-200 dark:grayscale">
        </div>
        <div class="flex items-center gap-4">
            <!-- Theme Toggle -->
            <button id="theme-toggle" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition text-gray-500 dark:text-gray-400">
                <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                </svg>
                <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path>
                </svg>
            </button>
            @php
                $dashboardRoute = match($user->role) {
                    'admin' => route('admin.dashboard'),
                    'client' => route('client.dashboard'),
                    'worker' => route('worker.dashboard'),
                    default => route('login'),
                };
            @endphp
            <a href="{{ $dashboardRoute }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white px-3 py-2">Back to Dashboard</a>
            <div class="h-8 w-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                {{ substr($user->first_name, 0, 1) }}
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-8">
        
        @if(session('success'))
            <div class="mb-6 p-4 rounded-lg bg-green-50 text-green-800 dark:bg-green-900/30 dark:border dark:border-green-800 dark:text-green-400 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-800 dark:bg-red-900/30 dark:border dark:border-red-800 dark:text-red-400 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        <!-- Profile Banner Area (Like iBU) -->
        <div class="rounded-2xl overflow-hidden bg-white dark:bg-[#1a1a1a] shadow-sm border border-gray-200 dark:border-gray-800 mb-8">
            <div class="h-48 bg-gradient-to-r from-blue-700 via-blue-800 to-gray-900 relative">
                <!-- Abstract patterns could go here -->
                <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>
            </div>
            
            <div class="px-8 pb-8 relative">
                <div class="absolute -top-16 left-8">
                    <div class="h-32 w-32 rounded-full border-4 border-white dark:border-[#1a1a1a] bg-white dark:bg-gray-800 flex items-center justify-center shadow-md relative overflow-hidden group">
                        <div class="text-4xl font-bold text-blue-600 dark:text-blue-400">{{ substr($user->first_name, 0, 1) }}</div>
                        <!-- Placeholder for photo upload overlay -->
                        <div class="absolute inset-0 bg-black/50 hidden group-hover:flex items-center justify-center cursor-pointer transition-opacity">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                    </div>
                </div>
                
                <div class="pt-20">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->first_name }} {{ $user->last_name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $user->email_account }}</p>
                    <div class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 capitalize">
                        {{ $user->role }}
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Navigation -->
            <div class="w-full lg:w-64 shrink-0">
                <div class="bg-white dark:bg-[#1a1a1a] rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Account Settings</h3>
                    </div>
                    <nav class="flex flex-col p-2 gap-1">
                        <button onclick="switchTab('profile')" id="tab-btn-profile" class="text-left px-4 py-2.5 rounded-lg text-sm font-medium transition shadow-sm {{ !session('show_2fa_setup') ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">Profile Information</button>
                        <button onclick="switchTab('2fa')" id="tab-btn-2fa" class="text-left px-4 py-2.5 rounded-lg text-sm font-medium transition shadow-sm {{ session('show_2fa_setup') ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">Two-Factor Auth</button>
                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1">
                <div class="bg-white dark:bg-[#1a1a1a] rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6 lg:p-8">
                    <!-- Profile Information Tab -->
                    <div id="tab-content-profile" class="{{ session('show_2fa_setup') ? 'hidden' : '' }}">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Profile Information</h2>
                        
                        <div class="mb-4 max-w-lg">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                            <input type="text" disabled value="{{ $user->first_name }} {{ $user->last_name }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 dark:text-gray-500 shadow-sm py-2.5 cursor-not-allowed">
                        </div>

                        <div class="mb-8 max-w-lg">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                            <input type="text" disabled value="{{ $user->email_account }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 dark:text-gray-500 shadow-sm py-2.5 cursor-not-allowed">
                        </div>

                        @if($user->isClient())
                        <hr class="border-gray-100 dark:border-gray-800 my-8 max-w-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Update your office and campus details.</p>
                        
                        <form action="{{ route('profile.update') }}" method="POST" class="space-y-4 max-w-lg">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Campus</label>
                                <select name="campus" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2.5">
                                    <option value="" disabled {{ !$user->client?->campus ? 'selected' : '' }}>Select your campus</option>
                                    @php $campuses = ['BU Main', 'BU Daraga', 'BU East', 'BU Polangui', 'BU Tabaco', 'BU Gubat', 'BU Guinobatan']; @endphp
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus }}" {{ $user->client?->campus === $campus ? 'selected' : '' }}>{{ $campus }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Office / Location</label>
                                <input type="text" name="office" value="{{ old('office', $user->client?->office) }}" placeholder="e.g. IT Building, Room 202" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2.5">
                            </div>
                            <div class="pt-2 flex justify-end">
                                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-lg shadow-sm transition">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                        @endif
                    </div>

                    <!-- 2FA Tab -->
                    <div id="tab-content-2fa" class="{{ !session('show_2fa_setup') ? 'hidden' : '' }}">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Two-Factor Authentication</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-8">Add additional security to your account using two-factor authentication (TOTP).</p>

                    @if($user->totp_secret && !session('show_2fa_setup'))
                        <div class="p-4 border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-800 rounded-lg flex flex-col md:flex-row items-center justify-between gap-4">
                            <div>
                                <h4 class="font-bold text-green-800 dark:text-green-400 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    2FA is Currently Enabled
                                </h4>
                                <p class="text-sm text-green-700 dark:text-green-500 mt-1">Your account is secured with a two-factor authenticator app.</p>
                            </div>
                            <form action="{{ route('profile.2fa.disable') }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-white dark:bg-gray-800 border border-red-200 dark:border-red-900/50 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 font-semibold text-sm rounded-lg shadow-sm transition">
                                    Disable 2FA
                                </button>
                            </form>
                        </div>
                    @elseif(session('show_2fa_setup') && $qrCodeSvg)
                        <div class="max-w-md bg-gray-50 dark:bg-[#141414] border border-gray-200 dark:border-gray-800 p-6 rounded-xl">
                            <h3 class="font-bold text-gray-900 dark:text-white mb-3">Scan this QR Code</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Open your authenticator app (e.g. Google Authenticator) and scan the QR code below.</p>
                            
                            <div class="bg-white p-4 rounded-xl shadow-sm inline-block mb-6 border border-gray-100">
                                {!! $qrCodeSvg !!}
                            </div>
                            
                            <form action="{{ route('profile.2fa.enable') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Verification Code</label>
                                    <input type="text" name="one_time_password" required placeholder="Enter 6-digit code" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-lg tracking-widest text-center py-3">
                                </div>
                                <div class="flex gap-3">
                                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg transition shadow-sm">
                                        Verify & Enable
                                    </button>
                                    <a href="{{ route('profile.index') }}" class="px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancel</a>
                                </div>
                            </form>
                        </div>
                    @else
                        <div>
                            <form action="{{ route('profile.2fa.initiate') }}" method="POST">
                                @csrf
                                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-lg shadow-sm transition inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    Enable Two-Factor Authentication
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script>
        function switchTab(tabId) {
            const btnProfile = document.getElementById('tab-btn-profile');
            const btn2fa = document.getElementById('tab-btn-2fa');
            const contentProfile = document.getElementById('tab-content-profile');
            const content2fa = document.getElementById('tab-content-2fa');

            const activeClass = ['bg-gray-100', 'dark:bg-gray-800', 'text-gray-900', 'dark:text-white'];
            const inactiveClass = ['text-gray-600', 'dark:text-gray-400', 'hover:bg-gray-50', 'dark:hover:bg-gray-800/50'];

            if (tabId === 'profile') {
                contentProfile.classList.remove('hidden');
                content2fa.classList.add('hidden');
                
                btnProfile.classList.add(...activeClass);
                btnProfile.classList.remove(...inactiveClass);
                
                btn2fa.classList.remove(...activeClass);
                btn2fa.classList.add(...inactiveClass);
            } else {
                contentProfile.classList.add('hidden');
                content2fa.classList.remove('hidden');
                
                btn2fa.classList.add(...activeClass);
                btn2fa.classList.remove(...inactiveClass);
                
                btnProfile.classList.remove(...activeClass);
                btnProfile.classList.add(...inactiveClass);
            }
        }
    </script>

    <!-- Theme Toggle Script -->
    <script>
        var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        // Change the icons inside the button based on previous settings
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
        }

        var themeToggleBtn = document.getElementById('theme-toggle');

        themeToggleBtn.addEventListener('click', function() {
            // toggle icons
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            // if set via local storage previously
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
        });
    </script>
</body>
</html>
