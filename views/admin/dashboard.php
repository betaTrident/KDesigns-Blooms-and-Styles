<?php
declare(strict_types=1);
?>
<?php View::render('partials/head', ['pageTitle' => $pageTitle, 'cssBundle' => $cssBundle]); ?>
<body class="bg-[#f6f3eb] font-sans flex min-h-dvh md:h-screen overflow-x-hidden md:overflow-hidden">
<?php View::render('partials/admin-sidebar', [
    'tab' => $tab,
    'pendingCount' => $pendingCount,
    'lowStockCount' => $lowStockCount,
]); ?>

    <div id="admin-sidebar-overlay" class="fixed inset-0 z-40 bg-black/50 hidden md:hidden"></div>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 overflow-y-auto bg-[#f6f3eb] p-3 sm:p-6 md:p-10 w-full min-w-0">

        <?php if ($flashError !== ''): ?>
            <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded text-sm text-red-800">
                <?= e($flashError); ?>
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
            <div class="mb-8">
                <p class="text-[10px] tracking-widest font-semibold text-gray-400 uppercase">OVERVIEW</p>
                <h2 class="admin-page-title font-serif font-bold text-gray-900 mt-0.5">Dashboard Overview</h2>
            </div>

            <!-- Top Metric Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3 sm:gap-4 mb-6 sm:mb-8">
                <div class="bg-white p-4 sm:p-5 rounded border-l-4 border-[#4c1719] shadow-sm">
                    <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">TOTAL REVENUE</p>
                    <h3 class="text-2xl font-serif font-bold text-[#4c1719] mt-2"><?= e(Catalog::formatPrice((int) $stats['revenue_php'])); ?></h3>
                    <p class="text-[11px] text-gray-400 mt-1">Excl. cancelled</p>
                </div>
                <div class="bg-white p-4 sm:p-5 rounded border-l-4 border-emerald-600 shadow-sm">
                    <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">TOTAL ORDERS</p>
                    <h3 class="text-2xl font-serif font-bold text-gray-900 mt-2"><?= (int) $stats['order_count']; ?></h3>
                    <p class="text-[11px] text-gray-400 mt-1"><?= (int) $stats['pending_count']; ?> pending</p>
                </div>
                <div class="bg-white p-4 sm:p-5 rounded border-l-4 border-amber-600 shadow-sm">
                    <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">LOW STOCK</p>
                    <h3 class="text-2xl font-serif font-bold text-amber-700 mt-2"><?= (int) $lowStockCount; ?></h3>
                    <p class="text-[11px] text-gray-400 mt-1">Need restocking</p>
                </div>
                <div class="bg-white p-4 sm:p-5 rounded border-l-4 border-blue-600 shadow-sm">
                    <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">ACTIVE PRODUCTS</p>
                    <h3 class="text-2xl font-serif font-bold text-blue-900 mt-2"><?= (int) $activeCount; ?></h3>
                    <p class="text-[11px] text-gray-400 mt-1">of <?= count($inventory); ?> total</p>
                </div>
                <div class="bg-white p-4 sm:p-5 rounded border-l-4 border-[#8c7853] shadow-sm">
                    <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">TOTAL BUYERS</p>
                    <h3 class="text-2xl font-serif font-bold text-gray-900 mt-2"><?= (int) $stats['buyer_count']; ?></h3>
                    <p class="text-[11px] text-gray-400 mt-1">Unique customers</p>
                </div>
            </div>

            <!-- Bottom Split Panels: Recent Orders & Stock Alerts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-8">
                <!-- Recent Orders Panel -->
                <div class="lg:col-span-2 bg-white rounded border border-gray-200 shadow-sm p-4 sm:p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-serif font-bold text-base text-gray-900">Recent Orders</h3>
                        <a href="admin.php?tab=orders" class="text-xs text-[#4c1719] font-semibold hover:underline">View all →</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php if ($recentOrders === []): ?>
                            <p class="py-4 text-sm text-gray-500">No orders yet.</p>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $ord): ?>
                                <div class="py-4 flex justify-between items-center gap-3">
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-sm text-gray-900"><?= e($ord['customer_name']); ?></h4>
                                        <p class="text-xs text-gray-400"><?= e($ord['public_code']); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-serif font-bold text-sm text-[#4c1719]"><?= e(Catalog::formatPrice((int) $ord['total_php'])); ?></p>
                                        <span class="text-[11px] font-semibold <?= e(Orders::statusTextClasses((string) $ord['status'])); ?>"><?= e(Orders::statusLabel((string) $ord['status'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stock Alerts Panel -->
                <div class="bg-white rounded border border-gray-200 shadow-sm p-4 sm:p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-serif font-bold text-base text-gray-900">Stock Alerts</h3>
                        <a href="admin.php?tab=inventory" class="text-xs text-[#4c1719] font-semibold hover:underline">Manage →</a>
                    </div>
                    <div class="space-y-4">
                        <?php if ($lowStock === []): ?>
                            <p class="text-sm text-gray-500">All products are sufficiently stocked.</p>
                        <?php else: ?>
                            <?php foreach ($lowStock as $p): ?>
                                <div class="flex items-center justify-between gap-3 pb-3 border-b border-gray-100">
                                    <img src="<?= e(kd_image_url($p['image_path'])); ?>" alt="<?= e($p['name']); ?>" class="w-10 h-10 object-cover rounded border">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-serif font-bold text-xs text-gray-900 truncate"><?= e($p['name']); ?></h4>
                                        <p class="text-[10px] text-gray-400 uppercase"><?= e(Catalog::categoryLabel($p['category'])); ?></p>
                                    </div>
                                    <span class="text-xs font-bold text-amber-700"><?= (int) $p['stock']; ?> left</span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <!-- TAB 2: ORDERS -->
        <?php elseif ($tab === 'orders'): ?>
            <div class="mb-6">
                <p class="text-[10px] tracking-widest font-semibold text-gray-400 uppercase">ORDERS</p>
                <h2 class="admin-page-title font-serif font-bold text-gray-900 mt-0.5">Order Management</h2>
            </div>

            <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
                <div class="md:hidden divide-y divide-gray-100">
                    <?php if ($adminOrders === []): ?>
                        <p class="p-4 text-center text-sm text-gray-500">No orders yet.</p>
                    <?php else: ?>
                        <?php foreach ($adminOrders as $ord):
                            $orderStatus = Orders::normalizeStatus((string) $ord['status']);
                            $createdTs = strtotime((string) $ord['created_at']);
                            $orderDate = $createdTs !== false ? date('Y-m-d', $createdTs) : '';
                            $paymentReceived = trim((string) ($ord['payment_received_at'] ?? '')) !== '';
                            $receiptRef = trim((string) ($ord['receipt_ref'] ?? ''));
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
                                            <form action="" method="POST">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="order_id" value="<?= (int) $ord['id']; ?>">
                                                <button type="submit" name="confirm_payment" value="1" class="w-full px-3 py-2 bg-emerald-700 text-white rounded text-[10px] font-bold uppercase">Record payment</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="pt-2 border-t border-gray-100">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Update Status</p>
                                    <form action="" method="POST" class="flex flex-col gap-2">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="order_id" value="<?= (int) $ord['id']; ?>">
                                        <select name="new_status" class="w-full px-2 py-2 bg-white border border-gray-300 rounded text-xs">
                                            <?php foreach (Orders::STATUSES as $statusOption): ?>
                                                <option value="<?= e($statusOption); ?>" <?= $orderStatus === $statusOption ? 'selected' : ''; ?>><?= e(Orders::statusLabel($statusOption)); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="update_order_status" class="w-full px-3 py-2 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase">Save</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="admin-desktop-table overflow-x-auto">
                <table class="w-full min-w-[980px] text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#fbf9f5] border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="p-4">Order ID</th>
                            <th class="p-4">Customer</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Items</th>
                            <th class="p-4">Total</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Payment</th>
                            <th class="p-4">Update Status</th>
                            <th class="p-4">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <?php if ($adminOrders === []): ?>
                            <tr>
                                <td colspan="9" class="p-4 text-center text-gray-500">No orders yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($adminOrders as $ord):
                                $orderStatus = Orders::normalizeStatus((string) $ord['status']);
                                $createdTs = strtotime((string) $ord['created_at']);
                                $orderDate = $createdTs !== false ? date('Y-m-d', $createdTs) : '';
                            ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 font-bold text-gray-900"><?= e($ord['public_code']); ?></td>
                                    <td class="p-4 font-bold text-gray-900"><?= e($ord['customer_name']); ?></td>
                                    <td class="p-4 text-gray-500"><?= e($ord['user_email']); ?></td>
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
                                                <form action="" method="POST">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="order_id" value="<?= (int) $ord['id']; ?>">
                                                    <button type="submit" name="confirm_payment" value="1" class="px-2 py-1 bg-emerald-700 text-white rounded text-[10px] font-bold uppercase">Record payment</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4">
                                        <form action="" method="POST" class="flex flex-wrap gap-2">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="order_id" value="<?= (int) $ord['id']; ?>">
                                            <select name="new_status" class="px-2 py-1 bg-white border border-gray-300 rounded text-xs">
                                                <?php foreach (Orders::STATUSES as $statusOption): ?>
                                                    <option value="<?= e($statusOption); ?>" <?= $orderStatus === $statusOption ? 'selected' : ''; ?>><?= e(Orders::statusLabel($statusOption)); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="update_order_status" class="px-2 py-1 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase">Save</button>
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
        <?php elseif ($tab === 'inventory'): ?>
            <div class="mb-6">
                <p class="text-[10px] tracking-widest font-semibold text-gray-400 uppercase">INVENTORY</p>
                <h2 class="admin-page-title font-serif font-bold text-gray-900 mt-0.5">Inventory System</h2>
            </div>

            <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
                <div class="md:hidden divide-y divide-gray-100">
                    <?php foreach ($inventory as $p):
                        $stockUi = Catalog::stockDisplay((int) $p['stock']);
                    ?>
                        <article class="p-4 space-y-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="<?= e(kd_image_url($p['image_path'])); ?>" alt="<?= e($p['name']); ?>" class="w-12 h-12 flex-shrink-0 object-cover rounded border">
                                <div class="min-w-0">
                                    <p class="font-serif font-bold text-sm text-gray-900"><?= e($p['name']); ?></p>
                                    <p class="text-[10px] text-gray-400 uppercase font-semibold"><?= e(Catalog::categoryLabel($p['category'])); ?></p>
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
                            <div class="pt-2 border-t border-gray-100">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Restock Qty</p>
                                <form action="" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= (int) $p['id']; ?>">
                                    <input type="number" name="restock_qty" min="0" max="99999" value="<?= (int) $p['stock']; ?>" class="w-full px-2 py-2 bg-white border border-gray-300 rounded text-xs">
                                    <input type="file" name="product_image" accept="image/jpeg,image/png,image/webp" class="w-full text-xs">
                                    <button type="submit" name="restock_item" class="w-full px-3 py-2 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase">Save</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="admin-desktop-table overflow-x-auto">
                <table class="w-full min-w-[720px] text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#fbf9f5] border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="p-4">Product</th>
                            <th class="p-4">Category</th>
                            <th class="p-4">Price</th>
                            <th class="p-4">Current Stock</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Restock Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <?php foreach ($inventory as $p):
                            $stockUi = Catalog::stockDisplay((int) $p['stock']);
                        ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 flex items-center gap-3">
                                    <img src="<?= e(kd_image_url($p['image_path'])); ?>" alt="<?= e($p['name']); ?>" class="w-10 h-10 object-cover rounded border">
                                    <span class="font-serif font-bold text-gray-900"><?= e($p['name']); ?></span>
                                </td>
                                <td class="p-4 text-gray-500 font-semibold"><?= e(Catalog::categoryLabel($p['category'])); ?></td>
                                <td class="p-4 font-serif font-bold text-[#4c1719]"><?= e(Catalog::formatPrice((int) $p['price_php'])); ?></td>
                                <td class="p-4 font-bold <?= (int) $p['stock'] <= 3 ? 'text-amber-700' : 'text-gray-900'; ?>"><?= (int) $p['stock']; ?></td>
                                <td class="p-4">
                                    <span class="<?= e($stockUi['class'] === 'out-of-stock' ? 'text-gray-500' : ($stockUi['class'] === 'low-stock' ? 'text-amber-700' : 'text-emerald-700')); ?> font-semibold"><?= e($stockUi['text']); ?></span>
                                </td>
                                <td class="p-4">
                                    <form action="" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= (int) $p['id']; ?>">
                                        <input type="number" name="restock_qty" min="0" max="99999" value="<?= (int) $p['stock']; ?>" class="w-16 px-2 py-1 bg-white border border-gray-300 rounded text-xs">
                                        <input type="file" name="product_image" accept="image/jpeg,image/png,image/webp" class="max-w-[160px] text-[10px]">
                                        <button type="submit" name="restock_item" class="px-3 py-1 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
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
    <script src="<?= e(kd_asset('assets/js/nav.js')); ?>" defer></script>
</body>
</html>
