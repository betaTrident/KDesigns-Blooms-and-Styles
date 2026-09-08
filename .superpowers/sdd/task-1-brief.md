# Task 1: Payment recording advances status (domain)

**Files:**
- Modify: `src/Orders.php` `markPaymentReceived`
- Test: `tests/OrdersPaymentTest.php` (new)
- Modify: `public/admin.php` flash success
- Modify: `views/admin/dashboard.php` to show `admin_flash_success` (green banner, same pattern as error)

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

## Exact messages and flash copy

- Missing order: `RuntimeException` `'Order not found.'`
- Cancelled: `InvalidArgumentException` `'Cancelled orders cannot be marked paid.'`
- Invalid id (`< 1`): keep existing `InvalidArgumentException` `'Invalid order.'`
- Success flash when pending→processing: `Payment recorded. Status is now Processing.`
- Success flash when status did not change: `Payment recorded.`
- Idempotent already-paid: no error, return current row, do not change status again, no extra mail.

## Test setup pattern

Mirror `tests/OrdersIntegrationTest.php`: skip if DB unreachable or P8 columns missing; insert disposable user/product/order; tearDown deletes order_items, orders, products, user.

PHPUnit: `C:\xampp\php\php.exe vendor\bin\phpunit`

Follow TDD: write failing tests first, watch them fail for the right reason, then implement.
