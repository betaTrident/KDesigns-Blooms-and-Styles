<?php
/**
 * config/env.php — Pure PHP .env file loader
 * Loads KEY=VALUE pairs from .env into $_ENV and putenv().
 * Call load_env() once at application bootstrap.
 */

function load_env(string $path): void {
    if (!file_exists($path)) {
        // In production, fail hard — missing .env is a misconfiguration
        throw new RuntimeException('.env file not found. Copy .env.example to .env and configure.');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new RuntimeException('Failed to read .env file.');
    }

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comment lines and lines without '='
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        // Strip surrounding quotes (single or double)
        if (preg_match('/^(["\'])(.*)(\1)$/', $value, $m)) {
            $value = $m[2];
        }

        // Do not overwrite already-set environment variables
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Auto-load .env from the project root (one level above config/)
load_env(__DIR__ . '/../.env');
