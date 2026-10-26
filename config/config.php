<?php
/**
 * Central Configuration - Change ONLY this file when moving to another computer
 * ============================================================================
 * TO RUN ON ANOTHER COMPUTER, CHANGE ONLY THE `BASE_URL` VALUE BELOW!
 * ============================================================================
 */

// ==========================================
// 0. PHP 7 COMPATIBILITY POLYFILLS
// ==========================================
// str_contains() / str_starts_with() / str_ends_with() were added in PHP 8.0.
// These polyfills let the same code run safely on PHP 7.x hosts.
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        if ($needle === '') return true;
        return substr($haystack, -strlen($needle)) === $needle;
    }
}

// ==========================================
// 1. BASE URL - CHANGE THIS ONE VALUE ONLY!
// ==========================================
// Examples:
//   - If your project is at: C:\xampp\htdocs\myproject\public
//     Set: define('BASE_URL', '/myproject');
//   
//   - If your project is at: C:\xampp\htdocs\QMS\applicants_dashboard\public
//     Set: define('BASE_URL', '/QMS/applicants_dashboard');
//   
//   - If your project is at the root: C:\xampp\htdocs\public
//     Set: define('BASE_URL', '');
//   
//   - If using virtual host: http://myapp.test
//     Set: define('BASE_URL', '');
//
// IMPORTANT: never put a space in this value, or in the folder name it
// points to under htdocs. A space gets URL-encoded to %20 in the browser,
// which breaks the string matching in public/index.php and causes an
// infinite redirect loop (ERR_TOO_MANY_REDIRECTS).

define('BASE_URL', '/Quarters_Management_System-V2/Quarters_Management_System-V2');  // ← CHANGE THIS ONLY! (must match your htdocs folder, no spaces)
//http://localhost/Quarters_Management_System-V2/Quarters_Management_System-V2/



// ==========================================
// 2. DATABASE CONFIGURATION
// ==========================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'qms_database_updated');

// ==========================================
// 3. APPLICATION SETTINGS
// ==========================================
define('APP_NAME', 'Quarter Management System');
define('APP_VERSION', '1.0.0');

// ==========================================
// 4. SECURITY SETTINGS
// ==========================================
// Password hashing
define('PASSWORD_COST', 12);

// Session security
define('SESSION_LIFETIME', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// CSRF protection
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// Password requirements
define('MIN_PASSWORD_LENGTH', 8);

// ==========================================
// 5. FUNCTIONS
// ==========================================

/**
 * Get the full base URL
 */
function baseUrl($path = '') {
    return BASE_URL . '/public/' . ltrim($path, '/');
}

/**
 * Get asset URL
 */
function assetUrl($path = '') {
    return BASE_URL . '/public/assets/' . ltrim($path, '/');
}

/**
 * Get database connection
 * @return mysqli
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die(json_encode([
            'success' => false,
            'error' => 'Database Connection Failed: ' . $conn->connect_error
        ]));
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Redirect to a URL
 */
function redirect($path = '') {
    header('Location: ' . baseUrl($path));
    exit();
}

/**
 * Get current URL
 */
function currentUrl() {
    return (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . 
           $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Check whether the current session is completing a direct allocation.
 */
function isDirectAllocationSession() {
    return !empty($_SESSION['direct_alloc']) && is_array($_SESSION['direct_alloc']);
}

/**
 * Clear the direct-allocation session marker.
 */
function clearDirectAllocationSession() {
    unset($_SESSION['direct_alloc']);
}
?>