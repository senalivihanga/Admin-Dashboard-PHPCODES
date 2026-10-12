<?php
/**
 * Quarters List - Sri Lanka Railways Admin
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Quarters List - Sri Lanka Railways Admin";

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

    .container { 
        max-width: 1400px; 
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
        text-underline-offset: 8px; 
        font-weight: 700;
    }
    
    .section-header-bar { 
        background: #ffffff; 
        padding: 20px; 
        border-radius: 12px; 
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
        border: 1px solid #e2e8f0; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 25px; 
        flex-wrap: wrap; 
        gap: 15px; 
    }
    
    .search-wrapper { display: flex; gap: 10px; align-items: center; width: 100%; max-width: 450px; }
    .search-box-wrapper { position: relative; display: flex; align-items: center; flex: 1; }
    .search-box-wrapper i { position: absolute; left: 14px; color: #64748b; font-size: 0.9rem; }
    .search-input { padding: 10px 14px 10px 40px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.9rem; width: 100%; outline: none; transition: all 0.2s; background-color: #ffffff; }
    .search-input:focus { border-color: #7A1C1C; box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1); }

    .search-btn { background-color: #7A1C1C; color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background 0.2s; font-size: 0.9rem; }
    .search-btn:hover { background-color: #5c1414; }

    .btn-group { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .add-quarter-btn { background-color: #10b981; color: white; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: background 0.2s; }
    .add-quarter-btn:hover { background-color: #059669; }
    .maint-page-btn { background-color: #2563eb; color: white; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: background 0.2s; }
    .maint-page-btn:hover { background-color: #1d4ed8; }
    .btn-print { background-color: #0284c7; color: #ffffff; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: background 0.2s; }
    .btn-print:hover { background-color: #0369a1; }

    .table-card { background: #ffffff; padding: 25px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-top: 5px solid #7A1C1C; overflow-x: auto; position: relative; border: 1px solid #e2e8f0; }
    .table-responsive { width: 100%; overflow-x: auto; }
    .quarters-table { width: 100%; max-width: 100%; table-layout: auto; border-collapse: collapse; text-align: left; min-width: 1200px; }
    
    .quarters-table th, .quarters-table td { 
        padding: 15px 12px; 
        border: 1px solid #e2e8f0; 
        vertical-align: middle; 
        font-size: 0.85rem; 
        text-align: center; 
    }
    
    .quarters-table th { background-color: #f8fafc; color: #1e293b; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    .quarters-table td { color: #4b5563; font-weight: normal; }
    .quarters-table tbody tr { transition: background-color 0.15s ease; }
    .quarters-table tbody tr:hover { background-color: #f1f5f9; }
    
    .facility-container { display: grid; gap: 6px; }
    .facility-item { display: flex; align-items: center; gap: 8px; justify-content: center; }
    .facility-label { min-width: 72px; color: #64748b; font-weight: 600; text-align: left; }
    .badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .badge-active { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-inactive { background-color: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

    .action-cell { display: flex; justify-content: center; align-items: center; gap: 6px; }
    .action-btn { border: none; width: 32px; height: 32px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: background-color 0.2s; }
    .btn-view { background-color: #8b5cf6; color: white; }
    .btn-view:hover { background-color: #7c3aed; }
    .btn-edit { background-color: #3b82f6; color: white; }
    .btn-edit:hover { background-color: #2563eb; }
    .btn-delete { background-color: #dc2626; color: white; }
    .btn-delete:hover { background-color: #b91c1c; }
    
    .custom-msg-box { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
    .msg-content { background: white; padding: 25px; border-radius: 10px; text-align: center; width: 350px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
    .msg-content p { margin-bottom: 20px; font-size: 1rem; color: #334155; font-weight: 600; }
    .msg-btns { display: flex; justify-content: center; gap: 10px; }
    .msg-btn { padding: 8px 15px; border: none; border-radius: 5px; font-weight: 600; cursor: pointer; }
    .msg-btn-yes { background-color: #dc2626; color: white; }
    .msg-btn-no { background-color: #64748b; color: white; }

    .system-toast {
        position: fixed;
        right: 24px;
        top: 24px;
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #86efac;
        border-left: 6px solid #22c55e;
        padding: 14px 18px;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(34, 197, 94, 0.18);
        display: none;
        z-index: 1100;
        font-weight: 700;
        align-items: center;
        gap: 10px;
        max-width: 380px;
        line-height: 1.4;
    }
    .system-toast i {
        color: #16a34a;
        font-size: 1.1rem;
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
        body { background-color: #ffffff; color: #000000; font-size: 9pt; }
        .container { max-width: 100%; margin: 0; padding: 0; }
        .back-link, .section-header-bar, .action-cell, th:last-child, td:last-child, .user-profile-wrapper { display: none !important; }
        .page-title { font-size: 1.3rem; margin-bottom: 10px; color: #000000; text-decoration: underline; }
        .table-card { border: none !important; box-shadow: none !important; padding: 0 !important; background: transparent !important; }
        .table-responsive { overflow: visible !important; width: 100%; }
        .quarters-table { width: 100% !important; min-width: 100% !important; border: 1px solid #000000; }
        .quarters-table th, .quarters-table td { border: 1px solid #000000; padding: 4px 3px; color: #000000; font-weight: normal; text-align: center; }
        .quarters-table th { background-color: #f0f0f0 !important; color: #000000 !important; }
    }

    @media (max-width: 768px) {
        .container {
            margin: 15px auto;
            padding: 0 10px;
        }
        .section-header-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .search-wrapper {
            max-width: 100%;
        }
        .btn-group {
            width: 100%;
            justify-content: stretch;
        }
        .btn-group > * {
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
                <a href="quarters_list.php"><i class="fa-solid fa-arrow-left"></i> Back to Quarters List</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="page-title">Quarters Details</h2>

    <div class="section-header-bar">
        <div class="search-wrapper">
            <div class="search-box-wrapper">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Search by Quarter No or Name..." oninput="searchTable()">
            </div>
            <button class="search-btn" onclick="searchTable()"><i class="fa-solid fa-search"></i> Search</button>
        </div>

        <div class="btn-group" id="actionBtnGroup">
            <a href="add_quarter.php" class="add-quarter-btn" id="addQuarterBtn"><i class="fa-solid fa-plus-circle"></i> Add Quarter</a>
            <a href="maintenance.php" class="maint-page-btn" id="maintBtn"><i class="fa-solid fa-screwdriver-wrench"></i> Maintenance</a>
            <button onclick="window.print()" class="btn-print" id="printBtn">
                <i class="fa-solid fa-print"></i> Print / Save as PDF
            </button>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="quarters-table" id="quartersTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Quarter No.</th>
                        <th>Quarter Name</th>
                        <th>Quarter Division</th>
                        <th>Ownership</th>
                        <th>Quarter Area (sq.ft)</th>
                        <th>Quarter Location</th>
                        <th>Address</th>
                        <th>Quarter Type</th>
                        <th>Assessment Rent (LKR)</th>
                        <th>Status</th>
                        <th>Facility (Electricity & Water)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="quartersBody">
                    <!-- Dynamic rows via JavaScript / localStorage or PHP backend -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Custom Delete Confirmation Modal -->
<div class="custom-msg-box" id="deleteModal">
    <div class="msg-content">
        <p><i class="fa-solid fa-triangle-exclamation" style="color: #dc2626; font-size: 1.5rem; margin-bottom: 10px; display:block;"></i> Are you sure you want to delete this quarter record?</p>
        <div class="msg-btns">
            <button class="msg-btn msg-btn-yes" id="confirmDeleteBtn">Yes, Delete</button>
            <button class="msg-btn msg-btn-no" onclick="closeDeleteModal()">Cancel</button>
        </div>
    </div>
</div>

<div id="systemToast" class="system-toast" style="display: none;"><i class="fa-solid fa-circle-check"></i> Quarter record deleted successfully!</div>

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

    const defaultData = [
        {
            quarterNum: "Q-01",
            quarterName: "Bungalow A",
            division: "Way & Works",
            ownership: "Railway Department",
            quarterArea: "1,450 sq.ft",
            location: "Colombo",
            address: "No. 12, Railway Avenue, Colombo 10",
            quarterType: "Bungalows",
            assessmentRent: "5,500.00",
            status: "Under Maintenance",
            electric: true,
            water: true
        },
        {
            quarterNum: "Q-02",
            quarterName: "Type II House",
            division: "Signal & Telecommunication",
            ownership: "Railway Department",
            quarterArea: "950 sq.ft",
            location: "Kandy",
            address: "No. 8, Peradeniya Road, Kandy",
            quarterType: "Type II",
            assessmentRent: "4,200.00",
            status: "Available",
            electric: true,
            water: true
        }
    ];

    function getStoredData() {
        const data = localStorage.getItem('quartersData_v2');
        let parsed = null;
        try {
            parsed = data ? JSON.parse(data) : null;
        } catch (e) {
            parsed = null;
        }

        if (!Array.isArray(parsed) || parsed.length === 0 || !parsed[0].quarterNum) {
            localStorage.setItem('quartersData_v2', JSON.stringify(defaultData));
            return [...defaultData];
        }

        return parsed;
    }

    function saveStoredData(data) {
        localStorage.setItem('quartersData_v2', JSON.stringify(data));
    }

    window.addEventListener('DOMContentLoaded', () => { 
        loadTableData(); 
    });

    function loadTableData() {
        const tbody = document.getElementById('quartersBody');
        tbody.innerHTML = '';
        const data = getStoredData();

        data.forEach((item, index) => {
            const electricBadge = item.electric ? `<span class="badge badge-active"><i class="fa-solid fa-bolt"></i> Available</span>` : `<span class="badge badge-inactive"><i class="fa-solid fa-bolt"></i> Unavailable</span>`;
            const waterBadge = item.water ? `<span class="badge badge-active"><i class="fa-solid fa-droplet"></i> Available</span>` : `<span class="badge badge-inactive"><i class="fa-solid fa-droplet"></i> Unavailable</span>`;

            const tr = document.createElement('tr');
            
            const actionsHtml = `
                <div class="action-cell">
                    <a href="view-quarters-list.php?index=${index}" class="action-btn btn-view" title="View Details"><i class="fa-solid fa-eye"></i></a>
                    <a href="edit-quarter.php?index=${index}" class="action-btn btn-edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    <button class="action-btn btn-delete" onclick="promptDelete(${index})" title="Delete"><i class="fa-solid fa-trash"></i></button>
                </div>
            `;

            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${item.quarterNum || 'N/A'}</td>
                <td>${item.quarterName || 'N/A'}</td>
                <td>${item.division || 'N/A'}</td>
                <td>${item.ownership || 'N/A'}</td>
                <td>${item.quarterArea || 'N/A'}</td>
                <td>${item.location || 'N/A'}</td>
                <td>${item.address || 'N/A'}</td>
                <td>${item.quarterType || 'N/A'}</td>
                <td>Rs. ${item.assessmentRent || '0.00'}</td>
                <td>${item.status || 'N/A'}</td>
                <td>
                    <div class="facility-container">
                        <div class="facility-item"><span class="facility-label">Electricity</span>${electricBadge}</div>
                        <div class="facility-item"><span class="facility-label">Water</span>${waterBadge}</div>
                    </div>
                </td>
                <td>${actionsHtml}</td>
            `;
            
            tbody.appendChild(tr);
        });
    }

    function searchTable() {
        const input = document.getElementById("searchInput");
        const filter = input.value.toLowerCase();
        const table = document.getElementById("quartersTable");
        const tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            const tdQuarterNo = tr[i].getElementsByTagName("td")[1];
            const tdQuarterName = tr[i].getElementsByTagName("td")[2];
            
            let rowMatched = false;
            
            if (tdQuarterNo || tdQuarterName) {
                const noText = tdQuarterNo ? (tdQuarterNo.textContent || tdQuarterNo.innerText).toLowerCase() : "";
                const nameText = tdQuarterName ? (tdQuarterName.textContent || tdQuarterName.innerText).toLowerCase() : "";
                
                if (noText.indexOf(filter) > -1 || nameText.indexOf(filter) > -1) {
                    rowMatched = true;
                }
            }

            tr[i].style.display = rowMatched ? "" : "none";
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
            const data = getStoredData();
            data.splice(deleteTargetIndex, 1);
            saveStoredData(data);
            loadTableData();
            closeDeleteModal();

            const toast = document.getElementById('systemToast');
            toast.style.display = 'flex';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 2200);
        }
    });
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>