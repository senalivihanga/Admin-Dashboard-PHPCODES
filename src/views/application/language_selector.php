<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
$auth->requireLogin();

$user = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Select Language - Quarter Application';
include __DIR__ . '/../layouts/header.php';

// Read the 'type' parameter from the URL (e.g., ?type=joint)
$type = isset($_GET['type']) ? '?type=' . urlencode($_GET['type']) : '';
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
        
        <p style="margin-bottom: 25px; font-weight: 500;">ඔබගේ භාෂාව තෝරන්න / Please select your language:</p>
        
        <!-- Sinhala link – appends type parameter if present -->
        <a href="<?php echo baseUrl('application/form/si' . $type); ?>" class="lang-btn sinhala-btn">සිංහල</a>
        <!-- English link – appends type parameter if present -->
        <a href="<?php echo baseUrl('application/form/en' . $type); ?>" class="lang-btn english-btn">English</a>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>