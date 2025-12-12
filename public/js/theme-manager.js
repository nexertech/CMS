/**
 * Laravel CMS - Theme Manager
 * Handles theme switching, persistence, and accessibility
 */

class ThemeManager {
  constructor() {
    this.themes = ['light', 'dark', 'night', 'auto'];
    this.currentTheme = 'auto';
    this.isInitialized = false;
    this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    this.init();
  }

  /**
   * Initialize theme manager
   */
  init() {
    if (this.isInitialized) return;
    
    this.loadTheme();
    this.bindEvents();
    this.setupAccessibility();
    this.isInitialized = true;
    
    // Announce theme change for screen readers
    this.announceThemeChange();
  }

  /**
   * Load theme from cookie or system preference
   */
  loadTheme() {
    // Try to get theme from cookie first
    const cookieTheme = this.getCookie('theme');
    
    if (cookieTheme && this.themes.includes(cookieTheme)) {
      this.currentTheme = cookieTheme;
    } else {
      // Fallback to system preference
      this.currentTheme = this.getSystemPreference();
    }

    this.applyTheme(this.currentTheme);
    this.updateUI();
  }

  /**
   * Get system color scheme preference
   */
  getSystemPreference() {
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      return 'dark';
    }
    return 'light';
  }

  /**
   * Apply theme to document
   */
  applyTheme(theme) {
    const html = document.documentElement;
    
    // Remove existing theme classes
    this.themes.forEach(t => html.classList.remove(`theme-${t}`));
    
    // Apply new theme
    if (theme === 'auto') {
      const systemTheme = this.getSystemPreference();
      html.classList.add(`theme-${systemTheme}`);
      html.setAttribute('data-theme', systemTheme);
    } else {
      html.classList.add(`theme-${theme}`);
      html.setAttribute('data-theme', theme);
    }

    // Update meta theme-color for mobile browsers
    this.updateMetaThemeColor(theme);
    
    // Store in localStorage for immediate access
    localStorage.setItem('theme', theme);
    
    this.currentTheme = theme;
  }

  /**
   * Update meta theme-color for mobile browsers
   */
  updateMetaThemeColor(theme) {
    let metaThemeColor = document.querySelector('meta[name="theme-color"]');
    
    if (!metaThemeColor) {
      metaThemeColor = document.createElement('meta');
      metaThemeColor.name = 'theme-color';
      document.head.appendChild(metaThemeColor);
    }

    const colors = {
      light: '#ffffff',
      dark: '#1e293b',
      night: '#0a0a0a',
      auto: this.getSystemPreference() === 'dark' ? '#1e293b' : '#ffffff'
    };

    metaThemeColor.content = colors[theme] || colors.auto;
  }

  /**
   * Update theme toggle UI
   */
  updateUI() {
    const themeOptions = document.querySelectorAll('.theme-option');
    const themeIcons = {
      light: document.querySelector('.theme-icon-light'),
      dark: document.querySelector('.theme-icon-dark'),
      auto: document.querySelector('.theme-icon-auto')
    };

    // Update active state
    themeOptions.forEach(option => {
      const theme = option.dataset.theme;
      const isActive = theme === this.currentTheme;
      
      option.classList.toggle('active', isActive);
      option.setAttribute('aria-pressed', isActive);
      
      const checkIcon = option.querySelector('.theme-check');
      if (checkIcon) {
        checkIcon.classList.toggle('d-none', !isActive);
      }
    });

    // Update toggle button icon
    Object.values(themeIcons).forEach(icon => {
      if (icon) icon.classList.add('d-none');
    });

    if (themeIcons[this.currentTheme]) {
      themeIcons[this.currentTheme].classList.remove('d-none');
    } else {
      themeIcons.auto?.classList.remove('d-none');
    }

    // Update toggle button label
    const themeLabel = document.querySelector('.theme-label');
    if (themeLabel) {
      const labels = {
        light: 'Light',
        dark: 'Dark', 
        night: 'Night',
        auto: 'Auto'
      };
      themeLabel.textContent = labels[this.currentTheme] || 'Theme';
    }
  }

  /**
   * Set theme and persist
   */
  async setTheme(theme) {
    if (!this.themes.includes(theme)) {
      console.warn(`Invalid theme: ${theme}`);
      return;
    }

    // Apply theme immediately for better UX
    this.applyTheme(theme);
    this.updateUI();

    // Persist to server and cookie
    try {
      await this.persistTheme(theme);
    } catch (error) {
      console.error('Failed to persist theme:', error);
      // Theme is still applied locally, just not persisted
    }

    // Announce change for accessibility
    this.announceThemeChange();
  }

  /**
   * Persist theme to server and cookie
   */
  async persistTheme(theme) {
    // Set cookie
    this.setCookie('theme', theme, 365);

    // Send to server if authenticated
    if (this.csrfToken) {
      try {
        const response = await fetch('/theme', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({ theme })
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }
      } catch (error) {
        console.warn('Failed to sync theme with server:', error);
      }
    }
  }

  /**
   * Bind event listeners
   */
  bindEvents() {
    // Theme option clicks
    document.addEventListener('click', (e) => {
      const themeOption = e.target.closest('.theme-option');
      if (themeOption) {
        e.preventDefault();
        const theme = themeOption.dataset.theme;
        this.setTheme(theme);
      }
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      const themeOption = e.target.closest('.theme-option');
      if (themeOption && (e.key === 'Enter' || e.key === ' ')) {
        e.preventDefault();
        const theme = themeOption.dataset.theme;
        this.setTheme(theme);
      }
    });

    // System preference changes
    if (window.matchMedia) {
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
      mediaQuery.addEventListener('change', () => {
        if (this.currentTheme === 'auto') {
          this.applyTheme('auto');
          this.updateUI();
        }
      });
    }

    // Reduced motion preference
    if (window.matchMedia) {
      const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
      reducedMotion.addEventListener('change', () => {
        document.documentElement.style.setProperty(
          '--theme-transition',
          reducedMotion.matches ? 'all 0.1s ease' : 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
        );
      });
    }
  }

  /**
   * Setup accessibility features
   */
  setupAccessibility() {
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
      themeToggle.setAttribute('aria-haspopup', 'true');
      themeToggle.setAttribute('aria-expanded', 'false');
    }

    // Add live region for announcements
    if (!document.getElementById('theme-announcer')) {
      const announcer = document.createElement('div');
      announcer.id = 'theme-announcer';
      announcer.className = 'sr-only';
      announcer.setAttribute('aria-live', 'polite');
      announcer.setAttribute('aria-atomic', 'true');
      document.body.appendChild(announcer);
    }
  }

  /**
   * Announce theme change for screen readers
   */
  announceThemeChange() {
    const announcer = document.getElementById('theme-announcer');
    if (announcer) {
      const themeNames = {
        light: 'Light theme',
        dark: 'Dark theme',
        night: 'Night theme',
        auto: 'Auto theme'
      };
      
      announcer.textContent = `Theme changed to ${themeNames[this.currentTheme] || 'unknown theme'}`;
    }
  }

  /**
   * Get cookie value
   */
  getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
  }

  /**
   * Set cookie
   */
  setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/;SameSite=Lax`;
  }

  /**
   * Get current theme
   */
  getCurrentTheme() {
    return this.currentTheme;
  }

  /**
   * Check if theme is supported
   */
  isThemeSupported(theme) {
    return this.themes.includes(theme);
  }
}

// Initialize theme manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  window.themeManager = new ThemeManager();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ThemeManager;
}
