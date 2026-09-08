<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OrdersAdminFiltersTest extends TestCase
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
            // Unit tests below do not require DB.
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

    public function testNormalizeAdminFiltersDefaultsToAll(): void
    {
        $this->assertSame(
            ['status' => 'all', 'payment' => 'all'],
            Orders::normalizeAdminFilters([])
        );
    }

    public function testNormalizeAdminFiltersAcceptsValidValues(): void
    {
        $this->assertSame(
            ['status' => 'pending', 'payment' => 'awaiting'],
            Orders::normalizeAdminFilters(['status' => 'pending', 'payment' => 'awaiting'])
        );

        $this->assertSame(
            ['status' => 'delivered', 'payment' => 'recorded'],
            Orders::normalizeAdminFilters(['status' => ' Delivered ', 'payment' => ' RECORDED '])
        );
    }

    public function testNormalizeAdminFiltersRejectsInvalidValues(): void
    {
        $this->assertSame(
            ['status' => 'all', 'payment' => 'all'],
            Orders::normalizeAdminFilters(['status' => 'shipped', 'payment' => 'paid'])
        );
    }

    public function testAdminOrdersUrlOmitsDefaultFilters(): void
    {
        $this->assertSame('admin.php?tab=orders', Orders::adminOrdersUrl([]));
    }

    public function testAdminOrdersUrlIncludesActiveFilters(): void
    {
        $this->assertSame(
            'admin.php?tab=orders&status=processing&payment=awaiting',
            Orders::adminOrdersUrl(['status' => 'processing', 'payment' => 'awaiting'])
        );
    }

    public function testAwaitingPaymentFilterExcludesCancelledAndMatchesAnalytics(): void
    {
        if ($this->pdo === null) {
            $this->markTestSkipped('Database unreachable.');
        }

        $beforeAnalytics = Orders::analytics();
        $beforeAwaiting = Orders::allForAdmin(['payment' => 'awaiting']);

        $pendingId = $this->createFilterTestOrder('pending', null);
        $cancelledId = $this->createFilterTestOrder('cancelled', null);

        $awaitingOrders = Orders::allForAdmin(['payment' => 'awaiting']);
        $awaitingIds = array_map(static fn (array $o): int => (int) $o['id'], $awaitingOrders);

        $this->assertContains($pendingId, $awaitingIds);
        $this->assertNotContains($cancelledId, $awaitingIds);

        $analytics = Orders::analytics();
        $this->assertSame(
            $beforeAnalytics['awaiting_payment'] + 1,
            $analytics['awaiting_payment']
        );
        $this->assertSame(
            count($beforeAwaiting) + 1,
            count($awaitingOrders)
        );
    }

    private function createFilterTestOrder(string $status, ?string $paymentReceivedAt): int
    {
        $this->userId = $this->userId > 0 ? $this->userId : Auth::createCustomer(
            'Filter Test',
            'filter-' . bin2hex(random_bytes(4)) . '@kdesigns.test',
            'FilterTest-2026'
        );

        $slug = 'filter-' . bin2hex(random_bytes(6));
        $stmt = $this->pdo->prepare(
            'INSERT INTO products (name, slug, category, description, price_php, image_path, stock, is_active)
             VALUES (:name, :slug, :category, :description, :price_php, :image_path, :stock, 1)'
        );
        $stmt->execute([
            ':name'        => 'Filter Bouquet',
            ':slug'        => $slug,
            ':category'    => 'fresh',
            ':description' => 'Filter test product',
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
            'receipt_ref'      => 'KDFILT1',
            'customer_name'    => 'Filter Tester',
            'customer_contact' => '09172222222',
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
