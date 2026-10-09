<?php
/**
 * Notifications Page
 */
require_once __DIR__ . '/../../controllers/NotificationController.php';

$notificationController = new NotificationController();
$notifications = $notificationController->getAll();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $targetId = isset($_POST['action_id']) ? (int)$_POST['action_id'] : 0;
    
    if ($_POST['ajax_action'] === 'mark_read' && $targetId > 0) {
        $result = $notificationController->markAsRead($targetId);
        echo json_encode($result);
        exit();
    } 
    elseif ($_POST['ajax_action'] === 'delete' && $targetId > 0) {
        $result = $notificationController->delete($targetId);
        echo json_encode($result);
        exit();
    }
}

$user = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Notifications';

include __DIR__ . '/../layouts/header.php';
?>

    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 hover:border-amber-400 transition-all duration-300">
            <!-- Back to Dashboard Button (Top-Left) -->
            <div class="mb-6">
                <a href="<?php echo baseUrl('dashboard'); ?>" class="inline-flex items-center bg-[#5c060d] hover:bg-[#4a050a] text-white hover:text-amber-300 font-semibold px-4 py-2 rounded-lg transition shadow-sm hover:shadow-md text-sm">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">🔔 Notifications</h2>
            <p class="text-gray-500 text-center text-sm mb-6">Stay updated with your application status</p>

            <div id="notificationsContainer" class="space-y-4">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div id="notif-box-<?php echo $notif['id']; ?>" 
                             class="bg-white border border-gray-200 border-l-4 border-l-amber-400 rounded-lg p-5 hover:shadow-md transition">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs text-gray-400">
                                    <i class="far fa-calendar-alt mr-1"></i> 
                                    <?php echo htmlspecialchars($notif['created_at'] ?? date('Y-m-d H:i')); ?>
                                </span>
                                <span id="status-text-<?php echo $notif['id']; ?>">
                                    <?php if ($notif['is_read'] == 1): ?>
                                        <span class="text-xs font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full border border-green-200">
                                            <i class="fas fa-check-circle mr-1"></i> Read
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs font-semibold text-red-600 bg-red-50 px-3 py-1 rounded-full border border-red-200">
                                            <i class="fas fa-exclamation-circle mr-1"></i> Unread
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <h4 class="font-bold text-gray-800 text-lg mb-2">
                                <?php echo htmlspecialchars($notif['title'] ?? 'Notification'); ?>
                            </h4>
                            <p class="text-gray-600 text-sm mb-4">
                                <?php echo htmlspecialchars($notif['message'] ?? ''); ?>
                            </p>
                            <div class="flex items-center justify-end space-x-3">
                                <div id="mark-read-btn-wrap-<?php echo $notif['id']; ?>">
                                    <?php if ($notif['is_read'] == 0): ?>
                                        <button type="button" onclick="markAsRead(<?php echo $notif['id']; ?>)" 
                                                class="px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold text-sm rounded-lg transition border border-amber-200">
                                            <i class="fas fa-check mr-1"></i> Mark as read
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <button type="button" data-id="<?php echo $notif['id']; ?>" 
                                        class="delete-trigger-btn px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 font-semibold text-sm rounded-lg transition border border-red-200">
                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                        <p class="text-lg">No notifications available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modern Notification Delete Confirmation Modal -->
    <div id="deleteModal" style="display: none;" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 transition-all duration-300">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 text-center transform transition-all border border-gray-100 mx-4">
            <div class="flex justify-center mb-5">
                <div class="w-16 h-16 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 text-2xl shadow-inner border border-red-100">
                    <i class="fas fa-trash-alt"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Delete Notification</h3>
            <p class="text-gray-500 text-sm mb-8 leading-relaxed">Are you sure you want to delete this message? </p>
            <div class="flex items-center justify-center gap-3">
                <button id="cancelDeleteBtn" type="button" class="flex-1 px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all duration-200 text-sm">
                    Cancel
                </button>
                <button id="confirmDeleteBtn" type="button" class="flex-1 px-5 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-red-600/25 text-sm">
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentDeleteId = null;
        const deleteModal = document.getElementById('deleteModal');

        // Event listener for all delete buttons
        document.addEventListener('click', function(event) {
            let btn = event.target.closest('.delete-trigger-btn');
            if (btn) {
                currentDeleteId = btn.getAttribute('data-id');
                deleteModal.style.display = 'flex';
            }
        });

        // Modal Cancel Button Event
        document.getElementById('cancelDeleteBtn').addEventListener('click', () => {
            deleteModal.style.display = 'none';
            currentDeleteId = null;
        });

        // Modal Confirm Delete Button Event
        document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
            deleteModal.style.display = 'none';
            if (currentDeleteId) {
                executeAction(currentDeleteId, 'delete');
                currentDeleteId = null;
            }
        });

        function markAsRead(id) {
            executeAction(id, 'mark_read');
        }

        function executeAction(id, action) {
            let formData = new URLSearchParams();
            formData.append('ajax_action', action);
            formData.append('action_id', id);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (action === 'mark_read') {
                        document.getElementById('status-text-' + id).innerHTML = 
                            '<span class="text-xs font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full border border-green-200"><i class="fas fa-check-circle mr-1"></i> Read</span>';
                        let readBtnWrap = document.getElementById('mark-read-btn-wrap-' + id);
                        if (readBtnWrap) readBtnWrap.innerHTML = '';
                    } else if (action === 'delete') {
                        let box = document.getElementById('notif-box-' + id);
                        if (box) box.remove();
                        
                        let container = document.getElementById('notificationsContainer');
                        if (container && container.querySelectorAll('[id^="notif-box-"]').length === 0) {
                            location.reload();
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>