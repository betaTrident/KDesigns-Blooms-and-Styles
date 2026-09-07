<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UploadsTest extends TestCase
{
    public function testExtensionForMimeAllowsImages(): void
    {
        $this->assertSame('jpg', Uploads::extensionForMime('image/jpeg'));
        $this->assertSame('png', Uploads::extensionForMime('image/png'));
        $this->assertSame('webp', Uploads::extensionForMime('image/webp'));
    }

    public function testExtensionForMimeRejectsEmptyAndDangerous(): void
    {
        $this->assertNull(Uploads::extensionForMime(''));
        $this->assertNull(Uploads::extensionForMime('application/x-httpd-php'));
        $this->assertNull(Uploads::extensionForMime('text/x-php'));
        $this->assertNull(Uploads::extensionForMime('image/svg+xml'));
        $this->assertNull(Uploads::extensionForMime('text/html'));
        $this->assertNull(Uploads::extensionForMime('application/octet-stream'));
    }
}
