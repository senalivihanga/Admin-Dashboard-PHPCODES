<?php
/**
 * Header layout for all pages
 */
require_once __DIR__ . '/../../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Department of Railways - Quarter Management System'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo assetUrl('css/style.css'); ?>">
    <style>
        .slr-logo {
            height: 70px !important;
            width: auto !important;
            max-width: none;
            object-fit: contain;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-800 m-0 p-0">

    <!-- Top Dark Red Header Bar -->
    <header class="bg-[#5c060d] text-white py-4 px-6 md:px-12 shadow-md flex flex-col md:flex-row justify-between items-center relative border-b-4 border-[#b59410]">
        <!-- Logo and Titles -->
        <div class="flex items-center space-x-4 mb-4 md:mb-0">
            <img src="<?php echo assetUrl('images/logo.png'); ?>" alt="Sri Lanka Railway Logo" class="slr-logo">
            <div>
                <h1 class="text-xl md:text-2xl font-bold tracking-wider">SRI LANKA RAILWAY</h1>
                <h2 class="text-sm md:text-base font-semibold tracking-wide text-amber-200">QUARTER MANAGEMENT SYSTEM</h2>
            </div>
        </div>

        <!-- Top Right Section: Notification & Profile (Only shown if user is set AND $hideHeaderActions is not true) -->
        <?php if (isset($user) && isset($user['name']) && empty($hideHeaderActions)): ?>
        <div class="flex items-center space-x-4">
            <!-- Notification Bell with Larger Size & Badge -->
            <a href="<?php echo baseUrl('notifications'); ?>" class="relative inline-flex items-center justify-center p-1 text-white hover:text-amber-300 transition">
                <div class="relative w-11 h-11 flex items-center justify-center bg-[#4a050a] rounded-full border border-amber-500/50 shadow-sm hover:bg-[#6e0710]">
                    <i class="fa-solid fa-bell text-amber-400 text-base"></i>
                    <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full shadow-md border-2 border-[#5c060d]">
                            <?php echo $unreadCount; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </a>

            <!-- Profile Dropdown Container -->
            <div class="relative profile-dropdown-container">
                <button onclick="toggleDropdown()" class="flex items-center space-x-3 border border-amber-500/50 rounded-full px-5 py-2.5 bg-[#4a050a] text-white shadow-sm hover:bg-[#6e0710] focus:outline-none transition">
                    <img src="<?php echo assetUrl('images/user.png'); ?>" alt="User" class="w-7 h-7 rounded-full object-contain bg-white p-0.5">
                    <span class="text-base md:text-lg font-medium">Hi, <?php echo htmlspecialchars($user['name']); ?></span>
                    <i class="fa-solid fa-chevron-down text-xs text-amber-300"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white text-gray-800 border border-gray-200 rounded-lg shadow-lg py-1 z-50">
                    <a href="<?php echo baseUrl('logout'); ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                        <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </header>