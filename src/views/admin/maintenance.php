<?php
/**
 * Quarters Maintenance - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Quarters Maintenance - Sri Lanka Railways";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$maintenanceData = [
    [
        'quarterNum' => 'Q-01',
        'quarterName' => 'Bungalow A',
        'location' => 'Colombo',
        'quarterType' => 'Bungalows',
        'status' => 'Under Maintenance',
        'lastMaint' => '2025-05-12'
    ],
    [
        'quarterNum' => 'Q-03',
        'quarterName' => 'Staff Flat',
        'location' => 'Galle',
        'quarterType' => 'Type I',
        'status' => 'Under Maintenance',
        'lastMaint' => '2025-06-05'
    ]
];

$searchQuery = trim($_GET['search'] ?? '');

require_once __DIR__ . '/../layouts/admin_header.php';
?>
<style>
    :root {
        --primary-color: #7A1C1C;
        --primary-dark: #571a1a;
        --danger-color: #ef4444;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --text-main: #1e293b;
        --border-color: #e2e8f0;
    }

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-y: auto;
        background-color: var(--bg-color);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container { 
        max-width: 1400px; 
        margin: 20px auto; 
        padding: 0 20px; 
        width: 100%;
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
        transition: color 0.2s; 
    }
    .back-link:hover { 
        color: var(--primary-dark);
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
        background: var(--primary-color);
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
        border: 1px solid var(--border-color);
    }

    .profile-dropdown.show {
        display: block;
    }

    .profile-dropdown a {
        color: var(--text-main);
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
        color: var(--primary-color);
    }

    .profile-dropdown a.logout-btn {
        color: var(--danger-color);
    }

    .profile-dropdown a.logout-btn:hover {
        background: #fee2e2;
    }

    .page-title { 
        text-align: center; 
        color: var(--primary-color); 
        font-size: 1.8rem; 
        margin-bottom: 25px; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        text-decoration: underline; 
        text-underline-offset: 8px; 
        font-weight: 700;
    }
    
    .search-card { 
        background: var(--card-bg); 
        padding: 20px 25px; 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.04); 
        margin-bottom: 25px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        flex-wrap: wrap; 
        gap: 20px; 
        border: 1px solid var(--border-color);
        width: 100%;
        box-sizing: border-box;
    }

    .search-wrapper { 
        display: flex; 
        gap: 12px; 
        align-items: center; 
        flex: 1;
        min-width: 300px;
        max-width: 450px;
    }
    
    .search-input-container {
        position: relative;
        flex: 1;
    }

    .search-input { 
        width: 100%;
        padding: 10px 36px 10px 14px; 
        border-radius: 8px; 
        border: 1px solid #cbd5e1; 
        font-size: 0.9rem; 
        outline: none; 
        box-sizing: border-box;
        transition: border-color 0.2s, box-shadow 0.2s;
        background-color: #ffffff;
    }
    
    .search-input:focus { 
        border-color: var(--primary-color); 
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1); 
    }
    
    .search-clear-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        text-decoration: none;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }
    
    .search-clear-icon:hover {
        color: var(--text-main);
    }

    .search-btn { 
        border: none; 
        padding: 10px 20px; 
        border-radius: 8px; 
        font-weight: 600; 
        cursor: pointer; 
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        font-size: 0.9rem;
        text-decoration: none; 
        transition: background 0.2s; 
        background-color: var(--primary-color); 
        color: white; 
        white-space: nowrap;
    }
    .search-btn:hover { 
        background-color: var(--primary-dark); 
    }

    .add-maint-btn { 
        background-color: #10b981; 
        color: white; 
        padding: 10px 18px; 
        border-radius: 8px; 
        font-weight: 600; 
        font-size: 0.875rem; 
        border: none; 
        cursor: pointer; 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        text-decoration: none; 
        transition: background 0.2s; 
    }
    .add-maint-btn:hover { background-color: #059669; }

    .table-card { background: #ffffff; padding: 25px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-top: 5px solid #7A1C1C; overflow-x: auto; border: 1px solid #e2e8f0; }
    .maint-table { width: 100%; border-collapse: collapse; text-align: left; min-width: 1100px; }
    .maint-table th, .maint-table td { padding: 14px 12px; border: 1px solid #e2e8f0; text-align: center; font-size: 0.85rem; vertical-align: middle; }
    .maint-table th { background-color: #f8fafc; color: #1e293b; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    .maint-table td { color: #4b5563; font-weight: normal; }
    .maint-table tbody tr { transition: background-color 0.15s ease; }
    .maint-table tbody tr:hover { background-color: #f1f5f9; }
    .no-data { text-align: center; padding: 25px; color: #64748b; font-style: italic; }

    .action-cell { display: flex; justify-content: center; align-items: center; gap: 6px; }
    .action-btn { border: none; width: 32px; height: 32px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: background-color 0.2s; }
    .btn-view { background-color: #8b5cf6; color: white; }
    .btn-view:hover { background-color: #7c3aed; }
    .btn-edit { background-color: #3b82f6; color: white; }
    .btn-edit:hover { background-color: #2563eb; }
    .btn-delete { background-color: #dc2626; color: white; }
    .btn-delete:hover { background-color: #b91c1c; }

    .custom-msg-box { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
    .msg-content { background: white; padding: 30px; border-radius: 12px; text-align: center; width: 100%; max-width: 380px; box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
    .msg-content p { margin-bottom: 20px; font-size: 0.95rem; color: #64748b; font-weight: 600; }
    .msg-content h3 { color: #1e293b; margin-bottom: 10px; font-size: 1.3rem; }
    .msg-btns { display: flex; justify-content: center; gap: 12px; }
    .msg-btn { padding: 8px 15px; border: none; border-radius: 5px; font-weight: 600; cursor: pointer; color: white; }
    .msg-btn-yes { background-color: #dc2626; }
    .msg-btn-no { background-color: #64748b; }

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
        .container {
            margin: 15px auto;
            padding: 0 10px;
        }
        .search-card {
            flex-direction: column;
            align-items: stretch;
        }
        .search-wrapper {
            max-width: 100%;
        }
        .add-maint-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="container">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) &&$_SESSION['role'] === 'admin')): ?>
        <a href="quarters_list.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
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
                <a href="quarters-list.php"><i class="fa-solid fa-arrow-left"></i> Back to Quarters List</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="page-title">Quarters Maintenance Details</h2>

    <form method="GET" action="" class="search-card">
        <div class="search-wrapper">
            <div class="search-input-container">
                <input type="text" name="search" class="search-input" placeholder="Search by Quarter No or Name..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                <?php if (!empty($searchQuery)): ?>
                    <a href="?" class="search-clear-icon" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
        <a href="add_maintenance.php" class="add-maint-btn"><i class="fa-solid fa-plus-circle"></i> Add Maintenance Quarter</a>
    </form>

    <div class="table-card">
        <div class="table-responsive">
            <table class="maint-table" id="maintTable">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Quarter No.</th>
                        <th>Quarter Name</th>
                        <th>Quarter Location</th>
                        <th>Quarter Type</th>
                        <th>Status</th>
                        <th>Last Maintenance Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="maintBody">
                    <!-- Dynamic rendering & Search filtering from localStorage -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Custom Delete Confirmation Modal -->
<div class="custom-msg-box" id="deleteModal">
    <div class="msg-content">
        <i class="fa-solid fa-triangle-exclamation" style="color: #dc2626; font-size: 2.5rem; margin-bottom: 10px; display:block;"></i>
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete this maintenance record?</p>
        <div class="msg-btns">
            <button class="msg-btn msg-btn-no" onclick="closeDeleteModal()">Cancel</button>
            <button class="msg-btn msg-btn-yes" id="confirmDeleteBtn">Yes, Delete</button>
        </div>
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
    const serverMaintenanceData = <?php echo json_encode($maintenanceData); ?>;
    const searchQuery = "<?php echo htmlspecialchars($searchQuery, ENT_QUOTES); ?>".toLowerCase();

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

    function getStoredMaintenanceData() {
        const data = localStorage.getItem('maintenanceData_v2');
        let parsed = null;
        try {
            parsed = data ? JSON.parse(data) : null;
        } catch (e) {
            parsed = null;
        }

        if (!Array.isArray(parsed) || parsed.length === 0) {
            localStorage.setItem('maintenanceData_v2', JSON.stringify(serverMaintenanceData));
            return [...serverMaintenanceData];
        }

        return parsed;
    }

    function saveStoredMaintenanceData(data) {
        localStorage.setItem('maintenanceData_v2', JSON.stringify(data));
    }

    function loadTableData() {
        const tbody = document.getElementById('maintBody');
        tbody.innerHTML = '';

        let data = getStoredMaintenanceData();

        if (searchQuery !== '') {
            data = data.filter(item => 
                (item.quarterNum && item.quarterNum.toLowerCase().includes(searchQuery)) ||
                (item.quarterName && item.quarterName.toLowerCase().includes(searchQuery)) ||
                (item.location && item.location.toLowerCase().includes(searchQuery))
            );
        }

        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="no-data">No quarters are currently under maintenance.</td></tr>`;
            return;
        }

        data.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td><strong>${item.quarterNum || '-'}</strong></td>
                <td>${item.quarterName || '-'}</td>
                <td>${item.location || '-'}</td>
                <td>${item.quarterType || '-'}</td>
                <td><span style="background:#fef3c7; color:#d97706; padding:4px 10px; border-radius:20px; font-size:0.75rem; font-weight:600;">${item.status || '-'}</span></td>
                <td>${item.lastMaint || '-'}</td>
                <td>
                    <div class="action-cell">
                        <a href="view_maintenance.php?index=${index}" class="action-btn btn-view" title="View Details"><i class="fa-solid fa-eye"></i></a>
                        <a href="edit_maintenance.php?index=${index}" class="action-btn btn-edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                        <button type="button" class="action-btn btn-delete" onclick="promptDelete(${index})" title="Delete"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    window.addEventListener('DOMContentLoaded', () => {
        loadTableData();
    });

    let deleteTargetIndex = null;
    function promptDelete(index) {
        deleteTargetIndex = index;
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        deleteTargetIndex = null;
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
        if (deleteTargetIndex !== null) {
            let data = getStoredMaintenanceData();
            data.splice(deleteTargetIndex, 1);
            saveStoredMaintenanceData(data);
            loadTableData();
            closeDeleteModal();
        }
    });
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>