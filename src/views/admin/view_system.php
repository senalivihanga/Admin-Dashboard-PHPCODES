<?php
/**
 * Approval Dashboards Page - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mock admin session if not set
if (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = 'Admin Name';
}

$admin_name = $_SESSION['admin_name'];

// Map to user array expected by admin_header.php
$user = [
    'name' => $admin_name
];

$pageTitle = "Approval Dashboard - Sri Lanka Railways"; // Title eka update kala
$hide_admin_extras = true; // Admin name button eka saha extras remove/hide karanna true kala

// Include Admin Specific Header
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
    /* Ensure body and html handle 100% height smoothly */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-y: auto; /* Content wadi unoth witharak scrollbar eka pennai */
    }

    .approval-container {
        max-width: 980px;
        margin: 5px auto;
        padding: 0 15px;
        box-sizing: border-box;
        width: 100%;
    }

    .dashboard-flex-container {
        display: flex;
        flex-direction: column;
        gap: 8px; /* Spacing adu kala 100% fit wenna */
        align-items: center;
    }

    .row {
        display: flex;
        justify-content: center;
        gap: 12px;
        width: 100%;
        flex-wrap: wrap;
    }

    /* Compact & Clean Tile Size for 100% View */
    .dash-tile {
        background: #ffffff;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        text-align: center;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        width: 265px;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .dash-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        border-color: #cbd5e1;
    }

    /* Top visual header inside each tile */
    .card-visual {
        width: 100%;
        height: 60px; /* Height eka poddak adu kala compact wenda */
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .dash-tile i.tile-main-icon {
        font-size: 1.5rem;
        transition: transform 0.3s ease;
    }

    .dash-tile:hover i.tile-main-icon {
        transform: scale(1.1);
    }

    /* Tile text content area */
    .card-content {
        padding: 8px 12px 12px 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        flex-grow: 1;
    }

    .dash-tile h3 {
        font-size: 0.9rem;
        color: #1e293b;
        margin: 0 0 3px 0;
        font-weight: 700;
    }

    .dash-tile p {
        font-size: 0.72rem;
        color: #64748b;
        margin: 0 0 8px 0;
        line-height: 1.3;
    }

    /* Button Style */
    .tile-btn {
        background-color: #7A1C1C;
        color: #ffffff;
        padding: 3px 12px;
        border-radius: 5px;
        font-weight: 600;
        font-size: 0.72rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        transition: all 0.2s ease;
        margin-top: auto;
        box-shadow: 0 2px 5px rgba(122, 28, 28, 0.2);
    }

    .dash-tile:hover .tile-btn {
        background-color: #5d1515;
        gap: 8px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 5px;
        color: #7A1C1C;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.82rem;
        transition: gap 0.2s;
    }

    .back-link:hover {
        gap: 10px;
        text-decoration: underline;
    }

    /* Centered Approval Dashboard Header Style */
    .approval-header-section {
        text-align: center;
        margin-bottom: 12px;
        width: 100%;
    }

    .approval-page-title {
        font-size: 1.25rem;
        color: #1e293b;
        font-weight: 700;
        margin: 0 0 2px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .approval-page-subtitle {
        font-size: 0.78rem;
        color: #64748b;
        margin: 0;
    }

    /* Color Gradients & Icon Colors */
    .bg-gradient-ib { background: linear-gradient(135deg, rgba(243, 232, 255, 0.95) 0%, rgba(216, 180, 254, 0.5) 100%); }
    .icon-ib { color: #7e22ce; }

    .bg-gradient-pf { background: linear-gradient(135deg, rgba(209, 250, 229, 0.95) 0%, rgba(167, 243, 208, 0.5) 100%); }
    .icon-pf { color: #047857; }

    .bg-gradient-sc { background: linear-gradient(135deg, rgba(224, 231, 255, 0.95) 0%, rgba(199, 210, 254, 0.5) 100%); }
    .icon-sc { color: #4338ca; }

    .bg-gradient-bo { background: linear-gradient(135deg, rgba(254, 243, 199, 0.95) 0%, rgba(253, 230, 138, 0.5) 100%); }
    .icon-bo { color: #b45309; }

    .bg-gradient-fa { background: linear-gradient(135deg, rgba(255, 228, 230, 0.95) 0%, rgba(254, 205, 211, 0.5) 100%); }
    .icon-fa { color: #be123c; }

    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .approval-container {
            padding: 0 8px;
            margin: 2px auto;
        }
        .dash-tile {
            width: 100%;
            max-width: 100%;
        }
        .row {
            gap: 8px;
        }
    }
</style>

<!-- Main Content Area -->
<main class="approval-container flex-grow py-2">
    <!-- Back Link -->
    <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>

    <!-- Centered Approval Dashboard Topic Added Here -->
    <div class="approval-header-section">
        <h1 class="approval-page-title">
            <i class="fa-solid fa-shield-halved text-[#7A1C1C]"></i> Approval Dashboard
        </h1>
        <p class="approval-page-subtitle">Manage and review applications through the approval process.</p>
    </div>

    <!-- Dashboard Grid Container -->
    <div class="dashboard-flex-container">
        <!-- Top Row: 3 Tiles -->
        <div class="row">
            <!-- 1. Immediate Boss (IB) -->
            <a href="IB_Dashboard.php" class="dash-tile">
                <div class="card-visual bg-gradient-ib">
                    <i class="fa-solid fa-user-tie tile-main-icon icon-ib"></i>
                </div>
                <div class="card-content">
                    <h3>Immediate Boss</h3>
                    <p>Manage and review requests at immediate supervisor level.</p>
                    <span class="tile-btn">Open <i class="fa-solid fa-arrow-right" style="font-size: 0.6rem;"></i></span>
                </div>
            </a>

            <!-- 2. Personal File (PF) -->
            <a href="PF_Dashboard.php" class="dash-tile">
                <div class="card-visual bg-gradient-pf">
                    <i class="fa-solid fa-folder-open tile-main-icon icon-pf"></i>
                </div>
                <div class="card-content">
                    <h3>Personal File</h3>
                    <p>Verify records and documents from employee personal files.</p>
                    <span class="tile-bar tile-btn">Open <i class="fa-solid fa-arrow-right" style="font-size: 0.6rem;"></i></span>
                </div>
            </a>

            <!-- 3. Subject Clerk (SC) -->
            <a href="SC_dashboard.php" class="dash-tile">
                <div class="card-visual bg-gradient-sc">
                    <i class="fa-solid fa-user-pen tile-main-icon icon-sc"></i>
                </div>
                <div class="card-content">
                    <h3>Subject Clerk</h3>
                    <p>Process initial applications and subject-related entries.</p>
                    <span class="tile-btn">Open <i class="fa-solid fa-arrow-right" style="font-size: 0.6rem;"></i></span>
                </div>
            </a>
        </div>

        <!-- Bottom Row: 2 Tiles -->
        <div class="row">
            <!-- 4. Branch Officer (BO) -->
            <a href="BO_Dashboard.php" class="dash-tile">
                <div class="card-visual bg-gradient-bo">
                    <i class="fa-solid fa-building-user tile-main-icon icon-bo"></i>
                </div>
                <div class="card-content">
                    <h3>Branch Officer</h3>
                    <p>Review and authorize workflows at branch officer level.</p>
                    <span class="tile-btn">Open <i class="fa-solid fa-arrow-right" style="font-size: 0.6rem;"></i></span>
                </div>
            </a>

            <!-- 5. Final Approval (FA) -->
            <a href="FA_dashboard.php" class="dash-tile">
                <div class="card-visual bg-gradient-fa">
                    <i class="fa-solid fa-circle-check tile-main-icon icon-fa"></i>
                </div>
                <div class="card-content">
                    <h3>Final Approval</h3>
                    <p>Provide final authorization and complete workflow requests.</p>
                    <span class="tile-btn">Open <i class="fa-solid fa-arrow-right" style="font-size: 0.6rem;"></i></span>
                </div>
            </a>
        </div>
    </div>
</main>

<?php
// Include Admin Specific Footer
require_once __DIR__ . '/../layouts/admin_footer.php';
?>