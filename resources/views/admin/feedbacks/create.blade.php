@extends('layouts.sidebar')

@section('title', 'Add Feedback — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-3">
  <div>
    <h5 class="text-white mb-1">Add Complainant Feedback</h5>
    <p class="text-light small mb-0">Enter feedback received from complainant via phone</p>
  </div>
</div>

<!-- COMPLAINT INFO -->
<div class="row justify-content-center mb-3">
  <div class="col-12">
    <div class="card-glass">
      <div class="card-header">
        <h5 class="card-title mb-0 text-white">
          <i data-feather="file-text" class="me-2"></i>Complaint Information
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <table class="table table-borderless">
              <tr>
                <td class="text-white"><strong>Complaint ID:</strong></td>
                <td class="text-white">{{ str_pad($complaint->complaint_id ?? $complaint->id, 4, '0', STR_PAD_LEFT) }}</td>
              </tr>
              <tr>
                <td class="text-white"><strong>Title:</strong></td>
                <td class="text-white">{{ $complaint->title ?? 'N/A' }}</td>
              </tr>
              <tr>
                <td class="text-white"><strong>Complainant:</strong></td>
                <td class="text-white">{{ $complaint->client->client_name ?? 'N/A' }}</td>
              </tr>
              <tr>
                <td class="text-white"><strong>Address:</strong></td>
                <td class="text-white">{{ $complaint->client->address ?? 'N/A' }}</td>
              </tr>
              <tr>
                <td class="text-white"><strong>Phone:</strong></td>
                <td class="text-white">{{ $complaint->client->phone ?? 'N/A' }}</td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-borderless">
              <tr>
                <td class="text-white"><strong>Status:</strong></td>
                <td>
                  <span class="badge bg-success" style="color: #ffffff !important;">Resolved</span>
                </td>
              </tr>
              <tr>
                <td class="text-white"><strong>Resolved Date:</strong></td>
                <td class="text-white">{{ $complaint->closed_at ? $complaint->closed_at->format('M d, Y H:i:s') : 'N/A' }}</td>
              </tr>
              @php
                $geUser = null;
                if ($complaint->city) {
                  $city = \App\Models\City::where('name', $complaint->city)->first();
                  if ($city) {
                    $geUser = \App\Models\User::where('city_id', $city->id)
                      ->whereHas('role', function($q) {
                        $q->where('role_name', 'garrison_engineer');
                      })
                      ->first();
                  }
                }
              @endphp
              @if($geUser)
              <tr>
                <td class="text-white"><strong>GE (City):</strong></td>
                <td class="text-white">{{ $geUser->name ?? $geUser->username ?? 'N/A' }}</td>
              </tr>
              @endif
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- FEEDBACK FORM -->
<div class="row justify-content-center">
  <div class="col-12">
    <div class="card-glass">
      <div class="card-header">
        <h5 class="card-title mb-0 text-white">
          <i data-feather="message-circle" class="me-2"></i>Complainant Feedback Form
        </h5>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.feedback.store', $complaint->id) }}" method="POST">
          @csrf
          
          <div class="row">
            <!-- Overall Rating -->
            <div class="col-md-6 mb-3">
              <label class="form-label text-white fw-bold mb-1" style="font-size: 0.9rem;">
                Overall Rating <span class="text-danger">*</span>
              </label>
              <select class="form-select @error('overall_rating') is-invalid @enderror" name="overall_rating" required>
                <option value="">Select Rating</option>
                <option value="excellent" {{ old('overall_rating') == 'excellent' ? 'selected' : '' }}>Excellent ⭐⭐⭐⭐⭐</option>
                <option value="good" {{ old('overall_rating') == 'good' ? 'selected' : '' }}>Good ⭐⭐⭐⭐</option>
                <option value="average" {{ old('overall_rating') == 'average' ? 'selected' : '' }}>Average ⭐⭐⭐</option>
                <option value="poor" {{ old('overall_rating') == 'poor' ? 'selected' : '' }}>Poor ⭐⭐</option>
              </select>
              @error('overall_rating')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Rating Score -->
            <div class="col-md-6 mb-3">
              <label class="form-label text-white fw-bold mb-1" style="font-size: 0.9rem;">
                Rating Score (1-5)
              </label>
              <input type="number" class="form-control @error('rating_score') is-invalid @enderror" 
                     name="rating_score" min="1" max="5" value="{{ old('rating_score') }}" 
                     placeholder="Enter score (1-5)">
              @error('rating_score')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Comments -->
          <div class="mb-3">
            <label class="form-label text-white fw-bold mb-1" style="font-size: 0.9rem;">
              Complainant Comments
            </label>
            <textarea class="form-control @error('comments') is-invalid @enderror" 
                      name="comments" rows="3" 
                      placeholder="Enter complainant feedback/comments...">{{ old('comments') }}</textarea>
            @error('comments')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Submit Buttons -->
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
              <i data-feather="save" class="me-2"></i>Save Feedback
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  feather.replace();
</script>
@endpush

