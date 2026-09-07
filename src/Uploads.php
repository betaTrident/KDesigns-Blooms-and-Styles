<?php
declare(strict_types=1);

final class Uploads
{
    public const MAX_BYTES = 2_000_000;

    private const ALLOWED_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    public static function extensionForMime(string $mime): ?string
    {
        $mime = strtolower(trim($mime));
        if ($mime === '' || str_contains($mime, 'php') || str_contains($mime, 'svg') || str_contains($mime, 'html')) {
            return null;
        }

        return self::ALLOWED_MIME[$mime] ?? null;
    }

    /**
     * Store an uploaded image. Returns the stored basename only (32hex.ext).
     *
     * @param array<string,mixed> $file A $_FILES element
     */
    public static function store(array $file): string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Image upload failed.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Image upload failed.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size < 1 || $size > self::MAX_BYTES) {
            throw new RuntimeException('Image must be between 1 byte and 2 MB.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        $ext = self::extensionForMime($mime);
        if ($ext === null) {
            throw new RuntimeException('Only JPEG, PNG, and WebP images are allowed.');
        }

        $root = defined('KD_ROOT') ? KD_ROOT : dirname(__DIR__);
        $dir = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads';
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        $basename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = $dir . DIRECTORY_SEPARATOR . $basename;
        if (!move_uploaded_file($tmp, $dest)) {
            throw new RuntimeException('Could not store the uploaded image.');
        }

        return $basename;
    }
}
