<?php
declare(strict_types=1);

/**
 * CLI health check: PDO can reach MariaDB and the Phase 1 tables.
 * Usage: C:\xampp\php\php.exe scripts/db-health.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

require_once dirname(__DIR__) . '/config/db.php';

$ok = true;

try {
    $pdo = db();

    $one = $pdo->query('SELECT 1')->fetchColumn();
    echo 'OK SELECT 1 => ' . (string) $one . PHP_EOL;

    $database = $pdo->query('SELECT DATABASE()')->fetchColumn();
    echo 'OK DATABASE() => ' . (string) $database . PHP_EOL;
} catch (Throwable $e) {
    echo 'FAIL connection: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

$tables = ['products', 'users', 'orders', 'order_items'];

foreach ($tables as $table) {
    try {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
        echo 'OK ' . $table . ' count=' . $count . PHP_EOL;
    } catch (PDOException $e) {
        $ok = false;
        echo 'FAIL ' . $table . ': table missing or not readable.' . PHP_EOL;
    }
}

exit($ok ? 0 : 1);
