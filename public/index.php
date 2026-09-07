<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';
kd_boot_http();
require_once KD_ROOT . '/src/Auth.php';
require_once KD_ROOT . '/src/Catalog.php';

if (Auth::isLoggedIn()) {
    check_session_timeout(1800);
}

View::render('storefront/home', [
    'pageTitle'     => 'KDesigns Blooms and Styles',
    'cssBundle'     => 'storefront',
    'is_logged_in'  => Auth::isLoggedIn(),
    'user_name'     => (string) ($_SESSION['user_name'] ?? ''),
    'catalog'       => Catalog::allActive(),
]);
