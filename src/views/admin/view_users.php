<?php
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure admin session name exists (middleware normally sets this)
if (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = $_SESSION['user_name'] ?? 'Admin';
}

$admin_name = $_SESSION['admin_name'];

$user = [
    'name' => $admin_name
];

$pageTitle = "Registered Users - Sri Lanka Railways Admin";

// ---------------------------------------------------
// REAL DATABASE QUERIES
// ---------------------------------------------------
$conn = getDBConnection();

// ---------------------------------------------------
// Handle delete request (deletes by NIC, not by int id)
// ---------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $deleteNic = trim((string) $_GET['id']);   // KEEP as string — NIC is varchar

    if ($deleteNic !== '') {
        $delStmt = $conn->prepare("DELETE FROM users WHERE nic = ? AND role_id = 1");
        if ($delStmt) {
            $delStmt->bind_param("s", $deleteNic);
            $delStmt->execute();
            $deleted = $delStmt->affected_rows;
            $delStmt->close();

            $_SESSION['flash_message'] = $deleted
                ? 'User record deleted successfully!'
                : 'User record could not be deleted (not found or not an applicant).';
        } else {
            $_SESSION['flash_message'] = 'Delete failed: ' . $conn->error;
        }
    } else {
        $_SESSION['flash_message'] = 'Invalid delete request.';
    }

    $conn->close();
    header('Location: ' . baseUrl('admin/view_users.php'));
    exit;
}

// ---------------------------------------------------
// Load all applicants (role_id = 1)
// ---------------------------------------------------
$applicants = [];
$result = $conn->query(
    "SELECT nic,
            name,
            email,
            computer_number,
            mobile,
            created_at
       FROM users
      WHERE role_id = 1
      ORDER BY created_at DESC"
);
if ($result) {
    $applicants = $result->fetch_all(MYSQLI_ASSOC);
}
$conn->close();

// Pull and clear the flash message
$flash_message = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

// Hide the admin dashboard title and profile dropdown on inner pages
$hide_admin_extras = true;

// Include Admin Header
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
    <a href="<?php echo baseUrl('admin/dashboard.php'); ?>" class="inline-flex items-center gap-2 text-[#7A1C1C] font-semibold mb-6 hover:underline transition">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>

    <!-- Page Title -->
    <h2 class="text-center text-[#7A1C1C] text-2xl sm:text-3xl font-bold uppercase mb-8 tracking-wide underline underline-offset-8 decoration-2">
        Registered Users List
    </h2>

    <!-- Top Controls -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 bg-white p-4 sm:p-5 rounded-xl shadow-md gap-4">
        <div class="flex gap-2 w-full sm:max-w-[450px]">
            <input type="text" id="searchInput" placeholder="Search by NIC Number or Name..."
                   class="flex-grow px-4 py-2.5 border border-slate-300 rounded-lg outline-none focus:border-[#7A1C1C] text-sm">
            <button class="bg-[#7A1C1C] hover:bg-[#631616] text-white px-5 py-2.5 rounded-lg font-semibold text-sm transition flex items-center gap-2 cursor-pointer"
                    onclick="filterTable()">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
        </div>
        <!-- Add New User Button -->
        <a href="<?php echo baseUrl('admin/add_user.php'); ?>"
           class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white border-none px-4 py-2.5 rounded-lg font-semibold text-sm transition inline-flex items-center justify-center gap-2 no-underline shadow-sm">
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
                <?php if (empty($applicants)): ?>
                    <tr>
                        <td colspan="7" class="p-6 text-center text-slate-500">No registered applicants found.</td>
                    </tr>
                <?php else: ?>
                    <?php $counter = 1; foreach ($applicants as $row): ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3.5 border border-slate-300 text-slate-700 text-center font-medium">
                                <?php echo $counter++; ?>
                            </td>
                            <td class="p-3.5 border border-slate-300 text-slate-700 font-mono">
                                <?php echo htmlspecialchars($row['nic']); ?>
                            </td>
                            <td class="p-3.5 border border-slate-300 text-slate-700">
                                <?php echo htmlspecialchars($row['computer_number'] ?? '-'); ?>
                            </td>
                            <td class="p-3.5 border border-slate-300 text-slate-900 font-medium">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </td>
                            <td class="p-3.5 border border-slate-300 text-blue-600 underline">
                                <?php echo htmlspecialchars($row['email']); ?>
                            </td>
                            <td class="p-3.5 border border-slate-300 text-slate-700">
                                <?php echo htmlspecialchars($row['mobile'] ?? '-'); ?>
                            </td>
                            <td class="p-3.5 border border-slate-300 text-center">
                                <div class="flex gap-1.5 justify-center items-center">
                                    <!-- View Details -->
                                    <a href="<?php echo baseUrl('admin/view_user_details.php') . '?nic=' . urlencode($row['nic']); ?>"
                                       class="p-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-xs font-semibold transition"
                                       title="View Details">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <!-- Edit -->
                                    <a href="<?php echo baseUrl('admin/edit_view_users.php') . '?nic=' . urlencode($row['nic']); ?>"
                                       class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-xs font-semibold transition"
                                       title="Edit Details">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <!-- Delete (passes NIC) -->
                                    <button onclick="openDeleteModal('<?php echo htmlspecialchars($row['nic'], ENT_QUOTES); ?>')"
                                            class="p-2 bg-red-500 hover:bg-red-600 text-white rounded-md text-xs font-semibold transition cursor-pointer"
                                            title="Delete Record">
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
            <button onclick="closeDeleteModal()"
                    class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg font-semibold text-sm transition cursor-pointer">
                Cancel
            </button>
            <a id="confirmDeleteBtn" href="#"
               class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-semibold text-sm transition inline-block">
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
            let tdNic  = tr[i].getElementsByTagName('td')[1]; // NIC column
            let tdName = tr[i].getElementsByTagName('td')[3]; // Name column

            if (tdNic && tdName) {
                let txtNic  = tdNic.textContent  || tdNic.innerText;
                let txtName = tdName.textContent || tdName.innerText;

                if (input === '' ||
                    txtNic.toLowerCase().indexOf(input)  > -1 ||
                    txtName.toLowerCase().indexOf(input) > -1) {
                    tr[i].style.display = '';
                } else {
                    tr[i].style.display = 'none';
                }
            }
        }
    }

    // Clear filter as soon as the input is emptied
    document.getElementById('searchInput').addEventListener('input', function () {
        if (this.value.trim() === '') {
            let tr = document.getElementById('userTable').getElementsByTagName('tr');
            for (let i = 1; i < tr.length; i++) {
                tr[i].style.display = '';
            }
        }
    });

    function openDeleteModal(nic) {
        // Build the delete URL pointing at the correct admin route
        let deleteUrl = "<?php echo baseUrl('admin/view_users.php'); ?>?action=delete&id=" + encodeURIComponent(nic);
        document.getElementById('confirmDeleteBtn').setAttribute('href', deleteUrl);
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').classList.add('flex');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('flex');
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Auto-hide the flash toast after 4 seconds
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
// Include Admin Footer
require_once __DIR__ . '/../layouts/admin_footer.php';
?>