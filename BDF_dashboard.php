<?>
<?php
/**
 * Subject Clerk Forwarded Letters - BDF Portal - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Page title setup
$pageTitle = "Subject Clerk Forwarded Letters - BDF Portal - Sri Lanka Railways";

// User data setup
$userData = [
    'name' => 'BDF Member',
    'role' => 'Sri Lanka Railways - Board of Directors',
    'initials' => 'BDF'
];

// Received Letters mock/session records data
$forwardedLetters = [
    [
        'app_no' => 'SLR/AOT/2026/0001',
        'name' => 'A. B. Perera',
        'nic' => '883421092V',
        'designation' => 'Station Master',
        'quarter' => 'Q-01',
        'quarter_name' => 'Sunflower Manor',
        'date' => '2026-11-10',
        'status' => 'Letter Received'
    ],
    [
        'app_no' => 'SLR/AOT/2026/0002',
        'name' => 'S. K. Silva',
        'nic' => '925631485V',
        'designation' => 'Signal Inspector',
        'quarter' => 'Q-02',
        'quarter_name' => 'Railway Bungalow 02',
        'date' => '2026-11-11',
        'status' => 'Letter Received'
    ],
    [
        'app_no' => 'SLR/AOT/2026/0003',
        'name' => 'M. N. Fernando',
        'nic' => '951234789V',
        'designation' => 'Loco Driver',
        'quarter' => 'Q-03',
        'quarter_name' => 'Staff Flat 04',
        'date' => '2026-11-12',
        'status' => 'Letter Received'
    ]
];

// Include Admin Header[cite: 11]
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

    .container-custom {
        max-width: 1350px;
        margin: 20px auto;
        padding: 0 20px;
        box-sizing: border-box;
    }

    /* Profile Dropdown Styles (IB Dashboard මෙන් දකුණුපසින්) */
    .user-profile-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 15px;
        float: right;
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
        right: 0;
        top: 105%;
        background: #ffffff;
        min-width: 180px;
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

    /* Main Page Title Styles */
    .page-main-title {
        text-align: center;
        color: #7A1C1C;
        font-size: 1.75rem;
        margin-bottom: 25px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 8px;
        clear: both;
    }

    /* Section Subtitle Styles */
    .section-title-sub {
        color: #1e293b;
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 15px;
        border-left: 4px solid #7A1C1C;
        padding-left: 10px;
        text-transform: uppercase;
    }

    /* Search Bar Card */
    .filter-card {
        background: #ffffff;
        padding: 18px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04);
        border: 1px solid #e2e8f0;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .search-box-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        flex: 1;
        max-width: 450px;
    }

    .search-box-wrapper i {
        position: absolute;
        left: 14px;
        color: #64748b;
        font-size: 0.9rem;
    }

    .search-input {
        width: 100%;
        padding: 10px 14px 10px 40px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        font-size: 0.9rem;
        outline: none;
        background-color: #ffffff;
    }

    .search-input:focus {
        border-color: #7A1C1C;
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1);
    }

    .search-btn {
        background-color: #7A1C1C;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s;
        font-size: 0.9rem;
    }

    .search-btn:hover {
        background-color: #5c1414;
    }

    /* Table Card */
    .table-card {
        background: #ffffff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        border-top: 5px solid #7A1C1C;
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
        min-width: 1150px;
    }

    .data-table th, .data-table td {
        padding: 12px 14px;
        border: 1px solid #e2e8f0;
        font-size: 0.88rem;
        vertical-align: middle;
    }

    .data-table th {
        background-color: #f8fafc;
        color: #1e293b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.78rem;
    }

    .data-table td {
        color: #4b5563;
    }

    .data-table td:nth-child(2) {
        font-weight: 700;
        color: #1e293b;
    }

    /* Status Badge */
    .status-badge {
        background: #fef3c7;
        color: #b45309;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
        display: inline-block;
        text-align: center;
    }

    /* Action Buttons */
    .action-btn-group {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .btn-view-sc {
        background-color: #7A1C1C;
        color: white;
        border: none;
        padding: 7px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        transition: background 0.2s;
        white-space: nowrap;
    }
    .btn-view-sc:hover {
        background-color: #5c1414;
    }

    .btn-gen-bdf {
        background-color: #0284c7;
        color: white;
        border: none;
        padding: 7px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        transition: background 0.2s;
        white-space: nowrap;
    }
    .btn-gen-bdf:hover {
        background-color: #0369a1;
    }

    /* Modal for Logout */
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

    .modal-action-btns {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 20px;
    }

    .btn-modal-confirm {
        background-color: #ef4444;
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-modal-cancel {
        background-color: #64748b;
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }

    @media (max-width: 768px) {
        .container-custom {
            padding: 0 12px;
            margin: 15px auto;
        }
        .filter-card {
            flex-direction: column;
            align-items: stretch;
        }
        .search-box-wrapper {
            max-width: 100%;
        }
        .user-profile-wrapper {
            float: none;
            display: block;
            margin-bottom: 15px;
        }
    }
</style>

<div class="container-custom">
    
    <!-- Profile Dropdown Menu (දකුණුපසින් My Dashboard, Edit Profile, Logout සමඟ) -->
    <div style="width: 100%; overflow: hidden;">
        <div class="user-profile-wrapper">
            <div class="user-profile-badge" onclick="toggleDropdown()">
                <div style="width: 30px; height: 30px; background: #ffffff; color: #7A1C1C; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                    <?php echo htmlspecialchars($userData['initials']); ?>
                </div>
                <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($userData['name']); ?></div>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; color: #f3e8ff; margin-left: 5px;"></i>
            </div>

            <div id="profileDropdown" class="profile-dropdown">
                <a href="dashboard.php"><i class="fa-solid fa-house"></i> My Dashboard</a>
                <a href="edit_profile.php"><i class="fa-solid fa-user-pen"></i> Edit Profile</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    </div>

    <!-- Main Title -->
    <h2 class="page-main-title">Subject Clerk Forwarded Letters</h2>

    <!-- Subtitle -->
    <div class="section-title-sub">Received Letters for BDF Approval</div>

    <!-- Search Bar -->
    <div class="filter-card">
        <div class="search-box-wrapper">
            <i class="fa-solid fa-search"></i>
            <input type="text" id="searchInput" class="search-input" placeholder="Search by Application No, NIC, or Applicant Name..." onkeyup="filterTable()">
        </div>
        <button class="search-btn" onclick="filterTable()"><i class="fa-solid fa-search"></i> Search</button>
    </div>

    <!-- Letters Table -->
    <div class="table-card">
        <table class="data-table" id="lettersTable">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Application No.</th>
                    <th>Applicant Name</th>
                    <th>NIC</th>
                    <th>Designation</th>
                    <th>Allocated Quarter</th>
                    <th>Letter Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($forwardedLetters)): ?>
                    <tr>
                        <td colspan="9" style="padding: 20px; color: #64748b; font-style: italic;">No forwarded letters found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($forwardedLetters as $index => $letter): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($letter['app_no']); ?></td>
                            <td><?php echo htmlspecialchars($letter['name']); ?></td>
                            <td><?php echo htmlspecialchars($letter['nic']); ?></td>
                            <td><?php echo htmlspecialchars($letter['designation']); ?></td>
                            <td><?php echo htmlspecialchars($letter['quarter']); ?><br><small style="color: #64748b;">(<?php echo htmlspecialchars($letter['quarter_name']); ?>)</small></td>
                            <td><?php echo htmlspecialchars($letter['date']); ?></td>
                            <td><span class="status-badge"><?php echo htmlspecialchars($letter['status']); ?></span></td>
                            <td>
                                <div class="action-btn-group">
                                    <button type="button" class="btn-view-sc" onclick="alert('Viewing Subject Clerk Letter for <?php echo $letter['app_no']; ?>')">
                                        <i class="fa-solid fa-envelope-open-text"></i> View Letter
                                    </button>
                                    <a href="generate_letter_BDF.php?app=<?php echo urlencode($letter['app_no']); ?>" class="btn-gen-bdf">
                                        <i class="fa-solid fa-file-pen"></i> Generate BDF Letter
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="custom-modal">
    <div class="custom-modal-content">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 3rem; color: #7A1C1C; margin-bottom: 15px;"></i>
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to log out from the BDF portal?</p>
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

    // Close dropdown when clicking outside
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

    function filterTable() {
        const input = document.getElementById("searchInput");
        const filter = input.value.toUpperCase();
        const table = document.getElementById("lettersTable");
        const tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            let tdApp = tr[i].getElementsByTagName("td")[1];
            let tdName = tr[i].getElementsByTagName("td")[2];
            let tdNic = tr[i].getElementsByTagName("td")[3];
            
            if (tdApp || tdName || tdNic) {
                let txtApp = tdApp.textContent || tdApp.innerText;
                let txtName = tdName.textContent || tdName.innerText;
                let txtNic = tdNic.textContent || tdNic.innerText;
                
                if (txtApp.toUpperCase().indexOf(filter) > -1 || txtName.toUpperCase().indexOf(filter) > -1 || txtNic.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
</script>

<?php
// Include Admin Footer[cite: 11]
require_once __DIR__ . '/../layouts/admin_footer.php';
?>