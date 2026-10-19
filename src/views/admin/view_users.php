<?php
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mock admin session if not set
if (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = 'Admin Name';
}

$admin_name = $_SESSION['admin_name'];

$user = [
    'name' => $admin_name
];

$pageTitle = "Registered Users - Sri Lanka Railways Admin";

// Initialize mock users data in session
if (!isset($_SESSION['system_users_data_v2'])) {
    $_SESSION['system_users_data_v2'] = [
        [
            'id' => 0,
            'fullName' => 'A. B. Perera',
            'nic' => '199812345678',
            'email' => 'abperera@railway.gov.lk',
            'computerNo' => 'COMP-405',
            'phone' => '0771234567'
        ],
        [
            'id' => 1,
            'fullName' => 'S. K. Silva',
            'nic' => '199287654321',
            'email' => 'sksilva@railway.gov.lk',
            'computerNo' => 'COMP-406',
            'phone' => '0719876543'
        ],
        [
            'id' => 2,
            'fullName' => 'N. M. Fernando',
            'nic' => '199567890123',
            'email' => 'nmfernando@railway.gov.lk',
            'computerNo' => 'COMP-407',
            'phone' => '0765432109'
        ],
        [
            'id' => 3,
            'fullName' => 'P. R. Wickramasinghe',
            'nic' => '199034567890',
            'email' => 'prwickramasinghe@railway.gov.lk',
            'computerNo' => 'COMP-408',
            'phone' => '0781234567'
        ],
        [
            'id' => 4,
            'fullName' => 'D. K. Jayasinghe',
            'nic' => '199456789012',
            'email' => 'dkjayasinghe@railway.gov.lk',
            'computerNo' => 'COMP-409',
            'phone' => '0759876543'
        ],
        [
            'id' => 5,
            'fullName' => 'H. M. Bandara',
            'nic' => '199678901234',
            'email' => 'hmbandara@railway.gov.lk',
            'computerNo' => 'COMP-410',
            'phone' => '0701234567'
        ]
    ];
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    foreach ($_SESSION['system_users_data_v2'] as $key => $user_item) {
        if ($user_item['id'] === $delete_id) {
            unset($_SESSION['system_users_data_v2'][$key]);
            // Re-index array keys so numbering remains consistent
            $_SESSION['system_users_data_v2'] = array_values($_SESSION['system_users_data_v2']);
            $_SESSION['flash_message'] = "User record deleted successfully!";
            break;
        }
    }
    header("Location: view_users.php");
    exit;
}

$flash_message = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

// Set flag to hide Admin Dashboard title and profile dropdown in inner pages
$hide_admin_extras = true;

// Include Admin Specific Header
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<!-- Floating Popup Toast Notification -->
<?php if (!empty($flash_message)): ?>
    <div id="alertBox" class="fixed top-6 right-6 z-50 bg-white text-slate-800 px-5 py-4 rounded-xl font-semibold shadow-2xl border border-slate-200 border-l-4 border-emerald-500 flex items-center gap-3 transition-all duration-500">
        <i class="fa-solid fa-circle-check text-emerald-500 text-xl"></i> 
        <span><?php echo htmlspecialchars($flash_message); ?></span>
    </div>
<?php endif; ?>

<!-- Main Content Area -->
<main class="flex-grow max-w-[1200px] mx-auto w-full px-4 sm:px-6 py-10">
        <!-- Back Link -->
        <a href="dashboard.php" class="inline-flex items-center gap-2 text-[#7A1C1C] font-semibold mb-6 hover:underline transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>

        <!-- Page Title -->
        <h2 class="text-center text-[#7A1C1C] text-2xl sm:text-3xl font-bold uppercase mb-8 tracking-wide underline underline-offset-8 decoration-2">
            Registered Users List
        </h2>

        <!-- Top Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 bg-white p-4 sm:p-5 rounded-xl shadow-md gap-4">
            <div class="flex gap-2 w-full sm:max-w-[450px]">
                <input type="text" id="searchInput" placeholder="Search by NIC Number or Name..." class="flex-grow px-4 py-2.5 border border-slate-300 rounded-lg outline-none focus:border-[#7A1C1C] text-sm">
                <button class="bg-[#7A1C1C] hover:bg-[#631616] text-white px-5 py-2.5 rounded-lg font-semibold text-sm transition flex items-center gap-2 cursor-pointer" onclick="filterTable()">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
            </div>
            <!-- Add New User Button -->
            <a href="add_user.php" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white border-none px-4 py-2.5 rounded-lg font-semibold text-sm transition inline-flex items-center justify-center gap-2 no-underline shadow-sm">
                <i class="fa-solid fa-user-plus"></i> Add New User
            </a>
        </div>

        <!-- Table Card -->
        <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-xl border-t-[5px] border-[#7A1C1C] overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm" id="userTable">
                <thead>
                    <tr class="bg-slate-50 text-slate-800">
                        <th class="p-3.5 border border-slate-300 font-bold text-center w-[60px]">No.</th>
                        <th class="p-3.5 border border-slate-300 font-bold text-center">NIC Number</th>
                        <th class="p-3.5 border border-slate-300 font-bold text-center">Computer Number</th>
                        <th class="p-3.5 border border-slate-300 font-bold text-center">Name</th>
                        <th class="p-3.5 border border-slate-300 font-bold text-center">Email Address</th>
                        <th class="p-3.5 border border-slate-300 font-bold text-center">Mobile Number</th>
                        <th class="p-3.5 border border-slate-300 font-bold text-center w-[150px]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (empty($_SESSION['system_users_data_v2'])): ?>
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-500">No registered users found.</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $counter = 1;
                        foreach ($_SESSION['system_users_data_v2'] as $user_row): 
                        ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3.5 border border-slate-300 text-slate-700 text-center font-medium"><?php echo $counter++; ?></td>
                                <td class="p-3.5 border border-slate-300 text-slate-700 font-mono"><?php echo htmlspecialchars($user_row['nic']); ?></td>
                                <td class="p-3.5 border border-slate-300 text-slate-700"><?php echo htmlspecialchars($user_row['computerNo']); ?></td>
                                <td class="p-3.5 border border-slate-300 text-slate-900 font-medium"><?php echo htmlspecialchars($user_row['fullName']); ?></td>
                                <td class="p-3.5 border border-slate-300 text-blue-600 underline"><?php echo htmlspecialchars($user_row['email']); ?></td>
                                <td class="p-3.5 border border-slate-300 text-slate-700"><?php echo htmlspecialchars($user_row['phone']); ?></td>
                                <td class="p-3.5 border border-slate-300 text-center">
                                    <div class="flex gap-1.5 justify-center items-center">
                                        <!-- View Details Link with ID -->
                                        <a href="view_user_details.php?id=<?php echo $user_row['id']; ?>" class="p-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-xs font-semibold transition" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="edit_view_users.php?id=<?php echo $user_row['id']; ?>" class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-xs font-semibold transition" title="Edit Details">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <button onclick="openDeleteModal(<?php echo $user_row['id']; ?>)" class="p-2 bg-red-500 hover:bg-red-600 text-white rounded-md text-xs font-semibold transition cursor-pointer" title="Delete Record">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
</main>

    <!-- Custom Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black/50 hidden justify-center items-center z-50 px-4">
        <div class="bg-white p-6 sm:p-8 rounded-2xl text-center max-w-sm w-full shadow-2xl">
            <i class="fa-solid fa-triangle-exclamation text-red-500 text-4xl mb-4"></i>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Are you sure?</h3>
            <p class="text-slate-600 text-sm mb-6">Do you really want to delete this user record?</p>
            <div class="flex gap-3 justify-center">
                <button onclick="closeDeleteModal()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg font-semibold text-sm transition cursor-pointer">
                    Cancel
                </button>
                <a id="confirmDeleteBtn" href="#" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-semibold text-sm transition inline-block">
                    Delete
                </a>
            </div>
        </div>
    </div>

    <!-- JavaScript Functions -->
    <script>
        function filterTable() {
            let input = document.getElementById('searchInput').value.toLowerCase().trim();
            let tr = document.getElementById('userTable').getElementsByTagName('tr');
            
            for (let i = 1; i < tr.length; i++) {
                let tdNic = tr[i].getElementsByTagName('td')[1]; // NIC Number index
                let tdName = tr[i].getElementsByTagName('td')[3]; // Name index
                if (tdNic && tdName) {
                    let txtNic = tdNic.textContent || tdNic.innerText;
                    let txtName = tdName.textContent || tdName.innerText;
                    
                    if (input === "" || txtNic.toLowerCase().indexOf(input) > -1 || txtName.toLowerCase().indexOf(input) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        document.getElementById('searchInput').addEventListener('input', function() {
            if (this.value.trim() === "") {
                let tr = document.getElementById('userTable').getElementsByTagName('tr');
                for (let i = 1; i < tr.length; i++) {
                    tr[i].style.display = "";
                }
            }
        });

        function openDeleteModal(userId) {
            let deleteUrl = "?action=delete&id=" + userId;
            document.getElementById('confirmDeleteBtn').setAttribute('href', deleteUrl);
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('flex');
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Auto hide alert toast after 4 seconds smoothly
        setTimeout(() => {
            let alertBox = document.getElementById('alertBox');
            if (alertBox) {
                alertBox.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                alertBox.style.opacity = '0';
                alertBox.style.transform = 'translateY(-20px)';
                setTimeout(() => alertBox.remove(), 500);
            }
        }, 4000);
    </script>

<?php
// Include Admin Specific Footer
require_once __DIR__ . '/../layouts/admin_footer.php';
?>