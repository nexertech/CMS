# Pakistan Navy Complaint Management System (CMS)
# Document 02: Software Requirements Specification (SRS)
## Formatted according to IEEE Std 830-1998 Standards

**Document Identifier:** CMS-SRS-V1.2.4  
**Product Version:** `v1.2.4`  
**Date:** September 2026  
**Status:** Approved for Handover  

---

## 1. Introduction

### 1.1 Purpose
This Software Requirements Specification (SRS) provides a comprehensive, rigorous technical description of the **Pakistan Navy Complaint Management System (CMS)**. It defines all functional capabilities, performance criteria, security parameters, database interactions, external API contracts, and structural interfaces required by the system.

### 1.2 Document Conventions
* **Keywords:** The terms MUST, MUST NOT, REQUIRED, SHALL, SHALL NOT, SHOULD, and RECOMMENDED are interpreted according to RFC 2119.
* **Identifiers:** Functional requirements are prefixed with `FR-`, Non-Functional with `NFR-`, and Interface requirements with `IFR-`.

### 1.3 Intended Audience
This document is prepared for:
1. **Software Engineering & DevOps Teams:** For maintenance, future module development, and deployment orchestration.
2. **Quality Assurance (QA) & Testing Teams:** As the primary baseline for unit, functional, integration, and regression test suites.
3. **Naval IT & Cyber Security Auditors:** To verify compliance with data security, authorization boundaries, and encryption standards.
4. **Project Managers & Naval Command:** To validate deliverables against contracted operational requirements.

### 1.4 Project Scope
The CMS is an enterprise web and API platform that manages civic, infrastructure, and technical complaint handling for Pakistan Navy housing directorates and CMES formations. It encompasses:
* Web-based administrative and operational portal.
* Mobile REST API gateway for resident smartphones and field devices.
* Inventory tracking and multi-tier electronic approval performa workflows.
* Granular Role-Based Access Control (RBAC) across 19 system modules.

---

## 2. Overall Description

### 2.1 Product Perspective & System Architecture
The CMS operates as a three-tier decoupled client-server architecture:

```
+─────────────────────────────────────────────────────────────────────────────────+
|                               SYSTEM CONTEXT DIAGRAM                            |
+─────────────────────────────────────────────────────────────────────────────────+
|                                                                                 |
|  [ Resident Mobile App ]        [ Control Room Browser ]     [ Admin / Officers ]|
|  (Android / iOS Native)         (Desktop Blade UI)           (Desktop / Tablet)  |
|            │                                │                         │         |
|            ▼                                ▼                         ▼         |
|    HTTPS / REST API                HTTPS / Web Routes        HTTPS / Web Routes |
|   (Sanctum / Manual Auth)          (CSRF Protected)          (RBAC Protected)   |
|            │                                │                         │         |
|            └───────────────────────┬────────┴─────────────────────────┘         |
|                                    ▼                                            |
|                  ┌───────────────────────────────────┐                          |
|                  │    Web Server & Reverse Proxy     │                          |
|                  │  (Apache / cPanel Linux VPS)      │                          |
|                  └─────────────────┬─────────────────┘                          |
|                                    ▼                                            |
|                  ┌───────────────────────────────────┐                          |
|                  │  Application Layer (Laravel 12+)  │                          |
|                  │  - HTTP Routing & Middleware      │                          |
|                  │  - Rate Limiting (Throttle)       │                          |
|                  │  - Granular RBAC Engine           │                          |
|                  │  - Complaint State Machine        │                          |
|                  │  - Performa Approval Engine       │                          |
|                  │  - Stock Audit & Logging          │                          |
|                  └─────────────────┬─────────────────┘                          |
|                                    ▼                                            |
|                  ┌───────────────────────────────────┐                          |
|                  │     Persistence Layer (MySQL 8)   │                          |
|                  │  - 34 Relational Tables           │                          |
|                  │  - Indexed Foreign Key Integrity  │                          |
|                  │  - Soft Delete Auditing           │                          |
|                  └───────────────────────────────────┘                          |
|                                                                                 |
+─────────────────────────────────────────────────────────────────────────────────+
```

### 2.2 Operating Environment
* **Server Operating System:** Linux (CentOS / CloudLinux via cPanel VPS)
* **PHP Runtime:** PHP 8.2 (specifically `/opt/cpanel/ea-php82/root/usr/bin/php`)
* **Web Server:** Apache 2.4 with `mod_rewrite`, `mod_headers`, and custom security `.htaccess`
* **Database Engine:** MySQL 8.0 Community / MariaDB 10.6+ (InnoDB Engine)
* **Client Browsers:** Google Chrome 90+, Mozilla Firefox 88+, Microsoft Edge 90+, Safari 14+
* **Mobile Operating Systems:** Android 8.0+ and iOS 13.0+

### 2.3 User Classes and Access Hierarchy
1. **Super Administrator (Role ID 1):** Unrestricted, immutable master access to all platform configurations, routes, audits, and database records.
2. **Executive Engineer / CMES Staff:** High-level supervisory review, budget approvals, and complex performa authorization.
3. **Garrison Engineer (GE) / Maintenance Supervisor:** Sector-level operational supervisory access, technician allocation, and performa authorization.
4. **Helpdesk / Data Entry Operator:** Front-office complaint registration, resident lookups, and basic technician dispatching.
5. **Field Technician / Employee:** Read-only access to assigned complaints, material requests, and progress updates.
6. **Resident / House Occupant:** Mobile app authenticated user restricted strictly to self-registered complaints, profile settings, and satisfaction feedback.

---

## 3. Detailed Functional Requirements

### 3.1 Module 1: Authentication & Session Management (FR-AUTH)
* **FR-AUTH-001 (Multi-Guard Authentication):** The system MUST support independent authentication guards for administrative personnel (`web` guard via `users` table) and housing occupants (`api` / `manual.auth` guard via `houses` and `frontend_users` tables).
* **FR-AUTH-002 (Password Encryption):** All user credentials MUST be hashed using the bcrypt algorithm with a minimum work factor of 10. Plaintext passwords MUST NEVER be logged or persisted.
* **FR-AUTH-003 (Device Binding Verification):** Mobile API requests MUST pass device verification against the `registered_devices` registry, confirming IMEI/UUID status before granting access tokens.
* **FR-AUTH-004 (Audit Trail Logging):** Every administrative and resident authentication event (success or failure) MUST be recorded in the `login_history` table with user ID, IP address, user-agent string, and timestamp.
* **FR-AUTH-005 (Rate Limiting Protection):** The authentication endpoints MUST enforce strict throttling: maximum 5 login attempts per minute per IP address (`throttle:5,1`).

### 3.2 Module 2: Granular Role-Based Access Control (FR-RBAC)
* **FR-RBAC-001 (Isolated Module Permissions):** The authorization layer MUST evaluate exact-match module permission strings without automatic cascading or parent-to-child inheritance.
* **FR-RBAC-002 (Supported Modules):** The system MUST maintain isolated permission definitions for the following 19 modules:
  `dashboard`, `users`, `frontend-users`, `cmes`, `city`, `sector`, `roles`, `employees`, `designation`, `houses`, `complaints`, `category`, `sub-category`, `complaint-titles`, `approvals`, `spares`, `reports`, `sla`, `registered-devices`.
* **FR-RBAC-003 (Route Middleware Enforcement):** Every protected administrative route MUST be guarded by the `permission:{module}.{action}` middleware.
* **FR-RBAC-004 (Superadmin Auto-Assignment):** Upon role creation or update, if `role_id === 1`, all 19 module permissions MUST be automatically synchronized via Eloquent model lifecycle hooks (`Role::boot()`).

### 3.3 Module 3: Geographic & Structural Directory (FR-GEO)
* **FR-GEO-001 (City Management):** System MUST allow administrative CRUD operations for regional cities.
* **FR-GEO-002 (CMES Formation):** System MUST map Commander Military Engineering Services (CME) units to respective cities.
* **FR-GEO-003 (Sector Partitioning):** System MUST support sector definitions linked to specific CME units and cities.
* **FR-GEO-004 (House Registry):** System MUST maintain records of individual houses/accommodations, capturing Sector, House Number, Block, Category, Contact Number, and Occupant Credentials.

### 3.4 Module 4: Staff & Technician Roster (FR-STAFF)
* **FR-STAFF-001 (Designations):** System MUST manage job titles/trades (e.g., Electrician, Line-man, Plumber, Valve-man, Carpenter, Mason).
* **FR-STAFF-002 (Employee Profiles):** System MUST record technician details, CNIC, mobile numbers, active status, and designated trade/designation.
* **FR-STAFF-003 (Workload Assignment):** The dispatch interface MUST display active complaints assigned per technician to prevent unbalanced allocations.

### 3.5 Module 5: Complaint Lifecycle Engine (FR-TICK)
* **FR-TICK-001 (Taxonomy Classification):** Complaints MUST require Category ID, Sub-Category ID, and Complaint Title ID.
* **FR-TICK-002 (Priority & Emergency Handling):** Complaints MUST support priority classifications: `normal` and `emergency`.
* **FR-TICK-003 (Availability Booking):** System MUST record the resident's designated visit window (`availability_time`).
* **FR-TICK-004 (Attachment Support):** System MUST support uploading multiple image attachments per complaint, securely stored with randomized filenames in `/storage/app/public/complaints/`.
* **FR-TICK-005 (12-Stage State Machine):** The `complaints.status` attribute MUST be stored as a `TINYINT` conforming to the exact operational specification:
  * `0`: In Progress (`STATUS_IN_PROGRESS`)
  * `1`: Addressed / Resolved (`STATUS_RESOLVED`)
  * `2`: Unassigned / New (`STATUS_UNASSIGNED` - Default)
  * `3`: Assigned (`STATUS_ASSIGNED`)
  * `4`: Work Performa (`STATUS_WORK_PERFORMA`)
  * `5`: Maintenance Performa (`STATUS_MAINT_PERFORMA`)
  * `6`: Work Performa Priced (`STATUS_WORK_PRICED_PERFORMA`)
  * `7`: Maintenance Performa Priced (`STATUS_MAINT_PRICED_PERFORMA`)
  * `8`: Product N/A (`STATUS_PRODUCT_NA`)
  * `9`: Un-Authorized (`STATUS_UN_AUTHORIZED`)
  * `10`: Barrack Damages (`STATUS_BARRACK_DAMAGES`)
  * `11`: Door Lock (`STATUS_DOOR_LOCK`)
* **FR-TICK-006 (Audit Logging):** Every status change, assignment change, or remark entry MUST generate an immutable record in `complaint_logs` with editor identity, old state, new state, and timestamp.
* **FR-TICK-007 (Citizen Feedback):** Resolved complaints MUST prompt for star ratings (1 to 5) and textual remarks in `complaint_feedbacks`.

### 3.6 Module 6: Spare Parts & Approval Performa Engine (FR-SPARE)
* **FR-SPARE-001 (Catalog Management):** System MUST track spares with name, item code, brand ID, vendor ID, unit of measure, unit price, and stock on hand.
* **FR-SPARE-002 (Stock Movements):** All incoming stock purchases MUST be logged in `stock_add_data` and historical adjustments in `spare_stock_logs`.
* **FR-SPARE-003 (Performa Creation):** Supervisors MUST be able to generate formal approval performas (`spare_approval_performa`) containing multiple line items (`spare_approval_items`).
* **FR-SPARE-004 (Multi-Level Authorization):** Performas MUST support states: `pending`, `approved`, `rejected`, and `barrack_damages`.
* **FR-SPARE-005 (Automatic Inventory Deduction):** Upon formal approval of a performa, the system MUST automatically decrement the physical quantity from `spares.current_stock` and record issuance in `complaint_spares`.

### 3.7 Module 7: Service Level Agreements & Escalations (FR-SLA)
* **FR-SLA-001 (Rule Definition):** System MUST allow administrators to define SLA rules based on Category, Priority, and target resolution hours.
* **FR-SLA-002 (Breach Tracking):** The system MUST calculate breach time (`created_at + sla_hours`) and visually highlight overdue tickets on supervisor dashboards.

### 3.8 Module 8: Mobile RESTful API Gateway (FR-API)
* **FR-API-001 (Payload Format):** All API responses MUST be serialized in standard RFC 8259 JSON format with structured `status`, `message`, and `data` keys.
* **FR-API-002 (Throttling Matrix):**
  * `/api/house/login`: Max 5 requests/min.
  * `/api/complaints/register`: Max 15 requests/min.
  * General read routes: Max 60 requests/min.
* **FR-API-003 (App Version Control):** The endpoint `/api/app/check-update` MUST return mandatory update flags, download URLs, and minimum supported version numbers.

### 3.9 Module 9: Theme & Accessibility Engine (FR-THEME)
* **FR-THEME-001 (Tri-Mode Switching):** The interface MUST support `Light`, `Dark`, `Night` (low blue-light amber tint), and `Auto` modes.
* **FR-THEME-002 (Persistence):** User theme preferences MUST persist to the database for authenticated users and via HTTP cookies for guests.
* **FR-THEME-003 (WCAG Compliance):** All themes MUST maintain WCAG AA compliant color contrast ratios (minimum 4.5:1 for normal text).

---

## 4. Non-Functional Requirements (NFR)

### 4.1 Performance & Scalability (NFR-PERF)
* **NFR-PERF-001 (Response Time):** 95% of standard administrative HTTP requests MUST render in under 800 milliseconds on a standard broadband connection.
* **NFR-PERF-002 (API Latency):** Mobile API endpoints MUST respond within 350 milliseconds under a concurrent load of 150 requests.
* **NFR-PERF-003 (Database Indexing):** All foreign key lookup columns and filtering combinations (`city_id`, `sector_id`, `status`, `created_at`) MUST be covered by compound B-tree indexes.

### 4.2 Security & Data Protection (NFR-SEC)
* **NFR-SEC-001 (SQL Injection Immunity):** All database interactions MUST utilize PDO parameterized queries via Laravel Eloquent ORM. Raw SQL concatenations are strictly forbidden.
* **NFR-SEC-002 (Cross-Site Scripting - XSS):** All Blade templating output MUST use automatic escaping syntax (`{{ $data }}`).
* **NFR-SEC-003 (CSRF Protection):** All incoming POST/PUT/DELETE web requests MUST carry a valid CSRF token.
* **NFR-SEC-004 (File Upload Validation):** Complaint attachments MUST be strictly validated against MIME types (image/jpeg, image/png, image/webp) with a maximum file size ceiling of 5MB. Executable extensions (`.php`, `.sh`, `.exe`) MUST be rejected at the web server and application layers.

### 4.3 Availability & Reliability (NFR-REL)
* **NFR-REL-001 (Uptime Target):** The system MUST achieve 99.9% uptime, excluding planned maintenance windows.
* **NFR-REL-002 (Soft Deletes):** Core business tables (`complaints`, `spares`, `users`, `employees`, `houses`) MUST implement soft deletion (`deleted_at`) to prevent irreversible accidental data loss.

---

## 5. System Verification & Requirements Traceability Matrix

| Requirement ID | Module / Feature | Code Implementation File | Verification Method |
|---|---|---|---|
| `FR-AUTH-001` | Multi-Guard Auth | `app/Http/Controllers/Api/HouseAuthController.php` | Integration Test / Postman |
| `FR-RBAC-001` | Isolated Exact Permissions | `app/Models/Role.php:hasPermission` | Unit Test |
| `FR-RBAC-003` | Middleware Route Protection | `routes/web.php` & `app/Http/Middleware/CheckRolePermission.php` | Functional Route Audit |
| `FR-TICK-005` | 12-Stage State Machine | `app/Models/Complaint.php` & `STATUS_MAPPING_REFERENCE.md` | Database Schema / Form Testing |
| `FR-SPARE-004` | Performa Approval Engine | `app/Models/SpareApprovalPerforma.php` | Workflow Inspection |
| `FR-SPARE-005` | Automatic Stock Deduction | `app/Http/Controllers/SpareController.php` | Transaction Test |
| `FR-API-002` | API Rate Throttling | `routes/api.php` | Load Test (ab / wrk) |
| `FR-THEME-001` | Tri-Mode UI Accessibility | `app/Http/Controllers/ThemeController.php` | UI Contrast Audit |
