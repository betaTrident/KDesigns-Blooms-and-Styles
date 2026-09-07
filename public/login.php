<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';
kd_boot_http();
require_once KD_ROOT . '/src/Auth.php';

if (Auth::isLoggedIn()) {
    kd_redirect(Auth::isAdmin() ? 'admin.php' : 'index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    enforce_csrf();

    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';
    $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    if (is_rate_limited($ip, 'login')) {
        $remaining = (int) ceil(rate_limit_remaining_seconds($ip, 'login') / 60);
        $error = "Too many failed attempts. Please wait {$remaining} minute(s) before trying again.";
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        record_login_failure($ip, 'login');
        $error = 'Invalid email address or password.';
    } else {
        $user = Auth::attempt($email, $password);

        if ($user !== null) {
            Auth::establishSession($user);
            record_login_success($ip, 'login');
            kd_redirect((($user['role'] ?? '') === 'admin') ? 'admin.php' : 'index.php');
        }

        record_login_failure($ip, 'login');
        $error = 'Invalid email address or password.';
    }
}

View::render('storefront/login', [
    'pageTitle'    => 'Login - KDesigns Blooms & Styles',
    'cssBundle'    => 'app',
    'is_logged_in' => false,
    'error'        => $error,
    'postedEmail'  => (string) ($_POST['email'] ?? ''),
]);
