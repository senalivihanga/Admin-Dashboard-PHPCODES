<?php
/**
 * Final Approval Dashboard - Sri Lanka Railways
 * Quarter Administration & Operation Team (AOT)
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------------------------------------------------------
// FA Dashboard stats — real counts, matching the pages each
// tile links to (appli_approve_FA.php, pending_list_FA.php,
// approve_reject_list_FA.php?tab=approved|rejected).
// ---------------------------------------------------------------
$conn = getDBConnection();

// 1. Total: every application that has reached the FA stage
//    (either currently pending or already decided).
$totalStmt = $conn->query(
    "SELECT COUNT(*) AS total
       FROM applications
      WHERE boss_status  = 'approved'
        AND file_status  = 'approved'
        AND clerk_status = 'approved'"
);
$totalApprovals = (int)($totalStmt->fetch_assoc()['total'] ?? 0);
$totalStmt->close();

// 2. Pending: waiting at the FA's desk right now.
//    Same predicate as appli_approve_FA.php.
$pendingStmt = $conn->query(
    "SELECT COUNT(*) AS total
       FROM applications
      WHERE boss_status  = 'approved'
        AND file_status  = 'approved'
        AND clerk_status = 'approved'
        AND final_status = 'pending'
        AND application_status NOT IN ('rejected','returned_to_boss','returned_to_file')"
);
$pendingCount = (int)($pendingStmt->fetch_assoc()['total'] ?? 0);
$pendingStmt->close();

// 3. Approved: FA has granted final approval.
$approvedStmt = $conn->query(
    "SELECT COUNT(*) AS total
       FROM applications
      WHERE final_status = 'approved'"
);
$approvedCount = (int)($approvedStmt->fetch_assoc()['total'] ?? 0);
$approvedStmt->close();

// 4. Rejected: FA has rejected (only rows that still exist — the
//    current reject flow deletes the row, so this usually reads 0).
$rejectedStmt = $conn->query(
    "SELECT COUNT(*) AS total
       FROM applications
      WHERE final_status = 'rejected'"
);
$rejectedCount = (int)($rejectedStmt->fetch_assoc()['total'] ?? 0);
$rejectedStmt->close();

$conn->close();

$dashboardStats = [
    'total_approvals' => $totalApprovals,
    'pending_list'    => $pendingCount,
    'approved_list'   => $approvedCount,
    'rejected_list'   => $rejectedCount
];

// Page Title saskasima
$pageTitle = "Final Approval Dashboard - Sri Lanka Railways";



$userData = [
    'name' => 'Final Approver',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'FA'
];

$user = [
    'name' => $userData['name']
];

// Admin Header include karni
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<!-- Dashboard custom styles -->
<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-y: auto; 
    }

    .dashboard-container {
        max-width: 1300px;
        margin: 15px auto;
        padding: 0 20px;
        width: 100%;
        box-sizing: border-box;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 24px;
        color: #7A1C1C;
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
        margin-bottom: 24px;
    }

    .user-profile-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #7A1C1C;
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
        border: 1px solid #e2e8f0;
    }

    .profile-dropdown.show {
        display: block;
    }

    .profile-dropdown a {
        color: #1e293b;
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
        color: #7A1C1C;
    }

    .profile-dropdown a.logout-btn {
        color: #ef4444;
    }

    .profile-dropdown a.logout-btn:hover {
        background: #fee2e2;
    }

    .dashboard-flex-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
        align-items: center;
        width: 100%;
    }

    .row {
        display: flex;
        justify-content: center;
        gap: 15px;
        width: 100%;
        flex-wrap: wrap;
    }

    .stat-card {
        background: #ffffff;
        padding: 16px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
        border: 1px solid #e2e8f0;
        border-top: 4px solid #7A1C1C;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
        transition: all 0.25s ease;
        text-decoration: none;
        cursor: pointer;
        width: 265px;
        box-sizing: border-box;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px -3px rgba(122, 28, 28, 0.12);
        border-color: #7A1C1C;
    }

    .stat-card i {
        font-size: 1.75rem;
        color: #7A1C1C;
        margin-bottom: 8px;
    }

    .stat-card h3 {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0 0 5px 0;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-count {
        font-size: 1.9rem;
        font-weight: 700;
        color: #1e293b;
    }

    .section-title {
        font-size: 1.15rem;
        color: #1e293b;
        margin: 10px 0 5px 0;
        font-weight: 700;
        border-left: 4px solid #7A1C1C;
        padding-left: 10px;
        width: 100%;
        max-width: 1155px;
    }

    .manage-tile {
        background: #ffffff;
        padding: 16px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
        border: 1px solid #e2e8f0;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: all 0.25s ease;
        width: 365px;
        box-sizing: border-box;
    }

    .manage-tile:hover {
        border-color: #7A1C1C;
        box-shadow: 0 8px 16px rgba(122, 28, 28, 0.08);
        transform: translateY(-3px);
    }

    .manage-tile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .manage-tile-header i {
        font-size: 1.35rem;
        color: #7A1C1C;
    }

    .manage-tile h4 {
        font-size: 1rem;
        color: #1e293b;
        margin: 0;
        font-weight: 700;
        text-decoration: none;
    }

    .manage-tile p {
        font-size: 0.82rem;
        color: #64748b;
        margin: 0;
        line-height: 1.35;
        flex-grow: 1;
    }

    .tile-link-text {
        margin-top: 4px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #7A1C1C;
        display: inline-flex;
        align-items: center;
        gap: 6px;
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
        font-size: 3rem;
        color: #7A1C1C;
        margin-bottom: 15px;
    }

    .custom-modal-content h3 {
        color: #1e293b;
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

    .btn-modal-confirm {
        background-color: #ef4444;
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-modal-cancel {
        background-color: #64748b;
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }

    @media (max-width: 768px) {
        .dashboard-container {
            margin: 15px auto;
            padding: 0 12px;
        }
        .stat-card, .manage-tile {
            width: 100%;
            max-width: 100%;
        }
    }
</style>

<div class="dashboard-container">
    <div class="dashboard-flex-container">
        
        <!-- Top Row: Stat Cards -->
        <div class="row">
            <a href="appli_approve_FA.php" class="stat-card">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <h3>Total Approval List</h3>
                <div class="stat-count"><?php echo str_pad($dashboardStats['total_approvals'], 2, '0', STR_PAD_LEFT); ?></div>
            </a>

            <a href="pending_list_FA.php" class="stat-card">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <h3>Pending List</h3>
                <div class="stat-count"><?php echo str_pad($dashboardStats['pending_list'], 2, '0', STR_PAD_LEFT); ?></div>
            </a>

            <div class="stat-card" onclick="location.href='approve_reject_list_FA.php?tab=approved'">
                <i class="fa-solid fa-clipboard-check"></i>
                <h3>Approved List</h3>
                <div class="stat-count"><?php echo str_pad($dashboardStats['approved_list'], 2, '0', STR_PAD_LEFT); ?></div>
            </div>

            <div class="stat-card" onclick="location.href='approve_reject_list_FA.php?tab=rejected'">
                <i class="fa-solid fa-ban"></i>
                <h3>Rejected List</h3>
                <div class="stat-count"><?php echo str_pad($dashboardStats['rejected_list'], 2, '0', STR_PAD_LEFT); ?></div>
            </div>
        </div>

        <div class="section-title">Application Management Modules</div>
        
        <!-- Management Modules Grid -->
        <div class="row">
            <a href="appli_approve_FA.php" class="manage-tile">
                <div class="manage-tile-header">
                    <h4>Application Approval</h4>
                    <i class="fa-solid fa-file-shield"></i>
                </div>
                <p>Process incoming applications, review primary credentials, and execute initial approvals.</p>
                <span class="tile-link-text">Open Approval Queue <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="approve_reject_list_FA.php" class="manage-tile">
                <div class="manage-tile-header">
                    <h4>Approved / Rejected List</h4>
                    <i class="fa-solid fa-circle-check" style="color: #10b981;"></i>
                </div>
                <p>Browse through historical records of fully processed, approved, or rejected applications.</p>
                <span class="tile-link-text">View Records <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="status_FA.php" onclick="localStorage.setItem('returnDashboard', 'FA_dashboard.php')" class="manage-tile">
                <div class="manage-tile-header">
                    <h4>Application Status</h4>
                    <i class="fa-solid fa-spinner" style="color: #0284c7;"></i>
                </div>
                <p>Track real-time progress, stages, and current workflow states of submitted applications.</p>
                <span class="tile-link-text">Check Status <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="waiting_list_FA.php" class="manage-tile">
                <div class="manage-tile-header">
                    <h4>Waiting Lists</h4>
                    <i class="fa-solid fa-list-ol" style="color: #d97706;"></i>
                </div>
                <p>Manage and review candidates lined up in priority queue sequences for allocation.</p>
                <span class="tile-link-text">View Waiting List <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="quarters_allocation_FA.php" class="manage-tile">
                <div class="manage-tile-header">
                    <h4>Quarters Allocation</h4>
                    <i class="fa-solid fa-house-chimney" style="color: #7A1C1C;"></i>
                </div>
                <p>Oversee railway housing unit distributions, assignments, and accommodation availability records.</p>
                <span class="tile-link-text">Manage Allocations <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="add_allocated_quarter.php" class="manage-tile">
                <div class="manage-tile-header">
                    <h4>Direct Quarter Allocation</h4>
                    <i class="fa-solid fa-hand-holding-heart" style="color: #059669;"></i>
                </div>
                <p>Allocate quarters directly under FA's discretion and submit applicant personal details into the system.</p>
                <span class="tile-link-text">Open Allocation Form <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="direct_allocations.php" class="manage-tile">
                <div class="manage-tile-header">
                    <h4>Direct Allocations (Audit)</h4>
                    <i class="fa-solid fa-list-check" style="color: #7c3aed;"></i>
                </div>
                <p>View direct allocations and continue their applications.</p>
                <span class="tile-link-text">Open Audit List <i class="fa-solid fa-arrow-right"></i></span>
            </a>
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
// Admin Footer include karni
require_once __DIR__ . '/../layouts/admin_footer.php';
?>