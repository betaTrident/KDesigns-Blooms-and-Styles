<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';
kd_boot_http();
require_once KD_ROOT . '/src/Auth.php';
require_once KD_ROOT . '/src/Orders.php';
require_once KD_ROOT . '/src/Catalog.php';

Auth::requireLogin();
check_session_timeout(1800);

$userId = Auth::id();
if ($userId === null) {
    kd_redirect('login.php');
}

$user_orders = [];
foreach (Orders::forUser($userId) as $dbOrder) {
    $first = $dbOrder['items'][0] ?? [];
    $createdAt = (string) ($dbOrder['created_at'] ?? '');
    $placedTs = $createdAt !== '' ? strtotime($createdAt) : false;

    $user_orders[] = [
        'id'           => (string) ($dbOrder['public_code'] ?? ''),
        'product_name' => (string) ($first['product_name_snapshot'] ?? ''),
        'image'        => kd_image_url((string) ($first['image_path_snapshot'] ?? '')),
        'price'        => Catalog::formatPrice((int) ($dbOrder['total_php'] ?? 0)),
        'fulfillment'  => Orders::fulfillmentLabel((string) ($dbOrder['fulfillment'] ?? '')),
        'status'       => Orders::statusLabel((string) ($dbOrder['status'] ?? '')),
        'badge_class'  => Orders::statusBadgeClasses((string) ($dbOrder['status'] ?? '')),
        'date_needed'  => (string) ($dbOrder['date_needed'] ?? ''),
        'time_needed'  => (string) ($dbOrder['time_needed'] ?? ''),
        'placed_date'  => $placedTs !== false ? date('M j, Y', $placedTs) : '',
    ];
}

View::render('storefront/orders', [
    'pageTitle'    => 'Order Tracker - KDesigns Blooms & Styles',
    'cssBundle'    => 'app',
    'is_logged_in' => true,
    'user_orders'  => $user_orders,
]);
