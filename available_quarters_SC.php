<?php
/**
 * Available Quarters - Sri Lanka Railways Admin
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Available Quarters - Sri Lanka Railways Admin";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$availableQuartersData = [
    [
        'quarterNo' => 'Q-01',
        'location' => 'Colombo',
        'quarterName' => 'Bungalow A',
        'address' => 'No. 12, Railway Avenue, Colombo 10',
        'quarterType' => 'Bungalows',
        'assessmentRent' => '5500.00',
        'status' => 'Under Maintenance',
        'statusColor' => '#d97706',
        'electric' => true,
        'water' => true
    ],
    [
        'quarterNo' => 'Q-02',
        'location' => 'Maradana',
        'quarterName' => 'Quarters B',
        'address' => 'No. 45, Station Road, Maradana',
        'quarterType' => 'Grade III',
        'assessmentRent' => '3500.00',
        'status' => 'Available',
        'statusColor' => '#16a34a',
        'electric' => true,
        'water' => false
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
        max-width: 1450px; 
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
        padding: 30px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid #7A1C1C; 
        margin-bottom: 40px; 
    }

    .data-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: left; 
        min-width: 1100px; 
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
        color: #1e293b; 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 0.78rem; 
    }
    .data-table td { 
        color: #334155; 
    }
    
    .data-table td:nth-child(4), 
    .data-table td:nth-child(5) { 
        text-align: left; 
    }

    .badge-status {
        font-weight: 600;
    }
    
    .facility-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin: 4px 0;
    }

    .facility-label {
        width: 75px;
        text-align: right;
        color: #334155;
    }

    .badge-available {
        background-color: #d1fae5;
        color: #065f46;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .badge-unavailable {
        background-color: #fee2e2;
        color: #b91c1c;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

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

    @page {
        size: landscape;
        margin: 10mm;
    }

    @media print {
        .container {
            max-width: none;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .page-title {
            margin-bottom: 12px;
        }

        .back-link, .top-action-bar, .user-profile-wrapper {
            display: none !important;
        }

        .management-section {
            width: 100%;
            margin: 0;
            border: none;
            box-shadow: none;
            padding: 0;
        }

        #availableTableContainer {
            overflow: visible !important;
        }

        .data-table {
            width: 100%;
            min-width: 0;
            table-layout: fixed;
            font-size: 7pt;
        }

        .data-table th,
        .data-table td {
            padding: 4px 3px;
            font-size: 7pt;
            overflow-wrap: anywhere;
        }

        .data-table th:nth-child(1), .data-table td:nth-child(1) { width: 5%; }
        .data-table th:nth-child(2), .data-table td:nth-child(2) { width: 8%; }
        .data-table th:nth-child(3), .data-table td:nth-child(3) { width: 10%; }
        .data-table th:nth-child(4), .data-table td:nth-child(4) { width: 10%; }
        .data-table th:nth-child(5), .data-table td:nth-child(5) { width: 18%; }
        .data-table th:nth-child(6), .data-table td:nth-child(6) { width: 10%; }
        .data-table th:nth-child(7), .data-table td:nth-child(7) { width: 12%; }
        .data-table th:nth-child(8), .data-table td:nth-child(8) { width: 11%; }
        .data-table th:nth-child(9), .data-table td:nth-child(9) { width: 16%; }

        .data-table thead {
            display: table-header-group;
        }

        .data-table tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .facility-row {
            display: block;
            margin: 2px 0;
        }

        .facility-label {
            width: auto;
            text-align: center;
        }

        #noResultsRow {
            display: none !important;
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
                <a href="assign-quarters-SC.php"><i class="fa-solid fa-arrow-left"></i> Back to Assign Quarters Management</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="page-title">Available Quarters Portal</h2>

    <div class="top-action-bar">
        <div class="search-wrapper">
            <input type="text" id="searchInput" class="search-input" placeholder="Search by Quarter No, Location or Name..." oninput="handleSearchInput(this)">
            <button type="button" class="search-btn" onclick="searchTable()"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
        
        <button onclick="window.print()" class="btn-print">
            <i class="fa-solid fa-print"></i> Print / Save as PDF
        </button>
    </div>

    <div class="management-section" id="availableQuartersTile">
        <div id="availableTableContainer" class="table-responsive" style="overflow-x: auto;">
            <table class="data-table" id="quartersTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Quarter No.</th>
                        <th>Quarter Location</th>
                        <th>Quarter Name</th>
                        <th>Address</th>
                        <th>Quarter Type</th>
                        <th>Assessment Rent (LKR)</th>
                        <th>Status</th>
                        <th>Facility (Electricity & Water)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($availableQuartersData)): ?>
                        <tr><td colspan="9" style="text-align: center; color: #64748b; padding: 25px;">No available quarters found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($availableQuartersData as $index =>$row): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><b><?php echo htmlspecialchars($row['quarterNo']); ?></b></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td><?php echo htmlspecialchars($row['quarterName']); ?></td>
                                <td><?php echo htmlspecialchars($row['address']); ?></td>
                                <td><?php echo htmlspecialchars($row['quarterType']); ?></td>
                                <td><b>Rs. <?php echo htmlspecialchars($row['assessmentRent']); ?></b></td>
                                <td><span class="badge-status" style="color: <?php echo htmlspecialchars($row['statusColor']); ?>;"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                <td>
                                    <div class="facility-row">
                                        <span class="facility-label">Electricity</span>
                                        <?php if ($row['electric']): ?>
                                            <span class="badge-available" title="Available" aria-label="Available"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                        <?php else: ?>
                                            <span class="badge-unavailable" title="Unavailable" aria-label="Unavailable"><i class="fa-solid fa-xmark" aria-hidden="true"></i></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="facility-row">
                                        <span class="facility-label">Water</span>
                                        <?php if ($row['water']): ?>
                                            <span class="badge-available" title="Available" aria-label="Available"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                        <?php else: ?>
                                            <span class="badge-unavailable" title="Unavailable" aria-label="Unavailable"><i class="fa-solid fa-xmark" aria-hidden="true"></i></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div id="noResultsRow">No matching quarters found.</div>
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
        const table = document.getElementById("quartersTable");
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
            let found = false;
            const td = tr[i].getElementsByTagName("td");
            for (let j = 0; j < td.length; j++) {
                if (td[j]) {
                    const accessibleLabels = [...td[j].querySelectorAll("[aria-label]")]
                        .map(element => element.getAttribute("aria-label"))
                        .join(" ");
                    const cellText = `${td[j].innerText} ${accessibleLabels}`.toLowerCase();
                    if (cellText.indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            tr[i].style.display = found ? "" : "none";
            if (found) visibleCount++;
        }

        noResults.style.display = visibleCount === 0 ? "block" : "none";
    }

    function handleSearchInput(input) {
        if (input.value.trim() === "") {
            searchTable();
        }
    }

    window.addEventListener("beforeprint", () => {
        document.querySelectorAll("#quartersTable tbody tr").forEach(row => {
            row.style.display = "";
        });
        document.getElementById("noResultsRow").style.display = "none";
    });

    window.addEventListener("afterprint", searchTable);
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>