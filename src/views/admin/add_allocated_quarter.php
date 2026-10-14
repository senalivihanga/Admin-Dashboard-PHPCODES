<?php
/**
 * Add Allocated Quarter - Sri Lanka Railways Admin
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection configuration[cite: 5]
$host = 'localhost';
$dbname = 'admin_dashboard_db';
$username = 'root';
$password = '';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "INSERT INTO allocated_quarters (nic, computer_no, quarter_no, quarter_name, quarter_location, quarter_type, resident_name, designation, workplace, allocated_date, other) 
                VALUES (:nic, :computerNo, :quarterNo, :quarterName, :quarterLocation, :quarterType, :residentName, :designation, :workplace, :allocatedDate, :other)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nic' => $_POST['nic'] ?? '',
            ':computerNo' => $_POST['computerNo'] ?? '',
            ':quarterNo' => $_POST['quarterNo'] ?? '',
            ':quarterName' => $_POST['quarterName'] ?? '',
            ':quarterLocation' => $_POST['quarterLocation'] ?? '',
            ':quarterType' => $_POST['quarterType'] ?? '',
            ':residentName' => $_POST['residentName'] ?? '',
            ':designation' => $_POST['designation'] ?? '',
            ':workplace' => $_POST['workplace'] ?? '',
            ':allocatedDate' => $_POST['allocatedDate'] ?? '',
            ':other' => $_POST['other'] ?? 'None'
        ]);

        // Data successfully save zalyanantara allocated_quarters.php page var redirect hoil[cite: 5]
        header("Location: allocated_quarters.php");
        exit();

    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

$pageTitle = "Add Allocated Quarter - Sri Lanka Railways";
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
    .container-custom {
        max-width: 1050px;
        margin: 30px auto;
        padding: 0 20px;
        box-sizing: border-box;
        width: 100%;
    }
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #7A1C1C;
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 20px;
        font-size: 1.05rem; /* Font size increased here */
    }
    .back-link:hover {
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
    .form-card {
        background: #ffffff;
        padding: 35px 40px;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04), 0 8px 16px -3px rgba(122, 28, 28, 0.06);
        border: 1px solid #e2e8f0;
        border-top: 5px solid #7A1C1C;
        box-sizing: border-box;
        width: 100%;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }
    .form-group {
        margin-bottom: 0;
    }
    .form-group.full-width {
        grid-column: span 2;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #334155;
        font-size: 0.9rem;
    }
    .form-control {
        width: 100%;
        padding: 11px 15px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.95rem;
        color: #1e293b;
        outline: none;
        transition: all 0.2s ease;
        box-sizing: border-box;
        background-color: #f8fafc;
    }
    .form-control:focus {
        border-color: #7A1C1C;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1);
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 10px;
        grid-column: span 2;
    }
    .btn-submit {
        background-color: #10b981;
        color: white;
        border: none;
        padding: 13px 20px;
        width: 100%;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        font-size: 1rem;
        grid-column: span 2;
        margin-top: 10px;
        transition: background 0.2s;
    }
    .btn-submit:hover {
        background-color: #059669;
    }
    .alert {
        padding: 12px;
        margin-bottom: 20px;
        border-radius: 8px;
        font-weight: 600;
        text-align: center;
    }
    .alert-error {
        background-color: #fee2e2;
        color: #991b1b;
    }
    @media (max-width: 768px) {
        .container-custom {
            margin: 15px 10px;
            padding: 0 10px;
        }
        .form-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        .form-group.full-width, .form-actions, .btn-submit {
            grid-column: span 1;
        }
        .form-card {
            padding: 20px 15px;
        }
        .page-title {
            font-size: 1.3rem;
        }
    }
</style>

<div class="container-custom">
    <a href="allocated_quarters.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to List</a>

    <div class="form-card">
        <h2 class="page-title">Add New Allocation</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="form-grid">
            <div class="form-group">
                <label for="nic">NIC</label>
                <input type="text" id="nic" name="nic" class="form-control" required placeholder="Enter NIC number">
            </div>
            <div class="form-group">
                <label for="computerNo">Computer No.</label>
                <input type="text" id="computerNo" name="computerNo" class="form-control" inputmode="numeric" pattern="[0-9]+" title="Enter digits only" required placeholder="Enter computer number">
            </div>
            <div class="form-group">
                <label for="quarterNo">Quarter No.</label>
                <input type="text" id="quarterNo" name="quarterNo" class="form-control" required placeholder="Enter quarter number">
            </div>
            <div class="form-group">
                <label for="quarterName">Quarter Name</label>
                <input type="text" id="quarterName" name="quarterName" class="form-control" required placeholder="Enter quarter name">
            </div>
            <div class="form-group">
                <label for="quarterLocation">Quarter Location</label>
                <input type="text" id="quarterLocation" name="quarterLocation" class="form-control" required placeholder="Enter quarter location">
            </div>
            <div class="form-group">
                <label for="quarterType">Quarter Type</label>
                <input type="text" id="quarterType" name="quarterType" class="form-control" required placeholder="Enter quarter type">
            </div>
            <div class="form-group">
                <label for="residentName">Resident Name</label>
                <input type="text" id="residentName" name="residentName" class="form-control" required placeholder="Enter resident name">
            </div>
            <div class="form-group">
                <label for="designation">Designation</label>
                <input type="text" id="designation" name="designation" class="form-control" required placeholder="Enter designation">
            </div>
            <div class="form-group">
                <label for="workplace">Workplace</label>
                <input type="text" id="workplace" name="workplace" class="form-control" required placeholder="Enter workplace">
            </div>
            <div class="form-group">
                <label for="allocatedDate">Allocated Date</label>
                <input type="date" id="allocatedDate" name="allocatedDate" class="form-control" required>
            </div>
            <div class="form-group full-width">
                <label for="other">Other</label>
                <textarea id="other" name="other" class="form-control" rows="3" placeholder="Additional notes (optional)"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Save Allocation</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('computerNo').addEventListener('input', event => {
        event.target.value = event.target.value.replace(/\D/g, '');
    });
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>