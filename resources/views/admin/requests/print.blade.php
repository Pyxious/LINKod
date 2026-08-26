<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Job Request Form - BU-F-GSO-2</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 12mm 8mm 12mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        html, body {
            width: 100%;
            height: 100%;
        }
        body {
            font-family: "Arial", "Helvetica Neue", sans-serif;
            font-size: 10.5px;
            color: #000;
            background: #fff;
            line-height: 1.3;
        }

        /* ─── PAGE ─────────────────────────────────────── */
        .page {
            width: 186mm;
            height: 277mm;           /* A4 - top/bottom margins */
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            page-break-after: always;
            break-after: page;
            overflow: hidden;
        }
        .page:last-child {
            page-break-after: avoid;
            break-after: avoid;
        }
        .page-standard {
            justify-content: space-between;
        }

        /* ─── HEADER ─────────────────────────────────────── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .header-table td { vertical-align: middle; }
        .hd-left  { width: 20%; text-align: center; }
        .hd-left img { width: 68px; height: auto; display: block; margin: 0 auto; }
        .iso-text { font-size: 7px; line-height: 1.1; margin-top: 2px; color: #333; }
        .hd-mid   { width: 60%; text-align: center; }
        .hd-mid .univ   { font-size: 12px; font-weight: 500; }
        .hd-mid .office { font-size: 13.5px; font-weight: bold; letter-spacing: 0.4px; }
        .hd-mid .city   { font-size: 11px; }
        .hd-mid .ftitle { font-size: 12.5px; font-weight: bold; margin-top: 2px; text-transform: uppercase; }
        .hd-mid .categ  { font-size: 11.5px; font-weight: bold; margin-top: 1px; text-transform: uppercase; }
        .hd-right { width: 20%; text-align: right; }
        .hd-right img { width: 130px; height: auto; }

        /* ─── REQ NO ─────────────────────────────────────── */
        .req-row {
            text-align: right;
            font-weight: bold;
            font-size: 11.5px;
            margin-bottom: 3px;
        }
        .req-line {
            display: inline-block;
            width: 130px;
            border-bottom: 1.5px solid #000;
            text-align: center;
            font-weight: bold;
        }

        /* ─── INFO TABLE ─────────────────────────────────── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            margin-bottom: 3px;
        }
        .info-table td {
            padding: 3.5px 6px;
            font-size: 11px;
            vertical-align: middle;
            border-bottom: 1px solid #000;
        }
        .info-table tr:last-child td { border-bottom: none; }
        .info-table .lbl { font-weight: bold; white-space: nowrap; width: 20%; }
        .info-table .val { width: 30%; }
        .info-table .bl  { border-left: 2px solid #000; }

        /* ─── MANPOWER BOX ───────────────────────────────── */
        .mp-box { border: 2px solid #000; flex: 1; display: flex; flex-direction: column; margin: 3px 0 4px; }
        .mp-box-hdr {
            text-align: center;
            font-weight: bold;
            padding: 4px 6px;
            border-bottom: 2px solid #000;
            font-size: 10.5px;
            line-height: 1.35;
            flex-shrink: 0;
        }
        .mp-section {
            padding: 5px 8px;
            border-bottom: 1.5px solid #000;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .mp-section:last-child { border-bottom: none; }
        .mp-title {
            font-weight: bold;
            font-size: 10.5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3px;
            flex-shrink: 0;
        }
        .write-lines { flex: 1; display: flex; flex-direction: column; justify-content: space-around; margin: 2px 0; }
        .write-line {
            border-bottom: 1px solid #999;
            flex: 1;
            min-height: 16px;
            max-height: 26px;
            line-height: 1;
            font-size: 11px;
            padding: 2px 4px 0;
            display: flex;
            align-items: flex-end;
        }
        .time-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10.5px;
            font-weight: bold;
            margin-top: 3px;
            padding: 0 6px;
            flex-shrink: 0;
        }
        .cb-inline { display: inline-flex; align-items: center; gap: 6px; }
        .box-sq {
            display: inline-block;
            width: 14px; height: 14px;
            border: 1.5px solid #000;
            text-align: center;
            line-height: 12px;
            font-size: 11px;
            font-weight: bold;
            flex-shrink: 0;
        }
        .uline {
            display: inline-block;
            border-bottom: 1.5px solid #000;
            min-width: 120px;
            text-align: center;
            padding: 0 4px;
        }

        /* ─── SIGN LINE ─────────────────────────────────── */
        .sign-line { border-bottom: 1.5px solid #000; height: 1px; margin-bottom: 2px; }
        .sign-block { text-align: center; }

        /* ─── FOOTER ─────────────────────────────────────── */
        .footer {
            display: flex;
            justify-content: space-between;
            font-size: 8.5px;
            color: #222;
            margin-top: 4px;
            padding-top: 2px;
            flex-shrink: 0;
        }

        /* ─── STAFF GRID ─────────────────────────────────── */
        .staff-grid { display: flex; gap: 16px; flex: 1; margin: 6px 0; }
        .staff-col  { flex: 1; display: flex; flex-direction: column; }
        .staff-line {
            border-bottom: 1px solid #777;
            flex: 1;
            min-height: 20px;
            max-height: 30px;
            line-height: 1;
            font-size: 11px;
            padding: 3px 6px 0;
            font-weight: 500;
            display: flex;
            align-items: flex-end;
        }

        /* ─── EQUIP BOX ─────────────────────────────────── */
        .equip-box {
            border: 2px solid #000;
            flex-shrink: 0;
            margin-bottom: 6px;
        }
        .equip-hdr {
            text-align: center;
            font-weight: bold;
            padding: 4px 6px;
            border-bottom: 2px solid #000;
            font-size: 10.5px;
            line-height: 1.3;
        }
        .equip-lines { padding: 4px 8px; }
        .equip-line { border-bottom: 1px solid #777; min-height: 22px; margin: 2px 0; }

        /* ─── STANDARD REPAIR STYLES ─────────────────────── */
        .sec-divider {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            padding: 3.5px 0;
            margin: 4px 0;
            letter-spacing: 0.4px;
        }
        .desc-box {
            border-bottom: 1.5px solid #000;
            padding: 6px 8px 4px;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .rec-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            min-height: 110px;
            margin: 3px 0 4px;
        }
        .rec-table th, .rec-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 10.5px;
            vertical-align: top;
        }
        .rec-table td {
            height: 95px;
        }

        @media print {
            .page { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

@php
    $catName = strtolower($serviceRequest->category->category_name ?? '');
    $isManpower = str_contains($catName, 'manpower') || str_contains($catName, 'event');
    $details = $serviceRequest->manpower_details;
    $clientUser = $serviceRequest->client?->user;
    $clientName = $clientUser ? trim($clientUser->first_name . ' ' . $clientUser->last_name) : 'Client Requestor';
    $contactNo = $clientUser?->contact_number ?? $serviceRequest->client?->contact_number ?? 'N/A';
    $submittedDate = $serviceRequest->submitted_at ? $serviceRequest->submitted_at->format('F d, Y') : date('F d, Y');

    $prefix = match(true) {
        str_contains($catName, 'landscaping') => 'LS',
        str_contains($catName, 'janitorial') => 'JS',
        str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') => 'CMS',
        str_contains($catName, 'plumbing') => 'PLS',
        str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'EMS',
        str_contains($catName, 'painting') || str_contains($catName, 'paint') => 'PAINT',
        str_contains($catName, 'manpower') || str_contains($catName, 'event') => 'MAN',
        default => 'REQ'
    };
    $reqCode = $serviceRequest->requisition_no ?: ($prefix . '-' . str_pad($serviceRequest->request_id, 3, '0', STR_PAD_LEFT));
    $assignedWorkers = $serviceRequest->project?->workers ?? collect();

    // Query parameter dates from admin request show view
    $rawDateStarted = request()->query('date_started');
    $rawTargetComp  = request()->query('target_completion');
    $rawSiteVisit   = request()->query('site_visit_date');

    $dateStarted = $rawDateStarted ? \Carbon\Carbon::parse($rawDateStarted)->format('F d, Y') : '';
    $targetCompletion = $rawTargetComp ? \Carbon\Carbon::parse($rawTargetComp)->format('F d, Y') : '';

    // Date of site visit dynamically reads today's current date
    $siteVisitDate = $rawSiteVisit ? \Carbon\Carbon::parse($rawSiteVisit)->format('F d, Y') : now()->format('F d, Y');

    // Team Leader Assessed By name fallback
    $assessedByName = $serviceRequest->project?->approvedBy?->user 
        ? strtoupper(trim($serviceRequest->project->approvedBy->user->first_name . ' ' . $serviceRequest->project->approvedBy->user->last_name)) 
        : 'MR. DIOGENES L. LONDONIO';
@endphp

@if($isManpower)
{{-- ═══════════════════════════════════════════════════════
     PAGE 1 — MANPOWER WORK DETAILS
══════════════════════════════════════════════════════════ --}}
<div class="page">

    {{-- Header --}}
    <table class="header-table" style="flex-shrink:0;">
        <tr>
            <td class="hd-left">
                <img src="{{ asset('images/left-toplogo.png') }}" alt="BU Logo">
                <div class="iso-text">ISO 9001:2015<br>SOCOTEC SCP000722Q</div>
            </td>
            <td class="hd-mid">
                <div class="univ">Bicol University</div>
                <div class="office">GENERAL SERVICES OFFICE</div>
                <div class="city">Legazpi City</div>
                <div class="ftitle">Service Job Request Form</div>
                <div class="categ">Manpower Services For Special Events</div>
            </td>
            <td class="hd-right">
                <img src="{{ asset('images/right-top logo.png') }}" alt="Bagong Pilipinas Logo">
            </td>
        </tr>
    </table>

    {{-- Req No --}}
    <div class="req-row" style="flex-shrink:0;">
        Requisition No. <span class="req-line">{{ $reqCode }}</span>
    </div>

    {{-- Info Table --}}
    <table class="info-table" style="flex-shrink:0;">
        <tr>
            <td class="lbl">REQUESTED BY:</td>
            <td class="val"><strong>{{ $clientName }}</strong></td>
            <td class="lbl bl">DATE:</td>
            <td class="val">{{ $submittedDate }}</td>
        </tr>
        <tr>
            <td class="lbl">OFFICE:</td>
            <td class="val">{{ $details['venue'] ?: ($serviceRequest->location ?? '') }}</td>
            <td class="lbl bl">CONTACT NO.:</td>
            <td class="val">{{ $contactNo }}</td>
        </tr>
        <tr>
            <td class="lbl">TITLE OF THE ACTIVITY:</td>
            <td class="val" colspan="3"><strong>{{ $details['activity_title'] ?: $serviceRequest->title }}</strong></td>
        </tr>
        <tr>
            <td class="lbl">DATE:</td>
            <td class="val" colspan="3">{{ $details['event_date'] ?: $submittedDate }}</td>
        </tr>
        <tr>
            <td class="lbl">VENUE:</td>
            <td class="val" colspan="3">{{ $details['venue'] ?: ($serviceRequest->location ?? '') }}</td>
        </tr>
        <tr>
            <td class="lbl">ADDRESS:</td>
            <td class="val" colspan="3">{{ $serviceRequest->campus ?? 'Legazpi City' }}</td>
        </tr>
    </table>

    {{-- Work Details Box — grows to fill remaining space --}}
    <div class="mp-box">
        <div class="mp-box-hdr">
            WORK DETAILS TO BE COMPLETED FOR THE<br>
            &ldquo; PREPARATION, ASSISTANCE DURING THE EVENT PROPER &amp; CLEARING UPON &rdquo;
        </div>

        {{-- 1. Preparation Activity --}}
        <div class="mp-section">
            <div class="mp-title">
                <span>PREPARATION ACTIVITY:</span>
                <span>DATE: <span class="uline" style="min-width:220px;">{{ $details['prep_date'] ?? '' }}</span></span>
            </div>
            <div class="write-lines">
                <div class="write-line">{{ $details['prep_details'] ?? '' }}</div>
                <div class="write-line"></div>
            </div>
            <div class="time-row">
                <div class="cb-inline">
                    <span class="box-sq">{{ !empty($details['prep_regular']) ? 'X' : '' }}</span>
                    Regular time:
                    <span class="uline" style="min-width:160px;">{{ !empty($details['prep_regular']) ? ($details['prep_regular_time'] ?: '8:00 - 12:00 / 1:00 - 5:00') : '' }}</span>
                </div>
                <div class="cb-inline">
                    <span class="box-sq">{{ !empty($details['prep_overtime']) ? 'X' : '' }}</span>
                    Overtime:
                    <span class="uline" style="min-width:120px;">{{ !empty($details['prep_overtime']) ? ($details['prep_overtime_time'] ?: '') : '' }}</span>
                </div>
            </div>
        </div>

        {{-- 2. Assistance During Event --}}
        <div class="mp-section">
            <div class="mp-title">
                <span>ASSISTANCE DURING THE EVENT:</span>
                <span>DATE: <span class="uline" style="min-width:220px;">{{ $details['assistance_date'] ?? '' }}</span></span>
            </div>
            <div class="write-lines">
                <div class="write-line">{{ $details['assistance_details'] ?? '' }}</div>
                <div class="write-line"></div>
            </div>
            <div class="time-row">
                <div class="cb-inline">
                    <span class="box-sq">{{ !empty($details['assistance_regular']) ? 'X' : '' }}</span>
                    Regular time:
                    <span class="uline" style="min-width:160px;">{{ !empty($details['assistance_regular']) ? ($details['assistance_regular_time'] ?: '8:00 - 12:00 / 1:00 - 5:00') : '' }}</span>
                </div>
                <div class="cb-inline">
                    <span class="box-sq">{{ !empty($details['assistance_overtime']) ? 'X' : '' }}</span>
                    Overtime:
                    <span class="uline" style="min-width:120px;">{{ !empty($details['assistance_overtime']) ? ($details['assistance_overtime_time'] ?: '') : '' }}</span>
                </div>
            </div>
        </div>

        {{-- 3. Clearing Upon Event --}}
        <div class="mp-section">
            <div class="mp-title">
                <span>CLEARING UPON THE EVENT:</span>
                <span>DATE: <span class="uline" style="min-width:220px;">{{ $details['clearing_date'] ?? '' }}</span></span>
            </div>
            <div class="write-lines">
                <div class="write-line">{{ $details['clearing_details'] ?? '' }}</div>
                <div class="write-line"></div>
            </div>
            <div class="time-row">
                <div class="cb-inline">
                    <span class="box-sq">{{ !empty($details['clearing_regular']) ? 'X' : '' }}</span>
                    Regular time:
                    <span class="uline" style="min-width:160px;">{{ !empty($details['clearing_regular']) ? ($details['clearing_regular_time'] ?: '8:00 - 12:00 / 1:00 - 5:00') : '' }}</span>
                </div>
                <div class="cb-inline">
                    <span class="box-sq">{{ !empty($details['clearing_overtime']) ? 'X' : '' }}</span>
                    Overtime:
                    <span class="uline" style="min-width:120px;">{{ !empty($details['clearing_overtime']) ? ($details['clearing_overtime_time'] ?: '') : '' }}</span>
                </div>
            </div>
        </div>

        {{-- 4. Additional Note --}}
        <div class="mp-section">
            <div class="mp-title">
                <span>ADDITIONAL NOTE: <span style="font-weight:normal;font-size:9.5px;">(SUPPLIES, MATERIALS, TOOLS, EQUIPMENT TO BE USED)</span></span>
                <span>DATE: <span class="uline" style="min-width:160px;">{{ $details['additional_date'] ?? '' }}</span></span>
            </div>
            <div class="write-lines">
                <div class="write-line">{{ $details['additional_notes'] ?? '' }}</div>
                <div class="write-line"></div>
            </div>
        </div>
    </div>

    {{-- Client Signature --}}
    <div style="margin-top:8px; text-align:center; flex-shrink:0;">
        <div style="width:300px; margin:0 auto;">
            <div class="sign-line"></div>
            <div style="font-weight:bold; font-size:11.5px; margin-top:2px;">{{ strtoupper($clientName) }}</div>
            <div style="font-size:9.5px; font-weight:bold; text-transform:uppercase;">CLIENTS NAME AND SIGNATURE</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div>BU-F-GSO-2<br>Effective Date: October 9, 2025</div>
        <div>Revision 2</div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     PAGE 2 — STAFF TO BE ASSIGNED
══════════════════════════════════════════════════════════ --}}
<div class="page">

    {{-- Header --}}
    <table class="header-table" style="flex-shrink:0;">
        <tr>
            <td class="hd-left">
                <img src="{{ asset('images/left-toplogo.png') }}" alt="BU Logo">
                <div class="iso-text">ISO 9001:2015<br>SOCOTEC SCP000722Q</div>
            </td>
            <td class="hd-mid">
                <div class="univ">Bicol University</div>
                <div class="office">GENERAL SERVICES OFFICE</div>
                <div class="city">Legazpi City</div>
                <div class="ftitle">Service Job Request Form</div>
                <div class="categ">Staff To Be Assigned</div>
                <div style="font-size:9px; font-weight:bold; margin-top:1px;">(THE NUMBER OF PERSONNEL DEPENDS ON THE WORK LOAD)</div>
            </td>
            <td class="hd-right">
                <img src="{{ asset('images/right-top logo.png') }}" alt="Bagong Pilipinas Logo">
            </td>
        </tr>
    </table>

    {{-- Staff Grid — 2 columns, grows to fill page --}}
    <div class="staff-grid">
        <div class="staff-col">
            @for($i = 0; $i < 10; $i++)
                @php $wn = isset($assignedWorkers[$i]) ? ($assignedWorkers[$i]->user->first_name . ' ' . $assignedWorkers[$i]->user->last_name) : ''; @endphp
                <div class="staff-line">{{ $wn }}</div>
            @endfor
        </div>
        <div class="staff-col">
            @for($i = 10; $i < 20; $i++)
                @php $wn = isset($assignedWorkers[$i]) ? ($assignedWorkers[$i]->user->first_name . ' ' . $assignedWorkers[$i]->user->last_name) : ''; @endphp
                <div class="staff-line">{{ $wn }}</div>
            @endfor
        </div>
    </div>

    {{-- Operational Equipment Box --}}
    <div class="equip-box">
        <div class="equip-hdr">
            OPERATIONAL EQUIPMENT, TOOLS, AND MATERIALS TO BE USED<br>
            <span style="font-size:9px; font-weight:normal;">[IF NEEDED]</span>
        </div>
        <div class="equip-lines">
            <div class="equip-line"></div>
            <div class="equip-line"></div>
            <div class="equip-line"></div>
            <div class="equip-line"></div>
            <div class="equip-line"></div>
        </div>
    </div>

    {{-- Approvals --}}
    <div style="display:flex; justify-content:space-between; margin-top:14px; margin-bottom:16px; flex-shrink:0;">
        <div style="width:46%;">
            <div style="font-size:10.5px; font-weight:bold; margin-bottom:28px;">EVALUATED &amp; RECOMMENDING APPROVAL:</div>
            <div class="sign-line"></div>
            <div style="font-weight:bold; font-size:11px;">GSO, STAFF</div>
            <div style="margin-top:4px; font-size:10.5px;">DATE: <span class="uline" style="min-width:130px;"></span></div>
        </div>
        <div style="width:46%;">
            <div style="font-size:10.5px; font-weight:bold; margin-bottom:28px;">APPROVED FOR IMPLEMENTATION:</div>
            <div class="sign-line"></div>
            <div style="font-weight:bold; font-size:11px;">REY A. PADILLA</div>
            <div style="font-size:10px; color:#222;">Head, General Services Office</div>
        </div>
    </div>

    {{-- Client Signature --}}
    <div style="text-align:center; flex-shrink:0;">
        <div style="width:320px; margin:0 auto;">
            <div class="sign-line"></div>
            <div style="font-weight:bold; font-size:11.5px; margin-top:2px;">{{ strtoupper($clientName) }}</div>
            <div style="font-size:9.5px; font-weight:bold; text-transform:uppercase;">CLIENTS NAME AND SIGNATURE</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div>BU-F-GSO-2<br>Effective Date: October 9, 2025</div>
        <div>Revision 2</div>
    </div>
</div>

@else
{{-- ═══════════════════════════════════════════════════════
     STANDARD REPAIR & MAINTENANCE REQUEST FORM
══════════════════════════════════════════════════════════ --}}
<div class="page page-standard">

    {{-- 1. Header --}}
    <div>
        <table class="header-table">
            <tr>
                <td class="hd-left">
                    <img src="{{ asset('images/left-toplogo.png') }}" alt="BU Logo">
                    <div class="iso-text">ISO 9001:2015<br>SOCOTEC SCP000722Q</div>
                </td>
                <td class="hd-mid">
                    <div class="univ">Bicol University</div>
                    <div class="office">GENERAL SERVICES OFFICE</div>
                    <div class="city">Legazpi City</div>
                    <div class="ftitle">Service Job Request Form</div>
                    <div class="categ">{{ strtoupper($serviceRequest->category->category_name ?? 'Carpentry/Masonry/Electrical Services') }}</div>
                </td>
                <td class="hd-right">
                    <img src="{{ asset('images/right-top logo.png') }}" alt="Bagong Pilipinas Logo">
                </td>
            </tr>
        </table>

        {{-- Req No --}}
        <div class="req-row">
            Requisition No.: <span class="req-line">{{ $reqCode }}</span>
        </div>

        {{-- Info Table --}}
        <table class="info-table">
            <tr>
                <td class="lbl">REQUESTED BY:</td>
                <td class="val">{{ $clientName }}</td>
                <td class="lbl bl">DATE:</td>
                <td class="val">{{ $submittedDate }}</td>
            </tr>
            <tr>
                <td class="lbl">OFFICE:</td>
                <td class="val">{{ $serviceRequest->location ?? '' }}</td>
                <td class="lbl bl">CONTACT NO.:</td>
                <td class="val">{{ $contactNo }}</td>
            </tr>
        </table>
    </div>

    {{-- 2. Description of Work Requested --}}
    <div class="desc-box">
        <div>
            <div style="font-weight:bold; font-size:11px; text-transform:uppercase; margin-bottom:4px; text-align:center;">
                Description of Work Requested:
            </div>
            <div style="font-weight:bold; font-size:11.5px; margin-bottom:2px;">{{ $serviceRequest->title }}</div>
            <div style="font-size:11px; line-height:1.4;">{{ $serviceRequest->description }}</div>
        </div>

        <div style="display:flex; justify-content:flex-end; margin-top:14px;">
            <div style="text-align:center; width:250px;">
                <div class="sign-line"></div>
                <div style="font-size:8.5px; font-weight:bold; text-transform:uppercase; letter-spacing:0.3px;">SIGNATURE OF REQUESTING PARTY</div>
            </div>
        </div>
    </div>

    {{-- Note below description --}}
    <div style="font-style:italic; font-size:9.5px; text-align:left; margin:2px 0;">
        NOTE: Entries below are to be accomplished by the General Services Office Personnel
    </div>

    {{-- 3. Initial Inspection and Evaluation --}}
    <div>
        <div class="sec-divider">INITIAL INSPECTION AND EVALUATION</div>
        
        <div style="margin:5px 0 6px; font-size:11px;">
            Date of site visit: <span class="uline" style="min-width:240px;">{{ $siteVisitDate }}</span>
        </div>

        @php $priority = strtolower($serviceRequest->priority ?? ''); @endphp
        <div style="display:flex; justify-content:center; gap:100px; margin:5px 0 6px;">
            <div class="cb-inline">
                <span class="box-sq">{{ $priority === 'high' ? 'X' : '' }}</span> High Priority
            </div>
            <div class="cb-inline">
                <span class="box-sq">{{ in_array($priority, ['medium','low', '']) ? 'X' : '' }}</span> Routine
            </div>
        </div>
    </div>

    {{-- 4. Recommendations and Work Details --}}
    <div>
        <div class="sec-divider">RECOMMENDATIONS AND WORK DETAILS</div>
        
        <table class="rec-table">
            <thead>
                <tr>
                    <th style="width:34%; text-align:center;">NATURE OF WORK TO BE DONE:</th>
                    <th style="width:32%; text-align:center;">ASSIGNED PERSONNEL/STAFF</th>
                    <th style="width:34%; text-align:center;">SUPPLIES &amp; MATERIALS NEEDED:</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="display:flex; flex-direction:column; justify-content:space-between; height:100px;">
                        <div>
                            @if($serviceRequest->project?->nature_of_work)
                                <div style="font-size:10.5px; font-weight:bold;">{{ strtoupper($serviceRequest->project->nature_of_work) }}</div>
                                @if($serviceRequest->project->recommendation && trim(strtolower($serviceRequest->project->recommendation)) !== trim(strtolower($serviceRequest->project->nature_of_work)))
                                    <div style="font-size:9.5px; margin-top:2px; color:#222; font-weight:normal;">{{ $serviceRequest->project->recommendation }}</div>
                                @endif
                            @endif
                        </div>
                        <div style="text-align:center; font-style:italic; font-size:8px; color:#555;">(Use separate sheet if needed)</div>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        <div style="font-size:11px; font-weight:500; line-height:1.5;">
                            @if($assignedWorkers->count() > 0)
                                @foreach($assignedWorkers as $w)
                                    <div>{{ ($w->user->first_name ?? '') . ' ' . ($w->user->last_name ?? '') }}</div>
                                @endforeach
                            @else
                                <span style="color:#777; font-style:italic;">--</span>
                            @endif
                        </div>
                    </td>
                    <td style="display:flex; flex-direction:column; justify-content:space-between; height:100px;">
                        <div></div>
                        <div style="text-align:center; font-style:italic; font-size:8px; color:#555;">(Use separate sheet if needed)</div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="display:flex; justify-content:space-between; margin:5px 0 6px; font-size:11px; font-weight:500;">
            <div>DATE STARTED : <span class="uline" style="min-width:180px;">{{ $dateStarted }}</span></div>
            <div>TARGET DATE OF COMPLETION: <span class="uline" style="min-width:180px;">{{ $targetCompletion }}</span></div>
        </div>
    </div>

    {{-- 5. Assessed By & Approved By --}}
    <div style="display:flex; justify-content:space-between; margin:6px 0 8px;">
        <div style="width:48%; padding-left:4px; font-size:11px;">
            <div style="font-weight:bold; margin-bottom:18px;">ASSESSED BY:</div>
            <div class="sign-line" style="width:220px;"></div>
            <div style="font-weight:bold; font-size:11px; margin-top:2px;">{{ $assessedByName }}</div>
            <div style="font-size:9.5px; color:#222;">Team Leader/GSO Staff</div>
            <div style="margin-top:3px; font-size:10.5px;">DATE: <span class="uline" style="min-width:130px;">{{ $siteVisitDate }}</span></div>
        </div>
        <div style="width:48%; padding-left:20px; font-size:11px;">
            <div style="font-weight:bold; margin-bottom:18px;">APPROVED BY:</div>
            <div class="sign-line" style="width:220px;"></div>
            <div style="font-weight:bold; font-size:11px; margin-top:2px;">REY A. PADILLA</div>
            <div style="font-size:9.5px; color:#222;">Head, General Services Office</div>
            <div style="margin-top:3px; font-size:10.5px;">DATE: <span class="uline" style="min-width:130px;">{{ $siteVisitDate }}</span></div>
        </div>
    </div>

    {{-- 6. Client's Agreement --}}
    <div>
        <div class="sec-divider">CLIENT'S AGREEMENT FOR PROJECT IMPLEMENTATION</div>
        
        <div style="display:flex; justify-content:center; gap:100px; margin:6px 0 8px;">
            <div class="cb-inline" style="font-weight:bold;"><span class="box-sq"></span> CONFIRM</div>
            <div class="cb-inline" style="font-weight:bold;"><span class="box-sq"></span> DISAGREE</div>
        </div>
        
        <div style="display:flex; justify-content:space-around; margin:4px 0 10px; font-size:9.5px; color:#333;">
            <div style="text-align:center; width:220px;">
                <div class="sign-line" style="margin-bottom:2px;"></div>
                Scheduled Date
            </div>
            <div style="text-align:center; width:220px;">
                <div class="sign-line" style="margin-bottom:2px;"></div>
                State Reasons:
            </div>
        </div>
        
        <div style="text-align:center; margin-bottom:4px;">
            <span class="uline" style="min-width:280px; height:1px;"></span><br>
            <span style="font-size:9.5px; font-style:italic;">Client's Signature</span>
        </div>
    </div>

    {{-- 7. Acceptance --}}
    <div>
        <div style="text-align:center; font-weight:bold; font-size:11.5px; margin:2px 0 6px;">ACCEPTANCE</div>
        
        <div style="display:flex; justify-content:space-between; padding:0 24px; margin-bottom:12px; font-size:11px;">
            <div>Date of Completion: <span class="uline" style="min-width:160px;">{{ $dateStarted }}</span></div>
            <div>Date of Acceptance: <span class="uline" style="min-width:160px;">{{ $targetCompletion }}</span></div>
        </div>
        
        <div style="text-align:center; margin-bottom:6px;">
            <span class="uline" style="min-width:280px; height:1px;"></span><br>
            <span style="font-size:9.5px; font-style:italic;">Client's Signature</span>
        </div>
    </div>

    {{-- 8. Footer --}}
    <div class="footer">
        <div>BU-F-GSO-2<br>Effective Date: October 9, 2025</div>
        <div>Rev. No. 2</div>
    </div>
</div>
@endif

</body>
</html>
