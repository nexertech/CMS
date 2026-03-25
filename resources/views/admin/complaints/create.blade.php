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

                    <div style="display: none;">
                        <input type="text" name="fake_title" autocomplete="off">
                        <input type="text" name="fake_description" autocomplete="off">
                    </div>

                    <!-- Complementary Remarks for Receiver -->
                    <div class="alert alert-info mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                        <div class="d-flex align-items-center">
                            <i data-feather="message-circle" class="me-3" style="width: 32px; height: 32px; color: #ffffff;"></i>
                            <div style="flex: 1;">
                                <h6 class="mb-2 text-white fw-bold" style="font-size: 16px;">Greeting Guidelines for Complaint Receiver</h6>
                                <p class="mb-1 text-white" style="font-size: 14px; opacity: 0.95;">
                                    <strong>Start with:</strong> السلام علیکم (As'salam O Alaikum)
                                </p>
                                <p class="mb-1 text-white" style="font-size: 14px; opacity: 0.95;">
                                    <strong>Ask politely:</strong> How may I assist you today? / آپ کی کیا مدد کر سکتا ہوں؟
                                </p>
                                <p class="mb-0 text-white" style="font-size: 13px; opacity: 0.85; font-style: italic;">
                                    Remember to be courteous, patient, and professional while recording the complaint.
                                </p>
                            </div>
                        </div>
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
                                            data-name="{{ $house->name }}"
                                            data-phone="{{ $house->phone }}"
                                            {{ old('house_id') == $house->id ? 'selected' : '' }}>
                                            {{ $house->house_no }}{{ $house->name ? ' - ' . $house->name : '' }}
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
                                <label for="complainant_name" class="form-label text-white">Name</label>
                                <input type="text" class="form-control @error('complainant_name') is-invalid @enderror"
                                    id="complainant_name" name="complainant_name" value="{{ old('complainant_name') }}"
                                    placeholder="Enter name">
                                @error('complainant_name')
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
                                    @foreach ($categories as $id => $name)
                                        <option value="{{ $id }}"
                                            {{ old('category') == $id ? 'selected' : '' }}>{{ ucfirst($name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="complaint_title_id" class="form-label text-white">Complaint Type <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('complaint_title_id') is-invalid @enderror" id="title"
                                    name="complaint_title_id" autocomplete="off" required data-prev="{{ old('complaint_title_id') }}">
                                    <option value="">Select Category First</option>
                                </select>
                                <input type="text" class="form-select @error('title') is-invalid @enderror"
                                    id="title_other" name="title_other" placeholder="Enter custom title..."
                                    style="display: none;" value="{{ old('title_other') }}">
                                {{-- <small class="text-muted">Select category above to see complaint titles</small> --}}
                                @error('complaint_title_id')
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
                                <label for="assigned_employee_id" class="form-label text-white">Assign Employee <span class="text-danger">*</span></label>
                                <select class="form-select @error('assigned_employee_id') is-invalid @enderror"
                                    id="assigned_employee_id" name="assigned_employee_id" required>
                                    <option value="">Select Employee</option>
                                    @if (isset($employees) && $employees->count() > 0)
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}"
                                                data-category="{{ $employee->category_id ?? '' }}"
                                                data-city="{{ $employee->city_id }}"
                                                data-sector="{{ $employee->sector_id }}"
                                                {{ (string) old('assigned_employee_id') === (string) $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}@if($employee->designation) ({{ $employee->designation->name }})@endif</option>
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
                                <div id="fixed-questions-container" class="alert alert-info mb-3" style="display: none;">
                                    <strong><i data-feather="help-circle" class="me-2" style="width: 16px; height: 16px;"></i>Questions to ask:</strong>
                                    <p id="fixed-questions-text" class="mb-0 mt-1"></p>
                                </div>
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" integrity="sha384-OXVF05DQEe311p6ohU11NwlnX08FzMCsyoXzGOaL+83dKAb3qS17yZJxESl8YrJQ" crossorigin="anonymous" />
    <style>
        /* Custom Select2 Styling for Light Theme */
        .select2-container--default .select2-selection--single {
            background-color: #ffffff;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            height: 38px;
            color: #495057;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #495057;
            line-height: 36px;
            padding-left: 12px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .select2-dropdown {
            background-color: #ffffff;
            border: 1px solid #ced4da;
            color: #495057;
            z-index: 9999;
        }

        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #3b82f6; /* Keep accent color for selection */
            color: white;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e9ecef;
            color: #495057;
        }

        .select2-search--dropdown .select2-search__field {
            background-color: #ffffff;
            border: 1px solid #ced4da;
            color: #495057;
            border-radius: 4px;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            display: block;
            padding-right: 20px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@endpush

@push('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" integrity="sha384-d3UHjPdzJkZuk5H3qKYMLRyWLAQBJbby2yr2Q58hXXtAGF8RSNO9jpLDlKKPv5v3" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Element Definitions
            const phoneInput = document.getElementById('phone');
            const clientNameInput = document.getElementById('complainant_name');
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

            // Store original options for filtering (Select2-compatible approach)
            let allHouseOptions = [];
            if (houseSelect) {
                Array.from(houseSelect.options).forEach(opt => {
                    if (opt.value) { 
                        allHouseOptions.push({
                            value: opt.value,
                            text: opt.innerText,
                            city: opt.getAttribute('data-city'),
                            sector: opt.getAttribute('data-sector'),
                            address: opt.getAttribute('data-address'),
                            name: opt.getAttribute('data-name'),
                            phone: opt.getAttribute('data-phone'),
                            selected: opt.selected
                        });
                    }
                });
            }

            // Initialize Select2
            $(document).ready(function() {
                $('#house_id').select2({
                    placeholder: "Select House Number",
                    allowClear: true,
                    width: '100%'
                });

                $('#house_id').on('select2:select', function (e) {
                    houseSelect.dispatchEvent(new Event('change'));
                });
                
                $('#house_id').on('select2:clear', function (e) {
                    houseSelect.dispatchEvent(new Event('change'));
                });
            });

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
                            
                            // Auto-select if only one option is available
                            if (data.length === 1) {
                                sectorSelect.value = data[0].id;
                            }
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
                
                // Get current selection from Select2 (or value)
                const currentSelectedId = $(houseSelect).val(); 
                
                // Clear and rebuild options
                houseSelect.innerHTML = '<option value="">Select House Number</option>';
                
                let hasSelection = false;

                allHouseOptions.forEach(optData => {
                    let show = true;
                    if (cityId && String(optData.city) !== String(cityId)) show = false;
                    if (sectorId && String(optData.sector) !== String(sectorId)) show = false;
                    
                    if (show) {
                        const option = document.createElement('option');
                        option.value = optData.value;
                        option.textContent = optData.text;
                        option.setAttribute('data-city', optData.city);
                        option.setAttribute('data-sector', optData.sector);
                        option.setAttribute('data-address', optData.address);
                        option.setAttribute('data-name', optData.name);
                        option.setAttribute('data-phone', optData.phone);
                        
                        // Restore selection logic
                        if (currentSelectedId && String(optData.value) === String(currentSelectedId)) {
                            option.selected = true;
                            hasSelection = true;
                        } else if (!currentSelectedId && optData.selected && !hasSelection) {
                            option.selected = true;
                            hasSelection = true;
                        }

                        houseSelect.appendChild(option);
                    }
                });
                
                // Refresh Select2
                $(houseSelect).trigger('change.select2');
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
                const questionsContainer = document.getElementById('fixed-questions-container');
                const questionsText = document.getElementById('fixed-questions-text');

                // Reset questions
                if (questionsContainer) questionsContainer.style.display = 'none';
                if (questionsText) questionsText.textContent = '';

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

                    // Show questions if available
                    const selectedOption = titleSelect.options[titleSelect.selectedIndex];
                    const questions = selectedOption ? selectedOption.getAttribute('data-questions') : null;
                    
                    if (questions && questionsContainer && questionsText) {
                        questionsText.textContent = questions;
                        questionsContainer.style.display = 'block';
                        // Re-initialize feather icons if needed, or just let the CSS handle it
                        if(typeof feather !== 'undefined') feather.replace();
                    }
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
                        if (citySelect.value !== cityId) {
                            citySelect.value = cityId;
                            loadSectors(cityId, sectorId);
                        } else if (sectorSelect.value !== sectorId) {
                             sectorSelect.value = sectorId;
                        }
                        filterEmployees();
                    }

                    if (address) {
                        addressInput.value = address;
                    }
                    
                    const name = selectedOption.getAttribute('data-name');
                    if (name && clientNameInput) {
                        clientNameInput.value = name;
                    }

                    const phone = selectedOption.getAttribute('data-phone');
                    if (phone && phoneInput) {
                        phoneInput.value = phone;
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
                            titleSelect.innerHTML = '<option value="">Select Category First</option>';
                            titleSelect.disabled = false;
                            return;
                        }

                        // Updated to use category ID
                        const url = `{{ route('admin.complaint-titles.by-category') }}?category=${encodeURIComponent(category)}`;
                        console.log('Fetching titles from:', url);
                        
                        fetch(url, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                            })
                            .then(response => {
                                console.log('Response status:', response.status);
                                return response.json();
                            })
                            .then(data => {
                                console.log('Titles data received:', data);
                                titleSelect.innerHTML = '<option value="">Select Complaint Title</option>';
                                if (data && data.length > 0) {
                                    // Use ID as value
                                    data.sort((a, b) => (a.title || '').toLowerCase().localeCompare((b.title || '').toLowerCase(), undefined, { numeric: true }))
                                        .forEach(title => {
                                            const option = document.createElement('option');
                                            option.value = title.id; // Use ID
                                            option.textContent = title.title;
                                            if (title.description) option.setAttribute('title', title.description);
                                            if (title.questions) option.setAttribute('data-questions', title.questions);
                                            titleSelect.appendChild(option);
                                        });
                                } else {
                                    console.warn('No titles found for category:', category);
                                    titleSelect.innerHTML = '<option value="">No titles found</option>';
                                }
                                
                                const otherOption = document.createElement('option');
                                otherOption.value = 'other';
                                otherOption.textContent = 'Other';
                                titleSelect.appendChild(otherOption);
                                
                                titleSelect.disabled = false;
                                
                                const previous = titleSelect.getAttribute('data-prev');
                                if (previous) {
                                    // value is now ID, so check against ID
                                    const opt = Array.from(titleSelect.options).find(o => o.value == previous);
                                    if (opt) {
                                        titleSelect.value = (String)(previous); // ensure type match
                                        handleTitleChange();
                                    } else {
                                        // If previous was "other" or not found ID, check if it was 'other' string
                                        // If previous was a string (old input), it might not match ID.
                                        // But old('complaint_title_id') is ID.
                                        
                                        // If we have title_other input carrying value?
                                        // The 'other' logic checks for 'other' value in Select.
                                        // If previous ID not found, maybe it was 'other'.
                                        // Let's assume standardized 'other' handling.
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


            if (categorySelect && categorySelect.value) {
                categorySelect.dispatchEvent(new Event('change'));
            }

            filterHouses();
            filterEmployees();
            
            // Auto-populate house details if house_id is set by old input
            if (houseSelect && houseSelect.value) {
                houseSelect.dispatchEvent(new Event('change'));
            }
            
            // Auto-select single city (for restricted users)
            if (citySelect && citySelect.options.length === 2 && !citySelect.value) { 
                 citySelect.selectedIndex = 1;
                 citySelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endpush
