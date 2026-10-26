<?php
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ((int)($_SESSION['role_id'] ?? 0) !== 7) {
    http_response_code(403);
    exit('Access denied.');
}

$pageTitle = 'Direct Quarter Allocations - Sri Lanka Railways';
$search = trim($_GET['search'] ?? '');
$conn = getDBConnection();

// -----------------------------------------------------------
// >>> NEW: Handle "Remove Direct Allocation" POST
// -----------------------------------------------------------
$flash = null;           // ['type' => 'success'|'error', 'text' => '...']
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'remove_allocation') {
    $allocId = filter_input(INPUT_POST, 'allocation_id', FILTER_VALIDATE_INT);
    $confirm = trim((string)($_POST['confirm_text'] ?? ''));

    if (!$allocId) {
        $flash = ['type' => 'error', 'text' => 'Invalid allocation id.'];
    } elseif (strtoupper($confirm) !== 'REMOVE') {
        $flash = ['type' => 'error', 'text' => 'Please type REMOVE to confirm.'];
    } else {
        $conn->begin_transaction();
        try {
            // 1) Load the direct allocation (must be source='direct')
            $selStmt = $conn->prepare(
                "SELECT id, nic, quarter_no, quarter_location, quarter_type, source
                   FROM allocated_quarters
                  WHERE id = ? AND source = 'direct'
                  LIMIT 1
                  FOR UPDATE"
            );
            $selStmt->bind_param('i', $allocId);
            $selStmt->execute();
            $alloc = $selStmt->get_result()->fetch_assoc();
            $selStmt->close();

            if (!$alloc) {
                throw new RuntimeException(
                    'Direct allocation not found or not a direct allocation.'
                );
            }

            // 2) Delete any applications this direct allocation created.
            //    We match on nic + source='direct' + quarter_no/location/type
            //    (the same keys add_allocated_quarter.php wrote to).
            $delApp = $conn->prepare(
                "DELETE FROM applications
                  WHERE nic = ?
                    AND source = 'direct'
                    AND applied_quarter_number = ?
                    AND applied_quarter_location = ?
                    AND quarter_type = ?"
            );
            $delApp->bind_param(
                'ssss',
                $alloc['nic'],
                $alloc['quarter_no'],
                $alloc['quarter_location'],
                $alloc['quarter_type']
            );
            $delApp->execute();
            $delApp->close();

            // 3) Delete the allocated_quarters row itself
            $delAlloc = $conn->prepare(
                "DELETE FROM allocated_quarters
                  WHERE id = ? AND source = 'direct'"
            );
            $delAlloc->bind_param('i', $allocId);
            $delAlloc->execute();
            if ($delAlloc->affected_rows !== 1) {
                $delAlloc->close();
                throw new RuntimeException('Failed to delete the allocation row.');
            }
            $delAlloc->close();

            // 4) Free the quarter — flip quarter_status back to 'Available'.
            //    There may be more than one matching quarter row if numbers
            //    were reused across locations; we set them all back to
            //    Available, which is the safest interpretation.
            $freeQ = $conn->prepare(
                "UPDATE quarters
                    SET quarter_status = 'Available'
                  WHERE quarter_number = ?
                    AND location_name  = ?"
            );
            $freeQ->bind_param('ss', $alloc['quarter_no'], $alloc['quarter_location']);
            $freeQ->execute();
            $freeQ->close();

            $conn->commit();
            $flash = [
                'type' => 'success',
                'text' => 'Direct allocation removed. Quarter '
                        . $alloc['quarter_no'] . ' (' . $alloc['quarter_location']
                        . ') is now available again.'
            ];
        } catch (Throwable $e) {
            $conn->rollback();
            $flash = ['type' => 'error', 'text' => 'Removal failed: ' . $e->getMessage()];
        }
    }
}

// -----------------------------------------------------------
// Load the direct allocations table (unchanged except we add
// `aq.quarter_location` was already there; nothing removed).
// -----------------------------------------------------------
$sql = "SELECT aq.id, aq.nic, aq.computer_no, aq.quarter_no, aq.quarter_name,
               aq.quarter_location, aq.quarter_type, aq.resident_name,
               aq.designation, aq.workplace, aq.allocated_date,
               a.application_no, a.application_status
        FROM allocated_quarters aq
        LEFT JOIN applications a ON a.id = (
            SELECT MAX(a2.id)
            FROM applications a2
            WHERE a2.nic = aq.nic
              AND a2.source = 'direct'
              AND a2.quarter_type = aq.quarter_type
              AND a2.applied_quarter_number = aq.quarter_no
              AND a2.applied_quarter_location = aq.quarter_location
        )
        WHERE aq.source = 'direct'";

if ($search !== '') {
    $sql .= " AND (aq.nic LIKE ? OR aq.resident_name LIKE ? OR aq.quarter_no LIKE ?)";
    $stmt = $conn->prepare($sql . ' ORDER BY aq.id DESC');
    $like = '%' . $search . '%';
    $stmt->bind_param('sss', $like, $like, $like);
} else {
    $stmt = $conn->prepare($sql . ' ORDER BY aq.id DESC');
}

$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    html, body { background: #f8fafc; }
    .direct-allocation-wrap { max-width: 1550px; margin: 20px auto; padding: 0 20px; }
    .direct-allocation-back { color: #7A1C1C; text-decoration: none; font-weight: 600; }
    .direct-allocation-title { text-align: center; color: #7A1C1C; margin: 18px 0 25px; }
    .direct-allocation-search { display: flex; gap: 10px; margin-bottom: 20px; padding: 16px 20px; background: #fff; border-radius: 12px; }
    .direct-allocation-search input { flex: 1; min-width: 0; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; }
    .direct-allocation-search button { border: 0; border-radius: 8px; padding: 10px 20px; background: #7A1C1C; color: #fff; cursor: pointer; font-weight: 600; }
    .direct-allocation-table { overflow-x: auto; padding: 20px; background: #fff; border-top: 5px solid #7A1C1C; border-radius: 12px; }
    .direct-allocation-table table { width: 100%; min-width: 1350px; border-collapse: collapse; font-size: .85rem; }
    .direct-allocation-table th, .direct-allocation-table td { border: 1px solid #e2e8f0; padding: 10px 8px; text-align: center; }
    .direct-allocation-table th { background: #f8fafc; font-size: .75rem; text-transform: uppercase; }
    .direct-allocation-action { display: inline-flex; gap: 6px; align-items: center; padding: 6px 12px; border-radius: 6px; background: #059669; color: #fff; text-decoration: none; white-space: nowrap; }
    .direct-allocation-status { display: inline-block; padding: 3px 10px; border-radius: 20px; background: #fef3c7; color: #92400e; font-weight: 600; font-size: .75rem; white-space: nowrap; }
    .direct-allocation-status.approved { background: #dcfce7; color: #166534; }

    /* >>> NEW: Remove button + modal styles */
    .btn-remove {
        background: #dc2626; color: #fff; border: 0; cursor: pointer;
        padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: .78rem;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-remove:hover { background: #b91c1c; }

    .remove-modal-backdrop {
        display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5);
        z-index: 1000; align-items: center; justify-content: center; padding: 15px;
    }
    .remove-modal-backdrop.show { display: flex; }
    .remove-modal {
        background: #fff; border-radius: 14px; padding: 26px;
        width: 100%; max-width: 480px; box-shadow: 0 20px 40px rgba(0,0,0,.2);
    }
    .remove-modal h3 {
        color: #991b1b; margin: 0 0 10px; font-size: 1.15rem;
        display: flex; align-items: center; gap: 8px;
    }
    .remove-modal p { color: #475569; font-size: .9rem; line-height: 1.5; margin: 0 0 14px; }
    .remove-modal .meta {
        background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px;
        padding: 10px 14px; font-size: .82rem; color: #7f1d1d; margin-bottom: 14px;
    }
    .remove-modal .meta strong { color: #991b1b; }
    .remove-modal input[type="text"] {
        width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 8px;
        border: 1px solid #cbd5e1; font-size: .9rem; outline: none;
    }
    .remove-modal input[type="text"]:focus {
        border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,.15);
    }
    .remove-modal-btns {
        display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;
    }
    .remove-modal-btns .btn-cancel {
        background: #e2e8f0; color: #1e293b; border: 0; cursor: pointer;
        padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: .85rem;
    }
    .remove-modal-btns .btn-confirm {
        background: #dc2626; color: #fff; border: 0; cursor: pointer;
        padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: .85rem;
    }
    .remove-modal-btns .btn-confirm:hover { background: #b91c1c; }
    .remove-modal-btns .btn-confirm:disabled {
        background: #fca5a5; cursor: not-allowed;
    }

    .flash-msg {
        padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
        font-weight: 600; font-size: .9rem;
    }
    .flash-msg.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .flash-msg.error   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
</style>

<div class="direct-allocation-wrap">
    <a class="direct-allocation-back" href="FA_dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2 class="direct-allocation-title">Direct Quarter Allocations (Audit)</h2>

    <?php if ($flash): ?>
        <div class="flash-msg <?= htmlspecialchars($flash['type']) ?>">
            <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
            <?= htmlspecialchars($flash['text']) ?>
        </div>
    <?php endif; ?>

    <form method="GET" class="direct-allocation-search">
        <input type="text" name="search" placeholder="Search by NIC, resident name, or quarter number"
               value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    </form>

    <div class="direct-allocation-table">
        <table>
            <thead>
                <tr>
                    <th>No.</th><th>NIC</th><th>Computer No.</th><th>Resident Name</th>
                    <th>Designation</th><th>Workplace</th><th>Quarter No.</th>
                    <th>Location</th><th>Type</th><th>Allocated Date</th>
                    <th>Application No.</th><th>Application Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="13" style="padding:25px; color:#64748b;">No direct allocations found.</td></tr>
            <?php else: foreach ($rows as $i => $row): ?>
                <?php
                $applicationStatus = $row['application_status'] ?? '';
                $statusLabel = $applicationStatus !== '' ? ucwords(str_replace('_', ' ', $applicationStatus)) : 'Application not submitted';
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($row['nic'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['computer_no'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['resident_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['designation'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['workplace'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['quarter_no'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['quarter_location'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['quarter_type'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['allocated_date'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['application_no'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <span class="direct-allocation-status<?= $applicationStatus === 'approved' ? ' approved' : '' ?>">
                            <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
                            <?php if (empty($row['application_no'])): ?>
                                <a class="direct-allocation-action" href="resume_direct_application.php?id=<?= (int)$row['id'] ?>">
                                    <i class="fa-solid fa-pen-to-square"></i> Go to Application
                                </a>
                            <?php endif; ?>

                            <!-- >>> NEW: Remove button -->
                            <button type="button"
                                    class="btn-remove"
                                    onclick="openRemoveModal(
                                        <?= (int)$row['id'] ?>,
                                        <?= htmlspecialchars(json_encode($row['nic']), ENT_QUOTES) ?>,
                                        <?= htmlspecialchars(json_encode($row['quarter_no']), ENT_QUOTES) ?>,
                                        <?= htmlspecialchars(json_encode($row['quarter_location']), ENT_QUOTES) ?>,
                                        <?= htmlspecialchars(json_encode($row['quarter_type']), ENT_QUOTES) ?>,
                                        <?= htmlspecialchars(json_encode($row['resident_name']), ENT_QUOTES) ?>
                                    )"
                                    title="Remove this direct allocation and free the quarter">
                                <i class="fa-solid fa-trash-can"></i> Remove
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- >>> NEW: Remove confirmation modal -->
<div class="remove-modal-backdrop" id="removeModalBackdrop">
    <div class="remove-modal" role="dialog" aria-modal="true" aria-labelledby="removeModalTitle">
        <h3 id="removeModalTitle">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Remove Direct Allocation
        </h3>
        <p>
            This will <strong>permanently delete</strong> the direct allocation, delete the
            linked application (if any), and set the quarter back to
            <strong>Available</strong> so it can be allocated again.
        </p>
        <div class="meta" id="removeModalMeta"></div>
        <form method="POST" id="removeForm">
            <input type="hidden" name="action" value="remove_allocation">
            <input type="hidden" name="allocation_id" id="removeAllocationId" value="">
            <label for="removeConfirmInput" style="display:block; font-size:.82rem; color:#475569; margin-bottom:6px;">
                Type <strong>REMOVE</strong> to confirm:
            </label>
            <input type="text" id="removeConfirmInput" name="confirm_text"
                   placeholder="REMOVE" autocomplete="off">
            <div class="remove-modal-btns">
                <button type="button" class="btn-cancel" onclick="closeRemoveModal()">Cancel</button>
                <button type="submit" class="btn-confirm" id="removeConfirmBtn" disabled>
                    <i class="fa-solid fa-trash-can"></i> Remove Allocation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRemoveModal(id, nic, quarterNo, quarterLoc, quarterType, resident) {
        document.getElementById('removeAllocationId').value = id;

        // Build a safe meta summary without injecting raw HTML from user data.
        const meta = document.getElementById('removeModalMeta');
        meta.innerHTML = '';
        const rows = [
            ['Applicant NIC', nic],
            ['Resident', resident],
            ['Quarter No.', quarterNo],
            ['Location', quarterLoc],
            ['Type', quarterType],
        ];
        rows.forEach(([label, value]) => {
            const div = document.createElement('div');
            div.innerHTML = '<strong>' + label + ':</strong> <span></span>';
            div.querySelector('span').textContent = value == null ? '—' : value;
            meta.appendChild(div);
        });

        document.getElementById('removeConfirmInput').value = '';
        document.getElementById('removeConfirmBtn').disabled = true;

        document.getElementById('removeModalBackdrop').classList.add('show');
        setTimeout(() => document.getElementById('removeConfirmInput').focus(), 50);
    }

    function closeRemoveModal() {
        document.getElementById('removeModalBackdrop').classList.remove('show');
    }

    // Enable confirm button only when the user types REMOVE
    document.getElementById('removeConfirmInput').addEventListener('input', function () {
        const btn = document.getElementById('removeConfirmBtn');
        btn.disabled = this.value.trim().toUpperCase() !== 'REMOVE';
    });

    // Close on backdrop click (but not on card click)
    document.getElementById('removeModalBackdrop').addEventListener('click', function (e) {
        if (e.target === this) closeRemoveModal();
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeRemoveModal();
    });
</script>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>