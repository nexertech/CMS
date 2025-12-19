@extends('frontend.layouts.app')

@section('title', 'Navy Complaint Management System - Login')

@push('styles')
<style>
  body {
    margin: 0;
    font-family: 'Inter', Arial, sans-serif;
    background: url('{{ asset('assests/Background.jpg') }}') no-repeat center center/cover;
    background-attachment: fixed;
    position: relative;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }
  
  
  /* Dark overlay for better readability */
  body::after {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0, 31, 63, 0.3) 0%, rgba(0, 51, 102, 0.35) 50%, rgba(0, 77, 153, 0.3) 100%);
    z-index: 1;
    pointer-events: none;
  }
  
  /* Hide default navbar and footer */
  .nav-spacer {
    display: none !important;
  }

  /* Footer styling for home page */
  footer {
    text-align: center !important;
    width: 100% !important;
    display: block !important;
    position: relative !important;
    margin-top: auto !important;
    margin-bottom: 0 !important;
    z-index: 10;
  }

  footer .container {
    text-align: center !important;
    margin: 0 auto !important;
    width: 100% !important;
    max-width: 100% !important;
    padding-left: 15px !important;
    padding-right: 15px !important;
  }

  footer .text-center {
    text-align: center !important;
    width: 100% !important;
  }

  footer p {
    text-align: center !important;
    margin: 0 auto !important;
    width: 100% !important;
    display: block !important;
  }

  main {
    padding: 0 !important;
    margin: 0 !important;
    padding-top: 25px !important;
    position: relative;
    z-index: 2;
    flex: 1;
  }

  /* Custom navbar for home page */
  .home-navbar {
    text-align: center;
    padding: 30px 30px;
    font-size: 16px;
    color: #fff;
    letter-spacing: 2px;
    position: relative;
    z-index: 10;
    width: 100%;
    margin-bottom: 40px;
  }

  .home-navbar a {
    margin: 0 25px;
    color: #fff;
    text-decoration: none;
    font-weight: bold;
    transition: opacity 0.3s ease;
  }

  .home-navbar a:hover {
    opacity: 0.8;
  }

  .container {
    width: 90%;
    max-width: 1100px;
    margin: 15px auto;
    margin-top: 75px;
    padding: 0;
    display: flex;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    position: relative;
    z-index: 10;
  }

  .left-section {
    flex: 1.3;
    min-height: 420px;
    background: url('{{ asset('assests/slider1.jpg') }}') no-repeat center center/cover;
    background-size: cover;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    transition: background-image 0.5s ease;
    border-radius: 20px 0 0 20px;
  }

  .left-section.default-bg {
    background: url('{{ asset('assests/slider1.jpg') }}') no-repeat center center/cover;
  }

  .left-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0, 31, 63, 0.15), rgba(0, 51, 102, 0.1));
    z-index: 1;
  }


  .right-section {
    flex: 0.7;
    background: #fff;
    padding: 40px 35px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border-radius: 0 20px 20px 0;
  }


  .logo {
    text-align: center;
    margin-bottom: 20px;
    margin-top: -10px;
  }

  .logo img {
    width: 120px;
    height: auto;
    display: block;
    margin: 0 auto;
  }

  .logo svg {
    width: 120px;
    height: 120px;
    display: block;
    margin: 0 auto;
  }

  .heading {
    text-align: center;
    font-size: 28px !important;
    font-weight: 700 !important;
    margin-top: 15px;
    margin-bottom: 5px;
    color: #003366;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1.3;
  }

  .subtitle {
    text-align: center;
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 30px;
    font-weight: 400;
  }

  .form {
    margin-top: 20px;
  }

  .form-group {
    margin-top: 15px;
  }

  .form-group label {
    display: block;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
    font-size: 14px;
  }

  input[type="email"],
  input[type="password"],
  input[type="text"] {
    width: 100%;
    padding: 12px 14px;
    margin-top: 6px;
    border-radius: 8px;
    border: 2px solid #3b82f6;
    background-color: #eff6ff;
    font-size: 15px;
    box-sizing: border-box;
    transition: all 0.3s ease;
    color: #1e293b;
  }

  input[type="email"]:focus,
  input[type="password"]:focus,
  input[type="text"]:focus {
    outline: none;
    border-color: #2563eb;
    background-color: #ffffff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  }

  .remember {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    margin-top: 10px;
  }

  .remember label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #495057;
    cursor: pointer;
    margin: 0;
  }

  .remember input[type="checkbox"] {
    width: auto;
    margin: 0;
    cursor: pointer;
  }

  .remember a {
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
  }

  .remember a:hover {
    color: #1d4ed8;
    text-decoration: underline;
  }

  .sign-btn {
    width: 100%;
    margin-top: 25px;
    padding: 12px;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
  }

  .sign-btn:hover {
    background: #1d4ed8;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
  }

  .sign-btn:active {
    transform: translateY(0);
  }

  .home-footer {
    text-align: center;
    color: white;
    margin: 30px 0;
    font-size: 14px;
    position: relative;
    z-index: 10;
    padding: 20px 0;
    width: 100%;
  }

  .subtitle {
    text-align: center;
    color: #6c757d;
    margin-top: 10px;
    font-size: 0.95rem;
    margin-bottom: 25px;
  }

  /* Responsive */
  @media (max-width: 991.98px) {
    .container {
      flex-direction: column;
      width: 95%;
      margin: 20px auto;
    }

    .left-section {
      min-height: 300px;
    }

    .right-section {
      padding: 30px;
    }

    .navbar a {
      margin: 0 15px;
      font-size: 16px;
    }
  }

  @media (max-width: 576px) {
    .navbar {
      padding: 15px;
    }

    .navbar a {
      margin: 0 10px;
      font-size: 14px;
    }

    .right-section {
      padding: 25px;
    }

    .heading {
      font-size: 22px !important;
    }

    .image-slider {
      gap: 10px;
      padding: 10px;
    }

    .image-slider img {
      width: 70px;
      height: 50px;
    }
  }
</style>
@endpush

@section('content')
<div style="display: flex; flex-direction: column; min-height: calc(100vh - 100px);">

  <div class="container">                                                                                                            
    <div class="left-section" id="leftSection" @auth('frontend') style="border-radius: 20px; min-height: 650px;" @endauth>
    </div>

    @guest('frontend')
    <div class="right-section">
        <div class="logo">
            <img src="{{ asset('assests/logo.png') }}" alt="Pakistan Navy Emblem" style="width: 120px; height: 120px; object-fit: contain;" />
        </div>
        <div class="heading">MES COMPLAINT MANAGEMENT SYSTEM</div>
        <p class="subtitle">Nice to see you again</p>

        <form method="POST" action="{{ route('frontend.login.post') }}" class="form">
            @csrf
            @if ($errors->any())
                <div style="padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; background: #fee; color: #c33; border: 1px solid #fcc; font-size: 14px;">
                    {{ $errors->first() }}
          </div>
            @endif

            <div class="form-group">
                <label>Login</label>
                <input type="text" name="username" placeholder="Username" value="{{ old('username') }}" required autofocus />
        </div>
        
            <div class="form-group">
                <label>Password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password" placeholder="Enter password" required style="padding-right: 40px;" />
                    <i data-feather="eye" id="togglePassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; width: 18px; height: 18px; color: #6c757d;"></i>
          </div>
        </div>
        
            <div class="remember">
                <label>
                    <input type="checkbox" name="remember" id="remember" />
                    Remember me
                </label>
                <a href="{{ route('frontend.forgot-password') }}">Forgot password?</a>
      </div>
      
            <button type="submit" class="sign-btn">Sign In</button>
        </form>
    </div>
    @endguest
  </div>
</div>

@push('scripts')
<script>
  // Password toggle functionality
  document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword && passwordInput) {
      togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Update icon
        if (typeof feather !== 'undefined') {
          feather.replace();
        }
      });
    }
    
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
      feather.replace();
    }

  });

</script>
@endpush
@endsection
