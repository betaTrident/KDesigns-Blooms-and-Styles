<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';
kd_boot_http();
require_once KD_ROOT . '/src/Auth.php';
require_once KD_ROOT . '/src/Catalog.php';
require_once KD_ROOT . '/src/Orders.php';
require_once KD_ROOT . '/src/Uploads.php';

Auth::requireAdmin();
check_session_timeout(1800);

$tab = $_GET['tab'] ?? 'overview';
$allowed_tabs = ['overview', 'orders', 'inventory', 'buyers'];
if (!in_array($tab, $allowed_tabs, true)) {
    $tab = 'overview';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    enforce_csrf();
    if (isset($_POST['update_order_status'])) {
        $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $newStatus = Orders::normalizeStatus((string) ($_POST['new_status'] ?? ''));

        if ($orderId !== false && in_array($newStatus, Orders::STATUSES, true)) {
            try {
                Orders::updateStatus((int) $orderId, $newStatus);
            } catch (InvalidArgumentException | RuntimeException $e) {
                $_SESSION['admin_flash_error'] = $e->getMessage();
            } catch (PDOException $e) {
                $_SESSION['admin_flash_error'] = 'Could not update that order. Please try again.';
            }
        }

        kd_redirect('admin.php?tab=orders');
    }
    if (isset($_POST['confirm_payment'])) {
        $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($orderId !== false) {
            try {
                Orders::markPaymentReceived((int) $orderId);
            } catch (InvalidArgumentException | RuntimeException $e) {
                $_SESSION['admin_flash_error'] = $e->getMessage();
            } catch (PDOException $e) {
                $_SESSION['admin_flash_error'] = 'Could not record payment. Please try again.';
            }
        }
        kd_redirect('admin.php?tab=orders');
    }
    if (isset($_POST['restock_item'])) {
        $pid = filter_var($_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);
        $qty = filter_var($_POST['restock_qty'] ?? -1, FILTER_VALIDATE_INT);
        if ($pid !== false && $qty !== false && $qty >= 0 && $qty <= 99999) {
            try {
                Catalog::updateStock((int) $pid, (int) $qty);
            } catch (InvalidArgumentException | RuntimeException $ex) {
                // Invalid product or quantity — inventory page still reloads
            }
            $fileError = (int) ($_FILES['product_image']['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($fileError !== UPLOAD_ERR_NO_FILE) {
                try {
                    $stored = Uploads::store($_FILES['product_image']);
                    Catalog::updateImage((int) $pid, 'uploads/' . $stored);
                } catch (InvalidArgumentException | RuntimeException $ex) {
                    $_SESSION['admin_flash_error'] = $ex->getMessage();
                }
            }
        }
        kd_redirect('admin.php?tab=inventory');
    }
}

$stats = Orders::stats();
$adminOrders = Orders::allForAdmin();
$buyers = Orders::buyers();
$inventory = Catalog::all();
$lowStock = array_values(array_filter($inventory, fn($p) => (int) $p['stock'] <= 5));
$flashError = (string) ($_SESSION['admin_flash_error'] ?? '');
unset($_SESSION['admin_flash_error']);

View::render('admin/dashboard', [
    'pageTitle'     => 'Admin Dashboard - KDesigns Blooms & Styles',
    'cssBundle'     => 'app',
    'tab'           => $tab,
    'stats'         => $stats,
    'adminOrders'   => $adminOrders,
    'buyers'        => $buyers,
    'pendingCount'  => (int) $stats['pending_count'],
    'inventory'     => $inventory,
    'lowStock'      => $lowStock,
    'lowStockCount' => count($lowStock),
    'activeCount'   => count(array_filter($inventory, fn($p) => (int) $p['is_active'] === 1)),
    'recentOrders'  => array_slice($adminOrders, 0, 4),
    'flashError'    => $flashError,
]);
