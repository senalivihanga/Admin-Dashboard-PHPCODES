<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
$auth->requireLogin();

$user = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Residence Selection - Railway Quarters';
include __DIR__ . '/../layouts/header.php';
?>

<style>
    /* Box-sizing පමණක් සියල්ලට දෙන්න, font-family එක header එකට බලපෑමක් නොවන පරිදි .main-container එකට පමණක් සීමා කරන්න */
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    body {
        background-color: #f8f9fa;
        color: #333;
        min-height: 100vh;
    }
    .main-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 0 20px;
        font-family: Arial, sans-serif; /* මෙතැනට පමණක් font එක යෙදීමෙන් Header එක ආරක්ෂා වේ */
    }
    .dashboard-card {
        background: white;
        padding: 35px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        text-align: center;
    }
    .dashboard-card h1 {
        font-size: 24px;
        color: #0f4c81;
        margin-bottom: 5px;
    }
    .dashboard-card h2 {
        font-size: 16px;
        color: #4b5563;
        margin-bottom: 25px;
        font-weight: 600;
    }
    .dashboard-card p {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 30px;
    }
    .tabs-container {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 25px;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 15px;
        flex-wrap: wrap;
    }
    .tab-btn {
        padding: 10px 22px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
        background-color: #e5e7eb;
        color: #374151;
    }
    .tab-btn.active {
        background-color: #0f4c81;
        color: white;
        box-shadow: 0 2px 6px rgba(15, 76, 129, 0.3);
    }
    .tab-btn:hover:not(.active) {
        background-color: #d1d5db;
    }
    .tab-panel-container {
        text-align: left;
        background-color: #f9fafb;
        padding: 25px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        min-height: 180px;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    .tab-content h3 {
        font-size: 18px;
        color: #0a365c;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .tab-content p {
        font-size: 14px;
        color: #4b5563;
        margin-bottom: 20px;
    }
    .action-btn {
        display: inline-block;
        background-color: #0f4c81;
        color: white;
        font-size: 14px;
        font-weight: 500;
        padding: 10px 20px;
        border-radius: 4px;
        text-decoration: none;
        transition: background-color 0.2s;
        border: none;
        cursor: pointer;
    }
    .action-btn:hover {
        background-color: #0b3961;
    }
    .action-btn.maroon {
        background-color: #701515;
    }
    .action-btn.maroon:hover {
        background-color: #5a1010;
    }
</style>

<!-- Main Container -->
<div class="main-container">
    <!-- Back to Applicant Dashboard Button with updated styling -->
    <div class="flex items-center justify-between pt-4 pb-4 border-gray-100">
        <a href="<?php echo baseUrl('dashboard'); ?>" class="inline-flex items-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-3 rounded-xl transition shadow-sm text-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Applicant Dashboard
        </a>
    </div>

    <div class="dashboard-card">
        <h1>Railway Quarters Management</h1>
        <h2>Railway Quarters Residence Selection Dashboard</h2>
        <p>Please select the category of residence configuration below to proceed with the relevant forms and management tools:</p>

        <!-- Tabs Navigation -->
        <div class="tabs-container">
            <button onclick="switchTab('joint')" id="btn-joint" class="tab-btn active">Joint Residence</button>
            <button onclick="switchTab('single')" id="btn-single" class="tab-btn">Single Residence</button>
        </div>

        <!-- Tab Panel Container -->
        <div class="tab-panel-container">
            <!-- Joint Residence Content -->
            <div id="content-joint" class="tab-content active">
                <h3>Joint Residence Management</h3>
                <p>Managing and processing applications for another resident to stay or co-occupy the quarters with the consent of the primary resident.</p>
                <a href="<?php echo baseUrl('application/apply.php?tab=joint'); ?>" class="action-btn maroon">Open Joint Residence &rarr;</a>
            </div>

            <!-- Single Residence Content -->
            <div id="content-single" class="tab-content">
                <h3>Single Residence Allocation</h3>
                <p>Allocation of a separate quarter exclusively for a single family only, along with managing applications and related information.</p>
                <a href="<?php echo baseUrl('application/select-language?type=single'); ?>" class="action-btn maroon">Open Application Form &rarr;</a>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Tab Switching -->
<script>
    function switchTab(tabName) {
        const contents = document.querySelectorAll('.tab-content');
        contents.forEach(content => content.classList.remove('active'));

        const buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(btn => btn.classList.remove('active'));

        document.getElementById('content-' + tabName).classList.add('active');
        document.getElementById('btn-' + tabName).classList.add('active');
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>