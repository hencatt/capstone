<?php
require_once 'includes.php';

session_start();

checkUser($_SESSION['user_id'], $_SESSION['user_username']);
doubleCheck("RET Chair");
// Returns to login if not RET Chair

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];


?>



<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("RET Chair"); ?>
</head>

<body>
    <?php addDelay("dashboard", $currentUser, $currentPosition) ?>

    <div class="row everything">
        <div class="col sidebar" id="sidebar">
            <?php sidebar("dashboard", $currentPosition);
            ?>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-10 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", "$currentPosition", "dashboard") ?>
            <div id="contents">
                <div class="row gap-3 mt-3">
                    <div class="col summaryOverview">
                        <h6 for="">Pending Researches</h6>
                        <br><h6>##NUMBER</h6>
                    </div>
                    <div class="col summaryOverview ">

                    </div>
                    <div class="col summaryOverview">

                    </div>
                </div>
                <div class="row mt-5">
                    <div class="col d-flex justify-content-center align-items-center summaryOverview">
                        <h5>##ONGOING RESEARCH</h5>
                    </div>
                </div>
                <div class="row gap-3 mt-3">
                    <div class="col-8 summaryOverview">
                        <h6>##TOTAL SUBMISSION</h6>
                    </div>
                    <div class="col summaryOverview">
                        <h6>##AUTHORS CO AUTHORS</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>