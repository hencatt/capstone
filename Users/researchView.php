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

function checkVotes($researchId)
{
    $approve = "Approved";
    $reject = "Rejected";
    $pending = "Pending";

    $con = con();

    $sql = "SELECT 
                SUM(vote = 'Approve') AS approve_count,
                SUM(vote = 'Reject')  AS reject_count
            FROM votes_tbl
            WHERE research_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $researchId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $approve_count = (int) $row['approve_count'];
    $reject_count = (int) $row['reject_count'];
    $total_votes = $approve_count + $reject_count;

    if ($total_votes > 1) {
        if ($approve_count > 1) {
            $sql2 = "UPDATE research_tbl SET status = ? WHERE id = ?";
            $stmt2 = $con->prepare($sql2);
            $stmt2->bind_param("si", $approve, $researchId);
            $stmt2->execute();
        } else if ($reject_count > 1) {
            $sql2 = "UPDATE research_tbl SET status = ? WHERE id = ?";
            $stmt2 = $con->prepare($sql2);
            $stmt2->bind_param("si", $reject, $researchId);
            $stmt2->execute();
        }
    } else {
        $sql2 = "UPDATE research_tbl SET status = ? WHERE id = ?";
        $stmt2 = $con->prepare($sql2);
        $stmt2->bind_param("si", $pending, $researchId);
        $stmt2->execute();
    }
}

function updateResearchStatus()
{
    $con = con();
    $sql = "SELECT id FROM research_tbl";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        checkVotes($row['id']);
    }
}

updateResearchStatus();

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
                    <!-- <div class="col d-flex align-items-center justify-content-end">
                        <form method="POST"><button class="btn btn-outline-success" name="refreshBtn"
                                type="submit">Refresh</button></form>
                    </div> -->
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
                                    <th>Category</th>
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
                                        <td>' . htmlspecialchars($row['research_category']) . '</td>
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