@extends('layouts.sidebar')

@section('title', 'Create New User — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Create New User</h2>
      <p class="text-light">Add a new user to the system</p>
    </div>
    
  </div>
</div>

<!-- CREATE USER FORM -->
<div class="card-glass">
  <div class="card-body">
    <form action="{{ route('admin.users.store') }}" method="POST">
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
            <label for="role_id" class="form-label text-white">Role <span class="text-danger">*</span></label>
            <select class="form-select @error('role_id') is-invalid @enderror" 
                    id="role_id" name="role_id" required>
              <option value="">Select a role</option>
              @foreach($roles as $role)
                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                  {{ $role->role_name }}
                </option>
              @endforeach
            </select>
            @error('role_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
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

      

      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="city_id" class="form-label text-white">GE Groups</label>
            <select class="form-select @error('city_id') is-invalid @enderror" 
                    id="city_id" name="city_id">
              <option value="">Select GE Groups</option>
              @foreach($cities as $city)
                <option value="{{ $city->id }}" data-province="{{ $city->province ?? '' }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                  {{ $city->name }}{{ $city->province ? ' (' . $city->province . ')' : '' }}
                </option>
              @endforeach
            </select>
            <small class="text-muted"></small>
            @error('city_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        
        <div class="col-md-6">
          <div class="mb-3">
            <label for="sector_id" class="form-label text-white">GE Nodes</label>
            <select class="form-select @error('sector_id') is-invalid @enderror" 
                    id="sector_id" name="sector_id" disabled>
              <option value="">Select GE Groups first</option>
            </select>
            <small class="text-muted"></small>
            @error('sector_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      
      
      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
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

  // Phone number input validation - only allow numbers
  document.addEventListener('DOMContentLoaded', function() {
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

  // Dynamic sector loading based on city
  const citySelect = document.getElementById('city_id');
  const sectorSelect = document.getElementById('sector_id');
  const roleSelect = document.getElementById('role_id');

  if (citySelect && sectorSelect) {
    citySelect.addEventListener('change', function() {
      const cityId = this.value;
      const roleText = roleSelect ? roleSelect.options[roleSelect.selectedIndex].text.toLowerCase() : '';
      
      // Don't load sectors if role is GE (garrison_engineer) - GE sees all sectors
      if (roleText.includes('garrison engineer') || roleText.includes('garrison_engineer')) {
        sectorSelect.innerHTML = '<option value="">N/A (GE sees all sectors)</option>';
        sectorSelect.disabled = true;
        return;
      }
      
      if (!cityId) {
        sectorSelect.innerHTML = '<option value="">Select GE Groups first</option>';
        sectorSelect.disabled = true;
        return;
      }

      sectorSelect.innerHTML = '<option value="">Loading sectors...</option>';
      sectorSelect.disabled = true;

      fetch(`{{ route('admin.sectors.by-city') }}?city_id=${cityId}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
        credentials: 'same-origin'
      })
      .then(response => response.json())
      .then(data => {
        sectorSelect.innerHTML = '<option value="">Select Sector</option>';
        if (data && data.length > 0) {
          data.forEach(sector => {
            const option = document.createElement('option');
            option.value = sector.id;
            option.textContent = sector.name;
            sectorSelect.appendChild(option);
          });
        }
        sectorSelect.disabled = false;
      })
      .catch(error => {
        console.error('Error loading sectors:', error);
        sectorSelect.innerHTML = '<option value="">Error loading sectors</option>';
        sectorSelect.disabled = false;
      });
    });

    // Handle role change - show/hide city/sector fields
    if (roleSelect) {
      roleSelect.addEventListener('change', function() {
        const roleId = this.value;
        const roleText = this.options[this.selectedIndex].text.toLowerCase();
        
        // Enable/disable city and sector based on role
        if (roleText.includes('director') || roleText.includes('admin')) {
          citySelect.disabled = true;
          sectorSelect.disabled = true;
          citySelect.value = '';
          sectorSelect.innerHTML = '<option value="">Select GE Groups first</option>';
          citySelect.required = false;
          sectorSelect.required = false;
        } else if (roleText.includes('garrison engineer') || roleText.includes('garrison_engineer')) {
          citySelect.disabled = false;
          sectorSelect.disabled = true;
          sectorSelect.innerHTML = '<option value="">N/A</option>';
          citySelect.required = true;
          sectorSelect.required = false;
        } else if (roleText.includes('complaint center') || roleText.includes('complaint_center') || 
                   roleText.includes('department staff') || roleText.includes('department_staff')) {
          citySelect.disabled = false;
          citySelect.required = true;
          sectorSelect.required = true;
          // Sector will be enabled when city is selected
        } else {
          citySelect.disabled = false;
          sectorSelect.disabled = false;
          citySelect.required = false;
          sectorSelect.required = false;
        }
      });

      // Trigger on page load if role is pre-selected
      if (roleSelect.value) {
        roleSelect.dispatchEvent(new Event('change'));
      }
    }

    // Trigger city change if pre-selected
    if (citySelect.value) {
      citySelect.dispatchEvent(new Event('change'));
    }
  }
</script>
@endpush
