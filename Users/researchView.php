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
                <!-- TOGGLE START -->
<?php if ($currentPosition !== "Researcher"): ?>
<div class="row mt-4">
    <div class="col">
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <label class="btn btn-secondary active" id="proposal_btn">
                <input type="radio" name="toggleOptions" autocomplete="off" value="Proposal" checked> Proposal
            </label>
            <label class="btn btn-secondary" id="completed_btn">
                <input type="radio" name="toggleOptions" autocomplete="off" value="Completed"> Completed
            </label>
        </div>
    </div>
</div>
<?php endif; ?>
<!-- TOGGLE END -->

<div class="row mt-3 flex flex-col gap-3">
    <div class="col" style="background-color: white; padding: 10px; border-radius: 10px;">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Research Title</th>
                    <?php if ($currentPosition === "RET Chair"): ?>
                        <th>Grant</th>
                    <?php endif; ?>
                    <th>Date Submitted</th>
                    <th>Status</th>
                    <th>Category</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="researchTableBody">
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
                        $statusColor = match($row['status']) {
                            "Approved" => "color: green;",
                            "Rejected" => "color: red;",
                            default => "color: orange;",
                        };

                        echo '<tr data-category="' . htmlspecialchars($row['research_category']) . '">';
                        echo '<td>' . htmlspecialchars($row['research_title']) . '</td>';

                        if ($currentPosition === "RET Chair") {
                            echo '<td>' . htmlspecialchars($row['research_grant']) . '</td>';
                        }

                        echo '<td>' . htmlspecialchars($row['date_submitted']) . '</td>';
                        echo '<td style="' . $statusColor . '">' . htmlspecialchars($row['status']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['research_category']) . '</td>';
                        echo '<td><a href="researchDetails.php?id=' . htmlspecialchars($row['id']) . '&prev=View Researches" style="color: #5f8cecff;">View More</a></td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script defer>
document.addEventListener("DOMContentLoaded", () => {
    const toggleButtons = document.querySelectorAll('input[name="toggleOptions"]');
    const rows = document.querySelectorAll('#researchTableBody tr');

    function filterTable(category) {
        rows.forEach(row => {
            const rowCategory = row.getAttribute('data-category');
            row.style.display = (rowCategory === category) ? '' : 'none';
        });
    }

    // Default: show only Proposal
    filterTable("Proposal");

    toggleButtons.forEach(btn => {
        btn.addEventListener('change', () => {
            filterTable(btn.value);
        });
    });
});
</script>
</body>

</html>