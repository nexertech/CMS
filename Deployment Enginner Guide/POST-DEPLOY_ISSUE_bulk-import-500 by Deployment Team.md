# Deployment Team → Dev: Post-Deploy Issue Report — Bulk Import 500 (RESOLVED)

**From:** Deployment Team (Siddique)
**To:** Amjad (Dev Team)
**Live version:** `v1.2.0`
**Issue found:** 2026-08-10 (during v1.2.0 feature testing)
**Status:** ✅ **RESOLVED on production via server config — no redeploy needed**

---

## 1. Summary

The new **Bulk Excel/CSV Import** (feature #7) returned **HTTP 500** on both
`/admin/houses/import` and `/admin/employees/import`, for **both `.csv` and `.xlsx`** files.

Root cause was a **missing PHP extension on the production server**, not an application code
fault. It has been fixed by installing that extension. Imports now work for both file types on
both modules.

---

## 2. Root cause (from `laravel.log`)

```
production.ERROR: Unable to guess the MIME type as no guessers are available
(have you enabled the php_fileinfo extension?)
  Symfony\Component\Mime\Exception\LogicException  at symfony/mime/MimeTypes.php:129
  #2 ValidatesAttributes.php: ->guessExtension()
  #3 Validator->validateMimes('file', …)
  #9 app/Http/Controllers/Admin/HouseController.php(444): ->validate([...])
```

- The crash is at the **`$request->validate([... 'mimes:csv,txt,xls,xlsx' ...])`** call
  (`HouseController.php:444`; `EmployeeController` has the same rule).
- Laravel's **`mimes:` rule sniffs the real MIME type using PHP's `fileinfo` extension** —
  and `fileinfo` was **not enabled** on the production PHP 8.2 build.
- No `fileinfo` → `LogicException` thrown **during validation**, *before* any file parsing,
  *before* the method's `try/catch` — so it surfaces as a raw 500.
- This is why **both `.csv` and `.xlsx` failed identically** — the `mimes` check runs on every
  upload regardless of type, and never reaches `Shuchkin\SimpleXLSX`.

Confirmed on the box: `php -m | grep -i fileinfo` returned **nothing** before the fix.

---

## 3. Note on `v1.2.1`

The `v1.2.1` hand-off diagnoses this as a **`Shuchkin\SimpleXLSX` autoloader** issue and re-maps
the class in `autoload_classmap.php` / `autoload_static.php`. That does **not** address this bug:
- `SimpleXLSX` was already correctly classmapped in `v1.2.0` (verified).
- The failure is at **MIME validation**, before `SimpleXLSX` is ever reached.

`v1.2.1` is therefore **not required** to fix the import 500 (it's harmless if deployed for other
reasons, but it won't change this behaviour). Please re-scope or shelve it accordingly.

---

## 4. Fix applied (production)

Installed the extension as `root` via WHM Terminal (Ubuntu 24.04 cPanel → `apt`), targeting the
single package to avoid the EA4 full-profile provision (which was also proposing to uninstall
kernel/system packages — deliberately avoided):

```bash
apt-get install ea-php82-php-fileinfo
/opt/cpanel/ea-php82/root/usr/bin/php -m | grep -i fileinfo   # now prints: fileinfo
/scripts/restartsrv_apache_php_fpm
```

**Verified:** Houses + Employees import now succeed with both `.csv` and `.xlsx`. No app code
change, no redeploy — still `v1.2.0`.

---

## 5. Requested hardening for a future release

1. **Declare the dependency:** add `"ext-fileinfo": "*"` to `composer.json`'s `require` block, so
   the requirement is documented and any future server build knows to include it.
2. **Defense-in-depth (optional):** consider switching the upload rule from `mimes:` to Laravel's
   **`extensions:csv,txt,xls,xlsx`** rule, which validates by file extension and does **not**
   depend on `fileinfo` — so a missing extension can't 500 the import again.
3. **Obsolete step in `v1.2.1` hand-off:** Step 4 still copies
   `deploy-assets/app-root-htaccess.txt` → `.htaccess`. That `.htaccess` is now committed in the
   repo (since v1.2.0), so the clone brings it — drop that copy line, keep only verification.

---

## 6. Other production note

At the client's request, the **dummy complaint data was cleared** on 2026-08-10 (complaints +
attachments/logs/spares/feedbacks truncated; complaint_titles/categories and all other data
preserved). Production `complaints` count is now **0** — ready for live data entry.
