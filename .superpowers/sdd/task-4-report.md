# Task 4 Report: Inventory CRUD (domain + admin UI)

## Status

DONE

## TDD Evidence

### RED (before implementation)

```
C:\xampp\php\php.exe vendor\bin\phpunit tests/CatalogCrudTest.php

EEEFEEEE                                                            8 / 8 (100%)

 ✘ Normalize slug lowercases and hyphenates
   │ Error: Call to undefined method Catalog::normalizeSlug()
 ✘ Unique slug appends suffix on collision
   │ Error: Call to undefined method Catalog::normalizeSlug()
 ✘ Create then all active contains product
   │ Error: Call to undefined method Catalog::create()
 ✘ Create without image path throws
   │ Failed asserting that exception of type "Error" matches expected exception "InvalidArgumentException"
 ✘ Set active false removes from all active and true restores
   │ Error: Call to undefined method Catalog::create()
 ✘ Delete if unused throws when order items exist
   │ Error: Call to undefined method Catalog::create()
 ✘ Hard delete succeeds when unused
   │ Error: Call to undefined method Catalog::create()
 ✘ Has order items returns true when ordered
   │ Error: Call to undefined method Catalog::create()

ERRORS!
Tests: 8, Assertions: 1, Errors: 7, Failures: 1
```

Failures were for the expected reason: Catalog CRUD methods did not exist yet.

### GREEN (after implementation)

```
C:\xampp\php\php.exe vendor\bin\phpunit tests/CatalogCrudTest.php

........                                                            8 / 8 (100%)

OK (8 tests, 14 assertions)
```

### Full suite

```
C:\xampp\php\php.exe vendor\bin\phpunit

...........................                                       27 / 27 (100%)

OK (27 tests, 70 assertions)
```

## Files Changed

| File | Change |
|------|--------|
| `tests/CatalogCrudTest.php` | **New** — 8 integration tests for slug, create, setActive, deleteIfUnused, hasOrderItems |
| `src/Catalog.php` | Added `normalizeSlug`, `uniqueSlug`, `create`, `update`, `setActive`, `hasOrderItems`, `deleteIfUnused` plus private validators |
| `public/admin.php` | POST handlers: `save_product`, `set_product_active`, `delete_product`; GET `$inventoryFormMode` / `$editProduct` |
| `views/admin/dashboard.php` | Inventory tab: Add/Edit form, Active/Hidden badges, Hide/Show/Delete actions, a11y table attrs, list restock kept |
| `views/partials/admin-product-dialog.php` | **New** — native `<dialog>` for hide/show/delete confirm |
| `public/assets/js/admin.js` | Product confirm dialog wiring (payment dialog unchanged) |

## Implementation Summary

### Domain (`Catalog.php`)

- **Slug:** `normalizeSlug()` lowercases, hyphenates; `uniqueSlug()` appends `-2`, `-3`, … on collision (respects `$ignoreId` on update).
- **Create:** Validates all input keys per brief; image required; inserts with generated slug.
- **Update:** All fields; optional new image (keeps existing path when omitted).
- **setActive:** Sets `is_active` 0|1.
- **hasOrderItems:** `COUNT(order_items) > 0`.
- **deleteIfUnused:** Throws `RuntimeException('This product is on past orders. Hide it instead.')` when ordered; hard-deletes otherwise.

### Admin POST actions

- `save_product` — create (`product_id` 0) or update; file upload via `Uploads::store()` or existing `images/…` path on create.
- `set_product_active` — hide/show with flash messages.
- `delete_product` — hard delete when unused.
- `restock_item` — unchanged (list stock + optional image).

### Admin UI

- **Add product** button → form at top (`?tab=inventory&new=1`).
- **Edit** per row → prefilled form (`?tab=inventory&edit={id}`).
- **Active/Hidden** badge on each row.
- **Hide/Show/Delete** with native confirm dialog (distinct copy for hide vs permanent delete).
- Delete hidden when `Catalog::hasOrderItems()`; tooltip “Hide instead”.
- List restock forms retained with labeled controls.
- Table: sr-only caption, `scope="col"`, cream/burgundy palette, `e()` on output.

## Self-Review

- All brief-required test cases covered and passing.
- Exact delete error message matches spec.
- Storefront uses `Catalog::allActive()` — hidden products excluded; order snapshots unaffected (FK RESTRICT + snapshot columns).
- CSRF enforced on all POSTs (existing admin.php gate).
- Payment dialog and `admin.js` payment flow preserved; product dialog added separately.
- `dashboard.php` is 680 lines — under 700, no partial extraction.
- View isolation: product dialog partial needs no extra `$data`; inventory form vars passed from `admin.php`.

## Concerns

1. **`hasOrderItems()` per row** — Called inside the inventory loop (N+1 queries). Acceptable for current catalog size; could batch-count if inventory grows large.
2. **Create image UX** — New product form accepts file upload *or* typed `images/…` path (server validates); no HTML `required` on file so path-only create works.
3. **Unchecked “Active in shop”** — Unchecked checkbox correctly maps to `is_active = 0` via `isset($_POST['is_active'])`.
4. **No browser/manual UI test** — PHPUnit covers domain; admin UI not automated.

## Commits

None (per instructions).
