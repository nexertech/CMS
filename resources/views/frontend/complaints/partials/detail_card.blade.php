@php
  // Status Logic
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
    'pending' => 'Pending', // Frontend specific fallback
  ];
  
  $statusDisplay = $statusLabels[$complaintStatus] ?? ucfirst(str_replace('_', ' ', $complaintStatus));

  $statusColors = [
      'in_progress' => ['bg' => '#dc2626', 'text' => '#ffffff'],
      'resolved' => ['bg' => '#64748b', 'text' => '#ffffff'],   // Slate/Grey
      'closed' => ['bg' => '#64748b', 'text' => '#ffffff'],      // Slate/Grey
      'work_performa' => ['bg' => '#60a5fa', 'text' => '#ffffff'],
      'maint_performa' => ['bg' => '#eab308', 'text' => '#ffffff'],
      'work_priced_performa' => ['bg' => '#9333ea', 'text' => '#ffffff'],
      'maint_priced_performa' => ['bg' => '#ea580c', 'text' => '#ffffff'],
      'product_na' => ['bg' => '#f97316', 'text' => '#ffffff'],
      'un_authorized' => ['bg' => '#ec4899', 'text' => '#ffffff'],
      'pertains_to_ge_const_isld' => ['bg' => '#06b6d4', 'text' => '#ffffff'],
      'barak_damages' => ['bg' => '#808000', 'text' => '#ffffff'],
      'assigned' => ['bg' => '#16a34a', 'text' => '#ffffff'],    // Green
      'new' => ['bg' => '#16a34a', 'text' => '#ffffff'],         // Green
      'pending' => ['bg' => '#f59e0b', 'text' => '#000000'],     // Orange
  ];

  $currentStatusColor = $statusColors[$complaintStatus] ?? $statusColors['assigned'];
  
  $category = $complaint->category ?? 'N/A';
  $designation = $complaint->assignedEmployee->designation ?? 'N/A';
  $displayText = ucfirst($category) . ' - ' . $designation;
@endphp
<style>
  /* Navy Theme Colors & Card Styles */
  :root {
    --navy-primary: #003366;
    --navy-dark: #001f3f;
    --navy-light: #004d99;
    --navy-accent: #0066cc;
    --navy-gold: #ffd700;
  }

  .card-glass {
    background: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
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
  
  .card-glass .card-header h5, 
  .card-glass .card-header .text-white {
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

  /* Scoped color overrides for this card */
  .card-glass .text-muted {
    color: #64748b !important;
  }
<style>
  /* Compact Mode Styles (Relaxed to Medium) */
  .card-glass.compact-mode .card-body {
    padding: 1.25rem !important;
  }
  .card-glass.compact-mode .info-item {
    padding: 10px 0 !important;
    border-bottom: 1px solid rgba(0,0,0,0.05);
  }
  .card-glass.compact-mode h6 {
    font-size: 0.95rem !important; /* Increased from 0.75rem */
    margin-bottom: 0.75rem !important;
  }
  .card-glass.compact-mode .text-muted.small {
    font-size: 0.75rem !important; /* Increased from 0.65rem */
    margin-bottom: 2px !important;
  }
  .card-glass.compact-mode .fw-medium {
    font-size: 0.85rem !important; /* Increased from 0.75rem */
  }
  .card-glass.compact-mode .feather {
    width: 18px !important; /* Increased from 14px */
    height: 18px !important;
  }
  /* Specific adjustments for feedback table */
  .table-compact td {
    padding: 6px 8px !important; /* Relaxed padding */
    font-size: 0.8rem !important; /* Increased font */
  }
</style>

<div class="card-glass compact-mode">
      <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
        <div class="d-flex align-items-center">
            <i data-feather="file-text" class="me-2" style="width: 20px; height: 20px; color: var(--navy-gold) !important;"></i>
            <h6 class="mb-0 text-white font-weight-bold" style="font-size: 1.1rem !important;">Complaint #{{ $complaint->id }}</h6>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge me-3" style="background-color: {{ $currentStatusColor['bg'] ?? '#16a34a' }}; color: #ffffff !important; padding: 6px 12px; font-size: 0.8rem;">
                {{ $statusDisplay ?? ucfirst($complaint->status) }}
            </span>
            @if(request()->ajax())
            <a href="javascript:void(0);" onclick="document.getElementById('complaintModal').classList.add('hidden'); document.getElementById('complaintModal').classList.remove('flex');" class="text-white text-decoration-none" title="Close">
                <i data-feather="x" style="width: 24px; height: 24px; color: #ffffff !important; stroke-width: 2;"></i>
            </a>
            @else
            <a href="{{ route('frontend.dashboard') }}" class="text-white text-decoration-none" title="Close">
                <i data-feather="x" style="width: 24px; height: 24px; color: #ffffff !important; stroke-width: 2;"></i>
            </a>
            @endif
        </div>
      </div>
      
      <div class="card-body">
        <!-- Main Info Grid -->
        <div class="row g-4">
            <!-- Left Column: Complainant Info -->
            <div class="col-md-6 border-end-md">
                <h6 class="text-primary fw-bold text-uppercase border-bottom pb-2 mb-3">Complainant Information</h6>
                
                <div class="info-item">
                    <div class="d-flex align-items-center">
                        <i data-feather="user" class="me-3 text-muted"></i>
                        <div class="w-100 d-flex justify-content-between">
                            <span class="text-muted small text-uppercase">Name:</span>
                            <span class="fw-medium text-dark text-end">{{ $complaint->client->client_name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                @if($complaint->client->phone)
                <div class="info-item">
                    <div class="d-flex align-items-center">
                        <i data-feather="phone" class="me-3 text-muted"></i>
                        <div class="w-100 d-flex justify-content-between">
                            <span class="text-muted small text-uppercase">Phone:</span>
                            <span class="fw-medium text-dark text-end">{{ $complaint->client->phone }}</span>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($complaint->client->address)
                <div class="info-item">
                    <div class="d-flex align-items-start">
                        <i data-feather="map-pin" class="me-3 text-muted mt-1"></i>
                        <div class="w-100 d-flex justify-content-between align-items-start">
                             <span class="text-muted small text-uppercase" style="white-space: nowrap;">Address:</span>
                             <span class="fw-medium text-dark text-end ms-2" style="line-height: 1.3; font-size: 0.85rem;">{{ $complaint->client->address }}</span>
                        </div>
                    </div>
                </div>
                @endif

                @if($complaint->city_id && $complaint->city)
                <div class="info-item">
                    <div class="d-flex align-items-center">
                        <i data-feather="map" class="me-3 text-muted"></i>
                        <div class="w-100 d-flex justify-content-between">
                            <span class="text-muted small text-uppercase">GE Group:</span>
                            <span class="fw-medium text-dark text-end">{{ $complaint->city->name }}</span>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($complaint->sector_id && $complaint->sector)
                <div class="info-item">
                    <div class="d-flex align-items-center">
                        <i data-feather="layers" class="me-3 text-muted"></i>
                        <div class="w-100 d-flex justify-content-between">
                             <span class="text-muted small text-uppercase">GE Node:</span>
                             <span class="fw-medium text-dark text-end">{{ $complaint->sector->name }}</span>
                        </div>
                    </div>
                </div>
                @endif

                @if($complaint->description)
                <div class="mt-3 p-3 bg-light rounded border">
                    <span class="text-muted small text-uppercase d-block mb-1">Description:</span>
                    <p class="mb-0 text-dark small" style="line-height: 1.5; font-size: 0.85rem;">{{ $complaint->description }}</p>
                </div>
                @endif
            </div>

            <!-- Right Column: Complaint Specifics -->
            <div class="col-md-6 ps-md-4">
                <h6 class="text-primary fw-bold text-uppercase border-bottom pb-2 mb-3">Complaint Details</h6>
                
                <div class="info-item">
                    <div class="d-flex align-items-center">
                        <i data-feather="tag" class="me-3 text-muted"></i>
                        <div class="w-100 d-flex justify-content-between">
                            <span class="text-muted small text-uppercase">Nature & Type:</span>
                            <span class="fw-medium text-dark text-end">{{ $displayText ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                @if($complaint->priority)
                <div class="info-item">
                    <div class="d-flex align-items-center">
                        <i data-feather="flag" class="me-3 text-muted"></i>
                         <div class="w-100 d-flex justify-content-between align-items-center">
                            <span class="text-muted small text-uppercase">Priority:</span>
                            <span class="badge bg-{{ $complaint->priority === 'high' ? 'danger' : ($complaint->priority === 'medium' ? 'warning' : 'success') }} p-2" style="font-size: 0.75rem;">
                                {{ ucfirst($complaint->priority) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endif

                <div class="info-item">
                    <div class="d-flex align-items-center">
                        <i data-feather="clock" class="me-3 text-muted"></i>
                        <div class="w-100 d-flex justify-content-between">
                            <span class="text-muted small text-uppercase">Avail. Time:</span>
                            <span class="fw-medium text-dark text-end">{{ $complaint->availability_time ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="d-flex align-items-center">
                        <i data-feather="user-check" class="me-3 text-muted"></i>
                         <div class="w-100 d-flex justify-content-between">
                            <span class="text-muted small text-uppercase">Assigned To:</span>
                            <span class="fw-medium text-dark text-end">{{ $complaint->assignedEmployee->name ?? 'Unassigned' }}</span>
                        </div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="d-flex align-items-center">
                        <i data-feather="calendar" class="me-3 text-muted"></i>
                        <div class="w-100 d-flex justify-content-between">
                            <span class="text-muted small text-uppercase">Registered On:</span>
                            <span class="fw-medium text-dark text-end">{{ $complaint->created_at ? $complaint->created_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                @if($complaint->closed_at || ($complaint->status == 'resolved' || $complaint->status == 'closed'))
                <div class="info-item">
                    <div class="d-flex align-items-center">
                        <i data-feather="check-circle" class="me-3 text-muted"></i>
                        <div class="w-100 d-flex justify-content-between">
                            <span class="text-muted small text-uppercase">Completed On:</span>
                            <span class="fw-medium text-dark text-end">
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
             <div class="mb-2 d-flex align-items-center">
                <span class="text-muted small text-uppercase me-2">Authority No:</span>
                <span class="fw-bold text-dark">{{ $authorityNumber }}</span>
             </div>
             @endif

             @if($issuedStock->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 bg-white table-compact">
                        <thead class="table-light">
                            <tr>
                                <th class="p-2 small">Product</th>
                                <th class="p-2 small">Qty</th>
                                <th class="p-2 small">Issued At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($issuedStock as $stock)
                            <tr>
                                <td class="p-2 small">{{ $stock->spare->item_name ?? 'N/A' }}</td>
                                <td class="p-2 small fw-bold">{{ $stock->quantity }}</td>
                                <td class="p-2 small">{{ $stock->created_at ? $stock->created_at->timezone('Asia/Karachi')->format('M d, Y H:i') : '-' }}</td>
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
                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="border rounded p-2 d-block text-center bg-light text-decoration-none">
                     <i data-feather="file" class="text-secondary mb-2"></i>
                    <span class="small text-truncate w-100 d-block text-dark" title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</span>
                </a>
            </div>
            @endforeach
        </div>
        @endif
        
        <!-- Feedback Section -->
        @if($complaint->feedback)
           <hr class="my-4">
           <h6 class="text-primary fw-bold text-uppercase mb-3 small">Feedback Details</h6>
           <div class="bg-light rounded p-3">
               <div class="row">
                 <div class="col-md-8">
                   <table class="table table-borderless table-sm mb-0 table-compact">
                     <tr>
                       <td class="text-muted small text-uppercase" style="width: 140px;">Overall Rating:</td>
                       <td>
                         <span class="badge bg-{{ $complaint->feedback->rating_badge_color }}" style="font-size: 0.8rem; padding: 4px 8px;">
                           {{ $complaint->feedback->overall_rating_display }}
                         </span>
                         @if($complaint->feedback->rating_score)
                           <span class="text-dark small ms-2">({{ $complaint->feedback->rating_score }}/5)</span>
                         @endif
                       </td>
                     </tr>
                     <tr>
                       <td class="text-muted small text-uppercase">Feedback Date:</td>
                       <td class="text-dark small">
                         {{ $complaint->feedback->created_at ? $complaint->feedback->created_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') : 'N/A' }}
                       </td>
                     </tr>
                     <tr>
                       <td class="text-muted small text-uppercase">Entered By:</td>
                       <td class="text-dark small">
                         @if($complaint->feedback->enteredBy)
                           {{ $complaint->feedback->enteredBy->name }} <span class="badge bg-secondary" style="font-size: 0.7rem;">Staff</span>
                         @elseif($complaint->feedback->submitted_by)
                           {{ $complaint->feedback->submitted_by }} <span class="badge bg-info text-white" style="font-size: 0.7rem;">Client</span>
                         @else
                           {{ $complaint->client->client_name ?? 'Client' }} <span class="badge bg-info text-white" style="font-size: 0.7rem;">Client</span>
                         @endif
                       </td>
                     </tr>
                     @if($complaint->city_id && $complaint->city)
                        @php
                         $geUser = \App\Models\User::where('city_id', $complaint->city_id)
                           ->whereHas('role', function($q) {
                             $q->where('role_name', 'garrison_engineer');
                           })->first();
                        @endphp
                        @if($geUser)
                        <tr>
                          <td class="text-muted small text-uppercase">GE Group:</td>
                          <td class="text-dark small">{{ $geUser->name }}</td>
                        </tr>
                        @endif
                     @endif
                   </table>
                 </div>
               </div>
     
               @if($complaint->feedback->comments)
               <div class="row mt-3">
                 <div class="col-12">
                   <h6 class="text-dark fw-bold mb-2 small text-uppercase">Complainant Comments:</h6>
                   <div class="p-3 rounded border" style="background-color: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.2) !important;">
                     <p class="text-dark mb-0 small" style="line-height: 1.6; font-size: 0.85rem;">
                       {{ $complaint->feedback->comments }}
                     </p>
                   </div>
                 </div>
               </div>
               @endif
           </div>
        @endif
      </div>
</div>
