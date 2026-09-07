<?php
declare(strict_types=1);
?>
<?php View::render('partials/head', ['pageTitle' => $pageTitle, 'cssBundle' => $cssBundle]); ?>
<body class="relative min-h-screen w-full bg-cover bg-center font-sans flex flex-col" style="background-image: url('<?= e(kd_image_url('images/IMG_8620.JPG')); ?>');">
<?php View::render('partials/storefront-nav', ['is_logged_in' => $is_logged_in ?? false, 'user_name' => $user_name ?? '', 'navVariant' => 'auth']); ?>
    
    <!-- Dark transparent overlay -->
    <div class="absolute inset-0 bg-black/70 z-0"></div>

    <!-- Modal Container Wrapper -->
    <div class="relative z-10 flex-1 flex items-center justify-center px-4 py-8">
        
        <!-- The Modal Card -->
        <div class="w-full max-w-[400px] bg-kdesigns-cream rounded-md shadow-2xl overflow-hidden border border-gray-300">
            
            <div class="bg-kdesigns-burgundy px-8 pt-8 pb-6">
                <p class="text-[10px] tracking-[0.15em] font-semibold text-white/60 uppercase mb-2">
                    KDesigns Blooms &amp; Styles
                </p>
                <h2 class="text-3xl font-serif text-white font-bold">Create an account</h2>
            </div>

            <div class="flex border-b border-kdesigns-inputBorder bg-white/50">
                <a href="login.php" class="w-1/2 text-center py-4 text-sm font-semibold text-kdesigns-textMuted hover:text-gray-800 transition">
                    LOG IN
                </a>
                <a href="signup.php" class="w-1/2 text-center py-4 text-sm font-bold text-kdesigns-burgundy border-b-2 border-kdesigns-burgundy">
                    SIGN UP
                </a>
            </div>

            <div class="px-8 pt-6 pb-8">
                
                <!-- Error Display — XSS-safe output -->
                <?php if (!empty($errors)): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded border border-red-200 text-center" role="alert">
                        <?php foreach ($errors as $err): ?>
                            <?= e($err); ?><br>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" autocomplete="off" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label for="signup-name" class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">
                            Full Name
                        </label>
                        <input type="text" id="signup-name" name="name"
                               placeholder="Your name" required maxlength="100"
                               value="<?= e($name ?? ''); ?>"
                               class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-kdesigns-burgundy focus:ring-1 focus:ring-kdesigns-burgundy transition">
                    </div>

                    <div class="mb-4">
                        <label for="signup-email" class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">
                            Email Address
                        </label>
                        <input type="email" id="signup-email" name="email"
                               placeholder="hello@you.com" required maxlength="254"
                               value="<?= e($email ?? ''); ?>"
                               class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-kdesigns-burgundy focus:ring-1 focus:ring-kdesigns-burgundy transition">
                    </div>

                    <div class="mb-6">
                        <label for="signup-password" class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">
                            Password
                        </label>
                        <input type="password" id="signup-password" name="password"
                               placeholder="Min. 8 characters, letters &amp; numbers" required minlength="8"
                               autocomplete="new-password"
                               class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-kdesigns-burgundy focus:ring-1 focus:ring-kdesigns-burgundy transition">
                    </div>

                    <button type="submit" id="signup-submit"
                            class="w-full bg-kdesigns-burgundy text-white font-bold py-3.5 rounded-sm text-sm tracking-wider hover:bg-opacity-90 transition-opacity">
                        CREATE ACCOUNT
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
