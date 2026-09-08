<?php
declare(strict_types=1);

/**
 * One-shot HTTP-verification fixtures. Not part of the app.
 * Usage: php .superpowers/sdd/verify-fixtures.php setup|cleanup|status
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';
require_once KD_ROOT . '/src/Auth.php';
require_once KD_ROOT . '/src/Catalog.php';
require_once KD_ROOT . '/src/Orders.php';

$markerPath = __DIR__ . '/verify-fixtures.json';
$cmd = $argv[1] ?? 'status';

if ($cmd === 'cleanup') {
    $state = is_file($markerPath) ? json_decode((string) file_get_contents($markerPath), true) : null;
    if (is_array($state)) {
        $pdo = db();
        if (!empty($state['order_id'])) {
            $id = (int) $state['order_id'];
            $pdo->prepare('DELETE FROM order_items WHERE order_id = :id')->execute([':id' => $id]);
            $pdo->prepare('DELETE FROM orders WHERE id = :id')->execute([':id' => $id]);
        }
        foreach (['pay_product_id', 'crud_product_id'] as $key) {
            if (!empty($state[$key])) {
                $pid = (int) $state[$key];
                $pdo->prepare('DELETE FROM products WHERE id = :id')->execute([':id' => $pid]);
            }
        }
        if (!empty($state['user_id'])) {
            $pdo->prepare('DELETE FROM users WHERE id = :id')->execute([':id' => (int) $state['user_id']]);
        }
    }
    @unlink($markerPath);
    echo json_encode(['ok' => true, 'cleaned' => true], JSON_PRETTY_PRINT), PHP_EOL;
    exit(0);
}

if ($cmd === 'status') {
    if (!is_file($markerPath)) {
        echo json_encode(['ok' => false, 'error' => 'no fixtures'], JSON_PRETTY_PRINT), PHP_EOL;
        exit(1);
    }
    echo file_get_contents($markerPath), PHP_EOL;
    exit(0);
}

$email = 'httpverify-' . bin2hex(random_bytes(4)) . '@kdesigns.test';
$userId = Auth::createCustomer('HTTP Verify', $email, 'HttpVerify-2026');

$payProductId = Catalog::create([
    'name'        => 'HTTP Pay Bouquet ' . bin2hex(random_bytes(3)),
    'category'    => 'fresh',
    'description' => 'Verification fixture',
    'price_php'   => 1111,
    'stock'       => 5,
    'badge'       => '',
    'is_active'   => 1,
    'image_path'  => 'images/logo.png',
]);

$crudName = 'HTTP CRUD Bloom ' . bin2hex(random_bytes(3));
$crudProductId = Catalog::create([
    'name'        => $crudName,
    'category'    => 'dried',
    'description' => 'CRUD verification fixture',
    'price_php'   => 2222,
    'stock'       => 8,
    'badge'       => 'New',
    'is_active'   => 1,
    'image_path'  => 'images/logo.png',
]);

$order = Orders::create([
    'user_id'          => $userId,
    'product_id'       => $payProductId,
    'qty'              => 1,
    'fulfillment'      => 'pickup',
    'payment_method'   => 'Pay at Shop',
    'receipt_ref'      => 'HTTPVERIFY1',
    'customer_name'    => 'HTTP Verifier',
    'customer_contact' => '09170000001',
    'date_needed'      => (new DateTimeImmutable('today'))->format('Y-m-d'),
]);

$state = [
    'user_id'         => $userId,
    'pay_product_id'  => $payProductId,
    'crud_product_id' => $crudProductId,
    'crud_name'       => $crudName,
    'order_id'        => (int) $order['id'],
    'public_code'     => (string) $order['public_code'],
    'status'          => (string) $order['status'],
    'paid_at'         => $order['payment_received_at'],
];
file_put_contents($markerPath, json_encode($state, JSON_PRETTY_PRINT));
echo json_encode($state, JSON_PRETTY_PRINT), PHP_EOL;
