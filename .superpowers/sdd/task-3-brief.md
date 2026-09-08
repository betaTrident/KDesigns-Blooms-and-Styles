# Task 3: Orders table UX (filters + a11y)

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

## Extra a11y bar (plan §5)

Apply on both desktop tables and mobile cards:

- `<table>`: `<caption class="sr-only">`. Add `.sr-only` in `resources/css/app.css` if Tailwind `sr-only` is not already available.
- Header cells: `scope="col"`.
- Every control has a visible or `aria-label` (e.g. `aria-label="Record payment for KDB-…"` — Task 2 already added this; keep it).
- Status `<select>`: `<label class="sr-only">Status for <?= e($code) ?></label>`.
- Success flash: already exists; add `role="status"` (and error `role="alert"` if easy).
- Empty states stay full-width messages, not a broken table.
- Do not use color as the only status signal; keep text labels (Pending, Payment recorded).

## Do not break

- Task 2 payment dialog: keep `[data-payment-open]` buttons and the dialog include + `admin.js`.
- Task 1 `markPaymentReceived` / success flash copy.

If `dashboard.php` grows past ~700 lines, extract `views/admin/orders.php` as a view partial included by dashboard (same `$tab` switch). Do not extract unless it actually exceeds ~700.

Do not commit.
