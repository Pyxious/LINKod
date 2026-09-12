<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $year }} Accomplishment Report — BU-GSO</title>
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
            margin: 12mm 15mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            color: #000;
            background: #fff;
            line-height: 1.25;
            font-size: 9.5pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .page-container {
            width: 100%;
            max-width: 195mm;
            margin: 0 auto;
            padding: 5px 0;
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
        .font-black { font-weight: 900; }
        .uppercase { text-transform: uppercase; }

        .report-header {
            text-align: center;
            margin-bottom: 16px;
        }
        .report-header h1 {
            font-family: 'Times New Roman', serif;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .report-header .sub1 {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .report-header .sub2 {
            font-size: 10.5pt;
            font-weight: bold;
        }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 16px;
        }
        table.report-table th, table.report-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            vertical-align: middle;
        }
        table.report-table th {
            font-weight: bold;
            text-align: center;
            background-color: #f2f2f2;
        }


        .signatures-grid {
            margin-top: 25px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            font-size: 9.5pt;
        }
        .sig-block {
            margin-bottom: 18px;
        }
        .sig-name {
            font-weight: bold;
            font-size: 10.5pt;
            margin-top: 28px;
            text-transform: uppercase;
        }
        .sig-role {
            font-size: 9pt;
        }

        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 999; display: flex; gap: 8px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0033a0; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            Print / Save as PDF
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #666; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            Close
        </button>
    </div>

    <div class="page-container">
        
        <!-- Header -->
        <div class="report-header">
            <h1>{{ $year }} ACCOMPLISHMENT REPORT</h1>
            <div class="sub1">MAINTENANCE SECTION: {{ strtoupper($categoryName) }}</div>
            <div class="sub2">{{ strtoupper($monthRange) }}</div>
        </div>

        <!-- Table -->
        <table class="report-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 14%;">REQUISITION<br>NUMBER</th>
                    <th rowspan="2" style="width: 15%;">OFFICE/<br>UNIT</th>
                    <th rowspan="2" style="{{ $includeWorker ? 'width: 25%;' : 'width: 32%;' }}">TASK DETAILS</th>
                    <th colspan="3" style="width: 22%;">DATES</th>
                    <th rowspan="2" style="width: 12%;">CLIENTELE<br>SATISFACTION</th>
                    @if($includeWorker)
                        <th rowspan="2" style="width: 12%;">WORKER<br>ASSIGNED</th>
                    @endif
                </tr>
                <tr>
                    <th style="width: 7%;">REQUEST</th>
                    <th style="width: 7%;">STARTED</th>
                    <th style="width: 8%;">COMPLETION</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 1; @endphp
                @forelse($serviceRequests as $req)
                    @php
                        $catName = strtolower($req->category->category_name ?? '');
                        $prefix = match(true) {
                            str_contains($catName, 'carpentry') || str_contains($catName, 'masonry') || str_contains($catName, 'electrical') || str_contains($catName, 'mechanical') => 'CMS',
                            str_contains($catName, 'plumbing') => 'PLS',
                            str_contains($catName, 'painting') || str_contains($catName, 'paint') => 'PAS',
                            str_contains($catName, 'janitorial') => 'JS',
                            str_contains($catName, 'landscaping') => 'LS',
                            str_contains($catName, 'manpower') || str_contains($catName, 'event') => 'MAN',
                            default => 'REQ'
                        };
                        $reqNum = $prefix . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
                        $counter++;

                        $office = $req->location ?? 'N/A';
                        $reqDate = $req->submitted_at ? \Carbon\Carbon::parse($req->submitted_at)->format('n/j/Y') : '';
                        
                        $startedDate = '';
                        $completionDate = '';
                        if ($req->project) {
                            $startHistory = $req->project->histories->where('current_status', 'In Progress')->first();
                            if ($startHistory) $startedDate = \Carbon\Carbon::parse($startHistory->updated_at)->format('n/j/Y');
                            $compHistory = $req->project->histories->where('current_status', 'Completed')->first();
                            if ($compHistory) $completionDate = \Carbon\Carbon::parse($compHistory->updated_at)->format('n/j/Y');
                        }
                        if (!$startedDate && $req->histories) {
                            $reqStart = $req->histories->where('current_status', 'In Progress')->first();
                            if ($reqStart) $startedDate = \Carbon\Carbon::parse($reqStart->updated_at)->format('n/j/Y');
                        }
                        if (!$completionDate && $req->histories) {
                            $reqComp = $req->histories->where('current_status', 'Completed')->first();
                            if ($reqComp) $completionDate = \Carbon\Carbon::parse($reqComp->updated_at)->format('n/j/Y');
                        }
                        if (!$startedDate) $startedDate = $reqDate;
                        if (!$completionDate) $completionDate = $reqDate;

                        $ratingVal = '—';
                        if ($req->evaluation) {
                            $r = (float) $req->evaluation->rating;
                            $ratingVal = ($r == (int)$r) ? (string)(int)$r : number_format($r, 1);
                        }

                        $isManpower = $prefix === 'MAN' || str_contains($catName, 'manpower') || str_contains($catName, 'event');
                        $verifiedWork = $req->project?->nature_of_work;
                        $hasVerifiedWork = $verifiedWork && !in_array(trim($verifiedWork), ['Completed', 'Repair & Maintenance Done', 'Direct Repair', '']);

                        if ($isManpower) {
                            $taskTitle = $hasVerifiedWork ? $verifiedWork : $req->title;
                            $taskDesc = null;
                        } else {
                            $taskTitle = $req->title;
                            $taskDesc = ($hasVerifiedWork && $verifiedWork !== $req->title) ? $verifiedWork : ($req->display_description ?? null);
                        }

                        $projectWorkers = $req->project?->workers ?? collect();
                        $workerNames = $projectWorkers->map(function($w) {
                            return trim(($w->staff?->user?->first_name ?? '') . ' ' . ($w->staff?->user?->last_name ?? ''));
                        })->filter()->values();
                        $workerNamesStr = $workerNames->join(', ') ?: 'Unassigned';
                    @endphp
                    <tr>
                        <td class="text-center font-bold">{{ $reqNum }}</td>
                        <td class="text-center">{{ $office }}</td>
                        <td class="text-left">
                            <strong>{{ $taskTitle }}</strong>
                            @if($taskDesc)
                                <div style="font-size: 8pt; color: #333; margin-top: 2px;">{{ $taskDesc }}</div>
                            @endif
                        </td>
                        <td class="text-center">{{ $reqDate }}</td>
                        <td class="text-center">{{ $startedDate }}</td>
                        <td class="text-center">{{ $completionDate }}</td>
                        <td class="text-center font-bold">{{ $ratingVal }}</td>
                        @if($includeWorker)
                            <td class="text-center" style="font-size: 8pt;">{{ $workerNamesStr }}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $includeWorker ? 8 : 7 }}" class="text-center" style="padding: 20px; font-style: italic; color: #666;">
                            No completed service requests found matching the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>


        <!-- Signatures Section -->
        <div class="signatures-grid no-break">
            <div class="sig-block">
                <div>Prepared By:</div>
                <div class="sig-name">{{ $teamLeaderName }}</div>
                <div class="sig-role">Team Leader</div>
                <div class="sig-role">{{ $teamSectionName }}</div>
            </div>

            <div class="sig-block">
                <div>Certified True and Correct:</div>
                <div class="sig-name">REY A. PADILLA</div>
                <div class="sig-role">Administrative Officer I</div>
                <div class="sig-role">Head, General Services Office</div>
            </div>

            <div class="sig-block">
                <div>Noted By:</div>
                <div class="sig-name">MA. MYRA A. CAPARAS</div>
                <div class="sig-role">Acting Chief Administrative Officer for</div>
                <div class="sig-role">Administrative Services Division</div>
            </div>
        </div>

    </div>

</body>
</html>
