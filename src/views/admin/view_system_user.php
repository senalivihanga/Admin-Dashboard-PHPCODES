<?php
/**
 * View System User - Sri Lanka Railways
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/config.php';

$pageTitle = "View System User - Sri Lanka Railways";
$hide_admin_extras = true;

$nic = $_GET['nic'] ?? '';
$user_row = null;
$error = '';

$roles = [
    2 => 'Super Admin',
    3 => 'Immediate Boss',
    4 => 'Personal File',
    5 => 'Subject Clerk',
    6 => 'Branch Officer',
    7 => 'Final Approval',
];

if ($nic !== '') {
    $conn = getDBConnection();
    $stmt = $conn->prepare(
        "SELECT nic, name, email, computer_number, mobile, role_id, created_at
           FROM users
          WHERE nic = ? AND role_id BETWEEN 2 AND 7
          LIMIT 1"
    );
    $stmt->bind_param("s", $nic);
    $stmt->execute();
    $user_row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if (!$user_row) {
        $error = 'System user not found.';
    }
} else {
    $error = 'No NIC provided.';
}

require_once __DIR__ . '/../layouts/admin_header.php';
?>

<main class="flex-grow py-10 px-4">
    <div class="max-w-3xl mx-auto w-full">
        <a href="<?php echo baseUrl('admin/system-users.php'); ?>"
           class="inline-flex items-center gap-2 text-[#7A1C1C] font-semibold mb-5 hover:underline">
            <i class="fa-solid fa-arrow-left"></i> Back to System Users List
        </a>

        <div class="bg-white p-6 md:p-8 rounded-xl shadow-md border-t-4 border-[#7A1C1C]">
            <h2 class="text-center text-[#7A1C1C] text-2xl md:text-3xl font-bold uppercase mb-6 tracking-wide">
                System User Details
            </h2>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded-lg text-center font-semibold">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php else: ?>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <dt class="text-xs uppercase text-slate-500 font-semibold">NIC Number</dt>
                        <dd class="mt-1 font-semibold text-slate-800"><?php echo htmlspecialchars($user_row['nic']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-slate-500 font-semibold">Computer Number</dt>
                        <dd class="mt-1 font-semibold text-slate-800"><?php echo htmlspecialchars($user_row['computer_number'] ?? '-'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-slate-500 font-semibold">Name</dt>
                        <dd class="mt-1 font-semibold text-slate-800"><?php echo htmlspecialchars($user_row['name']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-slate-500 font-semibold">System Role</dt>
                        <dd class="mt-1 font-semibold text-slate-800">
                            <?php echo htmlspecialchars($roles[(int) $user_row['role_id']] ?? 'Unknown'); ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-slate-500 font-semibold">Email</dt>
                        <dd class="mt-1 font-semibold text-slate-800"><?php echo htmlspecialchars($user_row['email']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-slate-500 font-semibold">Mobile</dt>
                        <dd class="mt-1 font-semibold text-slate-800"><?php echo htmlspecialchars($user_row['mobile'] ?? '-'); ?></dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-xs uppercase text-slate-500 font-semibold">Registered On</dt>
                        <dd class="mt-1 font-semibold text-slate-800"><?php echo htmlspecialchars($user_row['created_at'] ?? '-'); ?></dd>
                    </div>
                </dl>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
require_once __DIR__ . '/../layouts/admin_footer.php';
?>