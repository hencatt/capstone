<?php
require_once 'includes.php';

session_start();

checkUser($_SESSION['user_id'], $_SESSION['user_username']);
$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];

// getID ng research first!!

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("Research Details") ?>
</head>

<body>
    <?= addDelay("researchDetails", $currentUser, $currentPosition); ?>

    <!-- Left Sidebar -->
    <div class="row everything">
        <div class="col sidebar">
            <?php sidebar("researchDetails", $currentPosition) ?>
        </div>
        <!-- Main Contents -->

        <div class="col-10 mt-3 mainContent">
            <?php topbar($currentUser, $currentPosition, "researchDetails") ?>

            <div id="contents">
                <div class="row mt-5">
                    <div class="col d-flex flex-row gap-3">
                        <h1>ReserachTitle</h1>
                        <figcaption class="blockquote-footer align-self-end">##date submitted here</figcaption>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <p>##Description</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <h5>##actualComments</h5>
                    </div>
                    <div class="col">
                        <h5>###Authors</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>