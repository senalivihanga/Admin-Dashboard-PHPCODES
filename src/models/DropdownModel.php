<?php
require_once __DIR__ . '/../../config/config.php';

class DropdownModel {
    private $conn;

    public function __construct() {
        $this->conn = getDBConnection();
    }

    /**
     * Shared helper for the simple lookup tables.
     * Returns rows as: id, name (label in the chosen language), value (English name stored in the DB).
     * Table and column names are fixed in the methods below - never pass user input here.
     */
    private function getLookup($table, $column, $columnSi, $language = 'en') {
        $langField = $language === 'si' ? $columnSi : $column;
        $sql = "SELECT id, {$langField} AS name, {$column} AS value
                FROM {$table} WHERE is_active = 1 ORDER BY sort_order ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all active quarter types
     */
    public function getQuarterTypes($language = 'en') {
        return $this->getLookup('quarter_types', 'type_name', 'type_name_si', $language);
    }

    /**
     * Get all active designations
     */
    public function getDesignations($language = 'en') {
        return $this->getLookup('designations', 'designation_name', 'designation_name_si', $language);
    }

    /**
     * Get all active sub-departments
     */
    public function getSubDepartments($language = 'en') {
        return $this->getLookup('sub_departments', 'dept_name', 'dept_name_si', $language);
    }

    /**
     * Get all active districts
     */
    public function getDistricts($language = 'en') {
        return $this->getLookup('districts', 'district_name', 'district_name_si', $language);
    }

    /**
     * Get all active quarter locations (applied_quarter_location)
     */
    public function getQuarterLocations($language = 'en') {
        return $this->getLookup('quarter_locations', 'location_name', 'location_name_si', $language);
    }

    /**
     * Get all active work stations (applicant_current_work_station)
     */
    public function getWorkStations($language = 'en') {
        return $this->getLookup('work_stations', 'station_name', 'station_name_si', $language);
    }

    /**
     * Get all active salary account branches (applicant_salary_account_branch)
     */
    public function getSalaryAccountBranches($language = 'en') {
        return $this->getLookup('salary_account_branches', 'branch_name', 'branch_name_si', $language);
    }

    /**
     * Get all active personal file offices (applicant_personal_file_office)
     */
    public function getPersonalFileOffices($language = 'en') {
        return $this->getLookup('personal_file_offices', 'office_name', 'office_name_si', $language);
    }

    /**
     * Get all active quarters (applied_quarter_number). The form filters them
     * by the selected location in JavaScript.
     */
    public function getQuarters() {
        $sql = "SELECT quarter_number, location_name, quarter_type
                FROM quarters WHERE is_active = 1
                ORDER BY sort_order ASC, quarter_number ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all active quarter numbers (applied_quarter_number dropdown)
     */
    public function getQuarterNumbers($language = 'en') {
        $sql = "SELECT id, quarter_number AS name, quarter_number AS value
                FROM quarter_numbers WHERE is_active = 1 ORDER BY sort_order ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}