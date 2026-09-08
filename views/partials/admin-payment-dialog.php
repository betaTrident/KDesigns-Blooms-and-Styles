<?php
declare(strict_types=1);
?>
<dialog id="payment-confirm" class="payment-confirm-dialog rounded border border-gray-200 bg-white shadow-xl max-w-md w-[calc(100%-2rem)] p-0" aria-labelledby="payment-confirm-title">
    <form method="POST" action="<?= e($ordersTabUrl ?? Orders::adminOrdersUrl($orderFilters ?? [])); ?>" class="p-6 space-y-4">
        <?= csrf_field(); ?>
        <input type="hidden" name="order_id" id="payment-confirm-order-id" value="">
        <input type="hidden" name="filter_status" value="<?= e($orderFilters['status'] ?? 'all'); ?>">
        <input type="hidden" name="filter_payment" value="<?= e($orderFilters['payment'] ?? 'all'); ?>">
        <h2 id="payment-confirm-title" class="font-serif font-bold text-lg text-gray-900">Confirm payment</h2>
        <p class="text-sm text-gray-700">Record payment for <strong id="payment-confirm-code" class="text-gray-900"></strong>?</p>
        <p id="payment-confirm-meta" class="text-xs text-gray-500"></p>
        <div class="flex flex-wrap justify-end gap-2 pt-2">
            <button type="button" value="cancel" data-payment-cancel class="px-4 py-2 border border-gray-300 rounded text-[10px] font-bold uppercase text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" name="confirm_payment" value="1" class="px-4 py-2 bg-emerald-700 text-white rounded text-[10px] font-bold uppercase hover:bg-emerald-800">Confirm payment</button>
        </div>
    </form>
</dialog>
