<?php
/**
 * Edit Maintenance Quarter - Sri Lanka Railways Admin
 * ---------------------------------------------------
 * Mirrors edit_quarter.php field-for-field, PLUS maintenance_status and
 * last_maintenance_date.
 *
 * On save (single transaction):
 *   1) Update the quarter in `quarters` (status forced to 'Under Maintenance')
 *   2) Update the linked maintenance row (status + last_maintenance_date)
 *
 * Link between tables: (quarter_number, location_name).
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Edit Maintenance Quarter - Sri Lanka Railways Admin";

$userData = [
    'name'     => 'Subject Clerk',
    'role'     => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$error = '';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . baseUrl('admin/maintenance.php'));
    exit;
}

$conn = getDBConnection();

$mStmt = $conn->prepare(
    "SELECT id, quarter_number, quarter_name, location_name, quarter_type,
            maintenance_status, last_maintenance_date, notes
       FROM maintenance WHERE id = ? LIMIT 1"
);
$mStmt->bind_param("i", $id);
$mStmt->execute();
$maint = $mStmt->get_result()->fetch_assoc();
$mStmt->close();

if (!$maint) {
    $conn->close();
    header('Location: ' . baseUrl('admin/maintenance.php'));
    exit;
}

$qStmt = $conn->prepare(
    "SELECT id, quarter_number, quarter_name, location_name, quarter_address,
            quarter_type, quarter_division, quarter_ownership, quarter_area,
            quarter_assessment_rent, quarter_status, has_electricity, has_water
       FROM quarters
      WHERE quarter_number = ? AND location_name = ?
      LIMIT 1"
);
$qStmt->bind_param("ss", $maint['quarter_number'], $maint['location_name']);
$qStmt->execute();
$quarter = $qStmt->get_result()->fetch_assoc();
$qStmt->close();

$old = [
    'quarter_number'          => $maint['quarter_number'],
    'quarter_name'            => $quarter['quarter_name']  ?? $maint['quarter_name'] ?? '',
    'location_name'           => $maint['location_name'],
    'quarter_address'         => $quarter['quarter_address']        ?? '',
    'quarter_type'            => $maint['quarter_type'],
    'quarter_division'        => $quarter['quarter_division']       ?? '',
    'quarter_ownership'       => $quarter['quarter_ownership']      ?? '',
    'quarter_area'            => $quarter['quarter_area']           ?? '',
    'quarter_assessment_rent' => $quarter['quarter_assessment_rent'] ?? 0,
    'maintenance_status'      => $maint['maintenance_status'],
    'last_maintenance_date'   => $maint['last_maintenance_date']    ?? '',
    'has_electricity'         => isset($quarter['has_electricity']) ? (int)$quarter['has_electricity'] : 1,
    'has_water'               => isset($quarter['has_water'])       ? (int)$quarter['has_water']       : 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $v) {
        if (in_array($k, ['has_electricity', 'has_water'], true)) {
            $old[$k] = isset($_POST[$k]) ? 1 : 0;
        } elseif (isset($_POST[$k])) {
            $old[$k] = $_POST[$k];
        }
    }

    $old['quarter_number']          = trim((string)$old['quarter_number']);
    $old['quarter_name']            = trim((string)$old['quarter_name']);
    $old['location_name']           = trim((string)$old['location_name']);
    $old['quarter_address']         = trim((string)$old['quarter_address']);
    $old['quarter_type']            = trim((string)$old['quarter_type']);
    $old['quarter_division']        = trim((string)$old['quarter_division']);
    $old['quarter_ownership']       = trim((string)$old['quarter_ownership']);
    $old['quarter_area']            = trim((string)$old['quarter_area']);
    $old['quarter_assessment_rent'] = (float)$old['quarter_assessment_rent'];
    $old['maintenance_status']      = trim((string)$old['maintenance_status']);
    $old['last_maintenance_date']   = trim((string)$old['last_maintenance_date']);

    $validStatuses = ['Under Maintenance', 'Repair in Progress', 'Painting Pending', 'Electrical Work'];

    if ($old['quarter_number'] === '' || $old['location_name'] === '' || $old['quarter_type'] === '') {
        $error = 'Quarter No., Location, and Quarter Type are required.';
    } elseif (!in_array($old['maintenance_status'], $validStatuses, true)) {
        $error = 'Please select a valid maintenance status.';
    } else {
        $conn->begin_transaction();
        try {
            if ($quarter) {
                $updQ = $conn->prepare(
                    "UPDATE quarters
                        SET quarter_number = ?, quarter_name = ?, location_name = ?,
                            quarter_address = ?, quarter_type = ?, quarter_division = ?,
                            quarter_ownership = ?, quarter_area = ?, quarter_assessment_rent = ?,
                            quarter_status = 'Under Maintenance',
                            has_electricity = ?, has_water = ?
                      WHERE id = ?"
                );
                $updQ->bind_param(
                    "sssssssssdiii",
                    $old['quarter_number'], $old['quarter_name'], $old['location_name'],
                    $old['quarter_address'], $old['quarter_type'], $old['quarter_division'],
                    $old['quarter_ownership'], $old['quarter_area'], $old['quarter_assessment_rent'],
                    $old['has_electricity'], $old['has_water'],
                    $quarter['id']
                );
                $updQ->execute();
                $updQ->close();
            } else {
                $insQ = $conn->prepare(
                    "INSERT INTO quarters
                        (quarter_number, quarter_name, location_name, quarter_address,
                         quarter_type, quarter_division, quarter_ownership, quarter_area,
                         quarter_assessment_rent, quarter_status, has_electricity, has_water,
                         is_active, sort_order)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Under Maintenance', ?, ?, 1, 0)"
                );
                $insQ->bind_param(
                    "ssssssssdii",
                    $old['quarter_number'], $old['quarter_name'], $old['location_name'],
                    $old['quarter_address'], $old['quarter_type'], $old['quarter_division'],
                    $old['quarter_ownership'], $old['quarter_area'], $old['quarter_assessment_rent'],
                    $old['has_electricity'], $old['has_water']
                );
                $insQ->execute();
                $insQ->close();
            }

            $lastMaint = $old['last_maintenance_date'] !== '' ? $old['last_maintenance_date'] : null;

            $updM = $conn->prepare(
                "UPDATE maintenance
                    SET quarter_number = ?, quarter_name = ?, location_name = ?,
                        quarter_type = ?, maintenance_status = ?, last_maintenance_date = ?
                  WHERE id = ?"
            );
            $updM->bind_param(
                "ssssssi",
                $old['quarter_number'], $old['quarter_name'], $old['location_name'],
                $old['quarter_type'], $old['maintenance_status'], $lastMaint, $id
            );
            $updM->execute();
            $updM->close();

            $conn->commit();
            $conn->close();
            header('Location: ' . baseUrl('admin/maintenance.php?msg=updated'));
            exit;
        } catch (Throwable $e) {
            $conn->rollback();
            $error = 'Save failed: ' . $e->getMessage();
        }
    }
}

$conn->close();

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    html, body {
        height: 100%; margin: 0; padding: 0; overflow-y: auto;
        background-color: #f8fafc;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .container { max-width: 900px; margin: 20px auto; padding: 0 20px; width: 100%; box-sizing: border-box; }
    .form-container {
        width: 100%; background: white; padding: 30px; border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        border-top: 5px solid #7A1C1C; box-sizing: border-box;
    }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group { margin-bottom: 0; }
    .form-group.full-width { grid-column: span 2; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #334155; font-size: 0.85rem; }
    .form-group input, .form-group select {
        width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;
        font-size: 0.95rem; box-sizing: border-box; outline: none; background-color: #ffffff;
    }
    .form-group input:focus, .form-group select:focus {
        border-color: #7A1C1C; box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1);
    }
    .checkbox-group { display: flex; gap: 20px; margin-top: 10px; }
    .checkbox-group label {
        font-weight: normal; display: inline-flex; align-items: center;
        gap: 5px; cursor: pointer; font-size: 0.9rem;
    }
    .btn-submit {
        background-color: #7A1C1C; color: white; border: none; padding: 12px 20px;
        width: 100%; border-radius: 6px; font-weight: 600; cursor: pointer;
        font-size: 1rem; grid-column: span 2; margin-top: 10px; transition: background 0.2s;
    }
    .btn-submit:hover { background-color: #5c1414; }
    .back-link {
        display: inline-flex; align-items: center; gap: 6px; color: #7A1C1C;
        text-decoration: none; font-weight: 600; margin-bottom: 20px;
        font-size: 0.85rem; transition: color 0.2s;
    }
    .back-link:hover { text-decoration: underline; }
    .alert-error {
        background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca;
        padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;
        text-align: center; font-weight: 600; grid-column: span 2;
    }
    .user-profile-wrapper { position: relative; display: inline-block; margin-bottom: 20px; }
    .user-profile-badge {
        display: flex; align-items: center; gap: 10px; background: #7A1C1C;
        color: #ffffff; padding: 6px 14px; border-radius: 30px; cursor: pointer;
        user-select: none; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .profile-dropdown {
        display: none; position: absolute; left: 0; top: 105%; background: #ffffff;
        min-width: 220px; box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        border-radius: 8px; overflow: hidden; z-index: 100; border: 1px solid #e2e8f0;
    }
    .profile-dropdown.show { display: block; }
    .profile-dropdown a {
        color: #1e293b; padding: 10px 15px; text-decoration: none;
        display: flex; align-items: center; gap: 8px; font-size: 0.85rem; transition: background 0.2s;
    }
    .profile-dropdown a:hover { background: #f1f5f9; color: #7A1C1C; }
    .profile-dropdown a.logout-btn { color: #ef4444; }
    .profile-dropdown a.logout-btn:hover { background: #fee2e2; }
    .custom-modal {
        display: none; position: fixed; z-index: 1000; left: 0; top: 0;
        width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);
        justify-content: center; align-items: center; padding: 15px;
    }
    .custom-modal-content {
        background-color: #fff; padding: 30px; border-radius: 12px;
        width: 100%; max-width: 380px; text-align: center;
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    .custom-modal-content i { font-size: 2.8rem; color: #7A1C1C; margin-bottom: 15px; }
    .custom-modal-content h3 { color: #1e293b; margin-bottom: 10px; font-size: 1.3rem; }
    .custom-modal-content p { color: #64748b; font-size: 0.95rem; margin-bottom: 20px; }
    .modal-action-btns { display: flex; justify-content: center; gap: 12px; }
    .btn-modal-confirm, .btn-modal-cancel {
        padding: 8px 15px; border: none; border-radius: 5px;
        font-weight: 600; cursor: pointer; color: white;
    }
    .btn-modal-confirm { background-color: #ef4444; }
    .btn-modal-cancel  { background-color: #64748b; }
    @media (max-width: 768px) {
        .form-container { margin: 15px auto; padding: 20px 15px; }
        .form-grid { grid-template-columns: 1fr; gap: 15px; }
        .form-group.full-width, .btn-submit, .alert-error { grid-column: span 1; }
    }
</style>

<div class="container">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
        <a href="maintenance.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Maintenance List</a>
    <?php else: ?>
        <div class="user-profile-wrapper">
            <div class="user-profile-badge" onclick="toggleDropdown()">
                <div style="width: 35px; height: 35px; background: #ffffff; color: #7A1C1C; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem;">
                    <?php echo htmlspecialchars($userData['initials']); ?>
                </div>
                <div>
                    <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($userData['name']); ?></div>
                </div>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; color: #f3e8ff; margin-left: 5px;"></i>
            </div>
            <div id="profileDropdown" class="profile-dropdown">
                <a href="edit_profile.php"><i class="fa-solid fa-user-pen"></i> Edit Profile</a>
                <a href="maintenance.php"><i class="fa-solid fa-arrow-left"></i> Back to Maintenance List</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <h2 style="color: #0a0a0a; margin-bottom: 25px; text-align: center; font-size: 1.6rem; font-weight: 700;">
            Edit Maintenance Quarter
        </h2>

        <form method="POST" class="form-grid">
            <?php if ($error): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="quarter_number">Quarter No.</label>
                <input type="text" id="quarter_number" name="quarter_number" required
                       value="<?php echo htmlspecialchars($old['quarter_number']); ?>">
            </div>
            <div class="form-group">
                <label for="location_name">Quarter Location</label>
                <input type="text" id="location_name" name="location_name" required
                       value="<?php echo htmlspecialchars($old['location_name']); ?>">
            </div>
            <div class="form-group">
                <label for="quarter_name">Quarter Name</label>
                <input type="text" id="quarter_name" name="quarter_name"
                       value="<?php echo htmlspecialchars($old['quarter_name']); ?>">
            </div>
            <div class="form-group">
                <label for="quarter_division">Quarter Division</label>
                <input type="text" id="quarter_division" name="quarter_division"
                       value="<?php echo htmlspecialchars($old['quarter_division']); ?>">
            </div>
            <div class="form-group">
                <label for="quarter_ownership">Ownership</label>
                <input type="text" id="quarter_ownership" name="quarter_ownership"
                       value="<?php echo htmlspecialchars($old['quarter_ownership']); ?>">
            </div>
            <div class="form-group">
                <label for="quarter_area">Quarter Area</label>
                <input type="text" id="quarter_area" name="quarter_area"
                       value="<?php echo htmlspecialchars($old['quarter_area']); ?>">
            </div>
            <div class="form-group full-width">
                <label for="quarter_address">Address</label>
                <input type="text" id="quarter_address" name="quarter_address"
                       value="<?php echo htmlspecialchars($old['quarter_address']); ?>">
            </div>
            <div class="form-group">
                <label for="quarter_type">Quarter Type</label>
                <select id="quarter_type" name="quarter_type" required>
                    <option value="">-- Select Quarter Type --</option>
                    <option value="Bungalows" <?php echo $old['quarter_type'] === 'Bungalows' ? 'selected' : ''; ?>>Bungalows</option>
                    <option value="Type I"    <?php echo $old['quarter_type'] === 'Type I'    ? 'selected' : ''; ?>>Type I</option>
                    <option value="Type II"   <?php echo $old['quarter_type'] === 'Type II'   ? 'selected' : ''; ?>>Type II</option>
                    <option value="Type III"  <?php echo $old['quarter_type'] === 'Type III'  ? 'selected' : ''; ?>>Type III</option>
                </select>
            </div>
            <div class="form-group">
                <label for="quarter_assessment_rent">Assessment Rent (LKR)</label>
                <input type="number" id="quarter_assessment_rent" name="quarter_assessment_rent"
                       step="0.01" min="0"
                       value="<?php echo htmlspecialchars((string)$old['quarter_assessment_rent']); ?>">
            </div>
            <div class="form-group">
                <label for="maintenance_status">Status</label>
                <select id="maintenance_status" name="maintenance_status" required>
                    <option value="Under Maintenance"  <?php echo $old['maintenance_status'] === 'Under Maintenance'  ? 'selected' : ''; ?>>Under Maintenance</option>
                    <option value="Repair in Progress" <?php echo $old['maintenance_status'] === 'Repair in Progress' ? 'selected' : ''; ?>>Repair in Progress</option>
                    <option value="Painting Pending"   <?php echo $old['maintenance_status'] === 'Painting Pending'   ? 'selected' : ''; ?>>Painting Pending</option>
                    <option value="Electrical Work"    <?php echo $old['maintenance_status'] === 'Electrical Work'    ? 'selected' : ''; ?>>Electrical Work</option>
                </select>
            </div>
            <div class="form-group">
                <label for="last_maintenance_date">Last Maintenance Date</label>
                <input type="date" id="last_maintenance_date" name="last_maintenance_date"
                       value="<?php echo htmlspecialchars($old['last_maintenance_date']); ?>">
            </div>
            <div class="form-group">
                <label>Facilities</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" id="has_electricity" name="has_electricity" <?php echo $old['has_electricity'] ? 'checked' : ''; ?>> Electricity</label>
                    <label><input type="checkbox" id="has_water" name="has_water" <?php echo $old['has_water'] ? 'checked' : ''; ?>> Water</label>
                </div>
            </div>
            <button type="submit" class="btn-submit">Update Quarter</button>
        </form>
    </div>
</div>

<div id="logoutModal" class="custom-modal">
    <div class="custom-modal-content">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to log out from the Subject Clerk portal?</p>
        <div class="modal-action-btns">
            <button type="button" class="btn-modal-cancel" onclick="closeLogoutModal()">Cancel</button>
            <button type="button" class="btn-modal-confirm" onclick="executeLogout()">Logout</button>
        </div>
    </div>
</div>

<script>
    function toggleDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) dropdown.classList.toggle('show');
    }
    window.addEventListener('click', function (e) {
        if (!e.target.closest('.user-profile-wrapper')) {
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown && dropdown.classList.contains('show')) dropdown.classList.remove('show');
        }
    });
    function openLogoutModal(event) {
        event.preventDefault();
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) dropdown.classList.remove('show');
        document.getElementById('logoutModal').style.display = 'flex';
    }
    function closeLogoutModal() { document.getElementById('logoutModal').style.display = 'none'; }
    function executeLogout() { window.location.href = "<?php echo baseUrl('logout'); ?>"; }
</script>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>