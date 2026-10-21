<?php
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ((int)($_SESSION['role_id'] ?? 0) !== 7) {
    http_response_code(403);
    exit('Access denied.');
}

$pageTitle = 'Direct Quarter Allocations - Sri Lanka Railways';
$search = trim($_GET['search'] ?? '');
$conn = getDBConnection();

$sql = "SELECT aq.id, aq.nic, aq.computer_no, aq.quarter_no, aq.quarter_name,
               aq.quarter_location, aq.quarter_type, aq.resident_name,
               aq.designation, aq.workplace, aq.allocated_date,
               a.application_no, a.application_status
        FROM allocated_quarters aq
        LEFT JOIN applications a ON a.id = (
            SELECT MAX(a2.id)
            FROM applications a2
            WHERE a2.nic = aq.nic
              AND a2.source = 'direct'
              AND a2.quarter_type = aq.quarter_type
              AND a2.applied_quarter_number = aq.quarter_no
              AND a2.applied_quarter_location = aq.quarter_location
        )
        WHERE aq.source = 'direct'";

if ($search !== '') {
    $sql .= " AND (aq.nic LIKE ? OR aq.resident_name LIKE ? OR aq.quarter_no LIKE ?)";
    $stmt = $conn->prepare($sql . ' ORDER BY aq.id DESC');
    $like = '%' . $search . '%';
    $stmt->bind_param('sss', $like, $like, $like);
} else {
    $stmt = $conn->prepare($sql . ' ORDER BY aq.id DESC');
}

$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    html, body { background: #f8fafc; }
    .direct-allocation-wrap { max-width: 1500px; margin: 20px auto; padding: 0 20px; }
    .direct-allocation-back { color: #7A1C1C; text-decoration: none; font-weight: 600; }
    .direct-allocation-title { text-align: center; color: #7A1C1C; margin: 18px 0 25px; }
    .direct-allocation-search { display: flex; gap: 10px; margin-bottom: 20px; padding: 16px 20px; background: #fff; border-radius: 12px; }
    .direct-allocation-search input { flex: 1; min-width: 0; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; }
    .direct-allocation-search button { border: 0; border-radius: 8px; padding: 10px 20px; background: #7A1C1C; color: #fff; cursor: pointer; }
    .direct-allocation-table { overflow-x: auto; padding: 20px; background: #fff; border-top: 5px solid #7A1C1C; border-radius: 12px; }
    .direct-allocation-table table { width: 100%; min-width: 1250px; border-collapse: collapse; font-size: .85rem; }
    .direct-allocation-table th, .direct-allocation-table td { border: 1px solid #e2e8f0; padding: 10px 8px; text-align: center; }
    .direct-allocation-table th { background: #f8fafc; font-size: .75rem; text-transform: uppercase; }
    .direct-allocation-action { display: inline-flex; gap: 6px; align-items: center; padding: 6px 12px; border-radius: 6px; background: #059669; color: #fff; text-decoration: none; white-space: nowrap; }
    .direct-allocation-status { display: inline-block; padding: 3px 10px; border-radius: 20px; background: #fef3c7; color: #92400e; font-weight: 600; font-size: .75rem; white-space: nowrap; }
    .direct-allocation-status.approved { background: #dcfce7; color: #166534; }
</style>

<div class="direct-allocation-wrap">
    <a class="direct-allocation-back" href="FA_dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2 class="direct-allocation-title">Direct Quarter Allocations (Audit)</h2>

    <form method="GET" class="direct-allocation-search">
        <input type="text" name="search" placeholder="Search by NIC, resident name, or quarter number"
               value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    </form>

    <div class="direct-allocation-table">
        <table>
            <thead>
                <tr>
                    <th>No.</th><th>NIC</th><th>Computer No.</th><th>Resident Name</th>
                    <th>Designation</th><th>Workplace</th><th>Quarter No.</th>
                    <th>Location</th><th>Type</th><th>Allocated Date</th>
                    <th>Application No.</th><th>Application Status</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="13" style="padding:25px; color:#64748b;">No direct allocations found.</td></tr>
            <?php else: foreach ($rows as $i => $row): ?>
                <?php
                $applicationStatus = $row['application_status'] ?? '';
                $statusLabel = $applicationStatus !== '' ? ucwords(str_replace('_', ' ', $applicationStatus)) : 'Application not submitted';
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($row['nic'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['computer_no'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['resident_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['designation'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['workplace'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['quarter_no'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['quarter_location'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['quarter_type'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['allocated_date'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['application_no'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <span class="direct-allocation-status<?= $applicationStatus === 'approved' ? ' approved' : '' ?>">
                            <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td>
                        <?php if (empty($row['application_no'])): ?>
                            <a class="direct-allocation-action" href="resume_direct_application.php?id=<?= (int)$row['id'] ?>">
                                <i class="fa-solid fa-pen-to-square"></i> Go to Application
                            </a>
                        <?php else: ?>
                            <span>Application submitted</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>
