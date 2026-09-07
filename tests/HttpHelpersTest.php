<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HttpHelpersTest extends TestCase
{
    public function testImageUrlRejectsTraversal(): void
    {
        $this->assertSame('/images/logo.png', kd_image_url('..'));
        $this->assertSame('/images/logo.png', kd_image_url('../etc/passwd'));
    }

    public function testImageUrlKeepsImagesPath(): void
    {
        $this->assertSame('/images/foo.JPG', kd_image_url('images/foo.JPG'));
    }
}
