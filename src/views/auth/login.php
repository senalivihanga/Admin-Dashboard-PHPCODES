<?php
/**
 * Login Page with Security Features
 */
require_once __DIR__ . '/../../../config/config.php';

// Session is already started in index.php

$pageTitle = 'Login - Quarter Management System';

// If already logged in, redirect to dashboard
if (isset($_SESSION['nic']) && isset($_SESSION['session_valid'])) {
    header('Location: ' . baseUrl('dashboard'));
    exit();
}

$error = '';
$showCaptcha = false;

// Generate CSRF token function
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $auth = new AuthController();
    
    $nic = trim($_POST['nic'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Security token validation failed. Please try again.';
    } else {
        $result = $auth->login($nic, $password);
        if ($result['success']) {
            // Redirect to intended page or dashboard
            $redirect = $_SESSION['redirect_after_login'] ?? '/dashboard';
            unset($_SESSION['redirect_after_login']);
            
            // Clean the redirect path to avoid duplication
            $redirect = ltrim($redirect, '/');
            // If the redirect is empty or just 'dashboard', use dashboard
            if (empty($redirect) || $redirect === 'dashboard' || $redirect === '/') {
                $redirect = 'dashboard';
            }
            
            header('Location: ' . baseUrl($redirect));
            exit();
        } else {
            $error = $result['message'];
            // Show captcha after 3 failed attempts (client-side tracking)
            $showCaptcha = isset($_POST['attempt_count']) && $_POST['attempt_count'] >= 2;
        }
    }
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full border-t-4 border-[#b59410]">
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="<?php echo assetUrl('images/logo.png'); ?>" alt="Railway Logo" class="h-20 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-[#5c060d]">SRI LANKA RAILWAY</h1>
            <p class="text-sm text-gray-500">Quarter Management System</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded-lg text-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['registered']) && $_GET['registered'] === 'success'): ?>
            <div class="mb-4 p-3 bg-green-50 text-green-700 border border-green-200 rounded-lg text-center">
                <i class="fas fa-check-circle mr-2"></i>
                Registration successful! Please login.
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo baseUrl('login'); ?>" id="loginForm" class="space-y-4">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="attempt_count" id="attemptCount" value="0">
            
            <div>
                <label for="nic" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-id-card mr-2"></i> NIC Number
                </label>
                <input type="text" 
                       id="nic" 
                       name="nic" 
                       placeholder="Enter your NIC" 
                       value="<?php echo htmlspecialchars($_POST['nic'] ?? ''); ?>"
                       required
                       autocomplete="username"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#b59410] focus:border-transparent transition">
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2"></i> Password
                </label>
                <div class="relative">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Enter your password"
                           required
                           autocomplete="current-password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#b59410] focus:border-transparent transition pr-12">
                    <button type="button" 
                            onclick="togglePasswordVisibility()" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="passwordToggleIcon" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <?php if ($showCaptcha): ?>
            <div class="captcha-container bg-gray-50 p-3 rounded-lg border border-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-mono" id="captchaText"><?php echo rand(1000, 9999); ?></span>
                    <button type="button" onclick="refreshCaptcha()" class="text-sm text-[#5c060d] hover:underline">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                    </button>
                </div>
                <input type="text" 
                       name="captcha" 
                       placeholder="Enter the code above" 
                       class="w-full mt-2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#b59410] focus:border-transparent transition">
                <input type="hidden" name="captcha_hash" id="captchaHash">
            </div>
            <?php endif; ?>

            <button type="submit" 
                    class="w-full bg-[#5c060d] hover:bg-[#4a050a] text-white font-semibold py-3 rounded-lg transition shadow-sm hover:shadow-md hover:text-amber-300"
                    id="loginButton">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-gray-200 text-center space-y-3">
            <p class="text-sm text-gray-600">
                Don't have an account? 
                <a href="<?php echo baseUrl('register'); ?>" class="text-[#5c060d] hover:text-[#b59410] font-semibold">
                    Register here
                </a>
            </p>
            <p class="text-xs text-gray-400">Default test credentials:</p>
            <p class="text-xs text-gray-400 font-mono">NIC: 199012345678 | Password: password123</p>
        </div>
    </div>

    <script>
        let attemptCount = 0;
        
        // Toggle password visibility
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('passwordToggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
        
        // Track login attempts
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            attemptCount++;
            document.getElementById('attemptCount').value = attemptCount;
        });
        
        // Refresh captcha
        function refreshCaptcha() {
            const captchaText = document.getElementById('captchaText');
            const newCode = Math.floor(1000 + Math.random() * 9000);
            captchaText.textContent = newCode;
        }
        
        // Prevent multiple submissions
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const button = document.getElementById('loginButton');
            if (button.disabled) {
                e.preventDefault();
                return;
            }
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Logging in...';
        });
    </script>

</body>
</html>