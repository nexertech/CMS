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
                <form action="{{ route('admin.complaints.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off" novalidate>
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
                                <p class="mb-1 text-white" style="font-size: 14px; opacity: 0.95;">
                                    <strong>Recording:</strong> be courteous, patient, and professional while recording the complaint. / شکایت درج کرتے وقت خوش اخلاقی، صبر اور پیشہ ورانہ مہارت کا مظاہرہ کریں۔
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

                    <!-- Complaints Container - Repeatable Entries -->
                    <div id="complaints-container">
                        @php
                            $oldComplaints = old('complaints');
                            if (!is_array($oldComplaints) || empty($oldComplaints)) {
                                $oldComplaints = [0 => []];
                            }
                        @endphp

                        @foreach($oldComplaints as $index => $oldEntry)
                        @php
                            $catVal = is_array($oldEntry) && isset($oldEntry['category']) ? $oldEntry['category'] : old("complaints.{$index}.category");
                            $titleVal = is_array($oldEntry) && isset($oldEntry['complaint_title_id']) ? $oldEntry['complaint_title_id'] : old("complaints.{$index}.complaint_title_id");
                            $titleOtherVal = is_array($oldEntry) && isset($oldEntry['title_other']) ? $oldEntry['title_other'] : old("complaints.{$index}.title_other");
                            $priorityVal = is_array($oldEntry) && isset($oldEntry['priority']) ? $oldEntry['priority'] : old("complaints.{$index}.priority", 'normal');
                            $availVal = is_array($oldEntry) && isset($oldEntry['availability_time']) ? $oldEntry['availability_time'] : old("complaints.{$index}.availability_time");
                            $empVal = is_array($oldEntry) && isset($oldEntry['assigned_employee_id']) ? $oldEntry['assigned_employee_id'] : old("complaints.{$index}.assigned_employee_id");
                            $descVal = is_array($oldEntry) && isset($oldEntry['description']) ? $oldEntry['description'] : old("complaints.{$index}.description");
                        @endphp
                        <!-- Complaint Entry #{{ $loop->iteration }} -->
                        <div class="complaint-entry" data-index="{{ $index }}">
                            <div class="complaint-entry-header d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-white fw-bold mb-0">
                                    <i data-feather="alert-triangle" class="me-2" style="width: 16px; height: 16px;"></i>
                                    <span class="complaint-number">Complaint #{{ $loop->iteration }}</span>
                                </h6>
                                <button type="button" class="btn btn-sm btn-remove-complaint" onclick="removeComplaint(this)" style="{{ count($oldComplaints) > 1 ? 'display: inline-flex;' : 'display: none;' }} background: rgba(220,53,69,0.2); color: #ff6b6b; border: 1px solid rgba(220,53,69,0.3); border-radius: 8px; padding: 4px 12px; font-size: 13px;">
                                    <i data-feather="x" style="width: 14px; height: 14px;"></i> Remove
                                </button>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-white">Category <span class="text-danger">*</span></label>
                                        <select name="complaints[{{ $index }}][category]" class="form-select complaint-category" required>
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $id => $name)
                                                <option value="{{ $id }}" {{ (string)$catVal === (string)$id ? 'selected' : '' }}>{{ ucfirst($name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-white">Complaint Type <span class="text-danger">*</span></label>
                                        <div class="title-dropdown-container">
                                            <select name="complaints[{{ $index }}][complaint_title_id]" class="form-select complaint-title" required autocomplete="off" data-old-value="{{ $titleVal }}">
                                                <option value="">Select Category First</option>
                                            </select>
                                        </div>
                                        <div class="title-input-container" style="{{ $titleOtherVal || (string)$titleVal === 'other' ? 'display: block;' : 'display: none;' }} position: relative;">
                                            <input type="text" class="form-control complaint-title-other" name="complaints[{{ $index }}][title_other]" value="{{ $titleOtherVal }}" placeholder="Enter custom title...">
                                            <button type="button" class="btn btn-sm btn-link text-white position-absolute end-0 top-0 mt-1 me-1 btn-back-to-select" title="Back to dropdown">
                                                <i data-feather="corner-up-left" style="width: 14px; height: 14px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-white">Priority <span class="text-danger">*</span></label>
                                        <select name="complaints[{{ $index }}][priority]" class="form-select complaint-priority" required>
                                            <option value="normal" {{ (string)$priorityVal === 'normal' ? 'selected' : '' }}>Normal</option>
                                            <option value="emergency" {{ (string)$priorityVal === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-white">Availability Time</label>
                                        <input type="datetime-local" class="form-control complaint-availability" name="complaints[{{ $index }}][availability_time]" value="{{ $availVal }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-white">Assign Employee</label>
                                        <select name="complaints[{{ $index }}][assigned_employee_id]" class="form-select complaint-employee">
                                            <option value="">Select Employee (Optional)</option>
                                            @if (isset($employees) && $employees->count() > 0)
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}"
                                                        data-category="{{ $employee->category_id ?? '' }}"
                                                        data-city="{{ $employee->city_id }}"
                                                        data-sector="{{ $employee->sector_id }}"
                                                        {{ (string)$empVal === (string)$employee->id ? 'selected' : '' }}>
                                                        {{ $employee->name }}@if($employee->designation) ({{ $employee->designation->name }})@endif</option>
                                                @endforeach
                                            @else
                                                <option value="" disabled>No employees available</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <div class="fixed-questions-container alert alert-info mb-3" style="display: none;">
                                            <strong><i data-feather="help-circle" class="me-2" style="width: 16px; height: 16px;"></i>Questions to ask:</strong>
                                            <p class="fixed-questions-text mb-0 mt-1"></p>
                                        </div>
                                        <label class="form-label text-white">Description</label>
                                        <textarea class="form-control complaint-description" name="complaints[{{ $index }}][description]" rows="3" autocomplete="off">{{ $descVal }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Add Another Complaint Button -->
                    <div class="text-center mb-4">
                        <button type="button" id="btn-add-complaint" class="btn d-inline-flex align-items-center gap-2" onclick="addComplaint()"
                            style="background: rgba(37, 99, 235, 0.15); color: #60a5fa; border: 1.5px dashed rgba(96, 165, 250, 0.4); border-radius: 10px; padding: 10px 24px; font-weight: 600; font-size: 14px; transition: all 0.3s ease;">
                            <i data-feather="plus-circle" style="width: 18px; height: 18px;"></i>
                            Add Another Complaint
                        </button>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.complaints.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-accent" id="btn-submit-complaints">Create Complaint</button>
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

        /* Complaint Entry Card Styles */
        .complaint-entry {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            position: relative;
            transition: all 0.3s ease;
        }
        .complaint-entry:hover {
            border-color: rgba(96, 165, 250, 0.3);
            background: rgba(255, 255, 255, 0.06);
        }
        .complaint-entry + .complaint-entry {
            margin-top: 8px;
        }
        .complaint-entry-header h6 {
            font-size: 15px;
        }
        .btn-remove-complaint:hover {
            background: rgba(220,53,69,0.35) !important;
            color: #ff4444 !important;
        }
        #btn-add-complaint:hover {
            background: rgba(37, 99, 235, 0.25) !important;
            border-color: rgba(96, 165, 250, 0.6) !important;
            transform: translateY(-1px);
        }
        .complaint-entry.removing {
            animation: fadeSlideOut 0.3s ease forwards;
        }
        @keyframes fadeSlideOut {
            to { opacity: 0; transform: translateY(-10px); max-height: 0; margin: 0; padding: 0; overflow: hidden; }
        }
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .complaint-entry.adding {
            animation: fadeSlideIn 0.3s ease forwards;
        }
    </style>
@endpush

@push('scripts')
    <!-- jQuery -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" integrity="sha384-d3UHjPdzJkZuk5H3qKYMLRyWLAQBJbby2yr2Q58hXXtAGF8RSNO9jpLDlKKPv5v3" crossorigin="anonymous"></script>
    <script>
        // ============================================
        // Global: Complaint entry counter
        // ============================================
        let complaintCounter = 1; // starts at 1, index 0 already exists

        // ============================================
        // Add a new complaint entry
        // ============================================
        function addComplaint() {
            const container = document.getElementById('complaints-container');
            const firstEntry = container.querySelector('.complaint-entry');
            const newEntry = firstEntry.cloneNode(true);
            const newIndex = complaintCounter;
            complaintCounter++;

            // Update data-index
            newEntry.setAttribute('data-index', newIndex);

            // Update complaint number
            const numberSpan = newEntry.querySelector('.complaint-number');
            if (numberSpan) {
                numberSpan.textContent = 'Complaint #' + (container.querySelectorAll('.complaint-entry').length + 1);
            }

            // Show remove button
            const removeBtn = newEntry.querySelector('.btn-remove-complaint');
            if (removeBtn) removeBtn.style.display = 'inline-flex';

            // Reset all field values and update names
            newEntry.querySelectorAll('select').forEach(sel => {
                const oldName = sel.getAttribute('name');
                if (oldName) {
                    sel.setAttribute('name', oldName.replace(/complaints\[\d+\]/, 'complaints[' + newIndex + ']'));
                }
                sel.value = '';
                sel.disabled = false;
            });

            newEntry.querySelectorAll('input').forEach(inp => {
                const oldName = inp.getAttribute('name');
                if (oldName) {
                    inp.setAttribute('name', oldName.replace(/complaints\[\d+\]/, 'complaints[' + newIndex + ']'));
                }
                inp.value = '';
            });

            newEntry.querySelectorAll('textarea').forEach(ta => {
                const oldName = ta.getAttribute('name');
                if (oldName) {
                    ta.setAttribute('name', oldName.replace(/complaints\[\d+\]/, 'complaints[' + newIndex + ']'));
                }
                ta.value = '';
            });

            // Reset title dropdown to default state
            const titleDropdown = newEntry.querySelector('.title-dropdown-container');
            const titleInput = newEntry.querySelector('.title-input-container');
            if (titleDropdown) titleDropdown.style.display = 'block';
            if (titleInput) titleInput.style.display = 'none';

            // Reset title select to "Select Category First"
            const titleSelect = newEntry.querySelector('.complaint-title');
            if (titleSelect) {
                titleSelect.innerHTML = '<option value="">Select Category First</option>';
                titleSelect.required = true;
            }

            // Reset title other input
            const titleOther = newEntry.querySelector('.complaint-title-other');
            if (titleOther) titleOther.required = false;

            // Reset questions container
            const questionsContainer = newEntry.querySelector('.fixed-questions-container');
            if (questionsContainer) questionsContainer.style.display = 'none';

            // Filter employees based on current city/sector
            filterEmployeesInEntry(newEntry);

            // Add animation class
            newEntry.classList.add('adding');
            setTimeout(() => newEntry.classList.remove('adding'), 300);

            // Append
            container.appendChild(newEntry);

            // Attach event listeners for new entry
            attachEntryListeners(newEntry);

            // Update numbering and button text
            updateComplaintNumbers();
            updateSubmitButton();

            // Re-init feather icons
            if (typeof feather !== 'undefined') feather.replace();

            // Scroll to new entry
            newEntry.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // ============================================
        // Remove a complaint entry
        // ============================================
        function removeComplaint(btn) {
            const entry = btn.closest('.complaint-entry');
            const container = document.getElementById('complaints-container');

            if (container.querySelectorAll('.complaint-entry').length <= 1) {
                alert('At least one complaint is required.');
                return;
            }

            entry.classList.add('removing');
            setTimeout(() => {
                entry.remove();
                updateComplaintNumbers();
                updateSubmitButton();
            }, 300);
        }

        // ============================================
        // Update complaint numbering after add/remove
        // ============================================
        function updateComplaintNumbers() {
            const entries = document.querySelectorAll('#complaints-container .complaint-entry');
            entries.forEach((entry, i) => {
                const numberSpan = entry.querySelector('.complaint-number');
                if (numberSpan) numberSpan.textContent = 'Complaint #' + (i + 1);

                // Show/hide remove button (first entry can be removed if there are multiple)
                const removeBtn = entry.querySelector('.btn-remove-complaint');
                if (removeBtn) {
                    removeBtn.style.display = entries.length > 1 ? 'inline-flex' : 'none';
                }
            });
        }

        // ============================================
        // Update submit button text based on count
        // ============================================
        function updateSubmitButton() {
            const count = document.querySelectorAll('#complaints-container .complaint-entry').length;
            const btn = document.getElementById('btn-submit-complaints');
            if (btn) {
                btn.textContent = count > 1 ? 'Create ' + count + ' Complaints' : 'Create Complaint';
            }
        }

        // ============================================
        // Filter employees in a specific entry
        // ============================================
        function filterEmployeesInEntry(entry) {
            const citySelect = document.getElementById('city_id');
            const sectorSelect = document.getElementById('sector_id');
            const categorySelect = entry.querySelector('.complaint-category');
            const employeeSelect = entry.querySelector('.complaint-employee');
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

                const matchCategory = !category || !optCategory || String(optCategory) === String(category);
                const matchCity = !cityId || String(optCity) === String(cityId);
                const matchSector = !sectorId || String(optSector) === String(sectorId);

                const isSelected = currentSelectedId && String(opt.value) === String(currentSelectedId);
                const show = isSelected || (matchCategory && matchCity && matchSector);
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

        // ============================================
        // Load complaint titles for a specific entry
        // ============================================
        function loadTitlesForEntry(entry, selectedTitleId = null) {
            const categorySelect = entry.querySelector('.complaint-category');
            const titleSelect = entry.querySelector('.complaint-title');
            const titleOther = entry.querySelector('.complaint-title-other');
            const titleDropdown = entry.querySelector('.title-dropdown-container');
            const titleInputContainer = entry.querySelector('.title-input-container');

            if (!categorySelect || !titleSelect) return;

            const category = categorySelect.value;

            if (!category) {
                titleSelect.innerHTML = '<option value="">Select Category First</option>';
                titleSelect.disabled = false;
                return;
            }

            titleSelect.innerHTML = '<option value="">Loading titles...</option>';
            titleSelect.disabled = true;

            const url = `{{ route('admin.complaint-titles.by-category') }}?category=${encodeURIComponent(category)}`;

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                titleSelect.innerHTML = '<option value="">Select Complaint Title</option>';
                let matchedTitle = false;
                if (data && data.length > 0) {
                    data.sort((a, b) => (a.title || '').toLowerCase().localeCompare((b.title || '').toLowerCase(), undefined, { numeric: true }))
                        .forEach(title => {
                            const option = document.createElement('option');
                            option.value = title.id;
                            option.textContent = title.title;
                            if (title.description) option.setAttribute('title', title.description);
                            if (title.questions) option.setAttribute('data-questions', title.questions);
                            if (selectedTitleId && String(title.id) === String(selectedTitleId)) {
                                option.selected = true;
                                matchedTitle = true;
                            }
                            titleSelect.appendChild(option);
                        });
                } else {
                    titleSelect.innerHTML = '<option value="">No titles found</option>';
                }

                const otherOption = document.createElement('option');
                otherOption.value = 'other';
                otherOption.textContent = 'Other';
                if (selectedTitleId && (selectedTitleId === 'other' || (!matchedTitle && titleOther && titleOther.value))) {
                    otherOption.selected = true;
                }
                titleSelect.appendChild(otherOption);

                if (selectedTitleId && selectedTitleId !== 'other' && matchedTitle) {
                    titleSelect.value = selectedTitleId;
                }

                titleSelect.disabled = false;
                handleTitleChangeForEntry(entry);
            })
            .catch(error => {
                console.error('Error loading titles:', error);
                titleSelect.innerHTML = '<option value="">Failed to load titles</option>';
                titleSelect.disabled = false;
            });
        }

        // ============================================
        // Handle title change (other toggle) for entry
        // ============================================
        function handleTitleChangeForEntry(entry) {
            const titleSelect = entry.querySelector('.complaint-title');
            const titleOther = entry.querySelector('.complaint-title-other');
            const titleDropdown = entry.querySelector('.title-dropdown-container');
            const titleInputContainer = entry.querySelector('.title-input-container');
            const questionsContainer = entry.querySelector('.fixed-questions-container');
            const questionsText = entry.querySelector('.fixed-questions-text');

            if (!titleSelect || !titleOther) return;
            const selectedValue = titleSelect.value;

            // Reset questions
            if (questionsContainer) questionsContainer.style.display = 'none';
            if (questionsText) questionsText.textContent = '';

            if (selectedValue === 'other') {
                if (titleDropdown) titleDropdown.style.display = 'none';
                if (titleInputContainer) titleInputContainer.style.display = 'block';
                titleOther.style.display = 'block';
                titleOther.required = true;
                titleSelect.removeAttribute('required');
                setTimeout(() => titleOther.focus(), 100);
                if (typeof feather !== 'undefined') feather.replace();
            } else {
                if (titleDropdown) titleDropdown.style.display = 'block';
                if (titleInputContainer) titleInputContainer.style.display = 'none';
                titleOther.style.display = 'none';
                titleOther.required = false;
                titleSelect.required = true;

                // Show questions if available
                const selectedOption = titleSelect.options[titleSelect.selectedIndex];
                const questions = selectedOption ? selectedOption.getAttribute('data-questions') : null;

                if (questions && questionsContainer && questionsText) {
                    questionsText.textContent = questions;
                    questionsContainer.style.display = 'block';
                    if (typeof feather !== 'undefined') feather.replace();
                }
            }
        }

        // ============================================
        // Attach event listeners to an entry
        // ============================================
        function attachEntryListeners(entry) {
            const categorySelect = entry.querySelector('.complaint-category');
            const titleSelect = entry.querySelector('.complaint-title');
            const backBtn = entry.querySelector('.btn-back-to-select');

            if (categorySelect) {
                categorySelect.addEventListener('change', function() {
                    loadTitlesForEntry(entry);
                    filterEmployeesInEntry(entry);
                });
            }

            if (titleSelect) {
                titleSelect.addEventListener('change', function() {
                    handleTitleChangeForEntry(entry);
                });
            }

            if (backBtn) {
                backBtn.addEventListener('click', function() {
                    const ts = entry.querySelector('.complaint-title');
                    const to = entry.querySelector('.complaint-title-other');
                    if (ts) { ts.value = ''; handleTitleChangeForEntry(entry); }
                    if (to) to.value = '';
                });
            }
        }

        // ============================================
        // Main DOMContentLoaded
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            // Element Definitions (top-level / house section)
            const phoneInput = document.getElementById('phone');
            const clientNameInput = document.getElementById('complainant_name');
            const houseSelect = document.getElementById('house_id');
            const citySelect = document.getElementById('city_id');
            const sectorSelect = document.getElementById('sector_id');
            const addressInput = document.getElementById('address');
            const complaintForm = document.querySelector('form[action*="complaints"]');

            // Store original house options for filtering
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

            // Initialize Select2 for house
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

            // ---- Helper Functions ----

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

                        if (data.length === 1) {
                            sectorSelect.value = data[0].id;
                        }
                    } else {
                        sectorSelect.innerHTML = '<option value="">No GE Nodes found</option>';
                        sectorSelect.disabled = false;
                    }

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
                const currentSelectedId = $(houseSelect).val();

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

                $(houseSelect).trigger('change.select2');
            }

            // Filter employees in ALL complaint entries based on city/sector
            function filterAllEmployees() {
                document.querySelectorAll('#complaints-container .complaint-entry').forEach(entry => {
                    filterEmployeesInEntry(entry);
                });
            }

            // ---- Event Listeners ----

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
                    filterAllEmployees();
                });
            }

            if (sectorSelect) {
                sectorSelect.addEventListener('change', function() {
                    filterHouses();
                    filterAllEmployees();
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
                        filterAllEmployees();
                    }

                    if (address && addressInput) {
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

            // Form submission — validate all complaint entries
            if (complaintForm) {
                complaintForm.addEventListener('submit', function(e) {
                    // Phone validation
                    if (phoneInput && phoneInput.value.trim() && phoneInput.value.trim().length < 11) {
                        e.preventDefault();
                        alert('Phone number must be at least 11 digits.');
                        phoneInput.focus();
                        return false;
                    }

                    // Validate each complaint entry
                    const entries = document.querySelectorAll('#complaints-container .complaint-entry');
                    for (let i = 0; i < entries.length; i++) {
                        const entry = entries[i];
                        const num = i + 1;
                        const titleSelect = entry.querySelector('.complaint-title');
                        const titleOther = entry.querySelector('.complaint-title-other');
                        const titleInputContainer = entry.querySelector('.title-input-container');

                        // Check if "Other" title is selected but custom title is empty
                        if (titleSelect && (titleSelect.value === 'other' || (titleInputContainer && titleInputContainer.style.display !== 'none'))) {
                            if (!titleOther || !titleOther.value || titleOther.value.trim() === '') {
                                e.preventDefault();
                                alert('Complaint #' + num + ': Please enter a custom complaint title.');
                                if (titleOther) titleOther.focus();
                                return false;
                            }

                            // For "Other" entries: disable the title select so "other" is not sent as ID
                            const idx = entry.getAttribute('data-index');
                            titleSelect.removeAttribute('name');
                            titleSelect.removeAttribute('required');
                            titleSelect.disabled = true;

                            // Ensure title_other has the correct input name
                            if (titleOther) {
                                titleOther.name = 'complaints[' + idx + '][title_other]';
                            }
                        }
                    }
                });
            }

            // ---- Initial Setup ----

            // Attach listeners and restore state for all rendered complaint entries
            const allEntries = document.querySelectorAll('#complaints-container .complaint-entry');
            let maxIdx = 0;

            allEntries.forEach(entry => {
                attachEntryListeners(entry);

                const idx = parseInt(entry.getAttribute('data-index') || 0, 10);
                if (idx > maxIdx) maxIdx = idx;

                const categorySelect = entry.querySelector('.complaint-category');
                const titleSelect = entry.querySelector('.complaint-title');
                const oldTitleValue = titleSelect ? titleSelect.getAttribute('data-old-value') : null;
                const titleOther = entry.querySelector('.complaint-title-other');

                if (categorySelect && categorySelect.value) {
                    let titleToSelect = oldTitleValue;
                    if (!titleToSelect && titleOther && titleOther.value) {
                        titleToSelect = 'other';
                    }
                    loadTitlesForEntry(entry, titleToSelect);
                }
            });

            complaintCounter = maxIdx + 1;
            updateComplaintNumbers();
            updateSubmitButton();

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

            filterHouses();
            filterAllEmployees();

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

