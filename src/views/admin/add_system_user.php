<?php
/**
 * Add New System User - Sri Lanka Railways
 * Inserts a staff user (role_id 2-7) into the real `users` table.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/config.php';

$pageTitle = "Add System User - Sri Lanka Railways";
$hide_admin_extras = true;

$error_message = '';

// Form defaults
$nic         = '';
$computer_no = '';
$full_name   = '';
$email       = '';
$phone       = '';
$role_id     = '';

// Role map (matches src/helpers/roles.php; applicant excluded)
$roles = [
    2 => 'Super Admin',
    3 => 'Immediate Boss',
    4 => 'Personal File',
    5 => 'Subject Clerk',
    6 => 'Branch Officer',
    7 => 'Final Approval',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nic         = trim($_POST['nic'] ?? '');
    $computer_no = trim($_POST['computer_no'] ?? '');
    $full_name   = trim($_POST['full_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $role_id     = (int) ($_POST['role_id'] ?? 0);
    $password    = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';

    // Validation
    if ($nic === '' || $computer_no === '' || $full_name === '' || $email === '' || $phone === '' || $password === '' || $role_id === 0) {
        $error_message = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error_message = 'Passwords do not match! Please check again.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (!array_key_exists($role_id, $roles)) {
        $error_message = 'Please select a valid staff role.';
    } else {
        $conn = getDBConnection();

        // Uniqueness
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
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $insert->bind_param("ssssssi", $nic, $full_name, $email, $hash, $computer_no, $phone, $role_id);

            if ($insert->execute()) {
                $insert->close();
                $conn->close();
                $_SESSION['flash_message'] = 'New system user added successfully!';
                header('Location: ' . baseUrl('admin/system-users.php'));
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

<main class="flex-grow py-8 px-4">
    <div class="max-w-4xl mx-auto w-full">
        <a href="<?php echo baseUrl('admin/system-users.php'); ?>"
           class="inline-flex items-center gap-2 text-[#7A1C1C] font-semibold mb-5 hover:underline">
            <i class="fa-solid fa-arrow-left"></i> Back to System Users List
        </a>

        <div class="bg-white p-6 md:p-8 rounded-xl shadow-md border-t-4 border-[#7A1C1C]">
            <h2 class="text-center text-black text-2xl md:text-3xl font-bold uppercase mb-6 tracking-wide">
                Add New System User
            </h2>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-5 text-center font-semibold md:col-span-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" onsubmit="return validateForm(event)"
                  class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">NIC Number</label>
                    <input type="text" name="nic" value="<?php echo htmlspecialchars($nic); ?>" required
                           placeholder="Enter NIC number"
                           class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white">
                </div>

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Computer Number</label>
                    <input type="text" id="computerNumberInput" name="computer_no"
                           value="<?php echo htmlspecialchars($computer_no); ?>"
                           inputmode="numeric" pattern="[0-9]+" title="Enter digits only"
                           placeholder="Enter Computer Number (e.g. 405)" required
                           class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white">
                </div>

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required
                           placeholder="Enter your name"
                           class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white">
                </div>

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">System Role</label>
                    <select name="role_id" required
                            class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white">
                        <option value="">-- Select System Role --</option>
                        <?php foreach ($roles as $rid => $label): ?>
                            <option value="<?php echo $rid; ?>" <?php echo ($role_id === $rid) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required
                           placeholder="Enter email address"
                           class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white">
                </div>

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required
                           placeholder="Enter phone number"
                           class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white">
                </div>

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Password</label>
                    <input type="password" id="password" name="password" required minlength="8"
                           placeholder="Enter password"
                           class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white">
                </div>

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password" required minlength="8"
                           placeholder="Re-enter password"
                           class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white">
                </div>

                <div class="md:col-span-2 mt-2">
                    <button type="submit"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-4 rounded-md transition cursor-pointer">
                        <i class="fa-solid fa-save"></i> Save System User
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    const computerInput = document.getElementById('computerNumberInput');
    if (computerInput) {
        computerInput.addEventListener('input', event => {
            event.target.value = event.target.value.replace(/\D/g, '');
        });
    }

    function validateForm(event) {
        const p1 = document.getElementById('password').value;
        const p2 = document.getElementById('confirmPassword').value;
        if (p1 !== p2) {
            alert("Passwords do not match! Please check again.");
            document.getElementById('confirmPassword').focus();
            event.preventDefault();
            return false;
        }
        return true;
    }
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>