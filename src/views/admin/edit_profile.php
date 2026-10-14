<?php
/**
 * Edit Profile Page - Sri Lanka Railways Quarter Management System
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// දත්ත සමුදාය සම්බන්ධතාවය
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'admin_dashboard_db'; // අවශ්‍ය නම් ඔබේ database නම මෙහි වෙනස් කරන්න

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("දත්ත සමුදාය සම්බන්ධ වීම අසාර්ථක විය: " . $conn->connect_error);
}

$success_message = '';
$error_message = '';

// පද්ධතියේ ලොග් වන පරිශීලකයාගේ ID එක සැසියෙන් (Session) නිවැරදිව ලබා ගැනීම
$logged_in_id = $_SESSION['user_id'] 
             ?? $_SESSION['admin_id'] 
             ?? $_SESSION['boss_id'] 
             ?? $_SESSION['clerk_id'] 
             ?? $_SESSION['officer_id'] 
             ?? $_SESSION['id'] ?? 0;

// NIC මඟින් ලොග වී ඇත්නම් ID එක ලබා ගැනීමට ආරක්ෂිත පියවරක්
if ($logged_in_id <= 0 && isset($_SESSION['nic'])) {
    $nic_session = $_SESSION['nic'];
    $id_stmt = $conn->prepare("SELECT id FROM users WHERE nic = ?");
    $id_stmt->bind_param("s", $nic_session);
    $id_stmt->execute();
    $id_res = $id_stmt->get_result();
    if ($row = $id_res->fetch_assoc()) {
        $logged_in_id = $row['id'];
        $_SESSION['user_id'] = $logged_in_id;
    }
    $id_stmt->close();
}

if ($logged_in_id <= 0) {
    if (isset($_SESSION['admin_name']) || isset($_SESSION['name'])) {
        $logged_in_id = 1; 
    } else {
        header("Location: " . baseUrl('index.php')); 
        exit();
    }
}

// පෝරමය සබ්මිට් වූ විට දත්ත යාවත්කාලීන කිරීම
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $computer_number = trim($_POST['computer_number'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($mobile)) {
        $error_message = "ඊමේල් ලිපිනය සහ ජංගම දුරකථන අංකය ඇතුළත් කිරීම අත්‍යවශ්‍ය වේ.";
    } else {
        // දත්ත සමුදායේ ප්‍රොෆයිල් දත්ත යාවත්කාලීන කිරීම
        $update_stmt = $conn->prepare("UPDATE users SET email = ?, computer_number = ?, mobile = ?, whatsapp = ? WHERE id = ?");
        $update_stmt->bind_param("ssssi", $email, $computer_number, $mobile, $whatsapp, $logged_in_id);
        
        if ($update_stmt->execute()) {
            $success_message = "ප්‍රොෆයිල් දත්ත සාර්ථකව යාවත්කාලීන කරන ලදී!";
        } else {
            $error_message = "දත්ත යාවත්කාලීන කිරීමේ දෝෂයක් සිදු විය.";
        }
        $update_stmt->close();

        // මුරපදය වෙනස් කිරීම පරීක්ෂා කිරීම
        if (!empty($new_password) || !empty($confirm_password)) {
            if ($new_password !== $confirm_password) {
                $error_message = "නව මුරපද එකිනෙකට නොගැලපේ.";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $pass_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $pass_stmt->bind_param("si", $hashed_password, $logged_in_id);
                $pass_stmt->execute();
                $pass_stmt->close();

                $success_message = "ප්‍රොෆයිලය සහ මුරපදය සාර්ථකව යාවත්කාලීන කරන ලදී!";
            }
        }
    }
}

// දත්ත සමුදායෙන් ලොග් වී සිටින අදාළ පරිශීලකයා ලියාපදිංචි වන විට ලබාදුන් දත්ත ලබා ගැනීම (Auto-fill සඳහා)
$stmt = $conn->prepare("SELECT name, email, computer_number, mobile, whatsapp FROM users WHERE id = ?");
$stmt->bind_param("i", $logged_in_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    $user = [
        'name' => $_SESSION['name'] ?? $_SESSION['admin_name'] ?? 'User',
        'email' => '',
        'computer_number' => '',
        'mobile' => '',
        'whatsapp' => ''
    ];
}
$stmt->close();

// Admin Header එකට පිටුවේ නම ලබා දීම
$pageTitle = "Edit Profile - Sri Lanka Railways System";

// Header එක ඇතුළත් කිරීම
require_once __DIR__ . '/../layouts/admin_header.php';
?>

    <!-- Main Content Area -->
    <div class="profile-wrapper my-8 mx-auto max-w-xl w-full px-4 flex-grow">
        <a href="#" onclick="history.back(); return false;" class="inline-flex items-center gap-2 text-[#7A1C1C] font-semibold mb-5 hover:text-[#5c1414] transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="profile-card bg-white p-8 rounded-2xl shadow-lg border-t-4 border-[#7A1C1C]">
            <div class="profile-header text-center mb-6">
                <i class="fa-solid fa-user-pen text-4xl text-[#7A1C1C]"></i>
                <h2 class="text-2xl font-bold text-gray-800 mt-2 uppercase">Edit Profile</h2>
            </div>
            
            <?php if (!empty($success_message)): ?>
                <div class="bg-emerald-100 text-emerald-800 p-3 rounded-lg mb-4 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="bg-rose-100 text-rose-800 p-3 rounded-lg mb-4 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- 404 දෝෂය වළක්වා ගැනීමට Form action එක නිවැරදිව සකසා ඇත -->
            <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST">
                <!-- ඊමේල් ලිපිනය (Register වෙද්දී දුන් දත්ත මෙහි Auto-fill වේ) -->
                <div class="mb-4">
                    <label for="email" class="block mb-1 font-semibold text-gray-700 text-sm">Email Address</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Enter your email address" class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#7A1C1C]" required>
                    </div>
                </div>

                <!-- පරිගණක අංකය (Register වෙද්දී දුන් දත්ත මෙහි Auto-fill වේ) -->
                <div class="mb-4">
                    <label for="computer_number" class="block mb-1 font-semibold text-gray-700 text-sm">Computer Number</label>
                    <div class="relative">
                        <i class="fa-solid fa-desktop absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="computer_number" name="computer_number" value="<?php echo htmlspecialchars($user['computer_number'] ?? ''); ?>" placeholder="Enter your computer number" class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#7A1C1C]">
                    </div>
                </div>

                <!-- ජංගම දුරකථන අංකය (Register වෙද්දී දුන් දත්ත මෙහි Auto-fill වේ) -->
                <div class="mb-4">
                    <label for="mobile" class="block mb-1 font-semibold text-gray-700 text-sm">Mobile Number</label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>" placeholder="Enter mobile number" class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#7A1C1C]" required>
                    </div>
                </div>

                <!-- වට්ස්ඇප් අංකය -->
                <div class="mb-4">
                    <label for="whatsapp" class="block mb-1 font-semibold text-gray-700 text-sm">WhatsApp Number</label>
                    <div class="relative">
                        <i class="fa-brands fa-whatsapp absolute left-4 top-1/2 -translate-y-1/2 text-emerald-600 text-lg"></i>
                        <input type="tel" id="whatsapp" name="whatsapp" value="<?php echo htmlspecialchars($user['whatsapp'] ?? ''); ?>" placeholder="Enter WhatsApp number" class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#7A1C1C]">
                    </div>
                </div>

                <!-- මුරපදය වෙනස් කිරීමේ කොටස -->
                <div class="text-lg font-bold text-[#7A1C1C] border-b border-gray-200 pb-1 my-6">Change Password</div>

                <!-- දැනට ඇති මුරපදය -->
                <div class="mb-4">
                    <label for="current_password" class="block mb-1 font-semibold text-gray-700 text-sm">Current Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" id="current_password" name="current_password" placeholder="Enter current password" class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#7A1C1C]">
                    </div>
                </div>

                <!-- නව මුරපදය -->
                <div class="mb-4">
                    <label for="new_password" class="block mb-1 font-semibold text-gray-700 text-sm">New Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password" class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#7A1C1C]">
                    </div>
                </div>

                <!-- නව මුරපදය තහවුරු කිරීම -->
                <div class="mb-6">
                    <label for="confirm_password" class="block mb-1 font-semibold text-gray-700 text-sm">Confirm New Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#7A1C1C]">
                    </div>
                </div>

                <!-- වෙනස්කම් සුරැකීමේ බොත්තම -->
                <button type="submit" class="w-full py-3 bg-[#7A1C1C] hover:bg-[#5c1414] text-white font-semibold rounded-lg transition duration-200 shadow-md flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </form>
        </div>
    </div>

<?php
// Footer එක ඇතුළත් කිරීම
require_once __DIR__ . '/../layouts/admin_footer.php';
?>