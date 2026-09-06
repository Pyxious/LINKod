<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary of Accomplishment Report & Clientele Satisfaction Survey — BU-GSO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        @page {
            size: A4 portrait;
            margin: 15mm 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #fff;
            line-height: 1.3;
            font-size: 11pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .page-container {
            width: 100%;
            max-width: 190mm;
            margin: 0 auto;
            padding: 10px 0;
        }
        .page-break {
            page-break-before: always;
            break-before: page;
        }
        .no-break {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-normal { font-weight: normal; }
        .uppercase { text-transform: uppercase; }

        /* Report 1 Tables */
        table.section-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10pt;
        }
        table.section-table th, table.section-table td {
            border: 1px solid #000;
            padding: 5px 10px;
        }

        /* Survey Matrix Table */
        table.survey-matrix {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 20px;
            text-align: center;
        }
        table.survey-matrix th, table.survey-matrix td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        table.survey-matrix th {
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Calculations breakdown below matrix */
        .overall-box {
            display: inline-flex;
            border: 1.5px solid #000;
            margin-top: 15px;
            font-size: 10pt;
            font-weight: bold;
        }
        .overall-box .title {
            padding: 6px 20px;
            border-right: 1.5px solid #000;
        }
        .overall-box .score {
            padding: 6px 24px;
            min-width: 70px;
            text-align: center;
        }

        .signatory-block {
            margin-top: 35px;
            font-size: 10.5pt;
        }
        .signatory-name {
            font-weight: bold;
            font-size: 11pt;
            text-decoration: underline;
            margin-top: 30px;
        }
        .signatory-title {
            font-size: 9.5pt;
        }

        .print-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #fff;
            padding: 10px 16px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: flex;
            gap: 10px;
            z-index: 9999;
        }
        .btn-print {
            background: #0033a0;
            color: #fff;
            border: none;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
        }
        .btn-close {
            background: #e5e7eb;
            color: #1f2937;
            border: none;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
        }
        @media print {
            .print-actions { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>

    <div class="print-actions">
        <button onclick="window.print()" class="btn-print">Print / Save as PDF (A4)</button>
        <button onclick="window.close()" class="btn-close">Close</button>
    </div>

    <!-- ================================================================= -->
    <!-- PHOTO 1: SUMMARY OF ACCOMPLISHMENT REPORT & CLIENTELE SATISFACTION -->
    <!-- ================================================================= -->
    <div class="page-container">
        
        <div style="margin-bottom: 24px;">
            <h1 class="text-center uppercase font-bold" style="font-size: 13pt; letter-spacing: 0.5px; line-height: 1.35;">
                Summary of Accomplishment Report and Clientele<br>Satisfaction Survey
            </h1>
            <p class="text-center uppercase font-bold" style="font-size: 11pt; margin-top: 4px;">
                {{ $periodText }}
            </p>
        </div>

        @foreach($sections as $secKey => $sec)
            <table class="section-table no-break">
                <tr>
                    <td colspan="2" class="font-bold uppercase" style="background-color: #fbfbfb;">
                        Maintenance Section: <span style="letter-spacing: 0.3px;">{{ $sec['name'] }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="font-normal" style="width: 78%;">
                        Total Number of Request Received:
                    </td>
                    <td class="text-center font-bold" style="width: 22%; font-size: 10.5pt;">
                        {{ $sec['total_requests'] }}
                    </td>
                </tr>
                <tr>
                    <td class="font-normal" style="width: 78%;">
                        Clientele Satisfaction Survey Result:
                    </td>
                    <td class="text-center font-bold" style="width: 22%; font-size: 10.5pt;">
                        {{ $sec['cs_result'] > 0 ? number_format($sec['cs_result'], 2) : '0' }}
                    </td>
                </tr>
            </table>
        @endforeach

        <!-- Signatories (Exact BU-GSO Head and CAO) -->
        <div class="signatory-block no-break">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 25px;">
                <!-- Prepared By -->
                <div style="width: 48%;">
                    <p class="font-normal">Prepared by:</p>
                    <div style="height: 28px;"></div>
                    <p class="signatory-name">REY A. PADILLA</p>
                    <p class="signatory-title">Administrative Officer II</p>
                    <p class="signatory-title">Head, General Services Office</p>
                </div>

                <!-- Certified Correct -->
                <div style="width: 48%;">
                    <p class="font-normal">Certified Correct:</p>
                    <div style="height: 28px;"></div>
                    <p class="signatory-name">MA. MYRA A. CAPARAS</p>
                    <p class="signatory-title">Acting Chief Administrative Officer</p>
                    <p class="signatory-title">For Administrative Services Division</p>
                </div>
            </div>
        </div>

    </div>

    <!-- ================================================================= -->
    <!-- PHOTO 2: CLIENTELE SATISFACTION SURVEY (STARTS ON NEW PAGE)       -->
    <!-- ================================================================= -->
    @foreach($sectionsWithSurvey as $secIndex => $sec)
        <div class="page-break"></div>

        <div class="page-container">
            <div style="margin-bottom: 20px;">
                <h1 class="text-center uppercase font-bold" style="font-size: 13pt; letter-spacing: 0.5px;">
                    Clientele Satisfaction Survey
                </h1>
                <p class="text-center uppercase font-bold" style="font-size: 10.5pt; margin-top: 3px;">
                    Maintenance Section: {{ $sec['name'] }}
                </p>
                <p class="text-center uppercase font-bold" style="font-size: 10.5pt; margin-top: 2px;">
                    {{ $periodText }}
                </p>
            </div>

            <!-- 5 Functions Matrix Table -->
            <table class="survey-matrix">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 16%; vertical-align: middle;">
                            Number of<br>Rater
                        </th>
                        <th colspan="5" style="vertical-align: middle;">
                            Functions
                        </th>
                    </tr>
                    <tr>
                        <th style="width: 16.8%;">Quality of<br>Service</th>
                        <th style="width: 16.8%;">Attitude</th>
                        <th style="width: 16.8%;">Safety<br>Precautions<br>Awareness</th>
                        <th style="width: 16.8%;">Time<br>Bounded</th>
                        <th style="width: 16.8%;">Workplace<br>Housekeeping</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sec['raters'] as $r)
                        <tr>
                            <td class="font-bold">{{ $r['rater_no'] }}</td>
                            <td>{{ $r['quality'] }}</td>
                            <td>{{ $r['attitude'] }}</td>
                            <td>{{ $r['safety'] }}</td>
                            <td>{{ $r['time'] }}</td>
                            <td>{{ $r['housekeeping'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 16px;" class="font-normal italic text-center">
                                No survey responses recorded for this section in the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if(!empty($sec['raters']))
                <!-- Frequency & Points Breakdown (Matches Scanned Photo 2) -->
                <div class="no-break" style="margin-top: 10px;">
                    <table style="width: 100%; font-size: 9.5pt; border-collapse: collapse; text-align: center; margin-bottom: 12px;">
                        <!-- Counts of 5 and 4 -->
                        @foreach([5, 4] as $score)
                            @if(isset($sec['counts'][$score]))
                                <tr>
                                    <td style="width: 16%; font-weight: bold; text-align: center; padding: 2px 4px;">{{ $score }}</td>
                                    <td style="width: 16.8%; padding: 2px 4px;">{{ $sec['counts'][$score]['quality'] ?? 0 }}</td>
                                    <td style="width: 16.8%; padding: 2px 4px;">{{ $sec['counts'][$score]['attitude'] ?? 0 }}</td>
                                    <td style="width: 16.8%; padding: 2px 4px;">{{ $sec['counts'][$score]['safety'] ?? 0 }}</td>
                                    <td style="width: 16.8%; padding: 2px 4px;">{{ $sec['counts'][$score]['time'] ?? 0 }}</td>
                                    <td style="width: 16.8%; padding: 2px 4px;">{{ $sec['counts'][$score]['housekeeping'] ?? 0 }}</td>
                                </tr>
                            @endif
                        @endforeach

                        <!-- Spacer row -->
                        <tr><td colspan="6" style="height: 10px;"></td></tr>

                        <!-- Total points per score -->
                        @foreach([5, 4] as $score)
                            @if(isset($sec['points'][$score]))
                                <tr>
                                    <td style="width: 16%; font-weight: bold; text-align: center; padding: 2px 4px;">{{ $score }}</td>
                                    <td style="width: 16.8%; padding: 2px 4px;">{{ $sec['points'][$score]['quality'] ?? 0 }}</td>
                                    <td style="width: 16.8%; padding: 2px 4px;">{{ $sec['points'][$score]['attitude'] ?? 0 }}</td>
                                    <td style="width: 16.8%; padding: 2px 4px;">{{ $sec['points'][$score]['safety'] ?? 0 }}</td>
                                    <td style="width: 16.8%; padding: 2px 4px;">{{ $sec['points'][$score]['time'] ?? 0 }}</td>
                                    <td style="width: 16.8%; padding: 2px 4px;">{{ $sec['points'][$score]['housekeeping'] ?? 0 }}</td>
                                </tr>
                            @endif
                        @endforeach

                        <!-- Spacer row -->
                        <tr><td colspan="6" style="height: 10px;"></td></tr>

                        <!-- Weighted averages per column -->
                        <tr>
                            <td style="width: 16%; font-weight: bold; text-align: center; padding: 4px;"></td>
                            <td style="width: 16.8%; font-weight: bold; font-size: 10pt; padding: 4px;">{{ number_format($sec['means']['quality'] ?? 0, 2) }}</td>
                            <td style="width: 16.8%; font-weight: bold; font-size: 10pt; padding: 4px;">{{ number_format($sec['means']['attitude'] ?? 0, 2) }}</td>
                            <td style="width: 16.8%; font-weight: bold; font-size: 10pt; padding: 4px;">{{ number_format($sec['means']['safety'] ?? 0, 2) }}</td>
                            <td style="width: 16.8%; font-weight: bold; font-size: 10pt; padding: 4px;">{{ number_format($sec['means']['time'] ?? 0, 2) }}</td>
                            <td style="width: 16.8%; font-weight: bold; font-size: 10pt; padding: 4px;">{{ number_format($sec['means']['housekeeping'] ?? 0, 2) }}</td>
                        </tr>
                    </table>

                    <!-- Final Section Overall Score Card -->
                    <div style="display: flex; justify-content: flex-end; margin-top: 15px;">
                        <div class="overall-box">
                            <div class="title">{{ $sec['name'] }}</div>
                            <div class="score">{{ number_format($sec['overall_mean'] ?? 0, 2) }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endforeach

</body>
</html>
