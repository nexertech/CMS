# CMS App — Official Release & Deployment Handoff

**Application:** Navy Complaint Management CMS  
**Repository:** `https://github.com/nexertech/CMS.git`  
**Release Tag:** `v1.2.5`  
**Target Branch:** `deploy`  
**Previous Live Tag:** `v1.2.4`  
**Developer:** Amjad Subhani  
**Deployment Engineer:** Siddique / Deployment Team  
**Date:** 2026-09-28  

---

## 1. The 3 Mandatory Answers (fill these first)

| Question | Answer |
|---|---|
| **1. Release Tag?** | **`v1.2.5`** *(Immutable tag to be pushed to the `deploy` branch)* |
| **2. Database Change Classification?** | **None** *(No schema or table migrations required for this release)* |
| **3. Primary Page(s) to Test First?** | 1. `/login` & `/register` (Verify register link is removed and direct `/register` returns 404)<br>2. `/admin/complaints` (Click "Print Slip" on any complaint and verify the new thermal receipt print layout) |

---

## 2. What Changed (Summary for Deployment Engineer)

Release `v1.2.5` focuses on **Thermal Printer Receipt Slip Optimization** and **User Registration Deactivation (Login-Only Mode)**.

### Key Changes:
1. **Thermal Printer Slip Redesign (`resources/views/admin/complaints/print-slip.blade.php`)**:
   - Re-engineered the complaint print slip specifically tailored for standard 80mm and 58mm thermal / POS receipt printers.
   - Compact, high-contrast, structured layout displaying Complaint ID, House / Location, Complaint Nature & Title, Resident details, Timestamps, and Complaint Center information.
   - Clean receipt formatting with automatic browser print dialog integration.

2. **Removal of Public / Admin User Registration (`routes/auth.php`, `resources/views/auth/login.blade.php`, `resources/views/layouts/guest.blade.php`)**:
   - Disabled open registration routes (`/register`) to prevent unauthorized self-service account creation.
   - Removed all "Register", "Sign up", and new account navigation buttons/links from login views and guest layout headers.
   - System is now strictly login-only; internal user accounts are created exclusively through `/admin/users/create` by authorized administrators.

---

## 3. Database Change — pick ONE, never both

- [x] **None** — No schema or data changes. Skip database execution steps.

---

## 4. Build & Asset Confirmation

- [x] `public/build/` compiled with `npm run build` (to be committed on tag)
- [x] `vendor/` included with optimized autoloader (to be committed on tag)
- [x] `composer.json` changed? **No**
- [x] `package.json` / CSS / JS changed? **No**
- [x] New `.env` variable introduced? **No**

---

## 5. Deployment Step-by-Step Instructions (VPS `paknavy` — Staging Clone + Atomic Swap)

> **App Path:** `/home/paknavy/public_html`  
> **PHP Binary:** `/opt/cpanel/ea-php82/root/usr/bin/php`  
> **Execution User:** `paknavy` (Never run as root/sudo)

```bash
# Step 1: Navigate to home & clear previous staging/rollback leftovers (if any)
cd /home/paknavy
rm -rf public_html_new
rm -rf public_html_old

# Step 2: Create dated safety backup of current live folder
cp -r public_html public_html_backup_$(date +%F)

# Step 3: Clone the exact tagged version into staging folder
git clone --branch v1.2.5 https://github.com/nexertech/CMS.git public_html_new
cd /home/paknavy/public_html_new

# Step 4: Copy live-only files (.env and storage)
cp /home/paknavy/public_html/.env .env
rm -rf storage
cp -r /home/paknavy/public_html/storage ./

# Step 5: Restore app-root security .htaccess (drops raw IP source listing)
cp /home/paknavy/deploy-assets/app-root-htaccess.txt /home/paknavy/public_html_new/.htaccess

# Step 6: Set correct permissions on writable directories
chmod -R 775 storage bootstrap/cache
chown -R paknavy:paknavy storage bootstrap/cache

# Step 7: Atomic Folder Swap (Instant Safe Transition)
cd /home/paknavy
mv public_html public_html_old
mv public_html_new public_html

# Step 8: Clear & Rebuild Application Caches (Run AFTER swap inside live public_html)
cd /home/paknavy/public_html
/opt/cpanel/ea-php82/root/usr/bin/php artisan config:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan route:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan view:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan cache:clear

/opt/cpanel/ea-php82/root/usr/bin/php artisan config:cache
/opt/cpanel/ea-php82/root/usr/bin/php artisan route:cache
/opt/cpanel/ea-php82/root/usr/bin/php artisan view:cache

# Step 9: Update Live Version Tracker
echo "v1.2.5" > /home/paknavy/current-live-version.txt
```

---

## 6. Post-Deployment Test Checklist

- [ ] Homepage loads; admin login works smoothly (`/login`).
- [ ] Verify that `/register` returns `404 Not Found` and no "Register" link appears on the login screen.
- [ ] Open `/admin/complaints` and click "Print Slip" on any complaint:
  - [ ] Thermal slip formatting renders properly without overlapping text.
  - [ ] Print dialog triggers correctly with appropriate receipt dimensions.
- [ ] Security check: `/.git/config` returns 404 / Forbidden.

---

## 7. Rollback Instructions (Instant Folder Swap)

Since this release contains **no database schema changes**, rollback is instantaneous:

```bash
cd /home/paknavy
mv public_html public_html_failed
mv public_html_old public_html
cd /home/paknavy/public_html
/opt/cpanel/ea-php82/root/usr/bin/php artisan optimize:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan config:cache
/opt/cpanel/ea-php82/root/usr/bin/php artisan route:cache
/opt/cpanel/ea-php82/root/usr/bin/php artisan view:cache
echo "v1.2.4" > /home/paknavy/current-live-version.txt
```
