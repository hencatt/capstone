<?php
require_once 'includes.php';
require_once '../phpFunctions/email.php';
session_start();

checkUser($_SESSION['user_id']);
$user = getUser();
$currentUserId = $user['id'];
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];

$currentResearch = "";
$previousPage = "";
if (isset($_GET['id'])) {
    $currentResearch = $_GET['id'];
    $currentResearch = (int) $currentResearch;
}

if (isset($_GET['prev'])) {
    $previousPage = $_GET['prev'];
}

$con = con();
// MODIFIED QUERY: Include event information
$sql = "SELECT r.*, a.announceTitle as event_title, a.presentationDate 
        FROM research_tbl r
        LEFT JOIN announcement_tbl a ON r.event_id = a.id
        WHERE r.id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $currentResearch);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $researchId = htmlspecialchars($row['id']);
    $researchTitle = htmlspecialchars($row['research_title']);
    $researchDescription = htmlspecialchars($row['description']);
    $researchAuthors = htmlspecialchars($row['co_author']);
    $mainAuthor = htmlspecialchars($row['author']);
    $researchDateSubmitted = htmlspecialchars($row['date_submitted']);
    $agenda = htmlspecialchars($row['research_agenda']);
    $sdg = htmlspecialchars($row['research_sdg']);
    $grant = htmlspecialchars($row['research_grant']);
    $eventTitle = htmlspecialchars($row['event_title'] ?? 'No Event Assigned');
    $presentationDate = $row['presentationDate'] ? htmlspecialchars(date('M d, Y', strtotime($row['presentationDate']))) : 'N/A';

    $granted = false;

    if ($grant === "Yes") {
        $granted = true;
    }

    $file = $row['file'];
}

function redirectPage($researchId)
{
    header("Location: researchDetails.php?id=" . $researchId);
    exit;
}

function checkVoters($researchId, $panelId)
{
    $isVoted = false;

    $con = con();

    $sql = "SELECT panel_id, research_id 
        FROM votes_tbl 
        WHERE research_id = ? AND panel_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $researchId, $panelId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows >= 1) {
        $isVoted = true;
    } else {
        $isVoted = false;
    }

    return $isVoted;
}

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

    if ($approve_count > 1) {
        $sql2 = "UPDATE research_tbl SET status = ? WHERE id = ?";
        $stmt2 = $con->prepare($sql2);
        $stmt2->bind_param("si", $approve, $researchId);
        $stmt2->execute();
        sendResearchApprovalEmail($con, $researchId);

    } else if ($reject_count > 1) {
        $sql2 = "UPDATE research_tbl SET status = ? WHERE id = ?";
        $stmt2 = $con->prepare($sql2);
        $stmt2->bind_param("si", $reject, $researchId);
        $stmt2->execute();

        sendResearchRejectionEmail($con, $researchId);

    } else {
        $sql2 = "UPDATE research_tbl SET status = ? WHERE id = ?";
        $stmt2 = $con->prepare($sql2);
        $stmt2->bind_param("si", $pending, $researchId);
        $stmt2->execute();
    }
}

checkVotes($currentResearch);


if (isset($_POST['comment_send'])) {
    $currentDate = date('Y-m-d H:i:s');
    $comment = htmlspecialchars($_POST['comments']);
    $con = con();
    $sql = "INSERT INTO comments_tbl (comment, commentor_name, comment_datetime, research_id) VALUES (?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssi", $comment, $currentUser, $currentDate, $currentResearch);
    if ($stmt->execute()) {
        insertLog($currentUser, "Comment", date('Y-m-d H:i:s'));
        alertSuccess("Success", "Your comment was posted");
    }
    ;
    redirectPage($currentResearch);
}

if (isset($_POST['confirmBtnApprove'])) {
    $vote = "Approve";
    $voteName = $currentUser;
    $currentDateTime = date('Y-m-d H:i:s');

    $con = con();
    $sql = "INSERT INTO votes_tbl (vote, voter_name, voter_datetime, research_id, panel_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssii", $vote, $voteName, $currentDateTime, $currentResearch, $currentUserId);
    if ($stmt->execute()) {
        insertLog($currentUser, "Accepted"+$currentResearch+" (Research)", date('Y-m-d H:i:s'));
        alertSuccess("Voted", "You approved " . $researchTitle);
    }
    ;

    checkVotes($currentResearch);
    redirectPage($currentResearch);
}
;

if (isset($_POST['confirmBtnReject'])) {
    $vote = "Reject";
    $voteName = $currentUser;
    $currentDateTime = date('Y-m-d H:i:s');

    $con = con();
    $sql = "INSERT INTO votes_tbl (vote, voter_name, voter_datetime, research_id, panel_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssii", $vote, $voteName, $currentDateTime, $currentResearch, $currentUserId);
    if ($stmt->execute()) {
        insertLog($currentUser, "Rejected"+$currentResearch+" (Research)", date('Y-m-d H:i:s'));
        alertSuccess("Voted", "You rejected " . $researchTitle);
    };

    checkVotes($currentResearch);
    redirectPage($currentResearch);
}


;



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("Research Details") ?>
    <style>
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .info-card h6 {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .info-card p {
            font-size: 1.1rem;
            font-weight: 500;
            margin: 0;
        }

        .badge-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .custom-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .custom-badge:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .sdg-badge {
            background-color: white;
            color: black;
        }

        .agenda-badge {
            background-color: white;
            color: black;
        }

        .event-card {
            background: linear-gradient(135deg, #8a6fffff 0%, #f5576c 100%);
            border-radius: 15px;
            padding: 20px;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .grant-info-box {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .grant-status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .grant-status i {
            font-size: 1.2rem;
        }

        .status-granted {
            color: #38ef7d;
        }

        .status-not-granted {
            color: #ff6b6b;
        }
    </style>
</head>

<body>


    <!-- Left Sidebar -->

    <div class="row everything">
        <div class="col sidebar">
            <?php sidebar("researchDetails", $currentPosition, "researchDetails", $researchTitle) ?>
        </div>
        <!-- Main Contents -->

        <div class="col-10 mt-3 mainContent">
            <?php topbar($currentUser, $currentPosition, "researchDetails", $researchTitle, $previousPage) ?>

            <div id="contents">
                <div class="row">
                    <div class="col d-flex gap-3 align-items-center">
                        <h1><?= $researchTitle; ?></h1>
                        <span class="badge bg-secondary" style="font-size: 0.85rem;">
                            <i class="fas fa-calendar"></i> <?= $researchDateSubmitted; ?>
                        </span>
                    </div>
                    <div class="col d-flex justify-content-end align-items-center gap-3">

                        <div id="approvalBtn" class="gap-3">
                            <?php
                            if ($currentPosition === "Panel"): ?>
                                <button class="btn btn-success" name="approveBtn" id="approveBtn">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="btn btn-danger" name="rejectBtn" id="rejectBtn">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                                <?php
                            endif;
                            ?>
                        </div>

                        <?php
                        if ($currentPosition === "RET Chair" || $currentPosition === "Researcher"):
                            ?>
                            <button class="btn btn-outline-secondary" id="reSubmitPdf" name="reSubmitPd"
                                style="display: none;">
                                <i class="fas fa-redo"></i> Re-submit PDF
                            </button>
                            <?php
                        endif;
                        ?>
                        <button class="btn btn-outline-primary" id="viewPdf" name="viewPdf">
                            <i class="fas fa-file-pdf"></i> View PDF
                        </button>
                        <div id="pdfContainer" style="margin-top: 20px;"></div>
                    </div>
                </div>

                <!-- EVENT INFORMATION CARD -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="event-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 style="color: rgba(255, 255, 255, 0.9); font-size: 0.9rem; margin-bottom: 5px;">
                                        <i class="fas fa-calendar-alt"></i> Research Event
                                    </h6>
                                    <h4 style="margin: 0; font-weight: 600;"><?= $eventTitle ?></h4>
                                </div>
                                <div class="text-end">
                                    <h6 style="color: rgba(255, 255, 255, 0.9); font-size: 0.9rem; margin-bottom: 5px;">
                                        <i class="fas fa-presentation"></i> Presentation Date
                                    </h6>
                                    <h5 style="margin: 0;"><?= $presentationDate ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <?php
                if ($currentPosition === "RET Chair"):
                    ?>
                    <div class="row mt-3">
                        <div class="col d-flex flex-row align-items-center gap-3">
                            <button class="btn btn-primary" id="changeGrantStatus" name="changeGrantStatus">
                                <i class="fas fa-hand-holding-usd"></i> Change Grant Status
                            </button>
                            <button class="btn btn-outline-secondary" id="changeResubmissionStatus"
                                name="changeResubmissionStatus">
                                <i class="fas fa-folder-open"></i> Open Re-Submission
                            </button>
                        </div>
                    </div>
                    <?php
                endif;
                ?>

                <!-- SDG AND RESEARCH AGENDA -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="info-card">
                            <h6><i class="fas fa-leaf"></i> SUSTAINABLE DEVELOPMENT GOALS</h6>
                            <div class="badge-container">
                                <?php
                                $sdgs = explode(", ", $sdg);
                                foreach ($sdgs as $sdgItem):
                                    ?>
                                    <span class="custom-badge sdg-badge">
                                        <i class="fas fa-check-circle"></i> <?= trim($sdgItem) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card">
                            <h6><i class="fas fa-lightbulb"></i> NEUST RESEARCH AGENDA</h6>
                            <div class="badge-container">
                                <?php
                                $agendas = explode(", ", $agenda);
                                foreach ($agendas as $agendaItem):
                                    ?>
                                    <span class="custom-badge agenda-badge">
                                        <i class="fas fa-star"></i> <?= trim($agendaItem) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GRANT INFORMATION -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="grant-info-box">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="grant-status <?= $granted ? 'status-granted' : 'status-not-granted' ?>">
                                        <i class="fas fa-<?= $granted ? 'check-circle' : 'times-circle' ?>"></i>
                                        <div>
                                            <small class="text-muted d-block">Grant Status</small>
                                            <strong><?= $granted ? "Granted" : "Not Granted" ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="grant-status">
                                        <i class="fas fa-money-bill-wave text-success"></i>
                                        <div>
                                            <small class="text-muted d-block">Total Amount</small>
                                            <strong>
                                                <?php if ($grant === "No"): ?>
                                                    N/A
                                                <?php else: ?>
                                                    ₱
                                                    <?= number_format(htmlspecialchars($row['research_grant_times']) * 5000, 2) ?>
                                                <?php endif; ?>
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="grant-status">
                                        <i class="fas fa-sync-alt text-primary"></i>
                                        <div>
                                            <small class="text-muted d-block">Re-Submission Status</small>
                                            <strong>
                                                <?= ($row['research_resubmission_status'] === "Yes") ? "Open" : "Closed"; ?>
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DESCRIPTION -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div
                            style="background-color: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <h5 class="mb-3"><i class="fas fa-align-left"></i> Research Description</h5>
                            <p style="text-align: justify; line-height: 1.8;"><?= $researchDescription ?></p>
                        </div>
                    </div>
                </div>


                <div class="row mt-3 gap-5">
                    <div class="col d-flex flex-column"
                        style="background-color: white; padding: 25px; border-radius: 10px;">
                        <h5><i class="fas fa-comments"></i> Comments</h5>


                        <?php if ($currentPosition === "Panel") { ?>
                            <form method="POST">
                                <div class="row d-flex align-items-center">
                                    <div class="col-8 mt-4 d-flex flex-row gap-2 align-items-center"
                                        style="margin-left: 1.3rem">
                                        <textarea style="
                                            overflow: hidden;
                                            box-sizing: border-box;
                                            resize: none;
                                            border: 1px solid gray;
                                            padding: 10px;
                                            border-radius: 10px;
                                            width: 100%;" rows="1" cols="50" name="comments" id="comments"
                                            placeholder="Enter comment here..."></textarea>
                                        <button class="btn btn-outline-primary" id="comment_send" name="comment_send">
                                            <i class="fas fa-paper-plane"></i> Send
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <?php
                        }
                        ?>


                        <div class="row "
                            style="background-color: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <div class="col">

                                <!-- loop comments here -->
                                <?php
                                $con = con();
                                $sql = "SELECT c.comment_id, c.commentor_name, c.comment, c.comment_datetime
                                        FROM comments_tbl c
                                        JOIN research_tbl r ON c.research_id = r.id
                                        WHERE r.id = ?";

                                $stmt = $con->prepare($sql);
                                $stmt->bind_param("i", $currentResearch);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $rows = mysqli_num_rows($result);
                                if ($rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo '
                                <div style="border: solid 1px gray; margin-top: 1.3rem; padding:20px; border-radius: 10px">
                                    <div class="row d-flex flex-row">
                                        <div class="d-flex flex-row" style="width:max-content;">
                                         <span class="material-symbols-outlined">
                                                    person
                                                    </span>
                                            <h5 style="margin-left: 1rem">' . htmlspecialchars($row['commentor_name']) . '</h5>
                                        </div>
                                        <div class="col">
                                            <i class="blockquote-footer">' . htmlspecialchars($row['comment_datetime']) . '</i>
                                        </div>
                                    </div>
                                        <hr>
                                    <div class="row mt-1">
                                        <div class="col" style="overflow-wrap: anywhere; white-space: normal;
                                            display: -webkit-box;">
                                            ' . html_entity_decode($row['comment']) . '
                                        </div>
                                    </div>
                                    
                                </div>
                                ';
                                    }
                                } else {
                                    echo '<div class="row mt-1">
                                        <div class="col" style="text-align:center; overflow-wrap: anywhere; white-space: normal;
                                            display: -webkit-box;">
                                            <i>No comments yet.</i>
                                        </div>
                                    </div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-3 d-flex flex-column"
                        style="background-color: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-height:330px;">
                        <h5><i class="fas fa-users"></i> Authors</h5>
                        <div class="row">
                            <div class="col d-flex flex-column justify-content-center">
                                <ul>
                                    <?php
                                    echo "<li class='mt-4' style='list-style-type: none'> <span class='material-symbols-outlined'>
                                                person
                                                </span><span style='margin-left: 1rem'>" . htmlspecialchars($mainAuthor) . "</span></li>";
                                    if (!empty($researchAuthors)) {
                                        $researchAuthorsArray = explode(", ", $researchAuthors);
                                        foreach ($researchAuthorsArray as $coAuth) {
                                            if (!empty(trim($coAuth))) {
                                                echo "<li class='mt-3' style='list-style-type: none'> <span class='material-symbols-outlined'>
                                                        person
                                                        </span><span style='margin-left: 1rem'>" . htmlspecialchars($coAuth) . "</span></li>";
                                            }
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALS -->
    <div class="modalConfirmation" style="padding:20px; border: gray 1px solid; border-radius:10px;">
        <div class="modalConfirmation-content" style="width: 20%;">
            <div class="row">
                <div class="col">
                    <h6>Are you sure you want to do this?</h6>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col gap-3 d-flex justify-content-end">
                    <form method="POST">
                        <button class="btn btn-outline-danger" type="button" name="cancelBtn"
                            id="cancelBtn">Cancel</button>
                        <button class="btn btn-outline-success" name="confirmBtnApprove"
                            id="confirmBtnApprove">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modalConfirmationReject" style="padding:20px; border: gray 1px solid; border-radius:10px;">
        <div class="modalConfirmation-content" style="width: 20%;">
            <div class="row">
                <div class="col">
                    <h6>Are you sure you want to do this?</h6>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col gap-3 d-flex justify-content-end">
                    <form method="POST">
                        <button class="btn btn-outline-danger" type="button" name="cancelBtnReject"
                            id="cancelBtnReject">Cancel</button>
                        <button class="btn btn-outline-success" name="confirmBtnReject"
                            id="confirmBtnApprove">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include('../phpFunctions/alerts.php'); ?>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            console.log("aaaaaaaaaaaaaaaaaaaaa");
            const grantBtn = document.getElementById("changeGrantStatus");
            const resubmitBtn = document.getElementById("changeResubmissionStatus");

            // trigger modals
            if (grantBtn) grantBtn.addEventListener("click", openGrantModal);
            if (resubmitBtn) resubmitBtn.addEventListener("click", openReSubmitModal);


            // ------------ GRANT MODAL ------------------
            function openGrantModal() {
                const modal = document.createElement("div");
                modal.className = "modal fade show";
                modal.style.display = "block";
                modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content p-3">
                <h5>Change Grant Status</h5>
                <label class="mt-2">Grant?</label>
                <input type="checkbox" id="grantSwitch" class="form-check-input ms-2">

                <div id="grantTimesDiv" class="mt-3" style="display:none;">
                    <label>How many times granted?</label>
                    <input type="number" id="grantCount" class="form-control" min="1" value="1">
                    <small class="text-muted">₱5,000 per grant</small>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary" id="cancelGrant">Cancel</button>
                    <button class="btn btn-primary" id="saveGrant">Save</button>
                </div>
            </div>
        </div>
    `;
                document.body.appendChild(modal);

                const grantSwitch = document.getElementById("grantSwitch");
                const grantTimesDiv = document.getElementById("grantTimesDiv");

                grantSwitch.addEventListener("change", () => {
                    grantTimesDiv.style.display = grantSwitch.checked ? "block" : "none";
                });

                document.getElementById("cancelGrant").onclick = () => modal.remove();

                document.getElementById("saveGrant").onclick = () => {
                    const grant = grantSwitch.checked ? "Yes" : "No";
                    const times = grantSwitch.checked ? document.getElementById("grantCount").value : 0;

                    fetch("../phpFunctions/updateResearch.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            action: "grant",
                            research_id: <?= json_encode($researchId ?? null) ?>,
                            research_grant: grant,
                            research_grant_times: times
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            alert(data.message);
                            modal.remove();
                            location.reload();
                        });
                };
            }

            // ------------ RESUBMISSION MODAL ------------------
            function openReSubmitModal() {
                const modal = document.createElement("div");
                modal.className = "modal fade show";
                modal.style.display = "block";
                modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content p-3">
                <h5>Re-Submission Status</h5>
                
                <label class="mt-2">Allow Re-Submission?</label>
                <input type="checkbox" id="reSubmitSwitch" class="form-check-input ms-2">

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary" id="cancelRe">Cancel</button>
                    <button class="btn btn-primary" id="saveRe">Save</button>
                </div>
            </div>
        </div>
    `;

                document.body.appendChild(modal);

                document.getElementById("cancelRe").onclick = () => modal.remove();

                document.getElementById("saveRe").onclick = () => {
                    const status = document.getElementById("reSubmitSwitch").checked ? "Yes" : "No";

                    fetch("../phpFunctions/updateResearch.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            action: "resubmit",
                            research_id: <?= json_encode($researchId ?? null) ?>,
                            research_resubmission_status: status
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            alert(data.message);
                            modal.remove();

                            // toggle visibility instantly without reload
                            const box = document.getElementById("reSubmitPdf");
                            if (box) box.style.display = (status === "Yes") ? "block" : "none";

                            location.reload();
                        });
                };
            }

            const status = <?php echo json_encode($row['research_resubmission_status'] ?? null); ?>;
            const section = document.getElementById("reSubmitPdf");
            if (section) section.style.display = (status === "Yes") ? "block" : "none";

            function displayApproval() {
                const currentPos = <?php echo json_encode($currentPosition ?? null); ?>;
                const isVoted = <?php echo checkVoters($currentResearch, $currentUserId) ? 'true' : 'false'; ?>;

                if (currentPos === "Panel") {
                    const approvalBtn = document.getElementById("approvalBtn");
                    approvalBtn.style.display = isVoted ? "none" : "flex";
                }
            }

            displayApproval();

            const commentArea = document.getElementById("comments");
            const commentBtn = document.getElementById("comment_send");
            const approveBtn = document.getElementById("approveBtn");
            const rejectBtn = document.getElementById("rejectBtn");
            const cancelBtn = document.getElementById("cancelBtn");
            const cancelBtnReject = document.getElementById("cancelBtnReject")
            const confirmModal = document.querySelectorAll(".modalConfirmation");
            const confirmModalReject = document.querySelectorAll(".modalConfirmationReject");
            const pos = <?php echo json_encode($currentPosition ?? null); ?>

            console.log("Panel Line reached");


            if (pos === "Panel") {
                commentArea.addEventListener("input", function () {
                    this.style.height = "auto";
                    this.style.height = this.scrollHeight + "px";
                });

                commentArea.addEventListener("keydown", (e) => {
                    if (e.key === "Enter" && !e.shiftKey) {
                        e.preventDefault();
                        commentBtn.click();
                    }
                });


                approveBtn.addEventListener("click", function () {
                    console.log("clicked approve")
                    confirmModal.forEach((modal) => {
                        modal.classList.add("open");
                    });
                });

                rejectBtn.addEventListener("click", function () {
                    confirmModalReject.forEach((modal) => {
                        modal.classList.add("open");
                    });
                });

                cancelBtn.addEventListener("click", function () {
                    confirmModal.forEach((modal) => {
                        modal.classList.remove("open");
                    });
                });

                cancelBtnReject.addEventListener("click", function () {
                    confirmModalReject.forEach((modal) => {
                        modal.classList.remove("open");
                    });
                });
            }



            // SHOW PDF
            document.getElementById("viewPdf").addEventListener("click", function () {


                <?php
                $filePath = $file;
                $fileData = file_exists($filePath) ? file_get_contents($filePath) : null;
                ?>

                const base64Data = <?php echo json_encode($fileData ? base64_encode($fileData) : null); ?>;

                if (!base64Data) {
                    alert("PDF file not found!");
                    return;
                }

                // Decode base64 → binary
                const byteCharacters = atob(base64Data);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], { type: "application/pdf" });
                const pdfURL = URL.createObjectURL(blob);

                // 🧠 Create modal overlay
                const overlay = document.createElement("div");
                overlay.style.position = "fixed";
                overlay.style.top = "0";
                overlay.style.left = "0";
                overlay.style.width = "100%";
                overlay.style.height = "100%";
                overlay.style.backgroundColor = "rgba(0,0,0,0.6)";
                overlay.style.backdropFilter = "blur(4px)";
                overlay.style.display = "flex";
                overlay.style.justifyContent = "center";
                overlay.style.alignItems = "center";
                overlay.style.zIndex = "9999";

                // 🪟 Modal box
                const modal = document.createElement("div");
                modal.style.width = "80%";
                modal.style.height = "85%";
                modal.style.backgroundColor = "#fff";
                modal.style.borderRadius = "10px";
                modal.style.overflow = "hidden";
                modal.style.position = "relative";
                modal.style.boxShadow = "0 0 20px rgba(0,0,0,0.3)";
                modal.style.display = "flex";
                modal.style.flexDirection = "column";

                // ❌ Close button
                const closeBtn = document.createElement("button");
                closeBtn.textContent = "×";
                closeBtn.style.position = "absolute";
                closeBtn.style.top = "10px";
                closeBtn.style.right = "15px";
                closeBtn.style.border = "none";
                closeBtn.style.background = "transparent";
                closeBtn.style.fontSize = "28px";
                closeBtn.style.cursor = "pointer";
                closeBtn.style.zIndex = "10";
                closeBtn.addEventListener("click", () => {
                    URL.revokeObjectURL(pdfURL);
                    overlay.remove();
                });

                // 🧾 PDF viewer inside modal
                const pdfViewer = document.createElement("object");
                pdfViewer.data = pdfURL;
                pdfViewer.type = "application/pdf";
                pdfViewer.width = "100%";
                pdfViewer.height = "100%";
                pdfViewer.style.border = "none";
                pdfViewer.innerHTML = "<div style='padding:20px;text-align:center;'>No PDF viewer available</div>";

                modal.appendChild(closeBtn);
                modal.appendChild(pdfViewer);
                overlay.appendChild(modal);
                document.body.appendChild(overlay);

                // ✨ Optional: click outside to close
                overlay.addEventListener("click", (e) => {
                    if (e.target === overlay) {
                        URL.revokeObjectURL(pdfURL);
                        overlay.remove();
                    }
                });
            });

        });
    </script>
</body>

</html>