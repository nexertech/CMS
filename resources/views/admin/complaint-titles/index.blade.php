@extends('layouts.sidebar')

@section('title', 'Complaint Types — CMS Admin')

@section('content')
<div class="container-narrow">
<div class="mb-4 d-flex justify-content-between align-items-center">
  <div>
    <h2 class="text-white mb-1">Complaint Types</h2>
    <p class="text-light mb-0">Manage complaint Types by category</p>
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
    <h5 class="card-title mb-0 text-white"><i data-feather="plus" class="me-2"></i>Add Complaint Type</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.complaint-titles.store') }}" class="d-flex flex-wrap align-items-end gap-2">
      @csrf
      <div style="min-width: 200px; flex: 0 0 240px;">
        <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">Category <span class="text-danger">*</span></label>
        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
          <option value="">Select Category</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ old('category') == $cat->id ? 'selected' : '' }}>{{ ucfirst($cat->name) }}</option>
          @endforeach
        </select>
        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div style="min-width: 280px; flex: 1 1 400px;">
        <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">Types <span class="text-danger">*</span></label>
        <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" placeholder="Complaint Type" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div style="min-width: 260px; flex: 1 1 380px;">
        <label class="form-label small mb-1" style="color: #000000 !important; font-weight: 500;">Questions (to guide receiver)</label>
        <textarea name="questions" class="form-control @error('questions') is-invalid @enderror" placeholder="e.g. Is it in whole house or one room?" rows="1">{{ old('questions') }}</textarea>
        @error('questions')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="d-grid" style="flex: 0 0 140px;">
        <button class="btn btn-outline-secondary" type="submit" style="width: 100%;"> <i data-feather="plus" class="me-2"></i> Add</button>
      </div>
    </form>
  </div>
</div>

<div class="card-glass">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0 text-white"><i data-feather="list" class="me-2"></i>Complaint Types</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-dark align-middle compact-table">
        <thead>
          <tr>
            <th style="width:70px">#</th>
                        <th>Types</th>

            <th>Category</th>
            <th>Questions</th>
            <th style="width:180px">Actions</th>
          </tr>
        </thead>
        <tbody>
        @forelse($complaintTitles as $title)
          <tr>
            <td>{{ $title->id }}</td>
                        <td><strong>{{ $title->title }}</strong></td>

            <td>
              {{ ucfirst($title->category->name ?? '-') }}
            </td>
            <td>{{ $title->questions ? Str::limit($title->questions, 50) : '-' }}</td>
            <td>
              <div class="btn-group" role="group">
                <button class="btn btn-outline-primary btn-sm edit-title-btn" 
                    data-id="{{ $title->id }}"
                    data-category="{{ $title->category_id }}"
                    data-title="{!! htmlspecialchars($title->title) !!}"
                    data-questions="{!! htmlspecialchars($title->questions ?? '') !!}"
                    title="Edit" style="padding: 3px 8px;">
                  <i data-feather="edit" style="width: 16px; height: 16px;"></i>
                </button>
                <form action="{{ route('admin.complaint-titles.destroy', $title) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this complaint title?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete" style="padding: 3px 8px;">
                    <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center py-4">
              <i data-feather="alert-circle" class="feather-lg mb-2"></i>
              <div>No complaint Type found</div>
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>

    <!-- TOTAL RECORDS -->
    <div id="complaintTitlesTableFooter" class="text-center py-2 mt-2" style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
      <strong style="color: #ffffff; font-size: 14px;">
        Total Records: {{ $complaintTitles->total() }}
      </strong>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-3">
      {{ $complaintTitles->links() }}
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit Complaint Title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select name="category" id="edit_category" class="form-select" required>
              <option value="">Select Category</option>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ ucfirst($cat->name) }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="edit_title" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Questions (to guide receiver)</label>
            <textarea name="questions" id="edit_questions" class="form-control" rows="3"></textarea>
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

@push('styles')
@endpush

@push('scripts')
<script>
  feather.replace();

  // Use event delegation for dynamically loaded content if needed, or direct bind
  document.addEventListener('DOMContentLoaded', function() {
    // Attach click event to all edit buttons
    const editButtons = document.querySelectorAll('.edit-title-btn');
    editButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const category = this.getAttribute('data-category');
        const title = this.getAttribute('data-title');
        const questions = this.getAttribute('data-questions');
        
        document.getElementById('editForm').action = '{{ url("admin/complaint-titles") }}/' + id;
        document.getElementById('edit_category').value = category;
        document.getElementById('edit_title').value = title;
        document.getElementById('edit_questions').value = questions;
        
        const modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();
      });
    });
  });
</script>
@endpush
@endsection

