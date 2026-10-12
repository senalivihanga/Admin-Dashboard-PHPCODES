<?>
/**
 * Add Maintenance Quarter - Sri Lanka Railways Admin
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newMaintenance = [
        'quarterNum' => $_POST['quarterNum'] ?? '',
        'location' => $_POST['location'] ?? '',
        'quarterName' => $_POST['quarterName'] ?? '',
        'division' => $_POST['division'] ?? '',
        'ownership' => $_POST['ownership'] ?? '',
        'quarterArea' => $_POST['quarterArea'] ?? '',
        'address' => $_POST['address'] ?? '',
        'quarterType' => $_POST['quarterType'] ?? '',
        'assessmentRent' => $_POST['assessmentRent'] ?? '',
        'status' => $_POST['status'] ?? 'Under Maintenance',
        'lastMaint' => $_POST['lastMaint'] ?? '',
        'electric' => isset($_POST['electric']),
        'water' => isset($_POST['water'])
    ];

    header('Location: maintenance.php');
    exit;
}

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

    .form-container { 
        max-width: 900px; 
        margin: 20px auto 40px auto; 
        background: white; 
        padding: 30px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid #7A1C1C; 
        box-sizing: border-box;
    }

    .form-grid { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 20px; 
    }

    .form-group { 
        margin-bottom: 0; 
    }

    .form-group.full-width { 
        grid-column: span 2; 
    }

    .form-group label { 
        display: block; 
        font-weight: 600; 
        margin-bottom: 8px; 
        color: #334155; 
        font-size: 0.85rem;
    }

    .form-group input, .form-group select { 
        width: 100%; 
        padding: 10px; 
        border: 1px solid #cbd5e1; 
        border-radius: 6px; 
        font-size: 0.95rem; 
        box-sizing: border-box; 
        outline: none;
        background-color: #ffffff;
    }

    .form-group input:focus, .form-group select:focus {
        border-color: #7A1C1C;
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1);
    }

    .checkbox-group { 
        display: flex; 
        gap: 20px; 
        margin-top: 10px; 
    }

    .checkbox-group label { 
        font-weight: normal; 
        display: inline-flex; 
        align-items: center; 
        gap: 5px; 
        cursor: pointer; 
        font-size: 0.9rem;
    }

    .btn-submit { 
        background-color: #10b981; 
        color: white; 
        border: none; 
        padding: 12px 20px; 
        width: 100%; 
        border-radius: 6px; 
        font-weight: 600; 
        cursor: pointer; 
        font-size: 1rem; 
        grid-column: span 2; 
        margin-top: 10px; 
        transition: background 0.2s;
    }
    .btn-submit:hover { 
        background-color: #059669; 
    }

    .back-link { 
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        color: #7A1C1C; 
        text-decoration: none; 
        font-weight: 600; 
        margin-bottom: 18px; 
        font-size: 0.85rem; 
        transition: color 0.2s; 
    }
    .back-link:hover { 
        text-decoration: underline; 
    }

    .user-profile-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
    }

    .user-profile-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #7A1C1C;
        color: #ffffff;
        padding: 6px 14px;
        border-radius: 30px;
        cursor: pointer;
        user-select: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .profile-dropdown {
        display: none;
        position: absolute;
        left: 0;
        top: 105%;
        background: #ffffff;
        min-width: 220px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
        z-index: 100;
        border: 1px solid #e2e8f0;
    }

    .profile-dropdown.show {
        display: block;
    }

    .profile-dropdown a {
        color: #1e293b;
        padding: 10px 15px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        transition: background 0.2s;
    }

    .profile-dropdown a:hover {
        background: #f1f5f9;
        color: #7A1C1C;
    }

    .profile-dropdown a.logout-btn {
        color: #ef4444;
    }

    .profile-dropdown a.logout-btn:hover {
        background: #fee2e2;
    }

    .custom-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
        padding: 15px;
    }

    .custom-modal-content {
        background-color: #fff;
        padding: 30px;
        border-radius: 12px;
        width: 100%;
        max-width: 380px;
        text-align: center;
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }

    .custom-modal-content i {
        font-size: 2.8rem;
        color: #7A1C1C;
        margin-bottom: 15px;
    }

    .custom-modal-content h3 {
        color: #1e293b;
        margin-bottom: 10px;
        font-size: 1.3rem;
    }

    .custom-modal-content p {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 20px;
    }

    .modal-action-btns {
        display: flex;
        justify-content: center;
        gap: 12px;
    }

    .btn-modal-confirm, .btn-modal-cancel {
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        color: white;
    }

    .btn-modal-confirm { background-color: #ef4444; }
    .btn-modal-cancel { background-color: #64748b; }

    @media (max-width: 768px) {
        .form-container { 
            margin: 15px auto; 
            padding: 20px 15px; 
        }
        .form-grid { 
            grid-template-columns: 1fr; 
            gap: 15px;
        }
        .form-group.full-width, .btn-submit { 
            grid-column: span 1; 
        }
    }
</style>

<div style="max-width: 900px; margin: 20px auto 0 auto; padding: 0 20px; box-sizing: border-box;">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
        <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
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
</div>

<div class="form-container">
    <h2 style="color: #0a0a0a; margin-bottom: 25px; text-align: center; font-size: 1.6rem; font-weight: 700;">Add Quarter Under Maintenance</h2>
    <form method="POST" class="form-grid">
        <div class="form-group">
            <label for="quarterNum">Quarter No.</label>
            <input type="text" id="quarterNum" name="quarterNum" placeholder="Enter quarter number" required>
        </div>
        <div class="form-group">
            <label for="location">Quarter Location</label>
            <input type="text" id="location" name="location" placeholder="Enter quarter location" required>
        </div>
        <div class="form-group">
            <label for="quarterName">Quarter Name</label>
            <input type="text" id="quarterName" name="quarterName" placeholder="Enter quarter name">
        </div>
        <div class="form-group">
            <label for="division">Quarter Division</label>
            <input type="text" id="division" name="division" placeholder="Enter quarter division" required>
        </div>
        <div class="form-group">
            <label for="ownership">Ownership</label>
            <input type="text" id="ownership" name="ownership" placeholder="Enter ownership details" required>
        </div>
        <div class="form-group">
            <label for="quarterArea">Quarter Area</label>
            <input type="text" id="quarterArea" name="quarterArea" placeholder="Enter quarter area" required>
        </div>
        <div class="form-group full-width">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" placeholder="Enter address">
        </div>
        <div class="form-group">
            <label for="quarterType">Quarter Type</label>
            <select id="quarterType" name="quarterType" required>
                <option value="">-- Select Quarter Type --</option>
                <option value="Bungalows">Bungalows</option>
                <option value="Type I">Type I</option>
                <option value="Type II">Type II</option>
                <option value="Type III">Type III</option>
            </select>
        </div>
        <div class="form-group">
            <label for="assessmentRent">Assessment Rent (LKR)</label>
            <input type="number" id="assessmentRent" name="assessmentRent" step="0.01" placeholder="Enter assessment rent amount" required>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="Under Maintenance" selected>Under Maintenance</option>
                <option value="Available">Available</option>
                <option value="Occupied">Occupied</option>
            </select>
        </div>
        <div class="form-group">
            <label for="lastMaint">Last Maintenance Date</label>
            <input type="date" id="lastMaint" name="lastMaint" required>
        </div>
        <div class="form-group full-width">
            <label>Facilities</label>
            <div class="checkbox-group">
                <label><input type="checkbox" id="electric" name="electric" checked> Electricity</label>
                <label><input type="checkbox" id="water" name="water" checked> Water</label>
            </div>
        </div>
        <button type="submit" class="btn-submit">Save Maintenance Record</button>
    </form>
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
        if (dropdown) {
            dropdown.classList.toggle('show');
        }
    }

    window.addEventListener('click', function(e) {
        if (!e.target.closest('.user-profile-wrapper')) {
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown && dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        }
    });

    function openLogoutModal(event) {
        event.preventDefault();
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) {
            dropdown.classList.remove('show');
        }
        document.getElementById('logoutModal').style.display = 'flex';
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    function executeLogout() {
        window.location.href = "login.php";
    }
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>