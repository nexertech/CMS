# CMS App — Official Release & Deployment Handoff

**Application:** Navy Complaint Management CMS  
**Repository:** `https://github.com/nexertech/CMS.git`  
**Release Tag:** `v1.2.5`  
**Target Branch:** `deploy`  
**Previous Live Tag:** `v1.2.4`  
**Developer:** Amjad Subhani  
**Deployment Engineer:** Siddique / Deployment Team  
**Date:** 2026-10-12  

---

## 1. The 3 Mandatory Answers (fill these first)

| Question | Answer |
|---|---|
| **1. Release Tag?** | **`v1.2.5`** *(Immutable tag to be pushed to the `deploy` branch)* |
| **2. Database Change Classification?** | **None** *(No schema or table migrations required for this release)* |
| **3. Primary Page(s) to Test First?** | 1. `/login` & `/register` (Verify register link is removed and direct `/register` returns 404)<br>2. `/admin/complaints` (Click "Print Slip" on any complaint for thermal receipt; check complaint show page to ensure issued spares remain visible)<br>3. `/admin/complaints/create` (Verify only active, non-soft-deleted Complaint Types populate in dropdown)<br>4. Topbar Navbar (Click the new Emerald Green **DB Backup** button as Admin to verify instant `.sql` download with PKT timestamp)<br>5. `/admin/dashboard` (Hover on filter **Reset** button to verify the refresh icon stays visible)<br>6. `/dashboard` (Verify CMES chart displays resolution percentages `(X%)` on green bars and filter dropdown options work seamlessly) |

---

## 2. What Changed (Summary for Deployment Engineer)

Release `v1.2.5` incorporates **Database One-Click Backup Tool**, **Thermal Printer Slip Optimization**, **User Registration Deactivation (Login-Only Mode)**, **Topbar Profile & Dropdown Redesign**, **Dashboard Filter Reset Icon Fix**, and **Data Retention / Soft-Delete Hardening**.

### Key Changes:

1. **One-Click Database Backup for Admin (`app/Http/Controllers/Admin/BackupController.php`, `routes/web.php`, `resources/views/layouts/navigation.blade.php`)**:
   - Added instant streaming database dump feature accessible strictly to Admin (`role_id === 1`).
   - Generates and downloads standard MySQL SQL backup file with PKT timestamp: `cms_db_backup_YYYY-MM-DD_hh-mm-AM/PM.sql`.
   - Sleek sky blue pill button placed in topbar for instant manual backups before system actions.

2. **Topbar User Profile Pill & Executive Dropdown Redesign (`resources/views/layouts/navigation.blade.php`)**:
   - Modernized user dropdown trigger into a sleek capsule pill button with user initial avatar, online dot indicator, and role badge.
   - Redesigned dropdown menu into an executive card layout featuring user hero card, dual-line navigation options with icons, smooth micro-interactions, and red sign-out button across Light, Dark, and Night themes.

3. **Soft-Deleted Spares & Complaint Types Data Retention**:
   - Added `->withTrashed()` relationship bindings on `Spare` in `SpareStockLog`, `ComplaintSpare`, `SpareApprovalItem`, and `StockAddData`. Ensures historical complaint spare issuances and approvals never disappear from past logs if an inventory item is archived.
   - Hardened `ComplaintTitleController@getTitlesByCategory` to only return active, non-deleted complaint types (`whereNull('deleted_at')` and `status = 1`).

4. **Thermal Printer Slip Redesign (`resources/views/admin/complaints/print-slip.blade.php`)**:
   - Re-engineered the complaint print slip specifically tailored for standard 80mm and 58mm thermal / POS receipt printers.
   - Compact, high-contrast, structured layout displaying Complaint ID, House / Location, Complaint Nature & Title, Resident details, Timestamps, and Complaint Center information.

5. **Removal of Public / Admin User Registration (`routes/auth.php`, `resources/views/auth/login.blade.php`, `resources/views/layouts/guest.blade.php`)**:
   - Disabled open registration routes (`/register`) to prevent unauthorized self-service account creation.
   - System is strictly login-only; internal user accounts are created exclusively through `/admin/users/create` by authorized administrators.

6. **Complaint Types AJAX Dropdown Decoupling (`routes/web.php`)**:
   - Fixed permission requirement so operators can select complaint types during complaint registration without needing full CRUD access to the Complaint Types management screen.

7. **Frontend Dashboard CMES Graph & Table Resolution Tracking (`app/Http/Controllers/Frontend/HomeController.php`, `resources/views/frontend/dashboard.blade.php`, `resources/views/frontend/dashboard/partials/cme_table.blade.php`)**:
   - Added percentage resolution labels directly on the green bar above the count `(X%)`.
   - Updated CMES graph filter dropdown with quick period filters: `All Time` (default), `Last 15 Days`, `This Month`, `Last 3 Months`, `Last 6 Months`, `This Year`.
   - Synchronized CMES table with percentage resolution indicators.

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
- [ ] Open `/admin/complaints/create`:
  - [ ] Select a Category; verify the Complaint Types / Titles dropdown populates properly (even when tested with a role that has "Complaint Types" permission turned off in the sidebar).
  - [ ] Ensure soft-deleted complaint types do not appear in the dropdown.
- [ ] Database Backup & Topbar:
  - [ ] Log in as Admin (`role_id === 1`); verify the Emerald Green **DB Backup** pill appears in the topbar.
  - [ ] Click **DB Backup**; verify a `.sql` dump file downloads immediately with the format `cms_db_backup_YYYY-MM-DD_hh-mm-AM/PM.sql`.
  - [ ] Verify non-admin roles do not see or have access to the backup route.
- [ ] Topbar Profile & Dropdown:
  - [ ] Click the user profile pill in the topbar; verify the executive card dropdown renders properly with avatar, user details, role badge, and navigation options.
- [ ] Dashboard Filters:
  - [ ] Go to `/admin/dashboard`; hover on the filter **Reset** button and verify the refresh icon (`refresh-cw`) stays clearly visible and rotates smoothly.
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
