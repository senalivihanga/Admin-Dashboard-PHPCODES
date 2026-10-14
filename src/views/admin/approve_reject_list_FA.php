<?php
/**
 * Approved & Rejected Applications Log - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Approved & Rejected Applications Log - Sri Lanka Railways";

$userData = [
    'name' => 'Final Approver',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'FA'
];

$logData = [
    ['id' => 1, 'appNo' => 'SLR/AOT/2026/0001', 'nic' => '981234567V', 'compNo' => '101', 'name' => 'A. B. Perera', 'desig' => 'Station Master', 'status' => 'Approved', 'reason' => '-'],
    ['id' => 2, 'appNo' => 'SLR/AOT/2026/0002', 'nic' => '957891234V', 'compNo' => '102', 'name' => 'K. D. Silva', 'desig' => 'Signal Inspector', 'status' => 'Rejected', 'reason' => 'Incomplete documentation regarding current technical qualifications.'],
    ['id' => 3, 'appNo' => 'SLR/AOT/2026/0003', 'nic' => '923456789V', 'compNo' => '105', 'name' => 'M. N. Fernando', 'desig' => 'Locomotive Driver', 'status' => 'Approved', 'reason' => '-'],
    ['id' => 4, 'appNo' => 'SLR/AOT/2026/0004', 'nic' => '904561238V', 'compNo' => '110', 'name' => 'S. K. Jayasundara', 'desig' => 'Way Inspector', 'status' => 'Rejected', 'reason' => 'Does not meet the minimum service requirement period.'],
    ['id' => 5, 'appNo' => 'SLR/AOT/2026/0005', 'nic' => '945612378V', 'compNo' => '112', 'name' => 'P. R. Gunawardena', 'desig' => 'Telecommunication Tech', 'status' => 'Approved', 'reason' => '-']
];

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'approved';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// PHP filtering function[cite: 6, 7]
function filterData($data, $status, $query) {
    return array_filter($data, function($item) use ($status, $query) {
        if ($item['status'] !== $status) return false;
        if ($query !== '') {
            $q = strtolower($query);
            return strpos(strtolower($item['nic']), $q) !== false || strpos(strtolower($item['name']), $q) !== false;
        }
        return true;
    });
}

$approvedList = filterData($logData, 'Approved', $searchQuery);
$rejectedList = filterData($logData, 'Rejected', $searchQuery);

// Admin Header include karni[cite: 6]
require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    :root {
        --primary-color: #7A1C1C;
        --primary-dark: #571a1a;
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
        overflow-x: hidden;
        background-color: var(--bg-color);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .approval-container {
        width: 100%;
        max-width: 1300px;
        margin: 20px auto;
        padding: 0 20px;
        box-sizing: border-box;
    }

    .approval-header {
        text-align: center;
        margin-bottom: 25px;
    }

    .approval-container h2 {
        font-size: 1.8rem;
        color: var(--primary-color);
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-decoration: underline;
        text-underline-offset: 6px;
        font-weight: 700;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 20px;
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        transition: gap 0.2s;
    }

    .back-link:hover {
        gap: 10px;
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
        color: #ef4444;
    }

    .profile-dropdown a.logout-btn:hover {
        background: #fee2e2;
    }

    /* Tab Switcher Buttons */
    .tab-buttons-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .tab-btn {
        background-color: #e2e8f0;
        color: #334155;
        border: none;
        padding: 10px 22px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    #approvedTabBtn.active, 
    #rejectedTabBtn.active {
        background-color: var(--primary-color);
        color: white;
        box-shadow: 0 4px 6px -1px rgba(122, 28, 28, 0.2);
    }

    .tab-btn:hover:not(.active) {
        background-color: #cbd5e1;
    }

    /* Search and Print Action Card */
    .search-card {
        background: var(--card-bg);
        padding: 18px 22px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
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
        max-width: 350px;
    }

    .search-input {
        padding: 10px 36px 10px 14px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        font-size: 0.9rem;
        width: 100%;
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
        text-decoration: none;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: color 0.2s;
    }

    .clear-input-btn:hover {
        color: #1e293b;
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

    .section-print-title {
        font-size: 1.25rem;
        color: var(--primary-color);
        margin-bottom: 15px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }

    .badge-approved {
        background-color: #dcfce7;
        color: #166534;
    }

    .badge-rejected {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .table-card { 
        background: var(--card-bg); 
        padding: 20px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid var(--primary-color); 
        overflow-x: auto; 
        width: 100%;
        box-sizing: border-box;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
        font-size: 0.9rem;
        min-width: 800px;
    }

    th, td {
        padding: 12px 14px;
        border: 1px solid var(--border-color);
        vertical-align: middle;
    }

    th {
        background-color: #f8fafc;
        color: var(--text-main);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }

    tr:hover {
        background-color: #f1f5f9;
    }

    .reason-text {
        color: #475569;
        font-style: italic;
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

    .btn-modal-confirm { background-color: #ef4444; }
    .btn-modal-cancel { background-color: #64748b; }

    @media (max-width: 768px) {
        .approval-container {
            margin: 15px auto;
            padding: 0 10px;
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
            margin: 5mm;
        }

        html, body {
            background-color: #ffffff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .back-link, .user-profile-wrapper, .search-card, .tab-buttons-bar {
            display: none !important;
        }

        .table-section {
            display: none !important;
        }

        .table-section.print-active {
            display: block !important;
        }

        .table-card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
        }

        table {
            width: 100% !important;
            font-size: 8.5pt;
        }

        th, td {
            padding: 8px 10px !important;
        }
    }
</style>

<main class="approval-container">
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
    
    <div class="approval-header">
        <h2>Approved & Rejected Applications Log</h2>
    </div>

    <!-- Tab Switcher Buttons -->
    <div class="tab-buttons-bar">
        <a href="?tab=approved<?php echo $searchQuery !== '' ? '&search=' . urlencode($searchQuery) : ''; ?>" id="approvedTabBtn" class="tab-btn <?php echo $activeTab === 'approved' ? 'active' : ''; ?>">
            <i class="fa-solid fa-clipboard-check"></i> Approved List
        </a>
        <a href="?tab=rejected<?php echo $searchQuery !== '' ? '&search=' . urlencode($searchQuery) : ''; ?>" id="rejectedTabBtn" class="tab-btn <?php echo $activeTab === 'rejected' ? 'active' : ''; ?>">
            <i class="fa-solid fa-ban"></i> Rejected List
        </a>
    </div>

    <!-- 1. Approved Table Section -->
    <div id="approvedSection" class="table-section <?php echo $activeTab === 'approved' ? 'print-active' : ''; ?>" style="display: <?php echo $activeTab === 'approved' ? 'block' : 'none'; ?>;">
        <h3 class="section-print-title">Approved List</h3>
        
        <form method="GET" action="" class="search-card">
            <input type="hidden" name="tab" value="approved">
            <div class="search-wrapper">
                <div class="search-input-container">
                    <input type="text" name="search" id="approvedSearchInput" class="search-input" placeholder="Search by NIC or Name..." value="<?php echo htmlspecialchars($activeTab === 'approved' ? $searchQuery : ''); ?>" oninput="if(this.value.trim()==='') this.form.submit();">
                    <?php if ($activeTab === 'approved' && $searchQuery !== ''): ?>
                        <a href="?tab=approved" class="clear-input-btn" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </div>
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </div>
            <button type="button" class="print-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
        </form>

        <div class="table-card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Application Number</th>
                            <th>NIC</th>
                            <th>Computer Number</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Status</th>
                            <th>Remarks / Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($approvedList)): ?>
                            <tr><td colspan="8" style="text-align: center; color: #64748b; padding: 25px;">No approved records found.</td></tr>
                        <?php else: ?>
                            <?php $i = 1; foreach ($approvedList as $item): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($item['appNo']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['nic']); ?></td>
                                    <td><?php echo htmlspecialchars($item['compNo']); ?></td>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['desig']); ?></td>
                                    <td><span class="badge badge-approved"><i class="fa-solid fa-check" title="Approved"></i></span></td>
                                    <td><span class="reason-text"><?php echo htmlspecialchars($item['reason']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. Rejected Table Section -->
    <div id="rejectedSection" class="table-section <?php echo $activeTab === 'rejected' ? 'print-active' : ''; ?>" style="display: <?php echo $activeTab === 'rejected' ? 'block' : 'none'; ?>;">
        <h3 class="section-print-title">Rejected List</h3>

        <form method="GET" action="" class="search-card">
            <input type="hidden" name="tab" value="rejected">
            <div class="search-wrapper">
                <div class="search-input-container">
                    <input type="text" name="search" id="rejectedSearchInput" class="search-input" placeholder="Search by NIC or Name..." value="<?php echo htmlspecialchars($activeTab === 'rejected' ? $searchQuery : ''); ?>" oninput="if(this.value.trim()==='') this.form.submit();">
                    <?php if ($activeTab === 'rejected' && $searchQuery !== ''): ?>
                        <a href="?tab=rejected" class="clear-input-btn" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </div>
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </div>
            <button type="button" class="print-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
        </form>

        <div class="table-card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Application Number</th>
                            <th>NIC</th>
                            <th>Computer Number</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Status</th>
                            <th>Remarks / Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rejectedList)): ?>
                            <tr><td colspan="8" style="text-align: center; color: #64748b; padding: 25px;">No rejected records found.</td></tr>
                        <?php else: ?>
                            <?php $i = 1; foreach ($rejectedList as $item): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($item['appNo']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['nic']); ?></td>
                                    <td><?php echo htmlspecialchars($item['compNo']); ?></td>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['desig']); ?></td>
                                    <td><span class="badge badge-rejected"><i class="fa-solid fa-xmark" title="Rejected"></i></span></td>
                                    <td><span class="reason-text"><?php echo htmlspecialchars($item['reason']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Custom Logout Confirmation Modal -->
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
// Admin Footer include කිරීම (Corrected Footer integration)[cite: 7]
require_once __DIR__ . '/../layouts/admin_footer.php';
?>