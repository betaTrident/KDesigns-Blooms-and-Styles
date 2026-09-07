<?php
declare(strict_types=1);

/**
 * Web helpers. Loaded by bootstrap.php.
 * Session + CSRF helpers are only initialized from kd_boot_http().
 */

function kd_env(string $key, string $default = ''): string
{
    if (array_key_exists($key, $_ENV)) {
        return (string) $_ENV[$key];
    }

    $fromGetenv = getenv($key);
    if ($fromGetenv !== false) {
        return $fromGetenv;
    }

    return $default;
}

function kd_is_https(): bool
{
    $https = $_SERVER['HTTPS'] ?? '';
    if ($https !== '' && strtolower((string) $https) !== 'off') {
        return true;
    }

    if ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443) {
        return true;
    }

    if (kd_env('APP_TRUST_PROXY', '0') === '1') {
        $forwarded = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
        $forwarded = explode(',', $forwarded)[0];
        if (trim($forwarded) === 'https') {
            return true;
        }
    }

    return false;
}

function kd_is_production(): bool
{
    return kd_env('APP_ENV', 'local') === 'production';
}

function kd_send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    if (kd_is_production() && kd_is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    $existing = headers_list();
    $hasNosniff = false;
    foreach ($existing as $header) {
        if (stripos($header, 'X-Content-Type-Options:') === 0) {
            $hasNosniff = true;
            break;
        }
    }

    if (!$hasNosniff) {
        header('X-Content-Type-Options: nosniff');
    }
}

function kd_boot_http(): void
{
    require_once __DIR__ . '/session.php';
    require_once __DIR__ . '/security.php';
    secure_session_start();
    kd_send_security_headers();
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
 * Database values stay `images/file.JPG` or `uploads/{32hex}.ext`.
 */
function kd_image_url(?string $stored): string
{
    $path = str_replace('\\', '/', trim((string) $stored));
    $path = ltrim($path, '/');
    if ($path === '' || str_contains($path, '..')) {
        return kd_asset('images/logo.png');
    }

    if (str_starts_with($path, 'uploads/')) {
        return '/media.php?f=' . rawurlencode(basename($path));
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
