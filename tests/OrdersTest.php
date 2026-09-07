<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OrdersTest extends TestCase
{
    public function testPaymentMethods(): void
    {
        $this->assertContains('Pay at Shop', Orders::PAYMENTS);
        $this->assertContains('GCash', Orders::PAYMENTS);
        $this->assertContains('BDO', Orders::PAYMENTS);
        $this->assertContains('BPI', Orders::PAYMENTS);
        $this->assertCount(4, Orders::PAYMENTS);
    }

    public function testStatusesAndNormalize(): void
    {
        foreach (['pending', 'processing', 'delivered', 'cancelled'] as $status) {
            $this->assertContains($status, Orders::STATUSES);
            $this->assertSame($status, Orders::normalizeStatus($status));
        }

        $this->assertSame('cancelled', Orders::normalizeStatus('canceled'));
        $this->assertSame('processing', Orders::normalizeStatus(' Processing '));
    }
}
