# Task 4: Inventory CRUD (domain + admin UI)

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

## Domain details

Categories ENUM: `fresh`, `dried`, `bloombox`, `glassdome`, `others`.

**Create:** insert product; slug from name via `normalizeSlug`, then `uniqueSlug`; on collision append `-2`, `-3`. `is_active=1`. Image required: upload via `Uploads::store()` → `uploads/{32hex}.ext` **or** existing `images/…` path. Reuse `Catalog::updateImage` path rules (`images/` or `uploads/`, no `..`).

**Update:** all fields + optional new image. If no new file, keep current `image_path`.

**Delete:** default UX is hide (`is_active = 0`). **Reactivate** sets `is_active = 1`. **Hard delete** only if `COUNT(order_items) = 0`; otherwise throw with message exactly: `This product is on past orders. Hide it instead.`

Images: `Uploads::store()` only JPEG/PNG/WebP. Do not allow SVG/HTML/PHP.

Add a helper the UI can call, e.g. `Catalog::hasOrderItems(int $id): bool`, so Delete is hidden when the product was ordered.

## Admin POST actions (CSRF already on all POSTs in admin.php)

Suggested names (use these unless they collide):
- `save_product` — create or update (hidden `product_id` empty/0 = create)
- `set_product_active` — `product_id` + `is_active` 0|1
- `delete_product` — `product_id`
- keep existing `restock_item` for list stock + optional image

Flash success/error via existing `admin_flash_success` / `admin_flash_error`.

Pass `$editProduct` (nullable array) and `$inventoryFormMode` (`new`|`edit`|null) from `public/admin.php` when `?new=1` or `?edit={id}`.

## UI / a11y

- Show Active/Hidden badge on each row (plan §3.3 Read).
- Table: caption sr-only, `scope="col"`.
- Labels on every control; confirm dialogs native `<dialog>` like payment (can reuse `admin.js` with extra `data-confirm-open` or a second dialog). Prefer native `<dialog>` not `window.confirm`.
- Match cream `#f6f3eb` / burgundy `#4c1719`.
- Escape all output with `e()`.

If `views/admin/dashboard.php` grows past ~700 lines, extract `views/admin/inventory.php` and include it from the dashboard `$tab === 'inventory'` branch. Do not extract unless it actually exceeds ~700 after this task.

## Tests

New `tests/CatalogCrudTest.php` — DB integration, skip if no DB (same pattern as `tests/OrdersPaymentTest.php` / `tests/OrdersIntegrationTest.php`).

Required cases:
- unique slug collision (`normalizeSlug`/`uniqueSlug` append `-2`)
- `deleteIfUnused` throws when `order_items` exist (exact message)
- create then `allActive` contains it
- `setActive(false)` removes from `allActive`; `setActive(true)` restores
- create without image_path throws
- hard delete succeeds when unused (product gone from `all()`)

TDD: write failing tests first, watch them fail, then implement.

PHPUnit: `C:\xampp\php\php.exe vendor\bin\phpunit`

Do not commit.
