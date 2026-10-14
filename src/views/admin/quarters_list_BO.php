<?php
/**
 * Quarters Details - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Quarters Details - Sri Lanka Railways Admin";

// Quarters default data array[cite: 11]
$quartersData = [
    ["quarterNum" => "Q-01", "quarterName" => "Bungalow A", "division" => "Way & Works", "ownership" => "Railway Department", "quarterArea" => "1,450 sq.ft", "location" => "Colombo", "address" => "No. 12, Railway Avenue, Colombo 10", "quarterType" => "Bungalows", "assessmentRent" => "5,500.00", "status" => "Under Maintenance", "electric" => true, "water" => true],
    ["quarterNum" => "Q-02", "quarterName" => "Type II House", "division" => "Signal & Telecommunication", "ownership" => "Railway Department", "quarterArea" => "950 sq.ft", "location" => "Kandy", "address" => "No. 8, Peradeniya Road, Kandy", "quarterType" => "Type II", "assessmentRent" => "4,200.00", "status" => "Available", "electric" => true, "water" => true],
    ["quarterNum" => "Q-03", "quarterName" => "Staff Flat", "division" => "Electrical", "ownership" => "Railway Department", "quarterArea" => "800 sq.ft", "location" => "Galle", "address" => "No. 22, Fort Road, Galle", "quarterType" => "Type I", "assessmentRent" => "3,900.00", "status" => "Occupied", "electric" => true, "water" => false],
    ["quarterNum" => "Q-04", "quarterName" => "Annex Unit", "division" => "Mechanical", "ownership" => "Railway Department", "quarterArea" => "600 sq.ft", "location" => "Anuradhapura", "address" => "No. 45, Station Road, Anuradhapura", "quarterType" => "Type III", "assessmentRent" => "2,750.00", "status" => "Available", "electric" => false, "water" => true]
];

// Search filter handling in PHP[cite: 11]
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $quartersData = array_filter($quartersData, function($item) use ($search) {
        return stripos($item['quarterNum'], $search) !== false || stripos($item['quarterName'], $search) !== false;
    });
}

// Header include[cite: 11]
require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    :root {
        --primary-color: #7A1C1C;
        --primary-dark: #5c1414;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --text-main: #1e293b;
        --border-color: #e2e8f0;
    }

    .container { width: 100%; max-width: 1400px; box-sizing: border-box; margin: 30px auto; padding: 0 20px; }
    .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--primary-color); text-decoration: none; font-weight: 600; margin-bottom: 20px; font-size: 0.95rem; }
    .back-link:hover { text-decoration: underline; }
    
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
        padding: 20px 25px; 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.04); 
        margin-bottom: 25px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        flex-wrap: wrap; 
        gap: 15px; 
        border: 1px solid var(--border-color);
        box-sizing: border-box;
    }

    .search-wrapper { 
        display: flex; 
        gap: 12px; 
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
        padding: 10px 38px 10px 14px; 
        border-radius: 8px; 
        border: 1px solid #cbd5e1; 
        font-size: 0.9rem; 
        outline: none; 
        box-sizing: border-box;
        background-color: #ffffff;
        transition: all 0.2s;
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
        padding: 10px 20px; 
        border-radius: 8px; 
        font-weight: 600; 
        cursor: pointer; 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        font-size: 0.9rem;
        transition: background 0.2s; 
    }
    
    .search-btn:hover { 
        background-color: var(--primary-dark); 
    }

    .maintenance-btn { 
        background-color: #2563eb; 
        color: white; 
        padding: 10px 20px; 
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
        background: #ffffff; 
        padding: 20px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid var(--primary-color); 
        overflow: hidden; 
        margin-bottom: 30px;
    }
    
    .table-responsive { 
        width: 100%; 
        overflow-x: auto; 
        -webkit-overflow-scrolling: touch; 
    }
    
    .quarters-table { 
        width: 100%; 
        border-collapse: collapse; 
        min-width: 1100px; 
    }
    
    .quarters-table th, .quarters-table td { 
        padding: 12px 14px; 
        border: 1px solid #e2e8f0; 
        vertical-align: middle; 
        font-size: 0.88rem; 
    }
    
    .quarters-table th { 
        background-color: #f8fafc; 
        color: #1e293b; 
        font-weight: 700; 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        letter-spacing: 0.5px; 
        text-align: center;
    }
    
    /* Balanced Text Alignments for readability */
    .quarters-table td { color: #4b5563; }
    .quarters-table td:nth-child(1),
    .quarters-table td:nth-child(2),
    .quarters-table td:nth-child(6),
    .quarters-table td:nth-child(9),
    .quarters-table td:nth-child(10),
    .quarters-table td:nth-child(11),
    .quarters-table td:nth-child(12) {
        text-align: center;
    }

    .quarters-table td:nth-child(3),
    .quarters-table td:nth-child(4),
    .quarters-table td:nth-child(5),
    .quarters-table td:nth-child(7),
    .quarters-table td:nth-child(8) {
        text-align: left;
    }

    .quarters-table tbody tr { transition: background-color 0.15s ease; }
    .quarters-table tbody tr:hover { background-color: #f1f5f9; }
    
    .facility-container { display: flex; flex-direction: column; gap: 5px; align-items: center; }
    .facility-item { display: flex; align-items: center; justify-content: space-between; width: 100%; max-width: 130px; gap: 8px; font-size: 0.8rem; }
    
    .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .badge-active { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-inactive { background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

    @media (max-width: 768px) {
        .container { margin: 15px auto; padding: 0 10px; }
        .page-title { font-size: 1.4rem; margin-bottom: 15px; }
        .search-card { flex-direction: column; align-items: stretch; padding: 15px; }
        .search-wrapper { width: 100%; }
        .search-input-container { max-width: 100%; }
        .search-btn, .maintenance-btn { width: 100%; justify-content: center; box-sizing: border-box; }
        .table-card { padding: 12px; }
    }
</style>

<main class="container">
    <a href="BO_Dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2 class="page-title">Quarters Details</h2>

    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" class="search-input" placeholder="Search by Quarter No or Name..." value="<?php echo htmlspecialchars($search); ?>">
                <?php if (!empty($search)): ?>
                    <a href="?" class="search-clear-icon" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Search</button>
        </div>
        <a href="maintenance_BO.php" class="maintenance-btn"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i> Under Maintenance Quarters</a>
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
                <tbody>
                    <?php if (count($quartersData) > 0): ?>
                        <?php $counter = 1; foreach ($quartersData as $item): ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><strong><?php echo htmlspecialchars($item['quarterNum'] ?? 'N/A'); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['quarterName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['division'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['ownership'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['quarterArea'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['location'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['address'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['quarterType'] ?? 'N/A'); ?></td>
                                <td>Rs. <?php echo htmlspecialchars($item['assessmentRent'] ?? '0.00'); ?></td>
                                <td>
                                    <span style="font-weight: 600; color: <?php echo ($item['status'] === 'Available') ? '#166534' : (($item['status'] === 'Occupied') ? '#1e40af' : '#d97706'); ?>;">
                                        <?php echo htmlspecialchars($item['status'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="facility-container">
                                        <div class="facility-item">
                                            <span>Electricity</span>
                                            <?php if ($item['electric']): ?>
                                                <span class="badge badge-active">Available</span>
                                            <?php else: ?>
                                                <span class="badge badge-inactive">Unavailable</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="facility-item">
                                            <span>Water</span>
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
                    <?php else: ?>
                        <tr>
                            <td colspan="12" style="text-align: center; padding: 30px; color: #64748b; font-style: italic;">No quarters found matching your search.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php
// Footer include[cite: 11]
require_once __DIR__ . '/../layouts/admin_footer.php';
?>