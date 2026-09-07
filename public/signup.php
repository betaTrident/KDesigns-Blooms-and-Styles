<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';
kd_boot_http();
require_once KD_ROOT . '/src/Auth.php';

if (Auth::isLoggedIn()) {
    kd_redirect(Auth::isAdmin() ? 'admin.php' : 'index.php');
}

$errors = [];
$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    enforce_csrf();

    $password = (string) ($_POST['password'] ?? '');

    if ($name === '') {
        $errors[] = 'Full Name is required.';
    } elseif (strlen($name) > 100) {
        $errors[] = 'Full Name must be 100 characters or fewer.';
    }

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    } elseif (strlen($email) > 254) {
        $errors[] = 'Email address is too long.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one letter and one number.';
    }

    if ($errors === []) {
        try {
            $id = Auth::createCustomer($name, $email, $password);
            Auth::establishSession([
                'id'    => $id,
                'name'  => $name,
                'email' => $email,
                'role'  => 'customer',
            ]);
            kd_redirect('index.php');
        } catch (RuntimeException $ex) {
            if ($ex->getMessage() === 'That email is already registered.') {
                $errors[] = $ex->getMessage();
            } else {
                $errors[] = 'Could not create your account. Please try again.';
            }
        }
    }
}

View::render('storefront/signup', [
    'pageTitle'    => 'Create an Account - KDesigns Blooms & Styles',
    'cssBundle'    => 'app',
    'is_logged_in' => false,
    'errors'       => $errors,
    'name'         => $name,
    'email'        => $email,
]);
