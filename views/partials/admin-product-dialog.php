<?php
declare(strict_types=1);

$inventoryPagination = is_array($inventoryPagination ?? null) ? $inventoryPagination : [];
$inventoryPage = (int) ($inventoryPagination['page'] ?? 1);
$inventoryPer = (int) ($inventoryPagination['per_page'] ?? Catalog::DEFAULT_PAGE_SIZE);
$inventoryFormAction = Catalog::inventoryUrl($inventoryPage, $inventoryPer);
?>
<dialog id="product-confirm" class="payment-confirm-dialog rounded border border-gray-200 bg-white shadow-xl max-w-md w-[calc(100%-2rem)] p-0" aria-labelledby="product-confirm-title">
    <form method="POST" action="<?= e($inventoryFormAction); ?>" class="p-6 space-y-4">
        <?= csrf_field(); ?>
        <input type="hidden" name="inventory_page" value="<?= $inventoryPage; ?>">
        <input type="hidden" name="inventory_per" value="<?= $inventoryPer; ?>">
        <input type="hidden" name="product_id" id="product-confirm-id" value="">
        <input type="hidden" name="is_active" id="product-confirm-active" value="">
        <h2 id="product-confirm-title" class="font-serif font-bold text-lg text-gray-900">Confirm action</h2>
        <p id="product-confirm-message" class="text-sm text-gray-700"></p>
        <div class="flex flex-wrap justify-end gap-2 pt-2">
            <button type="button" value="cancel" data-product-cancel class="px-4 py-2 border border-gray-300 rounded text-[10px] font-bold uppercase text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" id="product-confirm-submit" name="set_product_active" value="1" class="px-4 py-2 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase hover:opacity-90">Confirm</button>
        </div>
    </form>
</dialog>
