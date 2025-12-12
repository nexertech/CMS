<!-- Logo - Separate from Navbar -->
<div class="logo-container">
  <a href="{{ url('/') }}" class="logo-link">
    <div class="logo-wrapper">
      <img src="{{ asset('assests/logo.png') }}" alt="Logo" class="main-logo">
    </div>
  </a>
</div>

<nav class="navbar navbar-expand-lg navbar-dark"
  style="background: transparent !important; background-color: transparent !important; box-shadow: none !important;">
  <div class="container-fluid px-1">
    <!-- Brand Text Only -->
    <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
      <span class="fw-bold text-white"
        style="font-size: 2.0rem !important; font-weight: 700 !important; letter-spacing: 0.5px;">MES Complaint
        Management System</span>
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Main Navigation -->
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link px-3 text-white {{ request()->routeIs('frontend.home') ? 'active' : '' }}"
            href="{{ route('frontend.home') }}" style="font-weight: 500;">
            HOME
          </a>
        </li>
        @if(Auth::guard('frontend')->check())
          <li class="nav-item">
            <a class="nav-link px-3 text-white {{ request()->routeIs('frontend.dashboard') ? 'active' : '' }}"
              href="{{ route('frontend.dashboard') }}" style="font-weight: 500;">
              DASHBOARD
            </a>
          </li>
        @endif
      </ul>

      <!-- Auth Navigation -->
      <ul class="navbar-nav ms-3">
        @if(Auth::guard('frontend')->check())
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center px-3 text-white" href="#" id="userDropdown"
                role="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-weight: 500;">
                <div class="user-avatar me-2">
                  <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                    <path fill-rule="evenodd"
                      d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
                  </svg>
                </div>
                <span>{{ Auth::guard('frontend')->user()->name ?? 'Account' }}</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                <a class="dropdown-item py-2" href="{{ route('frontend.profile') }}">
                  <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                    <path fill-rule="evenodd"
                      d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
                  </svg>
                  Profile
                </a>
            </li>
            <li>
              <a class="dropdown-item py-2" href="{{ route('frontend.password') }}">
                <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                  <path
                    d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z" />
                </svg>
                Change Password
              </a>
            <li>
              <form method="POST" action="{{ route('frontend.logout') }}" class="m-0">
                @csrf
                <button class="dropdown-item py-2 w-100 text-start border-0 bg-transparent" type="submit"
                  style="cursor: pointer;">
                  <svg width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                      d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z" />
                    <path fill-rule="evenodd"
                      d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z" />
                  </svg>
                  Logout
                </button>
              </form>
            </li>
          </ul>
          </li>
        @endif
      </ul>
    </div>
  </div>
</nav>

<style>
  /* Logo Container - Separate from Navbar */
  .logo-container {
    position: absolute;
    top: -10px;
    left: 0;
    z-index: 1040;
    padding: 0px 15px;
  }

  .logo-link {
    display: block;
    text-decoration: none;
  }

  .logo-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    perspective: 1000px;
    transform-style: preserve-3d;
  }

  .main-logo {
    width: 120px;
    height: 120px;
    object-fit: contain;
    animation: pageFlip 6s ease-in-out infinite;
    transform-origin: center center;
    transform-style: preserve-3d;
    backface-visibility: visible;
    transition: transform 0.3s ease;
  }

  .main-logo:hover {
    transform: scale(1.1);
  }

  .navbar,
  .navbar.navbar-dark,
  .navbar.navbar-expand-lg {
    transition: all 0.3s ease;
    z-index: 1030;
    margin: 0 !important;
    padding: 0.75rem 0;
    padding-left: 130px !important;
    /* Space for logo */
    background: linear-gradient(135deg, #001f3f 0%, #003366 50%, #0066cc 100%) !important;
    background-size: cover !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
    border-bottom: none !important;
    position: absolute !important;
    top: 10px;
    left: 0;
    right: 0;
    width: 100%;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
  }

  .navbar-brand {
    transition: transform 0.3s ease;
    color: #ffffff !important;
  }

  .navbar-brand span {
    color: #ffffff !important;
  }

  .navbar-brand:hover {
    transform: scale(1.05);
  }

  @keyframes pageFlip {
    0% {
      transform: perspective(1000px) rotateY(0deg);
    }

    25% {
      transform: perspective(1000px) rotateY(90deg);
    }

    50% {
      transform: perspective(1000px) rotateY(180deg);
    }

    75% {
      transform: perspective(1000px) rotateY(90deg);
    }

    100% {
      transform: perspective(1000px) rotateY(0deg);
    }
  }

  .nav-link {
    position: relative;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    color: #ffffff !important;
  }

  .nav-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: 0;
    left: 50%;
    background-color: #ffd700;
    transition: all 0.3s ease;
    transform: translateX(-50%);
  }

  .nav-link:hover::after,
  .nav-link.active::after {
    width: 80%;
  }

  .nav-link.active {
    color: #ffd700 !important;
  }

  .nav-link:hover {
    color: #ffd700 !important;
  }

  .dropdown-menu {
    margin-top: 0.5rem;
    animation: fadeIn 0.3s ease;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .dropdown-item {
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
  }

  .dropdown-item:hover {
    background-color: #f8f9fa;
    padding-left: 1.5rem;
  }

  .dropdown-item[type="submit"]:hover {
    background-color: #fee2e2;
    color: #dc2626;
  }

  .user-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #003366 0%, #0066cc 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
  }

  .btn-light {
    background: white;
    color: #001f3f;
    border: none;
    font-weight: 600;
  }

  .btn-light:hover {
    background: #f8f9fa;
    color: #001f3f;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }

  @media (max-width: 991px) {
    .logo-container {
      padding: 5px 10px;
    }

    .main-logo {
      width: 80px;
      height: 80px;
    }

    .navbar {
      padding-left: 90px !important;
      /* Less space for smaller logo */
    }

    .nav-link::after {
      display: none;
    }

    .navbar-nav {
      padding-top: 1rem;
    }

    .nav-item {
      margin-bottom: 0.5rem;
    }
  }
</style>
