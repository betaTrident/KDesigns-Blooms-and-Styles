<?php
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $email = htmlspecialchars($email);
        
        // Demo admin credentials shown in the image
        if ($email === 'admin@kdesigns.ph' && $password === 'kdesigns2026') {
            $_SESSION['admin_email'] = $email;
            header('Location: admin_dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid admin credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access - KDesigns Blooms & Styles</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
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
<body class="relative h-screen w-full bg-cover bg-center font-sans flex flex-col items-center justify-center" style="background-image: url('images/0e011570-2704-40fb-9a24-6a18d7c52867.jfif');">

    <!-- Dark transparent overlay -->
    <div class="absolute inset-0 bg-black/60 z-0"></div>

    <!-- Top Admin Header Branding -->
    <div class="relative z-10 text-center mb-6">
        <p class="text-[10px] tracking-[0.2em] font-semibold text-white/70 uppercase mb-1">
            Admin Access
        </p>
        <h1 class="text-3xl font-serif text-white font-bold">KDesigns</h1>
        <p class="text-xs text-white/80 tracking-wider">Blooms & Styles Dashboard</p>
    </div>

    <!-- Login Modal Box -->
    <div class="relative z-10 w-full max-w-[420px] bg-kdesigns-cream rounded-md shadow-2xl overflow-hidden border border-gray-300">
        
        <!-- Box Header -->
        <div class="bg-kdesigns-burgundy px-8 py-4">
            <h3 class="text-sm font-semibold text-white tracking-wide">Sign in to Dashboard</h3>
        </div>

        <div class="px-8 pt-6 pb-8">
            
            <?php if (!empty($errors)): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded border border-red-200 text-center">
                    <?php foreach ($errors as $e) { echo $e . "<br>"; } ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-4">
                    <label for="email" class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">
                        Email
                    </label>
                    <input type="email" id="email" name="email" placeholder="admin@kdesigns.ph" required 
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                           class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-kdesigns-burgundy focus:ring-1 focus:ring-kdesigns-burgundy transition">
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">
                        Password
                    </label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required 
                           class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-kdesigns-burgundy focus:ring-1 focus:ring-kdesigns-burgundy transition">
                </div>

                <button type="submit" class="w-full bg-kdesigns-burgundy text-white font-bold py-3.5 rounded-sm text-[11px] tracking-widest hover:bg-opacity-90 transition-opacity uppercase">
                    Access Dashboard
                </button>
            </form>

            <div class="mt-6 text-center text-[12px] text-gray-500">
                Demo: <span class="font-semibold text-kdesigns-burgundy">admin@kdesigns.ph</span> / <span class="font-semibold text-kdesigns-burgundy">kdesigns2026</span>
            </div>
        </div>
    </div>
</body>
</html>