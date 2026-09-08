# Task 5: Overview analytics

**Files:**
- Modify: `src/Orders.php` add `analytics(): array` (or expand `stats()` — prefer **separate** `analytics()` so existing `stats()` callers stay stable)
- Modify: `public/admin.php` pass `'analytics' => Orders::analytics()`
- Modify: `views/admin/dashboard.php` overview tab
- Test: `tests/OrdersAnalyticsTest.php` (optional light assertions on analytics shape if DB available — **do add this**; skip if no DB)

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

**Metric definitions** (all excluding cancelled unless noted):

| Metric | Definition |
|---|---|
| Awaiting payment | `payment_received_at IS NULL AND status <> 'cancelled'` |
| Paid orders | `payment_received_at IS NOT NULL AND status <> 'cancelled'` |
| Status counts | pending / processing / delivered / cancelled (cancelled included here) |
| Revenue 7d / 30d | `SUM(total_php)` where `status <> cancelled` and `created_at >= …` |
| Fulfillment mix | pickup vs delivery counts (non-cancelled) |
| Payment method mix | Pay at Shop / GCash / BDO / BPI (non-cancelled) |
| Top 5 products | `order_items` qty sum joined to non-cancelled orders |

**UI blocks (below existing KPI row, above Recent Orders + Stock Alerts):**

1. **Work queue:** awaiting payment, pending, processing (three cards; awaiting payment links to `admin.php?tab=orders&payment=awaiting`). Pending can link to `admin.php?tab=orders&status=pending`. Processing to `admin.php?tab=orders&status=processing`.
2. **Status mix:** four CSS bars (percent of total orders including cancelled). Keep text labels — not color-only.
3. **Revenue window:** 7-day and 30-day next to all-time (existing total revenue card stays).
4. **How customers order:** fulfillment + payment method bars.
5. **Top products:** five rows; empty state “No sales yet.”

Keep Recent Orders + Stock Alerts.

- [ ] **Step 1:** Implement `analytics()` with parameterized dates (`(new DateTimeImmutable('-7 days'))`).
- [ ] **Step 2:** Render widgets; links to filtered orders.
- [ ] **Step 3:** Smoke: `http://localhost:8080/admin.php` as admin — numbers match phpMyAdmin counts.

## Extra overview correctness

Filter `$lowStock` (and the LOW STOCK KPI count) to `is_active === 1` so hidden products do not inflate Overview alerts. Task 4 hide must not keep a product on the Overview stock alert list.

## Bars

Use simple horizontal CSS/Tailwind bars (width percent). No Chart.js, no extra JS library.

## Tests

`tests/OrdersAnalyticsTest.php`: skip if no DB. Assert the keys/shape of `analytics()` and that all counts are ints ≥ 0; `top_products` has at most 5 items; `by_status` has the four keys. If you can cheaply insert a cancelled vs paid order and assert awaiting_payment / paid_count, do that.

PHPUnit: `C:\xampp\php\php.exe vendor\bin\phpunit`

Do not change payment domain or inventory CRUD except the low-stock active filter above.

If dashboard exceeds ~700 lines after widgets, extract `views/admin/overview.php` included from the overview tab.

Do not commit.
