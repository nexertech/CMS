@extends('layouts.sidebar')

@section('title', 'Edit Complaint — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="text-white mb-2">Edit Complaint</h2>
            <p class="text-light">Modify complaint details</p>
        </div>
    </div>
</div>

<!-- COMPLAINT FORM -->
<div class="card-glass">
    <div class="card-body">
        <form action="{{ route('admin.complaints.update', $complaint) }}" method="POST">
            @csrf
            @method('PUT')

            @if(request('redirect_to'))
                <input type="hidden" name="redirect_to" value="{{ request('redirect_to') }}">
            @endif

            <!-- Complainant Information Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="text-white fw-bold mb-3"><i data-feather="user" class="me-2" style="width: 16px; height: 16px;"></i>Complainant Information</h6>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="city_id" class="form-label text-white">GE Groups</label>
                        <select class="form-select @error('city_id') is-invalid @enderror" id="city_id" name="city_id">
                            <option value="">Select GE Groups</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id', $complaint->city_id) == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}{{ $city->province ? ' (' . $city->province . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('city_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="sector_id" class="form-label text-white">GE Nodes</label>
                        <select class="form-select @error('sector_id') is-invalid @enderror" id="sector_id" name="sector_id" {{ $complaint->city_id ? '' : 'disabled' }}>
                            <option value="">Select GE Groups First</option>
                            @foreach($sectors as $sector)
                                <option value="{{ $sector->id }}" {{ old('sector_id', $complaint->sector_id) == $sector->id ? 'selected' : '' }}>
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('sector_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="house_id" class="form-label text-white">House Number <span class="text-danger">*</span></label>
                        <select class="form-select @error('house_id') is-invalid @enderror" id="house_id" name="house_id" required>
                            <option value="">Select House Number</option>
                            @foreach($houses as $house)
                                <option value="{{ $house->id }}" 
                                        data-city="{{ $house->city_id }}"
                                        data-sector="{{ $house->sector_id }}"
                                        data-name="{{ $house->name }}"
                                        data-phone="{{ $house->phone }}"
                                        {{ old('house_id', $complaint->house_id) == $house->id ? 'selected' : '' }}>
                                    {{ $house->house_no }}
                                </option>
                            @endforeach
                        </select>
                        @error('house_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="client_name" class="form-label text-white">Complainant Name</label>
                        <input type="text" class="form-control @error('client_name') is-invalid @enderror" id="client_name" name="client_name" value="{{ old('client_name', $complaint->client->client_name ?? '') }}" placeholder="Enter complainant name">
                        @error('client_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="address" class="form-label text-white">Address</label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror" id="client_address" name="address" value="{{ old('address', $complaint->client->address ?? '') }}" placeholder="Enter address">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="phone" class="form-label text-white">Phone No.</label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="client_phone" name="phone" value="{{ old('phone', $complaint->client->phone ?? '') }}" placeholder="Enter phone number">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Complaint Details Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="text-white fw-bold mb-3"><i data-feather="alert-triangle" class="me-2" style="width: 16px; height: 16px;"></i>Complaint Nature & Type</h6>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="category" class="form-label text-white">Category <span class="text-danger">*</span></label>
                        <select id="category" name="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}" {{ old('category', $complaint->category_id) == $id ? 'selected' : '' }}>{{ ucfirst($name) }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="complaint_title_id" class="form-label text-white">Complaint Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('complaint_title_id') is-invalid @enderror" 
                                id="title" name="complaint_title_id" required 
                                data-prev="{{ old('complaint_title_id', $complaint->complaint_title_id) }}"
                                data-custom="{{ old('title_other', $complaint->title) }}">
                            <option value="">Select Category First</option>
                        </select>
                        <input type="text" class="form-control mt-2 @error('title_other') is-invalid @enderror"
                                id="title_other" name="title_other" placeholder="Enter custom title..."
                                style="display: none;" value="{{ old('title_other', $complaint->title) }}">
                        @error('complaint_title_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="priority" class="form-label text-white">Priority <span class="text-danger">*</span></label>
                        <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                            <option value="">Select Priority</option>
                            <option value="low" {{ old('priority', $complaint->priority) == 'low' ? 'selected' : '' }}>Low - Can wait</option>
                            <option value="medium" {{ old('priority', $complaint->priority) == 'medium' ? 'selected' : '' }}>Medium - Normal</option>
                            <option value="high" {{ old('priority', $complaint->priority) == 'high' ? 'selected' : '' }}>High - Important</option>
                            <option value="urgent" {{ old('priority', $complaint->priority) == 'urgent' ? 'selected' : '' }}>Urgent - Critical</option>
                            <option value="emergency" {{ old('priority', $complaint->priority) == 'emergency' ? 'selected' : '' }}>Emergency</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="availability_time" class="form-label text-white">Availability Time</label>
                        <input type="datetime-local" class="form-control @error('availability_time') is-invalid @enderror"
                                id="availability_time" name="availability_time" value="{{ old('availability_time', $complaint->availability_time ? date('Y-m-d\TH:i', strtotime($complaint->availability_time)) : '') }}">
                        @error('availability_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="assigned_employee_id" class="form-label text-white">Assign Employee <span class="text-danger">*</span></label>
                        <select class="form-select @error('assigned_employee_id') is-invalid @enderror" id="assigned_employee_id" name="assigned_employee_id" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" 
                                        data-category="{{ $employee->category_id }}"
                                        data-city="{{ $employee->city_id }}"
                                        data-sector="{{ $employee->sector_id }}"
                                        {{ old('assigned_employee_id', $complaint->assigned_employee_id) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}@if($employee->designation) ({{ $employee->designation->name }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
                        <label for="description" class="form-label text-white">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $complaint->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.complaints.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Complaint</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const titleSelect = document.getElementById('title');
    const titleOtherInput = document.getElementById('title_other');
    const citySelect = document.getElementById('city_id');
    const sectorSelect = document.getElementById('sector_id');
    const houseSelect = document.getElementById('house_id');
    const employeeSelect = document.getElementById('assigned_employee_id');

    function handleTitleChange() {
        if (titleSelect.value === 'other') {
            titleOtherInput.style.display = 'block';
            titleOtherInput.required = true;
        } else {
            titleOtherInput.style.display = 'none';
            titleOtherInput.required = false;
        }
    }

    if (titleSelect) {
        titleSelect.addEventListener('change', handleTitleChange);
    }

    function filterEmployees() {
        if (!employeeSelect) return;
        const category = categorySelect ? categorySelect.value : '';
        const cityId = citySelect ? citySelect.value : '';
        const sectorId = sectorSelect ? sectorSelect.value : '';
        
        const currentSelectedId = employeeSelect.value;
        let currentlySelectedIsHidden = false;

        Array.from(employeeSelect.options).forEach(opt => {
            if (!opt.value) return; 
            const optCategory = opt.getAttribute('data-category') || '';
            const optCity = opt.getAttribute('data-city') || '';
            const optSector = opt.getAttribute('data-sector') || '';
            
            const matchCategory = !category || optCategory === category;
            const matchCity = !cityId || String(optCity) === String(cityId);
            const matchSector = !sectorId || String(optSector) === String(sectorId);
            
            const show = matchCategory && matchCity && matchSector;
            
            opt.hidden = !show;
            opt.style.display = show ? '' : 'none';
            opt.disabled = !show;

            if (!show && opt.value === currentSelectedId) {
                currentlySelectedIsHidden = true;
            }
        });

        if (currentlySelectedIsHidden) {
            employeeSelect.value = '';
        }
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            filterEmployees();
            const category = this.value;
            if (!category) {
                titleSelect.innerHTML = '<option value="">Select Category First</option>';
                return;
            }

            titleSelect.innerHTML = '<option value="">Loading titles...</option>';
            titleSelect.disabled = true;

            fetch(`{{ route('admin.complaint-titles.by-category') }}?category=${encodeURIComponent(category)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                titleSelect.innerHTML = '<option value="">Select Complaint Type</option>';
                if (data && data.length > 0) {
                    data.sort((a, b) => (a.title || '').toLowerCase().localeCompare((b.title || '').toLowerCase(), undefined, { numeric: true }))
                        .forEach(title => {
                            const option = document.createElement('option');
                            option.value = title.id;
                            option.textContent = title.title;
                            titleSelect.appendChild(option);
                        });
                }
                
                const otherOption = document.createElement('option');
                otherOption.value = 'other';
                otherOption.textContent = 'Other';
                titleSelect.appendChild(otherOption);
                
                titleSelect.disabled = false;
                
                const previous = titleSelect.getAttribute('data-prev');
                const custom = titleSelect.getAttribute('data-custom');
                
                if (previous) {
                    const opt = Array.from(titleSelect.options).find(o => o.value == previous);
                    if (opt) {
                        titleSelect.value = (String)(previous);
                        handleTitleChange();
                    }
                } else if (custom && custom !== 'null' && custom !== '') {
                    titleSelect.value = 'other';
                    handleTitleChange();
                    titleOtherInput.value = custom;
                }
            });
        });

        // Trigger change on load if category is selected
        if (categorySelect.value) {
            categorySelect.dispatchEvent(new Event('change'));
        }
    }

    if (citySelect) {
        citySelect.addEventListener('change', function() {
            filterEmployees();
            const cityId = this.value;
            if (!cityId) {
                sectorSelect.innerHTML = '<option value="">Select GE Groups First</option>';
                sectorSelect.disabled = true;
                return;
            }

            sectorSelect.innerHTML = '<option value="">Loading GE Nodes...</option>';
            sectorSelect.disabled = true;

            fetch(`{{ route('admin.sectors.by-city') }}?city_id=${cityId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                sectorSelect.innerHTML = '<option value="">Select GE Nodes</option>';
                data.forEach(sector => {
                    const option = document.createElement('option');
                    option.value = sector.id;
                    option.textContent = sector.name;
                    sectorSelect.appendChild(option);
                });
                sectorSelect.disabled = false;
            });
        });
    }

    if (sectorSelect) {
        sectorSelect.addEventListener('change', function() {
            filterEmployees();
        });
    }

    if (houseSelect) {
        houseSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                document.getElementById('client_name').value = option.getAttribute('data-name') || '';
                document.getElementById('client_phone').value = option.getAttribute('data-phone') || '';
                // Trigger location updates if needed
            }
        });
    }

    // Initial filter
    filterEmployees();
    
    feather.replace();
});
</script>
@endpush
