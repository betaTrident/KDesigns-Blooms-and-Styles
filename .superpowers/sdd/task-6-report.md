# Task 6 Report: Polish, verify, docs, cleanup

## Status: DONE

## Summary

Final polish pass for admin Orders/Inventory/Overview: aligned the awaiting-payment filter with analytics, fixed inventory form accessibility/defaults, cleaned dialog button order and backdrop CSS duplication, drove enum lists from `Catalog` constants, updated `STRATEGIC-PLAN.md`, removed dead `package.json` field, and verified the full PHPUnit suite.

## Changes

### `src/Orders.php`
- `allForAdmin()` awaiting filter now requires `payment_received_at IS NULL AND status <> 'cancelled'`, matching `analytics()['awaiting_payment']` and Overview unpaid card.

### `src/Catalog.php`
- Promoted `CATEGORIES` and `BADGES` from private to public constants for view reuse.

### `views/admin/dashboard.php`
- Inventory category/badge `<option>` lists driven from `Catalog::CATEGORIES` and `array_merge([''], Catalog::BADGES)`.
- New product price field defaults to empty (not `0`).
- Catalog image path input has a proper `<label for="product-image-path">` and matching `id`.
- Flash success + error remain at top of `<main>` — visible on all tabs (overview, orders, inventory, buyers).

### `views/partials/admin-payment-dialog.php` & `admin-product-dialog.php`
- Removed `flex-col-reverse` so DOM/visual order is Cancel then Confirm (matches Tab order).
- Removed duplicate `backdrop:bg-black/50` Tailwind class; backdrop uses `.payment-confirm-dialog::backdrop` in `resources/css/app.css` only.

### `package.json`
- Removed dead `"main": "index.js"` (no `index.js` exists).

### `docs/STRATEGIC-PLAN.md`
- Added **Admin operations (post-P8)** note linking to `superpowers/plans/2026-09-08-admin-orders-inventory-overview.md`.
- Kept existing TLS/password next-action unchanged; did not rewrite P0–P8 history.

### `tests/OrdersAdminFiltersTest.php`
- Added DB integration test `testAwaitingPaymentFilterExcludesCancelledAndMatchesAnalytics` asserting cancelled unpaid orders are excluded from awaiting filter and count matches analytics.

## Checklist

| Item | Done |
|---|---|
| `.sr-only` present (via Tailwind in compiled CSS) | Yes — already used in dashboard |
| Flash on all tabs | Yes — global at top of dashboard main |
| Awaiting filter matches analytics | Yes |
| Image path label | Yes |
| Empty price on create | Yes |
| Dialog button order | Yes |
| Single backdrop style | Yes |
| Catalog enum constants in view | Yes |
| Low stock active-only (Task 5) | Yes — not regressed |
| `package.json` cleanup | Yes |
| STRATEGIC-PLAN update | Yes |
| CSS rebuild | Not needed — no changes to `resources/css/app.css` or new Tailwind classes |

## PHPUnit Output

Command: `C:\xampp\php\php.exe vendor\bin\phpunit`

```
PHPUnit 10.5.64 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.1.25
Configuration: K:\KDesigns-Blooms-and-Styles\phpunit.xml

..............................                                    30 / 30 (100%)

Time: 00:06.603, Memory: 8.00 MB

Catalog
 ✔ Stock display out of stock
 ✔ Stock display low stock
 ✔ Stock display in stock

Catalog Crud
 ✔ Normalize slug lowercases and hyphenates
 ✔ Unique slug appends suffix on collision
 ✔ Create then all active contains product
 ✔ Create without image path throws
 ✔ Set active false removes from all active and true restores
 ✔ Delete if unused throws when order items exist
 ✔ Hard delete succeeds when unused
 ✔ Has order items returns true when ordered

Http Helpers
 ✔ Image url rejects traversal
 ✔ Image url keeps images path

Orders
 ✔ Payment methods
 ✔ Statuses and normalize

Orders Admin Filters
 ✔ Normalize admin filters defaults to all
 ✔ Normalize admin filters accepts valid values
 ✔ Normalize admin filters rejects invalid values
 ✔ Admin orders url omits default filters
 ✔ Admin orders url includes active filters
 ✔ Awaiting payment filter excludes cancelled and matches analytics

Orders Analytics
 ✔ Analytics returns expected shape
 ✔ Analytics separates awaiting and paid excluding cancelled

Orders Integration
 ✔ Create decrements stock and rolls back when stock zero

Orders Payment
 ✔ Mark payment received advances pending to processing
 ✔ Mark payment received rejects cancelled order
 ✔ Mark payment received sets timestamp without changing processing status
 ✔ Mark payment received is idempotent when already paid

Uploads
 ✔ Extension for mime allows images
 ✔ Extension for mime rejects empty and dangerous

OK (30 tests, 143 assertions)
```

## Manual Success Matrix

| Action | Expected | Verified |
|---|---|---|
| Record payment on pending (confirm) | Paid + Processing | **Not browser-tested** — covered by `OrdersPaymentTest::testMarkPaymentReceivedAdvancesPendingToProcessing` |
| Record payment on processing | Paid + still Processing | **Not browser-tested** — covered by `OrdersPaymentTest::testMarkPaymentReceivedSetsTimestampWithoutChangingProcessingStatus` |
| Record payment on cancelled | Error flash, no change | **Not browser-tested** — covered by `OrdersPaymentTest::testMarkPaymentReceivedRejectsCancelledOrder` |
| Cancel confirm on dialog | No POST | **Not browser-tested** — JS dialog cancel handler not exercised in PHPUnit |
| Create product | Appears on homepage | **Not browser-tested** — covered by `CatalogCrudTest::testCreateThenAllActiveContainsProduct` |
| Hide product | Gone from homepage, still in admin | **Not browser-tested** — covered by `CatalogCrudTest::testSetActiveFalseRemovesFromAllActiveAndTrueRestores` |
| Delete product with orders | Blocked | **Not browser-tested** — covered by `CatalogCrudTest::testDeleteIfUnusedThrowsWhenOrderItemsExist` |
| Overview unpaid card | Matches orders filter | **Not browser-tested** — logic verified by new filter/analytics PHPUnit test |

All matrix rows: **not browser-tested** (PHPUnit + code review only). Orchestrator should verify on localhost.

## Self-Review

| Check | Result |
|---|---|
| Features from Tasks 1–5 preserved | Yes |
| No commits / no git add | Yes |
| No historical docs deleted | Yes |
| `.superpowers/` preserved | Yes |
| Optional `hasOrderItems` batch | Skipped (not required) |

## Concerns

None blocking.
