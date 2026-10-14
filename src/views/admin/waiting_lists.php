<?php
/**
 * Waiting Lists - Sri Lanka Railways Admin
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Waiting Lists - Sri Lanka Railways Admin";

// Sample data structure for the lists
$lists_data = [
    'general' => [
        ['id' => 1, 'nic' => '199812345678', 'computer_no' => '101', 'name' => 'A. B. Perera', 'designation' => 'Clerk', 'workplace' => 'Colombo Fort', 'allocated_date' => '2026-01-10', 'quarter_type' => 'None', 'other' => 'None'],
        ['id' => 2, 'nic' => '199287654321', 'computer_no' => '102', 'name' => 'S. K. Silva', 'designation' => 'Train Guard', 'workplace' => 'Maradana', 'allocated_date' => '2026-01-10', 'quarter_type' => 'None', 'other' => 'None']
    ],
    'guard' => [
        ['id' => 1, 'nic' => '199287654321', 'computer_no' => '102', 'name' => 'S. K. Silva', 'designation' => 'Train Guard', 'workplace' => 'Maradana', 'allocated_date' => '2026-02-14', 'quarter_type' => 'Pending', 'other' => 'None']
    ],
    'station_master' => [
        ['id' => 1, 'nic' => '198533445566', 'computer_no' => '103', 'name' => 'M. N. Fernando', 'designation' => 'Station Master', 'workplace' => 'Gampaha', 'allocated_date' => '2026-03-01', 'quarter_type' => 'Urgent', 'other' => 'None'],
        ['id' => 2, 'nic' => '1985334455587', 'computer_no' => '103', 'name' => 'M. N. Fernando', 'designation' => 'Station Master', 'workplace' => 'Maradana', 'allocated_date' => '2026-03-01', 'quarter_type' => 'Urgent', 'other' => 'None']
    ],
    'controller' => [
        ['id' => 1, 'nic' => '199022334455', 'computer_no' => '104', 'name' => 'T. G. Rathnayake', 'designation' => 'Controller', 'workplace' => 'HQ Colombo', 'allocated_date' => '2026-03-10', 'quarter_type' => 'None', 'other' => 'None']
    ],
    'deputy_commissioner' => [
        ['id' => 1, 'nic' => '198211223344', 'computer_no' => '105', 'name' => 'W. M. D. Wickramasinghe', 'designation' => 'Deputy Commissioner', 'workplace' => 'Main Office', 'allocated_date' => '2026-04-05', 'quarter_type' => 'Approved', 'other' => 'None']
    ]
];

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';$message = '';

// Handle delete action via PHP if requested
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['list']) && isset($_GET['id'])) {$message = "Applicant entry deleted successfully!";
}

// Handle Search Filter
$searchQuery = trim($_GET['search'] ?? '');
if (!empty($searchQuery)) {
    foreach ($lists_data as$key => $rows) {$lists_data[$key] = array_values(array_filter($rows, function($item) use ($searchQuery) {
            return stripos($item['nic'],$searchQuery) !== false || stripos($item['name'],$searchQuery) !== false;
        }));
    }
}

// Header include
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

    .container { max-width: 1250px; margin: 20px auto; padding: 0 15px; box-sizing: border-box; }
    .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--primary-color); text-decoration: none; font-weight: 600; margin-bottom: 15px; font-size: 0.95rem; }
    .back-link:hover { text-decoration: underline; }
    
    .page-title { 
        text-align: center; 
        color: var(--primary-color); 
        font-size: 1.8rem; 
        margin-bottom: 20px; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        text-decoration: underline; 
        text-underline-offset: 6px; 
        font-weight: 700;
    }
    
    /* Tab Styles */
    .tab-container { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-bottom: 20px; }
    .tab-btn { background-color: #f1f5f9; color: #334155; border: 1px solid var(--border-color); padding: 8px 14px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: all 0.2s ease; text-decoration: none; display: inline-block; text-align: center; }
    .tab-btn:hover { background-color: #e2e8f0; }
    .tab-btn.active { background-color: var(--primary-color); color: white; border-color: var(--primary-color); }

    /* Inline Section Header & Search Styles */
    .section-header-bar { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 15px; 
        flex-wrap: wrap; 
        gap: 15px; 
    }
    
    .section-subtitle { 
        color: var(--primary-color); 
        font-size: 1.2rem; 
        font-weight: 700; 
        margin: 0; 
    }

    .inline-search-form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-input-container {
        position: relative;
        width: 260px;
    }

    .search-input { 
        width: 100%;
        padding: 8px 32px 8px 12px; 
        border-radius: 6px; 
        border: 1px solid var(--border-color); 
        font-size: 0.85rem; 
        outline: none; 
        box-sizing: border-box;
    }
    
    .search-input:focus { 
        border-color: var(--primary-color); 
        box-shadow: 0 0 0 3px rgba(122, 28, 28, 0.1); 
    }
    
    .search-clear-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }
    
    .search-clear-icon:hover {
        color: var(--text-main);
    }

    .search-btn { 
        background-color: var(--primary-color); 
        color: white; 
        border: none; 
        padding: 8px 16px; 
        border-radius: 6px; 
        font-weight: 600; 
        cursor: pointer; 
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        font-size: 0.85rem;
        text-decoration: none; 
        transition: background 0.2s; 
    }
    .search-btn:hover { 
        background-color: var(--primary-dark); 
    }

    .add-btn { 
        padding: 8px 16px; 
        border-radius: 6px; 
        font-weight: 600; 
        cursor: pointer; 
        font-size: 0.85rem; 
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        text-decoration: none; 
        border: none; 
        white-space: nowrap; 
        background-color: #10b981; 
        color: white; 
        transition: background-color 0.2s;
    }
    .add-btn:hover { background-color: #059669; }

    /* Table Card */
    .table-card { background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 5px solid var(--primary-color); display: none; overflow-x: auto; margin-bottom: 30px; }
    .table-card.active-table { display: block; }

    /* Responsive Table */
    .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .waiting-table { width: 100%; border-collapse: collapse; text-align: left; min-width: 850px; }
    .waiting-table th, .waiting-table td { padding: 10px 8px; border: 1px solid var(--border-color); vertical-align: middle; font-size: 0.85rem; text-align: center; }
    .waiting-table th { background-color: #f8fafc; color: var(--text-main); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; }
    .waiting-table td { color: #334155; }
    
    .action-btn { border: none; width: 30px; height: 30px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem; margin: 2px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: background-color 0.2s; }
    .btn-edit { background-color: #2563eb; color: white; }
    .btn-edit:hover { background-color: #1d4ed8; }
    .btn-delete { background-color: #dc2626; color: white; }
    .btn-delete:hover { background-color: #b91c1c; }

    /* Modal & Toast */
    .custom-msg-box { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); display: none; align-items: center; justify-content: center; z-index: 999; padding: 15px; }
    .custom-msg-card { background: #fff; border-radius: 16px; width: min(92vw, 400px); padding: 20px; box-shadow: 0 12px 30px rgba(0,0,0,0.18); text-align: center; }
    .custom-msg-card p { color: #1f2937; font-size: 1rem; line-height: 1.5; margin-bottom: 15px; }
    .custom-msg-card i { display: block; color: #dc2626; font-size: 1.8rem; margin-bottom: 8px; }
    .msg-btn-group { display: flex; justify-content: center; gap: 10px; }
    .msg-btn { border: none; border-radius: 8px; padding: 8px 16px; font-weight: 700; cursor: pointer; font-size: 0.9rem; text-decoration: none; display: inline-block; }
    .msg-btn-yes { background: #dc2626; color: white; }
    .msg-btn-no { background: #e2e8f0; color: #1f2937; }

    .system-toast { position: fixed; right: 20px; top: 20px; background: #f0fdf4; color: #166534; border: 1px solid #86efac; border-left: 6px solid #22c55e; padding: 12px 16px; border-radius: 10px; box-shadow: 0 10px 25px rgba(34, 197, 94, 0.15); display: none; z-index: 1000; font-weight: 700; align-items: center; gap: 8px; max-width: 350px; font-size: 0.9rem; }
    .system-toast i { color: #16a34a; font-size: 1rem; }

    /* Mobile Responsive Adjustments */
    @media (max-width: 768px) {
        .container { margin: 10px auto; padding: 0 10px; }
        .page-title { font-size: 1.4rem; margin-bottom: 15px; }
        .section-header-bar { flex-direction: column; align-items: stretch; }
        .inline-search-form { width: 100%; flex-direction: column; align-items: stretch; }
        .search-input-container { width: 100%; }
        .search-btn, .add-btn { width: 100%; justify-content: center; }
        .tab-container { gap: 6px; }
        .tab-btn { padding: 6px 10px; font-size: 0.78rem; flex: 1 1 calc(50% - 6px); text-align: center; }
    }
</style>

<!-- Delete Confirmation Modal -->
<div class="custom-msg-box" id="deleteModal" role="dialog" aria-modal="true">
    <div class="custom-msg-card">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <p>Are you sure you want to delete this applicant entry?</p>
        <div class="msg-btn-group">
            <a href="#" id="confirmDeleteBtn" class="msg-btn msg-btn-yes">Delete</a>
            <button type="button" class="msg-btn msg-btn-no" onclick="closeDeleteModal()">Cancel</button>
        </div>
    </div>
</div>

<?php if(!empty($message)): ?>
<div id="systemToast" class="system-toast" style="display: flex;"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($message); ?></div>
<script>
    setTimeout(() => {
        document.getElementById('systemToast').style.display = 'none';
    }, 3000);
</script>
<?php endif; ?>

<main class="container">
    <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2 class="page-title">Waiting Lists</h2>

    <!-- Tabs -->
    <div class="tab-container">
        <?php 
        $tabs = [
            'general' => '1. General List',
            'guard' => '2. Guard List',
            'station_master' => '3. Station Master List',
            'controller' => '4. Controller List',
            'deputy_commissioner' => '5. Deputy Superintendent ( Transportation ) Colombo List'
        ];
        foreach($tabs as$key => $label):$isActive = ($active_tab ==$key) ? 'active' : '';
        ?>
            <button type="button" class="tab-btn <?php echo $isActive; ?>" onclick="showTable('<?php echo $key; ?>', event)"><?php echo $label; ?></button>
        <?php endforeach; ?>
    </div>

    <!-- Tables Loop -->
    <?php foreach($lists_data as$list_key => $rows):$isCardActive = ($active_tab ==$list_key) ? 'active-table' : '';
    ?>
    <div id="<?php echo $list_key; ?>" class="table-card <?php echo $isCardActive; ?>">
        
        <!-- Section Header Bar with Inline Search & Add Button -->
        <div class="section-header-bar">
            <h3 class="section-subtitle"><?php echo $tabs[$list_key]; ?></h3>
            
            <form method="GET" action="" class="inline-search-form">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($list_key); ?>">
                <div class="search-input-container">
                    <input type="text" name="search" class="search-input" placeholder="Search by NIC or name" value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <?php if (!empty($searchQuery)): ?>
                        <a href="?tab=<?php echo htmlspecialchars($list_key); ?>" class="search-clear-icon" title="Clear Search"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </div>
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                <a href="add_applicant.php?list=<?php echo $list_key; ?>" class="add-btn"><i class="fa-solid fa-plus"></i> Add Applicant</a>
            </form>
        </div>

        <div class="table-responsive">
            <table class="waiting-table" id="<?php echo $list_key; ?>Table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIC</th>
                        <th>Computer No.</th>
                        <th>Applicant's Name</th>
                        <th>Designation</th>
                        <th>Workplace</th>
                        <th>Allocated Date</th>
                        <th>Allocated Quarter Type</th>
                        <th>Other</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($rows)): ?>
                        <tr><td colspan="10" style="text-align: center; padding: 15px;">No records found.</td></tr>
                    <?php else: ?>
                        <?php foreach($rows as $index =>$row): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($row['nic']); ?></td>
                            <td><?php echo htmlspecialchars($row['computer_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['designation']); ?></td>
                            <td><?php echo htmlspecialchars($row['workplace']); ?></td>
                            <td><?php echo htmlspecialchars($row['allocated_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['quarter_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['other']); ?></td>
                            <td>
                                <a href="edit_applicant.php?list=<?php echo $list_key; ?>&id=<?php echo$row['id']; ?>" class="action-btn btn-edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                <button type="button" class="action-btn btn-delete" onclick="openDeleteModal('<?php echo $list_key; ?>', '<?php echo$row['id']; ?>')" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
</main>

<script>
    function showTable(tableId, event) {
        document.querySelectorAll('.table-card').forEach(card => card.classList.remove('active-table'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById(tableId).classList.add('active-table');
        event.currentTarget.classList.add('active');
    }

    function openDeleteModal(listKey, id) {
        const deleteUrl = `?tab=${listKey}&action=delete&list=${listKey}&id=${id}`;
        document.getElementById('confirmDeleteBtn').setAttribute('href', deleteUrl);
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }
</script>

<?php
// Footer include
require_once __DIR__ . '/../layouts/admin_footer.php';
?>