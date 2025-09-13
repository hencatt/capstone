<?php
require_once 'includes.php';

session_start();

checkUser($_SESSION['user_id'], $_SESSION['user_username']);

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];


doubleCheck($currentPosition);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("Research View") ?>
</head>

<body>
    <?php addDelay("researchView", $currentUser, $currentPosition) ?>
    <div class="row everything">
        <div class="col sidebar">
            <?php echo sidebar("researchView", $currentPosition) ?>
        </div>
        <!-- MAIN CONTENT -->
        <div class="col-lg-10 col-sm-8 col-xs-6 mt-lg-3 mainContent">
            <?php topbar($currentUser, $currentPosition, "researchView") ?>

            <div id="contents">
                <div class="row mt-5">
                    <h1>Researches <span class="material-symbols-outlined">
                            article_person
                        </span></h1>
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <h6>List of all Researches</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <table class="table">
                            <thead>
                                <tr style="text-align: center;">
                                    <td>Research Title</td>
                                    <td>Author/s</td>
                                    <td>Date Created (YYYY-MM-DD)</td>
                                    <td>Status</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>GAD Portal</td>
                                    <td>Ivan Kyle, Henreich, Jayson, Ashley</td>
                                    <td>2025-01-12</td>
                                    <td>Approved</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>