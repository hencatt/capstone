<?php
error_reporting(0);
header('Content-Type: application/json');
require_once 'gad_portal.php';
header('Content-Type: application/json');

$con = newCon();
if ($con->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['emp_id']);
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid employee ID']);
        exit;
    }

    $fname = $con->real_escape_string($_POST['inputFname']);
    $mname = $con->real_escape_string($_POST['inputMname']);
    $lname = $con->real_escape_string($_POST['inputLname']);
    $email = $con->real_escape_string($_POST['inputEmail']);
    $contact = $con->real_escape_string($_POST['inputContact']);
    $department = $con->real_escape_string($_POST['inputDepartment']);
    $campus = $con->real_escape_string($_POST['inputCampus']);
    $birthdate = $con->real_escape_string($_POST['inputBirthdate']);
    $priority = $con->real_escape_string($_POST['inputPriority']);
    $street = $con->real_escape_string($_POST['inputStAddress']);
    $city = $con->real_escape_string($_POST['inputCity']);
    $province = $con->real_escape_string($_POST['inputProvince']);
    $marital = $con->real_escape_string($_POST['inputMaritalStatus']);
    $size = $con->real_escape_string($_POST['inputSize']);
    $sex = $con->real_escape_string($_POST['inputSex']);
    $gender = $con->real_escape_string($_POST['inputGender']);
    $income = $con->real_escape_string($_POST['inputIncome']);
    $children_num = isset($_POST['inputChildrenNum']) ? intval($_POST['inputChildrenNum']) : 0;
    $concern = $con->real_escape_string($_POST['inputConcern']);

    $address = trim("$street, $city, $province", ', ');

    // --- Update employee_tbl ---
    $updateEmp = "
        UPDATE employee_tbl 
        SET 
            email = '$email', 
            contact_no = '$contact', 
            department = '$department', 
            campus = '$campus'
        WHERE id = '$id'
    ";

    // --- Update employee_info ---
    $updateInfo = "
        UPDATE employee_info 
        SET 
            fname = '$fname',
            m_initial = '$mname',
            lname = '$lname',
            birthday = '$birthdate',
            priority_status = '$priority',
            address = '$address',
            marital_status = '$marital',
            size = '$size',
            sex = '$sex',
            gender = '$gender',
            income = '$income',
            children_num = '$children_num',
            concern = '$concern'
        WHERE employee_id = '$id'
    ";

    $empResult = $con->query($updateEmp);
    $infoResult = $con->query($updateInfo);

    if ($empResult && $infoResult) {
        echo json_encode(['success' => true, 'message' => 'Employee updated successfully!']);
    } else {
        echo json_encode([
            'error' => 'Database update failed.',
            'emp_error' => $con->error,
            'info_error' => $con->error
        ]);
    }

    error_log("✅ updateEmployee.php reached");
    $con->close();
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
