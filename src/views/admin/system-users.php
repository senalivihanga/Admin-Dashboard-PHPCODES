<?php
/**
 * System Users Management Page
 */

// සැසිය දැනටමත් ආරම්භ වී ඇත්දැයි පරීක්ෂා කර බැලීම[cite: 2]
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// පිටු මාතෘකාව සැකසීම[cite: 2]
$pageTitle = "System Users Management - Sri Lanka Railways";

// ඩේටාබේස් සම්බන්ධතාවය (Database Connection)[cite: 2]
$host = 'localhost';
$db   = 'admin_dashboard_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// පරිශීලක දත්ත මකා දැමීම සඳහා (Delete user based on Database ID)[cite: 2]
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    if ($delId > 0) {
        $stmt = $pdo->prepare("DELETE FROM system_users WHERE id = ?");
        $stmt->execute([$delId]);
    }
    header("Location: system-users.php");
    exit();
}

// සෙවුම් පෙරහන ක්‍රියාත්මක කිරීම (Filter users based on NIC search query)[cite: 2]
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($searchQuery !== '') {
    $stmt = $pdo->prepare("SELECT * FROM system_users WHERE nic LIKE ?");
    $stmt->execute(["%$searchQuery%"]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("SELECT * FROM system_users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Admin Header ඇතුළත් කිරීම[cite: 2]
require_once __DIR__ . '/../layouts/admin_header.php';
?>

    <!-- ප්‍රධාන අන්තර්ගත කන්ටේනරය[cite: 2] -->
    <div class="container mx-auto px-4 py-8 max-w-[1450px] flex-grow">
        <!-- ඩෑෂ්බෝඩ් වෙත නැවත යෑමේ සබැඳිය[cite: 2] -->
        <a href="dashboard.php" class="inline-flex items-center gap-2 text-[#7A1C1C] font-semibold mb-5 hover:underline">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
        
        <h2 class="text-center text-[#7A1C1C] text-2xl md:text-3xl font-bold uppercase mb-6 tracking-wide underline underline-offset-8">
            System Users Management
        </h2>

        <!-- ඉහළ ක්‍රියාකාරී තීරුව - සෙවීම සහ එකතු කිරීමේ බොත්තම[cite: 2] -->
        <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
            <form method="GET" action="" class="flex gap-2 flex-grow max-w-[480px] w-full">
                <input type="text" class="flex-grow px-4 py-2.5 border border-slate-300 rounded-lg outline-none text-sm bg-white placeholder-slate-400" name="search" placeholder="Search by NIC Number..." value="<?php echo htmlspecialchars($searchQuery); ?>" onfocus="this.placeholder=''" onblur="this.placeholder='Search by NIC Number...'" oninput="if(this.value.trim() === '') { this.form.submit(); }">
                <button type="submit" class="bg-[#7A1C1C] hover:bg-[#631616] text-white border-none px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition cursor-pointer">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
            </form>
            <!-- Add New User Button updated with view_users table theme color -->
            <a href="add_system_user.php" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white border-none px-4 py-2.5 rounded-lg font-semibold text-sm transition inline-flex items-center justify-center gap-2 no-underline shadow-sm">
                <i class="fa-solid fa-user-plus"></i> Add New User
            </a>
        </div>

        <!-- පරිශීලක වගු කාඩ්පත[cite: 2] -->
        <div class="bg-white p-6 rounded-xl shadow-md border-t-4 border-[#7A1C1C] overflow-x-auto">
            <table class="w-full border-collapse text-left min-w-[1300px]">
                <thead>
                    <tr>
                        <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">No.</th>
                        <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">NIC Number</th>
                        <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">Computer Number</th>
                        <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">Name</th>
                        <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">Designation</th>
                        <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">System Role</th>
                        <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">Current Workplace</th>
                        <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">Email Address</th>
                        <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">Phone Number</th>
                        <th class="p-3.5 border border-slate-300 bg-slate-50 text-slate-800 font-bold text-center text-xs">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $index => $userRecord): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs align-middle"><?php echo $index + 1; ?></td>
                                <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs align-middle"><?php echo htmlspecialchars($userRecord['nic']); ?></td>
                                <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs align-middle"><?php echo htmlspecialchars($userRecord['computer_no']); ?></td>
                                <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs align-middle"><?php echo htmlspecialchars($userRecord['full_name']); ?></td>
                                <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs align-middle"><?php echo htmlspecialchars($userRecord['designation']); ?></td>
                                <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs align-middle"><?php echo htmlspecialchars($userRecord['role']); ?></td>
                                <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs align-middle"><?php echo htmlspecialchars($userRecord['workplace']); ?></td>
                                <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs align-middle"><?php echo htmlspecialchars($userRecord['email']); ?></td>
                                <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs align-middle"><?php echo htmlspecialchars($userRecord['phone']); ?></td>
                                <td class="p-3.5 border border-slate-300 text-slate-700 text-center text-xs align-middle">
                                    <div class="flex justify-center gap-1.5">
                                        <!-- Action buttons with colors matching the first code (View: Purple, Edit: Blue, Delete: Red)[cite: 2] -->
                                        <a href="view_system_user.php?id=<?php echo $userRecord['id']; ?>" class="px-3 py-1.5 rounded bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold inline-flex items-center gap-1 no-underline" title="View User"><i class="fa-solid fa-eye"></i></a>
                                        <a href="edit_system_user.php?id=<?php echo $userRecord['id']; ?>" class="px-3 py-1.5 rounded bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-1 no-underline" title="Edit User"><i class="fa-solid fa-pen"></i></a>
                                        <button type="button" class="px-3 py-1.5 rounded bg-red-600 hover:bg-red-700 text-white text-xs font-semibold inline-flex items-center gap-1 cursor-pointer border-none" onclick="promptDelete(<?php echo $userRecord['id']; ?>)" title="Delete User"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center p-5 text-slate-500">No system users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- මකා දැමීමේ විමසුම් කවුළුව[cite: 2] -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black/50 justify-center items-center z-50 p-4">
        <div class="bg-white p-6 rounded-xl text-center w-full max-w-[350px] shadow-lg">
            <p class="mb-5 text-base text-slate-700 font-semibold">
                <i class="fa-solid fa-triangle-exclamation text-red-600 text-2xl mb-2.5 block"></i> 
                Are you sure you want to delete this system user record?
            </p>
            <div class="flex justify-center gap-2.5">
                <a href="#" id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-semibold no-underline inline-block">Yes, Delete</a>
                <button type="button" class="px-4 py-2 bg-slate-500 hover:bg-slate-600 text-white rounded font-semibold cursor-pointer border-none" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function promptDelete(id) {
            const deleteModal = document.getElementById('deleteModal');
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            confirmBtn.href = "system-users.php?delete=" + id;
            deleteModal.classList.remove('hidden');
            deleteModal.classList.add('flex');
        }

        function closeDeleteModal() {
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.classList.remove('flex');
            deleteModal.classList.add('hidden');
        }
    </script>

<?php
// Admin Footer ඇතුළත් කිරීම[cite: 2]
require_once __DIR__ . '/../layouts/admin_footer.php';
?>