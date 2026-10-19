<?php
/**
 * Add New System User Page
 */

// සැසිය දැනටමත් ආරම්භ වී ඇත්දැයි පරීක්ෂා කර බැලීම[cite: 18]
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// පිටු මාතෘකාව සැකසීම[cite: 18]
$pageTitle = "Add System User - Sri Lanka Railways";

// ඩේටාබේස් සම්බන්ධතාවය (Database Connection)[cite: 18]
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
$computer_no = "";

// ෆෝම් එක සබ්මිට් වූ විට ක්‍රියාත්මක වීම (Handle Form Submission)[cite: 18]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nic         = trim(isset($_POST['nic']) ? $_POST['nic'] : '');
    $computer_no = trim(isset($_POST['computer_no']) ? $_POST['computer_no'] : '');
    $fullName    = trim(isset($_POST['full_name']) ? $_POST['full_name'] : '');
    $designation = trim(isset($_POST['designation']) ? $_POST['designation'] : '');
    $role        = trim(isset($_POST['role']) ? $_POST['role'] : '');
    $workplace   = trim(isset($_POST['workplace']) ? $_POST['workplace'] : '');
    $email       = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $phone       = trim(isset($_POST['phone']) ? $_POST['phone'] : '');
    $password    = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPass = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // මුරපද දෙක සමාන දැයි පරීක්ෂා කිරීම (Check if passwords match)[cite: 18]
    if ($password !== $confirmPass) {
        $errorMessage = "Passwords do not match! Please check again.";
    } else {
        // ආරක්ෂාව සඳහා මුරපදය Hash කිරීම (Hash password securely)[cite: 18]
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            // ඩේටාබේස් එකට අලුත් සිස්ටම් යූසර් ඇතුළත් කිරීම (Insert user into database)[cite: 18]
            $stmt = $pdo->prepare("INSERT INTO system_users (nic, computer_no, full_name, designation, role, workplace, email, phone, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nic, $computer_no, $fullName, $designation, $role, $workplace, $email, $phone, $hashedPassword]);

            // දත්ත සාර්ථකව ඇතුළත් වූ පසු system-users.php වෙත ස්වයංක්‍රීයව යොමු කිරීම[cite: 18]
            header("Location: system-users.php");
            exit();

        } catch (PDOException $e) {
            $errorMessage = "Error: " . $e->getMessage();
        }
    }
}

// Admin Header ඇතුළත් කිරීම[cite: 18]
require_once __DIR__ . '/../layouts/admin_header.php';
?>

    <!-- ප්‍රධාන අන්තර්ගත කන්ටේනරය -->
    <div class="container mx-auto px-4 py-8 max-w-4xl flex-grow">
        <!-- පරිශීලක ලැයිස්තුව වෙත ආපසු යාමේ සබැඳිය (Back Link)[cite: 18] -->
        <a href="system-users.php" class="inline-flex items-center gap-2 text-[#7A1C1C] font-semibold mb-5 hover:underline">
            <i class="fa-solid fa-arrow-left"></i> Back to System Users List
        </a>

        <div class="bg-white p-6 md:p-8 rounded-xl shadow-md border-t-4 border-[#7A1C1C]">
            <h2 class="text-center text-black text-2xl md:text-3xl font-bold uppercase mb-6 tracking-wide">Add New System User</h2>

            <!-- දෝෂ පණිවිඩය පෙන්වීම (Error Message)[cite: 18] -->
            <?php if (!empty($errorMessage)): ?>
                <div class="bg-[#fee2e2] text-[#991b1b] p-3 rounded-lg mb-5 text-center font-semibold md:col-span-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>

            <!-- නව සිස්ටම් යූසර් ලියාපදිංචි කිරීමේ පෝරමය (Two Column Grid Layout)[cite: 18] -->
            <form method="POST" action="" onsubmit="return validateForm(event)" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">NIC Number</label>
                    <input type="text" name="nic" class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white" placeholder="Enter NIC number" required>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Computer Number</label>
                    <input type="text" id="computerNumberInput" name="computer_no" value="<?php echo htmlspecialchars($computer_no); ?>" class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white" inputmode="numeric" pattern="[0-9]+" title="Enter digits only" placeholder="Enter Computer Number (e.g. 405)" required>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Name</label>
                    <input type="text" name="full_name" class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white" placeholder="Enter your name" required>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Designation</label>
                    <input type="text" name="designation" class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white" placeholder="Enter designation" required>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">System Role</label>
                    <select name="role" class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white" required>
                        <option value="">-- Select System Role --</option>
                        <option value="Admin">Admin</option>
                        <option value="Immediate Boss">Immediate Boss</option>
                        <option value="Personal File">Personal File</option>
                        <option value="Subject Clerk">Subject Clerk</option>
                        <option value="Branch Officer">Branch Officer</option>
                        <option value="Final Approval">Final Approval</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Current Workplace</label>
                    <input type="text" name="workplace" class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white" placeholder="Enter current workplace" required>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Email Address</label>
                    <input type="email" name="email" class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white" placeholder="Enter email address" required>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Phone Number</label>
                    <input type="text" name="phone" class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white" placeholder="Enter phone number" required>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Password</label>
                    <input type="password" id="password" name="password" class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white" placeholder="Enter password" required>
                </div>

                <div class="mb-2">
                    <label class="block font-semibold mb-1.5 text-slate-800 text-sm">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password" class="w-full p-2.5 border border-slate-300 rounded-md text-sm outline-none focus:border-[#7A1C1C] bg-white" placeholder="Re-enter password" required>
                </div>

                <!-- Submit Button එක තීරු දෙකම ආවරණය වන පරිදි සකසා ඇත -->
                <div class="md:col-span-2 mt-2">
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-4 rounded-md transition cursor-pointer">
                        <i class="fa-solid fa-save"></i> Save System User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // පරිගණක අංකය සඳහා ඉලක්කම් පමණක් ඇතුළත් කිරීමට සැලැස්වීම[cite: 18]
        const computerInput = document.getElementById('computerNumberInput');
        if (computerInput) {
            computerInput.addEventListener('input', event => {
                event.target.value = event.target.value.replace(/\D/g, '');
            });
        }

        // මුරපද දෙක සමාන දැයි පරීක්ෂා කිරීමේ JavaScript පරීක්ෂාව[cite: 18]
        function validateForm(event) {
            const passwordVal = document.getElementById('password').value;
            const confirmPasswordVal = document.getElementById('confirmPassword').value;

            if (passwordVal !== confirmPasswordVal) {
                alert("Passwords do not match! Please check again.");
                document.getElementById('confirmPassword').focus();
                event.preventDefault();
                return false;
            }
            return true;
        }
    </script>

<?php
// Admin Footer ඇතුළත් කිරීම[cite: 18]
require_once __DIR__ . '/../layouts/admin_footer.php';
?>