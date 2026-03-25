@extends('layouts.sidebar')

@section('title', 'Edit Complaint — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<!-- <div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="text-white mb-2">Edit Complaint</h2>
            <p class="text-light">Modify complaint details</p>
        </div>
    </div>
</div> -->

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
                        <input type="text" class="form-control @error('complainant_name') is-invalid @enderror" id="complainant_name" name="complainant_name" value="{{ old('complainant_name', $complaint->house->name ?? '') }}" placeholder="Enter name">
                        @error('complainant_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="address" class="form-label text-white">Address</label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror" id="client_address" name="address" value="{{ old('address', $complaint->house->address ?? '') }}" placeholder="Enter address">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="phone" class="form-label text-white">Phone No.</label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="client_phone" name="phone" value="{{ old('phone', $complaint->house->phone ?? '') }}" placeholder="Enter phone number">
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
                <a href="{{ request('redirect_to', route('admin.complaints.index')) }}" 
                   class="btn btn-outline-secondary"
                   onclick="if(typeof closeComplaintModal === 'function') { event.preventDefault(); closeComplaintModal(); }">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Complaint</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@include('admin.complaints.partials.form_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.initializeComplaintForm();
});
</script>
@endpush
