<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CatalogCrudTest extends TestCase
{
    private PDO $pdo;

    /** @var list<int> */
    private array $productIds = [];

    /** @var list<int> */
    private array $orderIds = [];

    private int $userId = 0;

    protected function setUp(): void
    {
        require_once dirname(__DIR__) . '/config/bootstrap.php';
        require_once dirname(__DIR__) . '/src/Auth.php';
        require_once dirname(__DIR__) . '/src/Catalog.php';
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
            // Cleanup best-effort.
        }
    }

    public function testNormalizeSlugLowercasesAndHyphenates(): void
    {
        $this->assertSame('rose-bouquet', Catalog::normalizeSlug('Rose Bouquet'));
    }

    public function testUniqueSlugAppendsSuffixOnCollision(): void
    {
        $base = 'collision-test-' . bin2hex(random_bytes(4));
        $slug1 = Catalog::normalizeSlug($base);
        $id1 = $this->insertRawProduct($base, $slug1);
        $this->productIds[] = $id1;

        $unique = Catalog::uniqueSlug($slug1);
        $this->assertSame($slug1 . '-2', $unique);
    }

    public function testCreateThenAllActiveContainsProduct(): void
    {
        $name = 'CRUD Active ' . bin2hex(random_bytes(4));
        $id = Catalog::create($this->validInput(['name' => $name]));
        $this->productIds[] = $id;

        $activeIds = array_map(fn(array $p): int => (int) $p['id'], Catalog::allActive());
        $this->assertContains($id, $activeIds);

        $product = Catalog::findById($id);
        $this->assertNotNull($product);
        $this->assertSame($name, $product['name']);
        $this->assertSame(1, $product['is_active']);
    }

    public function testCreateWithoutImagePathThrows(): void
    {
        $input = $this->validInput();
        unset($input['image_path']);

        $this->expectException(InvalidArgumentException::class);
        Catalog::create($input);
    }

    public function testSetActiveFalseRemovesFromAllActiveAndTrueRestores(): void
    {
        $id = Catalog::create($this->validInput());
        $this->productIds[] = $id;

        Catalog::setActive($id, false);
        $activeIds = array_map(fn(array $p): int => (int) $p['id'], Catalog::allActive());
        $this->assertNotContains($id, $activeIds);

        Catalog::setActive($id, true);
        $activeIds = array_map(fn(array $p): int => (int) $p['id'], Catalog::allActive());
        $this->assertContains($id, $activeIds);
    }

    public function testDeleteIfUnusedThrowsWhenOrderItemsExist(): void
    {
        $productId = Catalog::create($this->validInput());
        $this->productIds[] = $productId;
        $orderId = $this->createOrderForProduct($productId);
        $this->orderIds[] = $orderId;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This product is on past orders. Hide it instead.');

        Catalog::deleteIfUnused($productId);
    }

    public function testHardDeleteSucceedsWhenUnused(): void
    {
        $id = Catalog::create($this->validInput());
        $this->productIds[] = $id;

        Catalog::deleteIfUnused($id);
        $this->productIds = array_values(array_filter(
            $this->productIds,
            fn(int $pid): bool => $pid !== $id
        ));

        $this->assertNull(Catalog::findById($id));
        $allIds = array_map(fn(array $p): int => (int) $p['id'], Catalog::all());
        $this->assertNotContains($id, $allIds);
    }

    public function testHasOrderItemsReturnsTrueWhenOrdered(): void
    {
        $productId = Catalog::create($this->validInput());
        $this->productIds[] = $productId;
        $orderId = $this->createOrderForProduct($productId);
        $this->orderIds[] = $orderId;

        $this->assertTrue(Catalog::hasOrderItems($productId));
    }

    /** @param array<string,mixed> $overrides */
    private function validInput(array $overrides = []): array
    {
        return array_merge([
            'name'        => 'CRUD Test ' . bin2hex(random_bytes(4)),
            'category'    => 'fresh',
            'description' => 'Test description',
            'price_php'   => 1500,
            'stock'       => 10,
            'badge'       => 'New',
            'is_active'   => 1,
            'image_path'  => 'images/logo.png',
        ], $overrides);
    }

    private function insertRawProduct(string $name, string $slug): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO products (name, slug, category, description, price_php, image_path, stock, is_active)
             VALUES (:name, :slug, :category, :description, :price_php, :image_path, :stock, 1)'
        );
        $stmt->execute([
            ':name'        => $name,
            ':slug'        => $slug,
            ':category'    => 'fresh',
            ':description' => null,
            ':price_php'   => 1000,
            ':image_path'  => 'images/logo.png',
            ':stock'       => 5,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function createOrderForProduct(int $productId): int
    {
        $this->userId = $this->userId > 0 ? $this->userId : Auth::createCustomer(
            'CRUD Test',
            'crud-' . bin2hex(random_bytes(4)) . '@kdesigns.test',
            'CrudTestPass-2026'
        );

        $order = Orders::create([
            'user_id'          => $this->userId,
            'product_id'       => $productId,
            'qty'              => 1,
            'fulfillment'      => 'pickup',
            'payment_method'   => 'Pay at Shop',
            'receipt_ref'      => 'KDCRUDREF1',
            'customer_name'    => 'CRUD Tester',
            'customer_contact' => '09170000001',
            'date_needed'      => (new DateTimeImmutable('today'))->format('Y-m-d'),
        ]);

        return (int) $order['id'];
    }
}
