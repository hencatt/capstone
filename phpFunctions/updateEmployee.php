<?php
// Save as: phpFunctions/updateEmployee.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once 'gad_portal.php';

// Check if user is logged in
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
        // Sanitize and validate input - HANDLE BOTH naming conventions
        $emp_id = isset($_POST['emp_id']) ? intval($_POST['emp_id']) : 0;
        
        // Try both naming conventions for form fields
        $fname = trim($_POST['inputFname'] ?? $_POST['fname'] ?? '');
        $mname = trim($_POST['inputMname'] ?? $_POST['m_initial'] ?? '');
        $lname = trim($_POST['inputLname'] ?? $_POST['lname'] ?? '');
        $email = trim($_POST['inputEmail'] ?? $_POST['email'] ?? '');
        $contact_no = trim($_POST['inputContact'] ?? $_POST['contact_no'] ?? '');
        $department = $_POST['inputDepartment'] ?? $_POST['department'] ?? '';
        $campus = $_POST['inputCampus'] ?? $_POST['campus'] ?? '';
        $birthday = $_POST['inputBirthdate'] ?? $_POST['birthday'] ?? '';
        $priority_status = $_POST['inputPriority'] ?? $_POST['priority_status'] ?? 'None';
        $address = trim($_POST['inputAddress'] ?? $_POST['address'] ?? '');
        $marital_status = $_POST['inputMaritalStatus'] ?? $_POST['marital_status'] ?? '';
        $size = $_POST['inputSize'] ?? $_POST['size'] ?? '';
        $sex = $_POST['inputSex'] ?? $_POST['sex'] ?? '';
        $gender = $_POST['inputGender'] ?? $_POST['gender'] ?? '';
        $income = $_POST['inputIncome'] ?? $_POST['income'] ?? '';
        $children_num = isset($_POST['inputChildrenNum']) ? intval($_POST['inputChildrenNum']) : (isset($_POST['children_num']) ? intval($_POST['children_num']) : 0);
        $concern = trim($_POST['inputConcern'] ?? $_POST['concern'] ?? '') ?: 'N/A';

        // Handle LGBTQIA+ gender
        if ($gender === 'LGBTQIA+' && !empty($_POST['otherGender'])) {
            $gender = $_POST['otherGender'];
        }

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

        // Start transaction
        $con->begin_transaction();

        // Check if this is an UPDATE or INSERT
        if ($emp_id > 0) {
            // UPDATE EXISTING EMPLOYEE
            
            // Update employee_tbl
            $stmt_emp = $con->prepare("
                UPDATE employee_tbl 
                SET email = ?, contact_no = ?, department = ?, campus = ?
                WHERE id = ?
            ");
            $stmt_emp->bind_param("ssssi", $email, $contact_no, $department, $campus, $emp_id);
            
            if (!$stmt_emp->execute()) {
                throw new Exception("Failed to update employee_tbl: " . $stmt_emp->error);
            }
            $stmt_emp->close();

            // Update employee_info
            $stmt_info = $con->prepare("
                UPDATE employee_info 
                SET fname = ?, m_initial = ?, lname = ?, address = ?, birthday = ?, 
                    marital_status = ?, sex = ?, gender = ?, priority_status = ?, 
                    size = ?, income = ?, children_num = ?, concern = ?
                WHERE employee_id = ?
            ");
            $stmt_info->bind_param(
                "sssssssssssiis",
                $fname, $mname, $lname, $address, $birthday, $marital_status,
                $sex, $gender, $priority_status, $size, $income,
                $children_num, $concern, $emp_id
            );

            if (!$stmt_info->execute()) {
                throw new Exception("Failed to update employee_info: " . $stmt_info->error);
            }
            $stmt_info->close();

            $con->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Employee updated successfully!',
                'employee_id' => $emp_id
            ]);

        } else {
            // INSERT NEW EMPLOYEE
            $status = 'Active';
            
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

            $con->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Employee added successfully!',
                'employee_id' => $employee_id
            ]);
        }

    } catch (Exception $e) {
        $con->rollback();
        echo json_encode(['error' => $e->getMessage()]);
    } finally {
        $con->close();
    }

} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>