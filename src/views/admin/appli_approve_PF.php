<?php
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";
$messageType = "success";
$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $reason = trim((string)($_POST['reason'] ?? ''));

    if ($id && ($action === 'approve' || ($action === 'reject' && $reason !== ''))) {
        $fileStatus = $action === 'approve' ? 'approved' : 'rejected';
        $fileReason = $action === 'reject' ? $reason : null;
        // Approve -> continues to Subject Clerk. Reject -> goes BACK TO THE IMMEDIATE BOSS (not the applicant).
        $applicationStatus = $action === 'approve' ? 'pending_clerk' : 'returned_to_boss';

        // >>> CHANGED: removed `AND is_resubmission = 0` so resubmitted apps
        //              (approved by IB) also arrive here and can be actioned.
        $stmt = $conn->prepare(
            "UPDATE applications
             SET file_status = ?, file_reason = ?, application_status = ?, clerk_status = 'pending'
             WHERE id = ? AND boss_status = 'approved'
               AND file_status = 'pending'"
        );
        $stmt->bind_param('sssi', $fileStatus, $fileReason, $applicationStatus, $id);
        $stmt->execute();

        if ($stmt->affected_rows === 1) {
            $message = $action === 'approve'
                ? 'Application has been successfully approved.'
                : 'Application rejected successfully.';
            $messageType = $action === 'approve' ? 'success' : 'error';
        } else {
            $message = 'This application is no longer awaiting your decision.';
            $messageType = 'error';
        }
        $stmt->close();
    } elseif ($action === 'reject') {
        $message = 'A rejection reason is required.';
        $messageType = 'error';
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// >>> CHANGED: removed `AND a.is_resubmission = 0` so both new and
//              resubmitted applications appear on PF's normal approval desk.
$sql = "SELECT a.id, a.application_no, a.nic, a.computer_no, a.boss_status,
               u.name, u.applicant_designation, u.applicant_current_work_station,
               u.applicant_personal_file_office
        FROM applications AS a
        INNER JOIN users AS u ON u.nic = a.nic
        WHERE a.boss_status = 'approved'
          AND a.file_status = 'pending'
          AND a.application_status = 'pending_clerk'";

if ($search !== '') {
    $sql .= " AND (a.application_no LIKE ? OR a.nic LIKE ? OR a.computer_no LIKE ? OR u.name LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $stmt = $conn->prepare($sql . ' ORDER BY a.application_date ASC, a.id ASC');
    $stmt->bind_param('ssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare($sql . ' ORDER BY a.application_date ASC, a.id ASC');
}
$stmt->execute();
$filteredApps = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$pageTitle = "Application Approval List - Sri Lanka Railways";
require_once __DIR__ . '/../layouts/admin_header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #7A1C1C;
            --primary-dark: #571a1a;
            --danger-color: #991b1b;
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
            background-color: var(--bg-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .approval-container {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 20px 30px;
            box-sizing: border-box;
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

        .clear-input-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            padding: 0;
        }
        .clear-input-btn:hover {
            color: var(--danger-color);
        }

        .search-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        .search-btn:hover {
            background-color: var(--primary-dark);
        }

        .print-btn {
            background-color: #0284c7;
            color: white;
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
        .print-btn:hover {
            background-color: #0369a1;
        }

        /* System Custom Alert Box */
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

        .table-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            border-top: 5px solid var(--primary-color);
            width: 100%;
            box-sizing: border-box;
        }

        .table-responsive {
            width: 100%;
            overflow-x: visible;
        }

        .approval-table {
            width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse;
            text-align: center;
            font-size: 0.82rem;
            white-space: normal !important;
        }

        .approval-table th, .approval-table td {
            padding: 12px 6px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .approval-table th:nth-child(1), .approval-table td:nth-child(1) { width: 4%; }
        .approval-table th:nth-child(2), .approval-table td:nth-child(2) { width: 12%; }
        .approval-table th:nth-child(3), .approval-table td:nth-child(3) { width: 10%; }
        .approval-table th:nth-child(4), .approval-table td:nth-child(4) { width: 7%; }
        .approval-table th:nth-child(5), .approval-table td:nth-child(5) { width: 11%; }
        .approval-table th:nth-child(6), .approval-table td:nth-child(6) { width: 11%; }
        .approval-table th:nth-child(7), .approval-table td:nth-child(7) { width: 13%; }
        .approval-table th:nth-child(8), .approval-table td:nth-child(8) { width: 14%; }
        .approval-table th:nth-child(9), .approval-table td:nth-child(9) { width: 5%; text-align: center; }
        .approval-table th:nth-child(10), .approval-table td:nth-child(10) { width: 6%; text-align: center; }
        .approval-table th:nth-child(11), .approval-table td:nth-child(11) { width: 7%; text-align: center; }

        .approval-table th {
            background-color: #f8fafc;
            color: var(--text-main);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
        }

        .approval-table td {
            color: #334155;
        }

        .action-group {
            display: flex;
            gap: 4px;
            align-items: center;
            justify-content: center;
        }

        .btn-action-symbol {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 0.75rem;
            transition: opacity 0.2s;
            text-decoration: none;
        }
        .btn-action-symbol:hover {
            opacity: 0.85;
        }

        .btn-approve {
            background-color: #10b981;
        }
        .btn-approve:hover { background-color: #059669; }

        .btn-reject {
            background-color: #ef4444;
        }
        .btn-reject:hover { background-color: #dc2626; }

        .badge-ib-status {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #f3e8ff;
            color: #7e22ce;
            font-size: 0.75rem;
        }

        .btn-view-symbol {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background-color: #8b5cf6;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 0.8rem;
            transition: background-color 0.2s;
        }
        .btn-view-symbol:hover {
            background-color: #7c3aed;
        }

        tr:hover {
            background-color: #f1f5f9;
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
        }

        .modal-content h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .modal-content textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 15px;
            font-family: inherit;
            outline: none;
            height: 90px;
            box-sizing: border-box;
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
        }

        @media (max-width: 768px) {
            .approval-container {
                padding: 10px 15px;
            }
            .approval-container h2 {
                font-size: 1.4rem;
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
                margin: 2mm;
            }

            html, body {
                background-color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                width: 100% !important;
                height: 100% !important;
                overflow: visible !important;
            }

            .back-link, .search-card, .system-alert {
                display: none !important;
            }

            .approval-container {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .table-card {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .table-responsive {
                overflow: visible !important;
                width: 100% !important;
            }

            .approval-table {
                width: 100% !important;
                max-width: 100% !important;
                table-layout: fixed !important;
                font-size: 8.5pt !important;
                white-space: normal !important;
            }

            .approval-table thead {
                display: table-header-group !important;
            }

            .approval-table tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .approval-table th, .approval-table td {
                padding: 10px 6px !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
            }

            .approval-table th:nth-child(1), .approval-table td:nth-child(1) { width: 5%; }
            .approval-table th:nth-child(2), .approval-table td:nth-child(2) { width: 16%; }
            .approval-table th:nth-child(3), .approval-table td:nth-child(3) { width: 11%; }
            .approval-table th:nth-child(4), .approval-table td:nth-child(4) { width: 8%; }
            .approval-table th:nth-child(5), .approval-table td:nth-child(5) { width: 14%; }
            .approval-table th:nth-child(6), .approval-table td:nth-child(6) { width: 13%; }
            .approval-table th:nth-child(7), .approval-table td:nth-child(7) { width: 15%; }
            .approval-table th:nth-child(8), .approval-table td:nth-child(8) { width: 15%; }
            .approval-table th:nth-child(9), .approval-table td:nth-child(9) { width: 3%; text-align: center; }

            th:nth-child(10), td:nth-child(10),
            th:nth-child(11), td:nth-child(11) {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <main class="approval-container">
        <a href="PF_Dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
        
        <div class="approval-header">
            <h2>Application Approval List</h2>
        </div>

        <?php if (!empty($message)): ?>
        <div id="systemAlert" class="system-alert <?php echo $messageType; ?>">
            <i class="fa-solid <?php echo ($messageType === 'success') ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i> 
            <span><?php echo htmlspecialchars($message); ?></span>
        </div>
        <?php endif; ?>

        <form method="GET" action="" class="search-card">
            <div class="search-wrapper">
                <div class="search-input-container">
                    <input type="text" name="search" id="searchInput" class="search-input" placeholder="Search by NIC or Name..." value="<?php echo htmlspecialchars($search); ?>">
                    <?php if ($search !== ''): ?>
                        <a href="?" class="clear-input-btn" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </div>
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </div>
            <button type="button" class="print-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
        </form>

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
                            <th>Current Work Station</th>
                            <th>Personal File Office</th>
                            <th>IB Status</th>
                            <th>View Application</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($filteredApps) === 0): ?>
                            <tr>
                                <td colspan="11" style="text-align: center; color: #64748b; padding: 30px;">No applications found matching your search.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($filteredApps as $index => $item): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($item['application_no'] ?? (string)$item['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['nic'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($item['computer_no'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($item['name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($item['applicant_designation'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($item['applicant_current_work_station'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($item['applicant_personal_file_office'] ?? '-'); ?></td>
                                <td><span class="badge-ib-status" title="<?php echo htmlspecialchars(ucfirst($item['boss_status'])); ?>"><i class="fa-solid fa-check"></i></span></td>
                                <td><a href="view-application-details.php?id=<?php echo $item['id'] ?? 0; ?>" class="btn-view-symbol" title="View Application"><i class="fa-solid fa-eye"></i></a></td>
                                <td>
                                    <div class="action-group">
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn-action-symbol btn-approve" title="Approve"><i class="fa-solid fa-check"></i></button>
                                        </form>
                                        <button type="button" class="btn-action-symbol btn-reject" onclick="openRejectModal(<?php echo $item['id'] ?? 0; ?>)" title="Reject"><i class="fa-solid fa-xmark"></i></button>
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

    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <h3>Provide Rejection Reason</h3>
            <form id="rejectForm" method="POST" action="">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="id" id="rejectAppId">
                <textarea name="reason" id="rejectionReasonInput" placeholder="Enter reason for rejecting this application..." required></textarea>
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="closeModal('rejectModal')">Cancel</button>
                    <button type="submit" class="btn-action-symbol btn-reject" style="width: auto; height: auto; padding: 6px 14px; border-radius: 5px; font-weight: 600;">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal(appId) {
            document.getElementById('rejectAppId').value = appId;
            document.getElementById('rejectionReasonInput').value = '';
            document.getElementById('rejectModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
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
</body>
</html>
<?php
// >>> FIXED: this used to be a second `admin_header.php` include that
//            broke the page. Now correctly loads the footer once.
require_once __DIR__ . '/../layouts/admin_footer.php';
?>