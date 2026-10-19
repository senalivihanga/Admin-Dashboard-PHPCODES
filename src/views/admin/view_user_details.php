<?php
/**
 * View User Details - Sri Lanka Railways
 * Fetches a single applicant's details directly from the `users` table.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/config.php';

$pageTitle = "View User Details - Sri Lanka Railways";

$nic = $_GET['nic'] ?? '';
$user_data = null;
$errorMessage = '';

if ($nic !== '') {
    $conn = getDBConnection();
    $stmt = $conn->prepare(
        "SELECT nic, name, email, computer_number, mobile, created_at
           FROM users
          WHERE nic = ? AND role_id = 1
          LIMIT 1"
    );
    $stmt->bind_param("s", $nic);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if (!$user_data) {
        $errorMessage = "User not found for the provided NIC.";
    }
} else {
    $errorMessage = "No NIC provided. Please go back to the user list and click View on a row.";
}

// Hide the admin dashboard title and profile dropdown on this inner page
$hide_admin_extras = true;

// Include Admin Header layout
require_once __DIR__ . '/../layouts/admin_header.php';
?>

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
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
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

    .detail-row:last-of-type {
        border-bottom: none;
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

    .error-box {
        background: #fef2f2;
        border: 1px dashed #fca5a5;
        color: #991b1b;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        margin-top: 20px;
    }

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
    <!-- Back to User List -->
    <a href="<?php echo baseUrl('admin/view_users.php'); ?>" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to User List
    </a>

    <div class="card-box">
        <h2><i class="fa-solid fa-circle-info"></i> User Detailed View</h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="error-box">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php else: ?>

            <div class="detail-row">
                <span>NIC Number:</span>
                <span><?php echo htmlspecialchars($user_data['nic'] ?? 'N/A'); ?></span>
            </div>

            <div class="detail-row">
                <span>Computer Number:</span>
                <span><?php echo htmlspecialchars($user_data['computer_number'] ?? 'N/A'); ?></span>
            </div>

            <div class="detail-row">
                <span>Full Name:</span>
                <span><?php echo htmlspecialchars($user_data['name'] ?? 'N/A'); ?></span>
            </div>

            <div class="detail-row">
                <span>Email Address:</span>
                <span><?php echo htmlspecialchars($user_data['email'] ?? 'N/A'); ?></span>
            </div>

            <div class="detail-row">
                <span>Mobile Number:</span>
                <span><?php echo htmlspecialchars($user_data['mobile'] ?? 'N/A'); ?></span>
            </div>

            <div class="detail-row">
                <span>Registered On:</span>
                <span><?php echo htmlspecialchars($user_data['created_at'] ?? 'N/A'); ?></span>
            </div>

            <div class="detail-row">
                <span>Account Status:</span>
                <span>
                    <span class="app-badge">
                        <i class="fa-solid fa-circle-check"></i> Active
                    </span>
                </span>
            </div>

        <?php endif; ?>
    </div>
</div>

<?php
// Include Admin Footer layout
require_once __DIR__ . '/../layouts/admin_footer.php';
?>