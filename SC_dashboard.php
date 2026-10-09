<?php
/**
 * Subject Clerk Dashboard - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Subject Clerk Dashboard - Sri Lanka Railways";

$dashboardStats = [
    'total_approvals' => 24,
    'pending_list' => 8,
    'approved_list' => 85
];

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-y: auto;
        background-color: #f8fafc;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .dashboard-container {
        max-width: 1300px;
        margin: 20px auto;
        padding: 0 20px;
        width: 100%;
        box-sizing: border-box;
    }

    .dashboard-header {
        background: linear-gradient(135deg, #7A1C1C 0%, #5a1313 100%);
        padding: 25px 30px;
        border-radius: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        box-shadow: 0 10px 25px -5px rgba(122, 28, 28, 0.3);
        position: relative;
        margin-bottom: 25px;
    }

    .dashboard-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(to right, #d4af37);
        border-bottom-left-radius: 14px;
        border-bottom-right-radius: 14px;
    }

    .dashboard-header h2 {
        font-size: 1.8rem;
        color: #ffffff;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 700;
    }

    .dashboard-header p {
        color: #f1f5f9;
        margin: 8px 0 0 0;
        font-size: 0.95rem;
        opacity: 0.9;
    }

    .user-profile-wrapper {
        position: relative;
    }

    .user-profile-badge {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255, 255, 255, 0.12);
        padding: 8px 16px;
        border-radius: 50px;
        border: 1px solid rgba(255, 255, 255, 0.25);
        cursor: pointer;
        transition: all 0.2s;
        color: white;
    }

    .user-profile-badge:hover {
        background: rgba(255, 255, 255, 0.22);
    }

    .profile-dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: 105%;
        background: #ffffff;
        width: 250px;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        border: 1px solid #e2e8f0;
        z-index: 100;
        overflow: hidden;
    }

    .profile-dropdown.show {
        display: block;
    }

    .profile-dropdown a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        color: #1e293b;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: background 0.2s;
    }

    .profile-dropdown a:hover {
        background-color: #fdf2f2;
        color: #7A1C1C;
    }

    .profile-dropdown a.logout-btn {
        color: #ef4444;
        border-top: 1px solid #e2e8f0;
    }

    .profile-dropdown a.logout-btn:hover {
        background-color: #fef2f2;
    }

    .custom-modal {
        display: none;
        position: fixed;
        z-index: 2000;
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

    .row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-top: 25px;
        margin-bottom: 35px;
    }

    .stat-card {
        background: #ffffff;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        border-top: 5px solid #7A1C1C;
        display: flex;
        flex-direction: column;
        position: relative;
        text-decoration: none;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
    }

    .stat-card i {
        font-size: 1.6rem;
        color: #7A1C1C;
        margin-bottom: 20px;
    }

    .stat-card h3 {
        font-size: 0.75rem;
        color: #475569;
        margin: 0 0 10px 0;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    .stat-count {
        font-size: 2.2rem;
        font-weight: 700;
        color: #1e293b;
    }

    .section-title {
        font-size: 1.25rem;
        color: #1e293b;
        margin-bottom: 20px;
        font-weight: 700;
        border-left: 4px solid #7A1C1C;
        padding-left: 10px;
    }

    .management-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .manage-tile {
        background: #ffffff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        gap: 12px;
        transition: all 0.2s ease;
        position: relative;
    }

    .manage-tile:hover {
        border-color: #7A1C1C;
        box-shadow: 0 8px 20px rgba(122, 28, 28, 0.08);
        transform: translateY(-2px);
    }

    .manage-tile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .manage-tile-header i {
        font-size: 1.5rem;
        color: #7A1C1C;
    }

    .manage-tile h4 {
        font-size: 1.1rem;
        color: #1e293b;
        margin: 0;
        font-weight: 700;
    }

    .manage-tile p {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
    }

    .tile-link-text {
        margin-top: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #7A1C1C;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    @media (max-width: 768px) {
        .dashboard-container {
            margin: 15px auto;
            padding: 0 10px;
        }
        .management-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h2>Subject Clerk Dashboard</h2>
            <p>Sri Lanka Railways - Quarter Administration & Operation Team (AOT)</p>
        </div>
        
        <div class="user-profile-wrapper">
            <div class="user-profile-badge" onclick="toggleDropdown()">
               <div style="width: 35px; height: 35px; background: #ffffff; color: #7A1C1C; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem;">
                   <?php echo htmlspecialchars($userData['initials']); ?>
               </div>
                <div>
                    <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($userData['name']); ?></div>
                </div>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; margin-left: 5px;"></i>
            </div>

            <div id="profileDropdown" class="profile-dropdown">
                <a href="edit_profile.php"><i class="fa-solid fa-user-pen"></i> Edit Profile</a>
                <a href="view_system.php"><i class="fa-solid fa-arrow-left"></i> Back to Approval Dashboard</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    </div>

    <div class="row">
        <a href="appli-approve-SC.php" class="stat-card">
            <i class="fa-solid fa-list-check"></i>
            <h3>Total Approval List</h3>
            <div class="stat-count"><?php echo str_pad($dashboardStats['total_approvals'], 2, '0', STR_PAD_LEFT); ?></div>
        </a>

        <a href="pending-list-SC.php" class="stat-card">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <h3>Pending List</h3>
            <div class="stat-count"><?php echo str_pad($dashboardStats['pending_list'], 2, '0', STR_PAD_LEFT); ?></div>
        </a>

        <a href="approve-list-SC.php" class="stat-card">
            <i class="fa-solid fa-circle-check" style="color: #10b981;"></i>
            <h3>Approved List</h3>
            <div class="stat-count"><?php echo $dashboardStats['approved_list']; ?></div>
        </a>
    </div>

    <div class="section-title">Application Management Modules</div>
    
    <div class="management-grid">
        <a href="appli-approve-SC.php" class="manage-tile">
            <div class="manage-tile-header">
                <h4>Application Approval</h4>
                <i class="fa-solid fa-file-shield"></i>
            </div>
            <p>Process incoming applications, review applicant details, and advance them through clerical reviews.</p>
            <span class="tile-link-text">Open Approval Queue <i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <a href="approve-list-SC.php" class="manage-tile">
            <div class="manage-tile-header">
                <h4>Approved List</h4>
                <i class="fa-solid fa-circle-check" style="color: #10b981;"></i>
            </div>
            <p>View the comprehensive log and records of all successfully approved applications.</p>
            <span class="tile-link-text">View Approve List <i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <a href="appli-list-SC.php" class="manage-tile">
            <div class="manage-tile-header">
                <h4>Total Request Applications</h4>
                <i class="fa-solid fa-folder-open" style="color: #6366f1;"></i>
            </div>
            <p>Access the master archive containing all submitted housing requests and pending files.</p>
            <span class="tile-link-text">View All Requests <i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <a href="status-SC.php" class="manage-tile">
            <div class="manage-tile-header">
                <h4>Application Status</h4>
                <i class="fa-solid fa-chart-line" style="color: #14b8a6;"></i>
            </div>
            <p>Track real-time progress, review stages, and active workflow states for all applications.</p>
            <span class="tile-link-text">Check Status <i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <a href="quarters-list-SC.php" class="manage-tile">
            <div class="manage-tile-header">
                <h4>Quarters Details</h4> 
                <i class="fa-solid fa-building-user" style="color: #0284c7;"></i>
            </div>
            <p>Manage database records of railway quarters, availability status, locations, and housing conditions.</p>
            <span class="tile-link-text">Print Quarters <i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <a href="assign-quarters-SC.php" class="manage-tile">
            <div class="manage-tile-header">
                <h4>Assign Quarters</h4>
                <i class="fa-solid fa-key" style="color: #d97706;"></i>
            </div>
            <p>Allocate available railway housing units to approved applicants based on seniority and eligibility.</p>
            <span class="tile-link-text">Assign Quarters <i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <a href="final-Approve-SC.php" class="manage-tile">
            <div class="manage-tile-header">
                <h4>Final Approval Approved List</h4>
                <i class="fa-solid fa-award" style="color: #7A1C1C;"></i>
            </div>
            <p>Monitor applications that have cleared final departmental boards and completed final approvals.</p>
            <span class="tile-link-text">View Final Approvals <i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <a href="inquiry.php" class="manage-tile">
            <div class="manage-tile-header">
                <h4>Inquiries</h4>
                <i class="fa-solid fa-circle-question" style="color: #ec4899;"></i>
            </div>
            <p>Review and respond to applicant questions, feedback, and support through the portal.</p>
            <span class="tile-link-text">View Inquiries <i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <a href="generate-letter-SC.php" class="manage-tile">
            <div class="manage-tile-header">
                <h4>Generate Letter</h4>
                <i class="fa-solid fa-file-pen" style="color: #8b5cf6;"></i>
            </div>
            <p>Create, preview, and print official department notification or allocation response letters for applicants.</p>
            <span class="tile-link-text">Generate Now <i class="fa-solid fa-arrow-right"></i></span>
        </a>
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
        dropdown.classList.toggle('show');
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