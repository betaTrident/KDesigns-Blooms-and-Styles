<?php
declare(strict_types=1);
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/security.php';

secure_session_start();

// Already logged in — redirect away
if (isset($_SESSION['user_email'])) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ── 1. Enforce CSRF ──────────────────────────────────────────────────────
    enforce_csrf();

    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    // ── 2. Validate inputs ───────────────────────────────────────────────────
    if (empty($name)) {
        $errors[] = 'Full Name is required.';
    } elseif (strlen($name) > 100) {
        $errors[] = 'Full Name must be 100 characters or fewer.';
    }

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    } elseif (strlen($email) > 254) {
        $errors[] = 'Email address is too long.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one letter and one number.';
    }

    // ── 3. If valid — hash password and create session ───────────────────────
    //       NOTE: In production, INSERT into database here using PDO.
    //       This file-based approach is a temporary placeholder until
    //       the database is configured (see config/db.php).
    if (empty($errors)) {
        // Hash the password with Argon2id before any storage
        $password_hash = password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost'   => 4,
            'threads'     => 3,
        ]);

        // TODO: Replace with: $db->prepare('INSERT INTO users ...')->execute(...)
        // For now, establish an authenticated session
        session_regenerate_id(true);

        $_SESSION['user_email']    = $email;
        $_SESSION['user_name']     = $name;
        $_SESSION['user_role']     = 'customer';
        $_SESSION['last_activity'] = time();

        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account - KDesigns Blooms &amp; Styles</title>
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
<body class="relative h-screen w-full bg-cover bg-center font-sans" style="background-image: url('images/IMG_8620.JPG');">
    
    <!-- Dark transparent overlay -->
    <div class="absolute inset-0 bg-black/70 z-0"></div>

    <!-- Close 'X' Button -->
    <a href="index.php" class="absolute top-6 right-8 text-white text-3xl font-light hover:text-gray-300 z-50 transition cursor-pointer">
        &times;
    </a>

    <!-- Modal Container Wrapper -->
    <div class="relative z-10 h-full flex items-center justify-center px-4">
        
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
                               value="<?= e($_POST['name'] ?? ''); ?>"
                               class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-kdesigns-burgundy focus:ring-1 focus:ring-kdesigns-burgundy transition">
                    </div>

                    <div class="mb-4">
                        <label for="signup-email" class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">
                            Email Address
                        </label>
                        <input type="email" id="signup-email" name="email"
                               placeholder="hello@you.com" required maxlength="254"
                               value="<?= e($_POST['email'] ?? ''); ?>"
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