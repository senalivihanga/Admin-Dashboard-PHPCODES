<?php
/**
 * BDF Dashboard - Subject Clerk Forwarded Letters
 * -------------------------------------------------
 * Now connected to the database. Shows all letters the SC has
 * forwarded (sent_to_bdf = 1) from `generated_letters`, joined
 * with `applications`, `users`, and `allocated_quarters`.
 */
require_once __DIR__ . '/../../../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$roleId = (int)($_SESSION['role_id'] ?? 0);
// Only BDF role (8) or Super Admin (2) may access
if (!in_array($roleId, [2, 8], true)) {
    http_response_code(403);
    exit('Access denied.');
}

$pageTitle = "Subject Clerk Forwarded Letters - BDF Portal - Sri Lanka Railways";
$userData  = [
    'name'     => $_SESSION['user_name'] ?? 'BDF Member',
    'role'     => 'Sri Lanka Railways - Board of Directors',
    'initials' => 'BDF'
];

// ----------------------------------------------------------------
// DATABASE QUERY - fetch all letters forwarded to BDF
// ----------------------------------------------------------------
$searchQuery = trim($_GET['search'] ?? '');
$conn = getDBConnection();

$sql = "SELECT
            gl.id                AS letter_id,
            gl.application_id,
            gl.application_no,
            gl.nic,
            gl.applicant_name,
            gl.letter_subject,
            gl.file_path,
            gl.sent_to_bdf_at   AS forwarded_date,
            gl.generated_at,
            COALESCE(u.applicant_designation, '')          AS designation,
            COALESCE(a.quarter_type, '')                   AS quarter_type,
            COALESCE(a.applied_quarter_number, '')         AS applied_quarter_number,
            COALESCE(a.applied_quarter_location, '')       AS applied_quarter_location,
            COALESCE(aq.quarter_no, '')                    AS allocated_quarter_no,
            COALESCE(aq.quarter_name, '')                  AS allocated_quarter_name,
            COALESCE(aq.quarter_location, '')              AS allocated_quarter_location,
            COALESCE(a.final_status, 'pending')            AS final_status
        FROM generated_letters AS gl
        INNER JOIN applications AS a  ON a.id  = gl.application_id
        INNER JOIN users        AS u  ON u.nic = gl.nic
        LEFT  JOIN allocated_quarters AS aq
            ON aq.nic = gl.nic AND aq.source = 'normal'
        WHERE gl.sent_to_bdf = 1";

if ($searchQuery !== '') {
    $sql .= " AND (gl.application_no LIKE ?
                 OR gl.nic LIKE ?
                 OR gl.applicant_name LIKE ?)";
    $searchTerm = '%' . $searchQuery . '%';
    $stmt = $conn->prepare($sql . ' ORDER BY gl.sent_to_bdf_at DESC, gl.id DESC');
    $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare($sql . ' ORDER BY gl.sent_to_bdf_at DESC, gl.id DESC');
}

$stmt->execute();
$forwardedLetters = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    html, body { height: 100%; margin: 0; padding: 0; overflow-y: auto; background: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .container-custom { max-width: 1550px; margin: 20px auto; padding: 0 20px; box-sizing: border-box; }    .page-main-title { text-align: center; color: #7A1C1C; font-size: 1.75rem; margin-bottom: 25px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; text-decoration: underline; text-underline-offset: 8px; clear: both; }
    .section-title-sub { color: #1e293b; font-size: 1.15rem; font-weight: 700; margin-bottom: 15px; border-left: 4px solid #7A1C1C; padding-left: 10px; text-transform: uppercase; }

    /* Stats bar */
    .stats-bar { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
    .stat-chip { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 16px; display: flex; align-items: center; gap: 10px; min-width: 180px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .stat-chip .stat-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; }
    .stat-chip .stat-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.4px; }
    .stat-chip .stat-value { font-size: 1.4rem; font-weight: 700; color: #1e293b; line-height: 1; }
    .icon-blue   { background: #2563eb; }
    .icon-green  { background: #10b981; }
    .icon-purple { background: #7c3aed; }

    .filter-card { background: #fff; padding: 18px 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04); border: 1px solid #e2e8f0; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .search-box-wrapper { position: relative; display: flex; align-items: center; flex: 1; max-width: 450px; }
    .search-box-wrapper i { position: absolute; left: 14px; color: #64748b; font-size: 0.9rem; }
    .search-input { width: 100%; padding: 10px 14px 10px 40px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.9rem; outline: none; background: #fff; }
    .search-input:focus { border-color: #7A1C1C; box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1); }
    .search-btn { background: #7A1C1C; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background 0.2s; font-size: 0.9rem; }
    .search-btn:hover { background: #5c1414; }
    .print-btn { background: #0284c7; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 0.9rem; }
    .print-btn:hover { background: #0369a1; }

    .table-card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-top: 5px solid #7A1C1C; overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; text-align: center; min-width: 1300px; }
    .data-table th, .data-table td { padding: 12px 14px; border: 1px solid #e2e8f0; font-size: 0.88rem; vertical-align: middle; }
    .data-table th { background: #f8fafc; color: #1e293b; font-weight: 700; text-transform: uppercase; font-size: 0.78rem; }
    .data-table td { color: #4b5563; }
    .data-table td:nth-child(2) { font-weight: 700; color: #1e293b; }
    .data-table tbody tr:hover { background: #f8fafc; }

    .status-badge { background: #dbeafe; color: #1e40af; padding: 6px 14px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; display: inline-block; }
    .status-badge.approved { background: #dcfce7; color: #166534; }
    .status-badge.rejected { background: #fee2e2; color: #991b1b; }

    .action-btn-group { display: flex; justify-content: center; align-items: center; gap: 8px; flex-wrap: nowrap; }
    .btn-view-sc { background: #7A1C1C; color: white; border: none; padding: 7px 12px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; transition: background 0.2s; white-space: nowrap; }
    .btn-view-sc:hover { background: #5c1414; }
    .btn-gen-bdf { background: #0284c7; color: white; border: none; padding: 7px 12px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; transition: background 0.2s; white-space: nowrap; }
    .btn-gen-bdf:hover { background: #0369a1; }
    .btn-approve-bdf { background: #10b981; color: white; border: none; padding: 7px 12px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; transition: background 0.2s; white-space: nowrap; }
    .btn-approve-bdf:hover { background: #059669; }

    .empty-state { text-align: center; padding: 60px 20px; color: #64748b; }
    .empty-state i { font-size: 3rem; color: #cbd5e1; display: block; margin-bottom: 12px; }

    @media (max-width: 768px) {
        html, body { width: 100%; overflow-x: hidden; }
        body > header {
            width: 100%;
            padding: 14px 12px;
            gap: 12px;
            box-sizing: border-box;
        }
        body > header > div:first-child {
            width: 100%;
            min-width: 0;
            justify-content: center;
            gap: 10px;
            margin-bottom: 0;
        }
        body > header > div:first-child img { max-width: 56px; height: 48px !important; }
        body > header > div:first-child > div { min-width: 0; }
        body > header > div:first-child h1 {
            font-size: 1.1rem;
            line-height: 1.2;
            letter-spacing: 0.03em;
            overflow-wrap: anywhere;
        }
        body > header > div:first-child h2 { font-size: 0.7rem; line-height: 1.25; }
        body > header > div:last-child { width: 100%; justify-content: center; }
        .profile-dropdown-container > button { max-width: calc(100vw - 24px); padding: 8px 12px; gap: 8px; }
        .profile-dropdown-container > button span { max-width: 65vw; overflow-wrap: anywhere; }
        .container-custom { width: 100%; max-width: 100%; padding: 0 12px; margin: 15px auto; }
        .page-main-title {
            display: block;
            width: 100%;
            max-width: 100%;
            margin: 0 auto 20px;
            padding: 0 8px;
            font-size: 1.2rem;
            line-height: 1.3;
            letter-spacing: 0.3px;
            text-underline-offset: 4px;
            overflow-wrap: break-word;
        }
        .section-title-sub { font-size: 1rem; line-height: 1.35; overflow-wrap: break-word; }
        .stats-bar { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .stat-chip { min-width: 0; padding: 10px; gap: 8px; }
        .stat-chip .stat-icon { flex: 0 0 32px; width: 32px; height: 32px; }
        .stat-chip .stat-label { font-size: 0.68rem; }
        .stat-chip .stat-value { font-size: 1.2rem; }
        .filter-card { flex-direction: column; align-items: stretch; padding: 14px; gap: 12px; }
        .search-box-wrapper { width: 100%; max-width: none; min-width: 0; }
        .search-input { min-width: 0; padding: 12px 14px 12px 40px; font-size: 16px; }
        .filter-card > div:last-child { width: 100%; }
        .filter-card > div:last-child .search-btn { flex: 1; min-width: 0; justify-content: center; padding: 10px 12px; }
        .management-tiles { grid-template-columns: minmax(0, 1fr) !important; gap: 12px !important; }
        .management-tiles > a { min-width: 0; padding: 16px !important; gap: 12px !important; }
        .table-card { width: 100%; max-width: 100%; padding: 12px; -webkit-overflow-scrolling: touch; }
        .data-table th, .data-table td { padding: 10px; }
        .empty-state { padding: 40px 12px; }
        .empty-state p { overflow-wrap: anywhere; }
    }

    @media (max-width: 360px) {
        .stats-bar { grid-template-columns: minmax(0, 1fr); }
        .page-main-title { font-size: 1.1rem; }
        body > header > div:first-child h1 { font-size: 1rem; }
    }

    @media print {
        @page { size: landscape; margin: 5mm; }
        body { background: #fff !important; }
        .user-profile-wrapper, .filter-card, .action-btn-group, .stats-bar { display: none !important; }
        .table-card { box-shadow: none !important; border: none !important; padding: 0 !important; }
        .data-table { font-size: 8.5pt !important; }
        .data-table th, .data-table td { padding: 6px 8px !important; }
    }
</style>

<div class="container-custom">
    <h2 class="page-main-title">Subject Clerk Forwarded Letters</h2>

    <!-- Stats bar -->
    <?php
        $totalCount     = count($forwardedLetters);
        $pendingCount   = 0;
        $approvedCount  = 0;
        foreach ($forwardedLetters as $fl) {
            if ($fl['final_status'] === 'approved') $approvedCount++;
            else $pendingCount++;
        }
    ?>
    <div class="stats-bar">
        <div class="stat-chip">
            <div class="stat-icon icon-blue"><i class="fa-solid fa-inbox"></i></div>
            <div>
                <div class="stat-label">Total Received</div>
                <div class="stat-value"><?php echo $totalCount; ?></div>
            </div>
        </div>
        <div class="stat-chip">
            <div class="stat-icon icon-purple"><i class="fa-solid fa-clock"></i></div>
            <div>
                <div class="stat-label">Pending Review</div>
                <div class="stat-value"><?php echo $pendingCount; ?></div>
            </div>
        </div>
        <div class="stat-chip">
            <div class="stat-icon icon-green"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <div class="stat-label">Approved</div>
                <div class="stat-value"><?php echo $approvedCount; ?></div>
            </div>
        </div>
    </div>

    <div class="section-title-sub">Received Letters for BDF Approval</div>

    <!-- Search Bar -->
    <form method="GET" action="" class="filter-card">
        <div class="search-box-wrapper">
            <i class="fa-solid fa-search"></i>
            <input type="text" name="search" class="search-input"
                placeholder="Search by Application No, NIC, or Applicant Name..."
                value="<?php echo htmlspecialchars($searchQuery); ?>">
        </div>
        <div style="display:flex;gap:10px;">
            <button type="submit" class="search-btn"><i class="fa-solid fa-search"></i> Search</button>
            <?php if ($searchQuery !== ''): ?>
                <a href="BDF_dashboard.php" class="search-btn" style="background:#64748b;">
                    <i class="fa-solid fa-xmark"></i> Clear
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- BDF Letters Management Tiles -->
    <div class="management-tiles" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:16px; margin-bottom:25px;">
        <a href="view_all_bdf_letters.php" style="background:#fff; padding:20px 24px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.05); border-top:5px solid #7c3aed; text-decoration:none; display:flex; align-items:center; gap:14px; transition:transform 0.2s;">
            <i class="fa-solid fa-folder-open" style="font-size:2rem; color:#7c3aed;"></i>
            <div>
                <h4 style="margin:0 0 4px; color:#1e293b; font-size:1rem; font-weight:700;">All BDF Letters</h4>
                <p style="margin:0; color:#64748b; font-size:0.85rem;">View every BDF letter ever generated</p>
            </div>
        </a>
    </div>


    <!-- Letters Table -->
    <div class="table-card">
        <?php if (empty($forwardedLetters)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <p style="font-size:1.1rem;font-weight:600;margin:0 0 6px;">No forwarded letters yet</p>
                <p style="margin:0;font-size:0.9rem;">Letters sent by the Subject Clerk will appear here.</p>
            </div>
        <?php else: ?>
            <table class="data-table" id="lettersTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Application No.</th>
                        <th>Applicant Name</th>
                        <th>NIC</th>
                        <th>Designation</th>
                        <th>Requested Quarter</th>
                        <th>Allocated Quarter</th>
                        <th>Forwarded Date</th>
                        <th>Final Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($forwardedLetters as $letter): ?>
                        <?php
                            $requestedQ = trim(($letter['applied_quarter_number'] ?? '') . ' ' . ($letter['applied_quarter_location'] ?? ''));
                            $allocatedQ = '';
                            if (!empty($letter['allocated_quarter_no'])) {
                                $allocatedQ = $letter['allocated_quarter_no'];
                                if (!empty($letter['allocated_quarter_name'])) $allocatedQ .= ' (' . $letter['allocated_quarter_name'] . ')';
                                elseif (!empty($letter['allocated_quarter_location'])) $allocatedQ .= ' (' . $letter['allocated_quarter_location'] . ')';
                            }
                            $statusClass = '';
                            $statusLabel = 'Pending';
                            if ($letter['final_status'] === 'approved') { $statusClass = 'approved'; $statusLabel = 'Approved'; }
                            elseif ($letter['final_status'] === 'rejected') { $statusClass = 'rejected'; $statusLabel = 'Rejected'; }
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($letter['application_no'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($letter['applicant_name']); ?></td>
                            <td><?php echo htmlspecialchars($letter['nic']); ?></td>
                            <td><?php echo htmlspecialchars($letter['designation'] ?: '-'); ?></td>
                            <td>
                                <?php if ($requestedQ !== ''): ?>
                                    <?php echo htmlspecialchars($requestedQ); ?>
                                    <?php if (!empty($letter['quarter_type'])): ?>
                                        <br><small style="color:#64748b;">(<?php echo htmlspecialchars($letter['quarter_type']); ?>)</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-style:italic;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($allocatedQ !== ''): ?>
                                    <span style="background:#dbeafe;color:#1e40af;padding:4px 10px;border-radius:12px;font-size:0.78rem;font-weight:600;">
                                        <?php echo htmlspecialchars($allocatedQ); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-style:italic;">Not allocated</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($letter['forwarded_date'] ? date('Y-m-d H:i', strtotime($letter['forwarded_date'])) : '-'); ?></td>
                            <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusLabel); ?></span></td>
                            <td>
                                <div class="action-btn-group">
                                    <!-- View SC's generated PDF letter -->
                                    <a href="<?php echo baseUrl($letter['file_path']); ?>" target="_blank" class="btn-view-sc" title="View Subject Clerk Letter">
                                        <i class="fa-solid fa-envelope-open-text"></i> View Letter
                                    </a>
                                    <!-- Generate BDF response letter -->
                                    <a href="generate_letter_BDF.php?app=<?php echo urlencode($letter['application_no']); ?>" class="btn-gen-bdf" title="Generate BDF Letter">
                                        <i class="fa-solid fa-file-pen"></i> Generate BDF Letter
                                    </a>
                                    <!-- Approve (only if still pending) -->
                                    <?php if ($letter['final_status'] !== 'approved'): ?>
                                        <a href="appli_approve_FA.php" class="btn-approve-bdf" title="Go to Final Approval">
                                            <i class="fa-solid fa-check"></i> Approve
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>