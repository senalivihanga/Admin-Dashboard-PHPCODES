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

// Handle Search Filter matching appli_approve_IB style[cite: 28]
$searchQuery = trim($_GET['search'] ?? '');
$serverQuartersData = $defaultData;

if (!empty($searchQuery)) {
    $serverQuartersData = array_filter($serverQuartersData, function($item) use ($searchQuery) {
        return stripos($item['quarterNum'], $searchQuery) !== false || 
               stripos($item['quarterName'], $searchQuery) !== false ||
               stripos($item['location'], $searchQuery) !== false;
    });
}

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    :root {
        --primary-color: #7A1C1C;
        --primary-dark: #571a1a;
        --danger-color: #ef4444;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --text-main: #1e293b;
        --border-color: #e2e8f0;
    }

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-y: auto;
        background-color: var(--bg-color);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container { 
        width: 100%; 
        max-width: 1750px; 
        box-sizing: border-box; 
        margin: 20px auto; 
        padding: 0 25px; 
    }

    .back-link { 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        color: var(--primary-color); 
        text-decoration: none; 
        font-weight: 600; 
        margin-bottom: 20px; 
        font-size: 0.95rem;
        transition: color 0.2s;
    }
    .back-link:hover { 
        color: var(--primary-dark);
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
        background: var(--primary-color);
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
        border: 1px solid var(--border-color);
    }

    .profile-dropdown.show {
        display: block;
    }

    .profile-dropdown a {
        color: var(--text-main);
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
        color: var(--primary-color);
    }

    .profile-dropdown a.logout-btn {
        color: var(--danger-color);
    }

    .profile-dropdown a.logout-btn:hover {
        background: #fee2e2;
    }

    .page-title { 
        text-align: center; 
        color: var(--primary-color); 
        font-size: 1.8rem; 
        margin-bottom: 25px; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        text-decoration: underline; 
        text-underline-offset: 6px; 
        font-weight: 700;
    }

    .section-header-bar { 
        background: var(--card-bg); 
        padding: 18px 22px; 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.04); 
        border: 1px solid var(--border-color); 
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
        flex: 1;
        min-width: 280px;
    }

    .search-input-container { 
        position: relative; 
        flex: 1;
        max-width: 350px;
    }

    .search-input { 
        width: 100%;
        padding: 10px 36px 10px 14px; 
        border-radius: 8px; 
        border: 1px solid #cbd5e1; 
        font-size: 0.9rem; 
        outline: none; 
        box-sizing: border-box;
        background-color: #ffffff; 
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .search-input:focus { 
        border-color: var(--primary-color); 
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1); 
    }

    .search-clear-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        text-decoration: none;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }

    .search-clear-icon:hover {
        color: var(--text-main);
    }

    .search-btn { 
        background-color: var(--primary-color); 
        color: white; 
        border: none; 
        padding: 10px 18px; 
        border-radius: 8px; 
        font-weight: 600; 
        cursor: pointer; 
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        transition: background 0.2s; 
        font-size: 0.9rem; 
    }

    .search-btn:hover { 
        background-color: var(--primary-dark); 
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
        background: var(--card-bg); 
        padding: 20px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid var(--primary-color); 
        overflow: hidden; 
        position: relative; 
        margin-bottom: 40px;
    }

    .table-responsive { 
        width: 100%; 
        overflow-x: auto; 
        -webkit-overflow-scrolling: touch;
    }

    .quarters-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: left; 
        min-width: 1300px;
    }

    .quarters-table th, .quarters-table td { 
        padding: 12px 10px; 
        border: 1px solid #cbd5e1; 
        vertical-align: middle; 
        font-size: 0.85rem; 
        text-align: center; 
    }

    .quarters-table th { 
        background-color: #f8fafc; 
        color: var(--text-main); 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        letter-spacing: 0.5px; 
    }

    .quarters-table td { 
        color: #4b5563; 
    }

    .quarters-table tbody tr { 
        transition: background-color 0.15s ease; 
    }

    .quarters-table tbody tr:hover { 
        background-color: #f1f5f9; 
    }

    .facility-container { 
        display: grid; 
        gap: 6px; 
    }

    .facility-item { 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        gap: 8px; 
    }

    .facility-label { 
        color: #334155; 
        font-size: 0.8rem; 
        font-weight: 500; 
    }

    .badge { 
        display: inline-flex; 
        align-items: center; 
        gap: 5px; 
        padding: 4px 8px; 
        border-radius: 20px; 
        font-size: 0.75rem; 
        font-weight: 600; 
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
        color: var(--primary-color);
        margin-bottom: 15px;
    }

    .custom-modal-content h3 {
        color: var(--text-main);
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

    .btn-modal-confirm { background-color: var(--danger-color); }
    .btn-modal-cancel { background-color: #64748b; }

    @media (max-width: 768px) {
        .container { margin: 15px auto; padding: 0 12px; }
        .page-title { font-size: 1.5rem; }
        .section-header-bar { flex-direction: column; align-items: stretch; }
        .search-wrapper { width: 100%; flex-wrap: wrap; }
        .search-input-container { max-width: 100%; }
        .search-btn, .maintenance-btn { width: 100%; justify-content: center; }
    }
</style>

<main class="container">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
        <a href="SC_dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
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

    <form method="GET" action="" class="section-header-bar">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" class="search-input" placeholder="Search by Quarter No or Name..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                <?php if (!empty($searchQuery)): ?>
                    <a href="?" class="search-clear-icon" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Search</button>
        </div>
        <a href="maintenance_SC.php" class="maintenance-btn"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i> Under Maintenance Quarters</a>
    </form>

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
                        <th>Facility (Electricity &amp; Water)</th>
                    </tr>
                </thead>
                <tbody id="quartersBody">
                    <?php if (empty($serverQuartersData)): ?>
                        <tr><td colspan="12" style="text-align: center; color: #64748b; padding: 25px;">No quarters found.</td></tr>
                    <?php else: ?>
                        <?php $counter = 1; foreach ($serverQuartersData as $item): ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><strong><?php echo htmlspecialchars($item['quarterNum']); ?></strong></td>
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
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>