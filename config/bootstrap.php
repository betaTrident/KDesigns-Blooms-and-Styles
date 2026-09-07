<?php
declare(strict_types=1);

/**
 * Application bootstrap.
 *
 * CLI: env + PDO only (no session).
 * Web: call kd_boot_http() after this file to start a hardened session.
 */

if (!defined('KD_ROOT')) {
    define('KD_ROOT', dirname(__DIR__));
}

$kdLogDir = KD_ROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
if (!is_dir($kdLogDir)) {
    mkdir($kdLogDir, 0750, true);
}

ini_set('log_errors', '1');
ini_set('error_log', $kdLogDir . DIRECTORY_SEPARATOR . 'php_errors.log');
ini_set('display_errors', '0');

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/http.php';
require_once KD_ROOT . '/src/View.php';
