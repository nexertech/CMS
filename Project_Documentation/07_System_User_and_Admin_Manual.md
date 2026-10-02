# Pakistan Navy Complaint Management System (CMS)
# Document 07: Comprehensive System User & Administration Manual

**Document Identifier:** CMS-OPS-V1.2.4  
**Target Audience:** Helpdesk Operators, Garrison Engineers (GE / Supervisors), CMES / XEN Officers, Storekeepers, System Administrators  
**Classification:** Restricted (Internal Pakistan Navy Housing & Engineering Directorate)  
**Date:** September 2026  
**Status:** Approved for Operations & Handover  

---

## 1. Document Control & Operational Overview

### 1.1 Purpose
This manual serves as the standard operational guide and administrative reference for the **Pakistan Navy Complaint Management System (CMS)**. It specifies exact operating procedures for handling residential complaints, coordinating engineering dispatches, sanctioning material requisitions through the Performa engine, managing warehouse stock, monitoring SLA compliance, and administering user roles.

### 1.2 Target Stakeholder Personas
The system provides tailored interfaces and workflows based on six core operational roles:

| Role Title | Operational Responsibilities | Primary Interface Modules |
|---|---|---|
| **Super Administrator** | Platform governance, RBAC permissions, audit logging, master catalog configuration | All Modules (`/admin/*`) |
| **CMES / XEN Officer** | Strategic oversight, financial sanction of priced performas, SLA compliance audit | Executive Dashboard, Approvals, Reports |
| **Garrison Engineer (GE) / Supervisor** | Ticket triage, technician allocation, work order generation, technical inspection | Complaints Board, Assign, Performas, Staff |
| **Helpdesk / Control Room Operator** | Call/walk-in intake, resident data validation, ticket registration, status follow-up | Register Complaint, Board, Houses, Devices |
| **Storekeeper / Inventory Manager** | Inward purchase entries, outward material issuance against vouchers, stock ledger | Spares Catalog, Stock Inward, Issue Slips |
| **Resident / Naval House Occupant** | Submitting maintenance requests, selecting availability slots, rating work | Mobile App / Resident Web Portal |

---

## 2. Daily Operational Workflow: End-to-End Complaint Lifecycle

The following workflow illustrates the end-to-end operational path of a maintenance request from initial registration to financial reconciliation and resident sign-off:

```
+─────────────────────────────────────────────────────────────────────────────────+
|                 DAILY OPERATIONAL WORKFLOW: COMPLAINT LIFECYCLE                 |
+─────────────────────────────────────────────────────────────────────────────────+
|                                                                                 |
|  [ Step 1: Lodging ] ───▶ [ Step 2: Assignment ] ───▶ [ Step 3: Field Visit ]   |
|  Resident App / Desk       Supervisor assigns Tech      Technician Inspects     |
|                                                                  │              |
|                   ┌──────────────────────────────────────────────┴──────┐       |
|                   ▼                                                     ▼       |
|         [ Standard Repair ]                                  [ Materials Req. ] |
|      Technician fixes fault                                  GE Performa Entry  |
|                   │                                                     │       |
|                   │                                                     ▼       |
|                   │                                          [ GE Approval ]    |
|                   │                                          Stock Sanction     |
|                   │                                                     │       |
|                   │                                                     ▼       |
|                   │                                          [ Stock Issue ]    |
|                   │                                          Store Dispatch     |
|                   │                                                     │       |
|                   └──────────────────────┬──────────────────────────────┘       |
|                                          ▼                                      |
|                                [ Step 4: Resolution ]                           |
|                               Marked Resolved (Status 1)                        |
|                                          │                                      |
|                                          ▼                                      |
|                              [ Step 5: Resident Rating ]                        |
|                             Resident rates 1-5 & Signs Off                      |
|                                                                                 |
+─────────────────────────────────────────────────────────────────────────────────+
```

---

## 3. Helpdesk Operations: Complaint Intake & Ticket Registration

The Helpdesk / Control Room is the front-line interface for capturing resident issues via phone call, radio transmission, or walk-in service desks.

### 3.1 Step-by-Step Ticket Registration
1. Navigate to **Complaints** -> **Register Complaint** (`/admin/complaints/create`).
2. **Geographic Identification (Cascading Filters):**
   * **City:** Select station (e.g. *Karachi*, *Islamabad*).
   * **CMES Formation:** Select the responsible engineering unit (e.g. *CMES (Navy) Karachi*).
   * **Housing Sector:** Select the designated colony/zone (e.g. *Karsaz*, *Naval Heights*).
   * **House / Accommodation:** Type or select the accommodation number from the predictive search.
3. **Resident Verification:**
   * System automatically displays the registered occupant's Rank, Name, Official ID, and Contact Number.
   * If details differ from caller, verify authorization before proceeding.
4. **Issue Classification:**
   * **Category:** Choose technical discipline (*Electrical*, *Civil / Plumbing*, *Carpentry*, *Sui Gas*, *HVAC*).
   * **Sub-Category:** Select defect group (e.g. *Ceiling Fan*, *Internal Water Supply*, *Main Circuit Breaker*).
   * **Complaint Title:** Select standard fault title (e.g. *Fan Motor Burned*, *Pipe Leakage Behind Wall*).
5. **Operational Priority & Time Window:**
   * **Priority Level:**
     * **Normal:** Standard routine repair (SLA: 24 - 48 Hours).
     * **Emergency:** Life safety, water flooding, power outage, or gas leak (SLA: 2 Hours).
   * **Availability Time:** Inquire occupant's preferred inspection window (e.g. `09:00 - 12:00` or `14:00 - 17:00`).
6. **Detailed Remarks & Photo Attachments:**
   * Enter precise diagnostic notes provided by the occupant.
   * Attach photo evidence if uploaded by the resident or captured on-site.
7. **Submission:** Click **"Submit Ticket"**. The system generates a unique **Tracking ID** and sets status to **Unassigned (2)**. An automated SMS alert is dispatched to the occupant.

---

## 4. Engineering Dispatch & Field Supervision (GE Office & Field Supervisor Guide)

The **Garrison Engineer (GE)** and Maintenance Supervisors govern technician assignments, job tracking, and field exceptions.

### 4.1 Ticket Dispatching Workflow
1. Navigate to **Complaints Board** (`/admin/complaints`).
2. Filter the grid by **Status: Unassigned** and your assigned **Sector**.
3. Click the target ticket to inspect complaint parameters and occupant availability.
4. In the **Assignment Section**:
   * Open the **Assigned Employee** dropdown. The system indicates active job counts beside each technician's name to prevent staff overloading.
   * Select a qualified technician matching the trade category.
5. Click **"Assign Technician"**. The ticket status transitions to **Assigned (3)**.

### 4.2 Job Dispatch Slip Printing
* On the complaint details view, click **"Print Dispatch Slip"** (`/admin/complaints/{id}/print-slip`).
* The system produces a physical job card containing:
  * Official Navy CMS header and QR code.
  * House number, occupant rank/name, and contact number.
  * Fault description and reported symptoms.
  * Assigned technician badge details.
  * Blank signature block for resident sign-off upon completion.

### 4.3 Field Exception Workflows
When technicians arrive on-site, unexpected circumstances may prevent immediate resolution. Technicians report back to the maintenance supervisor to apply specific status codes:

| Field Condition | System Action | Status Code | Required Operational Action |
|---|---|:---:|---|
| **House Locked / Occupant Absent** | Mark as **"Door Lock"** | `11` | Automated SMS notification sent to resident. Rescheduled for subsequent availability window. |
| **Material / Part Not in Store** | Mark as **"Product N/A"** | `8` | Triggers procurement alert to Storekeeper to initiate replenishment purchase order. |
| **Unauthorized Structural Alteration** | Mark as **"Un-Authorized"** | `9` | Work suspended immediately. Incident flagged to GE / CMES for official structural inquiry. |
| **Damage due to Resident Negligence** | Mark as **"Barrack Damages"** | `10` | Form B drafted for cost recovery. Repair deferred until damage assessment approval. |
| **Routine Fix Complete** | Mark as **"Resolved"** | `1` | Ticket marked resolved pending resident confirmation and star rating. |
| **Final Closure** | Mark as **"Closed"** | `4` | Administrative closure after inspection and quality verification. |

---

## 5. Material Requisition & The Performa Approval Engine

When maintenance requires parts beyond standard service toolkits, formal financial and inventory sanctions must be recorded.

### 5.1 Performa Classification Hierarchy
CMS enforces three distinct approval levels depending on material valuation:

* **Work Performa (Code 4):** Minor consumables (e.g. tap washers, light switches, fuses). Sanctioned directly at GE level.
* **Maintenance Performa (Code 5):** Standard structural replacements (e.g. sanitary fixtures, ceiling fan rewinding, distribution boards). Sanctioned by the **Garrison Engineer (GE)**.
* **Priced Performa (Codes 6 & 7):** Major capital replacements exceeding monetary threshold (e.g. main distribution cable, water pump motor replacement). Mandates formal digital signature by **Executive Engineer (XEN)** or **CMES** in concurrence with the **GE**.

### 5.2 Creating a Material Performa (Field Supervisor / In-Charge)
1. Open the active complaint and scroll to **Material Requisition** section.
2. Click **"Generate Material Performa"**.
3. Select appropriate **Performa Type** (Work, Maintenance, or Priced).
4. Click **"Add Spare Part"**:
   * Select item from the verified warehouse catalog.
   * System displays real-time warehouse availability.
   * Input required **Quantity** and justification remarks.
5. Repeat for all required items.
6. Click **"Submit for Approval"**. The Performa voucher transitions to `Pending` status.

### 5.3 Authorizing Performas (GE / Garrison Engineer Approval Authority)
1. Navigate to **Approvals** -> **Pending Performas** (`/admin/approvals`).
2. Inspect the voucher line items, estimated financial cost, and complaint history.
3. The **Garrison Engineer (GE)** or designated sanctioning officer reviews the request:
   * **Approve:** Performa status changes to `Approved`. System reserves stock in warehouse and generates a store issuance token.
   * **Reject:** Provide mandatory rejection rationale. Ticket returns to the maintenance supervisor for alternate engineering solution.
   * **Revise:** Modify sanctioned quantities or substitute specified items before granting approval.

---

## 6. Warehouse & Spares Inventory Management (Storekeeper Guide)

The Storekeeper manages warehouse inventory balances, inward stock deliveries, and outward dispatches against sanctioned vouchers.

### 6.1 Inward Stock Entry (Receiving Goods)
1. Navigate to **Inventory** -> **Spares Catalog** (`/admin/spares`).
2. Click **"Add Stock"** (`/admin/spares/{id}/add-stock`).
3. Enter inward delivery parameters:
   * **Vendor / Contractor:** Select certified supplier.
   * **Purchase Order (PO) / Delivery Challan:** Reference number.
   * **Batch / Lot Number:** If applicable for warranty tracking.
   * **Quantity Received:** Numerical count.
   * **Unit Cost:** Cost per unit in PKR.
4. Click **"Save Inward Stock"**. The ledger balance updates instantaneously, and an immutable record is registered in `stock_add_data`.

### 6.2 Outward Material Issuance against Sanctioned Performa
1. Open **Approvals** -> **Approved Performas** or **Spares** -> **Issue Stock**.
2. Scan or enter the sanctioned **Performa Token Number**.
3. Verify that the collecting technician's identity matches the assigned work order.
4. Click **"Issue Stock"**.
5. **System Effects:**
   * Sanctioned quantities are decremented from `spares.current_stock`.
   * Items are permanently debited to the complaint's expenditure ledger in `complaint_spares`.
   * Click **"Print Material Issuance Slip"** (`/admin/spares/{id}/print-slip`) for store archives.

### 6.3 Low Stock & Reorder Thresholds
The catalog dashboard incorporates visual threshold indicators:
* **Green (Normal):** Current stock is safely above safety buffer (`current_stock > minimum_stock`).
* **Amber (Warning):** Stock has reached or dropped below reorder level (`current_stock <= minimum_stock`). Storekeeper must prepare procurement requisition.
* **Red (Depleted):** Zero inventory balance. System automatically restricts dispatching this part on new performas.

---

## 7. Service Level Agreements (SLA) & Quality Assurance

### 7.1 SLA Architecture
CMS monitors ticket progression against trade-specific time benchmarks:

| Category | Priority | Max Response Time (Dispatch) | Max Resolution Time | Escalation Threshold |
|---|---|:---:|:---:|:---:|
| **Gas Leakage / Fire Hazard** | Critical | 15 Minutes | 2 Hours | 30 Minutes |
| **Main Water Pipe Burst** | Emergency | 30 Minutes | 4 Hours | 1 Hour |
| **Total Electrical Blackout** | Emergency | 30 Minutes | 4 Hours | 1 Hour |
| **Routine Electrical / Plumbing** | Normal | 4 Hours | 24 Hours | 12 Hours |
| **Civil / Carpentry / Painting** | Normal | 8 Hours | 48 Hours | 24 Hours |

### 7.2 Resident Feedback & Rating Engine
1. Following resolution, the resident is prompted via mobile app or SMS link to review the service.
2. Resident rates service on a **1 to 5 Star Scale** and leaves qualitative remarks.
3. **Automated Escalation Rule:**
   * Any rating of **1 or 2 Stars** triggers an automated quality audit alert on the GE and CMES dashboards.
   * The GE / Maintenance Supervisor must contact the occupant within 24 hours to address grievances and record corrective actions.

---

## 8. Master Data & Administrative Governance

System Administrators maintain structural directories, user credentials, and platform governance.

### 8.1 Staff & Technician Onboarding
1. Navigate to **Employee Management** (`/admin/employees`).
2. Click **"Add New Employee"**.
3. Input official particulars: Full Name, CNIC, Official Designation, Trade Category (e.g. Lineman, Plumber, Mason), Mobile Number, and Base Station.
4. Set status to **Active (1)**.

### 8.2 House & Accommodation Directory (Batch CSV Operations)
1. Navigate to **House Management** (`/admin/houses`).
2. To import batch accommodation records:
   * Click **"Download Sample CSV"** (`/admin/houses/sample-csv`).
   * Populate required columns: `city_id`, `cme_id`, `sector_id`, `house_no`, `occupant_rank`, `occupant_name`, `mobile_no`.
   * Click **"Import Houses"** and upload the verified CSV spreadsheet.
3. System validates records and indexes accommodations for helpdesk autocomplete.

### 8.3 Mobile Device Binding Governance (`/admin/registered-devices`)
To ensure operational security, resident accounts are cryptographically bound to authorized mobile hardware:
* Each house account may register a maximum of authorized handheld devices.
* **Device Reset Procedure:** When an officer or resident changes mobile handsets and receives a *"Device Registration Mismatch"* warning:
  1. Open **Registered Devices** (`/admin/registered-devices`).
  2. Search by House Number or Officer Rank.
  3. Verify resident identity through official naval directory.
  4. Click **"Revoke / Reset Device Binding"**.
  5. The resident can immediately log in from the new device, establishing a clean security bond.

### 8.4 Role-Based Access Control (RBAC) Management
* CMS implements strict **Exact-Match Permission Verification** (`in_array($module, $permissions, true)`).
* To configure role privileges:
  1. Open **Role Management** (`/admin/roles`).
  2. Select target role (e.g. *Helpdesk Operator*, *Storekeeper*).
  3. Explicitly tick individual module permission checkboxes.
  4. Save. Changes take effect on next user login session.

---

## 9. Reports, Analytics & Executive Decision Support

### 9.1 Standard Reporting Suites
Navigate to **Reports & Analytics** (`/admin/reports`) to extract verified audit documentation:

* **Complaint Volume & Resolution Report:** Aggregates total logged tickets, active jobs, resolved counts, and average resolution times broken down by City, Sector, and Trade.
* **Technician Performance Matrix:** Evaluates individual technician output, average turnaround time, active backlog, and resident satisfaction score.
* **Spares Consumption & Valuation Audit:** Itemized breakdown of materials issued per accommodation and category, comparing budget allocations against actual consumption.
* **SLA Breach & Escalation Report:** Logs all tickets that exceeded prescribed response or resolution windows with recorded supervisor justifications.

### 9.2 Export Protocols
All analytical datasets can be generated in three standardized formats:
1. **Interactive Screen View:** High-level executive charts with drill-down capability.
2. **Executive PDF Memo:** Pre-formatted, publication-ready A4 documentation containing official naval headers, sign-off blocks, and summary statistics.
3. **Raw CSV / Excel Data:** Tabular dumps for external auditing, spreadsheet analysis, and financial reconciliation.

---

## 10. Operational Troubleshooting & Frequently Asked Questions (FAQ)

### 10.1 Access & Authentication Issues
* **Q: A staff member receives a "403 Forbidden - Unauthorized Action" error.**
  * *Resolution:* Navigate to **Role Management** (`/admin/roles`), inspect the assigned role, and ensure the specific sub-permission (e.g. `complaints.view`, `spares.add-stock`) is explicitly checked. Have the user log out and log back in.
* **Q: A technician does not appear in the assignment dropdown list.**
  * *Resolution:* Ensure the employee account is set to `status = 1` (Active) and their designated trade category matches the complaint discipline.

### 10.2 Workflow & Ticket Issues
* **Q: Can a complaint be marked "Resolved" while a Material Performa is still pending?**
  * *Resolution:* No. The system state machine prevents ticket closure if an associated performa is in `pending` or `draft` state. The performa must either be formally approved or cancelled first.
* **Q: How to handle duplicate complaints lodged by multiple family members?**
  * *Resolution:* Open the duplicate ticket, navigate to status update, and select **"Closed"** with remarks: *"Duplicate of Ticket #[Original_ID]"*.

### 10.3 Inventory & Stock Discrepancies
* **Q: The system shows zero stock for an item that is physically present in the warehouse.**
  * *Resolution:* The Storekeeper must perform an **Inward Stock Entry** referencing the delivery voucher before the system will authorize warehouse dispatch.


