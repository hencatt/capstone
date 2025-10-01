<?php
require_once 'includes.php';

session_start();

checkUser($_SESSION['user_id'], $_SESSION['user_username']);
$user = getUser();
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
    $mainAuthor = htmlspecialchars($row['author']);
    $researchDateSubmitted = htmlspecialchars($row['date_submitted']);
    $agenda = htmlspecialchars($row['research_agenda']);
    $sdg = htmlspecialchars($row['research_sdg']);
}

if (isset($_POST['comment_send'])) {
    $currentDate = date('Y-m-d H:i:s');
    $comment = htmlspecialchars($_POST['comments']);
    $con = con();
    $sql = "INSERT INTO comments_tbl (comment, commentor_name, comment_datetime, research_id) VALUES (?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssi", $comment, $currentUser, $currentDate, $currentResearch);
    if ($stmt->execute()) {
        echo "<script>alert('Comment Posted!')</script>";
    }
    ;

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
            <?php topbar($currentUser, $currentPosition, "researchDetails", $researchTitle, $previousPage) ?>

            <div id="contents">
                <div class="row mt-5">
                    <div class="col d-flex flex-row gap-3">

                        <h1><?= $researchTitle; ?></h1>
                        <!-- TODO DATE SUBMITTED -->
                        <figcaption class="blockquote-footer align-self-end"><?= $researchDateSubmitted; ?></figcaption>
                    </div>
                    <div class="col d-flex justify-content-end gap-3">
                        <?php
                        if ($currentPosition === "Panel") {
                            echo '
                        <button class="btn btn-success">Approve</button>
                        <button class="btn btn-danger">Reject</button>
                        ';
                        } ?>
                        <button class="btn btn-outline-primary">View PDF</button>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-7" style="background-color: white; padding: 10px; border-radius: 10px;">
                        <p><?= $researchDescription ?></p>
                    </div>

                    <div class="col vertical-align: middle;">

                        <div class="row">
                            <div class="col" style="text-align: center;"><i>NEUST Research Agenda</i>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col" style="text-align: center;"><b><?= $agenda ?></b>
                            </div>
                        </div>

                    </div>

                    <div class="col vertical-align: middle;">

                        <div class="row">
                            <div class="col" style="text-align: center;"><i>Sustainable Development Goals</i>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col" style="text-align: center;"><b><?= $sdg ?></b>
                            </div>
                        </div>

                    </div>


                </div>
                <div class="row mt-3 gap-5">
                    <div class="col gap-3 d-flex flex-column"
                        style="background-color: white; padding: 25px; border-radius: 10px;">
                        <h5><i>Comments</i></h5>
                        <?php if ($currentPosition === "Panel") {
                            echo '
                            <form method="POST">
                            <div class="row d-flex align-items-center">
                            <div class="col-8 mt-4 d-flex flex-row gap-2 align-items-center" style="margin-left: 1.3rem">
                            <textarea 
                            style="
                            overflow: hidden;
                            box-sizing: border-box;
                            resize: none;
                            border: 1px solid gray;
                            padding: 10px;
                            border-radius: 10px;
                            width: 100%;"
                            rows="1" cols="50"  name="comments" id="comments" placeholder="Enter comment here..."
                            ></textarea>
                                        <button class="btn btn-outline-primary" id="comment_send"
                                            name="comment_send">send</button>
                                            </div>
                                            </div>
                                            </form>
                            ';
                        }
                        ?>


                        <div class="row " style="padding: 20px; border-radius: 10px;">
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
                                    <div class="row mt-3 d-flex flex-row">
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
                                    <div class="row mt-1">
                                        <div class="col" style="overflow-wrap: anywhere; white-space: normal;
                                            display: -webkit-box;">
                                            ' . htmlspecialchars($row['comment']) . '
                                        </div>
                                    </div>
                                    
                                </div>
                                ';
                                    }
                                }else{
                                  echo  '<div class="row mt-1">
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
                        style="background-color: white; padding: 25px; border-radius: 10px;">
                        <h5>Authors</h5>
                        <div class="row">
                            <div class="col d-flex flex-column justify-content-center">
                                <ul>
                                    <?php
                                    echo "<li class='mt-4' style='list-style-type: none'> <span class='material-symbols-outlined'>
                                                person
                                                </span><span style='margin-left: 1rem'>" . htmlspecialchars($mainAuthor) . "</span></li>";
                                    $researchAuthorsArray = explode(", ", $researchAuthors);
                                    foreach ($researchAuthorsArray as $coAuth) {
                                        echo "<li class='mt-3' style='list-style-type: none'> <span class='material-symbols-outlined'>
                                                    person
                                                    </span><span style='margin-left: 1rem'>" . htmlspecialchars($coAuth) . "</span></li>";
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

    <script>
        const commentArea = document.getElementById("comments");
        const commentBtn = document.getElementById("comment_send")

        commentArea.addEventListener("input", function () {
            this.style.height = "auto";
            this.style.height = this.scrollHeight + "px";
        });
        
        commentArea.addEventListener("keydown", (e) => {
            if(e.key === "Enter" && !e.shiftKey){
                e.preventDefault();
                commentBtn.click();
            }
        });
    </script>

</body>

</html>