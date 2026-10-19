<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/DropdownModel.php';
$auth = new AuthController();
$auth->requireLogin();

$user = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Railway Quarters Application Form - English';

require_once __DIR__ . '/../../models/Application.php';
$resubmitApp = null;
$resubmitId  = filter_input(INPUT_GET, 'resubmit', FILTER_VALIDATE_INT);
if ($resubmitId) {
    $appModel = new Application();
    $row = $appModel->findById($resubmitId);
    if ($row && $row['nic'] === $_SESSION['nic'] && $row['application_status'] === 'rejected') {
        $resubmitApp = $row;
    } else {
        $resubmitId = null;
    }
}

$dropdownModel = new DropdownModel();
$quarterTypes = $dropdownModel->getQuarterTypes('en');
$designations = $dropdownModel->getDesignations('en');
$subDepartments = $dropdownModel->getSubDepartments('en');
$districts = $dropdownModel->getDistricts('en');
$quarterLocations = $dropdownModel->getQuarterLocations('en');
$quarterNumbers = $dropdownModel->getQuarterNumbers('en');
$workStations = $dropdownModel->getWorkStations('en');
$salaryBranches = $dropdownModel->getSalaryAccountBranches('en');
$personalFileOffices = $dropdownModel->getPersonalFileOffices('en');

// Handle form submission POST request
include __DIR__ . '/../layouts/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sri Lanka Railways - Quarters Application Form</title>
    <link rel="stylesheet" href="<?php echo assetUrl('css/application.css'); ?>">
    <style>
        /* Mobile Responsive Styles */
        @media screen and (max-width: 768px) {
            body { padding: 5px; font-size: 14px; }
            .application-container { width: 100% !important; padding: 15px !important; margin: 5px auto !important; box-sizing: border-box; }
            .form-row { flex-direction: column !important; gap: 10px !important; display: block !important; }
            .form-group { width: 100% !important; margin-bottom: 10px !important; }
            .header-action-row { flex-direction: column !important; align-items: flex-start !important; gap: 15px; }
            .office-use-box { width: 100% !important; box-sizing: border-box; }
            .custom-table { display: block; overflow-x: auto; white-space: nowrap; width: 100%; }
            .button-container { flex-direction: column !important; gap: 10px !important; display: flex !important; }
            .button-container button, .button-container .nav-btn, .button-container .submit-btn { width: 100% !important; margin: 0 !important; text-align: center; box-sizing: border-box; }
            .radio-group { flex-wrap: wrap; gap: 10px; }
            .main-title-container h1 { font-size: 18px; }
            .main-title-container h2 { font-size: 13px; }
            .page-section { width: 100% !important; }
        }

        /* Print Styles */
        #print-approval-section, .clerk-approval-section, .print-only-element {
            display: none;
        }

        @media print {
            @page {
                size: A4;
                margin: 5mm;
            }

            header, footer, .site-header, .site-footer {
                display: none !important;
            }

            .back-btn-top, 
            .button-container,
            .clear-row-btn,
            button {
                display: none !important;
            }

            /* Ensure print approval and clerk sections appear and never break internally */
            #print-approval-section, .clerk-approval-section {
                display: block !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            /* Optional: Force the approval sections to start on a fresh page so they have plenty of room for stamps and signatures */
            #print-approval-section {
                page-break-before: always !important;
                break-before: page !important;
                margin-top: 10px !important;
            }

            .clerk-approval-section {
                margin-top: 15px !important;
            }

            .print-only-element {
                display: block !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .page-section {
                display: block !important;
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            body {
                background: white !important;
                padding: 0 !important;
                font-size: 11px !important;
            }
            
            .application-container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 5px !important;
                width: 100% !important;
            }

            .form-row {
                gap: 5px !important;
                margin-bottom: 5px !important;
            }

            .form-group {
                margin-bottom: 5px !important;
            }

            .section-title {
                margin: 10px 0 5px 0 !important;
                font-size: 12px !important;
            }

            .custom-table th, .custom-table td {
                padding: 4px !important;
                font-size: 10px !important;
            }
        }

        /* Number input arrows styling */
        #child-count-input::-webkit-outer-spin-button,
        #child-count-input::-webkit-inner-spin-button {
            -webkit-appearance: auto !important;
            opacity: 1 !important;
        }
        #child-count-input {
            -moz-appearance: number-input !important;
        }
        
        .clear-row-btn {
            background: #f39c12;
            color: white;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 13px;
        }
        .clear-row-btn:hover {
            background: #d68910;
        }

        body { font-family: sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .application-container { width: 1100px; max-width: 1100px; margin: 20px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); box-sizing: border-box; }
        .page-section { width: 100%; display: flex; flex-direction: column; justify-content: space-between; box-sizing: border-box; }
        .full-width { width: 100% !important; flex: 0 0 100% !important; }
        .note-text { font-size: 13px; color: #666; margin-top: 5px; font-style: italic; }
        .declaration-text p { margin: 5px 0; }
        .header-action-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .office-use-box { border: 1px solid #ccc; padding: 10px; border-radius: 5px; background: #f9f9f9; }
        .office-title { font-weight: bold; font-size: 12px; margin-bottom: 5px; color: #d9534f; }
        .office-field { display: flex; gap: 10px; align-items: center; }
        .main-title-container { text-align: center; margin-bottom: 25px; }
        .main-title-container h1 { font-size: 22px; color: #0f4c81; margin: 0 0 5px 0; }
        .main-title-container h2 { font-size: 16px; color: #555; margin: 0; }
        .note-box { background: #fff3cd; border: 1px solid #ffeeba; padding: 12px; border-radius: 5px; margin-bottom: 20px; color: #856404; font-size: 14px; }
        .form-row { display: flex; gap: 20px; margin-bottom: 15px; }
        .form-group { flex: 1; margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; color: #333; }
        .dropdown-select, .table-input, .form-input, .form-input-date, .form-input-date-sm { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        .radio-group { display: flex; gap: 15px; align-items: center; }
        .radio-label { font-weight: normal; cursor: pointer; display: flex; align-items: center; gap: 5px; }
        .section-title { font-size: 16px; font-weight: bold; color: #0f4c81; border-bottom: 2px solid #0f4c81; padding-bottom: 5px; margin: 25px 0 15px 0; }
        .custom-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .custom-table th, .custom-table td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 13px; }
        .custom-table th { background: #f2f2f2; color: #333; }
        .back-btn-top { padding: 10px 20px; background: #f39c12; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .back-btn-top:hover { background: #d68910; }
        .nav-btn, .submit-btn { padding: 10px 20px; background: #0f4c81; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .nav-btn:hover, .submit-btn:hover { background: #0b3963; }
    </style>
</head>
<body>

    <div class="application-container">
        
        <form id="railway-form" method="POST" action="<?php echo baseUrl('application/submit'); ?>" enctype="multipart/form-data">
            <input type="hidden" name="residence_type" value="<?= htmlspecialchars($_GET['type'] ?? ($_SESSION['residence_type'] ?? 'single'), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="form_language" value="en">
            <input type="hidden" name="resubmit_id" value="<?= (int)$resubmitId ?>">

            <!-- ================= PAGE 1 ================= -->
            <div id="page-1" class="page-section">
                <div>
                    <div class="header-action-row">
                        <div>
                            <!-- Updated Back Button to Residence Path -->
                            <button type="button" class="back-btn-top" onclick="window.location.href='<?php echo baseUrl('application/select-language?type=single'); ?>'">&laquo; Back </button>
                        </div>
                        <div class="office-use-box">
                            <div class="office-title">*** FOR OFFICE USE ONLY ***</div>
                            <div class="office-field">
                                <label>Date Received:</label>
                                <input type="date" name="office_received_date" class="form-input-date-sm">
                            </div>
                        </div>
                    </div>

                    <div class="main-title-container">
                        <h1>Sri Lanka Railways</h1>
                        <h2>Application for Railway Quarters - Transportation Sub-Department</h2>
                    </div>
                    <?php if ($resubmitApp): ?>
                    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:14px 18px;border-radius:8px;margin-bottom:20px;">
                        <strong><i class="fa-solid fa-triangle-exclamation"></i> Your previous application was rejected.</strong><br>
                        <?php if (!empty($resubmitApp['boss_reason']) || (empty($resubmitApp['file_reason']) && empty($resubmitApp['clerk_reason']))): ?>
                        <span>Reason from Immediate Boss:</span>
                        <em><?= htmlspecialchars($resubmitApp['boss_reason'] ?? '—') ?></em>
                        <?php endif; ?>
                        <?php if (!empty($resubmitApp['file_reason'])): ?>
                        <br><span>Reason from Personal File:</span>
                        <em><?= htmlspecialchars($resubmitApp['file_reason']) ?></em>
                        <?php endif; ?>
                        <?php if (!empty($resubmitApp['clerk_reason'])): ?>
                        <br><span>Reason from Subject Clerk:</span>
                        <em><?= htmlspecialchars($resubmitApp['clerk_reason']) ?></em>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="note-box">
                        <p><strong>Note :</strong> Every section herein must be filled accurately, clearly, and completely. Incomplete applications will be rejected.</p>
                    </div>

                    <div class="form-row top-row">
                        <div class="form-group">
                            <label>Location :</label>
                            <select id="location-dropdown" name="applied_quarter_location" class="dropdown-select" onkeydown="handleEnter(event, 'house-number-dropdown')">
                                <option value="" disabled <?= $resubmitApp ? '' : 'selected' ?>>-- Select --</option>
                                <?php foreach ($quarterLocations as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                        <?= ($resubmitApp && ($resubmitApp['applied_quarter_location'] ?? '') === $opt['name']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Quarter Number Applied For :</label>
                            <select id="house-number-dropdown" name="applied_quarter_number" class="dropdown-select" onkeydown="handleEnter(event, 'house-type')">
                                <option value="" disabled selected>-- Select Quarter Number --</option>
                                <?php foreach ($quarterNumbers as $qn): ?>
                                    <option value="<?= htmlspecialchars($qn['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($qn['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="house-type">Type of Quarter Applied For :</label>
                            <select id="house-type" name="quarter_type" class="dropdown-select" onkeydown="handleEnter(event, 'full_name')">
                                <option value="" disabled <?= $resubmitApp ? '' : 'selected' ?>>-- Select --</option>
                                <?php foreach ($quarterTypes as $quarterType): ?>
                                    <option value="<?= htmlspecialchars($quarterType['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                        <?= ($resubmitApp && ($resubmitApp['quarter_type'] ?? '') === $quarterType['name']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($quarterType['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">1. Applicant's Details</div>
                    
                    <div class="form-group">
                        <label>I. Applicant's Full Name :</label>
                        <input type="text" name="name" id="full_name" class="table-input auto-fill" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['user_name'] ?? ''); ?>" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'initials')">
                    </div>
                    
                    <div class="form-group">
                        <label>II. Name with Initials :</label>
                        <input type="text" name="applicant_initials" id="initials" class="table-input" placeholder="Ex: H. M. Perera" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'current_post')">
                    </div>

                    <div class="form-group">
                        <label>III. Current Workplace :</label>
                        <select name="applicant_current_work_station" id="current_post" class="dropdown-select" onkeydown="handleEnter(event, 'service_no')">
                            <option value="" disabled selected>-- Select --</option>
                                <?php foreach ($workStations as $opt): ?><option value="<?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>IV. Service Number :</label>
                            <input type="text" name="applicant_service_number" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'computer_no')">
                        </div>
                        <div class="form-group">
                            <label>V. Computer Number :</label>
                            <input type="text" name="computer_number" id="computer_no" class="table-input auto-fill" value="<?php echo htmlspecialchars($_SESSION['computer_no'] ?? ''); ?>" style="padding: 10px; font-size: 14px; width: 100%;" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" onkeydown="handleEnter(event, 'salary_slip_no')">
                        </div>
                        <div class="form-group">
                            <label>Salary Slip Number :</label>
                            <input type="text" name="applicant_salary_sheet_number" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" onkeydown="handleEnter(event, 'dob')">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="dob">VI. Date of Birth :</label>
                            <input type="date" id="applicant_date_of_birth" name="applicant_date_of_birth" class="form-input-date" onkeydown="handleEnter(event, 'phone_no')">
                        </div>
                        <div class="form-group">
                            <label>VIII. Telephone Number :</label>
                             <input type="tel" name="mobile" id="phone_no" class="table-input auto-fill" value="<?php echo htmlspecialchars($_SESSION['phone_no'] ?? $_SESSION['user_phone'] ?? ''); ?>" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'permanent_address')">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>VII. Permanent Address :</label>
                        <input type="text" name="applicant_private_address" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'designation')">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>IX. Designation :</label>
                            <select id="designation" name="applicant_designation" class="dropdown-select" onkeydown="handleEnter(event, 'national_id')">
                                <option value="" disabled selected>-- Select --</option>
                                <?php foreach ($designations as $designationOption): ?>
                                    <option value="<?= htmlspecialchars($designationOption['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($designationOption['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>XI. National Identity Card (NIC) Number :</label>
                            <input type="text" name="nic" id="national_id" class="table-input auto-fill" value="<?php echo htmlspecialchars($_SESSION['national_id'] ?? $_SESSION['nic'] ?? ''); ?>" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'residential_address')">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>X. Official Address :</label>
                        <input type="text" name="applicant_official_address" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'civil_status')">
                    </div>

                    <div class="form-group full-width">
                        <label>XII. Civil Status :</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="applicant_civil_status" value="Married" onclick="handleCivilStatus('Married')" onkeydown="handleEnter(event, 'sub_department')"> Married</label>
                            <label class="radio-label"><input type="radio" name="applicant_civil_status" value="Unmarried" onclick="handleCivilStatus('Unmarried')" onkeydown="handleEnter(event, 'sub_department')"> Unmarried</label>
                            <label class="radio-label"><input type="radio" name="applicant_civil_status" value="Divorced" onclick="handleCivilStatus('Divorced')" onkeydown="handleEnter(event, 'sub_department')"> Divorced</label>
                            <label class="radio-label"><input type="radio" name="applicant_civil_status" value="Widowed" onclick="handleCivilStatus('Widowed')" onkeydown="handleEnter(event, 'sub_department')"> Widowed</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>XIII. Details of Sub-Department of Service :</label>
                        <div class="form-row">
                            <div class="form-group indent">
                                <label>i. Sub-Department :</label>
                                <select name="applicant_current_sub_department" class="dropdown-select inline-select" onkeydown="handleEnter(event, 'salary_account')">
                                    <option value="" disabled selected>-- Select --</option>
                                    <?php foreach ($subDepartments as $department): ?>
                                        <option value="<?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group indent">
                                <label>ii. Accounts Branch handling Salary :</label>
                                 <select name="applicant_salary_account_branch" class="dropdown-select" style="width: 60%;" onkeydown="handleEnter(event, 'official_letter_office')">
                                     <option value="" disabled selected>-- Select Branch --</option>
                                <?php foreach ($salaryBranches as $opt): ?><option value="<?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
                                 </select>
                            </div>
                        </div>
                        <div class="form-group indent">
                            <label>iii. Office maintaining Personal Files :</label>
                            <select name="applicant_personal_file_office" class="dropdown-select" style="width: 60%;" onkeydown="handleEnter(event, 'is_permanent')">
                                <option value="" disabled selected>-- Select Office --</option>
                                <?php foreach ($personalFileOffices as $opt): ?><option value="<?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">2. Details of Service</div>
                    
                    <div class="form-group">
                        <label>Is your service confirmed?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="is_applicant_service_confirmed" value="yes" onclick="toggleContainer('permanent-date-group', true)" onkeydown="handleEnter(event, 'confirmed-date')"> Yes</label>
                            <label class="radio-label"><input type="radio" name="is_applicant_service_confirmed" value="no" onclick="toggleContainer('permanent-date-group', false)" onkeydown="handleEnter(event, 'gov-date')"> No</label>
                        </div>
                    </div>

                    <div id="permanent-date-group" class="form-row" style="display: none;">
                        <div class="form-group">
                            <label for="confirmed-date">Date of Confirmation :</label>
                            <input type="date" id="confirmed-date" name="applicant_service_confirmed_date" class="form-input-date" onkeydown="handleEnter(event, 'gov-date')">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gov-date">I. Date of Initial Appointment to Government Service :</label>
                            <input type="date" id="gov-date" name="applicant_gov_first_appointment_date" class="form-input-date" onchange="setMinDate('gov-date', ['railway-date', 'subdept-date', 'current-post-date', 'confirmed-date'])" onkeydown="handleEnter(event, 'railway-date')">
                        </div>
                        <div class="form-group">
                            <label for="railway-date">II. Date of Initial Appointment to Railway Service :</label>
                            <input type="date" id="railway-date" name="applicant_railway_first_appointment_date" class="form-input-date" onchange="setMinDate('railway-date', ['subdept-date', 'current-post-date'])" onkeydown="handleEnter(event, 'subdept-date')">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="subdept-date">III. Date of Assuming Duties in the Sub-Department:</label>
                            <input type="date" id="subdept-date" name="applicant_current_sub_dept_duty_date" class="form-input-date" onchange="setMinDate('subdept-date', ['current-post-date'])" onkeydown="handleEnter(event, 'current-post-date')">
                        </div>
                        <div class="form-group">
                            <label for="current-post-date">IV. Date of Appointment to Current Post:</label>
                            <input type="date" id="current-post-date" name="applicant_current_post_appointment_date" class="form-input-date" onkeydown="handleEnter(event, 'other_subdept')">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>V. Have you served in other Sub-Departments of the Railway Department?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="has_applicant_served_other_sub_dept" value="yes" onclick="toggleTable('table-v', true)" onkeydown="handleEnter(event, 'table-v')"> Yes</label>
                            <label class="radio-label"><input type="radio" name="has_applicant_served_other_sub_dept" value="no" onclick="toggleTable('table-v', false)" onkeydown="handleEnter(event, 'has_applicant_service_breaks')"> No</label>
                        </div>
                    </div>

                    <div id="table-v" class="conditional-table" style="display: none;">
                        <table class="custom-table" id="table-v-element">
                            <thead>
                                <tr>
                                    <th>Sub-Department</th>
                                    <th>From</th>
                                    <th>To</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><select class="dropdown-select table-select" name="sub_department_name[]"><option value="" disabled selected>-- Select --</option><?php foreach ($subDepartments as $department): ?><option value="<?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></td>
                                    <td><input type="date" name="service_from_date[]" class="form-input-date from-date" onchange="validateToDate(this)"></td>
                                    <td><input type="date" name="service_to_date[]" class="form-input-date to-date"></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="action-btn" onclick="addTableRow('table-v-element')" style="margin-top:10px; background:#0f4c81; color:white; border:none; padding:5px 10px; cursor:pointer;">Add Row</button>
                        <button type="button" class="action-btn" onclick="removeTableRow('table-v-element')" style="margin-top:10px; background:#c0392b; color:white; border:none; padding:5px 10px; cursor:pointer;">Remove Last Row</button>
                    </div>

                    <div class="form-group full-width" style="margin-top:15px;">
                        <label>VI. Are there any breaks in service? If so, state below.</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="has_applicant_service_breaks" value="yes" onclick="toggleTable('table-vi', true)"> Yes</label>
                            <label class="radio-label"><input type="radio" name="has_applicant_service_breaks" value="no" onclick="toggleTable('table-vi', false)"> No</label>
                        </div>
                    </div>

                    <div id="table-vi" class="conditional-table" style="display: none;">
                        <table class="custom-table" id="table-vi-element">
                            <thead>
                                <tr>
                                    <th>Sub-Department</th>
                                    <th>From</th>
                                    <th>To</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><select class="dropdown-select table-select" name="break_sub_department[]"><option value="" disabled selected>-- Select --</option><?php foreach ($subDepartments as $department): ?><option value="<?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></td>
                                    <td><input type="date" name="break_from_date[]" class="form-input-date from-date" onchange="validateToDate(this)"></td>
                                    <td><input type="date" name="break_to_date[]" class="form-input-date to-date"></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="action-btn" onclick="addTableRow('table-vi-element')" style="margin-top:10px; background:#0f4c81; color:white; border:none; padding:5px 10px; cursor:pointer;">Add Row</button>
                        <button type="button" class="action-btn" onclick="removeTableRow('table-vi-element')" style="margin-top:10px; background:#c0392b; color:white; border:none; padding:5px 10px; cursor:pointer;">Remove Last Row</button>
                    </div>
                </div>

                <div class="button-container" style="margin-top: 20px; text-align: right;">
                    <button type="button" class="nav-btn" onclick="changePage('page-1', 'page-2')">Next Page &raquo;</button>
                </div>
            </div>

            <!-- ================= PAGE 2 ================= -->
            <div id="page-2" class="page-section" style="display: none;">
                <div>
                     <div id="married-sections">
                        <div class="section-title">3. Details of Children</div>
                        
                        <div class="form-group">
                            <label>I. If you have children, will they reside with you? :</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="does_applicant_have_children_residing" value="yes" onclick="toggleContainer('children-details', true)" onkeydown="handleEnter(event, 'child-count-input')"> Yes
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="does_applicant_have_children_residing" value="no" onclick="toggleContainer('children-details', false)" onkeydown="handleEnter(event, 'has_spouse')"> No
                                </label>
                            </div>
                        </div>

                        <div id="children-details" style="display: none;">
                            <div class="form-group">
                                <label for="child-count-input">Enter Number of Children:</label>
                                <div class="action-row" style="display: flex; gap: 10px; align-items: center;">
                                    <input type="number" id="child-count-input" min="1" max="10" step="1" class="form-input" oninput="generateChildRows()" style="width: 120px; padding: 5px;">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>II. Details of Children :</label>
                                <table class="custom-table" id="children-table">
                                    <thead>
                                        <tr>
                                            <th>Full Name</th>
                                            <th>Gender</th>
                                            <th>Date of Birth</th>
                                            <th>Civil Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="children-tbody"></tbody>
                                </table>                                                                                                     
                            </div>
                            <p class="note-text">(If a child employed in the government sector is intended to be accommodated, special permission must be obtained.)</p>
                        </div>

                        <div class="section-title">4. Details of Spouse (Wife / Husband)</div>
                        
                        <div class="form-group full-width">
                            <label>If you have a spouse, will they reside with you? :</label>
                            <div class="radio-group">
                                <label class="radio-label"><input type="radio" name="does_applicant_have_spouse_residing" value="yes" onclick="toggleContainer('spouse-details', true)" onkeydown="handleEnter(event, 'spouse_name')"> Yes</label>
                                <label class="radio-label"><input type="radio" name="does_applicant_have_spouse_residing" value="no" onclick="toggleContainer('spouse-details', false)" onkeydown="handleEnter(event, 'permanent_address')"> No</label>
                            </div>
                        </div>

                        <div id="spouse-details" style="display: none;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>I. Full Name :</label>
                                     <input type="text" name="spouse_full_name" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_national_id')">
                                </div>
                                <div class="form-group">
                                    <label>II. National Identity Card (NIC) Number :</label>
                                    <input type="text" name="spouse_national_id" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_occupation')">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>III. Occupation :</label>
                                 <input type="text" name="spouse_occupation" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_moving')">
                            </div>

                            <div class="form-group">
                                <label>IV. If you are allocated a railway quarter, will the spouse come to reside there or not? :</label>
                                <div class="radio-group" style="margin-top: 5px;">
                                    <label class="radio-label"><input type="radio" name="spouse_will_reside_in_quarters" value="Will Come" onkeydown="handleEnter(event, 'spouse_gov_job')"> Will come</label>
                                    <label class="radio-label"><input type="radio" name="spouse_will_reside_in_quarters" value="Will Not Come" onkeydown="handleEnter(event, 'spouse_gov_job')"> Will not come</label>
                                </div>
                            </div>

                            <div class="section-title">5. If Spouse is Employed in the Government Sector</div>
                            
                            <div class="form-group full-width">
                                <label>Is the spouse employed in the government sector?</label>
                                <div class="radio-group">
                                    <label class="radio-label"><input type="radio" name="is_applicant_spouse_gov_employee" value="yes" onclick="toggleContainer('gov-spouse-details', true)" onkeydown="handleEnter(event, 'spouse_department')"> Yes</label>
                                    <label class="radio-label"><input type="radio" name="is_applicant_spouse_gov_employee" value="no" onclick="toggleContainer('gov-spouse-details', false)" onkeydown="handleEnter(event, 'permanent_address_sec6')"> No</label>
                                </div>
                            </div>

                            <div id="gov-spouse-details" style="display: none;">
                                <div class="form-row" style="margin-top: 10px;">
                                    <div class="form-group">
                                        <label>I. Department of Service :</label>
                                        <input type="text" name="spouse_department" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_service_number')">
                                    </div>
                                    <div class="form-group">
                                        <label>Service Number :</label>
                                        <input type="text" name="spouse_service_number" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_position')">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>II. Designation :</label>
                                    <input type="text" name="spouse_designation" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_institution')">
                                </div>

                                <div class="form-group">
                                    <label>III. Head of Institution and Address :</label>
                                    <input type="text" name="spouse_institution_head_and_address" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_current_post')">
                                </div>

                                <div class="form-group">
                                    <label>IV. Current Workplace and Address :</label>
                                    <input type="text" name="spouse_work_station_and_address" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_annual_salary')">
                                </div>

                                <div class="form-group">
                                    <label>V. Current Annual Salary :</label>
                                     <span style="font-weight: bold;">Rs.</span>
                                    <input type="number" name="spouse_annual_salary" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-title">6. Permanent Residence</div>
                    
                    <div class="form-group">
                        <label>I. Address of Permanent Residence :</label>
                        <input type="text" id="permanent_address_sec6" name="permanent_residence_address" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'owner_is_self')">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>II. Are you the owner of it?</label>
                            <div class="radio-group" style="margin-top: 5px;">
                                <label class="radio-label"><input type="radio" name="is_applicant_owner_of_residence" value="yes" onclick="toggleOwnerRelationship(false)" onkeydown="handleEnter(event, 'owner_from_home')"> Yes</label>
                                <label class="radio-label"><input type="radio" name="is_applicant_owner_of_residence" value="no" onclick="toggleOwnerRelationship(true)" onkeydown="handleEnter(event, 'owner_relationship')"> No</label>
                            </div>
                            <div id="owner-relationship-group" style="margin-top: 10px; display: none;">
                                <label style="display: block; margin-bottom: 5px;">If not, relationship to the owner :</label>
                                <input type="text" name="owner_relationship_if_not_self" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'commute_from_home')">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>III. Do you commute to work from there?</label>
                            <div class="radio-group" style="margin-top: 5px;">
                                <label class="radio-label"><input type="radio" name="does_applicant_commute_from_residence" value="yes" onkeydown="handleEnter(event, 'permanent-district')"> Yes</label>
                                <label class="radio-label"><input type="radio" name="does_applicant_commute_from_residence" value="no" onkeydown="handleEnter(event, 'permanent-district')"> No</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="permanent-district">IV. Permanent Residence District :</label>
                            <select id="permanent-district" name="permanent_residence_district" class="dropdown-select" onkeydown="handleEnter(event, 'nearest-city-distance')">
                                <option value="" disabled selected>-- Select --</option>
                                <?php foreach ($districts as $districtOption): ?>
                                    <option value="<?= htmlspecialchars($districtOption['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($districtOption['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>V. Nearest Main Town and Distance to it :</label>
                            <input type="text" name="nearest_major_city_name" class="table-input" placeholder="Nearest town">
                            <input type="number" id="nearest-city-distance" name="distance_to_nearest_city_km" class="table-input" min="0" step="0" placeholder="(km)" onkeydown="handleEnter(event, 'branch-to-work-distance')">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>VI. Distance from Railway Station/Halt to Workplace :</label>
                        <input type="number" id="branch-to-work-distance" name="distance_from_branch_post_to_work_km" class="table-input" min="0" step="1" style="max-width: 200px; margin-left: 10px;" placeholder="(km)">
                    </div>

                    <div class="section-title">7. If Currently Residing in a Rented House</div>
                    
                    <div class="form-group full-width">
                        <label>Are you currently residing in a rented house?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="is_applicant_currently_renting" value="yes" onclick="toggleContainer('rented-house-details', true)" onkeydown="handleEnter(event, 'house_owner')"> Yes</label>
                            <label class="radio-label"><input type="radio" name="is_applicant_currently_renting" value="no" onclick="toggleContainer('rented-house-details', false)"> No</label>
                        </div>
                    </div>

                    <div id="rented-house-details" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>I. Owner of the House :</label>
                                <input type="text" name="rented_house_owner_name" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'house_location')">
                            </div>
                            <div class="form-group">
                                <label>II. Location :</label>
                                 <input type="text" name="rented_house_location" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'rented_address')">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>III. Address :</label>
                             <input type="text" name="rented_house_address" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'rent_date')">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="rent-date">IV. Date Occupied :</label>
                                <input type="date" id="rent-date" name="rented_house_occupancy_date" class="form-input-date" onkeydown="handleEnter(event, 'monthly_rent')">
                            </div>
                            <div class="form-group">
                                <label>V. Monthly Rent Paid :</label>
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <span style="font-weight: bold;">Rs.</span>
                                    <input type="number" name="rented_house_monthly_rent" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="button-container space-between" style="margin-top: 20px; display: flex; justify-content: space-between;">
                    <button type="button" class="nav-btn" onclick="changePage('page-2', 'page-1')">&laquo; Previous</button>
                    <button type="button" class="nav-btn" onclick="changePage('page-2', 'page-3')">Next Page &raquo;</button>
                </div>
            </div>

            <!-- ================= PAGE 3 ================= -->
            <div id="page-3" class="page-section" style="display: none;">
                <div>
                    <div class="section-title">8. If you suffer from any walking disability or illness, state details.</div>
                    
                    <div class="form-group full-width">
                        <div class="radio-group" style="margin-bottom: 10px;">
                            <label class="radio-label"><input type="radio" name="does_applicant_have_disability" value="yes" onclick="toggleContainer('disability-details', true)" onkeydown="handleEnter(event, 'disability_details')"> Yes</label>
                            <label class="radio-label"><input type="radio" name="does_applicant_have_disability" value="no" onclick="toggleContainer('disability-details', false)" onkeydown="handleEnter(event, 'quarter_number')"> No</label>
                        </div>
                    </div>

                    <div id="disability-details" style="display: none;">
                        <div class="form-group">
                            <label>Details of Illness and Nature :</label>
                            <input type="text" name="disability_or_illness_details" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'medical-cert')">
                        </div>
                        <div class="form-group" style="margin-top: 10px;">
                            <label for="medical-cert" title="This file will be automatically renamed with your NIC number">Government Medical Certificate (PDF only) :</label>
                            <input type="file" id="medical-cert" name="medical_certificate_file_path" class="form-input" accept=".pdf" onmouseover="showRenameHint(this, 'Rename using National Identity Card number')" onmouseout="hideRenameHint(this)" onkeydown="handleEnter(event, 'quarter_number')">
                        </div>
                        <p class="note-text">(The disease and its nature must be certified through a Government Medical Certificate)</p>
                    </div>

                    <div class="section-title">9. If you previously occupied a Railway Quarter</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>I. Quarter Number :</label>
                            <input type="text" name="previous_quarter_number" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'moved-out-date')">
                        </div>
                        <div class="form-group">
                            <label for="moved-out-date">Date Vacated :</label>
                            <input type="date" id="moved-out-date" name="previous_quarter_vacated_date" class="form-input-date" onkeydown="handleEnter(event, 'reason_for_moving_out')">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Reason for Vacating :</label>
                        <input type="text" name="previous_quarter_vacate_reason" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'rejected_quarters')">
                    </div>

                    <div class="form-group full-width">
                        <label>II. Have you previously rejected a railway quarter allocated to you?</label>
                        <div class="radio-group" style="margin-top: 5px; margin-bottom: 10px;">
                            <label class="radio-label"><input type="radio" name="has_applicant_rejected_quarter_before" value="yes" onclick="toggleContainer('rejected-details', true)" onkeydown="handleEnter(event, 'rejected_details')"> Yes</label>
                            <label class="radio-label"><input type="radio" name="has_applicant_rejected_quarter_before" value="no" onclick="toggleContainer('rejected-details', false)" onkeydown="handleEnter(event, 'applied_before')"> No</label>
                        </div>
                    </div>

                    <div id="rejected-details" style="display: none;">
                        <div class="form-group">
                            <label>If so, details thereof :</label>
                            <input type="text" name="rejected_quarter_details" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'applied_before')">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>III. Have you previously submitted an application to obtain a railway quarter?</label>
                        <div class="radio-group" style="margin-top: 5px;">
                            <label class="radio-label"><input type="radio" name="has_applicant_applied_before" value="yes" onclick="toggleContainer('applied-before-details', true)" onkeydown="handleEnter(event, 'applied-date-prev')"> Yes</label>
                            <label class="radio-label"><input type="radio" name="has_applicant_applied_before" value="no" onclick="toggleContainer('applied-before-details', false)" onkeydown="handleEnter(event, 'declaration_year')"> No</label>
                        </div>
                    </div>

                    <div id="applied-before-details" style="display: none;">
                        <div class="form-row" style="margin-top: 10px;">
                            <div class="form-group">
                                <label for="applied-date-prev">Date Applied :</label>
                                <input type="date" id="applied-date-prev" name="previous_application_date" class="form-input-date" onkeydown="handleEnter(event, 'prev_house_type')">
                            </div>
                            <div class="form-group">
                                <label>Quarter Type :</label>
                                <select name="previous_applied_quarter_type" class="dropdown-select" onkeydown="handleEnter(event, 'prev_house_location')">
                                    <option value="" disabled selected>-- Select --</option>
                                <?php foreach ($quarterTypes as $opt): ?><option value="<?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Location :</label>
                            <select name="previous_applied_quarter_location" class="dropdown-select" onkeydown="handleEnter(event, 'declaration_year')">
                                <option value="" disabled selected>-- Select Location --</option>
                                <?php foreach ($quarterLocations as $opt): ?><option value="<?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Section 10: Updated to flow inline as shown in the reference image -->
                    <div class="section-title">10. Declaration</div>
                    <div class="form-group full-width declaration-text" style="font-size: 14px; line-height: 1.8;">
                        <p>If I am allocated a railway quarter,</p>
                        <p>I. That I will reside in it with my family members,</p>
                        <p>II. That I will not accommodate maintenance workers or outsiders without notice,</p>
                        <p>III. That if details stated in this application (salary changes, births of children, marriages, deaths, divorces, and children's spouses taking up employment) change hereafter, to report the same,</p>
                        <p>IV. Subject to the condition of vacating and handing over this quarter, with my dependents, upon notification by the Department or when required.</p>
                        
                        <div style="margin-top: 15px; line-height: 2.2;">
                            I hereby declare that the information stated above is true and correct, and I am aware that if proven to have made a false statement, disciplinary action may be taken against me at any time. I also certify that I am not currently the primary resident of another railway quarter. 
                            Year 
                            <input type="number" name="declaration_salary_year" class="table-input" placeholder="Year" min="2000" max="2100" style="width: 90px; padding: 5px; display: inline-block; margin: 0 5px;" required onkeydown="handleEnter(event, 'declaration_month')">
                            Year 
                            <select name="declaration_salary_month" class="dropdown-select" style="width: auto; display: inline-block; padding: 6px; margin: 0 5px;" onkeydown="handleEnter(event, 'salary-cert')">
                                <option value="" disabled selected>-- Month --</option>
                                <option value="January">January</option>
                                <option value="February">February</option>
                                <option value="March">March</option>
                                <option value="April">April</option>
                                <option value="May">May</option>
                                <option value="June">June</option>
                                <option value="July">July</option>
                                <option value="August">August</option>
                                <option value="September">September</option>
                                <option value="October">October</option>
                                <option value="November">November</option>
                                <option value="December">December</option>
                            </select>
                            A copy of the salary slip relevant to the month is attached.
                        </div>

                        <div class="form-group" style="margin-top: 15px;">
                            <label for="salary-cert" title="This file will be automatically renamed with your salary slip number"> Salary Slip Copy (PDF only) :</label>
                            <input type="file" id="salary-cert" name="salary_certificate_file_path" class="form-input" accept=".pdf" onmouseover="showRenameHint(this, 'Rename using salary slip number')" onmouseout="hideRenameHint(this)" onkeydown="handleEnter(event, 'page3-date')">
                        </div>
                    </div>

                    <div class="form-row" style="margin-top: 20px; display: flex; justify-content: space-between; align-items: flex-end;">
                        <div class="form-group">
                            <label for="page3-date">Date :</label>
                            <input type="date" id="page3-date" name="declaration_date" class="form-input-date">
                        </div>
                        <div class="signature-box print-only-element" style="text-align: right; margin-top: 20px;">
                            <div style="border-bottom: 1px dotted #333; width: 250px; display: inline-block; margin-bottom: 5px;"></div>
                            <p style="margin: 0; font-size: 14px;">Signature of Applicant</p>
                        </div>
                    </div>

                    <div id="print-approval-section">
                        <fieldset style="border: 1px solid #ccc; padding: 20px; border-radius: 5px;">
                            <legend style="font-weight: bold; color: #0f4c81; padding: 0 10px;">
                                Deputy Superintendent (Transportation) Colombo 
                            </legend>

                            <p style="font-size: 14px; margin-top: 10px; margin-bottom: 10px;">To the best of my knowledge,</p>
                            
                            <ul style="font-size: 14px; margin-left: 20px; margin-bottom: 20px; line-height: 1.6;">
                                <li>The information stated above is correct / correct as amended,</li>
                                <li>Applicant / spouse or children possess / do not possess residential property within 15 miles,</li>
                                <li>I hereby recommend / do not recommend his/her quarters application.</li>
                            </ul>

                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px;">Type and Number of Quarter Applied For :</label>
                                <input type="text" style="width: 100%; padding: 8px; border: 1px solid #ccc;" readonly>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px;">Essentiality for Duty (such as night shifts) and Other Remarks if any :</label>
                                <textarea rows="2" style="width: 100%; padding: 8px; border: 1px solid #ccc;" readonly></textarea>
                            </div>

                            <div style="display: flex; justify-content: space-between; margin-top: 20px; align-items: flex-end;">
                                <div>
                                    <label style="display: block; margin-bottom: 5px;">Date :</label>
                                    <input type="date" class="form-input-date" style="width: 180px;">
                                </div>
                                <div style="text-align: right;">
                                    <div style="border-bottom: 1px dotted #333; width: 280px; display: inline-block; margin-bottom: 5px;"></div>
                                    <p style="margin: 0; font-size: 13px;">Signature & Official Stamp of Station Master / Senior Officer</p>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <div class="clerk-approval-section">
                        <fieldset style="border: 1px solid #ccc; padding: 20px; border-radius: 5px; margin-top: 20px;">
                            <legend style="font-weight: bold; color: #0f4c81; padding: 0 10px;">
                                Recommendation of Subject Clerk Based on Personal File
                            </legend>

                            <p style="font-size: 14px; font-weight: bold; margin-top: 10px; margin-bottom: 15px;">
                                Recommendation regarding the eligibility of the above quarters applicant to obtain government / railway quarters :
                            </p>
                            
                            <div style="margin-bottom: 15px;">
                                <label>I. Has the right to government / railway quarters been forfeited due to a disciplinary order?</label>
                                <div style="display: flex; gap: 20px; margin-top: 5px;">
                                    <label><input type="radio" name="clerk_is_right_forfeited" value="Yes"> Yes</label>
                                    <label><input type="radio" name="clerk_is_right_forfeited" value="No"> No</label>
                                </div>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label>II. If yes, Date of expiry of disciplinary order :</label>
                                <input type="date" name="clerk_forfeit_expiry_date" class="form-input-date" style="margin-top: 5px;">
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label>III. Is he/she a recipient of Mihindusethpura housing?</label>
                                <div style="display: flex; gap: 20px; margin-top: 5px;">
                                    <label><input type="radio" name="clerk_is_mihindusethpura_recipient" value="Yes"> Yes</label>
                                    <label><input type="radio" name="clerk_is_mihindusethpura_recipient" value="No"> No</label>
                                </div>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label>IV. According to personal file, does/does not reside in another government / railway quarter?</label>
                                <div style="display: flex; gap: 20px; margin-top: 5px;">
                                    <label><input type="radio" name="clerk_residing_in_other_gov_quarter" value="Yes"> Yes</label>
                                    <label><input type="radio" name="clerk_residing_in_other_gov_quarter" value="No"> No</label>
                                </div>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label>V. Other Remarks :</label>
                                <textarea name="clerk_other_remarks" rows="2" class="table-input" style="margin-top: 5px; width: 100%;"></textarea>
                            </div>

                            <div style="display: flex; justify-content: space-between; margin-top: 20px; align-items: flex-end;">
                                <div>
                                    <label style="display: block; margin-bottom: 5px;">Cabinet Number :</label>
                                    <input type="text" class="table-input" style="width: 220px; padding: 6px;">
                                </div>
                                <div style="text-align: right;">
                                    <div style="border-bottom: 1px dotted #333; width: 220px; display: inline-block; margin-bottom: 5px;"></div>
                                    <p style="margin: 0; font-size: 13px;">Signature of Subject Clerk</p>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <div class="button-container space-between" style="margin-top: 25px; display: flex; justify-content: space-between;">
                    <button type="button" class="nav-btn" onclick="changePage('page-3', 'page-2')">&laquo; Previous</button>
                    <div>
                        <button type="button" class="nav-btn print-btn" onclick="printApplication()" style="background-color: #28a745; color: white; margin-right: 10px; padding: 10px 20px; border: none; cursor: pointer;">Print as PDF</button>
                        <button type="submit" class="submit-btn" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer;">Submit Application</button>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <script>
        let currentPage = 1;
        
                // Show the page/section of the first invalid field when Submit is clicked
        document.querySelector('.submit-btn').addEventListener('click', function () {
            const firstInvalid = document.querySelector('#railway-form input:invalid, #railway-form select:invalid, #railway-form textarea:invalid');
            if (!firstInvalid) return;

            const page = firstInvalid.closest('.page-section');
            if (page) {
                document.querySelectorAll('.page-section').forEach(p => p.style.display = 'none');
                page.style.display = 'block';
            }

            let el = firstInvalid.parentElement;
            while (el && el.id !== 'railway-form') {
                if (el.style && el.style.display === 'none') el.style.display = 'block';
                el = el.parentElement;
            }
        });

        window.onload = function() {
            const userData = {
                full_name: "<?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['user_name'] ?? '', ENT_QUOTES); ?>",
                computer_no: "<?php echo htmlspecialchars($_SESSION['computer_no'] ?? '', ENT_QUOTES); ?>",
                national_id: "<?php echo htmlspecialchars($_SESSION['national_id'] ?? $_SESSION['nic'] ?? '', ENT_QUOTES); ?>",
                phone_no: "<?php echo htmlspecialchars($_SESSION['phone_no'] ?? $_SESSION['user_phone'] ?? '', ENT_QUOTES); ?>"
            };
            
            if (userData.full_name) document.getElementById('full_name').value = userData.full_name;
            if (userData.computer_no) document.getElementById('computer_no').value = userData.computer_no;
            if (userData.national_id) document.getElementById('national_id').value = userData.national_id;
            if (userData.phone_no) document.getElementById('phone_no').value = userData.phone_no;
        };

        function handleEnter(event, nextFieldId) {
            if (event.key === 'Enter') {
                event.preventDefault();
                const nextField = document.getElementsByName(nextFieldId)[0] || document.getElementById(nextFieldId);
                if (nextField) {
                    nextField.focus();
                }
            }
        }

     function setMinDate(fromId, targetIds) {
        const from = document.getElementById(fromId);
        if (!from) return;
        const val = from.value;
        targetIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.min = val;
        });
        }

        function toggleOwnerRelationship(show) {
            document.getElementById('owner-relationship-group').style.display = show ? 'block' : 'none';
        }

        function showRenameHint(element, message) {
            element.title = message;
        }

        function hideRenameHint(element) {
            // Optional additional logic can go here
        }

        function changePage(currentId, targetId) {
            document.getElementById(currentId).style.display = 'none';
            document.getElementById(targetId).style.display = 'block';
            window.scrollTo(0, 0);
        }

        function toggleTable(tableId, show) {
            document.getElementById(tableId).style.display = show ? "block" : "none";
        }

        function addTableRow(tableId) {
            const table = document.getElementById(tableId).getElementsByTagName('tbody')[0];
            const newRow = table.insertRow();
            const isBreak = tableId === 'table-vi-element';
            const deptName = isBreak ? 'break_sub_department' : 'sub_department_name';
            const fromName = isBreak ? 'break_from_date' : 'service_from_date';
            const toName = isBreak ? 'break_to_date' : 'service_to_date';
            const departments = <?php echo json_encode(array_column($subDepartments, 'name'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
            const options = departments.map(name => `<option value="${name}">${name}</option>`).join('');
            newRow.innerHTML = `
                <td><select class="dropdown-select table-select" name="${deptName}[]"><option value="" disabled selected>-- Select --</option>${options}</select></td>
                <td><input type="date" name="${fromName}[]" class="form-input-date from-date" onchange="validateToDate(this)"></td>
                <td><input type="date" name="${toName}[]" class="form-input-date to-date"></td>
            `;
        }

        function removeTableRow(tableId) {
            const rows = document.getElementById(tableId).getElementsByTagName('tbody')[0].rows;
            if (rows.length > 1) rows[rows.length - 1].remove();
        }

    function toggleContainer(containerId, show) {
        const container = document.getElementById(containerId);
        container.style.display = show ? 'block' : 'none';
        if (!show) {
            container.querySelectorAll('input[type="date"]').forEach(i => { i.value = ''; i.min = ''; });
        }
        if (containerId === 'children-details' && show) {
            const tbody = document.getElementById('children-tbody');
            if (tbody.rows.length === 0) generateChildRows();
        }
    }

        function handleCivilStatus(status) {
            document.getElementById('married-sections').style.display = 'block';
        }

        function printApplication() {
            window.print();
        }

        // Generate rows based on child count
        function generateChildRows() {
            const countInput = document.getElementById('child-count-input');
            const tbody = document.getElementById('children-tbody');
            tbody.innerHTML = ''; 
            
            const count = parseInt(countInput.value) || 0;
            
            for (let i = 0; i < count; i++) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><input type="text" class="form-input" name="child_full_name[]" style="width: 100%;"></td>
                    <td>
                        <select class="dropdown-select table-select" name="child_gender[]">
                            <option value="" disabled selected>-- Select --</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </td>
                    <td><input type="date" class="form-input-date" name="child_date_of_birth[]"></td>
                    <td>
                        <select class="dropdown-select table-select" name="child_civil_status[]">
                            <option value="" disabled selected>-- Select --</option>
                            <option value="unmarried">Unmarried</option>
                            <option value="married">Married</option>
                            <option value="divorced">Divorced</option>
                            <option value="widowed">Widowed</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="clear-row-btn" onclick="clearChildRow(this)">Clear</button>
                    </td>
                `;
                tbody.appendChild(row);
            }
        }

        // Clear data only for the specific row
        function clearChildRow(button) {
            const row = button.closest('tr');
            const inputs = row.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.type === 'text' || input.type === 'date') {
                    input.value = '';
                } else if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                }
            });
        }

        // Manage To Date when From Date changes
        function validateToDate(fromInput) {
            const row = fromInput.closest('tr');
            const toInput = row.querySelector('.to-date');
            
            if (fromInput.value) {
                toInput.min = fromInput.value;
                if (toInput.value && toInput.value < fromInput.value) {
                    toInput.value = '';
                }
            } else {
                toInput.removeAttribute('min');
            }
        }
    </script>
</body>
</html>
<?php include __DIR__ . '/../layouts/footer.php'; ?>