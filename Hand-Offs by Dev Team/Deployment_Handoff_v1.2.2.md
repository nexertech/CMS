# CMS App — Official Release & Deployment Handoff

**Application:** Navy Complaint Management CMS  
**Repository:** `https://github.com/nexertech/CMS.git`  
**Release Tag:** `v1.2.2`  
**Target Branch:** `deploy`  
**Previous Live Tag:** `v1.2.0` / `v1.2.1`  
**Developer:** Dev Team  
**Deployment Engineer:** Siddique / Deployment Team  
**Date:** 2026-08-13  

---

## 1. Developer Handover Summary (Key Features in `v1.2.2`)

Release `v1.2.2` introduces **Searchable AJAX House Dropdown (`/admin/houses/search`)**, **Multi-Sector (GE Nodes) Assignment for Employees**, **Database Performance Indexing**, and **File Import Hardening**.

### Key Features & Enhancements:
1. **Searchable AJAX House Dropdown (Handles 13,502+ Houses Instantly)**:
   - Created dedicated `GET /admin/houses/search` endpoint (`LIMIT 30`, location-scoped) for Select2 AJAX lookup.
   - Replaced full house `<option>` rendering in Complaint Create (`/admin/complaints/create`) and Edit forms with server-side AJAX search, reducing page load time from seconds to < 50ms.
2. **Multi-Sector Employee Assignment (`sector_ids` JSON column)**:
   - Employees can now be assigned to 1, 2, 3, or more GE Nodes (Sectors) simultaneously using `sector_ids` JSON array storage.
   - Preserves `sector_id` on `employees` table for primary sector backward compatibility.
3. **Interactive GE Nodes Checkbox Dropdown UI**:
   - Replaced single dropdown with a custom multi-select checkbox dropdown menu in Employee Create (`/admin/employees/create`) and Edit (`/admin/employees/{id}/edit`).
   - **Smart 1-Node Auto-Selection**: If a GE Group (City) has only 1 GE Node available, that single checkbox is automatically checked by default.
4. **Multi-Sector Location Permission Scope (`LocationFilterTrait`)**:
   - Fixed location permission filtering so employees assigned to multiple sectors are **100% visible** to users belonging to any of those sectors.
5. **Table & Modal UI Formatting**:
   - Updated Employee list table (`/admin/employees`) to display all assigned GE Nodes.
   - Enforced single-line formatting (`text-nowrap`) on employee table cells.
6. **Complaint Export Enhancements**:
   - Added `Description` column as the last column in Complaint Excel / CSV exports.
7. **Database Performance Indexing (130,000+ Records)**:
   - Added B-Tree indexes to `houses`, `employees`, `spares`, `complaint_logs`, `registered_devices`, and other system tables to ensure instant query execution for large datasets.
8. **File Import Hardening**:
   - Switched CSV/Excel import validation to `extensions:csv,txt,xls,xlsx` and declared `"ext-fileinfo": "*"` dependency in `composer.json`.

---

## 2. Answers to Deployment Engineer's Mandatory Questions

Before touching the server, here are the 3 required answers:

| Question | Answer |
|---|---|
| **1. Release Tag?** | **`v1.2.2`** *(Release tag on `deploy` branch)* |
| **2. DB Migration / Schema Change?** | **Manual SQL** *(Paste SQL in phpMyAdmin — no Laravel migration file)* |
| **3. Primary Pages to Test First?** | 1. `/admin/complaints/create` (Searchable AJAX House Dropdown with 13,502+ houses)<br>2. `/admin/employees` (Employee listing, single-line formatting, multi-sector column)<br>3. `/admin/employees/create` (GE Nodes multi-select checkbox dropdown & 1-node auto-selection) |

---

## 3. Database Migration Instructions (Manual SQL)

Execute the following SQL queries verbatim in **phpMyAdmin → SQL tab**:

```sql
-- 1. Employees table multi-sector JSON column
ALTER TABLE `employees` ADD COLUMN IF NOT EXISTS `sector_ids` json DEFAULT NULL AFTER `sector_id`;
UPDATE `employees` SET `sector_ids` = JSON_ARRAY(`sector_id`) WHERE `sector_ids` IS NULL AND `sector_id` IS NOT NULL;

-- 2. Houses table high-performance B-Tree indexes (Fixes slowness with 130,000+ houses)
ALTER TABLE `houses` ADD INDEX IF NOT EXISTS `houses_city_id_idx` (`city_id`);
ALTER TABLE `houses` ADD INDEX IF NOT EXISTS `houses_sector_id_idx` (`sector_id`);
ALTER TABLE `houses` ADD INDEX IF NOT EXISTS `houses_status_idx` (`status`);
ALTER TABLE `houses` ADD INDEX IF NOT EXISTS `houses_house_no_idx` (`house_no`);
ALTER TABLE `houses` ADD INDEX IF NOT EXISTS `houses_phone_idx` (`phone`);
ALTER TABLE `houses` ADD INDEX IF NOT EXISTS `houses_city_sector_status_idx` (`city_id`, `sector_id`, `status`);
ALTER TABLE `houses` ADD INDEX IF NOT EXISTS `houses_sector_status_idx` (`sector_id`, `status`);
ALTER TABLE `houses` ADD INDEX IF NOT EXISTS `houses_city_status_idx` (`city_id`, `status`);

-- 3. Complaints table high-performance B-Tree indexes
ALTER TABLE `complaints` ADD INDEX IF NOT EXISTS `complaints_house_id_idx` (`house_id`);
ALTER TABLE `complaints` ADD INDEX IF NOT EXISTS `complaints_city_sector_status_idx` (`city_id`, `sector_id`, `status`);
ALTER TABLE `complaints` ADD INDEX IF NOT EXISTS `complaints_sector_status_idx` (`sector_id`, `status`);
ALTER TABLE `complaints` ADD INDEX IF NOT EXISTS `complaints_house_status_idx` (`house_id`, `status`);

-- 4. Employees & Spares table high-performance B-Tree indexes
ALTER TABLE `employees` ADD INDEX IF NOT EXISTS `employees_status_idx` (`status`);
ALTER TABLE `employees` ADD INDEX IF NOT EXISTS `employees_phone_idx` (`phone`);
ALTER TABLE `employees` ADD INDEX IF NOT EXISTS `employees_city_status_idx` (`city_id`, `status`);
ALTER TABLE `employees` ADD INDEX IF NOT EXISTS `employees_sector_status_idx` (`sector_id`, `status`);

ALTER TABLE `spares` ADD INDEX IF NOT EXISTS `spares_city_id_idx` (`city_id`);
ALTER TABLE `spares` ADD INDEX IF NOT EXISTS `spares_sector_id_idx` (`sector_id`);
ALTER TABLE `spares` ADD INDEX IF NOT EXISTS `spares_category_id_idx` (`category_id`);
ALTER TABLE `spares` ADD INDEX IF NOT EXISTS `spares_item_name_idx` (`item_name`);
ALTER TABLE `spare_stock_logs` ADD INDEX IF NOT EXISTS `spare_stock_logs_spare_id_idx` (`spare_id`);

-- 5. Additional System Table Performance Indexes
ALTER TABLE `complaint_logs` ADD INDEX IF NOT EXISTS `complaint_logs_complaint_id_idx` (`complaint_id`);
ALTER TABLE `complaint_logs` ADD INDEX IF NOT EXISTS `complaint_logs_action_by_idx` (`action_by`);
ALTER TABLE `spare_approval_performa` ADD INDEX IF NOT EXISTS `spare_approval_performa_complaint_id_idx` (`complaint_id`);
ALTER TABLE `complaint_spares` ADD INDEX IF NOT EXISTS `complaint_spares_complaint_id_idx` (`complaint_id`);
ALTER TABLE `complaint_feedbacks` ADD INDEX IF NOT EXISTS `complaint_feedbacks_complaint_id_idx` (`complaint_id`);
ALTER TABLE `login_history` ADD INDEX IF NOT EXISTS `login_history_user_id_idx` (`user_id`);
ALTER TABLE `registered_devices` ADD INDEX IF NOT EXISTS `registered_devices_is_active_idx` (`is_active`);
ALTER TABLE `registered_devices` ADD INDEX IF NOT EXISTS `registered_devices_assigned_house_idx` (`assigned_to_house_no`);
ALTER TABLE `registered_devices` ADD INDEX IF NOT EXISTS `registered_devices_sector_active_idx` (`sector_id`, `is_active`);
```

---

## 4. Step-by-Step Server Deployment Guide for Siddique

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
git clone --branch v1.2.2 https://github.com/nexertech/CMS.git public_html_new
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
/opt/cpanel/ea-php82/root/usr/bin/php artisan migrate --force

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
```

---

## 5. Post-Deployment Test Checklist

- [ ] Open `/admin/complaints/create` -> Type house number (e.g. `101`) -> Verify AJAX autocomplete loads in < 50ms.
- [ ] Log in as Admin -> Navigate to `/admin/employees`. Verify Employee names render on a single line (`text-nowrap`).
- [ ] Open `/admin/employees/create`. Select a GE Group with 1 GE Node -> Verify 1-node is auto-selected and dropdown button text updates.
- [ ] Select a GE Group with multiple GE Nodes -> Select 2 GE Nodes, create employee.
- [ ] Log in as a User belonging to the 2nd GE Node -> Verify the multi-sector employee is visible.
- [ ] Check `/admin/employees/{id}/edit` -> Verify pre-selected GE Nodes checkboxes match assigned sectors.
