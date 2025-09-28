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
$currentFname = $user['fname'];
$currentLname = $user['lname'];


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

                    <div class="table-responsive" style="background-color: white;
                    padding: 10px;
                    border-radius: 10px;
                    ">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle;">Title</th>
                                    <th style="text-align: center; vertical-align: middle;">Authors</th>
                                    <th style="text-align: center; vertical-align: middle;">Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- LOOP HERE -->
                                <?php
                                $con = con();
                                $sql = "SELECT * FROM research_tbl";
                                $result = $con->query($sql);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo '
                                                <tr>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    ' . htmlspecialchars($row['research_title']) . '</td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    ' . htmlspecialchars($row['author']) . ', ' .
                                            htmlspecialchars($row['co_author']) . '</td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    ' . htmlspecialchars($row['date_started']) . '</td>
                                                <td class="d-flex justify-content-center align-items-center gap-2">
                                                    <button class="btn btn-outline-success">Approve</button>
                                                    <button class="btn btn-outline-danger">Reject</button>
                                                </td>
                                                </tr>
                                                ';
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
</body>

</html>