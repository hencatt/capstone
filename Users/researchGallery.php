<?php
require_once 'includes.php';
session_start();
checkUser($_SESSION['user_id']);

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];
$currentFname = $user['fname'];
$currentLname = $user['lname'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php headerLinks("Research Gallery") ?>
</head>

<body>
    <div class="row everything">
        <div class="col sidebar">
            <?php sidebar("researchGallery", $currentPosition) ?>
        </div>
        <!-- Main Contents -->

        <div class="col-10 mt-3 mainContent">
            <?php topbar($currentUser, $currentPosition, "researchGallery") ?>

            <div id="contents">
                <div class="row mt-4">
                    <div class="col">
                        <h1>Research Gallery</h1>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col">
                        Lorem ipsum, dolor sit amet consectetur adipisicing elit. Repudiandae, magnam.
                    </div>
                </div>

                <!-- APPROVED RESEARCHES -->
                <div class="row mt-4">
                    <div class="col">
                        <h4>Approved Reseraches</h4>
                    </div>
                </div>

                <div class="row mt-2 d-flex flex-row gap-3" style="overflow-x: auto;">
                    <!-- LOOP HERE -->
                    <?php
                    $status = "Approved";
                    $con = con();
                    $sql = "SELECT * FROM research_tbl WHERE status = ?";
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("s", $status);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()): ?>


                        <div class="col d-flex flex-column gap-3"
                            style="background-color:white; padding:20px; border-radius: 10px; max-width: 400px; min-height:500px; max-height: 500px;">
                            <div style="max-height: 400px;">
                                <div>
                                    <h5>
                                        <?=
                                            $row['research_title'];
                                        ?>
                                    </h5>
                                    <i class="blockquote-footer">Date Submitted:
                                        <?=
                                            $row['date_submitted'];
                                        ?>
                                    </i>
                                </div>
                                <hr>
                                <div>
                                    <i><b>Author/s: </b>
                                        <?=
                                            $row['author'] . ", " . $row['co_author'];
                                        ?>
                                    </i><br>
                                    <i><b>NEUST Agenda: </b>
                                        <?=
                                            $row['research_agenda'];
                                        ?>
                                    </i><br>
                                    <i><b>SDG: </b>
                                        <?=
                                            $row['research_sdg'];
                                        ?>
                                    </i>
                                </div>
                                <hr>
                                <div style="max-height: 200px; overflow-y: auto;">
                                    <?=
                                        $row['description'];
                                    ?>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary mt-auto align-self-end">View PDF</button>
                        </div>

                    <?php endwhile; ?>
                </div>

                <hr>

                <div class="row">
                    <div class="col">
                        <h4>Rejected Researches</h4>
                    </div>
                </div>

                <div class="row mt-2 d-flex flex-row gap-3" style="overflow-x: auto;">
                    <!-- LOOP HERE -->
                    <?php
                    $status = "Rejected";
                    $con = con();
                    $sql = "SELECT * FROM research_tbl WHERE status = ?";
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("s", $status);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()): ?>


                        <div class="col d-flex flex-column gap-3"
                            style="background-color:white; padding:20px; border-radius: 10px; max-width: 400px; min-height:500px; max-height: 500px;">
                            <div style="max-height: 400px;">
                                <div>
                                    <h5>
                                        <?=
                                            $row['research_title'];
                                        ?>
                                    </h5>
                                    <i class="blockquote-footer">Date Submitted:
                                        <?=
                                            $row['date_submitted'];
                                        ?>
                                    </i>
                                </div>
                                <hr>
                                <div>
                                    <i><b>Author/s: </b>
                                        <?=
                                            $row['author'] . ", " . $row['co_author'];
                                        ?>
                                    </i><br>
                                    <i><b>NEUST Agenda: </b>
                                        <?=
                                            $row['research_agenda'];
                                        ?>
                                    </i><br>
                                    <i><b>SDG: </b>
                                        <?=
                                            $row['research_sdg'];
                                        ?>
                                    </i>
                                </div>
                                <hr>
                                <div style="max-height: 200px; overflow-y: auto;">
                                    <?=
                                        $row['description'];
                                    ?>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary mt-auto align-self-end">View PDF</button>
                        </div>

                    <?php endwhile; ?>
                </div>

            </div>
        </div>
    </div>
</body>

</html>