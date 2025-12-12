# Laravel CMS - Theme System Documentation

## Overview

The Laravel CMS now includes a comprehensive theme system with three modes: Light, Dark, and Night. The system provides smooth transitions, accessibility features, and persistent user preferences.

## Features

### 🎨 Three Theme Modes
- **Light**: Standard bright UI for daytime use
- **Dark**: Neutral dark UI for comfortable night use  
- **Night**: Ultra-low blue light mode for late-night reading
- **Auto**: Follows system preference (prefers-color-scheme)

### 🔄 Smart Persistence
- User preferences saved to database (authenticated users)
- Cookie fallback for guest users
- Server-side rendering support
- Automatic system preference detection

### ♿ Accessibility Features
- WCAG AA contrast compliance
- Screen reader announcements
- Keyboard navigation support
- Reduced motion preference support
- High contrast mode support
- Skip links for navigation

### ✨ Polish Features
- Smooth color transitions
- Custom animated cursors per theme
- Glass morphism effects
- Responsive design
- Mobile-optimized

## File Structure

```
app/Http/Controllers/ThemeController.php          # Theme management controller
resources/views/components/theme-toggle.blade.php  # Theme toggle component
resources/css/themes.css                          # Theme CSS variables
resources/js/theme-manager.js                     # Theme JavaScript
database/migrations/..._add_theme_to_users_table.php # Database migration
```

## Installation & Setup

### 1. Database Migration
The migration has been created and run. It adds a `theme` column to the users table:

```php
$table->string('theme', 10)->default('auto');
```

### 2. Routes
Theme routes are automatically added:
```php
Route::post('/theme', [ThemeController::class, 'update'])->name('theme.update');
Route::get('/theme', [ThemeController::class, 'get'])->name('theme.get');
```

### 3. Layout Integration
Both layouts have been updated to support themes:

**layouts/sidebar.blade.php:**
```html
<html class="{{ App\Http\Controllers\ThemeController::getThemeClass(request()) }}">
```

**layouts/app.blade.php:**
```html
<html class="{{ App\Http\Controllers\ThemeController::getThemeClass(request()) }}">
```

### 4. CSS & JavaScript
Theme CSS and JavaScript are automatically included in layouts.

## Usage

### Theme Toggle Component
The theme toggle is automatically included in the navigation:

```blade
@include('components.theme-toggle')
```

### Server-Side Theme Detection
Get theme class for server-side rendering:

```php
$themeClass = ThemeController::getThemeClass(request());
// Returns: "theme-light", "theme-dark", "theme-night", or "theme-auto"
```

### JavaScript API
Access theme manager in JavaScript:

```javascript
// Get current theme
const currentTheme = window.themeManager.getCurrentTheme();

// Set theme programmatically
window.themeManager.setTheme('dark');

// Check if theme is supported
const isSupported = window.themeManager.isThemeSupported('night');
```

## CSS Custom Properties

The theme system uses CSS custom properties for all colors:

```css
:root {
  --bg-primary: #ffffff;
  --text-primary: #1e293b;
  --accent-primary: #3b82f6;
  /* ... and many more */
}
```

### Available Properties

#### Background Colors
- `--bg-primary`: Main background
- `--bg-secondary`: Secondary background  
- `--bg-tertiary`: Tertiary background
- `--bg-elevated`: Elevated surfaces (cards, modals)
- `--bg-overlay`: Overlay backgrounds

#### Text Colors
- `--text-primary`: Primary text
- `--text-secondary`: Secondary text
- `--text-tertiary`: Tertiary text
- `--text-muted`: Muted text
- `--text-inverse`: Inverse text

#### Accent Colors
- `--accent-primary`: Primary accent
- `--accent-secondary`: Secondary accent
- `--accent-success`: Success color
- `--accent-warning`: Warning color
- `--accent-error`: Error color
- `--accent-info`: Info color

#### Form Elements
- `--input-bg`: Input background
- `--input-border`: Input border
- `--input-focus`: Focus color
- `--input-text`: Input text
- `--input-placeholder`: Placeholder text

## Theme-Specific Styling

### Light Theme
- Clean, bright interface
- High contrast text
- Standard shadows
- Blue accent colors

### Dark Theme  
- Dark backgrounds
- Light text
- Subtle shadows
- Blue accent colors

### Night Theme
- Ultra-dark backgrounds
- Warm text colors (cream/gold)
- Minimal blue light
- Warm accent colors

## Accessibility Features

### Screen Reader Support
- Live region announcements for theme changes
- Proper ARIA attributes on toggle
- Semantic HTML structure

### Keyboard Navigation
- Full keyboard support for theme toggle
- Tab navigation through options
- Enter/Space key activation

### Visual Accessibility
- High contrast mode support
- Reduced motion preference
- Focus indicators
- Skip links

## Customization

### Adding New Themes
1. Add theme to `themes` array in `ThemeManager.js`
2. Add CSS variables in `themes.css`
3. Update theme toggle component
4. Add theme icon if needed

### Custom Colors
Override CSS custom properties in your stylesheets:

```css
.theme-custom {
  --bg-primary: #your-color;
  --text-primary: #your-color;
}
```

## Browser Support

- Modern browsers with CSS custom properties support
- Graceful degradation for older browsers
- Mobile browser theme-color meta tag support

## Performance

- CSS variables for efficient theme switching
- Minimal JavaScript footprint
- Lazy loading of theme assets
- Optimized transitions

## Troubleshooting

### Theme Not Persisting
- Check CSRF token is present
- Verify user authentication
- Check browser cookie settings

### Styles Not Applying
- Ensure `themes.css` is loaded
- Check for CSS conflicts
- Verify theme class on `<html>` element

### JavaScript Errors
- Check console for errors
- Verify `theme-manager.js` is loaded
- Ensure Bootstrap is loaded for dropdowns

## Future Enhancements

- Theme preview mode
- Custom theme builder
- Theme sharing between users
- Advanced accessibility options
- Performance optimizations

## Support

For issues or questions about the theme system:
1. Check browser console for errors
2. Verify all files are properly included
3. Test with different browsers
4. Check accessibility compliance

---

**Note**: This theme system is designed to be maintainable and extensible. All components follow Laravel best practices and modern web standards.
