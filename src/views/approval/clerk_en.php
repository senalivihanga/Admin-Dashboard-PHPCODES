<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
$auth->requireLogin();

$user = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Subject Clerk Approval - English';
include __DIR__ . '/../layouts/header.php';
?>

<!-- Main Container – exact Tailwind structure from approve_2_en.html -->
<div class="max-w-4xl mx-auto bg-white mt-6 p-8 shadow-md rounded-lg border border-gray-200">
    
    <!-- Header Actions: Back Button -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <a href="<?php echo baseUrl('dashboard'); ?>" class="inline-block bg-amber-400 hover:bg-amber-500 text-black font-bold py-2.5 px-5 rounded-md shadow transition duration-150 text-sm">
                &laquo; Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Title Section -->
    <div class="text-center border-b-2 border-[#0f4c81] pb-4 mb-6">
        <h1 class="text-2xl font-bold text-[#0f4c81] mb-1">Sri Lanka Railways</h1>
        <h2 class="text-lg font-semibold text-gray-700">Transportation Sub Department</h2>
    </div>

    <!-- Clerk Recommendation Form -->
    <form action="<?php echo baseUrl('approval/clerk/submit'); ?>" method="POST">
        <input type="hidden" name="application_id" value="<?php echo (int)($_GET['application_id'] ?? 0); ?>">
        <fieldset class="bg-white border border-gray-300 rounded-lg p-6 shadow-sm mb-6">
            <legend class="text-sm font-bold text-[#0f4c81] px-3 py-1 bg-gray-100 border border-gray-200 rounded-md">
                Subject Clerk's Recommendation Based on Personal File
            </legend>

            <p class="text-sm font-bold text-gray-800 mt-4 mb-4">Recommendation regarding the eligibility of the above-mentioned housing applicant to obtain a Government / Railway Quarters:</p>
            
            <!-- Question I -->
            <div class="mb-4">
                <label class="block text-sm text-gray-700 mb-1">I. Has the right to Government / Railway quarters been forfeited due to a disciplinary order?</label>
                <div class="flex gap-6 mt-1">
                    <label class="flex items-center gap-2 text-sm cursor-pointer"><input type="radio" name="clerk_rec_forfeit" value="Yes" class="text-[#0f4c81] focus:ring-[#0f4c81]"> Yes</label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer"><input type="radio" name="clerk_rec_forfeit" value="No" class="text-[#0f4c81] focus:ring-[#0f4c81]"> No</label>
                </div>
            </div>

            <!-- Question II -->
            <div class="mb-4">
                <label class="block text-sm text-gray-700 mb-1">II. If yes, the date on which the disciplinary order expires :</label>
                <input type="date" name="house_vacated_date" class="w-full sm:w-2/5 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#0f4c81] text-sm">
            </div>

            <!-- Question III -->
            <div class="mb-4">
                <label class="block text-sm text-gray-700 mb-1">III. Is he/she a recipient of a Mihindusethpura housing scheme?</label>
                <div class="flex gap-6 mt-1">
                    <label class="flex items-center gap-2 text-sm cursor-pointer"><input type="radio" name="clerk_rec_waitlist" value="Yes" class="text-[#0f4c81] focus:ring-[#0f4c81]"> Yes</label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer"><input type="radio" name="clerk_rec_waitlist" value="No" class="text-[#0f4c81] focus:ring-[#0f4c81]"> No</label>
                </div>
            </div>

            <!-- Question IV -->
            <div class="mb-4">
                <label class="block text-sm text-gray-700 mb-1">IV. Residing / Not residing in another Government / Railway quarters according to the personal file?</label>
                <div class="flex gap-6 mt-1">
                    <label class="flex items-center gap-2 text-sm cursor-pointer"><input type="radio" name="is_another_residence_permanent" value="Yes" class="text-[#0f4c81] focus:ring-[#0f4c81]"> Yes</label>
                    <label class="flex items-center gap-2 text-sm cursor-pointer"><input type="radio" name="is_not_another_residence_permanent" value="No" class="text-[#0f4c81] focus:ring-[#0f4c81]"> No</label>
                </div>
            </div>

            <!-- Question V -->
            <div class="mb-6">
                <label class="block text-sm text-gray-700 mb-1">V. Other Remarks:</label>
                <textarea name="clerk_rec_other_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#0f4c81] text-sm"></textarea>
            </div>

            <!-- Signature & Cabinet Number Area -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mt-6 pt-4 border-t border-gray-200">
                <div style="text-align: right;">
                    <div style="border-bottom: 1px dotted #333; width: 220px; display: inline-block; margin-bottom: 5px;"></div>
                    <p style="margin: 0; font-size: 13px;">Subject Clerk's Signature</p>
                </div>

                <div>
                    <label class="block text-xs text-gray-600 mb-1">Cabinet Number:</label>
                    <input type="text" name="clerk_reg_no" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#0f4c81] text-sm">
                </div>
            </div>

            <div class="mt-6 text-center">
                <button type="submit" class="bg-[#0f4c81] hover:bg-[#0b3961] text-white font-medium text-sm px-6 py-2.5 rounded-md shadow transition duration-150">
                    Approve
                </button>
            </div>
        </fieldset>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>