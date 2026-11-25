<?php
/**
 * Generate Official Letter - Sri Lanka Railways
 * ------------------------------------------------
 * Auto-fills all fields from the database when opened via
 * final_Approve_SC.php?app_id=XX. All fields remain editable.
 *
 * Letter format: Subject Clerk → Building Department Foreman (BDF)
 * requesting approval for quarter allocation.
 *
 * Features:
 *   - Auto-fill from DB
 *   - Generate Preview (HTML)
 *   - Generate PDF Blob (html2pdf.js)
 *   - Save PDF to Database (AJAX → /letters/save-pdf)
 *   - Send Letter to BDF (AJAX → /letters/send-to-bdf)
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Generate Official Letter - Sri Lanka Railways";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

// ============================================================
// AUTO-FILL: Fetch applicant data from database using app_id
// ============================================================
$appId = filter_input(INPUT_GET, 'app_id', FILTER_VALIDATE_INT);
$prefill = [
    'appNo'             => '',
    'nic'               => '',
    'fullName'          => '',
    'designation'       => '',
    'receiverAddress'   => '',
    'quarterType'       => '',
    'quarterName'       => '',
    'applicationId'     => 0,
];

if ($appId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare(
        "SELECT a.id,
                a.application_no,
                a.nic,
                a.applied_quarter_number,
                a.applied_quarter_location,
                u.name,
                COALESCE(u.applicant_designation, '')            AS designation,
                COALESCE(u.applicant_current_work_station, '')   AS workplace,
                aq.quarter_no   AS allocated_quarter_no,
                aq.quarter_name AS allocated_quarter_name,
                aq.quarter_location AS allocated_quarter_location
         FROM applications AS a
         INNER JOIN users AS u ON u.nic = a.nic
         LEFT JOIN allocated_quarters AS aq
             ON aq.nic = a.nic AND aq.source = 'normal'
         WHERE a.id = ?
         LIMIT 1"
    );
    $stmt->bind_param('i', $appId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($row) {
        $prefill['applicationId']   = (int)$row['id'];
        $prefill['appNo']           = $row['application_no'] ?? '';
        $prefill['nic']             = $row['nic'] ?? '';
        $prefill['fullName']        = $row['name'] ?? '';
        $prefill['designation']     = $row['designation'] ?? '';
        $prefill['receiverAddress'] = $row['workplace'] ?? '';

        // Prefer allocated quarter; fall back to applied quarter
        if (!empty($row['allocated_quarter_no'])) {
            $prefill['quarterType'] = $row['allocated_quarter_no'];
            $prefill['quarterName'] = $row['allocated_quarter_name']
                ?: ($row['allocated_quarter_location'] ?? '');
        } else {
            $prefill['quarterType'] = $row['applied_quarter_number'] ?? '';
            $prefill['quarterName'] = $row['applied_quarter_location'] ?? '';
        }
    }
}

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-y: auto;
        background-color: #f8fafc;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container {
        max-width: 900px;
        margin: 20px auto;
        padding: 0 20px;
        width: 100%;
        box-sizing: border-box;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #7A1C1C;
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 20px;
        font-size: 0.85rem;
        transition: color 0.2s;
    }
    .back-link:hover { text-decoration: underline; }

    .user-profile-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
    }

    .user-profile-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #7A1C1C;
        color: #ffffff;
        padding: 6px 14px;
        border-radius: 30px;
        cursor: pointer;
        user-select: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .profile-dropdown {
        display: none;
        position: absolute;
        left: 0;
        top: 105%;
        background: #ffffff;
        min-width: 220px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
        z-index: 100;
        border: 1px solid #e2e8f0;
    }

    .profile-dropdown.show {
        display: block;
    }

    .profile-dropdown a {
        color: #1e293b;
        padding: 10px 15px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        transition: background 0.2s;
    }

    .profile-dropdown a:hover {
        background: #f1f5f9;
        color: #7A1C1C;
    }

    .profile-dropdown a.logout-btn {
        color: #ef4444;
    }

    .profile-dropdown a.logout-btn:hover {
        background: #fee2e2;
    }

    .card {
        background: #ffffff;
        padding: 30px;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
        border-top: 5px solid #7A1C1C;
        margin-bottom: 30px;
    }
    .card-title {
        color: #0a0a0a;
        font-size: 1.6rem;
        font-weight: 700;
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        border-bottom: 2px solid #f3f4f6;
        padding-bottom: 10px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
    }
    .form-group input, .form-group select, .form-group textarea {
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s;
        width: 100%;
        box-sizing: border-box;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        border-color: #7A1C1C;
    }
    .form-group input[readonly] {
        background-color: #f1f5f9;
        color: #475569;
        cursor: default;
    }
    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .section-subheading {
        font-size: 1.05rem;
        font-weight: 700;
        color: #7A1C1C;
        margin: 20px 0 10px 0;
        border-bottom: 1px dashed #cbd5e1;
        padding-bottom: 5px;
    }

    .btn-generate, .btn-save-letter, .btn-send-bdf {
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        font-size: 0.95rem;
        color: white;
    }
    .btn-generate { background-color: #7A1C1C; }
    .btn-generate:hover { background-color: #571a1a; }

    .btn-save-letter {
        background: linear-gradient(135deg, #059669, #047857);
        display: none;
    }
    .btn-save-letter:hover { background: linear-gradient(135deg, #047857, #065f46); }

    .btn-send-bdf {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        display: none;
    }
    .btn-send-bdf:hover { background: linear-gradient(135deg, #1d4ed8, #1e40af); }

    .btn-generate:disabled, .btn-save-letter:disabled, .btn-send-bdf:disabled {
        background: #9ca3af !important;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .btn-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    .success-alert {
        display: none;
        background: #dcfce7;
        color: #166534;
        padding: 14px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 600;
        border: 1px solid #bbf7d0;
        text-align: center;
    }
    .success-alert.show { display: block; }

    .autofill-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #dbeafe;
        color: #1e40af;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .loading-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    .loading-overlay.show { display: flex; }
    .loading-box {
        background: #fff;
        padding: 30px 40px;
        border-radius: 12px;
        text-align: center;
    }
    .loading-box i { font-size: 2.5rem; color: #7A1C1C; margin-bottom: 10px; }
    .loading-box p { margin: 0; font-weight: 600; color: #1e293b; }

    .letter-preview-box {
        background: #fff;
        padding: 45px 50px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
        font-family: 'Times New Roman', Times, serif;
        color: #000000;
        line-height: 1.6;
        font-size: 12pt;
        margin-top: 30px;
        display: none;
    }

    .letter-actions-bar {
        display: none;
        justify-content: flex-end;
        margin-top: 15px;
        margin-bottom: 40px;
    }

    .print-btn {
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
        text-decoration: none;
        transition: background 0.2s;
        background-color: #0284c7;
        color: white;
    }

    .print-btn:hover {
        background-color: #0369a1;
    }

    .custom-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
        padding: 15px;
    }

    .custom-modal-content {
        background-color: #fff;
        padding: 30px;
        border-radius: 12px;
        width: 100%;
        max-width: 380px;
        text-align: center;
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }

    .custom-modal-content i {
        font-size: 2.8rem;
        color: #7A1C1C;
        margin-bottom: 15px;
    }

    .custom-modal-content h3 {
        color: #1e293b;
        margin-bottom: 10px;
        font-size: 1.3rem;
    }

    .custom-modal-content p {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 20px;
    }

    .modal-action-btns {
        display: flex;
        justify-content: center;
        gap: 12px;
    }

    .btn-modal-confirm, .btn-modal-cancel {
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        color: white;
    }

    .btn-modal-confirm { background-color: #2563eb; }
    .btn-modal-cancel { background-color: #64748b; }

    @media print {
        @page {
            size: A4;
            margin: 15mm 20mm;
        }
        body {
            background: white !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        header, footer, nav, .site-header, .site-footer, #adminHeader, #adminFooter {
            display: none !important;
        }
        .no-print {
            display: none !important;
        }
        #letterPreviewBox {
            display: block !important;
            position: relative !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            background: white !important;
            color: #000000 !important;
            font-size: 12pt !important;
            line-height: 1.6 !important;
        }
    }

    @media (max-width: 768px) {
        .container {
            margin: 15px auto;
            padding: 0 10px;
        }
        .letter-preview-box {
            padding: 20px 15px;
            font-size: 11pt;
        }
    }
</style>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-box">
        <i class="fa-solid fa-spinner fa-spin"></i>
        <p id="loadingText">Processing...</p>
    </div>
</div>

<div class="container no-print">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
        <a href="SC_dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <?php else: ?>
        <div class="user-profile-wrapper">
            <div class="user-profile-badge" onclick="toggleDropdown()">
                <div style="width: 35px; height: 35px; background: #ffffff; color: #7A1C1C; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem;">
                    <?php echo htmlspecialchars($userData['initials']); ?>
                </div>
                <div>
                    <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($userData['name']); ?></div>
                </div>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; color: #f3e8ff; margin-left: 5px;"></i>
            </div>

            <div id="profileDropdown" class="profile-dropdown">
                <a href="edit_profile.php"><i class="fa-solid fa-user-pen"></i> Edit Profile</a>
                <a href="SC_dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Success Alert -->
    <div class="success-alert" id="successAlert">
        <i class="fa-solid fa-circle-check"></i> <span id="successMessage"></span>
    </div>

    <div class="card">
        <h2 class="card-title">Generate Official Letter</h2>

        <?php if ($appId && $prefill['appNo']): ?>
            <div class="autofill-badge">
                <i class="fa-solid fa-database"></i>
                All fields auto-filled from database for Application: <strong><?php echo htmlspecialchars($prefill['appNo']); ?></strong>
                — You can edit any field below before generating.
            </div>
        <?php else: ?>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">
                Enter Application Number first to auto-fill applicant information from stored records.
            </p>
        <?php endif; ?>

        <!-- Hidden field to store the application ID for DB save -->
        <input type="hidden" id="dbApplicationId" value="<?php echo (int)$prefill['applicationId']; ?>">

        <form id="letterForm" onsubmit="generateLetter(event)">

            <div class="section-subheading">1. Application Reference</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Application Number (Enter first)</label>
                    <input type="text" id="appNo" placeholder="e.g. SLR/AOT/2026/0001"
                           oninput="autoFillApplicant(this.value)"
                           value="<?php echo htmlspecialchars($prefill['appNo']); ?>"
                           required>
                </div>
                <div class="form-group">
                    <label>Applicant NIC</label>
                    <input type="text" id="nic" placeholder="e.g. 901234567V"
                           value="<?php echo htmlspecialchars($prefill['nic']); ?>"
                           required>
                </div>
            </div>

            <div class="section-subheading">2. Applicant & Quarter Information</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Applicant Name</label>
                    <input type="text" id="fullName" placeholder="e.g. John Doe"
                           value="<?php echo htmlspecialchars($prefill['fullName']); ?>"
                           required>
                </div>
                <div class="form-group">
                    <label>Designation</label>
                    <input type="text" id="designation" placeholder="e.g. Departmental Officer"
                           value="<?php echo htmlspecialchars($prefill['designation']); ?>"
                           required>
                </div>
                <div class="form-group">
                    <label>Workplace</label>
                    <input type="text" id="receiverAddress" placeholder="e.g. Colombo Railway Station"
                           value="<?php echo htmlspecialchars($prefill['receiverAddress']); ?>"
                           required>
                </div>
                <div class="form-group">
                    <label>Requested Quarter No.</label>
                    <input type="text" id="quarterType" placeholder="e.g. Q-001"
                           value="<?php echo htmlspecialchars($prefill['quarterType']); ?>"
                           required>
                </div>
                <div class="form-group">
                    <label>Requested Quarter Name</label>
                    <input type="text" id="quarterName" placeholder="e.g. Sunflower Manor"
                           value="<?php echo htmlspecialchars($prefill['quarterName']); ?>"
                           required>
                </div>
            </div>

            <div class="section-subheading">3. Letter Subject & Content</div>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Letter Subject</label>
                    <input type="text" id="letterSubject"
                           value="<?php
                               if ($prefill['fullName']) {
                                   echo htmlspecialchars('Request for Quarter Allocation for ' . $prefill['fullName'] . ' (' . $prefill['designation'] . ')');
                               } else {
                                   echo 'Request for Quarter Allocation for Departmental Officer - ';
                               }
                           ?>"
                           required>
                </div>
                <div class="form-group full-width">
                    <label>Main Body Description</label>
                    <textarea id="mainDescription" rows="5" required>This is to formally request the allocation of government quarters for the above-mentioned officer of Sri Lanka Railways. The applicant has duly submitted all required documentation, service records, and clearances through the Quarters Management Branch. Given the urgent service requirements and official duties, the approval of the Board of Directors / Housing Board (BDF) is hereby requested to proceed with the direct quarter allocation.</textarea>
                </div>
            </div>

            <div class="btn-actions">
                <button type="submit" class="btn-generate" id="generateBtn">
                    <i class="fa-solid fa-eye"></i> Generate Official Letter Preview
                </button>
                <button type="button" class="btn-save-letter" id="saveBtn" onclick="saveLetterToDatabase()">
                    <i class="fa-solid fa-floppy-disk"></i> Save PDF to Database
                </button>
                <button type="button" class="btn-send-bdf" id="sendBdfBtn" onclick="confirmSendToBdf()">
                    <i class="fa-solid fa-paper-plane"></i> Send Letter to BDF
                </button>
            </div>
        </form>
    </div>
</div>

<div class="container" style="margin-top: 0;">
    <div id="letterPreviewBox" class="letter-preview-box">
        <div style="text-align: center; margin-bottom: 25px;">
            <h2 style="margin: 0; font-size: 14pt; font-weight: bold; text-transform: uppercase; color: #000;">SRI LANKA RAILWAYS</h2>
            <p style="margin: 3px 0; font-size: 11pt; font-weight: bold; color: #000;">Quarters Management Branch - AOT</p>
            <p style="margin: 2px 0; font-size: 10pt; color: #000;">Department of Railways, P.O. Box 355, Colombo 10, Sri Lanka</p>
            <hr style="border: 0; border-top: 1.5px solid #000; margin-top: 10px;">
        </div>

        <div style="margin-bottom: 20px; font-size: 12pt; line-height: 1.5;">
            Subject Clerk,<br>
            Quarters Management Branch,<br>
            Sri Lanka Railways - AOT.<br>
            <span id="pDate" style="display: inline-block; margin-top: 8px;"></span>
        </div>

        <div style="margin-bottom: 20px; font-size: 12pt;">
            Building Foreman,<br>
            Sri Lanka Railways, Colombo 10.
        </div>

        <div style="margin-bottom: 15px;">
            <p style="margin: 0 0 8px 0;">Dear Sir/Madam,</p>
            <h3 id="pSubjectTitle" style="font-size: 12pt; text-decoration: underline; margin: 8px 0; font-weight: bold; color: #000;"></h3>
        </div>

        <div style="margin-bottom: 25px; text-align: justify; line-height: 1.6;">
            <p id="pIntroPara" style="margin-bottom: 12px;"></p>
            <p id="pCustomDescPara" style="margin-bottom: 12px;"></p>
            <p style="margin: 0;">Therefore, it is kindly requested to grant formal approval from the BDF for the requested quarter allocation at your earliest convenience so that further administrative actions can be taken.</p>
        </div>

        <div style="margin-top: 35px; font-size: 12pt;">
            <p style="margin: 0 0 4px 0;">Thank you,</p>
            <p style="margin: 0 0 20px 0;">Yours faithfully,</p>
            <p style="margin: 0;">___________________________<br>
            <strong>Subject Clerk</strong>,<br>
            Quarters Management Branch,<br>
            Sri Lanka Railways - AOT.</p>
        </div>
    </div>

    <div id="letterActionsBar" class="letter-actions-bar no-print">
        <button type="button" class="print-btn" onclick="triggerPrint()">
            <i class="fa-solid fa-print"></i> Print / Save as PDF
        </button>
    </div>
</div>

<!-- Send to BDF confirmation modal -->
<div id="sendBdfModal" class="custom-modal">
    <div class="custom-modal-content">
        <i class="fa-solid fa-paper-plane"></i>
        <h3>Forward Letter to BDF?</h3>
        <p>This letter will appear on the BDF dashboard for final review. Please confirm.</p>
        <div class="modal-action-btns">
            <button type="button" class="btn-modal-cancel" onclick="closeSendBdfModal()">Cancel</button>
            <button type="button" class="btn-modal-confirm" onclick="executeSendToBdf()">Yes, Send to BDF</button>
        </div>
    </div>
</div>

<!-- Logout Modal -->
<div id="logoutModal" class="custom-modal">
    <div class="custom-modal-content">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to log out from the Subject Clerk portal?</p>
        <div class="modal-action-btns">
            <button type="button" class="btn-modal-cancel" onclick="closeLogoutModal()">Cancel</button>
            <button type="button" class="btn-modal-confirm" onclick="executeLogout()">Logout</button>
        </div>
    </div>
</div>

<!-- Load html2pdf.js for client-side PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    // ============================================================
    // Global state
    // ============================================================
    let generatedPdfBlob = null;
    let savedLetterId    = null;

    function toggleDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) {
            dropdown.classList.toggle('show');
        }
    }

    window.addEventListener('click', function(e) {
        if (!e.target.closest('.user-profile-wrapper')) {
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown && dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        }
    });

    function openLogoutModal(event) {
        event.preventDefault();
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) {
            dropdown.classList.remove('show');
        }
        document.getElementById('logoutModal').style.display = 'flex';
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    function executeLogout() {
        window.location.href = "login.php";
    }

    // ============================================================
    // Hardcoded fallback database
    // ============================================================
    const applicantsDatabase = {
        "SLR/AOT/2026/0001": {
            nic: "883421092V",
            fullName: "A. B. Perera",
            designation: "Station Master",
            receiverAddress: "Railway Station, Polgahawela",
            quarterType: "Q-01",
            quarterName: "Sunflower Manor"
        },
        "SLR/AOT/2026/0002": {
            nic: "925631485V",
            fullName: "S. K. Silva",
            designation: "Signal Inspector",
            receiverAddress: "Railway Signal Office, Colombo Fort",
            quarterType: "Q-02",
            quarterName: "Railway Bungalow 02"
        },
        "SLR/AOT/2026/0003": {
            nic: "951234789V",
            fullName: "M. N. Fernando",
            designation: "Loco Driver",
            receiverAddress: "Railway Shed, Anuradhapura",
            quarterType: "Q-03",
            quarterName: "Staff Flat 04"
        }
    };

    function autoFillApplicant(appNoValue) {
        const rawVal = appNoValue.trim();
        const trimmedKey = rawVal.toUpperCase().startsWith("SLR/AOT/") ? rawVal.toUpperCase() : "SLR/AOT/2026/" + rawVal;
        const matchedData = applicantsDatabase[trimmedKey];

        if (matchedData) {
            document.getElementById('nic').value = matchedData.nic;
            document.getElementById('fullName').value = matchedData.fullName;
            document.getElementById('designation').value = matchedData.designation;
            document.getElementById('receiverAddress').value = matchedData.receiverAddress;
            document.getElementById('quarterType').value = matchedData.quarterType;
            document.getElementById('quarterName').value = matchedData.quarterName;

            document.getElementById('letterSubject').value = "Request for Quarter Allocation for " + matchedData.fullName + " (" + matchedData.designation + ")";
        }
    }

    // ============================================================
    // Generate Letter Preview
    // ============================================================
    function generateLetter(event) {
        event.preventDefault();
        updatePreviewContent();

        document.getElementById('letterPreviewBox').style.display = 'block';
        document.getElementById('letterActionsBar').style.display = 'flex';
        document.getElementById('letterPreviewBox').scrollIntoView({ behavior: 'smooth' });

        generatePdfBlob();
    }

    function updatePreviewContent() {
        let appInput = document.getElementById('appNo').value.trim();
        let appNoClean = appInput.replace(/^SLR\/AOT\/2026\//i, '');
        if(!appNoClean) appNoClean = '0001';

        const nic = document.getElementById('nic').value || '---';
        const fullName = document.getElementById('fullName').value || '---';
        const designation = document.getElementById('designation').value || '---';
        const receiverAddress = document.getElementById('receiverAddress').value || '---';
        const quarterType = document.getElementById('quarterType').value || '---';
        const quarterName = document.getElementById('quarterName').value || '---';
        const letterSubject = document.getElementById('letterSubject').value || '---';
        const mainDescription = document.getElementById('mainDescription').value || '';

        document.getElementById('pSubjectTitle').innerText = letterSubject;

        const today = new Date().toISOString().split('T')[0];
        document.getElementById('pDate').innerText = today;

        document.getElementById('pIntroPara').innerHTML = `This letter is submitted regarding the quarter allocation request made by <strong>${fullName}</strong> (Designation: <strong>${designation}</strong>, NIC: <strong>${nic}</strong>, Workplace: <strong>${receiverAddress}</strong>) under Application Reference No: <strong>SLR/AOT/2026/${appNoClean}</strong> for Quarter No: <strong>${quarterType}</strong> (${quarterName}).`;

        document.getElementById('pCustomDescPara').innerHTML = mainDescription;
    }

    // ============================================================
    // Generate PDF Blob
    // ============================================================
    function generatePdfBlob() {
        const btn = document.getElementById('generateBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating PDF...';

        const element = document.getElementById('letterPreviewBox');
        const appNo = document.getElementById('appNo').value || 'unknown';
        const nic = document.getElementById('nic').value || 'unknown';

        const opt = {
            margin:       [15, 15, 15, 15],
            filename:     'Letter_' + appNo + '_' + nic + '.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, letterRendering: true },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).outputPdf('blob').then(function(blob) {
            generatedPdfBlob = blob;
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-eye"></i> Regenerate Preview';
            document.getElementById('saveBtn').style.display = 'inline-flex';
            showSuccess('Letter preview generated! Click "Save PDF to Database" to store it permanently.');
        }).catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-eye"></i> Generate Official Letter Preview';
            console.error('PDF generation failed:', err);
            alert('PDF generation failed: ' + err.message);
        });
    }

    // ============================================================
    // Save PDF to Database
    // ============================================================
    function saveLetterToDatabase() {
        if (!generatedPdfBlob) {
            alert('Please generate the letter first before saving.');
            return;
        }

        const loading = document.getElementById('loadingOverlay');
        document.getElementById('loadingText').innerText = 'Saving to database...';
        loading.classList.add('show');

        const appNo = document.getElementById('appNo').value || '';
        const nic = document.getElementById('nic').value || '';
        const fullName = document.getElementById('fullName').value || '';
        const letterSubject = document.getElementById('letterSubject').value || '';
        const applicationId = document.getElementById('dbApplicationId').value || '0';

        const formData = new FormData();
        formData.append('application_id', applicationId);
        formData.append('nic', nic);
        formData.append('application_no', appNo);
        formData.append('applicant_name', fullName);
        formData.append('letter_type', 'quarter_allocation_request');
        formData.append('letter_subject', letterSubject);
        formData.append('letter_pdf',
            generatedPdfBlob,
            'Letter_' + appNo + '_' + nic + '.pdf'
        );

        fetch('<?php echo baseUrl('letters/save-pdf'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            loading.classList.remove('show');
            if (data.success) {
                savedLetterId = data.letter_id;
                showSuccess('✅ Letter saved to database successfully! File ID: ' + data.letter_id + '. <a href="' + data.file_url + '" target="_blank" style="color:#047857;font-weight:700;">Click here to open PDF</a>');
                document.getElementById('saveBtn').disabled = true;
                document.getElementById('saveBtn').innerHTML = '<i class="fa-solid fa-check"></i> Saved!';
                document.getElementById('sendBdfBtn').style.display = 'inline-flex';
            } else {
                alert('Save failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            loading.classList.remove('show');
            alert('Network error: ' + err.message);
        });
    }

    // ============================================================
    // Send Letter to BDF
    // ============================================================
    function confirmSendToBdf() {
        if (!savedLetterId) {
            alert('Please save the letter to the database first.');
            return;
        }
        document.getElementById('sendBdfModal').style.display = 'flex';
    }

    function closeSendBdfModal() {
        document.getElementById('sendBdfModal').style.display = 'none';
    }

    function executeSendToBdf() {
        closeSendBdfModal();

        const loading = document.getElementById('loadingOverlay');
        document.getElementById('loadingText').innerText = 'Forwarding to BDF dashboard...';
        loading.classList.add('show');

        const formData = new FormData();
        formData.append('letter_id', savedLetterId);

        fetch('<?php echo baseUrl('letters/send-to-bdf'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            loading.classList.remove('show');
            if (data.success) {
                const btn = document.getElementById('sendBdfBtn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Sent to BDF!';
                showSuccess('📨 ' + (data.already_sent ? data.message : 'Letter forwarded to BDF dashboard successfully!'));
            } else {
                alert('Failed to send to BDF: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            loading.classList.remove('show');
            alert('Network error: ' + err.message);
        });
    }

    // ============================================================
    // Show success message
    // ============================================================
    function showSuccess(msg) {
        const alert = document.getElementById('successAlert');
        document.getElementById('successMessage').innerHTML = msg;
        alert.classList.add('show');
        alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => alert.classList.remove('show'), 8000);
    }

    // ============================================================
    // Print / Save as PDF (browser native)
    // ============================================================
    function triggerPrint() {
        updatePreviewContent();
        document.getElementById('letterPreviewBox').style.display = 'block';
        window.print();
    }

    // ============================================================
    // Auto-generate preview on page load if data was pre-filled
    // ============================================================
    window.addEventListener('DOMContentLoaded', function() {
        const appNo = document.getElementById('appNo').value.trim();
        if (appNo) {
            updatePreviewContent();
            document.getElementById('letterPreviewBox').style.display = 'block';
            document.getElementById('letterActionsBar').style.display = 'flex';

            setTimeout(function() {
                generatePdfBlob();
            }, 500);
        }
    });
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>