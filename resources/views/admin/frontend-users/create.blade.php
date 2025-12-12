@extends('layouts.sidebar')

@section('title', 'Create Frontend User — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Create Frontend User</h2>
      <p class="text-light">Add a new frontend user to the system</p>
    </div>
  </div>
</div>

<!-- CREATE USER FORM -->
<div class="card-glass">
  <div class="card-body">
    <form action="{{ route('admin.frontend-users.store') }}" method="POST">
      @csrf

      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="username" class="form-label text-white">Username <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('username') is-invalid @enderror"
                   id="username" name="username" value="{{ old('username') }}" required>
            @error('username')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="name" class="form-label text-white">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror"
                   id="name" name="name" value="{{ old('name') }}">
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="email" class="form-label text-white">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}">
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="phone" class="form-label text-white">Phone</label>
            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                   id="phone" name="phone" value="{{ old('phone') }}"
                   pattern="[0-9]*" inputmode="numeric"
                   onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            @error('phone')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="status" class="form-label text-white">Status</label>
            <select class="form-select @error('status') is-invalid @enderror"
                    id="status" name="status">
              <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label text-white">Super Admin</label>
            <div class="d-flex align-items-center">
              <input type="checkbox" class="form-check-input" id="is_super_admin" name="is_super_admin"
                     value="1" {{ old('is_super_admin') ? 'checked' : '' }}
                     style="width: 20px; height: 20px; cursor: pointer;">
              <label for="is_super_admin" class="form-check-label text-muted ms-2 mb-0" style="cursor: pointer;">
                Grant all privileges
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="password" class="form-label text-white">Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control @error('password') is-invalid @enderror"
                   id="password" name="password" required>
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="password_confirmation" class="form-label text-white">Confirm Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control"
                   id="password_confirmation" name="password_confirmation" required>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('admin.frontend-users.index') }}" class="btn btn-outline-secondary">
          <i data-feather="x" class="me-2"></i>Cancel
        </a>
        <button type="submit" class="btn btn-accent">
          <i data-feather="user-plus" class="me-2"></i>Create User
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('styles')
@endpush

@push('scripts')
<script>
  feather.replace();

  // Handle Super Admin checkbox and Privilege logic
  document.addEventListener('DOMContentLoaded', function() {
    const superAdminCheckbox = document.getElementById('is_super_admin');
    const allPrivilegeCheckboxes = document.querySelectorAll('.privilege-checkbox');
    const cmeCheckboxes = document.querySelectorAll('.cme-checkbox');

    if (superAdminCheckbox) {
      // Toggle all checkboxes when Super Admin is clicked
      superAdminCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        allPrivilegeCheckboxes.forEach(cb => {
          cb.checked = isChecked;
        });
      });
    }

    // Handle CME checkbox clicking (Select all cities under it)
    cmeCheckboxes.forEach(cmeCb => {
      cmeCb.addEventListener('change', function() {
        const cmeId = this.dataset.cmeId;
        const cityCheckboxes = document.querySelectorAll(`.city-checkbox[data-parent-cme="${cmeId}"]`);
        cityCheckboxes.forEach(cityCb => {
          cityCb.checked = this.checked;
        });
        updateSuperAdminState();
      });
    });

    // Update Super Admin state if individual checkboxes are changed
    allPrivilegeCheckboxes.forEach(cb => {
      cb.addEventListener('change', updateSuperAdminState);
    });

    function updateSuperAdminState() {
      if (!superAdminCheckbox) return;

      const allChecked = Array.from(allPrivilegeCheckboxes).every(cb => cb.checked);
      superAdminCheckbox.checked = allChecked;

      // Also update CME checkboxes based on their cities?
      // Optional: If all cities are unchecked, uncheck CME? Or if all checked, check CME?
      // For now, keeping it simple as per request.
    }


    // Phone number input validation - only allow numbers
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
      phoneInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
      });
      phoneInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        const numbersOnly = pastedText.replace(/[^0-9]/g, '');
        this.value = numbersOnly;
      });
    }
  });
</script>
@endpush
