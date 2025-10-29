<?php
require_once "gad_portal.php";

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'No employee ID provided.']);
    exit;
}

$id = intval($_POST['id']);
$con = newCon();

if ($con->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $con->connect_error]);
    exit;
}

// ✅ Soft delete — mark as Inactive and set inactive_date to today
$sql = "UPDATE employee_tbl 
        SET status = 'Inactive', inactive_date = CURDATE()
        WHERE id = ?";

$stmt = $con->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'SQL prepare failed: ' . $con->error]);
    exit;
}

$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Employee has been marked as inactive.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'No record updated. Employee ID not found.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update employee: ' . $stmt->error]);
}

$stmt->close();
$con->close();
?>