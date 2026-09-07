# Admin Core Tables — Orders, Inventory, Overview

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the admin **Orders** and **Inventory** tables fully operational (correct payment→status behavior, confirmations, accessible table UX, product CRUD) and give **Overview** a real analytics surface — without a new framework, payment gateway, or schema rewrite.

**Architecture:** Keep PHP 8.1 server-rendered admin (`public/admin.php` + `views/admin/dashboard.php`). Domain rules stay in `src/Orders.php` and `src/Catalog.php`. Payment recording becomes one transactional write: stamp `payment_received_at` **and** advance fulfillment status when that is the correct next state. Inventory “delete” is **deactivate** by default because `order_items.product_id` is `ON DELETE RESTRICT`. Overview metrics are extra SQL in `Orders::stats()` / a small `Orders::analytics()` helper, rendered as cards and CSS bars (no chart.js).

**Tech Stack:** PHP 8.1 · MariaDB 10.4 · existing CSRF/session bootstrap · Tailwind compiled `app.css` · PHPUnit 10 · `<dialog>` for confirmations (native, accessible)

## Global Constraints

- Stack stays PHP server-rendered on XAMPP Apache `:8080`; DocumentRoot remains `public/`.
- Do not add React, Laravel, or a JS chart library.
- Do not add PayMongo/GCash APIs; payment is still an admin-recorded timestamp.
- CSRF (`enforce_csrf`) on every new POST. Escape all output with `e()`.
- Status enum stays `pending | processing | delivered | cancelled` — do **not** add a `paid` status.
- Products with existing `order_items` must not be hard-deleted.
- Storefront catalog continues to show `is_active = 1` only (`Catalog::allActive()`).
- PHPUnit via `C:\xampp\php\php.exe vendor\bin\phpunit`. Do not commit unless the user asks.
- Match existing visual language: cream `#f6f3eb`, burgundy `#4c1719`, Playfair + Inter.

---

## 1. Why this plan exists

The shop already persists orders and products. Admin can list them. What failed in testing is **operations**, not storage:

| What you did | What happened | What should happen |
|---|---|---|
| Click **Record payment** | `payment_received_at` is set; badge says “Payment recorded”; **status unchanged** (still Pending/Processing) | Payment is confirmed in a dialog; then timestamp **and** status move together when the order is still `pending` → `processing` |
| Inventory | Stock + optional image only | Create, edit, deactivate/reactivate; hard-delete only if never ordered |
| Overview | Five KPI cards + recent orders + low stock | Same KPIs **plus** status mix, unpaid queue, period revenue, fulfillment/payment mix, top products |

This is the next operational slice after Phase 8. It is not a redesign of the storefront.

---

## 2. Current code (do not rediscover)

| Area | Today | File |
|---|---|---|
| Record payment | `UPDATE … SET payment_received_at = NOW()` only | `src/Orders.php` `markPaymentReceived()` ~332–348 |
| Admin POST | `confirm_payment` then redirect; no confirm step | `public/admin.php` 38–49 |
| Status change | Separate form; cancel restocks / reopen decrements | `Orders::updateStatus()` |
| Inventory write | `Catalog::updateStock` + `updateImage` | `public/admin.php` 51–70 |
| Product create/update/delete | **Missing** | `src/Catalog.php` |
| Overview | `Orders::stats()`: revenue (excl. cancelled), order_count, pending_count, buyer_count, avg_order_php | `src/Orders.php` 412–445 · `views/admin/dashboard.php` 33–120 |
| Tables | Desktop `<table>` + mobile cards; weak captions/labels; instant payment POST | `views/admin/dashboard.php` |

Schema already has `receipt_ref` and `payment_received_at` (`database/schema.sql` 57–58). No migration is required for payment+status unless we add indexes (optional).

---

## 3. Domain rules (lock these before coding)

### 3.1 Order lifecycle

```text
place order  →  status = pending, payment_received_at = NULL
record payment (admin, confirmed)  →  payment_received_at = NOW()
                 if status was pending  →  status = processing
                 if status was processing or delivered  →  status unchanged
                 if status was cancelled  →  REJECT (do not un-cancel, do not restock dance)
manual status Save  →  existing updateStatus() rules (cancel restock / reopen decrement)
```

**Why pending → processing:** “We have the money; start the arrangement.” That is the missing link you hit in testing. Recording payment is not the same as Delivered.

**Why not a `paid` status:** Payment is orthogonal to fulfillment. Keep two columns: `status` (work) and `payment_received_at` (money). The UI already has a Payment column; it must stay visible next to Status so operators see both.

### 3.2 Record-payment confirmation

- Never rely on `window.confirm` alone (easy to miss; poor SR).
- Use a native `<dialog>` (or a single shared overlay) with:
  - order `public_code`, customer name, amount, payment method, receipt ref
  - **Cancel** (closes dialog, no POST)
  - **Confirm payment** (POST `confirm_payment` + CSRF + `order_id`)
- Keyboard: Esc closes; focus trapped in the dialog while open; return focus to the Record payment button.
- Server still rejects cancelled orders even if someone crafts a POST.

### 3.3 Inventory CRUD

| Action | Behavior |
|---|---|
| **Create** | Insert product: name, category, description, price_php (int pesos), stock, badge (optional), image (required on create: upload or existing `images/…`), `is_active=1`. Slug from name (`slugify`), unique; on collision append `-2`, `-3`. |
| **Read** | Existing list; show Active/Hidden badge. |
| **Update** | Edit all fields + optional new image; restock stays as stock field. |
| **Delete** | Default: `is_active = 0` (hidden from storefront). **Reactivate** sets `is_active = 1`. **Hard delete** only if `COUNT(order_items) = 0`; otherwise show error “This product is on past orders. Hide it instead.” |

Categories stay the ENUM: `fresh`, `dried`, `bloombox`, `glassdome`, `others`.

Images: reuse `Uploads::store()` → `uploads/{32hex}.ext`. Do not allow SVG/HTML/PHP.

### 3.4 Overview analytics (server-side, no new tables)

Extend stats (all **excluding cancelled** unless noted):

| Metric | Definition |
|---|---|
| Awaiting payment | `payment_received_at IS NULL AND status <> 'cancelled'` |
| Paid orders | `payment_received_at IS NOT NULL AND status <> 'cancelled'` |
| Status counts | pending / processing / delivered / cancelled (cancelled included here) |
| Revenue 7d / 30d | `SUM(total_php)` where `status <> cancelled` and `created_at >= …` |
| Fulfillment mix | pickup vs delivery counts (non-cancelled) |
| Payment method mix | Pay at Shop / GCash / BDO / BPI (non-cancelled) |
| Top 5 products | `order_items` qty sum joined to non-cancelled orders |

Render with cards + simple horizontal CSS bars (percent of total). No Chart.js.

---

## 4. File map

| File | Responsibility |
|---|---|
| `src/Orders.php` | `markPaymentReceived` transactional; `analytics()`; success/error messages |
| `src/Catalog.php` | `create`, `update`, `setActive`, `deleteIfUnused`, `uniqueSlug` |
| `public/admin.php` | POST actions, flashes, pass `$analytics`, `$editProduct` |
| `views/admin/dashboard.php` | Tables, dialogs, CRUD forms, overview widgets |
| `views/partials/admin-order-payment-dialog.php` | One confirmation dialog (include twice or once at page end) |
| `public/assets/js/admin.js` | Dialog open/close only (no business logic) |
| `views/partials/head.php` | Load `admin.js` when `cssBundle === 'app'` and we are on admin — **or** include script at bottom of dashboard only (prefer dashboard) |
| `tests/OrdersPaymentTest.php` | Payment→status rules |
| `tests/CatalogCrudTest.php` | Create/slug/deactivate/refuse hard delete |
| `tests/OrdersAnalyticsTest.php` | Optional light assertions on analytics shape if DB available |

Do **not** split `dashboard.php` unless it grows past ~700 lines after this work; if it does, extract `views/admin/orders.php`, `inventory.php`, `overview.php` as view partials included by dashboard (same `$tab` switch). Prefer extracting **during** Task 3/4 if the file is already hard to edit.

---

## 5. UX / accessibility bar (orders + inventory tables)

Apply on both desktop tables and mobile cards:

- `<table>`: `<caption class="sr-only">` (add `.sr-only` in `resources/css/app.css` if missing: clip/absolute visually hidden).
- Header cells: `scope="col"`.
- Every control has a visible or `aria-label` (e.g. `aria-label="Record payment for KDB-…"`).
- Status `<select>`: `<label class="sr-only">Status for <?= e($code) ?></label>`.
- Success flash: `$_SESSION['admin_flash_success']` green banner (today only `admin_flash_error`).
- Filters on orders: GET `?tab=orders&status=&payment=` (pending / unpaid). Do not use POST for filters.
- Empty states stay full-width messages, not a broken table.
- Do not use color as the only status signal; keep text labels (Pending, Payment recorded).

---

## 6. Implementation tasks

### Task 1: Payment recording advances status (domain)

**Files:**
- Modify: `src/Orders.php` `markPaymentReceived`
- Test: `tests/OrdersPaymentTest.php` (new)
- Modify: `public/admin.php` flash success

**Interfaces:**
- Consumes: existing `updateStatus()`, `findById()`, `STATUSES`
- Produces: `Orders::markPaymentReceived(int $orderId): array` returning the updated order row (so the controller can flash “Payment recorded. Status is now Processing.”)

**Rules to implement inside one DB transaction:**

```php
public static function markPaymentReceived(int $orderId): array
{
    // FOR UPDATE the order
    // if missing → RuntimeException 'Order not found.'
    // if cancelled → InvalidArgumentException 'Cancelled orders cannot be marked paid.'
    // if payment_received_at already set → return findById (idempotent, no status change)
    // SET payment_received_at = NOW()
    // if status === 'pending' → apply same path as updateStatus to 'processing'
    //   (either call internal status mutation WHILE already in this transaction,
    //    or set status = processing in the same UPDATE if old status is pending —
    //    pending→processing does not touch stock)
    // commit
    // Mailer::statusChanged only if status actually changed
    // return findById
}
```

Do **not** call `updateStatus()` as a nested transaction if it always `beginTransaction()` — either extract a private `applyStatusChange(PDO $pdo, …)` used by both methods, or special-case pending→processing in the payment UPDATE (stock unchanged). Prefer extracting `applyStatusChange` so cancel/reopen stays the single stock path.

- [ ] **Step 1:** Add failing tests in `tests/OrdersPaymentTest.php` (DB integration, skip if no DB): pending unpaid order → after `markPaymentReceived`, `payment_received_at` not null and `status === 'processing'`; cancelled order throws; already-processing unpaid → timestamp set, status stays `processing`.
- [ ] **Step 2:** Run `C:\xampp\php\php.exe vendor\bin\phpunit tests/OrdersPaymentTest.php` — expect FAIL.
- [ ] **Step 3:** Implement transactional `markPaymentReceived` + shared status helper if needed.
- [ ] **Step 4:** Re-run PHPUnit — expect PASS. Run full suite.
- [ ] **Step 5:** `admin.php` on success: `$_SESSION['admin_flash_success'] = 'Payment recorded.'` plus status clause if it changed. Dashboard shows success banner (same pattern as error, green).

### Task 2: Accessible payment confirmation

**Files:**
- Create: `views/partials/admin-payment-dialog.php`
- Create: `public/assets/js/admin.js`
- Modify: `views/admin/dashboard.php` (desktop + mobile Record payment buttons)
- Modify: `views/admin/dashboard.php` bottom: one `<dialog id="payment-confirm">`

**Interfaces:**
- Consumes: Task 1 POST `confirm_payment`
- Produces: buttons `data-payment-open` with `data-order-id`, `data-code`, `data-total`, `data-method`, `data-ref`

**Dialog markup (one per page):**

```html
<dialog id="payment-confirm" class="…" aria-labelledby="payment-confirm-title">
  <form method="POST" action="admin.php?tab=orders">
    <?= csrf_field() ?>
    <input type="hidden" name="order_id" id="payment-confirm-order-id" value="">
    <h2 id="payment-confirm-title">Confirm payment</h2>
    <p>Record payment for <strong id="payment-confirm-code"></strong>?</p>
    <p id="payment-confirm-meta"></p>
    <button type="button" value="cancel">Cancel</button>
    <button type="submit" name="confirm_payment" value="1">Confirm payment</button>
  </form>
</dialog>
```

Record payment buttons become `type="button"` that fill the hidden fields and `dialog.showModal()`.

- [ ] **Step 1:** Add dialog + `admin.js` (`showModal` / `close` / Esc is native on `<dialog>`).
- [ ] **Step 2:** Wire mobile cards and desktop table (same `data-*` attributes).
- [ ] **Step 3:** Keyboard check: Tab cycles dialog; Cancel and backdrop click close without POST.
- [ ] **Step 4:** Manual: Record payment on a **pending** order → confirm → status Processing + Payment recorded + success flash.

### Task 3: Orders table UX (filters + a11y)

**Files:**
- Modify: `views/admin/dashboard.php` orders tab
- Modify: `public/admin.php` to read GET filters and pass `$orderFilters`
- Modify: `src/Orders.php` `allForAdmin(array $filters = [])`

**Filters (GET):**

- `status`: `all|pending|processing|delivered|cancelled`
- `payment`: `all|awaiting|recorded`

SQL: whitelist only; use the same status/payment enums. Default `all`.

**Table a11y:** caption, `scope="col"`, sr-only labels on selects/buttons, show fulfillment + payment method in the row (new columns or stacked under customer) so operators do not need to guess.

**Row density:** Keep public_code as the primary ID. Date format `M j, Y` for readability.

- [ ] **Step 1:** Add `allForAdmin` filter arguments with bound parameters.
- [ ] **Step 2:** Filter bar above the table (GET form, method get, `tab=orders` hidden).
- [ ] **Step 3:** Accessibility attributes + success flash region `role="status"`.
- [ ] **Step 4:** Verify mobile cards respect the same filters (one query, two presentations).

### Task 4: Inventory CRUD (domain + admin UI)

**Files:**
- Modify: `src/Catalog.php`
- Modify: `public/admin.php`
- Modify: `views/admin/dashboard.php` inventory tab
- Test: `tests/CatalogCrudTest.php`

**Produces:**

```php
Catalog::normalizeSlug(string $name): string
Catalog::uniqueSlug(string $base, ?int $ignoreId = null): string
Catalog::create(array $input): int
Catalog::update(int $id, array $input): void
Catalog::setActive(int $id, bool $active): void
Catalog::deleteIfUnused(int $id): void  // throws if order_items exist
```

`$input` keys: `name` (1–160), `category` (enum), `description` (nullable, max 2000), `price_php` (1–999999), `stock` (0–99999), `badge` (nullable, allowlist: Bestseller|Limited|Luxury|New|''), `is_active` (0|1), `image_path` (on create required).

**Admin UI:**

- Button **Add product** opens a panel/form at top of inventory (not a new route unless `?tab=inventory&new=1` — GET is fine).
- Each row: **Edit** → same form prefilled (`?tab=inventory&edit={id}`).
- **Hide** / **Show** (POST `set_product_active`).
- **Delete** only shown when unused; otherwise tooltip “Hide instead”. Confirm with `<dialog>` (“Hide this product from the shop?” vs “Permanently delete?”).

Restock+image can merge into the edit form to avoid three competing POSTs. Keep a compact stock field on the list for speed, **or** only edit via the form — prefer **list stock still editable** plus full edit form (current restock is useful).

- [ ] **Step 1:** Failing tests: unique slug collision; deleteIfUnused throws when items exist; create then allActive contains it; setActive(false) removes from allActive.
- [ ] **Step 2:** Implement Catalog methods with prepared statements.
- [ ] **Step 3:** Admin POST handlers + CSRF + redirects `admin.php?tab=inventory`.
- [ ] **Step 4:** Forms: create/edit, hide/show, conditional delete dialog.
- [ ] **Step 5:** Storefront check: hidden product disappears from homepage catalog; existing order snapshots keep old name/image.

### Task 5: Overview analytics

**Files:**
- Modify: `src/Orders.php` add `analytics(): array` (or expand `stats()` — prefer **separate** `analytics()` so existing `stats()` callers stay stable)
- Modify: `public/admin.php` pass `'analytics' => Orders::analytics()`
- Modify: `views/admin/dashboard.php` overview tab

**`analytics()` shape:**

```php
[
  'awaiting_payment' => int,
  'paid_count' => int,
  'by_status' => ['pending' => int, 'processing' => int, 'delivered' => int, 'cancelled' => int],
  'revenue_7d_php' => int,
  'revenue_30d_php' => int,
  'by_fulfillment' => ['pickup' => int, 'delivery' => int],
  'by_payment_method' => ['Pay at Shop' => int, 'GCash' => int, 'BDO' => int, 'BPI' => int],
  'top_products' => list<array{name: string, qty: int, revenue_php: int}>, // max 5
]
```

**UI blocks (below existing KPI row):**

1. **Work queue:** awaiting payment, pending, processing (three cards; awaiting payment links to `admin.php?tab=orders&payment=awaiting`).
2. **Status mix:** four CSS bars.
3. **Revenue window:** 7-day and 30-day next to all-time (existing total revenue card stays).
4. **How customers order:** fulfillment + payment method bars.
5. **Top products:** five rows; empty state “No sales yet.”

Keep Recent Orders + Stock Alerts.

- [ ] **Step 1:** Implement `analytics()` with parameterized dates (`(new DateTimeImmutable('-7 days'))`).
- [ ] **Step 2:** Render widgets; links to filtered orders.
- [ ] **Step 3:** Smoke: `http://localhost:8080/admin.php` as admin — numbers match phpMyAdmin counts.

### Task 6: Polish, verify, docs

- [ ] Add `.sr-only` if not present; `npm run build:css` after new Tailwind classes.
- [ ] Flash success + error both on overview/orders/inventory.
- [ ] Full PHPUnit.
- [ ] Manual matrix:

  | Action | Expected |
  |---|---|
  | Record payment on pending (confirm) | Paid + Processing |
  | Record payment on processing | Paid + still Processing |
  | Record payment on cancelled | Error flash, no change |
  | Cancel confirm on dialog | No POST |
  | Create product | Appears on homepage |
  | Hide product | Gone from homepage, still in admin |
  | Delete product with orders | Blocked |
  | Overview unpaid card | Matches orders filter |

- [ ] Update `docs/STRATEGIC-PLAN.md` with a short “Admin operations” note pointing at this file. Do not rewrite P0–P8 history.

---

## 7. What we will not do

| Idea | Why not |
|---|---|
| New `paid` / `refunded` status enum | Payment is a timestamp; fulfillment stays four statuses |
| Hard-delete products that were ordered | Breaks `order_items` FK and history |
| Guest checkout / multi-item cart | Out of scope; qty is still 1 per checkout |
| Real GCash webhook | Needs merchant keys; Phase 8 already deferred this |
| SPA admin or DataTables.js | Extra runtime; CSS+PHP is enough |
| Changing customer `orders.php` beyond showing existing status/payment | Optional later: show “Payment received” on the tracker — **nice follow-up**, not required to close this plan |

**Optional follow-up (not this plan):** customer tracker shows payment recorded; email copy mentions “we started processing.”

---

## 8. Suggested build order

```text
Task 1 (payment→status)  →  Task 2 (confirm dialog)  →  Task 3 (table UX)
Task 4 (inventory CRUD) can start after Task 1 if two people; otherwise after Task 3
Task 5 (overview) after Task 1 so unpaid counts are meaningful
Task 6 last
```

Do not start Overview before payment rules are correct — otherwise “awaiting payment” will confuse testers.

---

## 9. Success definition

This slice is done when:

1. Recording a payment on a **pending** order, after confirmation, shows **Payment recorded** and **Processing**.
2. Cancelled orders cannot be marked paid.
3. Inventory can add, edit, hide/show, and delete-only-if-never-sold — storefront catalog matches `is_active`.
4. Overview shows unpaid queue, status mix, 7/30 day revenue, and top products from live MariaDB.
5. Tables are usable with keyboard and screen-reader labels; mobile cards still work.
6. PHPUnit covering payment status rules and catalog CRUD is green.

---

## 10. Execution handoff

Plan saved for implementation. Two options:

**1. Subagent-driven (recommended)** — one implementer per task, review between tasks.

**2. Inline in this session** — execute tasks here with checkpoints.

Say which approach to use when you want this built.
