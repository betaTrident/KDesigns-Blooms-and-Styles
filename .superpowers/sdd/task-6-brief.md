# Task 6: Polish, verify, docs, cleanup

## Plan checklist

- [ ] Add `.sr-only` if not present; `npm run build:css` after new Tailwind classes.
- [ ] Flash success + error both on overview/orders/inventory (already global at top of dashboard — confirm they still show on every tab).
- [ ] Full PHPUnit: `C:\xampp\php\php.exe vendor\bin\phpunit`
- [ ] Update `docs/STRATEGIC-PLAN.md` with a short **Admin operations** note pointing at `docs/superpowers/plans/2026-09-08-admin-orders-inventory-overview.md`. Do **not** rewrite P0–P8 history. Keep the TLS/password next-action; add a sentence that admin Orders/Inventory/Overview operations shipped after P8.
- [ ] Align Overview unpaid card with orders filter (required for the success matrix).

## Required code polish (from earlier reviews)

1. **Awaiting-payment filter matches analytics.** In `Orders::allForAdmin()`, when `payment === 'awaiting'`, also require `o.status <> 'cancelled'`. When `payment === 'recorded'`, keep `payment_received_at IS NOT NULL` (cancelled-but-paid should be rare; do not include cancelled in awaiting). Update `tests/OrdersAdminFiltersTest.php` / analytics tests if they assert the old filter SQL. This makes Overview “awaiting payment” match `admin.php?tab=orders&payment=awaiting`.

2. **Create product image path label.** The optional `images/…` text input in the inventory form needs a real `<label for="…">`.

3. **Create price field.** On new product, do not default price to `0`; use empty value.

4. **Payment dialog button order.** Remove `flex-col-reverse` so visual order matches Tab order (Cancel then Confirm), or put Confirm first in the DOM if it should appear first.

5. **Backdrop CSS.** Keep one backdrop style for `#payment-confirm` (either Tailwind `backdrop:bg-black/50` or `.payment-confirm-dialog::backdrop` in `resources/css/app.css`, not both). Rebuild CSS if you change source.

6. **Inventory form enums.** Drive category/badge `<option>` lists from `Catalog::CATEGORIES` / `Catalog::BADGES` (already constants on Catalog if present). Do not duplicate literal arrays in the view.

7. **Low stock** already filtered to active in Task 5 — do not regress.

## Cleanup (only safe deletes)

Do **not** delete:
- `docs/PHASE-8-PLAN.md`, `docs/notes/*`, `docs/database/*`, `.superpowers/`
- `public/admin_login.php` (bookmark redirect)
- `public/images/*`
- `node_modules/` (needed for `build:css`)
- compiled `public/assets/css/app.css`

Do:
- Remove dead `"main": "index.js"` from `package.json` if present and there is no `index.js`.
- Do not add new markdown besides the STRATEGIC-PLAN note.
- Do not invent extra features.

Optional if easy (not required): batch `hasOrderItems` so inventory does not query twice per row.

## Manual matrix (document what you could verify)

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

If you cannot drive a real browser, run PHPUnit plus any HTTP/curl smoke you can, and list the matrix as “not browser-tested” in the report. The orchestrator will attempt localhost verification after you finish.

## Do not commit.

PHPUnit: `C:\xampp\php\php.exe vendor\bin\phpunit`
