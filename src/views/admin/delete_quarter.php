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
$stmt = $conn->prepare("DELETE FROM quarters WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$deleted = $stmt->affected_rows;
$stmt->close();
$conn->close();

header('Location: ' . baseUrl('admin/quarters_list.php?deleted=' . ($deleted ? '1' : '0')));
exit;