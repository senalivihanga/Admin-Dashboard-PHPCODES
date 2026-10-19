<?php
// view_user_details.php - පරිශීලක විස්තර බැලීමේ පිටුව
// ශ්‍රී ලංකා දුම්රිය දෙපාර්තමේන්තුව - නිවාස කළමනාකරණ පද්ධතිය

// සැසිය (Session) ආරම්භ කර නොමැති නම් ආරම්භ කිරීම
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// පිටුවේ මාතෘකාව සැකසීම
$pageTitle = "View User Details - Sri Lanka Railways";

// Admin Header එක ඇතුළත් කිරීම[cite: 1]
require_once __DIR__ . '/../layouts/admin_header.php';

// Database Connection (PDO) - Modify credentials according to your database
$host = 'localhost';
$db   = 'railways_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Fallback demo mode if DB is not connected yet
     $pdo = null;
}

// URL එකෙන් ලැබෙන nic හෝ id (index) අංකය ලබා ගැනීම
$nic = $_GET['nic'] ?? '';
$userId = $_GET['id'] ?? ($_GET['index'] ?? '');

$user_data = null;
$auto_index = 1;
$errorMessage = "";

// 1. Database එක හරහා දත්ත සෙවීම
if ($pdo) {
    if ($nic !== '') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE nic = ?");
        $stmt->execute([$nic]);
        $user_data = $stmt->fetch();
    } elseif ($userId !== '') {
        // ID එක මගින් හෝ Offset මගින් දත්ත සෙවීම
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user_data = $stmt->fetch();
        
        if (!$user_data) {
            $stmt = $pdo->prepare("SELECT * FROM users LIMIT 1 OFFSET ?");
            $stmt->execute([(int)$userId]);
            $user_data = $stmt->fetch();
        }
        $auto_index = (int)$userId + 1;
    } else {
        $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
        $user_data = $stmt->fetch();
    }
}

// 2. Database එකේ නැත්නම් හෝ PDO නොමැති නම් Session දත්ත (view_users.php මඟින් එවන දත්ත) පරීක්ෂා කිරීම
if (!$user_data && isset($_SESSION['system_users_data_v2']) && !empty($_SESSION['system_users_data_v2'])) {
    foreach ($_SESSION['system_users_data_v2'] as $index => $user_item) {
        if (($nic !== '' && ($user_item['nic'] ?? '') === $nic) || 
            ($userId !== '' && ((string)($user_item['id'] ?? '') === (string)$userId || $index == (int)$userId))) {
            $user_data = $user_item;
            $auto_index = $index + 1;
            break;
        }
    }
}

// 3. ඒවාත් නැත්නම් Default Sample දත්ත පරීක්ෂා කිරීම
if (!$user_data) {
    $defaultUsers = [
        ['nic' => '199812345678', 'computer_no' => '405', 'full_name' => 'A. B. Perera', 'email' => 'abperera@railway.gov.lk', 'phone' => '0771234567'],
        ['nic' => '199287654321', 'computer_no' => '406', 'full_name' => 'S. K. Silva', 'email' => 'sksilva@railway.gov.lk', 'phone' => '0719876543']
    ];
    
    if ($nic !== '') {
        foreach ($defaultUsers as $index => $u) {
            if ($u['nic'] === $nic) {
                $user_data = $u;
                $auto_index = $index + 1;
                break;
            }
        }
    } elseif ($userId !== '' && isset($defaultUsers[$userId])) {
        $user_data = $defaultUsers[$userId];
        $auto_index = (int)$userId + 1;
    } else {
        $user_data = $defaultUsers[0] ?? null;
    }
}

// පරිශීලකයා හමු වූවා නම් අදාළ විචල්‍යයන්ට දත්ත ලබා දීම, නැතහොත් දෝෂ පණිවිඩයක් පෙන්වීම
if (!$user_data) {
    $errorMessage = "මෙම පරිශීලකයා සඳහා දත්ත හමු නොවීය.";
} else {
    $autoIndexId   = $auto_index;
    $nicVal        = $user_data['nic'] ?? ($user_data['NIC'] ?? 'N/A');
    $computerNum   = $user_data['computer_no'] ?? ($user_data['computerNo'] ?? 'N/A');
    $fullName      = $user_data['full_name'] ?? ($user_data['name'] ?? ($user_data['fullName'] ?? 'N/A'));
    $email         = $user_data['email'] ?? 'N/A';
    $mobile        = $user_data['phone'] ?? ($user_data['mobile'] ?? 'N/A');
    $status        = "Active";
}
?>

<!-- අමතර CSS හැඩතල (Styles)[cite: 1] -->
<style>
    .container-details { 
        max-width: 700px; 
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
        margin-bottom: 20px; 
        text-transform: uppercase; 
        text-align: center; 
    }

    .app-badge { 
        display: inline-flex; 
        align-items: center; 
        gap: 6px; 
        padding: 4px 12px; 
        border-radius: 20px; 
        font-weight: 700; 
        font-size: 0.85rem; 
        background-color: #dcfce7; 
        color: #166534; 
        border: 1px solid #bbf7d0; 
    }

    .detail-row { 
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        padding: 14px 0; 
        border-bottom: 1px solid #e2e8f0; 
        font-size: 0.95rem; 
        word-break: break-word;
    }

    .detail-row span:first-child { 
        font-weight: 600; 
        color: #475569; 
        flex-shrink: 0;
        margin-right: 15px;
    }

    .detail-row span:last-child { 
        color: #1e293b; 
        text-align: right;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .app-content-box { 
        margin-top: 25px; 
        padding: 20px; 
        background: #f0fdf4; 
        border: 1px dashed #86efac; 
        border-radius: 8px; 
        text-align: center; 
    }

    .app-content-box h4 { 
        margin-top: 0;
        margin-bottom: 8px; 
        color: #166534; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        gap: 8px; 
    }

    .app-content-box p { 
        color: #15803d; 
        margin: 0; 
        font-size: 0.95rem; 
    }

    .error-box {
        background: #fef2f2;
        border: 1px dashed #fca5a5;
        color: #991b1b;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        margin-top: 20px;
    }

    /* ජංගම දුරකථන සඳහා ප්‍රතිචාර දැක්වීම (Responsive Design)[cite: 1] */
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
            font-size: 1.25rem;
        }

        .detail-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
            padding: 12px 0;
        }

        .detail-row span:last-child {
            text-align: left;
            justify-content: flex-start;
        }
    }
</style>

<div class="container-details">
    <!-- පරිශීලක ලැයිස්තුවට නැවත යාමේ ලින්ක් එක (view_users.php වෙත) -->
    <a href="view_users.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to User List</a>

    <div class="card-box">
        <h2><i class="fa-solid fa-circle-info"></i> User Detailed View</h2>

        <?php if (!empty($errorMessage)): ?>
            <!-- දෝෂ පණිවිඩය පෙන්වන කොටස -->
            <div class="error-box">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php else: ?>
            <!-- Auto Index ID -->
            <div class="detail-row">
                <span>Auto Index ID:</span>
                <span><?php echo htmlspecialchars($autoIndexId); ?></span>
            </div>
            <!-- NIC අංකය -->
            <div class="detail-row">
                <span>NIC Number:</span>
                <span><?php echo htmlspecialchars($nicVal); ?></span>
            </div>
            <!-- පරිගණක අංකය -->
            <div class="detail-row">
                <span>Computer Number:</span>
                <span><?php echo htmlspecialchars($computerNum); ?></span>
            </div>
            <!-- සම්පූර්ණ නම -->
            <div class="detail-row">
                <span>Full Name:</span>
                <span><?php echo htmlspecialchars($fullName); ?></span>
            </div>
            <!-- විද්‍යුත් තැපෑල -->
            <div class="detail-row">
                <span>Email Address:</span>
                <span><?php echo htmlspecialchars($email); ?></span>
            </div>
            <!-- ජංගම දුරකථන අංකය -->
            <div class="detail-row">
                <span>Mobile Number:</span>
                <span><?php echo htmlspecialchars($mobile); ?></span>
            </div>
            <!-- ගිණුමේ තත්ත්වය (Account Status) -->
            <div class="detail-row">
                <span>Account Status:</span>
                <span>
                    <span class="app-badge"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($status); ?></span>
                </span>
            </div>

            
        <?php endif; ?>
    </div>
</div>

<?php
// Admin Footer එක ඇතුළත් කිරීම[cite: 1]
require_once __DIR__ . '/../layouts/admin_footer.php';
?>