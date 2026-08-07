# CMS Release & Deployment Hand-Off — TEMPLATE

> **How to use this file.** Amjid (or his AI) copies this template for each release, fills every
> field, and sends it to Siddique. Siddique deploys straight from it using
> `CMS_Deployment_Runbook.md` / `DEPLOY_CHEATSHEET.md`. **Do not leave a field blank** — write
> "None" or "N/A" instead, so a missing answer is never mistaken for an overlooked one.
> If any field below is unclear or empty, Siddique should **stop and ask** before touching the server.

---

**Application:** Navy Complaint Management CMS
**Repository:** `https://github.com/nexertech/CMS.git`
**Release Tag:** `vX.Y.Z`  *(immutable tag pushed to the `deploy` branch)*
**Target Branch:** `deploy`
**Previous Live Tag:** `vX.Y.Z`  *(what this release replaces — from `current-live-version.txt`)*
**Developer:** Amjid
**Deployment Engineer:** Siddique / Deployment Team
**Date:** YYYY-MM-DD

---

## 1. The 3 Mandatory Answers (fill these first)

| Question | Answer |
|---|---|
| **1. Release tag?** | `vX.Y.Z` |
| **2. Database change?** | **None** / **Migration** / **Manual SQL** — pick one (details in §3) |
| **3. Primary page(s) to test first?** | e.g. `/admin/complaints/create`, `/admin/dashboard` |

If all three aren't answered clearly, **do not deploy.**

---

## 2. What Changed (summary for the deploy engineer)

Short, testable bullets — what a person can click and verify, not internal refactors:

1. …
2. …
3. …

---

## 3. Database Change — pick ONE, never both

> Once the DB is changed, the fast folder-swap rollback is **not enough on its own** — the Step 1
> `.sql` backup becomes mandatory for rollback. See Runbook Step 5.

- [ ] **None** — no schema or data change. Skip Runbook Step 5 entirely.

- [ ] **Migration** — a Laravel migration file ships in this tag. Deploy with:
  `php artisan migrate --force` (Runbook Step 5a).

- [ ] **Manual SQL** — statements to paste into phpMyAdmin → SQL (Runbook Step 5b). **No `artisan migrate`.**
  - **Is it destructive or a column-type change?** Yes / No
    *(If yes, wrap the change + swap in maintenance mode: `artisan down` → SQL → swap → caches → `artisan up`.)*
  - **Exact statements (paste verbatim):**
    ```sql
    -- e.g.
    -- ALTER TABLE `complaints` MODIFY `status` TINYINT NOT NULL DEFAULT 2;
    ```
  - **Does existing production data need converting first?** Yes / No
    *(If a column type changes, values can't auto-convert — supply the mapping `UPDATE` to run BEFORE the `ALTER`. A `TRUNCATE` is acceptable ONLY when the table is confirmed dummy/empty.)*

---

## 4. Build & Asset Confirmation (so the deploy needs no composer/npm)

The `paknavy` server has no Composer/npm — the tag must carry built artifacts. Confirm:

- [ ] `vendor/` is committed on tag `vX.Y.Z` (`composer install --no-dev --optimize-autoloader` was run)
- [ ] `public/build/` is committed on tag `vX.Y.Z` (`npm run build` was run — required if JS/CSS/Blade classes changed)
- [ ] `composer.json` changed? Yes / No
- [ ] `package.json` / CSS / JS changed? Yes / No
- [ ] New `.env` variable introduced? Yes / No
  *(If yes: name + placeholder added to `.env.example`, and listed here — **never** send a real `.env` value.)*
  - New var(s): `NAME=<placeholder>` …

---

## 5. Post-Deployment Test Checklist

- [ ] Homepage loads; admin login works
- [ ] Primary page(s) from §1 work as described
- [ ] Attachments/images render (if not: `php artisan storage:link`)
- [ ] Feature-specific checks:
  - [ ] …
  - [ ] …
- [ ] **Security (every deploy):** raw server IP shows **no** `Index of /`; `/.env`, `/.git/config`,
      `/composer.json` return 403/404
- [ ] Record the version: `echo "vX.Y.Z" > /home/paknavy/current-live-version.txt`

---

## 6. Rollback Note for This Release

- **If DB change = None:** git rollback alone is sufficient (folder swap, or `git checkout <prev-tag>` + cache block).
- **If DB change = Migration or Manual SQL:** code rollback is **not** enough — restore the Step 1 `.sql`
  backup, then `git checkout` the matching tag. Note here anything that makes rollback non-trivial:
  - …
