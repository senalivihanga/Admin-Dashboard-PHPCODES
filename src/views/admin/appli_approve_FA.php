<?php
/**
 * Pending Applications Log - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Pending Applications Log - Sri Lanka Railways";

$userData = [
    'name' => 'Final Approver',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'FA'
];

$user = [
    'name' => $userData['name']
];

// Admin Header include karni
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

    .approval-container {
        max-width: 1300px;
        margin: 10px auto;
        padding: 0 15px;
        box-sizing: border-box;
        width: 100%;
    }

    .approval-header {
        text-align: center;
        margin-bottom: 15px;
    }

    .approval-container h2 {
        font-size: 1.6rem;
        color: #7A1C1C;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-decoration: underline;
        text-underline-offset: 6px;
        font-weight: 700;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 15px;
        color: #7A1C1C;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        transition: gap 0.2s;
    }

    .back-link:hover {
        gap: 10px;
        text-decoration: underline;
    }

    .user-profile-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 15px;
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

    .search-card {
        background: #ffffff;
        padding: 12px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-bottom: 15px;
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
        max-width: 500px;
    }

    .search-field {
        position: relative;
        flex: 1;
        min-width: 0;
    }

    .search-input {
        padding: 8px 14px;
        padding-right: 40px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        font-size: 0.9rem;
        width: 100%;
        box-sizing: border-box;
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
        padding: 8px 18px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s;
        white-space: nowrap;
        font-size: 0.9rem;
    }

    .search-btn:hover {
        background-color: #571a1a;
    }

    .search-clear {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        color: #7A1C1C;
        font-size: 1rem;
        line-height: 1;
        padding: 0;
        border: 0;
        background: transparent;
        cursor: pointer;
    }

    .search-clear:hover {
        color: #571a1a;
    }

    .badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #fef3c7;
        color: #d97706;
        font-size: 0.8rem;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        background: #ffffff;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        border-top: 5px solid #7A1C1C;
        box-sizing: border-box;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
        font-size: 0.9rem;
        min-width: 700px;
    }

    th, td {
        padding: 10px 10px;
        border: 1px solid #cbd5e1;
        vertical-align: middle;
        font-size: 0.85rem;
    }

    th {
        background-color: #f8fafc;
        color: #1e293b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
    }

    tr:hover {
        background-color: #f1f5f9;
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
        .approval-container {
            margin: 10px auto;
            padding: 0 10px;
        }
        .approval-container h2 {
            font-size: 1.3rem;
        }
        .search-card {
            padding: 10px;
        }
        .search-wrapper {
            flex-direction: column;
            width: 100%;
            max-width: 100%;
        }
        .search-input {
            width: 100%;
        }
        .search-btn {
            width: 100%;
            justify-content: center;
        }
        .table-responsive {
            padding: 8px;
        }
        th, td {
            padding: 6px 4px;
            font-size: 0.78rem;
        }
        .badge {
            width: 24px;
            height: 24px;
            font-size: 0.75rem;
        }
    }
</style>

<main class="approval-container">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
        <a href="FA_dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
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

    <div class="approval-header">
        <h2>Pending Applications Log</h2>
    </div>

    <!-- IB Code එකෙන් ගන්නා ලද Search Form (PHP GET Method) -->
    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
            <?php 
            $searchVal = isset($_GET['search']) ? trim($_GET['search']) : ''; 
            ?>
            <div class="search-field">
                <input type="text" name="search" id="searchInput" class="search-input" placeholder="Search by NIC or Name..." value="<?php echo htmlspecialchars($searchVal); ?>" oninput="if (this.value.trim() === '') this.form.submit();">
                <?php if ($searchVal !== ''): ?>
                    <button class="search-clear" type="button" onclick="this.form.elements['search'].value = ''; this.form.submit();" aria-label="Clear search" title="Clear search"><i class="fa-solid fa-xmark"></i></button>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Application Number</th>
                    <th>NIC</th>
                    <th>Computer Number</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Pending Data Array
                $pendingData = [
                    ['id' => 1, 'appNo' => 'SLR/AOT/2026/0006', 'nic' => '961234567V', 'compNo' => '115', 'name' => 'R. M. Rathnayake', 'desig' => 'Station Master', 'status' => 'Pending'],
                    ['id' => 2, 'appNo' => 'SLR/AOT/2026/0007', 'nic' => '934561239V', 'compNo' => '118', 'name' => 'T. S. Wickramasinghe', 'desig' => 'Guard', 'status' => 'Pending']
                ];

                $searchTermLower = strtolower($searchVal);

                // Filter records based on search query
                $filtered = array_filter($pendingData, function($item) use ($searchTermLower) {
                    if ($searchTermLower === '') return true;
                    return strpos(strtolower($item['nic']), $searchTermLower) !== false || strpos(strtolower($item['name']), $searchTermLower) !== false;
                });

                if (empty($filtered)) {
                    echo '<tr><td colspan="7" style="text-align: center; color: #64748b; padding: 20px;">No pending records found.</td></tr>';
                } else {
                    $counter = 1;
                    foreach ($filtered as $item) {
                        echo '<tr>';
                        echo '<td>' . $counter++ . '</td>';
                        echo '<td><strong>' . htmlspecialchars($item['appNo']) . '</strong></td>';
                        echo '<td>' . htmlspecialchars($item['nic']) . '</td>';
                        echo '<td>' . htmlspecialchars($item['compNo']) . '</td>';
                        echo '<td>' . htmlspecialchars($item['name']) . '</td>';
                        echo '<td>' . htmlspecialchars($item['desig']) . '</td>';
                        echo '<td><span class="badge"><i class="fa-solid fa-clock" title="Pending"></i></span></td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</main>

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
// Admin Footer include karni
require_once __DIR__ . '/../layouts/admin_footer.php';
?>