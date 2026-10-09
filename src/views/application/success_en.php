<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
$auth->requireLogin();

$user = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Application Successful - Sri Lanka Railways';
include __DIR__ . '/../layouts/header.php';

// Get the application number from URL, fallback to a default
$appNo = isset($_GET['appNo']) ? htmlspecialchars($_GET['appNo'], ENT_QUOTES, 'UTF-8') : null;

// If no app number provided, generate a placeholder
if (empty($appNo)) {
    $currentYear = date('Y');
    $appNo = "SLR/AOT/{$currentYear}/0001";
}
?>

<style>
    /* Subtle fade-in animation for the success card */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.5s ease-out forwards;
    }
    /* Pulse animation for the checkmark */
    @keyframes pulseCheck {
        0%   { transform: scale(0.9); }
        50%  { transform: scale(1.08); }
        100% { transform: scale(1); }
    }
    .animate-check {
        animation: pulseCheck 0.6s ease-in-out 0.3s both;
    }
</style>

<div class="flex items-center justify-center min-h-[75vh] p-4">
    <div class="w-full max-w-md animate-fade-in-up">
        <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 text-center">

            <!-- Success Icon -->
            <div class="mx-auto w-20 h-20 flex items-center justify-center rounded-full bg-green-100 mb-5 animate-check">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <!-- Title -->
            <h2 class="text-2xl sm:text-3xl font-bold text-[#0f4c81] mb-3">
                Application Submitted Successfully!
            </h2>

            <!-- Description -->
            <p class="text-gray-600 text-sm sm:text-base leading-relaxed mb-6">
                Your application for Sri Lanka Railways Quarters has been successfully received.
                Your reference number is shown below:
            </p>

            <!-- Application Number -->
            <div class="mb-6">
                <span id="display-app-no"
                      class="inline-block bg-blue-50 border border-blue-200 text-blue-700 font-bold text-base sm:text-lg px-5 py-3 rounded-lg tracking-wide break-all">
                    <?= $appNo ?>
                </span>
            </div>

            <!-- Info note -->
            <p class="text-xs text-gray-500 mb-6">
                Please save this reference number for future inquiries.
            </p>

            <!-- Action Button -->
            <a href="../dashboard/index.php"
               class="inline-flex items-center justify-center gap-2 w-full bg-[#0f4c81] hover:bg-[#0d3d6b] text-white font-medium text-sm sm:text-base px-6 py-3 rounded-lg transition-colors duration-200 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
               
                Go to Applicant Dashboard
            </a>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>