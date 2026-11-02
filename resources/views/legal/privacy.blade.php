@extends('layouts.client')

@section('fullwidth', true)

@section('content')
<div class="w-full flex flex-col font-sans bg-slate-50/50 dark:bg-[#111111] min-h-[calc(100vh-64px)]">
    <!-- Hero Banner -->
    <section class="w-full bg-[#edf4fb] dark:bg-[#18181b] py-12 px-6 sm:px-12 border-b border-gray-200 dark:border-zinc-800">
        <div class="max-w-5xl mx-auto">
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                <a href="{{ route('home') }}" class="hover:text-[#0033a0] dark:hover:text-blue-400 transition">Home</a>
                <span>/</span>
                <span class="text-[#0033a0] dark:text-blue-400 font-bold">Privacy Policy</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-[#0033a0] dark:text-blue-400 tracking-tight">
                Privacy Policy
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mt-2 max-w-2xl leading-relaxed">
                How the Bicol University General Services Office (BU-GSO) collects, protects, and processes personal data within the LINKod Service Request System in accordance with the Data Privacy Act of 2012 (Republic Act No. 10173).
            </p>
            <div class="mt-4 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white dark:bg-zinc-800 border border-blue-200 dark:border-zinc-700 text-[11px] font-bold text-slate-600 dark:text-gray-300">
                <span>Effective Date: October 2026</span>
                <span>•</span>
                <span>RA 10173 Compliant</span>
            </div>
        </div>
    </section>

    <!-- Policy Content -->
    <div class="max-w-5xl w-full mx-auto px-6 sm:px-12 py-10 space-y-6">
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 sm:p-9 shadow-sm space-y-8 text-sm text-slate-700 dark:text-gray-300 leading-relaxed">

            <!-- 1. Overview -->
            <section class="space-y-2.5">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-[#0033a0] dark:text-blue-400 text-xs font-black flex items-center justify-center">1</span>
                    <span>Commitment to Data Privacy</span>
                </h2>
                <p>
                    Bicol University – General Services Office (<strong>BU-GSO</strong>) respects and upholds your right to privacy. This Privacy Policy explains how <strong>LINKod</strong> (BU-GSO Service Request Management System) collects, uses, stores, and safeguards personal information of authorized university faculty, staff, maintenance personnel, and administrators in compliance with <strong>Republic Act No. 10173</strong>, otherwise known as the <em>Data Privacy Act of 2012</em>, and its Implementing Rules and Regulations.
                </p>
            </section>

            <!-- 2. Information We Collect -->
            <section class="space-y-2.5">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-[#0033a0] dark:text-blue-400 text-xs font-black flex items-center justify-center">2</span>
                    <span>Information We Collect</span>
                </h2>
                <p>When you sign in and use LINKod, we collect only the minimum information necessary to deliver official university maintenance and general services:</p>
                <ul class="list-disc pl-6 space-y-1.5 text-xs sm:text-sm">
                    <li><strong>Account &amp; Identity Data:</strong> Your full name, official Bicol University Google email address (<code>@bicol-u.edu.ph</code>), account role, and college/office affiliation via Google Single Sign-On (SSO).</li>
                    <li><strong>Contact Information:</strong> Official contact or mobile number provided on service requisitions so assigned GSO teams can coordinate on-site inspections and repairs.</li>
                    <li><strong>Service Request &amp; Operational Records:</strong> Facility/building locations, job descriptions, supporting photo or PDF attachments, List of Materials approvals, request messages, and post-service evaluation ratings.</li>
                    <li><strong>Security &amp; Audit Logs:</strong> Login timestamps, IP addresses, Two-Factor Authentication (2FA) verification status, and administrative action logs maintained for system integrity and accountability.</li>
                </ul>
            </section>

            <!-- 3. How We Use Your Information -->
            <section class="space-y-2.5">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-[#0033a0] dark:text-blue-400 text-xs font-black flex items-center justify-center">3</span>
                    <span>Purpose of Processing</span>
                </h2>
                <p>Personal and operational data collected in LINKod is processed strictly for legitimate university operations, including:</p>
                <ul class="list-disc pl-6 space-y-1.5 text-xs sm:text-sm">
                    <li>Validating your eligibility as an authorized Bicol University employee or unit representative;</li>
                    <li>Assessing, prioritizing, scheduling, and dispatching GSO maintenance, janitorial, landscaping, and manpower teams;</li>
                    <li>Sending real-time portal notifications and official email updates regarding your requisition status, schedules, and material approvals;</li>
                    <li>Generating official GSO accomplishment reports, material utilization summaries, and service quality analytics;</li>
                    <li>Maintaining cybersecurity, audit trails, and protection against unauthorized access.</li>
                </ul>
            </section>

            <!-- 4. Data Protection & Security -->
            <section class="space-y-2.5">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-[#0033a0] dark:text-blue-400 text-xs font-black flex items-center justify-center">4</span>
                    <span>Data Storage &amp; Security Measures</span>
                </h2>
                <p>
                    BU-GSO implements organizational, physical, and technical security measures to protect your data against unauthorized access, alteration, or disclosure:
                </p>
                <ul class="list-disc pl-6 space-y-1.5 text-xs sm:text-sm">
                    <li><strong>Encryption:</strong> Sensitive personal attributes are encrypted at rest using industry-standard AES-256 encryption, and all network traffic is encrypted in transit via HTTPS/TLS.</li>
                    <li><strong>Role-Based Access Control (RBAC):</strong> Clients can only view and manage their own submitted requisitions; Workers only see assigned job orders; Administrative oversight is restricted to authorized GSO personnel.</li>
                    <li><strong>Optional Two-Factor Authentication (2FA):</strong> Users may enable TOTP-based 2FA in Profile Settings for additional account protection.</li>
                </ul>
            </section>

            <!-- 5. Data Sharing & Retention -->
            <section class="space-y-2.5">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-[#0033a0] dark:text-blue-400 text-xs font-black flex items-center justify-center">5</span>
                    <span>Data Sharing &amp; Retention</span>
                </h2>
                <p>
                    We do <strong>not</strong> sell, rent, or trade your personal information to external third parties. Data is shared strictly within Bicol University's authorized GSO administrators and assigned maintenance staff to fulfill your request. When submitting post-completion service evaluations, you may choose whether to display your name or submit feedback anonymously. Official job requisition and accomplishment records are retained in accordance with university and National Archives of the Philippines retention schedules.
                </p>
            </section>

            <!-- 6. Cookies & Local Storage -->
            <section class="space-y-2.5">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-[#0033a0] dark:text-blue-400 text-xs font-black flex items-center justify-center">6</span>
                    <span>Cookies &amp; Browser Storage</span>
                </h2>
                <p>
                    LINKod uses strictly necessary cookies for session authentication, CSRF security tokens, and access verification, as well as optional functional storage for dark-mode display settings and temporary form drafts. We do not use third-party advertising or cross-site tracking cookies.
                </p>
                <div>
                    <button type="button"
                            onclick="window.dispatchEvent(new CustomEvent('open-cookie-settings'))"
                            class="px-4 py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/60 dark:hover:bg-blue-900/60 text-[#0033a0] dark:text-blue-300 border border-blue-200 dark:border-blue-800 rounded-xl text-xs font-bold transition cursor-pointer">
                        Manage Cookie Preferences
                    </button>
                </div>
            </section>

            <!-- 7. Data Subject Rights & Contact -->
            <section class="space-y-2.5 pt-4 border-t border-gray-100 dark:border-zinc-800">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-[#0033a0] dark:text-blue-400 text-xs font-black flex items-center justify-center">7</span>
                    <span>Your Rights &amp; Contact Information</span>
                </h2>
                <p>
                    Under the Data Privacy Act of 2012, you have the right to be informed, to access and rectify your personal data, to object to unauthorized processing, and to lodge a complaint before the National Privacy Commission. For privacy inquiries or support, please contact:
                </p>
                <div class="bg-slate-50 dark:bg-zinc-800/60 border border-gray-200 dark:border-zinc-700 rounded-xl p-4 text-xs sm:text-sm space-y-1">
                    <div class="font-bold text-slate-900 dark:text-white">Bicol University – General Services Office (BU-GSO)</div>
                    <div>Bicol University Main Campus, Legazpi City, Albay, Philippines</div>
                    <div>Email: <a href="mailto:bu-gso@bicol-u.edu.ph" class="text-[#0033a0] dark:text-blue-400 font-semibold hover:underline">bu-gso@bicol-u.edu.ph</a></div>
                </div>
            </section>

        </div>
    </div>
</div>
@endsection

