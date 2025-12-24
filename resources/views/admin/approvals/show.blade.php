@extends('layouts.sidebar')

@section('title', 'Complaint Details — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-2">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-1" style="font-size: 1.5rem;">Complaint Details</h2>
    </div>
  </div>
</div>

@php
  $complaint = $approval->complaint ?? null;
  if ($complaint) {
    $category = $complaint->category ?? 'N/A';

    // Map category for display - use original complaint category as-is
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

    // Use assigned employee designation like index
    $designation = $complaint->assignedEmployee->designation ?? 'N/A';
    $displayText = $catDisplay . ' - ' . $designation;

    $rawStatus = $complaint->status ?? 'new';
    $complaintStatus = ($rawStatus == 'new') ? 'assigned' : $rawStatus;
    $statusLabels = [
      'assigned' => 'Assigned',
      'in_progress' => 'In Progress',
      'resolved' => 'Addressed',
      'closed' => 'Closed',
      'work_performa' => 'Work Performa',
      'maint_performa' => 'Maintenance Performa',
      'work_priced_performa' => 'Work Priced',
      'maint_priced_performa' => 'Maintenance Priced',
      'product_na' => 'Product N/A',
      'un_authorized' => 'Un-Authorized',
      'pertains_to_ge_const_isld' => 'GE Const Isld',
    ];
    $statusDisplay = $statusLabels[$complaintStatus] ?? ucfirst(str_replace('_', ' ', $complaintStatus));
    $statusColors = [
      'in_progress' => ['bg' => '#dc2626', 'text' => '#ffffff', 'border' => '#b91c1c'],
      'resolved' => ['bg' => '#64748b', 'text' => '#ffffff', 'border' => '#475569'], // Grey (swapped from green)
      'work_performa' => ['bg' => '#60a5fa', 'text' => '#ffffff', 'border' => '#3b82f6'],
      'maint_performa' => ['bg' => '#eab308', 'text' => '#ffffff', 'border' => '#ca8a04'],
      'work_priced_performa' => ['bg' => '#9333ea', 'text' => '#ffffff', 'border' => '#7e22ce'],
      'maint_priced_performa' => ['bg' => '#ea580c', 'text' => '#ffffff', 'border' => '#c2410c'],
      'product_na' => ['bg' => '#f97316', 'text' => '#ffffff', 'border' => '#c2410c'],
      'un_authorized' => ['bg' => '#ec4899', 'text' => '#ffffff', 'border' => '#db2777'],
      'pertains_to_ge_const_isld' => ['bg' => '#06b6d4', 'text' => '#ffffff', 'border' => '#0891b2'],
      'assigned' => ['bg' => '#16a34a', 'text' => '#ffffff', 'border' => '#15803d'], // Green (swapped from grey)
    ];
    $currentStatusColor = $statusColors[$complaintStatus] ?? $statusColors['assigned'];
  }
@endphp

@if($complaint)
@php
  // Extract performa type and authority number
  $performaType = $approval->performa_type ?? null;
  
  // If no performa type on approval, check if complaint status indicates one
  if (!$performaType && $complaint) {
      if (in_array($complaint->status, ['work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa', 'product_na'])) {
          $performaType = $complaint->status;
      } elseif ($complaint->logs) {
          // If status matches none of the above (e.g. resolved), check history logs
          foreach ($complaint->logs as $log) {
              if ($log->action === 'status_changed' && $log->remarks) {
                  // Check for "Status changed from work_performa to ..."
                  if (preg_match('/Status changed from (work_performa|maint_performa|work_priced_performa|maint_priced_performa|product_na)/', $log->remarks, $matches)) {
                      $performaType = $matches[1];
                      break; // Found the most recent one (assuming logs are ordered or we take first found)
                  }
              }
          }
      }
  }
  
  $performaTypeLabel = $performaType ? ucwords(str_replace('_', ' ', $performaType)) : null;
  
  // Extract authority number - check dedicated column first, then remarks, then stock logs
  $authorityNumber = null;
  
  // First check the dedicated authority_number column
  if ($approval->authority_number) {
    $authorityNumber = $approval->authority_number;
  }
  
  // If not found, check approval remarks
  if (!$authorityNumber && $approval->remarks) {
    // Look for "Authority No:" or "Authority No" in remarks - extract just the number
    if (preg_match('/Authority\s+No[:\s]+([A-Za-z0-9\-]+)/i', $approval->remarks, $matches)) {
      $authorityNumber = trim($matches[1]);
    }
    // Also check for "authority number:" pattern (case insensitive)
    if (!$authorityNumber && preg_match('/authority\s+number[:\s]+([A-Za-z0-9\-]+)/i', $approval->remarks, $matches)) {
      $authorityNumber = trim($matches[1]);
    }
    // Check for pattern like "Stock issued with authority number: ZHD346"
    if (!$authorityNumber && preg_match('/authority\s+number[:\s]+([A-Za-z0-9\-]+)/i', $approval->remarks, $matches)) {
      $authorityNumber = trim($matches[1]);
    }
  }
  
  // If not found in approval remarks, check stock logs
  if (!$authorityNumber && $complaint) {
    $stockLogs = \App\Models\SpareStockLog::where('reference_id', $approval->id)
      ->where('change_type', 'out')
      ->get();
    
    foreach ($stockLogs as $log) {
      if ($log->remarks) {
        // Look for "Authority No:" or "Authority No" in stock log remarks
        if (preg_match('/Authority\s+No[:\s]+([A-Za-z0-9\-]+)/i', $log->remarks, $matches)) {
          $authorityNumber = trim($matches[1]);
          break;
        }
        // Also check for "authority number:" pattern
        if (!$authorityNumber && preg_match('/authority\s+number[:\s]+([A-Za-z0-9\-]+)/i', $log->remarks, $matches)) {
          $authorityNumber = trim($matches[1]);
          break;
        }
      }
    }
  }
@endphp

<!-- COMPLAINT DETAILS -->
<div class="row">
  <!-- Personal Information -->
  <div class="col-md-6 mb-3">
    <div class="card-glass h-100 p-2">
      <div class="d-flex align-items-center mb-2" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 8px;">
        <i data-feather="user" class="me-2 text-primary" style="width: 18px; height: 18px;"></i>
        <h5 class="text-white mb-0" style="font-size: 1rem; font-weight: 600;">Complainant Information</h5>
      </div>
      
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="user" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Name</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->client->client_name ?? 'N/A' }}</div>
          </div>
        </div>
      </div>

      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="home" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">House No.</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->house->username ?? 'N/A' }}</div>
          </div>
        </div>
      </div>
      
      @if($complaint->client->phone)
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="phone" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Phone</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->client->phone }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($complaint->client->address)
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="map-pin" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Address</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->client->address }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($complaint->city_id && $complaint->city)
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="map" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">GE Groups</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->city->name }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($complaint->sector_id && $complaint->sector)
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="layers" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">GE Nodes</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->sector->name }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($complaint->description)
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="file-text" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Description</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->description }}</div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
  
  <!-- Complaint Information -->
  <div class="col-md-6 mb-3">
    <div class="card-glass h-100 p-2">
      <div class="d-flex align-items-center mb-2" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 8px;">
        <i data-feather="alert-triangle" class="me-2 text-primary" style="width: 18px; height: 18px;"></i>
        <h5 class="text-white mb-0" style="font-size: 1rem; font-weight: 600;">Complaint Information</h5>
      </div>
      
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="hash" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Complaint ID</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">
              <a href="{{ route('admin.complaints.show', $complaint->id) }}" class="text-decoration-none" style="color: #3b82f6;">
                {{ str_pad($complaint->complaint_id ?? $complaint->id, 4, '0', STR_PAD_LEFT) }}
              </a>
            </div>
          </div>
        </div>
      </div>
      
      @if($complaint->title)
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="file-text" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Complaint Type</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->title }}</div>
          </div>
        </div>
      </div>
      @endif
      
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="tag" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Nature & Type</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $displayText }}</div>
          </div>
        </div>
      </div>
      
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="activity" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Status</div>
            <div>
              <span class="badge" style="background-color: {{ $currentStatusColor['bg'] }}; color: #ffffff !important; padding: 6px 12px; font-size: 0.85rem; font-weight: 600; border-radius: 6px; border: 1px solid {{ $currentStatusColor['border'] }};">
                {{ $statusDisplay }}
              </span>
            </div>
          </div>
        </div>
      </div>
      
      @if($complaint->assignedEmployee)
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="user-check" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Assigned Employee</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">
              {{ $complaint->assignedEmployee->name ?? 'N/A' }}
              @if($complaint->assignedEmployee && $complaint->assignedEmployee->phone)
                <span class="text-muted ms-2" style="font-size: 0.85rem;">
                  <i data-feather="phone" style="width: 14px; height: 14px; vertical-align: middle;"></i>
                  {{ $complaint->assignedEmployee->phone }}
                </span>
              @endif
            </div>
          </div>
        </div>
      </div>
      @endif
      
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="calendar" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Registration Date/Time</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->created_at ? $complaint->created_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') : 'N/A' }}</div>
          </div>
        </div>
      </div>
      
      @if($complaint->closed_at)
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="check-circle" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Addressed Date/Time</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->closed_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($performaTypeLabel)
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="file" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Performa Type</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $performaTypeLabel }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($authorityNumber)
      <div class="info-item mb-1">
        <div class="d-flex align-items-start">
          <i data-feather="hash" class="me-2 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Authority No.</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $authorityNumber }}</div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
@endif

{{-- Approval Information section removed as requested --}}

@php
  // Check if stock has been issued for this approval
  $hasStockIssued = false;
  if ($approval && $approval->id) {
    // Check if there are any stock logs with reference_id = approval->id and change_type = 'out'
    $issuedStockLogs = \App\Models\SpareStockLog::where('reference_id', $approval->id)
      ->where('change_type', 'out')
      ->exists();
    
    if ($issuedStockLogs) {
      $hasStockIssued = true;
    } else {
      // Also check complaint stock logs if reference_id matches complaint_id
      if ($complaint && $complaint->id) {
        $complaintStockLogs = \App\Models\SpareStockLog::where('reference_id', $complaint->id)
          ->where('change_type', 'out')
          ->exists();
        if ($complaintStockLogs) {
          $hasStockIssued = true;
        }
      }
    }
  }
  
  // Build unified requested items: prefer approval items; else robust fallbacks from complaint
  // Also include stock logs from spare_stock_logs table
  $stockLogs = $complaint ? $complaint->stockLogs()->with('spare')->get() : collect();
  
  $collections = [
    ($approval->items ?? null),
    ($complaint->spareParts ?? null),
    ($stockLogs->count() > 0 ? $stockLogs : null),
    ($complaint->requestedItems ?? null),
    ($complaint->items ?? null),
    ($complaint->stocks ?? null),
    ($complaint->stockItems ?? null),
    ($complaint->stock_ins ?? null),
  ];
  $requestedItems = collect();
  foreach ($collections as $col) {
    if ($col && $col->count() > 0) { 
      $requestedItems = $col; 
      break; 
    }
  }
  
  // If we got stock logs, convert them to the expected format
  if ($requestedItems->count() > 0 && $requestedItems->first() && isset($requestedItems->first()->change_type)) {
    // This is stock logs collection, convert to expected format
    $convertedItems = collect();
    foreach ($requestedItems as $log) {
      if ($log->spare) {
        $convertedItems->push((object)[
          'id' => $log->id,
          'spare' => $log->spare,
          'quantity_requested' => $log->quantity,
          'quantity_approved' => $log->quantity,
          'spare_id' => $log->spare_id,
        ]);
      }
    }
    $requestedItems = $convertedItems;
  }
@endphp

@if($hasStockIssued)
<!-- REQUESTED ITEMS -->
<div class="row mb-3">
  <div class="col-12">
    <div class="card-glass p-1">
      <div class="card-header py-2">
        <h5 class="card-title mb-0 text-white" style="font-size: 1rem;">
          <i data-feather="package" class="me-2" style="width: 18px; height: 18px;"></i>Requested Items ({{ $requestedItems->count() }})
        </h5>
      </div>
      <div class="card-body p-1">
        <style>
          /* Force table column borders */
          .card-body .table th:not(:last-child),
          .card-body .table td:not(:last-child) {
            border-right: 1px solid rgba(201, 160, 160, 0.3) !important;
          }
          .card-body .table th:last-child,
          .card-body .table td:last-child {
            border-right: none !important;
          }
        </style>
        <div class="table-responsive">
          <table class="table table-dark" style="margin-bottom: 0;">
            <thead>
              <tr style="background-color: rgba(59, 130, 246, 0.2); border-bottom: 2px solid rgba(59, 130, 246, 0.5);">
                <th style="color: #ffffff; font-weight: 600; padding: 6px 12px; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">#</th>
                <th style="color: #ffffff; font-weight: 600; padding: 6px 12px; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">Item Name</th>
                <th style="color: #ffffff; font-weight: 600; padding: 6px 12px; border-right: 1px solid rgba(201, 160, 160, 0.3) !important; text-align: center;">Quantity Requested</th>
                <th style="color: #ffffff; font-weight: 600; padding: 6px 12px; border-right: 1px solid rgba(201, 160, 160, 0.3) !important; text-align: center;">
                  @if($approval->status === 'pending' && ($approval->items && $approval->items->count() > 0))
                    Quantity Approved (Editable)
                  @else
                    Quantity Approved
                  @endif
                </th>
                <th style="color: #ffffff; font-weight: 600; padding: 6px 12px; text-align: center; border-right: none !important;">Available Stock</th>
              </tr>
            </thead>
            <tbody>
              @forelse($requestedItems as $index => $item)
              @php
                // Try to resolve an attached spare/product model
                $spareModel = $item->spare ?? $item->product ?? $item->item ?? null;
                $itemName = $spareModel->item_name ?? $spareModel->name ?? $item->spare_name ?? $item->item_name ?? $item->name ?? 'N/A';
                $availableQty = $spareModel->stock_quantity ?? $spareModel->available_quantity ?? 0;
                // Normalize requested/approved quantity field names
                $requestedQty = $item->quantity_requested ?? $item->requested_quantity ?? $item->qty ?? $item->quantity ?? 0;
                $approvedQty = $item->quantity_approved ?? $item->approved_quantity ?? null;
              @endphp
              <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.25);">
                <td style="color: #e2e8f0; padding: 6px 12px; border-right: 1px solid rgba(201, 160, 160, 0.3) !important; font-weight: 500;">{{ $index + 1 }}</td>
                <td style="color: #ffffff; padding: 6px 12px; border-right: 1px solid rgba(201, 160, 160, 0.3) !important; font-weight: 500;">{{ $itemName }}</td>
                <td style="color: #e2e8f0; padding: 6px 12px; border-right: 1px solid rgba(201, 160, 160, 0.3) !important; text-align: center;">
                  <span class="badge" style="background-color: rgba(245, 158, 11, 0.2); color: #fbbf24; padding: 6px 12px; font-weight: 600;">
                    {{ number_format((int)$requestedQty, 0) }}
                  </span>
                </td>
                <td style="color: #e2e8f0; padding: 6px 12px; border-right: 1px solid rgba(201, 160, 160, 0.3) !important; text-align: center;">
                  @if($approval->status === 'pending' && ($approval->items && $approval->items->count() > 0))
                    @php
                      $maxQty = min((int)$requestedQty, (int)$availableQty);
                      $inputVal = $approvedQty !== null ? (int)$approvedQty : ($availableQty > 0 ? $maxQty : 0);
                    @endphp
                    <input type="number" 
                           class="form-control form-control-sm text-center approved-qty-input" 
                           name="items[{{ $item->id }}][quantity_approved]"
                           value="{{ $inputVal }}"
                           min="0"
                           max="{{ $maxQty }}"
                           data-item-id="{{ $item->id }}"
                           data-requested="{{ (int)$requestedQty }}"
                           data-available="{{ (int)$availableQty }}"
                           style="width: 80px; display: inline-block; background-color: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.3); color: #ffffff;">
                  @else
                    <span class="badge" style="background-color: rgba(34, 197, 94, 0.2); color: #22c55e; padding: 6px 12px; font-weight: 600;">
                      {{ $approvedQty !== null ? number_format((int)$approvedQty, 0) : number_format((int)$requestedQty, 0) }}
                    </span>
                  @endif
                </td>
                <td style="color: #ffffff; padding: 6px 12px; text-align: center; border-right: none !important;">
                  <span class="badge bg-{{ ((int)$availableQty <= 0) ? 'danger' : 'success' }}" style="padding: 6px 12px; font-weight: 600; color: #ffffff !important;">
                    {{ number_format((int)$availableQty, 0) }}
                  </span>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4" style="color: #94a3b8;">No items found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- FEEDBACK SECTION -->
@if($complaint && ($complaint->status == 'resolved' || $complaint->status == 'closed' || $complaint->feedback))
<div class="row mt-2">
  <div class="col-12 d-flex justify-content-center">
    <div style="max-width: 900px; width: 100%;">
      <div class="card-glass p-1">
      <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 text-white" style="font-size: 1rem;">
          <i data-feather="message-circle" class="me-2" style="width: 18px; height: 18px;"></i>Complainant Feedback
        </h5>
        @php
          // Check if current user is GE (Garrison Engineer)
          $isGE = false;
          if (Auth::check() && Auth::user()->role) {
            $roleName = strtolower(Auth::user()->role->role_name ?? '');
            $isGE = in_array($roleName, ['garrison_engineer', 'garrison engineer']) || 
                    strpos(strtolower($roleName), 'garrison') !== false ||
                    strpos(strtolower($roleName), 'ge') !== false;
          }
        @endphp
        @if(!$complaint->feedback)
          <a href="{{ route('admin.feedback.create', $complaint->id) }}" class="btn btn-outline-secondary btn-sm" title="Add Feedback" style="padding: 3px 8px;">
            <i data-feather="plus-circle" style="width: 16px; height: 16px;"></i>
          </a>
        @else
          @if($isGE)
            <a href="{{ route('admin.feedback.edit', $complaint->feedback->id) }}" class="btn btn-outline-primary btn-sm" title="Edit Feedback" style="padding: 6px 10px; border: 1px solid #3b82f6 !important; color: #3b82f6 !important; background-color: transparent !important; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px;">
              <i data-feather="edit" style="width: 16px; height: 16px; color: #3b82f6;"></i>
            </a>
          @endif
        @endif
      </div>
      <div class="card-body p-2">
        @if($complaint->feedback)
          <div class="row">
            <div class="col-md-6">
              <table class="table table-borderless">
                <tr>
                  <td class="text-white"><strong>Overall Rating:</strong></td>
                  <td>
                    <span class="badge" style="background-color: {{ $complaint->feedback->rating_color }}; color: #ffffff !important;">
                      {{ $complaint->feedback->overall_rating_display }}
                    </span>
                    @if($complaint->feedback->rating_score)
                      <span class="text-white ms-2">({{ $complaint->feedback->rating_score }}/5)</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <td class="text-white"><strong>Feedback Date:</strong></td>
                  <td class="text-white">
                    @php
                      $feedbackDate = 'N/A';
                      if ($complaint->feedback) {
                        try {
                          if ($complaint->feedback->feedback_date) {
                            $date = $complaint->feedback->feedback_date;
                            if (is_string($date)) {
                              $date = \Carbon\Carbon::parse($date);
                            }
                            if ($date instanceof \Carbon\Carbon) {
                              $feedbackDate = $date->timezone('Asia/Karachi')->format('M d, Y H:i:s');
                            }
                          }
                          if ($feedbackDate === 'N/A' && $complaint->feedback->created_at) {
                            $feedbackDate = $complaint->feedback->created_at->timezone('Asia/Karachi')->format('M d, Y H:i:s');
                          }
                        } catch (\Exception $e) {
                          // If all fails, use created_at as fallback
                          try {
                            if ($complaint->feedback->created_at) {
                              $feedbackDate = $complaint->feedback->created_at->timezone('Asia/Karachi')->format('M d, Y H:i:s');
                            }
                          } catch (\Exception $e2) {
                            $feedbackDate = 'N/A';
                          }
                        }
                      }
                      echo $feedbackDate;
                    @endphp
                  </td>
                </tr>
                <tr>
                  <td class="text-white"><strong>Entered By:</strong></td>
                  <td class="text-white">
                    @if($complaint->feedback->enteredBy)
                      {{ $complaint->feedback->enteredBy->name ?? 'System' }}
                      <span class="badge badge-light">Staff</span>
                    @elseif($complaint->feedback->submitted_by)
                      {{ $complaint->feedback->submitted_by }}
                      <span class="badge badge-info text-white">Client</span>
                    @else
                      Client (Web)
                    @endif
                  </td>
                </tr>
                @php
                  $geUser = null;
                  if ($complaint->city_id && $complaint->city) {
                    $geUser = \App\Models\User::where('city_id', $complaint->city_id)
                      ->whereHas('role', function($q) {
                        $q->where('role_name', 'garrison_engineer');
                      })
                      ->first();
                  }
                @endphp
                @if($geUser)
                <tr>
                  <td class="text-white"><strong>GE (GE Groups):</strong></td>
                  <td class="text-white">{{ $geUser->name ?? $geUser->username ?? 'N/A' }}</td>
                </tr>
                @endif
              </table>
            </div>
          </div>
          @if($complaint->feedback->comments)
          <div class="row mt-3">
            <div class="col-12">
              <h6 class="text-white fw-bold mb-2" style="font-size: 0.9rem;">Complainant Comments:</h6>
              <div class="card-glass" style="background-color: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3);">
                <div class="card-body">
                  <p class="text-white mb-0" style="color: #dbeafe; line-height: 1.6;">
                    {{ $complaint->feedback->comments }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          @endif
        @else
          <div class="text-center py-4">
            <i data-feather="message-circle" class="feather-lg mb-3 text-muted"></i>
            <p class="text-muted mb-3">No feedback has been recorded for this complaint.</p>
            <a href="{{ route('admin.feedback.create', $complaint->id) }}" class="btn btn-primary">
              <i data-feather="plus-circle" class="me-2"></i>Add Complainant Feedback
            </a>
          </div>
        @endif
      </div>
    </div>
  </div>
  </div>
</div>
@endif

{{-- Approval Actions section removed as per user request --}}

@push('styles')
<style>
  .info-item {
    padding: 2px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  }
  
  .info-item:last-child {
    border-bottom: none;
  }
  
  .card-glass {
    transition: box-shadow 0.3s ease;
  }
  
  .card-glass:hover {
    box-shadow: 0 12px 40px rgba(15, 23, 42, 0.5);
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
  });
</script>
@endpush
@endsection
