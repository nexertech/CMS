@extends('layouts.sidebar')

@section('title', 'Edit Frontend User — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Edit Frontend User</h2>
      <p class="text-light">Update frontend user information</p>
    </div>
  </div>
</div>

<!-- EDIT USER FORM -->
<div class="card-glass">
  <div class="card-body">
    <form action="{{ route('admin.frontend-users.update', $frontend_user) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="username" class="form-label text-white">Username <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('username') is-invalid @enderror"
                   id="username" name="username" value="{{ old('username', $frontend_user->username) }}" required>
            @error('username')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="name" class="form-label text-white">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror"
                   id="name" name="name" value="{{ old('name', $frontend_user->name) }}">
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
                   id="email" name="email" value="{{ old('email', $frontend_user->email) }}">
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="phone" class="form-label text-white">Phone</label>
            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                   id="phone" name="phone" value="{{ old('phone', $frontend_user->phone) }}"
                   pattern="[0-9]*" inputmode="numeric"
                   onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            @error('phone')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      @php
        // Calculate all available CMEs and Cities (remove nulls)
        $allCmes = $cmes->pluck('id')->unique()->filter()->values()->toArray();
        $allCities = $cmes->pluck('city_id')->unique()->filter()->values()->toArray();

        // Check if user has ALL privileges (super admin)
        // User must have ALL CMEs and ALL Cities to be considered super admin
        $userCmeIdsArray = $userCmeIds ?? [];
        $userCityIdsArray = $userCityIds ?? [];

        $hasAllCmes = count($allCmes) > 0 &&
                      count($userCmeIdsArray) > 0 &&
                      count($allCmes) === count($userCmeIdsArray) &&
                      count(array_diff($allCmes, $userCmeIdsArray)) === 0;

        $hasAllCities = count($allCities) > 0 &&
                        count($userCityIdsArray) > 0 &&
                        count($allCities) === count($userCityIdsArray) &&
                        count(array_diff($allCities, $userCityIdsArray)) === 0;

        $isSuperAdmin = $hasAllCmes && $hasAllCities;
      @endphp

      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="status" class="form-label text-white">Status</label>
            <select class="form-select @error('status') is-invalid @enderror"
                    id="status" name="status">
              <option value="active" {{ old('status', $frontend_user->status) == 'active' ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ old('status', $frontend_user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                     value="1" {{ $isSuperAdmin ? 'checked' : '' }}
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
            <label for="password" class="form-label text-white">New Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror"
                   id="password" name="password">
            <div class="form-text text-muted">Leave blank to keep current password</div>
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="password_confirmation" class="form-label text-white">Confirm New Password</label>
            <input type="password" class="form-control"
                   id="password_confirmation" name="password_confirmation">
          </div>
        </div>
      </div>      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('admin.frontend-users.index') }}" class="btn btn-outline-secondary">
          <i data-feather="x" class="me-2"></i>Cancel
        </a>
        <button type="submit" class="btn btn-accent">
          <i data-feather="save" class="me-2"></i>Update User
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

    // Form validation - check phone number before submit
    const userForm = document.querySelector('form[action*="frontend-users"]');
    if (userForm) {
      userForm.addEventListener('submit', function(e) {
        const phoneValue = phoneInput ? phoneInput.value.trim() : '';
        if (phoneValue && phoneValue.length < 11) {
          e.preventDefault();
          alert('Phone number must be at least 11 digits.');
          if (phoneInput) phoneInput.focus();
          return false;
        }
      });
    }
  });
</script>

@endpush
