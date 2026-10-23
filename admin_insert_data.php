<?php
/**
 * Admin Master Data Management - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Admin Master Data Management - Sri Lanka Railways";

// PHP session madhye data save karnyasathi (database nastech tar session vaparun)
if (!isset($_SESSION['slr_master_entries'])) {
    $_SESSION['slr_master_entries'] = [];
}

// Data add kinva delete karnyasathi request handle karne
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $categoryKey = trim($_POST['categoryKey'] ?? '');
        $categoryName = trim($_POST['categoryName'] ?? '');
        $value = trim($_POST['value'] ?? '');

        if (!empty($categoryKey) && !empty($categoryName) && !empty($value)) {
            $_SESSION['slr_master_entries'][] = [
                'id' => time() . mt_rand(100, 999),
                'category' => $categoryName,
                'categoryKey' => $categoryKey,
                'value' => $value
            ];
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $deleteId = $_POST['id'] ?? '';
        $_SESSION['slr_master_entries'] = array_filter($_SESSION['slr_master_entries'], function($item) use ($deleteId) {
            return $item['id'] != $deleteId;
        });
        $_SESSION['slr_master_entries'] = array_values($_SESSION['slr_master_entries']);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

$entries = $_SESSION['slr_master_entries'];

// Admin Header Include karne[cite: 11]
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-y: auto;
        background-color: #f8fafc;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container {
        max-width: 1000px;
        margin: 30px auto;
        padding: 0 20px;
        box-sizing: border-box;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #7A1C1C;
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    .card {
        background: #ffffff;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        border-top: 5px solid #7A1C1C;
        margin-bottom: 30px;
        box-sizing: border-box;
    }

    .card h2 {
        color: #1e293b;
        font-size: 1.5rem;
        margin-bottom: 20px;
        text-transform: uppercase;
        text-align: center;
    }

    .entry-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .entry-box {
        background: #f1f5f9;
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
    }

    .entry-box label {
        display: block;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .input-group {
        display: flex;
        gap: 8px;
    }

    .input-group input {
        flex: 1;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        outline: none;
        font-size: 0.9rem;
        background: #fff;
    }

    .input-group input:focus {
        border-color: #7A1C1C;
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1);
    }

    .btn-save-single {
        background-color: #7A1C1C;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: background 0.2s;
        white-space: nowrap;
    }

    .btn-save-single:hover {
        background-color: #5c1414;
    }

    .table-card {
        background: #ffffff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        border-top: 5px solid #7A1C1C;
        overflow-x: auto;
    }

    .table-card h3 {
        color: #1e293b;
        font-size: 1.2rem;
        margin-bottom: 15px;
        text-align: center;
    }

    .master-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        min-width: 500px;
    }

    .master-table th, .master-table td {
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        font-size: 0.88rem;
        text-align: center;
    }

    .master-table th {
        background-color: #f8fafc;
        color: #1e293b;
        font-weight: 700;
    }

    .btn-delete {
        background-color: #dc2626;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-delete:hover {
        background-color: #b91c1c;
    }

    @media (max-width: 768px) {
        .entry-grid {
            grid-template-columns: 1fr;
        }
        .container {
            margin: 15px auto;
            padding: 0 12px;
        }
    }
</style>

<div class="container">
    <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>

    <div class="card">
        <h2>Add New Data</h2>

        <div class="entry-grid">
            
            <!-- Location -->
            <div class="entry-box">
                <label>Location:</label>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="categoryKey" value="location">
                    <input type="hidden" name="categoryName" value="Location">
                    <div class="input-group">
                        <input type="text" name="value" placeholder="Enter new location" required>
                        <button type="submit" class="btn-save-single"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                    </div>
                </form>
            </div>

            <!-- Quarter No -->
            <div class="entry-box">
                <label>Quarter No.:</label>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="categoryKey" value="quarterNo">
                    <input type="hidden" name="categoryName" value="Quarter No.">
                    <div class="input-group">
                        <input type="text" name="value" placeholder="Enter quarter number" required>
                        <button type="submit" class="btn-save-single"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                    </div>
                </form>
            </div>

            <!-- Quarter Type -->
            <div class="entry-box">
                <label>Quarter Type:</label>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="categoryKey" value="quarterType">
                    <input type="hidden" name="categoryName" value="Quarter Type">
                    <div class="input-group">
                        <input type="text" name="value" placeholder="Enter quarter type" required>
                        <button type="submit" class="btn-save-single"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                    </div>
                </form>
            </div>

            <!-- Current Workplace -->
            <div class="entry-box">
                <label>Current Workplace:</label>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="categoryKey" value="currentWorkplace">
                    <input type="hidden" name="categoryName" value="Current Workplace">
                    <div class="input-group">
                        <input type="text" name="value" placeholder="Enter workplace" required>
                        <button type="submit" class="btn-save-single"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                    </div>
                </form>
            </div>

            <!-- Designation -->
            <div class="entry-box">
                <label>Designation:</label>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="categoryKey" value="designation">
                    <input type="hidden" name="categoryName" value="Designation">
                    <div class="input-group">
                        <input type="text" name="value" placeholder="Enter designation" required>
                        <button type="submit" class="btn-save-single"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                    </div>
                </form>
            </div>

            <!-- Sub Department -->
            <div class="entry-box">
                <label>Sub Department:</label>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="categoryKey" value="subDepartment">
                    <input type="hidden" name="categoryName" value="Sub Department">
                    <div class="input-group">
                        <input type="text" name="value" placeholder="Enter sub department" required>
                        <button type="submit" class="btn-save-single"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                    </div>
                </form>
            </div>

            <!-- Account Branch -->
            <div class="entry-box">
                <label>Account Branch:</label>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="categoryKey" value="accountBranch">
                    <input type="hidden" name="categoryName" value="Account Branch">
                    <div class="input-group">
                        <input type="text" name="value" placeholder="Enter account branch" required>
                        <button type="submit" class="btn-save-single"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                    </div>
                </form>
            </div>

            <!-- Office Maintaining Personal Files -->
            <div class="entry-box">
                <label>Office Maintaining Personal Files:</label>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="categoryKey" value="personalFileOffice">
                    <input type="hidden" name="categoryName" value="Personal File Office">
                    <div class="input-group">
                        <input type="text" name="value" placeholder="Enter office name" required>
                        <button type="submit" class="btn-save-single"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- Master Data View Table -->
    <div class="table-card">
        <h3>Saved New Data Records</h3>
        <table class="master-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Category</th>
                    <th>Saved Value</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entries)): ?>
                    <tr>
                        <td colspan="4" style="color: #64748b; font-style: italic;">No master entries saved yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($entries as $index => $item): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><strong><?php echo htmlspecialchars($item['category']); ?></strong></td>
                            <td><?php echo htmlspecialchars($item['value']); ?></td>
                            <td>
                                <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this entry?');" style="margin:0;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn-delete"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Admin Footer Include karne[cite: 11]
require_once __DIR__ . '/../layouts/admin_footer.php';
?>