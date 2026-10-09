<?php
/**
 * Logout Page
 */
require_once __DIR__ . '/../../../config/config.php';

// Session is already started in index.php
// Only start if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_destroy();

// Redirect to login page
redirect('/login');
exit();
?>