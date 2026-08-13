# Deployment Team → Dev: Performance Issue — Slow Pages After House Bulk Import (Change Request)

**From:** Deployment Team (Siddique)
**To:** Amjad (Dev Team)
**Live version:** `v1.2.0`
**Found:** 2026-08-10, after the client bulk-imported houses
**Severity:** High — affects the operators' main data-entry page (complaint registration)

---

## 1. Symptom
After the client imported **13,502 houses**, admin pages — especially **Complaint → Register/New**
(`/admin/complaints/create`) and the complaint **edit** form — became slow to load.

## 2. Confirmed root cause (two compounding issues)

**(a) The complaint form loads ALL active houses into a `<select>`.**
`ComplaintController::create()`:
```php
$housesQuery = House::where('status', 1)->orderBy('house_no');
$this->filterHousesByLocation($housesQuery, $authUser);
$houses = $housesQuery->get();          // ← all matching houses, no limit
```
`resources/views/admin/complaints/create.blade.php` (~line 122): `@foreach($houses as $house)` →
one `<option>` per house. Same pattern on the edit form (`ComplaintController` ~line 812). With 13.5k
houses this queries, serializes, and renders them all on every page open.

**(b) The `houses` table has no useful indexes.**
`SHOW INDEX FROM houses` on production shows only `PRIMARY (id)` and `houses_username_unique
(username)`. There is **no index on `status`, `house_no`, `city_id`, `sector_id`**. Evidence:
```
EXPLAIN SELECT id, house_no FROM houses WHERE status=1 ORDER BY house_no;
→ type: ALL   rows: 13205   Extra: Using where; Using filesort
```
So the dropdown query, the houses-list filters, and the import's own duplicate check all do full
table scans + filesort.

## 3. Requested fixes

**Fix 1 — Add indexes (as a repo migration).**
Deployment Team is applying these indexes to production **now** for immediate relief:
```sql
ALTER TABLE `houses`
  ADD INDEX `houses_status_house_no_idx`   (`status`, `house_no`),
  ADD INDEX `houses_city_status_hno_idx`   (`city_id`, `status`, `house_no`),
  ADD INDEX `houses_sector_id_idx`         (`sector_id`),
  ADD INDEX `houses_dupcheck_idx`          (`house_no`, `city_id`, `sector_id`);
```
Please **add the same indexes as a migration** in the repo so fresh installs / new environments get
them. **Make the migration idempotent** (guard each with an existence check, e.g.
`if (!collect(DB::select("SHOW INDEX FROM houses"))->pluck('Key_name')->contains('houses_status_house_no_idx'))`
before adding) — production will already have these indexes by the time any migration runs there, so
a blind `->index()` would fail with "Duplicate key name."

**Fix 2 — Replace the "load all houses" dropdown with a searchable AJAX selector (the real fix).**
Indexes make the query fast but sending/rendering 13.5k `<option>`s is still heavy. Please convert the
house field on the complaint **create** and **edit** forms to a server-side searchable autocomplete:
- Add an endpoint, e.g. `GET /admin/houses/search?q=...&city_id=...&sector_id=...`, returning matching
  active houses with `LIMIT ~30`, respecting the same `filterHousesByLocation` scoping.
- Wire the `#house_id` field to Select2 (or equivalent) with `ajax`, so it fetches on-type only.
- Apply the same treatment to any other form that renders a full house (or employee) dropdown.
This makes page load constant-time regardless of house count.

## 4. Suggested release
Bundle both into the next patch (e.g. `v1.2.2`). No DB migration needs to run on the **current**
production box for Fix 1 (we've applied it manually) — the migration is for repo correctness and fresh
environments; keep it idempotent so it's safe everywhere.
