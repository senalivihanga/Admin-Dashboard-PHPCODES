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
            $position = $this->getNextWaitingListPosition();
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
            $position = $this->waitingListModel->getPosition($nic);
            $application['position'] = $position;
            return $application;
        }
        
        return null;
    }
    
    /**
     * Get next waiting list position
     */
    private function getNextWaitingListPosition() {
        $sql = "SELECT COUNT(*) as count FROM waiting_list";
        $conn = getDBConnection();
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $conn->close();
        return ($row['count'] ?? 0) + 1;
    }

    /**
     * Process the full application form submission
     */
    public function submitFullApplication() {
        $this->auth->requireLogin();
        $nic = $_SESSION['nic'];

        $required = ['full_name', 'national_id', 'designation', 'house_type'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['form_error'] = "Please fill in all required fields.";
                redirect('/application/form/en');
                return;
            }
        }

        // The NIC typed on the form must match the logged-in applicant's NIC.
        if (trim($_POST['national_id']) !== trim($nic)) {
            $_SESSION['form_error'] = "The NIC entered does not match your account. Please correct it.";
            redirect('/application/form/en');
            return;
        }
        $conn = getDBConnection();
        $conn->begin_transaction();

        try {
            // --- Assign all POST values to variables ---
            $full_name = $_POST['full_name'];
            $initials = $_POST['initials'] ?? null;
            $current_post = $_POST['current_post'] ?? null;
            $service_no = $_POST['service_no'] ?? null;
            $computer_no = $_POST['computer_no'] ?? null;
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

            // 2. Insert into applications
            $quarterType = $_POST['house_type'] ?? '';
            $quarterNumber = $_POST['house_no'] ?? null;
            $quarterLocation = $_POST['location'] ?? null;
            $computer_no_val = $_POST['computer_no'] ?? null;

            $hasOtherSub = isset($_POST['other_subdept']) && $_POST['other_subdept'] === 'yes' ? 1 : 0;
            $hasBreaks = isset($_POST['service_breaks']) && $_POST['service_breaks'] === 'yes' ? 1 : 0;
            $hasChildren = isset($_POST['has_children']) && $_POST['has_children'] === 'yes' ? 1 : 0;
            $hasSpouse = isset($_POST['has_spouse']) && $_POST['has_spouse'] === 'yes' ? 1 : 0;
            $spouseGov = isset($_POST['spouse_gov_job']) && $_POST['spouse_gov_job'] === 'yes' ? 1 : 0;
            $isRenting = isset($_POST['is_rented_house']) && $_POST['is_rented_house'] === 'yes' ? 1 : 0;
            $hasDisability = isset($_POST['has_disability']) && $_POST['has_disability'] === 'yes' ? 1 : 0;
            $rejectedBefore = isset($_POST['rejected_quarters']) && $_POST['rejected_quarters'] === 'yes' ? 1 : 0;
            $appliedBefore = isset($_POST['applied_before']) && $_POST['applied_before'] === 'yes' ? 1 : 0;

            $appSQL = "
                INSERT INTO applications (
                    nic, computer_no, quarter_type, application_date,
                    applied_quarter_number, applied_quarter_location,
                    has_applicant_served_other_sub_dept,
                    has_applicant_service_breaks,
                    does_applicant_have_children_residing,
                    does_applicant_have_spouse_residing,
                    is_applicant_spouse_gov_employee,
                    is_applicant_currently_renting,
                    does_applicant_have_disability,
                    has_applicant_rejected_quarter_before,
                    has_applicant_applied_before,
                    application_status
                ) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted')
            ";
            $stmt = $conn->prepare($appSQL);
            // 14 placeholders: 5 strings + 9 integers
            $stmt->bind_param(
                "sssssiiiiiiiii", // 5 's' + 9 'i' = 14
                $nic,
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
                $appliedBefore
            );
            $stmt->execute();
            $applicationId = $stmt->insert_id;
            $stmt->close();

            // 3. Other sub‑departments served
            if ($hasOtherSub && isset($_POST['other_sub_dept_name'])) {
                $names = $_POST['other_sub_dept_name'];
                $froms = $_POST['other_sub_dept_from'];
                $tos = $_POST['other_sub_dept_to'];
                $sql = "INSERT INTO other_sub_departments_served (application_id, sub_department_name, service_from_date, service_to_date) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                for ($i = 0; $i < count($names); $i++) {
                    if (!empty($names[$i])) {
                        $stmt->bind_param("isss", $applicationId, $names[$i], $froms[$i], $tos[$i]);
                        $stmt->execute();
                    }
                }
                $stmt->close();
            }

            // 4. Service breaks
            if ($hasBreaks && isset($_POST['break_sub_dept'])) {
                $names = $_POST['break_sub_dept'];
                $froms = $_POST['break_from'];
                $tos = $_POST['break_to'];
                $sql = "INSERT INTO service_breaks (application_id, break_sub_department, break_from_date, break_to_date) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                for ($i = 0; $i < count($names); $i++) {
                    if (!empty($names[$i])) {
                        $stmt->bind_param("isss", $applicationId, $names[$i], $froms[$i], $tos[$i]);
                        $stmt->execute();
                    }
                }
                $stmt->close();
            }

            // 5. Children details
            if ($hasChildren && isset($_POST['child_full_name'])) {
                $names = $_POST['child_full_name'];
                $genders = $_POST['child_gender'];
                $dobs = $_POST['child_dob'];
                $statuses = $_POST['child_civil_status'];
                $sql = "INSERT INTO children_details (application_id, child_full_name, child_gender, child_date_of_birth, child_civil_status) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                for ($i = 0; $i < count($names); $i++) {
                    if (!empty($names[$i])) {
                        $stmt->bind_param("issss", $applicationId, $names[$i], $genders[$i], $dobs[$i], $statuses[$i]);
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
                $spouseName = $_POST['spouse_name'] ?? null;
                $spouseNic = $_POST['spouse_national_id'] ?? null;
                $spouseOcc = $_POST['spouse_occupation'] ?? null;
                $spouseReside = $_POST['spouse_moving'] ?? null;
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
            $permAddress = $_POST['perm_res_address'] ?? null;
            $isOwner = isset($_POST['owner_is_self']) && $_POST['owner_is_self'] === 'yes' ? 1 : 0;
            $ownerRel = $_POST['owner_relationship'] ?? null;
            $commute = isset($_POST['commute_from_home']) && $_POST['commute_from_home'] === 'yes' ? 1 : 0;
            $district = $_POST['permanent_district'] ?? null;
            $nearestCity = $_POST['nearest_city_name'] ?? null;
            $distanceCity = $_POST['nearest_city_distance'] ?? null;
            $distanceBranch = $_POST['branch_to_work_distance'] ?? null;
            $stmt->bind_param(
                "isississd",
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
            $month = $_POST['declaration_month'] ?? null;
            $year = $_POST['declaration_year'] ?? null;
            $declDate = $_POST['page3_date'] ?? null;
            $stmt->bind_param("isiss", $applicationId, $month, $year, $salaryPath, $declDate);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
            $_SESSION['success_message'] = "Application submitted successfully!";
            redirect('/dashboard');

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['form_error'] = "Error submitting application: " . $e->getMessage();
            redirect('/application/form/en');
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