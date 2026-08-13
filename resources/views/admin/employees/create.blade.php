@extends('layouts.sidebar')

@section('title', 'Add Employee — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Add New Employee</h2>
      <p class="text-light">Create a new employee record</p>
    </div>
   
  </div>
</div>

<!-- EMPLOYEE FORM -->
<div class="card-glass">
  <form action="{{ route('admin.employees.store') }}" method="POST" autocomplete="off" id="employeeForm" onsubmit="return validateEmployeeForm()">
    @csrf
    
    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <label for="name" class="form-label text-white">Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control @error('name') is-invalid @enderror" 
                 id="name" name="name" value="{{ old('name') }}" autocomplete="off" required>
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label for="phone" class="form-label text-white">Phone</label>
          <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                 id="phone" name="phone" value="{{ old('phone') }}" 
                 maxlength="11" minlength="11" pattern="[0-9]{11}" placeholder="03001234567" inputmode="numeric" 
                 oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)">
          @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <label for="category" class="form-label text-white">Category <span class="text-danger">*</span></label>
          <select class="form-select @error('category_id') is-invalid @enderror" 
                  id="category" name="category_id" required>
            <option value="">Select Category</option>
            @if(isset($categories) && $categories->count() > 0)
              @foreach ($categories as $id => $cat)
                <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
              @endforeach
            @endif
          </select>
          @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label for="designation" class="form-label text-white">Designation <span class="text-danger">*</span></label>
          <select class="form-select @error('designation_id') is-invalid @enderror" 
                  id="designation" name="designation_id" disabled required>
            <option value="">Select Category First</option>
          </select>
          @error('designation_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label for="city_id" class="form-label text-white">GE Groups <span class="text-danger">*</span></label>
          <select class="form-select @error('city_id') is-invalid @enderror" 
                  id="city_id" name="city_id" required>
            <option value="">Select GE Groups</option>
            @if(isset($cities) && $cities->count() > 0)
              @foreach ($cities as $city)
                <option value="{{ $city->id }}" data-id="{{ $city->id }}" data-province="{{ $city->province ?? '' }}" {{ (old('city_id') == $city->id || (isset($defaultCityId) && $defaultCityId == $city->id)) ? 'selected' : '' }}>{{ $city->name }}{{ $city->province ? ' (' . $city->province . ')' : '' }}</option>
              @endforeach
            @endif
          </select>
          @error('city_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label class="form-label text-white">GE Nodes <span class="text-danger">*</span></label>
          <div class="dropdown" id="sectorDropdownContainer">
            <button class="form-select text-start text-white d-flex justify-content-between align-items-center @error('sector_ids') is-invalid @enderror @error('sector_id') is-invalid @enderror" 
                    type="button" 
                    id="sectorDropdownBtn" 
                    data-bs-toggle="dropdown" 
                    data-bs-auto-close="outside"
                    aria-expanded="false" 
                    disabled>
              <span id="sectorDropdownText" class="text-truncate me-2">Select GE Groups First</span>
            </button>
            <div class="dropdown-menu p-3 w-100 shadow-lg" id="sectors_dropdown_menu" aria-labelledby="sectorDropdownBtn" style="max-height: 220px; overflow-y: auto; background: #1e293b; border: 1px solid rgba(59, 130, 246, 0.4); border-radius: 8px;">
              <span class="text-muted small">Select GE Groups First</span>
            </div>
          </div>
          @error('sector_ids')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
          @error('sector_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <label for="date_of_hire" class="form-label text-white">Date of Hire</label>
          <input type="date" class="form-control @error('date_of_hire') is-invalid @enderror" 
                 id="date_of_hire" name="date_of_hire" value="{{ old('date_of_hire') }}">
          @error('date_of_hire')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label for="status" class="form-label text-white">Status</label>
          <select class="form-select @error('status') is-invalid @enderror" 
                  id="status" name="status">
            @php
              $currStatus = old('status', 1);
              $isActive = ($currStatus == 1 || $currStatus === '1' || $currStatus === 'active' || $currStatus === true);
            @endphp
            <option value="1" {{ $isActive ? 'selected' : '' }}>Active</option>
            <option value="0" {{ !$isActive ? 'selected' : '' }}>Inactive</option>
          </select>
          @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="mb-3">
          <label for="address" class="form-label text-white">Address</label>
          <textarea class="form-control @error('address') is-invalid @enderror" 
                    id="address" name="address" rows="3">{{ old('address') }}</textarea>
          @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
    
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-accent">
        <i data-feather="save" class="me-2"></i>Create Employee
      </button>
      <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
        <i data-feather="x" class="me-2"></i>Cancel
      </a>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  feather.replace();
  
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
    const employeeForm = document.getElementById('employeeForm');
    if (employeeForm) {
      employeeForm.addEventListener('submit', function(e) {
        const phoneValue = phoneInput ? phoneInput.value.trim() : '';
        if (phoneValue && phoneValue.length < 11) {
          e.preventDefault();
          alert('Phone number must be at least 11 digits.');
          if (phoneInput) phoneInput.focus();
          return false;
        }
      });
    }
    
    const categorySelect = document.getElementById('category');
    const designationSelect = document.getElementById('designation');
    const citySelect = document.getElementById('city_id');
    const sectorDropdownBtn = document.getElementById('sectorDropdownBtn');
    const sectorDropdownText = document.getElementById('sectorDropdownText');
    const sectorsDropdownMenu = document.getElementById('sectors_dropdown_menu');
    
    // Update button display text based on selected checkboxes
    function updateDropdownButtonText() {
      const checkedBoxes = document.querySelectorAll('input[name="sector_ids[]"]:checked');
      if (!sectorDropdownText) return;
      
      if (checkedBoxes.length === 0) {
        sectorDropdownText.textContent = 'Select GE Nodes';
      } else if (checkedBoxes.length === 1) {
        const label = checkedBoxes[0].nextElementSibling;
        sectorDropdownText.textContent = label ? label.textContent : '1 GE Node Selected';
      } else {
        const labels = Array.from(checkedBoxes).map(cb => cb.nextElementSibling ? cb.nextElementSibling.textContent : '').filter(Boolean);
        sectorDropdownText.textContent = `${checkedBoxes.length} GE Nodes Selected (${labels.join(', ')})`;
      }
    }

    // Handle category change to load designations
    if (categorySelect && designationSelect) {
      categorySelect.addEventListener('change', function() {
        const category = this.value;
        const designationSelectEl = document.getElementById('designation');
        
        if (!designationSelectEl) {
          console.error('Designation select element not found');
          return;
        }
        
        designationSelectEl.innerHTML = '<option value="">Loading...</option>';
        designationSelectEl.disabled = true;
        
        if (category) {
          fetch(`{{ route('admin.employees.designations') }}?category_id=${encodeURIComponent(category)}`, {
            method: 'GET',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
            },
            credentials: 'same-origin'
          })
          .then(response => {
            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
          })
          .then(data => {
            designationSelectEl.innerHTML = '<option value="">Select Designation</option>';
            
            if (data.designations && data.designations.length > 0) {
              data.designations.forEach(function(designation) {
                const option = document.createElement('option');
                option.value = designation.id;
                option.textContent = designation.name;
                designationSelectEl.appendChild(option);
              });
              designationSelectEl.disabled = false;
              designationSelectEl.required = true;
            } else {
              designationSelectEl.innerHTML = '<option value="">No Designation Available</option>';
              designationSelectEl.disabled = true;
              designationSelectEl.required = false;
            }
          })
          .catch(error => {
            console.error('Error fetching designations:', error);
            designationSelectEl.innerHTML = '<option value="">Error Loading Designations</option>';
            designationSelectEl.disabled = true;
            designationSelectEl.required = false;
          });
        } else {
          designationSelectEl.innerHTML = '<option value="">Select Category First</option>';
          designationSelectEl.disabled = true;
          designationSelectEl.required = false;
        }
      });
    }
    
    // Handle city change to load sectors (GE Nodes) into dropdown menu
    if (citySelect && sectorsDropdownMenu) {
        
      function loadSectors(cityId, targetSectorIds = []) {
          let actualCityId = cityId;
          
          if (this instanceof Element) {
             const selectedOption = this.options[this.selectedIndex];
             const cityIdFromData = selectedOption ? selectedOption.getAttribute('data-id') : null;
             actualCityId = cityIdFromData || this.value;
          } else if (citySelect) {
             for (let i = 0; i < citySelect.options.length; i++) {
                 if (citySelect.options[i].value == cityId) {
                     const cityIdFromData = citySelect.options[i].getAttribute('data-id');
                     if (cityIdFromData) actualCityId = cityIdFromData;
                     break;
                 }
             }
          }
          
          console.log('City selected/loaded:', actualCityId);
          sectorsDropdownMenu.innerHTML = '<span class="text-muted small"><i class="spinner-border spinner-border-sm me-1"></i>Loading GE Nodes...</span>';
          if (sectorDropdownText) sectorDropdownText.textContent = 'Loading GE Nodes...';
          if (sectorDropdownBtn) sectorDropdownBtn.disabled = true;
          
          if (actualCityId) {
            const url = `{{ route('admin.employees.sectors') }}?city_id=${actualCityId}`;
            
            fetch(url, {
              method: 'GET',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
              }
            })
            .then(response => {
              if (!response.ok) {
                throw new Error('Network response was not ok');
              }
              return response.json();
            })
            .then(data => {
              sectorsDropdownMenu.innerHTML = '';
              
              if (data.sectors && data.sectors.length > 0) {
                const isSingleNode = (data.sectors.length === 1);
                
                data.sectors.forEach(function(sector) {
                  const wrapper = document.createElement('div');
                  wrapper.className = 'form-check mb-2';

                  const checkbox = document.createElement('input');
                  checkbox.type = 'checkbox';
                  checkbox.className = 'form-check-input sector-checkbox';
                  checkbox.name = 'sector_ids[]';
                  checkbox.value = sector.id;
                  checkbox.id = 'sector_node_' + sector.id;
                  
                  // Auto-check if only 1 node is available OR if included in targetSectorIds
                  if (isSingleNode || (targetSectorIds && targetSectorIds.map(String).includes(String(sector.id)))) {
                      checkbox.checked = true;
                  }

                  checkbox.addEventListener('change', updateDropdownButtonText);

                  const label = document.createElement('label');
                  label.className = 'form-check-label text-white ms-1 cursor-pointer';
                  label.htmlFor = 'sector_node_' + sector.id;
                  label.textContent = sector.name;

                  wrapper.appendChild(checkbox);
                  wrapper.appendChild(label);
                  sectorsDropdownMenu.appendChild(wrapper);
                });

                if (sectorDropdownBtn) sectorDropdownBtn.disabled = false;
                updateDropdownButtonText();
              } else {
                sectorsDropdownMenu.innerHTML = '<span class="text-muted small">No GE Nodes Available</span>';
                if (sectorDropdownText) sectorDropdownText.textContent = 'No GE Nodes Available';
                if (sectorDropdownBtn) sectorDropdownBtn.disabled = true;
              }
            })
            .catch(error => {
              console.error('Error fetching GE Nodes:', error);
              sectorsDropdownMenu.innerHTML = '<span class="text-danger small">Error Loading GE Nodes</span>';
              if (sectorDropdownText) sectorDropdownText.textContent = 'Error Loading GE Nodes';
              if (sectorDropdownBtn) sectorDropdownBtn.disabled = true;
            });
          } else {
            sectorsDropdownMenu.innerHTML = '<span class="text-muted small">Select GE Groups First</span>';
            if (sectorDropdownText) sectorDropdownText.textContent = 'Select GE Groups First';
            if (sectorDropdownBtn) sectorDropdownBtn.disabled = true;
          }
      }

      citySelect.addEventListener('change', function() {
          loadSectors.call(this, this.value);
      });
      
      // Initial load if city is pre-selected
      if (citySelect.value) {
          const defaultSectorId = '{{ isset($defaultSectorId) ? $defaultSectorId : old('sector_id') }}';
          const initialSectorIds = defaultSectorId ? [defaultSectorId] : [];
          loadSectors(citySelect.value, initialSectorIds);
      }
    }
    
    // Form validation before submit
    window.validateEmployeeForm = function() {
      const citySelect = document.getElementById('city_id');
      const designationSelect = document.getElementById('designation');
      const checkedSectors = document.querySelectorAll('input[name="sector_ids[]"]:checked');
      
      // Check if city is selected
      if (!citySelect || !citySelect.value) {
        alert('Please select GE Groups');
        if (citySelect) citySelect.focus();
        return false;
      }
      
      // Check if at least one sector checkbox is selected
      if (checkedSectors.length === 0) {
        alert('Please select at least one GE Node checkbox from the dropdown.');
        if (sectorDropdownBtn) sectorDropdownBtn.focus();
        return false;
      }
      
      // Check if designation is selected
      if (!designationSelect || !designationSelect.value) {
        alert('Please select Designation');
        if (designationSelect) designationSelect.focus();
        return false;
      }
      
      return true;
    };
  });
</script>
@endpush
