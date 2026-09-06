<?php
session_start();

// Check if the customer is logged in; if not, redirect to login page
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

// Capture product details passed from the catalog URL
$product_name = $_GET['product'] ?? 'Pastel Peony Bouquet';
$product_price = $_GET['price'] ?? '2200';
$formatted_price = '₱' . number_format((float)$product_price);

$errors = [];
$step = $_SESSION['order_step'] ?? 'form'; // 'form', 'payment', 'ereceipt', 'success'

// Initialize user orders session array if it doesn't exist
if (!isset($_SESSION['user_orders'])) {
    $_SESSION['user_orders'] = [];
}

// Handle form submissions across steps
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_delivery') {
        $_SESSION['delivery_receiver'] = trim($_POST['receiver_name'] ?? '');
        $_SESSION['delivery_contact'] = trim($_POST['receiver_contact'] ?? '');
        $_SESSION['delivery_location'] = trim($_POST['delivery_location'] ?? '');
        $_SESSION['fulfillment_method'] = 'delivery';
    } 
    elseif ($action === 'proceed_to_payment') {
        $_SESSION['order_name'] = trim($_POST['name'] ?? '');
        $_SESSION['order_contact'] = trim($_POST['contact'] ?? '');
        $_SESSION['order_date'] = trim($_POST['date'] ?? '');
        $_SESSION['order_time'] = trim($_POST['time'] ?? '');
        $_SESSION['order_fulfillment'] = trim($_POST['fulfillment'] ?? 'pickup');

        if (empty($_SESSION['order_name'])) { $errors[] = "Name is required."; }
        if (empty($_SESSION['order_contact'])) { $errors[] = "Contact number is required."; }
        if (empty($_SESSION['order_date'])) { $errors[] = "Date needed is required."; }

        if (empty($errors)) {
            $_SESSION['order_step'] = 'payment';
            $step = 'payment';
        }
    }
    elseif ($action === 'select_payment') {
        $payment_method = $_POST['payment_method'] ?? 'Pay at Shop';
        $_SESSION['payment_method'] = $payment_method;

        // Image mapping for order tracker
        $img_map = [
            'Crimson Romance Bouquet' => 'images/IMG_8603.JPG',
            'Pastel Peony Bouquet' => 'images/IMG_8597.JPG',
            'Sunflower & Wildflower Mix' => 'images/IMG_8630.JPG',
            'Orchid Elegance Vase' => 'images/IMG_8618.JPG',
            'Garden Table Centerpiece' => 'images/IMG_8586.JPG',
            'Lavender Dreams Bundle' => 'images/IMG_8587.JPG',
            'Tropical Bloom Arrangement' => 'images/IMG_8564.JPG',
            'Bridal White Cascade' => 'images/IMG_8607.JPG',
            'Bloom Arrangement No. 10' => 'images/IMG_8635.JPG'
        ];
        $product_img = $img_map[$product_name] ?? 'images/IMG_8597.JPG';

        if ($payment_method === 'Pay at Shop') {
            // Finalize order directly and show success popup
            $new_order = [
                'id' => 'KDB-' . rand(200000, 299999),
                'product_name' => $product_name,
                'price' => $formatted_price,
                'fulfillment' => ucfirst($_SESSION['order_fulfillment'] ?? 'pickup'),
                'date_needed' => $_SESSION['order_date'] ?? '',
                'time_needed' => $_SESSION['order_time'] ?? '',
                'image' => $product_img,
                'status' => 'PENDING',
                'placed_date' => date('M j, Y')
            ];
            array_unshift($_SESSION['user_orders'], $new_order);
            $_SESSION['order_step'] = 'success';
            $step = 'success';
        } else {
            // Show E-Receipt for online payments (GCash, BDO, BPI)
            $_SESSION['receipt_ref'] = 'KD' . rand(10000000, 99999999);
            $_SESSION['order_step'] = 'ereceipt';
            $step = 'ereceipt';
        }
    }
    elseif ($action === 'finalize_ereceipt') {
        $img_map = [
            'Crimson Romance Bouquet' => 'images/IMG_8603.JPG',
            'Pastel Peony Bouquet' => 'images/IMG_8597.JPG',
            'Sunflower & Wildflower Mix' => 'images/IMG_8630.JPG',
            'Orchid Elegance Vase' => 'images/IMG_8618.JPG',
            'Garden Table Centerpiece' => 'images/IMG_8586.JPG',
            'Lavender Dreams Bundle' => 'images/IMG_8587.JPG',
            'Tropical Bloom Arrangement' => 'images/IMG_8564.JPG',
            'Bridal White Cascade' => 'images/IMG_8607.JPG',
            'Bloom Arrangement No. 10' => 'images/IMG_8635.JPG'
        ];
        $product_img = $img_map[$product_name] ?? 'images/IMG_8597.JPG';

        $new_order = [
            'id' => 'KDB-' . rand(200000, 299999),
            'product_name' => $product_name,
            'price' => $formatted_price,
            'fulfillment' => ucfirst($_SESSION['order_fulfillment'] ?? 'pickup'),
            'date_needed' => $_SESSION['order_date'] ?? '',
            'time_needed' => $_SESSION['order_time'] ?? '',
            'image' => $product_img,
            'status' => 'PENDING',
            'placed_date' => date('M j, Y')
        ];
        array_unshift($_SESSION['user_orders'], $new_order);
        $_SESSION['order_step'] = 'success';
        $step = 'success';
    }
    elseif ($action === 'back_to_form') {
        $_SESSION['order_step'] = 'form';
        $step = 'form';
    }
    elseif ($action === 'back_to_payment') {
        $_SESSION['order_step'] = 'payment';
        $step = 'payment';
    }
}

$fulfillment_method = $_SESSION['order_fulfillment'] ?? $_SESSION['fulfillment_method'] ?? 'pickup';
$has_delivery_info = !empty($_SESSION['delivery_location']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order - KDesigns Blooms & Styles</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
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
<body class="relative min-h-screen w-full bg-[#f4efe8] font-sans overflow-y-auto">

    <!-- Scrollable Background Catalog Content -->
    <div class="filter blur-[2px] opacity-85 transition-all py-16">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-end mb-10">
                <div>
                    <span class="subtitle text-[#b85d75]">WHAT WE CREATE</span>
                    <h2 class="text-3xl font-serif text-[#2c2c2c]">Flowers Catalog</h2>
                </div>
            </div>
            
            <div class="grid grid-cols-4 gap-6 mb-16">
                <div class="bg-white rounded p-4 shadow-sm">
                    <div class="h-48 bg-gray-200 mb-3 overflow-hidden"><img src="images/IMG_8603.JPG" class="w-full h-full object-cover"></div>
                    <p class="text-[10px] text-[#b85d75] font-bold uppercase mb-1">Fresh Bouquet</p>
                    <h4 class="font-serif font-bold text-sm text-[#2c2c2c]">Crimson Romance Bouquet</h4>
                    <p class="text-xs text-gray-500 mt-2 font-serif font-bold">₱1,850</p>
                </div>
                <div class="bg-white rounded p-4 shadow-sm border-2 border-kdesigns-burgundy">
                    <div class="h-48 bg-gray-200 mb-3 overflow-hidden"><img src="images/IMG_8597.JPG" class="w-full h-full object-cover"></div>
                    <p class="text-[10px] text-[#b85d75] font-bold uppercase mb-1">Fresh Bouquet</p>
                    <h4 class="font-serif font-bold text-sm text-[#2c2c2c]">Pastel Peony Bouquet</h4>
                    <p class="text-xs text-gray-500 mt-2 font-serif font-bold">₱2,200</p>
                </div>
                <div class="bg-white rounded p-4 shadow-sm">
                    <div class="h-48 bg-gray-200 mb-3 overflow-hidden"><img src="images/IMG_8618.JPG" class="w-full h-full object-cover"></div>
                    <p class="text-[10px] text-[#b85d75] font-bold uppercase mb-1">Fresh Bouquet</p>
                    <h4 class="font-serif font-bold text-sm text-[#2c2c2c]">Orchid Elegance Vase</h4>
                    <p class="text-xs text-gray-500 mt-2 font-serif font-bold">₱3,500</p>
                </div>
                <div class="bg-white rounded p-4 shadow-sm">
                    <div class="h-48 bg-gray-200 mb-3 overflow-hidden"><img src="images/IMG_8586.JPG" class="w-full h-full object-cover"></div>
                    <p class="text-[10px] text-[#b85d75] font-bold uppercase mb-1">Dried Bouquet</p>
                    <h4 class="font-serif font-bold text-sm text-[#2c2c2c]">Garden Table Centerpiece</h4>
                    <p class="text-xs text-gray-500 mt-2 font-serif font-bold">₱2,800</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dark transparent overlay -->
    <div class="fixed inset-0 bg-black/50 z-40 pointer-events-none"></div>

    <!-- STEP 1: INITIAL ORDER FORM -->
    <?php if ($step === 'form'): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="w-full max-w-[460px] bg-kdesigns-cream rounded-md shadow-2xl overflow-hidden border border-gray-300 my-auto flex flex-col max-h-[90vh]">
                <div class="bg-kdesigns-burgundy px-8 pt-6 pb-5 flex-shrink-0">
                    <p class="text-[10px] tracking-[0.15em] font-semibold text-white/70 uppercase mb-1">PLACE ORDER</p>
                    <h2 class="text-2xl font-serif text-white font-bold mb-1"><?= htmlspecialchars($product_name); ?></h2>
                    <p class="text-sm font-serif italic text-white/90"><?= $formatted_price; ?></p>
                </div>

                <div class="px-8 pt-6 pb-8 overflow-y-auto">
                    <?php if (!empty($errors)): ?>
                        <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded border border-red-200 text-center">
                            <?php foreach ($errors as $e) { echo htmlspecialchars($e) . "<br>"; } ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <input type="hidden" name="action" value="proceed_to_payment">
                        
                        <div class="mb-4">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">NAME <span class="text-red-600">*</span></label>
                            <input type="text" name="name" value="<?= htmlspecialchars($_SESSION['order_name'] ?? 'Maja Santos'); ?>" required class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition">
                        </div>

                        <div class="mb-4">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">CONTACT NO. <span class="text-red-600">*</span></label>
                            <input type="text" name="contact" value="<?= htmlspecialchars($_SESSION['order_contact'] ?? '09374927627'); ?>" required class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition">
                        </div>

                        <div class="mb-4">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">DATE NEEDED <span class="text-red-600">*</span></label>
                            <input type="date" name="date" value="<?= htmlspecialchars($_SESSION['order_date'] ?? '2026-09-08'); ?>" required class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition">
                        </div>

                        <div class="mb-2">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">TIME NEEDED</label>
                            <select name="time" class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition cursor-pointer">
                                <?php 
                                $times = ['10:00 AM','10:30 AM','11:00 AM','11:30 AM','12:00 PM','12:30 PM','1:00 PM','1:30 PM','2:00 PM','2:30 PM','3:00 PM','3:30 PM','4:00 PM','4:30 PM','5:00 PM','5:30 PM','6:00 PM'];
                                $selected_time = $_SESSION['order_time'] ?? '10:00 AM';
                                foreach($times as $t) {
                                    $sel = ($selected_time === $t) ? 'selected' : '';
                                    echo "<option value=\"$t\" $sel>$t</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <p class="text-[11px] text-kdesigns-textMuted mb-5">We are open 10:00 AM – 6:00 PM, Mon–Sat. Pre-orders only on Sundays.</p>

                        <div class="mb-6">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">FULFILLMENT METHOD</label>
                            <input type="hidden" name="fulfillment" id="fulfillmentInput" value="<?= $fulfillment_method; ?>">
                            
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" id="pickupBtn" onclick="setFulfillment('pickup')" class="py-3 px-4 rounded-sm border text-xs font-bold tracking-wider flex items-center justify-center gap-2 transition <?= $fulfillment_method === 'pickup' ? 'bg-kdesigns-burgundy text-white border-kdesigns-burgundy' : 'bg-white text-gray-800 border-kdesigns-inputBorder hover:bg-gray-50'; ?>">
                                    <i class="fa-solid fa-store"></i> Pick Up
                                </button>
                                <button type="button" id="deliveryBtn" onclick="openDeliveryModal()" class="py-3 px-4 rounded-sm border text-xs font-bold tracking-wider flex items-center justify-center gap-2 transition <?= $fulfillment_method === 'delivery' ? 'bg-kdesigns-burgundy text-white border-kdesigns-burgundy' : 'bg-white text-gray-800 border-kdesigns-inputBorder hover:bg-gray-50'; ?>">
                                    <i class="fa-solid fa-truck"></i> Delivery
                                </button>
                            </div>

                            <div id="deliverySummaryCard" class="<?= ($fulfillment_method === 'delivery' && $has_delivery_info) ? '' : 'hidden'; ?> mt-4 p-4 bg-[#efeadf] border border-[#e1d9cc] rounded-sm">
                                <p class="text-[10px] font-bold text-kdesigns-burgundy uppercase tracking-widest mb-2">Delivery Details</p>
                                <p class="text-xs text-gray-800 mb-1"><i class="fa-solid fa-user text-[10px] mr-2 text-kdesigns-textMuted"></i> <span id="sumReceiver"><?= htmlspecialchars($_SESSION['delivery_receiver'] ?? ''); ?></span></p>
                                <p class="text-xs text-gray-800 mb-1"><i class="fa-solid fa-phone text-[10px] mr-2 text-kdesigns-textMuted"></i> <span id="sumContact"><?= htmlspecialchars($_SESSION['delivery_contact'] ?? ''); ?></span></p>
                                <p class="text-xs text-gray-800 mb-3"><i class="fa-solid fa-location-dot text-[10px] mr-2 text-kdesigns-textMuted"></i> <span id="sumLocation"><?= htmlspecialchars($_SESSION['delivery_location'] ?? ''); ?></span></p>
                                <button type="button" onclick="openDeliveryModal()" class="px-3 py-1.5 bg-white border border-[#e1d9cc] text-gray-700 font-bold text-[10px] uppercase rounded hover:bg-gray-50 transition">Edit Details</button>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <a href="index.php" class="w-1/2 py-3.5 text-center text-[11px] font-bold text-kdesigns-burgundy border border-kdesigns-inputBorder rounded-sm tracking-widest uppercase hover:bg-gray-50 transition">CANCEL</a>
                            <button type="submit" class="w-1/2 bg-kdesigns-burgundy text-white font-bold py-3.5 rounded-sm text-[11px] tracking-widest uppercase hover:bg-opacity-90 transition-opacity">CONFIRM ORDER</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delivery Modal -->
        <div id="deliveryModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
            <div class="w-full max-w-[420px] bg-kdesigns-cream rounded-md shadow-2xl overflow-hidden border border-gray-300">
                <div class="bg-kdesigns-burgundy px-6 py-5 flex items-center justify-between">
                    <div>
                        <p class="text-[9px] tracking-[0.15em] font-semibold text-white/70 uppercase">DELIVERY</p>
                        <h3 class="text-xl font-serif text-white font-bold">Receiver Information</h3>
                    </div>
                    <button type="button" onclick="closeDeliveryModal()" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-white/20 transition"><i class="fa-solid fa-xmark text-sm"></i></button>
                </div>
                <div class="px-6 py-6">
                    <form id="deliveryForm" onsubmit="saveDeliveryDetails(event)">
                        <div class="mb-4">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">NAME OF RECEIVER <span class="text-red-600">*</span></label>
                            <input type="text" id="modalReceiver" required placeholder="Full name of the recipient" value="<?= htmlspecialchars($_SESSION['delivery_receiver'] ?? ''); ?>" class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition">
                        </div>
                        <div class="mb-4">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">CONTACT NUMBER <span class="text-red-600">*</span></label>
                            <input type="text" id="modalContact" required placeholder="+63 9XX XXX XXXX" value="<?= htmlspecialchars($_SESSION['delivery_contact'] ?? ''); ?>" class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition">
                        </div>
                        <div class="mb-6">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">LOCATION <span class="text-red-600">*</span></label>
                            <textarea id="modalLocation" required rows="3" placeholder="Street address, barangay, city..." class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition"><?= htmlspecialchars($_SESSION['delivery_location'] ?? ''); ?></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" onclick="closeDeliveryModal()" class="w-1/2 py-3 text-center text-[11px] font-bold text-kdesigns-burgundy border border-kdesigns-inputBorder rounded-sm tracking-wider uppercase hover:bg-gray-50 transition">CANCEL</button>
                            <button type="submit" class="w-1/2 bg-kdesigns-burgundy text-white font-bold py-3 rounded-sm text-[11px] tracking-wider uppercase hover:bg-opacity-90 transition-opacity">CONFIRM DELIVERY</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <!-- STEP 2: PAYMENT METHOD MODAL -->
    <?php elseif ($step === 'payment'): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-[440px] bg-kdesigns-cream rounded-md shadow-2xl overflow-hidden border border-gray-300">
                <div class="bg-kdesigns-burgundy px-6 py-5 flex items-center justify-between">
                    <div>
                        <p class="text-[9px] tracking-[0.15em] font-semibold text-white/70 uppercase">STEP 2 OF 2</p>
                        <h3 class="text-xl font-serif text-white font-bold">Payment Method</h3>
                    </div>
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="back_to_form">
                        <button type="submit" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-white/20 transition"><i class="fa-solid fa-xmark text-sm"></i></button>
                    </form>
                </div>

                <div class="p-6">
                    <p class="text-xs text-kdesigns-textMuted mb-5">Choose how you'd like to pay for your order.</p>
                    
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="select_payment">
                        
                        <div class="space-y-3 mb-6">
                            <label class="flex items-start gap-4 p-4 bg-white border-2 border-kdesigns-inputBorder rounded-md cursor-pointer hover:border-kdesigns-burgundy transition">
                                <input type="radio" name="payment_method" value="Pay at Shop" checked class="mt-1 accent-kdesigns-burgundy">
                                <div>
                                    <div class="font-bold text-sm text-gray-900">Pay at Shop</div>
                                    <div class="text-xs text-kdesigns-textMuted mt-0.5">Pay in cash when you pick up or upon delivery</div>
                                </div>
                            </label>

                            <label class="flex items-start gap-4 p-4 bg-white border-2 border-kdesigns-inputBorder rounded-md cursor-pointer hover:border-kdesigns-burgundy transition">
                                <input type="radio" name="payment_method" value="GCash" class="mt-1 accent-kdesigns-burgundy">
                                <div>
                                    <div class="font-bold text-sm text-gray-900">GCash</div>
                                    <div class="text-xs text-kdesigns-textMuted mt-0.5">Send payment via GCash — 09XX XXX XXXX</div>
                                </div>
                            </label>

                            <label class="flex items-start gap-4 p-4 bg-white border-2 border-kdesigns-inputBorder rounded-md cursor-pointer hover:border-kdesigns-burgundy transition">
                                <input type="radio" name="payment_method" value="BDO" class="mt-1 accent-kdesigns-burgundy">
                                <div>
                                    <div class="font-bold text-sm text-gray-900">BDO</div>
                                    <div class="text-xs text-kdesigns-textMuted mt-0.5">BDO transfer — Acct No. XXXX-XXXX-XX</div>
                                </div>
                            </label>

                            <label class="flex items-start gap-4 p-4 bg-white border-2 border-kdesigns-inputBorder rounded-md cursor-pointer hover:border-kdesigns-burgundy transition">
                                <input type="radio" name="payment_method" value="BPI" class="mt-1 accent-kdesigns-burgundy">
                                <div>
                                    <div class="font-bold text-sm text-gray-900">BPI</div>
                                    <div class="text-xs text-kdesigns-textMuted mt-0.5">BPI transfer — Acct No. XXXX-XXXX-XX</div>
                                </div>
                            </label>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" formaction="" formmethod="POST" name="action" value="back_to_form" class="w-1/2 py-3 text-center text-[11px] font-bold text-kdesigns-burgundy border border-kdesigns-inputBorder rounded-sm tracking-wider uppercase hover:bg-gray-50 transition">BACK</button>
                            <button type="submit" class="w-1/2 bg-kdesigns-burgundy text-white font-bold py-3 rounded-sm text-[11px] tracking-wider uppercase hover:bg-opacity-90 transition-opacity">SELECT PAYMENT</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <!-- STEP 3: E-RECEIPT MODAL (ONLINE PAYMENT) -->
    <?php elseif ($step === 'ereceipt'): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-[440px] bg-kdesigns-cream rounded-md shadow-2xl overflow-hidden border border-gray-300 max-h-[90vh] flex flex-col">
                <div class="bg-kdesigns-burgundy px-6 py-5 text-center flex-shrink-0 relative">
                    <p class="text-[9px] tracking-[0.15em] font-semibold text-white/70 uppercase">KDESIGNS BLOOMS & STYLES</p>
                    <h3 class="text-xl font-serif text-white font-bold">E-Receipt</h3>
                    <form action="" method="POST" class="absolute right-4 top-4">
                        <input type="hidden" name="action" value="back_to_payment">
                        <button type="submit" class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-white/20 transition"><i class="fa-solid fa-xmark text-xs"></i></button>
                    </form>
                </div>

                <div class="p-6 overflow-y-auto flex-1">
                    <div class="text-center mb-6">
                        <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 font-bold text-[10px] tracking-widest uppercase rounded-full">PAYMENT PENDING</span>
                    </div>

                    <div class="space-y-3 text-xs border-b border-dashed border-gray-300 pb-6 mb-6">
                        <div class="flex justify-between"><span class="text-kdesigns-textMuted">Reference No.</span><span class="font-bold text-gray-900"><?= $_SESSION['receipt_ref']; ?></span></div>
                        <div class="flex justify-between"><span class="text-kdesigns-textMuted">Date</span><span class="font-bold text-gray-900"><?= date('F j, Y'); ?></span></div>
                        <div class="flex justify-between"><span class="text-kdesigns-textMuted">Payment Via</span><span class="font-bold text-gray-900"><?= htmlspecialchars($_SESSION['payment_method']); ?></span></div>
                        <div class="flex justify-between"><span class="text-kdesigns-textMuted">Product</span><span class="font-bold text-gray-900"><?= htmlspecialchars($product_name); ?></span></div>
                        <div class="flex justify-between"><span class="text-kdesigns-textMuted">Fulfillment</span><span class="font-bold text-gray-900"><?= ucfirst($_SESSION['order_fulfillment']); ?></span></div>
                        <div class="flex justify-between"><span class="text-kdesigns-textMuted">Date Needed</span><span class="font-bold text-gray-900"><?= htmlspecialchars($_SESSION['order_date']); ?></span></div>
                        <div class="flex justify-between"><span class="text-kdesigns-textMuted">Time</span><span class="font-bold text-gray-900"><?= htmlspecialchars($_SESSION['order_time']); ?></span></div>
                    </div>

                    <div class="flex justify-between items-center mb-6">
                        <span class="font-serif font-bold text-base text-gray-900">Total</span>
                        <span class="font-serif font-bold text-lg text-kdesigns-burgundy"><?= $formatted_price; ?></span>
                    </div>

                    <div class="p-3 bg-[#efeadf] border border-[#e1d9cc] rounded text-[11px] text-gray-700 mb-6 leading-relaxed">
                        Please send payment to the number/account above and use reference no. <strong class="text-gray-900"><?= $_SESSION['receipt_ref']; ?></strong>. Our team will confirm once received.
                    </div>

                    <form action="" method="POST">
                        <input type="hidden" name="action" value="finalize_ereceipt">
                        <button type="submit" class="w-full bg-kdesigns-burgundy text-white font-bold py-3.5 rounded-sm text-[11px] tracking-widest uppercase hover:bg-opacity-90 transition-opacity">DONE — CONFIRM ORDER</button>
                    </form>
                </div>
            </div>
        </div>

    <!-- STEP 4: SUCCESS POPUP (RETURNS TO INDEX UPON CLICKING CONTINUE SHOPPING) -->
    <?php elseif ($step === 'success'): ?>
        <?php 
        // Reset step state back to form for future orders
        $_SESSION['order_step'] = 'form'; 
        ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-[440px] bg-kdesigns-cream rounded-md shadow-2xl p-8 text-center border border-gray-300">
                <div class="text-4xl mb-4">🌸</div>
                <h3 class="text-2xl font-serif font-bold text-gray-900 mb-3">Order Placed!</h3>
                <p class="text-xs text-kdesigns-textMuted leading-relaxed mb-8 px-4">
                    Thank you for your order. Our team will reach out within 24 hours to confirm your arrangement and delivery details.
                </p>
                <a href="index.php" class="inline-block w-full bg-kdesigns-burgundy text-white font-bold py-3.5 rounded-sm text-[11px] tracking-widest uppercase hover:bg-opacity-90 transition-opacity">
                    CONTINUE SHOPPING
                </a>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function setFulfillment(method) {
            const input = document.getElementById('fulfillmentInput');
            const pickupBtn = document.getElementById('pickupBtn');
            const deliveryBtn = document.getElementById('deliveryBtn');
            const summaryCard = document.getElementById('deliverySummaryCard');

            input.value = method;

            if (method === 'pickup') {
                pickupBtn.className = "py-3 px-4 rounded-sm border text-xs font-bold tracking-wider flex items-center justify-center gap-2 transition bg-kdesigns-burgundy text-white border-kdesigns-burgundy";
                deliveryBtn.className = "py-3 px-4 rounded-sm border text-xs font-bold tracking-wider flex items-center justify-center gap-2 transition bg-white text-gray-800 border-kdesigns-inputBorder hover:bg-gray-50";
                summaryCard.classList.add('hidden');
            }
        }

        function openDeliveryModal() {
            document.getElementById('deliveryModal').classList.remove('hidden');
        }

        function closeDeliveryModal() {
            document.getElementById('deliveryModal').classList.add('hidden');
        }

        function saveDeliveryDetails(event) {
            event.preventDefault();
            
            const receiver = document.getElementById('modalReceiver').value;
            const contact = document.getElementById('modalContact').value;
            const location = document.getElementById('modalLocation').value;

            document.getElementById('fulfillmentInput').value = 'delivery';
            document.getElementById('pickupBtn').className = "py-3 px-4 rounded-sm border text-xs font-bold tracking-wider flex items-center justify-center gap-2 transition bg-white text-gray-800 border-kdesigns-inputBorder hover:bg-gray-50";
            document.getElementById('deliveryBtn').className = "py-3 px-4 rounded-sm border text-xs font-bold tracking-wider flex items-center justify-center gap-2 transition bg-kdesigns-burgundy text-white border-kdesigns-burgundy";

            document.getElementById('sumReceiver').innerText = receiver;
            document.getElementById('sumContact').innerText = contact;
            document.getElementById('sumLocation').innerText = location;
            document.getElementById('deliverySummaryCard').classList.remove('hidden');

            const formData = new FormData();
            formData.append('action', 'save_delivery');
            formData.append('receiver_name', receiver);
            formData.append('receiver_contact', contact);
            formData.append('delivery_location', location);

            fetch('orderform.php?product=<?= urlencode($product_name); ?>&price=<?= $product_price; ?>', {
                method: 'POST',
                body: formData
            }).then(() => {
                closeDeliveryModal();
            });
        }
    </script>
</body>
</html>