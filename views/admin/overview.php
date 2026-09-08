<?php
declare(strict_types=1);

/** @var array<string,mixed> $stats */
/** @var array<string,mixed> $analytics */
/** @var list<array<string,mixed>> $inventory */
/** @var list<array<string,mixed>> $lowStock */
/** @var list<array<string,mixed>> $recentOrders */

$statusTotal = array_sum($analytics['by_status']);
$statusPct = static function (string $key) use ($analytics, $statusTotal): int {
    if ($statusTotal === 0) {
        return 0;
    }

    return (int) round(((int) $analytics['by_status'][$key] / $statusTotal) * 100);
};

$fulfillmentTotal = array_sum($analytics['by_fulfillment']);
$fulfillmentPct = static function (string $key) use ($analytics, $fulfillmentTotal): int {
    if ($fulfillmentTotal === 0) {
        return 0;
    }

    return (int) round(((int) $analytics['by_fulfillment'][$key] / $fulfillmentTotal) * 100);
};

$paymentTotal = array_sum($analytics['by_payment_method']);
$paymentPct = static function (string $key) use ($analytics, $paymentTotal): int {
    if ($paymentTotal === 0) {
        return 0;
    }

    return (int) round(((int) $analytics['by_payment_method'][$key] / $paymentTotal) * 100);
};
?>
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
        <p class="text-[11px] text-gray-400 mt-1">Active products only</p>
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

<!-- Analytics Widgets -->
<div class="space-y-4 sm:space-y-6 mb-6 sm:mb-8">
    <!-- Work Queue -->
    <section class="bg-white rounded border border-gray-200 shadow-sm p-4 sm:p-6">
        <h3 class="font-serif font-bold text-base text-gray-900 mb-4">Work Queue</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <a href="<?= e(Orders::adminOrdersUrl(['payment' => 'awaiting'])); ?>" class="block p-4 rounded border border-gray-200 hover:border-[#4c1719] hover:bg-[#f6f3eb]/50 transition-colors">
                <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">Awaiting Payment</p>
                <p class="text-2xl font-serif font-bold text-[#4c1719] mt-1"><?= (int) $analytics['awaiting_payment']; ?></p>
                <p class="text-[11px] text-[#4c1719] font-semibold mt-2">View orders →</p>
            </a>
            <a href="<?= e(Orders::adminOrdersUrl(['status' => 'pending'])); ?>" class="block p-4 rounded border border-gray-200 hover:border-amber-600 hover:bg-[#f6f3eb]/50 transition-colors">
                <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">Pending</p>
                <p class="text-2xl font-serif font-bold text-amber-700 mt-1"><?= (int) $analytics['by_status']['pending']; ?></p>
                <p class="text-[11px] text-[#4c1719] font-semibold mt-2">View orders →</p>
            </a>
            <a href="<?= e(Orders::adminOrdersUrl(['status' => 'processing'])); ?>" class="block p-4 rounded border border-gray-200 hover:border-blue-600 hover:bg-[#f6f3eb]/50 transition-colors">
                <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">Processing</p>
                <p class="text-2xl font-serif font-bold text-blue-700 mt-1"><?= (int) $analytics['by_status']['processing']; ?></p>
                <p class="text-[11px] text-[#4c1719] font-semibold mt-2">View orders →</p>
            </a>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Status Mix -->
        <section class="bg-white rounded border border-gray-200 shadow-sm p-4 sm:p-6">
            <h3 class="font-serif font-bold text-base text-gray-900 mb-4">Status Mix</h3>
            <p class="text-[11px] text-gray-500 mb-4"><?= (int) $statusTotal; ?> total orders</p>
            <div class="space-y-3">
                <?php
                $statusBars = [
                    'pending'    => ['label' => 'Pending', 'color' => 'bg-amber-500'],
                    'processing' => ['label' => 'Processing', 'color' => 'bg-blue-500'],
                    'delivered'  => ['label' => 'Delivered', 'color' => 'bg-emerald-500'],
                    'cancelled'  => ['label' => 'Cancelled', 'color' => 'bg-gray-400'],
                ];
                foreach ($statusBars as $key => $meta):
                    $count = (int) $analytics['by_status'][$key];
                    $pct = $statusPct($key);
                ?>
                    <div>
                        <div class="flex justify-between text-xs text-gray-700 mb-1">
                            <span><?= e($meta['label']); ?> (<?= $count; ?>)</span>
                            <span><?= $pct; ?>%</span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded overflow-hidden" role="img" aria-label="<?= e($meta['label']); ?> <?= $pct; ?> percent">
                            <div class="h-full <?= e($meta['color']); ?>" style="width: <?= $pct; ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Revenue Window -->
        <section class="bg-white rounded border border-gray-200 shadow-sm p-4 sm:p-6">
            <h3 class="font-serif font-bold text-base text-gray-900 mb-4">Revenue Window</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-3 rounded bg-[#f6f3eb] border border-gray-200">
                    <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">Last 7 Days</p>
                    <p class="text-xl font-serif font-bold text-[#4c1719] mt-1"><?= e(Catalog::formatPrice((int) $analytics['revenue_7d_php'])); ?></p>
                </div>
                <div class="p-3 rounded bg-[#f6f3eb] border border-gray-200">
                    <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">Last 30 Days</p>
                    <p class="text-xl font-serif font-bold text-[#4c1719] mt-1"><?= e(Catalog::formatPrice((int) $analytics['revenue_30d_php'])); ?></p>
                </div>
                <div class="p-3 rounded bg-[#f6f3eb] border border-gray-200">
                    <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">All Time</p>
                    <p class="text-xl font-serif font-bold text-[#4c1719] mt-1"><?= e(Catalog::formatPrice((int) $stats['revenue_php'])); ?></p>
                </div>
            </div>
            <p class="text-[11px] text-gray-400 mt-3">Excludes cancelled orders</p>
        </section>
    </div>

    <!-- How Customers Order -->
    <section class="bg-white rounded border border-gray-200 shadow-sm p-4 sm:p-6">
        <h3 class="font-serif font-bold text-base text-gray-900 mb-4">How Customers Order</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase mb-3">Fulfillment</p>
                <div class="space-y-3">
                    <?php
                    $fulfillmentBars = [
                        'pickup'   => ['label' => 'Pickup', 'color' => 'bg-[#4c1719]'],
                        'delivery' => ['label' => 'Delivery', 'color' => 'bg-[#8c7853]'],
                    ];
                    foreach ($fulfillmentBars as $key => $meta):
                        $count = (int) $analytics['by_fulfillment'][$key];
                        $pct = $fulfillmentPct($key);
                    ?>
                        <div>
                            <div class="flex justify-between text-xs text-gray-700 mb-1">
                                <span><?= e($meta['label']); ?> (<?= $count; ?>)</span>
                                <span><?= $pct; ?>%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded overflow-hidden" role="img" aria-label="<?= e($meta['label']); ?> <?= $pct; ?> percent">
                                <div class="h-full <?= e($meta['color']); ?>" style="width: <?= $pct; ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase mb-3">Payment Method</p>
                <div class="space-y-3">
                    <?php
                    $paymentColors = [
                        'Pay at Shop' => 'bg-[#4c1719]',
                        'GCash'       => 'bg-blue-500',
                        'BDO'         => 'bg-emerald-600',
                        'BPI'         => 'bg-amber-600',
                    ];
                    foreach (Orders::PAYMENTS as $method):
                        $count = (int) $analytics['by_payment_method'][$method];
                        $pct = $paymentPct($method);
                        $color = $paymentColors[$method] ?? 'bg-gray-500';
                    ?>
                        <div>
                            <div class="flex justify-between text-xs text-gray-700 mb-1">
                                <span><?= e($method); ?> (<?= $count; ?>)</span>
                                <span><?= $pct; ?>%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded overflow-hidden" role="img" aria-label="<?= e($method); ?> <?= $pct; ?> percent">
                                <div class="h-full <?= e($color); ?>" style="width: <?= $pct; ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Top Products -->
    <section class="bg-white rounded border border-gray-200 shadow-sm p-4 sm:p-6">
        <h3 class="font-serif font-bold text-base text-gray-900 mb-4">Top Products</h3>
        <?php if ($analytics['top_products'] === []): ?>
            <p class="text-sm text-gray-500">No sales yet.</p>
        <?php else: ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($analytics['top_products'] as $i => $product): ?>
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[#4c1719] text-white text-xs font-bold flex items-center justify-center"><?= $i + 1; ?></span>
                            <p class="font-serif font-bold text-sm text-gray-900 truncate"><?= e($product['name']); ?></p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-xs text-gray-500"><?= (int) $product['qty']; ?> sold</p>
                            <p class="font-serif font-bold text-sm text-[#4c1719]"><?= e(Catalog::formatPrice((int) $product['revenue_php'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
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
