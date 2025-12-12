<!-- Simple Theme Toggle -->
<div class="theme-toggle-container">
  <div class="dropdown">
    <button class="btn btn-outline-light theme-toggle-btn" type="button" id="themeToggle" 
            data-bs-toggle="dropdown" aria-expanded="false">
      <i data-feather="moon" class="theme-icon"></i>
      <span class="theme-label">Theme</span>
    </button>
    
    <ul class="dropdown-menu dropdown-menu-end">
      <li>
        <button class="dropdown-item theme-option" data-theme="light">
          <i data-feather="sun" class="me-2"></i>Light
        </button>
      </li>
      <li>
        <button class="dropdown-item theme-option" data-theme="dark">
          <i data-feather="moon" class="me-2"></i>Dark
        </button>
      </li>
      <li>
        <button class="dropdown-item theme-option" data-theme="night">
          <i data-feather="star" class="me-2"></i>Night
        </button>
      </li>
    </ul>
  </div>
</div>

<style>
  .theme-toggle-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.4);
    color: #ffffff !important;
    font-weight: 500;
  }
  
  .theme-toggle-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(59, 130, 246, 0.6);
    color: #ffffff !important;
  }
  
  .theme-option {
    color: #cbd5e1;
    border: none;
    background: transparent;
    width: 100%;
    text-align: left;
    padding: 8px 16px;
  }
  
  .theme-option:hover {
    background: rgba(59, 130, 246, 0.1);
    color: #ffffff;
  }
  
  .theme-option.active {
    background: rgba(59, 130, 246, 0.15);
    color: #ffffff;
  }
  
  /* Light theme button styling */
  .theme-light .theme-toggle-btn {
    background: rgba(0, 0, 0, 0.08) !important;
    border-color: rgba(0, 0, 0, 0.2) !important;
    color: #1e293b !important;
  }
  
  .theme-light .theme-toggle-btn:hover {
    background: rgba(0, 0, 0, 0.12) !important;
    border-color: rgba(0, 0, 0, 0.3) !important;
    color: #0f172a !important;
  }
  
  .theme-light .theme-label {
    color: #1e293b !important;
  }
  
  .theme-light .dropdown-menu {
    background: #ffffff !important;
    border: 1px solid rgba(0, 0, 0, 0.1) !important;
    color: #1e293b !important;
  }
  
  .theme-light .theme-option {
    color: #1e293b !important;
  }
  
  .theme-light .theme-option:hover {
    background: rgba(59, 130, 246, 0.1) !important;
    color: #0f172a !important;
  }
</style>

<script>
// Simple theme switching - no flickering
document.addEventListener('DOMContentLoaded', function() {
  let currentTheme = 'dark'; // Default to dark
  
  // Get saved theme
  const savedTheme = localStorage.getItem('theme') || 'dark';
  applyTheme(savedTheme);
  
  // Apply theme
  function applyTheme(theme) {
    const html = document.documentElement;
    html.className = html.className.replace(/theme-\w+/g, '');
    html.classList.add(`theme-${theme}`);
    currentTheme = theme;
    localStorage.setItem('theme', theme);
    updateUI();
  }
  
  // Update UI
  function updateUI() {
    const options = document.querySelectorAll('.theme-option');
    options.forEach(option => {
      option.classList.toggle('active', option.dataset.theme === currentTheme);
    });
    
    const icon = document.querySelector('.theme-icon');
    if (currentTheme === 'light') {
      icon.setAttribute('data-feather', 'sun');
    } else if (currentTheme === 'night') {
      icon.setAttribute('data-feather', 'star');
    } else {
      icon.setAttribute('data-feather', 'moon');
    }
    feather.replace();
  }
  
  // Click handlers
  document.addEventListener('click', function(e) {
    if (e.target.closest('.theme-option')) {
      const theme = e.target.closest('.theme-option').dataset.theme;
      applyTheme(theme);
    }
  });
});
</script>
