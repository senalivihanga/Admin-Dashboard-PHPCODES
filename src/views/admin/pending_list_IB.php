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
    'name' => 'Immediate Boss',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'IB'
];

$user = [
    'name' => $userData['name']
];

$searchVal = trim($_GET['search'] ?? '');
$conn = getDBConnection();
$sql = "SELECT a.id, a.application_no, a.nic, a.computer_no, a.boss_status,
               u.name, u.applicant_designation
        FROM applications AS a
        INNER JOIN users AS u ON u.nic = a.nic
        WHERE a.boss_status = 'pending'
          AND a.application_status = 'submitted'";

if ($searchVal !== '') {
    $sql .= " AND (a.application_no LIKE ? OR a.nic LIKE ? OR a.computer_no LIKE ? OR u.name LIKE ?)";
    $searchTerm = '%' . $searchVal . '%';
    $stmt = $conn->prepare($sql . ' ORDER BY a.application_date ASC, a.id ASC');
    $stmt->bind_param('ssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare($sql . ' ORDER BY a.application_date ASC, a.id ASC');
}
$stmt->execute();
$pendingData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Admin Header එක ඇතුළත් කිරීම[cite: 19]
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-y: auto;
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
        font-weight: 700; /* Resubmitted කෝඩ් එකේ ආකාරයට බෝල්ඩ් කරන ලදී[cite: 20] */
    }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px; 
        color: #7A1C1C;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .back-link:hover {
        text-decoration: underline;
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

    /* Responsive adjustments for mobile devices */
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
    <a href="IB_Dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    
    <div class="approval-header">
        <h2>Pending Applications Log</h2>
    </div>
    
    <!-- Search Form handled via PHP GET -->
    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
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
                if (empty($pendingData)) {
                    echo '<tr><td colspan="7" style="text-align: center; color: #64748b; padding: 20px;">No pending records found.</td></tr>';
                } else {
                    $counter = 1;
                    foreach ($pendingData as $item) {
                        echo '<tr>';
                        echo '<td>' . $counter++ . '</td>';
                        echo '<td><strong>' . htmlspecialchars($item['application_no'] ?? (string)$item['id']) . '</strong></td>';
                        echo '<td>' . htmlspecialchars($item['nic']) . '</td>';
                        echo '<td>' . htmlspecialchars($item['computer_no']) . '</td>';
                        echo '<td>' . htmlspecialchars($item['name']) . '</td>';
                        echo '<td>' . htmlspecialchars($item['applicant_designation'] ?? '') . '</td>';
                        echo '<td><span class="badge"><i class="fa-solid fa-clock" title="Pending"></i></span></td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</main>

<?php
// Admin Footer එක ඇතුළත් කිරීම[cite: 19]
require_once __DIR__ . '/../layouts/admin_footer.php';
?>