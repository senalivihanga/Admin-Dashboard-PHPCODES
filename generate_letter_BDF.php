<?php
/**
 * Generate BDF Handover Confirmation Letter - Sri Lanka Railways
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Generate BDF Official Letter - Sri Lanka Railways";

$userData = [
    'name' => 'BDF Member',
    'role' => 'Sri Lanka Railways - Board of Directors',
    'initials' => 'BDF'
];

// Include Admin Header[cite: 11]
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
    }

    .container-custom {
        max-width: 900px;
        margin: 20px auto;
        padding: 0 20px;
        box-sizing: border-box;
    }

    /* Profile Dropdown Styles (IB Dashboard මෙන් දකුණුපසින්) */
    .user-profile-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
        float: right;
    }

    .user-profile-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #7A1C1C;
        color: #ffffff;
        padding: 6px 14px;
        border-radius: 30px;
        cursor: pointer;
        user-select: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .profile-dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: 105%;
        background: #ffffff;
        min-width: 180px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
        z-index: 100;
        border: 1px solid #e2e8f0;
    }

    .profile-dropdown.show {
        display: block;
    }

    .profile-dropdown a {
        color: #1e293b;
        padding: 10px 15px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        transition: background 0.2s;
    }

    .profile-dropdown a:hover {
        background: #f1f5f9;
        color: #7A1C1C;
    }

    .profile-dropdown a.logout-btn {
        color: #ef4444;
    }

    .profile-dropdown a.logout-btn:hover {
        background: #fee2e2;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #7A1C1C;
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 20px;
        transition: color 0.2s;
        clear: both;
    }
    .back-link:hover { text-decoration: underline; }

    .card {
        background: #ffffff;
        padding: 30px;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
        border-top: 5px solid #7A1C1C;
        margin-bottom: 30px;
        clear: both;
    }
    .card-title {
        color: #0a0a0a;
        font-size: 1.7rem;
        font-weight: 700;
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        border-bottom: 2px solid #f3f4f6;
        padding-bottom: 10px;
        text-align: center;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
    }
    .form-group input, .form-group select, .form-group textarea {
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s;
        width: 100%;
        box-sizing: border-box;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        border-color: #7A1C1C;
    }
    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .section-subheading {
        font-size: 1.05rem;
        font-weight: 700;
        color: #7A1C1C;
        margin: 20px 0 10px 0;
        border-bottom: 1px dashed #cbd5e1;
        padding-bottom: 5px;
    }

    .btn-generate {
        background-color: #7A1C1C;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
        font-size: 0.95rem;
    }
    .btn-generate:hover { background-color: #571a1a; }

    /* Official Letter Preview Box - Pure Black & White / Times New Roman */
    .letter-preview-box {
        background: #fff;
        padding: 45px 50px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
        font-family: 'Times New Roman', Times, serif;
        color: #000000;
        line-height: 1.55;
        font-size: 12pt;
        margin-top: 30px;
        display: none; 
    }
    
    .letter-actions-bar {
        display: none;
        justify-content: flex-end;
        margin-top: 15px;
        margin-bottom: 40px;
    }

    .btn-print-action {
        background-color: #0284c7;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
        font-size: 0.95rem;
    }
    .btn-print-action:hover { background-color: #0369a1; }

    /* Logout Modal Styles */
    .custom-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
        padding: 15px;
    }

    .custom-modal-content {
        background-color: #fff;
        padding: 30px;
        border-radius: 12px;
        width: 100%;
        max-width: 380px;
        text-align: center;
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }

    .modal-action-btns {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 20px;
    }

    .btn-modal-confirm {
        background-color: #ef4444;
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-modal-cancel {
        background-color: #64748b;
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }

    /* Mobile Responsive Adjustments */
    @media (max-width: 768px) {
        .container-custom {
            margin: 15px auto;
            padding: 0 12px;
        }
        .card {
            padding: 20px;
        }
        .card-title {
            font-size: 1.35rem;
        }
        .letter-preview-box {
            padding: 25px 20px;
            font-size: 11pt;
        }
        .user-profile-wrapper {
            float: none;
            display: block;
            margin-bottom: 15px;
        }
    }

    /* Print Media Settings - Single Page A4 B&W */
    @media print {
        @page {
            size: A4;
            margin: 15mm 20mm;
        }
        body {
            background: white !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .no-print {
            display: none !important;
        }
        .container-custom {
            margin: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
        }
        #letterPreviewBox {
            display: block !important;
            position: relative !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            background: white !important;
            color: #000000 !important;
            font-size: 11.5pt !important;
            line-height: 1.5 !important;
        }
    }
</style>

<div class="container-custom">
    
    <!-- Profile Dropdown Menu (දකුණුපසින් My Dashboard, Edit Profile, Logout සමඟ) -->
    <div style="width: 100%; overflow: hidden;" class="no-print">
        <div class="user-profile-wrapper">
            <div class="user-profile-badge" onclick="toggleDropdown()">
                <div style="width: 30px; height: 30px; background: #ffffff; color: #7A1C1C; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                    <?php echo htmlspecialchars($userData['initials']); ?>
                </div>
                <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($userData['name']); ?></div>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; color: #f3e8ff; margin-left: 5px;"></i>
            </div>

            <div id="profileDropdown" class="profile-dropdown">
                <a href="dashboard.php"><i class="fa-solid fa-house"></i> My Dashboard</a>
                <a href="edit_profile.php"><i class="fa-solid fa-user-pen"></i> Edit Profile</a>
                <a href="#" class="logout-btn" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    </div>

    <!-- Form Container (Hidden on Print) -->
    <div class="no-print">
        <a href="BDF_dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Received Letters List</a>
        
        <div class="card">
            <h2 class="card-title">Generate BDF Handover Confirmation Letter</h2>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">
                Review auto-filled details from the selected record or enter Application Number to populate fields.
            </p>

            <form id="letterForm" onsubmit="generateLetter(event)">
                
                <div class="section-subheading">1. Application Reference</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Application Number</label>
                        <input type="text" id="appNo" placeholder="e.g. SLR/AOT/2026/0001" oninput="autoFillApplicant(this.value)" required>
                    </div>
                    <div class="form-group">
                        <label>Applicant NIC</label>
                        <input type="text" id="nic" placeholder="e.g. 883421092V" required>
                    </div>
                </div>

                <div class="section-subheading">2. Applicant & Handed-Over Quarter Information</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Applicant Name</label>
                        <input type="text" id="fullName" placeholder="e.g. A. B. Perera" required>
                    </div>
                    <div class="form-group">
                        <label>Designation</label>
                        <input type="text" id="designation" placeholder="e.g. Station Master" required>
                    </div>
                    <div class="form-group">
                        <label>Current Workplace</label>
                        <input type="text" id="receiverAddress" placeholder="e.g. Railway Station, Polgahawela" required>
                    </div>
                    <div class="form-group">
                        <label>Quarter No.</label>
                        <input type="text" id="quarterType" placeholder="e.g. Q-01" required>
                    </div>
                    <div class="form-group">
                        <label>Quarter Name & Location</label>
                        <input type="text" id="quarterName" placeholder="e.g. Sunflower Manor, Polgahawela" required>
                    </div>
                    <div class="form-group">
                        <label>Keys & Documents Handover Date</label>
                        <input type="date" id="handoverDate" required>
                    </div>
                </div>

                <div class="section-subheading">3. Official Letter Subject & Content</div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Letter Subject</label>
                        <input type="text" id="letterSubject" value="Acknowledgment of Letter and Confirmation of Quarter Keys & Documents Handover - " required>
                    </div>
                    <div class="form-group full-width">
                        <label>Main Body Description</label>
                        <textarea id="mainDescription" rows="5" required>We acknowledge the receipt of your official letter forwarded regarding the above-mentioned quarter allocation, and thank you for keeping us informed. We hereby confirm that the keys to the allocated railway quarters, along with all relevant inventory lists and official documents, have been duly handed over to the applicant.</textarea>
                    </div>
                </div>
                
                <button type="submit" class="btn-generate" style="margin-top: 15px;">
                    <i class="fa-solid fa-eye"></i> Generate Official Letter Preview
                </button>
            </form>
        </div>
    </div>

    <!-- Preview Container -->
    <div style="margin-top: 0;">
        <div id="letterPreviewBox" class="letter-preview-box">
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 20px;">
                <h2 style="margin: 0; font-size: 14pt; font-weight: bold; text-transform: uppercase; color: #000;">SRI LANKA RAILWAYS</h2>
                <p style="margin: 3px 0; font-size: 11pt; font-weight: bold; color: #000;">Quarters Management Branch - AOT</p>
                <p style="margin: 2px 0; font-size: 10pt; color: #000;">Department of Railways, P.O. Box 355, Colombo 10, Sri Lanka</p>
                <hr style="border: 0; border-top: 1.5px solid #000; margin-top: 10px;">
            </div>

            <!-- Sender & Date at the top -->
            <div style="margin-bottom: 18px; font-size: 11.5pt; line-height: 1.45;">
                Building Department Foreman (BDF),<br>
                Sri Lanka Railways, Colombo 10.<br>
                <span id="pDate" style="display: inline-block; margin-top: 6px;"></span>
            </div>

            <!-- Receiver (Subject Clerk) -->
            <div style="margin-bottom: 18px; font-size: 11.5pt; line-height: 1.45;">
                <strong>Subject Clerk,</strong><br>
                Quarters Management Branch,<br>
                Sri Lanka Railways - AOT,<br>
                Colombo 10.
            </div>

            <!-- Subject -->
            <div style="margin-bottom: 15px;">
                <p style="margin: 0 0 8px 0;">Dear Sir/Madam,</p>
                <h3 id="pSubjectTitle" style="font-size: 11.5pt; text-decoration: underline; margin: 8px 0; font-weight: bold; color: #000;"></h3>
            </div>

            <!-- Main Body -->
            <div style="margin-bottom: 20px; text-align: justify; line-height: 1.55;">
                <p id="pIntroPara" style="margin-bottom: 12px;"></p>
                <p id="pCustomDescPara" style="margin-bottom: 12px;"></p>
                <p style="margin: 0;">Please update the Quarters Management Registry and proceed with the necessary administrative and rent deduction records accordingly.</p>
            </div>

            <!-- Signature -->
            <div style="margin-top: 30px; font-size: 11.5pt;">
                <p style="margin: 0 0 4px 0;">Thank you,</p>
                <p style="margin: 0 0 22px 0;">Yours faithfully,</p>
                <p style="margin: 0;">___________________________<br>
                <strong>Building Department Foreman (BDF),</strong><br>
                Sri Lanka Railways.</p>
            </div>
        </div>

        <div id="letterActionsBar" class="letter-actions-bar no-print">
            <button type="button" class="btn-print-action" onclick="triggerPrint()">
                <i class="fa-solid fa-print"></i> Print Official Letter
            </button>
        </div>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="custom-modal">
    <div class="custom-modal-content">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 3rem; color: #7A1C1C; margin-bottom: 15px;"></i>
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to log out from the BDF portal?</p>
        <div class="modal-action-btns">
            <button type="button" class="btn-modal-cancel" onclick="closeLogoutModal()">Cancel</button>
            <button type="button" class="btn-modal-confirm" onclick="executeLogout()">Logout</button>
        </div>
    </div>
</div>

<script>
    function toggleDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) {
            dropdown.classList.toggle('show');
        }
    }

    // Close dropdown when clicking outside
    window.addEventListener('click', function(e) {
        if (!e.target.closest('.user-profile-wrapper')) {
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown && dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        }
    });

    function openLogoutModal(event) {
        event.preventDefault();
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) {
            dropdown.classList.remove('show');
        }
        document.getElementById('logoutModal').style.display = 'flex';
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    function executeLogout() {
        window.location.href = "login.php";
    }

    const applicantsDatabase = {
        "SLR/AOT/2026/0001": {
            nic: "883421092V",
            fullName: "A. B. Perera",
            designation: "Station Master",
            receiverAddress: "Railway Station, Polgahawela",
            quarterType: "Q-01",
            quarterName: "Sunflower Manor, Polgahawela"
        },
        "SLR/AOT/2026/0002": {
            nic: "925631485V",
            fullName: "S. K. Silva",
            designation: "Signal Inspector",
            receiverAddress: "Railway Signal Office, Colombo Fort",
            quarterType: "Q-02",
            quarterName: "Railway Bungalow 02, Colombo 10"
        },
        "SLR/AOT/2026/0003": {
            nic: "951234789V",
            fullName: "M. N. Fernando",
            designation: "Loco Driver",
            receiverAddress: "Railway Shed, Anuradhapura",
            quarterType: "Q-03",
            quarterName: "Staff Flat 04, Anuradhapura"
        }
    };

    // Check URL parameter on page load (e.g., ?app=SLR/AOT/2026/0001)
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const appParam = urlParams.get('app');
        
        // Set default handover date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('handoverDate').value = today;

        if (appParam) {
            document.getElementById('appNo').value = appParam;
            autoFillApplicant(appParam);
        }
    });

    function autoFillApplicant(appNoValue) {
        const rawVal = appNoValue.trim();
        const trimmedKey = rawVal.toUpperCase().startsWith("SLR/AOT/") ? rawVal.toUpperCase() : "SLR/AOT/2026/" + rawVal;
        const matchedData = applicantsDatabase[trimmedKey];

        if (matchedData) {
            document.getElementById('nic').value = matchedData.nic;
            document.getElementById('fullName').value = matchedData.fullName;
            document.getElementById('designation').value = matchedData.designation;
            document.getElementById('receiverAddress').value = matchedData.receiverAddress;
            document.getElementById('quarterType').value = matchedData.quarterType;
            document.getElementById('quarterName').value = matchedData.quarterName;
            
            document.getElementById('letterSubject').value = "Acknowledgment of Letter and Confirmation of Quarter Keys & Documents Handover - " + matchedData.fullName + " (" + matchedData.quarterType + ")";
        }
    }

    function generateLetter(event) {
        event.preventDefault();
        updatePreviewContent();

        document.getElementById('letterPreviewBox').style.display = 'block';
        document.getElementById('letterActionsBar').style.display = 'flex';
        document.getElementById('letterPreviewBox').scrollIntoView({ behavior: 'smooth' });
    }

    function updatePreviewContent() {
        let appInput = document.getElementById('appNo').value.trim();
        let appNoFull = appInput.toUpperCase().startsWith("SLR/AOT/") ? appInput.toUpperCase() : "SLR/AOT/2026/" + (appInput || '0001');

        const nic = document.getElementById('nic').value || '---';
        const fullName = document.getElementById('fullName').value || '---';
        const designation = document.getElementById('designation').value || '---';
        const receiverAddress = document.getElementById('receiverAddress').value || '---';
        const quarterType = document.getElementById('quarterType').value || '---';
        const quarterName = document.getElementById('quarterName').value || '---';
        const handoverDate = document.getElementById('handoverDate').value || '---';
        const letterSubject = document.getElementById('letterSubject').value || '---';
        const mainDescription = document.getElementById('mainDescription').value || '';

        // Populate Date & Subject
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('pDate').innerText = today;
        document.getElementById('pSubjectTitle').innerText = letterSubject;

        // Populate Body
        document.getElementById('pIntroPara').innerHTML = `This is with reference to the letter sent by you regarding Application Reference No: <strong>${appNoFull}</strong> for <strong>${fullName}</strong> (NIC: <strong>${nic}</strong>, Designation: <strong>${designation}</strong>, Workplace: <strong>${receiverAddress}</strong>). Thank you for your notification; we confirm that the keys and official documents for Quarter No: <strong>${quarterType}</strong> (<strong>${quarterName}</strong>) were handed over to the applicant on <strong>${handoverDate}</strong>.`;
        
        document.getElementById('pCustomDescPara').innerHTML = mainDescription;
    }

    function triggerPrint() {
        updatePreviewContent();
        document.getElementById('letterPreviewBox').style.display = 'block';
        window.print();
    }
</script>

<?php
// Include Admin Footer[cite: 11]
require_once __DIR__ . '/../layouts/admin_footer.php';
?>