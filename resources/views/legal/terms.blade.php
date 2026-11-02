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
                <span class="text-[#0033a0] dark:text-blue-400 font-bold">Terms &amp; Conditions</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-[#0033a0] dark:text-blue-400 tracking-tight">
                Terms and Conditions
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mt-2 max-w-2xl leading-relaxed">
                Guidelines, responsibilities, and acceptable use policies governing access to the Bicol University General Services Office (BU-GSO) LINKod Service Request System.
            </p>
            <div class="mt-4 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white dark:bg-zinc-800 border border-blue-200 dark:border-zinc-700 text-[11px] font-bold text-slate-600 dark:text-gray-300">
                <span>Last Updated: October 2026</span>
                <span>•</span>
                <span>Official BU-GSO Portal Policy</span>
            </div>
        </div>
    </section>

    <!-- Terms Content -->
    <div class="max-w-5xl w-full mx-auto px-6 sm:px-12 py-10 space-y-6">
        <div class="bg-white dark:bg-[#1c1c1e] border border-gray-200 dark:border-zinc-800 rounded-2xl p-6 sm:p-9 shadow-sm space-y-8 text-sm text-slate-700 dark:text-gray-300 leading-relaxed">

            <!-- 1. Acceptance & Eligibility -->
            <section class="space-y-2.5">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-[#0033a0] dark:text-blue-400 text-xs font-black flex items-center justify-center">1</span>
                    <span>Acceptance of Terms &amp; Eligibility</span>
                </h2>
                <p>
                    By signing in to or using <strong>BU-GSO LINKod</strong>, you agree to comply with these Terms and Conditions and all applicable Bicol University ICT and administrative policies. Access to LINKod is strictly restricted to authorized Bicol University faculty, administrative staff, GSO maintenance personnel, and recognized university offices holding an active institutional Google account (<code>@bicol-u.edu.ph</code>).
                </p>
            </section>

            <!-- 2. Proper Submission of Service Requests -->
            <section class="space-y-2.5">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-[#0033a0] dark:text-blue-400 text-xs font-black flex items-center justify-center">2</span>
                    <span>Submission of Official Requisitions</span>
                </h2>
                <ul class="list-disc pl-6 space-y-1.5 text-xs sm:text-sm">
                    <li>Service requests must pertain solely to official Bicol University buildings, campuses, facilities, grounds, equipment, or sanctioned university activities.</li>
                    <li>Requestors must provide accurate campus, college/office, room location, active contact number, and truthful descriptions of the concern.</li>
                    <li>Separate concerns involving distinct trades (e.g., Plumbing vs. Carpentry) or separate building locations should be submitted clearly so GSO can dispatch the appropriate unit.</li>
                    <li>Clients may cancel a submitted requisition prior to administrative approval and project deployment; once approved and scheduled, cancellation requests must be coordinated through GSO Messages.</li>
                </ul>
            </section>

            <!-- 3. Scheduling, Inspection & List of Materials -->
            <section class="space-y-2.5">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-[#0033a0] dark:text-blue-400 text-xs font-black flex items-center justify-center">3</span>
                    <span>Scheduling, Inspection &amp; List of Materials</span>
                </h2>
                <p>
                    Request prioritization and scheduling are determined by GSO based on urgency, safety impact, workforce availability, and material readiness. Where replacement parts or supplies are required, a verified <strong>List of Materials</strong> will be presented on your request page for review and client approval before procurement or installation proceeds.
                </p>
            </section>

            <!-- 4. Mandatory Service Evaluation -->
            <section class="space-y-2.5">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-[#0033a0] dark:text-blue-400 text-xs font-black flex items-center justify-center">4</span>
                    <span>Post-Completion Service Evaluation</span>
                </h2>
                <p>
                    To uphold service quality standards across Bicol University, clients are required to complete a brief Service Evaluation once a requisition is marked <strong>Completed</strong>. Submission of new service requests is temporarily locked while an earlier completed request remains unrated.
                </p>
            </section>

            <!-- 5. Acceptable Use & File Uploads -->
            <section class="space-y-2.5">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-[#0033a0] dark:text-blue-400 text-xs font-black flex items-center justify-center">5</span>
                    <span>Acceptable Use, Messaging &amp; Attachments</span>
                </h2>
                <p>Users agree <strong>not</strong> to:</p>
                <ul class="list-disc pl-6 space-y-1.5 text-xs sm:text-sm">
                    <li>Submit fraudulent, prank, abusive, or personal/non-university maintenance requests;</li>
                    <li>Upload malicious files, executable scripts, orunrelated content (only supported JPG, PNG, WEBP images and PDF documents up to 10 MB are permitted);</li>
                    <li>Attempt to bypass role-based access controls, probe system endpoints, or share institutional credentials with unauthorized individuals;</li>
                    <li>Use the built-in request messaging channel for harassment or unprofessional conduct.</li>
                </ul>
            </section>

            <!-- 6. Account Security -->
            <section class="space-y-2.5">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-[#0033a0] dark:text-blue-400 text-xs font-black flex items-center justify-center">6</span>
                    <span>Account Security &amp; Availability</span>
                </h2>
                <p>
                    You are responsible for safeguarding access to your BU Google account and signing out when using shared office workstations. BU-GSO strives to maintain continuous availability of LINKod during and outside standard university office hours (Monday–Friday, 8:00 AM – 5:00 PM), subject to scheduled maintenance or emergency infrastructure updates.
                </p>
            </section>

            <!-- 7. Updates & Contact -->
            <section class="space-y-2.5 pt-4 border-t border-gray-100 dark:border-zinc-800">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 dark:bg-blue-950/70 text-[#0033a0] dark:text-blue-400 text-xs font-black flex items-center justify-center">7</span>
                    <span>Policy Updates &amp; Contact</span>
                </h2>
                <p>
                    BU-GSO may update these Terms and Conditions periodically to reflect operational improvements or university regulations. For questions regarding these terms, please review our <a href="{{ route('privacy.policy') }}" class="text-[#0033a0] dark:text-blue-400 font-bold hover:underline">Privacy Policy</a>, visit our <a href="{{ route('faq') }}" class="text-[#0033a0] dark:text-blue-400 font-bold hover:underline">Help Center / FAQs</a>, or email <a href="mailto:bu-gso@bicol-u.edu.ph" class="text-[#0033a0] dark:text-blue-400 font-bold hover:underline">bu-gso@bicol-u.edu.ph</a>.
                </p>
            </section>

        </div>
    </div>
</div>
@endsection

