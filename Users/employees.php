<?php
require_once 'includes.php';
require_once '../phpFunctions/email.php';

session_start();

checkUser($_SESSION['user_id']);
$user = getUser();
$currentUser = $user['fullname'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];
$currentPosition = $user['position'];
doubleCheck($currentPosition);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fname']) && isset($_POST['lname']) && !isset($_POST['add_employee'])) {
        // Add Account Logic
        $fname = trim($_POST['fname'] ?? '');
        $lname = trim($_POST['lname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $plainPassword = $_POST['pass'] ?? '';

        // Handle multiple positions
        $positions = $_POST['pos'] ?? [];

        // Convert array to comma-separated string
        if (is_array($positions)) {
            $position = implode(', ', $positions);
        } else {
            $position = $positions;
        }

        // Use null coalescing to handle undefined dept/campus
        $department = $_POST['dept'] ?? $currentDepartment;
        $campus = $_POST['campus'] ?? $currentCampus;
        $status = 'Active';

        // Validate required fields
        if (empty($fname) || empty($lname) || empty($email) || empty($plainPassword)) {
            alertError("Error", "All fields are required");
            exit();
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            alertError("Error", "Invalid email format");
            exit();
        }

        // Validate position
        if (empty($position)) {
            alertError("Error", "At least one position is required");
            exit();
        }

        // Hash password
        $password = password_hash($plainPassword, PASSWORD_DEFAULT);

        // Start transaction for data integrity
        $con->begin_transaction();

        try {
            // STEP 1: Check if employee exists with this email
            $checkEmployee = $con->prepare("SELECT id FROM employee_tbl WHERE email = ?");
            $checkEmployee->bind_param("s", $email);
            $checkEmployee->execute();
            $checkEmployee->bind_result($employee_id);
            $checkEmployee->fetch();
            $checkEmployee->close();

            // STEP 2: If employee doesn't exist, create one first
            if (!$employee_id) {
                // Insert into employee_tbl first
                $insertEmployee = $con->prepare("INSERT INTO employee_tbl (email, contact_no, department, campus, status) VALUES (?, '', ?, ?, ?)");
                $insertEmployee->bind_param("ssss", $email, $department, $campus, $status);

                if (!$insertEmployee->execute()) {
                    throw new Exception("Failed to create employee record: " . $insertEmployee->error);
                }

                $employee_id = $con->insert_id;
                $insertEmployee->close();

                // Insert basic info into employee_info
                $insertInfo = $con->prepare("INSERT INTO employee_info (fname, lname, employee_id) VALUES (?, ?, ?)");
                $insertInfo->bind_param("ssi", $fname, $lname, $employee_id);

                if (!$insertInfo->execute()) {
                    throw new Exception("Failed to create employee info: " . $insertInfo->error);
                }
                $insertInfo->close();
            }

            // STEP 3: Check if account already exists for this employee
            $checkAccount = $con->prepare("SELECT id, position FROM accounts_tbl WHERE id = ?");
            $checkAccount->bind_param("i", $employee_id);
            $checkAccount->execute();
            $checkAccount->bind_result($existing_account_id, $existing_position);
            $checkAccount->fetch();
            $checkAccount->close();

            if ($existing_account_id) {
                // UPDATED: Account exists - UPDATE it with new positions (Ctrl+Click mode)

                // Merge existing positions with new ones (remove duplicates)
                $existingPositions = array_map('trim', explode(',', $existing_position));
                $newPositions = array_map('trim', explode(',', $position));
                $mergedPositions = array_unique(array_merge($existingPositions, $newPositions));
                $finalPosition = implode(', ', $mergedPositions);

                $updateAccount = $con->prepare("UPDATE accounts_tbl SET position = ? WHERE id = ?");
                $updateAccount->bind_param("si", $finalPosition, $employee_id);

                if (!$updateAccount->execute()) {
                    throw new Exception("Failed to update account positions: " . $updateAccount->error);
                }
                $updateAccount->close();

                // Commit transaction
                $con->commit();

                alertSuccess("Updated", "Account positions updated successfully! New positions: " . $finalPosition);

                // Log the update
                error_log("Account positions updated for employee ID $employee_id: $existing_position → $finalPosition");

            } else {
                // STEP 4: Account doesn't exist - INSERT new account
                $insertAccount = $con->prepare("INSERT INTO accounts_tbl (id, fname, lname, email, pass, position, department, campus, date_created, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)");
                $insertAccount->bind_param("isssssss", $employee_id, $fname, $lname, $email, $password, $position, $department, $campus);

                if (!$insertAccount->execute()) {
                    throw new Exception("Failed to create account: " . $insertAccount->error);
                }
                $insertAccount->close();

                // Commit transaction
                $con->commit();

                alertSuccess("Done", "Account Created Successfully with position(s): " . $position);

                // Send credentials email to the new user
                sendUserCredentials($email, $plainPassword, $fname, $lname);
            }

            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();

        } catch (Exception $e) {
            // Rollback on error
            $con->rollback();
            alertError("Error", $e->getMessage());
            error_log("Add/Update Account Error: " . $e->getMessage());
        }
    }
} elseif (isset($_POST['id'])) {
    // Deactivate User Logic
    $userId = $_POST['id'];

    $conn = new mysqli('localhost', 'root', '', 'gad_portal');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE accounts_tbl SET is_active = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        alertSuccess("Activated", "User account is activated");
    } else {
        alertError("Error", "Please Try Again");
    }

    $stmt->close();
    $conn->close();
}
// // Update Account Logic
// if (isset($_POST['update_account'])) {
//     $userId = $_POST['edit_id'];
//     $fname = $_POST['edit_fname'];
//     $lname = $_POST['edit_lname'];
//     $username = $_POST['edit_username'];
//     $email = $_POST['edit_email'];
//     $password = !empty($_POST['edit_password']) ? password_hash($_POST['edit_password'], PASSWORD_DEFAULT) : null;
//     $position = $_POST['edit_position'];
//     $department = $_POST['edit_department'];

//     $conn = new mysqli('localhost', 'root', '', 'gad_portal');

//     if ($conn->connect_error) {
//         die("Connection failed: " . $conn->connect_error);
//     }

//     if ($password) {
//         $sql = "UPDATE accounts_tbl SET fname = ?, lname = ?, username = ?, email = ?, pass = ?, position = ?, department = ? WHERE id = ?";
//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param("sssssssi", $fname, $lname, $username, $email, $password, $position, $department, $userId);
//     } else {
//         $sql = "UPDATE accounts_tbl SET fname = ?, lname = ?, username = ?, email = ?, position = ?, department = ? WHERE id = ?";
//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param("ssssssi", $fname, $lname, $username, $email, $position, $department, $userId);
//     }

//     if ($stmt->execute()) {
//         alertSuccess("Updated", "Account updated successfully!");
//     } else {
//         alertError("Error", "There has been an error updating account");
//     }

//     $stmt->close();
//     $conn->close();

//     // Redirect to the same page to prevent form resubmission
//     header("Location: " . $_SERVER['PHP_SELF']);
//     exit();
// }

$conn = new mysqli('localhost', 'root', '', 'gad_portal');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>

<!DOCTYPE html>
<html lang="en">
<?php include '../phpFunctions/email.php'; ?>

<head>
    <?= headerLinks("Employees"); ?>
</head>

<body>
    <style>
        /* Modal container */
        .modals {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            /* Semi-transparent background */
            display: none;
            /* Hidden by default */
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        /* Modal content */
        .modal_add_account {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 400px;
            max-width: 90%;
        }


        .modal_add_account .modal_title h2 {
            margin: 0 0 1rem;
            font-size: 1.8rem;
            font-weight: bold;
            text-align: center;
            color: #333;
        }

        .form_add_account {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form_add_account input,
        .form_add_account select {
            padding: 0.8rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .form_add_account input:focus,
        .form_add_account select:focus {
            border-color: #007bff;
        }

        .form_add_account input::placeholder {
            color: #aaa;
            font-size: 0.9rem;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
        }

        .buttons button {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .buttons button[type="submit"] {
            background-color: #28a745;
            color: #fff;
        }

        .buttons button[type="submit"]:hover {
            background-color: #218838;
        }

        .buttons .add_btn_close {
            background-color: #dc3545;
            color: #fff;
        }

        .buttons .add_btn_close:hover {
            background-color: #c82333;
        }

        .modal_view_all {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 80%;
            max-width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal_edit_account {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 400px;
            max-width: 90%;
        }

        .modal_edit_account .modal_title h2 {
            margin: 0 0 1rem;
            font-size: 1.8rem;
            font-weight: bold;
            text-align: center;
            color: #333;
        }

        .form_edit_account {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form_edit_account input,
        .form_edit_account select {
            padding: 0.8rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .form_edit_account input:focus,
        .form_edit_account select:focus {
            border-color: #007bff;
        }

        select[multiple] {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ced4da;
            background-color: #fff;
        }

        select[multiple]:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        select[multiple] option {
            padding: 8px 12px;
            border-radius: 4px;
            margin: 2px 0;
        }

        select[multiple] option:hover {
            background-color: #e9ecef;
        }

        select[multiple] option:checked {
            background: linear-gradient(0deg, #007bff 0%, #0056b3 100%);
            color: white;
            font-weight: 500;
        }

        /* Selected position indicator */
        .position-indicator {
            display: inline-block;
            padding: 4px 10px;
            margin: 2px;
            background-color: #007bff;
            color: white;
            border-radius: 12px;
            font-size: 0.85rem;
        }

        /* Form group styling */
        .form-group label small {
            display: block;
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 4px;
        }

        /* Modal adjustments for multi-select */
        #editAccountModal .modal-body select[multiple],
        #add_account_modal select[multiple] {
            width: 100%;
            min-height: 120px;
        }

        /* Improve readability */
        .form_add_account select[multiple] option,
        #acc_position option {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        /* Help text styling */
        .form-text.text-muted {
            font-size: 0.8rem;
            color: #6c757d !important;
            margin-top: 0.25rem;
            display: block;
        }

        /* Icon styling in labels */
        .form-label i {
            margin-right: 5px;
            color: #007bff;
        }
    </style>


    <div class="row everything">
        <div class="col sidebar" id="sidebar">
            <?php sidebar("employees", $currentPosition);
            ?>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-10 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", "$currentPosition", "employees") ?>
            <div id="contents">
                <div class="row mt-4">
                    <div class="col">
                        <h1>Employees</h1>
                    </div>
                </div>
                <?php
                if ($currentPosition === "Director" || $currentPosition === "Technical Assistant"): ?>
                    <div class="col d-flex justify-content-end">
                        <button id="add_account" class="btn btn-outline-success">
                            Add Account
                            <ion-icon name="add-outline" class="add-icon"></ion-icon>
                        </button>
                    </div>
                </div>

            <?php elseif ($currentPosition === "Focal Person"): ?>

                <div class="row mt-2">
                    <div class="col d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-success" id="addEmployeeBtn">
                            Add Employee
                        </button>
                    </div>
                    <!-- <div class="col-2">
                            <button id="add_account" class="btn btn-outline-success">
                                Add Researcher
                            </button>
                        </div> -->
                </div>
                <?php
                endif;
                ?>
            <!-- FiltersHere -->
            <div class="row mt-3">
                <div class="col d-flex flex-row justify-content-end align-items-center gap-3" id="filters">
                </div>
            </div>
            <div class="row mt-3 justify-content-between" id="filterButton">

                <!-- FILTER BUTTONS HERE -->
            </div>
            <div class="row">
                <div class="row mt-3 d-flex justify-content-end">
                    <?php if ($currentPosition === "Director" || $currentPosition === "Technical Assistant"): ?>
                        <div class="col">
                            <div class="btn-group btn-group-toggle" data-toggle="toggleButtons">
                                <label class="btn btn-secondary">
                                    <input type="radio" name="toggleOptions" id="employee_toggle" autocomplete="off"
                                        checked>
                                    Employees
                                </label>
                                <label class="btn btn-secondary">
                                    <input type="radio" name="toggleOptions" id="account_toggle" autocomplete="off">
                                    Accounts
                                </label>
                            </div>
                        </div>
                        <?php
                    endif;
                    ?>
                </div>


                <!-- TableHere -->
                <div class="row mt-2 tableOverview">
                    <div class="col" id="showEmployeeTable">
                        <!-- TABLES HERE -->
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Modals -->

    <div class="modals" id="add_account_modal" style="display: none;">
        <div class="modal_add_account">
            <div class="modal_title">
                <h2><?= ($currentPosition === "Focal Person") ? "Add Researcher" : "Add Account" ?></h2>
            </div>

            <form method="post" class="form_add_account" novalidate>
                <input type="hidden" id="existing_employee_id" name="existing_employee_id" value="">

                <input type="text" name="fname" placeholder="First Name" required>
                <input type="text" name="lname" placeholder="Last Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="pass" id="password"
                    placeholder="Password (at least 8 characters with uppercase, lowercase, and a number)" required
                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,64}" maxlength="64"
                    title="Password must be 8-64 characters, with uppercase, lowercase, and a number.">

                <?php if ($currentPosition !== "Focal Person"): ?>
                    <!-- UPDATED: Multiple Position Selection -->
                    <div class="form-group">
                        <label class="mb-2">
                            <i class="fas fa-user-tag"></i> Select Position(s)
                            <small class="text-muted">(Hold Ctrl/Cmd to select multiple)</small>
                        </label>
                        <select name="pos[]" id="position" multiple class="form-control" required
                            style="min-height: 120px;">
                            <?php if ($currentPosition === "Director" || $currentPosition === "Technical Assistant"): ?>
                                <option value="Technical Assistant">Technical Assistant</option>
                                <option value="Focal Person">Focal Person</option>
                                <option value="Panel">Panel</option>
                                <option value="RET Chair">RET Chair</option>
                                <option value="Researcher">Researcher</option>
                            <?php endif; ?>
                        </select>
                        <small class="form-text text-muted">
                            Selected positions will be combined (e.g., "Focal Person, Panel")
                        </small>
                    </div>

                    <select name="dept" id="department" required>
                        <option value="" disabled selected>Select Department</option>
                        <?php if ($currentPosition === "Director" || $currentPosition === "Technical Assistant"): ?>
                            <option value="CPADM">College of Public Administration and Disaster Management</option>
                            <option value="CMBT">College of Management and Business Technology</option>
                            <option value="CoArch">College of Architecture</option>
                            <option value="CoEd">College of Education</option>
                            <option value="Crim">Criminology</option>
                            <option value="COE">College of Engineering</option>
                            <option value="CICT">College of Infomations and Communications Technology</option>
                            <option value="IPE">Interprofessional Education</option>
                            <option value="LHS">Laboratory High School</option>
                            <option value="CIT">College of Industrial Technology</option>
                            <option value="CAS">College of Arts and Science</option>
                            <option value="IOLL">Institute of Linguistics and Literature</option>
                            <option value="CON">College Of Nursing</option>
                            <option value="GS">Graduate School</option>
                        <?php endif; ?>
                    </select>

                    <select name="campus" id="campus" required>
                        <option value="" disabled selected>Select Campus</option>
                        <?php if ($currentPosition === "Director" || $currentPosition === "Technical Assistant"): ?>
                            <option value="Sumacab">Sumacab</option>
                            <option value="GT">Gen. Tinio</option>
                            <option value="San Isidro">San Isidro</option>
                            <option value="Gabaldon">Gabaldon</option>
                            <option value="Atate">Atate</option>
                            <option value="Fort Magsaysay">Fort Magsaysay</option>
                        <?php endif; ?>
                    </select>
                <?php else: ?>
                    <input type="hidden" name="pos[]" value="Researcher">
                    <input type="hidden" name="dept" value="<?= htmlspecialchars($currentDepartment) ?>">
                    <input type="hidden" name="campus" value="<?= htmlspecialchars($currentCampus) ?>">
                <?php endif; ?>

                <div class="buttons">
                    <button type="submit" class="btn btn-outline-success">Add</button>
                    <button type="button" class="add_btn_close" id="close_add_account">Close</button>
                </div>
            </form>
        </div>
    </div>






    <!-- qwerty -->
    <!-- Edit Account Modal -->
    <div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg rounded-3 border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editAccountModalLabel">
                        <i class="fas fa-user-edit me-2"></i> Edit Account
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <form id="editAccountForm">
                    <div class="modal-body">
                        <input type="hidden" name="acc_id" id="acc_id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" id="acc_username" name="acc_username" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" id="acc_password" name="acc_password"
                                    placeholder="Leave blank to keep current">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" id="acc_fname" name="acc_fname" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="acc_lname" name="acc_lname" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="acc_email" name="acc_email" required>
                            </div>

                            <!-- UPDATED: Multiple Position Selection -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user-tag"></i> Position(s)
                                    <small class="text-muted">(Hold Ctrl/Cmd to select multiple)</small>
                                </label>
                                <select class="form-select" id="acc_position" name="acc_position[]" multiple required
                                    style="min-height: 100px;">
                                    <option value="Technical Assistant">Technical Assistant</option>
                                    <option value="Focal Person">Focal Person</option>
                                    <option value="Panel">Panel</option>
                                    <option value="RET Chair">RET Chair</option>
                                    <option value="Researcher">Researcher</option>
                                </select>
                                <small class="form-text text-muted">
                                    Selected positions will be combined (e.g., "Focal Person, Panel")
                                </small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department</label>
                                <select class="form-select" id="acc_department" name="acc_department" required>
                                    <option value="" disabled selected>Select Department</option>
                                    <option value="CPADM">CPADM</option>
                                    <option value="CMBT">CMBT - BA, HM</option>
                                    <option value="CoArch">CoArch</option>
                                    <option value="CoEd">CoEd</option>
                                    <option value="Crim">Crim</option>
                                    <option value="COE">COE</option>
                                    <option value="CICT">CICT</option>
                                    <option value="IPE">IPE</option>
                                    <option value="LHS">LHS</option>
                                    <option value="CIT">CIT</option>
                                    <option value="CAS">CAS</option>
                                    <option value="IOLL">IOLL</option>
                                    <option value="CON">CON</option>
                                    <option value="GS">GS</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Campus</label>
                                <select class="form-select" id="acc_campus" name="acc_campus" required>
                                    <option value="" disabled selected>Select Campus</option>
                                    <option value="Sumacab">Sumacab</option>
                                    <option value="GT">Gen. Tinio</option>
                                    <option value="San Isidro">San Isidro</option>
                                    <option value="Gabaldon">Gabaldon</option>
                                    <option value="Atate">Atate</option>
                                    <option value="Fort Magsaysay">Fort Magsaysay</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('../phpFunctions/alerts.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <scrip nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js">
        </script>




        <script>
            $(document).ready(function () {
                const position = <?= json_encode($currentPosition) ?>;
                const campus = <?= json_encode($currentCampus) ?>;
                const dept = <?= json_encode($currentDepartment) ?>;
                // Load filters, table, buttons first


                $('#filters').load("./reusableHTML/filters.php", function () {
                    resetFilterFunction(position);
                    restrictDeptAndCampus(position, dept, campus, "#filterDept", "#filterCampus");

                    $('#showEmployeeTable').load("./reusableHTML/employeeTable.php", function () {

                        $('#filterButton').load("./reusableHTML/filtersButton.php", function () {
                            // Now everything exists → safe to run
                            filterFunction("employee", "#searchBar", "#checkboxShowSummary", "#filterCampus", "#filterDept", "#filterSize", "#filterGender", position, "#employeeTable", "no", "filter", "#searchBtn");

                        });
                    });
                });
            });
        </script>


        <!-- CLAUDE LATEST -->

        <script>
            // Add this script to handle the Edit Account Modal
            $(document).ready(function () {
                // Handle Edit Account Modal opening
                $(document).on('click', '.edit-account-btn', function () {
                    const accountId = $(this).data('id');

                    // Fetch account details
                    $.ajax({
                        url: '../phpFunctions/getAccountDetails.php',
                        type: 'POST',
                        data: { id: accountId },
                        dataType: 'json',
                        success: function (data) {
                            if (data.error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.error
                                });
                                return;
                            }

                            // Fill form fields
                            $('#acc_id').val(data.id);
                            $('#acc_username').val(data.username);
                            $('#acc_email').val(data.email);
                            $('#acc_fname').val(data.fname);
                            $('#acc_lname').val(data.lname);
                            $('#acc_department').val(data.department);
                            $('#acc_campus').val(data.campus);
                            $('#acc_password').val(''); // Clear password field

                            // Handle multi-role selection
                            if (data.position) {
                                const positions = data.position.split(',').map(p => p.trim());
                                $('#acc_position').val(positions);
                            }

                            // Show modal
                            $('#editAccountModal').modal('show');
                        },
                        error: function (xhr, status, error) {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to load account details'
                            });
                        }
                    });
                });

                // Handle Edit Account Form Submission
                $('#editAccountForm').on('submit', function (e) {
                    e.preventDefault();

                    // Get selected positions and combine them
                    const selectedPositions = $('#acc_position').val();
                    const positionString = selectedPositions.join(', ');

                    // Create form data
                    const formData = new FormData(this);
                    formData.delete('acc_position[]'); // Remove array
                    formData.append('acc_position', positionString); // Add as string

                    // Show loading
                    Swal.fire({
                        title: 'Updating...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '../phpFunctions/updateAccount.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    timer: 2000
                                }).then(() => {
                                    $('#editAccountModal').modal('hide');
                                    // Reload accounts table
                                    if ($('#account_toggle').is(':checked')) {
                                        $('#showEmployeeTable').load('./reusableHTML/accountsTable.php');
                                    }
                                });
                            } else if (response.error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.error
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to update account'
                            });
                        }
                    });
                });

                $(document).ready(function () {

                    /* ========================================
                       MULTI-SELECT POSITION HANDLER
                    ======================================== */

                    // Function to display selected positions as badges
                    function updatePositionDisplay(selectElement) {
                        const selectedOptions = Array.from(selectElement.selectedOptions).map(opt => opt.value);
                        const container = $(selectElement).closest('.form-group, .mb-3');

                        // Remove existing indicator if present
                        container.find('.selected-positions-display').remove();

                        if (selectedOptions.length > 0) {
                            const displayHtml = `
                <div class="selected-positions-display mt-2">
                    <small class="text-muted d-block mb-1">Selected:</small>
                    <div class="d-flex flex-wrap gap-1">
                        ${selectedOptions.map(pos => `
                            <span class="position-indicator">
                                <i class="fas fa-check-circle me-1"></i>${pos}
                            </span>
                        `).join('')}
                    </div>
                </div>
            `;
                            container.append(displayHtml);
                        }
                    }

                    // Handle Add Account Modal position selection
                    $('#position').on('change', function () {
                        updatePositionDisplay(this);
                    });

                    // Handle Edit Account Modal position selection
                    $('#acc_position').on('change', function () {
                        updatePositionDisplay(this);
                    });

                    /* ========================================
                       VALIDATION FOR MULTI-SELECT
                    ======================================== */

                    // Validate that at least one position is selected
                    function validatePositionSelection(formId) {
                        const positionSelect = $(formId).find('select[name="pos[]"], select[name="acc_position[]"]');

                        if (positionSelect.length > 0) {
                            const selectedPositions = positionSelect.val();

                            if (!selectedPositions || selectedPositions.length === 0) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Position Required',
                                    text: 'Please select at least one position',
                                    confirmButtonColor: '#ffc107'
                                });
                                return false;
                            }
                        }

                        return true;
                    }

                    // Add validation to Add Account form
                    $('.form_add_account').on('submit', function (e) {
                        const currentPos = '<?= $currentPosition ?>';

                        // Skip validation for Focal Person (they only add Researchers)
                        if (currentPos !== 'Focal Person') {
                            if (!validatePositionSelection(this)) {
                                e.preventDefault();
                                return false;
                            }
                        }
                    });

                    // Add validation to Edit Account form
                    $('#editAccountForm').on('submit', function (e) {
                        if (!validatePositionSelection(this)) {
                            e.preventDefault();
                            return false;
                        }
                    });

                    /* ========================================
                       KEYBOARD SHORTCUTS FOR MULTI-SELECT
                    ======================================== */

                    // Add keyboard shortcuts hint
                    function addKeyboardHint(selectElement) {
                        const hint = `
            <div class="keyboard-hint mt-1">
                <small class="text-muted">
                    <i class="fas fa-keyboard me-1"></i>
                    <strong>Tip:</strong> Hold <kbd>Ctrl</kbd> (Windows) or <kbd>⌘ Cmd</kbd> (Mac) to select multiple
                </small>
            </div>
        `;

                        if ($(selectElement).next('.keyboard-hint').length === 0) {
                            $(selectElement).after(hint);
                        }
                    }

                    // Add hints to both modals
                    $('#position, #acc_position').each(function () {
                        addKeyboardHint(this);
                    });

                    /* ========================================
                       SELECT ALL / DESELECT ALL FUNCTIONALITY
                    ======================================== */

                    // Add Select All / Deselect All buttons
                    function addSelectAllButtons(selectElement) {
                        const container = $(selectElement).closest('.form-group, .mb-3');

                        if (container.find('.select-all-buttons').length === 0) {
                            const buttonsHtml = `
                <div class="select-all-buttons mb-2 d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary select-all-btn">
                        <i class="fas fa-check-double"></i> Select All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary deselect-all-btn">
                        <i class="fas fa-times"></i> Clear All
                    </button>
                </div>
            `;

                            $(selectElement).before(buttonsHtml);
                        }
                    }

                    // Add buttons to position selects
                    $('#position, #acc_position').each(function () {
                        addSelectAllButtons(this);
                    });

                    // Handle Select All button
                    $(document).on('click', '.select-all-btn', function () {
                        const select = $(this).closest('.form-group, .mb-3').find('select[multiple]');
                        select.find('option').prop('selected', true);
                        select.trigger('change');
                    });

                    // Handle Deselect All button
                    $(document).on('click', '.deselect-all-btn', function () {
                        const select = $(this).closest('.form-group, .mb-3').find('select[multiple]');
                        select.find('option').prop('selected', false);
                        select.trigger('change');
                    });

                    /* ========================================
                       DISPLAY CURRENT POSITION ON LOAD
                    ======================================== */

                    // When edit modal opens, show selected positions
                    $('#editAccountModal').on('shown.bs.modal', function () {
                        const posSelect = $('#acc_position')[0];
                        if (posSelect) {
                            updatePositionDisplay(posSelect);
                        }
                    });

                    // When add modal opens, clear display
                    $('#add_account').on('click', function () {
                        $('.selected-positions-display').remove();
                    });
                });

                // Add some custom styling for kbd tags
                $('<style>')
                    .text(`
                        kbd {
                            background-color: #f8f9fa;
                            border: 1px solid #dee2e6;
                            border-radius: 3px;
                            padding: 2px 6px;
                            font-size: 0.875em;
                            font-family: monospace;
                            box-shadow: 0 1px 0 rgba(0,0,0,0.1);
                        }
                        
                        .keyboard-hint {
                            margin-top: 5px;
                        }
                        
                        .select-all-buttons {
                            margin-top: 5px;
                        }
                        
                        .select-all-buttons .btn {
                            font-size: 0.8rem;
                            padding: 4px 10px;
                        }
                        
                        .gap-1 {
                            gap: 0.25rem !important;
                        }
                        
                        .gap-2 {
                            gap: 0.5rem !important;
                        }
                    `)
                    .appendTo('head');
            });
        </script>


        <script>
            console.log('script.js is loaded');
            document.addEventListener('DOMContentLoaded', () => {
                let pos = <?php echo json_encode($currentPosition); ?>

                console.log(pos);

                if (pos === "Focal Person") {
                    document.getElementById("position").addEventListener("mousedown", function (e) {
                        e.preventDefault();
                    })
                    document.getElementById("campus").addEventListener("mousedown", function (e) {
                        e.preventDefault();
                    })
                    document.getElementById("department").addEventListener("mousedown", function (e) {
                        e.preventDefault();
                    })
                }


                // Add Account Modal
                const addAccountButton = document.querySelector('#add_account');
                const addAccountModal = document.querySelector('#add_account_modal');
                const closeAddAccountButton = document.querySelector('#close_add_account');

                if (addAccountButton) {
                    addAccountButton.addEventListener('click', () => {
                        addAccountModal.style.display = 'flex';
                    });
                }

                if (closeAddAccountButton) {
                    closeAddAccountButton.addEventListener('click', () => {
                        addAccountModal.style.display = 'none';
                    });
                }

                // View All Modal
                const viewAllButton = document.querySelector('#view_all');
                const viewAllModal = document.querySelector('#view_all_modal');
                const closeViewAllButton = document.querySelector('#close_view_all');

                if (viewAllButton) {
                    viewAllButton.addEventListener('click', () => {
                        viewAllModal.style.display = 'flex';
                    });
                }

                if (closeViewAllButton) {
                    closeViewAllButton.addEventListener('click', () => {
                        viewAllModal.style.display = 'none';
                    });
                }
            });

            document.addEventListener('DOMContentLoaded', () => {
                const deactivateIcons = document.querySelectorAll('.deactivate-icon');

                deactivateIcons.forEach(icon => {
                    icon.addEventListener('click', () => {
                        const userId = icon.getAttribute('data-id');
                        const userRow = icon.closest('tr'); // Get the table row containing the icon

                        if (confirm('Are you sure you want to deactivate this user?')) {
                            fetch('../deactivate_user.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `id=${userId}`,
                            })
                                .then(response => response.text())
                                .then(data => {
                                    alert(data);

                                    // Remove the row from the table if the deactivation is successful
                                    if (data.includes('User deactivated successfully')) {
                                        userRow.remove();
                                    } else {
                                        alert('Failed to deactivate the user.');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('An error occurred while deactivating the user.');
                                });
                        }
                    });
                });
            });

            document.addEventListener('DOMContentLoaded', () => {
                const editIcons = document.querySelectorAll('.edit-icon');
                const editModal = document.querySelector('#edit_account_modal');
                const closeEditModalButton = document.querySelector('#close_edit_account');

                const editIdInput = document.querySelector('#edit_id');
                const editUsernameInput = document.querySelector('#edit_username');
                const editEmailInput = document.querySelector('#edit_email');
                const editPasswordInput = document.querySelector('#edit_password');
                const editPositionSelect = document.querySelector('#edit_position');
                const editDepartmentSelect = document.querySelector('#edit_department');

                // Open the modal and populate fields
                editIcons.forEach(icon => {
                    icon.addEventListener('click', () => {
                        const userId = icon.getAttribute('data-id');

                        // Fetch user details via AJAX
                        fetch(`../get_user_details.php?id=${userId}`)
                            .then(response => response.json())
                            .then(data => {
                                // Populate the modal fields
                                editIdInput.value = data.id;
                                editUsernameInput.value = data.username;
                                editEmailInput.value = data.email;
                                editPasswordInput.value = ''; // Leave password empty for security
                                editPositionSelect.value = data.position;
                                editDepartmentSelect.value = data.department;

                                // Show the modal
                                editModal.style.display = 'flex';
                            })
                            .catch(error => console.error('Error fetching user details:', error));
                    });
                });

                // Close the modal
                if (closeEditModalButton) {
                    closeEditModalButton.addEventListener('click', () => {
                        editModal.style.display = 'none';
                    });
                }
            });
        </script>

        <script>
            $(document).ready(function () {
                const position = <?= json_encode($currentPosition) ?>;
                const campus = <?= json_encode($currentCampus) ?>;
                const dept = <?= json_encode($currentDepartment) ?>;



                function loadEmployeeTable() {
                    $('#showEmployeeTable').load('./reusableHTML/employeeTable.php', function () {
                        filterFunction("employee", "#searchBar", "#checkboxShowSummary", "#filterCampus", "#filterDept", "#filterSize", "#filterGender", position, "#employeeTable", "no", "filter", "#searchBtn");

                    });
                }

                function loadAccountsTable() {
                    $('#showEmployeeTable').load('./reusableHTML/accountsTable.php', function () {

                    });
                }

                // Default: load employee table on page load
                loadEmployeeTable();

                $("input[name='toggleOptions']").change(function () {
                    if ($("#employee_toggle").is(":checked")) {
                        loadEmployeeTable();
                    } else if ($("#account_toggle").is(":checked")) {
                        loadAccountsTable();
                    }
                });
            });
        </script>

        <?php require('./reusableHTML/personalInfoModal.php'); ?>

        <script>
            $(document).ready(function () {
                /* -------------------------------
                   EMPLOYEE INFO MODAL (Personal Info Modal - for Add Employee button)
                -------------------------------- */
                const addEmployeeBtn = document.getElementById('addEmployeeBtn');
                const modal = document.getElementById('modal');

                if (addEmployeeBtn && modal) {
                    $(addEmployeeBtn).off('click').on('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();

                        // Close add account modal if open
                        $('#add_account_modal').css('display', 'none');

                        // Open personal info modal
                        $(modal).addClass('open');
                        document.body.style.overflow = 'hidden';
                    });
                }

                // Close personal info modal
                if (modal) {
                    const closeBtns = modal.querySelectorAll('.close-btn, #cancelInfo');
                    closeBtns.forEach(function (btn) {
                        btn.addEventListener('click', function (e) {
                            e.preventDefault();
                            modal.classList.remove('open');
                            document.body.style.overflow = '';
                        });
                    });

                    modal.addEventListener('click', function (e) {
                        if (e.target === modal) {
                            modal.classList.remove('open');
                            document.body.style.overflow = '';
                        }
                    });
                }

                // CLAUDE LATEST 2

                /* -------------------------------
                 ADD ACCOUNT MODAL (Assign Button with Ctrl Bypass)
                -------------------------------- */
                const addAccountModal = document.getElementById('add_account_modal');

                // Handle Assign Button Click
                $(document).on('click', '.assignBtn', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const row = $(this).closest('tr');
                    const ctrlPressed = e.ctrlKey || e.metaKey; // Ctrl on Windows/Linux, Cmd on Mac

                    // Get employee data from the row
                    const employeeId = $(this).data('id');
                    const fullName = row.find('.empName').text().trim();
                    const email = row.find('.empEmail').text().trim();

                    // Find department and campus
                    let department = '';
                    let campus = '';

                    const campusCell = row.find('td').eq(2);
                    if (campusCell.length && !campusCell.hasClass('empEmail')) {
                        campus = campusCell.text().trim();
                    }

                    const deptCell = row.find('td').eq(3);
                    if (deptCell.length) {
                        department = deptCell.text().trim();
                    }

                    // Validate email exists
                    if (!email || email === '') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Email Found',
                            text: 'This employee does not have an email address!',
                            confirmButtonColor: '#ffc107'
                        });
                        return;
                    }

                    // Split name into first and last
                    const nameParts = fullName.split(' ');
                    const fname = nameParts[0] || '';
                    const lname = nameParts.slice(1).join(' ') || '';

                    // Check if account already exists for this email
                    $.ajax({
                        url: '../phpFunctions/checkAccountExists.php',
                        type: 'POST',
                        data: { email: email },
                        dataType: 'json',
                        success: function (resp) {
                            if (resp.exists) {
                                // UPDATED: Check if Ctrl was pressed
                                if (ctrlPressed) {
                                    // Ctrl+Click: Bypass warning and open modal for multi-role
                                    openAssignModal(employeeId, fname, lname, email, department, campus, resp.position, true);
                                } else {
                                    // Normal click: Show warning with instructions
                                    if (resp.position === "Director") {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Already Has Position',
                                            html: `
                                    <p>This employee is the <strong>${resp.position}</strong>!</p>
                                    <hr>
                                    <p class="text-muted" style="font-size: 0.9rem;">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Tip:</strong> Hold <kbd>Ctrl</kbd> (or <kbd>⌘ Cmd</kbd> on Mac) 
                                        and click <strong>Assign</strong> to add additional roles.
                                    </p>
                                `,
                                            confirmButtonColor: '#ffc107'
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'info',
                                            title: 'Already Has Position',
                                            html: `
                                    <p>This employee's current position is <strong>${resp.position}</strong></p>
                                    <hr>
                                    <p class="text-muted" style="font-size: 0.9rem;">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Tip:</strong> Hold <kbd>Ctrl</kbd> (or <kbd>⌘ Cmd</kbd> on Mac) 
                                        and click <strong>Assign</strong> to add additional roles.
                                    </p>
                                `,
                                            confirmButtonColor: '#3085d6',
                                            confirmButtonText: 'Got it'
                                        });
                                    }
                                }
                            } else {
                                // Account doesn't exist - open modal normally
                                openAssignModal(employeeId, fname, lname, email, department, campus, null, false);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to check account status. Please try again.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                });

                // Function to open the assign modal
                function openAssignModal(employeeId, fname, lname, email, department, campus, existingPosition, isMultiRole) {
                    // Close any other open modals first
                    $('#modal').removeClass('open');
                    $('.modal').modal('hide');

                    $('#add_account_modal').css('display', 'flex');
                    document.body.style.overflow = 'hidden';

                    // Clear form first
                    $('form.form_add_account')[0].reset();

                    // Fill form with employee data
                    $('input[name="fname"]').val(fname);
                    $('input[name="lname"]').val(lname);
                    $('input[name="email"]').val(email).prop('readonly', true);

                    // Set password same as email
                    $('input[name="pass"]').val(email);

                    // Set department and campus
                    if ($('select[name="dept"]').length > 0) {
                        $('select[name="dept"]').val(department);
                    }
                    if ($('select[name="campus"]').length > 0) {
                        $('select[name="campus"]').val(campus);
                    }

                    // Store employee_id in hidden field
                    if ($('#existing_employee_id').length === 0) {
                        $('form.form_add_account').prepend(
                            '<input type="hidden" id="existing_employee_id" name="existing_employee_id" value="">'
                        );
                    }
                    $('#existing_employee_id').val(employeeId);

                    // UPDATED: Show different messages based on mode
                    if (isMultiRole && existingPosition) {
                        // Multi-role mode: Pre-select existing positions
                        if ($('#position').length > 0 && $('#position').prop('multiple')) {
                            const positions = existingPosition.split(',').map(p => p.trim());
                            $('#position').val(positions);

                            // Trigger change event to show selected positions
                            $('#position').trigger('change');
                        }

                        // Show multi-role note
                        if ($('#assign-note').length === 0) {
                            $('form.form_add_account').prepend(
                                `<div id="assign-note" class="alert alert-success mb-3" style="font-size: 0.9rem;">
                        <i class="fas fa-user-plus"></i> <strong>Adding Additional Role(s)</strong><br>
                        <small>Current position(s): <strong>${existingPosition}</strong></small><br>
                        <small class="text-muted">Select additional positions or modify existing ones.</small>
                    </div>`
                            );
                        }
                    } else {
                        // Normal mode: Creating new account
                        if ($('#assign-note').length === 0) {
                            $('form.form_add_account').prepend(
                                `<div id="assign-note" class="alert alert-info mb-3" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i> Creating account for existing employee<br>
                        <small>Default password is set to the email address</small>
                    </div>`
                            );
                        }
                    }
                }

                // Close add-account modal and cleanup
                $('#close_add_account').on('click', function () {
                    // Remove readonly attribute
                    $('input[name="email"]').prop('readonly', false);

                    // Remove temporary elements
                    $('#existing_employee_id').remove();
                    $('#assign-note').remove();

                    // Reset form
                    $('form.form_add_account')[0].reset();

                    // Hide modal
                    $('#add_account_modal').css('display', 'none');
                    document.body.style.overflow = '';
                });

                // Close modal when clicking overlay
                if (addAccountModal) {
                    addAccountModal.addEventListener('click', function (e) {
                        if (e.target === addAccountModal) {
                            $('#close_add_account').click();
                        }
                    });
                }

                /* -------------------------------
                   VISUAL INDICATOR FOR CTRL+CLICK
                -------------------------------- */

                // Add hover tooltip to Assign buttons
                $(document).on('mouseenter', '.assignBtn', function () {
                    if (!$(this).attr('title')) {
                        $(this).attr('title', 'Click to assign position | Ctrl+Click to add multiple roles');
                    }
                });

                // Add visual feedback when Ctrl is pressed
                let ctrlHintShown = false;

                $(document).on('keydown', function (e) {
                    if ((e.ctrlKey || e.metaKey) && !ctrlHintShown) {
                        // Show temporary hint when Ctrl is first pressed
                        $('.assignBtn').addClass('ctrl-active');
                        ctrlHintShown = true;
                    }
                });

                $(document).on('keyup', function (e) {
                    if (!e.ctrlKey && !e.metaKey) {
                        $('.assignBtn').removeClass('ctrl-active');
                        ctrlHintShown = false;
                    }
                });
            });

            // Add CSS for visual feedback
            $('<style>')
                .text(`
        .assignBtn.ctrl-active {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: white !important;
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
            transform: scale(1.05);
            transition: all 0.2s ease;
        }
        
        .assignBtn.ctrl-active::after {
            content: " (Multi-Role Mode)";
            font-size: 0.8em;
            font-weight: normal;
        }
        
        kbd {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 2px 6px;
            font-size: 0.875em;
            font-family: monospace;
            box-shadow: 0 1px 0 rgba(0,0,0,0.1);
            display: inline-block;
        }
        
        .alert hr {
            margin: 10px 0;
            opacity: 0.3;
        }
        
        .assignBtn {
            transition: all 0.2s ease;
        }
        
        .assignBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
    `)
                .appendTo('head');

        </script>

        <!-- CLAUDE CODE -->


        <script>
            $(document).ready(function () {

                /* ========================================
                   HANDLE FORM SUBMISSION (ADD/EDIT EMPLOYEE)
                ======================================== */
                $('#employeeForm').on('submit', function (e) {
                    e.preventDefault();

                    // Validate required fields before submission
                    let isValid = true;
                    $(this).find('input[required], select[required], textarea[required]').each(function () {
                        if (!$(this).val() || $(this).val().trim() === '') {
                            $(this).addClass('is-invalid');
                            isValid = false;
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });

                    if (!isValid) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Missing Information',
                            text: 'Please fill in all required fields marked with *',
                            confirmButtonColor: '#ffc107'
                        });
                        return;
                    }

                    // Get form data
                    const formData = new FormData(this);
                    const empId = $('#emp_id').val();
                    const isEdit = empId && empId !== '';

                    // Show loading state
                    const submitBtn = $('#saveInfo');
                    const originalBtnText = submitBtn.html();
                    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

                    // Send AJAX request
                    $.ajax({
                        url: '../phpFunctions/insertEmployee.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                // Show success message
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    confirmButtonColor: '#28a745',
                                    timer: 2000
                                }).then(() => {
                                    // Close modal
                                    $('#modal').removeClass('open');
                                    document.body.style.overflow = '';

                                    // Reset form
                                    resetEmployeeForm();

                                    // Reload employee table
                                    reloadEmployeeTable();
                                });
                            } else {
                                // Show error message
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Failed to save employee information',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX Error:', error);
                            console.error('Response:', xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while saving employee information. Please try again.',
                                confirmButtonColor: '#dc3545'
                            });
                        },
                        complete: function () {
                            // Restore button state
                            submitBtn.prop('disabled', false).html(originalBtnText);
                        }
                    });
                });

                /* ========================================
                   EDIT EMPLOYEE BUTTON HANDLER
                   (Works with the editEmployeeBtn from filterFunction.php)
                ======================================== */
                $(document).on('click', '.editEmployeeBtn', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const employeeId = $(this).data('id');

                    if (!employeeId) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Employee ID not found',
                            confirmButtonColor: '#dc3545'
                        });
                        return;
                    }

                    // Load employee data for editing
                    loadEmployeeForEdit(employeeId);
                });

                /* ========================================
                   LOAD EMPLOYEE DATA FOR EDITING
                ======================================== */
                window.loadEmployeeForEdit = function (employeeId) {
                    // Show loading state
                    Swal.fire({
                        title: 'Loading...',
                        text: 'Fetching employee information',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '../phpFunctions/getEmployeeDetails.php',
                        type: 'POST',
                        data: { id: employeeId },
                        dataType: 'json',
                        success: function (resp) {
                            console.log("Employee Data:", resp);

                            if (!resp || resp.error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: resp ? resp.error : 'Failed to load employee information',
                                    confirmButtonColor: '#dc3545'
                                });
                                return;
                            }

                            // Close loading
                            Swal.close();

                            // Update modal title
                            $('#modalTitle').text('Edit Employee');

                            // Fill form fields
                            $('#emp_id').val(resp.id || employeeId);
                            $('#fname').val(resp.fname || '');
                            $('#m_initial').val(resp.m_initial || '');
                            $('#lname').val(resp.lname || '');
                            $('#email').val(resp.email || '');
                            $('#contact_no').val(resp.contact_no || '');
                            $('#birthday').val(resp.birthdate || resp.birthday || '');
                            $('#sex').val(resp.sex || '');
                            $('#gender').val(resp.gender || '');
                            $('#address').val(resp.address || '');
                            $('#marital_status').val(resp.marital_status || '');
                            $('#size').val(resp.size || '');
                            $('#priority_status').val(resp.priority_status || 'None');
                            $('#income').val(resp.monthly_income || resp.income || '');

                            // Handle LGBTQIA+ other gender
                            if (resp.gender && !['Male', 'Female', 'LGBTQIA+'].includes(resp.gender)) {
                                $('#gender').val('LGBTQIA+');
                                $('#otherGender').val(resp.gender).show();
                            } else if (resp.gender === 'LGBTQIA+') {
                                $('#otherGender').show();
                            } else {
                                $('#otherGender').hide();
                            }

                            // Handle children fields
                            if (resp.has_children === 'Yes') {
                                $('#hasChildrenYes').prop('checked', true);
                                $('#childrenNumCol, #childConcernCol').show();
                                $('#children_num').val(resp.num_of_children || resp.children_num || 0);
                                $('#concern').val(resp.concern || '');
                            } else {
                                $('#hasChildrenNo').prop('checked', true);
                                $('#childrenNumCol, #childConcernCol').hide();
                            }

                            // Open modal
                            $('#modal').addClass('open');
                            document.body.style.overflow = 'hidden';

                        },
                        error: function (xhr, status, error) {
                            console.error('Error loading employee:', error);
                            console.error('Response:', xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to load employee information. Please try again.',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                };

                /* ========================================
                   FORM VALIDATION
                ======================================== */

                // Required field validation on blur
                $('#employeeForm input[required], #employeeForm select[required]').on('blur', function () {
                    if ($(this).val().trim() === '') {
                        $(this).addClass('is-invalid');
                        if ($(this).next('.invalid-feedback').length === 0) {
                            $(this).after('<div class="invalid-feedback">This field is required</div>');
                        }
                    } else {
                        $(this).removeClass('is-invalid');
                        $(this).next('.invalid-feedback').remove();
                    }
                });

                // Email validation
                $('#email').on('blur', function () {
                    const email = $(this).val().trim();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if (email && !emailRegex.test(email)) {
                        $(this).addClass('is-invalid');
                        $(this).next('.invalid-feedback').remove();
                        $(this).after('<div class="invalid-feedback">Please enter a valid email address</div>');
                    } else if (!email) {
                        $(this).addClass('is-invalid');
                        $(this).next('.invalid-feedback').remove();
                        $(this).after('<div class="invalid-feedback">Email is required</div>');
                    } else {
                        $(this).removeClass('is-invalid');
                        $(this).next('.invalid-feedback').remove();
                    }
                });

                // Contact number validation (Philippine format - 11 digits)
                $('#contact_no').on('input', function () {
                    let value = $(this).val().replace(/\D/g, ''); // Remove non-digits

                    // Limit to 11 digits
                    if (value.length > 11) {
                        value = value.substring(0, 11);
                    }

                    $(this).val(value);
                });

                $('#contact_no').on('blur', function () {
                    const value = $(this).val();

                    if (value.length > 0 && value.length !== 11) {
                        $(this).addClass('is-invalid');
                        $(this).next('.invalid-feedback').remove();
                        $(this).after('<div class="invalid-feedback">Contact number must be exactly 11 digits</div>');
                    } else if (value.length === 0) {
                        $(this).addClass('is-invalid');
                        $(this).next('.invalid-feedback').remove();
                        $(this).after('<div class="invalid-feedback">Contact number is required</div>');
                    } else {
                        $(this).removeClass('is-invalid');
                        $(this).next('.invalid-feedback').remove();
                    }
                });

                // Birthdate validation (must be 18+ years old)
                $('#birthday').on('change', function () {
                    const birthdate = new Date($(this).val());
                    const today = new Date();
                    let age = today.getFullYear() - birthdate.getFullYear();
                    const monthDiff = today.getMonth() - birthdate.getMonth();

                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                        age--;
                    }

                    if (age < 18) {
                        $(this).addClass('is-invalid');
                        $(this).next('.invalid-feedback').remove();
                        $(this).after('<div class="invalid-feedback">Employee must be at least 18 years old</div>');
                    } else {
                        $(this).removeClass('is-invalid');
                        $(this).next('.invalid-feedback').remove();
                    }
                });

                // Middle initial validation (max 2 characters with period)
                $('#m_initial').on('input', function () {
                    let value = $(this).val().toUpperCase();

                    // Remove any characters that aren't letters or periods
                    value = value.replace(/[^A-Z.]/g, '');

                    // Limit to 2 characters
                    if (value.length > 2) {
                        value = value.substring(0, 2);
                    }

                    $(this).val(value);
                });

                // Children number validation
                $('#children_num').on('input', function () {
                    let value = parseInt($(this).val());

                    if (value < 0) {
                        $(this).val(0);
                    } else if (value > 20) {
                        $(this).val(20);
                    }
                });

                /* ========================================
                   RESET FORM FUNCTION
                ======================================== */
                function resetEmployeeForm() {
                    $('#employeeForm')[0].reset();
                    $('#emp_id').val('');
                    $('#otherGender').hide();
                    $('#childrenNumCol, #childConcernCol').hide();
                    $('#hasChildrenNo').prop('checked', true);

                    // Remove all validation classes
                    $('#employeeForm .is-invalid').removeClass('is-invalid');
                    $('#employeeForm .invalid-feedback').remove();

                    // Update modal title back to "Add Employee"
                    $('#modalTitle').text('Add Employee');
                }

                /* ========================================
                   RELOAD EMPLOYEE TABLE FUNCTION
                ======================================== */
                function reloadEmployeeTable() {
                    const position = '<?= $currentPosition ?>';
                    const campus = '<?= $currentCampus ?>';
                    const dept = '<?= $currentDepartment ?>';

                    $('#showEmployeeTable').load('./reusableHTML/employeeTable.php', function () {
                        filterFunction("employee", "#searchBar", "#checkboxShowSummary",
                            "#filterCampus", "#filterDept", "#filterSize", "#filterGender",
                            position, "#employeeTable", "no", "filter", "#searchBtn");
                    });
                }

                /* ========================================
                   RESET FORM WHEN MODAL CLOSES
                ======================================== */
                $('#modal .close-btn, #cancelInfo').on('click', function () {
                    resetEmployeeForm();
                });

                // Close modal when clicking overlay
                $('#modal').on('click', function (e) {
                    if (e.target === this) {
                        resetEmployeeForm();
                        $(this).removeClass('open');
                        document.body.style.overflow = '';
                    }
                });

            });
        </script>

</body>

</html>