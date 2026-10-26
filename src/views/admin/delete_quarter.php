<?php
/**
 * Delete Quarter - Sri Lanka Railways Admin
 * Handles the delete button from quarters_list.php
 */
require_once __DIR__ . '/../../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . baseUrl('admin/quarters_list.php'));
    exit;
}

$conn = getDBConnection();
// Fetch quarter info first so we can clean up maintenance
$info = $conn->prepare("SELECT quarter_number, location_name FROM quarters WHERE id = ? LIMIT 1");
$info->bind_param("i", $id);
$info->execute();
$row = $info->get_result()->fetch_assoc();
$info->close();

$stmt = $conn->prepare("DELETE FROM quarters WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$deleted = $stmt->affected_rows;
$stmt->close();

if ($deleted && $row) {
    $del = $conn->prepare("DELETE FROM maintenance WHERE quarter_number = ? AND location_name = ?");
    $del->bind_param("ss", $row['quarter_number'], $row['location_name']);
    $del->execute();
    $del->close();
}

$conn->close();

header('Location: ' . baseUrl('admin/quarters_list.php?deleted=' . ($deleted ? '1' : '0')));
exit;