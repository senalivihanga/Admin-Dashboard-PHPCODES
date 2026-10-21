<?php
/**
 * Allocated Quarters List - Final Approver - Sri Lanka Railways
 * -------------------------------------------------------------
 * Reads real allocation records from `allocated_quarters`, joined
 * with `quarters` (for quarter details) and `users` (for the resident's
 * full name and designation, in case the stored copy is blank).
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Allocated Quarters List - Sri Lanka Railways Admin";

$userData = [
    'name'     => $_SESSION['user_name'] ?? 'Final Approver',
    'role'     => 'Sri Lanka Railways - AOT',
    'initials' => 'FA'
];

$searchQuery = trim($_GET['search'] ?? '');

$conn = getDBConnection();

// -------------------------------------------------------------
// Fetch real allocations (LEFT JOIN so we still show rows if the
// quarter or user has been removed for some reason)
// -------------------------------------------------------------
$sql = "SELECT
            aq.id,
            aq.nic,
            aq.computer_no,
            aq.quarter_no,
            aq.quarter_name,
            aq.quarter_location,
            aq.quarter_type,
            COALESCE(NULLIF(aq.resident_name, ''), u.name, '-')              AS resident_name,
            COALESCE(NULLIF(aq.designation, ''),  u.applicant_designation, '-') AS designation,
            COALESCE(NULLIF(aq.workplace, ''),    u.applicant_current_work_station, '-') AS workplace,
            aq.allocated_date,
            COALESCE(NULLIF(aq.other, ''), 'None')                            AS other
        FROM allocated_quarters AS aq
   LEFT JOIN users    AS u ON u.nic = aq.nic
   LEFT JOIN quarters AS q ON q.quarter_number = aq.quarter_no
                         AND q.location_name  = aq.quarter_location";

if ($searchQuery !== '') {
    $sql .= " WHERE (aq.nic LIKE ?
                 OR aq.computer_no LIKE ?
                 OR aq.quarter_no LIKE ?
                 OR aq.quarter_name LIKE ?
                 OR aq.quarter_location LIKE ?
                 OR aq.resident_name LIKE ?
                 OR u.name LIKE ?)";
    $stmt = $conn->prepare($sql . ' ORDER BY aq.id DESC');
    $term = '%' . $searchQuery . '%';
    $stmt->bind_param('sssssss', $term, $term, $term, $term, $term, $term, $term);
} else {
    $stmt = $conn->prepare($sql . ' ORDER BY aq.id DESC');
}

$stmt->execute();
$allocatedData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    :root {
        --primary-color: #7A1C1C;
        --primary-dark: #571a1a;
        --danger-color: #ef4444;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --text-main: #1e293b;
        --border-color: #e2e8f0;
    }

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-y: auto;
        background-color: var(--bg-color);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container { 
        max-width: 1650px; 
        margin: 20px auto; 
        padding: 0 20px; 
        width: 100%;
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

    .user-profile-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
    }

    .user-profile-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--primary-color);
        color: #ffffff;
        padding: 6px 14px;
        border-radius: 30px;
        cursor: pointer;
        user-select: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .profile-dropdown {
        display: none;
        position: absolute;
        left: 0;
        top: 105%;
        background: #ffffff;
        min-width: 220px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
        z-index: 100;
        border: 1px solid var(--border-color);
    }

    .profile-dropdown.show {
        display: block;
    }

    .profile-dropdown a {
        color: var(--text-main);
        padding: 10px 15px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        transition: background 0.2s;
    }

    .profile-dropdown a:hover {
        background: #f1f5f9;
        color: var(--primary-color);
    }

    .profile-dropdown a.logout-btn {
        color: var(--danger-color);
    }

    .profile-dropdown a.logout-btn:hover {
        background: #fee2e2;
    }

    .page-title { 
        text-align: center; 
        color: var(--primary-color); 
        font-size: 1.8rem; 
        margin-bottom: 25px; 
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
        margin-bottom: 25px; 
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
        color: white;
    }
    
    .search-btn { 
        background-color: var(--primary-color); 
    }
    .search-btn:hover { 
        background-color: var(--primary-dark); 
    }

    .print-btn { 
        background-color: #0284c7; 
    }
    .print-btn:hover { 
        background-color: #0369a1; 
    }

    .table-card { 
        background: var(--card-bg); 
        padding: 25px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid var(--primary-color); 
        overflow-x: auto; 
        position: relative; 
    }

    .allocated-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: left; 
        min-width: 1400px; 
    }

    .allocated-table th, .allocated-table td { 
        padding: 12px 10px; 
        border: 1px solid #cbd5e1; 
        vertical-align: middle; 
        font-size: 0.85rem; 
        text-align: center; 
    }

    .allocated-table th { 
        background-color: #f8fafc; 
        color: var(--text-main); 
        font-weight: 700; 
        text-transform: uppercase;
        font-size: 0.8rem;
    }

    .allocated-table td { 
        color: #334155; 
        word-break: break-word;
    }

    .custom-modal {
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
    }

    .custom-modal-content {
        background-color: #fff;
        padding: 30px;
        border-radius: 12px;
        width: 100%;
        max-width: 380px;
        text-align: center;
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }

    .custom-modal-content i {
        font-size: 2.8rem;
        color: var(--primary-color);
        margin-bottom: 15px;
    }

    .custom-modal-content h3 {
        color: var(--text-main);
        margin-bottom: 10px;
        font-size: 1.3rem;
    }

    .custom-modal-content p {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 20px;
    }

    .modal-action-btns {
        display: flex;
        justify-content: center;
        gap: 12px;
    }

    .btn-modal-confirm, .btn-modal-cancel {
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        color: white;
    }

    .btn-modal-confirm { background-color: var(--danger-color); }
    .btn-modal-cancel { background-color: #64748b; }

    @media (max-width: 768px) {
        .container {
            margin: 15px auto;
            padding: 0 10px;
        }
        .search-card {
            flex-direction: column;
            align-items: stretch;
        }
        .search-wrapper {
            max-width: 100%;
        }
        .search-btn, .print-btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media print {
        @page { size: landscape; margin: 10mm; }
        body, html { background-color: #ffffff !important; }
        .back-link, .search-card, .user-profile-wrapper, .print-btn {
            display: none !important;
        }
        .table-card {
            box-shadow: none !important; border: none !important; padding: 0 !important;
        }
        .allocated-table {
            width: 100% !important; min-width: 0 !important; font-size: 8.5pt;
        }
        .allocated-table th, .allocated-table td {
            padding: 6px 4px !important;
            border: 1px solid #333 !important;
        }
    }
</style>

<div class="container">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
        <a href="FA_dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
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
                <a href="FA_dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="page-title">Allocated Quarters List</h2>

    <!-- PHP GET Search Form -->
    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" class="search-input"
                       placeholder="Search by NIC, Computer No. or Quarter No..."
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                <?php if (!empty($searchQuery)): ?>
                    <a href="?" class="search-clear-icon" title="Clear Search">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
        </div>
        <button type="button" class="print-btn" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Print / Save as PDF
        </button>
    </form>

    <div class="table-card">
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
                </tr>
            </thead>
            <tbody>
                <?php if (empty($allocatedData)): ?>
                    <tr>
                        <td colspan="12" style="text-align: center; color: #64748b; padding: 25px;">
                            <?php echo $searchQuery !== ''
                                ? 'No allocations match your search.'
                                : 'No allocated quarters records found.'; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($allocatedData as $row): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['nic']); ?></td>
                            <td><?php echo htmlspecialchars($row['computer_no']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['quarter_no']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['quarter_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['quarter_location']); ?></td>
                            <td><?php echo htmlspecialchars($row['quarter_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['resident_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['designation']); ?></td>
                            <td><?php echo htmlspecialchars($row['workplace']); ?></td>
                            <td><?php echo htmlspecialchars($row['allocated_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['other']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="logoutModal" class="custom-modal">
    <div class="custom-modal-content">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to log out from the Final Approver portal?</p>
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

    window.addEventListener('click', function(e) {
        if (!e.target.closest('.user-profile-wrapper')) {
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown && dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        }
    });

    function openLogoutModal(event) {
        event.preventDefault();
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) dropdown.classList.remove('show');
        document.getElementById('logoutModal').style.display = 'flex';
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    function executeLogout() {
        window.location.href = "login.php";
    }
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>