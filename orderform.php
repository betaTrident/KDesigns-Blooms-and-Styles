<?php
session_start();

// Capture product details passed from the catalog URL
$product_name = $_GET['product'] ?? 'Lavender Dreams Bundle';
$product_price = $_GET['price'] ?? '980';
$formatted_price = '₱' . number_format((float)$product_price);

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $fulfillment = trim($_POST['fulfillment'] ?? 'pickup');

    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($contact)) {
        $errors[] = "Contact number is required.";
    }
    if (empty($date)) {
        $errors[] = "Date needed is required.";
    }

    if (empty($errors)) {
        $name = htmlspecialchars($name);
        $contact = htmlspecialchars($contact);
        $date = htmlspecialchars($date);
        $time = htmlspecialchars($time);
        $fulfillment = htmlspecialchars($fulfillment);

        // TODO: Insert into database using PDO prepared statements
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order - KDesigns Blooms & Styles</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
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
<!-- Same background image setup as your login and signup files -->
<body class="relative h-screen w-full bg-cover bg-center font-sans flex items-center justify-center overflow-hidden" style="background-image: url('images/IMG_8620.JPG');">

    <!-- Dark transparent overlay allowing background to show through -->
    <div class="absolute inset-0 bg-black/60 z-0"></div>

    <!-- Modal Container -->
    <div class="relative z-10 w-full max-w-[460px] bg-kdesigns-cream rounded-md shadow-2xl overflow-hidden border border-gray-300 my-8 max-h-[90vh] flex flex-col">
        
        <!-- Header Section -->
        <div class="bg-kdesigns-burgundy px-8 pt-6 pb-5 flex-shrink-0">
            <p class="text-[10px] tracking-[0.15em] font-semibold text-white/70 uppercase mb-1">
                PLACE ORDER
            </p>
            <h2 class="text-2xl font-serif text-white font-bold mb-1"><?= htmlspecialchars($product_name); ?></h2>
            <p class="text-sm font-serif italic text-white/90"><?= $formatted_price; ?></p>
        </div>

        <!-- Scrollable Form Body -->
        <div class="px-8 pt-6 pb-8 overflow-y-auto">
            
            <?php if (!empty($errors)): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded border border-red-200 text-center">
                    <?php foreach ($errors as $e) { echo htmlspecialchars($e) . "<br>"; } ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-4 p-4 bg-green-100 text-green-800 text-sm rounded border border-green-200 text-center">
                    Order confirmed successfully! We will contact you shortly.
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                
                <!-- Name Field -->
                <div class="mb-4">
                    <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">
                        NAME <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" placeholder="Maja Santos" required 
                           class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-kdesigns-burgundy transition">
                </div>

                <!-- Contact No Field -->
                <div class="mb-4">
                    <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">
                        CONTACT NO. <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="contact" placeholder="+63 9XX XXX XXXX" required 
                           value="<?= isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : '' ?>"
                           class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-kdesigns-burgundy transition">
                </div>

                <!-- Date Needed Field -->
                <div class="mb-4">
                    <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">
                        DATE NEEDED <span class="text-red-600">*</span>
                    </label>
                    <input type="date" name="date" value="<?= isset($_POST['date']) ? htmlspecialchars($_POST['date']) : '2026-10-06' ?>" required 
                           class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition">
                </div>

                <!-- Time Needed Field -->
                <div class="mb-2">
                    <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">
                        TIME NEEDED
                    </label>
                    <select name="time" class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition cursor-pointer">
                        <option value="10:00 AM">10:00 AM</option>
                        <option value="10:30 AM">10:30 AM</option>
                        <option value="11:00 AM">11:00 AM</option>
                        <option value="11:30 AM">11:30 AM</option>
                        <option value="12:00 PM">12:00 PM</option>
                        <option value="12:30 PM">12:30 PM</option>
                        <option value="1:00 PM">1:00 PM</option>
                        <option value="1:30 PM">1:30 PM</option>
                        <option value="2:00 PM">2:00 PM</option>
                        <option value="2:30 PM">2:30 PM</option>
                        <option value="3:00 PM">3:00 PM</option>
                        <option value="3:30 PM">3:30 PM</option>
                        <option value="4:00 PM">4:00 PM</option>
                        <option value="4:30 PM">4:30 PM</option>
                        <option value="5:00 PM">5:00 PM</option>
                        <option value="5:30 PM">5:30 PM</option>
                        <option value="6:00 PM">6:00 PM</option>
                    </select>
                </div>
                <p class="text-[11px] text-kdesigns-textMuted mb-5">
                    We are open 10:00 AM – 6:00 PM, Mon–Sat. Pre-orders only on Sundays.
                </p>

                <!-- Fulfillment Method Section -->
                <div class="mb-6">
                    <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">
                        FULFILLMENT METHOD
                    </label>
                    <input type="hidden" name="fulfillment" id="fulfillmentInput" value="pickup">
                    
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" id="pickupBtn" onclick="setFulfillment('pickup')" 
                                class="py-3 px-4 rounded-sm border text-xs font-bold tracking-wider flex items-center justify-center gap-2 transition bg-kdesigns-burgundy text-white border-kdesigns-burgundy">
                            <i class="fa-solid fa-store"></i> Pick Up
                        </button>
                        <button type="button" id="deliveryBtn" onclick="setFulfillment('delivery')" 
                                class="py-3 px-4 rounded-sm border text-xs font-bold tracking-wider flex items-center justify-center gap-2 transition bg-white text-gray-800 border-kdesigns-inputBorder hover:bg-gray-50">
                            <i class="fa-solid fa-truck"></i> Delivery
                        </button>
                    </div>

                    <div id="deliveryNotice" class="hidden mt-3 p-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder text-[12px] text-gray-700 rounded-sm">
                        Delivery fees apply based on location. Our team will contact you to confirm.
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <a href="index.php" class="w-1/2 py-3.5 text-center text-[11px] font-bold text-kdesigns-burgundy border border-kdesigns-inputBorder rounded-sm tracking-widest uppercase hover:bg-gray-50 transition">
                        CANCEL
                    </a>
                    <button type="submit" class="w-1/2 bg-kdesigns-burgundy text-white font-bold py-3.5 rounded-sm text-[11px] tracking-widest uppercase hover:bg-opacity-90 transition-opacity">
                        CONFIRM ORDER
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Fulfillment Toggle Script -->
    <script>
        function setFulfillment(method) {
            const input = document.getElementById('fulfillmentInput');
            const pickupBtn = document.getElementById('pickupBtn');
            const deliveryBtn = document.getElementById('deliveryBtn');
            const notice = document.getElementById('deliveryNotice');

            input.value = method;

            if (method === 'pickup') {
                pickupBtn.className = "py-3 px-4 rounded-sm border text-xs font-bold tracking-wider flex items-center justify-center gap-2 transition bg-kdesigns-burgundy text-white border-kdesigns-burgundy";
                deliveryBtn.className = "py-3 px-4 rounded-sm border text-xs font-bold tracking-wider flex items-center justify-center gap-2 transition bg-white text-gray-800 border-kdesigns-inputBorder hover:bg-gray-50";
                notice.classList.add('hidden');
            } else {
                deliveryBtn.className = "py-3 px-4 rounded-sm border text-xs font-bold tracking-wider flex items-center justify-center gap-2 transition bg-kdesigns-burgundy text-white border-kdesigns-burgundy";
                pickupBtn.className = "py-3 px-4 rounded-sm border text-xs font-bold tracking-wider flex items-center justify-center gap-2 transition bg-white text-gray-800 border-kdesigns-inputBorder hover:bg-gray-50";
                notice.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>