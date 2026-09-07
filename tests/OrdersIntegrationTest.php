<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OrdersIntegrationTest extends TestCase
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

    public function testCreateDecrementsStockAndRollsBackWhenStockZero(): void
    {
        $this->userId = $this->insertUser();
        $productId = $this->insertProduct(2);
        $this->productIds[] = $productId;

        $order = Orders::create($this->orderInput($this->userId, $productId));
        $this->orderIds[] = (int) $order['id'];

        $this->assertSame('pending', $order['status']);
        $this->assertNull($order['payment_received_at']);
        $this->assertSame(1, $this->productStock($productId));

        $emptyId = $this->insertProduct(0);
        $this->productIds[] = $emptyId;

        try {
            Orders::create($this->orderInput($this->userId, $emptyId));
            $this->fail('Expected stock-zero create to throw.');
        } catch (RuntimeException $e) {
            $this->assertSame('This item is no longer available.', $e->getMessage());
        }

        $this->assertSame(0, $this->productStock($emptyId));
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM orders WHERE user_id = :id');
        $stmt->execute([':id' => $this->userId]);
        $this->assertSame(1, (int) $stmt->fetchColumn());
    }

    private function insertUser(): int
    {
        $email = 'p8test-' . bin2hex(random_bytes(4)) . '@kdesigns.test';

        return Auth::createCustomer('P8 Test', $email, 'P8TestPass-2026');
    }

    private function insertProduct(int $stock): int
    {
        $slug = 'p8-test-' . bin2hex(random_bytes(6));
        $stmt = $this->pdo->prepare(
            'INSERT INTO products (name, slug, category, description, price_php, image_path, stock, is_active)
             VALUES (:name, :slug, :category, :description, :price_php, :image_path, :stock, 1)'
        );
        $stmt->execute([
            ':name'        => 'P8 Test Bouquet',
            ':slug'        => $slug,
            ':category'    => 'fresh',
            ':description' => 'Phase 8 test product',
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
            'customer_name'    => 'P8 Tester',
            'customer_contact' => '09170000000',
            'date_needed'      => (new DateTimeImmutable('today'))->format('Y-m-d'),
        ];
    }

    private function productStock(int $productId): int
    {
        $stmt = $this->pdo->prepare('SELECT stock FROM products WHERE id = :id');
        $stmt->execute([':id' => $productId]);

        return (int) $stmt->fetchColumn();
    }
}
