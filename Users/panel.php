<?php
require_once "includes.php";
session_start();

checkUser($_SESSION['user_id'], $_SESSION['user_username']);
doubleCheck("Panel");

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("Panel") ?>
</head>

<body>
    <?php addDelay("dashboard", $currentUser, $currentPosition) ?>

    <div class="row everything">
        <div class="col sidebar" id="sidebar">
            <?php sidebar("approval", $currentPosition);
            ?>
        </div>

        <!-- Right side, main content -->
        <div class="col-10 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", "$currentPosition", "approval") ?>
            <div id="contents">
                <div class="row mt-5">
                    <div class="col">
                        <h1>Research Approval</h1>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellat, eius?
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col">
                        <label>##TITLE</label>
                    </div>
                    <div class="col">
                        <label>##AUTHOR</label>
                    </div>
                    <div class="col">
                        <label>##DATE</label>
                    </div>
                    <div class="col">
                        <label>##BUTTONS</label>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</body>

</html>