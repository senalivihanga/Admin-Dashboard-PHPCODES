<?php
require_once __DIR__ . '/../../config/config.php';

class Application {
    private $conn;
    private $table = 'applications';
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Create new application
     */
    public function create($nic, $quarterType) {
        $userStmt = $this->conn->prepare("SELECT computer_number FROM users WHERE nic = ? AND is_active = 1");
        $userStmt->bind_param("s", $nic);
        $userStmt->execute();
        $user = $userStmt->get_result()->fetch_assoc();
        $userStmt->close();

        if (!$user || empty($user['computer_number'])) {
            return ['success' => false, 'error' => 'A registered computer number is required.'];
        }

        $computerNo = $user['computer_number'];
        $applicationDate = date('Y-m-d');
        
        $sql = "INSERT INTO {$this->table} (
                nic, computer_no, quarter_type, application_date, status,
                application_status, submit_status, application_submitted_at
            ) VALUES (?, ?, ?, ?, 'pending', 'submitted', 'success', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $nic, $computerNo, $quarterType, $applicationDate);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'computer_no' => $computerNo,
                'application_id' => $stmt->insert_id
            ];
        }
        return ['success' => false, 'error' => $this->conn->error];
    }
    
    /**
     * Get application by NIC
     */
    public function findByNic($nic) {
        $sql = "SELECT * FROM {$this->table} WHERE nic = ? ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Get application by computer number ONLY (for admin/search without NIC)
     */
    public function findByComputerNo($computerNo) {
        $sql = "SELECT * FROM {$this->table} WHERE computer_no = ? ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $computerNo);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Get application by computer number AND NIC (for user-specific search)
     */
    public function findByComputerNoAndNic($computerNo, $nic) {
        $sql = "SELECT * FROM {$this->table} WHERE computer_no = ? AND nic = ? ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $computerNo, $nic);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Get application with waiting list position
     */
    public function getApplicationWithPosition($nic) {
        $application = $this->findByNic($nic);
        if (!$application) {
            return null;
        }
        
        // Get waiting list position
          $pos_sql = "SELECT position FROM waiting_list WHERE nic = ? AND quarter_type = ? ORDER BY id DESC LIMIT 1";
        
        $pos_stmt = $this->conn->prepare($pos_sql);
          $pos_stmt->bind_param("ss", $nic, $application['quarter_type']);
        $pos_stmt->execute();
        $pos_result = $pos_stmt->get_result();
        $position = null;
        
        if ($pos_row = $pos_result->fetch_assoc()) {
            $position = $pos_row['position'];
        }
        
        $application['position'] = $position;
        return $application;
    }
    /** Save or update the current user's latest draft preference. */
        public function savePreference($nic, $residenceType, $formLanguage = null) {
        // If a rejected application is waiting to be fixed, don't create a new draft.
        $rejCheck = $this->conn->prepare(
            "SELECT id FROM {$this->table}
             WHERE nic = ? AND application_status = 'rejected'
             ORDER BY id DESC LIMIT 1"
        );
        $rejCheck->bind_param("s", $nic);
        $rejCheck->execute();
        $rejRow = $rejCheck->get_result()->fetch_assoc();
        $rejCheck->close();
        if ($rejRow) {
            return (int)$rejRow['id'];
        }

        $stmt = $this->conn->prepare(
            "SELECT id FROM {$this->table} WHERE nic = ? AND application_status = 'draft' ORDER BY id DESC LIMIT 1"
        );
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing) {
            if ($formLanguage !== null) {
                $sql = "UPDATE {$this->table} SET residence_type = ?, form_language = ? WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssi", $residenceType, $formLanguage, $existing['id']);
            } else {
                $sql = "UPDATE {$this->table} SET residence_type = ? WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("si", $residenceType, $existing['id']);
            }
            $stmt->execute();
            $stmt->close();
            return $existing['id'];
        }

        $computerNo = '';
        $userStmt = $this->conn->prepare(
            "SELECT computer_number FROM users WHERE nic = ? AND is_active = 1"
        );
        $userStmt->bind_param("s", $nic);
        $userStmt->execute();
        $userRow = $userStmt->get_result()->fetch_assoc();
        $userStmt->close();
        if ($userRow && !empty($userRow['computer_number'])) {
            $computerNo = $userRow['computer_number'];
        }

        $sql = "INSERT INTO {$this->table} (
                    nic, computer_no, quarter_type, application_date,
                    residence_type, form_language, application_status, status, submit_status
                ) VALUES (?, ?, '', CURDATE(), ?, ?, 'draft', 'pending', 'pending')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $nic, $computerNo, $residenceType, $formLanguage);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }

    /** Return the latest draft preference for a user, if one exists. */
    public function getPreference($nic) {
        $sql = "SELECT id, residence_type, form_language FROM {$this->table}
                WHERE nic = ? AND application_status = 'draft' ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

        /**
     * Find an application by its primary key (used for resubmission).
     */
    public function findById($id) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Find the latest rejected application for a NIC.
     */
    public function findRejectedByNic($nic) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM {$this->table}
             WHERE nic = ? AND application_status = 'rejected'
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    // >>> NEW: used by the applicant's View Status page to detect
    //          "the Final Approver just rejected my application".
    //          The application row itself will have been deleted, so we look
    //          at the notifications table for the marker title we insert on
    //          rejection in appli_approve_FA.php.
    public function hasUnseenFARejection($nic) {
        $sql = "SELECT message, created_at
                  FROM notifications
                 WHERE nic = ?
                   AND title = 'Application Rejected by Final Approval'
                   AND is_read = 0
                 ORDER BY id DESC
                 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    // >>> NEW: convenience wrapper that the View Status page will use
    //          to fetch the latest application ONLY if it still exists.
    //          Returns null when the FA has deleted it.
    public function findLatestForNic($nic) {
        return $this->findByNic($nic);   // already exists; alias for clarity
    }



    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>