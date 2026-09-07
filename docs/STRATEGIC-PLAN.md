# KDesigns Blooms & Styles — Strategic Plan

| | |
|---|---|
| **Status** | Draft for execution |
| **Date** | 8 September 2026 |
| **Stack to keep** | PHP 8.1 (XAMPP Apache) · MariaDB 10.4 (bundled, in use) · server-rendered HTML |
| **Runtime today** | Apache on `http://localhost:8080/` · project at `K:\KDesigns-Blooms-and-Styles` · XAMPP at `C:\xampp` |
| **North star** | One catalog, one login, one order ledger — storefront and admin share the same database |

This document is the project roadmap: what is broken, what to build, in what order, and what to refuse. It is not an invitation to rewrite the UI in another framework.

---

## 1. Purpose

The storefront, checkout chrome, and admin layout are already presentable. The product does not yet behave like a shop: data lives in PHP sessions, catalogs disagree, and admin is a disconnected demo.

**The strategic job is to solidify the backend in place** — persist users, products, and orders in MariaDB — while keeping the current screens.

When this plan is done:

- A customer can create an account, log in later, and see their orders after closing the browser.
- Placing an order updates stock and appears on the admin dashboard.
- Prices and stock come from one product table, not three copied lists.
- Admin login uses the same auth as the storefront (`users.role = admin`).
- Apache DocumentRoot can later shrink to a `public/` folder without changing how the business works.

---

## 2. Current-state snapshot

### 2.1 What already works

- XAMPP Apache serves the site at port 8080 (vhost → this repo).
- Storefront visual design (`index.php` + `style.css`).
- Checkout flow UI (form → payment → e-receipt / success).
- Checkout **price whitelist** in `orderform.php` (client cannot set the peso amount).
- CSRF, rate limiting, Argon2id, and hardened sessions on *some* pages (`config/security.php`, `config/session.php`, `config/env.php`).
- MariaDB **is already installed** (`C:\xampp\mysql`, version 10.4.32). No extra database installer.

### 2.2 What is not a backend yet

| Area | Today | Why it fails as a shop |
|---|---|---|
| Data store | `$_SESSION` | Lost on browser close / 30-minute idle timeout |
| Users | Two `.env` hashes + signup that never saves | Cannot return as the same customer |
| Catalog | HTML in `index.php` + `PRODUCT_CATALOG` in `orderform.php` + mock inventory in `admin.php` | Three sources of truth (12 / 11 / 8 products) |
| Orders | `$_SESSION['user_orders']` vs `$_SESSION['admin_orders']` | Checkout never reaches admin |
| Auth | `login.php` (`.env`) vs `admin.php` (plaintext in source) vs dead `admin_login.php` | Admin from `login.php` does not set `admin_logged_in` |
| Stock | Hardcoded “12 in stock” copy | Restock in admin does not change the catalog |
| Reporting | Literal KPIs in admin HTML (e.g. buyer revenue) | Not calculated |

### 2.3 Runtime map (do not add a fourth process)

| Process | Role | Action |
|---|---|---|
| Apache | Serves PHP + assets | Already required |
| PHP | Page logic | Already required |
| MariaDB (XAMPP label: **MySQL**) | Durable data | **Start when Phase 1 begins** — not a new install |
| Node | Tailwind CLI only | Not a server; optional later for CSS build |

---

## 3. Guiding principles

1. **Keep PHP server-rendered pages.** Do not split into React + REST until the domain is real. The UI is the asset; the gap is persistence.
2. **One write path per domain concept.** Products, users, and orders each have a single module and a single table (or small table group).
3. **Session holds identity only** (logged-in user id, CSRF, flash messages). Catalog, orders, and stock never live in the session.
4. **Database before folders.** Moving files without a model just scatters the same session demos.
5. **Reuse existing screens.** Replace data sources behind `index.php`, `orderform.php`, `orders.php`, and `admin.php`; do not redesign first.
6. **Security kernel is incomplete, not absent.** Extend `config/*` to every page; do not invent a second auth library.
7. **XAMPP is the local platform.** Use bundled MariaDB and phpMyAdmin. Do not install a second MySQL/MariaDB.

---

## 4. Priority ranking

Work **top to bottom**. Later phases assume earlier ones are done. Do not skip to “clean file structure” or “Tailwind build” before orders persist.

| Rank | Theme | Why this rank |
|---|---|---|
| **P0** | Runtime hygiene | Site must stay bootable (Apache, `.env`, `storage/`). Largely done. |
| **P1** | MariaDB + PDO + schema | Nothing else survives a refresh without this. Highest leverage. |
| **P2** | Single product catalog | Homepage, checkout, and inventory are already lying to each other. |
| **P3** | Unified authentication | Real orders need real users; three logins are a product bug and a security bug. |
| **P4** | Persistent checkout | This is the shop. Order insert + stock decrement in one transaction. |
| **P5** | Admin on live data | Dashboard becomes operational instead of a mock. |
| **P6** | Structure & maintainability | `src/`, `views/`, later `public/` webroot. After behavior is correct. |
| **P7** | Frontend consolidation | Shared layout, one CSS pipeline. Quality of life, not capability. |
| **P8** | Hardening & go-live | Secrets out of source, HTTPS, payments as integrations — last. |

**If time is short:** complete **P1 → P4**. That is the minimum viable backend. P5 is the minimum viable admin. P6–P8 can wait.

---

## 5. Needed solutions (problem → approach)

| # | Problem | Solution (keep it boring) |
|---|---|---|
| 1 | Session is used as a database | MariaDB tables; PHP PDO in `config/db.php` |
| 2 | Three product lists | `products` table; all pages query it |
| 3 | Signup discards the password hash | `INSERT` into `users`; login uses `password_verify` against that row |
| 4 | `.env` demo accounts + admin hardcoded password | Seed one admin user in SQL; delete plaintext credentials from `admin.php` |
| 5 | `login.php` admin redirect does not open the dashboard | Gate `admin.php` on `$_SESSION['user_role'] === 'admin'` (same session as storefront) |
| 6 | `admin_login.php` points at missing `admin_dashboard.php` | Remove the dead page; one admin entry (`admin.php` or login → admin) |
| 7 | Customer order ≠ admin order | `orders` + `order_items`; both UIs read those tables |
| 8 | Stock text is fake | `products.stock`; decrement on successful order; sold-out when `stock = 0` |
| 9 | Price in the query string is ignored in checkout but still advertised | Links use product id (or slug) only; price always from DB |
| 10 | CSRF/session hardening skipped on `index.php` / `admin.php` | Every page calls `secure_session_start()` |
| 11 | Tailwind config duplicated; `style.css` vs CDN | After P5: layout partial + one compiled CSS (optional) |
| 12 | Webroot exposes `config/`, `.env` | `.htaccess` already blocks some paths; later move DocumentRoot to `public/` |
| 13 | No payments processor | Keep method whitelist (`Pay at Shop`, `GCash`, `BDO`, `BPI`) as **recorded choice**, not a gateway, until P8 |
| 14 | `node_modules` in git history | Stop tracking it; `.gitignore` already lists it |

---

## 6. Phased roadmap

Each phase has **outcome**, **work**, and **exit criteria**. Do not start the next phase until exit criteria pass.

### Phase 0 — Keep the local site runnable (P0)

**Outcome:** Any developer can open the storefront after a reboot.

**Work:**

- Start Apache from XAMPP Control Panel (`C:\xampp\xampp-control.exe`).
- Confirm `http://localhost:8080/` loads.
- Keep `.env` gitignored; keep `storage/logs`, `storage/rate_limits`, `storage/uploads`.
- Re-run `scripts/setup-xampp.ps1` only if the vhost is lost.

**Exit criteria:** Homepage, login, and admin URL respond without PHP fatals.

**Status:** Complete as of 8 Sep 2026 — Apache `:8080`, `.env`, `storage/` dirs, vhost. Homepage/login/admin return 200; `config/` returns 403.

---

### Phase 1 — Persistence foundation (P1) — **do this first**

**Outcome:** PHP can read/write MariaDB. Schema exists. No user-facing behavior change required yet.

**Work:**

1. In XAMPP, **Start MySQL** (this is MariaDB). Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
2. Create database e.g. `kdesigns` with `utf8mb4`.
3. Add `config/db.php` (PDO, credentials from `.env`: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`). Default XAMPP user is `root` with empty password — local only.
4. Add `database/schema.sql` with at least:

   - `users` — id, name, email (unique), password_hash, role (`customer` \| `admin`), timestamps
   - `products` — id, name, slug, category, description, price (integer centavos **or** integer PHP pesos matching current ints), image_path, stock, badge, is_active, timestamps
   - `orders` — id, public_code (e.g. `KDB-…`), user_id, fulfillment (`pickup` \| `delivery`), payment_method, status, date_needed, time_needed, delivery fields, totals, timestamps
   - `order_items` — id, order_id, product_id, product_name_snapshot, unit_price_snapshot, qty

5. Seed products from the current catalog (see appendix). Seed one admin user with an Argon2id hash (not plaintext).
6. Add a tiny `public` health check or CLI script: `SELECT 1` via PDO.

**Exit criteria:** phpMyAdmin shows four tables with seed products; a PHP script connected through `.env` can `SELECT` them. The live site can still be session-based until Phase 2.

**Status:** Complete as of 8 Sep 2026. Database `kdesigns` applied via `scripts/setup-database.php`. Health: `C:\xampp\php\php.exe scripts/db-health.php`. Login still uses `.env` hashes (Phase 3).

---

### Phase 2 — Single catalog (P2)

**Outcome:** Homepage, order form, and admin inventory all use `products`.

**Work:**

- Add `src/Catalog.php` (or `includes/catalog.php`) — `all()`, `findById()`, `findBySlug()`, `inStock()`.
- Render `index.php` catalog as a loop (filters still client-side on `data-category`).
- Change order links from `orderform.php?product=Name&price=1850` to `orderform.php?id=…` (or slug). **Never trust `price` from the query string** (already the rule; make the URL match the rule).
- Replace `PRODUCT_CATALOG` const in `orderform.php`.
- Replace `$_SESSION['admin_inventory']` bootstrap in `admin.php` inventory tab with DB rows; restock `UPDATE`s `products.stock`.
- Include **Autumn Harvest Wreath** (sold out on the homepage, missing from checkout whitelist) as `stock = 0` so it is not a special case in HTML.

**Exit criteria:** Adding or changing a product in MariaDB changes the homepage *and* checkout *and* admin inventory without editing three PHP files. Out-of-stock products cannot be ordered.

**Status:** Complete as of 8 Sep 2026. `src/Catalog.php` drives homepage, `orderform.php?id=`, and admin inventory/restock.

---

### Phase 3 — Unified authentication (P3)

**Outcome:** One user table, one login, one session contract.

**Work:**

- `signup.php`: after validation, `INSERT` user (`role = customer`). Keep Argon2id. Unique email errors become a friendly message.
- `login.php`: authenticate against `users`, not `.env` `ADMIN_HASH` / `CUSTOMER_HASH`. Set `$_SESSION['user_id']`, `user_email`, `user_name`, `user_role`. `session_regenerate_id(true)` stays.
- `admin.php`: require `user_role === 'admin'`; remove in-file plaintext login and prefilled credentials.
- Redirect admins from `login.php` to `admin.php` **and** make that sufficient to enter the dashboard.
- Delete or redirect `admin_login.php` (broken: `admin_dashboard.php` does not exist).
- Retire `.env` demo identity keys once seed admin exists (`ADMIN_EMAIL`, `CUSTOMER_*`). Keep `DB_*` and later `APP_KEY` if needed.
- Apply `secure_session_start()` + idle timeout on `index.php` and `admin.php` (today they call raw `session_start()`).

**Exit criteria:** New signup survives logout and a new browser session. Admin cannot log in with a password that appears in PHP source. Customer cannot open `admin.php`.

**Status:** Complete as of 8 Sep 2026. `src/Auth.php` authenticates against `users`. `admin_login.php` redirects to `login.php`.

---

### Phase 4 — Persistent checkout (P4) — **minimum viable shop**

**Outcome:** Placing an order is an `INSERT`, not a session push.

**Work:**

- `src/Orders.php` — create order + items; load by user; load all for admin.
- On successful payment step (including “Pay at Shop”): begin transaction → insert `orders` + `order_items` → decrement `products.stock` if stock allows → commit. Fail cleanly if stock raced to zero.
- Snapshot product name and unit price on the line item (history must not change when the catalog price changes later).
- Store fulfillment and delivery fields **on the order row**, not only in session scratch keys.
- `orders.php` (customer tracker) reads DB for `user_id`.
- Keep CSRF on all POSTs. Keep payment method whitelist as a stored enum, not a charge API.

**Exit criteria:** Place order as customer A → log out → log in → order still listed. In phpMyAdmin, the row exists. Stock on the homepage dropped by one.

**Status:** Complete as of 8 Sep 2026. `src/Orders.php` creates orders in a transaction (`SELECT … FOR UPDATE`, snapshots, stock decrement). Checkout (`Pay at Shop` and e-receipt confirm) writes MariaDB. `orders.php` reads `Orders::forUser`.

---

### Phase 5 — Admin is operational (P5)

**Outcome:** Overview, orders, inventory, buyers are queries, not fixtures.

**Work:**

- Orders tab: list DB orders; status updates `UPDATE orders SET status = …` (whitelist: Pending, Processing, Delivered, Cancelled — match UI labels).
- Inventory tab: already DB from P2; show low-stock counts from `WHERE stock <= 5`.
- Buyers tab: `GROUP BY user` (or guest email if you allow guest later — **do not add guests in this phase**).
- Overview KPIs: `COUNT` / `SUM` from `orders` (paid/placed), not hardcoded `₱35,860`.
- Sidebar badges: real pending-order and low-stock counts.
- Optional: cancelled order restocks in a transaction (define the rule once and test it).

**Exit criteria:** A real checkout is visible in admin without touching session. Changing status in admin is what the customer would see if you later show status on `orders.php` (wire that display in this phase if the tracker already has a status field).

**Status:** Complete as of 8 Sep 2026. Admin overview/orders/buyers query MariaDB. Status updates use the internal order id; cancelling restocks, reopening decrements again if stock allows. Customer tracker shows the admin-updated status.

---

### Phase 6 — Clean structure (P6)

**Outcome:** Pages are thin; domain code is reusable; secrets stay off the web.

**Work (after P5, not before):**

Suggested layout (as implemented):

```text
public/                 ← Apache DocumentRoot on :8080
  index.php, login.php, signup.php, logout.php
  orderform.php, orders.php, admin.php
  assets/css/style.css, assets/css/app.css
  assets/js/catalog.js, assets/js/orderform.js
  images/               ← product/logo files; DB still stores images/…
src/
  Auth.php, Catalog.php, Orders.php, Checkout.php, View.php
views/
  partials/head.php, storefront-nav.php, storefront-footer.php,
           catalog-card.php, admin-sidebar.php
  storefront/…
  admin/dashboard.php
config/
  bootstrap.php, env.php, db.php, session.php, security.php, http.php
database/
  schema.sql
storage/
scripts/setup-xampp.ps1
```

- Point the `:8080` vhost `DocumentRoot` at `public/` and update image/CSS paths.
- One `require` bootstrap (`config/bootstrap.php`) for env + db; web pages then call `kd_boot_http()` for session + CSRF.

**Exit criteria:** `http://localhost:8080/` still works; `http://localhost:8080/config/env.php` is not executable as an app page (outside webroot).

**Status:** Complete as of 8 Sep 2026. DocumentRoot is `public/`. Controllers are thin; HTML lives in `views/`. `config/`, `src/`, and `views/` return 404 from the site. Run `scripts/setup-xampp.ps1` and restart Apache if the vhost is lost.

---

### Phase 7 — Frontend maintainability (P7)

**Outcome:** Same look, less duplication.

**Work:**

- Shared header/footer/nav from `views/partials`.
- Single Tailwind theme (compiled CSS **or** one shared include) — remove per-page `tailwind.config` blobs.
- Catalog card component/partial.
- Fix logo `href="/"` so it always returns to the storefront (already correct when DocumentRoot is the app).

**Exit criteria:** Changing the nav once updates storefront and auth pages. Visual regression check of home, login, orderform, admin.

**Status:** Complete as of 8 Sep 2026. Shared `views/partials` for head, storefront nav/footer, catalog card, and admin sidebar. Tailwind theme is compiled to `public/assets/css/app.css` (`npm run build:css`); homepage still uses `style.css`. Nav links use `/#home` `/#about` `/#catalog` and the logo is `href="/"`.

---

### Phase 8 — Hardening and later product (P8)

**Do after the shop works.** Do not block P1–P5 on these.

- Remove remaining secrets from git history if they were committed; rotate local admin password.
- Production: HTTPS, HSTS in `.htaccess` (comment is already there), `display_errors` off (already in `.htaccess`).
- `allow_url_fopen` off may break some PHP HTTP clients later — revisit if you add a payment API.
- Real GCash/card: official provider APIs, never “fake paid” on the client.
- Image uploads for inventory (use `storage/uploads`, not git).
- Email notifications (order placed / status change).
- Automated tests (PHPUnit) for Catalog stock rules and order transactions.
- Stop committing `node_modules` if it is still in the repo.

---

## 7. Target domain model (logical)

```text
users 1───* orders 1───* order_items *───1 products
```

**Session after P3:** `user_id`, `user_role`, `csrf_token`, `last_activity`, plus short-lived checkout wizard keys until the order is committed.

**Money:** keep integer pesos as the app already does (`1850`), or switch to centavos in the schema once and format in the view. Pick one in Phase 1 and do not mix.

**IDs:** integer PK internally; customer-facing `KDB-######` on `orders.public_code` (unique).

---

## 8. What we will not do (this cycle)

| Idea | Why not now |
|---|---|
| React / Vue / Next SPA | Rewrites the good UI; does not create persistence |
| Laravel / Symfony full migrate | Valid later; too much move for the same tables |
| Separate Node API | Second runtime for no gain on XAMPP hosting |
| Second MariaDB/MySQL install | Conflicts with XAMPP on port 3306 |
| Payment gateway | Record method only until the ledger is real |
| Guest checkout | Complicates `user_id`; add after logged-in path works |
| Folder refactor before the database | Rearranges a prototype, does not fix it |

---

## 9. How to run the project during this plan

Always:

1. XAMPP Control Panel → **Apache** → Start  
2. Open `http://localhost:8080/`

From **Phase 1 onward**, also:

3. XAMPP Control Panel → **MySQL** → Start  
4. Optional: `http://localhost/phpmyadmin` (this is the XAMPP dashboard on port 80, not port 8080)

Node/`npm` is not required to run the site.

---

## 10. Risks

| Risk | Impact | Mitigation |
|---|---|---|
| Skipping P1 and “just cleaning files” | No durable shop | Follow the rank table |
| Two Apache copies / closing the Apache console | Site down | Use XAMPP Control Panel Start/Stop only |
| Installing another MySQL | Port 3306 fight | Use only `C:\xampp\mysql` |
| Trusting GET `price` again | Fraudulent totals | Price only from `products` |
| Admin status without stock rules | Negative stock / double sell | Transaction + `stock >= qty` check |
| Migrating to `public/` with broken asset paths | Blank CSS/images | One dedicated Phase 6 pass + browser check |
| Keeping plaintext admin login “for convenience” | Credentials in git | Phase 3 deletes it |

---

## 11. Definition of done (project)

The backend is **solid enough** when all of the following are true:

1. MySQL (MariaDB) is the source of truth for users, products, and orders.
2. Storefront catalog, checkout, and admin inventory are the same rows.
3. There is one login implementation; admin is a role, not a second app.
4. A placed order is visible to that customer after a new login and to admin without sharing a browser session.
5. Stock cannot go negative; sold-out items cannot be purchased.
6. No passwords in PHP source; `.env` holds DB credentials only.
7. `index.php` and `admin.php` use the same session/CSRF bootstrap as the other pages.

Structure (P6) and CSS unification (P7) are **done** when the above still holds after the move — they are not a substitute for it.

---

## Appendix A — Catalog to seed (from current UI)

Align seed data to these names/prices/images so the site does not visually regress. Stock should match the homepage copy where possible. Include the sold-out wreath.

| Name | Category (UI) | Price (₱) | Image (current) | Notes |
|---|---|---|---|---|
| Crimson Romance Bouquet | fresh | 1850 | `images/IMG_8603.JPG` | Bestseller |
| Pastel Peony Bouquet | fresh | 2200 | `images/IMG_8597.JPG` | Limited |
| Sunflower & Wildflower Mix | fresh | 1200 | `images/IMG_8630.JPG` | |
| Orchid Elegance Vase | fresh | 3500 | `images/IMG_8618.JPG` | Luxury; low stock |
| Garden Table Centerpiece | dried | 2800 | `images/IMG_8586.JPG` | |
| Lavender Dreams Bundle | dried | 980 | `images/IMG_8587.JPG` | |
| Tropical Bloom Arrangement | dried | 3200 | `images/IMG_8564.JPG` | New |
| Bridal White Cascade | dried | 4800 | `images/IMG_8607.JPG` | Luxury |
| Autumn Harvest Wreath | dried | 1600 | `images/IMG_8643.JPG` | Sold out on homepage; missing from `PRODUCT_CATALOG` today |
| Bloom Arrangement No. 10 | bloombox | 1400 | `images/IMG_8635.JPG` | |
| Bloom Arrangement No. 11 | others | 1400 | `images/IMG_8606.JPG` | |
| Bloom Arrangement No. 12 | glassdome | 1400 | `images/IMG_8582.JPG` | |

Admin mock inventory currently has **eight** of these; checkout whitelist has **eleven** (no wreath). Seed **all twelve**.

---

## Appendix B — Execution sequence (checklist)

Use this as the live tracker. Finish a phase before starting the next.

- [x] **P0** Apache + `.env` + `storage/` (done if `http://localhost:8080/` works)
- [x] **P1** Start MySQL · database · `config/db.php` · `database/schema.sql` · seed products + admin
- [x] **P2** Catalog module · loop on homepage · order by id/slug · admin inventory = DB
- [x] **P3** Signup insert · login from `users` · admin role gate · remove extra logins
- [x] **P4** Order transaction · customer tracker from DB · stock decrement
- [x] **P5** Admin tabs from queries · real KPIs
- [x] **P6** `src/` + `views/` · optional `public/` webroot
- [x] **P7** Partials + one CSS pipeline
- [ ] **P8** Secrets, HTTPS, payments, tests, uploads

**Next concrete action:** Phase 8 — hardening and later product (HTTPS, real payment APIs, tests). Do not treat compiled CSS or folder moves as a reason to skip P8 security work.
