<style>
  /* Topbar Styles */
  .topbar {
    background: linear-gradient(90deg, #1e293b 0%, #334155 100%);
    border-bottom: 1px solid rgba(59, 130, 246, 0.2);
    padding: 8px 20px;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1001;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    height: 50px;
  }
  
  .topbar .brand {
    color: #3b82f6;
    font-weight: 700;
    font-size: 16px;
    text-decoration: none;
  }
  
  .topbar .brand i {
    width: 16px;
    height: 16px;
  }
  
  .topbar .breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
  }
  
  .topbar .breadcrumb-item a {
    color: #cbd5e1;
    text-decoration: none;
  }
  
  .topbar .breadcrumb-item.active {
    color: #ffffff;
  }
  
  .topbar .search-box .form-control {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(59, 130, 246, 0.4);
    color: #fff;
    height: 32px;
    font-size: 13px;
    padding: 6px 12px 6px 35px;
    border-radius: 20px;
    transition: all 0.3s ease;
  }
  
  .topbar .search-box .form-control:focus {
    background: rgba(255,255,255,0.12);
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    outline: none;
  }
  
  .topbar .search-box .form-control::placeholder {
    color: #94a3b8;
    font-size: 13px;
    font-weight: 400;
  }
  
  .topbar .search-box {
    position: relative;
  }
  
  .topbar .search-box::before {
    content: '';
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'/%3E%3C/svg%3E") no-repeat center;
    background-size: 16px 16px;
    z-index: 10;
  }
  
  .topbar .btn {
    height: 32px;
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 20px;
    margin-left: 8px;
  }
  
  .topbar .btn-outline-light {
    border: 1px solid rgba(59, 130, 246, 0.4);
    background: transparent;
  }
  
  .topbar .btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.6);
  }
  
  .topbar .btn i {
    width: 14px;
    height: 14px;
  }
  
  .user-avatar .avatar-sm,
  .user-avatar .avatar-lg {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: bold;
  }
  
  .user-avatar .avatar-sm {
    width: 28px;
    height: 28px;
    font-size: 12px;
  }
  
  .user-avatar .avatar-lg {
    width: 40px;
    height: 40px;
    font-size: 16px;
  }
  
  .user-info .user-name {
    color: #ffffff;
    font-weight: 600;
    font-size: 12px;
  }
  
  .user-info .user-role {
    color: #94a3b8;
    font-size: 10px;
  }
  
  .dropdown-menu {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 8px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
  }
  
  .dropdown-item {
    color: #cbd5e1;
    padding: 8px 16px;
  }
  
  .dropdown-item:hover {
    background: rgba(59, 130, 246, 0.1);
    color: #ffffff;
  }
  
  .dropdown-header {
    color: #ffffff;
    font-weight: 600;
  }
  
  .dropdown-divider {
    border-color: rgba(59, 130, 246, 0.2);
  }
  
  .notification-dropdown {
    width: 320px;
    max-height: 400px;
    overflow-y: auto;
  }
  
  .user-dropdown {
    width: 280px;
  }
  
  /* Light theme topbar styles */
  .theme-light .topbar {
    background: linear-gradient(90deg, #ffffff 0%, #f8fafc 100%) !important;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1) !important;
  }
  
  .theme-light .topbar .brand {
    color: #1e293b !important;
  }
  
  .theme-light .topbar .breadcrumb-item a {
    color: #64748b !important;
  }
  
  .theme-light .topbar .breadcrumb-item.active {
    color: #1e293b !important;
  }
  
  .theme-light .topbar .search-box .form-control {
    background: rgba(0, 0, 0, 0.05) !important;
    border: 1px solid rgba(0, 0, 0, 0.2) !important;
    color: #1e293b !important;
  }
  
  .theme-light .topbar .search-box .form-control::placeholder {
    color: #64748b !important;
  }
  
  .theme-light .topbar .search-box .form-control:focus {
    background: rgba(0, 0, 0, 0.08) !important;
    border-color: #3b82f6 !important;
    color: #1e293b !important;
  }
  
  .theme-light .topbar .btn-outline-light {
    color: #1e293b !important;
    border-color: rgba(0, 0, 0, 0.2) !important;
  }
  
  .theme-light .topbar .btn-outline-light:hover {
    background: rgba(0, 0, 0, 0.05) !important;
    border-color: rgba(0, 0, 0, 0.3) !important;
    color: #0f172a !important;
  }
  
  .theme-light .topbar .user-name {
    color: #1e293b !important;
  }
  
  .theme-light .topbar .user-role {
    color: #64748b !important;
  }
  
  .theme-light .topbar .dropdown-menu {
    background: #ffffff !important;
    border: 1px solid rgba(0, 0, 0, 0.1) !important;
  }
  
  .theme-light .topbar .dropdown-item {
    color: #1e293b !important;
  }
  
  .theme-light .topbar .dropdown-item:hover {
    background: rgba(0, 0, 0, 0.05) !important;
    color: #0f172a !important;
  }
  
  .theme-light .topbar .dropdown-header {
    color: #1e293b !important;
  }
  
  .theme-light .topbar .text-muted {
    color: #64748b !important;
  }
  
  .theme-light .topbar .notification-dropdown {
    background: #ffffff !important;
    border: 1px solid rgba(0, 0, 0, 0.1) !important;
  }
  
  .theme-light .topbar .notification-dropdown .dropdown-header {
    color: #1e293b !important;
  }
  
  .theme-light .topbar .notification-dropdown .dropdown-item {
    color: #1e293b !important;
  }
  
  .theme-light .topbar .notification-dropdown .dropdown-item:hover {
    background: rgba(0, 0, 0, 0.05) !important;
    color: #0f172a !important;
  }
  
  .theme-light .topbar .badge {
    color: #ffffff !important;
  }
  
  /* Night theme topbar styles */
  .theme-night .topbar {
    background: linear-gradient(90deg, #000000 0%, #111111 100%) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
  }
  
  .theme-night .topbar .brand {
    color: #e5e5e5 !important;
  }
  
  .theme-night .topbar .breadcrumb-item a {
    color: #9ca3af !important;
  }
  
  .theme-night .topbar .breadcrumb-item.active {
    color: #e5e5e5 !important;
  }
  
  .theme-night .topbar .search-box .form-control {
    background: rgba(0, 0, 0, 0.8) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: #e5e5e5 !important;
  }
  
  .theme-night .topbar .search-box .form-control::placeholder {
    color: #9ca3af !important;
  }
  
  .theme-night .topbar .search-box .form-control:focus {
    background: rgba(0, 0, 0, 0.9) !important;
    border-color: #60a5fa !important;
    color: #e5e5e5 !important;
  }
  
  .theme-night .topbar .btn-outline-light {
    color: #e5e5e5 !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
  }
  
  .theme-night .topbar .btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
    color: #ffffff !important;
  }
  
  .theme-night .topbar .user-name {
    color: #e5e5e5 !important;
  }
  
  .theme-night .topbar .user-role {
    color: #9ca3af !important;
  }
  
  .theme-night .topbar .dropdown-menu {
    background: rgba(0, 0, 0, 0.95) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
  }
  
  .theme-night .topbar .dropdown-item {
    color: #e5e5e5 !important;
  }
  
  .theme-night .topbar .dropdown-item:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    color: #ffffff !important;
  }
  
  .theme-night .topbar .dropdown-header {
    color: #e5e5e5 !important;
  }
  
  .theme-night .topbar .text-muted {
    color: #9ca3af !important;
  }
  
  .theme-night .topbar .notification-dropdown {
    background: rgba(0, 0, 0, 0.95) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
  }
  
  .theme-night .topbar .notification-dropdown .dropdown-header {
    color: #e5e5e5 !important;
  }
  
  .theme-night .topbar .notification-dropdown .dropdown-item {
    color: #e5e5e5 !important;
  }
  
  .theme-night .topbar .notification-dropdown .dropdown-item:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    color: #ffffff !important;
  }
  
  .theme-night .topbar .badge {
    color: #ffffff !important;
  }
</style>

<!-- Topbar Component -->
<div class="topbar">
  <div class="d-flex justify-content-between align-items-center">
    <!-- Left side - Logo/Brand -->
    <div class="d-flex align-items-center">
      <div class="brand me-4">
        <i data-feather="zap" class="me-2"></i>
        <span>CMS Admin</span>
      </div>
      
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="d-none d-md-block">
        <ol class="breadcrumb mb-0">
          @if(isset($breadcrumbs))
            @foreach($breadcrumbs as $breadcrumb)
              <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                @if($loop->last)
                  {{ $breadcrumb['title'] }}
                @else
                  <a href="{{ $breadcrumb['url'] }}" class="text-decoration-none">{{ $breadcrumb['title'] }}</a>
                @endif
              </li>
            @endforeach
          @endif
        </ol>
      </nav>
    </div>

    <!-- Right side - User info and actions -->
    <div class="d-flex align-items-center gap-3">
      <!-- Search -->
      <div class="search-box d-none d-lg-block">
        <div class="input-group">
          <input type="text" class="form-control" placeholder="Search..." id="globalSearch"
                 style="width: 220px;">
          <button class="btn btn-outline-light" type="button" id="searchButton">
            <i data-feather="search"></i>
          </button>
        </div>
      </div>

      <!-- Theme Toggle -->
      @include('components.theme-toggle')

      <!-- Notifications -->
      <div class="position-relative">
        <button class="btn btn-outline-light position-relative" id="notificationBtn" data-bs-toggle="dropdown">
          <i data-feather="bell"></i>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationCount">
            0
          </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end notification-dropdown">
          <div class="dropdown-header">
            <h6 class="mb-0">Notifications</h6>
            <small class="text-muted">You have <span id="notificationTotal">0</span> new notifications</small>
          </div>
          <div class="dropdown-divider"></div>
          <div id="notificationList">
            <!-- Notifications will be loaded here -->
            <div class="text-center py-3 text-muted">
              <i data-feather="bell-off" class="feather-lg mb-2"></i>
              <div>No notifications</div>
            </div>
          </div>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item text-center" href="#" id="viewAllNotifications">
            View all notifications
          </a>
        </div>
      </div>

      <!-- User Profile Dropdown -->
      <div class="dropdown">
        <button class="btn btn-outline-light d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown">
          <div class="user-avatar me-2">
            <div class="avatar-sm">
              {{ substr(auth()->user()->full_name ?? 'U', 0, 1) }}
            </div>
          </div>
          <div class="user-info text-start d-none d-md-block">
            <div class="user-name">{{ auth()->user()->full_name ?? 'User' }}</div>
            <div class="user-role text-muted small">{{ auth()->user()->role->role_name ?? 'Admin' }}</div>
          </div>
          <i data-feather="chevron-down" class="ms-2"></i>
        </button>
        
        <ul class="dropdown-menu dropdown-menu-end user-dropdown">
          <li class="dropdown-header">
            <div class="d-flex align-items-center">
              <div class="user-avatar me-3">
                <div class="avatar-lg">
                  {{ substr(auth()->user()->full_name ?? 'U', 0, 1) }}
                </div>
              </div>
              <div>
                <div class="fw-bold">{{ auth()->user()->full_name ?? 'User' }}</div>
                <div class="text-muted small">{{ auth()->user()->email ?? 'user@example.com' }}</div>
              </div>
            </div>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item" href="{{ route('profile.edit') }}">
              <i data-feather="user" class="me-2"></i>Profile
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
              <i data-feather="settings" class="me-2"></i>Settings
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('password.edit') }}">
              <i data-feather="lock" class="me-2"></i>Change Password
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('admin.help.index') }}">
              <i data-feather="help-circle" class="me-2"></i>Help & Support
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
              @csrf
              <button type="submit" class="dropdown-item text-danger">
                <i data-feather="log-out" class="me-2"></i>Logout
              </button>
            </form>
          </li>
        </ul>
      </div>

      <!-- Mobile Menu Toggle -->
      <button class="btn btn-outline-light d-lg-none" id="sidebarToggle">
        <i data-feather="menu"></i>
      </button>
    </div>
  </div>
</div>

<script>
  // View all notifications link
  document.addEventListener('DOMContentLoaded', function() {
    const viewAllNotifications = document.getElementById('viewAllNotifications');
    if (viewAllNotifications) {
      viewAllNotifications.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = '{{ route("admin.notifications.index") }}';
      });
    }
  });
</script>
