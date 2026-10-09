<?php
/**
 * Application Approval List - Sri Lanka Railways Admin
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Application Approval List - Sri Lanka Railways Admin";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$approvalData = [
    [
        'id' => 1,
        'appNo' => 'SLR/AOT/2026/0001',
        'nic' => '981234567V',
        'compNo' => '1054',
        'name' => 'A. B. Perera',
        'desig' => 'Station Master'
    ],
    [
        'id' => 2,
        'appNo' => 'SLR/AOT/2026/0002',
        'nic' => '957891234V',
        'compNo' => '1055',
        'name' => 'K. D. Silva',
        'desig' => 'Signal Inspector'
    ],
    [
        'id' => 3,
        'appNo' => 'SLR/AOT/2026/0003',
        'nic' => '923456789V',
        'compNo' => '1056',
        'name' => 'M. N. Fernando',
        'desig' => 'Locomotive Driver'
    ]
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
        max-width: 1500px; 
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
        font-size: 1.8rem; 
        margin-bottom: 25px; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        text-decoration: underline; 
        text-underline-offset: 6px; 
        font-weight: 700;
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

    .table-card { 
        background: #ffffff; 
        padding: 25px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid #7A1C1C; 
        overflow-x: auto; 
        position: relative; 
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

    .approval-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: left; 
        min-width: 1000px;
    }

    .approval-table th, .approval-table td { 
        padding: 12px 10px; 
        border: 1px solid #cbd5e1; 
        vertical-align: middle; 
        font-size: 0.85rem; 
        text-align: center; 
    }

    .approval-table th { 
        background-color: #f8fafc; 
        color: #1e293b; 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 0.8rem;
    }

    .approval-table td { 
        color: #334155; 
    }
        
    .action-cell { 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        gap: 6px; 
    }

    .action-btn { 
        border: none; 
        width: 32px; 
        height: 32px; 
        border-radius: 6px; 
        font-weight: 600; 
        cursor: pointer; 
        font-size: 0.85rem; 
        text-decoration: none; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        transition: background-color 0.2s; 
        color: white; 
    }
    .btn-view { background-color: #8b5cf6; }
    .btn-view:hover { background-color: #7c3aed; }
    .btn-approve { background-color: #10b981; }
    .btn-approve:hover { background-color: #059669; }

    .system-alert {
        background-color: #f0fdf4;
        color: #166534;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 600;
        border: 1px solid #bbf7d0;
        display: none;
        align-items: center;
        gap: 10px;
    }

    .fade-out {
        opacity: 0;
        transform: scale(0.98);
        transition: all 0.4s ease;
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

    @media print {
        @page { size: landscape; margin: 10mm; }
        body { background-color: #ffffff !important; -webkit-print-color-adjust: exact; }
        .back-link, .search-card, .system-alert, .user-profile-wrapper { display: none !important; }
        .container { max-width: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
        .table-card { box-shadow: none !important; border: none !important; padding: 0 !important; overflow: visible !important; }
        .approval-table { width: 100% !important; font-size: 8pt !important; }
        .approval-table th, .approval-table td { padding: 6px 4px !important; }
        th:nth-child(7), td:nth-child(7),
        th:nth-child(8), td:nth-child(8) { display: none !important; }
    }

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
                <a href="SC.php"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="page-title">Application Approval List</h2>

    <div class="search-card">
        <div class="search-wrapper">
            <input type="text" id="searchInput" class="search-input" placeholder="Search by NIC or Name..." oninput="checkSearchClear(this)">
            <button class="search-btn" onclick="filterTable()"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
        <button type="button" class="print-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
    </div>

    <div id="systemAlert" class="system-alert">
        <i id="alertIcon" class="fa-solid fa-circle-check"></i> 
        <span id="alertMessageText">Action completed successfully.</span>
    </div>

    <div class="table-card">
        <table class="approval-table" id="approvalTable">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Application Number</th>
                    <th>NIC</th>
                    <th>Computer Number</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>View Application</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <!-- Dynamic rows -->
            </tbody>
        </table>
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
    const serverApprovalData = <?php echo json_encode($approvalData); ?>;
    let applicationData = [...serverApprovalData];

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
        renderTable(applicationData); 
    });

    function renderTable(dataToRender) {
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';

        if (dataToRender.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: #64748b; padding: 30px;">No applications found.</td></tr>`;
            return;
        }

        dataToRender.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.id = `row-${item.id}`;
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td><strong>${item.appNo}</strong></td>
                <td>${item.nic}</td>
                <td>${item.compNo}</td>
                tek ${item.name}</td>
                <td>${item.desig}</td>
                <td>
                    <div class="action-cell">
                        <a href="view-application-details.php?id=${item.id}" class="action-btn btn-view" title="View Application"><i class="fa-solid fa-eye"></i></a>
                    </div>
                </td>
                <td>
                    <div class="action-cell">
                        <button class="action-btn btn-approve" onclick="handleApprove(${item.id})" title="Approve"><i class="fa-solid fa-check"></i></button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filterTable() {
        const input = document.getElementById("searchInput").value.toLowerCase().trim();
        const filtered = applicationData.filter(item => 
            item.nic.toLowerCase().includes(input) || item.name.toLowerCase().includes(input)
        );
        renderTable(filtered);
    }

    function checkSearchClear(input) {
        if (input.value.trim() === "") {
            renderTable(applicationData);
        }
    }

    function showSystemAlert(msg) {
        const alertBox = document.getElementById('systemAlert');
        const alertText = document.getElementById('alertMessageText');
        alertText.innerText = msg;
        alertBox.style.display = 'flex';
        setTimeout(() => { alertBox.style.display = 'none'; }, 4000);
    }

    function handleApprove(id) {
        const index = applicationData.findIndex(item => item.id == id);

        if (index > -1) {
            const approvedItem = applicationData[index];
            let approvedList = JSON.parse(localStorage.getItem('approvedApplications')) || [];
            approvedList.push(approvedItem);
            localStorage.setItem('approvedApplications', JSON.stringify(approvedList));

            showSystemAlert("Application approved successfully and moved to Approved List.");
            
            const row = document.getElementById(`row-${id}`);
            if (row) {
                row.classList.add('fade-out');
            }
            
            setTimeout(() => {
                applicationData.splice(index, 1);
                filterTable();
            }, 400);
        }
    }
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>