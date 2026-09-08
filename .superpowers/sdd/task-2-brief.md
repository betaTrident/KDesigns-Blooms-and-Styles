# Task 2: Accessible payment confirmation

**Files:**
- Create: `views/partials/admin-payment-dialog.php`
- Create: `public/assets/js/admin.js`
- Modify: `views/admin/dashboard.php` (desktop + mobile Record payment buttons)
- Modify: `views/admin/dashboard.php` bottom: one `<dialog id="payment-confirm">`

(File map also names `admin-order-payment-dialog.php`. Use **`views/partials/admin-payment-dialog.php`** as this task specifies.)

**Interfaces:**
- Consumes: Task 1 POST `confirm_payment` (already CSRF-protected in `public/admin.php`)
- Produces: buttons `data-payment-open` with `data-order-id`, `data-code`, `data-total`, `data-method`, `data-ref`

Also include customer name in the dialog (plan §3.2): add `data-customer` and show it in the meta line.

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

## Behavior details

- Never rely on `window.confirm` alone.
- Dialog shows: `public_code`, customer name, amount, payment method, receipt ref.
- **Cancel** closes dialog, no POST.
- **Confirm payment** POSTs `confirm_payment` + CSRF + `order_id`.
- Esc closes; native `<dialog>` modal focus; return focus to the Record payment button that opened it.
- Load `admin.js` at the bottom of `views/admin/dashboard.php` (plan prefers dashboard, not head.php). Keep existing `nav.js`.
- Style to match cream `#f6f3eb` and burgundy `#4c1719`. Use Tailwind classes already used on the admin dashboard.
- `aria-label` on Record payment buttons: `Record payment for {public_code}`.
- Do not change domain payment rules (Task 1). Do not add a JS chart library. Do not commit.
