<?php
require_once __DIR__ . '/../models/Notification.php';

class DashboardController {
    private $auth;
    private $notificationModel;
    
    public function __construct() {
        require_once __DIR__ . '/AuthController.php';
        $this->auth = new AuthController();
        $this->auth->requireLogin();
        $this->notificationModel = new Notification();
    }
    
    /**
     * Get dashboard data
     */
    public function getDashboardData() {
        $user = $this->auth->getCurrentUser();
        $unreadCount = $this->notificationModel->getUnreadCount($user['nic']);
        
        return [
            'user' => $user,
            'unread_count' => $unreadCount
        ];
    }
}
?>