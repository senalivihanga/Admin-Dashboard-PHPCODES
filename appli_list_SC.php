<?php
/**
 * Total Request Applications - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Total Request Applications - Sri Lanka Railways";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$requestData = [
    [
        'appNo' => 'SLR/AOT/2026/0001',
        'nic' => '854712093V',
        'computerNo' => '4021',
        'name' => 'H. M. Gunawardena',
        'designation' => 'Station Master',
        'subDate' => '2026-09-07',
        'quarterType' => 'Grade B Quarters',
        'reqDate' => '2026-09-06'
    ],
    [
        'appNo' => 'SLR/AOT/2026/0002',
        'nic' => '901245678V',
        'computerNo' => '4189',
        'name' => 'P. R. Jayasinghe',
        'designation' => 'Permanent Way Inspector',
        'subDate' => '2026-09-05',
        'quarterType' => 'Grade A Quarters',
        'reqDate' => '2026-09-04'
    ],
    [
        'appNo' => 'SLR/AOT/2026/0003',
        'nic' => '934512786V',
        'computerNo' => '5024',
        'name' => 'K. L. Wickramasinghe',
        'designation' => 'Locomotive Driver',
        'subDate' => '2026-08-30',
        'quarterType' => 'Grade C Quarters',
        'reqDate' => '2026-08-28'
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
        max-width: 1750px; 
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
        color: #5a1313; 
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
        transition: all 0.2s;
    }
    .search-input:focus { 
        border-color: #0284c7; 
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1); 
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

    .table-card { 
        background: #ffffff; 
        padding: 25px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid #7A1C1C; 
        overflow-x: auto; 
        position: relative; 
    }

    .data-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: left; 
        min-width: 1200px;
    }
    .data-table th, .data-table td { 
        padding: 12px 10px; 
        border: 1px solid #cbd5e1; 
        vertical-align: middle; 
        font-size: 0.85rem; 
        text-align: center; 
    }
    .data-table th { 
        background-color: #f8fafc; 
        color: #0f172a; 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }
    .data-table td { 
        color: #334155; 
    }

    .app-no { color: #333333; font-family: monospace; font-weight: 700; }
    .applicant-nic { font-family: monospace; color: #333333; }
    .computer-no { font-family: monospace; color: #333333; }
    .badge-type { color: #333333; }

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
        body { background-color: #ffffff; color: #000000; font-size: 9pt; }
        .container { max-width: 100%; margin: 0; padding: 0; width: 100%; }
        .back-link, .print-btn, .search-card, .user-profile-wrapper { display: none !important; }
        .page-title { font-size: 1.3rem; margin-bottom: 10px; color: #000000; text-decoration: underline; }
        .table-card { box-shadow: none !important; border: none !important; padding: 0 !important; overflow: visible !important; }
        .data-table { width: 100% !important; border: 1px solid #000000; min-width: 100% !important; }
        .data-table th, .data-table td { border: 1px solid #000000; padding: 6px 4px; color: #000000; }
        .data-table th { background-color: #f0f0f0 !important; color: #000000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
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
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
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

    <h2 class="page-title">Total Request Applications</h2>

    <div class="search-card">
        <div class="search-wrapper">
            <input type="text" id="searchInput" class="search-input" placeholder="Search by Application No, NIC or Name..." oninput="if (this.value.trim() === '') { searchTable(); }">
            <button class="search-btn" onclick="searchTable()"><i class="fa-solid fa-search"></i> Search</button>
        </div>

        <button type="button" class="print-btn" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Print / Save as PDF
        </button>
    </div>

    <div class="table-card">
        <table class="data-table" id="requestTable">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Application Number</th>
                    <th>NIC</th>
                    <th>Computer Number</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Application Submitted Date</th>
                    <th>Request Quarter Type</th>
                    <th>Request Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requestData)): ?>
                    <tr><td colspan="9" style="text-align: center; color: #64748b; padding: 25px;">No applications found.</td></tr>
                <?php else: ?>
                    <?php foreach ($requestData as $index => $row): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><span class="app-no"><?php echo htmlspecialchars($row['appNo']); ?></span></td>
                            <td><span class="applicant-nic"><?php echo htmlspecialchars($row['nic']); ?></span></td>
                            <td><span class="computer-no"><?php echo htmlspecialchars($row['computerNo']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['designation']); ?></td>
                            <td><?php echo htmlspecialchars($row['subDate']); ?></td>
                            <td><span class="badge-type"><?php echo htmlspecialchars($row['quarterType']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['reqDate']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
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

    function searchTable() {
        const input = document.getElementById("searchInput");
        const filter = input.value.toLowerCase().trim();
        const table = document.getElementById("requestTable");
        const rows = table.getElementsByTagName("tr");

        for (let i = 1; i < rows.length; i++) {
            const appNoTd = rows[i].getElementsByTagName("td")[1];
            const nicTd = rows[i].getElementsByTagName("td")[2];
            const nameTd = rows[i].getElementsByTagName("td")[4];

            const appNoText = appNoTd ? (appNoTd.textContent || appNoTd.innerText) : "";
            const nicText = nicTd ? (nicTd.textContent || nicTd.innerText) : "";
            const nameText = nameTd ? (nameTd.textContent || nameTd.innerText) : "";

            const matches = !filter ||
                appNoText.toLowerCase().includes(filter) ||
                nicText.toLowerCase().includes(filter) ||
                nameText.toLowerCase().includes(filter);

            rows[i].style.display = matches ? "" : "none";
        }
    }
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>