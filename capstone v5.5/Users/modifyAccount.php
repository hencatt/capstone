<?php
include("../gad_portal.php");
include("../insertingLogs.php");

session_start();

// GEGET CURRENT USER PARA MAREADY KUNG SAAN IBABALIK NA DASHBOARD
$currentId = $_SESSION['user_id'];
$con = new mysqli("localhost", "root", "", "gad_portal");
$sql = "SELECT fname, lname, username, email, position FROM accounts_tbl WHERE id = '$currentId'";
$result = $con->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $currentFname = htmlspecialchars($row["fname"]);
        $currentLname = htmlspecialchars($row["lname"]);
        $currentEmail = htmlspecialchars($row["email"]);
        $currentUsername = htmlspecialchars($row['username']);
        $currentUser = htmlspecialchars($row['fname']) . " " . htmlspecialchars($row['lname']);
        $currentPosition = htmlspecialchars($row['position']);
    }
} else {
    echo "<script>console.log('No UserID Found')</script>";
}


// TAGA LOCATE KUNG ANONG ROLE MAPUPUNTA/DASHBOARD
if ($currentPosition === "Technical Assistant") {
    $dashboard = "TA.php";
    echo " <script>console.log('Going TA')</script>";
} else if ($currentPosition === "Focal Person") {
    $dashboard = "focalPerson.php";
} else if ($currentPosition === "Director") {
    $dashboard = "director.php";
}

// UPDATE BUTTON FUNCTION
if (isset($_POST["saveBtn"])) {
    $newFn = $_POST["inputFn"];
    $newLn = $_POST["inputLn"];
    $newEmail = $_POST["inputEmail"];
    $newUsername = $_POST["inputUsername"];
    $oldPassword = $_POST["inputOldPassword"];
    $newPassword = $_POST["inputNewPassword"];
    $confirmPassword = $_POST["inputConfirmPassword"];

    $currentId = $_SESSION["user_id"];
    $con = new mysqli("localhost", "root", "", "gad_portal");

    $fields = [];
    $types = "";
    $params = [];

    // Optional fields
    if (!empty($newFn)) {
        $fields[] = "fname = ?";
        $types .= "s";
        $params[] = $newFn;
    }
    if (!empty($newLn)) {
        $fields[] = "lname = ?";
        $types .= "s";
        $params[] = $newLn;
    }
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="css/modifyAccount.css">
    <!-- font link -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <!-- icon link -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

    <title>Modify Account</title>

    <!-- jQuery Link -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<!-- Left Sidebar -->
<div class="row">
    <div class="col sidebar">
        <div class="row mt-lg-3 topLeft">
            <div class="col neustLogo">
                <img src="../assets/neust_logo-1-1799093319.png" alt="NeustLogo" class="logo">
            </div>
            <div class="col-lg-8">
                <label>NEUST GAD Portal</label>
            </div>
        </div>
        <div class="sidebarOptions">
            <label class="category">Home</label>
            <li id="active">
                <a href="<?= $dashboard ?>" class="categoryItem">
                    <span class="material-symbols-outlined">dashboard</span>
                    Dashboard
                </a>
            </li>
            <label class="category">General</label>
            <li><a href="#" class="categoryItem">
                    <span class="material-symbols-outlined">groups</span>
                    Employees</a></li>
            <li><a href="#" class="categoryItem">
                    <span class="material-symbols-outlined">inventory_2</span>
                    Inventory</a></li>
            <li><a href="#" class="categoryItem">
                    <span class="material-symbols-outlined">event</span>
                    Events</a></li>
            <label class="category">Settings</label>
            <li><a href="#" class="categoryItem">
                    <span class="material-symbols-outlined">person</span>
                    Account</a></li>
            <li><a href="../logout.php?logout=true" class="categoryItem">
                    <span class="material-symbols-outlined">logout</span>
                    Logout</a></li>
        </div>
    </div>
    <!-- MAIN CONTENTS -->
    <div class="col-lg-10 col-sm-8 col-xs-6 mt-lg-3 mainContent">
        <div class="row">
            <div class="col">
                <h1>Edit Profile</h1>
                <!-- FORM ROW -->
                <form method="POST" class="form-inline">
                    <div class="form-group row mt-lg-5 ">
                        <h5>User Info</h5>
                        <div class="row">
                            <div class="col-lg-4 form-group d-flex flex-row align-items-center gap-3">
                                <label for="inputFn" class="col-form-label col-sm-3">First Name:</label>
                                <input type="text" name="inputFn" id="inputFn" disabled placeholder="<?= $currentFname ?>"
                                    class="form-control">
                            </div>
                            <div class="col-lg-4 form-group d-flex flex-row align-items-center gap-3">
                                <label for="inputLn" class="col-form-label col-sm-3">Last Name:</label>
                                <input type="text" name="inputLn" id="inputLn" disabled placeholder="<?= $currentLname ?>"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-lg-4 form-group d-flex flex-row align-items-center gap-3">
                                <label for="inputUsername" class="col-form-label col-sm-3">Username:</label>
                                <input type="text" name="inputUsername" id="inputUsername" disabled
                                    placeholder="<?= $currentUsername ?>" class="form-control">
                            </div>
                            <div class="col-lg-4 form-group d-flex flex-row align-items-center gap-3">
                                <label for="inputEmail" class="col-form-label col-sm-3">Email:</label>
                                <input type="text" name="inputEmail" id="inputEmail" disabled
                                    placeholder="<?= $currentEmail ?>" class="form-control">
                            </div>
                        </div>
                        <div class="row mt-lg-5">
                            <h5>Change Password</h5>
                        </div>
                        <div class="row mt-2">
                            <div class="col-lg-4 form-group d-flex flex-row align-items-center gap-3">
                                <label for="inputOldPassword" class="col-sm-3 col-form-control">Old Password:</label>
                                <input type="password" name="inputOldPassword" id="inputOldPassword"
                                    placeholder="Enter Old Password" class="form-control">
                            </div>
                            <div class="row mt-2">
                                <div class="col-lg-4 form-group d-flex flex-row align-items-center gap-3">
                                    <label for="inputNewPassword" class="col-form-label col-sm-3">New Password:</label>
                                    <input type="password" name="inputNewPassword" id="inputNewPassword"
                                        placeholder="Enter New Password" class="form-control">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-lg-4 form-group d-flex flex-row align-items-center gap-3">
                                    <label for="inputConfirmPassword" class="col-sm-3 col-form-label">Confirm
                                        Password</label>
                                    <input type="password" name="inputConfirmPassword" id="inputConfirmPassword"
                                        placeholder="Confirm Password" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- BUTTONS ROW -->
                    <div class="row">
                        <div class="col-lg-8 d-flex align-items-center justify-content-end gap-3">
                            <button type="button" name="editBtn" id="editBtn" class="btn btn-outline-secondary">Edit
                                <span class="material-symbols-outlined">
                                    edit
                                </span></button>
                            <button type="submit" name="saveBtn" id="saveBtn" class="btn btn-outline-success"
                                disabled>Save
                                Changes <span class="material-symbols-outlined">
                                    check
                                </span></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
<!-- PANG TOGGLE NA BUTTON EDIT AND CANCEL -->
<script>
    $('#editBtn').click(function () {
        const isDisabled = $('#inputFn').prop('disabled');

        // Toggle disabled state for inputs
        $('#saveBtn, #inputFn, #inputLn, #inputEmail, #inputUsername').prop('disabled', !isDisabled);

        // Optional: Clear values when switching back to disabled (like a reset)
        if (!isDisabled) {
            $('#inputFn, #inputLn, #inputEmail, #inputUsername').val('');
        }

        // Change button text to "Cancel" or "Edit"
        $('#editBtn').text(isDisabled ? 'Cancel' : 'Edit');
    });
</script>
<!-- SCRAP KO MUNA TO PANG RELOAD SANA KASI HINDI NAG A-UPDATE YUNG PLACEHOLDERS SA FORM -->
<script>
    $('#saveBtn').click(function () {
        location.reload();
    });
</script>


</html>