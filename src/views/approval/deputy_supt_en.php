<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
$auth->requireLogin();

$user = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Deputy Superintendent Approval - English';
include __DIR__ . '/../layouts/header.php';
?>

<!-- Tailwind CSS CDN – kept from the original HTML to preserve its style -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Main Container – exact HTML structure from approve_1_en.html -->
<div class="max-w-4xl mx-auto bg-white mt-6 p-8 shadow-md rounded-lg border border-gray-200">
    
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <!-- Back button – now uses baseUrl for correct routing -->
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

    <!-- Form – action points to the backend submit endpoint -->
    <form action="<?php echo baseUrl('approval/deputy/submit'); ?>" method="POST">
        <input type="hidden" name="application_id" value="<?php echo (int)($_GET['application_id'] ?? 0); ?>">
        <fieldset class="bg-white border border-gray-300 rounded-lg p-6 shadow-sm mb-6">
            <legend class="text-sm font-bold text-[#0f4c81] px-3 py-1 bg-gray-100 border border-gray-200 rounded-md">
                Deputy Superintendent (Transportation) Colombo
            </legend>

            <p class="text-sm text-gray-700 mt-4 mb-2">To the best of my knowledge,</p>
            
            <ul class="text-sm text-gray-600 space-y-2 list-disc list-inside mb-6 leading-relaxed">
                <li>The above-mentioned information is correct / corrected as appropriate,</li>
                <li>The applicant / spouse or children do / do not possess residential property within 15 miles,</li>
                <li>I hereby recommend / do not recommend this housing application of the applicant.</li>
            </ul>

            <div class="mb-4">
                <label class="block text-sm text-gray-700 mb-1">Type and Number of Quarters Applied For :</label>
                <input type="text" name="rec_quarter_type_no" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#0f4c81] text-sm">
            </div>

            <div class="mb-6">
                <label class="block text-sm text-gray-700 mb-1">Essentiality to service (such as night shifts) and other remarks if any :</label>
                <textarea name="rec_other_remarks" rows="3" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#0f4c81] text-sm"></textarea>
            </div>

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mt-6 pt-4 border-t border-gray-100">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Date :</label>
                    <input type="date" name="rec_date" 
                        class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#0f4c81] text-sm">
                </div>

                <div style="text-align: right;">
                    <div style="border-bottom: 1px dotted #333; width: 280px; display: inline-block; margin-bottom: 5px;"></div>
                    <p style="margin: 0; font-size: 13px;">Signature and Official Stamp of D.S.A. / P.D.S. / D.S. / A.D.S. / Nearest Senior Officer</p>
                </div>
            </div>

            <div class="mt-6 text-center">
                <button type="submit" class="bg-[#0f4c81] hover:bg-[#0b3961] text-white font-medium text-sm px-6 py-2.5 rounded-md shadow transition duration-150">
                    Approved
                </button>
            </div>
        </fieldset>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>