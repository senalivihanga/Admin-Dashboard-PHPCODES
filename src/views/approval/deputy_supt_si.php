<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
$auth->requireLogin();

$user = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'නියෝජ්‍ය අධිකාරී අනුමැතිය - සිංහල';
include __DIR__ . '/../layouts/header.php';
?>

<!-- Main Container – exact Tailwind structure from approve_1_si.html -->
<div class="max-w-4xl mx-auto bg-white mt-6 p-8 shadow-md rounded-lg border border-gray-200">
    
    <!-- Header Actions: Back Button -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <a href="<?php echo baseUrl('dashboard'); ?>" class="inline-block bg-amber-400 hover:bg-amber-500 text-black font-bold py-2.5 px-5 rounded-md shadow transition duration-150 text-sm">
                &laquo; ඩැෂ්බෝඩ් වෙත
            </a>
        </div>
    </div>

    <!-- Title Section -->
    <div class="text-center border-b-2 border-[#0f4c81] pb-4 mb-6">
        <h1 class="text-2xl font-bold text-[#0f4c81] mb-1">ශ්‍රී ලංකා දුම්රිය දෙපාර්තමේන්තුව</h1>
        <h2 class="text-lg font-semibold text-gray-700">ප්‍රවාහන උප දෙපාර්තමේන්තුව</h2>
    </div>

    <!-- Form – action points to the backend submit endpoint -->
    <form action="<?php echo baseUrl('approval/deputy/submit'); ?>" method="POST">
        <fieldset class="bg-white border border-gray-300 rounded-lg p-6 shadow-sm mb-6">
            <legend class="text-sm font-bold text-[#0f4c81] px-3 py-1 bg-gray-100 border border-gray-200 rounded-md">
                නියෝජ්‍ය අධිකාරී (ගමනාගමන) කොළඹ
            </legend>

            <p class="text-sm font-medium text-gray-700 mt-4 mb-2">මා දන්නා තරමින්,</p>
            
            <ul class="text-sm text-gray-600 space-y-2 list-disc list-inside mb-6 leading-relaxed">
                <li>ඉහත සඳහන් තොරතුරු නිවැරදි බවත් / සංශෝධිත පරිදි නිවැරදි බවත්,</li>
                <li>අයදුම්කරු / කලත්‍රයා හෝ දරුවන් සතුව, සැතපුම් 15 කට මෙපිට නිවාස දේපලක් ඇති / නොමැති බවත්,</li>
                <li>මොහුගේ නිවාස අයදුම්පත්‍රය, නිර්දේශ කරන / නොකරන බවත් මෙයින් දන්වා සිටිමි.</li>
            </ul>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">අයදුම් කරන නිවාස වර්ගය හා අංකය:</label>
                <input type="text" name="rec_quarter_type_no" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#0f4c81] text-sm">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-1">සේවයට ඇති අත්‍යවශ්‍ය බව (රාත්‍රී වැඩ මුර වැනි) හා වෙනත් කරුණු ඇත්නම්:</label>
                <textarea name="rec_other_remarks" rows="3" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#0f4c81] text-sm"></textarea>
            </div>

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mt-6 pt-4 border-t border-gray-100">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">දිනය:</label>
                    <input type="date" name="rec_date" 
                        class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#0f4c81] text-sm">
                </div>

                <div style="text-align: right;">
                    <div style="border-bottom: 1px dotted #333; width: 280px; display: inline-block; margin-bottom: 5px;"></div>
                    <p style="margin: 0; font-size: 13px;">දු.ස්ථා.අධි / ප්‍ර.දු.ස්ථා. / දු.ස්ථා / උ.දු.ස්ථා. / ආසන්නම ජ්‍යෙෂ්ඨ නිලධාරියාගේ අත්සන හා නිල මුද්‍රාව</p>
                </div>
            </div>

            <div class="mt-6 text-center">
                <button type="submit" class="bg-[#0f4c81] hover:bg-[#0b3961] text-white font-medium text-sm px-6 py-2.5 rounded-md shadow transition duration-150">
                    අයදුම්පත්‍රය නිර්දේශ කරමි
                </button>
            </div>
        </fieldset>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>