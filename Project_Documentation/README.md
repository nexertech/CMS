# Pakistan Navy Complaint Management System (CMS)
## Official Project Documentation & Handover Package

**Project Name:** Pakistan Navy Complaint Management System (CMS)  
**Repository:** `https://github.com/nexertech/CMS.git`  
**Current Production Version:** `v1.2.4`  
**Document Generation Date:** September 2026  

---

### Executive Overview

This documentation package contains the complete formal technical, functional, architectural, and operational documentation suite for the **Pakistan Navy Complaint Management System (CMS)**. 

The system is an enterprise-grade, multi-tier web and mobile backend platform built using **Laravel (PHP 8.2)**, **MySQL**, and **RESTful JSON APIs**. It is engineered to streamline, automate, and track the end-to-end maintenance and civic complaint lifecycle across naval housing directorates, Commander Military Engineering Services (CME/CMES), administrative sectors, and field technician workforces.

---

### Documentation Suite Structure

| Document Reference | Document Title | Target Audience | Primary Focus |
|---|---|---|---|
| [**01_URD**](file:///c:/xampp/htdocs/CMS/Project_Documentation/01_URD_User_Requirements_Document.md) | **User Requirements Document (URD)** | Executive Leadership, Naval Officers, End Users | Business needs, problem statement, resident and officer workflows, high-level system expectations. |
| [**02_SRS**](file:///c:/xampp/htdocs/CMS/Project_Documentation/02_SRS_Software_Requirements_Specification.md) | **Software Requirements Specification (SRS)** | Project Managers, System Architects, QA Engineers | IEEE 830 compliant technical specifications, functional & non-functional requirements, data workflows. |
| [**03_RBAC**](file:///c:/xampp/htdocs/CMS/Project_Documentation/03_RBAC_Role_Based_Access_Control_Manual.md) | **Role-Based Access Control (RBAC) Manual** | System Administrators, Security Auditors | Security matrix, 19+ granular module permissions, role hierarchy, isolation architecture, and role management. |
| [**04_DEV**](file:///c:/xampp/htdocs/CMS/Project_Documentation/04_Developer_and_Technical_Manual.md) | **Developer & Technical Setup Manual** | Software Engineers, DevOps Engineers | Local environment setup, directory architecture, API contracts, deployment runbook (cPanel/VPS atomic swap). |
| [**05_ERD**](file:///c:/xampp/htdocs/CMS/Project_Documentation/05_Database_ERD_and_Data_Dictionary.md) | **Database ERD & Data Dictionary** | Database Administrators, Backend Developers | Visual Entity Relationship Diagrams (Mermaid), 30+ tables schema, foreign keys, indexes, and status mappings. |
| [**06_CODE**](file:///c:/xampp/htdocs/CMS/Project_Documentation/06_Code_Commenting_Standard_and_Audit.md) | **Code Commenting & Engineering Standards** | Code Auditors, Development Team | PSR-12 formatting, DocBlocks, architectural commenting guidelines, and critical path audit. |
| [**07_OPS**](file:///c:/xampp/htdocs/CMS/Project_Documentation/07_System_User_and_Admin_Manual.md) | **System User & Administration Manual** | Control Room Operators, Maintenance Staff | Operational walkthroughs: Complaint intake, performa approvals, spare part issuance, resident verification. |

---

### Key System Highlights & Architecture Metrics

```
+---------------------------------------------------------------------------------+
|                       PAKISTAN NAVY COMPLAINT MANAGEMENT CMS                    |
+---------------------------------------------------------------------------------+
|  Core Framework:     Laravel 12.x on ea-php82 (JazzCloud VPS / Linux cPanel)    |
|  Database Engine:    MySQL 8.0 InnoDB with Granular Foreign Keys & Soft Deletes |
|  Access Control:     19+ Exact-Match Granular Isolated Module Permissions       |
|  Service Workflows:  12 Lifecycle Statuses (TINYINT State Machine)              |
|  Inventory Mgmt:     Spare Parts, Multi-Item Approval Performas, Stock Audit    |
|  Integrations:       Mobile Android/iOS REST APIs, Device Registration, Sanitized|
|  Accessibility:      WCAG AA Compliant Theme Engine (Light, Dark, Night, Auto)  |
+---------------------------------------------------------------------------------+
```

---

### Document Maintenance & Revision Log

| Revision | Date | Author / Team | Description |
|---|---|---|---|
| `v1.0.0` | January 2025 | Nexertech Core Team | Initial architecture and core complaint ticketing engine. |
| `v1.2.0` | October 2025 | Development Team | State machine refactor: Integer `TINYINT` status migration, approval performa integration. |
| `v1.2.3` | July 2026 | Amjad Subhani (Dev Team) | Mobile API hardening, device management, rate limiting, and session security. |
| `v1.2.4` | August 2026 | Amjad Subhani / Deployment Team | Granular sub-module permission isolation (`Role::hasPermission` exact matching), route middleware sync. |
| `v1.2.4-DOCS` | September 2026 | Engineering Lead | Comprehensive 7-volume documentation and formal handover package compilation. |
