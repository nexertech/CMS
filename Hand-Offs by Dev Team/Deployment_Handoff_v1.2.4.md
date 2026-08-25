# CMS App — Official Release & Deployment Handoff

**Application:** Navy Complaint Management CMS  
**Repository:** `https://github.com/nexertech/CMS.git`  
**Release Tag:** `v1.2.4`  
**Target Branch:** `deploy`  
**Previous Live Tag:** `v1.2.3`  
**Developer:** Amjid  
**Deployment Engineer:** Siddique / Deployment Team  
**Date:** 2026-08-25  

---

## 1. The 3 Mandatory Answers (fill these first)

| Question | Answer |
|---|---|
| **1. Release Tag?** | **`v1.2.4`** *(Immutable tag pushed to the `deploy` branch)* |
| **2. Database Change Classification?** | **None** *(No schema or table changes required for this release)* |
| **3. Primary Page(s) to Test First?** | 1. `/admin/roles` (Edit permissions for non-admin roles)<br>2. `/admin/employees` & `/admin/designation` (Verify independent permission toggling)<br>3. `/admin/houses` (Verify Houses does not show unless `houses` permission is granted)<br>4. `/admin/cmes` (Verify CMES permission isolation) |

---

## 2. What Changed (Summary for Deployment Engineer)

Release `v1.2.4` delivers **Granular Role Permission Isolation and Route Protection Fixes**, resolving permission bleeding between parent and sub-modules.

### Key Fixes & Architecture Updates:
1. **Designation & Sub-Module Permission Isolation (`app/Models/Role.php`)**:
   - Removed legacy fallback logic from `Role::hasPermission()` that was auto-granting sublink permissions (`designation`, `category`, `sub-category`, etc.) whenever their parent module (`employees`, `complaints`) was granted.
   - Simplified `hasPermission()` to evaluate exact module names. Turning OFF `Designations` in a role now strictly revokes access even if `Employees` is enabled.

2. **Houses Sidebar & Route Permission Isolation**:
   - Fixed `resources/views/layouts/sidebar.blade.php` where the **Houses** menu item was using `@if($user && ($user->hasPermission('employees')))` instead of `hasPermission('houses')`.
   - Fixed `routes/web.php` where House Management routes were guarded with `permission:employees.view` instead of `permission:houses.view`.

3. **CMES Route Permission Guard Fix**:
   - Fixed `routes/web.php` where CMES routes were guarded with `permission:city.view` instead of `permission:cmes.view`.

4. **Role Display & Auto-Assignment Completeness**:
   - Updated `Role::boot()` auto-assignment list for superadmin (`role_id = 1`) to include all granular sub-modules (`designation`, `category`, `sub-category`, `complaint-titles`).
   - Updated `resources/views/admin/roles/show.blade.php` `$moduleLabels` to explicitly display labels for `frontend-users`, `cmes`, and `registered-devices`.

### ⚠️ Important Post-Deploy Behavior Note:
> **Sub-Permissions Re-Ticking for Existing Custom Roles:**  
> Since parent modules (e.g. `Employees`, `Complaints Mgmt`) no longer automatically auto-grant their sub-items (`Designations`, `Complaint Cat`, `Sub Categories`, `Complaint Types`, `Total Complaints`), any existing non-admin custom roles that require access to specific sub-modules should have those individual checkboxes re-ticked and saved in `/admin/roles/{id}/edit` post-deployment. (Superadmin `role_id = 1` remains unaffected and has access to everything).

---

## 3. Database Change — pick ONE, never both

- [x] **None** — No schema or data changes. Skip database execution steps.

---

## 4. Build & Asset Confirmation

- [x] `public/build/` compiled with `npm run build` (committed on tag)
- [x] `vendor/` included with optimized autoloader (committed on tag)
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
git clone --branch v1.2.4 https://github.com/nexertech/CMS.git public_html_new
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
echo "v1.2.4" > /home/paknavy/current-live-version.txt
```

---

## 6. Post-Deployment Test Checklist

- [ ] Homepage loads; admin login works.
- [ ] Log in as a role where **Employees** is enabled but **Designations** is disabled:
  - [ ] Only "Employees" link is visible in the sidebar.
  - [ ] The dropdown arrow for Designations is NOT rendered.
  - [ ] Navigating to `/admin/designation` returns `403 Forbidden`.
- [ ] Log in as a role where **Employees** is enabled but **Houses** is disabled:
  - [ ] "Houses" does NOT appear in the sidebar.
  - [ ] Navigating to `/admin/houses` returns `403 Forbidden`.
- [ ] Log in as a role with **CMES** enabled and **GE Groups (City)** disabled:
  - [ ] Navigating to `/admin/cmes` works properly without 403 error.
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
echo "v1.2.3" > /home/paknavy/current-live-version.txt
```
