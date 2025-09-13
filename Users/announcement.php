<?php
require_once 'includes.php';

session_start();

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");


$user = getUser();
$currentUser = $user['fullname'];
$currentPosition = $user['position'];
$currentDepartment = $user['department'];
$currentCampus = $user['campus'];

checkUser($_SESSION['user_id'], $_SESSION['user_username']);

if ($currentPosition !== "Director") {
    if ($currentPosition !== "Technical Assistant") {
        if ($currentPosition !== "RET Chair") {
            header("Location: ../index.php");
        }
    }
}

createAnnouncements("announcementBtn", $currentUser);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?= headerLinks("Announcement") ?>
</head>

<body>
    <?= addDelay("announcement", $currentUser, $currentPosition); ?>
    <div class="row everything">
        <div class="col sidebar">
            <?php sidebar("announcement", $currentPosition) ?>
        </div>
        <!-- MAIN CONTENT -->
        <div class="col-lg-10 col-sm-8 col-xs-6 mt-lg-3 mainContent">

            <?php topbar($currentUser, $currentPosition, "announcement") ?>
            <div id="contents">

                <div class="row mt-5">
                    <h1>Announcement <span class="material-symbols-outlined">
                            campaign
                        </span></h1>
                </div>
                <div class="row">
                    <h6>Everything here will be announced.</h6>
                </div>

                <form method="POST">
                    <div class="row mt-4">
                        <div class="col-4">
                            <label for="inputAnnouncementTitle" class="form-label">Announcement Title:</label>
                            <input type="text" name="inputAnnouncementTitle" id="inputAnnouncementTitle" class="form-control" placeholder="Enter title here.">
                        </div>
                        <div class="col"></div>
                        <div class="col-3">
                            <label for="inputAnnouncementDate" class="form-label">Date:</label>
                            <input type="date" name="inputAnnouncementDate" id="inputAnnouncementDate" class="form-control" pattern="\d{4}-\d{2}-\d{2}">
                        </div>
                        <div class="col">
                            <label for="inputCategory" class="form-label">Category</label>
                            <select name="inputCategory" id="inputCategory" class="form-select">
                                <option value="" disabled>Select Category</option>
                                <?php
                                if ($currentPosition === "RET Chair") {
                                    echo '<option value="Research Event" selected>Research Event</option>';
                                } else {
                                    echo <<<EOD
                                <option value="Holiday" default>Holiday</option>
                                <option value="Event">Event</option>
                                <option value="Research Event">Research Event</option>
                                EOD;
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col">
                            <label for="inputAnnouncementDescription" class="form-label">Description:</label>
                            <textarea name="inputAnnouncementDescription" id="inputAnnouncementDescription" class="form-control" placeholder="Enter text here..."
                                style="
                            height: 200px;
                            "></textarea>
                        </div>

                    </div>
                    <div class="row mt-3" id="researchEventDates">
                        <div class="col">
                            <label for="inputProposal" class="form-label">Proposal/Full Paper Submission:</label>
                            <input type="date" name="inputProposal" id="inputProposal" class="form-control" pattern="\d{4}-\d{2}-\d{2}">
                        </div>
                        <div class="col">
                            <label for="inputAcceptance" class="form-label">Acceptance notification:</label>
                            <input type="date" name="inputAcceptance" id="inputAcceptance" class="form-control" pattern="\d{4}-\d{2}-\d{2}">
                        </div>
                        <div class="col">
                            <label for="inputPresentationDate" class="form-label">Presentation Date:</label>
                            <input type="date" name="inputPresentationDate" id="inputPresentationDate" class="form-control" pattern="\d{4}-\d{2}-\d{2}">
                        </div>


                    </div>
                    <div class="row mt-5">
                        <div class="col gap-2 d-flex align-items-center justify-content-end">


                            <button type="button" class="btn btn-outline-secondary" id="clearAnnouncement">Clear</button>
                            <button class="btn btn-outline" style="background-color:plum;" id="announcementBtn" name="announcementBtn">Announce <span class="material-symbols-outlined">
                                    campaign</span></button>
                </form>
            </div>
        </div>
        <div class="row mt-2" id="announceNote" style="display:none">
            <figcaption class="blockquote-footer d-flex justify-content-end">(Note: wait for admin approval)</figcaption>
        </div>
    </div>
    </div>
    </div>


    <script>
        const currentPos = "<?php echo $currentPosition ?>";
        const announceTitle = $('#inputAnnouncementTitle');
        const announceDate = $('#inputAnnouncementDate');
        const announceDesc = $('#inputAnnouncementDescription');
        const announceBtn = $('#announcementBtn');
        const announceClearBtn = $('#clearAnnouncement');
        const resEventProposal = $('#inputProposal');
        const restEventAcceptance = $('#inputAcceptance');
        const resEventPresentationDate = $('#inputPresentationDate');
        const category = $('#inputCategory');



        $(document).ready(function() {
            console.log(currentPos);

            function checkFields() {
                if (announceTitle.val().trim() === "" || announceDesc.val().trim() === "") {
                    announceBtn.prop("disabled", true);
                    announceBtn.css('color', "white");
                } else {
                    announceBtn.prop("disabled", false);
                    announceBtn.css('color', "black");
                }

                if (category.val() === "Research Event") {
                    $('#researchEventDates').show();
                } else {
                    $('#researchEventDates').hide();
                }
            };


            announceClearBtn.click(function() {
                console.log("clicked");
                announceTitle.val("");
                announceDesc.val("");
                announceDate.val("");
                resEventPresentationDate.val("");
                resEventProposal.val("");
                restEventAcceptance.val("");
                checkFields();
            });


            checkFields();
            category.on('change', checkFields);
            announceTitle.on('input', checkFields);
            announceDesc.on('input', checkFields);

            // if (currentPos === "Technical Assistant") {
            //     $('#announceNote').show();
            // }
        });
    </script>
</body>

</html>