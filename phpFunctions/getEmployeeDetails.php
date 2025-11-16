<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['error' => 'Missing or invalid id']);
    exit;
}

$id = (int) $_POST['id'];

$conn = new mysqli('localhost', 'root', '', 'gad_portal');
if ($conn->connect_error) {
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

$sql = "SELECT 
            ei.fname,
            ei.m_initial,
            ei.lname,
            ei.address,
            ei.birthday,
            ei.marital_status,
            ei.sex,
            ei.gender,
            ei.priority_status,
            ei.size,
            ei.income,
            ei.children_num,
            ei.concern,
            et.email,
            et.contact_no,
            et.department,
            et.campus
        FROM employee_info ei
        INNER JOIN employee_tbl et ON ei.id = et.id
        WHERE et.id = ? LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    $conn->close();
    exit;
}

$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $row = $res->fetch_assoc()) {
    // ✅ Split address into parts safely
    $parts = array_map('trim', explode(',', $row['address'] ?? ''));
    $row['street']   = $parts[0] ?? '';
    $row['city']     = $parts[1] ?? '';
    $row['province'] = $parts[2] ?? '';

    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Record not found']);
}

$stmt->close();
$conn->close();
