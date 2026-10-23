<?php
/**
 * Waiting Lists - Sri Lanka Railways Admin
 *
 * Live data from `waiting_list` joined to `users` and `applications`.
 * Same final-approval gate as waiting_list_FA.php / waiting_list_SC.php.
 * General tab lists all rows; the other four tabs populate by designation.
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Waiting Lists - Sri Lanka Railways Admin";

$searchQuery = trim($_GET['search'] ?? '');

$lists_data = [
    'general'             => [],
    'guard'               => [],
    'station_master'      => [],
    'controller'          => [],
    'deputy_commissioner' => [],
];

$conn = getDBConnection();

$sql = "SELECT wl.nic,
               u.computer_number                          AS compNo,
               u.name,
               COALESCE(u.applicant_designation, '')      AS designation,
               COALESCE(u.applicant_current_work_station, '') AS workplace,
               wl.applied_date                            AS allocDate,
               wl.quarter_type                            AS houseType,
               COALESCE(wl.employee_marks, 0)             AS marks
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

// Every approved row starts in General, then buckets by designation
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
    :root {
        --primary-color: #7A1C1C;
        --primary-dark: #5c1515;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --text-main: #1e293b;
        --border-color: #cbd5e1;
    }

    .container { max-width: 1250px; margin: 20px auto; padding: 0 15px; box-sizing: border-box; }
    .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--primary-color); text-decoration: none; font-weight: 600; margin-bottom: 15px; font-size: 0.95rem; }
    .back-link:hover { text-decoration: underline; }

    .page-title {
        text-align: center;
        color: var(--primary-color);
        font-size: 1.8rem;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-decoration: underline;
        text-underline-offset: 6px;
        font-weight: 700;
    }

    .tab-container { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-bottom: 20px; }
    .tab-btn { background-color: #f1f5f9; color: #334155; border: 1px solid var(--border-color); padding: 8px 14px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: all 0.2s ease; text-decoration: none; display: inline-block; text-align: center; }
    .tab-btn:hover { background-color: #e2e8f0; }
    .tab-btn.active { background-color: var(--primary-color); color: white; border-color: var(--primary-color); }

    .section-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .section-subtitle {
        color: var(--primary-color);
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0;
    }

    .inline-search-form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-input-container {
        position: relative;
        width: 260px;
    }

    .search-input {
        width: 100%;
        padding: 8px 32px 8px 12px;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        font-size: 0.85rem;
        outline: none;
        box-sizing: border-box;
    }

    .search-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1);
    }

    .search-clear-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.9rem;
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
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        text-decoration: none;
        transition: background 0.2s;
    }
    .search-btn:hover {
        background-color: var(--primary-dark);
    }

    .table-card { background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 5px solid var(--primary-color); display: none; overflow-x: auto; margin-bottom: 30px; }
    .table-card.active-table { display: block; }

    .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .waiting-table { width: 100%; border-collapse: collapse; text-align: left; min-width: 850px; }
    .waiting-table th, .waiting-table td { padding: 10px 8px; border: 1px solid var(--border-color); vertical-align: middle; font-size: 0.85rem; text-align: center; }
    .waiting-table th { background-color: #f8fafc; color: var(--text-main); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; }
    .waiting-table td { color: #334155; }
    .waiting-table td:nth-child(4), .waiting-table td:nth-child(6) { text-align: left; }

    @media (max-width: 768px) {
        .container { margin: 10px auto; padding: 0 10px; }
        .page-title { font-size: 1.4rem; margin-bottom: 15px; }
        .section-header-bar { flex-direction: column; align-items: stretch; }
        .inline-search-form { width: 100%; flex-direction: column; align-items: stretch; }
        .search-input-container { width: 100%; }
        .search-btn { width: 100%; justify-content: center; }
        .tab-container { gap: 6px; }
        .tab-btn { padding: 6px 10px; font-size: 0.78rem; flex: 1 1 calc(50% - 6px); text-align: center; }
    }
</style>

<main class="container">
    <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2 class="page-title">Waiting Lists</h2>

    <div class="tab-container">
        <?php
        $tabs = [
            'general'             => '1. General List',
            'guard'               => '2. Guard List',
            'station_master'      => '3. Station Master List',
            'controller'          => '4. Controller List',
            'deputy_commissioner' => '5. Deputy Superintendent ( Transportation ) Colombo List'
        ];
        $first = true;
        foreach ($tabs as $key => $label):
            $isActive = $first ? 'active' : '';
            $first = false;
        ?>
            <button type="button" class="tab-btn <?php echo $isActive; ?>" onclick="showTable('<?php echo $key; ?>', event)"><?php echo htmlspecialchars($label); ?></button>
        <?php endforeach; ?>
    </div>

    <?php
    $first = true;
    foreach ($lists_data as $list_key => $rows):
        $isCardActive = $first ? 'active-table' : '';
        $first = false;
    ?>
        <div id="<?php echo $list_key; ?>" class="table-card <?php echo $isCardActive; ?>">

            <div class="section-header-bar">
                <h3 class="section-subtitle"><?php echo htmlspecialchars($tabs[$list_key]); ?></h3>

                <form method="GET" action="" class="inline-search-form">
                    <input type="hidden" name="tab" value="<?php echo htmlspecialchars($list_key); ?>">
                    <div class="search-input-container">
                        <input type="text" name="search" class="search-input"
                               placeholder="Search by NIC or name"
                               value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <?php if (!empty($searchQuery)): ?>
                            <a href="?tab=<?php echo htmlspecialchars($list_key); ?>" class="search-clear-icon" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="waiting-table" id="<?php echo $list_key; ?>Table">
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
                                    <td><?php echo htmlspecialchars($row['designation'] ?: '-'); ?></td>
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
</main>

<script>
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