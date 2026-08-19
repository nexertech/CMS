# CMS App — Official Release & Deployment Handoff

**Application:** Navy Complaint Management CMS  
**Repository:** `https://github.com/nexertech/CMS.git`  
**Release Tag:** `v1.2.3`  
**Target Branch:** `deploy`  
**Previous Live Tag:** `v1.2.2`  
**Developer:** Dev Team  
**Deployment Engineer:** Siddique / Deployment Team  
**Date:** 2026-08-17  

---

## 1. Developer Handover Summary (Key Features in `v1.2.3`)

Release `v1.2.3` delivers the **Complete Sub-Category System**, **Dashboard & Complaint List Sub-Category Filters**, **Popup Modal Filter Scoping Fix**, **Complaint Slip Priority & Sub-Category Integration**, **Database Priority ENUM Schema Fix**, and **UI/Modal Visual Consistency**.

### Detailed Features & Architectural Changes:
1. **Sub-Category Management System (`/admin/sub-category`)**:
   - **Database**: Created `sub_categories` table linked via foreign key to `complaint_categories` (`category_id`).
   - **Model & Relationships**: Added `App\Models\SubCategory` model; added `subCategories()` relation on `ComplaintCategory` and `subCategory()` relation on `Complaint`.
   - **Admin Management View**: Full CRUD UI with add form, single-line data table, AJAX deletion, and edit modal with background blur effect.
   - **Dynamic AJAX Dropdown**: Added `GET /admin/sub-categories/by-category?category={id}` endpoint to dynamically load sub-categories upon category selection in complaint forms.

2. **3x3 Complaint Form Grid (Create & Edit)**:
   - Redesigned `/admin/complaints/create` and `/admin/complaints/{id}/edit` into a clean **3x3 grid layout** (`col-md-4`):
     - **Row 1**: Category, Sub Category, Complaint Type
     - **Row 2**: Priority, Availability Time, Assign Employee
     - **Row 3**: Description (full width)

3. **Category-Dependent Employee Filtering in Complaint Forms**:
   - In complaint registration (`/admin/complaints/create`) and edit (`/admin/complaints/{id}/edit`), the **Assign Employee** dropdown is dynamically filtered based on the selected **Category** (and location scope).
   - Before a Category is selected, the Employee dropdown remains locked showing `"Select Category First"`, preventing cross-category technician misassignment. Previously, all employees were listed immediately.

4. **Dashboard Sub-Category Filter & Stat Modal Integration (`/admin/dashboard`)**:
   - Added interactive multi-select Sub Category checkbox dropdown to the Dashboard filter toolbar.
   - Updated `DashboardController` to apply `sub_category_id` filtering to all KPI cards, breakdown metrics, trend lines, and recent complaints.
   - **Fixed Stat Card Click Modal (`showComplaintsModal`)**: Updated `ComplaintController@index` to process `sub_category_id` array parameter so clicking any stat card (Total, In Progress, Addressed, etc.) displays strictly the complaints matching active sub-category filters.

5. **Complaints Management Sub-Category Filter (`/admin/complaints`)**:
   - Added `Sub Category` filter dropdown to Complaints Management page with instantaneous AJAX reload and filter reset support.

6. **Complaint Slip & Details Views (Priority & Sub-Category)**:
   - **Print Slip (`/admin/complaints/{id}/print-slip`)**:
     - Added `Sub Category` row under Client Information table.
     - Added `Priority` badge (`Emergency` / `Normal`) under Request Details table.
   - **Show View (`/admin/complaints/{id}`) & Modal Popups**:
     - Sub-category and formatted Priority badges rendered consistently across Admin details view, index popup modals, and Frontend user portal.

7. **Priority Column Database Schema Fix**:
   - Resolved an issue where selecting `Emergency` during complaint registration resulted in `Normal` due to legacy MySQL enum `('low','medium','high','urgent')`.
   - Updated schema to `ENUM('normal', 'emergency') NOT NULL DEFAULT 'normal'`.

8. **Modal UI & Theme Enhancements**:
   - Added background blur (`modal-open-blur`) for Category and Sub Category modals.
   - Fixed close button visibility on dark theme modal headers using `btn-close-white`.

9. **Excel/CSV Export Sub-Category Column**:
   - In Dashboard complaints export (`exportModalToExcel`), added the **Sub Category** column immediately after **Category** with full UTF-8 formatting.

10. **Employees & Houses Excel Export**:
    - **Employees Management (`/admin/employees`)**: Added direct one-click Excel/CSV export button to download complete employee roster records.
    - **Houses Management (`/admin/houses`)**: Added direct one-click Excel/CSV export button to download complete houses registry records.

---

## 2. Answers to Deployment Engineer's Mandatory Questions

Before deploying to the server, verify the 3 mandatory answers:

| Question | Answer |
|---|---|
| **1. Release Tag?** | **`v1.2.3`** *(Immutable tag on `deploy` branch)* |
| **2. Database Change Classification?** | **Manual SQL** *(Paste verbatim SQL script below)* + **Laravel Migration** *(Optional `php artisan migrate --force`)* |
| **3. Primary Pages to Test First?** | 1. `/admin/sub-category` (Sub Category CRUD & modal)<br>2. `/admin/complaints/create` (3x3 grid, dynamic Sub Category, Emergency priority)<br>3. `/admin/dashboard` (Sub Category filter & stat card click popup)<br>4. `/admin/complaints` (Sub Category filter dropdown)<br>5. `/admin/complaints/{id}/print-slip` (Sub Category and Priority on print slip) |

---

## 3. Database Changes

### Complete SQL Script for phpMyAdmin (Recommended — Run in phpMyAdmin → SQL tab)

Paste and execute the following queries verbatim:

```sql
-- ==============================================================================
-- CMS Release v1.2.3 — Complete Database Script
-- ==============================================================================

-- 1. Create sub_categories table
CREATE TABLE IF NOT EXISTS `sub_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `app_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sub_categories_category_id_foreign` (`category_id`),
  KEY `sub_categories_status_index` (`status`),
  CONSTRAINT `sub_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `complaint_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Add sub_category_id column to complaints table
ALTER TABLE `complaints` ADD COLUMN IF NOT EXISTS `sub_category_id` bigint(20) unsigned DEFAULT NULL AFTER `category_id`;
ALTER TABLE `complaints` ADD INDEX IF NOT EXISTS `complaints_sub_category_id_index` (`sub_category_id`);

-- 3. Update complaints priority enum to ('normal', 'emergency')
ALTER TABLE `complaints` MODIFY `priority` ENUM('normal', 'emergency') NOT NULL DEFAULT 'normal';
UPDATE `complaints` SET `priority` = 'normal' WHERE `priority` = '' OR `priority` IS NULL;
```

---

### Laravel Migration Alternative
If running migrations from terminal:
```bash
php artisan migrate --force
```
*(Note: `php artisan migrate --force` will execute migration `2026_08_17_120000_create_sub_categories_table.php` to create the `sub_categories` table).*

---

## 4. Complete List of Files Changed & Created

| File Path | Type | Description |
|---|---|---|
| `app/Http/Controllers/Admin/SubCategoryController.php` | **[NEW]** | Sub Category CRUD & dynamic AJAX `byCategory` endpoint |
| `app/Models/SubCategory.php` | **[NEW]** | Eloquent model for sub categories |
| `database/migrations/2026_08_17_120000_create_sub_categories_table.php` | **[NEW]** | Migration creating `sub_categories` table |
| `resources/views/admin/sub_category/index.blade.php` | **[NEW]** | Sub category management index & edit modal view |
| `app/Models/ComplaintCategory.php` | **[MODIFY]** | Added `subCategories()` relationship |
| `app/Models/Complaint.php` | **[MODIFY]** | Added `sub_category_id` to `$fillable` & `subCategory()` relation |
| `app/Http/Controllers/Admin/ComplaintController.php` | **[MODIFY]** | Added `sub_category_id` support in `store`, `storeMultiple`, `index`, `show`, `printSlip`, and Excel export |
| `app/Http/Controllers/Admin/DashboardController.php` | **[MODIFY]** | Added `sub_category_id` filtering to all dashboard metrics, charts, and queries |
| `app/Http/Controllers/Admin/EmployeeController.php` | **[MODIFY]** | Added Excel/CSV export and import handling for employees |
| `app/Http/Controllers/Admin/HouseController.php` | **[MODIFY]** | Added Excel/CSV export and import handling for houses |
| `app/Http/Controllers/Frontend/HomeController.php` | **[MODIFY]** | Eager-loaded `subCategory` in frontend complaint show method |
| `resources/views/admin/complaints/create.blade.php` | **[MODIFY]** | 3x3 layout, dynamic Sub Category dropdown, priority options, category-filtered employees |
| `resources/views/admin/complaints/edit.blade.php` | **[MODIFY]** | 3x3 layout, Sub Category dropdown, category-filtered employees |
| `resources/views/admin/complaints/partials/form_scripts.blade.php` | **[MODIFY]** | AJAX sub-category loader on Category change |
| `resources/views/admin/complaints/index.blade.php` | **[MODIFY]** | Sub Category filter dropdown |
| `resources/views/admin/employees/index.blade.php` | **[MODIFY]** | Export Excel and Import Excel modal UI |
| `resources/views/admin/houses/index.blade.php` | **[MODIFY]** | Export Excel and Import Excel modal UI |
| `resources/views/admin/dashboard.blade.php` | **[MODIFY]** | Sub Category filter dropdown, stat box click modal filter fix, and Excel export Sub Category column |
| `resources/views/admin/complaints/print-slip.blade.php` | **[MODIFY]** | Added Sub Category & Priority badge to printable slip |
| `resources/views/admin/complaints/show.blade.php` | **[MODIFY]** | Added Sub Category & Priority badge in complaint details |
| `resources/views/frontend/complaints/partials/detail_card.blade.php` | **[MODIFY]** | Added Sub Category & Priority badge in frontend details |
| `resources/views/admin/category/index.blade.php` | **[MODIFY]** | Added modal blur and white close button |
| `resources/views/layouts/sidebar.blade.php` | **[MODIFY]** | Added Sub Categories link under navigation |
| `routes/web.php` | **[MODIFY]** | Added Sub Category routes and AJAX endpoint |
| `database/migrations/2025_10_21_050121_create_complaints_table.php` | **[MODIFY]** | Updated base schema definition with `sub_category_id` & `priority` enum |

---

## 5. Build & Asset Confirmation

- [x] Production assets compiled locally (`public/build/` committed on tag)
- [x] Vendor dependencies included (`vendor/` committed on tag)
- [x] `composer.json` changed: **No**
- [x] `package.json` changed: **No**
- [x] New `.env` variables introduced: **No**

---

## 6. Step-by-Step Server Deployment Instructions (`paknavy` VPS)

Execute the following commands sequentially on the VPS:

```bash
# Step 1: Enable Maintenance Mode
php artisan down

# Step 2: Fetch Latest Release Tag
git fetch origin --tags
git checkout v1.2.3

# Step 3: Run Database Migration & SQL
php artisan migrate --force

# Step 4: Clear & Rebuild Application Caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 5: Disable Maintenance Mode
php artisan up

# Step 6: Update Live Version Tracking File
echo "v1.2.3" > /home/paknavy/current-live-version.txt
```

---

## 7. Post-Deployment Verification Checklist

Verify the following after deploying to production:

- [ ] **1. Sub-Category Management (`/admin/sub-category`)**:
  - [ ] Page loads; existing sub-categories table displays cleanly.
  - [ ] Add a new test Sub-Category; confirm it saves successfully.
  - [ ] Click "Edit"; confirm edit modal opens with background blur and white close button.
- [ ] **2. Complaint Creation (`/admin/complaints/create`)**:
  - [ ] Page loads with clean 3x3 layout.
  - [ ] Verify **Assign Employee** dropdown is disabled/locked with `"Select Category First"` until a category is chosen.
  - [ ] Select a Category; verify Sub-Category dropdown populates with relevant sub-categories and Employee dropdown enables with only technicians for that category.
  - [ ] Select **Emergency** Priority; submit complaint.
  - [ ] Verify complaint is saved with Emergency status (Red badge on index table).
- [ ] **3. Dashboard Filters & Popup (`/admin/dashboard`)**:
  - [ ] "Sub Category" multi-select checkbox dropdown appears in filter bar.
  - [ ] Check a Sub-Category and click "Apply"; total count updates to match filtered count.
  - [ ] Click on "Total Complaints" stat box; confirm popup modal displays **only** the complaints belonging to the selected sub-category.
- [ ] **4. Complaints Management (`/admin/complaints`)**:
  - [ ] "Sub Category" dropdown filter works with AJAX instant update.
  - [ ] "Reset" button clears the filter.
- [ ] **5. Complaint Print Slip (`/admin/complaints/{id}/print-slip`)**:
  - [ ] `Sub Category` displays under Client Information.
  - [ ] `Priority` badge (`Emergency` / `Normal`) displays under Request Details.
- [ ] **6. Security Check**:
  - [ ] `/.env`, `/.git`, and `/composer.json` return 403/404.

---

## 8. Rollback Plan

If an unexpected critical issue occurs:

```bash
# 1. Enable Maintenance Mode
php artisan down

# 2. Checkout Previous Stable Release Tag
git checkout v1.2.2

# 3. Clear & Rebuild Caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Disable Maintenance Mode
php artisan up

# 5. Update Live Version Tracking File
echo "v1.2.2" > /home/paknavy/current-live-version.txt
```
