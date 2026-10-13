<?php
/**
 * Complaint Controller
 */
require_once __DIR__ . '/../models/Complaint.php';

class ComplaintController {
    
    private $complaintModel;

    public function __construct() {
        $this->complaintModel = new Complaint();
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 1. Get form data
            $complaints = $_POST['complaints'] ?? '';
            $related_issue = $_POST['related_issue'] ?? '';
            
            // If 'other' is selected, use custom_issue
            if ($related_issue === 'other' && !empty($_POST['custom_issue'])) {
                $related_issue = trim($_POST['custom_issue']);
            }

            // Get logged-in user's NIC
            $applicantNic = $_SESSION['nic'] ?? null;
            $this->complaintModel->create($applicantNic, $complaints, $related_issue);

            // Redirect to dashboard
            header("Location: " . baseUrl('dashboard'));
            exit();
        }
    }
}