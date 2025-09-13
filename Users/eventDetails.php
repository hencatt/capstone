<?php
require_once 'includes.php';
session_start();

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];


checkUser($_SESSION['user_id'], $_SESSION['user_username']);

$eventId = $_GET['id'];
$con = con();
$sql = "SELECT id, 
        announceTitle, 
        announceDesc, 
        DAY(announceDate) AS day,
        MONTH(announceDate) AS month,
        YEAR(announceDate) AS year, 
        category, 
        DAY(proposalDate) AS proposalDay, 
        MONTH(proposalDate) AS proposalMonth, 
        YEAR(proposalDate) AS proposalYear, 
        DAY(acceptanceDate) AS acceptanceDay, 
        MONTH(acceptanceDate) AS acceptanceMonth, 
        YEAR(acceptanceDate) AS acceptanceYear, 
        DAY(presentationDate) AS presentationDay, 
        MONTH(presentationDate) AS presentationMonth, 
        YEAR(presentationDate) AS presentationYear
        FROM announcement_tbl
        WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $eventId);
if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $eventTitle = htmlspecialchars($row['announceTitle']);
        $eventDesc = htmlspecialchars($row['announceDesc']);
        $eventDay = htmlspecialchars($row['day']);
        $eventMonth = htmlspecialchars($row['month']);
        $eventYear = htmlspecialchars($row['year']);
        $eventCategory = htmlspecialchars($row['category']);
        $eventProposalDay = htmlspecialchars($row['proposalDay']);
        $eventProposalMonth = htmlspecialchars($row['proposalMonth']);
        $eventProposalYear = htmlspecialchars($row['proposalYear']);
        $acceptanceDay = htmlspecialchars($row['acceptanceDay']);
        $acceptanceMonth = htmlspecialchars($row['acceptanceMonth']);
        $acceptanceYear = htmlspecialchars($row['acceptanceYear']);
        $presentationDay = htmlspecialchars($row['presentationDay']);
        $presentationMonth = htmlspecialchars($row['presentationMonth']);
        $presentationYear = htmlspecialchars($row['presentationYear']);
    } else {
        echo "Event no found";
    }
} else {
    echo "Please refresh page";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("$eventTitle") ?>
</head>

<body>
    <?= addDelay("eventDetails", $currentUser, $currentPosition) ?>

    <div class="row everything">
        <div class="col sidebar">
            <?php echo sidebar("events", $currentPosition) ?>
        </div>

        <!-- MAIN CONTENTS -->
        <div class="col-10 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", $currentPosition, "eventDetails", $eventTitle) ?>
            <div id="contents">
                <div class="row mt-5">
                    <div class="col">
                        <h1><?= $eventTitle ?></h1>
                    </div>
                    <?php if ($eventCategory === "Research Event") {
                        echo <<<EOD
                            <div class="col d-flex align-items-center justify-content-start" style="color: white;">
                                <h6 style="background-color: #fd89e2ff; padding:10px; border-radius: 10px;">##presentationDateHere</h6>
                            </div>
                        EOD;
                    } else {
                        echo <<<EOD
                        <div class="col">
                        </div>
                        EOD;
                    }
                    ?>
                </div>
                <div class="row mt-3">
                    <div class="col"
                        style="background-color: white;
                    padding: 10px 15px;
                    border-radius: 10px;
                    ">
                        <p><?= $eventDesc ?></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <?php if ($eventCategory === "Research Event") {
                        echo <<<EOD
                        <div class="col">
                            ##DATES HERE
                        </div>
                        <div class="col">
                            ##DATES HERE
                        </div>
                        <div class="col">
                            ##DATES HERE
                        </div>
                        EOD;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>