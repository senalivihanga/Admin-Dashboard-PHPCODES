<?php
require_once __DIR__ . '/../../config/config.php';

class Offer {
    private $conn;
    private $table = 'respond_to_offer';
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Get active offer for user
     */
    public function getActiveOffer($nic) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE nic = ? AND status IN ('approved', 'accepted') 
                ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Accept offer
     */
    public function accept($id, $nic) {
        $sql = "UPDATE {$this->table} SET status = 'accepted' WHERE id = ? AND nic = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $id, $nic);
        return $stmt->execute();
    }
    
    /**
     * Postpone offer
     */
    public function postpone($id, $nic) {
        $sql = "UPDATE {$this->table} SET created_at = NOW(), status = 'pending' WHERE id = ? AND nic = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $id, $nic);
        return $stmt->execute();
    }
    
    /**
     * Deny offer
     */
    public function deny($id, $nic) {
        $sql = "DELETE FROM {$this->table} WHERE id = ? AND nic = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $id, $nic);
        return $stmt->execute();
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>