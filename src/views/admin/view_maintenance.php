<?php
/**
 * View Maintenance Quarter - Sri Lanka Railways (database-backed)
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "View Maintenance Quarter - Sri Lanka Railways";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . baseUrl('admin/maintenance.php'));
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare(
    "SELECT id, quarter_number, quarter_name, location_name, quarter_type,
            maintenance_status, last_maintenance_date, notes, created_at
       FROM maintenance WHERE id = ? LIMIT 1"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    html, body { height: 100%; margin: 0; padding: 0; overflow-y: auto;
        background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .container { max-width: 700px; margin: 20px auto; padding: 0 20px; width: 100%; box-sizing: border-box; }
    .view-card { background: #ffffff; padding: 30px; border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-top: 5px solid #7A1C1C; }
    .page-title { text-align: center; color: #7A1C1C; font-size: 1.6rem;
        margin-bottom: 20px; text-transform: uppercase; font-weight: 700; }
    .view-row { display: flex; border-bottom: 1px solid #f1f5f9;
        padding: 12px 0; font-size: 0.95rem; flex-wrap: wrap; }
    .view-row:last-child { border-bottom: none; }
    .view-label { font-weight: 700; width: 220px; color: #475569; }
    .view-value { color: #1e293b; flex: 1; }
    .back-link { display: inline-flex; align-items: center; gap: 6px;
        color: #7A1C1C; text-decoration: none; font-weight: 600;
        margin-bottom: 20px; font-size: 0.85rem; }
    .alert-error { text-align: center; color: #dc2626; font-weight: 600; padding: 20px 0; }
</style>

<div class="container">
    <a href="<?= baseUrl('admin/maintenance.php') ?>" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Maintenance List
    </a>

    <div class="view-card">
        <h2 class="page-title">Maintenance Detailed View</h2>

        <?php if (!$row): ?>
            <p class="alert-error">Record not found!</p>
        <?php else: ?>
            <div class="view-row"><div class="view-label">Auto ID:</div><div class="view-value"><?= (int)$row['id'] ?></div></div>
            <div class="view-row"><div class="view-label">Quarter No.:</div><div class="view-value"><?= htmlspecialchars($row['quarter_number']) ?></div></div>
            <div class="view-row"><div class="view-label">Quarter Name:</div><div class="view-value"><?= htmlspecialchars($row['quarter_name'] ?? '-') ?></div></div>
            <div class="view-row"><div class="view-label">Quarter Location:</div><div class="view-value"><?= htmlspecialchars($row['location_name']) ?></div></div>
            <div class="view-row"><div class="view-label">Quarter Type:</div><div class="view-value"><?= htmlspecialchars($row['quarter_type']) ?></div></div>
            <div class="view-row"><div class="view-label">Status:</div><div class="view-value"><?= htmlspecialchars($row['maintenance_status']) ?></div></div>
            <div class="view-row"><div class="view-label">Last Maintenance Date:</div><div class="view-value"><?= htmlspecialchars($row['last_maintenance_date'] ?? '-') ?></div></div>
            <div class="view-row"><div class="view-label">Notes:</div><div class="view-value"><?= htmlspecialchars($row['notes'] ?? '-') ?></div></div>
            <div class="view-row"><div class="view-label">Created At:</div><div class="view-value"><?= htmlspecialchars($row['created_at']) ?></div></div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>