<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $serviceRequest->formatted_id ?? 'LINKod Notification' }}</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; color: #1e293b;">

@php
    $reqId = $serviceRequest->formatted_id ?? ('REQ-' . str_pad((string)$serviceRequest->request_id, 4, '0', STR_PAD_LEFT));
    $showUrl = route('client.requests.show', $serviceRequest->request_id);
    $rateUrl = route('client.evaluations.create', $serviceRequest->request_id);

    $isCompleted = ($eventType === 'completed');
    $isSchedule = ($eventType === 'schedule_proposed');
    $isBom = ($eventType === 'bom_ready');
    $isInProgress = ($eventType === 'in_progress');
    $isSubmitted = ($eventType === 'submitted');

    // Badge styling & header texts
    $badgeText = match($eventType) {
        'submitted'         => 'REQUISITION RECEIVED',
        'schedule_proposed' => 'VISIT SCHEDULE PROPOSED',
        'bom_ready'         => 'MATERIALS SIGN-OFF REQUIRED',
        'in_progress'       => 'WORK IN PROGRESS',
        'completed'         => 'JOB COMPLETED & VERIFIED',
        default             => 'STATUS UPDATE',
    };

    $badgeBg = match($eventType) {
        'submitted'         => '#dbeafe',
        'schedule_proposed' => '#e0e7ff',
        'bom_ready'         => '#fef3c7',
        'in_progress'       => '#cffafe',
        'completed'         => '#d1fae5',
        default             => '#e2e8f0',
    };

    $badgeColor = match($eventType) {
        'submitted'         => '#1e40af',
        'schedule_proposed' => '#3730a3',
        'bom_ready'         => '#92400e',
        'in_progress'       => '#155e75',
        'completed'         => '#065f46',
        default             => '#334155',
    };

    $primaryActionUrl = $isCompleted ? $rateUrl : $showUrl;
    $primaryActionText = match($eventType) {
        'completed'         => 'Rate Service & Submit Feedback',
        'schedule_proposed' => 'Review & Confirm Schedule',
        'bom_ready'         => 'Review & Approve Materials',
        'in_progress'       => 'Track Progress On Site',
        default             => 'View Requisition Details',
    };

    // Official LINKod logo embedding
    $logoFile = public_path('images/LINKOD logo.png');
    $hasLogo = file_exists($logoFile);
    $logoSrc = null;
    if ($hasLogo) {
        $logoSrc = (isset($message) && method_exists($message, 'embed'))
            ? $message->embed($logoFile)
            : asset('images/LINKOD logo.png');
    }
@endphp

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f1f5f9; padding: 24px 12px;">
    <tr>
        <td align="center">
            <!-- Main Email Container -->
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid #e2e8f0;">
                
                <!-- Brand Top Header -->
                <tr>
                    <td style="background: #0033a0; padding: 22px 32px; text-align: left;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td>
                                    <!-- BU-GSO LINKod Brand Pill (Matching App Nav) -->
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="background-color: #ffffff; padding: 6px 14px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);">
                                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td style="vertical-align: middle; padding-right: 8px;">
                                                            <span style="color: #0033a0; font-size: 15px; font-weight: 900; letter-spacing: -0.3px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                                                                BU-GSO
                                                            </span>
                                                        </td>
                                                        <td style="vertical-align: middle; color: #cbd5e1; font-size: 15px; padding-right: 8px;">
                                                            |
                                                        </td>
                                                        <td style="vertical-align: middle;">
                                                            @if($logoSrc)
                                                                <img src="{{ $logoSrc }}" alt="LINKod" height="24" style="display: block; height: 24px; width: auto; max-width: 100px; border: 0; vertical-align: middle;">
                                                            @else
                                                                <span style="color: #0033a0; font-size: 15px; font-weight: 900; letter-spacing: -0.3px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                                                                    LINK<span style="color: #eab308;">od</span>
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                    <p style="margin: 8px 0 0 2px; font-size: 11.5px; color: #bfdbfe; font-weight: 500; letter-spacing: 0.3px;">
                                        General Services Office • Bicol University
                                    </p>
                                </td>
                                <td align="right" style="vertical-align: top; padding-top: 4px;">
                                    <span style="display: inline-block; background-color: rgba(255, 255, 255, 0.18); color: #ffffff; font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.4px;">
                                        Requisition #{{ $reqId }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Yellow/Gold Accent Divider Line -->
                <tr>
                    <td style="height: 4px; background-color: #fcd116; font-size: 0; line-height: 0;">&nbsp;</td>
                </tr>

                <!-- Email Body -->
                <tr>
                    <td style="padding: 32px 32px 24px 32px;">
                        
                        <!-- Status Pill -->
                        <div style="margin-bottom: 16px;">
                            <span style="display: inline-block; background-color: {{ $badgeBg }}; color: {{ $badgeColor }}; font-size: 11px; font-weight: 800; padding: 5px 12px; border-radius: 9999px; letter-spacing: 0.5px;">
                                {{ $badgeText }}
                            </span>
                        </div>

                        <!-- Greeting -->
                        <h2 style="margin: 0 0 12px 0; font-size: 18px; font-weight: 800; color: #0f172a;">
                            Hello, {{ $clientUser->first_name ?: 'Valued Client' }}!
                        </h2>

                        <!-- Dynamic Event Message -->
                        <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #334155;">
                            @if($isSubmitted)
                                Your service requisition <strong>"{{ $serviceRequest->title }}"</strong> has been successfully submitted and logged into LINKod. The General Services Office (GSO) will review your request and assign technical personnel shortly.
                            @elseif($isSchedule)
                                A site visit schedule has been proposed for your requisition <strong>"{{ $serviceRequest->title }}"</strong>. Please review the proposed date and confirm or request rescheduling.
                            @elseif($isBom)
                                The List of Materials (BOM) for your requisition <strong>"{{ $serviceRequest->title }}"</strong> has been prepared and verified by GSO Admin. Your sign-off is required so materials procurement can proceed without delay.
                            @elseif($isInProgress)
                                Work has officially commenced on site for your requisition <strong>"{{ $serviceRequest->title }}"</strong>. The assigned personnel have arrived with materials and are executing the requested tasks.
                            @elseif($isCompleted)
                                Great news! Your service requisition <strong>"{{ $serviceRequest->title }}"</strong> has been completed and verified by GSO Admin. We kindly ask you to take a moment to evaluate the quality of service provided to help us continually improve.
                            @else
                                There is a new status update regarding your requisition <strong>"{{ $serviceRequest->title }}"</strong>. The current status is now: <strong>{{ $serviceRequest->current_status }}</strong>.
                            @endif
                        </p>

                        <!-- Requisition Summary Card -->
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
                            <tr>
                                <td style="padding: 16px 20px;">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; width: 38%;">
                                                Requisition No.
                                            </td>
                                            <td style="padding: 4px 0; font-size: 13px; font-weight: 800; color: #0033a0;">
                                                #{{ $reqId }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">
                                                Title / Subject
                                            </td>
                                            <td style="padding: 4px 0; font-size: 13px; font-weight: 600; color: #0f172a;">
                                                {{ $serviceRequest->title }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">
                                                Service Category
                                            </td>
                                            <td style="padding: 4px 0; font-size: 12px; font-weight: 600; color: #334155;">
                                                {{ $serviceRequest->category->category_name ?? 'General Maintenance' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">
                                                Location &amp; Campus
                                            </td>
                                            <td style="padding: 4px 0; font-size: 12px; font-weight: 600; color: #334155;">
                                                {{ $serviceRequest->location ?? 'Campus Facility' }} ({{ $serviceRequest->campus ?? 'BU Main' }})
                                            </td>
                                        </tr>
                                        @if($isSchedule && !empty($extraData['scheduled_date']))
                                            <tr>
                                                <td style="padding: 6px 0; font-size: 11px; font-weight: 800; color: #1e40af; text-transform: uppercase;">
                                                    Proposed Visit Date
                                                </td>
                                                <td style="padding: 6px 0; font-size: 13px; font-weight: 800; color: #1e40af;">
                                                    {{ $extraData['scheduled_date'] }} ({{ $extraData['scheduled_window'] ?? 'Morning' }})
                                                </td>
                                            </tr>
                                        @elseif($serviceRequest->scheduled_date)
                                            <tr>
                                                <td style="padding: 4px 0; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">
                                                    Scheduled Date
                                                </td>
                                                <td style="padding: 4px 0; font-size: 12px; font-weight: 600; color: #334155;">
                                                    {{ $serviceRequest->scheduled_date->format('M d, Y') }} ({{ $serviceRequest->scheduled_time_window }})
                                                </td>
                                            </tr>
                                        @endif
                                        @if($isCompleted && !empty($extraData['nature_of_work']))
                                            <tr>
                                                <td style="padding: 6px 0; font-size: 11px; font-weight: 800; color: #065f46; text-transform: uppercase;">
                                                    Nature of Work Done
                                                </td>
                                                <td style="padding: 6px 0; font-size: 12px; font-weight: 700; color: #065f46;">
                                                    {{ $extraData['nature_of_work'] }}
                                                </td>
                                            </tr>
                                        @elseif($isCompleted && $serviceRequest->project?->nature_of_work)
                                            <tr>
                                                <td style="padding: 6px 0; font-size: 11px; font-weight: 800; color: #065f46; text-transform: uppercase;">
                                                    Nature of Work Done
                                                </td>
                                                <td style="padding: 6px 0; font-size: 12px; font-weight: 700; color: #065f46;">
                                                    {{ $serviceRequest->project->nature_of_work }}
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- Call-To-Action (Primary Button) -->
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 16px;">
                            <tr>
                                <td align="center">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td align="center" style="border-radius: 12px; background-color: {{ $isCompleted ? '#059669' : '#0033a0' }};">
                                                <a href="{{ $primaryActionUrl }}" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 13.5px; font-weight: 800; color: #ffffff; text-decoration: none; border-radius: 12px; letter-spacing: 0.3px;">
                                                    @if($isCompleted)
                                                        ★ {{ $primaryActionText }}
                                                    @else
                                                        {{ $primaryActionText }} →
                                                    @endif
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        @if($isCompleted)
                            <!-- Secondary Button for Completed: View Requisition Details -->
                            <div style="text-align: center; margin-bottom: 24px;">
                                <a href="{{ $showUrl }}" target="_blank" style="font-size: 12.5px; font-weight: 700; color: #0033a0; text-decoration: underline;">
                                    View Full Request Details &amp; Photographic Evidence
                                </a>
                            </div>
                        @endif

                        <!-- Plaintext Link Fallback for Accessibility -->
                        <div style="padding-top: 16px; border-top: 1px solid #f1f5f9; font-size: 11px; color: #94a3b8; line-height: 1.5;">
                            If you have difficulty clicking the button above, copy and paste this direct URL into your web browser:<br>
                            <a href="{{ $primaryActionUrl }}" target="_blank" style="color: #0033a0; word-break: break-all;">
                                {{ $primaryActionUrl }}
                            </a>
                        </div>

                    </td>
                </tr>

                <!-- Email Footer -->
                <tr>
                    <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 32px; text-align: center;">
                        <p style="margin: 0 0 6px 0; font-size: 11px; font-weight: 700; color: #475569;">
                            General Services Office (GSO) • Bicol University
                        </p>
                        <p style="margin: 0; font-size: 10px; color: #94a3b8; line-height: 1.4;">
                            This is an automated notification from LINKod. Please do not reply directly to this email.<br>
                            To manage or view your requisitions, log in to your account at Bicol University.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
