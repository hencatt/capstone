<?php
require_once 'includes.php';
session_start();

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];


checkUser($_SESSION['user_id']);

$previousPage = $_GET['prev'];
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
    

    <div class="row everything">
        <div class="col sidebar">
            <?php echo sidebar("eventDetails", $currentPosition, "eventDetails", $eventTitle) ?>
        </div>

        <!-- MAIN CONTENTS -->
        <div class="col-10 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", $currentPosition, "eventDetails", $eventTitle, $previousPage) ?>
            <div id="contents">
                <div class="row mt-5">
                    <div class="col">
                        <h1><?= $eventTitle ?></h1>
                    </div>
                    <?php if ($eventCategory === "Research Event") {
                        echo <<<EOD
                            <div class="col d-flex align-items-center justify-content-start" style="color: white;">
                                <h6 style="background-color: #ef7dd5ff; padding:10px; border-radius: 10px;"><b>Presentation on:</b> <i>$presentationMonth - $presentationDay - $presentationYear<i></h6>
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
                    <div class="col" style="background-color: white;
                    padding: 10px 15px;
                    border-radius: 10px;
                    ">
                        <p><?= $eventDesc ?></p>
                    </div>
                </div>
                <div class="row mt-3 gap-5">
                    <?php if ($eventCategory === "Research Event") {
                        echo <<<EOD
                        <div class="col flex flex-col text-center" style= "
                        background-color: white;
                        width: max-content;
                        height: max-content;
                        border-radius: 10px;
                        padding: 10px;
                        "
                        >
                            <h6>Proposal Date</h6>
                            <label>$eventProposalMonth - $eventProposalDay - $eventProposalYear</label>
                        </div>
                        <div class="col flex flex-col text-center" style= "
                        background-color: white;
                        width: max-content;
                        height: max-content;
                        border-radius: 10px;
                        padding: 10px;
                        "
                        >
                            <h6>Acceptance Date</h6>
                            <label>$acceptanceMonth - $acceptanceDay - $acceptanceYear</label>
                        </div>
                        <div class="col flex flex-col text-center" style= "
                        background-color: white;
                        width: max-content;
                        height: max-content;
                        border-radius: 10px;
                        padding: 10px;
                        "
                        >
                            <h6>Presentation Date</h6>
                            <label>$presentationMonth - $presentationDay - $presentationYear</label>
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