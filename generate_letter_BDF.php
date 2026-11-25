<?php
/**
 * Generate BDF Handover Confirmation Letter - Sri Lanka Railways
 * ----------------------------------------------------------------
 * Auto-fills from DB using ?app=XXX
 * Saves PDF to dedicated `bdf_generated_letters` table
 */
require_once __DIR__ . '/../../../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$pageTitle = "Generate BDF Official Letter - Sri Lanka Railways";
$userData = [
    'name'     => $_SESSION['user_name'] ?? 'BDF Member',
    'role'     => 'Sri Lanka Railways - Board of Directors',
    'initials' => 'BDF'
];

// ============================================================
// AUTO-FILL from DB using ?app=XXX (application_no)
// ============================================================
$appNoParam = trim($_GET['app'] ?? '');
$prefill = [
    'appNo'           => '',
    'applicationId'   => 0,
    'nic'             => '',
    'fullName'        => '',
    'designation'     => '',
    'receiverAddress' => '',
    'quarterType'     => '',
    'quarterName'     => '',
    'quarterLocation' => '',
];

if ($appNoParam !== '') {
    $conn = getDBConnection();
    $stmt = $conn->prepare(
        "SELECT a.id, a.application_no, a.nic, a.quarter_type,
                a.applied_quarter_number, a.applied_quarter_location,
                u.name,
                COALESCE(u.applicant_designation, '')          AS designation,
                COALESCE(u.applicant_current_work_station, '') AS workplace,
                aq.quarter_no       AS allocated_quarter_no,
                aq.quarter_name     AS allocated_quarter_name,
                aq.quarter_location AS allocated_quarter_location
         FROM applications AS a
         INNER JOIN users AS u ON u.nic = a.nic
         LEFT  JOIN allocated_quarters AS aq
                 ON aq.nic = a.nic AND aq.source = 'normal'
         WHERE a.application_no = ?
         LIMIT 1"
    );
    $stmt->bind_param('s', $appNoParam);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($row) {
        $prefill['applicationId']   = (int)$row['id'];
        $prefill['appNo']           = $row['application_no'] ?? '';
        $prefill['nic']             = $row['nic']            ?? '';
        $prefill['fullName']        = $row['name']           ?? '';
        $prefill['designation']     = $row['designation']    ?? '';
        $prefill['receiverAddress'] = $row['workplace']      ?? '';

        if (!empty($row['allocated_quarter_no'])) {
            $prefill['quarterType']     = $row['allocated_quarter_no'];
            $prefill['quarterName']     = $row['allocated_quarter_name']
                ?: ($row['allocated_quarter_location'] ?? '');
            $prefill['quarterLocation'] = $row['allocated_quarter_location'] ?? '';
        } else {
            $prefill['quarterType']     = $row['applied_quarter_number']   ?? '';
            $prefill['quarterName']     = $row['applied_quarter_location'] ?? '';
            $prefill['quarterLocation'] = $row['applied_quarter_location'] ?? '';
        }
    }
}

$todayIso = date('Y-m-d');
require_once __DIR__ . '/../layouts/admin_header.php';
?>
<!-- html2pdf.js for client-side PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    html, body { height:100%; margin:0; padding:0; overflow-y:auto; background:#f8fafc; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; }
    .container-custom { max-width:900px; margin:20px auto; padding:0 20px; box-sizing:border-box; }
    .back-link { display:inline-flex; align-items:center; gap:8px; color:#7A1C1C; text-decoration:none; font-weight:600; margin-bottom:20px; clear:both; }
    .back-link:hover { text-decoration:underline; }
    .card { background:#fff; padding:30px; border-radius:1rem; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); border:1px solid #e5e7eb; border-top:5px solid #7A1C1C; margin-bottom:30px; clear:both; }
    .card-title { color:#0a0a0a; font-size:1.7rem; font-weight:700; margin-bottom:20px; display:flex; justify-content:center; align-items:center; gap:10px; border-bottom:2px solid #f3f4f6; padding-bottom:10px; text-align:center; }
    .form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px; margin-bottom:20px; }
    .form-group { display:flex; flex-direction:column; gap:6px; }
    .form-group label { font-size:0.85rem; font-weight:600; color:#374151; }
    .form-group input, .form-group textarea { padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.9rem; outline:none; width:100%; box-sizing:border-box; }
    .form-group input:focus, .form-group textarea:focus { border-color:#7A1C1C; }
    .form-group.full-width { grid-column:1 / -1; }
    .section-subheading { font-size:1.05rem; font-weight:700; color:#7A1C1C; margin:20px 0 10px 0; border-bottom:1px dashed #cbd5e1; padding-bottom:5px; }

    .btn-generate, .btn-save-letter { border:none; padding:12px 24px; border-radius:6px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all 0.2s; font-size:0.95rem; color:white; }
    .btn-generate { background:#7A1C1C; }
    .btn-generate:hover { background:#571a1a; }
    .btn-save-letter { background:linear-gradient(135deg,#059669,#047857); display:none; }
    .btn-save-letter:hover { background:linear-gradient(135deg,#047857,#065f46); }
    .btn-generate:disabled, .btn-save-letter:disabled { background:#9ca3af !important; cursor:not-allowed; opacity:0.7; }
    .btn-actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:15px; }

    .success-alert { display:none; background:#dcfce7; color:#166534; padding:14px 20px; border-radius:8px; margin-bottom:20px; font-weight:600; border:1px solid #bbf7d0; text-align:center; }
    .success-alert.show { display:block; }
    .autofill-badge { display:inline-flex; align-items:center; gap:6px; background:#dbeafe; color:#1e40af; padding:6px 14px; border-radius:20px; font-size:0.8rem; font-weight:600; margin-bottom:20px; }

    .loading-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center; }
    .loading-overlay.show { display:flex; }
    .loading-box { background:#fff; padding:30px 40px; border-radius:12px; text-align:center; }
    .loading-box i { font-size:2.5rem; color:#7A1C1C; margin-bottom:10px; }
    .loading-box p { margin:0; font-weight:600; color:#1e293b; }

    .letter-preview-box { background:#fff; padding:45px 50px; border:1px solid #cbd5e1; border-radius:8px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.05); font-family:'Times New Roman',Times,serif; color:#000; line-height:1.55; font-size:12pt; margin-top:30px; display:none; }
    .letter-preview-box.pdf-export { width:190mm !important; max-width:none !important; box-sizing:border-box !important; padding:0 !important; border:0 !important; border-radius:0 !important; box-shadow:none !important; margin:0 !important; font-size:10pt !important; line-height:1.25 !important; }
    .letter-preview-box.pdf-export > div { margin-top:0 !important; margin-bottom:8px !important; font-size:10pt !important; line-height:1.25 !important; }
    .letter-preview-box.pdf-export h2 { font-size:12pt !important; }
    .letter-preview-box.pdf-export h3 { margin:4px 0 !important; font-size:10pt !important; line-height:1.2 !important; }
    .letter-preview-box.pdf-export p { margin:0 0 6px !important; line-height:1.25 !important; }
    .letter-actions-bar { display:none; justify-content:flex-end; margin-top:15px; margin-bottom:40px; }
    .btn-print-action { background:#0284c7; color:white; border:none; padding:10px 20px; border-radius:6px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:8px; font-size:0.95rem; }
    .btn-print-action:hover { background:#0369a1; }

    @media (max-width:768px) {
        .container-custom { margin:15px auto; padding:0 12px; }
        .card { padding:20px; }
        .card-title { font-size:1.35rem; }
        .letter-preview-box { padding:25px 20px; font-size:11pt; }
    }
    @media print {
        @page { size:A4 portrait; margin:10mm; }
        html, body { height:auto !important; min-height:0 !important; overflow:visible !important; }
        body { display:block !important; background:white !important; margin:0 !important; padding:0 !important; }
        body > header, body > footer, .no-print, .success-alert, .loading-overlay { display:none !important; }
        .container-custom { margin:0 !important; padding:0 !important; width:100% !important; max-width:none !important; }
        #letterPreviewBox { display:block !important; box-sizing:border-box !important; border:none !important; box-shadow:none !important; padding:0 !important; margin:0 !important; width:100% !important; background:white !important; font-size:10pt !important; line-height:1.25 !important; }
        #letterPreviewBox > div { margin-top:0 !important; margin-bottom:8px !important; font-size:10pt !important; line-height:1.25 !important; }
        #letterPreviewBox h2 { font-size:12pt !important; }
        #letterPreviewBox h3 { margin:4px 0 !important; font-size:10pt !important; line-height:1.2 !important; }
        #letterPreviewBox p { margin:0 0 6px !important; line-height:1.25 !important; }
    }
</style>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-box">
        <i class="fa-solid fa-spinner fa-spin"></i>
        <p id="loadingText">Processing...</p>
    </div>
</div>

<div class="container-custom">

    <div class="success-alert" id="successAlert">
        <i class="fa-solid fa-circle-check"></i> <span id="successMessage"></span>
    </div>

    <div class="no-print">
        <a href="BDF_dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Received Letters List</a>
        <div class="card">
            <h2 class="card-title">Generate BDF Handover Confirmation Letter</h2>

            <?php if ($prefill['appNo']): ?>
                <div class="autofill-badge">
                    <i class="fa-solid fa-database"></i>
                    All fields auto-filled from database for Application:
                    <strong><?php echo htmlspecialchars($prefill['appNo']); ?></strong>
                </div>
            <?php else: ?>
                <p style="color:#64748b;font-size:0.9rem;margin-bottom:20px;">
                    Enter Application Number first to auto-fill applicant information.
                </p>
            <?php endif; ?>

            <input type="hidden" id="dbApplicationId" value="<?php echo (int)$prefill['applicationId']; ?>">
            <input type="hidden" id="dbQuarterLocation" value="<?php echo htmlspecialchars($prefill['quarterLocation']); ?>">

            <form id="letterForm" onsubmit="generateLetter(event)">
                <div class="section-subheading">1. Application Reference</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Application Number</label>
                        <input type="text" id="appNo" placeholder="e.g. SLR/AOT/2026/0001"
                               oninput="autoFillApplicant(this.value)" required
                               value="<?php echo htmlspecialchars($prefill['appNo']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Applicant NIC</label>
                        <input type="text" id="nic" placeholder="e.g. 883421092V" required
                               value="<?php echo htmlspecialchars($prefill['nic']); ?>">
                    </div>
                </div>

                <div class="section-subheading">2. Applicant & Handed-Over Quarter Information</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Applicant Name</label>
                        <input type="text" id="fullName" required
                               value="<?php echo htmlspecialchars($prefill['fullName']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Designation</label>
                        <input type="text" id="designation" required
                               value="<?php echo htmlspecialchars($prefill['designation']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Current Workplace</label>
                        <input type="text" id="receiverAddress" required
                               value="<?php echo htmlspecialchars($prefill['receiverAddress']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Quarter No.</label>
                        <input type="text" id="quarterType" required
                               value="<?php echo htmlspecialchars($prefill['quarterType']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Quarter Name & Location</label>
                        <input type="text" id="quarterName" required
                               value="<?php echo htmlspecialchars($prefill['quarterName']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Keys & Documents Handover Date</label>
                        <input type="date" id="handoverDate" required value="<?php echo htmlspecialchars($todayIso); ?>">
                    </div>
                </div>

                <div class="section-subheading">3. Official Letter Subject & Content</div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Letter Subject</label>
                        <input type="text" id="letterSubject"
                               value="<?php
                                   echo $prefill['fullName']
                                       ? htmlspecialchars('Acknowledgment of Letter and Confirmation of Quarter Keys & Documents Handover - ' . $prefill['fullName'] . ' (' . $prefill['quarterType'] . ')')
                                       : 'Acknowledgment of Letter and Confirmation of Quarter Keys & Documents Handover - ';
                               ?>" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Main Body Description</label>
                        <textarea id="mainDescription" rows="5" required>We acknowledge the receipt of your official letter forwarded regarding the above-mentioned quarter allocation, and thank you for keeping us informed. We hereby confirm that the keys to the allocated railway quarters, along with all relevant inventory lists and official documents, have been duly handed over to the applicant.</textarea>
                    </div>
                </div>

                <div class="btn-actions">
                    <button type="submit" class="btn-generate" id="generateBtn">
                        <i class="fa-solid fa-eye"></i> Generate Official Letter Preview
                    </button>
                    <button type="button" class="btn-save-letter" id="saveBtn" onclick="saveLetterToDatabase()">
                        <i class="fa-solid fa-floppy-disk"></i> Save BDF Letter to Database
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div style="margin-top: 0;">
        <div id="letterPreviewBox" class="letter-preview-box">
            <div style="text-align:center; margin-bottom:20px;">
                <h2 style="margin:0;font-size:14pt;font-weight:bold;text-transform:uppercase;color:#000;">SRI LANKA RAILWAYS</h2>
                <p style="margin:3px 0;font-size:11pt;font-weight:bold;color:#000;">Quarters Management Branch - AOT</p>
                <p style="margin:2px 0;font-size:10pt;color:#000;">Department of Railways, P.O. Box 355, Colombo 10, Sri Lanka</p>
                <hr style="border:0;border-top:1.5px solid #000;margin-top:10px;">
            </div>
            <div style="margin-bottom:18px;font-size:11.5pt;line-height:1.45;">
                Building Foreman,<br>
                Sri Lanka Railways, Colombo 10.<br>
                <span id="pDate" style="display:inline-block;margin-top:6px;"></span>
            </div>
            <div style="margin-bottom:18px;font-size:11.5pt;line-height:1.45;">
                Subject Clerk,<br>
                Quarters Management Branch,<br>
                Sri Lanka Railways - AOT,<br>
                Colombo 10.
            </div>
            <div style="margin-bottom:15px;">
                <p style="margin:0 0 8px 0;">Dear Sir/Madam,</p>
                <h3 id="pSubjectTitle" style="font-size:11.5pt;text-decoration:underline;margin:8px 0;font-weight:bold;color:#000;"></h3>
            </div>
            <div style="margin-bottom:20px;text-align:justify;line-height:1.55;">
                <p id="pIntroPara" style="margin-bottom:12px;"></p>
                <p id="pCustomDescPara" style="margin-bottom:12px;"></p>
                <p style="margin:0;">Please update the Quarters Management Registry and proceed with the necessary administrative and rent deduction records accordingly.</p>
            </div>
            <div style="margin-top:30px;font-size:11.5pt;">
                <p style="margin:0 0 4px 0;">Thank you,</p>
                <p style="margin:0 0 22px 0;">Yours faithfully,</p>
                <p style="margin:0;">___________________________<br>
                <strong>Building Foreman,</strong><br>
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

<script>
    let generatedPdfBlob = null;

    const applicantsDatabase = {
        "SLR/AOT/2026/0001": { nic:"883421092V", fullName:"A. B. Perera", designation:"Station Master", receiverAddress:"Railway Station, Polgahawela", quarterType:"Q-01", quarterName:"Sunflower Manor, Polgahawela" },
        "SLR/AOT/2026/0002": { nic:"925631485V", fullName:"S. K. Silva", designation:"Signal Inspector", receiverAddress:"Railway Signal Office, Colombo Fort", quarterType:"Q-02", quarterName:"Railway Bungalow 02, Colombo 10" },
        "SLR/AOT/2026/0003": { nic:"951234789V", fullName:"M. N. Fernando", designation:"Loco Driver", receiverAddress:"Railway Shed, Anuradhapura", quarterType:"Q-03", quarterName:"Staff Flat 04, Anuradhapura" }
    };

    function autoFillApplicant(appNoValue) {
        const rawVal = appNoValue.trim();
        const trimmedKey = rawVal.toUpperCase().startsWith("SLR/AOT/") ? rawVal.toUpperCase() : "SLR/AOT/2026/" + rawVal;
        const m = applicantsDatabase[trimmedKey];
        if (m) {
            document.getElementById('nic').value = m.nic;
            document.getElementById('fullName').value = m.fullName;
            document.getElementById('designation').value = m.designation;
            document.getElementById('receiverAddress').value = m.receiverAddress;
            document.getElementById('quarterType').value = m.quarterType;
            document.getElementById('quarterName').value = m.quarterName;
            document.getElementById('letterSubject').value =
                "Acknowledgment of Letter and Confirmation of Quarter Keys & Documents Handover - " + m.fullName + " (" + m.quarterType + ")";
        }
    }

    function generateLetter(event) {
        event.preventDefault();
        updatePreviewContent();
        document.getElementById('letterPreviewBox').style.display = 'block';
        document.getElementById('letterActionsBar').style.display = 'flex';
        document.getElementById('letterPreviewBox').scrollIntoView({ behavior:'smooth' });
        generatePdfBlob();
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

        document.getElementById('pDate').innerText = new Date().toISOString().split('T')[0];
        document.getElementById('pSubjectTitle').innerText = letterSubject;
        document.getElementById('pIntroPara').innerHTML =
            `This is with reference to the letter sent by you regarding Application Reference No: <strong>${appNoFull}</strong> for <strong>${fullName}</strong> (NIC: <strong>${nic}</strong>, Designation: <strong>${designation}</strong>, Workplace: <strong>${receiverAddress}</strong>). Thank you for your notification; we confirm that the keys and official documents for Quarter No: <strong>${quarterType}</strong> (<strong>${quarterName}</strong>) were handed over to the applicant on <strong>${handoverDate}</strong>.`;
        document.getElementById('pCustomDescPara').innerHTML = mainDescription;
    }

    function generatePdfBlob() {
        const btn = document.getElementById('generateBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating PDF...';

        const element = document.getElementById('letterPreviewBox');
        element.classList.add('pdf-export');

        const opt = {
            margin: [8,10,8,10],
            html2canvas: { scale:2, useCORS:true, letterRendering:true }
        };

        html2pdf().set(opt).from(element).toCanvas().get('canvas')
            .then(function(canvas) {
                if (!canvas.width || !canvas.height) {
                    throw new Error('The letter preview could not be rendered.');
                }

                const JsPDF = window.jspdf && window.jspdf.jsPDF;
                if (typeof JsPDF !== 'function') {
                    throw new Error('The PDF export library is unavailable.');
                }

                const pdf = new JsPDF('p', 'mm', 'a4');
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                const marginX = 10;
                const marginY = 8;
                const scale = Math.min(
                    1,
                    (pageWidth - marginX * 2) / canvas.width,
                    (pageHeight - marginY * 2) / canvas.height
                );
                const imageWidth = canvas.width * scale;
                const imageHeight = canvas.height * scale;
                const imageX = (pageWidth - imageWidth) / 2;
                const imageData = canvas.toDataURL('image/jpeg', 0.98);

                pdf.addImage(imageData, 'JPEG', imageX, marginY, imageWidth, imageHeight);
                return pdf.output('blob');
            })
            .then(function(blob) {
                generatedPdfBlob = blob;
                element.classList.remove('pdf-export');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-eye"></i> Regenerate Preview';
                document.getElementById('saveBtn').style.display = 'inline-flex';
                showSuccess('Letter preview generated! Click "Save BDF Letter to Database" to store it permanently.');
            })
            .catch(function(err) {
                element.classList.remove('pdf-export');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-eye"></i> Generate Official Letter Preview';
                alert('PDF generation failed: ' + err.message);
            });
    }

    function saveLetterToDatabase() {
        if (!generatedPdfBlob) {
            alert('Please generate the letter first before saving.');
            return;
        }

        const loading = document.getElementById('loadingOverlay');
        document.getElementById('loadingText').innerText = 'Saving BDF letter to database...';
        loading.classList.add('show');

        const appNo = document.getElementById('appNo').value || '';
        const nic = document.getElementById('nic').value || '';
        const fullName = document.getElementById('fullName').value || '';
        const letterSubject = document.getElementById('letterSubject').value || '';
        const quarterNo = document.getElementById('quarterType').value || '';
        const quarterLoc = document.getElementById('dbQuarterLocation').value || document.getElementById('quarterName').value || '';
        const handoverDate = document.getElementById('handoverDate').value || '';
        const applicationId = document.getElementById('dbApplicationId').value || '0';

        const formData = new FormData();
        formData.append('application_id', applicationId);
        formData.append('nic', nic);
        formData.append('application_no', appNo);
        formData.append('applicant_name', fullName);
        formData.append('quarter_no', quarterNo);
        formData.append('quarter_location', quarterLoc);
        formData.append('handover_date', handoverDate);
        formData.append('letter_subject', letterSubject);
        formData.append('letter_pdf', generatedPdfBlob, 'BDF_Letter_' + appNo + '_' + nic + '.pdf');

        fetch('<?php echo baseUrl('letters/save-bdf-pdf'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            loading.classList.remove('show');
            if (data.success) {
                showSuccess('✅ BDF letter saved to database successfully! File ID: ' + data.letter_id + '. <a href="' + data.file_url + '" target="_blank" style="color:#047857;font-weight:700;">Click here to open PDF</a>');
                const saveBtn = document.getElementById('saveBtn');
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fa-solid fa-check"></i> Saved!';
            } else {
                alert('Save failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            loading.classList.remove('show');
            alert('Network error: ' + err.message);
        });
    }

    function showSuccess(msg) {
        const a = document.getElementById('successAlert');
        document.getElementById('successMessage').innerHTML = msg;
        a.classList.add('show');
        a.scrollIntoView({ behavior:'smooth', block:'center' });
        setTimeout(() => a.classList.remove('show'), 8000);
    }

    function triggerPrint() {
        updatePreviewContent();
        document.getElementById('letterPreviewBox').style.display = 'block';
        window.print();
    }

    window.addEventListener('DOMContentLoaded', function() {
        const appNo = document.getElementById('appNo').value.trim();
        if (appNo) {
            updatePreviewContent();
            document.getElementById('letterPreviewBox').style.display = 'block';
            document.getElementById('letterActionsBar').style.display = 'flex';
            setTimeout(generatePdfBlob, 500);
        }
    });
</script>

<?php require_once __DIR__ . '/../layouts/admin_footer.php'; ?>