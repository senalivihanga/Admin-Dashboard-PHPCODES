<?php
require_once __DIR__ . '/../models/Application.php';
require_once __DIR__ . '/AuthController.php';

class PreferenceController {
    private $auth;
    private $applicationModel;

    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->requireLogin();
        $this->applicationModel = new Application();
    }

    /**
     * Save residence type (called when user clicks
     * "Open Joint Residence" or "Open Application Form")
     */
    public function saveResidenceType() {
        $nic = $_SESSION['nic'];
        $residenceType = $_POST['residence_type'] ?? '';

        // Matches the enum in your DB: 'single' | 'joint_residence'
        if (!in_array($residenceType, ['single', 'joint_residence'], true)) {
            $_SESSION['preference_error'] = 'Invalid residence type.';
            redirect('/application/residence');
            return;
        }

        $this->applicationModel->savePreference($nic, $residenceType);
        $_SESSION['residence_type'] = $residenceType;

        // Redirect based on residence type
        if ($residenceType === 'joint_residence') {
            redirect('/application/apply.php?tab=joint');
        } else {
            redirect('/application/select-language?type=single');
        }
    }

    /**
     * Save language preference (called when user clicks Sinhala or English)
     * Matches the enum in your DB: 'en' | 'single_residence'
     */
    public function saveLanguage() {
        $nic = $_SESSION['nic'];
        $language = $_POST['language'] ?? '';
        $residenceType = $_POST['residence_type'] ?? 'single';

        if (!in_array($residenceType, ['single', 'joint_residence'], true)) {
            $residenceType = 'single';
        }

        if (!in_array($language, ['en', 'single_residence'], true)) {
            $_SESSION['preference_error'] = 'Invalid language.';
            redirect('/application/select-language?type=' . urlencode($residenceType));
            return;
        }

        // Save residence type + language together
        $this->applicationModel->savePreference($nic, $residenceType, $language);
        $_SESSION['residence_type'] = $residenceType;
        $_SESSION['form_language'] = $language;

        // Redirect to the appropriate form
        // 'single_residence' = Sinhala form (per your enum naming)
        if ($language === 'single_residence') {
            redirect('/application/form/si?type=' . urlencode($residenceType));
        } else {
            redirect('/application/form/en?type=' . urlencode($residenceType));
        }
    }
}