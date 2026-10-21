<?php
/**
 * Add Allocated Quarter - Sri Lanka Railways Admin
 * -------------------------------------------------
 * - Quarter Location / Type / No are pulled from the `quarters` table
 * - Only quarters with quarter_status = 'Available' are listed
 * - Applicant NIC lookup auto-fills: Name, Computer No, Designation, Workplace
 *   from the `users` table (columns: name, computer_number,
 *   applicant_designation, applicant_current_work_station)
 * - On save (inside a transaction):
 *     1. INSERT into allocated_quarters
 *     2. UPDATE quarters SET quarter_status = 'Occupied'
 *     3. UPDATE users SET applicant_designation / applicant_current_work_station
 *        (so admin edits are remembered for next time)
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn         = getDBConnection();
$message      = '';
$messageType  = 'error';

// -----------------------------------------------------------------
// Load quarters that are still Available (for the quarter dropdown)
// -----------------------------------------------------------------
$availableQuarters = [];
$qRes = $conn->query(
    "SELECT id, quarter_number, quarter_name, location_name, quarter_type,
            quarter_address, quarter_assessment_rent
       FROM quarters
      WHERE is_active = 1
        AND quarter_status = 'Available'
      ORDER BY location_name ASC, quarter_number ASC"
);
if ($qRes) {
    $availableQuarters = $qRes->fetch_all(MYSQLI_ASSOC);
}

// Distinct locations & types for filter dropdowns
$locations = [];
$types     = [];
foreach ($availableQuarters as $q) {
    $locations[$q['location_name']] = true;
    $types[$q['quarter_type']]      = true;
}
$locations = array_keys($locations);
$types     = array_keys($types);
sort($locations);
sort($types);

// -----------------------------------------------------------------
// Applicant lookup map: nic -> {name, computer_number,
//                                applicant_designation,
//                                applicant_current_work_station}
// Uses the EXACT column names from the users table
// -----------------------------------------------------------------
$applicants = [];
$aRes = $conn->query(
    "SELECT nic,
            COALESCE(name, '')                              AS name,
            COALESCE(computer_number, '')                   AS computer_number,
            COALESCE(applicant_designation, '')             AS applicant_designation,
            COALESCE(applicant_current_work_station, '')    AS applicant_current_work_station,
            COALESCE(mobile, '')                            AS mobile
       FROM users
      WHERE role_id = 1"
);
if ($aRes) {
    while ($r = $aRes->fetch_assoc()) {
        $applicants[$r['nic']] = $r;
    }
}

// -----------------------------------------------------------------
// Handle form submit
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nic           = trim($_POST['nic']          ?? '');
    $computerNo    = trim($_POST['computerNo']   ?? '');
    $quarterId     = filter_input(INPUT_POST, 'quarterId', FILTER_VALIDATE_INT);
    $residentName  = trim($_POST['residentName'] ?? '');
    $designation   = trim($_POST['designation']  ?? '');
    $workplace     = trim($_POST['workplace']    ?? '');
    $allocatedDate = $_POST['allocatedDate']     ?? date('Y-m-d');
    $other         = trim($_POST['other']        ?? '') ?: 'None';
    $allocationSource = (int)($_SESSION['role_id'] ?? 0) === 7 ? 'direct' : 'normal';

    if (!$quarterId || $nic === '' || $computerNo === '' || $residentName === '') {
        $message = 'Please fill in all required fields and select a quarter.';
    } elseif ($allocationSource === 'direct' && !isset($applicants[$nic])) {
        $message = 'The NIC must belong to a registered applicant before a direct allocation can be created.';
    } else {
        // Re-verify the quarter on the server (never trust the client)
        $qStmt = $conn->prepare(
            "SELECT id, quarter_number, quarter_name, location_name, quarter_type, quarter_status
               FROM quarters
              WHERE id = ? LIMIT 1"
        );
        $qStmt->bind_param('i', $quarterId);
        $qStmt->execute();
        $quarter = $qStmt->get_result()->fetch_assoc();
        $qStmt->close();

        if (!$quarter) {
            $message = 'The selected quarter could not be found.';
        } elseif ($quarter['quarter_status'] !== 'Available') {
            $message = 'This quarter is no longer available (status: ' . $quarter['quarter_status'] . ').';
        } else {
            $conn->begin_transaction();
            try {
                // 1. Insert allocation
                $ins = $conn->prepare(
                    "INSERT INTO allocated_quarters
                        (nic, computer_no, quarter_no, quarter_name, quarter_location,
                         quarter_type, resident_name, designation, workplace,
                         allocated_date, other, source)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?, ?)"
                );
                $ins->bind_param(
                    'ssssssssssss',
                    $nic,
                    $computerNo,
                    $quarter['quarter_number'],
                    $quarter['quarter_name'],
                    $quarter['location_name'],
                    $quarter['quarter_type'],
                    $residentName,
                    $designation,
                    $workplace,
                    $allocatedDate,
                    $other,
                    $allocationSource
                );
                $ins->execute();
                $allocationId = (int)$ins->insert_id;
                $ins->close();

                // 2. Mark quarter as Occupied
                $upd = $conn->prepare("UPDATE quarters SET quarter_status = 'Occupied' WHERE id = ? AND quarter_status = 'Available'");
                $upd->bind_param('i', $quarterId);
                $upd->execute();
                if ($upd->affected_rows !== 1) {
                    $upd->close();
                    throw new RuntimeException('This quarter is no longer available.');
                }
                $upd->close();

                // 3. Persist any admin edits back into the users table
                //    so next time the NIC lookup auto-fills with these values
                $sync = $conn->prepare(
                    "UPDATE users
                        SET applicant_designation = ?,
                            applicant_current_work_station = ?
                      WHERE nic = ? AND role_id = 1"
                );
                $sync->bind_param('sss', $designation, $workplace, $nic);
                $sync->execute();
                $sync->close();

                $conn->commit();
                $conn->close();

                if ($allocationSource === 'direct') {
                    $_SESSION['direct_alloc'] = [
                        'allocation_id' => $allocationId,
                        'nic'           => $nic,
                        'computer_no'   => $computerNo,
                        'phone_no'      => $applicants[$nic]['mobile'] ?? '',
                        'resident'      => $residentName,
                        'designation'   => $designation,
                        'workplace'     => $workplace,
                        'quarter_no'    => $quarter['quarter_number'],
                        'quarter_name'  => $quarter['quarter_name'],
                        'quarter_loc'   => $quarter['location_name'],
                        'quarter_type'  => $quarter['quarter_type'],
                    ];
                    header('Location: ' . baseUrl('application/form/en?direct=1'));
                } else {
                    header('Location: allocated_quarters.php');
                }
                exit();
            } catch (Throwable $e) {
                $conn->rollback();
                $message = 'Save failed: ' . $e->getMessage();
            }
        }
    }
}

$conn->close();

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
        font-size: 1.05rem;
    }
    .back-link:hover { text-decoration: underline; }
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
    .form-group { margin-bottom: 0; }
    .form-group.full-width { grid-column: span 2; }
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
    .form-control.autofilled {
        background-color: #ecfdf5;
        border-color: #86efac;
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
    .btn-submit:hover { background-color: #059669; }
    .alert {
        padding: 12px;
        margin-bottom: 20px;
        border-radius: 8px;
        font-weight: 600;
        text-align: center;
    }
    .alert-error { background-color: #fee2e2; color: #991b1b; }
    .empty-note {
        grid-column: span 2;
        padding: 14px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        border-radius: 8px;
        text-align: center;
        font-weight: 600;
    }
    .nic-hint {
        display: block;
        margin-top: 6px;
        font-size: 0.78rem;
        color: #64748b;
    }
    .nic-hint.ok   { color: #15803d; }
    .nic-hint.warn { color: #b45309; }
    @media (max-width: 768px) {
        .container-custom { margin: 15px 10px; padding: 0 10px; }
        .form-grid { grid-template-columns: 1fr; gap: 16px; }
        .form-group.full-width, .form-actions, .btn-submit, .empty-note { grid-column: span 1; }
        .form-card { padding: 20px 15px; }
        .page-title { font-size: 1.3rem; }
    }
</style>

<div class="container-custom">
    <a href="allocated_quarters.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to List
    </a>
    <?php if ((int)($_SESSION['role_id'] ?? 0) === 7): ?>
        <a href="direct_allocations.php" class="back-link" style="margin-left:12px;">
            <i class="fa-solid fa-list-check"></i> View Direct Allocations
        </a>
    <?php endif; ?>

    <div class="form-card">
        <h2 class="page-title">Add New Allocation</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (empty($availableQuarters)): ?>
            <div class="empty-note">
                <i class="fa-solid fa-circle-info"></i>
                No quarters are currently marked <strong>Available</strong>.
                Add a quarter or change an existing quarter's status first.
            </div>
        <?php else: ?>

        <form action="" method="POST" class="form-grid">

            <!-- ================= APPLICANT ================= -->
            <div class="form-group full-width">
                <label for="nic">Applicant NIC <span style="color:#dc2626;">*</span></label>
                <input type="text" id="nic" name="nic" class="form-control"
                       required placeholder="Enter NIC number (auto-fills below)"
                       autocomplete="off">
                <small id="nicHint" class="nic-hint"></small>
            </div>

            <div class="form-group">
                <label for="computerNo">Computer No.</label>
                <input type="text" id="computerNo" name="computerNo" class="form-control"
                       inputmode="numeric" pattern="[0-9]+" title="Enter digits only"
                       required placeholder="Enter computer number">
            </div>

            <div class="form-group">
                <label for="residentName">Resident Name</label>
                <input type="text" id="residentName" name="residentName" class="form-control"
                       required placeholder="Auto-filled from NIC (editable)">
            </div>

            <div class="form-group">
                <label for="designation">Designation</label>
                <input type="text" id="designation" name="designation" class="form-control"
                       placeholder="Auto-filled from NIC (editable)">
            </div>

            <div class="form-group">
                <label for="workplace">Workplace</label>
                <input type="text" id="workplace" name="workplace" class="form-control"
                       placeholder="Auto-filled from NIC (editable)">
            </div>

            <!-- ================= QUARTER ================= -->
            <div class="form-group">
                <label for="locationFilter">Quarter Location</label>
                <select id="locationFilter" name="locationFilter" class="form-control">
                    <option value="">-- All Locations --</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?php echo htmlspecialchars($loc); ?>">
                            <?php echo htmlspecialchars($loc); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="typeFilter">Quarter Type</label>
                <select id="typeFilter" name="typeFilter" class="form-control">
                    <option value="">-- All Types --</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?php echo htmlspecialchars($t); ?>">
                            <?php echo htmlspecialchars($t); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group full-width">
                <label for="quarterId">Available Quarter <span style="color:#dc2626;">*</span></label>
                <select id="quarterId" name="quarterId" class="form-control" required>
                    <option value="">-- Select Quarter --</option>
                    <?php foreach ($availableQuarters as $q): ?>
                        <option value="<?php echo (int)$q['id']; ?>"
                                data-location="<?php echo htmlspecialchars($q['location_name']); ?>"
                                data-type="<?php echo htmlspecialchars($q['quarter_type']); ?>">
                            <?php echo htmlspecialchars($q['quarter_number']); ?>
                            – <?php echo htmlspecialchars($q['quarter_name'] ?? ''); ?>
                            (<?php echo htmlspecialchars($q['location_name']); ?> / <?php echo htmlspecialchars($q['quarter_type']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="display:block; margin-top:6px; color:#64748b; font-size:0.8rem;">
                    Only quarters with status <strong>Available</strong> are listed.
                </small>
            </div>

            <div class="form-group">
                <label for="allocatedDate">Allocated Date</label>
                <input type="date" id="allocatedDate" name="allocatedDate" class="form-control"
                       required value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group full-width">
                <label for="other">Other</label>
                <textarea id="other" name="other" class="form-control" rows="3"
                          placeholder="Additional notes (optional)"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Save Allocation</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
/* =====================================================================
 * AUTO-FILL from `users` table
 * Fills name, computerNo, designation, workplace as soon as a valid NIC
 * is typed. Uses the exact DB column names:
 *     users.name
 *     users.computer_number
 *     users.applicant_designation
 *     users.applicant_current_work_station
 * ===================================================================== */
const applicants = <?php echo json_encode($applicants, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

const nicInput   = document.getElementById('nic');
const nicHint    = document.getElementById('nicHint');
const compInput  = document.getElementById('computerNo');
const nameInput  = document.getElementById('residentName');
const desigInput = document.getElementById('designation');
const workInput  = document.getElementById('workplace');

// debug helper – check DevTools console
console.log('[autofill] applicants loaded:', Object.keys(applicants).length, applicants);

function flashAutofill(el) {
    if (!el) return;
    el.classList.add('autofilled');
    setTimeout(() => el.classList.remove('autofilled'), 1200);
}

function autofillFromNic() {
    const nic = (nicInput.value || '').trim();

    if (!nic) {
        if (nicHint) { nicHint.textContent = ''; nicHint.className = 'nic-hint'; }
        return;
    }

    const a = applicants[nic];

    if (!a) {
        if (nicHint) {
            nicHint.textContent = '⚠ NIC not found in users table — fill in manually.';
            nicHint.className = 'nic-hint warn';
        }
        return;
    }

    // Always overwrite — the applicant lookup is the source of truth
    nameInput.value  = a.name || nameInput.value;
    compInput.value  = a.computer_number || compInput.value;
    desigInput.value = a.applicant_designation || desigInput.value;
    workInput.value  = a.applicant_current_work_station || workInput.value;

    flashAutofill(nameInput);
    flashAutofill(compInput);
    flashAutofill(desigInput);
    flashAutofill(workInput);

    const missing = [];
    if (!a.applicant_designation)        missing.push('designation');
    if (!a.applicant_current_work_station) missing.push('workplace');

    if (nicHint) {
        if (missing.length === 0) {
            nicHint.textContent = '✓ Applicant details loaded from database.';
            nicHint.className = 'nic-hint ok';
        } else {
            nicHint.textContent = '⚠ Applicant found, but ' + missing.join(' & ') +
                                  ' are empty in users table. Please fill manually.';
            nicHint.className = 'nic-hint warn';
        }
    }
}

if (nicInput) {
    // Fires immediately after the last keystroke (debounced 300ms)
    let t = null;
    nicInput.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(autofillFromNic, 300);
    });

    // Also on blur and change, in case user pastes and tabs out
    nicInput.addEventListener('blur',   autofillFromNic);
    nicInput.addEventListener('change', autofillFromNic);
}

/* ---- Digits only on computer number ---- */
if (compInput) {
    compInput.addEventListener('input', e => {
        e.target.value = e.target.value.replace(/\D/g, '');
    });
}

/* =====================================================================
 * FILTER quarter dropdown by location and/or type
 * ===================================================================== */
const locationSel = document.getElementById('locationFilter');
const typeSel     = document.getElementById('typeFilter');
const quarterSel  = document.getElementById('quarterId');

function filterQuarters() {
    if (!quarterSel) return;
    const loc  = locationSel.value;
    const type = typeSel.value;

    Array.from(quarterSel.options).forEach((opt, idx) => {
        if (idx === 0) return;
        const okLoc  = !loc  || opt.dataset.location === loc;
        const okType = !type || opt.dataset.type     === type;
        opt.hidden = !(okLoc && okType);
    });

    if (quarterSel.selectedIndex > 0) {
        const cur = quarterSel.options[quarterSel.selectedIndex];
        if (cur.hidden) quarterSel.selectedIndex = 0;
    }
}
if (locationSel) locationSel.addEventListener('change', filterQuarters);
if (typeSel)     typeSel.addEventListener('change',     filterQuarters);
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>