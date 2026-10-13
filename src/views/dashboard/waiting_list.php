<?php
require_once __DIR__ . '/../../models/WaitingList.php';

$waitingListModel = new WaitingList();
$entry = $waitingListModel->getLatestForNic($_SESSION['nic']);
$pageTitle = 'Waiting List';
include __DIR__ . '/../layouts/header.php';
?>
<main class="max-w-4xl mx-auto w-full px-4 py-10">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between gap-4 border-b border-gray-200 pb-4 mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Waiting List</h1>
                <p class="text-sm text-gray-500 mt-1">Your current quarter allocation queue entry</p>
            </div>
            <a href="<?= htmlspecialchars(baseUrl('dashboard'), ENT_QUOTES, 'UTF-8'); ?>" class="text-sm font-semibold text-[#5c060d]">Back to dashboard</a>
        </div>
        <?php if ($entry): ?>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div><dt class="text-xs uppercase text-gray-500">Quarter type</dt><dd class="mt-1 font-semibold text-gray-900"><?= htmlspecialchars($entry['quarter_type'] ?? '', ENT_QUOTES, 'UTF-8'); ?></dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Position</dt><dd class="mt-1 font-semibold text-gray-900"><?= htmlspecialchars((string)($entry['position'] ?? 'Pending'), ENT_QUOTES, 'UTF-8'); ?></dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Applied date</dt><dd class="mt-1 font-semibold text-gray-900"><?= htmlspecialchars($entry['applied_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?></dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Evaluation marks</dt><dd class="mt-1 font-semibold text-gray-900"><?= htmlspecialchars((string)($entry['employee_marks'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></dd></div>
            </dl>
        <?php else: ?>
            <p class="text-sm text-gray-600">You do not currently have a waiting-list entry.</p>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
