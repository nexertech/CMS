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
            line-height: 1.4;
            font-size: 11px;
        }

        .container {
            max-width: 800px;
            /* A4 width approx */
            margin: 0 auto;
            background: white;
            padding: 25px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--primary-color);
            margin-bottom: 20px;
        }

        .brand-section h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        .brand-section p {
            margin: 3px 0 0;
            color: var(--secondary-color);
            font-size: 11px;
        }

        .meta-section {
            text-align: right;
        }

        .meta-section .slip-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }

        .meta-section .date {
            font-size: 11px;
            color: var(--secondary-color);
        }

        /* Grid Layout */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--secondary-color);
            margin-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 5px;
            letter-spacing: 0.5px;
        }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .label {
            width: 90px;
            font-weight: 500;
            color: var(--secondary-color);
            font-size: 11px;
        }

        .value {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 11px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
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
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 18px;
        }

        .description-box p {
            margin: 0;
            white-space: pre-line;
            font-size: 11px;
        }

        /* Comments Section - Inside Feedback */
        .comments-section {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .comments-header {
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--secondary-color);
            margin-bottom: 5px;
            letter-spacing: 0.3px;
        }

        .comments-box {
            min-height: 40px;
            border: 1px dashed var(--border-color);
            border-radius: 3px;
            padding: 6px;
            background: var(--bg-light);
            font-size: 10px;
            color: var(--secondary-color);
        }

        /* Feedback Section - Professional Scan Look - Compact */
        .feedback-container {
            border: 2px dashed var(--border-color);
            border-radius: 5px;
            padding: 12px;
            margin-top: 15px;
            page-break-inside: avoid;
        }

        .feedback-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .feedback-header h3 {
            margin: 0 0 2px;
            font-size: 12px;
            font-weight: 700;
        }

        .feedback-header p {
            margin: 0;
            font-size: 9px;
            color: var(--secondary-color);
        }

        .feedback-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        /* Manual Feedback Side */
        .manual-feedback {
            flex: 2;
        }

        .rating-options {
            display: flex;
            justify-content: space-around;
            margin-bottom: 15px;
        }

        .rating-box {
            text-align: center;
            padding: 5px;
            border: 1px solid transparent;
        }

        .circle-checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid var(--primary-color);
            border-radius: 50%;
            margin: 0 auto 5px;
            transition: all 0.2s;
        }

        .rating-label {
            display: block;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .emoji-icon {
            width: 24px;
            height: 24px;
            margin-bottom: 3px;
            fill: none;
            stroke: var(--primary-color);
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* Signature Line */
        .signature-area {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .sign-line {
            width: 180px;
            border-bottom: 1px dashed #333;
            text-align: center;
            font-size: 10px;
            padding-bottom: 3px;
            color: var(--secondary-color);
        }

        /* QR Side */
        .qr-section {
            flex: 1;
            text-align: center;
            border-left: 1px solid var(--border-color);
            padding-left: 20px;
        }

        .qr-box {
            background: white;
            padding: 8px;
            display: inline-block;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .qr-box img {
            display: block;
            width: 70px;
            height: 70px;
        }

        .qr-label {
            font-size: 9px;
            font-weight: 500;
            color: var(--secondary-color);
            margin-top: 4px;
            display: block;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: var(--secondary-color);
            border-top: 1px solid var(--border-color);
            padding-top: 15px;
        }

        @media print {
            @page {
                margin: 0.5cm;
            }

            body {
                padding: 0;
            }

            .container {
                width: 100%;
                max-width: none;
                padding: 20px;
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
                <div style="display: flex; align-items: center; gap: 10px;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
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

        <!-- 2 Column Layout -->
        <div class="grid-2">
            <!-- Left: Client Info -->
            <div>
                <div class="section-title">Client Information</div>
                <table class="data-table">
                    <tr>
                        <td class="label">Name:</td>
                        <td class="value">{{ $complaint->client->client_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">House No:</td>
                        <td class="value">{{ $complaint->house->username ?? 'N/A' }}</td>
                    </tr>
                    @if($complaint->client && $complaint->client->phone)
                        <tr>
                            <td class="label">Phone:</td>
                            <td class="value">{{ $complaint->client->phone }}</td>
                        </tr>
                    @endif
                    @if($complaint->client && $complaint->client->address)
                        <tr>
                            <td class="label">Address:</td>
                            <td class="value">{{ $complaint->client->address }}</td>
                        </tr>
                    @endif
                    @if($complaint->city_id && $complaint->city)
                        <tr>
                            <td class="label">Group/City:</td>
                            <td class="value">{{ $complaint->city->name }}</td>
                        </tr>
                    @endif
                    @if($complaint->sector_id && $complaint->sector)
                        <tr>
                            <td class="label">Node/Sector:</td>
                            <td class="value">{{ $complaint->sector->name }}</td>
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
                        <td class="value" style="font-size: 16px;">#{{ $complaint->id }}</td>
                    </tr>
                    <tr>
                        <td class="label">Type:</td>
                        <td class="value">{{ ucfirst($complaint->category) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Priority:</td>
                        <td class="value">
                            <span class="badge badge-{{ strtolower($complaint->priority) }}">
                                {{ ucfirst($complaint->priority) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Availability:</td>
                        <td class="value">{{ str_replace('T', ' ', $complaint->availability_time ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status:</td>
                        <td class="value">
                            <span class="badge badge-{{ strtolower($complaint->status) }}">
                                {{ ucfirst($complaint->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Date:</td>
                        <td class="value">{{ $complaint->created_at->timezone('Asia/Karachi')->format('M d, Y') }}<br><span
                                style="font-size: 11px; font-weight: normal; color: #666;">{{ $complaint->created_at->timezone('Asia/Karachi')->format('H:i') }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Assignment Info -->
        <div
            style="margin-bottom: 30px; border: 1px solid var(--border-color); padding: 15px; border-radius: 6px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="label" style="width: auto; margin-bottom: 2px;">Assigned Technician:</div>
                <div class="value" style="font-size: 15px;">
                    {{ $complaint->assignedEmployee->name ?? 'Unassigned' }}
                    @if($complaint->assignedEmployee && $complaint->assignedEmployee->designation)
                        <span style="font-weight: 400; color: var(--secondary-color);"> -
                            {{ $complaint->assignedEmployee->designation }}</span>
                    @endif
                </div>
            </div>
            @if($complaint->assignedEmployee && $complaint->assignedEmployee->phone)
                <div>
                    <div class="label" style="width: auto; margin-bottom: 2px;">Contact:</div>
                    <div class="value">{{ $complaint->assignedEmployee->phone }}</div>
                </div>
            @endif
        </div>

        <!-- Description -->
        <div class="section-title">Problem Description</div>
        <div class="description-box">
            <p>{{ $complaint->description ?: 'No detailed description provided.' }}</p>
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
                        <!-- Excellent -->
                        <div class="rating-box">
                            <svg class="emoji-icon" viewBox="0 0 24 24" style="stroke: #15803d;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                <line x1="15" y1="9" x2="15.01" y2="9"></line>
                            </svg>
                            <label class="rating-label" style="color: #15803d;">Excellent</label>
                            <div class="circle-checkbox" style="margin-top: 5px;"></div>
                        </div>

                        <!-- Good -->
                        <div class="rating-box">
                            <svg class="emoji-icon" viewBox="0 0 24 24" style="stroke: #b45309;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M8 14a4 4 0 0 0 8 0"></path>
                                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                <line x1="15" y1="9" x2="15.01" y2="9"></line>
                            </svg>
                            <label class="rating-label" style="color: #b45309;">Good</label>
                            <div class="circle-checkbox" style="margin-top: 5px;"></div>
                        </div>

                        <!-- Average -->
                        <div class="rating-box">
                            <svg class="emoji-icon" viewBox="0 0 24 24" style="stroke: #ca8a04;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="8" y1="15" x2="16" y2="15"></line>
                                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                                <line x1="15" y1="9" x2="15.01" y2="9"></line>
                            </svg>
                            <label class="rating-label" style="color: #ca8a04;">Average</label>
                            <div class="circle-checkbox" style="margin-top: 5px;"></div>
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
                            <div class="circle-checkbox" style="margin-top: 5px;"></div>
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
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ route('frontend.feedback', $complaint->id) }}"
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