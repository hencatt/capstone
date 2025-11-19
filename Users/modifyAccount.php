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
    <style>
        :root {
            --primary-blue: #0a7afa;
            --primary-pink: #db0fe2;
        }

        .account-container {
            background-color: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .account-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .account-header h1 {
            color: var(--primary-blue);
            font-weight: 600;
            margin: 0;
        }

        .section-title {
            color: var(--primary-blue);
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 20px;
            margin-top: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 10px 15px;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(10, 122, 250, 0.15);
        }

        .btn-gradient {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-pink) 100%);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        /* .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(10, 122, 250, 0.3);
            color: white;
        }

        .btn-outline-gradient {
            background: white;
            border: 2px solid var(--primary-blue);
            color: var(--primary-blue);
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-outline-gradient:hover {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-pink) 100%);
            border-color: transparent;
            color: white;
        } */

        .profile-card {
            background-color: #ffffffff;
            border-radius: 20px;
            padding: 30px;
            color: black;
        }

        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 20px;
        }

        .profile-info {
            background: rgba(78, 102, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .profile-info label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 3px;
        }

        .profile-info div {
            font-weight: 600;
        }

        .password-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <!-- Left Sidebar -->
    <div class="row everything">
        <div class="col sidebar">
            <?php echo sidebar("account", $currentPosition) ?>
        </div>
        
        <!-- MAIN CONTENTS -->
        <div class="col-10 mt-3 mainContent">
            <?php topbar($currentUser, $currentPosition, "account") ?>

            <div id="contents">
                <div class="row">
                    <!-- Main Account Section -->
                    <div class="col-lg-8">
                        <div class="account-container">
                            <div class="account-header">
                                <h1>Account</h1>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-info" id="personalInfoButton">
                                        <i class="fas fa-user-edit"></i> Edit Personal Info
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="editBtn">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </div>
                            </div>

                            <!-- ACCOUNT INFO FORM -->
                            <form method="POST">
                                <h5 class="section-title">Account Information</h5>
                                
                                <div class="form-group">
                                    <label for="inputEmail">Email Address</label>
                                    <input type="text" name="inputEmail" id="inputEmail" 
                                           placeholder="<?= $currentEmail ?>" class="form-control" disabled>
                                </div>

                                <div class="password-section">
                                    <h5 class="section-title" style="margin-top: 0;">Change Password</h5>
                                    
                                    <div class="form-group">
                                        <label for="inputOldPassword">Old Password</label>
                                        <input type="password" name="inputOldPassword" id="inputOldPassword"
                                               placeholder="Enter Old Password" class="form-control" disabled>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="inputNewPassword">New Password</label>
                                        <input type="password" name="inputNewPassword" id="inputNewPassword"
                                               placeholder="Enter New Password" class="form-control" disabled>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="inputConfirmPassword">Confirm Password</label>
                                        <input type="password" name="inputConfirmPassword" id="inputConfirmPassword"
                                               placeholder="Confirm Password" class="form-control" disabled>
                                    </div>
                                </div>

                                <!-- SAVE BUTTON -->
                                <div class="text-end mt-4">
                                    <button type="submit" name="saveBtn" id="saveBtn" 
                                            class="btn btn-outline-success" disabled style="display: none;">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- PROFILE SIDEBAR -->
                    <div class="col-lg-4">
                        <div class="profile-card">
                            <div class="text-center">
                                <img src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fstatic.vecteezy.com%2Fsystem%2Fresources%2Fpreviews%2F005%2F544%2F718%2Foriginal%2Fprofile-icon-design-free-vector.jpg&f=1&nofb=1&ipt=a3a03e1e1c2a147e5b78c95b25acf2b7a4edf938f68908367342f6caf5625631"
                                     alt="Profile Photo" class="profile-img">
                            </div>
                            
                            <?php
                            $conn = newCon();
                            $sql = "SELECT ei.*, et.* FROM employee_info AS ei 
                                    INNER JOIN employee_tbl AS et ON et.id = ei.employee_id 
                                    WHERE ei.employee_id = $currentUserId";
                            $stmt = $conn->query($sql);
                            $row = $stmt->fetch_assoc();
                            ?>

                            <div class="profile-info">
                                <label>Name</label>
                                <div><?= $row["fname"] . " " . $row["lname"]; ?></div>
                            </div>

                            <div class="profile-info">
                                <label>Campus</label>
                                <div><?= $row["campus"]; ?></div>
                            </div>

                            <div class="profile-info">
                                <label>Department</label>
                                <div><?= $row["department"]; ?></div>
                            </div>

                            <div class="profile-info">
                                <label>Birthday</label>
                                <div><?= $row["birthday"]; ?></div>
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
                $.post('../phpFunctions/getEmployeeDetails.php', { id: <?= $currentUserId ?> }, function(resp) {
                    if (!resp || resp.error) {
                        alert(resp?.error || 'Unable to fetch your information.');
                        return;
                    }

                    $('#modalTitle').text('Edit Personal Information');
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
                    $('#department').val(resp.department || '');
                    $('#campus').val(resp.campus || '');
                    
                    // Handle gender
                    if (resp.gender && !['Male', 'Female'].includes(resp.gender)) {
                        $('#gender').val('LGBTQIA+');
                        $('#otherGender').val(resp.gender).show();
                    } else {
                        $('#gender').val(resp.gender || '');
                    }

                    // Handle children
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
            $("#gender").off('change').on("change", function () {
                if ($(this).val() === "LGBTQIA+") {
                    $("#otherGender").show();
                } else {
                    $("#otherGender").val("").hide();
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

            $('input[name="hasChildren"]').off('change').on('change', toggleChildOptions);
            toggleChildOptions();

            // Submit personal info form
            $('#employeeForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                const isModifyAccountPage = window.location.pathname.includes('modifyAccount');
                
                if (isModifyAccountPage) {
                    const formData = $(this).serialize() + '&saveInfo=1';
                    $.post('', formData, function(resp) {
                        location.reload();
                    }).fail(() => alert('Failed to update personal information.'));
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

                $('#editBtn').html(isDisabled ? '<i class="fas fa-times"></i> Cancel' : '<i class="fas fa-edit"></i> Edit');
                $('#saveBtn').toggle(isDisabled);
            });

            $('#saveBtn').click(function () {
                location.reload();
            });
        });
    </script>
</body>
</html>