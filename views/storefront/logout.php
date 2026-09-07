<?php
declare(strict_types=1);
?>
<?php View::render('partials/head', ['pageTitle' => $pageTitle, 'cssBundle' => $cssBundle]); ?>
<body class="relative min-h-dvh w-full bg-cover bg-center bg-no-repeat font-sans flex flex-col overflow-x-hidden" style="background-image: url('<?= e(kd_image_url('images/IMG_8620.JPG')); ?>');">
<?php View::render('partials/storefront-nav', ['is_logged_in' => $is_logged_in ?? false, 'user_name' => $user_name ?? '', 'navVariant' => 'auth']); ?>

    <!-- Dark transparent overlay -->
    <div class="fixed inset-0 bg-black/70 z-0"></div>

    <!-- Modal Container Wrapper -->
    <div class="relative z-10 flex-1 flex flex-col items-center justify-center px-4 py-6 sm:py-12 min-h-0 w-full overflow-y-auto">
        
        <div class="w-full max-w-[420px] bg-kdesigns-cream rounded-md shadow-2xl overflow-hidden border border-gray-300 flex flex-col max-h-[90vh] my-auto">
            
            <!-- Header -->
            <div class="bg-kdesigns-burgundy px-6 sm:px-8 pt-8 pb-6 flex-shrink-0">
                <p class="text-[10px] tracking-[0.15em] font-semibold text-white/60 uppercase mb-2">
                    KDesigns Blooms &amp; Styles
                </p>
                <h2 class="text-3xl font-serif text-white font-bold">Log Out</h2>
            </div>

            <!-- Body & Form -->
            <div class="flex-1 overflow-y-auto px-6 sm:px-8 pt-8 pb-8">
                <p class="text-sm text-gray-800 mb-8 font-medium">
                    Are you sure you want to log out of your account?
                </p>
                
                <!-- CSRF-protected POST logout form -->
                <form action="" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?= csrf_field() ?>

                    <!-- Cancel Button (Redirects back to home without logging out) -->
                    <a href="index.php"
                       class="w-full flex items-center justify-center text-center py-3.5 text-[11px] font-bold text-kdesigns-burgundy border border-kdesigns-inputBorder rounded-sm tracking-wider hover:bg-gray-50 transition cursor-pointer">
                        CANCEL
                    </a>
                    
                    <!-- Submit Button (Triggers the POST request to destroy the session) -->
                    <button type="submit" name="confirm_logout" id="logout-confirm-btn"
                            class="w-full bg-kdesigns-burgundy text-white font-bold py-3.5 rounded-sm text-[11px] tracking-wider hover:bg-opacity-90 transition-opacity">
                        YES, LOG OUT
                    </button>
                </form>
            </div>

        </div>
    </div>
</body>
</html>
