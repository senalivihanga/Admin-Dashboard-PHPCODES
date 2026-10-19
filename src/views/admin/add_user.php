<?php
/**
 * Add New Applicant - Sri Lanka Railways
 * Inserts into the real `users` table (role_id = 1).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/config.php';

$pageTitle = "Add New User - Sri Lanka Railways Admin";
$hide_admin_extras = true;

$success_message = '';
$error_message   = '';

$name        = '';
$nic         = '';
$email       = '';
$computer_no = '';
$mobile      = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name             = trim($_POST['name'] ?? '');
    $nic              = trim($_POST['nic'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $computer_no      = trim($_POST['computer_no'] ?? '');
    $mobile           = trim($_POST['mobile'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || $nic === '' || $email === '' || $computer_no === '' || $mobile === '' || $password === '') {
        $error_message = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match! Please check again.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[0-9]{9,12}$/', $nic)) {
        $error_message = 'NIC must be 9 to 12 digits.';
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $error_message = 'Mobile number must be 10 digits.';
    } else {
        $conn = getDBConnection();

        $chk = $conn->prepare("SELECT nic FROM users WHERE nic = ? LIMIT 1");
        $chk->bind_param("s", $nic);
        $chk->execute();
        $nicTaken = $chk->get_result()->num_rows > 0;
        $chk->close();

        $chk2 = $conn->prepare("SELECT nic FROM users WHERE email = ? LIMIT 1");
        $chk2->bind_param("s", $email);
        $chk2->execute();
        $emailTaken = $chk2->get_result()->num_rows > 0;
        $chk2->close();

        $chk3 = $conn->prepare("SELECT nic FROM users WHERE computer_number = ? LIMIT 1");
        $chk3->bind_param("s", $computer_no);
        $chk3->execute();
        $compTaken = $chk3->get_result()->num_rows > 0;
        $chk3->close();

        if ($nicTaken) {
            $error_message = 'This NIC is already registered.';
        } elseif ($emailTaken) {
            $error_message = 'This email is already registered.';
        } elseif ($compTaken) {
            $error_message = 'This computer number is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            $insert = $conn->prepare(
                "INSERT INTO users (nic, name, email, password, computer_number, mobile, role_id)
                 VALUES (?, ?, ?, ?, ?, ?, 1)"
            );
            $insert->bind_param("ssssss", $nic, $name, $email, $hash, $computer_no, $mobile);

            if ($insert->execute()) {
                $insert->close();
                $conn->close();
                $_SESSION['flash_message'] = 'New user record added successfully!';
                header('Location: ' . baseUrl('admin/view_users.php'));
                exit;
            } else {
                $error_message = 'Insert failed: ' . $conn->error;
                $insert->close();
            }
        }

        $conn->close();
    }
}

require_once __DIR__ . '/../layouts/admin_header.php';
?>

<main class="flex-grow py-10 px-4">
    <div class="max-w-xl mx-auto w-full">
        <a href="<?php echo baseUrl('admin/view_users.php'); ?>"
           class="inline-flex items-center gap-2 text-[#7A1C1C] font-semibold mb-6 hover:underline transition">
            <i class="fa-solid fa-arrow-left"></i> Back to User List
        </a>

        <div class="bg-white p-6 sm:p-10 rounded-2xl shadow-xl border-t-[6px] border-[#7A1C1C]">
            <h2 class="text-gray-800 text-xl sm:text-2xl font-bold mb-6 uppercase text-center tracking-wide">
                Add New Registered User
            </h2>

            <?php if (!empty($error_message)): ?>
                <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 text-sm rounded-r-lg">
                    <p><i class="fa-solid fa-circle-exclamation mr-2"></i> <?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="mb-6 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 text-sm rounded-r-lg">
                    <p><i class="fa-solid fa-circle-check mr-2"></i> <?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <form action="" method="POST" onsubmit="return validateForm()">
                <div class="mb-4">
                    <label class="block mb-2 font-semibold text-gray-700 text-sm">NIC Number</label>
                    <input type="text" name="nic" value="<?php echo htmlspecialchars($nic); ?>"
                           placeholder="Enter NIC Number" required
                           pattern="[0-9]{9,12}" title="NIC must be 9-12 digits"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-semibold text-gray-700 text-sm">Computer Number</label>
                    <input type="text" id="computerNumberInput" name="computer_no"
                           value="<?php echo htmlspecialchars($computer_no); ?>"
                           inputmode="numeric" pattern="[0-9]+" title="Enter digits only"
                           placeholder="Enter Computer Number (e.g. 405)" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-semibold text-gray-700 text-sm">Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>"
                           placeholder="Enter Full Name" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-semibold text-gray-700 text-sm">Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                           placeholder="Enter Email Address" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-semibold text-gray-700 text-sm">Mobile Number</label>
                    <input type="tel" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>"
                           placeholder="Enter Mobile Number" required
                           pattern="[0-9]{10}" title="Mobile must be 10 digits"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-semibold text-gray-700 text-sm">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Enter Password" required minlength="8"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                </div>

                <div class="mb-5">
                    <label class="block mb-2 font-semibold text-gray-700 text-sm">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password"
                           placeholder="Re-enter Password" required minlength="8"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                </div>

                <button type="submit"
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-base cursor-pointer transition duration-200 shadow-md flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user-plus"></i> Save User
                </button>
            </form>
        </div>
    </div>
</main>

<script>
    // Only digits in computer-number field
    const computerInput = document.getElementById('computerNumberInput');
    if (computerInput) {
        computerInput.addEventListener('input', event => {
            event.target.value = event.target.value.replace(/\D/g, '');
        });
    }

    // Client-side confirm-password check
    function validateForm() {
        const p1 = document.getElementById('password').value;
        const p2 = document.getElementById('confirmPassword').value;
        if (p1 !== p2) {
            alert("Passwords do not match! Please check again.");
            document.getElementById('confirmPassword').focus();
            return false;
        }
        return true;
    }
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>