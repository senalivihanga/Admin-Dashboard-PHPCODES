<?php
/**
 * Allocated Quarters List - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Allocated Quarters List - Sri Lanka Railways Admin";

// Use the central database connection
$conn = getDBConnection();

// Initialize $quarters array to prevent undefined variable / count() error
$quarters = [];

// Handle Delete Request with System Message Generation
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM allocated_quarters WHERE id = ? AND source = 'normal'");
    $stmt->bind_param("i", $deleteId);
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: allocated_quarters.php?msg=deleted");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: allocated_quarters.php?msg=error");
        exit();
    }
}

// Handle Search Filter & Order by ASC so new records appear at the bottom
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $sql = "SELECT * FROM allocated_quarters WHERE source = 'normal' AND (nic LIKE ? OR computer_no LIKE ? OR quarter_no LIKE ? OR quarter_name LIKE ? OR quarter_location LIKE ? OR resident_name LIKE ? OR designation LIKE ? OR workplace LIKE ?) ORDER BY id ASC";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("ssssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $quarters = $result->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM allocated_quarters WHERE source = 'normal' ORDER BY id ASC");
    if ($result) {
        $quarters = $result->fetch_all(MYSQLI_ASSOC);
    }
}
$conn->close();

// System Message Handling
$message = '';
$messageType = 'success';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') {
        $message = "Record deleted successfully!";
        $messageType = 'success';
    } elseif ($_GET['msg'] === 'error') {
        $message = "Error: Failed to delete the record. Please try again.";
        $messageType = 'error';
    }
}

// Include Admin Header
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
        max-width: 400px;
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

    .search-btn, .add-btn { 
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
    
    .add-btn { 
        background-color: #10b981; 
        color: white; 
        white-space: nowrap; 
    }
    
    .add-btn:hover { 
        background-color: #059669; 
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

    .allocated-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: center; 
        font-size: 0.9rem;
        white-space: nowrap;
        min-width: 1300px;
    }
    
    .allocated-table th, .allocated-table td { 
        padding: 14px 16px; 
        border: 1px solid var(--border-color);
        vertical-align: middle; 
    }
    
    .allocated-table th { 
        background-color: #f8fafc; 
        color: var(--text-main); 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    
    .allocated-table tr:hover {
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
    
    .btn-edit { background-color: #2563eb; }
    .btn-edit:hover { background-color: #1d4ed8; }
    
    .btn-delete { background-color: #dc2626; }
    .btn-delete:hover { background-color: #b91c1c; }

    /* Custom Delete Confirmation Modal Styles */
    .custom-msg-box { 
        position: fixed; 
        inset: 0; 
        background: rgba(15, 23, 42, 0.55); 
        display: none; 
        align-items: center; 
        justify-content: center; 
        z-index: 999; 
        padding: 15px; 
    }
    .custom-msg-card { 
        background: #fff; 
        border-radius: 16px; 
        width: min(92vw, 400px); 
        padding: 20px; 
        box-shadow: 0 12px 30px rgba(0,0,0,0.18); 
        text-align: center; 
    }
    .custom-msg-card p { 
        color: #1f2937; 
        font-size: 1rem; 
        line-height: 1.5; 
        margin-bottom: 15px; 
    }
    .custom-msg-card i.modal-icon { 
        display: block; 
        color: #dc2626; 
        font-size: 1.8rem; 
        margin-bottom: 8px; 
    }
    .msg-btn-group { 
        display: flex; 
        justify-content: center; 
        gap: 10px; 
    }
    .msg-btn { 
        border: none; 
        border-radius: 8px; 
        padding: 8px 16px; 
        font-weight: 700; 
        cursor: pointer; 
        font-size: 0.9rem; 
        text-decoration: none; 
        display: inline-block; 
    }
    .msg-btn-yes { background: #dc2626; color: white; }
    .msg-btn-no { background: #e2e8f0; color: #1f2937; }

    /* Toast Notification Styles */
    .system-toast { 
        position: fixed; 
        right: 20px; 
        top: 20px; 
        padding: 12px 18px; 
        border-radius: 10px; 
        display: none; 
        z-index: 1000; 
        font-weight: 700; 
        align-items: center; 
        gap: 10px; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        font-size: 0.9rem;
    }
    .system-toast.success { background: #f0fdf4; color: #166534; border: 1px solid #86efac; border-left: 6px solid #22c55e; }
    .system-toast.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; border-left: 6px solid #ef4444; }

    @media (max-width: 768px) {
        .approval-container { padding: 10px 15px; }
        .approval-container h2 { font-size: 1.4rem; }
        .search-card { flex-direction: column; align-items: stretch; }
        .search-wrapper { width: 100%; }
        .search-input-container { max-width: 100%; }
        .search-btn, .add-btn { width: 100%; justify-content: center; }
    }
</style>

<!-- Custom Delete Confirmation Modal -->
<div class="custom-msg-box" id="deleteModal" role="dialog" aria-modal="true">
    <div class="custom-msg-card">
        <i class="fa-solid fa-triangle-exclamation modal-icon"></i>
        <p>Are you sure you want to delete this allocated quarter record?</p>
        <div class="msg-btn-group">
            <a href="#" id="confirmDeleteBtn" class="msg-btn msg-btn-yes">Delete</a>
            <button type="button" class="msg-btn msg-btn-no" onclick="closeDeleteModal()">Cancel</button>
        </div>
    </div>
</div>

<!-- System Toast Notification -->
<?php if (!empty($message)): ?>
<div id="systemToast" class="system-toast <?php echo $messageType; ?>" style="display: flex;">
    <?php if ($messageType === 'success'): ?>
        <i class="fa-solid fa-circle-check" style="color: #22c55e; font-size: 1.1rem;"></i>
    <?php else: ?>
        <i class="fa-solid fa-circle-xmark" style="color: #ef4444; font-size: 1.1rem;"></i>
    <?php endif; ?>
    <span><?php echo htmlspecialchars($message); ?></span>
</div>
<script>
    setTimeout(() => { 
        const toast = document.getElementById('systemToast');
        if(toast) { 
            toast.style.display = 'none'; 
        }
    }, 4000);
</script>
<?php endif; ?>

<main class="approval-container">
    <main class="approval-container">
    <a href="<?php echo baseUrl(roleHomePath(currentRoleId() ?: 2)); ?>" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>

    <div class="approval-header">
        <h2>Allocated Quarters List</h2>
    </div>

    <!-- Search & Add Card Box -->
    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" class="search-input" placeholder="Search by NIC, Computer No or Quarter No..." value="<?php echo htmlspecialchars($search); ?>">
                <?php if (!empty($search)): ?>
                    <a href="?" class="search-clear-icon" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
        <a href="add_allocated_quarter.php" class="add-btn"><i class="fa-solid fa-plus"></i> Add New Allocation</a>
    </form>

    <!-- Table Card Box -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="allocated-table" id="allocatedTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIC</th>
                        <th>Computer No.</th>
                        <th>Quarter No.</th>
                        <th>Quarter Name</th>
                        <th>Quarter Location</th>
                        <th>Quarter Type</th>
                        <th>Resident Name</th>
                        <th>Designation</th>
                        <th>Workplace</th>
                        <th>Allocated Date</th>
                        <th>Other</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($quarters) > 0): ?>
                        <?php $no = 1; foreach ($quarters as $row): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nic']); ?></td>
                                <td><?php echo htmlspecialchars($row['computer_no']); ?></td>
                                <td><?php echo htmlspecialchars($row['quarter_no'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['quarter_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['quarter_location'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['quarter_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['resident_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                <td><?php echo htmlspecialchars($row['workplace']); ?></td>
                                <td><?php echo htmlspecialchars($row['allocated_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['other'] ?? 'None'); ?></td>
                                <td>
                                    <div class="action-cell">
                                        <a href="view_allocated_quarter.php?id=<?php echo $row['id']; ?>" class="action-btn btn-view" title="View Details"><i class="fa-solid fa-eye"></i></a>
                                        <a href="edit_allocated_quarter.php?id=<?php echo $row['id']; ?>" class="action-btn btn-edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                        <button type="button" class="action-btn btn-delete" onclick="openDeleteModal('<?php echo $row['id']; ?>')" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13" style="text-align: center; padding: 30px; color: #64748b;">No allocated quarters found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- JavaScript for Custom Delete Modal Handling -->
<script>
    function openDeleteModal(id) {
        const deleteUrl = `allocated_quarters.php?delete_id=${id}`;
        document.getElementById('confirmDeleteBtn').setAttribute('href', deleteUrl);
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }
</script>

<?php
// Include Admin Footer
require_once __DIR__ . '/../layouts/admin_footer.php';
?>