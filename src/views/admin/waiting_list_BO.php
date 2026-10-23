<?php
/**
 * Waiting Lists - Sri Lanka Railways Admin (Branch Officer view)
 *
 * Same data source and gate as waiting_list_FA.php / waiting_list_SC.php /
 * waiting_lists.php: `waiting_list ⋈ users ⋈ applications`, filtered to
 * final-approved applications only.
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Waiting Lists - Sri Lanka Railways Admin";

$searchQuery = trim($_GET['search'] ?? '');
$activeTab   = $_GET['tab'] ?? 'general';

$tabs = [
    'general'             => '1. General List',
    'guard'               => '2. Guard List',
    'station_master'      => '3. Station Master List',
    'controller'          => '4. Controller List',
    'deputy_commissioner' => '5. Deputy Superintendent (Transportation) Colombo List',
];

if (!array_key_exists($activeTab, $tabs)) {
    $activeTab = 'general';
}

// Buckets, filled from DB
$lists_data = [
    'general'             => [],
    'guard'               => [],
    'station_master'      => [],
    'controller'          => [],
    'deputy_commissioner' => [],
];

$conn = getDBConnection();

$sql = "SELECT wl.nic,
               u.computer_number                              AS compNo,
               u.name,
               COALESCE(u.applicant_designation, '')          AS designation,
               COALESCE(u.applicant_current_work_station, '') AS workplace,
               wl.applied_date                                AS allocDate,
               wl.quarter_type                                AS houseType,
               COALESCE(wl.employee_marks, 0)                 AS marks
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
        $sql . ' ORDER BY wl.applied_date ASC, wl.employee_marks DESC, wl.id ASC'
    );
    $stmt->bind_param('ss', $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare(
        $sql . ' ORDER BY wl.applied_date ASC, wl.employee_marks DESC, wl.id ASC'
    );
}

$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Everyone lands in General, then also buckets by designation
foreach ($rows as $row) {
    $lists_data['general'][] = $row;

    $desig = strtolower($row['designation']);

    if (str_contains($desig, 'guard')) {
        $lists_data['guard'][] = $row;
    } elseif (str_contains($desig, 'station master') || str_contains($desig, 'st. master')) {
        $lists_data['station_master'][] = $row;
    } elseif (str_contains($desig, 'controller')) {
        $lists_data['controller'][] = $row;
    } elseif (str_contains($desig, 'deputy superintendent')
           || str_contains($desig, 'deputy commissioner')
           || str_contains($desig, 'deputy supt')) {
        $lists_data['deputy_commissioner'][] = $row;
    }
}

require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
    * { box-sizing: border-box; }
    .container { max-width: 1250px; margin: 20px auto; padding: 0 15px; }
    .back-link { display: inline-flex; align-items: center; gap: 8px; color: #7A1C1C; text-decoration: none; font-weight: 600; margin-bottom: 15px; font-size: 0.95rem; }
    .back-link:hover { text-decoration: underline; }

    .page-title {
        text-align: center;
        color: #7A1C1C;
        font-size: 1.8rem;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-decoration: underline;
        text-underline-offset: 6px;
        font-weight: 700;
    }

    .tab-container { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-bottom: 20px; }
    .tab-btn { background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: all 0.2s ease; text-decoration: none; display: inline-block; text-align: center; }
    .tab-btn:hover { background-color: #e2e8f0; }
    .tab-btn.active { background-color: #7A1C1C; color: white; border-color: #7A1C1C; }

    .search-card {
        background: #ffffff;
        padding: 16px 20px;
        margin-bottom: 20px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
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
        max-width: 300px;
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
        border-color: #7A1C1C;
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
        color: #1e293b;
    }
    .search-btn {
        background: #7A1C1C;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
        transition: background 0.2s;
    }
    .search-btn:hover {
        background: #5c1414;
    }

    .table-card { background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-top: 5px solid #7A1C1C; margin-bottom: 20px; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .section-header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px; }
    .section-subtitle { color: #7A1C1C; font-size: 1.2rem; font-weight: 700; margin: 0; }

    .waiting-table { width: 100%; border-collapse: collapse; text-align: left; min-width: 850px; }
    .waiting-table th, .waiting-table td { padding: 12px 10px; border: 1px solid #cbd5e1; vertical-align: middle; font-size: 0.85rem; text-align: center; }
    .waiting-table th { background-color: #f8fafc; color: #1e293b; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; }
    .waiting-table tbody td { color: #334155; font-weight: 400; }
    .waiting-table td:nth-child(4), .waiting-table td:nth-child(6) { text-align: left; }
    .no-data { text-align: center; padding: 20px; color: #64748b; font-style: italic; }

    @media (max-width: 768px) {
        .container { margin: 10px auto; padding: 0 10px; }
        .page-title { font-size: 1.4rem; margin-bottom: 15px; }
        .tab-container { gap: 6px; }
        .tab-btn { padding: 8px 12px; font-size: 0.80rem; width: 100%; }
        .table-card { padding: 12px; border-radius: 8px; }
        .search-card { flex-direction: column; align-items: stretch; }
        .search-wrapper { width: 100%; }
        .search-input-container { max-width: 100%; }
        .search-btn { width: 100%; justify-content: center; }
    }
</style>

<div class="container">
    <a href="BO_Dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2 class="page-title">Waiting Lists</h2>

    <div class="tab-container">
        <?php foreach ($tabs as $key => $label): ?>
            <a href="?tab=<?php echo $key; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?>"
               class="tab-btn <?php echo ($activeTab === $key) ? 'active' : ''; ?>">
               <?php echo htmlspecialchars($label); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <form method="GET" action="" class="search-card">
        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab); ?>">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" class="search-input"
                       value="<?php echo htmlspecialchars($searchQuery); ?>"
                       placeholder="Search by NIC or applicant name..." aria-label="Search active waiting list">
                <?php if (!empty($searchQuery)): ?>
                    <a href="?tab=<?php echo htmlspecialchars($activeTab); ?>" class="search-clear-icon" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
    </form>

    <div class="table-card">
        <div class="section-header-bar">
            <h3 class="section-subtitle"><?php echo htmlspecialchars($tabs[$activeTab]); ?></h3>
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
                    <?php
                    $rows = $lists_data[$activeTab] ?? [];
                    if (!empty($rows)):
                        foreach ($rows as $index => $row):
                    ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($row['nic']); ?></td>
                            <td><?php echo htmlspecialchars($row['compNo'] ?? '-'); ?></td>
                            <td style="text-align: left;"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['designation'] ?: '-'); ?></td>
                            <td style="text-align: left;"><?php echo htmlspecialchars($row['workplace'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['allocDate'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['houseType'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars((string)($row['marks'] ?? 0)); ?></td>
                        </tr>
                    <?php
                        endforeach;
                    else:
                    ?>
                        <tr>
                            <td colspan="9" class="no-data">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>