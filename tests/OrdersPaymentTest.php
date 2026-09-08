<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OrdersPaymentTest extends TestCase
{
    private PDO $pdo;

    private int $userId = 0;

    /** @var list<int> */
    private array $productIds = [];

    /** @var list<int> */
    private array $orderIds = [];

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

        try {
            $this->pdo->query('SELECT receipt_ref, payment_received_at FROM orders LIMIT 1');
        } catch (Throwable $e) {
            $this->markTestSkipped('P8 order columns are not applied yet.');
        }
    }

    protected function tearDown(): void
    {
        if (!isset($this->pdo)) {
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
            // Cleanup best-effort so leftover rows do not fail later tests.
        }
    }

    public function testMarkPaymentReceivedAdvancesPendingToProcessing(): void
    {
        $orderId = $this->createPendingOrder();
        $result = Orders::markPaymentReceived($orderId);

        $this->assertNotNull($result['payment_received_at']);
        $this->assertSame('processing', $result['status']);

        $fresh = Orders::findById($orderId);
        $this->assertNotNull($fresh);
        $this->assertNotNull($fresh['payment_received_at']);
        $this->assertSame('processing', $fresh['status']);
    }

    public function testMarkPaymentReceivedRejectsCancelledOrder(): void
    {
        $orderId = $this->createPendingOrder();
        Orders::updateStatus($orderId, 'cancelled');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cancelled orders cannot be marked paid.');

        Orders::markPaymentReceived($orderId);
    }

    public function testMarkPaymentReceivedSetsTimestampWithoutChangingProcessingStatus(): void
    {
        $orderId = $this->createPendingOrder();
        Orders::updateStatus($orderId, 'processing');

        $before = Orders::findById($orderId);
        $this->assertNotNull($before);
        $this->assertNull($before['payment_received_at']);
        $this->assertSame('processing', $before['status']);

        $result = Orders::markPaymentReceived($orderId);

        $this->assertNotNull($result['payment_received_at']);
        $this->assertSame('processing', $result['status']);
    }

    public function testMarkPaymentReceivedIsIdempotentWhenAlreadyPaid(): void
    {
        $orderId = $this->createPendingOrder();
        $first = Orders::markPaymentReceived($orderId);
        $second = Orders::markPaymentReceived($orderId);

        $this->assertSame($first['payment_received_at'], $second['payment_received_at']);
        $this->assertSame('processing', $second['status']);
    }

    private function createPendingOrder(): int
    {
        $this->userId = $this->userId > 0 ? $this->userId : $this->insertUser();
        $productId = $this->insertProduct(5);
        $this->productIds[] = $productId;

        $order = Orders::create($this->orderInput($this->userId, $productId));
        $orderId = (int) $order['id'];
        $this->orderIds[] = $orderId;

        return $orderId;
    }

    private function insertUser(): int
    {
        $email = 'paytest-' . bin2hex(random_bytes(4)) . '@kdesigns.test';

        return Auth::createCustomer('Pay Test', $email, 'PayTestPass-2026');
    }

    private function insertProduct(int $stock): int
    {
        $slug = 'pay-test-' . bin2hex(random_bytes(6));
        $stmt = $this->pdo->prepare(
            'INSERT INTO products (name, slug, category, description, price_php, image_path, stock, is_active)
             VALUES (:name, :slug, :category, :description, :price_php, :image_path, :stock, 1)'
        );
        $stmt->execute([
            ':name'        => 'Pay Test Bouquet',
            ':slug'        => $slug,
            ':category'    => 'fresh',
            ':description' => 'Payment test product',
            ':price_php'   => 1000,
            ':image_path'  => 'images/logo.png',
            ':stock'       => $stock,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return array<string,mixed> */
    private function orderInput(int $userId, int $productId): array
    {
        return [
            'user_id'          => $userId,
            'product_id'       => $productId,
            'qty'              => 1,
            'fulfillment'      => 'pickup',
            'payment_method'   => 'Pay at Shop',
            'receipt_ref'      => 'KDTESTREF1',
            'customer_name'    => 'Pay Tester',
            'customer_contact' => '09170000000',
            'date_needed'      => (new DateTimeImmutable('today'))->format('Y-m-d'),
        ];
    }
}
