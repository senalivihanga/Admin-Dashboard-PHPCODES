<?php
/**
 * New Complaint Page
 */
require_once __DIR__ . '/../../controllers/ComplaintController.php';

$complaintController = new ComplaintController();
$pageTitle = 'New Complaint';

include __DIR__ . '/../layouts/header.php';
?>

    <div class="max-w-2xl mx-auto px-4 py-12">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 hover:border-amber-400 transition-all duration-300">
            
            <!-- Header Section with Icon -->
            <div class="flex items-start gap-4 mb-6 pb-6 border-b border-gray-100">
                <div class="w-12 h-12 rounded-xl bg-[#5c060d] text-amber-300 flex items-center justify-center shrink-0 shadow-sm">
                    <i class="fa-solid fa-file-lines text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">New Complaint</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Submit a complaint regarding quarters, water, light, etc.</p>
                </div>
            </div>

            <!-- Complaint Form -->
            <form action="<?php echo baseUrl('complaints/store'); ?>" method="POST">
                
                <!-- Complaints Textarea Field -->
                <div class="mb-6">
                    <label for="complaints" class="block text-sm font-semibold text-gray-700 mb-2">Complaints</label>
                    <textarea 
                        id="complaints" 
                        name="complaints" 
                        rows="5" 
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#5c060d] focus:border-transparent transition text-sm resize-y"
                        placeholder="List your complaints here (one per line)."
                        required
                    ></textarea>
                </div>

                <!-- Related Issues Select Dropdown -->
                <div class="mb-6">
                    <label for="related_issue" class="block text-sm font-semibold text-gray-700 mb-2">Related Issues</label>
                    <div class="relative">
                        <select 
                            id="related_issue" 
                            name="related_issue" 
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#5c060d] focus:border-transparent transition text-sm appearance-none cursor-pointer"
                            required
                        >
                            <option value="" disabled selected>Select a related issue</option>
                            <option value="water">Water Supply</option>
                            <option value="electricity">Electricity / Light</option>
                            <option value="maintenance">Quarter Maintenance</option>
                            <option value="other">Other</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- Custom Issue Input (Hidden by default, shown when 'Other' is selected) -->
                <div id="other-issue-container" class="mb-8 hidden">
                    <label for="custom_issue" class="block text-sm font-semibold text-gray-700 mb-2">Specify Other Issue</label>
                    <input 
                        type="text" 
                        id="custom_issue" 
                        name="custom_issue" 
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#5c060d] focus:border-transparent transition text-sm"
                        placeholder="Please specify your issue here"
                    >
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <a href="<?php echo baseUrl('dashboard'); ?>" class="inline-flex items-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-3 rounded-xl transition shadow-sm text-sm">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Back
                    </a>
                    <button type="submit" class="inline-flex items-center bg-[#5c060d] hover:bg-[#4a050a] text-white hover:text-amber-300 font-semibold px-6 py-3 rounded-xl transition shadow-sm hover:shadow-md text-sm">
                        Submit <i class="fa-solid fa-paper-plane ml-2"></i>
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- JavaScript to toggle 'Other' input field -->
    <script>
        document.getElementById('related_issue').addEventListener('change', function() {
            const otherContainer = document.getElementById('other-issue-container');
            const customInput = document.getElementById('custom_issue');
            
            if (this.value === 'other') {
                otherContainer.classList.remove('hidden');
                customInput.setAttribute('required', 'required');
            } else {
                otherContainer.classList.add('hidden');
                customInput.removeAttribute('required');
                customInput.value = ''; // clear input when hidden
            }
        });
    </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>