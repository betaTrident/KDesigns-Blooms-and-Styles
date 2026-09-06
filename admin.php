<?php
session_start();

// Handle Admin Login Check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if ($email === 'admin@kdesigns.ph' && $password === 'kdesigns2026') {
            $_SESSION['admin_logged_in'] = true;
            header('Location: admin.php');
            exit;
        } else {
            $login_error = "Invalid admin credentials. Use admin@kdesigns.ph / kdesigns2026";
        }
    }
    
    // Render Login Screen if not authenticated
    ?>
  <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Admin Login - KDesigns Blooms & Styles</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    </head>
    <body class="relative h-screen w-full bg-cover bg-center font-sans flex items-center justify-center overflow-hidden" style="background-image: url('images/IMG_8620.JPG');">

        <!-- Dark transparent overlay -->
        <div class="absolute inset-0 bg-black/60 z-0"></div>

        <!-- Wrapper Container for Logo + Login Card -->
        <div class="relative z-10 flex flex-col items-center max-w-md w-full px-4">
            
            <!-- Logo Outside on Top -->
            <img src="images/logo.png" alt="KDesigns Blooms and Style" class="h-14 mb-6 object-contain drop-shadow-md">

            <!-- Login Card Container -->
            <div class="w-full bg-[#f6f3eb] p-8 rounded shadow-2xl border border-gray-300">
                <div class="text-center mb-6">
                    <p class="text-[10px] tracking-widest font-semibold text-gray-500 uppercase">ADMIN ACCESS</p>
                    <h2 class="text-2xl font-serif font-bold text-gray-900 mt-1">KDesigns Dashboard</h2>
                </div>

                <?php if(isset($login_error)): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 text-xs rounded text-center"><?= $login_error; ?></div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 uppercase mb-1">Admin Email</label>
                        <input type="email" name="email" value="admin@kdesigns.ph" required class="w-full px-4 py-3 bg-[#efeadf] border border-[#e1d9cc] rounded text-sm text-gray-800 focus:outline-none focus:border-[#4c1719]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 uppercase mb-1">Password</label>
                        <input type="password" name="password" value="kdesigns2026" required class="w-full px-4 py-3 bg-[#efeadf] border border-[#e1d9cc] rounded text-sm text-gray-800 focus:outline-none focus:border-[#4c1719]">
                    </div>
                    <button type="submit" name="admin_login" class="w-full bg-[#4c1719] text-white font-bold py-3.5 rounded text-[11px] tracking-widest uppercase hover:bg-opacity-90 transition">Access Dashboard</button>
                </form>

                <div class="mt-6 text-center">
                    <a href="index.php" class="text-xs text-gray-600 hover:underline">← Back to Storefront</a>
                </div>
            </div>

        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    header('Location: admin.php');
    exit;
}

// Active Tab Handling
$tab = $_GET['tab'] ?? 'overview';

// Mock Data Store for Demo Dashboard
if (!isset($_SESSION['admin_orders'])) {
    $_SESSION['admin_orders'] = [
        ['id' => 'KDB-2026-001', 'customer' => 'Maja & Ron Santos', 'email' => 'maja.ron@gmail.com', 'items' => '2× Crimson Romance Bouquet', 'total' => '₱3,700', 'status' => 'Delivered', 'date' => '2026-08-28'],
        ['id' => 'KDB-2026-002', 'customer' => 'Angel Siglos', 'email' => 'angel.siglos@yahoo.com', 'items' => '1× Pastel Peony Bouquet<br>2× Lavender Dreams Bundle', 'total' => '₱4,160', 'status' => 'Processing', 'date' => '2026-09-01'],
        ['id' => 'KDB-2026-003', 'customer' => 'The Mango Ranch', 'email' => 'events@themangoranch.ph', 'items' => '6× Garden Table Centerpiece<br>2× Tropical Bloom Arrangement', 'total' => '₱23,200', 'status' => 'Pending', 'date' => '2026-09-03'],
        ['id' => 'KDB-2026-004', 'customer' => 'Claire Ann Ross', 'email' => 'claireann@gmail.com', 'items' => '1× Bridal White Cascade', 'total' => '₱4,800', 'status' => 'Processing', 'date' => '2026-09-02'],
    ];
}

if (!isset($_SESSION['admin_inventory'])) {
    $_SESSION['admin_inventory'] = [
        ['name' => 'Crimson Romance Bouquet', 'category' => 'FRESH BOUQUET', 'price' => '₱1,850', 'stock' => 12, 'badge' => 'Bestseller', 'img' => 'images/IMG_8603.JPG'],
        ['name' => 'Pastel Peony Bouquet', 'category' => 'FRESH BOUQUET', 'price' => '₱2,200', 'stock' => 5, 'badge' => 'Limited', 'img' => 'images/IMG_8597.JPG'],
        ['name' => 'Sunflower & Wildflower Mix', 'category' => 'FRESH BOUQUET', 'price' => '₱1,200', 'stock' => 20, 'badge' => '', 'img' => 'images/IMG_8630.JPG'],
        ['name' => 'Orchid Elegance Vase', 'category' => 'FRESH BOUQUET', 'price' => '₱3,500', 'stock' => 3, 'badge' => 'Luxury', 'img' => 'images/IMG_8618.JPG'],
        ['name' => 'Garden Table Centerpiece', 'category' => 'DRIED BOUQUET', 'price' => '₱2,800', 'stock' => 8, 'badge' => '', 'img' => 'images/IMG_8586.JPG'],
        ['name' => 'Lavender Dreams Bundle', 'category' => 'DRIED BOUQUET', 'price' => '₱980', 'stock' => 15, 'badge' => '', 'img' => 'images/IMG_8587.JPG'],
        ['name' => 'Tropical Bloom Arrangement', 'category' => 'DRIED BOUQUET', 'price' => '₱3,200', 'stock' => 4, 'badge' => 'New', 'img' => 'images/IMG_8564.JPG'],
        ['name' => 'Bridal White Cascade', 'category' => 'DRIED BOUQUET', 'price' => '₱4,800', 'stock' => 2, 'badge' => 'Luxury', 'img' => 'images/IMG_8607.JPG'],
    ];
}

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $oid = $_POST['order_id'];
    $nstatus = $_POST['new_status'];
    foreach($_SESSION['admin_orders'] as &$ord) {
        if($ord['id'] === $oid) { $ord['status'] = $nstatus; }
    }
    header('Location: admin.php?tab=orders');
    exit;
}

// Handle Inventory Restock Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restock_item'])) {
    $index = $_POST['item_index'];
    $new_qty = intval($_POST['restock_qty']);
    $_SESSION['admin_inventory'][$index]['stock'] = $new_qty;
    header('Location: admin.php?tab=inventory');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KDesigns Blooms & Styles</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        kdesigns: {
                            cream: '#f6f3eb',
                            burgundy: '#4c1719',
                            sidebar: '#361012',
                            inputBg: '#efeadf',
                            inputBorder: '#e1d9cc',
                            textMuted: '#7c766b'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#f6f3eb] font-sans flex h-screen overflow-hidden">

    <!-- LEFT SIDEBAR -->
    <aside class="w-64 bg-[#361012] text-white flex flex-col justify-between flex-shrink-0">
        <div>
            <!-- Sidebar Header -->
            <div class="px-6 py-6 border-b border-white/10">
                <p class="text-[9px] tracking-[0.2em] font-semibold text-white/50 uppercase mb-1">ADMIN PANEL</p>
                <h1 class="font-serif text-lg font-bold text-white">KDesigns</h1>
                <p class="text-[11px] text-white/70 italic">Blooms & Styles</p>
            </div>

            <!-- Sidebar Navigation Links -->
            <nav class="p-4 space-y-1 text-xs">
                <a href="admin.php?tab=overview" class="flex items-center justify-between px-4 py-3 rounded transition <?= $tab === 'overview' ? 'bg-[#4c1719] font-bold text-white' : 'text-white/80 hover:bg-white/5'; ?>">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-chart-pie text-sm"></i> Overview</span>
                </a>
                <a href="admin.php?tab=orders" class="flex items-center justify-between px-4 py-3 rounded transition <?= $tab === 'orders' ? 'bg-[#4c1719] font-bold text-white' : 'text-white/80 hover:bg-white/5'; ?>">
                    <span class="flex items-center gap-3"><i class="fa-regular fa-file-lines text-sm"></i> Orders</span>
                    <span class="px-2 py-0.5 bg-[#4c1719] border border-white/20 text-white rounded-full text-[10px]">1</span>
                </a>
                <a href="admin.php?tab=inventory" class="flex items-center justify-between px-4 py-3 rounded transition <?= $tab === 'inventory' ? 'bg-[#4c1719] font-bold text-white' : 'text-white/80 hover:bg-white/5'; ?>">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-box-archive text-sm"></i> Inventory</span>
                    <span class="px-2 py-0.5 bg-[#4c1719] border border-white/20 text-white rounded-full text-[10px]">3</span>
                </a>
                <a href="admin.php?tab=buyers" class="flex items-center justify-between px-4 py-3 rounded transition <?= $tab === 'buyers' ? 'bg-[#4c1719] font-bold text-white' : 'text-white/80 hover:bg-white/5'; ?>">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-users text-sm"></i> Buyers History</span>
                </a>
            </nav>
        </div>

        <!-- Back to Site Footer Button -->
        <div class="p-4 border-t border-white/10">
            <a href="index.php" class="flex items-center justify-center gap-2 py-3 px-4 bg-white/5 hover:bg-white/10 rounded text-xs text-white/90 border border-white/10 transition uppercase tracking-wider font-semibold">
                ← Back to Site
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 overflow-y-auto bg-[#f6f3eb] p-10">
        
        <!-- Top Bar Date Display -->
        <div class="flex justify-end mb-6">
            <div class="bg-white px-4 py-2 rounded border border-gray-200 text-xs text-gray-700 shadow-sm font-medium">
                Sun, September 6, 2026
            </div>
        </div>

        <!-- TAB 1: OVERVIEW -->
        <?php if ($tab === 'overview'): ?>
            <div class="mb-8">
                <p class="text-[10px] tracking-widest font-semibold text-gray-400 uppercase">OVERVIEW</p>
                <h2 class="text-3xl font-serif font-bold text-gray-900 mt-0.5">Dashboard Overview</h2>
            </div>

            <!-- Top Metric Cards Grid -->
            <div class="grid grid-cols-5 gap-4 mb-8">
                <div class="bg-white p-5 rounded border-l-4 border-[#4c1719] shadow-sm">
                    <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">TOTAL REVENUE</p>
                    <h3 class="text-2xl font-serif font-bold text-[#4c1719] mt-2">₱35,860</h3>
                    <p class="text-[11px] text-gray-400 mt-1">Excl. cancelled</p>
                </div>
                <div class="bg-white p-5 rounded border-l-4 border-emerald-600 shadow-sm">
                    <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">TOTAL ORDERS</p>
                    <h3 class="text-2xl font-serif font-bold text-gray-900 mt-2">4</h3>
                    <p class="text-[11px] text-gray-400 mt-1">1 pending</p>
                </div>
                <div class="bg-white p-5 rounded border-l-4 border-amber-600 shadow-sm">
                    <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">LOW STOCK</p>
                    <h3 class="text-2xl font-serif font-bold text-amber-700 mt-2">3</h3>
                    <p class="text-[11px] text-gray-400 mt-1">Need restocking</p>
                </div>
                <div class="bg-white p-5 rounded border-l-4 border-blue-600 shadow-sm">
                    <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">ACTIVE PRODUCTS</p>
                    <h3 class="text-2xl font-serif font-bold text-blue-900 mt-2">11</h3>
                    <p class="text-[11px] text-gray-400 mt-1">of 12 total</p>
                </div>
                <div class="bg-white p-5 rounded border-l-4 border-[#8c7853] shadow-sm">
                    <p class="text-[10px] tracking-wider font-bold text-gray-500 uppercase">TOTAL BUYERS</p>
                    <h3 class="text-2xl font-serif font-bold text-gray-900 mt-2">4</h3>
                    <p class="text-[11px] text-gray-400 mt-1">Unique customers</p>
                </div>
            </div>

            <!-- Bottom Split Panels: Recent Orders & Stock Alerts -->
            <div class="grid grid-cols-3 gap-8">
                <!-- Recent Orders Panel -->
                <div class="col-span-2 bg-white rounded border border-gray-200 shadow-sm p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-serif font-bold text-base text-gray-900">Recent Orders</h3>
                        <a href="admin.php?tab=orders" class="text-xs text-[#4c1719] font-semibold hover:underline">View all →</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php foreach($_SESSION['admin_orders'] as $ord): ?>
                            <div class="py-4 flex justify-between items-center">
                                <div>
                                    <h4 class="font-bold text-sm text-gray-900"><?= $ord['customer']; ?></h4>
                                    <p class="text-xs text-gray-400"><?= $ord['id']; ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-serif font-bold text-sm text-[#4c1719]"><?= $ord['total']; ?></p>
                                    <span class="text-[11px] font-semibold text-<?= $ord['status'] === 'Delivered' ? 'emerald' : ($ord['status'] === 'Processing' ? 'blue' : 'amber'); ?>-600"><?= $ord['status']; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Stock Alerts Panel -->
                <div class="bg-white rounded border border-gray-200 shadow-sm p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-serif font-bold text-base text-gray-900">Stock Alerts</h3>
                        <a href="admin.php?tab=inventory" class="text-xs text-[#4c1719] font-semibold hover:underline">Manage →</a>
                    </div>
                    <div class="space-y-4">
                        <?php foreach($_SESSION['admin_inventory'] as $inv): if($inv['stock'] <= 5): ?>
                            <div class="flex items-center justify-between gap-3 pb-3 border-b border-gray-100">
                                <img src="<?= $inv['img']; ?>" class="w-10 h-10 object-cover rounded border">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-serif font-bold text-xs text-gray-900 truncate"><?= $inv['name']; ?></h4>
                                    <p class="text-[10px] text-gray-400 uppercase"><?= $inv['category']; ?></p>
                                </div>
                                <span class="text-xs font-bold text-amber-700"><?= $inv['stock']; ?> left</span>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
            </div>

        <!-- TAB 2: ORDERS -->
        <?php elseif ($tab === 'orders'): ?>
            <div class="mb-6">
                <p class="text-[10px] tracking-widest font-semibold text-gray-400 uppercase">ORDERS</p>
                <h2 class="text-3xl font-serif font-bold text-gray-900 mt-0.5">Order Management</h2>
            </div>

            <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#fbf9f5] border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="p-4">Order ID</th>
                            <th class="p-4">Customer</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Items</th>
                            <th class="p-4">Total</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Update Status</th>
                            <th class="p-4">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <?php foreach($_SESSION['admin_orders'] as $ord): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 font-bold text-gray-900"><?= $ord['id']; ?></td>
                                <td class="p-4 font-bold text-gray-900"><?= $ord['customer']; ?></td>
                                <td class="p-4 text-gray-500"><?= $ord['email']; ?></td>
                                <td class="p-4"><?= $ord['items']; ?></td>
                                <td class="p-4 font-serif font-bold text-[#4c1719]"><?= $ord['total']; ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 bg-<?= $ord['status'] === 'Delivered' ? 'emerald' : ($ord['status'] === 'Processing' ? 'blue' : 'amber'); ?>-100 text-<?= $ord['status'] === 'Delivered' ? 'emerald' : ($ord['status'] === 'Processing' ? 'blue' : 'amber'); ?>-800 font-bold text-[10px] uppercase rounded">
                                        <?= $ord['status']; ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <form action="" method="POST" class="flex gap-2">
                                        <input type="hidden" name="order_id" value="<?= $ord['id']; ?>">
                                        <select name="new_status" class="px-2 py-1 bg-white border border-gray-300 rounded text-xs">
                                            <option value="Pending" <?= $ord['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Processing" <?= $ord['status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Delivered" <?= $ord['status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="Cancelled" <?= $ord['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_order_status" class="px-2 py-1 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase">Save</button>
                                    </form>
                                </td>
                                <td class="p-4 text-gray-500"><?= $ord['date']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <!-- TAB 3: INVENTORY -->
        <?php elseif ($tab === 'inventory'): ?>
            <div class="mb-6">
                <p class="text-[10px] tracking-widest font-semibold text-gray-400 uppercase">INVENTORY</p>
                <h2 class="text-3xl font-serif font-bold text-gray-900 mt-0.5">Inventory System</h2>
            </div>

            <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#fbf9f5] border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="p-4">Product</th>
                            <th class="p-4">Category</th>
                            <th class="p-4">Price</th>
                            <th class="p-4">Current Stock</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Restock Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <?php foreach($_SESSION['admin_inventory'] as $idx => $inv): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 flex items-center gap-3">
                                    <img src="<?= $inv['img']; ?>" class="w-10 h-10 object-cover rounded border">
                                    <span class="font-serif font-bold text-gray-900"><?= $inv['name']; ?></span>
                                </td>
                                <td class="p-4 text-gray-500 font-semibold"><?= $inv['category']; ?></td>
                                <td class="p-4 font-serif font-bold text-[#4c1719]"><?= $inv['price']; ?></td>
                                <td class="p-4 font-bold <?= $inv['stock'] <= 3 ? 'text-amber-700' : 'text-gray-900'; ?>"><?= $inv['stock']; ?></td>
                                <td class="p-4">
                                    <span class="text-emerald-700 font-semibold"><?= $inv['stock']; ?> in stock</span>
                                </td>
                                <td class="p-4">
                                    <form action="" method="POST" class="flex gap-2">
                                        <input type="hidden" name="item_index" value="<?= $idx; ?>">
                                        <input type="number" name="restock_qty" value="<?= $inv['stock']; ?>" class="w-16 px-2 py-1 bg-white border border-gray-300 rounded text-xs">
                                        <button type="submit" name="restock_item" class="px-3 py-1 bg-[#4c1719] text-white rounded text-[10px] font-bold uppercase">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <!-- TAB 4: BUYERS HISTORY -->
        <?php elseif ($tab === 'buyers'): ?>
            <div class="mb-6">
                <p class="text-[10px] tracking-widest font-semibold text-gray-400 uppercase">BUYERS HISTORY</p>
                <h2 class="text-3xl font-serif font-bold text-gray-900 mt-0.5">Buyers History</h2>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white p-5 rounded border-l-4 border-[#4c1719] shadow-sm">
                    <p class="text-[10px] font-bold text-gray-500 uppercase">TOTAL BUYERS</p>
                    <h3 class="text-2xl font-serif font-bold text-gray-900 mt-1">4</h3>
                </div>
                <div class="bg-white p-5 rounded border-l-4 border-emerald-600 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-500 uppercase">TOTAL REVENUE</p>
                    <h3 class="text-2xl font-serif font-bold text-[#4c1719] mt-1">₱35,860</h3>
                </div>
                <div class="bg-white p-5 rounded border-l-4 border-blue-600 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-500 uppercase">AVG. ORDER VALUE</p>
                    <h3 class="text-2xl font-serif font-bold text-blue-900 mt-1">₱8,965</h3>
                </div>
            </div>

            <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#fbf9f5] border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="p-4">#</th>
                            <th class="p-4">Customer</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Orders</th>
                            <th class="p-4">Items Purchased</th>
                            <th class="p-4">Total Spent</th>
                            <th class="p-4">Last Order</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 font-bold">⭐</td>
                            <td class="p-4 font-bold text-gray-900">The Mango Ranch</td>
                            <td class="p-4 text-gray-500">events@themangoranch.ph</td>
                            <td class="p-4">1</td>
                            <td class="p-4">6× Garden Table Centerpiece<br>2× Tropical Bloom Arrangement</td>
                            <td class="p-4 font-serif font-bold text-[#4c1719]">₱23,200</td>
                            <td class="p-4 text-gray-500">2026-09-03</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 font-bold">2</td>
                            <td class="p-4 font-bold text-gray-900">Claire Ann Ross</td>
                            <td class="p-4 text-gray-500">claireann@gmail.com</td>
                            <td class="p-4">1</td>
                            <td class="p-4">1× Bridal White Cascade</td>
                            <td class="p-4 font-serif font-bold text-[#4c1719]">₱4,800</td>
                            <td class="p-4 text-gray-500">2026-09-02</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 font-bold">3</td>
                            <td class="p-4 font-bold text-gray-900">Angel Siglos</td>
                            <td class="p-4 text-gray-500">angel.siglos@yahoo.com</td>
                            <td class="p-4">1</td>
                            <td class="p-4">1× Pastel Peony Bouquet<br>2× Lavender Dreams Bundle</td>
                            <td class="p-4 font-serif font-bold text-[#4c1719]">₱4,160</td>
                            <td class="p-4 text-gray-500">2026-09-01</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 font-bold">4</td>
                            <td class="p-4 font-bold text-gray-900">Maja & Ron Santos</td>
                            <td class="p-4 text-gray-500">maja.ron@gmail.com</td>
                            <td class="p-4">1</td>
                            <td class="p-4">2× Crimson Romance Bouquet</td>
                            <td class="p-4 font-serif font-bold text-[#4c1719]">₱3,700</td>
                            <td class="p-4 text-gray-500">2026-08-28</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </main>
</body>
</html>