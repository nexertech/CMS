@extends('layouts.sidebar')

@section('title', 'User Details — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">User Details</h2>
      <p class="text-light">View user information and records</p>
    </div>
  </div>
</div>

<!-- USER DETAILS -->
<div class="row">
  <!-- Basic Information -->
  <div class="col-md-6 mb-4">
    <div class="card-glass h-100">
      <div class="d-flex align-items-center mb-4" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
        <i data-feather="user" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
        <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Personal  Information</h5>
      </div>
      
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="user" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Name</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $user->name ?? 'N/A' }}</div>
          </div>
        </div>
      </div>
      
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="at-sign" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Username</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $user->username ?? 'N/A' }}</div>
          </div>
        </div>
      </div>
      
      @if($user->phone)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="phone" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Phone</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $user->phone }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($user->city)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="map" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">GE Groups</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $user->city->name ?? 'N/A' }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($user->sector)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="layers" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">GE Nodes</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $user->sector->name ?? 'N/A' }}</div>
          </div>
        </div>
      </div>
      @endif
      
      @if($user->country)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="globe" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Country</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $user->country }}</div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
  
  <!-- Account Information -->
  <div class="col-md-6 mb-4">
    <div class="card-glass h-100">
      <div class="d-flex align-items-center mb-4" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
        <i data-feather="shield" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
        <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Account Information</h5>
      </div>
      
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="shield" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Role</div>
            <div>
              <span class="badge" style="background-color: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.4); font-size: 0.85rem; padding: 6px 12px;">
                {{ $user->role->role_name ?? 'No Role' }}
              </span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="activity" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Status</div>
            <div>
              <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-danger' }}" style="font-size: 0.85rem; padding: 6px 12px; color: #ffffff !important;">
                {{ ucfirst($user->status ?? 'inactive') }}
              </span>
              @if($user->status === 'inactive' && $user->updated_at)
                <span class="text-muted ms-2 small" style="font-size: 0.8rem;">
                  (Since: {{ $user->updated_at->setTimezone('Asia/Karachi')->format('M d, Y H:i:s') }})
                </span>
              @endif
            </div>
          </div>
        </div>
      </div>
      
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="calendar" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Created</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $user->created_at ? $user->created_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') : 'N/A' }}</div>
          </div>
        </div>
      </div>
      
      @if($user->updated_at && $user->updated_at != $user->created_at)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="clock" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Last Updated</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $user->updated_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') }}</div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection

@push('styles')
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
  });
</script>
@endpush
