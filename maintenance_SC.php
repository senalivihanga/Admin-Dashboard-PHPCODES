<?>
/**
 * Quarters Maintenance - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Quarters Maintenance - Sri Lanka Railways";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$maintenanceData = [
    [
        'quarterNum' => 'Q-101',
        'location' => 'Colombo Fort',
        'quarterName' => 'Station View Bungalow',
        'quarterType' => 'Grade I',
        'status' => 'Under Maintenance',
        'lastMaint' => '2026-01-15'
    ],
    [
        'quarterNum' => 'Q-102',
        'location' => 'Maradana',
        'quarterName' => 'Yard Master Quarters',
        'quarterType' => 'Grade II',
        'status' => 'Under Maintenance',
        'lastMaint' => '2026-02-10'
    ],
    [
        'quarterNum' => 'Q-103',
        'location' => 'Gampaha',
        'quarterName' => 'Suburban Quarters',
        'quarterType' => 'Grade III',
        'status' => 'Repair in Progress',
        'lastMaint' => '2026-03-05'
    ],
    [
        'quarterNum' => 'Q-104',
        'location' => 'Kandy',
        'quarterName' => 'Station Staff Quarters',
        'quarterType' => 'Grade II',
        'status' => 'Painting Pending',
        'lastMaint' => '2026-04-12'
    ]
];

$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$filteredData = [];

foreach ($maintenanceData as $item) {
    $status = strtolower($item['status'] ?? '');
    if (strpos($status, 'maintenance') !== false || strpos($status, 'repair') !== false || strpos($status, 'painting') !== false || strpos($status, 'electrical') !== false) {
        if ($searchQuery === '' || strpos(strtolower($item['quarterNum']), strtolower($searchQuery)) !== false || strpos(strtolower($item['quarterName']), strtolower($searchQuery)) !== false) {
            $filteredData[] = $item;
        }
    }
}

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
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 25px; 
        flex-wrap: wrap; 
        gap: 15px; 
        background: #ffffff; 
        padding: 20px; 
        border-radius: 12px; 
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
        border: 1px solid #e2e8f0; 
    }

    .search-wrapper { display: flex; gap: 10px; align-items: center; width: 100%; max-width: 450px; }
    .search-box-wrapper { position: relative; display: flex; align-items: center; flex: 1; }
    .search-box-wrapper i { position: absolute; left: 14px; color: #64748b; font-size: 0.9rem; }
    .search-input { padding: 10px 14px 10px 40px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.9rem; width: 100%; outline: none; transition: all 0.2s; background-color: #ffffff; }
    .search-input:focus { border-color: #7A1C1C; box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1); }
    .search-btn { background-color: #7A1C1C; color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: background 0.2s; font-size: 0.9rem; }
    .search-btn:hover { background-color: #5c1414; }

    .table-card { background: #ffffff; padding: 25px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-top: 5px solid #7A1C1C; overflow-x: auto; border: 1px solid #e2e8f0; }
    .maint-table { width: 100%; border-collapse: collapse; text-align: left; min-width: 1100px; }
    .maint-table th, .maint-table td { padding: 14px 12px; border: 1px solid #e2e8f0; text-align: center; font-size: 0.85rem; vertical-align: middle; }
    .maint-table th { background-color: #f8fafc; color: #1e293b; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    .maint-table td { color: #4b5563; font-weight: normal; }
    .maint-table tbody tr { transition: background-color 0.15s ease; }
    .maint-table tbody tr:hover { background-color: #f1f5f9; }
    .no-data { text-align: center; padding: 25px; color: #64748b; font-style: italic; }

    .status-badge {
        display: inline-block;
        padding: 5px 14px;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 50px;
        text-align: center;
        text-transform: none;
    }
    .status-maintenance {
        background-color: #fef3c7;
        color: #d97706;
        border: 1px solid #fde68a;
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
        .section-header-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .search-wrapper {
            max-width: 100%;
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
                <a href="quarters-list-SC.php"><i class="fa-solid fa-arrow-left"></i> Back to Quarters List</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="page-title">Quarters Maintenance Details</h2>

    <div class="section-header-bar">
        <form method="GET" action="maintenance-SC.php" class="search-wrapper">
            <div class="search-box-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" id="searchInput" class="search-input" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search by Quarter No or Name...">
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </form>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="maint-table" id="maintTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Quarter No.</th>
                        <th>Quarter Name</th>
                        <th>Quarter Location</th>
                        <th>Quarter Type</th>
                        <th>Status</th>
                        <th>Last Maintenance Date</th>
                    </tr>
                </thead>
                <tbody id="maintBody">
                    <?php if (empty($filteredData)): ?>
                        <tr><td colspan="7" class="no-data">No quarters are currently under maintenance.</td></tr>
                    <?php else: ?>
                        <?php foreach ($filteredData as $index => $item): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($item['quarterNum']); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['quarterName']); ?></td>
                                <td><?php echo htmlspecialchars($item['location']); ?></td>
                                <td><?php echo htmlspecialchars($item['quarterType']); ?></td>
                                <td><span class="status-badge status-maintenance"><?php echo htmlspecialchars($item['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($item['lastMaint']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>