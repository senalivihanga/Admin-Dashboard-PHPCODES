<?php
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// PHP Form Handling Logic & Validation
$success_message = "";
$error_message = "";
$name = $nic = $email = $computer_no = $mobile = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $nic = trim($_POST['nic'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $computer_no = trim($_POST['computer_no'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match! Please check again.";
    } else {
        // ආරක්ෂාව සඳහා පාස්වර්ඩ් එක Hash කිරීම (Hash password securely)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // 1. ඩේටාබේස් එකට අලුත් යූසර් ඇතුළත් කිරීම[cite: 2]
            $stmt = $pdo->prepare("INSERT INTO system_users (full_name, nic, designation, workplace, computer_no, role, phone, email, password) VALUES (?, ?, 'N/A', 'N/A', ?, 'User', ?, ?, ?)");
            $stmt->execute([$name, $nic, $computer_no, $mobile, $email, $hashed_password]);

            // 2. view_users.php හි ඇති Session Table එක සඳහා දත්ත එකතු කිරීම[cite: 1, 3]
            if (!isset($_SESSION['system_users_data_v2'])) {
                $_SESSION['system_users_data_v2'] = [];
            }

            // නව ID එකක් ජනනය කිරීම[cite: 1]
            $new_id = 0;
            if (!empty($_SESSION['system_users_data_v2'])) {
                $ids = array_column($_SESSION['system_users_data_v2'], 'id');
                $new_id = max($ids) + 1;
            }

            // නව පරිශීලකයා array එකට ඇතුළත් කිරීම[cite: 1]
            $new_user = [
                'id' => $new_id,
                'fullName' => $name,
                'nic' => $nic,
                'email' => $email,
                'computerNo' => $computer_no,
                'phone' => $mobile
            ];
            $_SESSION['system_users_data_v2'][] = $new_user;

            // සාර්ථක පණිවිඩය (Flash Message) සකස් කිරීම[cite: 1]
            $_SESSION['flash_message'] = "New user record added successfully!";

            // 3. ස්වයංක්‍රීයව view_users.php වෙත යොමු කිරීම (Redirect)[cite: 1, 2]
            header("Location: view_users.php");
            exit();
        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

$pageTitle = "Add New User - Sri Lanka Railways Admin";
$hide_admin_extras = true;

// Include Admin Specific Header[cite: 2]
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<!-- Main Content Area -->
<main class="flex-grow py-10 px-4">
    <div class="max-w-xl mx-auto w-full">
        <!-- Back Link -->
        <a href="view_users.php" class="inline-flex items-center gap-2 text-[#7A1C1C] font-semibold mb-6 hover:underline transition">
            <i class="fa-solid fa-arrow-left"></i> Back to User List
        </a>

        <!-- Form Card -->
        <div class="bg-white p-6 sm:p-10 rounded-2xl shadow-xl border-t-[6px] border-[#7A1C1C]">
            <h2 class="text-gray-800 text-xl sm:text-2xl font-bold mb-6 uppercase text-center tracking-wide">Add New Registered User</h2>
            
            <!-- Error Message Alert -->
            <?php if (!empty($error_message)): ?>
                <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 text-sm rounded-r-lg">
                    <p><i class="fa-solid fa-circle-exclamation mr-2"></i> <?php echo $error_message; ?></p>
                </div>
            <?php endif; ?>

            <!-- Success Message Alert -->
            <?php if (!empty($success_message)): ?>
                <div class="mb-6 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 text-sm rounded-r-lg">
                    <p><i class="fa-solid fa-circle-check mr-2"></i> <?php echo $success_message; ?></p>
                </div>
            <?php endif; ?>

            <form action="" method="POST" onsubmit="return validateForm()">
                <div class="mb-4">
                    <label class="block mb-2 font-semibold text-gray-700 text-sm">NIC Number</label>
                    <input type="text" name="nic" value="<?php echo htmlspecialchars($nic); ?>" placeholder="Enter NIC Number" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-semibold text-gray-700 text-sm">Computer Number</label>
                    <input type="text" id="computerNumberInput" name="computer_no" value="<?php echo htmlspecialchars($computer_no); ?>" inputmode="numeric" pattern="[0-9]+" title="Enter digits only" placeholder="Enter Computer Number (e.g. 405)" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-semibold text-gray-700 text-sm">Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter Full Name" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-semibold text-gray-700 text-sm">Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter Email Address" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-semibold text-gray-700 text-sm">Mobile Number</label>
                    <input type="tel" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>" placeholder="Enter Mobile Number" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-semibold text-gray-700 text-sm">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter Password" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                </div>

                <div class="mb-5">
                    <label class="block mb-2 font-semibold text-gray-700 text-sm">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password" placeholder="Re-enter Password" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                </div>

                <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-base cursor-pointer transition duration-200 shadow-md flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user-plus"></i> Save User
                </button>
            </form>
        </div>
    </div>
</main>

<script>
    // පරිගණක අංකය සඳහා ඉලක්කම් පමණක් ඇතුළත් කිරීමට සැලැස්වීම
    const computerInput = document.getElementById('computerNumberInput');
    if (computerInput) {
        computerInput.addEventListener('input', event => {
            event.target.value = event.target.value.replace(/\D/g, '');
        });
    }

    // පාස්වර්ඩ් එක සමාන දැයි පරීක්ෂා කිරීමේ JavaScript පරීක්ෂාව
    function validateForm() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (password !== confirmPassword) {
            alert("Passwords do not match! Please check again.");
            document.getElementById('confirmPassword').focus();
            return false;
        }
        return true;
    }
</script>

<?php
// Include Admin Specific Footer[cite: 1]
require_once __DIR__ . '/../layouts/admin_footer.php';
?>