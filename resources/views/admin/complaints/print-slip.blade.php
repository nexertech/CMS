<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Request #{{ $complaint->id }} - {{ $complaint->title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0f172a;
            --secondary-color: #64748b;
            --accent-color: #2563eb;
            --border-color: #e2e8f0;
            --bg-light: #f8fafc;
            --success-bg: #dcfce7;
            --success-text: #15803d;
            --warning-bg: #fef9c3;
            --warning-text: #a16207;
            --danger-bg: #fee2e2;
            --danger-text: #b91c1c;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--primary-color);
            background: white;
            line-height: 1.35;
            font-size: 11.5px;
        }

        .container {
            width: 680px;
            margin: 0 auto;
            background: white;
            padding: 8px 16px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 6px;
            border-bottom: 2px solid var(--primary-color);
            margin-bottom: 10px;
        }

        .brand-section h1 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        .brand-section p {
            margin: 2px 0 0;
            color: var(--secondary-color);
            font-size: 10.5px;
        }

        .meta-section {
            text-align: right;
        }

        .meta-section .slip-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .meta-section .date {
            font-size: 10.5px;
            color: var(--secondary-color);
        }

        /* Grid Layout */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 10px;
        }

        .grid-2 > div {
            display: flex;
            flex-direction: column;
        }

        .grid-2 .description-box {
            flex: 1;
        }

        .assignment-banner {
            display: flex;
            justify-content: space-between;
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            padding: 6px 12px;
            border-radius: 0;
            margin-bottom: 10px;
            font-size: 11.5px;
            color: var(--primary-color);
        }

        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--secondary-color);
            margin-bottom: 6px;
            border-bottom: 1.5px solid var(--border-color);
            padding-bottom: 3px;
            letter-spacing: 0.4px;
        }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .label {
            width: 115px;
            font-weight: 500;
            color: var(--secondary-color);
            font-size: 11px;
            white-space: nowrap;
        }

        .value {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 11.5px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 0;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-new {
            background: #eff6ff;
            color: #1e40af;
        }

        .badge-assigned {
            background: #fff7ed;
            color: #9a3412;
        }

        .badge-in_progress {
            background: #f0fdf4;
            color: #166534;
        }

        .badge-resolved {
            background: #f0fdf4;
            color: #15803d;
        }

        .badge-closed {
            background: #f3e8ff;
            color: #6b21a8;
        }

        .badge-urgent {
            background: var(--danger-bg);
            color: var(--danger-text);
        }

        .badge-high {
            background: var(--warning-bg);
            color: var(--warning-text);
        }

        .badge-medium {
            background: #eff6ff;
            color: #1e40af;
        }

        .badge-low {
            background: var(--success-bg);
            color: var(--success-text);
        }

        /* Description Box */
        .description-box {
            background: var(--bg-light);
            border: 1px dashed #94a3b8;
            border-radius: 0;
            padding: 8px;
            margin-bottom: 8px;
        }

        .description-box p {
            margin: 0;
            white-space: pre-line;
            font-size: 11px;
        }

        /* Comments Section - Inside Feedback */
        .comments-section {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
        }

        .comments-header {
            font-size: 10.5px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--secondary-color);
            margin-bottom: 4px;
            letter-spacing: 0.3px;
        }

        .comments-box {
            min-height: 32px;
            border: 1px dashed var(--border-color);
            border-radius: 0;
            padding: 6px;
            background: var(--bg-light);
            font-size: 10.5px;
            color: var(--secondary-color);
        }

        /* Feedback Section - Professional Scan Look - Compact */
        .feedback-container {
            border: 1.5px dashed var(--border-color);
            border-radius: 0;
            padding: 8px 12px;
            margin-top: 8px;
            page-break-inside: avoid;
        }

        .feedback-header {
            text-align: center;
            margin-bottom: 8px;
        }

        .feedback-header h3 {
            margin: 0 0 3px;
            font-size: 13px;
            font-weight: 700;
        }

        .feedback-header p {
            margin: 0;
            font-size: 10px;
            color: var(--secondary-color);
        }

        .feedback-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        /* Manual Feedback Side */
        .manual-feedback {
            flex: 2.2;
        }

        .rating-options {
            display: flex;
            justify-content: space-around;
            margin-bottom: 6px;
        }

        .rating-box {
            text-align: center;
            padding: 3px;
            border: 1px solid transparent;
        }

        .circle-checkbox {
            width: 15px;
            height: 15px;
            border: 1.5px solid var(--primary-color);
            border-radius: 0;
            margin: 0 auto 4px;
            transition: all 0.2s;
        }

        .rating-label {
            display: block;
            font-size: 10.5px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .emoji-icon {
            width: 20px;
            height: 20px;
            margin-bottom: 3px;
            fill: none;
            stroke: var(--primary-color);
            stroke-width: 1.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* Signature Line */
        .signature-area {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .sign-line {
            width: 130px;
            border-bottom: 1px dashed #333;
            text-align: center;
            font-size: 10.5px;
            padding-bottom: 3px;
            color: var(--secondary-color);
        }

        /* QR Side */
        .qr-section {
            flex: 1;
            text-align: center;
            border-left: 1px solid var(--border-color);
            padding-left: 12px;
        }

        .qr-box {
            background: white;
            padding: 5px;
            display: inline-block;
            border: 1px solid var(--border-color);
            border-radius: 0;
        }

        .qr-box img {
            display: block;
            width: 95px;
            height: 95px;
        }

        .qr-label {
            font-size: 10px;
            font-weight: 500;
            color: var(--secondary-color);
            margin-top: 4px;
            display: block;
        }

        /* Footer */
        .footer {
            margin-top: 12px;
            text-align: center;
            font-size: 10px;
            color: var(--secondary-color);
            border-top: 1px solid var(--border-color);
            padding-top: 6px;
        }

        @media print {
            @page {
                margin: 0.4cm;
            }

            body {
                padding: 0;
            }

            .container {
                width: 680px;
                max-width: 680px;
                padding: 4px 0;
                margin: 0 auto;
            }

            .no-print {
                display: none;
            }

            /* Force background colors */
            .badge-urgent {
                background-color: #fee2e2 !important;
                -webkit-print-color-adjust: exact;
            }

            .badge-high {
                background-color: #fef9c3 !important;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="brand-section">
                <!-- Branding: Could use an img tag here if user has a logo file -->
                <div style="display: flex; align-items: center; gap: 6px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary-color);">
                        <path
                            d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                    </svg>
                    <div>
                        <h1>MES Complaint Management System</h1>
                        <p>Service Request Slip</p>
                    </div>
                </div>
            </div>
            <div class="meta-section">
                <div class="slip-title">Service Request Slip</div>
                <div class="date">Printed: {{ now()->timezone('Asia/Karachi')->format('M d, Y H:i') }}</div>
            </div>
        </div>

        <!-- Assignment Info Banner -->
        <div class="assignment-banner">
            <span><strong>Assigned Technician:</strong> {{ $complaint->assignedEmployee->name ?? 'Unassigned' }} @if($complaint->assignedEmployee && $complaint->assignedEmployee->designation) ({{ $complaint->assignedEmployee->designation->name ?? $complaint->assignedEmployee->designation }}) @endif</span>
            @if($complaint->assignedEmployee && $complaint->assignedEmployee->phone)
                <span><strong>Contact:</strong> {{ $complaint->assignedEmployee->phone }}</span>
            @endif
        </div>

        <!-- 2 Column Layout (Client & Request Details) -->
        <div class="grid-2">
            <!-- Left: Client Info -->
            <div>
                <div class="section-title">Client Information</div>
                <table class="data-table">
                    <tr>
                        <td class="label">House No:</td>
                        <td class="value">{{ $complaint->house->house_no ?? 'N/A' }}</td>
                    </tr>
                    @if($complaint->house && $complaint->house->phone)
                        <tr>
                            <td class="label">Phone:</td>
                            <td class="value">{{ $complaint->house->phone }}</td>
                        </tr>
                    @endif
                    @if($complaint->house && $complaint->house->address)
                        <tr>
                            <td class="label">Address:</td>
                            <td class="value">{{ $complaint->house->address }}</td>
                        </tr>
                    @endif
                    @if($complaint->city_id && $complaint->city)
                        <tr>
                            <td class="label">GE Group:</td>
                            <td class="value">{{ $complaint->city->name }}</td>
                        </tr>
                    @endif
                    @if($complaint->sector_id && $complaint->sector)
                        <tr>
                            <td class="label">GE Node:</td>
                            <td class="value">{{ $complaint->sector->name }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label">Nature/Type:</td>
                        <td class="value">{{ ucfirst($complaint->getCategoryDisplayAttribute()) . ' - ' . $complaint->getTitleDisplayAttribute() }}</td>
                    </tr>
                    @php
                        $createdLog = $complaint->logs->where('action', 'created')->first();
                        $registeredBy = null;
                        if ($createdLog) {
                            if (str_contains($createdLog->remarks, 'created by ')) {
                                $registeredBy = trim(str_replace('Complaint created by ', '', $createdLog->remarks));
                            } elseif (str_contains($createdLog->remarks, 'registered via App by ')) {
                                $registeredBy = trim(str_replace('Complaint registered via App by ', '', $createdLog->remarks));
                            } else {
                                $registeredBy = $createdLog->actionBy->name ?? 'Staff';
                            }
                        }

                        // Get the latest status change log
                        $statusLog = $complaint->logs->whereIn('action', ['status_changed', 'resolved', 'closed'])->last();
                        $statusChangedBy = null;
                        if ($statusLog) {
                            if (str_contains($statusLog->remarks, ' by ')) {
                                $parts = explode(' by ', $statusLog->remarks);
                                $afterBy = end($parts);
                                $cleanParts = explode('. Remarks:', $afterBy);
                                $statusChangedBy = trim($cleanParts[0]);
                            } else {
                                $statusChangedBy = auth()->user()->name ?? auth()->user()->username ?? 'Staff';
                            }
                        }
                    @endphp
                    @if($registeredBy)
                        <tr>
                            <td class="label">Registered By:</td>
                            <td class="value">{{ $registeredBy }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            <!-- Right: Complaint Info -->
            <div>
                <div class="section-title">Request Details</div>
                <table class="data-table">
                    <tr>
                        <td class="label">Complaint #</td>
                        <td class="value" style="font-size: 11px;">#{{ $complaint->id }}</td>
                    </tr>
                    <tr>
                        <td class="label">Availability:</td>
                        <td class="value">{{ str_replace('T', ' ', $complaint->availability_time ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status:</td>
                        <td class="value">{{ ucwords(str_replace('_', ' ', $complaint->status === 'resolved' ? 'addressed' : $complaint->status)) }}</td>
                    </tr>
                    @if($statusChangedBy)
                        <tr>
                            <td class="label">Changed By:</td>
                            <td class="value">{{ $statusChangedBy }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label">Date:</td>
                        <td class="value">{{ $complaint->created_at->timezone('Asia/Karachi')->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Description:</td>
                        <td class="value" style="font-weight: 500; white-space: pre-line;">{{ $complaint->description ?: 'No detailed description provided.' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Technical Remarks / Work Done (Full Width) -->
        <div style="margin-bottom: 6px;">
            <div class="section-title">Technician Remarks / Work Done</div>
            <div class="description-box" style="margin-bottom: 0; min-height: 48px;">
                <div style="border-bottom: 1px dotted var(--border-color); margin-top: 12px;"></div>
                <div style="border-bottom: 1px dotted var(--border-color); margin-top: 12px;"></div>
            </div>
        </div>

        <!-- Feedback & Closing Section -->
        <div class="feedback-container">
            <div class="feedback-header">
                <h3>Job Completion & Feedback</h3>
                <p>To be filled by the client after job completion</p>
            </div>

            <div class="feedback-content">
                <!-- Manual Feedback -->
                <div class="manual-feedback">
                    <div class="rating-options">
                        <!-- Good -->
                        <div class="rating-box">
                            <svg class="emoji-icon" viewBox="0 0 24 24" style="stroke: #2563eb;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M8 14a4 4 0 0 0 8 0"></path>
                                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                <line x1="15" y1="9" x2="15.01" y2="9"></line>
                            </svg>
                            <label class="rating-label" style="color: #2563eb;">Good</label>
                            <div class="circle-checkbox" style="margin-top: 3px;"></div>
                        </div>

                        <!-- Satisfied -->
                        <div class="rating-box">
                            <svg class="emoji-icon" viewBox="0 0 24 24" style="stroke: #0ea5e9;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M8 13.5s1.5 1.5 4 1.5 4-1.5 4-1.5"></path>
                                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                <line x1="15" y1="9" x2="15.01" y2="9"></line>
                            </svg>
                            <label class="rating-label" style="color: #0ea5e9;">Satisfied</label>
                            <div class="circle-checkbox" style="margin-top: 3px;"></div>
                        </div>

                        <!-- Poor -->
                        <div class="rating-box">
                            <svg class="emoji-icon" viewBox="0 0 24 24" style="stroke: #b91c1c;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M16 16s-1.5-2-4-2-4 2-4 2"></path>
                                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                <line x1="15" y1="9" x2="15.01" y2="9"></line>
                            </svg>
                            <label class="rating-label" style="color: #b91c1c;">Poor</label>
                            <div class="circle-checkbox" style="margin-top: 3px;"></div>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="comments-section">
                        <div class="comments-header">Comments / Manual Notes</div>
                        <div class="comments-box">
                            <!-- Empty space for manual comments -->
                        </div>
                    </div>

                    <div class="signature-area">
                        <div class="sign-line">Technician Signature</div>
                        <div class="sign-line">Client Signature</div>
                    </div>
                </div>

                <!-- Digital Feedback -->
                <div class="qr-section">
                    <div class="qr-box">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ route('frontend.feedback', $complaint->id) }}"
                            alt="Scan for Feedback">
                    </div>
                    <span class="qr-label">Scan for Digital Feedback</span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>System Generated Slip | {{ config('app.name') }} | Printed by {{ auth()->user()->name ?? 'System' }}</p>
        </div>
    </div>

    <script>
        window.onload = function () {
            window.print();
        }
    </script>
</body>

</html>