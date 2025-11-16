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
            <?php sidebar("approval", $currentPosition); ?>
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
                        <div class="btn-group btn-group-toggle">
                            <label class="btn btn-secondary active">
                                <input type="radio" name="toggleOptions" value="Proposal" autocomplete="off" checked>
                                Proposal
                            </label>

                            <label class="btn btn-secondary">
                                <input type="radio" name="toggleOptions" value="Completed" autocomplete="off">
                                Completed
                            </label>
                        </div>
                    </div>
                </div>
                <!-- TOGGLES END -->

                <!-- TABLE -->
                <div class="row mt-4">
                    <div class="col">
                        <div class="table-responsive" style="background-color: white; padding: 10px; border-radius: 10px;">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">Vote Status</th>
                                        <th style="text-align: center;">Title</th>
                                        <th style="text-align: center;">Authors</th>
                                        <th style="text-align: center;">Date</th>
                                        <th style="text-align: center;">Category</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody id="approvalTableBody">
                                    <?php
                                    $con = con();
                                    $sql = "SELECT * FROM research_tbl";
                                    $result = $con->query($sql);

                                    if ($result->num_rows > 0) {

                                        $sql2 = "SELECT panel_id FROM votes_tbl WHERE panel_id = ? AND research_id = ?";
                                        $stmt2 = $con->prepare($sql2);

                                        $sql3 = "SELECT vote FROM votes_tbl WHERE panel_id = ? AND research_id = ?";
                                        $stmt3 = $con->prepare($sql3);

                                        while ($row = $result->fetch_assoc()) {

                                            echo '<tr data-category="' . htmlspecialchars($row['research_category']) . '">';

                                            // Check if user already voted
                                            $stmt2->bind_param("ii", $currentUserId, $row['id']);
                                            $stmt2->execute();
                                            $stmt2->store_result();

                                            if ($stmt2->num_rows >= 1) {

                                                // Fetch vote
                                                $stmt3->bind_param("ii", $currentUserId, $row['id']);
                                                $stmt3->execute();
                                                $voteResult = $stmt3->get_result();
                                                $voteRow = $voteResult->fetch_assoc();

                                                $voteValue = $voteRow['vote'];
                                                $voteStyle = ($voteValue === "Reject") ? "color:red;" : "color:green;";
                                                $voteValue = ($voteValue === "Reject") ? "Rejected" : "Approved";

                                                echo '<td style="text-align: center; ' . $voteStyle . '">' . $voteValue . '</td>';

                                            } else {
                                                echo '<td style="text-align: center;">Not Voted Yet</td>';
                                            }

                                            echo '
                                                <td style="text-align: center;">' . htmlspecialchars($row['research_title']) . '</td>
                                                <td style="text-align: center;">' . htmlspecialchars($row['author']) . ', ' . htmlspecialchars($row['co_author']) . '</td>
                                                <td style="text-align: center;">' . htmlspecialchars($row['date_started']) . '</td>
                                                <td style="text-align: center;">' . htmlspecialchars($row['research_category']) . '</td>
                                                <td style="text-align: center;">
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

<!-- TOGGLE SCRIPT -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const toggleButtons = document.querySelectorAll('input[name="toggleOptions"]');
    const rows = document.querySelectorAll('#approvalTableBody tr');

    function filterTable(category) {
        rows.forEach(row => {
            row.style.display = (row.dataset.category === category) ? "" : "none";
        });
    }

    // Default view: Proposal
    filterTable("Proposal");

    toggleButtons.forEach(btn => {
        btn.addEventListener("change", () => {
            filterTable(btn.value);
        });
    });
});
</script>

</html>
