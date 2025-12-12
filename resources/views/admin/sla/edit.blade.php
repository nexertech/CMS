@extends('layouts.sidebar')

@section('title', 'Edit SLA Rule — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Edit SLA Rule</h2>
      <p class="text-light">Update SLA rule: {{ $sla->complaint_type }}</p>
    </div>
   
  </div>
</div>

<!-- SLA RULE FORM -->
<div class="card-glass">
  <div class="card-header">
    <h5 class="card-title mb-0 text-white">
      <i data-feather="clock" class="me-2"></i>SLA Rule Information
    </h5>
  </div>
  <div class="card-body">
          <form action="{{ route('admin.sla.update', $sla) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="complaint_type" class="form-label text-white">Complaint Type <span class="text-danger">*</span></label>
                  <select class="form-select @error('complaint_type') is-invalid @enderror" 
                          id="complaint_type" name="complaint_type" required>
                    <option value="">Select Complaint Type</option>
                    @foreach($complaintTypes as $key => $label)
                    <option value="{{ $key }}" {{ old('complaint_type', $sla->complaint_type) == $key ? 'selected' : '' }}>
                      {{ $label }}
                    </option>
                    @endforeach
                  </select>
                  @error('complaint_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="max_response_time" class="form-label text-white">Max Response Time (Hours) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control @error('max_response_time') is-invalid @enderror" 
                         id="max_response_time" name="max_response_time" value="{{ old('max_response_time', $sla->max_response_time) }}" required>
                  @error('max_response_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="max_resolution_time" class="form-label text-white">Max Resolution Time (Hours) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control @error('max_resolution_time') is-invalid @enderror" 
                         id="max_resolution_time" name="max_resolution_time" value="{{ old('max_resolution_time', $sla->max_resolution_time) }}" required>
                  @error('max_resolution_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="notify_to" class="form-label text-white">Notify To</label>
                  <select class="form-select @error('notify_to') is-invalid @enderror" 
                          id="notify_to" name="notify_to">
                    <option value="">Select User</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('notify_to', $sla->notify_to) == $user->id ? 'selected' : '' }}>
                      {{ $user->username }} ({{ $user->role->role_name ?? 'No Role' }})
                    </option>
                    @endforeach
                  </select>
                  @error('notify_to')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="priority" class="form-label text-white">Priority <span class="text-danger">*</span></label>
                  <select class="form-select @error('priority') is-invalid @enderror" 
                          id="priority" name="priority" required>
                    <option value="">Select Priority</option>
                    <option value="low" {{ old('priority', $sla->priority ?? 'medium') == 'low' ? 'selected' : '' }}>Low - Can wait</option>
                    <option value="medium" {{ old('priority', $sla->priority ?? 'medium') == 'medium' ? 'selected' : '' }}>Medium - Normal</option>
                    <option value="high" {{ old('priority', $sla->priority ?? 'medium') == 'high' ? 'selected' : '' }}>High - Important</option>
                    <option value="urgent" {{ old('priority', $sla->priority ?? 'medium') == 'urgent' ? 'selected' : '' }}>Urgent - Critical</option>
                  </select>
                  @error('priority')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="status" class="form-label text-white">Status</label>
                  <select class="form-select @error('status') is-invalid @enderror" 
                          id="status" name="status">
                    <option value="active" {{ old('status', $sla->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $sla->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                  </select>
                  @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <div class="mb-3">
                  <label for="description" class="form-label text-white">Description</label>
                  <textarea class="form-control @error('description') is-invalid @enderror" 
                            id="description" name="description" rows="3">{{ old('description', $sla->description) }}</textarea>
                  @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="{{ route('admin.sla.index') }}" class="btn btn-outline-secondary">Cancel</a>
              <button type="submit" class="btn btn-outline-secondary"><i data-feather="save" class="me-2"></i>Update SLA Rule</button>
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
