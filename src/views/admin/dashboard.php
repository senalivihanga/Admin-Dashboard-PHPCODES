<?php
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$admin_name = $_SESSION['admin_name'];

// Map to user array expected by admin_header.php
$user = [
    'name' => $admin_name
];

$pageTitle = "Admin Dashboard - Sri Lanka Railways";

// Explicitly show admin dashboard title and profile button on the dashboard
$hide_admin_extras = false;

// Include Admin Specific Header
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<!-- Custom Tile & Layout Styles (Matched with view_system.php) -->
<style>
    .dashboard-flex-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
        align-items: center;
        width: 100%;
    }

    .row {
        display: flex;
        justify-content: center;
        gap: 15px;
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
        width: 270px;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .dash-tile:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    /* Top visual header inside each tile */
    .card-visual {
        width: 100%;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .dash-tile i.tile-main-icon {
        font-size: 1.6rem;
        transition: transform 0.3s ease;
    }

    .dash-tile:hover i.tile-main-icon {
        transform: scale(1.1);
    }

    /* Tile text content area */
    .card-content {
        padding: 10px 14px 14px 14px;
        display: flex;
        flex-direction: column;
        align-items: center;
        flex-grow: 1;
    }

    .dash-tile h3 {
        font-size: 0.95rem;
        color: #1e293b;
        margin: 0 0 4px 0;
        font-weight: 700;
    }

    .dash-tile p {
        font-size: 0.75rem;
        color: #64748b;
        margin: 0 0 10px 0;
        line-height: 1.3;
    }

    /* Button Style */
    .tile-btn {
        background-color: #7A1C1C;
        color: #ffffff;
        padding: 4px 14px;
        border-radius: 5px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        transition: all 0.2s ease;
        margin-top: auto;
        box-shadow: 0 2px 6px rgba(122, 28, 28, 0.2);
    }

    .dash-tile:hover .tile-btn {
        background-color: #5d1515;
        gap: 8px;
    }

    /* Color Gradients */
    .waiting-bg { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #7A1C1C; }
    .quarters-bg { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); color: #7A1C1C; }
    .assign-quarters-bg { background: linear-gradient(135deg, #fae8ff 0%, #f5d0fe 100%); color: #7A1C1C; }
    .users-bg { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); color: #7A1C1C; }
    .assign-bg { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #7A1C1C; }
    .system-bg { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); color: #7A1C1C; }

    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .dash-tile {
            width: 100%;
            max-width: 100%;
        }
        .row {
            gap: 10px;
        }
    }
</style>

<!-- Main Content Area -->
<main class="flex-grow max-w-5xl mx-auto w-full px-4 sm:px-6 py-4">
    <h2 class="text-lg sm:text-xl font-bold text-slate-800 mb-4">Welcome, <?php echo htmlspecialchars($admin_name); ?></h2>

    <!-- Dashboard Action Tiles (Matched with view_system sizing structure) -->
    <div class="dashboard-flex-container">
        
        <!-- Top Row: Tiles 1, 2, 3 -->
        <div class="row">
            <!-- Tile 1: Applicant Management -->
            <a href="view_users.php" class="dash-tile">
                <div class="card-visual users-bg">
                    <i class="fa-solid fa-users tile-main-icon"></i>
                </div>
                <div class="card-content">
                    <h3>Applicant Management</h3>
                    <p>Manage and review applications from potential users.</p>
                    <span class="tile-btn">Open <i class="fa-solid fa-arrow-right" style="font-size: 0.65rem;"></i></span>
                </div>
            </a>

            <!-- Tile 2: System User Management -->
            <a href="system-users.php" class="dash-tile">
                <div class="card-visual waiting-bg">
                    <i class="fa-solid fa-user-gear tile-main-icon"></i>
                </div>
                <div class="card-content">
                    <h3>System User Management</h3>
                    <p>Manage and configure system users and their permissions.</p>
                    <span class="tile-btn">Open <i class="fa-solid fa-arrow-right" style="font-size: 0.65rem;"></i></span>
                </div>
            </a>

            <!-- Tile 3: Approval System -->
            <a href="view_system.php" class="dash-tile">
                <div class="card-visual assign-bg">
                    <i class="fa-solid fa-clipboard-check tile-main-icon"></i>
                </div>
                <div class="card-content">
                    <h3>Approval System</h3>
                    <p>Review and process pending housing or administrative approvals.</p>
                    <span class="tile-btn">Open <i class="fa-solid fa-arrow-right" style="font-size: 0.65rem;"></i></span>
                </div>
            </a>
        </div>

        <!-- Bottom Row: Tiles 4, 5, 6 -->
        <div class="row">
            <!-- Tile 4: Waiting List -->
            <a href="waiting_lists.php" class="dash-tile">
                <div class="card-visual waiting-bg">
                    <i class="fa-solid fa-hourglass-half tile-main-icon"></i>
                </div>
                <div class="card-content">
                    <h3>Waiting List</h3>
                    <p>Track applicants currently queued for quarters allocation.</p>
                    <span class="tile-btn">Open <i class="fa-solid fa-arrow-right" style="font-size: 0.65rem;"></i></span>
                </div>
            </a>

            <!-- Tile 5: Quarters List -->
            <a href="quarters_list.php" class="dash-tile">
                <div class="card-visual quarters-bg">
                    <i class="fa-solid fa-house-chimney tile-main-icon"></i>
                </div>
                <div class="card-content">
                    <h3>Quarters List</h3>
                    <p>View availability, locations, and details of housing units.</p>
                    <span class="tile-btn">Open <i class="fa-solid fa-arrow-right" style="font-size: 0.65rem;"></i></span>
                </div>
            </a>

            <!-- Tile 6: Assign Quarters List -->
            <a href="allocated_quarters.php" class="dash-tile">
                <div class="card-visual assign-quarters-bg">
                    <i class="fa-solid fa-house-user tile-main-icon"></i>
                </div>
                <div class="card-content">
                    <h3>Assign Quarters List</h3>
                    <p>Allocate available housing quarters to approved applicants.</p>
                    <span class="tile-btn">Open <i class="fa-solid fa-arrow-right" style="font-size: 0.65rem;"></i></span>
                </div>
            </a>
        </div>

    </div>
</main>

<?php
// Include Admin Specific Footer
require_once __DIR__ . '/../layouts/admin_footer.php';
?>