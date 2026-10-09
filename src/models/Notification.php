<?php
require_once __DIR__ . '/../../config/config.php';

class Notification {
    private $conn;
    private $table = 'notifications';
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Create notification
     */
    public function create($nic, $title, $message) {
        $sql = "INSERT INTO {$this->table} (nic, title, message, is_read) VALUES (?, ?, ?, 0)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $nic, $title, $message);
        return $stmt->execute();
    }
    
    /**
     * Get all notifications for user
     */
    public function getByNic($nic) {
        $sql = "SELECT * FROM {$this->table} WHERE nic = ? ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get unread count
     */
    public function getUnreadCount($nic) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE nic = ? AND is_read = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nic);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
    
    /**
     * Mark as read
     */
    public function markAsRead($id, $nic) {
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE id = ? AND nic = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $id, $nic);
        return $stmt->execute();
    }
    
    /**
     * Delete notification
     */
    public function delete($id, $nic) {
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