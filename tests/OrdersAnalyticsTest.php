<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OrdersAnalyticsTest extends TestCase
{
    private ?PDO $pdo = null;

    /** @var list<int> */
    private array $orderIds = [];

    /** @var list<int> */
    private array $productIds = [];

    private int $userId = 0;

    protected function setUp(): void
    {
        require_once dirname(__DIR__) . '/config/bootstrap.php';
        require_once dirname(__DIR__) . '/src/Auth.php';
        require_once dirname(__DIR__) . '/src/Orders.php';

        try {
            $this->pdo = db();
            $this->pdo->query('SELECT 1');
        } catch (Throwable $e) {
            $this->markTestSkipped('Database unreachable.');
        }
    }

    protected function tearDown(): void
    {
        if ($this->pdo === null) {
            return;
        }

        try {
            foreach ($this->orderIds as $orderId) {
                $stmt = $this->pdo->prepare('DELETE FROM order_items WHERE order_id = :id');
                $stmt->execute([':id' => $orderId]);
                $stmt = $this->pdo->prepare('DELETE FROM orders WHERE id = :id');
                $stmt->execute([':id' => $orderId]);
            }
            foreach ($this->productIds as $productId) {
                $stmt = $this->pdo->prepare('DELETE FROM products WHERE id = :id');
                $stmt->execute([':id' => $productId]);
            }
            if ($this->userId > 0) {
                $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
                $stmt->execute([':id' => $this->userId]);
            }
        } catch (Throwable $e) {
            // Cleanup best-effort.
        }
    }

    public function testAnalyticsReturnsExpectedShape(): void
    {
        $analytics = Orders::analytics();

        $this->assertArrayHasKey('awaiting_payment', $analytics);
        $this->assertArrayHasKey('paid_count', $analytics);
        $this->assertArrayHasKey('by_status', $analytics);
        $this->assertArrayHasKey('revenue_7d_php', $analytics);
        $this->assertArrayHasKey('revenue_30d_php', $analytics);
        $this->assertArrayHasKey('by_fulfillment', $analytics);
        $this->assertArrayHasKey('by_payment_method', $analytics);
        $this->assertArrayHasKey('top_products', $analytics);

        $this->assertIsInt($analytics['awaiting_payment']);
        $this->assertIsInt($analytics['paid_count']);
        $this->assertIsInt($analytics['revenue_7d_php']);
        $this->assertIsInt($analytics['revenue_30d_php']);
        $this->assertGreaterThanOrEqual(0, $analytics['awaiting_payment']);
        $this->assertGreaterThanOrEqual(0, $analytics['paid_count']);
        $this->assertGreaterThanOrEqual(0, $analytics['revenue_7d_php']);
        $this->assertGreaterThanOrEqual(0, $analytics['revenue_30d_php']);

        $this->assertSame(['pending', 'processing', 'delivered', 'cancelled'], array_keys($analytics['by_status']));
        foreach ($analytics['by_status'] as $count) {
            $this->assertIsInt($count);
            $this->assertGreaterThanOrEqual(0, $count);
        }

        $this->assertSame(['pickup', 'delivery'], array_keys($analytics['by_fulfillment']));
        foreach ($analytics['by_fulfillment'] as $count) {
            $this->assertIsInt($count);
            $this->assertGreaterThanOrEqual(0, $count);
        }

        $this->assertSame(['Pay at Shop', 'GCash', 'BDO', 'BPI'], array_keys($analytics['by_payment_method']));
        foreach ($analytics['by_payment_method'] as $count) {
            $this->assertIsInt($count);
            $this->assertGreaterThanOrEqual(0, $count);
        }

        $this->assertIsArray($analytics['top_products']);
        $this->assertLessThanOrEqual(5, count($analytics['top_products']));
        foreach ($analytics['top_products'] as $row) {
            $this->assertArrayHasKey('name', $row);
            $this->assertArrayHasKey('qty', $row);
            $this->assertArrayHasKey('revenue_php', $row);
            $this->assertIsString($row['name']);
            $this->assertIsInt($row['qty']);
            $this->assertIsInt($row['revenue_php']);
            $this->assertGreaterThanOrEqual(0, $row['qty']);
            $this->assertGreaterThanOrEqual(0, $row['revenue_php']);
        }
    }

    public function testAnalyticsSeparatesAwaitingAndPaidExcludingCancelled(): void
    {
        $before = Orders::analytics();

        $this->createOrder('pending', null);
        $this->createOrder('processing', date('Y-m-d H:i:s'));
        $this->createOrder('cancelled', null);

        $analytics = Orders::analytics();

        $this->assertSame($before['awaiting_payment'] + 1, $analytics['awaiting_payment']);
        $this->assertSame($before['paid_count'] + 1, $analytics['paid_count']);
        $this->assertSame($before['by_status']['cancelled'] + 1, $analytics['by_status']['cancelled']);
    }

    private function createOrder(string $status, ?string $paymentReceivedAt): int
    {
        $this->userId = $this->userId > 0 ? $this->userId : Auth::createCustomer(
            'Analytics Test',
            'analytics-' . bin2hex(random_bytes(4)) . '@kdesigns.test',
            'AnalyticsTest-2026'
        );

        $slug = 'analytics-' . bin2hex(random_bytes(6));
        $stmt = $this->pdo->prepare(
            'INSERT INTO products (name, slug, category, description, price_php, image_path, stock, is_active)
             VALUES (:name, :slug, :category, :description, :price_php, :image_path, :stock, 1)'
        );
        $stmt->execute([
            ':name'        => 'Analytics Bouquet',
            ':slug'        => $slug,
            ':category'    => 'fresh',
            ':description' => 'Analytics test product',
            ':price_php'   => 1500,
            ':image_path'  => 'images/logo.png',
            ':stock'       => 10,
        ]);
        $productId = (int) $this->pdo->lastInsertId();
        $this->productIds[] = $productId;

        $order = Orders::create([
            'user_id'          => $this->userId,
            'product_id'       => $productId,
            'qty'              => 1,
            'fulfillment'      => 'pickup',
            'payment_method'   => 'GCash',
            'receipt_ref'      => 'KDTESTAN1',
            'customer_name'    => 'Analytics Tester',
            'customer_contact' => '09171111111',
            'date_needed'      => (new DateTimeImmutable('today'))->format('Y-m-d'),
        ]);
        $orderId = (int) $order['id'];
        $this->orderIds[] = $orderId;

        if ($status !== 'pending') {
            Orders::updateStatus($orderId, $status);
        }

        if ($paymentReceivedAt !== null) {
            $payStmt = $this->pdo->prepare(
                'UPDATE orders SET payment_received_at = :paid_at WHERE id = :id'
            );
            $payStmt->execute([':paid_at' => $paymentReceivedAt, ':id' => $orderId]);
        }

        return $orderId;
    }
}
