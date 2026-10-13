<?php
require_once __DIR__ . '/../../config/config.php';

class Complaint {
    private $conn;

    public function __construct() {
        $this->conn = getDBConnection();
    }

    public function create($nic, $details, $relatedIssue) {
        $stmt = $this->conn->prepare("INSERT INTO complaints (user_id, complaints, related_issue, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->bind_param('sss', $nic, $details, $relatedIssue);
        $saved = $stmt->execute();
        $complaintId = $stmt->insert_id;
        $stmt->close();

        if (!$saved) {
            return false;
        }

        $notificationMessage = 'New complaint received regarding: ' . $relatedIssue;
        $notificationStmt = $this->conn->prepare("INSERT INTO notifications (nic, title, message, is_read) SELECT nic, 'New Complaint', ?, 0 FROM users WHERE role = 'subject_clerk'");
        $notificationStmt->bind_param('s', $notificationMessage);
        $notificationStmt->execute();
        $notificationStmt->close();

        return $complaintId;
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
