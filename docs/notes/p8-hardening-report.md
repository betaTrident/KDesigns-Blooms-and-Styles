# Phase 8 — Hardening report

Date: 2026-09-08  
Status: **COMPLETE** (no git commit, no push)

## What shipped

Security kernel, admin role re-check, CSRF-only logout, receipt/payment recording (admin-only), log mailer, inventory image uploads, PHPUnit 10, and an idempotent `001_p8` migration.

## Non-goals honored

- No PayMongo / GCash / card APIs
- Storefront never marks an order paid
- HSTS is **not** enabled on localhost HTTP
- No git commit / push / history rewrite

## Security kernel

| Item | Result |
|---|---|
| `.env.example` | Added `APP_ENV`, `APP_TRUST_PROXY`, `MAIL_DRIVER`, `MAIL_FROM`. Existing `DB_*` / `SEED_*` kept. No secrets. |
| Local `.env` | Same non-secret keys added (`APP_ENV=local`). File remains gitignored. |
| `kd_env` / `kd_is_https` / `kd_is_production` / `kd_send_security_headers` | In `config/http.php`. `X-Forwarded-Proto` honored only when `APP_TRUST_PROXY=1`. |
| HSTS | Sent from PHP only when `APP_ENV=production` **and** HTTPS. Apache HSTS stays commented. |
| `display_errors` | Always off. `log_errors` + `error_log` under `KD_ROOT/storage/logs/php_errors.log`. `storage/logs` created if missing. |
| `public/.htaccess` | Hardcoded `K:/.../php_errors.log` **removed**. `display_errors Off` and `allow_url_fopen Off` kept. |
| `public/images/.htaccess` | Denies `.php` / `.phtml` / `.phar` (same as assets). |
| Session cookies | `cookie_secure` uses `kd_is_https()` when defined. |
| Admin logout | `public/admin.php?logout` GET bypass **deleted**. Admins log out via `logout.php`. |
| `Auth::requireAdmin()` | Reloads user from DB, requires `role=admin`, refreshes `$_SESSION['user_role']`. Missing/non-admin → redirect (no session destroy). |
| `Auth::findById` | Maps without `password_hash`. |

## Schema + orders

- `database/migrations/001_p8.sql` adds `orders.receipt_ref` and `orders.payment_received_at`.
- `scripts/setup-database.php` applies `database/migrations/*.sql` in name order after schema + seeds (skips `CREATE DATABASE` / `USE`).
- Idempotent: MariaDB `ADD COLUMN IF NOT EXISTS` succeeded here; duplicate column (1060) is ignored; `information_schema` fallback exists if `IF NOT EXISTS` is rejected.
- `Orders::create` always inserts `status=pending`, `payment_received_at=NULL`. Optional `receipt_ref` (max 32, alphanumeric).
- `Checkout::placeFromSession` passes session `receipt_ref` when present.
- `Orders::markPaymentReceived` sets `payment_received_at=NOW()` only if currently NULL. Called only from admin POST `confirm_payment` + CSRF. **Not** called from orderform.
- No new paid status enum.

## Mailer

- Default `MAIL_DRIVER=log` → `storage/logs/mail.log` (`timestamp`, `to`, `subject`, body).
- `MAIL_DRIVER=php` uses `mail()`. Unknown driver logs.
- Recipient is `Auth::findById(order.user_id)` email. Missing email → log and return.
- `orderPlaced` after create **commit**; `statusChanged` after status **commit**. Failures are caught and never roll back the order.
- Password hashes are never logged.

## Uploads

- Max 2_000_000 bytes. MIME via `finfo(FILEINFO_MIME_TYPE)` only (jpeg / png / webp).
- PHP / SVG / HTML rejected. Stored as `storage/uploads/{32hex}.{ext}`; API returns basename only.
- `public/media.php` streams allowlisted `f=` names; 404 otherwise. No directory listing.
- `kd_image_url('uploads/...')` → `/media.php?f=` + basename.
- `Catalog::updateImage` allows only `images/...` or `uploads/...`, rejects `..`.
- Admin restock form is `multipart` and accepts optional `product_image`. Qty still saves when no file is chosen.

## Orderform blur catalog

Hardcoded four cards replaced with a loop over `$blurCatalog` (max 4 from `Catalog::allActive()` in `public/orderform.php`), using `kd_image_url` and `Catalog::formatPrice`.

## Tests

Composer was **not** on PATH. Official installer wrote gitignored `composer.phar`. Then:

```
C:\xampp\php\php.exe composer.phar install
C:\xampp\php\php.exe vendor/bin/phpunit
C:\xampp\php\php.exe scripts\setup-database.php
```

PHPUnit 10.5.64 / PHP 8.1.25:

```
OK (10 tests, 36 assertions)
```

- `kd_image_url('..')` → `/images/logo.png`
- `kd_image_url('images/foo.JPG')` → `/images/foo.JPG`
- `Catalog::stockDisplay` 0 / 2 / 10
- `Orders::PAYMENTS` / `STATUSES` + `normalizeStatus`
- `Uploads::extensionForMime` allow + reject empty/php/svg/html
- Integration: `Orders::create` decrements stock; stock=0 throws and does not insert an order. Dedicated product/user created and deleted.

Second `setup-database.php` run: `OK 001_p8.sql (2 statements)` — idempotent.

## Live HTTP smoke (localhost:8080)

| URL | Result |
|---|---|
| `/` | 200 |
| `/orderform.php?id=1` (anon) | 302 (login required) |
| `/media.php?f=not-valid` | 404 |
| `/admin.php?logout=1` (anon) | 302 to login — no GET session destroy |

Full logged-in admin “Record payment” / restock-with-file clicks were not driven in a browser session.

## Concerns / follow-ups

1. **Composer is local-only.** `composer.phar` is gitignored. Teammates need Composer on PATH or the same phar.
2. **HSTS stays off** until `APP_ENV=production` on real HTTPS. Do not flip that on `:8080`.
3. **Mail is file-only** unless `MAIL_DRIVER=php`. No SMTP.
4. **Payment recorded** is an admin timestamp, not a gateway settlement and not a customer-settable status.
5. **`storage/uploads`** is gitignored; production deploys must create the directory (runtime `mkdir` also exists).
6. **Cleanup from PHASE-8-PLAN** (untrack `node_modules` / delete unused photos) was **not** in the numbered execution list and was not done.

## Files touched (principal)

New: `src/Mailer.php`, `src/Uploads.php`, `public/media.php`, `public/images/.htaccess`, `database/migrations/001_p8.sql`, `composer.json`, `phpunit.xml`, `tests/*`

Updated: `.env.example`, `config/http.php`, `config/bootstrap.php`, `config/session.php`, `public/.htaccess`, `src/Auth.php`, `src/Orders.php`, `src/Checkout.php`, `src/Catalog.php`, `public/admin.php`, `public/orderform.php`, `views/admin/dashboard.php`, `views/storefront/orderform.php`, `scripts/setup-database.php`, `.gitignore`
