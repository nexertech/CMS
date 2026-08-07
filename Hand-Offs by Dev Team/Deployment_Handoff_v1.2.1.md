# CMS App — Official Release & Deployment Handoff

**Application:** Navy Complaint Management CMS  
**Repository:** `https://github.com/nexertech/CMS.git`  
**Release Tag:** `v1.2.1`  
**Target Branch:** `deploy`  
**Previous Live Tag:** `v1.2.0`  
**Developer:** Amjad  
**Deployment Engineer:** Siddique / Deployment Team  
**Date:** 2026-08-07  

---

## 1. Developer Handover Summary (Key Features & Fixes in Patch Release `v1.2.1`)

This patch release `v1.2.1` fixes the Bulk Import autoloader issue for Employees (`/admin/employees`) and Houses (`/admin/houses`).

### Key Fixes:
1. **Pre-Optimized Vendor Autoloader (`Shuchkin\SimpleXLSX`)**:
   - Compiled vendor dependencies with `composer install --no-dev --optimize-autoloader`.
   - Explicitly mapped `Shuchkin\SimpleXLSX` in `vendor/composer/autoload_classmap.php` & `autoload_static.php`.
   - Resolves HTTP 500 error during Excel (`.xlsx`) bulk imports on production without requiring server-side Composer execution.

---

## 2. Answers to Deployment Engineer's Mandatory Questions

Before touching the server, here are the 3 required answers:

| Question | Answer |
|---|---|
| **1. Release Tag?** | **`v1.2.1`** *(Immutable patch tag pushed to `deploy` branch)* |
| **2. DB Migration / Schema Change?** | **None** *(No DB migrations, no SQL queries required)* |
| **3. Primary Pages to Test First?** | 1. `/admin/employees` (Bulk Import Excel/CSV test)<br>2. `/admin/houses` (Bulk Import Excel/CSV test) |

---

## 3. Step-by-Step Server Deployment Guide for Siddique

Run on JazzCloud cPanel VPS as user `paknavy` (`/opt/cpanel/ea-php82/root/usr/bin/php`).

```bash
cd /home/paknavy

# -------------------------------------------------------------
# STEP 1: Backup current live (Code + Database)
# -------------------------------------------------------------
cp -r public_html public_html_backup_$(date +%F)

# -------------------------------------------------------------
# STEP 2: Clear staging leftovers from previous deploys
# -------------------------------------------------------------
rm -rf public_html_new
rm -rf public_html_old

# -------------------------------------------------------------
# STEP 3: Clone exact tagged release into staging
# -------------------------------------------------------------
git clone --branch v1.2.1 https://github.com/nexertech/CMS.git public_html_new
cd public_html_new

# -------------------------------------------------------------
# STEP 4: Bring over live-only configuration & storage
# -------------------------------------------------------------
cp /home/paknavy/public_html/.env .env
rm -rf storage
cp -r /home/paknavy/public_html/storage ./
cp /home/paknavy/deploy-assets/app-root-htaccess.txt ./.htaccess

# -------------------------------------------------------------
# STEP 5: Set storage permissions
# -------------------------------------------------------------
chmod -R 775 storage bootstrap/cache
chown -R paknavy:paknavy storage bootstrap/cache

# -------------------------------------------------------------
# STEP 6: Execute DB Updates
# -------------------------------------------------------------
# NO DB changes for v1.2.1. Skip this step.

# -------------------------------------------------------------
# STEP 7: Atomic Swap
# -------------------------------------------------------------
cd /home/paknavy
mv public_html public_html_old
mv public_html_new public_html

# -------------------------------------------------------------
# STEP 8: Clear & Rebuild Caches (AFTER THE SWAP in public_html)
# -------------------------------------------------------------
cd /home/paknavy/public_html
/opt/cpanel/ea-php82/root/usr/bin/php artisan config:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan route:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan view:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan cache:clear

/opt/cpanel/ea-php82/root/usr/bin/php artisan config:cache
/opt/cpanel/ea-php82/root/usr/bin/php artisan route:cache
/opt/cpanel/ea-php82/root/usr/bin/php artisan view:cache

# -------------------------------------------------------------
# STEP 9: Record Version & Verification
# -------------------------------------------------------------
echo "v1.2.1" > /home/paknavy/current-live-version.txt
```

---

## 4. Key Post-Deployment Test Checklist

- [ ] **Employee Bulk Import (`/admin/employees`)**:
  - Test uploading sample `.xlsx` or `.csv` file. Verify no 500 error occurs and employees import cleanly.
- [ ] **House Bulk Import (`/admin/houses`)**:
  - Test uploading sample `.xlsx` or `.csv` file. Verify houses import cleanly.
- [ ] **Security**:
  - Raw server IP does **NOT** show directory index, and `/.env` / `/.git/config` return 403/404.
