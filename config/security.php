<?php
/**
 * config/security.php — CSRF protection + brute-force rate limiting.
 * Require this file on every page that has forms or handles POST data.
 */

// ─────────────────────────────────────────────────────────────────────────────
// CSRF PROTECTION
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Generate (or retrieve) the CSRF token for the current session.
 * The token is a 64-char hex string backed by random_bytes(32).
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token against the session token.
 * Uses hash_equals() for timing-safe comparison (prevents timing attacks).
 */
function verify_csrf_token(string $submitted): bool {
    $stored = $_SESSION['csrf_token'] ?? '';
    return !empty($stored) && hash_equals($stored, $submitted);
}

/**
 * Render a hidden CSRF input field — drop this inside every <form>.
 * Usage: <?= csrf_field() ?>
 */
function csrf_field(): string {
    $token = htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Abort with 403 if CSRF token is invalid.
 * Call this at the top of every POST handler.
 */
function enforce_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Security validation failed. Please go back and try again.');
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// BRUTE-FORCE RATE LIMITING (file-based, no Redis/APCu needed)
// ─────────────────────────────────────────────────────────────────────────────

define('RATE_LIMIT_DIR',      __DIR__ . '/../storage/rate_limits/');
define('RATE_MAX_ATTEMPTS',   5);      // Max failed attempts before lockout
define('RATE_LOCKOUT_SECS',   900);    // Lockout duration: 15 minutes

/**
 * Check if an IP is currently rate-limited for a given action.
 */
function is_rate_limited(string $ip, string $action = 'login'): bool {
    _ensure_rate_limit_dir();
    $file = _rate_limit_file($ip, $action);

    if (!file_exists($file)) {
        return false;
    }

    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data)) {
        return false;
    }

    // Auto-reset if the lockout window has expired
    if ((time() - ($data['first_attempt'] ?? 0)) > RATE_LOCKOUT_SECS) {
        @unlink($file);
        return false;
    }

    return ($data['attempts'] ?? 0) >= RATE_MAX_ATTEMPTS;
}

/**
 * Record a failed login attempt for an IP.
 */
function record_login_failure(string $ip, string $action = 'login'): void {
    _ensure_rate_limit_dir();
    $file = _rate_limit_file($ip, $action);

    $data = file_exists($file)
        ? json_decode(file_get_contents($file), true)
        : ['attempts' => 0, 'first_attempt' => time()];

    if (!is_array($data)) {
        $data = ['attempts' => 0, 'first_attempt' => time()];
    }

    $data['attempts']++;
    $data['last_attempt'] = time();

    file_put_contents($file, json_encode($data), LOCK_EX);
}

/**
 * Clear rate-limit record on successful login.
 */
function record_login_success(string $ip, string $action = 'login'): void {
    $file = _rate_limit_file($ip, $action);
    if (file_exists($file)) {
        @unlink($file);
    }
}

/**
 * Return remaining lockout seconds for display in error messages.
 */
function rate_limit_remaining_seconds(string $ip, string $action = 'login'): int {
    $file = _rate_limit_file($ip, $action);
    if (!file_exists($file)) return 0;
    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data)) return 0;
    $elapsed = time() - ($data['first_attempt'] ?? time());
    return max(0, RATE_LOCKOUT_SECS - $elapsed);
}

/** @internal */
function _rate_limit_file(string $ip, string $action): string {
    return RATE_LIMIT_DIR . hash('sha256', $action . '|' . $ip) . '.json';
}

/** @internal */
function _ensure_rate_limit_dir(): void {
    if (!is_dir(RATE_LIMIT_DIR)) {
        mkdir(RATE_LIMIT_DIR, 0750, true);
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// XSS OUTPUT ESCAPING HELPER
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Safely escape a value for HTML output.
 * Usage: <?= e($untrusted_variable) ?>
 */
function e(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
