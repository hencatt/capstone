<?php
require_once "includes.php";
session_start();

checkUser($_SESSION['user_id']);
doubleCheck("Panel");

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];
$currentFname = $user['fname'];
$currentLname = $user['lname'];
$currentUserId = $user['id'];


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("Panel") ?>
</head>

<body>


    <div class="row everything">
        <div class="col sidebar" id="sidebar">
            <?php sidebar("approval", $currentPosition);
            ?>
        </div>

        <!-- Right side, main content -->
        <div class="col-10 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", "$currentPosition", "approval") ?>
            <div id="contents">
                <div class="row mt-4">
                    <div class="col">
                        <h1>Research Approval</h1>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellat, eius?
                    </div>
                </div>

                <!-- TOGGLES START -->
                <div class="row mt-4">
                    <div class="col">
                        <div class="btn-group btn-group-toggle" data-toggle="toggleButtons">
                            <label class="btn btn-secondary">
                                <input type="radio" name="toggleOptions" id="proposal_toggle" autocomplete="off"
                                    checked>
                                Proposal
                            </label>
                            <label class="btn btn-secondary">
                                <input type="radio" name="toggleOptions" id="completed_toggle" autocomplete="off"
                                    checked>
                                Completed
                            </label>
                        </div>
                    </div>
                </div>
                <!-- TOGGLES END -->

                <!-- TABLES -->
                <div class="row mt-4">
                    <div class="col">
                        <div class="table-responsive" style="background-color: white;
                        padding: 10px;
                        border-radius: 10px;
                        ">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th style="text-align: center; vertical-align: middle;">Vote Status</th>
                                        <th style="text-align: center; vertical-align: middle;">Title</th>
                                        <th style="text-align: center; vertical-align: middle;">Authors</th>
                                        <th style="text-align: center; vertical-align: middle;">Date</th>
                                        <th style="text-align: center; vertical-align: middle;">Category</th>
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
                                        $sql2 = "SELECT panel_id, research_id FROM votes_tbl WHERE panel_id = ? AND research_id = ?";
                                        $stmt2 = $con->prepare($sql2);
                                        $sql3 = "SELECT vote FROM votes_tbl WHERE panel_id = ? AND research_id = ?";
                                        $stmt3 = $con->prepare($sql3);
                                        while ($row = $result->fetch_assoc()) {
                                            echo '<tr>';
                                            $stmt2->bind_param("ii", $currentUserId, $row['id']);
                                            $stmt2->execute();
                                            $stmt2->store_result();
                                            if ($stmt2->num_rows >= 1) {
                                                $stmt3->bind_param("ii", $currentUserId, $row['id']);
                                                $stmt3->execute();
                                                $result3 = $stmt3->get_result();
                                                $voteRow = $result3->fetch_assoc();
                                                $voteValue = htmlspecialchars($voteRow['vote']);
                                                $voteStyle = "";
                                                if ($voteValue === "Reject") {
                                                    $voteStyle = "color:red;";
                                                    $voteValue = "Rejected";
                                                } else {
                                                    $voteStyle = "color:green;";
                                                    $voteValue = "Approved";
                                                }
                                                echo '<td style="text-align: center; vertical-align: middle; ' . $voteStyle . '">' . $voteValue . '</td>';
                                            } else {
                                                echo '<td style="text-align: center; vertical-align: middle;">Not Voted Yet</td>';
                                            }
                                            echo '
                                                <td style="text-align: center; vertical-align: middle;">' . htmlspecialchars($row['research_title']) . '</td>
                                                <td style="text-align: center; vertical-align: middle;">' . htmlspecialchars($row['author']) . ', ' . htmlspecialchars($row['co_author']) . '</td>
                                                <td style="text-align: center; vertical-align: middle;">' . htmlspecialchars($row['date_started']) . '</td>
                                                <td style="text-align: center; vertical-align: middle;">' . htmlspecialchars($row['research_category']) . '</td>
                                                <td style="vertical-align: middle;">
                                                    <a href="researchDetails.php?id=' . htmlspecialchars($row['id']) . '&prev=Approval" style="color: #5f8cecff;">View</a>
                                                </td>
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
    </div>
</body>

</html>