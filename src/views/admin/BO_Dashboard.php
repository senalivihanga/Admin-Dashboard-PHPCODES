<?php
/**
 * Branch Officer Dashboard - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Page Title
$pageTitle = "Branch Officer Dashboard - Sri Lanka Railways";

// Session values or defaults
$officer_name = isset($_SESSION['officer_name']) ? $_SESSION['officer_name'] : 'Branch Officer';
$officer_role = isset($_SESSION['officer_role']) ? $_SESSION['officer_role'] : 'Sri Lanka Railways - AOT';
$initials = isset($_SESSION['initials']) ? $_SESSION['initials'] : 'BO';

// Logout action handle
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Include Admin Header
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<!-- Custom Styles for Branch Officer Dashboard -->
<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-y: auto; 
        background-color: #f8fafc;
    }

    .dashboard-container {
        max-width: 1150px;
        margin: 30px auto;
        padding: 0 20px;
        width: 100%;
        box-sizing: border-box;
    }

    /* Back to Dashboard Link Style */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 20px;
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

    /* Profile & Dropdown Menu Styles */
    .user-profile-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 25px;
    }

    .user-profile-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #7A1C1C;
        color: #ffffff;
        padding: 6px 16px;
        border-radius: 30px;
        cursor: pointer;
        user-select: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: all 0.2s ease;
    }

    .user-profile-badge:hover {
        background: #621616;
    }

    .profile-dropdown {
        display: none;
        position: absolute;
        left: 0;
        top: 110%;
        background: #ffffff;
        min-width: 200px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
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
        border-top: 1px solid #e2e8f0;
    }

    .profile-dropdown a.logout-btn:hover {
        background: #fee2e2;
    }

    /* Custom Modal for Logout Confirmation */
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
        box-sizing: border-box;
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
        padding: 10px 18px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-modal-cancel {
        background-color: #64748b;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }

    /* Balanced Layout Container for Content */
    .dashboard-content-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    /* Management Grid & Section Title Balancing */
    .section-title {
        font-size: 1.15rem;
        color: #1e293b;
        margin-bottom: 20px;
        font-weight: 700;
        border-left: 4px solid #7A1C1C;
        padding-left: 10px;
        width: 100%;
        max-width: 760px;
    }

    .management-row {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
        width: 100%;
        max-width: 760px;
        margin-bottom: 40px;
    }

    .manage-tile {
        background: #ffffff;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
        border: 1px solid #e2e8f0;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        gap: 10px;
        transition: all 0.25s ease;
        width: 365px;
        box-sizing: border-box;
    }

    .manage-tile:hover {
        border-color: #7A1C1C;
        box-shadow: 0 8px 20px rgba(122, 28, 28, 0.08);
        transform: translateY(-3px);
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
        font-size: 1.05rem;
        color: #1e293b;
        margin: 0;
        font-weight: 700;
    }

    .manage-tile p {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
        flex-grow: 1;
    }

    .tile-link-text {
        margin-top: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #7A1C1C;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* Mobile Responsiveness Media Queries */
    @media (max-width: 768px) {
        .dashboard-container {
            margin: 15px auto;
            padding: 0 15px;
        }
        .manage-tile {
            width: 100%;
            max-width: 100%;
        }
        .section-title {
            max-width: 100%;
        }
    }
</style>

<div class="dashboard-container">
    <!-- Dashboard Content Wrapper for Perfect Centering & Balance -->
    <div class="dashboard-content-wrapper">
        
        <!-- Branch Officer Management Modules -->
        <div class="section-title">Application Management Modules</div>
        
        <div class="management-row">
            <!-- Tile 1: Waiting List -->
            <a href="waiting_list_BO.php" class="manage-tile">
                <div class="manage-tile-header">
                    <h4>Waiting List</h4>
                    <i class="fa-solid fa-list-ol" style="color: #d97706;"></i>
                </div>
                <p>Monitor and review the queue of approved applicants waiting for railway quarters allocations.</p>
                <span class="tile-link-text">View Waiting List <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <!-- Tile 2: Quarters Details (Branch Officer) -->
            <a href="quarters_list_BO.php" class="manage-tile">
                <div class="manage-tile-header">
                    <h4>Quarters Details</h4>
                    <i class="fa-solid fa-building-user" style="color: #0284c7;"></i>
                </div>
                <p>Inspect comprehensive records, availability status, and housing conditions of all railway quarters.</p>
                <span class="tile-link-text">View Quarters <i class="fa-solid fa-arrow-right"></i></span>
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

    emptyLogout = function(event) {
        // placeholder
    }

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
        window.location.href = "login.php?action=logout";
    }
</script>

<?php
// Include Admin Footer
require_once __DIR__ . '/../layouts/admin_footer.php';
?>