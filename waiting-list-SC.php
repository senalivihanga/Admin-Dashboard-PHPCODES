<?php
/**
 * Waiting Lists - Sri Lanka Railways Admin
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Waiting Lists - Sri Lanka Railways Admin";

$userData = [
    'name' => 'Subject Clerk',
    'role' => 'Sri Lanka Railways - AOT',
    'initials' => 'SC'
];

$waitingListsData = [
    'general' => [
        [
            'nic' => '199812345678',
            'computerNo' => '101',
            'name' => 'A. B. Perera',
            'designation' => 'Clerk',
            'workplace' => 'Colombo Fort',
            'allocatedDate' => '2026-01-10',
            'houseType' => 'None',
            'other' => 'None'
        ],
        [
            'nic' => '199812345648',
            'computerNo' => '102',
            'name' => 'A. B. Perera',
            'designation' => 'Clerk',
            'workplace' => 'Colombo Fort',
            'allocatedDate' => '2026-01-10',
            'houseType' => 'None',
            'other' => 'None'
        ]
    ],
    'guard' => [
        [
            'nic' => '199287654321',
            'computerNo' => '102',
            'name' => 'S. K. Silva',
            'designation' => 'Train Guard',
            'workplace' => 'Maradana',
            'allocatedDate' => '2026-02-14',
            'houseType' => 'Pending',
            'other' => 'None'
        ]
    ],
    'station_master' => [
        [
            'nic' => '198533445566',
            'computerNo' => '103',
            'name' => 'M. N. Fernando',
            'designation' => 'Station Master',
            'workplace' => 'Gampaha',
            'allocatedDate' => '2026-03-01',
            'houseType' => 'Urgent',
            'other' => 'None'
        ]
    ],
    'controller' => [
        [
            'nic' => '199022334455',
            'computerNo' => '104',
            'name' => 'T. G. Rathnayake',
            'designation' => 'Controller',
            'workplace' => 'HQ Colombo',
            'allocatedDate' => '2026-03-10',
            'houseType' => 'None',
            'other' => 'None'
        ]
    ],
    'deputy_commissioner' => [
        [
            'nic' => '198211223344',
            'computerNo' => '105',
            'name' => 'W. M. D. Wickramasinghe',
            'designation' => 'Deputy Commissioner',
            'workplace' => 'Main Office',
            'allocatedDate' => '2026-04-05',
            'houseType' => 'Approved',
            'other' => 'None'
        ]
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
        max-width: 1250px; 
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

    .page-title { 
        text-align: center; 
        color: #7A1C1C; 
        font-size: 1.8rem; 
        margin-bottom: 25px; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        text-decoration: underline; 
        text-underline-offset: 8px; 
        font-weight: 700;
    }

    .tab-container { 
        display: flex; 
        flex-wrap: wrap; 
        gap: 10px; 
        justify-content: center; 
        margin-bottom: 25px; 
    }

    .tab-btn { 
        background-color: #f1f5f9; 
        color: #334155; 
        border: 1px solid #cbd5e1; 
        padding: 10px 18px; 
        border-radius: 8px; 
        cursor: pointer; 
        font-weight: 600; 
        font-size: 0.9rem; 
        transition: all 0.2s ease; 
    }
    .tab-btn:hover { 
        background-color: #e2e8f0; 
    }
    .tab-btn.active { 
        background-color: #7A1C1C; 
        color: white; 
        border-color: #7A1C1C; 
    }

    .table-card { 
        background: #ffffff; 
        padding: 25px; 
        border-radius: 12px; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border-top: 5px solid #7A1C1C; 
        display: none; 
        overflow-x: auto; 
    }
    .table-card.active-table { 
        display: block; 
    }

    .search-card { 
        background: #ffffff; 
        padding: 16px 20px; 
        margin-bottom: 20px; 
        border: 1px solid #e2e8f0; 
        border-radius: 10px; 
        box-shadow: 0 4px 12px rgba(0,0,0,0.04); 
    }

    .section-header-bar { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 20px; 
        flex-wrap: wrap; 
        gap: 12px; 
    }

    .section-subtitle { 
        color: #7A1C1C; 
        font-size: 1.3rem; 
        font-weight: 700; 
        margin: 0; 
    }

    .list-controls { 
        display: flex; 
        align-items: center; 
        gap: 10px; 
        width: 100%;
        max-width: 450px;
    }

    .list-search-input { 
        flex: 1;
        padding: 9px 11px; 
        border: 1px solid #cbd5e1; 
        border-radius: 6px; 
        font-size: 0.9rem; 
        outline: none;
    }
    .list-search-input:focus { 
        border-color: #7A1C1C; 
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1); 
    }

    .search-btn { 
        background: #7A1C1C; 
        color: white; 
        border: 0; 
        padding: 9px 15px; 
        border-radius: 6px; 
        font-weight: 600; 
        cursor: pointer; 
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .search-btn:hover { 
        background: #5c1414; 
    }

    .waiting-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: left; 
        min-width: 900px; 
    }
    .waiting-table th, .waiting-table td { 
        padding: 12px 10px; 
        border: 1px solid #cbd5e1; 
        vertical-align: middle; 
        font-size: 0.85rem; 
        text-align: center; 
    }
    .waiting-table th { 
        background-color: #f8fafc; 
        color: #1e293b; 
        font-weight: 700; 
    }
    .waiting-table tbody td { 
        color: #334155; 
        font-size: 0.85rem; 
        font-weight: 400; 
    }
    .waiting-table td:nth-child(4), .waiting-table td:nth-child(7) { 
        text-align: left; 
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
        .container {
            margin: 15px auto;
            padding: 0 10px;
        }
        .list-controls {
            max-width: 100%;
        }
    }
</style>

<div class="container">
    <?php if (isset($_SESSION['admin_name']) || (isset($_SESSION['role']) &&$_SESSION['role'] === 'admin')): ?>
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
                <a href="assign-quarters-SC.php"><i class="fa-solid fa-arrow-left"></i> Back to Assign Quarters Management</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="page-title">Waiting Lists</h2>

    <!-- Tabs -->
    <div class="tab-container">
        <button class="tab-btn active" onclick="showTable('general', event)">1. General List</button>
        <button class="tab-btn" onclick="showTable('guard', event)">2. Guard List</button>
        <button class="tab-btn" onclick="showTable('station_master', event)">3. Station Master List</button>
        <button class="tab-btn" onclick="showTable('controller', event)">4. Controller List</button>
        <button class="tab-btn" onclick="showTable('deputy_commissioner', event)">5. Deputy Superintendent (Transportation) Colombo List</button>
    </div>

    <div class="search-card">
        <div class="list-controls">
            <input type="search" id="waitingListSearchInput" class="list-search-input" placeholder="Search by NIC or name..." aria-label="Search active waiting list" onkeydown="if (event.key === 'Enter') filterActiveWaitingList()" oninput="if (this.value.trim() === '') filterActiveWaitingList()">
            <button type="button" class="search-btn" onclick="filterActiveWaitingList()"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
    </div>

    <!-- 1. General List Table -->
    <div id="general" class="table-card active-table">
        <div class="section-header-bar">
            <h3 class="section-subtitle">1. General List</h3>
        </div>
        <div class="table-responsive">
            <table class="waiting-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIC</th>
                        <th>Computer No.</th>
                        <th>Applicant's Name</th>
                        <th>Designation</th>
                        <th>Workplace</th>
                        <th>Allocated Date</th>
                        <th>Allocated House Type</th>
                        <th>Other</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($waitingListsData['general'])): ?>
                        <tr><td colspan="9" style="text-align: center; color: #64748b; padding: 25px;">No records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($waitingListsData['general'] as $index =>$row): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($row['nic']); ?></td>
                                <td><?php echo htmlspecialchars($row['computerNo']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['workplace']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['allocatedDate']); ?></td>
                                <td><?php echo htmlspecialchars($row['houseType']); ?></td>
                                <td><?php echo htmlspecialchars($row['other']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Guard List Table -->
    <div id="guard" class="table-card">
        <div class="section-header-bar">
            <h3 class="section-subtitle">2. Guard List</h3>
        </div>
        <div class="table-responsive">
            <table class="waiting-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIC</th>
                        <th>Computer No.</th>
                        <th>Applicant's Name</th>
                        <th>Designation</th>
                        <th>Workplace</th>
                        <th>Allocated Date</th>
                        <th>Allocated House Type</th>
                        <th>Other</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($waitingListsData['guard'])): ?>
                        <tr><td colspan="9" style="text-align: center; color: #64748b; padding: 25px;">No records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($waitingListsData['guard'] as $index =>$row): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($row['nic']); ?></td>
                                <td><?php echo htmlspecialchars($row['computerNo']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['workplace']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['allocatedDate']); ?></td>
                                <td><?php echo htmlspecialchars($row['houseType']); ?></td>
                                <td><?php echo htmlspecialchars($row['other']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Station Master List Table -->
    <div id="station_master" class="table-card">
        <div class="section-header-bar">
            <h3 class="section-subtitle">3. Station Master List</h3>
        </div>
        <div class="table-responsive">
            <table class="waiting-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIC</th>
                        <th>Computer No.</th>
                        <th>Applicant's Name</th>
                        <th>Designation</th>
                        <th>Workplace</th>
                        <th>Allocated Date</th>
                        <th>Allocated House Type</th>
                        <th>Other</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($waitingListsData['station_master'])): ?>
                        <tr><td colspan="9" style="text-align: center; color: #64748b; padding: 25px;">No records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($waitingListsData['station_master'] as $index =>$row): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($row['nic']); ?></td>
                                <td><?php echo htmlspecialchars($row['computerNo']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['workplace']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['allocatedDate']); ?></td>
                                <td><?php echo htmlspecialchars($row['houseType']); ?></td>
                                <td><?php echo htmlspecialchars($row['other']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. Controller List Table -->
    <div id="controller" class="table-card">
        <div class="section-header-bar">
            <h3 class="section-subtitle">4. Controller List</h3>
        </div>
        <div class="table-responsive">
            <table class="waiting-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIC</th>
                        <th>Computer No.</th>
                        <th>Applicant's Name</th>
                        <th>Designation</th>
                        <th>Workplace</th>
                        <th>Allocated Date</th>
                        <th>Allocated House Type</th>
                        <th>Other</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($waitingListsData['controller'])): ?>
                        <tr><td colspan="9" style="text-align: center; color: #64748b; padding: 25px;">No records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($waitingListsData['controller'] as $index =>$row): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($row['nic']); ?></td>
                                <td><?php echo htmlspecialchars($row['computerNo']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['workplace']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['allocatedDate']); ?></td>
                                <td><?php echo htmlspecialchars($row['houseType']); ?></td>
                                <td><?php echo htmlspecialchars($row['other']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. Deputy Commissioner List Table -->
    <div id="deputy_commissioner" class="table-card">
        <div class="section-header-bar">
            <h3 class="section-subtitle">5. Deputy Superintendent (Transportation) Colombo List</h3>
        </div>
        <div class="table-responsive">
            <table class="waiting-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIC</th>
                        <th>Computer No.</th>
                        <th>Applicant's Name</th>
                        <th>Designation</th>
                        <th>Workplace</th>
                        <th>Allocated Date</th>
                        <th>Allocated House Type</th>
                        <th>Other</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($waitingListsData['deputy_commissioner'])): ?>
                        <tr><td colspan="9" style="text-align: center; color: #64748b; padding: 25px;">No records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($waitingListsData['deputy_commissioner'] as $index =>$row): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($row['nic']); ?></td>
                                <td><?php echo htmlspecialchars($row['computerNo']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['workplace']); ?></td>
                                <td style="text-align: center;"><?php echo htmlspecialchars($row['allocatedDate']); ?></td>
                                <td><?php echo htmlspecialchars($row['houseType']); ?></td>
                                <td><?php echo htmlspecialchars($row['other']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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

    function showTable(tableId, event) {
        document.querySelectorAll('.table-card').forEach(card => card.classList.remove('active-table'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById(tableId).classList.add('active-table');
        event.currentTarget.classList.add('active');
        filterWaitingList(tableId);
    }

    function filterActiveWaitingList() {
        const activeTable = document.querySelector('.table-card.active-table');
        if (activeTable) {
            filterWaitingList(activeTable.id);
        }
    }

    function filterWaitingList(tableId) {
        const query = document.getElementById('waitingListSearchInput').value.trim().toLowerCase();
        const rows = document.querySelectorAll(`#${tableId} tbody tr`);

        rows.forEach(row => {
            if (row.cells.length < 2) return;
            const nic = row.cells[1]?.textContent.toLowerCase() || '';
            const applicantName = row.cells[3]?.textContent.toLowerCase() || '';
            row.style.display = query === '' || nic.includes(query) || applicantName.includes(query) ? '' : 'none';
        });
    }
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>