<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Request #{{ $complaint->id }} - {{ $complaint->title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700;800&display=swap" rel="stylesheet">
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
            background: #f1f5f9;
            line-height: 1.25;
            font-size: 8.5px;
        }

        /* Top Action Bar (Screen Only) */
        .top-action-bar {
            position: sticky;
            top: 0;
            z-index: 999;
            background: #0f172a;
            color: white;
            padding: 10px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-family: 'Inter', sans-serif;
            font-size: 13px;
        }

        .bar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .bar-title {
            font-weight: 700;
            letter-spacing: -0.2px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .format-switcher {
            display: flex;
            background: #1e293b;
            padding: 3px;
            border-radius: 8px;
            gap: 4px;
            border: 1px solid #334155;
        }

        .format-btn {
            background: transparent;
            color: #94a3b8;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease-in-out;
        }

        .format-btn:hover {
            color: #ffffff;
            background: #334155;
        }

        .format-btn.active {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
        }

        .bar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print {
            background: #10b981;
            color: white;
            border: none;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s;
        }

        .btn-print:hover {
            background: #059669;
        }

        .btn-close {
            background: #334155;
            color: #cbd5e1;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-close:hover {
            background: #475569;
            color: white;
        }

        .memory-hint {
            font-size: 11px;
            color: #94a3b8;
            margin-left: 8px;
        }

        .slip-wrapper {
            padding: 20px 10px;
            display: flex;
            justify-content: center;
        }

        /* -------------------------------------------------------------
           1. STANDARD SLIP (A4 / Half-Page Layout)
        ------------------------------------------------------------- */
        .container.standard-slip {
            width: 580px;
            margin: 0 auto;
            background: white;
            padding: 12px 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 4px;
        }

        .standard-slip .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 4px;
            border-bottom: 1.5px solid var(--primary-color);
            margin-bottom: 8px;
        }

        .standard-slip .brand-section h1 {
            margin: 0;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        .standard-slip .brand-section p {
            margin: 2px 0 0;
            color: var(--secondary-color);
            font-size: 8px;
        }

        .standard-slip .meta-section {
            text-align: right;
        }

        .standard-slip .meta-section .slip-title {
            font-size: 9.5px;
            font-weight: 600;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .standard-slip .meta-section .date {
            font-size: 8px;
            color: var(--secondary-color);
        }

        .standard-slip .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 8px;
        }

        .standard-slip .grid-2 > div {
            display: flex;
            flex-direction: column;
        }

        .standard-slip .assignment-banner {
            display: flex;
            justify-content: space-between;
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            padding: 4px 8px;
            border-radius: 0;
            margin-bottom: 8px;
            font-size: 8.5px;
            color: var(--primary-color);
        }

        .standard-slip .section-title {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--secondary-color);
            margin-bottom: 4px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 2px;
            letter-spacing: 0.3px;
        }

        .standard-slip .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .standard-slip .data-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .standard-slip .label {
            width: 95px;
            font-weight: 500;
            color: var(--secondary-color);
            font-size: 8.5px;
            white-space: nowrap;
        }

        .standard-slip .value {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 8.5px;
        }

        .standard-slip .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 7.5px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .standard-slip .badge-urgent {
            background: var(--danger-bg);
            color: var(--danger-text);
        }

        .standard-slip .badge-medium {
            background: #eff6ff;
            color: #1e40af;
        }

        .standard-slip .description-box {
            background: var(--bg-light);
            border: 1px dashed #94a3b8;
            padding: 6px;
            margin-bottom: 6px;
        }

        .standard-slip .description-box p {
            margin: 0;
            white-space: pre-line;
            font-size: 8.5px;
        }

        .standard-slip .feedback-container {
            border: 1.5px dashed var(--border-color);
            padding: 6px;
            margin-top: 6px;
            page-break-inside: avoid;
        }

        .standard-slip .feedback-header {
            text-align: center;
            margin-bottom: 6px;
        }

        .standard-slip .feedback-header h3 {
            margin: 0 0 2px;
            font-size: 9.5px;
            font-weight: 700;
        }

        .standard-slip .feedback-header p {
            margin: 0;
            font-size: 8px;
            color: var(--secondary-color);
        }

        .standard-slip .feedback-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .standard-slip .manual-feedback {
            flex: 2.2;
        }

        .standard-slip .rating-options {
            display: flex;
            justify-content: space-around;
            margin-bottom: 4px;
        }

        .standard-slip .rating-box {
            text-align: center;
            padding: 2px;
        }

        .standard-slip .circle-checkbox {
            width: 12px;
            height: 12px;
            border: 1.5px solid var(--primary-color);
            margin: 0 auto 3px;
        }

        .standard-slip .rating-label {
            display: block;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .standard-slip .emoji-icon {
            width: 16px;
            height: 16px;
            margin-bottom: 2px;
            fill: none;
            stroke: var(--primary-color);
            stroke-width: 1.5;
        }

        .standard-slip .comments-section {
            margin-bottom: 6px;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--border-color);
        }

        .standard-slip .comments-header {
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--secondary-color);
            margin-bottom: 3px;
        }

        .standard-slip .comments-box {
            min-height: 25px;
            border: 1px dashed var(--border-color);
            padding: 4px;
            background: var(--bg-light);
            font-size: 8px;
            color: var(--secondary-color);
        }

        .standard-slip .signature-area {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .standard-slip .sign-line {
            width: 110px;
            border-bottom: 1px dashed #333;
            text-align: center;
            font-size: 8px;
            padding-bottom: 2px;
            color: var(--secondary-color);
        }

        .standard-slip .qr-section {
            flex: 1;
            text-align: center;
            border-left: 1px solid var(--border-color);
            padding-left: 10px;
        }

        .standard-slip .qr-box {
            background: white;
            padding: 4px;
            display: inline-block;
            border: 1px solid var(--border-color);
        }

        .standard-slip .qr-box img {
            display: block;
            width: 80px;
            height: 80px;
        }

        .standard-slip .qr-label {
            font-size: 8px;
            font-weight: 500;
            color: var(--secondary-color);
            margin-top: 3px;
            display: block;
        }

        .standard-slip .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 8px;
            color: var(--secondary-color);
            border-top: 1px solid var(--border-color);
            padding-top: 5px;
        }


        /* -------------------------------------------------------------
           2. THERMAL RECEIPT (80mm POS Roll Layout)
        ------------------------------------------------------------- */
        .container.thermal-slip {
            width: 76mm;
            max-width: 76mm;
            margin: 0 auto;
            background: white;
            padding: 10px 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            color: #000000 !important;
            font-family: 'Inter', 'JetBrains Mono', 'Segoe UI', monospace, sans-serif;
            font-size: 11px;
            line-height: 1.35;
        }

        .thermal-slip * {
            color: #000000 !important;
            border-color: #000000 !important;
        }

        .thermal-header {
            text-align: center;
            padding-bottom: 6px;
            border-bottom: 2px solid #000;
            margin-bottom: 6px;
        }

        .thermal-header h2 {
            margin: 0;
            font-size: 13.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -0.3px;
        }

        .thermal-header .thermal-sub {
            font-size: 11px;
            font-weight: 700;
            margin-top: 2px;
            text-transform: uppercase;
        }

        .thermal-header .thermal-time {
            font-size: 10px;
            margin-top: 3px;
            color: #111 !important;
        }

        .thermal-tech-banner {
            background: #f1f5f9 !important;
            border: 1px solid #000;
            padding: 5px 6px;
            margin: 6px 0;
            font-size: 10.5px;
        }

        .thermal-tech-banner strong {
            font-weight: 800;
        }

        .thermal-id-banner {
            border: 1.5px solid #000;
            padding: 5px 6px;
            text-align: center;
            margin: 6px 0;
            font-weight: 800;
            font-size: 13px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .thermal-id-banner .thermal-badge {
            background: #000 !important;
            color: #fff !important;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .thermal-divider {
            border-bottom: 1px dashed #000;
            margin: 6px 0;
        }

        .thermal-sec-title {
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            margin: 6px 0 4px 0;
            background: #e2e8f0;
            padding: 3px 5px;
            border-left: 3px solid #000;
            letter-spacing: 0.3px;
        }

        .thermal-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .thermal-table td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 10.5px;
        }

        .thermal-label {
            font-weight: 700;
            width: 95px;
            white-space: nowrap;
        }

        .thermal-val {
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }

        .thermal-desc-box {
            border: 1px dashed #000;
            padding: 5px;
            margin: 4px 0 6px 0;
            font-size: 10px;
            word-break: break-word;
            white-space: pre-line;
            min-height: 28px;
            background: #fafafa;
        }

        .thermal-work-done {
            border: 1px dashed #000;
            padding: 4px;
            margin: 4px 0 6px 0;
            min-height: 48px;
        }

        .thermal-work-line {
            border-bottom: 1px dotted #000;
            margin-top: 15px;
        }

        .thermal-feedback-block {
            border: 1.5px dashed #000;
            padding: 6px 4px;
            margin-top: 6px;
            text-align: center;
        }

        .thermal-feedback-title {
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .thermal-feedback-sub {
            font-size: 9px;
            margin-bottom: 6px;
        }

        .thermal-ratings {
            display: flex;
            justify-content: space-around;
            margin: 6px 0 8px 0;
            font-size: 10px;
            font-weight: 700;
        }

        .thermal-ratings span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .thermal-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1.5px solid #000;
        }

        .thermal-comments-box {
            border: 1px dashed #000;
            min-height: 24px;
            margin: 4px 0 6px 0;
            padding: 3px;
            font-size: 9px;
            text-align: left;
        }

        .thermal-qr-container {
            margin: 8px auto 4px auto;
            text-align: center;
        }

        .thermal-qr-container img {
            width: 105px;
            height: 105px;
            display: block;
            margin: 0 auto;
        }

        .thermal-qr-text {
            font-size: 9.5px;
            font-weight: 800;
            margin-top: 3px;
            text-transform: uppercase;
        }

        .thermal-signs {
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            font-size: 9.5px;
            font-weight: 700;
        }

        .thermal-sign-col {
            width: 47%;
            text-align: center;
        }

        .thermal-sign-underline {
            border-bottom: 1px solid #000;
            height: 20px;
            margin-bottom: 3px;
        }

        .thermal-footer {
            text-align: center;
            margin-top: 10px;
            font-size: 9px;
            padding-top: 6px;
            border-top: 1px dashed #000;
            line-height: 1.3;
        }

        .thermal-cut-mark {
            text-align: center;
            margin: 10px 0 4px 0;
            font-size: 10px;
            letter-spacing: 2px;
        }


        /* -------------------------------------------------------------
           3. PRINT MEDIA QUERIES
        ------------------------------------------------------------- */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .slip-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }

            /* When Standard is active */
            body.show-standard .container.standard-slip {
                display: block !important;
                width: 580px !important;
                max-width: 580px !important;
                padding: 4px 0 !important;
                margin: 0 auto !important;
                box-shadow: none !important;
            }

            body.show-standard .container.thermal-slip {
                display: none !important;
            }

            body.show-standard @page {
                size: portrait;
                margin: 0.4cm;
            }

            /* When Thermal is active */
            body.show-thermal .container.thermal-slip {
                display: block !important;
                width: 72mm !important;
                max-width: 72mm !important;
                padding: 0 !important;
                margin: 0 auto !important;
                box-shadow: none !important;
            }

            body.show-thermal .container.standard-slip {
                display: none !important;
            }

            body.show-thermal @page {
                size: 80mm auto;
                margin: 1.5mm 2mm;
            }

            .badge-urgent {
                background-color: #fee2e2 !important;
            }

            .badge-medium {
                background-color: #eff6ff !important;
            }
        }

        /* Screen Display Toggle Handling */
        body.show-standard .container.thermal-slip {
            display: none;
        }

        body.show-standard .container.standard-slip {
            display: block;
        }

        body.show-thermal .container.standard-slip {
            display: none;
        }

        body.show-thermal .container.thermal-slip {
            display: block;
        }
    </style>
</head>

<body class="show-standard">

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

        $pVal = strtolower($complaint->priority ?? 'normal');
        $isEmerg = in_array($pVal, ['emergency', 'urgent', 'high'], true);
    @endphp

    <!-- Top Action Toolbar (Never Printed) -->
    <div class="top-action-bar no-print">
        <div class="bar-left">
            <div class="bar-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                <span>Print Slip Mode:</span>
            </div>

            <div class="format-switcher">
                <button type="button" id="btn-standard" class="format-btn active" onclick="setFormat('standard', true)">
                    📄 Standard (A4 / Half)
                </button>
                <button type="button" id="btn-thermal" class="format-btn" onclick="setFormat('thermal', true)">
                    🧾 Thermal Receipt (80mm)
                </button>
            </div>
            <span class="memory-hint" id="memory-status">⚡ Auto-saved for this PC</span>
        </div>

        <div class="bar-right">
            <button type="button" class="btn-print" onclick="window.print()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Print Slip (Ctrl+P)
            </button>
            <button type="button" class="btn-close" onclick="window.close()">✕ Close</button>
        </div>
    </div>

    <div class="slip-wrapper">

        <!-- =========================================================
             FORMAT 1: STANDARD SLIP (Original A4 / Half-page)
        ========================================================= -->
        <div class="container standard-slip">
            <!-- Header -->
            <div class="header">
                <div class="brand-section">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
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
                        @if($complaint->subCategory)
                            <tr>
                                <td class="label">Sub Category:</td>
                                <td class="value">{{ $complaint->subCategory->name }}</td>
                            </tr>
                        @endif
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
                            <td class="value">#{{ $complaint->id }}</td>
                        </tr>
                        <tr>
                            <td class="label">Priority:</td>
                            <td class="value">
                                <span class="badge {{ $isEmerg ? 'badge-urgent' : 'badge-medium' }}">
                                    {{ $isEmerg ? 'Emergency' : 'Normal' }}
                                </span>
                            </td>
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

                        <div class="comments-section">
                            <div class="comments-header">Comments / Manual Notes</div>
                            <div class="comments-box"></div>
                        </div>

                        <div class="signature-area">
                            <div class="sign-line">Technician Signature</div>
                            <div class="sign-line">Client Signature</div>
                        </div>
                    </div>

                    <!-- Digital Feedback QR -->
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


        <!-- =========================================================
             FORMAT 2: 80MM THERMAL POS RECEIPT (100% Complete Fields)
        ========================================================= -->
        <div class="container thermal-slip">
            <!-- Thermal Header -->
            <div class="thermal-header">
                <h2>MES Complaint System</h2>
                <div class="thermal-sub">Service Request Slip</div>
                <div class="thermal-time">Printed: {{ now()->timezone('Asia/Karachi')->format('M d, Y H:i') }}</div>
            </div>

            <!-- Assigned Tech Banner -->
            <div class="thermal-tech-banner">
                <div><strong>Assigned Tech:</strong> {{ $complaint->assignedEmployee->name ?? 'Unassigned' }} @if($complaint->assignedEmployee && $complaint->assignedEmployee->designation) ({{ $complaint->assignedEmployee->designation->name ?? $complaint->assignedEmployee->designation }}) @endif</div>
                @if($complaint->assignedEmployee && $complaint->assignedEmployee->phone)
                    <div style="margin-top: 2px;"><strong>Contact:</strong> {{ $complaint->assignedEmployee->phone }}</div>
                @endif
            </div>

            <!-- Complaint ID & Priority Banner -->
            <div class="thermal-id-banner">
                <span>COMPLAINT #{{ $complaint->id }}</span>
                <span class="thermal-badge">{{ $isEmerg ? 'EMERGENCY' : 'NORMAL' }}</span>
            </div>

            <!-- 1. Client Information Section -->
            <div class="thermal-sec-title">CLIENT INFORMATION</div>
            <table class="thermal-table">
                <tr>
                    <td class="thermal-label">House No:</td>
                    <td class="thermal-val">{{ $complaint->house->house_no ?? 'N/A' }}</td>
                </tr>
                @if($complaint->house && $complaint->house->phone)
                <tr>
                    <td class="thermal-label">Phone:</td>
                    <td class="thermal-val">{{ $complaint->house->phone }}</td>
                </tr>
                @endif
                @if($complaint->house && $complaint->house->address)
                <tr>
                    <td class="thermal-label">Address:</td>
                    <td class="thermal-val">{{ $complaint->house->address }}</td>
                </tr>
                @endif
                @if($complaint->city_id && $complaint->city)
                <tr>
                    <td class="thermal-label">GE Group:</td>
                    <td class="thermal-val">{{ $complaint->city->name }}</td>
                </tr>
                @endif
                @if($complaint->sector_id && $complaint->sector)
                <tr>
                    <td class="thermal-label">GE Node:</td>
                    <td class="thermal-val">{{ $complaint->sector->name }}</td>
                </tr>
                @endif
                <tr>
                    <td class="thermal-label">Nature/Type:</td>
                    <td class="thermal-val">{{ ucfirst($complaint->getCategoryDisplayAttribute()) . ' - ' . $complaint->getTitleDisplayAttribute() }}</td>
                </tr>
                @if($complaint->subCategory)
                <tr>
                    <td class="thermal-label">Sub Category:</td>
                    <td class="thermal-val">{{ $complaint->subCategory->name }}</td>
                </tr>
                @endif
                @if($registeredBy)
                <tr>
                    <td class="thermal-label">Registered By:</td>
                    <td class="thermal-val">{{ $registeredBy }}</td>
                </tr>
                @endif
            </table>

            <div class="thermal-divider"></div>

            <!-- 2. Request Details Section -->
            <div class="thermal-sec-title">REQUEST DETAILS</div>
            <table class="thermal-table">
                <tr>
                    <td class="thermal-label">Priority:</td>
                    <td class="thermal-val">{{ $isEmerg ? 'EMERGENCY' : 'NORMAL' }}</td>
                </tr>
                <tr>
                    <td class="thermal-label">Availability:</td>
                    <td class="thermal-val">{{ str_replace('T', ' ', $complaint->availability_time ?? 'N/A') }}</td>
                </tr>
                <tr>
                    <td class="thermal-label">Status:</td>
                    <td class="thermal-val">{{ ucwords(str_replace('_', ' ', $complaint->status === 'resolved' ? 'addressed' : $complaint->status)) }}</td>
                </tr>
                @if($statusChangedBy)
                <tr>
                    <td class="thermal-label">Changed By:</td>
                    <td class="thermal-val">{{ $statusChangedBy }}</td>
                </tr>
                @endif
                <tr>
                    <td class="thermal-label">Date:</td>
                    <td class="thermal-val">{{ $complaint->created_at->timezone('Asia/Karachi')->format('M d, Y H:i') }}</td>
                </tr>
            </table>

            <!-- Description -->
            <div style="font-weight: 700; font-size: 10px; margin-top: 4px;">Description:</div>
            <div class="thermal-desc-box">
                {{ $complaint->description ?: 'No detailed description provided.' }}
            </div>

            <div class="thermal-divider"></div>

            <!-- 3. Technician Remarks / Work Done -->
            <div class="thermal-sec-title">TECHNICIAN REMARKS / WORK DONE</div>
            <div class="thermal-work-done">
                <div class="thermal-work-line"></div>
                <div class="thermal-work-line"></div>
            </div>

            <!-- 4. Job Completion & Feedback -->
            <div class="thermal-feedback-block">
                <div class="thermal-feedback-title">Job Completion & Feedback</div>
                <div class="thermal-feedback-sub">To be filled by the client after job completion</div>
                
                <div class="thermal-ratings">
                    <span><span class="thermal-box"></span> Good</span>
                    <span><span class="thermal-box"></span> Satisfied</span>
                    <span><span class="thermal-box"></span> Poor</span>
                </div>

                <div style="font-size: 9px; font-weight: 700; text-align: left; text-transform: uppercase;">Comments / Manual Notes:</div>
                <div class="thermal-comments-box"></div>

                <div class="thermal-qr-container">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ route('frontend.feedback', $complaint->id) }}"
                        alt="Scan for Feedback">
                    <div class="thermal-qr-text">Scan for Digital Feedback</div>
                </div>

                <div class="thermal-signs">
                    <div class="thermal-sign-col">
                        <div class="thermal-sign-underline"></div>
                        <span>Technician Signature</span>
                    </div>
                    <div class="thermal-sign-col">
                        <div class="thermal-sign-underline"></div>
                        <span>Client Signature</span>
                    </div>
                </div>
            </div>

            <div class="thermal-footer">
                <div>System Generated Slip | {{ config('app.name') }}</div>
                <div>Printed by {{ auth()->user()->name ?? 'System' }}</div>
            </div>

            <div class="thermal-cut-mark">- - - - - - - - - - - - - - - -</div>
        </div>

    </div>

    <!-- Script to handle dynamic switching and persistence -->
    <script>
        const STORAGE_KEY = 'cms_slip_format';

        function setFormat(format, triggerPrint = false) {
            const body = document.body;
            const btnStandard = document.getElementById('btn-standard');
            const btnThermal = document.getElementById('btn-thermal');
            const statusHint = document.getElementById('memory-status');

            if (format === 'thermal') {
                body.classList.remove('show-standard');
                body.classList.add('show-thermal');
                if (btnThermal) btnThermal.classList.add('active');
                if (btnStandard) btnStandard.classList.remove('active');
                localStorage.setItem(STORAGE_KEY, 'thermal');
                if (statusHint) statusHint.innerText = '⚡ Thermal (80mm) saved';
            } else {
                body.classList.remove('show-thermal');
                body.classList.add('show-standard');
                if (btnStandard) btnStandard.classList.add('active');
                if (btnThermal) btnThermal.classList.remove('active');
                localStorage.setItem(STORAGE_KEY, 'standard');
                if (statusHint) statusHint.innerText = '⚡ Standard A4 saved';
            }

            if (triggerPrint) {
                setTimeout(function() {
                    window.print();
                }, 150);
            }
        }

        window.onload = function () {
            // Check URL param first, then localStorage
            const urlParams = new URLSearchParams(window.location.search);
            const paramFormat = urlParams.get('format');
            const savedFormat = localStorage.getItem(STORAGE_KEY);

            const activeFormat = paramFormat || savedFormat || 'standard';
            setFormat(activeFormat, false);

            // Auto-trigger print on page load
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
</body>

</html>