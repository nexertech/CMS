# CMS App — Official Release & Deployment Handoff

**Application:** Navy Complaint Management CMS  
**Repository:** `https://github.com/nexertech/CMS.git`  
**Release Tag:** `v1.2.0`  
**Target Branch:** `deploy`  
**Developer:** Amjad  
**Deployment Engineer:** Siddique / Deployment Team  

---

## 1. Developer Handover Summary (Key Features & Testable Changes in Release `v1.2.0`)

The developer has completed all features, compiled production assets, merged into the `deploy` branch, and pushed tag `v1.2.0` to GitHub.

### Key Changes & Testable Features (Comparison with Previous Tag):

1. **Multi-Complaint Single-Submission Flow**:
   - In Complaint Registration (`/admin/complaints/create`), multiple complaints can now be added and submitted simultaneously in a single form submission for a house/complainant.
2. **Simplified Priority System**:
   - Priority is now strictly updated to **2 options**: **Normal** and **Emergency** across complaint registration, filters, details, and reports.
3. **Complaint Audit Trail (`Registered By` & `Changed By`)**:
   - Added `Registered By` and `Changed By` tracking fields across all detail views (`/admin/complaints/show`, `/admin/approvals/show`, and frontend detail modal).
4. **Compact View Pages (Reduced Vertical Scrolling)**:
   - Redesigned details layouts for Complaints, Approvals, Employees, and Stock/Spares into clean, single-line flex rows (`info-item`).
5. **Upgraded Excel/CSV Export**:
   - Includes 17 full complaint fields in project hierarchy order.
   - `Registered By` placed before `Status`; `Description` column removed as requested.
   - Phone numbers formatted as text to force left-alignment and preserve leading zeros in Excel.
   - Single **Total Complaints** summary count at the bottom row.
   - Pure client-side JS Blob export (zero vendor dependencies, no server memory/timeout issues).
6. **Database Schema Update**: `complaints.status` column definition updated to `TINYINT NOT NULL DEFAULT 2`.

---

## 2. Answers to Deployment Engineer's Mandatory Questions

Before touching the server, here are the 3 required answers:

| Question | Answer |
|---|---|
| **1. Release Tag?** | **`v1.2.0`** *(Immutable tag pushed to `deploy` branch)* |
| **2. DB Migration / Schema Change?** | **Manual SQL Statement**. (No `artisan migrate`). Run 2 SQL queries in phpMyAdmin/MySQL *(See Step 6 below)*. |
| **3. Primary Pages to Test First?** | 1. `/admin/complaints/create` (Multi-complaint registration & Normal/Emergency priority)<br>2. `/admin/dashboard` (Modal card **Export to Excel**)<br>3. `/admin/complaints/{id}` & `/admin/approvals/{id}` (Compact views & audit fields) |

---

## 3. Step-by-Step Server Deployment Guide for Siddique

Run on JazzCloud cPanel VPS as user `paknavy` (`/opt/cpanel/ea-php82/root/usr/bin/php`).

```bash
cd /home/paknavy

# -------------------------------------------------------------
# STEP 1: Backup current live (Code + Database)
# -------------------------------------------------------------
cp -r public_html public_html_backup_$(date +%F)
# Export .sql backup via phpMyAdmin -> Export -> Download .sql

# -------------------------------------------------------------
# STEP 2: Clear staging leftovers from previous deploys
# -------------------------------------------------------------
rm -rf public_html_new
rm -rf public_html_old

# -------------------------------------------------------------
# STEP 3: Clone exact tagged release into staging
# -------------------------------------------------------------
git clone --branch v1.2.0 https://github.com/nexertech/CMS.git public_html_new
cd public_html_new

# -------------------------------------------------------------
# STEP 4: Bring over live-only configuration & storage
# -------------------------------------------------------------
cp /home/paknavy/public_html/.env .env
rm -rf storage
cp -r /home/paknavy/public_html/storage ./
cp /home/paknavy/deploy-assets/app-root-htaccess.txt ./.htaccess

# Note: Ensure Password Renewal Policy in live .env (last line) is set to 180 days:
# PASSWORD_RENEWAL_DAYS=180

# -------------------------------------------------------------
# STEP 5: Set storage permissions
# -------------------------------------------------------------
chmod -R 775 storage bootstrap/cache
chown -R paknavy:paknavy storage bootstrap/cache

# -------------------------------------------------------------
# STEP 6: Execute DB Updates (Run SQL via phpMyAdmin/MySQL)
# Do NOT run php artisan migrate --force
# -------------------------------------------------------------
# 1. Truncate/empty dummy complaints table (if needed):
TRUNCATE TABLE complaints;

# 2. Modify status column definition:
ALTER TABLE `complaints` MODIFY `status` TINYINT NOT NULL DEFAULT 2;

# -------------------------------------------------------------
# STEP 7: Atomic Swap (DO THIS BEFORE CACHING)
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
echo "v1.2.0" > /home/paknavy/current-live-version.txt
```

---

## 4. Key Post-Deployment Test Checklist (Important Changes to Verify)

- [ ] **Multi-Complaint Registration (`/admin/complaints/create`)**:
  - Test adding multiple complaints in a single form submission.
- [ ] **Priority System**:
  - Verify Priority displays strictly as **Normal** or **Emergency**.
- [ ] **Excel Report (`/admin/dashboard` Modal)**:
  - Click **Export to Excel** -> Verify CSV downloads with 17 columns, Left-Aligned Phone Numbers, and Total Complaints count at the bottom.
- [ ] **Audit Trail & Compact Views (`/admin/complaints/{id}`)**:
  - Verify single-line compact rows and `Registered By` / `Changed By` fields.
- [ ] **Live Config**:
  - Verify `PASSWORD_RENEWAL_DAYS=180` in `.env`.
- [ ] **Security**:
  - Raw server IP does **NOT** show directory index, and `/.env` / `/.git/config` return 403/404.
