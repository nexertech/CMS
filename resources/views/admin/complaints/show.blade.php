@extends('layouts.sidebar')

@section('title', 'Complaint Details — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Complaint Details</h2>
      <p class="text-light">View and manage complaint information</p>
    </div>
  </div>
</div>

@php
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
    'barak_damages' => 'Barak Damages',
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
    'barak_damages' => ['bg' => '#808000', 'text' => '#ffffff', 'border' => '#666600'],
    'assigned' => ['bg' => '#16a34a', 'text' => '#ffffff', 'border' => '#15803d'], // Green (swapped from grey)
  ];
  $currentStatusColor = $statusColors[$complaintStatus] ?? $statusColors['assigned'];
  
  $category = $complaint->category ?? 'N/A';
  $designation = $complaint->assignedEmployee->designation ?? 'N/A';
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
  $catDisplay = $categoryDisplay[strtolower($category)] ?? ucfirst($category);
  $displayText = $catDisplay . ' - ' . $designation;
@endphp

<!-- COMPLAINT DETAILS -->
<div class="row">
  <!-- Personal Information -->
  <div class="col-md-6 mb-4">
    <div class="card-glass h-100">
      <div class="d-flex align-items-center mb-4" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
        <i data-feather="user" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
        <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Complainant Information</h5>
      </div>
      
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="user" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Name</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->client->client_name ?? 'N/A' }}</div>
          </div>
        </div>
      </div>
        @if($complaint->house_id && $complaint->house)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="home" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">House NO.</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->house->username }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($complaint->client->phone)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="phone" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Phone</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->client->phone }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($complaint->client->address)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="map-pin" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Address</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->client->address }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($complaint->city_id && $complaint->city)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="map" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">GE Groups</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->city->name }}</div>
          </div>
        </div>
      </div>
      @endif
      
    
      
      @if($complaint->sector_id && $complaint->sector)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="layers" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">GE Nodes</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->sector->name }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($complaint->description)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="file-text" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Description</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 400; line-height: 1.6;">{{ $complaint->description }}</div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
  
  <!-- Complaint Information -->
  <div class="col-md-6 mb-4">
    <div class="card-glass h-100">
      <div class="d-flex align-items-center mb-4" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
        <i data-feather="alert-triangle" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
        <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Complaint Information</h5>
      </div>
      
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="hash" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Complaint ID</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ (int)($complaint->complaint_id ?? $complaint->id) }}</div>
          </div>
        </div>
      </div>
      
      @if($complaint->title)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="file-text" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Complaint Type</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->title }}</div>
          </div>
        </div>
      </div>
      @endif
      
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="tag" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Nature & Type</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $displayText }}</div>
          </div>
        </div>
      </div>
      
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="activity" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Status</div>
            <div>
              <span class="badge" style="background-color: {{ $currentStatusColor['bg'] }}; color: #ffffff !important; padding: 6px 12px; font-size: 0.85rem; font-weight: 600; border-radius: 6px; border: 1px solid {{ $currentStatusColor['border'] }};">
                {{ $statusDisplay }}
              </span>
            </div>
          </div>
        </div>
      </div>
      
      @if($complaint->priority)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="flag" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Priority</div>
            <div>
              <span class="badge bg-{{ $complaint->priority === 'high' ? 'danger' : ($complaint->priority === 'medium' ? 'warning' : 'success') }}" style="font-size: 0.85rem; padding: 6px 12px; color: #ffffff !important;">
                {{ ucfirst($complaint->priority) }}
              </span>
            </div>
          </div>
        </div>
      </div>
      @endif
      
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="clock" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Availability Time</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ str_replace('T', ' ', $complaint->availability_time ?? 'N/A') }}</div>
          </div>
        </div>
      </div>
      
      @if($complaint->assignedEmployee)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="user-check" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Assigned Employee</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->assignedEmployee->name ?? 'N/A' }}@if($complaint->assignedEmployee && $complaint->assignedEmployee->designation) ({{ $complaint->assignedEmployee->designation }})@endif</div>
          </div>
        </div>
      </div>
      @endif
      
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="calendar" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Registration Date/Time</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $complaint->created_at ? $complaint->created_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') : 'N/A' }}</div>
          </div>
        </div>
      </div>
      
      @if($complaint->closed_at || ($complaint->status == 'resolved' || $complaint->status == 'closed'))
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="check-circle" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Completion Time</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">
              @if($complaint->closed_at)
                {{ $complaint->closed_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') }}
              @elseif($complaint->status == 'resolved' || $complaint->status == 'closed')
                {{ $complaint->updated_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') }}
              @else
                -
              @endif
            </div>
          </div>
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
<div class="row mb-4">
  <div class="col-12">
    <div class="card-glass">
      <div class="d-flex align-items-center mb-4" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
        <i data-feather="package" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
        <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Authority & Stock Details</h5>
      </div>
      
      @if($authorityNumber)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="file-text" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Authority Number</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $authorityNumber }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($issuedStock->count() > 0)
      <div class="info-item">
        <div class="d-flex align-items-start">
          <i data-feather="box" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Issued Stock</div>
            <div class="table-responsive">
              <table class="table table-sm table-dark" style="margin-bottom: 0;">
                <thead>
                  <tr>
                    <th style="font-size: 0.8rem; padding: 8px;">Product Name</th>
                    <th style="font-size: 0.8rem; padding: 8px;">Quantity</th>
                    <th style="font-size: 0.8rem; padding: 8px;">Issue Date/Time</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($issuedStock as $stock)
                  <tr>
                    <td style="font-size: 0.85rem; padding: 8px;">{{ $stock->spare->item_name ?? 'N/A' }}</td>
                    <td style="font-size: 0.85rem; padding: 8px;">{{ $stock->quantity }}</td>
                    <td style="font-size: 0.85rem; padding: 8px;">{{ $stock->created_at ? $stock->created_at->timezone('Asia/Karachi')->format('M d, Y H:i') : 'N/A' }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
@endif

@if($complaint->attachments->count() > 0)
<div class="row mb-4">
  <div class="col-12">
    <div class="card-glass">
      <div class="d-flex align-items-center mb-4" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
        <i data-feather="paperclip" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
        <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Attachments</h5>
      </div>
      <div class="row">
        @foreach($complaint->attachments as $attachment)
        <div class="col-md-3 mb-3">
          <div class="card-glass text-center" style="padding: 1rem;">
            <i data-feather="file" class="mb-2 text-primary" style="width: 24px; height: 24px;"></i>
            <p class="text-white small mb-2">{{ $attachment->original_name }}</p>
            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
              View
            </a>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endif

<!-- FEEDBACK SECTION -->
@if($complaint->status == 'resolved' || $complaint->status == 'closed' || $complaint->feedback)
<div class="row mt-4">
  <div class="col-12 d-flex justify-content-center">
    <div style="max-width: 900px; width: 100%;">
      <div class="card-glass">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 text-white">
          <i data-feather="message-circle" class="me-2"></i>Complainant Feedback
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
      <div class="card-body">
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
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  }
  
  .info-item:last-child {
    border-bottom: none;
  }
</style>
@endpush

@push('scripts')
<script>
  feather.replace();
</script>
@endpush
@endsection
