<?php
session_start();

$errors = [];

// Check if the form was submitted using POST[cite: 1]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and trim raw input to remove leading/trailing whitespace[cite: 1]
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate each field and collect errors[cite: 1]
    if (empty($name)) {
        $errors[] = "Full Name is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    // Validate email format[cite: 1]
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    // Sanitize only once validation passes[cite: 1]
    if (empty($errors)) {
        // Escape special characters to prevent XSS[cite: 1]
        $name = htmlspecialchars($name);
        $email = htmlspecialchars($email);
        
        // TODO: In a real application, you would hash the password using password_hash() and use PDO prepared statements to INSERT the new user here[cite: 1].
        
        // For now, simulate a successful registration
        $_SESSION['user_email'] = $email;
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
    <title>Create an Account - KDesigns Blooms & Styles</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (CDN for immediate execution) -->
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
<!-- Background image matching your index.php hero section -->
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
                    KDesigns Blooms & Styles
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
                
                <!-- Error Display -->
                <?php if (!empty($errors)): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded border border-red-200 text-center">
                        <?php foreach ($errors as $e) { echo $e . "<br>"; } ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-4">
                        <label for="name" class="block text-[11px] font-semibold text-kdesigns-textMuted uppercase tracking-wider mb-2">
                            Full Name
                        </label>
                        <!-- Using htmlspecialchars to output previously entered valid data if form submission fails[cite: 1] -->
                        <input type="text" id="name" name="name" placeholder="Your name" required 
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                               class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-kdesigns-burgundy focus:ring-1 focus:ring-kdesigns-burgundy transition">
                    </div>

                    <div class="mb-4">
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
                        <input type="password" id="password" name="password" placeholder="Min. 6 characters" required minlength="6"
                               class="w-full px-4 py-3 bg-kdesigns-inputBg border border-kdesigns-inputBorder rounded-sm text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-kdesigns-burgundy focus:ring-1 focus:ring-kdesigns-burgundy transition">
                    </div>

                    <button type="submit" class="w-full bg-kdesigns-burgundy text-white font-bold py-3.5 rounded-sm text-sm tracking-wider hover:bg-opacity-90 transition-opacity">
                        CREATE ACCOUNT
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>