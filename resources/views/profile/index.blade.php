@php
    $activePortal = match(true) {
        request()->routeIs('admin.*') || request()->is('admin/*') => 'admin',
        request()->routeIs('worker.*') || request()->is('worker/*') => 'worker',
        default => 'client'
    };
    $layoutName = match($activePortal) {
        'admin'  => 'layouts.admin',
        'worker' => 'layouts.worker',
        default  => 'layouts.client'
    };
@endphp
@extends($layoutName)

@if($activePortal === 'client')
    @section('fullwidth', true)
@endif
@section('hide_alerts', true)

@section('content')
@if($activePortal === 'admin')
    <!-- Admin Standardized Header Banner -->
    <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-8 py-6 flex justify-between items-center mb-6 shadow-sm font-sans">
        <div>
            <h1 class="text-[#0033a0] dark:text-blue-400 text-2xl font-bold mb-1">Profile Settings</h1>
            <p class="text-[#0033a0]/80 dark:text-gray-300 text-sm font-medium">Manage your administrator account credentials, details, and security preferences.</p>
        </div>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="bg-[#0033a0] hover:bg-[#002480] text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                &larr; Back to Dashboard
            </a>
        </div>
    </div>
    <div class="w-full max-w-7xl mx-auto font-sans">
@elseif($activePortal === 'worker')
    <!-- Worker Standardized Header Banner -->
    <div class="bg-[#fffde7] dark:bg-[#1c1c1e] border-2 border-[#0033a0] dark:border-blue-600 rounded-2xl px-8 py-6 flex justify-between items-center mb-6 shadow-sm font-sans">
        <div>
            <h1 class="text-[#0033a0] dark:text-blue-400 text-2xl font-bold mb-1">Profile Settings</h1>
            <p class="text-[#0033a0]/80 dark:text-gray-300 text-sm font-medium">Manage your worker account credentials and security settings.</p>
        </div>
        <div>
            <a href="{{ route('worker.dashboard') }}" class="bg-[#0033a0] hover:bg-[#002480] text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                &larr; Back to Dashboard
            </a>
        </div>
    </div>
    <div class="w-full max-w-7xl mx-auto font-sans">
@else
    <div class="w-full flex flex-col font-sans min-h-[calc(100vh-64px)] bg-slate-50/50 dark:bg-[#111111]">
        <!-- Top Hero Section (Wide Rectangle Banner) -->
        <div class="bg-[#fffde7] dark:bg-[#18181b] py-8 px-6 md:px-12">
            <div class="max-w-6xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-[#0033a0] dark:text-blue-400 text-3xl font-bold tracking-tight">Profile Settings</h1>
                    <p class="text-[#0033a0]/80 dark:text-gray-400 text-sm font-medium mt-1">Manage your account information, contact details, and security preferences</p>
                </div>
                <div>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-[#0038A8]/10 text-[#0038A8] dark:bg-blue-900/40 dark:text-blue-300 uppercase tracking-wider">
                        Client Account
                    </span>
                </div>
            </div>
        </div>
        <main class="max-w-6xl w-full mx-auto px-6 md:px-8 py-8 flex-1">
@endif
        
        <!-- Alerts -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 text-green-800 border border-green-200 dark:bg-green-900/30 dark:border-green-800 dark:text-green-400 text-sm flex items-center gap-3 shadow-2xs">
                <svg class="w-5 h-5 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        
        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-800 border border-red-200 dark:bg-red-900/30 dark:border-red-800 dark:text-red-400 text-sm flex items-center gap-3 shadow-2xs">
                <svg class="w-5 h-5 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Main Outer Box (Soft Blue Container with Blue Border) -->
        <div class="bg-[#EBF3FE] dark:bg-[#151d2a] border border-[#7DAAF4] dark:border-blue-800 rounded-2xl md:rounded-3xl p-6 md:p-8 shadow-2xs">
            
            <div class="flex flex-col lg:flex-row gap-8 items-start">
                
                <!-- Left Navigation Sidebar -->
                <div class="w-full lg:w-64 shrink-0">
                    <div class="bg-white dark:bg-[#1c1c1e] rounded-xl border border-gray-200 dark:border-zinc-800 p-4 shadow-2xs">
                        <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-3 mb-3">Account Settings</h3>
                        <nav class="flex flex-col gap-1.5">
                            <button onclick="switchTab('profile')" id="tab-btn-profile" 
                                    class="w-full text-left px-4 py-2.5 rounded-lg text-sm font-semibold transition shadow-2xs {{ !session('show_2fa_setup') ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                                Profile Information
                            </button>
                            <button onclick="switchTab('2fa')" id="tab-btn-2fa" 
                                    class="w-full text-left px-4 py-2.5 rounded-lg text-sm font-semibold transition shadow-2xs {{ session('show_2fa_setup') ? 'bg-[#0038A8] text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-700' }}">
                                Two-Factor Auth
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Right Form Content Box -->
                <div class="flex-1 w-full">
                    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl md:rounded-2xl p-6 md:p-8 shadow-2xs">
                        
                        <!-- Profile Information Tab -->
                        <div id="tab-content-profile" class="{{ session('show_2fa_setup') ? 'hidden' : '' }}">
                            <h2 class="text-xl font-bold text-[#042B74] dark:text-white mb-6">Profile Information</h2>
                            
                            <!-- User Avatar & Header Info -->
                            <div class="flex items-center gap-4 mb-8 pb-6 border-b border-gray-100 dark:border-zinc-800">
                                <div class="w-16 h-16 rounded-full bg-[#0038A8] text-white font-extrabold text-2xl flex items-center justify-center shadow-sm shrink-0">
                                    {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $user->first_name }} {{ $user->last_name }}</h3>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $user->email_account }}</p>
                                </div>
                            </div>

                            <form action="{{ route('profile.update') }}" 
                                  method="POST" 
                                  x-data="{
                                      selectedCampus: '{{ old('campus', $user->client?->campus ?? '') }}',
                                      selectedLocation: '{{ old('office', $user->client?->office ?? '') }}',
                                      customLocation: '',

                                      locationsMap: {
                                          'BU Main': [
                                              'Administration Building (GSO, OSAS, Registrar, Cashier)',
                                              'College of Education (BUCE)',
                                              'College of Arts and Letters (BUCAL)',
                                              'College of Science (BUCS)',
                                              'College of Nursing (BUCN)',
                                              'College of Medicine (BUCM)',
                                              'Information & Communications Technology Office (ICTO)',
                                              'University Student Center (USC)',
                                              'University Main Library & Amphitheater',
                                              'BU Gymnasium & Sports Complex',
                                              'Other Location (BU Main)'
                                          ],
                                          'BU Daraga': [
                                              'College of Social Sciences and Philosophy (CSSP)',
                                              'College of Business, Economics and Management (CBEM)',
                                              'Daraga Campus Administration Building',
                                              'Daraga Campus Library',
                                              'Student Activity Center & Canteen',
                                              'Other Location (BU Daraga)'
                                          ],
                                          'BU East': [
                                              'College of Engineering (BUCENG)',
                                              'College of Industrial Technology (BUCIT)',
                                              'East Campus Administration & Library',
                                              'Mechanical & Electrical Shop Buildings',
                                              'East Campus Student Center',
                                              'Other Location (BU East)'
                                          ],
                                          'BU Polangui': [
                                              'Polangui Campus Administration Building',
                                              'Department of Information Technology',
                                              'Engineering & Technology Building',
                                              'Nursing & Health Sciences Building',
                                              'Polangui Campus Library & Student Center',
                                              'Other Location (BU Polangui)'
                                          ],
                                          'BU Tabaco': [
                                              'Tabaco Campus Administration Building',
                                              'Department of Fisheries & Aquaculture',
                                              'Business & Teacher Education Building',
                                              'Tabaco Campus Library',
                                              'Other Location (BU Tabaco)'
                                          ],
                                          'BU Gubat': [
                                              'Gubat Campus Administration Building',
                                              'Academic & Multi-Purpose Building',
                                              'Gubat Campus Library',
                                              'Other Location (BU Gubat)'
                                          ],
                                          'BU Guinobatan': [
                                              'College of Agriculture and Forestry (BUCAF) Admin',
                                              'BUCAF Academic & Laboratory Buildings',
                                              'Research, Extension & Demonstration Farm',
                                              'Guinobatan Campus Library & Auditorium',
                                              'Other Location (BU Guinobatan)'
                                          ]
                                      },

                                      init() {
                                          if (this.selectedCampus && this.selectedLocation) {
                                              const available = this.locationsMap[this.selectedCampus] || [];
                                              if (!available.includes(this.selectedLocation)) {
                                                  this.customLocation = this.selectedLocation;
                                                  this.selectedLocation = 'Other Location (' + this.selectedCampus + ')';
                                              }
                                          }
                                      },

                                      get availableLocations() {
                                          if (!this.selectedCampus) return [];
                                          return this.locationsMap[this.selectedCampus] || ['Other Location'];
                                      },

                                      get finalOffice() {
                                          if (this.selectedLocation && this.selectedLocation.includes('Other')) {
                                              return this.customLocation || this.selectedLocation;
                                          }
                                          return this.selectedLocation;
                                      }
                                  }"
                                  class="space-y-5">
                                @csrf

                                <!-- Full Name (Disabled) -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">Full Name</label>
                                    <input type="text" disabled value="{{ $user->first_name }} {{ $user->last_name }}" 
                                           class="w-full px-4 py-2.5 bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-400 cursor-not-allowed">
                                </div>

                                <!-- Email Address (Disabled) -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">Email Address</label>
                                    <input type="text" disabled value="{{ $user->email_account }}" 
                                           class="w-full px-4 py-2.5 bg-gray-100 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-400 cursor-not-allowed">
                                </div>

                                <!-- Phone / Contact Number -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">Phone / Contact Number</label>
                                    <div class="flex rounded-xl border border-gray-200 dark:border-zinc-700 overflow-hidden focus-within:border-[#0038A8] focus-within:ring-1 focus-within:ring-[#0038A8]">
                                        <span class="inline-flex items-center px-4 bg-blue-50 dark:bg-zinc-800 text-[#0038A8] dark:text-blue-400 text-xs font-bold border-r border-gray-200 dark:border-zinc-700 shrink-0">
                                            🇵🇭 +63
                                        </span>
                                        <input type="text" 
                                               name="contact_number" 
                                               value="{{ old('contact_number', $user->contact_number) }}" 
                                               placeholder="09123456789" 
                                               pattern="^09\d{9}$"
                                               title="Contact number must be 11 digits starting with 09"
                                               maxlength="11"
                                               class="w-full px-4 py-2.5 bg-white dark:bg-zinc-900 border-none text-sm text-gray-800 dark:text-white focus:outline-none focus:ring-0">
                                    </div>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">11-digit Philippine mobile number starting with 09.</p>
                                </div>

                                @if($user->isClient())
                                <hr class="border-gray-100 dark:border-zinc-800 my-6">

                                <!-- Campus Dropdown -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">Campus</label>
                                    <select name="campus" 
                                            x-model="selectedCampus"
                                            @change="selectedLocation = ''; customLocation = '';"
                                            class="w-full px-4 py-2.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm font-medium text-gray-800 dark:text-gray-200 focus:outline-none focus:border-[#0038A8] focus:ring-1 focus:ring-[#0038A8]">
                                        <option value="" disabled>Select your campus</option>
                                        <option value="BU Main">BU Main</option>
                                        <option value="BU Daraga">BU Daraga</option>
                                        <option value="BU East">BU East</option>
                                        <option value="BU Polangui">BU Polangui</option>
                                        <option value="BU Tabaco">BU Tabaco</option>
                                        <option value="BU Gubat">BU Gubat</option>
                                        <option value="BU Guinobatan">BU Guinobatan</option>
                                    </select>
                                </div>

                                <!-- Office Location Dropdown (Dependent on Campus) -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-800 dark:text-gray-200 mb-1.5">Office / Location</label>
                                    <select x-model="selectedLocation"
                                            :disabled="!selectedCampus"
                                            class="w-full px-4 py-2.5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm font-medium text-gray-800 dark:text-gray-200 focus:outline-none focus:border-[#0038A8] focus:ring-1 focus:ring-[#0038A8] disabled:bg-gray-100 dark:disabled:bg-zinc-800 disabled:text-gray-400 disabled:cursor-not-allowed">
                                        <option value="" disabled x-text="selectedCampus ? 'Select office / building in ' + selectedCampus : 'Please select a Campus first'"></option>
                                        <template x-for="loc in availableLocations" :key="loc">
                                            <option :value="loc" x-text="loc" :selected="selectedLocation === loc"></option>
                                        </template>
                                    </select>

                                    <!-- Custom Location Input if 'Other' is selected -->
                                    <div x-show="selectedLocation && selectedLocation.includes('Other')" x-cloak class="mt-3">
                                        <input type="text" 
                                               x-model="customLocation" 
                                               placeholder="Specify specific office or room (e.g. IT Building, Room 202)" 
                                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:border-[#0038A8]">
                                    </div>

                                    <!-- Hidden Input submitting final office string -->
                                    <input type="hidden" name="office" :value="finalOffice">
                                </div>
                                @endif

                                <div class="pt-4 flex justify-end">
                                    <button type="submit" class="px-6 py-2.5 bg-[#0038A8] hover:bg-[#002B82] text-white font-semibold text-sm rounded-lg shadow-xs transition">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- 2FA Tab -->
                        <div id="tab-content-2fa" class="{{ !session('show_2fa_setup') ? 'hidden' : '' }}">
                            <h2 class="text-xl font-bold text-[#042B74] dark:text-white mb-2">Two-Factor Authentication</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">Add additional security to your account using two-factor authentication (TOTP).</p>

                            @if($user->totp_secret && !session('show_2fa_setup'))
                                <div class="p-5 border border-green-200 bg-green-50/60 dark:bg-green-950/30 dark:border-green-800 rounded-xl flex flex-col md:flex-row items-center justify-between gap-4">
                                    <div>
                                        <h4 class="font-bold text-green-800 dark:text-green-400 flex items-center gap-2 text-base">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            2FA is Currently Enabled
                                        </h4>
                                        <p class="text-sm text-green-700 dark:text-green-500 mt-1">Your account is secured with a two-factor authenticator app.</p>
                                    </div>
                                    <form action="{{ route('profile.2fa.disable') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-white dark:bg-zinc-800 border border-red-200 dark:border-red-900/50 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 font-semibold text-xs rounded-lg shadow-2xs transition">
                                            Disable 2FA
                                        </button>
                                    </form>
                                </div>
                            @elseif(session('show_2fa_setup') && $qrCodeSvg)
                                <div class="max-w-md bg-gray-50 dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 p-6 rounded-xl">
                                    <h3 class="font-bold text-gray-900 dark:text-white text-base mb-2">Scan this QR Code</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Open your authenticator app (e.g. Google Authenticator) and scan the QR code below.</p>
                                    
                                    <div class="bg-white p-4 rounded-xl shadow-xs inline-block mb-6 border border-gray-100">
                                        {!! $qrCodeSvg !!}
                                    </div>
                                    
                                    <form action="{{ route('profile.2fa.enable') }}" method="POST">
                                        @csrf
                                        <div class="mb-4">
                                            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1.5">Verification Code</label>
                                            <input type="text" name="one_time_password" required placeholder="Enter 6-digit code" class="w-full rounded-xl border-gray-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white shadow-2xs focus:border-[#0038A8] text-lg tracking-widest text-center py-2.5">
                                        </div>
                                        <div class="flex gap-3">
                                            <button type="submit" class="flex-1 bg-[#0038A8] hover:bg-[#002B82] text-white font-semibold text-xs py-2.5 px-4 rounded-lg transition shadow-xs">
                                                Verify & Enable
                                            </button>
                                            <a href="{{ route('profile.index') }}" class="px-4 py-2.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-300 font-semibold text-xs rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700 transition">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div>
                                    <form action="{{ route('profile.2fa.initiate') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-5 py-2.5 bg-[#0038A8] hover:bg-[#002B82] text-white font-semibold text-xs rounded-lg shadow-xs transition inline-flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span>Enable Two-Factor Authentication</span>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

            </div>

        </div>

@if($activePortal === 'client')
    </main>
</div>
@else
</div>
@endif

<script>
    function switchTab(tabId) {
        const btnProfile = document.getElementById('tab-btn-profile');
        const btn2fa = document.getElementById('tab-btn-2fa');
        const contentProfile = document.getElementById('tab-content-profile');
        const content2fa = document.getElementById('tab-content-2fa');

        const activeClass = ['bg-[#0038A8]', 'text-white'];
        const inactiveClass = ['bg-white', 'dark:bg-zinc-800', 'text-gray-700', 'dark:text-gray-200', 'hover:bg-gray-50', 'dark:hover:bg-zinc-700'];

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
@endsection
