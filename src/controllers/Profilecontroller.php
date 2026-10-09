<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/AuthController.php';

class ProfileController {
    private $auth;
    private $userModel;
    
    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->requireLogin();
        $this->userModel = new User();
    }
    
    /**
     * Get user profile
     */
    public function getProfile() {
        $nic = $_SESSION['nic'];
        return $this->userModel->findByNic($nic);
    }
    
    /**
     * Update full profile with all fields
     */
    public function updateProfile($name, $nic, $email, $computer_no, $mobile, $password = null) {
        $currentNic = $_SESSION['nic'];
        
        // Check if NIC is being changed and if it already exists
        if ($nic !== $currentNic && $this->userModel->nicExists($nic)) {
            return ['success' => false, 'message' => 'NIC already registered to another user'];
        }
        
        // Check if email already exists (if changed)
        $userData = $this->userModel->findByNic($currentNic);
        if ($email !== $userData['email'] && $this->userModel->emailExists($email)) {
            return ['success' => false, 'message' => 'Email already registered to another user'];
        }
        
        // Check if computer number already exists (if changed)
        if ($computer_no !== $userData['computer_number'] && $this->userModel->computerNumberExists($computer_no)) {
            return ['success' => false, 'message' => 'Computer Number already registered to another user'];
        }
        
        // Update the profile
        $result = $this->userModel->updateFullProfile($currentNic, $name, $nic, $email, $computer_no, $mobile, $password);
        
        if ($result) {
            // Update session if NIC changed
            if ($nic !== $currentNic) {
                $_SESSION['nic'] = $nic;
            }
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['computer_number'] = $computer_no;
            
            return ['success' => true, 'message' => 'Profile updated successfully!'];
        }
        
        return ['success' => false, 'message' => 'Error updating profile'];
    }
}
?>