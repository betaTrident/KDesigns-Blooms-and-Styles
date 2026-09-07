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
}
