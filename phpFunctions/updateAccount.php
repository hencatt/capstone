<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'gad_portal.php';
$conn = newCon();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Read POST (use the same names your form uses)
$id         = isset($_POST['acc_id']) ? intval($_POST['acc_id']) : 0;
$username   = trim($_POST['acc_username'] ?? '');
$email      = trim($_POST['acc_email'] ?? '');
$fname      = trim($_POST['acc_fname'] ?? '');
$lname      = trim($_POST['acc_lname'] ?? '');
$position   = trim($_POST['acc_position'] ?? '');
$department = trim($_POST['acc_department'] ?? '');
$campus     = trim($_POST['acc_campus'] ?? '');
$password   = trim($_POST['acc_password'] ?? ''); // optional: leave blank to keep current

// Basic validation
if ($id <= 0 || $username === '' || $email === '') {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}


$params = [];
$types  = '';

if ($password !== '') {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE accounts_tbl
            SET username = ?, email = ?, pass = ?, fname = ?, lname = ?, position = ?, department = ?, campus = ?
            WHERE id = ?";
    $types = "ssssssssi";
    $params = [$username, $email, $hashed, $fname, $lname, $position, $department, $campus, $id];
} else {
    $sql = "UPDATE accounts_tbl
            SET username = ?, email = ?, fname = ?, lname = ?, position = ?, department = ?, campus = ?
            WHERE id = ?";
    $types = "sssssssi";
    $params = [$username, $email, $fname, $lname, $position, $department, $campus, $id];
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

// Bind dynamically
$bind_names = array_merge([$types], $params);
$tmp = [];
foreach ($bind_names as $k => $v) {
    $tmp[$k] = &$bind_names[$k];
}
call_user_func_array([$stmt, 'bind_param'], $tmp);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Account updated successfully']);
} else {
    echo json_encode(['error' => 'Database update failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
