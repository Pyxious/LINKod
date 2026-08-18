<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Clientele Satisfaction Survey - BU-F-GSO-CSM</title>
    <style>
        @page {
            size: 8.5in 11in portrait;
            margin: 0.35in 0.45in;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 13.5px;
            color: #000;
            background-color: #fff;
            line-height: 1.35;
        }
        .container {
            position: relative;
            min-height: 10.2in;
            max-width: 760px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .content-wrap {
            flex: 1 0 auto;
        }

        /* Header Layout */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
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
            font-family: Arial, sans-serif;
        }
        .header-center {
            width: 56%;
            text-align: center;
        }
        .header-center .univ-name {
            font-size: 14px;
        }
        .header-center .office-name {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .header-center .city-name {
            font-size: 13px;
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

        /* Title */
        .survey-title {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 10px 0 14px;
        }

        /* Name of rater & Date */
        .rater-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            font-size: 13.5px;
        }
        .underlined-value {
            border-bottom: 1.5px solid #000;
            display: inline-block;
            min-width: 240px;
            padding-left: 6px;
            font-weight: bold;
        }
        .date-value {
            border-bottom: 1.5px solid #000;
            display: inline-block;
            min-width: 120px;
            padding-left: 6px;
            font-weight: bold;
            text-align: center;
        }

        /* Instructions & Rating Scales */
        .instructions-box {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            padding: 10px 0;
            margin: 12px 0 14px;
        }
        .instruction-p {
            text-align: justify;
            font-size: 13px;
            line-height: 1.4;
            margin-bottom: 8px;
        }
        .scale-grid {
            display: flex;
            justify-content: center;
            gap: 60px;
            font-size: 13px;
            margin-top: 6px;
        }

        /* Job Completion Info */
        .completion-info {
            margin-bottom: 16px;
            font-size: 13.5px;
        }
        .completion-line {
            margin-bottom: 6px;
        }
        .completion-val {
            border-bottom: 1.5px solid #000;
            display: inline-block;
            min-width: 240px;
            padding-left: 6px;
            font-weight: 500;
        }

        /* Survey Table */
        .survey-table {
            width: 90%;
            margin: 0 auto 20px;
            border-collapse: collapse;
            border: 1.5px solid #000;
        }
        .survey-table th, .survey-table td {
            border: 1px solid #000;
            padding: 6px 10px;
            text-align: center;
            font-size: 13px;
        }
        .survey-table th {
            background-color: #fafafa;
            font-weight: bold;
        }
        .survey-table td.function-col {
            text-align: left;
            font-weight: 500;
            width: 55%;
        }
        .survey-table td.check-col {
            width: 9%;
            font-size: 16px;
            font-weight: bold;
        }

        /* Suggestions */
        .suggestions-title {
            font-weight: bold;
            font-size: 13.5px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .ruled-line {
            border-bottom: 1.5px solid #000;
            min-height: 24px;
            font-size: 13px;
            font-style: italic;
            padding: 2px 6px;
            margin-bottom: 4px;
        }

        /* Footer pinned to bottom of page */
        .footer-table {
            margin-top: auto;
            width: 100%;
            border-top: 1px solid #000;
            padding-top: 6px;
            font-family: Arial, sans-serif;
            font-size: 9.5px;
        }
        .footer-table td {
            vertical-align: bottom;
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
                </td>
                <td class="header-right">
                    <img src="{{ asset('images/right-top logo.png') }}" alt="Bagong Pilipinas Logo">
                </td>
            </tr>
        </table>

        <!-- Title -->
        <div class="survey-title">
            CLIENTELE SATISFACTION SURVEY
        </div>

        <!-- Name of Rater & Date -->
        <div class="rater-row">
            <div>
                <strong>NAME OF RATER:</strong>
                <span class="underlined-value">
                    {{ $serviceRequest->client->user->first_name ?? '' }} {{ $serviceRequest->client->user->last_name ?? '' }}
                </span>
                <div style="font-size: 11px; font-style: italic; padding-left: 135px; color: #333;">(Optional)</div>
            </div>
            <div>
                <strong>DATE:</strong>
                <span class="date-value">
                    {{ $serviceRequest->evaluation->rated_at ? $serviceRequest->evaluation->rated_at->format('m/d/Y') : now()->format('m/d/Y') }}
                </span>
            </div>
        </div>

        <!-- Instructions & Rating Scales -->
        <div class="instructions-box">
            <p class="instruction-p">
                This Feedback Form is an important tool for us to measure our performance to help us improve our services to our clients. Kindly fill out this form to assess the performance of our staff and to improve further the quality of our services. You may use this form to express your Praise, Recommendations, or Criticisms. Please let us know how we performed our services to you by Checking the appropriate box opposite the respective rating scales.
            </p>

            <div class="scale-grid">
                <div>
                    <div><strong>5</strong> - Outstanding</div>
                    <div><strong>4</strong> - Very Satisfactory</div>
                    <div><strong>3</strong> - Satisfactory</div>
                </div>
                <div>
                    <div><strong>2</strong> - Fair</div>
                    <div><strong>1</strong> - Unsatisfactory</div>
                </div>
            </div>
        </div>

        <!-- Job Completion Information -->
        <div class="completion-info">
            <div class="completion-line">
                <strong>Services Completed :</strong>
                <span class="completion-val">{{ $serviceRequest->title }}</span>
            </div>
            <div class="completion-line">
                <strong>GSO Service Team Leader:</strong>
                <span class="completion-val">
                    {{ $serviceRequest->project->approvedBy->user->first_name ?? 'BU-GSO' }} {{ $serviceRequest->project->approvedBy->user->last_name ?? 'Team Leader' }}
                </span>
            </div>
            <div class="completion-line">
                <strong>Date Completed:</strong>
                <span class="completion-val">
                    {{ $serviceRequest->latestHistory ? \Carbon\Carbon::parse($serviceRequest->latestHistory->updated_at)->format('F d, Y') : now()->format('F d, Y') }}
                </span>
            </div>
        </div>

        <!-- Functions & Rating Scales Table -->
        @php
            $rating = (int) ($serviceRequest->evaluation->rating ?? 5);
            $functions = [
                'Quality of Service',
                'Attitude',
                'Safety Precaution Awareness',
                'Time Bound',
                'Workplace Housekeeping',
            ];
        @endphp

        <table class="survey-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 55%; vertical-align: middle;">FUNCTIONS</th>
                    <th colspan="5">RATING SCALES</th>
                </tr>
                <tr>
                    <th style="width: 9%;">5</th>
                    <th style="width: 9%;">4</th>
                    <th style="width: 9%;">3</th>
                    <th style="width: 9%;">2</th>
                    <th style="width: 9%;">1</th>
                </tr>
            </thead>
            <tbody>
                @foreach($functions as $func)
                    <tr>
                        <td class="function-col">{{ $func }}</td>
                        <td class="check-col">{{ $rating === 5 ? '✓' : '' }}</td>
                        <td class="check-col">{{ $rating === 4 ? '✓' : '' }}</td>
                        <td class="check-col">{{ $rating === 3 ? '✓' : '' }}</td>
                        <td class="check-col">{{ $rating === 2 ? '✓' : '' }}</td>
                        <td class="check-col">{{ $rating === 1 ? '✓' : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Suggestions & Recommendations -->
        <div>
            <div class="suggestions-title">
                SUGGESTIONS/RECOMMENDATIONS:
            </div>

            @php
                $feedback = trim($serviceRequest->evaluation->feedback_text ?? '');
                $lines = array_filter(explode("\n", wordwrap($feedback, 85, "\n")));
                if (empty($lines)) {
                    $lines = [''];
                }
                while(count($lines) < 3) {
                    $lines[] = '';
                }
            @endphp

            @foreach(array_slice($lines, 0, 4) as $line)
                <div class="ruled-line">
                    {{ $line }}
                </div>
            @endforeach
        </div>
    </div>

    <!-- Footer Form Metadata (pinned to bottom of page) -->
    <table class="footer-table">
        <tr>
            <td style="text-align: left; width: 33%;">
                BU-F-GSO-CSM<br>
                Effective Date: October 9, 2025
            </td>
            <td style="text-align: center; width: 34%; font-weight: bold;">
                Clientele Satisfaction Survey
            </td>
            <td style="text-align: right; width: 33%;">
                Rev. No. 2
            </td>
        </tr>
    </table>

</div>

</body>
</html>
