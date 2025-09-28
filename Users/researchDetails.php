<?php
require_once 'includes.php';

session_start();

checkUser($_SESSION['user_id'], $_SESSION['user_username']);
$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];

if (isset($_GET['id'])) {
    $currentResearch = $_GET['id'];
    $currentResearch = (int) $currentResearch;
}

$con = con();
$sql = "SELECT * FROM research_tbl WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $currentResearch);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $researchTitle = htmlspecialchars($row['research_title']);
    $researchDescription = htmlspecialchars($row['description']);
    $researchAuthors = htmlspecialchars($row['co_author']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("Research Details") ?>
</head>

<body>
    <?= addDelay("researchDetails", $currentUser, $currentPosition); ?>

    <!-- Left Sidebar -->
    <div class="row everything">
        <div class="col sidebar">
            <?php sidebar("researchDetails", $currentPosition) ?>
        </div>
        <!-- Main Contents -->

        <div class="col-10 mt-3 mainContent">
            <?php topbar($currentUser, $currentPosition, "researchDetails", $researchTitle) ?>

            <div id="contents">
                <div class="row mt-5">
                    <div class="col d-flex flex-row gap-3">

                        <h1><?= $researchTitle; ?></h1>
                        <!-- TODO DATE SUBMITTED -->
                        <figcaption class="blockquote-footer align-self-end">##date submitted here</figcaption>
                    </div>
                </div>
                <div class="row mt-3" style="background-color: white; padding: 10px; border-radius: 10px;">
                    <div class="col">
                        <p><?= $researchDescription ?></p>
                    </div>
                </div>
                <div class="row mt-3 gap-5">
                    <div class="col" style="background-color: white; padding: 10px; border-radius: 10px;">
                        <h5><i>Comments</i></h5>
                    </div>
                    <div class="col-3" style="background-color: white; padding: 10px; border-radius: 10px;">
                        <h5>Authors</h5>
                        <?= $researchAuthors ?> 
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>