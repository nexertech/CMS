# CMS Complaint Status Mapping Reference (CHAR / String -> TINYINT Integer)

This reference document maps all status keys in the CMS application to their corresponding `TINYINT` integer values.

In `v1.2.0`, the `complaints.status` column was converted to `TINYINT NOT NULL DEFAULT 2`.

---

## Complete Status Mapping Table

| String Key (`old_word`) | Numeric ID (`TINYINT`) | Constant Name | Display Label in UI | Notes |
|---|---|---|---|---|
| `in_progress` | **`0`** | `STATUS_IN_PROGRESS` | In Progress | Active work |
| `resolved` | **`1`** | `STATUS_RESOLVED` | Addressed | Done / Resolved |
| `closed` *(Legacy)* | **`1`** | `STATUS_RESOLVED` | Addressed | Folds legacy 'closed' into resolved(1) |
| `unassigned` / `new` | **`2`** | `STATUS_UNASSIGNED` *(Default)* | Unassigned | Deployed app stores unassigned as 'new' |
| `assigned` | **`3`** | `STATUS_ASSIGNED` | Assigned | Assigned to technician |
| `work_performa` | **`4`** | `STATUS_WORK_PERFORMA` | Work Performa | Performa created |
| `maint_performa` | **`5`** | `STATUS_MAINT_PERFORMA` | Maintenance Performa | Maintenance performa created |
| `work_priced_performa` | **`6`** | `STATUS_WORK_PRICED_PERFORMA` | Work Performa Priced | Priced performa |
| `maint_priced_performa` | **`7`** | `STATUS_MAINT_PRICED_PERFORMA` | Maintenance Performa Priced | Priced maintenance |
| `product_na` | **`8`** | `STATUS_PRODUCT_NA` | Product N/A | Spare not available |
| `un_authorized` | **`9`** | `STATUS_UN_AUTHORIZED` | Un-Authorized | Unauthorized |
| `barrack_damages` | **`10`** | `STATUS_BARRACK_DAMAGES` | Barrack Damages | Barrack damages |
| `door_lock` | **`11`** | `STATUS_DOOR_LOCK` | Door Lock | Door lock issue |

---

## SQL Migration Query for Live Data (Safe UPDATE without TRUNCATE)

For future data migrations on live databases containing active records:

```sql
-- STEP 1: PRE-FLIGHT (Run first to inspect every string value in the table)
SELECT status, COUNT(*) AS n FROM complaints GROUP BY status ORDER BY n DESC;

-- STEP 2: CONVERSION QUERY
UPDATE complaints SET status = CASE status
    WHEN 'new'                   THEN 2   -- Live app stores 'unassigned' as 'new'
    WHEN 'unassigned'            THEN 2
    WHEN 'in_progress'           THEN 0
    WHEN 'assigned'              THEN 3
    WHEN 'resolved'              THEN 1
    WHEN 'closed'                THEN 1   -- Legacy 'closed' mapped to 'resolved' (1)
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
