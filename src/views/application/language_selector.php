<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
$auth->requireLogin();

$user = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Select Language - Quarter Application';
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../models/Application.php';
$appModel = new Application();
$pref = $appModel->getPreference($_SESSION['nic']);
$residenceType = $_GET['type'] ?? ($pref['residence_type'] ?? 'single');
if (!in_array($residenceType, ['single', 'joint_residence'], true)) {
    $residenceType = 'single';
}
?>

<style>
    /* Custom styles from language_dashboard.html – kept exactly */
    .dashboard-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 80vh;
        text-align: center;
        padding: 20px;
    }
    .dashboard-card {
        background: #ffffff;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        max-width: 500px;
        width: 100%;
    }
    .lang-btn {
        display: block;
        width: 100%;
        padding: 15px;
        margin: 15px 0;
        font-size: 18px;
        font-weight: bold;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        text-align: center;
    }
    .sinhala-btn {
        background-color: #0f4c81;
        color: white;
    }
    .sinhala-btn:hover {
        background-color: #0b3961;
    }
    .english-btn {
        background-color: #28a745;
        color: white;
    }
    .english-btn:hover {
        background-color: #218838;
    }
    /* Style for Back to Residence Dashboard Button */
    .back-dashboard-btn {
        display: inline-block;
        background-color: #f3f4f6;
        border: 1px solid #d1d5db;
        color: #374151;
        padding: 10px 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: background-color 0.2s;
    }
    .back-dashboard-btn:hover {
        background-color: #e5e7eb;
    }
</style>

<!-- Main Container -->
<div class="dashboard-wrapper">
    
    <!-- Back to Residence Dashboard Button Container -->
    <div style="width: 100%; max-width: 500px; margin-bottom: 15px; text-align: left;">
        <a href="<?php echo baseUrl('application/residence.php'); ?>" class="back-dashboard-btn">
            &larr; Back to Residence Dashboard
        </a>
    </div>

    <div class="dashboard-card">
        <h1 style="color: #0f4c81; font-size: 24px; margin-bottom: 10px;">දුම්රිය නිවාස අයදුම්පත්‍රය</h1>
        <h2 style="font-size: 18px; color: #555; margin-bottom: 30px;">Railway Quarters Application Form</h2>

        <?php if (!empty($_SESSION['preference_error'])): ?>
            <p role="alert" style="color:#b91c1c;"><?= htmlspecialchars($_SESSION['preference_error'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php unset($_SESSION['preference_error']); ?>
        <?php endif; ?>

        <?php if ($pref && !empty($pref['form_language'])): ?>
            <div style="margin-bottom:20px; padding:12px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px;">
                Previously selected: <strong><?= $pref['form_language'] === 'single_residence' ? 'සිංහල' : 'English'; ?></strong>
            </div>
        <?php endif; ?>
        
        <p style="margin-bottom: 25px; font-weight: 500;">ඔබගේ භාෂාව තෝරන්න / Please select your language:</p>
        
        <form action="<?php echo baseUrl('application/save-language'); ?>" method="POST" style="margin:15px 0;">
            <input type="hidden" name="language" value="single_residence">
            <input type="hidden" name="residence_type" value="<?= htmlspecialchars($residenceType, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" class="lang-btn sinhala-btn" style="width:100%; cursor:pointer;">සිංහල</button>
        </form>
        <form action="<?php echo baseUrl('application/save-language'); ?>" method="POST" style="margin:15px 0;">
            <input type="hidden" name="language" value="en">
            <input type="hidden" name="residence_type" value="<?= htmlspecialchars($residenceType, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" class="lang-btn english-btn" style="width:100%; cursor:pointer;">English</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>