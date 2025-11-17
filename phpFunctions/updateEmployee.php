<?php
// Save as: phpFunctions/addEmployee.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once 'gad_portal.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$con = newCon();
if ($con->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize and validate input
        $fname = trim($_POST['fname'] ?? '');
        $mname = trim($_POST['m_initial'] ?? '');
        $lname = trim($_POST['lname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact_no = trim($_POST['contact_no'] ?? '');
        $department = $_POST['department'] ?? '';
        $campus = $_POST['campus'] ?? '';
        $birthday = $_POST['birthday'] ?? '';
        $priority_status = $_POST['priority_status'] ?? 'None';
        $address = trim($_POST['address'] ?? '');
        $marital_status = $_POST['marital_status'] ?? '';
        $size = $_POST['size'] ?? '';
        $sex = $_POST['sex'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $income = $_POST['income'] ?? '';
        $children_num = isset($_POST['children_num']) ? intval($_POST['children_num']) : 0;
        $concern = trim($_POST['concern'] ?? '') ?: 'N/A';
        $status = 'Active';

        // Validate required fields
        if (empty($fname) || empty($lname) || empty($email)) {
            echo json_encode(['error' => 'First Name, Last Name, and Email are required']);
            exit;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Invalid email format']);
            exit;
        }

        // Check if email already exists
        $checkStmt = $con->prepare("SELECT id FROM employee_tbl WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            echo json_encode(['error' => 'Email already exists in the system']);
            $checkStmt->close();
            $con->close();
            exit;
        }
        $checkStmt->close();

        // Start transaction
        $con->begin_transaction();

        // Insert into employee_tbl
        $stmt_emp = $con->prepare("
            INSERT INTO employee_tbl (email, contact_no, department, campus, status) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt_emp->bind_param("sssss", $email, $contact_no, $department, $campus, $status);

        if (!$stmt_emp->execute()) {
            throw new Exception("Failed to insert into employee_tbl: " . $stmt_emp->error);
        }

        $employee_id = $con->insert_id;
        $stmt_emp->close();

        // Insert into employee_info
        $stmt_info = $con->prepare("
            INSERT INTO employee_info 
            (fname, m_initial, lname, address, birthday, marital_status, sex, gender, 
             priority_status, size, income, employee_id, children_num, concern) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt_info->bind_param(
            "sssssssssssiis",
            $fname, $mname, $lname, $address, $birthday, $marital_status,
            $sex, $gender, $priority_status, $size, $income,
            $employee_id, $children_num, $concern
        );

        if (!$stmt_info->execute()) {
            throw new Exception("Failed to insert into employee_info: " . $stmt_info->error);
        }
        $stmt_info->close();

        // Commit transaction
        $con->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Employee added successfully!',
            'employee_id' => $employee_id
        ]);

    } catch (Exception $e) {
        // Rollback on error
        $con->rollback();
        echo json_encode(['error' => $e->getMessage()]);
    } finally {
        $con->close();
    }

} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>