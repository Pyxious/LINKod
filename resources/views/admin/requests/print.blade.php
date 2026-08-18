<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Job Request Form - BU-F-GSO-2</title>
    <style>
        @page {
            size: 8.5in 13in portrait;
            margin: 0.3in 0.4in;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: "Arial", "Helvetica Neue", sans-serif;
            font-size: 11.5px;
            color: #000;
            background-color: #fff;
            line-height: 1.25;
        }
        .container {
            position: relative;
            min-height: 12.2in;
            max-width: 780px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
        }
        .content-wrap {
            flex: 1 0 auto;
        }

        /* Header Layout */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .header-left {
            width: 22%;
            text-align: center;
        }
        .header-left img {
            width: 75px;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .iso-text {
            font-size: 7.5px;
            line-height: 1.1;
            margin-top: 3px;
            color: #222;
        }
        .header-center {
            width: 56%;
            text-align: center;
        }
        .header-center .univ-name {
            font-size: 13px;
            font-weight: 500;
        }
        .header-center .office-name {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .header-center .city-name {
            font-size: 12px;
        }
        .header-center .form-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .header-center .category-title {
            font-size: 13px;
            font-weight: bold;
            margin-top: 2px;
            color: #111;
        }
        .header-right {
            width: 22%;
            text-align: right;
        }
        .header-right img {
            width: 150px;
            height: auto;
            display: inline-block;
        }

        /* Requisition line */
        .req-no-row {
            text-align: right;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 6px;
        }
        .req-no-line {
            display: inline-block;
            width: 130px;
            border-bottom: 1.5px solid #000;
            text-align: center;
            font-weight: bold;
        }

        /* Section Dividers */
        .section-divider {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            padding: 3px 0;
            margin: 6px 0;
            letter-spacing: 0.5px;
        }
        .single-line {
            border-bottom: 1.5px solid #000;
            margin: 4px 0;
        }

        /* Client info box */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            margin-bottom: 8px;
        }
        .info-table td {
            padding: 4px 6px;
            font-size: 11.5px;
            vertical-align: middle;
        }
        .info-table tr:first-child td {
            border-bottom: 1.5px solid #000;
        }
        .info-table .label {
            font-weight: bold;
            width: 18%;
        }
        .info-table .val {
            width: 32%;
        }
        .info-table .border-left {
            border-left: 2px solid #000;
        }

        /* Description Box */
        .desc-section {
            text-align: center;
            padding: 6px 10px;
            min-height: 70px;
        }
        .desc-title {
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .desc-body {
            max-width: 620px;
            margin: 0 auto;
            line-height: 1.35;
        }
        .desc-headline {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 2px;
        }

        /* Signatures */
        .signature-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 8px;
            margin-bottom: 4px;
        }
        .signature-box {
            text-align: center;
            width: 260px;
        }
        .sign-line {
            border-bottom: 1.5px solid #000;
            height: 1px;
            margin-bottom: 2px;
        }
        .sign-caption {
            font-size: 9.5px;
            text-transform: uppercase;
        }

        /* Note text */
        .note-text {
            font-style: italic;
            font-size: 10px;
            margin: 4px 0;
        }

        /* Checkbox & Priority */
        .flex-row {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin: 6px 0;
        }
        .checkbox-item {
            display: inline-flex;
            align-items: center;
            font-size: 12px;
            font-weight: 500;
        }
        .checkbox-box {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 1.5px solid #000;
            text-align: center;
            line-height: 12px;
            font-weight: bold;
            font-size: 11px;
            margin-right: 6px;
        }

        /* Vertical Line Table for recommendations */
        .rec-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
            margin-bottom: 6px;
        }
        .rec-table th, .rec-table td {
            border-left: 2px solid #000;
            border-right: 2px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            font-size: 10.5px;
        }
        .rec-table td:first-child, .rec-table th:first-child { border-left: none; }
        .rec-table td:last-child, .rec-table th:last-child { border-right: none; }
        .rec-table .subnote {
            margin-top: 25px;
            text-align: center;
            font-style: italic;
            font-size: 8.5px;
            color: #555;
        }

        /* Two columns details */
        .two-col-row {
            display: flex;
            justify-content: space-between;
            margin: 6px 0;
            font-size: 11px;
        }
        .underline-span {
            display: inline-block;
            border-bottom: 1.5px solid #000;
            min-width: 150px;
            text-align: center;
        }

        /* Officials Section */
        .officials-row {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .official-col {
            width: 48%;
            font-size: 11.5px;
        }
        .official-name {
            font-weight: bold;
            font-size: 12px;
            margin-top: 15px;
        }
        .official-title {
            font-size: 10px;
            color: #222;
        }

        /* Acceptance Section */
        .center-heading {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin: 6px 0 4px;
        }

        /* Footer pinned to bottom */
        .footer-row {
            margin-top: auto;
            padding-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 9.5px;
            color: #222;
        }
    </style>
</head>
<body onload="window.print()">

<div class="container">

    <div class="content-wrap">
        <!-- Header Section -->
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <img src="{{ asset('images/left-toplogo.png') }}" alt="BU Logo">
                    <div class="iso-text">
                        ISO 9001:2015<br>SOCOTEC SCP000722Q
                    </div>
                </td>
                <td class="header-center">
                    <div class="univ-name">Bicol University</div>
                    <div class="office-name">GENERAL SERVICES OFFICE</div>
                    <div class="city-name">Legazpi City</div>
                    <div class="form-title">Service Job Request Form</div>
                    <div class="category-title">{{ strtoupper($serviceRequest->category->category_name ?? 'CARPENTRY / MASONRY / ELECTRICAL') }}</div>
                </td>
                <td class="header-right">
                    <img src="{{ asset('images/right-top logo.png') }}" alt="Bagong Pilipinas Logo">
                </td>
            </tr>
        </table>

        <!-- Requisition No -->
        <div class="req-no-row">
            Requisition No.: <span class="req-no-line">{{ $serviceRequest->request_id ? str_pad($serviceRequest->request_id, 4, '0', STR_PAD_LEFT) : '' }}</span>
        </div>

        <!-- Client Info Box -->
        <table class="info-table">
            <tr>
                <td class="label">REQUESTED BY:</td>
                <td class="val">{{ $serviceRequest->client->user->first_name ?? '' }} {{ $serviceRequest->client->user->last_name ?? '' }}</td>
                <td class="label border-left">DATE:</td>
                <td class="val">{{ $serviceRequest->submitted_at ? $serviceRequest->submitted_at->format('F d, Y') : '' }}</td>
            </tr>
            <tr>
                <td class="label">OFFICE:</td>
                <td class="val">{{ $serviceRequest->location ?? '' }}</td>
                <td class="label border-left">CONTACT NO.:</td>
                <td class="val">{{ $serviceRequest->client->user->contact_number ?? $serviceRequest->client->contact_number ?? '' }}</td>
            </tr>
        </table>

        <!-- Description of Work -->
        <div class="desc-section">
            <div class="desc-title">Description of Work Requested:</div>
            <div class="desc-body">
                <div class="desc-headline">{{ $serviceRequest->title }}</div>
                <div>{{ $serviceRequest->description }}</div>
            </div>
        </div>

        <!-- Signature of Requesting Party -->
        <div class="signature-row">
            <div class="signature-box">
                <div class="sign-line"></div>
                <div class="sign-caption">Signature of Requesting Party</div>
            </div>
        </div>

        <div class="single-line"></div>
        <div class="note-text">
            NOTE: Entries below are to be accomplished by the General Services Office Personnel
        </div>

        <!-- Section: Initial Inspection and Evaluation -->
        <div class="section-divider">INITIAL INSPECTION AND EVALUATION</div>
        <div style="margin: 6px 0 8px 4px; font-size: 11.5px;">
            Date of site visit: <span class="underline-span" style="min-width: 200px;"></span>
        </div>

        @php $priority = strtolower($serviceRequest->priority ?? ''); @endphp
        <div class="flex-row" style="justify-content: center; gap: 80px;">
            <div class="checkbox-item">
                <span class="checkbox-box">{{ $priority === 'high' ? 'X' : '' }}</span> High Priority
            </div>
            <div class="checkbox-item">
                <span class="checkbox-box">{{ in_array($priority, ['medium', 'low']) ? 'X' : '' }}</span> Routine
            </div>
        </div>

        <!-- Section: Recommendations and Work Details -->
        <div class="section-divider">RECOMMENDATIONS AND WORK DETAILS</div>
        <table class="rec-table" style="min-height: 80px;">
            <tr>
                <td style="width: 35%;">
                    <strong>NATURE OF WORK TO BE DONE:</strong><br>
                    <div class="subnote">(Use separate sheet if needed)</div>
                </td>
                <td style="width: 30%; text-align: center;">
                    <strong>ASSIGNED PERSONNEL/STAFF</strong><br>
                    <span style="font-size: 11px; margin-top: 4px; display: block; font-weight: 500;">
                    @if($serviceRequest->project && $serviceRequest->project->workers->count() > 0)
                        {{ $serviceRequest->project->workers->map(fn($w) => ($w->user->first_name ?? '') . ' ' . ($w->user->last_name ?? ''))->filter()->implode(', ') }}
                    @endif
                    </span>
                </td>
                <td style="width: 35%;">
                    <strong>SUPPLIES &amp; MATERIALS NEEDED:</strong><br>
                    <div class="subnote">(Use separate sheet if needed)</div>
                </td>
            </tr>
        </table>

        <div class="two-col-row">
            <div>DATE STARTED : <span class="underline-span" style="min-width: 170px;"></span></div>
            <div>TARGET DATE OF COMPLETION: <span class="underline-span" style="min-width: 170px;"></span></div>
        </div>

        <div class="officials-row">
            <div class="official-col" style="padding-left: 8px;">
                <div>ASSESSED BY:</div>
                <div class="official-name">MR. SONNY B. MARAYA</div>
                <div class="official-title">Team Leader/GSO Staff</div>
                <div style="margin-top: 4px; font-size: 10.5px;">DATE: <span class="underline-span" style="min-width: 140px;"></span></div>
            </div>
            <div class="official-col" style="padding-left: 20px;">
                <div>APPROVED BY:</div>
                <div class="official-name">REY A. PADILLA</div>
                <div class="official-title">Head, General Services Office</div>
                <div style="margin-top: 4px; font-size: 10.5px;">DATE: <span class="underline-span" style="min-width: 140px;"></span></div>
            </div>
        </div>

        <!-- Section: Client's Agreement -->
        <div class="section-divider">CLIENT'S AGREEMENT FOR PROJECT IMPLEMENTATION</div>
        <div class="flex-row" style="justify-content: center; gap: 80px; margin-top: 8px;">
            <div class="checkbox-item font-bold">
                <span class="checkbox-box"></span> CONFIRM
            </div>
            <div class="checkbox-item font-bold">
                <span class="checkbox-box"></span> DISAGREE
            </div>
        </div>

        <div class="flex-row" style="justify-content: space-around; margin: 6px 0 10px; font-size: 9.5px; color: #444;">
            <div style="text-align: center; width: 220px;">
                <div class="sign-line" style="margin-bottom: 2px;"></div>
                Scheduled Date
            </div>
            <div style="text-align: center; width: 220px;">
                <div class="sign-line" style="margin-bottom: 2px;"></div>
                State Reasons:
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 8px;">
            <span class="underline-span" style="min-width: 280px; height: 1px;"></span><br>
            <span style="font-size: 10px; font-style: italic;">Client's Signature</span>
        </div>

        <!-- Acceptance -->
        <div class="center-heading">ACCEPTANCE</div>
        <div class="two-col-row" style="padding: 0 20px; margin-bottom: 10px;">
            <div>Date of Completion: <span class="underline-span" style="min-width: 160px;"></span></div>
            <div>Date of Acceptance: <span class="underline-span" style="min-width: 160px;"></span></div>
        </div>

        <div style="text-align: center; margin-bottom: 10px;">
            <span class="underline-span" style="min-width: 280px; height: 1px;"></span><br>
            <span style="font-size: 10px; font-style: italic;">Client's Signature</span>
        </div>
    </div>

    <!-- Footer pinned to bottom of page -->
    <div class="footer-row">
        <div>
            BU-F-GSO-2<br>
            Effective Date: October 9, 2025
        </div>
        <div>
            Rev. No. 2
        </div>
    </div>

</div>

</body>
</html>
