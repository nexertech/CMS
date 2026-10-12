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
  
  .topbar .backup-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 36px;
    padding: 0 14px;
    border-radius: 9999px;
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.12) 0%, rgba(2, 132, 199, 0.18) 100%);
    border: 1px solid rgba(56, 189, 248, 0.4);
    color: #38bdf8 !important;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.2px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none !important;
  }
  
  .topbar .backup-btn i,
  .topbar .backup-btn svg {
    width: 13px;
    height: 13px;
    stroke-width: 2.2;
    stroke: currentColor;
    transition: transform 0.2s ease;
  }
  
  .topbar .backup-btn:hover {
    background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%) !important;
    border-color: #38bdf8 !important;
    box-shadow: 0 4px 14px rgba(14, 165, 233, 0.4);
    transform: translateY(-1px);
  }

  .topbar .backup-btn:hover,
  .topbar .backup-btn:hover *,
  .topbar .backup-btn:hover span,
  .topbar .backup-btn:hover i,
  .topbar .backup-btn:hover svg {
    color: #ffffff !important;
    stroke: #ffffff !important;
  }
  
  .topbar .backup-btn:hover i,
  .topbar .backup-btn:hover svg {
    transform: scale(1.1);
  }

  /* Executive User Pill Button */
  .topbar .user-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 36px;
    padding: 3px 12px 3px 4px;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid rgba(255, 255, 255, 0.14);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.08);
    color: #f8fafc;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
    text-decoration: none;
  }
  
  .topbar .user-pill-btn:hover {
    background: rgba(255, 255, 255, 0.14);
    border-color: rgba(59, 130, 246, 0.45);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(59, 130, 246, 0.25);
    transform: translateY(-1px);
    color: #ffffff;
  }
  
  .topbar .user-pill-btn:focus,
  .topbar .user-pill-btn[aria-expanded="true"] {
    background: rgba(255, 255, 255, 0.16);
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
  }
  
  .topbar .user-pill-btn .user-avatar-wrap {
    position: relative;
    display: inline-flex;
  }
  
  .topbar .user-pill-btn .avatar-sm {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #ffffff;
    font-weight: 700;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(29, 78, 216, 0.35);
  }
  
  .topbar .user-pill-btn .user-status-dot {
    position: absolute;
    bottom: -1px;
    right: -1px;
    width: 8px;
    height: 8px;
    background: #10b981;
    border: 1.5px solid #1e293b;
    border-radius: 50%;
  }
  
  .topbar .user-pill-btn .user-info {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.15;
  }
  
  .topbar .user-pill-btn .user-name {
    font-size: 12px;
    font-weight: 600;
    color: #ffffff;
    max-width: 120px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .topbar .user-pill-btn .user-role-badge {
    font-size: 9.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #94a3b8;
    margin-top: 1px;
  }
  
  .topbar .user-pill-btn .user-chevron {
    width: 13px;
    height: 13px;
    stroke-width: 2.2;
    color: #94a3b8;
    transition: transform 0.2s ease, color 0.2s ease;
    margin-left: 2px;
  }
  
  .topbar .user-pill-btn:hover .user-chevron,
  .topbar .user-pill-btn[aria-expanded="true"] .user-chevron {
    color: #ffffff;
  }
  
  .topbar .user-pill-btn[aria-expanded="true"] .user-chevron {
    transform: rotate(180deg);
  }
  
  .user-avatar .avatar-lg {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: bold;
    width: 40px;
    height: 40px;
    font-size: 16px;
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
  

  
  /* Executive User Dropdown */
  .user-dropdown {
    width: 290px !important;
    padding: 8px !important;
    border-radius: 14px !important;
    border: 1px solid rgba(59, 130, 246, 0.25) !important;
    background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%) !important;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.05) !important;
    margin-top: 10px !important;
    animation: userDropdownFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  }

  @keyframes userDropdownFadeIn {
    from {
      opacity: 0;
      transform: translateY(6px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .user-header-card {
    padding: 12px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(30, 41, 59, 0.6) 100%);
    border: 1px solid rgba(59, 130, 246, 0.2);
    margin-bottom: 6px;
    list-style: none;
  }

  .avatar-lg-wrap .avatar-lg {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #ffffff;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(29, 78, 216, 0.4);
    border: 2px solid rgba(255, 255, 255, 0.2);
    letter-spacing: -0.5px;
  }

  .user-header-info {
    min-width: 0;
    flex: 1;
  }

  .user-header-name {
    font-size: 13.5px;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .user-header-email {
    font-size: 11.5px;
    color: #94a3b8;
    line-height: 1.2;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .user-header-badge {
    display: inline-block;
    padding: 2px 7px;
    font-size: 9.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: 6px;
    background: rgba(59, 130, 246, 0.25);
    color: #93c5fd;
    border: 1px solid rgba(59, 130, 246, 0.35);
  }

  .dropdown-divider-wrap {
    padding: 2px 4px;
    list-style: none;
  }

  .dropdown-divider-wrap .dropdown-divider {
    border-color: rgba(255, 255, 255, 0.08);
    margin: 4px 0;
  }

  .user-menu-item {
    display: flex !important;
    align-items: center;
    gap: 12px;
    padding: 8px 10px !important;
    border-radius: 8px !important;
    color: #e2e8f0 !important;
    text-decoration: none;
    transition: all 0.18s ease;
    margin-bottom: 2px;
    background: transparent;
    border: none;
    width: 100%;
    text-align: left;
  }

  .user-menu-item:hover {
    background: rgba(59, 130, 246, 0.14) !important;
    color: #ffffff !important;
    transform: translateX(3px);
  }

  .menu-item-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.06);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    transition: all 0.18s ease;
    flex-shrink: 0;
  }

  .menu-item-icon i {
    width: 14px;
    height: 14px;
  }

  .user-menu-item:hover .menu-item-icon {
    background: #2563eb;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
  }

  .menu-item-text {
    display: flex;
    flex-direction: column;
    min-width: 0;
  }

  .menu-item-title {
    font-size: 12.5px;
    font-weight: 600;
    line-height: 1.2;
  }

  .menu-item-desc {
    font-size: 10.5px;
    color: #94a3b8;
    line-height: 1.2;
    margin-top: 1px;
  }

  .user-menu-item:hover .menu-item-desc {
    color: #cbd5e1;
  }

  /* Logout special styling */
  .user-logout-item {
    color: #f87171 !important;
  }

  .user-logout-item .menu-item-icon.logout-icon {
    background: rgba(239, 68, 68, 0.1);
    color: #f87171;
  }

  .user-logout-item .menu-item-desc {
    color: #fca5a5;
    opacity: 0.75;
  }

  .user-logout-item:hover {
    background: rgba(239, 68, 68, 0.12) !important;
    color: #ef4444 !important;
  }

  .user-logout-item:hover .menu-item-icon.logout-icon {
    background: #ef4444;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
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

  /* Light theme User Pill & Backup Button */
  .theme-light .topbar .user-pill-btn {
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%) !important;
    border: 1px solid #cbd5e1 !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08), 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    color: #0f172a !important;
  }
  
  .theme-light .topbar .user-pill-btn:hover {
    background: #ffffff !important;
    border-color: #94a3b8 !important;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.1) !important;
    color: #0f172a !important;
  }
  
  .theme-light .topbar .user-pill-btn[aria-expanded="true"] {
    background: #ffffff !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
  }
  
  .theme-light .topbar .user-pill-btn .user-name {
    color: #0f172a !important;
  }
  
  .theme-light .topbar .user-pill-btn .user-role-badge {
    color: #64748b !important;
  }
  
  .theme-light .topbar .user-pill-btn .user-chevron {
    color: #64748b !important;
  }
  
  .theme-light .topbar .user-pill-btn .user-status-dot {
    border-color: #ffffff !important;
  }
  
  .theme-light .topbar .backup-btn {
    background: linear-gradient(180deg, #f0f9ff 0%, #e0f2fe 100%) !important;
    border: 1px solid rgba(14, 165, 233, 0.45) !important;
    box-shadow: 0 1px 3px rgba(14, 165, 233, 0.12) !important;
  }

  .theme-light .topbar .backup-btn i,
  .theme-light .topbar .backup-btn svg {
    stroke: #0284c7 !important;
  }
  
  .theme-light .topbar .backup-btn:hover {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
    border-color: #0284c7 !important;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35) !important;
  }

  .theme-light .topbar .backup-btn:hover,
  .theme-light .topbar .backup-btn:hover *,
  .theme-light .topbar .backup-btn:hover span,
  .theme-light .topbar .backup-btn:hover i,
  .theme-light .topbar .backup-btn:hover svg {
    color: #ffffff !important;
    stroke: #ffffff !important;
  }
  
  /* Light Theme User Dropdown */
  .theme-light .topbar .user-dropdown {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.15), 0 0 0 1px rgba(15, 23, 42, 0.05) !important;
  }

  .theme-light .topbar .user-header-card {
    background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%) !important;
    border: 1px solid #dbeafe !important;
  }

  .theme-light .topbar .user-header-name {
    color: #0f172a !important;
  }

  .theme-light .topbar .user-header-email {
    color: #64748b !important;
  }

  .theme-light .topbar .user-header-badge {
    background: #dbeafe !important;
    color: #1d4ed8 !important;
    border-color: #bfdbfe !important;
  }

  .theme-light .topbar .dropdown-divider-wrap .dropdown-divider {
    border-color: #f1f5f9 !important;
  }

  .theme-light .topbar .user-menu-item {
    color: #1e293b !important;
  }

  .theme-light .topbar .user-menu-item:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
  }

  .theme-light .topbar .menu-item-icon {
    background: #f1f5f9 !important;
    color: #64748b !important;
  }

  .theme-light .topbar .user-menu-item:hover .menu-item-icon {
    background: #2563eb !important;
    color: #ffffff !important;
  }

  .theme-light .topbar .menu-item-desc {
    color: #64748b !important;
  }

  .theme-light .topbar .user-logout-item {
    color: #dc2626 !important;
  }

  .theme-light .topbar .user-logout-item .menu-item-icon.logout-icon {
    background: #fee2e2 !important;
    color: #dc2626 !important;
  }

  .theme-light .topbar .user-logout-item .menu-item-desc {
    color: #ef4444 !important;
  }

  .theme-light .topbar .user-logout-item:hover {
    background: #fef2f2 !important;
    color: #b91c1c !important;
  }

  .theme-light .topbar .user-logout-item:hover .menu-item-icon.logout-icon {
    background: #dc2626 !important;
    color: #ffffff !important;
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

  /* Night theme User Pill & Backup Button */
  .theme-night .topbar .user-pill-btn {
    background: linear-gradient(180deg, #18181b 0%, #09090b 100%) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5) !important;
    color: #e5e5e5 !important;
  }
  
  .theme-night .topbar .user-pill-btn:hover {
    background: #27272a !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
  }
  
  .theme-night .topbar .user-pill-btn .user-name {
    color: #e5e5e5 !important;
  }
  
  .theme-night .topbar .user-pill-btn .user-role-badge {
    color: #9ca3af !important;
  }
  
  .theme-night .topbar .user-pill-btn .user-chevron {
    color: #9ca3af !important;
  }
  
  .theme-night .topbar .user-pill-btn .user-status-dot {
    border-color: #09090b !important;
  }
  
  .theme-night .topbar .backup-btn {
    background: rgba(14, 165, 233, 0.1) !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
    color: #38bdf8 !important;
  }
  
  .theme-night .topbar .backup-btn:hover {
    background: #0284c7 !important;
    border-color: #38bdf8 !important;
  }

  .theme-night .topbar .backup-btn:hover,
  .theme-night .topbar .backup-btn:hover *,
  .theme-night .topbar .backup-btn:hover span,
  .theme-night .topbar .backup-btn:hover i,
  .theme-night .topbar .backup-btn:hover svg {
    color: #ffffff !important;
    stroke: #ffffff !important;
  }
  
  /* Night Theme User Dropdown */
  .theme-night .topbar .user-dropdown {
    background: #09090b !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7) !important;
  }

  .theme-night .topbar .user-header-card {
    background: #18181b !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
  }

  .theme-night .topbar .user-header-name {
    color: #ffffff !important;
  }

  .theme-night .topbar .user-header-email {
    color: #a1a1aa !important;
  }

  .theme-night .topbar .user-header-badge {
    background: rgba(255, 255, 255, 0.1) !important;
    color: #e4e4e7 !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
  }

  .theme-night .topbar .dropdown-divider-wrap .dropdown-divider {
    border-color: rgba(255, 255, 255, 0.1) !important;
  }

  .theme-night .topbar .user-menu-item {
    color: #e4e4e7 !important;
  }

  .theme-night .topbar .user-menu-item:hover {
    background: #27272a !important;
    color: #ffffff !important;
  }

  .theme-night .topbar .menu-item-icon {
    background: rgba(255, 255, 255, 0.05) !important;
    color: #a1a1aa !important;
  }

  .theme-night .topbar .user-menu-item:hover .menu-item-icon {
    background: #3b82f6 !important;
    color: #ffffff !important;
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
      

      <!-- Theme Toggle -->
      @include('components.theme-toggle')

      <!-- Database Backup Button (Role ID 1 or Role Name 'admin') -->
      @if(auth()->check() && ((int)auth()->user()->role_id === 1 || strtolower(trim(auth()->user()->role->role_name ?? '')) === 'admin'))
      <a href="{{ route('admin.database.backup') }}" 
         class="backup-btn" 
         title="Download Complete Database Backup (.sql)">
        <i data-feather="database"></i>
        <span class="d-none d-sm-inline">DB Backup</span>
      </a>
      @endif

      <!-- User Profile Dropdown -->
      <div class="dropdown">
        <button class="user-pill-btn" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="user-avatar-wrap">
            <div class="avatar-sm">
              {{ strtoupper(substr(auth()->user()->full_name ?? 'U', 0, 1)) }}
            </div>
            <span class="user-status-dot"></span>
          </div>
          <div class="user-info text-start d-none d-md-flex">
            <span class="user-name">{{ auth()->user()->full_name ?? 'User' }}</span>
            <span class="user-role-badge">{{ auth()->user()->role->role_name ?? 'Admin' }}</span>
          </div>
          <i data-feather="chevron-down" class="user-chevron"></i>
        </button>
        
        <ul class="dropdown-menu dropdown-menu-end user-dropdown">
          <!-- Dropdown Header Hero Card -->
          <li class="user-header-card">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar-lg-wrap">
                <div class="avatar-lg">
                  {{ strtoupper(substr(auth()->user()->full_name ?? 'U', 0, 1)) }}
                </div>
              </div>
              <div class="user-header-info">
                <div class="user-header-name">{{ auth()->user()->full_name ?? 'User' }}</div>
                <div class="user-header-email" title="{{ auth()->user()->email ?? 'user@example.com' }}">{{ auth()->user()->email ?? 'user@example.com' }}</div>
                <div class="mt-1">
                  <span class="user-header-badge">{{ auth()->user()->role->role_name ?? 'Admin' }}</span>
                </div>
              </div>
            </div>
          </li>
          
          <li class="dropdown-divider-wrap"><hr class="dropdown-divider"></li>
          
          <!-- Menu Items -->
          <li>
            <a class="dropdown-item user-menu-item" href="{{ route('profile.edit') }}">
              <div class="menu-item-icon"><i data-feather="user"></i></div>
              <div class="menu-item-text">
                <span class="menu-item-title">My Profile</span>
                <span class="menu-item-desc">Account settings & info</span>
              </div>
            </a>
          </li>

          @if(auth()->user() && strtolower(auth()->user()->role->role_name ?? '') === 'admin')
          <li>
            <a class="dropdown-item user-menu-item" href="{{ route('admin.settings.index') }}">
              <div class="menu-item-icon"><i data-feather="settings"></i></div>
              <div class="menu-item-text">
                <span class="menu-item-title">System Settings</span>
                <span class="menu-item-desc">Configuration & controls</span>
              </div>
            </a>
          </li>
          @endif

          <li>
            <a class="dropdown-item user-menu-item" href="{{ route('admin.help.index') }}">
              <div class="menu-item-icon"><i data-feather="help-circle"></i></div>
              <div class="menu-item-text">
                <span class="menu-item-title">Help & Support</span>
                <span class="menu-item-desc">Documentation & guides</span>
              </div>
            </a>
          </li>

          <li class="dropdown-divider-wrap"><hr class="dropdown-divider"></li>

          <!-- Logout Item -->
          <li>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
              @csrf
              <button type="submit" class="dropdown-item user-menu-item user-logout-item">
                <div class="menu-item-icon logout-icon"><i data-feather="log-out"></i></div>
                <div class="menu-item-text">
                  <span class="menu-item-title">Sign Out</span>
                  <span class="menu-item-desc">End your active session</span>
                </div>
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

