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
        
        if ($email === 'customer@kdesigns.ph' && $password === 'bloom123') {
            $_SESSION['user_email'] = $email;
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Invalid email address or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - KDesigns Blooms & Styles</title>
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
<!-- Pointing directly to your local image folder -->
<body class="relative h-screen w-full bg-cover bg-center font-sans" style="background-image: url('images/IMG_8620.JPG');">

    <!-- Adjusted overlay opacity so the background image remains visible -->
    <div class="absolute inset-0 bg-black/50 z-0"></div>

    <a href="index.php" class="absolute top-6 right-8 text-white/90 text-3xl font-light hover:text-white z-50 transition cursor-pointer">
        &times;
    </a>

    <div class="relative z-10 h-full flex items-center justify-center px-4">
        
        <div class="w-full max-w-[400px] bg-kdesigns-cream rounded-md shadow-2xl overflow-hidden border border-gray-300">
            
            <div class="bg-kdesigns-burgundy px-8 pt-8 pb-6">
                <p class="text-[10px] tracking-[0.15em] font-semibold text-white/60 uppercase mb-2">
                    KDesigns Blooms & Styles
                </p>
                <h2 class="text-3xl font-serif text-white font-bold">Welcome back</h2>
            </div>

            <div class="flex border-b border-kdesigns-inputBorder bg-white/50">
                <a href="login.php" class="w-1/2 text-center py-4 text-sm font-bold text-kdesigns-burgundy border-b-2 border-kdesigns-burgundy">
                    LOG IN
                </a>
                <a href="signup.php" class="w-1/2 text-center py-4 text-sm font-semibold text-kdesigns-textMuted hover:text-gray-800 transition">
                    SIGN UP
                </a>
            </div>

            <div class="px-8 pt-6 pb-8">
                
                <?php if (!empty($errors)): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded border border-red-200 text-center">
                        <?php foreach ($errors as $e) { echo $e . "<br>"; } ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-5">
                        <label for="email" class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">
                            Email Address
                        </label>
                        <input type="email" id="email" name="email" placeholder="hello@you.com" required 
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                               class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-kdesigns-burgundy focus:ring-1 focus:ring-kdesigns-burgundy transition">
                    </div>

                    <div class="mb-6">
                        <label for="password" class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">
                            Password
                        </label>
                        <input type="password" id="password" name="password" placeholder="Your password" required 
                               class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-kdesigns-burgundy focus:ring-1 focus:ring-kdesigns-burgundy transition">
                    </div>

                    <button type="submit" class="w-full bg-kdesigns-burgundy text-white font-bold py-3.5 rounded-sm text-sm tracking-wider hover:bg-opacity-90 transition-opacity">
                        LOG IN
                    </button>
                </form>

                <div class="mt-6 text-center text-[13px] text-gray-500">
                    Demo: <span class="font-semibold text-kdesigns-burgundy">customer@kdesigns.ph</span> / <span class="font-semibold text-kdesigns-burgundy">bloom123</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>