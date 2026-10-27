<?php
/**
 * View Maintenance Quarter - Sri Lanka Railways Admin
 * ---------------------------------------------------
 * Mirrors view_quarters_list.php layout, showing every maintenance field
 * plus the linked quarter fields.
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "View Maintenance Quarter - Sri Lanka Railways Admin";

$userData = [
    'name'     => 'Subject Clerk',
    'role'     => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . baseUrl('admin/maintenance.php'));
    exit;
}

$conn = getDBConnection();

// Load the maintenance row
$mStmt = $conn->prepare(
    "SELECT id, quarter_number, quarter_name, location_name, quarter_type,
            maintenance_status, last_maintenance_date, notes, created_at
       FROM maintenance WHERE id = ? LIMIT 1"
);
$mStmt->bind_param("i", $id);
$mStmt->execute();
$maint = $mStmt->get_result()->fetch_assoc();
$mStmt->close();

// Load the linked quarter row
$quarter = null;
if ($maint) {
    $qStmt = $conn->prepare(
        "SELECT quarter_number, quarter_name, location_name, quarter_address,
                quarter_type, quarter_division, quarter_ownership, quarter_area,
                quarter_assessment_rent, quarter_status,
                has_electricity, has_water, is_active
           FROM quarters
          WHERE quarter_number = ? AND location_name = ?
          LIMIT 1"
    );
    $qStmt->bind_param("ss", $maint['quarter_number'], $maint['location_name']);
    $qStmt->execute();
    $quarter = $qStmt->get_result()->fetch_assoc();
    $qStmt->close();
}

$conn->close();

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    html, body {
        height: 100%; margin: 0; padding: 0; overflow-y: auto;
        background: #f8fafc;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .container { max-width: 900px; margin: 20px auto; padding: 0 20px; width: 100%; box-sizing: border-box; }
    .view-container {
        background: white; padding: 30px; border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        border-top: 5px solid #7A1C1C;
    }
    .page-title {
        color: #000; margin: 0 0 25px; text-align: center;
        text-transform: uppercase; font-size: 1.8rem; font-weight: 700;
    }
    .details-grid {
        display: grid; grid-template-columns: repeat(2, 1fr);
        column-gap: 32px; row-gap: 15px;
    }
    .view-row {
        display: flex; flex-direction: column; gap: 8px;
        padding: 12px 0; border-bottom: 1px solid #e5e7eb;
    }
    .view-row.span2 { grid-column: span 2; }
    .view-label {
        color: #64748b; font-size: 0.82rem; font-weight: 700;
        text-transform: uppercase;
    }
    .view-value {
        color: #17212b; font-size: 1.05rem; font-weight: 600;
        overflow-wrap: anywhere;
    }
    .back-link {
        display: inline-flex; align-items: center; gap: 6px;
        color: #7A1C1C; text-decoration: none; font-weight: 600;
        margin-bottom: 20px; font-size: 0.85rem;
    }
    .back-link:hover { text-decoration: underline; }
    .alert-error {
        text-align: center; color: #dc2626; font-weight: 600;
        padding: 20px 0; grid-column: span 2;
    }
    .section-heading {
        grid-column: span 2; color: #7A1C1C; font-size: 1.05rem;
        font-weight: 700; margin-top: 8px;
        border-bottom: 2px solid #f1f5f9; padding-bottom: 6px;
    }
    @media (max-width: 768px) {
        .details-grid { grid-template-columns: 1fr; }
        .view-row.span2 { grid-column: span 1; }
        .section-heading { grid-column: span 1; }
    }
</style>

<div class="container">
    <a href="<?= baseUrl('admin/maintenance.php') ?>" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Maintenance List
    </a>

    <div class="view-container">
        <h2 class="page-title">Maintenance Quarter Details</h2>

        <div class="details-grid">
            <?php if (!$maint): ?>
                <p class="alert-error">Record not found!</p>
            <?php else: ?>

                <div class="section-heading">Maintenance Info</div>

                <div class="view-row"><div class="view-label">Maintenance ID</div><div class="view-value"><?= (int)$maint['id'] ?></div></div>
                <div class="view-row"><div class="view-label">Status</div><div class="view-value"><?= htmlspecialchars($maint['maintenance_status']) ?></div></div>
                <div class="view-row"><div class="view-label">Last Maintenance Date</div><div class="view-value"><?= htmlspecialchars($maint['last_maintenance_date'] ?? '-') ?></div></div>
                <div class="view-row"><div class="view-label">Created At</div><div class="view-value"><?= htmlspecialchars($maint['created_at']) ?></div></div>
                <div class="view-row span2"><div class="view-label">Notes</div><div class="view-value"><?= htmlspecialchars($maint['notes'] ?? '-') ?></div></div>

                <div class="section-heading">Quarter Info</div>

                <div class="view-row"><div class="view-label">Quarter No.</div><div class="view-value"><?= htmlspecialchars($maint['quarter_number']) ?></div></div>
                <div class="view-row"><div class="view-label">Quarter Name</div><div class="view-value"><?= htmlspecialchars($maint['quarter_name'] ?? '-') ?></div></div>
                <div class="view-row"><div class="view-label">Quarter Location</div><div class="view-value"><?= htmlspecialchars($maint['location_name']) ?></div></div>
                <div class="view-row"><div class="view-label">Quarter Type</div><div class="view-value"><?= htmlspecialchars($maint['quarter_type']) ?></div></div>

                <?php if ($quarter): ?>
                    <div class="view-row span2"><div class="view-label">Address</div><div class="view-value"><?= htmlspecialchars($quarter['quarter_address'] ?? '-') ?></div></div>
                    <div class="view-row"><div class="view-label">Quarter Division</div><div class="view-value"><?= htmlspecialchars($quarter['quarter_division'] ?? '-') ?></div></div>
                    <div class="view-row"><div class="view-label">Ownership</div><div class="view-value"><?= htmlspecialchars($quarter['quarter_ownership'] ?? '-') ?></div></div>
                    <div class="view-row"><div class="view-label">Quarter Area</div><div class="view-value"><?= htmlspecialchars($quarter['quarter_area'] ?? '-') ?></div></div>
                    <div class="view-row"><div class="view-label">Assessment Rent (LKR)</div><div class="view-value">Rs. <?= number_format((float)($quarter['quarter_assessment_rent'] ?? 0), 2) ?></div></div>
                    <div class="view-row"><div class="view-label">Quarter Status</div><div class="view-value"><?= htmlspecialchars($quarter['quarter_status']) ?></div></div>
                    <div class="view-row"><div class="view-label">Active</div><div class="view-value"><?= (int)$quarter['is_active'] ? 'Yes' : 'No' ?></div></div>
                    <div class="view-row"><div class="view-label">Electricity</div><div class="view-value"><?= (int)$quarter['has_electricity'] ? 'Available' : 'Unavailable' ?></div></div>
                    <div class="view-row"><div class="view-label">Water</div><div class="view-value"><?= (int)$quarter['has_water'] ? 'Available' : 'Unavailable' ?></div></div>
                <?php else: ?>
                    <p class="alert-error" style="grid-column: span 2;">No linked quarter record exists in the `quarters` table for this maintenance entry.</p>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>