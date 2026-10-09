<?php
require_once __DIR__ . '/../../config/config.php';

class DropdownModel {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Get all active quarter types
     */
    public function getQuarterTypes($language = 'en') {
        $langField = $language === 'si' ? 'type_name_si' : 'type_name';
        $sql = "SELECT id, {$langField} as name FROM quarter_types WHERE is_active = 1 ORDER BY sort_order ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get all active designations
     */
    public function getDesignations($language = 'en') {
        $langField = $language === 'si' ? 'designation_name_si' : 'designation_name';
        $sql = "SELECT id, {$langField} as name FROM designations WHERE is_active = 1 ORDER BY sort_order ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get all active sub-departments
     */
    public function getSubDepartments($language = 'en') {
        $langField = $language === 'si' ? 'dept_name_si' : 'dept_name';
        $sql = "SELECT id, {$langField} as name FROM sub_departments WHERE is_active = 1 ORDER BY sort_order ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get all active districts
     */
    public function getDistricts($language = 'en') {
        $langField = $language === 'si' ? 'district_name_si' : 'district_name';
        $sql = "SELECT id, {$langField} as name FROM districts WHERE is_active = 1 ORDER BY sort_order ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}