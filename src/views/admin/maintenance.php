<?php
/**
 * Quarters Maintenance - Sri Lanka Railways (database-backed)
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Quarters Maintenance - Sri Lanka Railways";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete']) && $_GET['delete'] !== '') {
    $deleteId = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM maintenance WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header('Location: ' . baseUrl('admin/maintenance.php?msg=deleted'));
    exit;
}

// Fetch with search
$searchQuery = trim($_GET['search'] ?? '');
if ($searchQuery !== '') {
    $like = '%' . $searchQuery . '%';
    $stmt = $conn->prepare(
        "SELECT id, quarter_number, quarter_name, location_name, quarter_type,
                maintenance_status, last_maintenance_date, notes, created_at
           FROM maintenance
          WHERE quarter_number LIKE ? OR quarter_name LIKE ? OR location_name LIKE ?
          ORDER BY id DESC"
    );
    $stmt->bind_param("sss", $like, $like, $like);
} else {
    $stmt = $conn->prepare(
        "SELECT id, quarter_number, quarter_name, location_name, quarter_type,
                maintenance_status, last_maintenance_date, notes, created_at
           FROM maintenance
          ORDER BY id DESC"
    );
}
$stmt->execute();
$maintenanceData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$flash = (($_GET['msg'] ?? '') === 'deleted') ? 'Maintenance record deleted successfully.'
       : ((($_GET['msg'] ?? '') === 'added') ? 'Maintenance record added successfully.' : '');

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    html, body { height: 100%; margin: 0; padding: 0; overflow-y: auto;
        background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .container { max-width: 1400px; margin: 20px auto; padding: 0 20px; width: 100%; box-sizing: border-box; }
    .back-link { display: inline-flex; align-items: center; gap: 8px; color: #7A1C1C;
        text-decoration: none; font-weight: 600; margin-bottom: 20px; font-size: 0.95rem; }
    .back-link:hover { text-decoration: underline; }
    .user-profile-wrapper { position: relative; display: inline-block; margin-bottom: 20px; }
    .user-profile-badge { display: flex; align-items: center; gap: 10px; background: #7A1C1C;
        color: #ffffff; padding: 6px 14px; border-radius: 30px; cursor: pointer;
        user-select: none; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .profile-dropdown { display: none; position: absolute; left: 0; top: 105%; background: #ffffff;
        min-width: 220px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); border-radius: 8px;
        overflow: hidden; z-index: 100; border: 1px solid #e2e8f0; }
    .profile-dropdown.show { display: block; }
    .profile-dropdown a { color: #1e293b; padding: 10px 15px; text-decoration: none;
        display: flex; align-items: center; gap: 8px; font-size: 0.85rem; }
    .profile-dropdown a:hover { background: #f1f5f9; color: #7A1C1C; }
    .profile-dropdown a.logout-btn { color: #ef4444; }
    .profile-dropdown a.logout-btn:hover { background: #fee2e2; }
    .page-title { text-align: center; color: #7A1C1C; font-size: 1.8rem; margin-bottom: 25px;
        text-transform: uppercase; letter-spacing: 1px; text-decoration: underline;
        text-underline-offset: 8px; font-weight: 700; }
    .search-card { background: #ffffff; padding: 20px 25px; border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-bottom: 25px; display: flex;
        justify-content: space-between; align-items: center; flex-wrap: wrap;
        gap: 20px; border: 1px solid #e2e8f0; width: 100%; box-sizing: border-box; }
    .search-wrapper { display: flex; gap: 12px; align-items: center; flex: 1;
        min-width: 300px; max-width: 450px; }
    .search-input-container { position: relative; flex: 1; }
    .search-input { width: 100%; padding: 10px 36px 10px 14px; border-radius: 8px;
        border: 1px solid #cbd5e1; font-size: 0.9rem; outline: none; box-sizing: border-box; }
    .search-input:focus { border-color: #7A1C1C; box-shadow: 0 0 0 3px rgba(122,28,28,0.1); }
    .search-clear-icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
        color: #94a3b8; text-decoration: none; font-size: 1rem; }
    .search-btn { border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
        font-size: 0.9rem; background-color: #7A1C1C; color: white; white-space: nowrap; }
    .search-btn:hover { background-color: #571a1a; }
    .add-maint-btn { background-color: #10b981; color: white; padding: 10px 18px;
        border-radius: 8px; font-weight: 600; font-size: 0.875rem; border: none;
        cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
        text-decoration: none; }
    .add-maint-btn:hover { background-color: #059669; }
    .table-card { background: #ffffff; padding: 25px; border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-top: 5px solid #7A1C1C;
        overflow-x: auto; border: 1px solid #e2e8f0; }
    .maint-table { width: 100%; border-collapse: collapse; min-width: 1100px; }
    .maint-table th, .maint-table td { padding: 14px 12px; border: 1px solid #e2e8f0;
        text-align: center; font-size: 0.85rem; vertical-align: middle; }
    .maint-table th { background-color: #f8fafc; color: #1e293b; font-weight: 700;
        text-transform: uppercase; font-size: 0.75rem; }
    .no-data { text-align: center; padding: 25px; color: #64748b; font-style: italic; }
    .action-cell { display: flex; justify-content: center; gap: 6px; }
    .action-btn { border: none; width: 32px; height: 32px; border-radius: 6px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.85rem; text-decoration: none; color: white; cursor: pointer; }
    .btn-view { background-color: #8b5cf6; }
    .btn-edit { background-color: #3b82f6; }
    .btn-delete { background-color: #dc2626; }
    .system-toast { position: fixed; right: 24px; top: 24px; background: #f0fdf4;
        color: #166534; border: 1px solid #86efac; border-left: 6px solid #22c55e;
        padding: 14px 18px; border-radius: 12px; font-weight: 700;
        box-shadow: 0 12px 30px rgba(34,197,94,0.18); z-index: 1100;
        display: flex; align-items: center; gap: 10px; }
    .status-pill { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem;
        font-weight: 600; background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
</style>

<?php if ($flash): ?>
    <div id="systemToast" class="system-toast">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($flash) ?>
    </div>
    <script>setTimeout(() => document.getElementById('systemToast').style.display='none', 3000);</script>
<?php endif; ?>

<div class="container">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
        <a href="quarters_list.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <?php else: ?>
        <div class="user-profile-wrapper">
            <div class="user-profile-badge" onclick="toggleDropdown()">
                <div style="width: 35px; height: 35px; background: #ffffff; color: #7A1C1C; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem;">
                    <?= htmlspecialchars($userData['initials']) ?>
                </div>
                <div><div style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($userData['name']) ?></div></div>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; color: #f3e8ff; margin-left: 5px;"></i>
            </div>
            <div id="profileDropdown" class="profile-dropdown">
                <a href="edit_profile.php"><i class="fa-solid fa-user-pen"></i> Edit Profile</a>
                <a href="quarters_list.php"><i class="fa-solid fa-arrow-left"></i> Back to Quarters List</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="page-title">Quarters Maintenance Details</h2>

    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" class="search-input" placeholder="Search by Quarter No or Name..." value="<?= htmlspecialchars($searchQuery) ?>">
                <?php if (!empty($searchQuery)): ?>
                    <a href="?" class="search-clear-icon"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
        <a href="add_maintenance.php" class="add-maint-btn"><i class="fa-solid fa-plus-circle"></i> Add Maintenance Quarter</a>
    </form>

    <div class="table-card">
        <table class="maint-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Quarter No.</th>
                    <th>Quarter Name</th>
                    <th>Quarter Location</th>
                    <th>Quarter Type</th>
                    <th>Status</th>
                    <th>Last Maintenance Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($maintenanceData)): ?>
                    <tr><td colspan="8" class="no-data">No maintenance records found.</td></tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($maintenanceData as $row): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><strong><?= htmlspecialchars($row['quarter_number']) ?></strong></td>
                            <td><?= htmlspecialchars($row['quarter_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['location_name']) ?></td>
                            <td><?= htmlspecialchars($row['quarter_type']) ?></td>
                            <td><span class="status-pill"><?= htmlspecialchars($row['maintenance_status']) ?></span></td>
                            <td><?= htmlspecialchars($row['last_maintenance_date'] ?? '-') ?></td>
                            <td>
                                <div class="action-cell">
                                    <a href="view_maintenance.php?id=<?= (int)$row['id'] ?>" class="action-btn btn-view" title="View"><i class="fa-solid fa-eye"></i></a>
                                    <a href="edit_maintenance.php?id=<?= (int)$row['id'] ?>" class="action-btn btn-edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <a href="javascript:void(0)" onclick="confirmDelete(<?= (int)$row['id'] ?>)" class="action-btn btn-delete" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleDropdown() {
        const d = document.getElementById('profileDropdown');
        if (d) d.classList.toggle('show');
    }
    window.addEventListener('click', e => {
        if (!e.target.closest('.user-profile-wrapper')) {
            const d = document.getElementById('profileDropdown');
            if (d && d.classList.contains('show')) d.classList.remove('show');
        }
    });
    function confirmDelete(id) {
        if (confirm('Delete this maintenance record?')) {
            window.location.href = '?delete=' + id;
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>