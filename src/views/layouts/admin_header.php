<?php
/**
 * Admin Header Layout
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = 'Admin Name';
}

$admin_name = $_SESSION['admin_name'];

// Initialize $user array if not already set, using session admin_name[cite: 2]
if (!isset($user) || !isset($user['name'])) {
    $user = ['name' => $admin_name];
}

$pageTitle = $pageTitle ?? 'Admin Dashboard - Sri Lanka Railway';

// Agar page ne manually $hide_admin_extras set nahi kiya hai, tabhi automatic check chale[cite: 2]
if (!isset($hide_admin_extras)) {
    $hide_admin_extras = (basename($_SERVER['PHP_SELF']) !== 'dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
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
<body class="bg-gray-50 font-sans antialiased text-gray-800 m-0 p-0 flex flex-col min-h-screen">

    <!-- Top Dark Red Header Bar for Admin -->
    <header class="bg-[#5c060d] text-white py-4 px-6 md:px-12 shadow-md flex flex-col md:flex-row justify-between items-center relative border-b-4 border-[#b59410]">
        <!-- Logo and Titles -->
        <div class="flex items-center space-x-4 mb-4 md:mb-0">
            <img src="<?php echo assetUrl('images/logo.png'); ?>" alt="Sri Lanka Railway Logo" class="slr-logo">
            <div>
                <h1 class="text-xl md:text-2xl font-bold tracking-wider">SRI LANKA RAILWAY</h1>
                <h2 class="text-sm md:text-base font-semibold tracking-wide text-amber-200">QUARTER MANAGEMENT SYSTEM</h2>
            </div>
        </div>

        <?php if (!$hide_admin_extras): ?>
        <!-- Centered Admin Dashboard Title -->
        <div class="hidden lg:block absolute left-1/2 transform -translate-x-1/2">
            <span class="text-xl font-extrabold tracking-wide uppercase m-0">ADMIN DASHBOARD</span>
        </div>

        <!-- Profile Dropdown Section -->
        <div class="flex items-center space-x-6">
            <?php if (isset($user) && isset($user['name'])): ?>
            <div class="flex items-center space-x-4">
                <!-- Profile Dropdown Container -->
                <div class="relative profile-dropdown-container">
                    <button onclick="toggleAdminDropdown()" class="flex items-center space-x-3 border border-amber-500/50 rounded-full px-5 py-2.5 bg-[#4a050a] text-white shadow-sm hover:bg-[#6e0710] focus:outline-none transition">
                        <i class="fa-solid fa-user text-amber-400 text-lg"></i>
                        <span class="text-base md:text-lg font-medium"><?php echo htmlspecialchars($user['name']); ?></span>
                        <i class="fa-solid fa-chevron-down text-xs text-amber-300"></i>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div id="adminProfileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white text-gray-800 border border-gray-200 rounded-lg shadow-lg py-1 z-50">
                        <a href="<?php echo baseUrl('admin/edit_profile.php'); ?>" class="block px-4 py-2 text-sm hover:bg-gray-100"><i class="fa-solid fa-pen-to-square mr-2 text-gray-600"></i> Edit Profile</a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="<?php echo baseUrl('logout'); ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100"><i class="fa-solid fa-right-from-bracket mr-2"></i> Logout</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </header>