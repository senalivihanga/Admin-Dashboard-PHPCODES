<?php
/**
 * Edit Profile Page - Sri Lanka Railways Quarter Management System
 * -------------------------------------------------------------
 * Uses the central config (qms_database_updated DB) and the logged-in
 * user's NIC from the session (users.nic is the primary key).
 *
 * Only columns that actually exist in the `users` table are updated:
 *   - email
 *   - computer_number
 *   - mobile
 *   - password  (optional; bcrypt-hashed with cost 12)
 *
 * `whatsapp` is displayed for UI parity but is NOT stored because the
 * column does not exist in the schema.
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ------------------------------------------------------------
// 1. Require a logged-in session
// ------------------------------------------------------------
$nic = $_SESSION['nic'] ?? '';
if ($nic === '') {
    header('Location: ' . baseUrl('login'));
    exit();
}

$success_message = '';
$error_message   = '';

$conn = getDBConnection();

// ------------------------------------------------------------
// 2. Handle POST (profile update)
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email            = trim($_POST['email'] ?? '');
    $computer_number  = trim($_POST['computer_number'] ?? '');
    $mobile           = trim($_POST['mobile'] ?? '');
    $whatsapp         = trim($_POST['whatsapp'] ?? ''); // UI only
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($email === '' || $mobile === '') {
        $error_message = "Email and mobile number are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif ($new_password !== '' && $new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } elseif ($new_password !== '' && strlen($new_password) < 8) {
        $error_message = "New password must be at least 8 characters.";
    } else {
        // Optional uniqueness check for email (excluding self)
        $chk = $conn->prepare("SELECT nic FROM users WHERE email = ? AND nic <> ? LIMIT 1");
        $chk->bind_param("ss", $email, $nic);
        $chk->execute();
        $emailTaken = $chk->get_result()->num_rows > 0;
        $chk->close();

        if ($emailTaken) {
            $error_message = "This email is already used by another account.";
        } else {
            try {
                if ($new_password !== '') {
                    $hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $upd  = $conn->prepare(
                        "UPDATE users
                            SET email = ?, computer_number = ?, mobile = ?, password = ?
                          WHERE nic = ?"
                    );
                    $upd->bind_param("sssss", $email, $computer_number, $mobile, $hash, $nic);
                } else {
                    $upd = $conn->prepare(
                        "UPDATE users
                            SET email = ?, computer_number = ?, mobile = ?
                          WHERE nic = ?"
                    );
                    $upd->bind_param("ssss", $email, $computer_number, $mobile, $nic);
                }

                if ($upd->execute()) {
                    // Keep session values in sync
                    $_SESSION['user_email']      = $email;
                    $_SESSION['computer_number'] = $computer_number;
                    $_SESSION['computer_no']     = $computer_number;

                    $success_message = "Profile updated successfully!";
                } else {
                    $error_message = "Update failed: " . htmlspecialchars($conn->error);
                }
                $upd->close();
            } catch (mysqli_sql_exception $e) {
                // e.g. duplicate email / invalid column — show friendly message
                $error_message = "Update failed: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}

// ------------------------------------------------------------
// 3. Load current user row for auto-fill
// ------------------------------------------------------------
$user = null;
try {
    $stmt = $conn->prepare(
        "SELECT nic, name, email, computer_number, mobile
           FROM users
          WHERE nic = ?
          LIMIT 1"
    );
    $stmt->bind_param("s", $nic);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $error_message = "Could not load profile: " . htmlspecialchars($e->getMessage());
}

if (!$user) {
    $conn->close();
    session_destroy();
    header('Location: ' . baseUrl('login'));
    exit();
}

$conn->close();

// ------------------------------------------------------------
// 4. Render page
// ------------------------------------------------------------
$pageTitle = "Edit Profile - Sri Lanka Railways System";
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<main class="flex-grow py-8 px-4 w-full">
    <div class="profile-wrapper max-w-xl mx-auto w-full">

        <!-- Back to Dashboard -->
        <a href="<?php echo baseUrl(roleHomePath(currentRoleId() ?: 2)); ?>"
           class="inline-flex items-center gap-2 text-[#7A1C1C] font-semibold mb-5 hover:text-[#5c1414] transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="profile-card bg-white p-6 sm:p-8 rounded-2xl shadow-lg border-t-4 border-[#7A1C1C]">
            <div class="profile-header text-center mb-6">
                <i class="fa-solid fa-user-pen text-4xl text-[#7A1C1C]"></i>
                <h2 class="text-2xl font-bold text-gray-800 mt-2 uppercase">Edit Profile</h2>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="bg-emerald-100 text-emerald-800 p-3 rounded-lg mb-4 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="bg-rose-100 text-rose-800 p-3 rounded-lg mb-4 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST">

                <!-- Read-only identity fields -->
                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-700 text-sm">NIC (cannot be changed)</label>
                    <div class="relative">
                        <i class="fa-solid fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" value="<?php echo htmlspecialchars($user['nic']); ?>" readonly
                               class="w-full pl-11 pr-4 py-2.5 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-600">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-700 text-sm">Name (cannot be changed)</label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" readonly
                               class="w-full pl-11 pr-4 py-2.5 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-600">
                    </div>
                </div>

                <!-- Editable fields -->
                <div class="mb-4">
                    <label for="email" class="block mb-1 font-semibold text-gray-700 text-sm">Email Address</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email" id="email" name="email" required
                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                               placeholder="Enter your email address"
                               class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#7A1C1C]">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="computer_number" class="block mb-1 font-semibold text-gray-700 text-sm">Computer Number</label>
                    <div class="relative">
                        <i class="fa-solid fa-desktop absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="computer_number" name="computer_number"
                               value="<?php echo htmlspecialchars($user['computer_number'] ?? ''); ?>"
                               placeholder="Enter your computer number"
                               inputmode="numeric" pattern="[0-9]*"
                               oninput="this.value = this.value.replace(/[^0-9]/g,'')"
                               class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#7A1C1C]">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="mobile" class="block mb-1 font-semibold text-gray-700 text-sm">Mobile Number</label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="tel" id="mobile" name="mobile" required
                               value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>"
                               placeholder="Enter mobile number"
                               class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#7A1C1C]">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="whatsapp" class="block mb-1 font-semibold text-gray-700 text-sm">
                        WhatsApp Number
                        <span class="text-xs font-normal text-gray-400">(optional, not saved to database)</span>
                    </label>
                    <div class="relative">
                        <i class="fa-brands fa-whatsapp absolute left-4 top-1/2 -translate-y-1/2 text-emerald-600 text-lg"></i>
                        <input type="tel" id="whatsapp" name="whatsapp" placeholder="Enter WhatsApp number"
                               class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#7A1C1C]">
                    </div>
                </div>

                <!-- Password change -->
                <div class="text-lg font-bold text-[#7A1C1C] border-b border-gray-200 pb-1 my-6">Change Password</div>

                <div class="mb-4">
                    <label for="new_password" class="block mb-1 font-semibold text-gray-700 text-sm">New Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" id="new_password" name="new_password" minlength="8"
                               placeholder="Leave blank to keep current password"
                               class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#7A1C1C]">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="confirm_password" class="block mb-1 font-semibold text-gray-700 text-sm">Confirm New Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="8"
                               placeholder="Re-enter new password"
                               class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#7A1C1C]">
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit"
                        class="w-full py-3 bg-[#7A1C1C] hover:bg-[#5c1414] text-white font-semibold rounded-lg transition duration-200 shadow-md flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</main>

<script>
    // Client-side confirmation of new password before submit
    document.querySelector('form').addEventListener('submit', function (e) {
        const p1 = document.getElementById('new_password').value;
        const p2 = document.getElementById('confirm_password').value;
        if (p1 !== '' && p1 !== p2) {
            e.preventDefault();
            alert('New passwords do not match!');
            document.getElementById('confirm_password').focus();
        }
    });
</script>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>