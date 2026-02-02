@extends('layouts.sidebar')

@section('title', 'Registered Devices — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Registered Devices</h2>
      <p class="text-light">Manage registered devices and assignments</p>
    </div>
    <a href="{{ route('admin.registered-devices.create') }}" class="btn btn-accent">
      <i data-feather="plus" class="me-2"></i>Add New Device
    </a>
  </div>
</div>

<!-- FILTERS -->
<div class="card-glass mb-4" style="display: inline-block; width: fit-content;">
  <form id="devicesFiltersForm" method="GET" action="{{ route('admin.registered-devices.index') }}">
  <div class="row g-2 align-items-end">
    <div class="col-auto">
      <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Search</label>
      <input type="text" class="form-control" id="searchInput" name="search" placeholder="Search..." 
             value="{{ request('search') }}" oninput="handleDevicesSearchInput()" style="font-size: 0.9rem; width: 200px;">
    </div>
    <div class="col-auto">
      <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Status</label>
      <select class="form-select" name="status" onchange="submitDevicesFilters()" style="font-size: 0.9rem; width: 120px;">
        <option value="" {{ request('status') === null ? 'selected' : '' }}>All</option>
        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label small text-muted mb-1" style="font-size: 0.8rem;">&nbsp;</label>
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetDevicesFilters()" style="font-size: 0.9rem; padding: 0.35rem 0.8rem;">
        <i data-feather="refresh-cw" class="me-1" style="width: 14px; height: 14px;"></i>Reset
      </button>
    </div>
  </div>
  </form>
</div>

<!-- SUCCESS MESSAGE -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i data-feather="check-circle" class="me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- DEVICES TABLE -->
<div class="card-glass">
  <div class="table-responsive">
    <table class="table table-dark table-sm" id="devicesTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Device ID</th>
          <th>Device Name</th>
          <th>House No.</th>
          <th>GE Group</th>
          <th>GE Node</th>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="devicesTableBody">
        @forelse($devices as $device)
        <tr>
          <td>{{ $device->id }}</td>
          <td class="px-4 py-2 whitespace-nowrap">{{ $device->device_id }}</td>
          <td>{{ $device->device_name ?? 'N/A' }}</td>
          <td>{{ $device->assigned_to_house_no ?? 'N/A' }}</td>
          <td>{{ $device->city ? $device->city->name : 'N/A' }}</td>
          <td>{{ $device->sector ? $device->sector->name : 'N/A' }}</td>
          <td>
            <span class="badge {{ $device->is_active ? 'bg-success' : 'bg-danger' }}" style="color: #ffffff !important;">
              {{ $device->is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td>{{ $device->created_at ? $device->created_at->format('M d, Y') : 'N/A' }}</td>
          <td>
            <div class="btn-group" role="group">
              <a href="{{ route('admin.registered-devices.edit', $device->id) }}" class="btn btn-outline-primary btn-sm" title="Edit" style="padding: 3px 8px;">
                <i data-feather="edit" style="width: 16px; height: 16px;"></i>
              </a>
              <button class="btn btn-outline-danger btn-sm" onclick="deleteDevice({{ $device->id }})" title="Delete" data-device-id="{{ $device->id }}" style="padding: 3px 8px;">
                <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9" class="text-center text-muted py-4">
            <i data-feather="smartphone" class="feather-lg mb-2"></i>
            <div>No devices found</div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  
  <!-- TOTAL RECORDS -->
  <div id="devicesTableFooter" class="text-center py-2 mt-2" style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
    <strong style="color: #ffffff; font-size: 14px;">
      Total Records: {{ $devices->total() }}
    </strong>
  </div>
  
  <!-- PAGINATION -->
  <div class="d-flex justify-content-center mt-4" id="devicesPagination">
    <div>
      {{ $devices->links() }}
    </div>
  </div>
</div>

<!-- Delete Device Modal -->
<div class="modal fade" id="deleteDeviceModal" tabindex="-1" aria-labelledby="deleteDeviceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(239, 68, 68, 0.3);">
      <div class="modal-header" style="border-bottom: 1px solid rgba(239, 68, 68, 0.2);">
        <h5 class="modal-title text-white" id="deleteDeviceModalLabel">
          <i data-feather="alert-triangle" class="me-2 text-danger"></i>Delete Device
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-color: #888; opacity: 1 !important;"></button>
      </div>
      <div class="modal-body">
        <p class="text-white mb-3">
          Are you sure you want to delete this device? This action cannot be undone.
        </p>
        <div class="alert alert-warning" role="alert">
          <i data-feather="info" class="me-2"></i>
          <strong>Note:</strong> This will permanently delete the device record.
        </div>
        <div id="deviceDetails" class="text-white">
          <p class="mb-1"><strong>Device ID:</strong> <span id="deviceIdModal"></span></p>
          <p class="mb-0"><strong>Device Name:</strong> <span id="deviceNameModal"></span></p>
        </div>
      </div>
      <div class="modal-footer" style="border-top: 1px solid rgba(239, 68, 68, 0.2);">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i data-feather="x" class="me-1"></i>Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
          <i data-feather="trash-2" class="me-1"></i>Delete Device
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  feather.replace();
  
  let devicesSearchTimeout = null;
  function handleDevicesSearchInput() {
    if (devicesSearchTimeout) clearTimeout(devicesSearchTimeout);
    devicesSearchTimeout = setTimeout(() => {
      document.getElementById('devicesFiltersForm').submit();
    }, 500);
  }

  function submitDevicesFilters() {
    document.getElementById('devicesFiltersForm').submit();
  }

  function resetDevicesFilters() {
    window.location.href = '{{ route('admin.registered-devices.index') }}';
  }

  let currentDeleteDeviceId = null;
  
  function deleteDevice(deviceId) {
    const row = document.querySelector(`button[data-device-id="${deviceId}"]`)?.closest('tr');
    if (!row) return;
    
    const deviceIdCell = row.cells[1].textContent.trim();
    const deviceNameCell = row.cells[2].textContent.trim() || 'N/A';
    
    document.getElementById('deviceIdModal').textContent = deviceIdCell;
    document.getElementById('deviceNameModal').textContent = deviceNameCell;
    
    currentDeleteDeviceId = deviceId;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteDeviceModal'));
    modal.show();
    
    // Add blur effect to background
    document.body.classList.add('modal-open-blur');
    
    // Remove blur when modal is hidden
    document.getElementById('deleteDeviceModal').addEventListener('hidden.bs.modal', function() {
      document.body.classList.remove('modal-open-blur');
    }, { once: true });
  }
  
  document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (!currentDeleteDeviceId) return;
    
    const deviceId = currentDeleteDeviceId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i data-feather="loader" class="spinning"></i> Deleting...';
    
    fetch(`/admin/registered-devices/${deviceId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteDeviceModal'));
        modal.hide();
        location.reload();
      } else {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('Error: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(error => {
      btn.disabled = false;
      btn.innerHTML = originalText;
      alert('Error deleting device: ' + error.message);
    });
    
    currentDeleteDeviceId = null;
  });
</script>
@endpush
