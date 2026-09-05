<?php
// Initialize session management
session_start();

// Check if the user confirmed the logout via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    // Unset all session variables and destroy the session
    $_SESSION = [];
    session_destroy();
    
    // Redirect to the home page after successful logout
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Out - KDesigns Blooms & Styles</title>
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
<!-- Background image matching the hero section -->
<body class="relative h-screen w-full bg-cover bg-center font-sans" style="background-image: url('https://images.unsplash.com/photo-1579727027552-9442a8656ee4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');">

    <!-- Dark transparent overlay -->
    <div class="absolute inset-0 bg-black/70 z-0"></div>

    <!-- Modal Container Wrapper -->
    <div class="relative z-10 h-full flex items-center justify-center px-4">
        
        <div class="w-full max-w-[400px] bg-kdesigns-cream rounded-md shadow-2xl overflow-hidden border border-gray-300">
            
            <!-- Header -->
            <div class="bg-kdesigns-burgundy px-8 pt-8 pb-6">
                <p class="text-[10px] tracking-[0.15em] font-semibold text-white/60 uppercase mb-2">
                    KDesigns Blooms & Styles
                </p>
                <h2 class="text-3xl font-serif text-white font-bold">Log Out</h2>
            </div>

            <!-- Body & Form -->
            <div class="px-8 pt-8 pb-8">
                <p class="text-sm text-gray-800 mb-8 font-medium">
                    Are you sure you want to log out of your account?
                </p>
                
                <form action="" method="POST" class="flex gap-4">
                    <!-- Cancel Button (Redirects back to home without logging out) -->
                    <a href="index.php" class="w-1/2 flex items-center justify-center text-center py-3 text-[11px] font-bold text-kdesigns-burgundy border border-kdesigns-inputBorder rounded-sm tracking-wider hover:bg-gray-50 transition cursor-pointer">
                        CANCEL
                    </a>
                    
                    <!-- Submit Button (Triggers the POST request to destroy the session) -->
                    <button type="submit" name="confirm_logout" class="w-1/2 bg-kdesigns-burgundy text-white font-bold py-3 rounded-sm text-[11px] tracking-wider hover:bg-opacity-90 transition-opacity">
                        YES, LOG OUT
                    </button>
                </form>
            </div>

        </div>
    </div>
</body>
</html>