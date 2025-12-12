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

  <div class="row">
    <!-- General Settings -->
    <div class="col-lg-6 mb-4">
      <div class="card-glass">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="settings" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">General Settings</h5>
        </div>
        
        <form method="POST" action="{{ route('admin.settings.general') }}">
          @csrf
          <div class="mb-3">
            <label for="site_name" class="form-label text-white">Site Name</label>
            <input type="text" class="form-control" id="site_name" name="site_name" 
                   value="CMS Admin" required>
          </div>
          
          <div class="mb-3">
            <label for="site_description" class="form-label text-white">Site Description</label>
            <textarea class="form-control" id="site_description" name="site_description" 
                      rows="3" placeholder="Enter site description..."></textarea>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="timezone" class="form-label text-white">Timezone</label>
              <select class="form-select" id="timezone" name="timezone" required>
                <option value="UTC">UTC</option>
                <option value="Asia/Karachi" selected>Asia/Karachi</option>
                <option value="America/New_York">America/New_York</option>
                <option value="Europe/London">Europe/London</option>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="date_format" class="form-label text-white">Date Format</label>
              <select class="form-select" id="date_format" name="date_format" required>
                <option value="Y-m-d">YYYY-MM-DD</option>
                <option value="d-m-Y" selected>DD-MM-YYYY</option>
                <option value="m/d/Y">MM/DD/YYYY</option>
              </select>
            </div>
          </div>
          
          <button type="submit" class="btn btn-primary">
            <i data-feather="save" class="me-1"></i>Save General Settings
          </button>
        </form>
      </div>
    </div>

    <!-- Notification Settings -->
    <div class="col-lg-6 mb-4">
      <div class="card-glass">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="bell" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">Notification Settings</h5>
        </div>
        
        <form method="POST" action="{{ route('admin.settings.notifications') }}">
          @csrf
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="email_notifications" 
                   name="email_notifications" value="1" checked>
            <label class="form-check-label text-white" for="email_notifications">
              Email Notifications
            </label>
          </div>
          
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="push_notifications" 
                   name="push_notifications" value="1" checked>
            <label class="form-check-label text-white" for="push_notifications">
              Push Notifications
            </label>
          </div>
          
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="sms_notifications" 
                   name="sms_notifications" value="1">
            <label class="form-check-label text-white" for="sms_notifications">
              SMS Notifications
            </label>
          </div>
          
          <button type="submit" class="btn btn-primary">
            <i data-feather="save" class="me-1"></i>Save Notification Settings
          </button>
        </form>
      </div>
    </div>

    <!-- Security Settings -->
    <div class="col-lg-6 mb-4">
      <div class="card-glass">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="shield" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">Security Settings</h5>
        </div>
        
        <form method="POST" action="{{ route('admin.settings.security') }}">
          @csrf
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="two_factor_auth" 
                   name="two_factor_auth" value="1">
            <label class="form-check-label text-white" for="two_factor_auth">
              Enable Two-Factor Authentication
            </label>
          </div>
          
          <div class="mb-3">
            <label for="session_timeout" class="form-label text-white">Session Timeout (minutes)</label>
            <select class="form-select" id="session_timeout" name="session_timeout" required>
              <option value="30">30 minutes</option>
              <option value="60" selected>1 hour</option>
              <option value="120">2 hours</option>
              <option value="240">4 hours</option>
              <option value="480">8 hours</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label for="password_policy" class="form-label text-white">Password Policy</label>
            <select class="form-select" id="password_policy" name="password_policy" required>
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
      <div class="card-glass">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="info" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">System Information</h5>
        </div>
        
        <div class="row">
          <div class="col-6">
            <div class="text-center p-3">
              <div class="h4 text-primary mb-1">{{ phpversion() }}</div>
              <div class="text-muted small">PHP Version</div>
            </div>
          </div>
          <div class="col-6">
            <div class="text-center p-3">
              <div class="h4 text-primary mb-1">{{ app()->version() }}</div>
              <div class="text-muted small">Laravel Version</div>
            </div>
          </div>
        </div>
        
        <div class="mt-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-white">Server Status</span>
            <span class="badge bg-success">Online</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-white">Database Status</span>
            <span class="badge bg-success">Connected</span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="text-white">Last Backup</span>
            <span class="text-muted">2 hours ago</span>
          </div>
        </div>
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
