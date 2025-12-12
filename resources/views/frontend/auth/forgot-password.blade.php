@extends('frontend.layouts.app')

@section('title', 'Forgot Password - NAVY COMPLAINT MANAGEMENT SYSTEM')

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

  /* Forgot Password Page */
  .navy-forgot-page {
    min-height: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem 0.5rem 1rem;
    margin-top: 90px;
    margin-bottom: 0;
    background: transparent;
    position: relative;
    overflow: hidden;
  }

  .navy-forgot-page::before {
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

  .navy-forgot-container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 500px;
  }

  .navy-forgot-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 51, 102, 0.1);
    position: relative;
    overflow: hidden;
  }

  .navy-forgot-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--navy-primary), var(--navy-accent), var(--navy-light));
  }

  .navy-forgot-icon-wrapper {
    width: 70px;
    height: 70px;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, var(--navy-primary), var(--navy-light));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 30px rgba(0, 51, 102, 0.3);
    animation: pulse 2s ease-in-out infinite;
  }

  @keyframes pulse {
    0%, 100% {
      transform: scale(1);
      box-shadow: 0 10px 30px rgba(0, 51, 102, 0.3);
    }
    50% {
      transform: scale(1.05);
      box-shadow: 0 15px 40px rgba(0, 51, 102, 0.4);
    }
  }

  .navy-forgot-icon {
    font-size: 2rem;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
  }

  .navy-forgot-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--navy-primary);
    text-align: center;
    margin-bottom: 0.5rem;
  }

  .navy-forgot-message {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-left: 4px solid var(--navy-accent);
    border-radius: 12px;
    padding: 1.25rem;
    margin: 1rem 0;
    box-shadow: 0 5px 15px rgba(0, 102, 204, 0.1);
  }

  .navy-forgot-message-icon {
    font-size: 1.75rem;
    margin-bottom: 0.5rem;
    display: block;
    text-align: center;
  }

  .navy-forgot-message-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--navy-dark);
    margin-bottom: 0.5rem;
    text-align: center;
  }

  .navy-forgot-message-text {
    font-size: 0.9rem;
    color: #37474f;
    line-height: 1.5;
    text-align: center;
    margin-bottom: 0;
  }

  .navy-forgot-contact-box {
    background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy-primary) 100%);
    border-radius: 12px;
    padding: 1rem;
    margin-top: 1rem;
    text-align: center;
    color: white;
  }

  .navy-forgot-contact-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.35rem;
  }

  .navy-forgot-contact-text {
    font-size: 0.9rem;
    opacity: 0.9;
    margin: 0;
  }

  .navy-forgot-back-btn {
    width: 100%;
    background: var(--navy-primary);
    color: white;
    border: none;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    margin-top: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
  }

  .navy-forgot-back-btn:hover {
    background: var(--navy-dark);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 51, 102, 0.3);
    color: white;
  }

  .navy-forgot-footer {
    text-align: center;
    color: #6c757d;
    font-size: 0.8rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
  }

  .navy-forgot-footer-icon {
    display: inline-block;
    margin: 0 0.3rem;
  }

  /* Responsive */
  @media (max-width: 767.98px) {
    .navy-forgot-page {
      padding: 0.5rem;
      margin-top: 80px;
    }

    .navy-forgot-card {
      padding: 1.5rem 1.25rem;
    }

    .navy-forgot-title {
      font-size: 1.4rem;
    }

    .navy-forgot-icon-wrapper {
      width: 70px;
      height: 70px;
      margin-bottom: 1.25rem;
    }

    .navy-forgot-icon {
      font-size: 2rem;
    }

    .navy-forgot-message {
      padding: 1.25rem;
      margin: 1.25rem 0;
    }

    .navy-forgot-message-title {
      font-size: 1rem;
    }

    .navy-forgot-message-text {
      font-size: 0.9rem;
    }
  }
</style>

<!-- Forgot Password Page -->
<div class="navy-forgot-page">
  <div class="navy-forgot-container">
    <div class="navy-forgot-card">
      <!-- Icon -->
      <div class="navy-forgot-icon-wrapper">
        <span class="navy-forgot-icon">🔒</span>
      </div>

      <!-- Title -->
      <h1 class="navy-forgot-title">Forgot Your Password?</h1>

      <!-- Main Message -->
      <div class="navy-forgot-message">
        <span class="navy-forgot-message-icon">📧</span>
        <h2 class="navy-forgot-message-title">Contact Your Administration</h2>
        <p class="navy-forgot-message-text">
          For security reasons, password reset requests must be handled by your system administrator. 
          Please reach out to your IT department or administration team to reset your password.
        </p>
      </div>

      <!-- Contact Information Box -->
      <div class="navy-forgot-contact-box">
        <div class="navy-forgot-contact-title">Need Immediate Assistance?</div>
        <p class="navy-forgot-contact-text">
          Contact your system administrator or IT support team for password reset assistance.
        </p>
      </div>

      <!-- Back to Login Button -->
      <a href="{{ route('frontend.home') }}" class="navy-forgot-back-btn">
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
        </svg>
        Back to Login
      </a>

      <!-- Footer -->
      <div class="navy-forgot-footer">
        <span class="navy-forgot-footer-icon">🛡️</span>
        Your account security is our priority
        <span class="navy-forgot-footer-icon">🛡️</span>
      </div>
    </div>
  </div>
</div>

@endsection
