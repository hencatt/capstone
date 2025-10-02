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
                <div class="row mt-3 flex flex-col gap-3">
                    <div class="col"
                        style="background-color: white;
                    padding: 10px;
                    border-radius: 10px;
                    ">
                        <div class="row flex flex-row text-center">
                            <div class="col ">
                                <b><label>Research Title</label></b>
                            </div>
                            <div class="col"><b><label>Description</label></b></div>
                            <div class="col-2"><b><label>Date Submitted</label></b></div>
                            <div class="col-2"><b><label>Status</label></b></div>
                            <div class="col-2"></div>
                        </div>
                        <div class="row mt-3 flex flex-row align-items-center">
                            <div class="col text-center">
                                <label>##Title Here</label>
                            </div>
                            <div class="col"><label>Lorem ipsum dolor sit amet consectetur adipisicing elit. Suscipit, odit.</label></div>
                            <div class="col-2 text-center"><label>12/12/2026</label></div>
                            <div class="col-2 text-center"><label>##Status Here</label></div>
                            <div class="col-2 text-center" style="color: #5f8cecff"><label>View More</label></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>