@extends('layouts.sidebar')

@section('title', 'Complaint Categories — CMS Admin')

@section('content')
<div class="container-narrow">
<div class="mb-4 d-flex justify-content-between align-items-center">
  <div>
    <h2 class="text-white mb-1">Complaint Categories</h2>
    <p class="text-light mb-0">Manage complaint categories for suggestions</p>
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
    <h5 class="text-white"><i data-feather="plus" class="me-2"></i>Add Category</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.category.store') }}" class="d-flex flex-wrap align-items-end gap-2">
      @csrf
      <div style="min-width: 220px; flex: 0 0 260px;">
        <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">Name</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Category name" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div style="min-width: 260px; flex: 1 1 380px;">
        <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">Description</label>
        <input type="text" name="description" value="{{ old('description') }}" class="form-control @error('description') is-invalid @enderror" placeholder="Short description (optional)">
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="d-grid" style="flex: 0 0 140px;">
        <button class="btn btn-outline-secondary" type="submit" style="width: 100%;"> <i data-feather="plus" class="me-2"></i> Add</button>
      </div>
    </form>
  </div>
@push('styles')
@endpush

</div>

<div class="card-glass">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0 text-white"><i data-feather="list" class="me-2"></i>Categories</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table  align-middle compact-table">
        <thead>
          <tr>
            <th style="width:70px">#</th>
            <th>Name</th>
            <th>Description</th>
            <th style="width:180px">Actions</th>
          </tr>
        </thead>
        <tbody>
        @forelse($categories as $cat)
          <tr>
            <td>{{ $cat->id }}</td>
            <td>{{ $cat->name }}</td>
            <td>{{ $cat->description ? Str::limit($cat->description, 80) : '-' }}</td>
            <td>
              <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editCategoryModal" 
                        data-id="{{ $cat->id }}" data-name="{{ $cat->name }}" data-description="{{ $cat->description }}" title="Edit" style="padding: 3px 8px;">
                  <i data-feather="edit" style="width: 16px; height: 16px;"></i>
                </button>
                <form action="{{ route('admin.category.destroy', $cat) }}" method="POST" class="category-delete-form" onsubmit="return confirm('Delete this category?')" style="display: inline;">
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
            <td colspan="4" class="text-center text-muted">No categories yet.</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
    
    <!-- TOTAL RECORDS -->
    <div id="categoriesTableFooter" class="text-center py-2 mt-2" style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
      <strong style="color: #ffffff; font-size: 14px;">
        Total Records: {{ $categories->total() }}
      </strong>
    </div>
    
    <div class="mt-3">
      {{ $categories->links() }}
    </div>
  </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header">
        <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editCategoryForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" id="editCategoryName" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" id="editCategoryDescription" class="form-control" rows="2" placeholder="Optional"></textarea>
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
  document.querySelectorAll('form.category-delete-form').forEach(function(form){
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

  const modalEl = document.getElementById('editCategoryModal');
  if (!modalEl) return;
  modalEl.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    const description = button.getAttribute('data-description') || '';

    const form = document.getElementById('editCategoryForm');
    const nameInput = document.getElementById('editCategoryName');

    if (form && id) {
      form.action = `${window.location.origin}/admin/category/${id}`;
    }
    if (nameInput) nameInput.value = name || '';
    const descInput = document.getElementById('editCategoryDescription');
    if (descInput) descInput.value = description;
  });
});
</script>
@endpush
@endsection


