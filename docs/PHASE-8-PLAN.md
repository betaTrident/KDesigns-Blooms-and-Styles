# Phase 8 — Hardening (execution brief)

Orchestrator scope for P8. Do **not** implement PayMongo/GCash APIs (no merchant keys). Do **not** rewrite git history. Do **not** git commit unless the user later asks.

## Non-goals

- Real payment gateways or client “mark paid”
- `git filter-repo` / BFG / history rewrite
- Enabling HSTS on local `http://localhost:8080/`
- Guest checkout, Laravel, SPA rewrite

## Goals

1. Cleanup: untrack `node_modules` / `package-lock.json` / `.vscode`; delete duplicate repo-root `images/` and unused product photos.
2. Security: `APP_ENV`, PHP `error_log` via `KD_ROOT` (remove hardcoded `K:/` in `public/.htaccess`), HTTPS detection without blindly trusting `X-Forwarded-Proto`, CSRF-only logout, admin role re-checked from DB, no PHP execution under image dirs.
3. Payments: keep method whitelist; persist `receipt_ref`; add admin-only `payment_received_at`; never set settlement from the storefront.
4. Inventory image upload into `storage/uploads` with MIME/size/name allowlist; public stream via `public/media.php`.
5. Notifications: `Mailer` with `MAIL_DRIVER=log` (file) by default.
6. PHPUnit 10 for Catalog/Orders/http helpers (PHP 8.1).
7. `database/migrations/001_p8.sql` applied from `scripts/setup-database.php`.

## File map (new)

- `src/Mailer.php`, `src/Uploads.php`
- `public/media.php`
- `database/migrations/001_p8.sql`
- `composer.json` (PHPUnit 10.5, autoload classmap `src/`)
- `tests/` + `phpunit.xml`
- `public/images/.htaccess` (deny PHP, same as assets)
- `vendor/` gitignored
