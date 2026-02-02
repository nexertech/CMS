@extends('layouts.sidebar')

@section('title', 'Add New Device')

@section('content')
<div class="container-fluid">


    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.registered-devices.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="device_id" class="form-label">Device ID <span class="text-danger">*</span></label>
                        <input type="text" name="device_id" id="device_id" class="form-control @error('device_id') is-invalid @enderror" value="{{ old('device_id') }}" required>
                        @error('device_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="device_name" class="form-label">Device Name</label>
                        <input type="text" name="device_name" id="device_name" class="form-control" value="{{ old('device_name') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="city_id" class="form-label">GE Group</label>
                        <select name="city_id" id="city_id" class="form-select @error('city_id') is-invalid @enderror" {{ $defaultCityId ? 'readonly onclick="return false;"' : '' }}>
                            <option value="">Select GE Group</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id', $defaultCityId ?? '') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                         @error('city_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="sector_id" class="form-label">GE Node</label>
                        <select name="sector_id" id="sector_id" class="form-select @error('sector_id') is-invalid @enderror" {{ $defaultSectorId ? 'readonly onclick="return false;"' : 'disabled' }}>
                            <option value="">Select GE Group First</option>
                            @if(isset($sectors) && $sectors->count() > 0)
                                @foreach($sectors as $sector)
                                    <option value="{{ $sector->id }}" {{ old('sector_id', $defaultSectorId ?? '') == $sector->id ? 'selected' : '' }}>{{ $sector->name }}</option>
                                @endforeach
                            @endif
                        </select>
                         @error('sector_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="assigned_to_house_no" class="form-label">Assigned House No</label>
                        <select name="assigned_to_house_no" id="assigned_to_house_no" class="form-select @error('assigned_to_house_no') is-invalid @enderror" disabled>
                            <option value="">Select Sector First</option>
                        </select>
                        @error('assigned_to_house_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.registered-devices.index') }}" class="btn btn-secondary">
                        <i data-feather="x" class="me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" class="me-1"></i>Create Device
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const citySelect = document.getElementById('city_id');
        const sectorSelect = document.getElementById('sector_id');
        const houseSelect = document.getElementById('assigned_to_house_no');
        
        // Helper to reset a select
        function resetSelect(select, defaultText, disabled = true) {
            select.innerHTML = `<option value="">${defaultText}</option>`;
            select.disabled = disabled;
        }

        // Load Sectors
        function loadSectors(cityId, selectedSectorId = null) {
            if (!cityId) {
                resetSelect(sectorSelect, 'Select Sector');
                resetSelect(houseSelect, 'Select House');
                return;
            }

            sectorSelect.innerHTML = '<option value="">Loading...</option>';
            sectorSelect.disabled = true;

            fetch(`{{ route('admin.houses.sectors') }}?city_id=${cityId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                resetSelect(sectorSelect, 'Select Sector', false);
                if (data.sectors && data.sectors.length > 0) {
                    data.sectors.forEach(sector => {
                        const option = new Option(sector.name, sector.id);
                        if (selectedSectorId && String(sector.id) === String(selectedSectorId)) {
                            option.selected = true;
                        }
                        sectorSelect.add(option);
                    });
                    
                    // Trigger house load if sector is selected
                    if (selectedSectorId) {
                        loadHouses(selectedSectorId, '{{ old('assigned_to_house_no') }}');
                    }
                } else {
                    resetSelect(sectorSelect, 'No Sectors Available');
                }
            })
            .catch(err => {
                console.error(err);
                resetSelect(sectorSelect, 'Error loading sectors');
            });
        }

        // Load Houses
        function loadHouses(sectorId, selectedHouseNo = null) {
            if (!sectorId) {
                resetSelect(houseSelect, 'Select House');
                return;
            }

            houseSelect.innerHTML = '<option value="">Loading...</option>';
            houseSelect.disabled = true;

            fetch(`{{ route('admin.registered-devices.houses') }}?sector_id=${sectorId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                resetSelect(houseSelect, 'Select House', false);
                if (data.houses && data.houses.length > 0) {
                    data.houses.forEach(house => {
                        // Display: H-101 (Owner Name)
                        const label = house.house_no + (house.name ? ` (${house.name})` : '');
                        const option = new Option(label, house.house_no); // Value is house_no (string)
                        if (selectedHouseNo && String(house.house_no) === String(selectedHouseNo)) {
                            option.selected = true;
                        }
                        houseSelect.add(option);
                    });
                } else {
                    resetSelect(houseSelect, 'No Houses Available');
                }
            })
            .catch(err => {
                console.error(err);
                resetSelect(houseSelect, 'Error loading houses');
            });
        }

        // Event Listeners
        if (citySelect) {
            citySelect.addEventListener('change', function() {
                loadSectors(this.value);
                resetSelect(houseSelect, 'Select House');
            });
        }

        if (sectorSelect) {
            sectorSelect.addEventListener('change', function() {
                loadHouses(this.value);
            });
        }
        
        // Initial Load (if editing or old input exists)
        const oldSectorId = '{{ old('sector_id', $defaultSectorId ?? '') }}';
        const oldHouseNo = '{{ old('assigned_to_house_no') }}';
        
        if (citySelect && citySelect.value) {
            // Check if sectors are populated from backend
            if (sectorSelect.options.length > 1) {
                // Sectors already there, enable the dropdown
                sectorSelect.disabled = false;
                
                // If a sector is selected, load houses
                if (sectorSelect.value) {
                    loadHouses(sectorSelect.value, oldHouseNo);
                }
            } else {
                // Sectors not populated, load them via AJAX
                if (oldSectorId) {
                    loadSectors(citySelect.value, oldSectorId);
                }
            }
        }
    });
</script>
@endpush
