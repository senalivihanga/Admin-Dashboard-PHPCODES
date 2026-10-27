<?php
/**
 * Quarters Maintenance - Sri Lanka Railways (database-backed)
 * ------------------------------------------------------------
 * Mirrors quarters_list.php structure.
 *
 * Actions:
 *   - View   → view_maintenance.php?id=...
 *   - Edit   → edit_maintenance.php?id=...
 *   - Mark as Available → removes the row from `maintenance` AND
 *                         sets the linked quarter's status to 'Available'
 *   - Delete → removes the row from `maintenance` AND
 *              sets the linked quarter's status to 'Available'
 *              (kept identical to Mark-as-Available so the quarter is never lost)
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Quarters Maintenance - Sri Lanka Railways";

$userData = [
    'name'     => 'Subject Clerk',
    'role'     => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$conn = getDBConnection();

// Helper: fetch the quarter_number/location_name for a maintenance id
function fetchMaintLink($conn, int $id): ?array {
    $stmt = $conn->prepare("SELECT quarter_number, location_name FROM maintenance WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

// --------------------------------------------------------------
// Delete (also flips quarter to Available)
// --------------------------------------------------------------
if (isset($_GET['delete']) && $_GET['delete'] !== '') {
    $deleteId = (int) $_GET['delete'];
    $conn->begin_transaction();
    try {
        $link = fetchMaintLink($conn, $deleteId);
        if ($link) {
            $del = $conn->prepare("DELETE FROM maintenance WHERE id = ?");
            $del->bind_param("i", $deleteId);
            $del->execute();
            $del->close();

            $upd = $conn->prepare(
                "UPDATE quarters SET quarter_status = 'Available'
                  WHERE quarter_number = ? AND location_name = ?"
            );
            $upd->bind_param("ss", $link['quarter_number'], $link['location_name']);
            $upd->execute();
            $upd->close();
        }
        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
    }
    $conn->close();
    header('Location: ' . baseUrl('admin/maintenance.php?msg=deleted'));
    exit;
}

// --------------------------------------------------------------
// Mark as Available
// --------------------------------------------------------------
if (isset($_GET['mark_available']) && $_GET['mark_available'] !== '') {
    $rowId = (int) $_GET['mark_available'];
    $conn->begin_transaction();
    try {
        $link = fetchMaintLink($conn, $rowId);
        if ($link) {
            $del = $conn->prepare("DELETE FROM maintenance WHERE id = ?");
            $del->bind_param("i", $rowId);
            $del->execute();
            $del->close();

            $upd = $conn->prepare(
                "UPDATE quarters SET quarter_status = 'Available'
                  WHERE quarter_number = ? AND location_name = ?"
            );
            $upd->bind_param("ss", $link['quarter_number'], $link['location_name']);
            $upd->execute();
            $upd->close();
        }
        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
    }
    $conn->close();
    header('Location: ' . baseUrl('admin/maintenance.php?msg=available'));
    exit;
}

// --------------------------------------------------------------
// Fetch list (with search)
// --------------------------------------------------------------
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

// Flash
$flash = '';
switch ($_GET['msg'] ?? '') {
    case 'deleted':   $flash = 'Maintenance record deleted successfully.'; break;
    case 'added':     $flash = 'Maintenance record added successfully.';   break;
    case 'updated':   $flash = 'Maintenance record updated successfully.'; break;
    case 'available': $flash = 'Quarter marked as Available and removed from the maintenance list.'; break;
}

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    html, body {
        height: 100%; margin: 0; padding: 0; overflow-y: auto;
        background-color: #f8fafc;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .container { max-width: 1400px; margin: 20px auto; padding: 0 20px; width: 100%; box-sizing: border-box; }
    .back-link {
        display: inline-flex; align-items: center; gap: 8px; color: #7A1C1C;
        text-decoration: none; font-weight: 600; margin-bottom: 20px; font-size: 0.95rem;
    }
    .back-link:hover { text-decoration: underline; }
    .user-profile-wrapper { position: relative; display: inline-block; margin-bottom: 20px; }
    .user-profile-badge {
        display: flex; align-items: center; gap: 10px; background: #7A1C1C;
        color: #ffffff; padding: 6px 14px; border-radius: 30px; cursor: pointer;
        user-select: none; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .profile-dropdown {
        display: none; position: absolute; left: 0; top: 105%; background: #ffffff;
        min-width: 220px; box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        border-radius: 8px; overflow: hidden; z-index: 100; border: 1px solid #e2e8f0;
    }
    .profile-dropdown.show { display: block; }
    .profile-dropdown a {
        color: #1e293b; padding: 10px 15px; text-decoration: none;
        display: flex; align-items: center; gap: 8px; font-size: 0.85rem;
    }
    .profile-dropdown a:hover { background: #f1f5f9; color: #7A1C1C; }
    .profile-dropdown a.logout-btn { color: #ef4444; }
    .profile-dropdown a.logout-btn:hover { background: #fee2e2; }
    .page-title {
        text-align: center; color: #7A1C1C; font-size: 1.8rem; margin-bottom: 25px;
        text-transform: uppercase; letter-spacing: 1px;
        text-decoration: underline; text-underline-offset: 8px; font-weight: 700;
    }
    .section-header-bar {
        background: #ffffff; padding: 18px 25px; border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.04); border: 1px solid #e2e8f0;
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 25px; flex-wrap: wrap; gap: 20px; box-sizing: border-box;
    }
    .search-wrapper { display: flex; gap: 10px; align-items: center; flex: 1; min-width: 300px; max-width: 450px; }
    .search-field { position: relative; flex: 1; min-width: 0; }
    .search-input {
        padding: 9px 14px; padding-right: 40px; border-radius: 8px;
        border: 1px solid #cbd5e1; font-size: 0.9rem; width: 100%;
        box-sizing: border-box; outline: none; background-color: #ffffff;
        transition: all 0.2s;
    }
    .search-input:focus { border-color: #7A1C1C; box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1); }
    .search-btn {
        background-color: #7A1C1C; color: white; border: none;
        padding: 9px 18px; border-radius: 8px; font-weight: 600;
        cursor: pointer; display: flex; align-items: center; gap: 6px;
        transition: background 0.2s; white-space: nowrap; font-size: 0.9rem;
    }
    .search-btn:hover { background-color: #571a1a; }
    .search-clear {
        position: absolute; top: 50%; right: 12px; transform: translateY(-50%);
        color: #7A1C1C; font-size: 1rem; line-height: 1; padding: 0;
        border: 0; background: transparent; cursor: pointer;
    }
    .search-clear:hover { color: #571a1a; }
    .btn-group { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
    .add-quarter-btn {
        background-color: #10b981; color: white; padding: 9px 16px; border-radius: 8px;
        font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer;
        display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
        transition: background 0.2s;
    }
    .add-quarter-btn:hover { background-color: #059669; }
    .table-card {
        background: #ffffff; padding: 25px; border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        border-top: 5px solid #7A1C1C; overflow: hidden; position: relative;
        border: 1px solid #e2e8f0; box-sizing: border-box;
    }
    .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .maint-table {
        width: 100%; border-collapse: collapse; text-align: left; min-width: 1100px;
    }
    .maint-table th, .maint-table td {
        padding: 14px 12px; border: 1px solid #e2e8f0;
        vertical-align: middle; font-size: 0.85rem; text-align: center;
    }
    .maint-table th {
        background-color: #f8fafc; color: #1e293b; font-weight: 700;
        text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;
    }
    .maint-table td { color: #4b5563; font-weight: normal; }
    .maint-table tbody tr { transition: background-color 0.15s ease; }
    .maint-table tbody tr:hover { background-color: #f1f5f9; }
    .status-pill {
        display: inline-block; padding: 4px 12px; border-radius: 20px;
        font-size: 0.75rem; font-weight: 600;
        background: #fef3c7; color: #d97706; border: 1px solid #fde68a;
    }
    .action-cell { display: flex; justify-content: center; align-items: center; gap: 6px; }
    .action-btn {
        border: none; width: 32px; height: 32px; border-radius: 6px; font-weight: 600;
        cursor: pointer; font-size: 0.85rem; text-decoration: none;
        display: inline-flex; align-items: center; justify-content: center;
        transition: background-color 0.2s; color: white;
    }
    .btn-view { background-color: #8b5cf6; }
    .btn-view:hover { background-color: #7c3aed; }
    .btn-edit { background-color: #3b82f6; }
    .btn-edit:hover { background-color: #2563eb; }
    .btn-available { background-color: #10b981; }
    .btn-available:hover { background-color: #059669; }
    .btn-delete { background-color: #dc2626; }
    .btn-delete:hover { background-color: #b91c1c; }
    .custom-msg-box {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;
    }
    .msg-content {
        background: white; padding: 25px; border-radius: 10px; text-align: center;
        width: 350px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .msg-content p {
        margin-bottom: 20px; font-size: 1rem; color: #334155; font-weight: 600;
    }
    .msg-btns { display: flex; justify-content: center; gap: 10px; }
    .msg-btn { padding: 8px 15px; border: none; border-radius: 5px; font-weight: 600; cursor: pointer; }
    .msg-btn-yes { background-color: #10b981; color: white; }
    .msg-btn-cancel { background-color: #64748b; color: white; }
    .msg-btn-del { background-color: #dc2626; color: white; }
    .system-toast {
        position: fixed; right: 24px; top: 24px;
        background: #f0fdf4; color: #166534; border: 1px solid #86efac;
        border-left: 6px solid #22c55e; padding: 14px 18px; border-radius: 12px;
        box-shadow: 0 12px 30px rgba(34, 197, 94, 0.18);
        display: none; z-index: 1100; font-weight: 700;
        align-items: center; gap: 10px; max-width: 380px; line-height: 1.4;
    }
    .system-toast i { color: #16a34a; font-size: 1.1rem; }
    .custom-modal {
        display: none; position: fixed; z-index: 1000; left: 0; top: 0;
        width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);
        justify-content: center; align-items: center; padding: 15px;
    }
    .custom-modal-content {
        background-color: #fff; padding: 30px; border-radius: 12px;
        width: 100%; max-width: 380px; text-align: center;
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    .custom-modal-content i { font-size: 2.8rem; color: #7A1C1C; margin-bottom: 15px; }
    .custom-modal-content h3 { color: #1e293b; margin-bottom: 10px; font-size: 1.3rem; }
    .custom-modal-content p { color: #64748b; font-size: 0.95rem; margin-bottom: 20px; }
    .modal-action-btns { display: flex; justify-content: center; gap: 12px; }
    .btn-modal-confirm, .btn-modal-cancel {
        padding: 8px 15px; border: none; border-radius: 5px;
        font-weight: 600; cursor: pointer; color: white;
    }
    .btn-modal-confirm { background-color: #ef4444; }
    .btn-modal-cancel  { background-color: #64748b; }
    @media (max-width: 768px) {
        .container { padding: 10px 15px; }
        .section-header-bar { flex-direction: column; align-items: stretch; }
        .search-wrapper { max-width: 100%; }
        .btn-group { width: 100%; justify-content: stretch; }
        .btn-group > * { flex: 1; justify-content: center; }
    }
</style>

<?php if ($flash): ?>
    <div id="systemToast" class="system-toast" style="display: flex;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($flash) ?>
    </div>
    <script>
        setTimeout(() => {
            const t = document.getElementById('systemToast');
            if (t) t.style.display = 'none';
        }, 3000);
    </script>
<?php endif; ?>

<div class="container">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
        <a href="quarters_list.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Quarters List</a>
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
                <a href="quarters_list.php"><i class="fa-solid fa-arrow-left"></i> Back to Quarters List</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="page-title">Quarters Maintenance Details</h2>

    <div class="section-header-bar">
        <form method="GET" action="" class="search-wrapper" style="margin:0;">
            <div class="search-field">
                <input type="text" name="search" id="searchInput" class="search-input"
                       placeholder="Search by Quarter No, Name or Location..."
                       value="<?= htmlspecialchars($searchQuery) ?>">
                <?php if ($searchQuery !== ''): ?>
                    <a href="?" class="search-clear" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </form>

        <div class="btn-group">
            <a href="add_maintenance.php" class="add-quarter-btn"><i class="fa-solid fa-plus-circle"></i> Add Maintenance Quarter</a>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
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
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px; color: #64748b;">
                                <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                                No maintenance records found.
                            </td>
                        </tr>
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
                                        <button type="button" class="action-btn btn-available" onclick="promptAvailable(<?= (int)$row['id'] ?>)" title="Mark as Available"><i class="fa-solid fa-circle-check"></i></button>
                                        <button type="button" class="action-btn btn-delete" onclick="promptDelete(<?= (int)$row['id'] ?>)" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Mark as Available Modal -->
<div class="custom-msg-box" id="availableModal">
    <div class="msg-content">
        <p>
            <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 1.5rem; margin-bottom: 10px; display:block;"></i>
            Mark this quarter as <strong>Available</strong>?<br>
            <span style="font-size: 0.85rem; font-weight: 500; color: #64748b;">
                The record will be removed from the maintenance list and the quarter status will change to <strong>Available</strong>.
            </span>
        </p>
        <div class="msg-btns">
            <button type="button" class="msg-btn msg-btn-yes" id="confirmAvailableBtn">Yes, Mark Available</button>
            <button type="button" class="msg-btn msg-btn-cancel" onclick="closeAvailableModal()">Cancel</button>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="custom-msg-box" id="deleteModal">
    <div class="msg-content">
        <p>
            <i class="fa-solid fa-triangle-exclamation" style="color: #dc2626; font-size: 1.5rem; margin-bottom: 10px; display:block;"></i>
            Are you sure you want to delete this maintenance record?<br>
            <span style="font-size: 0.85rem; font-weight: 500; color: #64748b;">
                The quarter will be marked <strong>Available</strong>.
            </span>
        </p>
        <div class="msg-btns">
            <button type="button" class="msg-btn msg-btn-del" id="confirmDeleteBtn">Yes, Delete</button>
            <button type="button" class="msg-btn msg-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
        </div>
    </div>
</div>

<!-- Logout modal (unchanged) -->
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
    function toggleDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) dropdown.classList.toggle('show');
    }
    window.addEventListener('click', function (e) {
        if (!e.target.closest('.user-profile-wrapper')) {
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown && dropdown.classList.contains('show')) dropdown.classList.remove('show');
        }
    });
    function openLogoutModal(event) {
        event.preventDefault();
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) dropdown.classList.remove('show');
        document.getElementById('logoutModal').style.display = 'flex';
    }
    function closeLogoutModal() { document.getElementById('logoutModal').style.display = 'none'; }
    function executeLogout() { window.location.href = "<?php echo baseUrl('logout'); ?>"; }

    // Mark as Available
    let availableTargetId = null;
    function promptAvailable(id) {
        availableTargetId = id;
        document.getElementById('availableModal').style.display = 'flex';
    }
    function closeAvailableModal() {
        availableTargetId = null;
        document.getElementById('availableModal').style.display = 'none';
    }
    document.getElementById('confirmAvailableBtn').addEventListener('click', function () {
        if (availableTargetId !== null) {
            window.location.href = '?mark_available=' + availableTargetId;
        }
    });

    // Delete
    let deleteTargetId = null;
    function promptDelete(id) {
        deleteTargetId = id;
        document.getElementById('deleteModal').style.display = 'flex';
    }
    function closeDeleteModal() {
        deleteTargetId = null;
        document.getElementById('deleteModal').style.display = 'none';
    }
    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        if (deleteTargetId !== null) {
            window.location.href = '?delete=' + deleteTargetId;
        }
    });
</script>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>