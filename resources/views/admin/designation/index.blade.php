@extends('layouts.sidebar')

@section('title', 'Designations — CMS Admin')

@section('content')
    <div class="container-narrow">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="text-white mb-1">Designations</h2>
                <p class="text-light mb-0">Manage designations for employees</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card-glass mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0 text-white"><i data-feather="plus" class="me-2"></i>Add Designation</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.designation.store') }}"
                    class="d-flex flex-wrap align-items-end gap-2">
                    @csrf
                    <div style="min-width: 200px; flex: 0 0 240px;">
                        <label class="form-label small mb-1"
                            style="color: #000000 !important; font-weight: 500;">Category</label>
                        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @if (isset($categories) && $categories->count() > 0)
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>
                                        {{ ucfirst($cat) }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div style="min-width: 200px; flex: 0 0 240px;">
                        <label class="form-label small mb-1"
                            style="color: #000000 !important; font-weight: 500;">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror" placeholder="Designation name"
                            required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div style="min-width: 140px; flex: 0 0 160px;">
                        <label class="form-label small mb-1"
                            style="color: #000000 !important; font-weight: 500;">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="d-grid" style="flex: 0 0 120px;">
                        <button class="btn btn-outline-secondary" type="submit" style="width: 100%;"><i data-feather="plus" class="me-2"></i>Add</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-glass">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 text-white"><i data-feather="list" class="me-2"></i>Designations</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table  align-middle compact-table">
                        <thead>
                            <tr>
                                <th style="width:70px">#</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th style="width:140px">Status</th>
                                <th style="width:180px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($designations as $designation)
                                <tr>
                                    <td>{{ $designation->id }}</td>
                                    <td>{{ $designation->name }}</td>
                                    <td>{{ ucfirst($designation->category ?? 'N/A') }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $designation->status === 'active' ? 'bg-success' : 'bg-danger' }}"
                                            style="color: #ffffff !important;">{{ ucfirst($designation->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#editDesignationModal"
                                                data-id="{{ $designation->id }}"
                                                data-category="{{ $designation->category }}"
                                                data-name="{{ $designation->name }}"
                                                data-status="{{ $designation->status }}" title="Edit" style="padding: 3px 8px;">
                                                <i data-feather="edit" style="width: 16px; height: 16px;"></i>
                                            </button>
                                            <form action="{{ route('admin.designation.destroy', $designation) }}"
                                                method="POST" class="designation-delete-form"
                                                onsubmit="return confirm('Delete this designation?')" style="display: inline;">
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
                                    <td colspan="5" class="text-center text-muted">No designations yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- TOTAL RECORDS -->
                <div id="designationsTableFooter" class="text-center py-2 mt-2"
                    style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
                    <strong style="color: #ffffff; font-size: 14px;">
                        Total Records: {{ $designations->total() }}
                    </strong>
                </div>

                <div class="mt-3">
                    {{ $designations->links() }}
                </div>
            </div>
        </div>

        <!-- Edit Designation Modal -->
        <div class="modal fade" id="editDesignationModal" tabindex="-1" aria-labelledby="editDesignationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content bg-dark text-white">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDesignationModalLabel">Edit Designation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editDesignationForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" id="editDesignationCategory" class="form-select" required>
                                    <option value="">Select Category</option>
                                    @if (isset($categories) && $categories->count() > 0)
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" id="editDesignationName" class="form-control"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="editDesignationStatus" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-accent">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @push('styles')
        @endpush

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // AJAX delete
                    document.querySelectorAll('form.designation-delete-form').forEach(function(form) {
                        form.addEventListener('submit', function(e) {
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
                                        setTimeout(() => {
                                            row.remove();
                                        }, 180);
                                    }
                                })
                                .catch(() => {
                                    form.submit();
                                });
                        });
                    });

                    const modalEl = document.getElementById('editDesignationModal');
                    if (modalEl) {
                        modalEl.addEventListener('show.bs.modal', function(event) {
                            const button = event.relatedTarget;
                            const id = button.getAttribute('data-id');
                            const category = button.getAttribute('data-category');
                            const name = button.getAttribute('data-name');
                            const status = button.getAttribute('data-status');
                            const form = document.getElementById('editDesignationForm');
                            const categorySelect = document.getElementById('editDesignationCategory');
                            const nameInput = document.getElementById('editDesignationName');
                            const statusSelect = document.getElementById('editDesignationStatus');

                            if (form && id) {
                                form.action = `${window.location.origin}/admin/designation/${id}`;
                            }
                            if (categorySelect && category) categorySelect.value = category;
                            if (nameInput) nameInput.value = name || '';
                            if (statusSelect) statusSelect.value = status || 'active';
                        });
                    }

                    // Handle form submission via AJAX to stay in modal
                    // Always prevent normal form submission and handle via AJAX
                    const editDesignationForm = document.getElementById('editDesignationForm');
                    if (editDesignationForm && !editDesignationForm.hasAttribute('data-ajax-bound')) {
                        editDesignationForm.setAttribute('data-ajax-bound', 'true');
                        editDesignationForm.addEventListener('submit', function(e) {
                            e.preventDefault();
                            e.stopPropagation();

                            const formData = new FormData(editDesignationForm);
                            const submitBtn = editDesignationForm.querySelector('button[type="submit"]');
                            const originalText = submitBtn ? submitBtn.textContent : '';

                            if (submitBtn) {
                                submitBtn.disabled = true;
                                submitBtn.textContent = 'Saving...';
                            }

                            fetch(editDesignationForm.action, {
                                    method: 'POST',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                            ?.getAttribute('content') || formData.get('_token')
                                    },
                                    credentials: 'same-origin',
                                    body: formData
                                })
                                .then(response => {
                                    if (response.ok) {
                                        const contentType = response.headers.get('content-type');
                                        if (contentType && contentType.includes('application/json')) {
                                            return response.json();
                                        }
                                        return {
                                            success: true
                                        };
                                    }
                                    return response.text().then(text => {
                                        try {
                                            return JSON.parse(text);
                                        } catch {
                                            throw new Error(text || 'Update failed');
                                        }
                                    });
                                })
                                .then(data => {
                                    // Close edit modal
                                    const editModalEl = document.getElementById('editDesignationModal');
                                    if (editModalEl) {
                                        const editModal = bootstrap.Modal.getInstance(editModalEl);
                                        if (editModal) {
                                            editModal.hide();
                                        }
                                    }

                                    // Show success message and reload page
                                    const alertDiv = document.createElement('div');
                                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                                    alertDiv.setAttribute('role', 'alert');
                                    alertDiv.innerHTML = (data.message || 'Designation updated successfully') +
                                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';

                                    const container = document.querySelector('.container-narrow');
                                    if (container) {
                                        container.insertBefore(alertDiv, container.firstChild);
                                        setTimeout(() => {
                                            alertDiv.remove();
                                        }, 5000);
                                    }

                                    // Reload page after a short delay to show updated data
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1000);
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('Error updating designation: ' + (error.message || 'Unknown error'));
                                    if (submitBtn) {
                                        submitBtn.disabled = false;
                                        submitBtn.textContent = originalText;
                                    }
                                });
                        });
                    }
                });
            </script>
        @endpush
    @endsection
