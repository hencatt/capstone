<?php
require_once 'includes.php';

session_start();

// GET CURRENT USER INFO
$user = getUser();
$currentFname = $user['fname'];
$currentLname = $user['lname'];
$currentEmail = $user['email'];
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];
$currentUserId = $user['id'];

// UPDATE ACCOUNT BUTTON FUNCTION
if (isset($_POST["saveBtn"])) {
    $newEmail = $_POST["inputEmail"];
    $oldPassword = $_POST["inputOldPassword"];
    $newPassword = $_POST["inputNewPassword"];
    $confirmPassword = $_POST["inputConfirmPassword"];

    $currentId = $_SESSION["user_id"];
    $con = newCon();

    $fields = [];
    $types = "";
    $params = [];

    if (!empty($newEmail)) {
        $fields[] = "email = ?";
        $types .= "s";
        $params[] = $newEmail;
    }


    // Password Update Logic
    if (!empty($oldPassword) && !empty($newPassword) && !empty($confirmPassword)) {
        if ($newPassword === $confirmPassword) {
            $stmt = $con->prepare("SELECT pass FROM accounts_tbl WHERE id = ?");
            $stmt->bind_param("i", $currentId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $originalPassword = $row["pass"];

                if (password_verify($oldPassword, $originalPassword)) {
                    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $fields[] = "pass = ?";
                    $types .= "s";
                    $params[] = $newHashedPassword;
                } else {
                    alertError("Error", "Incorrect password");
                }
            }
            $stmt->close();
        } else {
            alertError("Confirm Password", "Passwords do not match");
        }
    }

    if (!empty($fields)) {
        $sql = "UPDATE accounts_tbl SET " . implode(", ", $fields) . " WHERE id = ?";
        $types .= "i";
        $params[] = $currentId;

        $stmt = $con->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            insertLog($currentUser, "Profile Updated", date('Y-m-d H:i:s'));
            alertSuccess("Updated", "Account updated successfully");
        } else {
            alertError("Error", "Update Failed");
        }
        $stmt->close();
    } else {
        alertError("Failed", "No data to update");
    }
    $con->close();
}

// UPDATE PERSONAL INFO (NEW FUNCTIONALITY)
if (isset($_POST["saveInfo"])) {
    $currentId = $_SESSION["user_id"];
    $con = newCon();

    try {
        $con->begin_transaction();

        // Get data from form
        $fname = trim($_POST['inputFname'] ?? '');
        $mname = trim($_POST['inputMname'] ?? '');
        $lname = trim($_POST['inputLname'] ?? '');
        $email = trim($_POST['inputEmail'] ?? '');
        $contact_no = trim($_POST['inputContact'] ?? '');
        $department = $_POST['inputDepartment'] ?? '';
        $campus = $_POST['inputCampus'] ?? '';
        $birthday = $_POST['inputBirthdate'] ?? '';
        $priority_status = $_POST['inputPriority'] ?? 'None';
        $marital_status = $_POST['inputMaritalStatus'] ?? '';
        $sex = $_POST['inputSex'] ?? '';
        $gender = $_POST['inputGender'] ?? '';
        $size = $_POST['inputSize'] ?? '';
        $income = $_POST['inputIncome'] ?? '';
        $children_num = isset($_POST['inputChildrenNum']) ? intval($_POST['inputChildrenNum']) : 0;
        $concern = trim($_POST['inputConcern'] ?? '') ?: 'N/A';

        // Handle complete address field
        $address = trim($_POST['inputAddress'] ?? '');

        // Handle LGBTQIA+ gender
        if ($gender === 'LGBTQIA+' && !empty($_POST['otherGender'])) {
            $gender = $_POST['otherGender'];
        }

        // Update employee_tbl
        $stmt_emp = $con->prepare("
            UPDATE employee_tbl 
            SET email = ?, contact_no = ?, department = ?, campus = ?
            WHERE id = ?
        ");
        $stmt_emp->bind_param("ssssi", $email, $contact_no, $department, $campus, $currentId);
        
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
            $children_num, $concern, $currentId
        );

        if (!$stmt_info->execute()) {
            throw new Exception("Failed to update employee_info: " . $stmt_info->error);
        }
        $stmt_info->close();

        // Also update accounts_tbl fname/lname if they changed
        $stmt_acc = $con->prepare("UPDATE accounts_tbl SET fname = ?, lname = ?, email = ? WHERE id = ?");
        $stmt_acc->bind_param("sssi", $fname, $lname, $email, $currentId);
        $stmt_acc->execute();
        $stmt_acc->close();

        $con->commit();
        insertLog($currentUser, "Personal Info Updated", date('Y-m-d H:i:s'));
        alertSuccess("Updated", "Personal information updated successfully!");

    } catch (Exception $e) {
        $con->rollback();
        alertError("Error", $e->getMessage());
        error_log("Personal Info Update Error: " . $e->getMessage());
    } finally {
        $con->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<?php include '../phpFunctions/email.php'; ?>

<head>
    <?= headerLinks("Modify Account") ?>
</head>

<body>
    <!-- Left Sidebar -->
    <div class="row everything">
        <div class="col sidebar">
            <?php echo sidebar("account", $currentPosition) ?>
        </div>
        
        <!-- MAIN CONTENTS -->
        <div class="col-10 mt-lg-3 mainContent">
            <div id="contents">
                <div class="row">
                    <div class="col">
                        <h1>Account</h1>
                    </div>
                </div>
                
                <div class="row mt-lg-3" style="background-color: white; padding: 30px; border-radius: 10px">
                    <div class="col">
                        <!-- ACCOUNT INFO FORM -->
                        <form method="POST" class="form-inline">
                            <div class="form-group row">
                                <div class="col">
                                    <h5>Account Information</h5>
                                </div>
                                <div class="col d-flex justify-content-end gap-3">
                                    <button type="button" name="personalInfoButton" id="personalInfoButton"
                                        class="btn btn-outline-primary">Edit Personal Info</button>
                                    <button type="button" name="editBtn" id="editBtn" class="btn btn-outline-secondary">Edit</button>
                                </div>

                                <div class="row mt-2">
                                    <div class="row-lg-4 form-group d-flex flex-row align-items-center gy-2">
                                        <label for="inputEmail" class="col-form-label col-sm-3">Email:</label>
                                        <input type="text" name="inputEmail" id="inputEmail" disabled
                                            placeholder="<?= $currentEmail ?>" class="form-control">
                                    </div>
                                </div>

                                <div class="row mt-5">
                                    <h5>Change Password</h5>
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="row-lg-4 form-group d-flex flex-row align-items-center gy-3">
                                        <label for="inputOldPassword" class="col-sm-3 col-form-control">Old Password:</label>
                                        <input type="password" name="inputOldPassword" id="inputOldPassword"
                                            placeholder="Enter Old Password" class="form-control" disabled>
                                    </div>
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="row-lg-4 form-group d-flex flex-row align-items-center gy-2">
                                        <label for="inputNewPassword" class="col-form-label col-sm-3">New Password:</label>
                                        <input type="password" name="inputNewPassword" id="inputNewPassword"
                                            placeholder="Enter New Password" class="form-control" disabled>
                                    </div>
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="row-lg-4 form-group d-flex flex-row align-items-center gy-2">
                                        <label for="inputConfirmPassword" class="col-sm-3 col-form-label">Confirm Password</label>
                                        <input type="password" name="inputConfirmPassword" id="inputConfirmPassword"
                                            placeholder="Confirm Password" class="form-control" disabled>
                                    </div>
                                </div>
                            </div>

                            <!-- SAVE BUTTON ROW -->
                            <div class="row mt-3">
                                <div class="row-8 d-flex align-items-center justify-content-end">
                                    <button type="submit" name="saveBtn" id="saveBtn" class="btn btn-outline-success"
                                        disabled style="display: none;">Save Changes 
                                        <span class="material-symbols-outlined">check</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- PROFILE SIDEBAR -->
                    <div class="col-lg-4" style="border: black solid 1px; padding: 10px; border-radius: 10px;">
                        <div class="row">
                            <div class="col text-center">
                                <img src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fstatic.vecteezy.com%2Fsystem%2Fresources%2Fpreviews%2F005%2F544%2F718%2Foriginal%2Fprofile-icon-design-free-vector.jpg&f=1&nofb=1&ipt=a3a03e1e1c2a147e5b78c95b25acf2b7a4edf938f68908367342f6caf5625631"
                                    alt="Profile Photo" style="width:150px; height:150px; border-radius:50%; border: solid black 1px;">
                            </div>
                        </div>
                        
                        <div class="row mt-5">
                            <div class="col">
                                <?php
                                $conn = newCon();
                                $sql = "SELECT ei.*, et.* FROM employee_info AS ei 
                                        INNER JOIN employee_tbl AS et ON et.id = ei.employee_id 
                                        WHERE ei.employee_id = $currentUserId";
                                $stmt = $conn->query($sql);
                                $row = $stmt->fetch_assoc();
                                ?>

                                <div class="row">
                                    <div class="col">
                                        <!-- NAME -->
                                        <div class="row">
                                            <div class="col-6 text-end">
                                                <label for="">Name:</label>
                                            </div>
                                            <div class="col">
                                                <?= $row["fname"] . " " . $row["lname"]; ?>
                                            </div>
                                        </div>

                                        <!-- CAMPUS -->
                                        <div class="row mt-2">
                                            <div class="col-6 text-end">
                                                <label for="">Campus:</label>
                                            </div>
                                            <div class="col">
                                                <?= $row["campus"]; ?>
                                            </div>
                                        </div>

                                        <!-- DEPT -->
                                        <div class="row mt-2">
                                            <div class="col-6 text-end">
                                                <label for="">Department:</label>
                                            </div>
                                            <div class="col">
                                                <?= $row["department"]; ?>
                                            </div>
                                        </div>

                                        <!-- BIRTHDAY -->
                                        <div class="row mt-2">
                                            <div class="col-6 text-end">
                                                <label for="">Birthday:</label>
                                            </div>  
                                            <div class="col">
                                                <?= $row["birthday"]; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PERSONAL INFO MODAL -->
    <?php require('./reusableHTML/personalInfoModal.php') ?>
    <?php include('../phpFunctions/alerts.php'); ?>

    <script>
        $(document).ready(function () {
            const personalBtn = document.getElementById("personalInfoButton");
            const modal = document.getElementById("modal");
            const closeBtn = modal.querySelector(".close-btn");
            const cancelBtn = document.getElementById("cancelInfo");

            // Open modal and load current user data
            personalBtn.addEventListener("click", () => {
                // Fetch current employee data
                $.post('../phpFunctions/getEmployeeDetails.php', { id: <?= $currentUserId ?> }, function(resp) {
                    if (!resp || resp.error) {
                        alert(resp?.error || 'Unable to fetch your information.');
                        return;
                    }

                    // Change modal title to "Edit Personal Info"
                    $('#modalTitle').text('Edit Personal Information');
                    
                    // Set hidden employee ID
                    $('#emp_id').val(<?= $currentUserId ?>);

                    // Fill form fields
                    $('#fname').val(resp.fname || '');
                    $('#m_initial').val(resp.m_initial || '');
                    $('#lname').val(resp.lname || '');
                    $('#email').val(resp.email || '');
                    $('#contact_no').val(resp.contact_no || '');
                    $('#birthday').val(resp.birthday || '');
                    $('#priority_status').val(resp.priority_status || 'None');
                    $('#address').val(resp.address || '');
                    $('#marital_status').val(resp.marital_status || '');
                    $('#size').val(resp.size || '');
                    $('#sex').val(resp.sex || '');
                    $('#income').val(resp.income || '');
                    $('#children_num').val(resp.children_num || '');
                    $('#concern').val(resp.concern || '');
                    
                    // Set department and campus values
                    $('#department').val(resp.department || '');
                    $('#campus').val(resp.campus || '');
                    
                    // Handle gender - check if it's a custom LGBTQIA+ value
                    if (resp.gender && !['Male', 'Female'].includes(resp.gender)) {
                        $('#gender').val('LGBTQIA+');
                        $('#otherGender').val(resp.gender).show();
                    } else {
                        $('#gender').val(resp.gender || '');
                    }

                    // Handle children radio buttons
                    if (resp.children_num > 0) {
                        $('#hasChildrenYes').prop('checked', true);
                        $('#childrenNumCol, #childConcernCol').show();
                    } else {
                        $('#hasChildrenNo').prop('checked', true);
                        $('#childrenNumCol, #childConcernCol').hide();
                    }

                    modal.classList.add("open");
                    document.body.style.overflow = "hidden";
                }, 'json').fail(() => alert('Request failed.'));
            });

            closeBtn.addEventListener("click", () => {
                modal.classList.remove("open");
                document.body.style.overflow = "";
            });

            modal.addEventListener("click", e => {
                if (e.target === modal) {
                    modal.classList.remove("open");
                    document.body.style.overflow = "";
                }
            });

            cancelBtn.addEventListener("click", e => {
                modal.classList.remove("open");
                document.body.style.overflow = "";
            });

            // Gender toggle
            const genderSelect = $("#gender");
            const otherGender = $("#otherGender");

            genderSelect.off('change').on("change", function () {
                if ($(this).val() === "LGBTQIA+") {
                    otherGender.show();
                } else {
                    otherGender.val("").hide();
                }
            });

            // Children toggle
            function toggleChildOptions() {
                const checkedChild = $('input[name="hasChildren"]:checked').val();
                if (checkedChild === "No") {
                    $("#children_num").val("");
                    $("#childrenNumCol").hide();
                    $("#concern").val("");
                    $("#childConcernCol").hide();
                } else {
                    $("#childrenNumCol").show();
                    $("#childConcernCol").show();
                }
            }

            $('input[name="hasChildren"]').off('change').on('change', function () {
                toggleChildOptions();
            });
            
            // Initialize toggles
            toggleChildOptions();

            // Submit personal info form for modifyAccount page
            $('#employeeForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                
                // Check if we're on modifyAccount page (not employees page)
                const isModifyAccountPage = window.location.pathname.includes('modifyAccount');
                
                if (isModifyAccountPage) {
                    // Add the saveInfo button to the form data
                    const formData = $(this).serialize() + '&saveInfo=1';

                    $.post('', formData, function(resp) {
                        location.reload();
                    }).fail(() => {
                        alert('Failed to update personal information.');
                    });
                }
            });
        });

        // Edit account button toggle
        $(document).ready(function () {
            $('#editBtn').click(function () {
                const isDisabled = $('#inputEmail').prop('disabled');

                $('#saveBtn, #inputEmail, #inputOldPassword, #inputNewPassword, #inputConfirmPassword')
                    .prop('disabled', !isDisabled);

                if (!isDisabled) {
                    $('#inputEmail, #inputOldPassword, #inputNewPassword, #inputConfirmPassword').val('');
                }

                $('#editBtn').text(isDisabled ? 'Cancel' : 'Edit');
                if (isDisabled) {
                    $('#saveBtn').show();
                } else {
                    $('#saveBtn').hide();
                }
            });

            $('#saveBtn').click(function () {
                location.reload();
            });
        });
    </script>
</body>
</html>