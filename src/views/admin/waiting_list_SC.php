<?php
/**
 * Waiting Lists - Sri Lanka Railways Admin (Subject Clerk)
 *
 * Mirrors waiting_list_FA.php:
 *   - Live data from `waiting_list` joined to `users` and `applications`
 *   - Only rows whose application is finally approved are shown
 *   - General tab shows all; other tabs populate by designation
 *     (currently everyone falls into General until other designations exist).
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Waiting Lists - Sri Lanka Railways Admin";

$userData = [
    'name'     => 'Subject Clerk',
    'role'     => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$searchQuery = trim($_GET['search'] ?? '');

$waitingListsData = [
    'general'             => [],
    'guard'               => [],
    'station_master'      => [],
    'controller'          => [],
    'deputy_commissioner' => [],
];

$conn = getDBConnection();

// Same query as waiting_list_FA.php (final-approval gate enforced)
$sql = "SELECT wl.nic,
               u.computer_number                        AS compNo,
               u.name,
               COALESCE(u.applicant_designation, '')    AS desig,
               COALESCE(u.applicant_current_work_station, '') AS workplace,
               wl.applied_date                          AS allocDate,
               wl.quarter_type                          AS houseType,
               COALESCE(wl.employee_marks, 0)           AS marks
        FROM waiting_list AS wl
        INNER JOIN users AS u
                ON u.nic = wl.nic
        INNER JOIN applications AS a
                ON a.nic = wl.nic
               AND a.quarter_type = wl.quarter_type
               AND a.waiting_list_no = wl.position
               AND a.final_status = 'approved'
               AND a.application_status = 'approved'";

if ($searchQuery !== '') {
    $sql .= " WHERE (wl.nic LIKE ? OR u.name LIKE ?)";
    $searchTerm = '%' . $searchQuery . '%';
    $stmt = $conn->prepare(
        $sql . ' ORDER BY wl.id ASC'
    );
    $stmt->bind_param('ss', $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare(
        $sql . ' ORDER BY wl.id ASC'
    );
}

$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Everyone goes into General (matching FA), then bucket by designation
foreach ($rows as $row) {
    $waitingListsData['general'][] = $row;

    $desig = strtolower($row['desig']);

    if (str_contains($desig, 'guard')) {
        $waitingListsData['guard'][] = $row;
    } elseif (str_contains($desig, 'station master') || str_contains($desig, 'st. master')) {
        $waitingListsData['station_master'][] = $row;
    } elseif (str_contains($desig, 'controller')) {
        $waitingListsData['controller'][] = $row;
    } elseif (str_contains($desig, 'deputy superintendent')
           || str_contains($desig, 'deputy commissioner')
           || str_contains($desig, 'deputy supt')) {
        $waitingListsData['deputy_commissioner'][] = $row;
    }
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
        max-width: 1350px;
        margin: 20px auto;
        padding: 0 20px;
        width: 100%;
        box-sizing: border-box;
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

    .tab-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        margin-bottom: 25px;
    }

    .tab-btn {
        background-color: #f1f5f9;
        color: var(--text-main);
        border: 1px solid #cbd5e1;
        padding: 10px 18px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    .tab-btn:hover {
        background-color: #e2e8f0;
    }
    .tab-btn.active {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .search-card {
        background: var(--card-bg);
        padding: 18px 22px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        border: 1px solid var(--border-color);
        width: 100%;
        box-sizing: border-box;
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
        max-width: 380px;
    }

    .search-input {
        width: 100%;
        padding: 10px 36px 10px 14px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        font-size: 0.9rem;
        outline: none;
        box-sizing: border-box;
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
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
        text-decoration: none;
        transition: background 0.2s;
        background-color: var(--primary-color);
        color: white;
    }
    .search-btn:hover {
        background-color: var(--primary-dark);
    }

    .table-card {
        background: var(--card-bg);
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        border-top: 5px solid var(--primary-color);
        display: none;
        overflow-x: auto;
    }
    .table-card.active-table {
        display: block;
    }

    .section-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .section-subtitle {
        color: var(--primary-color);
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0;
    }

    .waiting-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        min-width: 900px;
    }
    .waiting-table th, .waiting-table td {
        padding: 12px 10px;
        border: 1px solid #cbd5e1;
        vertical-align: middle;
        font-size: 0.85rem;
        text-align: center;
    }
    .waiting-table th {
        background-color: #f8fafc;
        color: var(--text-main);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
    }
    .waiting-table tbody td {
        color: #334155;
        font-size: 0.85rem;
        font-weight: 400;
    }
    .waiting-table td:nth-child(4), .waiting-table td:nth-child(6) {
        text-align: left;
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
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
        <a href="assign_quarters_SC.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
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
                <a href="assign_quarters_SC.php"><i class="fa-solid fa-arrow-left"></i> Back to Assign Quarters Management</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="page-title">Waiting Lists</h2>

    <div class="tab-container">
        <button class="tab-btn active" onclick="showTable('general', event)">1. General List</button>
        <button class="tab-btn" onclick="showTable('guard', event)">2. Guard List</button>
        <button class="tab-btn" onclick="showTable('station_master', event)">3. Station Master List</button>
        <button class="tab-btn" onclick="showTable('controller', event)">4. Controller List</button>
        <button class="tab-btn" onclick="showTable('deputy_commissioner', event)">5. Deputy Superintendent (Transportation) Colombo List</button>
    </div>

    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" class="search-input"
                       placeholder="Search by NIC or Name..."
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                <?php if (!empty($searchQuery)): ?>
                    <a href="?" class="search-clear-icon" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
    </form>

    <?php
    $tabInfo = [
        'general'             => '1. General List',
        'guard'               => '2. Guard List',
        'station_master'      => '3. Station Master List',
        'controller'          => '4. Controller List',
        'deputy_commissioner' => '5. Deputy Superintendent (Transportation) Colombo List',
    ];
    $isFirst = true;
    foreach ($tabInfo as $key => $title):
        $rows = $waitingListsData[$key] ?? [];
        $activeClass = $isFirst ? 'active-table' : '';
        $isFirst = false;
    ?>
        <div id="<?php echo $key; ?>" class="table-card <?php echo $activeClass; ?>">
            <div class="section-header-bar">
                <h3 class="section-subtitle"><?php echo htmlspecialchars($title); ?></h3>
            </div>
            <div class="table-responsive">
                <table class="waiting-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>NIC</th>
                            <th>Computer No.</th>
                            <th>Applicant's Name</th>
                            <th>Designation</th>
                            <th>Workplace</th>
                            <th>Applied Date</th>
                            <th>Quarter Type</th>
                            <th>Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; color: #64748b; padding: 25px;">
                                    No records found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($rows as $row): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nic']); ?></td>
                                    <td><?php echo htmlspecialchars($row['compNo'] ?? '-'); ?></td>
                                    <td style="text-align: left;"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['desig'] ?: '-'); ?></td>
                                    <td style="text-align: left;"><?php echo htmlspecialchars($row['workplace'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['allocDate'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['houseType'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars((string)($row['marks'] ?? 0)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
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

    function showTable(tableId, event) {
        document.querySelectorAll('.table-card').forEach(card => card.classList.remove('active-table'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById(tableId).classList.add('active-table');
        event.currentTarget.classList.add('active');
    }
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>