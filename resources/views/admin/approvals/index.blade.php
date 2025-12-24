@extends('layouts.sidebar')

@section('title', 'Approvals Management — CMS Admin')

@section('content')
  <!-- PAGE HEADER -->
  <div class="mb-2">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h4 class="text-white mb-1" style="font-size: 1.2rem;">Total Complaints</h4>
        <p class="text-light small mb-0" style="font-size: 0.8rem;">View and manage complaint records</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" onclick="refreshPage()" style="padding: 0.25rem 0.6rem; font-size: 0.8rem;">
          <i data-feather="refresh-cw" class="me-1" style="width: 14px; height: 14px;"></i>Refresh
        </button>
      </div>
    </div>
  </div>

  <!-- FILTERS -->
  <div class="card-glass mb-2" style="display: inline-block; width: fit-content; padding: 0.75rem;">
    <form id="approvalsFiltersForm" method="GET" action="{{ route('admin.approvals.index') }}"
      onsubmit="event.preventDefault(); submitApprovalsFilters(event); return false;">
      <div class="row g-2 align-items-end">
        <div class="col-auto">
          <label class="form-label small mb-1"
            style="font-size: 0.75rem; color: #000000 !important; font-weight: 500;">Search</label>
          <input type="text" class="form-control form-control-sm" id="searchInput" name="search" placeholder="Complaint ID..."
            value="{{ request('search') }}" autocomplete="off" style="font-size: 0.8rem; width: 160px; height: 30px;">
        </div>
        <div class="col-auto">
          <label class="form-label small mb-1"
            style="font-size: 0.75rem; color: #000000 !important; font-weight: 500;">From Date</label>
          <input type="date" class="form-control form-control-sm" name="complaint_date" value="{{ request('complaint_date') }}"
            placeholder="Select Date" autocomplete="off" style="font-size: 0.8rem; width: 130px; height: 30px;">
        </div>
        <div class="col-auto">
          <label class="form-label small mb-1" style="font-size: 0.75rem; color: #000000 !important; font-weight: 500;">To
            Date</label>
          <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to') }}" placeholder="End Date"
            autocomplete="off" style="font-size: 0.8rem; width: 130px; height: 30px;">
        </div>
        <div class="col-auto">
          <label class="form-label small mb-1"
            style="font-size: 0.75rem; color: #000000 !important; font-weight: 500;">Category</label>
          <select class="form-select form-select-sm" name="category" autocomplete="off" style="font-size: 0.8rem; width: 120px; height: 30px;">
            <option value="" {{ request('category') ? '' : 'selected' }}>All</option>
            @if(isset($categories) && $categories->count() > 0)
              @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
              @endforeach
            @else
              <option value="electric">Electric</option>
              <option value="technical">Technical</option>
              <option value="service">Service</option>
              <option value="billing">Billing</option>
              <option value="water">Water Supply</option>
              <option value="sanitary">Sanitary</option>
              <option value="plumbing">Plumbing</option>
              <option value="kitchen">Kitchen</option>
              <option value="other">Other</option>
            @endif
          </select>
        </div>
        <div class="col-auto">
          <label class="form-label small mb-1"
            style="font-size: 0.75rem; color: #000000 !important; font-weight: 500;">Status</label>
          <select class="form-select form-select-sm" name="status" autocomplete="off" onchange="submitApprovalsFilters()"
            style="font-size: 0.8rem; width: 150px; height: 30px;">
            <option value="" {{ request('status') ? '' : 'selected' }}>All</option>
            @if(isset($statusesForFilter) && $statusesForFilter->count() > 0)
              @foreach($statusesForFilter as $statusValue => $statusLabel)
                @if(!empty($statusValue) && !empty($statusLabel))
                  <option value="{{ $statusValue }}" {{ request('status') == $statusValue ? 'selected' : '' }}>{{ $statusLabel }}
                  </option>
                @endif
              @endforeach
            @elseif(isset($statuses) && $statuses->count() > 0)
              @foreach($statuses as $statusValue => $statusLabel)
                @if(!empty($statusValue) && !empty($statusLabel))
                  <option value="{{ $statusValue }}" {{ request('status') == $statusValue ? 'selected' : '' }}>{{ $statusLabel }}
                  </option>
                @endif
              @endforeach
            @else
              <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
              <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
              <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Addressed</option>
              <option value="work_performa" {{ request('status') == 'work_performa' ? 'selected' : '' }}>Work Performa
              </option>
              <option value="maint_performa" {{ request('status') == 'maint_performa' ? 'selected' : '' }}>Maintenance
                Performa</option>
              <option value="work_priced_performa" {{ request('status') == 'work_priced_performa' ? 'selected' : '' }}>Work
                Performa Priced</option>
              <option value="maint_priced_performa" {{ request('status') == 'maint_priced_performa' ? 'selected' : '' }}>
                Maintenance Performa Priced</option>
              <option value="product_na" {{ request('status') == 'product_na' ? 'selected' : '' }}>Product N/A</option>
              <option value="un_authorized" {{ request('status') == 'un_authorized' ? 'selected' : '' }}>Un-Authorized
              </option>
              <option value="pertains_to_ge_const_isld" {{ request('status') == 'pertains_to_ge_const_isld' ? 'selected' : '' }}>Pertains to GE(N) Const Isld</option>
              <option value="barak_damages" {{ request('status') == 'barak_damages' ? 'selected' : '' }}>Barak Damages
              </option>
              <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
              <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            @endif
          </select>
        </div>
        <div class="col-auto">
          <label class="form-label small text-muted mb-1" style="font-size: 0.75rem;">&nbsp;</label>
          <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetApprovalsFilters()"
            style="font-size: 0.8rem; padding: 0.2rem 0.6rem; height: 30px; display: flex; align-items: center;">
            <i data-feather="refresh-cw" class="me-1" style="width: 12px; height: 12px;"></i>Reset
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- APPROVALS TABLE -->

  <div class="card-glass p-2">
    <div class="table-responsive">
      <table class="table table-dark table-sm mb-0" style="font-size: 0.75rem;">
        <thead>
          <tr style="font-size: 0.7rem; text-transform: uppercase;">
            <th style="text-align: left; white-space: nowrap; padding: 2px 4px; width: 1%;">CMP-ID</th>
            <th style="white-space: nowrap; padding: 2px 4px; width: 1%;">Reg Date</th>
            <th style="text-align: left; white-space: nowrap; padding: 2px 4px; width: 1%;">Addressed Date</th>
            <th style="white-space: nowrap; padding: 2px 4px; width: 1%;">Name</th>
            <th style="white-space: nowrap; padding: 2px 4px; width: 1%;">Address</th>
            <th style="padding: 2px 4px; width: auto;">Nature & Type</th>
            <th style="white-space: nowrap; padding: 2px 4px; width: 1%;">Phone</th>
            <th style="text-align: center; white-space: nowrap; padding: 2px 4px; width: 1%;">Performa</th>
            <th style="text-align: center; white-space: nowrap; padding: 2px 4px; width: 1%;">Status</th>
            <th style="padding: 2px 4px; width: 95px; white-space: nowrap; text-align: center;">Actions|Feedback</th>
          </tr>
        </thead>
        <tbody id="approvalsTableBody">
          @forelse($approvals as $approval)
            @php
              $complaint = $approval->complaint ?? null;
            @endphp
            @if($complaint)
              @php
                $category = $complaint->category ?? 'N/A';
                $designation = $complaint->assignedEmployee->designation ?? 'N/A';
                // Use original complaint category as-is, don't change it
                // Only format basic categories, keep all other categories as they are
                $categoryDisplay = [
                  'electric' => 'Electric',
                  'technical' => 'Technical',
                  'service' => 'Service',
                  'billing' => 'Billing',
                  'water' => 'Water Supply',
                  'sanitary' => 'Sanitary',
                  'plumbing' => 'Plumbing',
                  'kitchen' => 'Kitchen',
                  'other' => 'Other',
                ];
                // If category exists in mapping, use it; otherwise use original category as-is
                $catDisplay = $categoryDisplay[strtolower($category)] ?? $category;
                $displayText = $catDisplay . ' - ' . $designation;

                // Logic: If performa_type is set, use it as status, otherwise use complaint status
                $rawStatus = $complaint->status ?? 'new';

                // Check if approval has performa_type set (same logic as product_na)
                $hasPerformaType = isset($approval->performa_type) && $approval->performa_type;
                $performaTypeValue = $hasPerformaType ? $approval->performa_type : null;

                // For status column display: if performa_type is set or status is a performa type, show "In Progress" (like product_na)
                // Exception: If status is resolved, always use resolved (don't override)
                // Otherwise use complaint status
                if ($rawStatus === 'resolved' || $rawStatus === 'closed') {
                  // Always preserve resolved/closed status - don't override with performa type
                  $complaintStatus = ($rawStatus == 'new') ? 'assigned' : $rawStatus;
                } elseif ($hasPerformaType && in_array($performaTypeValue, ['product_na', 'work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa'])) {
                  // For all performa types, use in_progress for display (like product_na)
                  $complaintStatus = 'in_progress';
                } elseif (in_array($rawStatus, ['work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa', 'product_na'])) {
                  // If complaint status is a performa type, show "In Progress" in status column
                  $complaintStatus = 'in_progress';
                } else {
                  $complaintStatus = ($rawStatus == 'new') ? 'assigned' : $rawStatus;
                }

                $statusDisplay = $complaintStatus == 'in_progress' ? 'In Progress' :
                  ($complaintStatus == 'resolved' ? 'Addressed' :
                    ucfirst(str_replace('_', ' ', $complaintStatus)));

                // Status colors mapping
                // Performa column colors (original colors for badges)
                $performaColors = [
                  'work_performa' => ['bg' => '#60a5fa', 'text' => '#ffffff', 'border' => '#3b82f6'], // Light Blue
                  'maint_performa' => ['bg' => '#eab308', 'text' => '#ffffff', 'border' => '#ca8a04'], // Dark Yellow
                  'work_priced_performa' => ['bg' => '#9333ea', 'text' => '#ffffff', 'border' => '#7e22ce'], // Purple
                  'maint_priced_performa' => ['bg' => '#ea580c', 'text' => '#ffffff', 'border' => '#c2410c'], // Dark Orange
                  'product_na' => ['bg' => '#f97316', 'text' => '#ffffff', 'border' => '#c2410c'], // Orange
                ];

                // Status column colors (updated to match dashboard stat cards)
                $statusColors = [
                  'in_progress' => ['bg' => '#dc2626', 'text' => '#ffffff', 'border' => '#b91c1c'], // Red
                  'resolved' => ['bg' => '#64748b', 'text' => '#ffffff', 'border' => '#475569'], // Grey (swapped from green)
                  'work_performa' => ['bg' => '#60a5fa', 'text' => '#ffffff', 'border' => '#3b82f6'], // Light Blue (matching badge)
                  'maint_performa' => ['bg' => '#eab308', 'text' => '#ffffff', 'border' => '#ca8a04'], // Dark Yellow (matching badge)
                  'work_priced_performa' => ['bg' => '#9333ea', 'text' => '#ffffff', 'border' => '#7e22ce'], // Purple (matching badge)
                  'maint_priced_performa' => ['bg' => '#ea580c', 'text' => '#ffffff', 'border' => '#c2410c'], // Dark Orange (matching badge)
                  'product_na' => ['bg' => '#f97316', 'text' => '#ffffff', 'border' => '#c2410c'], // Orange (for status column)
                  'un_authorized' => ['bg' => '#ec4899', 'text' => '#ffffff', 'border' => '#db2777'], // Pink
                  'pertains_to_ge_const_isld' => ['bg' => '#06b6d4', 'text' => '#ffffff', 'border' => '#0891b2'], // Aqua/Cyan
                  'barak_damages' => ['bg' => '#808000', 'text' => '#ffffff', 'border' => '#666600'], // Olive (matching Users card)
                  'assigned' => ['bg' => '#16a34a', 'text' => '#ffffff', 'border' => '#15803d'], // Green (swapped from grey)
                ];

                // Get current status color or default
                $currentStatusColor = $statusColors[$complaintStatus] ?? $statusColors['assigned'];
              @endphp
              <tr style="position: relative;">
                {{-- waiting_for_authority removed - no blinking dot needed --}}
                <td
                  style="text-align: left !important; direction: ltr !important; justify-content: flex-start !important; align-items: flex-start !important;">
                  <a href="{{ route('admin.complaints.show', $complaint->id) }}" class="text-decoration-none"
                    style="color: #3b82f6; text-align: left !important; display: inline-block !important; direction: ltr !important; float: none !important; margin: 0 !important; width: auto !important;">
                    {{ (int) ($complaint->complaint_id ?? $complaint->id) }}
                  </a>
                </td>
                <td class="px-1" style="font-size: 0.8rem;">
                  {{ $complaint->created_at ? $complaint->created_at->timezone('Asia/Karachi')->format('M d, y H:i') : 'N/A' }}
                </td>
                <td class="px-1" style="text-align: left; font-size: 0.75rem;">
                  @if($complaint->closed_at)
                    @php
                      $closedAt = $complaint->closed_at;
                      if ($closedAt instanceof \Carbon\Carbon) {
                        $closedAtKarachi = $closedAt->utc()->setTimezone('Asia/Karachi');
                        echo $closedAtKarachi->format('M d, y H:i');
                      }
                    @endphp
                  @else
                    <span style="display: block; text-align: center;">-</span>
                  @endif
                </td>
                <td class="px-1" style="font-size: 0.75rem; white-space: nowrap; width: 1%;">{{ $complaint->client->client_name ?? 'N/A' }}</td>
                <td class="px-1" style="font-size: 0.73rem; white-space: nowrap; width: 1%; max-width: 150px; overflow: hidden; text-overflow: ellipsis;" title="{{ $complaint->client->address ?? 'N/A' }}">{{ $complaint->client->address ?? 'N/A' }}</td>
                <td class="px-1" style="width: auto;">
                  <div class="text-white" style="font-size: 0.75rem; line-height: 1.1; white-space: normal; min-width: 180px;">{{ $displayText }}</div>
                </td>
                <td class="px-1" style="font-size: 0.75rem; white-space: nowrap; width: 1%;">{{ $complaint->client->phone ?? 'N/A' }}</td>
                <td style="color: white !important; position: relative; text-align: center; vertical-align: middle;">
                  @if($complaintStatus == 'resolved' || $complaintStatus == 'closed')
                    <span style="color: white !important;">-</span>
                  @else
                    @php
                      // Check if we should show performa badge - either from approval performa_type or complaint status
                      $performaTypeToShow = null;
                      if (isset($approval->performa_type) && $approval->performa_type) {
                        $performaTypeToShow = $approval->performa_type;
                      } elseif (in_array($rawStatus, ['work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa', 'product_na'])) {
                        // If complaint status is a performa type, show badge even if performa_type not set
                        $performaTypeToShow = $rawStatus;
                      }

                      if ($performaTypeToShow) {
                        // Handle special cases for performa type labels
                        if ($performaTypeToShow === 'maint_performa') {
                          $performaTypeLabel = 'Maintenance Performa';
                        } elseif ($performaTypeToShow === 'product_na') {
                          $performaTypeLabel = 'Product N/A';
                        } else {
                          $performaTypeLabel = ucwords(str_replace('_', ' ', $performaTypeToShow));
                        }
                        // Use performa column colors (original colors) for performa badge
                        $badgeColor = $performaColors[$performaTypeToShow]['bg'] ?? $performaColors['work_performa']['bg'];
                      }
                    @endphp
                    @if($performaTypeToShow)
                      <span class="badge performa-badge"
                        style="width: 100px; height: 24px; padding: 0; font-weight: 700; color: white !important; background-color: {{ $badgeColor }} !important; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; font-size: 9px;">
                        {{ $performaTypeLabel }}
                      </span>
                    @else
                      <span class="badge performa-badge"
                        style="display:none; width: 140px; height: 32px; padding: 0; font-weight: 700; color: white !important; border-radius: 6px;"></span>
                    @endif
                  @endif
                </td>
                <td style="text-align: center;">
                  @php
                    // Get product and price information
                    $complaintSpare = $complaint->spareParts->first();
                    $pricePerForma = 'N/A';

                    if ($complaintSpare && $complaintSpare->spare && $complaintSpare->spare->unit_price) {
                      $pricePerForma = 'PKR ' . number_format($complaintSpare->spare->unit_price, 2);
                    }
                  @endphp
                  @if($complaintStatus == 'resolved')
                    <div class="status-chip"
                      style="background-color: {{ $statusColors['resolved']['bg'] }}; color: {{ $statusColors['resolved']['text'] }}; border-color: {{ $statusColors['resolved']['border'] }}; width: 100px; height: 24px; justify-content: center;">
                      <span style="font-size: 9px; font-weight: 700; color: white !important;">Addressed</span>
                    </div>
                  @elseif($complaintStatus == 'in_progress' || ($hasPerformaType && in_array($performaTypeValue, ['product_na', 'work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa']) && $complaintStatus != 'resolved') || in_array($rawStatus, ['work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa', 'product_na']))
                    @php
                      // waiting_for_authority removed - no dot needed
                      // For all performa types (including priced ones), show "In Progress" in status column with red color
                      // But keep the actual status value selected in dropdown for persistence
                      if (in_array($rawStatus, ['work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa', 'product_na'])) {
                        // Show "In Progress" in dropdown for all performa types (like work_performa)
                        // Actual value is preserved in data-actual-status attribute
                        $displayStatusForSelect = 'in_progress'; // Show "In Progress" in dropdown
                        $statusColorKey = 'in_progress'; // Always use red color for performa types in status column
                        $currentStatusColorForSelect = $statusColors['in_progress'];
                      } elseif ($hasPerformaType && in_array($performaTypeValue, ['product_na', 'work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa'])) {
                        // For performa types from approval, show "In Progress" in status column
                        // If performa_type matches rawStatus, use rawStatus, otherwise use in_progress
                        if ($performaTypeValue === $rawStatus) {
                          $displayStatusForSelect = $rawStatus;
                        } else {
                          $displayStatusForSelect = 'in_progress';
                        }
                        $statusColorKey = 'in_progress'; // Always use red color for performa types in status column
                        $currentStatusColorForSelect = $statusColors['in_progress'];
                      } else {
                        $displayStatusForSelect = $complaintStatus; // Use complaint status
                        $statusColorKey = 'in_progress';
                        $currentStatusColorForSelect = $statusColors['in_progress'];
                      }
                    @endphp
                    <div class="status-chip"
                      style="background-color: {{ $currentStatusColorForSelect['bg'] }}; color: {{ $currentStatusColorForSelect['text'] }}; border-color: {{ $currentStatusColorForSelect['border'] }}; position: relative; overflow: hidden; height: 24px; width: 100px;">
                      {{-- waiting_for_authority removed - no blinking dot needed --}}
                      {{-- status-indicator removed for performa types to avoid extra red line --}}
                      <select class="form-select form-select-sm status-select" data-complaint-id="{{ $complaint->id }}"
                        data-actual-status="{{ $rawStatus }}" data-status-color="{{ $statusColorKey }}"
                        style="width: 100px; font-size: 9px; font-weight: 700; height: 24px; text-align: center; text-align-last: center; background-color: {{ $currentStatusColorForSelect['bg'] }} !important; color: {{ $currentStatusColorForSelect['text'] }} !important; border-color: {{ $currentStatusColorForSelect['border'] }} !important; padding: 0;">
                        @if(isset($statuses) && $statuses->count() > 0)
                          @foreach($statuses as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" {{ $displayStatusForSelect == $statusValue ? 'selected' : '' }}>
                              {{ $statusLabel }}
                            </option>
                          @endforeach
                        @else
                          <option value="assigned" {{ $displayStatusForSelect == 'assigned' ? 'selected' : '' }}>Assigned</option>
                          <option value="in_progress" {{ $displayStatusForSelect == 'in_progress' ? 'selected' : '' }}>In Progress
                          </option>
                          <option value="resolved" {{ $displayStatusForSelect == 'resolved' ? 'selected' : '' }}>Addressed</option>
                          <option value="work_performa" {{ $displayStatusForSelect == 'work_performa' ? 'selected' : '' }}>Work
                            Performa</option>
                          <option value="maint_performa" {{ $displayStatusForSelect == 'maint_performa' ? 'selected' : '' }}>
                            Maintenance Performa</option>
                          <option value="work_priced_performa" {{ $displayStatusForSelect == 'work_priced_performa' ? 'selected' : '' }}>Work Performa Priced</option>
                          <option value="maint_priced_performa" {{ $displayStatusForSelect == 'maint_priced_performa' ? 'selected' : '' }}>Maintenance Performa Priced</option>
                          <option value="product_na" {{ $displayStatusForSelect == 'product_na' ? 'selected' : '' }}>Product N/A
                          </option>
                          <option value="un_authorized" {{ $displayStatusForSelect == 'un_authorized' ? 'selected' : '' }}>
                            Un-Authorized</option>
                          <option value="pertains_to_ge_const_isld" {{ $displayStatusForSelect == 'pertains_to_ge_const_isld' ? 'selected' : '' }}>Pertains to GE(N) Const Isld</option>
                          <option value="barak_damages" {{ $displayStatusForSelect == 'barak_damages' ? 'selected' : '' }}>Barak
                            Damages</option>
                        @endif
                      </select>
                      <i data-feather="chevron-down"
                        style="width: 14px; height: 14px; color: #ffffff !important; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 10; stroke: #ffffff;"></i>
                    </div>
                  @elseif(($complaintStatus == 'work_performa' || (isset($performaBadge) && strpos($performaBadge ?? '', 'Work') !== false)) && !$hasPerformaType)
                    <div class="status-chip"
                      style="background-color: {{ $statusColors['work_performa']['bg'] }}; color: {{ $statusColors['work_performa']['text'] }}; border-color: {{ $statusColors['work_performa']['border'] }}; position: relative; overflow: hidden; height: 24px; width: 100px;">
                      <span class="status-indicator"
                        style="background-color: {{ $statusColors['work_performa']['bg'] }}; border-color: {{ $statusColors['work_performa']['border'] }};"></span>
                      <select class="form-select form-select-sm status-select" data-complaint-id="{{ $complaint->id }}"
                        data-actual-status="{{ $rawStatus }}" data-status-color="work_performa"
                        style="width: 100px; font-size: 9px; font-weight: 700; height: 24px; text-align: center; text-align-last: center; padding: 0;">
                        @if(isset($statuses) && $statuses->count() > 0)
                          @foreach($statuses as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" {{ $complaintStatus == $statusValue ? 'selected' : '' }}>
                              {{ $statusLabel }}
                            </option>
                          @endforeach
                        @else
                          <option value="assigned" {{ $complaintStatus == 'assigned' ? 'selected' : '' }}>Assigned</option>
                          <option value="in_progress" {{ $complaintStatus == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                          <option value="resolved" {{ $complaintStatus == 'resolved' ? 'selected' : '' }}>Addressed</option>
                          <option value="work_priced_performa" {{ $complaintStatus == 'work_priced_performa' ? 'selected' : '' }}>
                            Work Performa Priced</option>
                          <option value="maint_priced_performa" {{ $complaintStatus == 'maint_priced_performa' ? 'selected' : '' }}>
                            Maintenance Performa Priced</option>
                          <option value="product_na" {{ $complaintStatus == 'product_na' ? 'selected' : '' }}>Product N/A</option>
                          <option value="un_authorized" {{ $complaintStatus == 'un_authorized' ? 'selected' : '' }}>Un-Authorized
                          </option>
                          <option value="pertains_to_ge_const_isld" {{ $complaintStatus == 'pertains_to_ge_const_isld' ? 'selected' : '' }}>Pertains to GE(N) Const Isld</option>
                          <option value="barak_damages" {{ $complaintStatus == 'barak_damages' ? 'selected' : '' }}>Barak Damages
                          </option>
                        @endif
                      </select>
                      <i data-feather="chevron-down"
                        style="width: 14px; height: 14px; color: #ffffff !important; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 10; stroke: #ffffff;"></i>
                    </div>
                  @elseif(($complaintStatus == 'maint_performa' || (isset($performaBadge) && (strpos($performaBadge ?? '', 'Maint') !== false || strpos($performaBadge ?? '', 'Maintenance') !== false))) && !$hasPerformaType && !in_array($performaTypeValue, ['work_priced_performa', 'maint_priced_performa']))
                    <div class="status-chip"
                      style="background-color: {{ $statusColors['maint_performa']['bg'] }}; color: {{ $statusColors['maint_performa']['text'] }}; border-color: {{ $statusColors['maint_performa']['border'] }}; position: relative; overflow: hidden; height: 24px; width: 100px;">
                      <span class="status-indicator"
                        style="background-color: {{ $statusColors['maint_performa']['bg'] }}; border-color: {{ $statusColors['maint_performa']['border'] }};"></span>
                      <select class="form-select form-select-sm status-select" data-complaint-id="{{ $complaint->id }}"
                        data-actual-status="{{ $rawStatus }}" data-status-color="maint_performa"
                        style="width: 100px; font-size: 9px; font-weight: 700; height: 24px; text-align: center; text-align-last: center; padding: 0;">
                        @if(isset($statuses) && $statuses->count() > 0)
                          @foreach($statuses as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" {{ $complaintStatus == $statusValue ? 'selected' : '' }}>
                              {{ $statusLabel }}
                            </option>
                          @endforeach
                        @else
                          <option value="assigned" {{ $complaintStatus == 'assigned' ? 'selected' : '' }}>Assigned</option>
                          <option value="in_progress" {{ $complaintStatus == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                          <option value="resolved" {{ $complaintStatus == 'resolved' ? 'selected' : '' }}>Addressed</option>
                          <option value="work_priced_performa" {{ $complaintStatus == 'work_priced_performa' ? 'selected' : '' }}>
                            Work Performa Priced</option>
                          <option value="maint_priced_performa" {{ $complaintStatus == 'maint_priced_performa' ? 'selected' : '' }}>
                            Maintenance Performa Priced</option>
                          <option value="product_na" {{ $complaintStatus == 'product_na' ? 'selected' : '' }}>Product N/A</option>
                          <option value="un_authorized" {{ $complaintStatus == 'un_authorized' ? 'selected' : '' }}>Un-Authorized
                          </option>
                          <option value="pertains_to_ge_const_isld" {{ $complaintStatus == 'pertains_to_ge_const_isld' ? 'selected' : '' }}>Pertains to GE(N) Const Isld</option>
                        @endif
                      </select>
                      <i data-feather="chevron-down"
                        style="width: 14px; height: 14px; color: #ffffff !important; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 10; stroke: #ffffff;"></i>
                    </div>
                  @elseif($complaintStatus == 'un_authorized')
                    <div class="status-chip"
                      style="background-color: {{ $statusColors['un_authorized']['bg'] }}; color: {{ $statusColors['un_authorized']['text'] }}; border-color: {{ $statusColors['un_authorized']['border'] }}; position: relative; overflow: hidden; height: 24px; width: 100px;">
                      <span class="status-indicator"
                        style="background-color: {{ $statusColors['un_authorized']['bg'] }}; border-color: {{ $statusColors['un_authorized']['border'] }};"></span>
                      <select class="form-select form-select-sm status-select" data-complaint-id="{{ $complaint->id }}"
                        data-actual-status="{{ $rawStatus }}" data-status-color="un_authorized"
                        style="width: 100px; font-size: 9px; font-weight: 700; height: 24px; text-align: center; text-align-last: center; padding: 0;">
                        @if(isset($statuses) && $statuses->count() > 0)
                          @foreach($statuses as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" {{ $complaintStatus == $statusValue ? 'selected' : '' }}>
                              {{ $statusLabel }}
                            </option>
                          @endforeach
                        @else
                          <option value="assigned" {{ $complaintStatus == 'assigned' ? 'selected' : '' }}>Assigned</option>
                          <option value="in_progress" {{ $complaintStatus == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                          <option value="resolved" {{ $complaintStatus == 'resolved' ? 'selected' : '' }}>Addressed</option>
                          <option value="work_priced_performa" {{ $complaintStatus == 'work_priced_performa' ? 'selected' : '' }}>
                            Work Performa Priced</option>
                          <option value="maint_priced_performa" {{ $complaintStatus == 'maint_priced_performa' ? 'selected' : '' }}>
                            Maintenance Performa Priced</option>
                          <option value="product_na" {{ $complaintStatus == 'product_na' ? 'selected' : '' }}>Product N/A</option>
                          <option value="un_authorized" {{ $complaintStatus == 'un_authorized' ? 'selected' : '' }}>Un-Authorized
                          </option>
                          <option value="pertains_to_ge_const_isld" {{ $complaintStatus == 'pertains_to_ge_const_isld' ? 'selected' : '' }}>Pertains to GE(N) Const Isld</option>
                          <option value="barak_damages" {{ $complaintStatus == 'barak_damages' ? 'selected' : '' }}>Barak Damages
                          </option>
                        @endif
                      </select>
                      <i data-feather="chevron-down"
                        style="width: 14px; height: 14px; color: #ffffff !important; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 10; stroke: #ffffff;"></i>
                    </div>
                  @elseif($complaintStatus == 'pertains_to_ge_const_isld')
                    <div class="status-chip"
                      style="background-color: {{ $statusColors['pertains_to_ge_const_isld']['bg'] }}; color: {{ $statusColors['pertains_to_ge_const_isld']['text'] }}; border-color: {{ $statusColors['pertains_to_ge_const_isld']['border'] }}; position: relative; overflow: hidden; height: 24px; width: 100px;">
                      <span class="status-indicator"
                        style="background-color: {{ $statusColors['pertains_to_ge_const_isld']['bg'] }}; border-color: {{ $statusColors['pertains_to_ge_const_isld']['border'] }};"></span>
                      <select class="form-select form-select-sm status-select" data-complaint-id="{{ $complaint->id }}"
                        data-actual-status="{{ $rawStatus }}" data-status-color="pertains_to_ge_const_isld"
                        style="width: 100px; font-size: 9px; font-weight: 700; height: 24px; text-align: center; text-align-last: center; padding: 0;">
                        @if(isset($statuses) && $statuses->count() > 0)
                          @foreach($statuses as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" {{ $complaintStatus == $statusValue ? 'selected' : '' }}>
                              {{ $statusLabel }}
                            </option>
                          @endforeach
                        @else
                          <option value="assigned" {{ $complaintStatus == 'assigned' ? 'selected' : '' }}>Assigned</option>
                          <option value="in_progress" {{ $complaintStatus == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                          <option value="resolved" {{ $complaintStatus == 'resolved' ? 'selected' : '' }}>Addressed</option>
                          <option value="work_priced_performa" {{ $complaintStatus == 'work_priced_performa' ? 'selected' : '' }}>
                            Work Performa Priced</option>
                          <option value="maint_priced_performa" {{ $complaintStatus == 'maint_priced_performa' ? 'selected' : '' }}>
                            Maintenance Performa Priced</option>
                          <option value="product_na" {{ $complaintStatus == 'product_na' ? 'selected' : '' }}>Product N/A</option>
                          <option value="un_authorized" {{ $complaintStatus == 'un_authorized' ? 'selected' : '' }}>Un-Authorized
                          </option>
                          <option value="pertains_to_ge_const_isld" {{ $complaintStatus == 'pertains_to_ge_const_isld' ? 'selected' : '' }}>Pertains to GE(N) Const Isld</option>
                          <option value="barak_damages" {{ $complaintStatus == 'barak_damages' ? 'selected' : '' }}>Barak Damages
                          </option>
                        @endif
                      </select>
                      <i data-feather="chevron-down"
                        style="width: 14px; height: 14px; color: #ffffff !important; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 10; stroke: #ffffff;"></i>
                    </div>
                  @elseif($complaintStatus == 'barak_damages')
                    <div class="status-chip"
                      style="background-color: {{ $statusColors['barak_damages']['bg'] }}; color: {{ $statusColors['barak_damages']['text'] }}; border-color: {{ $statusColors['barak_damages']['border'] }}; position: relative; overflow: hidden; height: 24px; width: 100px;">
                      <span class="status-indicator"
                        style="background-color: {{ $statusColors['barak_damages']['bg'] }}; border-color: {{ $statusColors['barak_damages']['border'] }};"></span>
                      <select class="form-select form-select-sm status-select" data-complaint-id="{{ $complaint->id }}"
                        data-actual-status="{{ $rawStatus }}" data-status-color="barak_damages"
                        style="width: 100px; font-size: 9px; font-weight: 700; height: 24px; text-align: center; text-align-last: center; padding: 0;">
                        @if(isset($statuses) && $statuses->count() > 0)
                          @foreach($statuses as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" {{ $complaintStatus == $statusValue ? 'selected' : '' }}>
                              {{ $statusLabel }}
                            </option>
                          @endforeach
                        @else
                          <option value="assigned" {{ $complaintStatus == 'assigned' ? 'selected' : '' }}>Assigned</option>
                          <option value="in_progress" {{ $complaintStatus == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                          <option value="resolved" {{ $complaintStatus == 'resolved' ? 'selected' : '' }}>Addressed</option>
                          <option value="work_priced_performa" {{ $complaintStatus == 'work_priced_performa' ? 'selected' : '' }}>
                            Work Performa Priced</option>
                          <option value="maint_priced_performa" {{ $complaintStatus == 'maint_priced_performa' ? 'selected' : '' }}>
                            Maintenance Performa Priced</option>
                          <option value="product_na" {{ $complaintStatus == 'product_na' ? 'selected' : '' }}>Product N/A</option>
                          <option value="un_authorized" {{ $complaintStatus == 'un_authorized' ? 'selected' : '' }}>Un-Authorized
                          </option>
                          <option value="pertains_to_ge_const_isld" {{ $complaintStatus == 'pertains_to_ge_const_isld' ? 'selected' : '' }}>Pertains to GE(N) Const Isld</option>
                          <option value="barak_damages" {{ $complaintStatus == 'barak_damages' ? 'selected' : '' }}>Barak Damages
                          </option>
                        @endif
                      </select>
                      <i data-feather="chevron-down"
                        style="width: 14px; height: 14px; color: #ffffff !important; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 10; stroke: #ffffff;"></i>
                    </div>
                  @else
                    <div class="status-chip"
                      style="background-color: {{ $statusColors['assigned']['bg'] }}; color: {{ $statusColors['assigned']['text'] }}; border-color: {{ $statusColors['assigned']['border'] }}; position: relative; overflow: hidden; height: 24px; width: 100px;">
                      <span class="status-indicator"
                        style="background-color: {{ $statusColors['assigned']['bg'] }}; border-color: {{ $statusColors['assigned']['border'] }};"></span>
                      <select class="form-select form-select-sm status-select" data-complaint-id="{{ $complaint->id }}"
                        data-actual-status="{{ $rawStatus }}" data-status-color="assigned"
                        style="width: 100px; font-size: 9px; font-weight: 700; height: 24px; text-align: center; text-align-last: center; padding: 0;">
                        @if(isset($statuses) && $statuses->count() > 0)
                          @foreach($statuses as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" {{ $complaintStatus == $statusValue ? 'selected' : '' }}>
                              {{ $statusLabel }}
                            </option>
                          @endforeach
                        @else
                          <option value="assigned" {{ $complaintStatus == 'assigned' ? 'selected' : '' }}>Assigned</option>
                          <option value="in_progress" {{ $complaintStatus == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                          <option value="resolved" {{ $complaintStatus == 'resolved' ? 'selected' : '' }}>Addressed</option>
                          <option value="work_priced_performa" {{ $complaintStatus == 'work_priced_performa' ? 'selected' : '' }}>
                            Work Performa Priced</option>
                          <option value="maint_priced_performa" {{ $complaintStatus == 'maint_priced_performa' ? 'selected' : '' }}>
                            Maintenance Performa Priced</option>
                          <option value="product_na" {{ $complaintStatus == 'product_na' ? 'selected' : '' }}>Product N/A</option>
                          <option value="un_authorized" {{ $complaintStatus == 'un_authorized' ? 'selected' : '' }}>Un-Authorized
                          </option>
                          <option value="pertains_to_ge_const_isld" {{ $complaintStatus == 'pertains_to_ge_const_isld' ? 'selected' : '' }}>Pertains to GE(N) Const Isld</option>
                          <option value="barak_damages" {{ $complaintStatus == 'barak_damages' ? 'selected' : '' }}>Barak Damages
                          </option>
                        @endif
                      </select>
                      <i data-feather="chevron-down"
                        style="width: 14px; height: 14px; color: #ffffff !important; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 10; stroke: #ffffff;"></i>
                    </div>
                  @endif
                </td>
                <td class="px-1" style="width: 1%; white-space: nowrap;">
                  <div class="d-flex align-items-center" style="gap: 1.5px;">
                    <button type="button" class="btn btn-outline-success btn-sm" title="View Details"
                      onclick="viewApproval({{ $approval->id }})" style="padding: 1px 3px;">
                      <i data-feather="eye" style="width: 12px; height: 12px;"></i>
                    </button>
                    @if($complaintStatus == 'resolved' || $complaintStatus == 'closed')
                      <button type="button" class="btn btn-outline-secondary btn-sm add-stock-btn"
                        title="Stock cannot be issued" data-approval-id="{{ $approval->id }}"
                        data-category="{{ $category }}" disabled style="padding: 1px 3px; cursor: not-allowed; opacity: 0.6;">
                        <i data-feather="plus-circle" style="width: 12px; height: 12px;"></i>
                      </button>
                    @elseif(isset($approval->has_issued_stock) && $approval->has_issued_stock)
                      <button type="button" class="btn btn-outline-secondary btn-sm add-stock-btn" title="Stock issued"
                        data-approval-id="{{ $approval->id }}" data-category="{{ $category }}" disabled
                        style="padding: 1px 3px; cursor: not-allowed; opacity: 0.6;">
                        <i data-feather="plus-circle" style="width: 12px; height: 12px;"></i>
                      </button>
                    @else
                      <button type="button" class="btn btn-outline-primary btn-sm add-stock-btn" title="Submit"
                        data-approval-id="{{ $approval->id }}" data-category="{{ $category }}"
                        onclick="openAddStockModal({{ $approval->id }}, '{{ $category }}')"
                        style="padding: 1px 3px; cursor: pointer;">
                        <i data-feather="plus-circle" style="width: 12px; height: 12px;"></i>
                      </button>
                    @endif
                    @if($complaintStatus == 'resolved' || $complaintStatus == 'closed')
                      @php
                        $hasFeedback = false;
                        $feedbackId = null;
                        // Safely check if feedback exists without triggering lazy loading errors
                        try {
                          if ($complaint && $complaint->relationLoaded('feedback')) {
                            $feedback = $complaint->getRelation('feedback');
                            if ($feedback && $feedback->id) {
                              $hasFeedback = true;
                              $feedbackId = $feedback->id;
                            }
                          } else {
                            // Use exists() method to check without loading the relationship
                            $hasFeedback = \App\Models\ComplaintFeedback::where('complaint_id', $complaint->id)->exists();
                            if ($hasFeedback) {
                              $feedback = \App\Models\ComplaintFeedback::where('complaint_id', $complaint->id)->first();
                              if ($feedback) {
                                $feedbackId = $feedback->id;
                              }
                            }
                          }
                        } catch (\Exception $e) {
                          $hasFeedback = false;
                          $feedbackId = null;
                        }

                        // Check if current user is GE (Garrison Engineer)
                        $isGE = false;
                        if (Auth::check() && Auth::user()->role) {
                          $roleName = strtolower(Auth::user()->role->role_name ?? '');
                          $isGE = in_array($roleName, ['garrison_engineer', 'garrison engineer']) ||
                            strpos(strtolower($roleName), 'garrison') !== false ||
                            strpos(strtolower($roleName), 'ge') !== false;
                        }
                      @endphp
                      @if($hasFeedback && $feedbackId)
                        @if($isGE)
                          <a href="javascript:void(0)" onclick="viewFeedbackEdit({{ $feedbackId }})" class="btn btn-success btn-sm"
                            title="Edit Feedback"
                            style="padding: 1px 3px; background-color: #16a34a !important; border-color: #16a34a !important; color: #ffffff !important;">
                            <i data-feather="check-circle" style="width: 12px; height: 12px; color: #ffffff;"></i>
                          </a>
                        @else
                          <span class="btn btn-success btn-sm" title="Feedback (View Only)"
                            style="padding: 1px 3px; background-color: #16a34a !important; border-color: #16a34a !important; color: #ffffff !important; cursor: default; opacity: 0.7;">
                            <i data-feather="check-circle" style="width: 12px; height: 12px; color: #ffffff;"></i>
                          </span>
                        @endif
                      @else
                        <a href="javascript:void(0)" onclick="viewFeedbackCreate({{ $complaint->id }})"
                          class="btn btn-warning btn-sm" title="Add Feedback"
                          style="padding: 1px 3px; background-color: #f59e0b !important; border-color: #f59e0b !important; color: #ffffff !important;">
                          <i data-feather="message-square" style="width: 12px; height: 12px; color: #ffffff;"></i>
                        </a>
                      @endif
                    @endif
                  </div>
                </td>
              </tr>
            @endif
          @empty
            <tr>
              <td colspan="10" class="text-center py-4">
                <i data-feather="check-circle" class="feather-lg mb-2"></i>
                <div>No complaints found</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- TOTAL RECORDS -->
    <div id="approvalsTableFooter" class="text-center py-1 mt-1"
      style="background-color: rgba(59, 130, 246, 0.2); border-top: 1px solid #3b82f6; border-radius: 0 0 8px 8px;">
      <strong style="color: #ffffff; font-size: 12px;">
        Total Records: {{ $approvals->total() }}
      </strong>
    </div>

    <!-- PAGINATION -->
    <div class="d-flex justify-content-center mt-2 small" id="approvalsPagination" style="transform: scale(0.9); transform-origin: center;">
      <div>
        {{ $approvals->links() }}
      </div>
    </div>
  </div>

  <!-- Issue Stock Modal -->
  <div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true"
    role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
      <div class="modal-content card-glass"
        style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(59, 130, 246, 0.3);">
        <div class="modal-header py-2" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
          <h6 class="modal-title text-white" id="addStockModalLabel">
            <i data-feather="package" class="me-2" style="width: 18px; height: 18px;"></i>Authority / Stock Management
          </h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" tabindex="0"
            style="background-color: rgba(255, 255, 255, 0.2); border-radius: 4px; padding: 0.5rem !important; opacity: 1 !important; filter: invert(1); background-size: 1.5em;"></button>
        </div>
        <div class="modal-body" id="addStockModalBody"
          style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
          <!-- Stock items will be loaded here -->
        </div>
        <div class="modal-footer"
          style="border-top: 2px solid rgba(59, 130, 246, 0.2); background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
          <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal" tabindex="0"
            style="border-color: rgba(255, 255, 255, 0.3); color: #ffffff;">
            <i data-feather="x" class="me-1" style="width: 16px; height: 16px;"></i>Close
          </button>
          <button type="button" class="btn btn-success" id="submitAddStockBtn"
            style="display: none; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); border: none; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);"
            tabindex="0">
            <i data-feather="check-circle" class="me-1" style="width: 16px; height: 16px;"></i> Submit
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Complaint Modal -->
  <div class="modal fade" id="complaintModal" tabindex="-1" aria-labelledby="complaintModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content card-glass"
        style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(59, 130, 246, 0.3);">
        <div class="modal-header py-2" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
          <h6 class="modal-title text-white" id="complaintModalLabel">
            <i data-feather="alert-triangle" class="me-2" style="width: 18px; height: 18px;"></i>Complaint Details
          </h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
            onclick="closeComplaintModal()"
            style="background-color: rgba(255, 255, 255, 0.2); border-radius: 4px; padding: 0.5rem !important; opacity: 1 !important; filter: invert(1); background-size: 1.5em;"></button>
        </div>
        <div class="modal-body" id="complaintModalBody">
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Approval Modal -->
  <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content card-glass"
        style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(59, 130, 246, 0.3);">
        <div class="modal-header py-2" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
          <h6 class="modal-title text-white" id="approvalModalLabel">
            <i data-feather="file-text" class="me-2" style="width: 18px; height: 18px;"></i>Complaint Details
          </h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
            onclick="closeApprovalModal()"
            style="background-color: rgba(255, 255, 255, 0.2); border-radius: 4px; padding: 0.5rem !important; opacity: 1 !important; filter: invert(1); background-size: 1.5em;"></button>
        </div>
        <div class="modal-body" id="approvalModalBody">
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="border-top: 2px solid rgba(59, 130, 246, 0.2);">
          <a href="#" id="approvalPrintBtn" class="btn btn-outline-primary" target="_blank" style="display: none;">
            <i data-feather="printer" class="me-2" style="width: 16px; height: 16px;"></i>Print Slip
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Feedback Modal -->
  <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content card-glass"
        style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(59, 130, 246, 0.3);">
        <div class="modal-header" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
          <h5 class="modal-title text-white" id="feedbackModalLabel">
            <i data-feather="message-circle" class="me-2"></i>Feedback
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
            onclick="closeFeedbackModal()"
            style="background-color: rgba(255, 255, 255, 0.2); border-radius: 4px; padding: 0.5rem !important; opacity: 1 !important; filter: invert(1); background-size: 1.5em;"></button>
        </div>
        <div class="modal-body" id="feedbackModalBody">
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection

@push('styles')
  <style>
    /* Compact table styling for approvals - smaller font to fit in one line and prevent horizontal scroll */
    .table.table-dark.table-sm {
      font-size: 0.7rem !important;
    }

    .table.table-dark.table-sm thead {
      display: table-header-group !important;
    }

    .table.table-dark.table-sm thead tr {
      display: table-row !important;
      width: 100% !important;
    }

    .table.table-dark.table-sm th {
      font-size: 0.65rem !important;
      padding: 0.4rem 0.4rem !important;
      white-space: nowrap !important;
      line-height: 1.1 !important;
      display: table-cell !important;
      vertical-align: middle !important;
      word-break: keep-all !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
      position: relative !important;
    }

    /* Specific header text size reduction for date columns */
    .table.table-dark.table-sm th:nth-child(2),
    .table.table-dark.table-sm th:nth-child(3) {
      font-size: 0.6rem !important;
      padding: 0.35rem 0.3rem !important;
    }

    .table.table-dark.table-sm td {
      font-size: 0.7rem !important;
      padding: 0.4rem 0.5rem !important;
      white-space: nowrap !important;
      line-height: 1.2 !important;
    }

    /* Specific styling for date columns to ensure they fit */
    .table.table-dark.table-sm th:nth-child(2),
    .table.table-dark.table-sm th:nth-child(3),
    .table.table-dark.table-sm td:nth-child(2),
    .table.table-dark.table-sm td:nth-child(3) {
      font-size: 0.65rem !important;
    }

    /* Prevent horizontal scroll - make table fit within viewport */
    .table-responsive {
      overflow-x: hidden !important;
      width: 100% !important;
      padding-right: 0 !important;
      margin-right: 0 !important;
    }

    /* Remove extra padding from card container */
    .card-glass .table-responsive {
      padding: 0 !important;
      margin: 0 !important;
    }

    /* Make table more compact with specific column widths */
    .table.table-dark.table-sm {
      width: 100% !important;
      table-layout: fixed !important;
      margin-right: 0 !important;
      border-collapse: collapse !important;
    }

    .table.table-dark.table-sm th:nth-child(1),
    .table.table-dark.table-sm td:nth-child(1) {
      width: 5% !important;
      min-width: 60px !important;
      max-width: 80px !important;
    }

    .table.table-dark.table-sm th:nth-child(2),
    .table.table-dark.table-sm td:nth-child(2) {
      width: 10% !important;
      min-width: 100px !important;
      max-width: 130px !important;
    }

    .table.table-dark.table-sm th:nth-child(3),
    .table.table-dark.table-sm td:nth-child(3) {
      width: 10% !important;
      min-width: 100px !important;
      max-width: 130px !important;
    }

    .table.table-dark.table-sm th:nth-child(4),
    .table.table-dark.table-sm td:nth-child(4) {
      width: 9% !important;
      max-width: 110px !important;
    }

    .table.table-dark.table-sm th:nth-child(5),
    .table.table-dark.table-sm td:nth-child(5) {
      width: 9% !important;
      max-width: 110px !important;
    }

    .table.table-dark.table-sm th:nth-child(6),
    .table.table-dark.table-sm td:nth-child(6) {
      width: 14% !important;
      max-width: 170px !important;
    }

    .table.table-dark.table-sm th:nth-child(7),
    .table.table-dark.table-sm td:nth-child(7) {
      width: 7% !important;
      max-width: 90px !important;
    }

    .table.table-dark.table-sm th:nth-child(8),
    .table.table-dark.table-sm td:nth-child(8) {
      width: 11% !important;
      max-width: 130px !important;
    }

    .table.table-dark.table-sm th:nth-child(9),
    .table.table-dark.table-sm td:nth-child(9) {
      width: 11% !important;
      max-width: 130px !important;
    }

    .table.table-dark.table-sm th:nth-child(10),
    .table.table-dark.table-sm td:nth-child(10) {
      width: 7% !important;
      max-width: 90px !important;
      padding-right: 0.2rem !important;
      padding-left: 0.2rem !important;
      text-align: left !important;
    }

    /* Text overflow handling */
    .table.table-dark.table-sm td {
      overflow: hidden !important;
      text-overflow: ellipsis !important;
      white-space: nowrap !important;
    }

    /* Actions|Feedback column - ensure buttons fit */
    .table.table-dark.table-sm td:nth-child(10) {
      white-space: normal !important;
    }

    .table.table-dark.table-sm td:nth-child(10) .btn {
      padding: 0.2rem 0.4rem !important;
      font-size: 0.65rem !important;
      margin: 0 0.1rem !important;
    }

    .table.table-dark.table-sm td:nth-child(10) .btn i {
      width: 12px !important;
      height: 12px !important;
    }

    body.modal-open-blur {
      overflow: hidden;
    }

    body.modal-open-blur::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
      -webkit-backdrop-filter: blur(5px);
      z-index: 1040;
      pointer-events: none;
    }

    body.modal-open-blur .modal-backdrop,
    #approvalModal.modal.show~.modal-backdrop,
    #approvalModal.modal.show+.modal-backdrop,
    .modal-backdrop.show,
    .modal-backdrop {
      display: none !important;
      visibility: hidden !important;
      opacity: 0 !important;
      background-color: transparent !important;
      backdrop-filter: none !important;
      -webkit-backdrop-filter: none !important;
      pointer-events: none !important;
    }

    /* Ensure modal content is above blur layer */
    #approvalModal {
      z-index: 1055 !important;
    }

    #approvalModal .modal-dialog {
      z-index: 1055 !important;
      position: relative;
    }

    #approvalModal .modal-content {
      max-height: 90vh;
      overflow-y: auto;
      z-index: 1055 !important;
      position: relative;
    }

    #approvalModal .modal-body {
      padding: 1.5rem;
    }

    #approvalModal .btn-close {
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 4px;
      padding: 0.5rem !important;
      opacity: 1 !important;
    }

    #approvalModal .btn-close:hover {
      background-color: rgba(255, 255, 255, 0.3);
    }

    /* Stock Modal Styling - Ensure it appears above blur layer */
    #addStockModal {
      z-index: 1055 !important;
    }

    #addStockModal .modal-dialog {
      z-index: 1055 !important;
      position: relative;
    }

    #addStockModal .modal-content {
      max-height: 90vh;
      overflow-y: auto;
      z-index: 1055 !important;
      position: relative;
    }

    #addStockModal .modal-body {
      padding: 1.5rem;
    }

    #addStockModal .btn-close {
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 4px;
      padding: 0.5rem !important;
      opacity: 1 !important;
    }

    #addStockModal .btn-close:hover {
      background-color: rgba(255, 255, 255, 0.3);
    }

    /* Feedback Modal Styling */
    #feedbackModal {
      z-index: 1055 !important;
    }

    #feedbackModal .modal-dialog {
      z-index: 1055 !important;
      position: relative;
      max-width: 1200px !important;
      width: 95vw !important;
    }

    #feedbackModal .modal-content {
      max-height: 90vh;
      overflow-y: auto;
      z-index: 1055 !important;
      position: relative;
    }

    #feedbackModal .modal-body {
      padding: 1.5rem;
    }

    #feedbackModal .btn-close {
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 4px;
      padding: 0.5rem !important;
      opacity: 1 !important;
    }

    #feedbackModal .btn-close:hover {
      background-color: rgba(255, 255, 255, 0.3);
    }

    /* Make content boxes smaller in feedback modal */
    #feedbackModal .col-lg-10,
    #feedbackModal .col-xl-9 {
      max-width: 100%;
    }

    #feedbackModal .card-glass {
      margin-bottom: 1rem;
    }

    /* Toast Notification Animations */
    @keyframes slideInRight {
      from {
        transform: translateX(100%);
        opacity: 0;
      }

      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    @keyframes fadeOut {
      from {
        opacity: 1;
        transform: translateX(0);
      }

      to {
        opacity: 0;
        transform: translateX(100%);
      }
    }

    @keyframes shake {

      0%,
      100% {
        transform: translateX(0);
      }

      10%,
      30%,
      50%,
      70%,
      90% {
        transform: translateX(-5px);
      }

      20%,
      40%,
      60%,
      80% {
        transform: translateX(5px);
      }
    }

    @keyframes blink {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: 0.3;
      }
    }

    .blinking-dot {
      animation: blink 1s infinite !important;
      -webkit-animation: blink 1s infinite !important;
      -moz-animation: blink 1s infinite !important;
      -o-animation: blink 1s infinite !important;
    }

    .type-badge {
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
    }

    .type-spare {
      background: rgba(59, 130, 246, 0.2);
      color: #3b82f6;
    }

    .status-badge {
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
    }

    .status-pending {
      background: rgba(245, 158, 11, 0.2);
      color: #f59e0b;
    }

    .status-approved {
      background: rgba(34, 197, 94, 0.2);
      color: #22c55e;
    }

    /* Performa badge - ensure white text for all badges including Product N/A (only badges, not heading) */
    .table td .performa-badge {
      color: white !important;
      border-radius: 6px !important;
    }

    /* Table text styling for all themes */
    .table td {
      color: #1e293b !important;
    }

    .theme-dark .table td,
    .theme-night .table td {
      color: #f1f5f9 !important;
    }

    .table .text-muted {
      color: #64748b !important;
    }

    .theme-dark .table .text-muted,
    .theme-night .table .text-muted {
      color: #94a3b8 !important;
    }

    /* Complaint ID column - force left alignment - VERY SPECIFIC */
    table.table td:first-child,
    table.table th:first-child,
    .table-dark td:first-child,
    .table-dark th:first-child,
    .table.table-dark td:first-child,
    .table.table-dark th:first-child,
    #approvalsTableBody td:first-child {
      text-align: left !important;
      direction: ltr !important;
      justify-content: flex-start !important;
      align-items: flex-start !important;
    }

    table.table td:first-child a,
    .table-dark td:first-child a,
    table.table td:first-child *,
    .table-dark td:first-child *,
    .table.table-dark td:first-child a,
    .table.table-dark td:first-child *,
    #approvalsTableBody td:first-child a,
    #approvalsTableBody td:first-child * {
      text-align: left !important;
      direction: ltr !important;
      float: none !important;
      display: inline-block !important;
      margin: 0 !important;
      padding-left: 0 !important;
      width: auto !important;
      text-align: left !important;
    }

    /* Override ANY possible center or right alignment for first column */
    #approvalsTableBody tr td:first-child,
    #approvalsTableBody tr td:first-child a,
    #approvalsTableBody tr td:first-child span,
    #approvalsTableBody tr td:first-child * {
      text-align: left !important;
      direction: ltr !important;
      float: none !important;
    }

    /* Performa Required column - white text (only values, not heading) */
    .table td:nth-child(9) {
      color: white !important;
      text-align: center !important;
      vertical-align: middle !important;
    }

    .table td:nth-child(9) .performa-badge {
      color: white !important;
      border-radius: 6px !important;
      width: 140px !important;
      height: 32px !important;
      padding: 0 !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      font-size: 11px !important;
      font-weight: 700 !important;
    }

    /* Compact status select box */
    .status-select {
      width: 140px !important;
      padding: 2px 6px !important;
      font-size: 11px !important;
      font-weight: 700 !important;
      height: 32px !important;
      line-height: 1.4 !important;
      cursor: pointer;
      transition: all 0.2s ease;
      border-radius: 4px;
      border: 1px solid rgba(0, 0, 0, 0.2) !important;
      text-align: center !important;
      text-align-last: center !important;
      /* Make native arrow consistent */
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
      padding-right: 22px !important;
      /* room for arrow */
      background-repeat: no-repeat !important;
      background-position: right 6px center !important;
      background-size: 12px 12px !important;
      /* SVG arrow uses white color */
      background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>") !important;
    }

    .status-select:hover {
      opacity: 0.9;
      transform: scale(1.02);
    }

    .status-select:focus {
      outline: 2px solid rgba(59, 130, 246, 0.5);
      outline-offset: 2px;
    }

    .status-select option {
      padding: 10px 12px;
      font-size: 12px;
      font-weight: 500;
      line-height: 1.6;
      background-color: #ffffff;
      color: #1f2937;
      min-height: 36px;
    }

    .status-select option:disabled {
      color: #9ca3af;
      font-style: italic;
    }

    /* Live color indicator next to status select (works across browsers) */
    .status-indicator {
      display: inline-block;
      width: 10px;
      height: 10px;
      border-radius: 50%;
      margin-right: 6px;
      border: 1px solid rgba(0, 0, 0, 0.25);
      vertical-align: middle;
    }

    /* Colored chip that wraps the whole status control */
    .status-chip {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 10px;
      border-radius: 6px;
      border: 1px solid rgba(0, 0, 0, 0.2) !important;
      height: 32px;
      width: 140px;
      justify-content: center;
      color: white !important;
    }

    .status-chip .status-select {
      background: transparent !important;
      color: white !important;
      border: none !important;
      padding-left: 0 !important;
      /* keep right padding for arrow */
      padding-right: 22px !important;
      height: 20px !important;
      line-height: 20px !important;
    }

    .status-chip span {
      font-size: 11px;
      font-weight: 700;
      color: white !important;
    }

    /* Always show number input spinner arrows (not only on hover) */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
      opacity: 1;
      -webkit-appearance: inner-spin-button;
      appearance: auto;
      margin: 0;
    }

    /* Ensure Firefox shows the spinner controls */
    input[type=number] {
      -moz-appearance: number-input;
      appearance: number-input;
    }

    /* Ensure the spinner is visible on small control too */
    #manualRequestQty::-webkit-inner-spin-button,
    #manualRequestQty::-webkit-outer-spin-button {
      opacity: 1 !important;
    }

    /* Ensure Add Stock button is clickable */
    .add-stock-btn {
      pointer-events: auto !important;
      z-index: 10 !important;
      position: relative !important;
      cursor: pointer !important;
      opacity: 1 !important;
    }

    .add-stock-btn:hover {
      opacity: 0.8 !important;
    }

    .add-stock-btn:active {
      opacity: 0.6 !important;
    }

    .btn-group .add-stock-btn {
      margin-left: 4px;
    }

    /* Ensure Submit button in Add Stock Modal is clickable */
    #submitAddStockBtn {
      pointer-events: auto !important;
      z-index: 1050 !important;
      position: relative !important;
      cursor: pointer !important;
      opacity: 1 !important;
      display: inline-block !important;
    }

    #submitAddStockBtn:hover {
      opacity: 0.9 !important;
    }

    #submitAddStockBtn:active {
      opacity: 0.7 !important;
    }

    #submitAddStockBtn:disabled {
      opacity: 0.6 !important;
      cursor: not-allowed !important;
    }

    /* Ensure modal footer buttons are clickable */
    #addStockModal .modal-footer button {
      pointer-events: auto !important;
      z-index: 1050 !important;
      position: relative !important;
    }

    /* Add Stock Modal Table Styling */
    #addStockModal .table {
      margin-bottom: 0;
    }

    #addStockModal .table thead th {
      background-color: #0d6efd;
      color: white;
      border: 1px solid #0d6efd;
      padding: 12px 15px;
      text-align: center;
      vertical-align: middle;
    }

    #addStockModal .table tbody td {
      padding: 12px 15px;
      border: 1px solid #dee2e6;
      vertical-align: middle;
    }

    /* Zebra striping for addStockModal */
    #addStockModal .table tbody tr:nth-child(odd) {
      background-color: rgba(255, 255, 255, 0.25) !important;
    }

    #addStockModal .table tbody tr:nth-child(even) {
      background-color: rgba(59, 130, 246, 0.2) !important;
    }

    #addStockModal .table tbody tr:hover {
      background-color: rgba(59, 130, 246, 0.35) !important;
    }

    #addStockModal .table-responsive {
      border-radius: 8px;
      overflow: hidden;
    }

    #addStockModal .total-quantity-input {
      border: 1px solid #ced4da;
      border-radius: 4px;
      padding: 6px 10px;
      font-size: 14px;
    }

    #addStockModal .total-quantity-input:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
      outline: none;
    }

    /* Table column borders - vertical lines between columns (same as complaints modal) */
    .table-dark th,
    .table-dark td {
      border-right: 1px solid rgba(201, 160, 160, 0.3);
      border-left: none;
    }

    #approvalModal .table-dark th:first-child,
    #approvalModal .table-dark td:first-child,
    .modal .table-dark th:first-child,
    .modal .table-dark td:first-child,
    #approvalModal .table th:first-child,
    #approvalModal .table td:first-child,
    .modal .table th:first-child,
    .modal .table td:first-child {
      border-left: none !important;
    }

    #approvalModal .table-dark th:last-child,
    #approvalModal .table-dark td:last-child,
    .modal .table-dark th:last-child,
    .modal .table-dark td:last-child,
    #approvalModal .table th:last-child,
    #approvalModal .table td:last-child,
    .modal .table th:last-child,
    .modal .table td:last-child {
      border-right: none !important;
    }
  </style>
@endpush

@push('scripts')
  <script>
    feather.replace();

    // Global variables
    let currentApprovalId = null;
    let isProcessing = false;
    let currentComplaintId = null;

    // Complaint Functions
    function viewComplaint(complaintId) {
      if (!complaintId) {
        alert('Invalid complaint ID');
        return;
      }

      currentComplaintId = complaintId;

      const modalElement = document.getElementById('complaintModal');
      const modalBody = document.getElementById('complaintModalBody');

      // Show loading state
      modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

      // Add blur effect to background first
      document.body.classList.add('modal-open-blur');

      // Show modal WITHOUT backdrop so we can see the blurred background
      const modal = new bootstrap.Modal(modalElement, {
        backdrop: false, // Disable Bootstrap backdrop completely
        keyboard: true,
        focus: true
      });
      modal.show();

      // Ensure any backdrop that might be created is removed
      const removeBackdrop = () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => {
          backdrop.remove(); // Remove from DOM
        });
      };

      // Use MutationObserver to catch and remove any backdrop creation
      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          mutation.addedNodes.forEach((node) => {
            if (node.nodeType === 1 && node.classList && node.classList.contains('modal-backdrop')) {
              node.remove(); // Remove immediately if created
            }
          });
        });
        removeBackdrop();
      });

      observer.observe(document.body, {
        childList: true,
        subtree: true
      });

      // Remove any existing backdrops
      removeBackdrop();
      setTimeout(removeBackdrop, 10);
      setTimeout(removeBackdrop, 50);
      setTimeout(removeBackdrop, 100);

      // Clean up observer when modal is hidden
      modalElement.addEventListener('hidden.bs.modal', function () {
        observer.disconnect();
        removeBackdrop();
      }, { once: true });

      // Load complaint details via AJAX - force HTML response
      fetch(`/admin/complaints/${complaintId}?format=html`, {
        method: 'GET',
        headers: {
          'Accept': 'text/html',
        },
        credentials: 'same-origin'
      })
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          // Check if response is JSON
          const contentType = response.headers.get('content-type');
          if (contentType && contentType.includes('application/json')) {
            return response.json().then(data => {
              throw new Error('Received JSON instead of HTML. Please check the route.');
            });
          }
          return response.text();
        })
        .then(html => {
          // Check if response is actually JSON (starts with {)
          if (html.trim().startsWith('{')) {
            console.error('Received JSON instead of HTML');
            modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error: Server returned JSON instead of HTML. Please check the route configuration.</div>';
            return;
          }

          // Extract the content from the show page
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');

          // Remove all share-modal.js scripts BEFORE processing content
          const allScripts = doc.querySelectorAll('script');
          allScripts.forEach(script => {
            if (script.src && script.src.includes('share-modal')) {
              script.remove();
            }
            if (script.textContent && script.textContent.includes('share-modal')) {
              script.remove();
            }
          });

          // Get the content section - try multiple selectors
          let contentSection = doc.querySelector('section.content');
          if (!contentSection) {
            contentSection = doc.querySelector('.content');
          }
          if (!contentSection) {
            // Try to find the main content area
            const mainContent = doc.querySelector('main') || doc.querySelector('[role="main"]');
            if (mainContent) {
              contentSection = mainContent;
            } else {
              contentSection = doc.body;
            }
          }

          // Extract the complaint details sections
          let complaintContent = '';

          // Get all rows that contain complaint information (skip page header)
          const allRows = contentSection.querySelectorAll('.row');
          const seenRows = new Set();

          allRows.forEach(row => {
            // Skip rows that are in page headers
            const isInHeader = row.closest('.mb-4') && row.closest('.mb-4').querySelector('h2');

            // Check if this row contains card-glass elements
            const hasCardGlass = row.querySelector('.card-glass');

            if (!isInHeader && hasCardGlass) {
              const rowHTML = row.outerHTML;
              // Use a simple hash to avoid duplicates
              const rowId = rowHTML.substring(0, 200);
              if (!seenRows.has(rowId)) {
                seenRows.add(rowId);
                complaintContent += rowHTML;
              }
            }
          });

          // If no rows found, fallback to extracting individual cards
          if (!complaintContent) {
            const allCards = contentSection.querySelectorAll('.card-glass');
            const seenCards = new Set();

            allCards.forEach(card => {
              // Skip cards that are in page headers
              const parentRow = card.closest('.row');
              const isInHeader = parentRow && parentRow.closest('.mb-4') && parentRow.closest('.mb-4').querySelector('h2');

              // Skip duplicate "Complainant Comments" sections
              const cardText = card.textContent || '';
              const isCommentsSection = cardText.includes('Complainant Comments') && !card.closest('.card-body');

              if (!isInHeader && !isCommentsSection) {
                const cardHTML = card.outerHTML;
                const cardId = cardHTML.substring(0, 300);
                if (!seenCards.has(cardId)) {
                  seenCards.add(cardId);
                  complaintContent += '<div class="mb-3">' + cardHTML + '</div>';
                }
              }
            });
          }

          // Remove duplicate "Complainant Comments" sections
          if (complaintContent) {
            const tempDivForComments = document.createElement('div');
            tempDivForComments.innerHTML = complaintContent;
            const commentSections = tempDivForComments.querySelectorAll('h6, h5, h4');
            let foundCommentsSection = false;
            commentSections.forEach(heading => {
              if (heading.textContent && heading.textContent.includes('Complainant Comments')) {
                if (foundCommentsSection) {
                  // Remove duplicate - find the parent row and remove it
                  const parentRow = heading.closest('.row');
                  if (parentRow) {
                    parentRow.remove();
                  }
                } else {
                  foundCommentsSection = true;
                }
              }
            });
            complaintContent = tempDivForComments.innerHTML;
          }

          if (complaintContent) {
            // Remove any share-modal.js scripts from the content before inserting
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = complaintContent;
            const scriptsInContent = tempDiv.querySelectorAll('script');
            scriptsInContent.forEach(script => {
              if (script.src && script.src.includes('share-modal')) {
                script.remove();
              }
              if (script.textContent && script.textContent.includes('share-modal')) {
                script.remove();
              }
            });
            complaintContent = tempDiv.innerHTML;

            modalBody.innerHTML = complaintContent;
            // Replace feather icons after content is loaded
            setTimeout(() => {
              feather.replace();
              // Double-check and remove any share-modal.js scripts that might have been added
              const shareModalScripts = document.querySelectorAll('script[src*="share-modal"]');
              shareModalScripts.forEach(script => {
                try {
                  script.remove();
                } catch (e) {
                  // Ignore errors
                }
              });
            }, 100);
          } else {
            console.error('Could not find complaint content in response');
            console.log('Content section:', contentSection);
            console.log('Found cards:', contentSection.querySelectorAll('.card-glass').length);
            modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error: Could not load complaint details. Please refresh and try again.</div>';
          }
        })
        .catch(error => {
          console.error('Error loading complaint:', error);
          modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error loading complaint details: ' + error.message + '. Please try again.</div>';
        });

      // Replace feather icons when modal is shown
      modalElement.addEventListener('shown.bs.modal', function () {
        feather.replace();
      });

      // Remove blur when modal is hidden
      modalElement.addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open-blur');
        feather.replace();
      }, { once: true });
    }

    // Function to close complaint modal and remove blur
    function closeComplaintModal() {
      const modalElement = document.getElementById('complaintModal');
      if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
          modal.hide();
        }
      }
      document.body.classList.remove('modal-open-blur');
    }

    // Approval Functions
    function viewApproval(approvalId) {
      if (!approvalId) {
        alert('Invalid approval ID');
        return;
      }

      currentApprovalId = approvalId;

      const modalElement = document.getElementById('approvalModal');
      const modalBody = document.getElementById('approvalModalBody');
      const printBtn = document.getElementById('approvalPrintBtn');

      // Hide print button initially
      if (printBtn) {
        printBtn.style.display = 'none';
      }

      // Show loading state
      modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

      // Add blur effect to background first
      document.body.classList.add('modal-open-blur');

      // Show modal WITHOUT backdrop so we can see the blurred background
      const modal = new bootstrap.Modal(modalElement, {
        backdrop: false, // Disable Bootstrap backdrop completely
        keyboard: true,
        focus: true
      });
      modal.show();

      // Ensure any backdrop that might be created is removed
      const removeBackdrop = () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => {
          backdrop.remove(); // Remove from DOM
        });
      };

      // Use MutationObserver to catch and remove any backdrop creation
      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          mutation.addedNodes.forEach((node) => {
            if (node.nodeType === 1 && node.classList && node.classList.contains('modal-backdrop')) {
              node.remove(); // Remove immediately if created
            }
          });
        });
        removeBackdrop();
      });

      observer.observe(document.body, {
        childList: true,
        subtree: true
      });

      // Remove any existing backdrops
      removeBackdrop();
      setTimeout(removeBackdrop, 10);
      setTimeout(removeBackdrop, 50);
      setTimeout(removeBackdrop, 100);

      // Clean up observer when modal is hidden
      modalElement.addEventListener('hidden.bs.modal', function () {
        observer.disconnect();
        removeBackdrop();
      }, { once: true });

      // Load approval details via AJAX - force HTML response
      fetch(`/admin/approvals/${approvalId}?format=html`, {
        method: 'GET',
        headers: {
          'Accept': 'text/html',
        },
        credentials: 'same-origin'
      })
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          // Check if response is JSON
          const contentType = response.headers.get('content-type');
          if (contentType && contentType.includes('application/json')) {
            return response.json().then(data => {
              throw new Error('Received JSON instead of HTML. Please check the route.');
            });
          }
          return response.text();
        })
        .then(html => {
          // Check if response is actually JSON (starts with {)
          if (html.trim().startsWith('{')) {
            console.error('Received JSON instead of HTML');
            modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error: Server returned JSON instead of HTML. Please check the route configuration.</div>';
            return;
          }

          // Extract the content from the show page
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');

          // Get the content section - try multiple selectors
          let contentSection = doc.querySelector('section.content');
          if (!contentSection) {
            contentSection = doc.querySelector('.content');
          }
          if (!contentSection) {
            // Try to find the main content area
            const mainContent = doc.querySelector('main') || doc.querySelector('[role="main"]');
            if (mainContent) {
              contentSection = mainContent;
            } else {
              contentSection = doc.body;
            }
          }

          // Extract the approval details sections
          let approvalContent = '';

          // Get all rows that contain approval information (skip page header)
          const allRows = contentSection.querySelectorAll('.row');
          const seenRows = new Set();

          allRows.forEach(row => {
            // Skip rows that are in page headers
            const isInHeader = row.closest('.mb-4') && row.closest('.mb-4').querySelector('h2');

            // Check if this row contains card-glass elements
            const hasCardGlass = row.querySelector('.card-glass');

            if (!isInHeader && hasCardGlass) {
              const rowHTML = row.outerHTML;
              // Use a simple hash to avoid duplicates
              const rowId = rowHTML.substring(0, 200);
              if (!seenRows.has(rowId)) {
                seenRows.add(rowId);
                approvalContent += rowHTML;
              }
            }
          });

          // Also extract standalone card-glass elements (like Requested Items section)
          const allCards = contentSection.querySelectorAll('.card-glass');
          const seenCards = new Set();

          allCards.forEach(card => {
            // Skip cards that are in page headers
            const parentRow = card.closest('.row');
            const isInHeader = parentRow && parentRow.closest('.mb-4') && parentRow.closest('.mb-4').querySelector('h2');

            // Skip if already added from rows
            const cardHTML = card.outerHTML;
            const cardId = cardHTML.substring(0, 300);

            if (!isInHeader && !seenCards.has(cardId) && !approvalContent.includes(cardHTML.substring(0, 100))) {
              seenCards.add(cardId);
              // Check if it's already in a row that was added
              const isInAddedRow = parentRow && approvalContent.includes(parentRow.outerHTML.substring(0, 100));
              if (!isInAddedRow) {
                approvalContent += '<div class="mb-3">' + cardHTML + '</div>';
              }
            }
          });

          if (approvalContent) {
            modalBody.innerHTML = approvalContent;

            // Extract complaint ID from the loaded content
            let complaintId = null;
            // Try to find complaint ID from links
            const complaintLink = modalBody.querySelector('a[href*="/admin/complaints/"]');
            if (complaintLink) {
              const href = complaintLink.getAttribute('href');
              const match = href.match(/\/admin\/complaints\/(\d+)/);
              if (match) {
                complaintId = match[1];
              }
            }
            // If not found in link, try to find in text content (Complaint ID section)
            if (!complaintId) {
              const complaintIdElements = modalBody.querySelectorAll('*');
              for (let el of complaintIdElements) {
                const text = el.textContent || '';
                if (text.includes('Complaint ID') || text.includes('Complaint Id')) {
                  // Try to find number after "Complaint ID"
                  const idMatch = text.match(/Complaint\s+ID[:\s]*(\d+)/i);
                  if (idMatch) {
                    complaintId = idMatch[1];
                    break;
                  }
                }
              }
            }

            // Show/hide print button based on complaint ID availability
            const printBtn = document.getElementById('approvalPrintBtn');
            if (printBtn) {
              if (complaintId) {
                printBtn.href = `/admin/complaints/${complaintId}/print-slip`;
                printBtn.style.display = 'inline-block';
              } else {
                printBtn.style.display = 'none';
                printBtn.href = '#';
              }
            }

            // Function to apply table column borders - VERY AGGRESSIVE
            const applyTableBorders = () => {
              // Find all tables in modal body
              const modalTables = modalBody.querySelectorAll('table');
              console.log('🔍 Found tables:', modalTables.length);

              modalTables.forEach((table, tableIndex) => {
                console.log(`📊 Processing table ${tableIndex + 1}`);

                // Get all th and td elements
                const ths = table.querySelectorAll('th');
                const tds = table.querySelectorAll('td');
                console.log(`   Found ${ths.length} headers and ${tds.length} cells`);

                // Apply borders to headers
                ths.forEach((th, thIndex) => {
                  const row = th.parentElement;
                  const cellsInRow = Array.from(row.querySelectorAll('th'));
                  const cellIndex = cellsInRow.indexOf(th);
                  const isLast = cellIndex === cellsInRow.length - 1;

                  // Force border with multiple methods
                  if (!isLast) {
                    th.setAttribute('style', (th.getAttribute('style') || '') + ' border-right: 1px solid rgba(201, 160, 160, 0.3) !important;');
                    th.style.borderRight = '1px solid rgba(201, 160, 160, 0.3)';
                    th.style.setProperty('border-right', '1px solid rgba(201, 160, 160, 0.3)', 'important');
                  } else {
                    th.setAttribute('style', (th.getAttribute('style') || '') + ' border-right: none !important;');
                    th.style.borderRight = 'none';
                    th.style.setProperty('border-right', 'none', 'important');
                  }
                });

                // Apply borders to cells
                tds.forEach((td, tdIndex) => {
                  const row = td.parentElement;
                  const cellsInRow = Array.from(row.querySelectorAll('td'));
                  const cellIndex = cellsInRow.indexOf(td);
                  const isLast = cellIndex === cellsInRow.length - 1;

                  // Force border with multiple methods
                  if (!isLast) {
                    td.setAttribute('style', (td.getAttribute('style') || '') + ' border-right: 1px solid rgba(201, 160, 160, 0.3) !important;');
                    td.style.borderRight = '1px solid rgba(201, 160, 160, 0.3)';
                    td.style.setProperty('border-right', '1px solid rgba(201, 160, 160, 0.3)', 'important');
                  } else {
                    td.setAttribute('style', (td.getAttribute('style') || '') + ' border-right: none !important;');
                    td.style.borderRight = 'none';
                    td.style.setProperty('border-right', 'none', 'important');
                  }
                });
              });

              console.log('✅ Borders applied to', modalTables.length, 'tables');
            };

            // Replace feather icons after content is loaded
            setTimeout(() => {
              feather.replace();
              applyTableBorders();
              // Apply again after delays to catch any late-loading content
              setTimeout(applyTableBorders, 100);
              setTimeout(applyTableBorders, 200);
              setTimeout(applyTableBorders, 500);
              setTimeout(applyTableBorders, 1000);
            }, 50);

            // Also apply when modal is fully shown
            setTimeout(() => {
              const modalElement = document.getElementById('approvalModal');
              if (modalElement) {
                const applyOnShow = function () {
                  setTimeout(applyTableBorders, 100);
                  setTimeout(applyTableBorders, 300);
                  setTimeout(applyTableBorders, 600);
                };
                modalElement.addEventListener('shown.bs.modal', applyOnShow, { once: true });
                // Also apply immediately if modal is already shown
                if (modalElement.classList.contains('show')) {
                  applyOnShow();
                }
              }
            }, 100);
          } else {
            console.error('Could not find approval content in response');
            console.log('Content section:', contentSection);
            console.log('Found cards:', contentSection.querySelectorAll('.card-glass').length);
            modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error: Could not load approval details. Please refresh and try again.</div>';
          }
        })
        .catch(error => {
          console.error('Error loading approval:', error);
          modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error loading approval details: ' + error.message + '. Please try again.</div>';
        });

      // Replace feather icons when modal is shown
      modalElement.addEventListener('shown.bs.modal', function () {
        feather.replace();
      });

      // Remove blur when modal is hidden
      modalElement.addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open-blur');
        feather.replace();
      }, { once: true });
    }

    function closeApprovalModal() {
      const modalElement = document.getElementById('approvalModal');
      if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
          modal.hide();
        }
      }
      document.body.classList.remove('modal-open-blur');
    }

    // Feedback Functions
    function viewFeedbackCreate(complaintId) {
      if (!complaintId) {
        alert('Invalid complaint ID');
        return;
      }

      const modalElement = document.getElementById('feedbackModal');
      const modalBody = document.getElementById('feedbackModalBody');

      // Show loading state
      modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

      // Add blur effect to background first
      document.body.classList.add('modal-open-blur');

      // Show modal WITHOUT backdrop so we can see the blurred background
      const modal = new bootstrap.Modal(modalElement, {
        backdrop: false, // Disable Bootstrap backdrop completely
        keyboard: true,
        focus: true
      });
      modal.show();

      // Ensure any backdrop that might be created is removed
      const removeBackdrop = () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => {
          backdrop.remove(); // Remove from DOM
        });
      };

      // Use MutationObserver to catch and remove any backdrop creation
      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          mutation.addedNodes.forEach((node) => {
            if (node.nodeType === 1 && node.classList && node.classList.contains('modal-backdrop')) {
              node.remove(); // Remove immediately if created
            }
          });
        });
        removeBackdrop();
      });

      observer.observe(document.body, {
        childList: true,
        subtree: true
      });

      // Remove any existing backdrops
      removeBackdrop();
      setTimeout(removeBackdrop, 10);
      setTimeout(removeBackdrop, 50);
      setTimeout(removeBackdrop, 100);

      // Clean up observer when modal is hidden
      modalElement.addEventListener('hidden.bs.modal', function () {
        observer.disconnect();
        removeBackdrop();
      }, { once: true });

      // Load feedback create form via AJAX
      fetch(`/admin/complaints/${complaintId}/feedback/create?modal=1`, {
        method: 'GET',
        headers: {
          'Accept': 'text/html',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin'
      })
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.text();
        })
        .then(html => {
          // Extract only the content section from the full page HTML
          const tempDiv = document.createElement('div');
          tempDiv.innerHTML = html;

          // Find the content section
          const contentSection = tempDiv.querySelector('section.content') || tempDiv.querySelector('.content');

          if (contentSection) {
            // Extract only the rows with card-glass (skip page header)
            let feedbackContent = '';
            const rows = contentSection.querySelectorAll('.row');

            rows.forEach(row => {
              // Skip page header rows (those with h5.text-white.mb-1 and p.text-light)
              const isHeader = row.querySelector('h5.text-white.mb-1') && row.querySelector('p.text-light');
              if (!isHeader && row.querySelector('.card-glass')) {
                feedbackContent += row.outerHTML;
              }
            });

            if (feedbackContent) {
              modalBody.innerHTML = feedbackContent;
            } else {
              // Fallback: extract all card-glass elements
              const cards = contentSection.querySelectorAll('.card-glass');
              cards.forEach(card => {
                const parentRow = card.closest('.row');
                if (parentRow) {
                  feedbackContent += parentRow.outerHTML;
                }
              });
              modalBody.innerHTML = feedbackContent || '<div class="text-center py-5 text-danger">Error: Could not load feedback form.</div>';
            }
          } else {
            modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error: Could not find content section.</div>';
          }

          // Update form action to submit via AJAX
          const form = modalBody.querySelector('form');
          if (form) {
            form.addEventListener('submit', function (e) {
              e.preventDefault();
              submitFeedbackForm(form, 'create', complaintId);
            });
          }

          // Replace feather icons after content is loaded
          feather.replace();
        })
        .catch(error => {
          console.error('Error loading feedback form:', error);
          modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error loading feedback form: ' + error.message + '. Please try again.</div>';
        });

      // Replace feather icons when modal is shown
      modalElement.addEventListener('shown.bs.modal', function () {
        feather.replace();
      });

      // Remove blur when modal is hidden
      modalElement.addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open-blur');
        feather.replace();
      }, { once: true });
    }

    function viewFeedbackEdit(feedbackId) {
      if (!feedbackId) {
        alert('Invalid feedback ID');
        return;
      }

      const modalElement = document.getElementById('feedbackModal');
      const modalBody = document.getElementById('feedbackModalBody');

      // Show loading state
      modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

      // Add blur effect to background first
      document.body.classList.add('modal-open-blur');

      // Show modal WITHOUT backdrop so we can see the blurred background
      const modal = new bootstrap.Modal(modalElement, {
        backdrop: false, // Disable Bootstrap backdrop completely
        keyboard: true,
        focus: true
      });
      modal.show();

      // Ensure any backdrop that might be created is removed
      const removeBackdrop = () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => {
          backdrop.remove(); // Remove from DOM
        });
      };

      // Use MutationObserver to catch and remove any backdrop creation
      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          mutation.addedNodes.forEach((node) => {
            if (node.nodeType === 1 && node.classList && node.classList.contains('modal-backdrop')) {
              node.remove(); // Remove immediately if created
            }
          });
        });
        removeBackdrop();
      });

      observer.observe(document.body, {
        childList: true,
        subtree: true
      });

      // Remove any existing backdrops
      removeBackdrop();
      setTimeout(removeBackdrop, 10);
      setTimeout(removeBackdrop, 50);
      setTimeout(removeBackdrop, 100);

      // Clean up observer when modal is hidden
      modalElement.addEventListener('hidden.bs.modal', function () {
        observer.disconnect();
        removeBackdrop();
      }, { once: true });

      // Load feedback edit form via AJAX
      fetch(`/admin/feedbacks/${feedbackId}/edit?modal=1`, {
        method: 'GET',
        headers: {
          'Accept': 'text/html',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin'
      })
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.text();
        })
        .then(html => {
          // Extract only the content section from the full page HTML
          const tempDiv = document.createElement('div');
          tempDiv.innerHTML = html;

          // Find the content section
          const contentSection = tempDiv.querySelector('section.content') || tempDiv.querySelector('.content');

          if (contentSection) {
            // Extract only the rows with card-glass (skip page header)
            let feedbackContent = '';
            const rows = contentSection.querySelectorAll('.row');

            rows.forEach(row => {
              // Skip page header rows (those with h5.text-white.mb-1 and p.text-light)
              const isHeader = row.querySelector('h5.text-white.mb-1') && row.querySelector('p.text-light');
              if (!isHeader && row.querySelector('.card-glass')) {
                feedbackContent += row.outerHTML;
              }
            });

            if (feedbackContent) {
              modalBody.innerHTML = feedbackContent;
            } else {
              // Fallback: extract all card-glass elements
              const cards = contentSection.querySelectorAll('.card-glass');
              cards.forEach(card => {
                const parentRow = card.closest('.row');
                if (parentRow) {
                  feedbackContent += parentRow.outerHTML;
                }
              });
              modalBody.innerHTML = feedbackContent || '<div class="text-center py-5 text-danger">Error: Could not load feedback form.</div>';
            }
          } else {
            modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error: Could not find content section.</div>';
          }

          // Update form action to submit via AJAX
          const form = modalBody.querySelector('form');
          if (form) {
            const formAction = form.getAttribute('action');
            const feedbackIdMatch = formAction.match(/\/feedbacks\/(\d+)/);
            if (feedbackIdMatch) {
              form.addEventListener('submit', function (e) {
                e.preventDefault();
                submitFeedbackForm(form, 'update', null, feedbackIdMatch[1]);
              });
            }
          }

          // Replace feather icons after content is loaded
          feather.replace();
        })
        .catch(error => {
          console.error('Error loading feedback form:', error);
          modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error loading feedback form: ' + error.message + '. Please try again.</div>';
        });

      // Replace feather icons when modal is shown
      modalElement.addEventListener('shown.bs.modal', function () {
        feather.replace();
      });

      // Remove blur when modal is hidden
      modalElement.addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open-blur');
        feather.replace();
      }, { once: true });
    }

    function submitFeedbackForm(form, action, complaintId, feedbackId) {
      const formData = new FormData(form);
      const submitButton = form.querySelector('button[type="submit"]');
      const originalButtonText = submitButton ? submitButton.innerHTML : '';

      // Disable submit button
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
      }

      let url;
      let method;
      if (action === 'create') {
        url = `/admin/complaints/${complaintId}/feedback`;
        method = 'POST';
      } else {
        url = `/admin/feedbacks/${feedbackId}`;
        method = 'POST'; // Use POST with _method for PUT
        formData.append('_method', 'PUT');
      }

      fetch(url, {
        method: method,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
        },
        body: formData,
        credentials: 'same-origin'
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Show success message
            if (typeof showSuccess === 'function') {
              showSuccess(data.message || 'Feedback saved successfully!');
            } else {
              alert(data.message || 'Feedback saved successfully!');
            }

            // Close modal
            closeFeedbackModal();

            // Reload page to refresh the table
            setTimeout(() => {
              location.reload();
            }, 1000);
          } else {
            // Show error message
            if (typeof showError === 'function') {
              showError(data.message || 'Failed to save feedback.');
            } else {
              alert(data.message || 'Failed to save feedback.');
            }

            // Re-enable submit button
            if (submitButton) {
              submitButton.disabled = false;
              submitButton.innerHTML = originalButtonText;
            }
          }
        })
        .catch(error => {
          console.error('Error submitting feedback:', error);
          if (typeof showError === 'function') {
            showError('An error occurred while saving feedback. Please try again.');
          } else {
            alert('An error occurred while saving feedback. Please try again.');
          }

          // Re-enable submit button
          if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
          }
        });
    }

    function closeFeedbackModal() {
      const modalElement = document.getElementById('feedbackModal');
      if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
          modal.hide();
        }
      }
      document.body.classList.remove('modal-open-blur');
    }

    // approveRequest and rejectRequest functions removed as per user request

    // Utility Functions
    function refreshPage() {
      console.log('Refreshing page...');
      location.reload();
    }

    // Debounced search input handler - auto filter on typing (instant response)
    let approvalsSearchTimeout = null;
    function handleApprovalsSearchInput(e) {
      if (e) e.preventDefault();
      if (e) e.stopPropagation();

      // Clear existing timeout
      if (approvalsSearchTimeout) clearTimeout(approvalsSearchTimeout);

      // Set new timeout - auto search after 200ms of no typing (faster response)
      approvalsSearchTimeout = setTimeout(() => {
        console.log('Auto-search triggered');
        loadApprovals();
      }, 200);
    }

    // Reset filters function
    function resetApprovalsFilters() {
      const form = document.getElementById('approvalsFiltersForm');
      if (!form) return;

      // Clear all form inputs
      form.querySelectorAll('input[type="text"], input[type="date"], select').forEach(input => {
        if (input.type === 'select-one') {
          input.selectedIndex = 0;
        } else {
          input.value = '';
        }
      });

      // Reset URL to base route
      window.location.href = '{{ route('admin.approvals.index') }}';
    }

    // Auto-submit for select filters - immediate filter on change
    function submitApprovalsFilters(e) {
      if (e) e.preventDefault();
      if (e) e.stopPropagation();

      const form = document.getElementById('approvalsFiltersForm');
      if (!form) {
        console.error('Filter form not found');
        return;
      }

      // Cancel any pending search timeout
      if (approvalsSearchTimeout) {
        clearTimeout(approvalsSearchTimeout);
        approvalsSearchTimeout = null;
      }

      // Immediately load approvals with current filter values (no delay)
      console.log('Filter change triggered');
      loadApprovals();
    }

    // Ensure functions are globally available
    window.handleApprovalsSearchInput = handleApprovalsSearchInput;
    window.submitApprovalsFilters = submitApprovalsFilters;
    window.loadApprovals = loadApprovals;

    // Helper function to escape HTML
    function escapeHtml(text) {
      if (!text) return '';
      const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      };
      return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Helper function to render item row
    function renderItemRow(item) {
      const canDelete = !item.isExisting;
      return `
              <tr data-item-id="${item.itemId}" data-spare-id="${item.spareId}" data-is-existing="${item.isExisting}">
                <td style="vertical-align: middle; font-weight: 500; padding: 12px;">${escapeHtml(item.productName)}</td>
                <td style="vertical-align: middle; text-align: center; font-weight: 500; padding: 12px;">${escapeHtml(item.category)}</td>
                <td style="vertical-align: middle; text-align: center; font-weight: 500; padding: 12px;">${item.requestedQty}</td>
                <td style="vertical-align: middle; text-align: center; font-weight: 500; padding: 12px;">
                  <span class="badge ${item.availableStock > 0 ? 'bg-success' : 'bg-danger'}" style="font-size: 12px;">${item.availableStock}</span>
                </td>
                <td style="vertical-align: middle; text-align: center; padding: 12px;">
                  <input type="number" 
                         class="form-control form-control-sm issue-quantity-input" 
                         name="items[${item.itemId}][issue_quantity]" 
                         value="${item.issueQty}" 
                         min="0" 
                         max="${item.availableStock}"
                         data-spare-id="${item.spareId}"
                         data-item-id="${item.itemId}"
                         data-product-name="${escapeHtml(item.productName)}"
                         data-available-stock="${item.availableStock}"
                         style="width: 120px; text-align: center; margin: 0 auto; display: block;">
                </td>
                <td style="vertical-align: middle; text-align: center; padding: 12px;">
                  ${canDelete ? `<button type="button" class="btn btn-danger btn-sm remove-item-btn" data-item-id="${item.itemId}" style="padding: 3px 8px;" title="Remove">
                    <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                  </button>` : '<span class="text-muted">-</span>'}
                </td>
              </tr>
            `;
    }

    // Load categories for modal dropdown
    function loadCategoriesForModal(selectedCategory = null) {
      console.log('Loading categories for modal...', selectedCategory);
      const categorySelect = document.getElementById('manualCategory');

      if (!categorySelect) {
        console.error('Category select element not found!');
        // Retry after a short delay
        setTimeout(() => {
          loadCategoriesForModal(selectedCategory);
        }, 200);
        return;
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      fetch('/admin/spares/get-categories', {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'same-origin'
      })
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          console.log('Categories response:', data);
          if (data.success && data.categories && Array.isArray(data.categories)) {
            categorySelect.innerHTML = '<option value="">All Products</option>';
            data.categories.forEach(cat => {
              const option = document.createElement('option');
              option.value = cat;
              option.textContent = cat;
              if (selectedCategory && cat.toLowerCase() === selectedCategory.toLowerCase()) {
                option.selected = true;
              }
              categorySelect.appendChild(option);
            });

            // Auto-select complaint category if provided
            if (selectedCategory) {
              categorySelect.value = selectedCategory;
              categorySelect.disabled = true; // Disable category dropdown
            }

            console.log(`✅ Loaded ${data.categories.length} categories`);
          } else {
            console.warn('⚠️ No categories found or invalid response:', data);
            categorySelect.innerHTML = '<option value="">All Products</option>';
          }
        })
        .catch(error => {
          console.error('❌ Error loading categories:', error);
          categorySelect.innerHTML = '<option value="">Error loading categories</option>';
        });
    }

    // Load products by category (or all products if category is empty)
    function loadProductsByCategory(category, sector = null, city = null) {
      const productSelect = document.getElementById('manualProduct');
      const availableStockInput = document.getElementById('manualAvailableStock');

      if (!productSelect) return;

      // If no category, show all products
      if (!category) {
        category = ''; // Empty category will fetch all products
      }

      productSelect.innerHTML = '<option value="">Loading...</option>';
      productSelect.disabled = true;
      availableStockInput.value = '';

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      // Build URL with category, sector, and city parameters
      const params = new URLSearchParams();
      if (category) {
        params.append('category', category);
      }
      if (sector) {
        params.append('sector', sector);
      }
      if (city) {
        params.append('city', city);
      }

      const url = `/admin/spares/get-products-by-category?${params.toString()}`;

      fetch(url, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'same-origin'
      })
        .then(response => response.json())
        .then(data => {
          if (data.success && data.products && data.products.length > 0) {
            productSelect.innerHTML = '<option value="">Select Product</option>';
            data.products.forEach(product => {
              const option = document.createElement('option');
              option.value = product.id;
              option.textContent = product.item_name;
              option.setAttribute('data-stock', product.stock_quantity || 0);
              option.setAttribute('data-category', product.category || '');
              productSelect.appendChild(option);
            });
            productSelect.disabled = false;
          } else {
            productSelect.innerHTML = '<option value="">No products found</option>';
            productSelect.disabled = false; // Enable even if no products so user can try again
          }
        })
        .catch(error => {
          console.error('Error loading products:', error);
          productSelect.innerHTML = '<option value="">Error loading products</option>';
          productSelect.disabled = false;
        });
    }

    // Add item to table
    function addManualItemToTable() {
      const categorySelect = document.getElementById('manualCategory');
      const productSelect = document.getElementById('manualProduct');
      const availableStockInput = document.getElementById('manualAvailableStock');
      const requestQtyInput = document.getElementById('manualRequestQty');

      // Category select is optional, but other fields are required
      if (!productSelect || !availableStockInput || !requestQtyInput) return;

      const category = categorySelect ? categorySelect.value || '' : '';
      const productId = productSelect.value;
      const productName = productSelect.options[productSelect.selectedIndex]?.textContent || 'N/A';
      const productCategory = productSelect.options[productSelect.selectedIndex]?.getAttribute('data-category') || category || '';
      const availableStock = parseInt(availableStockInput.value) || 0;
      const requestQty = parseInt(requestQtyInput.value) || 0;

      if (!productId) {
        alert('Please select a product');
        return;
      }

      if (requestQty <= 0) {
        alert('Please enter a valid request quantity');
        requestQtyInput.focus();
        return;
      }

      if (requestQty > availableStock) {
        alert(`Request quantity (${requestQty}) cannot exceed available stock (${availableStock})`);
        requestQtyInput.focus();
        return;
      }

      // Check if product already exists in manual items
      const existingItem = window.manualItems?.find(item => item.spare_id == productId);
      if (existingItem) {
        alert('This product is already added. Please remove it first or update the quantity.');
        return;
      }

      // Add to manual items array
      if (!window.manualItems) {
        window.manualItems = [];
      }

      const tempId = `manual_${Date.now()}`;
      const newItem = {
        tempId: tempId,
        spare_id: parseInt(productId),
        product_name: productName,
        category: productCategory, // Use product's actual category from data attribute
        requested_qty: requestQty,
        available_stock: availableStock,
        isExisting: false
      };

      window.manualItems.push(newItem);

      // Reset form
      if (categorySelect) {
        categorySelect.value = '';
      }
      // Don't reset product select - keep it enabled so user can add more items
      // productSelect.innerHTML = '<option value="">Select Category First</option>';
      // productSelect.disabled = true;
      availableStockInput.value = '';
      requestQtyInput.value = '';

      // Show success message
      console.log('Item added:', newItem);
      console.log('Total items:', window.manualItems.length);

      // Show submit button
      const submitBtn = document.getElementById('submitAddStockBtn');
      if (submitBtn) {
        submitBtn.style.display = 'inline-block';
      }
    }

    // Setup manual form event listeners
    function setupManualFormListeners() {
      const categorySelect = document.getElementById('manualCategory');
      const productSelect = document.getElementById('manualProduct');
      const availableStockInput = document.getElementById('manualAvailableStock');
      const requestQtyInput = document.getElementById('manualRequestQty');
      const authorityYes = document.getElementById('authorityYes');
      const authorityNo = document.getElementById('authorityNo');
      const authorityNoCol = document.getElementById('authorityNoCol');
      const authorityNumber = document.getElementById('authorityNumber');
      const authoritySimple = document.getElementById('authorityNumberSimple');

      // Do not require categorySelect since it may be removed; only require the fields we use
      if (!productSelect || !availableStockInput || !requestQtyInput) return;

      // Category change (only if category is not disabled)
      if (categorySelect && !categorySelect.disabled) {
        categorySelect.addEventListener('change', function () {
          const category = this.value;
          loadProductsByCategory(category);
          availableStockInput.value = '';
          requestQtyInput.value = '';
        });
      }

      // Product change
      productSelect.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const stock = parseInt(selectedOption?.getAttribute('data-stock')) || 0;
        availableStockInput.value = stock;

        // Check if authority is required first
        const isAuthorityRequired = authorityYes && authorityYes.checked;
        const hasAuthNo = isAuthorityRequired && authorityNumber && authorityNumber.value && authorityNumber.value.trim().length > 0;

        // If authority required and no authority number, lock the field
        if (isAuthorityRequired && !hasAuthNo) {
          requestQtyInput.disabled = true;
          requestQtyInput.readOnly = true;
          requestQtyInput.value = '';
          requestQtyInput.placeholder = 'Enter Authority No.';
          updateSubmitButtonState();
          return; // Don't set default value or enable field
        }

        // If not locked, set default value and enable field
        requestQtyInput.disabled = false;
        requestQtyInput.readOnly = false;
        requestQtyInput.placeholder = 'Enter quantity';

        // Default request quantity to 1 when a product is selected and stock is available
        if (stock > 0) {
          if (!requestQtyInput.value || parseInt(requestQtyInput.value) < 1) {
            requestQtyInput.value = 1;
          }
          requestQtyInput.min = 1;
          requestQtyInput.max = stock;
        } else {
          requestQtyInput.value = '';
        }

        updateSubmitButtonState();
      });

      // Authority number (simple) change - enable submit when provided
      if (authoritySimple) {
        authoritySimple.addEventListener('input', function () {
          updateSubmitButtonState();
        });
      }
      if (authorityNumber) {
        authorityNumber.addEventListener('input', function () {
          updateSubmitButtonState();
        });
      }

      // Request quantity input - enable submit button when valid
      requestQtyInput.addEventListener('input', function () {
        updateSubmitButtonState();
      });

      requestQtyInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          if (!requestQtyInput.disabled && productSelect.value && requestQtyInput.value) {
            const submitBtn = document.getElementById('submitAddStockBtn');
            if (submitBtn && !submitBtn.disabled) {
              window.submitIssueStock();
            }
          }
        }
      });

      // Function to update submit button state
      function updateSubmitButtonState() {
        const submitBtn = document.getElementById('submitAddStockBtn');
        if (!submitBtn) return;

        // Always keep submit enabled (authority-only submissions are allowed)
        submitBtn.disabled = false;
      }

      // Authority No. Required toggle handlers - Authority No. is optional
      if (authorityYes && authorityNo && authorityNoCol) {
        const updateAuthorityVisibility = () => {
          const required = authorityYes.checked;
          authorityNoCol.classList.toggle('d-none', !required);

          if (authorityNumber) authorityNumber.required = false;

          if (requestQtyInput) {
            requestQtyInput.disabled = false;
            requestQtyInput.readOnly = false;
            requestQtyInput.placeholder = 'Enter quantity';

            const stock = parseInt(availableStockInput.value) || 0;
            if (stock > 0 && (!requestQtyInput.value || parseInt(requestQtyInput.value) < 1)) {
              requestQtyInput.value = 1;
              requestQtyInput.min = 1;
              requestQtyInput.max = stock;
            }
          }

          // If "Yes" selected but no authority entered yet, keep fields visible and let user type
          // If "No" selected, clear both authority fields and hide legacy input
          if (!required) {
            if (authorityNumber) authorityNumber.value = '';
            if (authoritySimple) authoritySimple.value = '';
            window.currentAuthorityNumber = '';
          }

          updateSubmitButtonState();
        };
        authorityYes.addEventListener('change', updateAuthorityVisibility);
        authorityNo.addEventListener('change', updateAuthorityVisibility);
        updateAuthorityVisibility();
      }

      // Issue Stock toggle handlers - Show/hide product fields
      const issueStockYes = document.getElementById('issueStockYes');
      const issueStockNo = document.getElementById('issueStockNo');
      const productIssueFields = document.getElementById('productIssueFields');

      if (issueStockYes && issueStockNo && productIssueFields) {
        const updateIssueStockVisibility = () => {
          const showFields = issueStockYes.checked;
          productIssueFields.classList.toggle('d-none', !showFields);

          // Clear fields when hiding
          if (!showFields) {
            if (productSelect) productSelect.value = '';
            if (availableStockInput) availableStockInput.value = '';
            if (requestQtyInput) requestQtyInput.value = '';
          }

          updateSubmitButtonState();
        };
        issueStockYes.addEventListener('change', updateIssueStockVisibility);
        issueStockNo.addEventListener('change', updateIssueStockVisibility);
        updateIssueStockVisibility();
      }

    }

    // Forward declaration for Add Stock Modal function (defined later)
    window.openAddStockModal = function (approvalId, category = null) {
      console.log('openAddStockModal called with ID:', approvalId, 'Category:', category);

      // Store approvalId globally for submitIssueStock
      window.currentApprovalId = approvalId;
      console.log('Modal opened with approval_id:', approvalId);

      // Reset manual items array when modal opens (not needed anymore but keeping for compatibility)
      window.manualItems = [];

      // Find modal element
      const modalElement = document.getElementById('addStockModal');
      if (!modalElement) {
        console.error('Modal element not found');
        alert('Modal not found. Please refresh the page.');
        return;
      }

      const modalBody = document.getElementById('addStockModalBody');
      if (!modalBody) {
        console.error('Modal body not found');
        alert('Modal body not found. Please refresh the page.');
        return;
      }

      // Show loading immediately
      modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading approval items...</p></div>';

      // Show Submit button and hide it initially
      const submitBtn = document.getElementById('submitAddStockBtn');
      if (submitBtn) {
        submitBtn.style.display = 'none';
        submitBtn.disabled = false;
      }

      // Add blur effect to background first
      document.body.classList.add('modal-open-blur');

      // Show modal immediately (before data loads)
      let modalInstance = null;
      try {
        // Check if Bootstrap is available
        if (typeof bootstrap === 'undefined' || typeof bootstrap.Modal === 'undefined') {
          console.error('Bootstrap Modal not available');
          alert('Bootstrap Modal library not loaded. Please refresh the page.');
          return;
        }

        modalInstance = bootstrap.Modal.getInstance(modalElement);
        if (!modalInstance) {
          modalInstance = new bootstrap.Modal(modalElement, {
            backdrop: false, // Disable Bootstrap backdrop completely
            keyboard: true,
            focus: true
          });
        }

        // Fix accessibility: Set up event listeners before showing modal
        const handleShown = function () {
          // Bootstrap automatically removes aria-hidden on shown event
          // But we ensure it's properly set
          if (modalElement.hasAttribute('aria-hidden')) {
            modalElement.removeAttribute('aria-hidden');
          }
          modalElement.setAttribute('aria-modal', 'true');
          console.log('✅ Modal accessibility fixed');
        };

        const handleHidden = function () {
          modalElement.setAttribute('aria-hidden', 'true');
          modalElement.removeAttribute('aria-modal');
          // Remove blur effect when modal closes
          document.body.classList.remove('modal-open-blur');
          // No auto-save logic on modal close - user must explicitly submit
        };

        // Remove any existing listeners first
        modalElement.removeEventListener('shown.bs.modal', handleShown);
        modalElement.removeEventListener('hidden.bs.modal', handleHidden);

        // Add new listeners
        modalElement.addEventListener('shown.bs.modal', handleShown, { once: true });
        modalElement.addEventListener('hidden.bs.modal', handleHidden);

        // Ensure any backdrop that might be created is removed
        const removeBackdrop = () => {
          const backdrops = document.querySelectorAll('.modal-backdrop');
          backdrops.forEach(backdrop => {
            backdrop.remove(); // Remove from DOM
          });
        };

        // Use MutationObserver to catch and remove any backdrop creation
        const observer = new MutationObserver((mutations) => {
          mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
              if (node.nodeType === 1 && node.classList && node.classList.contains('modal-backdrop')) {
                node.remove();
                console.log('✅ Removed Bootstrap backdrop');
              }
            });
          });
        });

        // Observe document.body for any backdrop additions
        observer.observe(document.body, { childList: true });

        // Show the modal
        modalInstance.show();
        console.log('Modal shown successfully');

        // Remove any existing backdrops multiple times to ensure they're gone
        removeBackdrop();
        setTimeout(removeBackdrop, 10);
        setTimeout(removeBackdrop, 50);
        setTimeout(removeBackdrop, 100);

        // Clean up observer when modal is hidden
        modalElement.addEventListener('hidden.bs.modal', function () {
          observer.disconnect();
          removeBackdrop();
        }, { once: true });

        // Also fix immediately after a short delay (in case shown event fires before our listener)
        setTimeout(() => {
          if (modalElement.classList.contains('show') && modalElement.hasAttribute('aria-hidden')) {
            modalElement.removeAttribute('aria-hidden');
            modalElement.setAttribute('aria-modal', 'true');
            console.log('✅ Modal accessibility fixed (delayed check)');
          }
        }, 200);

      } catch (error) {
        console.error('Error showing modal:', error);
        alert('Error opening modal: ' + error.message);
        return;
      }

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      // Fetch approval details with timeout
      const controller = new AbortController();
      const timeoutId = setTimeout(() => {
        controller.abort();
        modalBody.innerHTML = '<div class="alert alert-warning">Request timeout. Please try again.</div>';
      }, 30000); // 30 second timeout

      fetch(`/admin/approvals/${approvalId}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'same-origin',
        signal: controller.signal
      })
        .then(response => {
          clearTimeout(timeoutId);
          console.log('Fetch response status:', response.status);
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          console.log('Fetch response data:', data);
          if (data.success && data.approval && data.approval.items) {
            const items = data.approval.items || [];
            const issuedStock = data.approval.issued_stock || [];

            console.log('✅ Creating table with 5 columns:');
            console.log('   1. Product Name (25% width)');
            console.log('   2. Category (20% width)');
            console.log('   3. Request Quantity (15% width)');
            console.log('   4. Available Stock (15% width)');
            console.log('   5. Issue Quantity (25% width)');
            console.log('Items to display:', items.length);
            console.log('Items data:', items);
            console.log('Issued stock:', issuedStock);

            if (items.length === 0) {
              console.info('ℹ️ No items found in approval ID:', approvalId, '- This approval may not have any items yet or items may have been removed.');
              console.info('ℹ️ You can manually add items using the form below.');
            } else {
              console.log('✅ Items found:', items.length);
              console.log('✅ Displaying items in table with 5 columns');
            }

            // Initialize manual items array
            if (!window.manualItems) {
              window.manualItems = [];
            }

            // Store items globally for submission (existing + manual)
            window.currentApprovalItems = items;
            window.lastIssuedStock = issuedStock.length > 0 ? issuedStock[0] : null; // Store last issued stock
            console.log('Approval items loaded:', items.length, 'items');
            console.log('Current approval_id:', window.currentApprovalId);
            console.log('Last issued stock:', window.lastIssuedStock);

            // Build form with manual add section
            let itemsHtml = '<form id="addStockForm">';

            // Check if stock has already been issued - if yes, disable the form
            const hasIssuedStock = issuedStock.length > 0;

            // Manual Add Form Section
            itemsHtml += `
                  <div class="card mb-3" style="border: 1px solid #dee2e6; border-radius: 8px;">
                    <div class="card-header bg-primary text-white" style="padding: 12px 16px; font-weight: 600; font-size: 14px;">
                    </div>
                    <div class="card-body" style="padding: 16px;">
                      <!-- Authority No. Req and Issue Stock Row - Combined -->
                      <div class="row g-3 mb-3 align-items-end" id="authorityRow">
                        <div class="col-md-3">
                          <label class="form-label small mb-1" style="font-size: 0.85rem; font-weight: 600; color: #000000 !important;">Authority No. Req</label>
                          <div class="d-flex align-items-center" style="gap: 10px;">
                            <div class="form-check form-check-inline" style="margin: 0;">
                              <input class="form-check-input" type="radio" name="authorityRequired" id="authorityNo" value="no" checked>
                              <label class="form-check-label" for="authorityNo" style="font-size: 0.85rem;">No</label>
                            </div>
                            <div class="form-check form-check-inline" style="margin: 0;">
                              <input class="form-check-input" type="radio" name="authorityRequired" id="authorityYes" value="yes">
                              <label class="form-check-label" for="authorityYes" style="font-size: 0.85rem;">Yes</label>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-3 d-none" id="authorityNoCol">
                          <label class="form-label small mb-1" style="font-size: 0.85rem; font-weight: 600; color: #000000 !important;">Authority No.</label>
                          <input type="text" class="form-control form-control-sm" id="authorityNumber" placeholder="Enter Authority No." style="font-size: 0.9rem;">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small mb-1" style="font-size: 0.85rem; font-weight: 600; color: #000000 !important;">Issue Stock</label>
                          <div class="d-flex align-items-center" style="gap: 10px;">
                            <div class="form-check form-check-inline" style="margin: 0;">
                              <input class="form-check-input" type="radio" name="issueStockRequired" id="issueStockNo" value="no" checked>
                              <label class="form-check-label" for="issueStockNo" style="font-size: 0.85rem;">No</label>
                            </div>
                            <div class="form-check form-check-inline" style="margin: 0;">
                              <input class="form-check-input" type="radio" name="issueStockRequired" id="issueStockYes" value="yes">
                              <label class="form-check-label" for="issueStockYes" style="font-size: 0.85rem;">Yes</label>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Product Issue Fields - Hidden by default -->
                      <div class="row g-3 d-none" id="productIssueFields">
                        <div class="col-md-4">
                          <label class="form-label small mb-1" style="font-size: 0.85rem; font-weight: 600; color: #000000 !important;">Product</label>
                          <select class="form-select form-select-sm" id="manualProduct" style="font-size: 0.9rem;" disabled>
                            <option value="">Loading Products...</option>
                          </select>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small mb-1" style="font-size: 0.85rem; font-weight: 600; color: #000000 !important;">Available Stock</label>
                          <input type="text" class="form-control form-control-sm" id="manualAvailableStock" readonly style="font-size: 0.9rem; background-color: #f8f9fa; font-weight: 600; text-align: center;">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small mb-1" style="font-size: 0.85rem; font-weight: 600; color: #000000 !important;">Request Quantity</label>
                          <input type="number" class="form-control form-control-sm" id="manualRequestQty" min="1" style="font-size: 0.9rem; text-align: center;" placeholder="Enter quantity">
                        </div>
                      </div>
                    </div>
                  </div>
                `;

            itemsHtml += '</form>';
            modalBody.innerHTML = itemsHtml;

            // Get complaint category and location from stored global variable or approval response
            const complaintCategory = window.currentComplaintCategory || data.approval?.complaint?.category || null;
            const complaintSector = data.approval?.complaint?.sector || null;
            const complaintCity = data.approval?.complaint?.city || null;
            console.log('Complaint category:', complaintCategory, 'From stored:', window.currentComplaintCategory, 'From response:', data.approval?.complaint?.category);
            console.log('Complaint sector:', complaintSector, 'Complaint city:', complaintCity);

            // Wait for DOM to be ready before initializing
            setTimeout(() => {
              // Load products for complaint category and sector only (no category dropdown needed)
              if (complaintCategory && complaintCategory.trim() !== '' && complaintCategory !== 'N/A') {
                console.log('Loading products for category:', complaintCategory, 'sector:', complaintSector);
                // Ensure category is passed correctly, along with sector for location filtering
                loadProductsByCategory(complaintCategory.trim(), complaintSector, complaintCity);
              } else {
                console.warn('No complaint category found');
                // Don't load all products if category is missing - show empty
                const productSelect = document.getElementById('manualProduct');
                if (productSelect) {
                  productSelect.innerHTML = '<option value="">No category found - Please select a product manually</option>';
                  productSelect.disabled = false;
                }
              }

              // Setup event listeners for manual form
              setupManualFormListeners();

              // Populate saved authority number if it exists
              let existingAuthority = null;

              // Check dedicated authority_number column first
              if (data.approval?.authority_number) {
                existingAuthority = data.approval.authority_number;
              } else if (data.approval?.remarks) {
                // Extract from remarks if not in dedicated column
                const remarksMatch = data.approval.remarks.match(/Authority\s+No[:\s]+([A-Za-z0-9\-]+)/i);
                if (remarksMatch) {
                  existingAuthority = remarksMatch[1];
                }
              }

              // If authority number exists, populate the form
              if (existingAuthority && !hasIssuedStock) {
                console.log('Found existing authority number:', existingAuthority);

                // Set "Authority No. Req" to "Yes"
                const authorityYesRadio = document.getElementById('authorityYes');
                if (authorityYesRadio) {
                  authorityYesRadio.checked = true;

                  // Trigger change event to show the authority number input field
                  authorityYesRadio.dispatchEvent(new Event('change'));
                }

                // Wait for the input field to be visible, then populate it
                setTimeout(() => {
                  const authorityInput = document.getElementById('authorityNumber');
                  if (authorityInput) {
                    authorityInput.value = existingAuthority;
                    console.log('Populated authority number input with:', existingAuthority);
                  }
                }, 50);
              }

              // Don't populate form if stock has already been issued
              if (hasIssuedStock) {
                // Form is already disabled above, no need to populate
                return;
              }
            }, 100);

            // Show Submit button always (will be enabled when product and quantity are selected)
            if (submitBtn) {
              submitBtn.style.display = 'inline-block';
              // Keep submit enabled in all cases
              submitBtn.disabled = false;
              if (hasIssuedStock) {
                submitBtn.innerHTML = 'Stock Already Issued';
                submitBtn.classList.add('btn-secondary');
                submitBtn.classList.remove('btn-success');
              }
            }

            // Replace feather icons (for empty state icon)
            if (typeof feather !== 'undefined') {
              feather.replace();
            }
          } else {
            console.error('Invalid response data:', data);
            modalBody.innerHTML = '<div class="alert alert-danger">Error loading approval items. Invalid response.</div>';
          }
        })
        .catch(error => {
          clearTimeout(timeoutId);
          console.error('Error fetching approval details:', error);
          if (error.name === 'AbortError') {
            modalBody.innerHTML = '<div class="alert alert-warning">Request timeout. The server is taking too long to respond. Please try again.</div>';
          } else {
            modalBody.innerHTML = '<div class="alert alert-danger">Error loading approval details: ' + (error.message || 'Unknown error') + '</div>';
          }
        });
    };

    // Forward declaration for Submit Add Stock function (defined later)
    // Issue Stock Function (early definition)
    window.submitIssueStock = function () {
      console.log('submitIssueStock called');

      // Get form values directly
      const productSelect = document.getElementById('manualProduct');
      const availableStockInput = document.getElementById('manualAvailableStock');
      const requestQtyInput = document.getElementById('manualRequestQty');
      const authorityYes = document.getElementById('authorityYes');
      const authorityNumber = document.getElementById('authorityNumber');
      const authoritySimple = document.getElementById('authorityNumberSimple');

      if (!productSelect || !availableStockInput || !requestQtyInput) {
        alert('Form fields not found. Please refresh the page.');
        return;
      }

      const productId = productSelect.value;
      const productName = productSelect.options[productSelect.selectedIndex]?.textContent || 'N/A';
      const availableStock = parseInt(availableStockInput.value) || 0;
      const issueQty = parseInt(requestQtyInput.value) || 0;

      // Allow authority-only submission (no product needed)
      const authNo = (authoritySimple && authoritySimple.value.trim()) || (authorityNumber && authorityNumber.value.trim()) || '';

      // Check if authority is required and provided
      const isAuthorityRequired = authorityYes && authorityYes.checked;

      // Get performa type from status dropdown (if work_performa or maint_performa is selected)
      const approvalId = window.currentApprovalId;
      let perfVal = '';
      if (approvalId) {
        // Find the row for this approval
        const addStockBtns = document.querySelectorAll(`button.add-stock-btn[data-approval-id="${approvalId}"]`);
        let approvalRow = null;
        for (let btn of addStockBtns) {
          approvalRow = btn.closest('tr');
          if (approvalRow) break;
        }
        if (approvalRow) {
          const statusSelect = approvalRow.querySelector('select.status-select[data-complaint-id]');
          if (statusSelect && (statusSelect.value === 'work_performa' || statusSelect.value === 'maint_performa')) {
            perfVal = statusSelect.value;
          }
        }
      }

      // If only authority number provided (no product), save authority and exit
      if (!productId && authNo && isAuthorityRequired) {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Disable submit button while saving
        const submitBtn = document.getElementById('submitAddStockBtn');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving Authority...';
        }

        // Build authority info string
        const typeLabel = perfVal === 'work_performa' ? 'Work Performa' : (perfVal === 'maint_performa' ? 'Maintenance Performa' : 'General');
        const authorityInfo = `Authority No. Req: ${typeLabel}, Authority No: ${authNo}`;

        // Save authority number only (no stock)
        fetch(`/admin/approvals/${window.currentApprovalId}/save-performa`, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            performa_type: perfVal || null,
            remarks: authorityInfo
          }),
          credentials: 'same-origin'
        })
          .then(response => response.json())
          .then(data => {
            if (submitBtn) {
              submitBtn.disabled = false;
              submitBtn.innerHTML = '<i data-feather="check-circle"></i> Submit';
            }

            if (data.success) {
              alert('Authority number saved successfully!');
              // Close modal and reload page
              const modalInstance = bootstrap.Modal.getInstance(document.getElementById('addStockModal'));
              if (modalInstance) modalInstance.hide();
              window.location.reload();
            } else {
              alert('Error saving authority number: ' + (data.message || 'Unknown error'));
            }
          })
          .catch(error => {
            console.error('Error saving authority:', error);
            if (submitBtn) {
              submitBtn.disabled = false;
              submitBtn.innerHTML = '<i data-feather="check-circle"></i> Submit';
            }
            alert('Error saving authority number: ' + error.message);
          });

        return;
      }

      // Validate form when product is required
      if (!productId) {
        alert('Please select a product');
        productSelect.focus();
        return;
      }

      if (issueQty <= 0) {
        alert('Please enter a valid request quantity');
        requestQtyInput.focus();
        return;
      }

      if (issueQty > availableStock) {
        alert(`Request quantity (${issueQty}) cannot exceed available stock (${availableStock})`);
        requestQtyInput.focus();
        return;
      }

      // If "No" is selected, don't save any authority info - just proceed with stock issue
      // If "Yes" is selected, authority number is optional - proceed with or without it
      // Authority number is always optional, so proceed with stock issue regardless

      // Prepare stock data
      const stockData = [{
        spare_id: parseInt(productId),
        item_id: null,
        issue_quantity: issueQty,
        product_name: productName,
        available_stock: availableStock
      }];

      // Confirm before submitting
      const confirmMessage = `Are you sure you want to ISSUE stock for the following items?\n\n` +
        stockData.map(item => `${item.product_name}: ${item.issue_quantity} units (Available: ${item.available_stock})`).join('\n');

      if (!confirm(confirmMessage)) {
        return;
      }

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      // Keep submit button enabled
      const submitBtn = document.getElementById('submitAddStockBtn');
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Issuing Stock...';
      }

      // Send requests for each item to ISSUE stock (decrease inventory)
      const promises = stockData.map(item => {
        // Build optional authority info string (appended in reason)
        // Only include if "Yes" is selected and authority number is provided
        let authorityInfo = '';
        if (isAuthorityRequired && authNo) {
          const typeLabel = perfVal === 'work_performa' ? 'Work Performa' : (perfVal === 'maint_performa' ? 'Maintenance Performa' : '');
          authorityInfo = ` | Authority No. Req: ${typeLabel || 'Yes'}, Authority No: ${authNo}`;
        }
        // If "No" is selected or no authority number provided, don't add authority info
        return fetch(`/admin/spares/${item.spare_id}/issue-stock`, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            quantity: item.issue_quantity,
            item_id: item.item_id,
            approval_id: window.currentApprovalId || null,
            reason: `Stock issued from approval - Product: ${item.product_name}${authorityInfo}`
          }),
          credentials: 'same-origin'
        });
      });

      // Process all requests
      Promise.all(promises)
        .then(responses => {
          // Parse all responses, handling both success and error cases
          return Promise.all(responses.map(async (response) => {
            try {
              const data = await response.json();
              console.log('Stock issue response:', { status: response.status, ok: response.ok, data });
              // Check both HTTP status and JSON success field
              if (response.ok && data.success) {
                return { success: true, data };
              } else {
                console.error('Stock issue failed:', { status: response.status, data });
                return { success: false, data };
              }
            } catch (error) {
              console.error('Error parsing response:', error);
              return { success: false, error: error.message };
            }
          }));
        })
        .then(results => {
          console.log('All results:', results);
          const successCount = results.filter(r => r.success).length;
          const failedCount = results.length - successCount;
          console.log(`Success: ${successCount}, Failed: ${failedCount}`);

          if (failedCount === 0) {
            // If authority is required (Yes selected) and authority number is provided, save it
            // If "No" is selected, don't save any authority info
            if (isAuthorityRequired && perfVal && authNo && window.currentApprovalId) {
              // Update approval to remove waiting flag
              fetch(`/admin/approvals/${window.currentApprovalId}/save-performa`, {
                method: 'POST',
                headers: {
                  'X-Requested-With': 'XMLHttpRequest',
                  'Accept': 'application/json',
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                  performa_type: perfVal,
                  remarks: `Stock issued with authority number: ${authNo}`
                }),
                credentials: 'same-origin'
              })
                .then(response => response.json())
                .then(data => {
                  if (data.success) {
                    // Update badge color without page reload
                    const approvalId = window.currentApprovalId;
                    if (approvalId) {
                      // Find the row for this approval
                      const viewLinks = document.querySelectorAll(`a[href*="/approvals/${approvalId}"]`);
                      let approvalRow = null;

                      for (let link of viewLinks) {
                        approvalRow = link.closest('tr');
                        if (approvalRow) break;
                      }

                      if (approvalRow) {
                        const badge = approvalRow.querySelector('.performa-badge');
                        if (badge) {
                          const performaType = data.approval?.performa_type || perfVal;
                          // waiting_for_authority removed - no need to check

                          // Update badge text and color
                          let typeLabel = '';
                          if (performaType === 'work_performa') {
                            typeLabel = 'Work Performa';
                          } else if (performaType === 'maint_performa') {
                            typeLabel = 'Maint Performa';
                          } else {
                            typeLabel = performaType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                          }

                          badge.textContent = typeLabel;

                          // Use performa column colors (original colors) for badge
                          const performaColors = {
                            'work_performa': '#60a5fa', // Light Blue
                            'maint_performa': '#eab308', // Dark Yellow
                            'work_priced_performa': '#9333ea', // Purple
                            'maint_priced_performa': '#ea580c', // Dark Orange
                            'product_na': '#000000' // Black
                          };
                          badge.style.backgroundColor = performaColors[performaType] || performaColors['work_performa'];
                          badge.style.width = '140px';
                          badge.style.height = '32px';
                          badge.style.padding = '0';
                          badge.style.display = 'inline-flex';
                          badge.style.alignItems = 'center';
                          badge.style.justifyContent = 'center';
                          badge.style.fontSize = '11px';
                          badge.style.fontWeight = '700';
                          badge.style.color = '#ffffff';
                          badge.style.setProperty('color', '#ffffff', 'important');
                        }
                      }
                    }
                  }
                })
                .catch(err => console.error('Error updating approval:', err));
            }

            alert(`Successfully issued stock for ${productName} (${issueQty} units)!`);
            bootstrap.Modal.getInstance(document.getElementById('addStockModal')).hide();
            // Reload the page to refresh stock quantities
            window.location.reload();
          } else {
            alert(`Failed to issue stock. Please try again.`);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error issuing stock: ' + error.message);
        })
        .finally(() => {
          // Reset submit button state
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i data-feather="check-circle"></i> Submit';
            feather.replace();
          }
        });
    };

    // Initialize event listeners on page load
    document.addEventListener('DOMContentLoaded', function () {
      console.log('Page loaded, filters initialized');

      // Verify form exists
      const form = document.getElementById('approvalsFiltersForm');
      if (form) {
        console.log('Filter form found');
      } else {
        console.error('Filter form NOT found!');
        return;
      }

      // Attach event listener to search input (instant response)
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        console.log('Search input found, attaching event listener');
        searchInput.addEventListener('input', handleApprovalsSearchInput);
        searchInput.addEventListener('keydown', function (e) {
          // Prevent Enter key from submitting form
          if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            // Cancel timeout and search immediately
            if (approvalsSearchTimeout) {
              clearTimeout(approvalsSearchTimeout);
            }
            loadApprovals();
          }
        });
      } else {
        console.error('Search input NOT found!');
      }

      // Attach event listener to date input
      const dateInput = form.querySelector('input[name="complaint_date"]');
      if (dateInput) {
        dateInput.addEventListener('change', submitApprovalsFilters);
      }

      // Attach event listener to end date input
      const endDateInput = form.querySelector('input[name="date_to"]');
      if (endDateInput) {
        endDateInput.addEventListener('change', submitApprovalsFilters);
      }

      // Attach event listener to category select
      const categorySelect = form.querySelector('select[name="category"]');
      if (categorySelect) {
        categorySelect.addEventListener('change', submitApprovalsFilters);
      }
    });

    // Load Approvals via AJAX
    function loadApprovals(url = null) {
      const form = document.getElementById('approvalsFiltersForm');
      if (!form) {
        console.error('Filter form not found');
        return;
      }

      const params = new URLSearchParams();

      if (url) {
        // If URL is provided, extract params from it
        const urlObj = new URL(url, window.location.origin);
        urlObj.searchParams.forEach((value, key) => {
          params.append(key, value);
        });
      } else {
        // Get all form inputs and build params
        const inputs = form.querySelectorAll('input[name], select[name], textarea[name]');
        inputs.forEach(input => {
          const name = input.name;
          if (!name) return;

          if (input.type === 'checkbox' || input.type === 'radio') {
            if (input.checked) {
              params.append(name, input.value);
            }
          } else {
            // Only append non-empty values to preserve other active filters
            if (input.value && input.value.trim() !== '') {
              params.append(name, input.value.trim());
            }
          }
        });
      }

      const tbody = document.getElementById('approvalsTableBody');
      const paginationContainer = document.getElementById('approvalsPagination');

      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
      }

      const fetchUrl = `{{ route('admin.approvals.index') }}?${params.toString()}`;
      console.log('Fetching URL:', fetchUrl);
      console.log('Params:', params.toString());

      // Show loading state
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
      }

      fetch(fetchUrl, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'text/html',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
      })
        .then(response => {
          console.log('Response status:', response.status);

          // Check if response is JSON (AJAX optimized)
          const contentType = response.headers.get('content-type');
          if (contentType && contentType.includes('application/json')) {
            return response.json().then(data => {
              console.log('Received JSON response');
              // Check if there's an error
              if (!response.ok || !data.success) {
                throw new Error(data.error || data.message || 'Error loading approvals');
              }
              return data.html || data;
            });
          }

          if (!response.ok) {
            return response.text().then(text => {
              throw new Error(`HTTP error! status: ${response.status}`);
            });
          }

          return response.text();
        })
        .then(html => {
          console.log('Received HTML length:', html.length);

          // Try to parse the HTML
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');

          // Check for errors in parsing
          const parserError = doc.querySelector('parsererror');
          if (parserError) {
            console.error('Parser error:', parserError.textContent);
            throw new Error('Failed to parse response HTML');
          }

          const newTbody = doc.querySelector('#approvalsTableBody');
          const newPagination = doc.querySelector('#approvalsPagination');
          const newTfoot = doc.querySelector('#approvalsTableFooter');

          console.log('Found newTbody:', !!newTbody);
          console.log('Found newPagination:', !!newPagination);
          console.log('Found newTfoot:', !!newTfoot);

          if (newTbody && tbody) {
            tbody.innerHTML = newTbody.innerHTML;
            feather.replace();
            // Re-initialize performa badges and status old values after table refresh
            if (typeof initPerformaBadges === 'function') {
              initPerformaBadges();
            }
            // Run initStatusSelects after initPerformaBadges to ensure correct values
            setTimeout(function () {
              if (typeof initStatusSelects === 'function') {
                initStatusSelects();
              }
            }, 50);
            console.log('Table updated successfully');
          } else {
            console.error('Table body not found in response');
            // Try fallback - check if entire page was returned
            if (html.includes('approvalsTableBody')) {
              console.log('Found table body in HTML, trying direct extraction');
              const tempDiv = document.createElement('div');
              tempDiv.innerHTML = html;
              const extractedTbody = tempDiv.querySelector('#approvalsTableBody');
              if (extractedTbody && tbody) {
                tbody.innerHTML = extractedTbody.innerHTML;
                feather.replace();
                if (typeof initPerformaBadges === 'function') {
                  initPerformaBadges();
                }
                // Run initStatusSelects after initPerformaBadges to ensure correct values
                setTimeout(function () {
                  if (typeof initStatusSelects === 'function') {
                    initStatusSelects();
                  }
                }, 50);
                console.log('Table updated via direct extraction');
              } else {
                throw new Error('Could not find table body in response');
              }
            } else {
              throw new Error('Response does not contain expected table structure');
            }
          }

          // Update table footer (total records)
          const tfoot = document.querySelector('#approvalsTableFooter');
          if (newTfoot && tfoot) {
            tfoot.innerHTML = newTfoot.innerHTML;
          } else if (tfoot) {
            const extractedTfoot = doc.querySelector('#approvalsTableFooter') ||
              (html.includes('approvalsTableFooter') ? (() => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                return tempDiv.querySelector('#approvalsTableFooter');
              })() : null);

            if (extractedTfoot) {
              tfoot.innerHTML = extractedTfoot.innerHTML;
            }
          }

          if (newPagination && paginationContainer) {
            paginationContainer.innerHTML = newPagination.innerHTML;
            // Re-initialize feather icons after pagination update
            feather.replace();
          } else if (paginationContainer) {
            const extractedPagination = doc.querySelector('#approvalsPagination') ||
              (html.includes('approvalsPagination') ? (() => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                return tempDiv.querySelector('#approvalsPagination');
              })() : null);

            if (extractedPagination) {
              paginationContainer.innerHTML = extractedPagination.innerHTML;
              // Re-initialize feather icons after pagination update
              feather.replace();
            }
          }

          // Update URL without reloading page
          window.history.pushState({ path: fetchUrl }, '', fetchUrl);
        })
        .catch(error => {
          console.error('Error loading approvals:', error);
          console.error('Error details:', error.message);

          // Show error message to user
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">' +
              '<div class="alert alert-danger mb-0">' +
              '<strong>Error:</strong> ' + (error.message || 'Failed to load approvals. Please try again.') +
              '<br><small>If this persists, please refresh the page.</small>' +
              '</div>' +
              '</td></tr>';
          }

          // Show error notification
          if (typeof showError === 'function') {
            showError(error.message || 'Failed to load approvals');
          } else {
            alert('Error: ' + (error.message || 'Failed to load approvals'));
          }

          // Optionally fallback to regular form submission after a delay
          setTimeout(() => {
            const form = document.getElementById('approvalsFiltersForm');
            if (form && confirm('Would you like to reload the page to see results?')) {
              form.submit();
            }
          }, 3000);
        });
    }

    // Handle pagination clicks
    document.addEventListener('click', function (e) {
      const paginationLink = e.target.closest('#approvalsPagination a');
      if (paginationLink && paginationLink.href && !paginationLink.href.includes('javascript:')) {
        e.preventDefault();
        loadApprovals(paginationLink.href);
      }
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function (e) {
      if (e.state && e.state.path) {
        loadApprovals(e.state.path);
      } else {
        loadApprovals();
      }
    });

    // displayApprovalDetails and submitApprovedQuantities functions removed - using show.blade.php page instead

    // Duplicate approveRequest and rejectRequest functions removed - already defined above

    // Utility Functions
    function showSuccess(message) {
      // Remove any existing alerts first
      const existingAlerts = document.querySelectorAll('.custom-alert-toast');
      existingAlerts.forEach(alert => alert.remove());

      // Create and show success alert
      const alertDiv = document.createElement('div');
      alertDiv.className = 'custom-alert-toast alert-success-toast';
      alertDiv.style.cssText = `
              position: fixed;
              top: 20px;
              right: 20px;
              z-index: 10000;
              min-width: 320px;
              max-width: 450px;
              background: linear-gradient(135deg, #10b981 0%, #059669 100%);
              color: white;
              padding: 16px 20px;
              border-radius: 12px;
              box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3), 0 4px 10px rgba(0, 0, 0, 0.2);
              display: flex;
              align-items: center;
              gap: 12px;
              animation: slideInRight 0.3s ease-out;
              font-size: 14px;
              font-weight: 500;
            `;
      alertDiv.innerHTML = `
              <div style="flex-shrink: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                  <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
              </div>
              <div style="flex: 1; line-height: 1.5;">
                <strong style="display: block; margin-bottom: 2px; font-size: 15px;">Success!</strong>
                <span style="opacity: 0.95;">${message}</span>
              </div>
              <button type="button" onclick="this.parentElement.remove()" style="
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                width: 24px;
                height: 24px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0;
                flex-shrink: 0;
                transition: background 0.2s;
              " onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            `;
      document.body.appendChild(alertDiv);

      // Auto remove after 3 seconds
      setTimeout(() => {
        if (alertDiv.parentNode) {
          alertDiv.style.animation = 'fadeOut 0.3s ease-in forwards';
          setTimeout(() => {
            if (alertDiv.parentNode) {
              alertDiv.parentNode.removeChild(alertDiv);
            }
          }, 300);
        }
      }, 3000);
    }

    function showError(message) {
      // Remove any existing alerts first
      const existingAlerts = document.querySelectorAll('.custom-alert-toast');
      existingAlerts.forEach(alert => alert.remove());

      // Create and show error alert
      const alertDiv = document.createElement('div');
      alertDiv.className = 'custom-alert-toast alert-error-toast';
      alertDiv.style.cssText = `
              position: fixed;
              top: 20px;
              right: 20px;
              z-index: 10000;
              min-width: 320px;
              max-width: 450px;
              background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
              color: white;
              padding: 16px 20px;
              border-radius: 12px;
              box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3), 0 4px 10px rgba(0, 0, 0, 0.2);
              display: flex;
              align-items: center;
              gap: 12px;
              animation: slideInRight 0.3s ease-out, shake 0.5s ease-in-out 0.3s;
              font-size: 14px;
              font-weight: 500;
            `;
      alertDiv.innerHTML = `
              <div style="flex-shrink: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="12" y1="8" x2="12" y2="12"></line>
                  <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
              </div>
              <div style="flex: 1; line-height: 1.5;">
                <strong style="display: block; margin-bottom: 2px; font-size: 15px;">Error!</strong>
                <span style="opacity: 0.95;">${message}</span>
              </div>
              <button type="button" onclick="this.parentElement.remove()" style="
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                width: 24px;
                height: 24px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0;
                flex-shrink: 0;
                transition: background 0.2s;
              " onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            `;
      document.body.appendChild(alertDiv);

      // Auto remove after 6 seconds (slightly longer for errors)
      setTimeout(() => {
        if (alertDiv.parentNode) {
          alertDiv.style.animation = 'fadeOut 0.3s ease-in forwards';
          setTimeout(() => {
            if (alertDiv.parentNode) {
              alertDiv.parentNode.removeChild(alertDiv);
            }
          }, 300);
        }
      }, 6000);
    }

    // Removed duplicate status change handler (handled by comprehensive handler below)
    // Event delegation for approval/reject buttons removed as per user request

    // Status colors mapping for JavaScript
    const statusColors = {
      'in_progress': { bg: '#dc2626', text: '#ffffff', border: '#b91c1c' }, // Darker Red
      'resolved': { bg: '#64748b', text: '#ffffff', border: '#475569' }, // Grey (swapped from green)
      'work_performa': { bg: '#dc2626', text: '#ffffff', border: '#b91c1c' }, // Red (like product_na)
      'maint_performa': { bg: '#dc2626', text: '#ffffff', border: '#b91c1c' }, // Red (like product_na)
      'work_priced_performa': { bg: '#dc2626', text: '#ffffff', border: '#b91c1c' }, // Red (like product_na)
      'maint_priced_performa': { bg: '#dc2626', text: '#ffffff', border: '#b91c1c' }, // Red (like product_na)
      'product_na': { bg: '#dc2626', text: '#ffffff', border: '#b91c1c' }, // Red
      'un_authorized': { bg: '#ec4899', text: '#ffffff', border: '#db2777' }, // Pink
      'pertains_to_ge_const_isld': { bg: '#06b6d4', text: '#ffffff', border: '#0891b2' }, // Aqua/Cyan
      'assigned': { bg: '#16a34a', text: '#ffffff', border: '#15803d' }, // Green (swapped from grey)
      'barak_damages': { bg: '#808000', text: '#ffffff', border: '#666600' }, // Olive
    };

    // Function to update status select box colors
    function updateStatusSelectColor(select, status) {
      const normalizedStatus = status === 'in-process' || status === 'in process' ? 'in_progress' : status;
      // If status is in_progress or any performa type, always use red color
      const performaTypes = ['in_progress', 'work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa', 'product_na'];
      // If status is a performa type, use red color; otherwise use the status color or default to assigned
      const color = performaTypes.includes(normalizedStatus) ? statusColors['in_progress'] : (statusColors[normalizedStatus] || statusColors['assigned']);
      select.style.backgroundColor = color.bg;
      select.style.color = '#ffffff';
      select.style.setProperty('color', '#ffffff', 'important');
      select.style.borderColor = color.border;
      select.setAttribute('data-status-color', normalizedStatus);
      // Update the small status indicator dot next to the select
      const td = select.closest('td');
      if (td) {
        const dot = td.querySelector('.status-indicator');
        const chip = td.querySelector('.status-chip');
        if (dot) {
          dot.style.backgroundColor = color.bg;
          dot.style.borderColor = color.border;
        }
        if (chip) {
          chip.style.backgroundColor = color.bg;
          chip.style.color = '#ffffff';
          chip.style.setProperty('color', '#ffffff', 'important');
          chip.style.borderColor = color.border;
        }
      }
    }

    // Handle Performa Required dropdown changes
    document.addEventListener('change', function (e) {
      if (e.target.classList.contains('status-select')) {
        const select = e.target;
        let newStatus = (select.value || '').toString().trim();
        const row = select.closest('tr');
        const performaBadge = row ? row.querySelector('.performa-badge') : null;
        const complaintId = select.getAttribute('data-complaint-id');
        let skipConfirm = false;

        // Normalize common variants to backend values
        const normalize = (val) => {
          const v = (val || '').toString().trim().toLowerCase();
          if (v === 'in-process' || v === 'in process' || v === 'inprocess') return 'in_progress';
          if (v === 'addressed' || v === 'done' || v === 'completed') return 'resolved';
          if (v === 'assign' || v === 'assignment') return 'assigned';
          return v;
        };
        newStatus = normalize(newStatus);

        // Store original status before any modifications for special options
        let originalStatusForSpecialOption = null;

        // Handle work_performa and maint_performa - update badge and save to approval
        if (newStatus === 'work_performa' || newStatus === 'maint_performa') {
          if (performaBadge) {
            // Performa column colors (original colors for badges)
            const performaColors = {
              'work_performa': '#60a5fa', // Light Blue
              'maint_performa': '#eab308', // Dark Yellow
              'work_priced_performa': '#9333ea', // Purple
              'maint_priced_performa': '#ea580c', // Dark Orange
              'product_na': '#000000' // Black
            };

            if (newStatus === 'work_performa') {
              performaBadge.textContent = 'Work Performa';
              performaBadge.style.backgroundColor = performaColors['work_performa'];
              performaBadge.style.width = '140px';
              performaBadge.style.height = '32px';
              performaBadge.style.padding = '0';
              performaBadge.style.display = 'inline-flex';
              performaBadge.style.alignItems = 'center';
              performaBadge.style.justifyContent = 'center';
              performaBadge.style.fontSize = '11px';
              performaBadge.style.fontWeight = '700';
              performaBadge.style.color = '#ffffff';
              performaBadge.style.setProperty('color', '#ffffff', 'important');
              // Update select box color to red (for status column)
              updateStatusSelectColor(select, 'work_performa');
            } else if (newStatus === 'maint_performa') {
              performaBadge.textContent = 'Maintenance Performa';
              performaBadge.style.backgroundColor = performaColors['maint_performa'];
              performaBadge.style.width = '140px';
              performaBadge.style.height = '32px';
              performaBadge.style.padding = '0';
              performaBadge.style.display = 'inline-flex';
              performaBadge.style.alignItems = 'center';
              performaBadge.style.justifyContent = 'center';
              performaBadge.style.fontSize = '11px';
              performaBadge.style.fontWeight = '700';
              performaBadge.style.color = '#ffffff';
              performaBadge.style.setProperty('color', '#ffffff', 'important');
              // Update select box color to red (for status column)
              updateStatusSelectColor(select, 'maint_performa');
            }
            if (performaBadge) {
              performaBadge.style.width = '140px';
              performaBadge.style.height = '32px';
              performaBadge.style.padding = '0';
              performaBadge.style.display = 'inline-flex';
              performaBadge.style.alignItems = 'center';
              performaBadge.style.justifyContent = 'center';
              performaBadge.style.fontSize = '11px';
            }
          }

          // Get approval ID and save performa_type
          const row = select.closest('tr');
          let approvalId = null;
          if (row) {
            const addStockBtn = row.querySelector('button[data-approval-id]');
            if (addStockBtn) {
              approvalId = addStockBtn.getAttribute('data-approval-id');
            }
          }

          // Save performa_type to approval if approvalId exists
          if (approvalId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
              fetch(`/admin/approvals/${approvalId}/save-performa`, {
                method: 'POST',
                headers: {
                  'X-Requested-With': 'XMLHttpRequest',
                  'Accept': 'application/json',
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                  performa_type: newStatus,
                  remarks: `Performa type selected: ${newStatus}`
                }),
                credentials: 'same-origin'
              })
                .then(response => response.json())
                .then(data => {
                  if (data.success) {
                    console.log('Performa type saved:', newStatus);
                  } else {
                    console.error('Failed to save performa type:', data.message);
                  }
                })
                .catch(error => {
                  console.error('Error saving performa type:', error);
                });
            }
          }

          // Persist selection locally so it survives reloads
          if (complaintId) {
            const key = `performaRequired:${complaintId}`;
            const val = newStatus === 'work_performa' ? 'work' : 'maint';
            try { localStorage.setItem(key, val); } catch (err) { }
          }

          // Update complaint status to in_progress but keep performa badge
          const performaType = newStatus; // Store original performa type
          newStatus = 'in_progress';
          // Keep dropdown value as selected performa type but status will be in_progress
          // Don't change select.value - keep it as work_performa or maint_performa
          updateStatusSelectColor(select, performaType); // Apply performa color
          skipConfirm = true;
          showSuccess(performaBadge?.textContent || 'Performa marked');
        } else if (newStatus === 'work_priced_performa' || newStatus === 'maint_priced_performa' || newStatus === 'product_na') {
          // Store original status before changing it for localStorage and specialOptionType
          originalStatusForSpecialOption = newStatus;
          const originalStatus = newStatus;

          // Handle Work Performa Priced, Maintenance Performa Priced and Product N/A options
          if (performaBadge) {
            if (newStatus === 'work_priced_performa') {
              performaBadge.textContent = 'Work Performa Priced';
              performaBadge.style.backgroundColor = '#9333ea';
              performaBadge.style.width = '140px';
              performaBadge.style.height = '32px';
              performaBadge.style.padding = '0';
              performaBadge.style.display = 'inline-flex';
              performaBadge.style.alignItems = 'center';
              performaBadge.style.justifyContent = 'center';
              performaBadge.style.fontSize = '11px';
              performaBadge.style.fontWeight = '700';
              performaBadge.style.color = '#ffffff';
              performaBadge.style.setProperty('color', '#ffffff', 'important');
              // Keep actual status as work_priced_performa (don't change to in_progress)
              select.value = 'work_priced_performa';
              updateStatusSelectColor(select, 'work_priced_performa'); // Apply purple color for work_priced_performa
              // Keep newStatus as work_priced_performa to send correct status to server
              newStatus = 'work_priced_performa';
            } else if (newStatus === 'maint_priced_performa') {
              performaBadge.textContent = 'Maintenance Performa Priced';
              performaBadge.style.backgroundColor = '#ea580c';
              performaBadge.style.width = '140px';
              performaBadge.style.height = '32px';
              performaBadge.style.padding = '0';
              performaBadge.style.display = 'inline-flex';
              performaBadge.style.alignItems = 'center';
              performaBadge.style.justifyContent = 'center';
              performaBadge.style.fontSize = '11px';
              performaBadge.style.fontWeight = '700';
              performaBadge.style.color = '#ffffff';
              performaBadge.style.setProperty('color', '#ffffff', 'important');
              // Keep actual status as maint_priced_performa (don't change to in_progress)
              select.value = 'maint_priced_performa';
              updateStatusSelectColor(select, 'maint_priced_performa'); // Apply orange color for maint_priced_performa
              // Keep newStatus as maint_priced_performa to send correct status to server
              newStatus = 'maint_priced_performa';
            } else if (newStatus === 'product_na') {
              performaBadge.textContent = 'Product N/A';
              performaBadge.style.backgroundColor = '#000000';
              performaBadge.style.width = '140px';
              performaBadge.style.height = '32px';
              performaBadge.style.padding = '0';
              performaBadge.style.display = 'inline-flex';
              performaBadge.style.alignItems = 'center';
              performaBadge.style.justifyContent = 'center';
              performaBadge.style.fontSize = '11px';
              performaBadge.style.fontWeight = '700';
              performaBadge.style.color = '#ffffff';
              performaBadge.style.setProperty('color', '#ffffff', 'important');
              // Keep status as product_na (don't change to in_progress)
              select.value = 'product_na';
              updateStatusSelectColor(select, 'product_na'); // Apply black color for product_na
              // newStatus remains 'product_na' - don't change it
            }
          }

          // Save performa_type to approval if status is work_priced_performa or maint_priced_performa
          if (newStatus === 'work_priced_performa' || newStatus === 'maint_priced_performa') {
            const row = select.closest('tr');
            let approvalId = null;
            if (row) {
              const addStockBtn = row.querySelector('button[data-approval-id]');
              if (addStockBtn) {
                approvalId = addStockBtn.getAttribute('data-approval-id');
              }
            }

            // Save performa_type to approval if approvalId exists
            if (approvalId) {
              const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
              if (csrfToken) {
                fetch(`/admin/approvals/${approvalId}/save-performa`, {
                  method: 'POST',
                  headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                  },
                  body: JSON.stringify({
                    performa_type: newStatus,
                    remarks: `Performa type selected: ${newStatus}`
                  }),
                  credentials: 'same-origin'
                })
                  .then(response => response.json())
                  .then(data => {
                    if (data.success) {
                      console.log('Performa type saved:', newStatus);
                    } else {
                      console.error('Failed to save performa type:', data.message);
                    }
                  })
                  .catch(error => {
                    console.error('Error saving performa type:', error);
                  });
              }
            }
          }

          // Persist selection locally - use originalStatus before it was changed
          if (complaintId) {
            const key = `performaRequired:${complaintId}`;
            const val = originalStatus === 'work_priced_performa' ? 'work_priced' : (originalStatus === 'maint_priced_performa' ? 'maint_priced' : 'product_na');
            try { localStorage.setItem(key, val); } catch (err) { }
          }
          skipConfirm = true;
          showSuccess(performaBadge?.textContent || 'Option marked');
        } else {
          // Update color for regular status changes
          updateStatusSelectColor(select, newStatus);
        }

        // Real statuses only - include all possible statuses
        const allowed = ['new', 'assigned', 'in_progress', 'resolved', 'closed', 'un_authorized', 'pertains_to_ge_const_isld', 'barak_damages', 'product_na', 'work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa'];
        if (!allowed.includes(newStatus)) {
          console.warn('Blocked unsupported status:', newStatus);
          // Revert to old on invalid
          const oldStatusLocal = select.dataset.oldStatus || 'assigned';
          select.value = oldStatusLocal;
          showError('Unsupported status selected.');
          return;
        }

        // Clear persisted performa flag when switching to real statuses (but not for product_na, priced_performa, work_performa, or maint_performa)
        // Check localStorage to determine if this is a special option (since dropdown value is now 'in_progress')
        let savedOptionForClear = null;
        if (complaintId) {
          try { savedOptionForClear = localStorage.getItem(`performaRequired:${complaintId}`); } catch (err) { savedOptionForClear = null; }
        }
        const isSpecialOption = savedOptionForClear === 'work' || savedOptionForClear === 'maint' || savedOptionForClear === 'work_priced' || savedOptionForClear === 'maint_priced' || savedOptionForClear === 'product_na' ||
          newStatus === 'work_performa' || newStatus === 'maint_performa' || newStatus === 'work_priced_performa' || newStatus === 'maint_priced_performa' || newStatus === 'product_na' ||
          originalStatusForSpecialOption === 'product_na';

        // Clear Performa Required badge if switching to un_authorized or pertains_to_ge_const_isld
        if (performaBadge && (newStatus === 'un_authorized' || newStatus === 'pertains_to_ge_const_isld')) {
          performaBadge.style.display = 'none';
          performaBadge.textContent = '';
          // Clear localStorage for these statuses
          if (complaintId) {
            try { localStorage.removeItem(`performaRequired:${complaintId}`); } catch (err) { }
          }
        } else if (performaBadge && complaintId && !isSpecialOption) {
          let savedFlag = null;
          try { savedFlag = localStorage.getItem(`performaRequired:${complaintId}`); } catch (err) { savedFlag = null; }
          if (!savedFlag) {
            performaBadge.style.display = 'none';
            performaBadge.textContent = '';
          } else {
            // Ensure correct styling if persisted
            if (savedFlag === 'work') {
              performaBadge.textContent = 'Work Performa Required';
              performaBadge.style.backgroundColor = '#60a5fa';
              performaBadge.style.width = '140px';
              performaBadge.style.height = '32px';
              performaBadge.style.padding = '0';
              performaBadge.style.display = 'inline-flex';
              performaBadge.style.alignItems = 'center';
              performaBadge.style.justifyContent = 'center';
              performaBadge.style.fontSize = '11px';
              performaBadge.style.fontWeight = '700';
              performaBadge.style.color = '#ffffff';
              performaBadge.style.setProperty('color', '#ffffff', 'important');
            } else if (savedFlag === 'maint') {
              performaBadge.textContent = 'Maintenance Performa Required';
              performaBadge.style.backgroundColor = '#eab308';
              performaBadge.style.width = '140px';
              performaBadge.style.height = '32px';
              performaBadge.style.padding = '0';
              performaBadge.style.display = 'inline-flex';
              performaBadge.style.alignItems = 'center';
              performaBadge.style.justifyContent = 'center';
              performaBadge.style.fontSize = '11px';
              performaBadge.style.fontWeight = '700';
              performaBadge.style.color = '#ffffff';
              performaBadge.style.setProperty('color', '#ffffff', 'important');
            } else if (savedFlag === 'work_priced') {
              performaBadge.textContent = 'Work Performa Priced';
              performaBadge.style.backgroundColor = '#9333ea';
              performaBadge.style.width = '140px';
              performaBadge.style.height = '32px';
              performaBadge.style.padding = '0';
              performaBadge.style.display = 'inline-flex';
              performaBadge.style.alignItems = 'center';
              performaBadge.style.justifyContent = 'center';
              performaBadge.style.fontSize = '11px';
              performaBadge.style.fontWeight = '700';
              performaBadge.style.color = '#ffffff';
              performaBadge.style.setProperty('color', '#ffffff', 'important');
            } else if (savedFlag === 'maint_priced') {
              performaBadge.textContent = 'Maintenance Performa Priced';
              performaBadge.style.backgroundColor = '#ea580c';
              performaBadge.style.width = '140px';
              performaBadge.style.height = '32px';
              performaBadge.style.padding = '0';
              performaBadge.style.display = 'inline-flex';
              performaBadge.style.alignItems = 'center';
              performaBadge.style.justifyContent = 'center';
              performaBadge.style.fontSize = '11px';
              performaBadge.style.fontWeight = '700';
              performaBadge.style.color = '#ffffff';
              performaBadge.style.setProperty('color', '#ffffff', 'important');
            } else if (savedFlag === 'product_na') {
              performaBadge.textContent = 'Product N/A';
              performaBadge.style.backgroundColor = '#000000';
              performaBadge.style.width = '140px';
              performaBadge.style.height = '32px';
              performaBadge.style.padding = '0';
              performaBadge.style.display = 'inline-flex';
              performaBadge.style.alignItems = 'center';
              performaBadge.style.justifyContent = 'center';
              performaBadge.style.fontSize = '11px';
              performaBadge.style.fontWeight = '700';
              performaBadge.style.color = '#ffffff';
              performaBadge.style.setProperty('color', '#ffffff', 'important');
            }
            if (performaBadge) {
              performaBadge.style.width = '140px';
              performaBadge.style.height = '32px';
              performaBadge.style.padding = '0';
              performaBadge.style.display = 'inline-flex';
              performaBadge.style.alignItems = 'center';
              performaBadge.style.justifyContent = 'center';
              performaBadge.style.fontSize = '11px';
            }
          }
        }

        if (complaintId && !isSpecialOption && newStatus !== 'un_authorized' && newStatus !== 'pertains_to_ge_const_isld') {
          try { localStorage.removeItem(`performaRequired:${complaintId}`); } catch (err) { }
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!complaintId || !csrfToken) {
          console.error('Missing complaintId or CSRF token');
          return;
        }

        const oldStatus = select.dataset.oldStatus || select.value;
        const labelMap = { in_progress: 'In Progress', resolved: 'Addressed', assigned: 'Assigned', new: 'New', closed: 'Closed' };
        const confirmMsg = `Are you sure you want to change status to "${labelMap[newStatus] || newStatus}"?`;
        if (!skipConfirm) {
          if (!confirm(confirmMsg)) {
            select.value = oldStatus;
            return;
          }
        }

        // Preserve color if special options are selected (check localStorage to determine which special option)
        let savedOption = null;
        if (complaintId) {
          try { savedOption = localStorage.getItem(`performaRequired:${complaintId}`); } catch (err) { savedOption = null; }
        }
        // Determine special option type based on localStorage, originalStatusForSpecialOption, or previous selection
        const preserveColor = savedOption === 'work' || savedOption === 'maint' || savedOption === 'work_priced' || savedOption === 'maint_priced' || savedOption === 'product_na' ||
          newStatus === 'work_performa' || newStatus === 'maint_performa' || newStatus === 'work_priced_performa' || newStatus === 'maint_priced_performa' || newStatus === 'product_na' ||
          originalStatusForSpecialOption === 'product_na';
        // Store the special option type to restore color after fetch
        let specialOptionType = null;
        if (newStatus === 'work_performa' || savedOption === 'work') {
          specialOptionType = 'work_performa';
        } else if (newStatus === 'maint_performa' || savedOption === 'maint') {
          specialOptionType = 'maint_performa';
        } else if (newStatus === 'work_priced_performa' || savedOption === 'work_priced') {
          specialOptionType = 'work_priced_performa';
        } else if (newStatus === 'maint_priced_performa' || savedOption === 'maint_priced') {
          specialOptionType = 'maint_priced_performa';
        } else if (newStatus === 'product_na' || savedOption === 'product_na' || originalStatusForSpecialOption === 'product_na') {
          specialOptionType = 'product_na';
        }

        select.style.opacity = '0.6';
        select.disabled = true;

        fetch(`/admin/approvals/complaints/${complaintId}/update-status`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ status: newStatus, notes: `Status updated from approvals view` })
        })
          .then(async (response) => {
            const contentType = response.headers.get('content-type') || '';
            const isJson = contentType.includes('application/json');
            const data = isJson ? await response.json() : null;
            if (!response.ok) {
              const message = (data && (data.message || (data.errors && Object.values(data.errors)[0]?.[0]))) || `HTTP ${response.status}`;
              throw new Error(message);
            }
            return data;
          })
          .then(data => {
            const updated = data && data.complaint ? data.complaint : null;

            // If product_na was selected, update approval record with performa_type
            if (specialOptionType === 'product_na' && complaintId) {
              // Get approval ID from the row's view button
              const viewBtn = row?.querySelector('button[onclick*="viewApproval"]');
              let approvalId = null;
              if (viewBtn) {
                const onclickAttr = viewBtn.getAttribute('onclick');
                const match = onclickAttr?.match(/viewApproval\((\d+)\)/);
                approvalId = match ? match[1] : null;
              }

              // Alternative: get from data-approval-id attribute if available
              if (!approvalId) {
                const addStockBtn = row?.querySelector('button[data-approval-id]');
                if (addStockBtn) {
                  approvalId = addStockBtn.getAttribute('data-approval-id');
                }
              }

              // Alternative: try to get from all buttons in the row
              if (!approvalId) {
                const allButtons = row?.querySelectorAll('button');
                if (allButtons) {
                  for (const btn of allButtons) {
                    const onclickAttr = btn.getAttribute('onclick');
                    if (onclickAttr && onclickAttr.includes('viewApproval')) {
                      const match = onclickAttr.match(/viewApproval\((\d+)\)/);
                      if (match) {
                        approvalId = match[1];
                        break;
                      }
                    }
                    const dataApprovalId = btn.getAttribute('data-approval-id');
                    if (dataApprovalId) {
                      approvalId = dataApprovalId;
                      break;
                    }
                  }
                }
              }

              // If approval ID found, update approval record
              if (approvalId) {
                console.log('Updating approval record with product_na performa_type. Approval ID:', approvalId);
                const updateCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch(`/admin/approvals/${approvalId}/update-performa-type`, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': updateCsrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                  },
                  body: JSON.stringify({ performa_type: 'product_na' })
                })
                  .then(response => {
                    if (!response.ok) {
                      throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                  })
                  .then(updateData => {
                    if (updateData.success) {
                      console.log('Approval record updated successfully with product_na performa_type');
                      // Reload the page to reflect the changes
                      setTimeout(() => {
                        window.location.reload();
                      }, 500);
                    } else {
                      console.error('Failed to update approval record:', updateData.message || 'Unknown error');
                      alert('Failed to update approval record: ' + (updateData.message || 'Unknown error'));
                    }
                  })
                  .catch(error => {
                    console.error('Error updating approval record:', error);
                    alert('Error updating approval record: ' + error.message);
                  });
              } else {
                console.warn('Approval ID not found for complaint:', complaintId, 'Row:', row);
                alert('Approval ID not found. Please refresh the page and try again.');
              }
            }

            // Handle resolved status - hide performa badge and update UI
            if (newStatus === 'resolved') {
              // Update addressed date cell
              const addressedDateCell = row?.querySelector('td:nth-child(3)');
              if (addressedDateCell) {
                if (updated && updated.closed_at) {
                  addressedDateCell.textContent = updated.closed_at;
                  addressedDateCell.style.textAlign = 'left';
                } else {
                  addressedDateCell.innerHTML = '<span style="display: block; text-align: center;">-</span>';
                }
              }

              // Hide performa badge immediately (without reload)
              const performaBadgeInRow = row?.querySelector('.performa-badge');
              if (performaBadgeInRow) {
                performaBadgeInRow.style.display = 'none';
                performaBadgeInRow.textContent = '';
              }

              // Clear performa_type from approval record
              const addStockBtn = row?.querySelector('button[data-approval-id]');
              const approvalId = addStockBtn ? addStockBtn.getAttribute('data-approval-id') : null;
              if (approvalId) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (csrfToken) {
                  fetch(`/admin/approvals/${approvalId}/save-performa`, {
                    method: 'POST',
                    headers: {
                      'X-Requested-With': 'XMLHttpRequest',
                      'Accept': 'application/json',
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                      performa_type: null,
                      remarks: `Performa type cleared - status changed to addressed`
                    }),
                    credentials: 'same-origin'
                  })
                    .then(response => response.json())
                    .then(data => {
                      if (data.success) {
                        console.log('Performa type cleared from approval for addressed status');
                      }
                    })
                    .catch(error => {
                      console.error('Error clearing performa type:', error);
                    });
                }
              }

              // Clear localStorage
              if (complaintId) {
                try { localStorage.removeItem(`performaRequired:${complaintId}`); } catch (err) { }
              }

              // Update status display - replace select with Addressed badge
              const statusCell = select.closest('td');
              // Remove arrow and circle if they exist
              const arrow = statusCell?.querySelector('i[data-feather="chevron-down"]');
              const circle = statusCell?.querySelector('.status-indicator');
              if (arrow) arrow.remove();
              if (circle) circle.remove();
              // Create simple Addressed badge without dropdown
              const badge = document.createElement('span');
              badge.className = 'badge';
              const resolvedColor = statusColors['resolved'];
              badge.style.cssText = `background-color: ${resolvedColor.bg}; color: #ffffff !important; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 4px; border: 1px solid ${resolvedColor.border}; width: 140px; height: 32px; display: inline-flex; align-items: center; justify-content: center;`;
              badge.style.setProperty('color', '#ffffff', 'important');
              badge.textContent = 'Addressed';
              // Replace select with badge
              const statusChip = select.closest('.status-chip');
              if (statusChip) {
                statusChip.innerHTML = '';
                statusChip.appendChild(badge);
                statusChip.style.cssText = `background-color: ${resolvedColor.bg}; color: ${resolvedColor.text}; border-color: ${resolvedColor.border}; width: 140px; height: 32px; justify-content: center;`;
              } else {
                select.replaceWith(badge);
              }

              // Show feedback button automatically when status becomes resolved
              const actionsCell = row?.querySelector('td:last-child');
              if (actionsCell) {
                const btnGroup = actionsCell.querySelector('.btn-group');
                if (btnGroup) {
                  // Check if feedback button already exists (check for onclick with viewFeedbackCreate or viewFeedbackEdit)
                  const existingFeedbackBtn = btnGroup.querySelector('a[onclick*="viewFeedback"]');
                  if (!existingFeedbackBtn && complaintId) {
                    // Create "Add Feedback" button with same structure as existing buttons
                    const feedbackBtn = document.createElement('a');
                    feedbackBtn.href = 'javascript:void(0)';
                    feedbackBtn.setAttribute('onclick', `viewFeedbackCreate(${complaintId})`);
                    feedbackBtn.className = 'btn btn-outline-warning btn-sm';
                    feedbackBtn.title = 'Add Feedback';
                    feedbackBtn.style.cssText = 'padding: 3px 8px; border-color: #f59e0b !important; color: #f59e0b !important;';
                    feedbackBtn.innerHTML = '<i data-feather="message-square" style="width: 16px; height: 16px; color: #f59e0b;"></i>';
                    btnGroup.appendChild(feedbackBtn);
                    // Reinitialize feather icons
                    if (typeof feather !== 'undefined') {
                      feather.replace();
                    }
                  }
                }
              }
            } else {
              // Update color for other status changes
              // For special options, keep their actual status value
              if (specialOptionType) {
                if (specialOptionType === 'product_na') {
                  select.value = 'in_progress';
                  updateStatusSelectColor(select, 'in_progress');
                } else {
                  select.value = 'in_progress';
                  updateStatusSelectColor(select, 'in_progress');
                }
              } else {
                // Check if newStatus is un_authorized or pertains_to_ge_const_isld first - these should always be set
                if (newStatus === 'un_authorized') {
                  // Set un_authorized value and color
                  select.value = 'un_authorized';
                  updateStatusSelectColor(select, 'un_authorized');
                  // Clear performa badge for un_authorized status
                  const performaBadgeInRow = row?.querySelector('.performa-badge');
                  if (performaBadgeInRow) {
                    performaBadgeInRow.style.display = 'none';
                    performaBadgeInRow.textContent = '';
                  }
                  // Clear localStorage
                  if (complaintId) {
                    try { localStorage.removeItem(`performaRequired:${complaintId}`); } catch (err) { }
                  }

                  // Clear performa_type from approval record
                  const addStockBtn = row?.querySelector('button[data-approval-id]');
                  const approvalId = addStockBtn ? addStockBtn.getAttribute('data-approval-id') : null;
                  if (approvalId) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (csrfToken) {
                      fetch(`/admin/approvals/${approvalId}/save-performa`, {
                        method: 'POST',
                        headers: {
                          'X-Requested-With': 'XMLHttpRequest',
                          'Accept': 'application/json',
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                          performa_type: null,
                          remarks: `Performa type cleared - status changed to ${newStatus}`
                        }),
                        credentials: 'same-origin'
                      })
                        .then(response => response.json())
                        .then(data => {
                          if (data.success) {
                            console.log('Performa type cleared from approval for un_authorized');
                          }
                        })
                        .catch(error => {
                          console.error('Error clearing performa type:', error);
                        });
                    }
                  }
                } else if (newStatus === 'pertains_to_ge_const_isld') {
                  // Set pertains_to_ge_const_isld value and color
                  select.value = 'pertains_to_ge_const_isld';
                  updateStatusSelectColor(select, 'pertains_to_ge_const_isld');
                  // Clear performa badge for pertains_to_ge_const_isld status
                  const performaBadgeInRow = row?.querySelector('.performa-badge');
                  if (performaBadgeInRow) {
                    performaBadgeInRow.style.display = 'none';
                    performaBadgeInRow.textContent = '';
                  }
                  // Clear localStorage
                  if (complaintId) {
                    try { localStorage.removeItem(`performaRequired:${complaintId}`); } catch (err) { }
                  }

                  // Clear performa_type from approval record
                  const addStockBtn = row?.querySelector('button[data-approval-id]');
                  const approvalId = addStockBtn ? addStockBtn.getAttribute('data-approval-id') : null;
                  if (approvalId) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (csrfToken) {
                      fetch(`/admin/approvals/${approvalId}/save-performa`, {
                        method: 'POST',
                        headers: {
                          'X-Requested-With': 'XMLHttpRequest',
                          'Accept': 'application/json',
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                          performa_type: null,
                          remarks: `Performa type cleared - status changed to ${newStatus}`
                        }),
                        credentials: 'same-origin'
                      })
                        .then(response => response.json())
                        .then(data => {
                          if (data.success) {
                            console.log('Performa type cleared from approval for pertains_to_ge_const_isld');
                          }
                        })
                        .catch(error => {
                          console.error('Error clearing performa type:', error);
                        });
                    }
                  }
                } else if (newStatus === 'barak_damages') {
                  // Set barak_damages value and color
                  select.value = 'barak_damages';
                  updateStatusSelectColor(select, 'barak_damages');
                  // Clear performa badge for barak_damages status
                  const performaBadgeInRow = row?.querySelector('.performa-badge');
                  if (performaBadgeInRow) {
                    performaBadgeInRow.style.display = 'none';
                    performaBadgeInRow.textContent = '';
                  }
                  // Clear localStorage
                  if (complaintId) {
                    try { localStorage.removeItem(`performaRequired:${complaintId}`); } catch (err) { }
                  }

                  // Clear performa_type from approval record
                  const addStockBtn = row?.querySelector('button[data-approval-id]');
                  const approvalId = addStockBtn ? addStockBtn.getAttribute('data-approval-id') : null;
                  if (approvalId) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (csrfToken) {
                      fetch(`/admin/approvals/${approvalId}/save-performa`, {
                        method: 'POST',
                        headers: {
                          'X-Requested-With': 'XMLHttpRequest',
                          'Accept': 'application/json',
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                          performa_type: null,
                          remarks: `Performa type cleared - status changed to ${newStatus}`
                        }),
                        credentials: 'same-origin'
                      })
                        .then(response => response.json())
                        .then(data => {
                          if (data.success) {
                            console.log('Performa type cleared from approval for barak_damages');
                          }
                        })
                        .catch(error => {
                          console.error('Error clearing performa type:', error);
                        });
                    }
                  }
                } else if (!preserveColor && newStatus !== 'work_performa' && newStatus !== 'maint_performa' && newStatus !== 'work_priced_performa' && newStatus !== 'maint_priced_performa' && newStatus !== 'product_na' && newStatus !== 'in_progress') {
                  // For all other non-performa statuses (assigned, resolved, etc.), hide performa badge
                  const performaBadgeInRow = row?.querySelector('.performa-badge');
                  if (performaBadgeInRow) {
                    performaBadgeInRow.style.display = 'none';
                    performaBadgeInRow.textContent = '';
                  }
                  // Clear localStorage for non-performa statuses
                  if (complaintId) {
                    try { localStorage.removeItem(`performaRequired:${complaintId}`); } catch (err) { }
                  }

                  // Clear performa_type from approval record if status is non-performa
                  const addStockBtn = row?.querySelector('button[data-approval-id]');
                  const approvalId = addStockBtn ? addStockBtn.getAttribute('data-approval-id') : null;
                  if (approvalId) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (csrfToken) {
                      fetch(`/admin/approvals/${approvalId}/save-performa`, {
                        method: 'POST',
                        headers: {
                          'X-Requested-With': 'XMLHttpRequest',
                          'Accept': 'application/json',
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                          performa_type: null,
                          remarks: `Performa type cleared - status changed to ${newStatus}`
                        }),
                        credentials: 'same-origin'
                      })
                        .then(response => response.json())
                        .then(data => {
                          if (data.success) {
                            console.log('Performa type cleared from approval');
                          }
                        })
                        .catch(error => {
                          console.error('Error clearing performa type:', error);
                        });
                    }
                  }

                  // Update select value and color for non-performa statuses
                  select.value = newStatus;
                  updateStatusSelectColor(select, newStatus);
                } else {
                  // Check if current select value is a special option to preserve their colors
                  const currentSelectValue = select.value;
                  if (currentSelectValue === 'product_na') {
                    // For product_na, use in_progress color (like work_performa and maint_performa)
                    select.value = 'in_progress';
                    updateStatusSelectColor(select, 'in_progress');
                  } else if (currentSelectValue === 'work_priced_performa') {
                    updateStatusSelectColor(select, 'work_priced_performa');
                  } else if (currentSelectValue === 'maint_priced_performa') {
                    updateStatusSelectColor(select, 'maint_priced_performa');
                  } else if (currentSelectValue === 'work_performa') {
                    updateStatusSelectColor(select, 'work_performa');
                  } else if (currentSelectValue === 'maint_performa') {
                    updateStatusSelectColor(select, 'maint_performa');
                  } else {
                    select.value = newStatus;
                    updateStatusSelectColor(select, newStatus);
                  }
                }
              }
            }
            showSuccess('Complaint status updated successfully!');
            if (select.isConnected) select.dataset.oldStatus = newStatus;
          })
          .catch(error => {
            console.error('Error updating status:', error);
            select.value = oldStatus;
            showError(error.message || 'Failed to update complaint status.');
          })
          .finally(() => {
            if (select.isConnected && select.value !== 'resolved') {
              select.style.opacity = '1';
              select.disabled = false;
              // Check if newStatus is un_authorized or pertains_to_ge_const_isld first - these should always be set
              if (newStatus === 'un_authorized') {
                // Keep un_authorized value and color
                select.value = 'un_authorized';
                updateStatusSelectColor(select, 'un_authorized');
              } else if (newStatus === 'pertains_to_ge_const_isld') {
                // Keep pertains_to_ge_const_isld value and color
                select.value = 'pertains_to_ge_const_isld';
                updateStatusSelectColor(select, 'pertains_to_ge_const_isld');
              } else if (specialOptionType) {
                // Restore dropdown value - show "In Progress" for all performa types (including priced ones)
                // Actual value is preserved in data-actual-status and will be used on next reload
                if (specialOptionType === 'work_priced_performa' || specialOptionType === 'maint_priced_performa') {
                  // Show "In Progress" in dropdown but keep actual value in data-actual-status
                  select.value = 'in_progress';
                  select.setAttribute('data-actual-status', specialOptionType); // Preserve actual value
                  updateStatusSelectColor(select, 'in_progress');
                } else if (specialOptionType === 'product_na') {
                  select.value = 'in_progress';
                  updateStatusSelectColor(select, 'in_progress');
                } else {
                  // For regular performas (work_performa, maint_performa), use in_progress
                  select.value = 'in_progress';
                  updateStatusSelectColor(select, 'in_progress');
                }
              } else if (preserveColor) {
                const finalSelectValue = select.value;
                if (finalSelectValue === 'product_na') {
                  // For product_na, use in_progress (like work_performa and maint_performa)
                  select.value = 'in_progress';
                  updateStatusSelectColor(select, 'in_progress');
                } else if (finalSelectValue === 'work_priced_performa') {
                  updateStatusSelectColor(select, 'work_priced_performa');
                } else if (finalSelectValue === 'maint_priced_performa') {
                  updateStatusSelectColor(select, 'maint_priced_performa');
                } else if (finalSelectValue === 'work_performa') {
                  updateStatusSelectColor(select, 'work_performa');
                } else if (finalSelectValue === 'maint_performa') {
                  updateStatusSelectColor(select, 'maint_performa');
                }
              }
            }
          });
      }
    });

    // Helpers to initialize UI after load/refresh
    function initPerformaBadges() {
      document.querySelectorAll('.performa-badge').forEach(function (b) {
        // Only hide badges that are truly empty (no textContent and display:none)
        // Don't hide badges that are already rendered from server with content
        if (!b.textContent && b.style.display !== 'none') {
          b.style.display = 'none';
        }
      });
      // Restore persisted Performa Required selections per complaint
      // But only if badge doesn't already have content from server
      document.querySelectorAll('select.status-select[data-complaint-id]').forEach(function (sel) {
        const complaintId = sel.getAttribute('data-complaint-id');
        if (!complaintId) return;

        const row = sel.closest('tr');
        const badge = row ? row.querySelector('.performa-badge') : null;
        if (!badge) return;

        // If badge already has content from server, don't override it
        if (badge.textContent && badge.textContent.trim() !== '') {
          return; // Server-rendered badge, keep it as is
        }

        // Don't restore from localStorage if current status is un_authorized or pertains_to_ge_const_isld
        const currentStatus = sel.value || sel.getAttribute('data-actual-status');
        if (currentStatus === 'un_authorized' || currentStatus === 'pertains_to_ge_const_isld') {
          // Clear localStorage for these statuses
          try { localStorage.removeItem(`performaRequired:${complaintId}`); } catch (err) { }
          // Hide badge for these statuses
          badge.style.display = 'none';
          badge.textContent = '';
          return;
        }

        let saved;
        try { saved = localStorage.getItem(`performaRequired:${complaintId}`); } catch (err) { saved = null; }
        if (!saved) return;

        // Restore based on saved value - check in order to prevent conflicts
        if (saved === 'priced') {
          badge.textContent = 'Maint/Work Priced';
          badge.style.backgroundColor = '#f59e0b';
          badge.style.width = '140px';
          badge.style.height = '32px';
          badge.style.padding = '0';
          badge.style.display = 'inline-flex';
          badge.style.alignItems = 'center';
          badge.style.justifyContent = 'center';
          badge.style.fontSize = '11px';
          badge.style.fontWeight = '700';
          badge.style.color = '#ffffff';
          badge.style.setProperty('color', '#ffffff', 'important');
          sel.value = 'in_progress';
          updateStatusSelectColor(sel, 'in_progress');
        } else if (saved === 'product_na') {
          badge.textContent = 'Product N/A';
          badge.style.backgroundColor = '#000000';
          badge.style.width = '140px';
          badge.style.height = '32px';
          badge.style.padding = '0';
          badge.style.display = 'inline-flex';
          badge.style.alignItems = 'center';
          badge.style.justifyContent = 'center';
          badge.style.fontSize = '11px';
          badge.style.fontWeight = '700';
          badge.style.color = '#ffffff';
          badge.style.setProperty('color', '#ffffff', 'important');
          sel.value = 'in_progress';
          updateStatusSelectColor(sel, 'in_progress');
        } else if (saved === 'work') {
          badge.textContent = 'Work Performa Required';
          badge.style.backgroundColor = '#60a5fa';
          badge.style.width = '140px';
          badge.style.height = '32px';
          badge.style.padding = '0';
          badge.style.display = 'inline-flex';
          badge.style.alignItems = 'center';
          badge.style.justifyContent = 'center';
          badge.style.fontSize = '11px';
          badge.style.fontWeight = '700';
          badge.style.color = '#ffffff';
          badge.style.setProperty('color', '#ffffff', 'important');
          sel.value = 'in_progress';
          updateStatusSelectColor(sel, 'in_progress');
        } else if (saved === 'maint') {
          badge.textContent = 'Maintenance Performa Required';
          badge.style.backgroundColor = '#eab308';
          badge.style.width = '140px';
          badge.style.height = '32px';
          badge.style.padding = '0';
          badge.style.display = 'inline-flex';
          badge.style.alignItems = 'center';
          badge.style.justifyContent = 'center';
          badge.style.fontSize = '11px';
          badge.style.fontWeight = '700';
          badge.style.color = '#ffffff';
          badge.style.setProperty('color', '#ffffff', 'important');
          sel.value = 'in_progress';
          updateStatusSelectColor(sel, 'in_progress');
        } else if (saved === 'work_priced') {
          badge.textContent = 'Work Performa Priced';
          badge.style.backgroundColor = '#9333ea';
          badge.style.width = '140px';
          badge.style.height = '32px';
          badge.style.padding = '0';
          badge.style.display = 'inline-flex';
          badge.style.alignItems = 'center';
          badge.style.justifyContent = 'center';
          badge.style.fontSize = '11px';
          badge.style.fontWeight = '700';
          badge.style.color = '#ffffff';
          badge.style.setProperty('color', '#ffffff', 'important');
          // Set actual status value, not in_progress
          sel.value = 'work_priced_performa';
          updateStatusSelectColor(sel, 'work_priced_performa');
        } else if (saved === 'maint_priced') {
          badge.textContent = 'Maintenance Performa Priced';
          badge.style.backgroundColor = '#ea580c';
          badge.style.width = '140px';
          badge.style.height = '32px';
          badge.style.padding = '0';
          badge.style.display = 'inline-flex';
          badge.style.alignItems = 'center';
          badge.style.justifyContent = 'center';
          badge.style.fontSize = '11px';
          badge.style.fontWeight = '700';
          badge.style.color = '#ffffff';
          badge.style.setProperty('color', '#ffffff', 'important');
          // Set actual status value, not in_progress
          sel.value = 'maint_priced_performa';
          updateStatusSelectColor(sel, 'maint_priced_performa');
        }
      });
    }

    function initStatusSelects() {
      document.querySelectorAll('.status-select').forEach(function (sel) {
        if (!sel.dataset.oldStatus) sel.dataset.oldStatus = sel.value;
        // Check if there's a localStorage value - set dropdown value to in_progress but keep color red
        const complaintId = sel.getAttribute('data-complaint-id');
        const statusColor = sel.getAttribute('data-status-color');

        // Check data-actual-status to see if it's a priced performa - show "In Progress" in dropdown
        const actualStatus = sel.getAttribute('data-actual-status');
        if (actualStatus === 'work_priced_performa' || actualStatus === 'maint_priced_performa') {
          // Show "In Progress" in dropdown but actual value is preserved in data-actual-status
          sel.value = 'in_progress';
        } else if (complaintId) {
          let saved;
          try { saved = localStorage.getItem(`performaRequired:${complaintId}`); } catch (err) { saved = null; }
          // Set dropdown value based on saved option
          if (saved === 'work' || saved === 'maint' || saved === 'priced') {
            // For regular performas, use in_progress; for priced, check actual status from server
            if (saved === 'work_priced') {
              sel.value = 'in_progress'; // Show "In Progress" for display
            } else if (saved === 'maint_priced') {
              sel.value = 'in_progress'; // Show "In Progress" for display
            } else {
              sel.value = 'in_progress';
            }
          } else if (saved === 'product_na') {
            // For product_na, show in_progress in status dropdown (like work_performa and maint_performa)
            sel.value = 'in_progress';
          }
        }

        // CRITICAL: If data-status-color is "in_progress" or any performa type, ALWAYS force red color immediately
        const performaTypes = ['in_progress', 'work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa', 'product_na'];
        if (statusColor && performaTypes.includes(statusColor)) {
          // Force red color immediately with !important for all performa types
          const redColor = statusColors['in_progress'];
          sel.style.setProperty('background-color', redColor.bg, 'important');
          sel.style.setProperty('color', redColor.text, 'important');
          sel.style.setProperty('border-color', redColor.border, 'important');

          // Also update via function to ensure chip and indicator are updated
          updateStatusSelectColor(sel, 'in_progress');
        } else if (statusColor) {
          updateStatusSelectColor(sel, statusColor);
        } else {
          const currentValue = sel.value;
          // Check if current value is a performa type - use red color
          if (performaTypes.includes(currentValue)) {
            const redColor = statusColors['in_progress'];
            sel.style.setProperty('background-color', redColor.bg, 'important');
            sel.style.setProperty('color', redColor.text, 'important');
            sel.style.setProperty('border-color', redColor.border, 'important');
            updateStatusSelectColor(sel, 'in_progress');
          } else if (currentValue && statusColors[currentValue]) {
            updateStatusSelectColor(sel, currentValue);
          } else {
            // Default to in_progress (red) not gray
            const redColor = statusColors['in_progress'];
            sel.style.setProperty('background-color', redColor.bg, 'important');
            sel.style.setProperty('color', redColor.text, 'important');
            sel.style.setProperty('border-color', redColor.border, 'important');
            updateStatusSelectColor(sel, 'in_progress');
          }
        }
      });
    }

    // Open Add Stock Modal
    function openAddStockModal(approvalId, category = null) {
      const modalBody = document.getElementById('addStockModalBody');
      if (!modalBody) {
        alert('Modal not found');
        return;
      }

      // Show loading
      modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      // Store category globally for use in fetch response
      window.currentComplaintCategory = category;

      // Fetch approval details
      fetch(`/admin/approvals/${approvalId}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'same-origin'
      })
        .then(response => response.json())
        .then(data => {
          if (data.success && data.approval && data.approval.items !== undefined) {
            const items = data.approval.items || [];

            let itemsHtml = '<form id="addStockForm">';
            itemsHtml += `
                  <div class="row g-3 mb-3">
                    <div class="col-md-6">
                      <label class="form-label small mb-1" style="font-size: 0.85rem; font-weight: 600; color: #000000 !important;">Authority No.</label>
                      <input type="text" class="form-control form-control-sm" id="authorityNumberSimple" placeholder="Enter Authority No. (optional)" style="font-size: 0.9rem;">
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead><tr><th>Category</th><th>Product</th><th>Total Stock</th><th>Request Stock</th></tr></thead>
                      <tbody>
                `;

            // Store items globally for submission
            window.currentApprovalItems = items;
            // Only keep actual performa_type (allowed values) – do not fall back to status to avoid validation errors
            window.currentPerformaType = data.approval?.performa_type || null;
            window.currentPerformaType = data.approval?.performa_type || '';

            if (items.length === 0) {
              itemsHtml += `
                    <tr>
                      <td colspan="4" class="text-center">No products found</td>
                    </tr>
                  `;
            } else {
              items.forEach((item, index) => {
                const productName = item.spare_name || 'N/A';
                const category = item.category || 'N/A';
                const availableStock = item.available_stock || 0;
                const requestedQty = item.quantity_requested || 0;
                const spareId = item.spare_id || 0;

                // Format category display
                const categoryDisplay = {
                  'electric': 'Electric',
                  'technical': 'Technical',
                  'service': 'Service',
                  'billing': 'Billing',
                  'water': 'Water Supply',
                  'sanitary': 'Sanitary',
                  'plumbing': 'Plumbing',
                  'kitchen': 'Kitchen',
                  'other': 'Other',
                };
                const catDisplay = categoryDisplay[category.toLowerCase()] || category.charAt(0).toUpperCase() + category.slice(1);

                itemsHtml += `
                      <tr>
                        <td>${catDisplay}</td>
                        <td>${productName}</td>
                        <td>
                          <span class="badge ${availableStock > 0 ? 'bg-success' : 'bg-danger'}" style="font-size: 12px;">
                            ${availableStock}
                          </span>
                        </td>
                        <td>
                          <span class="badge bg-info" style="font-size: 12px;">
                            ${requestedQty}
                          </span>
                        </td>
                      </tr>
                    `;
              });
            }

            itemsHtml += '</tbody></table></div></form>';
            modalBody.innerHTML = itemsHtml;

            // Show Submit button
            const submitBtn = document.getElementById('submitAddStockBtn');
            if (submitBtn) {
              submitBtn.style.display = 'inline-block';
              submitBtn.disabled = false; // Keep submit enabled
            }

            // Pre-fill authority number if present in approval remarks or local storage
            const authorityInput = document.getElementById('authorityNumberSimple');
            const authorityLegacy = document.getElementById('authorityNumber');
            const authorityYes = document.getElementById('authorityYes');
            const authorityNo = document.getElementById('authorityNo');
            const authorityNoCol = document.getElementById('authorityNoCol');
            if (authorityInput) {
              let existingAuthority = '';
              const remarks = data.approval?.remarks || '';
              const match = remarks.match(/Authority\s*No[:\s]+([A-Za-z0-9\-]+)/i);
              if (match && match[1]) {
                existingAuthority = match[1];
              } else if (data.approval?.authority_number) {
                existingAuthority = data.approval.authority_number;
              } else if (window.currentApprovalId) {
                existingAuthority = localStorage.getItem(`authority_no_${window.currentApprovalId}`) || '';
              }
              if (existingAuthority) {
                authorityInput.value = existingAuthority;
                if (authorityLegacy) authorityLegacy.value = existingAuthority;
                window.currentAuthorityNumber = existingAuthority;
                if (authorityYes && authorityNo) {
                  authorityYes.checked = true;
                  authorityNo.checked = false;
                  if (authorityNoCol) {
                    authorityNoCol.classList.remove('d-none');
                  }
                }
              } else if (authorityYes && authorityNo) {
                authorityYes.checked = false;
                authorityNo.checked = true;
                if (authorityNoCol) authorityNoCol.classList.add('d-none');
              }
            }

            // Replace feather icons
            feather.replace();

            // Show modal
            new bootstrap.Modal(document.getElementById('addStockModal')).show();
          } else {
            modalBody.innerHTML = '<div class="alert alert-danger">Error loading approval items.</div>';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          modalBody.innerHTML = '<div class="alert alert-danger">Error loading approval details: ' + error.message + '</div>';
        });
    }

    // Submit Issue Stock Form
    function submitIssueStock() {
      // Use approval items from window.currentApprovalItems (loaded in modal)
      // Allow authority-only submissions even when there are no items
      const hasItems = !!(window.currentApprovalItems && window.currentApprovalItems.length > 0);

      console.log('submitIssueStock - currentApprovalId:', window.currentApprovalId);
      console.log('submitIssueStock - currentApprovalItems:', window.currentApprovalItems);

      const authorityInput = document.getElementById('authorityNumberSimple') || document.getElementById('authorityNumber');
      const authorityNumber = authorityInput ? authorityInput.value.trim() : '';
      const authorityInfo = authorityNumber ? ` | Authority No: ${authorityNumber}` : '';

      // Validate and collect data from approval items
      const stockData = [];
      let hasError = false;
      let errorMessage = '';

      if (hasItems) {
        window.currentApprovalItems.forEach(item => {
          const spareId = item.spare_id || 0;
          const productName = item.spare_name || 'N/A';
          const availableStock = item.available_stock || 0;
          const requestedQty = item.quantity_requested || 0;
          const itemId = item.id || null;

          if (spareId === 0) {
            hasError = true;
            errorMessage = `Invalid product: ${productName}`;
            return;
          }

          if (requestedQty <= 0) {
            // Skip items with 0 or negative quantity
            return;
          }

          if (requestedQty > availableStock) {
            hasError = true;
            errorMessage = `Requested quantity (${requestedQty}) cannot exceed available stock (${availableStock}) for ${productName}`;
            return;
          }

          stockData.push({
            spare_id: spareId,
            item_id: itemId,
            approval_id: window.currentApprovalId || null,
            issue_quantity: requestedQty,
            product_name: productName,
            available_stock: availableStock
          });
        });
      }

      if (hasError) {
        alert(errorMessage);
        return;
      }

      if (stockData.length === 0) {
        if (authorityNumber) {
          const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          const approvalId = window.currentApprovalId || null;
          const performaType = window.currentPerformaType || null;
          if (approvalId) {
            fetch(`/admin/approvals/${approvalId}/save-performa`, {
              method: 'POST',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
              },
              body: JSON.stringify({
                performa_type: performaType,
                remarks: `Authority No: ${authorityNumber}`
              }),
              credentials: 'same-origin'
            })
              .then(r => r.json())
              .then(resp => {
                if (resp.success) {
                  // Cache locally for instant prefill on reopen
                  localStorage.setItem(`authority_no_${approvalId}`, authorityNumber);
                  window.currentAuthorityNumber = authorityNumber;
                  alert('Authority saved.');
                  const modalInstance = bootstrap.Modal.getInstance(document.getElementById('addStockModal'));
                  if (modalInstance) modalInstance.hide();
                  window.location.reload();
                } else {
                  console.error('save-performa failed', resp);
                  alert(resp.message || 'Failed to save authority number.');
                }
              })
              .catch(err => {
                console.error('Error saving authority:', err);
                alert('Error saving authority number. Please try again.');
              });
          } else {
            alert('Authority recorded. No stock items selected.');
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('addStockModal'));
            if (modalInstance) modalInstance.hide();
          }
          return;
        }
        alert('Please add items with valid quantity or enter Authority No.');
        return;
      }

      // Confirm before submitting
      const confirmMessage = `Are you sure you want to ISSUE stock for the following items?\n\n` +
        stockData.map(item => `${item.product_name}: ${item.issue_quantity} units (Available: ${item.available_stock})`).join('\n');

      if (!confirm(confirmMessage)) {
        return;
      }

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      // Keep submit button enabled
      const submitBtn = document.getElementById('submitAddStockBtn');
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Issuing Stock...';
      }

      // Log approval_id before sending
      console.log('Issuing stock with approval_id:', window.currentApprovalId);
      console.log('Stock data to send:', stockData);

      // Send requests for each item to ISSUE stock (decrease inventory)
      const promises = stockData.map(item => {
        const requestBody = {
          quantity: item.issue_quantity,
          item_id: item.item_id,
          approval_id: window.currentApprovalId || null,
          reason: `Stock issued from approval - Product: ${item.product_name}${authorityInfo}`
        };

        console.log('Sending request for item:', item.product_name, 'with approval_id:', requestBody.approval_id);

        return fetch(`/admin/spares/${item.spare_id}/issue-stock`, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify(requestBody),
          credentials: 'same-origin'
        });
      });

      // Process all requests
      Promise.all(promises)
        .then(responses => Promise.all(responses.map(r => r.json())))
        .then(results => {
          const successCount = results.filter(r => r.success).length;
          const failedCount = results.length - successCount;

          if (failedCount === 0) {
            alert(`Successfully issued stock for all ${successCount} item(s)!`);
            bootstrap.Modal.getInstance(document.getElementById('addStockModal')).hide();
            // Optionally reload the page to refresh stock quantities
            // window.location.reload();
          } else {
            alert(`Issued stock for ${successCount} item(s), but ${failedCount} item(s) failed.`);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error issuing stock: ' + error.message);
        })
        .finally(() => {
          // Re-enable submit button
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i data-feather="check-circle"></i> Submit';
            feather.replace();
          }
        });
    }

    // Make functions globally accessible
    window.openAddStockModal = openAddStockModal;
    window.submitIssueStock = submitIssueStock;
    window.submitAddStock = submitIssueStock; // alias for legacy handlers

    // Event delegation for Add Stock buttons (works for dynamically loaded buttons too)
    document.addEventListener('click', function (e) {
      const addStockBtn = e.target.closest('.add-stock-btn');
      if (addStockBtn) {
        e.preventDefault();
        e.stopPropagation();
        const approvalId = addStockBtn.getAttribute('data-approval-id') || addStockBtn.getAttribute('onclick')?.match(/\d+/)?.[0];
        const category = addStockBtn.getAttribute('data-category') || null;
        if (approvalId && window.openAddStockModal) {
          window.openAddStockModal(parseInt(approvalId), category);
        } else {
          console.error('Add Stock button clicked but approval ID or function not found', {
            approvalId: approvalId,
            hasFunction: !!window.openAddStockModal
          });
        }
      }

      // Event delegation for Submit button in Add Stock Modal
      const submitBtn = e.target.closest('#submitAddStockBtn');
      if (submitBtn && !submitBtn.disabled) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Submit button clicked in Add Stock Modal');
        if (window.submitIssueStock) {
          window.submitIssueStock();
        } else {
          console.error('submitIssueStock function not found');
          alert('Error: Submit function not available. Please refresh the page.');
        }
      }
    });

    // Initialize rows on page load
    document.addEventListener('DOMContentLoaded', function () {
      initPerformaBadges();
      // Run initStatusSelects after initPerformaBadges to ensure colors are set correctly
      setTimeout(function () {
        initStatusSelects();

        // Additional safety check: Force red color for all in_progress status selects
        document.querySelectorAll('.status-select[data-status-color="in_progress"]').forEach(function (sel) {
          const redColor = statusColors['in_progress'];
          sel.style.setProperty('background-color', redColor.bg, 'important');
          sel.style.setProperty('color', redColor.text, 'important');
          sel.style.setProperty('border-color', redColor.border, 'important');
        });
      }, 100);

      // Run again after a longer delay to catch any late-loading elements
      setTimeout(function () {
        initStatusSelects();

        // Force red color one more time
        document.querySelectorAll('.status-select[data-status-color="in_progress"]').forEach(function (sel) {
          const redColor = statusColors['in_progress'];
          sel.style.setProperty('background-color', redColor.bg, 'important');
          sel.style.setProperty('color', redColor.text, 'important');
          sel.style.setProperty('border-color', redColor.border, 'important');
        });
      }, 500);
    });

    // Store initial status on page load
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.status-select').forEach(select => {
        select.dataset.oldStatus = select.value;
      });

      // Replace feather icons
      feather.replace();

      // Check for view_complaint query parameter and open complaint modal automatically
      const urlParams = new URLSearchParams(window.location.search);
      const viewComplaintId = urlParams.get('view_complaint');
      if (viewComplaintId) {
        // Remove the parameter from URL to clean it up
        urlParams.delete('view_complaint');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', newUrl);

        // Open complaint modal after a short delay to ensure page is fully loaded
        setTimeout(() => {
          if (typeof viewComplaint === 'function') {
            viewComplaint(viewComplaintId);
          }
        }, 300);
      }
    });

  </script>
@endpush