<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

$filename = (string) ($_GET['f'] ?? '');
if (preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/', $filename) !== 1) {
    http_response_code(404);
    exit;
}

$dir = KD_ROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads';
$path = $dir . DIRECTORY_SEPARATOR . $filename;
$realDir = realpath($dir);
$realFile = realpath($path);

if ($realDir === false || $realFile === false || !is_file($realFile)) {
    http_response_code(404);
    exit;
}

$prefix = strtolower($realDir . DIRECTORY_SEPARATOR);
$file = strtolower($realFile);
if (!str_starts_with($file, $prefix)) {
    http_response_code(404);
    exit;
}

$ext = strtolower(pathinfo($realFile, PATHINFO_EXTENSION));
$types = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
];

header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . (string) filesize($realFile));
header('Cache-Control: public, max-age=86400');
readfile($realFile);
exit;
