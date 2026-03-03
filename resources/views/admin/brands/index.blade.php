@extends('layouts.sidebar')

@section('title', 'Brands — CMS Admin')

@section('content')
<div class="container-narrow">
<div class="mb-4 d-flex justify-content-between align-items-center">
  <div>
    <h2 class="text-white mb-1">Brands Management</h2>
    <p class="text-light mb-0">Manage brands for stock products</p>
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
    <h5 class="text-white"><i data-feather="plus" class="me-2"></i>Add Brand</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.brands.store') }}" class="d-flex flex-wrap align-items-end gap-2">
      @csrf
      <div style="min-width: 220px; flex: 0 0 260px;">
        <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">Category <span class="text-danger">*</span></label>
        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="" disabled selected>Select Category</option>
            @foreach($categories as $id => $name)
                <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div style="min-width: 220px; flex: 0 0 260px;">
        <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">Brand Name <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Brand name" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="d-grid" style="flex: 0 0 140px;">
        <button class="btn btn-outline-secondary" type="submit" style="width: 100%;"> <i data-feather="plus" class="me-2"></i> Add</button>
      </div>
    </form>
  </div>
</div>

<div class="card-glass">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0 text-white"><i data-feather="list" class="me-2"></i>Brands List</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle compact-table">
        <thead>
          <tr>
            <th style="width:70px">#</th>
            <th>Category</th>
            <th>Brand Name</th>
            <th style="width:180px">Actions</th>
          </tr>
        </thead>
        <tbody>
        @forelse($brands as $brand)
          <tr>
            <td>{{ $brand->id }}</td>
            <td>{{ $brand->category->name ?? 'N/A' }}</td>
            <td>{{ $brand->name }}</td>
            <td>
              <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editBrandModal" 
                        data-id="{{ $brand->id }}" data-name="{{ $brand->name }}" data-category-id="{{ $brand->category_id }}" title="Edit" style="padding: 3px 8px;">
                  <i data-feather="edit" style="width: 16px; height: 16px;"></i>
                </button>
                <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" class="brand-delete-form" onsubmit="return confirm('Delete this brand?')" style="display: inline;">
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
            <td colspan="4" class="text-center text-muted">No brands found.</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
    
    <div id="brandsTableFooter" class="text-center py-2 mt-2" style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
      <strong style="color: #ffffff; font-size: 14px;">
        Total Records: {{ $brands->total() }}
      </strong>
    </div>
    
    <div class="mt-3">
      {{ $brands->links() }}
    </div>
  </div>
</div>

<!-- Edit Brand Modal -->
<div class="modal fade" id="editBrandModal" tabindex="-1" aria-labelledby="editBrandModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header">
        <h5 class="modal-title" id="editBrandModalLabel">Edit Brand</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editBrandForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select name="category_id" id="editBrandCategoryId" class="form-select" required>
                @foreach($categories as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Brand Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="editBrandName" class="form-control" required>
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
  // AJAX delete to remove from table
  document.querySelectorAll('form.brand-delete-form').forEach(function(form){
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const row = form.closest('tr');
      const url = form.action;
      const token = form.querySelector('input[name="_token"]').value;

      const formData = new FormData();
      formData.append('_method', 'DELETE');
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

  const modalEl = document.getElementById('editBrandModal');
  if (!modalEl) return;
  modalEl.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    const categoryId = button.getAttribute('data-category-id');

    const form = document.getElementById('editBrandForm');
    const nameInput = document.getElementById('editBrandName');
    const categoryInput = document.getElementById('editBrandCategoryId');

    if (form && id) {
      form.action = `{{ url('admin/brands') }}/${id}`;
    }
    if (nameInput) nameInput.value = name || '';
    if (categoryInput) categoryInput.value = categoryId || '';
  });
});
</script>
@endpush
@endsection
