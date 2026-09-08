# Task 5 Report: Overview Analytics

## Status: DONE

## Summary

Added `Orders::analytics()` as a separate method from `stats()`, wired it through `admin.php`, and rendered five analytics widget blocks on the Overview tab. Low-stock KPI and Stock Alerts now filter to active products only. Extracted overview markup into `views/admin/overview.php` to keep `dashboard.php` under 700 lines.

## Changes

### `src/Orders.php`
- Added `analytics(?DateTimeImmutable $since7d, ?DateTimeImmutable $since30d): array` with the exact keys specified in the brief.
- Defaults: `(new DateTimeImmutable('-7 days'))` and `(new DateTimeImmutable('-30 days'))`.
- Single aggregate query for counts/revenue/fulfillment/payment mix; separate query for top 5 products by qty from non-cancelled orders.
- Added private `emptyAnalytics()` for the no-rows fallback.
- `stats()` unchanged.

### `public/admin.php`
- Calls `Orders::analytics()` and passes `'analytics'` to the dashboard view.
- `$lowStock` filter now requires `(int) $p['is_active'] === 1` in addition to `stock <= 5`.

### `views/admin/overview.php` (new)
- Extracted from dashboard; contains KPI row, five widget blocks, Recent Orders, and Stock Alerts.
- **Work queue:** three linked cards (awaiting payment → `payment=awaiting`, pending → `status=pending`, processing → `status=processing`).
- **Status mix:** four labeled CSS bars (% of total including cancelled).
- **Revenue window:** 7-day, 30-day, and all-time side by side.
- **How customers order:** fulfillment + payment method bars.
- **Top products:** up to five rows; empty state “No sales yet.”
- Cream `#f6f3eb` / burgundy `#4c1719` palette; all dynamic text escaped with `e()`.

### `views/admin/dashboard.php`
- Overview tab now includes `admin/overview` via `View::render(..., get_defined_vars())`.
- Orders, inventory, and buyers tabs untouched.

### `tests/OrdersAnalyticsTest.php` (new)
- Skips when DB unreachable.
- `testAnalyticsReturnsExpectedShape`: keys, int types, non-negative counts, `by_status` four keys, `top_products` ≤ 5.
- `testAnalyticsSeparatesAwaitingAndPaidExcludingCancelled`: inserts pending unpaid, paid processing, and cancelled; asserts +1 awaiting, +1 paid, +1 cancelled.

### CSS
- Ran `npm run build:css` after adding Tailwind classes in overview partial.

## Tests

```
OrdersAnalyticsTest: 2 tests, 69 assertions — OK
Full suite: 29 tests, 139 assertions — OK
```

Command: `C:\xampp\php\php.exe vendor\bin\phpunit`

## Self-Review

| Check | Result |
|---|---|
| `stats()` stable | Yes — no changes to signature or behavior |
| `analytics()` exact shape | Yes — all keys match brief |
| Metric definitions | Yes — cancelled excluded from awaiting/paid/revenue/fulfillment/payment/top products; included in `by_status` |
| Parameterized dates | Yes — optional `DateTimeImmutable` args with `-7 days` / `-30 days` defaults |
| Filter links | Yes — uses `Orders::adminOrdersUrl()` |
| Low stock active-only | Yes — `$lowStock` and `$lowStockCount` both derived from filtered array |
| No chart library | Yes — inline width-percent CSS bars only |
| Payment dialog / inventory CRUD | Untouched |
| Escaping | Yes — `e()` on all user-facing strings |
| Commits | None (per instructions) |

## Manual Smoke (not run in CI)

Visit `http://localhost:8080/admin.php` as admin and confirm widget numbers align with phpMyAdmin counts for orders, revenue windows, and top products.

## Concerns

None blocking. Revenue 7d/30d windows depend on server `NOW()` and order `created_at` timestamps — consistent with existing `stats()` behavior.
