<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
$auth->requireLogin();

$user = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Official Marking Scheme';
include __DIR__ . '/../layouts/header.php';
?>

<style>
    /* Custom styling for the marking scheme table – kept exactly as in HTML */
    .marking-table {
        width: 100%;
        border-collapse: collapse;
    }
    .marking-table th, .marking-table td {
        border: 1px solid #d1d5db;
        padding: 12px 16px;
        font-size: 14px;
    }
    .marking-table th {
        background-color: #0f4c81;
        color: #ffffff;
        font-weight: 600;
    }
    .marking-table tr:nth-child(even) {
        background-color: #f8fafc;
    }
    .marking-table tr:hover {
        background-color: #f1f5f9;
    }
</style>

<!-- Main Container Card – exact Tailwind structure from total_marks.html -->
<div class="max-w-4xl mx-auto bg-white mt-8 p-8 shadow-lg rounded-xl border border-slate-200">
    
    <!-- Header Actions: Back Button -->
    <div class="flex justify-between items-center mb-6">
        <a href="<?php echo baseUrl('dashboard'); ?>" class="inline-flex items-center bg-amber-400 hover:bg-amber-500 text-slate-900 font-semibold py-2 px-4 rounded-md shadow-sm transition duration-150 text-sm">
            &laquo; Back to Dashboard
        </a>
    </div>

    <!-- Title Section -->
    <div class="text-center border-b-2 border-[#0f4c81] pb-5 mb-8">
        <h1 class="text-2xl font-bold text-[#0f4c81] tracking-wide mb-1">Sri Lanka Railways</h1>
        <h2 class="text-base font-medium text-slate-600 uppercase tracking-wider">Railway Quarters Allotment - Evaluation & Marking Scheme</h2>
        <p class="text-xs text-slate-500 mt-1">Deputy Superintendent (Transportation) - Colombo</p>
    </div>

    <!-- Marking Scheme Form -->
    <form action="<?php echo baseUrl('approval/marks/submit'); ?>" method="POST">
        <input type="hidden" name="application_id" value="<?php echo (int)($_GET['application_id'] ?? 0); ?>">
        <div class="bg-white border border-slate-300 rounded-lg p-6 shadow-sm mb-6">
    
            <!-- Marking Table -->
            <div class="overflow-x-auto">
                <table class="marking-table">
                    <thead>
                        <tr>
                            <th style="width: 82%;">Evaluation Criteria Description</th>
                            <th style="width: 18%; text-align: center;">Assigned Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <span class="font-semibold text-slate-700">1. Duration on the Waiting List</span>
                                <p class="text-xs text-slate-500 mt-0.5">03 marks allocated for each month on the waiting list.</p>
                            </td>
                            <td style="text-align: center;" class="align-middle">
                                <input type="number" min="0" name="score_1" class="border border-slate-300 py-1.5 px-2 text-center rounded-md focus:ring-2 focus:ring-[#0f4c81] focus:outline-none w-20 text-sm shadow-inner" placeholder="0">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="font-semibold text-slate-700">2. Number of Children</span>
                                <p class="text-xs text-slate-500 mt-0.5">01 mark for each male child under 18 years of age and unmarried female children.</p>
                            </td>
                            <td style="text-align: center;" class="align-middle">
                                <input type="number" min="0" name="score_2" class="border border-slate-300 py-1.5 px-2 text-center rounded-md focus:ring-2 focus:ring-[#0f4c81] focus:outline-none w-20 text-sm shadow-inner" placeholder="0">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="font-semibold text-slate-700">3. Housing Condition</span>
                                <p class="text-xs text-slate-500 mt-0.5">For residing in a rented house or an unlivable house (01 mark).</p>
                            </td>
                            <td style="text-align: center;" class="align-middle">
                                <input type="number" min="0" name="score_3" class="border border-slate-300 py-1.5 px-2 text-center rounded-md focus:ring-2 focus:ring-[#0f4c81] focus:outline-none w-20 text-sm shadow-inner" placeholder="0">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="font-semibold text-slate-700">4. Distance from Permanent Residence to Workplace</span>
                                <p class="text-xs text-slate-500 mt-0.5">01 mark for each kilometer exceeding 30 km, up to a maximum of 10 marks.</p>
                            </td>
                            <td style="text-align: center;" class="align-middle">
                                <input type="number" min="0" name="score_4" class="border border-slate-300 py-1.5 px-2 text-center rounded-md focus:ring-2 focus:ring-[#0f4c81] focus:outline-none w-20 text-sm shadow-inner" placeholder="0">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="font-semibold text-slate-700">5. Serious Illnesses of the Applicant</span>
                                <p class="text-xs text-slate-500 mt-0.5">Conditions causing severe difficulty in walking or movement (02 marks).</p>
                            </td>
                            <td style="text-align: center;" class="align-middle">
                                <input type="number" min="0" name="score_5" class="border border-slate-300 py-1.5 px-2 text-center rounded-md focus:ring-2 focus:ring-[#0f4c81] focus:outline-none w-20 text-sm shadow-inner" placeholder="0">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="font-semibold text-slate-700">6. Serious Illnesses of Dependents</span>
                                <p class="text-xs text-slate-500 mt-0.5">Medical conditions affecting registered dependents (01 mark).</p>
                            </td>
                            <td style="text-align: center;" class="align-middle">
                                <input type="number" min="0" name="score_6" class="border border-slate-300 py-1.5 px-2 text-center rounded-md focus:ring-2 focus:ring-[#0f4c81] focus:outline-none w-20 text-sm shadow-inner" placeholder="0">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="font-semibold text-slate-700">7. Marital Status</span>
                                <p class="text-xs text-slate-500 mt-0.5">Married status verification (01 mark).</p>
                            </td>
                            <td style="text-align: center;" class="align-middle">
                                <input type="number" min="0" name="score_7" class="border border-slate-300 py-1.5 px-2 text-center rounded-md focus:ring-2 focus:ring-[#0f4c81] focus:outline-none w-20 text-sm shadow-inner" placeholder="0">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="font-semibold text-slate-700">8. Tie-breaker Exception (Total Service Duration)</span>
                                <p class="text-xs text-slate-500 mt-0.5">Applied *only* if multiple officers share identical total marks. 01 mark per year of total service (starting sequentially from the officer with the least service: 01 mark for the closest higher officer, 02 for the next, etc.).</p>
                            </td>
                            <td style="text-align: center;" class="align-middle">
                                <input type="number" min="0" name="score_8" class="border border-slate-300 py-1.5 px-2 text-center rounded-md focus:ring-2 focus:ring-[#0f4c81] focus:outline-none w-20 text-sm shadow-inner" placeholder="0">
                            </td>
                        </tr>
                        <tr style="background-color: #fef3c7; font-weight: bold;">
                            <td style="text-align: right;" class="text-[#701515] uppercase tracking-wider text-sm align-middle">Total Aggregate Marks :</td>
                            <td style="text-align: center;" class="align-middle">
                                <input type="text" name="total_score" class="border border-amber-400 bg-white py-1.5 px-2 text-center rounded-md font-bold text-[#701515] w-20 text-sm shadow-sm" readonly placeholder="0">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Evaluator Action Area -->
            <div class="flex flex-col sm:flex-row justify-end items-center gap-4 mt-8 pt-5 border-t border-slate-200">
                <button type="submit" class="w-full sm:w-auto bg-[#0f4c81] hover:bg-[#0b3961] text-white font-medium text-sm px-8 py-3 rounded-md shadow transition duration-150 flex items-center justify-center space-x-2">
                   <span>Submit Total Evaluation Marks</span>
                </button>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>