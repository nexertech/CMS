# Deploy Day — One-Page Cheat Sheet

Full details: `CMS_Deployment_Runbook.md`. This page is what you actually do on the day.
Always log in as **`paknavy`** (never root, never sudo). PHP binary: `/opt/cpanel/ea-php82/root/usr/bin/php`

---

## Before you touch anything — get 3 answers from Amjid

1. **Which version tag?** (e.g. `v1.2.0`)
2. **Any database change (migration)?** Yes / No
3. **Which page do I test first?**

If he can't answer all 3 clearly, **stop** — don't deploy.

---

## Deploy — run in order

```bash
cd /home/paknavy

# 1. Back up current live (code + database)
cp -r public_html public_html_backup_$(date +%F)
#    also: phpMyAdmin -> Export -> download the .sql (save off the server too)

# 2. Clear leftovers from last time
rm -rf public_html_new
rm -rf public_html_old

# 3. Get the new version
git clone --branch v1.2.0 https://github.com/nexertech/CMS.git public_html_new
cd public_html_new

# 4. Bring over the live-only files
cp /home/paknavy/public_html/.env .env
rm -rf storage
cp -r /home/paknavy/public_html/storage ./
cp /home/paknavy/deploy-assets/app-root-htaccess.txt ./.htaccess

# 5. Permissions
chmod -R 775 storage bootstrap/cache
chown -R paknavy:paknavy storage bootstrap/cache

# 6. Database change? ONLY if Amjid said YES:
# /opt/cpanel/ea-php82/root/usr/bin/php artisan migrate --force

# 7. THE SWAP (do this BEFORE caching)
cd /home/paknavy
mv public_html public_html_old
mv public_html_new public_html

# 8. Build caches — AFTER the swap, in the final folder
cd /home/paknavy/public_html
/opt/cpanel/ea-php82/root/usr/bin/php artisan config:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan route:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan view:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan config:cache
/opt/cpanel/ea-php82/root/usr/bin/php artisan route:cache
/opt/cpanel/ea-php82/root/usr/bin/php artisan view:cache
```

---

## Test before you call it done

- [ ] Homepage loads
- [ ] Admin login works
- [ ] The page Amjid told you to test works
- [ ] Attachments/images show (if not: `php artisan storage:link`)
- [ ] Security: browsing the raw **IP** does NOT show a file listing, and `/.env` and `/.git/config` return 403/404
- [ ] Record the version: `echo "v1.2.0" > /home/paknavy/current-live-version.txt`

---

## If something breaks

**Remember: git swaps the CODE, not the DATABASE.**

**Just deployed, broken, undo fast:**
```bash
cd /home/paknavy
mv public_html public_html_bad
mv public_html_old public_html
```

**Go to a specific version (any version):**
```bash
cd /home/paknavy/public_html
git tag -n        # list all tags before fetch tags (optional)
git fetch --tags  # fetch all tags from the remote
git tag -n        # list all tags after fetch tags (optional)
git checkout v1.1.1
# then the cache block (config/route/view: clear then cache) — always
```

**Database went wrong (only if a migration ran):** restore the `.sql` backup in phpMyAdmin, then `git checkout` the matching version.

---

## Two rules that prevent 90% of problems

1. **Cache commands run AFTER the swap** — never in `public_html_new`. (Skipping causes a 500 with an empty log.)
2. **Always ask about the database and export it before deploying** — it's the only way to undo a database change.
