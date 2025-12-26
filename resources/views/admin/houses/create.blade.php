@extends('layouts.sidebar')

@section('title', 'Add House — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Add New House</h2>
      <p class="text-light">Create a new house record</p>
    </div>
  </div>
</div>

<!-- HOUSE FORM -->
<div class="card-glass">
  <form action="{{ route('admin.houses.store') }}" method="POST" autocomplete="off" id="houseForm">
    @csrf
    
    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <label for="username" class="form-label text-white">Username <span class="text-danger">*</span></label>
          <input type="text" class="form-control @error('username') is-invalid @enderror" 
                 id="username" name="username" value="{{ old('username') }}" autocomplete="off" required>
          @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label for="password" class="form-label text-white">Password <span class="text-danger">*</span></label>
          <input type="password" class="form-control @error('password') is-invalid @enderror" 
                 id="password" name="password" autocomplete="new-password" required minlength="8" placeholder="Minimum 8 characters">
          
          @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <label for="name" class="form-label text-white">Name</label>
          <input type="text" class="form-control @error('name') is-invalid @enderror" 
                 id="name" name="name" value="{{ old('name') }}" autocomplete="off" placeholder="Owner/Resident Name">
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="col-md-6">
        <div class="mb-3">
          <label for="phone" class="form-label text-white">Phone</label>
          <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                 id="phone" name="phone" value="{{ old('phone') }}" autocomplete="off" placeholder="Phone Number">
          @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="mb-3">
          <label for="city_id" class="form-label text-white">GE Group <span class="text-danger">*</span></label>
          <select class="form-select @error('city_id') is-invalid @enderror" 
                  id="city_id" name="city_id" required>
            <option value="">Select GE Group</option>
            @if(isset($cities) && $cities->count() > 0)
              @foreach ($cities as $city)
                <option value="{{ $city->id }}" {{ old('city_id', $defaultCityId) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
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
          <label for="sector_id" class="form-label text-white">GE Node <span class="text-danger">*</span></label>
          <select class="form-select @error('sector_id') is-invalid @enderror" 
                  id="sector_id" name="sector_id" disabled required>
            <option value="">Select GE Group First</option>
          </select>
          @error('sector_id')
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
          <label for="address" class="form-label text-white">Address</label>
          <input type="text" class="form-control @error('address') is-invalid @enderror" 
                 id="address" name="address" value="{{ old('address') }}" placeholder="e.g., 00-ST0-B0">
          @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
    
    <div class="d-flex justify-content-end gap-2 mt-4">
      <a href="{{ route('admin.houses.index') }}" class="btn btn-outline-secondary">
        <i data-feather="x" class="me-2"></i>Cancel
      </a>
      <button type="submit" class="btn btn-accent">
        <i data-feather="save" class="me-2"></i>Create House
      </button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
    feather.replace();
    
    document.addEventListener('DOMContentLoaded', function() {
        const citySelect = document.getElementById('city_id');
        const sectorSelect = document.getElementById('sector_id');
        const addressInput = document.getElementById('address');
        
        function loadSectors(cityId, targetSectorId = null) {
            if (!sectorSelect) return;
            
            if (!cityId) {
                sectorSelect.innerHTML = '<option value="">Select GE Group First</option>';
                sectorSelect.disabled = true;
                return;
            }
            
            sectorSelect.innerHTML = '<option value="">Loading...</option>';
            sectorSelect.disabled = true;
            
            fetch(`{{ route('admin.houses.sectors') }}?city_id=${cityId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                sectorSelect.innerHTML = '<option value="">Select GE Node</option>';
                
                if (data.sectors && data.sectors.length > 0) {
                    data.sectors.forEach(function(sector) {
                        const option = document.createElement('option');
                        option.value = sector.id;
                        option.textContent = sector.name;
                        if (targetSectorId && String(sector.id) === String(targetSectorId)) {
                            option.selected = true;
                        }
                        sectorSelect.appendChild(option);
                    });
                    sectorSelect.disabled = false;
                    sectorSelect.required = true;
                } else {
                    sectorSelect.innerHTML = '<option value="">No GE Nodes Available</option>';
                    sectorSelect.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error fetching GE Nodes:', error);
                sectorSelect.innerHTML = '<option value="">Error Loading GE Nodes</option>';
                sectorSelect.disabled = false;
            });
        }
        
        if (citySelect) {
            citySelect.addEventListener('change', function() {
                loadSectors(this.value);
            });
            
            // Initial call if city is pre-selected
            if (citySelect.value) {
                const oldSectorId = '{{ old('sector_id', $defaultSectorId) }}';
                loadSectors(citySelect.value, oldSectorId);
            }
        }
        
        // Auto-replace space with slash in address field
        if (addressInput) {
            addressInput.addEventListener('keydown', function(e) {
                if (e.key === ' ' || e.keyCode === 32) {
                    e.preventDefault();
                    const cursorPos = this.selectionStart;
                    const currentValue = this.value;
                    const newValue = currentValue.substring(0, cursorPos) + '-' + currentValue.substring(cursorPos);
                    this.value = newValue;
                    this.setSelectionRange(cursorPos + 1, cursorPos + 1);
                }
            });
            
            addressInput.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                const replacedText = pastedText.replace(/\s+/g, '-');
                const cursorPos = this.selectionStart;
                const currentValue = this.value;
                const newValue = currentValue.substring(0, cursorPos) + replacedText + currentValue.substring(this.selectionEnd);
                this.value = newValue;
                this.setSelectionRange(cursorPos + replacedText.length, cursorPos + replacedText.length);
            });
        }
    });
</script>
@endpush
