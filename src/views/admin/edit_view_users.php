<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/config.php';

// Initialize session users data matching view_users.php[cite: 7]
if (!isset($_SESSION['system_users_data_v2'])) {
    $_SESSION['system_users_data_v2'] = [
        [
            'id' => 0,
            'fullName' => 'A. B. Perera',
            'nic' => '199812345678',
            'email' => 'abperera@railway.gov.lk',
            'computerNo' => '405',
            'phone' => '0771234567'
        ],
        [
            'id' => 1,
            'fullName' => 'S. K. Silva',
            'nic' => '199287654321',
            'email' => 'sksilva@railway.gov.lk',
            'computerNo' => '406',
            'phone' => '0719876543'
        ],
        [
            'id' => 2,
            'fullName' => 'N. M. Fernando',
            'nic' => '199567890123',
            'email' => 'nmfernando@railway.gov.lk',
            'computerNo' => '407',
            'phone' => '0765432109'
        ],
        [
            'id' => 3,
            'fullName' => 'P. R. Wickramasinghe',
            'nic' => '199034567890',
            'email' => 'prwickramasinghe@railway.gov.lk',
            'computerNo' => '408',
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
            'computerNo' => '410',
            'phone' => '0701234567'
        ]
    ];
}

// Get user ID from URL, default to 0[cite: 7]
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

// Find the exact array index matching the unique user ID[cite: 7]
$user_index = 0;
foreach ($_SESSION['system_users_data_v2'] as $index => $item) {
    if (isset($item['id']) && (int)$item['id'] === $id) {
        $user_index = $index;
        break;
    }
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nic = trim($_POST['nic'] ?? '');
    $computer_no = trim($_POST['computer_no'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!empty($password) && $password !== $confirm_password) {
        $error_message = "Passwords do not match! Please check again.";
    } else {
        // Update user details in the shared session array using the correct index[cite: 7]
        $_SESSION['system_users_data_v2'][$user_index]['nic'] = $nic;
        $_SESSION['system_users_data_v2'][$user_index]['computerNo'] = $computer_no;
        $_SESSION['system_users_data_v2'][$user_index]['fullName'] = $name;
        $_SESSION['system_users_data_v2'][$user_index]['email'] = $email;
        $_SESSION['system_users_data_v2'][$user_index]['phone'] = $mobile;

        // Set success flash message for view_users.php[cite: 7]
        $_SESSION['flash_message'] = "User record updated successfully!";
        
        // Redirect to view_users.php automatically upon saving[cite: 7]
        header("Location: " . baseUrl('admin/view_users.php'));
        exit();
    }
}

$user = $_SESSION['system_users_data_v2'][$user_index] ?? [];

// Hide admin dashboard title and profile dropdown in the header for this page[cite: 7]
$hide_admin_extras = true;
$pageTitle = "Edit User Details - Sri Lanka Railways";

// Include Admin Header layout from the correct layouts folder path[cite: 7]
include_once __DIR__ . '/../layouts/admin_header.php';
?>

<div class="max-w-xl mx-auto w-full my-10 px-4 flex-grow">
    <!-- Back Link -->
    <a href="<?php echo baseUrl('admin/view_users.php'); ?>" class="inline-flex items-center gap-2 text-[#7A1C1C] font-semibold mb-6 hover:underline transition">
        <i class="fa-solid fa-arrow-left"></i> Back to User List
    </a>

    <!-- Form Card -->
    <div class="bg-white p-6 sm:p-10 rounded-2xl shadow-xl border-t-[5px] border-[#7A1C1C]">
        
        <h2 class="text-center text-[#7A1C1C] text-xl sm:text-2xl font-bold uppercase tracking-wide mb-6">
            Edit User Information
        </h2>

        <!-- Error Message Alert -->
        <?php if (!empty($error_message)): ?>
            <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 text-sm rounded-r-lg flex justify-between items-center">
                <p><i class="fa-solid fa-circle-exclamation mr-2"></i> <?php echo htmlspecialchars($error_message); ?></p>
                <button onclick="this.parentElement.style.display='none'" class="text-red-700 font-bold">&times;</button>
            </div>
        <?php endif; ?>

        <form action="edit_view_users.php?id=<?php echo $id; ?>" method="POST" onsubmit="return validateForm()">
            <!-- 1. NIC Number -->
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-slate-700 text-sm">NIC Number</label>
                <input type="text" name="nic" value="<?php echo htmlspecialchars($user['nic'] ?? ''); ?>" placeholder="Enter NIC number" required 
                       class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm text-slate-800 outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C] transition">
            </div>

            <!-- 2. Computer Number (Updated with add_user specs) -->
            <div class="mb-4">
                <label class="block mb-2 font-semibold text-gray-700 text-sm">Computer Number</label>
                <input type="text" id="computerNumberInput" name="computer_no" value="<?php echo htmlspecialchars($user['computerNo'] ?? ''); ?>" inputmode="numeric" pattern="[0-9]+" title="Enter digits only" placeholder="Enter Computer Number (e.g. 405)" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
            </div>

            <!-- 3. Name -->
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-slate-700 text-sm">Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['fullName'] ?? ''); ?>" placeholder="Enter full name" required 
                       class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm text-slate-800 outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C] transition">
            </div>

            <!-- 4. Email Address -->
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-slate-700 text-sm">Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Enter email address" required 
                       class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm text-slate-800 outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C] transition">
            </div>

            <!-- 5. Mobile Number -->
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-slate-700 text-sm">Mobile Number</label>
                <input type="tel" name="mobile" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Enter mobile number" required 
                       class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm text-slate-800 outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C] transition">
            </div>

            <!-- 6. New Password -->
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-slate-700 text-sm">New Password </label>
                <input type="password" id="password" name="password" placeholder="Enter new password if changing" 
                       class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm text-slate-800 outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C] transition">
            </div>

            <!-- 7. Confirm New Password -->
            <div class="mb-6">
                <label class="block mb-2 font-semibold text-slate-700 text-sm">Confirm New Password</label>
                <input type="password" id="confirmPassword" name="confirm_password" placeholder="Re-enter new password" 
                       class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm text-slate-800 outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C] transition">
            </div>

            <button type="submit" class="w-full py-3 bg-[#7A1C1C] hover:bg-[#5c1414] text-white font-semibold rounded-lg text-base cursor-pointer transition duration-200 shadow-md flex items-center justify-center gap-2 mt-4">
                <i class="fa-solid fa-floppy-disk"></i> Save & Return to List
            </button>
        </form>
    </div>
</div>

<script>
    // පරිගණක අංකය සඳහා ඉලක්කම් පමණක් ඇතුළත් කිරීමට සැලැස්වීම[cite: 6]
    const computerInput = document.getElementById('computerNumberInput');
    if (computerInput) {
        computerInput.addEventListener('input', event => {
            event.target.value = event.target.value.replace(/\D/g, '');
        });
    }

    function validateForm() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (password !== "" && password !== confirmPassword) {
            alert("Passwords do not match! Please check again.");
            document.getElementById('confirmPassword').focus();
            return false;
        }
        return true;
    }
</script>

<?php
// Include Admin Footer layout from the correct layouts folder path[cite: 7]
include_once __DIR__ . '/../layouts/admin_footer.php';
?>