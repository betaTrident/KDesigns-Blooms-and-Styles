<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Check for Admin login credentials
    if ($email === 'admin@kdesigns.ph' && $password === 'kdesigns2026') {
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = 'Admin';
        header('Location: admin.php');
        exit;
    }
    
    // Check for Demo Customer credentials
    elseif ($email === 'customer@kdesigns.ph' && $password === 'bloom123') {
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = 'Maja Santos';
        header('Location: index.php');
        exit;
    }
    
    // Fallback error check
    else {
        $error = "Invalid email address or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KDesigns Blooms & Styles</title>
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
<body class="relative h-screen w-full bg-cover bg-center font-sans flex items-center justify-center overflow-hidden" style="background-image: url('images/IMG_8620.JPG');">

    <!-- Dark transparent overlay -->
    <div class="absolute inset-0 bg-black/60 z-0"></div>

    <!-- Modal Container -->
    <div class="relative z-10 w-full max-w-[420px] bg-kdesigns-cream rounded-md shadow-2xl overflow-hidden border border-gray-300">
        
        <!-- Header -->
        <div class="bg-kdesigns-burgundy px-8 pt-8 pb-6 text-center">
            <p class="text-[9px] tracking-[0.2em] font-semibold text-white/70 uppercase mb-1">KDESIGNS BLOOMS & STYLES</p>
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
                <div class="mb-4 p-3 bg-red-100 text-red-700 text-xs rounded border border-red-200 text-center">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-4">
                    <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">EMAIL ADDRESS</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? 'admin@kdesigns.ph'); ?>" required 
                           class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 focus:outline-none focus:border-kdesigns-burgundy transition">
                </div>

                <div class="mb-6">
                    <label class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">PASSWORD</label>
                    <input type="password" name="password" placeholder="Your password" required 
                           class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-kdesigns-burgundy transition">
                </div>

                <button type="submit" class="w-full bg-kdesigns-burgundy text-white font-bold py-3.5 rounded-sm text-[11px] tracking-widest uppercase hover:bg-opacity-90 transition-opacity mb-4">
                    LOG IN
                </button>
            </form>

            <div class="text-center text-[11px] text-kdesigns-textMuted">
                
            </div>
        </div>
    </div>
</body>
</html>