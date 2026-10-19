<?php
/**
 * Edit Maintenance Quarter - Sri Lanka Railways (database-backed)
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Edit Maintenance Quarter - Sri Lanka Railways Admin";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$error = '';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . baseUrl('admin/maintenance.php'));
    exit;
}

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quarter_number        = trim($_POST['quarter_number'] ?? '');
    $quarter_name          = trim($_POST['quarter_name'] ?? '');
    $location_name         = trim($_POST['location_name'] ?? '');
    $quarter_type          = trim($_POST['quarter_type'] ?? '');
    $maintenance_status    = trim($_POST['maintenance_status'] ?? 'Under Maintenance');
    $last_maintenance_date = ($_POST['last_maintenance_date'] ?? '') ?: null;
    $notes                 = trim($_POST['notes'] ?? '') ?: null;

    if ($quarter_number === '' || $location_name === '' || $quarter_type === '') {
        $error = 'Quarter No, Location and Quarter Type are required.';
    } else {
        $stmt = $conn->prepare(
            "UPDATE maintenance
                SET quarter_number = ?, quarter_name = ?, location_name = ?, quarter_type = ?,
                    maintenance_status = ?, last_maintenance_date = ?, notes = ?
              WHERE id = ?"
        );
        $stmt->bind_param(
            "sssssssi",
            $quarter_number, $quarter_name, $location_name, $quarter_type,
            $maintenance_status, $last_maintenance_date, $notes, $id
        );
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header('Location: ' . baseUrl('admin/maintenance.php'));
            exit;
        }
        $error = 'Update failed: ' . $conn->error;
        $stmt->close();
    }
}

$stmt = $conn->prepare(
    "SELECT id, quarter_number, quarter_name, location_name, quarter_type,
            maintenance_status, last_maintenance_date, notes
       FROM maintenance WHERE id = ? LIMIT 1"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$row) {
    header('Location: ' . baseUrl('admin/maintenance.php'));
    exit;
}

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    html, body { height: 100%; margin: 0; padding: 0; overflow-y: auto;
        background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .container { max-width: 900px; margin: 20px auto; padding: 0 20px; width: 100%; box-sizing: border-box; }
    .card { background: #ffffff; padding: 35px; border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08); border-top: 5px solid #7A1C1C; }
    .card h2 { color: #1e293b; font-size: 1.5rem; margin-bottom: 20px;
        text-transform: uppercase; text-align: center; font-weight: 700; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group { margin-bottom: 0; }
    .form-group.full-width { grid-column: span 2; }
    .form-group label { display: block; font-weight: 600; color: #475569;
        margin-bottom: 8px; font-size: 0.9rem; }
    .form-group input, .form-group select, .form-group textarea { width: 100%;
        padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 6px;
        outline: none; font-size: 0.95rem; box-sizing: border-box; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        border-color: #7A1C1C; box-shadow: 0 0 0 3px rgba(122,28,28,0.1); }
    .btn-submit { grid-column: span 2; background-color: #7A1C1C; color: white;
        border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer;
        font-weight: 600; width: 100%; font-size: 1rem; margin-top: 15px; }
    .btn-submit:hover { background-color: #5c1414; }
    .alert-error { background: #fee2e2; color: #991b1b; padding: 12px;
        border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 600; }
    .back-link { display: inline-flex; align-items: center; gap: 6px; color: #7A1C1C;
        text-decoration: none; font-weight: 600; margin-bottom: 20px; font-size: 0.85rem; }
    @media (max-width: 768px) {
        .form-row { grid-template-columns: 1fr; }
        .form-group.full-width, .btn-submit { grid-column: span 1; }
    }
</style>

<div class="container">
    <a href="<?= baseUrl('admin/maintenance.php') ?>" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Maintenance List
    </a>

    <div class="card">
        <h2>Edit Maintenance Record</h2>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-row">
            <div class="form-group">
                <label>Quarter No. *</label>
                <input type="text" name="quarter_number" required value="<?= htmlspecialchars($row['quarter_number']) ?>">
            </div>
            <div class="form-group">
                <label>Quarter Name</label>
                <input type="text" name="quarter_name" value="<?= htmlspecialchars($row['quarter_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Quarter Location *</label>
                <input type="text" name="location_name" required value="<?= htmlspecialchars($row['location_name']) ?>">
            </div>
            <div class="form-group">
                <label>Quarter Type *</label>
                <select name="quarter_type" required>
                    <?php foreach (['Bungalows','Type I','Type II','Type III','Row','Flats','Single','Double'] as $t): ?>
                        <option value="<?= $t ?>" <?= $row['quarter_type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select name="maintenance_status" required>
                    <?php foreach (['Under Maintenance','Repair in Progress','Painting Pending','Electrical Work'] as $s): ?>
                        <option value="<?= $s ?>" <?= $row['maintenance_status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Last Maintenance Date</label>
                <input type="date" name="last_maintenance_date" value="<?= htmlspecialchars($row['last_maintenance_date'] ?? '') ?>">
            </div>
            <div class="form-group full-width">
                <label>Notes</label>
                <textarea name="notes" rows="3"><?= htmlspecialchars($row['notes'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn-submit"><i class="fa-solid fa-floppy-disk"></i> Update Changes</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>