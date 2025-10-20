<?php
require_once 'includes.php';

session_start();

checkUser($_SESSION['user_id']);

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentFname = $user['fname'];
$currentLname = $user['lname'];


doubleCheck($currentPosition);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("Research View") ?>
</head>

<body>

    <div class="row everything">
        <div class="col sidebar">
            <?php echo sidebar("researchView", $currentPosition) ?>
        </div>
        <!-- MAIN CONTENT -->
        <div class="col-lg-10 col-sm-8 col-xs-6 mt-lg-3 mainContent">
            <?php topbar($currentUser, $currentPosition, "researchView") ?>

            <div id="contents">
                <div class="row mt-4">
                    <h1>Researches <span class="material-symbols-outlined">
                            article_person
                        </span></h1>
                </div>
                <div class="row mt-3">
                    <div class="col d-flex align-items-center">
                        <h6>List of all Researches</h6>
                    </div>
                    <div class="col d-flex align-items-center justify-content-end">
                        <form method="POST"><button class="btn btn-outline-success" name="refreshBtn" type="submit">Refresh</button></form>
                    </div>
                </div>
                <div class="row mt-3 flex flex-col gap-3">
                    <div class="col" style="background-color: white;
                    padding: 10px;
                    border-radius: 10px;
                    ">

                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Research Title</th>
                                    <th>Date Submitted</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                $con = con();
                                if ($currentPosition === "Researcher") {
                                    $sql = "SELECT * FROM research_tbl WHERE author = ?";
                                    $stmt = $con->prepare($sql);
                                    $stmt->bind_param("s", $currentUser);
                                } else {
                                    $sql = "SELECT * FROM research_tbl";
                                    $stmt = $con->prepare($sql);
                                }
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo '
                                        <tr>
                                        
                                        <td>' . htmlspecialchars($row['research_title']) . '</td>
                                        <td>' . htmlspecialchars($row['date_submitted']) . '</td>
                                        <td>' . htmlspecialchars($row['status']) . '</td>
                                        <td><a href="researchDetails.php?id=' . htmlspecialchars($row['id']) . '&prev=View Researches" style="color: #5f8cecff;">View More</a></td>
                                        
                                        </tr>';
                                    }
                                }

                                ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script defer>
        document.addEventListener("DOMContentLoaded", () => {
            const currentUser = <?php echo json_encode($currentUser); ?>;
            console.log(currentUser);
        });
    </script>
</body>

</html>