<?php
declare(strict_types=1);
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/security.php';

secure_session_start();

// ── AUTHENTICATION GUARD ─────────────────────────────────────────────────────
// Only logged-in users may access this page. No redirect leaks — generic 403.
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

// ── SESSION IDLE TIMEOUT ─────────────────────────────────────────────────────
check_session_timeout(1800); // 30-minute idle timeout

$user_orders = $_SESSION['user_orders'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracker - KDesigns Blooms &amp; Styles</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        kdesigns: {
                            cream: '#f6f3eb',
                            burgundy: '#4c1719',
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
<body class="relative h-screen w-full bg-cover bg-center font-sans overflow-hidden flex justify-end" style="background-image: url('images/IMG_8620.JPG');">

    <!-- Dark transparent overlay -->
    <div class="absolute inset-0 bg-black/60 z-0"></div>

    <!-- Side Drawer Modal -->
    <div class="relative z-10 w-full max-w-[420px] h-full bg-kdesigns-cream shadow-2xl flex flex-col border-l border-gray-300">
        
        <!-- Header -->
        <div class="bg-kdesigns-burgundy px-6 py-5 flex items-center justify-between flex-shrink-0">
            <div>
                <p class="text-[9px] tracking-[0.15em] font-semibold text-white/70 uppercase">MY ORDERS</p>
                <h3 class="text-xl font-serif text-white font-bold">Order Tracker</h3>
            </div>
            <a href="index.php" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-white/20 transition" aria-label="Close order tracker">
                <i class="fa-solid fa-xmark text-sm"></i>
            </a>
        </div>

        <!-- Content Body -->
        <div class="flex-1 p-6 overflow-y-auto">
            <?php if (empty($user_orders)): ?>
                <div class="h-full flex flex-col items-center justify-center text-center">
                    <div class="text-gray-400 mb-3 text-3xl">
                        <i class="fa-regular fa-clipboard"></i>
                    </div>
                    <h4 class="text-base font-bold text-gray-800 mb-1">No orders yet</h4>
                    <p class="text-xs text-kdesigns-textMuted">Your placed orders will appear here.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($user_orders as $order): ?>
                        <div class="bg-white border border-gray-200 rounded-md shadow-sm overflow-hidden">
                            <div class="p-4 flex gap-4 items-center">
                                <img src="<?= e($order['image']); ?>" alt="Product" class="w-16 h-16 object-cover rounded-sm border border-gray-100 flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <h4 class="font-serif font-bold text-sm text-gray-900 truncate"><?= e($order['product_name']); ?></h4>
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold text-[9px] tracking-wider uppercase rounded">
                                            <?= e($order['status']); ?>
                                        </span>
                                    </div>
                                    <p class="font-serif font-bold text-sm text-gray-900 mb-1"><?= e($order['price']); ?></p>
                                    <p class="text-[11px] text-gray-500"><?= e($order['fulfillment']); ?> · <?= e($order['date_needed']); ?> - <?= e($order['time_needed']); ?></p>
                                </div>
                            </div>
                            <div class="px-4 py-2.5 bg-[#fbf9f5] border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
                                <span class="font-medium text-gray-700">Order #<?= e($order['id']); ?></span>
                                <span>Placed <?= e($order['placed_date']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer Help Icon -->
        <div class="p-4 border-t border-kdesigns-inputBorder flex justify-end flex-shrink-0">
            <button class="w-8 h-8 rounded-full bg-gray-200 text-gray-700 flex items-center justify-center text-xs font-bold hover:bg-gray-300 transition" aria-label="Help">
                ?
            </button>
        </div>
    </div>
</body>
</html>