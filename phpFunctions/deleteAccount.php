<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'gad_portal.php';
$conn = newCon();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['error' => 'Invalid account ID']);
    exit;
}

// Soft delete — set is_active = 0
$stmt = $conn->prepare("UPDATE accounts_tbl SET is_active = 0 WHERE id = ?");
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Account deactivated successfully']);
} else {
    echo json_encode(['error' => 'Failed to deactivate account: ' . $stmt->error]);
}

$stmt->close();
$conn->close();