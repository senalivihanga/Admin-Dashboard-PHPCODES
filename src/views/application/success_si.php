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
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>අයදුම්පත සාර්ථකයි - ශ්‍රී ලංකා දුම්රිය දෙපාර්තමේන්තුව</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .content-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            width: 100%;
        }
        .success-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .success-icon {
            font-size: 50px;
            color: #28a745;
            margin-bottom: 20px;
        }
        h2 {
            color: #0f4c81;
            margin-bottom: 15px;
            font-size: 24px;
        }
        p {
            color: #333;
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 25px;
        }
        .app-number {
            font-weight: bold;
            color: #007bff;
            background: #e9ecef;
            padding: 10px 15px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 25px;
            font-size: 18px;
            word-break: break-all;
        }
        .btn-home {
            background-color: #0f4c81;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .btn-home:hover {
            background-color: #0d3d6b;
        }

        /* Footer එකේ සහ එහි ඇතුළත් සියලුම කොටස්වල අකුරු සුදු පාට කිරීම සඳහා */
        footer, .site-footer, .footer, footer *, .site-footer *, .footer * {
            color: white !important;
        }

        /* Mobile Responsiveness Improvements */
        @media (max-width: 480px) {
            .success-card {
                padding: 25px 15px;
            }
            h2 {
                font-size: 20px;
            }
            p {
                font-size: 14px;
            }
            .success-icon {
                font-size: 40px;
                margin-bottom: 15px;
            }
            .app-number {
                font-size: 16px;
                padding: 8px 12px;
            }
            .btn-home {
                width: 100%;
                padding: 12px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>

    <div class="content-wrapper">
        <div class="success-card">
            <div class="success-icon">&#10004;</div>
            <h2>අයදුම්පත සාර්ථකව යොමු කරන ලදී!</h2>
            <p>ශ්‍රී ලංකා දුම්රිය දෙපාර්තමේන්තුවේ නිවාස අයදුම්පත සාර්ථකව භාර ගන්නා ලදී. ඔබගේ යොමු අංකය පහතින් දැක්වේ:</p>

            <div>
                <?php
                $appNo = isset($_GET['appNo']) ? htmlspecialchars($_GET['appNo']) : 'SLR/AOT/2026/0001';
                ?>
                <span class="app-number"><?php echo $appNo; ?></span>
            </div>

            <div>
                <a href="<?php echo baseUrl('dashboard'); ?>" class="btn-home">මුල් පිටුවට යන්න</a>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>