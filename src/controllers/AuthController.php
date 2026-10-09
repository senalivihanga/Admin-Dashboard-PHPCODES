<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../config/config.php';

class AuthController {
    private $userModel;
    private $maxLoginAttempts = 5;
    private $lockoutTime = 900; // 15 minutes in seconds
    
    public function __construct() {
        $this->userModel = new User();
        // Session is already started in index.php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['session_created'])) {
            session_regenerate_id(true);
            $_SESSION['session_created'] = time();
        } elseif (time() - $_SESSION['session_created'] > 1800) {
            // Regenerate session every 30 minutes
            session_regenerate_id(true);
            $_SESSION['session_created'] = time();
        }
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['nic']) && isset($_SESSION['session_valid']);
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'nic' => $_SESSION['nic'],
                'name' => $_SESSION['user_name'] ?? 'User',
                'email' => $_SESSION['user_email'] ?? '',
                'computer_number' => $_SESSION['computer_number'] ?? ''
            ];
        }
        return null;
    }
    
    /**
     * Require login middleware
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            redirect('/login');
            exit();
        }
        
        // Validate session fingerprint
        if ($this->sessionFingerprintChanged()) {
            $this->logout();
            redirect('/login?error=session_expired');
            exit();
        }
    }
    
    /**
     * Session fingerprint for security
     */
    private function getSessionFingerprint() {
        return hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
    }
    
    /**
     * Check if session fingerprint has changed
     */
    private function sessionFingerprintChanged() {
        if (!isset($_SESSION['fingerprint'])) {
            return true;
        }
        return $_SESSION['fingerprint'] !== $this->getSessionFingerprint();
    }
    
    /**
     * Login user with security measures - FIXED VERSION
     */
    public function login($nic, $password) {
        // Sanitize input
        $nic = trim($nic);
        $password = trim($password);
        
        // Validate input
        if (empty($nic) || empty($password)) {
            $this->logAttempt($nic, false);
            return ['success' => false, 'message' => 'Please enter both NIC and Password'];
        }
        
        // Check for brute force
        $attempts = $this->userModel->getLoginAttempts($nic);
        if ($attempts >= $this->maxLoginAttempts) {
            return ['success' => false, 'message' => 'Too many login attempts. Please try again later.'];
        }
        
        // Verify credentials
        $user = $this->userModel->verifyLogin($nic, $password);
        
        if ($user) {
            // Login successful
            $_SESSION['nic'] = $user['nic'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['computer_number'] = $user['computer_number'] ?? '';
            $_SESSION['session_valid'] = true;
            $_SESSION['fingerprint'] = $this->getSessionFingerprint();
            $_SESSION['logged_in_at'] = time();
            
            // Regenerate session ID after login
            session_regenerate_id(true);
            
            // Log successful attempt
            $this->logAttempt($nic, true);
            
            return ['success' => true, 'message' => 'Login successful'];
        } else {
            // Login failed
            $this->logAttempt($nic, false);
            return ['success' => false, 'message' => 'Invalid NIC or Password'];
        }
    }
    
    /**
     * Log login attempt
     */
    private function logAttempt($nic, $success) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $this->userModel->logLoginAttempt($nic, $ip, $success);
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Clear session
        $_SESSION = array();
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        // Redirect to login
        redirect('/login');
        exit();
    }
    
    /**
     * Register new user with validation
     */
    public function register($data) {
        // Validate data
        $validation = $this->validateRegistration($data);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }
        
        // Check if NIC already exists
        if ($this->userModel->nicExists($data['nic'])) {
            return ['success' => false, 'message' => 'NIC already registered'];
        }
        
        // Check if email already exists
        if ($this->userModel->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Check if computer number already exists
        if ($this->userModel->computerNumberExists($data['computer_number'])) {
            return ['success' => false, 'message' => 'Computer Number already registered'];
        }
        
        // Create user
        $result = $this->userModel->create($data);
        
        if ($result['success']) {
            return ['success' => true, 'message' => 'Registration successful! Please login.'];
        } else {
            return ['success' => false, 'message' => 'Registration failed: ' . ($result['error'] ?? 'Unknown error')];
        }
    }
    
    /**
     * Validate registration data
     */
    private function validateRegistration($data) {
        // Required fields
        $required = ['name', 'nic', 'email', 'computer_number', 'mobile', 'password', 'confirm_password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['valid' => false, 'message' => 'All fields are required'];
            }
        }
        
        // Password match
        if ($data['password'] !== $data['confirm_password']) {
            return ['valid' => false, 'message' => 'Passwords do not match'];
        }
        
        // Password strength
        $password = $data['password'];
        if (strlen($password) < 8) {
            return ['valid' => false, 'message' => 'Password must be at least 8 characters'];
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter'];
        }
        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one lowercase letter'];
        }
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one number'];
        }
        
        // Email validation
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email address'];
        }
        
        // NIC validation (Sri Lankan NIC format)
        $nic = $data['nic'];
        if (!preg_match('/^[0-9]{9,12}$/', $nic)) {
            return ['valid' => false, 'message' => 'Invalid NIC format. Must be 9-12 digits'];
        }
        
        // Mobile validation
        $mobile = $data['mobile'];
        if (!preg_match('/^[0-9]{10}$/', $mobile)) {
            return ['valid' => false, 'message' => 'Invalid mobile number. Must be 10 digits'];
        }
        
        return ['valid' => true];
    }
}