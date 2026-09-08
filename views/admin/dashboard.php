<?php
declare(strict_types=1);
?>
<?php View::render('partials/head', ['pageTitle' => $pageTitle, 'cssBundle' => $cssBundle]); ?>
<body class="bg-[#f6f3eb] font-sans flex h-dvh overflow-hidden">
<?php View::render('partials/admin-sidebar', [
    'tab' => $tab,
    'pendingCount' => $pendingCount,
    'lowStockCount' => $lowStockCount,
]); ?>

    <div id="admin-sidebar-overlay" class="fixed inset-0 z-40 bg-black/50 hidden md:hidden"></div>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 min-h-0 min-w-0 overflow-y-auto overscroll-contain bg-[#f6f3eb] p-3 sm:p-6 md:p-10 w-full">

        <?php if ($flashError !== ''): ?>
            <div role="alert" class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded text-sm text-red-800">
                <?= e($flashError); ?>
            </div>
        <?php endif; ?>

        <?php if (($flashSuccess ?? '') !== ''): ?>
            <div role="status" class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded text-sm text-emerald-800">
                <?= e($flashSuccess); ?>
            </div>
        <?php endif; ?>

        <!-- Top Bar Date Display -->
        <div class="flex items-center justify-between gap-3 mb-6">
            <button type="button" id="admin-menu-toggle" class="md:hidden inline-flex items-center justify-center gap-2 px-3 py-2 bg-[#4c1719] text-white rounded text-xs font-bold uppercase tracking-wider" aria-controls="admin-sidebar" aria-expanded="false">
                <i class="fa-solid fa-bars"></i> Menu
            </button>
            <div class="bg-white px-4 py-2 rounded border border-gray-200 text-xs text-gray-700 shadow-sm font-medium ml-auto">
                <?= e(date('D, F j, Y')); ?>
            </div>
        </div>

        <!-- TAB 1: OVERVIEW -->
        <?php if ($tab === 'overview'): ?>
            <?php View::render('admin/overview', get_defined_vars()); ?>

        <!-- TAB 2: ORDERS -->
        <?php elseif ($tab === 'orders'):
            $hasActiveOrderFilters = ($orderFilters['status'] ?? 'all') !== 'all'
                || ($orderFilters['payment'] ?? 'all') !== 'all';
            $ordersEmptyMessage = $hasActiveOrderFilters
                ? 'No orders match these filters.'
                : 'No orders yet.';
        ?>
            <div class="mb-6">
                <p class="text-[10px] tracking-widest font-semibold text-gray-400 uppercase">ORDERS</p>
                <h2 class="admin-page-title font-serif font-bold text-gray-900 mt-0.5">Order Management</h2>
            </div>

            <form method="get" action="admin.php" class="mb-4 flex flex-wrap items-end gap-3 bg-white border border-gray-200 rounded shadow-sm p-4">
                <input type="hidden" name="tab" value="orders">
                <div>
                    <label for="filter-status" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                    <select id="filter-status" name="status" class="px-3 py-2 bg-white border border-gray-300 rounded text-xs min-w-[140px]">
                        <option value="all" <?= ($orderFilters['status'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All statuses</option>
                        <?php foreach (Orders::STATUSES as $statusOption): ?>
                            <option value="<?= e($statusOption); ?>" <?= ($orderFilters['status'] ?? 'all') === $statusOption ? 'selected' : ''; ?>><?= e(Orders::statusLabel($statusOption)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="filter-payment" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Payment</label>
                    <select id="filter-payment" name="payment" class="px-3 py-2 bg-white border border-gray-300 rounded text-xs min-w-[140px]">
                        <option value="all" <?= ($orderFilters['payment'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All payments</option>
                        <option value="awaiting" <?= ($orderFilters['payment'] ?? 'all') === 'awaiting' ? 'selected' : ''; ?>>Awaiting payment</option>
                        <option value="recorded" <?= ($orderFilters['payment'] ?? 'all') === 'recorded' ? 'selected' : ''; ?>>Payment recorded</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase hover:bg-[#361012]">Apply filters</button>
                <?php if ($hasActiveOrderFilters): ?>
                    <a href="admin.php?tab=orders" class="px-4 py-2 border border-gray-300 rounded text-[10px] font-bold uppercase text-gray-700 hover:bg-gray-50">Clear filters</a>
                <?php endif; ?>
            </form>

            <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
                <div class="md:hidden divide-y divide-gray-100">
                    <?php if ($adminOrders === []): ?>
                        <p class="p-4 text-center text-sm text-gray-500"><?= e($ordersEmptyMessage); ?></p>
                    <?php else: ?>
                        <?php foreach ($adminOrders as $ord):
                            $orderStatus = Orders::normalizeStatus((string) $ord['status']);
                            $createdTs = strtotime((string) $ord['created_at']);
                            $orderDate = $createdTs !== false ? date('M j, Y', $createdTs) : '';
                            $paymentReceived = trim((string) ($ord['payment_received_at'] ?? '')) !== '';
                            $receiptRef = trim((string) ($ord['receipt_ref'] ?? ''));
                            $statusSelectId = 'status-select-' . (int) $ord['id'];
                        ?>
                            <article class="p-4 space-y-3">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Order ID</p>
                                        <p class="font-bold text-sm text-gray-900"><?= e($ord['public_code']); ?></p>
                                    </div>
                                    <span class="flex-shrink-0 px-2 py-0.5 <?= e(Orders::statusBadgeClasses($orderStatus)); ?> font-bold text-[10px] uppercase rounded">
                                        <?= e(Orders::statusLabel($orderStatus)); ?>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Customer</p>
                                    <p class="font-bold text-sm text-gray-900"><?= e($ord['customer_name']); ?></p>
                                    <p class="text-xs text-gray-500 break-all"><?= e($ord['user_email']); ?></p>
                                    <p class="text-xs text-gray-600 mt-1"><?= e(Orders::fulfillmentLabel((string) $ord['fulfillment'])); ?> · <?= e($ord['payment_method']); ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Items</p>
                                    <div class="text-xs text-gray-700"><?= kd_items_html(Orders::itemsLabel($ord)); ?></div>
                                </div>
                                <div class="flex justify-between items-center gap-3">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total</p>
                                        <p class="font-serif font-bold text-[#4c1719]"><?= e(Catalog::formatPrice((int) $ord['total_php'])); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</p>
                                        <p class="text-xs text-gray-500"><?= e($orderDate); ?></p>
                                    </div>
                                </div>
                                <div class="pt-2 border-t border-gray-100">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Payment</p>
                                    <?php if ($paymentReceived): ?>
                                        <span class="text-emerald-700 font-semibold text-xs">Payment recorded</span>
                                    <?php else: ?>
                                        <div class="space-y-3">
                                            <span class="text-amber-700 font-semibold text-xs">Awaiting payment</span>
                                            <?php if ($receiptRef !== ''): ?>
                                                <p class="text-[10px] text-gray-500">Ref <?= e($receiptRef); ?></p>
                                            <?php endif; ?>
                                            <button
                                                type="button"
                                                data-payment-open
                                                data-order-id="<?= (int) $ord['id']; ?>"
                                                data-code="<?= e($ord['public_code']); ?>"
                                                data-customer="<?= e($ord['customer_name']); ?>"
                                                data-total="<?= e(Catalog::formatPrice((int) $ord['total_php'])); ?>"
                                                data-method="<?= e($ord['payment_method']); ?>"
                                                data-ref="<?= e($receiptRef); ?>"
                                                aria-label="Record payment for <?= e($ord['public_code']); ?>"
                                                class="w-full px-3 py-2 bg-emerald-700 text-white rounded text-[10px] font-bold uppercase hover:bg-emerald-800"
                                            >Record payment</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="pt-2 border-t border-gray-100">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Update Status</p>
                                    <form action="<?= e($ordersTabUrl ?? Orders::adminOrdersUrl($orderFilters ?? [])); ?>" method="POST" class="flex flex-col gap-2">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="order_id" value="<?= (int) $ord['id']; ?>">
                                        <input type="hidden" name="filter_status" value="<?= e($orderFilters['status'] ?? 'all'); ?>">
                                        <input type="hidden" name="filter_payment" value="<?= e($orderFilters['payment'] ?? 'all'); ?>">
                                        <label class="sr-only" for="<?= e($statusSelectId); ?>">Status for <?= e($ord['public_code']); ?></label>
                                        <select id="<?= e($statusSelectId); ?>" name="new_status" class="w-full px-2 py-2 bg-white border border-gray-300 rounded text-xs">
                                            <?php foreach (Orders::STATUSES as $statusOption): ?>
                                                <option value="<?= e($statusOption); ?>" <?= $orderStatus === $statusOption ? 'selected' : ''; ?>><?= e(Orders::statusLabel($statusOption)); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="update_order_status" aria-label="Save status for <?= e($ord['public_code']); ?>" class="w-full px-3 py-2 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase">Save</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="admin-desktop-table overflow-x-auto">
                <table class="w-full min-w-[1080px] text-left border-collapse text-xs">
                    <caption class="sr-only">Admin orders list</caption>
                    <thead>
                        <tr class="bg-[#fbf9f5] border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th scope="col" class="p-4">Order ID</th>
                            <th scope="col" class="p-4">Customer</th>
                            <th scope="col" class="p-4">Email</th>
                            <th scope="col" class="p-4">Fulfillment</th>
                            <th scope="col" class="p-4">Items</th>
                            <th scope="col" class="p-4">Total</th>
                            <th scope="col" class="p-4">Status</th>
                            <th scope="col" class="p-4">Payment</th>
                            <th scope="col" class="p-4">Update Status</th>
                            <th scope="col" class="p-4">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <?php if ($adminOrders === []): ?>
                            <tr>
                                <td colspan="10" class="p-4 text-center text-gray-500"><?= e($ordersEmptyMessage); ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($adminOrders as $ord):
                                $orderStatus = Orders::normalizeStatus((string) $ord['status']);
                                $createdTs = strtotime((string) $ord['created_at']);
                                $orderDate = $createdTs !== false ? date('M j, Y', $createdTs) : '';
                                $statusSelectId = 'status-select-dt-' . (int) $ord['id'];
                            ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 font-bold text-gray-900"><?= e($ord['public_code']); ?></td>
                                    <td class="p-4 font-bold text-gray-900"><?= e($ord['customer_name']); ?></td>
                                    <td class="p-4 text-gray-500"><?= e($ord['user_email']); ?></td>
                                    <td class="p-4 text-gray-600">
                                        <span class="block"><?= e(Orders::fulfillmentLabel((string) $ord['fulfillment'])); ?></span>
                                        <span class="block text-[10px] text-gray-500 mt-0.5"><?= e($ord['payment_method']); ?></span>
                                    </td>
                                    <td class="p-4"><?= kd_items_html(Orders::itemsLabel($ord)); ?></td>
                                    <td class="p-4 font-serif font-bold text-[#4c1719]"><?= e(Catalog::formatPrice((int) $ord['total_php'])); ?></td>
                                    <td class="p-4">
                                        <span class="px-2 py-0.5 <?= e(Orders::statusBadgeClasses($orderStatus)); ?> font-bold text-[10px] uppercase rounded">
                                            <?= e(Orders::statusLabel($orderStatus)); ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <?php
                                        $paymentReceived = trim((string) ($ord['payment_received_at'] ?? '')) !== '';
                                        $receiptRef = trim((string) ($ord['receipt_ref'] ?? ''));
                                        ?>
                                        <?php if ($paymentReceived): ?>
                                            <span class="text-emerald-700 font-semibold">Payment recorded</span>
                                        <?php else: ?>
                                            <div class="space-y-2">
                                                <span class="text-amber-700 font-semibold">Awaiting payment</span>
                                                <?php if ($receiptRef !== ''): ?>
                                                    <p class="text-[10px] text-gray-500">Ref <?= e($receiptRef); ?></p>
                                                <?php endif; ?>
                                                <button
                                                    type="button"
                                                    data-payment-open
                                                    data-order-id="<?= (int) $ord['id']; ?>"
                                                    data-code="<?= e($ord['public_code']); ?>"
                                                    data-customer="<?= e($ord['customer_name']); ?>"
                                                    data-total="<?= e(Catalog::formatPrice((int) $ord['total_php'])); ?>"
                                                    data-method="<?= e($ord['payment_method']); ?>"
                                                    data-ref="<?= e($receiptRef); ?>"
                                                    aria-label="Record payment for <?= e($ord['public_code']); ?>"
                                                    class="px-2 py-1 bg-emerald-700 text-white rounded text-[10px] font-bold uppercase hover:bg-emerald-800"
                                                >Record payment</button>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4">
                                        <form action="<?= e($ordersTabUrl ?? Orders::adminOrdersUrl($orderFilters ?? [])); ?>" method="POST" class="flex flex-wrap gap-2">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="order_id" value="<?= (int) $ord['id']; ?>">
                                            <input type="hidden" name="filter_status" value="<?= e($orderFilters['status'] ?? 'all'); ?>">
                                            <input type="hidden" name="filter_payment" value="<?= e($orderFilters['payment'] ?? 'all'); ?>">
                                            <label class="sr-only" for="<?= e($statusSelectId); ?>">Status for <?= e($ord['public_code']); ?></label>
                                            <select id="<?= e($statusSelectId); ?>" name="new_status" class="px-2 py-1 bg-white border border-gray-300 rounded text-xs">
                                                <?php foreach (Orders::STATUSES as $statusOption): ?>
                                                    <option value="<?= e($statusOption); ?>" <?= $orderStatus === $statusOption ? 'selected' : ''; ?>><?= e(Orders::statusLabel($statusOption)); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="update_order_status" aria-label="Save status for <?= e($ord['public_code']); ?>" class="px-2 py-1 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase">Save</button>
                                        </form>
                                    </td>
                                    <td class="p-4 text-gray-500"><?= e($orderDate); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>

        <!-- TAB 3: INVENTORY -->
        <?php elseif ($tab === 'inventory'):
            $inventoryPagination = $inventoryPagination ?? Catalog::paginate($inventory ?? [], 1, Catalog::DEFAULT_PAGE_SIZE);
            $pageItems = $inventoryPagination['items'];
            $inventoryPage = (int) $inventoryPagination['page'];
            $inventoryPer = (int) $inventoryPagination['per_page'];
        ?>
            <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <p class="text-[10px] tracking-widest font-semibold text-gray-400 uppercase">INVENTORY</p>
                    <h2 class="admin-page-title font-serif font-bold text-gray-900 mt-0.5">Inventory System</h2>
                </div>
                <button type="button" data-product-form-open class="inline-flex items-center justify-center px-4 py-2 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase tracking-wider hover:opacity-90">Add product</button>
            </div>

            <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
                <div class="md:hidden divide-y divide-gray-100">
                    <?php if ($pageItems === []): ?>
                        <p class="p-6 text-center text-sm text-gray-500">No products yet.</p>
                    <?php else: ?>
                    <?php foreach ($pageItems as $p):
                        $stockUi = Catalog::stockDisplay((int) $p['stock']);
                        $isActive = (int) $p['is_active'] === 1;
                        $hasOrders = Catalog::hasOrderItems((int) $p['id']);
                    ?>
                        <article class="p-4 space-y-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="<?= e(kd_image_url($p['image_path'])); ?>" alt="<?= e($p['name']); ?>" class="w-12 h-12 flex-shrink-0 object-cover rounded border">
                                <div class="min-w-0 flex-1">
                                    <p class="font-serif font-bold text-sm text-gray-900"><?= e($p['name']); ?></p>
                                    <p class="text-[10px] text-gray-400 uppercase font-semibold"><?= e(Catalog::categoryLabel($p['category'])); ?></p>
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $isActive ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600'; ?>"><?= $isActive ? 'Active' : 'Hidden'; ?></span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Price</p>
                                    <p class="font-serif font-bold text-[#4c1719] text-sm"><?= e(Catalog::formatPrice((int) $p['price_php'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Current Stock</p>
                                    <p class="font-bold text-sm <?= (int) $p['stock'] <= 3 ? 'text-amber-700' : 'text-gray-900'; ?>"><?= (int) $p['stock']; ?></p>
                                </div>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</p>
                                <span class="<?= e($stockUi['class'] === 'out-of-stock' ? 'text-gray-500' : ($stockUi['class'] === 'low-stock' ? 'text-amber-700' : 'text-emerald-700')); ?> font-semibold text-xs"><?= e($stockUi['text']); ?></span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="px-3 py-1 border border-gray-300 rounded text-[10px] font-bold uppercase text-gray-700" data-product-form-open data-product-id="<?= (int) $p['id']; ?>" aria-label="Edit <?= e($p['name']); ?>">Edit</button>
                                <?php if ($isActive): ?>
                                    <button type="button" class="px-3 py-1 border border-gray-300 rounded text-[10px] font-bold uppercase text-gray-700" data-product-open data-action="hide" data-product-id="<?= (int) $p['id']; ?>" data-product-name="<?= e($p['name']); ?>">Hide</button>
                                <?php else: ?>
                                    <button type="button" class="px-3 py-1 border border-emerald-700 text-emerald-800 rounded text-[10px] font-bold uppercase" data-product-open data-action="show" data-product-id="<?= (int) $p['id']; ?>" data-product-name="<?= e($p['name']); ?>">Show</button>
                                <?php endif; ?>
                                <?php if (!$hasOrders): ?>
                                    <button type="button" class="px-3 py-1 border border-red-300 text-red-800 rounded text-[10px] font-bold uppercase" data-product-open data-action="delete" data-product-id="<?= (int) $p['id']; ?>" data-product-name="<?= e($p['name']); ?>">Delete</button>
                                <?php else: ?>
                                    <span class="px-3 py-1 text-[10px] text-gray-400" title="Hide instead">Delete unavailable</span>
                                <?php endif; ?>
                            </div>
                            <div class="pt-2 border-t border-gray-100">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Restock Qty</p>
                                <form action="" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="inventory_page" value="<?= $inventoryPage; ?>">
                                    <input type="hidden" name="inventory_per" value="<?= $inventoryPer; ?>">
                                    <input type="hidden" name="product_id" value="<?= (int) $p['id']; ?>">
                                    <label class="sr-only" for="restock-qty-m-<?= (int) $p['id']; ?>">Restock quantity</label>
                                    <input type="number" id="restock-qty-m-<?= (int) $p['id']; ?>" name="restock_qty" min="0" max="99999" value="<?= (int) $p['stock']; ?>" class="w-full px-2 py-2 bg-white border border-gray-300 rounded text-xs">
                                    <label class="sr-only" for="restock-img-m-<?= (int) $p['id']; ?>">Replace image</label>
                                    <input type="file" id="restock-img-m-<?= (int) $p['id']; ?>" name="product_image" accept="image/jpeg,image/png,image/webp" class="w-full text-xs">
                                    <button type="submit" name="restock_item" class="w-full px-3 py-2 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase">Save</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="admin-desktop-table overflow-x-auto">
                <table class="w-full min-w-[900px] text-left border-collapse text-xs">
                    <caption class="sr-only">Product inventory</caption>
                    <thead>
                        <tr class="bg-[#fbf9f5] border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th scope="col" class="p-4">Product</th>
                            <th scope="col" class="p-4">Category</th>
                            <th scope="col" class="p-4">Price</th>
                            <th scope="col" class="p-4">Current Stock</th>
                            <th scope="col" class="p-4">Stock status</th>
                            <th scope="col" class="p-4">Visibility</th>
                            <th scope="col" class="p-4">Restock Qty</th>
                            <th scope="col" class="p-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <?php if ($pageItems === []): ?>
                            <tr>
                                <td colspan="8" class="p-6 text-center text-gray-500">No products yet.</td>
                            </tr>
                        <?php else: ?>
                        <?php foreach ($pageItems as $p):
                            $stockUi = Catalog::stockDisplay((int) $p['stock']);
                            $isActive = (int) $p['is_active'] === 1;
                            $hasOrders = Catalog::hasOrderItems((int) $p['id']);
                        ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <img src="<?= e(kd_image_url($p['image_path'])); ?>" alt="<?= e($p['name']); ?>" class="w-10 h-10 object-cover rounded border">
                                        <span class="font-serif font-bold text-gray-900"><?= e($p['name']); ?></span>
                                    </div>
                                </td>
                                <td class="p-4 text-gray-500 font-semibold"><?= e(Catalog::categoryLabel($p['category'])); ?></td>
                                <td class="p-4 font-serif font-bold text-[#4c1719]"><?= e(Catalog::formatPrice((int) $p['price_php'])); ?></td>
                                <td class="p-4 font-bold <?= (int) $p['stock'] <= 3 ? 'text-amber-700' : 'text-gray-900'; ?>"><?= (int) $p['stock']; ?></td>
                                <td class="p-4">
                                    <span class="<?= e($stockUi['class'] === 'out-of-stock' ? 'text-gray-500' : ($stockUi['class'] === 'low-stock' ? 'text-amber-700' : 'text-emerald-700')); ?> font-semibold"><?= e($stockUi['text']); ?></span>
                                </td>
                                <td class="p-4">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $isActive ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600'; ?>"><?= $isActive ? 'Active' : 'Hidden'; ?></span>
                                </td>
                                <td class="p-4">
                                    <form action="" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="inventory_page" value="<?= $inventoryPage; ?>">
                                        <input type="hidden" name="inventory_per" value="<?= $inventoryPer; ?>">
                                        <input type="hidden" name="product_id" value="<?= (int) $p['id']; ?>">
                                        <label class="sr-only" for="restock-qty-<?= (int) $p['id']; ?>">Restock quantity</label>
                                        <input type="number" id="restock-qty-<?= (int) $p['id']; ?>" name="restock_qty" min="0" max="99999" value="<?= (int) $p['stock']; ?>" class="w-16 px-2 py-1 bg-white border border-gray-300 rounded text-xs">
                                        <label class="sr-only" for="restock-img-<?= (int) $p['id']; ?>">Replace image</label>
                                        <input type="file" id="restock-img-<?= (int) $p['id']; ?>" name="product_image" accept="image/jpeg,image/png,image/webp" class="max-w-[120px] text-[10px]">
                                        <button type="submit" name="restock_item" class="px-3 py-1 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase">Save</button>
                                    </form>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap items-center gap-1">
                                        <button type="button" class="px-2 py-1 border border-gray-300 rounded text-[10px] font-bold uppercase text-gray-700 hover:bg-gray-50" data-product-form-open data-product-id="<?= (int) $p['id']; ?>" aria-label="Edit <?= e($p['name']); ?>">Edit</button>
                                        <?php if ($isActive): ?>
                                            <button type="button" class="px-2 py-1 border border-gray-300 rounded text-[10px] font-bold uppercase text-gray-700 hover:bg-gray-50" data-product-open data-action="hide" data-product-id="<?= (int) $p['id']; ?>" data-product-name="<?= e($p['name']); ?>">Hide</button>
                                        <?php else: ?>
                                            <button type="button" class="px-2 py-1 border border-emerald-700 text-emerald-800 rounded text-[10px] font-bold uppercase hover:bg-emerald-50" data-product-open data-action="show" data-product-id="<?= (int) $p['id']; ?>" data-product-name="<?= e($p['name']); ?>">Show</button>
                                        <?php endif; ?>
                                        <?php if (!$hasOrders): ?>
                                            <button type="button" class="px-2 py-1 border border-red-300 text-red-800 rounded text-[10px] font-bold uppercase hover:bg-red-50" data-product-open data-action="delete" data-product-id="<?= (int) $p['id']; ?>" data-product-name="<?= e($p['name']); ?>">Delete</button>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-[10px] text-gray-400 cursor-help" title="Hide instead">Delete</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
                <?php View::render('partials/admin-inventory-pagination', [
                    'inventoryPagination' => $inventoryPagination,
                ]); ?>
            </div>

        <!-- TAB 4: BUYERS HISTORY -->
        <?php elseif ($tab === 'buyers'): ?>
            <div class="mb-6">
                <p class="text-[10px] tracking-widest font-semibold text-gray-400 uppercase">BUYERS HISTORY</p>
                <h2 class="admin-page-title font-serif font-bold text-gray-900 mt-0.5">Buyers History</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-6">
                <div class="bg-white p-4 sm:p-5 rounded border-l-4 border-[#4c1719] shadow-sm">
                    <p class="text-[10px] font-bold text-gray-500 uppercase">TOTAL BUYERS</p>
                    <h3 class="text-2xl font-serif font-bold text-gray-900 mt-1"><?= (int) $stats['buyer_count']; ?></h3>
                </div>
                <div class="bg-white p-4 sm:p-5 rounded border-l-4 border-emerald-600 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-500 uppercase">TOTAL REVENUE</p>
                    <h3 class="text-2xl font-serif font-bold text-[#4c1719] mt-1"><?= e(Catalog::formatPrice((int) $stats['revenue_php'])); ?></h3>
                </div>
                <div class="bg-white p-4 sm:p-5 rounded border-l-4 border-blue-600 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-500 uppercase">AVG. ORDER VALUE</p>
                    <h3 class="text-2xl font-serif font-bold text-blue-900 mt-1"><?= e(Catalog::formatPrice((int) $stats['avg_order_php'])); ?></h3>
                </div>
            </div>

            <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
                <div class="md:hidden divide-y divide-gray-100">
                    <?php if ($buyers === []): ?>
                        <p class="p-4 text-center text-sm text-gray-500">No buyers yet.</p>
                    <?php else: ?>
                        <?php foreach ($buyers as $i => $buyer): ?>
                            <article class="p-4 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Customer</p>
                                        <p class="font-bold text-sm text-gray-900"><?= e($buyer['name']); ?></p>
                                        <p class="text-xs text-gray-500 break-all"><?= e($buyer['email']); ?></p>
                                    </div>
                                    <span class="flex-shrink-0 font-bold text-sm"><?= $i === 0 ? '⭐' : (string) ($i + 1); ?></span>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Orders</p>
                                        <p class="text-sm text-gray-700"><?= (int) $buyer['order_count']; ?></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Spent</p>
                                        <p class="font-serif font-bold text-sm text-[#4c1719]"><?= e(Catalog::formatPrice((int) $buyer['total_spent'])); ?></p>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Items Purchased</p>
                                    <div class="text-xs text-gray-700"><?= kd_items_html((string) $buyer['items_label']); ?></div>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Last Order</p>
                                    <p class="text-xs text-gray-500"><?= e($buyer['last_order_at']); ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="admin-desktop-table overflow-x-auto">
                <table class="w-full min-w-[760px] text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#fbf9f5] border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="p-4">#</th>
                            <th class="p-4">Customer</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Orders</th>
                            <th class="p-4">Items Purchased</th>
                            <th class="p-4">Total Spent</th>
                            <th class="p-4">Last Order</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <?php if ($buyers === []): ?>
                            <tr>
                                <td colspan="7" class="p-4 text-center text-gray-500">No buyers yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($buyers as $i => $buyer): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 font-bold"><?= $i === 0 ? '⭐' : (string) ($i + 1); ?></td>
                                    <td class="p-4 font-bold text-gray-900"><?= e($buyer['name']); ?></td>
                                    <td class="p-4 text-gray-500"><?= e($buyer['email']); ?></td>
                                    <td class="p-4"><?= (int) $buyer['order_count']; ?></td>
                                    <td class="p-4"><?= kd_items_html((string) $buyer['items_label']); ?></td>
                                    <td class="p-4 font-serif font-bold text-[#4c1719]"><?= e(Catalog::formatPrice((int) $buyer['total_spent'])); ?></td>
                                    <td class="p-4 text-gray-500"><?= e($buyer['last_order_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        <?php endif; ?>

    </main>
    <?php View::render('partials/admin-payment-dialog', [
        'ordersTabUrl' => $ordersTabUrl ?? Orders::adminOrdersUrl(Orders::normalizeAdminFilters([])),
        'orderFilters' => $orderFilters ?? Orders::normalizeAdminFilters([]),
    ]); ?>
    <?php if ($tab === 'inventory'): ?>
        <?php View::render('partials/admin-product-form-dialog', [
            'inventory'         => $inventory ?? [],
            'inventoryFormMode' => $inventoryFormMode ?? null,
            'editProduct'       => $editProduct ?? null,
            'inventoryPagination' => $inventoryPagination ?? null,
        ]); ?>
        <?php View::render('partials/admin-product-dialog', [
            'inventoryPagination' => $inventoryPagination ?? null,
        ]); ?>
    <?php endif; ?>
    <script src="<?= e(kd_asset('assets/js/nav.js')); ?>" defer></script>
    <script src="<?= e(kd_asset('assets/js/admin.js')); ?>" defer></script>
</body>
</html>
