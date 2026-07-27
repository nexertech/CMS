@extends('layouts.sidebar')

@section('title', 'Employee Details — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-3">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h3 class="text-white mb-1" style="font-size: 1.4rem; font-weight: 700;">Employee Details</h3>
      <p class="text-light mb-0 small" style="opacity: 0.8; font-size: 0.85rem;">View employee information and records</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary btn-sm py-1 px-3">
        <i data-feather="arrow-left" class="me-1" style="width: 14px; height: 14px;"></i>Back
      </a>
    </div>
  </div>
</div>

<!-- EMPLOYEE DETAILS -->
<div class="row g-3">
  <!-- Personal Information -->
  <div class="col-md-6">
    <div class="card-glass h-100">
      <div class="d-flex align-items-center mb-2 pb-2" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
        <i data-feather="user" class="me-2 text-primary" style="width: 17px; height: 17px;"></i>
        <h5 class="text-white mb-0" style="font-size: 0.95rem; font-weight: 600;">Personal Information</h5>
      </div>
      
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="user" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Name</span>
          </div>
          <span class="info-value text-end">{{ $employee->name ?? 'N/A' }}</span>
        </div>
      </div>
      
      @if($employee->phone)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="phone" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Phone</span>
          </div>
          <span class="info-value text-end">{{ $employee->phone }}</span>
        </div>
      </div>
      @endif
      
      @if($employee->address)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="map-pin" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Address</span>
          </div>
          <span class="info-value text-end" style="max-width: 60%;">{{ $employee->address }}</span>
        </div>
      </div>
      @endif
      
      @if($employee->city)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="map" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">GE Groups</span>
          </div>
          <span class="info-value text-end">{{ $employee->city->name ?? $employee->city ?? 'N/A' }}</span>
        </div>
      </div>
      @endif
      
      @if($employee->sector)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="layers" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">GE Nodes</span>
          </div>
          <span class="info-value text-end">{{ $employee->sector->name ?? $employee->sector ?? 'N/A' }}</span>
        </div>
      </div>
      @endif
    </div>
  </div>
  
  <!-- Work Information -->
  <div class="col-md-6">
    <div class="card-glass h-100">
      <div class="d-flex align-items-center mb-2 pb-2" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
        <i data-feather="briefcase" class="me-2 text-primary" style="width: 17px; height: 17px;"></i>
        <h5 class="text-white mb-0" style="font-size: 0.95rem; font-weight: 600;">Work Information</h5>
      </div>
      
      @if($employee->category)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="tag" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Category</span>
          </div>
          <span class="info-value text-end">{{ $employee->category->name ?? $employee->category ?? 'N/A' }}</span>
        </div>
      </div>
      @endif
      
      @if($employee->designation)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="award" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Designation</span>
          </div>
          <span class="info-value text-end">{{ $employee->designation->name ?? $employee->designation ?? 'N/A' }}</span>
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
            <span class="badge {{ $employee->status === 1 ? 'bg-success' : 'bg-danger' }}" style="font-size: 0.75rem; padding: 4px 10px; color: #ffffff !important;">
              {{ ($employee->status ? 'Active' : 'Inactive') }}
            </span>
            @if($employee->status === 0 && $employee->updated_at)
              <span class="text-muted ms-1 small" style="font-size: 0.75rem;">
                (Since: {{ $employee->updated_at->setTimezone('Asia/Karachi')->format('M d, Y') }})
              </span>
            @endif
          </div>
        </div>
      </div>
      
      @if($employee->date_of_hire)
      <div class="info-item">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i data-feather="calendar" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
            <span class="info-label">Hire Date</span>
          </div>
          <span class="info-value text-end">{{ $employee->date_of_hire->format('M d, Y') }}</span>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

@push('styles')
<style>
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
