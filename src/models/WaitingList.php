<?php
require_once __DIR__ . '/../../config/config.php';

class WaitingList {
    private $conn;
    private $table = 'waiting_list';
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Add applicant to waiting list
     */
    public function add($nic, $quarterType, $position) {
        $sql = "INSERT INTO {$this->table} (nic, position, quarter_type) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sis", $nic, $position, $quarterType);
        return $stmt->execute();
    }
    
    /**
     * Get position for applicant
     */
    public function getPosition($nic) {
        $sql = "SELECT position FROM {$this->table} WHERE nic = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['position'] : null;
    }
    
    /**
     * Get waiting list with calculated positions
     */
    public function getWaitingListWithPositions() {
        $sql = "SELECT 
                    a1.nic,
                    a1.quarter_type,
                    a1.applied_date,
                    a1.employee_marks,
                    (SELECT COUNT(*) 
                     FROM waiting_list a2 
                     WHERE (a2.applied_date < a1.applied_date) 
                        OR (a2.applied_date = a1.applied_date AND a2.employee_marks > a1.employee_marks)
                        OR (a2.applied_date = a1.applied_date AND a2.employee_marks = a1.employee_marks AND a2.id <= a1.id)
                    ) AS position
                FROM waiting_list a1 
                ORDER BY position ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>