<?php
/**
 * Edit Allocated Quarter - Sri Lanka Railways Admin
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Edit Allocated Quarter - Sri Lanka Railways";

// Use the central database connection
$conn = getDBConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$errorMessage = '';

// Form submit zalyavar data update karnyasathi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nic = $_POST['nic'] ?? '';
    $computerNo = $_POST['computerNo'] ?? '';
    $quarterNo = $_POST['quarterNo'] ?? '';
    $quarterName = $_POST['quarterName'] ?? '';
    $quarterLocation = $_POST['quarterLocation'] ?? '';
    $quarterType = $_POST['quarterType'] ?? '';
    $residentName = $_POST['residentName'] ?? '';
    $designation = $_POST['designation'] ?? '';
    $workplace = $_POST['workplace'] ?? '';
    $allocatedDate = $_POST['allocatedDate'] ?? '';
    $other = $_POST['other'] ?? 'None';

    $sql = "UPDATE allocated_quarters SET nic = ?, computer_no = ?, quarter_no = ?, quarter_name = ?, quarter_location = ?, quarter_type = ?, resident_name = ?, designation = ?, workplace = ?, allocated_date = ?, other = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssi", $nic, $computerNo, $quarterNo, $quarterName, $quarterLocation, $quarterType, $residentName, $designation, $workplace, $allocatedDate, $other, $id);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: allocated_quarters.php");
        exit();
    } else {
        $errorMessage = "Error updating record: " . $conn->error;
        $stmt->close();
    }
}

// Existing record fetch karne
$stmt = $conn->prepare("SELECT * FROM allocated_quarters WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$record) {
    echo "<script>alert('Record not found!'); window.location.href='allocated_quarters.php';</script>";
    exit();
}

// Admin Header එක ඇතුළත් කිරීම
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
        font-size: 1.05rem;
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
        background-color: #7A1C1C;
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
        background-color: #5c1414;
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
        <h2 class="page-title">Edit Allocation Details</h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="form-grid">
            <div class="form-group">
                <label for="nic">NIC</label>
                <input type="text" id="nic" name="nic" class="form-control" value="<?php echo htmlspecialchars($record['nic'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="computerNo">Computer No.</label>
                <input type="text" id="computerNo" name="computerNo" class="form-control" inputmode="numeric" pattern="[0-9]+" title="Enter digits only" value="<?php echo htmlspecialchars($record['computer_no'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="quarterNo">Quarter No.</label>
                <input type="text" id="quarterNo" name="quarterNo" class="form-control" value="<?php echo htmlspecialchars($record['quarter_no'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="quarterName">Quarter Name</label>
                <input type="text" id="quarterName" name="quarterName" class="form-control" value="<?php echo htmlspecialchars($record['quarter_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="quarterLocation">Quarter Location</label>
                <input type="text" id="quarterLocation" name="quarterLocation" class="form-control" value="<?php echo htmlspecialchars($record['quarter_location'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="quarterType">Quarter Type</label>
                <input type="text" id="quarterType" name="quarterType" class="form-control" value="<?php echo htmlspecialchars($record['quarter_type'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="residentName">Resident Name</label>
                <input type="text" id="residentName" name="residentName" class="form-control" value="<?php echo htmlspecialchars($record['resident_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="designation">Designation</label>
                <input type="text" id="designation" name="designation" class="form-control" value="<?php echo htmlspecialchars($record['designation'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="workplace">Workplace</label>
                <input type="text" id="workplace" name="workplace" class="form-control" value="<?php echo htmlspecialchars($record['workplace'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="allocatedDate">Allocated Date</label>
                <input type="date" id="allocatedDate" name="allocatedDate" class="form-control" value="<?php echo htmlspecialchars($record['allocated_date'] ?? ''); ?>" required>
            </div>
            <div class="form-group full-width">
                <label for="other">Other</label>
                <textarea id="other" name="other" class="form-control" rows="3"><?php echo htmlspecialchars($record['other'] ?? ''); ?></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit">Update Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Computer number එකට අංක පමණක් ඇතුළත් කිරීමට ඇති සීමාව
    document.getElementById('computerNo').addEventListener('input', event => {
        event.target.value = event.target.value.replace(/\D/g, '');
    });
</script>

<?php
// Admin Footer එක ඇතුළත් කිරීම
require_once __DIR__ . '/../layouts/admin_footer.php';
?>