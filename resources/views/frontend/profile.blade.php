@extends('frontend.layouts.app')

@section('title', 'My Profile - NAVY COMPLAINT MANAGEMENT SYSTEM')

@section('content')
  <style>
    /* Navy Theme Colors */
    :root {
      --navy-primary: #003366;
      --navy-dark: #001f3f;
      --navy-light: #004d99;
      --navy-accent: #0066cc;
      --navy-gold: #ffd700;
    }

    html {
      height: 100%;
      margin: 0;
      padding: 0;
    }

    body {
      background-image: url('{{ asset('assests/Background.jpg') }}') !important;
      background-size: cover !important;
      background-position: center !important;
      background-attachment: fixed !important;
      font-family: 'Inter', sans-serif;
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      position: relative;
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 31, 63, 0.4); /* Reduced opacity for better visibility */
      z-index: -1;
      position: fixed;
    }

    main {
      padding: 0 !important;
      margin: 0 !important;
      flex: 1;
    }

    /* Footer - allow it to flow naturally if content is long */
    footer {
      margin-top: auto !important;
      position: relative !important;
      width: 100% !important;
      z-index: 999 !important;
    }

    /* Override navbar to be visible */
    .navbar {
      background-image: url('{{ asset('assests/Background.jpg') }}') !important;
      background-size: cover !important;
      background-position: center !important;
    }

    /* Profile Page */
    .navy-profile-page {
      min-height: auto;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0.5rem 1rem 3rem; /* Extra bottom padding for breathing room */
      margin-top: 80px;
      margin-bottom: 0;
      background: transparent;
      position: relative;
      overflow: hidden;
    }

    .navy-profile-page::before {
      display: none;
    }

    .navy-profile-container {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 1100px;
    }

    .navy-profile-card {
      background: #ffffff;
      border-radius: 20px;
      padding: 0;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
      border: 1px solid rgba(0, 51, 102, 0.1);
      position: relative;
      overflow: hidden;
    }

    .navy-profile-header {
      background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
      padding: 1rem 1rem 2rem;
      text-align: center;
      position: relative;
      color: white;
    }

    .navy-profile-header::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 40px;
      background: #ffffff;
      border-radius: 50% 50% 0 0 / 100% 100% 0 0;
      transform: translateY(50%);
    }

    .profile-icon-wrapper {
      width: 60px;
      height: 60px;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 0.5rem;
      border: 2px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .profile-icon-wrapper i {
      font-size: 1.5rem;
      color: #ffffff;
    }

    .navy-profile-header h5 {
      font-weight: 700;
      letter-spacing: 0.5px;
      margin-bottom: 0.25rem;
      font-size: 1.2rem;
    }

    .navy-profile-body {
      padding: 1.25rem 2rem;
    }

    .form-label {
      font-weight: 600;
      color: #003366;
      margin-bottom: 0.3rem;
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .form-control {
      border: 2px solid #e2e8f0;
      border-radius: 8px;
      padding: 0.65rem 0.75rem;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      background-color: #f8fafc;
    }

    .form-control:focus {
      border-color: #003366;
      box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
      background-color: #ffffff;
    }

    /* Custom Select Arrow */
    .select-wrapper {
      position: relative;
    }

    .select-wrapper::after {
      content: '▼';
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      pointer-events: none;
      color: #003366;
      font-size: 0.75rem;
    }

    .select-wrapper select {
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      padding-right: 2.5rem;
    }

    .btn-update {
      background: linear-gradient(135deg, #003366 0%, #004d99 100%);
      border: none;
      padding: 0.5rem 2rem;
      font-size: 0.95rem;
      font-weight: 600;
      border-radius: 8px;
      color: white;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 51, 102, 0.2);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.4rem;
    }

    .btn-update:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 51, 102, 0.3);
      background: linear-gradient(135deg, #004d99 0%, #003366 100%);
    }

    .alert-success {
      background: rgba(22, 163, 74, 0.1);
      border: 1px solid rgba(22, 163, 74, 0.2);
      color: #16a34a;
      border-radius: 8px;
      padding: 1rem;
      font-weight: 500;
      display: flex;
      align-items: center;
    }

    .alert-dismissible .btn-close {
      padding: 1.25rem;
    }

    @media (min-width: 992px) {
      .border-end-lg {
        border-right: 1px solid rgba(0, 51, 102, 0.1) !important;
      }
    }
  </style>

  <div class="navy-profile-page">
    <div class="navy-profile-container">
      <div class="navy-profile-card">
        <div class="navy-profile-header">
          <div class="profile-icon-wrapper">
            <i class="fas fa-user-circle"></i>
          </div>
          <h5>My Profile</h5>
          <p class="mb-0 opacity-75 small">Manage your personal information</p>
        </div>

        <div class="navy-profile-body">
          @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
              <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
              <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          <div class="row g-5">
            <!-- Left Side: Profile Information -->
            <div class="col-lg-6 border-end-lg">
              <div class="d-flex align-items-center mb-4">
                <div class="bg-navy-light p-2 rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: rgba(0, 51, 102, 0.1);">
                  <i class="fas fa-id-card text-navy-primary"></i>
                </div>
                <div>
                  <h6 class="mb-0 text-navy-primary font-weight-bold" style="font-weight: 700;">Account Information</h6>
                  <p class="mb-0 text-muted small">Update your basic profile details</p>
                </div>
              </div>

              <form method="POST" action="{{ route('frontend.profile.update') }}">
                @csrf
                <div class="mb-3">
                  <label for="username" class="form-label">User Name</label>
                  <input type="text" class="form-control @error('username') is-invalid @enderror" id="username"
                    name="username" value="{{ old('username', $user->username) }}" required
                    placeholder="Enter your username">
                  @error('username')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                  @enderror
                </div>
                
                <div class="mb-3">
                  <label for="name" class="form-label">Full Name</label>
                  <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                    value="{{ old('name', $user->name) }}" required placeholder="Enter your full name">
                  @error('name')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                  @enderror
                </div>


                <div class="mb-4">
                  <label for="phone" class="form-label">Phone Number</label>
                  <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone"
                    value="{{ old('phone', $user->phone) }}" placeholder="Enter your phone number">
                </div>

                <div class="d-grid mt-4">
                  <button type="submit" class="btn btn-update">
                    <i class="fas fa-save"></i> Update Profile
                  </button>
                </div>
              </form>
            </div>

            <!-- Right Side: Security Settings -->
            <div class="col-lg-6">
              <div class="d-flex align-items-center mb-4">
                <div class="bg-navy-light p-2 rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: rgba(0, 51, 102, 0.1);">
                  <i class="fas fa-shield-alt text-navy-primary"></i>
                </div>
                <div>
                  <h6 class="mb-0 text-navy-primary font-weight-bold" style="font-weight: 700;">Security Settings</h6>
                  <p class="mb-0 text-muted small">Maintain account security</p>
                </div>
              </div>

              <form method="POST" action="{{ route('frontend.password.update') }}">
                @csrf
                <div class="mb-3">
                  <label for="current_password" class="form-label">Current Password</label>
                  <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required placeholder="Current Password">
                </div>

                <div class="mb-3">
                  <label for="password" class="form-label">New Password</label>
                  <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="New Password">
                </div>

                <div class="mb-4">
                  <label for="password_confirmation" class="form-label">Confirm Password</label>
                  <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Confirm Password">
                </div>

                <div class="d-grid mt-4">
                  <button type="submit" class="btn btn-update">
                    <i class="fas fa-lock"></i> Update Password
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
