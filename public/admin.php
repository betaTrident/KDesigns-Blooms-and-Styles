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

$orderFilterRaw = [
    'status'  => $_GET['status'] ?? 'all',
    'payment' => $_GET['payment'] ?? 'all',
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (array_key_exists('filter_status', $_POST)) {
        $orderFilterRaw['status'] = (string) $_POST['filter_status'];
    }
    if (array_key_exists('filter_payment', $_POST)) {
        $orderFilterRaw['payment'] = (string) $_POST['filter_payment'];
    }
}
$orderFilters = Orders::normalizeAdminFilters($orderFilterRaw);
$ordersTabUrl = Orders::adminOrdersUrl($orderFilters);

$inventoryPer = Catalog::normalizePageSize($_POST['inventory_per'] ?? $_GET['per'] ?? Catalog::DEFAULT_PAGE_SIZE);
$inventoryPageNum = filter_var($_POST['inventory_page'] ?? $_GET['page'] ?? 1, FILTER_VALIDATE_INT);
if ($inventoryPageNum === false || $inventoryPageNum < 1) {
    $inventoryPageNum = 1;
}

$redirectInventory = static function (array $extra = [], bool $lastPage = false) use ($inventoryPageNum, $inventoryPer): void {
    $page = $inventoryPageNum;
    $per = $inventoryPer;
    if ($lastPage) {
        $total = count(Catalog::all());
        $page = max(1, (int) ceil(max($total, 1) / $per));
    }
    kd_redirect(Catalog::inventoryUrl($page, $per, $extra));
};

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

        kd_redirect($ordersTabUrl);
    }
    if (isset($_POST['confirm_payment'])) {
        $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($orderId !== false) {
            try {
                $before = Orders::findById((int) $orderId);
                $order = Orders::markPaymentReceived((int) $orderId);
                if (
                    $before !== null
                    && $before['status'] === 'pending'
                    && ($order['status'] ?? '') === 'processing'
                ) {
                    $_SESSION['admin_flash_success'] = 'Payment recorded. Status is now Processing.';
                } else {
                    $_SESSION['admin_flash_success'] = 'Payment recorded.';
                }
            } catch (InvalidArgumentException | RuntimeException $e) {
                $_SESSION['admin_flash_error'] = $e->getMessage();
            } catch (PDOException $e) {
                $_SESSION['admin_flash_error'] = 'Could not record payment. Please try again.';
            }
        }
        kd_redirect($ordersTabUrl);
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
        $redirectInventory();
    }
    if (isset($_POST['save_product'])) {
        $productId = filter_var($_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);
        $input = [
            'name'        => (string) ($_POST['name'] ?? ''),
            'category'    => (string) ($_POST['category'] ?? ''),
            'description' => $_POST['description'] ?? null,
            'price_php'   => $_POST['price_php'] ?? null,
            'stock'       => $_POST['stock'] ?? null,
            'badge'       => $_POST['badge'] ?? null,
            'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        ];

        $fileError = (int) ($_FILES['product_image']['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($fileError !== UPLOAD_ERR_NO_FILE) {
            try {
                $stored = Uploads::store($_FILES['product_image']);
                $input['image_path'] = 'uploads/' . $stored;
            } catch (InvalidArgumentException | RuntimeException $ex) {
                $_SESSION['admin_flash_error'] = $ex->getMessage();
                $redirectInventory($productId > 0 ? ['edit' => (int) $productId] : ['new' => 1]);
            }
        } elseif ($productId === false || $productId < 1) {
            $existingPath = trim((string) ($_POST['image_path'] ?? ''));
            if ($existingPath !== '') {
                $input['image_path'] = $existingPath;
            }
        }

        try {
            if ($productId !== false && $productId > 0) {
                Catalog::update((int) $productId, $input);
                $_SESSION['admin_flash_success'] = 'Product updated.';
            } else {
                Catalog::create($input);
                $_SESSION['admin_flash_success'] = 'Product created.';
            }
        } catch (InvalidArgumentException | RuntimeException $ex) {
            $_SESSION['admin_flash_error'] = $ex->getMessage();
            $redirectInventory($productId > 0 ? ['edit' => (int) $productId] : ['new' => 1]);
        } catch (PDOException $e) {
            $_SESSION['admin_flash_error'] = 'Could not save that product. Please try again.';
            $redirectInventory($productId > 0 ? ['edit' => (int) $productId] : ['new' => 1]);
        }

        $redirectInventory([], $productId === false || $productId < 1);
    }
    if (isset($_POST['set_product_active'])) {
        $pid = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $active = filter_var($_POST['is_active'] ?? null, FILTER_VALIDATE_INT);
        if ($pid !== false && ($active === 0 || $active === 1)) {
            try {
                Catalog::setActive((int) $pid, $active === 1);
                $_SESSION['admin_flash_success'] = $active === 1 ? 'Product is now visible in the shop.' : 'Product hidden from the shop.';
            } catch (RuntimeException $ex) {
                $_SESSION['admin_flash_error'] = $ex->getMessage();
            } catch (PDOException $e) {
                $_SESSION['admin_flash_error'] = 'Could not update product visibility. Please try again.';
            }
        }
        $redirectInventory();
    }
    if (isset($_POST['delete_product'])) {
        $pid = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($pid !== false) {
            try {
                Catalog::deleteIfUnused((int) $pid);
                $_SESSION['admin_flash_success'] = 'Product permanently deleted.';
            } catch (InvalidArgumentException | RuntimeException $ex) {
                $_SESSION['admin_flash_error'] = $ex->getMessage();
            } catch (PDOException $e) {
                $_SESSION['admin_flash_error'] = 'Could not delete that product. Please try again.';
            }
        }
        $redirectInventory();
    }
}

$stats = Orders::stats();
$analytics = Orders::analytics();
$allOrders = Orders::allForAdmin();
$hasActiveOrderFilters = $orderFilters['status'] !== 'all' || $orderFilters['payment'] !== 'all';
$adminOrders = ($tab === 'orders' && $hasActiveOrderFilters)
    ? Orders::allForAdmin($orderFilters)
    : $allOrders;
$buyers = Orders::buyers();
$inventory = Catalog::all();
$inventoryPagination = Catalog::paginate($inventory, $inventoryPageNum, $inventoryPer);
$lowStock = array_values(array_filter(
    $inventory,
    fn($p) => (int) $p['is_active'] === 1 && (int) $p['stock'] <= 5
));

$inventoryFormMode = null;
$editProduct = null;
if ($tab === 'inventory') {
    if (array_key_exists('new', $_GET)) {
        $inventoryFormMode = 'new';
    } elseif (array_key_exists('edit', $_GET)) {
        $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($editId !== false) {
            $editProduct = Catalog::findById((int) $editId);
            if ($editProduct !== null) {
                $inventoryFormMode = 'edit';
            }
        }
    }
}

$flashError = (string) ($_SESSION['admin_flash_error'] ?? '');
unset($_SESSION['admin_flash_error']);
$flashSuccess = (string) ($_SESSION['admin_flash_success'] ?? '');
unset($_SESSION['admin_flash_success']);

View::render('admin/dashboard', [
    'pageTitle'     => 'Admin Dashboard - KDesigns Blooms & Styles',
    'cssBundle'     => 'app',
    'tab'           => $tab,
    'stats'         => $stats,
    'analytics'     => $analytics,
    'adminOrders'   => $adminOrders,
    'buyers'        => $buyers,
    'pendingCount'  => (int) $stats['pending_count'],
    'inventory'     => $inventory,
    'lowStock'      => $lowStock,
    'lowStockCount' => count($lowStock),
    'activeCount'   => count(array_filter($inventory, fn($p) => (int) $p['is_active'] === 1)),
    'recentOrders'  => array_slice($allOrders, 0, 4),
    'flashError'    => $flashError,
    'flashSuccess'  => $flashSuccess,
    'orderFilters'  => $orderFilters,
    'ordersTabUrl'  => $ordersTabUrl,
    'inventoryFormMode' => $inventoryFormMode,
    'editProduct'       => $editProduct,
    'inventoryPagination' => $inventoryPagination,
]);
