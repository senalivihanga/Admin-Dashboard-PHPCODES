<?php
/**
 * Final Approval Approved Application List - Sri Lanka Railways (SC view)
 *
 * Shows ONLY applications whose FINAL approval has been granted.
 * Each row has three action buttons:
 *   1. Generate Letter  -> opens editable letter generator
 *   2. Send to BDF      -> one-click auto-generate + save + forward
 *   3. View Letters     -> shows all previously generated letters
 */
require_once __DIR__ . '/../../../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Final Approval Approved List - Sri Lanka Railways";
$userData  = [
    'name'     => 'Subject Clerk',
    'role'     => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$searchQuery = trim($_GET['search'] ?? '');
$displayData = [];

$conn = getDBConnection();

// Expanded query: includes every field needed for the auto-generated letter
// plus letter counts (total and BDF-sent)
$sql = "SELECT a.id,
               a.application_no,
               a.nic,
               a.computer_no,
               a.final_status,
               a.final_reason,
               a.quarter_type,
               a.applied_quarter_number,
               a.applied_quarter_location,
               a.application_date,
               a.waiting_list_no,
               u.name,
               COALESCE(u.mobile, '')                                AS mobile,
               COALESCE(u.applicant_designation, '')                 AS applicant_designation,
               COALESCE(u.applicant_current_work_station, '')        AS workplace,
               aq.quarter_no            AS allocated_quarter_no,
               aq.quarter_name          AS allocated_quarter_name,
               aq.quarter_location      AS allocated_quarter_location,
               aq.allocated_date,
               (SELECT COUNT(*) FROM generated_letters gl
                       WHERE gl.application_id = a.id)              AS letter_count,
               (SELECT COUNT(*) FROM generated_letters gl
                       WHERE gl.application_id = a.id
                         AND gl.sent_to_bdf = 1)                    AS bdf_letter_count,
               (SELECT COUNT(*) FROM bdf_generated_letters bgl
                       WHERE bgl.application_id = a.id)             AS bdf_response_count
          FROM applications AS a
          INNER JOIN users AS u ON u.nic = a.nic
          LEFT JOIN allocated_quarters AS aq
                 ON aq.nic = a.nic AND aq.source = 'normal'
         WHERE a.final_status = 'approved'
           AND a.application_status = 'approved'";

if ($searchQuery !== '') {
    $sql .= " AND (a.application_no LIKE ?
               OR a.nic LIKE ?
               OR a.computer_no LIKE ?
               OR u.name LIKE ?)";
    $searchTerm = '%' . $searchQuery . '%';
    $stmt = $conn->prepare($sql . ' ORDER BY a.application_date DESC, a.id DESC');
    $stmt->bind_param('ssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare($sql . ' ORDER BY a.application_date DESC, a.id DESC');
}

$stmt->execute();
$displayData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
:root {
    --primary-color: #7A1C1C;
    --primary-dark:  #571a1a;
    --danger-color:  #ef4444;
    --bg-color:      #f8fafc;
    --card-bg:       #ffffff;
    --text-main:     #1e293b;
    --border-color:  #e2e8f0;
}
html { overflow-y: auto; overflow-x: hidden; }
body { margin: 0; padding: 0; background-color: var(--bg-color); overflow-x: hidden; }

.approval-container { width: 100%; max-width: 100%; margin: 0; padding: 20px 30px; box-sizing: border-box; }
.back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--primary-color); text-decoration: none; font-weight: 600; margin-bottom: 20px; font-size: 0.95rem; }
.back-link:hover { text-decoration: underline; }
.approval-header { text-align: center; margin-bottom: 25px; }
.approval-container h2 {
    font-size: 1.8rem; color: var(--primary-color); margin-bottom: 10px;
    text-transform: uppercase; letter-spacing: 1px;
    text-decoration: underline; text-underline-offset: 6px; font-weight: 700;
}

.user-profile-wrapper { position: relative; display: inline-block; margin-bottom: 20px; }
.user-profile-badge { display: flex; align-items: center; gap: 10px; background: var(--primary-color); color: #fff; padding: 6px 14px; border-radius: 30px; cursor: pointer; user-select: none; }
.profile-dropdown { display: none; position: absolute; left: 0; top: 105%; background: #fff; min-width: 220px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; z-index: 100; border: 1px solid var(--border-color); }
.profile-dropdown.show { display: block; }
.profile-dropdown a { color: var(--text-main); padding: 10px 15px; text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 0.85rem; }
.profile-dropdown a:hover { background: #f1f5f9; color: var(--primary-color); }
.profile-dropdown a.logout-btn { color: var(--danger-color); }

.search-card { background: var(--card-bg); padding: 18px 22px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; border: 1px solid var(--border-color); }
.search-wrapper { display: flex; gap: 10px; align-items: center; flex: 1; min-width: 280px; }
.search-input-container { position: relative; flex: 1; max-width: 350px; }
.search-input { width: 100%; padding: 10px 36px 10px 14px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.9rem; outline: none; box-sizing: border-box; }
.search-input:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1); }
.search-clear-icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; text-decoration: none; }
.search-btn, .print-btn { border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-size: 0.9rem; color: white; }
.search-btn { background-color: var(--primary-color); }
.search-btn:hover { background-color: var(--primary-dark); }
.print-btn { background-color: #0284c7; }
.print-btn:hover { background-color: #0369a1; }

.table-card { background: var(--card-bg); padding: 20px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.05); border-top: 5px solid var(--primary-color); overflow-x: auto; }
.table-responsive { width: 100%; overflow-x: auto; }
.approval-table { width: 100%; border-collapse: collapse; text-align: center; font-size: 0.9rem; min-width: 1600px; }
.approval-table th, .approval-table td { padding: 12px; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
.approval-table th { background-color: #f8fafc; color: var(--text-main); font-weight: 700; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.5px; border-bottom: 2px solid var(--border-color); }
.approval-table tr:hover { background-color: #f1f5f9; }

.status-badge { background-color: #d1fae5; color: #065f46; padding: 5px 12px; border-radius: 20px; font-weight: 600; font-size: 0.72rem; }
.quarter-badge { background-color: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
.quarter-empty { color: #94a3b8; font-style: italic; font-size: 0.8rem; }

/* Action buttons */
.action-cell { display: flex; gap: 6px; justify-content: center; align-items: center; flex-wrap: nowrap; }

.btn-gen-letter {
    background: linear-gradient(135deg, #059669, #047857);
    color: white; border: none; padding: 7px 12px; border-radius: 6px;
    font-weight: 600; font-size: 0.76rem; cursor: pointer;
    display: inline-flex; align-items: center; gap: 5px; text-decoration: none;
    transition: all 0.2s;
}
.btn-gen-letter:hover { background: linear-gradient(135deg, #047857, #065f46); transform: translateY(-1px); }

.btn-send-bdf {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: white; border: none; padding: 7px 12px; border-radius: 6px;
    font-weight: 600; font-size: 0.76rem; cursor: pointer;
    display: inline-flex; align-items: center; gap: 5px; text-decoration: none;
    transition: all 0.2s;
}
.btn-send-bdf:hover { background: linear-gradient(135deg, #1d4ed8, #1e40af); transform: translateY(-1px); }
.btn-send-bdf:disabled {
    background: #9ca3af !important; cursor: not-allowed; transform: none !important; opacity: 0.7;
}

.btn-sent-done {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: white; border: none; padding: 7px 12px; border-radius: 6px;
    font-weight: 600; font-size: 0.76rem; cursor: default;
    display: inline-flex; align-items: center; gap: 5px;
}

/* View Letters button (purple) */
.btn-view-letters {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    color: white; border: none; padding: 7px 12px; border-radius: 6px;
    font-weight: 600; font-size: 0.76rem; cursor: pointer;
    display: inline-flex; align-items: center; gap: 5px; text-decoration: none;
    transition: all 0.2s; position: relative;
}
.btn-view-letters:hover { background: linear-gradient(135deg, #6d28d9, #5b21b6); transform: translateY(-1px); }
.letter-count-badge {
    position: absolute; top: -6px; right: -6px;
    background: #fbbf24; color: #78350f;
    min-width: 18px; height: 18px; border-radius: 9px;
    font-size: 0.65rem; font-weight: 700;
    display: inline-flex; align-items: center; justify-content: center;
    padding: 0 4px; border: 2px solid white;
}

/* Toast + Loading */
.loading-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; }
.loading-overlay.show { display: flex; }
.loading-box { background: #fff; padding: 30px 40px; border-radius: 12px; text-align: center; }
.loading-box i { font-size: 2.5rem; color: var(--primary-color); margin-bottom: 10px; }
.loading-box p { margin: 0; font-weight: 600; color: var(--text-main); }

.system-toast {
    position: fixed; right: 24px; top: 24px;
    padding: 14px 20px; border-radius: 10px;
    display: none; z-index: 1000; font-weight: 700;
    align-items: center; gap: 10px; font-size: 0.9rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    max-width: 400px;
}
.system-toast.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; border-left: 6px solid #22c55e; }
.system-toast.error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; border-left: 6px solid #ef4444; }

/* Confirmation modal */
.custom-modal { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; padding: 15px; }
.custom-modal-content { background: #fff; padding: 30px; border-radius: 12px; width: 100%; max-width: 380px; text-align: center; box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
.custom-modal-content i { font-size: 2.8rem; color: #2563eb; margin-bottom: 15px; }
.custom-modal-content h3 { color: var(--text-main); margin-bottom: 10px; font-size: 1.3rem; }
.custom-modal-content p { color: #64748b; font-size: 0.95rem; margin-bottom: 20px; }
.modal-action-btns { display: flex; justify-content: center; gap: 12px; }
.btn-modal-confirm, .btn-modal-cancel { padding: 8px 15px; border: none; border-radius: 5px; font-weight: 600; cursor: pointer; color: white; }
.btn-modal-confirm { background-color: #2563eb; }
.btn-modal-cancel  { background-color: #64748b; }

/* Hidden letter template */
.letter-preview-box {
    position: fixed; top: -99999px; left: -99999px;
    background: #fff; padding: 45px 50px;
    font-family: 'Times New Roman', Times, serif;
    color: #000; line-height: 1.6; font-size: 12pt;
    width: 750px;
}

@media (max-width: 768px) {
    .approval-container { padding: 10px 15px; }
    .approval-container h2 { font-size: 1.4rem; }
    .search-card { flex-direction: column; align-items: stretch; }
    .search-wrapper { width: 100%; min-width: 0; gap: 8px; }
    .search-input-container { max-width: 100%; min-width: 0; }
    .search-input { padding: 12px 36px 12px 14px; font-size: 1rem; }
    .search-btn {
        width: auto;
        flex: 0 0 auto;
        padding: 8px 12px;
        font-size: 0.8rem;
        white-space: nowrap;
    }
    .print-btn { width: 100%; justify-content: center; }
}

@media print {
    @page { size: landscape; margin: 5mm; }
    
    html, body { 
        background-color: #fff !important; 
        overflow: visible !important; 
        width: 100% !important; 
    }
    
    .back-link, .search-card, .user-profile-wrapper,
    .btn-gen-letter, .btn-send-bdf, .btn-sent-done, .btn-view-letters,
    .loading-overlay, .system-toast { display: none !important; }
    
    .table-card { 
        box-shadow: none !important; 
        border: none !important; 
        padding: 0 !important; 
        overflow: visible !important; /* Prevents clipping of overflowing content */
        width: 100% !important;
    }
    
    .table-responsive {
        overflow: visible !important; /* Removes the horizontal scroll clipping */
        width: 100% !important;
    }

    .approval-table { 
        width: 100% !important; 
        min-width: 0 !important; /* CRITICAL: Overrides the 1600px min-width so it can shrink to fit the paper */
        font-size: 8.5pt !important; 
        table-layout: auto !important;
    }
    
    .approval-table th, .approval-table td { 
        padding: 6px 8px !important; 
        white-space: normal !important; /* CRITICAL: Allows text to wrap to the next line instead of forcing one long line */
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
    }
}
</style>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-box">
        <i class="fa-solid fa-spinner fa-spin"></i>
        <p id="loadingText">Processing...</p>
    </div>
</div>

<div id="systemToast" class="system-toast">
    <i id="toastIcon" class="fa-solid fa-circle-check" style="font-size: 1.1rem;"></i>
    <span id="toastMessage"></span>
</div>

<!-- Send to BDF confirmation modal -->
<div class="custom-modal" id="sendBdfModal">
    <div class="custom-modal-content">
        <i class="fa-solid fa-paper-plane"></i>
        <h3>Send Letter to BDF?</h3>
        <p id="sendBdfModalText">
            This will generate the official letter, save it to the database, and forward it to the BDF dashboard.
        </p>
        <div class="modal-action-btns">
            <button type="button" class="btn-modal-cancel" onclick="closeSendBdfModal()">Cancel</button>
            <button type="button" class="btn-modal-confirm" onclick="executeSendToBdf()">Yes, Send to BDF</button>
        </div>
    </div>
</div>

<!-- Hidden letter template (used only for PDF generation) -->
<div class="letter-preview-box" id="letterPreviewBox">
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
        Building Department Foreman (BDF),<br>
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

<main class="approval-container">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
        <a href="SC_dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <?php else: ?>
        <div class="user-profile-wrapper">
            <div class="user-profile-badge" onclick="toggleDropdown()">
                <div style="width: 35px; height: 35px; background: #fff; color: #7A1C1C; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem;">
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

    <div class="approval-header">
        <h2>Final Approval Approved List</h2>
    </div>

    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" class="search-input"
                    placeholder="Search by Application No, NIC or Name..."
                    value="<?php echo htmlspecialchars($searchQuery); ?>">
                <?php if (!empty($searchQuery)): ?>
                    <a href="?" class="search-clear-icon" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
        <button type="button" class="print-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
    </form>

    <div class="table-card">
        <div class="table-responsive">
            <table class="approval-table" id="approvedTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Application Number</th>
                        <th>NIC</th>
                        <th>Computer No.</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Workplace</th>
                        <th>Quarter Type</th>
                        <th>Allocated Quarter</th>
                        <th>Application Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($displayData)): ?>
                        <tr>
                            <td colspan="12" style="text-align: center; color: #64748b; padding: 30px;">
                                No finally-approved applications found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($displayData as $row): ?>
                            <?php
                                // Prefer allocated quarter; fall back to applied quarter
                                $quarterNo   = $row['allocated_quarter_no']   ?? ($row['applied_quarter_number']   ?? '');
                                $quarterName = $row['allocated_quarter_name']
                                            ?: ($row['allocated_quarter_location']
                                            ?: ($row['applied_quarter_location'] ?? ''));
                                $alreadySent = ((int)$row['bdf_letter_count']) > 0;
                                $totalLetters = (int)$row['letter_count'];
                                $bdfResponseCount = (int)($row['bdf_response_count'] ?? 0);
                            ?>
                            <tr id="row-<?php echo (int)$row['id']; ?>">
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['application_no'] ?? (string)$row['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['nic']); ?></td>
                                <td><?php echo htmlspecialchars($row['computer_no']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['applicant_designation'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['workplace'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['quarter_type'] ?: '-'); ?></td>
                                <td>
                                    <?php if (!empty($quarterNo)): ?>
                                        <span class="quarter-badge">
                                            <?php echo htmlspecialchars($quarterNo); ?>
                                            <br><small style="font-weight:400;"><?php echo htmlspecialchars($quarterName); ?></small>
                                        </span>
                                    <?php else: ?>
                                        <span class="quarter-empty">Not yet allocated</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['application_date'] ?: '-'); ?></td>
                                <td><span class="status-badge"><?php echo htmlspecialchars(ucfirst($row['final_status'])); ?></span></td>
                                <td>
                                    <div class="action-cell">
                                        <!-- 1. Generate Letter (opens editable page) -->
                                        <a href="generate_letter_SC.php?app_id=<?php echo (int)$row['id']; ?>"
                                           class="btn-gen-letter"
                                           title="Open editable letter generator">
                                            <i class="fa-solid fa-file-pen"></i> Generate Letter
                                        </a>

                                        <!-- 2. Send to BDF (one-click) -->
                                        <?php if ($alreadySent): ?>
                                            <span class="btn-sent-done" title="Already forwarded to BDF">
                                                <i class="fa-solid fa-circle-check"></i> Sent to BDF
                                            </span>
                                        <?php else: ?>
                                            <button type="button"
                                                    class="btn-send-bdf"
                                                    data-row-id="<?php echo (int)$row['id']; ?>"
                                                    data-app-no="<?php echo htmlspecialchars($row['application_no'] ?? (string)$row['id'], ENT_QUOTES); ?>"
                                                    data-nic="<?php echo htmlspecialchars($row['nic'], ENT_QUOTES); ?>"
                                                    data-name="<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>"
                                                    data-designation="<?php echo htmlspecialchars($row['applicant_designation'] ?? '', ENT_QUOTES); ?>"
                                                    data-workplace="<?php echo htmlspecialchars($row['workplace'] ?? '', ENT_QUOTES); ?>"
                                                    data-quarter-no="<?php echo htmlspecialchars($quarterNo, ENT_QUOTES); ?>"
                                                    data-quarter-name="<?php echo htmlspecialchars($quarterName, ENT_QUOTES); ?>"
                                                    data-quarter-type="<?php echo htmlspecialchars($row['quarter_type'] ?? '', ENT_QUOTES); ?>"
                                                    onclick="openSendBdfModal(this)"
                                                    title="Auto-generate letter & forward to BDF">
                                                <i class="fa-solid fa-paper-plane"></i> Send to BDF
                                            </button>
                                        <?php endif; ?>

                                        <!-- 3. View Letters (shows previously generated letters) -->
                                                                                <?php if ($totalLetters > 0): ?>
                                            <a href="view_generated_letters.php?nic=<?php echo urlencode($row['nic']); ?>"
                                               class="btn-view-letters"
                                               title="View SC letters for this applicant">
                                                <i class="fa-solid fa-folder-open"></i> SC Letters
                                                <span class="letter-count-badge"><?php echo $totalLetters; ?></span>
                                            </a>
                                        <?php endif; ?>
                                        <?php
                                        $bdfResponseCount = (int)($row['bdf_response_count'] ?? 0);
                                        if ($bdfResponseCount > 0): ?>
                                            <a href="view_bdf_generated_letters.php?nic=<?php echo urlencode($row['nic']); ?>"
                                               class="btn-view-letters"
                                               style="background:linear-gradient(135deg,#f59e0b,#d97706);"
                                               title="View BDF response letters for this applicant">
                                                <i class="fa-solid fa-file-signature"></i> BDF Letters
                                                <span class="letter-count-badge"><?php echo $bdfResponseCount; ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

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

<script>
/* ============================================================
 * Profile dropdown & logout
 * ============================================================ */
function toggleDropdown() {
    document.getElementById('profileDropdown').classList.toggle('show');
}
window.addEventListener('click', function(e) {
    if (!e.target.closest('.user-profile-wrapper')) {
        document.getElementById('profileDropdown').classList.remove('show');
    }
});
function openLogoutModal(e) {
    e.preventDefault();
    document.getElementById('logoutModal').style.display = 'flex';
}
function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
}
function executeLogout() { window.location.href = "login.php"; }

/* ============================================================
 * Toast + loading helpers
 * ============================================================ */
function showToast(message, type) {
    const toast = document.getElementById('systemToast');
    const icon  = document.getElementById('toastIcon');
    const msg   = document.getElementById('toastMessage');
    toast.className = 'system-toast ' + (type || 'success');
    icon.className = 'fa-solid ' + (type === 'error' ? 'fa-circle-xmark' : 'fa-circle-check');
    msg.innerHTML = message;
    toast.style.display = 'flex';
    clearTimeout(window._toastTimer);
    window._toastTimer = setTimeout(() => { toast.style.display = 'none'; }, 5000);
}

function showLoading(text) {
    document.getElementById('loadingText').textContent = text || 'Processing...';
    document.getElementById('loadingOverlay').classList.add('show');
}
function hideLoading() {
    document.getElementById('loadingOverlay').classList.remove('show');
}

/* ============================================================
 * Build the letter HTML into the hidden template
 * ============================================================ */
function populateLetterTemplate(row) {
    const appNo       = row.dataset.appNo       || '-';
    const nic         = row.dataset.nic         || '-';
    const name        = row.dataset.name        || '-';
    const designation = row.dataset.designation || '-';
    const workplace   = row.dataset.workplace   || '-';
    const quarterNo   = row.dataset.quarterNo   || '-';
    const quarterName = row.dataset.quarterName || '-';
    const quarterType = row.dataset.quarterType || '-';

    document.getElementById('pDate').innerText = new Date().toISOString().split('T')[0];

    document.getElementById('pSubjectTitle').innerText =
        'Request for Quarter Allocation for ' + name +
        (designation ? ' (' + designation + ')' : '');

    document.getElementById('pIntroPara').innerHTML =
        'This letter is submitted regarding the quarter allocation request made by <strong>' + name + '</strong>' +
        (designation ? ' (Designation: <strong>' + designation + '</strong>' : '') +
        ', NIC: <strong>' + nic + '</strong>' +
        (workplace   ? ', Workplace: <strong>' + workplace + '</strong>' : '') +
        ') under Application Reference No: <strong>' + appNo + '</strong>' +
        ' for Quarter No: <strong>' + quarterNo + '</strong>' +
        (quarterName ? ' (' + quarterName + ')' : '') + '.';

    document.getElementById('pCustomDescPara').innerHTML =
        'This is to formally request the allocation of government quarters for the above-mentioned officer of Sri Lanka Railways. ' +
        'The applicant has duly submitted all required documentation, service records, and clearances through the Quarters Management Branch. ' +
        'Given the urgent service requirements and official duties, the approval of the Board of Directors / Housing Board (BDF) is hereby requested to proceed with the direct quarter allocation.';
}

/* ============================================================
 * Generate PDF blob from the hidden letter template
 * ============================================================ */
function generateLetterPdf(appNo, nic) {
    const element = document.getElementById('letterPreviewBox');
    const opt = {
        margin:      [15, 15, 15, 15],
        filename:    'Letter_' + (appNo || 'unknown').replace(/[^\w-]/g, '_') + '_' + (nic || '') + '.pdf',
        image:       { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, letterRendering: true },
        jsPDF:       { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    return html2pdf().set(opt).from(element).outputPdf('blob');
}

/* ============================================================
 * Step 1: Open the confirmation modal
 * ============================================================ */
let pendingRow = null;

function openSendBdfModal(button) {
    pendingRow = button;
    document.getElementById('sendBdfModalText').innerHTML =
        'For <strong>' + button.dataset.name + '</strong> ' +
        '(' + (button.dataset.appNo || '') + '). ' +
        'This will auto-generate the official letter, save it, and forward it to the BDF dashboard.';
    document.getElementById('sendBdfModal').style.display = 'flex';
}

function closeSendBdfModal() {
    pendingRow = null;
    document.getElementById('sendBdfModal').style.display = 'none';
}

/* ============================================================
 * Step 2: Confirm → generate PDF → save to DB → mark as sent to BDF
 * ============================================================ */
async function executeSendToBdf() {
    if (!pendingRow) return;
    const btn = pendingRow;
    closeSendBdfModal();

    const appId       = btn.dataset.rowId;
    const appNo       = btn.dataset.appNo;
    const nic         = btn.dataset.nic;
    const name        = btn.dataset.name;
    const designation = btn.dataset.designation || '';
    const workplace   = btn.dataset.workplace   || '';
    const quarterNo   = btn.dataset.quarterNo   || '';
    const quarterName = btn.dataset.quarterName || '';
    const quarterType = btn.dataset.quarterType || '';

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

    try {
        /* ---------- A) Build letter & generate PDF ---------- */
        showLoading('Generating letter PDF...');
        populateLetterTemplate(btn);

        const pdfBlob = await generateLetterPdf(appNo, nic);

        /* ---------- B) Save the PDF to the database ---------- */
        document.getElementById('loadingText').textContent = 'Saving letter to database...';

        const saveForm = new FormData();
        saveForm.append('application_id', appId);
        saveForm.append('nic',            nic);
        saveForm.append('application_no', appNo);
        saveForm.append('applicant_name', name);
        saveForm.append('letter_type',    'quarter_allocation_request');
        saveForm.append('letter_subject', 'Request for Quarter Allocation for ' + name + (designation ? ' (' + designation + ')' : ''));
        saveForm.append('letter_pdf',
            pdfBlob,
            'Letter_' + (appNo || 'unknown').replace(/[^\w-]/g, '_') + '_' + nic + '.pdf'
        );

        const saveRes = await fetch('<?php echo baseUrl('letters/save-pdf'); ?>', {
            method: 'POST',
            body:   saveForm
        });
        const saveData = await saveRes.json();
        if (!saveData.success) {
            throw new Error(saveData.error || 'Failed to save letter to database');
        }
        const letterId = saveData.letter_id;

        /* ---------- C) Mark the letter as sent to BDF ---------- */
        document.getElementById('loadingText').textContent = 'Forwarding letter to BDF dashboard...';

        const sendForm = new FormData();
        sendForm.append('letter_id', letterId);

        const sendRes = await fetch('<?php echo baseUrl('letters/send-to-bdf'); ?>', {
            method: 'POST',
            body:   sendForm
        });
        const sendData = await sendRes.json();
        if (!sendData.success) {
            throw new Error(sendData.error || 'Failed to forward letter to BDF');
        }

        hideLoading();

        /* ---------- D) Update the row UI ---------- */
        const actionCell = btn.parentElement;

        // Replace "Send to BDF" button with "Sent to BDF" badge
        btn.remove();
        const doneSpan = document.createElement('span');
        doneSpan.className = 'btn-sent-done';
        doneSpan.title     = 'Already forwarded to BDF';
        doneSpan.innerHTML = '<i class="fa-solid fa-circle-check"></i> Sent to BDF';
        actionCell.insertBefore(doneSpan, actionCell.querySelector('.btn-view-letters') || null);

        // Add or update "View Letters" button
        let viewBtn = actionCell.querySelector('.btn-view-letters');
        if (!viewBtn) {
            viewBtn = document.createElement('a');
            viewBtn.href = 'view_generated_letters.php?nic=' + encodeURIComponent(nic);
            viewBtn.className = 'btn-view-letters';
            viewBtn.title = 'View all letters generated for this applicant';
            viewBtn.innerHTML = '<i class="fa-solid fa-folder-open"></i> View Letters' +
                '<span class="letter-count-badge">1</span>';
            actionCell.appendChild(viewBtn);
        } else {
            // Update count badge
            const badge = viewBtn.querySelector('.letter-count-badge');
            if (badge) {
                const currentCount = parseInt(badge.textContent) || 0;
                badge.textContent = currentCount + 1;
            }
        }

        showToast('✅ Letter generated, saved and forwarded to BDF successfully for <strong>' + name + '</strong>.', 'success');

    } catch (err) {
        hideLoading();
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send to BDF';
        showToast('❌ Failed: ' + err.message, 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>