@extends('layouts.sidebar')

@section('title', 'Settings')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white mb-0">Settings</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active text-white">Settings</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="background: rgba(22, 163, 74, 0.15); border: 1px solid rgba(22, 163, 74, 0.4); border-radius: 8px;">
      <div class="d-flex align-items-center">
        <i data-feather="check-circle" class="me-2" style="color: #4ade80;"></i>
        <span class="text-white">{{ session('success') }}</span>
      </div>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="row">
    <!-- General Settings -->
    <div class="col-lg-6 mb-4">
      <div class="profile-card-glass">
        <div class="d-flex align-items-center mb-4">
          <div class="profile-header-icon me-3">
            <i data-feather="settings" style="width: 20px; height: 20px;"></i>
          </div>
          <div>
            <h5 class="mb-1 text-white fw-bold">General Settings</h5>
            <p class="text-muted small mb-0">Configure your website parameters and defaults</p>
          </div>
        </div>
        
        <form method="POST" action="{{ route('admin.settings.general') }}">
          @csrf
          <div class="mb-3">
            <label for="site_name" class="form-label text-white fw-semibold">Site Name</label>
            <input type="text" class="form-control profile-form-control" id="site_name" name="site_name" 
                   value="CMS Admin" required>
          </div>
          
          <div class="mb-3">
            <label for="site_description" class="form-label text-white fw-semibold">Site Description</label>
            <textarea class="form-control profile-form-control" id="site_description" name="site_description" 
                      rows="3" placeholder="Enter site description..."></textarea>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="timezone" class="form-label text-white fw-semibold">Timezone</label>
              <select class="form-select profile-form-control" id="timezone" name="timezone" required>
                <option value="UTC">UTC</option>
                <option value="Asia/Karachi" selected>Asia/Karachi</option>
                <option value="America/New_York">America/New_York</option>
                <option value="Europe/London">Europe/London</option>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="date_format" class="form-label text-white fw-semibold">Date Format</label>
              <select class="form-select profile-form-control" id="date_format" name="date_format" required>
                <option value="Y-m-d">YYYY-MM-DD</option>
                <option value="d-m-Y" selected>DD-MM-YYYY</option>
                <option value="m/d/Y">MM/DD/YYYY</option>
              </select>
            </div>
          </div>
          
          <button type="submit" class="btn btn-primary mt-2">
            <i data-feather="save" class="me-1"></i>Save General Settings
          </button>
        </form>
      </div>
    </div>

    <!-- Notification Settings -->
    <div class="col-lg-6 mb-4">
      <div class="profile-card-glass">
        <div class="d-flex align-items-center mb-4">
          <div class="profile-header-icon me-3">
            <i data-feather="bell" style="width: 20px; height: 20px;"></i>
          </div>
          <div>
            <h5 class="mb-1 text-white fw-bold">Notification Settings</h5>
            <p class="text-muted small mb-0">Select and manage system alert preferences</p>
          </div>
        </div>
        
        <form method="POST" action="{{ route('admin.settings.notifications') }}">
          @csrf
          <div class="mb-4 mt-2">
            <div class="custom-check-card mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="email_notifications" 
                       name="email_notifications" value="1" checked>
                <label class="form-check-label text-white fw-medium ms-2" for="email_notifications">
                  Email Notifications
                  <span class="d-block text-muted small fw-normal mt-1">Receive system updates and reports via email</span>
                </label>
              </div>
            </div>
            
            <div class="custom-check-card mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="push_notifications" 
                       name="push_notifications" value="1" checked>
                <label class="form-check-label text-white fw-medium ms-2" for="push_notifications">
                  Push Notifications
                  <span class="d-block text-muted small fw-normal mt-1">Receive real-time browser push alerts</span>
                </label>
              </div>
            </div>
            
            <div class="custom-check-card mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="sms_notifications" 
                       name="sms_notifications" value="1">
                <label class="form-check-label text-white fw-medium ms-2" for="sms_notifications">
                  SMS Notifications
                  <span class="d-block text-muted small fw-normal mt-1">Receive urgent priority reports via SMS</span>
                </label>
              </div>
            </div>
          </div>
          
          <button type="submit" class="btn btn-primary">
            <i data-feather="save" class="me-1"></i>Save Notification Settings
          </button>
        </form>
      </div>
    </div>

    <!-- Security Settings -->
    <div class="col-lg-6 mb-4">
      <div class="profile-card-glass">
        <div class="d-flex align-items-center mb-4">
          <div class="profile-header-icon me-3">
            <i data-feather="shield" style="width: 20px; height: 20px;"></i>
          </div>
          <div>
            <h5 class="mb-1 text-white fw-bold">Security Settings</h5>
            <p class="text-muted small mb-0">Define password policy and authentication methods</p>
          </div>
        </div>
        
        <form method="POST" action="{{ route('admin.settings.security') }}">
          @csrf
          <div class="custom-check-card mb-4 mt-2">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="two_factor_auth" 
                     name="two_factor_auth" value="1">
              <label class="form-check-label text-white fw-medium ms-2" for="two_factor_auth">
                Enable Two-Factor Authentication
                <span class="d-block text-muted small fw-normal mt-1">Add an extra layer of security using Google Authenticator</span>
              </label>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="session_timeout" class="form-label text-white fw-semibold">Session Timeout</label>
            <select class="form-select profile-form-control" id="session_timeout" name="session_timeout" required>
              <option value="30">30 minutes</option>
              <option value="60" selected>1 hour</option>
              <option value="120">2 hours</option>
              <option value="240">4 hours</option>
              <option value="480">8 hours</option>
            </select>
          </div>
          
          <div class="mb-4">
            <label for="password_policy" class="form-label text-white fw-semibold">Password Policy</label>
            <select class="form-select profile-form-control" id="password_policy" name="password_policy" required>
              <option value="basic">Basic (6+ characters)</option>
              <option value="medium" selected>Medium (8+ characters, mixed case)</option>
              <option value="strong">Strong (12+ characters, special chars)</option>
            </select>
          </div>
          
          <button type="submit" class="btn btn-primary">
            <i data-feather="save" class="me-1"></i>Save Security Settings
          </button>
        </form>
      </div>
    </div>

    <!-- System Information -->
    <div class="col-lg-6 mb-4">
      <div class="profile-card-glass">
        <div class="d-flex align-items-center mb-4">
          <div class="profile-header-icon me-3">
            <i data-feather="info" style="width: 20px; height: 20px;"></i>
          </div>
          <div>
            <h5 class="mb-1 text-white fw-bold">System Information</h5>
            <p class="text-muted small mb-0">System frameworks details and environment statuses</p>
          </div>
        </div>
        
        <div class="row mb-4 mt-2">
          <div class="col-6">
            <div class="system-info-block text-center p-3">
              <div class="h3 mb-1 fw-bold" style="color: #3b82f6;">{{ phpversion() }}</div>
              <div class="text-muted small fw-semibold">PHP Version</div>
            </div>
          </div>
          <div class="col-6">
            <div class="system-info-block text-center p-3">
              <div class="h3 mb-1 fw-bold" style="color: #3b82f6;">{{ app()->version() }}</div>
              <div class="text-muted small fw-semibold">Laravel Version</div>
            </div>
          </div>
        </div>
        
        <div class="mt-3 py-2">
          <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-25">
            <span class="text-light fw-medium">Server Status</span>
            <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-2.5 py-1.5 rounded-pill">Online</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-25">
            <span class="text-light fw-medium">Database Status</span>
            <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-2.5 py-1.5 rounded-pill">Connected</span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="text-light fw-medium">Last Backup</span>
            <span class="text-muted small fw-semibold">2 hours ago</span>
          </div>
        </div>
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

  /* Custom Check Cards */
  .custom-check-card {
    background: rgba(255, 255, 255, 0.02) !important;
    border: 1px solid rgba(255, 255, 255, 0.05) !important;
    border-radius: 10px !important;
    padding: 15px !important;
    transition: all 0.2s ease !important;
  }
  .custom-check-card:hover {
    background: rgba(255, 255, 255, 0.04) !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
  }
  
  /* System Info Block */
  .system-info-block {
    background: rgba(59, 130, 246, 0.05) !important;
    border: 1px solid rgba(59, 130, 246, 0.15) !important;
    border-radius: 12px !important;
  }
</style>
@endpush

@push('scripts')
<script>
  feather.replace();
</script>
@endpush
