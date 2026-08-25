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

---

## 3. Database Change — pick ONE, never both

- [x] **None** — No schema or data changes. Skip database execution steps.

---

## 4. Build & Asset Confirmation

- [x] `public/build/` compiled with `npm run build`
- [x] `composer.json` changed? **No**
- [x] `package.json` / CSS / JS changed? **No**
- [x] New `.env` variable introduced? **No**

---

## 5. Deployment Step-by-Step Instructions (VPS `paknavy`)

```bash
# 1. SSH to VPS
ssh paknavy@<server-ip>

# 2. Navigate to CMS project root
cd /home/paknavy/CMS

# 3. Fetch latest tags and checkout v1.2.4
git fetch --tags
git checkout v1.2.4

# 4. Clear and optimize caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Record version
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
