<?php
/**
 * Add Direct Allocation - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Add Direct Allocation - Sri Lanka Railways";

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
        max-width: 800px; 
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

    .card { 
        background: #ffffff; 
        padding: 35px; 
        border-radius: 15px; 
        box-shadow: 0 10px 25px rgba(0,0,0,0.08); 
        border-top: 5px solid #7A1C1C; 
    }

    .card h2 { 
        color: #1e293b; 
        font-size: 1.5rem; 
        margin-bottom: 20px; 
        text-transform: uppercase; 
        text-align: center; 
        font-weight: 700;
    }

    .form-group { 
        margin-bottom: 20px; 
    }

    .form-group label { 
        display: block; 
        font-weight: 600; 
        color: #475569; 
        margin-bottom: 8px; 
        font-size: 0.9rem; 
    }

    .form-group input, .form-group select { 
        width: 100%; 
        padding: 12px 15px; 
        border: 1px solid #cbd5e1; 
        border-radius: 6px; 
        outline: none; 
        font-size: 0.95rem; 
        box-sizing: border-box; 
    }

    .form-group input:focus { 
        border-color: #7A1C1C; 
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1); 
    }

    .form-row { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 20px; 
    }

    .btn-submit { 
        background-color: #10b981; 
        color: white; 
        border: none; 
        padding: 12px 20px; 
        border-radius: 6px; 
        cursor: pointer; 
        font-weight: 600; 
        width: 100%; 
        font-size: 1rem; 
        transition: background 0.2s; 
    }

    .btn-submit:hover { 
        background-color: #059669; 
    }

    #successAlert { 
        display: none; 
        background-color: #dcfce7; 
        color: #166534; 
        padding: 12px 20px; 
        border-radius: 8px; 
        margin-bottom: 20px; 
        font-weight: 600; 
        border: 1px solid #bbf7d0; 
        text-align: center; 
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
        .form-row {
            grid-template-columns: 1fr;
            gap: 0;
        }
        .container {
            margin: 15px auto;
            padding: 0 10px;
        }
    }
</style>

<div class="container">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
        <a href="direct_allocation_FA.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Allocations List</a>
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

    <div class="card">
        <h2>Add Direct Quarter Allocation</h2>
        
        <div id="successAlert"><i class="fa-solid fa-circle-check"></i> Direct allocation saved successfully!</div>

        <form id="addDirectAllocationForm">
            <div class="form-row">
                <div class="form-group">
                    <label>Applicant NIC Number:</label>
                    <input type="text" id="nic" required placeholder="Enter NIC number">
                </div>
                <div class="form-group">
                    <label>Computer Number:</label>
                    <input type="text" id="computerNo" required placeholder="Enter computer number">
                </div>
            </div>

            <div class="form-group">
                <label>Name:</label>
                <input type="text" id="fullName" required placeholder="Enter your name">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Quarter No.:</label>
                    <input type="text" id="quarterNo" required placeholder="Enter quarter number">
                </div>
                <div class="form-group">
                    <label>Quarter Name:</label>
                    <input type="text" id="quarterName" required placeholder="Enter quarter name">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Quarter Location:</label>
                    <input type="text" id="quarterLocation" required placeholder="Enter quarter location">
                </div>
                <div class="form-group">
                    <label>Quarter Type:</label>
                    <input type="text" id="quarterType" required placeholder="Enter quarter type">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Mobile Number:</label>
                    <input type="text" id="phone" required placeholder="Enter mobile number" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <div class="form-group">
                    <label>Approval Authority Reason:</label>
                    <input type="text" id="reason" required placeholder="Enter reason">
                </div>
            </div>

            <button type="submit" class="btn-submit"><i class="fa-solid fa-check-circle"></i> Save Allocation</button>
        </form>
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

    document.getElementById('addDirectAllocationForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const allocationData = {
            nic: document.getElementById('nic').value,
            computerNo: document.getElementById('computerNo').value,
            fullName: document.getElementById('fullName').value,
            quarterNo: document.getElementById('quarterNo').value,
            quarterName: document.getElementById('quarterName').value,
            quarterLocation: document.getElementById('quarterLocation').value,
            quarterType: document.getElementById('quarterType').value,
            phone: document.getElementById('phone').value,
            reason: document.getElementById('reason').value
        };

        let existingAllocations = JSON.parse(localStorage.getItem('directAllocations')) || [];
        existingAllocations.push(allocationData);
        localStorage.setItem('directAllocations', JSON.stringify(existingAllocations));

        const alertBox = document.getElementById('successAlert');
        alertBox.style.display = 'block';

        setTimeout(() => {
            window.location.href = "direct_allocation_FA.php";
        }, 1500);
    });
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>