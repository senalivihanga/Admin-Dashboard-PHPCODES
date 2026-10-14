<?php
/**
 * Immediate Boss Dashboard - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// pituwe naama (Page Title) sækasima
$pageTitle = "Immediate Boss Dashboard - Sri Lanka Railways";

// aadarshavata data
$dashboardStats = [
    'total_approvals' => 24,
    'pending_list' => 8,
    'approved_list' => 12,
    'rejected_list' => 4
];

$userData = [
    'name' => $_SESSION['user_name'] ?? 'Immediate Boss',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'IB'
];

$user = [
    'name' => $userData['name']
];

// Admin Header eka wenas kara natha[cite: 21]
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<!-- dashboard eka sadaha abhiruchi mostara -->
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
        min-width: 180px;
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
        gap: 15px;
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
        margin: 5px 0;
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
        font-weight: 700; /* Resubmitted code tile puka bold style-eka lesa yothisla ayitha[cite: 22] */
        text-decoration: none; /* Yatin ira nathiwa[cite: 22] */
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
        
        <div class="row">
            <a href="appli-approve-IB.php" class="stat-card">
                <i class="fa-solid fa-list-check"></i>
                <h3>Total Approval List</h3>
                <div class="stat-count"><?php echo str_pad($dashboardStats['total_approvals'], 2, '0', STR_PAD_LEFT); ?></div>
            </a>

            <a href="pending_list_IB.php" class="stat-card">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <h3>Pending List</h3>
                <div class="stat-count"><?php echo str_pad($dashboardStats['pending_list'], 2, '0', STR_PAD_LEFT); ?></div>
            </a>

            <div class="stat-card" onclick="location.href='approve_reject_list_IB.php?tab=approved'">
                <i class="fa-solid fa-clipboard-check"></i>
                <h3>Approved List</h3>
                <div class="stat-count"><?php echo str_pad($dashboardStats['approved_list'], 2, '0', STR_PAD_LEFT); ?></div>
            </div>

            <div class="stat-card" onclick="location.href='approve_reject_list_IB.php?tab=rejected'">
                <i class="fa-solid fa-ban"></i>
                <h3>Rejected List</h3>
                <div class="stat-count"><?php echo str_pad($dashboardStats['rejected_list'], 2, '0', STR_PAD_LEFT); ?></div>
            </div>
        </div>

        <div class="section-title">Application Management Modules</div>
        
        <div class="row">
            <a href="appli-approve-IB.php" class="manage-tile">
                <div class="manage-tile-header">
                    <h4>Application Approval</h4>
                    <i class="fa-solid fa-file-shield"></i>
                </div>
                <p>Process incoming applications, review primary credentials, and execute initial approvals.</p>
                <span class="tile-link-text">Open Approval Queue <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="approve_reject_list_IB.php" class="manage-tile">
                <div class="manage-tile-header">
                    <h4>Approved / Rejected List</h4>
                    <i class="fa-solid fa-circle-check" style="color: #10b981;"></i>
                </div>
                <p>Browse through historical records of fully processed, approved, or rejected applications.</p>
                <span class="tile-link-text">View Records <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="resubmitted_list_IB.php" class="manage-tile">
                <div class="manage-tile-header">
                    <h4>Resubmitted List</h4>
                    <i class="fa-solid fa-rotate-right" style="color: #0284c7;"></i>
                </div>
                <p>Review applications that were resubmitted by staff following previous rejections and feedback.</p>
                <span class="tile-link-text">Open Resubmitted Queue <i class="fa-solid fa-arrow-right"></i></span>
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
require_once __DIR__ . '/../layouts/admin_footer.php';
?>