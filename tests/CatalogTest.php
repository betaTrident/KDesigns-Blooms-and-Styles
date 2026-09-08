<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CatalogTest extends TestCase
{
    public function testStockDisplayOutOfStock(): void
    {
        $this->assertSame(
            ['text' => 'Out of Stock', 'class' => 'out-of-stock'],
            Catalog::stockDisplay(0)
        );
    }

    public function testStockDisplayLowStock(): void
    {
        $this->assertSame(
            ['text' => 'Only 2 left', 'class' => 'low-stock'],
            Catalog::stockDisplay(2)
        );
    }

    public function testStockDisplayInStock(): void
    {
        $this->assertSame(
            ['text' => '10 in stock', 'class' => 'in-stock'],
            Catalog::stockDisplay(10)
        );
    }

    public function testNormalizePageSizeDefaultsInvalidValues(): void
    {
        require_once dirname(__DIR__) . '/src/Catalog.php';

        $this->assertSame(10, Catalog::normalizePageSize(10));
        $this->assertSame(10, Catalog::normalizePageSize('10'));
        $this->assertSame(25, Catalog::normalizePageSize(25));
        $this->assertSame(10, Catalog::normalizePageSize(7));
        $this->assertSame(10, Catalog::normalizePageSize('all'));
    }

    public function testPaginateSlicesAndClampsPage(): void
    {
        require_once dirname(__DIR__) . '/src/Catalog.php';

        $items = [];
        for ($i = 1; $i <= 12; $i++) {
            $items[] = ['id' => $i];
        }

        $first = Catalog::paginate($items, 1, 10);
        $this->assertSame(10, count($first['items']));
        $this->assertSame(1, $first['page']);
        $this->assertSame(2, $first['total_pages']);
        $this->assertSame(1, $first['from']);
        $this->assertSame(10, $first['to']);
        $this->assertSame(12, $first['total']);

        $second = Catalog::paginate($items, 2, 10);
        $this->assertSame(2, count($second['items']));
        $this->assertSame(11, $second['from']);
        $this->assertSame(12, $second['to']);

        $clamped = Catalog::paginate($items, 99, 10);
        $this->assertSame(2, $clamped['page']);
        $this->assertSame([11, 12], array_column($clamped['items'], 'id'));

        $empty = Catalog::paginate([], 3, 10);
        $this->assertSame([], $empty['items']);
        $this->assertSame(1, $empty['page']);
        $this->assertSame(1, $empty['total_pages']);
        $this->assertSame(0, $empty['from']);
        $this->assertSame(0, $empty['to']);
        $this->assertSame(0, $empty['total']);
    }

    public function testInventoryUrlOmitsDefaultQuery(): void
    {
        require_once dirname(__DIR__) . '/src/Catalog.php';

        $this->assertSame('admin.php?tab=inventory', Catalog::inventoryUrl(1, 10));
        $this->assertSame('admin.php?tab=inventory&per=25&page=3', Catalog::inventoryUrl(3, 25));
        $this->assertSame('admin.php?tab=inventory&edit=4', Catalog::inventoryUrl(1, 10, ['edit' => 4]));
    }
}
