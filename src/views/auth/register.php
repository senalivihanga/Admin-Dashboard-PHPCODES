<?php
/**
 * Register Page with Security Features
 */
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$pageTitle = 'Register - Quarter Management System';

// If already logged in, redirect to dashboard
if (isset($_SESSION['nic']) && isset($_SESSION['session_valid'])) {
    redirect('/dashboard');
    exit();
}

$error = '';
$success = '';
$formData = [];

// Handle registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $auth = new AuthController();
    
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Security token validation failed. Please try again.';
    } else {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'nic' => trim($_POST['nic'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'computer_number' => trim($_POST['computer_number'] ?? ''),
            'mobile' => trim($_POST['mobile'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? ''
        ];
        
        $formData = $data;
        $result = $auth->register($data);
        
        if ($result['success']) {
            // Clear form data
            $formData = [];
            // Redirect to login with success message
            redirect('/login?registered=success');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}

// Generate CSRF token
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
    <style>
        .password-strength {
            height: 4px;
            transition: width 0.3s;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center py-8">

    <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full border-t-4 border-[#b59410]">
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="<?php echo assetUrl('images/logo.png'); ?>" alt="Railway Logo" class="h-16 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-[#5c060d]">SRI LANKA RAILWAY</h1>
            <p class="text-sm text-gray-500">Create Account</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded-lg text-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="registerForm" class="space-y-4">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-user mr-2"></i> Full Name
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       placeholder="Enter your full name" 
                       value="<?php echo htmlspecialchars($formData['name'] ?? ''); ?>"
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#b59410] focus:border-transparent transition">
            </div>

            <!-- NIC -->
            <div>
                <label for="nic" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-id-card mr-2"></i> NIC Number
                </label>
                <input type="text" 
                       id="nic" 
                       name="nic" 
                       placeholder="Enter your NIC (9-12 digits)" 
                       value="<?php echo htmlspecialchars($formData['nic'] ?? ''); ?>"
                       required
                       pattern="[0-9]{9,12}"
                       title="NIC must be 9-12 digits"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#b59410] focus:border-transparent transition">
                <p class="text-xs text-gray-400 mt-1">Must be 9-12 digits</p>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2"></i> Email Address
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="Enter your email" 
                       value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#b59410] focus:border-transparent transition">
            </div>

            <!-- Computer Number -->
            <div>
                <label for="computer_number" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-desktop mr-2"></i> Computer Number
                </label>
                <input type="text" 
                       id="computer_number" 
                       name="computer_number" 
                       placeholder="Enter your computer number" 
                       value="<?php echo htmlspecialchars($formData['computer_number'] ?? ''); ?>"
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#b59410] focus:border-transparent transition">
            </div>

            <!-- Mobile -->
            <div>
                <label for="mobile" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-phone mr-2"></i> Mobile Number
                </label>
                <input type="tel" 
                       id="mobile" 
                       name="mobile" 
                       placeholder="Enter mobile number (10 digits)" 
                       value="<?php echo htmlspecialchars($formData['mobile'] ?? ''); ?>"
                       required
                       pattern="[0-9]{10}"
                       title="Mobile number must be 10 digits"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#b59410] focus:border-transparent transition">
                <p class="text-xs text-gray-400 mt-1">Must be 10 digits</p>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2"></i> Password
                </label>
                <div class="relative">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Create a password" 
                           required
                           minlength="8"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#b59410] focus:border-transparent transition pr-12">
                    <button type="button" 
                            onclick="togglePasswordVisibility('password')" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="passwordToggleIcon" class="fas fa-eye"></i>
                    </button>
                </div>
                
                <!-- Password Strength Indicator -->
                <div class="mt-2">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-gray-500">Password Strength:</span>
                        <span id="strengthText" class="text-xs font-semibold">Weak</span>
                    </div>
                    <div class="password-strength bg-gray-200 rounded-full h-1 overflow-hidden">
                        <div id="strengthBar" class="h-full bg-red-500 rounded-full" style="width: 0%"></div>
                    </div>
                    <ul id="passwordRequirements" class="text-xs text-gray-400 mt-2 space-y-1">
                        <li id="req-length" class="flex items-center gap-1">
                            <i class="fas fa-circle text-[8px]"></i> At least 8 characters
                        </li>
                        <li id="req-upper" class="flex items-center gap-1">
                            <i class="fas fa-circle text-[8px]"></i> One uppercase letter
                        </li>
                        <li id="req-lower" class="flex items-center gap-1">
                            <i class="fas fa-circle text-[8px]"></i> One lowercase letter
                        </li>
                        <li id="req-number" class="flex items-center gap-1">
                            <i class="fas fa-circle text-[8px]"></i> One number
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-check-circle mr-2"></i> Confirm Password
                </label>
                <div class="relative">
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           placeholder="Confirm your password" 
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#b59410] focus:border-transparent transition pr-12">
                    <button type="button" 
                            onclick="togglePasswordVisibility('confirm_password')" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="confirmPasswordToggleIcon" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Terms -->
            <div class="flex items-center">
                <input type="checkbox" 
                       id="terms" 
                       name="terms" 
                       required
                       class="w-4 h-4 text-[#5c060d] border-gray-300 rounded focus:ring-[#5c060d]">
                <label for="terms" class="ml-2 text-sm text-gray-600">
                    I agree to the <a href="#" class="text-[#5c060d] hover:underline">Terms and Conditions</a>
                </label>
            </div>

            <button type="submit" 
                    class="w-full bg-[#5c060d] hover:bg-[#4a050a] text-white font-semibold py-3 rounded-lg transition shadow-sm hover:shadow-md hover:text-amber-300"
                    id="registerButton">
                <i class="fas fa-user-plus mr-2"></i> Register
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-600">
                Already have an account? 
                <a href="<?php echo baseUrl('login'); ?>" class="text-[#5c060d] hover:text-[#b59410] font-semibold">
                    Login here
                </a>
            </p>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePasswordVisibility(fieldId) {
            const input = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + 'ToggleIcon');
            
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                if (icon) icon.className = 'fas fa-eye';
            }
        }

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        function checkPasswordStrength(password) {
            let score = 0;
            const checks = {
                length: password.length >= 8,
                upper: /[A-Z]/.test(password),
                lower: /[a-z]/.test(password),
                number: /[0-9]/.test(password)
            };
            
            // Update requirements list
            document.getElementById('req-length').innerHTML = 
                `<i class="fas fa-${checks.length ? 'check-circle text-green-500' : 'circle text-[8px]'}"></i> At least 8 characters`;
            document.getElementById('req-upper').innerHTML = 
                `<i class="fas fa-${checks.upper ? 'check-circle text-green-500' : 'circle text-[8px]'}"></i> One uppercase letter`;
            document.getElementById('req-lower').innerHTML = 
                `<i class="fas fa-${checks.lower ? 'check-circle text-green-500' : 'circle text-[8px]'}"></i> One lowercase letter`;
            document.getElementById('req-number').innerHTML = 
                `<i class="fas fa-${checks.number ? 'check-circle text-green-500' : 'circle text-[8px]'}"></i> One number`;
            
            // Calculate score
            if (checks.length) score++;
            if (checks.upper) score++;
            if (checks.lower) score++;
            if (checks.number) score++;
            
            // Update strength bar
            const percentage = (score / 4) * 100;
            strengthBar.style.width = percentage + '%';
            
            const colors = {
                0: 'bg-red-500',
                1: 'bg-red-400',
                2: 'bg-yellow-500',
                3: 'bg-blue-500',
                4: 'bg-green-500'
            };
            const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
            
            strengthBar.className = 'h-full rounded-full ' + (colors[score] || 'bg-gray-300');
            strengthText.textContent = labels[score] || '';
            strengthText.className = 'text-xs font-semibold ' + 
                (score >= 3 ? 'text-green-600' : score >= 2 ? 'text-yellow-600' : 'text-red-600');
            
            return checks;
        }

        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });

        // Confirm password validation
        confirmInput.addEventListener('input', function() {
            if (this.value && this.value !== passwordInput.value) {
                this.className = 'w-full px-4 py-3 border-2 border-red-500 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition pr-12';
            } else if (this.value && this.value === passwordInput.value) {
                this.className = 'w-full px-4 py-3 border-2 border-green-500 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition pr-12';
            } else {
                this.className = 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#b59410] focus:border-transparent transition pr-12';
            }
        });

        // Prevent multiple submissions
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const button = document.getElementById('registerButton');
            if (button.disabled) {
                e.preventDefault();
                return;
            }
            
            // Validate password match
            if (passwordInput.value !== confirmInput.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            // Validate terms
            if (!document.getElementById('terms').checked) {
                e.preventDefault();
                alert('Please agree to the Terms and Conditions');
                return;
            }
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Registering...';
        });
    </script>

</body>
</html>