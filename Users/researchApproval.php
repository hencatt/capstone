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
                        <p>Review and vote on research submissions for events you're assigned to as a panel member.</p>
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
                        <div class="table-responsive"
                            style="background-color: white; padding: 10px; border-radius: 10px;">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">Event</th>
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

                                    // MODIFIED QUERY: Only show researches for events where this panel member is assigned
                                    $sql = "SELECT 
                                                r.*,
                                                a.announceTitle as event_title
                                            FROM research_tbl r
                                            INNER JOIN announcement_tbl a ON r.event_id = a.id
                                            INNER JOIN event_panel_tbl ep ON ep.eventId = r.event_id
                                            WHERE ep.panelId = ?
                                            ORDER BY r.date_submitted DESC";

                                    $stmt = $con->prepare($sql);
                                    $stmt->bind_param("i", $currentUserId);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if ($result->num_rows > 0) {

                                        $sql2 = "SELECT panel_id FROM votes_tbl WHERE panel_id = ? AND research_id = ?";
                                        $stmt2 = $con->prepare($sql2);

                                        $sql3 = "SELECT vote FROM votes_tbl WHERE panel_id = ? AND research_id = ?";
                                        $stmt3 = $con->prepare($sql3);

                                        while ($row = $result->fetch_assoc()) {

                                            echo '<tr data-category="' . htmlspecialchars($row['research_category']) . '">';

                                            // Event Name
                                            echo '<td style="text-align: center;">' . htmlspecialchars($row['event_title']) . '</td>';

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

                                                echo '<td style="text-align: center; ' . $voteStyle . '"><strong>' . $voteValue . '</strong></td>';

                                            } else {
                                                echo '<td style="text-align: center;"><span class="badge bg-warning text-dark">Not Voted Yet</span></td>';
                                            }

                                            echo '
                                                <td style="text-align: center;">' . htmlspecialchars($row['research_title']) . '</td>
                                                <td style="text-align: center;">' . htmlspecialchars($row['author']) .
                                                (!empty($row['co_author']) ? ', ' . htmlspecialchars($row['co_author']) : '') . '</td>
                                                <td style="text-align: center;">' . htmlspecialchars($row['date_started']) . '</td>
                                                <td style="text-align: center;">' . htmlspecialchars($row['research_category']) . '</td>
                                                <td style="text-align: center;">
                                                    <a href="researchDetails.php?id=' . htmlspecialchars($row['id']) . '&prev=Approval" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>';
                                        }

                                        $stmt2->close();
                                        $stmt3->close();
                                    } else {
                                        echo '<tr><td colspan="7" style="text-align: center;">
                                                <div class="alert alert-info" role="alert">
                                                    <i class="fas fa-info-circle"></i> No research submissions found for events you\'re assigned to.
                                                </div>
                                              </td></tr>';
                                    }

                                    $stmt->close();
                                    $con->close();
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
            let visibleCount = 0;
            rows.forEach(row => {
                if (row.dataset.category) {
                    if (row.dataset.category === category) {
                        row.style.display = "";
                        visibleCount++;
                    } else {
                        row.style.display = "none";
                    }
                }
            });

            // If no rows match the filter, you could optionally show a message
            // But the initial "no data" message will handle that
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