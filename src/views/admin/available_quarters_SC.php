<?php
/**
 * Available Quarters - Sri Lanka Railways Admin
 *
 * Shows ONLY quarters with quarter_status = 'Available' and is_active = 1,
 * fetched live from the `quarters` table.
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Available Quarters - Sri Lanka Railways Admin";

$userData = [
    'name'     => 'Subject Clerk',
    'role'     => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$searchQuery = trim($_GET['search'] ?? '');
$displayData = [];

$conn = getDBConnection();

$sql = "SELECT id,
               quarter_number,
               quarter_name,
               location_name,
               quarter_address,
               quarter_type,
               quarter_assessment_rent,
               quarter_status,
               has_electricity,
               has_water
        FROM quarters
        WHERE is_active = 1
          AND quarter_status = 'Available'";

if ($searchQuery !== '') {
    $sql .= " AND (quarter_number LIKE ?
                OR location_name LIKE ?
                OR quarter_name LIKE ?)";
    $searchTerm = '%' . $searchQuery . '%';
    $stmt = $conn->prepare($sql . ' ORDER BY location_name ASC, quarter_number ASC');
    $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare($sql . ' ORDER BY location_name ASC, quarter_number ASC');
}

$stmt->execute();
$displayData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Status colours (only 'Available' will ever be shown here)
function statusColor(string $status): string {
    return match ($status) {
        'Available'         => '#16a34a',
        'Occupied'          => '#1e40af',
        'Under Maintenance' => '#d97706',
        default             => '#334155'
    };
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
        color: var(--primary-color);
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

    .search-card {
        background: var(--card-bg);
        padding: 18px 22px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        margin-bottom: 20px;
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

    .search-btn, .print-btn {
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
    }

    .search-btn {
        background-color: var(--primary-color);
        color: white;
    }

    .search-btn:hover {
        background-color: var(--primary-dark);
    }

    .print-btn {
        background-color: #0284c7;
        color: white;
    }

    .print-btn:hover {
        background-color: #0369a1;
    }

    .management-section {
        background: var(--card-bg);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        border-top: 5px solid var(--primary-color);
        overflow-x: auto;
        width: 100%;
        box-sizing: border-box;
        margin-bottom: 40px;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        min-width: 1100px;
    }
    .data-table th, .data-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
        font-size: 0.9rem;
        text-align: center;
    }
    .data-table th {
        background-color: #f8fafc;
        color: var(--text-main);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border-color);
    }
    .data-table td {
        color: var(--text-main);
    }

    .data-table td:nth-child(4),
    .data-table td:nth-child(5) {
        text-align: left;
    }

    .data-table tr:hover {
        background-color: #f1f5f9;
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
        .print-btn {
            width: 100%;
            justify-content: center;
        }
    }

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

        .back-link, .search-card, .user-profile-wrapper {
            display: none !important;
        }

        .management-section {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
        }

        .data-table {
            width: 100% !important;
            font-size: 8.5pt !important;
        }

        .data-table th, .data-table td {
            padding: 8px 10px !important;
        }

        .facility-row {
            display: block;
            margin: 2px 0;
        }

        .facility-label {
            width: auto;
            text-align: center;
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

    <h2 class="page-title">Available Quarters Portal</h2>

    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" class="search-input"
                       placeholder="Search by Quarter No, Location or Name..."
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                <?php if (!empty($searchQuery)): ?>
                    <a href="?" class="search-clear-icon" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
        <button type="button" class="print-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> Print / Save as PDF</button>
    </form>

    <div class="management-section" id="availableQuartersTile">
        <div class="table-responsive">
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
                        <th>Facility (Electricity &amp; Water)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($displayData)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: #64748b; padding: 30px;">
                                No available quarters found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $counter = 1; foreach ($displayData as $row): ?>
                            <?php
                                $status = $row['quarter_status'] ?? 'Available';
                                $statusCol = statusColor($status);
                                $electric  = !empty($row['has_electricity']);
                                $water     = !empty($row['has_water']);
                            ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><b><?php echo htmlspecialchars($row['quarter_number']); ?></b></td>
                                <td><?php echo htmlspecialchars($row['location_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['quarter_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['quarter_address'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['quarter_type']); ?></td>
                                <td><b>Rs. <?php echo htmlspecialchars(number_format((float)($row['quarter_assessment_rent'] ?? 0), 2)); ?></b></td>
                                <td><span class="badge-status" style="color: <?php echo htmlspecialchars($statusCol); ?>;"><?php echo htmlspecialchars($status); ?></span></td>
                                <td>
                                    <div class="facility-row">
                                        <span class="facility-label">Electricity</span>
                                        <?php if ($electric): ?>
                                            <span class="badge-available" title="Available" aria-label="Available"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                                        <?php else: ?>
                                            <span class="badge-unavailable" title="Unavailable" aria-label="Unavailable"><i class="fa-solid fa-xmark" aria-hidden="true"></i></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="facility-row">
                                        <span class="facility-label">Water</span>
                                        <?php if ($water): ?>
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