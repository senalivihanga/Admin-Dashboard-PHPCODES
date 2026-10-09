<?php
require_once __DIR__ . '/../../config/config.php';

class User {
    private $conn;
    private $table = 'users';
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Find user by NIC
     */
    public function findByNic($nic) {
        $sql = "SELECT * FROM {$this->table} WHERE nic = ? AND is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Check if NIC exists
     */
    public function nicExists($nic) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE nic = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    /**
     * Check if computer number exists
     */
    public function computerNumberExists($computerNumber) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE computer_number = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $computerNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    /**
     * Create new user with bcrypt password
     */
    public function create($data) {
        // Hash password with bcrypt (cost factor 12)
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        
        $sql = "INSERT INTO {$this->table} (nic, name, email, computer_number, mobile, password) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssss", 
            $data['nic'], 
            $data['name'], 
            $data['email'], 
            $data['computer_number'], 
            $data['mobile'], 
            $hashedPassword
        );
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'id' => $stmt->insert_id
            ];
        }
        return [
            'success' => false, 
            'error' => $this->conn->error
        ];
    }
    
    /**
     * Verify login credentials using bcrypt
     */
    public function verifyLogin($nic, $password) {
        $user = $this->findByNic($nic);
        
        if (!$user) {
            return false;
        }
        
        // Verify password using bcrypt
        if (password_verify($password, $user['password'])) {
            // Check if password needs rehashing
            if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => 12])) {
                $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $this->updatePassword($nic, $newHash);
            }
            return $user;
        }
        
        return false;
    }
    
    /**
     * Update user password
     */
    public function updatePassword($nic, $hashedPassword) {
        $sql = "UPDATE {$this->table} SET password = ? WHERE nic = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $hashedPassword, $nic);
        return $stmt->execute();
    }
    
    /**
     * Update user profile (simple version - keeps existing signature)
     */
    public function updateProfile($nic, $name, $email, $mobile = null) {
        $sql = "UPDATE {$this->table} SET name = ?, email = ?" . 
               ($mobile ? ", mobile = ?" : "") . 
               " WHERE nic = ?";
        
        if ($mobile) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssss", $name, $email, $mobile, $nic);
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sss", $name, $email, $nic);
        }
        
        return $stmt->execute();
    }
    
    /**
     * NEW: Update full user profile with all fields including password
     * This method is called by the enhanced edit_profile.php
     */
    public function updateFullProfile($currentNic, $name, $nic, $email, $computer_no, $mobile, $password = null) {
        if ($password && !empty($password)) {
            // With password update
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $sql = "UPDATE {$this->table} SET 
                    name = ?, 
                    nic = ?, 
                    email = ?, 
                    computer_number = ?, 
                    mobile = ?, 
                    password = ? 
                    WHERE nic = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssssss", $name, $nic, $email, $computer_no, $mobile, $hashedPassword, $currentNic);
        } else {
            // Without password update
            $sql = "UPDATE {$this->table} SET 
                    name = ?, 
                    nic = ?, 
                    email = ?, 
                    computer_number = ?, 
                    mobile = ? 
                    WHERE nic = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssss", $name, $nic, $email, $computer_no, $mobile, $currentNic);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Rate limit login attempts
     */
    public function getLoginAttempts($nic) {
        $sql = "SELECT COUNT(*) as count FROM login_attempts 
                WHERE nic = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
    
    /**
     * Log login attempt
     */
    public function logLoginAttempt($nic, $ip, $success) {
        // Create login_attempts table if not exists
        $this->createLoginAttemptsTable();
        
        $sql = "INSERT INTO login_attempts (nic, ip_address, success, attempt_time) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $nic, $ip, $success);
        return $stmt->execute();
    }
    
    /**
     * Create login attempts table
     */
    private function createLoginAttemptsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nic VARCHAR(20) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            success TINYINT(1) DEFAULT 0,
            attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_nic_time (nic, attempt_time)
        )";
        $this->conn->query($sql);
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>