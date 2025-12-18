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
                                <label for="client_name" class="form-label text-white">Complainant Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('client_name') is-invalid @enderror"
                                    id="client_name" name="client_name" value="{{ old('client_name') }}"
                                    placeholder="Enter complainant name" required>
                                @error('client_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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
        // Stock validation and auto-adjustment
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
            
            const spareSelect = document.getElementById('spare_select');
            const quantityInput = document.getElementById('quantity_input');
            const stockWarning = document.getElementById('stock_warning');
            const categorySelect = document.getElementById('category');
            
            // Get form reference once (will be used for multiple validations)
            const complaintForm = document.querySelector('form[action*="complaints"]');
            
            // Form validation - check phone number before submit
            if (complaintForm && phoneInput) {
                complaintForm.addEventListener('submit', function(e) {
                    const phoneValue = phoneInput.value.trim();
                    if (phoneValue && phoneValue.length < 11) {
                        e.preventDefault();
                        alert('Phone number must be at least 11 digits.');
                        phoneInput.focus();
                        return false;
                    }
                });
            }

            // Stock validation and auto-adjustment (only if spare/quantity inputs exist)
            if (spareSelect && quantityInput) {
                function updateStockWarning() {
                    if (!spareSelect.value) {
                        stockWarning && (stockWarning.style.display = 'none');
                        return;
                    }

                    const selectedOption = spareSelect.options[spareSelect.selectedIndex];
                    const stock = selectedOption ? parseInt(selectedOption.getAttribute('data-stock') || 0) : 0;
                    const requestedQty = parseInt(quantityInput.value) || 0;

                    if (stockWarning) {
                        if (requestedQty > stock && stock > 0) {
                            // Auto-adjust quantity to available stock
                            quantityInput.value = stock;
                            stockWarning.textContent =
                                `Insufficient stock! Quantity adjusted to available stock: ${stock}`;
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
                }

                // Update warning when product or quantity changes
                spareSelect.addEventListener('change', updateStockWarning);
                quantityInput.addEventListener('input', updateStockWarning);
                quantityInput.addEventListener('change', updateStockWarning);

                // Form submission validation
                const form = document.querySelector('form[action*="complaints.store"]');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        // Validate quantity only if product is selected
                        if (spareSelect.value && (!quantityInput.value || parseInt(quantityInput.value) <=
                                0)) {
                            e.preventDefault();
                            alert('Please enter quantity for selected product.');
                            return false;
                        }
                        // If quantity is entered but no product selected
                        if (quantityInput.value && parseInt(quantityInput.value) > 0 && !spareSelect
                            .value) {
                            e.preventDefault();
                            alert('Please select a product for the quantity.');
                            return false;
                        }
                    });
                }
            }

            const employeeSelect = document.getElementById('assigned_employee_id');

            // Employee filter: by Category, City, Sector
            function filterEmployees() {
                if (!employeeSelect) return;
                const category = categorySelect ? categorySelect.value : '';
                const cityId = document.getElementById('city_id') ? document.getElementById('city_id').value : '';
                const sectorId = document.getElementById('sector_id') ? document.getElementById('sector_id').value :
                    '';
                let firstVisible = null;
                Array.from(employeeSelect.options).forEach(opt => {
                    if (!opt.value) return; // placeholder
                    const optCategory = opt.getAttribute('data-category') || '';
                    const optCity = opt.getAttribute('data-city') || '';
                    const optSector = opt.getAttribute('data-sector') || '';
                    const matchCategory = !category || optCategory === category;
                    const matchCity = !cityId || String(optCity) === String(cityId);
                    const matchSector = !sectorId || String(optSector) === String(sectorId);
                    const show = matchCategory && matchCity && matchSector;
                    opt.hidden = !show;
                    if (show && !firstVisible) firstVisible = opt;
                });
                // If selected option is hidden, clear selection
                if (employeeSelect.selectedOptions.length) {
                    const sel = employeeSelect.selectedOptions[0];
                    if (sel && sel.hidden) employeeSelect.value = '';
                }
            }
            if (employeeSelect) {
                categorySelect && categorySelect.addEventListener('change', filterEmployees);
                const citySelectEl = document.getElementById('city_id');
                const sectorSelectEl = document.getElementById('sector_id');
                citySelectEl && citySelectEl.addEventListener('change', filterEmployees);
                sectorSelectEl && sectorSelectEl.addEventListener('change', filterEmployees);
                filterEmployees();
            }

            // Category -> Complaint Titles dynamic loading
            const titleSelect = document.getElementById('title');
            const titleOtherInput = document.getElementById('title_other');

            // Handle "Other" option selection
            function handleTitleChange() {
                if (!titleSelect || !titleOtherInput) return;

                const selectedValue = titleSelect.value;

                if (selectedValue === 'other') {
                    // Hide dropdown and show input field in same position
                    titleSelect.style.display = 'none';
                    titleOtherInput.style.display = 'block';
                    titleOtherInput.required = true;
                    titleSelect.removeAttribute('required');
                    // Focus on input field
                    setTimeout(() => titleOtherInput.focus(), 100);
                } else {
                    // Show dropdown and hide input field
                    titleSelect.style.display = 'block';
                    titleOtherInput.style.display = 'none';
                    titleOtherInput.required = false;
                    titleSelect.required = true;
                }
            }

            if (titleSelect) {
                titleSelect.addEventListener('change', handleTitleChange);
            }

            // Sync title_other input to title field when typing
            if (titleOtherInput) {
                titleOtherInput.addEventListener('input', function() {
                    if (titleSelect.value === 'other') {
                        // Update title select value to "other" (it's already selected)
                        // The actual title value will be taken from title_other on submit
                    }
                });
            }

            if (categorySelect && titleSelect) {
                categorySelect.addEventListener('change', function() {
                    const category = this.value;

                    // Clear existing options
                    titleSelect.innerHTML = '<option value="">Loading titles...</option>';
                    titleSelect.disabled = true;
                    if (titleOtherInput) {
                        titleOtherInput.style.display = 'none';
                        titleOtherInput.value = '';
                    }
                    // Ensure dropdown is visible when loading
                    if (titleSelect) {
                        titleSelect.style.display = 'block';
                    }

                    if (!category) {
                        titleSelect.innerHTML =
                            '<option value="">Select Category first, then choose title</option>';
                        titleSelect.disabled = false;
                        return;
                    }

                    // Fetch complaint titles by category
                    fetch(`{{ route('admin.complaint-titles.by-category') }}?category=${encodeURIComponent(category)}`, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin'
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Clear options
                            titleSelect.innerHTML = '<option value="">Select Complaint Title</option>';

                            if (data && data.length > 0) {
                                // Sort titles in ascending order by title name (natural/numeric sorting)
                                const sortedData = data.sort((a, b) => {
                                    const titleA = (a.title || '').toLowerCase();
                                    const titleB = (b.title || '').toLowerCase();
                                    return titleA.localeCompare(titleB, undefined, { numeric: true, sensitivity: 'base' });
                                });
                                
                                sortedData.forEach(title => {
                                    const option = document.createElement('option');
                                    option.value = title.title;
                                    option.textContent = title.title;
                                    if (title.description) {
                                        option.setAttribute('title', title.description);
                                    }
                                    titleSelect.appendChild(option);
                                });
                            } else {
                                const option = document.createElement('option');
                                option.value = '';
                                option.textContent = 'No titles found for this category';
                                titleSelect.appendChild(option);
                            }

                            // Add "Other" option
                            const otherOption = document.createElement('option');
                            otherOption.value = 'other';
                            otherOption.textContent = 'Other';
                            titleSelect.appendChild(otherOption);

                            titleSelect.disabled = false;
                            // Restore previously selected title if any
                            const previous = titleSelect.getAttribute('data-prev');
                            if (previous) {
                                const opt = Array.from(titleSelect.options).find(o => o.value ===
                                    previous);
                                if (opt) {
                                    titleSelect.value = previous;
                                } else if (previous === 'other') {
                                    // If previous was "other", restore it
                                    titleSelect.value = 'other';
                                    if (titleOtherInput) {
                                        const oldOther = '{{ old('title_other') }}';
                                        if (oldOther) {
                                            titleOtherInput.value = oldOther;
                                        }
                                        // Hide dropdown and show input field
                                        titleSelect.style.display = 'none';
                                        titleOtherInput.style.display = 'block';
                                        titleOtherInput.required = true;
                                        titleSelect.removeAttribute('required');
                                    }
                                }
                            }
                            handleTitleChange();
                        })
                        .catch(error => {
                            console.error('Error loading complaint titles:', error);
                            titleSelect.innerHTML =
                                '<option value="">Failed to load titles. Please try again.</option>';
                            titleSelect.disabled = false;
                        });
                });

                // Trigger on page load if category is pre-selected
                if (categorySelect.value) {
                    // Preserve old title if present
                    if (titleSelect && titleSelect.value) {
                        titleSelect.setAttribute('data-prev', titleSelect.value);
                    } else if ('{{ old('title') }}') {
                        const oldTitle = @json(old('title'));
                        titleSelect.setAttribute('data-prev', oldTitle);
                        // If old title was "other", check for title_other
                        if (oldTitle === 'other' && titleOtherInput) {
                            const oldOther = '{{ old('title_other') }}';
                            if (oldOther) {
                                titleOtherInput.value = oldOther;
                            }
                            // Hide dropdown and show input field
                            titleSelect.style.display = 'none';
                            titleOtherInput.style.display = 'block';
                            titleOtherInput.required = true;
                            titleSelect.removeAttribute('required');
                        }
                    }
                    categorySelect.dispatchEvent(new Event('change'));
                }
            }

            // City -> Sector dynamic loading
            const citySelect = document.getElementById('city_id');
            const sectorSelect = document.getElementById('sector_id');
            const addressInput = document.getElementById('address');

            // Auto-replace space with hyphen in address field
            if (addressInput) {
                addressInput.addEventListener('keydown', function(e) {
                    // If space key is pressed
                    if (e.key === ' ' || e.keyCode === 32) {
                        e.preventDefault(); // Prevent default space
                        
                        // Get current cursor position
                        const cursorPos = this.selectionStart;
                        const currentValue = this.value;
                        
                        // Insert hyphen at cursor position
                        const newValue = currentValue.substring(0, cursorPos) + '-' + currentValue.substring(cursorPos);
                        this.value = newValue;
                        
                        // Set cursor position after the inserted hyphen
                        this.setSelectionRange(cursorPos + 1, cursorPos + 1);
                    }
                });
                
                // Also handle paste events to replace spaces with hyphens
                addressInput.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                    const replacedText = pastedText.replace(/\s+/g, '-');
                    
                    // Get current cursor position
                    const cursorPos = this.selectionStart;
                    const currentValue = this.value;
                    
                    // Insert replaced text at cursor position
                    const newValue = currentValue.substring(0, cursorPos) + replacedText + currentValue.substring(this.selectionEnd);
                    this.value = newValue;
                    
                    // Set cursor position after the inserted text
                    this.setSelectionRange(cursorPos + replacedText.length, cursorPos + replacedText.length);
                });
            }

            if (citySelect && sectorSelect) {
                citySelect.addEventListener('change', function() {
                    const cityId = this.value;

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
                                    sectorSelect.appendChild(option);
                                });
                            } else {
                                sectorSelect.innerHTML =
                                    '<option value="">No GE Nodes found for this GE Groups</option>';
                            }
                            sectorSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error loading GE Nodes:', error);
                            sectorSelect.innerHTML = '<option value="">Error loading GE Nodes</option>';
                            sectorSelect.disabled = false;
                        });
                });

                // If city is pre-selected (e.g., for Department Staff), load sectors and select default
                const defaultCityId = @json(old('city_id', $defaultCityId));
                const defaultSectorId = @json(old('sector_id', $defaultSectorId));
                if (defaultCityId) {
                    citySelect.value = defaultCityId;
                    // Trigger fetch to load sectors, then select default
                    fetch(`{{ route('admin.sectors.by-city') }}?city_id=${defaultCityId}`, {
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
                                    sectorSelect.appendChild(option);
                                });

                                // Explicitly set the value if default sector exists in the list
                                if (defaultSectorId) {
                                    // Convert to string for comparison to be safe
                                    const targetId = String(defaultSectorId);
                                    // Check if option exists
                                    const optionExists = Array.from(sectorSelect.options).some(opt => opt.value === targetId);
                                    
                                    if (optionExists) {
                                        sectorSelect.value = targetId;
                                        // Trigger change event so dependent fields (like employee filter) update
                                        sectorSelect.dispatchEvent(new Event('change'));
                                    } else {
                                        console.warn(`Default sector ID ${defaultSectorId} not found in loaded sectors for city ${defaultCityId}`);
                                    }
                                }
                            } else {
                                sectorSelect.innerHTML =
                                    '<option value="">No GE Nodes found for this GE Groups</option>';
                            }
                            sectorSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error loading GE Nodes:', error);
                            sectorSelect.innerHTML = '<option value="">Error loading GE Nodes</option>';
                            sectorSelect.disabled = false;
                        });
                }
            }

            // Form submit handler: sync title_other to title when "Other" is selected
            // Use the same form reference declared above
            if (complaintForm && titleSelect && titleOtherInput) {
                complaintForm.addEventListener('submit', function(e) {
                    if (titleSelect.value === 'other' || titleOtherInput.style.display !== 'none') {
                        // User selected "Other" option
                        if (!titleOtherInput.value || titleOtherInput.value.trim() === '') {
                            e.preventDefault();
                            alert('Please enter a custom complaint title.');
                            titleOtherInput.focus();
                            return false;
                        }

                        // Remove any existing hidden title input
                        const existingHiddenTitle = document.getElementById('title_hidden');
                        if (existingHiddenTitle) {
                            existingHiddenTitle.remove();
                        }

                        // Remove name from select dropdown so it doesn't send "other"
                        titleSelect.removeAttribute('name');
                        titleSelect.disabled = true; // Disable to prevent sending value

                        // Create hidden input with custom title value
                        const hiddenTitle = document.createElement('input');
                        hiddenTitle.type = 'hidden';
                        hiddenTitle.id = 'title_hidden';
                        hiddenTitle.name = 'title';
                        hiddenTitle.value = titleOtherInput.value.trim();
                        complaintForm.appendChild(hiddenTitle);

                        // Also send title_other field explicitly
                        if (!document.getElementById('title_other_field')) {
                            const titleOtherField = document.createElement('input');
                            titleOtherField.type = 'hidden';
                            titleOtherField.id = 'title_other_field';
                            titleOtherField.name = 'title_other';
                            titleOtherField.value = titleOtherInput.value.trim();
                            complaintForm.appendChild(titleOtherField);
                        }
                    } else {
                        // Normal title selected - ensure select has name attribute
                        titleSelect.setAttribute('name', 'title');
                        titleSelect.disabled = false;
                        titleSelect.required = true;

                        // Remove hidden inputs if they exist
                        const hiddenTitle = document.getElementById('title_hidden');
                        if (hiddenTitle) {
                            hiddenTitle.remove();
                        }
                        const titleOtherField = document.getElementById('title_other_field');
                        if (titleOtherField) {
                            titleOtherField.remove();
                        }
                    }
                });
            }

        });
    </script>
@endpush
