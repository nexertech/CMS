
@extends('layouts.sidebar')

@section('title', 'Dashboard — CMS Admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<style>
  /* Enhanced matte finish for stat cards */
  .stat-card {
    position: relative;
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    opacity: 0.85;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2), 0 2px 10px rgba(0, 0, 0, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    border-radius: 3px !important;
    transition: all 0.3s ease;
    filter: saturate(0.9) brightness(0.95);
  }

  .stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.08);
    border-radius: inherit;
    pointer-events: none;
    z-index: 1;
  }

  .stat-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.03) 0%, rgba(0, 0, 0, 0.02) 100%);
    border-radius: inherit;
    pointer-events: none;
    z-index: 1;
  }

  .stat-card > * {
    position: relative;
    z-index: 2;
  }

  .stat-card:hover {
    opacity: 0.92;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.25), 0 4px 14px rgba(0, 0, 0, 0.18) !important;
    transform: translateY(-2px);
    filter: saturate(0.95) brightness(0.98);
  }

  /* Reduce gradient intensity for matte look */
  .stat-card[style*="linear-gradient"] {
    background-blend-mode: overlay !important;
  }

  /* Global styling for stat numbers and labels */
  .stat-card .stat-number {
    font-weight: 800 !important;
    font-size: 1.4rem !important;
  }

  .stat-card .stat-label {
    font-weight: 700 !important;
    font-size: 1.0rem !important;
  }

  /* Matte finish for chart containers */
  .card-glass.chart-container {
    position: relative;
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    opacity: 0.88;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2), 0 2px 10px rgba(0, 0, 0, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    filter: saturate(0.9) brightness(0.95);
  }

  .card-glass.chart-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.06);
    border-radius: inherit;
    pointer-events: none;
    z-index: 1;
  }

  .card-glass.chart-container > * {
    position: relative;
    z-index: 2;
  }

  .card-glass.chart-container:hover {
    opacity: 0.93;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.25), 0 4px 14px rgba(0, 0, 0, 0.18) !important;
  }

  /* Reduce column spacing in Recent Complaints table */
  .table-responsive .table.table-dark th,
  .table-responsive .table.table-dark td {
    padding: 0.6rem 0.5rem !important;
    white-space: nowrap;
  }

  .table-responsive .table.table-dark th:first-child,
  .table-responsive .table.table-dark td:first-child {
    padding-left: 0.75rem !important;
  }

  .table-responsive .table.table-dark th:last-child,
  .table-responsive .table.table-dark td:last-child {
    padding-right: 0.75rem !important;
  }

  .table-responsive .table.table-dark th {
    font-size: 0.85rem;
    font-weight: 600;
  }

  .table-responsive .table.table-dark td {
    font-size: 0.875rem;
  }

  /* Subtle matte finish for status badges */
  .status-badge {
    position: relative;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    filter: saturate(0.92) brightness(0.95);
    transition: all 0.2s ease;
  }

  .status-badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.05);
    border-radius: inherit;
    pointer-events: none;
    z-index: 1;
  }

  .status-badge > * {
    position: relative;
    z-index: 2;
  }

  .status-badge:hover {
    filter: saturate(0.95) brightness(0.98);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
  }

  /* Subtle matte finish for priority badges */
  .priority-badge {
    position: relative;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    filter: saturate(0.92) brightness(0.95);
    transition: all 0.2s ease;
  }

  .priority-badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.05);
    border-radius: inherit;
    pointer-events: none;
    z-index: 1;
  }

  .priority-badge > * {
    position: relative;
    z-index: 2;
  }

  .priority-badge:hover {
    filter: saturate(0.95) brightness(0.98);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
  }

  /* Force font size for filter labels */
  .filter-box .form-label,
  .filter-box .col-auto .form-label {
    font-size: 1rem !important;
    font-weight: 700 !important;
  }

  /* Matte finish for GE Feedback Overview section */
  .card-glass:has(h5:contains("GE Feedback Overview")),
  .card-glass:has([class*="ge-feedback"]) {
    position: relative;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.18), 0 2px 6px rgba(0, 0, 0, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.12) !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    filter: saturate(0.88) brightness(0.93);
    transition: all 0.3s ease;
  }

  /* Alternative selector for GE Feedback Overview */
  .row.mt-5.mb-5 .card-glass {
    position: relative;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.18), 0 2px 6px rgba(0, 0, 0, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.12) !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    filter: saturate(0.88) brightness(0.93);
    transition: all 0.3s ease;
  }

  .row.mt-5.mb-5 .card-glass::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.08);
    border-radius: inherit;
    pointer-events: none;
    z-index: 1;
  }

  .row.mt-5.mb-5 .card-glass:hover {
    filter: saturate(0.92) brightness(0.96);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22), 0 3px 8px rgba(0, 0, 0, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.18) !important;
  }

  /* Dropdown arrow for select boxes - High specificity */
  .filter-box .form-select,
  .filter-box select.form-select,
  .filter-box #cityFilter,
  .filter-box #sectorFilter,
  .filter-box #categoryFilter,
  .filter-box #complaintStatusFilter,
  .filter-box #dateRangeFilter,
  #cityFilter,
  #sectorFilter,
  #categoryFilter,
  #complaintStatusFilter,
  #dateRangeFilter {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 0.75rem center !important;
    background-size: 16px 12px !important;
    padding-right: 2.5rem !important;
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
  }

  /* Override dashboard.css for filter selects */
  .theme-light .filter-box .form-select,
  .theme-dark .filter-box .form-select,
  .theme-night .filter-box .form-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 0.75rem center !important;
    background-size: 16px 12px !important;
    padding-right: 2.5rem !important;
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
  }
</style>
@endpush

@section('content')

<!-- DASHBOARD HEADER -->
<div class="mb-5 dashboard-header">
  <h2 class="text-white mb-2">Dashboard Overview</h2>
  <p class="text-light">Real-time complaint management system</p>
</div>

<!-- FILTERS SECTION -->
@php
  $user = Auth::user();
  $filterCityId = request('city_id');
  if (!$filterCityId && isset($cityId)) {
    $filterCityId = $cityId;
  }

  $userHasNoCity = $user && (is_null($user->city_id) || $user->city_id == 0 || $user->city_id === '');
  $showCityFilter = $userHasNoCity;
  $showSectorFilter = $user && (!$user->sector_id || $user->sector_id == null || $user->sector_id == 0 || $user->sector_id == '');
@endphp

@if($showCityFilter || $showSectorFilter || $categories->count() > 0 || (isset($complaintStatuses) && count($complaintStatuses) > 0) || true)
<div class="mb-5 d-flex justify-content-center">
  <div class="filter-box" style="display: inline-block; width: fit-content;">
    <form id="dashboardFiltersForm" method="GET" action="{{ route('admin.dashboard') }}">
      <div class="row g-3 align-items-end">
        @if($showCityFilter)
        @if(isset($cmesList) && $cmesList->count() > 0)
        <div class="col-auto">
          <label class="form-label mb-1" style="font-size: 1rem !important; color: #1e293b !important; font-weight: 700 !important;">CMES</label>
          <select class="form-select" id="cmesFilter" name="cmes_id" style="font-size: 0.9rem; width: 180px;">
            <option value="">Select CMES</option>
            @foreach($cmesList as $cme)
              <option value="{{ $cme->id }}" {{ (request('cmes_id') == $cme->id || (isset($cmesId) && $cmesId == $cme->id)) ? 'selected' : '' }}>{{ $cme->name }}</option>
            @endforeach
          </select>
        </div>
        @endif
        <div class="col-auto" id="cityFilterContainer">
          <label class="form-label mb-1" style="font-size: 1rem !important; color: #1e293b !important; font-weight: 700 !important;">GE</label>
          <select class="form-select" id="cityFilter" name="city_id" style="font-size: 0.9rem; width: 180px; background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 16 16\'%3e%3cpath fill=\'none\' stroke=\'%23343a40\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2 5l6 6 6-6\'/%3e%3c/svg%3e') !important; background-repeat: no-repeat !important; background-position: right 0.75rem center !important; background-size: 16px 12px !important; padding-right: 2.5rem !important; appearance: none !important; -webkit-appearance: none !important; -moz-appearance: none !important;">
            <option value="">Select GE</option>
            @if($cities && $cities->count() > 0)
              @foreach($cities as $city)
                @php
                  $geUser = $city->users->where('role_id', $geRole->id ?? null)->where('status', 'active')->first();
                  $displayName = $city->name;
                  if ($geUser) {
                    if ($geUser->name) {
                      $displayName = $geUser->name . ' - ' . $city->name;
                    } elseif ($geUser->username) {
                      $displayName = $geUser->username . ' - ' . $city->name;
                    }
                  }
                @endphp
                <option value="{{ $city->id }}" {{ (request('city_id') == $city->id || $cityId == $city->id) ? 'selected' : '' }}>{{ $displayName }}</option>
              @endforeach
            @endif
          </select>
        </div>
        @endif

        @if($showSectorFilter)
        <div class="col-auto">
          <label class="form-label mb-1" style="font-size: 1rem !important; color: #1e293b !important; font-weight: 700 !important;">GE Nodes</label>
          <select class="form-select" id="sectorFilter" name="sector_id" style="font-size: 0.9rem; width: 180px; background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 16 16\'%3e%3cpath fill=\'none\' stroke=\'%23343a40\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2 5l6 6 6-6\'/%3e%3c/svg%3e') !important; background-repeat: no-repeat !important; background-position: right 0.75rem center !important; background-size: 16px 12px !important; padding-right: 2.5rem !important; appearance: none !important; -webkit-appearance: none !important; -moz-appearance: none !important;">
            <option value="">Select GE Nodes</option>
            @if($sectors && $sectors->count() > 0)
              @foreach($sectors as $sector)
                <option value="{{ $sector->id }}" {{ (request('sector_id') == $sector->id || $sectorId == $sector->id) ? 'selected' : '' }}>{{ $sector->name }}</option>
              @endforeach
            @endif
          </select>
        </div>
        @endif

        <div class="col-auto">
          <label class="form-label mb-1" style="font-size: 1rem !important; color: #1e293b !important; font-weight: 700 !important;">Complaint Category</label>
          <select class="form-select" id="categoryFilter" name="category" style="font-size: 0.9rem; width: 180px; background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 16 16\'%3e%3cpath fill=\'none\' stroke=\'%23343a40\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2 5l6 6 6-6\'/%3e%3c/svg%3e') !important; background-repeat: no-repeat !important; background-position: right 0.75rem center !important; background-size: 16px 12px !important; padding-right: 2.5rem !important; appearance: none !important; -webkit-appearance: none !important; -moz-appearance: none !important;">
            <option value="">Select Category</option>
            @if($categories && $categories->count() > 0)
              @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ (request('category') == $cat || $category == $cat) ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
              @endforeach
            @endif
          </select>
        </div>

        @if(isset($complaintStatuses) && count($complaintStatuses) > 0)
        <div class="col-auto">
          <label class="form-label mb-1" style="font-size: 1rem !important; color: #1e293b !important; font-weight: 700 !important;">Complaints Status</label>
          <select class="form-select" id="complaintStatusFilter" name="complaint_status" style="font-size: 0.9rem; width: 180px; background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 16 16\'%3e%3cpath fill=\'none\' stroke=\'%23343a40\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2 5l6 6 6-6\'/%3e%3c/svg%3e') !important; background-repeat: no-repeat !important; background-position: right 0.75rem center !important; background-size: 16px 12px !important; padding-right: 2.5rem !important; appearance: none !important; -webkit-appearance: none !important; -moz-appearance: none !important;">
            <option value="">Select Status</option>
            @foreach($complaintStatuses as $statusKey => $statusLabel)
              <option value="{{ $statusKey }}" {{ (request('complaint_status') == $statusKey || $complaintStatus == $statusKey) ? 'selected' : '' }}>{{ $statusLabel }}</option>
            @endforeach
          </select>
        </div>
        @endif

        <div class="col-auto">
          <label class="form-label mb-1" style="font-size: 1rem !important; color: #1e293b !important; font-weight: 700 !important;">Date Range</label>
          <select class="form-select" id="dateRangeFilter" name="date_range" style="font-size: 0.9rem; width: 180px; background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 16 16\'%3e%3cpath fill=\'none\' stroke=\'%23343a40\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2 5l6 6 6-6\'/%3e%3c/svg%3e') !important; background-repeat: no-repeat !important; background-position: right 0.75rem center !important; background-size: 16px 12px !important; padding-right: 2.5rem !important; appearance: none !important; -webkit-appearance: none !important; -moz-appearance: none !important;">
            <option value="">Select Date Range</option>
            <option value="yesterday" {{ (request('date_range') == 'yesterday' || $dateRange == 'yesterday') ? 'selected' : '' }}>Yesterday</option>
            <option value="today" {{ (request('date_range') == 'today' || $dateRange == 'today') ? 'selected' : '' }}>Today</option>
            <option value="this_week" {{ (request('date_range') == 'this_week' || $dateRange == 'this_week') ? 'selected' : '' }}>This Week</option>
            <option value="last_week" {{ (request('date_range') == 'last_week' || $dateRange == 'last_week') ? 'selected' : '' }}>Last Week</option>
            <option value="this_month" {{ (request('date_range') == 'this_month' || $dateRange == 'this_month') ? 'selected' : '' }}>This Month</option>
            <option value="last_month" {{ (request('date_range') == 'last_month' || $dateRange == 'last_month') ? 'selected' : '' }}>Last Month</option>
            <option value="last_6_months" {{ (request('date_range') == 'last_6_months' || $dateRange == 'last_6_months') ? 'selected' : '' }}>Last 6 Months</option>
          </select>
        </div>

        <div class="col-auto">
          <label class="form-label small text-muted mb-1" style="font-size: 0.8rem;">&nbsp;</label>
          <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetDashboardFilters()" style="font-size: 0.9rem; padding: 0.5rem 1.25rem;">
            <i data-feather="refresh-cw" class="me-1" style="width: 16px; height: 16px;"></i>Reset
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endif

<!-- STATISTICS CARDS -->
<div class="row mb-5 g-3 justify-content-center">
  <div class="col-md-2 col-lg-2">
    <div class="stat-card" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;">
      <div class="d-flex align-items-center justify-content-between">
        <div class="flex-grow-1">
          <div class="stat-number">{{ $stats['total_complaints'] ?? 0 }}</div>
          <div class="stat-label">Total Complaints</div>
        </div>
        <div class="stat-icon">
          <i data-feather="alert-circle" class="feather-lg"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-2 col-lg-2">
    <div class="stat-card" style="background: linear-gradient(135deg, #dd4040ff 0%, #b13030 100%) !important;">
      <div class="d-flex align-items-center justify-content-between">
        <div class="flex-grow-1">
          <div class="stat-number">{{ $stats['in_progress_complaints'] ?? 0 }}</div>
          <div class="stat-label">In Progress</div>
        </div>
        <div class="stat-icon">
          <i data-feather="clock" class="feather-lg"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-2 col-lg-2">
    <div class="stat-card" style="background: linear-gradient(135deg, #475569 0%, #334155 100%) !important;">
      <div class="d-flex align-items-center justify-content-between">
        <div class="flex-grow-1">
          <div class="stat-number">{{ $stats['addressed_complaints'] ?? 0 }}</div>
          <div class="stat-label">Addressed</div>
        </div>
        <div class="stat-icon">
          <i data-feather="check-circle" class="feather-lg"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-2 col-lg-2">
    <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;">
      <div class="d-flex align-items-center justify-content-between">
        <div class="flex-grow-1">
          <div class="stat-number">{{ $stats['work_performa'] ?? 0 }}</div>
          <div class="stat-label">Work Performa</div>
        </div>
        <div class="stat-icon">
          <i data-feather="file-text" class="feather-lg"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-2 col-lg-2">
    <div class="stat-card" style="background: linear-gradient(135deg, #eab308 0%, #fcbd2bff 100%) !important;">
      <div class="d-flex align-items-center justify-content-between">
        <div class="flex-grow-1">
          <div class="stat-number">{{ $stats['maint_performa'] ?? 0 }}</div>
          <div class="stat-label">Maintenance Performa</div>
        </div>
        <div class="stat-icon">
          <i data-feather="tool" class="feather-lg"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-2 col-lg-2">
    <div class="stat-card" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%) !important;">
      <div class="d-flex align-items-center justify-content-between">
        <div class="flex-grow-1">
          <div class="stat-number">{{ $stats['un_authorized'] ?? 0 }}</div>
          <div class="stat-label">Un Authorized</div>
        </div>
        <div class="stat-icon">
          <i data-feather="x-octagon" class="feather-lg"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-2 col-lg-2">
    <div class="stat-card" style="background: linear-gradient(135deg, #0deb7cff 0%, #22995dff 100%) !important;">
      <div class="d-flex align-items-center justify-content-between">
        <div class="flex-grow-1">
          <div class="stat-number">{{ $stats['product_na'] ?? 0 }}</div>
          <div class="stat-label">Product N/A</div>
        </div>
        <div class="stat-icon" style="display: flex !important; visibility: visible !important;">
          <i data-feather="box" class="feather-lg" style="display: block !important; visibility: visible !important; width: 24px !important; height: 24px !important;"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-2 col-lg-2">
    <div class="stat-card" style="background: linear-gradient(135deg, #05cbee 0%, #05bfee 100%) !important;">
      <div class="d-flex align-items-center justify-content-between">
        <div class="flex-grow-1">
          <div class="stat-number">{{ $stats['pertains_to_ge_const_isld'] ?? 0 }}</div>
          <div class="stat-label">Pertains to GE/Const/Isld</div>
        </div>
        <div class="stat-icon">
          <i data-feather="map-pin" class="feather-lg"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-2 col-lg-2">
    <div class="stat-card" style="background: linear-gradient(135deg, #808000 0%, #808000 100%) !important;">
      <div class="d-flex align-items-center justify-content-between">
        <div class="flex-grow-1">
          <div class="stat-number">{{ $stats['barak_damages'] ?? 0 }}</div>
          <div class="stat-label">Barrak Damages</div>
        </div>
        <div class="stat-icon">
          <i data-feather="alert-triangle" class="feather-lg"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-2 col-lg-2">
    <div class="stat-card" style="background: linear-gradient(135deg, #e00d0dff 0%, #b91c1c 100%) !important;">
      <div class="d-flex align-items-center justify-content-between">
        <div class="flex-grow-1">
          <div class="stat-number">{{ $stats['overdue_complaints'] ?? 0 }}</div>
          <div class="stat-label">Overdue Complaints</div>
        </div>
        <div class="stat-icon">
          <i data-feather="alert-octagon" class="feather-lg"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- CHARTS ROW -->
<div class="row mb-5 g-4">
  <div class="col-md-6">
    <div class="card-glass chart-container">
      <h5 class="mb-4 text-white" style="font-weight: 700; font-size: 1.25rem;">
        <i data-feather="pie-chart" class="me-2" style="width: 24px; height: 24px;"></i>
        Complaints by Status
      </h5>
      <div id="complaintsStatusChart" style="height: 300px;"></div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card-glass chart-container">
      <h5 class="mb-4 text-white" style="font-weight: 700; font-size: 1.25rem;">
        <i data-feather="bar-chart-2" class="me-2" style="width: 24px; height: 24px;"></i>
        Complaints by Category
      </h5>
      <div id="complaintsTypeChart" style="height: 300px;"></div>
    </div>
  </div>
</div>

<!-- GE FEEDBACK OVERVIEW SECTION -->
@php
  $showGEFeedback = false;
  $user = auth()->user();

  // Location filter logic:
  // 1. If user's city_id AND sector_id are both null - show all data
  // 2. If user's city_id is set but sector_id is null - show only their city's data
  // 3. If user has sector_id - they shouldn't see GE Feedback Overview
  $canSeeAllData = (!$user->city_id && !$user->sector_id);
  $canSeeCityData = ($user->city_id && !$user->sector_id);

  // Show section based on location filter only
  if ($canSeeAllData || $canSeeCityData) {
    $showGEFeedback = true;
  }
@endphp

@if($showGEFeedback)
@php
  $hasData = isset($geProgress) && count($geProgress) > 0;
  $displayedProgress = $hasData ? array_slice($geProgress, 0, 3) : [];
  $hasMore = $hasData && count($geProgress) > 3;
@endphp
<div class="row mt-5 mb-5">
  <div class="col-12">
    <div class="card-glass" style="padding: 2.5rem;">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 text-white" style="font-weight: 700; font-size: 1.5rem;">
          <i data-feather="users" class="me-2" style="width: 28px; height: 28px;"></i>GE Feedback Overview
        </h5>
      </div>
      @if($hasData)
      <div class="row g-4">
        @php
          $colorSchemes = [
            ['bg' => 'linear-gradient(135deg, #93c5fd 0%, #60a5fa 100%)', 'icon' => '#bfdbfe', 'progress' => 'linear-gradient(90deg, #93c5fd, #bfdbfe)'],
            ['bg' => 'linear-gradient(135deg, #6ee7b7 0%, #34d399 100%)', 'icon' => '#a7f3d0', 'progress' => 'linear-gradient(90deg, #6ee7b7, #a7f3d0)'],
            ['bg' => 'linear-gradient(135deg, #fcd34d 0%, #fbbf24 100%)', 'icon' => '#fde68a', 'progress' => 'linear-gradient(90deg, #fcd34d, #fde68a)'],
            ['bg' => 'linear-gradient(135deg, #c4b5fd 0%, #a78bfa 100%)', 'icon' => '#ddd6fe', 'progress' => 'linear-gradient(90deg, #c4b5fd, #ddd6fe)'],
            ['bg' => 'linear-gradient(135deg, #5eead4 0%, #2dd4bf 100%)', 'icon' => '#99f6e4', 'progress' => 'linear-gradient(90deg, #5eead4, #99f6e4)'],
          ];
          $totalCards = count($displayedProgress);
        @endphp
        @foreach($displayedProgress as $index => $geData)
        @php
          $colorScheme = $colorSchemes[$index % count($colorSchemes)];
          $progressColor = $geData['progress_percentage'] >= 80 ? 'linear-gradient(90deg, #ffffff, #f0f9ff)' :
                          ($geData['progress_percentage'] >= 50 ? 'linear-gradient(90deg, #ffffff, #f0f9ff)' :
                          ($geData['progress_percentage'] >= 30 ? 'linear-gradient(90deg, #fff7ed, #ffffff)' : 'linear-gradient(90deg, #fef2f2, #ffffff)'));

          $colClasses = 'col-md-6 col-lg-4 mb-3';
          $offsetClasses = '';

          if ($totalCards == 1) {
            $colClasses = 'col-md-6 col-lg-4 mb-3';
            $offsetClasses = 'offset-md-3 offset-lg-4';
          }
          elseif ($totalCards % 3 == 2 && $index >= $totalCards - 2) {
            if ($index == $totalCards - 2) {
              $offsetClasses = 'offset-lg-2';
            }
          }
          elseif ($totalCards % 3 == 1 && $index == $totalCards - 1) {
            $offsetClasses = 'offset-lg-4';
          }
        @endphp
        <div class="{{ $colClasses }} {{ $offsetClasses }}">
          <div class="ge-progress-card" style="padding: 1.25rem 1.5rem !important; background: {{ $colorScheme['bg'] }} !important; border: none !important; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2) !important; border-radius: 0 !important;">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <h6 class="mb-1 text-white" style="font-weight: 700; font-size: 1.15rem; color: #ffffff !important;">{{ $geData['ge_name'] ?? ($geData['ge']->name ?? $geData['ge']->username ?? 'N/A') }}</h6>
                <p class="mb-0 text-white" style="font-size: 0.95rem; opacity: 0.95; color: #ffffff !important;">
                  <i data-feather="map-pin" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; color: #ffffff;"></i>
                  <span style="color: #ffffff !important; margin-left: 0.25rem;">{{ $geData['city'] }}</span>
                </p>
              </div>
              <div style="width: 45px; height: 45px; background: rgba(255, 255, 255, 0.25); border-radius: 12px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);">
                <i data-feather="user-check" style="width: 22px; height: 22px; color: #ffffff;"></i>
              </div>
            </div>
            <div class="mb-2">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-white" style="font-size: 1rem; font-weight: 600; opacity: 0.95; color: #ffffff !important;">Performance</span>
                <span class="text-white" style="font-weight: 800; font-size: 1.6rem; color: #ffffff !important;">{{ $geData['progress_percentage'] }}%</span>
              </div>
              <div class="progress" style="height: 14px; background-color: rgba(0, 0, 0, 0.25); border-radius: 8px; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.15);">
                <div class="progress-bar" role="progressbar"
                     style="width: {{ $geData['progress_percentage'] }}%; background: linear-gradient(90deg, #ffffff 0%, rgba(255, 255, 255, 0.8) 100%); border-radius: 8px; box-shadow: 0 2px 12px rgba(255, 255, 255, 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.8); transition: width 0.6s ease; border: 1px solid rgba(255, 255, 255, 0.4);"
                     aria-valuenow="{{ $geData['progress_percentage'] }}"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-2" style="border-top: 1px solid rgba(255, 255, 255, 0.25);">
              <span class="text-white" style="font-size: 0.95rem; font-weight: 600; display: flex; align-items: center; gap: 6px; color: #ffffff !important;">
                <i data-feather="check-circle" style="width: 18px; height: 18px; color: #ffffff;"></i>
                <span style="font-weight: 700; color: #ffffff !important;">{{ $geData['resolved_complaints'] }}</span> <span style="color: #ffffff !important; opacity: 0.9;">Resolved</span>
              </span>
              <span class="text-white" style="font-size: 0.95rem; font-weight: 600; display: flex; align-items: center; gap: 6px; color: #ffffff !important;">
                <i data-feather="file-text" style="width: 18px; height: 18px; color: #ffffff;"></i>
                <span style="font-weight: 700; color: #ffffff !important;">{{ $geData['total_complaints'] }}</span> <span style="color: #ffffff !important; opacity: 0.9;">Total</span>
              </span>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="text-center py-5">
        <i data-feather="users" class="feather-lg mb-3 text-muted"></i>
        <p class="text-muted mb-0">No GE Feedback data available at the moment.</p>
      </div>
      @endif
      @if($hasData && $hasMore)
      <div class="row g-4 mt-2" id="allGEProgress" style="display: none;">
        @php
          $remainingProgress = array_slice($geProgress, 3);
          $totalRemainingCards = count($remainingProgress);
        @endphp
        @foreach($remainingProgress as $index => $geData)
        @php
          $actualIndex = $index + 3;
          $colorScheme = $colorSchemes[$actualIndex % count($colorSchemes)];
          $progressColor = $geData['progress_percentage'] >= 80 ? 'linear-gradient(90deg, #ffffff, #f0f9ff)' :
                          ($geData['progress_percentage'] >= 50 ? 'linear-gradient(90deg, #ffffff, #f0f9ff)' :
                          ($geData['progress_percentage'] >= 30 ? 'linear-gradient(90deg, #fff7ed, #ffffff)' : 'linear-gradient(90deg, #fef2f2, #ffffff)'));

          $colClasses = 'col-md-6 col-lg-4 mb-3';
          $offsetClasses = '';

          if ($totalRemainingCards == 1) {
            $colClasses = 'col-md-6 col-lg-4 mb-3';
            $offsetClasses = 'offset-md-3 offset-lg-4';
          }
          elseif ($totalRemainingCards % 3 == 2 && $index >= $totalRemainingCards - 2) {
            if ($index == $totalRemainingCards - 2) {
              $offsetClasses = 'offset-lg-2';
            }
          }
          elseif ($totalRemainingCards % 3 == 1 && $index == $totalRemainingCards - 1) {
            $offsetClasses = 'offset-lg-4';
          }
        @endphp
        <div class="{{ $colClasses }} {{ $offsetClasses }}">
          <div class="ge-progress-card" style="padding: 1.25rem 1.5rem !important; background: {{ $colorScheme['bg'] }} !important; border: none !important; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2) !important; border-radius: 0 !important;">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <h6 class="mb-1 text-white" style="font-weight: 700; font-size: 1rem; color: #ffffff !important;">{{ $geData['ge_name'] ?? ($geData['ge']->name ?? $geData['ge']->username ?? 'N/A') }}</h6>
                <p class="mb-0 text-white" style="font-size: 0.8rem; opacity: 0.95; color: #ffffff !important;">
                  <i data-feather="map-pin" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; color: #ffffff;"></i>
                  <span style="color: #ffffff !important; margin-left: 0.25rem;">{{ $geData['city'] }}</span>
                </p>
              </div>
              <div style="width: 45px; height: 45px; background: rgba(255, 255, 255, 0.25); border-radius: 12px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);">
                <i data-feather="user-check" style="width: 22px; height: 22px; color: #ffffff;"></i>
              </div>
            </div>
            <div class="mb-2">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-white" style="font-size: 0.85rem; font-weight: 600; opacity: 0.95; color: #ffffff !important;">Progress</span>
                <span class="text-white" style="font-weight: 800; font-size: 1.4rem; color: #ffffff !important;">{{ $geData['progress_percentage'] }}%</span>
              </div>
              <div class="progress" style="height: 14px; background-color: rgba(0, 0, 0, 0.25); border-radius: 8px; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.15);">
                <div class="progress-bar" role="progressbar"
                     style="width: {{ $geData['progress_percentage'] }}%; background: linear-gradient(90deg, #ffffff 0%, rgba(255, 255, 255, 0.8) 100%); border-radius: 8px; box-shadow: 0 2px 12px rgba(255, 255, 255, 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.8); transition: width 0.6s ease; border: 1px solid rgba(255, 255, 255, 0.4);"
                     aria-valuenow="{{ $geData['progress_percentage'] }}"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-2" style="border-top: 1px solid rgba(255, 255, 255, 0.25);">
              <span class="text-white" style="font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 6px; color: #ffffff !important;">
                <i data-feather="check-circle" style="width: 16px; height: 16px; color: #ffffff;"></i>
                <span style="font-weight: 700; color: #ffffff !important;">{{ $geData['resolved_complaints'] }}</span> <span style="color: #ffffff !important; opacity: 0.9;">Resolved</span>
              </span>
              <span class="text-white" style="font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 6px; color: #ffffff !important;">
                <i data-feather="file-text" style="width: 16px; height: 16px; color: #ffffff;"></i>
                <span style="font-weight: 700; color: #ffffff !important;">{{ $geData['total_complaints'] }}</span> <span style="color: #ffffff !important; opacity: 0.9;">Total</span>
              </span>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @endif
      @if($hasMore)
      <div class="text-center mt-4">
        <button type="button" class="btn btn-accent btn-sm" onclick="showAllGEProgress()" id="seeMoreBtn">
          <i data-feather="chevron-down" class="me-1" style="width: 16px; height: 16px;"></i>See More
        </button>
      </div>
      @endif
    </div>
  </div>
</div>
@endif

<!-- MONTHLY TRENDS CHART -->
<div class="row mb-5">
  <div class="col-12">
    <div class="card-glass chart-container">
      <h5 class="mb-4 text-white" style="font-weight: 700; font-size: 1.25rem;">
        <i data-feather="trending-up" class="me-2" style="width: 24px; height: 24px;"></i>
        Monthly Trends
      </h5>
      <div id="monthlyTrendsChart" style="height: 420px;"></div>
    </div>
  </div>
</div>

<!-- TABLES ROW -->
<div class="row mb-5">
  <div class="col-12">
    <div class="card-glass" style="padding: 2rem;">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 text-white" style="font-weight: 700; font-size: 1.25rem;">
          <i data-feather="list" class="me-2" style="width: 24px; height: 24px;"></i>
          Recent Complaints
        </h5>
        <a href="{{ route('admin.complaints.index') }}" class="btn btn-outline-warning btn-sm">View All</a>
      </div>
      <div class="table-responsive">
        <table class="table table-dark" style="table-layout: auto; width: 100%;">
          <thead>
            <tr>
              <th style="width: 8%;">ID</th>
              <th style="width: 18%;">Complainant</th>
              <th style="width: 12%;">Category</th>
              <th style="width: 15%;">Assigned To</th>
              <th style="width: 18%;">Status</th>
              <th style="width: 12%;">Priority</th>
              <th style="width: 17%;">Registered Date/Time</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentComplaints ?? [] as $complaint)
            <tr>
              <td><strong>{{ (int)$complaint->id }}</strong></td>
              <td>{{ $complaint->client->client_name ?? 'N/A' }}</td>
              <td>{{ $complaint->getCategoryDisplayAttribute() }}</td>
              <td>
                @if($complaint->assignedEmployee)
                  <span style="font-size: 0.875rem;">{{ $complaint->assignedEmployee->name ?? 'N/A' }}</span>
                @else
                  <span style="color: #94a3b8; font-style: italic; font-size: 0.875rem;">Unassigned</span>
                @endif
              </td>
              <td>
                @php
                  // Map 'new' status to 'assigned' for display
                  $displayStatus = ($complaint->status === 'new') ? 'assigned' : $complaint->status;
                  $fullStatusText = $complaint->getStatusDisplayAttribute();
                  $shortStatusText = $fullStatusText;
                  $hoverText = $fullStatusText;

                  // Set short text and hover text for specific statuses
                  if($displayStatus === 'pertains_to_ge_const_isld') {
                    $shortStatusText = 'Pertains to GE';
                    $hoverText = $fullStatusText;
                  } elseif($displayStatus === 'maint_priced_performa') {
                    $shortStatusText = 'Maint Priced';
                    $hoverText = $fullStatusText;
                  }
                @endphp
                  @if($displayStatus === 'resolved')
                   <span class="status-badge status-{{ $displayStatus }}" style="background-color: #64748b !important; color: #ffffff !important; border-color: #475569 !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; width: 120px !important; text-align: center !important;" title="{{ $fullStatusText }}">Addressed</span>
                  @elseif($displayStatus === 'in_progress')
                   <span class="status-badge status-{{ $displayStatus }}" style="background-color: #ec5454 !important; color: #ffffff !important; border-color: #b13030 !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; width: 120px !important; text-align: center !important;" title="{{ $fullStatusText }}">In Progress</span>
                  @elseif($displayStatus === 'assigned')
                   <span class="status-badge status-{{ $displayStatus }}" style="background-color: #16a34a !important; color: #ffffff !important; border-color: #15803d !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; width: 120px !important; text-align: center !important;" title="{{ $fullStatusText }}">{{ $fullStatusText }}</span>
                @elseif($displayStatus === 'work_performa')
                  <span class="status-badge status-{{ $displayStatus }}" style="background-color: #60a5fa !important; color: #ffffff !important; border-color: #3b82f6 !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; width: 120px !important; text-align: center !important;" title="{{ $fullStatusText }}">{{ $fullStatusText }}</span>
                @elseif($displayStatus === 'maint_performa')
                  <span class="status-badge status-{{ $displayStatus }}" style="background-color: #eab308 !important; color: #ffffff !important; border-color: #ca8a04 !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; width: 120px !important; text-align: center !important;" title="{{ $fullStatusText }}">{{ $fullStatusText }}</span>
                @elseif($displayStatus === 'work_priced_performa')
                  <span class="status-badge status-{{ $displayStatus }}" style="background-color: #9333ea !important; color: #ffffff !important; border-color: #7e22ce !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; width: 120px !important; text-align: center !important;" title="{{ $fullStatusText }}">{{ $fullStatusText }}</span>
                @elseif($displayStatus === 'maint_priced_performa')
                  <span class="status-badge status-{{ $displayStatus }}" style="background-color: #ea580c !important; color: #ffffff !important; border-color: #c2410c !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; width: 120px !important; text-align: center !important;" title="{{ $hoverText }}">{{ $shortStatusText }}</span>
                @elseif($displayStatus === 'un_authorized')
                  <span class="status-badge status-{{ $displayStatus }}" style="background-color: #ec4899 !important; color: #ffffff !important; border-color: #db2777 !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; width: 120px !important; text-align: center !important;" title="{{ $fullStatusText }}">{{ $fullStatusText }}</span>
                @elseif($displayStatus === 'pertains_to_ge_const_isld')
                  <span class="status-badge status-{{ $displayStatus }}" style="background-color: #06b6d4 !important; color: #ffffff !important; border-color: #0891b2 !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; width: 120px !important; text-align: center !important;" title="{{ $hoverText }}">{{ $shortStatusText }}</span>
                @elseif($displayStatus === 'product_na')
                  <span class="status-badge status-{{ $displayStatus }}" style="background-color: #0deb7c !important; color: #ffffff !important; border-color: #06b366 !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; width: 120px !important; text-align: center !important;" title="{{ $fullStatusText }}">{{ $fullStatusText }}</span>
                @elseif($displayStatus === 'closed')
                  <span class="status-badge status-{{ $displayStatus }}" style="background-color: #6b7280 !important; color: #ffffff !important; border-color: #4b5563 !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; width: 120px !important; text-align: center !important;" title="{{ $fullStatusText }}">{{ $fullStatusText }}</span>
                @else
                  <span class="status-badge status-{{ $displayStatus }}" style="color: #ffffff !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; width: 120px !important; text-align: center !important;" title="{{ $fullStatusText }}">{{ $fullStatusText }}</span>
                @endif
              </td>
              <td>
                @if($complaint->priority === 'urgent')
                  <span class="priority-badge priority-{{ $complaint->priority }}" style="background-color: #991b1b !important; color: #ffffff !important; border-color: #7f1d1d !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; min-width: 70px !important; text-align: center !important;">{{ $complaint->getPriorityDisplayAttribute() }}</span>
                @elseif($complaint->priority === 'high')
                  <span class="priority-badge priority-{{ $complaint->priority }}" style="background-color: #c2410c !important; color: #ffffff !important; border-color: #9a3412 !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; min-width: 70px !important; text-align: center !important;">{{ $complaint->getPriorityDisplayAttribute() }}</span>
                @elseif($complaint->priority === 'medium')
                  <span class="priority-badge priority-{{ $complaint->priority }}" style="background-color: #eab308 !important; color: #ffffff !important; border-color: #ca8a04 !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; min-width: 70px !important; text-align: center !important;">{{ $complaint->getPriorityDisplayAttribute() }}</span>
                @elseif($complaint->priority === 'low')
                  <span class="priority-badge priority-{{ $complaint->priority }}" style="background-color: #15803d !important; color: #ffffff !important; border-color: #166534 !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important; min-width: 70px !important; text-align: center !important;">{{ $complaint->getPriorityDisplayAttribute() }}</span>
                @else
                  <span class="priority-badge priority-{{ $complaint->priority }}" style="display: inline-block !important; min-width: 70px !important; text-align: center !important;">{{ $complaint->getPriorityDisplayAttribute() }}</span>
                @endif
              </td>
              <td>
                <span style="font-size: 0.875rem;">
                  {{ $complaint->created_at ? $complaint->created_at->format('M d, Y H:i') : 'N/A' }}
                </span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4" style="color: #64748b; font-style: italic;">No recent complaints</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

    <!-- APPROVALS SECTION -->
    @if(isset($pendingApprovals) && $pendingApprovals->count() > 0)
    <div class="row mt-4">
      <div class="col-12">
        <div class="card-glass">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 text-warning">
              <i data-feather="clock" class="me-2"></i>
              @if($approvalStatus)
                {{ ucfirst($approvalStatus) }} Approvals
              @else
                In Progress Complaints
              @endif
            </h5>
            <a href="{{ route('admin.approvals.index') }}" class="btn btn-outline-warning btn-sm">View All</a>
          </div>
          <div class="table-responsive">
            <table class="table table-dark" style="font-size: 0.8rem;">
              <thead>
                <tr>
                  <th style="width: 8%; padding: 0.4rem 0.5rem !important;">Complaint ID</th>
                  <th style="width: 15%; padding: 0.4rem 0.5rem !important;">Complainant</th>
                  <th style="width: 12%; padding: 0.4rem 0.5rem !important;">Assigned To</th>
                  <th style="width: 11%; padding: 0.4rem 0.5rem !important;">Category</th>
                  <th style="width: 10%; padding: 0.4rem 0.5rem !important;">Status</th>
                  <th style="width: 11%; padding: 0.4rem 0.5rem !important;">Priority</th>
                  <th style="width: 15%; padding: 0.4rem 0.5rem !important;">Registration Date/Time</th>
                  <th style="width: 10%; padding: 0.4rem 0.5rem !important;">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($pendingApprovals as $approval)
                <tr>
                  <td style="padding: 0.4rem 0.5rem !important;">{{ $approval->complaint ? (int)$approval->complaint->id : 'N/A' }}</td>
                  <td style="padding: 0.4rem 0.5rem !important;">{{ $approval->complaint && $approval->complaint->client ? $approval->complaint->client->client_name : 'N/A' }}</td>
                  <td style="padding: 0.4rem 0.5rem !important;">{{ $approval->requestedBy->name ?? 'N/A' }}</td>
                  <td style="padding: 0.4rem 0.5rem !important;">
                    @if($approval->complaint)
                      @php
                        $category = $approval->complaint->category ?? 'N/A';
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
                      @endphp
                      <span style="font-size: 0.75rem;">{{ $catDisplay }}</span>
                    @else
                      <span style="font-size: 0.75rem;">N/A</span>
                    @endif
                  </td>
                  <td style="padding: 0.4rem 0.5rem !important;">
                    @php
                      $statusColors = [
                        'pending' => ['bg' => '#ec5454', 'text' => '#ffffff', 'border' => '#b13030'],
                        'approved' => ['bg' => '#22c55e', 'text' => '#ffffff', 'border' => '#16a34a'],
                        'rejected' => ['bg' => '#ef4444', 'text' => '#ffffff', 'border' => '#dc2626'],
                      ];
                      $statusColor = $statusColors[$approval->status] ?? ['bg' => '#6b7280', 'text' => '#ffffff', 'border' => '#4b5563'];
                    @endphp
                    <span class="badge status-badge" style="background-color: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }} !important; border: 1px solid {{ $statusColor['border'] }}; padding: 3px 6px !important; font-size: 10px !important; font-weight: 600; line-height: 1.1; border-radius: 6px !important;">
                      {{ $approval->getStatusDisplayAttribute() }}
                    </span>
                  </td>
                  <td style="padding: 0.4rem 0.5rem !important;">
                    @if($approval->complaint)
                      @php
                        $priority = $approval->complaint->priority ?? 'medium';
                        $priorityColors = [
                          'urgent' => ['bg' => '#991b1b', 'text' => '#ffffff', 'border' => '#7f1d1d'],
                          'high' => ['bg' => '#c2410c', 'text' => '#ffffff', 'border' => '#9a3412'],
                          'medium' => ['bg' => '#eab308', 'text' => '#ffffff', 'border' => '#ca8a04'],
                          'low' => ['bg' => '#15803d', 'text' => '#ffffff', 'border' => '#166534'],
                        ];
                        $priorityColor = $priorityColors[$priority] ?? ['bg' => '#6b7280', 'text' => '#ffffff', 'border' => '#4b5563'];
                      @endphp
                      <span class="priority-badge" style="background-color: {{ $priorityColor['bg'] }} !important; color: {{ $priorityColor['text'] }} !important; border: 1px solid {{ $priorityColor['border'] }} !important; padding: 3px 6px !important; font-size: 10px !important; font-weight: 600; border-radius: 6px !important; display: inline-block !important; min-width: 70px !important; text-align: center !important;">
                        {{ $approval->complaint->getPriorityDisplayAttribute() ?? 'N/A' }}
                      </span>
                    @else
                      <span style="font-size: 0.75rem;">N/A</span>
                    @endif
                  </td>
                  <td style="padding: 0.4rem 0.5rem !important; font-size: 0.75rem;">{{ $approval->created_at->format('M d, Y H:i') }}</td>
                  <td style="padding: 0.4rem 0.5rem !important;">
                    <a href="{{ route('admin.approvals.show', $approval->id) }}" class="btn btn-xs btn-outline-primary" style="padding: 0.15rem 0.4rem; font-size: 0.7rem; line-height: 1.2;">
                      <i data-feather="eye" class="me-1" style="width: 8px; height: 8px;"></i>View
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    @endif

    <!-- LOW STOCK ALERTS -->
@if(isset($lowStockItems) && $lowStockItems->count() > 0)
    <div class="row mt-4">
      <div class="col-12">
        <div class="card-glass">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 text-warning">
              <i data-feather="alert-triangle" class="me-2"></i>Low Stock Alerts
            </h5>
            <a href="{{ route('admin.spares.index') }}" class="btn btn-outline-warning btn-sm">Manage Stock</a>
        </div>
        <div class="table-responsive">
            <table class="table table-dark ">
            <thead>
                <tr>
                  <th>Item</th>
                  <th>Category</th>
                  <th>Current Stock</th>
                  <th>Threshold</th>
                  <th>Status</th>
                </tr>
            </thead>
              <tbody>
                @foreach($lowStockItems as $item)
                <tr>
                  <td>{{ $item->item_name }}</td>
                  <td>{{ ucfirst($item->category) }}</td>
                  <td>{{ $item->stock_quantity }}</td>
                  <td>{{ $item->threshold_level }}</td>
                  <td>
                    @if($item->stock_quantity <= 0)
                      <span class="badge bg-danger" style="color: #ffffff !important;">Out of Stock</span>
                    @else
                      <span class="badge bg-warning" style="color: #ffffff !important;">Low Stock</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
          </table>
          </div>
        </div>
      </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
  feather.replace();

  // Define chart data
  @php
    $defaultComplaints = [0,0,0,0,0,0,0,0,0,0,0,0];
    $defaultResolutions = [0,0,0,0,0,0,0,0,0,0,0,0];
    $defaultMonths = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  @endphp
  var complaintsData = @json($monthlyTrends['complaints'] ?? $defaultComplaints);
  var resolutionsData = @json($monthlyTrends['resolutions'] ?? $defaultResolutions);
  var monthsData = @json($monthlyTrends['months'] ?? $defaultMonths);

    // Complaints by Status Chart
  @php
    // Status colors mapping (same as in approvals view)
    $statusColorMap = [
      'assigned' => '#16a34a', // Green (swapped from grey)
      'in_progress' => '#ec5454', // Brown-Red mix
      'resolved' => '#64748b', // Grey (swapped from green)
      'work_performa' => '#60a5fa', // Light Blue
      'maint_performa' => '#eab308', // Yellow
      'work_priced_performa' => '#9333ea', // Purple
      'maint_priced_performa' => '#ea580c', // Orange Red
      'product_na' => '#0deb7c', // Green (from Product N/A stat card)
      'un_authorized' => '#ec4899', // Pink (same as approvals view)
      'pertains_to_ge_const_isld' => '#06b6d4', // Aqua/Cyan (same as approvals view)
    ];

    // All possible statuses from approvals page (in order)
    $allPossibleStatuses = [
      'assigned',
      'in_progress',
      'resolved',
      'work_performa',
      'maint_performa',
      'work_priced_performa',
      'maint_priced_performa',
      'product_na',
      'un_authorized',
      'pertains_to_ge_const_isld'
    ];

    // Ensure we preserve the order of statuses and include all possible statuses
    $statusKeys = isset($complaintsByStatus) ? array_keys($complaintsByStatus) : [];
    $statusData = isset($complaintsByStatus) ? array_values($complaintsByStatus) : [0, 0, 0, 0, 0];

    // Merge with all possible statuses to ensure all are included (even with 0 count)
    $mergedStatusData = [];
    $mergedStatusKeys = [];
    foreach ($allPossibleStatuses as $status) {
      $mergedStatusKeys[] = $status;
      $mergedStatusData[] = isset($complaintsByStatus[$status]) ? $complaintsByStatus[$status] : 0;
    }

    // Use merged data if we have complaintsByStatus, otherwise use original
    if (isset($complaintsByStatus) && !empty($complaintsByStatus)) {
      $statusKeys = $mergedStatusKeys;
      $statusData = $mergedStatusData;
    }

    $statusLabels = isset($complaintsByStatus) ? array_map(function($status) {
      $label = ucfirst(str_replace('_', ' ', $status));
      // Handle special cases
      if ($label === 'Resolved') {
        return 'Addressed';
      } elseif ($status === 'work_performa') {
        return 'Work Performa';
      } elseif ($status === 'maint_performa') {
        return 'Maintenance Performa';
      } elseif ($status === 'work_priced_performa') {
        return 'Work Performa Priced';
      } elseif ($status === 'maint_priced_performa') {
        return 'Maintenance Performa Priced';
      } elseif ($status === 'product_na') {
        return 'Product N/A';
      } elseif ($status === 'un_authorized') {
        return 'Un-Authorized';
      } elseif ($status === 'pertains_to_ge_const_isld') {
        return 'Pertains to GE(N) Const Isld';
      } elseif ($status === 'in_progress') {
        return 'In Progress';
      }
      return $label;
    }, $statusKeys) : ['New', 'Assigned', 'In Progress', 'Addressed'];

    // Map colors based on status keys - ensure same order as data
    $statusColors = isset($complaintsByStatus) ? array_map(function($status) use ($statusColorMap) {
      return $statusColorMap[$status] ?? '#64748b'; // Default gray if status not found
    }, $statusKeys) : ['#3b82f6', '#f59e0b', '#a855f7', '#22c55e', '#6b7280'];
  @endphp
    var statusDataArray = @json($statusData);
    var statusLabelsArray = @json($statusLabels);
    var statusColorsArray = @json($statusColors);

    var complaintsStatusOptions = {
    series: statusDataArray,
      chart: {
        type: 'donut',
        height: 300,
        background: 'transparent'
      },
    labels: statusLabelsArray,
      colors: statusColorsArray,
      plotOptions: {
        pie: {
          donut: {
            labels: {
              show: true
            }
          }
        }
      },
      legend: {
        position: 'bottom',
        labels: {
          colors: '#e2e8f0'
        }
      },
      dataLabels: {
        enabled: true,
        style: {
          colors: ['#fff']
        }
      },
      tooltip: {
        theme: document.body.classList.contains('theme-light') ? 'light' : 'dark',
        style: {
          fontSize: '12px',
          fontFamily: 'inherit'
        }
      }
    };

    var complaintsStatusChart = new ApexCharts(document.querySelector("#complaintsStatusChart"), complaintsStatusOptions);
    complaintsStatusChart.render();

    // Complaints by Category Chart
  @php
    $typeData = isset($complaintsByType) ? array_values($complaintsByType) : [];
    $typeLabels = isset($complaintsByType) ? array_map(function($type) { return $type; }, array_keys($complaintsByType)) : [];

    // Color mapping based on category name to ensure unique colors
    $colorMap = [
      'B&R-I' => '#3b82f6',                    // Blue
      'B&R-II' => '#f97316',                 // Orange
      'E&M NRC (Elect)' => '#8b5cf6',        // Purple
      'E&M NRC (Gas)' => '#10b981',           // Green
      'E&M NRC (Water Supply)' => '#06b6d4', // Light Blue (Cyan)
      'F&S' => '#ec4899',                     // Pink
    ];
    $fallbackColors = ['#f59e0b', '#ef4444', '#84cc16', '#14b8a6', '#a855f7', '#22c55e'];
    $categoryColors = [];
    $colorIndex = 0;

    foreach($typeLabels as $label) {
      if(isset($colorMap[$label])) {
        $categoryColors[] = $colorMap[$label];
      } else {
        $categoryColors[] = $fallbackColors[$colorIndex % count($fallbackColors)];
        $colorIndex++;
      }
    }

    // If no colors, use default
    if(empty($categoryColors)) {
      $categoryColors = ['#3b82f6', '#f59e0b', '#a855f7', '#22c55e'];
    }
  @endphp
    var complaintsTypeOptions = {
    series: @json($typeData),
      chart: {
        type: 'pie',
        height: 300,
        background: 'transparent'
      },
    labels: @json($typeLabels),
      colors: @json($categoryColors),
      legend: {
        position: 'bottom',
        labels: {
          colors: '#e2e8f0'
        }
      },
      dataLabels: {
        enabled: true,
        style: {
          colors: ['#fff'],
          fontSize: '14px',
          fontWeight: 'bold'
        }
      },
      tooltip: {
        theme: document.body.classList.contains('theme-light') ? 'light' : 'dark',
        style: {
          fontSize: '12px',
          fontFamily: 'inherit'
        }
      }
    };

    var complaintsTypeChart = new ApexCharts(document.querySelector("#complaintsTypeChart"), complaintsTypeOptions);
    complaintsTypeChart.render();

    // Monthly Trends Chart - Modern Combination Chart (Column + Line)
    const isLightTheme = document.documentElement.classList.contains('theme-light');
    var monthlyTrendsOptions = {
      series: [{
        name: 'Complaints',
        type: 'column',
        data: complaintsData
      }, {
        name: 'Resolutions',
        type: 'line',
        data: resolutionsData
      }],
      chart: {
        height: 420,
        type: 'line',
        background: 'transparent',
        toolbar: {
          show: true,
          tools: {
            download: true,
            selection: true,
            zoom: true,
            zoomin: true,
            zoomout: true,
            pan: true,
            reset: true
          }
        },
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 1000,
          animateGradually: {
            enabled: true,
            delay: 200
          },
          dynamicAnimation: {
            enabled: true,
            speed: 400
          }
        },
        dropShadow: {
          enabled: true,
          color: '#000',
          top: 18,
          left: 7,
          blur: 10,
          opacity: 0.2
        }
      },
      colors: ['#3b82f6', '#22c55e'],
      plotOptions: {
        bar: {
          borderRadius: 8,
          columnWidth: '60%',
          dataLabels: {
            position: 'top'
          }
        }
      },
      dataLabels: {
        enabled: true,
        enabledOnSeries: [0],
        formatter: function (val) {
          return Math.floor(val);
        },
        offsetY: -20,
        style: {
          fontSize: '13px',
          fontWeight: 700,
          colors: isLightTheme ? ['#1e293b'] : ['#ffffff']
        },
        background: {
          enabled: true,
          foreColor: isLightTheme ? '#ffffff' : '#1e293b',
          padding: 6,
          borderRadius: 6,
          borderWidth: 2,
          borderColor: isLightTheme ? '#3b82f6' : '#60a5fa',
          opacity: 0.95,
          dropShadow: {
            enabled: true,
            top: 1,
            left: 1,
            blur: 3,
            opacity: 0.5
          }
        }
      },
      stroke: {
        width: [0, 4],
        curve: 'smooth',
        dashArray: [0, 0]
      },
      fill: {
        type: 'gradient',
        gradient: {
          shade: 'light',
          type: 'vertical',
          shadeIntensity: 0.5,
          gradientToColors: ['#60a5fa', '#34d399'],
          inverseColors: false,
          opacityFrom: 0.9,
          opacityTo: 0.6,
          stops: [0, 50, 100]
        }
      },
      markers: {
        size: [0, 7],
        strokeWidth: 3,
        strokeColors: ['#ffffff', '#ffffff'],
        fillColors: ['#22c55e', '#22c55e'],
        hover: {
          size: [0, 9]
        },
        shape: 'circle'
      },
      xaxis: {
        categories: monthsData,
        labels: {
          style: {
            colors: isLightTheme ? '#1e293b' : '#e2e8f0',
            fontSize: '13px',
            fontWeight: 600,
            fontFamily: 'inherit'
          }
        },
        axisBorder: {
          show: true,
          color: isLightTheme ? '#d1d5db' : '#374151',
          height: 2,
          width: '100%'
        },
        axisTicks: {
          show: true,
          color: isLightTheme ? '#d1d5db' : '#374151',
          height: 6
        }
      },
      yaxis: [{
        title: {
          text: 'Complaints',
          style: {
            color: '#3b82f6',
            fontSize: '16px',
            fontWeight: 700
          }
        },
        labels: {
          style: {
            colors: isLightTheme ? '#1e293b' : '#e2e8f0',
            fontSize: '12px',
            fontWeight: 600
          },
          formatter: function (val) {
            return Math.floor(val);
          }
        }
      }, {
        opposite: true,
        title: {
          text: 'Resolutions',
          style: {
            color: '#22c55e',
            fontSize: '16px',
            fontWeight: 700
          }
        },
        labels: {
          style: {
            colors: isLightTheme ? '#1e293b' : '#e2e8f0',
            fontSize: '12px',
            fontWeight: 600
          },
          formatter: function (val) {
            return Math.floor(val);
          }
        }
      }],
      legend: {
        position: 'top',
        horizontalAlign: 'center',
        floating: false,
        fontSize: '14px',
        fontWeight: 700,
        labels: {
          colors: isLightTheme ? '#1e293b' : '#e2e8f0',
          useSeriesColors: false
        },
        markers: {
          width: 14,
          height: 14,
          radius: 7,
          offsetX: -5,
          offsetY: 2
        },
        itemMargin: {
          horizontal: 20,
          vertical: 8
        }
      },
      grid: {
        borderColor: isLightTheme ? '#e2e8f0' : '#374151',
        strokeDashArray: 5,
        xaxis: {
          lines: {
            show: false
          }
        },
        yaxis: {
          lines: {
            show: true
          }
        },
        padding: {
          top: 20,
          right: 10,
          bottom: 0,
          left: 10
        }
      },
      tooltip: {
        theme: isLightTheme ? 'light' : 'dark',
        style: {
          fontSize: '13px',
          fontFamily: 'inherit'
        },
        y: [{
          formatter: function (val) {
            return val + ' complaints';
          }
        }, {
          formatter: function (val) {
            return val + ' resolutions';
          }
        }],
        marker: {
          show: true
        },
        shared: true,
        intersect: false
      },
      responsive: [{
        breakpoint: 768,
        options: {
          chart: {
            height: 350
          },
          legend: {
            position: 'bottom'
          },
          dataLabels: {
            enabled: false
          },
          plotOptions: {
            bar: {
              columnWidth: '70%'
            }
          }
        }
      }]
    };

    var monthlyTrendsChart = new ApexCharts(document.querySelector("#monthlyTrendsChart"), monthlyTrendsOptions);
    monthlyTrendsChart.render();

    // Refresh dashboard function
    function refreshDashboard() {
      location.reload();
    }

    // Auto-refresh every 5 minutes
    setInterval(function() {
      fetch('{{ route("admin.dashboard.real-time-updates") }}')
        .then(response => response.json())
        .then(data => {
          // Update real-time data here if needed
          console.log('Dashboard updated:', data);
        })
        .catch(error => console.error('Error updating dashboard:', error));
    }, 300000); // 5 minutes

    // Dashboard Filters Functions
    function applyDashboardFilters() {
      const form = document.getElementById('dashboardFiltersForm');
      const formData = new FormData(form);
      const params = new URLSearchParams();

      // Add filter values to params
      if (formData.get('cmes_id')) {
        params.append('cmes_id', formData.get('cmes_id'));
      }
      if (formData.get('city_id')) {
        params.append('city_id', formData.get('city_id'));
      }
      if (formData.get('sector_id')) {
        params.append('sector_id', formData.get('sector_id'));
      }
      if (formData.get('category')) {
        params.append('category', formData.get('category'));
      }
      if (formData.get('complaint_status')) {
        params.append('complaint_status', formData.get('complaint_status'));
      }
      if (formData.get('date_range')) {
        params.append('date_range', formData.get('date_range'));
      }

      // Reload dashboard with filters
      window.location.href = '{{ route("admin.dashboard") }}?' + params.toString();
    }

    function resetDashboardFilters() {
      window.location.href = '{{ route("admin.dashboard") }}';
    }

    // Dynamic sector loading for Director when city changes and auto-apply filters
    const cityFilter = document.getElementById('cityFilter');
    const sectorFilter = document.getElementById('sectorFilter');
    const categoryFilter = document.getElementById('categoryFilter');

    // Keep GE filter visible at all times - don't hide it
    // @if($user && !$user->city_id)
    // const cityFilterContainer = document.getElementById('cityFilterContainer');
    // if (cityFilterContainer && cityFilter) {
    //   const selectedCityId = cityFilter.value;
    //   if (selectedCityId && selectedCityId !== '') {
    //     cityFilterContainer.style.display = 'none';
    //   }
    // }
    // @endif

    // Auto-apply filters on change (like other modules)
    if (cityFilter) {
      cityFilter.addEventListener('change', function() {
        @if($user && !$user->city_id)
        // User can see all cities: Load sectors dynamically when city changes
        const cityId = this.value;

        // Keep GE filter visible at all times - don't hide it
        // const cityFilterContainer = document.getElementById('cityFilterContainer');
        // if (cityFilterContainer) {
        //   if (cityId && cityId !== '') {
        //     cityFilterContainer.style.display = 'none';
        //   } else {
        //     cityFilterContainer.style.display = 'block';
        //   }
        // }

        if (sectorFilter) {
          sectorFilter.innerHTML = '<option value="">Loading GE Nodes...</option>';
          sectorFilter.disabled = true;

          if (cityId) {
            // Fetch sectors for selected city
            fetch(`{{ route('admin.sectors.by-city') }}?city_id=${cityId}`, {
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
              },
              credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
              sectorFilter.innerHTML = '<option value="">All GE Nodes</option>';
              const sectors = Array.isArray(data) ? data : (data.sectors || []);
              if (sectors && sectors.length > 0) {
                sectors.forEach(function(sector) {
                  const option = document.createElement('option');
                  option.value = sector.id;
                  option.textContent = sector.name;
                  sectorFilter.appendChild(option);
                });
              }
              sectorFilter.disabled = false;
              // Auto-apply filters after loading GE Nodes
              applyDashboardFilters();
            })
            .catch(error => {
              console.error('Error loading GE Nodes:', error);
              sectorFilter.innerHTML = '<option value="">All GE Nodes</option>';
              sectorFilter.disabled = false;
              // Auto-apply filters even on error
              applyDashboardFilters();
            });
          } else {
            // Show all GE Nodes if no city selected (Director) - reload page to get all GE Nodes
            sectorFilter.innerHTML = '<option value="">All GE Nodes</option>';
            sectorFilter.disabled = false;
            // Auto-apply filters
            applyDashboardFilters();
          }
        } else {
          // Auto-apply filters when city changes
          applyDashboardFilters();
        }
        @else
        // For GE: Auto-apply filters when city changes
        applyDashboardFilters();
        @endif
      });
    }

    // Auto-apply filters when sector changes
    if (sectorFilter) {
      sectorFilter.addEventListener('change', function() {
        applyDashboardFilters();
      });
    }

    // Auto-apply filters when category changes
    if (categoryFilter) {
      categoryFilter.addEventListener('change', function() {
        applyDashboardFilters();
      });
    }

    // Auto-apply filters when complaint status changes
    const complaintStatusFilter = document.getElementById('complaintStatusFilter');
    if (complaintStatusFilter) {
      complaintStatusFilter.addEventListener('change', function() {
        applyDashboardFilters();
      });
    }

    // Auto-apply filters when date range changes
    const dateRangeFilter = document.getElementById('dateRangeFilter');
    if (dateRangeFilter) {
      dateRangeFilter.addEventListener('change', function() {
        applyDashboardFilters();
      });
    }

    // CMES filter change
    const cmesFilter = document.getElementById('cmesFilter');
    if (cmesFilter) {
      cmesFilter.addEventListener('change', function() {
        // Clear city/sector selection if desired, or let server handle dependent lists
        applyDashboardFilters();
      });
    }
    
    // Override inline styles for filter labels in dark/night theme using style injection
    function updateFilterLabelsColor() {
      const body = document.body;
      const isDarkTheme = body.classList.contains('theme-dark');
      const isNightTheme = body.classList.contains('theme-night');

      if (isDarkTheme || isNightTheme) {
        // Inject a style tag with maximum specificity to override inline styles
        let styleId = 'filter-labels-dark-theme-style';
        let existingStyle = document.getElementById(styleId);
        if (!existingStyle) {
          existingStyle = document.createElement('style');
          existingStyle.id = styleId;
          existingStyle.innerHTML = `
            body.theme-dark .filter-box label,
            body.theme-night .filter-box label,
            body.theme-dark .filter-box .form-label,
            body.theme-night .filter-box .form-label,
            body.theme-dark .filter-box .col-auto label,
            body.theme-night .filter-box .col-auto label {
              color: #e2e8f0 !important;
            }
          `;
          document.head.appendChild(existingStyle);
        }

        // Also directly manipulate the style attribute
        const filterLabels = document.querySelectorAll('.filter-box label, .filter-box .form-label, .filter-box .col-auto label');
        filterLabels.forEach(function(label) {
          // Completely replace the style attribute
          let currentStyle = label.getAttribute('style') || '';
          // Split by semicolon and filter out color
          let styles = currentStyle.split(';').filter(function(style) {
            return !style.trim().toLowerCase().startsWith('color');
          });
          // Join back and add white color
          let newStyle = styles.join(';').trim();
          if (newStyle && !newStyle.endsWith(';')) {
            newStyle += ';';
          }
          newStyle += ' color: #e2e8f0 !important;';
          // Set the new style
          label.setAttribute('style', newStyle);
          // Also use cssText as backup
          label.style.cssText = newStyle;
        });
      } else {
        // Remove the injected style in light theme
        let styleId = 'filter-labels-dark-theme-style';
        let existingStyle = document.getElementById(styleId);
        if (existingStyle) {
          existingStyle.remove();
        }
      }
    }

    // Run immediately and multiple times to ensure it works
    updateFilterLabelsColor();

    // Run on DOM ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function() {
        updateFilterLabelsColor();
        setTimeout(updateFilterLabelsColor, 100);
        setTimeout(updateFilterLabelsColor, 500);
        setTimeout(updateFilterLabelsColor, 1000);
      });
    } else {
      setTimeout(updateFilterLabelsColor, 100);
      setTimeout(updateFilterLabelsColor, 500);
      setTimeout(updateFilterLabelsColor, 1000);
    }

    // Watch for theme changes
    const observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
          updateFilterLabelsColor();
        }
      });
    });

    if (document.body) {
      observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
      });
    }

    // Function to show/hide all GE Progress boxes
    function showAllGEProgress() {
      const allGEProgress = document.getElementById('allGEProgress');
      const seeMoreBtn = document.getElementById('seeMoreBtn');

      if (allGEProgress && seeMoreBtn) {
        // Show all boxes - remove inline style to let Bootstrap row class handle display
        allGEProgress.removeAttribute('style');
        seeMoreBtn.innerHTML = '<i data-feather="chevron-up" class="me-1" style="width: 16px; height: 16px;"></i>See Less';
        seeMoreBtn.setAttribute('onclick', 'hideAllGEProgress()');
        // Reinitialize feather icons
        feather.replace();
      }
    }

    // Function to hide all GE Progress boxes
    function hideAllGEProgress() {
      const allGEProgress = document.getElementById('allGEProgress');
      const seeMoreBtn = document.getElementById('seeMoreBtn');

      if (allGEProgress && seeMoreBtn) {
        allGEProgress.style.display = 'none';
        seeMoreBtn.innerHTML = '<i data-feather="chevron-down" class="me-1" style="width: 16px; height: 16px;"></i>See More';
        seeMoreBtn.setAttribute('onclick', 'showAllGEProgress()');
        // Reinitialize feather icons
        feather.replace();
      }
    }
</script>
@endpush
