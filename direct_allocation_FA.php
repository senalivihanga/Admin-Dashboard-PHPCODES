<?>
/**
 * Direct Allocations List - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Direct Allocations List - Sri Lanka Railways";

$userData = [
    'name' => 'Final Approver',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'FA'
];

$allocationsData = [
    [
        'nic' => '981234567V',
        'computerNo' => '4021',
        'fullName' => 'H. M. Gunawardena',
        'quarterNo' => 'Q-01',
        'quarterName' => 'Bungalow A',
        'quarterLocation' => 'Colombo',
        'quarterType' => 'Bungalows',
        'phone' => '0712345678',
        'reason' => 'Service Requirement'
    ],
    [
        'nic' => '901245678V',
        'computerNo' => '4189',
        'fullName' => 'P. R. Jayasinghe',
        'quarterNo' => 'Q-02',
        'quarterName' => 'Type II House',
        'quarterLocation' => 'Kandy',
        'quarterType' => 'Type II',
        'phone' => '0779876543',
        'reason' => 'Transfer Basis'
    ],
    [
        'nic' => '934512786V',
        'computerNo' => '5024',
        'fullName' => 'K. L. Wickramasinghe',
        'quarterNo' => 'Q-03',
        'quarterName' => 'Staff Flat',
        'quarterLocation' => 'Galle',
        'quarterType' => 'Type I',
        'phone' => '0751122334',
        'reason' => 'Medical Grounds'
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
        max-width: 1650px; 
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
        color: #571a1a; 
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

    .right-actions { 
        display: flex; 
        gap: 10px; 
        align-items: center; 
        flex-wrap: wrap; 
    }

    .btn-print { 
        background-color: #0284c7; 
        color: #ffffff; 
        padding: 10px 18px; 
        border-radius: 8px; 
        font-weight: 600; 
        font-size: 0.9rem; 
        border: none; 
        cursor: pointer; 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        text-decoration: none; 
        transition: background 0.2s; 
    }
    .btn-print:hover { 
        background-color: #0369a1; 
    }

    .add-btn { 
        background-color: #10b981; 
        color: white; 
        border: none; 
        padding: 10px 18px; 
        border-radius: 8px; 
        font-weight: 600; 
        cursor: pointer; 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        text-decoration: none; 
        transition: background 0.2s; 
        font-size: 0.9rem;
    }
    .add-btn:hover { 
        background-color: #059669; 
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

    .allocation-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: left; 
        min-width: 1200px; 
    }

    .allocation-table th, .allocation-table td { 
        padding: 12px 10px; 
        border: 1px solid #cbd5e1; 
        vertical-align: middle; 
        font-size: 0.85rem; 
        text-align: center; 
    }

    .allocation-table th { 
        background-color: #f8fafc; 
        color: #1e293b; 
        font-weight: 700; 
        text-transform: uppercase;
        font-size: 0.8rem;
    }

    .allocation-table td { 
        color: #334155; 
    }
    
    .action-cell { 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        gap: 6px; 
        flex-wrap: wrap; 
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
    }
    .btn-view { background-color: #8b5cf6; color: white; }
    .btn-view:hover { background-color: #7c3aed; }
    .btn-edit { background-color: #2563eb; color: white; }
    .btn-edit:hover { background-color: #1d4ed8; }
    .btn-delete { background-color: #dc2626; color: white; }
    .btn-delete:hover { background-color: #b91c1c; }

    .btn-app { 
        background-color: #059669; 
        color: white; 
        padding: 6px 12px; 
        border-radius: 6px; 
        font-weight: 600; 
        text-decoration: none; 
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        font-size: 0.8rem; 
        transition: background 0.2s; 
    }
    .btn-app:hover { background-color: #047857; }

    .custom-msg-box { 
        display: none; 
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(0,0,0,0.5); 
        justify-content: center; 
        align-items: center; 
        z-index: 1000; 
    }
    .msg-content { 
        background: white; 
        padding: 25px; 
        border-radius: 10px; 
        text-align: center; 
        width: 350px; 
        box-shadow: 0 5px 15px rgba(0,0,0,0.3); 
    }
    .msg-content p { 
        margin-bottom: 20px; 
        font-size: 1rem; 
        color: #334155; 
        font-weight: 600; 
    }
    .msg-btns { 
        display: flex; 
        justify-content: center; 
        gap: 10px; 
    }
    .msg-btn { 
        padding: 8px 15px; 
        border: none; 
        border-radius: 5px; 
        font-weight: 600; 
        cursor: pointer; 
    }
    .msg-btn-yes { background-color: #dc2626; color: white; }
    .msg-btn-no { background-color: #64748b; color: white; }

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
        @page {
            size: landscape;
            margin: 10mm;
        }
        body, html {
            background-color: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: auto !important;
        }
        .container {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .back-link, .search-card, .custom-msg-box, .user-profile-wrapper {
            display: none !important;
        }
        .table-card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
        }
        .allocation-table {
            width: 100% !important;
            min-width: 100% !important;
            font-size: 8pt !important;
        }
        .allocation-table th, .allocation-table td {
            padding: 6px 4px !important;
            border: 1px solid #333333 !important;
        }
        .allocation-table th {
            background-color: #f1f5f9 !important;
            color: #000000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .allocation-table th:nth-child(11), .allocation-table td:nth-child(11),
        .allocation-table th:nth-child(12), .allocation-table td:nth-child(12) {
            display: none !important;
        }
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
        .right-actions {
            width: 100%;
            justify-content: space-between;
        }
        .btn-print, .add-btn {
            flex: 1;
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
                <a href="FA_dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="page-title">Direct Allocations List</h2>

    <div class="search-card">
        <div class="search-wrapper">
            <input type="text" id="searchInput" class="search-input" placeholder="Search by NIC or Computer No..." oninput="if(this.value.trim() === '') searchTable()">
            <button class="search-btn" onclick="searchTable()"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
        
        <div class="right-actions">
            <button onclick="window.print()" class="btn-print">
                <i class="fa-solid fa-print"></i> Print / Save as PDF
            </button>
            <a href="add_direct_allocation_FA.php" class="add-btn"><i class="fa-solid fa-plus"></i> Add New Allocation</a>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="allocation-table" id="allocationTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIC</th>
                        <th>Computer No.</th>
                        <th>Full Name</th>
                        <th>Quarter No.</th>
                        <th>Quarter Name</th>
                        <th>Quarter Location</th>
                        <th>Quarter Type</th>
                        <th>Mobile</th>
                        <th>Reason</th>
                        <th>Application Action</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Dynamic rendering & LocalStorage sync -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="custom-msg-box" id="deleteModal">
    <div class="msg-content">
        <p><i class="fa-solid fa-triangle-exclamation" style="color: #dc2626; font-size: 1.5rem; margin-bottom: 10px; display:block;"></i> Are you sure you want to delete this allocation record?</p>
        <div class="msg-btns">
            <button class="msg-btn msg-btn-yes" id="confirmDeleteBtn">Yes, Delete</button>
            <button class="msg-btn msg-btn-no" onclick="closeDeleteModal()">Cancel</button>
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
    const serverAllocationsData = <?php echo json_encode($allocationsData); ?>;

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

    document.addEventListener('DOMContentLoaded', () => {
        const currentVersion = 'v9';
        if (localStorage.getItem('directAllocations_version') !== currentVersion) {
            localStorage.removeItem('directAllocations');
            localStorage.setItem('directAllocations_version', currentVersion);
        }
        loadTableData();
    });

    function loadTableData() {
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';

        let storedAllocations = JSON.parse(localStorage.getItem('directAllocations'));

        if (!storedAllocations || storedAllocations.length === 0) {
            storedAllocations = serverAllocationsData;
            localStorage.setItem('directAllocations', JSON.stringify(storedAllocations));
        }

        if (storedAllocations.length === 0) {
            tbody.innerHTML = `<tr><td colspan="12" style="text-align: center; color: #64748b; padding: 20px;">No direct allocations found.</td></tr>`;
            return;
        }

        storedAllocations.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${item.nic || item.NIC || '-'}</td>
                <td>${item.computerNo || item.computer_no || item.computerNumber || '-'}</td>
                <td>${item.fullName || item.full_name || item.name || '-'}</td>
                <td>${item.quarterNo || item.quarter_no || item.quarterNumber || item.quarter || '-'}</td>
                <td>${item.quarterName || item.quarter_name || item.qName || '-'}</td>
                <td>${item.quarterLocation || item.quarter_location || item.location || '-'}</td>
                <td>${item.quarterType || item.quarter_type || item.type || '-'}</td>
                <td>${item.phone || item.mobile || item.telephone || '-'}</td>
                <td>${item.reason || '-'}</td>
                <td>
                    <a href="language_dashboard.php" class="btn-app" title="Go to Application"><i class="fa-solid fa-arrow-up-right-from-square"></i> Go to App</a>
                </td>
                <td>
                    <div class="action-cell">
                        <a href="view_direct_allocation_FA.php?id=${index}" class="action-btn btn-view" title="View Details"><i class="fa-solid fa-eye"></i></a>
                        <a href="edit_direct_allocation_FA.php?id=${index}" class="action-btn btn-edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
                        <button class="action-btn btn-delete" onclick="promptDelete(${index})" title="Delete"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function searchTable() {
        const input = document.getElementById("searchInput").value.toLowerCase();
        const tbody = document.getElementById("tableBody");
        const trs = tbody.getElementsByTagName("tr");

        for (let i = 0; i < trs.length; i++) {
            const nicTd = trs[i].getElementsByTagName("td")[1];
            const compNoTd = trs[i].getElementsByTagName("td")[2];
            
            if (nicTd || compNoTd) {
                const nicText = nicTd ? nicTd.textContent.toLowerCase() : "";
                const compText = compNoTd ? compNoTd.textContent.toLowerCase() : "";

                if (nicText.includes(input) || compText.includes(input)) {
                    trs[i].style.display = "";
                } else {
                    trs[i].style.display = "none";
                }
            }
        }
    }

    let deleteTargetIndex = null;
    function promptDelete(index) {
        deleteTargetIndex = index;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        deleteTargetIndex = null;
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
        if (deleteTargetIndex !== null) {
            let allocations = JSON.parse(localStorage.getItem('directAllocations')) || [];
            allocations.splice(deleteTargetIndex, 1);
            localStorage.setItem('directAllocations', JSON.stringify(allocations));
            loadTableData();
            closeDeleteModal();
        }
    });
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>