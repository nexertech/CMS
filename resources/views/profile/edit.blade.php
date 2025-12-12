@extends('layouts.sidebar')

@section('title', 'Profile')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white mb-0">Profile</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active text-white">Profile</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Profile Information -->
    <div class="col-lg-8 mb-4">
      <div class="card-glass">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="user" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">Profile Information</h5>
        </div>
        
        @include('profile.partials.update-profile-information-form')
      </div>
    </div>

    <!-- Password Update -->
    <div class="col-lg-8 mb-4">
      <div class="card-glass">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="lock" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">Update Password</h5>
        </div>
        
        @include('profile.partials.update-password-form')
      </div>
    </div>

    <!-- Account Actions -->
    <div class="col-lg-8 mb-4">
      <div class="card-glass">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="trash-2" class="me-2 text-danger"></i>
          <h5 class="mb-0 text-white">Delete Account</h5>
        </div>
        
        @include('profile.partials.delete-user-form')
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
