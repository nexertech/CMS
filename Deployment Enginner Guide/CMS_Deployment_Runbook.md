# CMS App — Update & Deployment Runbook

Repo: `https://github.com/nexertech/CMS.git`
Deploy branch: `deploy` (built assets committed here only)
Live server: JazzCloud VPS, cPanel, user `paknavy`, path `/home/paknavy/public_html`
PHP binary: `/opt/cpanel/ea-php82/root/usr/bin/php`

Versioning: use simple semantic tags — `v1.2.0` → `v1.2.1` for a small fix, `v1.3.0` for a new feature.

---

## PART 1 — For Amjid: Pushing an Update to GitHub

Do this every time you finish a set of changes and want them deployed.

### Step 1: Finish and test your changes locally
Make sure everything works on your own machine first, in your normal working branch (`Amjad_branch`).

### Step 2: Decide the version number for this release
Look at the last tag pushed (Siddique can tell you, or check `git tag` in the repo). Pick the next one:
- Bug fix / small tweak → bump the last number (`v1.2.0` → `v1.2.1`)
- New feature → bump the middle number (`v1.2.1` → `v1.3.0`)

### Step 3: Build the production assets locally
```bash
# Only if you changed resources/js, resources/css, or package.json:
npm install
npm run build

# Always, to make sure vendor/ is production-clean:
composer install --no-dev --optimize-autoloader
```

### Step 4: Switch to the `deploy` branch and bring in your changes
```bash
git checkout deploy
git pull origin deploy          # make sure you have the latest deploy branch
git merge Amjad_branch          # bring your finished work into deploy
```
If there are merge conflicts, resolve them here — don't push until `deploy` builds and runs cleanly on your machine.

### Step 5: Force-add the build folders (they're normally git-ignored)
This only happens on the `deploy` branch — never do this on `Amjad_branch`.
```bash
git add -f vendor
git add -f public/build
git add -A
```

### Step 6: Commit with a clear release note
This message is what tells Siddique what he needs to know — be specific:
```bash
git commit -m "Release v1.2.1: Fixed dashboard multi-select filters and sidebar CSS.
No DB migrations. No composer.json changes. No package.json changes.
Test page: /admin/dashboard"
```
Always state explicitly in the message:
- Whether `composer.json` changed
- Whether `package.json` / CSS / JS changed
- Whether there's a new database migration
- Which page/feature Siddique should test first

### Step 7: Push the branch
```bash
git push origin deploy
```

### Step 8: Tag this exact commit and push the tag
```bash
git tag v1.2.1
git push origin v1.2.1
```

### Step 9: Tell Siddique directly
Send him a short message, e.g.:
> "Pushed `v1.2.1` to `deploy`. Only blade/CSS changes, no migrations, no composer/package changes. Please test `/admin/dashboard` after deploy."

**Never** send Siddique a `.env` file. If you need a new environment variable, add the variable name (with a placeholder, not the real value) to `.env.example` and mention it in your message.

---

## PART 2 — For Siddique: Safe Deployment with Rollback

Do this on the JazzCloud VPS. Always log in as `paknavy` — never `root`, never `sudo`.

### Step 0: Know what's currently live
Keep a small text file on the server, e.g. `/home/paknavy/current-live-version.txt`, containing the last tag you successfully deployed (e.g. `v1.2.0`). Check it before you start.

### Step 1: Back up everything currently live — do not skip this
```bash
cd /home/paknavy
cp -r public_html public_html_backup_$(date +%F)
```
Also export the database:
- cPanel → phpMyAdmin → select the database → Export → download the `.sql` file
- Save it somewhere off the server too (your laptop or S3), not just on the VPS

### Step 1b: Clear leftover `_old` / `_new` folders — REQUIRED from the 2nd deploy onward
The fixed folder names `public_html_old` and `public_html_new` are reused every deploy, so leftovers from last cycle must be cleared **before** the `mv` steps — and the two `mv` commands fail differently if you don't:
- `mv public_html public_html_old` when `public_html_old` **already exists** does NOT overwrite it — it moves your live folder *inside* it (`public_html_old/public_html`) with **no error**, silently breaking the site.
- `git clone ... public_html_new` when `public_html_new` exists errors out (loud, safe) — but blocks the deploy until cleared.

Only run this **after** the Step 1 dated backup exists and you've confirmed the current live site is healthy:
```bash
cd /home/paknavy
rm -rf public_html_new     # leftover staging from a prior/aborted run
rm -rf public_html_old     # last cycle's rollback folder — now two versions old
```
Why it's safe to delete `public_html_old` here: it holds the version from **two** deploys ago. The currently-live `public_html` is the recent proven-stable one, and this deploy's swap (Step 6) will create a **fresh** `public_html_old` from it — so your instant one-command rollback for *this* deploy still works. For jumping back two versions you have the dated `public_html_backup_*` copies.

### Step 2: Clone the exact tagged version into a new folder
Use the tag Amjid gave you — not the branch name — so you know exactly what you're deploying:
```bash
cd /home/paknavy
git clone --branch v1.2.1 https://github.com/nexertech/CMS.git public_html_new
cd public_html_new
```

### Step 3: Bring over the live-only files
These are correctly excluded from git, so copy them from the current live folder:
```bash
cp /home/paknavy/public_html/.env .env
rm -rf storage
cp -r /home/paknavy/public_html/storage ./
```
Do **not** copy `vendor/` or `public/build/` from live — the tag you cloned already has those built in, so you don't need Composer or npm on this server at all.

### Step 3b: Restore the app-root `.htaccess` — SECURITY, do not skip
The root `.htaccess` is a **live-only file**: it is not in git and not in the build zip, so every `git clone` / fresh extract **drops it**. Without it, browsing the server by raw IP renders a full `Index of /` listing of the source tree (the domain's docroot is `public/`, but the default/IP vhost serves the app root).

Keep the master copy **outside** `public_html` so folder swaps can't destroy it:
```bash
# one-time: place the master copy (content: deploy-assets/app-root-htaccess.txt)
mkdir -p /home/paknavy/deploy-assets
# ...upload app-root-htaccess.txt to /home/paknavy/deploy-assets/ ...

# every deploy: restore it into the new folder BEFORE the swap
cp /home/paknavy/deploy-assets/app-root-htaccess.txt /home/paknavy/public_html_new/.htaccess
```
If cPanel later appends its own `# cPanel-generated` php-handler/ini blocks to the bottom of the live `.htaccess`, that's normal — leave them; just make sure the guard rules stay at the top.

> **Why this keeps biting:** it silently regressed once already. `public_html_old.zip` had the guard rules; after the zip deploy, `public_html.zip` had only cPanel's auto-generated handler block — the security lines were gone and nobody noticed until the IP listing reappeared.

> **The `.git` directory is the new risk (git method only).** `git clone` puts `.git/` *inside the web-served app root*. A `<FilesMatch "...\.git">` rule does **not** protect it — `FilesMatch` matches file basenames only, so `/.git/config` and `/.git/HEAD` stay fetchable even with `-Indexes`, and the whole repo can be reconstructed from them (`git-dumper`). The guard file closes this with a path-based `RedirectMatch 404 /\.git(/|$)`. Never rely on the old `FilesMatch` rule alone.

### Step 4: Fix permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R paknavy:paknavy storage bootstrap/cache
```

### Step 5: Run the database migration — ONLY if Amjid said there is one
Confirm your Step 1 database backup exists first, then (migrations hit the DB and don't care about the folder name, so it's fine to run this in `public_html_new` before the swap):
```bash
/opt/cpanel/ea-php82/root/usr/bin/php artisan migrate --force
```
If Amjid said "no migrations," skip this step entirely.

### Step 6: The atomic switch — this is what makes it "safe"
```bash
cd /home/paknavy
mv public_html public_html_old
mv public_html_new public_html
```
The site is now running the new version. Nothing has been deleted yet.

### Step 7: Clear and rebuild caches — do this AFTER the swap, never before
> **Critical ordering rule.** `config:cache`, `route:cache`, and `view:cache` bake the folder's **absolute path** into the cached files (`bootstrap/cache/config.php`, `bootstrap/cache/routes-v7.php`, and every compiled Blade view under `storage/framework/views/`). If you cache while the app is still called `public_html_new` and then rename it to `public_html`, every baked path points at a folder that no longer exists → **500 on every page.**
>
> The tell-tale sign of this specific failure: the site 500s but **nothing new appears in `storage/logs/laravel.log`** — because the cached log path also points at the dead `public_html_new` folder, so Laravel can't even write the error. If you ever see "500 + empty/stale log," suspect stale cached paths first.
>
> That is why caching moved to *after* the atomic swap in this runbook. Always `cd` into the final `public_html` before running these.

```bash
cd /home/paknavy/public_html
# clear any caches carried in from the build/staging folder first
/opt/cpanel/ea-php82/root/usr/bin/php artisan config:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan route:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan view:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan cache:clear
# now rebuild — paths bake against the correct /home/paknavy/public_html
/opt/cpanel/ea-php82/root/usr/bin/php artisan config:cache
/opt/cpanel/ea-php82/root/usr/bin/php artisan route:cache
/opt/cpanel/ea-php82/root/usr/bin/php artisan view:cache
```
If `config:clear` / `cache:clear` themselves error (they can, when the cache path baked in from staging is already dead), delete the stale files by hand, then run the rebuild trio above:
```bash
cd /home/paknavy/public_html
rm -f bootstrap/cache/config.php bootstrap/cache/routes-v7.php
rm -f storage/framework/views/*.php
```
Sanity check the rebuild picked up the right folder (should print `0`):
```bash
grep -c public_html_new bootstrap/cache/config.php
```

### Step 8: Test immediately
- Load the live domain — does the homepage work?
- Log in as admin — does it work?
- Go specifically to the page Amjid told you to test
- Check for errors: `tail -n 50 /home/paknavy/public_html/storage/logs/laravel.log`
- If attachments/images don't render, the `public/storage` symlink is missing: `/opt/cpanel/ea-php82/root/usr/bin/php artisan storage:link`

### Step 8b: Security checks — run these EVERY deploy
The app-root guard (Step 3b) is silently lost on every clone/extract, so verify it each time rather than assuming. From your laptop's browser or `curl`, against the **raw server IP** (this is the vhost that exposes the app root):

| Test | Expected |
|---|---|
| `https://<SERVER_IP>/` | **Not** an `Index of /` listing (403/404 is fine) |
| `https://<SERVER_IP>/.env` | 403 or 404 — **never** the file contents |
| `https://<SERVER_IP>/.git/config` | 403 or 404 — **never** the repo config |
| `https://<SERVER_IP>/composer.json` | 403 or 404 |
| `https://<SERVER_IP>/storage/logs/laravel.log` | 403/404 — never log contents |

Quick version from any machine that can reach the IP:
```bash
for p in / /.env /.git/config /composer.json /storage/logs/laravel.log; do
  printf "%-32s -> " "$p"; curl -k -s -o /dev/null -w "%{http_code}\n" "https://<SERVER_IP>$p"
done
```
If any of these return `200`, the guard file is missing or wrong — re-apply Step 3b before going further.

> **The real fix is architectural.** `.htaccess` is mitigation, not a cure: the underlying problem is that the default/IP vhost serves `/home/paknavy/public_html` (the Laravel app root) at all. A hardened setup keeps the app root entirely outside any document root — e.g. app at `/home/paknavy/cms` with the domain's docroot pointed at `/home/paknavy/cms/public` — so there is no app root to expose and no `.htaccess` to lose. Raise this with Farman before the app goes to production; until then, Step 3b + Step 8b are the stopgap.

### Step 9a: If everything works
```bash
echo "v1.2.1" > /home/paknavy/current-live-version.txt
```
Keep `public_html_old` and the backup for a few days before deleting, in case a problem shows up later that wasn't obvious right away.

### Step 9b: If something breaks — rollback

**The one thing to remember: git swaps the CODE, not the DATABASE.**

Before you roll back, answer one question: **did the version you're leaving behind add a database change (a migration)?** (You already asked Amjid this before deploying.)
- **No database change** → git rollback alone fixes everything. Use it.
- **Yes, database change** → git fixes the code, but the database won't match. You also need the DB backup (see "Database rollback" below).

#### Going to a specific version (e.g. v1.2.0 → v1.1.1) — your main tool
```bash
cd /home/paknavy/public_html
git fetch --tags
git checkout v1.1.1        # the version you want

# always run this block after checkout (rebuilds the caches):
/opt/cpanel/ea-php82/root/usr/bin/php artisan config:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan route:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan view:clear
/opt/cpanel/ea-php82/root/usr/bin/php artisan config:cache
/opt/cpanel/ea-php82/root/usr/bin/php artisan route:cache
/opt/cpanel/ea-php82/root/usr/bin/php artisan view:cache
```
This never touches your `.env` or your uploaded files. Works for any version, in either direction.

#### Just deployed and it's broken — undo it NOW (only right after a deploy)
```bash
cd /home/paknavy
mv public_html public_html_bad
mv public_html_old public_html
```
Back to the previous version in seconds, no cache rebuild needed. Only works while `public_html_old` still exists (i.e. the same deploy session).

#### Database rollback — only if a migration was involved and the DB is wrong
- Restore the database from the `.sql` export you made in Step 1 (phpMyAdmin → Import).
- Then use `git checkout` above to put the matching code back.

---

## Quick reference — rollback in one glance

| What you want | What to do |
|---|---|
| Move to a specific version (any version, any direction) | `git checkout <tag>` + the cache block |
| Undo the deploy you just did, fast | `mv` swap back (only right after deploying) |
| A database change went wrong | Restore the DB `.sql` backup, then `git checkout` the matching version |

**Always after a `git checkout`: run the cache block.** Skipping it causes a 500.
**Always before deploying: ask Amjid if there's a database change, and export the DB.** That's what makes database rollback possible.
