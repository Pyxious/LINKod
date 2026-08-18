@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full flex flex-col font-sans bg-slate-50/50 dark:bg-[#111111]"
     x-data="{
         searchQuery: '',
         selectedCategory: 'all',
         openItems: [1],
         faqs: [
             { id: 1, categoryKey: 'general', categoryName: 'General Questions', question: '1. What is LINKod?', answer: 'LINKod is the official web-based Service Request Management System of the Bicol University General Services Office (BUGSO). It enables authorized university personnel to submit, monitor, and manage service requests online.' },
             { id: 2, categoryKey: 'general', categoryName: 'General Questions', question: '2. Who can use LINKod?', answer: 'LINKod is intended for authorized Bicol University faculty, staff, offices, and personnel using their official BU Google account (@bicol-u.edu.ph).' },
             { id: 3, categoryKey: 'general', categoryName: 'General Questions', question: '3. Do I need to create a separate account?', answer: 'No. You can log in directly using your official Bicol University Google account via Single Sign-On (SSO).' },
             { id: 4, categoryKey: 'general', categoryName: 'General Questions', question: '4. What services can I request through LINKod?', answer: 'You may submit requests for: Carpentry / Masonry / Electrical Services, Plumbing Services, Painting Services, Landscaping Services, Janitorial Services, and Manpower Assistance.' },
             { id: 5, categoryKey: 'requests', categoryName: 'Service Requests', question: '5. How do I submit a service request?', answer: 'Log in to the Client Portal, click \'Submit a Request\', select your desired service category, specify the location and details, attach photos/documents if available, and submit your form.' },
             { id: 6, categoryKey: 'requests', categoryName: 'Service Requests', question: '6. Can I upload photos or supporting documents?', answer: 'Yes. You may attach images or other supporting documents to help GSO personnel assess your request before inspection and worker deployment.' },
             { id: 7, categoryKey: 'requests', categoryName: 'Service Requests', question: '7. Can I submit requests for multiple locations in one form?', answer: 'It is recommended to submit separate service requests for different locations or categories to ensure accurate assignment and swift processing by skilled workers.' },
             { id: 8, categoryKey: 'requests', categoryName: 'Service Requests', question: '8. Can I cancel a request after submitting?', answer: 'Yes, you can cancel a pending service request directly from your \'My Requests\' dashboard before work has commenced.' },
             { id: 9, categoryKey: 'tracking', categoryName: 'Tracking & Status', question: '9. How do I track the status of my request?', answer: 'Enter your tracking number (e.g., PS-012) in the Track Request section on the home page or view real-time updates under your \'My Requests\' page.' },
             { id: 10, categoryKey: 'tracking', categoryName: 'Tracking & Status', question: '10. What do the different request statuses mean?', answer: 'Pending: Under GSO admin review; Approved/Assigned: Approved and assigned to a worker team; In Progress: Work ongoing; Completed: Work completed; Cancelled/Rejected: Closed or declined.' },
             { id: 11, categoryKey: 'tracking', categoryName: 'Tracking & Status', question: '11. Will I receive notifications regarding status updates?', answer: 'Yes, LINKod provides real-time notifications via the bell icon on your portal whenever your request status changes or a new message is posted.' },
             { id: 12, categoryKey: 'tracking', categoryName: 'Tracking & Status', question: '12. How long does it take for a request to be inspected?', answer: 'Initial assessment takes 1-2 business days during office hours (Monday to Friday, 8:00 AM - 5:00 PM). Urgent requests are handled with priority.' },
             { id: 13, categoryKey: 'messages', categoryName: 'Messages & Feedback', question: '13. How can I communicate with the assigned worker or GSO admin?', answer: 'You can use the built-in Messages section on your request detail page to exchange comments, updates, and clarifications directly with GSO personnel.' },
             { id: 14, categoryKey: 'messages', categoryName: 'Messages & Feedback', question: '14. How do I submit feedback after job completion?', answer: 'Once a request status is marked as Completed, an evaluation prompt allows you to rate the service quality and submit feedback.' },
             { id: 15, categoryKey: 'messages', categoryName: 'Messages & Feedback', question: '15. Who views my feedback and service rating?', answer: 'All submitted feedback is securely reviewed by GSO administration to continuously monitor service quality and team performance.' },
             { id: 16, categoryKey: 'technical', categoryName: 'Technical Support', question: '16. I cannot sign in. What should I do?', answer: 'Ensure that you are using your official Bicol University Google account (@bicol-u.edu.ph). If the problem persists, contact the ICTO Helpdesk for account assistance.' },
             { id: 17, categoryKey: 'technical', categoryName: 'Technical Support', question: '17. What web browsers are supported?', answer: 'LINKod is optimized for all modern desktop and mobile browsers, including Google Chrome, Mozilla Firefox, Microsoft Edge, and Safari.' },
             { id: 18, categoryKey: 'technical', categoryName: 'Technical Support', question: '18. Why am I not receiving app notifications?', answer: 'Check your browser permission settings to ensure pop-ups and site notifications are allowed for the LINKod portal.' },
             { id: 19, categoryKey: 'technical', categoryName: 'Technical Support', question: '19. How do I set up Two-Factor Authentication (2FA)?', answer: 'Navigate to your Profile Settings page to enable 2FA using your preferred authenticator app (e.g. Google Authenticator).' },
             { id: 20, categoryKey: 'technical', categoryName: 'Technical Support', question: '20. Who do I contact for urgent GSO technical support?', answer: 'You can reach out directly via email at bu-gso@bicol-u.edu.ph or call 0912 345 6789 during standard office hours.' }
         ],
         toggleItem(id) {
             if (this.openItems.includes(id)) {
                 this.openItems = this.openItems.filter(i => i !== id);
             } else {
                 this.openItems.push(id);
             }
         },
         isOpen(id) {
             return this.openItems.includes(id);
         },
         allExpanded() {
             return this.filteredFaqs.length > 0 && this.filteredFaqs.every(item => this.openItems.includes(item.id));
         },
         toggleExpandAll() {
             if (this.allExpanded()) {
                 this.openItems = [];
             } else {
                 this.openItems = this.filteredFaqs.map(item => item.id);
             }
         },
         get filteredFaqs() {
             return this.faqs.filter(item => {
                 const matchesCategory = this.selectedCategory === 'all' || item.categoryKey === this.selectedCategory;
                 const q = this.searchQuery.toLowerCase().trim();
                 const matchesSearch = !q || item.question.toLowerCase().includes(q) || item.answer.toLowerCase().includes(q) || item.categoryName.toLowerCase().includes(q);
                 return matchesCategory && matchesSearch;
             });
         }
     }">

    <!-- 1. Header Banner -->
    <div class="w-full bg-[#edf4fb] dark:bg-[#18181b] py-12 px-6 sm:px-12 border-b border-gray-200 dark:border-zinc-800">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 tracking-wider uppercase block mb-1">
                    HELP CENTER
                </span>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-[#0033a0] dark:text-blue-400 tracking-tight">
                    Frequently Asked Questions
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mt-1">
                    Find answers to common questions about LINKod, the GSO Service Request System
                </p>
            </div>
            <!-- Search Input Box -->
            <div class="relative w-full md:w-80 shrink-0">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Search questions..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg text-xs font-medium text-slate-800 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#0033a0] shadow-sm transition">
            </div>
        </div>
    </div>

    <!-- 2. Topics Cards Row -->
    <div class="max-w-7xl mx-auto px-6 sm:px-12 pt-8 pb-6">
        <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-gray-200 mb-4">Topics</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
            
            <!-- Topic 1 -->
            <button @click="selectedCategory = selectedCategory === 'general' ? 'all' : 'general'"
                    :class="selectedCategory === 'general' ? 'border-[#0033a0] ring-2 ring-[#0033a0]/20 bg-blue-50/50 dark:bg-blue-950/30' : 'border-gray-200 dark:border-zinc-800 bg-white dark:bg-[#1c1c1e] hover:border-gray-300 dark:hover:border-zinc-700'"
                    class="p-3.5 rounded-xl border text-left transition shadow-sm flex flex-col justify-between h-full group cursor-pointer">
                <div>
                    <h4 class="text-[11px] font-bold text-[#0033a0] dark:text-blue-400 tracking-wider uppercase mb-1.5 leading-snug">
                        GENERAL QUESTIONS
                    </h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-normal">
                        Learn more about what LINKod does.
                    </p>
                </div>
            </button>

            <!-- Topic 2 -->
            <button @click="selectedCategory = selectedCategory === 'requests' ? 'all' : 'requests'"
                    :class="selectedCategory === 'requests' ? 'border-[#0033a0] ring-2 ring-[#0033a0]/20 bg-blue-50/50 dark:bg-blue-950/30' : 'border-gray-200 dark:border-zinc-800 bg-white dark:bg-[#1c1c1e] hover:border-gray-300 dark:hover:border-zinc-700'"
                    class="p-3.5 rounded-xl border text-left transition shadow-sm flex flex-col justify-between h-full group cursor-pointer">
                <div>
                    <h4 class="text-[11px] font-bold text-[#0033a0] dark:text-blue-400 tracking-wider uppercase mb-1.5 leading-snug">
                        SERVICE REQUESTS
                    </h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-normal">
                        How to submit and manage your requests
                    </p>
                </div>
            </button>

            <!-- Topic 3 -->
            <button @click="selectedCategory = selectedCategory === 'tracking' ? 'all' : 'tracking'"
                    :class="selectedCategory === 'tracking' ? 'border-[#0033a0] ring-2 ring-[#0033a0]/20 bg-blue-50/50 dark:bg-blue-950/30' : 'border-gray-200 dark:border-zinc-800 bg-white dark:bg-[#1c1c1e] hover:border-gray-300 dark:hover:border-zinc-700'"
                    class="p-3.5 rounded-xl border text-left transition shadow-sm flex flex-col justify-between h-full group cursor-pointer">
                <div>
                    <h4 class="text-[11px] font-bold text-[#0033a0] dark:text-blue-400 tracking-wider uppercase mb-1.5 leading-snug">
                        TRACKING &amp; STATUS
                    </h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-normal">
                        Monitor your request and notifications
                    </p>
                </div>
            </button>

            <!-- Topic 4 -->
            <button @click="selectedCategory = selectedCategory === 'messages' ? 'all' : 'messages'"
                    :class="selectedCategory === 'messages' ? 'border-[#0033a0] ring-2 ring-[#0033a0]/20 bg-blue-50/50 dark:bg-blue-950/30' : 'border-gray-200 dark:border-zinc-800 bg-white dark:bg-[#1c1c1e] hover:border-gray-300 dark:hover:border-zinc-700'"
                    class="p-3.5 rounded-xl border text-left transition shadow-sm flex flex-col justify-between h-full group cursor-pointer">
                <div>
                    <h4 class="text-[11px] font-bold text-[#0033a0] dark:text-blue-400 tracking-wider uppercase mb-1.5 leading-snug">
                        MESSAGES &amp; FEEDBACK
                    </h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-normal">
                        Communication and service feedback
                    </p>
                </div>
            </button>

            <!-- Topic 5 -->
            <button @click="selectedCategory = selectedCategory === 'technical' ? 'all' : 'technical'"
                    :class="selectedCategory === 'technical' ? 'border-[#0033a0] ring-2 ring-[#0033a0]/20 bg-blue-50/50 dark:bg-blue-950/30' : 'border-gray-200 dark:border-zinc-800 bg-white dark:bg-[#1c1c1e] hover:border-gray-300 dark:hover:border-zinc-700'"
                    class="p-3.5 rounded-xl border text-left transition shadow-sm flex flex-col justify-between h-full group cursor-pointer col-span-2 sm:col-span-1">
                <div>
                    <h4 class="text-[11px] font-bold text-[#0033a0] dark:text-blue-400 tracking-wider uppercase mb-1.5 leading-snug">
                        TECHNICAL SUPPORT
                    </h4>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-normal">
                        Sign in issues and technical concerns
                    </p>
                </div>
            </button>

        </div>
    </div>

    <!-- 3. Main Content: Sidebar + FAQ Accordions -->
    <div class="max-w-7xl mx-auto px-6 sm:px-12 py-6 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start mb-12">
        
        <!-- Left Sidebar ("Browse by Topic") - Only sticky on desktop (lg:) -->
        <div class="lg:col-span-3 bg-white dark:bg-[#1c1c1e] p-5 rounded-xl border border-gray-200 dark:border-zinc-800 shadow-sm lg:sticky lg:top-24">
            <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-3 px-1">
                Browse by Topic
            </h4>
            <nav class="space-y-1">
                <button @click="selectedCategory = 'all'"
                        :class="selectedCategory === 'all' 
                            ? 'bg-blue-50 dark:bg-blue-950/40 text-[#0033a0] dark:text-blue-400 font-bold border-l-4 border-[#0033a0] dark:border-blue-400 pl-3' 
                            : 'text-gray-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-zinc-800/60 font-medium pl-4'"
                        class="w-full text-left py-2 pr-3 text-xs rounded-r-lg transition cursor-pointer">
                    All FAQs
                </button>
                
                <button @click="selectedCategory = 'general'"
                        :class="selectedCategory === 'general' 
                            ? 'bg-blue-50 dark:bg-blue-950/40 text-[#0033a0] dark:text-blue-400 font-bold border-l-4 border-[#0033a0] dark:border-blue-400 pl-3' 
                            : 'text-gray-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-zinc-800/60 font-medium pl-4'"
                        class="w-full text-left py-2 pr-3 text-xs rounded-r-lg transition cursor-pointer">
                    General Questions
                </button>

                <button @click="selectedCategory = 'requests'"
                        :class="selectedCategory === 'requests' 
                            ? 'bg-blue-50 dark:bg-blue-950/40 text-[#0033a0] dark:text-blue-400 font-bold border-l-4 border-[#0033a0] dark:border-blue-400 pl-3' 
                            : 'text-gray-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-zinc-800/60 font-medium pl-4'"
                        class="w-full text-left py-2 pr-3 text-xs rounded-r-lg transition cursor-pointer">
                    Service Requests
                </button>

                <button @click="selectedCategory = 'tracking'"
                        :class="selectedCategory === 'tracking' 
                            ? 'bg-blue-50 dark:bg-blue-950/40 text-[#0033a0] dark:text-blue-400 font-bold border-l-4 border-[#0033a0] dark:border-blue-400 pl-3' 
                            : 'text-gray-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-zinc-800/60 font-medium pl-4'"
                        class="w-full text-left py-2 pr-3 text-xs rounded-r-lg transition cursor-pointer">
                    Tracking &amp; Status
                </button>

                <button @click="selectedCategory = 'messages'"
                        :class="selectedCategory === 'messages' 
                            ? 'bg-blue-50 dark:bg-blue-950/40 text-[#0033a0] dark:text-blue-400 font-bold border-l-4 border-[#0033a0] dark:border-blue-400 pl-3' 
                            : 'text-gray-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-zinc-800/60 font-medium pl-4'"
                        class="w-full text-left py-2 pr-3 text-xs rounded-r-lg transition cursor-pointer">
                    Messages &amp; Feedback
                </button>

                <button @click="selectedCategory = 'technical'"
                        :class="selectedCategory === 'technical' 
                            ? 'bg-blue-50 dark:bg-blue-950/40 text-[#0033a0] dark:text-blue-400 font-bold border-l-4 border-[#0033a0] dark:border-blue-400 pl-3' 
                            : 'text-gray-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-zinc-800/60 font-medium pl-4'"
                        class="w-full text-left py-2 pr-3 text-xs rounded-r-lg transition cursor-pointer">
                    Technical Support
                </button>
            </nav>
        </div>

        <!-- Right Main Area -->
        <div class="lg:col-span-9">
            <!-- Header Controls Line -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                        Frequently Asked Questions
                    </h2>
                    <span class="text-xs text-gray-500 dark:text-gray-400" x-text="filteredFaqs.length + ' results'"></span>
                </div>

                <button @click="toggleExpandAll()" class="px-3 py-1.5 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 transition shadow-sm cursor-pointer">
                    <span x-text="allExpanded() ? 'Collapse all' : 'Expand all'"></span>
                </button>
            </div>

            <!-- Accordion Items List -->
            <div class="space-y-3">
                <template x-for="item in filteredFaqs" :key="item.id">
                    <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm transition">
                        
                        <!-- Question Header Button -->
                        <button @click="toggleItem(item.id)" 
                                class="w-full p-4 text-left flex items-start justify-between gap-4 cursor-pointer hover:bg-gray-50/50 dark:hover:bg-zinc-800/30 transition">
                            <div>
                                <!-- Category Pill -->
                                <span class="inline-block px-2.5 py-0.5 text-[10px] font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-zinc-800 rounded-md mb-2">
                                    <span x-text="item.categoryName"></span>
                                </span>
                                <!-- Question Title -->
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white leading-snug" x-text="item.question"></h3>
                            </div>

                            <!-- Chevron Indicator -->
                            <div class="shrink-0 mt-1">
                                <svg class="w-4 h-4 text-[#0033a0] dark:text-blue-400 transition-transform duration-200"
                                     :class="isOpen(item.id) ? 'rotate-180' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>

                        <!-- Answer Content (Expandable) -->
                        <div x-show="isOpen(item.id)" 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="px-4 pb-4 text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed border-t border-gray-100 dark:border-zinc-800/60 pt-3">
                            <p x-text="item.answer"></p>
                        </div>

                    </div>
                </template>

                <!-- Empty State -->
                <div x-show="filteredFaqs.length === 0" x-cloak class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-xl p-8 text-center">
                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">No matching questions found.</p>
                    <button @click="searchQuery = ''; selectedCategory = 'all'" class="mt-3 text-xs font-bold text-[#0033a0] dark:text-blue-400 hover:underline cursor-pointer">
                        Clear search and filters
                    </button>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
