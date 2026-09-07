<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/db.php';

final class Orders
{
    public const STATUSES = ['pending', 'processing', 'delivered', 'cancelled'];

    public const PAYMENTS = ['Pay at Shop', 'GCash', 'BDO', 'BPI'];

    public const FULFILLMENTS = ['pickup', 'delivery'];

    private const AVAILABILITY_ERROR = 'This item is no longer available.';

    /** @param array<string,mixed> $input */
    public static function create(array $input): array
    {
        $validated = self::validateCreateInput($input);
        $pdo = db();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'SELECT id, name, price_php, image_path, stock, is_active
                 FROM products
                 WHERE id = :id
                 FOR UPDATE'
            );
            $stmt->execute([':id' => $validated['product_id']]);
            $product = $stmt->fetch();

            if (
                $product === false
                || (int) $product['is_active'] !== 1
                || (int) $product['stock'] < $validated['qty']
            ) {
                $pdo->rollBack();
                throw new RuntimeException(self::AVAILABILITY_ERROR);
            }

            $unitPrice = (int) $product['price_php'];
            $totalPhp = $unitPrice * $validated['qty'];
            $orderId = self::insertOrderWithRetry($pdo, $validated, $totalPhp);

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (
                    order_id, product_id, product_name_snapshot,
                    unit_price_php_snapshot, image_path_snapshot, qty
                 ) VALUES (
                    :order_id, :product_id, :product_name_snapshot,
                    :unit_price_php_snapshot, :image_path_snapshot, :qty
                 )'
            );
            $itemStmt->execute([
                ':order_id'                 => $orderId,
                ':product_id'               => $validated['product_id'],
                ':product_name_snapshot'    => (string) $product['name'],
                ':unit_price_php_snapshot'  => $unitPrice,
                ':image_path_snapshot'      => $product['image_path'] !== null
                    ? (string) $product['image_path']
                    : null,
                ':qty'                      => $validated['qty'],
            ]);

            $stockStmt = $pdo->prepare(
                'UPDATE products
                 SET stock = stock - :qty_dec
                 WHERE id = :id AND stock >= :qty_min'
            );
            $stockStmt->execute([
                ':qty_dec' => $validated['qty'],
                ':qty_min' => $validated['qty'],
                ':id'      => $validated['product_id'],
            ]);

            if ($stockStmt->rowCount() === 0) {
                $pdo->rollBack();
                throw new RuntimeException(self::AVAILABILITY_ERROR);
            }

            $pdo->commit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }

        $order = self::findById($orderId);
        if ($order === null) {
            throw new RuntimeException('Order was created but could not be loaded.');
        }

        return $order;
    }

    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT id, public_code, user_id, fulfillment, payment_method, status,
                    date_needed, time_needed, customer_name, customer_contact,
                    delivery_receiver, delivery_contact, delivery_location,
                    total_php, created_at, updated_at
             FROM orders
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        $order = self::mapOrderRow($row);
        $items = self::loadItemsForOrders([$id]);
        $order['items'] = $items[$id] ?? [];

        return $order;
    }

    /** @return list<array<string,mixed>> */
    public static function forUser(int $userId): array
    {
        $stmt = db()->prepare(
            'SELECT id, public_code, user_id, fulfillment, payment_method, status,
                    date_needed, time_needed, customer_name, customer_contact,
                    delivery_receiver, delivery_contact, delivery_location,
                    total_php, created_at, updated_at
             FROM orders
             WHERE user_id = :user_id
             ORDER BY created_at DESC, id DESC'
        );
        $stmt->execute([':user_id' => $userId]);

        $rows = $stmt->fetchAll();
        if ($rows === []) {
            return [];
        }

        $orders = [];
        $orderIds = [];

        foreach ($rows as $row) {
            $order = self::mapOrderRow($row);
            $order['items'] = [];
            $orders[(int) $row['id']] = $order;
            $orderIds[] = (int) $row['id'];
        }

        $itemsByOrder = self::loadItemsForOrders($orderIds);
        foreach ($itemsByOrder as $orderId => $items) {
            $orders[$orderId]['items'] = $items;
        }

        return array_values($orders);
    }

    /** @return list<array<string,mixed>> */
    public static function allForAdmin(): array
    {
        $stmt = db()->query(
            'SELECT o.id, o.public_code, o.user_id, u.email AS user_email,
                    o.customer_name, o.fulfillment, o.payment_method, o.status,
                    o.date_needed, o.time_needed, o.total_php, o.created_at
             FROM orders o
             INNER JOIN users u ON u.id = o.user_id
             ORDER BY o.created_at DESC, o.id DESC'
        );

        $rows = $stmt->fetchAll();
        if ($rows === []) {
            return [];
        }

        $orders = [];
        $orderIds = [];

        foreach ($rows as $row) {
            $orderId = (int) $row['id'];
            $orders[$orderId] = [
                'id'             => $orderId,
                'public_code'    => (string) $row['public_code'],
                'user_id'        => (int) $row['user_id'],
                'user_email'     => (string) $row['user_email'],
                'customer_name'  => (string) $row['customer_name'],
                'fulfillment'    => (string) $row['fulfillment'],
                'payment_method' => (string) $row['payment_method'],
                'status'         => (string) $row['status'],
                'date_needed'    => (string) $row['date_needed'],
                'time_needed'    => $row['time_needed'] !== null
                    ? (string) $row['time_needed']
                    : null,
                'total_php'      => (int) $row['total_php'],
                'created_at'     => (string) $row['created_at'],
                'items'          => [],
            ];
            $orderIds[] = $orderId;
        }

        self::attachAdminItems($orders, $orderIds);

        return array_values($orders);
    }

    /** @param array<string,mixed> $order */
    public static function itemsLabel(array $order): string
    {
        $parts = [];

        foreach ($order['items'] ?? [] as $item) {
            $name = (string) ($item['product_name_snapshot'] ?? $item['name_snapshot'] ?? '');
            $qty = (int) ($item['qty'] ?? 1);
            $parts[] = $qty . '× ' . $name;
        }

        return implode(' · ', $parts);
    }

    public static function updateStatus(int $orderId, string $status): void
    {
        $status = self::normalizeStatus($status);

        if (!in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Invalid order status.');
        }

        $pdo = db();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'SELECT id, status
                 FROM orders
                 WHERE id = :id
                 FOR UPDATE'
            );
            $stmt->execute([':id' => $orderId]);
            $order = $stmt->fetch();

            if ($order === false) {
                $pdo->rollBack();
                throw new RuntimeException('Order not found.');
            }

            $oldStatus = (string) $order['status'];

            if ($oldStatus === $status) {
                $pdo->commit();
                return;
            }

            $itemStmt = $pdo->prepare(
                'SELECT product_id, qty
                 FROM order_items
                 WHERE order_id = :order_id'
            );
            $itemStmt->execute([':order_id' => $orderId]);
            $items = $itemStmt->fetchAll();
            self::lockProductsForItems($pdo, $items);

            if ($status === 'cancelled' && $oldStatus !== 'cancelled') {
                self::restockItems($pdo, $items);
            } elseif ($oldStatus === 'cancelled' && $status !== 'cancelled') {
                self::decrementItems($pdo, $items);
            }

            $updateStmt = $pdo->prepare(
                'UPDATE orders
                 SET status = :status
                 WHERE id = :id'
            );
            $updateStmt->execute([
                ':status' => $status,
                ':id'     => $orderId,
            ]);

            $pdo->commit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    public static function normalizeStatus(string $raw): string
    {
        $normalized = strtolower(trim($raw));

        return match ($normalized) {
            'pending'    => 'pending',
            'processing' => 'processing',
            'delivered'  => 'delivered',
            'cancelled', 'canceled' => 'cancelled',
            default      => $normalized,
        };
    }

    public static function statusLabel(string $status): string
    {
        return match (self::normalizeStatus($status)) {
            'pending'    => 'Pending',
            'processing' => 'Processing',
            'delivered'  => 'Delivered',
            'cancelled'  => 'Cancelled',
            default      => ucfirst($status),
        };
    }

    public static function statusTone(string $status): string
    {
        return match (self::normalizeStatus($status)) {
            'delivered'  => 'emerald',
            'processing' => 'blue',
            'cancelled'  => 'gray',
            default      => 'amber',
        };
    }

    public static function fulfillmentLabel(string $fulfillment): string
    {
        return match ($fulfillment) {
            'delivery' => 'Delivery',
            default    => 'Pickup',
        };
    }

    public static function statusBadgeClasses(string $status): string
    {
        return match (self::statusTone($status)) {
            'emerald' => 'bg-emerald-100 text-emerald-800',
            'blue'    => 'bg-blue-100 text-blue-800',
            'gray'    => 'bg-gray-100 text-gray-800',
            default   => 'bg-amber-100 text-amber-800',
        };
    }

    public static function statusTextClasses(string $status): string
    {
        return match (self::statusTone($status)) {
            'emerald' => 'text-emerald-600',
            'blue'    => 'text-blue-600',
            'gray'    => 'text-gray-600',
            default   => 'text-amber-600',
        };
    }

    /** @return array{revenue_php: int, order_count: int, pending_count: int, buyer_count: int, avg_order_php: int} */
    public static function stats(): array
    {
        $stmt = db()->query(
            'SELECT
                COALESCE(SUM(CASE WHEN status <> \'cancelled\' THEN total_php ELSE 0 END), 0) AS revenue_php,
                COUNT(*) AS order_count,
                SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) AS pending_count,
                COUNT(DISTINCT user_id) AS buyer_count,
                SUM(CASE WHEN status <> \'cancelled\' THEN 1 ELSE 0 END) AS active_count
             FROM orders'
        );

        $row = $stmt->fetch();
        if ($row === false) {
            return [
                'revenue_php'   => 0,
                'order_count'   => 0,
                'pending_count' => 0,
                'buyer_count'   => 0,
                'avg_order_php' => 0,
            ];
        }

        $revenuePhp = (int) $row['revenue_php'];
        $activeCount = (int) $row['active_count'];

        return [
            'revenue_php'   => $revenuePhp,
            'order_count'   => (int) $row['order_count'],
            'pending_count' => (int) $row['pending_count'],
            'buyer_count'   => (int) $row['buyer_count'],
            'avg_order_php' => $activeCount > 0 ? (int) round($revenuePhp / $activeCount) : 0,
        ];
    }

    /** @return list<array<string,mixed>> */
    public static function buyers(): array
    {
        $stmt = db()->query(
            'SELECT
                u.id AS user_id,
                u.name,
                u.email,
                COUNT(o.id) AS order_count,
                COALESCE(SUM(CASE WHEN o.status <> \'cancelled\' THEN o.total_php ELSE 0 END), 0) AS total_spent,
                MAX(DATE(o.created_at)) AS last_order_at
             FROM users u
             INNER JOIN orders o ON o.user_id = u.id
             GROUP BY u.id, u.name, u.email
             ORDER BY total_spent DESC, last_order_at DESC, u.id DESC'
        );

        $rows = $stmt->fetchAll();
        if ($rows === []) {
            return [];
        }

        $userIds = array_map(static fn (array $row): int => (int) $row['user_id'], $rows);
        $labelsByUser = self::loadBuyerItemLabels($userIds);

        $buyers = [];
        foreach ($rows as $row) {
            $userId = (int) $row['user_id'];
            $buyers[] = [
                'user_id'       => $userId,
                'name'          => (string) $row['name'],
                'email'         => (string) $row['email'],
                'order_count'   => (int) $row['order_count'],
                'total_spent'   => (int) $row['total_spent'],
                'last_order_at' => (string) $row['last_order_at'],
                'items_label'   => $labelsByUser[$userId] ?? '',
            ];
        }

        return $buyers;
    }

    public static function pendingCount(): int
    {
        $stmt = db()->query(
            'SELECT COUNT(*) AS pending_count
             FROM orders
             WHERE status = \'pending\''
        );

        $row = $stmt->fetch();

        return $row === false ? 0 : (int) $row['pending_count'];
    }

    public static function lowStockCount(int $threshold = 5): int
    {
        if ($threshold < 0) {
            throw new InvalidArgumentException('Threshold must be zero or greater.');
        }

        $stmt = db()->prepare(
            'SELECT COUNT(*) AS low_stock_count
             FROM products
             WHERE stock <= :threshold'
        );
        $stmt->execute([':threshold' => $threshold]);

        $row = $stmt->fetch();

        return $row === false ? 0 : (int) $row['low_stock_count'];
    }

    /** @param array<string,mixed> $input @return array<string,mixed> */
    private static function validateCreateInput(array $input): array
    {
        $userId = self::requirePositiveInt($input['user_id'] ?? null, 'User');
        $productId = self::requirePositiveInt($input['product_id'] ?? null, 'Product');

        $qty = 1;
        if (array_key_exists('qty', $input)) {
            $qty = self::requirePositiveInt($input['qty'], 'Quantity');
        }

        if ($qty < 1 || $qty > 99) {
            throw new InvalidArgumentException('Quantity must be between 1 and 99.');
        }

        if (!array_key_exists('fulfillment', $input)) {
            throw new InvalidArgumentException('Fulfillment is required.');
        }

        $fulfillment = trim((string) $input['fulfillment']);
        if (!in_array($fulfillment, self::FULFILLMENTS, true)) {
            throw new InvalidArgumentException('Invalid fulfillment option.');
        }

        if (!array_key_exists('payment_method', $input)) {
            throw new InvalidArgumentException('Payment method is required.');
        }

        $paymentMethod = trim((string) $input['payment_method']);
        if (!in_array($paymentMethod, self::PAYMENTS, true)) {
            throw new InvalidArgumentException('Invalid payment method.');
        }

        $customerName = self::requireNonEmptyString($input['customer_name'] ?? null, 'Customer name', 100);
        $customerContact = self::requireNonEmptyString($input['customer_contact'] ?? null, 'Customer contact', 32);

        if (!array_key_exists('date_needed', $input)) {
            throw new InvalidArgumentException('Date needed is required.');
        }

        $dateNeeded = trim((string) $input['date_needed']);
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateNeeded);
        if ($date === false || $date->format('Y-m-d') !== $dateNeeded) {
            throw new InvalidArgumentException('Date needed must be a valid date.');
        }

        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        if ($dateNeeded < $today) {
            throw new InvalidArgumentException('Date needed cannot be in the past.');
        }

        $timeNeeded = self::optionalString($input['time_needed'] ?? null, 32);
        $deliveryReceiver = self::optionalString($input['delivery_receiver'] ?? null, 100);
        $deliveryContact = self::optionalString($input['delivery_contact'] ?? null, 32);
        $deliveryLocation = self::optionalText($input['delivery_location'] ?? null, 2000);

        if ($fulfillment === 'delivery') {
            if ($deliveryReceiver === null) {
                throw new InvalidArgumentException('Delivery receiver is required.');
            }
            if ($deliveryContact === null) {
                throw new InvalidArgumentException('Delivery contact is required.');
            }
            if ($deliveryLocation === null) {
                throw new InvalidArgumentException('Delivery location is required.');
            }
        } else {
            $deliveryReceiver = null;
            $deliveryContact = null;
            $deliveryLocation = null;
        }

        return [
            'user_id'            => $userId,
            'product_id'         => $productId,
            'qty'                => $qty,
            'fulfillment'        => $fulfillment,
            'payment_method'     => $paymentMethod,
            'customer_name'      => $customerName,
            'customer_contact'   => $customerContact,
            'date_needed'        => $dateNeeded,
            'time_needed'        => $timeNeeded,
            'delivery_receiver'  => $deliveryReceiver,
            'delivery_contact'   => $deliveryContact,
            'delivery_location'  => $deliveryLocation,
        ];
    }

    /** @param array<string,mixed> $validated */
    private static function insertOrderWithRetry(PDO $pdo, array $validated, int $totalPhp): int
    {
        $insertStmt = $pdo->prepare(
            'INSERT INTO orders (
                public_code, user_id, fulfillment, payment_method, status,
                date_needed, time_needed, customer_name, customer_contact,
                delivery_receiver, delivery_contact, delivery_location, total_php
             ) VALUES (
                :public_code, :user_id, :fulfillment, :payment_method, :status,
                :date_needed, :time_needed, :customer_name, :customer_contact,
                :delivery_receiver, :delivery_contact, :delivery_location, :total_php
             )'
        );

        for ($attempt = 0; $attempt < 8; $attempt++) {
            $publicCode = 'KDB-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            try {
                $insertStmt->execute([
                    ':public_code'       => $publicCode,
                    ':user_id'           => $validated['user_id'],
                    ':fulfillment'       => $validated['fulfillment'],
                    ':payment_method'    => $validated['payment_method'],
                    ':status'            => 'pending',
                    ':date_needed'       => $validated['date_needed'],
                    ':time_needed'       => $validated['time_needed'],
                    ':customer_name'     => $validated['customer_name'],
                    ':customer_contact'  => $validated['customer_contact'],
                    ':delivery_receiver' => $validated['delivery_receiver'],
                    ':delivery_contact'  => $validated['delivery_contact'],
                    ':delivery_location' => $validated['delivery_location'],
                    ':total_php'         => $totalPhp,
                ]);

                return (int) $pdo->lastInsertId();
            } catch (PDOException $e) {
                if (self::isDuplicateKey($e)) {
                    continue;
                }

                throw $e;
            }
        }

        throw new RuntimeException('Could not generate a unique order code.');
    }

    /** @param list<int> $orderIds @return array<int, list<array<string,mixed>>> */
    private static function loadItemsForOrders(array $orderIds): array
    {
        if ($orderIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $stmt = db()->prepare(
            'SELECT id, order_id, product_id, product_name_snapshot,
                    unit_price_php_snapshot, image_path_snapshot, qty
             FROM order_items
             WHERE order_id IN (' . $placeholders . ')
             ORDER BY id ASC'
        );
        $stmt->execute($orderIds);

        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $orderId = (int) $row['order_id'];
            $grouped[$orderId][] = self::mapItemRow($row);
        }

        return $grouped;
    }

    /**
     * @param array<int, array<string,mixed>> $orders
     * @param list<int> $orderIds
     */
    private static function attachAdminItems(array &$orders, array $orderIds): void
    {
        if ($orderIds === []) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $stmt = db()->prepare(
            'SELECT order_id, product_name_snapshot, unit_price_php_snapshot,
                    image_path_snapshot, qty
             FROM order_items
             WHERE order_id IN (' . $placeholders . ')
             ORDER BY id ASC'
        );
        $stmt->execute($orderIds);

        foreach ($stmt->fetchAll() as $row) {
            $orderId = (int) $row['order_id'];
            if (!isset($orders[$orderId])) {
                continue;
            }

            $orders[$orderId]['items'][] = [
                'name_snapshot' => (string) $row['product_name_snapshot'],
                'qty'           => (int) $row['qty'],
                'unit_price'    => (int) $row['unit_price_php_snapshot'],
                'image'         => $row['image_path_snapshot'] !== null
                    ? (string) $row['image_path_snapshot']
                    : null,
            ];
        }
    }

    /** @param list<int> $userIds @return array<int, string> */
    private static function loadBuyerItemLabels(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = db()->prepare(
            'SELECT o.user_id, oi.product_name_snapshot, oi.qty
             FROM orders o
             INNER JOIN order_items oi ON oi.order_id = o.id
             WHERE o.user_id IN (' . $placeholders . ')
             ORDER BY o.created_at DESC, oi.id ASC'
        );
        $stmt->execute($userIds);

        $labels = [];
        foreach ($stmt->fetchAll() as $row) {
            $userId = (int) $row['user_id'];
            $part = (int) $row['qty'] . '× ' . (string) $row['product_name_snapshot'];

            if (!isset($labels[$userId])) {
                $labels[$userId] = $part;
                continue;
            }

            $labels[$userId] .= ' · ' . $part;
        }

        return $labels;
    }

    /** @param list<array<string,mixed>> $items */
    private static function lockProductsForItems(PDO $pdo, array $items): void
    {
        $productIds = [];
        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            if ($productId > 0) {
                $productIds[$productId] = $productId;
            }
        }

        if ($productIds === []) {
            return;
        }

        $ids = array_values($productIds);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare(
            'SELECT id FROM products WHERE id IN (' . $placeholders . ') FOR UPDATE'
        );
        $stmt->execute($ids);
    }

    /** @param list<array<string,mixed>> $items */
    private static function restockItems(PDO $pdo, array $items): void
    {
        $stmt = $pdo->prepare(
            'UPDATE products
             SET stock = stock + :qty
             WHERE id = :id'
        );

        foreach ($items as $item) {
            $stmt->execute([
                ':qty' => (int) $item['qty'],
                ':id'  => (int) $item['product_id'],
            ]);
        }
    }

    /** @param list<array<string,mixed>> $items */
    private static function decrementItems(PDO $pdo, array $items): void
    {
        $stmt = $pdo->prepare(
            'UPDATE products
             SET stock = stock - :qty_dec
             WHERE id = :id AND stock >= :qty_min'
        );

        foreach ($items as $item) {
            $qty = (int) $item['qty'];
            $stmt->execute([
                ':qty_dec' => $qty,
                ':qty_min' => $qty,
                ':id'      => (int) $item['product_id'],
            ]);

            if ($stmt->rowCount() === 0) {
                throw new RuntimeException('Not enough stock to reopen this order.');
            }
        }
    }

    private static function isDuplicateKey(PDOException $e): bool
    {
        return $e->getCode() === '23000' || (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private static function requirePositiveInt(mixed $value, string $field): int
    {
        if (is_int($value)) {
            $n = $value;
        } elseif (is_string($value) && ctype_digit($value)) {
            $n = (int) $value;
        } else {
            throw new InvalidArgumentException($field . ' is required.');
        }

        if ($n < 1) {
            throw new InvalidArgumentException($field . ' is required.');
        }

        return $n;
    }

    private static function requireNonEmptyString(mixed $value, string $field, int $maxLen): string
    {
        $str = trim((string) $value);
        if ($str === '') {
            throw new InvalidArgumentException($field . ' is required.');
        }
        if (strlen($str) > $maxLen) {
            throw new InvalidArgumentException($field . ' is too long.');
        }

        return $str;
    }

    private static function optionalString(mixed $value, int $maxLen): ?string
    {
        if ($value === null) {
            return null;
        }

        $str = trim((string) $value);
        if ($str === '') {
            return null;
        }
        if (strlen($str) > $maxLen) {
            throw new InvalidArgumentException('Field exceeds maximum length.');
        }

        return $str;
    }

    private static function optionalText(mixed $value, int $maxLen): ?string
    {
        if ($value === null) {
            return null;
        }

        $str = trim((string) $value);
        if ($str === '') {
            return null;
        }
        if (strlen($str) > $maxLen) {
            throw new InvalidArgumentException('Field exceeds maximum length.');
        }

        return $str;
    }

    /** @param array<string,mixed> $row */
    private static function mapOrderRow(array $row): array
    {
        return [
            'id'                => (int) $row['id'],
            'public_code'       => (string) $row['public_code'],
            'user_id'           => (int) $row['user_id'],
            'fulfillment'       => (string) $row['fulfillment'],
            'payment_method'    => (string) $row['payment_method'],
            'status'            => (string) $row['status'],
            'date_needed'       => (string) $row['date_needed'],
            'time_needed'       => $row['time_needed'] !== null ? (string) $row['time_needed'] : null,
            'customer_name'     => (string) $row['customer_name'],
            'customer_contact'  => (string) $row['customer_contact'],
            'delivery_receiver' => $row['delivery_receiver'] !== null
                ? (string) $row['delivery_receiver']
                : null,
            'delivery_contact'  => $row['delivery_contact'] !== null
                ? (string) $row['delivery_contact']
                : null,
            'delivery_location' => $row['delivery_location'] !== null
                ? (string) $row['delivery_location']
                : null,
            'total_php'         => (int) $row['total_php'],
            'created_at'        => (string) $row['created_at'],
            'updated_at'        => (string) $row['updated_at'],
        ];
    }

    /** @param array<string,mixed> $row */
    private static function mapItemRow(array $row): array
    {
        return [
            'id'                       => (int) $row['id'],
            'order_id'                 => (int) $row['order_id'],
            'product_id'               => (int) $row['product_id'],
            'product_name_snapshot'    => (string) $row['product_name_snapshot'],
            'unit_price_php_snapshot'  => (int) $row['unit_price_php_snapshot'],
            'image_path_snapshot'      => $row['image_path_snapshot'] !== null
                ? (string) $row['image_path_snapshot']
                : null,
            'qty'                      => (int) $row['qty'],
        ];
    }
}
