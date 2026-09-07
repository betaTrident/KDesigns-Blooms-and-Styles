<?php
declare(strict_types=1);
?>
<?php View::render('partials/head', ['pageTitle' => $pageTitle, 'cssBundle' => $cssBundle]); ?>
<body class="relative min-h-screen w-full bg-[#f4efe8] font-sans overflow-y-auto">

<div id="orderform-app" data-csrf="<?= e($csrf_token); ?>" data-product-id="<?= (int) $product_id; ?>">

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
                    <div class="h-48 bg-gray-200 mb-3 overflow-hidden"><img src="<?= e(kd_image_url('images/IMG_8603.JPG')); ?>" class="w-full h-full object-cover"></div>
                    <p class="text-[10px] text-[#b85d75] font-bold uppercase mb-1">Fresh Bouquet</p>
                    <h4 class="font-serif font-bold text-sm text-[#2c2c2c]">Crimson Romance Bouquet</h4>
                    <p class="text-xs text-gray-500 mt-2 font-serif font-bold">₱1,850</p>
                </div>
                <div class="bg-white rounded p-4 shadow-sm border-2 border-kdesigns-burgundy">
                    <div class="h-48 bg-gray-200 mb-3 overflow-hidden"><img src="<?= e(kd_image_url('images/IMG_8597.JPG')); ?>" class="w-full h-full object-cover"></div>
                    <p class="text-[10px] text-[#b85d75] font-bold uppercase mb-1">Fresh Bouquet</p>
                    <h4 class="font-serif font-bold text-sm text-[#2c2c2c]">Pastel Peony Bouquet</h4>
                    <p class="text-xs text-gray-500 mt-2 font-serif font-bold">₱2,200</p>
                </div>
                <div class="bg-white rounded p-4 shadow-sm">
                    <div class="h-48 bg-gray-200 mb-3 overflow-hidden"><img src="<?= e(kd_image_url('images/IMG_8618.JPG')); ?>" class="w-full h-full object-cover"></div>
                    <p class="text-[10px] text-[#b85d75] font-bold uppercase mb-1">Fresh Bouquet</p>
                    <h4 class="font-serif font-bold text-sm text-[#2c2c2c]">Orchid Elegance Vase</h4>
                    <p class="text-xs text-gray-500 mt-2 font-serif font-bold">₱3,500</p>
                </div>
                <div class="bg-white rounded p-4 shadow-sm">
                    <div class="h-48 bg-gray-200 mb-3 overflow-hidden"><img src="<?= e(kd_image_url('images/IMG_8586.JPG')); ?>" class="w-full h-full object-cover"></div>
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
                    <h2 class="text-2xl font-serif text-white font-bold mb-1"><?= e($product_name); ?></h2>
                    <p class="text-sm font-serif italic text-white/90"><?= e($formatted_price); ?></p>
                </div>

                <div class="px-8 pt-6 pb-8 overflow-y-auto">
                    <?php if (!empty($errors)): ?>
                        <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded border border-red-200 text-center">
                            <?php foreach ($errors as $err): ?>
                                <?= e($err); ?><br>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="proceed_to_payment">
                        
                        <div class="mb-4">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">NAME <span class="text-red-600">*</span></label>
                            <input type="text" name="name" value="<?= e($_SESSION['order_name'] ?? ($_SESSION['user_name'] ?? '')); ?>" required class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition">
                        </div>

                        <div class="mb-4">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">CONTACT NO. <span class="text-red-600">*</span></label>
                            <input type="text" name="contact" value="<?= e($_SESSION['order_contact'] ?? ''); ?>" required class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition">
                        </div>

                        <div class="mb-4">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">DATE NEEDED <span class="text-red-600">*</span></label>
                            <input type="date" name="date" value="<?= e($_SESSION['order_date'] ?? date('Y-m-d')); ?>" required class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition">
                        </div>

                        <div class="mb-2">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">TIME NEEDED</label>
                            <select name="time" class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition cursor-pointer">
                                <?php
                                $selected_time = (string) ($_SESSION['order_time'] ?? '10:00 AM');
                                foreach ($times as $t):
                                    $t = (string) $t;
                                ?>
                                    <option value="<?= e($t); ?>"<?= $selected_time === $t ? ' selected' : ''; ?>><?= e($t); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <p class="text-[11px] text-kdesigns-textMuted mb-5">We are open 10:00 AM – 6:00 PM, Mon–Sat. Pre-orders only on Sundays.</p>

                        <div class="mb-6">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">FULFILLMENT METHOD</label>
                            <input type="hidden" name="fulfillment" id="fulfillmentInput" value="<?= e($fulfillment_method); ?>">
                            
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
                                <p class="text-xs text-gray-800 mb-1"><i class="fa-solid fa-user text-[10px] mr-2 text-kdesigns-textMuted"></i> <span id="sumReceiver"><?= e($_SESSION['delivery_receiver'] ?? ''); ?></span></p>
                                <p class="text-xs text-gray-800 mb-1"><i class="fa-solid fa-phone text-[10px] mr-2 text-kdesigns-textMuted"></i> <span id="sumContact"><?= e($_SESSION['delivery_contact'] ?? ''); ?></span></p>
                                <p class="text-xs text-gray-800 mb-3"><i class="fa-solid fa-location-dot text-[10px] mr-2 text-kdesigns-textMuted"></i> <span id="sumLocation"><?= e($_SESSION['delivery_location'] ?? ''); ?></span></p>
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
                            <input type="text" id="modalReceiver" required placeholder="Full name of the recipient" value="<?= e($_SESSION['delivery_receiver'] ?? ''); ?>" class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition">
                        </div>
                        <div class="mb-4">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">CONTACT NUMBER <span class="text-red-600">*</span></label>
                            <input type="text" id="modalContact" required placeholder="+63 9XX XXX XXXX" value="<?= e($_SESSION['delivery_contact'] ?? ''); ?>" class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition">
                        </div>
                        <div class="mb-6">
                            <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">LOCATION <span class="text-red-600">*</span></label>
                            <textarea id="modalLocation" required rows="3" placeholder="Street address, barangay, city..." class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition"><?= e($_SESSION['delivery_location'] ?? ''); ?></textarea>
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
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="back_to_form">
                        <button type="submit" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-white/20 transition"><i class="fa-solid fa-xmark text-sm"></i></button>
                    </form>
                </div>

                <div class="p-6">
                    <?php if (!empty($errors)): ?>
                        <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded border border-red-200 text-center">
                            <?php foreach ($errors as $err): ?>
                                <?= e($err); ?><br>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <p class="text-xs text-kdesigns-textMuted mb-5">Choose how you'd like to pay for your order.</p>
                    
                    <form action="" method="POST">
                        <?= csrf_field() ?>
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
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="back_to_payment">
                        <button type="submit" class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center text-white hover:bg-white/20 transition"><i class="fa-solid fa-xmark text-xs"></i></button>
                    </form>
                </div>

                <div class="p-6 overflow-y-auto flex-1">
                    <?php if (!empty($errors)): ?>
                        <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded border border-red-200 text-center">
                            <?php foreach ($errors as $err): ?>
                                <?= e($err); ?><br>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="text-center mb-6">
                        <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 font-bold text-[10px] tracking-widest uppercase rounded-full">PAYMENT PENDING</span>
                    </div>

                    <div class="space-y-3 text-xs border-b border-dashed border-gray-300 pb-6 mb-6">
                        <div class="flex justify-between"><span class="text-kdesigns-textMuted">Reference No.</span><span class="font-bold text-gray-900"><?= e($_SESSION['receipt_ref'] ?? ''); ?></span></div>
                        <div class="flex justify-between"><span class="text-kdesigns-textMuted">Date</span><span class="font-bold text-gray-900"><?= e(date('F j, Y')); ?></span></div>
                        <div class="flex justify-between"><span class="text-kdesigns-textMuted">Payment Via</span><span class="font-bold text-gray-900"><?= e($_SESSION['payment_method'] ?? ''); ?></span></div>
                        <div class="flex justify-between"><span class="text-kdesigns-textMuted">Product</span><span class="font-bold text-gray-900"><?= e($product_name); ?></span></div>
                        <div class="flex justify-between"><span class="text-kdesigns-textMuted">Fulfillment</span><span class="font-bold text-gray-900"><?= e(ucfirst((string) ($_SESSION['order_fulfillment'] ?? ''))); ?></span></div>
                        <div class="flex justify-between"><span class="text-kdesigns-textMuted">Date Needed</span><span class="font-bold text-gray-900"><?= e($_SESSION['order_date'] ?? ''); ?></span></div>
                        <div class="flex justify-between"><span class="text-kdesigns-textMuted">Time</span><span class="font-bold text-gray-900"><?= e($_SESSION['order_time'] ?? ''); ?></span></div>
                    </div>

                    <div class="flex justify-between items-center mb-6">
                        <span class="font-serif font-bold text-base text-gray-900">Total</span>
                        <span class="font-serif font-bold text-lg text-kdesigns-burgundy"><?= e($formatted_price); ?></span>
                    </div>

                    <div class="p-3 bg-[#efeadf] border border-[#e1d9cc] rounded text-[11px] text-gray-700 mb-6 leading-relaxed">
                        Please send payment to the number/account above and use reference no. <strong class="text-gray-900"><?= e($_SESSION['receipt_ref'] ?? ''); ?></strong>. Our team will confirm once received.
                    </div>

                    <form action="" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="finalize_ereceipt">
                        <button type="submit" class="w-full bg-kdesigns-burgundy text-white font-bold py-3.5 rounded-sm text-[11px] tracking-widest uppercase hover:bg-opacity-90 transition-opacity">DONE — CONFIRM ORDER</button>
                    </form>
                </div>
            </div>
        </div>

    <!-- STEP 4: SUCCESS POPUP (RETURNS TO INDEX UPON CLICKING CONTINUE SHOPPING) -->
    <?php elseif ($step === 'success'): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-[440px] bg-kdesigns-cream rounded-md shadow-2xl p-8 text-center border border-gray-300">
                <div class="text-4xl mb-4">🌸</div>
                <h3 class="text-2xl font-serif font-bold text-gray-900 mb-3">Order Placed!</h3>
                <p class="text-xs text-kdesigns-textMuted leading-relaxed mb-8 px-4">
                    Thank you for your order<?php if (($last_order_code ?? '') !== ''): ?> (<?= e($last_order_code); ?>)<?php endif; ?>. Our team will reach out within 24 hours to confirm your arrangement and delivery details.
                </p>
                <a href="index.php" class="inline-block w-full bg-kdesigns-burgundy text-white font-bold py-3.5 rounded-sm text-[11px] tracking-widest uppercase hover:bg-opacity-90 transition-opacity">
                    CONTINUE SHOPPING
                </a>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="<?= e(kd_asset('assets/js/orderform.js')); ?>" defer></script>
</body>
</html>
