@extends('layouts.sidebar')

@section('title', 'Frontend User Details — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Frontend User Details</h2>
      <p class="text-light">View frontend user information</p>
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
        <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Personal Information</h5>
      </div>

      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="user" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Name</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $frontend_user->name ?? 'N/A' }}</div>
          </div>
        </div>
      </div>

      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="at-sign" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Username</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $frontend_user->username ?? 'N/A' }}</div>
          </div>
        </div>
      </div>

      @if($frontend_user->email)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="mail" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Email</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $frontend_user->email }}</div>
          </div>
        </div>
      </div>
      @endif

      @if($frontend_user->phone)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="phone" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Phone</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $frontend_user->phone }}</div>
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
          <i data-feather="activity" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Status</div>
            <div>
              <span class="badge {{ $frontend_user->status === 'active' ? 'bg-success' : 'bg-danger' }}" style="font-size: 0.85rem; padding: 6px 12px; color: #ffffff !important;">
                {{ ucfirst($frontend_user->status ?? 'inactive') }}
              </span>
              @if($frontend_user->status === 'inactive' && $frontend_user->updated_at)
                <span class="text-muted ms-2 small" style="font-size: 0.8rem;">
                  (Since: {{ $frontend_user->updated_at->setTimezone('Asia/Karachi')->format('M d, Y H:i:s') }})
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
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $frontend_user->created_at ? $frontend_user->created_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') : 'N/A' }}</div>
          </div>
        </div>
      </div>

      @if($frontend_user->updated_at && $frontend_user->updated_at != $frontend_user->created_at)
      <div class="info-item mb-3">
        <div class="d-flex align-items-start">
          <i data-feather="clock" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
          <div class="flex-grow-1">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Last Updated</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $frontend_user->updated_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') }}</div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

<!-- Assigned Locations -->
@if(isset($assignedLocations) && $assignedLocations->count() > 0)
<div class="mb-4">
  <div class="d-flex align-items-center mb-4">
    <i data-feather="map-pin" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
    <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Assigned GE Groups & Nodes</h5>
  </div>

  @php
    // Group locations by CME -> City
    $groupedByCme = [];
    foreach ($assignedLocations as $location) {
      $city = $location->city;
      if (!$city) continue;

      // Lazy load CME if not eager loaded
      $cme = $city->cme;
      $cmeId = $cme ? $cme->id : 'no-cme';
      $cmeName = $cme ? $cme->name : 'Other Locations';

      if (!isset($groupedByCme[$cmeId])) {
        $groupedByCme[$cmeId] = [
          'name' => $cmeName,
          'cities' => []
        ];
      }

      $cityId = $city->id;
      if (!isset($groupedByCme[$cmeId]['cities'][$cityId])) {
        $groupedByCme[$cmeId]['cities'][$cityId] = [
          'name' => $city->name,
          'sectors' => []
        ];
      }

      if ($location->sector) {
        // Avoid duplicates if multiple rows point to same sector
        $sectorId = $location->sector->id;
        $exists = false;
        foreach ($groupedByCme[$cmeId]['cities'][$cityId]['sectors'] as $s) {
            if ($s->id === $sectorId) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $groupedByCme[$cmeId]['cities'][$cityId]['sectors'][] = $location->sector;
        }
      }
    }
  @endphp

  <div class="row">
    @foreach($groupedByCme as $cmeData)
      <div class="col-md-6 mb-4">
        <div class="card-glass h-100">
          <!-- CME Header -->
          <div class="d-flex align-items-center mb-3 pb-2" style="border-bottom: 1px solid rgba(59, 130, 246, 0.2);">
            <i data-feather="layers" class="me-2 text-warning" style="width: 18px; height: 18px;"></i>
            <h6 class="text-white mb-0 fw-bold">{{ $cmeData['name'] }}</h6>
          </div>

          <!-- Cities List -->
          <div class="cities-list">
            @foreach($cmeData['cities'] as $cityData)
              <div class="mb-3 last:mb-0">
                <div class="d-flex align-items-center mb-2">
                  <i data-feather="map" class="me-2 text-info" style="width: 16px; height: 16px;"></i>
                  <span class="text-light fw-bold" style="font-size: 0.95rem;">{{ $cityData['name'] }}</span>
                </div>

                @if(count($cityData['sectors']) > 0)
                  <div class="ms-4 ps-2" style="border-left: 2px solid rgba(255, 255, 255, 0.1);">
                    @foreach($cityData['sectors'] as $sector)
                      <div class="d-flex align-items-center mb-1">
                        <i data-feather="corner-down-right" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
                        <span class="text-white-50" style="font-size: 0.9rem;">{{ $sector->name }}</span>
                      </div>
                    @endforeach
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>
@else
<div class="card-glass mb-4">
  <div class="d-flex align-items-center mb-4" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
    <i data-feather="map-pin" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
    <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Assigned GE Groups & Nodes</h5>
  </div>
  <div class="text-center py-3">
    <i data-feather="map-pin" class="feather-lg text-muted mb-2"></i>
    <p class="text-muted mb-0">No locations assigned yet</p>
  </div>
</div>
@endif


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
