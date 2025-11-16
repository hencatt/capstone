<?php
require_once 'includes.php';

session_start();

checkUser($_SESSION['user_id']);

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];


if ($currentPosition !== "Director") {
    if ($currentPosition !== "Technical Assistant") {
        header("Location: ../index.php");
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("View Logs") ?>
</head>

<body>
  
    <div class="row everything">
        <div class="col sidebar" id="sidebar">
            <?php sidebar("viewLogs", $currentPosition);
            ?>
        </div>

        <!-- Main Content -->
        <div class="col-10 mt-lg-3 mainContent">
            <?= topbar($currentUser, $currentPosition, "logs") ?>
            <div id="contents">

                <div class="row mt-4">
                    <div class="col">
                        <h1>Logs <span class="material-symbols-outlined">overview</span></h1>
                        <p>Any user activity will be recorded here.</p>
                        <!-- SORTING?? -->
                        <!-- Table -->
                        <div class="row mt-3 table-responsive">
                            <div class="col">
                                <table class="table table-bordered" id="logTable">
                                    <thead>
                                        <tr>
                                            <td>Name</td>
                                            <td>Activity</td>
                                            <td>Log Date</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM logs ORDER BY log_id DESC";
                                        $result = $con->query($sql);
                                        while ($row = $result->fetch_assoc()) {
                                            $name = htmlspecialchars($row["username"]);
                                            $activity = htmlspecialchars($row["activity"]);
                                            $logDate = htmlspecialchars($row["log_date"]);
                                            $tableData = <<<EOD
                                                    <tr>
                                                        <td>$name</td>
                                                        <td>$activity</td>
                                                        <td>$logDate</td>
                                                    </tr>
                                                EOD;
                                            echo $tableData;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>