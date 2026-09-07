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
    <nav class="relative z-50 w-full bg-kdesigns-burgundy/95 text-white">
        <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between gap-4">
            <a href="/" class="flex items-center gap-3" aria-label="KDesigns home">
                <img src="<?= e(kd_image_url('images/logo.png')); ?>" alt="KDESIGNS Blooms and style" class="h-10 w-auto">
            </a>
            <div class="flex items-center gap-5 text-[11px] font-bold tracking-widest uppercase">
                <?php foreach ($navLinks as $link): ?>
                    <a href="<?= e($link['href']); ?>" class="text-white/80 hover:text-white transition"><?= e($link['label']); ?></a>
                <?php endforeach; ?>
                <?php if ($is_logged_in): ?>
                    <a href="logout.php" class="text-white/80 hover:text-white transition">Log out</a>
                <?php else: ?>
                    <a href="login.php" class="text-white/80 hover:text-white transition">Log in</a>
                <?php endif; ?>
            </div>
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
        </div>
    </nav>
<?php endif; ?>
