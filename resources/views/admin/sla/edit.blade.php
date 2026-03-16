@extends('layouts.sidebar')

@section('title', 'Edit SLA Rule — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Edit SLA Rule</h2>
      <p class="text-light">Update SLA rule: {{ $sla->category->name ?? 'Unknown' }}</p>
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
                  <label for="category_id" class="form-label text-white">Complaint Category <span class="text-danger">*</span></label>
                  <select class="form-select @error('category_id') is-invalid @enderror" 
                          id="category_id" name="category_id" required>
                    <option value="">Select Complaint Category</option>
                    @foreach($complaintTypes as $id => $label)
                    <option value="{{ $id }}" {{ old('category_id', $sla->category_id) == $id ? 'selected' : '' }}>
                      {{ $label }}
                    </option>
                    @endforeach
                  </select>
                  @error('category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

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
                  <label for="status" class="form-label text-white">Status</label>
                  <select class="form-select @error('status') is-invalid @enderror" 
                          id="status" name="status">
                    <option value="1" {{ old('status', $sla->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', $sla->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
