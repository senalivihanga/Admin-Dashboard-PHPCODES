<?php
/**
 * View System User Details Page
 */

// සැසිය දැනටමත් ආරම්භ වී ඇත්දැයි පරීක්ෂා කර බැලීම
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// පිටු මාතෘකාව සැකසීම
$pageTitle = "View System User Details - Sri Lanka Railways";

// ඩේටාබේස් සම්බන්ධතාවය (Database Connection)
$host = 'localhost';
$db   = 'admin_dashboard_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// URL එකෙන් 'id' ලබා ගැනීම (Get ID from URL)
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$user_details = null;
if ($id > 0) {
    // ඩේටාබේස් එකෙන් අදාළ පරිශීලක දත්ත ලබා ගැනීම (Fetch user details from database)
    $stmt = $pdo->prepare("SELECT * FROM system_users WHERE id = ?");
    $stmt->execute([$id]);
    $user_details = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Admin Header ඇතුළත් කිරීම
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<!-- UI Styles (දෙපැත්තට පෙන්වන Two-column grid මෝස්තරය) -->
<style>
    .container-details { 
        max-width: 900px; 
        margin: 40px auto; 
        padding: 0 15px; 
        width: 100%;
        box-sizing: border-box;
    }

    .back-link { 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        color: #7A1C1C; 
        text-decoration: none; 
        font-weight: 600; 
        margin-bottom: 20px; 
        transition: color 0.2s;
    }

    .back-link:hover {
        color: #5a1313;
    }

    .card-box { 
        background: #ffffff; 
        padding: 35px; 
        border-radius: 15px; 
        box-shadow: 0 10px 25px rgba(0,0,0,0.08); 
        border-top: 5px solid #7A1C1C; 
    }

    .card-box h2 { 
        color: #1e293b; 
        font-size: 1.6rem; 
        margin-bottom: 25px; 
        text-transform: uppercase; 
        text-align: center; 
    }

    /* දෙපැත්තට බෙදී යන Grid පද්ධතිය (Two Columns) */
    .details-grid { 
        display: grid; 
        grid-template-columns: repeat(2, minmax(0, 1fr)); 
        column-gap: 32px; 
    }

    .detail-row { 
        display: flex; 
        flex-direction: column; 
        gap: 6px; 
        padding: 15px 0; 
        border-bottom: 1px solid #e2e8f0; 
        min-width: 0;
    }

    .label { 
        color: #64748b; 
        font-size: 0.78rem; 
        font-weight: 700; 
        text-transform: uppercase; 
    }

    .value { 
        color: #17212b; 
        font-weight: 600; 
        font-size: 0.98rem; 
        overflow-wrap: anywhere; 
    }

    .error-box {
        background: #fef2f2;
        border: 1px dashed #fca5a5;
        color: #991b1b;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        grid-column: span 2;
    }

    /* ජංගම දුරකථන සඳහා ප්‍රතිචාර දැක්වීම (Mobile Responsive Design - එක පේළියකට හැරේ) */
    @media (max-width: 600px) {
        .container-details {
            margin: 20px 10px;
            padding: 0 10px;
        }

        .card-box {
            padding: 20px 15px;
            border-radius: 10px;
        }

        .card-box h2 {
            font-size: 1.3rem;
        }

        .details-grid {
            grid-template-columns: 1fr;
        }

        .error-box {
            grid-column: span 1;
        }
    }
</style>

<div class="container-details">
    <!-- පරිශීලක ලැයිස්තුව වෙත ආපසු යාමේ සබැඳිය (Back link to system users list) -->
    <a href="system-users.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to System Users List</a>

    <div class="card-box">
        <h2>SYSTEM USER DETAILED VIEW</h2>

        <div class="details-grid">
            <?php if (!$user_details): ?>
                <!-- පරිශීලකයා හමු නොවූ විට පෙන්වන දෝෂ පණිවිඩය (Error message if user not found) -->
                <div class="error-box">
                    <i class="fa-solid fa-triangle-exclamation"></i> System User not found!
                </div>
            <?php else: ?>
                
                <!-- ID / No. -->
                <div class="detail-row">
                    <span class="label">No.:</span>
                    <span class="value"><?php echo htmlspecialchars($user_details['id'] ?? 'N/A'); ?></span>
                </div>
                <!-- NIC Number -->
                <div class="detail-row">
                    <span class="label">NIC Number:</span>
                    <span class="value"><?php echo htmlspecialchars($user_details['nic'] ?? 'N/A'); ?></span>
                </div>
                <!-- Computer Number -->
                <div class="detail-row">
                    <span class="label">Computer Number:</span>
                    <span class="value"><?php echo htmlspecialchars($user_details['computer_no'] ?? 'N/A'); ?></span>
                </div>
                <!-- Name -->
                <div class="detail-row">
                    <span class="label">Name:</span>
                    <span class="value"><?php echo htmlspecialchars($user_details['full_name'] ?? 'N/A'); ?></span>
                </div>
                <!-- Designation -->
                <div class="detail-row">
                    <span class="label">Designation:</span>
                    <span class="value"><?php echo htmlspecialchars($user_details['designation'] ?? 'N/A'); ?></span>
                </div>
                <!-- System Role -->
                <div class="detail-row">
                    <span class="label">System Role:</span>
                    <span class="value"><?php echo htmlspecialchars($user_details['role'] ?? 'N/A'); ?></span>
                </div>
                <!-- Current Workplace -->
                <div class="detail-row">
                    <span class="label">Current Workplace:</span>
                    <span class="value"><?php echo htmlspecialchars($user_details['workplace'] ?? 'N/A'); ?></span>
                </div>
                <!-- Email Address -->
                <div class="detail-row">
                    <span class="label">Email Address:</span>
                    <span class="value"><?php echo htmlspecialchars($user_details['email'] ?? 'N/A'); ?></span>
                </div>
                <!-- Phone Number -->
                <div class="detail-row">
                    <span class="label">Phone Number:</span>
                    <span class="value"><?php echo htmlspecialchars($user_details['phone'] ?? 'N/A'); ?></span>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Admin Footer ඇතුළත් කිරීම
require_once __DIR__ . '/../layouts/admin_footer.php';
?>