<?php
// Load configuration for baseUrl and assetUrl
require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Railway Quarter Management System - AOT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="h-screen w-full flex justify-center items-center relative bg-cover bg-center bg-no-repeat font-sans overflow-hidden"
      style="font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-image: url('<?php echo assetUrl('images/train.jpg'); ?>');">

    <!-- Darker Overlay -->
    <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/55 to-black/70 z-0"></div>

    <!-- Main Container -->
    <div class="relative z-10 text-center text-white px-6 max-w-4xl mx-auto flex flex-col items-center">

        <!-- Logo Image -->
        <div class="mb-2">
            <img src="<?php echo assetUrl('images/logo.png'); ?>" alt="Railway Logo"
                 class="h-44 sm:h-52 w-auto mx-auto drop-shadow-[0_8px_20px_rgba(0,0,0,0.95)] transition-transform duration-500 hover:scale-105">
        </div>

        <!-- Main Title -->
        <h1 class="text-3xl sm:text-5xl lg:text-[52px] font-extrabold tracking-wide mb-4 [text-shadow:2px_2px_10px_black] drop-shadow-lg">
            Railway Quarter Management System <span class="text-white">- AOT</span>
        </h1>

        <!-- Subtitle -->
        <div class="text-lg sm:text-[22px] text-gray-200 mb-10 font-medium [text-shadow:2px_2px_6px_black]">
            A Digital Solution for Efficient Railway Quarter Management
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-center items-center gap-6">

            <!-- Login Button -->
            <a href="<?php echo baseUrl('login'); ?>"
               class="w-56 text-center no-underline bg-white hover:bg-gray-100 text-black border-2 border-black/30 py-3.5 px-8 rounded-lg text-xl font-bold transition-all duration-300 shadow-[0_4px_20px_rgba(0,0,0,0.5)] hover:-translate-y-1 flex items-center justify-center gap-3">
                <i class="fa-solid fa-right-to-bracket text-[#002b49]"></i> Login
            </a>

            <!-- Register Button -->
            <a href="<?php echo baseUrl('register'); ?>"
               class="w-56 text-center no-underline bg-white hover:bg-gray-100 text-black border-2 border-black/30 py-3.5 px-8 rounded-lg text-xl font-bold transition-all duration-300 shadow-[0_4px_20px_rgba(0,0,0,0.5)] hover:-translate-y-1 flex items-center justify-center gap-3">
                <i class="fa-solid fa-user-plus text-[#652c16]"></i> Register
            </a>

        </div>

    </div>

    <!-- Footer -->
    <div class="absolute bottom-5 text-gray-300 text-sm z-10 [text-shadow:2px_2px_5px_black] tracking-wider text-center w-full">
        &copy; <?php echo date('Y'); ?> Railway Quarter Management System - AOT. All Rights Reserved.
    </div>

</body>
</html>