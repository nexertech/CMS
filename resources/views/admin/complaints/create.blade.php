@extends('layouts.sidebar')

@section('title', 'Create New Complaint — CMS Admin')

@section('content')
    <!-- Flash Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Validation Errors:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- PAGE HEADER -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="text-white mb-2">Create New Complaint</h2>
                <p class="text-light">Add a new complaint to the system</p>
            </div>
        </div>

        <!-- COMPLAINT FORM -->
        <div class="card-glass">
            <div class="card-header">
                <h5 class="card-title mb-0 text-white">

                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.complaints.store') }}" method="POST" autocomplete="off" novalidate>
                    @csrf

                    <!-- Hidden dummy fields to prevent browser autofill -->
                    <div style="display: none;">
                        <input type="text" name="fake_title" autocomplete="off">
                        <input type="text" name="fake_description" autocomplete="off">
                    </div>

                    <!-- Complainant Information Section (matching index file columns) -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-white fw-bold mb-3"><i data-feather="user" class="me-2"
                                    style="width: 16px; height: 16px;"></i>Complainant Information</h6>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="city_id" class="form-label text-white">GE Groups</label>
                                <select class="form-select @error('city_id') is-invalid @enderror" id="city_id"
                                    name="city_id">
                                    <option value="">Select GE Groups</option>
                                    @if (isset($cities) && $cities->count() > 0)
                                        @foreach ($cities as $city)
                                            <option value="{{ $city->id }}"
                                                {{ old('city_id', $defaultCityId ?? null) == $city->id ? 'selected' : '' }}>
                                                {{ $city->name }}{{ $city->province ? ' (' . $city->province . ')' : '' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('city_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="sector_id" class="form-label text-white">GE Nodes</label>
                                <select class="form-select @error('sector_id') is-invalid @enderror" id="sector_id"
                                    name="sector_id" {{ old('city_id', $defaultCityId ?? null) ? '' : 'disabled' }}>
                                    <option value="">
                                        {{ old('city_id', $defaultCityId ?? null) ? 'Loading GE Nodes...' : 'Select GE Groups First' }}
                                    </option>
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
                                            data-address="{{ $house->address }}"
                                            {{ old('house_id') == $house->id ? 'selected' : '' }}>
                                            {{ $house->username }}
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
                                <input type="text" class="form-control @error('client_name') is-invalid @enderror"
                                    id="client_name" name="client_name" value="{{ old('client_name') }}"
                                    placeholder="Enter complainant name">
                                @error('client_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="address" class="form-label text-white">Address</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror"
                                    id="address" name="address" value="{{ old('address') }}"
                                    placeholder="e.g., 00-ST0-B0">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="phone" class="form-label text-white">Phone No.</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" value="{{ old('phone') }}"
                                    placeholder="Enter phone number"
                                    pattern="[0-9]*" inputmode="numeric" 
                                    onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Complaint Details Section (matching index file columns) -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-white fw-bold mb-3"><i data-feather="alert-triangle" class="me-2"
                                    style="width: 16px; height: 16px;"></i>Complaint Nature & Type</h6>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="category" class="form-label text-white">Category <span
                                        class="text-danger">*</span></label>
                                <select id="category" name="category"
                                    class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    @if (isset($categories) && $categories->count() > 0)
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat }}"
                                                {{ old('category') == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="title" class="form-label text-white">Complaint Type <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('title') is-invalid @enderror" id="title"
                                    name="title" autocomplete="off" required>
                                </select>
                                <input type="text" class="form-select @error('title') is-invalid @enderror"
                                    id="title_other" name="title_other" placeholder="Enter custom title..."
                                    style="display: none;" value="{{ old('title_other') }}">
                                {{-- <small class="text-muted">Select category above to see complaint titles</small> --}}
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>





                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="priority" class="form-label text-white">Priority <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror" id="priority"
                                    name="priority" required>
                                    <option value="">Select Priority</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low - Can wait
                                    </option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium -
                                        Normal</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High -
                                        Important</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent -
                                        Critical</option>
                                   
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
                                    id="availability_time" name="availability_time" value="{{ old('availability_time') }}">
                                @error('availability_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="assigned_employee_id" class="form-label text-white">Assign Employee</label>
                                <select class="form-select @error('assigned_employee_id') is-invalid @enderror"
                                    id="assigned_employee_id" name="assigned_employee_id">
                                    <option value="">Select Employee</option>
                                    @if (isset($employees) && $employees->count() > 0)
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}"
                                                data-category="{{ $employee->category ?? '' }}"
                                                data-city="{{ $employee->city_id }}"
                                                data-sector="{{ $employee->sector_id }}"
                                                {{ (string) old('assigned_employee_id') === (string) $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}@if($employee->designation) ({{ $employee->designation }})@endif</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>No employees available</option>
                                    @endif
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
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="4" autocomplete="off">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.complaints.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-accent">Create Complaint</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    </div>
@endsection

@push('styles')
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Element Definitions
            const phoneInput = document.getElementById('phone');
            const houseSelect = document.getElementById('house_id');
            const citySelect = document.getElementById('city_id');
            const sectorSelect = document.getElementById('sector_id');
            const addressInput = document.getElementById('address');
            const spareSelect = document.getElementById('spare_select');
            const quantityInput = document.getElementById('quantity_input');
            const stockWarning = document.getElementById('stock_warning');
            const categorySelect = document.getElementById('category');
            const employeeSelect = document.getElementById('assigned_employee_id');
            const titleSelect = document.getElementById('title');
            const titleOtherInput = document.getElementById('title_other');
            const complaintForm = document.querySelector('form[action*="complaints"]');

            // 2. Helper Functions

            // Load sectors based on city
            function loadSectors(cityId, targetSectorId = null) {
                if (!sectorSelect) return;
                if (!cityId) {
                    sectorSelect.innerHTML = '<option value="">Select GE Groups First</option>';
                    sectorSelect.disabled = true;
                    return;
                }

                sectorSelect.innerHTML = '<option value="">Loading GE Nodes...</option>';
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
                        sectorSelect.innerHTML = '<option value="">Select GE Nodes</option>';
                        if (data && data.length > 0) {
                            data.forEach(sector => {
                                const option = document.createElement('option');
                                option.value = sector.id;
                                option.textContent = sector.name;
                                if (targetSectorId && String(sector.id) === String(targetSectorId)) {
                                    option.selected = true;
                                }
                                sectorSelect.appendChild(option);
                            });
                            sectorSelect.disabled = false;
                        } else {
                            sectorSelect.innerHTML = '<option value="">No GE Nodes found</option>';
                            sectorSelect.disabled = false;
                        }
                        
                        // Trigger change to update dependent filters
                        sectorSelect.dispatchEvent(new Event('change'));
                    })
                    .catch(error => {
                        console.error('Error loading GE Nodes:', error);
                        sectorSelect.innerHTML = '<option value="">Error loading GE Nodes</option>';
                        sectorSelect.disabled = false;
                    });
            }

            function filterHouses() {
                if (!houseSelect) return;
                const cityId = citySelect ? citySelect.value : '';
                const sectorId = sectorSelect ? sectorSelect.value : '';
                const currentSelectedId = houseSelect.value;
                let currentlySelectedIsHidden = false;

                Array.from(houseSelect.options).forEach(opt => {
                    if (!opt.value) return; 
                    const optCity = opt.getAttribute('data-city') || '';
                    const optSector = opt.getAttribute('data-sector') || '';
                    
                    let show = true;
                    if (cityId && String(optCity) !== String(cityId)) show = false;
                    if (sectorId && String(optSector) !== String(sectorId)) show = false;
                    
                    opt.hidden = !show;
                    opt.style.display = show ? '' : 'none';
                    opt.disabled = !show;

                    if (!show && opt.value === currentSelectedId) {
                        currentlySelectedIsHidden = true;
                    }
                });

                if (currentlySelectedIsHidden) {
                    houseSelect.value = '';
                }
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

            function updateStockWarning() {
                if (!spareSelect || !quantityInput || !stockWarning) return;
                if (!spareSelect.value) {
                    stockWarning.style.display = 'none';
                    return;
                }

                const selectedOption = spareSelect.options[spareSelect.selectedIndex];
                const stock = selectedOption ? parseInt(selectedOption.getAttribute('data-stock') || 0) : 0;
                const requestedQty = parseInt(quantityInput.value) || 0;

                if (requestedQty > stock && stock > 0) {
                    quantityInput.value = stock;
                    stockWarning.textContent = `Insufficient stock! Quantity adjusted to available stock: ${stock}`;
                    stockWarning.style.display = 'block';
                    stockWarning.className = 'text-warning mt-1';
                } else if (stock === 0) {
                    stockWarning.textContent = 'Warning: This product has zero stock available.';
                    stockWarning.style.display = 'block';
                    stockWarning.className = 'text-danger mt-1';
                } else {
                    stockWarning.style.display = 'none';
                }
            }

            function handleTitleChange() {
                if (!titleSelect || !titleOtherInput) return;
                const selectedValue = titleSelect.value;

                if (selectedValue === 'other') {
                    titleSelect.style.display = 'none';
                    titleOtherInput.style.display = 'block';
                    titleOtherInput.required = true;
                    titleSelect.removeAttribute('required');
                    setTimeout(() => titleOtherInput.focus(), 100);
                } else {
                    titleSelect.style.display = 'block';
                    titleOtherInput.style.display = 'none';
                    titleOtherInput.required = false;
                    titleSelect.required = true;
                }
            }

            // 3. Event Listeners

            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
                phoneInput.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                    this.value = pastedText.replace(/[^0-9]/g, '');
                });
            }

            if (citySelect) {
                citySelect.addEventListener('change', function() {
                    loadSectors(this.value);
                    filterHouses();
                    filterEmployees();
                });
            }

            if (sectorSelect) {
                sectorSelect.addEventListener('change', function() {
                    filterHouses();
                    filterEmployees();
                });
            }

            if (houseSelect) {
                houseSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (!selectedOption || !selectedOption.value) return;

                    const cityId = selectedOption.getAttribute('data-city');
                    const sectorId = selectedOption.getAttribute('data-sector');
                    const address = selectedOption.getAttribute('data-address');

                    if (cityId) {
                        citySelect.value = cityId;
                        loadSectors(cityId, sectorId);
                        filterEmployees();
                    }

                    if (address) {
                        addressInput.value = address;
                    }
                });
            }

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

            if (spareSelect && quantityInput) {
                spareSelect.addEventListener('change', updateStockWarning);
                quantityInput.addEventListener('input', updateStockWarning);
                quantityInput.addEventListener('change', updateStockWarning);
            }

            if (categorySelect) {
                categorySelect.addEventListener('change', function() {
                    filterEmployees();
                    
                    if (titleSelect) {
                        const category = this.value;
                        titleSelect.innerHTML = '<option value="">Loading titles...</option>';
                        titleSelect.disabled = true;
                        if (titleOtherInput) {
                            titleOtherInput.style.display = 'none';
                            titleOtherInput.value = '';
                        }
                        titleSelect.style.display = 'block';

                        if (!category) {
                            titleSelect.innerHTML = '<option value="">Select Category first</option>';
                            titleSelect.disabled = false;
                            return;
                        }

                        fetch(`{{ route('admin.complaint-titles.by-category') }}?category=${encodeURIComponent(category)}`, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                            })
                            .then(response => response.json())
                            .then(data => {
                                titleSelect.innerHTML = '<option value="">Select Complaint Title</option>';
                                if (data && data.length > 0) {
                                    data.sort((a, b) => (a.title || '').toLowerCase().localeCompare((b.title || '').toLowerCase(), undefined, { numeric: true }))
                                        .forEach(title => {
                                            const option = document.createElement('option');
                                            option.value = title.title;
                                            option.textContent = title.title;
                                            if (title.description) option.setAttribute('title', title.description);
                                            titleSelect.appendChild(option);
                                        });
                                } else {
                                    titleSelect.innerHTML = '<option value="">No titles found</option>';
                                }
                                
                                const otherOption = document.createElement('option');
                                otherOption.value = 'other';
                                otherOption.textContent = 'Other';
                                titleSelect.appendChild(otherOption);
                                
                                titleSelect.disabled = false;
                                
                                const previous = titleSelect.getAttribute('data-prev');
                                if (previous) {
                                    if (Array.from(titleSelect.options).some(o => o.value === previous)) {
                                        titleSelect.value = previous;
                                    } else if (previous === 'other') {
                                        titleSelect.value = 'other';
                                        handleTitleChange();
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Error loading titles:', error);
                                titleSelect.innerHTML = '<option value="">Failed to load titles</option>';
                                titleSelect.disabled = false;
                            });
                    }
                });
            }

            if (titleSelect) {
                titleSelect.addEventListener('change', handleTitleChange);
            }

            if (complaintForm) {
                complaintForm.addEventListener('submit', function(e) {
                    // Phone validation
                    if (phoneInput && phoneInput.value.trim() && phoneInput.value.trim().length < 11) {
                        e.preventDefault();
                        alert('Phone number must be at least 11 digits.');
                        phoneInput.focus();
                        return false;
                    }

                    // Stock validation
                    if (spareSelect && spareSelect.value && (!quantityInput.value || parseInt(quantityInput.value) <= 0)) {
                        e.preventDefault();
                        alert('Please enter quantity for selected product.');
                        return false;
                    }

                    // Title mapping
                    if (titleSelect && (titleSelect.value === 'other' || titleOtherInput.style.display !== 'none')) {
                        if (!titleOtherInput.value || titleOtherInput.value.trim() === '') {
                            e.preventDefault();
                            alert('Please enter a custom complaint title.');
                            titleOtherInput.focus();
                            return false;
                        }

                        // Remove name from select and send hidden input
                        titleSelect.removeAttribute('name');
                        titleSelect.disabled = true;

                        const hiddenTitle = document.createElement('input');
                        hiddenTitle.type = 'hidden';
                        hiddenTitle.name = 'title';
                        hiddenTitle.value = titleOtherInput.value.trim();
                        this.appendChild(hiddenTitle);
                        
                        const hiddenOther = document.createElement('input');
                        hiddenOther.type = 'hidden';
                        hiddenOther.name = 'title_other';
                        hiddenOther.value = titleOtherInput.value.trim();
                        this.appendChild(hiddenOther);
                    }
                });
            }

            // 4. Initial Setup
            const defaultCityId = @json(old('city_id', $defaultCityId));
            const defaultSectorId = @json(old('sector_id', $defaultSectorId));
            
            if (defaultCityId) {
                loadSectors(defaultCityId, defaultSectorId);
            } else {
                if (sectorSelect) {
                    sectorSelect.innerHTML = '<option value="">Select GE Groups First</option>';
                    sectorSelect.disabled = true;
                }
            }

            // Restore "Other" title if needed
            if (titleSelect && '{{ old('title') }}' === 'other') {
                titleSelect.value = 'other';
                const oldOther = @json(old('title_other'));
                if (titleOtherInput && oldOther) titleOtherInput.value = oldOther;
                handleTitleChange();
            }

            filterHouses();
            filterEmployees();
        });
    </script>
@endpush
