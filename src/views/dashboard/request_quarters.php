<?php
/**
 * Request Quarters Page
 */
require_once __DIR__ . '/../../controllers/ApplicationController.php';

$appController = new ApplicationController();
$message = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['quarter_type'])) {
    $result = $appController->submit($_POST['quarter_type']);
    $message = $result['message'];
    $success = $result['success'];
}

$user = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Request Quarters';

include __DIR__ . '/../layouts/header.php';
?>

    <div class="max-w-2xl mx-auto px-4 py-12">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 hover:border-amber-400 transition-all duration-300">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">🏠 Request a Quarter</h2>
            <p class="text-gray-500 text-center text-sm mb-6">Fill in the form below to submit your application</p>
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg text-center font-medium <?php echo $success ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-6">
                    <label for="quarter_type" class="block text-sm font-semibold text-gray-700 mb-2">Select Quarter Type</label>
                    <select name="quarter_type" id="quarter_type" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-transparent transition">
                        <option value="">-- Select --</option>
                        <option value="Type A">Type A (2 Bedroom)</option>
                        <option value="Type B">Type B (3 Bedroom)</option>
                        <option value="Type C">Type C (4 Bedroom)</option>
                    </select>
                </div>
                
                <button type="submit" 
                        class="w-full bg-[#5c060d] hover:bg-[#4a050a] text-white font-semibold py-3 rounded-lg transition shadow-sm hover:shadow-md hover:text-amber-300">
                    Submit Application
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="<?php echo baseUrl('dashboard'); ?>" class="text-[#5c060d] hover:text-amber-600 font-medium text-sm">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>