<?>
/**
 * View Direct Allocation - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "View Direct Allocation - Sri Lanka Railways";

$userData = [
    'name' => 'Final Approver',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'FA'
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

    .container { 
        max-width: 700px; 
        margin: 20px auto; 
        padding: 0 20px; 
        width: 100%;
        box-sizing: border-box;
    }

    .back-link { 
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        color: #7A1C1C; 
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

    .page-title { 
        text-align: center; 
        color: #7A1C1C; 
        font-size: 1.6rem; 
        margin-bottom: 20px; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        font-weight: 700; 
    }

    .view-card { 
        background: #ffffff; 
        padding: 30px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid #7A1C1C; 
    }

    .view-row { 
        display: flex; 
        border-bottom: 1px solid #f1f5f9; 
        padding: 12px 0; 
        font-size: 0.95rem; 
        flex-wrap: wrap;
    }

    .view-row:last-child { 
        border-bottom: none; 
    }

    .view-label { 
        font-weight: 700; 
        width: 220px; 
        color: #475569; 
    }

    .view-value { 
        color: #1e293b; 
        flex: 1; 
        font-weight: 400; 
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
        .container {
            margin: 15px auto;
            padding: 0 10px;
        }
        .view-label {
            width: 100%;
            margin-bottom: 4px;
        }
        .view-value {
            width: 100%;
        }
    }
</style>

<div class="container">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) &&$_SESSION['role'] === 'admin')): ?>
        <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
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

    <a href="direct_allocation_FA.php" class="back-link" style="margin-bottom: 15px;"><i class="fa-solid fa-arrow-left"></i> Back to Allocation List</a>

    <div class="view-card">
        <h2 class="page-title">Direct Allocation Detailed View</h2>
        <div id="viewContent">
            <!-- Dynamic Data will load here -->
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

    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const index = urlParams.get('id');
        
        const allocations = JSON.parse(localStorage.getItem('directAllocations')) || [];
        const viewContent = document.getElementById('viewContent');

        if (index !== null && allocations[index]) {
            const item = allocations[index];
            viewContent.innerHTML = `
                <div class="view-row"><div class="view-label">Auto ID:</div><div class="view-value">${parseInt(index) + 1}</div></div>
                <div class="view-row"><div class="view-label">NIC Number:</div><div class="view-value">${item.nic || '-'}</div></div>
                <div class="view-row"><div class="view-label">Computer Number:</div><div class="view-value">${item.computerNo || '-'}</div></div>
                <div class="view-row"><div class="view-label">Full Name:</div><div class="view-value">${item.fullName || '-'}</div></div>
                <div class="view-row"><div class="view-label">Quarter No.:</div><div class="view-value">${item.quarterNo || '-'}</div></div>
                <div class="view-row"><div class="view-label">Quarter Name:</div><div class="view-value">${item.quarterName || '-'}</div></div>
                <div class="view-row"><div class="view-label">Quarter Location:</div><div class="view-value">${item.quarterLocation || '-'}</div></div>
                <div class="view-row"><div class="view-label">Quarter Type:</div><div class="view-value">${item.quarterType || '-'}</div></div>
                <div class="view-row"><div class="view-label">Mobile Number:</div><div class="view-value">${item.phone || '-'}</div></div>
                <div class="view-row"><div class="view-label">Reason:</div><div class="view-value">${item.reason || '-'}</div></div>
            `;
        } else {
            viewContent.innerHTML = `<p style="text-align: center; color: #dc2626; font-weight: 600; padding: 20px 0;">Record not found!</p>`;
        }
    });
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>