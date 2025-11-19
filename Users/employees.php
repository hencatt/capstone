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
        $position = $_POST['pos'] ?? '';

        // Use null coalescing to handle undefined dept/campus
        // If not set, use current user's department/campus
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
            alertError("Error", "Position is required");
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

            // STEP 3: Now insert into accounts_tbl with the employee_id
            $insertAccount = $con->prepare("INSERT INTO accounts_tbl (id, fname, lname, email, pass, position, department, campus, date_created, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)");
            $insertAccount->bind_param("isssssss", $employee_id, $fname, $lname, $email, $password, $position, $department, $campus);

            if (!$insertAccount->execute()) {
                throw new Exception("Failed to create account: " . $insertAccount->error);
            }
            $insertAccount->close();

            // Commit transaction
            $con->commit();

            alertSuccess("Done", "Account Created Successfully");

            // Send credentials email to the new user
            sendUserCredentials($email, $plainPassword, $fname, $lname);

            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();

        } catch (Exception $e) {
            // Rollback on error
            $con->rollback();
            alertError("Error", $e->getMessage());
            error_log("Add Account Error: " . $e->getMessage());
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
                <!-- Hidden field for existing employee ID (used when assigning account) -->
                <input type="hidden" id="existing_employee_id" name="existing_employee_id" value="">

                <input type="text" name="fname" placeholder="First Name" required>
                <input type="text" name="lname" placeholder="Last Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="pass" id="password"
                    placeholder="Password (at least 8 characters with uppercase, lowercase, and a number)" required
                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,64}" maxlength="64"
                    title="Password must be 8-64 characters, with uppercase, lowercase, and a number.">

                <?php if ($currentPosition !== "Focal Person"): ?>
                    <!-- Position Select (visible for Director/TA) -->
                    <select name="pos" id="position" required>
                        <option value="" disabled selected>Select Position</option>
                        <?php if ($currentPosition === "Director" || $currentPosition === "Technical Assistant"): ?>
                            <option value="Technical Assistant">Technical Assistant</option>
                            <option value="Focal Person">Focal Person</option>
                            <option value="Panel">Panel</option>
                            <option value="RET Chair">RET Chair</option>
                        <?php endif; ?>
                    </select>

                    <!-- Department Select (visible for Director/TA) -->
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

                    <!-- Campus Select (visible for Director/TA) -->
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
                    <!-- Hidden fields for Focal Person -->
                    <input type="hidden" name="pos" value="Researcher">
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

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Position</label>
                                <select class="form-select" id="acc_position" name="acc_position" required>
                                    <option value="" disabled selected>Select Position</option>
                                    <!-- <option value="Director">Director</option> -->
                                    <option value="Technical Assistant">Technical Assistant</option>
                                    <option value="Focal Person">Focal Person</option>
                                    <option value="Panel">Panel</option>
                                    <option value="RET Chair">RET Chair</option>
                                </select>
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

                            <div class="col-md-12 mb-3">
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

                /* -------------------------------
                   ADD ACCOUNT MODAL (Assign Button)
                -------------------------------- */
                const addAccountModal = document.getElementById('add_account_modal');

                // Handle Assign Button Click
                $(document).on('click', '.assignBtn', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const row = $(this).closest('tr');

                    // Get employee data from the row
                    const employeeId = $(this).data('id');
                    const fullName = row.find('.empName').text().trim();
                    const email = row.find('.empEmail').text().trim();

                    // Find department and campus - they're in different positions depending on filters
                    let department = '';
                    let campus = '';

                    // Try to get campus (usually 2nd column after name)
                    const campusCell = row.find('td').eq(2);
                    if (campusCell.length && !campusCell.hasClass('empEmail')) {
                        campus = campusCell.text().trim();
                    }

                    // Try to get department (usually 3rd column after name)
                    const deptCell = row.find('td').eq(3);
                    if (deptCell.length) {
                        department = deptCell.text().trim();
                    }

                    // Validate email exists
                    if (!email || email === '') {
                        alert('⚠️ No email found for this employee!');
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
                                alert('⚠️ This employee already has an account!\nPosition: ' + resp.position);
                                return;
                            }

                            // Account doesn't exist - open modal and fill fields
                            // CLOSE any other open modals first
                            $('#modal').removeClass('open'); // Close personal info modal if open
                            $('.modal').modal('hide'); // Close any Bootstrap modals

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

                            // Set department and campus (if selects exist)
                            if ($('select[name="dept"]').length > 0) {
                                $('select[name="dept"]').val(department);
                            }
                            if ($('select[name="campus"]').length > 0) {
                                $('select[name="campus"]').val(campus);
                            }

                            // Store employee_id in hidden field for reference
                            if ($('#existing_employee_id').length === 0) {
                                $('form.form_add_account').prepend(
                                    '<input type="hidden" id="existing_employee_id" name="existing_employee_id" value="">'
                                );
                            }
                            $('#existing_employee_id').val(employeeId);

                            // Add informational note
                            if ($('#assign-note').length === 0) {
                                $('form.form_add_account').prepend(
                                    '<div id="assign-note" class="alert alert-info mb-3" style="font-size: 0.9rem;">' +
                                    '<i class="fas fa-info-circle"></i> Creating account for existing employee<br>' +
                                    '<small>Default password is set to the email address</small>' +
                                    '</div>'
                                );
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX Error:', error);
                            alert('❌ Failed to check account status. Please try again.');
                        }
                    });
                });

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
                            // Trigger close button click to cleanup
                            $('#close_add_account').click();
                        }
                    });
                }
            });
        </script>

</body>




<!-- Try lang -->


</html>