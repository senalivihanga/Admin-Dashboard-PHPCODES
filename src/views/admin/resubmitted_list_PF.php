<?php
/**
 * Application Approval Resubmitted List - Sri Lanka Railways (PF)
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Page Title සැකසීම
$pageTitle = "Application Approval Resubmitted List - Sri Lanka Railways";

$alertMessage = '';
$alertType = '';
$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $actionId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $reason = trim((string)($_POST['reason'] ?? ''));

    if ($actionId && ($action === 'approve' || ($action === 'reject' && $reason !== ''))) {
                $fileStatus = $action === 'approve' ? 'approved' : 'rejected';
        $fileReason = $action === 'reject' ? $reason : null;
        // Approve -> continues to Subject Clerk. Reject -> goes BACK TO THE IMMEDIATE BOSS (not the applicant).
        $applicationStatus = $action === 'approve' ? 'pending_clerk' : 'returned_to_boss';
        $stmt = $conn->prepare(
            "UPDATE applications
             SET file_status = ?, file_reason = ?, application_status = ?, clerk_status = 'pending'
             WHERE id = ? AND boss_status = 'approved'
               AND file_status = 'pending' AND is_resubmission = 1"
        );
        $stmt->bind_param('sssi', $fileStatus, $fileReason, $applicationStatus, $actionId);
        $stmt->execute();

                if ($stmt->affected_rows === 1) {
            $alertMessage = $action === 'approve'
                ? 'Resubmitted application approved successfully.'
                : 'Resubmitted application rejected successfully.';
            $alertType = $action === 'approve' ? 'success' : 'error';
        } else {
            $alertMessage = 'This application is no longer awaiting your decision.';
            $alertType = 'error';
        }
        $stmt->close();
    } elseif ($action === 'reject') {
        $alertMessage = 'A rejection reason is required.';
        $alertType = 'error';
    }
}

$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT a.id, a.application_no, a.nic, a.computer_no, a.file_reason,
               a.boss_reason, u.name, u.applicant_designation
        FROM applications AS a
        INNER JOIN users AS u ON u.nic = a.nic
        WHERE a.boss_status = 'approved'
          AND a.file_status = 'pending'
          AND a.is_resubmission = 1
          AND a.application_status = 'pending_clerk'";

if ($searchQuery !== '') {
    $sql .= " AND (a.application_no LIKE ? OR a.nic LIKE ? OR a.computer_no LIKE ? OR u.name LIKE ?)";
    $searchTerm = '%' . $searchQuery . '%';
    $stmt = $conn->prepare($sql . ' ORDER BY a.application_date ASC, a.id ASC');
    $stmt->bind_param('ssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare($sql . ' ORDER BY a.application_date ASC, a.id ASC');
}
$stmt->execute();
$filteredData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

foreach ($filteredData as &$item) {
    $item['reason'] = $item['file_reason'] ?: ($item['boss_reason'] ?: '-');
}
unset($item);

// Admin Header Include
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
    :root {
        --primary-color: #7A1C1C;
        --primary-dark: #571a1a;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --text-main: #1e293b;
        --border-color: #cbd5e1;
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
        max-width: 1550px;
        margin: 20px auto;
        padding: 0 20px;
        box-sizing: border-box;
    }

    .approval-header {
        text-align: center;
        margin-bottom: 25px;
    }

    .approval-container h2 {
        font-size: 2rem;
        color: var(--primary-color);
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 6px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    .search-card {
        background: var(--card-bg);
        padding: 20px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        border: 1px solid #e2e8f0;
        box-sizing: border-box;
        width: 100%;
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
        padding: 10px 36px 10px 14px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        font-size: 0.9rem;
        width: 100%;
        outline: none;
        box-sizing: border-box;
    }

    .search-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1);
    }

    .clear-input-btn {
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
        cursor: pointer;
        transition: color 0.2s;
    }

    .clear-input-btn:hover {
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
        gap: 8px;
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

    .system-alert {
        background-color: #fef2f2;
        color: #991b1b;
        padding: 14px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 600;
        border: 1px solid #fecaca;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: opacity 0.5s ease;
    }

    .system-alert.success {
        background-color: #f0fdf4;
        color: #166534;
        border-color: #bbf7d0;
    }

    .table-responsive {
        width: 100%;
        box-sizing: border-box;
        overflow-x: auto;
        background: var(--card-bg);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        border-top: 5px solid var(--primary-color);
        -webkit-overflow-scrolling: touch;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.85rem;
        table-layout: fixed;
        word-wrap: break-word;
    }

    table th:nth-child(1), table td:nth-child(1) { width: 5%; }
    table th:nth-child(2), table td:nth-child(2) { width: 15%; }
    table th:nth-child(3), table td:nth-child(3) { width: 11%; }
    table th:nth-child(4), table td:nth-child(4) { width: 9%; }
    table th:nth-child(5), table td:nth-child(5) { width: 13%; }
    table th:nth-child(6), table td:nth-child(6) { width: 13%; }
    table th:nth-child(7), table td:nth-child(7) { width: 20%; }
    table th:nth-child(8), table td:nth-child(8) { width: 7%; }
    table th:nth-child(9), table td:nth-child(9) { width: 7%; }

    th, td {
        padding: 12px 8px;
        border: 1px solid var(--border-color);
        text-align: center;
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    th {
        background-color: #f8fafc;
        color: var(--text-main);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0;
    }

    tr:hover {
        background-color: #f1f5f9;
    }

    .reason-badge {
        background-color: #fef2f2;
        color: #dc2626;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
        border: 1px solid #fecaca;
        display: inline-block;
        width: 100%;
        text-align: left;
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .btn-view, .btn-approve, .btn-reject {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        cursor: pointer;
        border: none;
        color: white;
        transition: background-color 0.2s;
        text-decoration: none;
    }

    .btn-view { background-color: #3b82f6; }
    .btn-view:hover { background-color: #2563eb; }

    .btn-approve { background-color: #10b981; }
    .btn-approve:hover { background-color: #059669; }

    .btn-reject { background-color: #ef4444; }
    .btn-reject:hover { background-color: #dc2626; }

    .action-group {
        display: flex;
        gap: 5px;
        align-items: center;
        justify-content: center;
        flex-wrap: nowrap;
    }

    .modal {
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
        box-sizing: border-box;
    }

    .modal-content {
        background-color: #fff;
        padding: 25px;
        border-radius: 10px;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        box-sizing: border-box;
    }

    .modal-content h3 {
        color: var(--primary-color);
        margin-bottom: 15px;
        font-size: 1.2rem;
    }

    .modal-content textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        margin-bottom: 15px;
        font-family: inherit;
        outline: none;
        box-sizing: border-box;
        resize: vertical;
        height: 100px;
    }

    .modal-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-cancel {
        background-color: #64748b;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .approval-container {
            padding: 10px 15px;
        }
        .approval-container h2 {
            font-size: 1.5rem;
        }
        .search-card {
            flex-direction: column;
            align-items: stretch;
        }
        .search-wrapper {
            width: 100%;
        }
        .search-input-container {
            max-width: 100%;
        }
        .search-btn, .print-btn {
            width: 100%;
            justify-content: center;
        }
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

        .back-link, .search-card, .system-alert, .modal {
            display: none !important;
        }

        .approval-container {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .approval-header h2 {
            font-size: 16pt !important;
            color: var(--primary-color) !important;
            text-decoration: underline !important;
            margin-bottom: 15px !important;
        }

        .table-responsive {
            overflow: visible !important;
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
        }

        table {
            width: 100% !important;
            font-size: 8.5pt;
        }

        th, td {
            padding: 8px 10px !important;
        }

        .reason-badge {
            border: none !important;
            background: transparent !important;
            color: #000 !important;
            padding: 0 !important;
            text-align: left !important;
        }

        th:nth-child(8), td:nth-child(8),
        th:nth-child(9), td:nth-child(9) {
            display: none !important;
        }

        th {
            background-color: #f8fafc !important;
            color: #1e293b !important;
        }
    }
</style>

<main class="approval-container">
    <a href="PF_Dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <div class="approval-header">
        <h2>Application Approval Resubmitted List</h2>
    </div>

    <?php if (!empty($alertMessage)): ?>
        <div class="system-alert <?php echo $alertType; ?>" id="systemAlertBox">
            <i class="fa-solid <?php echo $alertType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i> 
            <span><?php echo htmlspecialchars($alertMessage); ?></span>
        </div>
    <?php endif; ?>

    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" id="searchInput" class="search-input" placeholder="Search by NIC or Name..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                <?php if ($searchQuery !== ''): ?>
                    <a href="?" class="clear-input-btn" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
        <button type="button" class="print-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Application Number</th>
                    <th>NIC</th>
                    <th>Computer Number</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Previous Rejection Reason</th>
                    <th>View Application</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($filteredData)): ?>
                    <tr><td colspan="9" style="text-align: center; color: #64748b; padding: 30px;">No resubmitted applications available.</td></tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($filteredData as $item): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><strong><?php echo htmlspecialchars($item['application_no'] ?? (string)$item['id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($item['nic']); ?></td>
                            <td><?php echo htmlspecialchars($item['computer_no']); ?></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['applicant_designation'] ?? ''); ?></td>
                            <td><span class="reason-badge"><?php echo htmlspecialchars($item['reason']); ?></span></td>
                            <td><a href="view-application-details.php?id=<?php echo $item['id']; ?>" class="btn-view" title="View Application"><i class="fa-solid fa-eye"></i></a></td>
                            <td>
                                <div class="action-group">
                                    <!-- Approve Form -->
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn-approve" title="Approve"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                    <!-- Reject Button -->
                                    <button type="button" class="btn-reject" onclick="openRejectModal(<?php echo $item['id']; ?>)" title="Reject"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Reject Modal Form -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <h3>Provide Rejection Reason</h3>
        <form id="rejectForm" method="POST" action="">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="id" id="modalAppId" value="">
            <textarea name="reason" id="rejectionReasonInput" placeholder="Enter reason for rejecting this application..." required></textarea>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-reject" style="width: auto; height: auto; padding: 8px 14px; border-radius: 5px; font-weight: 600;">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectModal(id) {
        document.getElementById('modalAppId').value = id;
        document.getElementById('rejectionReasonInput').value = '';
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    // Auto remove alert message after 2 seconds
    document.addEventListener("DOMContentLoaded", function() {
        const alertBox = document.getElementById('systemAlertBox');
        if (alertBox) {
            setTimeout(function() {
                alertBox.style.opacity = '0';
                setTimeout(function() {
                    alertBox.remove();
                }, 500); // fade out effect duration
            }, 2000); // 2000 milliseconds = 2 seconds
        }
    });
</script>

<?php
// Admin Footer Include
require_once __DIR__ . '/../layouts/admin_footer.php';
?>