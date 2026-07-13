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
    {{-- Server-side validation errors alert --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="background: rgba(220, 53, 69, 0.15); border: 1px solid rgba(220, 53, 69, 0.4); border-radius: 8px;">
      <div class="d-flex align-items-start">
        <i data-feather="alert-circle" class="me-2 mt-1 flex-shrink-0" style="width: 20px; height: 20px; color: #ff6b6b;"></i>
        <div>
          <strong class="text-danger">Please fix the following errors:</strong>
          <ul class="mb-0 mt-2" style="padding-left: 1.2rem;">
            @foreach($errors->all() as $error)
              <li class="text-danger" style="font-size: 0.9rem;">{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      </div>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" id="createUserForm" novalidate>
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
              <option value="1" {{ old('status', 1) == 'active' ? 'selected' : '' }}>Active</option>
              <option value="0" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                   id="password" name="password" placeholder="Minimum 8 characters" required>
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
            <label class="form-label text-white d-flex justify-content-between align-items-center mb-2">
              <span>GE Groups <span class="text-danger">*</span></span>
              <div>
                <button type="button" class="btn btn-sm btn-link text-info text-decoration-none p-0 me-2 js-select-all-cities" style="font-size: 0.75rem;">Select All</button>
                <span class="badge bg-primary bg-opacity-75 rounded-pill js-city-count" style="font-size: 0.70rem;">0 Selected</span>
              </div>
            </label>
            <div class="location-container custom-scrollbar">
              <div class="row g-2">
                @foreach($cities as $city)
                <div class="col-sm-6">
                  <label class="custom-checkbox-card w-100 mb-0">
                    <input class="form-check-input city-checkbox ms-1" type="checkbox" name="city_id[]" value="{{ $city->id }}" data-name="{{ $city->name }}" {{ (is_array(old('city_id')) && in_array($city->id, old('city_id'))) || (isset($defaultCityIds) && in_array($city->id, $defaultCityIds)) ? 'checked' : '' }}>
                    <div class="ms-2 text-truncate">
                      <span class="d-block text-white fw-medium" style="font-size: 0.9rem;">{{ $city->name }}</span>
                      @if($city->province)
                      <small class="text-muted d-block text-truncate lh-1 mt-1" style="font-size: 0.70rem;">{{ $city->province }}</small>
                      @endif
                    </div>
                  </label>
                </div>
                @endforeach
              </div>
            </div>
            @error('city_id')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>
        </div>
        
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label text-white d-flex justify-content-between align-items-center mb-2">
              <span>GE Nodes <span class="text-danger">*</span></span>
              <div>
                <button type="button" class="btn btn-sm btn-link text-info text-decoration-none p-0 me-2 js-select-all-sectors" style="font-size: 0.75rem;">Select All</button>
                <span class="badge bg-info bg-opacity-75 rounded-pill js-sector-count" style="font-size: 0.70rem;">0 Selected</span>
              </div>
            </label>
            <div class="location-container custom-scrollbar" id="sectors_container">
              <div class="text-center py-4 text-muted h-100 d-flex flex-column justify-content-center align-items-center" style="opacity: 0.6;">
                <i data-feather="map" class="mb-2" style="width: 24px; height: 24px;"></i>
                <span style="font-size: 0.85rem;">Select GE Groups first</span>
              </div>
            </div>
            @error('sector_id')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            <!-- Hidden field to ensure sector_id is always submitted even when disabled/empty -->
            <input type="hidden" id="sector_id_hidden" name="sector_id_hidden_val" value="{{ json_encode(old('sector_id', [])) }}">
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
<style>
    .readonly-select {
        pointer-events: none;
        background-color: #e9ecef;
        opacity: 1;
    }
    .location-container {
        height: 200px;
        overflow-y: auto;
        background: rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        padding: 10px;
    }
    .custom-checkbox-card {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.03);
        cursor: pointer;
        transition: all 0.2s ease;
        height: 100%;
        user-select: none;
    }
    .custom-checkbox-card:hover {
        background: rgba(59, 130, 246, 0.05);
        border-color: rgba(59, 130, 246, 0.2);
    }
    /* :has() is widely supported in modern browsers */
    .custom-checkbox-card:has(input:checked) {
        background: rgba(59, 130, 246, 0.15);
        border-color: #3b82f6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .custom-checkbox-card.checked {
        background: rgba(59, 130, 246, 0.15);
        border-color: #3b82f6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .custom-checkbox-card input[type="checkbox"] {
        width: 1.15rem;
        height: 1.15rem;
        cursor: pointer;
        margin-top: 0;
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.1);
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }
</style>
@endpush

@push('scripts')
<script>
  feather.replace();

  document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone');
    const cityCheckboxes = document.querySelectorAll('.city-checkbox');
    const sectorContainer = document.getElementById('sectors_container');
    const sectorHidden = document.getElementById('sector_id_hidden');
    const roleSelect = document.getElementById('role_id');
    const cityCountBadge = document.querySelector('.js-city-count');
    const sectorCountBadge = document.querySelector('.js-sector-count');
    const btnSelectAllCities = document.querySelector('.js-select-all-cities');
    const btnSelectAllSectors = document.querySelector('.js-select-all-sectors');
    const userForm = document.querySelector('form[action*="users"]');

    // Phone validation
    if (phoneInput) {
      phoneInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
      });
    }

    // Helper to update card styles for checked inputs
    function updateCardStyles() {
      document.querySelectorAll('.custom-checkbox-card').forEach(card => {
        const checkbox = card.querySelector('input[type="checkbox"]');
        if (checkbox) {
          if (checkbox.checked) {
            card.classList.add('checked');
          } else {
            card.classList.remove('checked');
          }
        }
      });
    }

    // Helper to update badge counts
    function updateCityCount() {
      const selectedCities = document.querySelectorAll('.city-checkbox:checked').length;
      if (cityCountBadge) cityCountBadge.textContent = selectedCities + ' Selected';
      updateCardStyles();
    }

    function updateSectorCount() {
      const selectedSectors = document.querySelectorAll('.sector-checkbox:checked').length;
      if (sectorCountBadge) sectorCountBadge.textContent = selectedSectors + ' Selected';
      updateCardStyles();
      syncSectorHidden();
    }

    // Sync sector hidden field
    function syncSectorHidden() {
      if (sectorHidden) {
        const checkedSectorBoxes = document.querySelectorAll('.sector-checkbox:checked');
        const values = Array.from(checkedSectorBoxes).map(cb => cb.value);
        sectorHidden.value = JSON.stringify(values);
      }
    }

    // Fetch sectors based on selected cities
    function fetchSectors() {
      const checkedCityBoxes = document.querySelectorAll('.city-checkbox:checked');
      const cityIds = Array.from(checkedCityBoxes).map(cb => cb.value);
      
      updateCityCount();
      
      if (cityIds.length === 0) {
        sectorContainer.innerHTML = `
          <div class="text-center py-4 text-muted h-100 d-flex flex-column justify-content-center align-items-center" style="opacity: 0.6;">
            <i data-feather="map" class="mb-2" style="width: 24px; height: 24px;"></i>
            <span style="font-size: 0.85rem;">Select GE Groups first</span>
          </div>
        `;
        feather.replace();
        sectorCountBadge.textContent = '0 Selected';
        syncSectorHidden();
        return;
      }

      sectorContainer.innerHTML = `
        <div class="text-center py-4 text-primary h-100 d-flex flex-column justify-content-center align-items-center">
          <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
          <span style="font-size: 0.85rem;">Loading nodes...</span>
        </div>
      `;
      
      fetch(`{{ route('admin.sectors.by-city') }}?city_id=${cityIds.join(',')}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        credentials: 'same-origin'
      })
      .then(response => response.json())
      .then(data => {
        if (data && data.length > 0) {
          let html = '<div class="row g-2">';
          const defaultSectorIds = {!! json_encode($defaultSectorIds ?? []) !!};
          
          data.forEach(sector => {
            const isChecked = defaultSectorIds && (defaultSectorIds.includes(parseInt(sector.id)) || defaultSectorIds.includes(sector.id.toString()));
            html += `
              <div class="col-sm-6">
                <label class="custom-checkbox-card w-100 mb-0">
                  <input class="form-check-input sector-checkbox ms-1" type="checkbox" name="sector_id[]" value="${sector.id}" ${isChecked ? 'checked' : ''}>
                  <div class="ms-2 text-truncate">
                    <span class="d-block text-white fw-medium" style="font-size: 0.9rem;">${sector.name}</span>
                  </div>
                </label>
              </div>
            `;
          });
          html += '</div>';
          sectorContainer.innerHTML = html;
          updateSectorCount();
          updateSelectAllButtons();
        } else {
          sectorContainer.innerHTML = `
            <div class="text-center py-4 text-warning h-100 d-flex flex-column justify-content-center align-items-center">
              <i data-feather="alert-circle" class="mb-2" style="width: 24px; height: 24px;"></i>
              <span style="font-size: 0.85rem;">No nodes found</span>
            </div>
          `;
          feather.replace();
          sectorCountBadge.textContent = '0 Selected';
          syncSectorHidden();
        }
      })
      .catch(error => {
        console.error('Error loading sectors:', error);
        sectorContainer.innerHTML = `
          <div class="text-center py-4 text-danger h-100 d-flex flex-column justify-content-center align-items-center">
            <i data-feather="x-circle" class="mb-2" style="width: 24px; height: 24px;"></i>
            <span style="font-size: 0.85rem;">Error loading nodes</span>
          </div>
        `;
        feather.replace();
      });
    }

    // Select All / Deselect All logic
    function updateSelectAllButtons() {
      if (btnSelectAllCities) {
        const boxes = document.querySelectorAll('.city-checkbox:not(:disabled)');
        const checked = document.querySelectorAll('.city-checkbox:checked:not(:disabled)');
        btnSelectAllCities.textContent = (boxes.length > 0 && boxes.length === checked.length) ? 'Deselect All' : 'Select All';
      }
      if (btnSelectAllSectors) {
        const boxes = document.querySelectorAll('.sector-checkbox:not(:disabled)');
        const checked = document.querySelectorAll('.sector-checkbox:checked:not(:disabled)');
        btnSelectAllSectors.textContent = (boxes.length > 0 && boxes.length === checked.length) ? 'Deselect All' : 'Select All';
      }
    }

    // Attach city change listeners
    cityCheckboxes.forEach(cb => cb.addEventListener('change', fetchSectors));

    // Delegation for sector checkboxes
    document.addEventListener('change', function(e) {
      if (e.target.classList.contains('sector-checkbox')) {
        updateSectorCount();
        updateSelectAllButtons();
      }
    });

    // Select All buttons click
    if (btnSelectAllCities) {
      btnSelectAllCities.addEventListener('click', function() {
        const isSelectAll = this.textContent === 'Select All';
        document.querySelectorAll('.city-checkbox:not(:disabled)').forEach(cb => cb.checked = isSelectAll);
        fetchSectors();
        updateSelectAllButtons();
      });
    }

    if (btnSelectAllSectors) {
      btnSelectAllSectors.addEventListener('click', function() {
        const isSelectAll = this.textContent === 'Select All';
        document.querySelectorAll('.sector-checkbox:not(:disabled)').forEach(cb => cb.checked = isSelectAll);
        updateSectorCount();
        updateSelectAllButtons();
      });
    }

    // Show validation error popup
    function showValidationAlert(errors) {
      // Remove any existing validation alert
      const existingAlert = document.getElementById('js-validation-alert');
      if (existingAlert) existingAlert.remove();

      const alertDiv = document.createElement('div');
      alertDiv.id = 'js-validation-alert';
      alertDiv.className = 'alert alert-danger alert-dismissible fade show mb-4';
      alertDiv.setAttribute('role', 'alert');
      alertDiv.style.cssText = 'background: rgba(220, 53, 69, 0.15); border: 1px solid rgba(220, 53, 69, 0.4); border-radius: 8px; animation: slideDown 0.3s ease;';
      
      let errorList = errors.map(err => `<li class="text-danger" style="font-size: 0.9rem;">${err}</li>`).join('');
      alertDiv.innerHTML = `
        <div class="d-flex align-items-start">
          <i data-feather="alert-circle" class="me-2 mt-1 flex-shrink-0" style="width: 20px; height: 20px; color: #ff6b6b;"></i>
          <div>
            <strong class="text-danger">Please fix the following errors:</strong>
            <ul class="mb-0 mt-2" style="padding-left: 1.2rem;">${errorList}</ul>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
      `;

      const cardBody = document.querySelector('.card-body');
      const form = document.getElementById('createUserForm');
      cardBody.insertBefore(alertDiv, form);
      feather.replace();
      alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Comprehensive form validation
    if (userForm) {
      userForm.addEventListener('submit', function(e) {
        const errors = [];
        
        // Username validation
        const usernameValue = document.getElementById('username').value.trim();
        if (!usernameValue) {
          errors.push('Username is required.');
        }

        // Role validation
        const roleValue = document.getElementById('role_id').value;
        if (!roleValue) {
          errors.push('Role is required. Please select a role.');
        }

        // Password validation
        const passwordValue = document.getElementById('password').value;
        const confirmPasswordValue = document.getElementById('password_confirmation').value;
        
        if (!passwordValue) {
          errors.push('Password is required.');
        } else if (passwordValue.length < 8) {
          errors.push('Password must be at least 8 characters long.');
        }
        
        if (passwordValue && !confirmPasswordValue) {
          errors.push('Please confirm your password.');
        } else if (passwordValue && confirmPasswordValue && passwordValue !== confirmPasswordValue) {
          errors.push('Password and Confirm Password do not match.');
        }

        // Phone validation
        const phoneValue = phoneInput ? phoneInput.value.trim() : '';
        if (phoneValue && phoneValue.length < 11) {
          errors.push('Phone number must be at least 11 digits.');
        }

        // GE Groups validation
        const checkedCities = document.querySelectorAll('.city-checkbox:checked');
        if (checkedCities.length === 0) {
          errors.push('Please select at least one GE Group.');
        }
        
        // GE Nodes validation
        const checkedSectors = document.querySelectorAll('.sector-checkbox:checked');
        if (checkedSectors.length === 0) {
          errors.push('Please select at least one GE Node.');
        }

        // If there are errors, prevent form submission and show alert
        if (errors.length > 0) {
          e.preventDefault();
          showValidationAlert(errors);
          alert("Validation Errors:\n\n- " + errors.join("\n- "));
          return false;
        }
      });
    }

    // Show server-side validation errors in popup if any
    @if($errors->any())
      const serverErrors = [];
      @foreach($errors->all() as $error)
        serverErrors.push("{!! addslashes($error) !!}");
      @endforeach
      alert("Validation Errors:\n\n- " + serverErrors.join("\n- "));
    @endif

    // Initial load
    fetchSectors();
    updateSelectAllButtons();
  });
</script>
@endpush
