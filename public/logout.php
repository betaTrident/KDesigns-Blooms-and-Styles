<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';
kd_boot_http();
require_once KD_ROOT . '/src/Auth.php';

if (!Auth::isLoggedIn()) {
    kd_redirect('login.php');
}

check_session_timeout(1800);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    enforce_csrf();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
    kd_redirect('index.php');
}

View::render('storefront/logout', [
    'pageTitle'    => 'Log Out - KDesigns Blooms & Styles',
    'cssBundle'    => 'app',
    'is_logged_in' => true,
    'user_name'    => (string) ($_SESSION['user_name'] ?? ''),
]);
