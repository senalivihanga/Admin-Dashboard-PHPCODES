<?php
/**
 * Quarters Maintenance - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Quarters Maintenance - Sri Lanka Railways";

// --- Fetch maintenance records from the database ---
$conn = getDBConnection();
$searchQuery = trim($_GET['search'] ?? '');

if ($searchQuery !== '') {
    $like = '%' . $searchQuery . '%';
    $stmt = $conn->prepare(
        "SELECT id, quarter_number, quarter_name, location_name, quarter_type,
                maintenance_status, last_maintenance_date, notes, created_at
           FROM maintenance
          WHERE quarter_number LIKE ? OR quarter_name LIKE ? OR location_name LIKE ?
          ORDER BY id ASC"
    );
    $stmt->bind_param("sss", $like, $like, $like);
} else {
    $stmt = $conn->prepare(
        "SELECT id, quarter_number, quarter_name, location_name, quarter_type,
                maintenance_status, last_maintenance_date, notes, created_at
           FROM maintenance
          ORDER BY id ASC"
    );
}
$stmt->execute();
$maintData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Admin Header include
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
    :root {
        --primary-color: #7A1C1C;
        --primary-dark: #571a1a;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --text-main: #1e293b;
        --border-color: #e2e8f0;
    }

    .container { 
        max-width: 1400px; 
        margin: 0 auto; 
        padding: 20px 30px; 
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
    
    .page-header {
        text-align: center;
        margin-bottom: 25px;
    }

    /* Updated Title Style matching Application Approval List */
    .page-title { 
        font-size: 1.8rem;
        color: var(--primary-color);
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-decoration: underline;
        text-underline-offset: 6px;
        font-weight: 700;
    }

    /* Updated Search Card Style matching Application Approval List */
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
        font-size: 0.9rem;
        text-decoration: none; 
        transition: background 0.2s; 
        justify-content: center;
    }
    
    .search-btn:hover { 
        background-color: var(--primary-dark); 
    }

    .table-card { 
        background: var(--card-bg); 
        padding: 20px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.05); 
        border-top: 5px solid var(--primary-color); 
        overflow-x: auto; 
        width: 100%;
        box-sizing: border-box;
        -webkit-overflow-scrolling: touch;
    }

    .maint-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: center; 
        font-size: 0.9rem; 
        min-width: 900px;
    }
    
    .maint-table th, .maint-table td { 
        padding: 14px 16px; 
        border: 1px solid var(--border-color); 
        vertical-align: middle; 
    }
    
    .maint-table th { 
        background-color: #f8fafc; 
        color: var(--text-main); 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        letter-spacing: 0.5px;
    }
    
    .maint-table td { 
        color: #4b5563; 
    }
    
    .maint-table tbody td:nth-child(2) { 
        font-weight: 700; 
    }
    
    .no-data { 
        text-align: center; 
        padding: 30px; 
        color: #64748b; 
        font-style: italic; 
    }

    .status-badge {
        display: inline-block;
        padding: 5px 14px;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 50px;
        text-align: center;
    }
    
    .status-maintenance {
        background-color: #fef3c7;
        color: #d97706;
        border: 1px solid #fde68a;
    }

    @media screen and (max-width: 768px) {
        .container { padding: 10px 15px; }
        .page-title { font-size: 1.4rem; }
        .search-card { flex-direction: column; align-items: stretch; padding: 12px; }
        .search-wrapper { width: 100%; max-width: 100%; }
        .search-input-container { max-width: 100%; }
        .search-btn { width: 100%; }
        .table-card { padding: 10px; }
    }
</style>

<div class="container">
    <a href="quarters_list_BO.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Quarters List</a>
    
    <div class="page-header">
        <h2 class="page-title">Quarters Maintenance Details</h2>
    </div>

    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" class="search-input" placeholder="Search by Quarter No or Name..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                <?php if (!empty($searchQuery)): ?>
                    <a href="maintenance_BO.php" class="search-clear-icon" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
    </form>

    <div class="table-card">
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
            <tbody>
                <?php if (empty($maintData)): ?>
                    <tr><td colspan="7" class="no-data">No quarters are currently under maintenance or matching your search.</td></tr>
                <?php else: ?>
                    <?php foreach ($maintData as $index => $item): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($item['quarter_number'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($item['quarter_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($item['location_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($item['quarter_type'] ?? '-'); ?></td>
                            <td><span class="status-badge status-maintenance"><?php echo htmlspecialchars($item['maintenance_status'] ?? 'Under Maintenance'); ?></span></td>
                            <td><?php echo htmlspecialchars($item['last_maintenance_date'] ?? 'Not Recorded'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Admin Footer include
require_once __DIR__ . '/../layouts/admin_footer.php';
?>