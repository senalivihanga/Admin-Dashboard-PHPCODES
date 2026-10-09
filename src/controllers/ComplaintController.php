<?php
/**
 * Complaint Controller
 */
class ComplaintController {
    
    private $db;

    public function __construct() {
        // Direct PDO database connection configuration
        try {
            $host = defined('DB_HOST') ? DB_HOST : 'localhost';
            $dbname = defined('DB_NAME') ? DB_NAME : 'quarters_db';
            $username = defined('DB_USER') ? DB_USER : 'root';
            $password = defined('DB_PASS') ? DB_PASS : '';

            $this->db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
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
            $applicant_nic = $_SESSION['nic'] ?? null; 

            // 2. Insert into complaints table
            $query = "INSERT INTO complaints (user_id, complaints, related_issue, status, created_at) 
            VALUES (?, ?, ?, 'Pending', NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$applicant_nic, $complaints, $related_issue]);
            
            $complaint_id = $this->db->lastInsertId();

            // 3. Send notification to Subject Clerk
            try {
                $notification_message = "New complaint received regarding: " . $related_issue;
                
                $notif_query = "INSERT INTO notifications (nic, message, is_read, created_at) 
                                SELECT nic, ?, 0, NOW() FROM users WHERE role = 'subject_clerk'";
                $notif_stmt = $this->db->prepare($notif_query);
                $notif_stmt->execute([$notification_message]);
            } catch (Exception $e) {
                // Notification error doesn't stop the complaint
            }

            // 4. Redirect to dashboard
            header("Location: " . baseUrl('dashboard'));
            exit();
        }
    }
}