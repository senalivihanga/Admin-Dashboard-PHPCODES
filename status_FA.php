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

    .approval-container {
        max-width: 1300px;
        margin: 20px auto;
        padding: 0 20px;
        width: 100%;
        box-sizing: border-box;
    }

    .approval-header {
        text-align: center;
        margin-bottom: 25px;
    }

    .approval-container h2 {
        font-size: 1.8rem;
        color: #7A1C1C;
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

    .tab-buttons-bar {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .tab-btn {
        background-color: #e2e8f0;
        color: #334155;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .tab-btn.active {
        background-color: #7A1C1C;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(122, 28, 28, 0.2);
    }

    .tab-btn:hover:not(.active) {
        background-color: #cbd5e1;
    }

    .search-card {
        background: #ffffff;
        padding: 20px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        border: 1px solid #e2e8f0;
    }

    .search-wrapper {
        display: flex;
        gap: 10px;
        align-items: center;
        width: 100%;
        max-width: 450px;
    }

    .search-input {
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        font-size: 0.9rem;
        flex: 1;
        outline: none;
    }

    .search-input:focus {
        border-color: #7A1C1C;
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1);
    }

    .search-btn {
        background-color: #7A1C1C;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s;
        font-size: 0.9rem;
    }

    .search-btn:hover {
        background-color: #571a1a;
    }

    .print-btn {
        background-color: #0284c7;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: background 0.2s;
        font-size: 0.9rem;
    }

    .print-btn:hover {
        background-color: #0369a1;
    }

    .section-print-title {
        font-size: 1.2rem;
        color: #7A1C1C;
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
        background: #ffffff; 
        padding: 20px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid #7A1C1C; 
        overflow-x: auto; 
        position: relative; 
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
        border: 1px solid #cbd5e1;
        vertical-align: middle;
    }

    th {
        background-color: #f8fafc;
        color: #1e293b;
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
        .approval-container {
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
</style>

<main class="approval-container">
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
    
    <div class="approval-header">
        <h2>Approved & Rejected Applications Log</h2>
    </div>

    <div class="tab-buttons-bar">
        <button type="button" id="approvedTabBtn" class="tab-btn active" onclick="switchTab('approved')">
            <i class="fa-solid fa-clipboard-check"></i> Approved List
        </button>
        <button type="button" id="rejectedTabBtn" class="tab-btn" onclick="switchTab('rejected')">
            <i class="fa-solid fa-ban"></i> Rejected List
        </button>
    </div>

    <div id="approvedSection" class="table-section">
        <h3 class="section-print-title">Approved List</h3>
        
        <div class="search-card">
            <div class="search-wrapper">
                <input type="text" id="approvedSearchInput" class="search-input" placeholder="Search by NIC or Name..." oninput="checkApprovedClear(this)">
                <button type="button" class="search-btn" onclick="filterApprovedTable()"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </div>
            <button type="button" class="print-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
        </div>

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
                        </tr>
                    </thead>
                    <tbody id="approvedTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="rejectedSection" class="table-section" style="display: none;">
        <h3 class="section-print-title">Rejected List</h3>

        <div class="search-card">
            <div class="search-wrapper">
                <input type="text" id="rejectedSearchInput" class="search-input" placeholder="Search by NIC or Name..." oninput="checkRejectedClear(this)">
                <button type="button" class="search-btn" onclick="filterRejectedTable()"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </div>
            <button type="button" class="print-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
        </div>

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
                    <tbody id="rejectedTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</main>

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
    const logData = <?php echo json_encode($logData); ?>;

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

    function switchTab(tabName) {
        const approvedSec = document.getElementById('approvedSection');
        const rejectedSec = document.getElementById('rejectedSection');
        const approvedBtn = document.getElementById('approvedTabBtn');
        const rejectedBtn = document.getElementById('rejectedTabBtn');

        if (tabName === 'approved') {
            approvedSec.style.display = 'block';
            rejectedSec.style.display = 'none';
            approvedBtn.classList.add('active');
            rejectedBtn.classList.remove('active');
        } else {
            approvedSec.style.display = 'none';
            rejectedSec.style.display = 'block';
            rejectedBtn.classList.add('active');
            approvedBtn.classList.remove('active');
        }
    }

    function renderApprovedTable(searchQuery = '') {
        const approvedTbody = document.getElementById('approvedTableBody');
        approvedTbody.innerHTML = "";

        let approvedList = logData.filter(item => item.status === 'Approved');

        if (searchQuery.trim() !== '') {
            const query = searchQuery.toLowerCase().trim();
            approvedList = approvedList.filter(item => 
                item.nic.toLowerCase().includes(query) || item.name.toLowerCase().includes(query)
            );
        }

        if (approvedList.length === 0) {
            approvedTbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: #64748b; padding: 25px;">No approved records found.</td></tr>`;
        } else {
            approvedList.forEach((item, index) => {
                approvedTbody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td><strong>${item.appNo}</strong></td>
                        <td>${item.nic}</td>
                        <td>${item.compNo}</td>
                        <td>${item.name}</td>
                        <td>${item.desig}</td>
                        <td><span class="badge badge-approved"><i class="fa-solid fa-check" title="Approved"></i></span></td>
                    </tr>
                `;
            });
        }
    }

    function renderRejectedTable(searchQuery = '') {
        const rejectedTbody = document.getElementById('rejectedTableBody');
        rejectedTbody.innerHTML = "";

        let rejectedList = logData.filter(item => item.status === 'Rejected');

        if (searchQuery.trim() !== '') {
            const query = searchQuery.toLowerCase().trim();
            rejectedList = rejectedList.filter(item => 
                item.nic.toLowerCase().includes(query) || item.name.toLowerCase().includes(query)
            );
        }

        if (rejectedList.length === 0) {
            rejectedTbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: #64748b; padding: 25px;">No rejected records found.</td></tr>`;
        } else {
            rejectedList.forEach((item, index) => {
                rejectedTbody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td><strong>${item.appNo}</strong></td>
                        <td>${item.nic}</td>
                        <td>${item.compNo}</td>
                        <td>${item.name}</td>
                        <td>${item.desig}</td>
                        <td><span class="badge badge-rejected"><i class="fa-solid fa-xmark" title="Rejected"></i></span></td>
                        <td><span class="reason-text">${item.reason}</span></td>
                    </tr>
                `;
            });
        }
    }

    function filterApprovedTable() {
        const query = document.getElementById('approvedSearchInput').value;
        renderApprovedTable(query);
    }

    function checkApprovedClear(input) {
        if (input.value.trim() === '') {
            renderApprovedTable('');
        }
    }

    function filterRejectedTable() {
        const query = document.getElementById('rejectedSearchInput').value;
        renderRejectedTable(query);
    }

    function checkRejectedClear(input) {
        if (input.value.trim() === '') {
            renderRejectedTable('');
        }
    }

    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        
        if (tabParam === 'rejected') {
            switchTab('rejected');
        } else {
            switchTab('approved');
        }

        renderApprovedTable();
        renderRejectedTable();
    }
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>