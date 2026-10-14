<?php
/**
 * Edit Quarter - Sri Lanka Railways Admin
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Edit Quarter - Sri Lanka Railways Admin";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$quartersDatabase = [
    [
        'quarterNum' => 'Q-01',
        'location' => 'Colombo',
        'quarterName' => 'Bungalow A',
        'division' => 'Way & Works',
        'ownership' => 'Railway Department',
        'quarterArea' => '1,450 sq.ft',
        'address' => 'No. 12, Railway Avenue, Colombo 10',
        'quarterType' => 'Bungalows',
        'assessmentRent' => '5500.00',
        'status' => 'Under Maintenance',
        'electric' => true,
        'water' => true
    ],
    [
        'quarterNum' => 'Q-02',
        'location' => 'Kandy',
        'quarterName' => 'Type II House',
        'division' => 'Signal & Telecommunication',
        'ownership' => 'Railway Department',
        'quarterArea' => '950 sq.ft',
        'address' => 'No. 8, Peradeniya Road, Kandy',
        'quarterType' => 'Type II',
        'assessmentRent' => '4200.00',
        'status' => 'Available',
        'electric' => true,
        'water' => true
    ]
];

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
        max-width: 800px; 
        margin: 20px auto; 
        padding: 0 20px; 
        width: 100%;
        box-sizing: border-box;
    }

    .back-link { 
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        color: #7A1C1C; 
        text-decoration: none; 
        font-weight: 600; 
        margin-bottom: 20px; 
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

    .card { 
        background: #ffffff; 
        padding: 35px; 
        border-radius: 15px; 
        box-shadow: 0 10px 25px rgba(0,0,0,0.08); 
        border-top: 5px solid #7A1C1C; 
    }

    .card h2 { 
        color: #1e293b; 
        font-size: 1.5rem; 
        margin-bottom: 20px; 
        text-transform: uppercase; 
        text-align: center; 
        font-weight: 700;
    }

    .form-group { 
        margin-bottom: 20px; 
    }

    .form-group label { 
        display: block; 
        font-weight: 600; 
        color: #475569; 
        margin-bottom: 8px; 
        font-size: 0.9rem; 
    }

    .form-group input, .form-group select { 
        width: 100%; 
        padding: 12px 15px; 
        border: 1px solid #cbd5e1; 
        border-radius: 6px; 
        outline: none; 
        font-size: 0.95rem; 
        box-sizing: border-box; 
        background-color: #ffffff;
    }

    .form-group input:focus, .form-group select:focus { 
        border-color: #7A1C1C; 
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1); 
    }

    .form-row { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 20px; 
    }

    .checkbox-group { 
        display: flex; 
        gap: 20px; 
        margin-top: 5px; 
    }

    .checkbox-group label { 
        font-weight: normal; 
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        cursor: pointer; 
        font-size: 0.95rem;
        color: #1e293b;
    }

    .btn-submit { 
        background-color: #7A1C1C; 
        color: white; 
        border: none; 
        padding: 12px 20px; 
        border-radius: 6px; 
        cursor: pointer; 
        font-weight: 600; 
        width: 100%; 
        font-size: 1rem; 
        transition: background 0.2s; 
    }

    .btn-submit:hover { 
        background-color: #5c1414; 
    }

    #successAlert { 
        display: none; 
        background-color: #dcfce7; 
        color: #166534; 
        padding: 12px 20px; 
        border-radius: 8px; 
        margin-bottom: 20px; 
        font-weight: 600; 
        border: 1px solid #bbf7d0; 
        text-align: center; 
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
        .form-row {
            grid-template-columns: 1fr;
            gap: 0;
        }
        .container {
            margin: 15px auto;
            padding: 0 10px;
        }
    }
</style>

<div class="container">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
        <a href="quarters_list.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Quarters List</a>
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
                <a href="quarters_list.php"><i class="fa-solid fa-arrow-left"></i> Back to Quarters List</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Edit Quarter Record</h2>
        
        <div id="successAlert"><i class="fa-solid fa-circle-check"></i> Quarter record updated successfully!</div>

        <form id="editQuarterForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="quarterNum">Quarter No.:</label>
                    <input type="text" id="quarterNum" required>
                </div>
                <div class="form-group">
                    <label for="quarterName">Quarter Name:</label>
                    <input type="text" id="quarterName">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="division">Quarter Division:</label>
                    <input type="text" id="division" required>
                </div>
                <div class="form-group">
                    <label for="ownership">Ownership:</label>
                    <input type="text" id="ownership" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="quarterArea">Quarter Area (sq.ft):</label>
                    <input type="text" id="quarterArea" required>
                </div>
                <div class="form-group">
                    <label for="location">Quarter Location:</label>
                    <input type="text" id="location" required>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="quarterType">Quarter Type:</label>
                    <select id="quarterType" required>
                        <option value="">-- Select Quarter Type --</option>
                        <option value="Bungalows">Bungalows</option>
                        <option value="Type I">Type I</option>
                        <option value="Type II">Type II</option>
                        <option value="Type III">Type III</option>
                        <option value="Grade III">Grade III</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="assessmentRent">Assessment Rent (LKR):</label>
                    <input type="text" id="assessmentRent" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" required>
                        <option value="Available">Available</option>
                        <option value="Occupied">Occupied</option>
                        <option value="Under Maintenance">Under Maintenance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Facility (Electricity & Water):</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" id="electric"> Electricity</label>
                        <label><input type="checkbox" id="water"> Water</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit"><i class="fa-solid fa-floppy-disk"></i> Update Changes</button>
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
    const serverQuartersData = <?php echo json_encode($quartersDatabase); ?>;

    function getStoredQuartersData() {
        const data = localStorage.getItem('quartersData_v2');
        let parsed = null;
        try {
            parsed = data ? JSON.parse(data) : null;
        } catch (e) {
            parsed = null;
        }

        if (!Array.isArray(parsed) || parsed.length === 0) {
            localStorage.setItem('quartersData_v2', JSON.stringify(serverQuartersData));
            return [...serverQuartersData];
        }

        return parsed;
    }

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

    let editIndex = null;

    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        editIndex = urlParams.get('index') ?? urlParams.get('id');

        const quartersList = getStoredQuartersData();
        if (editIndex !== null && quartersList[editIndex]) {
            const item = quartersList[editIndex];
            document.getElementById('quarterNum').value = item.quarterNum || '';
            document.getElementById('quarterName').value = item.quarterName || '';
            document.getElementById('division').value = item.division || '';
            document.getElementById('ownership').value = item.ownership || '';
            document.getElementById('quarterArea').value = item.quarterArea || '';
            document.getElementById('location').value = item.location || '';
            document.getElementById('address').value = item.address || '';
            document.getElementById('quarterType').value = item.quarterType || '';
            document.getElementById('assessmentRent').value = item.assessmentRent || '';
            document.getElementById('status').value = item.status || 'Available';
            document.getElementById('electric').checked = !!item.electric;
            document.getElementById('water').checked = !!item.water;
        }
    });

    document.getElementById('editQuarterForm').addEventListener('submit', function(e) {
        e.preventDefault();

        let quartersList = getStoredQuartersData();
        if (editIndex !== null && quartersList[editIndex]) {
            quartersList[editIndex] = {
                quarterNum: document.getElementById('quarterNum').value.trim(),
                quarterName: document.getElementById('quarterName').value.trim(),
                division: document.getElementById('division').value.trim(),
                ownership: document.getElementById('ownership').value.trim(),
                quarterArea: document.getElementById('quarterArea').value.trim(),
                location: document.getElementById('location').value.trim(),
                address: document.getElementById('address').value.trim(),
                quarterType: document.getElementById('quarterType').value,
                assessmentRent: document.getElementById('assessmentRent').value.trim(),
                status: document.getElementById('status').value,
                electric: document.getElementById('electric').checked,
                water: document.getElementById('water').checked
            };

            localStorage.setItem('quartersData_v2', JSON.stringify(quartersList));

            const alertBox = document.getElementById('successAlert');
            alertBox.style.display = 'block';

            setTimeout(() => {
                window.location.href = "quarters_list.php";
            }, 1500);
        }
    });
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>