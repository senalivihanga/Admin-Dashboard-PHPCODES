<?php
require_once __DIR__ . '/../models/Application.php';
require_once __DIR__ . '/../models/WaitingList.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/AuthController.php';

class ApplicationController {
    private $auth;
    private $applicationModel;
    private $waitingListModel;
    private $notificationModel;
    
    public function __construct() {
        $this->auth = new AuthController();
        $this->auth->requireLogin();
        $this->applicationModel = new Application();
        $this->waitingListModel = new WaitingList();
        $this->notificationModel = new Notification();
    }
    
    /**
     * Submit new application
     */
    public function submit($quarterType) {
        $nic = $_SESSION['nic'];
        
        $result = $this->applicationModel->create($nic, $quarterType);
        
        if ($result['success']) {
            // Add to waiting list
            $position = $this->getNextWaitingListPosition($quarterType);
            $this->waitingListModel->add($nic, $quarterType, $position);
            
            // Create notification
            $this->notificationModel->create(
                $nic,
                'Application Received',
                'Your quarter application has been received and is under review.'
            );
            
            return [
                'success' => true,
                'message' => 'Your application has been submitted successfully!',
                'computer_no' => $result['computer_no']
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error submitting application. Please try again.'
        ];
    }
    
    /**
     * Get application status
     */
    public function getStatus($computerNo = null) {
        $nic = $_SESSION['nic'];
        
        if ($computerNo) {
            $application = $this->applicationModel->findByComputerNoAndNic($computerNo, $nic);
        } else {
            $application = $this->applicationModel->findByNic($nic);
        }
        
        if ($application) {
            $position = $this->waitingListModel->getPosition($nic, $application['quarter_type']);
            $application['position'] = $position;
            return $application;
        }
        
        return null;
    }
    
    /**
     * Get next waiting list position
     */
    private function getNextWaitingListPosition($quarterType) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM waiting_list WHERE quarter_type = ?");
        $stmt->bind_param("s", $quarterType);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();
        return ($row['count'] ?? 0) + 1;
    }

    /**
     * Process the full application form submission
     */
    public function submitFullApplication() {
        $this->auth->requireLogin();
        $directAlloc = isDirectAllocationSession() ? $_SESSION['direct_alloc'] : null;
        $isDirectAlloc = $directAlloc !== null
            && (int)($_SESSION['role_id'] ?? 0) === 7
            && ($_POST['direct_alloc'] ?? '0') === '1'
            && !empty($directAlloc['allocation_id'])
            && !empty($directAlloc['nic']);
        if (($_POST['direct_alloc'] ?? '0') === '1' && !$isDirectAlloc) {
            $_SESSION['form_error'] = 'This direct-allocation application is no longer active. Please reopen it from the FA audit list.';
            redirect('/application/form/en');
            return;
        }
        $nic = $isDirectAlloc ? trim($directAlloc['nic']) : $_SESSION['nic'];
        $formLanguage = $_POST['form_language'] ?? 'en';
        $formPath = $formLanguage === 'si' ? '/application/form/si' : '/application/form/en';

        $fieldAliases = [
            'full_name' => 'name',
            'national_id' => 'nic',
            'house_type' => 'quarter_type',
            'initials' => 'applicant_initials',
            'current_post' => 'applicant_current_work_station',
            'service_no' => 'applicant_service_number',
            'computer_no' => 'computer_number',
            'salary_slip_no' => 'applicant_salary_sheet_number',
            'dob' => 'applicant_date_of_birth',
            'phone_no' => 'mobile',
            'private_address' => 'applicant_private_address',
            'residential_address' => 'applicant_official_address',
            'civil_status' => 'applicant_civil_status',
            'designation' => 'applicant_designation',
            'sub_department' => 'applicant_current_sub_department',
            'salary_account' => 'applicant_salary_account_branch',
            'official_letter_office' => 'applicant_personal_file_office',
            'is_permanent' => 'is_applicant_service_confirmed',
            'confirmed_date' => 'applicant_service_confirmed_date',
            'gov_date' => 'applicant_gov_first_appointment_date',
            'railway_date' => 'applicant_railway_first_appointment_date',
            'subdept_date' => 'applicant_current_sub_dept_duty_date',
            'current_post_date' => 'applicant_current_post_appointment_date',
            'other_subdept' => 'has_applicant_served_other_sub_dept',
            'service_breaks' => 'has_applicant_service_breaks',
            'has_children' => 'does_applicant_have_children_residing',
            'has_spouse' => 'does_applicant_have_spouse_residing',
            'spouse_gov_job' => 'is_applicant_spouse_gov_employee',
            'is_rented_house' => 'is_applicant_currently_renting',
            'has_disability' => 'does_applicant_have_disability',
            'rejected_quarters' => 'has_applicant_rejected_quarter_before',
            'applied_before' => 'has_applicant_applied_before',
            'location' => 'applied_quarter_location',
            'house_no' => 'applied_quarter_number',
            'spouse_name' => 'spouse_full_name',
            'spouse_moving' => 'spouse_will_reside_in_quarters',
            'spouse_position' => 'spouse_designation',
            'spouse_institution' => 'spouse_institution_head_and_address',
            'spouse_current_post' => 'spouse_work_station_and_address',
            'permanent_address_sec6' => 'permanent_residence_address',
            'owner_is_self' => 'is_applicant_owner_of_residence',
            'owner_relationship' => 'owner_relationship_if_not_self',
            'commute_from_home' => 'does_applicant_commute_from_residence',
            'permanent_district' => 'permanent_residence_district',
            'nearest_city_name' => 'nearest_major_city_name',
            'nearest_city_distance' => 'distance_to_nearest_city_km',
            'branch_to_work_distance' => 'distance_from_branch_post_to_work_km',
            'house_owner' => 'rented_house_owner_name',
            'house_location' => 'rented_house_location',
            'rented_address' => 'rented_house_address',
            'rent_date' => 'rented_house_occupancy_date',
            'monthly_rent' => 'rented_house_monthly_rent',
            'disability_details' => 'disability_or_illness_details',
            'quarter_number' => 'previous_quarter_number',
            'moved_out_date' => 'previous_quarter_vacated_date',
            'reason_for_moving_out' => 'previous_quarter_vacate_reason',
            'applied_date_prev' => 'previous_application_date',
            'prev_house_type' => 'previous_applied_quarter_type',
            'prev_house_location' => 'previous_applied_quarter_location',
            'declaration_month' => 'declaration_salary_month',
            'declaration_year' => 'declaration_salary_year',
            'page3_date' => 'declaration_date',
            'child_full_name' => 'child_name',
            'child_dob' => 'child_date_of_birth'
        ];
        foreach ($fieldAliases as $field => $alias) {
            if (!isset($_POST[$field]) && isset($_POST[$alias])) {
                $_POST[$field] = $_POST[$alias];
            }
        }
        if ($isDirectAlloc) {
            $_POST['national_id'] = $nic;
            $_POST['house_type'] = $directAlloc['quarter_type'];
            $_POST['quarter_type'] = $directAlloc['quarter_type'];
            $_POST['house_no'] = $directAlloc['quarter_no'];
            $_POST['applied_quarter_number'] = $directAlloc['quarter_no'];
            $_POST['location'] = $directAlloc['quarter_loc'];
            $_POST['applied_quarter_location'] = $directAlloc['quarter_loc'];
        }
        array_walk_recursive($_POST, static function (&$value) {
            if ($value === '') {
                $value = null;
            }
        });
        if (empty($_FILES['medical_cert']) && !empty($_FILES['medical_certificate_file_path'])) {
            $_FILES['medical_cert'] = $_FILES['medical_certificate_file_path'];
        }
        if (empty($_FILES['salary_cert']) && !empty($_FILES['salary_certificate_file_path'])) {
            $_FILES['salary_cert'] = $_FILES['salary_certificate_file_path'];
        }
        if (in_array($_POST['civil_status'] ?? '', ['විවාහක', 'අවිවාහක', 'දික්කසාදිත', 'වැන්දඹු'], true)) {
            $_POST['civil_status'] = [
                'විවාහක' => 'Married',
                'අවිවාහක' => 'Unmarried',
                'දික්කසාදිත' => 'Divorced',
                'වැන්දඹු' => 'Widowed'
            ][$_POST['civil_status']];
        }

        $required = ['full_name', 'national_id', 'designation', 'house_type'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['form_error'] = "Please fill in all required fields.";
                redirect($formPath);
                return;
            }
        }

        // The NIC typed on the form must match the logged-in applicant's NIC.
        if (trim((string)($_POST['national_id'] ?? '')) !== trim($nic)) {
            $_SESSION['form_error'] = "The NIC entered does not match your account. Please correct it.";
            redirect($formPath);
            return;
        }
        if (!in_array($formLanguage, ['en', 'si'], true)) {
            $formLanguage = 'en';
            $formPath = '/application/form/en';
        }
        $residenceType = $_POST['residence_type'] ?? ($_SESSION['residence_type'] ?? 'single');
        if (!in_array($residenceType, ['single', 'joint_residence'], true)) {
            $residenceType = 'single';
        }
        $conn = getDBConnection();
        $conn->begin_transaction();

        try {
            // --- Assign all POST values to variables ---
            $full_name = $_POST['full_name'];
            $initials = $_POST['initials'] ?? null;
            $current_post = $_POST['current_post'] ?? null;
            $service_no = $_POST['service_no'] ?? null;
            $computer_no = $_POST['computer_no'] ?? $_SESSION['computer_number'] ?? null;
            $salary_slip_no = $_POST['salary_slip_no'] ?? null;
            $dob = $_POST['dob'] ?? null;
            $phone_no = $_POST['phone_no'] ?? null;
            $private_address = $_POST['private_address'] ?? null;
            $residential_address = $_POST['residential_address'] ?? null;
            $civil_status = $_POST['civil_status'] ?? null;
            $designation = $_POST['designation'] ?? null;
            $sub_department = $_POST['sub_department'] ?? null;
            $salary_account = $_POST['salary_account'] ?? null;
            $official_letter_office = $_POST['official_letter_office'] ?? null;
            $isConfirmed = ($_POST['is_permanent'] ?? 'no') === 'yes' ? 1 : 0;
            $confirmedDate = !empty($_POST['confirmed_date']) ? $_POST['confirmed_date'] : null;
            $gov_date = $_POST['gov_date'] ?? null;
            $railway_date = $_POST['railway_date'] ?? null;
            $subdept_date = $_POST['subdept_date'] ?? null;
            $current_post_date = $_POST['current_post_date'] ?? null;

            // 1. Update users table - 21 SET fields + 1 WHERE = 22 placeholders
            $updateUserSQL = "
                UPDATE users SET
                    name = ?,
                    applicant_initials = ?,
                    applicant_current_work_station = ?,
                    applicant_service_number = ?,
                    computer_number = ?,
                    applicant_salary_sheet_number = ?,
                    applicant_date_of_birth = ?,
                    mobile = ?,
                    applicant_private_address = ?,
                    applicant_official_address = ?,
                    applicant_civil_status = ?,
                    applicant_designation = ?,
                    applicant_current_sub_department = ?,
                    applicant_salary_account_branch = ?,
                    applicant_personal_file_office = ?,
                    is_applicant_service_confirmed = ?,
                    applicant_service_confirmed_date = ?,
                    applicant_gov_first_appointment_date = ?,
                    applicant_railway_first_appointment_date = ?,
                    applicant_current_sub_dept_duty_date = ?,
                    applicant_current_post_appointment_date = ?
                WHERE nic = ?
            ";
            $stmt = $conn->prepare($updateUserSQL);
            // 22 's' characters for 22 variables (21 SET fields + 1 WHERE)
            $stmt->bind_param(
                "ssssssssssssssssssssss", // This is 22 's' characters
                $full_name,
                $initials,
                $current_post,
                $service_no,
                $computer_no,
                $salary_slip_no,
                $dob,
                $phone_no,
                $private_address,
                $residential_address,
                $civil_status,
                $designation,
                $sub_department,
                $salary_account,
                $official_letter_office,
                $isConfirmed,
                $confirmedDate,
                $gov_date,
                $railway_date,
                $subdept_date,
                $current_post_date,
                $nic  // 22nd variable for WHERE clause
            );
            $stmt->execute();
            $stmt->close();

            // 2. Promote this user's saved draft / rejected row, or create a submitted row if needed.
            $quarterType = $_POST['house_type'] ?? '';
            $quarterNumber = $_POST['house_no'] ?? null;
            $quarterLocation = $_POST['location'] ?? null;
            $computer_no_val = trim((string)($_POST['computer_no'] ?? $_SESSION['computer_number'] ?? ''));
            if ($computer_no_val === '') {
                $computerStmt = $conn->prepare("SELECT computer_number FROM users WHERE nic = ?");
                $computerStmt->bind_param("s", $nic);
                $computerStmt->execute();
                $computerRow = $computerStmt->get_result()->fetch_assoc();
                $computerStmt->close();
                $computer_no_val = $computerRow['computer_number'] ?? '';
            }
            if ($computer_no_val === '') {
                throw new RuntimeException('Your account does not have a computer number. Update your profile before applying.');
            }

            $hasOtherSub = isset($_POST['other_subdept']) && $_POST['other_subdept'] === 'yes' ? 1 : 0;
            $hasBreaks = isset($_POST['service_breaks']) && $_POST['service_breaks'] === 'yes' ? 1 : 0;
            $hasChildren = isset($_POST['has_children']) && $_POST['has_children'] === 'yes' ? 1 : 0;
            $hasSpouse = isset($_POST['has_spouse']) && $_POST['has_spouse'] === 'yes' ? 1 : 0;
            $spouseGov = isset($_POST['spouse_gov_job']) && $_POST['spouse_gov_job'] === 'yes' ? 1 : 0;
            $isRenting = isset($_POST['is_rented_house']) && $_POST['is_rented_house'] === 'yes' ? 1 : 0;
            $hasDisability = isset($_POST['has_disability']) && $_POST['has_disability'] === 'yes' ? 1 : 0;
            $rejectedBefore = isset($_POST['rejected_quarters']) && $_POST['rejected_quarters'] === 'yes' ? 1 : 0;
            $appliedBefore = isset($_POST['applied_before']) && $_POST['applied_before'] === 'yes' ? 1 : 0;

            // Priority 1: is this a resubmission of a rejected application?
            $resubmitId = filter_input(INPUT_POST, 'resubmit_id', FILTER_VALIDATE_INT);
            $isResubmit = 0;
            $draftRow = null;

            if (!$isDirectAlloc && $resubmitId) {
                $rejStmt = $conn->prepare(
                    "SELECT id, application_no FROM applications
                     WHERE id = ? AND nic = ? AND application_status = 'rejected'
                     LIMIT 1"
                );
                $rejStmt->bind_param("is", $resubmitId, $nic);
                $rejStmt->execute();
                $draftRow = $rejStmt->get_result()->fetch_assoc();
                $rejStmt->close();
                if ($draftRow) {
                    $isResubmit = 1;
                }
            }

            // Priority 2: otherwise use the latest draft
            if (!$draftRow && !$isDirectAlloc) {
                $draftStmt = $conn->prepare(
                    "SELECT id, application_no FROM applications
                     WHERE nic = ? AND application_status = 'draft'
                     ORDER BY id DESC LIMIT 1"
                );
                $draftStmt->bind_param("s", $nic);
                $draftStmt->execute();
                $draftRow = $draftStmt->get_result()->fetch_assoc();
                $draftStmt->close();
            }

            if ($draftRow) {
                $applicationId = (int)$draftRow['id'];

                
                // *** FIX: reuse the existing application number instead of generating a new one ***
                // This is what makes the success page show the SAME number after a resubmit.
                $applicationNo = $draftRow['application_no'];

                // Safety: if for some reason the row had no number, generate one now
                if (empty($applicationNo)) {
                    $year   = date('Y');
                    $prefix = "SLR/AOT/{$year}/";
                    $numStmt = $conn->prepare(
                        "SELECT application_no FROM applications
                         WHERE application_no LIKE ?
                         ORDER BY application_no DESC
                         LIMIT 1
                         FOR UPDATE"
                    );
                    $like = $prefix . '%';
                    $numStmt->bind_param("s", $like);
                    $numStmt->execute();
                    $lastRow = $numStmt->get_result()->fetch_assoc();
                    $numStmt->close();

                    $nextSeq = $lastRow
                        ? ((int)substr($lastRow['application_no'], -4)) + 1
                        : 1;

                    $applicationNo = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

                    // Save it back so the row is consistent
                    $saveNo = $conn->prepare("UPDATE applications SET application_no = ? WHERE id = ?");
                    $saveNo->bind_param("si", $applicationNo, $applicationId);
                    $saveNo->execute();
                    $saveNo->close();
                }

                $appSQL = "
                    UPDATE applications SET
                        source = 'normal',
                        computer_no = ?, quarter_type = ?, application_date = CURDATE(),
                        applied_quarter_number = ?, applied_quarter_location = ?,
                        has_applicant_served_other_sub_dept = ?, has_applicant_service_breaks = ?,
                        does_applicant_have_children_residing = ?, does_applicant_have_spouse_residing = ?,
                        is_applicant_spouse_gov_employee = ?, is_applicant_currently_renting = ?,
                        does_applicant_have_disability = ?, has_applicant_rejected_quarter_before = ?,
                        has_applicant_applied_before = ?,
                        boss_status = 'pending', boss_reason = NULL,
                        file_status = 'pending', file_reason = NULL,
                        clerk_status = 'pending', clerk_reason = NULL,
                        final_status = 'pending', final_reason = NULL,
                        branch_status = 'pending',
                        application_status = 'submitted', submit_status = 'success',
                        status = 'pending', application_submitted_at = NOW()
                    WHERE id = ? AND nic = ? AND application_status IN ('draft','rejected')
                ";
                $stmt = $conn->prepare($appSQL);
                $stmt->bind_param(
                    "ssssiiiiiiiiiis",
                    $computer_no_val,
                    $quarterType,
                    $quarterNumber,
                    $quarterLocation,
                    $hasOtherSub,
                    $hasBreaks,
                    $hasChildren,
                    $hasSpouse,
                    $spouseGov,
                    $isRenting,
                    $hasDisability,
                    $rejectedBefore,
                    $appliedBefore,
                    $applicationId,
                    $nic
                );
                $stmt->execute();
                $stmt->close();

                // Mark this as a resubmission so it shows in the IB / PF "Resubmitted" lists (and not in the new-application lists).
                if ($isResubmit) {
                    $flagStmt = $conn->prepare("UPDATE applications SET is_resubmission = 1 WHERE id = ? AND nic = ?");
                    $flagStmt->bind_param("is", $applicationId, $nic);
                    $flagStmt->execute();
                    $flagStmt->close();
                }

                // On a resubmit, wipe the old child-detail rows so we insert fresh copies.
                if ($isResubmit) {
                    $conn->query("DELETE FROM other_sub_departments_served WHERE application_id = " . (int)$applicationId);
                    $conn->query("DELETE FROM service_breaks WHERE application_id = " . (int)$applicationId);
                    $conn->query("DELETE FROM children_details WHERE application_id = " . (int)$applicationId);
                    $conn->query("DELETE FROM spouse_details WHERE application_id = " . (int)$applicationId);
                    $conn->query("DELETE FROM permanent_residence_details WHERE application_id = " . (int)$applicationId);
                    $conn->query("DELETE FROM rented_house_details WHERE application_id = " . (int)$applicationId);
                    $conn->query("DELETE FROM disability_medical_details WHERE application_id = " . (int)$applicationId);
                    $conn->query("DELETE FROM previous_quarter_details WHERE application_id = " . (int)$applicationId);
                    $conn->query("DELETE FROM declaration_details WHERE application_id = " . (int)$applicationId);
                }
            } else {
                // --- Generate the formatted application number (only for brand-new apps) ---
                // Format: SLR/AOT/YEAR/0001  (yearly reset)
                $year   = date('Y');
                $prefix = "SLR/AOT/{$year}/";

                $numStmt = $conn->prepare(
                    "SELECT application_no FROM applications
                     WHERE application_no LIKE ?
                     ORDER BY application_no DESC
                     LIMIT 1
                     FOR UPDATE"
                );
                $like = $prefix . '%';
                $numStmt->bind_param("s", $like);
                $numStmt->execute();
                $lastRow = $numStmt->get_result()->fetch_assoc();
                $numStmt->close();

                $nextSeq = $lastRow
                    ? ((int)substr($lastRow['application_no'], -4)) + 1
                    : 1;

                $applicationNo = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
                // Example output: SLR/AOT/2026/0001

                $appSQL = "
                    INSERT INTO applications (
                        nic, application_no, computer_no, quarter_type, application_date,
                        applied_quarter_number, applied_quarter_location,
                        has_applicant_served_other_sub_dept, has_applicant_service_breaks,
                        does_applicant_have_children_residing, does_applicant_have_spouse_residing,
                        is_applicant_spouse_gov_employee, is_applicant_currently_renting,
                        does_applicant_have_disability, has_applicant_rejected_quarter_before,
                        has_applicant_applied_before, residence_type, form_language,
                        application_status, submit_status, status, application_submitted_at
                    ) VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', 'success', 'pending', NOW())
                ";
                $stmt = $conn->prepare($appSQL);
                $stmt->bind_param(
                    "ssssssiiiiiiiiiss",
                    $nic,
                    $applicationNo,
                    $computer_no_val,
                    $quarterType,
                    $quarterNumber,
                    $quarterLocation,
                    $hasOtherSub,
                    $hasBreaks,
                    $hasChildren,
                    $hasSpouse,
                    $spouseGov,
                    $isRenting,
                    $hasDisability,
                    $rejectedBefore,
                    $appliedBefore,
                    $residenceType,
                    $formLanguage
                );
                $stmt->execute();
                $applicationId = $stmt->insert_id;
                $stmt->close();

                if ($isDirectAlloc) {
                    $forceApprove = $conn->prepare(
                        "UPDATE applications SET
                            source = 'direct',
                            boss_status = 'approved',
                            file_status = 'approved',
                            clerk_status = 'approved',
                            branch_status = 'approved',
                            final_status = 'approved',
                            application_status = 'approved',
                            status = 'approved',
                            submit_status = 'success'
                         WHERE id = ? AND nic = ?"
                    );
                    $forceApprove->bind_param('is', $applicationId, $nic);
                    $forceApprove->execute();
                    $forceApprove->close();

                    $allocationStmt = $conn->prepare(
                        "UPDATE allocated_quarters
                            SET computer_no = ?, resident_name = ?, designation = ?, workplace = ?
                          WHERE id = ? AND nic = ? AND source = 'direct'"
                    );
                    $allocationWorkplace = (string)($current_post ?? '');
                    $allocationStmt->bind_param(
                        'ssssis',
                        $computer_no_val,
                        $full_name,
                        $designation,
                        $allocationWorkplace,
                        $directAlloc['allocation_id'],
                        $nic
                    );
                    $allocationStmt->execute();
                    if ($allocationStmt->affected_rows === 0) {
                        $allocationCheck = $conn->prepare(
                            "SELECT id FROM allocated_quarters WHERE id = ? AND nic = ? AND source = 'direct' LIMIT 1"
                        );
                        $allocationCheck->bind_param('is', $directAlloc['allocation_id'], $nic);
                        $allocationCheck->execute();
                        $allocationExists = $allocationCheck->get_result()->fetch_assoc();
                        $allocationCheck->close();
                        if (!$allocationExists) {
                            throw new RuntimeException('The direct allocation record could not be found.');
                        }
                    }
                    $allocationStmt->close();
                }
            }

            $receivedDate = $_POST['office_received_date'] ?? null;
            $receivedStmt = $conn->prepare("UPDATE applications SET application_date_received = ? WHERE id = ?");
            $receivedStmt->bind_param("si", $receivedDate, $applicationId);
            $receivedStmt->execute();
            $receivedStmt->close();

            // 3. Other sub‑departments served
            if ($hasOtherSub) {
                $names = $_POST['other_sub_dept_name'] ?? $_POST['table_v_dept'] ?? [];
                $froms = $_POST['other_sub_dept_from'] ?? $_POST['table_v_from'] ?? [];
                $tos = $_POST['other_sub_dept_to'] ?? $_POST['table_v_to'] ?? [];
                $sql = "INSERT INTO other_sub_departments_served (application_id, sub_department_name, service_from_date, service_to_date) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                for ($i = 0; $i < count($names); $i++) {
                    $departmentName = trim((string)($names[$i] ?? ''));
                    $serviceFrom = $froms[$i] ?? '';
                    $serviceTo = $tos[$i] ?? '';
                    if ($departmentName !== '' && $serviceFrom !== '' && $serviceTo !== '') {
                        $stmt->bind_param("isss", $applicationId, $departmentName, $serviceFrom, $serviceTo);
                        $stmt->execute();
                    }
                }
                $stmt->close();
            }

            // 4. Service breaks
            if ($hasBreaks) {
                $names = $_POST['break_sub_dept'] ?? $_POST['table_vi_dept'] ?? [];
                $froms = $_POST['break_from'] ?? $_POST['table_vi_from'] ?? [];
                $tos = $_POST['break_to'] ?? $_POST['table_vi_to'] ?? [];
                $sql = "INSERT INTO service_breaks (application_id, break_sub_department, break_from_date, break_to_date) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                for ($i = 0; $i < count($names); $i++) {
                    $departmentName = trim((string)($names[$i] ?? ''));
                    $breakFrom = $froms[$i] ?? '';
                    $breakTo = $tos[$i] ?? '';
                    if ($departmentName !== '' && $breakFrom !== '' && $breakTo !== '') {
                        $stmt->bind_param("isss", $applicationId, $departmentName, $breakFrom, $breakTo);
                        $stmt->execute();
                    }
                }
                $stmt->close();
            }

            // 5. Children details
            if ($hasChildren) {
                $names = $_POST['child_full_name'] ?? $_POST['child_name'] ?? [];
                $genders = $_POST['child_gender'] ?? [];
                $dobs = $_POST['child_dob'] ?? [];
                $statuses = $_POST['child_civil_status'] ?? [];
                $sql = "INSERT INTO children_details (application_id, child_full_name, child_gender, child_date_of_birth, child_civil_status) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                for ($i = 0; $i < count($names); $i++) {
                    $childName = trim((string)($names[$i] ?? ''));
                    $childGender = $genders[$i] ?? '';
                    $childDob = $dobs[$i] ?? '';
                    $childStatus = $statuses[$i] ?? '';
                    if ($childName !== '' && in_array($childGender, ['female', 'male'], true) && $childDob !== '' && in_array($childStatus, ['married', 'unmarried', 'divorced', 'widowed'], true)) {
                        $stmt->bind_param("issss", $applicationId, $childName, $childGender, $childDob, $childStatus);
                        $stmt->execute();
                    }
                }
                $stmt->close();
            }

            // 6. Spouse details
            if ($hasSpouse) {
                $spouseSql = "INSERT INTO spouse_details (
                    application_id, spouse_full_name, spouse_national_id, spouse_occupation,
                    spouse_will_reside_in_quarters, spouse_department, spouse_service_number,
                    spouse_designation, spouse_institution_head_and_address,
                    spouse_work_station_and_address, spouse_annual_salary
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($spouseSql);
                $spouseName = trim((string)($_POST['spouse_name'] ?? ''));
                $spouseNic = trim((string)($_POST['spouse_national_id'] ?? ''));
                if ($spouseName === '' || $spouseNic === '') {
                    throw new RuntimeException('Enter the spouse name and NIC, or select that no spouse will reside in the quarter.');
                }
                $spouseOcc = $_POST['spouse_occupation'] ?? null;
                $spouseResideInput = $_POST['spouse_moving'] ?? '';
                $spouseReside = in_array($spouseResideInput, ['Will come', 'පැමිණේ'], true) ? 'Will Come' : (in_array($spouseResideInput, ['Will not come', 'පැමිණෙන්නේ නැත'], true) ? 'Will Not Come' : null);
                $spouseDept = $_POST['spouse_department'] ?? null;
                $spouseServNo = $_POST['spouse_service_number'] ?? null;
                $spouseDesig = $_POST['spouse_position'] ?? null;
                $spouseInst = $_POST['spouse_institution'] ?? null;
                $spouseWork = $_POST['spouse_current_post'] ?? null;
                $spouseSal = $_POST['spouse_annual_salary'] ?? null;
                $stmt->bind_param(
                    "issssssssss",
                    $applicationId, $spouseName, $spouseNic, $spouseOcc, $spouseReside,
                    $spouseDept, $spouseServNo, $spouseDesig, $spouseInst, $spouseWork, $spouseSal
                );
                $stmt->execute();
                $stmt->close();
            }

            // 7. Permanent residence details
            $permSql = "INSERT INTO permanent_residence_details (
                application_id, permanent_residence_address, is_applicant_owner_of_residence,
                owner_relationship_if_not_self, does_applicant_commute_from_residence,
                permanent_residence_district, nearest_major_city_name,
                distance_to_nearest_city_km, distance_from_branch_post_to_work_km
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($permSql);
            $permAddress = trim((string)($_POST['permanent_address_sec6'] ?? $_POST['permanent_address'] ?? ''));
            if ($permAddress === '') {
                throw new RuntimeException('Enter your permanent residence address.');
            }
            $isOwner = isset($_POST['owner_is_self']) && $_POST['owner_is_self'] === 'yes' ? 1 : 0;
            $ownerRel = $_POST['owner_relationship'] ?? null;
            $commute = isset($_POST['commute_from_home']) && $_POST['commute_from_home'] === 'yes' ? 1 : 0;
            $district = $_POST['permanent_district'] ?? null;
            $nearestCity = trim((string)($_POST['nearest_city_name'] ?? '')) ?: null;
            $distanceCity = ($_POST['nearest_city_distance'] ?? '') !== '' ? (float)$_POST['nearest_city_distance'] : null;
            $distanceBranch = ($_POST['branch_to_work_distance'] ?? '') !== '' ? (float)$_POST['branch_to_work_distance'] : null;
            $stmt->bind_param(
                "isisissdd",
                $applicationId, $permAddress, $isOwner, $ownerRel, $commute,
                $district, $nearestCity, $distanceCity, $distanceBranch
            );
            $stmt->execute();
            $stmt->close();

            // 8. Rented house details
            if ($isRenting) {
                $rentSql = "INSERT INTO rented_house_details (
                    application_id, rented_house_owner_name, rented_house_location,
                    rented_house_address, rented_house_occupancy_date, rented_house_monthly_rent
                ) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($rentSql);
                $owner = $_POST['house_owner'] ?? null;
                $location = $_POST['house_location'] ?? null;
                $address = $_POST['rented_address'] ?? null;
                $occDate = $_POST['rent_date'] ?? null;
                $rent = $_POST['monthly_rent'] ?? null;
                $stmt->bind_param("issssd", $applicationId, $owner, $location, $address, $occDate, $rent);
                $stmt->execute();
                $stmt->close();
            }

            // 9. Disability / illness details
            if ($hasDisability) {
                $medicalPath = $this->uploadFile('medical_cert', 'medical_certificates');
                $disSql = "INSERT INTO disability_medical_details (application_id, disability_or_illness_details, medical_certificate_file_path) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($disSql);
                $details = $_POST['disability_details'] ?? null;
                $stmt->bind_param("iss", $applicationId, $details, $medicalPath);
                $stmt->execute();
                $stmt->close();
            }

            // 10. Previous quarter details
            $prevSql = "INSERT INTO previous_quarter_details (
                application_id, previous_quarter_number, previous_quarter_vacated_date,
                previous_quarter_vacate_reason, has_applicant_rejected_quarter_before,
                rejected_quarter_details, has_applicant_applied_for_quarter_before,
                previous_application_date, previous_applied_quarter_type,
                previous_applied_quarter_location
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($prevSql);
            $prevQuarterNo = $_POST['quarter_number'] ?? null;
            $vacatedDate = $_POST['moved_out_date'] ?? null;
            $vacateReason = $_POST['reason_for_moving_out'] ?? null;
            $rejectedDetails = $_POST['rejected_details'] ?? null;
            $prevAppDate = $_POST['applied_date_prev'] ?? null;
            $prevQuarterType = $_POST['prev_house_type'] ?? null;
            $prevLocation = $_POST['prev_house_location'] ?? null;
            $stmt->bind_param(
                "isssisssss",
                $applicationId, $prevQuarterNo, $vacatedDate, $vacateReason,
                $rejectedBefore, $rejectedDetails, $appliedBefore,
                $prevAppDate, $prevQuarterType, $prevLocation
            );
            $stmt->execute();
            $stmt->close();

            // 11. Declaration details
            $salaryPath = $this->uploadFile('salary_cert', 'salary_certificates');
            $decSql = "INSERT INTO declaration_details (
                application_id, declaration_salary_month, declaration_salary_year,
                salary_certificate_file_path, declaration_date
            ) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($decSql);
            $month = $_POST['declaration_month'] ?? date('F');
            $year = (int)($_POST['declaration_year'] ?? date('Y'));
            $declDate = $_POST['page3_date'] ?? date('Y-m-d');
            $stmt->bind_param("isiss", $applicationId, $month, $year, $salaryPath, $declDate);
            $stmt->execute();
            $stmt->close();

            $notificationTitle = 'Application Received';
            $notificationMessage = $isDirectAlloc
                ? 'Your quarter application has been received and approved.'
                : 'Your quarter application has been received and is under review.';
            $notificationStmt = $conn->prepare("INSERT INTO notifications (nic, title, message, is_read) VALUES (?, ?, ?, 0)");
            $notificationStmt->bind_param("sss", $nic, $notificationTitle, $notificationMessage);
            $notificationStmt->execute();
            $notificationStmt->close();

            // Commit transaction
            $conn->commit();
            if ($isDirectAlloc) {
                clearDirectAllocationSession();
            }
            $_SESSION['success_message'] = "Application submitted successfully!";
            $appNo = $applicationNo;
            $successLang = $formLanguage;
            redirect('/application/success/' . $successLang . '?appNo=' . urlencode($appNo));

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['form_error'] = "Error submitting application: " . $e->getMessage();
            redirect($formPath);
        }
        $conn->close();
    }

    /**
     * Helper to handle file uploads
     */
    private function uploadFile($fieldName, $subDir) {
        if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/' . $subDir . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $dest)) {
                return 'uploads/' . $subDir . '/' . $filename;
            }
        }
        return null;
    }
}
?>