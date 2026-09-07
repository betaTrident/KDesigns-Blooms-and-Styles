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

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/http.php';
require_once KD_ROOT . '/src/View.php';
