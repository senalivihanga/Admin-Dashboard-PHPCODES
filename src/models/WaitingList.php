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
        $sql = "INSERT INTO {$this->table} (nic, position, quarter_type, applied_date) VALUES (?, ?, ?, CURDATE())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sis", $nic, $position, $quarterType);
        return $stmt->execute();
    }
    
    /**
     * Get position for applicant
     */
    public function getPosition($nic, $quarterType = null) {
        if ($quarterType !== null) {
            $sql = "SELECT position FROM {$this->table} WHERE nic = ? AND quarter_type = ? ORDER BY id DESC LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $nic, $quarterType);
        } else {
            $sql = "SELECT position FROM {$this->table} WHERE nic = ? ORDER BY id DESC LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $nic);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['position'] : null;
    }

    public function getLatestForNic($nic) {
        $sql = "SELECT position, quarter_type, applied_date, designation_score, employee_marks
                FROM {$this->table} WHERE nic = ? ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row;
    }
    
    /**
     * Get waiting list with positions.
     * `position` = the row's own id, so a lower number = earlier in the queue
     * (oldest applicant first, newest applicant last).
     */
    public function getWaitingListWithPositions() {
        $sql = "SELECT nic, quarter_type, applied_date, employee_marks, id AS position
                FROM waiting_list
                ORDER BY id ASC";
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