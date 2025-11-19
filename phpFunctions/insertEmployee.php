<?php
require_once './gad_portal.php';
require_once './checkUser.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

checkUser($_SESSION['user_id']);
$user = getUser();
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];

// Only Focal Persons can add/edit employees through this endpoint
if ($currentPosition !== "Focal Person") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if this is an update (emp_id present) or insert (emp_id empty)
        $emp_id = !empty($_POST['emp_id']) ? intval($_POST['emp_id']) : null;
        $isUpdate = !empty($emp_id);

        // Get and sanitize input data from the form field names
        $fname = trim($_POST['inputFname'] ?? '');
        $mname = trim($_POST['inputMname'] ?? '');
        $lname = trim($_POST['inputLname'] ?? '');
        $email = trim($_POST['inputEmail'] ?? '');
        $contact = trim($_POST['inputContact'] ?? '');
        $sex = trim($_POST['inputSex'] ?? '');
        $gender = trim($_POST['inputGender'] ?? '');
        $otherGender = trim($_POST['otherGender'] ?? '');
        $birthdate = trim($_POST['inputBirthdate'] ?? '');
        $address = trim($_POST['inputAddress'] ?? '');
        $maritalStatus = trim($_POST['inputMaritalStatus'] ?? '');
        $size = trim($_POST['inputSize'] ?? '');
        $priorityStatus = trim($_POST['inputPriority'] ?? 'None');
        $income = trim($_POST['inputIncome'] ?? '');
        $hasChildren = trim($_POST['hasChildren'] ?? 'No');
        $childrenNum = !empty($_POST['inputChildrenNum']) ? intval($_POST['inputChildrenNum']) : 0;
        $concern = trim($_POST['inputConcern'] ?? '');

        // Use Focal Person's department and campus
        $department = $currentDepartment;
        $campus = $currentCampus;
        $status = 'Active';

        // Handle LGBTQIA+ other gender
        if ($gender === 'LGBTQIA+' && !empty($otherGender)) {
            $gender = $otherGender;
        }

        // Validate required fields
        if (
            empty($fname) || empty($lname) || empty($email) || empty($sex) ||
            empty($gender) || empty($birthdate) || empty($maritalStatus) || empty($address) || empty($contact)
        ) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            exit();
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit();
        }

        // Validate birthdate (must be 18+ years old)
        $birthDateTime = new DateTime($birthdate);
        $today = new DateTime();
        $age = $today->diff($birthDateTime)->y;
        if ($age < 18) {
            echo json_encode(['success' => false, 'message' => 'Employee must be at least 18 years old']);
            exit();
        }

        // Validate contact number (11 digits)
        if (!preg_match('/^\d{11}$/', $contact)) {
            echo json_encode(['success' => false, 'message' => 'Contact number must be exactly 11 digits']);
            exit();
        }

        // Start transaction
        $con->begin_transaction();

        if ($isUpdate) {
            // UPDATE EXISTING EMPLOYEE

            // Check if employee exists
            $checkStmt = $con->prepare("SELECT id FROM employee_tbl WHERE id = ?");
            $checkStmt->bind_param("i", $emp_id);
            $checkStmt->execute();
            $checkStmt->bind_result($existingId);
            $checkStmt->fetch();
            $checkStmt->close();

            if (!$existingId) {
                $con->rollback();
                echo json_encode(['success' => false, 'message' => 'Employee not found']);
                exit();
            }

            // Check if new email is already used by another employee
            $emailCheckStmt = $con->prepare("SELECT id FROM employee_tbl WHERE email = ? AND id != ?");
            $emailCheckStmt->bind_param("si", $email, $emp_id);
            $emailCheckStmt->execute();
            $emailCheckStmt->bind_result($duplicateId);
            $emailCheckStmt->fetch();
            $emailCheckStmt->close();

            if ($duplicateId) {
                $con->rollback();
                echo json_encode(['success' => false, 'message' => 'Email is already used by another employee']);
                exit();
            }

            // Update employee_tbl
            $updateEmployee = $con->prepare("UPDATE employee_tbl SET email = ?, contact_no = ?, status = ? WHERE id = ?");
            $updateEmployee->bind_param("sssi", $email, $contact, $status, $emp_id);

            if (!$updateEmployee->execute()) {
                throw new Exception("Failed to update employee record: " . $updateEmployee->error);
            }
            $updateEmployee->close();

            // Update employee_info
            $updateInfo = $con->prepare("UPDATE employee_info SET 
                fname = ?, m_initial = ?, lname = ?, sex = ?, gender = ?, birthday = ?, 
                address = ?, marital_status = ?, size = ?, priority_status = ?, 
                income = ?, children_num = ?, concern = ? 
                WHERE employee_id = ?");
            $updateInfo->bind_param(
                "sssssssssssssi",
                $fname,
                $mname,
                $lname,
                $sex,
                $gender,
                $birthdate,
                $address,
                $maritalStatus,
                $size,
                $priorityStatus,
                $income,
                $childrenNum,
                $concern,
                $emp_id
            );

            if (!$updateInfo->execute()) {
                throw new Exception("Failed to update employee info: " . $updateInfo->error);
            }
            $updateInfo->close();

            $con->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Employee updated successfully',
                'employee_id' => $emp_id
            ]);

        } else {
            // INSERT NEW EMPLOYEE

            // Check if employee with this email already exists
            $checkStmt = $con->prepare("SELECT id FROM employee_tbl WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkStmt->bind_result($existingId);
            $checkStmt->fetch();
            $checkStmt->close();

            if ($existingId) {
                $con->rollback();
                echo json_encode(['success' => false, 'message' => 'An employee with this email already exists']);
                exit();
            }

            // Insert into employee_tbl
            $insertEmployee = $con->prepare("INSERT INTO employee_tbl (email, contact_no, department, campus, status) VALUES (?, ?, ?, ?, ?)");
            $insertEmployee->bind_param("sssss", $email, $contact, $department, $campus, $status);

            if (!$insertEmployee->execute()) {
                throw new Exception("Failed to create employee record: " . $insertEmployee->error);
            }

            $employee_id = $con->insert_id;
            $insertEmployee->close();

            // Insert into employee_info
            $insertInfo = $con->prepare("INSERT INTO employee_info 
                (fname, m_initial, lname, sex, gender, birthday, address, marital_status, 
                size, priority_status, income, children_num, concern, employee_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insertInfo->bind_param(
                "ssssssssssssssi",
                $fname,
                $mname,
                $lname,
                $sex,
                $gender,
                $birthdate,
                $address,
                $maritalStatus,
                $size,
                $priorityStatus,
                $income,
                $childrenNum,
                $concern,
                $employee_id
            );

            if (!$insertInfo->execute()) {
                throw new Exception("Failed to create employee info: " . $insertInfo->error);
            }
            $insertInfo->close();

            $con->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Employee added successfully',
                'employee_id' => $employee_id
            ]);
        }

    } catch (Exception $e) {
        $con->rollback();
        error_log("Employee Save Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>