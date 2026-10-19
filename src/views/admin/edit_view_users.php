<?php
/**
 * Edit User Details - Sri Lanka Railways
 * Fetches and updates applicant records directly from the `users` table.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/config.php';

$pageTitle = "Edit User Details - Sri Lanka Railways";
$error_message = '';
$nic = $_GET['nic'] ?? '';

$conn = getDBConnection();

// ------------------------------------------------------------
// 1. Fetch the existing user row from the database
// ------------------------------------------------------------
$user_row = null;
if ($nic !== '') {
    $stmt = $conn->prepare(
        "SELECT nic, name, email, computer_number, mobile, created_at
           FROM users
          WHERE nic = ? AND role_id = 1
          LIMIT 1"
    );
    $stmt->bind_param("s", $nic);
    $stmt->execute();
    $user_row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$user_row) {
    $_SESSION['flash_message'] = 'User not found.';
    $conn->close();
    header('Location: ' . baseUrl('admin/view_users.php'));
    exit;
}

// ------------------------------------------------------------
// 2. Handle form submission (UPDATE the real database row)
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newNic       = trim($_POST['nic'] ?? '');
    $computer_no  = trim($_POST['computer_no'] ?? '');
    $name         = trim($_POST['name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $mobile       = trim($_POST['mobile'] ?? '');
    $password     = $_POST['password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (!empty($password) && $password !== $confirm_pass) {
        $error_message = "Passwords do not match! Please check again.";
    } else {
        try {
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $conn->prepare(
                    "UPDATE users
                        SET nic = ?, computer_number = ?, name = ?, email = ?, mobile = ?, password = ?
                      WHERE nic = ?"
                );
                $upd->bind_param("sssssss", $newNic, $computer_no, $name, $email, $mobile, $hash, $nic);
            } else {
                $upd = $conn->prepare(
                    "UPDATE users
                        SET nic = ?, computer_number = ?, name = ?, email = ?, mobile = ?
                      WHERE nic = ?"
                );
                $upd->bind_param("ssssss", $newNic, $computer_no, $name, $email, $mobile, $nic);
            }

            if ($upd->execute()) {
                $upd->close();
                $conn->close();

                $_SESSION['flash_message'] = "User record updated successfully!";
                header('Location: ' . baseUrl('admin/view_users.php'));
                exit;
            } else {
                $error_message = "Update failed: " . htmlspecialchars($conn->error);
                $upd->close();
            }
        } catch (mysqli_sql_exception $e) {
            // Usually caused by changing NIC to one that already exists (duplicate key)
            $error_message = "Update failed: " . htmlspecialchars($e->getMessage());
        }
    }
}

$conn->close();

// Hide the admin dashboard title and profile dropdown on this inner page
$hide_admin_extras = true;

// Include Admin Header layout
require_once __DIR__ . '/../layouts/admin_header.php';
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
                <p><i class="fa-solid fa-circle-exclamation mr-2"></i> <?php echo $error_message; ?></p>
                <button onclick="this.parentElement.style.display='none'" class="text-red-700 font-bold">&times;</button>
            </div>
        <?php endif; ?>

        <!-- The form POSTs back to the same URL (preserving ?nic=...) -->
        <form action="<?php echo baseUrl('admin/edit_view_users.php') . '?nic=' . urlencode($nic); ?>" method="POST" onsubmit="return validateForm()">

            <!-- 1. NIC Number -->
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-slate-700 text-sm">NIC Number</label>
                <input type="text" name="nic"
                       value="<?php echo htmlspecialchars($user_row['nic'] ?? ''); ?>"
                       placeholder="Enter NIC number" required
                       class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm text-slate-800 outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C] transition">
            </div>

            <!-- 2. Computer Number -->
            <div class="mb-4">
                <label class="block mb-2 font-semibold text-gray-700 text-sm">Computer Number</label>
                <input type="text" id="computerNumberInput" name="computer_no"
                       value="<?php echo htmlspecialchars($user_row['computer_number'] ?? ''); ?>"
                       inputmode="numeric" pattern="[0-9]+" title="Enter digits only"
                       placeholder="Enter Computer Number (e.g. 405)" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
            </div>

            <!-- 3. Name -->
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-slate-700 text-sm">Name</label>
                <input type="text" name="name"
                       value="<?php echo htmlspecialchars($user_row['name'] ?? ''); ?>"
                       placeholder="Enter full name" required
                       class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm text-slate-800 outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C] transition">
            </div>

            <!-- 4. Email Address -->
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-slate-700 text-sm">Email Address</label>
                <input type="email" name="email"
                       value="<?php echo htmlspecialchars($user_row['email'] ?? ''); ?>"
                       placeholder="Enter email address" required
                       class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm text-slate-800 outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C] transition">
            </div>

            <!-- 5. Mobile Number -->
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-slate-700 text-sm">Mobile Number</label>
                <input type="tel" name="mobile"
                       value="<?php echo htmlspecialchars($user_row['mobile'] ?? ''); ?>"
                       placeholder="Enter mobile number" required
                       class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm text-slate-800 outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C] transition">
            </div>

            <!-- 6. New Password -->
            <div class="mb-5">
                <label class="block mb-2 font-semibold text-slate-700 text-sm">New Password <span class="text-xs font-normal text-gray-500">(leave blank to keep current)</span></label>
                <input type="password" id="password" name="password"
                       placeholder="Enter new password if changing"
                       class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm text-slate-800 outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C] transition">
            </div>

            <!-- 7. Confirm New Password -->
            <div class="mb-6">
                <label class="block mb-2 font-semibold text-slate-700 text-sm">Confirm New Password</label>
                <input type="password" id="confirmPassword" name="confirm_password"
                       placeholder="Re-enter new password"
                       class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm text-slate-800 outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C] transition">
            </div>

            <button type="submit"
                    class="w-full py-3 bg-[#7A1C1C] hover:bg-[#5c1414] text-white font-semibold rounded-lg text-base cursor-pointer transition duration-200 shadow-md flex items-center justify-center gap-2 mt-4">
                <i class="fa-solid fa-floppy-disk"></i> Save &amp; Return to List
            </button>
        </form>
    </div>
</div>

<script>
    // Only digits allowed in the computer-number field
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
// Include Admin Footer layout
require_once __DIR__ . '/../layouts/admin_footer.php';
?>