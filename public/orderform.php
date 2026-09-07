<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';
kd_boot_http();
require_once KD_ROOT . '/src/Auth.php';
require_once KD_ROOT . '/src/Catalog.php';
require_once KD_ROOT . '/src/Orders.php';
require_once KD_ROOT . '/src/Checkout.php';

Auth::requireLogin();
check_session_timeout(1800);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    enforce_csrf();
}

$step = $_SESSION['order_step'] ?? 'form';
$product = Catalog::fromRequest();

$persistAction = (string) ($_POST['action'] ?? '');
$isPersistPost = $_SERVER['REQUEST_METHOD'] === 'POST'
    && in_array($persistAction, ['select_payment', 'finalize_ereceipt'], true);

if ($product === null || (int) $product['is_active'] !== 1) {
    kd_redirect('index.php#catalog');
}

if ($step !== 'success' && !$isPersistPost && !Catalog::inStock($product)) {
    kd_redirect('index.php#catalog');
}

$product_id = (int) $product['id'];
$product_name = $product['name'];
$product_price = (int) $product['price_php'];
$formatted_price = Catalog::formatPrice($product_price);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_delivery') {
        $receiver = trim((string) ($_POST['receiver_name'] ?? ''));
        $contact  = trim((string) ($_POST['receiver_contact'] ?? ''));
        $location = trim((string) ($_POST['delivery_location'] ?? ''));

        if ($receiver !== '') {
            $_SESSION['delivery_receiver'] = substr($receiver, 0, 100);
        }
        if ($contact !== '') {
            $_SESSION['delivery_contact'] = substr($contact, 0, 32);
        }
        if ($location !== '') {
            $_SESSION['delivery_location'] = substr($location, 0, 2000);
        }
        $_SESSION['fulfillment_method'] = 'delivery';

    } elseif ($action === 'proceed_to_payment') {
        $raw_name        = trim((string) ($_POST['name'] ?? ''));
        $raw_contact     = trim((string) ($_POST['contact'] ?? ''));
        $raw_date        = trim((string) ($_POST['date'] ?? ''));
        $raw_time        = trim((string) ($_POST['time'] ?? ''));
        $raw_fulfillment = trim((string) ($_POST['fulfillment'] ?? 'pickup'));

        if (!in_array($raw_fulfillment, Orders::FULFILLMENTS, true)) {
            $raw_fulfillment = 'pickup';
        }

        if ($raw_name === '') {
            $errors[] = 'Name is required.';
        } elseif (strlen($raw_name) > 100) {
            $errors[] = 'Name is too long.';
        }

        if ($raw_contact === '') {
            $errors[] = 'Contact number is required.';
        } elseif (strlen($raw_contact) > 32) {
            $errors[] = 'Contact number is too long.';
        }

        if ($raw_date === '') {
            $errors[] = 'Date needed is required.';
        } else {
            $parsedDate = DateTimeImmutable::createFromFormat('Y-m-d', $raw_date);
            if ($parsedDate === false || $parsedDate->format('Y-m-d') !== $raw_date) {
                $errors[] = 'Date needed must be a valid date.';
            } elseif ($raw_date < (new DateTimeImmutable('today'))->format('Y-m-d')) {
                $errors[] = 'Date needed cannot be in the past.';
            }
        }

        if ($raw_time !== '' && !in_array($raw_time, Checkout::TIMES, true)) {
            $raw_time = '10:00 AM';
        }

        if ($raw_fulfillment === 'delivery') {
            $hasDelivery = trim((string) ($_SESSION['delivery_receiver'] ?? '')) !== ''
                && trim((string) ($_SESSION['delivery_contact'] ?? '')) !== ''
                && trim((string) ($_SESSION['delivery_location'] ?? '')) !== '';
            if (!$hasDelivery) {
                $errors[] = 'Please add delivery details.';
            }
        }

        if ($errors === []) {
            $_SESSION['order_name']        = $raw_name;
            $_SESSION['order_contact']     = $raw_contact;
            $_SESSION['order_date']        = $raw_date;
            $_SESSION['order_time']        = $raw_time;
            $_SESSION['order_fulfillment'] = $raw_fulfillment;
            $_SESSION['order_step']        = 'payment';
            $step = 'payment';
        }

    } elseif ($action === 'select_payment') {
        $payment_method = (string) ($_POST['payment_method'] ?? '');

        if (!in_array($payment_method, Orders::PAYMENTS, true)) {
            $errors[] = 'Invalid payment method.';
            $_SESSION['order_step'] = 'payment';
            $step = 'payment';
        } else {
            $_SESSION['payment_method'] = $payment_method;

            if ($payment_method === 'Pay at Shop') {
                try {
                    $order = Checkout::placeFromSession($product_id, $payment_method);
                    $_SESSION['last_order_code'] = $order['public_code'];
                    Checkout::clearWizard();
                    $step = 'success';
                } catch (InvalidArgumentException | RuntimeException $e) {
                    $errors[] = $e->getMessage();
                    $_SESSION['order_step'] = 'payment';
                    $step = 'payment';
                } catch (PDOException $e) {
                    $errors[] = 'Could not place your order. Please try again.';
                    $_SESSION['order_step'] = 'payment';
                    $step = 'payment';
                }
            } else {
                $_SESSION['receipt_ref'] = 'KD' . random_int(10000000, 99999999);
                $_SESSION['order_step']  = 'ereceipt';
                $step = 'ereceipt';
            }
        }

    } elseif ($action === 'finalize_ereceipt') {
        $payment_method = (string) ($_SESSION['payment_method'] ?? '');

        if (!in_array($payment_method, Orders::PAYMENTS, true) || $payment_method === 'Pay at Shop') {
            $errors[] = 'Invalid payment method.';
            $_SESSION['order_step'] = 'ereceipt';
            $step = 'ereceipt';
        } else {
            try {
                $order = Checkout::placeFromSession($product_id, $payment_method);
                $_SESSION['last_order_code'] = $order['public_code'];
                Checkout::clearWizard();
                $step = 'success';
            } catch (InvalidArgumentException | RuntimeException $e) {
                $errors[] = $e->getMessage();
                $_SESSION['order_step'] = 'ereceipt';
                $step = 'ereceipt';
            } catch (PDOException $e) {
                $errors[] = 'Could not place your order. Please try again.';
                $_SESSION['order_step'] = 'ereceipt';
                $step = 'ereceipt';
            }
        }

    } elseif ($action === 'back_to_form') {
        $_SESSION['order_step'] = 'form';
        $step = 'form';

    } elseif ($action === 'back_to_payment') {
        $_SESSION['order_step'] = 'payment';
        $step = 'payment';
    }
}

$last_order_code = '';
if ($step === 'success') {
    $last_order_code = isset($_SESSION['last_order_code'])
        ? (string) $_SESSION['last_order_code']
        : '';
    unset($_SESSION['last_order_code']);
    if (isset($_SESSION['order_step'])) {
        $_SESSION['order_step'] = 'form';
    }
}

View::render('storefront/orderform', [
    'pageTitle'           => 'Place Order - KDesigns Blooms & Styles',
    'cssBundle'           => 'both',
    'step'                => $step,
    'errors'              => $errors,
    'product_id'          => $product_id,
    'product_name'        => $product_name,
    'formatted_price'     => $formatted_price,
    'times'               => Checkout::TIMES,
    'fulfillment_method'  => $_SESSION['order_fulfillment'] ?? $_SESSION['fulfillment_method'] ?? 'pickup',
    'has_delivery_info'   => !empty($_SESSION['delivery_location']),
    'last_order_code'     => $last_order_code,
    'csrf_token'          => generate_csrf_token(),
    'blurCatalog'         => array_slice(Catalog::allActive(), 0, 4),
]);
