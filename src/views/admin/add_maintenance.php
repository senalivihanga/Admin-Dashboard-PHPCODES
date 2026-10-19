<?php
/**
 * Add Maintenance Quarter - Sri Lanka Railways (database-backed)
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Add Maintenance Quarter - Sri Lanka Railways Admin";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$error = '';

$old = [
    'quarter_number'        => '',
    'quarter_name'          => '',
    'location_name'         => '',
    'quarter_type'          => '',
    'maintenance_status'    => 'Under Maintenance',
    'last_maintenance_date' => '',
    'notes'                 => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $v) {
        if (isset($_POST[$k])) $old[$k] = trim((string)$_POST[$k]);
    }

    if ($old['quarter_number'] === '' || $old['location_name'] === '' || $old['quarter_type'] === '') {
        $error = 'Quarter No, Location and Quarter Type are required.';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare(
            "INSERT INTO maintenance
                (quarter_number, quarter_name, location_name, quarter_type,
                 maintenance_status, last_maintenance_date, notes)
             VALUES (?,?,?,?,?,?,?)"
        );
        $lastMaint = $old['last_maintenance_date'] !== '' ? $old['last_maintenance_date'] : null;
        $notes = $old['notes'] !== '' ? $old['notes'] : null;
        $stmt->bind_param(
            "sssssss",
            $old['quarter_number'],
            $old['quarter_name'],
            $old['location_name'],
            $old['quarter_type'],
            $old['maintenance_status'],
            $lastMaint,
            $notes
        );
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header('Location: ' . baseUrl('admin/maintenance.php'));
            exit;
        }
        $error = 'Insert failed: ' . $conn->error;
        $stmt->close();
        $conn->close();
    }
}

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    html, body { height: 100%; margin: 0; padding: 0; overflow-y: auto;
        background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .container { max-width: 1050px; margin: 20px auto; padding: 0 20px; width: 100%; box-sizing: border-box; }
    .form-container { width: 100%; background: white; padding: 35px; border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-top: 5px solid #7A1C1C; box-sizing: border-box; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group.full-width { grid-column: span 2; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #334155; font-size: 0.85rem; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px;
        border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem;
        box-sizing: border-box; outline: none; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        border-color: #7A1C1C; box-shadow: 0 0 0 3px rgba(122,28,28,0.1); }
    .btn-submit { grid-column: span 2; background-color: #10b981; color: white; border: none;
        padding: 12px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;
        font-size: 1rem; margin-top: 10px; }
    .btn-submit:hover { background-color: #059669; }
    .back-link { display: inline-flex; align-items: center; gap: 6px; color: #7A1C1C;
        text-decoration: none; font-weight: 600; margin-bottom: 20px; font-size: 0.85rem; }
    .back-link:hover { text-decoration: underline; }
    .alert-error { background: #fee2e2; color: #991b1b; padding: 12px;
        border-radius: 8px; margin-bottom: 20px; text-align: center;
        font-weight: 600; grid-column: span 2; }
</style>

<div class="container">
    <a href="<?= baseUrl('admin/maintenance.php') ?>" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Maintenance List
    </a>

    <div class="form-container">
        <h2 style="color: #0a0a0a; margin-bottom: 25px; text-align: center; font-size: 1.6rem; font-weight: 700;">
            Add Quarter Under Maintenance
        </h2>

        <form method="POST" class="form-grid">
            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label>Quarter No. *</label>
                <input type="text" name="quarter_number" required value="<?= htmlspecialchars($old['quarter_number']) ?>">
            </div>

            <div class="form-group">
                <label>Quarter Name</label>
                <input type="text" name="quarter_name" value="<?= htmlspecialchars($old['quarter_name']) ?>">
            </div>

            <div class="form-group">
                <label>Quarter Location *</label>
                <input type="text" name="location_name" required value="<?= htmlspecialchars($old['location_name']) ?>">
            </div>

            <div class="form-group">
                <label>Quarter Type *</label>
                <select name="quarter_type" required>
                    <option value="">-- Select --</option>
                    <?php foreach (['Bungalows','Type I','Type II','Type III','Row','Flats','Single','Double'] as $t): ?>
                        <option value="<?= $t ?>" <?= $old['quarter_type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Status *</label>
                <select name="maintenance_status" required>
                    <?php foreach (['Under Maintenance','Repair in Progress','Painting Pending','Electrical Work'] as $s): ?>
                        <option value="<?= $s ?>" <?= $old['maintenance_status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Last Maintenance Date</label>
                <input type="date" name="last_maintenance_date" value="<?= htmlspecialchars($old['last_maintenance_date']) ?>">
            </div>

            <div class="form-group full-width">
                <label>Notes</label>
                <textarea name="notes" rows="3"><?= htmlspecialchars($old['notes']) ?></textarea>
            </div>

            <button type="submit" class="btn-submit">Save Maintenance Record</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>