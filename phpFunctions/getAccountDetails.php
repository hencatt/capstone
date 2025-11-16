<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'gad_portal.php';
$conn = newCon();

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['error' => 'Missing or invalid id']);
    exit;
}

$id = (int) $_POST['id'];

$sql = "SELECT 
            id,
            username,
            email,
            fname,
            lname,
            position,
            department,
            campus
        FROM accounts_tbl
        WHERE id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    $conn->close();
    exit;
}

$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $row = $res->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Account not found']);
}

$stmt->close();
$conn->close();
