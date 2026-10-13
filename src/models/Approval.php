<?php
require_once __DIR__ . '/../../config/config.php';

class Approval {
    private $conn;

    public function __construct() {
        $this->conn = getDBConnection();
    }

    public function saveClerkRecommendation($applicationId, $data, $nic) {
        $sql = "INSERT INTO clerk_recommendations (
                    application_id, clerk_is_right_forfeited, clerk_forfeit_expiry_date,
                    clerk_is_mihindusethpura_recipient, clerk_residing_in_other_gov_quarter,
                    clerk_other_remarks, clerk_cabinet_number, clerk_recommendation_status,
                    clerk_recommended_at, clerk_signed_by_nic, clerk_signed_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'recommended', NOW(), ?, NOW())
                ON DUPLICATE KEY UPDATE
                    clerk_is_right_forfeited = VALUES(clerk_is_right_forfeited),
                    clerk_forfeit_expiry_date = VALUES(clerk_forfeit_expiry_date),
                    clerk_is_mihindusethpura_recipient = VALUES(clerk_is_mihindusethpura_recipient),
                    clerk_residing_in_other_gov_quarter = VALUES(clerk_residing_in_other_gov_quarter),
                    clerk_other_remarks = VALUES(clerk_other_remarks),
                    clerk_cabinet_number = VALUES(clerk_cabinet_number),
                    clerk_recommendation_status = 'recommended', clerk_recommended_at = NOW(),
                    clerk_signed_by_nic = VALUES(clerk_signed_by_nic), clerk_signed_at = NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            'issiisss',
            $applicationId,
            $data['forfeited'],
            $data['expiry_date'],
            $data['mihindu_recipient'],
            $data['other_quarter'],
            $data['remarks'],
            $data['cabinet_number'],
            $nic
        );
        $result = $stmt->execute();
        $stmt->close();
        if (!$result) {
            return false;
        }

        $statusStmt = $this->conn->prepare("UPDATE applications SET clerk_status = 'approved', application_status = 'pending_super' WHERE id = ?");
        $statusStmt->bind_param('i', $applicationId);
        $result = $statusStmt->execute();
        $statusStmt->close();
        return $result;
    }

    public function saveSuperintendentRecommendation($applicationId, $data, $nic) {
        $sql = "INSERT INTO superintendent_recommendations (
                    application_id, super_quarter_type_and_number,
                    super_essentiality_and_remarks, super_approval_date,
                    super_recommendation_status, super_recommended_at,
                    super_signed_by_nic, super_signed_at
                ) VALUES (?, ?, ?, ?, 'recommended', NOW(), ?, NOW())
                ON DUPLICATE KEY UPDATE
                    super_quarter_type_and_number = VALUES(super_quarter_type_and_number),
                    super_essentiality_and_remarks = VALUES(super_essentiality_and_remarks),
                    super_approval_date = VALUES(super_approval_date),
                    super_recommendation_status = 'recommended', super_recommended_at = NOW(),
                    super_signed_by_nic = VALUES(super_signed_by_nic), super_signed_at = NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            'issss',
            $applicationId,
            $data['quarter_type_number'],
            $data['remarks'],
            $data['approval_date'],
            $nic
        );
        $result = $stmt->execute();
        $stmt->close();
        if (!$result) {
            return false;
        }

        $statusStmt = $this->conn->prepare("UPDATE applications SET branch_status = 'approved', application_status = 'approved' WHERE id = ?");
        $statusStmt->bind_param('i', $applicationId);
        $result = $statusStmt->execute();
        $statusStmt->close();
        return $result;
    }

    public function saveEvaluationMarks($applicationId, $scores, $nic) {
        $total = array_sum($scores);
        $sql = "INSERT INTO evaluation_marks (
                    application_id, mark_waiting_list_duration, mark_child_count,
                    mark_housing_condition, mark_distance_above_30km,
                    mark_applicant_serious_illness, mark_dependent_serious_illness,
                    mark_marital_status, mark_tie_breaker_service_years,
                    total_evaluation_marks, marks_evaluated_by_nic
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    mark_waiting_list_duration = VALUES(mark_waiting_list_duration),
                    mark_child_count = VALUES(mark_child_count),
                    mark_housing_condition = VALUES(mark_housing_condition),
                    mark_distance_above_30km = VALUES(mark_distance_above_30km),
                    mark_applicant_serious_illness = VALUES(mark_applicant_serious_illness),
                    mark_dependent_serious_illness = VALUES(mark_dependent_serious_illness),
                    mark_marital_status = VALUES(mark_marital_status),
                    mark_tie_breaker_service_years = VALUES(mark_tie_breaker_service_years),
                    total_evaluation_marks = VALUES(total_evaluation_marks),
                    marks_evaluated_at = NOW(), marks_evaluated_by_nic = VALUES(marks_evaluated_by_nic)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            'iiiiiiiiiis',
            $applicationId,
            $scores[0], $scores[1], $scores[2], $scores[3],
            $scores[4], $scores[5], $scores[6], $scores[7],
            $total, $nic
        );
        $result = $stmt->execute();
        $stmt->close();
        if (!$result) {
            return false;
        }

        $applicationStmt = $this->conn->prepare('UPDATE applications SET marks = ? WHERE id = ?');
        $applicationStmt->bind_param('ii', $total, $applicationId);
        $result = $applicationStmt->execute();
        $applicationStmt->close();
        if (!$result) {
            return false;
        }

        $waitingStmt = $this->conn->prepare("UPDATE waiting_list SET employee_marks = ? WHERE nic = (SELECT nic FROM applications WHERE id = ?) ORDER BY id DESC LIMIT 1");
        $waitingStmt->bind_param('ii', $total, $applicationId);
        $result = $waitingStmt->execute();
        $waitingStmt->close();
        return $result;
    }

    public function applicationExists($applicationId) {
        $stmt = $this->conn->prepare('SELECT id FROM applications WHERE id = ?');
        $stmt->bind_param('i', $applicationId);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows === 1;
        $stmt->close();
        return $exists;
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
