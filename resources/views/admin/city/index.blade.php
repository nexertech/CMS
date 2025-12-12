@extends('layouts.sidebar')

@section('title', 'GE Groups — CMS Admin')

@section('content')
<div class="container-narrow">
<div class="mb-4 d-flex justify-content-between align-items-center">
  <div>
    <h2 class="text-white mb-1">GE Groups</h2>
    <p class="text-light mb-0">Manage GE Groups for employee selection</p>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif
@if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif
@if($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<div class="card-glass mb-3">
  <div class="card-header">
    <h5 class="card-title mb-0 text-white"><i data-feather="plus" class="me-2"></i>Add GE Groups</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.city.store') }}" class="d-flex flex-wrap align-items-end gap-2">
      @csrf
      <div style="min-width: 220px; flex: 0 0 240px;">
        <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">CMES <span class="text-danger">*</span></label>
        <select name="cme_id" class="form-select @error('cme_id') is-invalid @enderror" required>
          <option value="">Select CMES</option>
          @foreach(($cmes ?? collect()) as $cme)
            <option value="{{ $cme->id }}" {{ old('cme_id') == $cme->id ? 'selected' : '' }}>
              {{ $cme->name }}
            </option>
          @endforeach
        </select>
        @error('cme_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div style="min-width: 220px; flex: 0 0 260px;">
        <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">Name</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="GE Groups name" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div style="min-width: 160px; flex: 0 0 180px;">
        <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">Status</label>
        <select name="status" class="form-select">
          <option value="active" {{ old('status','active')==='active'?'selected':'' }}>Active</option>
          <option value="inactive" {{ old('status')==='inactive'?'selected':'' }}>Inactive</option>
        </select>
      </div>
      <div class="d-grid" style="flex: 0 0 140px;">
        <button class="btn btn-outline-secondary" type="submit" style="width: 100%;"><i data-feather="plus" class="me-2"></i>Add</button>
      </div>
    </form>
  </div>
@push('styles')
@endpush

</div>

<div class="card-glass">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0 text-white"><i data-feather="list" class="me-2"></i>GE Groups</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table  align-middle compact-table">
        <thead>
          <tr>
            <th style="width:70px">#</th>
            <th>Name</th>
            <th>CMES</th>
            <th style="width:140px">Status</th>
            <th style="width:180px">Actions</th>
          </tr>
        </thead>
        <tbody>
        @forelse($cities as $city)
          <tr>
            <td>{{ $city->id }}</td>
            <td>{{ $city->name }}</td>
            <td>{{ $city->cme ? $city->cme->name : 'N/A' }}</td>
            <td>
              <span class="badge {{ $city->status==='active' ? 'bg-success' : 'bg-danger' }}" style="color: #ffffff !important;">{{ ucfirst($city->status) }}</span>
            </td>
            <td>
              <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editCityModal" 
                        data-id="{{ $city->id }}" data-name="{{ $city->name }}" data-status="{{ $city->status }}" data-cme-id="{{ $city->cme_id }}" title="Edit" style="padding: 3px 8px;">
                  <i data-feather="edit" style="width: 16px; height: 16px;"></i>
                </button>
                <form action="{{ route('admin.city.destroy', $city) }}" method="POST" class="city-delete-form" onsubmit="return confirm('Delete this city?')" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-outline-danger btn-sm" type="submit" title="Delete" style="padding: 3px 8px;">
                    <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center text-muted">No GE Groups yet.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    
    <!-- TOTAL RECORDS -->
    <div id="citiesTableFooter" class="text-center py-2 mt-2" style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
      <strong style="color: #ffffff; font-size: 14px;">
        Total Records: {{ $cities->total() }}
      </strong>
    </div>
    
    <div class="mt-3">
      {{ $cities->links() }}
    </div>
  </div>
</div>

<!-- Edit City Modal -->
<div class="modal fade" id="editCityModal" tabindex="-1" aria-labelledby="editCityModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header">
        <h5 class="modal-title" id="editCityModalLabel">Edit GE Groups</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editCityForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">CMES <span class="text-danger">*</span></label>
            <select name="cme_id" id="editCityCmeId" class="form-select" required>
              <option value="">Select CMES</option>
              @foreach(($cmes ?? collect()) as $cme)
                <option value="{{ $cme->id }}">{{ $cme->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" id="editCityName" class="form-control" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" id="editCityStatus" class="form-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-accent">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
  
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // AJAX delete to remove only from table (not DB hard delete)
  document.querySelectorAll('form.city-delete-form').forEach(function(form){
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const row = form.closest('tr');
      const url = form.action;
      const token = form.querySelector('input[name="_token"]').value;
      const method = form.querySelector('input[name="_method"]').value || 'DELETE';

      const formData = new FormData();
      formData.append('_method', method);
      formData.append('_token', token);

      fetch(url, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(res => res.ok ? res.json() : Promise.reject())
      .then(() => {
        if (row) {
          row.style.opacity = '0.4';
          row.style.transition = 'opacity .2s ease';
          setTimeout(() => { row.remove(); }, 180);
        }
      })
      .catch(() => {
        // Fallback: submit normally
        form.submit();
      });
    });
  });

  const modalEl = document.getElementById('editCityModal');
  if (!modalEl) return;
  modalEl.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    const status = button.getAttribute('data-status');
    const cmeId = button.getAttribute('data-cme-id');
    const form = document.getElementById('editCityForm');
    const nameInput = document.getElementById('editCityName');
    const statusSelect = document.getElementById('editCityStatus');
    const cmeSelect = document.getElementById('editCityCmeId');

    if (form && id) {
      form.action = `${window.location.origin}/admin/city/${id}`;
    }
    if (nameInput) nameInput.value = name || '';
    if (statusSelect) statusSelect.value = status || 'active';
    if (cmeSelect) cmeSelect.value = cmeId || '';
  });
});
</script>
@endpush
@endsection
