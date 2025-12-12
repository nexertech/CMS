@extends('frontend.layouts.app')

@section('title', 'Change Password - NAVY COMPLAINT MANAGEMENT SYSTEM')

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
    background: linear-gradient(135deg, #001f3f 0%, #003366 50%, #004d99 100%) !important;
    font-family: 'Inter', sans-serif;
    margin: 0;
    padding: 0;
    min-height: 100%;
    display: flex;
    flex-direction: column;
  }

  main {
    padding: 0 !important;
    margin: 0 !important;
    flex: 1;
  }

  /* Footer override - fixed at bottom */
  footer {
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
    position: fixed !important;
    bottom: 0 !important;
    left: 0 !important;
    width: 100% !important;
    z-index: 999 !important;
  }

  /* Override navbar to be visible */
  .navbar {
    background-image: url('https://img.freepik.com/premium-photo/dark-blue-ocean-surface-seen-from-underwater_629685-6504.jpg') !important;
    background-size: cover !important;
    background-position: center !important;
  }

  /* Password Page */
  .navy-password-page {
    min-height: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    margin-top: 90px;
    margin-bottom: 0;
    background: transparent;
    position: relative;
    overflow: hidden;
  }

  .navy-password-page::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
      radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
      radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.03) 0%, transparent 50%);
    pointer-events: none;
  }

  .navy-password-container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 600px;
  }

  .navy-password-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 0;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 51, 102, 0.1);
    position: relative;
    overflow: hidden;
  }

  .navy-password-header {
    background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
    padding: 2rem;
    text-align: center;
    position: relative;
    color: white;
  }

  .navy-password-header::after {
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

  .password-icon-wrapper {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    border: 2px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
  }

  .password-icon-wrapper i {
    font-size: 2.5rem;
    color: #ffffff;
  }

  .navy-password-header h5 {
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    font-size: 1.5rem;
  }

  .navy-password-body {
    padding: 3rem 2rem 2rem;
  }

  .form-label {
    font-weight: 600;
    color: #003366;
    margin-bottom: 0.5rem;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .form-control {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background-color: #f8fafc;
  }

  .form-control:focus {
    border-color: #003366;
    box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
    background-color: #ffffff;
  }

  .btn-update-password {
    background: linear-gradient(135deg, #003366 0%, #004d99 100%);
    border: none;
    padding: 0.75rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 8px;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 51, 102, 0.2);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
  }

  .btn-update-password:hover {
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
</style>

<div class="navy-password-page">
  <div class="navy-password-container">
    <div class="navy-password-card">
      <div class="navy-password-header">
        <div class="password-icon-wrapper">
          <i class="fas fa-key"></i>
        </div>
        <h5>Change Password</h5>
        <p class="mb-0 opacity-75 small">Secure your account with a new password</p>
      </div>
      
      <div class="navy-password-body">
        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        <form method="POST" action="{{ route('frontend.password.update') }}">
          @csrf

          <div class="mb-4">
            <label for="current_password" class="form-label">Current Password</label>
            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
              id="current_password" name="current_password" required placeholder="Enter current password">
            @error('current_password')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
            @enderror
          </div>

          <div class="mb-4">
            <label for="password" class="form-label">New Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
              name="password" required placeholder="Enter new password (min. 8 characters)">
            @error('password')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
            @enderror
          </div>

          <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
              required placeholder="Confirm new password">
          </div>

          <div class="pt-2 border-top">
            <button type="submit" class="btn btn-update-password">
              <i class="fas fa-lock"></i> Update Password
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
