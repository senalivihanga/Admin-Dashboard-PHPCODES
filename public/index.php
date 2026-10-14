<?php
/**
 * Front Controller - Entry point for all routes
 */

// Start session ONLY if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// LOAD CONFIGURATION
// ==========================================
require_once __DIR__ . '/../config/config.php';

// ==========================================
// AUTOLOADING
// ==========================================
spl_autoload_register(function ($class) {
    $base_dir = __DIR__ . '/../src/';
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ==========================================
// AUTHENTICATION CHECK
// ==========================================
function checkAuth() {
    // Get the current path without base URL
    // FIX: rawurldecode() so %20 (space) and other encoded chars match BASE_URL correctly
    $currentPath = rawurldecode($_SERVER['REQUEST_URI']);

    // Remove base URL from path
    if (strpos($currentPath, BASE_URL) === 0) {
        $currentPath = substr($currentPath, strlen(BASE_URL));
    }

    // Remove /public from path if present
    if (strpos($currentPath, '/public') === 0) {
        $currentPath = substr($currentPath, 7);
    }

    // Remove query string
    if (strpos($currentPath, '?') !== false) {
        $currentPath = strtok($currentPath, '?');
    }

    // Clean up the path
    $currentPath = rtrim($currentPath, '/');
    if (empty($currentPath)) {
        $currentPath = '/';
    }

    // Define public pages (no login required)
    $publicPaths = ['/', '/login', '/register', '/logout'];
    foreach ($publicPaths as $publicPath) {
        if ($currentPath === $publicPath || strpos($currentPath, $publicPath . '?') === 0) {
            return true;
        }
    }

    // If accessing public/index.php directly, allow it
    if (strpos($currentPath, '/public/index.php') !== false) {
        return true;
    }

    // Check if user is logged in
    if (!isset($_SESSION['nic']) || !isset($_SESSION['session_valid'])) {
        // Store the intended URL for redirect after login (CLEAN VERSION)
        // FIX: rawurldecode() here too, for the same reason as above
        $cleanPath = rawurldecode($_SERVER['REQUEST_URI']);
        // Remove base URL
        if (strpos($cleanPath, BASE_URL) === 0) {
            $cleanPath = substr($cleanPath, strlen(BASE_URL));
        }
        // Remove /public if present
        if (strpos($cleanPath, '/public') === 0) {
            $cleanPath = substr($cleanPath, 7);
        }
        // Remove query string if present
        if (strpos($cleanPath, '?') !== false) {
            $cleanPath = strtok($cleanPath, '?');
        }
        // Ensure it starts with /
        if (!empty($cleanPath) && $cleanPath[0] !== '/') {
            $cleanPath = '/' . $cleanPath;
        }
        $_SESSION['redirect_after_login'] = $cleanPath;

        header('Location: ' . baseUrl('login'));
        exit();
    }

    return true;
}

// ==========================================
// ROUTE HANDLER
// ==========================================
function route($path, $method = 'GET') {
    // FIX: decode the incoming path too, so BASE_URL stripping works the same way here
    $path = rawurldecode($path);

    // Remove query string
    if (strpos($path, '?') !== false) {
        $path = strtok($path, '?');
    }

    // Remove base path if running in subdirectory
    if (strpos($path, BASE_URL) === 0) {
        $path = substr($path, strlen(BASE_URL));
    }

    // Remove /public from path if present
    if (strpos($path, '/public') === 0) {
        $path = substr($path, 7);
    }

    if (empty($path) || $path === '/' || $path === '') {
        $path = '/home';
    }

        // ---------- ROLE-BASED ACCESS ----------
    require_once __DIR__ . '/../src/helpers/roles.php';

    // Admin area: only staff roles, and only their own pages
    if (strpos($path, '/admin/') === 0) {
        if (!canAccessAdminPath($path)) {
            http_response_code(403);
            echo '<h1>403 - Access Denied</h1>';
            echo '<p>You do not have permission to view this page.</p>';
            echo '<p><a href="' . baseUrl(roleHomePath(currentRoleId() ?: 1)) . '">Go to my dashboard</a></p>';
            return;
        }
    }

    // Staff should not land on the applicant dashboard
    if ($path === '/dashboard' && currentRoleId() >= 2) {
        header('Location: ' . baseUrl(roleHomePath(currentRoleId())));
        exit();
    }
    // ---------------------------------------

    // Define routes - map URL paths to view files
    $routes = [
        // Existing routes
        '/home' => '/home.php',
        '/dashboard'                 => '/dashboard/index.php',
        '/application/request'       => '/dashboard/request_quarters.php',
        '/application/status'        => '/dashboard/view_status.php',
        '/waiting-list'              => '/dashboard/waiting_list.php',
        '/offer/respond'             => '/dashboard/view_offers.php',
        '/notifications'             => '/notifications/notification.php',
        '/profile/edit'              => '/profile/edit_profile.php',
        '/login'                     => '/auth/login.php',
        '/register'                  => '/auth/register.php',
        '/logout'                    => '/auth/logout.php',

        // --- NEW INQUIRY / COMPLAINT ROUTES ---
        '/complaints/new'           => '/dashboard/new_complaint.php',

        // Routes for the application forms and approvals
        '/application/select-language' => '/application/language_selector.php',
        '/application/residence' => '/application/residence.php',
        '/application/form/si'         => '/application/form_si.php',
        '/application/form/en'         => '/application/form_en.php',
        '/application/success/en'      => '/application/success_en.php',
        '/application/success/si'      => '/application/success_si.php',
        '/approval/deputy/en'          => '/approval/deputy_supt_en.php',
        '/approval/deputy/si'          => '/approval/deputy_supt_si.php',
        '/approval/clerk/en'           => '/approval/clerk_en.php',
        '/approval/clerk/si'           => '/approval/clerk_si.php',
        '/approval/marks'              => '/approval/marking_scheme.php',

        // +++ NEW ADMIN ROUTES +++
        '/admin/dashboard'            => '/admin/dashboard.php',
        '/admin/users'                => '/admin/users.php',
        '/admin/assign'               => '/admin/assign.php',
        '/admin/approvals'            => '/admin/approvals.php',
        '/admin/waiting-lists'        => '/admin/waiting_lists.php',
        '/admin/quarters'             => '/admin/quarters.php',
    ];

    // Handle AJAX requests first
    if ($method === 'POST' && isset($_POST['ajax_action'])) {
        handleAjaxRequest($_POST['ajax_action'], $_POST);
        return;
    }

    // ==========================================
    // CONTROLLER ROUTE HANDLING (e.g., complaints/store)
    // ==========================================
    if ($path === '/complaints/store' && $method === 'POST') {
        require_once __DIR__ . '/../src/controllers/ComplaintController.php';
        $controller = new ComplaintController();
        $controller->store();
        return;
    }

    if ($path === '/offer/respond' && $method === 'POST') {
        require_once __DIR__ . '/../src/controllers/OfferController.php';
        $controller = new OfferController();
        $controller->respond();
        return;
    }

    // ==========================================
// PREFERENCE SAVING ROUTES
// ==========================================
if ($path === '/application/save-residence' && $method === 'POST') {
    require_once __DIR__ . '/../src/controllers/PreferenceController.php';
    $controller = new PreferenceController();
    $controller->saveResidenceType();
    return;
}

if ($path === '/application/save-language' && $method === 'POST') {
    require_once __DIR__ . '/../src/controllers/PreferenceController.php';
    $controller = new PreferenceController();
    $controller->saveLanguage();
    return;
}

    $approvalActions = [
        '/approval/clerk/submit' => 'submitClerk',
        '/approval/deputy/submit' => 'submitSuperintendent',
        '/approval/marks/submit' => 'submitMarks'
    ];
    if ($method === 'POST' && isset($approvalActions[$path])) {
        require_once __DIR__ . '/../src/controllers/ApprovalController.php';
        try {
            $controller = new ApprovalController();
            $controller->{$approvalActions[$path]}();
        } catch (Throwable $error) {
            $_SESSION['approval_error'] = $error->getMessage();
            redirect('/dashboard');
        }
        return;
    }

    // Check if path is in routes
    if (isset($routes[$path])) {
        $viewPath = __DIR__ . '/../src/views' . $routes[$path];
        if (file_exists($viewPath)) {
            require_once $viewPath;
            return;
        }
    }

    // Handle direct PHP file requests
    if (strpos($path, '.php') !== false) {
        $viewPath = __DIR__ . '/../src/views' . $path;
        if (file_exists($viewPath)) {
            require_once $viewPath;
            return;
        }
    }

    // Handle root path
    if ($path === '/') {
        redirect('/dashboard');
        exit();
    }

    // 404 - Not Found
    http_response_code(404);
    echo '<h1>404 - Page Not Found</h1>';
    echo '<p>The page you are looking for does not exist.</p>';
    echo '<p><a href="' . baseUrl('/dashboard') . '">Go to Dashboard</a></p>';
}

// ==========================================
// AJAX REQUEST HANDLER
// ==========================================
function handleAjaxRequest($action, $data) {
    header('Content-Type: application/json');

    switch ($action) {
        case 'mark_read':
            require_once __DIR__ . '/../src/controllers/NotificationController.php';
            $controller = new NotificationController();
            $result = $controller->markAsRead($data['action_id'] ?? 0);
            echo json_encode($result);
            break;

        case 'delete':
            require_once __DIR__ . '/../src/controllers/NotificationController.php';
            $controller = new NotificationController();
            $result = $controller->delete($data['action_id'] ?? 0);
            echo json_encode($result);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
}

// ==========================================
// EXECUTE ROUTING
// ==========================================

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Check authentication for all pages
checkAuth();

// ==========================================
// NEW: Handle POST requests for form submission
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos(rawurldecode($_SERVER['REQUEST_URI']), '/application/submit') !== false) {
    require_once __DIR__ . '/../src/controllers/ApplicationController.php';
    $appController = new ApplicationController();
    $appController->submitFullApplication();
    exit();
}

// Route the request
route($requestUri, $requestMethod);
?>