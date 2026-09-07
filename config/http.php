<?php
declare(strict_types=1);

/**
 * Web helpers. Loaded by bootstrap.php.
 * Session + CSRF helpers are only initialized from kd_boot_http().
 */

function kd_boot_http(): void
{
    require_once __DIR__ . '/session.php';
    require_once __DIR__ . '/security.php';
    secure_session_start();
}

function kd_redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

/**
 * Public URL for a file under DocumentRoot (public/).
 * Rejects path traversal.
 */
function kd_asset(string $path): string
{
    $path = str_replace('\\', '/', trim($path));
    $path = ltrim($path, '/');
    if ($path === '' || str_contains($path, '..')) {
        return '/';
    }

    return '/' . $path;
}

/**
 * Public URL for a stored image path (products.image_path, snapshots).
 * Database values stay `images/file.JPG`; files live in public/images/.
 */
function kd_image_url(?string $stored): string
{
    $path = str_replace('\\', '/', trim((string) $stored));
    $path = ltrim($path, '/');
    if ($path === '' || str_contains($path, '..')) {
        return kd_asset('images/logo.png');
    }

    if (str_starts_with($path, 'assets/images/')) {
        return kd_asset($path);
    }

    if (str_starts_with($path, 'images/')) {
        return kd_asset($path);
    }

    return kd_asset('images/' . $path);
}

/** Escape each ` · `-separated segment and join with <br>. */
function kd_items_html(string $label): string
{
    if ($label === '') {
        return '';
    }

    return implode('<br>', array_map('e', explode(' · ', $label)));
}
