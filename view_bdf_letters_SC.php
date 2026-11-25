<?php
/**
 * Letters Sent by BDF - Subject Clerk View (Read-Only)
 * ----------------------------------------------------
 * Shows every letter the BDF has generated and saved in
 * the `bdf_generated_letters` table. Subject Clerk can
 * view and download the PDFs but cannot delete or edit them.
 */
require_once __DIR__ . '/../../../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only Subject Clerk (role_id = 5) or Super Admin (role_id = 2) may access
$roleId = (int)($_SESSION['role_id'] ?? 0);
if (!in_array($roleId, [2, 5], true)) {
    http_response_code(403);
    exit('Access denied.');
}

$pageTitle = "Letters Sent by BDF - Sri Lanka Railways";
$userData = [
    'name'     => $_SESSION['user_name'] ?? 'Subject Clerk',
    'role'     => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$searchQuery = trim($_GET['search'] ?? '');
$conn = getDBConnection();

// Fetch BDF letters joined with application + user data
$sql = "SELECT bgl.id,
               bgl.application_id,
               bgl.application_no,
               bgl.nic,
               bgl.applicant_name,
               bgl.quarter_no,
               bgl.quarter_location,
               bgl.handover_date,
               bgl.letter_subject,
               bgl.file_path,
               bgl.generated_at,
               bgl.generated_by_nic,
               COALESCE(u.applicant_designation, '')            AS designation,
               COALESCE(u.applicant_current_work_station, '')   AS workplace,
               COALESCE(u.name, bgl.applicant_name)             AS user_name
        FROM bdf_generated_letters AS bgl
        LEFT JOIN applications AS a ON a.id = bgl.application_id
        LEFT JOIN users        AS u ON u.nic = bgl.nic
        WHERE 1 = 1";

if ($searchQuery !== '') {
    $sql .= " AND (bgl.application_no LIKE ?
                OR bgl.nic LIKE ?
                OR bgl.applicant_name LIKE ?
                OR bgl.quarter_no LIKE ?
                OR bgl.letter_subject LIKE ?)";
    $searchTerm = '%' . $searchQuery . '%';
    $stmt = $conn->prepare($sql . ' ORDER BY bgl.generated_at DESC, bgl.id DESC');
    $stmt->bind_param('sssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare($sql . ' ORDER BY bgl.generated_at DESC, bgl.id DESC');
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
    --primary-dark:  #571a1a;
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
.page-subtitle {
    color: #64748b; font-size: 0.9rem; margin-top: 4px; font-weight: 500;
}

/* Stats bar */
.stats-bar { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
.stat-chip { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 16px; display: flex; align-items: center; gap: 10px; min-width: 180px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
.stat-chip .stat-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; }
.stat-chip .stat-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.4px; }
.stat-chip .stat-value { font-size: 1.4rem; font-weight: 700; color: #1e293b; line-height: 1; }
.icon-cyan   { background: #0891b2; }
.icon-purple { background: #7c3aed; }

.search-card { background: var(--card-bg); padding: 18px 22px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; border: 1px solid var(--border-color); }
.search-wrapper { display: flex; gap: 10px; align-items: center; flex: 1; min-width: 280px; }
.search-input-container { position: relative; flex: 1; max-width: 400px; }
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
.bdf-table { width: 100%; border-collapse: collapse; text-align: center; font-size: 0.88rem; min-width: 1500px; }
.bdf-table th, .bdf-table td { padding: 12px 10px; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
.bdf-table th { background-color: #f8fafc; color: var(--text-main); font-weight: 700; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.5px; border-bottom: 2px solid var(--border-color); }
.bdf-table tr:hover { background-color: #f1f5f9; }

.quarter-badge { background-color: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
.subject-cell { text-align: left; max-width: 300px; white-space: normal; word-break: break-word; color: #334155; font-size: 0.85rem; }

.btn-view-pdf {
    background: linear-gradient(135deg, #0891b2, #0e7490);
    color: white; border: none; padding: 7px 14px; border-radius: 6px;
    font-weight: 600; font-size: 0.78rem; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px; text-decoration: none;
    transition: all 0.2s;
}
.btn-view-pdf:hover { background: linear-gradient(135deg, #0e7490, #155e75); transform: translateY(-1px); }

.btn-download-pdf {
    background: linear-gradient(135deg, #059669, #047857);
    color: white; border: none; padding: 7px 14px; border-radius: 6px;
    font-weight: 600; font-size: 0.78rem; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px; text-decoration: none;
    transition: all 0.2s;
}
.btn-download-pdf:hover { background: linear-gradient(135deg, #047857, #065f46); transform: translateY(-1px); }

.action-cell { display: flex; gap: 6px; justify-content: center; align-items: center; }

.empty-state { text-align: center; padding: 50px 20px; color: #64748b; }
.empty-state i { font-size: 3rem; color: #cbd5e1; display: block; margin-bottom: 12px; }

@media (max-width: 768px) {
    .approval-container { padding: 10px 15px; }
    .approval-container h2 { font-size: 1.4rem; }
    .search-card { flex-direction: column; align-items: stretch; }
    .search-wrapper { width: 100%; }
    .search-input-container { max-width: 100%; }
    .search-btn, .print-btn { width: 100%; justify-content: center; }
}

@media print {
    @page { size: landscape; margin: 5mm; }
    html, body { background-color: #fff !important; overflow: visible !important; }
    .approval-container { width: 100% !important; max-width: none !important; padding: 0 !important; }
    .back-link, .search-card, .stats-bar, .action-cell { display: none !important; }
    th:last-child, td:last-child { display: none !important; }
    .table-card, .table-responsive { width: 100% !important; overflow: visible !important; }
    .table-card { box-shadow: none !important; border: none !important; padding: 0 !important; }
    .bdf-table {
        width: 100% !important;
        min-width: 0 !important;
        table-layout: fixed;
        font-size: 7pt !important;
    }
    .bdf-table thead { display: table-row-group !important; }
    .bdf-table tr { break-inside: avoid; page-break-inside: avoid; }
    .bdf-table th, .bdf-table td {
        padding: 4px 3px !important;
        white-space: normal !important;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
}
</style>

<main class="approval-container">
    <a href="SC_dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>

    <div class="approval-header">
        <h2>Letters Sent by BDF</h2>
        <p class="page-subtitle">Read-only view of all handover confirmation letters generated by the Building Department Foreman</p>
    </div>

    <?php
        $totalCount    = count($displayData);
        $thisMonthCount = 0;
        $currentMonth  = date('Y-m');
        foreach ($displayData as $row) {
            if (substr($row['generated_at'], 0, 7) === $currentMonth) {
                $thisMonthCount++;
            }
        }
    ?>
    <div class="stats-bar">
        <div class="stat-chip">
            <div class="stat-icon icon-cyan"><i class="fa-solid fa-envelope-open-text"></i></div>
            <div>
                <div class="stat-label">Total BDF Letters</div>
                <div class="stat-value"><?php echo $totalCount; ?></div>
            </div>
        </div>
        <div class="stat-chip">
            <div class="stat-icon icon-purple"><i class="fa-solid fa-calendar"></i></div>
            <div>
                <div class="stat-label">This Month</div>
                <div class="stat-value"><?php echo $thisMonthCount; ?></div>
            </div>
        </div>
    </div>

    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" class="search-input"
                    placeholder="Search by App No, NIC, Name, Quarter No or Subject..."
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
            <table class="bdf-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Application No.</th>
                        <th>NIC</th>
                        <th>Applicant Name</th>
                        <th>Designation</th>
                        <th>Quarter No.</th>
                        <th>Quarter Location</th>
                        <th>Handover Date</th>
                        <th>Letter Subject</th>
                        <th>Generated On</th>
                        <th>Generated By (NIC)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($displayData)): ?>
                        <tr>
                            <td colspan="12">
                                <div class="empty-state">
                                    <i class="fa-solid fa-inbox"></i>
                                    <p style="font-size: 1.1rem; font-weight: 600; margin: 0 0 6px;">No BDF letters received yet</p>
                                    <p style="margin: 0; font-size: 0.9rem;">Letters sent by the Building Department Foreman will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($displayData as $row): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['application_no'] ?? '-'); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['nic']); ?></td>
                                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['designation'] ?: '-'); ?></td>
                                <td>
                                    <?php if (!empty($row['quarter_no'])): ?>
                                        <span class="quarter-badge"><?php echo htmlspecialchars($row['quarter_no']); ?></span>
                                    <?php else: ?>
                                        <span style="color:#94a3b8; font-style:italic;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['quarter_location'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['handover_date'] ?: '-'); ?></td>
                                <td class="subject-cell"><?php echo htmlspecialchars($row['letter_subject'] ?: '-'); ?></td>
                                <td style="white-space:nowrap;">
                                    <?php echo htmlspecialchars($row['generated_at'] ? date('Y-m-d H:i', strtotime($row['generated_at'])) : '-'); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['generated_by_nic'] ?: '-'); ?></td>
                                <td>
                                    <div class="action-cell">
                                        <a href="<?php echo baseUrl($row['file_path']); ?>" target="_blank" class="btn-view-pdf" title="View PDF in new tab">
                                            <i class="fa-solid fa-eye"></i> View
                                        </a>
                                        <a href="<?php echo baseUrl($row['file_path']); ?>" download class="btn-download-pdf" title="Download PDF">
                                            <i class="fa-solid fa-download"></i> Download
                                        </a>
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

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>