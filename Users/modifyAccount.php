<?php
require_once 'includes.php';

session_start();

// GEGET CURRENT USER PARA MAREADY KUNG SAAN IBABALIK NA DASHBOARD
$user = getUser();
$currentFname = $user['fname'];
$currentLname = $user['lname'];
$currentUsername = $user['username'];
$currentEmail = $user['email'];
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];

// UPDATE BUTTON FUNCTION
if (isset($_POST["saveBtn"])) {
    // $newFn = $_POST["inputFn"];
    // $newLn = $_POST["inputLn"];
    $newEmail = $_POST["inputEmail"];
    $newUsername = $_POST["inputUsername"];
    $oldPassword = $_POST["inputOldPassword"];
    $newPassword = $_POST["inputNewPassword"];
    $confirmPassword = $_POST["inputConfirmPassword"];

    $currentId = $_SESSION["user_id"];
    $con = newCon();

    $fields = [];
    $types = "";
    $params = [];

    // Optional fields
    // if (!empty($newFn)) {
    //     $fields[] = "fname = ?";
    //     $types .= "s";
    //     $params[] = $newFn;
    // }
    // if (!empty($newLn)) {
    //     $fields[] = "lname = ?";
    //     $types .= "s";
    //     $params[] = $newLn;
    // }
    if (!empty($newEmail)) {
        $fields[] = "email = ?";
        $types .= "s";
        $params[] = $newEmail;
    }
    if (!empty($newUsername)) {
        $fields[] = "username = ?";
        $types .= "s";
        $params[] = $newUsername;
    }

    // ✅ Password Update Logic
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
                    echo "<script>alert('Incorrect old password');</script>";
                }
            }
            $stmt->close();
        } else {
            echo "<script>alert('New passwords do not match');</script>";
        }
    }

    // ✅ Execute Update if needed
    if (!empty($fields)) {
        $sql = "UPDATE accounts_tbl SET " . implode(", ", $fields) . " WHERE id = ?";
        $types .= "i";
        $params[] = $currentId;

        $stmt = $con->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            insertLog($currentUser, "Profile Updated", date('Y-m-d H:i:s'));
            echo '<script>alert("Update successful")</script>';
        } else {
            echo '<script>alert("Update failed")</script>';
        }

        $stmt->close();
    } else {
        echo '<script>alert("No data to update")</script>';
    }

    $con->close();
}




?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("Modify Account") ?>
</head>
<?php addDelay("account", $currentUser, $currentPosition) ?>

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
                    <!-- FORM ROW -->
                    <form method="POST" class="form-inline">
                        <div class="form-group row">
                            <div class="col">
                                <h5>Account Information</h5>
                            </div>
                            <div class="col d-flex justify-content-end gap-3">
                                <button type="button" name="personalInfoButton" id="personalInfoButton"
                                    class="btn btn-outline-primary">Edit Personal Info</button>
                                <button type="button" name="editBtn" id="editBtn" class="btn btn-outline-secondary">Edit
                                </button>
                            </div>

                            <!-- <div class="row">
                                <div class="row-lg-4 form-group d-flex flex-row align-items-center gy-3">
                                    <label for="inputFn" class="col-form-label col-sm-3">First Name:</label>
                                    <input type="text" name="inputFn" id="inputFn" disabled
                                        placeholder="<?= $currentFname ?>" class="form-control">
                                </div>
                                <div class="row-lg-4 form-group d-flex flex-row align-items-center gy-2">
                                    <label for="inputLn" class="col-form-label col-sm-3">Last Name:</label>
                                    <input type="text" name="inputLn" id="inputLn" disabled
                                        placeholder="<?= $currentLname ?>" class="form-control">
                                </div>
                            </div> -->
                            
                            <div class="row mt-2">
                                <div class="row-lg-4 form-group d-flex flex-row align-items-center gy-3">
                                    <label for="inputUsername" class="col-form-label col-sm-3">Username:</label>
                                    <input type="text" name="inputUsername" id="inputUsername" disabled
                                        placeholder="<?= $currentUsername ?>" class="form-control">
                                </div>
                                <div class="row-lg-4 form-group d-flex flex-row align-items-center gy-2">
                                    <label for="inputEmail" class="col-form-label col-sm-3">Email:</label>
                                    <input type="text" name="inputEmail" id="inputEmail" disabled
                                        placeholder="<?= $currentEmail ?>" class="form-control">
                                </div>
                            </div>
                            <!-- <div class="row mt-5" style="background-color: black; height:2px; width:100%">
                            </div> -->
                            <div class="row mt-5">
                                <h5>Change Password</h5>
                            </div>
                            <div class="row mt-2">
                                <div class="row">
                                    <div class="row-lg-4 form-group d-flex flex-row align-items-center gy-3">
                                        <label for="inputOldPassword" class="col-sm-3 col-form-control">Old
                                            Password:</label>
                                        <input type="password" name="inputOldPassword" id="inputOldPassword"
                                            placeholder="Enter Old Password" class="form-control" disabled>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="row-lg-4 form-group d-flex flex-row align-items-center gy-2">
                                        <label for="inputNewPassword" class="col-form-label col-sm-3">New
                                            Password:</label>
                                        <input type="password" name="inputNewPassword" id="inputNewPassword"
                                            placeholder="Enter New Password" class="form-control" disabled>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="row-lg-4 form-group d-flex flex-row align-items-center gy-2">
                                        <label for="inputConfirmPassword" class="col-sm-3 col-form-label">Confirm
                                            Password</label>
                                        <input type="password" name="inputConfirmPassword" id="inputConfirmPassword"
                                            placeholder="Confirm Password" class="form-control" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- SAVE BUTTON ROW -->
                        <div class="row mt-3">
                            <div class="row-8 d-flex align-items-center justify-content-end">
                                <button type="submit" name="saveBtn" id="saveBtn" class="btn btn-outline-success"
                                    disabled style="display: none;">Save
                                    Changes <span class="material-symbols-outlined">
                                        check
                                    </span></button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4">
                    <div class="row">
                        <div class="col text-center">
                            <img src="
                            https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fstatic.vecteezy.com%2Fsystem%2Fresources%2Fpreviews%2F005%2F544%2F718%2Foriginal%2Fprofile-icon-design-free-vector.jpg&f=1&nofb=1&ipt=a3a03e1e1c2a147e5b78c95b25acf2b7a4edf938f68908367342f6caf5625631
                            " alt="Profile Photo" style="
                            width :150px;
                            height :150px;
                            border-radius :50%;
                            border: solid black 1px;
                            ">
                        </div>
                    </div>
                    <div class="row mt-3 d-flex align-items-center text-center">
                        <div class="col" style="text-decoration: underline;">Edit</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL -->
<?php require('./reusableHTML/personalInfoModal.php') ?>

<script>
    $(document).ready(function () {
        // $('#personalModal').load("./reusableHTML/personalInfoModal.php", function () {

            const personalBtn = document.getElementById("personalInfoButton");
            const modal = document.getElementById("modal");
            const closeBtn = modal.querySelector(".close-btn");
            const cancelBtn = document.getElementById("cancelInfo");

            const childrenNum = $("#inputChildrenNum");
            const childConcern = $("#inputConcern");
            const childrenNumCol = $("#childrenNumCol");
            const childConcernCol = $("#childConcernCol");


            personalBtn.addEventListener("click", () => {
                console.log("open");
                modal.classList.add("open");
            });

            closeBtn.addEventListener("click", () => {
                console.log("close");
                modal.classList.remove("open");
            });

            modal.addEventListener("click", e => {
                if (e.target === modal) modal.classList.remove("open");
            });

            cancelBtn.addEventListener("click", e => {
                modal.classList.remove("open");
            })

            // Gender toggle
            const genderSelect = $("#inputGender");
            const otherGender = $("#otherGender");
            otherGender.hide();

            function toggleChildOptions() {
                const checkedChild = $('input[name="inputChildren"]:checked').val();
                if (checkedChild === "No") {
                    childrenNum.val("");
                    childrenNumCol.hide();
                    childConcern.val("");
                    childConcernCol.hide();
                } else {
                    childrenNumCol.show();
                    childConcernCol.show();
                }
            }


            genderSelect.on("change", function () {
                if ($(this).val() === "LGBTQIA+") {
                    otherGender.show();
                } else {
                    otherGender.val("")
                    otherGender.hide();
                }
            });

            toggleChildOptions();
            $('input[name="inputChildren"]').on('change', function () {
                toggleChildOptions();
            })
        // });
    });
</script>
<!-- PANG TOGGLE NA BUTTON EDIT AND CANCEL -->
<script>
    $('#editBtn').click(function () {
        const isDisabled = $('#inputEmail').prop('disabled');

        // Toggle disabled state for inputs
        $('#saveBtn, #inputEmail, #inputUsername, #inputOldPassword, #inputNewPassword, #inputConfirmPassword').prop('disabled', !isDisabled);

        // Optional: Clear values when switching back to disabled (like a reset)
        if (!isDisabled) {
            $('#inputEmail, #inputUsername, #inputOldPassword, #inputNewPassword, #inputConfirmPassword').val('');
        }

        // Change button text to "Cancel" or "Edit"
        $('#editBtn').text(isDisabled ? 'Cancel' : 'Edit');
        $('#editBtn').prop(isDisabled ? $('#saveBtn').show() : $('#saveBtn').hide());
    });
</script>

<script>
    $('#saveBtn').click(function () {
        location.reload();
    });
</script>



</body>

</html>