<?php
/**
 * View Status Page
 *
 * >>> CHANGED:
 *   - Correctly identifies WHICH approval stage rejected the application
 *     (Final Approval → Personal File → Subject Clerk → Immediate Boss).
 *   - If the Final Approver rejected, the application has been deleted from
 *     the system, so we render a dedicated "Start a new application" panel
 *     and never offer "Fix & Resubmit" for FA rejections.
 *   - The notification marker inserted by appli_approve_FA.php is read
 *     (and marked read) even if the row still exists briefly, so the FA
 *     rejection message is always shown the first time.
 */
require_once __DIR__ . '/../../controllers/ApplicationController.php';
require_once __DIR__ . '/../../models/Application.php';

$appController = new ApplicationController();
$appModel      = new Application();

$nic = $_SESSION['nic'] ?? null;

// >>> Load the applicant's current application (may be null if FA deleted it)
$application = $nic ? $appController->getStatus() : null;

// >>> NEW: look for a *just-arrived* FA rejection notification.
//          This survives the DELETE that appli_approve_FA.php performs.
$faRejection = null;
if ($nic) {
    $faRejection = $appModel->hasUnseenFARejection($nic);
    // Mark it read immediately so the applicant only sees the FA banner once.
    if ($faRejection) {
        $conn = getDBConnection();
        $stmt = $conn->prepare(
            "UPDATE notifications
                SET is_read = 1
              WHERE nic = ?
                AND title = 'Application Rejected by Final Approval'
                AND is_read = 0"
        );
        $stmt->bind_param('s', $nic);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
}

$user = [
    'name' => $_SESSION['user_name'] ?? 'User'
];

$userName = $user['name'] ?? 'User';
$pageTitle = 'View Status';

$waitingListPosition = !empty($application['waiting_list_no'])
    ? $application['waiting_list_no']
    : ($application['position'] ?? 'N/A');
$marks = $application['marks'] ?? '0';

// >>> NEW: figure out WHICH stage rejected, when application exists
$rejectingStage = null;   // 'boss' | 'file' | 'clerk' | 'final' | null
if ($application) {
    if (($application['final_status'] ?? '') === 'rejected') {
        $rejectingStage = 'final';
    } elseif (($application['clerk_status'] ?? '') === 'rejected') {
        $rejectingStage = 'clerk';
    } elseif (($application['file_status'] ?? '') === 'rejected') {
        $rejectingStage = 'file';
    } elseif (($application['boss_status'] ?? '') === 'rejected') {
        $rejectingStage = 'boss';
    }
}

// >>> If either (a) the row is gone but the notification exists, or
//          (b) the row still exists AND its final_status is rejected,
//     we route through the dedicated FA-rejection UI.
$showFARejectionPanel = ($faRejection !== null) || ($rejectingStage === 'final');

$rawSteps = [
    1 => ['name' => 'Application Submit', 'key' => 'submit', 'icon' => 'fa-file-lines', 'contact' => '011 234 5678', 'color' => 'blue', 'bg' => 'bg-blue-600', 'border' => 'border-blue-600', 'badge_bg' => 'bg-blue-200 text-blue-900 border-blue-400', 'row_bg' => 'border-blue-400 bg-blue-100/80'],
    2 => ['name' => 'Immediate Boss', 'key' => 'boss', 'icon' => 'fa-user-tie', 'contact' => '011 234 5679', 'color' => 'purple', 'bg' => 'bg-purple-600', 'border' => 'border-purple-600', 'badge_bg' => 'bg-purple-200 text-purple-900 border-purple-400', 'row_bg' => 'border-purple-400 bg-purple-100/80'],
    3 => ['name' => 'Personal File', 'key' => 'file', 'icon' => 'fa-folder-open', 'contact' => '011 234 5680', 'color' => 'emerald', 'bg' => 'bg-emerald-600', 'border' => 'border-emerald-600', 'badge_bg' => 'bg-emerald-200 text-emerald-900 border-emerald-400', 'row_bg' => 'border-emerald-400 bg-emerald-100/80'],
    4 => ['name' => 'Subject Clerk', 'key' => 'clerk', 'icon' => 'fa-user-pen', 'contact' => '011 234 5681', 'color' => 'indigo', 'bg' => 'bg-indigo-600', 'border' => 'border-indigo-600', 'badge_bg' => 'bg-indigo-200 text-indigo-900 border-indigo-400', 'row_bg' => 'border-indigo-400 bg-indigo-100/80'],
    5 => ['name' => 'Final Approver', 'key' => 'final', 'icon' => 'fa-award', 'contact' => '011 234 5683', 'color' => 'rose', 'bg' => 'bg-rose-600', 'border' => 'border-rose-600', 'badge_bg' => 'bg-rose-200 text-rose-900 border-rose-400', 'row_bg' => 'border-rose-400 bg-rose-100/80']
];

$steps = [];
$isRejectedChain = false;
$inProgressAssigned = false;

$subStatus = $application['submit_status'] ?? (
    ($application['application_status'] ?? '') === 'submitted' ? 'success' : 'pending'
);
$bStatus  = $application['boss_status']  ?? 'pending';
$fStatus  = $application['file_status']  ?? 'pending';
$cStatus  = $application['clerk_status'] ?? 'pending';
$fnStatus = $application['final_status'] ?? 'pending';

$statusMap = [
    1 => $subStatus,
    2 => $bStatus,
    3 => $fStatus,
    4 => $cStatus,
    5 => $fnStatus
];

foreach ($rawSteps as $id => $stepInfo) {
    $st = $statusMap[$id] ?? 'pending';
    if ($isRejectedChain) { $st = 'rejected'; }
    if ($st === 'rejected' && !$isRejectedChain) { $isRejectedChain = true; }

    if (!$isRejectedChain) {
        if ($st === 'pending' && !$inProgressAssigned) {
            $st = 'in-progress';
            $inProgressAssigned = true;
        } elseif ($st === 'in-progress') {
            $inProgressAssigned = true;
        }
    }

    $stepReason = null;
    if ($st === 'rejected') {
        $stepReason = $application[$stepInfo['key'] . '_reason'] ?? 'This step was rejected or stopped due to previous stage issues.';
    }

    $steps[$id] = [
        'name'     => $stepInfo['name'],
        'icon'     => $stepInfo['icon'],
        'status'   => $st,
        'contact'  => $stepInfo['contact'],
        'reason'   => $stepReason,
        'color'    => $stepInfo['color'],
        'bg'       => $stepInfo['bg'],
        'border'   => $stepInfo['border'],
        'badge_bg' => $stepInfo['badge_bg'],
        'row_bg'   => $stepInfo['row_bg']
    ];
}

$isSubmitted     = ($subStatus === 'success' || $subStatus === 'approved');
$isFinalApproved = ($fnStatus === 'success'  || $fnStatus === 'approved');

$hideHeaderActions = true;
include __DIR__ . '/../layouts/header.php';
?>

    <main class="max-w-6xl w-full mx-auto px-4 py-8 flex-grow">
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">

            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <div class="flex items-center space-x-3">
                    <div class="text-[#5a0b13] text-2xl">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">View Status</h2>
                        <p class="text-sm text-gray-500">Track your application verification progress.</p>
                    </div>
                </div>
                <a href="<?php echo baseUrl('dashboard'); ?>" class="bg-[#5a0b13] text-white text-sm px-4 py-2 rounded-lg hover:bg-opacity-95 transition flex items-center space-x-2 shadow">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Back to Dashboard</span>
                </a>
            </div>

            <?php if ($showFARejectionPanel): ?>

                <!-- ============================================================
                     FINAL APPROVER REJECTED — application was deleted from the
                     system. Show a dedicated "start fresh" panel.
                     ============================================================ -->
                <?php
                    // Prefer the live reason from the notification. If the row
                    // still somehow exists (edge case), fall back to final_reason.
                    $faReasonText = '';
                    if ($faRejection) {
                        // message format is:
                        // "Your quarter application has been rejected by the Final Approver. Reason: <reason>. Because ..."
                        // We pull out the reason between "Reason: " and the next period.
                        if (preg_match('/Reason:\s*(.+?)(?:\.\s*Because|$)/s', $faRejection['message'], $m)) {
                            $faReasonText = trim($m[1]);
                        } else {
                            $faReasonText = $faRejection['message'];
                        }
                    } elseif (!empty($application['final_reason'])) {
                        $faReasonText = $application['final_reason'];
                    }
                ?>

                <div class="text-center py-10 px-4">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-rose-100 text-rose-600 mb-5">
                        <i class="fa-solid fa-circle-xmark text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-rose-800 mb-3">
                        Application Rejected by Final Approval
                    </h3>
                    <p class="text-gray-600 max-w-2xl mx-auto mb-6 leading-relaxed">
                        Your quarter application has been reviewed at the <strong>Final Approval</strong> stage
                        and was <strong>rejected</strong>. Because this is the last step in the approval process,
                        the application has been <strong>removed from the system</strong>. To apply again,
                        you must submit a <strong>completely new application from the beginning</strong>
                        (choose residence type, language, and fill the form again).
                    </p>

                    <?php if ($faReasonText !== ''): ?>
                    <div class="bg-rose-50 border border-rose-200 rounded-lg p-5 max-w-xl mx-auto mb-6 text-left">
                        <p class="text-sm font-semibold text-rose-800 mb-1">Reason from Final Approver:</p>
                        <p class="text-sm text-rose-900 italic">
                            <?= htmlspecialchars($faReasonText) ?>
                        </p>
                        <?php if ($faRejection && !empty($faRejection['created_at'])): ?>
                        <p class="text-xs text-rose-600 mt-2">
                            <i class="fa-solid fa-clock mr-1"></i>
                            Rejected on: <?= htmlspecialchars($faRejection['created_at']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="flex flex-col sm:flex-row justify-center gap-3">
                        <a href="<?= htmlspecialchars(baseUrl('application/residence'), ENT_QUOTES, 'UTF-8') ?>"
                           class="inline-flex items-center justify-center bg-[#5a0b13] hover:bg-[#4a050a] text-white hover:text-amber-300 font-semibold px-6 py-3 rounded-lg shadow transition">
                            <i class="fa-solid fa-file-circle-plus mr-2"></i>
                            Fill a New Application
                        </a>
                        <a href="<?= htmlspecialchars(baseUrl('notifications'), ENT_QUOTES, 'UTF-8') ?>"
                           class="inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-3 rounded-lg shadow transition">
                            <i class="fa-solid fa-bell mr-2"></i>
                            View Notifications
                        </a>
                    </div>
                    <p class="text-xs text-gray-400 mt-4">
                        Note: your previous application has been permanently removed. Any saved draft is not reused.
                    </p>
                </div>

            <?php elseif ($application): ?>

                <!-- ============================================================
                     NORMAL FLOW: application exists -> progress tracker
                     ============================================================ -->
                <div class="mb-12 relative px-6 md:px-12">
                    <?php
                        $totalSteps = count($steps);
                        $progressPercent = 0;
                        if ($totalSteps > 1) {
                            $successSteps = 0;
                            foreach ($steps as $stItem) {
                                if ($stItem['status'] == 'success' || $stItem['status'] == 'approved') {
                                    $successSteps++;
                                }
                            }
                            $progressPercent = ($successSteps / ($totalSteps - 1)) * 100;
                            if ($progressPercent > 100) $progressPercent = 100;
                        }
                    ?>
                    <div class="absolute top-1/2 left-12 right-12 h-1.5 bg-gray-200 -translate-y-1/2 z-0 rounded-full"></div>
                    <div class="absolute top-1/2 left-12 h-1.5 bg-emerald-600 -translate-y-1/2 z-0 rounded-full transition-all duration-500" style="width: calc(<?= $progressPercent; ?>% - 24px);"></div>

                    <div class="relative z-10 flex justify-between items-center">
                        <?php foreach ($steps as $id => $step): ?>
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full border-2 flex items-center justify-center text-base <?= $step['bg']; ?> text-white <?= $step['border']; ?> shadow-md relative transition-all duration-300
                                <?php if ($step['status'] == 'in-progress'): ?> ring-4 ring-<?= $step['color']; ?>-100 shadow-<?= $step['color']; ?>-100
                                <?php elseif ($step['status'] == 'rejected'): ?> ring-4 ring-rose-100 shadow-rose-100
                                <?php endif; ?>">
                                <?php if ($step['status'] == 'success' || $step['status'] == 'approved'): ?>
                                    <i class="fa-solid fa-check text-sm"></i>
                                <?php elseif ($step['status'] == 'in-progress'): ?>
                                    <i class="fa-solid fa-clock text-sm"></i>
                                <?php elseif ($step['status'] == 'rejected'): ?>
                                    <i class="fa-solid fa-xmark text-sm"></i>
                                <?php else: ?>
                                    <i class="fa-solid <?= $step['icon']; ?> text-xs"></i>
                                <?php endif; ?>
                            </div>
                            <span class="text-xs font-semibold mt-2 text-gray-700 text-center max-w-[90px]"><?= $step['name']; ?></span>
                            <div class="mt-1 w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold text-white <?= $step['bg']; ?>">
                                <?= $id; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="space-y-4">
                    <?php foreach ($steps as $id => $step):
                        if ($step['status'] == 'pending') { continue; }

                        $borderCol = "border-gray-200 bg-white";
                        $badgeBg = "bg-gray-100 text-gray-600 border-gray-300";
                        $badgeText = "Pending";

                        if ($step['status'] == 'success' || $step['status'] == 'approved') {
                            $borderCol = $step['row_bg'];
                            $badgeBg = $step['badge_bg'];
                            $badgeText = "Success";
                        } elseif ($step['status'] == 'in-progress') {
                            $borderCol = "border-2 " . $step['border'] . " bg-white";
                            $badgeBg = $step['badge_bg'];
                            $badgeText = "In Progress";
                        } elseif ($step['status'] == 'rejected') {
                            $borderCol = "border-rose-200 bg-rose-50/30";
                            $badgeBg = "bg-rose-100 text-rose-800 border-rose-300";
                            $badgeText = "Rejected";
                        }
                    ?>
                    <div class="border <?= $borderCol; ?> rounded-xl p-4 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm">
                        <div class="flex items-center space-x-4 w-full md:w-2/4">
                            <div class="w-12 h-12 rounded-full bg-white border border-gray-200 flex items-center justify-center text-xl shadow-sm text-gray-700 flex-shrink-0">
                                <i class="fa-solid <?= $step['icon']; ?>"></i>
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-gray-800"><?= $id; ?>. <?= $step['name']; ?></span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <?php if ($step['status'] == 'success' || $step['status'] == 'approved'): ?>
                                        Your application has been successfully verified by <?= $step['name']; ?>.
                                    <?php elseif ($step['status'] == 'in-progress'): ?>
                                        Your application is currently being processed at <?= $step['name']; ?>.
                                    <?php elseif ($step['status'] == 'rejected'): ?>
                                        <?php if (!empty($step['reason'])): ?>
                                            <span class="text-rose-600 font-semibold">Reject Reason:</span> <?= htmlspecialchars($step['reason']); ?>
                                        <?php else: ?>
                                            This step was marked as rejected or stopped due to previous stage issues.
                                        <?php endif; ?>
                                    <?php else: ?>
                                        The <?= $step['name']; ?> verification is required and is pending in the current process.
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="w-full md:w-1/4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold border <?= $badgeBg; ?>">
                                <?= $badgeText; ?>
                            </span>
                        </div>
                        <div class="w-full md:w-1/4 text-xs text-gray-600 text-left md:text-center">
                            <span class="font-semibold block text-gray-500">Contact</span>
                            <span class="font-medium text-gray-800"><?= $step['contact']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center">
                                    <i class="fa-solid fa-list-ol"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Waiting List</h3>
                                    <p class="text-xs text-gray-500">Current waiting list position</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">Waiting</span>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Waiting List Position</p>
                                    <?php if ($isFinalApproved): ?>
                                        <p class="text-3xl font-bold text-blue-700 mt-1"><?= htmlspecialchars($waitingListPosition); ?></p>
                                    <?php else: ?>
                                        <p class="text-3xl font-bold text-gray-400 mt-1">-</p>
                                    <?php endif; ?>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700">
                                    <i class="fa-solid fa-ranking-star text-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                                    <i class="fa-solid fa-star"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Marks</h3>
                                    <p class="text-xs text-gray-500">Application evaluation marks</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">Score</span>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Total Marks</p>
                                    <?php if ($isSubmitted): ?>
                                        <p class="text-3xl font-bold text-amber-600 mt-1"><?= htmlspecialchars($marks); ?></p>
                                    <?php else: ?>
                                        <p class="text-sm font-semibold text-gray-500 mt-2">Pending Submission</p>
                                    <?php endif; ?>
                                </div>
                                <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                                    <i class="fa-solid fa-award text-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                $appStatus = $application['application_status'] ?? '';
                if ($appStatus === 'rejected' && $rejectingStage !== 'final'):
                    // >>> CHANGED: pick the *correct* stage label & reason, instead
                    //              of blindly defaulting to Immediate Boss.
                    $stageLabels = [
                        'boss'  => 'Immediate Boss',
                        'file'  => 'Personal File',
                        'clerk' => 'Subject Clerk',
                    ];
                    $stageLabel  = $stageLabels[$rejectingStage] ?? 'Approver';
                    $reasonField = $rejectingStage . '_reason';
                    $rejectReason = $application[$reasonField] ?? null;

                    // Build the language-specific resubmit URL
                    $langKey = $application['form_language'] ?? 'en';
                    $formPath = ($langKey === 'single_residence')
                        ? 'application/form/si'
                        : 'application/form/en';
                ?>
                <div class="mt-8 p-6 border-2 border-rose-300 bg-rose-50 rounded-xl">
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-rose-800 mb-2">
                            <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                            Your application was rejected by the <?= htmlspecialchars($stageLabel) ?>
                        </h3>
                        <?php if (!empty($rejectReason)): ?>
                        <p class="text-sm text-rose-700 mb-1">Reason from <?= htmlspecialchars($stageLabel) ?>:</p>
                        <p class="text-sm text-rose-900 italic mb-5"><?= htmlspecialchars($rejectReason) ?></p>
                        <?php endif; ?>

                        <p class="text-sm text-gray-600 mb-5">
                            You can correct the issue and resubmit the same application.
                        </p>

                        <a href="<?= htmlspecialchars(baseUrl($formPath . '?resubmit=' . (int)$application['id']), ENT_QUOTES, 'UTF-8') ?>"
                           class="inline-flex items-center bg-rose-700 hover:bg-rose-800
                                  text-white font-semibold px-6 py-3 rounded-lg shadow transition">
                            <i class="fa-solid fa-rotate-right mr-2"></i>
                            Fix &amp; Resubmit Application
                        </a>
                    </div>
                </div>
                <?php endif; ?>

            <?php else: ?>

                <!-- ============================================================
                     Empty state — applicant has never applied, or the FA just
                     deleted their application and the notification has already
                     been read on a previous visit.
                     ============================================================ -->
                <div class="text-center py-12 text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <div class="text-5xl mb-4">📋</div>
                    <p class="text-lg mb-2">No active application found.</p>
                    <p class="text-sm mb-6">
                        You have not submitted an application yet, or your previous application is no longer active.
                    </p>
                    <a href="<?= htmlspecialchars(baseUrl('application/residence'), ENT_QUOTES, 'UTF-8') ?>"
                       class="inline-flex items-center bg-[#5a0b13] hover:bg-[#4a050a] text-white font-semibold px-5 py-2.5 rounded-lg shadow transition">
                        <i class="fa-solid fa-file-circle-plus mr-2"></i>
                        Fill a New Application
                    </a>
                </div>

            <?php endif; ?>

        </div>
    </main>

<?php include __DIR__ . '/../layouts/footer.php'; ?>