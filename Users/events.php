<?php
require_once 'includes.php';

session_start();

if (!isset($_SESSION['user_department']) || !isset($_SESSION['user_campus'])) {
    die("Error: Department or Campus not set in session.");
}

$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];


checkUser($_SESSION['user_id'], $_SESSION['user_username']);

if (isset($_POST['editSaveBtn'])) {
    $id = htmlspecialchars($_POST['announceID']);
    $newTitle = htmlspecialchars_decode($_POST['inputNewTitle']);
    $newDescription = htmlspecialchars_decode($_POST['inputNewDescription']);
    $newDate = htmlspecialchars($_POST['inputNewDate']);

    $changes = array();
    $addChanges = array();

    if ($newTitle !== "") {
        $changes[] = "announceTitle = ?";
        $addChanges[] = $newTitle;
    }

    if ($newDescription !== "") {
        $changes[] = "announceDesc = ?";
        $addChanges[] = $newDescription;
    }

    if ($newDate !== "") {
        $changes[] = "announceDate = ?";
        $addChanges[] = $newDate;
    }

    if (!empty($changes)) {
        $sql = 'UPDATE announcement_tbl SET ' . implode(", ", $changes) . ' WHERE id = ?';
        $addChanges[] = $id;

        $stmt = $con->prepare($sql);

        // Generate types (assume strings for title/desc/date and int for id)
        $types = str_repeat("s", count($addChanges) - 1) . "i";

        // Bind parameters dynamically
        $stmt->bind_param($types, ...$addChanges);

        if ($stmt->execute()) {
            insertLog($currentUser, "Updated an Announcement", date('Y-m-d H:i:s'));
            alertSuccess("Success", "Saved Changes");
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        alertError("Error", "No data to change");
    }
}

if (isset($_POST["deleteBtn"])) {
    $id = htmlspecialchars($_POST["announceID"]);

    $sql = "DELETE FROM announcement_tbl WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        insertLog($currentUser, "Deleted an announcement", date("Y-m-d H:i:s"));
        alertSuccess("Deleted", "Deleted Successfully");
    } else {
        alertError("Error", "Error deleting event");
    }
    $stmt->close();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("Events") ?>
</head>

<body>



    <div class="row everything">
        <div class="col sidebar">
            <?php echo sidebar("events", $currentPosition) ?>
        </div>

        <!-- MAIN CONTENTS -->
        <div class="col-10 mt-lg-3 mainContent">
            <?php echo topbar("$currentUser", $currentPosition, "events") ?>
            <div id="contents">
                <div class="row mt-4">
                    <div class="col">
                        <h1>Events <span class="material-symbols-outlined">
                                event
                            </span></h1>
                    </div>
                    <div class="col-auto d-flex align-items-center justify-content-end gap-2">
                        <select name="inputCategory" id="inputCategory" class="form-select" style="width: 200px;">
                            <option value="all_category" default>All Category</option>
                            <option value="Holiday">Holiday</option>
                            <option value="Event">Event</option>
                            <option value="Research Event">Research</option>
                        </select>
                        <button class="btn btn-primary" id="createAnnouncementBtn" name="createAnnouncementBtn"><a
                                href="announcement.php" style="text-decoration: none; color:white;">Create
                                Announcement</a></button>
                        <button class="btn btn-outline-secondary" id="editAnnouncementBtn"
                            name="editAnnouncementBtn">Edit</button>
                        <input type="hidden" id="editBtnTrigger">
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <h6>All Events</h6>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col" style="border-radius: 10px;">
                        <?php
                        $currentDate = date("Y-m-d");
                        $currentDay = date("d");
                        $sql = "SELECT 
                            id,
                            announceDate, 
                            announceTitle,
                            announceDesc,
                            category,
                            DAY(announceDate) AS day,
                            MONTH(announceDate) AS month,
                            YEAR(announceDate) AS year
                            FROM announcement_tbl
                            WHERE announceDate > '$currentDate'
                            ORDER BY announceDate ASC;
                            ";
                        $stmt = $con->prepare($sql);
                        if ($stmt->execute()) {
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                                $colors = array("#d166cf", "#d16698", "#9266d1", "#d1bf66", "#d16666", "#6674d1", "#cb66d1");
                                $i = 0;
                                $j = 0;
                                $fontColor = "white";
                                $btnId = "modifyBtn" . $i;
                                while ($row = $result->fetch_assoc()) {
                                    $announceId = $row["id"];
                                    $oldDate = htmlspecialchars($row["announceDate"]);
                                    $oldTitle = htmlspecialchars($row["announceTitle"]);
                                    $oldDesc = htmlspecialchars($row["announceDesc"]);
                                    $oldDateMonth = htmlspecialchars($row["month"]);
                                    $oldDateYear = htmlspecialchars($row["year"]);
                                    $oldDateDay = htmlspecialchars($row["day"]);
                                    $oldCategory = htmlspecialchars($row["category"]);

                                    $categoryBg = "";
                                    switch ($row["category"]) {
                                        case "Holiday":
                                            $categoryBg = "#f5e896ff";
                                            break;
                                        case "Event":
                                            $categoryBg = "#cf8ff2ff";
                                            break;
                                        case "Research Event":
                                            $categoryBg = "#b799f9ff";
                                            break;
                                    }

                                    $calendarColor = $colors[$i];
                                    $announceMonth = '';
                                    $totalNumberOfRows = $result->num_rows;
                                    $editDeleteId = "modifyBtn" . $j;
                                    switch ((int) ($row['month'])) {
                                        case 1:
                                            $announceMonth = 'January';
                                            break;
                                        case 2:
                                            $announceMonth = 'February';
                                            break;
                                        case 3:
                                            $announceMonth = 'March';
                                            break;
                                        case 4:
                                            $announceMonth = 'April';
                                            break;
                                        case 5:
                                            $announceMonth = 'May';
                                            break;
                                        case 6:
                                            $announceMonth = 'June';
                                            break;
                                        case 7:
                                            $announceMonth = 'July';
                                            break;
                                        case 8:
                                            $announceMonth = 'August';
                                            break;
                                        case 9:
                                            $announceMonth = 'September';
                                            break;
                                        case 10:
                                            $announceMonth = 'October';
                                            break;
                                        case 11:
                                            $announceMonth = 'November';
                                            break;
                                        case 12:
                                            $announceMonth = 'December';
                                            break;
                                    }

                                    $calendarSizeWidth = '100px';

                                    echo "
                                            <div class='row mb-3 gap-5 eventRow'>
                                                <div class='col-1'>
                                                        <div style='display: flex; flex-direction: column; justify-content:center; align-items: center;'>
                                                        <div
                                                        style='
                                                        background-color: $calendarColor;
                                                        width: $calendarSizeWidth;
                                                        display: flex;
                                                        justify-content: center;
                                                        align-items: center;
                                                        border: solid black 1px;
                                                        color: $fontColor;
                                                        border-radius: 10px 10px 0% 0%;
                                                        '>" . $row['year'] . "
                                                        </div>
                                                        <div
                                                        style='height:90px;
                                                        width: $calendarSizeWidth;
                                                        background-color:$calendarColor;
                                                        color:$fontColor;
                                                        display:flex;
                                                        flex-direction: column;
                                                        align-items: center;
                                                        justify-content: center;
                                                        border: solid black 1px;
                                                        border-radius: 0px 0px 10px 10px;
                                                        '>" . "<h3>" . $row['day'] . "</h3>" . "<h6>" . $announceMonth . "</h6>" .
                                        "</div>" .
                                        "</div>
                                                </div>
                                                <div class='col d-flex flex-column gap-1'>
                                                    <h4 style='vertical-align: middle; font-weight:bold;'>" . htmlspecialchars($row['announceTitle']) . "</h4>
                                                    <div style='vertical-align: middle;'>" . htmlspecialchars($row['announceDesc']) . "</div>
                                                    <span style='
                                                    font-size: .8rem;
                                                    background-color: $categoryBg;
                                                    padding: 5px 10px 5px 10px;
                                                    border-radius: 10px;
                                                    text-align: center;
                                                    width: max-content;
                                                    '>" . htmlspecialchars($row['category']) . "</span>
                                                </div>
                                                <div class='col-2 d-flex justify-content-center align-items-center'>
                                                            <div id='viewMore" . $j . "'>
                                                                <h6><a href='eventDetails.php?id=" . $announceId . "&prev=Events' style='color: #5f8cecff;'>View More</a></h6>
                                                            </div>
                                                            <div id='" . $editDeleteId . "' style='display:none;'>
                                                            <button class='btn btn-outline-success editBtn btn-sm' data-target='editModal" . $j . "'><span class='material-symbols-outlined'>edit</span></button>
                                                            <button class='btn btn-outline-danger deleteBtn btn-sm' data-target='deleteModal" . $j . "'><span class='material-symbols-outlined'>delete</span></button>
                                                            </div>
                                                </div>
                                            </div>

                                        ";
                                    ?>             <?php

                                                 echo <<<EOD

                                        <div class="editModal" id="editModal$j">
                                            <div class="innerModal">
                                                <div class="row">
                                                    <div class="col">
                                                        <h1>Edit</h1>
                                                        <br><figcaption class="blockquote-footer">(Note: Leave blank to remain unchanged)</figcaption>
                                                    </div>
                                                    <div class="col d-flex justify-content-end align-items-center">
                                                        <button class="btn btn-outline-secondary closeEditModal">X</button>
                                                    </div>
                                                    
                                                </div>
                                                <hr>
                                                <form method="POST">
                                                    <div class="row mt-4">
                                                        <div class="col">
                                                            <input type="hidden" name="announceID" id="announceID" value="$announceId">
                                                            <label for="oldDate" class="form-label">Old Date:</label>
                                                            <input type="date" name="oldDate" id="oldDate" class="form-control" disabled value="$oldDate">
                                                        </div>
                                                        <div class="col">
                                                            <label for="oldCategory" class="form-label">Old Category:</label>
                                                            <select type="select" name="oldCategory" id="oldCategory" class="form-select" disabled>
                                                            <option value="" selected>$oldCategory</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-1"></div>
                                                        <div class="col">
                                                            <label for="inputNewDate" class="form-label">New Date:</label>
                                                            <input type="date" name="inputNewDate" id="inputNewDate" placeholder="Type Something" class="form-control">
                                                        </div>
                                                        <div class="col">
                                                            <label for="inputNewCategory" class="form-label">New Category:</label>
                                                            <select type="select" name="inputNewCategory" id="inputNewCategory" class="form-select">
                                                            <option value="" selected disabled>Select Category</option>
                                                            <option value="Holiday">Holiday</option>
                                                            <option value="Event" >Event</option>
                                                            <option value="Research Event" >Research Event</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-4">
                                                        <div class="col">
                                                            <label for="oldTitle" class="form-label">Old Title:</label>
                                                            <input type="text" name="oldTitle" id="oldTitle" class="form-control" disabled value="$oldTitle">
                                                        </div>
                                                        <div class="col-1 d-flex align-items-end justify-content-center">
                                                            <h3>-></h3>
                                                        </div>
                                                        <div class="col">
                                                            <label for="inputNewTitle" class="form-label">New Title:</label>
                                                            <input type="text" name="inputNewTitle" class="form-control" placeholder="Enter Title Here.">
                                                        </div>
                                                    </div>
                                                    <div class="row mt-4">
                                                        <div class="col">
                                                            <label for="oldDescription">Old Description:</label>
                                                            <textarea disabled name="oldDescription" id="oldDescription" placeholder="Enter Text Here..." class="form-control" style="height: 150px;">$oldDesc</textarea>
                                                        </div>                                         
                                                    <div class="col-1 d-flex align-items-center justify-content-center">
                                                        <h3>-></h3>
                                                    </div>

                                                    <div class="col">
                                                        <label for="inputNewDescription" class="form-label">New Description:</label>
                                                        <textarea name="inputNewDescription" id="inputNewDescription" placeholder="Enter Text Here..." class="form-control" style="height: 150px;"></textarea>
                                                    </div>
                                                    </div>
                                                    <div class="row mt-5">
                                                        <div class="col d-flex justify-content-end align-items-center gap-2">
                                                            <button type="button" class="btn btn-outline-secondary closeEditModal">Close</button>
                                                            <button class="btn btn-outline-success" name="editSaveBtn" id="editSaveBtn">Save Changes</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                </form>
                                            </div>

                                            

                                            <div class="deleteModal" id="deleteModal$j">
                                            <div class="deleteInnerModal">
                                                <div class="row">
                                                    <div class="col">
                                                        <h1>Delete Announcement?</h1>
                                                        <hr>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col">
                                                        <h6>Are you sure you want to delete "$oldTitle" announcement?</h6>
                                                    </div>
                                                </div>
                                                <form method="POST">
                                                    <div class="row">
                                                        <div class="col d-flex align-items-center justify-content-end mt-5 gap-2">
                                                            <button class="btn btn-outline-secondary cancelBtn" type="button">Cancel</button>
                                                            <button class="btn btn-danger destroyBtn" name="deleteBtn">Delete</button>
                                                            <input type="hidden" value="$announceId" name="announceID">
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>

                                        </div>

                                        EOD;


                                                 $i = ($i + 1 > 6) ? 0 : $i + 1;
                                                 $j++;
                                }
                            } else {
                                echo <<<EOD
                                <div class="row">
                                    <div class="col d-flex align-items-center justify-content-center" 
                                    style="background-color: white; height: 100px; border-radius: 10px;">
                                        <label for="">That's all for now.</label>
                                    </div>
                                </div>
                                EOD;
                            }
                        }
                        ?>


                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../phpFunctions/alerts.php'); ?>


    <script>
        const totalNumberOfBtn = "<?php echo $totalNumberOfRows ?>";
        const currentPos = "<?php echo $currentPosition ?>"
        const announceBtn = $('#createAnnouncementBtn');
        const editBtn = $('#editAnnouncementBtn');
        const editHead = $('#editHeadText');
        const editTrigger = $('#editBtnTrigger');

        let isVisible = false;

        function toggleEdit() {

            editBtn.click(function () {

                for (let i = 0; i < totalNumberOfBtn; i++) {
                    var editDelete = "modifyBtn" + i;
                    var editDeleteBtn = $('#' + editDelete);
                    var viewMore = "viewMore" + i;
                    var viewMoreId = $('#' + viewMore);
                    console.log(editDeleteBtn);
                    viewMoreId.toggle();
                    editDeleteBtn.toggle();
                };
                isVisible = !isVisible;
                // editTrigger.prop('disabled', !isDisabled);
                editBtn.text(isVisible ? "View" : "Edit");
            });

        }


        $(document).ready(function () {
            console.log(currentPos);
            if (currentPos !== "Director") {
                if (currentPos !== "Technical Assistant") {
                    if (currentPos !== "RET Chair") {
                        announceBtn.hide();
                        editBtn.hide();
                    };
                };
            };

            toggleEdit();
        });
    </script>

    <script src="../scripts/customModal.js" async></script>
</body>

</html>