<?php
/**
 * Edit Profile Page
 */
require_once __DIR__ . '/../../controllers/ProfileController.php';

$profileController = new ProfileController();
$userData = $profileController->getProfile();
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $computer_no = trim($_POST['computer_no'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Retain existing Name and NIC since users are not allowed to change them
    $name = $userData['name'] ?? '';
    $nic = $userData['nic'] ?? '';
    
    if (!empty($password) && $password !== $confirm_password) {
        $error = "Passwords do not match! Please check again.";
    } elseif ($email && $computer_no && $mobile && $whatsapp) {
        $result = $profileController->updateProfile($name, $nic, $email, $computer_no, $mobile, $whatsapp, $password);
        if ($result['success']) {
            // Update database and automatically redirect to dashboard on success
            header("Location: " . baseUrl('dashboard'));
            exit();
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}

$pageTitle = 'Edit Profile';

// Include layout header
include __DIR__ . '/../layouts/header.php';
?>

    <div class="max-w-5xl mx-auto px-4 py-10">
        <!-- Wide Card Box with Two-Column Grid Fields -->
        <div class="bg-white p-8 sm:p-12 rounded-2xl shadow-xl border-t-8 border-[#7A1C1C]">
            <div class="text-center mb-8">
                <span class="inline-block p-3 bg-red-50 text-[#7A1C1C] rounded-full mb-2 text-xl shadow-sm">
                    <i class="fa-solid fa-pen-to-square"></i>
                </span>
                <h2 class="text-2xl font-bold text-gray-800 uppercase">Edit Profile</h2>
                <p class="text-sm text-gray-500 mt-1">Update your personal information</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="mb-6 p-4 rounded-lg text-sm font-medium bg-red-100 text-red-700">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="mb-6 p-4 rounded-lg text-sm font-medium bg-green-100 text-green-700">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" onsubmit="return validateForm(event)">
                <!-- Two Column Grid Layout -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Computer Number (with PC/Desktop icon) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Computer Number</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-desktop"></i>
                            </span>
                            <input type="text" name="computer_no" value="<?php echo htmlspecialchars($userData['computer_number'] ?? ''); ?>" required 
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                        </div>
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required 
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                        </div>
                    </div>

                    <!-- Mobile Number -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Mobile Number</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-phone"></i>
                            </span>
                            <input type="tel" name="mobile" value="<?php echo htmlspecialchars($userData['mobile'] ?? ''); ?>" required 
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                        </div>
                    </div>

                    <!-- WhatsApp Number -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">WhatsApp Number</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-brands fa-whatsapp text-base"></i>
                            </span>
                            <input type="tel" name="whatsapp" value="<?php echo htmlspecialchars($userData['whatsapp'] ?? ''); ?>" required 
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                        </div>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">New Password <span class="text-xs font-normal text-gray-500">(Leave blank to keep old)</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" id="password" name="password" placeholder="Enter new password if changing" 
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                        </div>
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Confirm New Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" id="confirmPassword" name="confirm_password" placeholder="Re-enter new password" 
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#7A1C1C] focus:ring-1 focus:ring-[#7A1C1C]">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons (Centered) -->
                <div class="flex items-center justify-center gap-4 mt-8 pt-6 border-t border-gray-100">
                    <a href="<?php echo baseUrl('dashboard'); ?>" class="w-1/3 sm:w-40 text-center py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition shadow-sm">
                        Back
                    </a>
                    <button type="submit" class="w-2/3 sm:w-48 py-3 bg-[#7A1C1C] hover:bg-[#5c1414] text-white font-semibold rounded-lg transition shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validateForm(event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== "" && password !== confirmPassword) {
                alert("Passwords do not match! Please check again.");
                document.getElementById('confirmPassword').focus();
                event.preventDefault();
                return false;
            }
            return true;
        }
    </script>

<?php 
// Include layout footer
include __DIR__ . '/../layouts/footer.php'; 
?>