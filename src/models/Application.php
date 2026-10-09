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
        $computerNo = 'EMP' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $applicationDate = date('Y-m-d');
        
        $sql = "INSERT INTO {$this->table} (nic, computer_no, quarter_type, application_date, status) 
                VALUES (?, ?, ?, ?, 'pending')";
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
        $pos_sql = "SELECT (SELECT COUNT(*) 
                     FROM waiting_list a2 
                     WHERE (a2.applied_date < a1.applied_date) 
                        OR (a2.applied_date = a1.applied_date AND a2.employee_marks > a1.employee_marks)
                        OR (a2.applied_date = a1.applied_date AND a2.employee_marks = a1.employee_marks AND a2.id <= a1.id)
                     ) AS calculated_position
                     FROM waiting_list a1 
                     WHERE a1.nic = ?";
        
        $pos_stmt = $this->conn->prepare($pos_sql);
        $pos_stmt->bind_param("s", $nic);
        $pos_stmt->execute();
        $pos_result = $pos_stmt->get_result();
        $position = null;
        
        if ($pos_row = $pos_result->fetch_assoc()) {
            $position = $pos_row['calculated_position'];
        }
        
        $application['position'] = $position;
        return $application;
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>