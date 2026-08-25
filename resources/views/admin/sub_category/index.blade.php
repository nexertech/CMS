@extends('layouts.sidebar')

@section('title', 'Sub Categories — CMS Admin')

@section('content')
<div class="container-narrow">
<div class="mb-4 d-flex justify-content-between align-items-center">
  <div>
    <h2 class="text-white mb-1">Sub Categories</h2>
    <p class="text-light mb-0">Manage sub-categories linked to complaint categories</p>
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
    <h5 class="text-white"><i data-feather="plus" class="me-2"></i>Add Sub Category</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.sub-category.store') }}" class="d-flex flex-wrap align-items-end gap-2">
      @csrf
      <div style="min-width: 200px; flex: 0 0 220px;">
        <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">Name</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Sub category name" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div style="min-width: 200px; flex: 0 0 220px;">
        <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">Main Category</label>
        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
          <option value="">Select Main Category</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
          @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div style="min-width: 140px; flex: 0 0 140px;">
        <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">Status</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
          <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
          <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div style="min-width: 240px; flex: 1 1 300px;">
        <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">Description</label>
        <input type="text" name="description" value="{{ old('description') }}" class="form-control @error('description') is-invalid @enderror" placeholder="Short description (optional)">
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="d-grid" style="flex: 0 0 140px;">
        <button class="btn btn-outline-secondary" type="submit" style="width: 100%;"> <i data-feather="plus" class="me-2"></i> Add</button>
      </div>
    </form>
  </div>
</div>

<div class="card-glass">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0 text-white"><i data-feather="list" class="me-2"></i>Sub Categories</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle compact-table">
        <thead>
          <tr>
            <th style="width:70px">#</th>
            <th>Name</th>
            <th>Main Category</th>
            <th>Status</th>
            <th>Description</th>
            <th style="width:180px">Actions</th>
          </tr>
        </thead>
        <tbody>
        @forelse($subCategories as $subCat)
          <tr>
            <td>{{ $subCat->id }}</td>
            <td class="fw-bold">{{ $subCat->name }}</td>
            <td>{{ $subCat->category ? $subCat->category->name : 'N/A' }}</td>
            <td>
              @if($subCat->status === 1)
                <span class="badge bg-success" style="color: #ffffff !important;">Active</span>
              @else
                <span class="badge bg-danger" style="color: #ffffff !important;">Inactive</span>
              @endif
            </td>
            <td>{{ $subCat->description ? Str::limit($subCat->description, 80) : '-' }}</td>
            <td>
              <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editSubCategoryModal" 
                        data-id="{{ $subCat->id }}" data-category-id="{{ $subCat->category_id }}" data-name="{{ $subCat->name }}" data-status="{{ $subCat->status }}" data-description="{{ $subCat->description }}" title="Edit" style="padding: 3px 8px;">
                  <i data-feather="edit" style="width: 16px; height: 16px;"></i>
                </button>
                <form action="{{ route('admin.sub-category.destroy', $subCat) }}" method="POST" class="sub-category-delete-form" onsubmit="return confirm('Delete this sub-category?')" style="display: inline;">
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
            <td colspan="6" class="text-center text-muted">No sub categories yet.</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
    
    <!-- TOTAL RECORDS FOOTER -->
    <div id="subCategoriesTableFooter" class="text-center py-2 mt-2" style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
      <strong style="color: #ffffff; font-size: 14px;">
        Total Records: {{ $subCategories->total() }}
      </strong>
    </div>
    
    <div class="mt-3">
      {{ $subCategories->links() }}
    </div>
  </div>
</div>

<!-- Edit Sub Category Modal -->
<div class="modal fade" id="editSubCategoryModal" tabindex="-1" aria-labelledby="editSubCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header">
        <h5 class="modal-title" id="editSubCategoryModalLabel">Edit Sub Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editSubCategoryForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" id="editSubCategoryName" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Main Category</label>
            <select name="category_id" id="editSubCategoryCategoryId" class="form-select" required>
              <option value="">Select Main Category</option>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" id="editSubCategoryStatus" class="form-select" required>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" id="editSubCategoryDescription" class="form-control" rows="2" placeholder="Optional"></textarea>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // AJAX delete to remove only from table
  document.querySelectorAll('form.sub-category-delete-form').forEach(function(form){
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

  const modalEl = document.getElementById('editSubCategoryModal');
  if (!modalEl) return;

  // Add blur effect on modal open
  modalEl.addEventListener('show.bs.modal', function (event) {
    document.body.classList.add('modal-open-blur');
    const button = event.relatedTarget;
    if (!button) return;
    const id = button.getAttribute('data-id');
    const categoryId = button.getAttribute('data-category-id');
    const name = button.getAttribute('data-name');
    const status = button.getAttribute('data-status') || '1';
    const description = button.getAttribute('data-description') || '';

    const form = document.getElementById('editSubCategoryForm');
    const catInput = document.getElementById('editSubCategoryCategoryId');
    const nameInput = document.getElementById('editSubCategoryName');
    const statusInput = document.getElementById('editSubCategoryStatus');
    const descInput = document.getElementById('editSubCategoryDescription');

    if (form && id) {
      form.action = `${window.location.origin}/admin/sub-category/${id}`;
    }
    if (catInput) catInput.value = categoryId || '';
    if (nameInput) nameInput.value = name || '';
    if (statusInput) statusInput.value = status;
    if (descInput) descInput.value = description;
  });

  // Remove blur effect on modal close
  modalEl.addEventListener('hidden.bs.modal', function () {
    document.body.classList.remove('modal-open-blur');
  });
});
</script>
@endpush
@endsection
