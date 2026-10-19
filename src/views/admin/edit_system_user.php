<?php
/**
 * Edit System User - Sri Lanka Railways
 * Reads and updates a staff user from the real `users` table.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/config.php';

$pageTitle = "Edit System User - Sri Lanka Railways";
$hide_admin_extras = true;

$error_message = '';
$nic = $_GET['nic'] ?? '';

$roles = [
    2 => 'Super Admin',
    3 => 'Immediate Boss',
    4 => 'Personal File',
    5 => 'Subject Clerk',
    6 => 'Branch Officer',
    7 => 'Final Approval',
];

$conn = getDBConnection();

// Fetch staff user
$user_row = null;
if ($nic !== '') {
    $stmt = $conn->prepare(
        "SELECT nic, name, email, computer_number, mobile, role_id
           FROM users
          WHERE nic = ? AND role_id BETWEEN 2 AND 7
          LIMIT 1"
    );
    $stmt->bind_param("s", $nic);
    $stmt->execute();
    $user_row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$user_row) {
    $_SESSION['flash_message'] = 'System user not found.';
    $conn->close();
    header('Location: ' . baseUrl('admin/system-users.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newNic      = trim($_POST['nic'] ?? '');
    $computer_no = trim($_POST['computer_no'] ?? '');
    $name        = trim($_POST['full_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $role_id     = (int) ($_POST['role_id'] ?? 0);
    $password    = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';

    if (!array_key_exists($role_id, $roles)) {
        $error_message = 'Please select a valid staff role.';
    } elseif (!empty($password) && $password !== $confirm) {
        $error_message = 'Passwords do not match! Please check again.';
    } else {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $upd = $conn->prepare(
                "UPDATE users
                    SET nic = ?, computer_number = ?, name = ?, email = ?, mobile = ?, role_id = ?, password = ?
                  WHERE nic = ? AND role_id BETWEEN 2 AND 7"
            );
            $upd->bind_param("sssssiss", $newNic, $computer_no, $name, $email, $phone, $role_id, $hash, $nic);
        } else {
            $upd = $conn->prepare(
                "UPDATE users
                    SET nic = ?, computer_number = ?, name = ?, email = ?, mobile = ?, role_id = ?
                  WHERE nic = ? AND role_id BETWEEN 2 AND 7"
            );
            $upd->bind_param("sssssis", $newNic, $computer_no, $name, $email, $phone, $role_id, $nic);
        }

        if ($upd->execute()) {
            $upd->close();
            $conn->close();
            $_SESSION['flash_message'] = 'System user updated successfully!';
            header('Location: ' . baseUrl('admin/system-users.php'));
            exit;
        } else {
            $error_message = 'Update failed: ' . $conn->error;
            $upd->close();
        }
    }
}

$conn->close();

require_once __DIR__ . '/../layouts/admin_header.php';
?>

<main class="flex-grow py-8 px-4">
    <div class="max-w-4xl mx-auto w-full">
        <a href="<?php echo baseUrl('admin/system-users.php'); ?>"
           class="inline-flex items-center gap-2 text-[#7A1C1C] font-semibold mb-5 hover:underline">
            <i class="fa-solid fa-arrow-left"></i> Back to System Users List
        </a>

        <div class="bg-white p-6 md:p-8 rounded-xl shadow-md border-t-4 border-[#7A1C1C]">
            <h2 class="text-center text-[#7A1C1C] text-2xl md:text-3xl font-bold uppercase mb-6 tracking-wide">
                Edit System User
            </h2>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-5 text-center font-semibold">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo baseUrl('admin/edit_system_user.php') . '?nic=' . urlencode($nic); ?>"
                  class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">NIC Number</label>
                    <input type="text" name="nic" required
                           value="<?php echo htmlspecialchars($user_row['nic']); ?>"
                           class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]">
                </div>

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Computer Number</label>
                    <input type="text" id="computerNumberInput" name="computer_no" required
                           inputmode="numeric" pattern="[0-9]+"
                           value="<?php echo htmlspecialchars($user_row['computer_number'] ?? ''); ?>"
                           class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]">
                </div>

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Name</label>
                    <input type="text" name="full_name" required
                           value="<?php echo htmlspecialchars($user_row['name']); ?>"
                           class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]">
                </div>

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">System Role</label>
                    <select name="role_id" required
                            class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]">
                        <?php foreach ($roles as $rid => $label): ?>
                            <option value="<?php echo $rid; ?>" <?php echo ((int) $user_row['role_id'] === $rid) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Email Address</label>
                    <input type="email" name="email" required
                           value="<?php echo htmlspecialchars($user_row['email']); ?>"
                           class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]">
                </div>

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Phone Number</label>
                    <input type="text" name="phone"
                           value="<?php echo htmlspecialchars($user_row['mobile'] ?? ''); ?>"
                           class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]">
                </div>

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">
                        New Password <span class="text-xs font-normal text-gray-500">(leave blank to keep current)</span>
                    </label>
                    <input type="password" id="password" name="password" minlength="8"
                           placeholder="Enter new password if changing"
                           class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]">
                </div>

                <div>
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password" minlength="8"
                           placeholder="Re-enter new password"
                           class="w-full p-2.5 border border-slate-300 rounded-md text-sm bg-white outline-none focus:border-[#7A1C1C]">
                </div>

                <div class="md:col-span-2 mt-2">
                    <button type="submit"
                            class="w-full bg-[#7A1C1C] hover:bg-[#5c1414] text-white font-semibold py-3 px-4 rounded-md transition cursor-pointer">
                        <i class="fa-solid fa-save"></i> Update System User
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

    document.querySelector('form').addEventListener('submit', function (e) {
        const p1 = document.getElementById('password').value;
        const p2 = document.getElementById('confirmPassword').value;
        if (p1 !== '' && p1 !== p2) {
            alert("Passwords do not match! Please check again.");
            document.getElementById('confirmPassword').focus();
            e.preventDefault();
        }
    });
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>