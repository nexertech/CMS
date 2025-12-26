@extends('layouts.sidebar')

@section('title', 'Edit Complaint — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Edit Complaint</h2>
      <p class="text-light">Update complaint information</p>
    </div>
   
  </div>
</div>

<!-- COMPLAINT FORM -->
<div class="card-glass">
  <div class="card-body">
          <form action="{{ route('admin.complaints.update', $complaint) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Complainant Information Section (matching index file columns) -->
            <div class="row mb-4">
              <div class="col-12">
                <h6 class="text-white fw-bold mb-3"><i data-feather="user" class="me-2" style="width: 16px; height: 16px;"></i>Complainant Information</h6>
              </div>
              <div class="col-md-3">
                <div class="mb-3">
                  <label for="city_id" class="form-label text-white">GE Groups</label>
                  <select class="form-select @error('city_id') is-invalid @enderror" 
                          id="city_id" name="city_id">
                    <option value="">Select GE Groups</option>
                    @if(isset($cities) && $cities->count() > 0)
                      @foreach($cities as $city)
                        <option value="{{ $city->id }}" {{ (string)old('city_id', $defaultCityId ?? '') === (string)$city->id ? 'selected' : '' }}>
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
                  <select class="form-select @error('sector_id') is-invalid @enderror" 
                          id="sector_id" name="sector_id" {{ (old('city_id', $defaultCityId ?? null)) ? '' : 'disabled' }}>
                    @php $hasCity = old('city_id', $defaultCityId ?? null); @endphp
                    <option value="">{{ $hasCity ? 'Loading GE Nodes...' : 'Select GE Groups First' }}</option>
                    @if(isset($sectors) && $sectors->count() > 0)
                      @foreach($sectors as $sector)
                        <option value="{{ $sector->id }}" {{ (string)old('sector_id', $defaultSectorId ?? '') === (string)$sector->id ? 'selected' : '' }}>
                          {{ $sector->name }}
                        </option>
                      @endforeach
                    @endif
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
                              {{ old('house_id', $complaint->house_id) == $house->id ? 'selected' : '' }}>
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
                  <input type="text" 
                         class="form-control @error('client_name') is-invalid @enderror" 
                         id="client_name" 
                         name="client_name" 
                         value="{{ old('client_name', $complaint->client->client_name ?? '') }}"
                         placeholder="Enter complainant name">
                  @error('client_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-3">
                <div class="mb-3">
                  <label for="address" class="form-label text-white">Address</label>
                  <input type="text" class="form-control @error('address') is-invalid @enderror" id="client_address" name="address" value="{{ old('address', $complaint->client->address ?? '') }}" placeholder="e.g., 00/0-ST-0-B-0">
                  @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-3">
                <div class="mb-3">
                  <label for="phone" class="form-label text-white">Phone No.</label>
                  <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="client_phone" name="phone" value="{{ old('phone', $complaint->client->phone ?? '') }}" placeholder="Enter phone number"
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
                <h6 class="text-white fw-bold mb-3"><i data-feather="alert-triangle" class="me-2" style="width: 16px; height: 16px;"></i>Complaint Nature & Type</h6>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="category" class="form-label text-white">Category <span class="text-danger">*</span></label>
                  <select id="category" name="category" class="form-select @error('category') is-invalid @enderror" required>
                    <option value="">Select Category</option>
                    @if(isset($categories) && $categories->count() > 0)
                      @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category', $complaint->category) == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
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
                  <label for="title" class="form-label text-white">Complaint Type <span class="text-danger">*</span></label>
                  <select class="form-select @error('title') is-invalid @enderror" 
                          id="title" name="title" autocomplete="off" required>
                    <option value="">Select Complaint Type</option>
                    @if(old('title', $complaint->title))
                      <option value="{{ old('title', $complaint->title) }}" selected>{{ old('title', $complaint->title) }}</option>
                    @endif
                  </select>
                  <input type="text" class="form-select @error('title') is-invalid @enderror"
                          id="title_other" name="title_other" placeholder="Enter custom title..."
                          style="display: none;" value="{{ old('title_other', $complaint->title) }}">
                  @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              

              

              <div class="col-md-4">
                <div class="mb-3">
                  <label for="priority" class="form-label text-white">Priority <span class="text-danger">*</span></label>
                  <select class="form-select @error('priority') is-invalid @enderror" 
                          id="priority" name="priority" required>
                    <option value="">Select Priority</option>
                    <option value="low" {{ old('priority', $complaint->priority) == 'low' ? 'selected' : '' }}>Low - Can wait</option>
                    <option value="medium" {{ old('priority', $complaint->priority) == 'medium' ? 'selected' : '' }}>Medium - Normal</option>
                    <option value="high" {{ old('priority', $complaint->priority) == 'high' ? 'selected' : '' }}>High - Important</option>
                    <option value="urgent" {{ old('priority', $complaint->priority) == 'urgent' ? 'selected' : '' }}>Urgent - Critical</option>
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
                         id="availability_time" name="availability_time" 
                         value="{{ old('availability_time', $complaint->availability_time) }}">
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
                    @if(isset($employees) && $employees->count() > 0)
                      @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" 
                                data-category="{{ $employee->category ?? '' }}"
                                data-city="{{ $employee->city_id }}"
                                data-sector="{{ $employee->sector_id }}"
                                {{ (string)old('assigned_employee_id', $complaint->assigned_employee_id) === (string)$employee->id ? 'selected' : '' }}>
                          {{ $employee->name }}@if($employee->designation) ({{ $employee->designation }})@endif
                        </option>
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

            <!-- Description moved below product section -->
            <div class="row mt-3">
              <div class="col-12">
                <div class="mb-3">
                  <label for="description" class="form-label text-white">Description</label>
                  <textarea class="form-control @error('description') is-invalid @enderror" 
                            id="description" name="description" rows="4" >{{ old('description', $complaint->description) }}</textarea>
                  @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="{{ route('admin.complaints.index', $complaint) }}" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-accent">Update Complaint</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Element Definitions
            const phoneInput = document.getElementById('client_phone');
            const clientNameInput = document.getElementById('client_name');
            const houseSelect = document.getElementById('house_id');
            const citySelect = document.getElementById('city_id');
            const sectorSelect = document.getElementById('sector_id');
            const addressInput = document.getElementById('client_address');
            const categorySelect = document.getElementById('category');
            const employeeSelect = document.getElementById('assigned_employee_id');
            const titleSelect = document.getElementById('title');
            const titleOtherInput = document.getElementById('title_other');
            const complaintForm = document.querySelector('form[action*="complaints"]');
            const currentTitle = '{{ old('title', $complaint->title) }}';

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
                        
                        // Restore selection
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
                    const optCategory = (opt.getAttribute('data-category') || '').trim();
                    const optCity = opt.getAttribute('data-city') || '';
                    const optSector = opt.getAttribute('data-sector') || '';
                    
                    const matchCategory = !category || optCategory === category.trim();
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
                        // Only update city/sector if they differ
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
                                titleSelect.innerHTML = '<option value="">Select Complaint Type</option>';
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
                                
                                const previous = titleSelect.getAttribute('data-prev') || currentTitle;
                                if (previous) {
                                    const opt = Array.from(titleSelect.options).find(o => o.value === previous);
                                    if (opt) {
                                        titleSelect.value = previous;
                                    } else if (previous === 'other' || previous) {
                                        // If not in list, it's a custom title
                                        titleSelect.value = 'other';
                                        handleTitleChange();
                                        if (titleOtherInput) titleOtherInput.value = previous;
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
            const defaultCityId = '{{ old('city_id', $defaultCityId ?? '') }}';
            const defaultSectorId = '{{ old('sector_id', $defaultSectorId ?? '') }}';
            
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
            
            // Auto-select single city (for restricted users) if nothing selected
            if (citySelect && citySelect.options.length === 2 && !citySelect.value) { 
                 citySelect.selectedIndex = 1;
                 citySelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endpush




