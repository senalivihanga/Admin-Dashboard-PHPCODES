<?php
/**
 * Edit System User Details Page
 */

// සැසිය දැනටමත් ආරම්භ වී ඇත්දැයි පරීක්ෂා කර බැලීම[cite: 19]
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// පිටු මාතෘකාව සැකසීම[cite: 19]
$pageTitle = "Edit System User Details - Sri Lanka Railways";

// ඩේටාබේස් සම්බන්ධතාවය (Database Connection)[cite: 19]
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

$successMessage = "";
$errorMessage = "";

// URL එකෙන් 'id' ලබා ගැනීම (Get ID from URL)[cite: 19]
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: system-users.php");
    exit();
}

// ෆෝම් එක සබ්මිට් වූ විට ඩේටා යාවත්කාලීන කිරීම (Handle Form Submission & Update Data)[cite: 19]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nic         = trim($_POST['nic'] ?? '');
    $computer_no = trim($_POST['computer_no'] ?? '');
    $fullName    = trim($_POST['full_name'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $role        = trim($_POST['role'] ?? '');
    $workplace   = trim($_POST['workplace'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $password    = $_POST['password'] ?? '';

    // නව පාස්වර්ඩ් එකක් ලබා දී ඇත්දැයි පරීක්ෂා කිරීම[cite: 19]
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE system_users SET nic = ?, computer_no = ?, full_name = ?, designation = ?, role = ?, workplace = ?, email = ?, phone = ?, password = ? WHERE id = ?");
        $success = $stmt->execute([$nic, $computer_no, $fullName, $designation, $role, $workplace, $email, $phone, $hashedPassword, $id]);
    } else {
        // පාස්වර්ඩ් එක හිස්ව තැබුවහොත් පැරණි පාස්වර්ඩ් එක එලෙසම තබා අනෙක් දත්ත පමණක් යාවත්කාලීන කිරීම[cite: 19]
        $stmt = $pdo->prepare("UPDATE system_users SET nic = ?, computer_no = ?, full_name = ?, designation = ?, role = ?, workplace = ?, email = ?, phone = ? WHERE id = ?");
        $success = $stmt->execute([$nic, $computer_no, $fullName, $designation, $role, $workplace, $email, $phone, $id]);
    }

    // යාවත්කාලීන වීම සාර්ථක නම් කෙලින්ම system-users.php වෙත යැවීම[cite: 19]
    if ($success) {
        header("Location: system-users.php");
        exit();
    } else {
        $errorMessage = "Failed to update system user details.";
    }
}

// ඩේටාබේස් එකෙන් අදාළ පරිශීලක දත්ත ලබා ගැනීම[cite: 19]
$stmt = $pdo->prepare("SELECT * FROM system_users WHERE id = ?");
$stmt->execute([$id]);
$systemUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$systemUser) {
    header("Location: system-users.php");
    exit();
}

// Admin Header ඇතුළත් කිරීම[cite: 19]
require_once __DIR__ . '/../layouts/admin_header.php';
?>

    <!-- ප්‍රධාන අන්තර්ගත කන්ටේනරය -->
    <div class="container mx-auto px-4 py-8 max-w-4xl flex-grow">
        <!-- පරිශීලක ලැයිස්තුව වෙත ආපසු යාමේ සබැඳිය (Back Link)[cite: 19] -->
        <a href="system-users.php" class="inline-flex items-center gap-2 text-[#7A1C1C] font-semibold mb-5 hover:underline">
            <i class="fa-solid fa-arrow-left"></i> Back to System Users List
        </a>

        <div class="bg-white p-6 md:p-8 rounded-xl shadow-md border-t-4 border-[#7A1C1C]">
            <h2 class="text-center text-[#7A1C1C] text-2xl md:text-3xl font-bold uppercase mb-6 tracking-wide md:col-span-2">Edit System User & Password</h2>

            <!-- දෝෂ පණිවිඩය පෙන්වීම (Error Message)[cite: 19] -->
            <?php if (!empty($errorMessage)): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-5 text-center font-semibold md:col-span-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>

            <!-- පරිශීලක තොරතුරු සංස්කරණ පෝරමය (Two Column Grid Layout)[cite: 19] -->
            <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">NIC Number</label>
                    <input type="text" name="nic" class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]" value="<?php echo htmlspecialchars($systemUser['nic'] ?? ''); ?>" required>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Computer Number</label>
                    <input type="text" id="computerNumberInput" name="computer_no" value="<?php echo htmlspecialchars($systemUser['computer_no'] ?? ''); ?>" inputmode="numeric" pattern="[0-9]+" title="Enter digits only" placeholder="Enter Computer Number (e.g. 405)" required class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]">
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Name</label>
                    <input type="text" name="full_name" class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]" value="<?php echo htmlspecialchars($systemUser['full_name'] ?? ''); ?>" required>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Designation</label>
                    <input type="text" name="designation" class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]" value="<?php echo htmlspecialchars($systemUser['designation'] ?? ''); ?>" required>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">System Role</label>
                    <select name="role" class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]" required>
                        <option value="">-- Select System Role --</option>
                        <option value="Admin" <?php if(($systemUser['role'] ?? '') == 'Admin') echo 'selected'; ?>>Admin</option>
                        <option value="Immediate Boss" <?php if(($systemUser['role'] ?? '') == 'Immediate Boss') echo 'selected'; ?>>Immediate Boss</option>
                        <option value="Personal File" <?php if(($systemUser['role'] ?? '') == 'Personal File') echo 'selected'; ?>>Personal File</option>
                        <option value="Subject Clerk" <?php if(($systemUser['role'] ?? '') == 'Subject Clerk') echo 'selected'; ?>>Subject Clerk</option>
                        <option value="Branch Officer" <?php if(($systemUser['role'] ?? '') == 'Branch Officer') echo 'selected'; ?>>Branch Officer</option>
                        <option value="Final Approval" <?php if(($systemUser['role'] ?? '') == 'Final Approval') echo 'selected'; ?>>Final Approval</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Current Workplace</label>
                    <input type="text" name="workplace" class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]" value="<?php echo htmlspecialchars($systemUser['workplace'] ?? ''); ?>" required>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Email Address</label>
                    <input type="email" name="email" class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]" value="<?php echo htmlspecialchars($systemUser['email'] ?? ''); ?>" required>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Phone Number</label>
                    <input type="text" name="phone" class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]" value="<?php echo htmlspecialchars($systemUser['phone'] ?? ''); ?>" required>
                </div>

                <div class="mb-2 md:col-span-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">New Password</label>
                    <input type="password" name="password" class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]" placeholder="Enter new password if changing">
                </div>

                <!-- Submit Button එක තීරු දෙකම ආවරණය වන පරිදි සකසා ඇත -->
                <div class="md:col-span-2 mt-2">
                    <button type="submit" class="bg-[#7A1C1C] hover:bg-[#5c1414] text-white border-none py-3 w-full rounded-md font-semibold text-base cursor-pointer transition">
                        <i class="fa-solid fa-save"></i> Update Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // පරිගණක අංකය සඳහා ඉලක්කම් පමණක් ඇතුළත් කිරීමට සැලැස්වීම[cite: 19]
        const computerInput = document.getElementById('computerNumberInput');
        if (computerInput) {
            computerInput.addEventListener('input', event => {
                event.target.value = event.target.value.replace(/\D/g, '');
            });
        }
    </script>

<?php
// Admin Footer ඇතුළත් කිරීම[cite: 19]
require_once __DIR__ . '/../layouts/admin_footer.php';
?>