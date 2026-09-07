<?php
/**
 * config/session.php — Centralized secure session bootstrap.
 * Require this file at the top of every PHP page instead of
 * calling session_start() directly.
 */

function secure_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return; // Already started — do nothing
    }

    // Detect whether we're on HTTPS
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || (($_SERVER['SERVER_PORT'] ?? 80) == 443);

    session_start([
        'cookie_lifetime' => 0,           // Expire on browser close
        'cookie_path'     => '/',
        'cookie_domain'   => '',
        'cookie_secure'   => $is_https,   // HTTPS-only when on SSL
        'cookie_httponly' => true,         // Deny JS access — blocks XSS theft
        'cookie_samesite' => 'Strict',    // Block cross-origin requests — CSRF mitigation
        'use_strict_mode' => true,         // Reject uninitialized session IDs (fixation defence)
        'use_only_cookies'=> true,         // No session IDs in URLs
        'gc_maxlifetime'  => 3600,         // Server-side session TTL: 1 hour
    ]);
}

/**
 * Check if the authenticated session has expired (idle timeout).
 * Call this on every protected page after secure_session_start().
 *
 * @param int $timeout Idle timeout in seconds (default 30 minutes)
 */
function check_session_timeout(int $timeout = 1800): void {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        // Session expired — destroy it cleanly
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        header('Location: login.php?reason=timeout');
        exit;
    }
    // Refresh timestamp on every request
    $_SESSION['last_activity'] = time();
}
