# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this repository is

This directory is a **deployment and documentation workspace** for the Navy Complaint Management CMS, a Laravel application built for a Pakistan Navy complaint-tracking deployment. It is not the canonical source repo — the app's real git history lives at `https://github.com/nexertech/CMS.git`. This folder instead holds deployment guides, prior AI-assisted troubleshooting chat exports, and several packaged/zipped copies of the app at different points in time.

The only extracted (non-zipped) copy of the app's source is at:
`Deployment through CPanel - Currently Deployed/Initial deployment guide and assests/navy-app-ready-to-deploy/`

All other app copies (`CMS.zip`, `v2CMS.zip`, `complaint app.zip`, `navy-app-ready-to-deploy.zip`, and the `complaint-app` / `v1-complaint-app` / `v3-complaint app` folders under `Deployement through Bash Script - Optional/`) are zip archives representing alternate or historical builds — don't assume they match the extracted copy without checking.

## Commands

Run these from inside `navy-app-ready-to-deploy/` (the only extracted app copy):

```bash
composer install --no-dev --optimize-autoloader   # production-clean install, matches the live deploy process
npm install
npm run build       # vite build — compiles Tailwind CSS + JS
npm run dev          # vite dev server
composer run dev     # runs artisan serve + queue:listen + pail + npm dev concurrently
```

**Testing/linting cannot be run against this copy.** `phpunit.xml` and `pestphp/pest` / `laravel/pint` are declared in `composer.json`'s `require-dev`, but this is a production build: there is no `tests/` directory and dev dependencies were never vendored here. Don't attempt `php artisan test` or `./vendor/bin/pint` here — they'll fail. Test/lint work belongs against an actual checkout of the GitHub repo, not this deployment snapshot.

**Tailwind build gotcha:** `tailwind.config.js`'s `content` scan includes `resources/views/**/*.blade.php`. Blade-only changes (new utility classes) can change the compiled CSS output even when nobody touches `resources/css` — treat "Blade files changed" as its own trigger for `npm run build`, not just CSS/JS changes.

## Deployment process

The canonical, currently-live deployment process is documented in [`Deployment through CPanel - Currently Deployed/CMS_Deployment_Runbook.md`](Deployment%20through%20CPanel%20-%20Currently%20Deployed/CMS_Deployment_Runbook.md) — read it before giving deployment advice. Summary:
- The app is deployed manually to a JazzCloud cPanel VPS (`paknavy` user, `/home/paknavy/public_html`): zip built locally, uploaded via File Manager, extracted, then `artisan config/route/view:cache` run over SSH. This is **not** the git+`.cpanel.yml` auto-deploy flow described in some of the PDF guides in this repo — that method was evaluated but never adopted for the live server (largely because `paknavy` has no composer/npm/sudo access on the box).
- Releases are tagged (`vX.Y.Z`) on a dedicated `deploy` branch — the only branch where built `vendor/` and `public/build/` are force-committed.
- Rollback has 3 layers, cheapest first: instant folder swap (`mv public_html ↔ public_html_old`), `git checkout <previous-tag>`, then full backup+DB restore.
- `Deployement through Bash Script - Optional/` documents an alternate AWS/Terraform-based deployment path that was explored but is **not** the live deployment — don't treat its `deploy.sh` / `terraform/` contents as current production process.

**Cache-path ordering gotcha (caused a live 500):** `php artisan config:cache`/`route:cache`/`view:cache` bake the current folder's **absolute path** into `bootstrap/cache/config.php`, `bootstrap/cache/routes-v7.php`, and the compiled Blade views in `storage/framework/views/`. The deploy flow builds in a staging folder (`public_html_new`) and then atomically renames it to `public_html`, so these cache commands MUST run **after** the rename — never in the staging folder — or every baked path points at the now-gone `public_html_new` and the app 500s on boot. Signature of this failure: a 500 with **no new lines in `storage/logs/laravel.log`** (the cached log path is also dead). Fix = clear + rebuild caches in the final `public_html`. The runbook's Step 7 now enforces this ordering.

## Architecture (navy-app-ready-to-deploy)

Laravel 12, PHP ^8.2, classic server-rendered Blade + Tailwind CSS + Alpine.js + Vite (no Livewire/Inertia/Vue/React).

**Three auth guards** (`config/auth.php`), each with its own user model and route file:
- `web` — session guard, `App\Models\User` — the **admin panel**: `routes/web.php`'s `admin.*` group, prefixed `/admin`, gated by `auth`, `verified`, and a custom `AdminAccessMiddleware`, then further per-section `permission:*` middleware (dashboard, users, complaints, employees, houses, reports, sla, settings, etc.).
- `frontend` — session guard, `App\Models\FrontendUser` — the **public complaint portal** (`routes/frontend.php`): home/login/register plus an authenticated dashboard/stock/complaint-detail area gated by `auth:frontend` and a custom `password.renewal` middleware.
- `sanctum` — token guard, `App\Models\House` — the **mobile/device API** (`routes/api.php`): public device-registration/login endpoints plus protected complaint-submission/feedback endpoints behind a custom `manual.auth` middleware (not Sanctum's default guard middleware).

**Authorization is homegrown, not a package.** Despite a full roles/permissions system, there's no spatie/laravel-permission — it's built from `app/Models/Role.php` + `RolePermission.php` + `app/Http/Middleware/RoleMiddleware.php` + `PermissionMiddleware.php`. Same pattern elsewhere: no dompdf/maatwebsite-excel for the report/export features — custom-built instead.

**Notifications** go through a custom Firebase Cloud Messaging channel (`app/Channels/FcmChannel.php`), not a Laravel-native push package — driven by `FIREBASE_PROJECT_ID` / `FIREBASE_CREDENTIALS`, which are set in the live `.env` but absent from `.env.example` (along with `SESSION_SECURE_COOKIE` / `SESSION_HTTP_ONLY` / `SESSION_SAME_SITE`). Don't rely on `.env.example` alone when reasoning about production config.

**Security-hardening middleware** beyond the auth guards: `SqlInjectionMitigation`, `SecureHeaders`, `PreventBackHistory` (`app/Http/Middleware/`).

**Controllers** are organized by area: `Admin/` (one controller per admin section — Complaints, Employees, Houses, Roles, Sla, Reports, etc.), `Api/` (device/complaint/notification endpoints), `Auth/` (stock Breeze scaffolding), `Frontend/` (public portal).

**Console commands** (`app/Console/Commands/`) are one-off/maintenance data-migration scripts (e.g. `BackfillStockLogBrandNames`, `MigrateApprovalStockData`), not scheduled jobs — `routes/console.php` only has Laravel's default `inspire`.

## Sensitive files

`Deployment through CPanel - Currently Deployed/Initial deployment guide and assests/NAVY App deployment walkthrough.docx` previously contained live production credentials (cPanel/DB passwords, server IP) in plaintext; these have since been rotated in production, but treat any credentials found in docs in this repo as stale/illustrative, never something to reuse or forward.
