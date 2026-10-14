<?php
/**
 * Edit Applicant - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

// Session active aagiddare check maadi start maduvudhu
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// URL athava POST haraha labyena list vargaya laba kolluthade
$listType = isset($_GET['list']) ? htmlspecialchars($_GET['list']) : (isset($_POST['list']) ? htmlspecialchars($_POST['list']) : 'general');
$applicantId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : (isset($_POST['id']) ? htmlspecialchars($_POST['id']) : '');

// All lists titles
$listTitles = [
    'general' => 'General List',
    'guard' => 'Guard List',
    'station_master' => 'Station Master List',
    'controller' => 'Controller List',
    'deputy_commissioner' => 'Deputy Superintendent ( Transportation ) Colombo List'
];

$currentTitle = $listTitles[$listType] ?? 'General List';
$pageTitle = "Edit Applicant - " . $currentTitle . " - Sri Lanka Railways";

$message = '';
$status = '';

// Sample or existing database fetch data
$applicantData = [
    'nic' => $_POST['nic'] ?? '199812345678',
    'computerNo' => $_POST['computerNo'] ?? '101',
    'fullName' => $_POST['fullName'] ?? 'A. B. Perera',
    'designation' => $_POST['designation'] ?? 'Clerk',
    'workplace' => $_POST['workplace'] ?? 'Colombo Fort',
    'allocatedDate' => $_POST['allocatedDate'] ?? '2026-01-10',
    'allocatedHouseType' => $_POST['allocatedHouseType'] ?? 'None',
    'other' => $_POST['other'] ?? 'None'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nic = trim($_POST['nic'] ?? '');
    $computerNo = trim($_POST['computerNo'] ?? '');
    $fullName = trim($_POST['fullName'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $workplace = trim($_POST['workplace'] ?? '');
    $allocatedDate = trim($_POST['allocatedDate'] ?? '');
    $allocatedHouseType = trim($_POST['allocatedHouseType'] ?? '');
    $other = trim($_POST['other'] ?? '');

    $applicantData = $_POST;

    if (!empty($nic) && !empty($computerNo) && !empty($fullName)) {
        
        // --- DATABASE UPDATE CODE ---
        /*
        $conn = new mysqli("localhost", "username", "password", "railways_db");
        $stmt = $conn->prepare("UPDATE applicants SET nic=?, computer_no=?, full_name=?, designation=?, workplace=?, allocated_date=?, allocated_house_type=?, remarks=? WHERE id=? AND list_type=?");
        $stmt->bind_param("ssssssssis", $nic, $computerNo, $fullName, $designation, $workplace, $allocatedDate, $allocatedHouseType, $other, $applicantId, $listType);
        $stmt->execute();
        */

        $message = "Applicant details updated successfully in the " . $currentTitle . "!";
        $status = "success";
    } else {
        $message = "Please fill in all required fields.";
        $status = "error";
    }
}

// Admin Header inclusion
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
    :root {
        --primary-color: #7A1C1C;
        --primary-dark: #5c1515;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --text-main: #1e293b;
        --border-color: #cbd5e1;
    }

    .form-wrapper { 
        max-width: 1300px; 
        margin: 30px auto; 
        padding: 0 20px; 
        box-sizing: border-box; 
    }
    .back-link { 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        color: var(--primary-color); 
        text-decoration: none; 
        font-weight: 600; 
        margin-bottom: 20px; 
        font-size: 0.95rem; 
    }
    .back-link:hover { text-decoration: underline; }
    
    .card { 
        background: var(--card-bg); 
        padding: 40px; 
        border-radius: 12px; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid var(--primary-color); 
    }
    .card h2 { 
        color: var(--text-main); 
        font-size: 1.5rem; 
        margin-bottom: 30px; 
        text-transform: uppercase; 
        text-align: center; 
        line-height: 1.4;
        font-weight: 700;
    }
    .card form { 
        display: grid; 
        grid-template-columns: repeat(2, minmax(0, 1fr)); 
        column-gap: 35px; 
        row-gap: 20px;
    }
    .form-group { 
        margin-bottom: 0; 
    }
    .form-group label { 
        display: block; 
        margin-bottom: 8px; 
        font-weight: 600; 
        color: var(--text-main); 
        font-size: 0.85rem; 
        text-transform: uppercase;
    }
    .form-group input, .form-group select { 
        width: 100%; 
        padding: 12px 14px; 
        border: 1px solid var(--border-color); 
        border-radius: 6px; 
        font-size: 0.95rem; 
        outline: none; 
        background: #fff; 
        box-sizing: border-box; 
        color: #334155;
    }
    .form-group input:focus, .form-group select:focus { 
        border-color: var(--primary-color); 
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1); 
    }
    .btn-save { 
        grid-column: 1 / -1; 
        width: 100%; 
        padding: 14px; 
        background-color: #10b981; 
        color: #ffffff; 
        border: none; 
        border-radius: 6px; 
        font-size: 1rem; 
        font-weight: 600; 
        cursor: pointer; 
        margin-top: 10px; 
        transition: background-color 0.2s; 
    }
    .btn-save:hover { background-color: #059669; }
    
    .alert { 
        padding: 12px 16px; 
        margin-bottom: 25px; 
        border-radius: 8px; 
        font-weight: 500; 
        font-size: 0.95rem; 
    }
    .alert-success { background-color: #f0fdf4; color: #166534; border: 1px solid #86efac; }
    .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    @media (max-width: 768px) {
        .form-wrapper { margin: 15px auto; padding: 0 10px; }
        .card { padding: 25px 20px; border-radius: 10px; }
        .card h2 { font-size: 1.2rem; margin-bottom: 20px; }
        .card form { grid-template-columns: 1fr; column-gap: 0; row-gap: 15px; }
        .card form .btn-save { grid-column: auto; }
    }
</style>

<div class="form-wrapper">
    <a href="waiting_lists.php?tab=<?php echo $listType; ?>" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Waiting Lists</a>

    <div class="card">
        <h2>Edit Applicant Details - <?php echo $currentTitle; ?></h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $status; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="list" value="<?php echo $listType; ?>">
            <input type="hidden" name="id" value="<?php echo $applicantId; ?>">

            <div class="form-group">
                <label>NIC</label>
                <input type="text" id="nic" name="nic" placeholder="Enter NIC" required value="<?php echo htmlspecialchars($applicantData['nic'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Computer No.</label>
                <input type="text" id="computerNo" name="computerNo" inputmode="numeric" pattern="[0-9]+" title="Enter digits only" placeholder="Enter Computer Number" required value="<?php echo htmlspecialchars($applicantData['computerNo'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Applicant's Name</label>
                <input type="text" id="fullName" name="fullName" placeholder="Enter Full Name" required value="<?php echo htmlspecialchars($applicantData['fullName'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Designation</label>
                <input type="text" id="designation" name="designation" placeholder="Enter Designation" required value="<?php echo htmlspecialchars($applicantData['designation'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Workplace</label>
                <input type="text" id="workplace" name="workplace" placeholder="Enter Workplace" required value="<?php echo htmlspecialchars($applicantData['workplace'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Allocated Date</label>
                <input type="date" id="allocatedDate" name="allocatedDate" required value="<?php echo htmlspecialchars($applicantData['allocatedDate'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Allocated Quarter Type</label>
                <input type="text" id="allocatedHouseType" name="allocatedHouseType" placeholder="Enter Allocated Quarter Type" required value="<?php echo htmlspecialchars($applicantData['allocatedHouseType'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Other</label>
                <input type="text" id="other" name="other" placeholder="Any remarks" value="<?php echo htmlspecialchars($applicantData['other'] ?? ''); ?>">
            </div>

            <button type="submit" class="btn-save"><i class="fa-solid fa-floppy-disk"></i> Update Applicant</button>
        </form>
    </div>
</div>

<script>
    document.getElementById('computerNo').addEventListener('input', event => {
        event.target.value = event.target.value.replace(/\D/g, '');
    });
</script>

<?php
// Admin Footer inclusion
require_once __DIR__ . '/../layouts/admin_footer.php';
?>