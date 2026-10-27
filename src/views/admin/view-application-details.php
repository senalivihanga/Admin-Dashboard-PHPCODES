<?php
/**
 * View Application Details (Read-Only) — Sri Lanka Railways
 * ---------------------------------------------------------
 * Complete, non-editable rendering of every field collected in
 * the applicant's form (EN + SI variants).
 */
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/roles.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$roleId = currentRoleId();
if ($roleId < 2 || $roleId > 7) {
    http_response_code(403);
    exit('Access denied.');
}

$applicationId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$applicationId) {
    header('Location: ' . baseUrl(roleHomePath($roleId)));
    exit;
}

$conn = getDBConnection();

// ---- Main application + user row -----------------------------------------
$stmt = $conn->prepare(
    "SELECT a.*,
            u.name                        AS applicant_name,
            u.email                       AS applicant_email,
            u.mobile                      AS applicant_mobile,
            u.applicant_initials,
            u.applicant_current_work_station,
            u.applicant_service_number,
            u.applicant_salary_sheet_number,
            u.applicant_date_of_birth,
            u.applicant_private_address,
            u.applicant_official_address,
            u.applicant_civil_status,
            u.applicant_designation,
            u.applicant_current_sub_department,
            u.applicant_salary_account_branch,
            u.applicant_personal_file_office,
            u.is_applicant_service_confirmed,
            u.applicant_service_confirmed_date,
            u.applicant_gov_first_appointment_date,
            u.applicant_railway_first_appointment_date,
            u.applicant_current_sub_dept_duty_date,
            u.applicant_current_post_appointment_date
       FROM applications a
 INNER JOIN users u ON u.nic = a.nic
      WHERE a.id = ?
      LIMIT 1"
);
$stmt->bind_param('i', $applicationId);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$app) {
    $conn->close();
    http_response_code(404);
    exit('Application not found.');
}

// ---- Child tables --------------------------------------------------------
function fetchAllForApp(mysqli $conn, string $sql, int $appId): array
{
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $appId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

$otherSubDepts = fetchAllForApp($conn, "SELECT * FROM other_sub_departments_served WHERE application_id = ? ORDER BY id", $applicationId);
$serviceBreaks = fetchAllForApp($conn, "SELECT * FROM service_breaks WHERE application_id = ? ORDER BY id", $applicationId);
$children      = fetchAllForApp($conn, "SELECT * FROM children_details WHERE application_id = ? ORDER BY id", $applicationId);

$spouseRows     = fetchAllForApp($conn, "SELECT * FROM spouse_details WHERE application_id = ? LIMIT 1", $applicationId);
$spouse         = $spouseRows[0] ?? null;

$permRows       = fetchAllForApp($conn, "SELECT * FROM permanent_residence_details WHERE application_id = ? LIMIT 1", $applicationId);
$permanent      = $permRows[0] ?? null;

$rentedRows     = fetchAllForApp($conn, "SELECT * FROM rented_house_details WHERE application_id = ? LIMIT 1", $applicationId);
$rented         = $rentedRows[0] ?? null;

$disabilityRows = fetchAllForApp($conn, "SELECT * FROM disability_medical_details WHERE application_id = ? LIMIT 1", $applicationId);
$disability     = $disabilityRows[0] ?? null;

$prevRows       = fetchAllForApp($conn, "SELECT * FROM previous_quarter_details WHERE application_id = ? LIMIT 1", $applicationId);
$previous       = $prevRows[0] ?? null;

$declRows       = fetchAllForApp($conn, "SELECT * FROM declaration_details WHERE application_id = ? LIMIT 1", $applicationId);
$declaration    = $declRows[0] ?? null;

$marksRows      = fetchAllForApp($conn, "SELECT * FROM evaluation_marks WHERE application_id = ? LIMIT 1", $applicationId);
$marks          = $marksRows[0] ?? null;

$conn->close();

// ---- Helpers -------------------------------------------------------------
function val($v, $fallback = '—') {
    if ($v === null || $v === '') return $fallback;
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
function yesNo($v): string {
    if ($v === null) return '—';
    return ((int)$v === 1) ? 'Yes' : 'No';
}
function statusBadge(string $status): string {
    $map = [
        'approved' => 'background:#dcfce7;color:#166534;border:1px solid #bbf7d0;',
        'pending'  => 'background:#fef3c7;color:#92400e;border:1px solid #fde68a;',
        'rejected' => 'background:#fee2e2;color:#991b1b;border:1px solid #fecaca;',
    ];
    $style = $map[$status] ?? 'background:#e2e8f0;color:#1e293b;border:1px solid #cbd5e1;';
    return '<span style="padding:4px 10px;border-radius:9999px;font-size:0.75rem;font-weight:700;text-transform:uppercase;' . $style . '">'
         . htmlspecialchars($status) . '</span>';
}

$pageTitle = "View Application - " . ($app['application_no'] ?? 'Sri Lanka Railways');
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
    .view-app-container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
    .back-link { display:inline-flex; align-items:center; gap:6px; color:#7A1C1C; text-decoration:none; font-weight:600; margin-bottom:20px; font-size:0.9rem; }
    .back-link:hover { text-decoration: underline; }
    .va-card { background:#fff; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.06); border-top:5px solid #7A1C1C; padding:26px 30px; margin-bottom:20px; }
    .va-title { color:#7A1C1C; font-size:1.4rem; font-weight:700; text-transform:uppercase; text-align:center; margin:0 0 6px; text-decoration: underline; text-underline-offset:6px; }
    .va-subtitle { text-align:center; color:#64748b; font-size:0.85rem; margin-bottom:20px; }
    .va-section { margin-top:20px; }
    .va-section-title { color:#7A1C1C; font-size:1rem; font-weight:700; text-transform:uppercase; letter-spacing:0.3px; border-bottom:2px solid #f1f5f9; padding-bottom:6px; margin-bottom:14px; }
    .va-grid { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:12px 24px; }
    .va-grid-3 { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:12px 24px; }
    .va-field { display:flex; flex-direction:column; gap:3px; }
    .va-label { font-size:0.72rem; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; }
    .va-value { font-size:0.9rem; color:#1e293b; font-weight:500; padding:8px 10px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; min-height:36px; word-break:break-word; }
    .va-table { width:100%; border-collapse:collapse; margin-top:6px; }
    .va-table th, .va-table td { padding:8px 10px; border:1px solid #e2e8f0; text-align:center; font-size:0.82rem; }
    .va-table th { background:#f8fafc; font-weight:700; text-transform:uppercase; font-size:0.7rem; color:#1e293b; }
    .va-note { font-size:0.8rem; color:#64748b; font-style:italic; margin-top:4px; }
    .va-status-row { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; margin-bottom:16px; }
    .va-status-row > div { display:flex; align-items:center; gap:6px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:6px 12px; }
    .va-status-row .label { font-size:0.75rem; color:#475569; font-weight:600; text-transform:uppercase; }
    .va-file-link { color:#0369a1; text-decoration:underline; font-weight:600; }
    .va-empty { text-align:center; color:#64748b; padding:16px; font-style:italic; }
    .va-print-btn { background:#0284c7; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-weight:600; cursor:pointer; font-size:0.85rem; display:inline-flex; align-items:center; gap:6px; }
    @media (max-width: 768px) {
        .va-grid, .va-grid-3 { grid-template-columns:1fr; }
        .va-card { padding:20px 15px; }
    }
    @media print {
        .back-link, .va-print-btn { display:none !important; }
        .va-card { box-shadow:none; border:none; padding:0; }
    }
</style>

<div class="view-app-container">
    <a href="javascript:history.back()" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>

    <div class="va-card">
        <h2 class="va-title">Application Details</h2>
        <p class="va-subtitle">
            Application No: <strong><?php echo val($app['application_no']); ?></strong>
            &nbsp;•&nbsp;
            Submitted: <?php echo val($app['application_submitted_at'] ?? $app['application_date']); ?>
        </p>

        <div class="va-status-row">
            <div><span class="label">Immediate Boss</span> <?php echo statusBadge($app['boss_status'] ?? 'pending'); ?></div>
            <div><span class="label">Personal File</span> <?php echo statusBadge($app['file_status'] ?? 'pending'); ?></div>
            <div><span class="label">Subject Clerk</span> <?php echo statusBadge($app['clerk_status'] ?? 'pending'); ?></div>
            <div><span class="label">Final Approval</span> <?php echo statusBadge($app['final_status'] ?? 'pending'); ?></div>
            <div><span class="label">Overall</span> <?php echo statusBadge($app['application_status'] ?? 'pending'); ?></div>
        </div>

        <!-- ===================== METADATA ===================== -->
        <div class="va-section">
            <div class="va-section-title">0. Application Metadata</div>
            <div class="va-grid-3">
                <div class="va-field"><span class="va-label">Application No.</span><div class="va-value"><?php echo val($app['application_no']); ?></div></div>
                <div class="va-field"><span class="va-label">Application Date</span><div class="va-value"><?php echo val($app['application_date']); ?></div></div>
                <div class="va-field"><span class="va-label">Submitted At</span><div class="va-value"><?php echo val($app['application_submitted_at']); ?></div></div>
                <div class="va-field"><span class="va-label">Date Received (Office Use)</span><div class="va-value"><?php echo val($app['application_date_received']); ?></div></div>
                <div class="va-field"><span class="va-label">Residence Type</span><div class="va-value"><?php echo val(ucwords(str_replace('_',' ', (string)$app['residence_type']))); ?></div></div>
                <div class="va-field"><span class="va-label">Form Language</span><div class="va-value"><?php echo $app['form_language'] === 'single_residence' ? 'සිංහල (Sinhala)' : 'English'; ?></div></div>
                <div class="va-field"><span class="va-label">Source</span><div class="va-value"><?php echo val(ucfirst((string)$app['source'])); ?></div></div>
                <div class="va-field"><span class="va-label">Is Resubmission?</span><div class="va-value"><?php echo yesNo($app['is_resubmission'] ?? 0); ?></div></div>
                <div class="va-field"><span class="va-label">Waiting List No.</span><div class="va-value"><?php echo val($app['waiting_list_no']); ?></div></div>
            </div>
        </div>

        <!-- ===================== QUARTER APPLIED FOR ===================== -->
        <div class="va-section">
            <div class="va-section-title">Quarter Applied For</div>
            <div class="va-grid-3">
                <div class="va-field"><span class="va-label">Location</span><div class="va-value"><?php echo val($app['applied_quarter_location']); ?></div></div>
                <div class="va-field"><span class="va-label">Quarter Number</span><div class="va-value"><?php echo val($app['applied_quarter_number']); ?></div></div>
                <div class="va-field"><span class="va-label">Quarter Type</span><div class="va-value"><?php echo val($app['quarter_type']); ?></div></div>
            </div>
        </div>

        <!-- ===================== SECTION 1: Applicant ===================== -->
        <div class="va-section">
            <div class="va-section-title">1. Applicant Details</div>
            <div class="va-grid">
                <div class="va-field"><span class="va-label">Full Name</span><div class="va-value"><?php echo val($app['applicant_name']); ?></div></div>
                <div class="va-field"><span class="va-label">Name with Initials</span><div class="va-value"><?php echo val($app['applicant_initials']); ?></div></div>
                <div class="va-field"><span class="va-label">NIC</span><div class="va-value"><?php echo val($app['nic']); ?></div></div>
                <div class="va-field"><span class="va-label">Computer Number</span><div class="va-value"><?php echo val($app['computer_no']); ?></div></div>
                <div class="va-field"><span class="va-label">Designation</span><div class="va-value"><?php echo val($app['applicant_designation']); ?></div></div>
                <div class="va-field"><span class="va-label">Service Number</span><div class="va-value"><?php echo val($app['applicant_service_number']); ?></div></div>
                <div class="va-field"><span class="va-label">Date of Birth</span><div class="va-value"><?php echo val($app['applicant_date_of_birth']); ?></div></div>
                <div class="va-field"><span class="va-label">Mobile</span><div class="va-value"><?php echo val($app['applicant_mobile']); ?></div></div>
                <div class="va-field"><span class="va-label">Civil Status</span><div class="va-value"><?php echo val($app['applicant_civil_status']); ?></div></div>
                <div class="va-field"><span class="va-label">Salary Slip No.</span><div class="va-value"><?php echo val($app['applicant_salary_sheet_number']); ?></div></div>
                <div class="va-field"><span class="va-label">Current Sub-Department</span><div class="va-value"><?php echo val($app['applicant_current_sub_department']); ?></div></div>
                <div class="va-field"><span class="va-label">Current Work Station</span><div class="va-value"><?php echo val($app['applicant_current_work_station']); ?></div></div>
                <div class="va-field"><span class="va-label">Salary Account Branch</span><div class="va-value"><?php echo val($app['applicant_salary_account_branch']); ?></div></div>
                <div class="va-field"><span class="va-label">Personal File Office</span><div class="va-value"><?php echo val($app['applicant_personal_file_office']); ?></div></div>
                <div class="va-field"><span class="va-label">Private Address</span><div class="va-value"><?php echo val($app['applicant_private_address']); ?></div></div>
                <div class="va-field"><span class="va-label">Official Address</span><div class="va-value"><?php echo val($app['applicant_official_address']); ?></div></div>
            </div>
        </div>

        <!-- ===================== SECTION 2: Service ===================== -->
        <div class="va-section">
            <div class="va-section-title">2. Service Details</div>
            <div class="va-grid-3">
                <div class="va-field"><span class="va-label">Service Confirmed?</span><div class="va-value"><?php echo yesNo($app['is_applicant_service_confirmed']); ?></div></div>
                <div class="va-field"><span class="va-label">Confirmation Date</span><div class="va-value"><?php echo val($app['applicant_service_confirmed_date']); ?></div></div>
                <div class="va-field"><span class="va-label">Gov First Appointment</span><div class="va-value"><?php echo val($app['applicant_gov_first_appointment_date']); ?></div></div>
                <div class="va-field"><span class="va-label">Railway First Appointment</span><div class="va-value"><?php echo val($app['applicant_railway_first_appointment_date']); ?></div></div>
                <div class="va-field"><span class="va-label">Sub-Dept Duty Date</span><div class="va-value"><?php echo val($app['applicant_current_sub_dept_duty_date']); ?></div></div>
                <div class="va-field"><span class="va-label">Current Post Date</span><div class="va-value"><?php echo val($app['applicant_current_post_appointment_date']); ?></div></div>
            </div>

            <div class="va-field" style="margin-top:12px;">
                <span class="va-label">Served in Other Sub-Departments?</span>
                <div class="va-value"><?php echo yesNo($app['has_applicant_served_other_sub_dept']); ?></div>
            </div>
            <?php if (!empty($otherSubDepts)): ?>
                <table class="va-table">
                    <thead><tr><th>Sub-Department</th><th>From</th><th>To</th></tr></thead>
                    <tbody>
                        <?php foreach ($otherSubDepts as $r): ?>
                            <tr>
                                <td><?php echo val($r['sub_department_name']); ?></td>
                                <td><?php echo val($r['service_from_date']); ?></td>
                                <td><?php echo val($r['service_to_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="va-field" style="margin-top:12px;">
                <span class="va-label">Any Service Breaks?</span>
                <div class="va-value"><?php echo yesNo($app['has_applicant_service_breaks']); ?></div>
            </div>
            <?php if (!empty($serviceBreaks)): ?>
                <table class="va-table">
                    <thead><tr><th>Sub-Department</th><th>From</th><th>To</th></tr></thead>
                    <tbody>
                        <?php foreach ($serviceBreaks as $r): ?>
                            <tr>
                                <td><?php echo val($r['break_sub_department']); ?></td>
                                <td><?php echo val($r['break_from_date']); ?></td>
                                <td><?php echo val($r['break_to_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- ===================== SECTION 3: Children ===================== -->
        <div class="va-section">
            <div class="va-section-title">3. Children</div>
            <div class="va-field">
                <span class="va-label">Children Residing With Applicant?</span>
                <div class="va-value"><?php echo yesNo($app['does_applicant_have_children_residing']); ?></div>
            </div>
            <?php if (!empty($children)): ?>
                <table class="va-table">
                    <thead><tr><th>Name</th><th>Gender</th><th>DOB</th><th>Civil Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($children as $c): ?>
                            <tr>
                                <td><?php echo val($c['child_full_name']); ?></td>
                                <td><?php echo val($c['child_gender']); ?></td>
                                <td><?php echo val($c['child_date_of_birth']); ?></td>
                                <td><?php echo val($c['child_civil_status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="va-empty">No children details submitted.</p>
            <?php endif; ?>
        </div>

        <!-- ===================== SECTION 4: Spouse ===================== -->
        <div class="va-section">
            <div class="va-section-title">4. Spouse</div>
            <div class="va-field">
                <span class="va-label">Spouse Residing With Applicant?</span>
                <div class="va-value"><?php echo yesNo($app['does_applicant_have_spouse_residing']); ?></div>
            </div>
            <?php if ($spouse): ?>
                <div class="va-grid" style="margin-top:12px;">
                    <div class="va-field"><span class="va-label">Spouse Full Name</span><div class="va-value"><?php echo val($spouse['spouse_full_name']); ?></div></div>
                    <div class="va-field"><span class="va-label">Spouse NIC</span><div class="va-value"><?php echo val($spouse['spouse_national_id']); ?></div></div>
                    <div class="va-field"><span class="va-label">Occupation</span><div class="va-value"><?php echo val($spouse['spouse_occupation']); ?></div></div>
                    <div class="va-field"><span class="va-label">Will Reside in Quarters</span><div class="va-value"><?php echo val($spouse['spouse_will_reside_in_quarters']); ?></div></div>
                </div>
                <div class="va-field" style="margin-top:8px;">
                    <span class="va-label">Spouse Gov Employee?</span>
                    <div class="va-value"><?php echo yesNo($app['is_applicant_spouse_gov_employee']); ?></div>
                </div>
                <?php if ((int)$app['is_applicant_spouse_gov_employee'] === 1): ?>
                    <div class="va-grid" style="margin-top:8px;">
                        <div class="va-field"><span class="va-label">Department</span><div class="va-value"><?php echo val($spouse['spouse_department']); ?></div></div>
                        <div class="va-field"><span class="va-label">Service Number</span><div class="va-value"><?php echo val($spouse['spouse_service_number']); ?></div></div>
                        <div class="va-field"><span class="va-label">Designation</span><div class="va-value"><?php echo val($spouse['spouse_designation']); ?></div></div>
                        <div class="va-field"><span class="va-label">Institution Head + Address</span><div class="va-value"><?php echo val($spouse['spouse_institution_head_and_address']); ?></div></div>
                        <div class="va-field"><span class="va-label">Work Station + Address</span><div class="va-value"><?php echo val($spouse['spouse_work_station_and_address']); ?></div></div>
                        <div class="va-field"><span class="va-label">Annual Salary</span><div class="va-value"><?php echo val($spouse['spouse_annual_salary']); ?></div></div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="va-empty">No spouse details submitted.</p>
            <?php endif; ?>
        </div>

        <!-- ===================== SECTION 5: Permanent Residence ===================== -->
        <div class="va-section">
            <div class="va-section-title">5. Permanent Residence</div>
            <?php if ($permanent): ?>
                <div class="va-grid">
                    <div class="va-field"><span class="va-label">Address</span><div class="va-value"><?php echo val($permanent['permanent_residence_address']); ?></div></div>
                    <div class="va-field"><span class="va-label">Owner?</span><div class="va-value"><?php echo yesNo($permanent['is_applicant_owner_of_residence']); ?></div></div>
                    <div class="va-field"><span class="va-label">Owner Relationship</span><div class="va-value"><?php echo val($permanent['owner_relationship_if_not_self']); ?></div></div>
                    <div class="va-field"><span class="va-label">Commutes from Residence?</span><div class="va-value"><?php echo yesNo($permanent['does_applicant_commute_from_residence']); ?></div></div>
                    <div class="va-field"><span class="va-label">District</span><div class="va-value"><?php echo val($permanent['permanent_residence_district']); ?></div></div>
                    <div class="va-field"><span class="va-label">Nearest City</span><div class="va-value"><?php echo val($permanent['nearest_major_city_name']); ?></div></div>
                    <div class="va-field"><span class="va-label">Distance to City (km)</span><div class="va-value"><?php echo val($permanent['distance_to_nearest_city_km']); ?></div></div>
                    <div class="va-field"><span class="va-label">Distance to Work (km)</span><div class="va-value"><?php echo val($permanent['distance_from_branch_post_to_work_km']); ?></div></div>
                </div>
            <?php else: ?>
                <p class="va-empty">No permanent residence details submitted.</p>
            <?php endif; ?>
        </div>

        <!-- ===================== SECTION 6: Rented House ===================== -->
        <div class="va-section">
            <div class="va-section-title">6. Rented House</div>
            <div class="va-field">
                <span class="va-label">Currently Renting?</span>
                <div class="va-value"><?php echo yesNo($app['is_applicant_currently_renting']); ?></div>
            </div>
            <?php if ($rented): ?>
                <div class="va-grid" style="margin-top:12px;">
                    <div class="va-field"><span class="va-label">Owner</span><div class="va-value"><?php echo val($rented['rented_house_owner_name']); ?></div></div>
                    <div class="va-field"><span class="va-label">Location</span><div class="va-value"><?php echo val($rented['rented_house_location']); ?></div></div>
                    <div class="va-field"><span class="va-label">Address</span><div class="va-value"><?php echo val($rented['rented_house_address']); ?></div></div>
                    <div class="va-field"><span class="va-label">Occupancy Date</span><div class="va-value"><?php echo val($rented['rented_house_occupancy_date']); ?></div></div>
                    <div class="va-field"><span class="va-label">Monthly Rent (LKR)</span><div class="va-value"><?php echo val($rented['rented_house_monthly_rent']); ?></div></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ===================== SECTION 7: Disability ===================== -->
        <div class="va-section">
            <div class="va-section-title">7. Disability / Illness</div>
            <div class="va-field">
                <span class="va-label">Has Disability?</span>
                <div class="va-value"><?php echo yesNo($app['does_applicant_have_disability']); ?></div>
            </div>
            <?php if ($disability): ?>
                <div class="va-grid" style="margin-top:12px;">
                    <div class="va-field"><span class="va-label">Details</span><div class="va-value"><?php echo val($disability['disability_or_illness_details']); ?></div></div>
                    <div class="va-field">
                        <span class="va-label">Medical Certificate</span>
                        <div class="va-value">
                            <?php if (!empty($disability['medical_certificate_file_path'])): ?>
                                <a class="va-file-link" target="_blank"
                                   href="<?php echo baseUrl($disability['medical_certificate_file_path']); ?>">
                                    <i class="fa-solid fa-file-pdf"></i> View PDF
                                </a>
                            <?php else: ?>—<?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ===================== SECTION 8: Previous Quarter ===================== -->
        <div class="va-section">
            <div class="va-section-title">8. Previous Quarter</div>

            <!-- Top-level flags stored on applications -->
            <div class="va-grid" style="margin-bottom:12px;">
                <div class="va-field"><span class="va-label">Rejected Quarter Before? (app flag)</span><div class="va-value"><?php echo yesNo($app['has_applicant_rejected_quarter_before']); ?></div></div>
                <div class="va-field"><span class="va-label">Applied Before? (app flag)</span><div class="va-value"><?php echo yesNo($app['has_applicant_applied_before']); ?></div></div>
            </div>

            <?php if ($previous): ?>
                <div class="va-grid">
                    <div class="va-field"><span class="va-label">Previous Quarter No.</span><div class="va-value"><?php echo val($previous['previous_quarter_number']); ?></div></div>
                    <div class="va-field"><span class="va-label">Vacated Date</span><div class="va-value"><?php echo val($previous['previous_quarter_vacated_date']); ?></div></div>
                    <div class="va-field"><span class="va-label">Vacate Reason</span><div class="va-value"><?php echo val($previous['previous_quarter_vacate_reason']); ?></div></div>
                    <div class="va-field"><span class="va-label">Rejected Quarter Before? (prev flag)</span><div class="va-value"><?php echo yesNo($previous['has_applicant_rejected_quarter_before']); ?></div></div>
                    <div class="va-field"><span class="va-label">Rejected Details</span><div class="va-value"><?php echo val($previous['rejected_quarter_details']); ?></div></div>
                    <div class="va-field"><span class="va-label">Applied Before? (prev flag)</span><div class="va-value"><?php echo yesNo($previous['has_applicant_applied_for_quarter_before']); ?></div></div>
                    <div class="va-field"><span class="va-label">Previous Application Date</span><div class="va-value"><?php echo val($previous['previous_application_date']); ?></div></div>
                    <div class="va-field"><span class="va-label">Previous Quarter Type</span><div class="va-value"><?php echo val($previous['previous_applied_quarter_type']); ?></div></div>
                    <div class="va-field"><span class="va-label">Previous Quarter Location</span><div class="va-value"><?php echo val($previous['previous_applied_quarter_location']); ?></div></div>
                </div>
            <?php else: ?>
                <p class="va-empty">No previous quarter details submitted.</p>
            <?php endif; ?>
        </div>

        <!-- ===================== SECTION 9: Declaration ===================== -->
        <div class="va-section">
            <div class="va-section-title">9. Declaration</div>
            <?php if ($declaration): ?>
                <div class="va-grid">
                    <div class="va-field"><span class="va-label">Salary Month</span><div class="va-value"><?php echo val($declaration['declaration_salary_month']); ?></div></div>
                    <div class="va-field"><span class="va-label">Salary Year</span><div class="va-value"><?php echo val($declaration['declaration_salary_year']); ?></div></div>
                    <div class="va-field">
                        <span class="va-label">Salary Certificate</span>
                        <div class="va-value">
                            <?php if (!empty($declaration['salary_certificate_file_path'])): ?>
                                <a class="va-file-link" target="_blank"
                                   href="<?php echo baseUrl($declaration['salary_certificate_file_path']); ?>">
                                    <i class="fa-solid fa-file-pdf"></i> View PDF
                                </a>
                            <?php else: ?>—<?php endif; ?>
                        </div>
                    </div>
                    <div class="va-field"><span class="va-label">Declaration Date</span><div class="va-value"><?php echo val($declaration['declaration_date']); ?></div></div>
                </div>
            <?php else: ?>
                <p class="va-empty">No declaration details submitted.</p>
            <?php endif; ?>
        </div>

        <!-- ===================== SECTION 10: Evaluation ===================== -->
        <div class="va-section">
            <div class="va-section-title">10. Evaluation Marks</div>
            <?php if ($marks): ?>
                <table class="va-table">
                    <thead>
                        <tr>
                            <th>Waiting List</th>
                            <th>Children</th>
                            <th>Housing</th>
                            <th>Distance &gt;30km</th>
                            <th>Applicant Illness</th>
                            <th>Dependent Illness</th>
                            <th>Marital</th>
                            <th>Tie-breaker</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo val($marks['mark_waiting_list_duration']); ?></td>
                            <td><?php echo val($marks['mark_child_count']); ?></td>
                            <td><?php echo val($marks['mark_housing_condition']); ?></td>
                            <td><?php echo val($marks['mark_distance_above_30km']); ?></td>
                            <td><?php echo val($marks['mark_applicant_serious_illness']); ?></td>
                            <td><?php echo val($marks['mark_dependent_serious_illness']); ?></td>
                            <td><?php echo val($marks['mark_marital_status']); ?></td>
                            <td><?php echo val($marks['mark_tie_breaker_service_years']); ?></td>
                            <td><strong><?php echo val($marks['total_evaluation_marks']); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
                <p class="va-note">
                    Evaluated by NIC: <?php echo val($marks['marks_evaluated_by_nic']); ?>
                    on <?php echo val($marks['marks_evaluated_at']); ?>
                </p>
            <?php else: ?>
                <p class="va-empty">Evaluation marks have not been entered yet.</p>
            <?php endif; ?>
        </div>

        <div style="text-align:right; margin-top:24px;">
            <button type="button" class="va-print-btn" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Print / Save as PDF
            </button>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';