# Pakistan Navy Complaint Management System (CMS)
# Document 01: User Requirements Document (URD)

**Document Identifier:** CMS-URD-V1.2.4  
**Target Audience:** Navy Housing Directorate, CMES Directorate, Engineering Officers, Operations Staff  
**Date:** September 2026  
**Status:** Approved for Handover  

---

## 1. Executive Summary & Project Purpose

The **Pakistan Navy Complaint Management System (CMS)** is a centralized digital governance and maintenance automation platform designed to manage and resolve civil, electrical, plumbing, carpentry, and general infrastructural complaints arising within naval residential estates, barracks, mess facilities, and administrative establishments.

Prior to the deployment of CMS, complaint resolution suffered from manual paper-based registers, unverified verbal phone calls, lack of parts traceability, delayed technician dispatching, and absent audit trails. The CMS replaces legacy processes with an automated, transparent, multi-channel workflow enabling naval residents, complaint cell operators, engineering supervisors, and field technicians to collaborate in real time.

---

## 2. Business Problem Statement & Objectives

### 2.1 Current Operational Bottlenecks
* **Lack of Accountability:** Paper complaints frequently get lost, misdirected, or closed without physical inspection or resident sign-off.
* **Inventory Leakage & Material Abuse:** High-value maintenance materials (spares, wiring, piping, sanitary ware) were utilized without formal requisition or financial ceiling approval.
* **Absence of SLA Monitoring:** Emergency civic emergencies (e.g. electrical short circuits, main waterline ruptures) were treated with identical priority to aesthetic maintenance.
* **No Direct Resident Communication:** Residents remained uninformed regarding technician arrival schedules, component availability, or estimated resolution times.

### 2.2 Core Business Objectives
1. **100% Digital Lifecycle:** Provide end-to-end digital tracking from complaint submission to physical closure.
2. **Material Transparency:** Ensure every spare part issued is mapped to a specific complaint, approved via a formal Performa, and logged in real-time inventory balances.
3. **Hierarchy-Aware Routing:** Align complaints with naval geographic hierarchies (City -> CMES -> Sector -> House/Barrack).
4. **Service Level Agreement (SLA) Adherence:** Automatically escalate unresolved or delayed issues to higher naval authorities.
5. **Auditable Governance:** Preserve complete historical activity logs, device signatures, and resident feedback.

---

## 3. User Stakeholder Personas & Roles

The system caters to six distinct stakeholder groups:

```
+---------------------------------------------------------------------------------+
|                               STAKEHOLDER HIERARCHY                             |
+---------------------------------------------------------------------------------+
|                                                                                 |
|   [ Resident / Naval Personnel ] ---- (Mobile App / Portal)                     |
|                   │                                                             |
|                   ▼                                                             |
|   [ Control Room / Helpdesk Operator ] ── (Intake, Verification & Dispatch)     |
|                   │                                                             |
|                   ▼                                                             |
|   [ Maintenance Technician / Staff ] ──── (Inspection & Material Request)       |
|                   │                                                             |
|                   ▼                                                             |
|   [ GE / Maintenance Supervisor ] ─────── (Technical Review & Performa Gen)     |
|                   │                                                             |
|                   ▼                                                             |
|   [ CMES / Executive Engineer (XEN) ] ── (Financial / High-Value Approval)      |
|                   │                                                             |
|                   ▼                                                             |
|   [ Super Administrator ] ────────────── (Platform Governance & Audit)          |
|                                                                                 |
+---------------------------------------------------------------------------------+
```

### 3.1 Resident / Naval House Occupant (End User)
* **Profile:** Commissioned officers, sailors, civilian staff, or family members residing in official naval quarters or barracks.
* **Core Needs:**
  * Simple, secure login using official house credentials and registered mobile devices.
  * Instant lodging of complaints with photo attachments and audio/text notes.
  * Selection of preferred technician visit times (Availability Slot).
  * Real-time notifications on status transitions and technician assignment.
  * Submission of mandatory post-resolution rating (Feedback & Star rating).

### 3.2 Helpdesk / Complaint Cell Operator
* **Profile:** Centralized operations center personnel receiving citizen visits or phone inquiries.
* **Core Needs:**
  * Rapid data-entry UI to lodge complaints on behalf of residents.
  * Real-time search across houses, sectors, and complaint categories.
  * Immediate visibility into technician workloads and availability.
  * Dispatching complaints to appropriate field staff with a single click.

### 3.3 Field Maintenance Staff (Technicians)
* **Profile:** Electricians, Plumbers, Carpenters, Masons, and HVAC technicians employed by CMES.
* **Core Needs:**
  * Clear list of daily job allocations with house address, contact info, and fault summary.
  * Ability to request spare parts directly against an active complaint ID.
  * Ability to mark status flags: Door Lock, Product N/A, Barrack Damages, or Work Complete.

### 3.4 Garrison Engineer (GE) / Maintenance Supervisor
* **Profile:** Military engineering officers and maintenance supervisors responsible for a specific sector, technical review, and material sanctions.
* **Core Needs:**
  * Reviewing technician field inspection reports.
  * Generating and validating formal **Work Performas** or **Maintenance Performas** for non-standard repairs.
  * Recommending spare parts and validating material requisition costs.
  * Monitoring sector SLA compliance and technician job completion speed.

### 3.5 Executive Engineer (XEN) / CMES Officer / Directorate
* **Profile:** Senior command officers with administrative and financial spending authority.
* **Core Needs:**
  * Review and sign off on high-value or priced performas.
  * Comprehensive business intelligence dashboards showing sector-wise complaint trends, recurring defects, and budget consumption.
  * Exportable PDF/Excel executive reports for naval board meetings.

### 3.6 System Administrator
* **Profile:** IT Directorate / Technical administrators managing software infrastructure.
* **Core Needs:**
  * Granular Role-Based Access Control (RBAC) to enforce strict boundary separation.
  * Device registration controls to prevent unauthorized access.
  * System health, database backups, audit logs, and version control.

---

## 4. High-Level User Requirements

### 4.1 Requirement Category A: Identity, Housing & Device Verification
* **URD-REQ-001 (Unique Residence Profiling):** Every official naval accommodation must be registered with City, Sector, Block, House Number, and occupant details.
* **URD-REQ-002 (Device Binding):** Resident mobile applications must bind to authorized device identifiers (IMEI/UUID) to prevent fraudulent submissions from external unauthorized parties.
* **URD-REQ-003 (Access History):** The platform must maintain a detailed login history with IP addresses, timestamps, and browser/device metadata for all administrative and resident sessions.

### 4.2 Requirement Category B: Complaint Management Lifecycle
* **URD-REQ-004 (Hierarchical Taxonomy):** Complaints must follow a strict three-tier classification:
  1. *Category* (e.g., Electrical, Civil, Water Supply, Carpentry).
  2. *Sub-Category* (e.g., Internal Wiring, Sanitary Fittings, Doors & Windows).
  3. *Complaint Title* (e.g., Ceiling Fan Motor Burnt, Main Valve Leakage).
* **URD-REQ-005 (Priority & Emergency Routing):** Users must be able to designate complaints as **Normal** or **Emergency**. Emergency complaints must visually flash in red on operator consoles and trigger expedited SLA rules.
* **URD-REQ-006 (12-Stage Lifecycle Visibility):** The system must support clear real-time progression across defined military operational statuses (Unassigned, Assigned, In Progress, Performa Generation, Addressed/Resolved, Door Lock, Barrack Damages, etc.).

### 4.3 Requirement Category C: Spare Parts, Requisitions & Performas
* **URD-REQ-007 (Spares Catalog):** Centralized catalog of inventory items including Item Name, Brand, Vendor, Part Code, Unit of Measure, and Current Stock Level.
* **URD-REQ-008 (Performa Workflow):** When repairs require materials beyond basic toolkits:
  * Technician requests items on-site.
  * Maintenance supervisor initiates a formal **Spare Approval Performa** (Work Performa, Maintenance Performa, or Priced Performa).
  * The **GE (Garrison Engineer)** or Competent Authority reviews and approves/rejects the performa.
  * Inventory balances decrement automatically upon authorized issuance.
* **URD-REQ-009 (Damage Attribution):** Where damage is caused by negligence rather than standard wear-and-tear, the system must support tagging as **Barrack Damages** for cost recovery assessment.

### 4.4 Requirement Category D: Analytics, Executive Dashboards & SLA
* **URD-REQ-010 (Executive Dashboards):** Visual breakdown of:
  * Total Complaints Lodged vs Resolved.
  * Technician Efficiency & Mean Time to Resolution (MTTR).
  * Aging Analysis (Pending > 24h, > 48h, > 7 Days).
  * Category-wise Heatmaps of frequent structural failures.
* **URD-REQ-011 (Automated Escalation):** If a complaint exceeds the threshold defined in `sla_rules`, automated escalation markers must alert senior supervisors.
* **URD-REQ-012 (Citizen Feedback & Quality Audit):** No complaint can transition to permanent archival without verified resident satisfaction rating and feedback comments.

---

## 5. Non-Functional & Operational Requirements

* **Accessibility & UI Themes:** The system must offer multiple UI contrast modes (Light, Dark, Night/Low Blue Light, Auto) compliant with WCAG AA standards to facilitate 24/7 control room shifts.
* **Security & Confidentiality:** All passwords hashed via bcrypt. All internal routes guarded with isolated exact-match permissions. Strict rate limiting on public and mobile endpoints.
* **Availability & Reliability:** 99.9% uptime requirement during active duty operational hours. Automated database backups daily.
* **Device Responsiveness:** Fully functional across desktop monitors, rugged field tablets, and mobile smartphones.

---

## 6. Assumptions and Operational Dependencies

1. **Network Connectivity:** Resident mobile apps connect via standard cellular or naval Wi-Fi. In the event of temporary network loss, requests queue locally.
2. **Authority Signatures:** Approval authority roles must be designated by official Naval CMES administrative orders.
3. **Data Integrity:** House allocation records are kept up-to-date by the Naval Housing Directorate.
