@extends('layouts.sidebar')

@section('title', 'Complaint Details — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-3">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h3 class="text-white mb-1" style="font-size: 1.4rem; font-weight: 700;">Complaint Details</h3>
      <p class="text-light mb-0 small" style="opacity: 0.8; font-size: 0.85rem;">View and manage complaint information</p>
    </div>
  </div>
</div>

@php
  $rawStatus = $complaint->status ?? 'new';
  $complaintStatus = ($rawStatus == 'new') ? 'assigned' : $rawStatus;
  $statusLabels = [
    'unassigned' => 'Unassigned',
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
    'barrack_damages' => 'Barrack Damages',
    'door_lock' => 'Door Lock',
  ];
  
  // Real mapping: 'new' is 'unassigned'
  $displayStatus = ($rawStatus == 'new') ? 'unassigned' : $rawStatus;
  $statusDisplay = $statusLabels[$displayStatus] ?? ucfirst(str_replace('_', ' ', $displayStatus));
  
  $statusColors = [
    'unassigned' => ['bg' => '#000000', 'text' => '#ffffff', 'border' => '#000000'],
    'in_progress' => ['bg' => '#dc2626', 'text' => '#ffffff', 'border' => '#b91c1c'],
    'resolved' => ['bg' => '#64748b', 'text' => '#ffffff', 'border' => '#475569'], // Grey
    'work_performa' => ['bg' => '#60a5fa', 'text' => '#ffffff', 'border' => '#3b82f6'],
    'maint_performa' => ['bg' => '#eab308', 'text' => '#ffffff', 'border' => '#ca8a04'],
    'work_priced_performa' => ['bg' => '#9333ea', 'text' => '#ffffff', 'border' => '#7e22ce'],
    'maint_priced_performa' => ['bg' => '#ea580c', 'text' => '#ffffff', 'border' => '#c2410c'],
    'product_na' => ['bg' => '#f97316', 'text' => '#ffffff', 'border' => '#c2410c'],
    'un_authorized' => ['bg' => '#ec4899', 'text' => '#ffffff', 'border' => '#db2777'],
    'barrack_damages' => ['bg' => '#808000', 'text' => '#ffffff', 'border' => '#666600'],
    'door_lock' => ['bg' => '#000000', 'text' => '#ffffff', 'border' => '#000000'],
    'assigned' => ['bg' => '#16a34a', 'text' => '#ffffff', 'border' => '#15803d'], // Green
  ];
  $currentStatusColor = $statusColors[$displayStatus] ?? $statusColors['assigned'];
  
  $category = $complaint->category_id ?? 'N/A';
  $designation = $complaint->assignedEmployee->designation->name ?? $complaint->assignedEmployee->designation ?? 'N/A';
  $titleName = $complaint->getTitleDisplayAttribute();
  $catDisplay = $complaint->getCategoryDisplayAttribute();
  $displayText = $catDisplay . ' - ' . $titleName;
  
  // Extract performa type
  $approval = $complaint->spareApprovals->first();
  $performaType = $approval?->performa_type ?? null;
  // If no performa type on approval, check if complaint status indicates one
  if (!$performaType && $complaint) {
      if (in_array($complaint->status, ['work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa', 'product_na'])) {
          $performaType = $complaint->status;
      }
  }
  
  if ($performaType === 'maint_performa') {
    $performaTypeLabel = 'Maintenance Performa';
  } elseif ($performaType === 'work_priced_performa') {
    $performaTypeLabel = 'Work Priced';
  } elseif ($performaType === 'maint_priced_performa') {
    $performaTypeLabel = 'Maintenance Priced';
  } elseif ($performaType === 'product_na') {
    $performaTypeLabel = 'Product N/A';
  } else {
    $performaTypeLabel = $performaType ? ucwords(str_replace('_', ' ', $performaType)) : null;
  }

  // Extract Registered By and Status Changed By
  $createdLog = $complaint->logs ? $complaint->logs->where('action', 'created')->first() : null;
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

  $statusLog = $complaint->logs ? $complaint->logs->whereIn('action', ['status_changed', 'resolved', 'closed'])->last() : null;
  $statusChangedBy = null;
  if ($statusLog) {
      if (str_contains($statusLog->remarks, ' by ')) {
          $parts = explode(' by ', $statusLog->remarks);
          $afterBy = end($parts);
          $cleanParts = explode('. Remarks:', $afterBy);
          $statusChangedBy = trim($cleanParts[0]);
      } else {
          $statusChangedBy = $statusLog->actionBy->name ?? $statusLog->actionBy->username ?? 'Staff';
      }
  }
@endphp

<!-- COMPLAINT DETAILS -->
<div class="row g-3">
  <!-- Personal Information -->
  <div class="col-md-6">
    <div class="card-glass h-100">
      <div class="d-flex align-items-center mb-2 pb-2" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
        <i data-feather="user" class="me-2 text-primary" style="width: 17px; height: 17px;"></i>
        <h5 class="text-white mb-0" style="font-size: 0.95rem; font-weight: 600;">Complainant Information</h5>
      </div>
      
      @if($complaint->house_id && $complaint->house)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="home" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">House NO.</span>
          </div>
          <span class="info-value text-end">{{ $complaint->house->house_no ?? 'N/A' }}</span>
        </div>
      </div>
      @endif

      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="user" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Name</span>
          </div>
          <span class="info-value text-end">{{ $complaint->house->name ?? 'N/A' }}</span>
        </div>
      </div>
      
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="phone" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Phone</span>
          </div>
          <span class="info-value text-end">{{ $complaint->house?->phone ?? 'N/A' }}</span>
        </div>
      </div>
      
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="map-pin" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Address</span>
          </div>
          <span class="info-value text-end" style="max-width: 60%;">{{ $complaint->house?->address ?? 'N/A' }}</span>
        </div>
      </div>
      
      @if($complaint->city_id && $complaint->city)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="map" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">GE Groups</span>
          </div>
          <span class="info-value text-end">{{ $complaint->city->name }}</span>
        </div>
      </div>
      @endif
      
      @if($complaint->sector_id && $complaint->sector)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="layers" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">GE Nodes</span>
          </div>
          <span class="info-value text-end">{{ $complaint->sector->name }}</span>
        </div>
      </div>
      @endif
      
      @if($complaint->description)
      <div class="info-item pt-2 border-0">
        <div class="d-flex align-items-start flex-column">
          <div class="d-flex align-items-center mb-1">
            <i data-feather="file-text" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Description</span>
          </div>
          <div class="info-value w-100 p-2 rounded" style="background: rgba(255,255,255,0.04); font-size: 0.82rem; font-weight: 400; line-height: 1.4;">{{ $complaint->description }}</div>
        </div>
      </div>
      @endif
    </div>
  </div>
  
  <!-- Complaint Information -->
  <div class="col-md-6">
    <div class="card-glass h-100">
      <div class="d-flex align-items-center mb-2 pb-2" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
        <i data-feather="alert-triangle" class="me-2 text-primary" style="width: 17px; height: 17px;"></i>
        <h5 class="text-white mb-0" style="font-size: 0.95rem; font-weight: 600;">Complaint Information</h5>
      </div>
      
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="hash" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Complaint ID</span>
          </div>
          <span class="info-value text-end text-primary font-weight-bold">#{{ (int)($complaint->complaint_id ?? $complaint->id) }}</span>
        </div>
      </div>
      
      @if($complaint->title)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="file-text" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Complaint Type</span>
          </div>
          <span class="info-value text-end">{{ $complaint->title }}</span>
        </div>
      </div>
      @endif
      
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="tag" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Nature & Type</span>
          </div>
          <span class="info-value text-end" style="max-width: 60%;">{{ $displayText }}</span>
        </div>
      </div>

      @if($complaint->subCategory)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="layers" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Sub Category</span>
          </div>
          <span class="info-value text-end">{{ $complaint->subCategory->name }}</span>
        </div>
      </div>
      @endif
      
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="activity" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Status</span>
          </div>
          <div>
            <span class="badge" style="background-color: {{ $currentStatusColor['bg'] }}; color: #ffffff !important; padding: 4px 10px; font-size: 0.75rem; font-weight: 600; border-radius: 6px; border: 1px solid {{ $currentStatusColor['border'] }};">
              {{ $statusDisplay }}
            </span>
          </div>
        </div>
      </div>

      @if($statusChangedBy)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="user-check" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Changed By</span>
          </div>
          <span class="info-value text-end">{{ $statusChangedBy }}</span>
        </div>
      </div>
      @endif

      @if($registeredBy)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="user-plus" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Registered By</span>
          </div>
          <span class="info-value text-end">{{ $registeredBy }}</span>
        </div>
      </div>
      @endif
      
      @if($performaTypeLabel)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="file" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Performa Type</span>
          </div>
          <span class="info-value text-end">{{ $performaTypeLabel }}</span>
        </div>
      </div>
      @endif
      
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="flag" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Priority</span>
          </div>
          <div>
            @php
              $pVal = strtolower($complaint->priority ?? 'normal');
              $isEmerg = in_array($pVal, ['emergency', 'urgent', 'high'], true);
            @endphp
            <span class="badge" style="background-color: {{ $isEmerg ? '#991b1b' : '#1d4ed8' }} !important; color: #ffffff !important; border: 1px solid {{ $isEmerg ? '#7f1d1d' : '#1e40af' }} !important; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px;">
              {{ $isEmerg ? 'Emergency' : 'Normal' }}
            </span>
          </div>
        </div>
      </div>
      
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="clock" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Availability Time</span>
          </div>
          <span class="info-value text-end">{{ str_replace('T', ' ', $complaint->availability_time ?? 'N/A') }}</span>
        </div>
      </div>
      
      @if($complaint->assignedEmployee)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="user-check" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Assigned Employee</span>
          </div>
          <span class="info-value text-end">{{ $complaint->assignedEmployee->name ?? 'N/A' }}@if($complaint->assignedEmployee && $complaint->assignedEmployee->designation) <span class="text-muted small">({{ $complaint->assignedEmployee->designation->name ?? $complaint->assignedEmployee->designation }})</span>@endif</span>
        </div>
      </div>
      @endif
      
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="calendar" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Registration Date/Time</span>
          </div>
          <span class="info-value text-end">{{ $complaint->created_at ? $complaint->created_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') : 'N/A' }}</span>
        </div>
      </div>
      
      @if($complaint->closed_at || ($complaint->status == 'resolved' || $complaint->status == 'closed'))
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="check-circle" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Completion Time</span>
          </div>
          <span class="info-value text-end">
            @if($complaint->closed_at)
              {{ $complaint->closed_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') }}
            @elseif($complaint->status == 'resolved' || $complaint->status == 'closed')
              {{ $complaint->updated_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') }}
            @else
              -
            @endif
          </span>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

@php
  // Get approval data with authority number and issued stock
  $approval = $complaint->spareApprovals->first();
  $authorityNumber = $approval?->authority_number ?? null;
  
  // Get issued stock from stock logs
  $issuedStock = \App\Models\SpareStockLog::where('reference_id', $complaint->id)
    ->where('change_type', 'out')
    ->with('spare:id,item_name')
    ->orderBy('created_at', 'desc')
    ->get();
@endphp

@if($authorityNumber || $issuedStock->count() > 0)
<div class="row mt-3">
  <div class="col-12">
    <div class="card-glass">
      <div class="d-flex align-items-center mb-2 pb-2" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
        <i data-feather="package" class="me-2 text-primary" style="width: 17px; height: 17px;"></i>
        <h5 class="text-white mb-0" style="font-size: 0.95rem; font-weight: 600;">Authority & Stock Details</h5>
      </div>
      
      @if($authorityNumber)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="file-text" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Authority Number</span>
          </div>
          <span class="info-value text-end fw-bold">{{ $authorityNumber }}</span>
        </div>
      </div>
      @endif
      
      @if($issuedStock->count() > 0)
      <div class="info-item border-0 pt-2">
        <div class="d-flex align-items-start flex-column">
          <div class="d-flex align-items-center mb-2">
            <i data-feather="box" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Issued Stock</span>
          </div>
          <div class="table-responsive w-100">
            <table class="table table-sm table-dark align-middle mb-0" style="font-size: 0.8rem;">
              <thead>
                <tr>
                  <th style="padding: 5px 8px;">Product Name</th>
                  <th style="padding: 5px 8px;">Quantity</th>
                  <th style="padding: 5px 8px;">Issue Date/Time</th>
                </tr>
              </thead>
              <tbody>
                @foreach($issuedStock as $stock)
                <tr>
                  <td style="padding: 4px 8px;">{{ $stock->spare->item_name ?? 'N/A' }}</td>
                  <td style="padding: 4px 8px;">{{ $stock->quantity }}</td>
                  <td style="padding: 4px 8px;">{{ $stock->created_at ? $stock->created_at->timezone('Asia/Karachi')->format('M d, Y H:i') : 'N/A' }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
@endif


<!-- FEEDBACK SECTION -->
@if($complaint->status == 'resolved' || $complaint->status == 'closed' || $complaint->feedback)
<div class="row mt-3">
  <div class="col-12 d-flex justify-content-center">
    <div style="max-width: 900px; width: 100%;">
      <div class="card-glass">
      <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 text-white" style="font-size: 0.95rem;">
          <i data-feather="message-circle" class="me-2"></i>Complainant Feedback
        </h6>
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
          <a href="{{ route('admin.feedback.create', $complaint->id) }}" class="btn btn-outline-secondary btn-sm" title="Add Feedback" style="padding: 2px 6px;">
            <i data-feather="plus-circle" style="width: 14px; height: 14px;"></i>
          </a>
        @else
          @if($isGE)
            <a href="{{ route('admin.feedback.edit', $complaint->feedback->id) }}" class="btn btn-outline-primary btn-sm" title="Edit Feedback" style="padding: 4px 8px; border: 1px solid #3b82f6 !important; color: #3b82f6 !important; background-color: transparent !important; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;">
              <i data-feather="edit" style="width: 14px; height: 14px; color: #3b82f6;"></i>
            </a>
          @endif
        @endif
      </div>
      <div class="card-body py-2">
        @if($complaint->feedback)
          <div class="row">
            <div class="col-md-6">
              <table class="table table-sm table-borderless mb-0" style="font-size: 0.85rem;">
                <tr>
                  <td class="text-white py-1"><strong>Overall Rating:</strong></td>
                  <td class="py-1">
                    <span class="badge" style="background-color: {{ $complaint->feedback->rating_color }}; color: #ffffff !important; font-size: 0.75rem;">
                      {{ $complaint->feedback->overall_rating_display }}
                    </span>
                    @if($complaint->feedback->rating_score)
                      <span class="text-white ms-2">{{ $complaint->feedback->rating_score }} / 5 Stars</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <td class="text-white py-1"><strong>Feedback Date:</strong></td>
                  <td class="text-white py-1">
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
                  <td class="text-white py-1"><strong>Entered By:</strong></td>
                  <td class="text-white py-1">
                    @if($complaint->feedback->enteredBy)
                      {{ $complaint->feedback->enteredBy->name ?? 'System' }}
                      <span class="badge badge-light" style="font-size: 0.7rem;">Staff</span>
                    @elseif($complaint->feedback->submitted_by)
                      {{ $complaint->feedback->submitted_by }}
                      <span class="badge badge-info text-white" style="font-size: 0.7rem;">Client</span>
                    @else
                      Client (Web)
                    @endif
                  </td>
                </tr>
                @php
                  $geUser = null;
                  if ($complaint->city_id && $complaint->city) {
                    $geUser = \App\Models\User::whereJsonContains('city_ids', (int)$complaint->city_id)
                      ->whereHas('role', function($q) {
                        $q->where('role_name', 'garrison_engineer');
                      })
                      ->first();
                  }
                @endphp
                @if($geUser)
                <tr>
                  <td class="text-white py-1"><strong>GE (GE Groups):</strong></td>
                  <td class="text-white py-1">{{ $geUser->name ?? $geUser->username ?? 'N/A' }}</td>
                </tr>
                @endif
              </table>
            </div>
          </div>
          @if($complaint->feedback->comments)
          <div class="mt-2">
            <h6 class="text-white fw-bold mb-1" style="font-size: 0.82rem;">Complainant Comments:</h6>
            <div class="p-2 rounded" style="background-color: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3);">
              <p class="text-white mb-0" style="color: #dbeafe; font-size: 0.82rem; line-height: 1.4;">
                {{ $complaint->feedback->comments }}
              </p>
            </div>
          </div>
          @endif
          
          @if($complaint->feedback->remarks)
          <div class="mt-2">
            <h6 class="text-white fw-bold mb-1" style="font-size: 0.82rem;">Technician Remarks:</h6>
            <div class="p-2 rounded" style="background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
              <p class="text-white mb-0" style="color: #ecfdf5; font-size: 0.82rem; line-height: 1.4;">
                {{ $complaint->feedback->remarks }}
              </p>
            </div>
          </div>
          @endif
        @else
          <div class="text-center py-2">
            <i data-feather="message-circle" class="feather-lg mb-2 text-muted" style="width: 24px; height: 24px;"></i>
            <p class="text-muted mb-2 small">No feedback has been recorded for this complaint.</p>
            <a href="{{ route('admin.feedback.create', $complaint->id) }}" class="btn btn-primary btn-sm py-1 px-3">
              <i data-feather="plus-circle" class="me-1" style="width: 14px; height: 14px;"></i>Add Complainant Feedback
            </a>
          </div>
        @endif
      </div>
    </div>
  </div>
  </div>
</div>
@endif

@push('styles')
<style>
  /* Blurred background effect like employee view page */
  body {
    position: relative;
  }
  
  body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: -1;
    pointer-events: none;
  }
  
  /* Ensure content is above blur */
  .card-glass {
    position: relative;
    z-index: 1;
    background: rgba(30, 41, 59, 0.85) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
    transition: box-shadow 0.3s ease;
    padding: 12px 16px !important;
  }
  
  .card-glass:hover {
    box-shadow: 0 12px 40px rgba(15, 23, 42, 0.5);
  }
  
  .card-glass .card-header {
    background: rgba(59, 130, 246, 0.2) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
  }
  
  .card-glass .card-body {
    background: transparent !important;
  }
  
  .info-item {
    padding: 5px 0 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
  }
  
  .info-item:last-child {
    border-bottom: none !important;
  }

  .info-label {
    font-size: 0.78rem !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #94a3b8;
    font-weight: 500;
  }

  .info-value {
    font-size: 0.88rem !important;
    font-weight: 500;
    color: #ffffff;
  }
</style>
@endpush

@push('scripts')
<script>
  feather.replace();
</script>
@endpush
@endsection
