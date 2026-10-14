<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/DropdownModel.php';

$auth = new AuthController();
$auth->requireLogin();

$user = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'නිවාස අයදුම්පත්‍රය - සිංහල';

$dropdownModel = new DropdownModel();
$quarterTypes = $dropdownModel->getQuarterTypes('si');
$designations = $dropdownModel->getDesignations('si');
$subDepartments = $dropdownModel->getSubDepartments('si');
$districts = $dropdownModel->getDistricts('si');
$quarterLocations = $dropdownModel->getQuarterLocations('si');
$quarterNumbers = $dropdownModel->getQuarterNumbers('si');
$workStations = $dropdownModel->getWorkStations('si');
$salaryBranches = $dropdownModel->getSalaryAccountBranches('si');
$personalFileOffices = $dropdownModel->getPersonalFileOffices('si');

include __DIR__ . '/../layouts/header.php';
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ශ්‍රී ලංකා දුම්රිය දෙපාර්තමේන්තුව - නිවාස අයදුම්පත්‍රය</title>
    <link rel="stylesheet" href="<?php echo assetUrl('css/application.css'); ?>">
    <style>
        /* Mobile Responsive Styles Added */
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
            .top-navbar { font-size: 15px; padding: 10px; }
            .main-title-container h1 { font-size: 18px; }
            .main-title-container h2 { font-size: 13px; }
            .page-section { width: 100% !important; }
        }

        /* මුද්‍රණය කිරීමේදී පමණක් පෙන්වීම සඳහා සහ තිරයේදී සැඟවීම සඳහා CSS */
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

    .top-navbar, 
    .back-btn-top, 
    .button-container,
    .clear-row-btn,
    button {
        display: none !important;
    }

    /* මුද්‍රණය කරන විට approval sections පෙන්වීම සඳහා */
    #print-approval-section, .clerk-approval-section, .print-only-element {
        display: block !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    #print-approval-section {
        page-break-before: always !important;
        break-before: page !important;
        margin-top: 10px !important;
    }

    .clerk-approval-section {
        margin-top: 15px !important;
    }

    .page-section {
        display: block !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        page-break-after: avoid !important; /* පිටු කැඩීම වැළැක්වීම සඳහා */
        page-break-inside: avoid !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    body {
        background: white !important;
        padding: 0 !important;
        font-size: 11px !important; /* මුද්‍රණයේදී අකුරු ප්‍රමාණය සකස් කිරීම */
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
        .top-navbar { background: #0f4c81; color: white; padding: 15px; text-align: center; font-size: 18px; font-weight: bold; border-radius: 5px; }
        
        .application-container { width: 1100px; max-width: 1100px; margin: 20px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); box-sizing: border-box; }
        
        .page-section { 
            width: 100%;
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
            box-sizing: border-box;
        }

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
            <input type="hidden" name="form_language" value="si">
            
            <!-- ================= PAGE 1 ================= -->
            <div id="page-1" class="page-section">
                <div>
                    <div class="header-action-row">
                        <div>
                            <button type="button" class="back-btn-top" onclick="window.location.href='<?php echo baseUrl('application/select-language?type=single'); ?>'">&laquo; ආපසු </button>
                        </div>
                        <div class="office-use-box">
                            <div class="office-title">*** කාර්යාලීය ප්‍රයෝජනය සඳහායි ***</div>
                            <div class="office-field">
                                <label>අයදුම්පත ලැබුණු දිනය:</label>
                                <input type="date" name="office_received_date" class="form-input-date-sm">
                            </div>
                        </div>
                    </div>

                    <div class="main-title-container">
                        <h1>ශ්‍රී ලංකා දුම්රිය දෙපාර්තමේන්තුව</h1>
                        <h2>දුම්රිය නිවාස අයදුම්පත්‍රය - ප්‍රවාහන උප දෙපාර්තමේන්තුව</h2>
                    </div>

                    <div class="note-box">
                        <p><strong>සටහන :</strong> මෙහි සෑම වගන්තියක්ම නිවැරදිවත්, පැහැදිලිවත්, සම්පූර්ණ කළ යුතුය. අසම්පූර්ණ අයදුම්පත් ප්‍රතික්ෂේප කරනු ලැබේ.</p>
                    </div>

                    <div class="form-row top-row">
                        <div class="form-group">
                            <label>ස්ථානය :</label>
                            <select id="location-dropdown" name="applied_quarter_location" class="dropdown-select" onkeydown="handleEnter(event, 'house-number-dropdown')">
                                <option value="" disabled selected>-- තෝරන්න --</option>
                                <?php foreach ($quarterLocations as $opt): ?><option value="<?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>අයදුම් කරන නිවාස අංකය :</label>
                            <select id="house-number-dropdown" name="applied_quarter_number" class="dropdown-select" onkeydown="handleEnter(event, 'house-type')">
                                <option value="" disabled selected>-- නිවාස අංකය තෝරන්න --</option>
                                <?php foreach ($quarterNumbers as $qn): ?>
                                    <option value="<?= htmlspecialchars($qn['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($qn['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="house-type">අයදුම් කරන නිවාස වර්ගය :</label>
                            <select id="house-type" name="quarter_type" class="dropdown-select" onkeydown="handleEnter(event, 'full_name')">
                                <option value="" disabled selected>-- තෝරන්න --</option>
                                <?php foreach ($quarterTypes as $quarterType): ?>
                                    <option value="<?= htmlspecialchars($quarterType['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($quarterType['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">1. අයදුම්කරුගේ විස්තර </div>
                    
                    <div class="form-group">
                        <label>I. අයදුම්කරුගේ සම්පූර්ණ නම :</label>
                        <input type="text" name="name" id="full_name" class="table-input auto-fill" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['user_name'] ?? ''); ?>" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'initials')">
                    </div>
                    
                    <div class="form-group">
                        <label>II. මුලකුරු සමග නම :</label>
                        <input type="text" name="applicant_initials" id="initials" class="table-input" placeholder="උදා: එච්. එම්. පෙරේරා" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'current_post')">
                    </div>

                    <div class="form-group">
                        <label>III. වර්තමාන සේවා ස්ථානය :</label>
                        <select name="applicant_current_work_station" id="current_post" class="dropdown-select" onkeydown="handleEnter(event, 'service_no')">
                            <option value="" disabled selected>-- තෝරන්න --</option>
                            <?php foreach ($workStations as $opt): ?><option value="<?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>IV. සේවා අංකය :</label>
                            <input type="text" name="applicant_service_number" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'computer_no')">
                        </div>
                        <div class="form-group">
                            <label>V. පරිගණක අංකය :</label>
                            <input type="text" name="computer_number" id="computer_no" class="table-input auto-fill" value="<?php echo htmlspecialchars($_SESSION['computer_no'] ?? ''); ?>" style="padding: 10px; font-size: 14px; width: 100%;" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" onkeydown="handleEnter(event, 'salary_slip_no')">
                        </div>
                        <div class="form-group">
                            <label>වැටුපත් අංකය :</label>
                            <input type="text" name="applicant_salary_sheet_number" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" onkeydown="handleEnter(event, 'dob')">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="dob">VI. උපන් දිනය :</label>
                            <input type="date" id="applicant_date_of_birth" name="applicant_date_of_birth" class="form-input-date" onkeydown="handleEnter(event, 'phone_no')">
                        </div>
                        <div class="form-group">
                            <label>VIII. දුරකථන අංකය :</label>
                             <input type="tel" name="mobile" id="phone_no" class="table-input auto-fill" value="<?php echo htmlspecialchars($_SESSION['phone_no'] ?? $_SESSION['user_phone'] ?? ''); ?>" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'permanent_address')">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>VII. පෞද්ගලික ලිපිනය :</label>
                        <input type="text" name="applicant_private_address" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'designation')">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>IX. තනතුර :</label>
                            <select id="designation" name="applicant_designation" class="dropdown-select" onkeydown="handleEnter(event, 'national_id')">
                                <option value="" disabled selected>-- තෝරන්න --</option>
                                <?php foreach ($designations as $designationOption): ?>
                                    <option value="<?= htmlspecialchars($designationOption['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($designationOption['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>XI. ජාතික හැඳුනුම්පත් අංකය :</label>
                            <input type="text" name="nic" id="national_id" class="table-input auto-fill" value="<?php echo htmlspecialchars($_SESSION['national_id'] ?? $_SESSION['nic'] ?? ''); ?>" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'residential_address')">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>X. රාජකාරි ලිපිනය :</label>
                        <input type="text" name="applicant_official_address" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'civil_status')">
                    </div>

                    <div class="form-group full-width">
                        <label>XII. සිවිල් තත්වය :</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="applicant_civil_status" value="Married" onclick="handleCivilStatus('Married')" onkeydown="handleEnter(event, 'sub_department')"> විවාහක</label>
                            <label class="radio-label"><input type="radio" name="applicant_civil_status" value="Unmarried" onclick="handleCivilStatus('Unmarried')" onkeydown="handleEnter(event, 'sub_department')"> අවිවාහක</label>
                            <label class="radio-label"><input type="radio" name="applicant_civil_status" value="Divorced" onclick="handleCivilStatus('Divorced')" onkeydown="handleEnter(event, 'sub_department')"> දික්කසාද</label>
                            <label class="radio-label"><input type="radio" name="applicant_civil_status" value="Widowed" onclick="handleCivilStatus('Widowed')" onkeydown="handleEnter(event, 'sub_department')"> වැන්දඹු</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>XIII. ඔබ සේවය කරන :</label>
                        <div class="form-row">
                            <div class="form-group indent">
                                <label>i. උප දෙපාර්තමේන්තුව :</label>
                                <select name="applicant_current_sub_department" class="dropdown-select inline-select" onkeydown="handleEnter(event, 'salary_account')">
                                    <option value="" disabled selected>-- තෝරන්න --</option>
                                    <?php foreach ($subDepartments as $department): ?>
                                        <option value="<?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group indent">
                                <label>ii. වැටුප් සකස් කරන ගිණුම් අංශය :</label>
                                 <select name="applicant_salary_account_branch" class="dropdown-select" style="width: 60%;" onkeydown="handleEnter(event, 'official_letter_office')">
                                     <option value="" disabled selected>-- ශාඛාව තෝරන්න --</option>
                                     <?php foreach ($salaryBranches as $opt): ?><option value="<?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
                                 </select>
                            </div>
                        </div>
                        <div class="form-group indent">
                            <label>iii. පෞද්ගලික ලිපිගොනු නඩත්තු කරන කාර්යාලය :</label>
                            <select name="applicant_personal_file_office" class="dropdown-select" style="width: 60%;" onkeydown="handleEnter(event, 'is_permanent')">
                                <option value="" disabled selected>-- කාර්යාලය තෝරන්න --</option>
                                <?php foreach ($personalFileOffices as $opt): ?><option value="<?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">2. ඔබගේ සේවය පිළිබඳ විස්තර</div>
                    
                    <div class="form-group">
                        <label>ඔබගේ සේවය ස්ථිර ද?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="is_applicant_service_confirmed" value="yes" onclick="toggleContainer('permanent-date-group', true)" onkeydown="handleEnter(event, 'confirmed-date')"> ඔව්</label>
                            <label class="radio-label"><input type="radio" name="is_applicant_service_confirmed" value="no" onclick="toggleContainer('permanent-date-group', false)" onkeydown="handleEnter(event, 'gov-date')"> නැත</label>
                        </div>
                    </div>

                    <div id="permanent-date-group" class="form-row" style="display: none;">
                        <div class="form-group">
                            <label for="confirmed-date">සේවය ස්ථිර වූ දිනය :</label>
                            <input type="date" id="confirmed-date" name="applicant_service_confirmed_date" class="form-input-date" onkeydown="handleEnter(event, 'gov-date')">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gov-date">I. රජයේ සේවයට ප්‍රථමයෙන් පත් වූ දිනය :</label>
                            <input type="date" id="gov-date" name="applicant_gov_first_appointment_date" class="form-input-date" onchange="setMinDate('gov-date', ['railway-date', 'subdept-date', 'current-post-date', 'confirmed-date'])" onkeydown="handleEnter(event, 'railway-date')">
                        </div>
                        <div class="form-group">
                            <label for="railway-date">II. දුම්රිය සේවයට ප්‍රථමයෙන් පත් වූ දිනය :</label>
                            <input type="date" id="railway-date" name="applicant_railway_first_appointment_date" class="form-input-date" onchange="setMinDate('railway-date', ['subdept-date', 'current-post-date'])" onkeydown="handleEnter(event, 'subdept-date')">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="subdept-date">III. උප දෙපාර්තමේන්තුවේ වැඩ භාර ගත් දිනය:</label>
                            <input type="date" id="subdept-date" name="applicant_current_sub_dept_duty_date" class="form-input-date" onchange="setMinDate('subdept-date', ['current-post-date'])" onkeydown="handleEnter(event, 'current-post-date')">
                        </div>
                        <div class="form-group">
                            <label for="current-post-date">IV. දැනට දරන තනතුරට පත් වූ දිනය:</label>
                            <input type="date" id="current-post-date" name="applicant_current_post_appointment_date" class="form-input-date" onkeydown="handleEnter(event, 'other_subdept')">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>V. දුම්රිය දෙපාර්තමේන්තුවේ අනෙකුත් උප දෙපාර්තමේන්තු වල සේවය කර තිබේ ද?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="has_applicant_served_other_sub_dept" value="yes" onclick="toggleTable('table-v', true)" onkeydown="handleEnter(event, 'table-v')"> ඔව්</label>
                            <label class="radio-label"><input type="radio" name="has_applicant_served_other_sub_dept" value="no" onclick="toggleTable('table-v', false)" onkeydown="handleEnter(event, 'has_applicant_service_breaks')"> නැත</label>
                        </div>
                    </div>

                    <div id="table-v" class="conditional-table" style="display: none;">
                        <table class="custom-table" id="table-v-element">
                            <thead>
                                <tr>
                                    <th>උප දෙපාර්තමේන්තුව</th>
                                    <th>සිට</th>
                                    <th>දක්වා</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><select class="dropdown-select table-select" name="sub_department_name[]"><option value="" disabled selected>-- තෝරන්න --</option><?php foreach ($subDepartments as $department): ?><option value="<?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></td>
                                    <td><input type="date" name="service_from_date[]" class="form-input-date from-date" onchange="validateToDate(this)"></td>
                                    <td><input type="date" name="service_to_date[]" class="form-input-date to-date"></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="action-btn" onclick="addTableRow('table-v-element')" style="margin-top:10px; background:#0f4c81; color:white; border:none; padding:5px 10px; cursor:pointer;">තවත් පේළියක් එකතු කරන්න</button>
                        <button type="button" class="action-btn" onclick="removeTableRow('table-v-element')" style="margin-top:10px; background:#c0392b; color:white; border:none; padding:5px 10px; cursor:pointer;">අවසාන පේළිය ඉවත් කරන්න</button>
                    </div>

                    <div class="form-group full-width" style="margin-top:15px;">
                        <label>VI. සේවයේ කඩවීම් තිබේ ද? ඇත්නම් පහත දක්වන්න.</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="has_applicant_service_breaks" value="yes" onclick="toggleTable('table-vi', true)"> ඔව්</label>
                            <label class="radio-label"><input type="radio" name="has_applicant_service_breaks" value="no" onclick="toggleTable('table-vi', false)"> නැත</label>
                        </div>
                    </div>

                    <div id="table-vi" class="conditional-table" style="display: none;">
                        <table class="custom-table" id="table-vi-element">
                            <thead>
                                <tr>
                                    <th>උප දෙපාර්තමේන්තුව</th>
                                    <th>සිට</th>
                                    <th>දක්වා</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><select class="dropdown-select table-select" name="break_sub_department[]"><option value="" disabled selected>-- තෝරන්න --</option><?php foreach ($subDepartments as $department): ?><option value="<?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></td>
                                    <td><input type="date" name="break_from_date[]" class="form-input-date from-date" onchange="validateToDate(this)"></td>
                                    <td><input type="date" name="break_to_date[]" class="form-input-date to-date"></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="action-btn" onclick="addTableRow('table-vi-element')" style="margin-top:10px; background:#0f4c81; color:white; border:none; padding:5px 10px; cursor:pointer;">තවත් පේළියක් එකතු කරන්න</button>
                        <button type="button" class="action-btn" onclick="removeTableRow('table-vi-element')" style="margin-top:10px; background:#c0392b; color:white; border:none; padding:5px 10px; cursor:pointer;">අවසාන පේළිය ඉවත් කරන්න</button>
                    </div>
                </div>
                <div class="button-container" style="margin-top: 20px; text-align: right;">
                    <button type="button" class="nav-btn" onclick="changePage('page-1', 'page-2')">ඊළඟ පිටුව &raquo;</button>
                </div>
            </div>

            <!-- ================= PAGE 2 ================= -->
            <div id="page-2" class="page-section" style="display: none;">
                <div>
                    <div id="married-sections">
                        <div class="section-title">3. දරුවන් පිළිබඳ විස්තර</div>
                        
                        <div class="form-group">
                            <label>I. දරුවන් සිටීනම් ඔබ සමඟ ද යන වග :</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="does_applicant_have_children_residing" value="yes" onclick="toggleContainer('children-details', true)" onkeydown="handleEnter(event, 'child-count-input')"> ඔව්
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="does_applicant_have_children_residing" value="no" onclick="toggleContainer('children-details', false)" onkeydown="handleEnter(event, 'has_spouse')"> නැත
                                </label>
                            </div>
                        </div>

                        <div id="children-details" style="display: none;">
                            <div class="form-group">
                                <label for="child-count-input">දරුවන් සංඛ්‍යාව ඇතුළත් කරන්න:</label>
                                <div class="action-row" style="display: flex; gap: 10px; align-items: center;">
                                    <input type="number" id="child-count-input" min="1" max="10" step="1" class="form-input" oninput="generateChildRows()" style="width: 120px; padding: 10px; font-size: 14px;">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>II. දරුවන් පිළිබඳ විස්තර :</label>
                                <table class="custom-table" id="children-table">
                                   <thead>
                                        <tr>
                                            <th>සම්පූර්ණ නම</th>
                                            <th>ස්ත්‍රී / පුරුෂ භාවය</th>
                                            <th>උපන් දිනය</th>
                                            <th>සිවිල් තත්වය</th>
                                            <th>ක්‍රියාමාර්ග</th>
                                        </tr>
                                    </thead>
                                    <tbody id="children-tbody"></tbody>
                                </table>                                                                                                                                              
                            </div>
                            <p class="note-text">(රජයේ රැකියාවల නියුතු දරුවකු පදිංචි කර ගැනීමට බලාපොරොත්තු වන්නේ නම්, ඒ සඳහා විශේෂ අවසරයක් ලබා ගත යුතුය.)</p>
                        </div>

                        <div class="section-title">4. කලත්‍රයා (භාර්යාව / ස්වාමිපුරුෂයා) පිළිබඳ විස්තර </div>
                        
                        <div class="form-group full-width">
                            <label>කලත්‍රයා සිටීනම් ඔබ සමඟ ද යන වග :</label>
                            <div class="radio-group">
                                <label class="radio-label"><input type="radio" name="does_applicant_have_spouse_residing" value="yes" onclick="toggleContainer('spouse-details', true)" onkeydown="handleEnter(event, 'spouse_name')"> ඔව්</label>
                                <label class="radio-label"><input type="radio" name="does_applicant_have_spouse_residing" value="no" onclick="toggleContainer('spouse-details', false)" onkeydown="handleEnter(event, 'permanent_address')"> නැත</label>
                            </div>
                        </div>

                        <div id="spouse-details" style="display: none;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>I. සම්පූර්ණ නම :</label>
                                     <input type="text" name="spouse_full_name" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_national_id')">
                                </div>
                                <div class="form-group">
                                    <label>II. ජාතික හැඳුනුම්පත් අංකය :</label>
                                    <input type="text" name="spouse_national_id" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_occupation')">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>III. රැකියාව :</label>
                                 <input type="text" name="spouse_occupation" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_moving')">
                            </div>

                            <div class="form-group">
                                <label>IV. ඔබට දුම්රිය නිවාසයක් හිමි වුවහොත්, කලත්‍රයා එහි පදිංචියට පැමිණෙන්නේද / නැද්ද යන වග :</label>
                                <div class="radio-group" style="margin-top: 5px;">
                                    <label class="radio-label"><input type="radio" name="spouse_will_reside_in_quarters" value="Will Come" onkeydown="handleEnter(event, 'spouse_gov_job')"> පැමිණේ</label>
                                    <label class="radio-label"><input type="radio" name="spouse_will_reside_in_quarters" value="Will Not Come" onkeydown="handleEnter(event, 'spouse_gov_job')"> පැමිණෙන්නේ නැත</label>
                                </div>
                            </div>

                            <div class="section-title">5. කලත්‍රයා රජයේ රැකියාවක නියුතු නම් </div>
                            
                            <div class="form-group full-width">
                                <label>කලත්‍රයා රජයේ රැකියාවක නියුතු වේද?</label>
                                <div class="radio-group">
                                    <label class="radio-label"><input type="radio" name="is_applicant_spouse_gov_employee" value="yes" onclick="toggleContainer('gov-spouse-details', true)" onkeydown="handleEnter(event, 'spouse_department')"> ඔව්</label>
                                    <label class="radio-label"><input type="radio" name="is_applicant_spouse_gov_employee" value="no" onclick="toggleContainer('gov-spouse-details', false)" onkeydown="handleEnter(event, 'permanent_address_sec6')"> නැත</label>
                                </div>
                            </div>

                            <div id="gov-spouse-details" style="display: none;">
                                <div class="form-row" style="margin-top: 10px;">
                                    <div class="form-group">
                                        <label>I. සේවය කරන දෙපාර්තමේන්තුව :</label>
                                        <input type="text" name="spouse_department" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_service_number')">
                                    </div>
                                    <div class="form-group">
                                        <label>සේවා අංකය :</label>
                                        <input type="text" name="spouse_service_number" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_position')">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>II. තනතුර :</label>
                                    <input type="text" name="spouse_designation" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_institution')">
                                </div>

                                <div class="form-group">
                                    <label>III. ආයතන ප්‍රධානියා හා ඔහුගේ ලිපිනය:</label>
                                    <input type="text" name="spouse_institution_head_and_address" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_current_post')">
                                </div>

                                <div class="form-group">
                                    <label>IV. දැනට සේවය කරනු ලබන ස්ථානය හා ලිපිනය :</label>
                                    <input type="text" name="spouse_work_station_and_address" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'spouse_annual_salary')">
                                </div>

                                <div class="form-group">
                                    <label>V. දැනට ලබන වාර්ෂික වැටුප :</label>
                                    <span style="font-weight: bold;">Rs.</span>
                                    <input type="number" name="spouse_annual_salary" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-title">6. ස්ථිර පදිංචිය </div>
                    
                    <div class="form-group">
                        <label>I. ස්ථිර පදිංචි ස්ථානයේ ලිපිනය :</label>
                        <input type="text" id="permanent_address_sec6" name="permanent_residence_address" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'owner_is_self')">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>II. එහි හිමිකරු ඔබ ද?</label>
                            <div class="radio-group" style="margin-top: 5px;">
                                <label class="radio-label"><input type="radio" name="is_applicant_owner_of_residence" value="yes" onclick="toggleOwnerRelationship(false)" onkeydown="handleEnter(event, 'owner_from_home')"> ඔව්</label>
                                <label class="radio-label"><input type="radio" name="is_applicant_owner_of_residence" value="no" onclick="toggleOwnerRelationship(true)" onkeydown="handleEnter(event, 'owner_relationship')"> නැත</label>
                            </div>
                            <div id="owner-relationship-group" style="margin-top: 10px; display: none;">
                                <label style="display: block; margin-bottom: 5px;">එසේ නොමැති නම් හිමිකරුට ඇති සම්බන්ධතාවය :</label>
                                <input type="text" name="owner_relationship_if_not_self" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'commute_from_home')">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>III. ඔබ රාජකාරියට පැමිණෙන්නේ එහි සිට ද?</label>
                            <div class="radio-group" style="margin-top: 5px;">
                                <label class="radio-label"><input type="radio" name="does_applicant_commute_from_residence" value="yes" onkeydown="handleEnter(event, 'permanent-district')"> ඔව්</label>
                                <label class="radio-label"><input type="radio" name="does_applicant_commute_from_residence" value="no" onkeydown="handleEnter(event, 'permanent-district')"> නැත</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="permanent-district">IV. ස්ථිර පදිංචි දිස්ත්‍රික්කය :</label>
                            <select id="permanent-district" name="permanent_residence_district" class="dropdown-select" onkeydown="handleEnter(event, 'nearest-city-distance')">
                                <option value="" disabled selected>-- තෝරන්න --</option>
                                <?php foreach ($districts as $districtOption): ?>
                                    <option value="<?= htmlspecialchars($districtOption['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($districtOption['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>V. ආසන්නතම ප්‍රධාන නගරය හා ඊට ඇති දුර ප්‍රමාණය :</label>
                            <input type="text" name="nearest_major_city_name" class="table-input" placeholder="ආසන්නතම නගරය">
                            <input type="number" id="nearest-city-distance" name="distance_to_nearest_city_km" class="table-input" min="0" step="0" placeholder="(කි.මී.)" onkeydown="handleEnter(event, 'branch-to-work-distance')">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>VI. ශාඛා ලිපියක සිට සේවා ස්ථානයට ඇති දුර ප්‍රමාණය :</label>
                        <input type="number" id="branch-to-work-distance" name="distance_from_branch_post_to_work_km" class="table-input" min="0" step="1" style="max-width: 200px; margin-left: 10px;" placeholder="(කි.මී.)">
                    </div>

                    <div class="section-title">7. දැනට කුලී නිවාසයක පදිංචි වී සිටී නම් </div>
                    
                    <div class="form-group full-width">
                        <label>දැනට කුලී නිවාසයක පදිංචි වී සිටී ද?</label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="is_applicant_currently_renting" value="yes" onclick="toggleContainer('rented-house-details', true)" onkeydown="handleEnter(event, 'house_owner')"> ඔව්</label>
                            <label class="radio-label"><input type="radio" name="is_applicant_currently_renting" value="no" onclick="toggleContainer('rented-house-details', false)"> නැත</label>
                        </div>
                    </div>

                    <div id="rented-house-details" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>I. එහි අයිතිකරු :</label>
                                <input type="text" name="rented_house_owner_name" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'house_location')">
                            </div>
                            <div class="form-group">
                                <label>II. පිහිටි ස්ථානය :</label>
                                 <input type="text" name="rented_house_location" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'rented_address')">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>III. එහි ලිපිනය :</label>
                             <input type="text" name="rented_house_address" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'rent_date')">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="rent-date">IV. එහි පදිංචියට ගිය දිනය :</label>
                                <input type="date" id="rent-date" name="rented_house_occupancy_date" class="form-input-date" onkeydown="handleEnter(event, 'monthly_rent')">
                            </div>
                            <div class="form-group">
                                <label>V. ගෙවන මාසික කුලිය :</label>
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <span style="font-weight: bold;">Rs.</span>
                                    <input type="number" name="rented_house_monthly_rent" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="button-container space-between" style="margin-top: 20px; display: flex; justify-content: space-between;">
                    <button type="button" class="nav-btn" onclick="changePage('page-2', 'page-1')">&laquo; පසුපසට</button>
                    <button type="button" class="nav-btn" onclick="changePage('page-2', 'page-3')">ඊළඟ පිටුව &raquo;</button>
                </div>
            </div>

            <!-- ================= PAGE 3 ================= -->
            <div id="page-3" class="page-section" style="display: none;">
                <div>
                    <div class="section-title">8. ඔබ ඇවිදීමට අපහසු ආබාධිත හෝ රෝගාබාධයකින් පෙළෙන්නේ නම්, ඒ බව සඳහන් කරන්න.</div>
                    
                    <div class="form-group full-width">
                        <div class="radio-group" style="margin-bottom: 10px;">
                            <label class="radio-label"><input type="radio" name="does_applicant_have_disability" value="yes" onclick="toggleContainer('disability-details', true)" onkeydown="handleEnter(event, 'disability_details')"> ඔව්</label>
                            <label class="radio-label"><input type="radio" name="does_applicant_have_disability" value="no" onclick="toggleContainer('disability-details', false)" onkeydown="handleEnter(event, 'quarter_number')"> නැත</label>
                        </div>
                    </div>

                    <div id="disability-details" style="display: none;">
                        <div class="form-group">
                            <label>රෝගය සහ එහි ස්වභාවය පිළිබඳ විස්තර :</label>
                            <input type="text" name="disability_or_illness_details" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'medical-cert')">
                        </div>
                        <div class="form-group" style="margin-top: 10px;">
                            <label for="medical-cert" title="මෙම ගොනුව ස්වයංක්‍රීයව ඔබගේ ජාතික හැඳුනුම්පත් අංකයෙන් rename කරනු ලැබේ">රජයේ වෛද්‍ය සහතිකය (PDF පමණි) :</label>
                            <input type="file" id="medical-cert" name="medical_certificate_file_path" class="form-input" accept=".pdf" onmouseover="showRenameHint(this, 'ජාතික හැඳුනුම්පත් අංකය භාවිතයෙන් rename කරන්න')" onmouseout="hideRenameHint(this)" onkeydown="handleEnter(event, 'quarter_number')">
                        </div>
                        <p class="note-text">(රෝගය, රෝගය සහ එහි ස්වභාවය පැහැදිලි කල රජයේ වෛද්‍ය සහතිකයක් මගින් සනාථ කළ යුතුය)</p>
                    </div>

                    <div class="section-title">9. ඔබ, මීට පෙර, දුම්රිය නිවාසයක පදිංචිව සිටියේ නම් </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>I. එහි අංකය :</label>
                            <input type="text" name="previous_quarter_number" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'moved-out-date')">
                        </div>
                        <div class="form-group">
                            <label for="moved-out-date">ඉවත් වූ දිනය :</label>
                            <input type="date" id="moved-out-date" name="previous_quarter_vacated_date" class="form-input-date" onkeydown="handleEnter(event, 'reason_for_moving_out')">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>ඉවත් වීමට හේතුව :</label>
                        <input type="text" name="previous_quarter_vacate_reason" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'rejected_quarters')">
                    </div>

                    <div class="form-group full-width">
                        <label>II. මීට පෙර ඔබ වෙනුවෙන් වෙන් කරන ලද දුම්රිය නිවාසයක් ප්‍රතික්ෂේප කර තිබේ ද?</label>
                        <div class="radio-group" style="margin-top: 5px; margin-bottom: 10px;">
                            <label class="radio-label"><input type="radio" name="has_applicant_rejected_quarter_before" value="yes" onclick="toggleContainer('rejected-details', true)" onkeydown="handleEnter(event, 'rejected_details')"> ඔව්</label>
                            <label class="radio-label"><input type="radio" name="has_applicant_rejected_quarter_before" value="no" onclick="toggleContainer('rejected-details', false)" onkeydown="handleEnter(event, 'applied_before')"> නැත</label>
                        </div>
                    </div>

                    <div id="rejected-details" style="display: none;">
                        <div class="form-group">
                            <label>එසේ නම්, ඒ පිළිබඳ විස්තර :</label>
                            <input type="text" name="rejected_quarter_details" class="table-input" style="padding: 10px; font-size: 14px; width: 100%;" onkeydown="handleEnter(event, 'applied_before')">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>III. මීට පෙර දුම්රිය නිවාසයක් ලබා ගැනීම සඳහා අයදුම්පතක් ඉදිරිපත් කර තිබේ ද?</label>
                        <div class="radio-group" style="margin-top: 5px;">
                            <label class="radio-label"><input type="radio" name="has_applicant_applied_before" value="yes" onclick="toggleContainer('applied-before-details', true)" onkeydown="handleEnter(event, 'applied-date-prev')"> ඔව්</label>
                            <label class="radio-label"><input type="radio" name="has_applicant_applied_before" value="no" onclick="toggleContainer('applied-before-details', false)" onkeydown="handleEnter(event, 'declaration_year')"> නැත</label>
                        </div>
                    </div>

                    <div id="applied-before-details" style="display: none;">
                        <div class="form-row" style="margin-top: 10px;">
                            <div class="form-group">
                                <label for="applied-date-prev">අයදුම් කළ දිනය :</label>
                                <input type="date" id="applied-date-prev" name="previous_application_date" class="form-input-date" onkeydown="handleEnter(event, 'prev_house_type')">
                            </div>
                            <div class="form-group">
                                <label>නිවාස වර්ගය :</label>
                                <select name="previous_applied_quarter_type" class="dropdown-select" onkeydown="handleEnter(event, 'prev_house_location')">
                                    <option value="" disabled selected>-- තෝරන්න --</option>
                                    <?php foreach ($quarterTypes as $opt): ?><option value="<?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>ස්ථානය :</label>
                            <select name="previous_applied_quarter_location" class="dropdown-select" onkeydown="handleEnter(event, 'declaration_year')">
                                <option value="" disabled selected>-- තෝරන්න --</option>
                                <?php foreach ($quarterLocations as $opt): ?><option value="<?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($opt['name'], ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Section 10 -->
                    <div class="section-title">10. ප්‍රකාශය </div>
                    <div class="form-group full-width declaration-text" style="font-size: 14px; line-height: 1.8;">
                        <p>මා හට දුම්රිය නිවාසයක් වෙන් කරනු ලැබුවහොත්,</p>
                        <p>I. මගේ පවුලේ සාමාජිකයින් සමග පදිංචිව සිටින බවත්,</p>
                        <p>II. දැනුම් දීමකින් තොරව, නඩත්තු කරුවන් හෝ පිටස්තර පුද්ගලයින් එහි පදිංචි කර නොගන්නා බවත්,</p>
                        <p>III. මෙම ඉල්ලුම්පත්‍රයේ සඳහන් කරුණු (වේතනය වෙනස් වීම, දරුවන්ගේ උපත්, විවාහයන්, අභාවයන්, දික්කසාද වීම හා දරුවන්ගේ කලත්‍රයා රැකියාවක යෙදීම) මින් පසුව වෙනස් වුවහොත්, ඒ පිළිබඳව වාර්තා කිරීමටත්,</p>
                        <p>IV. දෙපාර්තමේන්තුව මගින් දැනුම් දීමෙන් පසුව හෝ අවශ්‍ය වූ විටක, මගේ යැපෙන්නන් සමඟ මෙම නිවාසයෙන් ඉවත්ව, එය හිස් කර භාර දීමේ කොන්දේසියට යටත්ව භාර ගනිමි.</p>
                        
                        <div style="margin-top: 15px; line-height: 2.2;">
                            ඉහත සඳහන් කර ඇති තොරතුරු සත්‍ය හා නිවැරදි බවත්, වැරදි ප්‍රකාශයක් කළ බවට තහවුරු වුවහොත්, කෙදිනක හෝ මට විරුද්ධව විනයානුකූලව කටයුතු කරනු ලැබීමට ඉඩ ඇති බවට මම දැනුවත් බවත් මෙයින් ප්‍රකාශ කරමි. මම දැනට වෙනත් දුම්රිය නිවාසයක ප්‍රධාන පදිංචිකරු නොවන බව ද සහතික කරමි. 
                            වර්ෂ 
                            <input type="number" name="declaration_salary_year" class="table-input" placeholder="වර්ෂය" min="2000" max="2100" style="width: 90px; padding: 5px; display: inline-block; margin: 0 5px;" required onkeydown="handleEnter(event, 'declaration_month')">
                            වර්ෂයේ 
                            <select name="declaration_salary_month" class="dropdown-select" style="width: auto; display: inline-block; padding: 6px; margin: 0 5px;" onkeydown="handleEnter(event, 'salary-cert')">
                                <option value="" disabled selected>-- මාසය --</option>
                                <option value="January">ජනවාරි</option>
                                <option value="February">පෙබරවාරි</option>
                                <option value="March">මාර්තු</option>
                                <option value="April">අප්‍රේල්</option>
                                <option value="May">මැයි</option>
                                <option value="June">ජූනි</option>
                                <option value="July">ජූලි</option>
                                <option value="August">අගෝස්තු</option>
                                <option value="September">සැප්තැම්බර්</option>
                                <option value="October">ඔක්තෝබර්</option>
                                <option value="November">නොවැම්බර්</option>
                                <option value="December">දෙසැම්බර්</option>
                            </select>
                            මාසයට අදාළ වැටුප් පත්‍රිකාවේ ඡායා පිටපතක් අමුණා ඇත.
                        </div>

                        <div class="form-group" style="margin-top: 15px;">
                            <label for="salary-cert" title="මෙම ගොනුව ස්වයංක්‍රීයව ඔබගේ වැටුපත් අංකයෙන් rename වේ"> වැටුප් පත්‍රිකාවේ ඡායා පිටපත (PDF පමණි) :</label>
                            <input type="file" id="salary-cert" name="salary_certificate_file_path" class="form-input" accept=".pdf" onmouseover="showRenameHint(this, 'වැටුපත් අංකය භාවිතයෙන් rename කරන්න')" onmouseout="hideRenameHint(this)" onkeydown="handleEnter(event, 'page3-date')">
                        </div>
                    </div>

                    <div class="form-row" style="margin-top: 20px; display: flex; justify-content: space-between; align-items: flex-end;">
                        <div class="form-group">
                            <label for="page3-date">දිනය :</label>
                            <input type="date" id="page3-date" name="declaration_date" class="form-input-date">
                        </div>
                        <div class="signature-box print-only-element" style="text-align: right; margin-top: 20px;">
                            <div style="border-bottom: 1px dotted #333; width: 250px; display: inline-block; margin-bottom: 5px;"></div>
                            <p style="margin: 0; font-size: 14px;">අයදුම්කරුගේ අත්සන</p>
                        </div>
                    </div>

                    <div id="print-approval-section">
                        <fieldset style="border: 1px solid #ccc; padding: 20px; border-radius: 5px;">
                            <legend style="font-weight: bold; color: #0f4c81; padding: 0 10px;">
                                නියෝජ්‍ය අධිකාරී (ගමනාගමන) කොළඹ 
                            </legend>

                            <p style="font-size: 14px; margin-top: 10px; margin-bottom: 10px;">මා දන්නා තරමින්,</p>
                            
                            <ul style="font-size: 14px; margin-left: 20px; margin-bottom: 20px; line-height: 1.6;">
                                <li>ඉහත සඳහන් තොරතුරු නිවැරදි බවත් / සංශෝධිත පරිදි නිවැරදි බවත්,</li>
                                <li>අයදුම්කරු / කලත්‍රයා හෝ දරුවන් සතුව, සැතපුම් 15 කට මෙපිට නිවාස දේපලක් ඇති / නොමැති බවත්,</li>
                                <li>මොහුගේ නිවාස අයදුම්පත්‍රය, නිර්දේශ කරන / නොකරන බවත් මෙයින් දන්වා සිටිමි.</li>
                            </ul>

                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px;">අයදුම් කරන නිවාස වර්ගය හා අංකය :</label>
                                <input type="text" style="width: 100%; padding: 8px; border: 1px solid #ccc;" readonly>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 5px;">සේවයට ඇති අත්‍යවශ්‍ය බව (රාත්‍රී වැඩ මුර වැනි) හා වෙනත් කරුණු ඇත්නම් :</label>
                                <textarea rows="2" style="width: 100%; padding: 8px; border: 1px solid #ccc;" readonly></textarea>
                            </div>

                            <div style="display: flex; justify-content: space-between; margin-top: 20px; align-items: flex-end;">
                                <div>
                                    <label style="display: block; margin-bottom: 5px;">දිනය :</label>
                                    <input type="date" class="form-input-date" style="width: 180px;">
                                </div>
                                <div style="text-align: right;">
                                    <div style="border-bottom: 1px dotted #333; width: 280px; display: inline-block; margin-bottom: 5px;"></div>
                                    <p style="margin: 0; font-size: 13px;">දු.ස්ථා.අධි / ප්‍ර.දු.ස්ථා. / දු.ස්ථා / උ.දු.ස්ථා. / ආසන්නම ජ්‍යෙෂ්ඨ නිලධාරියාගේ අත්සන හා නිල මුද්‍රාව</p>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <div class="clerk-approval-section">
                        <fieldset style="border: 1px solid #ccc; padding: 20px; border-radius: 5px; margin-top: 20px;">
                            <legend style="font-weight: bold; color: #0f4c81; padding: 0 10px;">
                                පෞද්ගලික ලිපි ගොනුව අනුව විෂය ලිපිකරුගේ නිර්දේශය
                            </legend>

                            <p style="font-size: 14px; font-weight: bold; margin-top: 10px; margin-bottom: 15px;">
                                ඉහත සඳහන් නිවාස අයදුම්කරු රජයේ / දුම්රිය නිවාසයක් ලබා ගැනීමට ඇති හිමිකම පිළිබඳ නිර්දේශය :
                            </p>
                            
                            <div style="margin-bottom: 15px;">
                                <label>I. විනය නියෝගයක් මත, රජයේ / දුම්රිය නිවාස අයිතිය අහිමි කර තිබේ ද?</label>
                                <div style="display: flex; gap: 20px; margin-top: 5px;">
                                    <label><input type="radio" name="clerk_is_right_forfeited" value="Yes"> ඔව්</label>
                                    <label><input type="radio" name="clerk_is_right_forfeited" value="No"> නැත</label>
                                </div>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label>II. ඔව් නම්, විනය නියෝගය අවසන් වන දිනය :</label>
                                <input type="date" name="clerk_forfeit_expiry_date" class="form-input-date" style="margin-top: 5px;">
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label>III. මිහිඳුසෙත්පුර නිවාස ලාභියකු / ලාභිනියක ද?</label>
                                <div style="display: flex; gap: 20px; margin-top: 5px;">
                                    <label><input type="radio" name="clerk_is_mihindusethpura_recipient" value="Yes"> ඔව්</label>
                                    <label><input type="radio" name="clerk_is_mihindusethpura_recipient" value="No"> නැත</label>
                                </div>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label>IV. පෞද්ගලික ලිපිගොනුව අනුව වෙනත් රජයේ / දුම්රිය නිවාසයක පදිංචිව සිටී / නොසිටී ද?</label>
                                <div style="display: flex; gap: 20px; margin-top: 5px;">
                                    <label><input type="radio" name="clerk_residing_in_other_gov_quarter" value="Yes"> ඔව්</label>
                                    <label><input type="radio" name="clerk_residing_in_other_gov_quarter" value="No"> නැත</label>
                                </div>
                            </div>

                            <div style="margin-bottom: 15px;">
                                <label>V. වෙනත් කරුණු :</label>
                                <textarea name="clerk_other_remarks" rows="2" class="table-input" style="margin-top: 5px; width: 100%;"></textarea>
                            </div>

                            <div style="display: flex; justify-content: space-between; margin-top: 20px; align-items: flex-end;">
                                <div>
                                    <label style="display: block; margin-bottom: 5px;">කැබිනට් අංකය :</label>
                                    <input type="text" class="table-input" style="width: 220px; padding: 6px;">
                                </div>
                                <div style="text-align: right;">
                                    <div style="border-bottom: 1px dotted #333; width: 220px; display: inline-block; margin-bottom: 5px;"></div>
                                    <p style="margin: 0; font-size: 13px;">විෂය ලිපිකරුගේ අත්සන</p>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <div class="button-container space-between" style="margin-top: 25px; display: flex; justify-content: space-between;">
                    <button type="button" class="nav-btn" onclick="changePage('page-3', 'page-2')">&laquo; පසුපසට</button>
                    <div>
                        <button type="button" class="nav-btn print-btn" onclick="printApplication()" style="background-color: #28a745; color: white; margin-right: 10px; padding: 10px 20px; border: none; cursor: pointer;">PDF ලෙස මුද්‍රණය කරන්න</button>
                        <button type="submit" class="submit-btn" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer;">අයදුම්පත යොමු කරන්න</button>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <script>
        let currentPage = 1;
        
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
                <td><select class="dropdown-select table-select" name="${deptName}[]"><option value="" disabled selected>-- තෝරන්න --</option>${options}</select></td>
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
                            <option value="" disabled selected>-- තෝරන්න --</option>
                            <option value="male">පුරුෂ</option>
                            <option value="female">ස්ත්‍රී</option>
                        </select>
                    </td>
                    <td><input type="date" class="form-input-date" name="child_date_of_birth[]"></td>
                    <td>
                        <select class="dropdown-select table-select" name="child_civil_status[]">
                            <option value="" disabled selected>-- තෝරන්න --</option>
                            <option value="unmarried">අවිවාහක</option>
                            <option value="married">විවාහක</option>
                            <option value="divorced">දික්කසාද</option>
                            <option value="widowed">වැන්දඹු</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="clear-row-btn" onclick="clearChildRow(this)">Clear</button>
                    </td>
                `;
                tbody.appendChild(row);
            }
        }

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