<?php
/**
 * Approved Applications List - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Approved Applications List - Sri Lanka Railways";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$approvedData = [
    [
        'id' => 1,
        'appNo' => 'SLR/AOT/2026/0001',
        'nic' => '981234567V',
        'compNo' => '101',
        'name' => 'A. B. Perera',
        'desig' => 'Station Master'
    ],
    [
        'id' => 2,
        'appNo' => 'SLR/AOT/2026/0003',
        'nic' => '923456789V',
        'compNo' => '105',
        'name' => 'M. N. Fernando',
        'desig' => 'Locomotive Driver'
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

    .approval-container {
        max-width: 1400px;
        margin: 20px auto;
        padding: 0 20px;
        width: 100%;
        box-sizing: border-box;
    }

    .approval-header {
        text-align: center;
        margin-bottom: 25px;
    }

    .approval-container h2 {
        font-size: 1.8rem;
        color: #7A1C1C;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-decoration: underline;
        text-underline-offset: 6px; /* Pending list එකේ මෙන් bar එකේ පිහිටීම සකස් කරන ලදී[cite: 12] */
        font-weight: 700;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 20px;
        color: #7A1C1C;
        text-decoration: none;
        font-weight: 600;
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
        max-width: 500px;
    }

    .search-field {
        position: relative;
        flex: 1;
        min-width: 0;
    }

    .search-input {
        padding: 10px 14px;
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
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s;
        font-size: 0.9rem;
        white-space: nowrap;
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

    .table-card {
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

    table {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
        font-size: 0.9rem;
        min-width: 900px;
    }

    th, td {
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    th {
        background-color: #f8fafc;
        color: #1e293b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }

    tr:hover {
        background-color: #f1f5f9;
    }

    .status-badge-icon {
        background-color: #d1fae5;
        color: #065f46;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
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
        @page {
            size: landscape;
            margin: 5mm;
        }

        html, body {
            background-color: #ffffff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .back-link, .top-action-bar, .user-profile-wrapper {
            display: none !important;
        }

        .table-card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
        }

        table {
            width: 100% !important;
            font-size: 8.5pt !important;
        }

        th, td {
            padding: 8px 10px !important;
        }
    }

    @media (max-width: 768px) {
        .approval-container {
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

<main class="approval-container">
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
    
    <div class="approval-header">
        <h2>Approved Applications List</h2>
    </div>

    <!-- Search Form handled via PHP GET -->
    <div class="top-action-bar">
        <form method="GET" action="" class="search-wrapper">
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
        </form>
        
        <button onclick="window.print()" class="btn-print">
            <i class="fa-solid fa-print"></i> Print / Save as PDF
        </button>
    </div>

    <div class="table-card">
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
                        <th style="text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $searchTermLower = strtolower($searchVal);

                    // Filter records based on search query[cite: 11, 12]
                    $filtered = array_filter($approvedData, function($item) use ($searchTermLower) {
                        if ($searchTermLower === '') return true;
                        return strpos(strtolower($item['nic']), $searchTermLower) !== false || strpos(strtolower($item['name']), $searchTermLower) !== false;
                    });

                    if (empty($filtered)) {
                        echo '<tr><td colspan="7" style="text-align: center; color: #64748b; padding: 25px;">No approved applications found.</td></tr>';
                    } else {
                        $counter = 1;
                        foreach ($filtered as $row) {
                            echo '<tr>';
                            echo '<td>' . $counter++ . '</td>';
                            echo '<td><strong>' . htmlspecialchars($row['appNo']) . '</strong></td>';
                            echo '<td>' . htmlspecialchars($row['nic']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['compNo']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['desig']) . '</td>';
                            echo '<td style="text-align: center;"><span class="status-badge-icon" title="Approved"><i class="fa-solid fa-check"></i></span></td>';
                            echo '</tr>';
                        }
                    }
                    ?>
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