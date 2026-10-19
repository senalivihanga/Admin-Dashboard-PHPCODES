<?php
/**
 * View Quarter Details - Sri Lanka Railways Admin (database-backed)
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "View Quarter Details - Sri Lanka Railways Admin";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . baseUrl('admin/quarters_list.php'));
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare(
    "SELECT id, quarter_number, quarter_name, location_name, quarter_address,
            quarter_type, quarter_division, quarter_ownership, quarter_area,
            quarter_assessment_rent, is_active, quarter_status,
            has_electricity, has_water, sort_order
       FROM quarters WHERE id = ? LIMIT 1"
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
        background: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .container { max-width: 900px; margin: 20px auto; padding: 0 20px; width: 100%; box-sizing: border-box; }
    .view-container { background: white; padding: 30px; border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-top: 5px solid #7A1C1C; }
    .page-title { color: #000; margin: 0 0 25px; text-align: center;
        text-transform: uppercase; font-size: 1.8rem; font-weight: 700; }
    .details-grid { display: grid; grid-template-columns: repeat(2, 1fr);
        column-gap: 32px; row-gap: 15px; }
    .view-row { display: flex; flex-direction: column; gap: 8px;
        padding: 12px 0; border-bottom: 1px solid #e5e7eb; }
    .view-row.span2 { grid-column: span 2; }
    .view-label { color: #64748b; font-size: 0.82rem; font-weight: 700;
        text-transform: uppercase; }
    .view-value { color: #17212b; font-size: 1.05rem; font-weight: 600;
        overflow-wrap: anywhere; }
    .back-link { display: inline-flex; align-items: center; gap: 6px;
        color: #7A1C1C; text-decoration: none; font-weight: 600;
        margin-bottom: 20px; font-size: 0.85rem; }
    .alert-error { text-align: center; color: #dc2626;
        font-weight: 600; padding: 20px 0; grid-column: span 2; }
    @media (max-width: 768px) {
        .details-grid { grid-template-columns: 1fr; }
        .view-row.span2 { grid-column: span 1; }
    }
</style>

<div class="container">
    <a href="<?= baseUrl('admin/quarters_list.php') ?>" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Quarters List
    </a>

    <div class="view-container">
        <h2 class="page-title">Quarter Details View</h2>

        <div class="details-grid">
            <?php if (!$row): ?>
                <p class="alert-error">Record not found!</p>
            <?php else: ?>
                <div class="view-row"><div class="view-label">Quarter No.</div><div class="view-value"><?= htmlspecialchars($row['quarter_number']) ?></div></div>
                <div class="view-row"><div class="view-label">Quarter Name</div><div class="view-value"><?= htmlspecialchars($row['quarter_name'] ?? '-') ?></div></div>
                <div class="view-row"><div class="view-label">Quarter Location</div><div class="view-value"><?= htmlspecialchars($row['location_name']) ?></div></div>
                <div class="view-row"><div class="view-label">Quarter Type</div><div class="view-value"><?= htmlspecialchars($row['quarter_type']) ?></div></div>
                <div class="view-row"><div class="view-label">Quarter Division</div><div class="view-value"><?= htmlspecialchars($row['quarter_division'] ?? '-') ?></div></div>
                <div class="view-row"><div class="view-label">Ownership</div><div class="view-value"><?= htmlspecialchars($row['quarter_ownership'] ?? '-') ?></div></div>
                <div class="view-row"><div class="view-label">Quarter Area</div><div class="view-value"><?= htmlspecialchars($row['quarter_area'] ?? '-') ?></div></div>
                <div class="view-row"><div class="view-label">Assessment Rent (LKR)</div><div class="view-value">Rs. <?= number_format((float)($row['quarter_assessment_rent'] ?? 0), 2) ?></div></div>
                <div class="view-row span2"><div class="view-label">Address</div><div class="view-value"><?= htmlspecialchars($row['quarter_address'] ?? '-') ?></div></div>
                <div class="view-row"><div class="view-label">Status</div><div class="view-value"><?= htmlspecialchars($row['quarter_status']) ?></div></div>
                <div class="view-row"><div class="view-label">Active</div><div class="view-value"><?= (int)$row['is_active'] ? 'Yes' : 'No' ?></div></div>
                <div class="view-row"><div class="view-label">Electricity</div><div class="view-value"><?= (int)$row['has_electricity'] ? 'Available' : 'Unavailable' ?></div></div>
                <div class="view-row"><div class="view-label">Water</div><div class="view-value"><?= (int)$row['has_water'] ? 'Available' : 'Unavailable' ?></div></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>