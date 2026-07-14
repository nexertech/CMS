<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Complaint Feedback - {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== RESET & BASE ===== */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f2f5;
            color: #1a1a2e;
            min-height: 100vh;
            padding: 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ===== CONTAINER ===== */
        .container {
            max-width: 540px;
            margin: 0 auto;
            width: 100%;
        }

        /* ===== CARD ===== */
        .card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 16px;
        }

        .card-body {
            padding: 20px;
        }

        /* ===== HEADER ===== */
        .page-header {
            text-align: center;
            padding: 24px 20px 16px;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            color: #ffffff;
            border-radius: 16px 16px 0 0;
        }

        .page-header .logo-icon {
            width: 40px;
            height: 40px;
            margin: 0 auto 10px;
            opacity: 0.9;
        }

        .page-header h1 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: -0.3px;
        }

        .page-header .subtitle {
            font-size: 13px;
            opacity: 0.75;
            font-weight: 400;
        }

        /* ===== STATUS SECTION ===== */
        .status-section {
            padding: 16px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .status-section .section-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            margin-bottom: 10px;
        }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        .status-badge .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* Status Colors */
        .status-new { background: #eff6ff; color: #1e40af; }
        .status-new .dot { background: #3b82f6; }
        .status-assigned { background: #fff7ed; color: #9a3412; }
        .status-assigned .dot { background: #f97316; }
        .status-in_progress { background: #ecfdf5; color: #166534; }
        .status-in_progress .dot { background: #22c55e; animation: pulse-dot 2s infinite; }
        .status-resolved { background: #f0fdf4; color: #15803d; }
        .status-resolved .dot { background: #16a34a; }
        .status-closed { background: #faf5ff; color: #6b21a8; }
        .status-closed .dot { background: #9333ea; }
        .status-work_performa, .status-maint_performa { background: #fffbeb; color: #92400e; }
        .status-work_performa .dot, .status-maint_performa .dot { background: #f59e0b; }
        .status-work_priced_performa, .status-maint_priced_performa { background: #fef3c7; color: #78350f; }
        .status-work_priced_performa .dot, .status-maint_priced_performa .dot { background: #d97706; }
        .status-product_na { background: #fef2f2; color: #991b1b; }
        .status-product_na .dot { background: #ef4444; }
        .status-un_authorized { background: #fef2f2; color: #b91c1c; }
        .status-un_authorized .dot { background: #dc2626; }
        .status-barrack_damages { background: #fff1f2; color: #9f1239; }
        .status-barrack_damages .dot { background: #e11d48; }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .complaint-id {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }

        /* ===== STATUS TIMELINE ===== */
        .timeline-info {
            display: flex;
            gap: 16px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .timeline-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #64748b;
        }

        .timeline-item svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
            stroke: #94a3b8;
        }

        .timeline-item strong {
            font-weight: 600;
            color: #334155;
        }

        /* ===== COMPLAINT DETAILS ===== */
        .details-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            gap: 12px;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
            white-space: nowrap;
            min-width: 80px;
        }

        .detail-value {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            text-align: right;
            word-break: break-word;
        }

        .priority-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-urgent, .priority-emergency { background: #fee2e2; color: #b91c1c; }
        .priority-high { background: #fef9c3; color: #a16207; }
        .priority-medium { background: #eff6ff; color: #1e40af; }
        .priority-low { background: #dcfce7; color: #15803d; }

        /* ===== ALERTS ===== */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-icon {
            font-size: 18px;
            flex-shrink: 0;
        }

        /* ===== ALREADY SUBMITTED ===== */
        .submitted-state {
            text-align: center;
            padding: 32px 20px;
        }

        .submitted-state .check-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .submitted-state h2 {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .submitted-state p {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
        }

        /* ===== FORM ===== */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .form-label .optional {
            font-weight: 400;
            color: #94a3b8;
            font-size: 11px;
        }

        .form-input,
        .form-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            color: #1e293b;
            background: #ffffff;
            transition: border-color 0.2s, box-shadow 0.2s;
            -webkit-appearance: none;
            appearance: none;
        }

        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .form-textarea {
            resize: vertical;
            min-height: 70px;
        }

        .form-error {
            font-size: 12px;
            color: #dc2626;
            margin-top: 4px;
        }

        /* ===== STAR RATING ===== */
        .rating-container {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px 16px;
            text-align: center;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
        }

        .star-rating input {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
        }

        .star-rating label {
            cursor: pointer;
            color: #d1d5db;
            transition: color 0.15s, transform 0.15s;
            -webkit-tap-highlight-color: transparent;
        }

        .star-rating label svg {
            width: 40px;
            height: 40px;
            fill: currentColor;
            display: block;
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #fbbf24;
        }

        .star-rating input:checked ~ label {
            color: #f59e0b;
        }

        .star-rating label:active {
            transform: scale(0.9);
        }

        .rating-text {
            font-size: 15px;
            font-weight: 700;
            color: #94a3b8;
            min-height: 24px;
            transition: color 0.2s;
        }

        .rating-text.excellent { color: #15803d; }
        .rating-text.good { color: #2563eb; }
        .rating-text.satisfied { color: #0ea5e9; }
        .rating-text.fair { color: #ca8a04; }
        .rating-text.poor { color: #b91c1c; }

        /* ===== SUBMIT BUTTON ===== */
        .btn-submit {
            display: block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.1s, box-shadow 0.2s;
            -webkit-appearance: none;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-submit:hover {
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.35);
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        /* ===== FOOTER ===== */
        .page-footer {
            text-align: center;
            padding: 16px 0;
            font-size: 11px;
            color: #94a3b8;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 400px) {
            body {
                padding: 10px;
            }

            .card-body {
                padding: 16px;
            }

            .page-header {
                padding: 20px 16px 14px;
            }

            .page-header h1 {
                font-size: 16px;
            }

            .star-rating label svg {
                width: 34px;
                height: 34px;
            }

            .star-rating {
                gap: 4px;
            }

            .complaint-id {
                font-size: 18px;
            }

            .status-badge {
                font-size: 11px;
                padding: 5px 10px;
            }

            .timeline-info {
                gap: 10px;
            }
        }

        @media (min-width: 541px) {
            body {
                padding: 24px 16px;
            }

            .page-header h1 {
                font-size: 20px;
            }

            .star-rating label svg {
                width: 46px;
                height: 46px;
            }

            .star-rating {
                gap: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <!-- Header -->
            <div class="page-header">
                <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                </svg>
                <h1>Complaint Feedback</h1>
                <p class="subtitle">{{ config('app.name') }}</p>
            </div>

            <!-- Current Status Section -->
            <div class="status-section">
                <div class="section-label">Current Status</div>
                <div class="status-row">
                    <span class="complaint-id">#{{ $complaint->id }}</span>
                    @php
                        $statusKey = strtolower($complaint->status);
                        $statusLabels = [
                            'new' => 'New',
                            'assigned' => 'Assigned',
                            'in_progress' => 'In Progress',
                            'resolved' => 'Addressed',
                            'closed' => 'Closed',
                            'work_performa' => 'Work Performa',
                            'maint_performa' => 'Maint Performa',
                            'work_priced_performa' => 'Work Priced Performa',
                            'maint_priced_performa' => 'Maint Priced Performa',
                            'product_na' => 'Product N/A',
                            'un_authorized' => 'Un-Authorized',
                            'barrack_damages' => 'Barrack Damages',
                        ];
                        $statusLabel = $statusLabels[$statusKey] ?? ucfirst(str_replace('_', ' ', $statusKey));
                    @endphp
                    <span class="status-badge status-{{ $statusKey }}">
                        <span class="dot"></span>
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="timeline-info">
                    <div class="timeline-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Filed: <strong>{{ $complaint->created_at->timezone('Asia/Karachi')->format('M d, Y') }}</strong></span>
                    </div>
                    @if($complaint->closed_at)
                    <div class="timeline-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <span>Resolved: <strong>{{ $complaint->closed_at->timezone('Asia/Karachi')->format('M d, Y') }}</strong></span>
                    </div>
                    @endif
                    @if($complaint->assignedEmployee)
                    <div class="timeline-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Technician: <strong>{{ $complaint->assignedEmployee->name }}</strong></span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <span class="alert-icon">✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">
                        <span class="alert-icon">❌</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if(isset($already_submitted) && $already_submitted)
                    <div class="submitted-state">
                        <div class="check-icon">✅</div>
                        <h2>Feedback Received</h2>
                        <p>Thank you! Your feedback has already been recorded for this complaint.</p>
                    </div>
                @elseif(!session('success'))

                    <!-- Complaint Details -->
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="detail-label">Nature & Type</span>
                            <span class="detail-value">{{ ucfirst($complaint->getCategoryDisplayAttribute()) }} - {{ $complaint->complaintTitle->title ?? $complaint->title ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Priority</span>
                            <span class="detail-value">
                                <span class="priority-badge priority-{{ strtolower($complaint->priority) }}">{{ ucfirst($complaint->priority) }}</span>
                            </span>
                        </div>
                        @if($complaint->house)
                        <div class="detail-item">
                            <span class="detail-label">House No</span>
                            <span class="detail-value">{{ $complaint->house->house_no ?? 'N/A' }}</span>
                        </div>
                        @endif
                        <div class="detail-item">
                            <span class="detail-label">Assigned To</span>
                            <span class="detail-value">{{ $complaint->assignedEmployee->name ?? 'Unassigned' }}</span>
                        </div>
                    </div>

                    @if(in_array($complaint->status, ['resolved', 'closed']))
                        <!-- Feedback Form -->
                        <form action="{{ route('frontend.feedback.submit', $complaint->id) }}" method="POST" style="margin-top: 20px;">
                            @csrf

                            <div class="form-group">
                                <label for="submitted_by" class="form-label">Your Name</label>
                                <input type="text" id="submitted_by" name="submitted_by" required
                                    value="{{ old('submitted_by', $complaint->house->name ?? '') }}"
                                    class="form-input"
                                    placeholder="Enter your name">
                                @error('submitted_by')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="text-align: center;">Overall Experience</label>
                                <div class="rating-container">
                                    <div class="star-rating" id="star-rating">
                                        <input type="radio" id="star5" name="overall_rating" value="excellent" required />
                                        <label for="star5" data-rating="excellent" data-text="Excellent">
                                            <svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        </label>

                                        <input type="radio" id="star4" name="overall_rating" value="good" />
                                        <label for="star4" data-rating="good" data-text="Good">
                                            <svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        </label>

                                        <input type="radio" id="star3" name="overall_rating" value="satisfied" />
                                        <label for="star3" data-rating="satisfied" data-text="Satisfied">
                                            <svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        </label>

                                        <input type="radio" id="star2" name="overall_rating" value="fair" />
                                        <label for="star2" data-rating="fair" data-text="Fair">
                                            <svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        </label>

                                        <input type="radio" id="star1" name="overall_rating" value="poor" />
                                        <label for="star1" data-rating="poor" data-text="Poor">
                                            <svg viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        </label>
                                    </div>
                                    <div class="rating-text" id="rating-label">Tap to Rate</div>
                                </div>
                                @error('overall_rating')
                                    <p class="form-error" style="text-align: center; margin-top: 6px;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="remarks" class="form-label">Technician Remarks <span class="optional">(Optional)</span></label>
                                <textarea id="remarks" name="remarks" rows="2" class="form-textarea"
                                    placeholder="Enter technician remarks..."></textarea>
                                @error('remarks')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="comments" class="form-label">Comments <span class="optional">(Optional)</span></label>
                                <textarea id="comments" name="comments" rows="3" class="form-textarea"
                                    placeholder="Any additional feedback..."></textarea>
                                @error('comments')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="btn-submit">
                                Submit Feedback
                            </button>
                        </form>
                    @else
                        <div class="submitted-state" style="padding: 24px 10px 10px; margin-top: 15px; border-top: 1.5px dashed #e2e8f0;">
                            <div class="check-icon" style="font-size: 44px; margin-bottom: 10px;">⏳</div>
                            <h2 style="font-size: 16px; font-weight: 700; color: #334155; margin-bottom: 6px;">Feedback Locked</h2>
                            <p style="font-size: 13px; color: #64748b; line-height: 1.5; max-width: 320px; margin: 0 auto;">You can submit feedback once this complaint is marked as <strong>Addressed (Resolved)</strong> by the Complaint centre.</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="page-footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var labels = document.querySelectorAll('#star-rating label');
            var ratingText = document.getElementById('rating-label');
            var inputs = document.querySelectorAll('#star-rating input');

            if (!ratingText || !labels.length) return;

            var colorMap = {
                'excellent': 'excellent',
                'good': 'good',
                'satisfied': 'satisfied',
                'fair': 'fair',
                'poor': 'poor'
            };

            function updateLabel(label) {
                var text = label.getAttribute('data-text');
                var rating = label.getAttribute('data-rating');
                ratingText.textContent = text;
                ratingText.className = 'rating-text ' + (colorMap[rating] || '');
            }

            function resetLabel() {
                var checkedInput = document.querySelector('#star-rating input:checked');
                if (checkedInput) {
                    var checkedLabel = document.querySelector('label[for="' + checkedInput.id + '"]');
                    updateLabel(checkedLabel);
                } else {
                    ratingText.textContent = 'Tap to Rate';
                    ratingText.className = 'rating-text';
                }
            }

            for (var i = 0; i < labels.length; i++) {
                labels[i].addEventListener('mouseenter', function() {
                    if (!document.querySelector('#star-rating input:checked')) {
                        ratingText.textContent = this.getAttribute('data-text');
                        ratingText.className = 'rating-text ' + (colorMap[this.getAttribute('data-rating')] || '');
                    }
                });

                labels[i].addEventListener('mouseleave', function() {
                    resetLabel();
                });
            }

            for (var j = 0; j < inputs.length; j++) {
                inputs[j].addEventListener('change', function() {
                    var label = document.querySelector('label[for="' + this.id + '"]');
                    updateLabel(label);
                });
            }
        });
    </script>
</body>

</html>