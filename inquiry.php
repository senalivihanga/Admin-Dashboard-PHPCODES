<?php
/**
 * My Inquiries & Complaints - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "My Inquiries & Complaints - Sri Lanka Railways";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$inquiriesData = [
    [
        'nic' => '883421092V',
        'name' => 'A. B. Perera',
        'complaint' => 'Low water pressure during morning hours in the kitchen area.',
        'issueType' => 'Water Supply',
        'issueClass' => 'issue-water',
        'issueIcon' => 'fa-droplet',
        'date' => '2026-09-08',
        'status' => 'Pending',
        'statusClass' => 'status-pending',
        'statusIcon' => 'fa-clock'
    ],
    [
        'nic' => '925631485V',
        'name' => 'S. K. Silva',
        'complaint' => 'Main street light pole outside quarter gate is flickering continuously.',
        'issueType' => 'Electricity / Light',
        'issueClass' => 'issue-electricity',
        'issueIcon' => 'fa-bolt',
        'date' => '2026-09-06',
        'status' => 'In Progress',
        'statusClass' => 'status-progress',
        'statusIcon' => 'fa-spinner fa-spin'
    ],
    [
        'nic' => '951234789V',
        'name' => 'M. N. Fernando',
        'complaint' => 'Bathroom window latch is broken and needs replacement.',
        'issueType' => 'Quarter Maintenance',
        'issueClass' => 'issue-maintenance',
        'issueIcon' => 'fa-house-chimney-crack',
        'date' => '2026-09-01',
        'status' => 'Resolved',
        'statusClass' => 'status-resolved',
        'statusIcon' => 'fa-check'
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
    
    .top-action-bar {
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
        background-color: #ffffff;
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

    .management-section { 
        background: #ffffff; 
        padding: 25px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border: 1px solid #e2e8f0;
        border-top: 5px solid #7A1C1C; 
        margin-bottom: 40px; 
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .data-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: center; 
        min-width: 1200px; 
    }
    .data-table th, .data-table td { 
        padding: 14px 16px; 
        border: 1px solid #e2e8f0; 
        vertical-align: middle; 
        font-size: 0.85rem; 
    }
    .data-table th { 
        background-color: #f8fafc; 
        color: #1e293b; 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 0.8rem; 
        letter-spacing: 0.5px; 
        text-align: center; 
    }
    .data-table td { 
        color: #334155; 
        text-align: center; 
    }
    .data-table tbody tr { 
        transition: background-color 0.15s ease; 
    }
    .data-table tbody tr:hover { 
        background-color: #f1f5f9; 
    }

    .applicant-info { display: flex; flex-direction: column; gap: 2px; text-align: left; }
    .applicant-name { font-weight: normal; color: #111827; font-size: 0.85rem; }
    .applicant-nic { font-size: 0.75rem; color: #6b7280; font-family: monospace; letter-spacing: 0.5px; }

    .badge-issue { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 9999px; font-weight: normal; font-size: 0.75rem; gap: 4px; }
    .issue-water { background-color: #e0f2fe; color: #0369a1; }
    .issue-electricity { background-color: #fef9c3; color: #a16207; }
    .issue-maintenance { background-color: #fee2e2; color: #991b1b; }
    .issue-other { background-color: #f3f4f6; color: #374151; }

    .badge-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; font-weight: normal; font-size: 0.75rem; }
    .status-pending { background-color: #fff7ed; color: #c2410c; }
    .status-progress { background-color: #fef9c3; color: #854d0e; }
    .status-resolved { background-color: #f0fdf4; color: #15803d; }

    .complaint-text { width: 100%; max-width: 350px; white-space: normal; word-break: break-word; color: #1f2937; font-weight: normal; text-align: left; }

    #noResultsRow {
        display: none;
        text-align: center;
        color: #64748b;
        font-weight: 500;
        padding: 20px;
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
        body {
            background-color: #ffffff;
        }
        .back-link, .top-action-bar, .user-profile-wrapper {
            display: none !important;
        }
        .management-section {
            box-shadow: none;
            border: none;
            padding: 0;
            border-top: none;
        }
        .table-responsive {
            overflow: visible !important;
        }
        .data-table {
            width: 100% !important;
            min-width: 100% !important;
        }
    }

    @media (max-width: 768px) {
        .container {
            margin: 15px auto;
            padding: 0 10px;
        }
        .top-action-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .search-wrapper {
            max-width: 100%;
        }
        .btn-print {
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

    <h2 class="page-title">Inquiries & Complaints</h2>

    <div class="top-action-bar">
        <div class="search-wrapper">
            <input type="text" id="searchInput" class="search-input" placeholder="Search by NIC or Name..." oninput="handleSearchInput(this)">
            <button type="button" class="search-btn" onclick="searchTable()"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
        
        <button onclick="window.print()" class="btn-print">
            <i class="fa-solid fa-print"></i> Print / Save as PDF
        </button>
    </div>

    <div class="management-section">
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="data-table" id="inquiryTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Applicant NIC</th>
                        <th>Applicant Name</th>
                        <th>Complaints Description</th>
                        <th>Related Issue</th>
                        <th>Submitted Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inquiriesData)): ?>
                        <tr><td colspan="7" style="text-align: center; color: #64748b; padding: 25px;">No matching inquiries found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($inquiriesData as $index => $row): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><span class="applicant-nic"><?php echo htmlspecialchars($row['nic']); ?></span></td>
                                <td><span class="applicant-name"><?php echo htmlspecialchars($row['name']); ?></span></td>
                                <td>
                                    <div class="complaint-text">
                                        <?php echo htmlspecialchars($row['complaint']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-issue <?php echo htmlspecialchars($row['issueClass']); ?>">
                                        <i class="fa-solid <?php echo htmlspecialchars($row['issueIcon']); ?>"></i> <?php echo htmlspecialchars($row['issueType']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td>
                                    <span class="badge-status <?php echo htmlspecialchars($row['statusClass']); ?>">
                                        <i class="fa-solid <?php echo htmlspecialchars($row['statusIcon']); ?>"></i> <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div id="noResultsRow">No matching inquiries found.</div>
        </div>
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
        const filter = input.value.trim().toLowerCase();
        const table = document.getElementById("inquiryTable");
        const tr = table.getElementsByTagName("tr");
        const noResults = document.getElementById("noResultsRow");
        let visibleCount = 0;

        if (filter === "") {
            for (let i = 1; i < tr.length; i++) {
                if (tr[i].id !== 'noResultsRow') tr[i].style.display = "";
            }
            noResults.style.display = "none";
            return;
        }

        for (let i = 1; i < tr.length; i++) {
            if (tr[i].id === 'noResultsRow') continue;
            const tdNic = tr[i].getElementsByTagName("td")[1];
            const tdName = tr[i].getElementsByTagName("td")[2];
            
            let rowMatched = false;
            
            if (tdNic || tdName) {
                const nicText = tdNic ? (tdNic.textContent || tdNic.innerText).toLowerCase() : "";
                const nameText = tdName ? (tdName.textContent || tdName.innerText).toLowerCase() : "";
                
                if (nicText.indexOf(filter) > -1 || nameText.indexOf(filter) > -1) {
                    rowMatched = true;
                }
            }

            tr[i].style.display = rowMatched ? "" : "none";
            if (rowMatched) visibleCount++;
        }

        noResults.style.display = visibleCount === 0 ? "block" : "none";
    }

    function handleSearchInput(input) {
        if (input.value.trim() === "") {
            searchTable();
        }
    }
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>