@extends('layouts.sidebar')

@section('title', 'Employee Details — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Employee Details</h2>
      <p class="text-light">View employee information and records</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
        <i data-feather="arrow-left" class="me-2"></i>Back
      </a>
     
    </div>
  </div>
</div>

<!-- EMPLOYEE DETAILS -->
<div class="row">
  <div class="col-md-6 mb-4">
    <div class="card-glass h-100">
      <div class="d-flex align-items-center mb-4" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
        <i data-feather="user" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
        <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Personal Information</h5>
      </div>
      
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="user" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Name</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $employee->name ?? 'N/A' }}</div>
          </div>
        </div>
      </div>
      
      @if($employee->phone)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="phone" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Phone</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $employee->phone }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($employee->address)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="map-pin" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Address</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $employee->address }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($employee->city)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="map" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">GE Groups</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $employee->city->name ?? $employee->city ?? 'N/A' }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($employee->sector)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="layers" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">GE Nodes</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $employee->sector->name ?? $employee->sector ?? 'N/A' }}</div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
  
  <!-- Work Information -->
  <div class="col-md-6 mb-4">
    <div class="card-glass h-100">
      <div class="d-flex align-items-center mb-4" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
        <i data-feather="briefcase" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
        <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Work Information</h5>
      </div>
      
      @if($employee->category)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="tag" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Category</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $employee->category }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($employee->designation)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="award" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Designation</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $employee->designation }}</div>
          </div>
        </div>
      </div>
      @endif
      
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="activity" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Status</div>
            <div>
              <span class="badge {{ $employee->status === 'active' ? 'bg-success' : 'bg-danger' }}" style="font-size: 0.85rem; padding: 6px 12px; color: #ffffff !important;">
                {{ ucfirst($employee->status ?? 'inactive') }}
              </span>
              @if($employee->status === 'inactive' && $employee->updated_at)
                <span class="text-muted ms-2 small" style="font-size: 0.8rem;">
                  (Since: {{ $employee->updated_at->setTimezone('Asia/Karachi')->format('M d, Y') }})
                </span>
              @endif
            </div>
          </div>
        </div>
      </div>
      
      @if($employee->date_of_hire)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="calendar" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Hire Date</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $employee->date_of_hire->format('M d, Y') }}</div>
          </div>
        </div>
      </div>
      @endif
      
    </div>
  </div>
</div>

@push('styles')
@endpush

@push('scripts')
<script>
  feather.replace();
</script>
@endpush
@endsection
