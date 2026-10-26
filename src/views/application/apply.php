<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
$auth->requireLogin();

// ---------- AJAX: quarter search (joint residence) ----------
if (isset($_GET['ajax_search'])) {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');

    $quarterNo = trim($_GET['quarter_no'] ?? '');
    if ($quarterNo === '') {
        echo json_encode(['found' => false]);
        exit();
    }

    $conn = getDBConnection();
    $stmt = $conn->prepare(
        "SELECT quarter_no, nic, computer_no, quarter_type, resident_name,
                designation, workplace, allocated_date, other
         FROM allocated_quarters
         WHERE quarter_no = ?
         ORDER BY id ASC
         LIMIT 1"
    );
    $stmt->bind_param('s', $quarterNo);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    echo json_encode($row ? ['found' => true, 'data' => $row] : ['found' => false]);
    exit();
}
// ------------------------------------------------------------

$user = $_SESSION['user_name'] ?? 'User';
$pageTitle = 'Joint Residence - Quarter Search';
include __DIR__ . '/../layouts/header.php';
?>

<style>
    /* Print formatting styles to remove headers/footers and manage margins */
    @media print {
        @page {
            size: A4;
            margin-top: 20mm;
            margin-bottom: 20mm;
            margin-left: 15mm;
            margin-right: 15mm;
        }
        body {
            background-color: #ffffff !important;
            margin: 0;
        }
        .search-box-container, .search-btn, .back-link {
            display: none !important;
        }
    }
</style>

<!-- Main Container Card -->
<main class="max-w-4xl w-full mx-auto my-10 px-4 flex-grow">

    <!-- Back to Residence Dashboard (Links to residence.php) with updated styling -->
    <div class="flex items-center justify-between pt-4 pb-4 border-gray-100 mb-2">
        <a href="<?php echo baseUrl('application/residence.php'); ?>" class="back-link inline-flex items-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-3 rounded-xl transition shadow-sm text-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Main Dashboard
        </a>
    </div>

    <div class="dashboard-card bg-white p-6 sm:p-10 shadow-md rounded-lg border border-gray-200">
        <h1 class="text-2xl font-bold text-[#0f4c81] mb-1 text-center">Joint Residence - Quarter Search</h1>
        <h2 class="text-sm sm:text-base font-semibold text-gray-600 mb-6 text-center">Search existing quarter details using Quarter Number</h2>

        <!-- Search Bar Form (Supports Enter Key and Button Click) -->
        <div class="search-box-container flex flex-col sm:flex-row gap-3 mb-6 justify-center">
            <input type="text" id="quarterSearchInput" onkeydown="handleKeyPress(event)" class="search-input w-full sm:w-3/5 px-4 py-2.5 text-sm border border-gray-300 rounded-md outline-none focus:border-[#0f4c81]" placeholder="Enter Quarter No (e.g., Q-001)" />
            <button onclick="searchQuarter()" class="search-btn bg-[#0f4c81] hover:bg-[#0b3961] text-white px-5 py-2.5 text-sm font-semibold rounded-md transition cursor-pointer">Search Quarter</button>
        </div>

        <div id="errorMsg" class="error-msg text-red-600 text-sm text-center mt-4 hidden">Quarter not found. Please check the Quarter No and try again.</div>

        <!-- Quarter Details Result Box -->
        <div id="resultContainer" class="result-container hidden bg-gray-50 border border-gray-200 rounded-md p-6 mt-6 text-left">
            <div class="result-title text-base font-bold text-[#0f4c81] mb-4 border-b-2 border-gray-200 pb-2">Assigned Resident & Quarter Information</div>

            <div class="details-grid grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm mb-6">
                <div class="details-item bg-white p-3 border border-gray-200 rounded-md"><span class="font-bold text-gray-600">No:</span> <span id="resNo">-</span></div>
                <div class="details-item bg-white p-3 border border-gray-200 rounded-md"><span class="font-bold text-gray-600">NIC:</span> <span id="resNic">-</span></div>
                <div class="details-item bg-white p-3 border border-gray-200 rounded-md"><span class="font-bold text-gray-600">Computer No:</span> <span id="resCompNo">-</span></div>
                <div class="details-item bg-white p-3 border border-gray-200 rounded-md"><span class="font-bold text-gray-600">Quarter Type:</span> <span id="resType">-</span></div>
                <div class="details-item bg-white p-3 border border-gray-200 rounded-md"><span class="font-bold text-gray-600">Resident Name:</span> <span id="resName">-</span></div>
                <div class="details-item bg-white p-3 border border-gray-200 rounded-md"><span class="font-bold text-gray-600">Designation:</span> <span id="resDesignation">-</span></div>
                <div class="details-item bg-white p-3 border border-gray-200 rounded-md"><span class="font-bold text-gray-600">Workplace:</span> <span id="resWorkplace">-</span></div>
                <div class="details-item bg-white p-3 border border-gray-200 rounded-md"><span class="font-bold text-gray-600">Allocated Date:</span> <span id="resDate">-</span></div>
                <div class="details-item bg-white p-3 border border-gray-200 rounded-md sm:col-span-2"><span class="font-bold text-gray-600">Other:</span> <span id="resOther">-</span></div>
            </div>

            <!-- Apply Quarter Button -> Redirects to Language Selection Dashboard -->
            <a href="<?php echo baseUrl('application/select-language?type=joint_residence'); ?>" class="apply-btn block w-full bg-[#701515] hover:bg-[#5a1010] text-white text-center py-3 text-sm font-bold rounded-md transition no-underline">Apply Quarter & Proceed to Language Selection &rarr;</a>
        </div>

    </div>

</main>

<!-- JavaScript for Search Logic -->

<script>
    function handleKeyPress(event) {
        if (event.key === 'Enter') {
            searchQuarter();
        }
    }

    function searchQuarter() {
        const query     = document.getElementById('quarterSearchInput').value.trim();
        const resultBox = document.getElementById('resultContainer');
        const errorMsg  = document.getElementById('errorMsg');

        resultBox.classList.add('hidden');
        errorMsg.classList.add('hidden');

        if (query === '') {
            errorMsg.textContent = 'Please enter a Quarter No.';
            errorMsg.classList.remove('hidden');
            return;
        }

        fetch(window.location.pathname + '?ajax_search=1&quarter_no=' + encodeURIComponent(query), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
        .then(function (response) { return response.json(); })
        .then(function (res) {
            if (res.found) {
                const d = res.data;
                document.getElementById('resNo').innerText          = d.quarter_no || '-';
                document.getElementById('resNic').innerText         = d.nic || '-';
                document.getElementById('resCompNo').innerText      = d.computer_no || '-';
                document.getElementById('resType').innerText        = d.quarter_type || '-';
                document.getElementById('resName').innerText        = d.resident_name || '-';
                document.getElementById('resDesignation').innerText = d.designation || '-';
                document.getElementById('resWorkplace').innerText   = d.workplace || '-';
                document.getElementById('resDate').innerText        = d.allocated_date || '-';
                document.getElementById('resOther').innerText       = d.other || '-';
                resultBox.classList.remove('hidden');
            } else {
                errorMsg.textContent = 'Quarter not found. Please check the Quarter No and try again.';
                errorMsg.classList.remove('hidden');
            }
        })
        .catch(function () {
            errorMsg.textContent = 'Something went wrong while searching. Please try again.';
            errorMsg.classList.remove('hidden');
        });
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>