<?php
/**
 * Total Request Applications Status - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Total Request Applications Status - Sri Lanka Railways";

$userData = [
    'name' => 'Final Approver',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'FA'
];

// --- Fetch real applications from the database ---
$conn = getDBConnection();
$sql = "SELECT a.id,
               a.application_no,
               a.nic,
               a.computer_no,
               a.quarter_type,
               a.application_date,
               a.application_submitted_at,
               a.created_at,
               a.application_status,
               a.boss_status,
               a.file_status,
               a.clerk_status,
               a.branch_status,
               a.final_status,
               u.name,
               u.applicant_designation
        FROM applications AS a
        INNER JOIN users AS u ON u.nic = a.nic
        WHERE a.application_status IN ('submitted', 'pending_clerk', 'pending_super', 'approved', 'rejected', 'returned_to_boss', 'returned_to_file')
        ORDER BY a.application_date DESC, a.id DESC";

$result = $conn->query($sql);
$statusData = [];

while ($row = $result->fetch_assoc()) {
    $appStatus = $row['application_status'];
    $statusType = 'status-submit';
    $statusText = '<i class="fa-solid fa-check"></i> 1. Application Submit';

    if ($appStatus === 'returned_to_boss') {
        $statusType = 'status-boss';
        $statusText = '<i class="fa-solid fa-rotate-left"></i> Returned to Immediate Boss';
    } elseif ($appStatus === 'returned_to_file') {
        $statusType = 'status-clerk';
        $statusText = '<i class="fa-solid fa-rotate-left"></i> Returned to Personal File';
    } elseif ($appStatus === 'rejected') {
        $statusType = 'status-clerk';
        $statusText = '<i class="fa-solid fa-ban"></i> Rejected';
    } elseif ($appStatus === 'approved') {
        $statusType = 'status-submit';
        $statusText = '<i class="fa-solid fa-check"></i> Final Approved';
    } elseif ($appStatus === 'pending_super') {
        $statusType = 'status-clerk';
        $statusText = '<i class="fa-solid fa-clock"></i> 3. Deputy Superintendent (Pending)';
    } elseif ($appStatus === 'pending_clerk') {
        if ($row['boss_status'] === 'approved' && $row['file_status'] === 'approved') {
            $statusType = 'status-clerk';
            $statusText = '<i class="fa-solid fa-clock"></i> 4. Subject Clerk (Pending)';
        } elseif ($row['boss_status'] === 'approved' && $row['file_status'] === 'pending') {
            $statusType = 'status-clerk';
            $statusText = '<i class="fa-solid fa-clock"></i> 3. Personal File (Pending)';
        } else {
            $statusType = 'status-boss';
            $statusText = '<i class="fa-solid fa-clock"></i> 2. Immediate Boss (Pending)';
        }
    } else {
        if ($row['boss_status'] === 'approved') {
            $statusType = 'status-boss';
            $statusText = '<i class="fa-solid fa-check"></i> 2. Immediate Boss (Approved)';
        } else {
            $statusType = 'status-boss';
            $statusText = '<i class="fa-solid fa-clock"></i> 2. Immediate Boss (Pending)';
        }
    }

    $statusData[] = [
        'appNo' => $row['application_no'] ?? (string)$row['id'],
        'nic' => $row['nic'],
        'computerNo' => $row['computer_no'],
        'name' => $row['name'],
        'designation' => $row['applicant_designation'] ?? '',
        'subDate' => $row['application_submitted_at']
            ? date('Y-m-d', strtotime($row['application_submitted_at']))
            : ($row['created_at'] ? date('Y-m-d', strtotime($row['created_at'])) : '-'),
        'quarterType' => $row['quarter_type'],
        'reqDate' => $row['application_date'],
        'statusType' => $statusType,
        'statusText' => $statusText
    ];
}
$conn->close();

// Handle Search Filter matching SC style
$searchQuery = trim($_GET['search'] ?? '');
$displayData = $statusData;

if (!empty($searchQuery)) {
    $displayData = array_filter($displayData, function($item) use ($searchQuery) {
        return stripos($item['nic'], $searchQuery) !== false || 
               stripos($item['name'], $searchQuery) !== false || 
               stripos($item['appNo'], $searchQuery) !== false;
    });
}

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
        max-width: 1750px; 
        margin: 25px auto; 
        padding: 0 25px; 
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
        transition: color 0.2s;
    }
    .back-link:hover { 
        color: var(--primary-dark);
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
        padding: 20px 25px; 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.04); 
        margin-bottom: 25px; 
        display: flex; 
        justify-content: flex-start; 
        align-items: center; 
        flex-wrap: wrap; 
        gap: 15px; 
        border: 1px solid var(--border-color);
        width: 100%;
        box-sizing: border-box;
    }

    .search-wrapper { 
        display: flex; 
        gap: 12px; 
        align-items: center; 
        flex: 1;
        max-width: 450px;
    }
    
    .search-input-container {
        position: relative;
        flex: 1;
    }

    .search-input { 
        width: 100%;
        padding: 10px 36px 10px 14px; 
        border-radius: 8px; 
        border: 1px solid #cbd5e1; 
        font-size: 0.9rem; 
        outline: none; 
        box-sizing: border-box;
        transition: border-color 0.2s, box-shadow 0.2s;
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
        
    .search-btn { 
        border: none; 
        padding: 10px 20px; 
        border-radius: 8px; 
        font-weight: 600; 
        cursor: pointer; 
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        font-size: 0.9rem;
        text-decoration: none; 
        transition: background 0.2s; 
        background-color: var(--primary-color); 
        color: white; 
        white-space: nowrap;
    }

    .search-btn:hover { 
        background-color: var(--primary-dark); 
    }

    .management-section { 
        background: var(--card-bg); 
        padding: 25px; 
        border-radius: 12px; 
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05); 
        border: 1px solid var(--border-color); 
        border-top: 5px solid var(--primary-color); 
        margin-bottom: 40px; 
        overflow: hidden;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .data-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: center; 
        min-width: 1250px; 
    }

    .data-table th, .data-table td { 
        padding: 14px 12px; 
        border: 1px solid #e2e8f0; 
        vertical-align: middle; 
        font-size: 0.88rem; 
        text-align: center; 
    }

    .data-table th { 
        background-color: #f8fafc; 
        color: var(--text-main); 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 0.78rem;
        letter-spacing: 0.5px; 
    }

    .data-table tbody tr {
        transition: background-color 0.2s;
    }

    .data-table tbody tr:hover { 
        background-color: #f8fafc; 
    }

    .app-no { color: var(--text-main); font-weight: 700; font-family: monospace; }
    .applicant-nic { color: #4b5563; font-family: monospace; }
    .computer-no { color: #4b5563; font-family: monospace; }
    .badge-type { color: #4b5563; }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .status-submit { background-color: #e6f4ea; color: #137333; border: 1px solid #ceead6; }
    .status-boss { background-color: #fef7e0; color: #b06000; border: 1px solid #fef0cd; }
    .status-clerk { background-color: #f1f3f4; color: #5f6368; border: 1px solid #e8eaed; }

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
            padding: 0 12px;
        }
        .search-card {
            flex-direction: column;
            align-items: stretch;
        }
        .search-wrapper {
            max-width: 100%;
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

    <h2 class="page-title">Total Request Applications Status</h2>

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
    </form>

    <div class="management-section">
        <div class="table-responsive">
            <table class="data-table" id="requestsTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Application No</th>
                        <th>Applicant NIC</th>
                        <th>Computer No</th>
                        <th>Applicant Name</th>
                        <th>Designation</th>
                        <th>Application Submitted Date</th>
                        <th>Request Quarter Type</th>
                        <th>Request Date</th>
                        <th>Current Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($displayData)): ?>
                        <tr><td colspan="10" style="text-align: center; color: #64748b; padding: 25px;">No applications found.</td></tr>
                    <?php else: ?>
                        <?php $counter = 1; foreach ($displayData as $row): ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><span class="app-no"><?php echo htmlspecialchars($row['appNo']); ?></span></td>
                                <td><span class="applicant-nic"><?php echo htmlspecialchars($row['nic']); ?></span></td>
                                <td><span class="computer-no"><?php echo htmlspecialchars($row['computerNo']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                <td><?php echo htmlspecialchars($row['subDate']); ?></td>
                                <td><span class="badge-type"><?php echo htmlspecialchars($row['quarterType']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['reqDate']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo htmlspecialchars($row['statusType']); ?>">
                                        <?php echo $row['statusText']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
        if (dropdown) {
            dropdown.classList.toggle('show');
        }
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
        if (dropdown) {
            dropdown.classList.remove('show');
        }
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