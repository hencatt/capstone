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

            <?php
            if ($eventCategory === "Research Event"):
                ?>
                <div class="row mt-5">
                    <div class="col">

                        <button class="btn btn-outline-primary" id="addPanelBtn">Assign Panel</button>

                    </div>
                </div>
                <?php
            endif;
            ?>

            <!-- PANEL MODAL -->
            <div id="addPanelModal" style="display: none;">
                <div class="custom-modal-backdrop"></div>
                <div class="custom-modal-dialog">
                    <div class="custom-modal-content">
                        <div class="custom-modal-header">
                            <h1 class="custom-modal-title">Assign Panel Members</h1>
                            <button type="button" class="custom-close-btn" id="closeModalBtn">&times;</button>
                        </div>
                        <form method="POST" action="" id="panelAssignForm">
                            <div class="custom-modal-body">
                                <input type="hidden" name="eventId" value="<?= $eventId ?>">

                                <div class="mb-3">
                                    <label class="form-label"><strong>Select Panel Members (Minimum 3
                                            required)</strong></label>
                                    <div id="panelMembersList" class="border rounded p-3"
                                        style="max-height: 400px; overflow-y: auto; background-color: white;">
                                        <?php
                                        $con = con();
                                        $sql = "SELECT 
                                        a.id, 
                                        CONCAT(a.fname, ' ', a.lname) as fullname, 
                                        a.department, 
                                        a.campus 
                                    FROM accounts_tbl a
                                    INNER JOIN employee_tbl e ON a.id = e.id
                                    WHERE a.position = 'Panel' 
                                    AND a.is_active = 1
                                    AND e.status = 'Active'
                                    ORDER BY a.lname, a.fname ASC";
                                        $result = $con->query($sql);

                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo '
                                    <div class="form-check mb-2">
                                        <input class="form-check-input panel-checkbox" type="checkbox" name="panelMembers[]" value="' . htmlspecialchars($row['id']) . '" id="panel' . htmlspecialchars($row['id']) . '">
                                        <label class="form-check-label" for="panel' . htmlspecialchars($row['id']) . '">
                                            <strong>' . htmlspecialchars($row['fullname']) . '</strong><br>
                                            <small class="text-muted">' . htmlspecialchars($row['department']) . ' - ' . htmlspecialchars($row['campus']) . '</small>
                                        </label>
                                    </div>';
                                            }
                                        } else {
                                            echo '<p class="text-muted">No panel members available.</p>';
                                        }
                                        $con->close();
                                        ?>
                                    </div>
                                    <small class="text-muted">Selected: <span id="selectedCount">0</span> panel
                                        member(s)</small>
                                </div>

                                <div class="alert alert-warning" role="alert" id="panelWarning" style="display: none;">
                                    Please select at least 3 panel members.
                                </div>
                            </div>
                            <div class="custom-modal-footer">
                                <button type="button" class="btn btn-secondary" id="cancelModalBtn">Cancel</button>
                                <button type="submit" class="btn btn-primary" name="assignPanel" id="assignPanelBtn"
                                    disabled>Assign Panel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <style>
                /* Custom Modal Styles */
                #addPanelModal {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 9999;
                }

                .custom-modal-backdrop {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0, 0, 0, 0.5);
                    z-index: 9998;
                }

                .custom-modal-dialog {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 90%;
                    max-width: 800px;
                    z-index: 9999;
                }

                .custom-modal-content {
                    background-color: white;
                    border-radius: 8px;
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                    max-height: 90vh;
                    overflow-y: auto;
                }

                .custom-modal-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 1rem 1.5rem;
                    border-bottom: 1px solid #dee2e6;
                }

                .custom-modal-title {
                    margin: 0;
                    font-size: 1.25rem;
                    font-weight: 500;
                }

                .custom-close-btn {
                    background: none;
                    border: none;
                    font-size: 2rem;
                    line-height: 1;
                    cursor: pointer;
                    padding: 0;
                    width: 30px;
                    height: 30px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .custom-close-btn:hover {
                    opacity: 0.7;
                }

                .custom-modal-body {
                    padding: 1.5rem;
                }

                .custom-modal-footer {
                    display: flex;
                    justify-content: flex-end;
                    gap: 10px;
                    padding: 1rem 1.5rem;
                    border-top: 1px solid #dee2e6;
                }
            </style>



        </div>
    </div>

    <!-- Add this script at the bottom of eventDetails.php before closing </body> -->
    <script>
        $(document).ready(function () {
            console.log("alksdjflkj")
            const addPanelBtn = $("#addPanelBtn");
            const panelModal = $("#addPanelModal");
            const closeModalBtn = $("#closeModalBtn");
            const cancelModalBtn = $("#cancelModalBtn");
            const panelCheckboxes = $(".panel-checkbox");
            const selectedCount = $("#selectedCount");
            const assignPanelBtn = $("#assignPanelBtn");
            const panelWarning = $("#panelWarning");

            // Open modal
            addPanelBtn.on("click", function () {
                console.log("clicked addpanel ")
                panelModal.fadeIn(200);
                $("body").css("overflow", "hidden"); // Prevent background scrolling
            });

            // Close modal functions
            function closeModal() {
                panelModal.fadeOut(200);
                $("body").css("overflow", "auto"); // Re-enable scrolling
            }

            closeModalBtn.on("click", closeModal);
            cancelModalBtn.on("click", closeModal);

            // Close on backdrop click
            $(".custom-modal-backdrop").on("click", closeModal);

            // Update count and validate selection
            panelCheckboxes.on("change", function () {
                const checkedCount = $(".panel-checkbox:checked").length;
                selectedCount.text(checkedCount);

                if (checkedCount >= 3) {
                    assignPanelBtn.prop("disabled", false);
                    panelWarning.hide();
                } else {
                    assignPanelBtn.prop("disabled", true);
                    if (checkedCount > 0) {
                        panelWarning.show();
                    } else {
                        panelWarning.hide();
                    }
                }
            });

            // Validate before form submission
            $("#panelAssignForm").on("submit", function (e) {
                const checkedCount = $(".panel-checkbox:checked").length;
                if (checkedCount < 3) {
                    e.preventDefault();
                    panelWarning.show();
                    return false;
                }
            });
        });
    </script>
</body>

</html>