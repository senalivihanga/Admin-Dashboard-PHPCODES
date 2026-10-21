<?php
/**
 * View Allocated Quarter - Sri Lanka Railways Admin
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "View Allocated Quarter - Sri Lanka Railways Admin";

// Use the central database connection
$conn = getDBConnection();

$item = null;
$recordId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($recordId > 0) {
    $stmt = $conn->prepare("SELECT * FROM allocated_quarters WHERE id = ? AND source = 'normal'");
    $stmt->bind_param("i", $recordId);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();
}
$conn->close();

// Admin Header eka include kirima
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
    .container { 
        max-width: 900px; 
        margin: 40px auto; 
        padding: 0 20px; 
    }
    .back-link { 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        color: #7A1C1C; 
        text-decoration: none; 
        font-weight: 600; 
        margin-bottom: 20px; 
        transition: color 0.2s;
    }
    .back-link:hover {
        color: #5c1414;
        text-decoration: underline;
    }
    .page-title { 
        text-align: center; 
        color: #000000; 
        font-size: 1.8rem; 
        margin-bottom: 25px; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        font-weight: 700; 
    }
    .view-card { 
        background: #ffffff; 
        padding: 30px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid #7A1C1C; 
    }
    
    /* Grid layout for balanced 2-column design */
    .view-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px 24px;
    }
    .view-item {
        background: #f8fafc;
        padding: 12px 16px;
        border-radius: 8px;
        border: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .view-label { 
        font-weight: 700; 
        color: #64748b; 
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .view-value { 
        color: #1e293b; 
        font-weight: 600; 
        font-size: 0.95rem; 
        word-break: break-word;
    }
    .error-msg {
        text-align: center; 
        color: #dc2626; 
        font-weight: 600; 
        padding: 20px 0;
        grid-column: span 2;
    }

    /* Mobile Responsive Design: 1 column on smaller screens */
    @media (max-width: 768px) {
        .container {
            margin: 20px 10px;
            padding: 0 10px;
        }
        .view-card {
            padding: 20px 15px;
        }
        .page-title {
            font-size: 1.4rem;
            margin-bottom: 20px;
        }
        .view-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
    }
</style>

<div class="container">
    <a href="allocated_quarters.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to List</a>

    <div class="view-card" id="viewCard">
        <h2 class="page-title">Allocation Detailed View</h2>
        <div class="view-grid">
            <?php if ($item !== null): ?>
                <div class="view-item"><div class="view-label">Auto ID</div><div class="view-value"><?php echo htmlspecialchars($item['id'] ?? $recordId); ?></div></div>
                <div class="view-item"><div class="view-label">NIC</div><div class="view-value"><?php echo htmlspecialchars($item['nic'] ?? '-'); ?></div></div>
                <div class="view-item"><div class="view-label">Computer No</div><div class="view-value"><?php echo htmlspecialchars($item['computer_no'] ?? '-'); ?></div></div>
                <div class="view-item"><div class="view-label">Quarter No</div><div class="view-value"><?php echo htmlspecialchars($item['quarter_no'] ?? '-'); ?></div></div>
                <div class="view-item"><div class="view-label">Quarter Name</div><div class="view-value"><?php echo htmlspecialchars($item['quarter_name'] ?? '-'); ?></div></div>
                <div class="view-item"><div class="view-label">Quarter Location</div><div class="view-value"><?php echo htmlspecialchars($item['quarter_location'] ?? '-'); ?></div></div>
                <div class="view-item"><div class="view-label">Quarter Type</div><div class="view-value"><?php echo htmlspecialchars($item['quarter_type'] ?? '-'); ?></div></div>
                <div class="view-item"><div class="view-label">Resident Name</div><div class="view-value"><?php echo htmlspecialchars($item['resident_name'] ?? '-'); ?></div></div>
                <div class="view-item"><div class="view-label">Designation</div><div class="view-value"><?php echo htmlspecialchars($item['designation'] ?? '-'); ?></div></div>
                <div class="view-item"><div class="view-label">Workplace</div><div class="view-value"><?php echo htmlspecialchars($item['workplace'] ?? '-'); ?></div></div>
                <div class="view-item"><div class="view-label">Allocated Date</div><div class="view-value"><?php echo htmlspecialchars($item['allocated_date'] ?? '-'); ?></div></div>
                <div class="view-item"><div class="view-label">Other</div><div class="view-value"><?php echo !empty($item['other']) ? htmlspecialchars($item['other']) : 'None'; ?></div></div>
            <?php else: ?>
                <div class="error-msg">Record not found!</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Admin Footer eka include kirima
require_once __DIR__ . '/../layouts/admin_footer.php';
?>