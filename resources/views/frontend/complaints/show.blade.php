@extends('frontend.layouts.app')

@section('title', 'Complaint Details')

@section('content')
<div class="container py-4" style="margin-top: 80px;">

<!-- PAGE HEADER -->
<!-- <div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="page-header-title mb-2">Complaint Details</h2>
      <p class="page-header-subtitle">View and manage complaint information</p>
    </div>
  </div>
</div> -->

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

<!-- Single Combined Card for All Details -->
<div class="row">
  <div class="col-12">
    <div class="card-glass">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i data-feather="file-text" class="me-2" style="width: 20px; height: 20px; color: var(--navy-gold) !important;"></i>
            <h5 class="mb-0 text-white font-weight-bold">Complaint Reference #{{ $complaint->id }}</h5>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge me-3" style="background-color: {{ $currentStatusColor['bg'] }}; color: #ffffff !important; padding: 6px 12px;">
                {{ $statusDisplay }}
            </span>
            <a href="{{ route('frontend.dashboard') }}" class="text-white text-decoration-none" title="Close">
                <i data-feather="x" style="width: 24px; height: 24px; color: #ffffff !important; stroke-width: 3;"></i>
            </a>
        </div>
      </div>
      
      <div class="card-body">
        <!-- Main Info Grid -->
        <div class="row g-4">
            <!-- Left Column: Complainant Info -->
            <div class="col-md-6 border-end-md">
                <h6 class="text-primary fw-bold text-uppercase mb-3 small border-bottom pb-2">Complainant Details</h6>
                
                <div class="info-item mb-2">
                    <div class="d-flex">
                        <i data-feather="user" class="me-3 text-muted" style="width: 16px; height: 16px; margin-top: 3px;"></i>
                        <div>
                            <div class="text-muted small text-uppercase">Name</div>
                            <div class="fw-medium text-dark">{{ $complaint->client->client_name ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                @if($complaint->client->phone)
                <div class="info-item mb-2">
                    <div class="d-flex">
                        <i data-feather="phone" class="me-3 text-muted" style="width: 16px; height: 16px; margin-top: 3px;"></i>
                        <div>
                            <div class="text-muted small text-uppercase">Phone</div>
                            <div class="fw-medium text-dark">{{ $complaint->client->phone }}</div>
                        </div>
                    </div>
                </div>
                @endif

                @if($complaint->client->address)
                <div class="info-item mb-2">
                    <div class="d-flex">
                        <i data-feather="map-pin" class="me-3 text-muted" style="width: 16px; height: 16px; margin-top: 3px;"></i>
                        <div>
                            <div class="text-muted small text-uppercase">Address</div>
                            <div class="fw-medium text-dark">{{ $complaint->client->address }}</div>
                        </div>
                    </div>
                </div>
                @endif

                @if($complaint->city_id && $complaint->city)
                <div class="info-item mb-2">
                    <div class="d-flex">
                        <i data-feather="map" class="me-3 text-muted" style="width: 16px; height: 16px; margin-top: 3px;"></i>
                        <div>
                            <div class="text-muted small text-uppercase">GE Group</div>
                            <div class="fw-medium text-dark">{{ $complaint->city->name }}</div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($complaint->sector_id && $complaint->sector)
                <div class="info-item mb-2">
                    <div class="d-flex">
                        <i data-feather="layers" class="me-3 text-muted" style="width: 16px; height: 16px; margin-top: 3px;"></i>
                        <div>
                            <div class="text-muted small text-uppercase">GE Node</div>
                            <div class="fw-medium text-dark">{{ $complaint->sector->name }}</div>
                        </div>
                    </div>
                </div>
                @endif

                @if($complaint->description)
                <div class="mt-3 p-3 bg-light rounded border">
                    <div class="text-muted small text-uppercase mb-1">Description</div>
                    <p class="mb-0 text-dark small">{{ $complaint->description }}</p>
                </div>
                @endif
            </div>

            <!-- Right Column: Complaint Specifics -->
            <div class="col-md-6 ps-md-4">
                <h6 class="text-primary fw-bold text-uppercase mb-3 small border-bottom pb-2">Complaint Information</h6>
                
                <div class="info-item mb-2">
                    <div class="d-flex">
                        <i data-feather="tag" class="me-3 text-muted" style="width: 16px; height: 16px; margin-top: 3px;"></i>
                        <div>
                            <div class="text-muted small text-uppercase">Nature & Type</div>
                            <div class="fw-medium text-dark">{{ $displayText }}</div>
                        </div>
                    </div>
                </div>

                @if($complaint->priority)
                <div class="info-item mb-2">
                    <div class="d-flex">
                        <i data-feather="flag" class="me-3 text-muted" style="width: 16px; height: 16px; margin-top: 3px;"></i>
                        <div>
                            <div class="text-muted small text-uppercase">Priority</div>
                            <span class="badge bg-{{ $complaint->priority === 'high' ? 'danger' : ($complaint->priority === 'medium' ? 'warning' : 'success') }}">
                                {{ ucfirst($complaint->priority) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endif

                <div class="info-item mb-2">
                    <div class="d-flex">
                        <i data-feather="clock" class="me-3 text-muted" style="width: 16px; height: 16px; margin-top: 3px;"></i>
                        <div>
                            <div class="text-muted small text-uppercase">Availability Time</div>
                            <div class="fw-medium text-dark">{{ $complaint->availability_time ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <div class="info-item mb-2">
                    <div class="d-flex">
                        <i data-feather="user-check" class="me-3 text-muted" style="width: 16px; height: 16px; margin-top: 3px;"></i>
                        <div>
                            <div class="text-muted small text-uppercase">Assigned Employee</div>
                            <div class="fw-medium text-dark">
                                {{ $complaint->assignedEmployee->name ?? 'Unassigned' }}
                                @if($complaint->assignedEmployee && $complaint->assignedEmployee->designation) 
                                    <span class="text-muted small">({{ $complaint->assignedEmployee->designation }})</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-item mb-2">
                    <div class="d-flex">
                        <i data-feather="calendar" class="me-3 text-muted" style="width: 16px; height: 16px; margin-top: 3px;"></i>
                        <div>
                            <div class="text-muted small text-uppercase">Registered On</div>
                            <div class="fw-medium text-dark">{{ $complaint->created_at ? $complaint->created_at->timezone('Asia/Karachi')->format('M d, Y H:i A') : 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                @if($complaint->closed_at || ($complaint->status == 'resolved' || $complaint->status == 'closed'))
                <div class="info-item mb-2">
                    <div class="d-flex">
                        <i data-feather="check-circle" class="me-3 text-muted" style="width: 16px; height: 16px; margin-top: 3px;"></i>
                        <div>
                            <div class="text-muted small text-uppercase">Completed On</div>
                            <div class="fw-medium text-dark">
                                @if($complaint->closed_at)
                                    {{ $complaint->closed_at->timezone('Asia/Karachi')->format('M d, Y H:i A') }}
                                @elseif($complaint->status == 'resolved' || $complaint->status == 'closed')
                                    {{ $complaint->updated_at->timezone('Asia/Karachi')->format('M d, Y H:i A') }}
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

        <!-- Authority & Stock Section -->
        @php
          $approval = $complaint->spareApprovals->first();
          $authorityNumber = $approval?->authority_number ?? null;
          
          $issuedStock = \App\Models\SpareStockLog::where('reference_id', $complaint->id)
            ->where('change_type', 'out')
            ->with('spare:id,item_name')
            ->orderBy('created_at', 'desc')
            ->get();
        @endphp

        @if($authorityNumber || $issuedStock->count() > 0)
        <hr class="my-4">
        <h6 class="text-primary fw-bold text-uppercase mb-3 small">Authority & Stock Details</h6>
        <div class="bg-light rounded p-3 pt-3">
             @if($authorityNumber)
             <div class="mb-3 d-flex align-items-center">
                <span class="text-muted small text-uppercase me-2">Authority No:</span>
                <span class="fw-bold text-dark">{{ $authorityNumber }}</span>
             </div>
             @endif

             @if($issuedStock->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 bg-white">
                        <thead class="table-light">
                            <tr>
                                <th class="small">Product</th>
                                <th class="small">Qty</th>
                                <th class="small">Issued At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($issuedStock as $stock)
                            <tr>
                                <td class="small">{{ $stock->spare->item_name ?? 'N/A' }}</td>
                                <td class="small fw-bold">{{ $stock->quantity }}</td>
                                <td class="small">{{ $stock->created_at ? $stock->created_at->timezone('Asia/Karachi')->format('M d, Y H:i') : '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
             @endif
        </div>
        @endif

        <!-- Attachments Section -->
        @if($complaint->attachments->count() > 0)
        <hr class="my-4">
        <h6 class="text-primary fw-bold text-uppercase mb-3 small">Attachments</h6>
        <div class="row g-2">
            @foreach($complaint->attachments as $attachment)
            <div class="col-md-3 col-6">
                <div class="border rounded p-2 d-flex flex-column align-items-center bg-light h-100 justify-content-center text-center">
                    <i data-feather="file" class="text-secondary mb-2"></i>
                    <span class="small text-truncate w-100 mb-2" title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</span>
                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0" style="font-size: 0.75rem;">View</a>
                </div>
            </div>
            @endforeach
        </div>
        @endif

      </div>
    </div>
  </div>
</div>

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
        @if(!$complaint->feedback)
          <a href="{{ route('frontend.feedback', $complaint->id) }}" class="btn btn-outline-secondary btn-sm" title="Add Feedback" style="padding: 3px 8px;">
            <i data-feather="plus-circle" style="width: 16px; height: 16px;"></i>
          </a>
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
                    <span class="badge bg-{{ $complaint->feedback->rating_badge_color }}" style="color: #ffffff !important;">
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
                    {{ $complaint->feedback->created_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') }}
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
            <a href="{{ route('frontend.feedback', $complaint->id) }}" class="btn btn-primary">
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

</div>
@push('styles')
<style>
  /* Navy Theme Colors */
  :root {
    --navy-primary: #003366;
    --navy-dark: #001f3f;
    --navy-light: #004d99;
    --navy-accent: #0066cc;
    --navy-gold: #ffd700;
  }

  html {
    height: 100%;
  }

  body {
    background: linear-gradient(135deg, #001f3f 0%, #003366 50%, #004d99 100%) !important;
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  .container {
    flex: 1;
    padding-top: 100px; /* Fix header overlap */
  }
  
  /* Navbar Override to match profile page */
  .navbar {
    background-image: url('{{ asset('assests/Background.jpg') }}') !important;
    background-size: cover !important;
    background-position: center !important;
  }

  /* Card Styling for Light Theme (kept white as requested) */
  .card-glass {
    background: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 15px; /* More rounded like profile */
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
    overflow: hidden;
  }
  
  .card-glass:hover {
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    transform: translateY(-2px);
  }
  
  .card-glass .card-header {
    background: linear-gradient(135deg, var(--navy-primary), var(--navy-dark));
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1rem 1.25rem;
  }
  
  /* Header text inside card should be white now that header bg is dark */
  .card-glass .card-header h5 {
    color: #ffffff !important; 
  }

  .card-glass .card-header i {
    color: var(--navy-gold) !important;
  }
  
  .card-glass .card-body {
    padding: 1.25rem;
  }
  
  .info-item {
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
  }
  
  .info-item:last-child {
    border-bottom: none;
  }

  /* Text Colors Adjustment for White Card Body */
  .text-muted {
    color: #64748b !important; /* Slate-500 - Dark enough for white bg */
  }
  .text-white {
    color: #334155 !important; /* Slate-700 for content inside white cards */
  }
  
  /* But Header outside card needs to be White on Dark Body */
  .page-header-title {
    color: #ffffff !important;
    font-weight: 700;
  }
  
  .page-header-subtitle {
    color: rgba(255, 255, 255, 0.7) !important;
  }

  /* Table overrides */
  .table-dark {
    --bs-table-bg: #f8fafc;
    --bs-table-color: #334155;
    --bs-table-border-color: #e2e8f0;
    color: #334155;
  }
</style>
@endpush
@push('scripts')
<script>
  feather.replace();
</script>
@endpush
@endsection
