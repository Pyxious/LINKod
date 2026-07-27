<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: 8.5in 13in portrait;
            margin: 0; 
        }
        body {
            font-family: "Inter", "Arial", sans-serif;
            font-size: 13px; /* Slightly reduced to guarantee 8.5x13 fit */
            color: black;
            background-color: white;
            line-height: 1.15;
            margin: 0.25in 0.4in; 
        }
        .section-divider {
            border-top: 2px solid black;
            border-bottom: 2px solid black;
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            padding: 3px 0;
            margin: 6px 0;
        }
        .single-line {
            border-bottom: 2px solid black;
        }
        .checkbox-box { 
            display: inline-block; width: 14px; height: 14px; border: 2px solid black; text-align: center; line-height: 12px; font-weight: bold; margin-right: 6px; vertical-align: middle; 
        }
        .vertical-line-table {
            width: 100%;
            border-collapse: collapse;
        }
        .vertical-line-table td {
            border-left: 2px solid black;
            border-right: 2px solid black;
            padding: 3px 4px;
            vertical-align: top;
        }
        .vertical-line-table td:first-child { border-left: none; }
        .vertical-line-table td:last-child { border-right: none; }
        .grid-border { border-top: 2px solid black; border-bottom: 2px solid black; }
        .grid-row { display: flex; width: 100%; }
        .grid-cell { padding: 2px 0; }
    </style>
</head>
<body onload="window.print()">

<div class="max-w-[750px] mx-auto">

    <!-- Header Section -->
    <div class="flex justify-between items-start mb-3">
        <div class="w-[200px] text-center">
            <img src="{{ asset('images/left-toplogo.png') }}" class="w-[85px] h-auto mx-auto" alt="BU Logo">
            <div style="font-size: 8px; line-height: 1.1; margin-top: 4px; color: #333;">
                ISO 9001:2015<br>SOCOTEC SCP000722Q
            </div>
        </div>
        <div class="flex-1 text-center pt-2">
            <div class="text-[14px]">Bicol University</div>
            <div class="font-bold text-[15px]">GENERAL SERVICES OFFICE</div>
            <div class="text-[14px]">Legazpi City</div>
            <div class="font-bold text-[16px] mt-2">Service Job Request Form</div>
            <div class="font-bold text-[15px] mt-1">{{ strtoupper($serviceRequest->category->category_name ?? 'CARPENTRY / MASONRY / ELECTRICAL') }}</div>
        </div>
        <div class="w-[200px] text-right">
            <img src="{{ asset('images/right-top logo.png') }}" class="w-[200px] h-auto ml-auto" alt="BP Logo">
        </div>
    </div>

    <div class="text-right mb-1 font-bold pr-10 text-[13px]">
        Requisition No.: <span class="inline-block w-[120px] border-b-[1.5px] border-black"></span>
    </div>

    <!-- Client Info Box -->
    <div class="grid-border flex flex-col mb-2">
        <div class="grid-row">
            <div class="w-[15%] grid-cell font-bold">REQUESTED BY:</div>
            <div class="w-[35%] grid-cell">{{ $serviceRequest->client->user->first_name ?? '' }} {{ $serviceRequest->client->user->last_name ?? '' }}</div>
            <div class="w-[10%] grid-cell font-bold border-l-[2px] border-black pl-2">DATE:</div>
            <div class="w-[40%] grid-cell pl-2">{{ $serviceRequest->submitted_at ? $serviceRequest->submitted_at->format('F d, Y') : '' }}</div>
        </div>
        <div class="grid-row border-t-[2px] border-black">
            <div class="w-[15%] grid-cell font-bold">OFFICE:</div>
            <div class="w-[35%] grid-cell">{{ $serviceRequest->location ?? '' }}</div>
            <div class="w-[10%] grid-cell font-bold border-l-[2px] border-black pl-2">CONTACT NO.:</div>
            <div class="w-[40%] grid-cell pl-2">{{ $serviceRequest->client->user->contact_number ?? $serviceRequest->client->contact_number ?? '' }}</div>
        </div>
    </div>

    <!-- Description (Centered) -->
    <div class="mt-3 min-h-[85px] text-center flex flex-col items-center justify-center">
        <div class="mb-2 font-bold uppercase text-[13px]">Description of Work Requested:</div>
        <div class="max-w-[650px] mx-auto text-center leading-relaxed">
            <div class="font-extrabold text-[14px] mb-1">{{ $serviceRequest->title }}</div>
            <div class="text-[13px] text-gray-800">{{ $serviceRequest->description }}</div>
        </div>
    </div>

    <div class="text-right mt-3 mb-1 pr-6">
        <div class="inline-block border-b-[1.5px] border-black w-[250px]"></div><br>
        <span style="font-size: 10px;" class="pr-12">SIGNATURE OF REQUESTING PARTY</span>
    </div>

    <div class="single-line mt-1"></div>
    <div class="italic text-[11px] mb-1 mt-1">
        NOTE: Entries below are to be accomplished by the General Services Office Personnel
    </div>

    <!-- Evaluation Section -->
    <div class="section-divider">INITIAL INSPECTION AND EVALUATION</div>
    <div class="mt-2 mb-3">
        Date of site visit: <span class="inline-block w-[200px] border-b-[1.5px] border-black"></span>
    </div>
    
    @php $priority = strtolower($serviceRequest->priority ?? ''); @endphp
    <div class="flex justify-around mb-4 pl-10 pr-20">
        <div class="flex items-center">
            <div class="checkbox-box">{{ $priority === 'high' ? 'X' : '' }}</div> High Priority
        </div>
        <div class="flex items-center">
            <div class="checkbox-box">{{ in_array($priority, ['medium', 'low']) ? 'X' : '' }}</div> Routine
        </div>
    </div>

    <!-- Recommendations Section -->
    <div class="section-divider">RECOMMENDATIONS AND WORK DETAILS</div>
    <table class="vertical-line-table h-[90px] border-b-[2px] border-black text-center text-[11px]">
        <tr>
            <td style="width: 35%; text-align: left;">
                NATURE OF WORK TO BE DONE:<br>
                <div class="mt-12 text-center italic text-[9px] text-gray-500">(Use separate sheet if needed)</div>
            </td>
            <td style="width: 30%;">
                ASSIGNED PERSONNEL/STAFF<br>
                <span class="text-[12px] mt-1 block">
                @if($serviceRequest->project && $serviceRequest->project->workers->count() > 0)
                    {{ $serviceRequest->project->workers->map(fn($w) => $w->user->first_name . ' ' . $w->user->last_name)->implode(', ') }}
                @endif
                </span>
            </td>
            <td style="width: 35%; text-align: left;">
                SUPPLIES & MATERIALS NEEDED<br>
                <div class="mt-12 text-center italic text-[9px] text-gray-500">(Use separate sheet if needed)</div>
            </td>
        </tr>
    </table>

    <div class="flex justify-between mt-2 mb-4 text-[12px]">
        <div class="w-[50%]">DATE STARTED : <span class="inline-block border-b-[1.5px] border-black w-[180px]"></span></div>
        <div class="w-[50%] pl-4">TARGET DATE OF COMPLETION: <span class="inline-block border-b-[1.5px] border-black w-[180px]"></span></div>
    </div>

    <div class="flex justify-between text-center text-[13px] mb-6">
        <div class="w-[50%] text-left pl-4">
            <div class="mb-6">ASSESSED BY:</div>
            <div class="font-bold">MR. SONNY B. MARAYA</div>
            <div class="text-[11px]">Team Leader/GSO Staff</div>
            <div class="mt-3 text-[11px]">DATE: <span class="inline-block border-b-[1.5px] border-black w-[150px]"></span></div>
        </div>
        <div class="w-[50%] text-left pl-10">
            <div class="mb-6">APPROVED BY:</div>
            <div class="font-bold">REY A. PADILLA</div>
            <div class="text-[11px]">Head, General Services Office</div>
            <div class="mt-3 text-[11px]">DATE: <span class="inline-block border-b-[1.5px] border-black w-[150px]"></span></div>
        </div>
    </div>

    <!-- Client Agreement Section -->
    <div class="section-divider">CLIENT'S AGREEMENT FOR PROJECT IMPLEMENTATION</div>
    <div class="flex justify-around mt-3 mb-4 font-bold pl-10 pr-20">
        <div class="flex items-center"><div class="checkbox-box"></div> CONFIRM</div>
        <div class="flex items-center"><div class="checkbox-box"></div> DISAGREE</div>
    </div>
    
    <div class="flex justify-around mb-4 text-center text-[10px] text-gray-600">
        <div class="w-[250px]">
            <div class="border-b-[1.5px] border-black mb-1 mx-4"></div>
            Scheduled Date
        </div>
        <div class="w-[250px]">
            <div class="border-b-[1.5px] border-black mb-1 mx-4"></div>
            State Reasons:
        </div>
    </div>

    <div class="text-center mb-3">
        <div class="inline-block border-b-[1.5px] border-black w-[300px]"></div><br>
        <span class="text-[11px] italic">Client's Signature</span>
    </div>

    <div class="text-center font-bold text-[13px] mb-3">ACCEPTANCE</div>
    <div class="flex justify-between text-[11px] mb-6 px-8">
        <div>Date of Completion: <span class="inline-block border-b-[1.5px] border-black w-[180px]"></span></div>
        <div>Date of Acceptance: <span class="inline-block border-b-[1.5px] border-black w-[180px]"></span></div>
    </div>
    
    <div class="text-center mb-6">
        <div class="inline-block border-b-[1.5px] border-black w-[300px]"></div><br>
        <span class="text-[11px] italic">Client's Signature</span>
    </div>

    <!-- Footer -->
    <div class="mt-4 w-full flex justify-between items-end text-[10px]">
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
