<?php
require_once __DIR__ . '/../models/Approval.php';
require_once __DIR__ . '/AuthController.php';

class ApprovalController {
    private $approvalModel;
    private $auth;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->requireLogin();
        $this->approvalModel = new Approval();
    }

    public function submitClerk() {
        $applicationId = $this->getApplicationId();
        $yesNo = static function ($value) {
            return $value === 'Yes' ? 1 : ($value === 'No' ? 0 : null);
        };
        if (isset($_POST['is_another_residence_permanent'])) {
            $otherQuarter = $yesNo($_POST['is_another_residence_permanent']);
        } elseif (isset($_POST['is_not_another_residence_permanent'])) {
            $otherQuarter = $_POST['is_not_another_residence_permanent'] === 'No' ? 0 : null;
        } else {
            $otherQuarter = null;
        }
        $data = [
            'forfeited' => $yesNo($_POST['clerk_rec_forfeit'] ?? null),
            'expiry_date' => ($_POST['house_vacated_date'] ?? '') ?: null,
            'mihindu_recipient' => $yesNo($_POST['clerk_rec_waitlist'] ?? null),
            'other_quarter' => $otherQuarter,
            'remarks' => trim((string)($_POST['clerk_rec_other_notes'] ?? '')) ?: null,
            'cabinet_number' => trim((string)($_POST['clerk_reg_no'] ?? '')) ?: null
        ];
        $this->saveAndRedirect(
            $this->approvalModel->saveClerkRecommendation($applicationId, $data, $_SESSION['nic']),
            '/approval/clerk/en',
            $applicationId
        );
    }

    public function submitSuperintendent() {
        $applicationId = $this->getApplicationId();
        $data = [
            'quarter_type_number' => trim((string)($_POST['rec_quarter_type_no'] ?? '')) ?: null,
            'remarks' => trim((string)($_POST['rec_other_remarks'] ?? '')) ?: null,
            'approval_date' => ($_POST['rec_date'] ?? '') ?: null
        ];
        $this->saveAndRedirect(
            $this->approvalModel->saveSuperintendentRecommendation($applicationId, $data, $_SESSION['nic']),
            '/approval/deputy/en',
            $applicationId
        );
    }

    public function submitMarks() {
        $applicationId = $this->getApplicationId();
        $scores = [];
        for ($index = 1; $index <= 8; $index++) {
            $value = filter_var($_POST['score_' . $index] ?? 0, FILTER_VALIDATE_INT);
            if ($value === false || $value < 0) {
                throw new RuntimeException('Evaluation marks must be non-negative whole numbers.');
            }
            $scores[] = $value;
        }
        $this->saveAndRedirect(
            $this->approvalModel->saveEvaluationMarks($applicationId, $scores, $_SESSION['nic']),
            '/approval/marks',
            $applicationId
        );
    }

    private function getApplicationId() {
        $applicationId = filter_var($_POST['application_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$applicationId || !$this->approvalModel->applicationExists($applicationId)) {
            throw new RuntimeException('A valid application_id is required for this approval.');
        }
        return $applicationId;
    }

    private function saveAndRedirect($saved, $path, $applicationId) {
        if (!$saved) {
            throw new RuntimeException('The approval could not be saved.');
        }
        $_SESSION['approval_message'] = 'Approval details saved.';
        redirect($path . '?application_id=' . $applicationId);
    }
}
