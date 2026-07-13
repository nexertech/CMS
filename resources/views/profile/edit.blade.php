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
    <div class="col-lg-6 mb-4">
      <div class="profile-card-glass">
        <div class="d-flex align-items-center mb-4">
          <div class="profile-header-icon me-3">
            <i data-feather="user" style="width: 20px; height: 20px;"></i>
          </div>
          <div>
            <h5 class="mb-1 text-white fw-bold">Profile Information</h5>
            <p class="text-muted small mb-0">Update your account name and email address</p>
          </div>
        </div>
        
        @include('profile.partials.update-profile-information-form')
      </div>
    </div>

    <!-- Password Update -->
    <div class="col-lg-6 mb-4">
      <div class="profile-card-glass">
        <div class="d-flex align-items-center mb-4">
          <div class="profile-header-icon me-3">
            <i data-feather="lock" style="width: 20px; height: 20px;"></i>
          </div>
          <div>
            <h5 class="mb-1 text-white fw-bold">Update Password</h5>
            <p class="text-muted small mb-0">Change password to keep your account secure</p>
          </div>
        </div>
        
        @include('profile.partials.update-password-form')
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  /* Premium Glassmorphic Inputs */
  .profile-form-control {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 10px !important;
    color: #ffffff !important;
    padding: 11px 15px !important;
    font-size: 0.95rem !important;
    transition: all 0.25s ease-in-out !important;
  }
  .profile-form-control:focus {
    background: rgba(255, 255, 255, 0.1) !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
    outline: none !important;
    color: #ffffff !important;
  }
  
  /* Card Design */
  .profile-card-glass {
    background: rgba(255, 255, 255, 0.03) !important;
    backdrop-filter: blur(15px) !important;
    -webkit-backdrop-filter: blur(15px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 18px !important;
    padding: 30px !important;
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2) !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease !important;
    height: 100%;
  }
  .profile-card-glass:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.3) !important;
  }
  
  /* Header Icon Wrapper */
  .profile-header-icon {
    background: rgba(59, 130, 246, 0.15) !important;
    color: #3b82f6 !important;
    border-radius: 12px !important;
    padding: 10px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
  }

  /* Form Labels */
  .form-label {
    font-size: 0.88rem !important;
    letter-spacing: 0.3px !important;
    margin-bottom: 6px !important;
  }
</style>
@endpush

@push('scripts')
<script>
  feather.replace();

  document.addEventListener('DOMContentLoaded', function() {
    // Only allow numbers in phone input
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
      phoneInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
      });
    }

    // Profile info form validation
    const profileForm = document.querySelector('form[action*="profile"]');
    if (profileForm) {
      profileForm.setAttribute('novalidate', '');
      profileForm.addEventListener('submit', function(e) {
        const errors = [];
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = phoneInput ? phoneInput.value.trim() : '';

        if (!name) {
          errors.push('Name is required.');
        }
        if (!email) {
          errors.push('Email is required.');
        }
        if (phone && phone.length < 11) {
          errors.push('Phone number must be at least 11 digits.');
        }

        if (errors.length > 0) {
          e.preventDefault();
          alert("Validation Errors:\n\n- " + errors.join("\n- "));
          return false;
        }
      });
    }

    // Password update form validation
    const passwordForm = document.querySelector('form[action*="password"]');
    if (passwordForm) {
      passwordForm.setAttribute('novalidate', '');
      passwordForm.addEventListener('submit', function(e) {
        const errors = [];
        const currentPassword = document.getElementById('update_password_current_password').value;
        const newPassword = document.getElementById('update_password_password').value;
        const confirmPassword = document.getElementById('update_password_password_confirmation').value;

        if (!currentPassword) {
          errors.push('Current Password is required.');
        }
        if (!newPassword) {
          errors.push('New Password is required.');
        } else if (newPassword.length < 8) {
          errors.push('New Password must be at least 8 characters long.');
        }
        if (newPassword && newPassword !== confirmPassword) {
          errors.push('New Password and Confirm Password do not match.');
        }

        if (errors.length > 0) {
          e.preventDefault();
          alert("Validation Errors:\n\n- " + errors.join("\n- "));
          return false;
        }
      });
    }

    // Show server-side validation errors in popup if any
    @if($errors->any() || $errors->updatePassword->any())
      const serverErrors = [];
      @if($errors->any())
        @foreach($errors->all() as $error)
          serverErrors.push("{!! addslashes($error) !!}");
        @endforeach
      @endif
      @if($errors->updatePassword->any())
        @foreach($errors->updatePassword->all() as $error)
          serverErrors.push("{!! addslashes($error) !!}");
        @endforeach
      @endif
      alert("Validation Errors:\n\n- " + serverErrors.join("\n- "));
    @endif
  });
</script>
@endpush
