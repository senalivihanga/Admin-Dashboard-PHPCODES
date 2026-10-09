<?>
/**
 * Allocated Quarters List - Sri Lanka Railways Admin
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Allocated Quarters List - Sri Lanka Railways Admin";

$userData = [
    'name' => 'Final Approver',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'FA'
];

$allocatedData = [
    [
        'nic' => '199812345678',
        'computerNo' => '101',
        'quarterNo' => 'Q-01',
        'quarterName' => 'Sunflower Manor',
        'quarterLocation' => 'Colombo 10',
        'quarterType' => 'Grade II',
        'residentName' => 'A. B. Perera',
        'designation' => 'Clerk',
        'workplace' => 'Colombo Fort',
        'allocatedDate' => '2026-01-10',
        'other' => 'None'
    ],
    [
        'nic' => '199912345678',
        'computerNo' => '102',
        'quarterNo' => 'Q-04',
        'quarterName' => 'Railway View',
        'quarterLocation' => 'Maradana',
        'quarterType' => 'Grade III',
        'residentName' => 'N. K. Silva',
        'designation' => 'Station Master',
        'workplace' => 'Maradana',
        'allocatedDate' => '2026-02-15',
        'other' => 'Sample record'
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

    .allocated-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: left; 
        min-width: 1400px; 
    }

    .allocated-table th, .allocated-table td { 
        padding: 12px 10px; 
        border: 1px solid #cbd5e1; 
        vertical-align: middle; 
        font-size: 0.85rem; 
        text-align: center; 
    }

    .allocated-table th { 
        background-color: #f8fafc; 
        color: #1e293b; 
        font-weight: 700; 
        text-transform: uppercase;
        font-size: 0.8rem;
    }

    .allocated-table td { 
        color: #334155; 
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
        .search-card {
            flex-direction: column;
            align-items: stretch;
        }
        .search-wrapper {
            max-width: 100%;
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

    <h2 class="page-title">Allocated Quarters List</h2>

    <div class="search-card">
        <div class="search-wrapper">
            <input type="text" id="searchInput" class="search-input" placeholder="Search by NIC, Computer No. or Quarter No..." oninput="if (this.value.trim() === '') searchTable()">
            <button class="search-btn" onclick="searchTable()"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
    </div>

    <div class="table-card">
        <table class="allocated-table" id="allocatedTable">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>NIC</th>
                    <th>Computer No.</th>
                    <th>Quarter No.</th>
                    <th>Quarter Name</th>
                    <th>Quarter Location</th>
                    <th>Quarter Type</th>
                    <th>Resident Name</th>
                    <th>Designation</th>
                    <th>Workplace</th>
                    <th>Allocated Date</th>
                    <th>Other</th>
                </tr>
            </thead>
            <tbody id="allocatedBody">
                <!-- PHP initial render & JS merge -->
            </tbody>
        </table>
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
    const serverAllocatedData = <?php echo json_encode($allocatedData); ?>;

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

    function getStoredData() {
        const data = localStorage.getItem('allocatedQuartersData');
        const storedData = data ? JSON.parse(data) : [];

        const mergedData = [...serverAllocatedData];
        storedData.forEach(item => {
            const isDuplicate = mergedData.some(existing =>
                existing.nic === item.nic && existing.computerNo === item.computerNo
            );
            if (!isDuplicate) {
                mergedData.push(item);
            }
        });

        return mergedData;
    }

    function loadTableData() {
        const tbody = document.getElementById('allocatedBody');
        tbody.innerHTML = '';
        const data = getStoredData();

        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="12" style="text-align: center; color: #64748b; padding: 25px;">No allocated quarters records found.</td></tr>`;
            return;
        }

        data.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${item.nic}</td>
                <td>${item.computerNo}</td>
                <td>${item.quarterNo || '-'}</td>
                <td>${item.quarterName || '-'}</td>
                <td>${item.quarterLocation || '-'}</td>
                <td>${item.quarterType}</td>
                <td>${item.residentName}</td>
                <td>${item.designation}</td>
                <td>${item.workplace}</td>
                <td>${item.allocatedDate}</td>
                <td>${item.other || 'None'}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    window.addEventListener('DOMContentLoaded', () => { 
        loadTableData(); 
    });

    function searchTable() {
        const input = document.getElementById("searchInput").value.toLowerCase();
        const tbody = document.getElementById("allocatedBody");
        const trs = tbody.getElementsByTagName("tr");

        for (let i = 0; i < trs.length; i++) {
            const nicTd = trs[i].getElementsByTagName("td")[1];
            const compNoTd = trs[i].getElementsByTagName("td")[2];
            const quarterNoTd = trs[i].getElementsByTagName("td")[3];

            if (nicTd || compNoTd || quarterNoTd) {
                const nicText = nicTd ? nicTd.textContent.toLowerCase() : "";
                const compText = compNoTd ? compNoTd.textContent.toLowerCase() : "";
                const quarterNoText = quarterNoTd ? quarterNoTd.textContent.toLowerCase() : "";

                if (nicText.includes(input) || compText.includes(input) || quarterNoText.includes(input)) {
                    trs[i].style.display = "";
                } else {
                    trs[i].style.display = "none";
                }
            }
        }
    }
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>