<?php
/**
 * Admin Footer Layout
 */
require_once __DIR__ . '/../../../config/config.php';
?>
    <!-- Admin Footer -->
    <footer class="bg-[#5c060d] text-white/85 text-center py-5 mt-auto border-t-2 border-[#b59410]/40 shadow-inner w-full">
        <p class="text-sm font-medium">&copy; <?php echo date('Y'); ?> Department of Railways - Quarter Management System (Admin Panel). All rights reserved.</p>
    </footer>

    <!-- Custom JavaScript for Admin Dropdown -->
    <script>
        function toggleAdminDropdown() {
            const dropdown = document.getElementById('adminProfileDropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            const dropdown = document.getElementById('adminProfileDropdown');
            const container = document.querySelector('.profile-dropdown-container');
            if (dropdown && container && !container.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
    <script src="<?php echo assetUrl('js/main.js'); ?>"></script>
</body>
</html>