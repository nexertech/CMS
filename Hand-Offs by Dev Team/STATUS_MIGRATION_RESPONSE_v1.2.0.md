# Dev Team → Deployment Team: Status Migration & Mapping Review Response

**From:** Amjad (Dev Team)  
**To:** Siddique / Deployment Team  
**Re:** Response to `STATUS_MIGRATION_REVIEW by Deployment Team.md`  
**Related Release:** `v1.2.0`  
**Date:** 2026-08-02  

---

## 1. Executive Summary

Thank you to you and your Claude Code agent for the thorough review against live `public_html.zip`. 

We confirm 100% agreement on the status migration strategy for future live data cutovers. 

Below are the answers to the two items raised, along with the finalized SQL migration query and status mapping reference.

---

## 2. Clarifications & Decisions

### 2.1 Decision on Legacy `closed` Complaints
- **Decision:** **CONFIRMED — Map legacy `'closed'` complaints to `1` (`STATUS_RESOLVED` / Addressed).**
- **Rationale:** In `v1.2.0`, the `closed` status has been intentionally removed from the active application lifecycle. Since both `resolved` and `closed` represented completed tasks in the legacy application, all legacy `closed` database records will cleanly map to `1` (`resolved`).

### 2.2 Clarification on `new` vs `unassigned`
- In `v1.2.0`, employee assignment is optional during complaint creation. The string key `new` was replaced by `unassigned` (`STATUS_UNASSIGNED = 2`).
- To prevent any legacy database records with status `'new'` from hitting the `ELSE` fallback, the SQL query explicitly includes `WHEN 'new' THEN 2`.

---

## 3. Finalized Safe SQL Migration Query (For Future Live Cutover)

For future releases migrating live production databases containing historical complaints:

```sql
-- STEP 1: PRE-FLIGHT CHECK (Run first to inspect every status string in the live table)
SELECT status, COUNT(*) AS n FROM complaints GROUP BY status ORDER BY n DESC;

-- STEP 2: SAFE STATUS CONVERSION
UPDATE complaints SET status = CASE status
    WHEN 'new'                   THEN 2   -- Legacy 'new' -> unassigned(2)
    WHEN 'unassigned'            THEN 2   -- Default unassigned(2)
    WHEN 'in_progress'           THEN 0
    WHEN 'assigned'              THEN 3
    WHEN 'resolved'              THEN 1
    WHEN 'closed'                THEN 1   -- Legacy 'closed' -> resolved(1)
    WHEN 'work_performa'         THEN 4
    WHEN 'maint_performa'        THEN 5
    WHEN 'work_priced_performa'  THEN 6
    WHEN 'maint_priced_performa' THEN 7
    WHEN 'product_na'            THEN 8
    WHEN 'un_authorized'         THEN 9
    WHEN 'barrack_damages'       THEN 10
    WHEN 'door_lock'             THEN 11
    ELSE 2
END;

-- STEP 3: SCHEMA ALTERATION
ALTER TABLE `complaints` MODIFY `status` TINYINT NOT NULL DEFAULT 2;
```

---

## 4. Complete Status ID Reference Matrix

| String Key (`old_word`) | Numeric ID (`TINYINT`) | Constant Name | Display Label in UI | Notes |
|---|---|---|---|---|
| `in_progress` | **`0`** | `STATUS_IN_PROGRESS` | In Progress | Active work |
| `resolved` | **`1`** | `STATUS_RESOLVED` | Addressed | Resolved complaint |
| `closed` *(Legacy)* | **`1`** | `STATUS_RESOLVED` | Addressed | Folds legacy 'closed' into resolved(1) |
| `unassigned` / `new` | **`2`** | `STATUS_UNASSIGNED` | Unassigned | Default status |
| `assigned` | **`3`** | `STATUS_ASSIGNED` | Assigned | Assigned to employee |
| `work_performa` | **`4`** | `STATUS_WORK_PERFORMA` | Work Performa | Performa created |
| `maint_performa` | **`5`** | `STATUS_MAINT_PERFORMA` | Maintenance Performa | Maintenance performa |
| `work_priced_performa` | **`6`** | `STATUS_WORK_PRICED_PERFORMA` | Work Performa Priced | Priced performa |
| `maint_priced_performa` | **`7`** | `STATUS_MAINT_PRICED_PERFORMA` | Maintenance Performa Priced | Priced maintenance |
| `product_na` | **`8`** | `STATUS_PRODUCT_NA` | Product N/A | Spare not available |
| `un_authorized` | **`9`** | `STATUS_UN_AUTHORIZED` | Un-Authorized | Unauthorized |
| `barrack_damages` | **`10`** | `STATUS_BARRACK_DAMAGES` | Barrack Damages | Barrack damages |
| `door_lock` | **`11`** | `STATUS_DOOR_LOCK` | Door Lock | Door lock issue |

---
*All files in `Hand-Offs by Dev Team` have been updated and synced.*
