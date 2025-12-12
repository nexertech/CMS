@extends('layouts.sidebar')

@section('title', 'CMES — CMS Admin')

@section('content')
<div class="container-narrow">
  <div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-1">CMES</h2>
      <p class="text-light mb-0">Manage CMES records used for GE Groups & Nodes</p>
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
      <h5 class="card-title mb-0 text-white"><i data-feather="plus" class="me-2"></i>Add CMES</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.cmes.store') }}" class="d-flex flex-wrap align-items-end gap-2">
        @csrf
        <div style="min-width: 220px; flex: 0 0 260px;">
          <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">Name</label>
          <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="CMES name" required>
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
  </div>

  <div class="card-glass">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0 text-white"><i data-feather="layers" class="me-2"></i>CMES List</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table align-middle compact-table">
          <thead>
            <tr>
              <th style="width:70px">#</th>
              <th>Name</th>
              <th style="width:140px">Status</th>
              <th style="width:180px">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($cmes as $cme)
            <tr>
              <td>{{ $cme->id }}</td>
              <td>{{ $cme->name }}</td>
              <td>
                <span class="badge {{ $cme->status==='active' ? 'bg-success' : 'bg-danger' }}" style="color: #ffffff !important;">{{ ucfirst($cme->status) }}</span>
              </td>
              <td>
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editCmeModal" 
                          data-id="{{ $cme->id }}" data-name="{{ $cme->name }}" data-status="{{ $cme->status }}" title="Edit" style="padding: 3px 8px;">
                    <i data-feather="edit" style="width: 16px; height: 16px;"></i>
                  </button>
                  <form action="{{ route('admin.cmes.destroy', $cme) }}" method="POST" class="cme-delete-form" onsubmit="return confirm('Delete this CMES?')" style="display: inline;">
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
              <td colspan="4" class="text-center text-muted">No CMES yet.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div id="cmesTableFooter" class="text-center py-2 mt-2" style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
        <strong style="color: #ffffff; font-size: 14px;">Total Records: {{ $cmes->total() }}</strong>
      </div>

      <div class="mt-3">
        {{ $cmes->links() }}
      </div>
    </div>
  </div>

  <div class="modal fade" id="editCmeModal" tabindex="-1" aria-labelledby="editCmeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content bg-dark text-white">
        <div class="modal-header">
          <h5 class="modal-title" id="editCmeModalLabel">Edit CMES</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editCmeForm" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" name="name" id="editCmeName" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select name="status" id="editCmeStatus" class="form-select">
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
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('form.cme-delete-form').forEach(function(form){
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
        form.submit();
      });
    });
  });

  const modalEl = document.getElementById('editCmeModal');
  if (!modalEl) return;
  modalEl.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    const status = button.getAttribute('data-status');
    const form = document.getElementById('editCmeForm');
    const nameInput = document.getElementById('editCmeName');
    const statusSelect = document.getElementById('editCmeStatus');

    if (form && id) {
      form.action = `${window.location.origin}/admin/cmes/${id}`;
    }
    if (nameInput) nameInput.value = name || '';
    if (statusSelect) statusSelect.value = status || 'active';
  });
});
</script>
@endpush

