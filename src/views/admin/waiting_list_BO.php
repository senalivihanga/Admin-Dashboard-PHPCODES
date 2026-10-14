<?php
/**
 * Waiting Lists - Sri Lanka Railways Admin[cite: 3, 5, 7]
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Waiting Lists - Sri Lanka Railways Admin";

$host = 'localhost';
$dbname = 'railway_quarters_db';
$username = 'root';
$password = '';

$pdo = null;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Automatically create table & insert sample/test data if table is empty[cite: 3, 5, 7]
    $pdo->exec("CREATE TABLE IF NOT EXISTS waiting_lists (
        id INT AUTO_INCREMENT PRIMARY KEY,
        list_type VARCHAR(50) NOT NULL,
        nic VARCHAR(20) NOT NULL,
        computer_no VARCHAR(20) NOT NULL,
        applicant_name VARCHAR(100) NOT NULL,
        designation VARCHAR(100) NOT NULL,
        workplace VARCHAR(100) NOT NULL,
        allocated_date DATE NOT NULL,
        allocated_house_type VARCHAR(50) NOT NULL,
        other TEXT
    )");

    $stmtCheck = $pdo->query("SELECT COUNT(*) FROM waiting_lists");
    if ($stmtCheck->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO waiting_lists (list_type, nic, computer_no, applicant_name, designation, workplace, allocated_date, allocated_house_type, other) VALUES
        ('general', '199812345678', '101', 'A. B. Perera', 'Clerk', 'Colombo Fort', '2026-01-10', 'None', 'None'),
        ('general', '199812345648', '102', 'A. B. Perera', 'Clerk', 'Colombo Fort', '2026-01-10', 'None', 'None'),
        ('guard', '199287654321', '102', 'S. K. Silva', 'Train Guard', 'Maradana', '2026-02-14', 'Pending', 'None'),
        ('station_master', '198533445566', '103', 'M. N. Fernando', 'Station Master', 'Gampaha', '2026-03-01', 'Urgent', 'None'),
        ('controller', '199022334455', '104', 'T. G. Rathnayake', 'Controller', 'HQ Colombo', '2026-03-10', 'None', 'None'),
        ('deputy_commissioner', '198211223344', '105', 'W. M. D. Wickramasinghe', 'Deputy Commissioner', 'Main Office', '2026-04-05', 'Approved', 'None'),
        ('general', '200055556666', '999', 'Test User (Example)', 'Tester', 'Colombo HQ', '2026-10-08', 'Test Type', 'Test Note')");
    }

} catch (PDOException $e) {
    $pdo = null;
}

// Function to fetch waiting list records from database[cite: 3, 5, 7]
function getWaitingLists($pdo, $listType, $search = '') {
    if (!$pdo) return [];
    
    try {
        $sql = "SELECT * FROM waiting_lists WHERE list_type = :list_type";
        $params = ['list_type' => $listType];
        
        if (!empty($search)) {
            $sql .= " AND (nic LIKE :search OR applicant_name LIKE :search)";
            $params['search'] = "%$search%";
        }
        $sql .= " ORDER BY id ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

$activeTab = $_GET['tab'] ?? 'general';
$searchQuery = trim($_GET['search'] ?? '');

$tabs = [
    'general' => '1. General List',
    'guard' => '2. Guard List',
    'station_master' => '3. Station Master List',
    'controller' => '4. Controller List',
    'deputy_commissioner' => '5. Deputy Superintendent (Transportation) Colombo List'
];

// Include Admin Header layout[cite: 2, 4, 5, 7]
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
    * { box-sizing: border-box; }
    .container { max-width: 1250px; margin: 20px auto; padding: 0 15px; }
    .back-link { display: inline-flex; align-items: center; gap: 8px; color: #7A1C1C; text-decoration: none; font-weight: 600; margin-bottom: 15px; font-size: 0.95rem; }
    
    /* Updated Title Styling matching appli-approve-IB[cite: 6] */
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
    
    /* Tabs Styling */
    .tab-container { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-bottom: 20px; }
    .tab-btn { background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: all 0.2s ease; text-decoration: none; display: inline-block; text-align: center; }
    .tab-btn:hover { background-color: #e2e8f0; }
    .tab-btn.active { background-color: #7A1C1C; color: white; border-color: #7A1C1C; }

    /* Search Card (Adapted from IB code layout & styling)[cite: 2, 7] */
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

    /* Table Card & Responsiveness */
    .table-card { background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-top: 5px solid #7A1C1C; margin-bottom: 20px; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .section-header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px; }
    .section-subtitle { color: #7A1C1C; font-size: 1.2rem; font-weight: 700; margin: 0; }
    
    .waiting-table { width: 100%; border-collapse: collapse; text-align: left; min-width: 850px; }
    .waiting-table th, .waiting-table td { padding: 12px 10px; border: 1px solid #cbd5e1; vertical-align: middle; font-size: 0.85rem; text-align: center; }
    .waiting-table th { background-color: #f8fafc; color: #1e293b; font-weight: 700; }
    .waiting-table tbody td { color: #334155; font-weight: 400; }
    .waiting-table td:nth-child(4), .waiting-table td:nth-child(7) { text-align: left; }
    .no-data { text-align: center; padding: 20px; color: #64748b; font-style: italic; }

    /* Mobile Responsive Media Queries */
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

    <!-- Tabs -->
    <div class="tab-container">
        <?php foreach ($tabs as $key => $label): ?>
            <a href="?tab=<?php echo $key; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?>" 
               class="tab-btn <?php echo ($activeTab === $key) ? 'active' : ''; ?>">
               <?php echo $label; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Search Form -->
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

    <!-- Active Table Display -->
    <div class="table-card">
        <div class="section-header-bar">
            <h3 class="section-subtitle"><?php echo $tabs[$activeTab]; ?></h3>
        </div>
        <table class="waiting-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>NIC</th>
                    <th>Computer No.</th>
                    <th>Applicant's Name</th>
                    <th>Designation</th>
                    <th>Workplace</th>
                    <th>Allocated Date</th>
                    <th>Allocated House Type</th>
                    <th>Other</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rows = getWaitingLists($pdo, $activeTab, $searchQuery);
                if (!empty($rows)):
                    foreach ($rows as $index => $row):
                ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($row['nic']); ?></td>
                        <td><?php echo htmlspecialchars($row['computer_no']); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['applicant_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['designation']); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['workplace']); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['allocated_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['allocated_house_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['other']); ?></td>
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

<?php
// Include Admin Footer layout[cite: 4, 5, 7]
require_once __DIR__ . '/../layouts/admin_footer.php';
?>