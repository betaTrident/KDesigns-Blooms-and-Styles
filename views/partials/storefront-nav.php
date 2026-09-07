<?php
declare(strict_types=1);

$is_logged_in = $is_logged_in ?? false;
$user_name = $user_name ?? '';
$navVariant = $navVariant ?? 'storefront';
$navLinks = [
    ['href' => '/#home', 'label' => 'HOME', 'hash' => '#home'],
    ['href' => '/#about', 'label' => 'ABOUT', 'hash' => '#about'],
    ['href' => '/#catalog', 'label' => 'CATALOG', 'hash' => '#catalog'],
];

if ($navVariant === 'auth'):
?>
    <nav class="relative z-50 w-full" aria-label="Account">
        <div class="px-4 sm:px-6 py-4">
            <a href="/" class="inline-flex items-center gap-2.5 rounded-full bg-black/40 hover:bg-black/55 backdrop-blur-sm px-3.5 py-2 text-white text-xs font-semibold tracking-wide transition">
                <i class="fa-solid fa-arrow-left text-[11px]" aria-hidden="true"></i>
                <span>Back to homepage</span>
            </a>
        </div>
    </nav>
<?php else: ?>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <a href="/" class="logo-brand">
                    <img src="<?= e(kd_image_url('images/logo.png')); ?>" alt="KDESIGNS Blooms and style" class="logo-img">
                </a>
            </div>
            <ul class="nav-links">
                <?php foreach ($navLinks as $i => $link): ?>
                    <li><a href="<?= e($link['href']); ?>"<?= $i === 0 ? ' class="active"' : ''; ?>><?= e($link['label']); ?></a></li>
                <?php endforeach; ?>
            </ul>
            <div class="nav-actions">
                <?php if ($is_logged_in): ?>
                    <span class="nav-greeting">Hi, <?= e($user_name); ?></span>
                    <a href="orders.php" class="btn-order"><i class="fa-solid fa-clipboard-list"></i> ORDERS</a>
                    <a href="logout.php" class="btn-login">LOG OUT</a>
                <?php else: ?>
                    <a href="login.php" class="btn-login">LOG IN</a>
                <?php endif; ?>
            </div>
            <button type="button" class="nav-toggle" data-nav-toggle aria-controls="storefront-mobile-nav" aria-expanded="false" aria-label="Open menu">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
        <div id="storefront-mobile-nav" class="mobile-nav" data-nav-panel>
            <?php foreach ($navLinks as $i => $link): ?>
                <a href="<?= e($link['href']); ?>"<?= $i === 0 ? ' class="active"' : ''; ?>><?= e($link['label']); ?></a>
            <?php endforeach; ?>
            <?php if ($is_logged_in): ?>
                <span class="nav-greeting">Hi, <?= e($user_name); ?></span>
                <a href="orders.php" class="btn-order"><i class="fa-solid fa-clipboard-list"></i> ORDERS</a>
                <a href="logout.php" class="btn-login">LOG OUT</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">LOG IN</a>
            <?php endif; ?>
        </div>
    </nav>
    <script src="<?= e(kd_asset('assets/js/nav.js')); ?>" defer></script>
<?php endif; ?>
