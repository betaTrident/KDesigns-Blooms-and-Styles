<?php
declare(strict_types=1);

/**
 * CLI installer: create database, apply schema.sql, then database/seeds.sql.
 * Usage: C:\xampp\php\php.exe scripts/setup-database.php
 *
 * Refresh the seed file from live data:
 *   C:\xampp\php\php.exe scripts/export-seeds.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

require_once dirname(__DIR__) . '/config/db.php';

const SEED_HASH_OPTIONS = [
    'memory_cost' => 65536,
    'time_cost'   => 4,
    'threads'     => 1,
];

try {
    $dbName = db_required_env('DB_NAME');
    db_assert_ident($dbName, 'DB_NAME');

    $server = db_server();
    $server->exec(
        'CREATE DATABASE IF NOT EXISTS `' . $dbName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
    echo 'OK CREATE DATABASE `' . $dbName . '`' . PHP_EOL;

    $pdo = db();
    $databaseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR;

    apply_sql_file($pdo, $databaseDir . 'schema.sql', true);
    apply_sql_file($pdo, $databaseDir . 'seeds.sql', false);
    maybe_update_seed_passwords($pdo);

    echo 'OK users count=' . table_count($pdo, 'users') . PHP_EOL;
    echo 'OK products count=' . table_count($pdo, 'products') . PHP_EOL;
    echo 'OK orders count=' . table_count($pdo, 'orders') . PHP_EOL;
    echo 'OK order_items count=' . table_count($pdo, 'order_items') . PHP_EOL;
    echo 'OK setup complete' . PHP_EOL;
    exit(0);
} catch (Throwable $e) {
    echo 'FAIL ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

function apply_sql_file(PDO $pdo, string $path, bool $skipCreateAndUse): void
{
    $label = basename($path);
    if (!is_readable($path)) {
        throw new RuntimeException($label . ' is missing or unreadable.');
    }

    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException('Failed to read ' . $label . '.');
    }
    if (str_starts_with($sql, "\xEF\xBB\xBF")) {
        $sql = substr($sql, 3);
    }

    $statements = sql_split_statements($sql);
    $executed = 0;
    foreach ($statements as $index => $statement) {
        if ($skipCreateAndUse && preg_match('/^(CREATE\s+DATABASE|USE)\b/i', $statement) === 1) {
            continue;
        }
        try {
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            $sqlState = (string) $e->getCode();
            throw new RuntimeException(
                $label . ' statement ' . ($index + 1) . ' failed'
                . ($sqlState !== '' ? ' (SQLSTATE ' . $sqlState . ')' : '')
                . '.'
            );
        }
    }
    echo 'OK ' . $label . ' (' . $executed . ' statements)' . PHP_EOL;
}

/**
 * Split SQL into statements. Strips block and -- comments; does not split on `;` inside quotes.
 *
 * @return list<string>
 */
function sql_split_statements(string $sql): array
{
    $statements = [];
    $buffer = '';
    $len = strlen($sql);
    $i = 0;
    $state = 'code';

    while ($i < $len) {
        $ch = $sql[$i];
        $next = ($i + 1 < $len) ? $sql[$i + 1] : '';

        if ($state === 'code') {
            if ($ch === '-' && $next === '-') {
                $state = 'line_comment';
                $i += 2;
                continue;
            }
            if ($ch === '/' && $next === '*') {
                $state = 'block_comment';
                $i += 2;
                continue;
            }
            if ($ch === "'") {
                $state = 'single';
                $buffer .= $ch;
                $i++;
                continue;
            }
            if ($ch === '"') {
                $state = 'double';
                $buffer .= $ch;
                $i++;
                continue;
            }
            if ($ch === '`') {
                $state = 'backtick';
                $buffer .= $ch;
                $i++;
                continue;
            }
            if ($ch === ';') {
                $stmt = trim($buffer);
                if ($stmt !== '') {
                    $statements[] = $stmt;
                }
                $buffer = '';
                $i++;
                continue;
            }
            $buffer .= $ch;
            $i++;
            continue;
        }

        if ($state === 'line_comment') {
            if ($ch === "\n") {
                $state = 'code';
                $buffer .= $ch;
            }
            $i++;
            continue;
        }

        if ($state === 'block_comment') {
            if ($ch === '*' && $next === '/') {
                $state = 'code';
                $i += 2;
                continue;
            }
            $i++;
            continue;
        }

        $buffer .= $ch;
        if ($ch === '\\' && ($state === 'single' || $state === 'double') && $i + 1 < $len) {
            $buffer .= $sql[$i + 1];
            $i += 2;
            continue;
        }
        if ($state === 'single' && $ch === "'" && $next === "'") {
            $buffer .= $next;
            $i += 2;
            continue;
        }
        if ($state === 'double' && $ch === '"' && $next === '"') {
            $buffer .= $next;
            $i += 2;
            continue;
        }
        if ($state === 'backtick' && $ch === '`' && $next === '`') {
            $buffer .= $next;
            $i += 2;
            continue;
        }
        if ($state === 'single' && $ch === "'") {
            $state = 'code';
        } elseif ($state === 'double' && $ch === '"') {
            $state = 'code';
        } elseif ($state === 'backtick' && $ch === '`') {
            $state = 'code';
        }
        $i++;
    }

    $stmt = trim($buffer);
    if ($stmt !== '') {
        $statements[] = $stmt;
    }

    return $statements;
}

function maybe_update_seed_passwords(PDO $pdo): void
{
    update_seed_password_if_set(
        $pdo,
        trim((string) ($_ENV['ADMIN_EMAIL'] ?? 'admin@kdesigns.ph')),
        (string) ($_ENV['SEED_ADMIN_PASSWORD'] ?? ''),
        'SEED_ADMIN_PASSWORD'
    );
    update_seed_password_if_set(
        $pdo,
        trim((string) ($_ENV['CUSTOMER_EMAIL'] ?? 'maja@kdesigns.ph')),
        (string) ($_ENV['SEED_CUSTOMER_PASSWORD'] ?? ''),
        'SEED_CUSTOMER_PASSWORD'
    );
}

function update_seed_password_if_set(PDO $pdo, string $email, string $plainPassword, string $envKey): void
{
    if ($plainPassword === '') {
        echo 'OK skip password update (' . $envKey . ' not set; using seeds.sql hash)' . PHP_EOL;
        return;
    }
    if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        throw new RuntimeException('Invalid seed email for ' . $envKey . '.');
    }

    $hash = password_hash($plainPassword, PASSWORD_ARGON2ID, SEED_HASH_OPTIONS);
    if ($hash === false) {
        throw new RuntimeException('Failed to hash seed password.');
    }

    $stmt = $pdo->prepare(
        'UPDATE users SET password_hash = :password_hash WHERE email = :email'
    );
    $stmt->execute([
        ':password_hash' => $hash,
        ':email'         => $email,
    ]);

    if ($stmt->rowCount() === 0 && user_missing($pdo, $email)) {
        throw new RuntimeException('Cannot update password; user ' . $email . ' is not in seeds.sql.');
    }

    echo 'OK updated password for ' . $email . PHP_EOL;
}

function user_missing(PDO $pdo, string $email): bool
{
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);

    return $stmt->fetchColumn() === false;
}

function table_count(PDO $pdo, string $table): int
{
    $allowed = ['users', 'products', 'orders', 'order_items'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Unknown table.');
    }
    return (int) $pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
}
