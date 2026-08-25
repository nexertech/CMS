# CMS App — Official Release & Deployment Handoff

**Application:** Navy Complaint Management CMS  
**Repository:** `https://github.com/nexertech/CMS.git`  
**Release Tag:** `v1.2.3`  
**Target Branch:** `deploy`  
**Previous Live Tag:** `v1.2.2`  
**Developer:** Dev Team  
**Deployment Engineer:** Siddique / Deployment Team  
**Date:** 2026-08-24  

---

## 1. Developer Handover Summary (Key Features in `v1.2.3`)

Release `v1.2.3` delivers the **Complete Sub-Category System & Role Permissions**, **Universal Pagination Filter Preservation**, **Automatic In-Progress Assignment Workflow**, **Universal QR Feedback Submission**, **Dashboard Stat Modal PDF Export**, **Total Complaints Action Button Refinements**, and **Print Slip Dimensions Reversion**.

### Detailed Features & Architectural Changes:
1. **Sub-Category Management System (`/admin/sub-category`)**:
   - **Database**: Created `sub_categories` table linked via foreign key to `complaint_categories` (`category_id`).
   - **Model & Relationships**: Added `App\Models\SubCategory` model; added `subCategories()` relation on `ComplaintCategory` and `subCategory()` relation on `Complaint`.
   - **Admin Management View**: Full CRUD UI with add form, single-line data table, AJAX deletion, and edit modal with background blur effect.
   - **Dynamic AJAX Dropdown**: Added `GET /admin/sub-categories/by-category?category={id}` endpoint to dynamically load sub-categories upon category selection in complaint forms.

2. **Sub-Category Role Permissions Integration (`/admin/roles`)**:
   - Added `'sub-category' => 'Sub Categories'` to `Complaints Mgmt` sublinks inside Role create (`create.blade.php`), edit (`edit.blade.php`), and show (`show.blade.php`).
   - Updated `app/Models/Role.php` `$sublinkToParent` map to include `'sub-category' => 'complaints'`.
   - Updated `resources/views/layouts/sidebar.blade.php` to independently protect Sub Categories with `@if($user && ($user->hasPermission('sub-category')))`.
   - Protected routes in `routes/web.php` with `permission:sub-category.view`.

3. **3x3 Complaint Form Grid (Create & Edit)**:
   - Redesigned `/admin/complaints/create` and `/admin/complaints/{id}/edit` into a clean **3x3 grid layout** (`col-md-4`):
     - **Row 1**: Category, Sub Category, Complaint Type
     - **Row 2**: Priority, Availability Time, Assign Employee
     - **Row 3**: Description (full width)

4. **Category-Dependent Employee Filtering in Complaint Forms**:
   - In complaint registration (`/admin/complaints/create`) and edit (`/admin/complaints/{id}/edit`), the **Assign Employee** dropdown is dynamically filtered based on the selected **Category** (and location scope).
   - Before a Category is selected, the Employee dropdown remains locked showing `"Select Category First"`, preventing cross-category technician misassignment. Previously, all employees were listed immediately.

5. **Automatic Status Transition on Assignment to In Progress (`0`)**:
   - In `ComplaintController.php` (single create `store()`, batch create `storeMultiple()`, single assignment `assign()`, update `update()`, and bulk actions `bulkAction()`), assigning an unassigned complaint to a technician transitions status directly to **In Progress** (`Complaint::STATUS_IN_PROGRESS` = 0) instead of `Assigned` (3).
   - Client notifications also reflect status `'in_progress'`.

6. **Universal Pagination Filter Preservation (`->withQueryString()`)**:
   - Resolved an issue across all system pages (Houses, Employees, Total Complaints / Approvals, Spares, Users, Sub Categories, Roles, SLAs, Titles, Devices, Feedbacks, Sectors, Cities, CMEs, Categories, Brands, Designations) where navigating pagination (page 2, 3, Next) lost applied filters (e.g. GE Group, Sector, Search, Status, Category, Priority, Date).
   - Added `->withQueryString()` to pagination calls in all admin controllers.

7. **Universal QR Code Public Feedback Workflow**:
   - Allowed clients to submit feedback via QR code URL (`/complaint/feedback/{id}`) on **any complaint status** (previously restricted to only `resolved`/`closed`).
   - Removed the automatic forced status transition upon feedback submission (`complaint status remains unchanged and is managed manually by the complaint office`).
   - In `resources/views/frontend/feedback.blade.php`, removed locked feedback card so the rating form is always open and interactive.

8. **Dashboard Sub-Category Filter & Stat Modal Integration (`/admin/dashboard`)**:
   - Added interactive multi-select Sub Category checkbox dropdown to the Dashboard filter toolbar.
   - Updated `DashboardController` to apply `sub_category_id` filtering to all KPI cards, breakdown metrics, trend lines, and recent complaints.
   - **Fixed Stat Card Click Modal (`showComplaintsModal`)**: Updated `ComplaintController@index` to process `sub_category_id` array parameter so clicking any stat card (Total, In Progress, Addressed, etc.) displays strictly the complaints matching active sub-category filters.

9. **Dashboard Complaints Modal: "Export to PDF" with Custom 8-Column Layout**:
   - Added **Export to PDF** button in Dashboard complaints popup modal (`complaintsListModal`).
   - Implemented `exportModalToPdf()` generating a clean, professional landscape A4 document featuring exactly 8 fields: `CMP-ID`, `Register Date`, `House No`, `Category`, `Sub Category`, `Type`, `Priority`, `Description`.

10. **Total Complaints (`/admin/approvals`) Action Button Updates**:
    - **Feedback Check-Circle Button**: Displays on the Total Complaints table whenever feedback is submitted for a complaint regardless of its status.
    - **Addressed Complaint Edit Button Disable**: When a complaint status is `Addressed` (`resolved`/`closed`), its Edit button in the table is disabled (`cursor: not-allowed`).

11. **Complaints Management Sub-Category Filter (`/admin/complaints`)**:
    - Added `Sub Category` filter dropdown to Complaints Management page with instantaneous AJAX reload and filter reset support.

12. **Complaint Slip & Details Views (Priority & Sub-Category)**:
    - **Print Slip (`/admin/complaints/{id}/print-slip`)**:
      - Added `Sub Category` row under Client Information table.
      - Added `Priority` badge (`Emergency` / `Normal`) under Request Details table.
      - Reverted layout to the compact 580px width requested by the client.
    - **Show View (`/admin/complaints/{id}`) & Modal Popups**:
      - Sub-category and formatted Priority badges rendered consistently across Admin details view, index popup modals, and Frontend user portal.

13. **Priority Column Database Schema Fix**:
    - Resolved an issue where selecting `Emergency` during complaint registration resulted in `Normal` due to legacy MySQL enum `('low','medium','high','urgent')`.
    - Updated schema to `ENUM('normal', 'emergency') NOT NULL DEFAULT 'normal'`.

14. **Modal UI & Theme Enhancements**:
    - Added background blur (`modal-open-blur`) for Category and Sub Category modals.
    - Fixed close button visibility on dark theme modal headers using `btn-close-white`.

15. **Employees & Houses Excel Export**:
    - **Employees Management (`/admin/employees`)**: Added direct one-click Excel/CSV export button to download complete employee roster records.
    - **Houses Management (`/admin/houses`)**: Added direct one-click Excel/CSV export button to download complete houses registry records.

---

## 2. Answers to Deployment Engineer's Mandatory Questions

Before deploying to the server, verify the 3 mandatory answers:

| Question | Answer |
|---|---|
| **1. Release Tag?** | **`v1.2.3`** *(Immutable tag on `deploy` branch)* |
| **2. Database Change Classification?** | **Manual SQL** *(Paste verbatim SQL script below)* + **Laravel Migration** *(Optional `php artisan migrate --force`)* |
| **3. Primary Pages to Test First?** | 1. `/admin/sub-category` (Sub Category CRUD & modal)<br>2. `/admin/roles` (Sub Categories permission checkbox)<br>3. `/admin/houses` & `/admin/employees` (Pagination filter retention)<br>4. `/admin/complaints/create` (3x3 grid, dynamic Sub Category, In Progress assignment)<br>5. `/admin/dashboard` (Sub Category filter & Export to PDF in stat modal)<br>6. `/admin/approvals` (Feedback checkmarks & disabled edit on addressed) |

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

## 4. Complete List of Files Changed & Created

| File Path | Type | Description |
|---|---|---|
| `app/Http/Controllers/Admin/SubCategoryController.php` | **[NEW]** | Sub Category CRUD, pagination with query string, dynamic AJAX `byCategory` |
| `app/Models/SubCategory.php` | **[NEW]** | Eloquent model for sub categories |
| `database/migrations/2026_08_17_120000_create_sub_categories_table.php` | **[NEW]** | Migration creating `sub_categories` table |
| `resources/views/admin/sub_category/index.blade.php` | **[NEW]** | Sub category management index & edit modal view |
| `app/Models/ComplaintCategory.php` | **[MODIFY]** | Added `subCategories()` relationship |
| `app/Models/Complaint.php` | **[MODIFY]** | Added `sub_category_id` to `$fillable` & `subCategory()` relation |
| `app/Models/Role.php` | **[MODIFY]** | Added `'sub-category' => 'complaints'` mapping |
| `app/Http/Controllers/Admin/ComplaintController.php` | **[MODIFY]** | Added `sub_category_id` support, assignment to `STATUS_IN_PROGRESS` (0), and modal data scoping |
| `app/Http/Controllers/Admin/DashboardController.php` | **[MODIFY]** | Added `sub_category_id` filtering to all dashboard metrics, charts, and queries |
| `app/Http/Controllers/Admin/ApprovalController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Admin/EmployeeController.php` | **[MODIFY]** | Added Excel export and `->withQueryString()` pagination |
| `app/Http/Controllers/Admin/HouseController.php` | **[MODIFY]** | Added Excel export and `->withQueryString()` pagination |
| `app/Http/Controllers/Admin/SpareController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Admin/UserController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Admin/FrontendUserController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Admin/ComplaintTitleController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Admin/FeedbackController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Admin/RegisteredDeviceController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Admin/RoleController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Admin/SlaController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Admin/SectorController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Admin/CityController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Admin/CmeController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Admin/CategoryController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Admin/BrandController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Admin/DesignationController.php` | **[MODIFY]** | Added `->withQueryString()` to pagination |
| `app/Http/Controllers/Frontend/HomeController.php` | **[MODIFY]** | Allowed QR feedback on any status, removed forced auto-resolve |
| `resources/views/frontend/feedback.blade.php` | **[MODIFY]** | Removed locked card, enabled feedback form for all statuses |
| `resources/views/admin/approvals/index.blade.php` | **[MODIFY]** | Feedback button on all statuses, disabled edit button for Addressed complaints |
| `resources/views/admin/dashboard.blade.php` | **[MODIFY]** | Added "Export to PDF" button with custom 8-column layout |
| `resources/views/admin/roles/create.blade.php` | **[MODIFY]** | Added Sub Categories permission checkbox |
| `resources/views/admin/roles/edit.blade.php` | **[MODIFY]** | Added Sub Categories permission checkbox |
| `resources/views/admin/roles/show.blade.php` | **[MODIFY]** | Added Sub Categories in permissions view |
| `resources/views/admin/complaints/create.blade.php` | **[MODIFY]** | 3x3 layout, dynamic Sub Category dropdown, priority options, category-filtered employees |
| `resources/views/admin/complaints/edit.blade.php` | **[MODIFY]** | 3x3 layout, Sub Category dropdown, category-filtered employees |
| `resources/views/admin/complaints/partials/form_scripts.blade.php` | **[MODIFY]** | AJAX sub-category loader on Category change |
| `resources/views/admin/complaints/index.blade.php` | **[MODIFY]** | Sub Category filter dropdown |
| `resources/views/admin/complaints/print-slip.blade.php` | **[MODIFY]** | Added Sub Category & Priority badge, reverted to 580px compact width |
| `resources/views/admin/complaints/show.blade.php` | **[MODIFY]** | Added Sub Category & Priority badge in complaint details |
| `resources/views/layouts/sidebar.blade.php` | **[MODIFY]** | Added Sub Categories navigation link protected by permission |
| `routes/web.php` | **[MODIFY]** | Added Sub Category routes and permission middleware |

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

- [ ] **1. Sub-Category Management & Roles (`/admin/sub-category` & `/admin/roles`)**:
  - [ ] Add Sub Category; edit modal opens with background blur.
  - [ ] Check Role permissions; confirm Sub Category permission checkbox is present and functional.
- [ ] **2. Pagination Filter Retention**:
  - [ ] On `/admin/houses` or `/admin/employees`, apply a filter (e.g. GE/Sector/Category) and click Page 2 / Next.
  - [ ] Confirm filters remain active and applied.
- [ ] **3. Complaint Assignment to In Progress**:
  - [ ] Assign an unassigned complaint to a technician; confirm status moves to **In Progress**.
- [ ] **4. QR Code Feedback**:
  - [ ] Scan QR code / open feedback page for an in-progress complaint.
  - [ ] Confirm feedback form is accessible and submits without altering complaint status.
- [ ] **5. Total Complaints Action Buttons (`/admin/approvals`)**:
  - [ ] Verify complaints with feedback show the green check-circle button.
  - [ ] Verify Addressed complaints have a disabled Edit button.
- [ ] **6. Dashboard Export to PDF (`/admin/dashboard`)**:
  - [ ] Click any complaint stat card popup; click **Export to PDF**.
  - [ ] Confirm 8-column landscape PDF opens cleanly.
- [ ] **7. Complaint Print Slip (`/admin/complaints/{id}/print-slip`)**:
  - [ ] Confirm print slip renders in the compact 580px width layout with Sub Category & Priority.

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
