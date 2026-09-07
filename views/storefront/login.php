<?php
declare(strict_types=1);
?>
<?php View::render('partials/head', ['pageTitle' => $pageTitle, 'cssBundle' => $cssBundle]); ?>
<body class="relative min-h-screen w-full bg-cover bg-center font-sans flex flex-col" style="background-image: url('<?= e(kd_image_url('images/IMG_8620.JPG')); ?>');">
<?php View::render('partials/storefront-nav', ['is_logged_in' => $is_logged_in ?? false, 'user_name' => $user_name ?? '', 'navVariant' => 'auth']); ?>

    <!-- Dark transparent overlay -->
    <div class="absolute inset-0 bg-black/60 z-0"></div>

    <!-- Modal Container -->
    <div class="relative z-10 flex-1 flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-[420px] bg-kdesigns-cream rounded-md shadow-2xl overflow-hidden border border-gray-300">
        
        <!-- Header -->
        <div class="bg-kdesigns-burgundy px-8 pt-8 pb-6 text-center">
            <p class="text-[9px] tracking-[0.2em] font-semibold text-white/70 uppercase mb-1">KDESIGNS BLOOMS &amp; STYLES</p>
            <h2 class="text-2xl font-serif text-white font-bold">Welcome back</h2>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-kdesigns-inputBorder text-xs font-bold text-center">
            <div class="w-1/2 py-3 border-b-2 border-kdesigns-burgundy text-kdesigns-burgundy bg-kdesigns-cream cursor-pointer">LOG IN</div>
            <a href="signup.php" class="w-1/2 py-3 text-kdesigns-textMuted hover:text-gray-900 bg-[#efe7db] transition">SIGN UP</a>
        </div>

        <!-- Form Body -->
        <div class="p-8">
            
            <?php if (!empty($error)): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 text-xs rounded border border-red-200 text-center" role="alert">
                    <?= e($error); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" autocomplete="off" novalidate>
                <?= csrf_field() ?>

                <div class="mb-4">
                    <label for="login-email" class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">EMAIL ADDRESS</label>
                    <input type="email" id="login-email" name="email"
                           value="<?= e($postedEmail ?? ''); ?>"
                           required autocomplete="email"
                           class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition">
                </div>

                <div class="mb-6">
                    <label for="login-password" class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">PASSWORD</label>
                    <input type="password" id="login-password" name="password"
                           placeholder="Your password" required autocomplete="current-password"
                           class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-kdesigns-burgundy transition">
                </div>

                <button type="submit" id="login-submit"
                        class="w-full bg-kdesigns-burgundy text-white font-bold py-3.5 rounded-sm text-[11px] tracking-widest uppercase hover:bg-opacity-90 transition-opacity mb-4">
                    LOG IN
                </button>
            </form>
        </div>
    </div>
    </div>
</body>
</html>
