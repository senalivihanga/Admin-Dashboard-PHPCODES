<?php
/**
 * Application Approval List - Final Approver - Sri Lanka Railways
 * ----------------------------------------------------------------
 * The Final Approver's "Application Approval" page shows ONLY applications
 * that have been approved by the Subject Clerk and are awaiting the Final
 * Approver's decision, i.e.:
 *   - boss_status       = 'approved'   (Immediate Boss approved)
 *   - file_status       = 'approved'   (Personal File approved)
 *   - clerk_status      = 'approved'   (Subject Clerk approved)
 *   - final_status      = 'pending'    (Final Approver has NOT decided yet)
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Application Approval List - Sri Lanka Railways";

$userData = [
    'name'     => $_SESSION['user_name'] ?? 'Final Approver',
    'role'     => 'Sri Lanka Railways - AOT',
    'initials' => 'FA'
];

$user = [
    'name' => $userData['name']
];

$alert_msg  = '';
$alert_type = '';
$conn       = getDBConnection();

// -------------------------------------------------------------
// Handle Final Approver decision (approve / reject)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $reason = trim((string)($_POST['rejectionReason'] ?? ''));

    if ($id && ($action === 'approve' || ($action === 'reject' && $reason !== ''))) {
        $finalStatus       = $action === 'approve' ? 'approved' : 'rejected';
        $finalReason       = $action === 'reject'  ? $reason    : null;
        // Approve → application fully approved. Reject → rejected.
        $applicationStatus = $action === 'approve' ? 'approved' : 'rejected';

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare(
                "UPDATE applications
                    SET final_status = ?, final_reason = ?, application_status = ?
                  WHERE id = ?
                    AND boss_status   = 'approved'
                    AND file_status   = 'approved'
                    AND clerk_status  = 'approved'
                    AND final_status  = 'pending'"
            );
            $stmt->bind_param('sssi', $finalStatus, $finalReason, $applicationStatus, $id);
            $stmt->execute();
            $wasFinalized = $stmt->affected_rows === 1;
            $stmt->close();

            if ($wasFinalized && $action === 'approve') {
                $applicationStmt = $conn->prepare(
                    "SELECT nic, quarter_type, waiting_list_no, COALESCE(marks, 0) AS marks
                       FROM applications
                      WHERE id = ?"
                );
                $applicationStmt->bind_param('i', $id);
                $applicationStmt->execute();
                $application = $applicationStmt->get_result()->fetch_assoc();
                $applicationStmt->close();

                if (!$application) {
                    throw new RuntimeException('The approved application could not be loaded for waiting-list placement.');
                }

                $waitingListNo = (int)($application['waiting_list_no'] ?? 0);
                $existingWaitingStmt = null;
                if ($waitingListNo > 0) {
                    $existingWaitingStmt = $conn->prepare(
                        "SELECT id FROM waiting_list
                          WHERE nic = ? AND quarter_type = ? AND position = ?
                          LIMIT 1"
                    );
                    $existingWaitingStmt->bind_param(
                        'ssi',
                        $application['nic'],
                        $application['quarter_type'],
                        $waitingListNo
                    );
                    $existingWaitingStmt->execute();
                    $alreadyListed = $existingWaitingStmt->get_result()->num_rows > 0;
                    $existingWaitingStmt->close();
                } else {
                    $alreadyListed = false;
                }

                if (!$alreadyListed) {
                    $positionStmt = $conn->prepare(
                        "SELECT COALESCE(MAX(position), 0) + 1 AS next_position
                           FROM waiting_list
                          WHERE quarter_type = ?"
                    );
                    $positionStmt->bind_param('s', $application['quarter_type']);
                    $positionStmt->execute();
                    $waitingListNo = (int)$positionStmt->get_result()->fetch_assoc()['next_position'];
                    $positionStmt->close();

                    $waitingStmt = $conn->prepare(
                        "INSERT INTO waiting_list
                            (nic, position, quarter_type, applied_date, employee_marks)
                         VALUES (?, ?, ?, CURDATE(), ?)"
                    );
                    $marks = (int)$application['marks'];
                    $waitingStmt->bind_param(
                        'sisi',
                        $application['nic'],
                        $waitingListNo,
                        $application['quarter_type'],
                        $marks
                    );
                    $waitingStmt->execute();
                    $waitingStmt->close();

                    $applicationListStmt = $conn->prepare(
                        "UPDATE applications SET waiting_list_no = ? WHERE id = ?"
                    );
                    $applicationListStmt->bind_param('ii', $waitingListNo, $id);
                    $applicationListStmt->execute();
                    $applicationListStmt->close();
                }
            }

            if ($wasFinalized) {
                // Notify the applicant
                $notifTitle = $action === 'approve' ? 'Application Approved' : 'Application Rejected';
                $notifMsg   = $action === 'approve'
                    ? 'Your quarter application has been fully approved by the Final Approver.'
                    : 'Your application was rejected by the Final Approver. Reason: ' . $reason;

                $notif = $conn->prepare(
                    "INSERT INTO notifications (nic, title, message, is_read)
                     SELECT nic, ?, ?, 0
                     FROM applications WHERE id = ?"
                );
                $notif->bind_param('ssi', $notifTitle, $notifMsg, $id);
                $notif->execute();
                $notif->close();
            }

            $conn->commit();
        } catch (Throwable $error) {
            $conn->rollback();
            throw $error;
        }

        if ($wasFinalized) {
            $alert_msg  = $action === 'approve'
                ? 'Application approved successfully and finalized.'
                : 'Application rejected successfully.';
            $alert_type = $action === 'approve' ? 'success' : 'error';
        } else {
            $alert_msg  = 'This application is no longer awaiting your decision.';
            $alert_type = 'error';
        }
    } elseif ($action === 'reject') {
        $alert_msg  = 'A rejection reason is required.';
        $alert_type = 'error';
    }
}

// -------------------------------------------------------------
// Fetch the Final Approver's pending queue
// -------------------------------------------------------------
$searchQuery = trim($_GET['search'] ?? '');

$sql = "SELECT a.id,
               a.application_no   AS appNo,
               a.nic,
               a.computer_no      AS compNo,
               u.name,
               COALESCE(u.applicant_designation, '') AS desig
          FROM applications AS a
    INNER JOIN users        AS u ON u.nic = a.nic
         WHERE a.boss_status   = 'approved'
           AND a.file_status   = 'approved'
           AND a.clerk_status  = 'approved'
           AND a.final_status  = 'pending'
           AND a.application_status NOT IN ('rejected','returned_to_boss','returned_to_file')";

if ($searchQuery !== '') {
    $sql .= " AND (a.application_no LIKE ?
                OR a.nic LIKE ?
                OR a.computer_no LIKE ?
                OR u.name LIKE ?)";
    $stmt = $conn->prepare($sql . ' ORDER BY a.application_date ASC, a.id ASC');
    $searchTerm = '%' . $searchQuery . '%';
    $stmt->bind_param('ssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare($sql . ' ORDER BY a.application_date ASC, a.id ASC');
}

$stmt->execute();
$displayData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
    :root {
        --primary-color: #7A1C1C;
        --primary-dark: #571a1a;
        --danger-color: #ef4444;
        --danger-dark: #dc2626;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --text-main: #1e293b;
        --border-color: #e2e8f0;
    }

    html {
        overflow-y: auto;
        overflow-x: hidden;
    }

    body {
        margin: 0;
        padding: 0;
        background-color: var(--bg-color);
        overflow-x: hidden;
    }

    .approval-container {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 20px 30px;
        box-sizing: border-box;
    }

    .back-link { 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        color: var(--primary-color); 
        text-decoration: none; 
        font-weight: 600; 
        margin-bottom: 20px; 
        font-size: 0.95rem;
    }

    .back-link:hover {
        text-decoration: underline;
    }
    
    .approval-header {
        text-align: center;
        margin-bottom: 25px;
    }

    .approval-container h2 { 
        font-size: 1.8rem;
        color: var(--primary-color);
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-decoration: underline;
        text-underline-offset: 6px;
        font-weight: 700;
    }

    .search-card { 
        background: var(--card-bg); 
        padding: 18px 22px; 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.04); 
        margin-bottom: 20px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        flex-wrap: wrap; 
        gap: 15px; 
        border: 1px solid var(--border-color);
        width: 100%;
        box-sizing: border-box;
    }

    .search-wrapper { 
        display: flex; 
        gap: 10px; 
        align-items: center; 
        flex: 1;
        min-width: 280px;
    }
    
    .search-input-container {
        position: relative;
        flex: 1;
        max-width: 350px;
    }

    .search-input { 
        width: 100%;
        padding: 10px 36px 10px 14px; 
        border-radius: 8px; 
        border: 1px solid #cbd5e1; 
        font-size: 0.9rem; 
        outline: none; 
        box-sizing: border-box;
    }
    
    .search-input:focus { 
        border-color: var(--primary-color); 
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1); 
    }
    
    .search-clear-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        text-decoration: none;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }
    
    .search-clear-icon:hover {
        color: var(--text-main);
    }

    .search-btn, .print-btn { 
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
    }
    
    .search-btn { 
        background-color: var(--primary-color); 
        color: white; 
    }
    
    .search-btn:hover { 
        background-color: var(--primary-dark); 
    }
    
    .print-btn { 
        background-color: #0284c7; 
        color: white; 
    }
    
    .print-btn:hover { 
        background-color: #0369a1; 
    }

    .table-card { 
        background: var(--card-bg); 
        padding: 20px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.05); 
        border-top: 5px solid var(--primary-color); 
        overflow-x: auto; 
        width: 100%;
        box-sizing: border-box;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .approval-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: center; 
        font-size: 0.9rem;
        white-space: nowrap;
    }
    
    .approval-table th, .approval-table td { 
        padding: 14px 16px; 
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle; 
    }
    
    .approval-table th { 
        background-color: #f8fafc; 
        color: var(--text-main); 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border-color);
    }
    
    .approval-table tr:hover {
        background-color: #f1f5f9;
    }
    
    .action-cell { 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        gap: 6px; 
    }
    
    .action-btn { 
        border: none; 
        width: 32px; 
        height: 32px; 
        border-radius: 6px; 
        font-weight: 600; 
        cursor: pointer; 
        font-size: 0.85rem; 
        text-decoration: none; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        transition: background-color 0.2s; 
        color: white; 
    }
    
    .btn-view { background-color: #8b5cf6; }
    .btn-view:hover { background-color: #7c3aed; }
    
    .btn-approve { background-color: #10b981; }
    .btn-approve:hover { background-color: #059669; }
    
    .btn-reject { background-color: #ef4444; }
    .btn-reject:hover { background-color: #dc2626; }

    .system-alert {
        background-color: #f0fdf4;
        color: #166534;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 600;
        border: 1px solid #bbf7d0;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: opacity 0.5s ease;
    }

    .system-alert.error {
        background-color: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
    }

    .custom-msg-box { 
        display: none; 
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(0,0,0,0.5); 
        justify-content: center; 
        align-items: center; 
        z-index: 1000; 
        padding: 15px; 
        box-sizing: border-box; 
    }
    .msg-content { 
        background: white; 
        padding: 25px; 
        border-radius: 10px; 
        width: 100%; 
        max-width: 400px; 
        box-shadow: 0 5px 15px rgba(0,0,0,0.3); 
    }
    .msg-content h3 { 
        color: var(--primary-color); 
        margin-bottom: 15px; 
        font-size: 1.1rem; 
    }
    .msg-content textarea { 
        width: 100%; 
        padding: 10px; 
        border: 1px solid #cbd5e1; 
        border-radius: 6px; 
        margin-bottom: 15px; 
        outline: none; 
        box-sizing: border-box; 
        font-family: inherit; 
    }
    .msg-btns { 
        display: flex; 
        justify-content: flex-end; 
        gap: 10px; 
    }
    .msg-btn { 
        padding: 8px 15px; 
        border: none; 
        border-radius: 5px; 
        font-weight: 600; 
        cursor: pointer; 
        color: white; 
    }
    .msg-btn-yes { background-color: var(--danger-color); }
    .msg-btn-no { background-color: #64748b; }

    @media (max-width: 768px) {
        .approval-container { padding: 10px 15px; }
        .approval-container h2 { font-size: 1.4rem; }
        .search-card { flex-direction: column; align-items: stretch; }
        .search-wrapper { width: 100%; }
        .search-input-container { max-width: 100%; }
        .search-btn, .print-btn { width: 100%; justify-content: center; }
    }

    @media print {
        @page {
            size: landscape;
            margin: 5mm;
        }

        html, body {
            background-color: #ffffff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .back-link, .search-card, .system-alert { 
            display: none !important; 
        }

        .table-card { 
            box-shadow: none !important; 
            border: none !important; 
            padding: 0 !important; 
        }

        .approval-table { 
            width: 100% !important; 
            font-size: 8.5pt !important; 
        }

        .approval-table th, .approval-table td { 
            padding: 8px 10px !important; 
        }

        th:nth-child(7), td:nth-child(7),
        th:nth-child(8), td:nth-child(8) { 
            display: none !important; 
        }
    }
</style>

<main class="approval-container">
    <a href="FA_dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>

    <div class="approval-header">
        <h2>Application Approval List</h2>
    </div>

    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" class="search-input" placeholder="Search by Application No, NIC or Name..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                <?php if (!empty($searchQuery)): ?>
                    <a href="?" class="search-clear-icon" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
        <button type="button" class="print-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
    </form>

    <?php if (!empty($alert_msg)): ?>
        <div id="systemAlert" class="system-alert <?php echo ($alert_type === 'error') ? 'error' : ''; ?>">
            <i class="fa-solid <?php echo ($alert_type === 'error') ? 'fa-triangle-exclamation' : 'fa-circle-check'; ?>"></i> 
            <span><?php echo htmlspecialchars($alert_msg); ?></span>
        </div>
    <?php endif; ?>

    <div class="table-card">
        <div class="table-responsive">
            <table class="approval-table" id="approvalTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Application Number</th>
                        <th>NIC</th>
                        <th>Computer Number</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>View Application</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($displayData)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 30px;">No applications awaiting final approval.</td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($displayData as $item): ?>
                            <tr id="row-<?php echo $item['id']; ?>">
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo htmlspecialchars($item['appNo'] ?? (string)$item['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['nic']); ?></td>
                                <td><?php echo htmlspecialchars($item['compNo']); ?></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['desig']); ?></td>
                                <td>
                                    <div class="action-cell">
                                        <a href="view-application-details.php?id=<?php echo $item['id']; ?>" class="action-btn btn-view" title="View Application"><i class="fa-solid fa-eye"></i></a>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-cell">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="action-btn btn-approve" title="Approve"><i class="fa-solid fa-check"></i></button>
                                        </form>
                                        <button type="button" class="action-btn btn-reject" onclick="promptReject(<?php echo $item['id']; ?>)" title="Reject"><i class="fa-solid fa-xmark"></i></button>
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

<div class="custom-msg-box" id="rejectModal">
    <div class="msg-content">
        <h3>Provide Rejection Reason</h3>
        <form method="POST" id="rejectForm">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="id" id="rejectRowId" value="">
            <textarea name="rejectionReason" id="rejectionReasonInput" rows="3" placeholder="Enter reason for rejection..." required></textarea>
            <div class="msg-btns">
                <button type="button" class="msg-btn msg-btn-no" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="msg-btn msg-btn-yes">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

<script>
    function promptReject(id) {
        document.getElementById('rejectRowId').value = id;
        document.getElementById('rejectionReasonInput').value = '';
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
        document.getElementById('rejectRowId').value = '';
    }

    document.addEventListener("DOMContentLoaded", function() {
        const alertBox = document.getElementById('systemAlert');
        if (alertBox) {
            setTimeout(function() {
                alertBox.style.opacity = '0';
                setTimeout(function() {
                    alertBox.style.display = 'none';
                }, 500);
            }, 2000);
        }
    });
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>