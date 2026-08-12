<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: 8.5in 11in portrait;
            margin: 0; 
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 14px;
            color: black;
            background-color: white;
            line-height: 1.3;
            margin: 0.3in 0.5in; 
        }
        .survey-table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            border: 1.5px solid black;
        }
        .survey-table th, .survey-table td {
            border: 1px solid black;
            padding: 5px 8px;
            text-align: center;
        }
        .survey-table td.function-col {
            text-align: left;
            font-weight: 500;
        }
        .line-border-top-bottom {
            border-top: 1.5px solid black;
            border-bottom: 1.5px solid black;
        }
        .underlined-value {
            border-bottom: 1.5px solid black;
            display: inline-block;
            min-width: 250px;
            padding-left: 5px;
        }
    </style>
</head>
<body onload="window.print()">

<div class="max-w-[750px] mx-auto">

    <!-- Header Section (Kept as user confirmed header logos and layout are correct) -->
    <div class="flex justify-between items-start mb-3">
        <div class="w-[200px] text-center">
            <img src="{{ asset('images/left-toplogo.png') }}" class="w-[85px] h-auto mx-auto" alt="BU Logo">
            <div style="font-size: 8px; line-height: 1.1; margin-top: 4px; color: #333; font-family: Arial, sans-serif;">
                ISO 9001:2015<br>SOCOTEC SCP000722Q
            </div>
        </div>
        <div class="flex-1 text-center pt-2">
            <div class="text-[14px]">Bicol University</div>
            <div class="font-bold text-[15px]">GENERAL SERVICES OFFICE</div>
            <div class="text-[14px]">Legazpi City</div>
        </div>
        <div class="w-[200px] text-right">
            <img src="{{ asset('images/right-top logo.png') }}" class="w-[200px] h-auto ml-auto" alt="BP Logo">
        </div>
    </div>

    <!-- Title: CLIENTELE SATISFACTION SURVEY -->
    <div class="text-center font-bold text-[16px] mb-4 uppercase tracking-wide">
        CLIENTELE SATISFACTION SURVEY
    </div>

    <!-- Name of Rater & Date -->
    <div class="flex justify-between items-start mb-4 text-[14px]">
        <div>
            <span class="font-bold">NAME OF RATER:</span>
            <span class="underlined-value font-bold">
                {{ $serviceRequest->client->user->first_name ?? '' }} {{ $serviceRequest->client->user->last_name ?? '' }}
            </span>
            <br>
            <span class="text-[12px] italic pl-32">(Optional)</span>
        </div>
        <div class="pt-1">
            <span class="font-bold">DATE:</span>
            <span class="inline-block border-b-[1.5px] border-black min-w-[130px] pl-2 font-bold">
                {{ $serviceRequest->evaluation->rated_at ? $serviceRequest->evaluation->rated_at->format('m/d/Y') : now()->format('m/d/Y') }}
            </span>
        </div>
    </div>

    <!-- Feedback Instructions & Rating Scales (Enclosed with Top & Bottom Line) -->
    <div class="line-border-top-bottom py-3 my-4">
        <p class="text-justify text-[13px] leading-relaxed mb-3">
            This Feedback Form is an important tool for us to measure our performance to help us improve our services to our clients. Kindly fill out this form to assess the performance of our staff and to improve further the quality of our services. You may use this form to express your Praise, Recommendations, or Criticisms. Please let us know how we performed our services to you by Checking the appropriate box opposite the respective rating scales.
        </p>

        <div class="grid grid-cols-2 gap-x-12 max-w-[450px] mx-auto text-[13px]">
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
    <div class="mb-6 space-y-1.5 text-[14px]">
        <div>
            <span class="font-bold">Services Completed :</span>
            <span class="font-semibold border-b-[1.5px] border-black min-w-[200px] inline-block pl-2">{{ $serviceRequest->title }}</span>
        </div>
        <div>
            <span class="font-bold">GSO Service Team Leader:</span>
            <span class="font-semibold border-b-[1.5px] border-black min-w-[200px] inline-block pl-2">
                {{ $serviceRequest->project->approvedBy->user->first_name ?? 'BU-GSO' }} {{ $serviceRequest->project->approvedBy->user->last_name ?? 'Team Leader' }}
            </span>
        </div>
        <div>
            <span class="font-bold">Date Completed:</span>
            <span class="font-semibold border-b-[1.5px] border-black min-w-[200px] inline-block pl-2">
                {{ $serviceRequest->latestHistory ? \Carbon\Carbon::parse($serviceRequest->latestHistory->updated_at)->format('F d, Y') : 'N/A' }}
            </span>
        </div>
    </div>

    <!-- Functions & Rating Scales Table (Matches Mockup) -->
    @php
        $rating = (int) ($serviceRequest->evaluation->rating ?? 5);
    @endphp
    
    <table class="survey-table mb-8">
        <thead>
            <tr>
                <th rowspan="2" class="w-[60%] font-bold text-[15px] uppercase tracking-wide">FUNCTIONS</th>
                <th colspan="5" class="font-bold text-[14px] uppercase tracking-wide">RATING SCALES</th>
            </tr>
            <tr>
                <th class="w-[8%] font-bold">5</th>
                <th class="w-[8%] font-bold">4</th>
                <th class="w-[8%] font-bold">3</th>
                <th class="w-[8%] font-bold">2</th>
                <th class="w-[8%] font-bold">1</th>
            </tr>
        </thead>
        <tbody>
            @php
                $functions = [
                    'Quality of Service',
                    'Attitude',
                    'Safety Precaution Awareness',
                    'Time Bound',
                    'Workplace Housekeeping',
                ];
            @endphp

            @foreach($functions as $func)
                <tr>
                    <td class="function-col">{{ $func }}</td>
                    <td class="font-extrabold text-[15px]">{{ $rating === 5 ? '✓' : '' }}</td>
                    <td class="font-extrabold text-[15px]">{{ $rating === 4 ? '✓' : '' }}</td>
                    <td class="font-extrabold text-[15px]">{{ $rating === 3 ? '✓' : '' }}</td>
                    <td class="font-extrabold text-[15px]">{{ $rating === 2 ? '✓' : '' }}</td>
                    <td class="font-extrabold text-[15px]">{{ $rating === 1 ? '✓' : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Suggestions & Recommendations Ruled Lines Section -->
    <div class="mt-6">
        <div class="font-bold text-[14px] uppercase mb-2">
            SUGGESTIONS/RECOMMENDATIONS:
        </div>

        <div class="space-y-3">
            @php
                $feedback = trim($serviceRequest->evaluation->feedback_text ?? '');
                $lines = array_filter(explode("\n", wordwrap($feedback, 80, "\n")));
                if (empty($lines)) {
                    $lines = [''];
                }
                while(count($lines) < 4) {
                    $lines[] = '';
                }
            @endphp

            @foreach(array_slice($lines, 0, 5) as $line)
                <div class="border-b-[1.5px] border-black min-h-[24px] text-[13px] italic px-2 font-medium">
                    {{ $line }}
                </div>
            @endforeach
        </div>
    </div>

    <!-- Footer Form Metadata -->
    <div class="mt-12 w-full flex justify-between items-end text-[10px] border-t border-black pt-2 font-sans">
        <div>
            BU-F-GSO-CSM<br>
            Effective Date: October 9, 2025
        </div>
        <div class="font-bold">
            Clientele Satisfaction Survey
        </div>
        <div>
            Rev. No. 2
        </div>
    </div>

</div>

</body>
</html>
