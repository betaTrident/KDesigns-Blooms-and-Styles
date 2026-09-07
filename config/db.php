<?php
declare(strict_types=1);

/**
 * config/db.php — PDO connection helper.
 * Credentials come from .env (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS).
 * This file is safe to commit; never put passwords here.
 */

if (!array_key_exists('DB_NAME', $_ENV)) {
    require_once __DIR__ . '/env.php';
}

/**
 * Application connection (includes dbname).
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $pdo = db_connect(true);
    return $pdo;
}

/**
 * Server connection without dbname — used to CREATE DATABASE.
 */
function db_server(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $pdo = db_connect(false);
    return $pdo;
}

/**
 * @param bool $withDatabase When true, DSN includes dbname (DB_NAME).
 */
function db_connect(bool $withDatabase): PDO
{
    if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
        throw new RuntimeException('PDO MySQL driver is not available.');
    }

    $host = db_required_env('DB_HOST');
    $user = db_required_env('DB_USER');
    $port = db_port();
    $pass = (string) ($_ENV['DB_PASS'] ?? '');

    db_assert_dsn_token($host, 'DB_HOST');

    $dsn = 'mysql:host=' . $host . ';port=' . $port . ';charset=utf8mb4';
    if ($withDatabase) {
        $name = db_required_env('DB_NAME');
        db_assert_ident($name, 'DB_NAME');
        $dsn .= ';dbname=' . $name;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        $pdo->exec('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');
    } catch (PDOException $e) {
        throw new RuntimeException('Could not connect to the database.');
    }

    return $pdo;
}

function db_required_env(string $key): string
{
    $value = trim((string) ($_ENV[$key] ?? ''));
    if ($value === '') {
        throw new RuntimeException(
            'Database configuration is incomplete. Set DB_HOST, DB_PORT, DB_NAME, and DB_USER in .env.'
        );
    }
    return $value;
}

function db_port(): string
{
    $raw = trim((string) ($_ENV['DB_PORT'] ?? '3306'));
    if ($raw === '') {
        $raw = '3306';
    }
    if (!ctype_digit($raw)) {
        throw new RuntimeException('Database configuration is invalid: DB_PORT.');
    }
    $port = (int) $raw;
    if ($port < 1 || $port > 65535) {
        throw new RuntimeException('Database configuration is invalid: DB_PORT.');
    }
    return (string) $port;
}

function db_assert_dsn_token(string $value, string $key): void
{
    if (strpbrk($value, ";=\0") !== false) {
        throw new RuntimeException('Database configuration is invalid: ' . $key . '.');
    }
}

function db_assert_ident(string $value, string $key): void
{
    if (preg_match('/^[A-Za-z0-9_]+$/', $value) !== 1) {
        throw new RuntimeException('Database configuration is invalid: ' . $key . '.');
    }
}
