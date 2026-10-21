<?php
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ((int)($_SESSION['role_id'] ?? 0) !== 7) {
    http_response_code(403);
    exit('Access denied.');
}

if (isset($_GET['cancel'])) {
    clearDirectAllocationSession();
    header('Location: ' . baseUrl('admin/direct_allocations.php'));
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . baseUrl('admin/direct_allocations.php'));
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare(
    "SELECT aq.nic, aq.computer_no, aq.quarter_no, aq.quarter_name,
            aq.quarter_location, aq.quarter_type, aq.resident_name,
            aq.designation, aq.workplace, COALESCE(u.mobile, '') AS phone_no
       FROM allocated_quarters aq
       LEFT JOIN users u ON u.nic = aq.nic
      WHERE aq.id = ? AND aq.source = 'direct'
      LIMIT 1"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$row) {
    header('Location: ' . baseUrl('admin/direct_allocations.php'));
    exit;
}

$_SESSION['direct_alloc'] = [
    'allocation_id' => (int)$id,
    'nic' => $row['nic'],
    'computer_no' => $row['computer_no'],
    'phone_no' => $row['phone_no'],
    'resident' => $row['resident_name'],
    'designation' => $row['designation'],
    'workplace' => $row['workplace'],
    'quarter_no' => $row['quarter_no'],
    'quarter_name' => $row['quarter_name'],
    'quarter_loc' => $row['quarter_location'],
    'quarter_type' => $row['quarter_type'],
];

header('Location: ' . baseUrl('application/form/en?direct=1'));
exit;
