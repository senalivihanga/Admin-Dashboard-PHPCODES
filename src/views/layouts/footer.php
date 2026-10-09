<?php
/**
 * Footer layout for all pages
 */
require_once __DIR__ . '/../../../config/config.php';
?>
    </div> <!-- Closing main content wrapper -->

    <!-- Footer -->
    <footer class="bg-[#5c060d] text-white/80 text-center py-6 mt-auto border-t-2 border-[#b59410]/30 shadow-inner w-full">
        <p class="text-sm font-medium">&copy; <?php echo date('Y'); ?> Department of Railways - Quarter Management System. All rights reserved.</p>
    </footer>

    <!-- Custom JavaScript -->
    <script src="<?php echo assetUrl('js/main.js'); ?>"></script>
</body>
</html>