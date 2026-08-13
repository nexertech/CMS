@extends('layouts.sidebar')

@section('title', 'Edit Employee — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Edit Employee</h2>
      <p class="text-light">Update employee information</p>
    </div>
  </div>
</div>

<!-- EMPLOYEE FORM -->
<div class="card-glass">
  <form action="{{ route('admin.employees.update', $employee) }}" method="POST" autocomplete="off" id="employeeForm" onsubmit="return validateEmployeeForm()">
    @csrf
    @method('PUT')
    
    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <label for="name" class="form-label text-white">Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control @error('name') is-invalid @enderror" 
                 id="name" name="name" value="{{ old('name', $employee->name) }}" required>
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label for="phone" class="form-label text-white">Phone</label>
          <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                 id="phone" name="phone" value="{{ old('phone', $employee->phone) }}" 
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
                <option value="{{ $id }}" {{ old('category_id', $employee->category_id) == $id ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
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
                  id="designation" name="designation_id" {{ old('category_id', $employee->category_id) ? '' : 'disabled' }} required>
            <option value="">{{ old('category_id', $employee->category_id) ? 'Loading...' : 'Select Category First' }}</option>
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
                <option value="{{ $city->id }}" data-id="{{ $city->id }}" data-province="{{ $city->province ?? '' }}" {{ old('city_id', $employee->city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}{{ $city->province ? ' (' . $city->province . ')' : '' }}</option>
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
                 id="date_of_hire" name="date_of_hire" value="{{ old('date_of_hire', $employee->date_of_hire ? $employee->date_of_hire->format('Y-m-d') : '') }}">
          @error('date_of_hire')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label for="status" class="form-label text-white">Status <span class="text-danger">*</span></label>
          <select class="form-select @error('status') is-invalid @enderror" 
                  id="status" name="status" required>
            <option value="1" {{ old('status', $employee->status) == 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ old('status', $employee->status) == 0 ? 'selected' : '' }}>Inactive</option>
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
                    id="address" name="address" rows="3">{{ old('address', $employee->address) }}</textarea>
          @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
    
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-accent">
        <i data-feather="save" class="me-2"></i>Update Employee
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
    // Phone number input validation
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
      phoneInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
      });
    }
    
    const categorySelect = document.getElementById('category');
    const designationSelect = document.getElementById('designation');
    const citySelect = document.getElementById('city_id');
    const sectorDropdownBtn = document.getElementById('sectorDropdownBtn');
    const sectorDropdownText = document.getElementById('sectorDropdownText');
    const sectorsDropdownMenu = document.getElementById('sectors_dropdown_menu');
    const currentCategory = '{{ old('category_id', $employee->category_id) }}';
    const currentDesignation = '{{ old('designation_id', $employee->designation_id) }}';
    const currentCity = '{{ old('city_id', $employee->city_id) }}';
    const assignedSectorIds = @json(old('sector_ids', $employee->sectors ? $employee->sectors->pluck('id')->toArray() : []));
    
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
        sectorDropdownText.textContent = `${checkedBoxes.length} GE Nodes Selected`;
      }
    }

    // Function to load designations
    function loadDesignations(categoryId, targetDesignationId = null) {
      if (!categoryId || !designationSelect) return;
      fetch(`{{ route('admin.employees.designations') }}?category_id=${encodeURIComponent(categoryId)}`)
      .then(response => response.json())
      .then(data => {
        designationSelect.innerHTML = '<option value="">Select Designation</option>';
        data.designations.forEach(function(designation) {
          const option = document.createElement('option');
          option.value = designation.id;
          option.textContent = designation.name;
          if (targetDesignationId && String(designation.id) === String(targetDesignationId)) option.selected = true;
          designationSelect.appendChild(option);
        });
        designationSelect.disabled = false;
      });
    }
    
    if (currentCategory && categorySelect) loadDesignations(currentCategory, currentDesignation);
    
    if (categorySelect) {
      categorySelect.addEventListener('change', function() {
        if (this.value) loadDesignations(this.value);
      });
    }
    
    // Function to load GE Nodes
    function loadSectors(cityId, targetSectorIds = assignedSectorIds) {
      let actualCityId = cityId;
      if (this instanceof Element) {
         const selectedOption = this.options[this.selectedIndex];
         actualCityId = selectedOption ? selectedOption.getAttribute('data-id') : null;
      }
      
      if (!actualCityId || !sectorsDropdownMenu) return;
      
      sectorsDropdownMenu.innerHTML = '<span class="text-muted small">Loading GE Nodes...</span>';
      if (sectorDropdownText) sectorDropdownText.textContent = 'Loading...';
      if (sectorDropdownBtn) sectorDropdownBtn.disabled = true;

      fetch(`{{ route('admin.employees.sectors') }}?city_id=${actualCityId}`)
      .then(response => response.json())
      .then(data => {
        sectorsDropdownMenu.innerHTML = '';
        if (data.sectors && data.sectors.length > 0) {
          data.sectors.forEach(function(sector) {
            const wrapper = document.createElement('div');
            wrapper.className = 'form-check mb-2';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'form-check-input';
            checkbox.name = 'sector_ids[]';
            checkbox.value = sector.id;
            checkbox.id = 'sector_node_' + sector.id;
            if (targetSectorIds.map(String).includes(String(sector.id))) checkbox.checked = true;
            checkbox.addEventListener('change', updateDropdownButtonText);
            const label = document.createElement('label');
            label.className = 'form-check-label text-white ms-1';
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
        }
      });
    }

    if (currentCity && citySelect) loadSectors(currentCity, assignedSectorIds);
    
    if (citySelect) {
      citySelect.addEventListener('change', function() {
        loadSectors.call(this, this.value, []);
      });
    }
    
    window.validateEmployeeForm = function() {
      const citySelect = document.getElementById('city_id');
      const designationSelect = document.getElementById('designation');
      const checkedSectors = document.querySelectorAll('input[name="sector_ids[]"]:checked');
      if (!citySelect.value) { alert('Select GE Group'); return false; }
      if (checkedSectors.length === 0) { alert('Select at least one GE Node.'); return false; }
      if (!designationSelect.value) { alert('Select Designation'); return false; }
      return true;
    };
  });
</script>
@endpush
