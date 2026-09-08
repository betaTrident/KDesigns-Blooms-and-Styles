<?php
declare(strict_types=1);

$inventoryFormMode = $inventoryFormMode ?? null;
$formProduct = $editProduct ?? null;
$inventoryCategories = Catalog::CATEGORIES;
$inventoryBadges = array_merge([''], Catalog::BADGES);
$isEdit = $inventoryFormMode === 'edit' && is_array($formProduct);
$autoOpen = ($inventoryFormMode === 'new' || $isEdit) ? (string) $inventoryFormMode : '';

$inventoryPagination = is_array($inventoryPagination ?? null) ? $inventoryPagination : [];
$inventoryPage = (int) ($inventoryPagination['page'] ?? 1);
$inventoryPer = (int) ($inventoryPagination['per_page'] ?? Catalog::DEFAULT_PAGE_SIZE);
$inventoryFormAction = Catalog::inventoryUrl($inventoryPage, $inventoryPer);

$inventoryProductsJson = [];
foreach ($inventory ?? [] as $p) {
    $inventoryProductsJson[(string) (int) $p['id']] = [
        'id'          => (int) $p['id'],
        'name'        => (string) $p['name'],
        'category'    => (string) $p['category'],
        'description' => (string) ($p['description'] ?? ''),
        'price_php'   => (int) $p['price_php'],
        'stock'       => (int) $p['stock'],
        'badge'       => (string) ($p['badge'] ?? ''),
        'is_active'   => (int) $p['is_active'],
        'image_path'  => (string) $p['image_path'],
        'image_url'   => kd_image_url((string) $p['image_path']),
    ];
}
?>
<dialog
    id="product-form-dialog"
    class="payment-confirm-dialog product-form-dialog rounded border border-gray-200 bg-white shadow-xl w-[calc(100%-2rem)] p-0"
    aria-labelledby="product-form-title"
    data-auto-open="<?= e($autoOpen); ?>"
>
    <form action="<?= e($inventoryFormAction); ?>" method="POST" enctype="multipart/form-data" class="p-5 sm:p-6" id="product-form">
        <?= csrf_field(); ?>
        <input type="hidden" name="inventory_page" value="<?= $inventoryPage; ?>">
        <input type="hidden" name="inventory_per" value="<?= $inventoryPer; ?>">
        <input type="hidden" name="product_id" id="product-id" value="<?= $isEdit ? (int) $formProduct['id'] : 0; ?>">
        <div class="flex items-start justify-between gap-3 mb-5">
            <div>
                <p class="text-[10px] tracking-widest font-semibold text-gray-400 uppercase" id="product-form-kicker"><?= $isEdit ? 'Edit product' : 'New product'; ?></p>
                <h2 id="product-form-title" class="font-serif font-bold text-xl text-gray-900 mt-0.5"><?= $isEdit ? 'Edit product' : 'Add product'; ?></h2>
            </div>
            <button type="button" data-product-form-cancel class="inline-flex items-center justify-center w-9 h-9 rounded border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-800" aria-label="Close product form">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <div>
                <label for="product-name" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Name</label>
                <input type="text" id="product-name" name="name" required maxlength="160" value="<?= e((string) ($formProduct['name'] ?? '')); ?>" class="w-full px-3 py-2 bg-white border border-gray-300 rounded">
            </div>
            <div>
                <label for="product-category" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Category</label>
                <select id="product-category" name="category" required class="w-full px-3 py-2 bg-white border border-gray-300 rounded">
                    <?php foreach ($inventoryCategories as $cat): ?>
                        <option value="<?= e($cat); ?>"<?= ($formProduct['category'] ?? 'fresh') === $cat ? ' selected' : ''; ?>><?= e(Catalog::categoryLabel($cat)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2">
                <label for="product-description" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Description</label>
                <textarea id="product-description" name="description" rows="3" maxlength="2000" class="w-full px-3 py-2 bg-white border border-gray-300 rounded"><?= e((string) ($formProduct['description'] ?? '')); ?></textarea>
            </div>
            <div>
                <label for="product-price" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Price (₱)</label>
                <input type="number" id="product-price" name="price_php" required min="1" max="999999" value="<?= $isEdit ? (int) ($formProduct['price_php'] ?? 0) : ''; ?>" class="w-full px-3 py-2 bg-white border border-gray-300 rounded">
            </div>
            <div>
                <label for="product-stock" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Stock</label>
                <input type="number" id="product-stock" name="stock" required min="0" max="99999" value="<?= (int) ($formProduct['stock'] ?? 0); ?>" class="w-full px-3 py-2 bg-white border border-gray-300 rounded">
            </div>
            <div>
                <label for="product-badge" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Badge</label>
                <select id="product-badge" name="badge" class="w-full px-3 py-2 bg-white border border-gray-300 rounded">
                    <?php
                    $currentBadge = (string) ($formProduct['badge'] ?? '');
                    foreach ($inventoryBadges as $badgeOpt):
                        $selected = ($badgeOpt === '' && $currentBadge === '') || ($badgeOpt !== '' && $currentBadge === $badgeOpt);
                    ?>
                        <option value="<?= e($badgeOpt); ?>"<?= $selected ? ' selected' : ''; ?>><?= $badgeOpt === '' ? 'None' : e($badgeOpt); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="product-active" name="is_active" value="1" class="rounded border-gray-300"<?= (int) ($formProduct['is_active'] ?? 1) === 1 ? ' checked' : ''; ?>>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Active in shop</span>
                </label>
            </div>
            <div class="md:col-span-2">
                <label for="product-image" id="product-image-label" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">
                    <?= $isEdit ? 'Product image (optional)' : 'Product image (required)'; ?>
                </label>
                <div id="product-current-image" class="<?= $isEdit && !empty($formProduct['image_path']) ? 'flex' : 'hidden'; ?> items-center gap-3 mb-2">
                    <img id="product-current-image-src" src="<?= $isEdit && !empty($formProduct['image_path']) ? e(kd_image_url((string) $formProduct['image_path'])) : ''; ?>" alt="" class="w-14 h-14 object-cover rounded border">
                    <p class="text-gray-500">Current: <span id="product-current-image-path"><?= $isEdit ? e((string) ($formProduct['image_path'] ?? '')) : ''; ?></span></p>
                </div>
                <input type="file" id="product-image" name="product_image" accept="image/jpeg,image/png,image/webp" class="w-full text-xs">
                <div id="product-image-path-wrap" class="<?= $isEdit ? 'hidden' : ''; ?>">
                    <label for="product-image-path" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mt-2 mb-1">Catalog image path (optional)</label>
                    <input type="text" id="product-image-path" name="image_path" placeholder="images/example.jpg" class="w-full px-3 py-2 bg-white border border-gray-300 rounded"<?= $isEdit ? ' disabled' : ''; ?>>
                    <p class="mt-1 text-[11px] text-gray-400">Upload a JPEG, PNG, or WebP, or type an existing <code>images/…</code> path.</p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap justify-end gap-2 pt-5">
            <button type="button" data-product-form-cancel class="px-4 py-2 border border-gray-300 rounded text-[10px] font-bold uppercase text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" name="save_product" value="1" id="product-form-submit" class="px-4 py-2 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase hover:opacity-90"><?= $isEdit ? 'Save changes' : 'Create product'; ?></button>
        </div>
    </form>
</dialog>
<script type="application/json" id="inventory-products-data"><?= json_encode($inventoryProductsJson, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?></script>
