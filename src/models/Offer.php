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

    public function getByNic($nic) {
        $sql = "SELECT * FROM {$this->table} WHERE nic = ? ORDER BY created_at DESC, id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $offers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $offers;
    }
    
    /**
     * Accept offer
     */
    public function accept($id, $nic) {
        $sql = "UPDATE {$this->table} SET status = 'accepted' WHERE id = ? AND nic = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $id, $nic);
        $saved = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        return $saved;
    }
    
    /**
     * Postpone offer
     */
    public function postpone($id, $nic) {
        $sql = "UPDATE {$this->table} SET created_at = NOW(), status = 'pending' WHERE id = ? AND nic = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $id, $nic);
        $saved = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        return $saved;
    }
    
    /**
     * Deny offer
     */
    public function deny($id, $nic) {
        $sql = "DELETE FROM {$this->table} WHERE id = ? AND nic = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $id, $nic);
        $saved = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();
        return $saved;
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>