<?php
/**
 * Quarters Details - Sri Lanka Railways Admin
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Quarters Details - Sri Lanka Railways Admin";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$defaultData = [
    [
        'quarterNum' => 'Q-01',
        'quarterName' => 'Bungalow A',
        'division' => 'Way & Works',
        'ownership' => 'Railway Department',
        'quarterArea' => '1,450 sq.ft',
        'location' => 'Colombo',
        'address' => 'No. 12, Railway Avenue, Colombo 10',
        'quarterType' => 'Bungalows',
        'assessmentRent' => '5,500.00',
        'status' => 'Under Maintenance',
        'electric' => true,
        'water' => true
    ],
    [
        'quarterNum' => 'Q-02',
        'quarterName' => 'Type II House',
        'division' => 'Signal & Telecommunication',
        'ownership' => 'Railway Department',
        'quarterArea' => '950 sq.ft',
        'location' => 'Kandy',
        'address' => 'No. 8, Peradeniya Road, Kandy',
        'quarterType' => 'Type II',
        'assessmentRent' => '4,200.00',
        'status' => 'Available',
        'electric' => true,
        'water' => true
    ],
    [
        'quarterNum' => 'Q-03',
        'quarterName' => 'Staff Flat',
        'division' => 'Electrical',
        'ownership' => 'Railway Department',
        'quarterArea' => '800 sq.ft',
        'location' => 'Galle',
        'address' => 'No. 22, Fort Road, Galle',
        'quarterType' => 'Type I',
        'assessmentRent' => '3,900.00',
        'status' => 'Occupied',
        'electric' => true,
        'water' => false
    ],
    [
        'quarterNum' => 'Q-04',
        'quarterName' => 'Annex Unit',
        'division' => 'Mechanical',
        'ownership' => 'Railway Department',
        'quarterArea' => '600 sq.ft',
        'location' => 'Anuradhapura',
        'address' => 'No. 45, Station Road, Anuradhapura',
        'quarterType' => 'Type III',
        'assessmentRent' => '2,750.00',
        'status' => 'Available',
        'electric' => false,
        'water' => true
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
        width: 100%; 
        max-width: none; 
        box-sizing: border-box; 
        margin: 20px auto; 
        padding: 0 12px; 
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

    .search-wrapper { 
        display: flex; 
        gap: 10px; 
        align-items: center; 
    }

    .search-box-wrapper { 
        position: relative; 
        display: flex; 
        align-items: center; 
    }

    .search-box-wrapper i { 
        position: absolute; 
        left: 14px; 
        color: #64748b; 
        font-size: 0.9rem; 
    }

    .search-input { 
        padding: 10px 14px 10px 40px; 
        border-radius: 8px; 
        border: 1px solid #cbd5e1; 
        font-size: 0.9rem; 
        width: 300px; 
        outline: none; 
        transition: all 0.2s; 
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
        background-color: #5c1414; 
    }

    .maintenance-btn { 
        background-color: #2563eb; 
        color: white; 
        padding: 10px 18px; 
        border-radius: 8px; 
        font-weight: 600; 
        font-size: 0.9rem; 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        text-decoration: none; 
        transition: background-color 0.2s; 
    }

    .maintenance-btn:hover { 
        background-color: #1d4ed8; 
    }

    .table-card { 
        background: #ffffff; 
        padding: 12px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid #7A1C1C; 
        overflow: hidden; 
        position: relative; 
    }

    .table-responsive { 
        width: 100%; 
        overflow-x: auto; 
    }

    .quarters-table { 
        width: 100%; 
        table-layout: fixed; 
        border-collapse: collapse; 
        text-align: left; 
        min-width: 1300px;
    }

    .quarters-table th, .quarters-table td { 
        padding: 8px 5px; 
        border: 1px solid #e2e8f0; 
        vertical-align: middle; 
        font-size: 0.75rem; 
        text-align: center; 
        overflow-wrap: anywhere; 
    }

    .quarters-table th { 
        background-color: #f8fafc; 
        color: #1e293b; 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 0.68rem; 
        letter-spacing: 0; 
    }

    .quarters-table td { 
        color: #4b5563; 
        font-weight: normal; 
    }

    .quarters-table tbody tr { 
        transition: background-color 0.15s ease; 
    }

    .quarters-table tbody tr:hover { 
        background-color: #f1f5f9; 
    }

    .facility-container { 
        display: grid; 
        gap: 5px; 
    }

    .facility-item { 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        gap: 6px; 
    }

    .facility-label { 
        color: #334155; 
        font-size: inherit; 
        font-weight: normal; 
        text-align: left; 
    }

    .badge { 
        display: inline-flex; 
        align-items: center; 
        gap: 5px; 
        padding: 4px 7px; 
        border-radius: 20px; 
        font-size: inherit; 
        font-weight: normal; 
    }

    .badge-active { 
        background-color: #dcfce7; 
        color: #166534; 
        border: 1px solid #bbf7d0; 
    }

    .badge-inactive { 
        background-color: #f1f5f9; 
        color: #64748b; 
        border: 1px solid #e2e8f0; 
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

    @media (max-width: 640px) {
        .container { margin: 15px auto; padding: 0 6px; }
        .page-title { font-size: 1.5rem; }
        .section-header-bar, .table-card { padding: 10px; }
        .search-wrapper { width: 100%; flex-wrap: wrap; }
        .search-box-wrapper, .search-input { width: 100%; box-sizing: border-box; }
        .search-btn { justify-content: center; width: 100%; }
        .maintenance-btn { width: 100%; justify-content: center; }
        .quarters-table th, .quarters-table td { padding: 4px 2px; font-size: 0.58rem; line-height: 1.2; }
        .quarters-table th { font-size: 0.52rem; }
        .badge { gap: 2px; padding: 2px 3px; }
        .facility-item { flex-direction: column; gap: 2px; }
        .facility-label { text-align: center; }
    }
</style>

<main class="container">
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

    <h2 class="page-title">Quarters Details</h2>

    <div class="section-header-bar">
        <div class="search-wrapper">
            <div class="search-box-wrapper">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input type="search" id="searchInput" class="search-input" placeholder="Search by Quarter No or Name..." aria-label="Search quarters" oninput="checkInputEmpty(this)">
            </div>
            <button type="button" class="search-btn" onclick="searchTable()"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Search</button>
        </div>
        <a href="maintenance-SC.php" class="maintenance-btn"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i> Under Maintenance Quarters</a>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="quarters-table" id="quartersTable">
                <colgroup>
                    <col style="width: 4%">
                    <col style="width: 9%">
                    <col style="width: 10%">
                    <col style="width: 9%">
                    <col style="width: 7%">
                    <col style="width: 7%">
                    <col style="width: 7%">
                    <col style="width: 12%">
                    <col style="width: 7%">
                    <col style="width: 8%">
                    <col style="width: 8%">
                    <col style="width: 12%">
                </colgroup>
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
                        <th>Facility (Electricity &amp; Water)</th>
                    </tr>
                </thead>
                <tbody id="quartersBody">
                    <?php 
                    $serverQuartersData = $defaultData;
                    if (empty($serverQuartersData)): 
                    ?>
                        <tr><td colspan="12" style="text-align: center; color: #64748b; padding: 25px;">No quarters found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($serverQuartersData as $index => $item): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($item['quarterNum']); ?></td>
                                <td><?php echo htmlspecialchars($item['quarterName']); ?></td>
                                <td><?php echo htmlspecialchars($item['division']); ?></td>
                                <td><?php echo htmlspecialchars($item['ownership']); ?></td>
                                <td><?php echo htmlspecialchars($item['quarterArea']); ?></td>
                                <td><?php echo htmlspecialchars($item['location']); ?></td>
                                <td><?php echo htmlspecialchars($item['address']); ?></td>
                                <td><?php echo htmlspecialchars($item['quarterType']); ?></td>
                                <td>Rs. <?php echo htmlspecialchars($item['assessmentRent']); ?></td>
                                <td><?php echo htmlspecialchars($item['status']); ?></td>
                                <td>
                                    <div class="facility-container">
                                        <div class="facility-item">
                                            <span class="facility-label">Electricity</span>
                                            <?php if ($item['electric']): ?>
                                                <span class="badge badge-active">Available</span>
                                            <?php else: ?>
                                                <span class="badge badge-inactive">Unavailable</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="facility-item">
                                            <span class="facility-label">Water</span>
                                            <?php if ($item['water']): ?>
                                                <span class="badge badge-active">Available</span>
                                            <?php else: ?>
                                                <span class="badge badge-inactive">Unavailable</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

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

    function checkInputEmpty(input) {
        if (input.value.trim() === '') {
            searchTable();
        }
    }

    function searchTable() {
        const query = document.getElementById('searchInput').value.trim().toLowerCase();
        const rows = document.querySelectorAll('#quartersBody tr');
        
        rows.forEach(row => {
            if (row.cells.length < 2) return;
            const quarterNumber = row.cells[1].textContent.toLowerCase();
            const quarterName = row.cells[2].textContent.toLowerCase();
            row.style.display = (query === '' || quarterNumber.includes(query) || quarterName.includes(query)) ? '' : 'none';
        });
    }
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>