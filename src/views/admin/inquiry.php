<?php
/**
 * My Inquiries & Complaints - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "My Inquiries & Complaints - Sri Lanka Railways";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

// --- Fetch complaints from the database ---
$conn = getDBConnection();

// Issue type metadata map (colour class + icon) keyed by related_issue value
$issueMeta = [
    'Water Supply'        => ['class' => 'issue-water',       'icon' => 'fa-droplet'],
    'water'               => ['class' => 'issue-water',       'icon' => 'fa-droplet'],
    'Electricity / Light' => ['class' => 'issue-electricity', 'icon' => 'fa-bolt'],
    'electricity'         => ['class' => 'issue-electricity', 'icon' => 'fa-bolt'],
    'Quarter Maintenance' => ['class' => 'issue-maintenance', 'icon' => 'fa-house-chimney-crack'],
    'maintenance'         => ['class' => 'issue-maintenance', 'icon' => 'fa-house-chimney-crack'],
];

// Status metadata map (colour class + icon) keyed by complaints.status value
$statusMeta = [
    'Pending'     => ['class' => 'status-pending',  'icon' => 'fa-clock'],
    'In Progress' => ['class' => 'status-progress', 'icon' => 'fa-spinner fa-spin'],
    'Resolved'    => ['class' => 'status-resolved', 'icon' => 'fa-check'],
];

$searchQuery = trim($_GET['search'] ?? '');

$sql = "SELECT c.id,
               c.user_id AS nic,
               u.name,
               c.complaints AS complaint,
               c.related_issue AS issueType,
               DATE(c.created_at) AS date,
               c.status
        FROM complaints AS c
        LEFT JOIN users AS u ON u.nic = c.user_id";

if ($searchQuery !== '') {
    $sql .= " WHERE c.user_id LIKE ? OR u.name LIKE ?";
    $searchTerm = '%' . $searchQuery . '%';
    $stmt = $conn->prepare($sql . ' ORDER BY c.created_at DESC, c.id DESC');
    $stmt->bind_param('ss', $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare($sql . ' ORDER BY c.created_at DESC, c.id DESC');
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Build the display array with resolved class / icon metadata
$displayData = [];
foreach ($rows as $row) {
    $issueKey = $row['issueType'] ?? '';
    $issue = $issueMeta[$issueKey] ?? ['class' => 'issue-other', 'icon' => 'fa-circle-info'];

    $statusKey = $row['status'] ?? 'Pending';
    $status = $statusMeta[$statusKey] ?? $statusMeta['Pending'];

    $displayData[] = [
        'nic'         => $row['nic'] ?? '',
        'name'        => $row['name'] ?? 'Unknown',
        'complaint'   => $row['complaint'] ?? '',
        'issueType'   => $issueKey !== '' ? $issueKey : 'Other',
        'issueClass'  => $issue['class'],
        'issueIcon'   => $issue['icon'],
        'date'        => $row['date'] ?? '-',
        'status'      => $statusKey,
        'statusClass' => $status['class'],
        'statusIcon'  => $status['icon']
    ];
}

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

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-y: auto;
        background-color: var(--bg-color);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container { 
        max-width: 1400px; 
        margin: 20px auto; 
        padding: 0 20px; 
        width: 100%;
        box-sizing: border-box;
    }

    .back-link { 
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        color: var(--primary-color); 
        text-decoration: none; 
        font-weight: 600; 
        margin-bottom: 20px; 
        font-size: 0.85rem;
        transition: color 0.2s; 
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

    /* Title style matching appli-approve-IB exactly[cite: 21] */
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
    
    /* Search card matching appli-approve-IB style[cite: 21] */
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
        background-color: #ffffff;
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

    .management-section { 
        background: var(--card-bg); 
        padding: 25px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border: 1px solid var(--border-color);
        border-top: 5px solid var(--primary-color); 
        margin-bottom: 40px; 
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .data-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: center; 
        min-width: 1200px; 
    }
    .data-table th, .data-table td { 
        padding: 14px 16px; 
        border: 1px solid var(--border-color); 
        vertical-align: middle; 
        font-size: 0.85rem; 
    }
    .data-table th { 
        background-color: #f8fafc; 
        color: var(--text-main); 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 0.8rem; 
        letter-spacing: 0.5px; 
        text-align: center; 
    }
    .data-table td { 
        color: #334155; 
        text-align: center; 
    }
    .data-table tbody tr { 
        transition: background-color 0.15s ease; 
    }
    .data-table tbody tr:hover { 
        background-color: #f1f5f9; 
    }

    .applicant-info { display: flex; flex-direction: column; gap: 2px; text-align: left; }
    .applicant-name { font-weight: normal; color: #111827; font-size: 0.85rem; }
    .applicant-nic { font-size: 0.75rem; color: #6b7280; font-family: monospace; letter-spacing: 0.5px; }

    .badge-issue { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 9999px; font-weight: normal; font-size: 0.75rem; gap: 4px; }
    .issue-water { background-color: #e0f2fe; color: #0369a1; }
    .issue-electricity { background-color: #fef9c3; color: #a16207; }
    .issue-maintenance { background-color: #fee2e2; color: #991b1b; }
    .issue-other { background-color: #f3f4f6; color: #374151; }

    .badge-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; font-weight: normal; font-size: 0.75rem; }
    .status-pending { background-color: #fff7ed; color: #c2410c; }
    .status-progress { background-color: #fef9c3; color: #854d0e; }
    .status-resolved { background-color: #f0fdf4; color: #15803d; }

    .complaint-text { width: 100%; max-width: 350px; white-space: normal; word-break: break-word; color: #1f2937; font-weight: normal; text-align: left; }

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
        .print-btn {
            width: 100%;
            justify-content: center;
        }
    }

    /* Print / Save as PDF style matching appli-approve-IB exactly[cite: 21] */
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

        .back-link, .search-card, .user-profile-wrapper { 
            display: none !important; 
        }

        .management-section { 
            box-shadow: none !important; 
            border: none !important; 
            padding: 0 !important; 
        }

        .data-table { 
            width: 100% !important; 
            font-size: 8.5pt !important; 
            min-width: 0 !important;
        }

        .data-table th, .data-table td { 
            padding: 8px 10px !important; 
        }
    }
</style>

<div class="container">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
        <a href="SC_dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
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
                <a href="SC.php"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="page-title">Inquiries & Complaints</h2>

    <!-- Search Card matching appli-approve-IB style[cite: 21] -->
    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" class="search-input" placeholder="Search by NIC or Name..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                <?php if (!empty($searchQuery)): ?>
                    <a href="?" class="search-clear-icon" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
        <button type="button" class="print-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
    </form>

    <div class="management-section">
        <div class="table-responsive">
            <table class="data-table" id="inquiryTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Applicant NIC</th>
                        <th>Applicant Name</th>
                        <th>Complaints Description</th>
                        <th>Related Issue</th>
                        <th>Submitted Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($displayData)): ?>
                        <tr><td colspan="7" style="text-align: center; color: #64748b; padding: 30px;">No matching inquiries found.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($displayData as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($no++); ?></td>
                                <td><span class="applicant-nic"><?php echo htmlspecialchars($row['nic']); ?></span></td>
                                <td><span class="applicant-name"><?php echo htmlspecialchars($row['name']); ?></span></td>
                                <td>
                                    <div class="complaint-text">
                                        <?php echo htmlspecialchars($row['complaint']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-issue <?php echo htmlspecialchars($row['issueClass']); ?>">
                                        <i class="fa-solid <?php echo htmlspecialchars($row['issueIcon']); ?>"></i> <?php echo htmlspecialchars($row['issueType']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td>
                                    <span class="badge-status <?php echo htmlspecialchars($row['statusClass']); ?>">
                                        <i class="fa-solid <?php echo htmlspecialchars($row['statusIcon']); ?>"></i> <?php echo htmlspecialchars($row['status']); ?>
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