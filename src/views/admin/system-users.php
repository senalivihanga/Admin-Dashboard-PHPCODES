<?php
/**
 * System Users Management - Sri Lanka Railways
 * Lists all staff users (role_id BETWEEN 2 AND 7).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/config.php';

$pageTitle = "System Users Management - Sri Lanka Railways";
$hide_admin_extras = true;

$conn = getDBConnection();

// Map role_id -> human label (matches src/helpers/roles.php)
$roleLabels = [
    2 => 'Super Admin',
    3 => 'Immediate Boss',
    4 => 'Personal File',
    5 => 'Subject Clerk',
    6 => 'Branch Officer',
    7 => 'Final Approval',
];

// ---- Handle delete (only staff, i.e. role_id BETWEEN 2 AND 7) ----
if (isset($_GET['delete']) && $_GET['delete'] !== '') {
    $deleteNic = trim((string) $_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM users WHERE nic = ? AND role_id BETWEEN 2 AND 7");
    $stmt->bind_param("s", $deleteNic);
    $stmt->execute();
    $deleted = $stmt->affected_rows;
    $stmt->close();

    $_SESSION['flash_message'] = $deleted
        ? 'System user deleted successfully!'
        : 'User could not be deleted (not found or not a staff role).';

    $conn->close();
    header('Location: ' . baseUrl('admin/system-users.php'));
    exit;
}

// ---- Search ----
$searchQuery = trim($_GET['search'] ?? '');

$sql = "SELECT nic, name, email, computer_number, mobile, role_id, created_at
          FROM users
         WHERE role_id BETWEEN 2 AND 7";

if ($searchQuery !== '') {
    $sql .= " AND (nic LIKE ? OR name LIKE ? OR email LIKE ?)";
    $stmt = $conn->prepare($sql . " ORDER BY role_id ASC, created_at DESC");
    $like = '%' . $searchQuery . '%';
    $stmt->bind_param("sss", $like, $like, $like);
} else {
    $stmt = $conn->prepare($sql . " ORDER BY role_id ASC, created_at DESC");
}

$stmt->execute();
$systemUsers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$flash_message = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

require_once __DIR__ . '/../layouts/admin_header.php';
?>

<!-- Flash toast -->
<?php if (!empty($flash_message)): ?>
    <div id="alertBox" class="fixed top-6 right-6 z-50 bg-white text-slate-800 px-5 py-4 rounded-xl font-semibold shadow-2xl border border-slate-200 border-l-4 border-emerald-500 flex items-center gap-3 transition-all duration-500">
        <i class="fa-solid fa-circle-check text-emerald-500 text-xl"></i>
        <span><?php echo htmlspecialchars($flash_message); ?></span>
    </div>
<?php endif; ?>

<main class="flex-grow max-w-[1450px] mx-auto w-full px-4 sm:px-6 py-8">
    <a href="<?php echo baseUrl('admin/dashboard.php'); ?>" class="inline-flex items-center gap-2 text-[#7A1C1C] font-semibold mb-5 hover:underline">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>

    <h2 class="text-center text-[#7A1C1C] text-2xl md:text-3xl font-bold uppercase mb-6 tracking-wide underline underline-offset-8">
        System Users Management
    </h2>

    <!-- Search / Add -->
<div class="flex justify-between items-center mb-6 flex-wrap gap-4">
    <form method="GET" action="" class="flex gap-2 flex-grow max-w-[480px] w-full">
        <div class="relative flex-grow">
            <input type="text" name="search" id="searchInput"
                   value="<?php echo htmlspecialchars($searchQuery); ?>"
                   placeholder="Search by NIC, Name or Email..."
                   oninput="if (this.value.trim() === '') this.form.submit();"
                   class="w-full px-4 py-2.5 pr-10 border border-slate-300 rounded-lg outline-none text-sm bg-white">

            <?php if ($searchQuery !== ''): ?>
                <button type="button"
                        onclick="this.form.search.value=''; this.form.submit();"
                        title="Clear search"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 bg-transparent border-none cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            <?php endif; ?>
        </div>

        <button type="submit"
                class="bg-[#7A1C1C] hover:bg-[#631616] text-white border-none px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition cursor-pointer">
            <i class="fa-solid fa-magnifying-glass"></i> Search
        </button>
    </form>

    <a href="<?php echo baseUrl('admin/add_system_user.php'); ?>"
       class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg font-semibold text-sm transition inline-flex items-center justify-center gap-2 no-underline shadow-sm">
        <i class="fa-solid fa-user-plus"></i> Add New User
    </a>
</div>

    <!-- Table -->
    <div class="bg-white p-6 rounded-xl shadow-md border-t-4 border-[#7A1C1C] overflow-x-auto">
        <table class="w-full border-collapse text-left min-w-[1300px]">
            <thead>
                <tr>
                    <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">No.</th>
                    <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">NIC Number</th>
                    <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">Computer Number</th>
                    <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">Name</th>
                    <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">Email Address</th>
                    <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">Mobile</th>
                    <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">System Role</th>
                    <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($systemUsers)): ?>
                    <tr>
                        <td colspan="8" class="text-center p-5 text-slate-500">No system users found.</td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($systemUsers as $row): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs"><?php echo $i++; ?></td>
                            <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs font-mono"><?php echo htmlspecialchars($row['nic']); ?></td>
                            <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs"><?php echo htmlspecialchars($row['computer_number'] ?? '-'); ?></td>
                            <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs"><?php echo htmlspecialchars($row['mobile'] ?? '-'); ?></td>
                            <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs font-semibold">
                                <?php echo htmlspecialchars($roleLabels[(int) $row['role_id']] ?? 'Unknown'); ?>
                            </td>
                            <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs">
                                <div class="flex justify-center gap-1.5">
                                    <a href="<?php echo baseUrl('admin/edit_system_user.php') . '?nic=' . urlencode($row['nic']); ?>"
                                       class="px-3 py-1.5 rounded bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center no-underline"
                                       title="Edit"><i class="fa-solid fa-pen"></i></a>
                                    <button type="button"
                                            onclick="promptDelete('<?php echo htmlspecialchars($row['nic'], ENT_QUOTES); ?>')"
                                            class="px-3 py-1.5 rounded bg-red-600 hover:bg-red-700 text-white text-xs font-semibold inline-flex items-center cursor-pointer border-none"
                                            title="Delete"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Delete Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black/50 justify-center items-center z-50 p-4">
    <div class="bg-white p-6 rounded-xl text-center w-full max-w-[350px] shadow-lg">
        <p class="mb-5 text-base text-slate-700 font-semibold">
            <i class="fa-solid fa-triangle-exclamation text-red-600 text-2xl mb-2.5 block"></i>
            Are you sure you want to delete this system user record?
        </p>
        <div class="flex justify-center gap-2.5">
            <a href="#" id="confirmDeleteBtn"
               class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-semibold no-underline inline-block">Yes, Delete</a>
            <button type="button" onclick="closeDeleteModal()"
                    class="px-4 py-2 bg-slate-500 hover:bg-slate-600 text-white rounded font-semibold cursor-pointer border-none">Cancel</button>
        </div>
    </div>
</div>

<script>
    function promptDelete(nic) {
        const modal = document.getElementById('deleteModal');
        document.getElementById('confirmDeleteBtn').href =
            "<?php echo baseUrl('admin/system-users.php'); ?>?delete=" + encodeURIComponent(nic);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    // Auto-hide toast
    setTimeout(() => {
        const alertBox = document.getElementById('alertBox');
        if (alertBox) {
            alertBox.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alertBox.style.opacity = '0';
            alertBox.style.transform = 'translateY(-20px)';
            setTimeout(() => alertBox.remove(), 500);
        }
    }, 4000);
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>